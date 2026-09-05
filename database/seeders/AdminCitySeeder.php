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
     * Menghubungkan akun Admin ke Kota/Kabupaten Sleman & Surakarta.
     */
    public function run(): void
    {
        $adminSleman = User::whereIn('email', ['admin.sleman@sayabantu.com', 'admin@sayabantu.com'])->first();
        $adminSolo   = User::where('email', 'admin.surakarta@sayabantu.com')->first();

        $slemanCity  = City::where('name', 'like', '%Sleman%')->first();
        $soloCity    = City::where('name', 'like', '%Surakarta%')->first();

        // 1. Hubungkan Admin Sleman
        if ($adminSleman && $slemanCity) {
            if (Schema::hasTable('admin_city')) {
                DB::table('admin_city')->updateOrInsert(
                    ['city_id' => $slemanCity->id, 'user_id' => $adminSleman->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
            $slemanCity->update([
                'admin_id' => $adminSleman->id,
                'is_active' => true,
            ]);
            $this->command->info("Admin '{$adminSleman->name}' berhasil ditugaskan mengurusi Kota/Kabupaten '{$slemanCity->name}'.");
        }

        // 2. Hubungkan Admin Surakarta
        if ($adminSolo && $soloCity) {
            if (Schema::hasTable('admin_city')) {
                DB::table('admin_city')->updateOrInsert(
                    ['city_id' => $soloCity->id, 'user_id' => $adminSolo->id],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
            $soloCity->update([
                'admin_id' => $adminSolo->id,
                'is_active' => true,
            ]);
            $this->command->info("Admin '{$adminSolo->name}' berhasil ditugaskan mengurusi Kota/Kabupaten '{$soloCity->name}'.");
        }
    }
}

