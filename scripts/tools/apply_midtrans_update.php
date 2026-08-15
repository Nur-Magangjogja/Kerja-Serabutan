<?php
// Script to mark a topup order as completed and recompute user balance
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$orderId = $argv[1] ?? 'TOPUP-3-1763707433';
$paymentType = $argv[2] ?? 'qris';
$midtransRaw = $argv[3] ?? json_encode(['transaction_status' => 'settlement', 'fraud_status' => 'accept', 'payment_type' => $paymentType, 'gross_amount' => '100000.00']);

$txn = DB::table('balance_transactions')->where('order_id', $orderId)->first();
if (!$txn) {
    echo "Transaction not found for order_id={$orderId}\n";
    exit(1);
}

DB::table('balance_transactions')
    ->where('order_id', $orderId)
    ->update([
        'status' => 'completed',
        'payment_type' => $paymentType,
        'midtrans_response' => $midtransRaw,
        'updated_at' => date('Y-m-d H:i:s'),
    ]);

// Recompute user balance
$sum = DB::table('balance_transactions')
    ->where('user_id', $txn->user_id)
    ->where('type', 'topup')
    ->whereRaw("LOWER(TRIM(status)) = 'completed'")
    ->sum('amount');

DB::table('user_balances')
    ->updateOrInsert(
        ['user_id' => $txn->user_id],
        ['balance' => $sum, 'updated_at' => date('Y-m-d H:i:s'), 'created_at' => date('Y-m-d H:i:s')]
    );

echo "Marked order {$orderId} as completed; user_id={$txn->user_id}; new_balance={$sum}\n";
