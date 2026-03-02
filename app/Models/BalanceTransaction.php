<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\BalanceTransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'admin_fee',
        'total_payment',
        'type',
        'description',
        'reference_id',
        'order_id',
        'status',
        'processed_at',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_notes',
        'payment_method',
        'proof_of_payment',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'request_code',
        'expired_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'total_payment' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'processed_at' => 'datetime',
        'approved_at' => 'datetime',
        'expired_at' => 'datetime',
    ];



    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeTopup($query)
    {
        return $query->where('type', 'topup');
    }

    public function scopeDeduction($query)
    {
        return $query->where('type', 'deduction');
    }

    public function scopeWaitingApproval($query)
    {
        return $query->where('status', 'waiting_approval');
    }

    public function scopeByCity($query, $cityId)
    {
        return $query->whereHas('user', function ($q) use ($cityId) {
            $q->where('city_id', $cityId);
        });
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Accessors
    public function getProofOfPaymentUrlAttribute()
    {
        return $this->proof_of_payment ? asset('storage/' . $this->proof_of_payment) : null;
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            // Ensure topup transactions always have an order_id
            if (($model->type ?? null) === 'topup') {
                if (empty($model->order_id)) {
                    $userPart = $model->user_id ?? (auth()->id() ?? '0');
                    $ts = time();
                    // Add a short unique suffix to reduce collision risk
                    $suffix = substr(uniqid('', true), -6);
                    $model->order_id = sprintf('TOPUP-%s-%s-%s', $userPart, $ts, $suffix);
                }
            }
        });

        // Normalize status values on save to avoid storing typos like 'panding'
        static::saving(function ($model) {
            if (property_exists($model, 'status') || array_key_exists('status', $model->getAttributes())) {
                $s = strtolower(trim((string) ($model->status ?? '')));

                $map = [
                    // common misspellings -> canonical
                    'panding' => 'pending',
                    'pendding' => 'pending',
                    'pendng' => 'pending',
                    'pending' => 'pending',

                    'complate' => 'completed',
                    'compleatd' => 'completed',
                    'complted' => 'completed',
                    'completed' => 'completed',

                    'failed' => 'failed',
                ];

                if ($s === '') {
                    // leave empty as-is
                } elseif (isset($map[$s])) {
                    $model->status = $map[$s];
                } else {
                    // if unknown, keep original trimmed lowercased value
                    $model->status = $s;
                }
            }
        });
    }
}
