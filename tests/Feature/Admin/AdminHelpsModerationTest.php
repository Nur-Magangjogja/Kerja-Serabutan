<?php

namespace Tests\Feature\Admin;

use App\Models\City;
use App\Models\Help;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminHelpsModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_helps_status_filter_and_statistics_work_properly()
    {
        $city = City::create([
            'name' => 'Yogyakarta',
            'state_name' => 'DIY',
            'latitude' => -7.7956,
            'longitude' => 110.3695,
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'city_id' => $city->id,
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
            'city_id' => $city->id,
        ]);

        // Create helps with different statuses
        $helpPending = Help::create([
            'user_id' => $customer->id,
            'customer_id' => $customer->id,
            'city_id' => $city->id,
            'title' => 'Bantuan Pending',
            'description' => 'Deskripsi pending',
            'amount' => 50000,
            'total_amount' => 50000,
            'status' => 'menunggu_mitra',
        ]);

        $helpActive = Help::create([
            'user_id' => $customer->id,
            'customer_id' => $customer->id,
            'city_id' => $city->id,
            'title' => 'Bantuan Sedang Berjalan',
            'description' => 'Deskripsi aktif',
            'amount' => 75000,
            'total_amount' => 75000,
            'status' => 'in_progress',
        ]);

        $helpCompleted = Help::create([
            'user_id' => $customer->id,
            'customer_id' => $customer->id,
            'city_id' => $city->id,
            'title' => 'Bantuan Selesai',
            'description' => 'Deskripsi selesai',
            'amount' => 100000,
            'total_amount' => 100000,
            'status' => 'selesai',
        ]);

        $helpCancelled = Help::create([
            'user_id' => $customer->id,
            'customer_id' => $customer->id,
            'city_id' => $city->id,
            'title' => 'Bantuan Dibatalkan',
            'description' => 'Deskripsi dibatalkan',
            'amount' => 30000,
            'total_amount' => 30000,
            'status' => 'dibatalkan',
        ]);

        $this->actingAs($admin);

        // Test Livewire component
        Livewire::test(\App\Livewire\Admin\Helps\Index::class)
            ->assertViewHas('totalHelps', 4)
            ->assertViewHas('pendingHelps', 1)
            ->assertViewHas('activeHelps', 1)
            ->assertViewHas('completedHelps', 1)
            ->assertViewHas('cancelledHelps', 1)
            // Filter: Pending
            ->call('filterByStatus', 'pending')
            ->assertSet('statusFilter', 'pending')
            ->assertSee('Bantuan Pending')
            ->assertDontSee('Bantuan Selesai')
            // Filter: Active
            ->call('filterByStatus', 'active')
            ->assertSet('statusFilter', 'active')
            ->assertSee('Bantuan Sedang Berjalan')
            ->assertDontSee('Bantuan Pending')
            // Filter: Completed
            ->call('filterByStatus', 'completed')
            ->assertSet('statusFilter', 'completed')
            ->assertSee('Bantuan Selesai')
            ->assertDontSee('Bantuan Sedang Berjalan')
            // Filter: Cancelled
            ->call('filterByStatus', 'cancelled')
            ->assertSet('statusFilter', 'cancelled')
            ->assertSee('Bantuan Dibatalkan')
            ->assertDontSee('Bantuan Selesai')
            // Filter: All
            ->call('filterByStatus', 'all')
            ->assertSet('statusFilter', '')
            ->assertSee('Bantuan Pending')
            ->assertSee('Bantuan Selesai');
    }

    public function test_admin_helps_approved_route_is_removed()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/admin/helps/approved');
        $response->assertStatus(404);
    }
}
