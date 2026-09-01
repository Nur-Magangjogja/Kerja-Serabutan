<?php

namespace App\Notifications;

use App\Models\BalanceTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TopupRejected extends Notification
{
    use Queueable;

    protected $transaction;

    /**
     * Create a new notification instance.
     */
    public function __construct(BalanceTransaction $transaction)
    {
        $this->transaction = $transaction;
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
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Request Top-Up Saldo Ditolak')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Mohon maaf, request top-up saldo Anda telah ditolak oleh admin.')
            ->line('Kode Request: ' . $this->transaction->request_code)
            ->line('Nominal: Rp ' . number_format($this->transaction->amount, 0, ',', '.'))
            ->line('Alasan Penolakan: ' . $this->transaction->rejection_reason)
            ->line('Silakan periksa kembali bukti transfer Anda dan ajukan request baru.')
            ->action('Buat Request Baru', route('customer.topup.request'))
            ->line('Jika ada pertanyaan, silakan hubungi customer service kami.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'topup_rejected',
            'transaction_id' => $this->transaction->id,
            'request_code' => $this->transaction->request_code,
            'amount' => $this->transaction->amount,
            'rejection_reason' => $this->transaction->rejection_reason,
            'message' => 'Request top-up saldo Anda ditolak. Alasan: ' . $this->transaction->rejection_reason,
        ];
    }
}
