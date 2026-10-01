<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Help;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\DashboardStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $mitra;
    protected User $customer;
    protected City $city;
    protected DashboardStatsService $statsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::create([
            'name'      => 'Sleman',
            'latitude'  => -7.7156,
            'longitude' => 110.3556,
        ]);

        $this->mitra = User::factory()->create([
            'role'     => 'mitra',
            'city_id'  => $this->city->id,
            'verified' => true,
            'status'   => 'active',
        ]);
        UserBalance::create(['user_id' => $this->mitra->id, 'balance' => 150000]);

        $this->customer = User::factory()->create([
            'role'    => 'customer',
            'city_id' => $this->city->id,
        ]);

        $this->statsService = new DashboardStatsService();
    }

    public function test_get_summary_stats_aggregates_and_caches_correctly()
    {
        // 1 pool help available
        Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->city->id,
            'title'          => 'Tersedia 1',
            'description'    => 'Pool',
            'amount'         => 50000,
            'admin_fee'      => 5000,
            'total_amount'   => 55000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_POOL,
        ]);

        // 1 in-progress help
        Help::create([
            'user_id'        => $this->customer->id,
            'mitra_id'       => $this->mitra->id,
            'city_id'        => $this->city->id,
            'title'          => 'In Progress 1',
            'description'    => 'Progress',
            'amount'         => 70000,
            'admin_fee'      => 7000,
            'total_amount'   => 77000,
            'status'         => Help::STATUS_IN_PROGRESS,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
        ]);

        $stats = $this->statsService->getSummaryStats($this->mitra);

        $this->assertEquals(150000, $stats['balance']);
        $this->assertEquals(1, $stats['available']);
        $this->assertEquals(1, $stats['inProgress']);
        $this->assertEquals(0, $stats['completed']);

        // Check cached value
        $this->assertTrue(Cache::has("mitra_dash_stats_{$this->mitra->id}"));

        // Test clear cache
        $this->statsService->clearStatsCache($this->mitra->id, $this->mitra->city_id);
        $this->assertFalse(Cache::has("mitra_dash_stats_{$this->mitra->id}"));
    }

    public function test_get_recommended_and_latest_helps()
    {
        Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->city->id,
            'title'          => 'Sleman Help',
            'description'    => 'Near',
            'amount'         => 30000,
            'admin_fee'      => 3000,
            'total_amount'   => 33000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_POOL,
        ]);

        $recommended = $this->statsService->getRecommendedHelps($this->mitra);
        $this->assertCount(1, $recommended);
        $this->assertEquals('Sleman Help', $recommended->first()->title);

        $latest = $this->statsService->getLatestHelps($this->mitra);
        $this->assertCount(1, $latest);
    }
}
