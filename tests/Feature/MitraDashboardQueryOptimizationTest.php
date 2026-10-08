<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Services\Dashboard\DashboardQueryService;
use App\Services\DashboardStatsService;
use App\Services\PartnerOnlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MitraDashboardQueryOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $mitra;
    protected City $city;

    protected function setUp(): void
    {
        parent::setUp();

        PartnerOnlineService::clearStateCache();

        $this->city = City::create([
            'name'       => 'Kota Yogyakarta',
            'state_name' => 'DI Yogyakarta',
            'latitude'   => -7.7956,
            'longitude'  => 110.3695,
        ]);

        $this->mitra = User::factory()->create([
            'role'                        => 'mitra',
            'city_id'                     => $this->city->id,
            'vehicle_plate_number'        => 'AB 1234 CD',
            'vehicle_sim_number'          => '123456789012',
            'vehicle_sim_photo'           => 'vehicles/sim/photo.jpg',
            'vehicle_stnk_number'         => 'STNK-123456',
            'vehicle_stnk_photo'          => 'vehicles/stnk/photo.jpg',
            'vehicle_verification_status' => 'verified',
            'vehicle_verified'            => true,
        ]);
    }

    public function test_get_or_create_state_memoization_prevents_duplicate_queries()
    {
        $service = app(PartnerOnlineService::class);

        // First call will run initial queries and memoize
        $state1 = $service->getOrCreateState($this->mitra);
        $this->assertNotNull($state1);

        // Count queries on subsequent calls during same lifecycle
        DB::enableQueryLog();
        DB::flushQueryLog();

        $state2 = $service->getOrCreateState($this->mitra);
        $state3 = $service->getOrCreateState($this->mitra->id);

        $queries = DB::getQueryLog();
        $this->assertCount(0, $queries, 'Subsequent getOrCreateState calls should use memoization without executing SQL queries.');
        $this->assertSame($state1->id, $state2->id);
        $this->assertSame($state1->id, $state3->id);
    }

    public function test_get_help_list_by_tab_with_known_total_bypasses_count_query()
    {
        $queryService = app(DashboardQueryService::class);
        $statsService = app(DashboardStatsService::class);

        $customer = User::factory()->create(['role' => 'customer']);

        Help::create([
            'user_id'       => $customer->id,
            'title'         => 'Bantuan Antar Barang',
            'description'   => 'Deskripsi bantuan test',
            'service_type'  => 'pickup_delivery',
            'category'      => 'logistik',
            'status'        => Help::STATUS_MENUNGGU_MITRA,
            'dispatch_mode' => Help::DISPATCH_MODE_POOL,
            'mitra_id'      => null,
            'city_id'       => $this->city->id,
            'latitude'      => -7.7956,
            'longitude'     => 110.3695,
            'price'         => 25000,
            'published_at'  => now()->subMinute(),
        ]);

        $stats = $queryService->getSummaryStats($this->mitra, true);
        $this->assertEquals(1, $stats['available']);

        // Test with knownTotal passed to getHelpListByTab
        DB::enableQueryLog();
        DB::flushQueryLog();

        $paginator = $queryService->getHelpListByTab($this->mitra, 'tersedia', 6, $stats['available']);

        $queries = DB::getQueryLog();
        // Check that NO "count(*)" query is executed in getHelpListByTab
        $countQueries = array_filter($queries, function ($q) {
            return stripos($q['query'], 'count(*)') !== false || stripos($q['query'], 'count(') !== false;
        });

        $this->assertCount(0, $countQueries, 'Paginate with knownTotal must not execute duplicate count query.');
        $this->assertEquals(1, $paginator->total());
        $this->assertCount(1, $paginator->items());
    }
}
