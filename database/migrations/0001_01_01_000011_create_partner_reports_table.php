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
        Schema::create('partner_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('help_id')->nullable()->constrained('helps')->nullOnDelete();
            $table->unsignedBigInteger('reported_partner_id')->nullable()->index();
            $table->unsignedBigInteger('reported_customer_id')->nullable()->index();
            $table->unsignedBigInteger('reported_help_id')->nullable()->index();
            $table->string('reported_partner_text')->nullable();
            $table->string('reported_customer_text')->nullable();
            $table->string('reported_help_text')->nullable();
            $table->string('report_type')->nullable();
            $table->string('category')->nullable();
            $table->string('title');
            $table->text('message');
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_reports');
    }
};
