<?php

namespace App\Console\Commands;

use App\Models\Help;
use App\Services\HelpTransactionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoConfirmHelps extends Command
{
    protected $signature = 'helps:auto-confirm';
    protected $description = 'Auto-confirm helps that waited for customer confirmation past their 24-hour deadline and release escrow funds.';

    public function handle(HelpTransactionService $transactionService): int
    {
        $this->info("Scanning for helps ready to be auto-confirmed (24-hour deadline passed)...");

        $totalConfirmed = 0;
        $totalFailed    = 0;

        Help::where('status', Help::STATUS_WAITING_CONFIRMATION)
            ->where('escrow_status', Help::ESCROW_STATUS_HELD)
            ->whereNotNull('confirmation_deadline_at')
            ->where('confirmation_deadline_at', '<=', now())
            ->chunkById(100, function ($helps) use ($transactionService, &$totalConfirmed, &$totalFailed) {
                foreach ($helps as $help) {
                    try {
                        $success = $transactionService->autoConfirmExpiredConfirmation($help);
                        if ($success) {
                            $totalConfirmed++;
                            $this->info("  [✓] Auto-confirmed & released escrow for Help #{$help->id} (Order: {$help->order_id})");
                        } else {
                            $this->line("  [-] Skipped Help #{$help->id} (Preconditions not met in lock)");
                        }
                    } catch (\Throwable $e) {
                        $totalFailed++;
                        $this->error("  [✗] Failed to auto-confirm Help #{$help->id}: " . $e->getMessage());
                        Log::error("[AutoConfirmHelps] Error processing Help #{$help->id}: " . $e->getMessage(), [
                            'exception' => $e,
                        ]);
                    }
                }
            });

        $this->info("Completed: {$totalConfirmed} confirmed, {$totalFailed} errors.");

        return 0;
    }
}
