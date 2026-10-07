<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MigrateLegacyDistrictsDataCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_fails_gracefully_when_districts_table_is_empty_without_silent_seeding()
    {
        // Ensure table is empty
        $this->assertEquals(0, District::count());

        $this->artisan('sayabantu:migrate-districts-data')
            ->expectsOutputToContain('Data kecamatan belum tersedia.')
            ->assertExitCode(Command::FAILURE);

        // Verify that districts table was NOT secretly seeded
        $this->assertEquals(0, District::count());
    }

    public function test_command_migrates_legacy_users_helps_and_admins_successfully()
    {
        // 1. Setup City and Districts
        $city = City::create([
            'name' => 'Kota Yogyakarta',
            'province' => 'DI Yogyakarta',
            'latitude' => -7.7956,
            'longitude' => 110.3695,
        ]);

        $districtDanurejan = District::create([
            'city_id' => $city->id,
            'name' => 'Danurejan',
            'code' => '347101',
            'is_active' => true,
        ]);

        $districtGondomanan = District::create([
            'city_id' => $city->id,
            'name' => 'Gondomanan',
            'code' => '347102',
            'is_active' => true,
        ]);

        // 2. Setup Legacy User (Customer with city_id, but district_id null, kecamatan text 'Danurejan')
        $customer = User::factory()->create([
            'role' => 'customer',
            'city_id' => $city->id,
            'district_id' => null,
            'kecamatan' => 'Danurejan',
        ]);

        // 3. Setup Legacy User (Mitra with city_id, district_id null, address containing 'Gondomanan')
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'city_id' => $city->id,
            'district_id' => null,
            'kecamatan' => null,
            'address' => 'Jl. Malioboro, Gondomanan, Yogyakarta',
        ]);

        // 4. Setup Legacy Help (city_id set, district_id null, location containing 'Danurejan')
        $help = Help::create([
            'user_id' => $customer->id,
            'customer_id' => $customer->id,
            'city_id' => $city->id,
            'district_id' => null,
            'title' => 'Bantuan Antar Barang',
            'description' => 'Tolong antarkan barang',
            'location' => 'Kec. Danurejan',
            'amount' => 50000,
            'total_amount' => 50000,
            'status' => 'menunggu_mitra',
        ]);

        // 5. Setup Admin with city_id, but empty managedDistricts
        $admin = User::factory()->create([
            'role' => 'admin',
            'city_id' => $city->id,
            'district_id' => null,
        ]);

        // Run Command
        $this->artisan('sayabantu:migrate-districts-data')
            ->expectsOutputToContain('=== MIGRASI DATA WILAYAH KE KECAMATAN ===')
            ->expectsOutputToContain('=== MIGRASI DATA WILAYAH KE KECAMATAN SELESAI ===')
            ->assertExitCode(Command::SUCCESS);

        // Verify Customer updated to Danurejan
        $customer->refresh();
        $this->assertEquals($districtDanurejan->id, $customer->district_id);

        // Verify Mitra updated to Gondomanan
        $mitra->refresh();
        $this->assertEquals($districtGondomanan->id, $mitra->district_id);

        // Verify Help updated to Danurejan
        $help->refresh();
        $this->assertEquals($districtDanurejan->id, $help->district_id);

        // Verify Admin with only profile city_id is not auto-expanded to all districts (0 assigned districts = 0 authority)
        $admin->refresh();
        $this->assertCount(0, $admin->managedDistricts);
    }

    public function test_command_dry_run_option_does_not_persist_changes()
    {
        $city = City::create([
            'name' => 'Kota Yogyakarta',
            'province' => 'DI Yogyakarta',
        ]);

        $district = District::create([
            'city_id' => $city->id,
            'name' => 'Danurejan',
            'code' => '347101',
            'is_active' => true,
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
            'city_id' => $city->id,
            'district_id' => null,
            'kecamatan' => 'Danurejan',
        ]);

        $this->artisan('sayabantu:migrate-districts-data', ['--dry-run' => true])
            ->expectsOutputToContain('[DRY-RUN]')
            ->assertExitCode(Command::SUCCESS);

        $customer->refresh();
        $this->assertNull($customer->district_id);
    }
}
