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
            if (!Schema::hasColumn('helps', 'base_fare_applied')) {
                $table->decimal('base_fare_applied', 12, 2)->nullable()->after('service_fee');
            }
            if (!Schema::hasColumn('helps', 'price_per_km_applied')) {
                $table->decimal('price_per_km_applied', 12, 2)->nullable()->after('base_fare_applied');
            }
            if (!Schema::hasColumn('helps', 'platform_fee_applied')) {
                $table->decimal('platform_fee_applied', 12, 2)->nullable()->after('price_per_km_applied');
            }
            if (!Schema::hasColumn('helps', 'estimated_road_distance_km')) {
                $table->decimal('estimated_road_distance_km', 8, 2)->nullable()->after('service_route_distance_km');
            }
        });

        Schema::table('help_cancel_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('help_cancel_requests', 'd_cancel_km')) {
                $table->decimal('d_cancel_km', 8, 2)->nullable()->after('help_id');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'd_leg1_km')) {
                $table->decimal('d_leg1_km', 8, 2)->nullable()->after('d_cancel_km');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'd_compensated_km')) {
                $table->decimal('d_compensated_km', 8, 2)->nullable()->after('d_leg1_km');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'compensation_amount')) {
                $table->decimal('compensation_amount', 12, 2)->nullable()->after('d_compensated_km');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'refund_amount')) {
                $table->decimal('refund_amount', 12, 2)->nullable()->after('compensation_amount');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'cancellation_stage')) {
                $table->string('cancellation_stage', 50)->nullable()->after('refund_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            $table->dropColumn([
                'base_fare_applied',
                'price_per_km_applied',
                'platform_fee_applied',
                'estimated_road_distance_km',
            ]);
        });

        Schema::table('help_cancel_requests', function (Blueprint $table) {
            $table->dropColumn([
                'd_cancel_km',
                'd_leg1_km',
                'd_compensated_km',
                'compensation_amount',
                'refund_amount',
                'cancellation_stage',
            ]);
        });
    }
};
