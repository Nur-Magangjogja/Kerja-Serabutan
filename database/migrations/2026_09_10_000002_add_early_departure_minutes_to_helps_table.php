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
            $table->unsignedInteger('early_departure_minutes')->nullable()->after('delivery_deadline_at');
            $table->timestamp('departure_reminder_sent_at')->nullable()->after('early_departure_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            $table->dropColumn([
                'early_departure_minutes',
                'departure_reminder_sent_at',
            ]);
        });
    }
};
