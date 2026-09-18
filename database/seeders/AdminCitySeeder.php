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
     * Menghubungkan seluruh akun Admin Wilayah ke Kecamatan (`admin_district`) dan Kota (`admin_city`) binaannya.
     */
    public function run(): void
    {
        $adminAssignments = [
            [
                'email'     => 'admin@sayabantu.com',
                'cityName'  => 'Yogyakarta',
                'districts' => ['Gondomanan', 'Danurejan', 'Umbulharjo', 'Mantrijeron', 'Kotagede', 'Gedongtengen'],
            ],
            [
                'email'     => 'admin.sleman@sayabantu.com',
                'cityName'  => 'Sleman',
                'districts' => ['Depok', 'Mlati', 'Ngaglik', 'Gamping', 'Kalasan', 'Sleman'],
            ],
            [
                'email'     => 'admin.solo@sayabantu.com',
                'cityName'  => 'Surakarta',
                'districts' => ['Banjarsari', 'Jebres', 'Laweyan', 'Pasar Kliwon', 'Serengan'],
            ],
            [
                'email'     => 'admin.jaksel@sayabantu.com',
                'cityName'  => 'Jakarta Selatan',
                'districts' => ['Tebet', 'Kebayoran Baru', 'Setiabudi', 'Mampang Prapatan', 'Cilandak', 'Pancoran'],
            ],
        ];

        $hasAdminDistrict = Schema::hasTable('admin_district');
        $hasAdminCity = Schema::hasTable('admin_city');

        foreach ($adminAssignments as $assign) {
            $admin = User::where('email', $assign['email'])->first();
            if (!$admin) {
                continue;
            }

            $city = City::where('name', 'like', "%{$assign['cityName']}%")->first();
            $cityIds = $city ? [$city->id] : [];
            $districtIds = [];

            foreach ($assign['districts'] as $distName) {
                $query = District::where('name', 'like', "%{$distName}%");
                if ($city) {
                    $query->where('city_id', $city->id);
                }
                $dist = $query->first();

                if (!$dist) {
                    $dist = District::where('name', 'like', "%{$distName}%")->first();
                }

                if ($dist) {
                    $districtIds[] = $dist->id;
                    if ($dist->city_id && !in_array($dist->city_id, $cityIds, true)) {
                        $cityIds[] = $dist->city_id;
                    }
                }
            }

            // Sync pivot `admin_district`
            if ($hasAdminDistrict && !empty($districtIds)) {
                $admin->managedDistricts()->sync($districtIds);
            }

            // Sync pivot `admin_city`
            if ($hasAdminCity && !empty($cityIds)) {
                $admin->managedCities()->sync($cityIds);
            }

            if (!empty($districtIds)) {
                $admin->district_id = $districtIds[0];
            }
            if (!empty($cityIds)) {
                $admin->city_id = $cityIds[0];
            }
            $admin->save();
        }

        $this->command->info('✓ AdminCitySeeder berhasil menghubungkan Admin Wilayah ke Kota dan Kecamatan binaannya.');
    }
}
