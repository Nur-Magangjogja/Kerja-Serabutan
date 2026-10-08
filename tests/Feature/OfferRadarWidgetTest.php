<?php

namespace Tests\Feature;

use App\Livewire\Mitra\Dashboard\OfferRadarWidget;
use App\Models\AppSetting;
use App\Models\City;
use App\Models\Help;
use App\Models\HelpDispatch;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\HelpMatchingService;
use App\Services\PartnerOnlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OfferRadarWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $mitra;
    protected City $city;

    protected function setUp(): void
    {
        parent::setUp();

        AppSetting::set('offer_timeout_seconds', 45);
        AppSetting::set('heartbeat_ttl_seconds', 60);

        $this->city = City::create([
            'name'       => 'Yogyakarta',
            'latitude'   => -7.7956,
            'longitude'  => 110.3695,
        ]);

        $this->customer = User::factory()->create([
            'role'    => 'customer',
            'city_id' => $this->city->id,
        ]);
        UserBalance::create(['user_id' => $this->customer->id, 'balance' => 1000000]);

        $this->mitra = User::factory()->create([
            'role'     => 'mitra',
            'city_id'  => $this->city->id,
            'verified' => true,
            'status'   => 'active',
        ]);
        UserBalance::create(['user_id' => $this->mitra->id, 'balance' => 0]);
    }

    public function test_offer_radar_widget_renders_online_and_searching_state()
    {
        $this->actingAs($this->mitra);

        // 1. Start Offline -> Go Online
        Livewire::test(OfferRadarWidget::class)
            ->call('goOnline', -7.7956, 110.3695)
            ->assertDispatched('partner-state-changed')
            ->assertSee('ONLINE');

        $state = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_ONLINE, $state->matching_status);

        // 2. Start Searching
        Livewire::test(OfferRadarWidget::class)
            ->call('startSearching', -7.7956, 110.3695)
            ->assertDispatched('partner-state-changed')
            ->assertSee('Mencari Order');

        $state->refresh();
        $this->assertEquals(PartnerOnlineState::STATUS_SEARCHING, $state->matching_status);

        // 3. Stop Searching
        Livewire::test(OfferRadarWidget::class)
            ->call('stopSearching')
            ->assertDispatched('partner-state-changed');

        $state->refresh();
        $this->assertEquals(PartnerOnlineState::STATUS_ONLINE, $state->matching_status);
    }

    public function test_offer_radar_widget_displays_active_offer_card_and_accepts()
    {
        $this->actingAs($this->mitra);
        app(PartnerOnlineService::class)->startSearching($this->mitra, -7.7956, 110.3695);

        $help = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->city->id,
            'title'          => 'Bantuan Khusus Widget',
            'description'    => 'Deskripsi Pekerjaan Widget',
            'amount'         => 80000,
            'admin_fee'      => 8000,
            'total_amount'   => 88000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_SEEKING,
        ]);

        app(HelpMatchingService::class)->initiateMatching($help);

        $dispatch = HelpDispatch::where('help_id', $help->id)->where('mitra_id', $this->mitra->id)->first();
        $this->assertNotNull($dispatch);

        // Test Widget renders active offer
        Livewire::test(OfferRadarWidget::class)
            ->assertSee('Bantuan Khusus Widget')
            ->assertSee('Rp 80.000')
            ->assertSee('Tawaran Khusus Anda')
            ->call('acceptOffer', $dispatch->id)
            ->assertRedirect(route('mitra.helps.detail', ['id' => $help->id]));

        $help->refresh();
        $this->assertEquals($this->mitra->id, $help->mitra_id);
        $this->assertEquals(Help::STATUS_TAKEN, $help->status);
    }

    public function test_offer_radar_widget_can_reject_offer()
    {
        $this->actingAs($this->mitra);
        app(PartnerOnlineService::class)->startSearching($this->mitra, -7.7956, 110.3695);

        $help = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->city->id,
            'title'          => 'Bantuan Tolak Widget',
            'description'    => 'Deskripsi Tolak',
            'amount'         => 45000,
            'admin_fee'      => 4500,
            'total_amount'   => 49500,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_SEEKING,
        ]);

        app(HelpMatchingService::class)->initiateMatching($help);
        $dispatch = HelpDispatch::where('help_id', $help->id)->where('mitra_id', $this->mitra->id)->first();

        Livewire::test(OfferRadarWidget::class)
            ->call('rejectOffer', $dispatch->id, 'Mitra melewati tawaran')
            ->assertDispatched('partner-state-changed');

        $dispatch->refresh();
        $this->assertEquals(HelpDispatch::STATUS_REJECTED, $dispatch->status);
    }
}
