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
        Schema::create('partner_online_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->enum('matching_status', ['offline', 'online', 'searching', 'offer_pending', 'busy'])->default('offline')->index();
            $table->foreignId('current_help_id')->nullable()->constrained('helps')->nullOnDelete();
            $table->unsignedTinyInteger('consecutive_declines')->default(0);
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('searching_since')->nullable()->index();
            $table->timestamp('last_completed_at')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();

            $table->index(['matching_status', 'last_seen_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_online_states');
    }
};
