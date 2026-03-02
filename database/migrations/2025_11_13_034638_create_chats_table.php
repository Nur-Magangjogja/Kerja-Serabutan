<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('help_id');
            $table->unsignedBigInteger('mitra_id')->comment('User dengan role mitra');
            $table->unsignedBigInteger('customer_id')->comment('User dengan role customer/kustomer');
            $table->text('message');
            $table->string('sender_type')->comment('mitra atau customer'); // 'mitra' or 'customer'
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('help_id')->references('id')->on('helps')->onDelete('cascade');
            $table->foreign('mitra_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['help_id', 'created_at']);
            $table->index(['mitra_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
