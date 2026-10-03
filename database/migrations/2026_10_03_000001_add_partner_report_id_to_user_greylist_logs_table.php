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
        Schema::table('user_greylist_logs', function (Blueprint $table) {
            $table->foreignId('partner_report_id')
                ->nullable()
                ->after('admin_id')
                ->constrained('partner_reports')
                ->nullOnDelete();

            $table->unique(
                'partner_report_id',
                'user_greylist_logs_partner_report_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_greylist_logs', function (Blueprint $table) {
            $table->dropUnique('user_greylist_logs_partner_report_unique');
            $table->dropForeign(['partner_report_id']);
            $table->dropColumn('partner_report_id');
        });
    }
};
