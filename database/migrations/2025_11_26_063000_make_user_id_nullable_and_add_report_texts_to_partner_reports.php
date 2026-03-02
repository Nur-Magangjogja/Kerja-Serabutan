<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('partner_reports', function (Blueprint $table) {
            // drop foreign key to allow altering column
            $table->dropForeign(['user_id']);
        });

        // make user_id nullable using raw SQL (portable for MySQL)
        DB::statement('ALTER TABLE `partner_reports` MODIFY `user_id` BIGINT UNSIGNED NULL');

        Schema::table('partner_reports', function (Blueprint $table) {
            // re-create foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // add free-text fields for reported help/customer
            $table->string('reported_help_text')->nullable()->after('reported_help_id');
            $table->string('reported_user_text')->nullable()->after('reported_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partner_reports', function (Blueprint $table) {
            $table->dropColumn(['reported_help_text', 'reported_user_text']);
            $table->dropForeign(['user_id']);
        });

        // make user_id NOT NULL again
        DB::statement('ALTER TABLE `partner_reports` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('partner_reports', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
