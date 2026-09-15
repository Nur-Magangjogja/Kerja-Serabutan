<?php

namespace App\Notifications;

use App\Models\Help;
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

        $statusNorm = Help::normalizeStatus($this->newStatus);
        $targetTime = $this->help->getScheduledTargetTime()?->format('H:i') ?? 'sesuai jadwal';

        $title = match (strtolower($this->newStatus === 'scheduled_departure_due' ? 'scheduled_departure_due' : ($this->newStatus === 'near_arrival' ? 'near_arrival' : $statusNorm))) {
            'scheduled_departure_due'           => "⏰ Waktunya Berangkat (Tugas Terjadwal)",
            'near_arrival'                      => "🛵 Rekan Jasa Hampir Sampai",
            Help::STATUS_TAKEN                  => "Rekan Jasa Mengambil Pesanan",
            Help::STATUS_MENUNGGU_MITRA         => "Mencari Rekan Jasa Baru",
            Help::STATUS_PARTNER_ON_THE_WAY     => "Rekan Jasa Menuju Lokasi",
            Help::STATUS_PARTNER_ARRIVED        => "Rekan Jasa Telah Tiba",
            Help::STATUS_IN_PROGRESS            => "Pekerjaan Dimulai",
            Help::STATUS_WAITING_CONFIRMATION   => "Pekerjaan Selesai (Menunggu Konfirmasi)",
            Help::STATUS_SELESAI                => "Bantuan Selesai",
            Help::STATUS_PARTNER_CANCEL_REQUESTED => "Permintaan Pembatalan Rekan Jasa",
            Help::STATUS_CUSTOMER_CANCEL_REQUESTED => "Pengajuan Pembatalan Customer",
            'cancel_accepted'                   => "Pembatalan Diterima",
            'cancel_rejected'                   => "Pembatalan Ditolak",
            'customer_cancelled_during_matching', 'customer_cancelled' => "Pesanan Bantuan Dibatalkan Pemesan",
            Help::STATUS_DIBATALKAN             => "Bantuan Dibatalkan",
            default                             => "Pembaruan Status Bantuan"
        };

        $message = match (strtolower($this->newStatus === 'scheduled_departure_due' ? 'scheduled_departure_due' : ($this->newStatus === 'near_arrival' ? 'near_arrival' : $statusNorm))) {
            'scheduled_departure_due'           => "Tugas terjadwal '{$this->help->title}' dijadwalkan pada pukul {$targetTime}. Tombol keberangkatan sudah aktif, harap segera bersiap dan berangkat menuju lokasi.",
            'near_arrival'                      => "Rekan Jasa $mitraName sudah hampir sampai di lokasi Anda (jarak < 250 meter). Silakan bersiap menyambut rekan jasa.",
            Help::STATUS_TAKEN                  => "Rekan Jasa $mitraName telah mengambil pesanan bantuan Anda '{$this->help->title}'. Silakan pantau perkembangannya.",
            Help::STATUS_MENUNGGU_MITRA         => "Pesanan Anda '{$this->help->title}' kembali tersedia dan sedang mencari Rekan Jasa baru.",
            Help::STATUS_PARTNER_ON_THE_WAY     => "Rekan Jasa $mitraName sedang dalam perjalanan menuju lokasi Anda.",
            Help::STATUS_PARTNER_ARRIVED        => "Rekan Jasa $mitraName telah tiba di lokasi Anda.",
            Help::STATUS_IN_PROGRESS            => "Rekan Jasa $mitraName telah mulai mengerjakan bantuan '{$this->help->title}'.",
            Help::STATUS_WAITING_CONFIRMATION   => "Rekan Jasa $mitraName telah menyelesaikan pekerjaan '{$this->help->title}'. Mohon periksa hasil pengerjaan dan konfirmasi penyelesaian.",
            Help::STATUS_SELESAI                => "Bantuan '{$this->help->title}' telah selesai dikerjakan oleh $mitraName.",
            Help::STATUS_PARTNER_CANCEL_REQUESTED => "$mitraName mengajukan permintaan kendala/pembatalan. Admin dan sistem akan meninjau proses ini.",
            Help::STATUS_CUSTOMER_CANCEL_REQUESTED => "Pengajuan pembatalan untuk pesanan '{$this->help->title}' sedang ditinjau oleh Admin.",
            'cancel_accepted'                   => "Permintaan pembatalan telah diterima. Kami sedang mencari Rekan Jasa lain untuk Anda.",
            'cancel_rejected'                   => "Permintaan pembatalan ditolak. Pekerjaan akan tetap dilanjutkan.",
            'customer_cancelled_during_matching', 'customer_cancelled' => "Permintaan bantuan '{$this->help->title}' telah dibatalkan oleh pemesan saat proses pencarian. Radar Anda otomatis kembali mencari order lain.",
            Help::STATUS_DIBATALKAN             => "Permintaan bantuan '{$this->help->title}' telah dibatalkan.",
            default                             => "Status bantuan '{$this->help->title}' kini menjadi: {$this->newStatus}."
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
