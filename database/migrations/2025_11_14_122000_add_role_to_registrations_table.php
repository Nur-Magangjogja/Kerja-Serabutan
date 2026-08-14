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
        if (Schema::hasTable('registrations') && !Schema::hasColumn('registrations', 'role')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->enum('role', ['customer', 'mitra'])->default('customer')->after('selfie_photo_path');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('registrations') && Schema::hasColumn('registrations', 'role')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
