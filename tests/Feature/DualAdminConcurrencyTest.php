<?php

namespace Tests\Feature;

use App\Livewire\Admin\Partners\Reports\Show as PartnerReportShow;
use App\Livewire\Admin\Topup\Approval as TopupApproval;
use App\Livewire\Admin\Verifications\Index as VerificationsIndex;
use App\Livewire\Admin\Withdraws\Index as WithdrawsIndex;
use App\Models\ActivityLog;
use App\Models\BalanceTransaction;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\PartnerReport;
use App\Models\Province;
use App\Models\Registration;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\UserGreylistLog;
use App\Models\WithdrawRequest;
use App\Services\HelpCancellationService;
use App\Services\HelpTransactionService;
use App\Services\Territory\ProfileTerritoryMigrationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DualAdminConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;
    protected City $city;
    protected District $districtX;
    protected District $districtY;
    protected District $districtZ;
    protected User $adminA;
    protected User $adminB;
    protected User $customer;
    protected User $mitra;

    protected function setUp(): void
    {
        parent::setUp();

        $this->province = Province::create([
            'name'      => 'DI Yogyakarta',
            'code'      => '34',
            'is_active' => true,
        ]);

        $this->city = City::create([
            'name'        => 'Kota Yogyakarta',
            'province'    => 'DI Yogyakarta',
            'province_id' => $this->province->id,
            'is_active'   => true,
            'latitude'    => -7.7956,
            'longitude'   => 110.3695,
        ]);

        // District X (Shared between Admin A and Admin B)
        $this->districtX = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Kecamatan X',
            'is_active' => true,
        ]);

        // District Y (Owned by Admin A, outside Admin B)
        $this->districtY = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Kecamatan Y',
            'is_active' => true,
        ]);

        // District Z (Owned by Admin B, outside Admin A)
        $this->districtZ = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Kecamatan Z',
            'is_active' => true,
        ]);

        // Admin A manages District X and District Y
        $this->adminA = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->districtX->id,
        ]);
        $this->adminA->managedDistricts()->sync([$this->districtX->id, $this->districtY->id]);

        // Admin B manages District X and District Z
        $this->adminB = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->districtX->id,
        ]);
        $this->adminB->managedDistricts()->sync([$this->districtX->id, $this->districtZ->id]);

        // Customer & Mitra di District X
        $this->customer = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->districtX->id,
            'city'        => $this->city->name,
            'province'    => $this->province->name,
            'kecamatan'   => $this->districtX->name,
        ]);

        $this->mitra = User::factory()->create([
            'role'        => 'mitra',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->districtX->id,
            'city'        => $this->city->name,
            'province'    => $this->province->name,
            'kecamatan'   => $this->districtX->name,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // C1 & C2: KTP VERIFICATION CONCURRENCY
    // ─────────────────────────────────────────────────────────────────────────

    public function test_c1_ktp_double_approve_produces_one_effective_approval_and_one_activity_log(): void
    {
        $regUser = User::factory()->create([
            'email'    => 'calon_mitra@example.com',
            'status'   => 'inactive',
            'verified' => false,
        ]);

        $reg = Registration::create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'full_name'   => 'Calon Mitra',
            'name'        => 'Calon Mitra',
            'email'       => 'calon_mitra@example.com',
            'phone'       => '081234567890',
            'role'        => 'mitra',
            'nik'         => '3201019999990001',
            'city_id'     => $this->city->id,
            'city'        => $this->city->name,
            'district_id' => $this->districtX->id,
            'status'      => 'pending_verification',
        ]);

        // Admin A approves
        $this->actingAs($this->adminA);
        Livewire::test(VerificationsIndex::class)
            ->call('approveKtp', $reg->id);

        $this->assertSame('approved', $reg->fresh()->status);
        $this->assertTrue($regUser->fresh()->verified);
        $this->assertSame('active', $regUser->fresh()->status);

        $logCountAfterA = ActivityLog::where('action', 'ktp_verified')
            ->whereJsonContains('properties->registration_id', $reg->id)
            ->count();
        $this->assertSame(1, $logCountAfterA);

        // Admin B (stale page) attempts to approve again
        $this->actingAs($this->adminB);
        Livewire::test(VerificationsIndex::class)
            ->call('approveKtp', $reg->id);

        // Harus tetap 1 log, tidak ada duplikasi log aktivitas
        $logCountAfterB = ActivityLog::where('action', 'ktp_verified')
            ->whereJsonContains('properties->registration_id', $reg->id)
            ->count();
        $this->assertSame(1, $logCountAfterB, 'Second stale approval must not produce duplicate ActivityLog');
    }

    public function test_c2_ktp_conflicting_decision_approve_then_stale_reject_prevents_flip_flop(): void
    {
        $regUser = User::factory()->create([
            'email'    => 'calon_mitra2@example.com',
            'status'   => 'inactive',
            'verified' => false,
        ]);

        $reg = Registration::create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'full_name'   => 'Calon Mitra 2',
            'name'        => 'Calon Mitra 2',
            'email'       => 'calon_mitra2@example.com',
            'phone'       => '081234567891',
            'role'        => 'mitra',
            'nik'         => '3201019999990002',
            'city_id'     => $this->city->id,
            'city'        => $this->city->name,
            'district_id' => $this->districtX->id,
            'status'      => 'pending_verification',
        ]);

        // Admin A approves
        $this->actingAs($this->adminA);
        Livewire::test(VerificationsIndex::class)
            ->call('approveKtp', $reg->id);

        $this->assertSame('approved', $reg->fresh()->status);
        $this->assertSame('active', $regUser->fresh()->status);
        $this->assertTrue($regUser->fresh()->verified);

        // Admin B (stale page) attempts to reject
        $this->actingAs($this->adminB);
        Livewire::test(VerificationsIndex::class)
            ->set('rejectingId', $reg->id)
            ->set('rejectReason', 'Foto KTP tidak jelas')
            ->call('confirmReject');

        // Status Registrasi dan User HARUS TETAP APPROVED / ACTIVE, tidak boleh flip-flop ke rejected
        $this->assertSame('approved', $reg->fresh()->status, 'Stale reject must not overwrite approved registration');
        $this->assertSame('active', $regUser->fresh()->status, 'User must remain active');
        $this->assertTrue($regUser->fresh()->verified, 'User must remain verified');

        // Tidak boleh ada ActivityLog ktp_rejected
        $rejectLogCount = ActivityLog::where('action', 'ktp_rejected')
            ->whereJsonContains('properties->registration_id', $reg->id)
            ->count();
        $this->assertSame(0, $rejectLogCount);
    }

    public function test_c2_inverse_ktp_conflicting_decision_reject_then_stale_approve_prevents_flip_flop(): void
    {
        $regUser = User::factory()->create([
            'email'    => 'calon_mitra3@example.com',
            'status'   => 'inactive',
            'verified' => false,
        ]);

        $reg = Registration::create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'full_name'   => 'Calon Mitra 3',
            'name'        => 'Calon Mitra 3',
            'email'       => 'calon_mitra3@example.com',
            'phone'       => '081234567892',
            'role'        => 'mitra',
            'nik'         => '3201019999990003',
            'city_id'     => $this->city->id,
            'city'        => $this->city->name,
            'district_id' => $this->districtX->id,
            'status'      => 'pending_verification',
        ]);

        // Admin A rejects
        $this->actingAs($this->adminA);
        Livewire::test(VerificationsIndex::class)
            ->set('rejectingId', $reg->id)
            ->set('rejectReason', 'NIK tidak sesuai')
            ->call('confirmReject');

        $this->assertSame('rejected', $reg->fresh()->status);
        $this->assertSame('inactive', $regUser->fresh()->status);

        // Admin B (stale page) attempts to approve
        $this->actingAs($this->adminB);
        Livewire::test(VerificationsIndex::class)
            ->call('approveKtp', $reg->id);

        $this->assertSame('rejected', $reg->fresh()->status, 'Stale approve must not overwrite rejected registration');
        $this->assertSame('inactive', $regUser->fresh()->status);
        $this->assertFalse($regUser->fresh()->verified);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // C3 & C4: TOPUP CONCURRENCY
    // ─────────────────────────────────────────────────────────────────────────

    public function test_c3_topup_double_approve_credits_balance_once_and_creates_one_success_log(): void
    {
        $userBalance = UserBalance::create([
            'user_id' => $this->customer->id,
            'balance' => 100000,
        ]);

        $tx = BalanceTransaction::create([
            'user_id'                => $this->customer->id,
            'type'                   => 'topup',
            'amount'                 => 50000,
            'direction'              => 'credit',
            'total_payment'          => 50000,
            'status'                 => 'waiting_approval',
            'manual_approval_status' => 'pending',
        ]);

        // Admin A approves
        $this->actingAs($this->adminA);
        Livewire::test(TopupApproval::class)
            ->call('approve', $tx->id);

        $this->assertSame(150000.0, (float) $userBalance->fresh()->balance);
        $this->assertSame('completed', $tx->fresh()->status);

        // Admin B stale approves
        $this->actingAs($this->adminB);
        Livewire::test(TopupApproval::class)
            ->call('approve', $tx->id)
            ->assertSee('tidak valid, sudah diproses');

        // Saldo tidak bertambah untuk kedua kalinya!
        $this->assertSame(150000.0, (float) $userBalance->fresh()->balance);

        $logs = ActivityLog::where('action', 'topup_approved')
            ->whereJsonContains('properties->transaction_id', $tx->id)
            ->count();
        $this->assertSame(1, $logs);
    }

    public function test_c4_topup_approve_then_stale_reject_retains_success(): void
    {
        $userBalance = UserBalance::create([
            'user_id' => $this->customer->id,
            'balance' => 100000,
        ]);

        $tx = BalanceTransaction::create([
            'user_id'                => $this->customer->id,
            'type'                   => 'topup',
            'amount'                 => 50000,
            'direction'              => 'credit',
            'total_payment'          => 50000,
            'status'                 => 'waiting_approval',
            'manual_approval_status' => 'pending',
        ]);

        // Admin A approves
        $this->actingAs($this->adminA);
        Livewire::test(TopupApproval::class)
            ->call('approve', $tx->id);

        $this->assertSame(150000.0, (float) $userBalance->fresh()->balance);

        // Admin B stale rejects
        $this->actingAs($this->adminB);
        Livewire::test(TopupApproval::class)
            ->set('selectedTransaction', $tx)
            ->set('rejectionReason', 'Bukti transfer palsu')
            ->call('reject', $tx->id)
            ->assertSee('tidak valid, sudah diproses');

        $this->assertSame('completed', $tx->fresh()->status);
        $this->assertSame(150000.0, (float) $userBalance->fresh()->balance);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // C5 & C6: WITHDRAW CONCURRENCY (ACTUAL LIFECYCLE)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_c5_withdraw_double_approve_respects_actual_lifecycle_no_second_debit_one_completion(): void
    {
        // Lifecycle: Saldo pengguna sudah didebit saat request dibuat (misal saldo sisa 100rb dari 200rb)
        $userBalance = UserBalance::create([
            'user_id' => $this->mitra->id,
            'balance' => 100000,
        ]);

        $withdraw = WithdrawRequest::create([
            'user_id'        => $this->mitra->id,
            'amount'         => 100000,
            'admin_fee'      => 5000,
            'net_amount'     => 95000,
            'bank_code'      => 'BCA',
            'account_number' => '1234567890',
            'account_name'   => 'Mitra Test',
            'status'         => 'pending',
        ]);

        // Transaksi ledger pending sudah dibuat di awal
        $ledger = BalanceTransaction::create([
            'user_id'        => $this->mitra->id,
            'type'           => 'withdraw',
            'amount'         => 105000,
            'direction'      => 'debit',
            'reference_id'   => $withdraw->id,
            'reference_type' => 'withdraw',
            'status'         => 'pending',
        ]);

        // Fake image untuk proof
        $file = \Illuminate\Http\UploadedFile::fake()->image('transfer_proof.jpg');

        // Admin A approves
        $this->actingAs($this->adminA);
        Livewire::test(WithdrawsIndex::class)
            ->set('selectedWithdrawId', $withdraw->id)
            ->set('proofPhoto', $file)
            ->call('submitApprove');

        $this->assertSame('completed', $withdraw->fresh()->status);
        $this->assertSame('success', $ledger->fresh()->status);
        // Saldo mitra TIDAK boleh didebit lagi
        $this->assertSame(100000.0, (float) $userBalance->fresh()->balance);

        // Admin B stale approves
        $this->actingAs($this->adminB);
        Livewire::test(WithdrawsIndex::class)
            ->set('selectedWithdrawId', $withdraw->id)
            ->set('proofPhoto', $file)
            ->call('submitApprove')
            ->assertSee('sudah diproses');

        $this->assertSame('completed', $withdraw->fresh()->status);
        $this->assertSame(100000.0, (float) $userBalance->fresh()->balance);
    }

    public function test_c6_withdraw_double_reject_refunds_principal_plus_fee_once_with_unique_key(): void
    {
        // Lifecycle: Saldo pengguna awalnya 100rb setelah didebit 105rb
        $userBalance = UserBalance::create([
            'user_id' => $this->mitra->id,
            'balance' => 100000,
        ]);

        $withdraw = WithdrawRequest::create([
            'user_id'        => $this->mitra->id,
            'amount'         => 100000,
            'admin_fee'      => 5000,
            'net_amount'     => 95000,
            'bank_code'      => 'BCA',
            'account_number' => '1234567890',
            'account_name'   => 'Mitra Test',
            'status'         => 'pending',
        ]);

        // Admin A rejects -> refund 105rb ke mitra
        $this->actingAs($this->adminA);
        Livewire::test(WithdrawsIndex::class)
            ->set('selectedWithdrawId', $withdraw->id)
            ->set('rejectReason', 'Nomor rekening tidak terdaftar')
            ->call('submitReject');

        $this->assertSame('rejected', $withdraw->fresh()->status);
        // Saldo kembali 100.000 + 105.000 = 205.000
        $this->assertSame(205000.0, (float) $userBalance->fresh()->balance);

        $refundLedgers = BalanceTransaction::where('type', 'refund')
            ->where('idempotency_key', "withdraw:{$withdraw->id}:refund")
            ->get();
        $this->assertCount(1, $refundLedgers);

        // Admin B stale rejects
        $this->actingAs($this->adminB);
        Livewire::test(WithdrawsIndex::class)
            ->set('selectedWithdrawId', $withdraw->id)
            ->set('rejectReason', 'Alasan kedua')
            ->call('submitReject')
            ->assertSee('sudah diproses');

        // Saldo tidak boleh direfund dua kali!
        $this->assertSame(205000.0, (float) $userBalance->fresh()->balance);
        $refundLedgersAfterB = BalanceTransaction::where('type', 'refund')
            ->where('idempotency_key', "withdraw:{$withdraw->id}:refund")
            ->count();
        $this->assertSame(1, $refundLedgersAfterB);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // C7: CANCELLATION REVIEW CONCURRENCY
    // ─────────────────────────────────────────────────────────────────────────

    public function test_c7_cancellation_review_double_action_prevents_second_processing(): void
    {
        $help = Help::create([
            'user_id'        => $this->customer->id,
            'mitra_id'       => $this->mitra->id,
            'city_id'        => $this->city->id,
            'district_id'    => $this->districtX->id,
            'title'          => 'Bantuan Pindahan',
            'description'    => 'Pindahan rumah',
            'category'       => 'tenaga_bantuan',
            'amount'         => 100000,
            'total_amount'   => 100000,
            'status'         => Help::STATUS_CUSTOMER_CANCEL_REQUESTED,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
        ]);

        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $help->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'requester_type'  => 'customer',
            'action_type'     => HelpCancelRequest::ACTION_CUSTOMER_WITHDRAW,
            'district_id'     => $this->districtX->id,
            'previous_status' => Help::STATUS_IN_PROGRESS,
            'status'          => HelpCancelRequest::STATUS_PENDING,
            'reason'          => 'Perubahan rencana mendadak',
        ]);

        $service = app(HelpCancellationService::class);

        // Admin A approves cancellation (full refund)
        $service->reviewByAdmin(
            $cancelReq,
            $this->adminA,
            true,
            HelpCancelRequest::SETTLEMENT_FULL_REFUND,
            ['admin_notes' => 'Disetujui Admin A']
        );

        $this->assertSame(HelpCancelRequest::STATUS_APPROVED, $cancelReq->fresh()->status);
        $this->assertSame(Help::STATUS_DIBATALKAN, $help->fresh()->status);

        // Admin B stale attempts to reject
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('sudah pernah diproses');

        $service->reviewByAdmin(
            $cancelReq,
            $this->adminB,
            false,
            HelpCancelRequest::SETTLEMENT_FULL_REFUND,
            ['admin_notes' => 'Ditolak Admin B']
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // C8: DISPUTE CONFLICTING RESOLUTION
    // ─────────────────────────────────────────────────────────────────────────

    public function test_c8_dispute_conflicting_resolution_executes_first_resolution_only(): void
    {
        $customerBalance = UserBalance::create([
            'user_id' => $this->customer->id,
            'balance' => 0,
        ]);

        $mitraBalance = UserBalance::create([
            'user_id' => $this->mitra->id,
            'balance' => 0,
        ]);

        $help = Help::create([
            'user_id'             => $this->customer->id,
            'mitra_id'            => $this->mitra->id,
            'city_id'             => $this->city->id,
            'district_id'         => $this->districtX->id,
            'title'               => 'Sengketa Reparasi',
            'description'         => 'Reparasi alat elektronik',
            'category'            => 'tenaga_bantuan',
            'amount'              => 200000,
            'total_amount'        => 200000,
            'status'              => Help::STATUS_WAITING_CONFIRMATION,
            'escrow_status'       => Help::ESCROW_STATUS_DISPUTED_FREEZE,
            'payment_status'      => Help::PAYMENT_STATUS_PAID,
            'disputed_at'         => now(),
            'dispute_resolved_at' => null,
        ]);

        $txService = app(HelpTransactionService::class);

        // Admin A resolves full refund to customer
        $txService->resolveDispute($help, $this->adminA, 'full_refund');

        $this->assertSame(Help::STATUS_DIBATALKAN, $help->fresh()->status);
        $this->assertSame(Help::ESCROW_STATUS_REFUNDED, $help->fresh()->escrow_status);
        $this->assertNotNull($help->fresh()->dispute_resolved_at);
        $this->assertSame(200000.0, (float) $customerBalance->fresh()->balance);
        $this->assertSame(0.0, (float) $mitraBalance->fresh()->balance);

        // Admin B stale attempts full release to partner
        try {
            $txService->resolveDispute($help, $this->adminB, 'full_release');
            $this->fail('Second dispute resolution must throw RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('disputed_freeze', $e->getMessage());
        }

        // Saldo mitra tetap 0, customer tetap 200rb
        $this->assertSame(200000.0, (float) $customerBalance->fresh()->balance);
        $this->assertSame(0.0, (float) $mitraBalance->fresh()->balance);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // C9: PARTNER REPORT SP CONCURRENCY & IDEMPOTENCY
    // ─────────────────────────────────────────────────────────────────────────

    public function test_c9_partner_report_double_sp_processed_once_and_blocks_stale_action(): void
    {
        $this->mitra->update([
            'warning_level' => 0,
            'is_greylisted' => false,
        ]);

        $report = PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra->id,
            'title'            => 'Laporan Pelanggaran Layanan',
            'message'          => 'Mitra tidak membawa perlengkapan sesuai pesanan',
            'report_type'      => 'pelanggaran_mitra',
            'category'         => 'layanan',
            'status'           => 'pending',
        ]);

        // Admin A and Admin B both open the report before action.
        // Admin A issues SP
        $this->actingAs($this->adminA);
        $testA = Livewire::test(PartnerReportShow::class, ['report' => $report])
            ->set('spTargetUserId', $this->mitra->id)
            ->set('spWarningLevel', 1)
            ->set('spReason', 'Terbukti melanggar SOP pelayanan')
            ->set('spAutoNoteInReport', true)
            ->call('submitInstantSp')
            ->assertSet('showSpModal', false)
            ->assertSee('berhasil diterbitkan');

        $spActivityLogCount = ActivityLog::where('action', 'admin_manual_warning_issued')
            ->get()
            ->filter(fn($log) => ($log->properties['partner_report_id'] ?? null) == $report->id)
            ->count();
        $this->assertSame(1, $spActivityLogCount, 'Exactly one ActivityLog recorded');

        // Admin B stale attempts to issue SP on the SAME report
        $this->actingAs($this->adminB);
        $testB = Livewire::test(PartnerReportShow::class, ['report' => $report])
            ->set('spTargetUserId', $this->mitra->id)
            ->set('spWarningLevel', 1)
            ->set('spReason', 'Terbukti melanggar SOP pelayanan oleh Admin B')
            ->set('spAutoNoteInReport', true)
            ->call('submitInstantSp')
            ->assertSee('telah diproses');

        // Asserts:
        // - warning level = 1 (NOT 2)
        // - exactly one UserGreylistLog with partner_report_id = report.id
        // - exactly one ActivityLog for SP on this report
        $this->assertSame(1, $this->mitra->fresh()->warning_level, 'Warning level must remain 1 and not be incremented by stale admin action');
        $this->assertSame(1, UserGreylistLog::where('partner_report_id', $report->id)->count(), 'Exactly one UserGreylistLog linked to this report');

        $finalSpActivityLogCount = ActivityLog::where('action', 'admin_manual_warning_issued')
            ->get()
            ->filter(fn($log) => ($log->properties['partner_report_id'] ?? null) == $report->id)
            ->count();
        $this->assertSame(1, $finalSpActivityLogCount, 'Exactly one ActivityLog recorded');
    }

    public function test_c9_different_report_on_same_user_allows_progressive_discipline(): void
    {
        $this->mitra->update([
            'warning_level' => 0,
            'is_greylisted' => false,
        ]);

        $report10 = PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra->id,
            'title'            => 'Laporan #10',
            'message'          => 'Pelanggaran pertama',
            'report_type'      => 'pelanggaran_mitra',
            'category'         => 'layanan',
            'status'           => 'pending',
        ]);

        $report11 = PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra->id,
            'title'            => 'Laporan #11',
            'message'          => 'Pelanggaran kedua',
            'report_type'      => 'pelanggaran_mitra',
            'category'         => 'layanan',
            'status'           => 'pending',
        ]);

        // Process SP1 from Report #10
        $this->actingAs($this->adminA);
        Livewire::test(PartnerReportShow::class, ['report' => $report10])
            ->set('spTargetUserId', $this->mitra->id)
            ->set('spWarningLevel', 1)
            ->set('spReason', 'Pelanggaran #10')
            ->call('submitInstantSp')
            ->assertSet('showSpModal', false)
            ->assertSee('berhasil diterbitkan');

        $this->assertSame(1, $this->mitra->fresh()->warning_level);

        // Process subsequent SP from a DIFFERENT Report #11
        $this->actingAs($this->adminB);
        Livewire::test(PartnerReportShow::class, ['report' => $report11])
            ->set('spTargetUserId', $this->mitra->id)
            ->set('spWarningLevel', 2)
            ->set('spReason', 'Pelanggaran #11')
            ->call('submitInstantSp')
            ->assertSet('showSpModal', false)
            ->assertSee('berhasil diterbitkan');

        $this->assertSame(2, $this->mitra->fresh()->warning_level, 'Different report must legitimately increment warning level to 2');
        $this->assertSame(1, UserGreylistLog::where('partner_report_id', $report10->id)->count());
        $this->assertSame(1, UserGreylistLog::where('partner_report_id', $report11->id)->count());
        $this->assertSame(2, UserGreylistLog::where('user_id', $this->mitra->id)->count());
    }

    public function test_c9_database_unique_constraint_enforces_one_greylist_log_per_report(): void
    {
        $report = PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra->id,
            'title'            => 'Laporan #12',
            'message'          => 'Pelanggaran untuk uji database constraint',
            'report_type'      => 'pelanggaran_mitra',
            'category'         => 'layanan',
            'status'           => 'pending',
        ]);

        // First insert succeeds
        UserGreylistLog::create([
            'user_id'           => $this->mitra->id,
            'admin_id'          => $this->adminA->id,
            'partner_report_id' => $report->id,
            'action'            => 'warning_issued',
            'warning_level'     => 1,
            'reason'            => 'Uji unik 1',
        ]);

        // Second insert with same partner_report_id MUST be rejected by database unique constraint
        $this->expectException(\Illuminate\Database\QueryException::class);
        UserGreylistLog::create([
            'user_id'           => $this->mitra->id,
            'admin_id'          => $this->adminB->id,
            'partner_report_id' => $report->id,
            'action'            => 'warning_issued',
            'warning_level'     => 1,
            'reason'            => 'Uji unik 2',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // C10: PROFILE TERRITORY MIGRATION STALE AUTHORITY
    // ─────────────────────────────────────────────────────────────────────────

    public function test_c10_profile_migration_stale_authority_re_authorized_against_locked_territory(): void
    {
        // User awalnya di District X (dimiliki Admin A dan Admin B)
        $user = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->districtX->id,
            'city'        => $this->city->name,
            'province'    => $this->province->name,
            'kecamatan'   => $this->districtX->name,
        ]);

        $service = app(ProfileTerritoryMigrationService::class);

        // 1. Admin A memindahkan User dari District X ke District Y (District Y di luar wewenang Admin B)
        $service->migrate(
            $this->adminA,
            $user,
            $this->city->id,
            $this->districtY->id,
            'Migrasi sah oleh Admin A ke District Y'
        );

        $this->assertSame($this->districtY->id, (int) $user->fresh()->district_id);

        // 2. Admin B membuka form sebelum Admin A submit (in-memory masih mencatat District X)
        // Admin B mencoba memindahkan user dari X ke Z (Z dimiliki Admin B)
        $staleUserForB = clone $user;
        $staleUserForB->district_id = $this->districtX->id;

        // Ketika Admin B mencoba migrasi, setelah row $user dikunci dan dibaca bahwa user saat ini berada di Y,
        // sistem HARUS mere-authorisasi Admin B terhadap Y, dan melempar AuthorizationException!
        $this->expectException(AuthorizationException::class);

        $service->migrate(
            $this->adminB,
            $staleUserForB,
            $this->city->id,
            $this->districtZ->id,
            'Admin B mencoba migrasi ilegal dari territory yang tidak lagi dimiliki'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SPECIAL CASES: REFUND APPROVE VS REJECT, VEHICLE, UNLINK
    // ─────────────────────────────────────────────────────────────────────────

    public function test_partner_report_refund_approve_vs_stale_reject_prevents_overwrite(): void
    {
        $customerBalance = UserBalance::create([
            'user_id' => $this->customer->id,
            'balance' => 0,
        ]);

        $help = Help::create([
            'user_id'        => $this->customer->id,
            'mitra_id'       => $this->mitra->id,
            'city_id'        => $this->city->id,
            'district_id'    => $this->districtX->id,
            'title'          => 'Bantuan Fiktif',
            'description'    => 'Deskripsi bantuan fiktif',
            'category'       => 'tenaga_bantuan',
            'amount'         => 75000,
            'total_amount'   => 75000,
            'status'         => Help::STATUS_WAITING_CONFIRMATION,
            'escrow_status'  => Help::ESCROW_STATUS_DISPUTED_FREEZE,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
        ]);

        $report = PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra->id,
            'reported_help_id' => $help->id,
            'title'            => 'Laporan Pekerjaan Fiktif',
            'message'          => 'Mitra tidak datang sama sekali',
            'status'           => 'pending',
            'refund_status'    => 'requested',
            'refund_amount'    => 75000,
        ]);

        $txService = app(HelpTransactionService::class);

        // Admin A approves refund
        $txService->processReportRefund($report, $this->adminA, 'Disetujui penuh');

        $this->assertSame('approved', $report->fresh()->refund_status);
        $this->assertSame(75000.0, (float) $customerBalance->fresh()->balance);
        $this->assertSame(Help::STATUS_DIBATALKAN, $help->fresh()->status);
        $this->assertSame(Help::ESCROW_STATUS_REFUNDED, $help->fresh()->escrow_status);

        // Admin B stale rejects
        try {
            $txService->rejectReportRefund($report, $this->adminB, 'Stale reject attempt');
            $this->fail('Stale reject must throw RuntimeException when refund is already approved');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('telah berubah', $e->getMessage());
        }

        // Pastikan status report tetap approved, saldo customer tidak berubah, dan help tetap dibatalkan
        $this->assertSame('approved', $report->fresh()->refund_status);
        $this->assertSame(75000.0, (float) $customerBalance->fresh()->balance);
        $this->assertSame(Help::STATUS_DIBATALKAN, $help->fresh()->status);
    }

    public function test_partner_report_refund_reject_vs_stale_approve_prevents_wild_refund(): void
    {
        $customerBalance = UserBalance::create([
            'user_id' => $this->customer->id,
            'balance' => 0,
        ]);

        $report = PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra->id,
            'title'            => 'Laporan Pelanggaran',
            'message'          => 'Perilaku buruk',
            'status'           => 'pending',
            'refund_status'    => 'requested',
            'refund_amount'    => 50000,
        ]);

        $txService = app(HelpTransactionService::class);

        // Admin A rejects refund
        $txService->rejectReportRefund($report, $this->adminA, 'Bukti tidak memadai');

        $this->assertSame('rejected', $report->fresh()->refund_status);
        $this->assertSame(0.0, (float) $customerBalance->fresh()->balance);

        // Admin B stale approves
        try {
            $txService->processReportRefund($report, $this->adminB, 'Stale approve attempt');
            $this->fail('Stale approve must throw RuntimeException when refund is already rejected');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('telah berubah', $e->getMessage());
        }

        // Saldo customer tetap 0, status tetap rejected
        $this->assertSame('rejected', $report->fresh()->refund_status);
        $this->assertSame(0.0, (float) $customerBalance->fresh()->balance);
    }

    public function test_vehicle_verification_approve_vs_reject_concurrency(): void
    {
        $mitraUser = User::factory()->create([
            'role'                        => 'mitra',
            'status'                      => 'active',
            'verified'                    => true,
            'city_id'                     => $this->city->id,
            'district_id'                 => $this->districtX->id,
            'vehicle_verified'            => false,
            'vehicle_verification_status' => 'pending',
            'vehicle_plate_number'        => 'AB 1234 CD',
        ]);

        // Admin A approves vehicle
        $this->actingAs($this->adminA);
        Livewire::test(VerificationsIndex::class)
            ->call('approveVehicle', $mitraUser->id);

        $this->assertTrue($mitraUser->fresh()->vehicle_verified);
        $this->assertSame('verified', $mitraUser->fresh()->vehicle_verification_status);

        $logCount = ActivityLog::where('action', 'vehicle_verified')
            ->whereJsonContains('properties->user_id', $mitraUser->id)
            ->count();
        $this->assertSame(1, $logCount);

        // Admin B stale rejects vehicle
        $this->actingAs($this->adminB);
        Livewire::test(VerificationsIndex::class)
            ->set('rejectingVehicleUserId', $mitraUser->id)
            ->set('vehicleRejectReason', 'Foto STNK tidak jelas')
            ->call('confirmRejectVehicle');

        // Status kendaraan HARUS TETAP VERIFIED, tidak boleh dibalikkan ke rejected
        $this->assertTrue($mitraUser->fresh()->vehicle_verified);
        $this->assertSame('verified', $mitraUser->fresh()->vehicle_verification_status);

        $rejectLogCount = ActivityLog::where('action', 'vehicle_rejected')
            ->whereJsonContains('properties->user_id', $mitraUser->id)
            ->count();
        $this->assertSame(0, $rejectLogCount);
    }

    public function test_cancellation_unlink_stale_action_rejected_when_not_pending(): void
    {
        $help = Help::create([
            'user_id'        => $this->customer->id,
            'mitra_id'       => $this->mitra->id,
            'city_id'        => $this->city->id,
            'district_id'    => $this->districtX->id,
            'title'          => 'Tugas Cuci AC',
            'description'    => 'Cuci AC 1 PK',
            'category'       => 'tenaga_bantuan',
            'amount'         => 80000,
            'status'         => Help::STATUS_CUSTOMER_CANCEL_REQUESTED,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
        ]);

        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $help->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'requester_type'  => 'customer',
            'action_type'     => HelpCancelRequest::ACTION_CUSTOMER_WITHDRAW,
            'district_id'     => $this->districtX->id,
            'previous_status' => Help::STATUS_IN_PROGRESS,
            'status'          => HelpCancelRequest::STATUS_APPROVED, // Sudah selesai disetujui sebelumnya
            'settlement_type' => HelpCancelRequest::SETTLEMENT_FULL_REFUND,
            'reason'          => 'Permintaan pembatalan awal',
        ]);

        $service = app(HelpCancellationService::class);

        // Admin B mencoba unlink pada request yang sudah diselesaikan
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('sudah pernah diproses');

        $service->adminUnlinkPartnerAndHoldTask($cancelReq, $this->adminB);
    }
}
