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
     * Mengisi akun autentikasi utama dengan identitas Indonesia unik & tanpa duplikasi:
     * - 1 Super Admin (Tanpa nama, password: password)
     * - 2 Admin Utama (Admin Sleman & Yogyakarta, Admin Surakarta) + 1 Admin Pendamping
     * - Mitra & Customer di Wilayah Kabupaten Sleman (D.I. Yogyakarta)
     * - Mitra & Customer di Wilayah Kota Yogyakarta (D.I. Yogyakarta)
     * - Mitra & Customer di Wilayah Kota Surakarta (Jawa Tengah)
     * - Mitra & Customer di Wilayah Kabupaten Sukoharjo (Jawa Tengah)
     */
    public function run(): void
    {
        $slemanCity    = City::where('code', '3404')->orWhere('name', 'like', '%Sleman%')->first();
        $jogjaCity     = City::where('code', '3471')->orWhere('name', 'like', '%Yogyakarta%')->first();
        $surakartaCity = City::where('code', '3372')->orWhere('name', 'like', '%Surakarta%')->first();
        $sukoharjoCity = City::where('code', '3311')->orWhere('name', 'like', '%Sukoharjo%')->first();

        $slemanId    = $slemanCity ? $slemanCity->id : 226;
        $jogjaId     = $jogjaCity ? $jogjaCity->id : 227;
        $surakartaId = $surakartaCity ? $surakartaCity->id : 218;
        $sukoharjoId = $sukoharjoCity ? $sukoharjoCity->id : 198;

        $commonPassword = Hash::make('password');

        // =========================================================================
        // 1. SUPER ADMIN (Nama: SuperAdmin untuk profil header)
        // =========================================================================
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
                'city_id'           => $slemanId,
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
        // 2. ADMIN WILAYAH
        // =========================================================================
        // Admin 1: Mengurus 2 Wilayah (Kabupaten Sleman & Kota Yogyakarta)
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
                'city_id'           => $slemanId,
                'ktp_path'          => 'ktp-photos/fGVuPrx8ZjTKgYkNFYzEaCraVufoRQePv6sG96pW.jpg',
                'ktp_photo'         => 'ktp-photos/fGVuPrx8ZjTKgYkNFYzEaCraVufoRQePv6sG96pW.jpg',
                'selfie_photo'      => 'selfie-photos/aj0rpJR0A1FiXWtxSvPovBxWdQoMCNJbWV3CdXB8.png',
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
                'occupation'        => 'Admin Wilayah Sleman & Yogyakarta',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Admin Pendamping (Admin Sleman / Yogyakarta)
        User::updateOrCreate(
            ['email' => 'admin@sayabantu.com'],
            [
                'name'              => 'Siti Nurhaliza',
                'password'          => $commonPassword,
                'role'              => 'admin',
                'nik'               => '3471015509920003',
                'gender'            => 'Perempuan',
                'place_of_birth'    => 'Yogyakarta',
                'date_of_birth'     => '1992-09-15',
                'city_id'           => $jogjaId,
                'ktp_path'          => 'ktp-photos/qay3N5t3H7v5eWIAHyfyj3FyLY2e2FHh9cyKFU24.jpg',
                'ktp_photo'         => 'ktp-photos/qay3N5t3H7v5eWIAHyfyj3FyLY2e2FHh9cyKFU24.jpg',
                'selfie_photo'      => 'selfie-photos/HEoXRxmmYVLa5KieSPQDqyEZSdA9aLfXTuhOUCm5.png',
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567803',
                'address'           => 'Jl. Kenari No. 56, Muja Muju, Kec. Umbulharjo, Kota Yogyakarta, D.I. Yogyakarta 55165',
                'rt'                => 3,
                'rw'                => 2,
                'kelurahan'         => 'Muja Muju',
                'kecamatan'         => 'Umbulharjo',
                'city'              => 'Kota Yogyakarta',
                'province'          => 'D.I. Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Belum Kawin',
                'occupation'        => 'Admin Operasional Layanan',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Admin 2: Mengurus 1 Wilayah (Kota Surakarta)
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
                'city_id'           => $surakartaId,
                'ktp_path'          => 'ktp-photos/grVlYNH7Kvym4me9PnL8HyIjGtP08HyyBo2EPflB.jpg',
                'ktp_photo'         => 'ktp-photos/grVlYNH7Kvym4me9PnL8HyIjGtP08HyyBo2EPflB.jpg',
                'selfie_photo'      => 'selfie-photos/bjBnGW64AzafQarzSopIxVtlAPSqOgg5Kf6T4BRE.png',
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
        // 3. WILAYAH KABUPATEN SLEMAN (D.I. YOGYAKARTA)
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
                'city_id'           => $slemanId,
                'ktp_path'          => 'ktp-photos/0Us0WrrpLJdWMrUkcRs1XS4FI3a9tBeZkkpaSAKE.jpg',
                'ktp_photo'         => 'ktp-photos/0Us0WrrpLJdWMrUkcRs1XS4FI3a9tBeZkkpaSAKE.jpg',
                'selfie_photo'      => 'selfie-photos/9lp933vpWL9YN6JQ8ISEbocs2qLwvr78DklO0dEt.png',
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
                'occupation'        => 'Penyedia Jasa Angkut & Pertukangan',
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
                'city_id'           => $slemanId,
                'ktp_path'          => 'ktp-photos/fGVuPrx8ZjTKgYkNFYzEaCraVufoRQePv6sG96pW.jpg',
                'ktp_photo'         => 'ktp-photos/fGVuPrx8ZjTKgYkNFYzEaCraVufoRQePv6sG96pW.jpg',
                'selfie_photo'      => 'selfie-photos/ChMs7KK0dqiYoDWrrFhGyQb0NG2KB7h4PwP8CpdU.png',
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
                'occupation'        => 'Teknisi Pompa Air & Kelistrikan',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Mitra Default / Sleman 3 (Nama Unik: Fajar Nugroho)
        User::updateOrCreate(
            ['email' => 'mitra@sayabantu.com'],
            [
                'name'              => 'Fajar Nugroho',
                'password'          => $commonPassword,
                'role'              => 'mitra',
                'nik'               => '3404071506930006',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1993-06-15',
                'city_id'           => $slemanId,
                'ktp_path'          => 'ktp-photos/OVtzirBwZPsiogm55VlzVd3PC4gZVs18v3AULRxp.jpg',
                'ktp_photo'         => 'ktp-photos/OVtzirBwZPsiogm55VlzVd3PC4gZVs18v3AULRxp.jpg',
                'selfie_photo'      => 'selfie-photos/HJnGBIQz4wxp2HCOrglxoVM9jisvt6hcL42OzRGk.png',
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567810',
                'address'           => 'Jl. Godean KM 4 No. 88, Banyuraden, Kec. Gamping, Kabupaten Sleman, D.I. Yogyakarta 55293',
                'rt'                => 4,
                'rw'                => 2,
                'kelurahan'         => 'Banyuraden',
                'kecamatan'         => 'Gamping',
                'city'              => 'Kabupaten Sleman',
                'province'          => 'D.I. Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Belum Kawin',
                'occupation'        => 'Tukang Kebun & Perawatan Rumah',
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
                'city_id'           => $slemanId,
                'ktp_path'          => 'ktp-photos/qay3N5t3H7v5eWIAHyfyj3FyLY2e2FHh9cyKFU24.jpg',
                'ktp_photo'         => 'ktp-photos/qay3N5t3H7v5eWIAHyfyj3FyLY2e2FHh9cyKFU24.jpg',
                'selfie_photo'      => 'selfie-photos/HEoXRxmmYVLa5KieSPQDqyEZSdA9aLfXTuhOUCm5.png',
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
                'city_id'           => $slemanId,
                'ktp_path'          => 'ktp-photos/REDZcbxGQDcD1FHcmKkADMdiyqyFdrt2UrKQsSa6.jpg',
                'ktp_photo'         => 'ktp-photos/REDZcbxGQDcD1FHcmKkADMdiyqyFdrt2UrKQsSa6.jpg',
                'selfie_photo'      => 'selfie-photos/HJnGBIQz4wxp2HCOrglxoVM9jisvt6hcL42OzRGk.png',
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
                'occupation'        => 'Ibu Rumah Tangga & Online Shop',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Customer Default / Sleman 3 (Nama Unik: Maya Anggraini)
        User::updateOrCreate(
            ['email' => 'customer@sayabantu.com'],
            [
                'name'              => 'Maya Anggraini',
                'password'          => $commonPassword,
                'role'              => 'customer',
                'nik'               => '3404085004970008',
                'gender'            => 'Perempuan',
                'place_of_birth'    => 'Sleman',
                'date_of_birth'     => '1997-04-10',
                'city_id'           => $slemanId,
                'ktp_path'          => 'ktp-photos/qay3N5t3H7v5eWIAHyfyj3FyLY2e2FHh9cyKFU24.jpg',
                'ktp_photo'         => 'ktp-photos/qay3N5t3H7v5eWIAHyfyj3FyLY2e2FHh9cyKFU24.jpg',
                'selfie_photo'      => 'selfie-photos/NDY3IR8kVUiu9gi7KOoZiqno2iruVdffhQFzk3Ur.png',
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567820',
                'address'           => 'Jl. Palagan Tentara Pelajar KM 7 No. 20, Sariharjo, Kec. Ngaglik, Sleman 55581',
                'rt'                => 3,
                'rw'                => 1,
                'kelurahan'         => 'Sariharjo',
                'kecamatan'         => 'Ngaglik',
                'city'              => 'Kabupaten Sleman',
                'province'          => 'D.I. Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Belum Kawin',
                'occupation'        => 'Akuntan Perusahaan Swasta',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // =========================================================================
        // 4. WILAYAH KOTA YOGYAKARTA (D.I. YOGYAKARTA)
        // =========================================================================
        // Mitra 1 Yogyakarta
        User::updateOrCreate(
            ['email' => 'mitra.jogja1@sayabantu.com'],
            [
                'name'              => 'Danang Saputra',
                'password'          => $commonPassword,
                'role'              => 'mitra',
                'nik'               => '3471011204910001',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Yogyakarta',
                'date_of_birth'     => '1991-04-12',
                'city_id'           => $jogjaId,
                'ktp_path'          => 'ktp-photos/0Us0WrrpLJdWMrUkcRs1XS4FI3a9tBeZkkpaSAKE.jpg',
                'ktp_photo'         => 'ktp-photos/0Us0WrrpLJdWMrUkcRs1XS4FI3a9tBeZkkpaSAKE.jpg',
                'selfie_photo'      => 'selfie-photos/IPJHSjUEhosEwUIcVOE3AitChg2pQqdMWz8bI5td.png',
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567813',
                'address'           => 'Jl. Malioboro No. 65, Sosromenduran, Kec. Gedong Tengen, Kota Yogyakarta 55271',
                'rt'                => 2,
                'rw'                => 3,
                'kelurahan'         => 'Sosromenduran',
                'kecamatan'         => 'Gedong Tengen',
                'city'              => 'Kota Yogyakarta',
                'province'          => 'D.I. Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Kawin',
                'occupation'        => 'Jasa Instalasi & Servis Elektronik',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Customer 1 Yogyakarta
        User::updateOrCreate(
            ['email' => 'customer.jogja1@sayabantu.com'],
            [
                'name'              => 'Nia Safitri',
                'password'          => $commonPassword,
                'role'              => 'customer',
                'nik'               => '3471024508960001',
                'gender'            => 'Perempuan',
                'place_of_birth'    => 'Yogyakarta',
                'date_of_birth'     => '1996-08-25',
                'city_id'           => $jogjaId,
                'ktp_path'          => 'ktp-photos/REDZcbxGQDcD1FHcmKkADMdiyqyFdrt2UrKQsSa6.jpg',
                'ktp_photo'         => 'ktp-photos/REDZcbxGQDcD1FHcmKkADMdiyqyFdrt2UrKQsSa6.jpg',
                'selfie_photo'      => 'selfie-photos/KHnZzbW6gDcNNEhofwdRHurdkb0GTzY74aMK3Tsq.png',
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567823',
                'address'           => 'Jl. Mataram No. 42, Suryatmajan, Kec. Danurejan, Kota Yogyakarta 55213',
                'rt'                => 1,
                'rw'                => 4,
                'kelurahan'         => 'Suryatmajan',
                'kecamatan'         => 'Danurejan',
                'city'              => 'Kota Yogyakarta',
                'province'          => 'D.I. Yogyakarta',
                'religion'          => 'Islam',
                'marital_status'    => 'Belum Kawin',
                'occupation'        => 'Manajer Restoran & Kafe',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // =========================================================================
        // 5. WILAYAH KOTA SURAKARTA (JAWA TENGAH)
        // =========================================================================
        // Mitra 1 Surakarta
        User::updateOrCreate(
            ['email' => 'mitra.surakarta1@sayabantu.com'],
            [
                'name'              => 'Eko Saputra',
                'password'          => $commonPassword,
                'role'              => 'mitra',
                'nik'               => '3372021008910002',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Surakarta',
                'date_of_birth'     => '1991-08-10',
                'city_id'           => $surakartaId,
                'ktp_path'          => 'ktp-photos/OVtzirBwZPsiogm55VlzVd3PC4gZVs18v3AULRxp.jpg',
                'ktp_photo'         => 'ktp-photos/OVtzirBwZPsiogm55VlzVd3PC4gZVs18v3AULRxp.jpg',
                'selfie_photo'      => 'selfie-photos/IPJHSjUEhosEwUIcVOE3AitChg2pQqdMWz8bI5td.png',
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
            ['email' => 'mitra.surakarta2@sayabantu.com'],
            [
                'name'              => 'Hendra Wijaya',
                'password'          => $commonPassword,
                'role'              => 'mitra',
                'nik'               => '3372032512950003',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Surakarta',
                'date_of_birth'     => '1995-12-25',
                'city_id'           => $surakartaId,
                'ktp_path'          => 'ktp-photos/grVlYNH7Kvym4me9PnL8HyIjGtP08HyyBo2EPflB.jpg',
                'ktp_photo'         => 'ktp-photos/grVlYNH7Kvym4me9PnL8HyIjGtP08HyyBo2EPflB.jpg',
                'selfie_photo'      => 'selfie-photos/jRj5sTvECec9FUHhJm6v6AvxX7NBw5WfvNmHMMFm.png',
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
            ['email' => 'customer.surakarta1@sayabantu.com'],
            [
                'name'              => 'Dewi Anggraini',
                'password'          => $commonPassword,
                'role'              => 'customer',
                'nik'               => '3372044207970004',
                'gender'            => 'Perempuan',
                'place_of_birth'    => 'Surakarta',
                'date_of_birth'     => '1997-07-02',
                'city_id'           => $surakartaId,
                'ktp_path'          => 'ktp-photos/qay3N5t3H7v5eWIAHyfyj3FyLY2e2FHh9cyKFU24.jpg',
                'ktp_photo'         => 'ktp-photos/qay3N5t3H7v5eWIAHyfyj3FyLY2e2FHh9cyKFU24.jpg',
                'selfie_photo'      => 'selfie-photos/KHnZzbW6gDcNNEhofwdRHurdkb0GTzY74aMK3Tsq.png',
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
            ['email' => 'customer.surakarta2@sayabantu.com'],
            [
                'name'              => 'Anisa Putri',
                'password'          => $commonPassword,
                'role'              => 'customer',
                'nik'               => '3372056009990005',
                'gender'            => 'Perempuan',
                'place_of_birth'    => 'Surakarta',
                'date_of_birth'     => '1999-09-20',
                'city_id'           => $surakartaId,
                'ktp_path'          => 'ktp-photos/REDZcbxGQDcD1FHcmKkADMdiyqyFdrt2UrKQsSa6.jpg',
                'ktp_photo'         => 'ktp-photos/REDZcbxGQDcD1FHcmKkADMdiyqyFdrt2UrKQsSa6.jpg',
                'selfie_photo'      => 'selfie-photos/NDY3IR8kVUiu9gi7KOoZiqno2iruVdffhQFzk3Ur.png',
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

        // =========================================================================
        // 6. WILAYAH KABUPATEN SUKOHARJO (JAWA TENGAH)
        // =========================================================================
        // Mitra 1 Sukoharjo
        User::updateOrCreate(
            ['email' => 'mitra.sukoharjo1@sayabantu.com'],
            [
                'name'              => 'Tri Wahyudi',
                'password'          => $commonPassword,
                'role'              => 'mitra',
                'nik'               => '3311011406930001',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Sukoharjo',
                'date_of_birth'     => '1993-06-14',
                'city_id'           => $sukoharjoId,
                'ktp_path'          => 'ktp-photos/0Us0WrrpLJdWMrUkcRs1XS4FI3a9tBeZkkpaSAKE.jpg',
                'ktp_photo'         => 'ktp-photos/0Us0WrrpLJdWMrUkcRs1XS4FI3a9tBeZkkpaSAKE.jpg',
                'selfie_photo'      => 'selfie-photos/aj0rpJR0A1FiXWtxSvPovBxWdQoMCNJbWV3CdXB8.png',
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567851',
                'address'           => 'Jl. Raya Kartasura No. 120, Ngadirejo, Kec. Kartasura, Kabupaten Sukoharjo 57163',
                'rt'                => 3,
                'rw'                => 4,
                'kelurahan'         => 'Ngadirejo',
                'kecamatan'         => 'Kartasura',
                'city'              => 'Kabupaten Sukoharjo',
                'province'          => 'Jawa Tengah',
                'religion'          => 'Islam',
                'marital_status'    => 'Kawin',
                'occupation'        => 'Servis AC & Perbaikan Rumah',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Mitra 2 Sukoharjo
        User::updateOrCreate(
            ['email' => 'mitra.sukoharjo2@sayabantu.com'],
            [
                'name'              => 'Joko Susilo',
                'password'          => $commonPassword,
                'role'              => 'mitra',
                'nik'               => '3311022009920002',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Sukoharjo',
                'date_of_birth'     => '1992-09-20',
                'city_id'           => $sukoharjoId,
                'ktp_path'          => 'ktp-photos/fGVuPrx8ZjTKgYkNFYzEaCraVufoRQePv6sG96pW.jpg',
                'ktp_photo'         => 'ktp-photos/fGVuPrx8ZjTKgYkNFYzEaCraVufoRQePv6sG96pW.jpg',
                'selfie_photo'      => 'selfie-photos/bjBnGW64AzafQarzSopIxVtlAPSqOgg5Kf6T4BRE.png',
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567852',
                'address'           => 'Jl. Ir. Soekarno No. 45, Madegondo, Kec. Grogol, Kabupaten Sukoharjo 57552',
                'rt'                => 2,
                'rw'                => 1,
                'kelurahan'         => 'Madegondo',
                'kecamatan'         => 'Grogol',
                'city'              => 'Kabupaten Sukoharjo',
                'province'          => 'Jawa Tengah',
                'religion'          => 'Islam',
                'marital_status'    => 'Belum Kawin',
                'occupation'        => 'Penyedia Jasa Angkut & Pengiriman',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Customer 1 Sukoharjo
        User::updateOrCreate(
            ['email' => 'customer.sukoharjo1@sayabantu.com'],
            [
                'name'              => 'Indah Permatasari',
                'password'          => $commonPassword,
                'role'              => 'customer',
                'nik'               => '3311035508980001',
                'gender'            => 'Perempuan',
                'place_of_birth'    => 'Sukoharjo',
                'date_of_birth'     => '1998-08-15',
                'city_id'           => $sukoharjoId,
                'ktp_path'          => 'ktp-photos/qay3N5t3H7v5eWIAHyfyj3FyLY2e2FHh9cyKFU24.jpg',
                'ktp_photo'         => 'ktp-photos/qay3N5t3H7v5eWIAHyfyj3FyLY2e2FHh9cyKFU24.jpg',
                'selfie_photo'      => 'selfie-photos/HEoXRxmmYVLa5KieSPQDqyEZSdA9aLfXTuhOUCm5.png',
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567861',
                'address'           => 'Perum Baki Permai Blok C-12, Kudu, Kec. Baki, Kabupaten Sukoharjo 57556',
                'rt'                => 1,
                'rw'                => 5,
                'kelurahan'         => 'Kudu',
                'kecamatan'         => 'Baki',
                'city'              => 'Kabupaten Sukoharjo',
                'province'          => 'Jawa Tengah',
                'religion'          => 'Islam',
                'marital_status'    => 'Belum Kawin',
                'occupation'        => 'Dokter Umum & Praktisi Medis',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        // Customer 2 Sukoharjo
        User::updateOrCreate(
            ['email' => 'customer.sukoharjo2@sayabantu.com'],
            [
                'name'              => 'Bayu Setiawan',
                'password'          => $commonPassword,
                'role'              => 'customer',
                'nik'               => '3311041011950002',
                'gender'            => 'Laki-laki',
                'place_of_birth'    => 'Sukoharjo',
                'date_of_birth'     => '1995-11-10',
                'city_id'           => $sukoharjoId,
                'ktp_path'          => 'ktp-photos/REDZcbxGQDcD1FHcmKkADMdiyqyFdrt2UrKQsSa6.jpg',
                'ktp_photo'         => 'ktp-photos/REDZcbxGQDcD1FHcmKkADMdiyqyFdrt2UrKQsSa6.jpg',
                'selfie_photo'      => 'selfie-photos/IPJHSjUEhosEwUIcVOE3AitChg2pQqdMWz8bI5td.png',
                'verified'          => true,
                'status'            => 'active',
                'phone'             => '081234567862',
                'address'           => 'Jl. Jenderal Sudirman No. 80, Jombor, Kec. Sukoharjo, Kabupaten Sukoharjo 57512',
                'rt'                => 4,
                'rw'                => 2,
                'kelurahan'         => 'Jombor',
                'kecamatan'         => 'Sukoharjo',
                'city'              => 'Kabupaten Sukoharjo',
                'province'          => 'Jawa Tengah',
                'religion'          => 'Islam',
                'marital_status'    => 'Kawin',
                'occupation'        => 'Apoteker & Pengusaha Apotek',
                'is_greylisted'     => false,
                'is_shadow_banned'  => false,
                'warning_level'     => 0,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('UserSeeder berhasil membuat akun Superadmin, Admin Wilayah (Sleman, Yogyakarta, Surakarta), serta Mitra & Customer di 4 wilayah.');
    }
}
