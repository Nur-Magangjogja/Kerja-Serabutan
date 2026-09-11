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

    public function isPlatformAccount(): bool
    {
        $banks = AppSetting::getWithdrawBanks();
        $codeUpper = strtoupper(trim($this->bank_code ?? ''));
        $matched = collect($banks)->first(function ($b) use ($codeUpper) {
            return strtoupper($b['code'] ?? '') === $codeUpper;
        });
        return !empty($matched['is_platform_account']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('pending_withdraws_count_superadmin');
            $ver = (int) \Illuminate\Support\Facades\Cache::get('pending_withdraws_count_version', 1);
            \Illuminate\Support\Facades\Cache::put('pending_withdraws_count_version', $ver + 1, now()->addDays(7));
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('pending_withdraws_count_superadmin');
            $ver = (int) \Illuminate\Support\Facades\Cache::get('pending_withdraws_count_version', 1);
            \Illuminate\Support\Facades\Cache::put('pending_withdraws_count_version', $ver + 1, now()->addDays(7));
        });
    }

    /**
     * Menghitung total permintaan withdraw yang pending (menunggu proses)
     * untuk keperluan indikator badge di sidebar Admin & Superadmin.
     */
    public static function getPendingWithdrawsCountForUser(?User $user = null): int
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return 0;
        }

        $isSuperAdmin = in_array($user->role ?? '', ['super_admin', 'superadmin']);
        $version = \Illuminate\Support\Facades\Cache::get('pending_withdraws_count_version', 1);

        $saTerritory = $isSuperAdmin ? $user->getActiveSuperadminTerritory() : null;
        $saKeyPart = $isSuperAdmin ? ('sa_' . $saTerritory['type'] . '_' . ($saTerritory['id'] ?? 'all')) : ('admin_' . $user->id . '_' . ($user->getActiveAdminDistrictFilter() ?? 'all'));
        $cacheKey = 'pending_withdraws_count_v' . $version . '_' . $saKeyPart;

        return (int) \Illuminate\Support\Facades\Cache::remember($cacheKey, 15, function () use ($user, $isSuperAdmin, $saTerritory) {
            $query = static::where('status', self::STATUS_PENDING);

            if (!$isSuperAdmin) {
                $districtIds = $user->getEffectiveAdminDistrictIds();
                if (!empty($districtIds)) {
                    $query->whereHas('user', fn($q) => $q->whereIn('district_id', $districtIds));
                } else {
                    return 0;
                }
            } else {
                if ($saTerritory && $saTerritory['type'] === 'district' && !empty($saTerritory['id'])) {
                    $dId = (int) $saTerritory['id'];
                    $query->whereHas('user', fn($q) => $q->where('district_id', $dId));
                } elseif ($saTerritory && $saTerritory['type'] === 'city' && !empty($saTerritory['id'])) {
                    $cId = (int) $saTerritory['id'];
                    $saDistrictIds = $user->getEffectiveSuperadminDistrictIds();
                    $query->whereHas('user', function ($q) use ($cId, $saDistrictIds) {
                        if (!empty($saDistrictIds)) $q->whereIn('district_id', $saDistrictIds);
                        if ($cId) $q->orWhere('city_id', $cId);
                    });
                }
            }

            return (int) $query->count();
        });
    }
}

