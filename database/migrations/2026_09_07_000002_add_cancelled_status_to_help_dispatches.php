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
            DB::statement("ALTER TABLE `help_dispatches` MODIFY COLUMN `status` ENUM('offered', 'accepted', 'rejected', 'expired', 'cancelled') NOT NULL DEFAULT 'offered'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `help_dispatches` MODIFY COLUMN `status` ENUM('offered', 'accepted', 'rejected', 'expired') NOT NULL DEFAULT 'offered'");
        }
    }
};
