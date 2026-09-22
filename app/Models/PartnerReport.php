<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PartnerReport extends Model
{
    use HasFactory;

    // Status Constants
    public const STATUS_PENDING        = 'pending';
    public const STATUS_IN_PROGRESS    = 'in_progress';
    public const STATUS_INVESTIGATING  = 'investigating';
    public const STATUS_UNDER_REVIEW   = 'under_review';
    public const STATUS_PROSES         = 'proses';
    public const STATUS_RESOLVED       = 'resolved';
    public const STATUS_DISMISSED      = 'dismissed';
    public const STATUS_CLOSED         = 'closed';

    public const ACTIVE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_INVESTIGATING,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_PROSES,
    ];

    public const TERMINAL_STATUSES = [
        self::STATUS_RESOLVED,
        self::STATUS_DISMISSED,
        self::STATUS_CLOSED,
        'rejected',
        'ditolak',
        'selesai',
    ];

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

    /**
     * In-request static memoization cache for badge counters.
     */
    protected static array $memoizedActiveReportsCounts = [];

    protected static function booted()
    {
        static::saved(function () {
            static::$memoizedActiveReportsCounts = [];
            \Illuminate\Support\Facades\Cache::forget('active_reports_count_superadmin');
            \Illuminate\Support\Facades\Cache::increment('active_reports_count_version');
        });

        static::deleted(function () {
            static::$memoizedActiveReportsCounts = [];
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
        $saTerritory = $isSuperAdmin ? $user->getActiveSuperadminTerritory() : null;
        $saKeyPart = $isSuperAdmin ? ('sa_' . $saTerritory['type'] . '_' . ($saTerritory['id'] ?? 'all')) : ('admin_' . $user->id . '_' . ($user->getActiveAdminDistrictFilter() ?? 'all'));

        $memoKey = $user->id . '_' . $saKeyPart;
        if (array_key_exists($memoKey, static::$memoizedActiveReportsCounts)) {
            return static::$memoizedActiveReportsCounts[$memoKey];
        }

        $version = \Illuminate\Support\Facades\Cache::get('active_reports_count_version', 1);
        $cacheKey = 'active_reports_count_v' . $version . '_' . $saKeyPart;

        return static::$memoizedActiveReportsCounts[$memoKey] = (int) \Illuminate\Support\Facades\Cache::remember($cacheKey, 180, function () use ($user, $isSuperAdmin, $saTerritory) {
            $query = static::whereIn('status', ['pending', 'in_progress', 'investigating']);

            if (!$isSuperAdmin) {
                $districtIds = $user->getEffectiveAdminDistrictIds();

                if (!empty($districtIds)) {
                    $query->where(function ($q) use ($districtIds) {
                        $q->whereHas('reporter', fn($sq) => $sq->whereIn('district_id', $districtIds))
                          ->orWhereHas('reportedUser', fn($sq) => $sq->whereIn('district_id', $districtIds))
                          ->orWhereHas('reportedHelp', fn($sq) => $sq->whereIn('district_id', $districtIds));
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                if ($saTerritory && $saTerritory['type'] === 'district' && !empty($saTerritory['id'])) {
                    $dId = (int) $saTerritory['id'];
                    $query->where(function ($q) use ($dId) {
                        $q->whereHas('reporter', fn($sq) => $sq->where('district_id', $dId))
                          ->orWhereHas('reportedUser', fn($sq) => $sq->where('district_id', $dId))
                          ->orWhereHas('reportedHelp', fn($sq) => $sq->where('district_id', $dId));
                    });
                } elseif ($saTerritory && $saTerritory['type'] === 'city' && !empty($saTerritory['id'])) {
                    $cId = (int) $saTerritory['id'];
                    $saDistrictIds = $user->getEffectiveSuperadminDistrictIds();
                    $query->where(function ($q) use ($cId, $saDistrictIds) {
                        $q->whereHas('reporter', function ($sq) use ($cId, $saDistrictIds) {
                            if (!empty($saDistrictIds)) $sq->whereIn('district_id', $saDistrictIds);
                            if ($cId) $sq->orWhere('city_id', $cId);
                        })->orWhereHas('reportedUser', function ($sq) use ($cId, $saDistrictIds) {
                            if (!empty($saDistrictIds)) $sq->whereIn('district_id', $saDistrictIds);
                            if ($cId) $sq->orWhere('city_id', $cId);
                        })->orWhereHas('reportedHelp', function ($sq) use ($cId, $saDistrictIds) {
                            if (!empty($saDistrictIds)) $sq->whereIn('district_id', $saDistrictIds);
                            if ($cId) $sq->orWhere('city_id', $cId);
                        });
                    });
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
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    public function isDismissed(): bool
    {
        return $this->status === self::STATUS_DISMISSED;
    }

    public function isResolvedOrClosed(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES, true);
    }

    /**
     * Cek apakah ada laporan aduan aktif (pending/in progress) untuk tugas tertentu.
     */
    public static function hasActiveReportForHelp(int $helpId, ?int $reporterId = null): bool
    {
        return static::where('reported_help_id', $helpId)
            ->when($reporterId, fn($q) => $q->where('reporter_id', $reporterId))
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->exists();
    }

    /**
     * Ambil laporan aduan aktif terakhir untuk tugas tertentu.
     */
    public static function getActiveReportForHelp(int $helpId, ?int $reporterId = null): ?self
    {
        return static::where('reported_help_id', $helpId)
            ->when($reporterId, fn($q) => $q->where('reporter_id', $reporterId))
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->latest()
            ->first();
    }

    /**
     * Ambil laporan terakhir (termasuk yang sudah resolved/closed) untuk tugas tertentu.
     */
    public static function getLatestReportForHelp(int $helpId, ?int $reporterId = null): ?self
    {
        return static::where('reported_help_id', $helpId)
            ->when($reporterId, fn($q) => $q->where('reporter_id', $reporterId))
            ->latest()
            ->first();
    }

    /**
     * Ambil laporan terakhir yang sudah diselesaikan (resolved/closed) untuk tugas tertentu.
     */
    public static function getLatestResolvedReportForHelp(int $helpId, ?int $reporterId = null): ?self
    {
        return static::where('reported_help_id', $helpId)
            ->when($reporterId, fn($q) => $q->where('reporter_id', $reporterId))
            ->whereIn('status', self::TERMINAL_STATUSES)
            ->latest()
            ->first();
    }

    public function isFromCustomer(): bool
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

    /**
     * Membersihkan string judul dari prefix sistem seperti "Klarifikasi Tugas #168:",
     * "Sengketa Bantuan #168:", "Klaim Garansi: Bantuan #168 -", "Klarifikasi /", atau "#168:".
     */
    public function cleanTitleString(?string $str): string
    {
        if (!$str) return '';

        // Hapus prefix pola "Klarifikasi Tugas #168:", "Sengketa Bantuan #168:", "Tugas #168:", "Bantuan #168 -"
        $cleaned = preg_replace('/^(?:Klarifikasi\s+(?:Tugas|Bantuan)?|Sengketa\s+Bantuan|Tugas|Bantuan)\s*#\d+\s*[:\-–—]?\s*/iu', '', $str);

        // Hapus "Klaim Garansi ...: Bantuan #168 - "
        $cleaned = preg_replace('/^Klaim\s+Garansi[^:]*:\s*Bantuan\s*#\d+\s*[:\-–—]?\s*/iu', 'Klaim Garansi: ', $cleaned);

        // Hapus leading "Klarifikasi / " atau "Klarifikasi "
        $cleaned = preg_replace('/^Klarifikasi\s*(?:\/|\-)?\s*/iu', '', $cleaned);

        // Hapus prefix ID seperti "#123: " atau "#123 - "
        $cleaned = preg_replace('/^#\d+\s*[:\-–—]?\s*/iu', '', $cleaned);

        return trim($cleaned);
    }

    /**
     * Judul tampilan yang informatif dan bersih untuk ruang obrolan / daftar laporan
     * tanpa kata 'Klarifikasi Tugas #' atau ID '#' yang mengganggu.
     */
    public function getDisplayTitleAttribute(): string
    {
        $rawTitle = $this->title ?? '';
        $cleanedRaw = $this->cleanTitleString($rawTitle);

        // Jika judul asli bertipe auto-generated ("Klarifikasi Tugas #...", kosong, atau angka saja),
        // cek apakah di kronologi / pesan terdapat nama tugas spesifik
        if ($cleanedRaw === '' || is_numeric($cleanedRaw) || str_starts_with(strtolower($rawTitle), 'klarifikasi tugas #')) {
            if (!empty($this->message) && preg_match('/(?:untuk tugas|pada tugas|tugas)\s*[:\-–—]\s*["\']?([^"\'\n\r]+)["\']?/iu', $this->message, $matches)) {
                $extracted = trim($matches[1]);
                if (!empty($extracted) && !is_numeric($extracted)) {
                    return $this->cleanTitleString($extracted);
                }
            }
        }

        // Jika judul setelah dibersihkan merupakan teks deskriptif valid (bukan angka murni)
        if ($cleanedRaw !== '' && !is_numeric($cleanedRaw)) {
            return $cleanedRaw;
        }

        // Coba dari judul relasi reportedHelp atau reported_help_text
        $helpTitle = $this->reportedHelp?->title ?? $this->reported_help_text;
        if ($helpTitle) {
            $cleanedHelp = $this->cleanTitleString($helpTitle);
            if ($cleanedHelp !== '' && !is_numeric($cleanedHelp)) {
                return $cleanedHelp;
            }
        }

        // Jika hanya ada judul numerik
        if ($cleanedRaw !== '') {
            return $cleanedRaw;
        }
        if ($helpTitle) {
            $cleanedHelp = $this->cleanTitleString($helpTitle);
            if ($cleanedHelp !== '') return $cleanedHelp;
        }

        return $this->report_type_label ?? 'Laporan Aduan';
    }

    /**
     * Topik / Tugas terkait yang bersih untuk tampilan sub-header (Terkait: ...)
     */
    public function getDisplayTopicAttribute(): string
    {
        $helpTitle = $this->reportedHelp?->title ?? $this->reported_help_text;
        if ($helpTitle) {
            $cleanedHelp = $this->cleanTitleString($helpTitle);
            if ($cleanedHelp !== '' && !is_numeric($cleanedHelp)) {
                return $cleanedHelp;
            }
        }

        // Coba ekstrak dari kronologi / pesan jika ada
        if (!empty($this->message) && preg_match('/(?:untuk tugas|pada tugas|tugas)\s*[:\-–—]\s*["\']?([^"\'\n\r]+)["\']?/iu', $this->message, $matches)) {
            $extracted = trim($matches[1]);
            if (!empty($extracted) && !is_numeric($extracted)) {
                return $this->cleanTitleString($extracted);
            }
        }

        if ($helpTitle) {
            $cleanedHelp = $this->cleanTitleString($helpTitle);
            if ($cleanedHelp !== '') return $cleanedHelp;
        }

        if (!empty($this->title)) {
            $cleanedTitle = $this->cleanTitleString($this->title);
            if ($cleanedTitle !== '') return $cleanedTitle;
        }

        return 'Layanan Platform';
    }
}

