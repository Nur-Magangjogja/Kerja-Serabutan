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
    protected $description = 'Memetakan data lama User, Help, dan Admin dari city_id ke district_id.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        $this->info("=== MIGRASI DATA WILAYAH KE KECAMATAN ===" . ($isDryRun ? " [DRY-RUN]" : ""));

        // 1. Pastikan tabel districts terisi
        $totalDistricts = District::count();
        $this->info("Total data Kecamatan di tabel districts: {$totalDistricts}");
        if ($totalDistricts === 0) {
            $this->error(
                'Data kecamatan belum tersedia. ' .
                'Jalankan "php artisan db:seed --class=CitySeeder" terlebih dahulu untuk mengisi data wilayah.'
            );

            return Command::FAILURE;
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

        // 4. Normalisasi Metadata Admin (Kecamatan Eksplisit -> Kota Binaan)
        // Canonical Rule AW6: admin_district adalah satu-satunya sumber wewenang operasional.
        // admin_city diturunkan secara deterministik dari parent city milik managedDistricts.
        // Tidak boleh melakukan ekspansi terbalik (admin_city -> semua kecamatan) atau menebak wewenang!
        $adminPivotCount = 0;
        $admins = User::where('role', 'admin')->with('managedDistricts.city')->get();
        $this->info("Memproses penugasan wilayah untuk " . $admins->count() . " admin...");

        foreach ($admins as $admin) {
            $assignedDistricts = $admin->managedDistricts;
            if ($assignedDistricts->isNotEmpty()) {
                $derivedCityIds = $assignedDistricts->pluck('city_id')->filter()->unique()->values()->all();
                if (!$isDryRun) {
                    $admin->managedCities()->sync($derivedCityIds);
                }
                $adminPivotCount += count($derivedCityIds);
            } else {
                // Fail-closed: Admin tanpa managedDistricts tidak boleh ditebak wewenangnya
                $this->line("  [INFO] Admin #{$admin->id} ({$admin->email}) belum memiliki penugasan kecamatan eksplisit (tetap fail-closed 0 wewenang).");
            }
        }
        $this->info("✓ Metadata Kota binaan berhasil disinkronkan dari Kecamatan wewenang: {$adminPivotCount} relasi.");

        $this->info("=== MIGRASI DATA WILAYAH KE KECAMATAN SELESAI ===");
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
