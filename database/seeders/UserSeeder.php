<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi akun autentikasi utama:
     * - 1 Super Admin (password: password)
     * - 2 Admin (Admin Sleman, Yogyakarta & Admin Surakarta, Jawa Tengah)
     * - 2 Mitra & 2 Customer di Wilayah Sleman
     * - 2 Mitra & 2 Customer di Wilayah Surakarta
     */
    public function run(): void
    {
        $sleman = City::where('name', 'like', '%Sleman%')->first();
        $slemanCityId = $sleman ? $sleman->id : 1;

        $surakarta = City::where('name', 'like', '%Surakarta%')->first();
        $surakartaCityId = $surakarta ? $surakarta->id : 2;

        $commonPassword = Hash::make('password');

        // =========================================================================
        // 1. SUPER ADMIN
        // =========================================================================
        User::updateOrCreate(
            ['email' => 'superadmin@sayabantu.com'],
            [
                'name'              => 'Super Admin',
                'password'          => $commonPassword,
                'role'              => 'super_admin',
                'nik'               => '3404011205850001',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1985-05-12',
                'city_id'           => $slemanCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567800',
                'address'           => 'Jl. Kaliurang KM 9.5 No. 100, Sardonoharjo, Kec. Ngaglik, Kabupaten Sleman, D.I. Yogyakarta 55581',
                'rt'                => 1,
                'rw'                => 3,
                'kelurahan'         => 'Sardonoharjo',
                'kecamatan'         => 'Ngaglik',
                'city'              => 'Kabupaten Sleman',
                'province'          => 'D.I. Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Kawin',
                'occupation'        => 'Platform Administrator',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // =========================================================================
        // 2. ADMIN WILAYAH (Sleman & Surakarta)
        // =========================================================================
        // Admin 1: Wilayah Sleman, Yogyakarta
        User::updateOrCreate(
            ['email' => 'admin.sleman@sayabantu.com'],
            [
                'name'              => 'Dian Wahyuni',
                'password'          => $commonPassword,
                'role'              => 'admin',
                'nik'               => '3404024508900002',
                'gender'            => 'Perempuan',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1990-08-15',
                'city_id'           => $slemanCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567801',
                'address'           => 'Jl. Parasamya No. 1, Tridadi, Kec. Sleman, Kabupaten Sleman, D.I. Yogyakarta 55511',
                'rt'                => 2,
                'rw'                => 4,
                'kelurahan'         => 'Tridadi',
                'kecamatan'         => 'Sleman',
                'city'              => 'Kabupaten Sleman',
                'province'          => 'D.I. Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Kawin',
                'occupation'        => 'Admin Wilayah Sleman',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Alias akun admin@sayabantu.com menuju admin sleman
        User::updateOrCreate(
            ['email' => 'admin@sayabantu.com'],
            [
                'name'              => 'Dian Wahyuni',
                'password'          => $commonPassword,
                'role'              => 'admin',
                'nik'               => '3404024508900002',
                'gender'            => 'Perempuan',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1990-08-15',
                'city_id'           => $slemanCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567801',
                'address'           => 'Jl. Parasamya No. 1, Tridadi, Kec. Sleman, Kabupaten Sleman, D.I. Yogyakarta 55511',
                'rt'                => 2,
                'rw'                => 4,
                'kelurahan'         => 'Tridadi',
                'kecamatan'         => 'Sleman',
                'city'              => 'Kabupaten Sleman',
                'province'          => 'D.I. Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Kawin',
                'occupation'        => 'Admin Wilayah Sleman',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Admin 2: Wilayah Surakarta, Jawa Tengah
        User::updateOrCreate(
            ['email' => 'admin.surakarta@sayabantu.com'],
            [
                'name'              => 'Bambang Haryanto',
                'password'          => $commonPassword,
                'role'              => 'admin',
                'nik'               => '3372011504880001',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Surakarta',
                'date_of_birth'     => '1988-04-15',
                'city_id'           => $surakartaCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567802',
                'address'           => 'Jl. Jend. Sudirman No. 2, Kedung Lumbu, Kec. Pasar Kliwon, Kota Surakarta, Jawa Tengah 57113',
                'rt'                => 1,
                'rw'                => 2,
                'kelurahan'         => 'Kedung Lumbu',
                'kecamatan'         => 'Pasar Kliwon',
                'city'              => 'Kota Surakarta',
                'province'          => 'Jawa Tengah',
                'religion'          => 'Islam',
                'marital_status'    => 'Kawin',
                'occupation'        => 'Admin Wilayah Surakarta',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // =========================================================================
        // 3. WILAYAH KABUPATEN SLEMAN (2 Mitra & 2 Customer)
        // =========================================================================
        // Mitra 1 Sleman
        User::updateOrCreate(
            ['email' => 'mitra.sleman1@sayabantu.com'],
            [
                'name'              => 'Agus Prasetyo',
                'password'          => $commonPassword,
                'role'              => 'mitra',
                'nik'               => '3404031708940003',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1994-08-17',
                'city_id'           => $slemanCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567811',
                'address'           => 'Jl. Affandi (Gejayan) No. 45, Santren, Caturtunggal, Kec. Depok, Kabupaten Sleman, D.I. Yogyakarta 55281',
                'rt'                => 3,
                'rw'                => 5,
                'kelurahan'         => 'Caturtunggal',
                'kecamatan'         => 'Depok',
                'city'              => 'Kabupaten Sleman',
                'province'          => 'D.I. Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Belum Kawin',
                'occupation'        => 'Penyedia Jasa & Teknisi Serabutan',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Alias mitra@sayabantu.com
        User::updateOrCreate(
            ['email' => 'mitra@sayabantu.com'],
            [
                'name'              => 'Agus Prasetyo',
                'password'          => $commonPassword,
                'role'              => 'mitra',
                'nik'               => '3404031708940003',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1994-08-17',
                'city_id'           => $slemanCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567811',
                'address'           => 'Jl. Affandi (Gejayan) No. 45, Santren, Caturtunggal, Kec. Depok, Kabupaten Sleman, D.I. Yogyakarta 55281',
                'rt'                => 3,
                'rw'                => 5,
                'kelurahan'         => 'Caturtunggal',
                'kecamatan'         => 'Depok',
                'city'              => 'Kabupaten Sleman',
                'province'          => 'D.I. Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Belum Kawin',
                'occupation'        => 'Penyedia Jasa & Teknisi Serabutan',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Mitra 2 Sleman
        User::updateOrCreate(
            ['email' => 'mitra.sleman2@sayabantu.com'],
            [
                'name'              => 'Budi Santoso',
                'password'          => $commonPassword,
                'role'              => 'mitra',
                'nik'               => '3404052002920005',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1992-02-20',
                'city_id'           => $slemanCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567812',
                'address'           => 'Jl. Magelang KM 6.5, Sinduadi, Kec. Mlati, Kabupaten Sleman, D.I. Yogyakarta 55284',
                'rt'                => 2,
                'rw'                => 1,
                'kelurahan'         => 'Sinduadi',
                'kecamatan'         => 'Mlati',
                'city'              => 'Kabupaten Sleman',
                'province'          => 'D.I. Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Kawin',
                'occupation'        => 'Tukang Listrik & Kebersihan Rumah',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Customer 1 Sleman
        User::updateOrCreate(
            ['email' => 'customer.sleman1@sayabantu.com'],
            [
                'name'              => 'Rina Kusuma',
                'password'          => $commonPassword,
                'role'              => 'customer',
                'nik'               => '3404046103980004',
                'gender'            => 'Perempuan',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1998-03-21',
                'city_id'           => $slemanCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567821',
                'address'           => 'Jl. Kaliurang KM 8 No. 12, Sinduharjo, Kec. Ngaglik, Kabupaten Sleman, D.I. Yogyakarta 55581',
                'rt'                => 2,
                'rw'                => 1,
                'kelurahan'         => 'Sinduharjo',
                'kecamatan'         => 'Ngaglik',
                'city'              => 'Kabupaten Sleman',
                'province'          => 'D.I. Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Belum Kawin',
                'occupation'        => 'Wiraswasta / Pemilik Usaha',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Alias customer@sayabantu.com
        User::updateOrCreate(
            ['email' => 'customer@sayabantu.com'],
            [
                'name'              => 'Rina Kusuma',
                'password'          => $commonPassword,
                'role'              => 'customer',
                'nik'               => '3404046103980004',
                'gender'            => 'Perempuan',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1998-03-21',
                'city_id'           => $slemanCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567821',
                'address'           => 'Jl. Kaliurang KM 8 No. 12, Sinduharjo, Kec. Ngaglik, Kabupaten Sleman, D.I. Yogyakarta 55581',
                'rt'                => 2,
                'rw'                => 1,
                'kelurahan'         => 'Sinduharjo',
                'kecamatan'         => 'Ngaglik',
                'city'              => 'Kabupaten Sleman',
                'province'          => 'D.I. Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Belum Kawin',
                'occupation'        => 'Wiraswasta / Pemilik Usaha',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Customer 2 Sleman
        User::updateOrCreate(
            ['email' => 'customer.sleman2@sayabantu.com'],
            [
                'name'              => 'Siti Rahayu',
                'password'          => $commonPassword,
                'role'              => 'customer',
                'nik'               => '3404065511960006',
                'gender'            => 'Perempuan',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1996-11-15',
                'city_id'           => $slemanCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567822',
                'address'           => 'Jl. Ringroad Barat No. 108, Banyuraden, Kec. Gamping, Kabupaten Sleman, D.I. Yogyakarta 55293',
                'rt'                => 1,
                'rw'                => 2,
                'kelurahan'         => 'Banyuraden',
                'kecamatan'         => 'Gamping',
                'city'              => 'Kabupaten Sleman',
                'province'          => 'D.I. Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Kawin',
                'occupation'        => 'Ibu Rumah Tangga & Pebisnis Online',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // =========================================================================
        // 4. WILAYAH KOTA SURAKARTA / SOLO (2 Mitra & 2 Customer)
        // =========================================================================
        // Mitra 1 Surakarta
        User::updateOrCreate(
            ['email' => 'mitra.solo1@sayabantu.com'],
            [
                'name'              => 'Eko Saputra',
                'password'          => $commonPassword,
                'role'              => 'mitra',
                'nik'               => '3372021008910002',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Surakarta',
                'date_of_birth'     => '1991-08-10',
                'city_id'           => $surakartaCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567831',
                'address'           => 'Jl. Slamet Riyadi No. 182, Timuran, Kec. Banjarsari, Kota Surakarta, Jawa Tengah 57131',
                'rt'                => 3,
                'rw'                => 2,
                'kelurahan'         => 'Timuran',
                'kecamatan'         => 'Banjarsari',
                'city'              => 'Kota Surakarta',
                'province'          => 'Jawa Tengah',
                'religion'          => 'Islam',
                'marital_status'    => 'Kawin',
                'occupation'        => 'Montir Mesin & Tukang Bangunan',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Mitra 2 Surakarta
        User::updateOrCreate(
            ['email' => 'mitra.solo2@sayabantu.com'],
            [
                'name'              => 'Hendra Wijaya',
                'password'          => $commonPassword,
                'role'              => 'mitra',
                'nik'               => '3372032512950003',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Surakarta',
                'date_of_birth'     => '1995-12-25',
                'city_id'           => $surakartaCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567832',
                'address'           => 'Jl. Ir. Sutami No. 36, Jebres, Kec. Jebres, Kota Surakarta, Jawa Tengah 57126',
                'rt'                => 4,
                'rw'                => 6,
                'kelurahan'         => 'Jebres',
                'kecamatan'         => 'Jebres',
                'city'              => 'Kota Surakarta',
                'province'          => 'Jawa Tengah',
                'religion'          => 'Kristen',
                'marital_status'    => 'Belum Kawin',
                'occupation'        => 'Kurir & Angkut Barang',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Customer 1 Surakarta
        User::updateOrCreate(
            ['email' => 'customer.solo1@sayabantu.com'],
            [
                'name'              => 'Dewi Anggraini',
                'password'          => $commonPassword,
                'role'              => 'customer',
                'nik'               => '3372044207970004',
                'gender'            => 'Perempuan',
                'place_of_birth'    => 'Surakarta',
                'date_of_birth'     => '1997-07-02',
                'city_id'           => $surakartaCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567841',
                'address'           => 'Jl. Dr. Radjiman No. 512, Sondakan, Kec. Laweyan, Kota Surakarta, Jawa Tengah 57147',
                'rt'                => 2,
                'rw'                => 3,
                'kelurahan'         => 'Sondakan',
                'kecamatan'         => 'Laweyan',
                'city'              => 'Kota Surakarta',
                'province'          => 'Jawa Tengah',
                'religion'          => 'Islam',
                'marital_status'    => 'Belum Kawin',
                'occupation'        => 'Desainer Grafis & Pegawai Swasta',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Customer 2 Surakarta
        User::updateOrCreate(
            ['email' => 'customer.solo2@sayabantu.com'],
            [
                'name'              => 'Anisa Putri',
                'password'          => $commonPassword,
                'role'              => 'customer',
                'nik'               => '3372056009990005',
                'gender'            => 'Perempuan',
                'place_of_birth'    => 'Surakarta',
                'date_of_birth'     => '1999-09-20',
                'city_id'           => $surakartaCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567842',
                'address'           => 'Jl. Veteran No. 89, Joyosuran, Kec. Pasar Kliwon, Kota Surakarta, Jawa Tengah 57116',
                'rt'                => 1,
                'rw'                => 1,
                'kelurahan'         => 'Joyosuran',
                'kecamatan'         => 'Pasar Kliwon',
                'city'              => 'Kota Surakarta',
                'province'          => 'Jawa Tengah',
                'religion'          => 'Islam',
                'marital_status'    => 'Kawin',
                'occupation'        => 'Karyawan Bank',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('UserSeeder berhasil membuat Superadmin, 2 Admin Wilayah (Sleman & Solo), serta 2 Mitra dan 2 Customer di masing-masing wilayah.');
    }
}
