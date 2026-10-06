<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\TerritorySwitcher as AdminTerritorySwitcher;
use App\Livewire\SuperAdmin\TerritorySwitcher as SuperAdminTerritorySwitcher;
use App\Models\City;
use App\Models\District;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTerritorySwitcherResponsiveTest extends TestCase
{
    use RefreshDatabase;

    protected City $city;
    protected District $district1;
    protected District $district2;
    protected District $district3;
    protected User $admin;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::create([
            'name'     => 'Kota Yogyakarta',
            'province' => 'D.I. Yogyakarta',
        ]);

        $this->district1 = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);

        $this->district2 = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Gondokusuman',
            'is_active' => true,
        ]);

        $this->district3 = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Kraton',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'name'        => 'Admin Wilayah Yogyakarta',
            'email'       => 'admin.yk@sayabantu.test',
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => null,
            'password'    => Hash::make('password123'),
        ]);

        $this->admin->managedDistricts()->sync([
            $this->district1->id,
            $this->district2->id,
            $this->district3->id,
        ]);

        $this->superAdmin = User::factory()->create([
            'name'        => 'Super Administrator',
            'email'       => 'superadmin@sayabantu.test',
            'role'        => 'super_admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->district1->id,
            'password'    => Hash::make('password123'),
        ]);
    }

    /**
     * 1. Test Admin Territory Switcher renders joined dropdown popover with responsive classes.
     */
    public function test_admin_territory_switcher_renders_joined_dropdown_and_overflow_classes(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AdminTerritorySwitcher::class)
            ->assertSee('Wilayah Kecamatan Pantauan')
            ->assertSee('Semua Wilayah Wewenang')
            ->assertSee('Kec. Danurejan')
            ->assertSee('Kec. Gondokusuman')
            ->assertSee('Kec. Kraton')
            // Verify responsive mobile & desktop placement (fixed on mobile, absolute on desktop)
            ->assertSeeHtml('fixed sm:absolute inset-x-3 sm:inset-x-auto top-16 sm:top-full sm:right-0 sm:mt-2')
            // Verify responsive width
            ->assertSeeHtml('w-auto sm:w-[26rem] md:w-[28rem]')
            // Verify 2-column responsive grid on desktop
            ->assertSeeHtml('grid grid-cols-1 sm:grid-cols-2 gap-1.5')
            // Verify scroll container with overflow handling
            ->assertSeeHtml('custom-scrollbar overscroll-contain');
    }

    /**
     * 2. Test selecting a district updates active filter and dispatches events.
     */
    public function test_admin_territory_switcher_select_district_updates_filter_and_dispatches_events(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AdminTerritorySwitcher::class)
            ->call('selectDistrict', (string) $this->district2->id)
            ->assertDispatched('admin-district-changed', districtId: (string) $this->district2->id)
            ->assertDispatched('admin-city-changed', cityId: (string) $this->district2->id)
            ->assertDispatched('chart-refresh');

        $this->assertEquals((string) $this->district2->id, $this->admin->fresh()->getActiveAdminDistrictFilter());
    }

    /**
     * 3. Test resetting district to 'all' dispatches all events.
     */
    public function test_admin_territory_switcher_reset_all_districts(): void
    {
        $this->admin->setActiveAdminDistrictFilter((string) $this->district1->id);

        Livewire::actingAs($this->admin)
            ->test(AdminTerritorySwitcher::class)
            ->call('selectDistrict', 'all')
            ->assertDispatched('admin-district-changed', districtId: 'all')
            ->assertDispatched('admin-city-changed', cityId: 'all')
            ->assertDispatched('chart-refresh');

        $this->assertEquals('all', $this->admin->fresh()->getActiveAdminDistrictFilter());
    }

    /**
     * 4. Test SuperAdmin Territory Switcher renders joined dropdown without emojis.
     */
    public function test_superadmin_territory_switcher_renders_joined_dropdown_and_no_emojis(): void
    {
        $component = Livewire::actingAs($this->superAdmin)
            ->test(SuperAdminTerritorySwitcher::class)
            ->call('openDropdown')
            ->assertSet('isOpen', true);

        $html = $component->html();

        // Must be responsive on mobile and desktop
        $this->assertStringContainsString('fixed sm:absolute inset-x-3 sm:inset-x-auto top-16 sm:top-full sm:right-0 sm:mt-2', $html);
        $this->assertStringContainsString('w-auto sm:w-[26rem] md:w-[28rem]', $html);
        $this->assertStringContainsString('custom-scrollbar overscroll-contain', $html);

        // Must not contain pin emoji or checkmark emoji
        $this->assertStringNotContainsString('📌', $html);
        $this->assertStringNotContainsString('✓', $html);
    }
}
