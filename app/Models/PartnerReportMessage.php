<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerReportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'partner_report_id',
        'sender_id',
        'recipient_type',
        'message',
        'photo',
        'is_read',
        'read_at',
        'customer_read_at',
        'mitra_read_at',
    ];

    protected $casts = [
        'is_read'          => 'boolean',
        'read_at'          => 'datetime',
        'customer_read_at' => 'datetime',
        'mitra_read_at'    => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    // Relationships
    public function report(): BelongsTo
    {
        return $this->belongsTo(PartnerReport::class, 'partner_report_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function isFromAdmin(): bool
    {
        return in_array($this->sender?->role ?? '', ['admin', 'super_admin', 'superadmin']);
    }

    public function isFromCustomer(): bool
    {
        return $this->sender?->role === 'customer';
    }

    public function isFromMitra(): bool
    {
        return $this->sender?->role === 'mitra';
    }

    protected static function booted()
    {
        static::created(function (PartnerReportMessage $message) {
            try {
                // Jangan kirim notifikasi ke admin jika pesan dikirim oleh admin sendiri
                if ($message->isFromAdmin()) {
                    return;
                }

                $report = $message->report;
                if (!$report) {
                    return;
                }

                // KONDISI UTAMA: Hanya kirim notifikasi jika laporan masih AKTIF / PENDING
                // Jika laporan sudah selesai (resolved) atau ditutup (dismissed), notifikasi ditekan / dihentikan.
                if ($report->isActive()) {
                    $help = $report->reportedHelp ?? $report->help;
                    if ($help) {
                        // JOB_NOTIFICATION: Help territory wins! No broadcast fallback!
                        $recipients = app(\App\Services\HelpNotificationService::class)->resolveAdminsForHelp($help, includeSuperAdmin: true);
                    } else {
                        // ACCOUNT / REPORT NOTIFICATION: route by canonical resource territory (G8)
                        $canonical = $report->getCanonicalTerritory();
                        $districtId = $canonical['district_id'];
                        $cityId = $canonical['city_id'];

                        $recipients = app(\App\Services\AccountNotificationService::class)->resolveAdminsForTerritory(
                            $districtId ? (int) $districtId : null,
                            $cityId ? (int) $cityId : null,
                            includeSuperAdmin: true
                        );
                    }

                    foreach ($recipients as $recipient) {
                        $recipient->notify(new \App\Notifications\NewReportMessageNotification($message));
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[PartnerReportMessage] Gagal mengirim notifikasi chat aduan: ' . $e->getMessage());
            }
        });
    }
}
