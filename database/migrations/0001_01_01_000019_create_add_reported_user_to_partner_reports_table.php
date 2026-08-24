<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('partner_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('partner_reports', 'reported_user_id')) {
                $table->foreignId('reported_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('partner_reports', 'reported_user_text')) {
                $table->string('reported_user_text')->nullable()->after('reported_help_text');
            }
        });

        try {
            if (Schema::hasColumn('partner_reports', 'reported_user_id')) {
                DB::statement('UPDATE partner_reports SET reported_user_id = COALESCE(user_id, reported_partner_id, reported_customer_id) WHERE reported_user_id IS NULL');
            }
            if (Schema::hasColumn('partner_reports', 'reported_user_text')) {
                DB::statement('UPDATE partner_reports SET reported_user_text = COALESCE(reported_partner_text, reported_customer_text) WHERE reported_user_text IS NULL');
            }
        } catch (\Throwable $e) {
            // Ignore if columns empty
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_reports', function (Blueprint $table) {
            if (Schema::hasColumn('partner_reports', 'reported_user_id')) {
                $table->dropForeign(['reported_user_id']);
                $table->dropColumn('reported_user_id');
            }
            if (Schema::hasColumn('partner_reports', 'reported_user_text')) {
                $table->dropColumn('reported_user_text');
            }
        });
    }
};
