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
     * Masing-masing role memiliki tepat 1 akun autentikasi utama.
     */
    public function run(): void
    {
        // Cari id Kabupaten Sleman di tabel cities (fallback ke id 1 jika belum ada)
        $slemanCity = City::where('name', 'like', '%Sleman%')->first();
        $slemanCityId = $slemanCity ? $slemanCity->id : 1;

        // =========================================================================
        // 1. SUPER ADMIN (1 Akun Utama Pengelola Sistem Global)
        // =========================================================================
        User::updateOrCreate(
            ['email' => 'superadmin@sayabantu.com'],
            [
                'name'              => 'Super Admin',
                'password'          => Hash::make('password'),
                'role'              => 'super_admin',
                'nik'               => '3404011205850001',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1985-05-12',
                'city_id'           => $slemanCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567890',
                'address'           => 'Jl. Kaliurang KM 9.5 No. 100, Sardonoharjo, Kec. Ngaglik, Kabupaten Sleman, D.I. Yogyakarta 55581',
                'rt'                => 1,
                'rw'                => 3,
                'kelurahan'         => 'Sardonoharjo',
                'kecamatan'         => 'Ngaglik',
                'city'              => 'Kabupaten Sleman',
                'province'          => 'DI Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Kawin',
                'occupation'        => 'Platform Administrator',
                'email_verified_at' => now(),
            ]
        );

        // =========================================================================
        // 2. ADMIN (1 Akun Admin Wilayah - Fokus Mengurusi Kabupaten Sleman)
        // =========================================================================
        User::updateOrCreate(
            ['email' => 'admin@sayabantu.com'],
            [
                'name'              => 'Dian Wahyuni',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'nik'               => '3404024508900002',
                'gender'            => 'Perempuan',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1990-08-15',
                'city_id'           => $slemanCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567891',
                'address'           => 'Jl. Parasamya No. 1, Tridadi, Kec. Sleman, Kabupaten Sleman, D.I. Yogyakarta 55511',
                'rt'                => 2,
                'rw'                => 4,
                'kelurahan'         => 'Tridadi',
                'kecamatan'         => 'Sleman',
                'city'              => 'Kabupaten Sleman',
                'province'          => 'DI Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Kawin',
                'occupation'        => 'Admin Wilayah Sleman',
                'email_verified_at' => now(),
            ]
        );

        // =========================================================================
        // 3. MITRA (1 Akun Mitra Penolong / Penyedia Jasa di Wilayah Sleman)
        // =========================================================================
        User::updateOrCreate(
            ['email' => 'mitra@sayabantu.com'],
            [
                'name'              => 'Agus Prasetyo',
                'password'          => Hash::make('password'),
                'role'              => 'mitra',
                'nik'               => '3404031708940003',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1994-08-17',
                'city_id'           => $slemanCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567892',
                'address'           => 'Jl. Affandi (Gejayan) No. 45, Santren, Caturtunggal, Kec. Depok, Kabupaten Sleman, D.I. Yogyakarta 55281',
                'rt'                => 3,
                'rw'                => 5,
                'kelurahan'         => 'Caturtunggal',
                'kecamatan'         => 'Depok',
                'city'              => 'Kabupaten Sleman',
                'province'          => 'DI Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Belum Kawin',
                'occupation'        => 'Penyedia Jasa & Teknisi Serabutan',
                'email_verified_at' => now(),
            ]
        );

        // =========================================================================
        // 4. CUSTOMER (1 Akun Customer Pembuat Bantuan di Wilayah Sleman)
        // =========================================================================
        User::updateOrCreate(
            ['email' => 'customer@sayabantu.com'],
            [
                'name'              => 'Rina Kusuma',
                'password'          => Hash::make('password'),
                'role'              => 'customer',
                'nik'               => '3404046103980004',
                'gender'            => 'Perempuan',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1998-03-21',
                'city_id'           => $slemanCityId,
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567893',
                'address'           => 'Jl. Magelang KM 5.5 No. 88, Kutu Tegal, Sinduadi, Kec. Mlati, Kabupaten Sleman, D.I. Yogyakarta 55284',
                'rt'                => 2,
                'rw'                => 1,
                'kelurahan'         => 'Sinduadi',
                'kecamatan'         => 'Mlati',
                'city'              => 'Kabupaten Sleman',
                'province'          => 'DI Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Belum Kawin',
                'occupation'        => 'Wiraswasta / Pemilik Usaha',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('UserSeeder berhasil membuat 4 akun autentikasi utama (Superadmin, Admin Sleman, Mitra Sleman, Customer Sleman).');
    }
}
