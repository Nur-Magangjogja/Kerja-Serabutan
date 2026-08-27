<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewKtpVerificationNotification extends Notification
{
    use Queueable;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $roleLabel = ucfirst($this->user->role ?? 'Pengguna');
        $cityName = $this->user->city_name ?: 'Wilayah Tidak Diketahui';

        return [
            'type' => 'new_ktp_verification',
            'category' => 'ktp',
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'user_role' => $this->user->role,
            'city' => $cityName,
            'title' => 'Verifikasi KTP Akun Baru: ' . $this->user->name,
            'message' => "Akun baru ({$roleLabel} - {$cityName}) telah mendaftar dan menunggu verifikasi berkas KTP.",
            'url' => route('admin.verifications'),
            'icon' => '🪪',
        ];
    }
}
