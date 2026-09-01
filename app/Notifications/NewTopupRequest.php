<?php

namespace App\Notifications;

use App\Models\BalanceTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTopupRequest extends Notification
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
            ->subject('🔔 Request Top-Up Saldo Baru')
            ->greeting('Halo Admin,')
            ->line('Ada request top-up saldo baru yang menunggu persetujuan Anda.')
            ->line('Customer: ' . $this->transaction->user->name)
            ->line('Kode Request: ' . $this->transaction->request_code)
            ->line('Nominal: Rp ' . number_format($this->transaction->amount, 0, ',', '.'))
            ->line('Total Pembayaran: Rp ' . number_format($this->transaction->total_payment, 0, ',', '.'))
            ->action('Review Request', route('admin.topup.approvals'))
            ->line('Silakan verifikasi bukti transfer dan approve/reject request tersebut.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_topup_request',
            'transaction_id' => $this->transaction->id,
            'request_code' => $this->transaction->request_code,
            'customer_name' => $this->transaction->user->name,
            'customer_id' => $this->transaction->user_id,
            'amount' => $this->transaction->amount,
            'total_payment' => $this->transaction->total_payment,
            'message' => 'Request top-up baru dari ' . $this->transaction->user->name . ' sebesar Rp ' . number_format($this->transaction->amount, 0, ',', '.'),
        ];
    }
}
