<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AdminCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menghubungkan seluruh akun Admin Wilayah ke Kecamatan (`admin_district`) dan Kota (`admin_city`) binaannya secara stabil dan idempoten.
     *
     * Canonical Rule AW6:
     * - `admin_district` adalah SATU-SATUNYA sumber wewenang operasional Admin Wilayah.
     * - `admin_city` adalah context/grouping/display metadata yang diturunkan secara deterministik dari parent city milik managedDistricts.
     * - Profile territory (`users.city_id` / `users.district_id`) TIDAK BOLEH diubah oleh seeder ini.
     * - Zero district assignment menghasilkan 0 wewenang fail-closed (admin_district = [], admin_city = []).
     */
    public function run(): void
    {
        $adminAssignments = [
            // 1. Single District Example: Exactly 1 district (Ngaglik in Sleman)
            [
                'email'     => 'admin.single@sayabantu.com',
                'districts' => [
                    ['city' => 'Sleman', 'district' => 'Ngaglik'],
                ],
            ],
            // 2. Multi-District Same-City Example: 2 districts in Sleman (Ngaglik + Depok)
            [
                'email'     => 'admin.sleman@sayabantu.com',
                'districts' => [
                    ['city' => 'Sleman', 'district' => 'Ngaglik'],
                    ['city' => 'Sleman', 'district' => 'Depok'],
                ],
            ],
            // 3. Cross-City Example: 2 districts across 2 cities (Ngaglik in Sleman + Gondomanan in Yogyakarta)
            [
                'email'     => 'admin.cross@sayabantu.com',
                'districts' => [
                    ['city' => 'Sleman', 'district' => 'Ngaglik'],
                    ['city' => 'Yogyakarta', 'district' => 'Gondomanan'],
                ],
            ],
            // 4. Zero-Territory Example: 0 assigned districts = 0 authority (Fail-Closed)
            [
                'email'     => 'admin.zero@sayabantu.com',
                'districts' => [],
            ],
            // 5. Regional Admin DIY: Explicit districts in Yogyakarta (Gondomanan + Danurejan)
            [
                'email'     => 'admin@sayabantu.com',
                'districts' => [
                    ['city' => 'Yogyakarta', 'district' => 'Gondomanan'],
                    ['city' => 'Yogyakarta', 'district' => 'Danurejan'],
                ],
            ],
            // 6. Regional Admin Surakarta: Explicit districts in Surakarta (Banjarsari + Jebres)
            [
                'email'     => 'admin.solo@sayabantu.com',
                'districts' => [
                    ['city' => 'Surakarta', 'district' => 'Banjarsari'],
                    ['city' => 'Surakarta', 'district' => 'Jebres'],
                ],
            ],
            // 7. Regional Admin Jakarta Selatan: Explicit districts in Jakarta Selatan (Tebet + Setiabudi)
            [
                'email'     => 'admin.jaksel@sayabantu.com',
                'districts' => [
                    ['city' => 'Jakarta Selatan', 'district' => 'Tebet'],
                    ['city' => 'Jakarta Selatan', 'district' => 'Setiabudi'],
                ],
            ],
        ];

        $hasAdminDistrict = Schema::hasTable('admin_district');
        $hasAdminCity = Schema::hasTable('admin_city');

        foreach ($adminAssignments as $assign) {
            $admin = User::where('email', $assign['email'])->first();
            if (!$admin) {
                continue;
            }

            $districtIds = [];

            foreach ($assign['districts'] as $item) {
                $city = City::where('name', 'like', "%{$item['city']}%")->first();
                if ($city) {
                    $dist = District::where('city_id', $city->id)
                        ->where('name', 'like', "%{$item['district']}%")
                        ->first();
                    if ($dist) {
                        $districtIds[] = $dist->id;
                    }
                }
            }

            $districtIds = array_values(array_unique(array_filter(array_map('intval', $districtIds))));

            // Derives strictly and deterministically from unique parent city_ids of assigned districts
            $cityIds = District::whereIn('id', $districtIds)
                ->pluck('city_id')
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all();

            // Sync pivot `admin_district`
            if ($hasAdminDistrict) {
                $admin->managedDistricts()->sync($districtIds);
            }

            // Sync pivot `admin_city` (derived context only)
            if ($hasAdminCity) {
                $admin->managedCities()->sync($cityIds);
            }

            // Flush instance & request-level caching
            $admin->flushInstanceCache();
            User::flushRequestCache($admin->id);
        }

        $this->command?->info('✓ AdminCitySeeder berhasil menghubungkan Admin Wilayah ke Kecamatan dan Kota binaannya secara idempoten.');
    }
}
