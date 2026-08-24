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
        Schema::create('user_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('balance', 15, 2)->default(0.00)->index();
            $table->timestamps();
        });

        Schema::create('balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('order_id')->nullable()->index();
            $table->string('reference_id')->nullable()->index();
            $table->string('request_code')->nullable();
            $table->enum('type', ['topup', 'withdraw', 'payment', 'refund', 'service_fee', 'earning', 'deduction', 'penalty'])->index();
            $table->decimal('amount', 15, 2);
            $table->decimal('admin_fee', 15, 2)->default(0.00);
            $table->decimal('total_payment', 15, 2)->default(0.00);
            $table->string('payment_method')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->text('customer_notes')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('midtrans_transaction_id')->nullable()->index();
            $table->string('midtrans_payment_type')->nullable();
            $table->string('midtrans_fraud_status')->nullable();
            $table->json('midtrans_response')->nullable();
            $table->string('proof_of_payment')->nullable();
            $table->enum('manual_approval_status', ['pending', 'approved', 'rejected'])->nullable()->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('withdraw_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('bank_code');
            $table->string('account_number');
            $table->string('status')->default('pending')->index();
            $table->string('external_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraw_requests');
        Schema::dropIfExists('balance_transactions');
        Schema::dropIfExists('user_balances');
    }
};
