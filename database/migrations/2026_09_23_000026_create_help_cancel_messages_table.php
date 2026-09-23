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
        Schema::create('help_cancel_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_cancel_request_id')->constrained('help_cancel_requests')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->enum('recipient_type', ['all', 'customer', 'mitra', 'admin'])->default('all');
            $table->text('message')->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('customer_read_at')->nullable();
            $table->timestamp('mitra_read_at')->nullable();
            $table->timestamps();

            $table->index(['help_cancel_request_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_cancel_messages');
    }
};