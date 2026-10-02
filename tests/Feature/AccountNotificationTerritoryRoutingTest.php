<?php

namespace Tests\Feature;

use App\Livewire\Customer\Reports\Create as CustomerReportCreate;
use App\Livewire\Customer\Topup\TopupRequest as CustomerTopupRequest;
use App\Livewire\Customer\Withdraw\WithdrawForm as CustomerWithdrawForm;
use App\Livewire\Mitra\Reports\Create as MitraReportCreate;
use App\Livewire\Mitra\Withdraw\WithdrawForm as MitraWithdrawForm;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use App\Models\Province;
use App\Models\User;
use App\Models\UserBalance;
use App\Notifications\NewKtpVerificationNotification;
use App\Notifications\NewReportMessageNotification;
use App\Notifications\NewReportNotification;
use App\Notifications\NewTopupRequest;
use App\Notifications\NewWithdrawNotification;
use App\Services\AccountNotificationService;
use App\Services\Territory\AdminTerritoryAuthorizationService;
use App\Services\Territory\ProfileTerritoryMigrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AccountNotificationTerritoryRoutingTest extends TestCase
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
    protected User $customerDanurejan; // Profile City A, distA1
    protected User $mitraDepok;         // Profile City B, distB1

    protected AccountNotificationService $accountNotificationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accountNotificationService = app(AccountNotificationService::class);

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

        // 4. Admin City B (Sleman - Depok)
        $this->adminCityB = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => $this->cityB->id,
            'district_id' => $this->distB1->id,
        ]);
        $this->adminCityB->managedDistricts()->attach($this->distB1->id);

        // 5. Admin City C (Bantul - Sewon)
        $this->adminCityC = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => $this->cityC->id,
            'district_id' => $this->distC1->id,
        ]);
        $this->adminCityC->managedDistricts()->attach($this->distC1->id);

        // 6. Super Admin
        $this->superAdmin = User::factory()->create([
            'role'        => 'super_admin',
            'status'      => 'active',
            'city_id'     => null,
            'district_id' => null,
        ]);

        // Customer Danurejan (City A, Danurejan)
        $this->customerDanurejan = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
            'verified'    => true,
        ]);

        // Mitra Depok (City B, Depok)
        $this->mitraDepok = User::factory()->create([
            'role'        => 'mitra',
            'status'      => 'active',
            'city_id'     => $this->cityB->id,
            'district_id' => $this->distB1->id,
            'verified'    => true,
        ]);

        UserBalance::create(['user_id' => $this->customerDanurejan->id, 'balance' => 200000]);
        UserBalance::create(['user_id' => $this->mitraDepok->id, 'balance' => 200000]);
    }

    protected function createHelp(array $attributes = []): Help
    {
        return Help::create(array_merge([
            'user_id'      => $this->customerDanurejan->id,
            'title'        => 'Bantuan Test',
            'description'  => 'Deskripsi bantuan test',
            'category'     => 'tenaga',
            'price'        => 50000,
            'total_amount' => 50000,
            'status'       => 'taken',
            'city_id'      => $this->cityA->id,
            'district_id'  => $this->distA1->id,
            'mitra_id'     => $this->mitraDepok->id,
        ], $attributes));
    }

    /**
     * A1: Customer profile A submits account/general event -> Admin A receives, Admin B does not.
     */
    public function test_a1_customer_profile_a_submits_account_event_routes_to_admin_a_not_b()
    {
        Notification::fake();

        $this->actingAs($this->customerDanurejan);

        Livewire::test(CustomerReportCreate::class)
            ->set('report_type', 'dukungan_umum')
            ->set('title', 'Kendala Akun Customer')
            ->set('message', 'Saya butuh bantuan terkait informasi profil akun.')
            ->call('submit');

        Notification::assertSentTo($this->adminA1, NewReportNotification::class);
        Notification::assertNotSentTo($this->adminCityB, NewReportNotification::class);
        Notification::assertNotSentTo($this->adminCityC, NewReportNotification::class);
        Notification::assertNotSentTo($this->adminA2, NewReportNotification::class);
    }

    /**
     * A2: Mitra profile B submits account-level event -> Admin B receives.
     */
    public function test_a2_mitra_profile_b_submits_account_level_event_routes_to_admin_b()
    {
        Notification::fake();

        $this->actingAs($this->mitraDepok);

        Livewire::test(MitraReportCreate::class)
            ->set('report_type', 'dukungan_umum')
            ->set('title', 'Kendala Akun Mitra')
            ->set('message', 'Ada kendala pengaturan rekening bank pada akun saya.')
            ->call('submit');

        Notification::assertSentTo($this->adminCityB, NewReportNotification::class);
        Notification::assertNotSentTo($this->adminA1, NewReportNotification::class);
        Notification::assertNotSentTo($this->adminCityC, NewReportNotification::class);
    }

    /**
     * A3: Mitra GPS C, Profile B -> General support notification routes to B.
     */
    public function test_a3_mitra_gps_c_profile_b_general_support_routes_to_b()
    {
        Notification::fake();

        // Mitra moves runtime GPS to Bantul (City C)
        \App\Models\PartnerOnlineState::updateOrCreate(
            ['user_id' => $this->mitraDepok->id],
            [
                'latitude'  => -7.8680,
                'longitude' => 110.3550,
                'is_online' => true,
            ]
        );

        $this->actingAs($this->mitraDepok);

        Livewire::test(MitraReportCreate::class)
            ->set('report_type', 'dukungan_umum')
            ->set('title', 'Bantuan Dukungan Akun')
            ->set('message', 'Pertanyaan seputar dokumen profil.')
            ->call('submit');

        // Must route to Profile territory B (Depok Admin), NOT GPS location C (Bantul Admin)
        Notification::assertSentTo($this->adminCityB, NewReportNotification::class);
        Notification::assertNotSentTo($this->adminCityC, NewReportNotification::class);
    }

    /**
     * A4: Customer has latest Help C, Profile A -> General support routes to A. Help C irrelevant.
     */
    public function test_a4_customer_latest_help_c_profile_a_general_support_routes_to_a()
    {
        Notification::fake();

        // Customer has a recent Help in Bantul (City C, Sewon)
        $this->createHelp([
            'city_id'     => $this->cityC->id,
            'district_id' => $this->distC1->id,
            'status'      => 'completed',
        ]);

        $this->actingAs($this->customerDanurejan);

        // General support (non-help)
        Livewire::test(CustomerReportCreate::class)
            ->set('report_type', 'dukungan_umum')
            ->set('title', 'Pertanyaan Umum Akun')
            ->set('message', 'Bagaimana cara mengubah nomor telepon saya?')
            ->call('submit');

        // Must route to Customer Profile A (Danurejan Admin), NOT Bantul Admin
        Notification::assertSentTo($this->adminA1, NewReportNotification::class);
        Notification::assertNotSentTo($this->adminCityC, NewReportNotification::class);
    }

    /**
     * A5: Profile migration: A -> B. New account notification routes to B. A does not receive new notification.
     */
    public function test_a5_profile_migration_a_to_b_new_account_notification_routes_to_b()
    {
        Notification::fake();

        // Migrate customer from Danurejan (City A) to Depok (City B)
        $migrationService = app(ProfileTerritoryMigrationService::class);
        $migrationService->migrate(
            $this->superAdmin,
            $this->customerDanurejan,
            $this->cityB->id,
            $this->distB1->id,
            'Customer pindah domisili resmi ke Sleman'
        );

        $this->customerDanurejan->refresh();
        $this->assertEquals($this->cityB->id, $this->customerDanurejan->city_id);
        $this->assertEquals($this->distB1->id, $this->customerDanurejan->district_id);

        $this->actingAs($this->customerDanurejan);

        Livewire::test(CustomerReportCreate::class)
            ->set('report_type', 'dukungan_umum')
            ->set('title', 'Laporan Setelah Migrasi')
            ->set('message', 'Laporan akun baru setelah pindah domisili.')
            ->call('submit');

        // New notification must route to Admin B (Depok), NOT former Admin A1 (Danurejan)
        Notification::assertSentTo($this->adminCityB, NewReportNotification::class);
        Notification::assertNotSentTo($this->adminA1, NewReportNotification::class);
    }

    /**
     * A6: District-level profile: correct District Admin receives. Other District does not.
     */
    public function test_a6_district_level_profile_correct_district_admin_receives_other_district_does_not()
    {
        Notification::fake();

        // Customer in distA1 (Danurejan)
        $recipients = $this->accountNotificationService->resolveAdminsForUser($this->customerDanurejan);

        $recipientIds = $recipients->pluck('id')->all();
        $this->assertContains($this->adminA1->id, $recipientIds);
        $this->assertNotContains($this->adminA2->id, $recipientIds);
        $this->assertNotContains($this->adminCityB->id, $recipientIds);
    }

    /**
     * A7: City-level profile: explicit City Admin receives. District-only Admin does not.
     */
    public function test_a7_city_level_profile_explicit_city_admin_receives_district_only_does_not()
    {
        Notification::fake();

        // User with city-level profile (district_id null)
        $cityLevelUser = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'city_id'     => $this->cityA->id,
            'district_id' => null,
            'verified'    => true,
        ]);

        $recipients = $this->accountNotificationService->resolveAdminsForUser($cityLevelUser);

        $recipientIds = $recipients->pluck('id')->all();
        // Explicit City Admin A receives
        $this->assertContains($this->adminCityA->id, $recipientIds);
        // District-only Admins do NOT receive city-level account notifications
        $this->assertNotContains($this->adminA1->id, $recipientIds);
        $this->assertNotContains($this->adminA2->id, $recipientIds);
    }

    /**
     * A8: Active navbar filter on another district does not suppress account notification.
     */
    public function test_a8_active_navbar_filter_does_not_suppress_account_notification()
    {
        Notification::fake();

        // Give adminA1 authority over both distA1 and distA2
        $this->adminA1->managedDistricts()->attach($this->distA2->id);

        // Simulate active navbar filter focused on distA1
        session(['admin_selected_district_id' => $this->distA1->id]);

        // Customer is in distA2 (Gondomanan)
        $customerGondomanan = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA2->id,
            'verified'    => true,
        ]);

        $recipients = $this->accountNotificationService->resolveAdminsForUser($customerGondomanan);

        // adminA1 has canonical authority over distA2 -> must receive despite active navbar filter on distA1
        $this->assertTrue($recipients->contains('id', $this->adminA1->id));
    }

    /**
     * A9: No eligible regional Admin: no broadcast.
     */
    public function test_a9_no_eligible_regional_admin_suppresses_broadcast_fallback()
    {
        Notification::fake();

        // Create isolated territory with no admins
        $isolatedCity = City::create(['name' => 'Kota Terpencil', 'province_id' => $this->provinceDIY->id, 'province' => 'DI Yogyakarta', 'is_active' => true]);
        $isolatedDist = District::create(['name' => 'Kecamatan Terpencil', 'city_id' => $isolatedCity->id, 'is_active' => true]);

        $isolatedUser = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'city_id'     => $isolatedCity->id,
            'district_id' => $isolatedDist->id,
            'verified'    => true,
        ]);

        $recipients = $this->accountNotificationService->resolveAdminsForUser($isolatedUser);

        // Must be completely empty, no broadcast to all admins
        $this->assertTrue($recipients->isEmpty());
    }

    /**
     * A10: Help-linked report: still routes using Help territory (G6 regression protection).
     */
    public function test_a10_help_linked_report_still_routes_using_help_territory()
    {
        Notification::fake();

        // Customer profile in City A / distA1
        // Help order in City B / distB1
        $help = $this->createHelp([
            'user_id'     => $this->customerDanurejan->id,
            'city_id'     => $this->cityB->id,
            'district_id' => $this->distB1->id,
            'status'      => 'taken',
        ]);

        $this->actingAs($this->customerDanurejan);

        Livewire::test(CustomerReportCreate::class, ['help_id' => $help->id])
            ->set('report_type', 'pelanggaran_aturan')
            ->set('title', 'Laporan Terkait Tugas')
            ->set('message', 'Mitra melanggar SOP pengerjaan bantuan di Sleman.')
            ->call('submit');

        // Help territory wins! Routes to Admin B (Depok), NOT Admin A1 (Customer profile)
        Notification::assertSentTo($this->adminCityB, NewReportNotification::class);
        Notification::assertNotSentTo($this->adminA1, NewReportNotification::class);
    }

    /**
     * A11: General support non-Help: routes using profile territory.
     */
    public function test_a11_general_support_non_help_routes_using_profile_territory()
    {
        Notification::fake();

        $this->actingAs($this->customerDanurejan);

        Livewire::test(CustomerReportCreate::class)
            ->set('report_type', 'dukungan_umum')
            ->set('title', 'Bantuan Non Order')
            ->set('message', 'Pertanyaan umum kendala aplikasi.')
            ->call('submit');

        Notification::assertSentTo($this->adminA1, NewReportNotification::class);
        Notification::assertNotSentTo($this->adminCityB, NewReportNotification::class);
    }

    /**
     * A12: Existing general support thread + profile migration: next message follows NEW profile territory.
     */
    public function test_a12_existing_general_support_thread_and_profile_migration_next_message_follows_new_profile_territory()
    {
        Notification::fake();

        // 1. Customer creates a general support report in Danurejan (City A)
        $report = PartnerReport::create([
            'reporter_id'      => $this->customerDanurejan->id,
            'reported_user_id' => null,
            'reported_help_id' => null,
            'report_type'      => 'dukungan_umum',
            'title'            => 'Dukungan Awal di Danurejan',
            'message'          => 'Saya membutuhkan bantuan awal.',
            'category'         => 'dari_customer',
            'status'           => 'pending',
            'city_id'          => $this->cityA->id,
            'district_id'      => $this->distA1->id,
        ]);

        // 2. Officially migrate Customer to City B / distB1
        $migrationService = app(ProfileTerritoryMigrationService::class);
        $migrationService->migrate(
            $this->superAdmin,
            $this->customerDanurejan,
            $this->cityB->id,
            $this->distB1->id,
            'Customer migrasi ke Sleman saat chat support masih terbuka'
        );

        $this->customerDanurejan->refresh();
        User::flushRequestCache();

        // 3. Customer sends a new message on the existing support thread
        $message = PartnerReportMessage::create([
            'partner_report_id' => $report->id,
            'sender_id'         => $this->customerDanurejan->id,
            'recipient_type'    => 'admin',
            'message'           => 'Pesan lanjutan setelah saya pindah ke Sleman.',
        ]);

        // Next message notification must route to NEW profile territory Admin B (Depok), NOT old Admin A1
        Notification::assertSentTo($this->adminCityB, NewReportMessageNotification::class);
        Notification::assertNotSentTo($this->adminA1, NewReportMessageNotification::class);

        // 4. Target page authorization check:
        // Admin A1 attempts to access the support chat -> MUST BE FORBIDDEN (HTTP 403)
        $this->actingAs($this->adminA1)
            ->get(route('admin.support.chat', $report->id))
            ->assertStatus(403);

        // Admin B attempts to access the support chat -> ALLOWED (HTTP 200)
        $this->actingAs($this->adminCityB)
            ->get(route('admin.support.chat', $report->id))
            ->assertStatus(200);
    }

    /**
     * A13: KTP submission routes to user profile district admin without broadcast.
     */
    public function test_a13_ktp_submission_routes_to_user_profile_district_admin_without_broadcast()
    {
        Notification::fake();

        $unverifiedCustomer = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'inactive',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
            'verified'    => false,
        ]);

        $admins = $this->accountNotificationService->resolveAdminsForUser($unverifiedCustomer);

        foreach ($admins as $adm) {
            $adm->notify(new NewKtpVerificationNotification($unverifiedCustomer));
        }

        Notification::assertSentTo($this->adminA1, NewKtpVerificationNotification::class);
        Notification::assertNotSentTo($this->adminCityB, NewKtpVerificationNotification::class);
        Notification::assertNotSentTo($this->adminA2, NewKtpVerificationNotification::class);
    }

    /**
     * A14: Topup request routes to customer profile district admin with superadmin.
     */
    public function test_a14_topup_request_routes_to_customer_profile_district_admin_with_superadmin()
    {
        Notification::fake();
        \App\Models\AppSetting::set('topup_qris_image', 'qris_test.png');
        \Illuminate\Support\Facades\Storage::fake('public');
        $proof = \Illuminate\Http\UploadedFile::fake()->image('proof.jpg');

        $this->actingAs($this->customerDanurejan);

        Livewire::test(CustomerTopupRequest::class)
            ->set('amount', 50000)
            ->set('customerName', 'Customer Danurejan')
            ->set('customerPhone', '08123456789')
            ->set('customerEmail', 'danurejan@example.com')
            ->set('proofOfPayment', $proof)
            ->call('submitRequest');

        Notification::assertSentTo($this->adminA1, NewTopupRequest::class);
        Notification::assertSentTo($this->superAdmin, NewTopupRequest::class);
        Notification::assertNotSentTo($this->adminCityB, NewTopupRequest::class);
        Notification::assertNotSentTo($this->adminA2, NewTopupRequest::class);
    }

    /**
     * A15: Withdraw request routes to user profile district admin with superadmin.
     */
    public function test_a15_withdraw_request_routes_to_user_profile_district_admin_with_superadmin()
    {
        Notification::fake();

        $this->actingAs($this->mitraDepok);

        Livewire::test(MitraWithdrawForm::class)
            ->set('bankCode', 'BCA')
            ->set('accountNumber', '1234567890')
            ->set('accountName', 'Mitra Depok')
            ->set('amount', 50000)
            ->call('submit');

        Notification::assertSentTo($this->adminCityB, NewWithdrawNotification::class);
        Notification::assertSentTo($this->superAdmin, NewWithdrawNotification::class);
        Notification::assertNotSentTo($this->adminA1, NewWithdrawNotification::class);
    }
}
