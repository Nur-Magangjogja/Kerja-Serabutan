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
     */
    public function computeScheduleTimestamps(
        string $orderMode = Help::ORDER_MODE_INSTANT,
        ?Carbon $targetScheduledAt = null,
        float $travelDistanceKm = 0.0,
        string $serviceType = Help::SERVICE_TYPE_ON_SITE,
        float $serviceRouteDistanceKm = 0.0
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
                'order_mode'            => Help::ORDER_MODE_INSTANT,
                'published_at'          => $publishedAt,
                'departure_at'          => $departureAt,
                'service_scheduled_at'  => $serviceScheduledAt,
                'pickup_scheduled_at'   => $pickupScheduledAt,
                'delivery_deadline_at'  => $deliveryDeadlineAt,
                'estimated_arrival_at'  => $serviceScheduledAt,
            ];
        }

        // Mode Scheduled: Waktu target ditentukan oleh Customer
        $publishedAt = $now;
        $serviceScheduledAt = $targetScheduledAt;
        $departureAt = $targetScheduledAt->copy()->subMinutes($travelMinutes);

        // Jika waktu keberangkatan yang dihitung sudah lewat, jadwalkan keberangkatan segera
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
            'order_mode'            => Help::ORDER_MODE_SCHEDULED,
            'published_at'          => $publishedAt,
            'departure_at'          => $departureAt,
            'service_scheduled_at'  => $serviceScheduledAt,
            'pickup_scheduled_at'   => $pickupScheduledAt,
            'delivery_deadline_at'  => $deliveryDeadlineAt,
            'estimated_arrival_at'  => $serviceScheduledAt,
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
     * Dapatkan payload 3 Countdown terpisah untuk render realtime di client-side:
     * 1. Countdown Keberangkatan (departure_at - now)
     * 2. ETA Perjalanan (estimated_arrival_at - now)
     * 3. Countdown Layanan / Batas Waktu (service_scheduled_at / delivery_deadline_at - now)
     */
    public function getThreeCountdownData(Help $help): array
    {
        $now = now();

        $departureSec = 0;
        if ($help->departure_at && $help->departure_at->isFuture()) {
            $departureSec = $now->diffInSeconds($help->departure_at, false);
        }

        $etaTravelSec = 0;
        if ($help->estimated_arrival_at && $help->estimated_arrival_at->isFuture()) {
            $etaTravelSec = $now->diffInSeconds($help->estimated_arrival_at, false);
        }

        $targetServiceTime = $help->delivery_deadline_at ?: ($help->service_scheduled_at ?: $help->scheduled_at);
        $serviceCountdownSec = 0;
        if ($targetServiceTime && $targetServiceTime->isFuture()) {
            $serviceCountdownSec = $now->diffInSeconds($targetServiceTime, false);
        }

        return [
            'departure_countdown_seconds' => max(0, $departureSec),
            'departure_at_iso'            => $help->departure_at?->toIso8601String(),
            'eta_travel_seconds'          => max(0, $etaTravelSec),
            'estimated_arrival_at_iso'    => $help->estimated_arrival_at?->toIso8601String(),
            'service_countdown_seconds'   => max(0, $serviceCountdownSec),
            'service_scheduled_at_iso'    => $targetServiceTime?->toIso8601String(),
            'is_departure_due'            => $this->isDepartureDue($help),
        ];
    }
}
