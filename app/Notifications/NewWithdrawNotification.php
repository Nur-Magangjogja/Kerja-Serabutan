<?php

namespace App\Notifications;

use App\Models\WithdrawRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewWithdrawNotification extends Notification
{
    use Queueable;

    public WithdrawRequest $withdraw;

    public function __construct(WithdrawRequest $withdraw)
    {
        $this->withdraw = $withdraw;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $userName = $this->withdraw->user?->name ?? 'Pengguna';
        $amountFormatted = number_format($this->withdraw->amount, 0, ',', '.');

        return [
            'type' => 'new_withdraw',
            'category' => 'withdraw',
            'withdraw_id' => $this->withdraw->id,
            'user_id' => $this->withdraw->user_id,
            'user_name' => $userName,
            'amount' => $this->withdraw->amount,
            'bank_code' => $this->withdraw->bank_code,
            'account_number' => $this->withdraw->account_number,
            'title' => 'Permintaan Penarikan Dana (Withdraw)',
            'message' => "{$userName} mengajukan penarikan dana Rp {$amountFormatted} ke {$this->withdraw->bank_code}.",
            'url' => route('admin.withdraws.index'),
            'icon' => '💸',
        ];
    }
}
