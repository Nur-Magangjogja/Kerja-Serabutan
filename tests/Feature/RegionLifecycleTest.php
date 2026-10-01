<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpDispatch;
use App\Models\PartnerOnlineState;
use App\Models\Province;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\Cancellation\CancellationSettlementService;
use App\Services\Cancellation\OnSiteCancellationService;
use App\Services\HelpCancellationService;
use App\Services\HelpCreationService;
use App\Services\HelpTransactionService;
use App\Services\RegionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;
    protected City $city;
    protected District $district;
    protected User $customer;
    protected User $mitra;
    protected HelpCreationService $creationService;
    protected HelpTransactionService $transactionService;
    protected RegionService $regionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->province = Province::create([
            'name'      => 'DI Yogyakarta',
            'is_active' => true,
        ]);

        $this->city = City::create([
            'name'        => 'Kota Yogyakarta',
            'province'    => 'DI Yogyakarta',
            'province_id' => $this->province->id,
            'latitude'    => -7.7956,
            'longitude'   => 110.3695,
            'is_active'   => true,
        ]);

        $this->district = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Danurejan',
            'latitude'  => -7.7930,
            'longitude' => 110.3700,
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'name'          => 'Customer Test',
            'role'          => 'customer',
            'city_id'       => $this->city->id,
            'district_id'   => $this->district->id,
            'warning_level' => 0,
            'status'        => 'active',
        ]);

        // Berikan saldo customer untuk escrow
        UserBalance::create([
            'user_id' => $this->customer->id,
            'balance' => 500000,
        ]);

        $this->mitra = User::factory()->create([
            'name'                       => 'Mitra Test',
            'role'                       => 'mitra',
            'city_id'                    => $this->city->id,
            'district_id'                => $this->district->id,
            'warning_level'              => 0,
            'status'                     => 'active',
            'verified'                   => true,
            'vehicle_verification_status'=> 'approved',
        ]);

        PartnerOnlineState::create([
            'user_id'         => $this->mitra->id,
            'matching_status' => PartnerOnlineState::STATUS_SEARCHING,
            'latitude'        => -7.7930,
            'longitude'       => 110.3700,
        ]);

        $this->creationService    = app(HelpCreationService::class);
        $this->transactionService = app(HelpTransactionService::class);
        $this->regionService      = app(RegionService::class);
    }

    private function createOrder(
        string $status = Help::STATUS_MENUNGGU_MITRA,
        string $dispatchMode = Help::DISPATCH_MODE_POOL,
        ?int $mitraId = null,
        float $amount = 50000,
        float $fee = 5000
    ): Help {
        return Help::create([
            'user_id'             => $this->customer->id,
            'mitra_id'            => $mitraId,
            'city_id'             => $this->city->id,
            'district_id'         => $this->district->id,
            'title'               => 'Bantuan Reparasi Pintu',
            'description'         => 'Perbaikan engsel pintu rusak',
            'amount'              => $amount,
            'admin_fee'           => $fee,
            'total_amount'        => $amount + $fee,
            'latitude'            => -7.7956,
            'longitude'           => 110.3695,
            'status'              => $status,
            'escrow_status'       => Help::ESCROW_STATUS_HELD,
            'payment_status'      => Help::PAYMENT_STATUS_PAID,
            'dispatch_mode'       => $dispatchMode,
            'service_type'        => Help::SERVICE_TYPE_ON_SITE,
            'order_mode'          => Help::ORDER_MODE_INSTANT,
            'model_version'       => 2,
            'mitra_earning'       => $amount,
            'platform_fee_amount' => $fee,
            'taken_at'            => $mitraId ? now() : null,
        ]);
    }

    /**
     * 1. District OFF -> create gagal.
     */
    public function test_district_off_rejects_help_creation(): void
    {
        $this->district->update(['is_active' => false]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('dinonaktifkan sementara');

        $this->creationService->createHelp($this->customer, [
            'title'        => 'Bantu Cat Dinding',
            'description'  => 'Cat dinding kamar',
            'service_type' => Help::SERVICE_TYPE_ON_SITE,
            'order_mode'   => Help::ORDER_MODE_INSTANT,
            'city_id'      => $this->city->id,
            'district_id'  => $this->district->id,
            'address'      => 'Jl. Malioboro No. 1',
            'latitude'     => -7.7930,
            'longitude'    => 110.3700,
            'amount'       => 75000,
        ]);
    }

    /**
     * 2. City OFF -> child district create gagal.
     */
    public function test_city_off_rejects_help_creation_for_child_district(): void
    {
        $this->city->update(['is_active' => false]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('dinonaktifkan sementara');

        $this->creationService->createHelp($this->customer, [
            'title'        => 'Bantu Cuci AC',
            'description'  => 'Cuci 1 unit AC kamar',
            'service_type' => Help::SERVICE_TYPE_ON_SITE,
            'order_mode'   => Help::ORDER_MODE_INSTANT,
            'city_id'      => $this->city->id,
            'district_id'  => $this->district->id,
            'address'      => 'Jl. Danurejan No. 10',
            'latitude'     => -7.7930,
            'longitude'    => 110.3700,
            'amount'       => 80000,
        ]);
    }

    /**
     * 3. Province OFF -> semua child create gagal.
     */
    public function test_province_off_rejects_help_creation_for_all_children(): void
    {
        $this->province->update(['is_active' => false]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('dinonaktifkan sementara');

        $this->creationService->createHelp($this->customer, [
            'title'        => 'Bantu Bersih Rumah',
            'description'  => 'Sapu dan pel rumah',
            'service_type' => Help::SERVICE_TYPE_ON_SITE,
            'order_mode'   => Help::ORDER_MODE_INSTANT,
            'city_id'      => $this->city->id,
            'district_id'  => $this->district->id,
            'address'      => 'Jl. Sosrowijayan No. 5',
            'latitude'     => -7.7930,
            'longitude'    => 110.3700,
            'amount'       => 50000,
        ]);
    }

    /**
     * 4. Pending pool + OFF -> cancel + refund.
     */
    public function test_pending_pool_order_cancelled_and_refunded_when_region_deactivated(): void
    {
        $order = $this->createOrder(
            Help::STATUS_MENUNGGU_MITRA,
            Help::DISPATCH_MODE_POOL,
            null,
            50000,
            5000
        );

        $initialBalance = (float) $this->customer->userBalance->fresh()->balance;

        // Deaktivasi wilayah
        $cancelledCount = $this->regionService->cancelAndRefundUntakenOrdersInRegion('city', $this->city->id);

        $this->assertEquals(1, $cancelledCount);

        $order->refresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $order->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $order->escrow_status);
        $this->assertEquals(Help::PAYMENT_STATUS_REFUNDED, $order->payment_status);
        $this->assertEquals(Help::DISPATCH_MODE_CLOSED, $order->dispatch_mode);

        $newBalance = (float) $this->customer->userBalance->fresh()->balance;
        $this->assertEquals($initialBalance + 55000, $newBalance);
    }

    /**
     * 5. Seeking + OFF -> cancel + refund.
     */
    public function test_seeking_order_cancelled_and_refunded_when_region_deactivated(): void
    {
        $order = $this->createOrder(
            Help::STATUS_MENUNGGU_MITRA,
            Help::DISPATCH_MODE_SEEKING,
            null,
            60000,
            6000
        );

        $initialBalance = (float) $this->customer->userBalance->fresh()->balance;

        $cancelledCount = $this->regionService->cancelAndRefundUntakenOrdersInRegion('district', $this->district->id);

        $this->assertEquals(1, $cancelledCount);

        $order->refresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $order->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $order->escrow_status);

        $newBalance = (float) $this->customer->userBalance->fresh()->balance;
        $this->assertEquals($initialBalance + 66000, $newBalance);
    }

    /**
     * 6. Offered + OFF -> cancel + refund + dispatch cancelled.
     */
    public function test_offered_order_cancelled_and_refunded_with_dispatch_cancelled(): void
    {
        $order = $this->createOrder(
            Help::STATUS_MENUNGGU_MITRA,
            Help::DISPATCH_MODE_OFFERED,
            null,
            70000,
            7000
        );

        $dispatch = HelpDispatch::create([
            'help_id'    => $order->id,
            'mitra_id'   => $this->mitra->id,
            'status'     => HelpDispatch::STATUS_OFFERED,
            'round'      => 1,
            'rank'       => 1,
            'offered_at' => now(),
            'expires_at' => now()->addSeconds(45),
        ]);

        $cancelledCount = $this->regionService->cancelAndRefundUntakenOrdersInRegion('province', $this->province->id);

        $this->assertEquals(1, $cancelledCount);

        $order->refresh();
        $dispatch->refresh();

        $this->assertEquals(Help::STATUS_DIBATALKAN, $order->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $order->escrow_status);
        $this->assertEquals(HelpDispatch::STATUS_CANCELLED, $dispatch->status);
    }

    /**
     * 7. Stale UI + takeHelp setelah OFF -> gagal.
     */
    public function test_stale_ui_take_help_rejected_after_region_deactivated(): void
    {
        $order = $this->createOrder(
            Help::STATUS_MENUNGGU_MITRA,
            Help::DISPATCH_MODE_POOL,
            null
        );

        // Simulasi admin menonaktifkan kecamatan sebelum mitra klik "Ambil"
        $this->district->update(['is_active' => false]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('dinonaktifkan');

        $this->transactionService->takeHelp($order, $this->mitra, -7.7930, 110.3700);

        $order->refresh();
        $this->assertNull($order->mitra_id);
    }

    /**
     * 8. Taken + OFF -> tetap bisa lanjut (markOnTheWay).
     */
    public function test_taken_order_can_continue_after_region_deactivated(): void
    {
        $order = $this->createOrder(
            Help::STATUS_TAKEN,
            Help::DISPATCH_MODE_ASSIGNED,
            $this->mitra->id
        );

        // Deaktivasi wilayah
        $this->city->update(['is_active' => false]);
        $this->district->update(['is_active' => false]);

        // Region nonaktif tidak boleh menghalangi pekerjaan yang sudah diambil
        $this->transactionService->markOnTheWay($order, $this->mitra);

        $order->refresh();
        $this->assertEquals(Help::STATUS_PARTNER_ON_THE_WAY, $order->status);
        $this->assertNotNull($order->partner_started_at);
    }

    /**
     * 9. Partner on the way + OFF -> tetap bisa lanjut (markArrived).
     */
    public function test_partner_on_the_way_order_can_continue_after_region_deactivated(): void
    {
        $order = $this->createOrder(
            Help::STATUS_PARTNER_ON_THE_WAY,
            Help::DISPATCH_MODE_ASSIGNED,
            $this->mitra->id
        );
        $order->update(['partner_started_at' => now()]);

        // Deaktivasi wilayah
        $this->city->update(['is_active' => false]);

        // Mitra dapat menandai tiba di lokasi
        $this->transactionService->markArrived($order, $this->mitra);

        $order->refresh();
        $this->assertEquals(Help::STATUS_PARTNER_ARRIVED, $order->status);
    }

    /**
     * 10. In progress + OFF -> tetap bisa selesai.
     */
    public function test_in_progress_order_can_be_completed_after_region_deactivated(): void
    {
        $order = $this->createOrder(
            Help::STATUS_IN_PROGRESS,
            Help::DISPATCH_MODE_ASSIGNED,
            $this->mitra->id,
            100000,
            10000
        );

        // Deaktivasi wilayah
        $this->province->update(['is_active' => false]);
        $this->city->update(['is_active' => false]);

        // Mitra submit completion
        $this->transactionService->submitCompletion($order, $this->mitra, 'dummy_proof.jpg', 'Pekerjaan selesai rapi');
        $order->refresh();
        $this->assertEquals(Help::STATUS_WAITING_CONFIRMATION, $order->status);

        // Customer konfirmasi selesai
        $this->transactionService->customerConfirmCompletion($order, $this->customer);

        $order->refresh();
        $this->assertEquals(Help::STATUS_SELESAI, $order->status);
        $this->assertEquals(Help::ESCROW_STATUS_RELEASED, $order->escrow_status);
    }

    /**
     * 11. Taken + OFF + Mitra cancel -> tidak rematch ke pool, langsung cancel + full refund.
     */
    public function test_taken_order_partner_cancelled_after_region_off_does_not_rematch(): void
    {
        $order = $this->createOrder(
            Help::STATUS_TAKEN,
            Help::DISPATCH_MODE_ASSIGNED,
            $this->mitra->id,
            75000,
            7500
        );

        app(\App\Services\PartnerOnlineService::class)->setBusy($this->mitra->id, $order->id);

        $initialCustomerBalance = (float) $this->customer->userBalance->fresh()->balance;

        // Nonaktifkan wilayah
        $this->district->update(['is_active' => false]);

        // Mitra membatalkan saat perjalanan (Konsep 1)
        $onSiteService = app(OnSiteCancellationService::class);
        $result = $onSiteService->cancelByPartner(
            $order,
            $this->mitra,
            'Motor mogok parah'
        );

        $this->assertTrue($result['success']);
        $this->assertFalse($result['relisted'], 'Order tidak boleh di-relist ke pool karena wilayah nonaktif');
        $this->assertTrue($result['cancelled']);
        $this->assertTrue($result['refunded']);

        $order->refresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $order->status, 'Order harus dibatalkan');
        $this->assertNotEquals(Help::STATUS_MENUNGGU_MITRA, $order->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $order->escrow_status);

        // Dana kembali utuh ke customer
        $newCustomerBalance = (float) $this->customer->userBalance->fresh()->balance;
        $this->assertEquals($initialCustomerBalance + 82500, $newCustomerBalance);

        // Mitra tidak lagi BUSY
        $partnerState = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertNotEquals(PartnerOnlineState::STATUS_BUSY, $partnerState->matching_status);
    }

    /**
     * 12. OFF -> ON -> cancelled order tidak hidup lagi.
     */
    public function test_re_activating_region_does_not_revive_cancelled_orders(): void
    {
        $order = $this->createOrder(
            Help::STATUS_MENUNGGU_MITRA,
            Help::DISPATCH_MODE_POOL,
            null
        );

        // OFF
        $this->city->update(['is_active' => false]);
        $this->regionService->cancelAndRefundUntakenOrdersInRegion('city', $this->city->id);

        $order->refresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $order->status);

        // ON kembali
        $this->city->update(['is_active' => true]);

        // Order yang dibatalkan harus tetap dibatalkan
        $order->refresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $order->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $order->escrow_status);
    }

    /**
     * 13. Safety-net command dijalankan 2x -> tidak double refund.
     */
    public function test_safety_net_command_run_twice_is_idempotent_without_double_refund(): void
    {
        $order = $this->createOrder(
            Help::STATUS_MENUNGGU_MITRA,
            Help::DISPATCH_MODE_POOL,
            null,
            50000,
            5000
        );

        $initialBalance = (float) $this->customer->userBalance->fresh()->balance;

        // Nonaktifkan distrik tanpa men-trigger cancel langsung
        $this->district->update(['is_active' => false]);

        // Jalankan safety net command run 1
        $exitCode1 = Artisan::call('helps:cancel-inactive-regions');
        $this->assertEquals(0, $exitCode1);

        $order->refresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $order->status);
        $balanceAfterRun1 = (float) $this->customer->userBalance->fresh()->balance;
        $this->assertEquals($initialBalance + 55000, $balanceAfterRun1);

        // Jalankan safety net command run 2
        $exitCode2 = Artisan::call('helps:cancel-inactive-regions');
        $this->assertEquals(0, $exitCode2);

        $order->refresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $order->status);
        $balanceAfterRun2 = (float) $this->customer->userBalance->fresh()->balance;
        $this->assertEquals($balanceAfterRun1, $balanceAfterRun2, 'Saldo tidak boleh bertambah lagi (Idempotent)');
    }

    /**
     * 14. SuperAdmin Livewire toggleStatus memicu pembatalan dan refund otomatis.
     */
    public function test_superadmin_livewire_toggle_city_status_triggers_cancellation_and_refund(): void
    {
        $superAdmin = User::factory()->create([
            'role'     => 'super_admin',
            'status'   => 'active',
            'verified' => true,
        ]);

        $order = $this->createOrder(
            Help::STATUS_MENUNGGU_MITRA,
            Help::DISPATCH_MODE_POOL,
            null,
            40000,
            4000
        );

        $initialBalance = (float) $this->customer->userBalance->fresh()->balance;

        $this->actingAs($superAdmin);

        \Livewire\Livewire::test(\App\Livewire\SuperAdmin\Cities\Index::class)
            ->call('toggleStatus', $this->city->id);

        $this->city->refresh();
        $this->assertFalse((bool) $this->city->is_active);

        $order->refresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $order->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $order->escrow_status);

        $newBalance = (float) $this->customer->userBalance->fresh()->balance;
        $this->assertEquals($initialBalance + 44000, $newBalance);
    }

    /**
     * 15. SuperAdmin Livewire toggleDistrictStatus memicu pembatalan dan refund otomatis.
     */
    public function test_superadmin_livewire_toggle_district_status_triggers_cancellation_and_refund(): void
    {
        $superAdmin = User::factory()->create([
            'role'     => 'super_admin',
            'status'   => 'active',
            'verified' => true,
        ]);

        $order = $this->createOrder(
            Help::STATUS_MENUNGGU_MITRA,
            Help::DISPATCH_MODE_POOL,
            null,
            30000,
            3000
        );

        $initialBalance = (float) $this->customer->userBalance->fresh()->balance;

        $this->actingAs($superAdmin);

        \Livewire\Livewire::test(\App\Livewire\SuperAdmin\Cities\Index::class)
            ->call('toggleDistrictStatus', $this->district->id);

        $this->district->refresh();
        $this->assertFalse((bool) $this->district->is_active);

        $order->refresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $order->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $order->escrow_status);

        $newBalance = (float) $this->customer->userBalance->fresh()->balance;
        $this->assertEquals($initialBalance + 33000, $newBalance);
    }

    /**
     * 16. Pending Help pada region inactive langsung:
     *     - matching tidak memilih order tersebut
     *     - pool/available query tidak menampilkannya
     *     - dispatch baru tidak dibuat
     */
    public function test_pending_help_in_inactive_region_is_not_matched_or_dispatched(): void
    {
        $order = $this->createOrder(
            Help::STATUS_MENUNGGU_MITRA,
            Help::DISPATCH_MODE_SEEKING,
            null,
            50000,
            5000
        );

        // Langsung ubah kecamatan menjadi nonaktif di database (tanpa toggle handler)
        $this->district->update(['is_active' => false]);

        $matchingService = app(\App\Services\HelpMatchingService::class);

        // 1. matching tidak memilih order tersebut
        $matched = $matchingService->matchPendingOrderForPartner($this->mitra);
        $this->assertNull($matched, 'Matching tidak boleh memilih order pada wilayah nonaktif');

        // 2. pool/available query tidak menampilkannya
        $availableHelps = Help::where('status', Help::STATUS_MENUNGGU_MITRA)
            ->whereNull('mitra_id')
            ->inActiveRegion()
            ->get();
        $this->assertFalse($availableHelps->contains('id', $order->id), 'Pool query tidak boleh memuat order di wilayah nonaktif');

        // canBeTakenBy juga harus false
        $this->assertFalse($order->canBeTakenBy($this->mitra));

        // 3. dispatch baru tidak dibuat
        $dispatched = $matchingService->initiateMatching($order);
        $this->assertFalse($dispatched, 'initiateMatching harus gagal jika wilayah nonaktif');
        $this->assertEquals(0, HelpDispatch::where('help_id', $order->id)->count(), 'Tidak boleh ada dispatch dibuat');
        $this->assertNotEquals(Help::DISPATCH_MODE_POOL, $order->fresh()->dispatch_mode);
    }

    /**
     * 17. Pending Help pada Province inactive langsung:
     *     - diexclude dari pool/available query
     *     - diexclude dari matchPendingOrderForPartner
     *     - canBeTakenBy menghasilkan false
     */
    public function test_pending_help_in_inactive_province_is_excluded_from_matching_and_pool(): void
    {
        $order = $this->createOrder(
            Help::STATUS_MENUNGGU_MITRA,
            Help::DISPATCH_MODE_POOL,
            null,
            60000,
            6000
        );

        // Langsung nonaktifkan provinsi di DB
        $this->province->update(['is_active' => false]);

        $matchingService = app(\App\Services\HelpMatchingService::class);

        $matched = $matchingService->matchPendingOrderForPartner($this->mitra);
        $this->assertNull($matched, 'Matching tidak boleh memilih order pada provinsi nonaktif');

        $availableHelps = Help::where('status', Help::STATUS_MENUNGGU_MITRA)
            ->whereNull('mitra_id')
            ->inActiveRegion()
            ->get();
        $this->assertFalse($availableHelps->contains('id', $order->id), 'Pool query tidak boleh memuat order jika provinsi nonaktif');

        $this->assertFalse($order->canBeTakenBy($this->mitra));

        $dispatched = $matchingService->initiateMatching($order);
        $this->assertFalse($dispatched);
    }

    /**
     * 18. Pickup/Delivery: Help sudah taken -> region kemudian OFF -> Mitra cancel -> tidak return ke pool -> JANGAN rematch -> batalkan + refund.
     */
    public function test_pickup_delivery_taken_order_partner_cancel_after_region_off_does_not_rematch(): void
    {
        $order = Help::create([
            'user_id'             => $this->customer->id,
            'mitra_id'            => $this->mitra->id,
            'city_id'             => $this->city->id,
            'district_id'         => $this->district->id,
            'title'               => 'Antar Dokumen Penting',
            'description'         => 'Pengantaran dokumen ke kantor cabang',
            'amount'              => 35000,
            'service_fee'         => 35000,
            'travel_fee'          => 0,
            'item_fund'           => 0,
            'platform_fee_amount' => 2000,
            'total_amount'        => 37000,
            'latitude'            => -7.7956,
            'longitude'           => 110.3695,
            'status'              => Help::STATUS_TAKEN,
            'dispatch_mode'       => Help::DISPATCH_MODE_ASSIGNED,
            'escrow_status'       => Help::ESCROW_STATUS_HELD,
            'payment_status'      => Help::PAYMENT_STATUS_PAID,
            'service_type'        => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'order_mode'          => Help::ORDER_MODE_INSTANT,
            'taken_at'            => now(),
        ]);

        app(\App\Services\PartnerOnlineService::class)->setBusy($this->mitra->id, $order->id);

        $initialCustomerBalance = (float) $this->customer->userBalance->fresh()->balance;

        // Region kemudian dinonaktifkan
        $this->city->update(['is_active' => false]);

        // Mitra cancel
        $cancelService = app(HelpCancellationService::class);
        $cancelReq = $cancelService->submitPartnerCancelRequest(
            $order,
            $this->mitra,
            'Kendaraan mengalami masalah mendadak sebelum berangkat'
        );

        $order->refresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $order->status, 'Order harus dibatalkan');
        $this->assertNotEquals(Help::STATUS_MENUNGGU_MITRA, $order->status, 'Order tidak boleh dikembalikan ke pool');
        $this->assertEquals(Help::DISPATCH_MODE_CLOSED, $order->dispatch_mode);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $order->escrow_status);

        // Refund diproses ke customer
        $newCustomerBalance = (float) $this->customer->userBalance->fresh()->balance;
        $this->assertEquals($initialCustomerBalance + 37000, $newCustomerBalance);

        // Mitra dilepas dari status BUSY
        $partnerState = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertNotEquals(PartnerOnlineState::STATUS_BUSY, $partnerState->matching_status);
    }

    /**
     * 19. SuperAdmin Livewire toggleProvinceStatus:
     *     Province active -> ada pending Help pada child City/District -> Super Admin toggle Province menjadi inactive -> Help harus cancelled -> refund harus diproses -> tidak boleh ada active dispatch tersisa.
     */
    public function test_superadmin_livewire_toggle_province_status_triggers_cancellation_and_refund(): void
    {
        $superAdmin = User::factory()->create([
            'role'     => 'super_admin',
            'status'   => 'active',
            'verified' => true,
        ]);

        $order = $this->createOrder(
            Help::STATUS_MENUNGGU_MITRA,
            Help::DISPATCH_MODE_OFFERED,
            null,
            45000,
            4500
        );

        $dispatch = HelpDispatch::create([
            'help_id'    => $order->id,
            'mitra_id'   => $this->mitra->id,
            'status'     => HelpDispatch::STATUS_OFFERED,
            'round'      => 1,
            'rank'       => 1,
            'offered_at' => now(),
            'expires_at' => now()->addSeconds(45),
        ]);

        $initialBalance = (float) $this->customer->userBalance->fresh()->balance;

        $this->actingAs($superAdmin);

        \Livewire\Livewire::test(\App\Livewire\SuperAdmin\Cities\Index::class)
            ->call('toggleProvinceStatus', $this->province->id);

        $this->province->refresh();
        $this->assertFalse((bool) $this->province->is_active);

        $order->refresh();
        $dispatch->refresh();

        $this->assertEquals(Help::STATUS_DIBATALKAN, $order->status, 'Order harus dibatalkan setelah provinsi di-toggle nonaktif');
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $order->escrow_status);
        $this->assertEquals(HelpDispatch::STATUS_CANCELLED, $dispatch->status, 'Tidak boleh ada active dispatch tersisa');

        $newBalance = (float) $this->customer->userBalance->fresh()->balance;
        $this->assertEquals($initialBalance + 49500, $newBalance);
    }
}
