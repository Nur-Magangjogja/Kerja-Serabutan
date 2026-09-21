<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan preferensi jenis bantuan yang ingin dicari (matching/radar)
     * oleh mitra: 'all', 'on_site_service', 'pickup_delivery'.
     */
    public function up(): void
    {
        Schema::table('partner_online_states', function (Blueprint $table) {
            $table->string('service_preference', 30)->default('all')->after('matching_status')
                ->comment('Preferensi jenis bantuan radar: all, on_site_service, pickup_delivery');
            $table->index('service_preference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_online_states', function (Blueprint $table) {
            $table->dropIndex(['service_preference']);
            $table->dropColumn('service_preference');
        });
    }
};
