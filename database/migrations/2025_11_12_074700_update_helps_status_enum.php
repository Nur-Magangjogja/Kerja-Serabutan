<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing status values to new ones
        DB::table('helps')->where('status', 'pending')->update(['status' => 'temp_menunggu']);
        DB::table('helps')->where('status', 'taken')->update(['status' => 'temp_memperoleh']);
        DB::table('helps')->where('status', 'in_progress')->update(['status' => 'temp_memperoleh']);
        DB::table('helps')->where('status', 'completed')->update(['status' => 'temp_selesai']);

        // Change the enum column
        DB::statement("ALTER TABLE helps MODIFY COLUMN status ENUM('menunggu_mitra', 'memperoleh_mitra', 'selesai') DEFAULT 'menunggu_mitra'");

        // Update temp values to final values
        DB::table('helps')->where('status', 'temp_menunggu')->update(['status' => 'menunggu_mitra']);
        DB::table('helps')->where('status', 'temp_memperoleh')->update(['status' => 'memperoleh_mitra']);
        DB::table('helps')->where('status', 'temp_selesai')->update(['status' => 'selesai']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old enum values
        DB::statement("ALTER TABLE helps MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'taken', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};
