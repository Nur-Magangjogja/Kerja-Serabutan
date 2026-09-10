<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normalisasi status lama pada tabel helps ke status kanonik resmi
        DB::table('helps')->where('status', 'memperoleh_mitra')->update(['status' => 'taken']);
        DB::table('helps')->where('status', 'sedang_diproses')->update(['status' => 'in_progress']);
        DB::table('helps')->where('status', 'completed')->update(['status' => 'selesai']);
        DB::table('helps')->where('status', 'cancelled')->update(['status' => 'dibatalkan']);
        DB::table('helps')->whereIn('status', ['mencari_mitra', 'pending', 'menunggu_pembayaran'])->update(['status' => 'menunggu_mitra']);
        DB::table('helps')->where('status', 'waiting_confirmation')->update(['status' => 'waiting_customer_confirmation']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed as canonical statuses are superset
    }
};
