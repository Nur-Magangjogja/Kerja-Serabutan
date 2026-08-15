<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\UserBalance;

$userId = $argv[1] ?? null;
if (!$userId) {
    echo "Usage: php scripts/check_balance.php USER_ID\n";
    exit(1);
}

$b = UserBalance::where('user_id', $userId)->first();
if (!$b) {
    echo "No user_balance row for user_id={$userId}\n";
    exit(0);
}

echo json_encode(['user_id' => $b->user_id, 'balance' => (string) $b->balance], JSON_PRETTY_PRINT) . "\n";
