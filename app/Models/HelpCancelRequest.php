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

    protected $fillable = [
        'help_id',
        'requester_type',
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
        'item_purchased',
        'item_purchase_amount',
        'work_completed_percentage',
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
        'item_purchased'            => 'boolean',
        'item_purchase_amount'      => 'decimal:2',
        'work_completed_percentage' => 'decimal:2',
        'refund_amount_customer'    => 'decimal:2',
        'payout_amount_mitra'       => 'decimal:2',
        'partner_sp_level'          => 'integer',
        'customer_sp_level'         => 'integer',
        'requested_at'              => 'datetime',
        'partner_clarified_at'      => 'datetime',
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

    public function getRefundAmountAttribute()
    {
        return $this->refund_amount_customer;
    }

    public function getPartnerAmountAttribute()
    {
        return $this->payout_amount_mitra;
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
}
