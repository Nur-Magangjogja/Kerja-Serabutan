<?php

namespace Tests\Feature;

use App\Livewire\Mitra\Helps\AllHelps;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MitraPoolTabsFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $mitra;
    protected City $cityJogja;
    protected City $citySleman;
    protected District $districtDanurejan;
    protected District $districtGondomanan;
    protected District $districtDepok;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Kota & Kecamatan
        $this->cityJogja = City::create([
            'name'      => 'Kota Yogyakarta',
            'province'  => 'DI Yogyakarta',
            'latitude'  => -7.7956,
            'longitude' => 110.3695,
        ]);

        $this->citySleman = City::create([
            'name'      => 'Kabupaten Sleman',
            'province'  => 'DI Yogyakarta',
            'latitude'  => -7.7156,
            'longitude' => 110.3556,
        ]);

        $this->districtDanurejan = District::create([
            'city_id'   => $this->cityJogja->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);

        $this->districtGondomanan = District::create([
            'city_id'   => $this->cityJogja->id,
            'name'      => 'Gondomanan',
            'is_active' => true,
        ]);

        $this->districtDepok = District::create([
            'city_id'   => $this->citySleman->id,
            'name'      => 'Depok',
            'is_active' => true,
        ]);

        // 2. Setup Mitra (Berdiri di Malioboro, Danurejan, Kota Jogja: -7.7930, 110.3658)
        $this->mitra = User::factory()->create([
            'role'        => 'mitra',
            'city_id'     => $this->cityJogja->id,
            'district_id' => $this->districtDanurejan->id,
        ]);

        PartnerOnlineState::create([
            'user_id'         => $this->mitra->id,
            'matching_status' => PartnerOnlineState::STATUS_SEARCHING,
            'latitude'        => -7.7930,
            'longitude'       => 110.3658,
            'last_seen_at'    => now(),
        ]);
    }

    public function test_mitra_pool_tabs_filter_correctly_between_10km_district_and_city()
    {
        $customer = User::factory()->create(['role' => 'customer']);

        // Order 1: Dekat (1.5 KM dari mitra), di Kecamatan Danurejan, Kota Jogja
        $helpNearbyDanurejan = Help::create([
            'user_id'       => $customer->id,
            'order_id'      => 'HLP-NEAR-001',
            'city_id'       => $this->cityJogja->id,
            'district_id'   => $this->districtDanurejan->id,
            'title'         => 'Order Dekat Danurejan',
            'description'   => 'Dekat 1.5 KM',
            'amount'        => 35000,
            'status'        => Help::STATUS_MENUNGGU_MITRA,
            'dispatch_mode' => Help::DISPATCH_MODE_POOL,
            'latitude'      => -7.7940,
            'longitude'     => 110.3660,
        ]);

        // Order 2: Di Kecamatan Gondomanan (3 KM dari mitra), masih di Kota Jogja
        $helpGondomanan = Help::create([
            'user_id'       => $customer->id,
            'order_id'      => 'HLP-GONDO-002',
            'city_id'       => $this->cityJogja->id,
            'district_id'   => $this->districtGondomanan->id,
            'title'         => 'Order Gondomanan',
            'description'   => '3 KM beda kecamatan',
            'amount'        => 50000,
            'status'        => Help::STATUS_MENUNGGU_MITRA,
            'dispatch_mode' => Help::DISPATCH_MODE_POOL,
            'latitude'      => -7.8010,
            'longitude'     => 110.3640,
        ]);

        // Order 3: Jauh (18 KM dari mitra), tapi MASIH di Kota Jogja (simulasi ujung kota/kabupaten)
        $helpFarCity = Help::create([
            'user_id'       => $customer->id,
            'order_id'      => 'HLP-FAR-003',
            'city_id'       => $this->cityJogja->id,
            'district_id'   => $this->districtGondomanan->id,
            'title'         => 'Order Ujung Kota Jogja (18 KM)',
            'description'   => '18 KM dari mitra',
            'amount'        => 75000,
            'status'        => Help::STATUS_MENUNGGU_MITRA,
            'dispatch_mode' => Help::DISPATCH_MODE_POOL,
            'latitude'      => -7.6300, // ~18 KM ke utara
            'longitude'     => 110.3658,
        ]);

        // Order 4: Di Sleman (Luar Kota Jogja), berjarak 28 KM
        $helpSleman = Help::create([
            'user_id'       => $customer->id,
            'order_id'      => 'HLP-SLEMAN-004',
            'city_id'       => $this->citySleman->id,
            'district_id'   => $this->districtDepok->id,
            'title'         => 'Order Luar Kota Sleman (28 KM)',
            'description'   => 'Luar Kota 28 KM',
            'amount'        => 60000,
            'status'        => Help::STATUS_MENUNGGU_MITRA,
            'dispatch_mode' => Help::DISPATCH_MODE_POOL,
            'latitude'      => -7.5500,
            'longitude'     => 110.4300,
        ]);

        $this->actingAs($this->mitra);

        // --- TAB 1: Radius <= 10 KM ---
        // Seharusnya hanya menampilkan Order 1 dan Order 2 (jarak <= 10 KM). Order 3 (18 KM) & Order 4 tidak masuk.
        Livewire::test(AllHelps::class)
            ->set('districtFilter', 'all')
            ->assertViewHas('countRadius10km', 2)
            ->assertSee('Order Dekat Danurejan')
            ->assertSee('Order Gondomanan')
            ->assertDontSee('Order Ujung Kota Jogja (18 KM)')
            ->assertDontSee('Order Luar Kota Sleman');

        // --- TAB 2: Kecamatan Danurejan ---
        // Seharusnya menampilkan semua order di Kecamatan Danurejan (Order 1)
        Livewire::test(AllHelps::class)
            ->set('districtFilter', 'my_district')
            ->assertViewHas('countDistrict', 1)
            ->assertSee('Order Dekat Danurejan')
            ->assertDontSee('Order Gondomanan')
            ->assertDontSee('Order Ujung Kota Jogja (18 KM)')
            ->assertDontSee('Order Luar Kota Sleman');

        // --- TAB 3: Kabupaten / Kota Yogyakarta ---
        // Seharusnya menampilkan SEMUA order di Kota Jogja (Order 1, Order 2, DAN Order 3 yang 18 KM) tanpa terpotong radius 10 KM!
        Livewire::test(AllHelps::class)
            ->set('districtFilter', 'my_city')
            ->assertViewHas('countCity', 3)
            ->assertSee('Order Dekat Danurejan')
            ->assertSee('Order Gondomanan')
            ->assertSee('Order Ujung Kota Jogja (18 KM)')
            ->assertDontSee('Order Luar Kota Sleman');
    }
}
