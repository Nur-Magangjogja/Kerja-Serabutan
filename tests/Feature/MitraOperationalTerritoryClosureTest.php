<?php

namespace Tests\Feature;

use App\Actions\Matching\MitraMatchingActions;
use App\Livewire\Mitra\Dashboard\OfferRadarWidget;
use App\Livewire\Mitra\Helps\AllHelps;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\Province;
use App\Models\User;
use App\Services\PartnerOnlineService;
use App\Services\RegionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MitraOperationalTerritoryClosureTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;
    protected City $activeCity;
    protected City $inactiveCity;
    protected District $activeDistrict;
    protected District $inactiveDistrict;
    protected User $mitraInInactiveCity;
    protected User $mitraInInactiveDistrict;
    protected User $mitraInActiveCity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->province = Province::create([
            'name'      => 'DI Yogyakarta',
            'is_active' => true,
        ]);

        $this->activeCity = City::create([
            'province_id' => $this->province->id,
            'name'        => 'Kota Yogyakarta',
            'latitude'    => -7.7956,
            'longitude'   => 110.3695,
            'is_active'   => true,
        ]);

        $this->inactiveCity = City::create([
            'province_id' => $this->province->id,
            'name'        => 'Kabupaten Sleman',
            'latitude'    => -7.7156,
            'longitude'   => 110.3556,
            'is_active'   => false,
        ]);

        $this->activeDistrict = District::create([
            'city_id'   => $this->activeCity->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);

        $this->inactiveDistrict = District::create([
            'city_id'   => $this->activeCity->id,
            'name'      => 'Gondomanan',
            'is_active' => false,
        ]);

        $this->mitraInInactiveCity = User::factory()->create([
            'role'        => 'mitra',
            'status'      => 'active',
            'city_id'     => $this->inactiveCity->id,
            'district_id' => null,
        ]);

        $this->mitraInInactiveDistrict = User::factory()->create([
            'role'        => 'mitra',
            'status'      => 'active',
            'city_id'     => $this->activeCity->id,
            'district_id' => $this->inactiveDistrict->id,
        ]);

        $this->mitraInActiveCity = User::factory()->create([
            'role'        => 'mitra',
            'status'      => 'active',
            'city_id'     => $this->activeCity->id,
            'district_id' => $this->activeDistrict->id,
        ]);
    }

    public function test_region_service_returns_exact_closure_message_for_city_and_district(): void
    {
        $regionService = app(RegionService::class);

        // Inactive city message
        $cityMsg = $regionService->getClosedRegionMessage(null, $this->inactiveCity->id);
        $this->assertEquals(
            'Wilayah "Kabupaten Sleman" sedang ditutup sementara demi keamanan / penataan operasional dan tidak menerima permintaan bantuan baru.',
            $cityMsg
        );

        // Inactive district message
        $districtMsg = $regionService->getClosedRegionMessage($this->inactiveDistrict->id, $this->activeCity->id);
        $this->assertEquals(
            'Kecamatan "Gondomanan" sedang ditutup sementara demi keamanan / penataan operasional dan tidak menerima permintaan bantuan baru.',
            $districtMsg
        );

        // Active territory returns null
        $activeMsg = $regionService->getClosedRegionMessage($this->activeDistrict->id, $this->activeCity->id);
        $this->assertNull($activeMsg);
    }

    public function test_matching_radar_shows_closure_warning_and_blocks_mitra_in_inactive_city(): void
    {
        $this->actingAs($this->mitraInInactiveCity);

        $expectedMessage = 'Wilayah "Kabupaten Sleman" sedang ditutup sementara demi keamanan / penataan operasional dan tidak menerima permintaan bantuan baru.';

        // 1. Blade Component contains closure banner & message
        Livewire::test(OfferRadarWidget::class)
            ->assertSee('Wilayah Operasional Ditutup Sementara')
            ->assertSee($expectedMessage)
            ->assertSee('Wilayah Ditutup');

        // 2. Action goOnline returns error with exact message
        $resOnline = app(MitraMatchingActions::class)->goOnline($this->mitraInInactiveCity);
        $this->assertFalse($resOnline['success']);
        $this->assertEquals($expectedMessage, $resOnline['message']);

        // 3. Action startSearching returns error with exact message
        $resSearching = app(MitraMatchingActions::class)->startSearching($this->mitraInInactiveCity, -7.7156, 110.3556);
        $this->assertFalse($resSearching['success']);
        $this->assertEquals($expectedMessage, $resSearching['message']);
    }

    public function test_matching_radar_shows_closure_warning_and_blocks_mitra_in_inactive_district(): void
    {
        $this->actingAs($this->mitraInInactiveDistrict);

        $expectedMessage = 'Kecamatan "Gondomanan" sedang ditutup sementara demi keamanan / penataan operasional dan tidak menerima permintaan bantuan baru.';

        // 1. Livewire radar widget contains closure banner
        Livewire::test(OfferRadarWidget::class)
            ->assertSee('Wilayah Operasional Ditutup Sementara')
            ->assertSee($expectedMessage);

        // 2. Action goOnline returns error
        $resOnline = app(MitraMatchingActions::class)->goOnline($this->mitraInInactiveDistrict);
        $this->assertFalse($resOnline['success']);
        $this->assertEquals($expectedMessage, $resOnline['message']);

        // 3. Service directly throws exception with exact message
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($expectedMessage);
        app(PartnerOnlineService::class)->goOnline($this->mitraInInactiveDistrict);
    }

    public function test_pool_all_helps_shows_closure_warning_and_blocks_taking_help_in_inactive_city(): void
    {
        $this->actingAs($this->mitraInInactiveCity);

        $expectedMessage = 'Wilayah "Kabupaten Sleman" sedang ditutup sementara demi keamanan / penataan operasional dan tidak menerima permintaan bantuan baru.';

        // Create help in active city
        $help = Help::create([
            'user_id'       => User::factory()->create(['role' => 'customer'])->id,
            'city_id'       => $this->activeCity->id,
            'district_id'   => $this->activeDistrict->id,
            'title'         => 'Bantuan Aktif',
            'description'   => 'Deskripsi bantuan aktif',
            'status'        => Help::STATUS_MENUNGGU_MITRA,
            'dispatch_mode' => Help::DISPATCH_MODE_POOL,
            'amount'        => 50000,
            'service_type'  => Help::SERVICE_TYPE_ON_SITE,
            'latitude'      => -7.7956,
            'longitude'     => 110.3695,
        ]);

        // 1. AllHelps component renders closure banner
        Livewire::test(AllHelps::class)
            ->assertSee('Wilayah Operasional Ditutup Sementara')
            ->assertSee($expectedMessage)
            // Attempt to take help should fail and flash closure message
            ->call('takeHelp', $help->id, -7.7156, 110.3556)
            ->assertSee($expectedMessage);

        // Verify help remains untaken
        $this->assertNull($help->fresh()->mitra_id);
    }

    public function test_mitra_cannot_take_help_located_in_inactive_city(): void
    {
        $this->actingAs($this->mitraInActiveCity);

        $inactiveCityHelp = Help::create([
            'user_id'       => User::factory()->create(['role' => 'customer'])->id,
            'city_id'       => $this->inactiveCity->id,
            'district_id'   => null,
            'title'         => 'Bantuan Sleman Tertutup',
            'description'   => 'Deskripsi bantuan di kota nonaktif',
            'status'        => Help::STATUS_MENUNGGU_MITRA,
            'dispatch_mode' => Help::DISPATCH_MODE_POOL,
            'amount'        => 50000,
            'service_type'  => Help::SERVICE_TYPE_ON_SITE,
            'latitude'      => -7.7156,
            'longitude'     => 110.3556,
        ]);

        $expectedMessage = 'Wilayah "Kabupaten Sleman" sedang ditutup sementara demi keamanan / penataan operasional dan tidak menerima permintaan bantuan baru.';

        Livewire::test(AllHelps::class)
            ->call('takeHelp', $inactiveCityHelp->id, -7.7956, 110.3695)
            ->assertSee($expectedMessage);

        // Verify help remains untaken
        $this->assertNull($inactiveCityHelp->fresh()->mitra_id);
    }
}
