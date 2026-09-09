<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Help;
use App\Models\HelpDispatch;
use App\Models\User;
use App\Models\UserGreylistLog;
use Illuminate\Support\Facades\Log;

class PartnerDisciplineService
{
    /**
     * Catat pembatalan tugas bantuan yang telah diambil oleh mitra.
     * Masukkan mitra ke Daftar Abu-Abu (Greylist) pada pembatalan pertama,
     * dan evaluasi eskalasi Surat Peringatan (SP).
     */
    public function recordPartnerCancellation(User $mitra, Help $help, ?string $reason = null): void
    {
        if ($mitra->role !== 'mitra') {
            return;
        }

        // 1. Masukkan ke Daftar Abu-Abu jika belum greylisted
        if (!$mitra->is_greylisted) {
            $mitra->update([
                'is_greylisted'   => true,
                'greylisted_at'   => now(),
                'greylist_reason' => "Otomatis Sistem: Mitra melakukan pembatalan tugas bantuan #{$help->id} ('{$help->title}') yang telah diambil.",
            ]);

            UserGreylistLog::create([
                'user_id'       => $mitra->id,
                'admin_id'      => null,
                'action'        => 'greylist_add',
                'warning_level' => (int) $mitra->warning_level,
                'reason'        => "Pembatalan tugas bantuan #{$help->id} yang telah diambil. Alasan: " . ($reason ?: 'Tidak disebutkan'),
                'message'       => "Akun mitra otomatis dimasukkan ke Daftar Abu-Abu untuk pengawasan Admin.",
            ]);

            ActivityLog::record(
                null,
                'greylist_add_partner_cancel',
                "Sistem otomatis memasukkan mitra {$mitra->name} (#{$mitra->id}) ke Daftar Abu-Abu karena membatalkan tugas bantuan #{$help->id}.",
                ['mitra_id' => $mitra->id, 'help_id' => $help->id]
            );
        }

        // 2. Evaluasi eskalasi Surat Peringatan (SP 1 - SP 3)
        $this->evaluateWarningEscalation($mitra, 'cancellation', "Pembatalan tugas bantuan #{$help->id}");
    }

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
        $helpInfo    = $help ? " pada pesanan #{$help->id} ('{$help->title}')" : "";

        $roleTitle = ($user->role === 'mitra') ? 'Mitra' : 'Customer';

        $warningMsg = match ($targetLevel) {
            1 => "Surat Peringatan Pertama (SP 1): Anda mendapatkan SP 1 dari {$adminName}{$helpInfo}. Alasan: {$reason}. Harap patuhi ketentuan layanan SayaBantu.",
            2 => "Surat Peringatan Kedua (SP 2): Anda mendapatkan SP 2 dari {$adminName}{$helpInfo}. Alasan: {$reason}. Akun Anda berada dalam pengawasan ketat.",
            3 => "Surat Peringatan Terakhir (SP 3): Anda mendapatkan SP 3 dari {$adminName}{$helpInfo}. Alasan: {$reason}. Akun Anda dikenakan pembatasan penuh / sanksi keras.",
            default => "Peringatan kedisiplinan dari {$adminName}: {$reason}",
        };

        $updateData = [
            'is_greylisted'          => true,
            'greylisted_at'          => $user->greylisted_at ?? now(),
            'warning_level'          => $targetLevel,
            'greylist_reason'        => "Keputusan {$adminName}: SP {$targetLevel}{$helpInfo}. Alasan: {$reason}",
            'latest_warning_message' => $warningMsg,
            'latest_warning_at'      => now(),
        ];

        // Pada SP 3 untuk mitra, aktifkan Shadow Ban otomatis
        if ($targetLevel === 3 && $user->role === 'mitra') {
            $updateData['is_shadow_banned'] = true;
            $updateData['shadow_banned_at'] = now();
            \App\Models\PartnerOnlineState::where('user_id', $user->id)->update([
                'matching_status' => \App\Models\PartnerOnlineState::STATUS_OFFLINE,
                'searching_since' => null,
            ]);
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

        if ($targetLevel === 3 && $user->role === 'mitra') {
            UserGreylistLog::create([
                'user_id'       => $user->id,
                'admin_id'      => $admin?->id,
                'action'        => 'shadow_ban_enabled',
                'warning_level' => 3,
                'reason'        => "Shadow Ban diaktifkan karena telah mencapai SP 3.",
                'message'       => "Akun dikenakan Shadow Ban oleh {$adminName}.",
            ]);
        }

        ActivityLog::record(
            $admin?->id,
            'admin_manual_warning_issued',
            "{$adminName} menerbitkan SP {$targetLevel} kepada {$roleTitle} {$user->name} (#{$user->id}){$helpInfo}. Alasan: {$reason}",
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
     * Evaluasi eskalasi Surat Peringatan (SP 1, SP 2, SP 3) akumulatif (legacy helper).
     */
    public function evaluateWarningEscalation(User $mitra, string $triggerType, string $detail): void
    {
        // Hitung total pembatalan bantuan yang pernah diambil oleh mitra via relasi terindeks
        $totalCancels = 0;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('help_partner_exclusions')) {
                $totalCancels = \App\Models\HelpPartnerExclusion::where('mitra_id', $mitra->id)->count();
            }
        } catch (\Throwable $e) {
            // ignore
        }

        if ($totalCancels === 0) {
            $totalCancels = Help::where(function ($q) use ($mitra) {
                $q->whereJsonContains('cancelled_mitra_ids', $mitra->id)
                  ->orWhereJsonContains('cancelled_mitra_ids', (string) $mitra->id);
            })->count();
        }

        $currentLevel = (int) ($mitra->warning_level ?? 0);

        // Ambang batas sanksi pembatalan tugas yang sudah diambil:
        // Pembatalan ke-1: Masuk Daftar Abu-Abu (pengawasan, warning_level 0)
        // - 3x pembatalan => SP 1
        // - 6x pembatalan => SP 2
        // - 9x pembatalan => SP 3 (+ Otomatis Shadow Ban)
        $targetLevel = 0;
        if ($totalCancels >= 9) {
            $targetLevel = 3;
        } elseif ($totalCancels >= 6) {
            $targetLevel = 2;
        } elseif ($totalCancels >= 3) {
            $targetLevel = 1;
        }

        if ($targetLevel > $currentLevel) {
            $this->issueManualWarningToUser(
                $mitra,
                $targetLevel,
                "Akumulasi pembatalan tugas bantuan yang telah diambil mencapai {$totalCancels} kali ({$detail}).",
                null
            );
        }
    }
}
