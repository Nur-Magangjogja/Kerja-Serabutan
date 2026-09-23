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
        $reportTitle = $report?->title ?? 'Laporan Aduan';
        $preview = Str::limit($this->reportMessage->message ?? ($this->reportMessage->photo ? '[Lampiran Foto]' : 'Pesan baru'), 80);

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
            'icon'          => '📢',
        ];
    }
}
