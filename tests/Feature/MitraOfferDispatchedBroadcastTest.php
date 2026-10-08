<?php

namespace Tests\Feature;

use App\Events\MitraOfferDispatched;
use App\Models\AppSetting;
use App\Models\City;
use App\Models\Help;
use App\Models\HelpDispatch;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\HelpMatchingService;
use App\Services\PartnerOnlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MitraOfferDispatchedBroadcastTest extends TestCase
{
    use RefreshDatabase;

    protected User $mitra;
    protected User $customer;
    protected City $city;

    protected function setUp(): void
    {
        parent::setUp();

        AppSetting::set('offer_timeout_seconds', 45);
        AppSetting::set('heartbeat_ttl_seconds', 60);

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

    public function test_mitra_offer_dispatched_event_is_broadcast_when_matched()
    {
        Event::fake([MitraOfferDispatched::class]);

        app(PartnerOnlineService::class)->startSearching($this->mitra, -7.7960, 110.3700);

        $help = Help::create([
            'user_id'             => $this->customer->id,
            'city_id'             => $this->city->id,
            'title'               => 'Tugas Realtime Reverb',
            'description'         => 'Deskripsi Realtime Reverb',
            'amount'              => 65000,
            'admin_fee'           => 6500,
            'total_amount'        => 71500,
            'latitude'            => -7.7956,
            'longitude'           => 110.3695,
            'status'              => Help::STATUS_MENUNGGU_MITRA,
            'payment_status'      => Help::PAYMENT_STATUS_PAID,
            'escrow_status'       => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'       => Help::DISPATCH_MODE_SEEKING,
            'model_version'       => 2,
            'mitra_earning'       => 65000,
            'platform_fee_amount' => 6500,
        ]);

        $matchingService = app(HelpMatchingService::class);
        $success = $matchingService->initiateMatching($help);

        $this->assertTrue($success);

        Event::assertDispatched(MitraOfferDispatched::class, function ($event) use ($help) {
            return $event->mitraId === $this->mitra->id
                && $event->helpId === $help->id
                && $event->broadcastOn()[0]->name === 'private-mitra.' . $this->mitra->id
                && $event->broadcastAs() === 'MitraOfferDispatched'
                && $event->amount === 65000.0;
        });
    }
}
