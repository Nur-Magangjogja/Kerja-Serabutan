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
     * Kirim pesan saat Mitra membatalkan / mengajukan kendala:
     * - Stage 'transit': Konsep 1 (di perjalanan / tiba di lokasi sebelum mulai). Relist ke pool.
     * - Stage 'in_progress': Konsep 2 (setelah tombol Mulai Pekerjaan). Menunggu verifikasi admin & klarifikasi customer.
     * - Stage 'pickup_delivery': Pembatalan layanan antar jemput oleh mitra.
     */
    public function sendPartnerCancellationChat(
        Help $help,
        User $mitra,
        string $reason,
        ?string $notes = null,
        string $stage = 'transit'
    ): void {
        try {
            $customer = $help->user ?? User::find($help->user_id);
            if (!$customer || !$mitra) return;

            $greeting  = $customer->name ? "Halo Kak {$customer->name}" : "Halo Kak";
            $notesText = !empty($notes) ? " Catatan: \"{$notes}\"." : "";

            if ($stage === 'in_progress') {
                $message = "{$greeting}, mohon maaf saya ({$mitra->name}) mengajukan kendala lapangan saat pengerjaan untuk pesanan '{$help->title}' dengan alasan: \"{$reason}\".{$notesText} Pengajuan ini telah diteruskan ke Admin Wilayah SayaBantu untuk ditinjau dan dikonfirmasikan lebih lanjut ke Anda.";
            } elseif ($stage === 'pickup_delivery') {
                $message = "{$greeting}, mohon maaf saya ({$mitra->name}) terpaksa membatalkan pesanan antar-jemput '{$help->title}' karena kendala: \"{$reason}\".{$notesText} Mohon maaf atas ketidaknyamanan yang ditimbulkan.";
            } else {
                $message = "{$greeting}, mohon maaf sebesar-besarnya saya ({$mitra->name}) mengalami kendala di perjalanan sehingga tidak dapat melanjutkan pesanan '{$help->title}': \"{$reason}\".{$notesText} Pesanan Anda otomatis dialihkan kembali ke pencarian Rekan Jasa pengganti agar dapat segera ditangani.";
            }

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $mitra->id,
                'customer_id' => $customer->id,
                'sender_id'   => $mitra->id,
                'sender_type' => 'mitra',
                'message'     => $message,
                'read_at'     => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[HelpChatService] Failed to send partner cancellation chat: ' . $e->getMessage());
        }
    }

    /**
     * Kirim pesan saat Customer membatalkan pesanan / menarik pekerjaan:
     * - Type 'withdraw': Customer mengajukan penarikan pesanan yang telah diambil mitra.
     * - Type 'pickup_delivery': Customer membatalkan pesanan antar-jemput.
     */
    public function sendCustomerCancellationChat(
        Help $help,
        User $customer,
        User $mitra,
        string $reason,
        ?string $notes = null,
        string $type = 'withdraw'
    ): void {
        try {
            if (!$customer || !$mitra) return;

            $partnerName = $mitra->name ? "Halo Kak {$mitra->name}" : "Halo Kak";
            $notesText   = !empty($notes) ? " Catatan: \"{$notes}\"." : "";

            if ($type === 'pickup_delivery') {
                $message = "{$partnerName}, saya telah membatalkan pesanan antar-jemput '{$help->title}' dengan alasan: \"{$reason}\".{$notesText} Kompensasi perjalanan dan pengembalian dana telah diproses oleh sistem sesuai ketentuan.";
            } else {
                $message = "{$partnerName}, saya mengajukan penarikan pekerjaan untuk pesanan '{$help->title}' dengan alasan: \"{$reason}\".{$notesText} Mohon kesediaannya untuk memberikan konfirmasi persetujuan pada aplikasi Anda. Terima kasih.";
            }

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $mitra->id,
                'customer_id' => $customer->id,
                'sender_id'   => $customer->id,
                'sender_type' => 'customer',
                'message'     => $message,
                'read_at'     => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[HelpChatService] Failed to send customer cancellation chat: ' . $e->getMessage());
        }
    }

    /**
     * Kirim pesan saat Customer memilih opsi "Ganti Mitra".
     */
    public function sendCustomerSwitchPartnerChat(
        Help $help,
        User $customer,
        User $oldMitra,
        string $reason,
        ?string $notes = null
    ): void {
        try {
            if (!$customer || !$oldMitra) return;

            $partnerName = $oldMitra->name ? "Halo Kak {$oldMitra->name}" : "Halo Kak";
            $notesText   = !empty($notes) ? " Catatan: \"{$notes}\"." : "";
            $message     = "{$partnerName}, saya telah mengajukan permohonan Ganti Rekan Jasa untuk pesanan '{$help->title}' dengan alasan: \"{$reason}\".{$notesText} Tugas dialihkan kembali ke pencarian rekan jasa lain. Terima kasih atas waktunya.";

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $oldMitra->id,
                'customer_id' => $customer->id,
                'sender_id'   => $customer->id,
                'sender_type' => 'customer',
                'message'     => $message,
                'read_at'     => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[HelpChatService] Failed to send customer switch partner chat: ' . $e->getMessage());
        }
    }

    /**
     * Kirim pesan respon Mitra atas pengajuan penarikan Customer (Setuju vs Tolak).
     */
    public function sendPartnerResponseToWithdrawChat(
        Help $help,
        User $partner,
        User $customer,
        bool $isConfirmed,
        ?string $notes = null
    ): void {
        try {
            if (!$partner || !$customer) return;

            $custGreeting = $customer->name ? "Halo Kak {$customer->name}" : "Halo Kak";
            $notesText    = !empty($notes) ? " Keterangan: \"{$notes}\"." : "";

            if ($isConfirmed) {
                $message = "{$custGreeting}, saya menyetujui pengajuan penarikan/pembatalan untuk pesanan '{$help->title}'.{$notesText} Pesanan resmi dibatalkan dan proses refund telah diselesaikan.";
            } else {
                $message = "{$custGreeting}, saya mengajukan keberatan atas pengajuan penarikan pesanan '{$help->title}'.{$notesText} Kasus ini telah diteruskan ke Admin Wilayah SayaBantu untuk evaluasi dan mediasi.";
            }

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $partner->id,
                'customer_id' => $customer->id,
                'sender_id'   => $partner->id,
                'sender_type' => 'mitra',
                'message'     => $message,
                'read_at'     => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[HelpChatService] Failed to send partner response to withdraw chat: ' . $e->getMessage());
        }
    }

    /**
     * Kirim pesan resolusi Customer atas pembatalan Mitra (Menerima / Relist cari pengganti).
     */
    public function sendCustomerResolutionToPartnerCancelChat(
        Help $help,
        User $customer,
        User $mitra,
        string $action
    ): void {
        try {
            if (!$customer || !$mitra) return;

            $partnerName = $mitra->name ? "Halo Rekan Jasa {$mitra->name}" : "Halo Rekan Jasa";

            if ($action === 'accepted') {
                $message = "{$partnerName}, saya telah menyetujui pembatalan tugas dari Anda untuk pesanan '{$help->title}'. Pesanan resmi dibatalkan dan pengembalian dana telah diproses.";
            } elseif ($action === 'relisted') {
                $message = "{$partnerName}, saya telah menerima permohonan pembatalan dari Anda untuk pesanan '{$help->title}' dan memilih mencari Rekan Jasa pengganti. Tugas telah dialihkan kembali ke pencarian umum.";
            } else {
                $message = "{$partnerName}, permohonan pembatalan Anda untuk pesanan '{$help->title}' telah diproses oleh Customer.";
            }

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $mitra->id,
                'customer_id' => $customer->id,
                'sender_id'   => $customer->id,
                'sender_type' => 'customer',
                'message'     => $message,
                'read_at'     => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[HelpChatService] Failed to send customer resolution to partner cancel chat: ' . $e->getMessage());
        }
    }

    /**
     * Kirim pesan pembatalan jika terjadi Customer No-Show pada layanan antar-jemput.
     */
    public function sendNoShowCancellationChat(
        Help $help,
        User $mitra,
        User $customer
    ): void {
        try {
            if (!$mitra || !$customer) return;

            $message = "Sistem SayaBantu: Pesanan '{$help->title}' telah dibatalkan karena Customer No-Show (tidak hadir atau tidak merespons di titik penjemputan setelah masa tunggu berakhir). Seluruh ongkos antar telah diteruskan ke Rekan Jasa {$mitra->name}.";

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $mitra->id,
                'customer_id' => $customer->id,
                'sender_id'   => $mitra->id,
                'sender_type' => 'system',
                'message'     => $message,
                'read_at'     => null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[HelpChatService] Failed to send no-show cancellation chat: ' . $e->getMessage());
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
                'sender_id'   => $mitra->id,
                'sender_type' => 'mitra',
                'message'     => $message,
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
                    'sender_id'   => $mitra->id,
                    'sender_type' => 'system',
                    'message'     => $message,
                    'read_at'     => null,
                ]);
            } elseif ($action === 'accepted') {
                $message = "Sistem SayaBantu: Permintaan pembatalan untuk bantuan '{$help->title}' telah disetujui oleh Customer. Pesanan ini telah dikembalikan ke pencarian Rekan Jasa lain.";

                Chat::create([
                    'help_id'     => $help->id,
                    'mitra_id'    => $mitra->id,
                    'customer_id' => $customer->id,
                    'sender_id'   => $customer->id,
                    'sender_type' => 'system',
                    'message'     => $message,
                    'read_at'     => null,
                ]);
            } else {
                $message = "Halo Rekan Jasa {$mitra->name}, permintaan pembatalan Anda untuk bantuan '{$help->title}' ditolak oleh Customer. Mohon untuk melanjutkan pengerjaan bantuan ini.";

                Chat::create([
                    'help_id'     => $help->id,
                    'mitra_id'    => $mitra->id,
                    'customer_id' => $customer->id,
                    'sender_id'   => $customer->id,
                    'sender_type' => 'system',
                    'message'     => $message,
                    'read_at'     => null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('[HelpChatService] Failed to send cancellation resolved chat: ' . $e->getMessage());
        }
    }
}
