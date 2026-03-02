<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Expand enum values for registrations.status to include app statuses
        DB::statement("ALTER TABLE `registrations` MODIFY `status` ENUM('in_progress','pending_verification','approved','rejected','completed','cancelled') NOT NULL DEFAULT 'in_progress'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE `registrations` MODIFY `status` ENUM('in_progress','completed','cancelled') NOT NULL DEFAULT 'in_progress'");
    }
};
