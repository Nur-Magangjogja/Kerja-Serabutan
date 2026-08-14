<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$userId = $argv[1] ?? 3;
$txns = DB::table('balance_transactions')->where('user_id', $userId)->orderBy('created_at')->get();
echo "Transactions for user_id={$userId}\n";
foreach ($txns as $t) {
    echo "id={$t->id} order_id={$t->order_id} amount={$t->amount} type={$t->type} status={$t->status} processed_at={$t->processed_at} created_at={$t->created_at}\n";
}
$ub = DB::table('user_balances')->where('user_id', $userId)->first();
echo "\nUser balance record: ";
echo $ub ? json_encode($ub) : 'none';
echo "\n";
