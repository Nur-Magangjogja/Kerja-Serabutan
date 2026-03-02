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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin', 'kustomer', 'mitra'])->default('kustomer')->after('email');
            $table->foreignId('city_id')->nullable()->constrained()->onDelete('set null')->after('role');
            $table->string('ktp_path')->nullable()->after('city_id');
            $table->boolean('verified')->default(false)->after('ktp_path');
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('inactive')->after('verified');
            $table->string('phone')->nullable()->after('status');
            $table->text('address')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn(['role', 'city_id', 'ktp_path', 'verified', 'status', 'phone', 'address']);
        });
    }
};
