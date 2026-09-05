<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * Mengisi seluruh data awal sistem SayaBantu secara lengkap, terstruktur, dan realistis.
     */
    public function run(): void
    {
        $this->call([
            AppSettingsSeeder::class,
            CitySeeder::class,
            UserSeeder::class,
            AdminCitySeeder::class,
            RegistrationsSeeder::class,
            HelpsSeeder::class,
            UserBalancesSeeder::class,
            PartnerHistorySeeder::class,
            PartnerOnlineStateSeeder::class,
            ActivityLogsSeeder::class,
            AdminNotificationSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
