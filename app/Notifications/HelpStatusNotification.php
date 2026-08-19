<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class HelpStatusNotification extends Notification
{
    use Queueable;

    protected $help;
    protected $oldStatus;
    protected $newStatus;
    protected $mitra;

    public function __construct($help, $oldStatus = null, $newStatus = null, $mitra = null)
    {
        $this->help = $help;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus ?? $help->status;
        $this->mitra = $mitra ?? $help->mitra;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $mitraName = $this->mitra?->name ?? 'Mitra';

        $title = match (strtolower($this->newStatus)) {
            'taken', 'memperoleh_mitra'     => "Rekan Jasa Mengambil Pesanan",
            'menunggu_mitra'                => "Mencari Rekan Jasa Baru",
            'partner_on_the_way'            => "Rekan Jasa Menuju Lokasi",
            'partner_arrived'               => "Rekan Jasa Telah Tiba",
            'in_progress', 'sedang_diproses' => "Pekerjaan Dimulai",
            'waiting_customer_confirmation' => "Pekerjaan Selesai (Menunggu Konfirmasi)",
            'completed', 'selesai'          => "Bantuan Selesai",
            'partner_cancel_requested'      => "Permintaan Pembatalan Rekan Jasa",
            'cancel_accepted'               => "Pembatalan Diterima",
            'cancel_rejected'               => "Pembatalan Ditolak",
            default                         => "Pembaruan Status Bantuan"
        };

        $message = match (strtolower($this->newStatus)) {
            'taken', 'memperoleh_mitra'     => "Rekan Jasa $mitraName telah mengambil pesanan bantuan Anda '{$this->help->title}'. Silakan pantau perkembangannya.",
            'menunggu_mitra'                => "Pesanan Anda '{$this->help->title}' kembali tersedia dan sedang mencari Rekan Jasa baru.",
            'partner_on_the_way'            => "Rekan Jasa $mitraName sedang dalam perjalanan menuju lokasi Anda.",
            'partner_arrived'               => "Rekan Jasa $mitraName telah tiba di lokasi Anda.",
            'in_progress', 'sedang_diproses' => "Rekan Jasa $mitraName telah mulai mengerjakan bantuan '{$this->help->title}'.",
            'waiting_customer_confirmation' => "Rekan Jasa $mitraName telah menyelesaikan pekerjaan '{$this->help->title}'. Mohon periksa hasil pengerjaan dan konfirmasi penyelesaian.",
            'completed', 'selesai'          => "Bantuan '{$this->help->title}' telah selesai dikerjakan oleh $mitraName.",
            'partner_cancel_requested'      => "$mitraName mengajukan permintaan pembatalan. Silakan tinjau dan berikan keputusan Anda.",
            'cancel_accepted'               => "Permintaan pembatalan telah diterima. Kami sedang mencari Rekan Jasa lain untuk Anda.",
            'cancel_rejected'               => "Permintaan pembatalan ditolak. Pekerjaan akan tetap dilanjutkan.",
            default                         => "Status bantuan '{$this->help->title}' kini menjadi: {$this->newStatus}."
        };

        return [
            'type' => 'help_status',
            'title' => $title,
            'help_id' => $this->help->id ?? null,
            'help_title' => $this->help->title ?? null,
            'help_amount' => $this->help->amount ?? null,
            'mitra_id' => $this->mitra?->id ?? null,
            'mitra_name' => $mitraName,
            'from_name' => $mitraName,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => $message,
            'body' => $message,
        ];
    }
}
