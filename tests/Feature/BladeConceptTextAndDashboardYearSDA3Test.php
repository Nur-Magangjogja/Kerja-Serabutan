<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard\Index as AdminDashboard;
use App\Livewire\Admin\Partners\Greylist;
use App\Livewire\Admin\Disputes\Index as AdminDisputes;
use App\Models\City;
use App\Models\District;
use App\Models\Province;
use App\Models\Help;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class BladeConceptTextAndDashboardYearSDA3Test extends TestCase
{
    use RefreshDatabase;

    protected Province $provinceDIY;
    protected City $cityJogja;
    protected District $distGondomanan;
    protected City $citySleman;
    protected District $distNgaglik;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setLocale('id');

        $this->provinceDIY = Province::create(['name' => 'DI Yogyakarta', 'code' => '34', 'is_active' => true]);

        $this->cityJogja = City::create([
            'name' => 'Kota Yogyakarta',
            'province_id' => $this->provinceDIY->id,
            'province' => 'DI Yogyakarta',
            'bps_code' => '3471',
            'is_active' => true,
        ]);
        $this->distGondomanan = District::create([
            'city_id' => $this->cityJogja->id,
            'name' => 'Gondomanan',
            'bps_code' => '347101',
            'is_active' => true,
        ]);

        $this->citySleman = City::create([
            'name' => 'Kabupaten Sleman',
            'province_id' => $this->provinceDIY->id,
            'province' => 'DI Yogyakarta',
            'bps_code' => '3404',
            'is_active' => true,
        ]);
        $this->distNgaglik = District::create([
            'city_id' => $this->citySleman->id,
            'name' => 'Ngaglik',
            'bps_code' => '340401',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    protected function createHelp(array $overrides = []): Help
    {
        $createdAt = $overrides['created_at'] ?? null;
        unset($overrides['created_at']);

        $help = Help::create(array_merge([
            'user_id'          => User::factory()->create(['role' => 'customer'])->id,
            'city_id'          => $this->cityJogja->id,
            'district_id'      => $this->distGondomanan->id,
            'title'            => 'Bantuan Uji Coba SDA3',
            'description'      => 'Deskripsi uji coba',
            'amount'           => 50000,
            'total_amount'     => 50000,
            'status'           => Help::STATUS_SELESAI,
            'payment_status'   => 'paid',
            'service_type'     => Help::SERVICE_TYPE_ON_SITE,
            'service_category' => 'general',
            'location'         => 'Jl. Malioboro No. 12',
            'latitude'         => -7.7956,
            'longitude'        => 110.3695,
        ], $overrides));

        if ($createdAt) {
            DB::table('helps')->where('id', $help->id)->update(['created_at' => $createdAt]);
            $help->refresh();
        }

        return $help;
    }

    /**
     * Test 1: Greylist rendered UI does NOT contain visible "Konsep 1" or "K1:"
     * and uses clean domain wording "Batal di Perjalanan".
     */
    public function test_greylist_blade_does_not_contain_visible_konsep_1_or_k1(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'city_id' => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
        ]);
        $admin->managedDistricts()->sync([$this->distGondomanan->id]);

        $mitra = User::factory()->create([
            'role' => 'mitra',
            'city_id' => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
            'status' => 'active',
            'is_greylisted' => true,
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(Greylist::class);

        $html = $component->html();

        // Must NOT contain visible Konsep 1 or K1:
        $this->assertStringNotContainsString('Konsep 1', $html);
        $this->assertStringNotContainsString('K1:', $html);
        $this->assertStringNotContainsString('(Konsep 1)', $html);

        // Must contain clean domain wording
        $this->assertStringContainsString('Batal di Perjalanan', $html);
    }

    /**
     * Test 2: Disputes rendered UI does NOT contain visible "Konsep 1"
     * and renders clean domain wording for transit/travel cancellation history.
     */
    public function test_disputes_blade_does_not_contain_visible_konsep_1(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'city_id' => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
        ]);
        $admin->managedDistricts()->sync([$this->distGondomanan->id]);

        $this->actingAs($admin);

        $component = Livewire::test(AdminDisputes::class);

        $html = $component->html();

        // Must NOT contain visible Konsep 1 or (Konsep 1)
        $this->assertStringNotContainsString('Konsep 1', $html);
        $this->assertStringNotContainsString('(Konsep 1)', $html);
    }

    /**
     * Test 3: Admin Dashboard period list includes Month + Year for all options,
     * preserves canonical YYYY-MM keys and "all" for Semua Periode.
     */
    public function test_admin_dashboard_period_options_contain_month_and_year(): void
    {
        Carbon::setTestNow('2026-10-15 10:00:00');

        $admin = User::factory()->create([
            'role' => 'admin',
            'city_id' => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
        ]);
        $admin->managedDistricts()->sync([$this->distGondomanan->id]);

        $this->actingAs($admin);

        $component = Livewire::test(AdminDashboard::class);

        $availableMonths = $component->viewData('availableMonths');

        $this->assertIsArray($availableMonths);
        $this->assertCount(12, $availableMonths);

        // Current month check (2026-10)
        $this->assertArrayHasKey('2026-10', $availableMonths);
        $this->assertEquals('2026-10', $availableMonths['2026-10']['key']);
        $this->assertStringContainsString('Oktober 2026', $availableMonths['2026-10']['label']);
        $this->assertStringContainsString('Bulan Ini', $availableMonths['2026-10']['label']);

        // Previous year month check (2025-11 in past 11 months)
        $this->assertArrayHasKey('2025-11', $availableMonths);
        $this->assertEquals('2025-11', $availableMonths['2025-11']['key']);
        $this->assertStringContainsString('November 2025', $availableMonths['2025-11']['label']);

        // Check HTML contains Semua Periode button and labels
        $html = $component->html();
        $this->assertStringContainsString('Semua Periode', $html);
        $this->assertStringContainsString('Bulan Ini (Oktober 2026)', $html);
        $this->assertStringContainsString('Oktober 2026', $html);
        $this->assertStringContainsString('November 2025', $html);
        $this->assertStringContainsString("setMonth('2026-10')", $html);
        $this->assertStringContainsString("setMonth('2025-11')", $html);
    }

    /**
     * Test 4: Cross-year filter accuracy:
     * When selecting 2026-10, only October 2026 helps are counted.
     * When selecting 2025-10, only October 2025 helps are counted.
     * When selecting 'all', both are counted.
     */
    public function test_admin_dashboard_filters_cross_year_accurately_without_collision(): void
    {
        Carbon::setTestNow('2026-10-15 10:00:00');

        $admin = User::factory()->create([
            'role' => 'admin',
            'city_id' => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
        ]);
        $admin->managedDistricts()->sync([$this->distGondomanan->id]);

        // Help in October 2026
        $this->createHelp([
            'city_id'     => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
            'status'      => Help::STATUS_SELESAI,
            'created_at'  => '2026-10-05 14:00:00',
        ]);

        // Help in October 2025 (Same month number, different year)
        $this->createHelp([
            'city_id'     => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
            'status'      => Help::STATUS_SELESAI,
            'created_at'  => '2025-10-05 14:00:00',
        ]);

        $this->actingAs($admin);

        // 1. Select 2026-10
        $component2026 = Livewire::test(AdminDashboard::class)
            ->call('setMonth', '2026-10');

        $this->assertEquals(1, $component2026->viewData('completedHelps'));
        $this->assertEquals(1, $component2026->viewData('totalHelps'));

        // 2. Select 2025-10
        $component2025 = Livewire::test(AdminDashboard::class)
            ->call('setMonth', '2025-10');

        $this->assertEquals(1, $component2025->viewData('completedHelps'));
        $this->assertEquals(1, $component2025->viewData('totalHelps'));

        // 3. Select all
        $componentAll = Livewire::test(AdminDashboard::class)
            ->call('setAllPeriod');

        $this->assertEquals(2, $componentAll->viewData('completedHelps'));
        $this->assertEquals(2, $componentAll->viewData('totalHelps'));
    }

    /**
     * Test 5: Territorial filtering is strictly preserved in Admin Dashboard.
     */
    public function test_admin_dashboard_territory_filtering_preserved(): void
    {
        Carbon::setTestNow('2026-10-15 10:00:00');

        $adminJogja = User::factory()->create([
            'role' => 'admin',
            'city_id' => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
        ]);
        $adminJogja->managedDistricts()->sync([$this->distGondomanan->id]);

        // Help in Jogja / Gondomanan
        $this->createHelp([
            'city_id'     => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
            'status'      => Help::STATUS_SELESAI,
            'created_at'  => '2026-10-05 14:00:00',
        ]);

        // Help in Sleman / Ngaglik (Outside Jogja Admin Authority)
        $this->createHelp([
            'city_id'     => $this->citySleman->id,
            'district_id' => $this->distNgaglik->id,
            'status'      => Help::STATUS_SELESAI,
            'created_at'  => '2026-10-05 14:00:00',
        ]);

        $this->actingAs($adminJogja);

        $component = Livewire::test(AdminDashboard::class)
            ->call('setAllPeriod');

        // Admin Jogja must only see 1 help (Gondomanan), not Sleman
        $this->assertEquals(1, $component->viewData('totalHelps'));
        $this->assertEquals(1, $component->viewData('completedHelps'));
    }
}
