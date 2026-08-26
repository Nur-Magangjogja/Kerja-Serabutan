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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_id')->constrained('helps')->cascadeOnDelete();
            $table->foreignId('rater_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('ratee_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['customer_to_mitra', 'mitra_to_customer'])->default('customer_to_mitra');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mitra_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('review')->nullable();
            $table->timestamps();

            $table->index('help_id');
            $table->index('rater_id');
            $table->index('ratee_id');
            $table->index('user_id');
            $table->index('mitra_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
