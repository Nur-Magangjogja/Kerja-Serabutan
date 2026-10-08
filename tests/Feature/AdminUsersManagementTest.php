<?php

namespace Tests\Feature;

use App\Livewire\SuperAdmin\Users\AdminUsers;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUsersManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_create_new_admin()
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'nik' => '3201010000000001',
            'password' => Hash::make('password123'),
        ]);

        $city1 = City::create(['name' => 'Sleman', 'province' => 'DI Yogyakarta']);
        $city2 = City::create(['name' => 'Bantul', 'province' => 'DI Yogyakarta']);

        Livewire::actingAs($superAdmin)
            ->test(AdminUsers::class)
            ->call('openCreateModal')
            ->set('name', 'Admin Sleman')
            ->set('email', 'admin.sleman@sayabantu.com')
            ->set('phone', '081234567890')
            ->set('password', 'secret1234')
            ->set('status', 'active')
            ->set('verified', true)
            ->set('managed_city_ids', [$city1->id, $city2->id])
            ->call('saveUser')
            ->assertHasNoErrors();

        $newAdmin = User::where('email', 'admin.sleman@sayabantu.com')->first();
        $this->assertNotNull($newAdmin);
        $this->assertEquals('Admin Sleman', $newAdmin->name);
        $this->assertEquals('admin', $newAdmin->role);
        $this->assertEquals('active', $newAdmin->status);
        $this->assertCount(2, $newAdmin->managedCities);
    }

    public function test_superadmin_can_update_existing_admin_with_password()
    {
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'nik' => '3201010000000001',
            'password' => Hash::make('superadminpass'),
        ]);

        $city1 = City::create(['name' => 'Kota Yogyakarta', 'province' => 'DI Yogyakarta']);
        $admin = User::factory()->create([
            'name' => 'Old Admin Name',
            'email' => 'old.admin@sayabantu.com',
            'nik' => '3201010000000002',
            'role' => 'admin',
            'status' => 'active',
        ]);

        Livewire::actingAs($superAdmin)
            ->test(AdminUsers::class)
            ->call('editUser', $admin->id)
            ->set('name', 'Updated Admin Name')
            ->set('managed_city_ids', [$city1->id])
            ->set('adminPassword', 'superadminpass')
            ->call('saveUser')
            ->assertHasNoErrors();

        $admin->refresh();
        $this->assertEquals('Updated Admin Name', $admin->name);
        $this->assertCount(1, $admin->managedCities);
    }
}
