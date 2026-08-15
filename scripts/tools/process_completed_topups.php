<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BalanceTransaction;
use App\Models\UserBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "Looking for completed topup transactions that haven't been processed...\n";

$rows = BalanceTransaction::where('type', 'topup')
    ->where('status', 'completed')
    ->whereNull('processed_at')
    ->get();

if ($rows->isEmpty()) {
    echo "Nothing to process.\n";
    exit(0);
}

foreach ($rows as $tx) {
    DB::transaction(function () use ($tx) {
        $amount = (float) $tx->amount;
        if ($amount <= 0) {
            Log::warning('Skipping zero/negative amount', ['transaction_id' => $tx->id]);
            return;
        }

        $userBalance = UserBalance::firstOrCreate(
            ['user_id' => $tx->user_id],
            ['balance' => 0]
        );

        $userBalance->increment('balance', $amount);

        $tx->processed_at = now();
        $tx->save();

        Log::info('Processed completed topup via script', ['transaction_id' => $tx->id, 'user_id' => $tx->user_id, 'amount' => $amount]);
        echo "Processed transaction {$tx->id} for user {$tx->user_id} (+{$amount})\n";
    });
}

echo "Done.\n";
