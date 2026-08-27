<?php

namespace App\Notifications;

use App\Models\PartnerReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReportNotification extends Notification
{
    use Queueable;

    public PartnerReport $report;

    public function __construct(PartnerReport $report)
    {
        $this->report = $report;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $reporterName = $this->report->reporter?->name ?? 'Pengguna';
        $reportType = ucwords(str_replace('_', ' ', $this->report->report_type ?? 'aduan'));

        return [
            'type' => 'new_report',
            'category' => 'report',
            'report_id' => $this->report->id,
            'reporter_name' => $reporterName,
            'report_type' => $reportType,
            'title' => 'Laporan Aduan Baru #' . $this->report->id,
            'message' => $reporterName . " mengirim laporan: '{$this->report->title}' ({$reportType})",
            'url' => route('admin.partners.reports.show', $this->report->id),
            'icon' => '📢',
        ];
    }
}
