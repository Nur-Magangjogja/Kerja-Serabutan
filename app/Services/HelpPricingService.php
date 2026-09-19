<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Help;
use App\Models\User;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

class HelpPricingService
{
    protected GeoService $geoService;

    public function __construct(GeoService $geoService)
    {
        $this->geoService = $geoService;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 1. CANONICAL PRICING RULES (On-Site & Pickup/Delivery)
    // ═════════════════════════════════════════════════════════════════════════

    public const MIN_SERVICE_FEE_ON_SITE     = 10000.0;
    public const BASE_FARE_PICKUP_DEFAULT    = 10000.0;
    public const PRICE_PER_KM_DEFAULT        = 2500.0;
    public const LONG_DIST_THRESHOLD_DEFAULT = 20.0;
    public const LONG_DIST_PRICE_KM_DEFAULT  = 2750.0;
    public const MAX_SERVICE_DISTANCE_KM     = 40.0;
    public const MAX_LEG1_MATCHING_RADIUS_KM = 5.0; // Constraint jarak mitra ke titik awal

    /**
     * Hitung tarif canonical khusus layanan Antar/Jemput (pickup_delivery):
     * Formula:
     * - D <= 20 KM : BASE_FARE (10k) + ceil(D) * PRICE_PER_KM (2.5k)
     * - > 20 KM & <= 40 KM : BASE_FARE (10k) + (20 * PRICE_PER_KM) + ((ceil(D) - 20) * LONG_DIST_PRICE_KM (2.75k))
     *                        = 10.000 + 50.000 + ((ceil(D) - 20) * 2.750) = 60.000 + ((ceil(D) - 20) * 2.750)
     * - D > 40 KM : Melebihi batas jangkauan motor (Ditolak)
     *
     * @param float $distanceKm Jarak jalan raya (road distance) dalam KM
     * @return float Total biaya jasa / route fare
     * @throws InvalidArgumentException jika jarak > 40 KM
     */
    public function calculatePickupDeliveryFare(float $distanceKm): float
    {
        if ($distanceKm > self::MAX_SERVICE_DISTANCE_KM) {
            throw new InvalidArgumentException("Jarak pengantaran ({$distanceKm} KM) melebihi batas maksimal 40 KM untuk armada sepeda motor.");
        }

        $effectiveKm = max(1.0, ceil($distanceKm));
        $baseFare = (float) AppSetting::get('pickup_delivery.base_fare', self::BASE_FARE_PICKUP_DEFAULT);
        $pricePerKm = (float) AppSetting::get('pickup_delivery.price_per_km', self::PRICE_PER_KM_DEFAULT);
        $longDistThreshold = (float) AppSetting::get('pickup_delivery.long_distance_threshold', self::LONG_DIST_THRESHOLD_DEFAULT);
        $longDistPricePerKm = (float) AppSetting::get('pickup_delivery.long_distance_price_per_km', self::LONG_DIST_PRICE_KM_DEFAULT);

        if ($effectiveKm <= $longDistThreshold) {
            return $baseFare + ($effectiveKm * $pricePerKm);
        }

        $standardPortion = $baseFare + ($longDistThreshold * $pricePerKm); // 10.000 + 50.000 = 60.000
        $excessKm = $effectiveKm - $longDistThreshold;
        $longDistPortion = $excessKm * $longDistPricePerKm;

        return $standardPortion + $longDistPortion;
    }

    /**
     * Hitung nilai minimum biaya jasa (minimum_service_fee) untuk masing-masing tipe layanan:
     * - On-Site      : Minimal Rp 10.000
     * - Antar/Jemput : Formula canonical bertingkat (Base Fare + KM)
     */
    public function calculateMinimumServiceFee(
        string $serviceType = Help::SERVICE_TYPE_ON_SITE,
        ?string $category = 'general',
        float $durationHours = 1.0,
        float $distanceKm = 0.0
    ): float {
        if ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY) {
            return $this->calculatePickupDeliveryFare($distanceKm);
        }

        // On-Site Default: Minimal Rp 10.000
        return self::MIN_SERVICE_FEE_ON_SITE;
    }

    /**
     * Hitung kompensasi perjalanan mitra (travel_fee).
     * Leg 1 (Mitra -> Titik Awal) adalah MATCHING CONSTRAINT (Maks 5 KM),
     * bukan biaya tambahan yang ditagihkan kepada customer di awal.
     */
    public function calculateTravelFee(float $travelDistanceKm, string $serviceType = Help::SERVICE_TYPE_ON_SITE): float
    {
        return 0.0;
    }

    /**
     * Hitung ongkos pengantaran rute layanan (service_route_fee) untuk Pickup & Delivery.
     */
    public function calculateServiceDeliveryFee(float $serviceRouteDistanceKm, string $serviceType = Help::SERVICE_TYPE_PICKUP_DELIVERY): float
    {
        if ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY) {
            return $this->calculatePickupDeliveryFare($serviceRouteDistanceKm);
        }

        return self::MIN_SERVICE_FEE_ON_SITE;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 2. 2-PHASE PRICING ENGINE DENGAN IMMUTABLE SNAPSHOTS
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * FASE 1: Estimasi Awal Pembuatan Pesanan (Sebelum Mitra Ditemukan).
     * Menghitung nilai service_amount, memisahkan item_fund (Escrow murni), dan mengunci snapshot audit.
     */
    public function calculateInitialOrderEstimate(array $params): array
    {
        $serviceType   = $params['service_type'] ?? Help::SERVICE_TYPE_ON_SITE;
        $category      = $params['service_category'] ?? 'general';
        $durationHours = (float) ($params['service_duration_hours'] ?? 1.0);
        $inputAmount   = (float) ($params['amount'] ?? 0);
        $materialFee   = (float) ($params['material_fee'] ?? 0);
        $itemFund      = (float) ($params['item_fund'] ?? 0);
        $itemFundMode  = $params['item_fund_mode'] ?? Help::ITEM_FUND_CUSTOMER_PAID;
        $reimburseMode = $params['customer_reimbursement_method'] ?? 'cash';
        $advanceLimit  = (float) ($params['advance_limit'] ?? AppSetting::getAdvanceLimitDefault());

        // 1. Hitung Jarak Rute Layanan (Leg 2: Pickup -> Dest)
        $estimatedServiceDist = 0.0;
        $routeProvider = 'fallback';

        if ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY) {
            if (!empty($params['service_route_distance_km']) || !empty($params['route_distance_km'])) {
                $estimatedServiceDist = (float) ($params['service_route_distance_km'] ?? $params['route_distance_km']);
                $routeProvider = 'road_provider';
            } elseif (!empty($params['pickup_latitude']) && !empty($params['delivery_latitude'])) {
                $estimatedServiceDist = $this->geoService->getRouteDistance(
                    (float) $params['pickup_latitude'],
                    (float) $params['pickup_longitude'],
                    (float) $params['delivery_latitude'],
                    (float) $params['delivery_longitude']
                );
                $routeProvider = 'geo_service';
            }

            if ($estimatedServiceDist > self::MAX_SERVICE_DISTANCE_KM) {
                throw new InvalidArgumentException("Jarak rute pengantaran ({$estimatedServiceDist} KM) melebihi batas maksimal 40 KM.");
            }
        }

        // 2. Minimum Service Fee & Service Fee (Earning Mitra)
        $minServiceFee = $this->calculateMinimumServiceFee($serviceType, $category, $durationHours, $estimatedServiceDist);
        $serviceFee    = ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY) ? $minServiceFee : max($minServiceFee, $inputAmount);

        // 3. Platform Fee Dinamis dari AppSetting (Default Rp 2.000)
        $platformFee = AppSetting::getPlatformServiceFee();

        // 4. Total Pemotongan Saldo Customer (Escrow)
        $totalEscrow = $serviceFee + $materialFee;
        if ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY && $itemFundMode === Help::ITEM_FUND_CUSTOMER_PAID && $itemFund > 0) {
            $totalEscrow += $itemFund;
        }

        $totalCustomerPayment = $totalEscrow + $platformFee;
        $mitraEarningEstimate = $serviceFee + $materialFee;

        // 5. Tentukan Matching Radius Snapshot
        $matchingRadiusSnapshot = 10.0;
        if ($serviceType === Help::SERVICE_TYPE_ON_SITE) {
            $matchingRadiusSnapshot = 10.0;
        } else {
            // Pickup Leg 1 Priority Constraint
            $matchingRadiusSnapshot = self::MAX_LEG1_MATCHING_RADIUS_KM; // 5.0 KM
        }

        $baseFareSnapshot = (float) AppSetting::get('pickup_delivery.base_fare', self::BASE_FARE_PICKUP_DEFAULT);
        $pricePerKmSnapshot = (float) AppSetting::get('pickup_delivery.price_per_km', self::PRICE_PER_KM_DEFAULT);

        return [
            'service_type'                 => $serviceType,
            'service_category'             => $category,
            'service_duration_hours'       => $durationHours,
            'service_fee'                  => $serviceFee,
            'travel_fee'                   => 0.0,
            'material_fee'                 => $materialFee,
            'item_fund'                    => ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY) ? $itemFund : 0.0,
            'item_fund_mode'               => $itemFundMode,
            'customer_reimbursement_method'=> $reimburseMode,
            'advance_limit'                => $advanceLimit,
            'minimum_service_fee'          => $minServiceFee,
            'minimum_order_value'          => $minServiceFee + $materialFee,
            'platform_fee'                 => $platformFee,
            'total_amount'                 => $totalCustomerPayment,
            'total_escrow'                 => $totalEscrow,
            'mitra_earning'                => $mitraEarningEstimate,
            'service_route_distance_km'    => round($estimatedServiceDist, 2),
            // Snapshots
            'base_fare_applied'            => ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY) ? $baseFareSnapshot : 0.0,
            'price_per_km_applied'         => ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY) ? $pricePerKmSnapshot : 0.0,
            'platform_fee_applied'         => $platformFee,
            'estimated_road_distance_km'   => round($estimatedServiceDist, 2),
            'price_per_km_snapshot'        => $pricePerKmSnapshot,
            'minimum_service_snapshot'     => $minServiceFee,
            'matching_radius_snapshot'     => $matchingRadiusSnapshot,
            'platform_fee_snapshot'        => $platformFee,
            'route_provider'               => $routeProvider,
            'pricing_calculated_at'        => now()->toIso8601String(),
        ];
    }

    /**
     * FASE 2: Finalisasi Tarif saat Mitra Ditemukan / Mengambil Pesanan.
     * Mengunci nilai dari snapshot yang telah disetujui tanpa biaya tambahan tersembunyi.
     */
    public function finalizePartnerPricing(Help $help, float $partnerLat, float $partnerLng): array
    {
        $distances = $this->geoService->calculateThreeDistanceContexts($help, $partnerLat, $partnerLng);

        $travelDist       = (float) $distances['travel_distance_km'];
        $serviceRouteDist = (float) $distances['service_route_distance_km'];

        $serviceFee  = (float) ($help->service_fee > 0 ? $help->service_fee : $help->amount);
        $materialFee = (float) ($help->material_fee ?? 0);
        $itemFund    = (float) ($help->item_fund ?? 0);
        $platformFee = (float) $help->getPlatformFee();

        $mitraEarning = $serviceFee + $materialFee;

        $totalAmount = $serviceFee + $materialFee;
        if ($help->isPickup() && $help->item_fund_mode === Help::ITEM_FUND_CUSTOMER_PAID && $itemFund > 0) {
            $totalAmount += $itemFund;
        }
        $totalAmount += $platformFee;

        return [
            'matching_distance_km'      => $distances['matching_distance_km'],
            'travel_distance_km'        => $travelDist,
            'service_route_distance_km' => $serviceRouteDist ?: ($help->service_route_distance_km ?? 0),
            'route_source'              => $distances['route_source'],
            'estimated_duration_minutes'=> $distances['estimated_duration_minutes'],
            'service_fee'               => $serviceFee,
            'travel_fee'                => 0.0,
            'material_fee'              => $materialFee,
            'item_fund'                 => $itemFund,
            'mitra_earning'             => $mitraEarning,
            'total_amount'              => $totalAmount,
            'amount'                    => $serviceFee + $materialFee,
        ];
    }

    /**
     * Memisahkan struktur keuangan secara ketat antara Kas Platform, Pendapatan Mitra, dan Dana Belanjaan / Pengadaan Barang (Item Fund).
     */
    public function calculateEscrowAndEarnings(Help $help): array
    {
        $serviceFee   = (float) ($help->service_fee > 0 ? $help->service_fee : $help->amount);
        $materialFee  = (float) ($help->material_fee ?? 0);
        $itemFund     = (float) ($help->item_fund ?? 0);
        $platformFee  = (float) $help->getPlatformFee();

        $mitraEarning = $serviceFee + $materialFee;
        $isItemFundInEscrow = ($help->isPickup() && $help->item_fund_mode === Help::ITEM_FUND_CUSTOMER_PAID && $itemFund > 0);

        return [
            'customer_total_paid' => (float) ($help->total_amount ?: ($mitraEarning + $platformFee + ($isItemFundInEscrow ? $itemFund : 0))),
            'platform_revenue'    => $platformFee,
            'escrow_service_held' => $mitraEarning,
            'escrow_item_held'    => $isItemFundInEscrow ? $itemFund : 0.0,
            'mitra_net_earning'   => $mitraEarning,
            'partner_advance_req' => ($help->item_fund_mode === Help::ITEM_FUND_PARTNER_ADVANCE) ? $itemFund : 0.0,
        ];
    }
}
