<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CityController extends Controller
{
    /**
     * Search cities by query string `q`.
     */
    public function search(Request $request)
    {
        $q = $request->get('q', '');
        $limit = (int) $request->get('limit', 10);

        if (trim($q) === '') {
            return response()->json([]);
        }

        $results = City::where('is_active', true)
            ->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                        ->orWhere('province', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
            })
            ->select('id', 'name', 'province', 'code')
            ->orderBy('name')
            ->limit($limit)
            ->get();

        // If we don't have enough results, search the imported `regencies`/`provinces`
        // tables and create corresponding City records on-the-fly so the frontend
        // can select them and Help::create will reference a proper `cities` row.
        if ($results->count() < $limit) {
            $remaining = $limit - $results->count();

            $regRows = DB::table('regencies')
                ->join('provinces', 'regencies.province_id', '=', 'provinces.id')
                ->where(function ($builder) use ($q) {
                    $builder->where('regencies.regency', 'like', "%{$q}%")
                            ->orWhere('provinces.province', 'like', "%{$q}%");
                })
                ->select('regencies.id as regency_id', 'regencies.regency', 'regencies.type', 'provinces.province')
                ->orderBy('regencies.regency')
                ->limit($remaining)
                ->get();

            foreach ($regRows as $r) {
                // Ensure a City record exists for this regency code
                $city = City::firstOrCreate(
                    ['code' => $r->regency_id],
                    [
                        'name' => $r->regency,
                        'province' => $r->province,
                        'type' => $r->type ?? null,
                        'is_active' => true,
                    ]
                );

                // Only add if not already present in results
                if (! $results->contains('id', $city->id)) {
                    $results->push($city->only(['id', 'name', 'province', 'code']));
                }
            }
        }

        return response()->json($results);
    }

    /**
     * Reverse geocode coordinates (lat, lng) to human-readable Indonesian address.
     * Cached and proxied via server to eliminate browser CORS and 429 Too Many Requests errors.
     */
    public function reverseGeocode(Request $request)
    {
        $lat = (float) $request->get('lat');
        $lng = (float) $request->get('lng');

        if (!$lat || !$lng || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return response()->json(['error' => 'Invalid coordinates'], 400);
        }

        // Cache for 24 hours keyed by 4 decimal places (~11 meters)
        $cacheKey = 'osm_rev_v2_' . round($lat, 4) . '_' . round($lng, 4);

        $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($lat, $lng) {
            try {
                $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lng}&zoom=18&addressdetails=1";
                $res = \Illuminate\Support\Facades\Http::timeout(3)
                    ->withHeaders([
                        'User-Agent'      => 'SayaBantuApp/1.0 (ReverseGeocode; info@sayabantu.id)',
                        'Accept-Language' => 'id',
                    ])
                    ->get($url);

                if ($res->successful()) {
                    $json = $res->json();
                    if (!empty($json) && is_array($json) && !empty($json['display_name'])) {
                        return $json;
                    }
                }
            } catch (\Throwable $e) {
                // fall through to local db fallback
            }

            return null;
        });

        // If Nominatim failed, rate-limited, or empty, fallback to local DB cities & districts
        if (!$data) {
            $matchedCity = null;
            $driver = DB::connection()->getDriverName();
            if ($driver === 'sqlite') {
                $candidate = City::whereNotNull('latitude')->whereNotNull('longitude')->get()
                    ->map(function ($c) use ($lat, $lng) {
                        $c->dist = 6371 * acos(min(1.0, max(-1.0,
                            cos(deg2rad($lat)) * cos(deg2rad($c->latitude)) * cos(deg2rad($c->longitude) - deg2rad($lng))
                            + sin(deg2rad($lat)) * sin(deg2rad($c->latitude))
                        )));
                        return $c;
                    })->sortBy('dist')->first();
                if ($candidate && (float) $candidate->dist <= 25.0) {
                    $matchedCity = $candidate;
                }
            } else {
                $matchedCity = City::select('*')
                    ->selectRaw("(6371 * acos(least(1.0, greatest(-1.0, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))))) AS dist", [$lat, $lng, $lat])
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->having('dist', '<=', 25.0)
                    ->orderBy('dist')
                    ->first();
            }

            $matchedDistrict = null;
            if ($matchedCity) {
                $matchedDistrict = \App\Models\District::where('city_id', $matchedCity->id)->where('is_active', true)->first();
            }

            $cityName     = $matchedCity ? $matchedCity->name : '';
            $districtName = $matchedDistrict ? $matchedDistrict->name : '';
            $provinceName = $matchedCity ? $matchedCity->province : '';

            $addressParts = [];
            if ($districtName) $addressParts[] = 'Kec. ' . $districtName;
            if ($cityName)     $addressParts[] = $cityName;
            if ($provinceName) $addressParts[] = $provinceName;
            $displayName = implode(', ', $addressParts);

            $data = [
                'display_name' => $displayName ?: 'Indonesia',
                'address' => [
                    'city'         => $cityName,
                    'town'         => $cityName,
                    'municipality' => $districtName,
                    'district'     => $districtName,
                    'state'        => $provinceName,
                    'country'      => 'Indonesia',
                    'country_code' => 'id',
                ],
                'is_fallback' => true,
            ];
        }

        return response()->json($data);
    }
}
