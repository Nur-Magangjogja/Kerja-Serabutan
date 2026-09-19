<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpCancelRequest extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const REQUESTER_PARTNER  = 'partner';
    public const REQUESTER_CUSTOMER = 'customer';

    public const ACTION_PARTNER_INCIDENT = 'partner_incident';
    public const ACTION_SWITCH_PARTNER   = 'switch_partner';
    public const ACTION_CUSTOMER_WITHDRAW = 'customer_withdraw';

    public const PARTNER_RESPONSE_PENDING   = 'pending';
    public const PARTNER_RESPONSE_CONFIRMED = 'confirmed';
    public const PARTNER_RESPONSE_REJECTED  = 'rejected';
    public const PARTNER_RESPONSE_EXPIRED   = 'expired';

    public const SP_TARGET_NONE     = 'none';
    public const SP_TARGET_PARTNER  = 'partner';
    public const SP_TARGET_CUSTOMER = 'customer';
    public const SP_TARGET_BOTH     = 'both';

    public const AUDIT_VALID_NO_SP  = 'valid_no_sp';
    public const AUDIT_PENALTY_ISSUED = 'penalty_issued';
    public const AUDIT_REJECTED     = 'rejected';

    public const SETTLEMENT_FULL_REFUND        = 'full_refund';
    public const SETTLEMENT_PARTIAL_SETTLEMENT = 'partial_settlement';
    public const SETTLEMENT_ITEM_SETTLED       = 'item_settled';
    public const SETTLEMENT_NO_REFUND          = 'no_refund';
    public const SETTLEMENT_RELIST_POOL        = 'relist_pool';
    public const SETTLEMENT_PARTNER_UNLINKED_HELD = 'partner_unlinked_held';

    protected $fillable = [
        'help_id',
        'requester_type',
        'action_type',
        'partner_response_type',
        'partner_id',
        'customer_id',
        'district_id',
        'previous_status',
        'previous_stage',
        'reason',
        'notes',
        'evidence_photo',
        'partner_clarification',
        'partner_clarification_photo',
        'partner_clarified_at',
        'partner_response_notes',
        'partner_response_photo',
        'partner_responded_at',
        'item_purchased',
        'item_purchase_amount',
        'work_completed_percentage',
        'd_cancel_km',
        'd_leg1_km',
        'd_compensated_km',
        'compensation_amount',
        'refund_amount',
        'cancellation_stage',
        'partner_start_lat',
        'partner_start_lng',
        'partner_cancel_lat',
        'partner_cancel_lng',
        'partner_moved_km',
        'distance_to_target_km',
        'time_elapsed_minutes',
        'chat_messages_count',
        'partner_last_chat_at',
        'status',
        'settlement_type',
        'refund_amount_customer',
        'payout_amount_mitra',
        'reviewed_by',
        'admin_notes',
        'sp_target',
        'partner_sp_level',
        'partner_sp_reason',
        'customer_sp_level',
        'customer_sp_reason',
        'audit_decision',
        'requested_at',
        'reviewed_at',
        'expires_at',
    ];

    protected $casts = [
        'd_cancel_km'               => 'decimal:2',
        'd_leg1_km'                 => 'decimal:2',
        'd_compensated_km'          => 'decimal:2',
        'compensation_amount'       => 'decimal:2',
        'refund_amount'             => 'decimal:2',
        'partner_start_lat'         => 'float',
        'partner_start_lng'         => 'float',
        'partner_cancel_lat'        => 'float',
        'partner_cancel_lng'        => 'float',
        'partner_moved_km'          => 'decimal:2',
        'distance_to_target_km'     => 'decimal:2',
        'time_elapsed_minutes'      => 'integer',
        'chat_messages_count'       => 'integer',
        'item_purchased'            => 'boolean',
        'item_purchase_amount'      => 'decimal:2',
        'work_completed_percentage' => 'decimal:2',
        'refund_amount_customer'    => 'decimal:2',
        'payout_amount_mitra'       => 'decimal:2',
        'partner_sp_level'          => 'integer',
        'customer_sp_level'         => 'integer',
        'requested_at'              => 'datetime',
        'partner_clarified_at'      => 'datetime',
        'partner_responded_at'      => 'datetime',
        'partner_last_chat_at'      => 'datetime',
        'reviewed_at'               => 'datetime',
        'expires_at'                => 'datetime',
    ];

    public function help()
    {
        return $this->belongsTo(Help::class);
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function user()
    {
        return $this->requestedBy();
    }

    public function requestedBy()
    {
        if ($this->requester_type === self::REQUESTER_CUSTOMER) {
            return $this->belongsTo(User::class, 'customer_id');
        }
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function messages()
    {
        return $this->hasMany(HelpCancelMessage::class, 'help_cancel_request_id');
    }

    public function getRefundAmountAttribute()
    {
        return $this->refund_amount_customer;
    }

    public function getPartnerAmountAttribute()
    {
        return $this->payout_amount_mitra;
    }

    /**
     * Dapatkan ikon representatif sesuai jenis pekerjaan / layanan
     */
    public function getJobIconAttribute(): string
    {
        $help = $this->help;
        if (!$help) {
            return '🛵';
        }

        if ($help->isPickup()) {
            if ($help->isPassenger()) {
                return '🛵';
            }
            return '📦';
        }

        return '🛠️';
    }

    /**
     * Dapatkan label jenis pekerjaan / layanan
     */
    public function getJobLabelAttribute(): string
    {
        $help = $this->help;
        if (!$help) {
            return 'Layanan SayaBantu';
        }

        if ($help->isPickup()) {
            if ($help->isPassenger()) {
                return 'Antar Penumpang';
            }
            return 'Kurir Barang & Dokumen';
        }

        return 'Kerja Serabutan Di Lokasi';
    }

    /**
     * Class CSS container ikon pekerjaan
     */
    public function getJobIconBoxClassAttribute(): string
    {
        $help = $this->help;
        if (!$help || $help->isOnSite()) {
            return 'bg-amber-500/10 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 border-amber-500/20 dark:border-amber-500/30';
        }

        if ($help->isPassenger()) {
            return 'bg-emerald-500/10 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border-emerald-500/20 dark:border-emerald-500/30';
        }

        return 'bg-sky-500/10 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 border-sky-500/20 dark:border-sky-500/30';
    }

    /**
     * Class CSS badge kategori pekerjaan
     */
    public function getJobCategoryBadgeClassAttribute(): string
    {
        $help = $this->help;
        if (!$help || $help->isOnSite()) {
            return 'bg-amber-50 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/60';
        }

        if ($help->isPassenger()) {
            return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800/60';
        }

        return 'bg-sky-50 text-sky-700 dark:bg-sky-950/70 dark:text-sky-300 border-sky-200/80 dark:border-sky-800/60';
    }

    /**
     * Dapatkan label kondisi & tipe pembatalan spesifik
     */
    public function getCancellationTypeLabelAttribute(): string
    {
        $help = $this->help;
        $isPartner = ($this->requester_type === self::REQUESTER_PARTNER);

        if (!$help || $help->isOnSite()) {
            $isKonsep2 = ($isPartner && (
                $this->cancellation_stage === 'in_progress' || 
                $this->previous_status === 'in_progress' || 
                ($help?->status === Help::STATUS_PARTNER_CANCEL_REQUESTED)
            ));

            if ($isKonsep2) {
                return 'Saat Pengerjaan (Konsep 2)';
            }
            if ($isPartner) {
                return 'Kendala Lapangan (Konsep 1)';
            }
            return 'Permohonan Customer';
        }

        $stage = $this->cancellation_stage ?: ($this->previous_stage ?: $help->service_stage);
        return match($stage) {
            'going_to_pickup', Help::STAGE_GOING_TO_PICKUP => 'Menuju Titik Jemput',
            'at_pickup', Help::STAGE_AT_PICKUP, Help::STAGE_WAITING_FOR_CUSTOMER => 'Di Titik Jemput',
            'item_collected', Help::STAGE_ITEM_COLLECTED, 'going_to_destination', Help::STAGE_GOING_TO_DESTINATION => 'Dalam Pengantaran',
            'at_destination', Help::STAGE_AT_DESTINATION, Help::STAGE_FINAL_APPROACH => 'Tiba di Tujuan',
            default => $isPartner ? 'Kendala Pengantaran Driver' : 'Pembatalan Customer'
        };
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast() && $this->isPending();
    }

    protected static function booted()
    {
        static::created(function () {
            \Illuminate\Support\Facades\Cache::increment('active_cancellations_count_version');
        });

        static::updated(function () {
            \Illuminate\Support\Facades\Cache::increment('active_cancellations_count_version');
        });
    }

    /**
     * Menghitung total permintaan pembatalan pending & sengketa escrow aktif
     * untuk keperluan indikator badge di sidebar Admin & Superadmin.
     */
    public static function getPendingReviewsCountForUser(?User $user = null): int
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return 0;
        }

        $isSuperAdmin = in_array($user->role ?? '', ['super_admin', 'superadmin']);
        $version = \Illuminate\Support\Facades\Cache::get('active_cancellations_count_version', 1);

        $saTerritory = $isSuperAdmin ? $user->getActiveSuperadminTerritory() : null;
        $saKeyPart = $isSuperAdmin ? ('sa_' . $saTerritory['type'] . '_' . ($saTerritory['id'] ?? 'all')) : ('admin_' . $user->id . '_' . ($user->getActiveAdminDistrictFilter() ?? 'all'));
        $cacheKey = 'active_cancels_disputes_count_v' . $version . '_' . $saKeyPart;

        return (int) \Illuminate\Support\Facades\Cache::remember($cacheKey, 15, function () use ($user, $isSuperAdmin, $saTerritory) {
            // 1. Permintaan pembatalan tugas yang menunggu audit admin
            $cancelQuery = static::where('status', self::STATUS_PENDING);

            // 2. Sengketa dana escrow yang dibekukan
            $disputeQuery = Help::where('escrow_status', Help::ESCROW_STATUS_DISPUTED_FREEZE);

            if (!$isSuperAdmin) {
                $districtIds = $user->getEffectiveAdminDistrictIds();
                $adminCityId = $user->city_id;

                if (!empty($districtIds)) {
                    $cancelQuery->where(function ($q) use ($districtIds) {
                        $q->whereIn('district_id', $districtIds)
                          ->orWhereHas('help', fn($hq) => $hq->whereIn('district_id', $districtIds));
                    });
                    $disputeQuery->where(function ($q) use ($districtIds) {
                        $q->whereIn('district_id', $districtIds)
                          ->orWhereHas('user', fn($uq) => $uq->whereIn('district_id', $districtIds));
                    });
                } elseif ($adminCityId) {
                    $cancelQuery->whereHas('help', fn($hq) => $hq->where('city_id', $adminCityId));
                    $disputeQuery->where(function ($q) use ($adminCityId) {
                        $q->where('city_id', $adminCityId)
                          ->orWhereHas('user', fn($uq) => $uq->where('city_id', $adminCityId));
                    });
                } else {
                    return 0;
                }
            } else {
                if ($saTerritory && $saTerritory['type'] === 'district' && !empty($saTerritory['id'])) {
                    $dId = (int) $saTerritory['id'];
                    $cancelQuery->where(function ($q) use ($dId) {
                        $q->where('district_id', $dId)
                          ->orWhereHas('help', fn($hq) => $hq->where('district_id', $dId));
                    });
                    $disputeQuery->where(function ($q) use ($dId) {
                        $q->where('district_id', $dId)
                          ->orWhereHas('user', fn($uq) => $uq->where('district_id', $dId));
                    });
                } elseif ($saTerritory && $saTerritory['type'] === 'city' && !empty($saTerritory['id'])) {
                    $cId = (int) $saTerritory['id'];
                    $saDistrictIds = $user->getEffectiveSuperadminDistrictIds();
                    $cancelQuery->where(function ($q) use ($cId, $saDistrictIds) {
                        $q->whereHas('help', fn($hq) => $hq->where('city_id', $cId));
                        if (!empty($saDistrictIds)) {
                            $q->orWhereIn('district_id', $saDistrictIds)
                              ->orWhereHas('help', fn($hq) => $hq->whereIn('district_id', $saDistrictIds));
                        }
                    });
                    $disputeQuery->where(function ($q) use ($cId, $saDistrictIds) {
                        $q->where('city_id', $cId)
                          ->orWhereHas('user', fn($uq) => $uq->where('city_id', $cId));
                        if (!empty($saDistrictIds)) {
                            $q->orWhereIn('district_id', $saDistrictIds)
                              ->orWhereHas('user', fn($uq) => $uq->whereIn('district_id', $saDistrictIds));
                        }
                    });
                }
            }

            return (int) ($cancelQuery->count() + $disputeQuery->count());
        });
    }
}

