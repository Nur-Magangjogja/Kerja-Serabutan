<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpCancelMessage;
use App\Models\HelpCancelRequest;
use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use App\Models\Province;
use App\Models\User;
use App\Notifications\HelpStatusNotification;
use App\Notifications\NewReportMessageNotification;
use App\Notifications\NewReportNotification;
use App\Services\HelpNotificationService;
use App\Services\Territory\AdminTerritoryAuthorizationService;
use App\Services\Territory\ProfileTerritoryMigrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class JobNotificationTerritoryRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected Province $provinceDIY;

    // City A (Kota Yogyakarta)
    protected City $cityA;
    protected District $distA1; // Danurejan
    protected District $distA2; // Gondomanan

    // City B (Kabupaten Sleman)
    protected City $cityB;
    protected District $distB1; // Depok

    // City C (Kabupaten Bantul)
    protected City $cityC;
    protected District $distC1; // Sewon

    // Admins
    protected User $adminA1;        // District-only Admin for distA1 (Danurejan)
    protected User $adminA2;        // District-only Admin for distA2 (Gondomanan)
    protected User $adminCityA;     // Explicit City Admin for cityA
    protected User $adminCityB;     // Admin for cityB (Sleman)
    protected User $adminCityC;     // Admin for cityC (Bantul)
    protected User $superAdmin;

    // Customer & Mitra
    protected User $customerSleman; // Profile Sleman
    protected User $mitraBantul;     // Profile Bantul

    protected HelpNotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationService = app(HelpNotificationService::class);

        $this->provinceDIY = Province::create([
            'name'      => 'DI Yogyakarta',
            'code'      => '34',
            'is_active' => true,
        ]);

        // City A (Kota Yogyakarta)
        $this->cityA = City::create([
            'name'        => 'Kota Yogyakarta',
            'province_id' => $this->provinceDIY->id,
            'province'    => 'DI Yogyakarta',
            'is_active'   => true,
        ]);
        $this->distA1 = District::create(['city_id' => $this->cityA->id, 'name' => 'Danurejan', 'is_active' => true]);
        $this->distA2 = District::create(['city_id' => $this->cityA->id, 'name' => 'Gondomanan', 'is_active' => true]);

        // City B (Kabupaten Sleman)
        $this->cityB = City::create([
            'name'        => 'Kabupaten Sleman',
            'province_id' => $this->provinceDIY->id,
            'province'    => 'DI Yogyakarta',
            'is_active'   => true,
        ]);
        $this->distB1 = District::create(['city_id' => $this->cityB->id, 'name' => 'Depok', 'is_active' => true]);

        // City C (Kabupaten Bantul)
        $this->cityC = City::create([
            'name'        => 'Kabupaten Bantul',
            'province_id' => $this->provinceDIY->id,
            'province'    => 'DI Yogyakarta',
            'is_active'   => true,
        ]);
        $this->distC1 = District::create(['city_id' => $this->cityC->id, 'name' => 'Sewon', 'is_active' => true]);

        // 1. Admin A1 (Danurejan only via admin_district pivot)
        $this->adminA1 = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => null,
            'district_id' => null,
        ]);
        $this->adminA1->managedDistricts()->attach($this->distA1->id);

        // 2. Admin A2 (Gondomanan only via admin_district pivot)
        $this->adminA2 = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA2->id,
        ]);
        $this->adminA2->managedDistricts()->attach($this->distA2->id);

        // 3. Explicit City Admin for City A (managedCities pivot)
        $this->adminCityA = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => $this->cityA->id,
            'district_id' => null,
        ]);
        $this->adminCityA->managedCities()->attach($this->cityA->id);

        // 4. Admin City B (Sleman)
        $this->adminCityB = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => $this->cityB->id,
            'district_id' => $this->distB1->id,
        ]);
        $this->adminCityB->managedDistricts()->attach($this->distB1->id);

        // 5. Admin City C (Bantul)
        $this->adminCityC = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => $this->cityC->id,
            'district_id' => $this->distC1->id,
        ]);
        $this->adminCityC->managedDistricts()->attach($this->distC1->id);

        // 6. SuperAdmin
        $this->superAdmin = User::factory()->create([
            'role'   => 'super_admin',
            'status' => 'active',
        ]);

        // Customer with profile Sleman (City B)
        $this->customerSleman = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'city_id'     => $this->cityB->id,
            'district_id' => $this->distB1->id,
        ]);

        // Mitra with profile Bantul (City C)
        $this->mitraBantul = User::factory()->create([
            'role'        => 'mitra',
            'status'      => 'active',
            'city_id'     => $this->cityC->id,
            'district_id' => $this->distC1->id,
        ]);
    }

    /**
     * Helper to create a Help.
     */
    protected function createHelp(array $attributes = []): Help
    {
        return Help::create(array_merge([
            'user_id'      => $this->customerSleman->id,
            'city_id'      => $this->cityA->id,
            'district_id'  => $this->distA1->id,
            'title'        => 'Bantuan Danurejan',
            'description'  => 'Deskripsi pekerjaan',
            'amount'       => 50000,
            'total_amount' => 50000,
            'status'       => Help::STATUS_IN_PROGRESS,
            'mitra_id'     => $this->mitraBantul->id,
        ], $attributes));
    }

    /**
     * N1: Customer profile A creates Help B.
     * Admin B receives notification. Admin A does not receive.
     */
    public function test_n1_customer_profile_a_creates_help_b_routes_to_admin_b_not_admin_a()
    {
        Notification::fake();

        // Customer profile is Sleman (City B). Help is in Danurejan (City A, Dist A1).
        $help = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
        ]);

        $this->actingAs($this->customerSleman);

        // Customer submits a report for Help B
        Livewire::test(\App\Livewire\Customer\Reports\Create::class)
            ->set('help_id', $help->id)
            ->set('reported_help_id', $help->id)
            ->set('reported_user_id', $this->mitraBantul->id)
            ->set('title', 'Laporan Help B')
            ->set('message', 'Kendala pekerjaan di Danurejan')
            ->set('report_type', 'pelanggaran_aturan')
            ->call('submit');

        // Admin Danurejan (Admin B1) MUST receive
        Notification::assertSentTo($this->adminA1, NewReportNotification::class);

        // Explicit City Admin for City A also has full authority over City A's districts
        Notification::assertSentTo($this->adminCityA, NewReportNotification::class);

        // Admin Sleman (Customer's profile city!) MUST NOT receive
        Notification::assertNotSentTo($this->adminCityB, NewReportNotification::class);
    }

    /**
     * N2: Mitra profile C takes Help B.
     * Admin B receives notification. Admin C does not receive.
     */
    public function test_n2_mitra_profile_c_takes_help_b_routes_to_admin_b_not_admin_c()
    {
        Notification::fake();

        // Help B is in Danurejan. Mitra profile is Bantul (City C).
        $help = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
            'mitra_id'    => $this->mitraBantul->id,
        ]);

        $this->actingAs($this->mitraBantul);

        // Mitra submits a report for Help B
        Livewire::test(\App\Livewire\Mitra\Reports\Create::class)
            ->set('reported_help_id', $help->id)
            ->set('reported_user_id', $this->customerSleman->id)
            ->set('title', 'Kendala Mitra di Help B')
            ->set('message', 'Customer tidak dapat dihubungi di lokasi Danurejan')
            ->set('report_type', 'pelanggaran_mitra')
            ->call('submit');

        // Admin Danurejan MUST receive
        Notification::assertSentTo($this->adminA1, NewReportNotification::class);

        // Admin Bantul (Mitra's profile city!) MUST NOT receive
        Notification::assertNotSentTo($this->adminCityC, NewReportNotification::class);
    }

    /**
     * N3: Mitra GPS C but Help B.
     * Notification routes to Help B territory, NOT to GPS location C.
     */
    public function test_n3_mitra_gps_c_but_help_b_routes_to_help_b_territory()
    {
        Notification::fake();

        // Help is in Danurejan (City A). Mitra GPS state is set to Bantul (City C).
        $help = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
        ]);

        // Create cancellation request for Help B
        $cancelReq = HelpCancelRequest::create([
            'help_id'        => $help->id,
            'requester_type' => HelpCancelRequest::REQUESTER_PARTNER,
            'action_type'    => HelpCancelRequest::ACTION_PARTNER_INCIDENT,
            'previous_status'=> Help::STATUS_IN_PROGRESS,
            'status'         => HelpCancelRequest::STATUS_PENDING,
            'partner_id'     => $this->mitraBantul->id,
            'customer_id'    => $this->customerSleman->id,
            'district_id'    => $help->district_id,
            'city_id'        => $help->city_id,
            'reason'         => 'Kendala motor mogok di perjalanan',
        ]);

        // Mitra sends clarification message
        HelpCancelMessage::create([
            'help_cancel_request_id' => $cancelReq->id,
            'sender_id'              => $this->mitraBantul->id,
            'recipient_type'         => 'admin',
            'message'                => 'Saya sedang di bengkel, mohon bantuan admin membatalkan tugas Danurejan.',
            'is_read'                => false,
        ]);

        // Admin Danurejan MUST receive HelpStatusNotification
        Notification::assertSentTo($this->adminA1, HelpStatusNotification::class);

        // Admin Bantul (GPS / Profile of Mitra) MUST NOT receive
        Notification::assertNotSentTo($this->adminCityC, HelpStatusNotification::class);
    }

    /**
     * N4: Customer profile migration A → C while Help B exists.
     * Later Help event routes to Admin B.
     */
    public function test_n4_customer_profile_migration_does_not_affect_existing_help_routing()
    {
        Notification::fake();

        // Customer created Help B in Danurejan
        $help = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
        ]);

        // Customer's profile now migrates to City C (Bantul)
        $migrationService = app(ProfileTerritoryMigrationService::class);
        $migrationService->migrate(
            $this->superAdmin,
            $this->customerSleman,
            $this->cityC->id,
            $this->distC1->id,
            'Pindah domisili'
        );

        $this->assertEquals($this->cityC->id, $this->customerSleman->fresh()->city_id);

        // Later Help event occurs: report message on Help B
        $report = PartnerReport::create([
            'reporter_id'      => $this->customerSleman->id,
            'reported_user_id' => $this->mitraBantul->id,
            'reported_help_id' => $help->id,
            'title'            => 'Aduan Pekerjaan B',
            'message'          => 'Aduan tindak lanjut pekerjaan B',
            'report_type'      => 'mitra_tidak_selesai',
            'category'         => 'dari_customer',
            'status'           => 'pending',
        ]);

        PartnerReportMessage::create([
            'partner_report_id' => $report->id,
            'sender_id'         => $this->customerSleman->id,
            'recipient_type'    => 'admin',
            'message'           => 'Update kronologi tambahan',
            'is_read'           => false,
        ]);

        // Admin Danurejan (Help B) MUST receive
        Notification::assertSentTo($this->adminA1, NewReportMessageNotification::class);

        // Admin Bantul (new profile city C) MUST NOT receive
        Notification::assertNotSentTo($this->adminCityC, NewReportMessageNotification::class);
    }

    /**
     * N5: District B1 Help.
     * Admin B1 receives. Admin B2 does not.
     */
    public function test_n5_district_b1_help_routes_to_admin_b1_not_admin_b2()
    {
        Notification::fake();

        // Help in distA1 (Danurejan)
        $help = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
        ]);

        $cancelReq = HelpCancelRequest::create([
            'help_id'        => $help->id,
            'requester_type' => HelpCancelRequest::REQUESTER_CUSTOMER,
            'action_type'    => HelpCancelRequest::ACTION_CUSTOMER_WITHDRAW,
            'previous_status'=> Help::STATUS_IN_PROGRESS,
            'status'         => HelpCancelRequest::STATUS_PENDING,
            'partner_id'     => $this->mitraBantul->id,
            'customer_id'    => $this->customerSleman->id,
            'district_id'    => $help->district_id,
            'city_id'        => $help->city_id,
            'reason'         => 'Permintaan pembatalan customer',
        ]);

        HelpCancelMessage::create([
            'help_cancel_request_id' => $cancelReq->id,
            'sender_id'              => $this->customerSleman->id,
            'recipient_type'         => 'admin',
            'message'                => 'Mohon diproses pembatalan ini.',
            'is_read'                => false,
        ]);

        // Admin distA1 (Danurejan) MUST receive
        Notification::assertSentTo($this->adminA1, HelpStatusNotification::class);

        // Admin distA2 (Gondomanan) MUST NOT receive
        Notification::assertNotSentTo($this->adminA2, HelpStatusNotification::class);
    }

    /**
     * N6: City-level Help B (district_id == null, city_id != null).
     * Explicit City Admin B receives. District-only Admin B1 does not.
     */
    public function test_n6_city_level_help_routes_to_explicit_city_admin_not_district_only_admin()
    {
        // City-level Help in City A (district_id null)
        $help = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => null,
        ]);

        $recipients = $this->notificationService->resolveAdminsForHelp($help);

        // Explicit City Admin A MUST be included
        $this->assertTrue($recipients->contains('id', $this->adminCityA->id));

        // District-only Admin A1 MUST NOT be included
        $this->assertFalse($recipients->contains('id', $this->adminA1->id));

        // District-only Admin A2 MUST NOT be included
        $this->assertFalse($recipients->contains('id', $this->adminA2->id));

        // Admin outside City A MUST NOT be included
        $this->assertFalse($recipients->contains('id', $this->adminCityB->id));
    }

    /**
     * N7: Admin authority includes B1/B2. Navbar active filter B1.
     * Notification event B2: Admin still receives.
     */
    public function test_n7_navbar_active_filter_does_not_suppress_notification_for_authorized_district()
    {
        // Give Admin A1 authority over BOTH distA1 and distA2
        $this->adminA1->managedDistricts()->sync([$this->distA1->id, $this->distA2->id]);
        $this->adminA1->refresh();

        // Simulate Admin A1 having an active navbar filter set to distA1
        session(['admin_active_district_filter' => (string) $this->distA1->id]);

        // Help event in distA2 occurs
        $helpA2 = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA2->id,
        ]);

        $recipients = $this->notificationService->resolveAdminsForHelp($helpA2);

        // Admin A1 MUST still receive notification for distA2, because navbar filter is presentation-only!
        $this->assertTrue($recipients->contains('id', $this->adminA1->id));
    }

    /**
     * N8: Admin outside territory does not receive.
     */
    public function test_n8_admin_outside_territory_does_not_receive()
    {
        $help = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
        ]);

        $recipients = $this->notificationService->resolveAdminsForHelp($help);

        // Admin Sleman (City B) and Admin Bantul (City C) must NOT receive
        $this->assertFalse($recipients->contains('id', $this->adminCityB->id));
        $this->assertFalse($recipients->contains('id', $this->adminCityC->id));
    }

    /**
     * N9: No matching regional Admin. No global broadcast.
     */
    public function test_n9_no_matching_regional_admin_suppresses_broadcast_fallback()
    {
        // Create an isolated city with no assigned admins
        $isolatedCity = City::create([
            'name'        => 'Kota Terisolasi',
            'province_id' => $this->provinceDIY->id,
            'province'    => 'DI Yogyakarta',
            'is_active'   => true,
        ]);
        $isolatedDist = District::create(['city_id' => $isolatedCity->id, 'name' => 'Kecamatan Terisolasi', 'is_active' => true]);

        $help = $this->createHelp([
            'city_id'     => $isolatedCity->id,
            'district_id' => $isolatedDist->id,
        ]);

        // Resolve without super admin
        $recipients = $this->notificationService->resolveAdminsForHelp($help, includeSuperAdmin: false);

        // MUST be empty! Absolutely NO broadcast fallback to other regional admins!
        $this->assertTrue($recipients->isEmpty());
        $this->assertCount(0, $recipients);

        // Check with SuperAdmin included: ONLY SuperAdmin, 0 regional broadcast
        $recipientsWithSA = $this->notificationService->resolveAdminsForHelp($help, includeSuperAdmin: true);
        $this->assertTrue($recipientsWithSA->contains('id', $this->superAdmin->id));
        $this->assertFalse($recipientsWithSA->contains('id', $this->adminA1->id));
        $this->assertFalse($recipientsWithSA->contains('id', $this->adminCityB->id));
        $this->assertFalse($recipientsWithSA->contains('id', $this->adminCityC->id));
    }

    /**
     * N10: Old notification exists but Admin authority later removed.
     * Click target: DENY via G4.
     */
    public function test_n10_old_notification_target_denied_when_authority_later_revoked()
    {
        // 1. Help in Danurejan (City A, Dist A1)
        $help = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
        ]);

        $cancelReq = HelpCancelRequest::create([
            'help_id'        => $help->id,
            'requester_type' => HelpCancelRequest::REQUESTER_CUSTOMER,
            'action_type'    => HelpCancelRequest::ACTION_CUSTOMER_WITHDRAW,
            'previous_status'=> Help::STATUS_IN_PROGRESS,
            'status'         => HelpCancelRequest::STATUS_PENDING,
            'partner_id'     => $this->mitraBantul->id,
            'customer_id'    => $this->customerSleman->id,
            'district_id'    => $help->district_id,
            'city_id'        => $help->city_id,
            'reason'         => 'Permintaan pembatalan customer',
        ]);

        // Admin A1 receives notification with target URL
        $targetUrl = route('admin.cancellations.chat', $cancelReq->id);

        $this->adminA1->notify(new HelpStatusNotification(
            $help,
            $help->status,
            'cancellation_response',
            null,
            'Pesan pembatalan',
            'Klarifikasi',
            $targetUrl
        ));

        // Verify notification exists in database for Admin A1
        $notif = $this->adminA1->notifications()->first();
        $this->assertNotNull($notif);
        $this->assertEquals($targetUrl, $notif->data['url']);

        // 2. Admin A1 initially HAS authority, so accessing the target chat is allowed
        $this->actingAs($this->adminA1);
        Livewire::test(\App\Livewire\Admin\Disputes\Chat::class, ['cancelRequest' => $cancelReq])
            ->assertOk();

        // 3. Authority is now REVOKED from Admin A1 (detached from admin_district pivot)
        $this->adminA1->managedDistricts()->detach($this->distA1->id);
        User::flushRequestCache();
        $this->adminA1 = User::find($this->adminA1->id);
        $this->actingAs($this->adminA1);

        // G4 canonical check directly confirms DENY
        $authService = app(AdminTerritoryAuthorizationService::class);
        $this->assertFalse($authService->canAccessTerritory($this->adminA1, $help->district_id, $help->city_id));

        // 4. Admin A1 clicks the old notification target link:
        // Target page MUST DENY access via G4 guard and redirect with error!
        Livewire::test(\App\Livewire\Admin\Disputes\Chat::class, ['cancelRequest' => $cancelReq])
            ->assertRedirect(route('admin.cancellations.index'))
            ->assertSessionHas('error');
    }
}
