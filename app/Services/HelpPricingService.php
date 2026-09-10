<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Help;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class HelpPricingService
{
    protected GeoService $geoService;

    public function __construct(GeoService $geoService)
    {
        $this->geoService = $geoService;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 1. DYNAMIC PRICING RULES (On-Site, Pickup & Delivery, Titip Beli)
    // ═════════════════════════════════════════════════════════════════════════

    public const PRICE_PER_KM_DEFAULT        = 2000.0;
    public const MIN_SERVICE_FEE_ON_SITE     = 10000.0;
    public const MIN_SERVICE_FEE_PICKUP      = 10000.0; // Minimal Rp 10.000 (untuk jarak <= 4 KM)
    public const MIN_SERVICE_FEE_BUY         = 15000.0;
    public const MAX_LEG1_MATCHING_RADIUS_KM = 5.0; // Constraint jarak mitra ke titik awal

    /**
     * Hitung tarif bertingkat khusus layanan Antar/Jemput (pickup_delivery):
     * - 0 - 4 KM   : Rp 10.000 (Tarif Dasar / Minimum)
     * - > 4 - 20 KM: ceil(jarak) * Rp 2.500 (Tarif Standar)
     * - > 20 KM    : (20 * 2.500) + ((ceil(jarak) - 20) * 2.750) = 50.000 + ((ceil(jarak) - 20) * 2.750)
     */
    public function calculatePickupDeliveryFare(float $distanceKm): float
    {
        $effectiveKm = max(1.0, ceil($distanceKm));
        $minFare = AppSetting::getPickupDeliveryMinimumFare(); // 10000
        $pricePerKm = AppSetting::getPickupDeliveryPricePerKm(); // 2500
        $longDistThreshold = AppSetting::getPickupDeliveryLongDistanceThresholdKm(); // 20
        $longDistPricePerKm = AppSetting::getPickupDeliveryLongDistancePricePerKm(); // 2750

        if ($effectiveKm <= 4.0) {
            return $minFare;
        }

        if ($effectiveKm <= $longDistThreshold) {
            return $effectiveKm * $pricePerKm;
        }

        $standardPortion = $longDistThreshold * $pricePerKm; // 50.000
        $excessKm = $effectiveKm - $longDistThreshold;
        $longDistPortion = $excessKm * $longDistPricePerKm;

        return $standardPortion + $longDistPortion;
    }

    /**
     * Hitung nilai minimum biaya jasa (minimum_service_fee) untuk masing-masing tipe layanan:
     * - On-Site      : Minimal Rp 10.000
     * - Antar/Jemput : Tarif bertingkat (Rp 10.000 untuk <= 4 KM, Rp 2.500/KM untuk 4-20 KM, Rp 2.750/KM untuk > 20 KM)
     * - Titip Beli   : Minimal Rp 15.000 (Rp 2.000 / KM dari rute Toko → Customer + Jasa Titip)
     */
    public function calculateMinimumServiceFee(
        string $serviceType = Help::SERVICE_TYPE_ON_SITE,
        ?string $category = 'general',
        float $durationHours = 1.0,
        float $distanceKm = 0.0
    ): float {
        $pricePerKm = self::PRICE_PER_KM_DEFAULT;

        if ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY) {
            return $this->calculatePickupDeliveryFare($distanceKm);
        }

        if ($serviceType === Help::SERVICE_TYPE_BUY_FOR_CUSTOMER) {
            $distanceFee = ceil(max(0.0, $distanceKm)) * $pricePerKm;
            return max(self::MIN_SERVICE_FEE_BUY, $distanceFee);
        }

        // On-Site Default: Minimal Rp 10.000
        return self::MIN_SERVICE_FEE_ON_SITE;
    }

    /**
     * Hitung kompensasi perjalanan mitra (travel_fee).
     * Pada V3, Leg 1 (Mitra -> Titik Awal) adalah MATCHING CONSTRAINT (Maks 5 KM),
     * bukan biaya tambahan yang ditagihkan kepada customer.
     */
    public function calculateTravelFee(float $travelDistanceKm, string $serviceType = Help::SERVICE_TYPE_ON_SITE): float
    {
        return 0.0;
    }

    /**
     * Hitung ongkos pengantaran rute layanan (service_route_fee) untuk Pickup & Delivery / Belanja.
     */
    public function calculateServiceDeliveryFee(float $serviceRouteDistanceKm, string $serviceType = Help::SERVICE_TYPE_PICKUP_DELIVERY): float
    {
        if ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY) {
            return $this->calculatePickupDeliveryFare($serviceRouteDistanceKm);
        }

        $minFee = self::MIN_SERVICE_FEE_BUY;
        $distanceFee = ceil(max(0.0, $serviceRouteDistanceKm)) * self::PRICE_PER_KM_DEFAULT;
        return max($minFee, $distanceFee);
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

        // 1. Hitung Jarak Rute Layanan (Leg 2: Pickup -> Dest atau Toko -> Cust)
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
        } elseif ($serviceType === Help::SERVICE_TYPE_BUY_FOR_CUSTOMER) {
            $destLat = $params['delivery_latitude'] ?? $params['latitude'] ?? null;
            $destLng = $params['delivery_longitude'] ?? $params['longitude'] ?? null;
            $storeLat = $params['store_latitude'] ?? $params['pickup_latitude'] ?? null;
            $storeLng = $params['store_longitude'] ?? $params['pickup_longitude'] ?? null;

            if (!empty($storeLat) && !empty($destLat)) {
                $estimatedServiceDist = $this->geoService->getRouteDistance(
                    (float) $storeLat,
                    (float) $storeLng,
                    (float) $destLat,
                    (float) $destLng
                );
                $routeProvider = 'geo_service';
            }
        }

        // 2. Minimum Service Fee & Service Fee (Earning Mitra)
        $minServiceFee = $this->calculateMinimumServiceFee($serviceType, $category, $durationHours, $estimatedServiceDist);
        $serviceFee    = ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY) ? $minServiceFee : max($minServiceFee, $inputAmount);

        // 3. Platform Fee Dinamis dari AppSetting (Default Rp 2.000)
        $platformFee = AppSetting::getPlatformServiceFee();

        // 4. Total Pemotongan Saldo Customer (Escrow)
        $totalEscrow = $serviceFee + $materialFee;
        if ($serviceType === Help::SERVICE_TYPE_BUY_FOR_CUSTOMER && $itemFundMode === Help::ITEM_FUND_CUSTOMER_PAID && $itemFund > 0) {
            $totalEscrow += $itemFund;
        }

        $totalCustomerPayment = $totalEscrow + $platformFee;
        $mitraEarningEstimate = $serviceFee + $materialFee;

        // 5. Tentukan Matching Radius Snapshot
        $matchingRadiusSnapshot = 10.0;
        if ($serviceType === Help::SERVICE_TYPE_ON_SITE) {
            if ($serviceFee <= 20000.0) {
                $matchingRadiusSnapshot = 3.0;
            } elseif ($serviceFee <= 50000.0) {
                $matchingRadiusSnapshot = 5.0;
            } else {
                $matchingRadiusSnapshot = 10.0;
            }
        } else {
            // Pickup & Buy Leg 1 Constraint
            $matchingRadiusSnapshot = self::MAX_LEG1_MATCHING_RADIUS_KM; // 5.0 KM
        }

        $pricePerKmSnapshot = ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY)
            ? AppSetting::getPickupDeliveryPricePerKm()
            : self::PRICE_PER_KM_DEFAULT;

        return [
            'service_type'                 => $serviceType,
            'service_category'             => $category,
            'service_duration_hours'       => $durationHours,
            'service_fee'                  => $serviceFee,
            'travel_fee'                   => 0.0,
            'material_fee'                 => $materialFee,
            'item_fund'                    => ($serviceType === Help::SERVICE_TYPE_BUY_FOR_CUSTOMER) ? $itemFund : 0.0,
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
        if ($help->isBuy() && $help->item_fund_mode === Help::ITEM_FUND_CUSTOMER_PAID && $itemFund > 0) {
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
     * Memisahkan struktur keuangan secara ketat antara Kas Platform, Pendapatan Mitra, dan Dana Belanjaan Barang (Item Fund).
     */
    public function calculateEscrowAndEarnings(Help $help): array
    {
        $serviceFee   = (float) ($help->service_fee > 0 ? $help->service_fee : $help->amount);
        $materialFee  = (float) ($help->material_fee ?? 0);
        $itemFund     = (float) ($help->item_fund ?? 0);
        $platformFee  = (float) $help->getPlatformFee();

        $mitraEarning = $serviceFee + $materialFee;
        $isItemFundInEscrow = ($help->isBuy() && $help->item_fund_mode === Help::ITEM_FUND_CUSTOMER_PAID && $itemFund > 0);

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
