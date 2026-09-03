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
        Schema::create('city_capacities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->unique()->constrained('cities')->cascadeOnDelete();
            $table->enum('capacity_status', ['open', 'limited', 'closed'])->default('open')->index();
            $table->integer('consecutive_closed_evaluations')->default(0);
            $table->integer('consecutive_open_evaluations')->default(0);
            $table->integer('current_unmatched_demand')->default(0);
            $table->integer('recent_request_volume_2h')->default(0);
            $table->integer('searching_now')->default(0);
            $table->integer('busy_now')->default(0);
            $table->integer('online_total')->default(0);
            $table->decimal('avg_waiting_minutes', 8, 2)->default(0);
            $table->integer('unserved_requests_24h')->default(0);
            $table->decimal('partner_utilization_rate', 5, 2)->default(0);
            $table->decimal('max_matching_radius_km', 5, 1)->nullable();
            $table->boolean('auto_manage')->default(true);
            $table->enum('admin_override_status', ['open', 'limited', 'closed'])->nullable();
            $table->timestamp('admin_override_until')->nullable();
            $table->text('admin_override_notes')->nullable();
            $table->integer('waiting_list_count')->default(0);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('city_capacities');
    }
};
