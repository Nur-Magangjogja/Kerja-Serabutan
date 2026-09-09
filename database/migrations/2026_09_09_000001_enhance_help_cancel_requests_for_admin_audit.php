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
        Schema::table('help_cancel_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('help_cancel_requests', 'requester_type')) {
                $table->enum('requester_type', ['partner', 'customer'])->default('partner')->after('help_id');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('partner_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('help_cancel_requests', 'partner_clarification')) {
                $table->text('partner_clarification')->nullable()->after('evidence_photo');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'partner_clarification_photo')) {
                $table->string('partner_clarification_photo')->nullable()->after('partner_clarification');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'partner_clarified_at')) {
                $table->timestamp('partner_clarified_at')->nullable()->after('partner_clarification_photo');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'sp_target')) {
                $table->enum('sp_target', ['none', 'partner', 'customer', 'both'])->default('none')->after('admin_notes');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'partner_sp_level')) {
                $table->tinyInteger('partner_sp_level')->nullable()->after('sp_target');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'partner_sp_reason')) {
                $table->string('partner_sp_reason')->nullable()->after('partner_sp_level');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'customer_sp_level')) {
                $table->tinyInteger('customer_sp_level')->nullable()->after('partner_sp_reason');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'customer_sp_reason')) {
                $table->string('customer_sp_reason')->nullable()->after('customer_sp_level');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'audit_decision')) {
                $table->enum('audit_decision', ['valid_no_sp', 'penalty_issued', 'rejected'])->nullable()->after('customer_sp_reason');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('reviewed_at');
            }
        });

        Schema::table('helps', function (Blueprint $table) {
            if (!Schema::hasColumn('helps', 'cancel_requested_by')) {
                $table->enum('cancel_requested_by', ['partner', 'customer'])->nullable()->after('partner_cancel_requested_at');
            }
            if (!Schema::hasColumn('helps', 'cancel_deadline_at')) {
                $table->timestamp('cancel_deadline_at')->nullable()->after('cancel_requested_by');
            }
            if (!Schema::hasColumn('helps', 'cancel_evidence_photo')) {
                $table->string('cancel_evidence_photo')->nullable()->after('cancel_deadline_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('help_cancel_requests', function (Blueprint $table) {
            $table->dropColumn([
                'requester_type',
                'customer_id',
                'partner_clarification',
                'partner_clarification_photo',
                'partner_clarified_at',
                'sp_target',
                'partner_sp_level',
                'partner_sp_reason',
                'customer_sp_level',
                'customer_sp_reason',
                'audit_decision',
                'expires_at',
            ]);
        });

        Schema::table('helps', function (Blueprint $table) {
            $table->dropColumn([
                'cancel_requested_by',
                'cancel_deadline_at',
                'cancel_evidence_photo',
            ]);
        });
    }
};
