<?php

namespace App\Traits;

/**
 * Trait FiltersByCityForAdmin
 * 
 * Mengelola batasan akses data berdasarkan wilayah Kecamatan untuk pengguna dengan role 'admin'.
 * 
 * Mekanisme Pembatasan:
 * 1. Admin dibatasi ruang lingkup datanya berdasarkan Kecamatan wewenangnya (`district_id` / `admin_district`).
 * 2. Tidak ada lagi penugasan atau pembatasan berbasis kota/kabupaten penuh.
 * 3. Super Admin memiliki akses global ke seluruh wilayah tanpa filter.
 */
trait FiltersByCityForAdmin
{
    /**
     * Apply district filter for admin users
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $districtColumn Column name for district_id (default: 'district_id')
     * @param string $cityColumn Column name for city_id (fallback: 'city_id')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyTerritoryFilter($query, $districtColumn = 'district_id', $cityColumn = 'city_id')
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return $query;
        }

        $effectiveDistrictIds = $user->getEffectiveAdminDistrictIds();
        
        if (\Illuminate\Support\Facades\Schema::hasColumn($query->getModel()->getTable(), $districtColumn)) {
            if (!empty($effectiveDistrictIds)) {
                return $query->whereIn($districtColumn, $effectiveDistrictIds);
            }
            // Admin has no assigned districts: show nothing
            return $query->whereRaw('1 = 0');
        }

        // Fallback for tables without direct district_id column: derive city_ids from assigned districts
        if (!empty($effectiveDistrictIds)) {
            $cityIds = \App\Models\District::whereIn('id', $effectiveDistrictIds)->pluck('city_id')->unique()->filter()->all();
            if (!empty($cityIds) && \Illuminate\Support\Facades\Schema::hasColumn($query->getModel()->getTable(), $cityColumn)) {
                return $query->whereIn($cityColumn, $cityIds);
            }
        }

        return $query;
    }

    /**
     * Apply district filter directly for admin users
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $districtColumn Column name for district_id (default: 'district_id')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyDistrictFilter($query, $districtColumn = 'district_id')
    {
        return $this->applyTerritoryFilter($query, $districtColumn);
    }

    /**
     * Apply city filter for admin users (backward compatibility, scoped to districts)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $cityColumn Column name for city_id (default: 'city_id')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyCityFilter($query, $cityColumn = 'city_id')
    {
        return $this->applyTerritoryFilter($query, 'district_id', $cityColumn ?: 'city_id');
    }

    /**
     * Apply district filter via relationship for admin users
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $relationName Name of the relationship
     * @param string $districtColumn Column name in the related table (default: 'district_id')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyCityFilterViaRelation($query, $relationName, $districtColumn = 'district_id')
    {
        $user = auth()->user();
        
        if ($user && $user->role === 'admin') {
            $effectiveDistrictIds = $user->getEffectiveAdminDistrictIds();

            if (empty($effectiveDistrictIds)) {
                return $query->whereRaw('1 = 0');
            }

            $query->whereHas($relationName, function ($q) use ($effectiveDistrictIds, $districtColumn) {
                if (\Illuminate\Support\Facades\Schema::hasColumn($q->getModel()->getTable(), $districtColumn)) {
                    $q->whereIn($districtColumn, $effectiveDistrictIds);
                } else {
                    $cityIds = \App\Models\District::whereIn('id', $effectiveDistrictIds)->pluck('city_id')->unique()->filter()->all();
                    $q->whereIn('city_id', $cityIds);
                }
            });
        }
        
        return $query;
    }

    /**
     * Check if current user is admin with district territory restriction
     * 
     * @return bool
     */
    protected function isAdminWithCityRestriction()
    {
        $user = auth()->user();
        return $user && $user->role === 'admin' && (!empty($user->district_id) || !empty($user->getAdminDistrictIds()));
    }

    /**
     * Get current admin's primary district_id
     * 
     * @return int|null
     */
    protected function getAdminDistrictId()
    {
        $user = auth()->user();
        return ($user && $user->role === 'admin') ? ($user->district_id ?? $user->getAdminDistrictIds()[0] ?? null) : null;
    }

    /**
     * Get current admin's parent city_id (derived from primary district)
     * 
     * @return int|null
     */
    protected function getAdminCityId()
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            return null;
        }

        $districtIds = $user->getAdminDistrictIds();
        if (!empty($districtIds)) {
            return \App\Models\District::whereIn('id', $districtIds)->value('city_id');
        }

        return $user->city_id;
    }
}

