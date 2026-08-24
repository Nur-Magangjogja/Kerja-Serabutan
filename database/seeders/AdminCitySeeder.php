<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menghubungkan akun Admin ke Kota/Kabupaten Sleman.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@sayabantu.com')->first();
        $slemanCity = City::where('name', 'like', '%Sleman%')->first();

        if (!$admin || !$slemanCity) {
            $this->command->warn('Admin user atau Kota Sleman tidak ditemukan.');
            return;
        }

        // 1. Hubungkan di tabel pivot `admin_city`
        if (Schema::hasTable('admin_city')) {
            DB::table('admin_city')->updateOrInsert(
                [
                    'city_id' => $slemanCity->id,
                    'user_id' => $admin->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 2. Set admin_id di tabel `cities`
        $slemanCity->update([
            'admin_id' => $admin->id,
            'is_active' => true,
        ]);

        $this->command->info("Admin '{$admin->name}' berhasil ditugaskan mengurusi Kota/Kabupaten '{$slemanCity->name}'.");
    }
}
