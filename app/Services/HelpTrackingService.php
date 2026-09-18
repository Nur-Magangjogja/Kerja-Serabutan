<?php

namespace App\Services;

use App\Models\Help;
use App\Models\User;
use App\Notifications\HelpStatusNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HelpTrackingService
{
    protected GeoService $geoService;

    public function __construct(GeoService $geoService)
    {
        $this->geoService = $geoService;
    }

    /**
     * Memulai perjalanan mitra menuju titik jemput / lokasi kerja.
     */
    public function startJourney(Help $help, float $initialLat, float $initialLng, ?float $gpsAccuracy = null): Help
    {
        return DB::transaction(function () use ($help, $initialLat, $initialLng, $gpsAccuracy) {
            $locked = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            $nextStage = $locked->isPickup() ? Help::STAGE_GOING_TO_PICKUP : null;

            $locked->update([
                'status'                    => Help::STATUS_PARTNER_ON_THE_WAY,
                'service_stage'             => $nextStage,
                'partner_initial_lat'       => $initialLat,
                'partner_initial_lng'       => $initialLng,
                'partner_current_lat'       => $initialLat,
                'partner_current_lng'       => $initialLng,
                'gps_accuracy'              => $gpsAccuracy,
                'partner_started_moving_at' => now(),
                'partner_started_at'        => now(),
                'partner_location_updated_at' => now(),
            ]);

            $this->notifyCustomer($locked, 'partner_on_the_way');
            return $locked;
        });
    }

    /**
     * Update koordinat GPS mitra dengan filter anti-drift dan validasi akurasi kedatangan.
     */
    public function updatePartnerLocation(Help $help, float $currentLat, float $currentLng, ?float $gpsAccuracy = null): array
    {
        if (!$help->isActive()) {
            return [
                'success' => false,
                'message' => 'Pesanan tidak dalam status aktif untuk pelacakan.',
            ];
        }

        $now = now();
        $prevLat  = (float) ($help->partner_current_lat ?: $currentLat);
        $prevLng  = (float) ($help->partner_current_lng ?: $currentLng);
        $prevTime = $help->partner_location_updated_at ? Carbon::parse($help->partner_location_updated_at) : null;

        // 1. Verifikasi pergerakan nyata (Movement vs Drift)
        $movementCheck = $this->geoService->hasMeaningfulMovement(
            $prevLat,
            $prevLng,
            $prevTime,
            $currentLat,
            $currentLng,
            $now,
            $gpsAccuracy
        );

        $statusChanged = false;
        $oldStatus = $help->status;
        $oldStage  = $help->service_stage;

        // Update koordinat terkini
        $help->partner_current_lat = $currentLat;
        $help->partner_current_lng = $currentLng;
        $help->gps_accuracy = $gpsAccuracy;
        $help->partner_location_updated_at = $now;

        if ($movementCheck['has_moved']) {
            $help->last_movement_at = $now;
        }

        // 2. Evaluasi target kedatangan sesuai jenis layanan dan sub-tahap (service_stage)
        $targetCoords = $this->resolveCurrentTargetCoordinates($help);

        $arrivalResult = null;
        if ($targetCoords['lat'] != 0 && $targetCoords['lng'] != 0) {
            $arrivalResult = $this->geoService->isWithinArrivalRadius(
                $currentLat,
                $currentLng,
                $targetCoords['lat'],
                $targetCoords['lng'],
                $gpsAccuracy
            );

            // Jika memenuhi radius <= 50m DAN akurasi GPS valid
            if ($arrivalResult['arrived']) {
                $statusChanged = $this->triggerAutomaticArrival($help);
            } else {
                // 2b. Evaluasi Notifikasi Hampir Sampai (<= 250m) jika belum tiba
                $isNearArrival = $this->geoService->isWithinNearArrivalRadius(
                    $currentLat,
                    $currentLng,
                    $targetCoords['lat'],
                    $targetCoords['lng'],
                    250.0
                );

                if ($isNearArrival) {
                    $nearNotifCacheKey = "help_near_notif_{$help->id}_" . ($help->service_stage ?: 'default');
                    if (!\Illuminate\Support\Facades\Cache::has($nearNotifCacheKey)) {
                        \Illuminate\Support\Facades\Cache::put($nearNotifCacheKey, true, 3600);
                        $this->notifyCustomer($help, 'near_arrival');

                        if ($help->mitra) {
                            try {
                                $help->mitra->notify(new HelpStatusNotification(
                                    $help,
                                    $help->status,
                                    'near_arrival',
                                    $help->mitra
                                ));
                            } catch (\Throwable $e) {
                                Log::warning("[HelpTrackingService] Gagal kirim near_arrival ke mitra: " . $e->getMessage());
                            }
                        }
                    }
                }
            }
        }

        // 3. Khusus pickup_delivery: Deteksi otomatis Tahap Akhir (STAGE_FINAL_APPROACH)
        if ($help->isPickup() && $help->service_stage === Help::STAGE_GOING_TO_DESTINATION) {
            $destLat = (float) ($help->delivery_latitude ?: $help->latitude);
            $destLng = (float) ($help->delivery_longitude ?: $help->longitude);
            if ($destLat != 0 && $destLng != 0) {
                $distToDestMeters = $this->calculateDistance($currentLat, $currentLng, $destLat, $destLng);
                $distToDestKm = $distToDestMeters / 1000.0;
                $totalRouteKm = (float) ($help->service_route_distance_km ?: 5.0);

                $progressPct = ($totalRouteKm > 0) ? max(0, min(100, (1.0 - ($distToDestKm / $totalRouteKm)) * 100)) : 0;
                $etaMin = $this->geoService->getRouteDurationMinutes($distToDestKm, 25.0, 2);

                $progThreshold = \App\Models\AppSetting::getPickupDeliveryFinalApproachProgress(); // 80%
                $etaThreshold  = \App\Models\AppSetting::getPickupDeliveryFinalApproachEtaMinutes(); // 10 min

                if ($progressPct >= $progThreshold || $etaMin <= $etaThreshold) {
                    $help->service_stage = Help::STAGE_FINAL_APPROACH;
                    $statusChanged = true;
                }
            }
        }

        $help->save();

        try {
            broadcast(new \App\Events\PartnerLocationUpdated(
                helpId: (int) $help->id,
                mitraId: (int) ($help->mitra_id ?? 0),
                partnerLat: (float) $help->partner_current_lat,
                partnerLng: (float) $help->partner_current_lng,
                customerLat: $help->latitude ? (float) $help->latitude : null,
                customerLng: $help->longitude ? (float) $help->longitude : null,
                partnerName: $help->mitra?->name ?? 'Mitra'
            ));
        } catch (\Throwable $e) {
            Log::warning('[HelpTrackingService] PartnerLocationUpdated broadcast failed: ' . $e->getMessage());
        }

        if ($statusChanged && $help->user) {
            $this->notifyCustomer($help, $help->status);
        }

        $distanceFromInitial = 0;
        if ($help->partner_initial_lat && $help->partner_initial_lng) {
            $distanceFromInitial = $this->calculateDistance(
                (float) $help->partner_initial_lat,
                (float) $help->partner_initial_lng,
                $currentLat,
                $currentLng
            );
        }

        $distanceToCustomer = null;
        if ($help->latitude && $help->longitude) {
            $distanceToCustomer = $this->calculateDistance(
                $currentLat,
                $currentLng,
                (float) $help->latitude,
                (float) $help->longitude
            );
        }

        return [
            'success'               => true,
            'status'                => $help->status,
            'service_stage'         => $help->service_stage,
            'status_changed'        => $statusChanged,
            'old_status'            => $oldStatus,
            'distance_from_initial' => round($distanceFromInitial, 2),
            'distance_to_customer'  => $distanceToCustomer ? round($distanceToCustomer, 2) : null,
            'movement'              => $movementCheck,
            'arrival_check'         => $arrivalResult,
            'target_context'        => $targetCoords['label'],
        ];
    }

    /**
     * Memajukan tahapan sub-layanan (service_stage) secara eksplisit oleh mitra.
     */
    public function advanceServiceStage(Help $help, string $nextStage, array $extraData = []): Help
    {
        // Guard penjadwalan jika memulai pergerakan awal
        if ($nextStage === Help::STAGE_GOING_TO_PICKUP) {
            if ($help->isScheduled() && !$help->canPartnerStartDeparture()) {
                $openTime = $help->departure_window_opens_at?->format('H:i') ?? '1 jam sebelum jadwal';
                $targetTime = $help->getScheduledTargetTime()?->format('H:i') ?? '-';
                throw new \RuntimeException("Tugas ini dijadwalkan untuk pukul {$targetTime}. Tombol keberangkatan baru dapat diaktifkan mulai pukul {$openTime} ({$help->departure_lead_minutes} menit sebelum jadwal).");
            }
        }

        return DB::transaction(function () use ($help, $nextStage, $extraData) {
            $locked = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            $updatePayload = array_merge(['service_stage' => $nextStage], $extraData);

            // Pemetaan transisi otomatis ke Help.status untuk pickup_delivery
            if ($locked->isPickup()) {
                if ($nextStage === Help::STAGE_GOING_TO_PICKUP) {
                    $updatePayload['status'] = Help::STATUS_PARTNER_ON_THE_WAY;
                    $updatePayload['partner_started_at'] = $locked->partner_started_at ?? now();
                } elseif ($nextStage === Help::STAGE_AT_PICKUP) {
                    $updatePayload['status'] = Help::STATUS_PARTNER_ARRIVED;
                    $updatePayload['partner_arrived_at'] = now();
                    $updatePayload['arrived_at'] = now();
                } elseif ($nextStage === Help::STAGE_WAITING_FOR_CUSTOMER) {
                    $updatePayload['status'] = Help::STATUS_PARTNER_ARRIVED;
                    if (empty($locked->partner_arrived_at)) {
                        $updatePayload['partner_arrived_at'] = now();
                        $updatePayload['arrived_at'] = now();
                    }
                } elseif (in_array($nextStage, [Help::STAGE_ITEM_COLLECTED, Help::STAGE_GOING_TO_DESTINATION, Help::STAGE_FINAL_APPROACH])) {
                    $updatePayload['status'] = Help::STATUS_IN_PROGRESS;
                } elseif ($nextStage === Help::STAGE_AT_DESTINATION) {
                    $updatePayload['status'] = Help::STATUS_IN_PROGRESS;
                }
            }

            $locked->update($updatePayload);
            $this->notifyCustomer($locked, $locked->status);

            return $locked;
        });
    }

    /**
     * Alias method untuk memajukan tahapan layanan oleh mitra.
     */
    public function advanceStage(Help $help, User $mitra, string $nextStage, array $extraData = []): Help
    {
        if ($help->mitra_id !== $mitra->id) {
            throw new \RuntimeException('Anda tidak memiliki hak akses untuk memajukan tahapan pesanan ini.');
        }

        return $this->advanceServiceStage($help, $nextStage, $extraData);
    }

    /**
     * Tentukan koordinat target yang sedang dituju berdasarkan tipe dan stage saat ini.
     */
    protected function resolveCurrentTargetCoordinates(Help $help): array
    {
        if ($help->isPickup()) {
            if (in_array($help->service_stage, [Help::STAGE_GOING_TO_DESTINATION, Help::STAGE_FINAL_APPROACH, Help::STAGE_ITEM_COLLECTED], true)) {
                return [
                    'lat'   => (float) ($help->delivery_latitude ?: $help->latitude),
                    'lng'   => (float) ($help->delivery_longitude ?: $help->longitude),
                    'label' => 'Lokasi Pengantaran (Destination)',
                ];
            }
            return [
                'lat'   => (float) ($help->pickup_latitude ?: $help->latitude),
                'lng'   => (float) ($help->pickup_longitude ?: $help->longitude),
                'label' => 'Lokasi Penjemputan (Pickup)',
            ];
        }

        // On-Site Service: Target adalah alamat customer
        return [
            'lat'   => (float) ($help->latitude ?: 0),
            'lng'   => (float) ($help->longitude ?: 0),
            'label' => 'Lokasi Pekerjaan (Customer)',
        ];
    }

    /**
     * Memicu perubahan status kedatangan otomatis saat berada di radius <= 50m.
     */
    protected function triggerAutomaticArrival(Help $help): bool
    {
        if ($help->isPickup()) {
            if ($help->service_stage === Help::STAGE_GOING_TO_PICKUP) {
                $help->service_stage = Help::STAGE_AT_PICKUP;
                $help->status = Help::STATUS_PARTNER_ARRIVED;
                $help->partner_arrived_at = now();
                $help->arrived_at = now();
                return true;
            } elseif (in_array($help->service_stage, [Help::STAGE_GOING_TO_DESTINATION, Help::STAGE_FINAL_APPROACH], true)) {
                $help->service_stage = Help::STAGE_AT_DESTINATION;
                return true;
            }
        } else {
            // On-Site
            if (in_array($help->status, [Help::STATUS_TAKEN, Help::STATUS_PARTNER_ON_THE_WAY])) {
                $help->status = Help::STATUS_PARTNER_ARRIVED;
                $help->partner_arrived_at = now();
                $help->arrived_at = now();
                return true;
            }
        }

        return false;
    }

    protected function notifyCustomer(Help $help, string $action): void
    {
        if (!$help->user) {
            return;
        }

        try {
            $help->user->notify(new HelpStatusNotification(
                $help,
                $help->status,
                $action,
                $help->mitra
            ));
        } catch (\Throwable $e) {
            Log::warning("[HelpTrackingService] Failed to notify customer: " . $e->getMessage());
        }
    }

    /**
     * Hitung jarak antara dua koordinat (dalam meter) menggunakan Haversine formula.
     */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meter

        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $latDiff = deg2rad($lat2 - $lat1);
        $lngDiff = deg2rad($lng2 - $lng1);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($lngDiff / 2) * sin($lngDiff / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Set lokasi awal mitra saat mengambil bantuan.
     */
    public function setInitialLocation(Help $help, float $lat, float $lng): void
    {
        $help->partner_initial_lat = $lat;
        $help->partner_initial_lng = $lng;
        $help->partner_current_lat = $lat;
        $help->partner_current_lng = $lng;
        $help->save();

        Log::info("[HelpTrackingService] Lokasi awal mitra di-set", [
            'help_id' => $help->id,
            'lat'     => $lat,
            'lng'     => $lng,
        ]);
    }

    /**
     * Dapatkan status tracking untuk UI map / tracking drawer.
     */
    public function getTrackingStatus(Help $help): array
    {
        $status = [
            'help_id'              => $help->id,
            'current_status'       => $help->status,
            'partner_location'     => null,
            'customer_location'    => null,
            'distance_to_customer' => null,
            'service_stage'        => $help->service_stage,
        ];

        if ($help->partner_current_lat && $help->partner_current_lng) {
            $status['partner_location'] = [
                'lat' => (float) $help->partner_current_lat,
                'lng' => (float) $help->partner_current_lng,
            ];
        }

        if ($help->latitude && $help->longitude) {
            $status['customer_location'] = [
                'lat' => (float) $help->latitude,
                'lng' => (float) $help->longitude,
            ];

            if ($status['partner_location']) {
                $status['distance_to_customer'] = round(
                    $this->calculateDistance(
                        (float) $help->partner_current_lat,
                        (float) $help->partner_current_lng,
                        (float) $help->latitude,
                        (float) $help->longitude
                    ),
                    2
                );
            }
        }

        return $status;
    }
}
