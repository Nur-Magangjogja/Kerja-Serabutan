<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixBalanceStatuses extends Command
{
    protected $signature = 'balances:fix-statuses';
    protected $description = 'Fix common typos in balance_transactions.status (e.g. panding -> pending)';

    public function handle()
    {
        $this->info('Scanning and fixing balance_transactions.status typos...');

        $mapping = [
            'panding' => 'pending',
            'pendding' => 'pending',
            'pendng' => 'pending',
            'complate' => 'completed',
            'compleatd' => 'completed',
            'complted' => 'completed',
        ];

        foreach ($mapping as $wrong => $correct) {
            $count = DB::table('balance_transactions')
                ->whereRaw("LOWER(TRIM(status)) = ?", [$wrong])
                ->count();

            if ($count > 0) {
                DB::table('balance_transactions')
                    ->whereRaw("LOWER(TRIM(status)) = ?", [$wrong])
                    ->update(['status' => $correct, 'updated_at' => now()]);

                $this->info("Updated {$count} rows: '{$wrong}' -> '{$correct}'");
            }
        }

        $this->info('Done.');
        return 0;
    }
}
