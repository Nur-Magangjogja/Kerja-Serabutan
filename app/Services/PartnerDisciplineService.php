<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Help;
use App\Models\User;
use App\Models\UserGreylistLog;
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
}
