<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking balance sync between balance_transactions (completed) and user_balances\n\n";

$rows = DB::table('balance_transactions')
    ->select('user_id', DB::raw("SUM(CASE WHEN type='topup' THEN amount ELSE 0 END) as topups"), DB::raw("SUM(CASE WHEN type='deduction' THEN amount ELSE 0 END) as deductions"))
    ->whereRaw("LOWER(TRIM(COALESCE(status,''))) = 'completed'")
    ->groupBy('user_id')
    ->get();

$diffs = [];
foreach ($rows as $r) {
    $userId = $r->user_id;
    $expected = (float) $r->topups - (float) $r->deductions;
    $ub = DB::table('user_balances')->where('user_id', $userId)->first();
    $actual = $ub ? (float) $ub->balance : 0.0;
    if (abs($expected - $actual) > 0.001) {
        $diffs[] = ['user_id' => $userId, 'expected' => $expected, 'actual' => $actual];
    }
}

if (empty($diffs)) {
    echo "All balances are synchronized.\n";
    exit(0);
}

echo "Found " . count($diffs) . " mismatches:\n";
foreach ($diffs as $d) {
    echo "user_id={$d['user_id']} expected={$d['expected']} actual={$d['actual']}\n";
}

exit(0);
