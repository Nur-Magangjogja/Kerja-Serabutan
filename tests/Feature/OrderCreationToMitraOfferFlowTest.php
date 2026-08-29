<?php

namespace Tests\Feature;

use App\Livewire\Customer\Helps\Create;
use App\Livewire\Mitra\Dashboard\Index as MitraDashboard;
use App\Livewire\Mitra\Helps\AllHelps;
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

class OrderCreationToMitraOfferFlowTest extends TestCase
{
    use RefreshDatabase;

    protected City $city;
    protected User $customer;
    protected User $mitra1;
    protected User $mitra2;
    protected PartnerOnlineService $onlineService;
    protected HelpMatchingService $matchingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::create([
            'name'       => 'Kota Yogyakarta',
            'state_name' => 'DI Yogyakarta',
            'is_active'  => true,
            'latitude'   => -7.7956,
            'longitude'  => 110.3695,
        ]);

        $this->customer = User::factory()->create([
            'role'    => 'customer',
            'city_id' => $this->city->id,
        ]);
        UserBalance::create(['user_id' => $this->customer->id, 'balance' => 500000]);

        $this->mitra1 = User::factory()->create([
            'role'    => 'mitra',
            'city_id' => $this->city->id,
        ]);
        UserBalance::create(['user_id' => $this->mitra1->id, 'balance' => 0]);

        $this->mitra2 = User::factory()->create([
            'role'    => 'mitra',
            'city_id' => $this->city->id,
        ]);
        UserBalance::create(['user_id' => $this->mitra2->id, 'balance' => 0]);

        $this->onlineService   = app(PartnerOnlineService::class);
        $this->matchingService = app(HelpMatchingService::class);
    }

    public function test_customer_creates_help_sets_paid_held_seeking_and_dispatches_offer_to_searching_mitra()
    {
        // 1. Mitra 1 & Mitra 2 start searching
        $this->onlineService->startSearching($this->mitra1, -7.7960, 110.3700);
        $this->onlineService->startSearching($this->mitra2, -7.8000, 110.3750);

        // 2. Customer creates order via Livewire
        $this->actingAs($this->customer);

        Livewire::test(Create::class)
            ->set('title', 'Bantu Angkat Meja')
            ->set('description', 'Perlu bantuan pindahan meja belajar')
            ->set('amount', 50000)
            ->set('city_id', $this->city->id)
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('customer.helps.index'));

        // 3. Verify Help in DB has correct architecture status
        $help = Help::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($help);
        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);
        $this->assertEquals(Help::PAYMENT_STATUS_PAID, $help->payment_status);
        $this->assertEquals(Help::ESCROW_STATUS_HELD, $help->escrow_status);
        $this->assertEquals(Help::DISPATCH_MODE_OFFERED, $help->dispatch_mode); // Mode is 'offered' because sequential matching offered it to Mitra 1

        // 4. Verify Mitra 1 received offer and is in OFFER_PENDING state
        $state1 = PartnerOnlineState::where('user_id', $this->mitra1->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_OFFER_PENDING, $state1->matching_status);
        $this->assertEquals($help->id, $state1->current_help_id);

        $dispatch = HelpDispatch::where('help_id', $help->id)->where('mitra_id', $this->mitra1->id)->first();
        $this->assertNotNull($dispatch);
        $this->assertEquals(HelpDispatch::STATUS_OFFERED, $dispatch->status);

        // 5. Mitra 1 accepts offer via Livewire Mitra Dashboard
        $this->actingAs($this->mitra1);
        Livewire::test(MitraDashboard::class)
            ->call('acceptOffer', $dispatch->id)
            ->assertHasNoErrors();

        // 6. Verify Help is assigned and Mitra 1 is busy
        $help->refresh();
        $this->assertEquals(Help::STATUS_TAKEN, $help->status);
        $this->assertEquals($this->mitra1->id, $help->mitra_id);
        $this->assertEquals(Help::DISPATCH_MODE_ASSIGNED, $help->dispatch_mode);

        $state1->refresh();
        $this->assertEquals(PartnerOnlineState::STATUS_BUSY, $state1->matching_status);

        $dispatch->refresh();
        $this->assertEquals(HelpDispatch::STATUS_ACCEPTED, $dispatch->status);
    }

    public function test_all_helps_marketplace_blocks_taking_order_that_is_in_sequential_offered_mode()
    {
        $this->onlineService->startSearching($this->mitra1, -7.7960, 110.3700);

        $help = Help::create([
            'user_id'             => $this->customer->id,
            'city_id'             => $this->city->id,
            'title'               => 'Bantuan Sequential',
            'description'         => 'Order',
            'amount'              => 50000,
            'admin_fee'           => 5000,
            'total_amount'        => 55000,
            'status'              => Help::STATUS_MENUNGGU_MITRA,
            'payment_status'      => Help::PAYMENT_STATUS_PAID,
            'escrow_status'       => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'       => Help::DISPATCH_MODE_OFFERED,
            'latitude'            => -7.7956,
            'longitude'           => 110.3695,
            'model_version'       => 2,
            'mitra_earning'       => 50000,
            'platform_fee_amount' => 5000,
        ]);

        // Mitra 2 tries to take this offer manually via AllHelps marketplace
        $this->actingAs($this->mitra2);
        Livewire::test(AllHelps::class)
            ->call('takeHelp', $help->id);

        // Verify help remains unassigned (mitra 2 cannot snatch offered task)
        $help->refresh();
        $this->assertNull($help->mitra_id);
        $this->assertEquals(Help::DISPATCH_MODE_OFFERED, $help->dispatch_mode);
        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);
    }

    public function test_mitra_dashboard_recommendations_do_not_leak_sequential_offered_helps()
    {
        // 1. Create a help that is offered / seeking in sequential dispatch
        $sequentialHelp = Help::create([
            'user_id'             => $this->customer->id,
            'city_id'             => $this->city->id,
            'title'               => 'Bantuan Rahasia Sequential',
            'description'         => 'Hanya untuk calon mitra tertentu',
            'amount'              => 75000,
            'admin_fee'           => 7500,
            'total_amount'        => 82500,
            'status'              => Help::STATUS_MENUNGGU_MITRA,
            'payment_status'      => Help::PAYMENT_STATUS_PAID,
            'escrow_status'       => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'       => Help::DISPATCH_MODE_OFFERED,
            'latitude'            => -7.7956,
            'longitude'           => 110.3695,
            'model_version'       => 2,
            'mitra_earning'       => 75000,
            'platform_fee_amount' => 7500,
        ]);

        // 2. Create an open pool help
        $poolHelp = Help::create([
            'user_id'             => $this->customer->id,
            'city_id'             => $this->city->id,
            'title'               => 'Bantuan Terbuka Umum Pool',
            'description'         => 'Boleh diambil siapa saja di pool',
            'amount'              => 50000,
            'admin_fee'           => 5000,
            'total_amount'        => 55000,
            'status'              => Help::STATUS_MENUNGGU_MITRA,
            'payment_status'      => Help::PAYMENT_STATUS_PAID,
            'escrow_status'       => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'       => Help::DISPATCH_MODE_POOL,
            'latitude'            => -7.7956,
            'longitude'           => 110.3695,
            'model_version'       => 2,
            'mitra_earning'       => 50000,
            'platform_fee_amount' => 5000,
        ]);

        // 3. Mitra 2 checks dashboard
        $this->actingAs($this->mitra2);
        $component = Livewire::test(MitraDashboard::class);

        $viewData = $component->viewData('recommendedHelps');
        $this->assertFalse($viewData->contains('id', $sequentialHelp->id));
        $this->assertTrue($viewData->contains('id', $poolHelp->id));

        $latestHelps = $component->viewData('latestHelps');
        $this->assertFalse($latestHelps->contains('id', $sequentialHelp->id));
        $this->assertTrue($latestHelps->contains('id', $poolHelp->id));

        $nearbyHelps = $component->viewData('nearbyHelps');
        $this->assertFalse($nearbyHelps->contains('id', $sequentialHelp->id));
        $this->assertTrue($nearbyHelps->contains('id', $poolHelp->id));
    }

    public function test_mitra_dashboard_auto_expires_offer_and_advances_without_queue_worker()
    {
        // 1. Mitra 1 is in searching state
        $this->onlineService->startSearching($this->mitra1, -7.7960, 110.3700);

        // 2. Customer creates order -> offered to Mitra 1
        $this->actingAs($this->customer);
        Livewire::test(Create::class)
            ->set('title', 'Bantuan Cepat')
            ->set('description', 'Perlu bantuan segera')
            ->set('amount', 50000)
            ->set('city_id', $this->city->id)
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->call('save');

        $help = Help::where('user_id', $this->customer->id)->latest()->first();
        $this->assertEquals(Help::DISPATCH_MODE_OFFERED, $help->dispatch_mode);

        $dispatch = HelpDispatch::where('help_id', $help->id)->where('mitra_id', $this->mitra1->id)->first();
        $this->assertNotNull($dispatch);

        // 3. Simulate 46 seconds passed (offer has expired in real-world time)
        $dispatch->update(['expires_at' => now()->subSeconds(10)]);

        // 4. Mitra 1 opens/refreshes Dashboard -> Realtime Self-Healing triggers handleExpiry automatically
        $this->actingAs($this->mitra1);
        $component = Livewire::test(MitraDashboard::class);

        // Mitra 1 state is healed back to 'searching' (not stuck in offer_pending)
        $state1 = PartnerOnlineState::where('user_id', $this->mitra1->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_SEARCHING, $state1->matching_status);

        // Active offer is cleaned
        $this->assertNull($component->viewData('activeOffer'));

        // Dispatch status updated to expired
        $dispatch->refresh();
        $this->assertEquals(HelpDispatch::STATUS_EXPIRED, $dispatch->status);

        // Help has now fallen back to Open Pool because there are no other candidates
        $help->refresh();
        $this->assertEquals(Help::DISPATCH_MODE_POOL, $help->dispatch_mode);
    }

    public function test_mitra_can_reject_offer_via_livewire_dashboard()
    {
        $this->onlineService->startSearching($this->mitra1, -7.7960, 110.3700);

        $help = Help::create([
            'user_id'             => $this->customer->id,
            'city_id'             => $this->city->id,
            'title'               => 'Bantuan Dispatched',
            'description'         => 'Order',
            'amount'              => 50000,
            'admin_fee'           => 5000,
            'total_amount'        => 55000,
            'status'              => Help::STATUS_MENUNGGU_MITRA,
            'payment_status'      => Help::PAYMENT_STATUS_PAID,
            'escrow_status'       => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'       => Help::DISPATCH_MODE_OFFERED,
            'latitude'            => -7.7956,
            'longitude'           => 110.3695,
        ]);

        $this->matchingService->initiateMatching($help);

        $dispatch = HelpDispatch::where('help_id', $help->id)->where('mitra_id', $this->mitra1->id)->first();
        $this->assertNotNull($dispatch);

        // Mitra 1 clicks "Lewati / Tolak" on Livewire Dashboard
        $this->actingAs($this->mitra1);
        Livewire::test(MitraDashboard::class)
            ->call('rejectOffer', $dispatch->id)
            ->assertHasNoErrors()
            ->assertDispatched('show-status-notification', message: 'Tawaran dilewati.');

        $dispatch->refresh();
        $this->assertEquals(HelpDispatch::STATUS_REJECTED, $dispatch->status);

        // Help falls back to pool because no other candidate
        $help->refresh();
        $this->assertEquals(Help::DISPATCH_MODE_POOL, $help->dispatch_mode);
    }
}
