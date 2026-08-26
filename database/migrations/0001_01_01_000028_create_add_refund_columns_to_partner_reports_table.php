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
        Schema::table('partner_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('partner_reports', 'evidence_photo')) {
                $table->string('evidence_photo')->nullable()->after('message');
            }
            if (!Schema::hasColumn('partner_reports', 'refund_status')) {
                $table->string('refund_status')->default('none')->after('status')->index();
            }
            if (!Schema::hasColumn('partner_reports', 'refund_amount')) {
                $table->decimal('refund_amount', 12, 2)->nullable()->after('refund_status');
            }
            if (!Schema::hasColumn('partner_reports', 'refund_processed_at')) {
                $table->timestamp('refund_processed_at')->nullable()->after('refund_amount');
            }
            if (!Schema::hasColumn('partner_reports', 'refund_processed_by')) {
                $table->foreignId('refund_processed_by')->nullable()->after('refund_processed_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_reports', function (Blueprint $table) {
            if (Schema::hasColumn('partner_reports', 'refund_processed_by')) {
                $table->dropConstrainedForeignId('refund_processed_by');
            }
            $columns = ['evidence_photo', 'refund_status', 'refund_amount', 'refund_processed_at'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('partner_reports', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
