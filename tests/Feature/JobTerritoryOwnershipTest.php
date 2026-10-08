<?php

namespace Tests\Feature;

use App\Livewire\Admin\Helps\Index as AdminHelpsIndex;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\Province;
use App\Models\User;
use App\Services\Territory\AdminTerritoryAuthorizationService;
use App\Services\Territory\ProfileTerritoryMigrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JobTerritoryOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected Province $provinceDIY;
    protected City $cityA;
    protected District $distA1;
    protected District $distA2;

    protected City $cityB;
    protected District $distB1;

    protected City $cityC;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provinceDIY = Province::create([
            'name'      => 'DI Yogyakarta',
            'code'      => '34',
            'is_active' => true,
        ]);

        // City A (Kota Yogyakarta) with distA1 (Danurejan) & distA2 (Gondomanan)
        $this->cityA = City::create([
            'name'        => 'Kota Yogyakarta',
            'province_id' => $this->provinceDIY->id,
            'province'    => 'DI Yogyakarta',
            'is_active'   => true,
        ]);
        $this->distA1 = District::create(['city_id' => $this->cityA->id, 'name' => 'Danurejan', 'is_active' => true]);
        $this->distA2 = District::create(['city_id' => $this->cityA->id, 'name' => 'Gondomanan', 'is_active' => true]);

        // City B (Kabupaten Sleman) with distB1 (Depok)
        $this->cityB = City::create([
            'name'        => 'Kabupaten Sleman',
            'province_id' => $this->provinceDIY->id,
            'province'    => 'DI Yogyakarta',
            'is_active'   => true,
        ]);
        $this->distB1 = District::create(['city_id' => $this->cityB->id, 'name' => 'Depok', 'is_active' => true]);

        // City C (Kota Semarang)
        $this->cityC = City::create([
            'name'      => 'Kota Semarang',
            'is_active' => true,
        ]);

        $this->superAdmin = User::factory()->create([
            'role'   => 'super_admin',
            'status' => 'active',
        ]);
    }

    /**
     * Helper to create a valid Help fixture.
     */
    protected function createHelp(array $attributes = []): Help
    {
        $customer = $attributes['customer'] ?? User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
        ]);

        return Help::create(array_merge([
            'user_id'      => $customer->id,
            'city_id'      => $this->cityA->id,
            'district_id'  => $this->distA1->id,
            'title'        => 'Pekerjaan di Wilayah A1',
            'description'  => 'Deskripsi order pekerjaan',
            'amount'       => 50000,
            'total_amount' => 50000,
            'status'       => Help::STATUS_MENUNGGU_MITRA,
        ], array_diff_key($attributes, ['customer' => true])));
    }

    /**
     * J1: Help A owned by Admin territory A.
     */
    public function test_j1_help_a_owned_by_admin_territory_a(): void
    {
        $adminA = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminA->managedDistricts()->sync([$this->distA1->id]);

        $helpA = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
            'title'       => 'Help Kanonik Danurejan',
        ]);

        Livewire::actingAs($adminA)
            ->test(AdminHelpsIndex::class)
            ->assertSee($helpA->title)
            ->assertSee($helpA->order_id);
    }

    /**
     * J2: Customer profile B + Help A: Admin B tidak memperoleh Help.
     */
    public function test_j2_customer_profile_b_with_help_a_denies_admin_b(): void
    {
        $adminB = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminB->managedDistricts()->sync([$this->distB1->id]);

        // Customer has profile in Sleman / Depok (B1)
        $customerB = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'city_id'     => $this->cityB->id,
            'district_id' => $this->distB1->id,
        ]);

        // But Help is located in Yogyakarta / Danurejan (A1)
        $helpA = $this->createHelp([
            'customer'    => $customerB,
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
            'title'       => 'Order Yogyakarta dari Akun Sleman',
        ]);

        // Admin B must NOT see Help A despite Customer B's home profile being in Sleman
        Livewire::actingAs($adminB)
            ->test(AdminHelpsIndex::class)
            ->assertDontSee($helpA->title);
    }

    /**
     * J3: Mitra profile C + Help A: Admin C tidak memperoleh Help.
     */
    public function test_j3_mitra_profile_c_with_help_a_denies_admin_c(): void
    {
        $adminC = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminC->managedCities()->sync([$this->cityC->id]);

        $adminA = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminA->managedDistricts()->sync([$this->distA1->id]);

        // Mitra has profile in Semarang (City C)
        $mitraC = User::factory()->create([
            'role'        => 'mitra',
            'status'      => 'active',
            'city_id'     => $this->cityC->id,
            'district_id' => null,
        ]);

        // Help is in Danurejan (A1), and Mitra C takes the order
        $helpA = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
            'mitra_id'    => $mitraC->id,
            'status'      => Help::STATUS_TAKEN,
            'title'       => 'Help Danurejan Diambil Mitra Semarang',
        ]);

        // Admin C must NOT see the Help
        Livewire::actingAs($adminC)
            ->test(AdminHelpsIndex::class)
            ->assertDontSee($helpA->title);

        // Admin A MUST see the Help
        Livewire::actingAs($adminA)
            ->test(AdminHelpsIndex::class)
            ->assertSee($helpA->title);
    }

    /**
     * J4: Profile migration tidak mengubah job ownership.
     */
    public function test_j4_profile_migration_does_not_change_job_ownership(): void
    {
        $adminA = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminA->managedDistricts()->sync([$this->distA1->id]);

        $adminB = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminB->managedDistricts()->sync([$this->distB1->id]);

        $customer = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
        ]);

        $helpA = $this->createHelp([
            'customer'    => $customer,
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
            'title'       => 'Help Pra Migrasi Domisili',
        ]);

        // Migrate customer identity profile to City B / District B1
        $migrationService = app(ProfileTerritoryMigrationService::class);
        $migrationService->migrate(
            $this->superAdmin,
            $customer,
            $this->cityB->id,
            $this->distB1->id,
            'Pindah domisili resmi'
        );

        $this->assertEquals($this->cityB->id, $customer->fresh()->city_id);
        $this->assertEquals($this->distB1->id, $customer->fresh()->district_id);

        // Help territory MUST remain Danurejan (City A, District A1)
        $this->assertEquals($this->cityA->id, $helpA->fresh()->city_id);
        $this->assertEquals($this->distA1->id, $helpA->fresh()->district_id);

        // Admin A still owns and sees the Help
        Livewire::actingAs($adminA)
            ->test(AdminHelpsIndex::class)
            ->assertSee($helpA->title);

        // Admin B does NOT see the Help
        Livewire::actingAs($adminB)
            ->test(AdminHelpsIndex::class)
            ->assertDontSee($helpA->title);
    }

    /**
     * J5: GPS movement tidak mengubah job ownership.
     */
    public function test_j5_gps_movement_does_not_change_job_ownership(): void
    {
        $adminA = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminA->managedDistricts()->sync([$this->distA1->id]);

        $adminB = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminB->managedDistricts()->sync([$this->distB1->id]);

        $mitra = User::factory()->create([
            'role'        => 'mitra',
            'status'      => 'active',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
        ]);

        $helpA = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
            'mitra_id'    => $mitra->id,
            'status'      => Help::STATUS_IN_PROGRESS,
            'title'       => 'Help Berjalan Danurejan',
        ]);

        // Partner moves GPS location to City B (Sleman)
        PartnerOnlineState::updateOrCreate(
            ['user_id' => $mitra->id],
            [
                'latitude'         => -7.7156,
                'longitude'        => 110.3556,
                'is_online'        => true,
                'operational_mode' => 'busy',
                'active_city_id'   => $this->cityB->id,
            ]
        );

        // Help territory record is unaffected
        $this->assertEquals($this->cityA->id, $helpA->fresh()->city_id);
        $this->assertEquals($this->distA1->id, $helpA->fresh()->district_id);

        // Admin A retains ownership
        Livewire::actingAs($adminA)
            ->test(AdminHelpsIndex::class)
            ->assertSee($helpA->title);

        // Admin B does NOT get ownership
        Livewire::actingAs($adminB)
            ->test(AdminHelpsIndex::class)
            ->assertDontSee($helpA->title);
    }

    /**
     * J6: District-level Admin melihat Help district-nya.
     */
    public function test_j6_district_level_admin_sees_own_district_help(): void
    {
        $adminA1 = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminA1->managedDistricts()->sync([$this->distA1->id]);

        $helpA1 = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
            'title'       => 'Order Khusus Danurejan',
        ]);

        Livewire::actingAs($adminA1)
            ->test(AdminHelpsIndex::class)
            ->assertSee($helpA1->title);
    }

    /**
     * J7: District-level Admin tidak melihat district lain.
     */
    public function test_j7_district_level_admin_does_not_see_other_district(): void
    {
        $adminA1 = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminA1->managedDistricts()->sync([$this->distA1->id]);

        // Help located in Gondomanan (A2) in the same city
        $helpA2 = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA2->id,
            'title'       => 'Order Khusus Gondomanan',
        ]);

        Livewire::actingAs($adminA1)
            ->test(AdminHelpsIndex::class)
            ->assertDontSee($helpA2->title);
    }

    /**
     * J8: Admin with assigned district in City A sees city-level Help in City A, while orphan city admin does NOT see it.
     */
    public function test_j8_explicit_city_admin_sees_city_level_help(): void
    {
        $adminA = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminA->managedDistricts()->sync([$this->distA1->id]);

        $orphanCityAdmin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $orphanCityAdmin->managedCities()->sync([$this->cityA->id]);

        // City-level Help without district
        $cityHelp = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => null,
            'title'       => 'Help Level Kota Yogyakarta',
        ]);

        // Admin A (assigned Danurejan in City A) sees city-level help in City A
        Livewire::actingAs($adminA)
            ->test(AdminHelpsIndex::class)
            ->assertSee($cityHelp->title);

        // Orphan city admin has zero authority -> does NOT see city-level Help
        Livewire::actingAs($orphanCityAdmin)
            ->test(AdminHelpsIndex::class)
            ->assertDontSee($cityHelp->title);
    }

    /**
     * J9: Admin outside City A (e.g. Admin B1 in Sleman) does not see City A city-level Help.
     */
    public function test_j9_district_only_admin_does_not_see_city_level_help(): void
    {
        // Admin has assignment to Depok (City B) only
        $distAdminB1 = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $distAdminB1->managedDistricts()->sync([$this->distB1->id]);

        // Help has city_id = City A, district_id = null
        $cityHelp = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => null,
            'title'       => 'Help Level Kota Tanpa Kecamatan',
        ]);

        Livewire::actingAs($distAdminB1)
            ->test(AdminHelpsIndex::class)
            ->assertDontSee($cityHelp->title);
    }

    /**
     * J10: SuperAdmin melihat semua.
     */
    public function test_j10_superadmin_sees_all_helps(): void
    {
        $helpA1 = $this->createHelp(['city_id' => $this->cityA->id, 'district_id' => $this->distA1->id, 'title' => 'Help Danurejan']);
        $helpA2 = $this->createHelp(['city_id' => $this->cityA->id, 'district_id' => $this->distA2->id, 'title' => 'Help Gondomanan']);
        $helpB1 = $this->createHelp(['city_id' => $this->cityB->id, 'district_id' => $this->distB1->id, 'title' => 'Help Depok Sleman']);
        $helpCity = $this->createHelp(['city_id' => $this->cityA->id, 'district_id' => null, 'title' => 'Help Kota Global']);

        Livewire::actingAs($this->superAdmin)
            ->test(AdminHelpsIndex::class)
            ->assertSee($helpA1->title)
            ->assertSee($helpA2->title)
            ->assertSee($helpB1->title)
            ->assertSee($helpCity->title);
    }

    /**
     * J11: Direct access di luar Help territory ditolak.
     */
    public function test_j11_direct_access_outside_help_territory_denied(): void
    {
        $adminB = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminB->managedDistricts()->sync([$this->distB1->id]);

        $helpA = $this->createHelp([
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
            'status'      => Help::STATUS_MENUNGGU_MITRA,
        ]);

        // 1. viewHelp outside territory denied
        Livewire::actingAs($adminB)
            ->test(AdminHelpsIndex::class)
            ->call('viewHelp', $helpA->id)
            ->assertSee('Anda tidak memiliki wewenang untuk melihat bantuan di luar wilayah Anda.')
            ->assertSet('selectedHelpId', null)
            ->assertSet('showDetailModal', false);

        // 2. approveHelp outside territory denied
        Livewire::actingAs($adminB)
            ->test(AdminHelpsIndex::class)
            ->call('approveHelp', $helpA->id)
            ->assertSee('Anda tidak memiliki wewenang untuk menyetujui bantuan di luar wilayah Anda.');

        // Status unchanged
        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $helpA->fresh()->status);

        // 3. rejectHelp outside territory denied
        Livewire::actingAs($adminB)
            ->test(AdminHelpsIndex::class)
            ->call('rejectHelp', $helpA->id)
            ->assertSee('Anda tidak memiliki wewenang untuk menolak bantuan di luar wilayah Anda.');

        // Status still unchanged
        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $helpA->fresh()->status);
    }

    /**
     * J12: Historical Help tetap original territory.
     */
    public function test_j12_historical_help_retains_original_territory(): void
    {
        $adminA = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminA->managedDistricts()->sync([$this->distA1->id]);

        $adminB = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminB->managedDistricts()->sync([$this->distB1->id]);

        $customer = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->distA1->id,
        ]);

        // Create completed historical order in A1
        $historicalHelp = $this->createHelp([
            'customer'     => $customer,
            'city_id'      => $this->cityA->id,
            'district_id'  => $this->distA1->id,
            'status'       => Help::STATUS_SELESAI,
            'completed_at' => now()->subMonths(3),
            'title'        => 'Historical Help Selesai 3 Bulan Lalu',
        ]);

        // Customer relocates to B
        $customer->update([
            'city_id'     => $this->cityB->id,
            'district_id' => $this->distB1->id,
        ]);

        // Filtering by completed status
        Livewire::actingAs($adminA)
            ->test(AdminHelpsIndex::class)
            ->set('statusFilter', 'completed')
            ->assertSee($historicalHelp->title);

        Livewire::actingAs($adminB)
            ->test(AdminHelpsIndex::class)
            ->set('statusFilter', 'completed')
            ->assertDontSee($historicalHelp->title);
    }
}
