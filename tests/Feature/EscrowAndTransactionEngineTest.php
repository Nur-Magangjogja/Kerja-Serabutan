<?php

namespace Tests\Feature;

use App\Models\BalanceTransaction;
use App\Models\City;
use App\Models\Help;
use App\Models\PaymentWebhookLog;
use App\Models\Rating;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\HelpTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EscrowAndTransactionEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $mitra;
    protected User $admin;
    protected City $city;
    protected HelpTransactionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->city = City::create([
            'name'       => 'Kota Yogyakarta',
            'state_name' => 'DI Yogyakarta',
            'latitude'   => -7.7956,
            'longitude'  => 110.3695,
        ]);

        $this->customer = User::factory()->create([
            'role'    => 'customer',
            'city_id' => $this->city->id,
        ]);

        $this->mitra = User::factory()->create([
            'role'    => 'mitra',
            'city_id' => $this->city->id,
        ]);

        $this->admin = User::factory()->create([
            'role'    => 'admin',
            'city_id' => $this->city->id,
        ]);

        $this->service = app(HelpTransactionService::class);
    }

    private function createAssignedHelp(float $amount = 50000, float $fee = 5000): Help
    {
        // 1. Customer has balance and locks escrow
        $custBalance = UserBalance::create([
            'user_id' => $this->customer->id,
            'balance' => 100000,
        ]);

        $help = Help::create([
            'user_id'                  => $this->customer->id,
            'mitra_id'                 => $this->mitra->id,
            'city_id'                  => $this->city->id,
            'title'                    => 'Bantu Cuci AC',
            'description'              => 'Deskripsi lengkap pengerjaan cuci AC split 1PK',
            'amount'                   => $amount,
            'admin_fee'                => $fee,
            'total_amount'             => $amount + $fee,
            'model_version'            => 2,
            'mitra_earning'            => $amount,
            'platform_fee_amount'      => $fee,
            'status'                   => Help::STATUS_IN_PROGRESS,
            'escrow_status'            => Help::ESCROW_STATUS_HELD,
            'payment_status'           => Help::PAYMENT_STATUS_PAID,
            'dispatch_mode'            => Help::DISPATCH_MODE_ASSIGNED,
            'order_id'                 => 'TEST-ORDER-' . uniqid(),
            'service_started_at'       => now(),
        ]);

        // Deduct customer balance & record escrow_lock
        $custBalance->decrement('balance', $amount + $fee);
        BalanceTransaction::create([
            'idempotency_key' => "help:{$help->id}:escrow_lock:{$this->customer->id}",
            'user_id'         => $this->customer->id,
            'amount'          => $amount + $fee,
            'direction'       => 'debit',
            'type'            => 'escrow_lock',
            'reference_id'    => $help->id,
            'reference_type'  => 'help',
            'status'          => 'completed',
        ]);

        return $help;
    }

    public function test_submit_completion_transitions_to_waiting_confirmation_and_sets_24h_deadline_without_releasing_funds()
    {
        $help = $this->createAssignedHelp(50000, 5000);
        $file = UploadedFile::fake()->image('proof.jpg');

        $this->service->submitCompletion($help, $this->mitra, $file, 'Pekerjaan selesai dengan rapi.');

        $help->refresh();

        $this->assertEquals(Help::STATUS_WAITING_CONFIRMATION, $help->status);
        $this->assertEquals(Help::ESCROW_STATUS_HELD, $help->escrow_status);
        $this->assertEquals(Help::DISPATCH_MODE_ASSIGNED, $help->dispatch_mode);
        $this->assertNotNull($help->confirmation_deadline_at);
        $this->assertTrue($help->confirmation_deadline_at->isFuture());

        // Pastikan mitra belum menerima saldo
        $mitraBalance = UserBalance::where('user_id', $this->mitra->id)->first();
        $this->assertTrue($mitraBalance === null || $mitraBalance->balance == 0);

        // Pastikan tidak ada transaksi earning di ledger
        $this->assertDatabaseMissing('balance_transactions', [
            'reference_id' => $help->id,
            'type'         => 'earning',
        ]);
    }

    public function test_customer_confirm_completion_releases_escrow_atomically_and_idempotently()
    {
        $help = $this->createAssignedHelp(50000, 5000);
        $file = UploadedFile::fake()->image('proof.jpg');
        $this->service->submitCompletion($help, $this->mitra, $file);

        // Customer confirms
        $this->service->customerConfirmCompletion($help, $this->customer);

        $help->refresh();
        $this->assertEquals(Help::STATUS_SELESAI, $help->status);
        $this->assertEquals(Help::ESCROW_STATUS_RELEASED, $help->escrow_status);
        $this->assertEquals(Help::PAYMENT_STATUS_PAID, $help->payment_status);
        $this->assertEquals(Help::DISPATCH_MODE_CLOSED, $help->dispatch_mode);

        // Saldo mitra bertambah 50.000
        $mitraBalance = UserBalance::where('user_id', $this->mitra->id)->first();
        $this->assertEquals(50000, (float) $mitraBalance->balance);

        // Ledger earning & platform fee tercipta
        $this->assertDatabaseHas('balance_transactions', [
            'idempotency_key' => "help:{$help->id}:earning:{$this->mitra->id}",
            'type'            => 'earning',
            'amount'          => 50000,
            'direction'       => 'credit',
        ]);

        $this->assertDatabaseHas('balance_transactions', [
            'idempotency_key' => "help:{$help->id}:platform_fee",
            'type'            => 'platform_fee',
            'amount'          => 5000,
            'direction'       => 'credit',
        ]);

        // Test idempotency: pemanggilan ulang harus dicegah
        $this->expectException(\RuntimeException::class);
        $this->service->customerConfirmCompletion($help, $this->customer);
    }

    public function test_auto_confirm_releases_escrow_only_when_deadline_passed()
    {
        $help = $this->createAssignedHelp(60000, 6000);
        $file = UploadedFile::fake()->image('proof.jpg');
        $this->service->submitCompletion($help, $this->mitra, $file);

        // 1. Deadline masih di masa depan -> autoConfirm return false
        $result = $this->service->autoConfirmExpiredConfirmation($help);
        $this->assertFalse($result);
        $help->refresh();
        $this->assertEquals(Help::STATUS_WAITING_CONFIRMATION, $help->status);

        // 2. Set deadline ke masa lalu (expired 24 jam)
        $help->update(['confirmation_deadline_at' => now()->subMinutes(10)]);

        $result2 = $this->service->autoConfirmExpiredConfirmation($help);
        $this->assertTrue($result2);

        $help->refresh();
        $this->assertEquals(Help::STATUS_SELESAI, $help->status);
        $this->assertEquals(Help::ESCROW_STATUS_RELEASED, $help->escrow_status);
        $this->assertEquals(Help::DISPATCH_MODE_CLOSED, $help->dispatch_mode);
        $this->assertNotNull($help->auto_confirmed_at);

        $mitraBalance = UserBalance::where('user_id', $this->mitra->id)->first();
        $this->assertEquals(60000, (float) $mitraBalance->balance);
    }

    public function test_raise_dispute_freezes_escrow_and_prevents_auto_confirm()
    {
        $help = $this->createAssignedHelp(70000, 7000);
        $file = UploadedFile::fake()->image('proof.jpg');
        $this->service->submitCompletion($help, $this->mitra, $file);

        // Customer raises dispute
        $report = $this->service->raiseDispute($help, $this->customer, 'AC masih bocor dan tidak dingin sama sekali.');

        $help->refresh();
        $this->assertEquals(Help::ESCROW_STATUS_DISPUTED_FREEZE, $help->escrow_status);
        $this->assertNotNull($help->disputed_at);
        $this->assertDatabaseHas('partner_reports', [
            'id'               => $report->id,
            'reported_help_id' => $help->id,
            'status'           => 'pending',
        ]);

        // Auto-confirm even after deadline MUST be blocked
        $help->update(['confirmation_deadline_at' => now()->subMinutes(10)]);
        $autoConfirmResult = $this->service->autoConfirmExpiredConfirmation($help);
        $this->assertFalse($autoConfirmResult);

        // Mitra cannot be credited
        $this->assertDatabaseMissing('balance_transactions', [
            'reference_id' => $help->id,
            'type'         => 'earning',
        ]);
    }

    public function test_resolve_dispute_full_refund_refunds_gross_to_customer()
    {
        $help = $this->createAssignedHelp(80000, 8000);
        $file = UploadedFile::fake()->image('proof.jpg');
        $this->service->submitCompletion($help, $this->mitra, $file);
        $this->service->raiseDispute($help, $this->customer, 'Pekerjaan tidak dilakukan.');

        $custBalanceBefore = (float) UserBalance::where('user_id', $this->customer->id)->value('balance');

        // Admin resolves full refund
        $this->service->resolveDispute($help, $this->admin, 'full_refund');

        $help->refresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $help->escrow_status);
        $this->assertEquals(Help::PAYMENT_STATUS_REFUNDED, $help->payment_status);
        $this->assertEquals(Help::DISPATCH_MODE_CLOSED, $help->dispatch_mode);

        // Customer balance receives full gross amount (80000 + 8000 = 88000)
        $custBalanceAfter = (float) UserBalance::where('user_id', $this->customer->id)->value('balance');
        $this->assertEquals($custBalanceBefore + 88000, $custBalanceAfter);
    }

    public function test_resolve_dispute_partial_split_enforces_financial_invariant()
    {
        $help = $this->createAssignedHelp(100000, 10000); // Gross = 110,000
        $file = UploadedFile::fake()->image('proof.jpg');
        $this->service->submitCompletion($help, $this->mitra, $file);
        $this->service->raiseDispute($help, $this->customer, 'Hasil setengah selesai.');

        // 1. Invariant mismatch: partner(60k) + fee(10k) + refund(30k) = 100k != 110k -> throw Exception
        $this->expectException(\RuntimeException::class);
        $this->service->resolveDispute($help, $this->admin, 'partial_split', [
            'partner_amount'  => 60000,
            'platform_fee'    => 10000,
            'customer_refund' => 30000,
        ]);
    }

    public function test_resolve_dispute_partial_split_success()
    {
        $help = $this->createAssignedHelp(100000, 10000); // Gross = 110,000
        $file = UploadedFile::fake()->image('proof.jpg');
        $this->service->submitCompletion($help, $this->mitra, $file);
        $this->service->raiseDispute($help, $this->customer, 'Hasil setengah selesai.');

        // Exact split: partner(60k) + fee(10k) + refund(40k) = 110,000
        $this->service->resolveDispute($help, $this->admin, 'partial_split', [
            'partner_amount'  => 60000,
            'platform_fee'    => 10000,
            'customer_refund' => 40000,
        ]);

        $help->refresh();
        $this->assertEquals(Help::STATUS_SELESAI, $help->status);
        $this->assertEquals(Help::ESCROW_STATUS_PARTIAL_REFUND, $help->escrow_status);
        $this->assertEquals(Help::PAYMENT_STATUS_PARTIALLY_REFUNDED, $help->payment_status);
        $this->assertEquals(Help::DISPATCH_MODE_CLOSED, $help->dispatch_mode);

        $mitraBalance = UserBalance::where('user_id', $this->mitra->id)->first();
        $this->assertEquals(60000, (float) $mitraBalance->balance);
    }

    public function test_payment_webhook_log_enforces_unique_event_id()
    {
        PaymentWebhookLog::create([
            'event_id' => 'evt_test_12345',
            'provider' => 'midtrans',
            'status'   => 'processed',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        PaymentWebhookLog::create([
            'event_id' => 'evt_test_12345',
            'provider' => 'midtrans',
            'status'   => 'processed',
        ]);
    }

    public function test_rating_enforces_unique_help_rater_ratee()
    {
        $help = $this->createAssignedHelp(50000, 5000);

        Rating::create([
            'help_id'  => $help->id,
            'rater_id' => $this->customer->id,
            'ratee_id' => $this->mitra->id,
            'type'     => 'customer_to_mitra',
            'rating'   => 5,
            'review'   => 'Sangat bagus!',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Rating::create([
            'help_id'  => $help->id,
            'rater_id' => $this->customer->id,
            'ratee_id' => $this->mitra->id,
            'type'     => 'customer_to_mitra',
            'rating'   => 4,
            'review'   => 'Duplikat rating',
        ]);
    }
}
