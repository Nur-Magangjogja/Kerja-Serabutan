<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\CityCapacity;
use App\Models\District;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengimpor seluruh kota/kabupaten dan kecamatan se-Indonesia dari folder data/ dan sql/.
     */
    public function run(): void
    {
        // 1. Eksekusi SQL Dump (indonesia.sql & kecamatan.sql) jika ada
        $indonesiaSqlPath = database_path('seeders/sql/indonesia.sql');
        $kecamatanSqlPath = database_path('seeders/sql/kecamatan.sql');

        if (file_exists($indonesiaSqlPath) || file_exists($kecamatanSqlPath)) {
            $this->command->info('Memproses impor database SQL wilayah Indonesia...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            if (file_exists($indonesiaSqlPath)) {
                DB::unprepared(file_get_contents($indonesiaSqlPath));
                $this->command->info('✓ Berhasil memuat data indonesia.sql');
            }

            if (file_exists($kecamatanSqlPath)) {
                DB::unprepared(file_get_contents($kecamatanSqlPath));
                $this->command->info('✓ Berhasil memuat data kecamatan.sql');
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // 2. Impor Seluruh Kota/Kabupaten dari cities.json
        $citiesPath = database_path('seeders/data/cities.json');
        if (!file_exists($citiesPath)) {
            $this->command->error("File tidak ditemukan: {$citiesPath}");
            return;
        }

        $citiesJson = json_decode(file_get_contents($citiesPath), true);
        if (!is_array($citiesJson)) {
            $this->command->error("Format JSON pada {$citiesPath} tidak valid.");
            return;
        }

        $this->command->info("Mengimpor " . count($citiesJson) . " kota/kabupaten ke tabel `cities`...");

        $now = now();
        $citiesBatch = [];

        foreach ($citiesJson as $c) {
            $code = (string)($c['code'] ?? $c['id']);
            $rawName = trim($c['name'] ?? '');
            $type = $c['type'] ?? (str_starts_with($code, '347') || substr($code, 2, 1) === '7' ? 'Kota' : 'Kabupaten');
            $provName = $c['province'] ?? 'Indonesia';
            $provId = (string)($c['province_id'] ?? substr($code, 0, 2));

            // Format nama standar rapi: 'Kabupaten Sleman', 'Kota Surakarta', dll.
            $formattedName = $rawName;
            if (!str_starts_with($rawName, 'Kota') && !str_starts_with($rawName, 'Kabupaten')) {
                $formattedName = $type . ' ' . $rawName;
            }

            // Koordinat khusus untuk Sleman dan Surakarta
            $latitude = $c['latitude'] ?? null;
            $longitude = $c['longitude'] ?? null;

            if ($code === '3404' || str_contains($rawName, 'Sleman')) {
                $formattedName = 'Kabupaten Sleman';
                $latitude = -7.7155600;
                $longitude = 110.3555600;
            } elseif ($code === '3372' || str_contains($rawName, 'Surakarta')) {
                $formattedName = 'Kota Surakarta';
                $latitude = -7.5666700;
                $longitude = 110.8166700;
            } elseif ($code === '3471' || str_contains($rawName, 'Yogyakarta')) {
                $formattedName = 'Kota Yogyakarta';
                $latitude = -7.7956000;
                $longitude = 110.3695000;
            } elseif ($code === '3374' || str_contains($rawName, 'Semarang')) {
                $formattedName = 'Kota Semarang';
                $latitude = -6.9932000;
                $longitude = 110.4203000;
            }

            $citiesBatch[] = [
                'code'        => $code,
                'name'        => $formattedName,
                'type'        => $type,
                'province'    => $provName,
                'province_id' => $provId,
                'postal_code' => $c['postal_code'] ?? null,
                'latitude'    => $latitude,
                'longitude'   => $longitude,
                'is_active'   => isset($c['is_active']) ? (bool)$c['is_active'] : true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        // Upsert data kota ke tabel cities
        DB::transaction(function () use ($citiesBatch) {
            foreach (array_chunk($citiesBatch, 100) as $chunk) {
                City::upsert(
                    $chunk,
                    ['code'],
                    ['name', 'type', 'province', 'province_id', 'postal_code', 'latitude', 'longitude', 'is_active', 'updated_at']
                );
            }
        });

        // 3. Inisialisasi Kapasitas Kota (CityCapacity) untuk semua kota yang terdaftar
        $allCities = City::select('id')->get();
        $capacityBatch = [];
        foreach ($allCities as $city) {
            $capacityBatch[] = [
                'city_id'                  => $city->id,
                'capacity_status'          => 'open',
                'auto_manage'              => true,
                'online_total'             => 25,
                'busy_now'                 => 0,
                'searching_now'            => 0,
                'partner_utilization_rate' => 0.0,
                'created_at'               => $now,
                'updated_at'               => $now,
            ];
        }

        foreach (array_chunk($capacityBatch, 100) as $chunk) {
            CityCapacity::upsert(
                $chunk,
                ['city_id'],
                ['capacity_status', 'auto_manage', 'updated_at']
            );
        }
        $this->command->info("✓ Inisialisasi CityCapacity untuk " . count($allCities) . " kota berhasil.");

        // 4. Impor Seluruh Kecamatan dari districts.json
        $districtsPath = database_path('seeders/data/districts.json');
        if (file_exists($districtsPath) && Schema::hasTable('districts')) {
            $districtsJson = json_decode(file_get_contents($districtsPath), true);
            if (is_array($districtsJson)) {
                $cityIdByCode = City::pluck('id', 'code')->toArray();
                $districtsBatch = [];

                foreach ($districtsJson as $d) {
                    $code = (string)($d['code'] ?? '');
                    // Filter 6 digit kode kecamatan
                    if (strlen($code) === 6) {
                        $cityCode = (string)($d['city_code'] ?? substr($code, 0, 4));
                        $cityId = $cityIdByCode[$cityCode] ?? null;

                        if ($cityId) {
                            $districtsBatch[] = [
                                'city_id'    => $cityId,
                                'name'       => $d['name'],
                                'code'       => $code,
                                'is_active'  => true,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                }

                if (!empty($districtsBatch)) {
                    $this->command->info("Mengimpor " . count($districtsBatch) . " kecamatan ke tabel `districts`...");
                    DB::transaction(function () use ($districtsBatch) {
                        foreach (array_chunk($districtsBatch, 500) as $chunk) {
                            District::upsert(
                                $chunk,
                                ['code'],
                                ['city_id', 'name', 'is_active', 'updated_at']
                            );
                        }
                    });
                    $this->command->info("✓ Impor kecamatan selesai.");
                }
            }
        }

        $this->command->info("CitySeeder berhasil menyiapkan seluruh kota dan wilayah Indonesia.");
    }
}
