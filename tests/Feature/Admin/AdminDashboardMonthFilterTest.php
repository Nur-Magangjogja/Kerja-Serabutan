<?php

namespace Tests\Feature\Admin;

use App\Models\City;
use App\Models\Help;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardMonthFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_defaults_to_current_month_and_can_filter_previous_month()
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

        // Help in current month
        $help1 = Help::create([
            'user_id' => $customer->id,
            'customer_id' => $customer->id,
            'city_id' => $city->id,
            'title' => 'Bantuan Bulan Ini',
            'description' => 'Deskripsi bantuan',
            'amount' => 50000,
            'total_amount' => 50000,
            'status' => 'selesai',
        ]);
        \DB::table('helps')->where('id', $help1->id)->update(['created_at' => now()]);

        // Help in previous month
        $help2 = Help::create([
            'user_id' => $customer->id,
            'customer_id' => $customer->id,
            'city_id' => $city->id,
            'title' => 'Bantuan Bulan Lalu',
            'description' => 'Deskripsi bantuan',
            'amount' => 60000,
            'total_amount' => 60000,
            'status' => 'selesai',
        ]);
        \DB::table('helps')->where('id', $help2->id)->update(['created_at' => now()->subMonth()->startOfMonth()->addDays(5)]);

        $this->actingAs($admin);

        $currentMonth = now()->format('Y-m');
        $prevMonth = now()->subMonth()->format('Y-m');

        // Test default: current month only 1 help & unified chart datasets exist
        Livewire::test(\App\Livewire\Admin\Dashboard\Index::class)
            ->assertSet('selectedMonth', $currentMonth)
            ->assertViewHas('totalHelps', 1)
            ->assertViewHas('completedHelps', 1)
            ->assertViewHas('chartHelpsData')
            ->assertViewHas('chartCompletedData')
            ->assertViewHas('chartCancelledData')
            ->assertViewHas('chartVerificationsData')
            // Change month using setMonth
            ->call('setMonth', $prevMonth)
            ->assertSet('selectedMonth', $prevMonth)
            ->assertViewHas('totalHelps', 1)
            ->assertViewHas('completedHelps', 1)
            // Change to all periods (accumulation)
            ->call('setAllPeriod')
            ->assertSet('selectedMonth', 'all')
            ->assertViewHas('totalHelps', 2)
            ->assertViewHas('completedHelps', 2)
            // Return to current month
            ->call('setCurrentMonth')
            ->assertSet('selectedMonth', $currentMonth)
            ->assertViewHas('totalHelps', 1);
    }
}
