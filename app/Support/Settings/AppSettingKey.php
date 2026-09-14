<?php

namespace App\Support\Settings;

/**
 * Single source of truth for all application setting keys.
 */
class AppSettingKey
{
    // Platform Fees & Limits
    public const PLATFORM_SERVICE_FEE = 'platform_service_fee';
    public const PLATFORM_FIXED_FEE = 'platform_fixed_fee'; // legacy fallback
    public const HELP_AUTO_CANCEL_HOURS = 'help_auto_cancel_hours';
    public const MIN_HELP_NOMINAL = 'min_help_nominal';

    // Withdraw & Financial
    public const WITHDRAW_BANKS_CONFIG = 'withdraw_banks_config';
    public const WITHDRAW_MIN_AMOUNT = 'withdraw_min_amount';
    public const WITHDRAW_DEFAULT_OTHER_FEE = 'withdraw_default_other_fee';
    public const WITHDRAW_FEE_MODE = 'withdraw_fee_mode';

    // Matching & Radar
    public const OFFER_TIMEOUT_SECONDS = 'offer_timeout_seconds';
    public const MAX_CONSECUTIVE_DECLINES = 'max_consecutive_declines';
    public const MAX_DISPATCH_CANDIDATES = 'max_dispatch_candidates';
    public const HEARTBEAT_TTL_SECONDS = 'heartbeat_ttl_seconds';
    public const MAX_MATCHING_RADIUS_KM = 'max_matching_radius_km';
    public const MAX_POOL_RADIUS_KM = 'max_pool_radius_km';
    public const MATCHING_SEEKING_ENABLED = 'matching_seeking_enabled';
    public const ADJACENT_DISTRICT_MATCHING_ENABLED = 'adjacent_district_matching_enabled';

    // Matching Weights
    public const WEIGHT_DISTANCE = 'weight_distance';
    public const WEIGHT_RATING = 'weight_rating';
    public const WEIGHT_RELIABILITY = 'weight_reliability';
    public const WEIGHT_FAIRNESS = 'weight_fairness';
    public const MAX_FAIRNESS_BOOST_MINUTES = 'max_fairness_boost_minutes';

    // Rating & Reputation
    public const NEUTRAL_RATING_PRIOR = 'neutral_rating_prior';
    public const RATING_MIN_VOTES = 'rating_min_votes';

    // Newbie Boost
    public const NEWBIE_BOOST_ENABLED = 'newbie_boost_enabled';
    public const NEWBIE_BOOST_DAYS = 'newbie_boost_days';
    public const NEWBIE_ORDER_THRESHOLD = 'newbie_order_threshold';
    public const NEWBIE_MIN_FAIRNESS_SCORE = 'newbie_min_fairness_score';

    // Capacity & Demand
    public const CAPACITY_HIGH_DEMAND_MIN = 'capacity_high_demand_min';
    public const CAPACITY_LOW_DEMAND_MIN = 'capacity_low_demand_min';
    public const CAPACITY_OVERSUPPLY_UTIL = 'capacity_oversupply_util';

    // Travel & GPS
    public const TRAVEL_FREE_RADIUS_KM = 'travel_free_radius_km';
    public const TRAVEL_BASE_FEE = 'travel_base_fee';
    public const TRAVEL_PRICE_PER_KM = 'travel_price_per_km';
    public const ARRIVAL_RADIUS_METERS = 'arrival_radius_meters';
    public const ARRIVAL_ACCEPTABLE_GPS_ACCURACY = 'arrival_acceptable_gps_accuracy';
    public const MOVEMENT_MIN_METERS = 'movement_min_meters';
    public const ADVANCE_LIMIT_DEFAULT = 'advance_limit_default';

    // Pickup & Delivery
    public const PICKUP_BASE_FARE = 'pickup_delivery.base_fare';
    public const PICKUP_PRICE_PER_KM = 'pickup_delivery.price_per_km';
    public const PICKUP_MAX_DISTANCE_KM = 'pickup_delivery.max_distance_km';
    public const PICKUP_MINIMUM_FARE = 'pickup_delivery.minimum_fare';
    public const PICKUP_LONG_DISTANCE_THRESHOLD = 'pickup_delivery.long_distance_threshold';
    public const PICKUP_LONG_DISTANCE_PRICE_PER_KM = 'pickup_delivery.long_distance_price_per_km';
    public const PICKUP_CANCEL_MAX_AFTER_PICKUP_KM = 'pickup_delivery.cancellation.max_after_pickup_distance_km';
    public const PICKUP_CANCEL_BASE_FARE = 'pickup_delivery.cancellation.base_fare';
    public const PICKUP_CANCEL_RATE_PER_KM = 'pickup_delivery.cancellation.rate_per_km';
    public const PICKUP_CANCEL_MINIMUM_AT_PICKUP = 'pickup_delivery.cancellation.minimum_at_pickup';
    public const PICKUP_CANCEL_NO_SHOW_WAIT_MINUTES = 'pickup_delivery.cancellation.no_show_wait_minutes';
    public const PICKUP_CANCEL_FINAL_APPROACH_PROGRESS = 'pickup_delivery.cancellation.final_approach_progress';
    public const PICKUP_CANCEL_FINAL_APPROACH_ETA_MINUTES = 'pickup_delivery.cancellation.final_approach_eta_minutes';

    // Scheduled Orders
    public const SCHEDULED_EARLY_DEPARTURE_WINDOW_MINUTES = 'scheduled_early_departure_window_minutes';
    public const SCHEDULED_DEPARTURE_REMINDER_MINUTES = 'scheduled_departure_reminder_minutes';
    public const SCHEDULED_MAX_ADVANCE_DAYS = 'scheduled_max_advance_days';
    public const SCHEDULED_MIN_LEAD_TIME_HOURS = 'scheduled_min_lead_time_hours';
}
