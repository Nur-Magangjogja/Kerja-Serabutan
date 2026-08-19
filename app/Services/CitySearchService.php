<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * CitySearchService
 *
 * Memusatkan logika pencarian kota dari berbagai tabel:
 * - cities (tabel utama aplikasi)
 * - req_regencies / req_provinces / req_districts
 * - reg_regencies / reg_provinces / reg_districts
 * - regencies / provinces (legacy)
 */
class CitySearchService
{
    public function search(string $query, int $limit = 10): array
    {
        $q = trim($query);

        if ($q === '') {
            return [];
        }

        // 1) Cari dari tabel cities utama
        $results = City::where('is_active', true)
            ->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('province', 'like', "%{$q}%")
                  ->orWhere('code', 'like', "%{$q}%");
            })
            ->whereRaw("COALESCE(code,'') NOT LIKE 'reqd-%' AND COALESCE(code,'') NOT LIKE 'regd-%'")
            ->select('id', 'name', 'province', 'code')
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->toArray();

        // 2) Isi sisa slot dari tabel wilayah yang diimpor
        if (count($results) < $limit) {
            $regRows = collect();
            $remaining = $limit - count($results);

            $regRows = $this->searchReqRegencies($q, $remaining, $regRows);
            $regRows = $this->searchReqDistricts($q, $remaining, $regRows);
            $regRows = $this->searchRegDistricts($q, $remaining, $regRows);
            $regRows = $this->searchRegRegencies($q, $remaining, $regRows);
            $regRows = $this->searchLegacyRegencies($q, $remaining, $regRows);

            foreach ($regRows as $r) {
                $mapped = $this->mapRowToCity($r);
                if ($mapped) {
                    // Hindari duplikasi
                    $exists = collect($results)->contains('id', $mapped['id']);
                    if (!$exists) {
                        $results[] = $mapped;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Resolve city_id dari kode distrik (reqd- atau regd-) menjadi kota (City).
     * Digunakan saat user memilih distrik dari dropdown req_*.
     */
    public function resolveDistrictToCity(string $districtCode): ?City
    {
        if (str_starts_with($districtCode, 'reqd-') && Schema::hasTable('req_districts')) {
            $did = substr($districtCode, 5);
            $row = DB::table('req_districts')
                ->join('req_regencies', 'req_districts.regency_id', '=', 'req_regencies.id')
                ->join('req_provinces', 'req_regencies.province_id', '=', 'req_provinces.id')
                ->where('req_districts.id', $did)
                ->select('req_regencies.id as regency_id', 'req_regencies.regency', 'req_provinces.province')
                ->first();

            if ($row) {
                return City::firstOrCreate(
                    ['code' => 'reqr-' . $row->regency_id],
                    ['name' => $row->regency, 'province' => $row->province, 'is_active' => true]
                );
            }
        }

        if (str_starts_with($districtCode, 'regd-') && Schema::hasTable('reg_districts')) {
            $did = substr($districtCode, 5);
            $row = DB::table('reg_districts')
                ->join('reg_regencies', 'reg_districts.regency_id', '=', 'reg_regencies.id')
                ->join('reg_provinces', 'reg_regencies.province_id', '=', 'reg_provinces.id')
                ->where('reg_districts.id', $did)
                ->select('reg_regencies.id as regency_id', 'reg_regencies.name as regency', 'reg_provinces.name as province')
                ->first();

            if ($row) {
                return City::firstOrCreate(
                    ['code' => 'reqr-' . $row->regency_id],
                    ['name' => $row->regency, 'province' => $row->province, 'is_active' => true]
                );
            }
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private search helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function searchReqRegencies(string $q, int $remaining, $regRows): mixed
    {
        if (!Schema::hasTable('req_regencies') || !Schema::hasTable('req_provinces')) return $regRows;

        $rows = DB::table('req_regencies')
            ->join('req_provinces', 'req_regencies.province_id', '=', 'req_provinces.id')
            ->where(fn($b) => $b->where('req_regencies.regency', 'like', "%{$q}%")
                                ->orWhere('req_provinces.province', 'like', "%{$q}%"))
            ->select('req_regencies.id as regency_id', 'req_regencies.regency', 'req_regencies.type', 'req_provinces.province')
            ->orderBy('req_regencies.regency')
            ->limit($remaining)
            ->get();

        foreach ($rows as $r) { $regRows->push($r); }
        return $regRows;
    }

    private function searchReqDistricts(string $q, int $remaining, $regRows): mixed
    {
        if (count($regRows) >= $remaining) return $regRows;
        if (!Schema::hasTable('req_districts') || !Schema::hasTable('req_regencies') || !Schema::hasTable('req_provinces')) return $regRows;

        $limit2 = $remaining - count($regRows);
        $rows = DB::table('req_districts')
            ->join('req_regencies', 'req_districts.regency_id', '=', 'req_regencies.id')
            ->join('req_provinces', 'req_regencies.province_id', '=', 'req_provinces.id')
            ->where(fn($b) => $b->where('req_districts.district', 'like', "%{$q}%")
                                ->orWhere('req_regencies.regency', 'like', "%{$q}%")
                                ->orWhere('req_provinces.province', 'like', "%{$q}%"))
            ->select(DB::raw("CONCAT('reqd-', req_districts.id) as regency_id"),
                     'req_districts.district as regency',
                     'req_regencies.regency as parent_regency',
                     DB::raw('null as type'),
                     'req_provinces.province')
            ->orderBy('req_districts.district')
            ->limit($limit2)
            ->get();

        foreach ($rows as $r) { $regRows->push($r); }
        return $regRows;
    }

    private function searchRegDistricts(string $q, int $remaining, $regRows): mixed
    {
        if (count($regRows) >= $remaining) return $regRows;
        if (!Schema::hasTable('reg_districts') || !Schema::hasTable('reg_regencies') || !Schema::hasTable('reg_provinces')) return $regRows;

        $limit3 = $remaining - count($regRows);
        $rows = DB::table('reg_districts')
            ->join('reg_regencies', 'reg_districts.regency_id', '=', 'reg_regencies.id')
            ->join('reg_provinces', 'reg_regencies.province_id', '=', 'reg_provinces.id')
            ->where(fn($b) => $b->where('reg_districts.name', 'like', "%{$q}%")
                                ->orWhere('reg_regencies.name', 'like', "%{$q}%")
                                ->orWhere('reg_provinces.name', 'like', "%{$q}%"))
            ->select(DB::raw("CONCAT('regd-', reg_districts.id) as regency_id"),
                     'reg_districts.name as regency',
                     'reg_regencies.name as parent_regency',
                     DB::raw('null as type'),
                     'reg_provinces.name as province')
            ->orderBy('reg_districts.name')
            ->limit($limit3)
            ->get();

        foreach ($rows as $r) { $regRows->push($r); }
        return $regRows;
    }

    private function searchRegRegencies(string $q, int $remaining, $regRows): mixed
    {
        if (count($regRows) >= $remaining) return $regRows;
        if (!Schema::hasTable('reg_regencies') || !Schema::hasTable('reg_provinces')) return $regRows;

        $limit2 = $remaining - count($regRows);
        $rows = DB::table('reg_regencies')
            ->join('reg_provinces', 'reg_regencies.province_id', '=', 'reg_provinces.id')
            ->where(fn($b) => $b->where('reg_regencies.name', 'like', "%{$q}%")
                                ->orWhere('reg_provinces.name', 'like', "%{$q}%"))
            ->select('reg_regencies.id as regency_id', 'reg_regencies.name as regency', DB::raw('null as type'), 'reg_provinces.name as province')
            ->orderBy('reg_regencies.name')
            ->limit($limit2)
            ->get();

        foreach ($rows as $r) { $regRows->push($r); }
        return $regRows;
    }

    private function searchLegacyRegencies(string $q, int $remaining, $regRows): mixed
    {
        if (count($regRows) >= $remaining) return $regRows;
        if (!Schema::hasTable('regencies') || !Schema::hasTable('provinces')) return $regRows;

        $limit3 = $remaining - count($regRows);
        $rows = DB::table('regencies')
            ->join('provinces', 'regencies.province_id', '=', 'provinces.id')
            ->where(fn($b) => $b->where('regencies.regency', 'like', "%{$q}%")
                                ->orWhere('provinces.province', 'like', "%{$q}%"))
            ->select('regencies.id as regency_id', 'regencies.regency', 'regencies.type', 'provinces.province')
            ->orderBy('regencies.regency')
            ->limit($limit3)
            ->get();

        foreach ($rows as $r) { $regRows->push($r); }
        return $regRows;
    }

    /**
     * Mapping row dari tabel wilayah menjadi City model dan format array hasil.
     */
    private function mapRowToCity(object $r): ?array
    {
        try {
            $regencyIdStr = is_string($r->regency_id) ? $r->regency_id : (string) $r->regency_id;
            $display      = null;
            $targetCity   = null;

            if (str_starts_with($regencyIdStr, 'reqd-')) {
                $did = substr($regencyIdStr, 5);
                $parent = DB::table('req_districts')
                    ->join('req_regencies', 'req_districts.regency_id', '=', 'req_regencies.id')
                    ->join('req_provinces', 'req_regencies.province_id', '=', 'req_provinces.id')
                    ->where('req_districts.id', $did)
                    ->select('req_districts.district', 'req_regencies.id as pid', 'req_regencies.regency as pregency', 'req_provinces.province')
                    ->first();

                if ($parent) {
                    $targetCity = City::firstOrCreate(
                        ['code' => 'reqr-' . $parent->pid],
                        ['name' => $parent->pregency, 'province' => $parent->province, 'is_active' => true]
                    );
                    $display = $parent->district . ', ' . $parent->pregency . ', ' . $parent->province;
                }
            } elseif (str_starts_with($regencyIdStr, 'regd-')) {
                $did = substr($regencyIdStr, 5);
                $parent = DB::table('reg_districts')
                    ->join('reg_regencies', 'reg_districts.regency_id', '=', 'reg_regencies.id')
                    ->join('reg_provinces', 'reg_regencies.province_id', '=', 'reg_provinces.id')
                    ->where('reg_districts.id', $did)
                    ->select('reg_districts.name as district', 'reg_regencies.id as pid', 'reg_regencies.name as pregency', 'reg_provinces.name as province')
                    ->first();

                if ($parent) {
                    $targetCity = City::firstOrCreate(
                        ['code' => 'reqr-' . $parent->pid],
                        ['name' => $parent->pregency, 'province' => $parent->province, 'is_active' => true]
                    );
                    $display = $parent->district . ', ' . $parent->pregency . ', ' . $parent->province;
                }
            }

            if (!$targetCity) {
                $targetCity = City::firstOrCreate(
                    ['code' => $regencyIdStr],
                    ['name' => $r->regency, 'province' => $r->province, 'type' => $r->type ?? null, 'is_active' => true]
                );
                if (!$display && !empty($r->parent_regency)) {
                    $display = $r->regency . ', ' . $r->parent_regency . ', ' . $r->province;
                }
            }

            if (!$display) {
                $display = $targetCity->name . ', ' . $targetCity->province;
            }

            $item = [
                'id'       => $targetCity->id,
                'name'     => $targetCity->name,
                'province' => $targetCity->province,
                'code'     => $targetCity->code,
            ];
            if ($display) $item['display'] = $display;

            return $item;
        } catch (\Throwable $e) {
            Log::warning('[CitySearchService] mapRowToCity error: ' . $e->getMessage());
            return null;
        }
    }
}
