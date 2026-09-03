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
            $table->string('title');
            $table->text('description');
            $table->string('photo')->nullable();
            $table->string('proof_photo')->nullable();
            $table->text('completion_notes')->nullable();
            $table->string('location')->nullable();
            $table->text('full_address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('admin_fee', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('booking_fee', 12, 2)->default(0);
            $table->string('voucher_code')->nullable();
            $table->string('equipment_provided')->nullable();
            $table->string('status')->default('menunggu_mitra');

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
            $table->timestamp('expires_at')->nullable();

            // Cancellation tracking
            $table->string('partner_cancel_reason')->nullable();
            $table->text('partner_cancel_notes')->nullable();
            $table->string('partner_cancel_prev_status')->nullable();
            $table->json('cancelled_mitra_ids')->nullable();
            $table->timestamp('partner_cancel_requested_at')->nullable();

            // Mitra Location Tracking
            $table->decimal('partner_latitude', 10, 7)->nullable();
            $table->decimal('partner_longitude', 10, 7)->nullable();
            $table->decimal('partner_initial_lat', 10, 7)->nullable();
            $table->decimal('partner_initial_lng', 10, 7)->nullable();
            $table->decimal('partner_current_lat', 10, 7)->nullable();
            $table->decimal('partner_current_lng', 10, 7)->nullable();
            $table->timestamp('partner_location_updated_at')->nullable();
            $table->timestamp('partner_started_moving_at')->nullable();
            $table->timestamp('partner_started_at')->nullable();
            $table->timestamp('partner_arrived_at')->nullable();

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
            $table->index(['status', 'city_id']);
            $table->index(['mitra_id', 'status']);
            $table->index(['user_id', 'status']);
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
