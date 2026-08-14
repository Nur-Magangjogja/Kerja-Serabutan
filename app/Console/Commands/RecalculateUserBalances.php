<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\BalanceTransaction;

class RecalculateUserBalances extends Command
{
    protected $signature = 'userbalances:recalculate {--user=* : Optional user id(s) to limit recalculation}';
    protected $description = 'Recalculate all user balances from completed balance_transactions';

    public function handle()
    {
        $userIds = $this->option('user');

        $query = User::query();
        if (!empty($userIds)) {
            $query->whereIn('id', $userIds);
        }

        $bar = $this->output->createProgressBar($query->count());
        $bar->start();

        $query->chunkById(200, function ($users) use ($bar) {
            foreach ($users as $user) {
                $topups = BalanceTransaction::where('user_id', $user->id)
                    ->where('type', 'topup')
                    ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = 'completed'")
                    ->sum('amount');

                $deductions = BalanceTransaction::where('user_id', $user->id)
                    ->where('type', 'deduction')
                    ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = 'completed'")
                    ->sum('amount');

                $balance = $topups - $deductions;

                UserBalance::updateOrCreate(['user_id' => $user->id], ['balance' => $balance]);

                $bar->advance();
            }
        });

        $bar->finish();
        $this->info("\nRecalculation finished.");

        return 0;
    }
}
