<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Help;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GeoService
{
    /**
     * Hitung jarak lurus (Straight-line / Haversine) antara 2 titik koordinat (dalam Kilometer).
     */
    public function calculateStraightDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        if ($lat1 == 0 || $lng1 == 0 || $lat2 == 0 || $lng2 == 0) {
            return 0.0;
        }

        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 2);
    }

    /**
     * Hitung jarak antara 2 titik koordinat (dalam Meter).
     */
    public function calculateDistanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        return $this->calculateStraightDistance($lat1, $lng1, $lat2, $lng2) * 1000.0;
    }

    /**
     * Hitung estimasi jarak rute perjalanan jalan (Route Distance dalam KM).
     * Menerapkan fallback estimasi 1.25x dengan pencatatan `route_source` transparan.
     */
    public function getRouteDistance(float $lat1, float $lng1, float $lat2, float $lng2, ?string &$source = null): float
    {
        $straightDist = $this->calculateStraightDistance($lat1, $lng1, $lat2, $lng2);

        if ($straightDist <= 0) {
            $source = Help::ROUTE_SOURCE_FALLBACK;
            return 0.0;
        }

        // LEVEL 1 & Fallback Estimasi: 1.25x road factor
        // Dapat dikembangkan ke Level 2 (Google Maps / OSRM API) saat production provider aktif
        $source = Help::ROUTE_SOURCE_FALLBACK;
        $estimatedRoute = $straightDist * 1.25;

        return round($estimatedRoute, 2);
    }

    /**
     * Hitung estimasi durasi perjalanan (menit) berdasarkan kecepatan rata-rata kendaraan motor di perkotaan.
     */
    public function getRouteDurationMinutes(float $distanceKm, float $avgSpeedKmh = 25.0, int $bufferMinutes = 5): int
    {
        if ($distanceKm <= 0) {
            return $bufferMinutes;
        }

        $travelHours = $distanceKm / max(5.0, $avgSpeedKmh);
        $travelMinutes = (int) ceil($travelHours * 60);

        return $travelMinutes + $bufferMinutes;
    }

    /**
     * Validasi apakah matching distance berada dalam batas baku MAX_MATCHING_DISTANCE (<= 10.0 KM).
     */
    public function isWithinOperationalMatchingDistance(float $lat1, float $lng1, float $lat2, float $lng2, float $maxKm = 10.0): bool
    {
        $dist = $this->calculateStraightDistance($lat1, $lng1, $lat2, $lng2);
        return $dist <= $maxKm;
    }

    /**
     * Verifikasi kedatangan mitra di lokasi tujuan.
     * Menggabungkan Jarak (<= 50 meter) DAN Akurasi GPS (<= 50 meter) untuk mencegah false arrival akibat GPS drift.
     */
    public function isWithinArrivalRadius(
        float $currentLat,
        float $currentLng,
        float $targetLat,
        float $targetLng,
        ?float $gpsAccuracy = null,
        ?float $arrivalRadiusMeters = null,
        ?float $acceptableAccuracyMeters = null
    ): array {
        $radius = $arrivalRadiusMeters ?? AppSetting::getArrivalRadiusMeters();
        $accTolerance = $acceptableAccuracyMeters ?? AppSetting::getAcceptableGpsAccuracy();

        $distanceMeters = $this->calculateDistanceMeters($currentLat, $currentLng, $targetLat, $targetLng);
        $isDistanceClose = $distanceMeters <= $radius;

        $isAccuracyAcceptable = true;
        if ($gpsAccuracy !== null && $gpsAccuracy > 0) {
            $isAccuracyAcceptable = ($gpsAccuracy <= $accTolerance);
        }

        $arrived = $isDistanceClose && $isAccuracyAcceptable;

        $reason = 'valid';
        if ($isDistanceClose && !$isAccuracyAcceptable) {
            $reason = "Jarak memenuhi syarat ({$distanceMeters}m), namun akurasi GPS terlalu rendah (±{$gpsAccuracy}m > {$accTolerance}m). Menunggu sinyal GPS stabil.";
        } elseif (!$isDistanceClose) {
            $reason = "Belum mencapai radius kedatangan ({$distanceMeters}m > {$radius}m).";
        }

        return [
            'arrived'             => $arrived,
            'distance_meters'     => round($distanceMeters, 2),
            'gps_accuracy'        => $gpsAccuracy,
            'accuracy_acceptable' => $isAccuracyAcceptable,
            'reason'              => $reason,
        ];
    }

    /**
     * Deteksi pergerakan nyata (Movement vs Drift) dengan mempertimbangkan Jarak + Waktu + Akurasi.
     * Mencegah false movement trigger jika mitra diam tetapi GPS melompat 30m dalam waktu yang sangat lama.
     */
    public function hasMeaningfulMovement(
        float $prevLat,
        float $prevLng,
        ?Carbon $prevTime,
        float $currLat,
        float $currLng,
        Carbon $currTime,
        ?float $gpsAccuracy = null,
        ?float $minDistanceMeters = null
    ): array {
        $minMeters = $minDistanceMeters ?? AppSetting::getMovementMinMeters();
        $distanceMeters = $this->calculateDistanceMeters($prevLat, $prevLng, $currLat, $currLng);

        if ($distanceMeters < $minMeters) {
            return [
                'has_moved'       => false,
                'distance_meters' => round($distanceMeters, 2),
                'reason'          => 'Perpindahan belum melewati ambang batas pergerakan.',
            ];
        }

        // Validasi kecepatan perpindahan jika ada riwayat waktu
        if ($prevTime) {
            $secondsElapsed = max(1, $prevTime->diffInSeconds($currTime));
            $speedMps = $distanceMeters / $secondsElapsed; // Meter per detik

            // Kecepatan < 0.1 m/s untuk jarak 30m (artinya butuh > 5 menit tanpa gerak nyata) kemungkinan adalah GPS drift
            if ($speedMps < 0.1 && $distanceMeters < ($minMeters * 1.5)) {
                return [
                    'has_moved'       => false,
                    'distance_meters' => round($distanceMeters, 2),
                    'speed_mps'       => round($speedMps, 2),
                    'reason'          => 'Terdeteksi sebagai GPS drift (perubahan lambat tanpa pergerakan dinamis).',
                ];
            }
        }

        return [
            'has_moved'       => true,
            'distance_meters' => round($distanceMeters, 2),
            'reason'          => 'Pergerakan nyata terkonfirmasi.',
        ];
    }

    /**
     * Hitung 3 Konteks Jarak secara terpisah untuk Pesanan Bantuan:
     * 1. Matching Distance (Mitra -> Lokasi Customer/Pickup) <= 10 KM
     * 2. Travel Distance (Mitra -> Titik Awal Layanan)
     * 3. Service Route Distance (Pickup -> Delivery / Mitra -> Toko -> Customer)
     */
    public function calculateThreeDistanceContexts(Help $help, float $partnerLat, float $partnerLng): array
    {
        $matchingDistance = 0.0;
        $travelDistance   = 0.0;
        $serviceRouteDist = 0.0;
        $routeSource      = Help::ROUTE_SOURCE_FALLBACK;

        if ($help->isPickup()) {
            $pickupLat = (float) ($help->pickup_latitude ?: $help->latitude);
            $pickupLng = (float) ($help->pickup_longitude ?: $help->longitude);
            $deliveryLat = (float) ($help->delivery_latitude ?: $help->latitude);
            $deliveryLng = (float) ($help->delivery_longitude ?: $help->longitude);

            // Leg 1: Mitra -> Pickup (Matching & Travel Distance)
            $matchingDistance = $this->calculateStraightDistance($partnerLat, $partnerLng, $pickupLat, $pickupLng);
            $travelDistance   = $this->getRouteDistance($partnerLat, $partnerLng, $pickupLat, $pickupLng, $routeSource);

            // Leg 2: Pickup -> Delivery (Service / Delivery Distance)
            $serviceRouteDist = $this->getRouteDistance($pickupLat, $pickupLng, $deliveryLat, $deliveryLng, $routeSource);
        } elseif ($help->isBuy()) {
            $storeLat = (float) ($help->store_latitude ?: $help->latitude);
            $storeLng = (float) ($help->store_longitude ?: $help->longitude);
            $customerLat = (float) ($help->latitude ?: 0);
            $customerLng = (float) ($help->longitude ?: 0);

            // Leg 1: Mitra -> Toko (Matching & Travel Distance)
            $matchingDistance = $this->calculateStraightDistance($partnerLat, $partnerLng, $storeLat, $storeLng);
            $travelDistance   = $this->getRouteDistance($partnerLat, $partnerLng, $storeLat, $storeLng, $routeSource);

            // Leg 2: Toko -> Customer (Service Distance)
            $serviceRouteDist = $this->getRouteDistance($storeLat, $storeLng, $customerLat, $customerLng, $routeSource);
        } else {
            // On-site Service: Matching Distance = Travel Distance = Mitra -> Customer
            $customerLat = (float) ($help->latitude ?: 0);
            $customerLng = (float) ($help->longitude ?: 0);

            $matchingDistance = $this->calculateStraightDistance($partnerLat, $partnerLng, $customerLat, $customerLng);
            $travelDistance   = $this->getRouteDistance($partnerLat, $partnerLng, $customerLat, $customerLng, $routeSource);
            $serviceRouteDist = 0.0;
        }

        $totalEstimatedDistance = $travelDistance + $serviceRouteDist;
        $durationMinutes = $this->getRouteDurationMinutes($totalEstimatedDistance);

        return [
            'matching_distance_km'      => round($matchingDistance, 2),
            'travel_distance_km'        => round($travelDistance, 2),
            'service_route_distance_km' => round($serviceRouteDist, 2),
            'total_distance_km'         => round($totalEstimatedDistance, 2),
            'route_source'              => $routeSource,
            'estimated_duration_minutes'=> $durationMinutes,
        ];
    }
}
