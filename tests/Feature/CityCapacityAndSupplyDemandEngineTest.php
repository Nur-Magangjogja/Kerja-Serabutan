<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\City;
use App\Models\CityCapacity;
use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Services\PartnerOnlineService;
use App\Services\SupplyDemandService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityCapacityAndSupplyDemandEngineTest extends TestCase
{
    use RefreshDatabase;

    protected City $city;
    protected User $admin;
    protected User $customer;
    protected SupplyDemandService $supplyDemandService;
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

        $this->admin = User::factory()->create([
            'role'    => 'admin',
            'city_id' => $this->city->id,
        ]);

        $this->customer = User::factory()->create([
            'role'    => 'customer',
            'city_id' => $this->city->id,
        ]);

        $this->onlineService       = app(PartnerOnlineService::class);
        $this->supplyDemandService = app(SupplyDemandService::class);
    }

    public function test_calculate_city_metrics_gathers_accurate_supply_and_demand_counts()
    {
        // 1. Mitra searching
        $mitra1 = User::factory()->create(['role' => 'mitra', 'city_id' => $this->city->id]);
        $this->onlineService->startSearching($mitra1, -7.7956, 110.3695);

        // 2. Mitra busy
        $mitra2 = User::factory()->create(['role' => 'mitra', 'city_id' => $this->city->id]);
        $this->onlineService->startSearching($mitra2, -7.7956, 110.3695);
        $help = Help::create([
            'user_id'      => $this->customer->id,
            'city_id'      => $this->city->id,
            'title'        => 'Bantuan Aktif',
            'description'  => 'Sedang dikerjakan',
            'amount'       => 50000,
            'admin_fee'    => 5000,
            'total_amount' => 55000,
            'status'       => Help::STATUS_TAKEN,
        ]);
        $this->onlineService->setBusy($mitra2->id, $help->id);

        // 3. Unmatched demand help
        Help::create([
            'user_id'      => $this->customer->id,
            'city_id'      => $this->city->id,
            'title'        => 'Bantuan Mencari Mitra',
            'description'  => 'Menunggu',
            'amount'       => 40000,
            'admin_fee'    => 4000,
            'total_amount' => 44000,
            'status'       => Help::STATUS_MENUNGGU_MITRA,
        ]);

        $metrics = $this->supplyDemandService->calculateCityMetrics($this->city);

        $this->assertEquals(1, $metrics['searching_now']);
        $this->assertEquals(1, $metrics['busy_now']);
        $this->assertEquals(1, $metrics['current_unmatched_demand']);
        $this->assertEquals(50.0, $metrics['partner_utilization_rate']); // 1 busy / 2 total active = 50%
    }

    public function test_evaluate_capacity_transitions_open_to_limited_to_closed_on_persistent_oversupply()
    {
        // Setup oversupply: 5 searching mitras, 0 busy, 0 unmatched demand -> utilization 0%
        for ($i = 0; $i < 5; $i++) {
            $m = User::factory()->create(['role' => 'mitra', 'city_id' => $this->city->id]);
            $this->onlineService->startSearching($m, -7.7956, 110.3695);
        }

        // 1st Evaluation: OPEN -> LIMITED (1x evaluation trigger)
        $cap1 = $this->supplyDemandService->evaluateCapacity($this->city);
        $this->assertEquals(CityCapacity::STATUS_LIMITED, $cap1->capacity_status);
        $this->assertEquals(1, $cap1->consecutive_closed_evaluations);

        // 2nd Evaluation: LIMITED -> CLOSED (2x persistent oversupply trigger)
        $cap2 = $this->supplyDemandService->evaluateCapacity($this->city);
        $this->assertEquals(CityCapacity::STATUS_CLOSED, $cap2->capacity_status);
        $this->assertEquals(2, $cap2->consecutive_closed_evaluations);
        $this->assertTrue($cap2->isClosed());
        $this->assertFalse($cap2->canRegisterNewPartners());
    }

    public function test_evaluate_capacity_transitions_closed_to_limited_to_open_on_persistent_high_demand()
    {
        $capacity = CityCapacity::create([
            'city_id'                        => $this->city->id,
            'capacity_status'                => CityCapacity::STATUS_CLOSED,
            'consecutive_closed_evaluations' => 2,
            'auto_manage'                    => true,
        ]);

        // Create high demand: Help created 20 minutes ago and still waiting
        $help = Help::create([
            'user_id'      => $this->customer->id,
            'city_id'      => $this->city->id,
            'title'        => 'Bantuan Darurat',
            'description'  => 'Tunggu lama',
            'amount'       => 50000,
            'admin_fee'    => 5000,
            'total_amount' => 55000,
            'status'       => Help::STATUS_MENUNGGU_MITRA,
        ]);
        Help::where('id', $help->id)->update(['created_at' => now()->subMinutes(20)]);

        // 1st Evaluation on high demand: CLOSED -> LIMITED (1x evaluation)
        $cap1 = $this->supplyDemandService->evaluateCapacity($this->city);
        $this->assertEquals(CityCapacity::STATUS_LIMITED, $cap1->capacity_status);
        $this->assertEquals(1, $cap1->consecutive_open_evaluations);

        // 2nd Evaluation on high demand: LIMITED -> OPEN (2x evaluation)
        $cap2 = $this->supplyDemandService->evaluateCapacity($this->city);
        $this->assertEquals(CityCapacity::STATUS_OPEN, $cap2->capacity_status);
        $this->assertEquals(2, $cap2->consecutive_open_evaluations);
        $this->assertTrue($cap2->isOpen());
        $this->assertTrue($cap2->canRegisterNewPartners());
    }

    public function test_admin_override_takes_precedence_over_auto_evaluation_until_expiry()
    {
        // City capacity is CLOSED automatically
        $capacity = CityCapacity::create([
            'city_id'         => $this->city->id,
            'capacity_status' => CityCapacity::STATUS_CLOSED,
            'auto_manage'     => true,
        ]);

        // Admin forces override to OPEN for next 24 hours
        $this->supplyDemandService->setAdminOverride(
            $this->city,
            $this->admin,
            CityCapacity::STATUS_OPEN,
            now()->addHours(24),
            'Acara festival kota butuh banyak mitra'
        );

        $capacity->refresh();
        $this->assertEquals(CityCapacity::STATUS_OPEN, $capacity->getEffectiveStatus());
        $this->assertTrue($capacity->isOpen());
        $this->assertTrue($capacity->canRegisterNewPartners());

        // Even after auto-evaluation runs, override still rules
        $evaluated = $this->supplyDemandService->evaluateCapacity($this->city);
        $this->assertEquals(CityCapacity::STATUS_OPEN, $evaluated->getEffectiveStatus());

        // Clear override
        $this->supplyDemandService->clearAdminOverride($this->city);
        $capacity->refresh();
        $this->assertNull($capacity->admin_override_status);
        $this->assertEquals(CityCapacity::STATUS_CLOSED, $capacity->getEffectiveStatus());
    }

    public function test_evaluate_all_cities_command_processes_all_active_cities()
    {
        $this->artisan('city:evaluate-capacities')
            ->expectsOutputToContain('Berhasil mengevaluasi kapasitas')
            ->assertExitCode(0);

        $this->assertDatabaseHas('city_capacities', [
            'city_id' => $this->city->id,
        ]);
    }
}
