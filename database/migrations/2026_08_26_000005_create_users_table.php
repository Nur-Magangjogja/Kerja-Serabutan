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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('nik', 16)->nullable();
            $table->string('place_of_birth', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Laki-laki', 'Perempuan'])->nullable();
            $table->enum('role', ['super_admin', 'admin', 'kustomer', 'mitra', 'customer'])->default('customer');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->unsignedBigInteger('balance')->default(0);
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('ktp_path')->nullable();
            $table->string('ktp_photo')->nullable();
            $table->string('selfie_photo')->nullable();
            $table->boolean('verified')->default(false);
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('inactive');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->unsignedInteger('rt')->nullable();
            $table->unsignedInteger('rw')->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('religion', 50)->nullable();
            $table->enum('marital_status', ['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'])->nullable();
            $table->string('occupation', 100)->nullable();
            $table->json('notification_settings')->nullable();

            // Greylist & Shadow Ban System
            $table->boolean('is_greylisted')->default(false);
            $table->timestamp('greylisted_at')->nullable();
            $table->text('greylist_reason')->nullable();
            $table->boolean('is_shadow_banned')->default(false);
            $table->timestamp('shadow_banned_at')->nullable();
            $table->unsignedTinyInteger('warning_level')->default(0); // 0=normal, 1=SP1, 2=SP2, 3=SP3
            $table->text('latest_warning_message')->nullable();
            $table->timestamp('latest_warning_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['role', 'status']);
            $table->index(['is_greylisted', 'is_shadow_banned']);
            $table->index('city_id');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
