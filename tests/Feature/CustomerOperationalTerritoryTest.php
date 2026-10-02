<?php

namespace Tests\Feature;

use App\Livewire\Customer\Helps\Create;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\HelpCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerOperationalTerritoryTest extends TestCase
{
    use RefreshDatabase;

    protected City $cityA;
    protected District $districtA;
    protected City $cityB;
    protected District $districtB1;
    protected District $districtB2;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // City A (Yogyakarta)
        $this->cityA = City::create([
            'name'       => 'Kota Yogyakarta',
            'state_name' => 'DI Yogyakarta',
            'is_active'  => true,
            'latitude'   => -7.7956,
            'longitude'  => 110.3695,
        ]);

        $this->districtA = District::create([
            'city_id'   => $this->cityA->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);

        // City B (Kabupaten Sleman)
        $this->cityB = City::create([
            'name'       => 'Kabupaten Sleman',
            'state_name' => 'DI Yogyakarta',
            'is_active'  => true,
            'latitude'   => -7.7167,
            'longitude'  => 110.3556,
        ]);

        $this->districtB1 = District::create([
            'city_id'   => $this->cityB->id,
            'name'      => 'Depok',
            'is_active' => true,
        ]);

        $this->districtB2 = District::create([
            'city_id'   => $this->cityB->id,
            'name'      => 'Mlati',
            'is_active' => true,
        ]);

        // Customer Profile: Domiciled in City A / District A
        $this->customer = User::factory()->create([
            'role'        => 'customer',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->districtA->id,
        ]);

        UserBalance::create([
            'user_id' => $this->customer->id,
            'balance' => 1000000,
        ]);
    }

    /**
     * Test A: Profile City A / District A + Map City B / District B
     * Expected: Help uses City B and District B. Profile A does not affect order.
     */
    public function test_a_profile_city_a_district_a_with_map_city_b_district_b_uses_b(): void
    {
        $this->actingAs($this->customer);

        Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_ON_SITE)
            ->set('title', 'Bantu Pasang Keramik')
            ->set('description', 'Pemasangan keramik teras di Sleman')
            ->set('amount', 50000)
            ->set('city_id', $this->cityB->id)
            ->set('district_id', $this->districtB2->id)
            ->set('latitude', -7.7167)
            ->set('longitude', 110.3556)
            ->call('prepareConfirm')
            ->assertSet('showConfirmModal', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $help = Help::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($help);
        $this->assertEquals($this->cityB->id, $help->city_id, 'Help city_id must match operational map city B, not profile city A');
        $this->assertEquals($this->districtB2->id, $help->district_id, 'Help district_id must match operational map district B2, not profile district A');
    }

    /**
     * Test B: Profile region OFF + Map region ON
     * Expected: Creation success. Do not reject because Customer profile domicile is OFF.
     */
    public function test_b_profile_region_off_with_map_region_on_allows_creation(): void
    {
        // Deactivate Customer profile domicile (City A and District A)
        $this->cityA->update(['is_active' => false]);
        $this->districtA->update(['is_active' => false]);

        $this->actingAs($this->customer);

        Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_ON_SITE)
            ->set('title', 'Bantu Angkut Kardus')
            ->set('description', 'Perlu bantuan di wilayah Sleman yang aktif')
            ->set('amount', 30000)
            ->set('city_id', $this->cityB->id)
            ->set('district_id', $this->districtB1->id)
            ->set('latitude', -7.7167)
            ->set('longitude', 110.3556)
            ->call('prepareConfirm')
            ->assertSet('showConfirmModal', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $help = Help::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($help);
        $this->assertEquals($this->cityB->id, $help->city_id);
        $this->assertEquals($this->districtB1->id, $help->district_id);
    }

    /**
     * Test C: Profile region ON + Map region OFF
     * Expected: Creation rejected. Region validation must be based on territory from coordinates.
     */
    public function test_c_profile_region_on_with_map_region_off_rejects_creation(): void
    {
        // Customer profile region A is ON, but operational destination City B is OFF
        $this->cityA->update(['is_active' => true]);
        $this->cityB->update(['is_active' => false]);

        $this->actingAs($this->customer);

        // 1. Livewire UI rejection via resolveLocationFromMap
        Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_ON_SITE)
            ->call('syncOnSiteLocation', -7.7167, 110.3556, 'Jl. Magelang KM 5', $this->cityB->name)
            ->assertHasErrors(['city_id']);

        // 2. Direct Service Guard rejection
        $creationService = app(HelpCreationService::class);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('dinonaktifkan sementara');

        $creationService->createHelp($this->customer, [
            'title'        => 'Bantu Cuci Toren',
            'description'  => 'Pembersihan toren air',
            'service_type' => Help::SERVICE_TYPE_ON_SITE,
            'order_mode'   => Help::ORDER_MODE_INSTANT,
            'city_id'      => $this->cityB->id,
            'district_id'  => $this->districtB1->id,
            'latitude'     => -7.7167,
            'longitude'    => 110.3556,
            'amount'       => 50000,
        ]);
    }

    /**
     * Test D: Coordinate district resolution failure
     * Expected: Does NOT arbitrarily pick the first district of that city.
     */
    public function test_d_coordinate_district_resolution_failure_does_not_pick_first_district(): void
    {
        $this->actingAs($this->customer);

        // When coordinates reverse geocoding returns null for districtName (resolution failure)
        $comp = Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_ON_SITE)
            ->call('resolveLocationFromMap', -7.7167, 110.3556, $this->cityB->name, null)
            ->set('title', 'Bantu Perbaiki Lampu')
            ->set('description', 'Perbaikan lampu jalan')
            ->set('amount', 40000)
            ->call('prepareConfirm')
            ->assertSet('showConfirmModal', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $help = Help::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($help);
        $this->assertEquals($this->cityB->id, $help->city_id);
        // Canonical W1: It must NOT arbitrarily take $this->districtB1->id (the first district)
        $this->assertNull($help->district_id, 'District ID must remain null if coordinate district resolution failed, rather than guessing first district');
    }

    /**
     * Test E: Saved landmark in territory different from profile
     * Expected: Help follows landmark coordinates and resolved territory.
     */
    public function test_e_saved_landmark_in_territory_different_from_profile_follows_landmark_coordinates(): void
    {
        $this->actingAs($this->customer);

        // Customer has a saved landmark note in their profile
        $this->customer->addSavedLandmark('Gudang Sleman', 'Komp. Pergudangan Sleman No. 12, pagar biru');

        Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_ON_SITE)
            ->call('applySavedLandmark', 'Komp. Pergudangan Sleman No. 12, pagar biru')
            ->assertSet('full_address', 'Komp. Pergudangan Sleman No. 12, pagar biru')
            // Set coordinates to City B
            ->call('syncOnSiteLocation', -7.7167, 110.3556, 'Komp. Pergudangan Sleman No. 12, pagar biru', $this->cityB->name, $this->districtB2->name)
            ->set('title', 'Bantu Bongkar Muat')
            ->set('description', 'Bongkar muatan barang di gudang')
            ->set('amount', 70000)
            ->call('prepareConfirm')
            ->call('save')
            ->assertHasNoErrors();

        $help = Help::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($help);
        $this->assertEquals($this->cityB->id, $help->city_id);
        $this->assertEquals($this->districtB2->id, $help->district_id);
    }

    /**
     * Test F: Manipulated profile territory
     * Changing profile city/district does not alter Help territory as long as coordinates remain same.
     */
    public function test_f_manipulating_profile_territory_does_not_alter_help_territory_for_same_coordinates(): void
    {
        $this->actingAs($this->customer);

        // Order 1: Profile is City A, coordinates in City B
        Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_ON_SITE)
            ->set('title', 'Order 1 Map City B')
            ->set('description', 'Test 1')
            ->set('amount', 50000)
            ->set('city_id', $this->cityB->id)
            ->set('district_id', $this->districtB1->id)
            ->set('latitude', -7.7167)
            ->set('longitude', 110.3556)
            ->call('save')
            ->assertHasNoErrors();

        $help1 = Help::where('title', 'Order 1 Map City B')->first();
        $this->assertEquals($this->cityB->id, $help1->city_id);

        // Manipulate Customer's profile to a completely new territory (or null)
        $this->customer->update([
            'city_id'     => null,
            'district_id' => null,
        ]);

        // Order 2: Profile is null/manipulated, but coordinates are still City B
        Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_ON_SITE)
            ->set('title', 'Order 2 Map City B')
            ->set('description', 'Test 2')
            ->set('amount', 50000)
            ->set('city_id', $this->cityB->id)
            ->set('district_id', $this->districtB1->id)
            ->set('latitude', -7.7167)
            ->set('longitude', 110.3556)
            ->call('save')
            ->assertHasNoErrors();

        $help2 = Help::where('title', 'Order 2 Map City B')->first();
        $this->assertEquals($this->cityB->id, $help2->city_id, 'Order 2 territory must follow map coordinates regardless of profile changes');
    }
}
