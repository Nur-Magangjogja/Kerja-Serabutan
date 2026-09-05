<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menghubungkan akun Admin ke Kota/Kabupaten:
     * - Admin Dian Wahyuni mengelola 2 WILAYAH (Kabupaten Sleman & Kota Yogyakarta)
     * - Admin Siti Nurhaliza mengelola 2 WILAYAH (Kabupaten Sleman & Kota Yogyakarta)
     * - Admin Bambang Haryanto mengelola 2 WILAYAH (Kota Surakarta & Kabupaten Sukoharjo)
     */
    public function run(): void
    {
        $adminSleman    = User::where('email', 'admin.sleman@sayabantu.com')->first();
        $adminJogja     = User::where('email', 'admin@sayabantu.com')->first();
        $adminSurakarta = User::where('email', 'admin.surakarta@sayabantu.com')->first();

        $slemanCity    = City::where('code', '3404')->orWhere('name', 'like', '%Sleman%')->first();
        $jogjaCity     = City::where('code', '3471')->orWhere('name', 'like', '%Yogyakarta%')->first();
        $surakartaCity = City::where('code', '3372')->orWhere('name', 'like', '%Surakarta%')->first();
        $sukoharjoCity = City::where('code', '3311')->orWhere('name', 'like', '%Sukoharjo%')->first();

        // 1. Hubungkan Admin Sleman & Yogyakarta (Dian Wahyuni)
        if ($adminSleman) {
            if ($slemanCity) {
                DB::table('admin_city')->updateOrInsert(
                    ['city_id' => $slemanCity->id, 'user_id' => $adminSleman->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
                $slemanCity->update(['admin_id' => $adminSleman->id, 'is_active' => true]);
            }
            if ($jogjaCity) {
                DB::table('admin_city')->updateOrInsert(
                    ['city_id' => $jogjaCity->id, 'user_id' => $adminSleman->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
                $jogjaCity->update(['admin_id' => $adminSleman->id, 'is_active' => true]);
            }
            $this->command->info("Admin '{$adminSleman->name}' berhasil ditugaskan mengurusi 2 Wilayah: '{$slemanCity->name}' & '{$jogjaCity->name}'.");
        }

        // 2. Hubungkan Admin Pendamping (Siti Nurhaliza)
        if ($adminJogja) {
            if ($jogjaCity) {
                DB::table('admin_city')->updateOrInsert(
                    ['city_id' => $jogjaCity->id, 'user_id' => $adminJogja->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
            if ($slemanCity) {
                DB::table('admin_city')->updateOrInsert(
                    ['city_id' => $slemanCity->id, 'user_id' => $adminJogja->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // 3. Hubungkan Admin Surakarta & Sukoharjo (Bambang Haryanto)
        if ($adminSurakarta) {
            if ($surakartaCity) {
                DB::table('admin_city')->updateOrInsert(
                    ['city_id' => $surakartaCity->id, 'user_id' => $adminSurakarta->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
                $surakartaCity->update(['admin_id' => $adminSurakarta->id, 'is_active' => true]);
            }
            if ($sukoharjoCity) {
                DB::table('admin_city')->updateOrInsert(
                    ['city_id' => $sukoharjoCity->id, 'user_id' => $adminSurakarta->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
                $sukoharjoCity->update(['admin_id' => $adminSurakarta->id, 'is_active' => true]);
            }
            $this->command->info("Admin '{$adminSurakarta->name}' berhasil ditugaskan mengurusi Wilayah '{$surakartaCity->name}' & '{$sukoharjoCity->name}'.");
        }
    }
}
