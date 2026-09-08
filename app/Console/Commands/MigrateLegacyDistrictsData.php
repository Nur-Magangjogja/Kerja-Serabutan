<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateLegacyDistrictsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sayabantu:migrate-districts-data {--dry-run : Run without making actual database updates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pemetaan dan migrasi data wilayah lama (Kota/Alamat) ke Kecamatan (district_id) secara aman dan menyeluruh';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        $this->info("=== MEMULAI MIGRASI DATA WILAYAH KECAMATAN REVISI 3 ===" . ($isDryRun ? " [DRY-RUN]" : ""));

        // 1. Pastikan tabel districts terisi
        $totalDistricts = District::count();
        $this->info("Total data Kecamatan di tabel districts: {$totalDistricts}");
        if ($totalDistricts === 0) {
            $this->warn("Tabel districts kosong. Menjalankan seeder IndonesiaRegionsSeeder / DistrictSeeder...");
            if (!$isDryRun) {
                $this->call('db:seed', ['--class' => 'DistrictSeeder', '--force' => true]);
            }
        }

        // 2. Migrasi Users (Customer & Mitra)
        $usersUpdated = 0;
        $users = User::whereNull('district_id')->whereNotNull('city_id')->get();
        $this->info("Memproses " . $users->count() . " akun pengguna yang belum memiliki district_id...");

        foreach ($users as $u) {
            $districtId = $this->resolveUserDistrict($u);
            if ($districtId) {
                $usersUpdated++;
                if (!$isDryRun) {
                    $u->update(['district_id' => $districtId]);
                }
            }
        }
        $this->info("✓ Pengguna berhasil dipetakan ke Kecamatan: {$usersUpdated}");

        // 3. Migrasi Helps (Pesanan / Tugas Bantuan)
        $helpsUpdated = 0;
        $helps = Help::whereNull('district_id')->whereNotNull('city_id')->get();
        $this->info("Memproses " . $helps->count() . " data bantuan yang belum memiliki district_id...");

        foreach ($helps as $h) {
            $districtId = $this->resolveHelpDistrict($h);
            if ($districtId) {
                $helpsUpdated++;
                if (!$isDryRun) {
                    $h->update(['district_id' => $districtId]);
                }
            }
        }
        $this->info("✓ Bantuan berhasil dipetakan ke Kecamatan: {$helpsUpdated}");

        // 4. Migrasi Admin City ke Admin District
        $adminPivotCount = 0;
        $admins = User::where('role', 'admin')->get();
        $this->info("Memproses penugasan wilayah untuk " . $admins->count() . " admin...");

        foreach ($admins as $admin) {
            $cityIds = $admin->getAdminCityIds();
            if (!empty($cityIds)) {
                $districts = District::whereIn('city_id', $cityIds)->pluck('id')->all();
                if (!empty($districts)) {
                    if (!$isDryRun) {
                        $admin->managedDistricts()->syncWithoutDetaching($districts);
                        if (empty($admin->district_id)) {
                            $admin->update(['district_id' => $districts[0]]);
                        }
                    }
                    $adminPivotCount += count($districts);
                }
            }
        }
        $this->info("✓ Relasi Admin-Kecamatan berhasil disinkronkan: {$adminPivotCount} relasi.");

        $this->info("=== MIGRASI DATA WILAYAH KECAMATAN REVISI 3 SELESAI ===");
        return Command::SUCCESS;
    }

    private function resolveUserDistrict(User $user): ?int
    {
        $cityId = $user->city_id;
        if (!$cityId) return null;

        $districts = District::where('city_id', $cityId)->get();
        if ($districts->isEmpty()) return null;

        // 1. Coba cocokkan dengan kolom kecamatan / kelurahan / address text
        $searchTerms = array_filter([
            trim((string)$user->kecamatan),
            trim((string)$user->kelurahan),
            trim((string)$user->address),
        ]);

        foreach ($searchTerms as $term) {
            if (empty($term)) continue;
            foreach ($districts as $d) {
                if (stripos($term, $d->name) !== false || stripos($d->name, $term) !== false) {
                    return $d->id;
                }
            }
        }

        // 2. Fallback: Ambil kecamatan pertama dari kota terkait
        return $districts->first()->id;
    }

    private function resolveHelpDistrict(Help $help): ?int
    {
        $cityId = $help->city_id;
        if (!$cityId) return null;

        $districts = District::where('city_id', $cityId)->get();
        if ($districts->isEmpty()) return null;

        // 1. Coba cocokkan dengan kolom location / full_address
        $searchTerms = array_filter([
            trim((string)$help->location),
            trim((string)$help->full_address),
        ]);

        foreach ($searchTerms as $term) {
            if (empty($term)) continue;
            foreach ($districts as $d) {
                if (stripos($term, $d->name) !== false || stripos($d->name, $term) !== false) {
                    return $d->id;
                }
            }
        }

        // 2. Coba ambil dari user pembuat (customer)
        if ($help->user && !empty($help->user->district_id)) {
            $userDistrict = $districts->firstWhere('id', $help->user->district_id);
            if ($userDistrict) {
                return $userDistrict->id;
            }
        }

        // 3. Fallback: Ambil kecamatan pertama dari kota terkait
        return $districts->first()->id;
    }
}
