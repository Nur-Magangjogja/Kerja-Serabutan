<?php

namespace Tests\Feature;

use App\Livewire\Profile\DeleteUserForm;
use App\Livewire\Profile\UpdateProfileInformationForm;
use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected City $city;

    protected function setUp(): void
    {
        parent::setUp();
        $this->city = City::create([
            'name'      => 'Yogyakarta',
            'province'  => 'DI Yogyakarta',
            'latitude'  => -7.7956,
            'longitude' => 110.3695,
            'is_active' => true,
        ]);
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create([
            'city_id' => $this->city->id,
            'role'    => 'customer',
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create([
            'city_id' => $this->city->id,
            'role'    => 'customer',
            'phone'   => '081234567890',
            'address' => 'Jl. Malioboro No. 1',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(UpdateProfileInformationForm::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('phone', '081234567899')
            ->set('city_id', $this->city->id)
            ->set('address', 'Jl. Kaliurang KM 5')
            ->call('updateProfileInformation');

        $component
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertSame('081234567899', $user->phone);
        $this->assertSame('Jl. Kaliurang KM 5', $user->address);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create([
            'city_id'           => $this->city->id,
            'role'              => 'customer',
            'phone'             => '081234567890',
            'address'           => 'Jl. Malioboro No. 1',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $component = Livewire::test(UpdateProfileInformationForm::class)
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->set('phone', $user->phone)
            ->set('city_id', $this->city->id)
            ->set('address', $user->address)
            ->call('updateProfileInformation');

        $component
            ->assertHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = \Livewire\Volt\Volt::test('profile.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = \Livewire\Volt\Volt::test('profile.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        $component
            ->assertHasErrors('password')
            ->assertNoRedirect();

        $this->assertNotNull($user->fresh());
    }
}
