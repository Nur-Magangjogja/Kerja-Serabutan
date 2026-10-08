<?php

namespace App\Notifications;

use App\Models\PartnerReportMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewReportMessageNotification extends Notification
{
    use Queueable;

    public PartnerReportMessage $reportMessage;

    public function __construct(PartnerReportMessage $reportMessage)
    {
        $this->reportMessage = $reportMessage;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $senderName = $this->reportMessage->sender?->name ?? 'Pengguna';
        $report = $this->reportMessage->report;
        $reportId = $report?->id ?? '-';
        $isSupport = ($report?->report_type === 'dukungan_umum');
        $preview = Str::limit($this->reportMessage->message ?? ($this->reportMessage->photo ? '[Lampiran Foto]' : 'Pesan baru'), 80);

        if ($isSupport) {
            return [
                'type'          => 'new_support_message',
                'category'      => 'support',
                'report_id'     => $reportId,
                'message_id'    => $this->reportMessage->id,
                'sender_id'     => $this->reportMessage->sender_id,
                'sender_name'   => $senderName,
                'title'         => "Pesan Bantuan Admin: {$senderName}",
                'message'       => "{$senderName} mengirim pesan bantuan: \"{$preview}\"",
                'url'           => route('admin.support.chat', $reportId),
                'icon'          => 'chat-bubble-left-right',
            ];
        }

        return [
            'type'          => 'new_report_message',
            'category'      => 'report',
            'report_id'     => $reportId,
            'message_id'    => $this->reportMessage->id,
            'sender_id'     => $this->reportMessage->sender_id,
            'sender_name'   => $senderName,
            'title'         => "Pesan Baru Aduan: {$senderName}",
            'message'       => "{$senderName} mengirim pesan pada aduan: \"{$preview}\"",
            'url'           => route('admin.partners.reports.chat', $reportId),
            'icon'          => 'megaphone',
        ];
    }
}
