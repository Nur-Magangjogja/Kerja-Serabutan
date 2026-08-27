<?php

namespace App\Traits;

/**
 * Trait FiltersByCityForAdmin
 * 
 * Mengelola batasan akses data berdasarkan kota untuk pengguna dengan role 'admin'.
 * 
 * Mekanisme Pembatasan:
 * 1. Setiap admin dibatasi ruang lingkup datanya berdasarkan kolom `users.city_id`.
 * 2. Super Admin memiliki akses global ke seluruh kota tanpa filter.
 * 3. Tabel pivot `admin_city` disediakan untuk skenario relasi multi-kota jika dibutuhkan di masa mendatang.
 */
trait FiltersByCityForAdmin
{
    /**
     * Apply city filter for admin users
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $cityColumn Column name for city_id (default: 'city_id')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyCityFilter($query, $cityColumn = 'city_id')
    {
        $user = auth()->user();
        
        if ($user && $user->role === 'admin' && $user->city_id) {
            $query->where($cityColumn, $user->city_id);
        }
        
        return $query;
    }

    /**
     * Apply city filter via relationship for admin users
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $relationName Name of the relationship
     * @param string|null $cityColumn Column name for city_id in the related table (default: 'city_id')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyCityFilterViaRelation($query, $relationName, $cityColumn = 'city_id')
    {
        $user = auth()->user();
        
        if ($user && $user->role === 'admin' && $user->city_id) {
            $query->whereHas($relationName, function ($q) use ($cityColumn, $user) {
                $q->where($cityColumn, $user->city_id);
            });
        }
        
        return $query;
    }

    /**
     * Check if current user is admin with city restriction
     * 
     * @return bool
     */
    protected function isAdminWithCityRestriction()
    {
        $user = auth()->user();
        return $user && $user->role === 'admin' && $user->city_id;
    }

    /**
     * Get current admin's city_id
     * 
     * @return int|null
     */
    protected function getAdminCityId()
    {
        $user = auth()->user();
        return ($user && $user->role === 'admin') ? $user->city_id : null;
    }
}
