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
        Schema::create('helps', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->nullable()->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mitra_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('photo')->nullable();
            $table->string('proof_photo')->nullable();
            $table->text('completion_notes')->nullable();
            $table->string('location')->nullable();
            $table->text('full_address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // 3-Context Distances & Route Source
            $table->decimal('matching_distance_km', 8, 2)->default(0);
            $table->decimal('travel_distance_km', 8, 2)->default(0);
            $table->decimal('service_route_distance_km', 8, 2)->default(0);
            $table->decimal('estimated_road_distance_km', 8, 2)->nullable();
            $table->decimal('route_distance_km', 8, 2)->nullable();
            $table->string('route_source', 50)->default('fallback_estimation');
            $table->unsignedInteger('estimated_duration_minutes')->default(0);
            $table->timestamp('estimated_arrival_at')->nullable();

            // Pricing Breakdown & Canonical Pricing
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('service_fee', 12, 2)->default(0);
            $table->decimal('base_fare_applied', 12, 2)->nullable();
            $table->decimal('price_per_km_applied', 12, 2)->nullable();
            $table->decimal('platform_fee_applied', 12, 2)->nullable();
            $table->decimal('travel_fee', 12, 2)->default(0);
            $table->decimal('material_fee', 12, 2)->default(0);
            $table->decimal('item_fund', 12, 2)->default(0);
            $table->string('item_fund_mode', 50)->nullable()->default('customer_paid_in_app');
            $table->string('customer_reimbursement_method', 50)->nullable()->default('cash');
            $table->decimal('advance_limit', 12, 2)->default(0);
            $table->decimal('handling_fee', 12, 2)->default(0);
            $table->decimal('minimum_service_fee', 12, 2)->default(0);
            $table->decimal('minimum_order_value', 12, 2)->default(0);
            $table->decimal('admin_fee', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('booking_fee', 12, 2)->default(0);
            $table->string('voucher_code')->nullable();
            $table->string('equipment_provided')->nullable();

            // Service Type, Stages & Scheduling Milestones
            $table->string('service_type', 50)->default('on_site_service');
            $table->string('service_stage', 50)->nullable();
            $table->string('order_mode', 50)->default('instant');
            $table->string('service_category', 50)->nullable();
            $table->decimal('service_duration_hours', 4, 2)->default(1.0);
            $table->string('status')->default('menunggu_mitra');

            // Specialized Multi-Point Locations (Pickup, Delivery, Store)
            $table->decimal('pickup_latitude', 10, 7)->nullable();
            $table->decimal('pickup_longitude', 10, 7)->nullable();
            $table->text('pickup_address')->nullable();
            $table->decimal('delivery_latitude', 10, 7)->nullable();
            $table->decimal('delivery_longitude', 10, 7)->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('store_name')->nullable();
            $table->text('store_address')->nullable();
            $table->decimal('store_latitude', 10, 7)->nullable();
            $table->decimal('store_longitude', 10, 7)->nullable();

            // Escrow, Payment, Rating & Dispatch states
            $table->string('escrow_status', 50)->default('uninitialized')->index();
            $table->enum('payment_status', ['unpaid', 'paid', 'partially_refunded', 'refunded', 'failed'])->default('unpaid')->index();
            $table->string('rating_status', 50)->default('pending')->index();
            $table->enum('dispatch_mode', ['seeking', 'offered', 'pool', 'assigned', 'closed'])->default('seeking')->index();

            // Business & Commission Model (v2)
            $table->unsignedTinyInteger('model_version')->default(1);
            $table->decimal('platform_commission_rate', 5, 2)->default(0);
            $table->decimal('platform_fee_amount', 12, 2)->default(0);
            $table->decimal('mitra_earning', 12, 2)->default(0);
            $table->unsignedBigInteger('escrow_transaction_id')->nullable();
            $table->timestamp('escrow_locked_at')->nullable();

            $table->text('admin_notes')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('departure_at')->nullable();
            $table->timestamp('service_scheduled_at')->nullable();
            $table->timestamp('pickup_scheduled_at')->nullable();
            $table->timestamp('delivery_deadline_at')->nullable();
            $table->unsignedInteger('early_departure_minutes')->nullable();
            $table->timestamp('departure_reminder_sent_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Cancellation tracking
            $table->string('partner_cancel_reason')->nullable();
            $table->text('partner_cancel_notes')->nullable();
            $table->string('partner_cancel_prev_status')->nullable();
            $table->json('cancelled_mitra_ids')->nullable();
            $table->timestamp('partner_cancel_requested_at')->nullable();
            $table->enum('cancel_requested_by', ['partner', 'customer'])->nullable();
            $table->timestamp('cancel_deadline_at')->nullable();
            $table->string('cancel_evidence_photo')->nullable();

            // Mitra Location Tracking & Drift Filter
            $table->decimal('partner_latitude', 10, 7)->nullable();
            $table->decimal('partner_longitude', 10, 7)->nullable();
            $table->decimal('partner_initial_lat', 10, 7)->nullable();
            $table->decimal('partner_initial_lng', 10, 7)->nullable();
            $table->decimal('partner_current_lat', 10, 7)->nullable();
            $table->decimal('partner_current_lng', 10, 7)->nullable();
            $table->decimal('gps_accuracy', 8, 2)->nullable();
            $table->timestamp('partner_location_updated_at')->nullable();
            $table->timestamp('partner_started_moving_at')->nullable();
            $table->timestamp('last_movement_at')->nullable();
            $table->timestamp('partner_started_at')->nullable();
            $table->timestamp('partner_arrived_at')->nullable();
            $table->timestamp('arrived_at')->nullable();

            // Timestamp milestones
            $table->timestamp('mitra_assigned_at')->nullable();
            $table->timestamp('taken_at')->nullable();
            $table->timestamp('service_started_at')->nullable();
            $table->timestamp('service_completed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('confirmation_deadline_at')->nullable()->index();
            $table->timestamp('auto_confirmed_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('pool_opened_at')->nullable();

            // Dispute tracking
            $table->timestamp('disputed_at')->nullable()->index();
            $table->text('dispute_reason')->nullable();
            $table->timestamp('dispute_resolved_at')->nullable();
            $table->foreignId('dispute_resolved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Performance Indexes
            $table->index('order_id');
            $table->index('status');
            $table->index('user_id');
            $table->index('mitra_id');
            $table->index('city_id');
            $table->index('district_id');
            $table->index(['status', 'city_id']);
            $table->index(['status', 'district_id']);
            $table->index(['mitra_id', 'status']);
            $table->index(['user_id', 'status']);
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
        Schema::dropIfExists('helps');
    }
};
