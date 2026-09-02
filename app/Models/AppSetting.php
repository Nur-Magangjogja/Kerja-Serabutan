<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $table = 'app_settings';
    protected $fillable = ['key', 'value'];

    const CACHE_KEY = 'app_settings';
    const CACHE_TTL_SECONDS = 300; // 5 Menit

    /**
     * @var array<string, mixed>|null Static in-memory memoization per request lifecycle
     */
    protected static ?array $memoizedSettings = null;

    /**
     * Ambil seluruh konfigurasi aplikasi sebagai dictionary [key => value]
     * yang di-cache di sistem Cache Laravel dan di-memoize di RAM PHP.
     *
     * @return array<string, mixed>
     */
    public static function allSettings(): array
    {
        if (static::$memoizedSettings !== null) {
            return static::$memoizedSettings;
        }

        try {
            static::$memoizedSettings = \Illuminate\Support\Facades\Cache::remember(
                static::CACHE_KEY,
                static::CACHE_TTL_SECONDS,
                function () {
                    return static::pluck('value', 'key')->toArray();
                }
            );
        } catch (\Throwable $e) {
            try {
                static::$memoizedSettings = static::pluck('value', 'key')->toArray();
            } catch (\Throwable $e2) {
                static::$memoizedSettings = [];
            }
        }

        return static::$memoizedSettings ?? [];
    }

    /**
     * Ambil nilai konfigurasi berdasarkan key.
     * 100% bebas dari query database berulang.
     */
    public static function get($key, $default = null)
    {
        $settings = static::allSettings();

        if (array_key_exists($key, $settings) && $settings[$key] !== null) {
            return $settings[$key];
        }

        return $default;
    }

    /**
     * Simpan / perbarui konfigurasi dan otomatis bersihkan cache.
     */
    public static function set($key, $value)
    {
        $valString = is_array($value) ? json_encode($value) : (string) $value;

        $record = static::updateOrCreate(
            ['key' => $key],
            ['value' => $valString]
        );

        // Invalidate Cache
        static::clearCache();

        return $record;
    }

    /**
     * Bersihkan cache sistem untuk konfigurasi aplikasi.
     */
    public static function clearCache(): void
    {
        static::$memoizedSettings = null;
        try {
            \Illuminate\Support\Facades\Cache::forget(static::CACHE_KEY);
        } catch (\Throwable $e) {
            // ignore
        }
    }

    /**
     * Alias untuk clearCache
     */
    public static function clearRuntimeCache(): void
    {
        static::clearCache();
    }

    /** Platform Service Fee Helpers (Nilai Pajak Layanan Tetap Rp) */
    public static function getPlatformServiceFee(): float
    {
        $val = static::get('platform_service_fee');
        if ($val !== null && $val !== '') {
            return max(0, (float) $val);
        }
        $legacyFixed = static::get('platform_fixed_fee');
        if ($legacyFixed !== null && $legacyFixed !== '') {
            return max(0, (float) $legacyFixed);
        }
        return 2000.0;
    }

    /** Batas Waktu Pembatalan Otomatis (Jam) */
    public static function getHelpAutoCancelHours(): int
    {
        return max(1, (int) static::get('help_auto_cancel_hours', 24));
    }

    public static function calculatePlatformFee(float $amount): array
    {
        $fee = static::getPlatformServiceFee();
        $label = 'Rp ' . number_format($fee, 0, ',', '.');

        return [
            'type'       => 'fixed',
            'rate'       => 0.0,
            'fee_amount' => $fee,
            'label'      => $label,
            'total'      => $amount + $fee,
        ];
    }

    /** Default list of banks & e-wallets with BI-FAST and platform account configuration */
    public static function getDefaultWithdrawBanks(): array
    {
        return [
            ['code' => 'BCA', 'name' => 'Bank Central Asia (BCA)', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 0, 'is_platform_account' => true, 'is_active' => true],
            ['code' => 'BRI', 'name' => 'Bank Rakyat Indonesia (BRI)', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'BNI', 'name' => 'Bank Negara Indonesia (BNI)', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'MANDIRI', 'name' => 'Bank Mandiri', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 0, 'is_platform_account' => true, 'is_active' => true],
            ['code' => 'BSI', 'name' => 'Bank Syariah Indonesia (BSI)', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'CIMB', 'name' => 'CIMB Niaga', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'PERMATA', 'name' => 'Bank Permata', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'DANAMON', 'name' => 'Bank Danamon', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'SEABANK', 'name' => 'SeaBank Indonesia', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'JAGO', 'name' => 'Bank Jago', 'category' => 'Bank', 'icon' => '🏦', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'DANA', 'name' => 'DANA', 'category' => 'E-Wallet', 'icon' => '📱', 'fee' => 0, 'is_platform_account' => true, 'is_active' => true],
            ['code' => 'GOPAY', 'name' => 'GoPay', 'category' => 'E-Wallet', 'icon' => '📱', 'fee' => 1000, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'OVO', 'name' => 'OVO', 'category' => 'E-Wallet', 'icon' => '📱', 'fee' => 1000, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'SHOPEEPAY', 'name' => 'ShopeePay', 'category' => 'E-Wallet', 'icon' => '📱', 'fee' => 1000, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'LINKAJA', 'name' => 'LinkAja', 'category' => 'E-Wallet', 'icon' => '📱', 'fee' => 1000, 'is_platform_account' => false, 'is_active' => true],
            ['code' => 'OTHER', 'name' => 'Bank / E-Wallet Lainnya', 'category' => 'Lainnya', 'icon' => '💳', 'fee' => 2500, 'is_platform_account' => false, 'is_active' => true],
        ];
    }

    public static function getWithdrawBanks(): array
    {
        $raw = static::get('withdraw_banks_config');
        if ($raw) {
            $decoded = is_array($raw) ? $raw : json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }
        return static::getDefaultWithdrawBanks();
    }

    public static function getWithdrawMinAmount(): int
    {
        return (int) static::get('withdraw_min_amount', 10000);
    }

    public static function getWithdrawDefaultFee(): int
    {
        return (int) static::get('withdraw_default_other_fee', 2500);
    }

    public static function getWithdrawFeeMode(): string
    {
        return (string) static::get('withdraw_fee_mode', 'deduct_from_received');
    }

    public static function calculateWithdrawFee(string $bankCode, int $amount = 0): array
    {
        $banks = static::getWithdrawBanks();
        $codeUpper = strtoupper(trim($bankCode));

        $matched = collect($banks)->first(function ($b) use ($codeUpper) {
            return strtoupper($b['code'] ?? '') === $codeUpper;
        });

        if ($matched) {
            $fee = (int) ($matched['fee'] ?? 0);
            $isPlatform = !empty($matched['is_platform_account']);
            $bankName = $matched['name'] ?? $bankCode;
            $bankIcon = $matched['icon'] ?? '🏦';
        } else {
            $fee = static::getWithdrawDefaultFee();
            $isPlatform = false;
            $bankName = $bankCode;
            $bankIcon = '💳';
        }

        // Biaya admin ditambahkan dari pemotongan saldo (dana masuk rekening utuh sesuai nominal yang ditarik)
        $netAmount = max(0, $amount);
        $totalDeduction = $amount + $fee;

        return [
            'fee' => $fee,
            'is_platform_account' => $isPlatform,
            'bank_name' => $bankName,
            'bank_icon' => $bankIcon,
            'net_amount' => $netAmount,
            'total_deduction' => $totalDeduction,
            'fee_mode' => 'deduct_from_balance',
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TAHAP 4: TYPED MATCHING & FAIRNESS CONFIGURATION
    // ─────────────────────────────────────────────────────────────────────────

    public static function getOfferTimeoutSeconds(): int
    {
        $val = (int) static::get('offer_timeout_seconds', 45);
        return max(15, min(600, $val));
    }

    public static function getMaxConsecutiveDeclines(): int
    {
        $val = (int) static::get('max_consecutive_declines', 2);
        return max(1, min(10, $val));
    }

    public static function getMaxDispatchCandidates(): int
    {
        $val = (int) static::get('max_dispatch_candidates', 5);
        return max(1, min(30, $val));
    }

    public static function getHeartbeatTtlSeconds(): int
    {
        $val = (int) static::get('heartbeat_ttl_seconds', 60);
        return max(30, min(300, $val));
    }

    public static function getMaxMatchingRadiusKm(): float
    {
        $val = (float) static::get('max_matching_radius_km', 15.0);
        return max(1.0, min(100.0, $val));
    }

    public static function getMaxPoolRadiusKm(): float
    {
        $val = (float) static::get('max_pool_radius_km', 60.0);
        return max(1.0, min(150.0, $val));
    }

    public static function getNeutralRatingPrior(): float
    {
        $val = (float) static::get('neutral_rating_prior', 4.5);
        return max(3.0, min(5.0, $val));
    }

    public static function getRatingMinVotes(): int
    {
        $val = (int) static::get('rating_min_votes', 5);
        return max(1, min(50, $val));
    }

    public static function getMatchingWeights(): array
    {
        $wDist = (float) static::get('weight_distance', 0.35);
        $wRate = (float) static::get('weight_rating', 0.30);
        $wRel  = (float) static::get('weight_reliability', 0.25);
        $wFair = (float) static::get('weight_fairness', 0.10);

        // Normalize if total sum deviates from 1.0
        $sum = $wDist + $wRate + $wRel + $wFair;
        if ($sum <= 0) {
            return [
                'distance'    => 0.35,
                'rating'      => 0.30,
                'reliability' => 0.25,
                'fairness'    => 0.10,
            ];
        }

        return [
            'distance'    => round($wDist / $sum, 4),
            'rating'      => round($wRate / $sum, 4),
            'reliability' => round($wRel / $sum, 4),
            'fairness'    => round($wFair / $sum, 4),
        ];
    }

    public static function getMaxFairnessBoostMinutes(): float
    {
        $val = (float) static::get('max_fairness_boost_minutes', 60.0);
        return max(10.0, min(240.0, $val));
    }

    public static function isNewbieBoostEnabled(): bool
    {
        return (bool) static::get('newbie_boost_enabled', true);
    }

    public static function getNewbieBoostDays(): int
    {
        $val = (int) static::get('newbie_boost_days', 7);
        return max(1, min(30, $val));
    }

    public static function getNewbieOrderThreshold(): int
    {
        $val = (int) static::get('newbie_order_threshold', 3);
        return max(1, min(20, $val));
    }

    public static function getNewbieMinFairnessScore(): float
    {
        $val = (float) static::get('newbie_min_fairness_score', 0.50);
        return max(0.1, min(1.0, $val));
    }

    public static function getCapacityHighDemandMin(): float
    {
        return (float) static::get('capacity_high_demand_min', 15.0);
    }

    public static function getCapacityLowDemandMin(): float
    {
        return (float) static::get('capacity_low_demand_min', 5.0);
    }

    public static function getCapacityOversupplyUtil(): float
    {
        return (float) static::get('capacity_oversupply_util', 30.0);
    }
}
