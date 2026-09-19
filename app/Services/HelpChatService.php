<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Help;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class HelpChatService
{
    /**
     * Kirim ucapan selamat datang dari Mitra ke Customer saat tugas diambil.
     */
    public function sendWelcomeChat(Help $help, User $mitra): void
    {
        try {
            $customer = $help->user ?? User::find($help->user_id);
            if (!$customer || !$mitra) return;

            $greeting = $customer->name ? "Halo Kak {$customer->name}" : "Halo Kak";
            $message  = "{$greeting}, perkenalkan saya {$mitra->name}. Saya telah mengambil permohonan bantuan Anda '{$help->title}'. Saya akan segera menuju lokasi Anda. Jika ada instruksi tambahan, silakan infokan di sini!";

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $mitra->id,
                'customer_id' => $customer->id,
                'message'     => $message,
                'sender_type' => 'mitra',
                'read_at'     => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[HelpChatService] Failed to send welcome chat: ' . $e->getMessage());
        }
    }

    /**
     * Kirim pesan bahwa pengerjaan jasa telah dimulai.
     */
    public function sendServiceStartedChat(Help $help, User $mitra): void
    {
        try {
            $customer = $help->user ?? User::find($help->user_id);
            if (!$customer || !$mitra) return;

            $greeting = $customer->name ? "Halo Kak {$customer->name}" : "Halo Kak";
            $message  = "{$greeting}, saya ({$mitra->name}) telah mulai mengerjakan permohonan bantuan Anda '{$help->title}'. Pelayanan saat ini dalam proses pengerjaan. Jika ada instruksi atau hal yang perlu dikoordinasikan, silakan infokan di chat ini ya!";

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $mitra->id,
                'customer_id' => $customer->id,
                'message'     => $message,
                'sender_type' => 'mitra',
                'read_at'     => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[HelpChatService] Failed to send service started chat: ' . $e->getMessage());
        }
    }

    /**
     * Kirim pesan penyelesaian dari Mitra beserta lampiran foto bukti.
     */
    public function sendCompletionChat(Help $help, User $mitra, ?string $proofPath = null, ?string $notes = null): void
    {
        try {
            $customer = $help->user ?? User::find($help->user_id);
            if (!$customer) return;

            $notesText = $notes ? "Catatan: \"{$notes}\". " : '';
            $caption   = "Halo Kak {$customer->name}, pekerjaan '{$help->title}' telah selesai saya kerjakan. {$notesText}Tugas ini telah diselesaikan dan menunggu konfirmasi Anda. Terima kasih!";

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $mitra->id,
                'customer_id' => $customer->id,
                'message'     => $caption,
                'photo'       => $proofPath,
                'sender_type' => 'mitra',
                'read_at'     => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[HelpChatService] Failed to send completion chat: ' . $e->getMessage());
        }
    }

    /**
     * Kirim ucapan terima kasih dari Customer saat konfirmasi penyelesaian.
     */
    public function sendConfirmationChat(Help $help, User $customer, ?User $mitra): void
    {
        try {
            if (!$mitra) return;

            $message = "Terima kasih Kak {$mitra->name}, pekerjaan '{$help->title}' telah saya konfirmasi selesai. Pembayaran telah diteruskan ke saldo akun Anda. Semoga sukses selalu!";

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $mitra->id,
                'customer_id' => $customer->id,
                'message'     => $message,
                'sender_type' => 'customer',
                'read_at'     => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[HelpChatService] Failed to send confirmation chat: ' . $e->getMessage());
        }
    }

    /**
     * Kirim pesan pengajuan kendala / pembatalan ke ruang chat.
     */
    public function sendCancellationRequestChat(Help $help, User $mitra, ?string $reason = null): void
    {
        try {
            $customer = $help->user ?? User::find($help->user_id);
            if (!$customer || !$mitra) return;

            $greeting   = $customer->name ? "Halo Kak {$customer->name}" : "Halo Kak";
            $reasonText = !empty($reason) ? " dengan alasan: \"{$reason}\"" : "";
            $message    = "{$greeting}, mohon maaf saya mengajukan pembatalan untuk permohonan bantuan '{$help->title}'{$reasonText}. Mohon kesediaannya untuk memeriksa dan memberikan persetujuan pada detail pesanan Anda. Terima kasih dan mohon maaf atas ketidaknyamanannya.";

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $mitra->id,
                'customer_id' => $customer->id,
                'message'     => $message,
                'sender_type' => 'mitra',
                'read_at'     => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[HelpChatService] Failed to send cancellation request chat: ' . $e->getMessage());
        }
    }

    /**
     * Kirim pesan resolusi status pembatalan (disetujui / ditolak / di-redispatch).
     */
    public function sendCancellationResolvedChat(Help $help, ?User $mitra, ?User $customer, string $action): void
    {
        try {
            if (!$mitra || !$customer) return;

            if ($action === 'partner_cancelled_redispatched') {
                $reasonText = $help->partner_cancel_reason ? " (Alasan: {$help->partner_cancel_reason})" : "";
                $message = "Sistem SayaBantu: Rekan Jasa {$mitra->name} telah membatalkan penugasan bantuan ini{$reasonText}. Sistem saat ini sedang otomatis mencari Rekan Jasa pengganti untuk Anda.";

                Chat::create([
                    'help_id'     => $help->id,
                    'mitra_id'    => $mitra->id,
                    'customer_id' => $customer->id,
                    'message'     => $message,
                    'sender_type' => 'system',
                    'read_at'     => null,
                ]);
            } elseif ($action === 'accepted') {
                $message = "Sistem SayaBantu: Permintaan pembatalan untuk bantuan '{$help->title}' telah disetujui oleh Customer. Pesanan ini telah dikembalikan ke pencarian Rekan Jasa lain.";

                Chat::create([
                    'help_id'     => $help->id,
                    'mitra_id'    => $mitra->id,
                    'customer_id' => $customer->id,
                    'message'     => $message,
                    'sender_type' => 'system',
                    'read_at'     => null,
                ]);
            } else {
                $message = "Halo Rekan Jasa {$mitra->name}, permintaan pembatalan Anda untuk bantuan '{$help->title}' ditolak oleh Customer. Mohon untuk melanjutkan pengerjaan bantuan ini.";

                Chat::create([
                    'help_id'     => $help->id,
                    'mitra_id'    => $mitra->id,
                    'customer_id' => $customer->id,
                    'message'     => $message,
                    'sender_type' => 'system',
                    'read_at'     => null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('[HelpChatService] Failed to send cancellation resolved chat: ' . $e->getMessage());
        }
    }
}
