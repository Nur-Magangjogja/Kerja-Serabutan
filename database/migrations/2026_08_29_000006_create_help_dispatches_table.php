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
        Schema::create('help_dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_id')->constrained('helps')->cascadeOnDelete();
            $table->foreignId('mitra_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('round')->default(1);
            $table->unsignedTinyInteger('rank');
            $table->enum('status', ['offered', 'accepted', 'rejected', 'expired'])->default('offered')->index();
            $table->timestamp('offered_at')->index();
            $table->timestamp('expires_at')->index();
            $table->timestamp('responded_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->json('score_snapshot')->nullable();
            $table->timestamps();

            // Constraint keunikan: 1 tawaran per rank per round, dan 1 mitra per round
            $table->unique(['help_id', 'round', 'rank'], 'help_dispatch_round_rank_unique');
            $table->unique(['help_id', 'round', 'mitra_id'], 'help_dispatch_round_mitra_unique');
            $table->index(['help_id', 'status']);
            $table->index(['mitra_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_dispatches');
    }
};
