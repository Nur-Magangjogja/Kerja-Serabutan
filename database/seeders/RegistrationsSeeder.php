<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RegistrationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi data pendaftaran & verifikasi KTP identitas pengguna (Approved, Pending Verification, & Rejected)
     * Tersebar di berbagai kecamatan se-Indonesia untuk pengujian moderasi admin.
     */
    public function run(): void
    {
        // 1. Registrasi Approved untuk semua Customer & Mitra aktif yang sudah ada
        $users = User::whereIn('role', ['customer', 'mitra'])->get();

        foreach ($users as $index => $user) {
            $regCreatedAt = $user->created_at ?? Carbon::parse('2026-08-02 08:00:00')->addMinutes($index * 15);
            $regUpdatedAt = $user->email_verified_at ?? $regCreatedAt->copy()->addMinutes(45);

            Registration::updateOrCreate(
                ['email' => $user->email],
                [
                    'uuid'              => (string) Str::uuid(),
                    'nik'               => $user->nik,
                    'full_name'         => $user->name,
                    'phone'             => $user->phone,
                    'place_of_birth'    => $user->place_of_birth ?? 'Indonesia',
                    'date_of_birth'     => $user->date_of_birth ?? '1995-05-15',
                    'gender'            => $user->gender ?? 'Laki-laki',
                    'address'           => $user->address,
                    'rt'                => $user->rt ?? 1,
                    'rw'                => $user->rw ?? 1,
                    'kelurahan'         => $user->kelurahan ?? 'Kelurahan',
                    'kecamatan'         => $user->kecamatan ?? 'Kecamatan',
                    'district_id'       => $user->district_id,
                    'city'              => $user->city ?? 'Kota',
                    'city_id'           => $user->city_id,
                    'province'          => $user->province ?? 'Indonesia',
                    'ktp_photo_path'    => $user->ktp_path ?: 'ktp-photos/0Us0WrrpLJdWMrUkcRs1XS4FI3a9tBeZkkpaSAKE.jpg',
                    'selfie_photo_path' => $user->selfie_photo ?: 'selfie-photos/9lp933vpWL9YN6JQ8ISEbocs2qLwvr78DklO0dEt.png',
                    'role'              => $user->role,
                    'status'            => 'approved',
                    'created_at'        => $regCreatedAt,
                    'updated_at'        => $regUpdatedAt,
                ]
            );
        }

        // Helper function for district lookup
        $getDistrict = function ($cityName, $distName) {
            $city = City::where('name', 'like', "%{$cityName}%")->first();
            $district = null;
            if ($city) {
                $district = District::where('city_id', $city->id)->where('name', 'like', "%{$distName}%")->first();
            }
            if (!$district) {
                $district = District::where('name', 'like', "%{$distName}%")->first();
            }
            return [$city, $district];
        };

        // 2. Data Registrasi Pending (Menunggu Verifikasi Admin Kecamatan)
        $pendingRegistrations = [
            [
                'email'     => 'calon.mitra.depok@gmail.com',
                'name'      => 'Bambang Triyono',
                'phone'     => '082133445566',
                'role'      => 'mitra',
                'city'      => 'Sleman',
                'dist'      => 'Depok',
                'nik'       => '3404071208930007',
                'gender'    => 'Laki-laki',
                'pob'       => 'Sleman',
                'dob'       => '1993-08-12',
                'address'   => 'Jl. Selokan Mataram No. 88, Caturtunggal, Depok, Sleman',
                'province'  => 'D.I. Yogyakarta',
            ],
            [
                'email'     => 'calon.mitra.mlati@gmail.com',
                'name'      => 'Sigit Purnomo',
                'phone'     => '082133445577',
                'role'      => 'mitra',
                'city'      => 'Sleman',
                'dist'      => 'Mlati',
                'nik'       => '3404081504950002',
                'gender'    => 'Laki-laki',
                'pob'       => 'Sleman',
                'dob'       => '1995-04-15',
                'address'   => 'Jl. Kebon Agung No. 12, Sendangadi, Mlati, Sleman',
                'province'  => 'D.I. Yogyakarta',
            ],
            [
                'email'     => 'calon.cust.gondomanan@gmail.com',
                'name'      => 'Nabila Syakieb',
                'phone'     => '082133445588',
                'role'      => 'customer',
                'city'      => 'Yogyakarta',
                'dist'      => 'Gondomanan',
                'nik'       => '3471015509960004',
                'gender'    => 'Perempuan',
                'pob'       => 'Yogyakarta',
                'dob'       => '1996-09-15',
                'address'   => 'Jl. Ibu Ruswo No. 20, Prawirodirjan, Gondomanan, Yogyakarta',
                'province'  => 'D.I. Yogyakarta',
            ],
            [
                'email'     => 'calon.mitra.tebet@gmail.com',
                'name'      => 'Rian Firmansyah',
                'phone'     => '082133445599',
                'role'      => 'mitra',
                'city'      => 'Jakarta Selatan',
                'dist'      => 'Tebet',
                'nik'       => '3174011802920008',
                'gender'    => 'Laki-laki',
                'pob'       => 'Jakarta',
                'dob'       => '1992-02-18',
                'address'   => 'Jl. Tebet Timur Dalam VII No. 3, Tebet, Jakarta Selatan',
                'province'  => 'DKI Jakarta',
            ],
            [
                'email'     => 'calon.mitra.coblong@gmail.com',
                'name'      => 'Gugun Gunawan',
                'phone'     => '082133445510',
                'role'      => 'mitra',
                'city'      => 'Bandung',
                'dist'      => 'Coblong',
                'nik'       => '3273012507910003',
                'gender'    => 'Laki-laki',
                'pob'       => 'Bandung',
                'dob'       => '1991-07-25',
                'address'   => 'Jl. Sangkuriang No. 10, Dago, Coblong, Kota Bandung',
                'province'  => 'Jawa Barat',
            ],
            [
                'email'     => 'calon.mitra.surabaya@gmail.com',
                'name'      => 'Hadi Sucipto',
                'phone'     => '082133445511',
                'role'      => 'mitra',
                'city'      => 'Surabaya',
                'dist'      => 'Wonokromo',
                'nik'       => '3578010506890006',
                'gender'    => 'Laki-laki',
                'pob'       => 'Surabaya',
                'dob'       => '1989-06-05',
                'address'   => 'Jl. Jagir Wonokromo No. 44, Wonokromo, Surabaya',
                'province'  => 'Jawa Timur',
            ],
            [
                'email'     => 'calon.mitra.semarang@gmail.com',
                'name'      => 'Dedi Mulyadi',
                'phone'     => '082133445514',
                'role'      => 'mitra',
                'city'      => 'Semarang',
                'dist'      => 'Banyumanik',
                'nik'       => '3374011409920005',
                'gender'    => 'Laki-laki',
                'pob'       => 'Semarang',
                'dob'       => '1992-09-14',
                'address'   => 'Jl. Sukun Raya No. 50, Srondol Wetan, Banyumanik, Semarang',
                'province'  => 'Jawa Tengah',
            ],
            [
                'email'     => 'calon.mitra.denpasar@gmail.com',
                'name'      => 'I Kadek Putra',
                'phone'     => '082133445515',
                'role'      => 'mitra',
                'city'      => 'Denpasar',
                'dist'      => 'Denpasar Selatan',
                'nik'       => '5171011803930002',
                'gender'    => 'Laki-laki',
                'pob'       => 'Denpasar',
                'dob'       => '1993-03-18',
                'address'   => 'Jl. Bypass Ngurah Rai No. 88, Sanur, Denpasar Selatan',
                'province'  => 'Bali',
            ],
            [
                'email'     => 'calon.mitra.medan@gmail.com',
                'name'      => 'Togu Nainggolan',
                'phone'     => '082133445516',
                'role'      => 'mitra',
                'city'      => 'Medan',
                'dist'      => 'Medan Baru',
                'nik'       => '1271012211900007',
                'gender'    => 'Laki-laki',
                'pob'       => 'Medan',
                'dob'       => '1990-11-22',
                'address'   => 'Jl. Padang Bulan No. 15, Babura, Medan Baru, Medan',
                'province'  => 'Sumatera Utara',
            ],
            [
                'email'     => 'calon.mitra.makassar@gmail.com',
                'name'      => 'Muh. Syahril',
                'phone'     => '082133445517',
                'role'      => 'mitra',
                'city'      => 'Makassar',
                'dist'      => 'Panakkukang',
                'nik'       => '7371011908940003',
                'gender'    => 'Laki-laki',
                'pob'       => 'Makassar',
                'dob'       => '1994-08-19',
                'address'   => 'Jl. Hertasning Baru No. 20, Pandang, Panakkukang, Makassar',
                'province'  => 'Sulawesi Selatan',
            ],
            [
                'email'     => 'calon.cust.tangsel@gmail.com',
                'name'      => 'Sabrina Anggraini',
                'phone'     => '082133445518',
                'role'      => 'customer',
                'city'      => 'Tangerang Selatan',
                'dist'      => 'Serpong',
                'nik'       => '3674015507960004',
                'gender'    => 'Perempuan',
                'pob'       => 'Tangerang',
                'dob'       => '1996-07-15',
                'address'   => 'Greenwich Park BSD Blok A No. 5, Serpong, Tangsel',
                'province'  => 'Banten',
            ],
        ];

        foreach ($pendingRegistrations as $idx => $p) {
            [$cityModel, $distModel] = $getDistrict($p['city'], $p['dist']);

            Registration::updateOrCreate(
                ['email' => $p['email']],
                [
                    'uuid'              => (string) Str::uuid(),
                    'nik'               => $p['nik'],
                    'full_name'         => $p['name'],
                    'phone'             => $p['phone'],
                    'place_of_birth'    => $p['pob'],
                    'date_of_birth'     => $p['dob'],
                    'gender'            => $p['gender'],
                    'address'           => $p['address'],
                    'rt'                => 2,
                    'rw'                => 3,
                    'kelurahan'         => 'Kelurahan Calon',
                    'kecamatan'         => $distModel?->name ?? $p['dist'],
                    'district_id'       => $distModel?->id,
                    'city'              => $cityModel?->name ?? $p['city'],
                    'city_id'           => $cityModel?->id,
                    'province'          => $p['province'],
                    'ktp_photo_path'    => 'ktp-photos/fGVuPrx8ZjTKgYkNFYzEaCraVufoRQePv6sG96pW.jpg',
                    'selfie_photo_path' => 'selfie-photos/aj0rpJR0A1FiXWtxSvPovBxWdQoMCNJbWV3CdXB8.png',
                    'role'              => $p['role'],
                    'status'            => 'pending_verification',
                    'created_at'        => now()->subHours(5 + $idx),
                    'updated_at'        => now()->subHours(5 + $idx),
                ]
            );
        }

        // 3. Data Registrasi Ditolak (Rejected) dengan Beragam Alasan Penolakan Otentik
        $rejectedRegistrations = [
            [
                'email'     => 'ditolak.sleman@gmail.com',
                'name'      => 'Rudi Hermawan',
                'phone'     => '082133445512',
                'role'      => 'mitra',
                'city'      => 'Sleman',
                'dist'      => 'Depok',
                'nik'       => '3404071010900009',
                'gender'    => 'Laki-laki',
                'pob'       => 'Sleman',
                'dob'       => '1990-10-10',
                'address'   => 'Jl. Moses Gatotkaca No. 11, Caturtunggal, Depok, Sleman',
                'province'  => 'D.I. Yogyakarta',
                'reason'    => 'Foto KTP terlalu buram, pantulan cahaya menutupi NIK dan tanggal lahir. Mohon unggah ulang dengan pencahayaan yang jelas.',
            ],
            [
                'email'     => 'ditolak.jaksel@gmail.com',
                'name'      => 'Hendra Wijaya',
                'phone'     => '082133445513',
                'role'      => 'mitra',
                'city'      => 'Jakarta Selatan',
                'dist'      => 'Tebet',
                'nik'       => '3174012012880004',
                'gender'    => 'Laki-laki',
                'pob'       => 'Jakarta',
                'dob'       => '1988-12-20',
                'address'   => 'Jl. Tebet Barat Raya No. 99, Tebet, Jakarta Selatan',
                'province'  => 'DKI Jakarta',
                'reason'    => 'Foto selfie tidak memegang KTP fisik dan wajah terpotong topi/kacamata hitam. Harap selfie tampak depan jelas.',
            ],
            [
                'email'     => 'ditolak.bandung@gmail.com',
                'name'      => 'Yayan Ruhian',
                'phone'     => '082133445519',
                'role'      => 'mitra',
                'city'      => 'Bandung',
                'dist'      => 'Coblong',
                'nik'       => '3273011406850007',
                'gender'    => 'Laki-laki',
                'pob'       => 'Bandung',
                'dob'       => '1985-06-14',
                'address'   => 'Jl. Cisitu Indah No. 3, Dago, Coblong, Bandung',
                'province'  => 'Jawa Barat',
                'reason'    => 'Masa berlaku KTP telah habis/dokumen rusak. Harap melampirkan e-KTP terbaru atau Surat Keterangan Pengganti dari Disdukcapil.',
            ],
            [
                'email'     => 'ditolak.surabaya@gmail.com',
                'name'      => 'Farid Miftah',
                'phone'     => '082133445520',
                'role'      => 'mitra',
                'city'      => 'Surabaya',
                'dist'      => 'Gubeng',
                'nik'       => '3578011904910003',
                'gender'    => 'Laki-laki',
                'pob'       => 'Surabaya',
                'dob'       => '1991-04-19',
                'address'   => 'Jl. Dharmawangsa No. 80, Airlangga, Gubeng, Surabaya',
                'province'  => 'Jawa Timur',
                'reason'    => 'Nama pada foto KTP tidak sesuai dengan nama akun pendaftar. Mohon perbaiki data registrasi sesuai e-KTP asli.',
            ],
        ];

        foreach ($rejectedRegistrations as $idx => $r) {
            [$cityModel, $distModel] = $getDistrict($r['city'], $r['dist']);

            Registration::updateOrCreate(
                ['email' => $r['email']],
                [
                    'uuid'              => (string) Str::uuid(),
                    'nik'               => $r['nik'],
                    'full_name'         => $r['name'],
                    'phone'             => $r['phone'],
                    'place_of_birth'    => $r['pob'],
                    'date_of_birth'     => $r['dob'],
                    'gender'            => $r['gender'],
                    'address'           => $r['address'],
                    'rt'                => 1,
                    'rw'                => 1,
                    'kelurahan'         => 'Kelurahan',
                    'kecamatan'         => $distModel?->name ?? $r['dist'],
                    'district_id'       => $distModel?->id,
                    'city'              => $cityModel?->name ?? $r['city'],
                    'city_id'           => $cityModel?->id,
                    'province'          => $r['province'],
                    'ktp_photo_path'    => 'ktp-photos/fGVuPrx8ZjTKgYkNFYzEaCraVufoRQePv6sG96pW.jpg',
                    'selfie_photo_path' => 'selfie-photos/aj0rpJR0A1FiXWtxSvPovBxWdQoMCNJbWV3CdXB8.png',
                    'role'              => $r['role'],
                    'status'            => 'rejected',
                    'rejection_reason'  => $r['reason'],
                    'created_at'        => now()->subDays(2 + $idx),
                    'updated_at'        => now()->subDays(1 + $idx),
                ]
            );
        }

        $this->command->info('RegistrationsSeeder berhasil membuat riwayat verifikasi KTP (Approved, Pending Verification, & Rejected) di berbagai kota/kabupaten se-Indonesia.');
    }
}
