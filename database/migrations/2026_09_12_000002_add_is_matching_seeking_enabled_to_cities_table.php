<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            if (!Schema::hasColumn('cities', 'is_matching_seeking_enabled')) {
                $table->boolean('is_matching_seeking_enabled')
                    ->nullable()
                    ->default(null)
                    ->after('is_active')
                    ->comment('NULL = Ikuti Global, 1 = Aktifkan Antrean, 0 = Langsung ke Daftar Bantuan');
                $table->index('is_matching_seeking_enabled', 'idx_cities_seeking_enabled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            if (Schema::hasColumn('cities', 'is_matching_seeking_enabled')) {
                $table->dropIndex('idx_cities_seeking_enabled');
                $table->dropColumn('is_matching_seeking_enabled');
            }
        });
    }
};
