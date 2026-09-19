<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('partner_report_messages')) {
            Schema::table('partner_report_messages', function (Blueprint $table) {
                if (!Schema::hasColumn('partner_report_messages', 'customer_read_at')) {
                    $table->timestamp('customer_read_at')->nullable()->after('read_at');
                }
                if (!Schema::hasColumn('partner_report_messages', 'mitra_read_at')) {
                    $table->timestamp('mitra_read_at')->nullable()->after('customer_read_at');
                }
            });

            // Backfill existing data
            DB::table('partner_report_messages')
                ->where('is_read', true)
                ->update([
                    'customer_read_at' => DB::raw('COALESCE(read_at, created_at)'),
                    'mitra_read_at'    => DB::raw('COALESCE(read_at, created_at)'),
                ]);
        }

        if (Schema::hasTable('help_cancel_messages')) {
            Schema::table('help_cancel_messages', function (Blueprint $table) {
                if (!Schema::hasColumn('help_cancel_messages', 'customer_read_at')) {
                    $table->timestamp('customer_read_at')->nullable()->after('read_at');
                }
                if (!Schema::hasColumn('help_cancel_messages', 'mitra_read_at')) {
                    $table->timestamp('mitra_read_at')->nullable()->after('customer_read_at');
                }
            });

            // Backfill existing data
            DB::table('help_cancel_messages')
                ->where('is_read', true)
                ->update([
                    'customer_read_at' => DB::raw('COALESCE(read_at, created_at)'),
                    'mitra_read_at'    => DB::raw('COALESCE(read_at, created_at)'),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('partner_report_messages')) {
            Schema::table('partner_report_messages', function (Blueprint $table) {
                if (Schema::hasColumn('partner_report_messages', 'customer_read_at')) {
                    $table->dropColumn('customer_read_at');
                }
                if (Schema::hasColumn('partner_report_messages', 'mitra_read_at')) {
                    $table->dropColumn('mitra_read_at');
                }
            });
        }

        if (Schema::hasTable('help_cancel_messages')) {
            Schema::table('help_cancel_messages', function (Blueprint $table) {
                if (Schema::hasColumn('help_cancel_messages', 'customer_read_at')) {
                    $table->dropColumn('customer_read_at');
                }
                if (Schema::hasColumn('help_cancel_messages', 'mitra_read_at')) {
                    $table->dropColumn('mitra_read_at');
                }
            });
        }
    }
};
