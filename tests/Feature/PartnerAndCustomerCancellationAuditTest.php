<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Models\UserGreylistLog;
use App\Services\HelpCancellationService;
use App\Services\HelpTransactionService;
use App\Services\PartnerDisciplineService;
use App\Services\PartnerOnlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PartnerAndCustomerCancellationAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $partner;
    protected User $admin;
    protected City $city;
    protected District $district;

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
            'name'          => 'Customer Test',
            'role'          => 'customer',
            'city_id'       => $this->city->id,
            'district_id'   => $this->district->id,
            'warning_level' => 0,
        ]);

        $this->partner = User::factory()->create([
            'name'          => 'Mitra Test',
            'role'          => 'mitra',
            'city_id'       => $this->city->id,
            'district_id'   => $this->district->id,
            'warning_level' => 0,
        ]);

        $this->admin = User::factory()->create([
            'name'        => 'Admin Wilayah Danurejan',
            'role'        => 'admin',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);
        $this->admin->managedDistricts()->sync([$this->district->id]);

        PartnerOnlineState::create([
            'user_id'         => $this->partner->id,
            'matching_status' => PartnerOnlineState::STATUS_BUSY,
        ]);
    }

    protected function createTakenHelp(): Help
    {
        return Help::create([
            'user_id'                 => $this->customer->id,
            'mitra_id'                => $this->partner->id,
            'city_id'                 => $this->city->id,
            'district_id'             => $this->district->id,
            'title'                   => 'Bantu Perbaiki Genteng Bocor',
            'description'             => 'Deskripsi pekerjaan perbaikan genteng bocor',
            'amount'                  => 75000,
            'total_amount'            => 77000,
            'status'                  => Help::STATUS_TAKEN,
            'dispatch_mode'           => Help::DISPATCH_MODE_ASSIGNED,
            'escrow_status'           => Help::ESCROW_STATUS_HELD,
            'payment_status'          => Help::PAYMENT_STATUS_PAID,
            'service_type'            => Help::SERVICE_TYPE_ON_SITE,
            'order_mode'              => Help::ORDER_MODE_INSTANT,
            'taken_at'                => now(),
        ]);
    }

    public function test_partner_submits_cancel_request_gets_instantly_released_without_auto_sp(): void
    {
        $help = $this->createTakenHelp();
        $service = app(HelpCancellationService::class);

        $cancelReq = $service->submitPartnerCancelRequest(
            $help,
            $this->partner,
            'Ban motor bocor dan tidak ada tambal ban terdekat',
            'Mohon maaf sekali kepada customer.'
        );

        $help->refresh();
        $this->partner->refresh();
        $partnerState = PartnerOnlineState::where('user_id', $this->partner->id)->first();

        // 1. Status Help directly relists to menunggu_mitra for On-Site service
        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);
        $this->assertNull($help->mitra_id);

        // 2. Partner is released and can search for new orders
        $this->assertNotEquals(PartnerOnlineState::STATUS_BUSY, $partnerState->matching_status);
        $this->assertNull($partnerState->current_help_id);

        // 3. Ticket is pending for admin audit
        $this->assertEquals(HelpCancelRequest::STATUS_PENDING, $cancelReq->status);
        $this->assertEquals(HelpCancelRequest::REQUESTER_PARTNER, $cancelReq->requester_type);

        // 4. Partner warning level is NOT bumped automatically (No auto SP)
        $this->assertEquals(0, (int) $this->partner->warning_level);
        $this->assertDatabaseMissing('user_greylist_logs', [
            'user_id' => $this->partner->id,
            'action'  => 'warning_issued',
        ]);
    }

    public function test_customer_can_cancel_unassigned_help_and_receive_full_refund(): void
    {
        $help = $this->createTakenHelp();
        $help->update(['status' => Help::STATUS_MENUNGGU_MITRA, 'mitra_id' => null]);
        $service = app(HelpCancellationService::class);

        $this->customer->update(['balance' => 0]);

        $service->cancelOrderBeforePartnerTaken($help, $this->customer, 'Customer batal sebelum ada mitra');

        $help->refresh();
        $this->customer->refresh();

        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $help->escrow_status);
        $this->assertGreaterThanOrEqual(75000, $this->customer->balance);
    }

    public function test_admin_reviews_on_site_partner_cancel_and_validates_no_sp(): void
    {
        $help = $this->createTakenHelp();
        $service = app(HelpCancellationService::class);

        $req = $service->submitPartnerCancelRequest(
            $help,
            $this->partner,
            'Kecelakaan di jalan',
            'Disertai foto luka dan motor rusak'
        );

        $service->reviewByAdmin(
            $req,
            $this->admin,
            true,
            HelpCancelRequest::SETTLEMENT_RELIST_POOL,
            [
                'sp_target'   => HelpCancelRequest::SP_TARGET_NONE,
                'admin_notes' => 'Bukti foto kecelakaan valid dan terverifikasi.',
            ]
        );

        $help->refresh();
        $req->refresh();
        $this->partner->refresh();

        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);
        $this->assertEquals(HelpCancelRequest::STATUS_APPROVED, $req->status);
        $this->assertEquals(HelpCancelRequest::AUDIT_VALID_NO_SP, $req->audit_decision);
        $this->assertEquals(0, (int) $this->partner->warning_level); // Bebas SP
    }

    public function test_admin_penalizes_lying_partner_with_sp(): void
    {
        $help = $this->createTakenHelp();
        $service = app(HelpCancellationService::class);

        $req = $service->submitPartnerCancelRequest(
            $help,
            $this->partner,
            'Alasan palsu / berbohong',
            'Tidak ada foto bukti'
        );

        $service->reviewByAdmin(
            $req,
            $this->admin,
            true,
            HelpCancelRequest::SETTLEMENT_RELIST_POOL,
            [
                'sp_target'         => HelpCancelRequest::SP_TARGET_PARTNER,
                'partner_sp_level'  => 1,
                'partner_sp_reason' => 'Terbukti berbohong dalam klaim ketidaksanggupan',
                'admin_notes'       => 'Mitra mengaku ban bocor tapi posisi GPS di rumah.',
            ]
        );

        $help->refresh();
        $req->refresh();
        $this->partner->refresh();

        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);
        $this->assertEquals(HelpCancelRequest::STATUS_APPROVED, $req->status);
        $this->assertEquals(HelpCancelRequest::AUDIT_PENALTY_ISSUED, $req->audit_decision);
        $this->assertEquals(1, (int) $this->partner->warning_level);
        $this->assertTrue($this->partner->is_greylisted);

        $this->assertDatabaseHas('user_greylist_logs', [
            'user_id'       => $this->partner->id,
            'admin_id'      => $this->admin->id,
            'warning_level' => 1,
        ]);
    }

    public function test_customer_cancel_flow_with_partner_clarification_and_admin_dual_sp(): void
    {
        $help = $this->createTakenHelp();
        $service = app(HelpCancellationService::class);

        // 1. Customer submits cancel
        $req = $service->submitCustomerCancelRequest(
            $help,
            $this->customer,
            'Mitra tidak kunjung datang dan order mencurigakan',
            'Sudah menunggu 1 jam'
        );

        $help->refresh();
        $this->assertEquals(Help::STATUS_CUSTOMER_CANCEL_REQUESTED, $help->status);
        $this->assertEquals('customer', $help->cancel_requested_by);

        // 2. Partner provides optional clarification
        $service->submitPartnerClarification(
            $req,
            $this->partner,
            'Saya mengalami kendala macet parah di jembatan layang'
        );

        $req->refresh();
        $this->assertEquals('Saya mengalami kendala macet parah di jembatan layang', $req->partner_clarification);

        // 3. Admin reviews and detects both parties involved in violation -> issue SP to both
        $service->reviewByAdmin(
            $req,
            $this->admin,
            true,
            HelpCancelRequest::SETTLEMENT_FULL_REFUND,
            [
                'sp_target'          => HelpCancelRequest::SP_TARGET_BOTH,
                'partner_sp_level'   => 1,
                'partner_sp_reason'  => 'Mitra mangkir dan tidak segera memberi kabar ke pemesan',
                'customer_sp_level'  => 1,
                'customer_sp_reason' => 'Customer terindikasi membuat transaksi tidak wajar',
                'admin_notes'        => 'Kedua user diberikan SP 1 untuk pembinaan.',
            ]
        );

        $help->refresh();
        $this->partner->refresh();
        $this->customer->refresh();

        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->status);
        $this->assertEquals(1, (int) $this->partner->warning_level);
        $this->assertEquals(1, (int) $this->customer->warning_level);

        $this->assertDatabaseHas('user_greylist_logs', [
            'user_id'  => $this->partner->id,
            'admin_id' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('user_greylist_logs', [
            'user_id'  => $this->customer->id,
            'admin_id' => $this->admin->id,
        ]);
    }

    public function test_customer_livewire_cancel_help_unassigned_on_site(): void
    {
        $this->actingAs($this->customer);

        $help = Help::create([
            'user_id'                 => $this->customer->id,
            'mitra_id'                => null,
            'city_id'                 => $this->city->id,
            'district_id'             => $this->district->id,
            'title'                   => 'Bantu Bersihkan Halaman',
            'description'             => 'Deskripsi',
            'amount'                  => 50000,
            'total_amount'            => 52000,
            'status'                  => Help::STATUS_MENUNGGU_MITRA,
            'dispatch_mode'           => Help::DISPATCH_MODE_POOL,
            'escrow_status'           => Help::ESCROW_STATUS_HELD,
            'payment_status'          => Help::PAYMENT_STATUS_PAID,
            'service_type'            => Help::SERVICE_TYPE_ON_SITE,
            'order_mode'              => Help::ORDER_MODE_INSTANT,
        ]);

        \App\Models\UserBalance::updateOrCreate(
            ['user_id' => $this->customer->id],
            ['balance' => 0]
        );

        \Livewire\Livewire::test(\App\Livewire\Customer\Helps\Detail::class, ['id' => $help->id])
            ->call('cancelHelp')
            ->assertRedirect(route('customer.helps.index'))
            ->assertSessionHas('success');

        $help->refresh();
        $userBalance = \App\Models\UserBalance::where('user_id', $this->customer->id)->first();

        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $help->escrow_status);
        $this->assertEquals(52000, (float) $userBalance->balance);
    }

    public function test_customer_livewire_cancel_help_unassigned_pickup_delivery(): void
    {
        $this->actingAs($this->customer);

        $help = Help::create([
            'user_id'                 => $this->customer->id,
            'mitra_id'                => null,
            'city_id'                 => $this->city->id,
            'district_id'             => $this->district->id,
            'title'                   => 'Antar Makanan Ringan',
            'description'             => 'Deskripsi antar makanan',
            'amount'                  => 15000,
            'service_fee'             => 15000,
            'total_amount'            => 17000,
            'status'                  => Help::STATUS_MENUNGGU_MITRA,
            'dispatch_mode'           => Help::DISPATCH_MODE_POOL,
            'escrow_status'           => Help::ESCROW_STATUS_HELD,
            'payment_status'          => Help::PAYMENT_STATUS_PAID,
            'service_type'            => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'order_mode'              => Help::ORDER_MODE_INSTANT,
        ]);

        \App\Models\UserBalance::updateOrCreate(
            ['user_id' => $this->customer->id],
            ['balance' => 0]
        );

        \Livewire\Livewire::test(\App\Livewire\Customer\Helps\Detail::class, ['id' => $help->id])
            ->call('cancelHelp')
            ->assertRedirect(route('customer.helps.index'))
            ->assertSessionHas('success');

        $help->refresh();
        $userBalance = \App\Models\UserBalance::where('user_id', $this->customer->id)->first();

        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $help->escrow_status);
        $this->assertEquals(17000, (float) $userBalance->balance);

        $this->assertDatabaseHas('help_cancel_requests', [
            'help_id'        => $help->id,
            'partner_id'     => null,
            'customer_id'    => $this->customer->id,
            'status'         => HelpCancelRequest::STATUS_APPROVED,
        ]);
    }

    public function test_admin_reviews_customer_cancel_and_relists_to_pool_switching_slow_partner(): void
    {
        $help = $this->createTakenHelp();
        $service = app(HelpCancellationService::class);

        $req = $service->submitCustomerCancelRequest(
            $help,
            $this->customer,
            'Mitra tidak kunjung merespons / terlalu lambat',
            'Ingin ganti mitra lain yang aktif'
        );

        $service->reviewByAdmin(
            $req,
            $this->admin,
            true,
            HelpCancelRequest::SETTLEMENT_RELIST_POOL,
            [
                'sp_target'         => HelpCancelRequest::SP_TARGET_PARTNER,
                'partner_sp_level'  => 1,
                'partner_sp_reason' => 'Mitra lambat dan tidak merespons pesanan customer',
                'admin_notes'       => 'Mitra lama dilepas dan diberi SP 1, pesanan dikembalikan ke pool.',
            ]
        );

        $help->refresh();
        $req->refresh();
        $this->partner->refresh();

        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);
        $this->assertNull($help->mitra_id);
        $this->assertEquals(HelpCancelRequest::STATUS_APPROVED, $req->status);
        $this->assertEquals(HelpCancelRequest::SETTLEMENT_RELIST_POOL, $req->settlement_type);
        $this->assertEquals(1, (int) $this->partner->warning_level);
    }

    public function test_admin_disputes_livewire_partner_cancel_modal_audit(): void
    {
        $this->actingAs($this->admin);

        $help = $this->createTakenHelp();
        $service = app(HelpCancellationService::class);

        $req = $service->submitPartnerCancelRequest(
            $help,
            $this->partner,
            'Kendaraan mogok di jalan',
            'Motor mati mendadak'
        );

        \Livewire\Livewire::test(\App\Livewire\Admin\Disputes\Index::class)
            ->call('openCancelReviewModal', $req->id)
            ->assertSet('showCancelReviewModal', true)
            ->assertSet('settlementType', 'relist_pool')
            ->assertSet('spTarget', 'none')
            ->call('executeCancelReview')
            ->assertHasNoErrors()
            ->assertSet('showCancelReviewModal', false);

        $req->refresh();
        $this->assertEquals(HelpCancelRequest::STATUS_APPROVED, $req->status);
        $this->assertEquals(HelpCancelRequest::SETTLEMENT_RELIST_POOL, $req->settlement_type);
        $this->assertEquals(HelpCancelRequest::AUDIT_VALID_NO_SP, $req->audit_decision);
    }

    public function test_admin_disputes_livewire_customer_cancel_modal_audit(): void
    {
        $this->actingAs($this->admin);

        $help = $this->createTakenHelp();
        $service = app(HelpCancellationService::class);

        $req = $service->submitCustomerCancelRequest(
            $help,
            $this->customer,
            'Sudah tidak membutuhkan bantuan',
            'Ingin menarik kembali saldo'
        );

        \Livewire\Livewire::test(\App\Livewire\Admin\Disputes\Index::class)
            ->call('openCancelReviewModal', $req->id)
            ->assertSet('showCancelReviewModal', true)
            ->call('executeCancelReview')
            ->assertHasNoErrors()
            ->assertSet('showCancelReviewModal', false);

        $req->refresh();
        $help->refresh();
        $this->assertEquals(HelpCancelRequest::STATUS_APPROVED, $req->status);
        $this->assertEquals(HelpCancelRequest::SETTLEMENT_FULL_REFUND, $req->settlement_type);
        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->status);
    }

    /**
     * Alur Pergantian Mitra (Switch Partner) Canonical:
     * 1. Customer mengajukan ganti mitra -> status menjadi 'customer_cancel_requested', mitra lama tetap terpasang sementara.
     * 2. Mitra merespons & menyetujui -> mitra dilepas dari BUSY, diexclude agar tidak mengambil order yang sama,
     *    order direlist ke pool ('menunggu_mitra') di wilayah aktif, dan mitra baru dapat mengambil order tersebut.
     */
    public function test_customer_can_switch_partner_instantly(): void
    {
        $help = $this->createTakenHelp();
        $service = app(HelpCancellationService::class);

        // 1. Customer mengajukan pergantian mitra
        $cancelReq = $service->switchPartnerByCustomer(
            $help,
            $this->customer,
            'Mitra tidak bergerak / tidak kunjung datang',
            'Sudah menunggu 30 menit tanpa kabar'
        );

        $help->refresh();
        $this->partner->refresh();
        $partnerState = PartnerOnlineState::where('user_id', $this->partner->id)->first();

        // Status awal: order berstatus customer_cancel_requested, mitra lama masih terikat sementara
        $this->assertEquals(Help::STATUS_CUSTOMER_CANCEL_REQUESTED, $help->status);
        $this->assertEquals($this->partner->id, $help->mitra_id);
        $this->assertEquals(PartnerOnlineState::STATUS_BUSY, $partnerState->matching_status);

        // Tiket permohonan pergantian mitra tercipta
        $this->assertEquals(HelpCancelRequest::ACTION_SWITCH_PARTNER, $cancelReq->action_type);
        $this->assertEquals(HelpCancelRequest::STATUS_PENDING, $cancelReq->status);
        $this->assertEquals(HelpCancelRequest::PARTNER_RESPONSE_PENDING, $cancelReq->partner_response_type);
        $this->assertEquals(HelpCancelRequest::SETTLEMENT_RELIST_POOL, $cancelReq->settlement_type);

        // 2. Mitra merespons dan menyetujui pengalihan tugas
        $response = $service->respondWithdrawByPartner(
            $cancelReq,
            $this->partner,
            true,
            'Saya setuju mengalihkan pesanan ke mitra lain.'
        );

        $this->assertTrue($response['success']);
        $this->assertTrue($response['confirmed']);

        $help->refresh();
        $cancelReq->refresh();
        $this->partner->refresh();
        $partnerState = PartnerOnlineState::where('user_id', $this->partner->id)->first();

        // 3. Setelah mitra setuju di wilayah aktif: order direlist ke pool, mitra lama dilepas & diexclude
        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);
        $this->assertNull($help->mitra_id);
        $this->assertEquals(Help::DISPATCH_MODE_POOL, $help->dispatch_mode);

        $this->assertFalse((bool) $partnerState->is_busy);
        $this->assertDatabaseHas('help_partner_exclusions', [
            'help_id'  => $help->id,
            'mitra_id' => $this->partner->id,
        ]);
        $this->assertTrue($help->hasCancelledBy($this->partner->id));
        $this->assertFalse($help->canBeTakenBy($this->partner));

        // Tiket disetujui dengan settlement relist pool
        $this->assertEquals(HelpCancelRequest::STATUS_APPROVED, $cancelReq->status);
        $this->assertEquals(HelpCancelRequest::PARTNER_RESPONSE_CONFIRMED, $cancelReq->partner_response_type);
        $this->assertEquals(HelpCancelRequest::SETTLEMENT_RELIST_POOL, $cancelReq->settlement_type);

        // 4. Verifikasi rematch: mitra baru dapat mengambil order dari pool
        $newPartner = User::factory()->create([
            'role'        => 'mitra',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);
        PartnerOnlineState::create([
            'user_id'         => $newPartner->id,
            'matching_status' => PartnerOnlineState::STATUS_ONLINE,
            'latitude'        => -7.7956,
            'longitude'       => 110.3695,
        ]);

        $this->assertTrue($help->canBeTakenBy($newPartner));
        app(HelpTransactionService::class)->takeHelp($help, $newPartner);

        $help->refresh();
        $this->assertEquals(Help::STATUS_TAKEN, $help->status);
        $this->assertEquals($newPartner->id, $help->mitra_id);
    }

    /**
     * Skenario Wilayah Nonaktif (Phase 2 Region Lifecycle Guard):
     * Jika pergantian mitra disetujui saat wilayah sedang nonaktif,
     * sistem TIDAK boleh me-relist order ke pool, melainkan membatalkan order dan refund 100% penuh.
     */
    public function test_switch_partner_when_region_inactive_cancels_and_refunds_without_rematch(): void
    {
        $help = $this->createTakenHelp();
        $service = app(HelpCancellationService::class);

        \App\Models\UserBalance::updateOrCreate(
            ['user_id' => $this->customer->id],
            ['balance' => 0]
        );

        $cancelReq = $service->switchPartnerByCustomer(
            $help,
            $this->customer,
            'Mitra macet di perjalanan',
            'Ingin ganti mitra'
        );

        $help->refresh();
        $this->assertEquals(Help::STATUS_CUSTOMER_CANCEL_REQUESTED, $help->status);

        // Nonaktifkan wilayah (Phase 2 Region Lifecycle)
        $this->district->update(['is_active' => false]);

        // Mitra merespons setuju
        $result = $service->respondWithdrawByPartner(
            $cancelReq,
            $this->partner,
            true,
            'Saya setuju dialihkan'
        );

        $this->assertTrue($result['success']);
        $this->assertTrue($result['cancelled']);
        $this->assertTrue($result['refunded']);

        $help->refresh();
        $cancelReq->refresh();
        $this->partner->refresh();

        // 1. Order dibatalkan dan ditutup, TIDAK kembali ke pool
        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->status);
        $this->assertEquals(Help::DISPATCH_MODE_CLOSED, $help->dispatch_mode);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $help->escrow_status);

        // 2. Mitra lama dilepas dan diexclude
        $partnerState = PartnerOnlineState::where('user_id', $this->partner->id)->first();
        $this->assertFalse((bool) $partnerState->is_busy);
        $this->assertTrue($help->hasCancelledBy($this->partner->id));

        // 3. Saldo customer dikembalikan penuh (100%)
        $userBalance = \App\Models\UserBalance::where('user_id', $this->customer->id)->first();
        $this->assertEquals(77000, (float) $userBalance->balance);

        // 4. Idempotency guard: pemanggilan refund ulang tidak menyebabkan double refund
        app(\App\Services\Cancellation\CancellationSettlementService::class)->processFullRefund($help, 'Check repeat');
        $userBalance->refresh();
        $this->assertEquals(77000, (float) $userBalance->balance);
    }

    public function test_partner_confirms_withdrawal_and_refunds_100_percent(): void
    {
        $help = $this->createTakenHelp();
        $service = app(HelpCancellationService::class);

        $this->customer->update(['balance' => 0]);

        $cancelReq = $service->submitCustomerCancelRequest(
            $help,
            $this->customer,
            'Perubahan rencana mendesak',
            'Tidak jadi membutuhkan bantuan'
        );

        $help->refresh();
        $this->assertEquals(Help::STATUS_CUSTOMER_CANCEL_REQUESTED, $help->status);

        // Partner confirms withdrawal
        $result = $service->respondWithdrawByPartner(
            $cancelReq,
            $this->partner,
            true,
            'Saya setuju membatalkan pesanan.'
        );

        $this->assertTrue($result['success']);
        $this->assertTrue($result['confirmed']);

        $help->refresh();
        $cancelReq->refresh();
        $this->customer->refresh();

        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->status);
        $this->assertEquals(HelpCancelRequest::STATUS_APPROVED, $cancelReq->status);
        $this->assertEquals(HelpCancelRequest::PARTNER_RESPONSE_CONFIRMED, $cancelReq->partner_response_type);
        $this->assertGreaterThanOrEqual(77000, $this->customer->balance);
    }

    public function test_partner_rejects_withdrawal_with_objection_for_admin_jury(): void
    {
        $help = $this->createTakenHelp();
        $service = app(HelpCancellationService::class);

        $cancelReq = $service->submitCustomerCancelRequest(
            $help,
            $this->customer,
            'Customer ingin batal sepihak',
            'Minta batal padahal mitra sudah jalan'
        );

        // Partner rejects withdrawal with notes
        $result = $service->respondWithdrawByPartner(
            $cancelReq,
            $this->partner,
            false,
            'Saya sudah di jalan menempuh 4 km dan membawa alat lengkap'
        );

        $this->assertTrue($result['success']);
        $this->assertFalse($result['confirmed']);

        $help->refresh();
        $cancelReq->refresh();

        // Status stays customer_cancel_requested waiting for Admin review
        $this->assertEquals(Help::STATUS_CUSTOMER_CANCEL_REQUESTED, $help->status);
        $this->assertEquals(HelpCancelRequest::STATUS_PENDING, $cancelReq->status);
        $this->assertEquals(HelpCancelRequest::PARTNER_RESPONSE_REJECTED, $cancelReq->partner_response_type);
        $this->assertEquals('Saya sudah di jalan menempuh 4 km dan membawa alat lengkap', $cancelReq->partner_response_notes);
    }

    public function test_admin_disputes_livewire_arbitrates_rejected_withdrawal(): void
    {
        $this->actingAs($this->admin);

        $help = $this->createTakenHelp();
        $service = app(HelpCancellationService::class);

        $cancelReq = $service->submitCustomerCancelRequest(
            $help,
            $this->customer,
            'Customer ingin batal sepihak',
            'Minta batal padahal mitra sudah jalan'
        );

        $service->respondWithdrawByPartner(
            $cancelReq,
            $this->partner,
            false,
            'Saya sudah di jalan menempuh 4 km'
        );

        $cancelReq->update([
            'partner_moved_km' => 4.2,
        ]);

        \Livewire\Livewire::test(\App\Livewire\Admin\Disputes\Index::class)
            ->call('openCancelReviewModal', $cancelReq->id)
            ->assertSet('showCancelReviewModal', true)
            ->assertSet('settlementType', 'partial_settlement')
            ->set('cancelPartnerAmount', 20000)
            ->set('cancelRefundAmount', 57000)
            ->set('spTarget', 'customer')
            ->set('customerSpLevel', 1)
            ->set('customerSpReason', 'Pembatalan sepihak setelah mitra menempuh perjalanan')
            ->call('executeCancelReview')
            ->assertHasNoErrors()
            ->assertSet('showCancelReviewModal', false);

        $help->refresh();
        $cancelReq->refresh();
        $this->customer->refresh();

        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->status);
        $this->assertEquals(HelpCancelRequest::STATUS_APPROVED, $cancelReq->status);
        $this->assertEquals(1, (int) $this->customer->warning_level);
        $this->assertEquals(20000, (float) $cancelReq->payout_amount_mitra);
        $this->assertEquals(57000, (float) $cancelReq->refund_amount_customer);
    }
}

