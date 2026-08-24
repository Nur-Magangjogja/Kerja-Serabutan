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
        if (Schema::hasTable('helps') && !Schema::hasColumn('helps', 'cancelled_mitra_ids')) {
            Schema::table('helps', function (Blueprint $table) {
                $table->json('cancelled_mitra_ids')->nullable()->after('partner_cancel_prev_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('helps') && Schema::hasColumn('helps', 'cancelled_mitra_ids')) {
            Schema::table('helps', function (Blueprint $table) {
                $table->dropColumn('cancelled_mitra_ids');
            });
        }
    }
};
