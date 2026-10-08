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
        Schema::table('helps', function (Blueprint $table) {
            if (!Schema::hasColumn('helps', 'schedule_reconfirmation_requested_at')) {
                $table->timestamp('schedule_reconfirmation_requested_at')->nullable()->after('schedule_overdue_at');
            }
            if (!Schema::hasColumn('helps', 'schedule_confirmed_by_customer_at')) {
                $table->timestamp('schedule_confirmed_by_customer_at')->nullable()->after('schedule_reconfirmation_requested_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            if (Schema::hasColumn('helps', 'schedule_confirmed_by_customer_at')) {
                $table->dropColumn('schedule_confirmed_by_customer_at');
            }
            if (Schema::hasColumn('helps', 'schedule_reconfirmation_requested_at')) {
                $table->dropColumn('schedule_reconfirmation_requested_at');
            }
        });
    }
};
