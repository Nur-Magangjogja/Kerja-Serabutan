<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use App\Models\Province;
use App\Models\User;
use App\Services\Territory\AdminTerritoryAuthorizationService;
use App\Services\Territory\PartnerReportTerritoryResolver;
use App\Services\Territory\ProfileTerritoryMigrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CanonicalResourceOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;
    protected City $cityA;
    protected District $distA1;
    protected District $distA2;

    protected City $cityB;
    protected District $distB1;

    protected City $cityC;
    protected District $distC1;

    protected User $adminA1;
    protected User $adminCityA;
    protected User $adminB1;
    protected User $adminC1;
    protected User $superAdmin;

    protected User $customerA;
    protected User $mitraB;
    protected User $mitraC;

    protected PartnerReportTerritoryResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = app(PartnerReportTerritoryResolver::class);

        $this->province = Province::create(['name' => 'DI Yogyakarta', 'code' => '34', 'is_active' => true]);

        // City A (Kota Yogyakarta) with 2 districts
        $this->cityA = City::create(['name' => 'Kota Yogyakarta', 'province_id' => $this->province->id, 'province' => 'DI Yogyakarta', 'is_active' => true]);
        $this->distA1 = District::create(['city_id' => $this->cityA->id, 'name' => 'Danurejan', 'is_active' => true]);
        $this->distA2 = District::create(['city_id' => $this->cityA->id, 'name' => 'Gondomanan', 'is_active' => true]);

        // City B (Kabupaten Sleman)
        $this->cityB = City::create(['name' => 'Kabupaten Sleman', 'province_id' => $this->province->id, 'province' => 'DI Yogyakarta', 'is_active' => true]);
        $this->distB1 = District::create(['city_id' => $this->cityB->id, 'name' => 'Depok', 'is_active' => true]);

        // City C (Kabupaten Bantul)
        $this->cityC = City::create(['name' => 'Kabupaten Bantul', 'province_id' => $this->province->id, 'province' => 'DI Yogyakarta', 'is_active' => true]);
        $this->distC1 = District::create(['city_id' => $this->cityC->id, 'name' => 'Sewon', 'is_active' => true]);

        // Admins
        // Admin A1: District-only admin in Danurejan (distA1)
        $this->adminA1 = User::factory()->create(['role' => 'admin', 'city_id' => $this->cityA->id, 'district_id' => $this->distA1->id, 'status' => 'active']);
        $this->adminA1->managedDistricts()->sync([$this->distA1->id]);

        // Admin City A: Explicit City Admin for Kota Yogyakarta
        $this->adminCityA = User::factory()->create(['role' => 'admin', 'city_id' => $this->cityA->id, 'district_id' => null, 'status' => 'active']);
        $this->adminCityA->managedCities()->sync([$this->cityA->id]);

        // Admin B1: District admin in Depok (distB1)
        $this->adminB1 = User::factory()->create(['role' => 'admin', 'city_id' => $this->cityB->id, 'district_id' => $this->distB1->id, 'status' => 'active']);
        $this->adminB1->managedDistricts()->sync([$this->distB1->id]);

        // Admin C1: District admin in Sewon (distC1)
        $this->adminC1 = User::factory()->create(['role' => 'admin', 'city_id' => $this->cityC->id, 'district_id' => $this->distC1->id, 'status' => 'active']);
        $this->adminC1->managedDistricts()->sync([$this->distC1->id]);

        // Superadmin
        $this->superAdmin = User::factory()->create(['role' => 'super_admin', 'status' => 'active']);

        // Users
        $this->customerA = User::factory()->create([
            'role'        => 'customer',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
            'status'      => 'active',
        ]);

        $this->mitraB = User::factory()->create([
            'role'        => 'mitra',
            'city_id'     => $this->cityB->id,
            'district_id' => $this->distB1->id,
            'status'      => 'active',
        ]);

        $this->mitraC = User::factory()->create([
            'role'        => 'mitra',
            'city_id'     => $this->cityC->id,
            'district_id' => $this->distC1->id,
            'status'      => 'active',
        ]);
    }

    protected function createHelp(array $attributes = []): Help
    {
        return Help::create(array_merge([
            'user_id'     => $this->customerA->id,
            'title'       => 'Judul Bantuan',
            'description' => 'Deskripsi tugas bantuan',
            'status'      => 'taken',
            'amount'      => 50000,
        ], $attributes));
    }

    /**
     * R1: Help-linked PartnerReport -> Help territory wins.
     */
    public function test_r1_help_linked_partner_report_wins_by_help_territory(): void
    {
        // Help order is in District B (Depok, Sleman)
        $help = $this->createHelp([
            'mitra_id'    => $this->mitraC->id,
            'city_id'     => $this->cityB->id,
            'district_id' => $this->distB1->id,
            'title'       => 'Pekerjaan di Sleman',
        ]);

        // Reporter is customerA (Wilayah A), reported user is mitraC (Wilayah C), Help in Wilayah B
        $report = PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_user_id' => $this->mitraC->id,
            'reported_help_id' => $help->id,
            'report_type'      => 'pelanggaran_aturan',
            'title'            => 'Laporan Terkait Tugas',
            'message'          => 'Pelanggaran SOP tugas di Sleman',
            'status'           => 'pending',
        ]);

        $canonical = $report->getCanonicalTerritory();
        $this->assertEquals('help', $canonical['source']);
        $this->assertEquals($this->distB1->id, $canonical['district_id']);
        $this->assertEquals($this->cityB->id, $canonical['city_id']);

        // Listing check: Only Admin B1 sees the report
        $this->actingAs($this->adminB1);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Index::class)
            ->assertSee($report->title);

        $this->actingAs($this->adminA1);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Index::class)
            ->assertDontSee($report->title);

        $this->actingAs($this->adminC1);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Index::class)
            ->assertDontSee($report->title);
    }

    /**
     * R2: Reporter profile mismatch does not add ownership.
     */
    public function test_r2_reporter_profile_mismatch_does_not_add_ownership(): void
    {
        $help = $this->createHelp([
            'city_id'     => $this->cityB->id,
            'district_id' => $this->distB1->id,
            'title'       => 'Order di Sleman',
        ]);

        $report = PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_help_id' => $help->id,
            'report_type'      => 'klaim_refund_pekerjaan_fiktif',
            'title'            => 'Klaim Refund Order B',
            'message'          => 'Pekerjaan fiktif',
            'status'           => 'pending',
        ]);

        // Admin A1 (reporter's district) must be DENIED direct access (403)
        $this->actingAs($this->adminA1);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Show::class, ['report' => $report])
            ->assertStatus(403);
    }

    /**
     * R3: Reported user profile mismatch does not add ownership.
     */
    public function test_r3_reported_user_profile_mismatch_does_not_add_ownership(): void
    {
        $help = $this->createHelp([
            'mitra_id'    => $this->mitraC->id,
            'city_id'     => $this->cityB->id,
            'district_id' => $this->distB1->id,
            'title'       => 'Order di Sleman',
        ]);

        $report = PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_user_id' => $this->mitraC->id,
            'reported_help_id' => $help->id,
            'report_type'      => 'mitra_berperilaku_buruk',
            'title'            => 'Mitra Kasar',
            'message'          => 'Perilaku buruk di lokasi',
            'status'           => 'pending',
        ]);

        // Admin C1 (reported user's district) must be DENIED direct access (403)
        $this->actingAs($this->adminC1);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Show::class, ['report' => $report])
            ->assertStatus(403);
    }

    /**
     * R4: dukungan_umum non-Help -> reporter current Profile Territory.
     */
    public function test_r4_dukungan_umum_non_help_belongs_to_reporter_profile_territory(): void
    {
        $support = PartnerReport::create([
            'reporter_id' => $this->customerA->id,
            'report_type' => 'dukungan_umum',
            'category'    => 'dari_customer',
            'title'       => 'Dukungan Akun Customer A',
            'message'     => 'Bagaimana cara top up saldo?',
            'status'      => 'pending',
        ]);

        $canonical = $support->getCanonicalTerritory();
        $this->assertEquals('profile_reporter', $canonical['source']);
        $this->assertEquals($this->distA1->id, $canonical['district_id']);
        $this->assertEquals($this->cityA->id, $canonical['city_id']);

        // Listing check: Admin A1 sees it, Admin B1 does not
        $this->actingAs($this->adminA1);
        Livewire::test(\App\Livewire\Admin\Support\Index::class)
            ->assertSee($support->title);

        $this->actingAs($this->adminB1);
        Livewire::test(\App\Livewire\Admin\Support\Index::class)
            ->assertDontSee($support->title);
    }

    /**
     * R5: General support profile migration A->B moves current ownership without rewriting record/history.
     */
    public function test_r5_general_support_profile_migration_moves_ownership_without_rewriting_record(): void
    {
        $support = PartnerReport::create([
            'reporter_id' => $this->customerA->id,
            'report_type' => 'dukungan_umum',
            'category'    => 'dari_customer',
            'title'       => 'Pertanyaan Customer Awal',
            'message'     => 'Pertanyaan pesan awal',
            'status'      => 'pending',
        ]);

        $msg1 = PartnerReportMessage::create([
            'partner_report_id' => $support->id,
            'sender_id'         => $this->customerA->id,
            'recipient_type'    => 'admin',
            'message'           => 'Pesan pertama sebelum pindah domisili',
            'is_read'           => false,
        ]);

        // Migrate customer profile from Danurejan (distA1) to Depok (distB1)
        app(ProfileTerritoryMigrationService::class)->migrate(
            $this->superAdmin,
            $this->customerA,
            $this->cityB->id,
            $this->distB1->id,
            'Pindah tempat tinggal ke Sleman'
        );
        $this->customerA->refresh();

        // Check: Thread is still the same record
        $freshSupport = $support->fresh();
        $this->assertEquals($support->id, $freshSupport->id);
        $this->assertEquals($msg1->id, $freshSupport->messages()->first()->id);

        // Canonical territory dynamically resolves to NEW profile territory B
        $canonical = $freshSupport->getCanonicalTerritory();
        $this->assertEquals('profile_reporter', $canonical['source']);
        $this->assertEquals($this->distB1->id, $canonical['district_id']);
        $this->assertEquals($this->cityB->id, $canonical['city_id']);

        // Listing in Admin A: Thread no longer appears
        $this->actingAs($this->adminA1);
        Livewire::test(\App\Livewire\Admin\Support\Index::class)
            ->assertDontSee($support->title);

        // Listing in Admin B: Thread now appears
        $this->actingAs($this->adminB1);
        Livewire::test(\App\Livewire\Admin\Support\Index::class)
            ->assertSee($support->title);
    }

    /**
     * R6: Old Admin direct access after migration -> DENY (403).
     */
    public function test_r6_old_admin_direct_access_after_migration_denied(): void
    {
        $support = PartnerReport::create([
            'reporter_id' => $this->customerA->id,
            'report_type' => 'dukungan_umum',
            'category'    => 'dari_customer',
            'title'       => 'Tiket Bantuan',
            'message'     => 'Pesan bantuan',
            'status'      => 'pending',
        ]);

        // Customer migrates from A to B
        app(ProfileTerritoryMigrationService::class)->migrate(
            $this->superAdmin,
            $this->customerA,
            $this->cityB->id,
            $this->distB1->id,
            'Migrasi profil'
        );
        $this->customerA->refresh();

        // Old Admin A1 mounting Support Chat must be aborted with 403
        $this->actingAs($this->adminA1);
        Livewire::test(\App\Livewire\Admin\Support\Chat::class, ['report' => $support->fresh()])
            ->assertStatus(403);
    }

    /**
     * R7: New Admin after migration -> ALLOW.
     */
    public function test_r7_new_admin_after_migration_allowed(): void
    {
        $support = PartnerReport::create([
            'reporter_id' => $this->customerA->id,
            'report_type' => 'dukungan_umum',
            'category'    => 'dari_customer',
            'title'       => 'Tiket Bantuan Baru',
            'message'     => 'Pesan bantuan',
            'status'      => 'pending',
        ]);

        // Customer migrates from A to B
        app(ProfileTerritoryMigrationService::class)->migrate(
            $this->superAdmin,
            $this->customerA,
            $this->cityB->id,
            $this->distB1->id,
            'Migrasi profil'
        );
        $this->customerA->refresh();

        // New Admin B1 mounting Support Chat must SUCCEED (200 OK)
        $this->actingAs($this->adminB1);
        Livewire::test(\App\Livewire\Admin\Support\Chat::class, ['report' => $support->fresh()])
            ->assertStatus(200)
            ->assertSee($support->message);
    }

    /**
     * R8: Help-linked report remains same territory after reporter profile migration.
     */
    public function test_r8_help_linked_report_remains_same_territory_after_reporter_profile_migration(): void
    {
        // Help order is permanently in District B
        $help = $this->createHelp([
            'city_id'     => $this->cityB->id,
            'district_id' => $this->distB1->id,
            'title'       => 'Order Pekerjaan Permanen B',
        ]);

        $report = PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_help_id' => $help->id,
            'report_type'      => 'pelanggaran_aturan',
            'title'            => 'Laporan Pelanggaran SOP Order B',
            'message'          => 'Detail keluhan',
            'status'           => 'pending',
        ]);

        // Customer migrates from A to C (Bantul)
        app(ProfileTerritoryMigrationService::class)->migrate(
            $this->superAdmin,
            $this->customerA,
            $this->cityC->id,
            $this->distC1->id,
            'Pindah ke Bantul'
        );
        $this->customerA->refresh();

        // Help-linked report remains firmly owned by B!
        $canonical = $report->fresh()->getCanonicalTerritory();
        $this->assertEquals('help', $canonical['source']);
        $this->assertEquals($this->distB1->id, $canonical['district_id']);
        $this->assertEquals($this->cityB->id, $canonical['city_id']);

        // Admin B1 still manages
        $this->actingAs($this->adminB1);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Show::class, ['report' => $report->fresh()])
            ->assertStatus(200);

        // Admin C1 does NOT gain ownership
        $this->actingAs($this->adminC1);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Show::class, ['report' => $report->fresh()])
            ->assertStatus(403);
    }

    /**
     * R9: Cancellation -> parent Help territory.
     */
    public function test_r9_cancellation_follows_parent_help_territory(): void
    {
        // Customer profile in A, Mitra profile in C, Help order in B
        $help = $this->createHelp([
            'mitra_id'    => $this->mitraC->id,
            'city_id'     => $this->cityB->id,
            'district_id' => $this->distB1->id,
            'title'       => 'Order Pembatalan di B',
        ]);

        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $help->id,
            'requested_by'    => $this->customerA->id,
            'requester_type'  => 'customer',
            'district_id'     => $help->district_id,
            'city_id'         => $help->city_id,
            'status'          => 'pending',
            'previous_status' => 'taken',
            'reason'          => 'Mitra berhalangan hadir',
        ]);

        // Listing check: Only Admin B1 sees the cancellation
        $this->actingAs($this->adminB1);
        Livewire::test(\App\Livewire\Admin\Disputes\Index::class, ['activeTab' => 'cancellations'])
            ->assertSee($cancelReq->reason);

        $this->actingAs($this->adminA1);
        Livewire::test(\App\Livewire\Admin\Disputes\Index::class, ['activeTab' => 'cancellations'])
            ->assertDontSee($cancelReq->reason);

        $this->actingAs($this->adminC1);
        Livewire::test(\App\Livewire\Admin\Disputes\Index::class, ['activeTab' => 'cancellations'])
            ->assertDontSee($cancelReq->reason);

        // Modal review authorization: Admin B1 allowed, Admin A1 denied
        $this->actingAs($this->adminB1);
        $component = Livewire::test(\App\Livewire\Admin\Disputes\Index::class)
            ->call('openCancelReviewModal', $cancelReq->id);
        $this->assertNotNull($component->get('selectedCancelRequest'));

        $this->actingAs($this->adminA1);
        $componentDenied = Livewire::test(\App\Livewire\Admin\Disputes\Index::class)
            ->call('openCancelReviewModal', $cancelReq->id);
        $this->assertNull($componentDenied->get('selectedCancelRequest'));
    }

    /**
     * R10: Dispute -> Help territory.
     */
    public function test_r10_dispute_follows_parent_help_territory(): void
    {
        // Customer profile in A, Mitra profile in C, Help order in B
        $help = $this->createHelp([
            'mitra_id'      => $this->mitraC->id,
            'city_id'       => $this->cityB->id,
            'district_id'   => $this->distB1->id,
            'title'         => 'Order Sengketa Escrow B',
            'status'        => 'waiting_confirmation',
            'escrow_status' => Help::ESCROW_STATUS_DISPUTED_FREEZE,
            'disputed_at'   => now(),
        ]);

        // Listing check: Admin B1 sees it in disputes tab
        $this->actingAs($this->adminB1);
        Livewire::test(\App\Livewire\Admin\Disputes\Index::class)
            ->call('setActiveTab', 'disputes')
            ->assertSee($help->title);

        $this->actingAs($this->adminA1);
        Livewire::test(\App\Livewire\Admin\Disputes\Index::class)
            ->call('setActiveTab', 'disputes')
            ->assertDontSee($help->title);

        // Resolve modal check: Admin B1 allowed, Admin A1 denied
        $this->actingAs($this->adminB1);
        $component = Livewire::test(\App\Livewire\Admin\Disputes\Index::class)
            ->call('openResolveModal', $help->id);
        $this->assertNotNull($component->get('selectedHelp'));

        $this->actingAs($this->adminA1);
        $componentDenied = Livewire::test(\App\Livewire\Admin\Disputes\Index::class)
            ->call('openResolveModal', $help->id);
        $this->assertNull($componentDenied->get('selectedHelp'));
    }

    /**
     * R11: District-only Admin denied City-level resource.
     */
    public function test_r11_district_only_admin_denied_city_level_resource(): void
    {
        // City-level Help resource (district_id = null, city_id = cityA)
        $cityHelp = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => null,
            'title'       => 'Order City-Level Kota Yogya',
        ]);

        $report = PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_help_id' => $cityHelp->id,
            'report_type'      => 'pelayanan_tidak_sesuai',
            'title'            => 'Laporan City-Level',
            'message'          => 'Detail keluhan',
            'status'           => 'pending',
        ]);

        // Admin A1 is district-only (Danurejan). Must be DENIED access to city-level resource
        $this->actingAs($this->adminA1);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Show::class, ['report' => $report])
            ->assertStatus(403);
    }

    /**
     * R12: Explicit City Admin allowed City-level resource.
     */
    public function test_r12_explicit_city_admin_allowed_city_level_resource(): void
    {
        // City-level Help resource
        $cityHelp = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => null,
            'title'       => 'Order City-Level Kota Yogya',
        ]);

        $report = PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_help_id' => $cityHelp->id,
            'report_type'      => 'pelayanan_tidak_sesuai',
            'title'            => 'Laporan City-Level',
            'message'          => 'Detail keluhan',
            'status'           => 'pending',
        ]);

        // AdminCityA has explicit city authority for cityA. Must be ALLOWED (200 OK)
        $this->actingAs($this->adminCityA);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Show::class, ['report' => $report])
            ->assertStatus(200)
            ->assertSee($report->title);
    }

    /**
     * R13: Listing and badge count use same ownership source.
     */
    public function test_r13_listing_and_badge_count_use_same_ownership_source(): void
    {
        // Create 2 reports in B1
        $helpB1 = $this->createHelp([
            'city_id'     => $this->cityB->id,
            'district_id' => $this->distB1->id,
            'title'       => 'Help B1',
        ]);

        PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_help_id' => $helpB1->id,
            'report_type'      => 'pelanggaran_aturan',
            'title'            => 'Aduan 1 B1',
            'message'          => 'Keluhan 1',
            'status'           => 'pending',
        ]);

        PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_help_id' => $helpB1->id,
            'report_type'      => 'pelayanan_tidak_sesuai',
            'title'            => 'Aduan 2 B1',
            'message'          => 'Keluhan 2',
            'status'           => 'in_progress',
        ]);

        // Create 1 general support in A1
        PartnerReport::create([
            'reporter_id' => $this->customerA->id,
            'report_type' => 'dukungan_umum',
            'title'       => 'Support Customer A1',
            'message'     => 'Bantuan customer',
            'status'      => 'pending',
        ]);

        // Badge count for Admin B1: exactly 2 active reports, 0 support
        $this->assertEquals(2, PartnerReport::getActiveReportsCountForUser($this->adminB1));
        $this->assertEquals(0, PartnerReport::getActiveSupportCountForUser($this->adminB1));

        // Badge count for Admin A1: 0 reports (since customerA is only reporter on Help B1), 1 support
        $this->assertEquals(0, PartnerReport::getActiveReportsCountForUser($this->adminA1));
        $this->assertEquals(1, PartnerReport::getActiveSupportCountForUser($this->adminA1));
    }

    /**
     * R14: Direct URL outside territory -> DENY.
     */
    public function test_r14_direct_url_outside_territory_denied_with_403(): void
    {
        $help = $this->createHelp([
            'city_id'     => $this->cityB->id,
            'district_id' => $this->distB1->id,
            'title'       => 'Order di Sleman B',
        ]);

        $report = PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_help_id' => $help->id,
            'report_type'      => 'penipuan',
            'title'            => 'Laporan Penipuan di Sleman',
            'message'          => 'Detail penipuan',
            'status'           => 'pending',
        ]);

        // Admin C1 from Bantul tries to access direct URL -> 403
        $this->actingAs($this->adminC1);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Show::class, ['report' => $report])
            ->assertStatus(403);
    }

    /**
     * R15: No territory -> regional Admin DENY.
     */
    public function test_r15_no_territory_regional_admin_denied(): void
    {
        $userNoTerritory = User::factory()->create([
            'role'        => 'customer',
            'city_id'     => null,
            'district_id' => null,
            'status'      => 'active',
        ]);

        // Report with no help, no territory on reporter
        $reportOrphan = PartnerReport::create([
            'reporter_id'      => $userNoTerritory->id,
            'reported_user_id' => null,
            'reported_help_id' => null,
            'report_type'      => 'dukungan_umum',
            'title'            => 'Laporan Yatim',
            'message'          => 'Tidak ada wilayah',
            'status'           => 'pending',
            'city_id'          => null,
            'district_id'      => null,
        ]);

        $canonical = $reportOrphan->getCanonicalTerritory();
        $this->assertNull($canonical['district_id']);
        $this->assertNull($canonical['city_id']);

        // Regional admin must be DENIED (fail closed)
        $this->actingAs($this->adminA1);
        Livewire::test(\App\Livewire\Admin\Support\Chat::class, ['report' => $reportOrphan])
            ->assertStatus(403);
    }

    /**
     * R16: Non-Help user report (reported_user_id != null) -> Reported User Profile Territory.
     */
    public function test_r16_non_help_user_report_belongs_to_reported_user_profile_territory(): void
    {
        // Customer A (Wilayah A) reports Mitra B (Wilayah B) without Help
        $report = PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_user_id' => $this->mitraB->id,
            'reported_help_id' => null,
            'report_type'      => 'penipuan',
            'title'            => 'Laporan Penipuan Mitra B',
            'message'          => 'Mitra meminta transfer di luar aplikasi',
            'status'           => 'pending',
        ]);

        $canonical = $report->getCanonicalTerritory();
        $this->assertEquals('profile_reported_user', $canonical['source']);
        $this->assertEquals($this->distB1->id, $canonical['district_id']);
        $this->assertEquals($this->cityB->id, $canonical['city_id']);

        // Admin B1 (Wilayah B - Mitra) manages the report
        $this->actingAs($this->adminB1);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Index::class)
            ->assertSee($report->title);

        Livewire::test(\App\Livewire\Admin\Partners\Reports\Show::class, ['report' => $report])
            ->assertStatus(200);

        // Admin A1 (Wilayah A - Customer) does NOT gain ownership
        $this->actingAs($this->adminA1);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Index::class)
            ->assertDontSee($report->title);

        Livewire::test(\App\Livewire\Admin\Partners\Reports\Show::class, ['report' => $report])
            ->assertStatus(403);
    }

    /**
     * R17: Non-Help, bukan dukungan_umum, no Help, no reported_user -> UNRESOLVED and DENY regional admin.
     */
    public function test_r17_non_help_non_support_without_reported_user_is_unresolved_and_denies_regional_admin(): void
    {
        $report = PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_user_id' => null,
            'reported_help_id' => null,
            'report_type'      => 'pelanggaran_aturan',
            'title'            => 'Laporan Aturan Tanpa Target Pengguna',
            'message'          => 'Laporan pelanggaran aturan umum tanpa pelaku spesifik',
            'status'           => 'pending',
        ]);

        $canonical = $report->getCanonicalTerritory();
        $this->assertEquals('unresolved', $canonical['source']);
        $this->assertNull($canonical['district_id']);
        $this->assertNull($canonical['city_id']);

        // Regional Admin A1 (reporter's district) must be DENIED (403) - NO fallback to reporter profile!
        $this->actingAs($this->adminA1);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Show::class, ['report' => $report])
            ->assertStatus(403);

        // Regional Admin B1 must also be DENIED (403)
        $this->actingAs($this->adminB1);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Show::class, ['report' => $report])
            ->assertStatus(403);
    }

    /**
     * R18: Unresolved report does not appear in regional listing or regional counts.
     */
    public function test_r18_unresolved_report_does_not_appear_in_regional_listing(): void
    {
        $report = PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_user_id' => null,
            'reported_help_id' => null,
            'report_type'      => 'lainnya',
            'title'            => 'Laporan Lainnya Tanpa Target',
            'message'          => 'Keluhan umum tanpa order dan tanpa user target',
            'status'           => 'pending',
        ]);

        // Regional Admin A1 must NOT see the report in listing
        $this->actingAs($this->adminA1);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Index::class)
            ->assertDontSee($report->title);

        // Regional Admin B1 must NOT see the report in listing
        $this->actingAs($this->adminB1);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Index::class)
            ->assertDontSee($report->title);

        // Active reports count for regional admins must be 0
        $this->assertEquals(0, PartnerReport::getActiveReportsCountForUser($this->adminA1));
        $this->assertEquals(0, PartnerReport::getActiveReportsCountForUser($this->adminB1));
    }

    /**
     * R19: Unresolved report does not generate regional broadcast notifications.
     */
    public function test_r19_unresolved_report_does_not_broadcast_regional_notifications(): void
    {
        $report = PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_user_id' => null,
            'reported_help_id' => null,
            'report_type'      => 'penipuan',
            'title'            => 'Laporan Penipuan Umum Tanpa Akun',
            'message'          => 'Ditemukan indikasi penipuan di platform',
            'status'           => 'pending',
        ]);

        $canonical = $report->getCanonicalTerritory();
        $this->assertEquals('unresolved', $canonical['source']);

        $notificationService = app(\App\Services\AccountNotificationService::class);
        $recipients = $notificationService->resolveAdminsForTerritory(
            $canonical['district_id'],
            $canonical['city_id'],
            includeSuperAdmin: false
        );

        $this->assertCount(0, $recipients);
    }

    /**
     * R20: SuperAdmin can still access and list unresolved reports under global access.
     */
    public function test_r20_superadmin_can_access_and_list_unresolved_reports(): void
    {
        $report = PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_user_id' => null,
            'reported_help_id' => null,
            'report_type'      => 'pelanggaran_aturan',
            'title'            => 'Laporan Khusus SuperAdmin',
            'message'          => 'Laporan tanpa wilayah yang dapat ditangani oleh pusat',
            'status'           => 'pending',
        ]);

        // SuperAdmin can access direct show route (200 OK)
        $this->actingAs($this->superAdmin);
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Show::class, ['report' => $report])
            ->assertStatus(200)
            ->assertSee($report->title);

        // SuperAdmin listing without territory filter sees the report
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Index::class)
            ->assertSee($report->title);
    }
}
