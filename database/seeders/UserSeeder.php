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
     * Mengisi akun autentikasi skala besar se-Indonesia (Superadmin, Admin Wilayah Kecamatan, Mitra Ahli, & Customer Aktif)
     * Terdistribusi di berbagai provinsi & kota utama (DIY, Jateng, Jabar, DKI Jakarta, Jatim, Bali, Sumut, Sulsel).
     */
    public function run(): void
    {
        $commonPassword = Hash::make('password');

        // Helper function to resolve City and District safely
        $resolveLocation = function ($cityQuery, $districtName) {
            $city = null;
            if (is_numeric($cityQuery)) {
                $city = City::find($cityQuery) ?? City::where('code', (string)$cityQuery)->first();
            } else {
                $city = City::where('name', 'like', "%{$cityQuery}%")->first();
            }

            if (!$city) {
                // Fallback default
                $city = City::first();
            }

            $district = null;
            if ($city) {
                $district = District::where('city_id', $city->id)
                    ->where('name', 'like', "%{$districtName}%")
                    ->first();
                if (!$district) {
                    $district = District::where('city_id', $city->id)->first();
                }
            }

            if (!$district) {
                $district = District::first();
            }

            return [$city, $district];
        };

        // =========================================================================
        // 1. SUPER ADMIN (Nasional / Global Access)
        // =========================================================================
        [$slemanCity, $ngaglikDist] = $resolveLocation('Sleman', 'Ngaglik');

        User::updateOrCreate(
            ['email' => 'superadmin@sayabantu.com'],
            [
                'name'              => 'SuperAdmin',
                'password'          => $commonPassword,
                'role'              => 'super_admin',
                'nik'               => '3404011205850001',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1985-05-12',
                'city_id'           => $slemanCity?->id,
                'district_id'       => $ngaglikDist?->id,
                'ktp_path'          => 'ktp-photos/0Us0WrrpLJdWMrUkcRs1XS4FI3a9tBeZkkpaSAKE.jpg',
                'ktp_photo'         => 'ktp-photos/0Us0WrrpLJdWMrUkcRs1XS4FI3a9tBeZkkpaSAKE.jpg',
                'selfie_photo'      => 'selfie-photos/9lp933vpWL9YN6JQ8ISEbocs2qLwvr78DklO0dEt.png',
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567800',
                'address'           => 'Jl. Kaliurang KM 9.5 No. 100, Sardonoharjo, Kec. Ngaglik, Kabupaten Sleman, D.I. Yogyakarta 55581',
                'rt'                => 1,
                'rw'                => 3,
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
                'email_verified_at' => \Carbon\Carbon::parse('2026-08-01 07:00:00'),
                'created_at'        => \Carbon\Carbon::parse('2026-08-01 07:00:00'),
                'updated_at'        => \Carbon\Carbon::parse('2026-08-01 07:00:00'),
            ]
        );

        // =========================================================================
        // 2. ADMIN WILAYAH KECAMATAN (15 KOTA/KABUPATEN STRATEGIS SE-INDONESIA)
        // =========================================================================
        $adminConfigs = [
            // DIY: Sleman
            [
                'email'    => 'admin.sleman@sayabantu.com', 'name' => 'Dian Wahyuni, S.E', 'phone' => '081234567801',
                'city'     => 'Sleman', 'dist' => 'Depok', 'nik' => '3404024508900002', 'gender' => 'Perempuan',
                'pob'      => 'Sleman', 'dob' => '1990-08-15', 'address' => 'Jl. Ring Road Utara No. 12, Condongcatur, Kec. Depok, Kabupaten Sleman 55281', 'province' => 'D.I. Yogyakarta'
            ],
            // DIY: Kota Yogyakarta
            [
                'email'    => 'admin@sayabantu.com', 'name' => 'Siti Nurhaliza, M.M', 'phone' => '081234567802',
                'city'     => 'Yogyakarta', 'dist' => 'Umbulharjo', 'nik' => '3471015509920003', 'gender' => 'Perempuan',
                'pob'      => 'Yogyakarta', 'dob' => '1992-09-15', 'address' => 'Jl. Kenari No. 56, Muja Muju, Kec. Umbulharjo, Kota Yogyakarta 55165', 'province' => 'D.I. Yogyakarta'
            ],
            // Jawa Tengah: Surakarta (Solo)
            [
                'email'    => 'admin.surakarta@sayabantu.com', 'name' => 'Bambang Haryanto, S.Sos', 'phone' => '081234567803',
                'city'     => 'Surakarta', 'dist' => 'Banjarsari', 'nik' => '3372011504880001', 'gender' => 'Laki-laki',
                'pob'      => 'Surakarta', 'dob' => '1988-04-15', 'address' => 'Jl. Gajah Mada No. 88, Ketelan, Kec. Banjarsari, Kota Surakarta 57132', 'province' => 'Jawa Tengah'
            ],
            // Jawa Tengah: Semarang
            [
                'email'    => 'admin.semarang@sayabantu.com', 'name' => 'Hendro Prasetyo, S.T', 'phone' => '081234567807',
                'city'     => 'Semarang', 'dist' => 'Banyumanik', 'nik' => '3374011211880003', 'gender' => 'Laki-laki',
                'pob'      => 'Semarang', 'dob' => '1988-11-12', 'address' => 'Jl. Setiabudi No. 55, Srondol Kulon, Kec. Banyumanik, Kota Semarang 50263', 'province' => 'Jawa Tengah'
            ],
            // DKI Jakarta: Jakarta Selatan
            [
                'email'    => 'admin.jaksel@sayabantu.com', 'name' => 'Andi Wijaya, S.Kom', 'phone' => '081234567804',
                'city'     => 'Jakarta Selatan', 'dist' => 'Tebet', 'nik' => '3174011406890005', 'gender' => 'Laki-laki',
                'pob'      => 'Jakarta', 'dob' => '1989-06-14', 'address' => 'Jl. Tebet Raya No. 42, Tebet Barat, Kec. Tebet, Kota Jakarta Selatan 12810', 'province' => 'DKI Jakarta'
            ],
            // DKI Jakarta: Jakarta Barat
            [
                'email'    => 'admin.jakbar@sayabantu.com', 'name' => 'Rian Hidayat, S.Kom', 'phone' => '081234567811',
                'city'     => 'Jakarta Barat', 'dist' => 'Kebon Jeruk', 'nik' => '3173011902910004', 'gender' => 'Laki-laki',
                'pob'      => 'Jakarta', 'dob' => '1991-02-19', 'address' => 'Jl. Panjang No. 70, Kedoya Selatan, Kec. Kebon Jeruk, Kota Jakarta Barat 11520', 'province' => 'DKI Jakarta'
            ],
            // DKI Jakarta: Jakarta Timur
            [
                'email'    => 'admin.jaktim@sayabantu.com', 'name' => 'Farhan Mahendra, S.Ak', 'phone' => '081234567812',
                'city'     => 'Jakarta Timur', 'dist' => 'Duren Sawit', 'nik' => '3175012508900002', 'gender' => 'Laki-laki',
                'pob'      => 'Jakarta', 'dob' => '1990-08-25', 'address' => 'Jl. Raden Inten II No. 18, Duren Sawit, Kota Jakarta Timur 13440', 'province' => 'DKI Jakarta'
            ],
            // Jawa Barat: Kota Bandung
            [
                'email'    => 'admin.bandung@sayabantu.com', 'name' => 'Rahmat Hidayat, S.T', 'phone' => '081234567805',
                'city'     => 'Bandung', 'dist' => 'Coblong', 'nik' => '3273012010870002', 'gender' => 'Laki-laki',
                'pob'      => 'Bandung', 'dob' => '1987-10-20', 'address' => 'Jl. Dago Elos No. 7, Dago, Kec. Coblong, Kota Bandung 40135', 'province' => 'Jawa Barat'
            ],
            // Jawa Timur: Kota Surabaya
            [
                'email'    => 'admin.surabaya@sayabantu.com', 'name' => 'Tri Santoso, S.E', 'phone' => '081234567806',
                'city'     => 'Surabaya', 'dist' => 'Wonokromo', 'nik' => '3578010803910004', 'gender' => 'Laki-laki',
                'pob'      => 'Surabaya', 'dob' => '1991-03-08', 'address' => 'Jl. Darmo No. 110, Darmo, Kec. Wonokromo, Kota Surabaya 60241', 'province' => 'Jawa Timur'
            ],
            // Jawa Timur: Kota Malang
            [
                'email'    => 'admin.malang@sayabantu.com', 'name' => 'Achmad Fauzi, S.Pt', 'phone' => '081234567813',
                'city'     => 'Malang', 'dist' => 'Lowokwaru', 'nik' => '3573011405900007', 'gender' => 'Laki-laki',
                'pob'      => 'Malang', 'dob' => '1990-05-14', 'address' => 'Jl. Soekarno Hatta No. 40, Jatimulyo, Kec. Lowokwaru, Kota Malang 65141', 'province' => 'Jawa Timur'
            ],
            // Bali: Kota Denpasar
            [
                'email'    => 'admin.denpasar@sayabantu.com', 'name' => 'I Wayan Sudarta, S.Sos', 'phone' => '081234567808',
                'city'     => 'Denpasar', 'dist' => 'Denpasar Selatan', 'nik' => '5171012507860001', 'gender' => 'Laki-laki',
                'pob'      => 'Denpasar', 'dob' => '1986-07-25', 'address' => 'Jl. Hang Tuah No. 30, Sanur Kaja, Kec. Denpasar Selatan, Kota Denpasar 80227', 'province' => 'Bali'
            ],
            // Sumatera Utara: Kota Medan
            [
                'email'    => 'admin.medan@sayabantu.com', 'name' => 'Faisal Siregar, S.Kom', 'phone' => '081234567809',
                'city'     => 'Medan', 'dist' => 'Medan Kota', 'nik' => '1271011802900007', 'gender' => 'Laki-laki',
                'pob'      => 'Medan', 'dob' => '1990-02-18', 'address' => 'Jl. Brigjend Katamso No. 89, Kampung Baru, Kec. Medan Kota 20158', 'province' => 'Sumatera Utara'
            ],
            // Sumatera Selatan: Kota Palembang
            [
                'email'    => 'admin.palembang@sayabantu.com', 'name' => 'Muhammad Ridwan, S.E', 'phone' => '081234567814',
                'city'     => 'Palembang', 'dist' => 'Ilir Barat I', 'nik' => '1671010906890005', 'gender' => 'Laki-laki',
                'pob'      => 'Palembang', 'dob' => '1989-06-09', 'address' => 'Jl. Jenderal Sudirman No. 120, 20 Ilir D. IV, Kec. Ilir Barat I, Kota Palembang 30138', 'province' => 'Sumatera Selatan'
            ],
            // Sulawesi Selatan: Kota Makassar
            [
                'email'    => 'admin.makassar@sayabantu.com', 'name' => 'Daeng Rauf, S.E', 'phone' => '081234567810',
                'city'     => 'Makassar', 'dist' => 'Panakkukang', 'nik' => '7371010509890003', 'gender' => 'Laki-laki',
                'pob'      => 'Makassar', 'dob' => '1989-09-05', 'address' => 'Jl. Pengayoman No. 15, Masale, Kec. Panakkukang, Kota Makassar 90231', 'province' => 'Sulawesi Selatan'
            ],
            // Banten: Tangerang Selatan
            [
                'email'    => 'admin.tangsel@sayabantu.com', 'name' => 'Gunawan Wibowo, S.T', 'phone' => '081234567815',
                'city'     => 'Tangerang Selatan', 'dist' => 'Serpong', 'nik' => '3674011004880006', 'gender' => 'Laki-laki',
                'pob'      => 'Tangerang', 'dob' => '1988-04-10', 'address' => 'Jl. Pahlawan Seribu No. 25, Lengkong Gudang, Kec. Serpong, Kota Tangerang Selatan 15321', 'province' => 'Banten'
            ],
        ];

        foreach ($adminConfigs as $index => $cfg) {
            [$cityModel, $distModel] = $resolveLocation($cfg['city'], $cfg['dist']);
            $adminTime = \Carbon\Carbon::parse('2026-08-01 08:00:00')->addMinutes($index * 15);

            User::updateOrCreate(
                ['email' => $cfg['email']],
                [
                    'name'              => $cfg['name'],
                    'password'          => $commonPassword,
                    'role'              => 'admin',
                    'nik'               => $cfg['nik'],
                    'gender'            => $cfg['gender'],
                    'place_of_birth'    => $cfg['pob'],
                    'date_of_birth'     => $cfg['dob'],
                    'city_id'           => $cityModel?->id,
                    'district_id'       => $distModel?->id,
                    'ktp_path'          => 'ktp-photos/fGVuPrx8ZjTKgYkNFYzEaCraVufoRQePv6sG96pW.jpg',
                    'ktp_photo'         => 'ktp-photos/fGVuPrx8ZjTKgYkNFYzEaCraVufoRQePv6sG96pW.jpg',
                    'selfie_photo'      => 'selfie-photos/aj0rpJR0A1FiXWtxSvPovBxWdQoMCNJbWV3CdXB8.png',
                    'verified'          => true,
                    'status'            => 'active',
                    'phone'             => $cfg['phone'],
                    'address'           => $cfg['address'],
                    'rt'                => 2,
                    'rw'                => 4,
                    'kelurahan'         => 'Kelurahan Pusat',
                    'kecamatan'         => $distModel?->name ?? $cfg['dist'],
                    'city'              => $cityModel?->name ?? $cfg['city'],
                    'province'          => $cfg['province'],
                    'religion'          => 'Islam',
                    'marital_status'    => 'Kawin',
                    'occupation'        => "Admin Wilayah {$cityModel?->name}",
                    'is_greylisted'     => false,
                    'is_shadow_banned'  => false,
                    'warning_level'     => 0,
                    'email_verified_at' => $adminTime,
                    'created_at'        => $adminTime,
                    'updated_at'        => $adminTime,
                ]
            );
        }

        // =========================================================================
        // 3. REKAN JASA (36 MITRA AHLI SKALA BESAR SE-INDONESIA)
        // Beragam Profesi: Ledeng, Listrik PLN, AC/Kulkas, Mebel, Las, Bangunan, Montir, Kebun, dll.
        // =========================================================================
        $mitraList = [
            // --- DIY: Sleman ---
            [
                'email' => 'mitra.sleman1@sayabantu.com', 'name' => 'Agus Prasetyo', 'phone' => '081234567821',
                'city' => 'Sleman', 'dist' => 'Depok', 'skills' => 'Tukang Ledeng, Pompa Air & Saluran Mampet',
                'nik' => '3404031708940003', 'dob' => '1994-08-17', 'pob' => 'Sleman', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Gejayan No. 25, Caturtunggal, Kec. Depok, Kabupaten Sleman 55281', 'prov' => 'D.I. Yogyakarta'
            ],
            [
                'email' => 'mitra.sleman2@sayabantu.com', 'name' => 'Budi Santoso', 'phone' => '081234567822',
                'city' => 'Sleman', 'dist' => 'Mlati', 'skills' => 'Servis AC, Kulkas Inverter & Mesin Cuci',
                'nik' => '3404041005910002', 'dob' => '1991-05-10', 'pob' => 'Sleman', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Magelang KM 6, Sinduadi, Kec. Mlati, Kabupaten Sleman 55284', 'prov' => 'D.I. Yogyakarta'
            ],
            [
                'email' => 'mitra.sleman3@sayabantu.com', 'name' => 'Joko Widodo', 'phone' => '081234567823',
                'city' => 'Sleman', 'dist' => 'Ngaglik', 'skills' => 'Pertukangan Kayu, Atap Genteng & Bangunan',
                'nik' => '3404052106880004', 'dob' => '1988-06-21', 'pob' => 'Sleman', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Kaliurang KM 10, Sardonoharjo, Kec. Ngaglik, Kabupaten Sleman 55581', 'prov' => 'D.I. Yogyakarta'
            ],
            [
                'email' => 'mitra@sayabantu.com', 'name' => 'Eko Nugroho', 'phone' => '081234567824',
                'city' => 'Sleman', 'dist' => 'Gamping', 'skills' => 'Angkut Pindahan Kos & Bersih Kebun',
                'nik' => '3404061503930001', 'dob' => '1993-03-15', 'pob' => 'Sleman', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Wates KM 5, Ambarketawang, Kec. Gamping, Kabupaten Sleman 55294', 'prov' => 'D.I. Yogyakarta'
            ],

            // --- DIY: Kota Yogyakarta ---
            [
                'email' => 'mitra.jogja1@sayabantu.com', 'name' => 'Surya Saputra', 'phone' => '081234567825',
                'city' => 'Yogyakarta', 'dist' => 'Gondomanan', 'skills' => 'Instalasi Listrik PLN, Lampu & Saklar',
                'nik' => '3471021204920005', 'dob' => '1992-04-12', 'pob' => 'Yogyakarta', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Brigjend Katamso No. 78, Prawirodirjan, Kec. Gondomanan, Kota Yogyakarta 55121', 'prov' => 'D.I. Yogyakarta'
            ],
            [
                'email' => 'mitra.jogja2@sayabantu.com', 'name' => 'Hendra Setiawan', 'phone' => '081234567826',
                'city' => 'Yogyakarta', 'dist' => 'Danurejan', 'skills' => 'Tambal Ban Panggilan & Montir Motor Darurat',
                'nik' => '3471032511900003', 'dob' => '1990-11-25', 'pob' => 'Yogyakarta', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Hayam Wuruk No. 14, Bausasran, Kec. Danurejan, Kota Yogyakarta 55211', 'prov' => 'D.I. Yogyakarta'
            ],
            [
                'email' => 'mitra.jogja3@sayabantu.com', 'name' => 'Danang Prasetyo', 'phone' => '081234567841',
                'city' => 'Yogyakarta', 'dist' => 'Umbulharjo', 'skills' => 'Tukang Kunci Panggilan & Pasang Grendel Pintu',
                'nik' => '3471041806930006', 'dob' => '1993-06-18', 'pob' => 'Yogyakarta', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Veteran No. 89, Pandeyan, Umbulharjo, Kota Yogyakarta 55161', 'prov' => 'D.I. Yogyakarta'
            ],

            // --- Jawa Tengah: Surakarta & Sukoharjo ---
            [
                'email' => 'mitra.surakarta1@sayabantu.com', 'name' => 'Eko Susanto', 'phone' => '081234567827',
                'city' => 'Surakarta', 'dist' => 'Banjarsari', 'skills' => 'Pemasangan Lampu Gantung & Kelistrikan',
                'nik' => '3372021402890001', 'dob' => '1989-02-14', 'pob' => 'Surakarta', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Veteran No. 34, Timuran, Kec. Banjarsari, Kota Surakarta 57131', 'prov' => 'Jawa Tengah'
            ],
            [
                'email' => 'mitra.surakarta2@sayabantu.com', 'name' => 'Dwi Haryanto', 'phone' => '081234567828',
                'city' => 'Surakarta', 'dist' => 'Laweyan', 'skills' => 'Cat Dinding Rumah & Plafon Gypsum',
                'nik' => '3372031908920002', 'dob' => '1992-08-19', 'pob' => 'Surakarta', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Dr. Radjiman No. 402, Sondakan, Kec. Laweyan, Kota Surakarta 57147', 'prov' => 'Jawa Tengah'
            ],
            [
                'email' => 'mitra.sukoharjo1@sayabantu.com', 'name' => 'Sugeng Riyadi', 'phone' => '081234567829',
                'city' => 'Sukoharjo', 'dist' => 'Kartasura', 'skills' => 'Cuci AC Split & Servis Elektronik',
                'nik' => '3311021107900004', 'dob' => '1990-07-11', 'pob' => 'Sukoharjo', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Ahmad Yani No. 150, Kartasura, Kec. Kartasura, Kabupaten Sukoharjo 57169', 'prov' => 'Jawa Tengah'
            ],
            [
                'email' => 'mitra.sukoharjo2@sayabantu.com', 'name' => 'Tri Wahyudi', 'phone' => '081234567842',
                'city' => 'Sukoharjo', 'dist' => 'Grogol', 'skills' => 'Bor Dinding, Pasang Ambalan & Pasang Keramik',
                'nik' => '3311032804910007', 'dob' => '1991-04-28', 'pob' => 'Sukoharjo', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Ir. Soekarno No. 45, Madegondo, Grogol, Sukoharjo 57552', 'prov' => 'Jawa Tengah'
            ],
            [
                'email' => 'mitra.semarang1@sayabantu.com', 'name' => 'Bambang Pamungkas', 'phone' => '081234567843',
                'city' => 'Semarang', 'dist' => 'Banyumanik', 'skills' => 'Servis Pompa Air Jetpump & Toren Otomatis',
                'nik' => '3374020905920005', 'dob' => '1992-05-09', 'pob' => 'Semarang', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Grafika Raya No. 12, Gedawang, Banyumanik, Semarang 50266', 'prov' => 'Jawa Tengah'
            ],
            [
                'email' => 'mitra.semarang2@sayabantu.com', 'name' => 'Kuswanto', 'phone' => '081234567844',
                'city' => 'Semarang', 'dist' => 'Tembalang', 'skills' => 'Perbaikan Pagar Besi, Tralis & Las Kanopi',
                'nik' => '3374031508890003', 'dob' => '1989-08-15', 'pob' => 'Semarang', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Prof. Soedarto No. 30, Tembalang, Kota Semarang 50275', 'prov' => 'Jawa Tengah'
            ],

            // --- DKI Jakarta: Jakarta Selatan, Barat, Timur ---
            [
                'email' => 'mitra.jaksel1@sayabantu.com', 'name' => 'Fahmi Idris', 'phone' => '081234567830',
                'city' => 'Jakarta Selatan', 'dist' => 'Tebet', 'skills' => 'Teknisi AC Inverter, Kulkas 2 Pintu & Chiller',
                'nik' => '3174020904910006', 'dob' => '1991-04-09', 'pob' => 'Jakarta', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Tebet Barat Dalam No. 18, Kec. Tebet, Kota Jakarta Selatan 12810', 'prov' => 'DKI Jakarta'
            ],
            [
                'email' => 'mitra.jaksel2@sayabantu.com', 'name' => 'Doni Darmawan', 'phone' => '081234567831',
                'city' => 'Jakarta Selatan', 'dist' => 'Kebayoran Baru', 'skills' => 'Plumbing Sanitasi, Water Heater & Siphon',
                'nik' => '3174031508880002', 'dob' => '1988-08-15', 'pob' => 'Jakarta', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Gandaria I No. 4, Kramat Pela, Kec. Kebayoran Baru, Jakarta Selatan 12130', 'prov' => 'DKI Jakarta'
            ],
            [
                'email' => 'mitra.jaksel3@sayabantu.com', 'name' => 'Teguh Santoso', 'phone' => '081234567832',
                'city' => 'Jakarta Selatan', 'dist' => 'Setiabudi', 'skills' => 'Deep Cleaning Rumah, Cuci Sofa Microfiber & Kasur',
                'nik' => '3174042012930005', 'dob' => '1993-12-20', 'pob' => 'Jakarta', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Rasuna Said No. 70, Karet Kuningan, Kec. Setiabudi, Jakarta Selatan 12940', 'prov' => 'DKI Jakarta'
            ],
            [
                'email' => 'mitra.jakbar1@sayabantu.com', 'name' => 'Hendra Kurniawan', 'phone' => '081234567845',
                'city' => 'Jakarta Barat', 'dist' => 'Kebon Jeruk', 'skills' => 'Pasang Bracket TV Dinding, CCTV & Jaringan Wifi',
                'nik' => '3173021406900008', 'dob' => '1990-06-14', 'pob' => 'Jakarta', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Surya Wijaya No. 22, Kedoya Utara, Kebon Jeruk, Jakarta Barat 11520', 'prov' => 'DKI Jakarta'
            ],
            [
                'email' => 'mitra.jaktim1@sayabantu.com', 'name' => 'Rizky Ramadhan', 'phone' => '081234567846',
                'city' => 'Jakarta Timur', 'dist' => 'Duren Sawit', 'skills' => 'Servis Mesin Cuci 1 & 2 Tabung, Kulkas & Dispenser',
                'nik' => '3175022802920003', 'dob' => '1992-02-28', 'pob' => 'Jakarta', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Pondok Kelapa Raya No. 55, Duren Sawit, Jakarta Timur 13450', 'prov' => 'DKI Jakarta'
            ],

            // --- Jawa Barat: Kota Bandung ---
            [
                'email' => 'mitra.bandung1@sayabantu.com', 'name' => 'Asep Sunandar', 'phone' => '081234567833',
                'city' => 'Bandung', 'dist' => 'Coblong', 'skills' => 'Tukang Kayu, Rakit Lemari Flatpack & Pasang Pintu',
                'nik' => '3273021805900001', 'dob' => '1990-05-18', 'pob' => 'Bandung', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Tubagus Ismail No. 22, Sekeloa, Kec. Coblong, Kota Bandung 40134', 'prov' => 'Jawa Barat'
            ],
            [
                'email' => 'mitra.bandung2@sayabantu.com', 'name' => 'Cecep Supriatna', 'phone' => '081234567834',
                'city' => 'Bandung', 'dist' => 'Sukajadi', 'skills' => 'Bongkar Pasang Pompa Air & Filter Toren Air',
                'nik' => '3273030409890004', 'dob' => '1989-09-04', 'pob' => 'Bandung', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Sukajadi No. 115, Cipedes, Kec. Sukajadi, Kota Bandung 40162', 'prov' => 'Jawa Barat'
            ],
            [
                'email' => 'mitra.bandung3@sayabantu.com', 'name' => 'Dadan Ramdani', 'phone' => '081234567847',
                'city' => 'Bandung', 'dist' => 'Sumur Bandung', 'skills' => 'Pengecatan Rumah, Plafon Gypsum & Waterproofing',
                'nik' => '3273041903910006', 'dob' => '1991-03-19', 'pob' => 'Bandung', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Naripan No. 44, Kebon Pisang, Sumur Bandung, Kota Bandung 40112', 'prov' => 'Jawa Barat'
            ],

            // --- Jawa Timur: Kota Surabaya & Malang ---
            [
                'email' => 'mitra.surabaya1@sayabantu.com', 'name' => 'Cak Slamet Riyadi', 'phone' => '081234567835',
                'city' => 'Surabaya', 'dist' => 'Wonokromo', 'skills' => 'Beres-Beres Halaman, Potong Rumput & Angkut Sampah',
                'nik' => '3578021406870003', 'dob' => '1987-06-14', 'pob' => 'Surabaya', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Joyoboyo No. 8, Sawunggaling, Kec. Wonokromo, Kota Surabaya 60242', 'prov' => 'Jawa Timur'
            ],
            [
                'email' => 'mitra.surabaya2@sayabantu.com', 'name' => 'Bambang Sugiarto', 'phone' => '081234567836',
                'city' => 'Surabaya', 'dist' => 'Gubeng', 'skills' => 'Cuci AC Split, Kulkas & Pemasangan Exhaust Fan',
                'nik' => '3578032210910002', 'dob' => '1991-10-22', 'pob' => 'Surabaya', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Kertajaya No. 89, Airlangga, Kec. Gubeng, Kota Surabaya 60286', 'prov' => 'Jawa Timur'
            ],
            [
                'email' => 'mitra.surabaya3@sayabantu.com', 'name' => 'Wahyudi Utomo', 'phone' => '081234567848',
                'city' => 'Surabaya', 'dist' => 'Rungkut', 'skills' => 'Jasa Pindahan Rumah, Angkut Barang Pick-Up & Pasang Mebel',
                'nik' => '3578041501930007', 'dob' => '1993-01-15', 'pob' => 'Surabaya', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Rungkut Asri Timur No. 12, Rungkut Kidul, Surabaya 60293', 'prov' => 'Jawa Timur'
            ],
            [
                'email' => 'mitra.malang1@sayabantu.com', 'name' => 'Bayu Wicaksono', 'phone' => '081234567849',
                'city' => 'Malang', 'dist' => 'Lowokwaru', 'skills' => 'Servis AC Kamar Kost, Kelistrikan & Water Heater',
                'nik' => '3573021907920004', 'dob' => '1992-07-19', 'pob' => 'Malang', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Sigura-Gura No. 20, Sumbersari, Lowokwaru, Kota Malang 65145', 'prov' => 'Jawa Timur'
            ],

            // --- Bali: Kota Denpasar ---
            [
                'email' => 'mitra.denpasar1@sayabantu.com', 'name' => 'I Made Sukadana', 'phone' => '081234567837',
                'city' => 'Denpasar', 'dist' => 'Denpasar Selatan', 'skills' => 'Servis AC Villa, Kelistrikan & Pompa Sirkulasi Kolam',
                'nik' => '5171020703890001', 'dob' => '1989-03-07', 'pob' => 'Denpasar', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Danau Poso No. 45, Sanur Kauh, Kec. Denpasar Selatan 80228', 'prov' => 'Bali'
            ],
            [
                'email' => 'mitra.denpasar2@sayabantu.com', 'name' => 'I Wayan Gede Arsana', 'phone' => '081234567850',
                'city' => 'Denpasar', 'dist' => 'Denpasar Barat', 'skills' => 'Pertukangan Bangunan, Pasang Keramik & Atap Genteng',
                'nik' => '5171031409910006', 'dob' => '1991-09-14', 'pob' => 'Denpasar', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Teuku Umar No. 102, Dauh Puri Kauh, Denpasar Barat 80113', 'prov' => 'Bali'
            ],

            // --- Sumatera Utara: Kota Medan ---
            [
                'email' => 'mitra.medan1@sayabantu.com', 'name' => 'Ucok Harahap', 'phone' => '081234567838',
                'city' => 'Medan', 'dist' => 'Medan Kota', 'skills' => 'Tambal Ban Tubeless, Ganti Oli & Montir Panggilan',
                'nik' => '1271021901920004', 'dob' => '1992-01-19', 'pob' => 'Medan', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Sisingamangaraja No. 120, Mesjid, Kec. Medan Kota 20217', 'prov' => 'Sumatera Utara'
            ],
            [
                'email' => 'mitra.medan2@sayabantu.com', 'name' => 'Baringin Simanjuntak', 'phone' => '081234567865',
                'city' => 'Medan', 'dist' => 'Medan Baru', 'skills' => 'Servis AC Ruko & Rumah, Las Pagar Besi & Kanopi',
                'nik' => '1271032804890008', 'dob' => '1989-04-28', 'pob' => 'Medan', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Iskandar Muda No. 76, Babura, Medan Baru, Kota Medan 20154', 'prov' => 'Sumatera Utara'
            ],

            // --- Sumatera Selatan: Kota Palembang ---
            [
                'email' => 'mitra.palembang1@sayabantu.com', 'name' => 'Rian Kurniawan', 'phone' => '081234567866',
                'city' => 'Palembang', 'dist' => 'Ilir Barat I', 'skills' => 'Teknisi Listrik, Pasang Saklar, MCB & Lampu Hias',
                'nik' => '1671021406930005', 'dob' => '1993-06-14', 'pob' => 'Palembang', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Angkatan 45 No. 88, Demang Lebar Daun, Ilir Barat I, Palembang 30137', 'prov' => 'Sumatera Selatan'
            ],

            // --- Sulawesi Selatan: Kota Makassar ---
            [
                'email' => 'mitra.makassar1@sayabantu.com', 'name' => 'Baso Daeng Tompo', 'phone' => '081234567839',
                'city' => 'Makassar', 'dist' => 'Panakkukang', 'skills' => 'Tukang Las Kanopi, Pintu Pagar & Konstruksi Besi',
                'nik' => '7371022808880005', 'dob' => '1988-08-28', 'pob' => 'Makassar', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Toddopuli Raya No. 33, Pandang, Kec. Panakkukang, Kota Makassar 90222', 'prov' => 'Sulawesi Selatan'
            ],
            [
                'email' => 'mitra.makassar2@sayabantu.com', 'name' => 'Andi Muhammad Ilham', 'phone' => '081234567867',
                'city' => 'Makassar', 'dist' => 'Tamalanrea', 'skills' => 'Servis AC Split, Kulkas & Pipa Air Mampet',
                'nik' => '7371031905920002', 'dob' => '1992-05-19', 'pob' => 'Makassar', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Perintis Kemerdekaan KM 10, Tamalanrea Indah, Makassar 90245', 'prov' => 'Sulawesi Selatan'
            ],

            // --- Banten: Tangerang Selatan ---
            [
                'email' => 'mitra.tangsel1@sayabantu.com', 'name' => 'Agung Nugroho', 'phone' => '081234567868',
                'city' => 'Tangerang Selatan', 'dist' => 'Serpong', 'skills' => 'Deep Cleaning Rumah, Sofa & Jasa Angkut Pindahan',
                'nik' => '3674021208910009', 'dob' => '1991-08-12', 'pob' => 'Tangerang', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Ciater Raya No. 15, Rawa Mekar Jaya, Serpong, Tangsel 15310', 'prov' => 'Banten'
            ],
        ];

        foreach ($mitraList as $index => $m) {
            [$cModel, $dModel] = $resolveLocation($m['city'], $m['dist']);
            $regTime = \Carbon\Carbon::parse('2026-08-02 08:00:00')->addMinutes($index * 20);
            $verifiedTime = $regTime->copy()->addMinutes(45);

            User::updateOrCreate(
                ['email' => $m['email']],
                [
                    'name'              => $m['name'],
                    'password'          => $commonPassword,
                    'role'              => 'mitra',
                    'nik'               => $m['nik'],
                    'gender'            => $m['gender'],
                    'place_of_birth'    => $m['pob'],
                    'date_of_birth'     => $m['dob'],
                    'city_id'           => $cModel?->id,
                    'district_id'       => $dModel?->id,
                    'ktp_path'          => 'ktp-photos/0Us0WrrpLJdWMrUkcRs1XS4FI3a9tBeZkkpaSAKE.jpg',
                    'ktp_photo'         => 'ktp-photos/0Us0WrrpLJdWMrUkcRs1XS4FI3a9tBeZkkpaSAKE.jpg',
                    'selfie_photo'      => 'selfie-photos/9lp933vpWL9YN6JQ8ISEbocs2qLwvr78DklO0dEt.png',
                    'verified'          => true,
                    'status'            => 'active',
                    'phone'             => $m['phone'],
                    'address'           => $m['addr'],
                    'rt'                => 1,
                    'rw'                => 2,
                    'kelurahan'         => 'Kelurahan Mitra',
                    'kecamatan'         => $dModel?->name ?? $m['dist'],
                    'city'              => $cModel?->name ?? $m['city'],
                    'province'          => $m['prov'],
                    'religion'          => 'Islam',
                    'marital_status'    => 'Kawin',
                    'occupation'        => $m['skills'],
                    'is_greylisted'     => false,
                    'is_shadow_banned'  => false,
                    'warning_level'     => 0,
                    'email_verified_at' => $verifiedTime,
                    'created_at'        => $regTime,
                    'updated_at'        => $verifiedTime,
                ]
            );
        }

        // =========================================================================
        // 4. CUSTOMER (30 PEMOHON BANTUAN AKTIF SE-INDONESIA)
        // =========================================================================
        $customerList = [
            // --- DIY: Sleman & Jogja ---
            [
                'email' => 'customer.sleman1@sayabantu.com', 'name' => 'Rina Kusuma', 'phone' => '081234567851',
                'city' => 'Sleman', 'dist' => 'Depok', 'nik' => '3404074503970001', 'dob' => '1997-03-15', 'pob' => 'Sleman', 'gender' => 'Perempuan',
                'addr' => 'Kost Putri Anggrek, Jl. Affandi No. 45, Caturtunggal, Depok, Sleman 55281', 'prov' => 'D.I. Yogyakarta',
                'job' => 'Mahasiswi Pascasarjana UGM'
            ],
            [
                'email' => 'customer.sleman2@sayabantu.com', 'name' => 'Muhammad Farhan', 'phone' => '081234567852',
                'city' => 'Sleman', 'dist' => 'Mlati', 'nik' => '3404081210940003', 'dob' => '1994-10-12', 'pob' => 'Sleman', 'gender' => 'Laki-laki',
                'addr' => 'Perumahan Mlati Asri No. C-4, Sinduadi, Mlati, Sleman 55284', 'prov' => 'D.I. Yogyakarta',
                'job' => 'Software Engineer'
            ],
            [
                'email' => 'customer@sayabantu.com', 'name' => 'Siti Rahmawati', 'phone' => '081234567853',
                'city' => 'Sleman', 'dist' => 'Ngaglik', 'nik' => '3404095509920002', 'dob' => '1992-09-15', 'pob' => 'Sleman', 'gender' => 'Perempuan',
                'addr' => 'Jl. Palagan Tentara Pelajar KM 8.5, Sariharjo, Ngaglik, Sleman 55581', 'prov' => 'D.I. Yogyakarta',
                'job' => 'Ibu Rumah Tangga & Pengusaha Kuliner'
            ],
            [
                'email' => 'customer.sleman3@sayabantu.com', 'name' => 'Agung Prasetya, S.T', 'phone' => '081234567871',
                'city' => 'Sleman', 'dist' => 'Gamping', 'nik' => '3404101402930005', 'dob' => '1993-02-14', 'pob' => 'Sleman', 'gender' => 'Laki-laki',
                'addr' => 'Perum Griya Kencana No. B-12, Ambarketawang, Gamping, Sleman 55294', 'prov' => 'D.I. Yogyakarta',
                'job' => 'Dosen Teknik'
            ],
            [
                'email' => 'customer.jogja1@sayabantu.com', 'name' => 'Arif Budiman', 'phone' => '081234567854',
                'city' => 'Yogyakarta', 'dist' => 'Danurejan', 'nik' => '3471041908950004', 'dob' => '1995-08-19', 'pob' => 'Yogyakarta', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Mataram No. 60, Suryatmajan, Danurejan, Kota Yogyakarta 55213', 'prov' => 'D.I. Yogyakarta',
                'job' => 'Pemilik Kafe & Resto'
            ],
            [
                'email' => 'customer.jogja2@sayabantu.com', 'name' => 'Nia Ramadhani', 'phone' => '081234567872',
                'city' => 'Yogyakarta', 'dist' => 'Gondomanan', 'nik' => '3471055204960007', 'dob' => '1996-04-12', 'pob' => 'Yogyakarta', 'gender' => 'Perempuan',
                'addr' => 'Butik Cantika, Jl. Ibu Ruswo No. 24, Prawirodirjan, Gondomanan, Yogyakarta 55121', 'prov' => 'D.I. Yogyakarta',
                'job' => 'Fashion Designer'
            ],

            // --- Jawa Tengah: Surakarta, Sukoharjo & Semarang ---
            [
                'email' => 'customer.surakarta1@sayabantu.com', 'name' => 'Dewi Lestari', 'phone' => '081234567855',
                'city' => 'Surakarta', 'dist' => 'Banjarsari', 'nik' => '3372045506930002', 'dob' => '1993-06-15', 'pob' => 'Surakarta', 'gender' => 'Perempuan',
                'addr' => 'Jl. Slamet Riyadi No. 182, Timuran, Banjarsari, Surakarta 57131', 'prov' => 'Jawa Tengah',
                'job' => 'Karyawan Swasta'
            ],
            [
                'email' => 'customer.surakarta2@sayabantu.com', 'name' => 'Rizky Pratama', 'phone' => '081234567856',
                'city' => 'Surakarta', 'dist' => 'Laweyan', 'nik' => '3372051012900001', 'dob' => '1990-12-10', 'pob' => 'Surakarta', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Perintis Kemerdekaan No. 30, Sondakan, Laweyan, Surakarta 57147', 'prov' => 'Jawa Tengah',
                'job' => 'Desainer Interior'
            ],
            [
                'email' => 'customer.sukoharjo1@sayabantu.com', 'name' => 'Indah Permatasari', 'phone' => '081234567857',
                'city' => 'Sukoharjo', 'dist' => 'Kartasura', 'nik' => '3311035508980001', 'dob' => '1998-08-15', 'pob' => 'Sukoharjo', 'gender' => 'Perempuan',
                'addr' => 'Perumahan Baki Indah Blok C-12, Kudu, Sukoharjo 57556', 'prov' => 'Jawa Tengah',
                'job' => 'Dokter Umum'
            ],
            [
                'email' => 'customer.sukoharjo2@sayabantu.com', 'name' => 'Bayu Wicaksono', 'phone' => '081234567873',
                'city' => 'Sukoharjo', 'dist' => 'Grogol', 'nik' => '3311041804910008', 'dob' => '1991-04-18', 'pob' => 'Sukoharjo', 'gender' => 'Laki-laki',
                'addr' => 'Apotek Sehat Medika, Jl. Raya Solo Baru No. 8, Madegondo, Grogol 57552', 'prov' => 'Jawa Tengah',
                'job' => 'Apoteker'
            ],
            [
                'email' => 'customer.semarang1@sayabantu.com', 'name' => 'drg. Maya Kusuma', 'phone' => '081234567874',
                'city' => 'Semarang', 'dist' => 'Banyumanik', 'nik' => '3374045509930006', 'dob' => '1993-09-15', 'pob' => 'Semarang', 'gender' => 'Perempuan',
                'addr' => 'Jl. Sukun Raya No. 35, Srondol Wetan, Banyumanik, Semarang 50263', 'prov' => 'Jawa Tengah',
                'job' => 'Dokter Gigi'
            ],

            // --- DKI Jakarta: Jakarta Selatan, Barat, Timur ---
            [
                'email' => 'customer.jaksel1@sayabantu.com', 'name' => 'Amanda Putri', 'phone' => '081234567858',
                'city' => 'Jakarta Selatan', 'dist' => 'Tebet', 'nik' => '3174055204960003', 'dob' => '1996-04-12', 'pob' => 'Jakarta', 'gender' => 'Perempuan',
                'addr' => 'Apartemen Casablanca Tower B Lt 15, Menteng Dalam, Tebet, Jakarta Selatan 12870', 'prov' => 'DKI Jakarta',
                'job' => 'Digital Marketing Lead'
            ],
            [
                'email' => 'customer.jaksel2@sayabantu.com', 'name' => 'Kevin Sanjaya', 'phone' => '081234567859',
                'city' => 'Jakarta Selatan', 'dist' => 'Kebayoran Baru', 'nik' => '3174061807920005', 'dob' => '1992-07-18', 'pob' => 'Jakarta', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Senopati No. 88, Selong, Kebayoran Baru, Jakarta Selatan 12110', 'prov' => 'DKI Jakarta',
                'job' => 'Konsultan Bisnis'
            ],
            [
                'email' => 'customer.jaksel3@sayabantu.com', 'name' => 'Natasha Wilona', 'phone' => '081234567875',
                'city' => 'Jakarta Selatan', 'dist' => 'Setiabudi', 'nik' => '3174075512950009', 'dob' => '1995-12-15', 'pob' => 'Jakarta', 'gender' => 'Perempuan',
                'addr' => 'Kuningan City Residence Lt. 22, Karet Kuningan, Setiabudi, Jaksel 12940', 'prov' => 'DKI Jakarta',
                'job' => 'Content Creator'
            ],
            [
                'email' => 'customer.jakbar1@sayabantu.com', 'name' => 'Jessica Tanuwijaya', 'phone' => '081234567876',
                'city' => 'Jakarta Barat', 'dist' => 'Kebon Jeruk', 'nik' => '3173036005940001', 'dob' => '1994-05-20', 'pob' => 'Jakarta', 'gender' => 'Perempuan',
                'addr' => 'Perumahan Greenville Blok B No. 10, Duri Kepa, Kebon Jeruk, Jakarta Barat 11510', 'prov' => 'DKI Jakarta',
                'job' => 'Business Owner'
            ],
            [
                'email' => 'customer.jaktim1@sayabantu.com', 'name' => 'Bramantyo Wicaksono', 'phone' => '081234567877',
                'city' => 'Jakarta Timur', 'dist' => 'Duren Sawit', 'nik' => '3175031408910008', 'dob' => '1991-08-14', 'pob' => 'Jakarta', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Kolonel Sugiono No. 28, Duren Sawit, Jakarta Timur 13440', 'prov' => 'DKI Jakarta',
                'job' => 'PNS Kementerian BUMN'
            ],

            // --- Jawa Barat: Kota Bandung ---
            [
                'email' => 'customer.bandung1@sayabantu.com', 'name' => 'Fitri Handayani', 'phone' => '081234567860',
                'city' => 'Bandung', 'dist' => 'Coblong', 'nik' => '3273046205950002', 'dob' => '1995-05-22', 'pob' => 'Bandung', 'gender' => 'Perempuan',
                'addr' => 'Jl. Cisitu Lama No. 15, Dago, Coblong, Kota Bandung 40135', 'prov' => 'Jawa Barat',
                'job' => 'Peneliti & Dosen Muda ITB'
            ],
            [
                'email' => 'customer.bandung2@sayabantu.com', 'name' => 'Dimas Anggara', 'phone' => '081234567878',
                'city' => 'Bandung', 'dist' => 'Sukajadi', 'nik' => '3273051402920005', 'dob' => '1992-02-14', 'pob' => 'Bandung', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Karangsari No. 8, Pasteur, Sukajadi, Kota Bandung 40161', 'prov' => 'Jawa Barat',
                'job' => 'Event Organizer Director'
            ],

            // --- Jawa Timur: Kota Surabaya & Malang ---
            [
                'email' => 'customer.surabaya1@sayabantu.com', 'name' => 'Maya Anggraini', 'phone' => '081234567861',
                'city' => 'Surabaya', 'dist' => 'Gubeng', 'nik' => '3578044809930004', 'dob' => '1993-09-08', 'pob' => 'Surabaya', 'gender' => 'Perempuan',
                'addr' => 'Jl. Raya Manyar No. 20, Baratajaya, Gubeng, Kota Surabaya 60284', 'prov' => 'Jawa Timur',
                'job' => 'Branch Manager Perbankan'
            ],
            [
                'email' => 'customer.surabaya2@sayabantu.com', 'name' => 'Ir. Hendra Gunawan', 'phone' => '081234567879',
                'city' => 'Surabaya', 'dist' => 'Wonokromo', 'nik' => '3578051906880003', 'dob' => '1988-06-19', 'pob' => 'Surabaya', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Raya Darmo Permai No. 15, Sawunggaling, Wonokromo, Surabaya 60242', 'prov' => 'Jawa Timur',
                'job' => 'Kontraktor Sipil'
            ],
            [
                'email' => 'customer.malang1@sayabantu.com', 'name' => 'Clarissa Putri', 'phone' => '081234567880',
                'city' => 'Malang', 'dist' => 'Lowokwaru', 'nik' => '3573035501970002', 'dob' => '1997-01-15', 'pob' => 'Malang', 'gender' => 'Perempuan',
                'addr' => 'Jl. Candi Mendut No. 14, Mojolangu, Lowokwaru, Kota Malang 65142', 'prov' => 'Jawa Timur',
                'job' => 'Mahasiswi Universitas Brawijaya'
            ],

            // --- Bali: Kota Denpasar ---
            [
                'email' => 'customer.denpasar1@sayabantu.com', 'name' => 'Putu Ayu Saraswati', 'phone' => '081234567862',
                'city' => 'Denpasar', 'dist' => 'Denpasar Selatan', 'nik' => '5171036011940003', 'dob' => '1994-11-20', 'pob' => 'Denpasar', 'gender' => 'Perempuan',
                'addr' => 'Villa Cempaka, Jl. Danau Tamblingan No. 12, Sanur, Denpasar Selatan 80228', 'prov' => 'Bali',
                'job' => 'Owner Butik & Villa'
            ],
            [
                'email' => 'customer.denpasar2@sayabantu.com', 'name' => 'Kadek Bagus Pratama', 'phone' => '081234567881',
                'city' => 'Denpasar', 'dist' => 'Denpasar Barat', 'nik' => '5171041208920008', 'dob' => '1992-08-12', 'pob' => 'Denpasar', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Gunung Agung No. 50, Pemecutan, Denpasar Barat 80119', 'prov' => 'Bali',
                'job' => 'General Manager Hotel'
            ],

            // --- Sumatera Utara: Kota Medan ---
            [
                'email' => 'customer.medan1@sayabantu.com', 'name' => 'Desi Simanjuntak', 'phone' => '081234567863',
                'city' => 'Medan', 'dist' => 'Medan Kota', 'nik' => '1271034408960002', 'dob' => '1996-08-04', 'pob' => 'Medan', 'gender' => 'Perempuan',
                'addr' => 'Jl. Asia No. 145, Sei Rengas II, Medan Kota 20214', 'prov' => 'Sumatera Utara',
                'job' => 'Akuntan Publik'
            ],
            [
                'email' => 'customer.medan2@sayabantu.com', 'name' => 'dr. Benny Siregar, Sp.A', 'phone' => '081234567882',
                'city' => 'Medan', 'dist' => 'Medan Baru', 'nik' => '1271041904870001', 'dob' => '1987-04-19', 'pob' => 'Medan', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Gajah Mada No. 30, Petisah Hulu, Medan Baru, Kota Medan 20153', 'prov' => 'Sumatera Utara',
                'job' => 'Dokter Spesialis Anak'
            ],

            // --- Sumatera Selatan: Kota Palembang ---
            [
                'email' => 'customer.palembang1@sayabantu.com', 'name' => 'Tiara Andini', 'phone' => '081234567883',
                'city' => 'Palembang', 'dist' => 'Ilir Barat I', 'nik' => '1671035509950007', 'dob' => '1995-09-15', 'pob' => 'Palembang', 'gender' => 'Perempuan',
                'addr' => 'Jl. POM IX No. 20, Lorok Pakjo, Ilir Barat I, Kota Palembang 30137', 'prov' => 'Sumatera Selatan',
                'job' => 'Banker'
            ],

            // --- Sulawesi Selatan: Kota Makassar ---
            [
                'email' => 'customer.makassar1@sayabantu.com', 'name' => 'Nurul Wahidah', 'phone' => '081234567864',
                'city' => 'Makassar', 'dist' => 'Panakkukang', 'nik' => '7371035010970001', 'dob' => '1997-10-10', 'pob' => 'Makassar', 'gender' => 'Perempuan',
                'addr' => 'Jl. Boulevard No. 28, Masale, Panakkukang, Makassar 90231', 'prov' => 'Sulawesi Selatan',
                'job' => 'Arsitek & Desainer'
            ],
            [
                'email' => 'customer.makassar2@sayabantu.com', 'name' => 'Fadli Rahman, S.Farm', 'phone' => '081234567884',
                'city' => 'Makassar', 'dist' => 'Tamalanrea', 'nik' => '7371041407940009', 'dob' => '1994-07-14', 'pob' => 'Makassar', 'gender' => 'Laki-laki',
                'addr' => 'Jl. Pintu Nol Unhas No. 8, Tamalanrea Indah, Makassar 90245', 'prov' => 'Sulawesi Selatan',
                'job' => 'Peneliti Farmasi'
            ],

            // --- Banten: Tangerang Selatan ---
            [
                'email' => 'customer.tangsel1@sayabantu.com', 'name' => 'Rangga Pratama', 'phone' => '081234567885',
                'city' => 'Tangerang Selatan', 'dist' => 'Serpong', 'nik' => '3674031908900004', 'dob' => '1990-08-19', 'pob' => 'Tangerang', 'gender' => 'Laki-laki',
                'addr' => 'Cluster Foresta BSD City Blok E No. 8, Lengkong Kulon, Serpong 15331', 'prov' => 'Banten',
                'job' => 'Head of Product Fintech'
            ],
        ];

        foreach ($customerList as $index => $c) {
            [$cMod, $dMod] = $resolveLocation($c['city'], $c['dist']);
            $regTime = \Carbon\Carbon::parse('2026-08-03 08:00:00')->addMinutes($index * 15);
            $verifiedTime = $regTime->copy()->addMinutes(30);

            User::updateOrCreate(
                ['email' => $c['email']],
                [
                    'name'              => $c['name'],
                    'password'          => $commonPassword,
                    'role'              => 'customer',
                    'nik'               => $c['nik'],
                    'gender'            => $c['gender'],
                    'place_of_birth'    => $c['pob'],
                    'date_of_birth'     => $c['dob'],
                    'city_id'           => $cMod?->id,
                    'district_id'       => $dMod?->id,
                    'ktp_path'          => 'ktp-photos/REDZcbxGQDcD1FHcmKkADMdiyqyFdrt2UrKQsSa6.jpg',
                    'ktp_photo'         => 'ktp-photos/REDZcbxGQDcD1FHcmKkADMdiyqyFdrt2UrKQsSa6.jpg',
                    'selfie_photo'      => 'selfie-photos/IPJHSjUEhosEwUIcVOE3AitChg2pQqdMWz8bI5td.png',
                    'verified'          => true,
                    'status'            => 'active',
                    'phone'             => $c['phone'],
                    'address'           => $c['addr'],
                    'rt'                => 3,
                    'rw'                => 1,
                    'kelurahan'         => 'Kelurahan Customer',
                    'kecamatan'         => $dMod?->name ?? $c['dist'],
                    'city'              => $cMod?->name ?? $c['city'],
                    'province'          => $c['prov'],
                    'religion'          => 'Islam',
                    'marital_status'    => 'Belum Kawin',
                    'occupation'        => $c['job'],
                    'is_greylisted'     => false,
                    'is_shadow_banned'  => false,
                    'warning_level'     => 0,
                    'email_verified_at' => $verifiedTime,
                    'created_at'        => $regTime,
                    'updated_at'        => $verifiedTime,
                ]
            );
        }

        $this->command->info('UserSeeder berhasil membuat Superadmin, 15 Admin Wilayah Kecamatan, 36 Mitra Ahli, & 30 Customer di seluruh kota strategis Indonesia.');
    }
}

