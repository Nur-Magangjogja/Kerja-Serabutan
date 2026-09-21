<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VehicleVerificationNotification extends Notification
{
    use Queueable;

    protected string $status;
    protected ?string $reason;
    protected ?string $plateNumber;

    public function __construct(string $status, ?string $reason = null, ?string $plateNumber = null)
    {
        $this->status = $status;
        $this->reason = $reason;
        $this->plateNumber = $plateNumber;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        if ($this->status === 'verified') {
            return [
                'type'             => 'vehicle_verification',
                'status'           => 'verified',
                'title'            => 'Data Kendaraan Terverifikasi ✅',
                'message'          => "Data kendaraan Anda ({$this->plateNumber}) telah diverifikasi oleh Admin. Anda kini dapat mengambil pekerjaan jenis Antar & Jemput!",
                'action_url'       => route('mitra.profile'),
                'icon'             => 'shield-check',
            ];
        }

        return [
            'type'             => 'vehicle_verification',
            'status'           => 'rejected',
            'title'            => 'Verifikasi Data Kendaraan Ditolak ⚠️',
            'message'          => "Verifikasi kendaraan Anda ({$this->plateNumber}) ditolak oleh Admin. Alasan: " . ($this->reason ?? 'Dokumen tidak valid') . ". Silakan perbarui dokumen pada profil Anda.",
            'action_url'       => route('mitra.profile'),
            'icon'             => 'x-circle',
            'rejection_reason' => $this->reason,
        ];
    }
}
