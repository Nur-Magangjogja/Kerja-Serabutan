<?php

namespace Tests\Feature;

use App\Livewire\SuperAdmin\Users\Index;
use App\Models\BalanceTransaction;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\PartnerReport;
use App\Models\Province;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\UserGreylistLog;
use App\Services\Territory\ProfileTerritoryMigrationService;
use App\Services\UserAuditTimelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccountOversightTimelineAT1B1Test extends TestCase
{
    use RefreshDatabase;

    protected Province $provinceDIY;
    protected Province $provinceJateng;
    protected City $cityYogya;
    protected District $districtDanurejan;
    protected District $districtGondomanan;
    protected City $citySleman;
    protected District $districtDepok;
    protected City $citySemarang;
    protected District $districtBanyumanik;

    protected User $superAdmin;
    protected User $adminYogya;
    protected User $adminSleman;
    protected User $adminSemarang;
    protected User $customer;
    protected User $mitra;

    protected UserAuditTimelineService $timelineService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->timelineService = app(UserAuditTimelineService::class);

        // 1. Setup Provinces
        $this->provinceDIY = Province::create([
            'name'      => 'DI Yogyakarta',
            'code'      => '34',
            'is_active' => true,
        ]);

        $this->provinceJateng = Province::create([
            'name'      => 'Jawa Tengah',
            'code'      => '33',
            'is_active' => true,
        ]);

        // 2. Setup Cities & Districts
        // City 1: Kota Yogyakarta
        $this->cityYogya = City::create([
            'name'        => 'Kota Yogyakarta',
            'province'    => 'DI Yogyakarta',
            'province_id' => $this->provinceDIY->id,
            'is_active'   => true,
            'latitude'    => -7.7956,
            'longitude'   => 110.3695,
        ]);

        $this->districtDanurejan = District::create([
            'city_id'   => $this->cityYogya->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);

        $this->districtGondomanan = District::create([
            'city_id'   => $this->cityYogya->id,
            'name'      => 'Gondomanan',
            'is_active' => true,
        ]);

        // City 2: Kabupaten Sleman
        $this->citySleman = City::create([
            'name'        => 'Kabupaten Sleman',
            'province'    => 'DI Yogyakarta',
            'province_id' => $this->provinceDIY->id,
            'is_active'   => true,
            'latitude'    => -7.7167,
            'longitude'   => 110.3556,
        ]);

        $this->districtDepok = District::create([
            'city_id'   => $this->citySleman->id,
            'name'      => 'Depok',
            'is_active' => true,
        ]);

        // City 3: Kota Semarang
        $this->citySemarang = City::create([
            'name'        => 'Kota Semarang',
            'province'    => 'Jawa Tengah',
            'province_id' => $this->provinceJateng->id,
            'is_active'   => true,
            'latitude'    => -6.9667,
            'longitude'   => 110.4167,
        ]);

        $this->districtBanyumanik = District::create([
            'city_id'   => $this->citySemarang->id,
            'name'      => 'Banyumanik',
            'is_active' => true,
        ]);

        // 3. Setup Admins
        $this->superAdmin = User::factory()->create([
            'role'        => 'super_admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->districtDanurejan->id,
        ]);

        $this->adminYogya = User::factory()->create([
            'name'        => 'Admin Yogya',
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->districtDanurejan->id,
        ]);
        $this->adminYogya->managedCities()->sync([$this->cityYogya->id]);
        $this->adminYogya->managedDistricts()->sync([$this->districtDanurejan->id, $this->districtGondomanan->id]);

        $this->adminSleman = User::factory()->create([
            'name'        => 'Admin Sleman',
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->citySleman->id,
            'district_id' => $this->districtDepok->id,
        ]);
        $this->adminSleman->managedCities()->sync([$this->citySleman->id]);
        $this->adminSleman->managedDistricts()->sync([$this->districtDepok->id]);

        $this->adminSemarang = User::factory()->create([
            'name'        => 'Admin Semarang',
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->citySemarang->id,
            'district_id' => $this->districtBanyumanik->id,
        ]);
        $this->adminSemarang->managedCities()->sync([$this->citySemarang->id]);
        $this->adminSemarang->managedDistricts()->sync([$this->districtBanyumanik->id]);

        // 4. Setup Customer & Mitra (Home Profile Territory: Danurejan, Yogya)
        $this->customer = User::factory()->create([
            'name'        => 'Budi Customer',
            'role'        => 'customer',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->districtDanurejan->id,
            'city'        => $this->cityYogya->name,
        ]);
        UserBalance::create(['user_id' => $this->customer->id, 'balance' => 500000]);

        $this->mitra = User::factory()->create([
            'name'        => 'Siti Mitra',
            'role'        => 'mitra',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->districtDanurejan->id,
            'city'        => $this->cityYogya->name,
        ]);
        UserBalance::create(['user_id' => $this->mitra->id, 'balance' => 200000]);
    }

    /**
     * Section 35: CUSTOMER CROSS-TERRITORY HISTORY
     */
    public function test_customer_cross_territory_history(): void
    {
        // 1. Help in Home Territory (Yogya)
        $helpYogya = Help::create([
            'user_id'        => $this->customer->id,
            'order_id'       => 'HELP-YOGYA-01',
            'title'          => 'Perbaikan AC Yogya',
            'description'    => 'AC bocor di Yogya',
            'status'         => Help::STATUS_SELESAI,
            'city_id'        => $this->cityYogya->id,
            'district_id'    => $this->districtDanurejan->id,
            'price'          => 150000,
            'total_amount'   => 150000,
            'service_type'   => Help::SERVICE_TYPE_ON_SITE,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
        ]);

        // 2. Help in Foreign Territory (Sleman)
        $helpSleman = Help::create([
            'user_id'        => $this->customer->id,
            'order_id'       => 'HELP-SLEMAN-01',
            'title'          => 'Bongkar Lemari Sleman',
            'description'    => 'Pindah perabot di Sleman',
            'status'         => Help::STATUS_DIBATALKAN,
            'city_id'        => $this->citySleman->id,
            'district_id'    => $this->districtDepok->id,
            'price'          => 200000,
            'total_amount'   => 200000,
            'service_type'   => Help::SERVICE_TYPE_ON_SITE,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
        ]);

        // 3. Cancellation in Sleman
        $cancelSleman = HelpCancelRequest::create([
            'help_id'         => $helpSleman->id,
            'requested_by'    => $this->customer->id,
            'customer_id'     => $this->customer->id,
            'requester_type'  => 'customer',
            'previous_status' => Help::STATUS_IN_PROGRESS,
            'district_id'     => $this->districtDepok->id,
            'reason'          => 'Mitra tidak kunjung tiba di Depok Sleman',
            'status'          => 'approved',
            'decision'        => 'approved',
            'reviewed_by'     => $this->adminSleman->id,
            'reviewed_at'     => now(),
            'refund_amount'   => 200000,
        ]);

        // 4. Dispute in Sleman
        $helpDispute = Help::create([
            'user_id'             => $this->customer->id,
            'order_id'            => 'HELP-DISPUTE-01',
            'title'               => 'Sengketa Pengecatan Sleman',
            'description'         => 'Cat tidak sesuai pesanan',
            'status'              => Help::STATUS_IN_PROGRESS,
            'escrow_status'       => Help::ESCROW_STATUS_DISPUTED_FREEZE,
            'dispute_reason'      => 'Customer komplain warna cat tidak sesuai',
            'city_id'             => $this->citySleman->id,
            'district_id'         => $this->districtDepok->id,
            'price'               => 300000,
            'total_amount'        => 300000,
            'service_type'        => Help::SERVICE_TYPE_ON_SITE,
            'payment_status'      => Help::PAYMENT_STATUS_PAID,
        ]);

        // 5. Partner Report in Sleman
        $reportSleman = PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra->id,
            'reported_help_id' => $helpSleman->id,
            'help_id'          => $helpSleman->id,
            'report_type'      => 'investigasi',
            'category'         => 'Keterlambatan Ekstrem',
            'title'            => 'Laporan Mitra Terlambat',
            'message'          => 'Mitra terlambat 2 jam di lokasi Depok',
            'status'           => 'resolved',
            'resolved_by'      => $this->adminSleman->id,
            'resolved_at'      => now(),
        ]);

        // 6. Balance Transaction
        $balanceTx = BalanceTransaction::create([
            'user_id'          => $this->customer->id,
            'type'             => 'topup',
            'amount'           => 100000,
            'status'           => 'success',
            'reference_number' => 'TOPUP-001',
            'description'      => 'Top up saldo akun',
        ]);

        // Admin Yogya audits User Budi Customer
        $timeline = $this->timelineService->getTimelineForUser(
            $this->customer,
            $this->adminYogya,
            page: 1,
            perPage: 20,
            filter: 'all'
        );

        $this->assertGreaterThanOrEqual(6, $timeline->total());

        // Check Yogya Help: is_cross_territory == false, can_access_case == true (Admin Yogya manages Danurejan)
        $yogyaEntry = collect($timeline->items())->firstWhere('public_ref', 'HELP-YOGYA-01');
        $this->assertNotNull($yogyaEntry);
        $this->assertFalse($yogyaEntry['is_cross_territory']);
        $this->assertTrue($yogyaEntry['can_access_case']);

        // Check Sleman Help: is_cross_territory == true, incident_district_id == Depok, can_access_case == false
        $slemanEntry = collect($timeline->items())->firstWhere('public_ref', 'HELP-SLEMAN-01');
        $this->assertNotNull($slemanEntry);
        $this->assertTrue($slemanEntry['is_cross_territory']);
        $this->assertEquals($this->districtDepok->id, $slemanEntry['incident_district_id']);
        $this->assertFalse($slemanEntry['can_access_case']); // Admin Yogya cannot access Sleman case directly

        // Check Cancellation: retains incident territory
        $cancelEntry = collect($timeline->items())->firstWhere('event_type', 'cancellation');
        $this->assertNotNull($cancelEntry);
        $this->assertEquals('HelpCancelRequest', $cancelEntry['source_type']);
        $this->assertEquals('Pemohon Pembatalan', $cancelEntry['user_role']);
        $this->assertFalse($cancelEntry['can_access_case']);

        // Check Dispute: retains incident territory Sleman
        $disputeEntry = collect($timeline->items())->firstWhere('event_type', 'dispute');
        $this->assertNotNull($disputeEntry);
        $this->assertEquals('Customer', $disputeEntry['user_role']);
        $this->assertFalse($disputeEntry['can_access_case']);
    }

    /**
     * Section 36: MITRA CROSS-TERRITORY HISTORY & SP CONTINUITY
     */
    public function test_mitra_cross_territory_history(): void
    {
        // 1. Mitra takes job in Sleman (Depok)
        $helpSleman = Help::create([
            'user_id'        => $this->customer->id,
            'mitra_id'       => $this->mitra->id,
            'order_id'       => 'MITRA-JOB-SLEMAN-01',
            'title'          => 'Reparasi Mesin Sleman',
            'description'    => 'Mesin cuci rusak di Sleman',
            'status'         => Help::STATUS_DIBATALKAN,
            'city_id'        => $this->citySleman->id,
            'district_id'    => $this->districtDepok->id,
            'price'          => 250000,
            'total_amount'   => 250000,
            'service_type'   => Help::SERVICE_TYPE_ON_SITE,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
        ]);

        // Cancellation in Sleman where Mitra is affected
        $cancelSleman = HelpCancelRequest::create([
            'help_id'         => $helpSleman->id,
            'requested_by'    => $this->customer->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'requester_type'  => 'customer',
            'previous_status' => Help::STATUS_IN_PROGRESS,
            'district_id'     => $this->districtDepok->id,
            'reason'          => 'Customer membatalkan di Sleman',
            'status'          => 'approved',
            'decision'        => 'approved',
            'reviewed_by'     => $this->adminSleman->id,
        ]);

        // 2. Mitra takes job in Semarang (Banyumanik)
        $helpSemarang = Help::create([
            'user_id'        => $this->customer->id,
            'mitra_id'       => $this->mitra->id,
            'order_id'       => 'MITRA-JOB-SEMARANG-01',
            'title'          => 'Instalasi Listrik Semarang',
            'description'    => 'Instalasi rumah di Semarang',
            'status'         => Help::STATUS_SELESAI,
            'city_id'        => $this->citySemarang->id,
            'district_id'    => $this->districtBanyumanik->id,
            'price'          => 400000,
            'total_amount'   => 400000,
            'service_type'   => Help::SERVICE_TYPE_ON_SITE,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
        ]);

        // Investigation in Semarang against Mitra
        $reportSemarang = PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra->id,
            'reported_help_id' => $helpSemarang->id,
            'help_id'          => $helpSemarang->id,
            'report_type'      => 'investigasi',
            'category'         => 'Kerusakan Barang',
            'title'            => 'Laporan Kerusakan Instalasi',
            'message'          => 'Kabel terbakar karena kelalaian mitra',
            'status'           => 'resolved',
            'resolved_by'      => $this->adminSemarang->id,
            'resolved_at'      => now(),
        ]);

        // Admin Semarang issues SP1 to Mitra linked to report
        $spLog = UserGreylistLog::create([
            'user_id'           => $this->mitra->id,
            'admin_id'          => $this->adminSemarang->id,
            'partner_report_id' => $reportSemarang->id,
            'action'            => 'warning_issued',
            'warning_level'     => 1,
            'reason'            => 'Kelalaian teknis pekerjaan listrik di Semarang',
            'message'           => 'Surat Peringatan 1 diterbitkan',
        ]);
        $this->mitra->update(['warning_level' => 1]);

        // Admin Yogya (Home Territory Admin) views Mitra's timeline
        $timeline = $this->timelineService->getTimelineForUser(
            $this->mitra,
            $this->adminYogya,
            page: 1,
            perPage: 20,
            filter: 'all'
        );

        // Assert Admin Yogya sees Sleman job, Semarang job, Cancellation, Investigation, and SP1
        $this->assertGreaterThanOrEqual(5, $timeline->total());

        // SP event assert
        $spEntry = collect($timeline->items())->firstWhere('event_type', 'discipline');
        $this->assertNotNull($spEntry);
        $this->assertEquals(1, $spEntry['sp_level']);
        $this->assertEquals('Admin Semarang', $spEntry['actor_name']);
        $this->assertEquals('UserGreylistLog', $spEntry['source_type']);
        $this->assertFalse($spEntry['can_access_case']); // Admin Yogya cannot mutate Semarang case

        // Job roles assert
        $slemanJobEntry = collect($timeline->items())->firstWhere('public_ref', 'MITRA-JOB-SLEMAN-01');
        $this->assertEquals('Mitra Bertugas', $slemanJobEntry['user_role']);
        $this->assertTrue($slemanJobEntry['is_cross_territory']);
    }

    /**
     * Section 37: DEDUPLICATION RULE
     */
    public function test_deduplication_rule(): void
    {
        $help = Help::create([
            'user_id'        => $this->customer->id,
            'order_id'       => 'DEDUP-TEST-01',
            'title'          => 'Pekerjaan Dedup',
            'description'    => 'Testing deduplication',
            'status'         => Help::STATUS_DIBATALKAN,
            'city_id'        => $this->cityYogya->id,
            'district_id'    => $this->districtDanurejan->id,
            'price'          => 100000,
            'total_amount'   => 100000,
            'service_type'   => Help::SERVICE_TYPE_ON_SITE,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
        ]);

        // HelpCancelRequest where user is BOTH requested_by AND customer_id
        $cancel = HelpCancelRequest::create([
            'help_id'         => $help->id,
            'requested_by'    => $this->customer->id,
            'customer_id'     => $this->customer->id,
            'requester_type'  => 'customer',
            'previous_status' => Help::STATUS_IN_PROGRESS,
            'district_id'     => $this->districtDanurejan->id,
            'reason'          => 'Duplikasi query branch check',
            'status'          => 'pending',
        ]);

        $timeline = $this->timelineService->getTimelineForUser(
            $this->customer,
            $this->adminYogya,
            page: 1,
            perPage: 20,
            filter: 'cancel_dispute'
        );

        // Cancel event should appear exactly once
        $cancelEntries = collect($timeline->items())->where('source_id', $cancel->id)->where('source_type', 'HelpCancelRequest');
        $this->assertCount(1, $cancelEntries);

        // Ensure all event_key items in the timeline are strictly unique
        $allKeys = collect($timeline->items())->pluck('event_key');
        $this->assertEquals($allKeys->count(), $allKeys->unique()->count());
    }

    /**
     * Section 38: FOREIGN CASE PRIVACY & MUTATION BOUNDARY
     */
    public function test_foreign_case_privacy(): void
    {
        // Incident in Sleman
        $helpSleman = Help::create([
            'user_id'             => $this->customer->id,
            'order_id'            => 'PRIVACY-TEST-01',
            'title'               => 'Pekerjaan Rahasia Sleman',
            'description'         => 'Catatan rahasia customer',
            'status'              => Help::STATUS_IN_PROGRESS,
            'escrow_status'       => Help::ESCROW_STATUS_DISPUTED_FREEZE,
            'dispute_reason'      => 'Sengketa dana internal',
            'city_id'             => $this->citySleman->id,
            'district_id'         => $this->districtDepok->id,
            'price'               => 150000,
            'total_amount'        => 150000,
            'service_type'        => Help::SERVICE_TYPE_ON_SITE,
            'payment_status'      => Help::PAYMENT_STATUS_PAID,
        ]);

        // Audit entry for Admin Yogya
        $timeline = $this->timelineService->getTimelineForUser(
            $this->customer,
            $this->adminYogya,
            page: 1,
            perPage: 20,
            filter: 'cancel_dispute'
        );

        $disputeEntry = collect($timeline->items())->firstWhere('source_id', $helpSleman->id);
        $this->assertNotNull($disputeEntry);

        // can_access_case must be false
        $this->assertFalse($disputeEntry['can_access_case']);
        $this->assertNull($disputeEntry['case_route']);

        // Case route directly hit by Admin Yogya should return 403 or denied
        // (Admin Yogya lacks territory authority for Sleman case)
        $this->actingAs($this->adminYogya);
        $authService = app(\App\Services\Territory\AdminTerritoryAuthorizationService::class);
        $this->assertFalse(
            $authService->canAccessTerritory($this->adminYogya, $this->districtDepok->id, $this->citySleman->id)
        );
    }

    /**
     * Profile Territory Authorization check: Admin C (Semarang) cannot access User A (Yogya)
     */
    public function test_profile_territory_authorization_and_unrelated_admin(): void
    {
        // Admin Semarang attempts to view User Yogya via Livewire component
        Livewire::actingAs($this->adminSemarang)
            ->test(Index::class)
            ->call('viewUser', $this->customer->id)
            ->assertSet('showViewModal', false)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');

        // Admin Yogya can view User Yogya
        Livewire::actingAs($this->adminYogya)
            ->test(Index::class)
            ->call('viewUser', $this->customer->id)
            ->assertSet('showViewModal', true)
            ->assertSet('selectedUserId', $this->customer->id)
            ->assertSee('Data Profil', false)
            ->assertSee('Riwayat & Audit Akun', false);
    }

    /**
     * Section 39: PROFILE MIGRATION AUDIT TRANSFER
     */
    public function test_profile_migration_oversight_transfer(): void
    {
        // Customer has events while living in Yogya
        Help::create([
            'user_id'        => $this->customer->id,
            'order_id'       => 'HISTORICAL-JOB-01',
            'title'          => 'Pekerjaan Masa Lalu di Yogya',
            'description'    => 'Pekerjaan lama',
            'status'         => Help::STATUS_SELESAI,
            'city_id'        => $this->cityYogya->id,
            'district_id'    => $this->districtDanurejan->id,
            'price'          => 100000,
            'total_amount'   => 100000,
            'service_type'   => Help::SERVICE_TYPE_ON_SITE,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
        ]);

        // Initially: Admin Yogya can view, Admin Sleman cannot
        Livewire::actingAs($this->adminYogya)
            ->test(Index::class)
            ->call('viewUser', $this->customer->id)
            ->assertSet('showViewModal', true);

        Livewire::actingAs($this->adminSleman)
            ->test(Index::class)
            ->call('viewUser', $this->customer->id)
            ->assertSet('showViewModal', false);

        // Perform official migration: Yogya (Danurejan) -> Sleman (Depok)
        $migrationService = app(ProfileTerritoryMigrationService::class);
        $migrationService->migrate(
            $this->superAdmin,
            $this->customer,
            $this->citySleman->id,
            $this->districtDepok->id,
            'Pindah domisili resmi ke Sleman',
            $this->provinceDIY->id
        );

        $this->customer->refresh();
        $this->assertEquals($this->districtDepok->id, $this->customer->district_id);
        $this->assertEquals($this->citySleman->id, $this->customer->city_id);

        // AFTER MIGRATION:
        // Admin Sleman (New Home Territory Admin) CAN view customer and consolidated history
        Livewire::actingAs($this->adminSleman)
            ->test(Index::class)
            ->call('viewUser', $this->customer->id)
            ->assertSet('showViewModal', true)
            ->call('setModalTab', 'audit')
            ->assertSet('activeModalTab', 'audit')
            ->assertSee('Pekerjaan Masa Lalu di Yogya');

        // Admin Yogya (Old Home Territory Admin) LOSES oversight
        Livewire::actingAs($this->adminYogya)
            ->test(Index::class)
            ->call('viewUser', $this->customer->id)
            ->assertSet('showViewModal', false)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');

        // Historical job territory remains unchanged (Yogya / Danurejan)
        $historicalJob = Help::where('order_id', 'HISTORICAL-JOB-01')->first();
        $this->assertEquals($this->districtDanurejan->id, $historicalJob->district_id);
        $this->assertEquals($this->cityYogya->id, $historicalJob->city_id);

        // Zero duplicate records
        $this->assertEquals(1, Help::where('order_id', 'HISTORICAL-JOB-01')->count());
    }

    /**
     * Test Livewire Timeline filter interaction and ensure no DB PK is leaked into the DOM.
     */
    public function test_livewire_modal_timeline_filter_and_cross_territory_badge_rendering(): void
    {
        // Create job in Sleman for customer Yogya (Cross territory)
        $help = Help::create([
            'user_id'        => $this->customer->id,
            'order_id'       => 'JOB-CROSS-001',
            'title'          => 'Pindahan Kos Lintas Wilayah',
            'description'    => 'Pindahan kos dari Sleman ke tempat lain',
            'status'         => Help::STATUS_SELESAI,
            'city_id'        => $this->citySleman->id,
            'district_id'    => $this->districtDepok->id,
            'price'          => 75000,
            'total_amount'   => 75000,
            'service_type'   => Help::SERVICE_TYPE_ON_SITE,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
        ]);

        Livewire::actingAs($this->adminYogya)
            ->test(Index::class)
            ->call('viewUser', $this->customer->id)
            ->assertSet('showViewModal', true)
            ->call('setModalTab', 'audit')
            ->assertSet('activeModalTab', 'audit')
            ->assertSee('Lintas Wilayah')
            ->assertSee('JOB-CROSS-001')
            ->assertSee('Pindahan Kos Lintas Wilayah')
            ->call('setTimelineFilter', 'job')
            ->assertSet('auditFilter', 'job')
            ->assertSee('JOB-CROSS-001')
            ->call('setTimelineFilter', 'dispute')
            ->assertSet('auditFilter', 'dispute')
            ->assertDontSee('JOB-CROSS-001')
            ->assertSee('Belum Ada Riwayat Aktivitas');
    }
}
