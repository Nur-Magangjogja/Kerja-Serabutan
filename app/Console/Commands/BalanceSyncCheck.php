<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BalanceTransaction;
use App\Models\UserBalance;
use Illuminate\Support\Facades\Log;

class BalanceSyncCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balances:sync-check {--threshold=1000000 : Maximum delta (in smallest currency unit) to auto-fix} {--fix : Force fix even when delta is large}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check user balances against completed topup transactions and auto-fix small mismatches.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = (int) $this->option('threshold');
        $forceFix = (bool) $this->option('fix');

        $this->info('Running balances sync check...');

        // Collect user ids from both tables
        $userIdsFromTx = BalanceTransaction::select('user_id')->distinct()->pluck('user_id')->toArray();
        $userIdsFromUb = UserBalance::select('user_id')->distinct()->pluck('user_id')->toArray();

        $userIds = array_unique(array_merge($userIdsFromTx, $userIdsFromUb));

        $fixed = 0;
        $issues = 0;

        foreach ($userIds as $uid) {
            try {
                $sum = (float) BalanceTransaction::where('user_id', $uid)
                    ->where('type', 'topup')
                    ->whereRaw("LOWER(TRIM(status)) = 'completed'")
                    ->sum('amount');

                $ub = UserBalance::where('user_id', $uid)->first();
                $current = $ub ? (float) $ub->balance : 0.0;

                if (abs($sum - $current) < 0.000001) {
                    continue; // OK
                }

                $delta = (float) ($sum - $current);

                if (abs($delta) <= $threshold || $forceFix) {
                    // Apply fix
                    UserBalance::updateOrCreate(
                        ['user_id' => $uid],
                        ['balance' => $sum]
                    );

                    Log::info('balances:sync-check fixed balance', ['user_id' => $uid, 'balance' => $sum, 'delta' => $delta]);
                    $this->line("Fixed user_id={$uid} delta={$delta}");
                    $fixed++;
                } else {
                    Log::warning('balances:sync-check detected large mismatch', ['user_id' => $uid, 'balance' => $current, 'expected' => $sum, 'delta' => $delta]);
                    $this->warn("Large mismatch user_id={$uid} delta={$delta} (skipped)");
                    $issues++;
                }
            } catch (\Throwable $e) {
                Log::error('balances:sync-check error processing user', ['user_id' => $uid, 'error' => $e->getMessage()]);
                $this->error("Error processing user {$uid}: " . $e->getMessage());
            }
        }

        $this->info("Done. Fixed={$fixed} issues={$issues}");
        return 0;
    }
}
