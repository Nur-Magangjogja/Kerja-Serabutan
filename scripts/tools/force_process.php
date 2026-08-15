<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\BalanceTransaction;

$id = $argv[1] ?? null;
if (!$id) {
    echo "Usage: php scripts/force_process.php TXN_ID\n";
    exit(1);
}

// Force set to pending via DB to avoid firing observer
DB::table((new BalanceTransaction())->getTable())->where('id', $id)->update(['status' => 'pending', 'updated_at' => now()]);

// Load Eloquent and save to completed to trigger observer
$t = BalanceTransaction::find($id);
if (!$t) {
    echo "Txn not found\n";
    exit(1);
}

echo "Before: status={$t->status}\n";
$t->status = 'completed';
$t->save();

echo "After: status={$t->status}\n";
