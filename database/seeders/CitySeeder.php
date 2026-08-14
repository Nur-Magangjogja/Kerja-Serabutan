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
        $cities = [
            ['name' => 'Jakarta', 'province' => 'DKI Jakarta'],
            ['name' => 'Surabaya', 'province' => 'Jawa Timur'],
            ['name' => 'Bandung', 'province' => 'Jawa Barat'],
            ['name' => 'Medan', 'province' => 'Sumatera Utara'],
            ['name' => 'Semarang', 'province' => 'Jawa Tengah'],
            ['name' => 'Yogyakarta', 'province' => 'DI Yogyakarta'],
            ['name' => 'Makassar', 'province' => 'Sulawesi Selatan'],
            ['name' => 'Palembang', 'province' => 'Sumatera Selatan'],
            ['name' => 'Denpasar', 'province' => 'Bali'],
            ['name' => 'Malang', 'province' => 'Jawa Timur'],
        ];

        foreach ($cities as $city) {
            City::create($city);
        }
    }
}
