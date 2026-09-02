<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model
{
    use HasFactory;

    protected $table = 'withdraw_requests';

    protected $fillable = [
        'user_id',
        'amount',
        'admin_fee',
        'net_amount',
        'bank_code',
        'account_number',
        'account_name',
        'proof_of_transfer',
        'status',
        'external_id',
        'description',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'admin_fee' => 'integer',
        'net_amount' => 'integer',
        'processed_at' => 'datetime',
    ];

    public function getEffectiveAdminFeeAttribute(): int
    {
        return (int) ($this->admin_fee ?? 0);
    }

    public function getEffectiveNetAmountAttribute(): int
    {
        if ($this->net_amount && $this->net_amount > 0) {
            return (int) $this->net_amount;
        }
        return (int) ($this->amount ?? 0);
    }

    public function getTotalDeductionAttribute(): int
    {
        return (int) ($this->amount + $this->effective_admin_fee);
    }

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
