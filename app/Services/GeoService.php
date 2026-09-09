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
     * Hitung estimasi jarak rute perjalanan jalan raya (Road Route Distance dalam KM).
     * Menggunakan OSRM Driving Routing API mengikuti kontur jalan nyata, dengan fallback faktor jalan (1.30x).
     */
    public function getRouteDistance(float $lat1, float $lng1, float $lat2, float $lng2, ?string &$source = null): float
    {
        $straightDist = $this->calculateStraightDistance($lat1, $lng1, $lat2, $lng2);

        if ($straightDist <= 0) {
            $source = Help::ROUTE_SOURCE_FALLBACK;
            return 0.0;
        }

        // 1. Coba hitung jarak rute jalan nyata via OSRM Driving API (dengan cache per koordinat)
        $cacheKey = 'road_dist_' . round($lat1, 5) . '_' . round($lng1, 5) . '_' . round($lat2, 5) . '_' . round($lng2, 5);
        
        try {
            $roadDist = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($lat1, $lng1, $lat2, $lng2) {
                $url = "https://router.project-osrm.org/route/v1/driving/{$lng1},{$lat1};{$lng2},{$lat2}?overview=false";
                $res = \Illuminate\Support\Facades\Http::timeout(2)->get($url);
                if ($res->successful()) {
                    $data = $res->json();
                    if (isset($data['routes'][0]['distance'])) {
                        return round((float)$data['routes'][0]['distance'] / 1000.0, 2);
                    }
                }
                return null;
            });

            if ($roadDist !== null && $roadDist > 0) {
                $source = 'osrm_road_network';
                return $roadDist;
            }
        } catch (\Throwable $e) {
            // Log or fallback to road curvature factor
        }

        // 2. Fallback Estimasi Jalur Jalan: 1.30x faktor belokan jalan perkotaan
        $source = Help::ROUTE_SOURCE_FALLBACK;
        $estimatedRoute = $straightDist * 1.30;

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
     * Hitung Live Dynamic Travel ETA dari posisi mitra saat ini menuju lokasi target.
     * Mengembalikan metrik jarak tersisa, estimasi durasi menit, dan label tampilan format manusia.
     */
    public function calculateLiveTravelEta(float $currentLat, float $currentLng, float $targetLat, float $targetLng): array
    {
        if ($currentLat == 0 || $currentLng == 0 || $targetLat == 0 || $targetLng == 0) {
            return [
                'distance_meters'          => 0,
                'distance_km'              => 0.0,
                'estimated_minutes'        => 0,
                'formatted_distance'       => '--',
                'formatted_eta'            => 'Menunggu GPS...',
                'is_arrived'               => false,
            ];
        }

        $distMeters = $this->calculateDistanceMeters($currentLat, $currentLng, $targetLat, $targetLng);
        $distKm     = round($distMeters / 1000.0, 2);

        // Jika sudah <= 50 meter, dianggap tiba
        if ($distMeters <= 50.0) {
            return [
                'distance_meters'          => round($distMeters, 1),
                'distance_km'              => $distKm,
                'estimated_minutes'        => 0,
                'formatted_distance'       => round($distMeters) . ' m',
                'formatted_eta'            => 'Tiba di lokasi',
                'is_arrived'               => true,
            ];
        }

        // Estimasi durasi berkendara motor dengan kecepatan perkotaan
        $durationMinutes = $this->getRouteDurationMinutes($distKm, 25.0, 2);

        $formattedDistance = ($distKm < 1.0)
            ? round($distMeters) . ' meter'
            : number_format($distKm, 1, ',', '.') . ' KM';

        $formattedEta = ($durationMinutes <= 2)
            ? '< 2 Menit (Hampir Tiba)'
            : "~{$durationMinutes} Menit";

        return [
            'distance_meters'          => round($distMeters, 1),
            'distance_km'              => $distKm,
            'estimated_minutes'        => $durationMinutes,
            'formatted_distance'       => $formattedDistance,
            'formatted_eta'            => $formattedEta,
            'is_arrived'               => false,
        ];
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

    /**
     * Validasi Keamanan & Kelayakan Wilayah Titik Lokasi Peta (Restricted / Forbidden Zones Check).
     * Memeriksa apakah koordinat:
     * 1. Berada dalam batas teritori Indonesia (Latitude: -11.5 s/d 6.5, Longitude: 94.5 s/d 141.5).
     * 2. Bukan berada di perairan terbuka / laut lepas / danau tanpa akses darat.
     * 3. Bukan berada di zona militer tertutup atau kawasan bahaya terlarang.
     */
    public function validateLocationSafety(float $lat, float $lng, ?array $osmDetails = null): array
    {
        // 1. Validasi Bounding Box Teritori Indonesia
        // Batas wilayah Indonesia: Lat: 6.5 N s/d -11.5 S, Lng: 94.5 E s/d 141.5 E
        if ($lat > 6.5 || $lat < -11.5 || $lng < 94.5 || $lng > 141.5) {
            return [
                'is_safe' => false,
                'reason'  => 'Titik lokasi berada di luar batas wilayah Republik Indonesia.',
            ];
        }

        // 2. Validasi Klasifikasi OSM Data jika tersedia
        if ($osmDetails) {
            $category = strtolower($osmDetails['category'] ?? $osmDetails['class'] ?? '');
            $type     = strtolower($osmDetails['type'] ?? '');
            $country  = strtolower($osmDetails['address']['country_code'] ?? '');

            if (!empty($country) && $country !== 'id') {
                return [
                    'is_safe' => false,
                    'reason'  => 'Titik lokasi terdeteksi berada di luar wilayah Indonesia.',
                ];
            }

            // Perairan terbuka / lautan / perairan bebas
            $forbiddenWaterTypes = ['water', 'sea', 'ocean', 'bay', 'coastline', 'beach', 'strait', 'lake', 'riverbank'];
            if ($category === 'natural' && in_array($type, $forbiddenWaterTypes, true)) {
                return [
                    'is_safe' => false,
                    'reason'  => 'Titik lokasi terdeteksi berada di area perairan / lautan yang tidak dapat diakses rekan jasa.',
                ];
            }

            if ($category === 'waterway' || $type === 'waterway') {
                return [
                    'is_safe' => false,
                    'reason'  => 'Titik lokasi terdeteksi berada di perairan sungai / kanal.',
                ];
            }

            // Zona militer khusus / zona bahaya
            $forbiddenMilitaryTypes = ['military', 'barracks', 'danger_area', 'airfield', 'naval_base'];
            if ($category === 'military' || in_array($type, $forbiddenMilitaryTypes, true)) {
                return [
                    'is_safe' => false,
                    'reason'  => 'Titik lokasi terdeteksi berada di zona instalasi militer / area terbatas khusus.',
                ];
            }
        }

        return [
            'is_safe' => true,
            'reason'  => null,
        ];
    }
}
