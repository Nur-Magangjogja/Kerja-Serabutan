<?php

namespace App\Notifications;

use App\Models\BalanceTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TopupRequestSubmitted extends Notification
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
            ->subject('Request Top-Up Saldo Diterima')
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Request top-up saldo Anda telah berhasil diterima.')
            ->line('Kode Request: ' . $this->transaction->request_code)
            ->line('Nominal: Rp ' . number_format($this->transaction->amount, 0, ',', '.'))
            ->line('Total Pembayaran: Rp ' . number_format($this->transaction->total_payment, 0, ',', '.'))
            ->line('Request Anda akan diverifikasi oleh admin maksimal 1x24 jam.')
            ->action('Lihat Status', route('customer.transactions.index'))
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
            'type' => 'topup_request_submitted',
            'transaction_id' => $this->transaction->id,
            'request_code' => $this->transaction->request_code,
            'amount' => $this->transaction->amount,
            'total_payment' => $this->transaction->total_payment,
            'message' => 'Request top-up saldo Anda telah diterima dan menunggu verifikasi admin.',
        ];
    }
}
