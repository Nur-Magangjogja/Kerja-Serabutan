<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Make user_id nullable in balance_transactions
 *
 * Diperlukan agar transaksi tingkat kas platform (platform_fee) dan entitas holding
 * dapat dicatat dalam ledger double-entry tanpa harus terikat ke ID user tertentu.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Drop FK if exists, modify column, re-add FK with nullOnDelete
        Schema::table('balance_transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('balance_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('balance_transactions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('balance_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
