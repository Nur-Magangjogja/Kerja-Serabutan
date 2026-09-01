<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PartnerReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'reported_help_id',
        'reported_help_text',
        'reported_user_text',
        'title',
        'message',
        'evidence_photo',
        'status',
        'refund_status',
        'refund_amount',
        'refund_processed_at',
        'refund_processed_by',
        'report_type',
        'category',
        'admin_notes',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'resolved_at'         => 'datetime',
        'refund_processed_at' => 'datetime',
        'refund_amount'       => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('active_reports_count_superadmin');
            \Illuminate\Support\Facades\Cache::increment('active_reports_count_version');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('active_reports_count_superadmin');
            \Illuminate\Support\Facades\Cache::increment('active_reports_count_version');
        });
    }

    /**
     * Menghitung jumlah laporan aduan aktif (masuk / sedang diproses)
     * dengan optimasi query dan cache versi otomatis.
     */
    public static function getActiveReportsCountForUser(?User $user = null): int
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return 0;
        }

        $isSuperAdmin = in_array($user->role ?? '', ['super_admin', 'superadmin']);
        $version = \Illuminate\Support\Facades\Cache::get('active_reports_count_version', 1);
        $cacheKey = 'active_reports_count_v' . $version . '_' . ($isSuperAdmin ? 'sa' : 'admin_' . $user->id . '_' . ($user->city_id ?? 'all'));

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 15, function () use ($user, $isSuperAdmin) {
            $query = static::whereIn('status', ['pending', 'in_progress', 'investigating']);

            if (!$isSuperAdmin) {
                $cityIds = $user->getAdminCityIds();

                if (!empty($cityIds)) {
                    $query->where(function ($q) use ($cityIds) {
                        $q->whereHas('reporter', fn($sq) => $sq->whereIn('city_id', $cityIds))
                          ->orWhereHas('reportedUser', fn($sq) => $sq->whereIn('city_id', $cityIds));
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            }

            return (int) $query->count();
        });
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reportedHelp()
    {
        return $this->belongsTo(Help::class, 'reported_help_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function refundProcessedBy()
    {
        return $this->belongsTo(User::class, 'refund_processed_by');
    }

    public function messages()
    {
        return $this->hasMany(PartnerReportMessage::class, 'partner_report_id')->orderBy('created_at', 'asc');
    }

    // Scopes
    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress(Builder $query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved(Builder $query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeDismissed(Builder $query)
    {
        return $query->where('status', 'dismissed');
    }

    public function scopeFromCustomer(Builder $query)
    {
        return $query->where('category', 'dari_customer');
    }

    public function scopeFromMitra(Builder $query)
    {
        return $query->where('category', 'dari_mitra');
    }

    public function scopeByReportType(Builder $query, string $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeByStatus(Builder $query, string $status)
    {
        return $query->where('status', $status);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    public function isDismissed()
    {
        return $this->status === 'dismissed';
    }

    public function isFromCustomer()
    {
        return $this->category === 'dari_customer';
    }

    public function isFromMitra()
    {
        return $this->category === 'dari_mitra';
    }

    public function isRefundRequested(): bool
    {
        return $this->refund_status === 'requested';
    }

    public function isRefundApproved(): bool
    {
        return $this->refund_status === 'approved';
    }

    public function isRefundRejected(): bool
    {
        return $this->refund_status === 'rejected';
    }

    // Get report type label
    public function getReportTypeLabelAttribute()
    {
        $types = [
            'klaim_refund_pekerjaan_fiktif' => 'Klaim Refund (Pekerjaan Fiktif / Belum Selesai)',
            'mitra_tidak_selesai'           => 'Rekan Jasa Belum Menyelesaikan Pekerjaan',
            'mitra_berperilaku_buruk'       => 'Mitra Berperilaku Buruk',
            'bantuan_fiktif'                => 'Bantuan Fiktif',
            'penipuan'                      => 'Penipuan / Manipulasi',
            'pelanggaran_aturan'            => 'Pelanggaran Aturan Layanan',
            'konten_tidak_pantas'           => 'Konten Tidak Pantas',
            'pelayanan_tidak_sesuai'        => 'Pelayanan Tidak Sesuai',
            'pengguna_spam'                 => 'Pengguna Spam',
            'pengguna_kasar'                => 'Pengguna Kasar',
            'data_tidak_valid'              => 'Data Tidak Valid',
        ];

        return $types[$this->report_type] ?? ucfirst(str_replace('_', ' ', $this->report_type));
    }

    // Get refund status label
    public function getRefundStatusLabelAttribute(): string
    {
        return match($this->refund_status) {
            'requested' => 'Pengajuan Refund',
            'approved'  => 'Refund Disetujui',
            'rejected'  => 'Refund Ditolak',
            default     => 'Tidak Ada Refund',
        };
    }

    // Get category label
    public function getCategoryLabelAttribute()
    {
        return $this->category === 'dari_customer' ? 'Dari Customer' : 'Dari Mitra';
    }
}
