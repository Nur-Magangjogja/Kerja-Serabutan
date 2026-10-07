<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\User;
use App\Services\Territory\AdminTerritoryAuthorizationService;
use Database\Seeders\AdminCitySeeder;
use Database\Seeders\CitySeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminLegacyTerritoryAlignmentAW6Test extends TestCase
{
    use RefreshDatabase;

    protected AdminTerritoryAuthorizationService $authService;
    protected City $slemanCity;
    protected City $yogyaCity;
    protected District $ngaglikDist;
    protected District $depokDist;
    protected District $mlatiDist;
    protected District $gondomananDist;
    protected District $danurejanDist;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = app(AdminTerritoryAuthorizationService::class);

        // Run standard DatabaseSeeder to hydrate realistic seeded state
        $this->seed(DatabaseSeeder::class);

        $this->slemanCity = City::where('code', '3404')->orWhere('name', 'like', '%Sleman%')->firstOrFail();
        $this->yogyaCity = City::where('code', '3471')->orWhere('name', 'like', '%Yogyakarta%')->firstOrFail();

        $this->ngaglikDist = District::where('city_id', $this->slemanCity->id)->where('name', 'like', '%Ngaglik%')->firstOrFail();
        $this->depokDist = District::where('city_id', $this->slemanCity->id)->where('name', 'like', '%Depok%')->firstOrFail();
        $this->mlatiDist = District::where('city_id', $this->slemanCity->id)->where('name', 'like', '%Mlati%')->firstOrFail();

        $this->gondomananDist = District::where('city_id', $this->yogyaCity->id)->where('name', 'like', '%Gondomanan%')->firstOrFail();
        $this->danurejanDist = District::where('city_id', $this->yogyaCity->id)->where('name', 'like', '%Danurejan%')->firstOrFail();
    }

    /**
     * AW6-1: Exact district seed: 1 district -> exactly 1 authority.
     * Sibling districts in same city are denied.
     */
    public function test_aw6_1_exact_single_district_seed(): void
    {
        $admin = User::where('email', 'admin.single@sayabantu.com')->firstOrFail();

        // Authority is strictly 1 district (Ngaglik)
        $this->assertSame([$this->ngaglikDist->id], $admin->getAdminDistrictIds());
        $this->assertSame([$this->ngaglikDist->id], $admin->getEffectiveAdminDistrictIds());

        // Derived city is Sleman
        $this->assertSame([$this->slemanCity->id], $admin->getAdminCityIds());

        // canAccessTerritory allows Ngaglik
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->ngaglikDist->id, $this->slemanCity->id));

        // Sibling districts in Sleman (Depok, Mlati) are strictly DENIED
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->depokDist->id, $this->slemanCity->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->mlatiDist->id, $this->slemanCity->id));

        // Foreign district (Gondomanan) is strictly DENIED
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->gondomananDist->id, $this->yogyaCity->id));
    }

    /**
     * AW6-2: Same-city multi district: 2 districts -> exactly 2 authority.
     * Sibling districts in same city are denied.
     */
    public function test_aw6_2_same_city_multi_district_seed(): void
    {
        $admin = User::where('email', 'admin.sleman@sayabantu.com')->firstOrFail();

        $expectedDistrictIds = [$this->ngaglikDist->id, $this->depokDist->id];
        sort($expectedDistrictIds);

        $actualDistrictIds = $admin->getAdminDistrictIds();
        sort($actualDistrictIds);

        $this->assertSame($expectedDistrictIds, $actualDistrictIds);
        $this->assertSame([$this->slemanCity->id], $admin->getAdminCityIds());

        // Both assigned districts are authorized
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->ngaglikDist->id, $this->slemanCity->id));
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->depokDist->id, $this->slemanCity->id));

        // Unassigned sibling district in Sleman (Mlati) is strictly DENIED
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->mlatiDist->id, $this->slemanCity->id));
    }

    /**
     * AW6-3: Cross-city: 2 districts in 2 cities -> exactly 2 authority.
     * Derived parent cities are both cities, sibling districts in both cities are denied.
     */
    public function test_aw6_3_cross_city_seed(): void
    {
        $admin = User::where('email', 'admin.cross@sayabantu.com')->firstOrFail();

        $expectedDistrictIds = [$this->ngaglikDist->id, $this->gondomananDist->id];
        sort($expectedDistrictIds);

        $actualDistrictIds = $admin->getAdminDistrictIds();
        sort($actualDistrictIds);

        $this->assertSame($expectedDistrictIds, $actualDistrictIds);

        $expectedCityIds = [$this->slemanCity->id, $this->yogyaCity->id];
        sort($expectedCityIds);

        $actualCityIds = $admin->getAdminCityIds();
        sort($actualCityIds);

        $this->assertSame($expectedCityIds, $actualCityIds);

        // Both assigned districts across cities are authorized
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->ngaglikDist->id, $this->slemanCity->id));
        $this->assertTrue($this->authService->canAccessTerritory($admin, $this->gondomananDist->id, $this->yogyaCity->id));

        // Sibling districts in both parent cities are strictly DENIED
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->depokDist->id, $this->slemanCity->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->danurejanDist->id, $this->yogyaCity->id));
    }

    /**
     * AW6-4: Zero territory seed: no districts -> zero authority.
     */
    public function test_aw6_4_zero_territory_seed(): void
    {
        $admin = User::where('email', 'admin.zero@sayabantu.com')->firstOrFail();

        $this->assertSame([], $admin->getAdminDistrictIds());
        $this->assertSame([], $admin->getEffectiveAdminDistrictIds());
        $this->assertSame([], $admin->getAdminCityIds());
        $this->assertSame([], $admin->getEffectiveAdminCityIds());

        $this->assertSame('Belum Ada Wilayah', $admin->active_admin_district_label);
        $this->assertSame('Belum Ada Wilayah', $admin->active_admin_city_label);

        // Any territorial access is DENIED
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->ngaglikDist->id, $this->slemanCity->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->gondomananDist->id, $this->yogyaCity->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->slemanCity->id));
    }

    /**
     * AW6-5: Orphan admin_city: admin_city only -> zero authority.
     */
    public function test_aw6_5_orphan_admin_city(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'city_id' => $this->slemanCity->id,
            'district_id' => $this->ngaglikDist->id,
        ]);

        // Manually create legacy orphan admin_city row without admin_district
        $admin->managedCities()->sync([$this->slemanCity->id]);
        $admin->managedDistricts()->sync([]);
        $admin->flushInstanceCache();

        $this->assertSame([], $admin->getAdminDistrictIds());
        $this->assertSame([], $admin->getEffectiveAdminDistrictIds());
        $this->assertSame([], $admin->getEffectiveAdminCityIds());

        // Zero territorial authority: orphan city does NOT grant district or city access
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->ngaglikDist->id, $this->slemanCity->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->slemanCity->id));
    }

    /**
     * AW6-6: Profile-only admin: profile city/district only -> zero authority.
     */
    public function test_aw6_6_profile_only_admin(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'city_id' => $this->slemanCity->id,
            'district_id' => $this->ngaglikDist->id,
        ]);
        // No pivots attached
        $this->assertSame(0, $admin->managedDistricts()->count());
        $this->assertSame(0, $admin->managedCities()->count());

        $this->assertSame([], $admin->getAdminDistrictIds());
        $this->assertSame([], $admin->getEffectiveAdminDistrictIds());
        $this->assertSame([], $admin->getAdminCityIds());

        // Profile territory NEVER grants Admin authority
        $this->assertFalse($this->authService->canAccessTerritory($admin, $this->ngaglikDist->id, $this->slemanCity->id));
        $this->assertFalse($this->authService->canAccessTerritory($admin, null, $this->slemanCity->id));
    }

    /**
     * AW6-7: Derived city metadata: assigned districts -> exact unique parent cities.
     */
    public function test_aw6_7_derived_city_metadata(): void
    {
        $admin = User::where('email', 'admin.cross@sayabantu.com')->firstOrFail();

        // Exact unique parent cities derived from assigned districts
        $pivotCities = $admin->managedCities()->pluck('cities.id')->sort()->values()->all();
        $expectedCities = [$this->slemanCity->id, $this->yogyaCity->id];
        sort($expectedCities);

        $this->assertSame($expectedCities, $pivotCities);

        // No orphan or extra cities in pivot
        $this->assertCount(2, $pivotCities);
    }

    /**
     * AW6-8: No reverse expansion: admin_city MUST NOT create admin_district rows.
     */
    public function test_aw6_8_no_reverse_expansion(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->managedCities()->sync([$this->slemanCity->id]);
        $admin->managedDistricts()->sync([]);

        // Rerun seeder or migration
        $this->artisan('sayabantu:migrate-districts-data');

        $admin->refresh();
        // admin_district remains empty; no reverse expansion happened
        $this->assertCount(0, $admin->managedDistricts);
        $this->assertSame([], $admin->getAdminDistrictIds());
    }

    /**
     * AW6-9: Seeder idempotency: running seeder multiple times produces identical state.
     */
    public function test_aw6_9_seeder_idempotency(): void
    {
        $initialDistPivots = DB::table('admin_district')->count();
        $initialCityPivots = DB::table('admin_city')->count();
        $initialAdminCount = User::where('role', 'admin')->count();

        // Re-run AdminCitySeeder a second time
        $this->seed(AdminCitySeeder::class);

        $this->assertSame($initialAdminCount, User::where('role', 'admin')->count());
        $this->assertSame($initialDistPivots, DB::table('admin_district')->count());
        $this->assertSame($initialCityPivots, DB::table('admin_city')->count());

        // Re-run AdminCitySeeder a third time
        $this->seed(AdminCitySeeder::class);

        $this->assertSame($initialAdminCount, User::where('role', 'admin')->count());
        $this->assertSame($initialDistPivots, DB::table('admin_district')->count());
        $this->assertSame($initialCityPivots, DB::table('admin_city')->count());
    }

    /**
     * AW6-10: Migration command: sayabantu:migrate-districts-data does not expand city/profile into all districts.
     */
    public function test_aw6_10_migration_command_does_not_expand_city_or_profile_to_all_districts(): void
    {
        // 1. Admin with only profile city_id
        $profileAdmin = User::factory()->create([
            'role' => 'admin',
            'city_id' => $this->slemanCity->id,
            'district_id' => null,
        ]);

        // 2. Admin with orphan admin_city
        $orphanAdmin = User::factory()->create([
            'role' => 'admin',
        ]);
        $orphanAdmin->managedCities()->sync([$this->slemanCity->id]);

        // 3. Admin with explicit district (Ngaglik only)
        $explicitAdmin = User::factory()->create([
            'role' => 'admin',
        ]);
        $explicitAdmin->managedDistricts()->sync([$this->ngaglikDist->id]);

        // Run the command
        $this->artisan('sayabantu:migrate-districts-data')
            ->assertExitCode(0);

        // Verify profileAdmin is NOT given any managedDistricts
        $profileAdmin->refresh();
        $this->assertCount(0, $profileAdmin->managedDistricts);

        // Verify orphanAdmin is NOT expanded to all Sleman districts
        $orphanAdmin->refresh();
        $this->assertCount(0, $orphanAdmin->managedDistricts);

        // Verify explicitAdmin keeps exactly Ngaglik, and its derived parent city is synced
        $explicitAdmin->refresh();
        $this->assertSame([$this->ngaglikDist->id], $explicitAdmin->getAdminDistrictIds());
        $this->assertSame([$this->slemanCity->id], $explicitAdmin->getAdminCityIds());
    }
}
