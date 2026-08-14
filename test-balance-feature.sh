#!/bin/bash

# Test Balance Feature Implementation
# Run this script to verify all components are working correctly

echo "================================"
echo "Testing Balance Feature"
echo "================================"
echo ""

# Test 1: Check if migrations are complete
echo "[1] Checking database tables..."
php artisan tinker --execute "
use Illuminate\Support\Facades\DB;
echo 'user_balances table exists: ' . (DB::connection()->getSchemaBuilder()->hasTable('user_balances') ? 'YES' : 'NO') . PHP_EOL;
echo 'balance_transactions table exists: ' . (DB::connection()->getSchemaBuilder()->hasTable('balance_transactions') ? 'YES' : 'NO') . PHP_EOL;
exit();
"

echo ""
echo "[2] Checking UserBalance records..."
php artisan tinker --execute "
use App\Models\UserBalance;
echo 'Total UserBalance records: ' . UserBalance::count() . PHP_EOL;
exit();
"

echo ""
echo "[3] Checking Models..."
php artisan tinker --execute "
use App\Models\User;
use App\Models\UserBalance;
use App\Models\BalanceTransaction;

\$user = User::first();
if (\$user) {
    echo 'Testing with user: ' . \$user->name . PHP_EOL;
    echo 'User has balance: ' . (\$user->balance ? 'YES' : 'NO') . PHP_EOL;
    echo 'User balance amount: Rp ' . number_format(\$user->balance->balance ?? 0, 0, ',', '.') . PHP_EOL;
    echo 'User total transactions: ' . \$user->transactions->count() . PHP_EOL;
}
exit();
"

echo ""
echo "[4] Testing AddBalance functionality..."
php artisan tinker --execute "
use App\Models\User;
use App\Models\UserBalance;

\$user = User::find(1);
if (\$user) {
    \$balance = \$user->balance ?? \$user->balance()->create(['balance' => 0]);
    \$balance->addBalance(50000, 'Test Topup');
    
    echo 'Balance after adding 50000: Rp ' . number_format(\$balance->balance, 0, ',', '.') . PHP_EOL;
    echo 'Transaction created: ' . (\$user->transactions->where('amount', 50000)->exists() ? 'YES' : 'NO') . PHP_EOL;
}
exit();
"

echo ""
echo "================================"
echo "All tests completed!"
echo "================================"
