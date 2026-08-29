<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Help;
use App\Models\HelpDispatch;
use App\Models\PartnerOnlineState;
use App\Models\Rating;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\HelpMatchingService;
use App\Services\PartnerOnlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SequentialMatchingAndDisputeMediationEngineTest extends TestCase
{
    use RefreshDatabase;

    protected City $city;
    protected User $customer;
    protected User $mitra1;
    protected User $mitra2;
    protected User $mitra3;
    protected User $admin;
    protected HelpMatchingService $matchingService;
    protected PartnerOnlineService $onlineService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::create([
            'name'       => 'Kota Yogyakarta',
            'state_name' => 'DI Yogyakarta',
            'latitude'   => -7.7956,
            'longitude'  => 110.3695,
        ]);

        $this->customer = User::factory()->create([
            'role'    => 'customer',
            'city_id' => $this->city->id,
        ]);

        $this->mitra1 = User::factory()->create([
            'role'    => 'mitra',
            'city_id' => $this->city->id,
        ]);

        $this->mitra2 = User::factory()->create([
            'role'    => 'mitra',
            'city_id' => $this->city->id,
        ]);

        $this->mitra3 = User::factory()->create([
            'role'    => 'mitra',
            'city_id' => $this->city->id,
        ]);

        $this->admin = User::factory()->create([
            'role'    => 'admin',
            'city_id' => $this->city->id,
        ]);

        $this->onlineService   = app(PartnerOnlineService::class);
        $this->matchingService = app(HelpMatchingService::class);
    }

    private function createHelp(float $amount = 50000, float $fee = 5000): Help
    {
        return Help::create([
            'user_id'             => $this->customer->id,
            'city_id'             => $this->city->id,
            'title'               => 'Bantu Cuci Sepeda Motor',
            'description'         => 'Cuci motor matic bersih detail',
            'amount'              => $amount,
            'admin_fee'           => $fee,
            'total_amount'        => $amount + $fee,
            'latitude'            => -7.7956,
            'longitude'           => 110.3695,
            'status'              => Help::STATUS_MENUNGGU_MITRA,
            'escrow_status'       => Help::ESCROW_STATUS_HELD,
            'payment_status'      => Help::PAYMENT_STATUS_PAID,
            'dispatch_mode'       => Help::DISPATCH_MODE_SEEKING,
            'model_version'       => 2,
            'mitra_earning'       => $amount,
            'platform_fee_amount' => $fee,
        ]);
    }

    public function test_scoring_formula_calculates_distance_rating_reliability_fairness()
    {
        $help = $this->createHelp();

        // Berikan rating 5 pada mitra1
        Rating::create([
            'help_id'  => $help->id,
            'rater_id' => $this->customer->id,
            'ratee_id' => $this->mitra1->id,
            'type'     => 'customer_to_mitra',
            'rating'   => 5,
        ]);

        $this->onlineService->startSearching($this->mitra1, -7.7960, 110.3700);
        $state = PartnerOnlineState::where('user_id', $this->mitra1->id)->first();

        $scoreDetails = $this->matchingService->calculatePartnerCompositeScore($help, $this->mitra1, $state);

        $this->assertArrayHasKey('total_score', $scoreDetails);
        $this->assertArrayHasKey('distance_score', $scoreDetails);
        $this->assertArrayHasKey('rating_score', $scoreDetails);
        $this->assertArrayHasKey('reliability_score', $scoreDetails);
        $this->assertArrayHasKey('fairness_score', $scoreDetails);
        $this->assertGreaterThan(0.5, $scoreDetails['total_score']);
    }

    public function test_get_ranked_candidates_orders_by_total_score_and_tie_breakers()
    {
        $this->onlineService->startSearching($this->mitra1, -7.7960, 110.3700); // Terdekat
        $this->onlineService->startSearching($this->mitra2, -7.8000, 110.3750); // Sedang
        $this->onlineService->startSearching($this->mitra3, -7.8100, 110.3800); // Lebih jauh

        $help = $this->createHelp();
        $candidates = $this->matchingService->getRankedCandidates($help);

        $this->assertCount(3, $candidates);
        // Mitra 1 harus berada di Rank 1 karena skor jarak tertinggi
        $this->assertEquals($this->mitra1->id, $candidates->first()->user->id);
    }

    public function test_initiate_matching_dispatches_to_rank_1_and_sets_offer_pending()
    {
        $this->onlineService->startSearching($this->mitra1, -7.7960, 110.3700);
        $this->onlineService->startSearching($this->mitra2, -7.8000, 110.3750);

        $help = $this->createHelp();
        $success = $this->matchingService->initiateMatching($help);

        $this->assertTrue($success);

        $help->refresh();
        $this->assertEquals(Help::DISPATCH_MODE_OFFERED, $help->dispatch_mode);

        // Pastikan record HelpDispatch tercipta untuk Rank 1 (mitra 1)
        $dispatch = HelpDispatch::where('help_id', $help->id)->where('rank', 1)->first();
        $this->assertNotNull($dispatch);
        $this->assertEquals($this->mitra1->id, $dispatch->mitra_id);
        $this->assertEquals(HelpDispatch::STATUS_OFFERED, $dispatch->status);
        $this->assertTrue($dispatch->isPending());

        // Status online mitra 1 harus berubah jadi OFFER_PENDING
        $state1 = PartnerOnlineState::where('user_id', $this->mitra1->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_OFFER_PENDING, $state1->matching_status);
        $this->assertEquals($help->id, $state1->current_help_id);
    }

    public function test_initiate_matching_falls_back_to_open_pool_when_no_candidates()
    {
        // Tidak ada mitra yang online / searching
        $help = $this->createHelp();
        $success = $this->matchingService->initiateMatching($help);

        $this->assertFalse($success);

        $help->refresh();
        $this->assertEquals(Help::DISPATCH_MODE_POOL, $help->dispatch_mode);
        $this->assertNotNull($help->pool_opened_at);
    }

    public function test_accept_offer_locks_order_assigns_mitra_and_sets_busy()
    {
        $this->onlineService->startSearching($this->mitra1, -7.7960, 110.3700);
        $help = $this->createHelp();
        $this->matchingService->initiateMatching($help);

        $dispatch = HelpDispatch::where('help_id', $help->id)->where('rank', 1)->first();

        // Mitra 1 accepts offer
        $accepted = $this->matchingService->acceptOffer($dispatch->id, $this->mitra1);
        $this->assertTrue($accepted);

        $dispatch->refresh();
        $help->refresh();

        $this->assertEquals(HelpDispatch::STATUS_ACCEPTED, $dispatch->status);
        $this->assertEquals(Help::STATUS_TAKEN, $help->status);
        $this->assertEquals(Help::DISPATCH_MODE_ASSIGNED, $help->dispatch_mode);
        $this->assertEquals($this->mitra1->id, $help->mitra_id);

        // Status mitra 1 berubah menjadi BUSY
        $state1 = PartnerOnlineState::where('user_id', $this->mitra1->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_BUSY, $state1->matching_status);
        $this->assertEquals($help->id, $state1->current_help_id);

        // Percobaan accept ulang oleh orang lain / yang sama harus throw Exception
        $this->expectException(\RuntimeException::class);
        $this->matchingService->acceptOffer($dispatch->id, $this->mitra1);
    }

    public function test_reject_offer_reverts_state_and_advances_to_next_candidate()
    {
        $this->onlineService->startSearching($this->mitra1, -7.7960, 110.3700);
        $this->onlineService->startSearching($this->mitra2, -7.8000, 110.3750);

        $help = $this->createHelp();
        $this->matchingService->initiateMatching($help);

        $dispatch1 = HelpDispatch::where('help_id', $help->id)->where('rank', 1)->first();

        // Mitra 1 rejects offer
        $this->matchingService->rejectOffer($dispatch1->id, $this->mitra1, 'Sedang istirahat makan.');

        $dispatch1->refresh();
        $this->assertEquals(HelpDispatch::STATUS_REJECTED, $dispatch1->status);
        $this->assertEquals('Sedang istirahat makan.', $dispatch1->rejection_reason);

        // Mitra 1 status pulih ke SEARCHING
        $state1 = PartnerOnlineState::where('user_id', $this->mitra1->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_SEARCHING, $state1->matching_status);
        $this->assertNull($state1->current_help_id);

        // Tawaran otomatis diteruskan ke Mitra 2 (Rank 2)
        $dispatch2 = HelpDispatch::where('help_id', $help->id)->where('rank', 2)->first();
        $this->assertNotNull($dispatch2);
        $this->assertEquals($this->mitra2->id, $dispatch2->mitra_id);
        $this->assertEquals(HelpDispatch::STATUS_OFFERED, $dispatch2->status);

        $state2 = PartnerOnlineState::where('user_id', $this->mitra2->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_OFFER_PENDING, $state2->matching_status);
    }

    public function test_handle_expiry_reverts_state_and_advances_to_next_candidate()
    {
        $this->onlineService->startSearching($this->mitra1, -7.7960, 110.3700);
        $this->onlineService->startSearching($this->mitra2, -7.8000, 110.3750);

        $help = $this->createHelp();
        $this->matchingService->initiateMatching($help);

        $dispatch1 = HelpDispatch::where('help_id', $help->id)->where('rank', 1)->first();

        // 45 detik lewat tanpa respon (Handle Expiry)
        $this->matchingService->handleExpiry($dispatch1->id, force: true);

        $dispatch1->refresh();
        $this->assertEquals(HelpDispatch::STATUS_EXPIRED, $dispatch1->status);

        // Mitra 2 menerima tawaran Rank 2
        $dispatch2 = HelpDispatch::where('help_id', $help->id)->where('rank', 2)->first();
        $this->assertNotNull($dispatch2);
        $this->assertEquals($this->mitra2->id, $dispatch2->mitra_id);
    }

    public function test_sequential_dispatch_falls_back_to_open_pool_after_all_candidates_exhausted()
    {
        // Hanya 1 mitra yang online
        $this->onlineService->startSearching($this->mitra1, -7.7960, 110.3700);

        $help = $this->createHelp();
        $this->matchingService->initiateMatching($help);

        $dispatch1 = HelpDispatch::where('help_id', $help->id)->where('rank', 1)->first();

        // Mitra 1 menolak -> tidak ada kandidat lagi -> langsung buka Open Pool
        $this->matchingService->rejectOffer($dispatch1->id, $this->mitra1);

        $help->refresh();
        $this->assertEquals(Help::DISPATCH_MODE_POOL, $help->dispatch_mode);
        $this->assertNotNull($help->pool_opened_at);
    }
}
