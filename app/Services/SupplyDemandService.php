<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\City;
use App\Models\CityCapacity;
use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplyDemandService
{
    /**
     * Hitung seluruh metrik supply dan demand untuk kota tertentu.
     */
    public function calculateCityMetrics(City $city): array
    {
        $ttl = AppSetting::getHeartbeatTtlSeconds();

        // 1. Mitra searching aktif (heartbeat fresh) di kota ini
        $searchingNow = PartnerOnlineState::eligibleForMatching($ttl)
            ->whereHas('user', fn($q) => $q->where('city_id', $city->id)->where('role', 'mitra'))
            ->count();

        // 2. Mitra busy (sedang bekerja) di kota ini
        $busyNow = PartnerOnlineState::where('matching_status', PartnerOnlineState::STATUS_BUSY)
            ->whereHas('user', fn($q) => $q->where('city_id', $city->id)->where('role', 'mitra'))
            ->count();

        // 3. Total mitra online (searching + busy + online standby)
        $onlineTotal = PartnerOnlineState::whereIn('matching_status', [
                PartnerOnlineState::STATUS_SEARCHING,
                PartnerOnlineState::STATUS_BUSY,
                PartnerOnlineState::STATUS_OFFER_PENDING,
                PartnerOnlineState::STATUS_ONLINE,
            ])
            ->whereHas('user', fn($q) => $q->where('city_id', $city->id)->where('role', 'mitra'))
            ->count();

        // 4. Permintaan bantuan yang belum diambil mitra di kota ini
        $unmatchedDemand = Help::where('city_id', $city->id)
            ->where('status', Help::STATUS_MENUNGGU_MITRA)
            ->whereNull('mitra_id')
            ->count();

        // 5. Volume permintaan baru dalam 2 jam terakhir
        $recentVolume2h = Help::where('city_id', $city->id)
            ->where('created_at', '>=', now()->subHours(2))
            ->count();

        // 6. Permintaan tidak terlayani dalam 24 jam (dibatalkan tanpa mitra)
        $unserved24h = Help::where('city_id', $city->id)
            ->where('status', Help::STATUS_DIBATALKAN)
            ->whereNull('mitra_id')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        // 7. Rata-rata durasi tunggu bantuan yang sedang mencari mitra (menit)
        $pendingHelps = Help::where('city_id', $city->id)
            ->where('status', Help::STATUS_MENUNGGU_MITRA)
            ->whereNull('mitra_id')
            ->get();

        $avgWaitingMinutes = $pendingHelps->isEmpty()
            ? 0.0
            : (float) $pendingHelps->avg(fn($h) => max(0, $h->created_at->diffInMinutes(now())));

        // 8. Rasio utilisasi mitra (Busy / Total Online)
        $activeSupply = max($searchingNow + $busyNow, $onlineTotal);
        $utilizationRate = $activeSupply > 0
            ? min(100.0, ($busyNow / (float) $activeSupply) * 100.0)
            : 0.0;

        return [
            'searching_now'            => $searchingNow,
            'busy_now'                 => $busyNow,
            'online_total'             => $onlineTotal,
            'current_unmatched_demand' => $unmatchedDemand,
            'recent_request_volume_2h' => $recentVolume2h,
            'unserved_requests_24h'    => $unserved24h,
            'avg_waiting_minutes'      => round($avgWaitingMinutes, 2),
            'partner_utilization_rate' => round($utilizationRate, 2),
        ];
    }

    /**
     * Evaluasi kapasitas kota menggunakan Hysteresis State Machine.
     * Transisi: OPEN <-> LIMITED <-> CLOSED.
     */
    public function evaluateCapacity(City $city): CityCapacity
    {
        $metrics = $this->calculateCityMetrics($city);

        $capacity = CityCapacity::firstOrCreate(
            ['city_id' => $city->id],
            [
                'capacity_status'     => CityCapacity::STATUS_OPEN,
                'auto_manage'         => true,
                'waiting_list_count'  => 0,
            ]
        );

        // Update metrik riil
        $capacity->searching_now            = $metrics['searching_now'];
        $capacity->busy_now                 = $metrics['busy_now'];
        $capacity->online_total             = $metrics['online_total'];
        $capacity->current_unmatched_demand = $metrics['current_unmatched_demand'];
        $capacity->recent_request_volume_2h = $metrics['recent_request_volume_2h'];
        $capacity->unserved_requests_24h    = $metrics['unserved_requests_24h'];
        $capacity->avg_waiting_minutes      = $metrics['avg_waiting_minutes'];
        $capacity->partner_utilization_rate = $metrics['partner_utilization_rate'];
        $capacity->last_calculated_at       = now();

        // Jika auto_manage diaktifkan dan tidak ada override admin aktif
        if ($capacity->auto_manage) {
            $currentStatus = $capacity->capacity_status ?? CityCapacity::STATUS_OPEN;

            $highDemandMin   = AppSetting::getCapacityHighDemandMin();
            $oversupplyUtil  = AppSetting::getCapacityOversupplyUtil();

            $isHighDemand = ($metrics['avg_waiting_minutes'] > $highDemandMin || $metrics['unserved_requests_24h'] > 5);
            $isOversupply = ($metrics['partner_utilization_rate'] < $oversupplyUtil && $metrics['searching_now'] > max(1, $metrics['current_unmatched_demand'] * 3));

            if ($isHighDemand) {
                $capacity->consecutive_open_evaluations++;
                $capacity->consecutive_closed_evaluations = 0;

                if ($currentStatus === CityCapacity::STATUS_CLOSED) {
                    $capacity->capacity_status = CityCapacity::STATUS_LIMITED;
                    Log::info("[SupplyDemandService] City #{$city->id} transitioned CLOSED -> LIMITED on High Demand.");
                } elseif ($currentStatus === CityCapacity::STATUS_LIMITED && $capacity->consecutive_open_evaluations >= 2) {
                    $capacity->capacity_status = CityCapacity::STATUS_OPEN;
                    Log::info("[SupplyDemandService] City #{$city->id} transitioned LIMITED -> OPEN (High Demand persisted 2x).");
                }
            } elseif ($isOversupply) {
                $capacity->consecutive_closed_evaluations++;
                $capacity->consecutive_open_evaluations = 0;

                if ($currentStatus === CityCapacity::STATUS_OPEN) {
                    $capacity->capacity_status = CityCapacity::STATUS_LIMITED;
                    Log::info("[SupplyDemandService] City #{$city->id} transitioned OPEN -> LIMITED on Oversupply.");
                } elseif ($currentStatus === CityCapacity::STATUS_LIMITED && $capacity->consecutive_closed_evaluations >= 2) {
                    $capacity->capacity_status = CityCapacity::STATUS_CLOSED;
                    Log::info("[SupplyDemandService] City #{$city->id} transitioned LIMITED -> CLOSED (Oversupply persisted 2x).");
                }
            } else {
                // Kondisi Normal / Balanced
                $capacity->consecutive_closed_evaluations = max(0, $capacity->consecutive_closed_evaluations - 1);
                $capacity->consecutive_open_evaluations   = max(0, $capacity->consecutive_open_evaluations - 1);

                if ($currentStatus === CityCapacity::STATUS_CLOSED && $metrics['partner_utilization_rate'] >= $oversupplyUtil) {
                    $capacity->capacity_status = CityCapacity::STATUS_LIMITED;
                    Log::info("[SupplyDemandService] City #{$city->id} transitioned CLOSED -> LIMITED (Supply Normalized).");
                }
            }
        }

        $capacity->save();
        return $capacity;
    }

    /**
     * Evaluasi seluruh kota aktif yang terdaftar di platform.
     */
    public function evaluateAllCities(): int
    {
        $cities = City::where('is_active', true)->get();
        $count = 0;

        foreach ($cities as $city) {
            $this->evaluateCapacity($city);
            $count++;
        }

        Log::info("[SupplyDemandService] Evaluated capacity for {$count} active cities.");
        return $count;
    }

    /**
     * Set Admin Override manual pada status kapasitas kota.
     */
    public function setAdminOverride(City $city, User $admin, string $status, ?Carbon $until = null, ?string $notes = null): CityCapacity
    {
        $capacity = CityCapacity::firstOrCreate(
            ['city_id' => $city->id],
            ['capacity_status' => CityCapacity::STATUS_OPEN]
        );

        $capacity->admin_override_status = $status;
        $capacity->admin_override_until  = $until;
        $capacity->admin_override_notes  = $notes;
        $capacity->save();

        Log::info("[SupplyDemandService] Admin #{$admin->id} set OVERRIDE to '{$status}' for City #{$city->id}.");
        return $capacity;
    }

    /**
     * Hapus Admin Override manual agar kembali ke auto-manage.
     */
    public function clearAdminOverride(City $city): CityCapacity
    {
        $capacity = CityCapacity::firstOrCreate(
            ['city_id' => $city->id],
            ['capacity_status' => CityCapacity::STATUS_OPEN]
        );

        $capacity->admin_override_status = null;
        $capacity->admin_override_until  = null;
        $capacity->admin_override_notes  = null;
        $capacity->save();

        Log::info("[SupplyDemandService] Admin OVERRIDE cleared for City #{$city->id}.");
        return $capacity;
    }
}
