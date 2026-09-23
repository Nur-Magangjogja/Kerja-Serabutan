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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('name');
            $table->string('type')->nullable(); // e.g. "Kota" or "Kabupaten"
            $table->string('province')->nullable();
            $table->char('province_id', 2)->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_matching_seeking_enabled')->nullable()->default(null)->comment('NULL = Ikuti Global, 1 = Aktifkan Antrean, 0 = Langsung ke Daftar Bantuan');
            $table->timestamps();

            $table->index('province_id');
            $table->index('admin_id');
            $table->index('is_matching_seeking_enabled', 'idx_cities_seeking_enabled');
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
        Schema::dropIfExists('cities');
    }
};
