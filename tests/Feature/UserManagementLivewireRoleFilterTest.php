<?php

namespace Tests\Feature;

use App\Livewire\SuperAdmin\Users\Index;
use App\Models\City;
use App\Models\District;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementLivewireRoleFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_management_only_shows_mitra_and_customer_for_both_superadmin_and_admin()
    {
        $city = City::create(['name' => 'Yogyakarta', 'province' => 'DIY']);
        $district = District::create(['city_id' => $city->id, 'name' => 'Danurejan']);

        $superAdmin = User::factory()->create([
            'name' => 'Main SuperAdmin',
            'role' => 'super_admin',
            'status' => 'active',
            'verified' => true,
            'city_id' => $city->id,
            'district_id' => $district->id,
            'password' => Hash::make('password123'),
        ]);

        $customer = User::factory()->create([
            'name' => 'Budi Customer',
            'email' => 'budi@customer.com',
            'role' => 'customer',
            'status' => 'active',
            'verified' => true,
            'city_id' => $city->id,
            'district_id' => $district->id,
        ]);

        $mitra = User::factory()->create([
            'name' => 'Siti Mitra',
            'email' => 'siti@mitra.com',
            'role' => 'mitra',
            'status' => 'active',
            'verified' => true,
            'city_id' => $city->id,
            'district_id' => $district->id,
        ]);

        $admin = User::factory()->create([
            'name' => 'Joko Admin Wilayah',
            'email' => 'joko@admin.com',
            'role' => 'admin',
            'status' => 'active',
            'verified' => true,
            'city_id' => $city->id,
            'district_id' => $district->id,
        ]);

        // 1. SuperAdmin viewing Manajemen User should ONLY see Mitra and Customer, NEVER Admin/SuperAdmin
        Livewire::actingAs($superAdmin)
            ->test(Index::class)
            ->assertSee('Budi Customer')
            ->assertSee('Siti Mitra')
            ->assertDontSee('Joko Admin Wilayah')
            ->assertDontSee('Main SuperAdmin')
            // 2. Setting roleFilter to customer
            ->set('roleFilter', 'customer')
            ->assertSee('Budi Customer')
            ->assertDontSee('Siti Mitra')
            ->assertDontSee('Joko Admin Wilayah')
            // 3. Setting roleFilter to mitra
            ->set('roleFilter', 'mitra')
            ->assertSee('Siti Mitra')
            ->assertDontSee('Budi Customer')
            ->assertDontSee('Joko Admin Wilayah')
            // 4. Clearing roleFilter shows only customer and mitra
            ->set('roleFilter', '')
            ->assertSee('Budi Customer')
            ->assertSee('Siti Mitra')
            ->assertDontSee('Joko Admin Wilayah');
    }

    public function test_regional_admin_can_only_see_and_filter_mitra_and_customer_in_their_territory()
    {
        $city = City::create(['name' => 'Yogyakarta', 'province' => 'DIY']);
        $district1 = District::create(['city_id' => $city->id, 'name' => 'Danurejan']);
        $district2 = District::create(['city_id' => $city->id, 'name' => 'Gondomanan']);

        $admin = User::factory()->create([
            'name' => 'Admin Wilayah',
            'email' => 'admin.wilayah@sayabantu.com',
            'role' => 'admin',
            'status' => 'active',
            'verified' => true,
            'city_id' => $city->id,
            'district_id' => $district1->id,
            'password' => Hash::make('password123'),
        ]);

        // Attach district1 to admin's managed districts
        $admin->managedDistricts()->attach($district1->id);

        $customerInDistrict1 = User::factory()->create([
            'name' => 'Customer Wilayah 1',
            'email' => 'c1@sayabantu.com',
            'role' => 'customer',
            'status' => 'active',
            'verified' => true,
            'city_id' => $city->id,
            'district_id' => $district1->id,
        ]);

        $mitraInDistrict1 = User::factory()->create([
            'name' => 'Mitra Wilayah 1',
            'email' => 'm1@sayabantu.com',
            'role' => 'mitra',
            'status' => 'active',
            'verified' => true,
            'city_id' => $city->id,
            'district_id' => $district1->id,
        ]);

        $customerInDistrict2 = User::factory()->create([
            'name' => 'Customer Wilayah 2',
            'email' => 'c2@sayabantu.com',
            'role' => 'customer',
            'status' => 'active',
            'verified' => true,
            'city_id' => $city->id,
            'district_id' => $district2->id,
        ]);

        $otherAdmin = User::factory()->create([
            'name' => 'Another Admin',
            'email' => 'another.admin@sayabantu.com',
            'role' => 'admin',
            'status' => 'active',
            'verified' => true,
            'city_id' => $city->id,
            'district_id' => $district1->id,
        ]);

        // Regional admin should see customer and mitra in district1, but NEVER district2 users or other admins
        Livewire::actingAs($admin)
            ->test(Index::class)
            ->assertSee('Customer Wilayah 1')
            ->assertSee('Mitra Wilayah 1')
            ->assertDontSee('Customer Wilayah 2')
            ->assertDontSee('Another Admin')
            // Filter to customer
            ->set('roleFilter', 'customer')
            ->assertSee('Customer Wilayah 1')
            ->assertDontSee('Mitra Wilayah 1')
            // Filter to mitra
            ->set('roleFilter', 'mitra')
            ->assertSee('Mitra Wilayah 1')
            ->assertDontSee('Customer Wilayah 1');
    }

    public function test_view_contains_wire_model_live_and_only_customer_and_mitra_options()
    {
        $city = City::create(['name' => 'Yogyakarta', 'province' => 'DIY']);
        $district = District::create(['city_id' => $city->id, 'name' => 'Danurejan']);

        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'verified' => true,
            'city_id' => $city->id,
            'district_id' => $district->id,
            'password' => Hash::make('password123'),
        ]);

        Livewire::actingAs($superAdmin)
            ->test(Index::class)
            ->assertSeeHtml('wire:model.live="roleFilter"')
            ->assertSeeHtml('wire:model.live="perPage"')
            ->assertSeeHtml('wire:model.live.debounce.400ms="search"')
            ->assertSeeHtml('<option value="customer">Customer</option>')
            ->assertSeeHtml('<option value="mitra">Mitra</option>')
            ->assertDontSeeHtml('<option value="admin">Admin</option>')
            ->assertDontSeeHtml('<option value="super_admin">Super Admin</option>');
    }

    public function test_quick_whatsapp_in_detail_modal_shows_for_user_with_phone()
    {
        $city = City::create(['name' => 'Yogyakarta', 'province' => 'DIY']);
        $district = District::create(['city_id' => $city->id, 'name' => 'Danurejan']);

        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'status' => 'active',
            'verified' => true,
            'city_id' => $city->id,
            'district_id' => $district->id,
            'password' => Hash::make('password123'),
        ]);

        $customer = User::factory()->create([
            'name' => 'Andi Customer',
            'phone' => '081234567890',
            'role' => 'customer',
            'status' => 'active',
            'city_id' => $city->id,
            'district_id' => $district->id,
        ]);

        $mitraNoPhone = User::factory()->create([
            'name' => 'Budi Mitra No Phone',
            'phone' => null,
            'role' => 'mitra',
            'status' => 'active',
            'city_id' => $city->id,
            'district_id' => $district->id,
        ]);

        // Customer with phone should show Quick WhatsApp button and sanitized URL
        Livewire::actingAs($superAdmin)
            ->test(Index::class)
            ->call('viewUser', $customer->id)
            ->assertSee('WhatsApp')
            ->assertSeeHtml('https://wa.me/6281234567890');

        // Mitra without phone should not show Quick WhatsApp button link
        Livewire::actingAs($superAdmin)
            ->test(Index::class)
            ->call('viewUser', $mitraNoPhone->id)
            ->assertDontSeeHtml('https://wa.me/');
    }

    public function test_user_whatsapp_url_helper()
    {
        $user = new User();
        $user->phone = '085712345678';
        $this->assertEquals('https://wa.me/6285712345678', $user->whatsapp_url);
        $this->assertEquals('https://wa.me/6285712345678?text=Halo+Mitra', $user->getWhatsappUrl('Halo Mitra'));

        $user->phone = null;
        $this->assertNull($user->whatsapp_url);
        $this->assertNull($user->getWhatsappUrl('Halo'));
    }
}

