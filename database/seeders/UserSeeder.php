<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi akun autentikasi terstruktur & sederhana untuk kemudahan pengujian dan kesiapan deployment.
     */
    public function run(): void
    {
        $commonPassword = Hash::make('password');
        $now = now();

        // Helper untuk mencari City dan District
        $resolveLocation = function ($cityQuery, $districtName = null) {
            $city = is_numeric($cityQuery)
                ? (City::find($cityQuery) ?? City::where('code', (string)$cityQuery)->first())
                : City::where('name', 'like', "%{$cityQuery}%")->first();

            if (!$city) {
                $city = City::where('code', '3404')->first() ?? City::first();
            }

            $district = null;
            if ($city && $districtName) {
                $district = District::where('city_id', $city->id)
                    ->where('name', 'like', "%{$districtName}%")
                    ->first();
            }

            if (!$district && $city) {
                $district = District::where('city_id', $city->id)->first();
            }

            if (!$district) {
                $district = District::first();
            }

            return [$city, $district];
        };

        [$slemanCity, $ngaglikDist] = $resolveLocation('Sleman', 'Ngaglik');
        [$jogjaCity, $gondomananDist] = $resolveLocation('Yogyakarta', 'Gondomanan');
        [$soloCity, $banjarsariDist]  = $resolveLocation('Surakarta', 'Banjarsari');
        [$jakselCity, $tebetDist]     = $resolveLocation('Jakarta Selatan', 'Tebet');

        // =========================================================================
        // 1. SUPER ADMIN
        // =========================================================================
        User::updateOrCreate(
            ['email' => 'superadmin@sayabantu.com'],
            [
                'name'              => 'SuperAdmin SayaBantu',
                'password'          => $commonPassword,
                'role'              => 'super_admin',
                'nik'               => '3404011205850001',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1985-05-12',
                'city_id'           => $slemanCity?->id,
                'district_id'       => $ngaglikDist?->id,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567800',
                'address'           => 'Jl. Kaliurang KM 9.5, Sardonoharjo, Sleman',
                'kelurahan'         => 'Sardonoharjo',
                'kecamatan'         => $ngaglikDist?->name ?? 'Ngaglik',
                'city'              => $slemanCity?->name ?? 'Kabupaten Sleman',
                'province'          => 'D.I. Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Kawin',
                'occupation'        => 'Platform Administrator',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => $now,
            ]
        );

        // =========================================================================
        // 2. ADMIN WILAYAH
        // =========================================================================
        $admins = [
            [
                'email'       => 'admin@sayabantu.com',
                'name'        => 'Admin Wilayah DIY',
                'city'        => $jogjaCity,
                'district'    => $gondomananDist,
                'phone'       => '081234567801',
                'address'     => 'Pusat Operasional DIY, Gondomanan, Kota Yogyakarta',
            ],
            [
                'email'       => 'admin.sleman@sayabantu.com',
                'name'        => 'Admin Wilayah Sleman',
                'city'        => $slemanCity,
                'district'    => $ngaglikDist,
                'phone'       => '081234567802',
                'address'     => 'Kantor Cabang Sleman, Ngaglik, Sleman',
            ],
            [
                'email'       => 'admin.solo@sayabantu.com',
                'name'        => 'Admin Wilayah Surakarta',
                'city'        => $soloCity,
                'district'    => $banjarsariDist,
                'phone'       => '081234567803',
                'address'     => 'Kantor Operasional Solo, Banjarsari, Surakarta',
            ],
            [
                'email'       => 'admin.jaksel@sayabantu.com',
                'name'        => 'Admin Wilayah Jakarta Selatan',
                'city'        => $jakselCity,
                'district'    => $tebetDist,
                'phone'       => '081234567804',
                'address'     => 'Kantor Cabang Jakarta, Tebet, Jakarta Selatan',
            ],
        ];

        foreach ($admins as $adm) {
            User::updateOrCreate(
                ['email' => $adm['email']],
                [
                    'name'              => $adm['name'],
                    'password'          => $commonPassword,
                    'role'              => 'admin',
                    'nik'               => '3404' . rand(100000000000, 999999999999),
                    'gender'            => 'Laki-laki',
                    'city_id'           => $adm['city']?->id,
                    'district_id'       => $adm['district']?->id,
                    'verified'          => true,
                    'status'            => 'active',
                    'phone'             => $adm['phone'],
                    'address'           => $adm['address'],
                    'kecamatan'         => $adm['district']?->name,
                    'city'              => $adm['city']?->name,
                    'province'          => $adm['city']?->province ?? 'Indonesia',
                    'email_verified_at' => $now,
                ]
            );
        }

        // =========================================================================
        // 3. MITRA (REKAN JASA)
        // =========================================================================
        $mitras = [
            [
                'email'       => 'mitra@sayabantu.com',
                'name'        => 'Budi Santoso',
                'city'        => $slemanCity,
                'district'    => $ngaglikDist,
                'phone'       => '081234567810',
                'address'     => 'Jl. Palagan Tentara Pelajar KM 8, Ngaglik, Sleman',
                'occupation'  => 'Teknisi AC & Listrik Berpengalaman',
                'verified'    => true,
                'status'      => 'active',
            ],
            [
                'email'       => 'mitra.jogja@sayabantu.com',
                'name'        => 'Agus Setiawan',
                'city'        => $jogjaCity,
                'district'    => $gondomananDist,
                'phone'       => '081234567811',
                'address'     => 'Jl. Malioboro No. 45, Gondomanan, Kota Yogyakarta',
                'occupation'  => 'Tukang Bangunan & Renovasi Ringan',
                'verified'    => true,
                'status'      => 'active',
            ],
            [
                'email'       => 'mitra.solo@sayabantu.com',
                'name'        => 'Eko Prasetyo',
                'city'        => $soloCity,
                'district'    => $banjarsariDist,
                'phone'       => '081234567812',
                'address'     => 'Jl. Slamet Riyadi No. 120, Banjarsari, Surakarta',
                'occupation'  => 'Jasa Angkut Barang & Kebersihan',
                'verified'    => true,
                'status'      => 'active',
            ],
            [
                'email'       => 'mitra.jaksel@sayabantu.com',
                'name'        => 'Hendra Wijaya',
                'city'        => $jakselCity,
                'district'    => $tebetDist,
                'phone'       => '081234567813',
                'address'     => 'Jl. Tebet Barat Dalam No. 18, Tebet, Jakarta Selatan',
                'occupation'  => 'Teknisi Elektronik & Mesin Cuci',
                'verified'    => true,
                'status'      => 'active',
            ],
            [
                'email'       => 'mitra.pending@sayabantu.com',
                'name'        => 'Rudi Hartono',
                'city'        => $slemanCity,
                'district'    => $ngaglikDist,
                'phone'       => '081234567814',
                'address'     => 'Jl. Kaliurang KM 12, Ngaglik, Sleman',
                'occupation'  => 'Tukang Ledeng & Pompa Air',
                'verified'    => false,
                'status'      => 'inactive',
            ],
        ];

        foreach ($mitras as $mtr) {
            User::updateOrCreate(
                ['email' => $mtr['email']],
                [
                    'name'              => $mtr['name'],
                    'password'          => $commonPassword,
                    'role'              => 'mitra',
                    'nik'               => '3404' . rand(100000000000, 999999999999),
                    'gender'            => 'Laki-laki',
                    'city_id'           => $mtr['city']?->id,
                    'district_id'       => $mtr['district']?->id,
                    'verified'          => $mtr['verified'],
                    'status'            => $mtr['status'],
                    'phone'             => $mtr['phone'],
                    'address'           => $mtr['address'],
                    'kecamatan'         => $mtr['district']?->name,
                    'city'              => $mtr['city']?->name,
                    'province'          => $mtr['city']?->province ?? 'Indonesia',
                    'occupation'        => $mtr['occupation'],
                    'email_verified_at' => $now,
                ]
            );
        }

        // =========================================================================
        // 4. CUSTOMER (PEMESAN JASA)
        // =========================================================================
        $customers = [
            [
                'email'    => 'customer@sayabantu.com',
                'name'     => 'Rina Kusuma',
                'city'     => $slemanCity,
                'district' => $ngaglikDist,
                'phone'    => '081234567820',
                'address'  => 'Perumahan Pondok Permai No. A-12, Ngaglik, Sleman',
            ],
            [
                'email'    => 'customer.jogja@sayabantu.com',
                'name'     => 'Dewi Lestari',
                'city'     => $jogjaCity,
                'district' => $gondomananDist,
                'phone'    => '081234567821',
                'address'  => 'Jl. Panembahan Senopati No. 8, Gondomanan, Kota Yogyakarta',
            ],
            [
                'email'    => 'customer.solo@sayabantu.com',
                'name'     => 'Siti Aminah',
                'city'     => $soloCity,
                'district' => $banjarsariDist,
                'phone'    => '081234567822',
                'address'  => 'Jl. Gajah Mada No. 34, Banjarsari, Surakarta',
            ],
            [
                'email'    => 'customer.jaksel@sayabantu.com',
                'name'     => 'Bambang Susanto',
                'city'     => $jakselCity,
                'district' => $tebetDist,
                'phone'    => '081234567823',
                'address'  => 'Apartemen Casablanca Tower B, Tebet, Jakarta Selatan',
            ],
        ];

        foreach ($customers as $cst) {
            User::updateOrCreate(
                ['email' => $cst['email']],
                [
                    'name'              => $cst['name'],
                    'password'          => $commonPassword,
                    'role'              => 'customer',
                    'nik'               => '3404' . rand(100000000000, 999999999999),
                    'gender'            => 'Perempuan',
                    'city_id'           => $cst['city']?->id,
                    'district_id'       => $cst['district']?->id,
                    'verified'          => true,
                    'status'            => 'active',
                    'phone'             => $cst['phone'],
                    'address'           => $cst['address'],
                    'kecamatan'         => $cst['district']?->name,
                    'city'              => $cst['city']?->name,
                    'province'          => $cst['city']?->province ?? 'Indonesia',
                    'email_verified_at' => $now,
                ]
            );
        }

        $this->command->info('✓ UserSeeder berhasil mengisi akun Superadmin, Admin Wilayah, Mitra, dan Customer.');
    }
}
