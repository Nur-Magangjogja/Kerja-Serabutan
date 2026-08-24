<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Help;
use App\Models\UserBalance;
use App\Models\BalanceTransaction;
use App\Models\City;
use App\Services\HelpTransactionService;
use Illuminate\Support\Facades\DB;

echo "=== STARTING VERIFICATION OF MODEL V2 ESCROW & SPLIT PAYMENT ===\n\n";

DB::beginTransaction();

try {
    // 1. Setup Test Users
    $city = City::first() ?? City::create(['name' => 'Kota Uji', 'province' => 'Jawa Barat', 'is_active' => true]);

    $customer = User::create([
        'name' => 'Test Customer V2',
        'email' => 'customer_v2_' . time() . '@test.com',
        'password' => bcrypt('password'),
        'role' => 'customer',
        'city_id' => $city->id,
    ]);

    $mitra = User::create([
        'name' => 'Test Mitra V2',
        'email' => 'mitra_v2_' . time() . '@test.com',
        'password' => bcrypt('password'),
        'role' => 'mitra',
        'city_id' => $city->id,
    ]);

    // Give customer Rp 100.000 initial balance via topup
    $custBal = UserBalance::create(['user_id' => $customer->id, 'balance' => 0]);
    $custBal->addBalance(100000, 'Topup Awal');
    $custBal->refresh();
    echo "1. Customer Initial Balance: Rp " . number_format($custBal->balance, 0) . " (Expected: 100.000)\n";
    assert($custBal->balance == 100000, "Customer initial balance mismatch");

    // 2. Customer creates Help with amount = Rp 50.000, 10% commission
    $taskAmount = 50000;
    $commissionRate = 10;
    $feeAmount = 5000;
    $mitraEarning = 45000;

    $help = Help::create([
        'user_id' => $customer->id,
        'order_id' => 'HELP-TEST-' . time(),
        'city_id' => $city->id,
        'title' => 'Bantu Bersihkan Halaman V2',
        'description' => 'Uji coba sistem escrow v2',
        'amount' => $taskAmount,
        'admin_fee' => 0,
        'total_amount' => $taskAmount,
        'status' => Help::STATUS_MENUNGGU_MITRA,
        'model_version' => 2,
        'platform_commission_rate' => $commissionRate,
        'platform_fee_amount' => $feeAmount,
        'mitra_earning' => $mitraEarning,
        'escrow_locked_at' => now(),
    ]);

    // Lock Escrow
    $escrowTx = $custBal->lockForEscrow($taskAmount, $help->id);
    $help->update(['escrow_transaction_id' => $escrowTx->id]);
    $custBal->refresh();

    echo "2. After Escrow Lock: Customer Balance = Rp " . number_format($custBal->balance, 0) . " (Expected: 50.000)\n";
    assert($custBal->balance == 50000, "Customer balance after escrow lock mismatch");
    assert($escrowTx->type === 'escrow_lock', "Escrow tx type mismatch");

    // 3. Mitra takes help
    $service = app(HelpTransactionService::class);
    $service->takeHelp($help, $mitra);
    $help->refresh();
    echo "3. Mitra Takes Help -> Status: " . $help->status . " (Expected: taken)\n";
    assert($help->status === Help::STATUS_TAKEN, "Status should be taken");

    // 4. Mitra goes on the way -> arrived -> in progress -> completion
    $service->markOnTheWay($help, $mitra);
    $help->refresh();
    $service->markArrived($help, $mitra);
    $help->refresh();
    $service->startService($help, $mitra);
    $help->refresh();
    $service->submitCompletion($help, $mitra, 'dummy_proof.jpg', 'Selesai rapi');
    $help->refresh();
    echo "4. Submit Completion -> Status: " . $help->status . " (Expected: waiting_customer_confirmation)\n";

    echo "Debug Before Confirm: model_version=" . var_export($help->model_version, true) . ", isV2=" . var_export($help->isV2Model(), true) . ", mitra_earning=" . var_export($help->mitra_earning, true) . "\n";

    // 5. Customer confirms completion -> Split Payment
    $service->customerConfirmCompletion($help, $customer);
    $help->refresh();

    $allTxs = BalanceTransaction::all();
    echo "\n--- ALL CREATED BALANCE TRANSACTIONS ---\n";
    foreach ($allTxs as $t) {
        echo "Tx #{$t->id}: user_id={$t->user_id}, type={$t->type}, amount={$t->amount}, desc={$t->description}\n";
    }
    echo "----------------------------------------\n\n";

    $mitraBal = UserBalance::where('user_id', $mitra->id)->first();
    echo "5. After Customer Confirmation:\n";
    echo "   - Help Status: " . $help->status . " (Expected: selesai)\n";
    echo "   - Mitra Balance (Net Earning): Rp " . number_format($mitraBal->balance, 0) . " (Expected: 45.000)\n";

    assert($help->status === Help::STATUS_SELESAI, "Status should be selesai");
    assert($mitraBal->balance == 45000, "Mitra balance mismatch, expected 45.000");

    $platformFeeTx = BalanceTransaction::where('reference_id', $help->id)->where('type', 'platform_fee')->first();
    echo "   - Platform Fee Transaction: Rp " . number_format($platformFeeTx->amount, 0) . " (Expected: 5.000)\n";
    assert($platformFeeTx !== null && $platformFeeTx->amount == 5000, "Platform fee tx mismatch");

    // 6. Test Refund Scenario on another task
    echo "\n--- TESTING REFUND SCENARIO (TASK CANCELLED 100% REFUND) ---\n";
    $helpRefund = Help::create([
        'user_id' => $customer->id,
        'order_id' => 'HELP-REFUND-' . time(),
        'city_id' => $city->id,
        'title' => 'Bantuan Dibatalkan',
        'description' => 'Test refund',
        'amount' => 30000,
        'admin_fee' => 0,
        'total_amount' => 30000,
        'status' => Help::STATUS_MENUNGGU_MITRA,
        'model_version' => 2,
        'platform_commission_rate' => 10,
        'platform_fee_amount' => 3000,
        'mitra_earning' => 27000,
    ]);

    $custBal->lockForEscrow(30000, $helpRefund->id);
    $custBal->refresh();
    echo "6a. Customer Balance after 2nd Escrow: Rp " . number_format($custBal->balance, 0) . " (Expected: 20.000)\n";
    assert($custBal->balance == 20000, "Customer balance after 2nd escrow mismatch");

    // Customer cancels before mitra
    $service->customerCancelHelp($helpRefund, $customer);
    $custBal->refresh();
    echo "6b. Customer Balance after 100% Refund: Rp " . number_format($custBal->balance, 0) . " (Expected: 50.000)\n";
    assert($custBal->balance == 50000, "Customer balance after refund mismatch");

    // 7. Test Mitra Cancellation + Penalty Scenario
    echo "\n--- TESTING MITRA CANCELLATION + PENALTY SCENARIO ---\n";
    $helpPenalty = Help::create([
        'user_id' => $customer->id,
        'order_id' => 'HELP-PENALTY-' . time(),
        'city_id' => $city->id,
        'title' => 'Bantuan Dibatalkan Oleh Mitra',
        'description' => 'Test mitra penalty',
        'amount' => 40000,
        'admin_fee' => 0,
        'total_amount' => 40000,
        'status' => Help::STATUS_MENUNGGU_MITRA,
        'model_version' => 2,
        'platform_commission_rate' => 10,
        'platform_fee_amount' => 4000,
        'mitra_earning' => 36000,
    ]);

    $custBal->lockForEscrow(40000, $helpPenalty->id);
    $custBal->refresh();
    echo "7a. Customer Balance after 3rd Escrow: Rp " . number_format($custBal->balance, 0) . " (Expected: 10.000)\n";
    assert($custBal->balance == 10000, "Customer balance after 3rd escrow mismatch");

    // Mitra takes help
    $service->takeHelp($helpPenalty, $mitra);
    $helpPenalty->refresh();

    // Mitra requests cancel
    $service->requestPartnerCancel($helpPenalty, $mitra, 'Motor mogok di jalan');
    $helpPenalty->refresh();
    echo "7b. Mitra Request Cancel -> Status: " . $helpPenalty->status . " (Expected: partner_cancel_requested)\n";

    // Customer accepts cancel -> Mitra penalized Rp 5.000 (from 45.000 -> 40.000), Escrow remains locked because Help is back to pool
    $service->customerAcceptCancel($helpPenalty, $customer);
    $custBal->refresh();
    $mitraBal->refresh();
    $helpPenalty->refresh();

    echo "7c. After Customer Accepts Cancel:\n";
    echo "   - Customer Balance (Escrow Still Held): Rp " . number_format($custBal->balance, 0) . " (Expected: 10.000)\n";
    echo "   - Mitra Balance (Penalized -5.000): Rp " . number_format($mitraBal->balance, 0) . " (Expected: 40.000)\n";
    echo "   - Help Status (Reset to pool): " . $helpPenalty->status . " (Expected: menunggu_mitra)\n";

    assert($custBal->balance == 10000, "Customer balance should still hold escrow while help is in pool");
    assert($mitraBal->balance == 40000, "Mitra balance after penalty mismatch");
    assert($helpPenalty->status === Help::STATUS_MENUNGGU_MITRA, "Help should be reset to pool");

    // 7d. Customer decides to cancel the help while it is in 'menunggu_mitra' -> 100% Escrow Refunded
    $service->customerCancelHelp($helpPenalty, $customer);
    $custBal->refresh();
    $helpPenalty->refresh();

    echo "7d. After Customer Cancels Help from Pool:\n";
    echo "   - Customer Balance (100% Refunded): Rp " . number_format($custBal->balance, 0) . " (Expected: 50.000)\n";
    echo "   - Help Status: " . $helpPenalty->status . " (Expected: dibatalkan)\n";

    assert($custBal->balance == 50000, "Customer balance after cancel should be refunded 100%");
    assert($helpPenalty->status === Help::STATUS_DIBATALKAN, "Help status should be dibatalkan");

    echo "\n=== ALL VERIFICATION CHECKS (INCLUDING ESCROW RETENTION & LATER REFUND) PASSED PERFECTLY! ===\n";

} catch (\Throwable $e) {
    echo "\n[ERROR] " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
} finally {
    // Rollback so no test dirty data stays in database
    DB::rollBack();
    echo "Database rollback executed cleanly.\n";
}
