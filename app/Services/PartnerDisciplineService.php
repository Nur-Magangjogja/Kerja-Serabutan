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
     * Catat penolakan tawaran bantuan (Reject Offer / Expired Offer).
     * Evaluasi eskalasi SP jika akumulasi penolakan mencapai kelipatan 3x (3x -> SP1, 6x -> SP2, 9x -> SP3).
     */
    public function recordPartnerDecline(User $mitra, ?Help $help = null, ?string $reason = null): void
    {
        if ($mitra->role !== 'mitra') {
            return;
        }

        $this->evaluateWarningEscalation($mitra, 'decline', "Penolakan tawaran bantuan" . ($help ? " #{$help->id}" : ""));
    }

    /**
     * Evaluasi eskalasi Surat Peringatan (SP 1, SP 2, SP 3).
     * Setiap 3x penolakan/pembatalan:
     * - 3x  => SP 1
     * - 6x  => SP 2
     * - 9x  => SP 3
     */
    public function evaluateWarningEscalation(User $mitra, string $triggerType, string $detail): void
    {
        // Hitung total akumulasi penolakan tawaran dispatch oleh mitra
        $totalDeclines = HelpDispatch::where('mitra_id', $mitra->id)
            ->whereIn('status', [HelpDispatch::STATUS_REJECTED, HelpDispatch::STATUS_EXPIRED])
            ->count();

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

        $totalViolations = $totalDeclines + $totalCancels;
        $currentLevel = (int) ($mitra->warning_level ?? 0);

        // Perhitungan ambang batas:
        // Pembatalan ke-1: Masuk Daftar Abu-Abu (pengawasan, warning_level 0)
        // Setiap 3x pembatalan lagi:
        // - 1 + 3 = 4x pembatalan  => SP 1
        // - 4 + 3 = 7x pembatalan  => SP 2
        // - 7 + 3 = 10x pembatalan => SP 3 (+ Otomatis Shadow Ban)
        $targetLevel = 0;
        if ($totalCancels >= 10 || $totalViolations >= 10) {
            $targetLevel = 3;
        } elseif ($totalCancels >= 7 || $totalViolations >= 7) {
            $targetLevel = 2;
        } elseif ($totalCancels >= 4 || $totalViolations >= 4) {
            $targetLevel = 1;
        }

        if ($targetLevel > $currentLevel) {
            $warningMsg = match ($targetLevel) {
                1 => "Surat Peringatan Pertama (SP 1): Anda telah melakukan pembatalan/penolakan tugas sebanyak {$totalViolations} kali. Harap menjaga komitmen dalam menerima dan menyelesaikan permintaan bantuan.",
                2 => "Surat Peringatan Kedua (SP 2): Anda telah melakukan pembatalan/penolakan tugas sebanyak {$totalViolations} kali. Kinerja Anda dalam pengawasan ketat sistem.",
                3 => "Surat Peringatan Terakhir (SP 3): Batas pelanggaran pembatalan tugas telah tercapai ({$totalViolations} kali). Akun Anda dikenakan Shadow Ban (pembatasan akses tugas) dan menunggu peninjauan pemblokiran permanen oleh Admin.",
                default => "Peringatan kedisiplinan mitra.",
            };

            $updateData = [
                'is_greylisted'          => true,
                'greylisted_at'          => $mitra->greylisted_at ?? now(),
                'warning_level'          => $targetLevel,
                'greylist_reason'        => "Akumulasi penolakan/pembatalan tugas mencapai {$totalViolations} kali ({$detail}).",
                'latest_warning_message' => $warningMsg,
                'latest_warning_at'      => now(),
            ];

            // Pada SP 3, langsung kenakan Shadow Ban otomatis
            if ($targetLevel === 3) {
                $updateData['is_shadow_banned'] = true;
                $updateData['shadow_banned_at'] = now();
                \App\Models\PartnerOnlineState::where('user_id', $mitra->id)->update([
                    'matching_status' => \App\Models\PartnerOnlineState::STATUS_OFFLINE,
                    'searching_since' => null,
                ]);
            }

            $mitra->update($updateData);

            UserGreylistLog::create([
                'user_id'       => $mitra->id,
                'admin_id'      => null,
                'action'        => 'warning_issued',
                'warning_level' => $targetLevel,
                'reason'        => "Akumulasi penolakan/pembatalan tugas mencapai {$totalViolations} kali ({$detail}).",
                'message'       => $warningMsg,
            ]);

            if ($targetLevel === 3) {
                UserGreylistLog::create([
                    'user_id'       => $mitra->id,
                    'admin_id'      => null,
                    'action'        => 'shadow_ban_enabled',
                    'warning_level' => 3,
                    'reason'        => "Otomatis Shadow Ban karena telah mencapai SP 3 (Batas maksimal pembatalan tugas).",
                    'message'       => "Akun otomatis dikenakan Shadow Ban. Fitur pencarian bantuan ditangguhkan sementara.",
                ]);
            }

            ActivityLog::record(
                null,
                'warning_issued_auto',
                "Sistem otomatis menerbitkan SP {$targetLevel} kepada mitra {$mitra->name} (#{$mitra->id}) karena akumulasi pelanggaran mencapai {$totalViolations} kali." . ($targetLevel === 3 ? " [Shadow Ban Diterapkan]" : ""),
                ['mitra_id' => $mitra->id, 'warning_level' => $targetLevel, 'total_violations' => $totalViolations]
            );

            Log::info("[PartnerDisciplineService] Mitra #{$mitra->id} issued SP {$targetLevel} (Total violations: {$totalViolations})");
        }
    }
}
