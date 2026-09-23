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
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->string('ktp_path')->nullable();
            $table->string('ktp_photo')->nullable();
            $table->string('selfie_photo')->nullable();
            $table->string('profile_photo')->nullable();
            $table->boolean('verified')->default(false);
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('inactive');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->json('saved_landmarks')->nullable();
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

            // Data Identitas Kendaraan & Legalitas Dokumen (Mitra)
            $table->string('vehicle_plate_number', 20)->nullable()->comment('Nomor plat kendaraan mitra (contoh: AB 1234 CD)');
            $table->string('vehicle_sim_number', 50)->nullable()->comment('Nomor SIM C (motor)');
            $table->string('vehicle_sim_photo')->nullable()->comment('Path file foto SIM C');
            $table->string('vehicle_stnk_number', 50)->nullable()->comment('Nomor STNK');
            $table->string('vehicle_stnk_photo')->nullable()->comment('Path file foto STNK');
            $table->string('vehicle_brand', 50)->nullable()->comment('Merek kendaraan (opsional)');
            $table->string('vehicle_model', 50)->nullable()->comment('Tipe/model kendaraan (opsional)');
            $table->string('vehicle_color', 30)->nullable()->comment('Warna kendaraan (opsional)');
            $table->string('vehicle_verification_status', 20)->default('unsubmitted')->comment('Status verifikasi: unsubmitted, pending, verified, rejected');
            $table->boolean('vehicle_verified')->default(false)->comment('Apakah data kendaraan sudah disetujui admin');
            $table->timestamp('vehicle_verified_at')->nullable()->comment('Waktu data kendaraan disetujui');
            $table->foreignId('vehicle_verified_by')->nullable()->constrained('users')->nullOnDelete()->comment('Admin yang memverifikasi data kendaraan');
            $table->text('vehicle_rejection_reason')->nullable()->comment('Alasan penolakan jika verifikasi kendaraan ditolak');

            // Greylist & Shadow Ban System
            $table->boolean('is_greylisted')->default(false);
            $table->timestamp('greylisted_at')->nullable();
            $table->text('greylist_reason')->nullable();
            $table->boolean('is_shadow_banned')->default(false);
            $table->timestamp('shadow_banned_at')->nullable();
            $table->unsignedTinyInteger('warning_level')->default(0); // 0=normal, 1=SP1, 2=SP2, 3=SP3
            $table->text('latest_warning_message')->nullable();
            $table->timestamp('latest_warning_at')->nullable();

            // Konsep 1 Cancellation Tracking
            $table->unsignedInteger('konsep1_cancel_count')->default(0);
            $table->timestamp('konsep1_pardoned_at')->nullable();
            $table->text('konsep1_pardon_notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['role', 'status']);
            $table->index(['is_greylisted', 'is_shadow_banned']);
            $table->index('city_id');
            $table->index('district_id');
            $table->index(['role', 'district_id']);
            $table->index(['status', 'district_id']);
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