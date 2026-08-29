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
            $table->string('escrow_status', 50)->default('uninitialized')->after('status')->index();
            $table->enum('payment_status', ['unpaid', 'paid', 'partially_refunded', 'refunded', 'failed'])->default('unpaid')->after('escrow_status')->index();
            $table->string('rating_status', 50)->default('pending')->after('payment_status')->index();
            $table->enum('dispatch_mode', ['seeking', 'offered', 'pool', 'assigned', 'closed'])->default('seeking')->after('rating_status')->index();

            $table->timestamp('confirmation_deadline_at')->nullable()->after('completed_at')->index();
            $table->timestamp('auto_confirmed_at')->nullable()->after('confirmation_deadline_at');
            $table->timestamp('assigned_at')->nullable()->after('auto_confirmed_at');
            $table->timestamp('pool_opened_at')->nullable()->after('assigned_at');

            // Dispute tracking
            $table->timestamp('disputed_at')->nullable()->after('pool_opened_at')->index();
            $table->text('dispute_reason')->nullable()->after('disputed_at');
            $table->timestamp('dispute_resolved_at')->nullable()->after('dispute_reason');
            $table->foreignId('dispute_resolved_by')->nullable()->after('dispute_resolved_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            $table->dropForeign(['dispute_resolved_by']);
            $table->dropColumn([
                'escrow_status',
                'payment_status',
                'rating_status',
                'dispatch_mode',
                'confirmation_deadline_at',
                'auto_confirmed_at',
                'assigned_at',
                'pool_opened_at',
                'disputed_at',
                'dispute_reason',
                'dispute_resolved_at',
                'dispute_resolved_by',
            ]);
        });
    }
};
