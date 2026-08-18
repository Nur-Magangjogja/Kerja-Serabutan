<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $citiesPath = database_path('seeders/data/cities.json');
        if (file_exists($citiesPath)) {
            $this->call(IndonesiaRegionsSeeder::class);
            return;
        }

        $cities = [
            ['name' => 'Jakarta', 'province' => 'DKI Jakarta', 'code' => '3171', 'type' => 'Kota'],
            ['name' => 'Ponorogo', 'province' => 'Jawa Timur', 'code' => '3502', 'type' => 'Kabupaten'],
            ['name' => 'Surabaya', 'province' => 'Jawa Timur', 'code' => '3578', 'type' => 'Kota'],
            ['name' => 'Bandung', 'province' => 'Jawa Barat', 'code' => '3273', 'type' => 'Kota'],
            ['name' => 'Medan', 'province' => 'Sumatera Utara', 'code' => '1271', 'type' => 'Kota'],
            ['name' => 'Semarang', 'province' => 'Jawa Tengah', 'code' => '3374', 'type' => 'Kota'],
            ['name' => 'Yogyakarta', 'province' => 'DI Yogyakarta', 'code' => '3471', 'type' => 'Kota'],
            ['name' => 'Makassar', 'province' => 'Sulawesi Selatan', 'code' => '7371', 'type' => 'Kota'],
            ['name' => 'Palembang', 'province' => 'Sumatera Selatan', 'code' => '1671', 'type' => 'Kota'],
            ['name' => 'Denpasar', 'province' => 'Bali', 'code' => '5171', 'type' => 'Kota'],
            ['name' => 'Malang', 'province' => 'Jawa Timur', 'code' => '3573', 'type' => 'Kota'],
        ];

        foreach ($cities as $city) {
            City::firstOrCreate(['name' => $city['name']], $city);
        }
    }
}
