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

    public static function calculateWithdrawFee(string $bankCode, int|float $amount = 0): array
    {
        $banks = static::getWithdrawBanks();
        $codeUpper = strtoupper(trim($bankCode));
        $amount = max(0, (float) $amount);

        $matched = collect($banks)->first(function ($b) use ($codeUpper) {
            return strtoupper($b['code'] ?? '') === $codeUpper;
        });

        if ($matched) {
            $isPlatform = !empty($matched['is_platform_account']);
            // Jika rekening adalah Rekening Platform, biaya admin otomatis Rp 0 (gratis admin) sesuai settingan saat ini
            $fee = $isPlatform ? 0 : (int) ($matched['fee'] ?? 0);
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

    public const MAX_OPERATIONAL_RADIUS_KM = 10.0;

    /**
     * Batas radius matching baku (15.0 KM default, max 100.0 KM).
     */
    public static function getMaxMatchingRadiusKm(): float
    {
        $val = (float) static::get('max_matching_radius_km', 15.0);
        return max(1.0, min(100.0, $val));
    }

    /**
     * Batas radius pool baku (15.0 KM default, max 100.0 KM).
     */
    public static function getMaxPoolRadiusKm(): float
    {
        $val = (float) static::get('max_pool_radius_km', 15.0);
        return max(1.0, min(100.0, $val));
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

    // ─────────────────────────────────────────────────────────────────────────
    // TAHAP 5: REVISI 3 SYSTEM CONFIGURATIONS
    // ─────────────────────────────────────────────────────────────────────────

    public static function getTravelFreeRadiusKm(): float
    {
        return (float) static::get('travel_free_radius_km', 2.0);
    }

    public static function getTravelBaseFee(): float
    {
        return (float) static::get('travel_base_fee', 5000.0);
    }

    public static function getTravelPricePerKm(): float
    {
        return (float) static::get('travel_price_per_km', 2500.0);
    }

    public static function getArrivalRadiusMeters(): float
    {
        return (float) static::get('arrival_radius_meters', 50.0);
    }

    public static function getAcceptableGpsAccuracy(): float
    {
        return (float) static::get('arrival_acceptable_gps_accuracy', 50.0);
    }

    public static function getMovementMinMeters(): float
    {
        return (float) static::get('movement_min_meters', 30.0);
    }

    public static function getAdvanceLimitDefault(): float
    {
        return (float) static::get('advance_limit_default', 100000.0);
    }

    public static function isAdjacentDistrictMatchingEnabled(): bool
    {
        return (bool) static::get('adjacent_district_matching_enabled', true);
    }

    public static function isMatchingSeekingEnabled(?int $cityId = null): bool
    {
        if ($cityId) {
            $cityOverride = City::where('id', $cityId)->value('is_matching_seeking_enabled');
            if ($cityOverride !== null) {
                return (bool) $cityOverride;
            }
        }

        return (bool) static::get('matching_seeking_enabled', true);
    }

    public static function isMatchingSeekingEnabledForUser(?User $user): bool
    {
        if (!$user) {
            return (bool) static::get('matching_seeking_enabled', true);
        }

        $cityId = $user->city_id;

        if (!$cityId) {
            $onlineState = \App\Models\PartnerOnlineState::where('user_id', $user->id)->first();
            if ($onlineState && $onlineState->latitude && $onlineState->longitude) {
                $closestCity = City::where('is_active', true)
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->orderByRaw("(6371 * acos(least(1.0, greatest(-1.0, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))))) ASC", [$onlineState->latitude, $onlineState->longitude, $onlineState->latitude])
                    ->first();
                $cityId = $closestCity?->id;
            }
        }

        return static::isMatchingSeekingEnabled($cityId);
    }

    public static function getPickupDeliveryBaseFare(): float
    {
        return (float) static::get('pickup_delivery.base_fare', 10000.0);
    }

    public static function getPickupDeliveryMaxDistanceKm(): float
    {
        return (float) static::get('pickup_delivery.max_distance_km', 40.0);
    }

    public static function getPickupDeliveryMaxCancellationDistanceAfterPickup(): float
    {
        return (float) static::get('pickup_delivery.cancellation.max_after_pickup_distance_km', 5.0);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TAHAP 6: PICKUP & DELIVERY PRICING & CANCELLATION CONFIGURATION
    // ─────────────────────────────────────────────────────────────────────────

    public static function getPickupDeliveryMinimumFare(): float
    {
        return (float) static::get('pickup_delivery.base_fare', static::get('pickup_delivery.minimum_fare', 10000.0));
    }

    public static function getPickupDeliveryPricePerKm(): float
    {
        return (float) static::get('pickup_delivery.price_per_km', 2500.0);
    }

    public static function getPickupDeliveryLongDistanceThresholdKm(): float
    {
        return (float) static::get('pickup_delivery.long_distance_threshold', 20.0);
    }

    public static function getPickupDeliveryLongDistancePricePerKm(): float
    {
        return (float) static::get('pickup_delivery.long_distance_price_per_km', 2750.0);
    }

    public static function getPickupDeliveryCancellationBaseFare(): float
    {
        return (float) static::get('pickup_delivery.cancellation.base_fare', 10000.0);
    }

    public static function getPickupDeliveryCancellationRatePerKm(): float
    {
        return (float) static::get('pickup_delivery.cancellation.rate_per_km', 2500.0);
    }

    public static function getPickupDeliveryCancellationMinimumAtPickup(): float
    {
        return (float) static::get('pickup_delivery.cancellation.minimum_at_pickup', 15000.0);
    }

    public static function getPickupDeliveryNoShowWaitMinutes(): int
    {
        return (int) static::get('pickup_delivery.cancellation.no_show_wait_minutes', 10);
    }

    public static function getPickupDeliveryFinalApproachProgress(): float
    {
        return (float) static::get('pickup_delivery.cancellation.final_approach_progress', 80.0);
    }

    public static function getPickupDeliveryFinalApproachEtaMinutes(): int
    {
        return (int) static::get('pickup_delivery.cancellation.final_approach_eta_minutes', 10);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TAHAP 7: SCHEDULED ORDERS DEPARTURE WINDOW & REMINDER CONFIGURATION
    // ─────────────────────────────────────────────────────────────────────────

    public static function getScheduledEarlyDepartureWindowMinutes(): int
    {
        $val = (int) static::get('scheduled_early_departure_window_minutes', 60);
        return max(5, min(180, $val));
    }
}


