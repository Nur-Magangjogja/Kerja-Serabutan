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

    public function test_w3_a_mitra_profile_a_with_gps_b_counts_supply_in_b_not_a()
    {
        $cityB = City::create([
            'name'       => 'Kota Surakarta',
            'state_name' => 'Jawa Tengah',
            'is_active'  => true,
            'latitude'   => -7.5755,
            'longitude'  => 110.8243,
        ]);

        // Mitra profile di City A (Yogyakarta)
        $mitra = User::factory()->create([
            'role'    => 'mitra',
            'city_id' => $this->city->id,
            'status'  => 'active',
        ]);

        // Tapi runtime GPS di City B (Surakarta)
        $this->onlineService->startSearching($mitra, -7.5755, 110.8243);

        $metricsA = $this->supplyDemandService->calculateCityMetrics($this->city);
        $metricsB = $this->supplyDemandService->calculateCityMetrics($cityB);

        $this->assertEquals(0, $metricsA['searching_now'], 'Supply City A tidak boleh bertambah karena mitra tidak secara fisik di City A');
        $this->assertEquals(0, $metricsA['online_total']);
        $this->assertEquals(1, $metricsB['searching_now'], 'Supply City B wajib bertambah karena mitra ber-GPS di City B');
        $this->assertEquals(1, $metricsB['online_total']);
    }

    public function test_w3_b_mitra_moves_gps_a_to_b_moves_supply_without_double_counting()
    {
        $cityB = City::create([
            'name'       => 'Kota Surakarta',
            'state_name' => 'Jawa Tengah',
            'is_active'  => true,
            'latitude'   => -7.5755,
            'longitude'  => 110.8243,
        ]);

        $mitra = User::factory()->create(['role' => 'mitra', 'city_id' => $this->city->id]);

        // 1. Awalnya di City A
        $this->onlineService->startSearching($mitra, -7.7956, 110.3695);
        $metricsA1 = $this->supplyDemandService->calculateCityMetrics($this->city);
        $metricsB1 = $this->supplyDemandService->calculateCityMetrics($cityB);
        $this->assertEquals(1, $metricsA1['searching_now']);
        $this->assertEquals(0, $metricsB1['searching_now']);

        // 2. Mitra pindah ke City B melalui heartbeat endpoint
        $this->actingAs($mitra);
        $this->postJson(route('mitra.heartbeat'), [
            'latitude'  => -7.5755,
            'longitude' => 110.8243,
        ])->assertStatus(200);

        $metricsA2 = $this->supplyDemandService->calculateCityMetrics($this->city);
        $metricsB2 = $this->supplyDemandService->calculateCityMetrics($cityB);

        $this->assertEquals(0, $metricsA2['searching_now'], 'Supply City A berkurang setelah mitra berpindah');
        $this->assertEquals(1, $metricsB2['searching_now'], 'Supply City B bertambah setelah mitra berpindah');
        $this->assertEquals(1, $metricsA2['searching_now'] + $metricsB2['searching_now'], 'Tidak boleh terjadi double count supply');
    }

    public function test_w3_c_mitra_stale_heartbeat_not_counted_as_supply()
    {
        $cityB = City::create([
            'name'       => 'Kota Surakarta',
            'state_name' => 'Jawa Tengah',
            'is_active'  => true,
            'latitude'   => -7.5755,
            'longitude'  => 110.8243,
        ]);

        $mitra = User::factory()->create(['role' => 'mitra', 'city_id' => $this->city->id]);
        $this->onlineService->startSearching($mitra, -7.5755, 110.8243);

        // Buat heartbeat menjadi stale (melebihi TTL)
        $ttl = AppSetting::getHeartbeatTtlSeconds();
        PartnerOnlineState::where('user_id', $mitra->id)->update([
            'last_seen_at' => now()->subSeconds($ttl + 60),
        ]);

        $metricsB = $this->supplyDemandService->calculateCityMetrics($cityB);
        $metricsA = $this->supplyDemandService->calculateCityMetrics($this->city);

        $this->assertEquals(0, $metricsB['searching_now'], 'Mitra dengan stale heartbeat tidak boleh dihitung sebagai supply');
        $this->assertEquals(0, $metricsB['online_total']);
        $this->assertEquals(0, $metricsA['searching_now'], 'Dilarang fallback ke profil domisili');
    }

    public function test_w3_d_mitra_missing_gps_not_counted_as_supply()
    {
        $mitra = User::factory()->create(['role' => 'mitra', 'city_id' => $this->city->id]);

        PartnerOnlineState::create([
            'user_id'         => $mitra->id,
            'matching_status' => PartnerOnlineState::STATUS_SEARCHING,
            'latitude'        => null,
            'longitude'       => null,
            'last_seen_at'    => now(),
        ]);

        $metrics = $this->supplyDemandService->calculateCityMetrics($this->city);

        $this->assertEquals(0, $metrics['searching_now'], 'Mitra tanpa runtime GPS valid tidak boleh dihitung sebagai supply');
        $this->assertEquals(0, $metrics['online_total']);
    }

    public function test_w3_e_customer_profile_a_with_help_map_b_counts_demand_in_b_not_a()
    {
        $cityB = City::create([
            'name'       => 'Kota Surakarta',
            'state_name' => 'Jawa Tengah',
            'is_active'  => true,
            'latitude'   => -7.5755,
            'longitude'  => 110.8243,
        ]);

        // Customer berprofil di City A ($this->city)
        $customer = User::factory()->create(['role' => 'customer', 'city_id' => $this->city->id]);

        // Buat order dengan koordinat/city di City B
        Help::create([
            'user_id'      => $customer->id,
            'city_id'      => $cityB->id,
            'latitude'     => -7.5755,
            'longitude'    => 110.8243,
            'title'        => 'Pesanan di Solo',
            'description'  => 'Map di Solo',
            'amount'       => 50000,
            'admin_fee'    => 5000,
            'total_amount' => 55000,
            'status'       => Help::STATUS_MENUNGGU_MITRA,
        ]);

        $metricsA = $this->supplyDemandService->calculateCityMetrics($this->city);
        $metricsB = $this->supplyDemandService->calculateCityMetrics($cityB);

        $this->assertEquals(0, $metricsA['current_unmatched_demand'], 'Demand City A tidak boleh terpengaruh profil customer');
        $this->assertEquals(1, $metricsB['current_unmatched_demand'], 'Demand City B bertambah sesuai lokasi map pesanan');
    }

    public function test_w3_f_help_with_null_district_in_city_b_counts_as_city_demand()
    {
        $cityB = City::create([
            'name'       => 'Kota Surakarta',
            'state_name' => 'Jawa Tengah',
            'is_active'  => true,
            'latitude'   => -7.5755,
            'longitude'  => 110.8243,
        ]);

        Help::create([
            'user_id'      => $this->customer->id,
            'city_id'      => $cityB->id,
            'district_id'  => null, // City-level demand
            'latitude'     => -7.5755,
            'longitude'    => 110.8243,
            'title'        => 'Pesanan City Level',
            'description'  => 'District null',
            'amount'       => 40000,
            'admin_fee'    => 4000,
            'total_amount' => 44000,
            'status'       => Help::STATUS_MENUNGGU_MITRA,
        ]);

        $metricsB = $this->supplyDemandService->calculateCityMetrics($cityB);
        $this->assertEquals(1, $metricsB['current_unmatched_demand'], 'Pesanan city-level dengan district null wajib terhitung pada demand kota');
    }

    public function test_w3_g_change_profile_mitra_while_gps_unchanged_does_not_affect_supply()
    {
        $cityB = City::create([
            'name'       => 'Kota Surakarta',
            'state_name' => 'Jawa Tengah',
            'is_active'  => true,
            'latitude'   => -7.5755,
            'longitude'  => 110.8243,
        ]);

        $cityC = City::create([
            'name'       => 'Kota Semarang',
            'state_name' => 'Jawa Tengah',
            'is_active'  => true,
            'latitude'   => -6.9667,
            'longitude'  => 110.4167,
        ]);

        $mitra = User::factory()->create(['role' => 'mitra', 'city_id' => $this->city->id]);
        $this->onlineService->startSearching($mitra, -7.5755, 110.8243); // GPS di City B

        $this->assertEquals(1, $this->supplyDemandService->calculateCityMetrics($cityB)['searching_now']);

        // Mitra ubah kota profil pendaftaran ke City C
        $mitra->city_id = $cityC->id;
        $mitra->save();

        // Supply harus tetap di City B (karena GPS tidak berubah), dan City C tidak bertambah
        $metricsB = $this->supplyDemandService->calculateCityMetrics($cityB);
        $metricsC = $this->supplyDemandService->calculateCityMetrics($cityC);

        $this->assertEquals(1, $metricsB['searching_now'], 'Supply City B harus tetap 1 karena posisi GPS tidak berubah');
        $this->assertEquals(0, $metricsC['searching_now'], 'Supply City C tidak boleh berubah karena perubahan profil semata');
    }

    public function test_w3_h_change_profile_customer_after_order_created_does_not_affect_demand()
    {
        $cityB = City::create([
            'name'       => 'Kota Surakarta',
            'state_name' => 'Jawa Tengah',
            'is_active'  => true,
            'latitude'   => -7.5755,
            'longitude'  => 110.8243,
        ]);

        $cityC = City::create([
            'name'       => 'Kota Semarang',
            'state_name' => 'Jawa Tengah',
            'is_active'  => true,
            'latitude'   => -6.9667,
            'longitude'  => 110.4167,
        ]);

        $customer = User::factory()->create(['role' => 'customer', 'city_id' => $this->city->id]);

        Help::create([
            'user_id'      => $customer->id,
            'city_id'      => $cityB->id,
            'title'        => 'Pesanan Tetap di Solo',
            'description'  => 'Map di Solo',
            'amount'       => 50000,
            'admin_fee'    => 5000,
            'total_amount' => 55000,
            'status'       => Help::STATUS_MENUNGGU_MITRA,
        ]);

        $this->assertEquals(1, $this->supplyDemandService->calculateCityMetrics($cityB)['current_unmatched_demand']);

        // Customer ubah profil ke City C
        $customer->city_id = $cityC->id;
        $customer->save();

        $metricsB = $this->supplyDemandService->calculateCityMetrics($cityB);
        $metricsC = $this->supplyDemandService->calculateCityMetrics($cityC);

        $this->assertEquals(1, $metricsB['current_unmatched_demand'], 'Demand City B tetap 1');
        $this->assertEquals(0, $metricsC['current_unmatched_demand'], 'Demand City C tetap 0');
    }

    public function test_w3_i_city_capacity_evaluation_uses_operational_supply_and_demand()
    {
        $cityB = City::create([
            'name'       => 'Kota Surakarta',
            'state_name' => 'Jawa Tengah',
            'is_active'  => true,
            'latitude'   => -7.5755,
            'longitude'  => 110.8243,
        ]);

        // Buat 5 mitra dengan profil di City A ($this->city), tapi semua GPS di City B
        for ($i = 0; $i < 5; $i++) {
            $m = User::factory()->create(['role' => 'mitra', 'city_id' => $this->city->id]);
            $this->onlineService->startSearching($m, -7.5755, 110.8243);
        }

        // Evaluasi City A: supply 0, demand 0 -> status tetap OPEN (tidak ada oversupply di A)
        $capA = $this->supplyDemandService->evaluateCapacity($this->city);
        $this->assertEquals(0, $capA->searching_now);
        $this->assertEquals(CityCapacity::STATUS_OPEN, $capA->capacity_status);

        // Evaluasi City B: 5 searching mitras, 0 demand -> oversupply trigger di City B (OPEN -> LIMITED)
        $capB = $this->supplyDemandService->evaluateCapacity($cityB);
        $this->assertEquals(5, $capB->searching_now);
        $this->assertEquals(CityCapacity::STATUS_LIMITED, $capB->capacity_status);
    }

    public function test_w3_j_outside_service_area_gps_is_not_counted_as_supply_and_no_profile_fallback()
    {
        // Mitra berprofil di City A (Yogyakarta)
        $mitra = User::factory()->create([
            'role'    => 'mitra',
            'city_id' => $this->city->id,
            'status'  => 'active',
        ]);

        // Wilayah luar jangkauan (inactive city)
        $outsideCity = City::create([
            'name'       => 'Wilayah Luar Jangkauan',
            'is_active'  => false,
            'latitude'   => -10.5000,
            'longitude'  => 105.0000,
        ]);

        // Partner memiliki GPS di luar wilayah layanan aktif
        PartnerOnlineState::updateOrCreate(
            ['user_id' => $mitra->id],
            [
                'matching_status' => PartnerOnlineState::STATUS_SEARCHING,
                'latitude'        => -10.5000,
                'longitude'       => 105.0000,
                'last_seen_at'    => now(),
            ]
        );

        $metrics = $this->supplyDemandService->calculateCityMetrics($this->city);

        $this->assertEquals(0, $metrics['searching_now'], 'Mitra di luar wilayah operasional tidak boleh dihitung sebagai supply City A');
        $this->assertEquals(0, $metrics['busy_now']);
        $this->assertEquals(0, $metrics['online_total']);
    }

    public function test_w3_k_mitra_gps_in_inactive_city_is_not_counted_as_active_operational_supply()
    {
        // City B nonaktif
        $cityB = City::create([
            'name'       => 'Kota Magelang (Nonaktif)',
            'state_name' => 'Jawa Tengah',
            'is_active'  => false,
            'latitude'   => -7.4705,
            'longitude'  => 110.2178,
        ]);

        $mitra = User::factory()->create([
            'role'    => 'mitra',
            'city_id' => $this->city->id,
            'status'  => 'active',
        ]);

        // Mitra berposisi GPS tepat di City B (nonaktif)
        PartnerOnlineState::updateOrCreate(
            ['user_id' => $mitra->id],
            [
                'matching_status' => PartnerOnlineState::STATUS_SEARCHING,
                'latitude'        => -7.4705,
                'longitude'       => 110.2178,
                'last_seen_at'    => now(),
            ]
        );

        $metricsA = $this->supplyDemandService->calculateCityMetrics($this->city);
        $metricsB = $this->supplyDemandService->calculateCityMetrics($cityB);

        $this->assertEquals(0, $metricsA['searching_now'], 'Mitra di kota lain tidak dihitung di City A');
        $this->assertEquals(0, $metricsB['searching_now'], 'Kota nonaktif tidak memiliki supply operasional aktif');
        $this->assertEquals(0, $metricsB['online_total']);
    }

    public function test_w3_l_mitra_movement_outside_territory_reduces_supply_to_zero()
    {
        $outsideCity = City::create([
            'name'       => 'Wilayah Luar Jangkauan',
            'is_active'  => false,
            'latitude'   => -10.5000,
            'longitude'  => 105.0000,
        ]);

        $mitra = User::factory()->create(['role' => 'mitra', 'city_id' => $this->city->id]);

        // 1. Awalnya di City A
        $this->onlineService->startSearching($mitra, -7.7956, 110.3695);
        $metrics1 = $this->supplyDemandService->calculateCityMetrics($this->city);
        $this->assertEquals(1, $metrics1['searching_now']);

        // 2. Mitra bergerak keluar wilayah operasional melalui heartbeat
        $this->actingAs($mitra);
        $this->postJson(route('mitra.heartbeat'), [
            'latitude'  => -10.5000,
            'longitude' => 105.0000,
        ])->assertStatus(200);

        $metrics2 = $this->supplyDemandService->calculateCityMetrics($this->city);
        $this->assertEquals(0, $metrics2['searching_now'], 'Supply City A berkurang menjadi 0 saat mitra keluar wilayah');
        $this->assertEquals(0, $metrics2['online_total']);
    }

    public function test_w3_m_dashboard_resolver_returns_null_when_partner_gps_is_missing_or_invalid()
    {
        $mitra = User::factory()->create([
            'role'    => 'mitra',
            'city_id' => $this->city->id,
        ]);

        // Partner GPS tidak valid / kosong (0.0, 0.0)
        PartnerOnlineState::updateOrCreate(
            ['user_id' => $mitra->id],
            [
                'matching_status' => PartnerOnlineState::STATUS_SEARCHING,
                'latitude'        => 0.0,
                'longitude'       => 0.0,
                'last_seen_at'    => now(),
            ]
        );

        $statsService = app(\App\Services\DashboardStatsService::class);
        $queryService = app(\App\Services\Dashboard\DashboardQueryService::class);

        // Reflection method protected resolveMitraOperationalCityId
        $refMethod = new \ReflectionMethod($statsService, 'resolveMitraOperationalCityId');
        $refMethod->setAccessible(true);
        $resolvedCityId = $refMethod->invoke($statsService, $mitra);

        $this->assertNull($resolvedCityId, 'Mitra tanpa GPS valid harus me-resolve cityId = null');

        // Dashboard Query Service tidak mengikat order ke kota manapun
        $paginator = $queryService->getHelpListByTab($mitra, 'tersedia');
        $this->assertNotNull($paginator);
    }

    public function test_w3_n_partner_gps_greater_than_10km_from_centroid_remains_valid_in_city_supply()
    {
        // City A aktif
        $mitra = User::factory()->create([
            'role'    => 'mitra',
            'city_id' => $this->city->id,
            'status'  => 'active',
        ]);

        // Partner GPS berjarak ~16.2 KM dari centroid City A (-7.7956, 110.3695)
        // Koordinat: -7.6500, 110.3700 (> 10 KM batas matching)
        PartnerOnlineState::updateOrCreate(
            ['user_id' => $mitra->id],
            [
                'matching_status' => PartnerOnlineState::STATUS_SEARCHING,
                'latitude'        => -7.6500,
                'longitude'       => 110.3700,
                'last_seen_at'    => now(),
            ]
        );

        $metrics = $this->supplyDemandService->calculateCityMetrics($this->city);

        // Harus dihitung sebagai supply City A, TIDAK boleh ditolak hanya karena > 10 KM matching radius
        $this->assertEquals(1, $metrics['searching_now'], 'Mitra 16 KM dari pusat kota harus tetap dihitung sebagai supply wilayah sah kota tersebut');
        $this->assertEquals(1, $metrics['online_total']);

        // Pastikan City::findNearest umum tanpa maxDistanceKm juga mengenali kota ini
        $nearest = City::findNearest(-7.6500, 110.3700);
        $this->assertNotNull($nearest);
        $this->assertEquals($this->city->id, $nearest->id);

        // Pastikan Dashboard stats resolver juga me-resolve kota ini
        $statsService = app(\App\Services\DashboardStatsService::class);
        $refMethod = new \ReflectionMethod($statsService, 'resolveMitraOperationalCityId');
        $refMethod->setAccessible(true);
        $this->assertEquals($this->city->id, $refMethod->invoke($statsService, $mitra));
    }

    public function test_w3_o_generic_city_find_nearest_does_not_artificially_bound_global_callers()
    {
        // Partner berjarak 25 KM dari titik pusat City A (-7.7956, 110.3695)
        // Koordinat: -7.5700, 110.3695 (~25 KM utara)
        $nearestUnbounded = City::findNearest(-7.5700, 110.3695);
        $this->assertNotNull($nearestUnbounded, 'City::findNearest() global harus tetap menemukan kota terdekat tanpa batas artificial 10 km');
        $this->assertEquals($this->city->id, $nearestUnbounded->id);

        // Caller dengan batasan matching 10 KM jika dioper secara eksplisit tetap membatasi
        $nearestMatching = City::findNearest(-7.5700, 110.3695, (float) AppSetting::MAX_OPERATIONAL_RADIUS_KM);
        $this->assertNull($nearestMatching, 'Caller matching yang mengoper batas 10 KM eksplisit harus menolak titik > 10 KM');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SECTION 7: CONSISTENCY TEST SUITE (SINGLE SOURCE OF TRUTH TERRITORY)
    // ─────────────────────────────────────────────────────────────────────────

    public function test_w3_consistency_scenario_a_normal_city_a()
    {
        $lat = -7.7956;
        $lng = 110.3695;

        $mitra = User::factory()->create(['role' => 'mitra', 'city_id' => $this->city->id]);

        // 1. Seeking mode resolver
        $seekingCity = City::findNearest($lat, $lng);
        $this->assertEquals($this->city->id, $seekingCity->id);

        // 2. startSearching
        $this->onlineService->startSearching($mitra, $lat, $lng);
        $state = PartnerOnlineState::where('user_id', $mitra->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_SEARCHING, $state->matching_status);

        // 3. Supply calculation
        $metrics = $this->supplyDemandService->calculateCityMetrics($this->city);
        $this->assertEquals(1, $metrics['searching_now']);

        // 4. Dashboard resolver
        $statsService = app(\App\Services\DashboardStatsService::class);
        $refMethod = new \ReflectionMethod($statsService, 'resolveMitraOperationalCityId');
        $refMethod->setAccessible(true);
        $dashCityId = $refMethod->invoke($statsService, $mitra);
        $this->assertEquals($this->city->id, $dashCityId);

        // Consistency assertion: All = City A
        $this->assertEquals($this->city->id, $seekingCity->id);
        $this->assertEquals($this->city->id, $dashCityId);
    }

    public function test_w3_consistency_scenario_b_greater_than_10km_same_city()
    {
        $lat = -7.6500;
        $lng = 110.3700; // ~16.2 KM utara centroid

        $mitra = User::factory()->create(['role' => 'mitra', 'city_id' => $this->city->id]);

        // 1. Seeking mode resolver
        $seekingCity = City::findNearest($lat, $lng);
        $this->assertEquals($this->city->id, $seekingCity->id);

        // 2. startSearching
        $this->onlineService->startSearching($mitra, $lat, $lng);
        $state = PartnerOnlineState::where('user_id', $mitra->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_SEARCHING, $state->matching_status);

        // 3. Supply calculation
        $metrics = $this->supplyDemandService->calculateCityMetrics($this->city);
        $this->assertEquals(1, $metrics['searching_now']);

        // 4. Dashboard resolver
        $statsService = app(\App\Services\DashboardStatsService::class);
        $refMethod = new \ReflectionMethod($statsService, 'resolveMitraOperationalCityId');
        $refMethod->setAccessible(true);
        $dashCityId = $refMethod->invoke($statsService, $mitra);
        $this->assertEquals($this->city->id, $dashCityId);

        // Consistency assertion: All = City A
        $this->assertEquals($this->city->id, $seekingCity->id);
        $this->assertEquals($this->city->id, $dashCityId);
    }

    public function test_w3_consistency_scenario_c_near_inactive_city_b()
    {
        $cityB = City::create([
            'name'       => 'Kota Magelang (Nonaktif)',
            'state_name' => 'Jawa Tengah',
            'is_active'  => false,
            'latitude'   => -7.4705,
            'longitude'  => 110.2178,
        ]);

        $lat = -7.4705;
        $lng = 110.2178;

        $mitra = User::factory()->create(['role' => 'mitra', 'city_id' => $this->city->id]);

        // 1. Seeking mode resolver
        $seekingCity = City::findNearest($lat, $lng);
        $this->assertEquals($cityB->id, $seekingCity->id, 'Resolver harus menemukan City B terdekat');

        // 2. startSearching -> ditolak karena City B nonaktif
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('sedang ditutup sementara demi keamanan / penataan operasional dan tidak menerima permintaan bantuan baru');
        $this->onlineService->startSearching($mitra, $lat, $lng);
    }

    public function test_w3_consistency_scenario_c_supply_and_dashboard_respect_inactive_city_b()
    {
        $cityB = City::create([
            'name'       => 'Kota Magelang (Nonaktif)',
            'state_name' => 'Jawa Tengah',
            'is_active'  => false,
            'latitude'   => -7.4705,
            'longitude'  => 110.2178,
        ]);

        $lat = -7.4705;
        $lng = 110.2178;

        $mitra = User::factory()->create(['role' => 'mitra', 'city_id' => $this->city->id]);

        PartnerOnlineState::updateOrCreate(
            ['user_id' => $mitra->id],
            [
                'matching_status' => PartnerOnlineState::STATUS_SEARCHING,
                'latitude'        => $lat,
                'longitude'       => $lng,
                'last_seen_at'    => now(),
            ]
        );

        // 3. Supply calculation: City A tidak menyerap supply City B, City B returns 0
        $metricsA = $this->supplyDemandService->calculateCityMetrics($this->city);
        $metricsB = $this->supplyDemandService->calculateCityMetrics($cityB);
        $this->assertEquals(0, $metricsA['searching_now'], 'City A tidak boleh menyerap mitra di City B');
        $this->assertEquals(0, $metricsB['searching_now'], 'City B nonaktif memiliki 0 supply');

        // 4. Dashboard resolver: resolve ke City B (konsisten)
        $statsService = app(\App\Services\DashboardStatsService::class);
        $refMethod = new \ReflectionMethod($statsService, 'resolveMitraOperationalCityId');
        $refMethod->setAccessible(true);
        $dashCityId = $refMethod->invoke($statsService, $mitra);
        $this->assertEquals($cityB->id, $dashCityId, 'Dashboard resolver konsisten mengenali City B');
    }

    public function test_w3_consistency_scenario_d_invalid_gps_rejected_consistently()
    {
        $mitra = User::factory()->create(['role' => 'mitra', 'city_id' => $this->city->id]);

        // 1. Seeking mode dengan GPS 0,0 / null -> fallback ke global (cityId = null)
        $this->assertTrue(AppSetting::isMatchingSeekingEnabledForUser($mitra, 0.0, 0.0));

        // 2. MitraMatchingActions dengan GPS null -> ditolak
        $actionResult = app(\App\Actions\Matching\MitraMatchingActions::class)->startSearching($mitra, null, null);
        $this->assertFalse($actionResult['success']);
        $this->assertStringContainsString('Lokasi GPS tidak tersedia', $actionResult['message']);

        // 3. Direct HelpTransactionService::takeHelp dengan GPS null -> ditolak
        $help = Help::create([
            'user_id'      => $this->customer->id,
            'city_id'      => $this->city->id,
            'title'        => 'Order Uji Konsistensi',
            'description'  => 'Uji GPS',
            'amount'       => 30000,
            'admin_fee'    => 3000,
            'total_amount' => 33000,
            'status'       => Help::STATUS_MENUNGGU_MITRA,
            'latitude'     => -7.7956,
            'longitude'    => 110.3695,
        ]);

        $exceptionThrown = false;
        try {
            app(\App\Services\HelpTransactionService::class)->takeHelp($help, $mitra, null, null);
        } catch (\RuntimeException $e) {
            $exceptionThrown = true;
            $this->assertStringContainsString('Lokasi GPS operasional tidak tersedia', $e->getMessage());
        }
        $this->assertTrue($exceptionThrown);

        // 4. Supply calculation -> mitra tanpa GPS tidak dihitung
        $metrics = $this->supplyDemandService->calculateCityMetrics($this->city);
        $this->assertEquals(0, $metrics['searching_now']);

        // 5. Dashboard resolver -> menghasilkan null
        $statsService = app(\App\Services\DashboardStatsService::class);
        $refMethod = new \ReflectionMethod($statsService, 'resolveMitraOperationalCityId');
        $refMethod->setAccessible(true);
        $dashCityId = $refMethod->invoke($statsService, $mitra);
        $this->assertNull($dashCityId, 'Dashboard resolver harus null untuk GPS invalid');
    }
}
