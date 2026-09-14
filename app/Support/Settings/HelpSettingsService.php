<?php

namespace App\Support\Settings;

use App\Models\AppSetting;

class HelpSettingsService
{
    /**
     * Get platform fixed service fee in Rupiah.
     */
    public function getPlatformFee(): float
    {
        return AppSetting::getPlatformServiceFee();
    }

    /**
     * Calculate platform fee breakdown for a given nominal amount.
     */
    public function calculatePlatformFee(float $amount): array
    {
        return AppSetting::calculatePlatformFee($amount);
    }

    /**
     * Auto cancellation deadline in hours.
     */
    public function getAutoCancelHours(): int
    {
        return AppSetting::getHelpAutoCancelHours();
    }

    /**
     * Minimum help nominal allowed.
     */
    public function getMinHelpNominal(): float
    {
        return (float) AppSetting::get(AppSettingKey::MIN_HELP_NOMINAL, 10000.0);
    }

    /**
     * Bank configurations for withdrawal.
     */
    public function getWithdrawBanks(): array
    {
        return AppSetting::getWithdrawBanks();
    }

    /**
     * Calculate withdrawal fee for a specific bank and amount.
     */
    public function calculateWithdrawFee(string $bankCode, int|float $amount = 0): array
    {
        return AppSetting::calculateWithdrawFee($bankCode, $amount);
    }

    /**
     * Offer timeout duration in seconds for matching radar.
     */
    public function getOfferTimeoutSeconds(): int
    {
        return AppSetting::getOfferTimeoutSeconds();
    }

    /**
     * Max consecutive declines allowed before partner cooling off.
     */
    public function getMaxConsecutiveDeclines(): int
    {
        return AppSetting::getMaxConsecutiveDeclines();
    }

    /**
     * Max candidates dispatched per matching wave.
     */
    public function getMaxDispatchCandidates(): int
    {
        return AppSetting::getMaxDispatchCandidates();
    }

    /**
     * Heartbeat TTL in seconds.
     */
    public function getHeartbeatTtlSeconds(): int
    {
        return AppSetting::getHeartbeatTtlSeconds();
    }

    /**
     * Max matching radius in KM.
     */
    public function getMaxMatchingRadiusKm(): float
    {
        return AppSetting::getMaxMatchingRadiusKm();
    }

    /**
     * Max pool radius in KM.
     */
    public function getMaxPoolRadiusKm(): float
    {
        return AppSetting::getMaxPoolRadiusKm();
    }

    /**
     * Matching weights array.
     */
    public function getMatchingWeights(): array
    {
        return AppSetting::getMatchingWeights();
    }

    /**
     * Travel free radius in KM for on-site services.
     */
    public function getTravelFreeRadiusKm(): float
    {
        return AppSetting::getTravelFreeRadiusKm();
    }

    /**
     * Travel base fee.
     */
    public function getTravelBaseFee(): float
    {
        return AppSetting::getTravelBaseFee();
    }

    /**
     * Travel price per KM.
     */
    public function getTravelPricePerKm(): float
    {
        return AppSetting::getTravelPricePerKm();
    }

    /**
     * Pickup delivery base fare.
     */
    public function getPickupDeliveryBaseFare(): float
    {
        return AppSetting::getPickupDeliveryBaseFare();
    }

    /**
     * Pickup delivery price per KM.
     */
    public function getPickupDeliveryPricePerKm(): float
    {
        return AppSetting::getPickupDeliveryPricePerKm();
    }

    /**
     * Pickup delivery max distance in KM.
     */
    public function getPickupDeliveryMaxDistanceKm(): float
    {
        return AppSetting::getPickupDeliveryMaxDistanceKm();
    }

    /**
     * Generic getter with caching.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return AppSetting::get($key, $default);
    }

    /**
     * Generic setter with cache invalidation.
     */
    public function set(string $key, mixed $value): mixed
    {
        return AppSetting::set($key, $value);
    }

    /**
     * Clear all cached settings.
     */
    public function clearCache(): void
    {
        AppSetting::clearCache();
    }
}
