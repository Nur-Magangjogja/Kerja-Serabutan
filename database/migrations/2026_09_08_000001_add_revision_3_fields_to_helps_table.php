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
            // Service Type & Sub-Stage
            $table->string('service_type', 50)->default('on_site_service')->after('equipment_provided');
            $table->string('service_stage', 50)->nullable()->after('service_type');
            $table->string('order_mode', 50)->default('instant')->after('service_stage');
            $table->string('service_category', 50)->nullable()->after('order_mode');
            $table->decimal('service_duration_hours', 4, 2)->default(1.0)->after('service_category');

            // Scheduling Milestones
            $table->timestamp('published_at')->nullable()->after('scheduled_at');
            $table->timestamp('departure_at')->nullable()->after('published_at');
            $table->timestamp('service_scheduled_at')->nullable()->after('departure_at');
            $table->timestamp('pickup_scheduled_at')->nullable()->after('service_scheduled_at');
            $table->timestamp('delivery_deadline_at')->nullable()->after('pickup_scheduled_at');

            // 3-Context Distances & Route Source
            $table->decimal('matching_distance_km', 8, 2)->default(0)->after('longitude');
            $table->decimal('travel_distance_km', 8, 2)->default(0)->after('matching_distance_km');
            $table->decimal('service_route_distance_km', 8, 2)->default(0)->after('travel_distance_km');
            $table->decimal('route_distance_km', 8, 2)->nullable()->after('service_route_distance_km');
            $table->string('route_source', 50)->default('fallback_estimation')->after('route_distance_km');
            $table->unsignedInteger('estimated_duration_minutes')->default(0)->after('route_source');
            $table->timestamp('estimated_arrival_at')->nullable()->after('estimated_duration_minutes');

            // Pricing Breakdown & Item Fund Isolation
            $table->decimal('service_fee', 12, 2)->default(0)->after('amount');
            $table->decimal('travel_fee', 12, 2)->default(0)->after('service_fee');
            $table->decimal('material_fee', 12, 2)->default(0)->after('travel_fee');
            $table->decimal('item_fund', 12, 2)->default(0)->after('material_fee');
            $table->string('item_fund_mode', 50)->nullable()->default('customer_paid_in_app')->after('item_fund');
            $table->string('customer_reimbursement_method', 50)->nullable()->default('cash')->after('item_fund_mode');
            $table->decimal('advance_limit', 12, 2)->default(0)->after('customer_reimbursement_method');
            $table->decimal('handling_fee', 12, 2)->default(0)->after('advance_limit');
            $table->decimal('minimum_service_fee', 12, 2)->default(0)->after('handling_fee');
            $table->decimal('minimum_order_value', 12, 2)->default(0)->after('minimum_service_fee');

            // Specialized Multi-Point Locations (Pickup, Delivery, Store)
            $table->decimal('pickup_latitude', 10, 7)->nullable()->after('full_address');
            $table->decimal('pickup_longitude', 10, 7)->nullable()->after('pickup_latitude');
            $table->text('pickup_address')->nullable()->after('pickup_longitude');
            $table->decimal('delivery_latitude', 10, 7)->nullable()->after('pickup_address');
            $table->decimal('delivery_longitude', 10, 7)->nullable()->after('delivery_latitude');
            $table->text('delivery_address')->nullable()->after('delivery_longitude');
            $table->string('store_name')->nullable()->after('delivery_address');
            $table->text('store_address')->nullable()->after('store_name');
            $table->decimal('store_latitude', 10, 7)->nullable()->after('store_address');
            $table->decimal('store_longitude', 10, 7)->nullable()->after('store_latitude');

            // High Precision GPS Tracking & Drift Filter
            $table->decimal('gps_accuracy', 8, 2)->nullable()->after('partner_current_lng');
            $table->timestamp('last_movement_at')->nullable()->after('partner_started_moving_at');
            $table->timestamp('arrived_at')->nullable()->after('partner_arrived_at');

            // Indexes for Performance
            $table->index('service_type');
            $table->index('service_stage');
            $table->index('order_mode');
            $table->index('departure_at');
            $table->index('service_scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            $table->dropIndex(['service_type']);
            $table->dropIndex(['service_stage']);
            $table->dropIndex(['order_mode']);
            $table->dropIndex(['departure_at']);
            $table->dropIndex(['service_scheduled_at']);

            $table->dropColumn([
                'service_type',
                'service_stage',
                'order_mode',
                'service_category',
                'service_duration_hours',
                'published_at',
                'departure_at',
                'service_scheduled_at',
                'pickup_scheduled_at',
                'delivery_deadline_at',
                'matching_distance_km',
                'travel_distance_km',
                'service_route_distance_km',
                'route_source',
                'estimated_duration_minutes',
                'estimated_arrival_at',
                'service_fee',
                'travel_fee',
                'material_fee',
                'item_fund',
                'item_fund_mode',
                'customer_reimbursement_method',
                'advance_limit',
                'handling_fee',
                'minimum_service_fee',
                'minimum_order_value',
                'pickup_latitude',
                'pickup_longitude',
                'pickup_address',
                'delivery_latitude',
                'delivery_longitude',
                'delivery_address',
                'store_name',
                'store_address',
                'store_latitude',
                'store_longitude',
                'gps_accuracy',
                'last_movement_at',
                'arrived_at',
            ]);
        });
    }
};
