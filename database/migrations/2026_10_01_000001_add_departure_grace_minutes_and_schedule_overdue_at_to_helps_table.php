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
            if (!Schema::hasColumn('helps', 'departure_grace_minutes')) {
                $table->unsignedSmallInteger('departure_grace_minutes')->nullable()->after('early_departure_minutes');
            }
            if (!Schema::hasColumn('helps', 'schedule_overdue_at')) {
                $table->timestamp('schedule_overdue_at')->nullable()->after('departure_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            if (Schema::hasColumn('helps', 'departure_grace_minutes')) {
                $table->dropColumn('departure_grace_minutes');
            }
            if (Schema::hasColumn('helps', 'schedule_overdue_at')) {
                $table->dropColumn('schedule_overdue_at');
            }
        });
    }
};
