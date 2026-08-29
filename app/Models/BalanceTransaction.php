<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\BalanceTransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'idempotency_key',
        'user_id',
        'amount',
        'direction',
        'admin_fee',
        'total_payment',
        'type',
        'description',
        'reference_id',
        'reference_type',
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

    public function help()
    {
        return $this->belongsTo(Help::class, 'reference_id');
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

    public function scopePenalty($query)
    {
        return $query->where('type', 'penalty');
    }

    public function scopeEscrowLock($query)
    {
        return $query->where('type', 'escrow_lock');
    }

    public function scopeEarning($query)
    {
        return $query->where('type', 'earning');
    }

    public function scopePlatformFee($query)
    {
        return $query->where('type', 'platform_fee');
    }

    public function scopeRefund($query)
    {
        return $query->where('type', 'refund');
    }

    /**
     * Jenis transaksi yang mengurangi saldo pengguna.
     * Digunakan untuk kalkulasi RecalculateUserBalances dan balance sync.
     */
    public static function debitTypes(): array
    {
        return ['deduction', 'penalty', 'escrow_lock', 'withdraw', 'pg_fee_topup', 'pg_fee_withdraw'];
    }

    /**
     * Jenis transaksi yang menambah saldo pengguna.
     */
    public static function creditTypes(): array
    {
        return ['topup', 'earning', 'refund'];
    }

    /**
     * Jenis transaksi pemasukan kas platform / admin.
     */
    public static function platformIncomeTypes(): array
    {
        return ['platform_fee', 'penalty'];
    }

    /**
     * Jenis transaksi pengeluaran/beban kas platform (cth: fee PG).
     */
    public static function platformExpenseTypes(): array
    {
        return ['pg_fee_topup', 'pg_fee_withdraw'];
    }

    /**
     * Apakah transaksi ini merupakan transaksi internal Kas Platform (bukan akun perorangan).
     */
    public function isPlatformTransaction(): bool
    {
        return $this->type === 'platform_fee' || ($this->user_id === null && in_array($this->type, self::platformIncomeTypes(), true));
    }

    /**
     * Apakah transaksi ini milik akun user yang sudah dihapus dari sistem (audit trail).
     */
    public function isDeletedUser(): bool
    {
        return $this->user_id === null && !$this->isPlatformTransaction();
    }

    /**
     * Nama entitas pemilik transaksi untuk keperluan audit & tampilan UI.
     */
    public function getUserDisplayNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }

        if ($this->isPlatformTransaction()) {
            return 'Kas Platform (Sistem)';
        }

        return 'Pengguna Terhapus (Historis)';
    }

    /**
     * Apakah transaksi ini mengurangi saldo (debit dari sisi user).
     */
    public function getIsDebitAttribute(): bool
    {
        return in_array($this->type, self::debitTypes(), true);
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

    public function getFormattedReferenceAttribute(): string
    {
        $items = [];
        if (!empty($this->order_id)) {
            $items[] = 'Order: ' . $this->order_id;
        }
        if (!empty($this->request_code)) {
            $items[] = 'Kode: ' . $this->request_code;
        }
        if (!empty($this->reference_id)) {
            $items[] = 'ID Bantuan: ' . $this->reference_id;
        }
        if (!empty($this->reference) && !in_array((string)$this->reference, [$this->order_id, $this->request_code, (string)$this->reference_id])) {
            $items[] = 'Ref: ' . $this->reference;
        }

        if (empty($items)) {
            return '—';
        }

        return implode(' | ', $items);
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
