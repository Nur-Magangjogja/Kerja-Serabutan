<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ==========================================
        // 1. SUPER ADMIN (Pemilik / Super Admin)
        // ==========================================
        User::firstOrCreate(
            ['email' => 'superadmin@sayabantu.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'verified' => true,
                'status' => 'active',
                'phone' => '081234567890',
            ]
        );

        // ==========================================
        // 2. ADMIN (Admin CS / Pemantau Kota)
        // ==========================================
        User::firstOrCreate(
            ['email' => 'admin.jakarta@sayabantu.com'],
            [
                'name' => 'Admin Jakarta',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'city_id' => 1,
                'verified' => true,
                'status' => 'active',
                'phone' => '081234567891',
            ]
        );

        // ==========================================
        // 3. CUSTOMER (3 Pengguna / Pembuat Bantuan)
        // ==========================================
        
        // Customer 1: Budi Santoso
        User::firstOrCreate(
            ['email' => 'budi@example.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'city_id' => 1,
                'verified' => true,
                'status' => 'active',
                'phone' => '081234567892',
                'address' => 'Jl. Merdeka No. 123, Jakarta Pusat',
            ]
        );

        // Customer 2: Siti Rahayu
        User::firstOrCreate(
            ['email' => 'siti@example.com'],
            [
                'name' => 'Siti Rahayu',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'city_id' => 1,
                'verified' => true,
                'status' => 'active',
                'phone' => '081234567894',
                'address' => 'Jl. Jenderal Sudirman No. 45, Jakarta Selatan',
            ]
        );

        // Customer 3: Dewi Lestari
        User::firstOrCreate(
            ['email' => 'dewi@example.com'],
            [
                'name' => 'Dewi Lestari',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'city_id' => 1,
                'verified' => true,
                'status' => 'active',
                'phone' => '081234567895',
                'address' => 'Jl. Diponegoro No. 88, Ponorogo',
            ]
        );

        // ==========================================
        // 4. MITRA (3 Penolong / Penyedia Jasa)
        // ==========================================
        
        // Mitra 1: Ahmad Relawan
        User::firstOrCreate(
            ['email' => 'ahmad@example.com'],
            [
                'name' => 'Ahmad Relawan',
                'password' => Hash::make('password'),
                'role' => 'mitra',
                'city_id' => 1,
                'verified' => true,
                'status' => 'active',
                'phone' => '081234567893',
                'address' => 'Jl. Kemanusiaan No. 456, Jakarta',
            ]
        );

        // Mitra 2: Joko Santoso
        User::firstOrCreate(
            ['email' => 'joko@example.com'],
            [
                'name' => 'Joko Santoso',
                'password' => Hash::make('password'),
                'role' => 'mitra',
                'city_id' => 1,
                'verified' => true,
                'status' => 'active',
                'phone' => '081234567896',
                'address' => 'Jl. Pahlawan No. 12, Ponorogo',
            ]
        );

        // Mitra 3: Rizky Pratama
        User::firstOrCreate(
            ['email' => 'rizky@example.com'],
            [
                'name' => 'Rizky Pratama',
                'password' => Hash::make('password'),
                'role' => 'mitra',
                'city_id' => 1,
                'verified' => true,
                'status' => 'active',
                'phone' => '081234567897',
                'address' => 'Jl. Pemuda No. 77, Ponorogo',
            ]
        );
    }
}


