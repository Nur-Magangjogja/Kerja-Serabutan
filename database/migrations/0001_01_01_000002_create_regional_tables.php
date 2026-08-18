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
        Schema::create('reg_provinces', function (Blueprint $table) {
            $table->char('id', 2)->primary();
            $table->string('name');
        });

        Schema::create('reg_regencies', function (Blueprint $table) {
            $table->char('id', 4)->primary();
            $table->char('province_id', 2);
            $table->string('name');

            $table->foreign('province_id')->references('id')->on('reg_provinces')->onDelete('cascade');
        });

        Schema::create('reg_districts', function (Blueprint $table) {
            $table->char('id', 6)->primary();
            $table->char('regency_id', 4);
            $table->string('name');

            $table->foreign('regency_id')->references('id')->on('reg_regencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reg_districts');
        Schema::dropIfExists('reg_regencies');
        Schema::dropIfExists('reg_provinces');
    }
};
