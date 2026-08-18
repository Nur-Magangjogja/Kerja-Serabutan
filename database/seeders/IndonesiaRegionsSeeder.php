<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IndonesiaRegionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $citiesPath = database_path('seeders/data/cities.json');
        $districtsPath = database_path('seeders/data/districts.json');

        if (! file_exists($citiesPath)) {
            $this->command->error("Missing file: {$citiesPath}");
            return;
        }

        $citiesJson = json_decode(file_get_contents($citiesPath), true);
        if (! is_array($citiesJson)) {
            $this->command->error('Invalid JSON in cities file');
            return;
        }

        $this->command->info('Importing cities (' . count($citiesJson) . ' cities/kabupaten)...');

        $now = now();
        $citiesBatch = [];
        $regProvinces = [];
        $regRegencies = [];

        foreach ($citiesJson as $c) {
            $code = (string)($c['code'] ?? $c['id']);
            $provId = substr($code, 0, 2);
            $provName = $c['province'] ?? 'Indonesia';

            $regProvinces[$provId] = [
                'id' => $provId,
                'name' => $provName,
            ];

            $regRegencies[$code] = [
                'id' => $code,
                'province_id' => $provId,
                'name' => $c['name'],
            ];

            $citiesBatch[] = [
                'name' => $c['name'],
                'province' => $provName,
                'code' => $code,
                'type' => $c['type'] ?? (substr($code, 2, 1) === '7' ? 'Kota' : 'Kabupaten'),
                'postal_code' => $c['postal_code'] ?? null,
                'latitude' => $c['latitude'] ?? null,
                'longitude' => $c['longitude'] ?? null,
                'is_active' => isset($c['is_active']) ? (bool)$c['is_active'] : true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // 1. Upsert into `cities` table
        DB::transaction(function () use ($citiesBatch) {
            foreach (array_chunk($citiesBatch, 100) as $chunk) {
                City::upsert(
                    $chunk,
                    ['name', 'province'],
                    ['code', 'type', 'postal_code', 'latitude', 'longitude', 'is_active', 'updated_at']
                );
            }
        });

        // 2. Upsert into `reg_provinces` and `reg_regencies` if present
        if (Schema::hasTable('reg_provinces')) {
            DB::table('reg_provinces')->upsert(
                array_values($regProvinces),
                ['id'],
                ['name']
            );
        }

        if (Schema::hasTable('reg_regencies')) {
            DB::table('reg_regencies')->upsert(
                array_values($regRegencies),
                ['id'],
                ['province_id', 'name']
            );
        }

        if (! file_exists($districtsPath)) {
            $this->command->warn("Districts file not found: {$districtsPath} — skipping districts import.");
            return;
        }

        $districtsJson = json_decode(file_get_contents($districtsPath), true);
        if (! is_array($districtsJson)) {
            $this->command->error('Invalid JSON in districts file');
            return;
        }

        // Build City map by code and by name for fast O(1) in-memory lookup
        $cityIdByCode = City::pluck('id', 'code')->toArray();
        $cityIdByName = City::pluck('id', 'name')->toArray();

        $this->command->info('Importing districts and subdistricts into database...');

        $districtsBatch = [];
        $regDistrictsBatch = [];
        $regVillagesBatch = [];

        foreach ($districtsJson as $d) {
            $code = (string)($d['code'] ?? '');
            
            // 6-digit: Kecamatan
            if (strlen($code) === 6) {
                $cityCode = (string)($d['city_code'] ?? substr($code, 0, 4));
                $cityId = $cityIdByCode[$cityCode] ?? ($cityIdByName[$d['name'] ?? ''] ?? null);

                if ($cityId) {
                    $districtsBatch[] = [
                        'city_id' => $cityId,
                        'name' => $d['name'],
                        'code' => $code,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                $regDistrictsBatch[] = [
                    'id' => $code,
                    'regency_id' => $cityCode,
                    'name' => $d['name'],
                ];
            } elseif (strlen($code) === 10) {
                // 10-digit: Desa / Kelurahan
                $districtCode = (string)($d['district_code'] ?? substr($code, 0, 6));
                $regVillagesBatch[] = [
                    'id' => $code,
                    'district_id' => $districtCode,
                    'name' => $d['name'],
                ];
            }
        }

        // 3. Fast bulk insert into `districts`
        $hasDistrictsTable = Schema::hasTable('districts');
        if ($hasDistrictsTable && !empty($districtsBatch)) {
            $this->command->info('Inserting ' . count($districtsBatch) . ' kecamatan into `districts` table...');
            DB::transaction(function () use ($districtsBatch) {
                foreach (array_chunk($districtsBatch, 500) as $chunk) {
                    District::upsert(
                        $chunk,
                        ['city_id', 'name'],
                        ['code', 'is_active', 'updated_at']
                    );
                }
            });
        }

        // 4. Fast bulk insert into `reg_districts`
        if (Schema::hasTable('reg_districts') && !empty($regDistrictsBatch)) {
            $this->command->info('Inserting ' . count($regDistrictsBatch) . ' kecamatan into `reg_districts` table...');
            foreach (array_chunk($regDistrictsBatch, 500) as $chunk) {
                DB::table('reg_districts')->upsert(
                    $chunk,
                    ['id'],
                    ['regency_id', 'name']
                );
            }
        }

        // 5. Fast bulk insert into `reg_villages` if table exists
        if (Schema::hasTable('reg_villages') && !empty($regVillagesBatch)) {
            $this->command->info('Inserting ' . count($regVillagesBatch) . ' desa/kelurahan into `reg_villages` table...');
            foreach (array_chunk($regVillagesBatch, 1000) as $chunk) {
                DB::table('reg_villages')->upsert(
                    $chunk,
                    ['id'],
                    ['district_id', 'name']
                );
            }
        }

        $this->command->info('Indonesia regions import completed successfully!');
    }
}

