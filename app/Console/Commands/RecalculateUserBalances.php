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
                // Kredit (menambah saldo user): topup, earning, refund
                $credits = BalanceTransaction::where('user_id', $user->id)
                    ->whereIn('type', BalanceTransaction::creditTypes())
                    ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = 'completed'")
                    ->sum('amount');

                // Debit (mengurangi saldo user): deduction, penalty, escrow_lock, withdraw, pg_fee_*
                $debits = BalanceTransaction::where('user_id', $user->id)
                    ->whereIn('type', BalanceTransaction::debitTypes())
                    ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = 'completed'")
                    ->sum('amount');

                $balance = (float) $credits - (float) $debits;

                UserBalance::updateOrCreate(['user_id' => $user->id], ['balance' => $balance]);

                $bar->advance();
            }
        });

        $bar->finish();
        $this->info("\nRecalculation finished.");

        return 0;
    }
}
