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
        Schema::create('help_cancel_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_id')->constrained('helps')->cascadeOnDelete();
            $table->enum('requester_type', ['partner', 'customer'])->default('partner');
            $table->enum('action_type', ['partner_incident', 'switch_partner', 'customer_withdraw'])->default('partner_incident');
            $table->enum('partner_response_type', ['pending', 'confirmed', 'rejected', 'expired'])->nullable();
            $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->string('previous_status', 50);
            $table->string('previous_stage', 50)->nullable();
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->string('evidence_photo')->nullable();
            $table->text('partner_clarification')->nullable();
            $table->text('partner_response_notes')->nullable();
            $table->string('partner_clarification_photo')->nullable();
            $table->string('partner_response_photo')->nullable();
            $table->timestamp('partner_clarified_at')->nullable();
            $table->timestamp('partner_responded_at')->nullable();

            // Financial & Progress State for State-Aware Settlement
            $table->boolean('item_purchased')->default(false);
            $table->decimal('item_purchase_amount', 12, 2)->default(0);
            $table->decimal('work_completed_percentage', 5, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->string('settlement_type', 50)->nullable();
            $table->decimal('refund_amount_customer', 12, 2)->default(0);
            $table->decimal('payout_amount_mitra', 12, 2)->default(0);

            // Canonical Distance & Compensation Telemetry
            $table->decimal('d_cancel_km', 8, 2)->nullable();
            $table->decimal('d_leg1_km', 8, 2)->nullable();
            $table->decimal('d_compensated_km', 8, 2)->nullable();
            $table->decimal('compensation_amount', 12, 2)->nullable();
            $table->decimal('refund_amount', 12, 2)->nullable();
            $table->string('cancellation_stage', 50)->nullable();
            $table->decimal('partner_start_lat', 10, 7)->nullable();
            $table->decimal('partner_start_lng', 10, 7)->nullable();
            $table->decimal('partner_cancel_lat', 10, 7)->nullable();
            $table->decimal('partner_cancel_lng', 10, 7)->nullable();
            $table->decimal('partner_moved_km', 8, 2)->nullable();
            $table->decimal('distance_to_target_km', 8, 2)->nullable();
            $table->integer('time_elapsed_minutes')->nullable();
            $table->integer('chat_messages_count')->default(0);
            $table->timestamp('partner_last_chat_at')->nullable();

            // SP Penalty & Admin Audit Decisions
            $table->enum('sp_target', ['none', 'partner', 'customer', 'both'])->default('none');
            $table->tinyInteger('partner_sp_level')->nullable();
            $table->string('partner_sp_reason')->nullable();
            $table->tinyInteger('customer_sp_level')->nullable();
            $table->string('customer_sp_reason')->nullable();
            $table->enum('audit_decision', ['valid_no_sp', 'penalty_issued', 'rejected'])->nullable();

            // Review Milestones
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['help_id', 'status']);
            $table->index(['partner_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['district_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_cancel_requests');
    }
};
