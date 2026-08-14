<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menghapus tabel wilayah lama (provinces, regencies) yang sudah
 * digantikan oleh reg_provinces, reg_regencies, reg_districts.
 *
 * Tabel 'districts' TIDAK disentuh di sini — dikelola oleh:
 * → 2025_12_24_000001_create_districts_table (skema city_id)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('regencies');
        Schema::dropIfExists('provinces');
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->timestamps();
        });

        Schema::create('regencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->string('name');
            $table->string('code')->nullable();
            $table->timestamps();
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('set null');
        });
    }
};
