<?php

namespace App\Notifications;

use App\Models\BalanceTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TopupApproved extends Notification
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
            ->subject('✅ Request Top-Up Saldo Disetujui')
            ->greeting('Kabar Baik, ' . $notifiable->name . '!')
            ->line('Request top-up saldo Anda telah disetujui oleh admin.')
            ->line('Kode Request: ' . $this->transaction->request_code)
            ->line('Nominal: Rp ' . number_format($this->transaction->amount, 0, ',', '.'))
            ->line('Saldo Anda telah bertambah dan siap digunakan.')
            ->action('Lihat Saldo', route('customer.dashboard'))
            ->line('Terima kasih telah menggunakan layanan kami!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'topup_approved',
            'transaction_id' => $this->transaction->id,
            'request_code' => $this->transaction->request_code,
            'amount' => $this->transaction->amount,
            'message' => 'Request top-up saldo Anda telah disetujui! Saldo Rp ' . number_format($this->transaction->amount, 0, ',', '.') . ' telah ditambahkan.',
        ];
    }
}
