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
        Schema::table('helps', function (Blueprint $table) {
            // Drop foreign key and column category_id
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');

            // Add amount column
            $table->decimal('amount', 15, 2)->after('title')->comment('Nominal uang untuk mitra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            // Remove amount column
            $table->dropColumn('amount');

            // Add back category_id
            $table->foreignId('category_id')->after('user_id')->constrained()->onDelete('cascade');
        });
    }
};
