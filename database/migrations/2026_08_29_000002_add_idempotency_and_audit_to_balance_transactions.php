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
        Schema::table('balance_transactions', function (Blueprint $table) {
            $table->string('idempotency_key', 120)->nullable()->unique()->after('id');
            $table->string('reference_type', 50)->nullable()->after('reference_id')->index();
            $table->enum('direction', ['credit', 'debit'])->nullable()->after('amount')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('balance_transactions', function (Blueprint $table) {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn([
                'idempotency_key',
                'reference_type',
                'direction',
            ]);
        });
    }
};
