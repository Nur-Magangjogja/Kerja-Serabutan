<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\CityCapacity;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            [
                'name'        => 'Kabupaten Sleman',
                'province'    => 'D.I. Yogyakarta',
                'province_id' => '34',
                'code'        => '3404',
                'type'        => 'Kabupaten',
                'postal_code' => '55511',
                'latitude'    => -7.7155600,
                'longitude'   => 110.3555600,
                'is_active'   => true,
            ],
            [
                'name'        => 'Kota Surakarta',
                'province'    => 'Jawa Tengah',
                'province_id' => '33',
                'code'        => '3372',
                'type'        => 'Kota',
                'postal_code' => '57111',
                'latitude'    => -7.5666700,
                'longitude'   => 110.8166700,
                'is_active'   => true,
            ],
            [
                'name'        => 'Kota Yogyakarta',
                'province'    => 'D.I. Yogyakarta',
                'province_id' => '34',
                'code'        => '3471',
                'type'        => 'Kota',
                'postal_code' => '55000',
                'latitude'    => -7.7956000,
                'longitude'   => 110.3695000,
                'is_active'   => true,
            ],
            [
                'name'        => 'Kota Semarang',
                'province'    => 'Jawa Tengah',
                'province_id' => '33',
                'code'        => '3374',
                'type'        => 'Kota',
                'postal_code' => '50134',
                'latitude'    => -6.9932000,
                'longitude'   => 110.4203000,
                'is_active'   => true,
            ],
        ];

        foreach ($cities as $cityData) {
            $city = City::updateOrCreate(
                ['name' => $cityData['name']],
                $cityData
            );

            // Inisialisasi kapasitas kota
            CityCapacity::firstOrCreate(
                ['city_id' => $city->id],
                [
                    'capacity_status'          => 'open',
                    'online_total'             => 25,
                    'busy_now'                 => 0,
                    'searching_now'            => 0,
                    'partner_utilization_rate' => 15.0,
                ]
            );
        }

        $this->command->info('CitySeeder berhasil menyiapkan wilayah Sleman (D.I. Yogyakarta) & Surakarta (Jawa Tengah).');
    }
}
