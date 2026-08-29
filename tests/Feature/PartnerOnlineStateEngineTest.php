<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Services\PartnerOnlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerOnlineStateEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $mitra;
    protected City $city;
    protected PartnerOnlineService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::create([
            'name'       => 'Kota Yogyakarta',
            'state_name' => 'DI Yogyakarta',
            'latitude'   => -7.7956,
            'longitude'  => 110.3695,
        ]);

        $this->mitra = User::factory()->create([
            'role'    => 'mitra',
            'city_id' => $this->city->id,
        ]);

        $this->service = app(PartnerOnlineService::class);
    }

    public function test_mitra_can_go_online_from_offline_state()
    {
        $this->service->goOnline($this->mitra, -7.7956, 110.3695);

        $state = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertNotNull($state);
        $this->assertEquals(PartnerOnlineState::STATUS_ONLINE, $state->matching_status);
        $this->assertNotNull($state->last_seen_at);
        $this->assertEquals(-7.79560000, (float) $state->latitude);
        $this->assertEquals(110.36950000, (float) $state->longitude);
    }

    public function test_mitra_can_start_and_stop_searching()
    {
        $this->service->goOnline($this->mitra);
        $this->service->startSearching($this->mitra);

        $state = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_SEARCHING, $state->matching_status);
        $this->assertNotNull($state->searching_since);
        $this->assertTrue($state->isSearching());
        $this->assertTrue($state->isEligibleForOffer());

        // Stop searching -> back to online
        $this->service->stopSearching($this->mitra);
        $state->refresh();
        $this->assertEquals(PartnerOnlineState::STATUS_ONLINE, $state->matching_status);
        $this->assertNull($state->searching_since);
    }

    public function test_go_offline_clears_search_and_is_rejected_when_busy_or_offer_pending()
    {
        $this->service->startSearching($this->mitra);
        $this->service->goOffline($this->mitra);

        $state = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_OFFLINE, $state->matching_status);
        $this->assertNull($state->searching_since);

        // Set to Busy
        $help = $this->createTestHelp();
        $state->update(['matching_status' => PartnerOnlineState::STATUS_BUSY, 'current_help_id' => $help->id]);

        // Attempting to go offline when busy MUST throw Exception
        $this->expectException(\RuntimeException::class);
        $this->service->goOffline($this->mitra);
    }

    public function test_heartbeat_updates_timestamp_and_does_not_revive_offline_mitra()
    {
        // 1. Offline mitra -> heartbeat does NOT change status to online
        $this->service->getOrCreateState($this->mitra->id);
        $this->service->heartbeat($this->mitra, -7.8000, 110.3700);

        $state = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_OFFLINE, $state->matching_status);
        $this->assertNull($state->last_seen_at); // Not revived

        // 2. Online mitra -> heartbeat refreshes last_seen_at
        $this->service->goOnline($this->mitra);
        $pastTime = now()->subSeconds(30);
        $state->update(['last_seen_at' => $pastTime]);

        $this->service->heartbeat($this->mitra);
        $state->refresh();
        $this->assertTrue($state->last_seen_at->greaterThan($pastTime));
    }

    private function createTestHelp(): Help
    {
        $cust = User::factory()->create(['role' => 'customer', 'city_id' => $this->city->id]);
        return Help::create([
            'user_id'      => $cust->id,
            'city_id'      => $this->city->id,
            'title'        => 'Test Bantuan',
            'description'  => 'Deskripsi uji coba matching state',
            'amount'       => 50000,
            'admin_fee'    => 5000,
            'total_amount' => 55000,
            'status'       => Help::STATUS_MENUNGGU_MITRA,
        ]);
    }

    public function test_set_offer_pending_requires_searching_and_fresh_heartbeat()
    {
        $help = $this->createTestHelp();

        // 1. Mitra is Online (not Searching) -> rejected
        $this->service->goOnline($this->mitra);
        $result = $this->service->setOfferPending($this->mitra->id, $help->id);
        $this->assertFalse($result);

        // 2. Mitra is Searching but Heartbeat is Stale (> 60s) -> rejected
        $this->service->startSearching($this->mitra);
        PartnerOnlineState::where('user_id', $this->mitra->id)->update([
            'last_seen_at' => now()->subSeconds(120),
        ]);
        $resultStale = $this->service->setOfferPending($this->mitra->id, $help->id);
        $this->assertFalse($resultStale);

        // 3. Mitra is Searching + Fresh Heartbeat -> SUCCESS
        PartnerOnlineState::where('user_id', $this->mitra->id)->update([
            'last_seen_at' => now(),
        ]);
        $resultFresh = $this->service->setOfferPending($this->mitra->id, $help->id);
        $this->assertTrue($resultFresh);

        $state = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_OFFER_PENDING, $state->matching_status);
        $this->assertEquals($help->id, $state->current_help_id);
    }

    public function test_revert_from_offer_pending_restores_searching_or_online()
    {
        $help = $this->createTestHelp();
        $this->service->startSearching($this->mitra);
        $this->service->setOfferPending($this->mitra->id, $help->id);

        // Revert with fresh heartbeat -> back to searching
        $this->service->revertFromOfferPending($this->mitra->id, $help->id);
        $state = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_SEARCHING, $state->matching_status);
        $this->assertNull($state->current_help_id);

        // Revert with stale heartbeat -> demoted to online
        $this->service->setOfferPending($this->mitra->id, $help->id);
        $state->update(['last_seen_at' => now()->subSeconds(90)]);
        $this->service->revertFromOfferPending($this->mitra->id, $help->id);
        $state->refresh();
        $this->assertEquals(PartnerOnlineState::STATUS_ONLINE, $state->matching_status);
        $this->assertNull($state->current_help_id);
    }

    public function test_set_busy_and_release_busy_lifecycle()
    {
        $help = $this->createTestHelp();
        $this->service->startSearching($this->mitra);
        $this->service->setBusy($this->mitra->id, $help->id);

        $state = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_BUSY, $state->matching_status);
        $this->assertEquals($help->id, $state->current_help_id);

        // Release busy
        $this->service->releaseBusy($this->mitra->id, $help->id);
        $state->refresh();
        $this->assertNull($state->current_help_id);
        $this->assertNotNull($state->last_completed_at);
        $this->assertEquals(PartnerOnlineState::STATUS_SEARCHING, $state->matching_status);
    }

    public function test_cleanup_stale_states_demotes_only_searching_mitra_and_protects_busy()
    {
        $help = $this->createTestHelp();

        // Mitra 1: Searching with Stale Heartbeat (90s ago)
        $this->service->startSearching($this->mitra);
        PartnerOnlineState::where('user_id', $this->mitra->id)->update([
            'last_seen_at' => now()->subSeconds(90),
        ]);

        // Mitra 2: Busy with Stale Heartbeat (battery died during service)
        $mitra2 = User::factory()->create(['role' => 'mitra', 'city_id' => $this->city->id]);
        $this->service->startSearching($mitra2);
        $this->service->setBusy($mitra2->id, $help->id);
        PartnerOnlineState::where('user_id', $mitra2->id)->update([
            'last_seen_at' => now()->subMinutes(10),
        ]);

        // Run cleanup with TTL = 60s
        $demoted = $this->service->cleanupStaleStates(60);
        $this->assertEquals(1, $demoted);

        // Mitra 1 -> Demoted to ONLINE
        $state1 = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_ONLINE, $state1->matching_status);

        // Mitra 2 -> PROTECTED, remains BUSY!
        $state2 = PartnerOnlineState::where('user_id', $mitra2->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_BUSY, $state2->matching_status);
        $this->assertEquals($help->id, $state2->current_help_id);
    }
}
