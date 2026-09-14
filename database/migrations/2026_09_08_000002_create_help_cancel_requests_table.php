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
            $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->string('previous_status', 50);
            $table->string('previous_stage', 50)->nullable();
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->string('evidence_photo')->nullable();

            // Financial & Progress State for State-Aware Settlement
            $table->boolean('item_purchased')->default(false);
            $table->decimal('item_purchase_amount', 12, 2)->default(0);
            $table->decimal('work_completed_percentage', 5, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->enum('settlement_type', ['full_refund', 'partial_settlement', 'item_settled', 'no_refund'])->nullable();
            $table->decimal('refund_amount_customer', 12, 2)->default(0);
            $table->decimal('payout_amount_mitra', 12, 2)->default(0);

            // Review Milestones
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_notes')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['help_id', 'status']);
            $table->index(['partner_id', 'status']);
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
