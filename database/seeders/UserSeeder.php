<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

        $sampleKtp = 'ktp-photos/sample_ktp.jpg';
        $sampleSelfie = 'selfie-photos/sample_selfie.png';

        // Pastikan synthetic fixture terpasang di public storage untuk kelancaran demo/test di clean environment
        $fixtureDir = database_path('seeders/fixtures');
        $disk = Storage::disk('public');
        if (!$disk->exists($sampleKtp) && file_exists($fixtureDir . '/sample_ktp.jpg')) {
            $disk->put($sampleKtp, file_get_contents($fixtureDir . '/sample_ktp.jpg'));
        }
        if (!$disk->exists($sampleSelfie) && file_exists($fixtureDir . '/sample_selfie.png')) {
            $disk->put($sampleSelfie, file_get_contents($fixtureDir . '/sample_selfie.png'));
        }

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
        // 1. ADMIN WILAYAH
        // =========================================================================
        $admins = [
            [
                'email'       => 'admin@sayabantu.com',
                'name'        => 'Admin Wilayah DIY',
                'nik'         => '3471011505900001',
                'city'        => $jogjaCity,
                'district'    => $gondomananDist,
                'phone'       => '081234567801',
                'address'     => 'Pusat Operasional DIY, Gondomanan, Kota Yogyakarta',
            ],
            [
                'email'       => 'admin.sleman@sayabantu.com',
                'name'        => 'Admin Wilayah Sleman',
                'nik'         => '3404011505900002',
                'city'        => $slemanCity,
                'district'    => $ngaglikDist,
                'phone'       => '081234567802',
                'address'     => 'Kantor Cabang Sleman, Ngaglik, Sleman',
            ],
            [
                'email'       => 'admin.solo@sayabantu.com',
                'name'        => 'Admin Wilayah Surakarta',
                'nik'         => '3372011505900003',
                'city'        => $soloCity,
                'district'    => $banjarsariDist,
                'phone'       => '081234567803',
                'address'     => 'Kantor Operasional Solo, Banjarsari, Surakarta',
            ],
            [
                'email'       => 'admin.jaksel@sayabantu.com',
                'name'        => 'Admin Wilayah Jakarta Selatan',
                'nik'         => '3174011505900004',
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
                    'nik'               => $adm['nik'],
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
        // 2. MITRA (REKAN JASA)
        // =========================================================================
        $mitras = [
            [
                'email'          => 'mitra@sayabantu.com',
                'name'           => 'Budi Santoso',
                'nik'            => '3404011505950001',
                'city'           => $slemanCity,
                'district'       => $ngaglikDist,
                'phone'          => '081234567810',
                'gender'         => 'Laki-laki',
                'address'        => 'Jl. Palagan Tentara Pelajar KM 8, Ngaglik, Sleman',
                'place_of_birth' => 'Sleman',
                'date_of_birth'  => '1995-05-15',
                'rt'             => 1,
                'rw'             => 1,
                'kelurahan'      => $ngaglikDist?->name ?? 'Ngaglik',
                'occupation'     => 'Teknisi AC & Listrik Berpengalaman',
                'verified'       => true,
                'status'         => 'active',
            ],
            [
                'email'          => 'mitra.jogja@sayabantu.com',
                'name'           => 'Agus Setiawan',
                'nik'            => '3471011505950002',
                'city'           => $jogjaCity,
                'district'       => $gondomananDist,
                'phone'          => '081234567811',
                'gender'         => 'Laki-laki',
                'address'        => 'Jl. Malioboro No. 45, Gondomanan, Kota Yogyakarta',
                'place_of_birth' => 'Yogyakarta',
                'date_of_birth'  => '1993-08-20',
                'rt'             => 2,
                'rw'             => 3,
                'kelurahan'      => $gondomananDist?->name ?? 'Gondomanan',
                'occupation'     => 'Tukang Bangunan & Renovasi Ringan',
                'verified'       => true,
                'status'         => 'active',
            ],
            [
                'email'          => 'mitra.solo@sayabantu.com',
                'name'           => 'Eko Prasetyo',
                'nik'            => '3372011505950003',
                'city'           => $soloCity,
                'district'       => $banjarsariDist,
                'phone'          => '081234567812',
                'gender'         => 'Laki-laki',
                'address'        => 'Jl. Slamet Riyadi No. 120, Banjarsari, Surakarta',
                'place_of_birth' => 'Surakarta',
                'date_of_birth'  => '1991-11-12',
                'rt'             => 4,
                'rw'             => 2,
                'kelurahan'      => $banjarsariDist?->name ?? 'Banjarsari',
                'occupation'     => 'Jasa Angkut Barang & Kebersihan',
                'verified'       => true,
                'status'         => 'active',
            ],
            [
                'email'          => 'mitra.jaksel@sayabantu.com',
                'name'           => 'Hendra Wijaya',
                'nik'            => '3174011505950004',
                'city'           => $jakselCity,
                'district'       => $tebetDist,
                'phone'          => '081234567813',
                'gender'         => 'Laki-laki',
                'address'        => 'Jl. Tebet Barat Dalam No. 18, Tebet, Jakarta Selatan',
                'place_of_birth' => 'Jakarta Selatan',
                'date_of_birth'  => '1994-03-25',
                'rt'             => 5,
                'rw'             => 6,
                'kelurahan'      => $tebetDist?->name ?? 'Tebet',
                'occupation'     => 'Teknisi Elektronik & Mesin Cuci',
                'verified'       => true,
                'status'         => 'active',
            ],
            [
                'email'          => 'mitra.pending@sayabantu.com',
                'name'           => 'Rudi Hartono',
                'nik'            => '3404011505950005',
                'city'           => $slemanCity,
                'district'       => $ngaglikDist,
                'phone'          => '081234567814',
                'gender'         => 'Laki-laki',
                'address'        => 'Jl. Kaliurang KM 12, Ngaglik, Sleman',
                'place_of_birth' => 'Sleman',
                'date_of_birth'  => '1992-08-10',
                'rt'             => 1,
                'rw'             => 1,
                'kelurahan'      => $ngaglikDist?->name ?? 'Ngaglik',
                'occupation'     => 'Tukang Ledeng & Pompa Air',
                'verified'       => false,
                'status'         => 'inactive',
            ],
        ];

        foreach ($mitras as $mtr) {
            User::updateOrCreate(
                ['email' => $mtr['email']],
                [
                    'name'              => $mtr['name'],
                    'password'          => $commonPassword,
                    'role'              => 'mitra',
                    'nik'               => $mtr['nik'],
                    'gender'            => $mtr['gender'] ?? 'Laki-laki',
                    'place_of_birth'    => $mtr['place_of_birth'],
                    'date_of_birth'     => $mtr['date_of_birth'],
                    'rt'                => $mtr['rt'],
                    'rw'                => $mtr['rw'],
                    'kelurahan'         => $mtr['kelurahan'],
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
                    'ktp_photo'         => $sampleKtp,
                    'ktp_path'          => $sampleKtp,
                    'selfie_photo'      => $sampleSelfie,
                    'email_verified_at' => $now,
                ]
            );
        }

        // =========================================================================
        // 3. CUSTOMER (PEMESAN JASA)
        // =========================================================================
        $customers = [
            [
                'email'          => 'customer@sayabantu.com',
                'name'           => 'Rina Kusuma',
                'nik'            => '3404011505960001',
                'city'           => $slemanCity,
                'district'       => $ngaglikDist,
                'phone'          => '081234567820',
                'gender'         => 'Perempuan',
                'address'        => 'Perumahan Pondok Permai No. A-12, Ngaglik, Sleman',
                'place_of_birth' => 'Sleman',
                'date_of_birth'  => '1996-06-20',
                'rt'             => 1,
                'rw'             => 1,
                'kelurahan'      => $ngaglikDist?->name ?? 'Ngaglik',
            ],
            [
                'email'          => 'customer.jogja@sayabantu.com',
                'name'           => 'Dewi Lestari',
                'nik'            => '3471011505960002',
                'city'           => $jogjaCity,
                'district'       => $gondomananDist,
                'phone'          => '081234567821',
                'gender'         => 'Perempuan',
                'address'        => 'Jl. Panembahan Senopati No. 8, Gondomanan, Kota Yogyakarta',
                'place_of_birth' => 'Yogyakarta',
                'date_of_birth'  => '1997-04-14',
                'rt'             => 2,
                'rw'             => 1,
                'kelurahan'      => $gondomananDist?->name ?? 'Gondomanan',
            ],
            [
                'email'          => 'customer.solo@sayabantu.com',
                'name'           => 'Siti Aminah',
                'nik'            => '3372011505960003',
                'city'           => $soloCity,
                'district'       => $banjarsariDist,
                'phone'          => '081234567822',
                'gender'         => 'Perempuan',
                'address'        => 'Jl. Gajah Mada No. 34, Banjarsari, Surakarta',
                'place_of_birth' => 'Surakarta',
                'date_of_birth'  => '1998-09-05',
                'rt'             => 3,
                'rw'             => 4,
                'kelurahan'      => $banjarsariDist?->name ?? 'Banjarsari',
            ],
            [
                'email'          => 'customer.jaksel@sayabantu.com',
                'name'           => 'Bambang Susanto',
                'nik'            => '3174011505960004',
                'city'           => $jakselCity,
                'district'       => $tebetDist,
                'phone'          => '081234567823',
                'gender'         => 'Laki-laki',
                'address'        => 'Apartemen Casablanca Tower B, Tebet, Jakarta Selatan',
                'place_of_birth' => 'Jakarta Selatan',
                'date_of_birth'  => '1990-12-18',
                'rt'             => 1,
                'rw'             => 5,
                'kelurahan'      => $tebetDist?->name ?? 'Tebet',
            ],
        ];

        foreach ($customers as $cst) {
            User::updateOrCreate(
                ['email' => $cst['email']],
                [
                    'name'              => $cst['name'],
                    'password'          => $commonPassword,
                    'role'              => 'customer',
                    'nik'               => $cst['nik'],
                    'gender'            => $cst['gender'] ?? 'Perempuan',
                    'place_of_birth'    => $cst['place_of_birth'],
                    'date_of_birth'     => $cst['date_of_birth'],
                    'rt'                => $cst['rt'],
                    'rw'                => $cst['rw'],
                    'kelurahan'         => $cst['kelurahan'],
                    'city_id'           => $cst['city']?->id,
                    'district_id'       => $cst['district']?->id,
                    'verified'          => true,
                    'status'            => 'active',
                    'phone'             => $cst['phone'],
                    'address'           => $cst['address'],
                    'kecamatan'         => $cst['district']?->name,
                    'city'              => $cst['city']?->name,
                    'province'          => $cst['city']?->province ?? 'Indonesia',
                    'ktp_photo'         => $sampleKtp,
                    'ktp_path'          => $sampleKtp,
                    'selfie_photo'      => $sampleSelfie,
                    'email_verified_at' => $now,
                ]
            );
        }

        $this->command->info('✓ UserSeeder berhasil mengisi akun Superadmin, Admin Wilayah, Mitra, dan Customer.');
    }
}
