<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\Province;
use App\Models\User;
use App\Services\Territory\AdminTerritoryAuthorizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExactDistrictAuthorityAW1Test extends TestCase
{
    use RefreshDatabase;

    protected AdminTerritoryAuthorizationService $authService;
    protected Province $provinceDIY;

    // City Sleman (City B)
    protected City $citySleman;
    protected District $distNgaglik;
    protected District $distDepok;
    protected District $distMlati;

    // City Yogyakarta (City A)
    protected City $cityYogya;
    protected District $distDanurejan;
    protected District $distGondomanan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = app(AdminTerritoryAuthorizationService::class);

        $this->provinceDIY = Province::create([
            'name' => 'DI Yogyakarta',
            'code' => '34',
            'is_active' => true,
        ]);

        // Sleman
        $this->citySleman = City::create([
            'name' => 'Kabupaten Sleman',
            'province_id' => $this->provinceDIY->id,
            'province' => 'DI Yogyakarta',
            'is_active' => true,
        ]);
        $this->distNgaglik = District::create(['city_id' => $this->citySleman->id, 'name' => 'Ngaglik', 'is_active' => true]);
        $this->distDepok = District::create(['city_id' => $this->citySleman->id, 'name' => 'Depok', 'is_active' => true]);
        $this->distMlati = District::create(['city_id' => $this->citySleman->id, 'name' => 'Mlati', 'is_active' => true]);

        // Yogya
        $this->cityYogya = City::create([
            'name' => 'Kota Yogyakarta',
            'province_id' => $this->provinceDIY->id,
            'province' => 'DI Yogyakarta',
            'is_active' => true,
        ]);
        $this->distDanurejan = District::create(['city_id' => $this->cityYogya->id, 'name' => 'Danurejan', 'is_active' => true]);
        $this->distGondomanan = District::create(['city_id' => $this->cityYogya->id, 'name' => 'Gondomanan', 'is_active' => true]);
    }

    /**
     * MATRIX 1: 1 district assigned -> only that district authorized.
     */
    public function test_aw1_1_single_district_assigned_only_that_district_authorized(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedDistricts()->sync([$this->distNgaglik->id]);

        $effectiveIds = $admin->getEffectiveAdminDistrictIds();
        $this->assertEquals([(int) $this->distNgaglik->id], $effectiveIds);

        // Allowed: Ngaglik
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
        $this->assertTrue($admin->hasAccessToDistrict($this->distNgaglik->id));

        // Denied: Sibling Depok
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distDepok->id, $this->citySleman->id));
        $this->assertFalse($admin->hasAccessToDistrict($this->distDepok->id));

        // Denied: Other city Danurejan
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distDanurejan->id, $this->cityYogya->id));
    }

    /**
     * MATRIX 2: 2 districts same city -> exactly 2 districts authorized.
     */
    public function test_aw1_2_two_districts_same_city_exactly_two_districts_authorized(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedDistricts()->sync([$this->distNgaglik->id, $this->distDepok->id]);

        $effectiveIds = $admin->getEffectiveAdminDistrictIds();
        sort($effectiveIds);
        $expected = [(int) $this->distNgaglik->id, (int) $this->distDepok->id];
        sort($expected);
        $this->assertEquals($expected, $effectiveIds);

        // Allowed: Ngaglik & Depok
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distDepok->id, $this->citySleman->id));

        // Denied: Sibling Mlati
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distMlati->id, $this->citySleman->id));
        $this->assertFalse($admin->hasAccessToDistrict($this->distMlati->id));
    }

    /**
     * MATRIX 3: district in City A + district in City B -> exactly those districts authorized.
     */
    public function test_aw1_3_districts_across_multiple_cities_authorized(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedDistricts()->sync([$this->distNgaglik->id, $this->distGondomanan->id]);

        $effectiveIds = $admin->getEffectiveAdminDistrictIds();
        sort($effectiveIds);
        $expected = [(int) $this->distNgaglik->id, (int) $this->distGondomanan->id];
        sort($expected);
        $this->assertEquals($expected, $effectiveIds);

        // Allowed: Ngaglik (Sleman) and Gondomanan (Yogya)
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distGondomanan->id, $this->cityYogya->id));

        // Denied: Sibling Depok in Sleman
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distDepok->id, $this->citySleman->id));

        // Denied: Sibling Danurejan in Yogya
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distDanurejan->id, $this->cityYogya->id));
    }

    /**
     * MATRIX 4: Sibling district same city -> denied.
     */
    public function test_aw1_4_sibling_district_same_city_denied(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedDistricts()->sync([$this->distNgaglik->id]);

        // Sibling districts in same city MUST NOT be authorized
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distDepok->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distMlati->id, $this->citySleman->id));
    }

    /**
     * MATRIX 5: 0 district -> no district authority.
     */
    public function test_aw1_5_zero_district_has_no_district_authority(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->assertEquals([], $admin->getAdminDistrictIds());
        $this->assertEquals([], $admin->getEffectiveAdminDistrictIds());
        $this->assertEquals([], $admin->getAdminCityIds());
        $this->assertEquals([], $admin->getEffectiveAdminCityIds());

        // Labels fail closed
        $this->assertEquals('Belum Ada Wilayah', $admin->admin_district_names);
        $this->assertEquals('Belum Ada Wilayah', $admin->active_admin_district_label);
        $this->assertEquals('Belum Ada Wilayah', $admin->admin_city_names);
        $this->assertEquals('Belum Ada Wilayah', $admin->active_admin_city_label);

        // Security guards deny everything
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->citySleman->id));
        $this->assertFalse($admin->hasAccessToDistrict($this->distNgaglik->id));
    }

    /**
     * MATRIX 6: profile district only -> does not grant Admin authority.
     */
    public function test_aw1_6_profile_district_or_city_only_does_not_grant_admin_authority(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'city_id' => $this->citySleman->id,
            'district_id' => $this->distNgaglik->id,
        ]);

        // Explicit authority is empty
        $this->assertEquals([], $admin->getAdminDistrictIds());
        $this->assertEquals([], $admin->getEffectiveAdminDistrictIds());
        $this->assertEquals([], $admin->getAdminCityIds());
        $this->assertEquals([], $admin->getEffectiveAdminCityIds());

        // Access denied
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->citySleman->id));
        $this->assertFalse($admin->hasAccessToDistrict($this->distNgaglik->id));
    }

    /**
     * MATRIX 7: managed city does not expand to sibling districts.
     */
    public function test_aw1_7_managed_city_does_not_expand_to_sibling_districts(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedCities()->sync([$this->citySleman->id]);
        $admin->managedDistricts()->sync([$this->distNgaglik->id]);

        // Ngaglik is explicitly assigned -> ALLOWED
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));

        // Sibling districts Depok and Mlati MUST NOT be authorized despite managedCities Sleman
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distDepok->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distMlati->id, $this->citySleman->id));

        // And if an admin ONLY has managedCities without managedDistricts:
        $cityOnlyAdmin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $cityOnlyAdmin->managedCities()->sync([$this->citySleman->id]);

        $this->assertEquals([], $cityOnlyAdmin->getAdminDistrictIds());
        $this->assertEquals([], $cityOnlyAdmin->getEffectiveAdminDistrictIds());
        $this->assertFalse($this->authService->canAccessTerritory($cityOnlyAdmin, $this->distNgaglik->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($cityOnlyAdmin, $this->distDepok->id, $this->citySleman->id));
    }

    /**
     * MATRIX 8: Preserve legitimate city-level resource compatibility.
     * Authority over city-level resources is derived strictly from the parent city of ACTUALLY ASSIGNED districts.
     */
    public function test_aw1_8_legitimate_city_level_resource_derived_from_assigned_districts(): void
    {
        // Admin assigned Ngaglik (in Sleman)
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedDistricts()->sync([$this->distNgaglik->id]);
        $admin->managedCities()->sync([$this->citySleman->id]);

        // Legitimate city-level resource in Sleman (district_id is null, city_id is Sleman) -> ALLOWED (derived parent city)
        $this->assertTrue($this->authService->canAccessTerritory($admin, null, $this->citySleman->id));

        // City-level resource in Yogya (admin has no districts in Yogya) -> DENIED
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->cityYogya->id));

        // District authority in Sleman is restricted to Ngaglik only (sibling Depok is denied)
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distDepok->id, $this->citySleman->id));
    }

    /**
     * MATRIX 9: Stale session district protection.
     */
    public function test_aw1_9_stale_session_district_does_not_grant_unauthorized_access(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedDistricts()->sync([$this->distNgaglik->id]);

        // Admin has active session for Ngaglik
        $admin->setActiveAdminDistrictFilter((string) $this->distNgaglik->id);
        $this->assertEquals([(int) $this->distNgaglik->id], $admin->getEffectiveAdminDistrictIds());

        // SuperAdmin revokes Ngaglik (admin now has 0 districts)
        $admin->managedDistricts()->sync([]);
        $admin->flushInstanceCache();

        // Next request revalidates against current assignments -> resets to empty, stale session is rejected!
        $this->assertEquals([], $admin->getEffectiveAdminDistrictIds());
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
    }

    // =========================================================================
    // AW1-V FOCUSED TEST MATRIX: ADMIN CITY MUST NOT BECOME INDEPENDENT AUTHORITY
    // =========================================================================

    /**
     * AW1-V Case A: Orphan admin_city row (admin_city = Sleman, admin_district = [])
     * Expected: ZERO authority.
     */
    public function test_aw1_v_case_a_orphan_city_row_grants_zero_authority(): void
    {
        $admin = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => $this->citySleman->id,
            'district_id' => $this->distNgaglik->id,
        ]);
        // admin_city contains Sleman, but admin_district is EMPTY
        $admin->managedCities()->sync([$this->citySleman->id]);
        $admin->managedDistricts()->sync([]);
        $admin->flushInstanceCache();

        // Authority queries must all be empty
        $this->assertEquals([], $admin->getAdminDistrictIds());
        $this->assertEquals([], $admin->getEffectiveAdminDistrictIds());
        $this->assertEquals([], $admin->getAdminCityIds());
        $this->assertEquals([], $admin->getEffectiveAdminCityIds());

        // District access denied
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distDepok->id, $this->citySleman->id));

        // City-level resource (district_id is null) also denied (orphan city cannot authorize)
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->citySleman->id));

        // Labels fail-closed
        $this->assertEquals('Belum Ada Wilayah', $admin->admin_district_names);
        $this->assertEquals('Belum Ada Wilayah', $admin->active_admin_district_label);
        $this->assertEquals('Belum Ada Wilayah', $admin->admin_city_names);
        $this->assertEquals('Belum Ada Wilayah', $admin->active_admin_city_label);
    }

    /**
     * AW1-V Case B: One district (admin_district = Ngaglik, admin_city = Sleman)
     * Expected: Ngaglik authorized, Depok denied, Mlati denied, city-level Sleman authorized.
     */
    public function test_aw1_v_case_b_one_district_authorized_siblings_denied(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedDistricts()->sync([$this->distNgaglik->id]);
        $admin->managedCities()->sync([$this->citySleman->id]);
        $admin->flushInstanceCache();

        $this->assertEquals([(int) $this->distNgaglik->id], $admin->getEffectiveAdminDistrictIds());
        $this->assertEquals([(int) $this->citySleman->id], $admin->getEffectiveAdminCityIds());

        // Ngaglik authorized
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));

        // Sibling districts denied
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distDepok->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distMlati->id, $this->citySleman->id));

        // City-level Sleman resource authorized (derived parent city)
        $this->assertTrue($this->authService->canAccessTerritory($admin, null, $this->citySleman->id));

        // City-level Yogya resource denied
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->cityYogya->id));
    }

    /**
     * AW1-V Case C: Cross-city districts (admin_district = Ngaglik in Sleman, Gondomanan in Yogya)
     * Expected: exactly those districts, both parent cities derived for city-level resources.
     */
    public function test_aw1_v_case_c_cross_city_districts_exact_authority(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->managedDistricts()->sync([$this->distNgaglik->id, $this->distGondomanan->id]);
        $admin->managedCities()->sync([$this->citySleman->id, $this->cityYogya->id]);
        $admin->flushInstanceCache();

        $effectiveDistrictIds = $admin->getEffectiveAdminDistrictIds();
        sort($effectiveDistrictIds);
        $expectedDistrictIds = [(int) $this->distNgaglik->id, (int) $this->distGondomanan->id];
        sort($expectedDistrictIds);
        $this->assertEquals($expectedDistrictIds, $effectiveDistrictIds);

        $effectiveCityIds = $admin->getEffectiveAdminCityIds();
        sort($effectiveCityIds);
        $expectedCityIds = [(int) $this->citySleman->id, (int) $this->cityYogya->id];
        sort($expectedCityIds);
        $this->assertEquals($expectedCityIds, $effectiveCityIds);

        // Assigned districts authorized
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->distGondomanan->id, $this->cityYogya->id));

        // Unassigned siblings denied
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distDepok->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distDanurejan->id, $this->cityYogya->id));

        // Truly city-level resources in both parent cities authorized
        $this->assertTrue($this->authService->canAccessTerritory($admin, null, $this->citySleman->id));
        $this->assertTrue($this->authService->canAccessTerritory($admin, null, $this->cityYogya->id));
    }

    /**
     * AW1-V Case D: No pivots but profile domicile exists (users.city_id = Sleman, users.district_id = Ngaglik)
     * Expected: ZERO authority.
     */
    public function test_aw1_v_case_d_profile_domicile_without_pivots_grants_zero_authority(): void
    {
        $admin = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => $this->citySleman->id,
            'district_id' => $this->distNgaglik->id,
        ]);
        $admin->managedCities()->sync([]);
        $admin->managedDistricts()->sync([]);
        $admin->flushInstanceCache();

        $this->assertEquals([], $admin->getAdminDistrictIds());
        $this->assertEquals([], $admin->getEffectiveAdminDistrictIds());
        $this->assertEquals([], $admin->getAdminCityIds());
        $this->assertEquals([], $admin->getEffectiveAdminCityIds());

        // All access denied
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->distNgaglik->id, $this->citySleman->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->citySleman->id));
    }
}
