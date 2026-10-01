<?php

namespace App\Services;

use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpDispatch;
use App\Models\Province;
use App\Services\Cancellation\CancellationSettlementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegionService
{
    /**
     * Single Source of Truth: Menentukan apakah sebuah wilayah aktif untuk layanan.
     * Aturan evaluasi hierarkis:
     * - District harus active (jika districtId diberikan).
     * - City (parent dari district, atau cityId langsung) harus active.
     * - Province (parent dari city) harus active jika ada record Province.
     * Semua level harus active. Jika salah satu nonaktif, wilayah dinyatakan NONAKTIF.
     */
    public function isRegionActive(?int $districtId = null, ?int $cityId = null, ?int $provinceId = null): bool
    {
        // 1. Validasi District jika diberikan
        if ($districtId) {
            $district = District::find($districtId);
            if (!$district || !$district->is_active) {
                return false;
            }

            if (!$cityId) {
                $cityId = $district->city_id;
            }
        }

        // 2. Validasi City jika ada
        if ($cityId) {
            $city = City::find($cityId);
            if (!$city || !$city->is_active) {
                return false;
            }

            // Validasi Province dari City
            $province = null;
            if ($city->province_id) {
                $province = Province::find($city->province_id);
            } elseif (!empty($city->province)) {
                $province = Province::where('name', $city->province)->first();
            }

            if ($province && !$province->is_active) {
                return false;
            }
        }

        // 3. Validasi ProvinceId jika dipassing eksplisit
        if ($provinceId) {
            $province = Province::find($provinceId);
            if (!$province || !$province->is_active) {
                return false;
            }
        }

        return true;
    }

    /**
     * Membatalkan seluruh pesanan yang belum diambil (untaken) di wilayah yang dinonaktifkan,
     * serta mengembalikan dana 100% (escrow refund) secara idempotent dan synchronous.
     *
     * @param string $type 'district'|'city'|'province'
     * @param int $id
     * @param string $reason
     * @return int Jumlah order yang berhasil dibatalkan
     */
    public function cancelAndRefundUntakenOrdersInRegion(string $type, int $id, string $reason = 'Wilayah dinonaktifkan oleh administrator'): int
    {
        $query = Help::where('status', Help::STATUS_MENUNGGU_MITRA)
            ->whereNull('mitra_id');

        if ($type === 'district') {
            $query->where('district_id', $id);
        } elseif ($type === 'city') {
            $districtIds = District::where('city_id', $id)->pluck('id')->all();
            $query->where(function ($q) use ($id, $districtIds) {
                $q->where('city_id', $id);
                if (!empty($districtIds)) {
                    $q->orWhereIn('district_id', $districtIds);
                }
            });
        } elseif ($type === 'province') {
            $province = Province::find($id);
            $citiesQuery = City::where('province_id', $id);
            if ($province) {
                $citiesQuery->orWhere('province', $province->name);
            }
            $cityIds = $citiesQuery->pluck('id')->all();
            $districtIds = !empty($cityIds) ? District::whereIn('city_id', $cityIds)->pluck('id')->all() : [];

            $query->where(function ($q) use ($cityIds, $districtIds) {
                if (!empty($cityIds)) {
                    $q->whereIn('city_id', $cityIds);
                }
                if (!empty($districtIds)) {
                    $q->orWhereIn('district_id', $districtIds);
                }
            });
        }

        $untakenHelps = $query->get();
        $cancelledCount = 0;
        $settlementService = app(CancellationSettlementService::class);

        foreach ($untakenHelps as $help) {
            DB::transaction(function () use ($help, $reason, $settlementService, &$cancelledCount) {
                $lockedHelp = Help::where('id', $help->id)
                    ->lockForUpdate()
                    ->first();

                // Pastikan masih belum diambil mitra dan belum dibatalkan (Idempotency)
                if (!$lockedHelp || $lockedHelp->status !== Help::STATUS_MENUNGGU_MITRA || $lockedHelp->mitra_id !== null) {
                    return;
                }

                // 1. Batalkan tawaran dispatch aktif mitra jika ada (offered)
                HelpDispatch::where('help_id', $lockedHelp->id)
                    ->where('status', HelpDispatch::STATUS_OFFERED)
                    ->update([
                        'status'           => HelpDispatch::STATUS_CANCELLED,
                        'responded_at'     => now(),
                        'rejection_reason' => $reason,
                    ]);

                // 2. Eksekusi full refund ke Customer via existing CancellationSettlementService
                $settlementService->processFullRefund($lockedHelp, $reason);

                // 3. Catat admin notes
                $lockedHelp->update([
                    'admin_notes' => "Dibatalkan otomatis oleh sistem. Alasan: {$reason}",
                ]);

                $cancelledCount++;
            });
        }

        return $cancelledCount;
    }

    /**
     * Memeriksa seluruh wilayah yang nonaktif di sistem dan membersihkan order untaken yang tersisa
     * (Digunakan sebagai Safety Net oleh Scheduled Command).
     */
    public function sweepUntakenOrdersInAllInactiveRegions(string $reason = 'Pembersihan berkala pesanan di wilayah nonaktif (Safety Net)'): int
    {
        $totalCancelled = 0;

        // 1. Seluruh provinsi nonaktif
        $inactiveProvinces = Province::where('is_active', false)->pluck('id')->all();
        foreach ($inactiveProvinces as $provId) {
            $totalCancelled += $this->cancelAndRefundUntakenOrdersInRegion('province', $provId, $reason);
        }

        // 2. Seluruh kota nonaktif
        $inactiveCities = City::where('is_active', false)->pluck('id')->all();
        foreach ($inactiveCities as $cityId) {
            $totalCancelled += $this->cancelAndRefundUntakenOrdersInRegion('city', $cityId, $reason);
        }

        // 3. Seluruh kecamatan nonaktif
        $inactiveDistricts = District::where('is_active', false)->pluck('id')->all();
        foreach ($inactiveDistricts as $distId) {
            $totalCancelled += $this->cancelAndRefundUntakenOrdersInRegion('district', $distId, $reason);
        }

        return $totalCancelled;
    }
}
