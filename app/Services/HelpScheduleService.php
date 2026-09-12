<?php

namespace App\Services;

use App\Models\Help;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HelpScheduleService
{
    protected GeoService $geoService;

    public function __construct(GeoService $geoService)
    {
        $this->geoService = $geoService;
    }

    /**
     * Hitung jadwal milestone waktu pesanan berdasarkan Mode (Instant vs Scheduled) dan Jenis Layanan.
     * Mengimplementasikan 3 konsep waktu terpisah:
     * 1. Waktu Buat (created_at)
     * 2. Waktu Publish / Muncul di Pool Mitra (published_at)
     * 3. Waktu Pelaksanaan / Jadwal Kerja (scheduled_at / service_scheduled_at)
     */
    public function computeScheduleTimestamps(
        string $orderMode = Help::ORDER_MODE_INSTANT,
        ?Carbon $targetScheduledAt = null,
        float $travelDistanceKm = 0.0,
        string $serviceType = Help::SERVICE_TYPE_ON_SITE,
        float $serviceRouteDistanceKm = 0.0,
        ?int $earlyDepartureMinutes = null
    ): array {
        $now = now();
        $travelMinutes = $this->geoService->getRouteDurationMinutes($travelDistanceKm, 25.0, 5);
        $deliveryMinutes = $this->geoService->getRouteDurationMinutes($serviceRouteDistanceKm, 25.0, 5);

        if ($orderMode === Help::ORDER_MODE_INSTANT || !$targetScheduledAt || $targetScheduledAt->lte($now)) {
            // Mode Instant: langsung publish dan berangkat sekarang
            $publishedAt = $now;
            $departureAt = $now;
            $serviceScheduledAt = $now->copy()->addMinutes($travelMinutes);

            $pickupScheduledAt = ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY) ? $serviceScheduledAt : null;
            $deliveryDeadlineAt = ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY)
                ? $serviceScheduledAt->copy()->addMinutes($deliveryMinutes)
                : null;

            return [
                'order_mode'              => Help::ORDER_MODE_INSTANT,
                'published_at'            => $publishedAt,
                'departure_at'            => $departureAt,
                'service_scheduled_at'    => $serviceScheduledAt,
                'pickup_scheduled_at'     => $pickupScheduledAt,
                'delivery_deadline_at'    => $deliveryDeadlineAt,
                'estimated_arrival_at'    => $serviceScheduledAt,
                'early_departure_minutes' => null,
            ];
        }

        // Mode Scheduled: Waktu target ditentukan oleh Customer
        $serviceScheduledAt = $targetScheduledAt;
        
        // Hitung published_at dinamis berdasarkan jarak waktu ke jadwal pelaksanaan
        $deltaHours = $now->diffInHours($targetScheduledAt, false);
        if ($deltaHours <= 2) {
            $publishedAt = $now; // Jika kurang dari 2 jam, langsung publish
        } elseif ($deltaHours <= 24) {
            $publishedAt = $targetScheduledAt->copy()->subHours(2); // Jika 2 - 24 jam ke depan, muncul 2 jam sebelumnya
        } else {
            $publishedAt = $targetScheduledAt->copy()->subHours(4); // Jika > 24 jam ke depan, muncul 4 jam sebelumnya
        }

        // Pastikan published_at tidak melewati waktu sekarang jika sudah terlewat
        if ($publishedAt->lt($now)) {
            $publishedAt = $now;
        }

        $leadMinutes = $earlyDepartureMinutes ?: \App\Models\AppSetting::getScheduledEarlyDepartureWindowMinutes();
        $departureAt = $targetScheduledAt->copy()->subMinutes(max($travelMinutes, $leadMinutes));

        if ($departureAt->lt($now)) {
            $departureAt = $now;
        }

        $pickupScheduledAt = null;
        $deliveryDeadlineAt = null;

        if ($serviceType === Help::SERVICE_TYPE_PICKUP_DELIVERY) {
            $pickupScheduledAt = $targetScheduledAt;
            $deliveryDeadlineAt = $targetScheduledAt->copy()->addMinutes($deliveryMinutes);
        }

        return [
            'order_mode'              => Help::ORDER_MODE_SCHEDULED,
            'published_at'            => $publishedAt,
            'departure_at'            => $departureAt,
            'service_scheduled_at'    => $serviceScheduledAt,
            'pickup_scheduled_at'     => $pickupScheduledAt,
            'delivery_deadline_at'    => $deliveryDeadlineAt,
            'estimated_arrival_at'    => $serviceScheduledAt,
            'early_departure_minutes' => $earlyDepartureMinutes,
        ];
    }

    /**
     * Cek apakah mitra sudah harus berangkat menuju lokasi.
     */
    public function isDepartureDue(Help $help): bool
    {
        if (!$help->departure_at) {
            return true;
        }
        return now()->gte($help->departure_at);
    }

    /**
     * Dapatkan data pemantauan perjalanan live mitra (Live Dynamic Travel Progress & ETA):
     * - Titik Target: Lokasi Kerja (On-site), Titik Jemput (Pickup), atau Titik Antar (Delivery)
     * - Jarak Tersisa & Live ETA dinamis berdasarkan pergerakan GPS riil mitra.
     */
    public function getLiveTravelProgress(Help $help): array
    {
        // 1. Tentukan Titik Sasaran Saat Ini
        $targetLat = 0.0;
        $targetLng = 0.0;
        $targetLabel = 'Lokasi Pekerjaan';
        $targetAddress = $help->location ?: $help->full_address;

        if ($help->isPickup()) {
            if (in_array($help->service_stage, [Help::STAGE_GOING_TO_DESTINATION, Help::STAGE_FINAL_APPROACH, Help::STAGE_ITEM_COLLECTED], true)) {
                $targetLat = (float) ($help->delivery_latitude ?: $help->latitude);
                $targetLng = (float) ($help->delivery_longitude ?: $help->longitude);
                $targetLabel = 'Titik 2: Antar (Tujuan)';
                $targetAddress = $help->delivery_address ?: $help->location;
            } else {
                $targetLat = (float) ($help->pickup_latitude ?: $help->latitude);
                $targetLng = (float) ($help->pickup_longitude ?: $help->longitude);
                $targetLabel = 'Titik 1: Jemput (Pickup)';
                $targetAddress = $help->pickup_address ?: $help->location;
            }
        } else {
            // On-Site Service
            $targetLat = (float) ($help->latitude ?: 0);
            $targetLng = (float) ($help->longitude ?: 0);
            $targetLabel = 'Lokasi Pekerjaan';
            $targetAddress = $help->location ?: $help->full_address;
        }

        // 2. Ambil Posisi Terkini Rekan Jasa (GPS)
        $partnerLat = (float) ($help->partner_current_lat ?: $help->partner_initial_lat ?: 0);
        $partnerLng = (float) ($help->partner_current_lng ?: $help->partner_initial_lng ?: 0);

        $isArrived = in_array($help->status, [
            Help::STATUS_PARTNER_ARRIVED,
            Help::STATUS_IN_PROGRESS,
            Help::STATUS_WAITING_CONFIRMATION,
            Help::STATUS_SELESAI,
        ], true);

        // 3. Hitung Live Dynamic Travel ETA
        $etaData = [
            'distance_meters'          => 0,
            'distance_km'              => 0.0,
            'estimated_minutes'        => 0,
            'formatted_distance'       => '--',
            'formatted_eta'            => 'Menunggu GPS Rekan Jasa...',
            'is_arrived'               => $isArrived,
            'is_delayed'               => false,
            'delay_minutes'            => 0,
            'delay_reason'             => null,
        ];

        if ($partnerLat != 0 && $partnerLng != 0 && $targetLat != 0 && $targetLng != 0) {
            $startedMovingAt = $help->partner_started_moving_at ? Carbon::parse($help->partner_started_moving_at) : null;
            $initialDist = (float) ($help->travel_distance_km ?: $help->matching_distance_km ?: 0);

            $etaData = $this->geoService->calculateLiveTravelEta(
                $partnerLat,
                $partnerLng,
                $targetLat,
                $targetLng,
                $startedMovingAt,
                $initialDist
            );

            if ($isArrived) {
                $etaData['is_arrived'] = true;
                $etaData['formatted_eta'] = 'Tiba di lokasi';
            }
        }

        return [
            'target_label'          => $targetLabel,
            'target_address'        => $targetAddress,
            'target_lat'            => $targetLat,
            'target_lng'            => $targetLng,
            'partner_lat'           => $partnerLat,
            'partner_lng'           => $partnerLng,
            'partner_last_seen'     => $help->partner_location_updated_at ? \Carbon\Carbon::parse($help->partner_location_updated_at)->diffForHumans() : null,
            'is_arrived'            => $etaData['is_arrived'],
            'remaining_distance_km' => $etaData['distance_km'],
            'formatted_distance'    => $etaData['formatted_distance'],
            'formatted_eta'         => $etaData['formatted_eta'],
            'estimated_minutes'     => $etaData['estimated_minutes'],
            'is_delayed'            => $etaData['is_delayed'] ?? false,
            'delay_minutes'         => $etaData['delay_minutes'] ?? 0,
            'delay_reason'          => $etaData['delay_reason'] ?? null,
        ];
    }
}
