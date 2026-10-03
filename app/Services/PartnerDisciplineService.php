<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Help;
use App\Models\PartnerReport;
use App\Models\User;
use App\Models\UserGreylistLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PartnerDisciplineService
{
    /**
     * Penolakan/pengabaian tawaran order saat matching radar TIDAK memicu SP.
     * Mitra bebas memilih tawaran yang sesuai. Penolakan hanya mempengaruhi
     * transisi status radar (demote ke standby jika menolak 2x berturut-turut).
     */
    public function recordPartnerDecline(User $mitra, ?Help $help = null, ?string $reason = null): void
    {
        // No-op: Menolak saat mencari order tidak dihitung untuk SP.
    }

    /**
     * Compatibility bridge untuk issueManualWarningToUser.
     */
    public function issueWarning(
        int|User $user,
        int $targetLevel,
        string $reason,
        int|User|null $admin = null,
        int|Help|null $help = null
    ): void {
        $userObj  = is_numeric($user) ? User::find($user) : $user;
        $adminObj = is_numeric($admin) ? User::find($admin) : $admin;
        $helpObj  = is_numeric($help) ? Help::find($help) : $help;

        if ($userObj) {
            $this->issueManualWarningToUser($userObj, $targetLevel, $reason, $adminObj, $helpObj);
        }
    }

    /**
     * Penerbitan Surat Peringatan (SP 1, SP 2, SP 3) secara manual oleh Admin Wilayah
     * kepada Pengguna (Mitra maupun Customer) jika ditemukan kejanggalan/pelanggaran.
     */
    public function issueManualWarningToUser(
        User $user,
        int $targetLevel,
        string $reason,
        ?User $admin = null,
        ?Help $help = null
    ): void {
        $targetLevel = max(1, min(3, $targetLevel));
        $adminName   = $admin ? $admin->name : 'Admin Wilayah';
        $helpInfo    = $help ? " pada pesanan '{$help->title}'" : "";

        $roleTitle = ($user->role === 'mitra') ? 'Mitra' : 'Customer';

        // Pesan user-facing: tanpa referensi pesanan & nama admin digeneralisasi
        $warningMsg = match ($targetLevel) {
            1 => "Surat Peringatan Pertama (SP 1): Anda mendapatkan SP 1 dari Admin Wilayah SayaBantu. Alasan: {$reason}. Harap patuhi ketentuan layanan SayaBantu.",
            2 => "Surat Peringatan Kedua (SP 2): Anda mendapatkan SP 2 dari Admin Wilayah SayaBantu. Alasan: {$reason}. Akun Anda berada dalam pengawasan ketat.",
            3 => "Surat Peringatan Terakhir (SP 3): Anda mendapatkan SP 3 dari Admin Wilayah SayaBantu. Alasan: {$reason}. Akun Anda dikenakan pembatasan penuh / sanksi keras.",
            default => "Peringatan kedisiplinan dari Admin Wilayah SayaBantu: {$reason}",
        };

        $updateData = [
            'is_greylisted'          => true,
            'greylisted_at'          => $user->greylisted_at ?? now(),
            'warning_level'          => $targetLevel,
            'greylist_reason'        => "Keputusan {$adminName}: SP {$targetLevel}{$helpInfo}. Alasan: {$reason}",
            'latest_warning_message' => $warningMsg,
            'latest_warning_at'      => now(),
        ];

        // Pada SP 3 (Mitra maupun Customer), aktifkan Shadow Ban otomatis
        if ($targetLevel === 3) {
            $updateData['is_shadow_banned'] = true;
            $updateData['shadow_banned_at'] = now();
            if ($user->role === 'mitra') {
                \App\Models\PartnerOnlineState::where('user_id', $user->id)->update([
                    'matching_status' => \App\Models\PartnerOnlineState::STATUS_OFFLINE,
                    'searching_since' => null,
                ]);
            }
        }

        $user->update($updateData);

        UserGreylistLog::create([
            'user_id'       => $user->id,
            'admin_id'      => $admin?->id,
            'action'        => 'warning_issued',
            'warning_level' => $targetLevel,
            'reason'        => "SP {$targetLevel} diterbitkan {$adminName}{$helpInfo}. Alasan: {$reason}",
            'message'       => $warningMsg,
        ]);

        if ($targetLevel === 3) {
            UserGreylistLog::create([
                'user_id'       => $user->id,
                'admin_id'      => $admin?->id,
                'action'        => 'shadow_ban_enabled',
                'warning_level' => 3,
                'reason'        => "Shadow Ban diaktifkan karena telah mencapai SP 3.",
                'message'       => "Akun {$user->name} dikenakan Shadow Ban oleh {$adminName}.",
            ]);
        }

        ActivityLog::record(
            $admin?->id,
            'admin_manual_warning_issued',
            "{$adminName} menerbitkan SP {$targetLevel} kepada {$roleTitle} {$user->name}{$helpInfo}. Alasan: {$reason}",
            [
                'target_user_id' => $user->id,
                'role'           => $user->role,
                'warning_level'  => $targetLevel,
                'help_id'        => $help?->id,
                'reason'         => $reason,
            ]
        );

        // Kirim notifikasi sistem ke user terkait
        try {
            if ($help) {
                $user->notify(new \App\Notifications\HelpStatusNotification($help, $warningMsg));
            }
        } catch (\Throwable $e) {
            Log::warning("[PartnerDisciplineService] Failed notifying user #{$user->id} for manual SP: " . $e->getMessage());
        }

        Log::info("[PartnerDisciplineService] {$roleTitle} #{$user->id} issued manual SP {$targetLevel} by Admin #{$admin?->id}");
    }

    /**
     * Penerbitan Surat Peringatan (SP) instan dari Laporan Aduan (PartnerReport)
     * dengan atomic transaction, lock ordering PartnerReport -> User, dan idempotency guard.
     */
    public function issueInstantSpFromReport(
        PartnerReport $report,
        int|User $targetUser,
        string $reason,
        User $admin,
        ?int $requestedLevel = null,
        bool $autoNoteInReport = true
    ): array {
        // 1. Authorize Admin
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);
        if (!$isSuperAdmin) {
            $canonicalTerritory = $report->getCanonicalTerritory();
            $authService = app(\App\Services\Territory\AdminTerritoryAuthorizationService::class);
            if (!$authService->canAccessTerritory(
                $admin,
                $canonicalTerritory['district_id'] ? (int)$canonicalTerritory['district_id'] : null,
                $canonicalTerritory['city_id'] ? (int)$canonicalTerritory['city_id'] : null
            )) {
                throw new \Illuminate\Auth\Access\AuthorizationException('Anda tidak memiliki wewenang untuk meninjau atau mendisiplinkan laporan di luar wilayah Anda.');
            }
        }

        // 2. Begin DB Transaction
        try {
            $result = DB::transaction(function () use ($report, $targetUser, $reason, $admin, $requestedLevel, $autoNoteInReport, $isSuperAdmin) {
                // 3. Lock PartnerReport row
                /** @var PartnerReport $lockedReport */
                $lockedReport = PartnerReport::where('id', $report->id)->lockForUpdate()->firstOrFail();

                // Re-verify territory against locked report state
                if (!$isSuperAdmin) {
                    $canonicalTerritory = $lockedReport->getCanonicalTerritory();
                    $authService = app(\App\Services\Territory\AdminTerritoryAuthorizationService::class);
                    if (!$authService->canAccessTerritory(
                        $admin,
                        $canonicalTerritory['district_id'] ? (int)$canonicalTerritory['district_id'] : null,
                        $canonicalTerritory['city_id'] ? (int)$canonicalTerritory['city_id'] : null
                    )) {
                        throw new \Illuminate\Auth\Access\AuthorizationException('Laporan ini berada di luar wilayah wewenang Anda.');
                    }
                }

                // 4. Resolve target User
                $targetUserId = ($targetUser instanceof User) ? $targetUser->id : (int) $targetUser;

                // 5. Check if UserGreylistLog with partner_report_id = lockedReport->id already exists
                $alreadyProcessed = UserGreylistLog::where('partner_report_id', $lockedReport->id)->exists();
                if ($alreadyProcessed) {
                    throw new \RuntimeException('Tindakan pendisiplinan (SP) untuk laporan aduan ini telah diproses sebelumnya oleh administrator.');
                }

                // 7. Lock target User row (Lock order: PartnerReport -> User)
                /** @var User $lockedUser */
                $lockedUser = User::where('id', $targetUserId)->lockForUpdate()->firstOrFail();

                // 8. Read CURRENT warning_level
                $currentLevel = (int) ($lockedUser->warning_level ?? 0);

                // 9. Compute next warning level
                $nextLevel = min(3, max(1, $currentLevel + 1));
                $targetLevel = ($requestedLevel !== null && $requestedLevel > $currentLevel)
                    ? min(3, $requestedLevel)
                    : $nextLevel;

                $adminName = $admin->name ?? 'Admin Wilayah';
                $help = $lockedReport->reportedHelp;
                $helpInfo = $help ? " pada pesanan '{$help->title}'" : "";
                $roleTitle = ($lockedUser->role === 'mitra') ? 'Mitra' : 'Customer';

                $warningMsg = match ($targetLevel) {
                    1 => "Surat Peringatan Pertama (SP 1): Anda mendapatkan SP 1 dari Admin Wilayah SayaBantu. Alasan: {$reason}. Harap patuhi ketentuan layanan SayaBantu.",
                    2 => "Surat Peringatan Kedua (SP 2): Anda mendapatkan SP 2 dari Admin Wilayah SayaBantu. Alasan: {$reason}. Akun Anda berada dalam pengawasan ketat.",
                    3 => "Surat Peringatan Terakhir (SP 3): Anda mendapatkan SP 3 dari Admin Wilayah SayaBantu. Alasan: {$reason}. Akun Anda dikenakan pembatasan penuh / sanksi keras.",
                    default => "Peringatan kedisiplinan dari Admin Wilayah SayaBantu: {$reason}",
                };

                // 10. Update User
                $updateData = [
                    'is_greylisted'          => true,
                    'greylisted_at'          => $lockedUser->greylisted_at ?? now(),
                    'warning_level'          => $targetLevel,
                    'greylist_reason'        => "Keputusan {$adminName}: SP {$targetLevel}{$helpInfo}. Alasan: {$reason}",
                    'latest_warning_message' => $warningMsg,
                    'latest_warning_at'      => now(),
                ];

                if ($targetLevel === 3) {
                    $updateData['is_shadow_banned'] = true;
                    $updateData['shadow_banned_at'] = now();
                    if ($lockedUser->role === 'mitra') {
                        \App\Models\PartnerOnlineState::where('user_id', $lockedUser->id)->update([
                            'matching_status' => \App\Models\PartnerOnlineState::STATUS_OFFLINE,
                            'searching_since' => null,
                        ]);
                    }
                }

                $lockedUser->update($updateData);

                // 11. Create UserGreylistLog with partner_report_id
                UserGreylistLog::create([
                    'user_id'           => $lockedUser->id,
                    'admin_id'          => $admin->id,
                    'partner_report_id' => $lockedReport->id,
                    'action'            => 'warning_issued',
                    'warning_level'     => $targetLevel,
                    'reason'            => "SP {$targetLevel} diterbitkan {$adminName}{$helpInfo}. Alasan: {$reason}",
                    'message'           => $warningMsg,
                ]);

                if ($targetLevel === 3) {
                    UserGreylistLog::create([
                        'user_id'       => $lockedUser->id,
                        'admin_id'      => $admin->id,
                        'action'        => 'shadow_ban_enabled',
                        'warning_level' => 3,
                        'reason'        => "Shadow Ban diaktifkan karena telah mencapai SP 3.",
                        'message'       => "Akun {$lockedUser->name} dikenakan Shadow Ban oleh {$adminName}.",
                    ]);
                }

                // 12. Create ActivityLog
                ActivityLog::record(
                    $admin->id,
                    'admin_manual_warning_issued',
                    "{$adminName} menerbitkan SP {$targetLevel} kepada {$roleTitle} {$lockedUser->name}{$helpInfo}. Alasan: {$reason}",
                    [
                        'target_user_id'    => $lockedUser->id,
                        'partner_report_id' => $lockedReport->id,
                        'role'              => $lockedUser->role,
                        'warning_level'     => $targetLevel,
                        'help_id'           => $help?->id,
                        'reason'            => $reason,
                    ]
                );

                // 13. Update PartnerReport admin note if requested
                if ($autoNoteInReport) {
                    $timestamp = now()->format('d M Y H:i');
                    $entry = "[{$timestamp} oleh {$adminName}]: Menerbitkan SP {$targetLevel} kepada {$roleTitle} {$lockedUser->name}. Alasan: " . trim($reason);
                    $existing = $lockedReport->admin_notes ? $lockedReport->admin_notes . "\n" : '';
                    $lockedReport->update([
                        'admin_notes' => $existing . $entry,
                    ]);
                }

                // 14. Return commit payload
                return [
                    'user'        => $lockedUser,
                    'help'        => $help,
                    'warningMsg'  => $warningMsg,
                    'targetLevel' => $targetLevel,
                    'roleTitle'   => $roleTitle,
                ];
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            throw new \RuntimeException('Laporan aduan ini telah diproses untuk tindakan pendisiplinan oleh administrator lain.');
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'user_greylist_logs_partner_report_unique') || $e->getCode() == 23000) {
                throw new \RuntimeException('Laporan aduan ini telah diproses untuk tindakan pendisiplinan oleh administrator lain.');
            }
            throw $e;
        }

        // 15. Notification dispatch after commit
        try {
            if ($result['help']) {
                $result['user']->notify(new \App\Notifications\HelpStatusNotification($result['help'], $result['warningMsg']));
            }
        } catch (\Throwable $e) {
            Log::warning("[PartnerDisciplineService] Failed notifying user #{$result['user']->id} for manual SP: " . $e->getMessage());
        }

        Log::info("[PartnerDisciplineService] {$result['roleTitle']} #{$result['user']->id} issued manual SP {$result['targetLevel']} via Report #{$report->id} by Admin #{$admin->id}");

        return $result;
    }
}

