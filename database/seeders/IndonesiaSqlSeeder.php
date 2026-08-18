<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndonesiaSqlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $indonesiaPath = __DIR__ . '/sql/indonesia.sql';
        $kecamatanPath = __DIR__ . '/sql/kecamatan.sql';

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        if (file_exists($indonesiaPath)) {
            $this->command->info('Importing ' . $indonesiaPath . '...');
            DB::unprepared(file_get_contents($indonesiaPath));
            $this->command->info('Successfully imported indonesia.sql');
        } else {
            $this->command->warn("Missing file: {$indonesiaPath}");
        }

        if (file_exists($kecamatanPath)) {
            $this->command->info('Importing ' . $kecamatanPath . '...');
            DB::unprepared(file_get_contents($kecamatanPath));
            $this->command->info('Successfully imported kecamatan.sql');
        } else {
            $this->command->warn("Missing file: {$kecamatanPath}");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

