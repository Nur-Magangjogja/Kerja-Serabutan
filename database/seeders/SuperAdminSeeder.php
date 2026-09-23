<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi akun utama Super Admin untuk pengelolaan sistem SayaBantu secara terpusat.
     */
    public function run(): void
    {
        $commonPassword = Hash::make('password');
        $now = now();

        // =========================================================================
        // SUPER ADMIN
        // =========================================================================
        User::updateOrCreate(
            ['email' => 'superadmin@sayabantu.com'],
            [
                'name'              => 'superadmin',
                'password'          => $commonPassword,
                'role'              => 'super_admin',
                'status'            => 'active',
                'email_verified_at' => $now,
            ]
        );
    }
}
