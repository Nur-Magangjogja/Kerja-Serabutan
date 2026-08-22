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
        Schema::table('partner_activities', function (Blueprint $table) {
            if (!Schema::hasColumn('partner_activities', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('photo');
            }
            if (!Schema::hasColumn('partner_activities', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_activities', function (Blueprint $table) {
            if (Schema::hasColumn('partner_activities', 'user_agent')) {
                $table->dropColumn('user_agent');
            }
            if (Schema::hasColumn('partner_activities', 'ip_address')) {
                $table->dropColumn('ip_address');
            }
        });
    }
};
