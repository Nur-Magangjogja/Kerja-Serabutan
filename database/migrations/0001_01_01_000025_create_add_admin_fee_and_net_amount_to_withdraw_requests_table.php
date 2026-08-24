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
        Schema::table('withdraw_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('withdraw_requests', 'admin_fee')) {
                $table->unsignedBigInteger('admin_fee')->default(0)->after('amount');
            }
            if (!Schema::hasColumn('withdraw_requests', 'net_amount')) {
                $table->unsignedBigInteger('net_amount')->default(0)->after('admin_fee');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdraw_requests', function (Blueprint $table) {
            if (Schema::hasColumn('withdraw_requests', 'net_amount')) {
                $table->dropColumn('net_amount');
            }
            if (Schema::hasColumn('withdraw_requests', 'admin_fee')) {
                $table->dropColumn('admin_fee');
            }
        });
    }
};
