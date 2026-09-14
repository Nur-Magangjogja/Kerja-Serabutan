<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `help_cancel_requests` MODIFY COLUMN `partner_id` BIGINT UNSIGNED NULL DEFAULT NULL");
        } else {
            Schema::table('help_cancel_requests', function (Blueprint $table) {
                // Handled in base creation migration for SQLite
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `help_cancel_requests` MODIFY COLUMN `partner_id` BIGINT UNSIGNED NOT NULL");
        }
    }
};
