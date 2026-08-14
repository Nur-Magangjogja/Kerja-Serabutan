<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('balance_transactions', function (Blueprint $table) {
            $table->string('snap_token')->nullable()->after('reference_id');
            $table->string('payment_type')->nullable()->after('snap_token');
            $table->string('order_id')->nullable()->after('payment_type');
            $table->text('midtrans_response')->nullable()->after('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('balance_transactions', function (Blueprint $table) {
            $table->dropColumn(['snap_token', 'payment_type', 'order_id', 'midtrans_response']);
        });
    }
};
