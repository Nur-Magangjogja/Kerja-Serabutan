<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\PartnerReport;
use App\Models\Province;
use App\Models\User;
use App\Models\UserGreylistLog;
use App\Notifications\AccountOversightNotification;
use App\Notifications\NewCancellationReviewNotification;
use App\Notifications\NewReportNotification;
use App\Services\AccountNotificationService;
use App\Services\Cancellation\OnSiteCancellationService;
use App\Services\HelpTransactionService;
use App\Services\PartnerDisciplineService;
use App\Services\Territory\ProfileTerritoryMigrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class CrossTerritoryOversightNotificationTG1CTest extends TestCase
{
    use RefreshDatabase;

    protected Province $provinceDIY;
    protected Province $provinceJateng;

    // Territory A: Kota Yogyakarta (Danurejan)
    protected City $cityYogya;
    protected District $distDanurejan;

    // Territory B: Kabupaten Sleman (Depok)
    protected City $citySleman;
    protected District $distDepok;

    // Territory C: Kota Semarang (Banyumanik)
    protected City $citySemarang;
    protected District $distBanyumanik;

    // Admins
    protected User $adminYogya;      // Admin for Territory A
    protected User $adminYogya2;     // Second Admin for Territory A (Multi-Admin test)
    protected User $adminSleman;     // Admin for Territory B
    protected User $adminSemarang;   // Admin for Territory C
    protected User $superAdmin;

    // Users
    protected User $customerYogya;   // Profile Territory A
    protected User $mitraYogya;      // Profile Territory A
    protected User $mitraSleman;     // Profile Territory B

    protected AccountNotificationService $accountNotificationService;
    protected OnSiteCancellationService $cancellationService;
    protected HelpTransactionService $transactionService;
    protected PartnerDisciplineService $disciplineService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accountNotificationService = app(AccountNotificationService::class);
        $this->cancellationService        = app(OnSiteCancellationService::class);
        $this->transactionService         = app(HelpTransactionService::class);
        $this->disciplineService          = app(PartnerDisciplineService::class);

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

        // Territory A: Yogya - Danurejan
        $this->cityYogya = City::create([
            'name'        => 'Kota Yogyakarta',
            'province_id' => $this->provinceDIY->id,
            'province'    => 'DI Yogyakarta',
            'is_active'   => true,
        ]);
        $this->distDanurejan = District::create([
            'city_id'   => $this->cityYogya->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);

        // Territory B: Sleman - Depok
        $this->citySleman = City::create([
            'name'        => 'Kabupaten Sleman',
            'province_id' => $this->provinceDIY->id,
            'province'    => 'DI Yogyakarta',
            'is_active'   => true,
        ]);
        $this->distDepok = District::create([
            'city_id'   => $this->citySleman->id,
            'name'      => 'Depok',
            'is_active' => true,
        ]);

        // Territory C: Semarang - Banyumanik
        $this->citySemarang = City::create([
            'name'        => 'Kota Semarang',
            'province_id' => $this->provinceJateng->id,
            'province'    => 'Jawa Tengah',
            'is_active'   => true,
        ]);
        $this->distBanyumanik = District::create([
            'city_id'   => $this->citySemarang->id,
            'name'      => 'Banyumanik',
            'is_active' => true,
        ]);

        // Admin Yogya 1 & 2 (Territory A)
        $this->adminYogya = User::factory()->create([
            'name'        => 'Admin Yogya 1',
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->distDanurejan->id,
        ]);
        $this->adminYogya->managedDistricts()->attach($this->distDanurejan->id);

        $this->adminYogya2 = User::factory()->create([
            'name'        => 'Admin Yogya 2',
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->distDanurejan->id,
        ]);
        $this->adminYogya2->managedDistricts()->attach($this->distDanurejan->id);

        // Admin Sleman (Territory B)
        $this->adminSleman = User::factory()->create([
            'name'        => 'Admin Sleman',
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => $this->citySleman->id,
            'district_id' => $this->distDepok->id,
        ]);
        $this->adminSleman->managedDistricts()->attach($this->distDepok->id);

        // Admin Semarang (Territory C)
        $this->adminSemarang = User::factory()->create([
            'name'        => 'Admin Semarang',
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => $this->citySemarang->id,
            'district_id' => $this->distBanyumanik->id,
        ]);
        $this->adminSemarang->managedDistricts()->attach($this->distBanyumanik->id);

        // Super Admin
        $this->superAdmin = User::factory()->create([
            'name'   => 'Super Admin',
            'role'   => 'super_admin',
            'status' => 'active',
        ]);

        // Customer & Mitra Profile Yogya (Territory A)
        $this->customerYogya = User::factory()->create([
            'name'        => 'Budi Customer Yogya',
            'role'        => 'customer',
            'status'      => 'active',
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->distDanurejan->id,
            'verified'    => true,
        ]);

        $this->mitraYogya = User::factory()->create([
            'name'        => 'Siti Mitra Yogya',
            'role'        => 'mitra',
            'status'      => 'active',
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->distDanurejan->id,
            'verified'    => true,
        ]);

        // Mitra Profile Sleman (Territory B)
        $this->mitraSleman = User::factory()->create([
            'name'        => 'Joko Mitra Sleman',
            'role'        => 'mitra',
            'status'      => 'active',
            'city_id'     => $this->citySleman->id,
            'district_id' => $this->distDepok->id,
            'verified'    => true,
        ]);
    }

    /**
     * 1. TEST — CROSS-TERRITORY CANCELLATION
     * Customer & Mitra Profile Yogya (A), Help in Sleman (B).
     * Cancellation review routes actionable notification to Admin Sleman (B)
     * and informational oversight notification to Admin Yogya (A).
     */
    public function test_cross_territory_cancellation_routes_actionable_to_case_admin_and_oversight_to_profile_admin(): void
    {
        Notification::fake();

        $helpInSleman = Help::create([
            'user_id'        => $this->customerYogya->id,
            'mitra_id'       => $this->mitraYogya->id,
            'order_id'       => 'HELP-SLEMAN-CANC-01',
            'title'          => 'Perbaikan Pipa Sleman',
            'description'    => 'Pipa bocor di Depok Sleman',
            'status'         => Help::STATUS_IN_PROGRESS,
            'service_stage'  => 'in_progress',
            'city_id'        => $this->citySleman->id,
            'district_id'    => $this->distDepok->id,
            'price'          => 200000,
            'total_amount'   => 200000,
            'service_type'   => Help::SERVICE_TYPE_ON_SITE,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
        ]);

        // Mitra Yogya triggers partner incident cancellation in Sleman
        $result = $this->cancellationService->cancelByPartner(
            $helpInSleman,
            $this->mitraYogya,
            'Kendala teknis pipa di lapangan Sleman'
        );

        $this->assertTrue($result['success']);
        $this->assertTrue($result['under_review']);

        // Case Admin Sleman receives 1 actionable cancellation review notification
        Notification::assertSentTo($this->adminSleman, NewCancellationReviewNotification::class);
        Notification::assertNotSentTo($this->adminSleman, AccountOversightNotification::class);

        // Profile Admin Yogya receives informational oversight notification
        Notification::assertSentTo($this->adminYogya, AccountOversightNotification::class, function ($notification) {
            $data = $notification->toArray($this->adminYogya);
            return $data['category'] === 'account_oversight'
                && $data['event_type'] === 'cancellation'
                && str_contains($data['title'], 'Pengawasan Akun')
                && str_contains($data['url'], 'users');
        });

        // Admin Yogya cannot access Sleman cancellation chat (redirects with error)
        $cancelReq = HelpCancelRequest::find($result['cancel_request_id']);
        Livewire::actingAs($this->adminYogya)
            ->test(\App\Livewire\Admin\Disputes\Chat::class, ['cancelRequest' => $cancelReq])
            ->assertRedirect(route('admin.cancellations.index'));

        // Case Admin Sleman has full access (HTTP 200)
        $this->actingAs($this->adminSleman)
            ->get(route('admin.cancellations.chat', $cancelReq->id))
            ->assertStatus(200);
    }

    /**
     * 2. TEST — SAME ADMIN DEDUP
     * If an Admin covers both Profile A and Case B, only 1 actionable notification is sent,
     * and no redundant duplicate oversight notification is sent.
     */
    public function test_same_admin_deduplication_prevents_duplicate_oversight_notification(): void
    {
        Notification::fake();

        // Admin Sleman also covers Danurejan (Territory A)
        $this->adminSleman->managedDistricts()->attach($this->distDanurejan->id);

        $helpInSleman = Help::create([
            'user_id'        => $this->customerYogya->id,
            'mitra_id'       => $this->mitraYogya->id,
            'order_id'       => 'HELP-SLEMAN-DEDUP-01',
            'title'          => 'Servis AC Sleman',
            'description'    => 'Servis AC di Depok Sleman',
            'status'         => Help::STATUS_IN_PROGRESS,
            'service_stage'  => 'in_progress',
            'city_id'        => $this->citySleman->id,
            'district_id'    => $this->distDepok->id,
            'price'          => 150000,
            'total_amount'   => 150000,
            'service_type'   => Help::SERVICE_TYPE_ON_SITE,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
        ]);

        $this->cancellationService->cancelByPartner(
            $helpInSleman,
            $this->mitraYogya,
            'Kendala di jalan menuju Sleman'
        );

        // Admin Sleman is Case Admin -> receives actionable notification
        Notification::assertSentTo($this->adminSleman, NewCancellationReviewNotification::class);
        // Admin Sleman must NOT receive redundant oversight notification
        Notification::assertNotSentTo($this->adminSleman, AccountOversightNotification::class);
    }

    /**
     * 3. TEST — MULTIPLE PROFILE ADMINS DEDUP
     * Both Admin Yogya 1 & Admin Yogya 2 manage Territory A.
     * Both receive exactly 1 oversight notification, with no duplicated dispatch.
     */
    public function test_multiple_profile_admins_receive_one_oversight_notification_each(): void
    {
        Notification::fake();

        $helpInSleman = Help::create([
            'user_id'        => $this->customerYogya->id,
            'mitra_id'       => $this->mitraYogya->id,
            'order_id'       => 'HELP-SLEMAN-MULTI-01',
            'title'          => 'Pembersihan Rumah Sleman',
            'description'    => 'Bersih-bersih di Depok Sleman',
            'status'         => Help::STATUS_IN_PROGRESS,
            'service_stage'  => 'in_progress',
            'city_id'        => $this->citySleman->id,
            'district_id'    => $this->distDepok->id,
            'price'          => 120000,
            'total_amount'   => 120000,
            'service_type'   => Help::SERVICE_TYPE_ON_SITE,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
        ]);

        $this->cancellationService->cancelByPartner(
            $helpInSleman,
            $this->mitraYogya,
            'Kendala peralatan lapangan'
        );

        Notification::assertSentToTimes($this->adminYogya, AccountOversightNotification::class, 2); // 1 for Mitra Yogya, 1 for Customer Yogya
        Notification::assertSentToTimes($this->adminYogya2, AccountOversightNotification::class, 2);
    }

    /**
     * 4. TEST — DISPUTE / FREEZE
     * Customer Profile Yogya (A) raises dispute on Help in Sleman (B).
     * Case Admin Sleman gets dispute report notification, Profile Admin Yogya gets oversight notification.
     */
    public function test_dispute_freeze_routes_actionable_to_case_admin_and_oversight_to_profile_admin(): void
    {
        Notification::fake();

        $helpInSleman = Help::create([
            'user_id'        => $this->customerYogya->id,
            'mitra_id'       => $this->mitraSleman->id,
            'order_id'       => 'HELP-SLEMAN-DISP-01',
            'title'          => 'Instalasi Listrik Sleman',
            'description'    => 'Pemasangan stop kontak di Sleman',
            'status'         => Help::STATUS_SELESAI,
            'service_stage'  => 'completed',
            'completed_at'   => now(),
            'city_id'        => $this->citySleman->id,
            'district_id'    => $this->distDepok->id,
            'price'          => 300000,
            'total_amount'   => 300000,
            'service_type'   => Help::SERVICE_TYPE_ON_SITE,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_RELEASED,
        ]);

        $report = $this->transactionService->claimWarrantyAndClawbackEscrow(
            $helpInSleman,
            $this->customerYogya,
            'Kabel korslet setelah 2 jam pekerjaan selesai'
        );

        // Case Admin Sleman receives report/dispute notification
        Notification::assertSentTo($this->adminSleman, NewReportNotification::class);

        // Profile Admin Yogya receives dispute oversight notification
        Notification::assertSentTo($this->adminYogya, AccountOversightNotification::class, function ($notification) {
            $data = $notification->toArray($this->adminYogya);
            return $data['event_type'] === 'dispute'
                && $data['status'] === 'disputed_freeze';
        });
    }

    /**
     * 5. TEST — INVESTIGATION / PARTNER REPORT
     * Report filed against Mitra Yogya (A) in Sleman (B).
     * Case Admin Sleman gets actionable report notification.
     * Profile Admin Yogya gets informational oversight notification without private message leak.
     */
    public function test_investigation_report_routes_actionable_to_case_admin_and_oversight_to_profile_admin(): void
    {
        Notification::fake();

        $helpInSleman = Help::create([
            'user_id'        => $this->customerYogya->id,
            'mitra_id'       => $this->mitraYogya->id,
            'order_id'       => 'HELP-SLEMAN-REP-01',
            'title'          => 'Cat Dinding Sleman',
            'description'    => 'Pengecatan di Depok Sleman',
            'status'         => Help::STATUS_IN_PROGRESS,
            'city_id'        => $this->citySleman->id,
            'district_id'    => $this->distDepok->id,
            'price'          => 250000,
            'total_amount'   => 250000,
            'service_type'   => Help::SERVICE_TYPE_ON_SITE,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
        ]);

        $this->actingAs($this->customerYogya);

        Livewire::test(\App\Livewire\Customer\Reports\Create::class, ['help_id' => $helpInSleman->id])
            ->set('report_type', 'pelanggaran_aturan')
            ->set('title', 'Laporan Keterlambatan Mitra')
            ->set('message', 'Pesan rahasia investigasi: Mitra datang terlambat 3 jam dan tidak membawa peralatan.')
            ->call('submit');

        // Case Admin Sleman receives actionable report notification
        Notification::assertSentTo($this->adminSleman, NewReportNotification::class);

        // Profile Admin Yogya receives oversight notification
        Notification::assertSentTo($this->adminYogya, AccountOversightNotification::class, function ($notification) {
            $data = $notification->toArray($this->adminYogya);
            // Must NOT leak private message text
            return $data['event_type'] === 'investigation'
                && !str_contains($data['message'], 'Pesan rahasia investigasi')
                && str_contains($data['title'], 'Pengawasan Akun');
        });
    }

    /**
     * 6. TEST — SP / DISCIPLINARY FROM FOREIGN CASE
     * Case Admin Sleman issues SP1 to Mitra Yogya from investigation report.
     * Exactly 1 UserGreylistLog is created, user warning level updated once,
     * Profile Admin Yogya receives oversight notification, Admin Sleman does NOT receive redundant oversight.
     */
    public function test_foreign_case_sp_issuance_triggers_oversight_notification_without_duplicate_sp(): void
    {
        Notification::fake();

        $helpInSleman = Help::create([
            'user_id'        => $this->customerYogya->id,
            'mitra_id'       => $this->mitraYogya->id,
            'order_id'       => 'HELP-SLEMAN-SP-01',
            'title'          => 'Instalasi Listrik Semarang',
            'description'    => 'Pekerjaan di Sleman',
            'status'         => Help::STATUS_SELESAI,
            'city_id'        => $this->citySleman->id,
            'district_id'    => $this->distDepok->id,
            'price'          => 400000,
            'total_amount'   => 400000,
            'service_type'   => Help::SERVICE_TYPE_ON_SITE,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
        ]);

        $reportSleman = PartnerReport::create([
            'reporter_id'      => $this->customerYogya->id,
            'reported_user_id' => $this->mitraYogya->id,
            'reported_help_id' => $helpInSleman->id,
            'help_id'          => $helpInSleman->id,
            'report_type'      => 'investigasi',
            'category'         => 'Kerusakan Barang',
            'title'            => 'Laporan Kerusakan Instalasi',
            'message'          => 'Kabel terbakar karena kelalaian mitra',
            'status'           => 'in_investigation',
        ]);

        // Admin Sleman issues SP 1
        $result = $this->disciplineService->issueInstantSpFromReport(
            $reportSleman,
            $this->mitraYogya,
            'Kelalaian fatal instalasi di Sleman',
            $this->adminSleman,
            1
        );

        $this->assertEquals(1, $result['targetLevel']);
        $this->mitraYogya->refresh();
        $this->assertEquals(1, $this->mitraYogya->warning_level);

        // Exactly one UserGreylistLog exists for this report
        $this->assertEquals(1, UserGreylistLog::where('partner_report_id', $reportSleman->id)->count());

        // Profile Admin Yogya receives oversight notification
        Notification::assertSentTo($this->adminYogya, AccountOversightNotification::class, function ($notification) {
            $data = $notification->toArray($this->adminYogya);
            return $data['event_type'] === 'discipline'
                && str_contains($data['status'], 'SP 1');
        });

        // Case Admin Sleman (the issuer) does NOT receive redundant oversight notification
        Notification::assertNotSentTo($this->adminSleman, AccountOversightNotification::class);
    }

    /**
     * 7. TEST — PROFILE MIGRATION
     * Customer officially migrates Yogya (A) -> Sleman (B).
     * Future cross-territory event in Semarang (C) sends oversight notification to Admin Sleman (B),
     * NOT former Admin Yogya (A).
     */
    public function test_profile_migration_routes_future_cross_territory_oversight_to_new_home_admin(): void
    {
        Notification::fake();

        // Migrate Customer from Yogya (A) to Sleman (B)
        app(ProfileTerritoryMigrationService::class)->migrate(
            $this->superAdmin,
            $this->customerYogya,
            $this->citySleman->id,
            $this->distDepok->id,
            'Customer pindah domisili resmi ke Sleman'
        );

        $this->customerYogya->refresh();
        $this->assertEquals($this->citySleman->id, $this->customerYogya->city_id);
        $this->assertEquals($this->distDepok->id, $this->customerYogya->district_id);

        // Event occurs in Semarang (C)
        $helpInSemarang = Help::create([
            'user_id'        => $this->customerYogya->id,
            'mitra_id'       => $this->mitraYogya->id,
            'order_id'       => 'HELP-SEMARANG-MIG-01',
            'title'          => 'Bongkar Pasang Semarang',
            'description'    => 'Pekerjaan di Semarang',
            'status'         => Help::STATUS_IN_PROGRESS,
            'service_stage'  => 'in_progress',
            'city_id'        => $this->citySemarang->id,
            'district_id'    => $this->distBanyumanik->id,
            'price'          => 500000,
            'total_amount'   => 500000,
            'service_type'   => Help::SERVICE_TYPE_ON_SITE,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
        ]);

        $this->cancellationService->cancelByPartner(
            $helpInSemarang,
            $this->mitraYogya,
            'Kendala di Semarang'
        );

        // Case Admin Semarang gets actionable cancellation review notification
        Notification::assertSentTo($this->adminSemarang, NewCancellationReviewNotification::class);

        // New Home Admin Sleman (B) gets oversight notification for migrated Customer
        Notification::assertSentTo($this->adminSleman, AccountOversightNotification::class);

        // Former Home Admin Yogya (A) does NOT receive oversight notification for customer
        // (Admin Yogya only receives for Mitra Yogya who is still profile A)
        $sentToYogya = Notification::sent($this->adminYogya, AccountOversightNotification::class);
        foreach ($sentToYogya as $notif) {
            $data = $notif->toArray($this->adminYogya);
            $this->assertEquals($this->mitraYogya->id, $data['user_id']); // Only for Mitra, NOT migrated Customer
        }
    }

    /**
     * 8. TEST — NOTIFICATION != AUTHORIZATION & PRIVACY
     * Oversight notification points to User Detail / Audit tab,
     * and Profile Admin is forbidden (403) from accessing foreign case routes.
     */
    public function test_notification_does_not_grant_case_authorization(): void
    {
        $helpInSemarang = Help::create([
            'user_id'        => $this->customerYogya->id,
            'order_id'       => 'HELP-SEMARANG-AUTH-01',
            'title'          => 'Renovasi Semarang',
            'description'    => 'Pekerjaan di Semarang',
            'status'         => Help::STATUS_IN_PROGRESS,
            'city_id'        => $this->citySemarang->id,
            'district_id'    => $this->distBanyumanik->id,
            'price'          => 1000000,
            'total_amount'   => 1000000,
            'service_type'   => Help::SERVICE_TYPE_ON_SITE,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
        ]);

        $reportSemarang = PartnerReport::create([
            'reporter_id'      => $this->customerYogya->id,
            'reported_user_id' => $this->mitraSleman->id,
            'reported_help_id' => $helpInSemarang->id,
            'help_id'          => $helpInSemarang->id,
            'report_type'      => 'investigasi',
            'category'         => 'Kerusakan',
            'title'            => 'Laporan Kerusakan Semarang',
            'message'          => 'Investigasi Semarang',
            'status'           => 'in_investigation',
        ]);

        // 1. Admin Yogya (Profile Admin) is forbidden from accessing foreign case show page (HTTP 403)
        $this->actingAs($this->adminYogya)
            ->get(route('admin.partners.reports.show', $reportSemarang->id))
            ->assertStatus(403);

        // 2. Case Admin Semarang has full case authority (HTTP 200)
        $this->actingAs($this->adminSemarang)
            ->get(route('admin.partners.reports.show', $reportSemarang->id))
            ->assertStatus(200);

        // 3. Admin Yogya can view User Management / Audit history tab (HTTP 200)
        $this->actingAs($this->adminYogya)
            ->get(route('admin.users.index', ['search' => $this->customerYogya->name, 'tab' => 'audit']))
            ->assertStatus(200);

        // 4. Admin Yogya attempts mutation (SP issuance) on foreign case -> AuthorizationException (403)
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->disciplineService->issueInstantSpFromReport(
            $reportSemarang,
            $this->mitraSleman,
            'Sanksi ilegal dari luar wilayah',
            $this->adminYogya,
            1
        );
    }
}
