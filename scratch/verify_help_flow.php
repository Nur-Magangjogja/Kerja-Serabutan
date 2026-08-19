<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Help;
use App\Models\City;
use App\Models\UserBalance;
use App\Livewire\Customer\Helps\Detail as CustomerHelpDetail;
use App\Livewire\Customer\Helps\Index as CustomerHelpIndex;
use App\Livewire\Mitra\Helps\HelpDetail as MitraHelpDetail;
use Livewire\Livewire;

echo "=== MEMULAI TEST VERIFIKASI ALUR REALTIME & PEMBATALAN BANTUAN ===\n";

// 1. Setup Customer & Mitra
$customer = User::where('role', 'customer')->first();
if (!$customer) {
    $customer = User::create([
        'name' => 'Test Customer',
        'email' => 'test_customer_' . time() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'customer',
        'phone' => '081234567890',
        'status' => 'active',
    ]);
}

$mitra = User::where('role', 'mitra')->first();
if (!$mitra) {
    $mitra = User::create([
        'name' => 'Test Mitra',
        'email' => 'test_mitra_' . time() . '@example.com',
        'password' => bcrypt('password'),
        'role' => 'mitra',
        'phone' => '081234567891',
        'status' => 'active',
    ]);
}

$city = City::first();

echo "Customer: {$customer->name} (ID: {$customer->id})\n";
echo "Mitra: {$mitra->name} (ID: {$mitra->id})\n";

// 2. Test Create Help by Customer
auth()->login($customer);
$help = Help::create([
    'user_id' => $customer->id,
    'order_id' => 'HELP-' . time(),
    'city_id' => $city?->id,
    'title' => 'Test Bantuan Perbaikan AC',
    'amount' => 50000,
    'admin_fee' => 2000,
    'total_amount' => 52000,
    'description' => 'AC tidak dingin, mohon dicek freonnya.',
    'status' => 'menunggu_mitra',
]);

echo "\n[1] Bantuan dibuat oleh customer -> ID: {$help->id}, Status: {$help->status}\n";
assert($help->status === 'menunggu_mitra', 'Status awal harus menunggu_mitra');

// 3. Test Take Help by Mitra
auth()->login($mitra);
$help->update([
    'mitra_id' => $mitra->id,
    'status' => 'taken',
    'taken_at' => now(),
]);
echo "[2] Mitra mengambil bantuan -> Mitra ID: {$help->mitra_id}, Status: {$help->status}\n";
assert($help->mitra_id === $mitra->id, 'Mitra ID harus sesuai');

// 4. Test Mitra requesting cancellation
$help->update([
    'partner_cancel_prev_status' => 'taken',
    'status' => 'partner_cancel_requested',
    'partner_cancel_requested_at' => now(),
    'partner_cancel_reason' => 'Ban motor bocor di perjalanan',
]);
echo "[3] Mitra mengajukan pembatalan -> Status: {$help->status}, Alasan: {$help->partner_cancel_reason}\n";
assert($help->status === 'partner_cancel_requested', 'Status harus partner_cancel_requested');

// 5. Test Customer Rejects cancellation first
auth()->login($customer);
$customerDetailComponent = new CustomerHelpDetail();
$customerDetailComponent->helpId = $help->id;
$customerDetailComponent->loadHelp();
$customerDetailComponent->rejectPartnerCancellation();

$help->refresh();
echo "[4] Customer MENOLAK pembatalan -> Status kembali ke: {$help->status}, Prev Status Flag: {$help->partner_cancel_prev_status}\n";
assert($help->status === 'taken', 'Status harus kembali ke taken');
assert($help->partner_cancel_prev_status === 'cancel_rejected', 'Flag harus cancel_rejected');

// 6. Test Mitra requests cancellation again
auth()->login($mitra);
$help->update([
    'partner_cancel_prev_status' => 'taken',
    'status' => 'partner_cancel_requested',
    'partner_cancel_requested_at' => now(),
    'partner_cancel_reason' => 'Keluarga sakit mendadak',
]);

// 7. Test Customer Accepts cancellation
auth()->login($customer);
$customerDetailComponent->loadHelp();
$customerDetailComponent->acceptPartnerCancellation();

$help->refresh();
echo "[5] Customer MENYETUJUI pembatalan -> Status: {$help->status}, Mitra ID: " . var_export($help->mitra_id, true) . ", Flag: {$help->partner_cancel_prev_status}\n";
assert($help->status === 'menunggu_mitra', 'Status harus kembali ke menunggu_mitra agar tersedia di daftar bantuan');
assert($help->mitra_id === null, 'Mitra ID harus null agar bisa diambil mitra lain');
assert($help->partner_cancel_prev_status === 'cancel_accepted', 'Flag harus cancel_accepted');

// 8. Test Available Helps Query for Mitra
$availableCount = Help::where('status', 'menunggu_mitra')->whereNull('mitra_id')->where('id', $help->id)->count();
echo "[6] Cek query bantuan tersedia untuk Mitra -> Bantuan ID {$help->id} muncul di pencarian: " . ($availableCount > 0 ? "YA (BERHASIL)" : "TIDAK (GAGAL)") . "\n";
assert($availableCount === 1, 'Bantuan harus muncul kembali di pencarian mitra');

// Cleanup test record
$help->delete();
echo "\n=== SEMUA TEST BERHASIL (PASSED) ===\n";
