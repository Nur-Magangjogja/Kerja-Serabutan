<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@sayabantu.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'verified' => true,
            'status' => 'active',
            'phone' => '081234567890',
        ]);

        // Admin Jakarta
        User::create([
            'name' => 'Admin Jakarta',
            'email' => 'admin.jakarta@sayabantu.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'city_id' => 1,
            'verified' => true,
            'status' => 'active',
            'phone' => '081234567891',
        ]);

        // Sample Kustomer
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => Hash::make('password'),
            'role' => 'kustomer',
            'city_id' => 1,
            'verified' => true,
            'status' => 'active',
            'phone' => '081234567892',
            'address' => 'Jl. Merdeka No. 123, Jakarta',
        ]);

        // Sample Mitra
        User::create([
            'name' => 'Ahmad Relawan',
            'email' => 'ahmad@example.com',
            'password' => Hash::make('password'),
            'role' => 'mitra',
            'city_id' => 1,
            'verified' => true,
            'status' => 'active',
            'phone' => '081234567893',
            'address' => 'Jl. Kemanusiaan No. 456, Jakarta',
        ]);
    }
}
