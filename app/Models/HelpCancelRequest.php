<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpCancelRequest extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const SETTLEMENT_FULL_REFUND        = 'full_refund';
    public const SETTLEMENT_PARTIAL_SETTLEMENT = 'partial_settlement';
    public const SETTLEMENT_ITEM_SETTLED       = 'item_settled';
    public const SETTLEMENT_NO_REFUND          = 'no_refund';

    protected $fillable = [
        'help_id',
        'partner_id',
        'district_id',
        'previous_status',
        'previous_stage',
        'reason',
        'notes',
        'evidence_photo',
        'item_purchased',
        'item_purchase_amount',
        'work_completed_percentage',
        'status',
        'settlement_type',
        'refund_amount_customer',
        'payout_amount_mitra',
        'reviewed_by',
        'admin_notes',
        'requested_at',
        'reviewed_at',
    ];

    protected $casts = [
        'item_purchased'            => 'boolean',
        'item_purchase_amount'      => 'decimal:2',
        'work_completed_percentage' => 'decimal:2',
        'refund_amount_customer'    => 'decimal:2',
        'payout_amount_mitra'       => 'decimal:2',
        'requested_at'              => 'datetime',
        'reviewed_at'               => 'datetime',
    ];

    public function help()
    {
        return $this->belongsTo(Help::class);
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function requestedBy()
    {
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
}
