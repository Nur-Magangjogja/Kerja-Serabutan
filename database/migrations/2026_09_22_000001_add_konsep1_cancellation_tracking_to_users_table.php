<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('konsep1_cancel_count')->default(0)->after('latest_warning_at');
            $table->timestamp('konsep1_pardoned_at')->nullable()->after('konsep1_cancel_count');
            $table->text('konsep1_pardon_notes')->nullable()->after('konsep1_pardoned_at');
        });

        // Backfill data historis dari help_cancel_requests untuk pembatalan Konsep 1
        try {
            $historicalCounts = DB::table('help_cancel_requests')
                ->where('requester_type', 'partner')
                ->where(function ($q) {
                    $q->where('cancellation_stage', 'transit')
                      ->orWhere(function ($sub) {
                          $sub->where(function ($s) {
                              $s->whereNull('cancellation_stage')
                                ->orWhere('cancellation_stage', '!=', 'in_progress');
                          })->where(function ($s) {
                              $s->whereNull('previous_status')
                                ->orWhere('previous_status', '!=', 'in_progress');
                          });
                      });
                })
                ->selectRaw('partner_id, count(*) as total')
                ->groupBy('partner_id')
                ->get();

            foreach ($historicalCounts as $row) {
                if ($row->partner_id) {
                    DB::table('users')
                        ->where('id', $row->partner_id)
                        ->update(['konsep1_cancel_count' => (int) $row->total]);
                }
            }
        } catch (\Throwable $e) {
            // Abaikan jika tabel belum siap
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'konsep1_cancel_count',
                'konsep1_pardoned_at',
                'konsep1_pardon_notes',
            ]);
        });
    }
};
