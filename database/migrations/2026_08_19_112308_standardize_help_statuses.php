<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Standarisasi status bantuan yang redundan/alias ke status kanonik.
     *
     * Pemetaan:
     *   memperoleh_mitra  → taken
     *   sedang_diproses   → in_progress
     *   completed         → selesai
     *   active            → menunggu_mitra  (jika memang belum punya mitra)
     */
    public function up(): void
    {
        if (!Schema::hasTable('helps')) {
            return;
        }

        // memperoleh_mitra → taken
        DB::table('helps')
            ->where('status', 'memperoleh_mitra')
            ->update(['status' => 'taken']);

        // sedang_diproses → in_progress
        DB::table('helps')
            ->where('status', 'sedang_diproses')
            ->update(['status' => 'in_progress']);

        // completed → selesai
        DB::table('helps')
            ->where('status', 'completed')
            ->update(['status' => 'selesai']);

        // cancelled → dibatalkan
        DB::table('helps')
            ->where('status', 'cancelled')
            ->update(['status' => 'dibatalkan']);

        // active (status lama) yang sudah punya mitra → taken
        DB::table('helps')
            ->where('status', 'active')
            ->whereNotNull('mitra_id')
            ->update(['status' => 'taken']);

        // active (status lama) yang belum punya mitra → menunggu_mitra
        DB::table('helps')
            ->where('status', 'active')
            ->whereNull('mitra_id')
            ->update(['status' => 'menunggu_mitra']);

        // approved → menunggu_mitra
        DB::table('helps')
            ->where('status', 'approved')
            ->whereNull('mitra_id')
            ->update(['status' => 'menunggu_mitra']);

        // pending → menunggu_mitra
        DB::table('helps')
            ->where('status', 'pending')
            ->update(['status' => 'menunggu_mitra']);

        // open → menunggu_mitra
        DB::table('helps')
            ->where('status', 'open')
            ->whereNull('mitra_id')
            ->update(['status' => 'menunggu_mitra']);

        // diproses_mitra → in_progress
        DB::table('helps')
            ->where('status', 'diproses_mitra')
            ->update(['status' => 'in_progress']);
    }

    /**
     * Rollback standardisasi tidak mungkin karena mapping banyak-ke-satu.
     * Kita cukup log dan tidak melakukan apa-apa.
     */
    public function down(): void
    {
        // Tidak dapat di-rollback secara otomatis (destructive one-way migration)
        // Silakan restore dari backup database jika diperlukan.
    }
};
