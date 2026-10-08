<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'province',
        'province_id',
        'admin_id',
        'is_active',
        'code',
        'type',
        'postal_code',
        'latitude',
        'longitude',
        'is_matching_seeking_enabled',
    ];

    protected $casts = [
        'is_active'                    => 'boolean',
        'is_matching_seeking_enabled'  => 'boolean',
    ];

    protected static function booted()
    {
        static::saved(function () {
            cache()->forget('all_cities_cached');
            cache()->forget('admin_all_cities');
        });

        static::deleted(function () {
            cache()->forget('all_cities_cached');
            cache()->forget('admin_all_cities');
        });
    }

    /**
     * Dapatkan semua kota terurut abjad dengan caching (1 jam).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllCached()
    {
        return cache()->remember('all_cities_cached', 3600, function () {
            return static::orderBy('name')->get();
        });
    }

    /**
     * Cek status efektif seeking mode kota (dengan fallback ke global AppSetting).
     */
    public function isSeekingModeEffective(): bool
    {
        if ($this->is_matching_seeking_enabled !== null) {
            return (bool) $this->is_matching_seeking_enabled;
        }

        return AppSetting::isMatchingSeekingEnabled();
    }

    /**
     * Dapatkan label opsi konfigurasi seeking mode.
     */
    public function getSeekingModeConfigLabel(): string
    {
        if ($this->is_matching_seeking_enabled === null) {
            return 'inherit';
        }

        return $this->is_matching_seeking_enabled ? 'enabled' : 'disabled';
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Admins who manage this city (many-to-many)
    public function admins()
    {
        return $this->belongsToMany(User::class, 'admin_city', 'city_id', 'user_id')
                    ->withTimestamps();
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function helps()
    {
        return $this->hasMany(Help::class);
    }

    public function districts()
    {
        return $this->hasMany(District::class);
    }

    public function capacity()
    {
        return $this->hasOne(CityCapacity::class);
    }

    /**
     * Cari Kota terdekat dari koordinat GPS (mendukung SQLite dan MySQL).
     */
    public static function findNearest(float $lat, float $lng, ?float $maxDistanceKm = null, ?\Illuminate\Support\Collection $cities = null): ?self
    {
        if ($cities !== null) {
            if ($cities->isEmpty()) {
                return null;
            }
            $geoService = app(\App\Services\GeoService::class);
            $nearest = null;
            $minDist = PHP_FLOAT_MAX;
            foreach ($cities as $city) {
                if ($city->latitude === null || $city->longitude === null) {
                    continue;
                }
                $dist = $geoService->calculateStraightDistance($lat, $lng, (float) $city->latitude, (float) $city->longitude);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $nearest = $city;
                }
            }
            if ($maxDistanceKm !== null && $minDist > $maxDistanceKm) {
                return null;
            }
            return $nearest;
        }

        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $cities = static::whereNotNull('latitude')->whereNotNull('longitude')->get();
            if ($cities->isEmpty()) {
                return null;
            }
            $geoService = app(\App\Services\GeoService::class);
            $nearest = null;
            $minDist = PHP_FLOAT_MAX;
            foreach ($cities as $city) {
                $dist = $geoService->calculateStraightDistance($lat, $lng, (float) $city->latitude, (float) $city->longitude);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $nearest = $city;
                }
            }
            if ($maxDistanceKm !== null && $minDist > $maxDistanceKm) {
                return null;
            }
            return $nearest;
        }

        $query = static::select('*')
            ->selectRaw("(6371 * acos(least(1.0, greatest(-1.0, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))))) AS dist", [$lat, $lng, $lat])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('dist');

        if ($maxDistanceKm !== null) {
            $query->having('dist', '<=', $maxDistanceKm);
        }

        return $query->first();
    }
}

