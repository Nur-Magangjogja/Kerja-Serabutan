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
    // 1. DYNAMIC PRICING RULES (Kategori + Durasi + Zona Jarak)
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Hitung nilai minimum biaya jasa (minimum_service_fee) secara dinamis
     * berdasarkan jenis pekerjaan, durasi perkiraan, dan zona jarak.
     */
    public function calculateMinimumServiceFee(
        string $serviceType = Help::SERVICE_TYPE_ON_SITE,
        ?string $category = 'general',
        float $durationHours = 1.0,
        float $distanceKm = 0.0
    ): float {
        $duration = max(0.5, $durationHours);

        // Baseline rate per jam berdasarkan kategori pekerjaan
        $hourlyRate = match(strtolower((string) $category)) {
            'moving', 'pindahan', 'angkut'     => 40000.0,
            'cleaning', 'kebersihan', 'cuci'   => 30000.0,
            'repair', 'pertukangan', 'bengkel' => 35000.0,
            'gardening', 'taman', 'kebun'      => 30000.0,
            'technical', 'elektronik', 'it'    => 45000.0,
            'delivery', 'antar_jemput'         => 20000.0,
            'shopping', 'belanja'              => 20000.0,
            default                            => 25000.0,
        };

        // Biaya dasar minimum durasi
        $baseFee = $hourlyRate * $duration;

        // Zona Jarak Jasa (On-site baseline adjustment)
        $distanceAdjustment = 0.0;
        if ($distanceKm > 7.0) {
            $distanceAdjustment = 15000.0;
        } elseif ($distanceKm > 5.0) {
            $distanceAdjustment = 10000.0;
        } elseif ($distanceKm > 2.0) {
            $distanceAdjustment = 5000.0;
        }

        $minFee = $baseFee + $distanceAdjustment;

        // Absolute platform floor: Rp 15.000
        return max(15000.0, round($minFee, -3));
    }

    /**
     * Hitung kompensasi perjalanan mitra (travel_fee).
     * On-Site: 0-2 KM = Rp 0 (Bebas Ongkos).
     */
    public function calculateTravelFee(float $travelDistanceKm, string $serviceType = Help::SERVICE_TYPE_ON_SITE): float
    {
        $freeRadius = AppSetting::getTravelFreeRadiusKm(); // 2.0 KM
        $baseFee    = AppSetting::getTravelBaseFee();       // Rp 5.000
        $ratePerKm  = AppSetting::getTravelPricePerKm();    // Rp 2.500

        if ($travelDistanceKm <= $freeRadius) {
            return 0.0;
        }

        $billableKm = max(0.0, $travelDistanceKm - $freeRadius);
        $travelFee  = $baseFee + ($billableKm * $ratePerKm);

        // Tambahan tier jarak jauh (> 7 KM)
        if ($travelDistanceKm > 7.0) {
            $travelFee += 5000.0;
        }

        return round($travelFee, -2);
    }

    /**
     * Hitung ongkos pengantaran rute layanan (service_route_fee) untuk Pickup & Delivery / Belanja.
     */
    public function calculateServiceDeliveryFee(float $serviceRouteDistanceKm): float
    {
        if ($serviceRouteDistanceKm <= 0) {
            return 0.0;
        }

        $baseDeliveryFee = 8000.0;  // 0 - 2 KM delivery dasar
        $ratePerKm       = 2500.0;

        if ($serviceRouteDistanceKm <= 2.0) {
            return $baseDeliveryFee;
        }

        $extraKm = $serviceRouteDistanceKm - 2.0;
        return round($baseDeliveryFee + ($extraKm * $ratePerKm), -2);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // 2. 2-PHASE PRICING ENGINE
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * FASE 1: Estimasi Awal Pembuatan Pesanan (Sebelum Mitra Ditemukan).
     * Menghitung nilai minimum order, perkiraan kompensasi perjalanan baseline, dan memisahkan item_fund.
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

        // Estimasi jarak layanan awal jika ada koordinat
        $estimatedServiceDist = 0.0;
        if (!empty($params['pickup_latitude']) && !empty($params['delivery_latitude'])) {
            $estimatedServiceDist = $this->geoService->getRouteDistance(
                (float) $params['pickup_latitude'],
                (float) $params['pickup_longitude'],
                (float) $params['delivery_latitude'],
                (float) $params['delivery_longitude']
            );
        }

        $minServiceFee = $this->calculateMinimumServiceFee($serviceType, $category, $durationHours, $estimatedServiceDist);
        $serviceFee    = max($minServiceFee, $inputAmount);

        // Biaya pengantaran awal untuk pickup/buy
        $serviceDeliveryFee = 0.0;
        if ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY || $serviceType === Help::SERVICE_TYPE_BUY_FOR_CUSTOMER) {
            $serviceDeliveryFee = $this->calculateServiceDeliveryFee($estimatedServiceDist);
        }

        // Baseline travel fee estimasi awal (0 untuk On-site sebelum mitra ditemukan)
        $travelFee = $serviceDeliveryFee;

        // Platform fee
        $platformFee = AppSetting::getPlatformServiceFee();

        // Total yang harus didepositkan ke Escrow
        $totalEscrow = $serviceFee + $travelFee + $materialFee;
        if ($itemFundMode === Help::ITEM_FUND_CUSTOMER_PAID && $itemFund > 0) {
            $totalEscrow += $itemFund;
        }

        $totalCustomerPayment = $totalEscrow + $platformFee;
        $mitraEarningEstimate = $serviceFee + $travelFee + $materialFee;

        return [
            'service_type'                 => $serviceType,
            'service_category'             => $category,
            'service_duration_hours'       => $durationHours,
            'service_fee'                  => $serviceFee,
            'travel_fee'                   => $travelFee,
            'material_fee'                 => $materialFee,
            'item_fund'                    => $itemFund,
            'item_fund_mode'               => $itemFundMode,
            'customer_reimbursement_method'=> $reimburseMode,
            'advance_limit'                => $advanceLimit,
            'minimum_service_fee'          => $minServiceFee,
            'minimum_order_value'          => $minServiceFee + $materialFee,
            'platform_fee'                 => $platformFee,
            'total_amount'                 => $totalCustomerPayment,
            'total_escrow'                 => $totalEscrow,
            'mitra_earning'                => $mitraEarningEstimate,
            'service_route_distance_km'    => $estimatedServiceDist,
        ];
    }

    /**
     * FASE 2: Finalisasi Tarif saat Mitra Ditemukan / Mengambil Pesanan.
     * Menghitung jarak riil mitra -> titik awal, memfinalisasi `travel_fee`, dan mengupdate pesanan.
     */
    public function finalizePartnerPricing(Help $help, float $partnerLat, float $partnerLng): array
    {
        $distances = $this->geoService->calculateThreeDistanceContexts($help, $partnerLat, $partnerLng);

        $travelDist       = (float) $distances['travel_distance_km'];
        $serviceRouteDist = (float) $distances['service_route_distance_km'];

        // Hitung kompensasi perjalanan riil mitra menuju titik jemput/kerja
        $travelCompensation = $this->calculateTravelFee($travelDist, $help->service_type ?? Help::SERVICE_TYPE_ON_SITE);

        // Jika layanan adalah Pickup atau Buy, tambahkan ongkos rute pengantaran ke travel fee
        if ($help->isPickup() || $help->isBuy()) {
            $deliveryFee = $this->calculateServiceDeliveryFee($serviceRouteDist);
            $finalTravelFee = $travelCompensation + $deliveryFee;
        } else {
            $finalTravelFee = $travelCompensation;
        }

        // Pertahankan service fee eksisting yang telah disepakati/dibuat customer
        $serviceFee  = (float) ($help->service_fee > 0 ? $help->service_fee : $help->amount);
        $materialFee = (float) ($help->material_fee ?? 0);
        $itemFund    = (float) ($help->item_fund ?? 0);
        $platformFee = $help->getPlatformFee();

        $mitraEarning = $serviceFee + $finalTravelFee + $materialFee;

        $totalAmount = $serviceFee + $finalTravelFee + $materialFee;
        if ($help->item_fund_mode === Help::ITEM_FUND_CUSTOMER_PAID && $itemFund > 0) {
            $totalAmount += $itemFund;
        }
        $totalAmount += $platformFee;

        return [
            'matching_distance_km'      => $distances['matching_distance_km'],
            'travel_distance_km'        => $travelDist,
            'service_route_distance_km' => $serviceRouteDist,
            'route_source'              => $distances['route_source'],
            'estimated_duration_minutes'=> $distances['estimated_duration_minutes'],
            'service_fee'               => $serviceFee,
            'travel_fee'                => $finalTravelFee,
            'material_fee'              => $materialFee,
            'item_fund'                 => $itemFund,
            'mitra_earning'             => $mitraEarning,
            'total_amount'              => $totalAmount,
            'amount'                    => $serviceFee + $finalTravelFee + $materialFee, // backward compatibility
        ];
    }

    /**
     * Memisahkan struktur keuangan secara ketat antara Kas Platform, Pendapatan Mitra, dan Dana Belanjaan Barang (Item Fund).
     */
    public function calculateEscrowAndEarnings(Help $help): array
    {
        $serviceFee   = (float) ($help->service_fee > 0 ? $help->service_fee : $help->amount);
        $travelFee    = (float) ($help->travel_fee ?? 0);
        $materialFee  = (float) ($help->material_fee ?? 0);
        $itemFund     = (float) ($help->item_fund ?? 0);
        $platformFee  = (float) $help->getPlatformFee();

        $mitraEarning = $serviceFee + $travelFee + $materialFee;

        $isItemFundInEscrow = ($help->item_fund_mode === Help::ITEM_FUND_CUSTOMER_PAID && $itemFund > 0);

        return [
            'customer_total_paid' => $help->getCalculatedTotalAttribute(),
            'platform_revenue'    => $platformFee,
            'escrow_service_held' => $mitraEarning,
            'escrow_item_held'    => $isItemFundInEscrow ? $itemFund : 0.0,
            'mitra_net_earning'   => $mitraEarning,
            'partner_advance_req' => ($help->item_fund_mode === Help::ITEM_FUND_PARTNER_ADVANCE) ? $itemFund : 0.0,
        ];
    }
}
