<?php

namespace Tests\Feature;

use App\Livewire\Mitra\Dashboard\Index as MitraDashboardIndex;
use App\Livewire\Mitra\Dashboard\OfferRadarWidget;
use App\Models\AppSetting;
use App\Models\City;
use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\DashboardStatsService;
use App\Services\PartnerOnlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MitraOperationalDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected City $cityA; // Profile Domicile Sleman
    protected City $cityB; // Operational Runtime Bantul
    protected City $cityC; // Changed Profile Domicile Yogyakarta
    protected User $mitra;
    protected User $customer;
    protected DashboardStatsService $statsService;

    protected function setUp(): void
    {
        parent::setUp();

        AppSetting::set('heartbeat_ttl_seconds', 60);
        AppSetting::set('max_operational_radius_km', 10.0);

        PartnerOnlineService::clearStateCache();

        $this->cityA = City::create([
            'name'      => 'Sleman',
            'latitude'  => -7.7156,
            'longitude' => 110.3556,
            'is_active' => true,
        ]);

        $this->cityB = City::create([
            'name'      => 'Bantul',
            'latitude'  => -7.8890,
            'longitude' => 110.3290,
            'is_active' => true,
        ]);

        $this->cityC = City::create([
            'name'      => 'Kota Yogyakarta',
            'latitude'  => -7.7956,
            'longitude' => 110.3695,
            'is_active' => true,
        ]);

        $this->mitra = User::factory()->create([
            'role'     => 'mitra',
            'city_id'  => $this->cityA->id,
            'verified' => true,
            'status'   => 'active',
        ]);
        UserBalance::create(['user_id' => $this->mitra->id, 'balance' => 100000]);

        $this->customer = User::factory()->create([
            'role'    => 'customer',
            'city_id' => $this->cityA->id,
        ]);

        $this->statsService = new DashboardStatsService();
    }

    /**
     * TEST A: Profile City A + GPS City B -> dashboard operational label B.
     */
    public function test_a_profile_city_a_and_gps_city_b_shows_dashboard_operational_label_b(): void
    {
        $this->actingAs($this->mitra);

        // Mitra Profile is City A (Sleman)
        $this->assertEquals($this->cityA->id, $this->mitra->city_id);

        // Set fresh GPS to City B (Bantul)
        PartnerOnlineState::updateOrCreate(
            ['user_id' => $this->mitra->id],
            [
                'latitude'        => $this->cityB->latitude,
                'longitude'       => $this->cityB->longitude,
                'matching_status' => PartnerOnlineState::STATUS_ONLINE,
                'last_seen_at'    => now(),
            ]
        );
        PartnerOnlineService::clearStateCache($this->mitra->id);

        Livewire::test(MitraDashboardIndex::class)
            ->assertViewHas('operationalCity', fn($c) => $c?->name === 'Bantul')
            ->assertSee('Bantul')
            ->assertDontSee('Sleman');
    }

    /**
     * TEST B: Profile City A + GPS City B -> nearby jobs around B, not A.
     */
    public function test_b_profile_city_a_and_gps_city_b_returns_nearby_jobs_for_city_b(): void
    {
        // Help 1 in City A (Sleman)
        Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->cityA->id,
            'title'          => 'Help Sleman (Profile City)',
            'description'    => 'Pool',
            'amount'         => 50000,
            'admin_fee'      => 5000,
            'total_amount'   => 55000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_POOL,
        ]);

        // Help 2 in City B (Bantul)
        Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->cityB->id,
            'title'          => 'Help Bantul (Runtime GPS City)',
            'description'    => 'Pool',
            'amount'         => 60000,
            'admin_fee'      => 6000,
            'total_amount'   => 66000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_POOL,
        ]);

        // Fresh GPS at City B
        PartnerOnlineState::updateOrCreate(
            ['user_id' => $this->mitra->id],
            [
                'latitude'        => $this->cityB->latitude,
                'longitude'       => $this->cityB->longitude,
                'matching_status' => PartnerOnlineState::STATUS_ONLINE,
                'last_seen_at'    => now(),
            ]
        );
        PartnerOnlineService::clearStateCache($this->mitra->id);

        $nearby = $this->statsService->getNearbyHelps($this->mitra, 5, true);

        $this->assertCount(1, $nearby);
        $this->assertEquals('Help Bantul (Runtime GPS City)', $nearby->first()->title);
        $this->assertEquals($this->cityB->id, $nearby->first()->city_id);

        $recommended = $this->statsService->getRecommendedHelps($this->mitra, 5, true);
        $this->assertTrue($recommended->contains('city_id', $this->cityB->id));
        $this->assertFalse($recommended->contains('city_id', $this->cityA->id));
    }

    /**
     * TEST C: Profile City A + GPS City B -> radar B.
     */
    public function test_c_profile_city_a_and_gps_city_b_shows_radar_for_city_b(): void
    {
        $this->actingAs($this->mitra);

        // Fresh GPS at City B
        PartnerOnlineState::updateOrCreate(
            ['user_id' => $this->mitra->id],
            [
                'latitude'        => $this->cityB->latitude,
                'longitude'       => $this->cityB->longitude,
                'matching_status' => PartnerOnlineState::STATUS_SEARCHING,
                'last_seen_at'    => now(),
            ]
        );
        PartnerOnlineService::clearStateCache($this->mitra->id);

        Livewire::test(OfferRadarWidget::class)
            ->assertViewHas('operationalCity', fn($c) => $c?->name === 'Bantul')
            ->assertSee('Bantul')
            ->assertDontSee('Sleman');
    }

    /**
     * TEST D: GPS missing -> no profile fallback.
     */
    public function test_d_gps_missing_does_not_fallback_to_profile(): void
    {
        $this->actingAs($this->mitra);

        // No PartnerOnlineState GPS record exists
        PartnerOnlineState::where('user_id', $this->mitra->id)->delete();
        PartnerOnlineService::clearStateCache($this->mitra->id);

        // 1. Dashboard Index header badge shows "Lokasi belum tersedia"
        Livewire::test(MitraDashboardIndex::class)
            ->assertViewHas('operationalCity', null)
            ->assertSee('Lokasi belum tersedia')
            ->assertDontSee('Sleman');

        // 2. Nearby jobs returns empty collection (NO fake nearby jobs)
        $nearby = $this->statsService->getNearbyHelps($this->mitra, 5, true);
        $this->assertCount(0, $nearby);

        // 3. Operational city resolver returns null
        $resolvedCityId = $this->statsService->resolveMitraOperationalCityId($this->mitra);
        $this->assertNull($resolvedCityId);
    }

    /**
     * TEST E: GPS stale -> no stale operational display / no profile fallback.
     */
    public function test_e_gps_stale_does_not_display_stale_or_profile_territory(): void
    {
        $this->actingAs($this->mitra);

        // Set STALE GPS (last_seen_at 15 minutes ago, TTL is 60s)
        PartnerOnlineState::updateOrCreate(
            ['user_id' => $this->mitra->id],
            [
                'latitude'        => $this->cityB->latitude,
                'longitude'       => $this->cityB->longitude,
                'matching_status' => PartnerOnlineState::STATUS_ONLINE,
                'last_seen_at'    => now()->subMinutes(15),
            ]
        );
        PartnerOnlineService::clearStateCache($this->mitra->id);

        // 1. Dashboard Index header badge shows "Lokasi belum tersedia"
        Livewire::test(MitraDashboardIndex::class)
            ->assertViewHas('operationalCity', null)
            ->assertSee('Lokasi belum tersedia')
            ->assertDontSee('Bantul')
            ->assertDontSee('Sleman');

        // 2. Nearby jobs returns empty collection
        $nearby = $this->statsService->getNearbyHelps($this->mitra, 5, true);
        $this->assertCount(0, $nearby);

        // 3. Operational city resolver returns null
        $resolvedCityId = $this->statsService->resolveMitraOperationalCityId($this->mitra);
        $this->assertNull($resolvedCityId);
    }

    /**
     * TEST F: Change profile A -> C while GPS B -> operational dashboard remains B.
     */
    public function test_f_profile_change_from_a_to_c_keeps_operational_dashboard_at_b(): void
    {
        $this->actingAs($this->mitra);

        // Fresh GPS at City B (Bantul)
        PartnerOnlineState::updateOrCreate(
            ['user_id' => $this->mitra->id],
            [
                'latitude'        => $this->cityB->latitude,
                'longitude'       => $this->cityB->longitude,
                'matching_status' => PartnerOnlineState::STATUS_ONLINE,
                'last_seen_at'    => now(),
            ]
        );
        PartnerOnlineService::clearStateCache($this->mitra->id);

        // Change Mitra profile domicile from City A to City C (Kota Yogyakarta)
        $this->mitra->update(['city_id' => $this->cityC->id]);
        $this->assertEquals($this->cityC->id, $this->mitra->fresh()->city_id);

        // Dashboard remains City B
        Livewire::test(MitraDashboardIndex::class)
            ->assertViewHas('operationalCity', fn($c) => $c?->name === 'Bantul')
            ->assertSee('Bantul')
            ->assertDontSee('Kota Yogyakarta');

        // Nearby helps remains City B
        $resolvedCityId = $this->statsService->resolveMitraOperationalCityId($this->mitra);
        $this->assertEquals($this->cityB->id, $resolvedCityId);
    }

    /**
     * TEST G: Profile page still shows identity territory (domicile).
     */
    public function test_g_profile_page_still_shows_identity_territory(): void
    {
        // Fresh GPS at City B (Bantul)
        PartnerOnlineState::updateOrCreate(
            ['user_id' => $this->mitra->id],
            [
                'latitude'        => $this->cityB->latitude,
                'longitude'       => $this->cityB->longitude,
                'matching_status' => PartnerOnlineState::STATUS_ONLINE,
                'last_seen_at'    => now(),
            ]
        );
        PartnerOnlineService::clearStateCache($this->mitra->id);

        // Domicile in user model remains City A
        $this->assertEquals($this->cityA->id, $this->mitra->fresh()->city_id);
        $this->assertEquals('Sleman', $this->mitra->fresh()->city_name);

        // Identity field is untouched by GPS
        $this->mitra->update(['city_id' => $this->cityC->id]);
        $this->assertEquals('Kota Yogyakarta', $this->mitra->fresh()->city_name);
    }
}
