<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\PartnerOnlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerHeartbeatEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected User $mitra;
    protected User $customer;
    protected City $city;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::create([
            'name'      => 'Yogyakarta',
            'latitude'  => -7.7956,
            'longitude' => 110.3695,
        ]);

        $this->mitra = User::factory()->create([
            'role'     => 'mitra',
            'city_id'  => $this->city->id,
            'verified' => true,
            'status'   => 'active',
        ]);
        UserBalance::create(['user_id' => $this->mitra->id, 'balance' => 0]);

        $this->customer = User::factory()->create([
            'role'     => 'customer',
            'city_id'  => $this->city->id,
            'verified' => true,
            'status'   => 'active',
        ]);
    }

    public function test_unauthenticated_request_is_redirected()
    {
        $response = $this->postJson(route('mitra.heartbeat'), [
            'latitude'  => -7.7956,
            'longitude' => 110.3695,
        ]);

        $response->assertStatus(401);
    }

    public function test_customer_cannot_call_mitra_heartbeat()
    {
        $this->actingAs($this->customer);

        $response = $this->postJson(route('mitra.heartbeat'), [
            'latitude'  => -7.7956,
            'longitude' => 110.3695,
        ]);

        $response->assertStatus(403);
    }

    public function test_mitra_heartbeat_updates_gps_and_timestamp()
    {
        $this->actingAs($this->mitra);

        // Turn online first
        app(PartnerOnlineService::class)->goOnline($this->mitra, -7.7950, 110.3690);

        $response = $this->postJson(route('mitra.heartbeat'), [
            'latitude'  => -7.7960,
            'longitude' => 110.3700,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status'          => 'ok',
                'matching_status' => PartnerOnlineState::STATUS_ONLINE,
            ])
            ->assertJsonStructure(['status', 'matching_status', 'server_time']);

        $state = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertNotNull($state);
        $this->assertEquals(-7.7960, (float) $state->latitude);
        $this->assertEquals(110.3700, (float) $state->longitude);
    }
}
