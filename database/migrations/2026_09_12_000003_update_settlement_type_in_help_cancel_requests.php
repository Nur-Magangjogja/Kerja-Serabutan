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
            DB::statement("ALTER TABLE `help_cancel_requests` MODIFY COLUMN `settlement_type` VARCHAR(50) NULL DEFAULT NULL");
        } else {
            Schema::table('help_cancel_requests', function (Blueprint $table) {
                // SQLite handles string/enum uniformly
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `help_cancel_requests` MODIFY COLUMN `settlement_type` ENUM('full_refund', 'partial_settlement', 'item_settled', 'no_refund') NULL DEFAULT NULL");
        }
    }
};
