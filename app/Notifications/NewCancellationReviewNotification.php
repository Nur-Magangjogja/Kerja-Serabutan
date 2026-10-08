<?php

namespace App\Notifications;

use App\Models\Help;
use App\Models\HelpCancelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCancellationReviewNotification extends Notification
{
    use Queueable;

    public Help $help;
    public ?HelpCancelRequest $cancelRequest;
    public string $requesterName;
    public string $reason;

    public function __construct(Help $help, ?HelpCancelRequest $cancelRequest = null, ?string $requesterName = null, ?string $reason = null)
    {
        $this->help = $help;
        $this->cancelRequest = $cancelRequest;
        $this->requesterName = $requesterName ?? ($cancelRequest?->partner?->name ?? $help->mitra?->name ?? 'Pengguna');
        $this->reason = $reason ?? ($cancelRequest?->reason ?? 'Kendala tugas');
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'              => 'cancellation_review',
            'category'          => 'cancellation',
            'help_id'           => $this->help->id,
            'cancel_request_id' => $this->cancelRequest?->id,
            'requester_name'    => $this->requesterName,
            'title'             => 'Tinjauan Pembatalan: ' . ($this->help->title ?? 'Tugas Bantuan'),
            'message'           => "{$this->requesterName} mengajukan pembatalan pesanan. Alasan: \"{$this->reason}\". Memerlukan tinjauan Admin Wilayah.",
            'url'               => route('admin.cancellations.index'),
            'icon'              => 'shield-exclamation',
        ];
    }
}
