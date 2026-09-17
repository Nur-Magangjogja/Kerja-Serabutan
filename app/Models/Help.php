<?php

namespace App\Models;

use App\Enums\HelpStatus;
use App\Support\Presenters\HelpStatusPresenter;
use Illuminate\Database\Eloquent\Model;

class Help extends Model
{
    /**
     * Dapatkan representasi Enum dari status saat ini.
     */
    public function statusEnum(): ?HelpStatus
    {
        return HelpStatus::tryFromOrNormalize($this->status);
    }

    /**
     * Dapatkan metadata visual status (badge, warna, icon, label) untuk UI.
     */
    public function statusPresenter(): array
    {
        return HelpStatusPresenter::for($this);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SERVICE TYPES & ORDER MODES (REVISI FINAL)
    // ─────────────────────────────────────────────────────────────────────────
    public const SERVICE_TYPE_ON_SITE           = 'on_site_service';
    public const SERVICE_TYPE_PICKUP_DELIVERY   = 'pickup_delivery';

    public const ORDER_MODE_INSTANT             = 'instant';
    public const ORDER_MODE_SCHEDULED           = 'scheduled';

    // Sub-stages for Pickup & Delivery
    public const STAGE_GOING_TO_PICKUP          = 'going_to_pickup';
    public const STAGE_AT_PICKUP                = 'at_pickup';
    public const STAGE_WAITING_FOR_CUSTOMER     = 'waiting_for_customer';
    public const STAGE_ITEM_COLLECTED           = 'item_collected';
    public const STAGE_GOING_TO_DESTINATION     = 'going_to_destination';
    public const STAGE_FINAL_APPROACH           = 'final_approach';
    public const STAGE_AT_DESTINATION           = 'at_destination';

    // Item Fund Modes
    public const ITEM_FUND_CUSTOMER_PAID        = 'customer_paid_in_app';
    public const ITEM_FUND_PARTNER_ADVANCE      = 'partner_advance';
    public const ITEM_FUND_COD                  = 'cash_on_delivery';

    // Route Sources
    public const ROUTE_SOURCE_ACTUAL_PROVIDER   = 'actual_provider';
    public const ROUTE_SOURCE_FALLBACK          = 'fallback_estimation';

    // ─────────────────────────────────────────────────────────────────────────
    // STATE MACHINE & CANONICAL STATUSES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Status resmi / kanonik yang valid dalam sistem.
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
    public const STATUS_CUSTOMER_CANCEL_REQUESTED   = 'customer_cancel_requested';

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
     * Daftar status yang aktif dikerjakan oleh mitra.
     */
    public static function activeStatuses(): array
    {
        return [
            self::STATUS_TAKEN,
            self::STATUS_PARTNER_ON_THE_WAY,
            self::STATUS_PARTNER_ARRIVED,
            self::STATUS_IN_PROGRESS,
        ];
    }

    /**
     * Daftar status akhir (terminal).
     */
    public static function terminalStatuses(): array
    {
        return [
            self::STATUS_SELESAI,
            self::STATUS_DIBATALKAN,
        ];
    }

    /**
     * Daftar seluruh status kanonik yang diakui sistem.
     */
    public static function canonicalStatuses(): array
    {
        return [
            self::STATUS_MENUNGGU_MITRA,
            self::STATUS_TAKEN,
            self::STATUS_PARTNER_ON_THE_WAY,
            self::STATUS_PARTNER_ARRIVED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_WAITING_CONFIRMATION,
            self::STATUS_SELESAI,
            self::STATUS_DIBATALKAN,
            self::STATUS_PARTNER_CANCEL_REQUESTED,
            self::STATUS_CUSTOMER_CANCEL_REQUESTED,
        ];
    }

    /**
     * Normalisasi status lawas / alias ke status kanonik.
     */
    public static function normalizeStatus(?string $status): string
    {
        if (empty($status)) {
            return self::STATUS_MENUNGGU_MITRA;
        }

        return match ($status) {
            'completed' => self::STATUS_SELESAI,
            'cancelled' => self::STATUS_DIBATALKAN,
            'memperoleh_mitra' => self::STATUS_TAKEN,
            'sedang_diproses' => self::STATUS_IN_PROGRESS,
            'mencari_mitra', 'pending', 'menunggu_pembayaran' => self::STATUS_MENUNGGU_MITRA,
            'waiting_confirmation' => self::STATUS_WAITING_CONFIRMATION,
            default => $status,
        };
    }

    /**
     * Transisi status yang diizinkan (Kanonik).
     * Key: status saat ini
     * Value: array status tujuan yang valid
     */
    public const VALID_TRANSITIONS = [
        self::STATUS_MENUNGGU_MITRA => [
            self::STATUS_TAKEN,
            self::STATUS_DIBATALKAN,
        ],
        self::STATUS_TAKEN => [
            self::STATUS_PARTNER_ON_THE_WAY,
            self::STATUS_PARTNER_ARRIVED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_PARTNER_CANCEL_REQUESTED,
            self::STATUS_CUSTOMER_CANCEL_REQUESTED,
            self::STATUS_DIBATALKAN,
        ],
        self::STATUS_PARTNER_ON_THE_WAY => [
            self::STATUS_PARTNER_ARRIVED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_PARTNER_CANCEL_REQUESTED,
            self::STATUS_CUSTOMER_CANCEL_REQUESTED,
            self::STATUS_DIBATALKAN,
        ],
        self::STATUS_PARTNER_ARRIVED => [
            self::STATUS_IN_PROGRESS,
            self::STATUS_SELESAI,
            self::STATUS_WAITING_CONFIRMATION,
            self::STATUS_PARTNER_CANCEL_REQUESTED,
            self::STATUS_CUSTOMER_CANCEL_REQUESTED,
            self::STATUS_DIBATALKAN,
        ],
        self::STATUS_IN_PROGRESS => [
            self::STATUS_SELESAI,
            self::STATUS_WAITING_CONFIRMATION,
            self::STATUS_PARTNER_CANCEL_REQUESTED,
            self::STATUS_CUSTOMER_CANCEL_REQUESTED,
            self::STATUS_DIBATALKAN,
        ],
        self::STATUS_WAITING_CONFIRMATION => [
            self::STATUS_SELESAI,
            self::STATUS_PARTNER_CANCEL_REQUESTED,
            self::STATUS_CUSTOMER_CANCEL_REQUESTED,
        ],
        self::STATUS_PARTNER_CANCEL_REQUESTED => [
            self::STATUS_MENUNGGU_MITRA,    // customer accept / admin rematch
            self::STATUS_DIBATALKAN,        // admin approve cancellation / timeout
            self::STATUS_TAKEN,
            self::STATUS_PARTNER_ON_THE_WAY,
            self::STATUS_PARTNER_ARRIVED,
            self::STATUS_IN_PROGRESS,
        ],
        self::STATUS_CUSTOMER_CANCEL_REQUESTED => [
            self::STATUS_MENUNGGU_MITRA,    // admin relist ke pool
            self::STATUS_DIBATALKAN,        // admin approve full cancel & refund
            self::STATUS_TAKEN,
            self::STATUS_PARTNER_ON_THE_WAY,
            self::STATUS_PARTNER_ARRIVED,
            self::STATUS_IN_PROGRESS,
        ],
        self::STATUS_SELESAI   => [], // terminal
        self::STATUS_DIBATALKAN => [], // terminal
    ];

    /**
     * Cek apakah transisi ke status target diizinkan (Fail-Closed).
     */
    public function canTransitionTo(string $toStatus): bool
    {
        $from = self::normalizeStatus($this->status ?? '');
        $target = self::normalizeStatus($toStatus);
        $allowed = self::VALID_TRANSITIONS[$from] ?? null;

        // Fail-Closed: Jika status saat ini tidak dikenal dalam map, tolak transisi secara tegas demi integritas data
        if ($allowed === null) {
            return false;
        }

        return in_array($target, $allowed, true);
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
            // Hanya jalankan query validasi role jika user_id baru dibuat atau diubah nilainya
            if ($help->isDirty('user_id') && $help->user_id) {
                $user = $help->relationLoaded('user') ? $help->user : User::find($help->user_id);
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
        'district_id',
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
        'cancel_requested_by',
        'cancel_deadline_at',
        'cancel_evidence_photo',
        'cancelled_mitra_ids',
        // Kolom model v2 (Commission-Based / Escrow System)
        'model_version',
        'platform_commission_rate',
        'platform_fee_amount',
        'mitra_earning',
        'escrow_transaction_id',
        'escrow_locked_at',
        // Kolom model v3 (Revisi 3: Multi-Service, 3-Context Distances, Stages, Pricing Rules)
        'service_type',
        'service_stage',
        'order_mode',
        'service_category',
        'service_duration_hours',
        'published_at',
        'departure_at',
        'service_scheduled_at',
        'pickup_scheduled_at',
        'delivery_deadline_at',
        'early_departure_minutes',
        'departure_reminder_sent_at',
        'matching_distance_km',
        'travel_distance_km',
        'service_route_distance_km',
        'route_source',
        'estimated_duration_minutes',
        'estimated_arrival_at',
        'service_fee',
        'base_fare_applied',
        'price_per_km_applied',
        'platform_fee_applied',
        'estimated_road_distance_km',
        'travel_fee',
        'material_fee',
        'item_fund',
        'item_fund_mode',
        'customer_reimbursement_method',
        'advance_limit',
        'handling_fee',
        'minimum_service_fee',
        'minimum_order_value',
        'pickup_latitude',
        'pickup_longitude',
        'pickup_address',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_address',
        'store_name',
        'store_address',
        'store_latitude',
        'store_longitude',
        'gps_accuracy',
        'last_movement_at',
        'arrived_at',
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
        'cancel_deadline_at'         => 'datetime',
        'cancelled_mitra_ids'        => 'array',
        // Model v2 casts
        'model_version'              => 'integer',
        'platform_commission_rate'   => 'decimal:2',
        'platform_fee_amount'        => 'decimal:2',
        'mitra_earning'              => 'decimal:2',
        'escrow_locked_at'           => 'datetime',
        // Model v3 casts
        'published_at'               => 'datetime',
        'departure_at'               => 'datetime',
        'service_scheduled_at'       => 'datetime',
        'pickup_scheduled_at'        => 'datetime',
        'delivery_deadline_at'       => 'datetime',
        'early_departure_minutes'    => 'integer',
        'departure_reminder_sent_at' => 'datetime',
        'matching_distance_km'       => 'decimal:2',
        'travel_distance_km'         => 'decimal:2',
        'service_route_distance_km'  => 'decimal:2',
        'estimated_duration_minutes' => 'integer',
        'estimated_arrival_at'       => 'datetime',
        'service_duration_hours'     => 'decimal:2',
        'service_fee'                => 'decimal:2',
        'travel_fee'                 => 'decimal:2',
        'material_fee'               => 'decimal:2',
        'item_fund'                  => 'decimal:2',
        'advance_limit'              => 'decimal:2',
        'handling_fee'               => 'decimal:2',
        'minimum_service_fee'        => 'decimal:2',
        'minimum_order_value'        => 'decimal:2',
        'pickup_latitude'            => 'decimal:8',
        'pickup_longitude'           => 'decimal:8',
        'delivery_latitude'          => 'decimal:8',
        'delivery_longitude'         => 'decimal:8',
        'store_latitude'             => 'decimal:8',
        'store_longitude'            => 'decimal:8',
        'gps_accuracy'               => 'decimal:2',
        'last_movement_at'           => 'datetime',
        'arrived_at'                 => 'datetime',
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

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function mitra()
    {
        return $this->belongsTo(User::class, 'mitra_id');
    }

    public function cancelRequests()
    {
        return $this->hasMany(HelpCancelRequest::class);
    }

    public function cancelRequest()
    {
        return $this->hasOne(HelpCancelRequest::class)->latestOfMany();
    }

    public function latestCancelRequest()
    {
        return $this->hasOne(HelpCancelRequest::class)->latestOfMany();
    }

    public function activeCancelRequest()
    {
        return $this->hasOne(HelpCancelRequest::class)->where('status', HelpCancelRequest::STATUS_PENDING)->latestOfMany();
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

    public function partnerExclusions()
    {
        return $this->hasMany(HelpPartnerExclusion::class, 'help_id');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // QUERY SCOPES
    // ─────────────────────────────────────────────────────────────────────────

    /** Bantuan yang sedang menunggu mitra (tersedia di pool dan belum kadaluwarsa). */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_MENUNGGU_MITRA)
                     ->whereNull('mitra_id')
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    /** Bantuan yang sedang aktif dikerjakan oleh mitra. */
    public function scopeActive($query)
    {
        return $query->whereIn('status', self::activeStatuses());
    }

    /** Bantuan yang telah diselesaikan mitra dan sedang menunggu konfirmasi customer (1x24 jam). */
    public function scopeWaitingConfirmation($query)
    {
        return $query->where('status', self::STATUS_WAITING_CONFIRMATION);
    }

    /** Bantuan yang sudah selesai. */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_SELESAI);
    }

    /** Bantuan yang dibatalkan. */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_DIBATALKAN);
    }

    /**
     * Scope untuk menyaring bantuan dalam satu wilayah Kecamatan.
     */
    public function scopeInDistrict($query, $districtId)
    {
        if (empty($districtId)) {
            return $query;
        }
        return $query->where('district_id', $districtId);
    }

    /**
     * Scope untuk menyaring bantuan dalam daftar wilayah Kecamatan.
     */
    public function scopeInDistricts($query, array $districtIds)
    {
        $filtered = array_values(array_filter(array_map('intval', $districtIds)));
        if (empty($filtered)) {
            return $query;
        }
        return $query->whereIn('district_id', $filtered);
    }

    /**
     * Scope menyaring bantuan dalam batas operasional baku (maks. 10.0 KM) menggunakan bounding box GPS.
     */
    public function scopeWithinOperationalDistance($query, ?float $lat, ?float $lng, float $maxKm = 10.0)
    {
        if (!$lat || !$lng) {
            return $query;
        }

        $latDelta = $maxKm / 111.045;
        $lngDelta = $maxKm / (111.045 * max(0.01, cos(deg2rad($lat))));

        $minLat = $lat - $latDelta;
        $maxLat = $lat + $latDelta;
        $minLng = $lng - $lngDelta;
        $maxLng = $lng + $lngDelta;

        return $query->whereBetween('latitude', [$minLat, $maxLat])
                     ->whereBetween('longitude', [$minLng, $maxLng]);
    }

    /**
     * Scope untuk menyaring bantuan yang sudah dipublikasikan (published_at <= now()).
     */
    public function scopePublished($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('published_at')
              ->orWhere('published_at', '<=', now());
        });
    }

    /**
     * Scope untuk menyaring bantuan yang berhak diambil oleh mitra tertentu
     * (mengecualikan bantuan yang pernah dibatalkan oleh mitra tersebut via tabel relasional terindeks).
     */
    public function scopeAvailableForMitra($query, ?int $mitraId = null)
    {
        $query->published();

        if (!$mitraId) {
            return $query;
        }

        return $query->whereDoesntHave('partnerExclusions', function ($q) use ($mitraId) {
            $q->where('mitra_id', $mitraId);
        })->where(function ($q) use ($mitraId) {
            // Fallback backward-compatibility untuk data legacy
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

        // Cek dari relasi terindeks (jika sudah di-load atau dari tabel)
        if ($this->relationLoaded('partnerExclusions')) {
            if ($this->partnerExclusions->contains('mitra_id', $mitraId)) {
                return true;
            }
        } elseif (\Illuminate\Support\Facades\Schema::hasTable('help_partner_exclusions') && HelpPartnerExclusion::where('help_id', $this->id)->where('mitra_id', $mitraId)->exists()) {
            return true;
        }

        // Fallback backward-compatibility untuk data legacy
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

        if ($this->dispatch_mode && $this->dispatch_mode !== self::DISPATCH_MODE_POOL) {
            return false;
        }

        if ($this->hasCancelledBy($mitra->id)) {
            return false;
        }

        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // COMPUTED / HELPERS (REVISI 3)
    // ─────────────────────────────────────────────────────────────────────────

    public function isPickup(): bool
    {
        return $this->service_type === self::SERVICE_TYPE_PICKUP_DELIVERY;
    }

    public function isPassenger(): bool
    {
        return $this->isPickup() && $this->service_category === 'passenger';
    }

    public function isGoodsDocument(): bool
    {
        return $this->isPickup() && ($this->service_category === 'goods_document' || empty($this->service_category));
    }

    public function getServiceCategoryLabelAttribute(): string
    {
        if ($this->isPickup()) {
            if ($this->service_category === 'passenger') {
                return 'Antar Penumpang';
            }
            return 'Barang & Dokumen';
        }
        return 'Kerja Serabutan';
    }

    public function getServiceCategoryIconAttribute(): string
    {
        if ($this->isPickup()) {
            if ($this->service_category === 'passenger') {
                return '👥';
            }
            return '📦';
        }
        return '🛠️';
    }

    public function isBuy(): bool
    {
        return false;
    }

    public function isOnSite(): bool
    {
        return empty($this->service_type) || $this->service_type === self::SERVICE_TYPE_ON_SITE;
    }

    public function isRegular(): bool
    {
        return $this->isOnSite();
    }

    public function isPublished(): bool
    {
        if (!$this->published_at) {
            return true;
        }
        return now()->gte($this->published_at);
    }

    /**
     * Dapatkan batas waktu pencarian efektif (expires_at jika ada, atau fallback jam setting sistem).
     */
    public function getEffectiveExpiresAtAttribute(): ?\Carbon\Carbon
    {
        if ($this->expires_at) {
            return \Carbon\Carbon::parse($this->expires_at);
        }

        $fallbackHours = \App\Models\AppSetting::getHelpAutoCancelHours();
        return $this->created_at 
            ? $this->created_at->copy()->addHours($fallbackHours) 
            : now()->addHours($fallbackHours ?: 24);
    }

    /**
     * Sisa detik pencarian mitra sebelum tugas berakhir.
     */
    public function getSearchRemainingSecondsAttribute(): int
    {
        $expiry = $this->effective_expires_at;
        if (!$expiry) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($expiry, false));
    }

    /**
     * Cek apakah bantuan menunggu mitra ini sudah melewati batas waktu pencarian (expired).
     */
    public function isExpired(): bool
    {
        if ($this->status !== self::STATUS_MENUNGGU_MITRA || $this->mitra_id !== null) {
            return false;
        }

        $expiry = $this->effective_expires_at;
        return $expiry ? now()->gte($expiry) : false;
    }

    public function isScheduled(): bool
    {
        return $this->order_mode === self::ORDER_MODE_SCHEDULED;
    }

    public function isInstant(): bool
    {
        return empty($this->order_mode) || $this->order_mode === self::ORDER_MODE_INSTANT;
    }

    /**
     * Dapatkan target waktu jadwal pengerjaan yang ditentukan customer.
     */
    public function getScheduledTargetTime(): ?\Carbon\Carbon
    {
        if ($this->service_scheduled_at) {
            return \Carbon\Carbon::parse($this->service_scheduled_at);
        }
        if ($this->scheduled_at) {
            return \Carbon\Carbon::parse($this->scheduled_at);
        }
        return null;
    }

    /**
     * Dapatkan jeda menit sebelum jadwal buka keberangkatan (Default 60 menit jika null).
     */
    public function getDepartureLeadMinutesAttribute(): int
    {
        return !is_null($this->early_departure_minutes) ? (int) $this->early_departure_minutes : (int) AppSetting::getScheduledEarlyDepartureWindowMinutes();
    }

    /**
     * Waktu persis saat jendela keberangkatan mulai dibuka untuk mitra.
     * Misal target pkl 07:00 dengan jeda 60 menit -> dibuka pkl 06:00.
     */
    public function getDepartureWindowOpensAtAttribute(): ?\Carbon\Carbon
    {
        if (!$this->isScheduled()) {
            return null;
        }

        $target = $this->getScheduledTargetTime();
        if (!$target) {
            return null;
        }

        return $target->copy()->subMinutes($this->departure_lead_minutes);
    }

    /**
     * Periksa apakah mitra sudah diizinkan mulai berangkat / menuju lokasi.
     */
    public function canPartnerStartDeparture(): bool
    {
        if (!$this->isScheduled()) {
            return true;
        }

        $windowOpensAt = $this->departure_window_opens_at;
        if (!$windowOpensAt) {
            return true;
        }

        return now()->gte($windowOpensAt);
    }

    /**
     * Alias method status jendela keberangkatan.
     */
    public function isDepartureWindowOpen(): bool
    {
        return $this->canPartnerStartDeparture();
    }

    /**
     * Hitung sisa menit hingga jendela keberangkatan dibuka (0 jika sudah buka).
     */
    public function getDepartureCountdownMinutesAttribute(): int
    {
        if ($this->canPartnerStartDeparture()) {
            return 0;
        }

        $windowOpensAt = $this->departure_window_opens_at;
        if (!$windowOpensAt) {
            return 0;
        }

        return max(0, (int) now()->diffInMinutes($windowOpensAt, false));
    }

    /**
     * Teks deskripsi hitung mundur ramah pengguna.
     */
    public function getDepartureCountdownFormattedAttribute(): string
    {
        if ($this->canPartnerStartDeparture()) {
            return 'Sudah dapat berangkat';
        }

        $windowOpensAt = $this->departure_window_opens_at;
        if (!$windowOpensAt) {
            return '';
        }

        $minutes = $this->departure_countdown_minutes;
        if ($minutes <= 0) {
            return 'Siap dibuka';
        }

        if ($minutes >= 60) {
            $hours = floor($minutes / 60);
            $remMinutes = $minutes % 60;
            return $remMinutes > 0 ? "{$hours} jam {$remMinutes} menit lagi" : "{$hours} jam lagi";
        }

        return "{$minutes} menit lagi";
    }

    /**
     * Label tahapan sub-layanan (service_stage) dalam Bahasa Indonesia.
     */
    public function getStageLabelAttribute(): ?string
    {
        if (empty($this->service_stage)) {
            return null;
        }

        return match($this->service_stage) {
            self::STAGE_GOING_TO_PICKUP      => 'Menuju Lokasi Pengambilan',
            self::STAGE_AT_PICKUP            => 'Tiba di Lokasi Pengambilan',
            self::STAGE_WAITING_FOR_CUSTOMER => 'Menunggu Customer di Titik Jemput',
            self::STAGE_ITEM_COLLECTED       => 'Barang Telah Diambil',
            self::STAGE_GOING_TO_DESTINATION => 'Mengantar ke Tujuan',
            self::STAGE_FINAL_APPROACH       => 'Mendekati Tujuan (Tahap Akhir)',
            self::STAGE_AT_DESTINATION       => 'Tiba di Lokasi Tujuan',
            default                          => ucwords(str_replace('_', ' ', $this->service_stage)),
        };
    }

    /**
     * Label status dalam bahasa Indonesia kontekstual dengan jenis layanan dan stage.
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->service_stage && $stageLabel = $this->stage_label) {
            if ($this->status === self::STATUS_IN_PROGRESS || $this->status === self::STATUS_PARTNER_ON_THE_WAY) {
                return $stageLabel;
            }
        }

        return match($this->status) {
            self::STATUS_MENUNGGU_MITRA            => 'Menunggu Rekan Jasa',
            self::STATUS_TAKEN                     => 'Rekan Jasa Mengambil Pesanan',
            self::STATUS_PARTNER_ON_THE_WAY        => $this->isPickup() ? 'Menuju Titik Jemput' : 'Rekan Jasa Menuju Lokasi',
            self::STATUS_PARTNER_ARRIVED           => $this->isPickup() ? 'Tiba di Titik Jemput' : 'Rekan Jasa Tiba di Lokasi',
            self::STATUS_IN_PROGRESS               => $this->isPickup() ? 'Mengantar ke Tujuan' : 'Pelayanan Dalam Proses',
            self::STATUS_WAITING_CONFIRMATION     => 'Menunggu Konfirmasi Anda',
            self::STATUS_SELESAI                   => 'Selesai',
            self::STATUS_DIBATALKAN                => 'Dibatalkan',
            self::STATUS_PARTNER_CANCEL_REQUESTED  => 'Mitra Mengajukan Kendala/Pembatalan',
            self::STATUS_CUSTOMER_CANCEL_REQUESTED => 'Pengajuan Kendala Customer Sedang Ditinjau Admin',
            default                                => ucfirst(str_replace('_', ' ', $this->status ?? '')),
        };
    }

    /**
     * Langkah progres saat ini (1 sampai 5) yang dinamis mengikuti konteks jenis layanan.
     */
    public function getProgressStepAttribute(): int
    {
        if ($this->status === self::STATUS_DIBATALKAN) {
            return 0;
        }

        if ($this->status === self::STATUS_MENUNGGU_MITRA) {
            return 1;
        }

        if ($this->status === self::STATUS_TAKEN) {
            return 2;
        }

        if ($this->isPickup()) {
            return match($this->service_stage) {
                self::STAGE_GOING_TO_PICKUP                                                => 2,
                self::STAGE_AT_PICKUP, self::STAGE_WAITING_FOR_CUSTOMER                    => 3,
                self::STAGE_ITEM_COLLECTED, self::STAGE_GOING_TO_DESTINATION, self::STAGE_FINAL_APPROACH => 3,
                self::STAGE_AT_DESTINATION                                                 => 4,
                default                                                                    => $this->status === self::STATUS_WAITING_CONFIRMATION ? 4 : ($this->isDone() ? 5 : 3),
            };
        }

        return match($this->status) {
            self::STATUS_PARTNER_ON_THE_WAY                                    => 3,
            self::STATUS_PARTNER_ARRIVED, self::STATUS_IN_PROGRESS             => 4,
            self::STATUS_WAITING_CONFIRMATION, self::STATUS_SELESAI            => 5,
            default                                                            => 1,
        };
    }

    /**
     * Persentase progres bantuan (0% - 100%).
     */
    public function getProgressPercentageAttribute(): int
    {
        if ($this->status === self::STATUS_DIBATALKAN) {
            return 0;
        }

        if ($this->isDone()) {
            return 100;
        }

        if ($this->status === self::STATUS_WAITING_CONFIRMATION) {
            return 90;
        }

        if ($this->isPickup()) {
            return match($this->service_stage) {
                self::STAGE_GOING_TO_PICKUP      => 30,
                self::STAGE_AT_PICKUP            => 45,
                self::STAGE_WAITING_FOR_CUSTOMER => 50,
                self::STAGE_ITEM_COLLECTED       => 60,
                self::STAGE_GOING_TO_DESTINATION => 75,
                self::STAGE_FINAL_APPROACH       => 85,
                self::STAGE_AT_DESTINATION       => 90,
                default                          => 40,
            };
        }

        return match($this->status) {
            self::STATUS_MENUNGGU_MITRA            => 20,
            self::STATUS_TAKEN                     => 40,
            self::STATUS_PARTNER_ON_THE_WAY        => 60,
            self::STATUS_PARTNER_ARRIVED           => 75,
            self::STATUS_IN_PROGRESS               => 85,
            self::STATUS_PARTNER_CANCEL_REQUESTED  => 50,
            self::STATUS_CUSTOMER_CANCEL_REQUESTED => 50,
            default                                => 20,
        };
    }

    /**
     * Daftar tahapan dinamis (stepper) berdasarkan jenis layanan & status/stage.
     *
     * @return array<int, array{key: string, title: string, icon: string, active: bool}>
     */
    public function getMultiStageStepsAttribute(): array
    {
        $status = $this->status;
        $stage = $this->service_stage;

        if ($this->isPickup()) {
            return [
                [
                    'key'    => 'taken',
                    'title'  => 'Pesanan Diterima',
                    'icon'   => '🤝',
                    'active' => $status === self::STATUS_TAKEN,
                ],
                [
                    'key'    => 'pickup_otw',
                    'title'  => 'Ke Titik Jemput',
                    'icon'   => '🛵',
                    'active' => $stage === self::STAGE_GOING_TO_PICKUP || ($status === self::STATUS_PARTNER_ON_THE_WAY && !$stage),
                ],
                [
                    'key'    => 'at_pickup',
                    'title'  => 'Tiba di Titik Jemput',
                    'icon'   => '📍',
                    'active' => in_array($stage, [self::STAGE_AT_PICKUP, self::STAGE_WAITING_FOR_CUSTOMER, self::STAGE_ITEM_COLLECTED]) || ($status === self::STATUS_PARTNER_ARRIVED && !$stage),
                ],
                [
                    'key'    => 'delivery_otw',
                    'title'  => 'Antar ke Tujuan',
                    'icon'   => '🚚',
                    'active' => in_array($stage, [self::STAGE_GOING_TO_DESTINATION, self::STAGE_FINAL_APPROACH, self::STAGE_AT_DESTINATION]) || ($status === self::STATUS_IN_PROGRESS && !$stage),
                ],
                [
                    'key'    => 'done',
                    'title'  => 'Selesai',
                    'icon'   => '✅',
                    'active' => in_array($status, [self::STATUS_WAITING_CONFIRMATION, self::STATUS_SELESAI]),
                ],
            ];
        }

        // On-Site Service / Default
        return [
            [
                'key'    => 'taken',
                'title'  => 'Pesanan Diterima',
                'icon'   => '🤝',
                'active' => $status === self::STATUS_TAKEN,
            ],
            [
                'key'    => 'otw',
                'title'  => 'Menuju Lokasi',
                'icon'   => '🛵',
                'active' => $status === self::STATUS_PARTNER_ON_THE_WAY,
            ],
            [
                'key'    => 'arrived',
                'title'  => 'Tiba di Lokasi',
                'icon'   => '📍',
                'active' => $status === self::STATUS_PARTNER_ARRIVED,
            ],
            [
                'key'    => 'in_progress',
                'title'  => 'Pengerjaan Jasa',
                'icon'   => '⚡',
                'active' => $status === self::STATUS_IN_PROGRESS,
            ],
            [
                'key'    => 'done',
                'title'  => 'Selesai',
                'icon'   => '✅',
                'active' => in_array($status, [self::STATUS_WAITING_CONFIRMATION, self::STATUS_SELESAI]),
            ],
        ];
    }

    /**
     * Persentase progres multi-stage (0 - 100).
     */
    public function getMultiStageProgressPercentageAttribute(): int
    {
        return $this->progress_percentage;
    }

    /**
     * Ikon/Emoji penanda status progres.
     */
    public function getProgressIconAttribute(): string
    {
        if ($this->isPickup()) {
            return match($this->service_stage) {
                self::STAGE_GOING_TO_PICKUP      => '🛵',
                self::STAGE_AT_PICKUP            => '📍',
                self::STAGE_WAITING_FOR_CUSTOMER => '⏳',
                self::STAGE_ITEM_COLLECTED       => '📦',
                self::STAGE_GOING_TO_DESTINATION => '🚚',
                self::STAGE_FINAL_APPROACH       => '🏁',
                self::STAGE_AT_DESTINATION       => '🏁',
                default                          => '📦',
            };
        }

        return match($this->status) {
            self::STATUS_MENUNGGU_MITRA            => '🔍',
            self::STATUS_TAKEN                     => '🤝',
            self::STATUS_PARTNER_ON_THE_WAY        => '🛵',
            self::STATUS_PARTNER_ARRIVED           => '📍',
            self::STATUS_IN_PROGRESS               => '⚡',
            self::STATUS_WAITING_CONFIRMATION      => '📸',
            self::STATUS_SELESAI                   => '✅',
            self::STATUS_DIBATALKAN                => '❌',
            self::STATUS_PARTNER_CANCEL_REQUESTED  => '⚠️',
            self::STATUS_CUSTOMER_CANCEL_REQUESTED => '⚠️',
            default                                => '📋',
        };
    }

    /**
     * Ringkasan progres (contoh: "Langkah 3/5: Menuju Lokasi").
     */
    public function getProgressSummaryAttribute(): string
    {
        if ($this->status === self::STATUS_DIBATALKAN) {
            return 'Pesanan Dibatalkan';
        }
        if ($this->status === self::STATUS_PARTNER_CANCEL_REQUESTED || $this->status === self::STATUS_CUSTOMER_CANCEL_REQUESTED) {
            return 'Pengajuan Kendala / Pembatalan';
        }
        if ($this->status === self::STATUS_SELESAI) {
            return 'Pesanan Selesai';
        }

        if ($this->service_stage && $stageLabel = $this->stage_label) {
            return "Tahap: {$stageLabel}";
        }

        $step = $this->progress_step;
        $label = match($step) {
            1 => 'Mencari Rekan Jasa',
            2 => 'Pesanan Diambil',
            3 => 'Menuju Lokasi',
            4 => 'Pelayanan Dalam Proses',
            5 => 'Menunggu Konfirmasi',
            default => 'Diproses',
        };

        return "Langkah {$step}/5: {$label}";
    }

    /**
     * Warna badge status (Tailwind classes) - Bersih: bg-white pada mode terang, dark:bg-gray-800 pada mode gelap, text-primary.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_MENUNGGU_MITRA,
            self::STATUS_TAKEN,
            self::STATUS_PARTNER_ON_THE_WAY,
            self::STATUS_IN_PROGRESS               => 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 border border-primary-200/70 dark:border-gray-700 shadow-2xs',
            self::STATUS_PARTNER_ARRIVED           => 'bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 border border-emerald-200/70 dark:border-gray-700 shadow-2xs',
            self::STATUS_WAITING_CONFIRMATION      => 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 border border-amber-200/70 dark:border-gray-700 shadow-2xs',
            self::STATUS_SELESAI                   => 'bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 border border-emerald-200/70 dark:border-gray-700 shadow-2xs',
            self::STATUS_DIBATALKAN                => 'bg-white dark:bg-gray-800 text-rose-600 dark:text-rose-400 border border-rose-200/70 dark:border-gray-700 shadow-2xs',
            self::STATUS_PARTNER_CANCEL_REQUESTED,
            self::STATUS_CUSTOMER_CANCEL_REQUESTED => 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 border border-amber-200/70 dark:border-gray-700 shadow-2xs',
            default                                => 'bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 border border-gray-200 dark:border-gray-700 shadow-2xs',
        };
    }

    /**
     * Apakah bantuan masih bisa dibatalkan oleh customer sebelum ada mitra.
     */
    public function isCustomerCancellable(): bool
    {
        return $this->status === self::STATUS_MENUNGGU_MITRA;
    }

    /**
     * Anti-Bypass Lock: Cek apakah customer diperbolehkan membatalkan pesanan.
     * Untuk layanan pickup_delivery:
     * - Status MENUNGGU_MITRA -> Boleh (100% Refund)
     * - Sub-stage GOING_TO_PICKUP / AT_PICKUP / WAITING_FOR_CUSTOMER -> Boleh (Kompensasi tahap penjemputan)
     * - Sub-stage ITEM_COLLECTED / GOING_TO_DESTINATION -> Boleh jika D_cancel <= 5 KM dari titik jemput
     * - Sub-stage ITEM_COLLECTED / GOING_TO_DESTINATION dengan D_cancel > 5 KM -> TERKUNCI (Anti-Bypass Lock)
     * - Sub-stage FINAL_APPROACH / AT_DESTINATION -> TERKUNCI
     */
    public function canCustomerCancel(?float $partnerLat = null, ?float $partnerLng = null): bool
    {
        if (!$this->isPickup()) {
            return $this->isCustomerCancellable() || in_array($this->status, [self::STATUS_TAKEN, self::STATUS_PARTNER_ON_THE_WAY, self::STATUS_PARTNER_ARRIVED, self::STATUS_IN_PROGRESS], true);
        }

        if ($this->status === self::STATUS_MENUNGGU_MITRA || !$this->mitra_id) {
            return true;
        }

        if (in_array($this->status, [self::STATUS_SELESAI, self::STATUS_DIBATALKAN], true)) {
            return false;
        }

        // Tahap final approach / tiba di tujuan -> Terkunci demi keselamatan
        if (in_array($this->service_stage, [self::STAGE_FINAL_APPROACH, self::STAGE_AT_DESTINATION], true)) {
            return false;
        }

        // Tahap barang/penumpang sedang diantar
        if (in_array($this->service_stage, [self::STAGE_ITEM_COLLECTED, self::STAGE_GOING_TO_DESTINATION], true) || $this->status === self::STATUS_IN_PROGRESS) {
            $pLat = $partnerLat ?: (float) ($this->partner_current_lat ?: $this->partner_initial_lat ?: 0);
            $pLng = $partnerLng ?: (float) ($this->partner_current_lng ?: $this->partner_initial_lng ?: 0);
            $dCancel = app(\App\Services\GeoService::class)->calculateCancelDistanceAfterPickup($this, $pLat, $pLng);
            $maxPostPickupKm = AppSetting::getPickupDeliveryMaxCancellationDistanceAfterPickup(); // 5.0 KM
            return $dCancel <= $maxPostPickupKm;
        }

        // Status sebelum barang diambil (taken, partner_on_the_way, partner_arrived)
        return in_array($this->status, [self::STATUS_TAKEN, self::STATUS_PARTNER_ON_THE_WAY, self::STATUS_PARTNER_ARRIVED], true);
    }

    /**
     * Cek apakah pesanan pickup_delivery sudah masuk tahap akhir (Final Approach).
     */
    public function isFinalApproach(): bool
    {
        return $this->isPickup() && $this->service_stage === self::STAGE_FINAL_APPROACH;
    }

    /**
     * Cek apakah mitra berhak memicu Customer No-Show (telah tiba di titik jemput & menunggu >= 10 menit).
     */
    public function canPartnerTriggerNoShow(): bool
    {
        if (!$this->isPickup()) {
            return false;
        }

        $isAtPickup = in_array($this->service_stage, [self::STAGE_AT_PICKUP, self::STAGE_WAITING_FOR_CUSTOMER], true) ||
                      $this->status === self::STATUS_PARTNER_ARRIVED;

        if (!$isAtPickup) {
            return false;
        }

        $arrivedTime = $this->partner_arrived_at ?? $this->arrived_at;
        if (!$arrivedTime && is_array($this->stage_history) && !empty($this->stage_history['arrived_at_pickup_at'])) {
            $arrivedTime = \Illuminate\Support\Carbon::parse($this->stage_history['arrived_at_pickup_at']);
        } elseif (is_string($arrivedTime)) {
            $arrivedTime = \Illuminate\Support\Carbon::parse($arrivedTime);
        }

        if (!$arrivedTime) {
            return false;
        }

        $waitMinutes = AppSetting::getPickupDeliveryNoShowWaitMinutes(); // default 10 menit
        return $arrivedTime->copy()->addMinutes($waitMinutes)->isPast();
    }

    /**
     * Apakah bantuan sedang aktif (sedang dikerjakan mitra).
     */
    public function isActive(): bool
    {
        return in_array($this->status, self::activeStatuses());
    }

    /**
     * Apakah bantuan sudah selesai.
     */
    public function isDone(): bool
    {
        return $this->status === self::STATUS_SELESAI;
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
        if ($this->service_fee > 0 || $this->travel_fee > 0) {
            return (float) ($this->service_fee + $this->travel_fee + $this->material_fee);
        }
        return (float) $this->amount;
    }

    /**
     * Hitung total nilai pesanan secara komprehensif (Layanan + Travel + Material + Item Fund + Admin Fee).
     */
    public function getCalculatedTotalAttribute(): float
    {
        if ($this->service_fee > 0 || $this->travel_fee > 0 || $this->item_fund > 0) {
            return (float) ($this->service_fee + $this->travel_fee + $this->material_fee + $this->item_fund + $this->handling_fee + $this->getPlatformFee());
        }
        return (float) ($this->total_amount > 0 ? $this->total_amount : ($this->amount + $this->getPlatformFee()));
    }

    /**
     * Pendapatan bersih mitra (Service Fee + Travel Fee + Material Fee) tanpa item fund.
     */
    public function getNetPartnerEarningAttribute(): float
    {
        return $this->getNetEarning();
    }

    /**
     * Apakah dana belanjaan (item fund) dipisahkan dari pendapatan mitra.
     */
    public function isItemFundSeparated(): bool
    {
        return $this->isPickup() && (float) $this->item_fund > 0;
    }

    /**
     * Label mode pengadaan dana barang belanjaan.
     */
    public function getItemFundModeLabelAttribute(): string
    {
        return match($this->item_fund_mode) {
            self::ITEM_FUND_CUSTOMER_PAID   => 'Dibayar Pemesan di Aplikasi (Dana Tahan)',
            self::ITEM_FUND_PARTNER_ADVANCE => 'Ditalangi Mitra Terlebih Dahulu (Reimburse)',
            self::ITEM_FUND_COD             => 'Bayar Tunai di Tempat (COD)',
            default                         => 'Dalam Aplikasi',
        };
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

