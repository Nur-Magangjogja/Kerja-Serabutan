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

        // Seed a help in 2025-11 to create operational history in 2025
        $this->createHelp([
            'city_id'     => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
            'status'      => Help::STATUS_SELESAI,
            'created_at'  => '2025-11-10 10:00:00',
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(AdminDashboard::class);

        $availableMonths = $component->viewData('availableMonths');
        $availableYears = $component->viewData('availableYears');
        $monthsByYear = $component->viewData('monthsByYear');

        $this->assertIsArray($availableMonths);
        $this->assertIsArray($availableYears);
        $this->assertContains(2026, $availableYears);
        $this->assertContains(2025, $availableYears);

        $this->assertIsArray($monthsByYear);
        $this->assertArrayHasKey(2026, $monthsByYear);
        $this->assertArrayHasKey(2025, $monthsByYear);

        // Current month check (2026-10) is active
        $this->assertArrayHasKey('2026-10', $availableMonths);
        $this->assertEquals('2026-10', $availableMonths['2026-10']['key']);
        $this->assertStringContainsString('Oktober 2026', $availableMonths['2026-10']['label']);
        $this->assertStringContainsString('Bulan Ini', $availableMonths['2026-10']['label']);

        // Operational month check (2025-11) is present
        $this->assertArrayHasKey('2025-11', $availableMonths);
        $this->assertEquals('2025-11', $availableMonths['2025-11']['key']);
        $this->assertStringContainsString('November 2025', $availableMonths['2025-11']['label']);

        // Unstarted / empty month before operational start (e.g. 2025-01) is omitted
        $this->assertArrayNotHasKey('2025-01', $availableMonths);
        $this->assertArrayNotHasKey('2024-05', $availableMonths);

        // Check HTML contains Year Selector, Whole-year selection & Operational months
        $html = $component->html();
        $this->assertStringContainsString('Semua Periode', $html);
        $this->assertStringContainsString('Pilih Tahun', $html);
        $this->assertStringContainsString('Tahun 2026', $html);
        $this->assertStringContainsString('Tahun 2025', $html);
        $this->assertStringContainsString('Lihat Seluruh Tahun 2026', $html);
        $this->assertStringContainsString('Lihat Seluruh Tahun 2025', $html);
        $this->assertStringContainsString('Bulan Operasional (2026)', $html);
        $this->assertStringContainsString('Bulan Operasional (2025)', $html);
        $this->assertStringContainsString('Bulan Ini (Oktober 2026)', $html);
        $this->assertStringContainsString('Oktober 2026', $html);
        $this->assertStringContainsString('November 2025', $html);
        $this->assertStringContainsString("setMonth('2026-10')", $html);
        $this->assertStringContainsString("setMonth('2025-11')", $html);
    }

    /**
     * Test: Whole year selection aggregates all months for that year,
     * formats periodLabel to Tahun YYYY (Semua Bulan), and handles prev/next year stepping.
     */
    public function test_admin_dashboard_full_year_selection_aggregates_entire_year(): void
    {
        Carbon::setTestNow('2026-10-15 10:00:00');

        $admin = User::factory()->create([
            'role' => 'admin',
            'city_id' => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
        ]);
        $admin->managedDistricts()->sync([$this->distGondomanan->id]);

        // 3 helps in different months of 2026
        $this->createHelp([
            'city_id'     => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
            'status'      => Help::STATUS_SELESAI,
            'created_at'  => '2026-03-10 10:00:00',
        ]);
        $this->createHelp([
            'city_id'     => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
            'status'      => Help::STATUS_SELESAI,
            'created_at'  => '2026-07-20 14:00:00',
        ]);
        $this->createHelp([
            'city_id'     => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
            'status'      => Help::STATUS_SELESAI,
            'created_at'  => '2026-10-05 09:00:00',
        ]);

        // 1 help in 2025
        $this->createHelp([
            'city_id'     => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
            'status'      => Help::STATUS_SELESAI,
            'created_at'  => '2025-11-15 11:00:00',
        ]);

        $this->actingAs($admin);

        // 1. Select full year 2026 via setYear('2026')
        $component2026 = Livewire::test(AdminDashboard::class)
            ->call('setYear', '2026');

        $this->assertEquals('year-2026', $component2026->get('selectedMonth'));
        $this->assertEquals('Tahun 2026 (Semua Bulan)', $component2026->viewData('periodLabel'));
        $this->assertEquals(3, $component2026->viewData('totalHelps'));
        $this->assertEquals(3, $component2026->viewData('completedHelps'));

        // 2. Select full year 2025 via setYear('2025')
        $component2025 = Livewire::test(AdminDashboard::class)
            ->call('setYear', '2025');

        $this->assertEquals('year-2025', $component2025->get('selectedMonth'));
        $this->assertEquals('Tahun 2025 (Semua Bulan)', $component2025->viewData('periodLabel'));
        $this->assertEquals(1, $component2025->viewData('totalHelps'));
        $this->assertEquals(1, $component2025->viewData('completedHelps'));

        // 3. Step backward in year mode via prevMonth()
        $component2025->call('prevMonth');
        $this->assertEquals('year-2024', $component2025->get('selectedMonth'));
        $this->assertEquals('Tahun 2024 (Semua Bulan)', $component2025->viewData('periodLabel'));
        $this->assertEquals(0, $component2025->viewData('totalHelps'));

        // 4. Step forward back to 2025
        $component2025->call('nextMonth');
        $this->assertEquals('year-2025', $component2025->get('selectedMonth'));
        $this->assertEquals(1, $component2025->viewData('totalHelps'));
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

    /**
     * Test 6: Multi-year toggle dropdown rendering and balanced layout:
     * - Popover has balanced dimensions decoupled from button width (w-[22rem] sm:w-[26rem] md:w-[28rem]).
     * - Toggle button has stable width (w-[210px] sm:w-[230px]).
     * - Multiple registered years (2026 down to 2020) are available and rendered as tab buttons.
     * - Operational months are laid out in a 2-column grid (grid grid-cols-2).
     * - Years without operational history show the clean empty state message.
     */
    public function test_admin_dashboard_toggle_dropdown_multi_year_display_and_layout(): void
    {
        Carbon::setTestNow('2026-10-15 10:00:00');

        $admin = User::factory()->create([
            'role' => 'admin',
            'city_id' => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
        ]);
        $admin->managedDistricts()->sync([$this->distGondomanan->id]);

        // Seed 1 help in 2025
        $this->createHelp([
            'city_id'     => $this->cityJogja->id,
            'district_id' => $this->distGondomanan->id,
            'status'      => Help::STATUS_SELESAI,
            'created_at'  => '2025-08-10 10:00:00',
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(AdminDashboard::class);

        $availableYears = $component->viewData('availableYears');
        $this->assertIsArray($availableYears);

        // Check multi-year availability: 2026, 2025, 2024, 2023, 2022, 2021, 2020
        $expectedYears = [2026, 2025, 2024, 2023, 2022, 2021, 2020];
        foreach ($expectedYears as $yr) {
            $this->assertContains($yr, $availableYears, "availableYears should contain year {$yr}");
        }

        $html = $component->html();

        // 1. Responsive popover, stable toggle button, and overflow classes
        $this->assertStringContainsString('sm:w-[230px]', $html);
        $this->assertStringContainsString('sm:w-[26rem] md:w-[28rem]', $html);
        $this->assertStringContainsString('grid grid-cols-1 sm:grid-cols-2 gap-1.5', $html);
        $this->assertStringContainsString('overscroll-x-contain', $html);
        $this->assertStringContainsString('overflow-y-auto', $html);

        // 2. All year tabs rendered in HTML
        foreach ($expectedYears as $yr) {
            $this->assertStringContainsString("Tahun {$yr}", $html);
            $this->assertStringContainsString("Lihat Seluruh Tahun {$yr}", $html);
        }

        // 3. Operational months segregation
        $monthsByYear = $component->viewData('monthsByYear');
        $this->assertNotEmpty($monthsByYear[2026]);
        $this->assertNotEmpty($monthsByYear[2025]);
        $this->assertEmpty($monthsByYear[2023]);
        $this->assertEmpty($monthsByYear[2022]);
        $this->assertEmpty($monthsByYear[2021]);
        $this->assertEmpty($monthsByYear[2020]);

        // 4. Empty state notice rendered for unoperated years
        $this->assertStringContainsString('Belum ada riwayat layanan operasional pada tahun ini.', $html);
    }
}
