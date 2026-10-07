<?php

namespace Tests\Feature;

use App\Livewire\SuperAdmin\Users\AdminUsers;
use App\Livewire\SuperAdmin\Users\Index as UsersIndex;
use App\Models\City;
use App\Models\District;
use App\Models\Province;
use App\Models\User;
use App\Services\Territory\AdminTerritoryAuthorizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminExactDistrictAuthorityAW2Test extends TestCase
{
    use RefreshDatabase;

    protected AdminTerritoryAuthorizationService $authService;
    protected User $superAdmin;
    protected Province $provinceDIY;

    // City Sleman
    protected City $citySleman;
    protected District $distNgaglik;
    protected District $distDepok;
    protected District $distMlati;

    // City Yogyakarta
    protected City $cityYogya;
    protected District $distDanurejan;
    protected District $distGondomanan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = app(AdminTerritoryAuthorizationService::class);

        $this->superAdmin = User::factory()->create([
            'role'     => 'super_admin',
            'status'   => 'active',
            'nik'      => '3400000000000001',
            'password' => Hash::make('superadmin123'),
        ]);

        $this->provinceDIY = Province::create([
            'name'      => 'DI Yogyakarta',
            'code'      => '34',
            'is_active' => true,
        ]);

        // Sleman
        $this->citySleman = City::create([
            'name'        => 'Kabupaten Sleman',
            'province_id' => $this->provinceDIY->id,
            'province'    => 'DI Yogyakarta',
            'is_active'   => true,
        ]);
        $this->distNgaglik = District::create(['city_id' => $this->citySleman->id, 'name' => 'Ngaglik', 'is_active' => true]);
        $this->distDepok = District::create(['city_id' => $this->citySleman->id, 'name' => 'Depok', 'is_active' => true]);
        $this->distMlati = District::create(['city_id' => $this->citySleman->id, 'name' => 'Mlati', 'is_active' => true]);

        // Yogya
        $this->cityYogya = City::create([
            'name'        => 'Kota Yogyakarta',
            'province_id' => $this->provinceDIY->id,
            'province'    => 'DI Yogyakarta',
            'is_active'   => true,
        ]);
        $this->distDanurejan = District::create(['city_id' => $this->cityYogya->id, 'name' => 'Danurejan', 'is_active' => true]);
        $this->distGondomanan = District::create(['city_id' => $this->cityYogya->id, 'name' => 'Gondomanan', 'is_active' => true]);
    }

    /**
     * AW2-1: Single District Assignment
     * SuperAdmin selects Ngaglik -> only Ngaglik attached. Depok & Mlati NOT attached.
     */
    public function test_aw2_1_single_district_assignment(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(AdminUsers::class)
            ->call('openCreateModal')
            ->set('name', 'Admin Ngaglik')
            ->set('email', 'admin.ngaglik@example.com')
            ->set('password', 'password123')
            ->set('status', 'active')
            ->set('managed_district_ids', [$this->distNgaglik->id])
            ->call('saveUser')
            ->assertHasNoErrors();

        $admin = User::where('email', 'admin.ngaglik@example.com')->first();
        $this->assertNotNull($admin);

        $assignedIds = $admin->managedDistricts()->pluck('districts.id')->all();
        $this->assertEquals([(int) $this->distNgaglik->id], $assignedIds);

        // Verify siblings are NOT attached
        $this->assertNotContains((int) $this->distDepok->id, $assignedIds);
        $this->assertNotContains((int) $this->distMlati->id, $assignedIds);

        // Security check
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distDepok->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distMlati->id, $this->citySleman->id));
    }

    /**
     * AW2-2: Same City Multiple Districts
     * Select Ngaglik, Depok -> exact set [Ngaglik, Depok]. No other Sleman district.
     */
    public function test_aw2_2_same_city_multiple_districts(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(AdminUsers::class)
            ->call('openCreateModal')
            ->set('name', 'Admin Sleman East')
            ->set('email', 'admin.sleman.east@example.com')
            ->set('password', 'password123')
            ->set('status', 'active')
            ->set('managed_district_ids', [$this->distNgaglik->id, $this->distDepok->id])
            ->call('saveUser')
            ->assertHasNoErrors();

        $admin = User::where('email', 'admin.sleman.east@example.com')->first();
        $this->assertNotNull($admin);

        $assignedIds = $admin->managedDistricts()->pluck('districts.id')->map('intval')->all();
        sort($assignedIds);
        $expected = [(int) $this->distNgaglik->id, (int) $this->distDepok->id];
        sort($expected);
        $this->assertEquals($expected, $assignedIds);

        // Sibling Mlati NOT attached
        $this->assertNotContains((int) $this->distMlati->id, $assignedIds);

        // Authority verification
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distDepok->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distMlati->id, $this->citySleman->id));
    }

    /**
     * AW2-3: Cross-City Assignment
     * Select Ngaglik (Sleman) and Gondomanan (Yogya) -> exactly those two.
     * Parent city metadata derived (Sleman & Yogya), siblings in both cities denied.
     */
    public function test_aw2_3_cross_city_assignment(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(AdminUsers::class)
            ->call('openCreateModal')
            ->set('name', 'Admin Cross')
            ->set('email', 'admin.cross@example.com')
            ->set('password', 'password123')
            ->set('status', 'active')
            ->set('managed_district_ids', [$this->distNgaglik->id, $this->distGondomanan->id])
            ->call('saveUser')
            ->assertHasNoErrors();

        $admin = User::where('email', 'admin.cross@example.com')->first();
        $this->assertNotNull($admin);

        $assignedDistrictIds = $admin->managedDistricts()->pluck('districts.id')->map('intval')->all();
        sort($assignedDistrictIds);
        $expectedDistrictIds = [(int) $this->distNgaglik->id, (int) $this->distGondomanan->id];
        sort($expectedDistrictIds);
        $this->assertEquals($expectedDistrictIds, $assignedDistrictIds);

        // Parent city metadata contains both
        $assignedCityIds = $admin->managedCities()->pluck('cities.id')->map('intval')->all();
        $this->assertContains((int) $this->citySleman->id, $assignedCityIds);
        $this->assertContains((int) $this->cityYogya->id, $assignedCityIds);

        // Sibling districts in both cities are strictly DENIED
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distDepok->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distMlati->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distDanurejan->id, $this->cityYogya->id));

        // Exact assigned districts are ALLOWED
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distGondomanan->id, $this->cityYogya->id));
    }

    /**
     * AW2-4: Deselect One
     * Initial: Ngaglik, Depok.
     * SuperAdmin unchecks Depok and saves -> Depok detached immediately.
     */
    public function test_aw2_4_deselect_one_district(): void
    {
        $admin = User::factory()->create([
            'role'   => 'admin',
            'status' => 'active',
            'nik'    => '3400000000000002',
        ]);
        $admin->managedDistricts()->sync([$this->distNgaglik->id, $this->distDepok->id]);
        $admin->managedCities()->sync([$this->citySleman->id]);

        Livewire::actingAs($this->superAdmin)
            ->test(AdminUsers::class)
            ->call('editUser', $admin->id)
            ->assertSet('managed_district_ids', [(int) $this->distNgaglik->id, (int) $this->distDepok->id])
            ->call('toggleDistrict', $this->distDepok->id) // uncheck Depok
            ->assertSet('managed_district_ids', [(int) $this->distNgaglik->id])
            ->set('adminPassword', 'superadmin123')
            ->call('saveUser')
            ->assertHasNoErrors();

        $admin->refresh();
        $assignedIds = $admin->managedDistricts()->pluck('districts.id')->map('intval')->all();
        $this->assertEquals([(int) $this->distNgaglik->id], $assignedIds);

        // Authority for Depok is revoked immediately
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distDepok->id, $this->citySleman->id));
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
    }

    /**
     * AW2-5: Remove All Districts
     * Initial: Ngaglik.
     * Save [] -> managedDistricts = [], effective authority = [], label = 'Belum Ada Wilayah'.
     */
    public function test_aw2_5_remove_all_districts_grants_zero_authority(): void
    {
        $admin = User::factory()->create([
            'role'   => 'admin',
            'status' => 'active',
            'nik'    => '3400000000000003',
        ]);
        $admin->managedDistricts()->sync([$this->distNgaglik->id]);
        $admin->managedCities()->sync([$this->citySleman->id]);

        Livewire::actingAs($this->superAdmin)
            ->test(AdminUsers::class)
            ->call('editUser', $admin->id)
            ->call('clearAllDistricts')
            ->assertSet('managed_district_ids', [])
            ->set('adminPassword', 'superadmin123')
            ->call('saveUser')
            ->assertHasNoErrors();

        $admin->refresh();
        $this->assertEquals([], $admin->managedDistricts()->pluck('districts.id')->all());
        $this->assertEquals([], $admin->getAdminDistrictIds());
        $this->assertEquals([], $admin->getEffectiveAdminDistrictIds());
        $this->assertEquals('Belum Ada Wilayah', $admin->admin_district_names);
        $this->assertEquals('Belum Ada Wilayah', $admin->active_admin_district_label);

        // Existing action guard strictly denies
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->citySleman->id));
    }

    /**
     * AW2-6: Edit Form Hydration
     * Existing exact assignments: Ngaglik, Gondomanan.
     * Open edit modal -> only Ngaglik, Gondomanan are in managed_district_ids.
     * No sibling district selected due to parent city metadata.
     */
    public function test_aw2_6_edit_form_hydration_exact_checkbox_state(): void
    {
        $admin = User::factory()->create([
            'role'   => 'admin',
            'status' => 'active',
            'nik'    => '3400000000000004',
        ]);
        $admin->managedDistricts()->sync([$this->distNgaglik->id, $this->distGondomanan->id]);
        $admin->managedCities()->sync([$this->citySleman->id, $this->cityYogya->id]);

        $component = Livewire::actingAs($this->superAdmin)
            ->test(AdminUsers::class)
            ->call('editUser', $admin->id);

        $selectedInForm = $component->get('managed_district_ids');
        sort($selectedInForm);
        $expected = [(int) $this->distNgaglik->id, (int) $this->distGondomanan->id];
        sort($expected);

        $this->assertEquals($expected, $selectedInForm);
        $this->assertNotContains((int) $this->distDepok->id, $selectedInForm);
        $this->assertNotContains((int) $this->distMlati->id, $selectedInForm);
        $this->assertNotContains((int) $this->distDanurejan->id, $selectedInForm);
    }

    /**
     * AW2-7: Repeated Save Idempotency
     * Saving the same selection twice results in clean state without duplicate pivots.
     */
    public function test_aw2_7_repeated_save_is_idempotent(): void
    {
        $component = Livewire::actingAs($this->superAdmin)
            ->test(AdminUsers::class)
            ->call('openCreateModal')
            ->set('name', 'Admin Idempotent')
            ->set('email', 'admin.idempotent@example.com')
            ->set('password', 'password123')
            ->set('status', 'active')
            ->set('managed_district_ids', [$this->distNgaglik->id, $this->distDepok->id])
            ->call('saveUser')
            ->assertHasNoErrors();

        $admin = User::where('email', 'admin.idempotent@example.com')->first();
        $this->assertCount(2, $admin->managedDistricts);

        // Edit and save again with same selection
        Livewire::actingAs($this->superAdmin)
            ->test(AdminUsers::class)
            ->call('editUser', $admin->id)
            ->set('adminPassword', 'superadmin123')
            ->call('saveUser')
            ->assertHasNoErrors();

        $admin->refresh();
        $this->assertCount(2, $admin->managedDistricts);
        $this->assertCount(1, $admin->managedCities);
    }

    /**
     * AW2-8: Legacy Orphan admin_city
     * Prepare admin_city = Sleman, admin_district = [].
     * Open Admin edit screen -> no Sleman districts auto-selected.
     * Save without selecting districts -> zero authority remains.
     */
    public function test_aw2_8_legacy_orphan_admin_city_does_not_select_districts(): void
    {
        $admin = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'nik'         => '3400000000000005',
            'city_id'     => $this->citySleman->id,
            'district_id' => $this->distNgaglik->id,
        ]);
        // Stale/legacy state: admin_city contains Sleman, but admin_district is EMPTY
        $admin->managedCities()->sync([$this->citySleman->id]);
        $admin->managedDistricts()->sync([]);

        $component = Livewire::actingAs($this->superAdmin)
            ->test(AdminUsers::class)
            ->call('editUser', $admin->id);

        // Checkbox state MUST NOT auto-select Sleman districts
        $selectedInForm = $component->get('managed_district_ids');
        $this->assertEquals([], $selectedInForm);

        // Save without selecting any districts
        $component->set('adminPassword', 'superadmin123')
            ->call('saveUser')
            ->assertHasNoErrors();

        $admin->refresh();
        $this->assertEquals([], $admin->getAdminDistrictIds());
        $this->assertEquals([], $admin->getEffectiveAdminDistrictIds());
        $this->assertEquals([], $admin->getAdminCityIds());
        $this->assertEquals([], $admin->getEffectiveAdminCityIds());
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
    }

    /**
     * Conflict Check: Generic User Management (/superadmin/users)
     * Updating an admin from generic User Management does NOT grant city-wide authority
     * or conflict with exact district assignments.
     */
    public function test_generic_user_management_does_not_conflict_with_exact_district_authority(): void
    {
        $admin = User::factory()->create([
            'role'   => 'admin',
            'status' => 'active',
            'nik'    => '3400000000000006',
        ]);
        $admin->managedDistricts()->sync([$this->distNgaglik->id]);
        $admin->managedCities()->sync([$this->citySleman->id]);

        // SuperAdmin edits this user via generic User Management
        Livewire::actingAs($this->superAdmin)
            ->test(UsersIndex::class)
            ->call('editUser', $admin->id)
            ->set('name', 'Admin Updated From Generic Users')
            ->call('saveUser')
            ->assertHasNoErrors();

        $admin->refresh();
        $this->assertEquals('Admin Updated From Generic Users', $admin->name);

        // Exact district authority remains intact and uncorrupted!
        $this->assertEquals([(int) $this->distNgaglik->id], $admin->getAdminDistrictIds());
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distDepok->id, $this->citySleman->id));
    }
}
