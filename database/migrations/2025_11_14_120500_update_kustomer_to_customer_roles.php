<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update users table: first ensure enum includes 'customer'
        if (Schema::hasTable('users')) {
            // Modify enum to include 'customer' (MySQL)
            try {
                DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('super_admin','admin','kustomer','mitra','customer') NOT NULL DEFAULT 'customer';");
            } catch (\Exception $e) {
                // If altering fails (non-MySQL or permission), proceed to attempt update anyway
            }

            DB::table('users')
                ->where('role', 'kustomer')
                ->update(['role' => 'customer']);
        }

        // Update registrations table if the column exists
        if (Schema::hasTable('registrations') && Schema::hasColumn('registrations', 'role')) {
            DB::table('registrations')
                ->where('role', 'kustomer')
                ->update(['role' => 'customer']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('users')) {
            DB::table('users')
                ->where('role', 'customer')
                ->update(['role' => 'kustomer']);

            // Revert enum to previous values (without 'customer')
            try {
                DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('super_admin','admin','kustomer','mitra') NOT NULL DEFAULT 'kustomer';");
            } catch (\Exception $e) {
                // ignore
            }
        }

        if (Schema::hasTable('registrations') && Schema::hasColumn('registrations', 'role')) {
            DB::table('registrations')
                ->where('role', 'customer')
                ->update(['role' => 'kustomer']);
        }
    }
};
