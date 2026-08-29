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
        Schema::table('partner_online_states', function (Blueprint $table) {
            $table->unsignedTinyInteger('consecutive_declines')->default(0)->after('current_help_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_online_states', function (Blueprint $table) {
            $table->dropColumn('consecutive_declines');
        });
    }
};
