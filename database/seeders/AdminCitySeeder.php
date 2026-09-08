<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menghubungkan seluruh akun Admin Wilayah ke Kecamatan (`admin_district`) dan Kota (`admin_city`) binaannya se-Indonesia.
     */
    public function run(): void
    {
        $adminAssignments = [
            // 1. Admin Sleman
            [
                'email'     => 'admin.sleman@sayabantu.com',
                'cityName'  => 'Sleman',
                'districts' => ['Depok', 'Mlati', 'Ngaglik', 'Gamping', 'Kalasan', 'Sleman'],
            ],
            // 2. Admin Yogyakarta
            [
                'email'     => 'admin@sayabantu.com',
                'cityName'  => 'Yogyakarta',
                'districts' => ['Gondomanan', 'Danurejan', 'Umbulharjo', 'Mantrijeron', 'Kotagede', 'Gedongtengen'],
            ],
            // 3. Admin Surakarta (Solo & Sukoharjo)
            [
                'email'     => 'admin.surakarta@sayabantu.com',
                'cityName'  => 'Surakarta',
                'districts' => ['Banjarsari', 'Jebres', 'Laweyan', 'Pasar Kliwon', 'Serengan', 'Kartasura', 'Baki'],
            ],
            // 4. Admin Jakarta Selatan
            [
                'email'     => 'admin.jaksel@sayabantu.com',
                'cityName'  => 'Jakarta Selatan',
                'districts' => ['Tebet', 'Kebayoran Baru', 'Setiabudi', 'Mampang Prapatan', 'Cilandak', 'Pancoran'],
            ],
            // 5. Admin Kota Bandung
            [
                'email'     => 'admin.bandung@sayabantu.com',
                'cityName'  => 'Bandung',
                'districts' => ['Coblong', 'Sukajadi', 'Lengkong', 'Sumur Bandung', 'Antapani', 'Cicendo'],
            ],
            // 6. Admin Kota Surabaya
            [
                'email'     => 'admin.surabaya@sayabantu.com',
                'cityName'  => 'Surabaya',
                'districts' => ['Wonokromo', 'Gubeng', 'Tegalsari', 'Sukolilo', 'Rungkut', 'Genteng'],
            ],
            // 7. Admin Kota Semarang
            [
                'email'     => 'admin.semarang@sayabantu.com',
                'cityName'  => 'Semarang',
                'districts' => ['Banyumanik', 'Tembalang', 'Semarang Tengah', 'Semarang Barat', 'Pedurungan', 'Gajahmungkur'],
            ],
            // 8. Admin Kota Denpasar
            [
                'email'     => 'admin.denpasar@sayabantu.com',
                'cityName'  => 'Denpasar',
                'districts' => ['Denpasar Selatan', 'Denpasar Barat', 'Denpasar Utara', 'Denpasar Timur'],
            ],
            // 9. Admin Kota Medan
            [
                'email'     => 'admin.medan@sayabantu.com',
                'cityName'  => 'Medan',
                'districts' => ['Medan Kota', 'Medan Petisah', 'Medan Baru', 'Medan Sunggal', 'Medan Helvetia', 'Medan Tembung'],
            ],
            // 10. Admin Kota Makassar
            [
                'email'     => 'admin.makassar@sayabantu.com',
                'cityName'  => 'Makassar',
                'districts' => ['Panakkukang', 'Rappocini', 'Ujung Pandang', 'Tamalanrea', 'Mariso', 'Bontoala'],
            ],
            // 11. Admin Kota Jakarta Barat
            [
                'email'     => 'admin.jakbar@sayabantu.com',
                'cityName'  => 'Jakarta Barat',
                'districts' => ['Kebon Jeruk', 'Kembangan', 'Palmerah', 'Grogol Petamburan', 'Cengkareng'],
            ],
            // 12. Admin Kota Jakarta Timur
            [
                'email'     => 'admin.jaktim@sayabantu.com',
                'cityName'  => 'Jakarta Timur',
                'districts' => ['Duren Sawit', 'Jatinegara', 'Matraman', 'Cakung', 'Pulogadung'],
            ],
            // 13. Admin Kota Malang
            [
                'email'     => 'admin.malang@sayabantu.com',
                'cityName'  => 'Malang',
                'districts' => ['Lowokwaru', 'Klojen', 'Blimbing', 'Sukun', 'Kedungkandang'],
            ],
            // 14. Admin Kota Palembang
            [
                'email'     => 'admin.palembang@sayabantu.com',
                'cityName'  => 'Palembang',
                'districts' => ['Ilir Barat I', 'Ilir Timur II', 'Kemuning', 'Sukarami', 'Bukit Kecil'],
            ],
            // 15. Admin Kota Tangerang Selatan
            [
                'email'     => 'admin.tangsel@sayabantu.com',
                'cityName'  => 'Tangerang Selatan',
                'districts' => ['Serpong', 'Ciputat Timur', 'Pondok Aren', 'Pamulang', 'Setu', 'Serpong Utara'],
            ],
        ];

        $now = now();
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

                // If not found in primary city, lookup across any matching district
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

            // Sync pivot `admin_city` (backward compatibility)
            if ($hasAdminCity && !empty($cityIds)) {
                $admin->managedCities()->sync($cityIds);
            }

            // Ensure primary district_id and city_id on user model are set
            if (!empty($districtIds)) {
                $admin->district_id = $districtIds[0];
            }
            if (!empty($cityIds)) {
                $admin->city_id = $cityIds[0];
            }
            $admin->save();

            $distCount = count($districtIds);
            $this->command->info("✓ Admin '{$admin->name}' ({$admin->email}) berhasil ditugaskan mengelola {$distCount} Kecamatan di {$assign['cityName']}.");
        }

        $this->command->info('AdminCitySeeder berhasil menghubungkan seluruh Admin Wilayah ke Kecamatan wewenangnya.');
    }
}
