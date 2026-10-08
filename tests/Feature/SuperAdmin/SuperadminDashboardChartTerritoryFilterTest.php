<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\City;
use App\Models\District;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SuperadminDashboardChartTerritoryFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_dashboard_chart_filters_by_territory_and_dispatches_event()
    {
        // 1. Create 2 Cities with Districts
        $city1 = City::create([
            'name' => 'Yogyakarta',
            'state_name' => 'DIY',
            'latitude' => -7.7956,
            'longitude' => 110.3695,
        ]);
        $district1A = District::create([
            'city_id' => $city1->id,
            'name' => 'Gondokusuman',
            'latitude' => -7.7850,
            'longitude' => 110.3750,
        ]);
        $district1B = District::create([
            'city_id' => $city1->id,
            'name' => 'Danurejan',
            'latitude' => -7.7920,
            'longitude' => 110.3700,
        ]);

        $city2 = City::create([
            'name' => 'Sleman',
            'state_name' => 'DIY',
            'latitude' => -7.7150,
            'longitude' => 110.3550,
        ]);
        $district2A = District::create([
            'city_id' => $city2->id,
            'name' => 'Depok',
            'latitude' => -7.7700,
            'longitude' => 110.3900,
        ]);

        // 2. Create Superadmin
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        // 3. Create Users in different territories and dates
        $today = Carbon::today();

        // City 1 - District 1A (2 users)
        $user1 = User::factory()->create([
            'role' => 'customer',
            'city_id' => $city1->id,
            'district_id' => $district1A->id,
            'created_at' => $today->copy()->day(5)->setTime(10, 0),
        ]);
        $user2 = User::factory()->create([
            'role' => 'mitra',
            'city_id' => $city1->id,
            'district_id' => $district1A->id,
            'created_at' => $today->copy()->day(10)->setTime(12, 0),
        ]);

        // City 1 - District 1B (1 user)
        $user3 = User::factory()->create([
            'role' => 'customer',
            'city_id' => $city1->id,
            'district_id' => $district1B->id,
            'created_at' => $today->copy()->day(15)->setTime(14, 0),
        ]);

        // City 2 - District 2A (3 users)
        $user4 = User::factory()->create([
            'role' => 'customer',
            'city_id' => $city2->id,
            'district_id' => $district2A->id,
            'created_at' => $today->copy()->day(5)->setTime(9, 0),
        ]);
        $user5 = User::factory()->create([
            'role' => 'mitra',
            'city_id' => $city2->id,
            'district_id' => $district2A->id,
            'created_at' => $today->copy()->day(20)->setTime(11, 0),
        ]);
        $user6 = User::factory()->create([
            'role' => 'customer',
            'city_id' => $city2->id,
            'district_id' => $district2A->id,
            'created_at' => $today->copy()->day(25)->setTime(16, 0),
        ]);

        $this->actingAs($superAdmin);

        // 4. Test Nationwide (Default)
        // Total users = superadmin (1) + city1 (3) + city2 (3) = 7
        $test = Livewire::test(\App\Livewire\SuperAdmin\Dashboard\Index::class);
        $chartDataAll = $test->get('userChart');

        $this->assertNotEmpty($chartDataAll['daily']['labels']);
        $this->assertNotEmpty($chartDataAll['monthly']['labels']);
        $this->assertNotEmpty($chartDataAll['yearly']['labels']);

        // Sum of all daily data should equal total users created this month (superadmin + 6 users = 7)
        $totalDailyAll = array_sum($chartDataAll['daily']['data']);
        $this->assertEquals(7, $totalDailyAll);

        // 5. Test Filter by City 1 (Yogyakarta)
        $superAdmin->setActiveSuperadminTerritory('city', $city1->id);
        $test->dispatch('superadmin-territory-changed', [
            'type' => 'city',
            'id' => $city1->id,
        ])->assertDispatched('users-chart-updated');

        $chartDataCity1 = $test->get('userChart');
        $totalDailyCity1 = array_sum($chartDataCity1['daily']['data']);
        // City 1 has 3 users (user1, user2, user3)
        $this->assertEquals(3, $totalDailyCity1);

        // 6. Test Filter by District 1A (Gondokusuman)
        $superAdmin->setActiveSuperadminTerritory('district', $district1A->id);
        $test->dispatch('superadmin-territory-changed', [
            'type' => 'district',
            'id' => $district1A->id,
        ])->assertDispatched('users-chart-updated');

        $chartDataDist1A = $test->get('userChart');
        $totalDailyDist1A = array_sum($chartDataDist1A['daily']['data']);
        // District 1A has 2 users (user1, user2)
        $this->assertEquals(2, $totalDailyDist1A);

        // 7. Test Filter by City 2 (Sleman)
        $superAdmin->setActiveSuperadminTerritory('city', $city2->id);
        $test->dispatch('superadmin-territory-changed', [
            'type' => 'city',
            'id' => $city2->id,
        ])->assertDispatched('users-chart-updated');

        $chartDataCity2 = $test->get('userChart');
        $totalDailyCity2 = array_sum($chartDataCity2['daily']['data']);
        // City 2 has 3 users (user4, user5, user6)
        $this->assertEquals(3, $totalDailyCity2);

        // 8. Test Reset to All (Semua Wilayah) via resetGlobalTerritory action
        $test->call('resetGlobalTerritory')
            ->assertDispatched('superadmin-territory-changed');

        $chartDataReset = $test->get('userChart');
        $totalDailyReset = array_sum($chartDataReset['daily']['data']);
        $this->assertEquals(7, $totalDailyReset);

        $freshTerritory = $superAdmin->fresh()->getActiveSuperadminTerritory();
        $this->assertEquals('all', $freshTerritory['type']);
    }
}
