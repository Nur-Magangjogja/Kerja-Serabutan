<?php

namespace App\Notifications;

use App\Models\BalanceTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TopupCancelled extends Notification
{
    use Queueable;

    protected $transaction;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(BalanceTransaction $transaction, string $reason = '')
    {
        $this->transaction = $transaction;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Pembatalan Top-Up Saldo')
            ->greeting('Pemberitahuan, ' . $notifiable->name . '!')
            ->line('Persetujuan top-up saldo Anda sebelumnya telah dibatalkan oleh Superadmin.')
            ->line('Kode Request: ' . ($this->transaction->request_code ?? '#' . $this->transaction->id))
            ->line('Nominal: Rp ' . number_format($this->transaction->amount, 0, ',', '.'))
            ->line('Alasan Pembatalan: ' . ($this->reason ?: ($this->transaction->rejection_reason ?: 'Bukti pembayaran tidak valid / terindikasi penipuan.')))
            ->line('Saldo akun Anda telah disesuaikan kembali (dikurangi sesuai nominal top-up yang dibatalkan).')
            ->action('Cek Riwayat Saldo', route('customer.transactions.index'))
            ->line('Jika Anda merasa ini adalah kekeliruan, silakan hubungi admin atau customer service kami.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $reasonText = $this->reason ?: ($this->transaction->rejection_reason ?: 'Bukti pembayaran tidak valid / fiktif.');
        return [
            'type' => 'topup_cancelled',
            'transaction_id' => $this->transaction->id,
            'request_code' => $this->transaction->request_code ?? '#' . $this->transaction->id,
            'amount' => $this->transaction->amount,
            'rejection_reason' => $reasonText,
            'message' => 'Top-up saldo sebesar Rp ' . number_format($this->transaction->amount, 0, ',', '.') . ' telah dibatalkan oleh Superadmin. Alasan: ' . $reasonText,
        ];
    }
}
