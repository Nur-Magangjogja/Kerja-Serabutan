<?php

namespace Tests\Feature\Customer;

use App\Livewire\Customer\Helps\Create;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\HelpMatchingService;
use App\Services\HelpPricingService;
use App\Services\PartnerOnlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateThreeServiceTypesTest extends TestCase
{
    use RefreshDatabase;

    protected City $city;
    protected District $district;
    protected User $customer;
    protected User $mitraNear;
    protected User $mitraFar;
    protected HelpPricingService $pricingService;
    protected HelpMatchingService $matchingService;
    protected PartnerOnlineService $onlineService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::create([
            'name'       => 'Kota Yogyakarta',
            'state_name' => 'DI Yogyakarta',
            'is_active'  => true,
            'latitude'   => -7.7956,
            'longitude'  => 110.3695,
        ]);

        $this->district = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'role'    => 'customer',
            'city_id' => $this->city->id,
        ]);
        UserBalance::create(['user_id' => $this->customer->id, 'balance' => 1000000]);

        $this->mitraNear = User::factory()->create([
            'role'    => 'mitra',
            'city_id' => $this->city->id,
        ]);
        UserBalance::create(['user_id' => $this->mitraNear->id, 'balance' => 0]);

        $this->mitraFar = User::factory()->create([
            'role'    => 'mitra',
            'city_id' => $this->city->id,
        ]);
        UserBalance::create(['user_id' => $this->mitraFar->id, 'balance' => 0]);

        $this->pricingService  = app(HelpPricingService::class);
        $this->matchingService = app(HelpMatchingService::class);
        $this->onlineService   = app(PartnerOnlineService::class);
    }

    public function test_customer_creates_on_site_service_with_exact_transparent_breakdown()
    {
        $this->actingAs($this->customer);

        $comp = Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_ON_SITE)
            ->set('title', 'Bantu Kupas Bawang dan Bersihkan Dapur')
            ->set('description', 'Perlu bantuan cepat di dapur')
            ->set('amount', 20000)
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->call('prepareConfirm')
            ->assertSet('showConfirmModal', true)
            ->assertSet('confirmServiceFee', 20000.0)
            ->assertSet('confirmPlatformFee', 2000.0)
            ->assertSet('confirmTotal', 22000.0)
            ->call('save')
            ->assertHasNoErrors();

        $help = Help::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($help);
        $comp->assertRedirect(route('customer.helps.detail', ['id' => $help->id]));
        $this->assertEquals(Help::SERVICE_TYPE_ON_SITE, $help->service_type);
        $this->assertEquals(20000, (int) $help->service_fee);
        $this->assertEquals(0, (int) $help->travel_fee);
        $this->assertEquals(0, (int) $help->item_fund);
        $this->assertEquals(2000, (int) $help->platform_fee_amount);
        $this->assertEquals(22000, (int) $help->total_amount);
        $this->assertEquals(Help::PAYMENT_STATUS_PAID, $help->payment_status);
        $this->assertEquals(Help::ESCROW_STATUS_HELD, $help->escrow_status);

        // Check user balance: 1.000.000 - 22.000 = 978.000
        $balance = UserBalance::where('user_id', $this->customer->id)->first();
        $this->assertEquals(978000, (int) $balance->balance);
    }

    public function test_customer_creates_pickup_delivery_with_route_pricing()
    {
        $this->actingAs($this->customer);

        $comp = Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_PICKUP_DELIVERY)
            ->set('title', 'Antar Paket Dokumen Penting')
            ->set('description', 'Antar berkas dari kantor ke rumah klien')
            ->set('pickup_address', 'Kantor Pusat Yogyakarta')
            ->set('pickup_latitude', -7.7956)
            ->set('pickup_longitude', 110.3695)
            ->set('delivery_address', 'Jl. Kaliurang KM 8')
            ->set('delivery_latitude', -7.7400)
            ->set('delivery_longitude', 110.3800)
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->call('prepareConfirm')
            ->assertSet('showConfirmModal', true)
            ->call('save')
            ->assertHasNoErrors();

        $help = Help::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($help);
        $comp->assertRedirect(route('customer.helps.detail', ['id' => $help->id]));
        $this->assertEquals(Help::SERVICE_TYPE_PICKUP_DELIVERY, $help->service_type);
        $this->assertGreaterThanOrEqual(10000, (int) $help->service_fee);
        $this->assertEquals(2000, (int) $help->platform_fee_amount);
        $this->assertEquals($help->service_fee + 2000, (int) $help->total_amount);
        $this->assertEquals('Kantor Pusat Yogyakarta', $help->pickup_address);
        $this->assertEquals('Jl. Kaliurang KM 8', $help->delivery_address);
    }

    public function test_pickup_delivery_under_5km_enforces_minimum_10k_fee()
    {
        $this->actingAs($this->customer);

        // Jarak 2.5 KM -> Base Fare 10.000 + ceil(2.5)*2.500 = 17.500
        $comp = Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_PICKUP_DELIVERY)
            ->call('updateRouteDistanceRoad', 2.5)
            ->assertSet('route_distance_km', 2.5)
            ->assertSet('amount', 17500)
            ->assertSet('minHelpNominal', 17500)
            ->set('title', 'Kirim Dokumen Dekat')
            ->set('description', 'Kirim surat ke seberang kantor')
            ->set('pickup_address', 'Jl. Malioboro No. 10')
            ->set('pickup_latitude', -7.7928)
            ->set('pickup_longitude', 110.3658)
            ->set('delivery_address', 'Jl. Mataram No. 20')
            ->set('delivery_latitude', -7.7910)
            ->set('delivery_longitude', 110.3680)
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->set('latitude', -7.7928)
            ->set('longitude', 110.3658)
            ->call('prepareConfirm')
            ->assertSet('showConfirmModal', true)
            ->assertSet('confirmServiceFee', 17500.0)
            ->assertSet('confirmPlatformFee', 2000.0)
            ->assertSet('confirmTotal', 19500.0)
            ->call('save')
            ->assertHasNoErrors();

        $help = Help::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($help);
        $comp->assertRedirect(route('customer.helps.detail', ['id' => $help->id]));
        $this->assertEquals(17500, (int) $help->service_fee);
        $this->assertEquals(2000, (int) $help->platform_fee_amount);
        $this->assertEquals(19500, (int) $help->total_amount);
    }

    public function test_pickup_delivery_road_distance_recalculation_and_pricing()
    {
        $this->actingAs($this->customer);

        // Simulasi update jarak rute jalan raya (road network distance) dari OSRM
        // Jarak 7.2 KM -> 10.000 + ceil(7.2) * 2500 = 10.000 + 8 * 2500 = 30000
        $comp = Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_PICKUP_DELIVERY)
            ->call('updateRouteDistanceRoad', 7.2)
            ->assertSet('route_distance_km', 7.2)
            ->assertSet('amount', 30000)
            ->assertSet('minHelpNominal', 30000)
            ->set('title', 'Kirim Parsel Makanan')
            ->set('description', 'Kirim parsel dari toko ke rumah')
            ->set('pickup_address', 'Jl. Malioboro No. 1')
            ->set('pickup_latitude', -7.7928)
            ->set('pickup_longitude', 110.3658)
            ->set('delivery_address', 'Jl. Kaliurang KM 8')
            ->set('delivery_latitude', -7.7400)
            ->set('delivery_longitude', 110.3800)
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->set('latitude', -7.7928)
            ->set('longitude', 110.3658)
            ->call('prepareConfirm')
            ->assertSet('showConfirmModal', true)
            ->assertSet('confirmServiceFee', 30000.0)
            ->assertSet('confirmPlatformFee', 2000.0)
            ->assertSet('confirmTotal', 32000.0)
            ->call('save')
            ->assertHasNoErrors();

        $help = Help::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($help);
        $comp->assertRedirect(route('customer.helps.detail', ['id' => $help->id]));
        $this->assertEquals(30000, (int) $help->service_fee);
        $this->assertEquals(32000, (int) $help->total_amount);
    }

    public function test_on_site_matching_radius_canonical()
    {
        // 1. On-site <= 20k -> radius 5.0 KM (canonical tier 1)
        $help10k = new Help(['service_type' => Help::SERVICE_TYPE_ON_SITE, 'service_fee' => 10000, 'amount' => 10000]);
        $this->assertEquals(5.0, $this->matchingService->computeServiceMaxMatchingRadius($help10k));

        // 2. On-site 20k-40k -> radius 7.5 KM (canonical tier 2)
        $help30k = new Help(['service_type' => Help::SERVICE_TYPE_ON_SITE, 'service_fee' => 30000, 'amount' => 30000]);
        $this->assertEquals(7.5, $this->matchingService->computeServiceMaxMatchingRadius($help30k));

        // 3. On-site > 40k -> radius 10.0 KM (canonical tier 3)
        $help50k = new Help(['service_type' => Help::SERVICE_TYPE_ON_SITE, 'service_fee' => 50000, 'amount' => 50000]);
        $this->assertEquals(10.0, $this->matchingService->computeServiceMaxMatchingRadius($help50k));

        // 4. Pickup & Delivery -> radius 10.0 KM (AppSetting::MAX_OPERATIONAL_RADIUS_KM)
        $helpPickup = new Help(['service_type' => Help::SERVICE_TYPE_PICKUP_DELIVERY, 'service_fee' => 20000, 'amount' => 20000]);
        $this->assertEquals(10.0, $this->matchingService->computeServiceMaxMatchingRadius($helpPickup));
    }

    public function test_amount_adjustments_by_hundred_and_custom_inputs()
    {
        $this->actingAs($this->customer);

        Livewire::test(Create::class)
            ->set('amount', 10000)
            ->call('adjustAmount', 100)
            ->assertSet('amount', 10100)
            ->call('adjustAmount', -100)
            ->assertSet('amount', 10000)
            ->call('adjustAmount', 500)
            ->assertSet('amount', 10500)
            ->set('amount', 12300)
            ->assertSet('amount', 12300);
    }

    public function test_customer_saved_landmarks_lifecycle_in_profile_and_create_help()
    {
        $this->actingAs($this->customer);

        // 1. Tambah landmark di Profile
        Livewire::test(\App\Livewire\Profile\UpdateProfileInformationForm::class)
            ->call('openNewLandmarkForm')
            ->set('newLandmarkLabel', 'Rumah Utama')
            ->set('newLandmarkPatokan', 'Pagar hitam samping warung Madura no 12')
            ->call('saveLandmark')
            ->assertHasNoErrors();

        $this->customer->refresh();
        $landmarks = $this->customer->getSavedLandmarksList();
        $this->assertCount(1, $landmarks);
        $this->assertEquals('Rumah Utama', $landmarks[0]['label']);
        $this->assertEquals('Pagar hitam samping warung Madura no 12', $landmarks[0]['patokan']);

        // 2. Gunakan landmark di Create Help Form
        Livewire::test(Create::class)
            ->assertCount('savedLandmarks', 1)
            ->call('applySavedLandmark', 'Pagar hitam samping warung Madura no 12')
            ->assertSet('full_address', 'Pagar hitam samping warung Madura no 12');

        // 3. Simpan landmark baru langsung dari Create Help Form
        Livewire::test(Create::class)
            ->set('full_address', 'Kantor Graha Lt 2 depan lobi satpam')
            ->set('newLandmarkLabel', 'Kantor')
            ->call('saveCurrentPatokanToProfile')
            ->assertHasNoErrors()
            ->assertCount('savedLandmarks', 2);

        $this->customer->refresh();
        $updatedLandmarks = $this->customer->getSavedLandmarksList();
        $this->assertCount(2, $updatedLandmarks);
        $this->assertEquals('Kantor', $updatedLandmarks[1]['label']);
        $this->assertEquals('Kantor Graha Lt 2 depan lobi satpam', $updatedLandmarks[1]['patokan']);
    }

    public function test_service_type_switching_isolates_map_and_transaction_data()
    {
        $this->actingAs($this->customer);

        // 1. Awalnya input data Pickup & Delivery
        $comp = Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_PICKUP_DELIVERY)
            ->assertDispatched('service-type-changed')
            ->set('pickup_address', 'Jl. Kaliurang KM 5')
            ->set('pickup_latitude', -7.7600)
            ->set('pickup_longitude', 110.3800)
            ->set('delivery_address', 'Jl. Gejayan No. 20')
            ->set('delivery_latitude', -7.7700)
            ->set('delivery_longitude', 110.3900)
            ->set('route_distance_km', 4.5);

        $this->assertEquals('Jl. Kaliurang KM 5', $comp->get('pickup_address'));
        $this->assertEquals(4.5, $comp->get('route_distance_km'));

        // 2. Beralih ke On-Site Service (Kerja di Lokasi) -> Data pickup/delivery harus bersih total
        $comp->call('setServiceType', Help::SERVICE_TYPE_ON_SITE)
            ->assertDispatched('service-type-changed')
            ->assertSet('pickup_address', '')
            ->assertSet('pickup_latitude', null)
            ->assertSet('pickup_longitude', null)
            ->assertSet('delivery_address', '')
            ->assertSet('delivery_latitude', null)
            ->assertSet('delivery_longitude', null)
            ->assertSet('route_distance_km', 0.0)
            ->assertSet('minHelpNominal', 10000);

        // 3. Lengkapi data on-site dan simpan transaksi
        $comp->set('title', 'Bantu Angkat Lemari')
            ->set('description', 'Perlu bantuan angkat lemari ke lantai 2')
            ->set('location', 'Jl. Malioboro No. 50')
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->set('amount', 25000)
            ->call('prepareConfirm')
            ->assertSet('showConfirmModal', true)
            ->call('save')
            ->assertHasNoErrors();

        $help = Help::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($help);
        $comp->assertRedirect(route('customer.helps.detail', ['id' => $help->id]));
        $this->assertEquals(Help::SERVICE_TYPE_ON_SITE, $help->service_type);
        $this->assertNull($help->pickup_address);
        $this->assertNull($help->pickup_latitude);
        $this->assertNull($help->delivery_address);
        $this->assertNull($help->delivery_latitude);
        $this->assertEquals(0.0, (float) $help->service_route_distance_km);
        $this->assertEquals('Jl. Malioboro No. 50', $help->location);
        $this->assertEquals(25000, (int) $help->service_fee);
        $this->assertEquals(27000, (int) $help->total_amount);
    }

    public function test_geoservice_validates_restricted_and_forbidden_osm_locations()
    {
        $geo = app(\App\Services\GeoService::class);

        // 1. Koordinat di luar batas Indonesia
        $outOfBounds = $geo->validateLocationSafety(40.7128, -74.0060); // New York
        $this->assertFalse($outOfBounds['is_safe']);
        $this->assertStringContainsString('luar batas wilayah', $outOfBounds['reason']);

        // 2. Koordinat perairan terbuka / laut lepas
        $waterArea = $geo->validateLocationSafety(-7.7956, 110.3695, [
            'category' => 'natural',
            'type'     => 'ocean',
            'address'  => ['country_code' => 'id']
        ]);
        $this->assertFalse($waterArea['is_safe']);
        $this->assertStringContainsString('area perairan', $waterArea['reason']);

        // 3. Zona militer terlarang
        $militaryZone = $geo->validateLocationSafety(-7.7956, 110.3695, [
            'category' => 'military',
            'type'     => 'naval_base',
            'address'  => ['country_code' => 'id']
        ]);
        $this->assertFalse($militaryZone['is_safe']);
        $this->assertStringContainsString('zona instalasi militer', $militaryZone['reason']);

        // 4. Koordinat daratan valid di Indonesia
        $validArea = $geo->validateLocationSafety(-7.7956, 110.3695, [
            'category' => 'highway',
            'type'     => 'residential',
            'address'  => ['country_code' => 'id']
        ]);
        $this->assertTrue($validArea['is_safe']);
        $this->assertNull($validArea['reason']);
    }

    public function test_customer_direct_map_click_records_coordinates_and_resolves_territory()
    {
        $this->actingAs($this->customer);

        // 1. On-site direct map click (coordinate only, no initial address)
        Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_ON_SITE)
            ->call('syncOnSiteLocation', -7.7956, 110.3695)
            ->assertSet('latitude', -7.7956)
            ->assertSet('longitude', 110.3695)
            ->assertSet('city_id', $this->city->id)
            ->assertNotSet('location', '');

        $lw = Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_PICKUP_DELIVERY)
            ->call('syncPickupLocation', -7.7956, 110.3695)
            ->assertSet('pickup_latitude', -7.7956)
            ->assertSet('pickup_longitude', 110.3695)
            ->assertSet('city_id', $this->city->id)
            ->assertNotSet('pickup_address', '')
            ->call('syncDeliveryLocation', -7.7600, 110.3700)
            ->assertSet('delivery_latitude', -7.7600)
            ->assertSet('delivery_longitude', 110.3700)
            ->assertNotSet('delivery_address', '');

        $this->assertGreaterThan(0.0, (float) $lw->get('route_distance_km'));
    }

    public function test_customer_form_blocks_restricted_locations()
    {
        $this->actingAs($this->customer);

        // 1. Coba sync on-site ke koordinat luar negeri
        Livewire::test(Create::class)
            ->call('syncOnSiteLocation', 48.8566, 2.3522) // Paris
            ->assertHasErrors(['latitude'])
            ->assertDispatched('restricted-location-detected')
            ->assertSet('latitude', null)
            ->assertSet('longitude', null);

        // 2. Coba sync pickup & delivery ke koordinat luar negeri
        Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_PICKUP_DELIVERY)
            ->call('syncPickupLocation', -20.0, 100.0) // Samudra Hindia jauh
            ->assertHasErrors(['pickup_address'])
            ->assertDispatched('restricted-location-detected')
            ->assertSet('pickup_latitude', null);
    }
}

