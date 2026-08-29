<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Help extends Model
{
    // ─────────────────────────────────────────────────────────────────────────
    // STATE MACHINE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Status yang valid dalam sistem.
     */
    public const STATUS_MENUNGGU_MITRA              = 'menunggu_mitra';
    public const STATUS_TAKEN                       = 'taken';
    public const STATUS_PARTNER_ON_THE_WAY          = 'partner_on_the_way';
    public const STATUS_PARTNER_ARRIVED             = 'partner_arrived';
    public const STATUS_IN_PROGRESS                 = 'in_progress';
    public const STATUS_WAITING_CONFIRMATION        = 'waiting_customer_confirmation';
    public const STATUS_SELESAI                     = 'selesai';
    public const STATUS_DIBATALKAN                  = 'dibatalkan';
    public const STATUS_PARTNER_CANCEL_REQUESTED    = 'partner_cancel_requested';

    // Escrow Statuses
    public const ESCROW_STATUS_UNINITIALIZED        = 'uninitialized';
    public const ESCROW_STATUS_HELD                 = 'held';
    public const ESCROW_STATUS_RELEASED             = 'released';
    public const ESCROW_STATUS_REFUNDED             = 'refunded';
    public const ESCROW_STATUS_PARTIAL_REFUND       = 'partial_refund';
    public const ESCROW_STATUS_DISPUTED_FREEZE      = 'disputed_freeze';

    // Payment Statuses
    public const PAYMENT_STATUS_UNPAID              = 'unpaid';
    public const PAYMENT_STATUS_PAID                = 'paid';
    public const PAYMENT_STATUS_PARTIALLY_REFUNDED  = 'partially_refunded';
    public const PAYMENT_STATUS_REFUNDED            = 'refunded';
    public const PAYMENT_STATUS_FAILED              = 'failed';

    // Rating Statuses
    public const RATING_STATUS_PENDING              = 'pending';
    public const RATING_STATUS_RATED                = 'rated';

    // Dispatch Modes
    public const DISPATCH_MODE_SEEKING              = 'seeking';
    public const DISPATCH_MODE_OFFERED              = 'offered';
    public const DISPATCH_MODE_POOL                 = 'pool';
    public const DISPATCH_MODE_ASSIGNED             = 'assigned';
    public const DISPATCH_MODE_CLOSED               = 'closed';

    /**
     * Transisi status yang diizinkan.
     * Key: status saat ini
     * Value: array status tujuan yang valid
     */
    public const VALID_TRANSITIONS = [
        self::STATUS_MENUNGGU_MITRA => [
            self::STATUS_TAKEN,
            'memperoleh_mitra',
            self::STATUS_DIBATALKAN,
            'cancelled',
        ],
        'mencari_mitra' => [
            self::STATUS_TAKEN,
            'memperoleh_mitra',
            self::STATUS_DIBATALKAN,
            'cancelled',
        ],
        'menunggu_pembayaran' => [
            self::STATUS_MENUNGGU_MITRA,
            self::STATUS_DIBATALKAN,
            'cancelled',
        ],
        'pending' => [
            self::STATUS_MENUNGGU_MITRA,
            self::STATUS_TAKEN,
            self::STATUS_DIBATALKAN,
            'cancelled',
        ],
        self::STATUS_TAKEN => [
            self::STATUS_PARTNER_ON_THE_WAY,
            self::STATUS_PARTNER_ARRIVED,   // jika mitra tiba tanpa update on_the_way
            self::STATUS_IN_PROGRESS,
            self::STATUS_PARTNER_CANCEL_REQUESTED,
            // Alias lawas yang mungkin masih ada di data
            'memperoleh_mitra',
        ],
        'memperoleh_mitra' => [
            self::STATUS_TAKEN,
            self::STATUS_PARTNER_ON_THE_WAY,
            self::STATUS_PARTNER_CANCEL_REQUESTED,
        ],
        self::STATUS_PARTNER_ON_THE_WAY => [
            self::STATUS_PARTNER_ARRIVED,
            self::STATUS_PARTNER_CANCEL_REQUESTED,
        ],
        self::STATUS_PARTNER_ARRIVED => [
            self::STATUS_IN_PROGRESS,
            self::STATUS_SELESAI,
            self::STATUS_WAITING_CONFIRMATION,
            self::STATUS_PARTNER_CANCEL_REQUESTED,
            // Alias
            'sedang_diproses',
        ],
        'sedang_diproses' => [
            self::STATUS_SELESAI,
            self::STATUS_WAITING_CONFIRMATION,
        ],
        self::STATUS_IN_PROGRESS => [
            self::STATUS_SELESAI,
            self::STATUS_WAITING_CONFIRMATION,
        ],
        self::STATUS_WAITING_CONFIRMATION => [
            self::STATUS_SELESAI,
        ],
        self::STATUS_PARTNER_CANCEL_REQUESTED => [
            self::STATUS_MENUNGGU_MITRA,    // customer accept
            // Kembali ke status sebelumnya (dinamis, ditangani di service)
            self::STATUS_TAKEN,
            self::STATUS_PARTNER_ON_THE_WAY,
            self::STATUS_PARTNER_ARRIVED,
            self::STATUS_IN_PROGRESS,
        ],
        self::STATUS_SELESAI   => [], // terminal
        'completed'            => [], // alias lawas
        self::STATUS_DIBATALKAN => [], // terminal
        'cancelled'             => [], // alias lawas
    ];

    /**
     * Cek apakah transisi ke status target diizinkan.
     */
    public function canTransitionTo(string $toStatus): bool
    {
        $from = $this->status ?? '';
        $allowed = self::VALID_TRANSITIONS[$from] ?? null;

        // Jika status saat ini tidak dikenal dalam map, izinkan transisi
        // (misal: data lama yang belum dimigrasi)
        if ($allowed === null) {
            return true;
        }

        return in_array($toStatus, $allowed, true);
    }

    /**
     * Validasi dan lakukan transisi status.
     *
     * @throws \RuntimeException jika transisi tidak valid
     */
    public function transitionTo(string $toStatus, array $extraData = []): self
    {
        if (!$this->canTransitionTo($toStatus)) {
            throw new \RuntimeException(
                "Transisi status dari '{$this->status}' ke '{$toStatus}' tidak diizinkan."
            );
        }

        $this->update(array_merge(['status' => $toStatus], $extraData));

        return $this;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BOOT (GUARD)
    // ─────────────────────────────────────────────────────────────────────────

    protected static function booted()
    {
        static::saving(function ($help) {
            if ($help->user_id) {
                $user = User::find($help->user_id);
                if ($user && !$user->isCustomer()) {
                    throw new \InvalidArgumentException(
                        "Hanya pengguna dengan peran Customer yang dapat membuat atau memiliki permintaan bantuan. " .
                        "Pengguna '{$user->name}' memiliki peran '{$user->role}'."
                    );
                }
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FILLABLE & CASTS
    // ─────────────────────────────────────────────────────────────────────────

    protected $fillable = [
        'user_id',
        'city_id',
        'title',
        'amount',
        'admin_fee',
        'total_amount',
        'description',
        'equipment_provided',
        'photo',
        'proof_photo',
        'completion_notes',
        'location',
        'full_address',
        'latitude',
        'longitude',
        'status',
        'escrow_status',
        'payment_status',
        'rating_status',
        'dispatch_mode',
        'mitra_id',
        'taken_at',
        'completed_at',
        'confirmation_deadline_at',
        'auto_confirmed_at',
        'assigned_at',
        'pool_opened_at',
        'disputed_at',
        'dispute_reason',
        'dispute_resolved_at',
        'dispute_resolved_by',
        'admin_notes',
        'order_id',
        'voucher_code',
        'discount_amount',
        'booking_fee',
        'mitra_assigned_at',
        'partner_started_at',
        'partner_arrived_at',
        'service_started_at',
        'service_completed_at',
        'scheduled_at',
        'expires_at',
        'partner_initial_lat',
        'partner_initial_lng',
        'partner_current_lat',
        'partner_current_lng',
        'partner_started_moving_at',
        'partner_cancel_requested_at',
        'partner_cancel_reason',
        'partner_cancel_notes',
        'partner_cancel_prev_status',
        'cancelled_mitra_ids',
        // Kolom model v2 (Commission-Based / Escrow System)
        'model_version',
        'platform_commission_rate',
        'platform_fee_amount',
        'mitra_earning',
        'escrow_transaction_id',
        'escrow_locked_at',
    ];

    protected $casts = [
        'taken_at'                   => 'datetime',
        'completed_at'               => 'datetime',
        'confirmation_deadline_at'   => 'datetime',
        'auto_confirmed_at'          => 'datetime',
        'assigned_at'                => 'datetime',
        'pool_opened_at'             => 'datetime',
        'disputed_at'                => 'datetime',
        'dispute_resolved_at'        => 'datetime',
        'amount'                     => 'decimal:2',
        'admin_fee'                  => 'decimal:2',
        'total_amount'               => 'decimal:2',
        'latitude'                   => 'decimal:8',
        'longitude'                  => 'decimal:8',
        'discount_amount'            => 'decimal:2',
        'booking_fee'                => 'decimal:2',
        'mitra_assigned_at'          => 'datetime',
        'partner_started_at'         => 'datetime',
        'partner_arrived_at'         => 'datetime',
        'service_started_at'         => 'datetime',
        'service_completed_at'       => 'datetime',
        'scheduled_at'               => 'datetime',
        'expires_at'                 => 'datetime',
        'partner_initial_lat'        => 'decimal:8',
        'partner_initial_lng'        => 'decimal:8',
        'partner_current_lat'        => 'decimal:8',
        'partner_current_lng'        => 'decimal:8',
        'partner_started_moving_at'  => 'datetime',
        'partner_cancel_requested_at'=> 'datetime',
        'cancelled_mitra_ids'        => 'array',
        // Model v2 casts
        'model_version'              => 'integer',
        'platform_commission_rate'   => 'decimal:2',
        'platform_fee_amount'        => 'decimal:2',
        'mitra_earning'              => 'decimal:2',
        'escrow_locked_at'           => 'datetime',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Alias untuk user (customer yang membuat bantuan). */
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }


    public function mitra()
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /** Rating & ulasan yang diberikan oleh customer kepada mitra. */
    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(Chat::class);
    }

    /** Transaksi escrow holding saat bantuan dibuat (model v2). */
    public function escrowTransaction()
    {
        return $this->belongsTo(BalanceTransaction::class, 'escrow_transaction_id');
    }

    public function dispatches()
    {
        return $this->hasMany(HelpDispatch::class);
    }

    public function activeDispatch()
    {
        return $this->hasOne(HelpDispatch::class)->where('status', HelpDispatch::STATUS_OFFERED)->latestOfMany();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // QUERY SCOPES
    // ─────────────────────────────────────────────────────────────────────────

    /** Bantuan yang sedang menunggu mitra (tersedia di pool dan jadwalnya sudah tiba atau tanpa jadwal). */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_MENUNGGU_MITRA)
                     ->whereNull('mitra_id')
                     ->where(function ($q) {
                         $q->whereNull('scheduled_at')
                           ->orWhere('scheduled_at', '<=', now());
                     });
    }

    /** Bantuan yang sedang aktif dikerjakan. */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_TAKEN,
            'memperoleh_mitra',
            self::STATUS_PARTNER_ON_THE_WAY,
            self::STATUS_PARTNER_ARRIVED,
            self::STATUS_IN_PROGRESS,
            'sedang_diproses',
            self::STATUS_WAITING_CONFIRMATION,
            self::STATUS_PARTNER_CANCEL_REQUESTED,
        ]);
    }

    /** Bantuan yang sudah selesai. */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [self::STATUS_SELESAI, 'completed']);
    }

    /** Bantuan yang dibatalkan. */
    public function scopeCancelled($query)
    {
        return $query->whereIn('status', [self::STATUS_DIBATALKAN, 'cancelled']);
    }

    /**
     * Scope untuk menyaring bantuan yang berhak diambil oleh mitra tertentu
     * (mengecualikan bantuan yang pernah dibatalkan oleh mitra tersebut).
     */
    public function scopeAvailableForMitra($query, ?int $mitraId = null)
    {
        if (!$mitraId) {
            return $query;
        }

        return $query->where(function ($q) use ($mitraId) {
            $q->whereNull('cancelled_mitra_ids')
              ->orWhereJsonDoesntContain('cancelled_mitra_ids', (int) $mitraId);
        });
    }

    /**
     * Cek apakah bantuan ini pernah dibatalkan oleh mitra tertentu.
     */
    public function hasCancelledBy(?int $mitraId): bool
    {
        if (!$mitraId) {
            return false;
        }

        $ids = $this->cancelled_mitra_ids ?? [];
        if (!is_array($ids)) {
            $ids = json_decode((string) $ids, true) ?? [];
        }

        return in_array($mitraId, $ids, false) || in_array((string) $mitraId, $ids, false);
    }

    /**
     * Cek apakah bantuan ini berhak diambil oleh mitra tertentu.
     */
    public function canBeTakenBy(?User $mitra): bool
    {
        if (!$mitra || !$mitra->isMitra()) {
            return false;
        }

        if ($this->status !== self::STATUS_MENUNGGU_MITRA || $this->mitra_id !== null) {
            return false;
        }

        if ($this->hasCancelledBy($mitra->id)) {
            return false;
        }

        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // COMPUTED / HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Label status dalam bahasa Indonesia.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'menunggu_mitra'               => 'Menunggu Rekan Jasa',
            'taken', 'memperoleh_mitra'   => 'Rekan Jasa Mengambil Pesanan',
            'partner_on_the_way'           => 'Rekan Jasa Menuju Lokasi',
            'partner_arrived'              => 'Rekan Jasa Tiba di Lokasi',
            'in_progress', 'sedang_diproses' => 'Pelayanan Dalam Proses',
            'waiting_customer_confirmation'=> 'Menunggu Konfirmasi Anda',
            'selesai', 'completed'         => 'Selesai',
            'dibatalkan', 'cancelled'      => 'Dibatalkan',
            'partner_cancel_requested'     => 'Mitra Mengajukan Pembatalan',
            default                        => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Langkah progres saat ini (1 sampai 5).
     */
    public function getProgressStepAttribute(): int
    {
        return match($this->status) {
            'menunggu_mitra', 'mencari_mitra', 'menunggu_pembayaran' => 1,
            'taken', 'memperoleh_mitra'                             => 2,
            'partner_on_the_way'                                    => 3,
            'partner_arrived', 'in_progress', 'sedang_diproses'     => 4,
            'waiting_customer_confirmation', 'selesai', 'completed' => 5,
            default                                                 => 1,
        };
    }

    /**
     * Persentase progres bantuan (0% - 100%).
     */
    public function getProgressPercentageAttribute(): int
    {
        return match($this->status) {
            'menunggu_mitra', 'mencari_mitra', 'menunggu_pembayaran' => 20,
            'taken', 'memperoleh_mitra'                             => 40,
            'partner_on_the_way'                                    => 60,
            'partner_arrived', 'in_progress', 'sedang_diproses'     => 80,
            'waiting_customer_confirmation'                         => 90,
            'selesai', 'completed'                                  => 100,
            'dibatalkan', 'cancelled'                               => 0,
            'partner_cancel_requested'                              => 50,
            default                                                 => 20,
        };
    }

    /**
     * Ikon/Emoji penanda status progres.
     */
    public function getProgressIconAttribute(): string
    {
        return match($this->status) {
            'menunggu_mitra', 'mencari_mitra'                       => '🔍',
            'taken', 'memperoleh_mitra'                             => '🤝',
            'partner_on_the_way'                                    => '🛵',
            'partner_arrived'                                       => '📍',
            'in_progress', 'sedang_diproses'                        => '⚡',
            'waiting_customer_confirmation'                         => '📸',
            'selesai', 'completed'                                  => '✅',
            'dibatalkan', 'cancelled'                               => '❌',
            'partner_cancel_requested'                              => '⚠️',
            default                                                 => '📋',
        };
    }

    /**
     * Ringkasan progres (contoh: "Langkah 3/5: Menuju Lokasi").
     */
    public function getProgressSummaryAttribute(): string
    {
        if (in_array($this->status, ['dibatalkan', 'cancelled'])) {
            return 'Pesanan Dibatalkan';
        }
        if ($this->status === 'partner_cancel_requested') {
            return 'Pengajuan Pembatalan';
        }
        if (in_array($this->status, ['selesai', 'completed'])) {
            return 'Pesanan Selesai';
        }

        $step = $this->progress_step;
        $label = match($step) {
            1 => 'Mencari Mitra',
            2 => 'Pesanan Diambil',
            3 => 'Menuju Lokasi',
            4 => 'Pelayanan Dalam Proses',
            5 => 'Menunggu Konfirmasi',
            default => 'Diproses',
        };

        return "Langkah {$step}/5: {$label}";
    }

    /**
     * Warna badge status (Tailwind classes).
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'menunggu_mitra'                => 'bg-blue-100 text-blue-700',
            'taken', 'memperoleh_mitra'    => 'bg-blue-100 text-blue-700',
            'partner_on_the_way'            => 'bg-indigo-100 text-indigo-700',
            'partner_arrived'               => 'bg-green-100 text-green-700',
            'in_progress', 'sedang_diproses'=> 'bg-cyan-100 text-cyan-700',
            'waiting_customer_confirmation' => 'bg-orange-100 text-orange-700',
            'selesai', 'completed'          => 'bg-green-100 text-green-700',
            'dibatalkan', 'cancelled'       => 'bg-red-100 text-red-700',
            'partner_cancel_requested'      => 'bg-yellow-100 text-yellow-700',
            default                         => 'bg-gray-100 text-gray-700',
        };
    }

    /**
     * Apakah bantuan masih bisa dibatalkan oleh customer.
     */
    public function isCustomerCancellable(): bool
    {
        return in_array($this->status, [
            self::STATUS_MENUNGGU_MITRA,
            'mencari_mitra',
            'menunggu_pembayaran',
            'pending',
        ]);
    }



    /**
     * Apakah bantuan sedang aktif (belum selesai dan belum dibatalkan).
     */
    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_TAKEN,
            'memperoleh_mitra',
            self::STATUS_PARTNER_ON_THE_WAY,
            self::STATUS_PARTNER_ARRIVED,
            self::STATUS_IN_PROGRESS,
            'sedang_diproses',
            self::STATUS_WAITING_CONFIRMATION,
            self::STATUS_PARTNER_CANCEL_REQUESTED,
        ]);
    }

    /**
     * Apakah bantuan sudah selesai.
     */
    public function isDone(): bool
    {
        return in_array($this->status, [self::STATUS_SELESAI, 'completed']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MODEL V2 HELPERS (Commission-Based / Escrow)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Apakah bantuan ini menggunakan model v2 (Commission-Based / Escrow).
     * Data lama (model_version = 1 atau NULL) menggunakan logika Buyer-Pays lama.
     */
    public function isV2Model(): bool
    {
        return (int) ($this->model_version ?? 1) >= 2;
    }

    /**
     * Kalkulasi nominal bersih mitra (jika model_version = 2).
     * Mitra menerima 100% nominal bantuan (tanpa potongan komisi pada mitra).
     */
    public function getNetEarning(): float
    {
        if ($this->isV2Model() && $this->mitra_earning > 0) {
            return (float) $this->mitra_earning;
        }
        return (float) $this->amount;
    }

    /**
     * Kalkulasi nominal biaya layanan / pajak platform yang dibayar oleh customer.
     */
    public function getPlatformFee(): float
    {
        if ($this->isV2Model()) {
            if ($this->platform_fee_amount > 0) {
                return (float) $this->platform_fee_amount;
            }
            if ($this->admin_fee > 0) {
                return (float) $this->admin_fee;
            }
        }
        return (float) ($this->admin_fee ?? 0);
    }

    /**
     * Label biaya layanan platform.
     * Contoh: "Rp 2.000"
     */
    public function getCommissionRateLabel(): string
    {
        $fee = $this->getPlatformFee();
        if ($fee > 0) {
            return 'Rp ' . number_format($fee, 0, ',', '.');
        }
        return 'Rp 0';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ESCROW, DISPUTE & RATING HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    public function disputeResolvedBy()
    {
        return $this->belongsTo(User::class, 'dispute_resolved_by');
    }

    public function isEscrowHeld(): bool
    {
        return $this->escrow_status === self::ESCROW_STATUS_HELD;
    }

    public function isEscrowReleased(): bool
    {
        return $this->escrow_status === self::ESCROW_STATUS_RELEASED;
    }

    public function isDisputed(): bool
    {
        return $this->escrow_status === self::ESCROW_STATUS_DISPUTED_FREEZE;
    }

    public function isAutoConfirmable(): bool
    {
        return $this->status === self::STATUS_WAITING_CONFIRMATION
            && $this->isEscrowHeld()
            && !$this->isDisputed()
            && $this->confirmation_deadline_at !== null
            && $this->confirmation_deadline_at->isPast();
    }

    public function canBeRated(): bool
    {
        return $this->rating_status === self::RATING_STATUS_PENDING
            && in_array($this->escrow_status, [self::ESCROW_STATUS_RELEASED, self::ESCROW_STATUS_REFUNDED, self::ESCROW_STATUS_PARTIAL_REFUND], true)
            && in_array($this->status, [self::STATUS_SELESAI, self::STATUS_DIBATALKAN], true)
            && !$this->isDisputed();
    }

    public function getConfirmationRemainingMinutesAttribute(): ?int
    {
        if (!$this->confirmation_deadline_at) {
            return null;
        }
        return (int) max(0, now()->diffInMinutes($this->confirmation_deadline_at, false));
    }
}

