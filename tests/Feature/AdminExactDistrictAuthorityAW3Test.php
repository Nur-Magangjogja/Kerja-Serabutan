<?php

namespace Tests\Feature;

use App\Livewire\Admin\TerritorySwitcher;
use App\Models\City;
use App\Models\District;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminExactDistrictAuthorityAW3Test extends TestCase
{
    use RefreshDatabase;

    protected City $citySleman;
    protected City $cityYogya;
    protected City $cityBantul;

    protected District $distNgaglik;
    protected District $distDepok;
    protected District $distMlati;
    protected District $distGondomanan;
    protected District $distDanurejan;
    protected District $distKasihan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->citySleman = City::create([
            'name'     => 'Kabupaten Sleman',
            'province' => 'D.I. Yogyakarta',
        ]);

        $this->cityYogya = City::create([
            'name'     => 'Kota Yogyakarta',
            'province' => 'D.I. Yogyakarta',
        ]);

        $this->cityBantul = City::create([
            'name'     => 'Kabupaten Bantul',
            'province' => 'D.I. Yogyakarta',
        ]);

        $this->distNgaglik = District::create([
            'city_id'   => $this->citySleman->id,
            'name'      => 'Ngaglik',
            'is_active' => true,
        ]);

        $this->distDepok = District::create([
            'city_id'   => $this->citySleman->id,
            'name'      => 'Depok',
            'is_active' => true,
        ]);

        $this->distMlati = District::create([
            'city_id'   => $this->citySleman->id,
            'name'      => 'Mlati',
            'is_active' => true,
        ]);

        $this->distGondomanan = District::create([
            'city_id'   => $this->cityYogya->id,
            'name'      => 'Gondomanan',
            'is_active' => true,
        ]);

        $this->distDanurejan = District::create([
            'city_id'   => $this->cityYogya->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);

        $this->distKasihan = District::create([
            'city_id'   => $this->cityBantul->id,
            'name'      => 'Kasihan',
            'is_active' => true,
        ]);
    }

    protected function createAdmin(array $districtIds = []): User
    {
        $admin = User::factory()->create([
            'name'     => 'Test Admin Wilayah',
            'email'    => 'admin.' . uniqid() . '@sayabantu.test',
            'role'     => 'admin',
            'status'   => 'active',
            'verified' => true,
            'password' => Hash::make('password123'),
        ]);

        if (!empty($districtIds)) {
            $admin->managedDistricts()->sync($districtIds);
        }

        return $admin;
    }

    /**
     * AW3-1: Single District
     * Assigned: Ngaglik
     * Expected:
     * - available options: Ngaglik only (no sibling district)
     * - If UI hides switcher: assert active label is Ngaglik
     * - Calling selectDistrict with unassigned district is rejected
     */
    public function test_aw3_1_single_district(): void
    {
        $admin = $this->createAdmin([$this->distNgaglik->id]);

        // UI hides switcher when only 1 district is assigned
        $component = Livewire::actingAs($admin)->test(TerritorySwitcher::class);
        $component->assertDontSee('Wilayah Kecamatan Pantauan')
            ->assertDontSee('selectDistrict');
        $this->assertMatchesRegularExpression('/^<div[^>]*><\/div>$/', trim($component->html()));

        // Active label correctly shows exact district name
        $this->assertStringContainsString('Ngaglik', $admin->active_admin_district_label);
        $this->assertStringNotContainsString('Semua Wilayah', $admin->active_admin_district_label);
        $this->assertStringNotContainsString('Kabupaten Sleman', $admin->active_admin_district_label);

        // Managed districts collection has only Ngaglik
        $managedNames = $admin->getAdminDistricts()->pluck('name')->all();
        $this->assertSame(['Ngaglik'], $managedNames);

        // Unassigned siblings are absent
        $this->assertNotContains('Depok', $managedNames);
        $this->assertNotContains('Mlati', $managedNames);

        // Attempting to select unassigned district fails and falls back to 'all'
        Livewire::actingAs($admin)
            ->test(TerritorySwitcher::class)
            ->call('selectDistrict', (string) $this->distDepok->id);

        $this->assertSame('all', $admin->fresh()->getActiveAdminDistrictFilter());
        $this->assertSame([$this->distNgaglik->id], $admin->fresh()->getEffectiveAdminDistrictIds());
    }

    /**
     * AW3-2: Same-City Multiple Districts
     * Assigned: Ngaglik, Depok
     * Expected:
     * - Group header: Kabupaten Sleman
     * - Options: Ngaglik, Depok
     * - Mlati absent
     * - Group header is visual non-clickable context
     * - Exact district_id used as option values
     */
    public function test_aw3_2_same_city_multiple_districts(): void
    {
        $admin = $this->createAdmin([$this->distNgaglik->id, $this->distDepok->id]);

        $test = Livewire::actingAs($admin)
            ->test(TerritorySwitcher::class)
            // Visual Group Header
            ->assertSee('Kabupaten Sleman')
            // Exact assigned districts
            ->assertSee('Kec. Ngaglik')
            ->assertSee('Kec. Depok')
            // Unassigned sibling is ABSENT
            ->assertDontSee('Kec. Mlati')
            // Exact district_id wire:click bindings
            ->assertSeeHtml("selectDistrict('{$this->distNgaglik->id}')")
            ->assertSeeHtml("selectDistrict('{$this->distDepok->id}')")
            ->assertDontSeeHtml("selectDistrict('{$this->distMlati->id}')");

        // Assert visual non-clickable class on header container
        $html = $test->html();
        $this->assertStringContainsString('pointer-events-none', $html);
        $this->assertStringContainsString('select-none', $html);

        // Check view data grouping
        $viewData = $test->viewData('groupedDistricts');
        $this->assertNotNull($viewData);
        $this->assertTrue($viewData->has('Kabupaten Sleman'));
        $slemanDistricts = $viewData->get('Kabupaten Sleman')->pluck('name')->all();
        $this->assertContains('Ngaglik', $slemanDistricts);
        $this->assertContains('Depok', $slemanDistricts);
        $this->assertNotContains('Mlati', $slemanDistricts);
    }

    /**
     * AW3-3: Cross-City Assignment
     * Assigned: Ngaglik (Kabupaten Sleman), Gondomanan (Kota Yogyakarta)
     * Expected:
     * - Group 1: Kabupaten Sleman -> Ngaglik only (Depok, Mlati absent)
     * - Group 2: Kota Yogyakarta -> Gondomanan only (Danurejan absent)
     */
    public function test_aw3_3_cross_city_assignment(): void
    {
        $admin = $this->createAdmin([$this->distNgaglik->id, $this->distGondomanan->id]);

        $test = Livewire::actingAs($admin)
            ->test(TerritorySwitcher::class)
            // Both city headers visible
            ->assertSee('Kabupaten Sleman')
            ->assertSee('Kota Yogyakarta')
            // Both exact assigned districts visible
            ->assertSee('Kec. Ngaglik')
            ->assertSee('Kec. Gondomanan')
            // Unassigned siblings in both cities ABSENT
            ->assertDontSee('Kec. Depok')
            ->assertDontSee('Kec. Mlati')
            ->assertDontSee('Kec. Danurejan');

        $viewData = $test->viewData('groupedDistricts');
        $this->assertCount(2, $viewData);

        $slemanGroup = $viewData->get('Kabupaten Sleman')->pluck('name')->all();
        $this->assertSame(['Ngaglik'], $slemanGroup);

        $yogyaGroup = $viewData->get('Kota Yogyakarta')->pluck('name')->all();
        $this->assertSame(['Gondomanan'], $yogyaGroup);
    }

    /**
     * AW3-4: Zero Territory
     * Assigned: []
     * Expected:
     * - Switcher UI is inactive / hidden
     * - Label is "Belum Ada Wilayah"
     * - No switchable district options
     * - No global / all-Indonesia authority interpretation
     */
    public function test_aw3_4_zero_territory(): void
    {
        $admin = $this->createAdmin([]);

        $component = Livewire::actingAs($admin)->test(TerritorySwitcher::class);
        $component->assertDontSee('Wilayah Kecamatan Pantauan')
            ->assertDontSee('selectDistrict');
        $this->assertMatchesRegularExpression('/^<div[^>]*><\/div>$/', trim($component->html()));

        $this->assertSame('Belum Ada Wilayah', $admin->active_admin_district_label);
        $this->assertSame('Belum Ada Wilayah', $admin->admin_district_names);
        $this->assertSame([], $admin->getEffectiveAdminDistrictIds());
        $this->assertSame([], $admin->getEffectiveAdminCityIds());

        // Even if selectDistrict is invoked, it stays safe with 0 authority
        Livewire::actingAs($admin)
            ->test(TerritorySwitcher::class)
            ->call('selectDistrict', 'all');

        $this->assertSame([], $admin->fresh()->getEffectiveAdminDistrictIds());
        $this->assertSame('Belum Ada Wilayah', $admin->fresh()->active_admin_district_label);
    }

    /**
     * AW3-5: Stale Session Invalidation
     * Initial: Ngaglik, Depok assigned. Session selected: Depok.
     * Revocation: SuperAdmin removes Depok from admin.
     * Expected:
     * - Depok session invalidated immediately
     * - Depok no longer selectable
     * - Depok no longer effective
     * - Authority safely resets to remaining assigned districts
     */
    public function test_aw3_5_stale_session_invalidation(): void
    {
        $admin = $this->createAdmin([$this->distNgaglik->id, $this->distDepok->id]);

        // Select Depok initially
        $admin->setActiveAdminDistrictFilter((string) $this->distDepok->id);
        $this->assertSame((string) $this->distDepok->id, $admin->getActiveAdminDistrictFilter());
        $this->assertSame([$this->distDepok->id], $admin->getEffectiveAdminDistrictIds());

        // SuperAdmin revokes Depok (admin now has only Ngaglik)
        $admin->managedDistricts()->sync([$this->distNgaglik->id]);
        $admin->flushInstanceCache();

        // Testing getActiveAdminDistrictFilter cleans stale session
        $this->assertSame('all', $admin->getActiveAdminDistrictFilter());
        $this->assertSame('all', session('admin_active_district_filter'));

        // Effective district authority has Ngaglik only, NEVER Depok
        $this->assertSame([$this->distNgaglik->id], $admin->getEffectiveAdminDistrictIds());

        // Switcher now hides (count is 1) and active label is Ngaglik
        $component = Livewire::actingAs($admin)->test(TerritorySwitcher::class);
        $component->assertDontSee('Wilayah Kecamatan Pantauan');
        $this->assertMatchesRegularExpression('/^<div[^>]*><\/div>$/', trim($component->html()));

        $this->assertStringContainsString('Ngaglik', $admin->active_admin_district_label);
        $this->assertStringNotContainsString('Depok', $admin->active_admin_district_label);

        // Attempting to select revoked Depok is rejected
        Livewire::actingAs($admin)
            ->test(TerritorySwitcher::class)
            ->call('selectDistrict', (string) $this->distDepok->id);

        $this->assertSame('all', $admin->fresh()->getActiveAdminDistrictFilter());
        $this->assertSame([$this->distNgaglik->id], $admin->fresh()->getEffectiveAdminDistrictIds());
    }

    /**
     * AW3-6: Aggregate Assigned Territories
     * Assigned: Ngaglik, Depok
     * Aggregate option "Semua Wilayah Tugas / Wewenang" scopes ONLY to assigned districts.
     * Never city-wide (Mlati excluded) and never other cities (Kasihan excluded).
     */
    public function test_aw3_6_aggregate_assigned_territories(): void
    {
        $admin = $this->createAdmin([$this->distNgaglik->id, $this->distDepok->id]);

        Livewire::actingAs($admin)
            ->test(TerritorySwitcher::class)
            ->assertSee('Semua Wilayah Wewenang')
            ->call('selectDistrict', 'all');

        $this->assertSame('all', $admin->fresh()->getActiveAdminDistrictFilter());

        $effectiveIds = $admin->fresh()->getEffectiveAdminDistrictIds();
        sort($effectiveIds);
        $expectedIds = [$this->distNgaglik->id, $this->distDepok->id];
        sort($expectedIds);

        $this->assertSame($expectedIds, $effectiveIds);
        $this->assertNotContains($this->distMlati->id, $effectiveIds);
        $this->assertNotContains($this->distKasihan->id, $effectiveIds);
    }

    /**
     * AW3-7: Search Filters Only Within Assigned Districts
     * Search must only match assigned districts or their parent cities.
     * Unassigned siblings are never rendered or disclosed in search keywords.
     */
    public function test_aw3_7_search_filters_only_assigned_districts(): void
    {
        $admin = $this->createAdmin([
            $this->distNgaglik->id,
            $this->distDepok->id,
            $this->distGondomanan->id,
        ]);

        $test = Livewire::actingAs($admin)
            ->test(TerritorySwitcher::class)
            // Search input is rendered
            ->assertSeeHtml('x-model="search"')
            // Assigned districts and cities are present
            ->assertSee('Kabupaten Sleman')
            ->assertSee('Kota Yogyakarta')
            ->assertSee('Kec. Ngaglik')
            ->assertSee('Kec. Depok')
            ->assertSee('Kec. Gondomanan')
            // Unassigned siblings are NEVER present anywhere in HTML or search data
            ->assertDontSee('Mlati')
            ->assertDontSee('Danurejan')
            ->assertDontSee('Kasihan');

        $html = $test->html();
        $this->assertStringNotContainsString('mlati', strtolower($html));
        $this->assertStringNotContainsString('danurejan', strtolower($html));
        $this->assertStringNotContainsString('kasihan', strtolower($html));
    }
}
