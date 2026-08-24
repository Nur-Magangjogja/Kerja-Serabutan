<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * Mengisi data awal sistem sesuai struktur migration database terbaru.
     */
    public function run(): void
    {
        $this->call([
            IndonesiaRegionsSeeder::class,
            AppSettingsSeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            AdminCitySeeder::class,
            HelpsSeeder::class,
            UserBalancesSeeder::class,
            AdminNotificationSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
