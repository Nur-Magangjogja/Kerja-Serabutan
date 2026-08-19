<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewHelpAvailableNotification extends Notification
{
    use Queueable;

    protected $help;

    public function __construct($help)
    {
        $this->help = $help;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $customer = $this->help->user ?? null;
        $customerName = $customer?->name ?? 'Customer';
        $amountFormatted = 'Rp ' . number_format($this->help->amount ?? 0, 0, ',', '.');
        $cityName = $this->help->city?->name ?? 'Terdekat';

        return [
            'type' => 'new_help_available',
            'title' => 'Bantuan Baru Tersedia!',
            'help_id' => $this->help->id ?? null,
            'help_title' => $this->help->title ?? null,
            'help_amount' => $this->help->amount ?? null,
            'customer_name' => $customerName,
            'city_name' => $cityName,
            'message' => "Ada permintaan bantuan baru: '{$this->help->title}' ({$amountFormatted}) di area {$cityName}. Ambil sekarang!",
            'body' => "Ada permintaan bantuan baru: '{$this->help->title}' ({$amountFormatted}) di area {$cityName}. Ambil sekarang!",
        ];
    }
}
