<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Help;
use App\Models\User;

class HelpTakenNotification extends Notification
{
    use Queueable;

    public $help;
    public $mitra;

    /**
     * Create a new notification instance.
     */
    public function __construct(Help $help, User $mitra)
    {
        $this->help = $help;
        $this->mitra = $mitra;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'help_id' => $this->help->id,
            'help_title' => $this->help->title,
            'help_amount' => $this->help->amount,
            'mitra_id' => $this->mitra->id,
            'mitra_name' => $this->mitra->name,
            'message' => "Bantuan Anda '{$this->help->title}' senilai Rp " . number_format((float) $this->help->amount, 0, ',', '.') . " telah diambil oleh {$this->mitra->name}",
            'type' => 'help_taken',
        ];
    }
}
