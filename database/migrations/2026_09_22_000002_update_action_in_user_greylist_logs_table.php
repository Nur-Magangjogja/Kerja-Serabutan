<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('user_greylist_logs')) {
            DB::statement("ALTER TABLE user_greylist_logs MODIFY COLUMN action VARCHAR(50) NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_greylist_logs')) {
            DB::statement("ALTER TABLE user_greylist_logs MODIFY COLUMN action ENUM('greylist_add', 'greylist_remove', 'warning_issued', 'shadow_ban_enabled', 'shadow_ban_disabled') NOT NULL");
        }
    }
};
