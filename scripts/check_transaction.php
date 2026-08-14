<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BalanceTransaction;

$order = $argv[1] ?? null;
if (!$order) {
    echo "Usage: php scripts/check_transaction.php ORDER_ID\n";
    exit(1);
}

$t = BalanceTransaction::where('order_id', $order)->first();
if (!$t) {
    echo "Not found\n";
    exit(0);
}

$out = [
    'id' => $t->id,
    'user_id' => $t->user_id,
    'amount' => (string) $t->amount,
    'status' => $t->status,
    'payment_type' => $t->payment_type,
    'processed_at' => (string) $t->processed_at,
    'midtrans_response_present' => !empty($t->midtrans_response),
];

echo json_encode($out, JSON_PRETTY_PRINT) . "\n";
