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
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
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
            $table->text('admin_notes')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('partner_cancel_reason')->nullable();
            $table->text('partner_cancel_notes')->nullable();
            $table->string('partner_cancel_prev_status')->nullable();
            $table->json('cancelled_mitra_ids')->nullable();
            $table->timestamp('partner_cancel_requested_at')->nullable();
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
            $table->timestamp('mitra_assigned_at')->nullable();
            $table->timestamp('taken_at')->nullable();
            $table->timestamp('service_started_at')->nullable();
            $table->timestamp('service_completed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
            $table->index('user_id');
            $table->index('mitra_id');
            $table->index('city_id');
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
