<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\WithdrawRequest;

class WithdrawStatusNotification extends Notification
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
        $status = $this->withdraw->status;
        $message = $status === WithdrawRequest::STATUS_SUCCESS
            ? 'Penarikan Anda berhasil diproses.'
            : ($status === WithdrawRequest::STATUS_FAILED ? 'Penarikan Anda dibatalkan / gagal.' : 'Status penarikan diperbarui.');

        $url = $status === WithdrawRequest::STATUS_FAILED
            ? route('mitra.withdraw.rejected', $this->withdraw->id)
            : route('mitra.withdraw.success', $this->withdraw->id);

        return [
            'withdraw_id' => $this->withdraw->id,
            'amount' => $this->withdraw->amount,
            'status' => $status,
            'message' => $message,
            'url' => $url,
            'type' => 'withdraw_status',
        ];
    }
}
