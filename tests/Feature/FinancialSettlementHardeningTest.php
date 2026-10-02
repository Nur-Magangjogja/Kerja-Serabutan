<?php

namespace Tests\Feature;

use App\Livewire\Admin\Disputes\Index as DisputesIndex;
use App\Models\BalanceTransaction;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\Cancellation\CancellationAuditService;
use App\Services\Cancellation\CancellationSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FinancialSettlementHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $partner;
    protected User $admin;
    protected City $city;
    protected District $district;
    protected CancellationSettlementService $settlementService;
    protected CancellationAuditService $auditService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::create([
            'name'      => 'Kota Yogyakarta',
            'latitude'  => -7.7956,
            'longitude' => 110.3695,
        ]);

        $this->district = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Danurejan',
            'latitude'  => -7.7930,
            'longitude' => 110.3700,
        ]);

        $this->customer = User::factory()->create([
            'name'        => 'Customer Test',
            'role'        => 'customer',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);

        $this->partner = User::factory()->create([
            'name'        => 'Mitra Test',
            'role'        => 'mitra',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);

        $this->admin = User::factory()->create([
            'name'        => 'Admin Wilayah Danurejan',
            'role'        => 'admin',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);

        PartnerOnlineState::create([
            'user_id'         => $this->partner->id,
            'matching_status' => PartnerOnlineState::STATUS_BUSY,
        ]);

        $this->settlementService = app(CancellationSettlementService::class);
        $this->auditService      = app(CancellationAuditService::class);
    }

    protected function createHeldHelp(float $totalAmount = 100000): Help
    {
        return Help::create([
            'user_id'        => $this->customer->id,
            'mitra_id'       => $this->partner->id,
            'city_id'        => $this->city->id,
            'district_id'    => $this->district->id,
            'title'          => 'Bantuan Reparasi AC',
            'description'    => 'Deskripsi reparasi ac bocor',
            'amount'         => $totalAmount - 2000,
            'total_amount'   => $totalAmount,
            'status'         => Help::STATUS_TAKEN,
            'dispatch_mode'  => Help::DISPATCH_MODE_ASSIGNED,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'service_type'   => Help::SERVICE_TYPE_ON_SITE,
            'order_mode'     => Help::ORDER_MODE_INSTANT,
            'taken_at'       => now(),
        ]);
    }

    public function test_partial_settlement_total_greater_than_escrow_rejected(): void
    {
        $help = $this->createHeldHelp(100000);

        // Case: Escrow 100.000, refund 100.000, payout 100.000 -> Total 200.000 > 100.000
        $this->expectException(\RuntimeException::class);
        $this->settlementService->processPartialSettlement($help, 100000, 100000, 'Test split over escrow');
    }

    public function test_partial_settlement_total_less_than_escrow_rejected(): void
    {
        $help = $this->createHeldHelp(100000);

        // Case: Escrow 100.000, refund 40.000, payout 30.000 -> Total 70.000 < 100.000
        $this->expectException(\RuntimeException::class);
        $this->settlementService->processPartialSettlement($help, 30000, 40000, 'Test under-settlement');
    }

    public function test_partial_settlement_negative_amounts_rejected(): void
    {
        $help = $this->createHeldHelp(100000);

        // Case: Negative refund or negative payout
        $this->expectException(\InvalidArgumentException::class);
        $this->settlementService->processPartialSettlement($help, 110000, -10000, 'Test negative amount');
    }

    public function test_partial_settlement_valid_exact_split_accepted(): void
    {
        $help = $this->createHeldHelp(100000);

        UserBalance::where('user_id', $this->customer->id)->update(['balance' => 0]);
        UserBalance::where('user_id', $this->partner->id)->update(['balance' => 0]);

        // Exact split: Payout 40.000, Refund 60.000 -> Total 100.000 == Escrow
        $this->settlementService->processPartialSettlement($help, 40000, 60000, 'Kompensasi audit parsial');

        $help->refresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->status);
        $this->assertEquals(Help::ESCROW_STATUS_PARTIAL_REFUND, $help->escrow_status);
        $this->assertEquals(Help::PAYMENT_STATUS_REFUNDED, $help->payment_status);

        $customerBal = (float) UserBalance::where('user_id', $this->customer->id)->value('balance');
        $partnerBal  = (float) UserBalance::where('user_id', $this->partner->id)->value('balance');

        $this->assertEquals(60000, $customerBal);
        $this->assertEquals(40000, $partnerBal);

        // Verify ledger transactions
        $this->assertTrue(BalanceTransaction::where('user_id', $this->customer->id)->where('type', 'refund')->where('amount', 60000)->exists());
        $this->assertTrue(BalanceTransaction::where('user_id', $this->partner->id)->where('type', 'earning')->where('amount', 40000)->exists());
    }

    public function test_full_refund_repeat_call_is_idempotent_no_double_mutation_and_no_crash(): void
    {
        $help = $this->createHeldHelp(100000);

        UserBalance::where('user_id', $this->customer->id)->update(['balance' => 0]);

        // 1st run: Full refund executed
        $this->settlementService->processFullRefund($help, 'Pembatalan awal');

        $this->assertEquals(100000, (float) UserBalance::where('user_id', $this->customer->id)->value('balance'));
        $this->assertEquals(1, BalanceTransaction::where('user_id', $this->customer->id)->where('type', 'refund')->count());

        // 2nd run: Calling processFullRefund again must NOT crash and must NOT add balance again
        $this->settlementService->processFullRefund($help, 'Panggilan kedua refund');

        $this->assertEquals(100000, (float) UserBalance::where('user_id', $this->customer->id)->value('balance'));
        $this->assertEquals(1, BalanceTransaction::where('user_id', $this->customer->id)->where('type', 'refund')->count());

        // 3rd run on an already released escrow: Must safely no-op without crashing
        $help->update(['escrow_status' => Help::ESCROW_STATUS_RELEASED]);
        $this->settlementService->processFullRefund($help, 'Panggilan ketiga released escrow');

        $this->assertEquals(100000, (float) UserBalance::where('user_id', $this->customer->id)->value('balance'));
        $this->assertEquals(1, BalanceTransaction::where('user_id', $this->customer->id)->where('type', 'refund')->count());
    }

    public function test_partial_settlement_repeat_call_rejected_and_no_double_mutation(): void
    {
        $help = $this->createHeldHelp(100000);

        UserBalance::where('user_id', $this->customer->id)->update(['balance' => 0]);
        UserBalance::where('user_id', $this->partner->id)->update(['balance' => 0]);

        // 1st run: Valid exact split
        $this->settlementService->processPartialSettlement($help, 40000, 60000, 'Valid 1st split');

        $this->assertEquals(60000, (float) UserBalance::where('user_id', $this->customer->id)->value('balance'));
        $this->assertEquals(40000, (float) UserBalance::where('user_id', $this->partner->id)->value('balance'));
        $this->assertEquals(1, BalanceTransaction::where('user_id', $this->customer->id)->where('type', 'refund')->count());
        $this->assertEquals(1, BalanceTransaction::where('user_id', $this->partner->id)->where('type', 'earning')->count());

        // 2nd run: Should throw exception and NOT double-credit
        try {
            $this->settlementService->processPartialSettlement($help, 40000, 60000, 'Repeat split call');
            $this->fail('Harusnya melempar RuntimeException karena escrow sudah tidak berstatus held.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('sudah diselesaikan', $e->getMessage());
        }

        // Balances and ledger entries must remain strictly unchanged
        $this->assertEquals(60000, (float) UserBalance::where('user_id', $this->customer->id)->value('balance'));
        $this->assertEquals(40000, (float) UserBalance::where('user_id', $this->partner->id)->value('balance'));
        $this->assertEquals(1, BalanceTransaction::where('user_id', $this->customer->id)->where('type', 'refund')->count());
        $this->assertEquals(1, BalanceTransaction::where('user_id', $this->partner->id)->where('type', 'earning')->count());
    }

    public function test_partial_settlement_transaction_atomicity_rolls_back_on_mid_settlement_failure(): void
    {
        $help = $this->createHeldHelp(100000);

        UserBalance::where('user_id', $this->customer->id)->update(['balance' => 0]);
        UserBalance::where('user_id', $this->partner->id)->update(['balance' => 0]);

        // Mock HelpEscrowService so that refund succeeds, but payoutPartialFromEscrowDirect fails midway
        $mockEscrow = $this->partialMock(\App\Services\HelpEscrowService::class, function ($mock) {
            $mock->shouldReceive('refundFromEscrowDirect')->once()->passthru();
            $mock->shouldReceive('payoutPartialFromEscrowDirect')->once()->andThrow(new \RuntimeException('Simulated payment gateway timeout during partner payout'));
        });

        $service = new CancellationSettlementService($mockEscrow, app(\App\Services\PartnerOnlineService::class));

        try {
            $service->processPartialSettlement($help, 40000, 60000, 'Test atomic failure');
            $this->fail('Harusnya melempar RuntimeException.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Simulated payment gateway timeout', $e->getMessage());
        }

        // Entire transaction MUST rollback: customer balance is 0, no ledger transactions exist, help status is unchanged
        $this->assertEquals(0, (float) UserBalance::where('user_id', $this->customer->id)->value('balance'));
        $this->assertEquals(0, (float) UserBalance::where('user_id', $this->partner->id)->value('balance'));
        $this->assertEquals(0, BalanceTransaction::where('reference_id', $help->id)->count());

        $help->refresh();
        $this->assertEquals(Help::STATUS_TAKEN, $help->status);
        $this->assertEquals(Help::ESCROW_STATUS_HELD, $help->escrow_status);
    }

    public function test_cancellation_audit_service_rejects_invalid_partial_split(): void
    {
        $help = $this->createHeldHelp(100000);

        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $help->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->partner->id,
            'requester_type'  => HelpCancelRequest::REQUESTER_CUSTOMER,
            'action_type'     => HelpCancelRequest::ACTION_CUSTOMER_WITHDRAW,
            'status'          => HelpCancelRequest::STATUS_PENDING,
            'previous_status' => Help::STATUS_TAKEN,
            'reason'          => 'Alasan pembatalan',
            'requested_at'    => now(),
        ]);

        // Try over-escrow in audit review: 100.000 + 100.000 = 200.000 > 100.000
        $this->expectException(\RuntimeException::class);
        $this->auditService->reviewCancelRequest(
            $cancelReq,
            $this->admin,
            'valid_no_sp',
            'Notes',
            HelpCancelRequest::SP_TARGET_NONE,
            null,
            null,
            null,
            null,
            HelpCancelRequest::SETTLEMENT_PARTIAL_SETTLEMENT,
            100000, // refund
            100000  // partner payout
        );
    }

    public function test_cancellation_audit_service_rejects_under_escrow_split(): void
    {
        $help = $this->createHeldHelp(100000);

        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $help->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->partner->id,
            'requester_type'  => HelpCancelRequest::REQUESTER_CUSTOMER,
            'action_type'     => HelpCancelRequest::ACTION_CUSTOMER_WITHDRAW,
            'status'          => HelpCancelRequest::STATUS_PENDING,
            'previous_status' => Help::STATUS_TAKEN,
            'reason'          => 'Alasan pembatalan',
            'requested_at'    => now(),
        ]);

        // Try under-escrow in audit review: 40.000 + 40.000 = 80.000 < 100.000
        $this->expectException(\RuntimeException::class);
        $this->auditService->reviewCancelRequest(
            $cancelReq,
            $this->admin,
            'valid_no_sp',
            'Notes',
            HelpCancelRequest::SP_TARGET_NONE,
            null,
            null,
            null,
            null,
            HelpCancelRequest::SETTLEMENT_PARTIAL_SETTLEMENT,
            40000, // refund
            40000  // partner payout
        );
    }

    public function test_help_cancellation_service_review_by_admin_rejects_over_escrow_split(): void
    {
        $help = $this->createHeldHelp(100000);

        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $help->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->partner->id,
            'requester_type'  => HelpCancelRequest::REQUESTER_CUSTOMER,
            'action_type'     => HelpCancelRequest::ACTION_CUSTOMER_WITHDRAW,
            'status'          => HelpCancelRequest::STATUS_PENDING,
            'previous_status' => Help::STATUS_TAKEN,
            'reason'          => 'Alasan pembatalan',
            'requested_at'    => now(),
        ]);

        $cancellationService = app(\App\Services\HelpCancellationService::class);

        // Direct call to reviewByAdmin with manipulated values (refund 100.000, payout 100.000)
        $this->expectException(\RuntimeException::class);
        $cancellationService->reviewByAdmin(
            $cancelReq,
            $this->admin,
            true,
            HelpCancelRequest::SETTLEMENT_PARTIAL_SETTLEMENT,
            [
                'refund_amount'  => 100000,
                'partner_amount' => 100000,
                'admin_notes'    => 'Manipulated split',
            ]
        );
    }

    public function test_livewire_disputes_validates_negative_amount(): void
    {
        $help = $this->createHeldHelp(100000);

        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $help->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->partner->id,
            'requester_type'  => HelpCancelRequest::REQUESTER_CUSTOMER,
            'action_type'     => HelpCancelRequest::ACTION_CUSTOMER_WITHDRAW,
            'status'          => HelpCancelRequest::STATUS_PENDING,
            'previous_status' => Help::STATUS_TAKEN,
            'reason'          => 'Alasan pembatalan',
            'requested_at'    => now(),
        ]);

        $this->actingAs($this->admin);

        Livewire::test(DisputesIndex::class)
            ->call('openCancelReviewModal', $cancelReq->id)
            ->set('cancelDecision', 'approved')
            ->set('settlementType', 'partial_settlement')
            ->set('cancelRefundAmount', -5000)
            ->call('openCancelReviewConfirmModal')
            ->assertHasErrors(['cancelRefundAmount']);

        // Verify that cancelReq was NOT approved
        $cancelReq->refresh();
        $this->assertEquals(HelpCancelRequest::STATUS_PENDING, $cancelReq->status);
    }
}
