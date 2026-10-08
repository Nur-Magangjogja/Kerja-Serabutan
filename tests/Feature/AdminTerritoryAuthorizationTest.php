<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\Province;
use App\Models\User;
use App\Models\Help;
use App\Models\PartnerReport;
use App\Models\HelpCancelRequest;
use App\Models\Registration;
use App\Models\BalanceTransaction;
use App\Models\WithdrawRequest;
use App\Services\Territory\AdminTerritoryAuthorizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTerritoryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected AdminTerritoryAuthorizationService $authService;
    protected Province $provinceDIY;
    protected City $cityA; // Kota Yogyakarta
    protected District $distA1; // Danurejan
    protected District $distA2; // Gondomanan

    protected City $cityB; // Kabupaten Sleman
    protected District $distB1; // Depok
    protected District $distB2; // Mlati

    protected City $cityC; // Kota Semarang

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = app(AdminTerritoryAuthorizationService::class);

        $this->provinceDIY = Province::create(['name' => 'DI Yogyakarta', 'code' => '34', 'is_active' => true]);

        // City A
        $this->cityA = City::create(['name' => 'Kota Yogyakarta', 'province_id' => $this->provinceDIY->id, 'province' => 'DI Yogyakarta', 'is_active' => true]);
        $this->distA1 = District::create(['city_id' => $this->cityA->id, 'name' => 'Danurejan', 'is_active' => true]);
        $this->distA2 = District::create(['city_id' => $this->cityA->id, 'name' => 'Gondomanan', 'is_active' => true]);

        // City B
        $this->cityB = City::create(['name' => 'Kabupaten Sleman', 'province_id' => $this->provinceDIY->id, 'province' => 'DI Yogyakarta', 'is_active' => true]);
        $this->distB1 = District::create(['city_id' => $this->cityB->id, 'name' => 'Depok', 'is_active' => true]);
        $this->distB2 = District::create(['city_id' => $this->cityB->id, 'name' => 'Mlati', 'is_active' => true]);

        // City C
        $this->cityC = City::create(['name' => 'Kota Semarang', 'is_active' => true]);

        $this->superAdmin = User::factory()->create(['role' => 'super_admin', 'status' => 'active']);
    }

    // =========================================================================
    // SECTION 1: CORE CANONICAL SPEC TESTS (AUTH1 - AUTH16)
    // =========================================================================

    public function test_auth1_superadmin_any_valid_territory_allowed(): void
    {
        // District valid
        $this->assertTrue($this->authService->canAccessTerritory($this->superAdmin, $this->distA1->id, $this->cityA->id));
        $this->assertTrue($this->authService->canAccessTerritory($this->superAdmin, $this->distB1->id, null));
        // City valid, district null
        $this->assertTrue($this->authService->canAccessTerritory($this->superAdmin, null, $this->cityA->id));
        $this->assertTrue($this->authService->canAccessTerritory($this->superAdmin, null, $this->cityB->id));
    }

    public function test_auth2_city_a_admin_district_a1_allowed(): void
    {
        // City assignment ALONE does not grant district authority
        $cityOnlyAdmin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $cityOnlyAdmin->managedCities()->sync([$this->cityA->id]);

        $this->assertFalse($this->authService->canAccessTerritory($cityOnlyAdmin, $this->distA1->id, $this->cityA->id));
        $this->assertFalse($this->authService->canAccessTerritory($cityOnlyAdmin, $this->distA2->id, $this->cityA->id));

        // Exact district assignment grants authority to assigned district only, not sibling
        $districtAdmin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $districtAdmin->managedDistricts()->sync([$this->distA1->id]);

        $this->assertTrue($this->authService->canAccessTerritory($districtAdmin, $this->distA1->id, $this->cityA->id));
        $this->assertFalse($this->authService->canAccessTerritory($districtAdmin, $this->distA2->id, $this->cityA->id));
    }

    public function test_auth3_explicit_city_a_admin_city_a_district_null_allowed(): void
    {
        // Admin with assigned district in City A derives parent City A authority for city-level resources
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedDistricts()->sync([$this->distA1->id]);
        $admin->managedCities()->sync([$this->cityA->id]);

        $this->assertTrue($this->authService->canAccessTerritory($admin, null, $this->cityA->id));

        // An orphan admin_city row without assigned districts has ZERO authority (AW1-V Case A)
        $orphanAdmin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $orphanAdmin->managedCities()->sync([$this->cityA->id]);

        $this->assertFalse($this->authService->canAccessTerritory($orphanAdmin, null, $this->cityA->id));
    }

    public function test_auth4_district_b1_admin_district_b1_allowed(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedDistricts()->sync([$this->distB1->id]);

        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distB1->id, $this->cityB->id));
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distB1->id, null));
    }

    public function test_auth5_district_b1_admin_district_b2_denied(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedDistricts()->sync([$this->distB1->id]);

        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distB2->id, $this->cityB->id));
    }

    public function test_auth6_district_b1_admin_city_b_district_null_allowed_city_a_denied(): void
    {
        // Assigned district B1 derives parent city B context, allowing city-level resources in City B
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedDistricts()->sync([$this->distB1->id]);

        $this->assertTrue($this->authService->canAccessTerritory($admin, null, $this->cityB->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->cityA->id));
    }

    public function test_auth7_mixed_city_a_and_district_b1(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedCities()->sync([$this->cityA->id]);
        $admin->managedDistricts()->sync([$this->distB1->id]);

        // A districts are NOT in managedDistricts -> DENY
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distA1->id, $this->cityA->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distA2->id, $this->cityA->id));

        // City A null district -> DENY (orphan city row: 0 districts assigned in City A)
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->cityA->id));

        // B1 -> ALLOW (in managedDistricts)
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distB1->id, $this->cityB->id));

        // City B null district -> ALLOW (derived parent city from assigned district B1)
        $this->assertTrue($this->authService->canAccessTerritory($admin, null, $this->cityB->id));

        // B2 -> DENY (sibling not in managedDistricts)
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distB2->id, $this->cityB->id));
    }

    public function test_auth8_explicit_city_a_and_profile_city_c_denies_c(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'city_id' => $this->cityC->id,
        ]);
        $admin->managedDistricts()->sync([$this->distA1->id]);
        $admin->managedCities()->sync([$this->cityA->id]);

        // City A -> ALLOW (derived parent city from assigned district A1)
        $this->assertTrue($this->authService->canAccessTerritory($admin, null, $this->cityA->id));

        // Profile City C -> DENY (no leak)
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->cityC->id));
    }

    public function test_auth9_navbar_filter_a1_while_full_authority_includes_a2_allows_a2(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedDistricts()->sync([$this->distA1->id, $this->distA2->id]);
        $admin->managedCities()->sync([$this->cityA->id]);

        // Admin filters navbar to A1
        $admin->setActiveAdminDistrictFilter((string) $this->distA1->id);
        $this->assertEquals([(int) $this->distA1->id], $admin->getEffectiveAdminDistrictIds());

        // But security authority for direct resource in A2 remains ALLOWED!
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distA2->id, $this->cityA->id));
    }

    public function test_auth10_district_a1_with_incorrect_city_b_denied(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedDistricts()->sync([$this->distA1->id]);
        $admin->managedCities()->sync([$this->cityA->id, $this->cityB->id]);

        // Inconsistent pair: A1 belongs to City A, not City B
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distA1->id, $this->cityB->id));
    }

    public function test_auth11_nonexistent_district_denied(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedCities()->sync([$this->cityA->id]);

        $this->assertFalse($this->authService->canAccessTerritory($admin, 999999, $this->cityA->id));
        $this->assertFalse($this->authService->canAccessTerritory($this->superAdmin, 999999, $this->cityA->id));
    }

    public function test_auth12_nonexistent_city_denied(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedCities()->sync([$this->cityA->id]);

        $this->assertFalse($this->authService->canAccessTerritory($admin, null, 999999));
        $this->assertFalse($this->authService->canAccessTerritory($this->superAdmin, null, 999999));
    }

    public function test_auth13_null_district_and_null_city_denied(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedCities()->sync([$this->cityA->id]);

        $this->assertFalse($this->authService->canAccessTerritory($admin, null, null));
        $this->assertFalse($this->authService->canAccessTerritory($this->superAdmin, null, null));
    }

    public function test_auth14_profile_city_only_admin_without_pivots_denies_all_authority(): void
    {
        // Zero territory: no pivots, profile city_id = City A, district_id = null
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'city_id' => $this->cityA->id,
            'district_id' => null,
        ]);

        // Zero territory rule: profile location does NOT grant admin authority
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->cityA->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distA1->id, $this->cityA->id));
    }

    public function test_auth15_profile_district_only_admin_without_pivots_denies_all_authority(): void
    {
        // Zero territory: no pivots, profile city_id = City A, profile district_id = A1
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'city_id' => $this->cityA->id,
            'district_id' => $this->distA1->id,
        ]);

        // Zero territory rule: profile district does NOT grant admin authority
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distA1->id, $this->cityA->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->cityA->id));
    }

    public function test_auth16_explicit_district_b1_allows_parent_city_b_denies_other_cities(): void
    {
        // Admin has explicit managedDistricts = [B1], and has profile city_id = City B
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'city_id' => $this->cityB->id,
            'district_id' => $this->distB1->id,
        ]);
        $admin->managedDistricts()->sync([$this->distB1->id]);

        // Explicit District B1 -> ALLOW
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distB1->id, $this->cityB->id));

        // City B null district -> ALLOW (derived parent city from assigned district B1)
        $this->assertTrue($this->authService->canAccessTerritory($admin, null, $this->cityB->id));

        // Other cities (City A, City C) null district -> DENY (no leak)
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->cityA->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->cityC->id));

        // Sibling district in City B -> DENY
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distB2->id, $this->cityB->id));
    }

    // =========================================================================
    // SECTION 2: REPRESENTATIVE SECURITY GUARD TESTS (GUARD1 - GUARD9)
    // =========================================================================

    public function test_guard1_partner_report_show_outside_territory_aborts_403(): void
    {
        $adminB = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminB->managedCities()->sync([$this->cityB->id]);

        $customerA = User::factory()->create(['role' => 'customer', 'city_id' => $this->cityA->id, 'district_id' => $this->distA1->id]);
        $reportA = PartnerReport::create([
            'reporter_id' => $customerA->id,
            'report_type' => 'aduan_mitra',
            'status'      => 'pending',
            'title'       => 'Laporan di Wilayah A',
            'message'     => 'Deskripsi keluhan',
        ]);

        $this->actingAs($adminB);

        Livewire::test(\App\Livewire\Admin\Partners\Reports\Show::class, ['report' => $reportA])
            ->assertStatus(403);
    }

    public function test_guard2_partner_report_chat_outside_territory_redirects_with_error(): void
    {
        $adminB = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminB->managedCities()->sync([$this->cityB->id]);

        $customerA = User::factory()->create(['role' => 'customer', 'city_id' => $this->cityA->id, 'district_id' => $this->distA1->id]);
        $reportA = PartnerReport::create([
            'reporter_id' => $customerA->id,
            'report_type' => 'aduan_mitra',
            'status'      => 'pending',
            'title'       => 'Laporan di Wilayah A',
            'message'     => 'Deskripsi keluhan',
        ]);

        $this->actingAs($adminB);

        Livewire::test(\App\Livewire\Admin\Partners\Reports\Chat::class, ['report' => $reportA])
            ->assertRedirect(route('admin.partners.reports'));
    }

    public function test_guard3_admin_support_chat_valid_territory_outside_navbar_filter_allowed(): void
    {
        // Admin A manages distA1 and distA2 in City A
        $adminA = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminA->managedDistricts()->sync([$this->distA1->id, $this->distA2->id]);
        $adminA->managedCities()->sync([$this->cityA->id]);

        // Admin filters navbar to distA1
        $adminA->setActiveAdminDistrictFilter((string) $this->distA1->id);

        // Support report is in distA2 (still within City A authority!)
        $customerA2 = User::factory()->create(['role' => 'customer', 'city_id' => $this->cityA->id, 'district_id' => $this->distA2->id]);
        $supportA2 = PartnerReport::create([
            'reporter_id' => $customerA2->id,
            'report_type' => 'dukungan_umum',
            'status'      => 'pending',
            'title'       => 'Dukungan Umum A2',
            'message'     => 'Pertanyaan akun',
        ]);

        $this->actingAs($adminA);

        // Mounting Support Chat MUST SUCCEED (not 403!) because A2 is within admin's lawful authority
        Livewire::test(\App\Livewire\Admin\Support\Chat::class, ['report' => $supportA2])
            ->assertSee($customerA2->name)
            ->assertHasNoErrors();
    }

    public function test_guard4_dispute_resolve_modal_outside_full_authority_denied(): void
    {
        $adminB = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminB->managedCities()->sync([$this->cityB->id]);

        $customerA = User::factory()->create(['role' => 'customer', 'city_id' => $this->cityA->id, 'district_id' => $this->distA1->id]);
        $helpA = Help::create([
            'user_id'      => $customerA->id,
            'customer_id'  => $customerA->id,
            'city_id'      => $this->cityA->id,
            'district_id'  => $this->distA1->id,
            'title'        => 'Help Sengketa di Wilayah A',
            'description'  => 'Deskripsi sengketa',
            'amount'       => 50000,
            'total_amount' => 50000,
            'status'       => 'selesai',
        ]);

        $this->actingAs($adminB);

        Livewire::test(\App\Livewire\Admin\Disputes\Index::class)
            ->call('openResolveModal', $helpA->id)
            ->assertSee('Anda tidak memiliki wewenang untuk menyelesaikan sengketa di luar wilayah Anda.')
            ->assertSet('selectedHelpId', null)
            ->assertSet('showResolveModal', false);
    }

    public function test_guard5_cancellation_review_modal_and_chat_outside_full_authority_denied(): void
    {
        $adminB = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminB->managedCities()->sync([$this->cityB->id]);

        $customerA = User::factory()->create(['role' => 'customer', 'city_id' => $this->cityA->id, 'district_id' => $this->distA1->id]);
        $helpA = Help::create([
            'user_id'      => $customerA->id,
            'customer_id'  => $customerA->id,
            'city_id'      => $this->cityA->id,
            'district_id'  => $this->distA1->id,
            'title'        => 'Help Batal di Wilayah A',
            'description'  => 'Deskripsi batal',
            'amount'       => 50000,
            'total_amount' => 50000,
            'status'       => 'batal_mitra',
        ]);

        $cancelRequestA = HelpCancelRequest::create([
            'help_id'         => $helpA->id,
            'requested_by'    => $customerA->id,
            'requester_type'  => 'customer',
            'reason'          => 'Permintaan pembatalan customer',
            'previous_status' => 'menunggu_mitra',
            'status'          => 'pending',
            'city_id'         => $this->cityA->id,
            'district_id'     => $this->distA1->id,
        ]);

        $this->actingAs($adminB);

        // 1. Cancel Review Modal DENIED
        Livewire::test(\App\Livewire\Admin\Disputes\Index::class)
            ->call('openCancelReviewModal', $cancelRequestA->id)
            ->assertSee('Anda tidak memiliki wewenang untuk meninjau pembatalan di luar wilayah Anda.')
            ->assertSet('selectedCancelRequestId', null)
            ->assertSet('showCancelReviewModal', false);

        // 2. Cancellation Chat DENIED
        Livewire::test(\App\Livewire\Admin\Disputes\Chat::class, ['cancelRequest' => $cancelRequestA])
            ->assertRedirect(route('admin.cancellations.index'));
    }

    public function test_guard6_ktp_action_outside_full_authority_denied(): void
    {
        $adminB = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminB->managedCities()->sync([$this->cityB->id]);

        $regA = Registration::create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'name'        => 'Calon Customer A',
            'email'       => 'calon_a@example.com',
            'phone'       => '081234567890',
            'nik'         => '3471012345678901',
            'role'        => 'customer',
            'status'      => 'pending_verification',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
        ]);

        $this->actingAs($adminB);

        Livewire::test(\App\Livewire\Admin\Verifications\Index::class)
            ->call('viewKtp', $regA->id)
            ->assertSee('Data tidak ditemukan atau berada di luar wilayah wewenang Anda.');
    }

    public function test_guard7_topup_approval_without_applicable_territory_denied(): void
    {
        $adminB = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminB->managedCities()->sync([$this->cityB->id]);

        $customerA = User::factory()->create(['role' => 'customer', 'city_id' => $this->cityA->id, 'district_id' => $this->distA1->id]);
        $txA = BalanceTransaction::create([
            'user_id'          => $customerA->id,
            'amount'           => 100000,
            'type'             => 'topup',
            'status'           => 'pending',
            'transaction_code' => 'TOP-TEST-A',
        ]);

        $this->actingAs($adminB);

        Livewire::test(\App\Livewire\Admin\Topup\Approval::class)
            ->call('viewDetail', $txA->id)
            ->assertSee('Transaksi tidak ditemukan atau berada di luar wilayah wewenang Anda.');
    }

    public function test_guard8_withdraw_approval_without_applicable_territory_denied(): void
    {
        $adminB = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminB->managedCities()->sync([$this->cityB->id]);

        $mitraA = User::factory()->create(['role' => 'mitra', 'city_id' => $this->cityA->id, 'district_id' => $this->distA1->id]);
        $withdrawA = WithdrawRequest::create([
            'user_id'        => $mitraA->id,
            'amount'         => 100000,
            'status'         => 'pending',
            'bank_code'      => 'BCA',
            'account_number' => '1234567890',
            'account_name'   => 'Mitra A',
        ]);

        $this->actingAs($adminB);

        Livewire::test(\App\Livewire\Admin\Withdraws\Index::class)
            ->call('openReviewModal', $withdrawA->id)
            ->assertSee('Anda tidak memiliki wewenang untuk memproses penarikan dana dari luar wilayah wewenang Anda.')
            ->assertSet('selectedWithdrawId', null)
            ->assertSet('showReviewModal', false);
    }

    public function test_guard9_notification_or_url_does_not_grant_access(): void
    {
        // Admin B receives URL / direct link to a report in Territory A
        $adminB = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminB->managedCities()->sync([$this->cityB->id]);

        $customerA = User::factory()->create(['role' => 'customer', 'city_id' => $this->cityA->id, 'district_id' => $this->distA1->id]);
        $reportA = PartnerReport::create([
            'reporter_id' => $customerA->id,
            'report_type' => 'aduan_mitra',
            'status'      => 'pending',
            'title'       => 'Aduan Mitra A',
            'message'     => 'Deskripsi keluhan',
        ]);

        $this->actingAs($adminB);

        // Attempting to open the Show page via direct URL fails closed with 403
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Show::class, ['report' => $reportA])
            ->assertStatus(403);
    }
}
