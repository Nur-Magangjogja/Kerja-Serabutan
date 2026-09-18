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
        Schema::table('chats', function (Blueprint $table) {
            if (!Schema::hasColumn('chats', 'sender_id')) {
                $table->foreignId('sender_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            // Ubah sender_type menjadi string(30)
            $table->string('sender_type', 30)->default('customer')->change();
        });

        // Backfill data lama yang sudah ada di database
        try {
            DB::table('chats')
                ->where('sender_type', 'customer')
                ->whereNull('sender_id')
                ->update(['sender_id' => DB::raw('customer_id')]);

            DB::table('chats')
                ->where('sender_type', 'mitra')
                ->whereNull('sender_id')
                ->update(['sender_id' => DB::raw('mitra_id')]);

            DB::table('chats')
                ->where('sender_type', 'system')
                ->update(['sender_id' => null]);
        } catch (\Throwable $e) {
            // Ignored on empty table or seed
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            if (Schema::hasColumn('chats', 'sender_id')) {
                $table->dropConstrainedForeignId('sender_id');
            }
            $table->enum('sender_type', ['mitra', 'customer', 'system'])->default('customer')->change();
        });
    }
};
