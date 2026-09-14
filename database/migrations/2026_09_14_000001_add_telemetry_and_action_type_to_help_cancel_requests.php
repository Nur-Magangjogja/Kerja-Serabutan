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
        Schema::table('help_cancel_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('help_cancel_requests', 'action_type')) {
                $table->enum('action_type', ['partner_incident', 'switch_partner', 'customer_withdraw'])->default('partner_incident')->after('requester_type');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'partner_response_type')) {
                $table->enum('partner_response_type', ['pending', 'confirmed', 'rejected', 'expired'])->nullable()->after('action_type');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'partner_response_notes')) {
                $table->text('partner_response_notes')->nullable()->after('partner_clarification');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'partner_response_photo')) {
                $table->string('partner_response_photo')->nullable()->after('partner_clarification_photo');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'partner_responded_at')) {
                $table->timestamp('partner_responded_at')->nullable()->after('partner_clarified_at');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'partner_start_lat')) {
                $table->decimal('partner_start_lat', 10, 7)->nullable()->after('cancellation_stage');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'partner_start_lng')) {
                $table->decimal('partner_start_lng', 10, 7)->nullable()->after('partner_start_lat');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'partner_cancel_lat')) {
                $table->decimal('partner_cancel_lat', 10, 7)->nullable()->after('partner_start_lng');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'partner_cancel_lng')) {
                $table->decimal('partner_cancel_lng', 10, 7)->nullable()->after('partner_cancel_lat');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'partner_moved_km')) {
                $table->decimal('partner_moved_km', 8, 2)->nullable()->after('partner_cancel_lng');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'distance_to_target_km')) {
                $table->decimal('distance_to_target_km', 8, 2)->nullable()->after('partner_moved_km');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'time_elapsed_minutes')) {
                $table->integer('time_elapsed_minutes')->nullable()->after('distance_to_target_km');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'chat_messages_count')) {
                $table->integer('chat_messages_count')->default(0)->after('time_elapsed_minutes');
            }
            if (!Schema::hasColumn('help_cancel_requests', 'partner_last_chat_at')) {
                $table->timestamp('partner_last_chat_at')->nullable()->after('chat_messages_count');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('help_cancel_requests', function (Blueprint $table) {
            $table->dropColumn([
                'action_type',
                'partner_response_type',
                'partner_response_notes',
                'partner_response_photo',
                'partner_responded_at',
                'partner_start_lat',
                'partner_start_lng',
                'partner_cancel_lat',
                'partner_cancel_lng',
                'partner_moved_km',
                'distance_to_target_km',
                'time_elapsed_minutes',
                'chat_messages_count',
                'partner_last_chat_at',
            ]);
        });
    }
};
