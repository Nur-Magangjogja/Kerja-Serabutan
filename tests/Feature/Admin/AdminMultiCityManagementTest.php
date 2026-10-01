<?php

namespace Tests\Feature\Admin;

use App\Models\City;
use App\Models\Help;
use App\Models\Registration;
use App\Models\User;
use App\Models\BalanceTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminMultiCityManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_assign_multiple_cities_to_admin_and_admin_only_sees_assigned_cities()
    {
        // Create 3 cities
        $cityYogya = City::create(['name' => 'Kota Yogyakarta', 'state_name' => 'DIY', 'latitude' => -7.7956, 'longitude' => 110.3695]);
        $citySleman = City::create(['name' => 'Kabupaten Sleman', 'state_name' => 'DIY', 'latitude' => -7.7156, 'longitude' => 110.3556]);
        $cityBantul = City::create(['name' => 'Kabupaten Bantul', 'state_name' => 'DIY', 'latitude' => -7.8890, 'longitude' => 110.3289]);

        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        // Create Admin user
        $admin = User::factory()->create([
            'role' => 'admin',
            'city_id' => $cityYogya->id,
        ]);

        // Super Admin assigns Sleman as well (2 cities: Yogya & Sleman)
        $this->actingAs($superAdmin);
        $admin->managedCities()->sync([$cityYogya->id, $citySleman->id]);

        $adminCityIds = $admin->getAdminCityIds();
        $this->assertCount(2, $adminCityIds);
        $this->assertContains($cityYogya->id, $adminCityIds);
        $this->assertContains($citySleman->id, $adminCityIds);
        $this->assertNotContains($cityBantul->id, $adminCityIds);

        // Create customers in all 3 cities
        $customerYogya = User::factory()->create(['role' => 'customer', 'city_id' => $cityYogya->id]);
        $customerSleman = User::factory()->create(['role' => 'customer', 'city_id' => $citySleman->id]);
        $customerBantul = User::factory()->create(['role' => 'customer', 'city_id' => $cityBantul->id]);

        // Create helps in all 3 cities
        $helpYogya = Help::create([
            'user_id' => $customerYogya->id,
            'customer_id' => $customerYogya->id,
            'city_id' => $cityYogya->id,
            'title' => 'Bantuan di Yogya',
            'description' => 'Deskripsi bantuan Yogya',
            'amount' => 50000,
            'total_amount' => 50000,
            'status' => 'menunggu_mitra',
        ]);

        $helpSleman = Help::create([
            'user_id' => $customerSleman->id,
            'customer_id' => $customerSleman->id,
            'city_id' => $citySleman->id,
            'title' => 'Bantuan di Sleman',
            'description' => 'Deskripsi bantuan Sleman',
            'amount' => 60000,
            'total_amount' => 60000,
            'status' => 'menunggu_mitra',
        ]);

        $helpBantul = Help::create([
            'user_id' => $customerBantul->id,
            'customer_id' => $customerBantul->id,
            'city_id' => $cityBantul->id,
            'title' => 'Bantuan di Bantul',
            'description' => 'Deskripsi bantuan Bantul',
            'amount' => 70000,
            'total_amount' => 70000,
            'status' => 'menunggu_mitra',
        ]);

        // Now test as the Admin
        $this->actingAs($admin);

        // 1. Dashboard test: admin sees Yogya and Sleman (Total 2 helps, not Bantul)
        Livewire::test(\App\Livewire\Admin\Dashboard\Index::class)
            ->assertViewHas('totalHelps', 2)
            ->assertViewHas('pendingHelps', 2)
            ->assertSee('Bantuan di Yogya')
            ->assertSee('Bantuan di Sleman')
            ->assertDontSee('Bantuan di Bantul')
            // Switch city to Sleman only
            ->call('setCityFilter', (string)$citySleman->id)
            ->assertSet('selectedCity', (string)$citySleman->id)
            ->assertViewHas('totalHelps', 1)
            ->assertSee('Bantuan di Sleman')
            ->assertDontSee('Bantuan di Yogya');

        // 2. Helps Moderation test: admin sees Yogya & Sleman helps, but cannot see Bantul
        Livewire::test(\App\Livewire\Admin\Helps\Index::class)
            ->assertViewHas('totalHelps', 2)
            ->assertSee('Bantuan di Yogya')
            ->assertSee('Bantuan di Sleman')
            ->assertDontSee('Bantuan di Bantul')
            // Filter by Yogya city
            ->set('cityFilter', (string)$cityYogya->id)
            ->assertViewHas('totalHelps', 1)
            ->assertSee('Bantuan di Yogya')
            ->assertDontSee('Bantuan di Sleman');

        // 3. Verifications test:
        $regYogya = Registration::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Pendaftar Yogya',
            'full_name' => 'Pendaftar Yogya',
            'email' => 'yogya@test.com',
            'city_id' => $cityYogya->id,
            'role' => 'mitra',
            'status' => 'pending_verification',
        ]);

        $regBantul = Registration::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Pendaftar Bantul',
            'full_name' => 'Pendaftar Bantul',
            'email' => 'bantul@test.com',
            'city_id' => $cityBantul->id,
            'role' => 'mitra',
            'status' => 'pending_verification',
        ]);

        Livewire::test(\App\Livewire\Admin\Verifications\Index::class)
            ->assertSee('Pendaftar Yogya')
            ->assertDontSee('Pendaftar Bantul');

        // 4. Top-Up Approval test:
        $topupYogya = BalanceTransaction::create([
            'user_id' => $customerYogya->id,
            'request_code' => 'TU-YOGYA-001',
            'type' => 'topup',
            'amount' => 100000,
            'status' => 'waiting_approval',
        ]);

        $topupBantul = BalanceTransaction::create([
            'user_id' => $customerBantul->id,
            'request_code' => 'TU-BANTUL-001',
            'type' => 'topup',
            'amount' => 200000,
            'status' => 'waiting_approval',
        ]);

        Livewire::test(\App\Livewire\Admin\Topup\Approval::class)
            ->assertSee('TU-YOGYA-001')
            ->assertDontSee('TU-BANTUL-001');
    }
}
