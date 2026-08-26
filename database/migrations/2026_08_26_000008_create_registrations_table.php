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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('nik', 16)->nullable();
            $table->string('full_name')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['Laki-laki', 'Perempuan'])->nullable();
            $table->text('address')->nullable();
            $table->unsignedInteger('rt')->nullable();
            $table->unsignedInteger('rw')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('province')->nullable();
            $table->string('religion')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('occupation')->nullable();
            $table->string('ktp_photo_path')->nullable();
            $table->string('selfie_photo_path')->nullable();
            $table->enum('role', ['customer', 'mitra'])->default('customer');
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->enum('status', ['in_progress', 'pending_verification', 'approved', 'rejected', 'completed', 'cancelled'])->default('in_progress');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
