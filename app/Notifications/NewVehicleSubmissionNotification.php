<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewVehicleSubmissionNotification extends Notification
{
    use Queueable;

    public User $user;
    public ?string $plateNumber;

    public function __construct(User $user, ?string $plateNumber = null)
    {
        $this->user = $user;
        $this->plateNumber = $plateNumber ?? $user->vehicle_plate_number;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $cityName = $this->user->city_name ?: 'Wilayah';
        $plate = $this->plateNumber ?: 'Kendaraan';

        return [
            'type'       => 'new_vehicle_verification',
            'category'   => 'kendaraan',
            'user_id'    => $this->user->id,
            'user_name'  => $this->user->name,
            'user_role'  => $this->user->role,
            'city'       => $cityName,
            'title'      => 'Pengajuan Data Kendaraan: ' . $this->user->name,
            'message'    => "Mitra {$this->user->name} ({$cityName}) mengajukan verifikasi data kendaraan (Plat: {$plate}).",
            'url'        => route('admin.verifications'),
            'icon'       => 'truck',
        ];
    }
}
