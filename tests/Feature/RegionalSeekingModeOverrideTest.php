<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\AppSetting;
use App\Services\HelpMatchingService;
use App\Livewire\SuperAdmin\Settings\HelpSettings;
use Livewire\Livewire;

class RegionalSeekingModeOverrideTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $customer;
    protected User $partner;
    protected City $cityA;
    protected City $cityB;
    protected City $cityC;
    protected District $districtA;
    protected District $districtB;
    protected District $districtC;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $this->partner = User::factory()->create([
            'role' => 'mitra',
            'verified' => true,
        ]);

        $this->cityA = City::create([
            'name' => 'Kota Yogyakarta',
            'province' => 'DI Yogyakarta',
            'is_active' => true,
            'is_matching_seeking_enabled' => null, // Inherit Global
        ]);

        $this->districtA = District::create([
            'name' => 'Danurejan',
            'city_id' => $this->cityA->id,
            'is_active' => true,
        ]);

        $this->cityB = City::create([
            'name' => 'Kabupaten Gunungkidul',
            'province' => 'DI Yogyakarta',
            'is_active' => true,
            'is_matching_seeking_enabled' => false, // Override: Langsung Pool
        ]);

        $this->districtB = District::create([
            'name' => 'Wonosari',
            'city_id' => $this->cityB->id,
            'is_active' => true,
        ]);

        $this->cityC = City::create([
            'name' => 'Kabupaten Sleman',
            'province' => 'DI Yogyakarta',
            'is_active' => true,
            'is_matching_seeking_enabled' => true, // Override: Paksa Antrean
        ]);

        $this->districtC = District::create([
            'name' => 'Depok',
            'city_id' => $this->cityC->id,
            'is_active' => true,
        ]);
    }

    public function test_hierarchical_fallback_resolves_correctly(): void
    {
        // Case 1: Global ON
        AppSetting::set('matching_seeking_enabled', '1');

        $this->assertTrue(AppSetting::isMatchingSeekingEnabled($this->cityA->id), 'City A (null) must inherit Global ON');
        $this->assertFalse(AppSetting::isMatchingSeekingEnabled($this->cityB->id), 'City B (false) must override to Disabled');
        $this->assertTrue(AppSetting::isMatchingSeekingEnabled($this->cityC->id), 'City C (true) must remain Enabled');

        // Case 2: Global OFF
        AppSetting::set('matching_seeking_enabled', '0');

        $this->assertFalse(AppSetting::isMatchingSeekingEnabled($this->cityA->id), 'City A (null) must inherit Global OFF');
        $this->assertFalse(AppSetting::isMatchingSeekingEnabled($this->cityB->id), 'City B (false) must remain Disabled');
        $this->assertTrue(AppSetting::isMatchingSeekingEnabled($this->cityC->id), 'City C (true) must override to Enabled');
    }

    public function test_help_matching_service_respects_regional_override(): void
    {
        // Set Global ON
        AppSetting::set('matching_seeking_enabled', '1');

        $matchingService = app(HelpMatchingService::class);

        // Buat Order di City B (Override Disabled -> harus langsung fallback ke Open Pool)
        $helpB = Help::create([
            'user_id'             => $this->customer->id,
            'city_id'             => $this->cityB->id,
            'district_id'         => $this->districtB->id,
            'title'               => 'Bantuan di Gunungkidul',
            'description'         => 'Order di wilayah volume rendah',
            'service_type'        => Help::SERVICE_TYPE_ON_SITE,
            'order_mode'          => Help::ORDER_MODE_INSTANT,
            'status'              => Help::STATUS_MENUNGGU_MITRA,
            'latitude'            => -7.9650,
            'longitude'           => 110.6030,
            'amount'              => 50000,
            'service_fee'         => 50000,
            'platform_fee_amount' => 2000,
            'total_amount'        => 52000,
            'published_at'        => now(),
        ]);

        $resultB = $matchingService->initiateMatching($helpB);
        $this->assertFalse($resultB, 'Order in City B with disabled seeking mode should bypass matching and return false');
        $helpB->refresh();
        $this->assertEquals(Help::DISPATCH_MODE_POOL, $helpB->dispatch_mode);
    }

    public function test_superadmin_can_update_city_overrides_via_livewire(): void
    {
        $this->actingAs($this->superadmin);

        Livewire::test(HelpSettings::class)
            ->assertSee('Pengaturan Kebijakan Wilayah / Kota')
            ->assertSee('Kota Yogyakarta')
            ->assertSee('Kabupaten Gunungkidul')
            ->set('city_overrides.' . $this->cityA->id, 'disabled')
            ->set('city_overrides.' . $this->cityB->id, 'inherit')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('settingsSaved');

        // Verifikasi perubahan di database
        $this->cityA->refresh();
        $this->cityB->refresh();

        $this->assertFalse($this->cityA->is_matching_seeking_enabled, 'City A should now be false (disabled)');
        $this->assertNull($this->cityB->is_matching_seeking_enabled, 'City B should now be null (inherit)');
    }
}
