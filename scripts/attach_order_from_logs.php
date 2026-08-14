<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BalanceTransaction;
use Illuminate\Support\Facades\DB;

$log = __DIR__ . '/../storage/logs/laravel.log';
if (!file_exists($log)) {
    echo "Log file not found: $log\n";
    exit(1);
}

$handle = fopen($log, 'r');
if (!$handle) {
    echo "Failed to open log file\n";
    exit(1);
}

$updated = 0;
$linesProcessed = 0;
while (($line = fgets($handle)) !== false) {
    $linesProcessed++;
    if (strpos($line, 'Midtrans Request') === false)
        continue;

    // Example log line prefix: [2025-11-21 02:14:25] local.INFO: Midtrans Request {"server_key_prefix":"...","is_production":false,"params":{"transaction_details":{"order_id":"TOPUP-3-1763691265","gross_amount":25000},...}}
    // Extract timestamp
    if (preg_match('/^\[(.*?)\]/', $line, $m)) {
        $tsStr = $m[1];
        $ts = strtotime($tsStr);
    } else {
        $ts = null;
    }

    // Extract order_id and gross_amount via regex
    $orderId = null;
    $gross = null;
    if (preg_match('/"order_id"\s*:\s*"([^"]+)"/', $line, $mo)) {
        $orderId = $mo[1];
    }
    if (preg_match('/"gross_amount"\s*:\s*(\d+(?:\.\d+)?)/', $line, $mg)) {
        $gross = $mg[1];
    }

    if (!$orderId || !$gross)
        continue;

    // parse user id from order id like TOPUP-{userId}-{ts}
    if (preg_match('/^TOPUP-(\d+)-/', $orderId, $mu)) {
        $userId = (int) $mu[1];
    } else {
        $userId = null;
    }

    // Find pending transaction with null order_id for this user, amount and time window
    $from = $ts ? date('Y-m-d H:i:s', max(0, $ts - 300)) : null;
    $to = $ts ? date('Y-m-d H:i:s', $ts + 300) : null;

    $qb = BalanceTransaction::where('type', 'topup')
        ->where('status', 'pending')
        ->whereNull('order_id');
    if ($userId)
        $qb->where('user_id', $userId);
    if ($from && $to)
        $qb->whereBetween('created_at', [$from, $to]);
    $qb->where('amount', (float) $gross);

    $candidate = $qb->orderByDesc('created_at')->first();
    if ($candidate) {
        try {
            DB::table((new BalanceTransaction())->getTable())->where('id', $candidate->id)->update([
                'order_id' => $orderId,
                'updated_at' => now(),
            ]);
            echo "Attached order_id {$orderId} to txn id={$candidate->id} (user={$candidate->user_id})\n";
            $updated++;
        } catch (\Throwable $e) {
            echo "Failed to attach for order {$orderId} -> {$e->getMessage()}\n";
        }
    }
}

fclose($handle);

echo "Done. Lines processed: {$linesProcessed}. Updated: {$updated}\n";
