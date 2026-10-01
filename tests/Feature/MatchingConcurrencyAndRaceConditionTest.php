<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\City;
use App\Models\Help;
use App\Models\HelpDispatch;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\HelpMatchingService;
use App\Services\HelpTransactionService;
use App\Services\PartnerOnlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MatchingConcurrencyAndRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $mitraA;
    protected User $mitraB;
    protected City $city;
    protected HelpMatchingService $matchingService;
    protected HelpTransactionService $transactionService;
    protected PartnerOnlineService $onlineService;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed settings
        AppSetting::set('offer_timeout_seconds', 45);
        AppSetting::set('heartbeat_ttl_seconds', 60);
        AppSetting::set('max_dispatch_candidates', 5);
        AppSetting::set('max_pool_radius_km', 60.0);
        AppSetting::set('newbie_boost_enabled', false);

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

        $this->mitraA = User::factory()->create([
            'role'     => 'mitra',
            'city_id'  => $this->city->id,
            'verified' => true,
            'status'   => 'active',
        ]);
        UserBalance::create(['user_id' => $this->mitraA->id, 'balance' => 0]);

        $this->mitraB = User::factory()->create([
            'role'     => 'mitra',
            'city_id'  => $this->city->id,
            'verified' => true,
            'status'   => 'active',
        ]);
        UserBalance::create(['user_id' => $this->mitraB->id, 'balance' => 0]);

        $this->matchingService    = app(HelpMatchingService::class);
        $this->transactionService = app(HelpTransactionService::class);
        $this->onlineService      = app(PartnerOnlineService::class);
    }

    /**
     * TEST A:
     * Mitra A dan Mitra B mencoba mengambil Help #1 (Open Pool) secara bersamaan.
     * Expected: Tepat SATU assignment yang berhasil, request kedua ditolak bersih,
     * dan tidak ada penugasan ganda (No Double Assignment).
     */
    public function test_a_concurrent_pool_take_results_in_exactly_one_valid_assignment()
    {
        $help = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->city->id,
            'title'          => 'Bantuan Pool Concurrent',
            'description'    => 'Tes konkurensi pool',
            'amount'         => 50000,
            'admin_fee'      => 5000,
            'total_amount'   => 55000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_POOL,
        ]);

        $this->onlineService->startSearching($this->mitraA, -7.7956, 110.3695);
        $this->onlineService->startSearching($this->mitraB, -7.7956, 110.3695);

        // Request 1: Mitra A mengambil bantuan
        $this->transactionService->takeHelp($help, $this->mitraA);

        $help->refresh();
        $this->assertEquals($this->mitraA->id, $help->mitra_id);
        $this->assertEquals(Help::STATUS_TAKEN, $help->status);
        $this->assertEquals(Help::DISPATCH_MODE_ASSIGNED, $help->dispatch_mode);

        $stateA = PartnerOnlineState::where('user_id', $this->mitraA->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_BUSY, $stateA->matching_status);
        $this->assertEquals($help->id, $stateA->current_help_id);

        // Request 2: Mitra B mencoba mengambil bantuan yang sama -> HARUS DITOLAK
        $exceptionThrown = false;
        try {
            $this->transactionService->takeHelp($help, $this->mitraB);
        } catch (\RuntimeException $e) {
            $exceptionThrown = true;
            $this->assertStringContainsString('sudah diambil', $e->getMessage());
        }

        $this->assertTrue($exceptionThrown, 'Request kedua harus melempar RuntimeException');

        // Verifikasi Integritas: Mitra B tetap SEARCHING (tidak terpengaruh), Help tetap milik Mitra A
        $help->refresh();
        $this->assertEquals($this->mitraA->id, $help->mitra_id);

        $stateB = PartnerOnlineState::where('user_id', $this->mitraB->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_SEARCHING, $stateB->matching_status);
        $this->assertNull($stateB->current_help_id);
    }

    /**
     * TEST B:
     * Race Condition antara dispatchOfferToCandidate() dan takeHelp().
     * Expected: Jika Help sudah diambil (TAKEN), dispatchOfferToCandidate() atomik membatalkan diri (return false),
     * dan TIDAK PERNAH menghasilkan status Help=TAKEN tetapi dispatch=OFFERED.
     */
    public function test_b_dispatch_offer_and_pool_take_interleaving_never_creates_inconsistent_state()
    {
        $help = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->city->id,
            'title'          => 'Bantuan Interleaving Race',
            'description'    => 'Tes race dispatch vs take',
            'amount'         => 60000,
            'admin_fee'      => 6000,
            'total_amount'   => 66000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_POOL,
        ]);

        $this->onlineService->startSearching($this->mitraA, -7.7956, 110.3695);

        // Skenario 1: Mitra A mengambil tugas dari Pool terlebih dahulu
        $this->transactionService->takeHelp($help, $this->mitraA);
        $help->refresh();
        $this->assertEquals(Help::STATUS_TAKEN, $help->status);

        // Skenario 2: Sistem Matching Engine mencoba mengirim tawaran sequential untuk Help yang sama ke kandidat lain (Mitra B)
        $this->onlineService->startSearching($this->mitraB, -7.7956, 110.3695);
        $stateB = PartnerOnlineState::where('user_id', $this->mitraB->id)->first();
        $candidateB = (object) [
            'user'          => $this->mitraB,
            'state'         => $stateB,
            'score_details' => ['distance' => 1.0, 'total_score' => 0.9],
        ];

        $dispatched = $this->matchingService->dispatchOfferToCandidate($help, collect([$candidateB]), 1, 1);

        // Matching engine wajib menolak karena order sudah TAKEN
        $this->assertFalse($dispatched);

        // Pastikan TIDAK ADA record HelpDispatch yang terbuat dengan status 'offered'
        $offeredDispatches = HelpDispatch::where('help_id', $help->id)
            ->where('status', HelpDispatch::STATUS_OFFERED)
            ->count();
        $this->assertEquals(0, $offeredDispatches);

        // Status Help tetap TAKEN dan milik Mitra A
        $help->refresh();
        $this->assertEquals(Help::STATUS_TAKEN, $help->status);
        $this->assertEquals($this->mitraA->id, $help->mitra_id);
    }

    /**
     * TEST C:
     * Mitra A sedang memiliki tawaran pending (OFFER_PENDING pada Help #1).
     * Kemudian Mitra A mengambil Help #2 dari Open Pool.
     * Expected:
     * - Help #2 berhasil di-assign ke Mitra A.
     * - Mitra A beralih ke STATUS_BUSY pada Help #2.
     * - Help #1 dispatch ditandai REJECTED dan otomatis dialihkan ke antrean berikutnya.
     * - Tidak ada status OFFER_PENDING yang tersangkut (zero stuck state).
     */
    public function test_c_mitra_taking_pool_help_while_offer_pending_resolves_offer_without_stuck_state()
    {
        // 1. Buat Help #1 (Sequential) & Help #2 (Pool)
        $help1 = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->city->id,
            'title'          => 'Help 1 Sequential',
            'description'    => 'Order 1',
            'amount'         => 40000,
            'admin_fee'      => 4000,
            'total_amount'   => 44000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_SEEKING,
        ]);

        $help2 = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->city->id,
            'title'          => 'Help 2 Open Pool',
            'description'    => 'Order 2',
            'amount'         => 50000,
            'admin_fee'      => 5000,
            'total_amount'   => 55000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_POOL,
        ]);

        // 2. Mitra A & Mitra B mulai mencari
        $this->onlineService->startSearching($this->mitraA, -7.7956, 110.3695);
        $this->onlineService->startSearching($this->mitraB, -7.7956, 110.3695);

        // 3. Sistem matching mendispatch Help #1 ke Mitra A (Rank 1)
        $this->matchingService->initiateMatching($help1);

        $stateA = PartnerOnlineState::where('user_id', $this->mitraA->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_OFFER_PENDING, $stateA->matching_status);
        $this->assertEquals($help1->id, $stateA->current_help_id);

        $dispatch1 = HelpDispatch::where('help_id', $help1->id)->where('mitra_id', $this->mitraA->id)->first();
        $this->assertEquals(HelpDispatch::STATUS_OFFERED, $dispatch1->status);

        // 4. Mitra A membuka tab Open Pool dan mengambil Help #2
        $this->transactionService->takeHelp($help2, $this->mitraA);

        // 5. Verifikasi: Help #2 assigned ke Mitra A dan Mitra A menjadi BUSY
        $help2->refresh();
        $this->assertEquals($this->mitraA->id, $help2->mitra_id);
        $this->assertEquals(Help::STATUS_TAKEN, $help2->status);

        $stateA->refresh();
        $this->assertEquals(PartnerOnlineState::STATUS_BUSY, $stateA->matching_status);
        $this->assertEquals($help2->id, $stateA->current_help_id);

        // 6. Verifikasi: Dispatch lama Help #1 otomatis REJECTED
        $dispatch1->refresh();
        $this->assertEquals(HelpDispatch::STATUS_REJECTED, $dispatch1->status);

        // 7. Verifikasi: Help #1 otomatis diteruskan ke Mitra B (Rank 2)
        $dispatchB = HelpDispatch::where('help_id', $help1->id)->where('mitra_id', $this->mitraB->id)->first();
        $this->assertNotNull($dispatchB);
        $this->assertEquals(HelpDispatch::STATUS_OFFERED, $dispatchB->status);

        $stateB = PartnerOnlineState::where('user_id', $this->mitraB->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_OFFER_PENDING, $stateB->matching_status);
        $this->assertEquals($help1->id, $stateB->current_help_id);
    }

    /**
     * TEST D:
     * Penerimaan tawaran (Accept Offer) pada batas kadaluarsa.
     * Expected:
     * - Jika tawaran sudah kedaluwarsa melewati grace period, ditolak tegas dan diubah ke EXPIRED (tidak boleh ACCEPTED).
     * - Jika tawaran diterima dalam batas valid, diubah ke ACCEPTED (tidak boleh di-overwrite EXPIRED kemudian).
     * - Kedua status bersifat mutually exclusive.
     */
    public function test_d_accept_offer_at_expiry_boundary_is_strictly_mutually_exclusive()
    {
        $help = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->city->id,
            'title'          => 'Help Expiry Boundary',
            'description'    => 'Tes mutual exclusive expiry vs accept',
            'amount'         => 75000,
            'admin_fee'      => 7500,
            'total_amount'   => 82500,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_OFFERED,
        ]);

        $this->onlineService->startSearching($this->mitraA, -7.7956, 110.3695);
        $stateA = PartnerOnlineState::where('user_id', $this->mitraA->id)->first();
        $stateA->update([
            'matching_status' => PartnerOnlineState::STATUS_OFFER_PENDING,
            'current_help_id' => $help->id,
        ]);

        // Kasus 1: Dispatch sudah kadaluarsa 10 detik lalu (melewati 2s grace window)
        $expiredDispatch = HelpDispatch::create([
            'help_id'        => $help->id,
            'mitra_id'       => $this->mitraA->id,
            'round'          => 1,
            'rank'           => 1,
            'status'         => HelpDispatch::STATUS_OFFERED,
            'offered_at'     => now()->subSeconds(60),
            'expires_at'     => now()->subSeconds(10),
            'score_snapshot' => ['distance' => 1.0, 'total_score' => 0.95],
        ]);

        $expiredException = false;
        try {
            $this->matchingService->acceptOffer($expiredDispatch->id, $this->mitraA);
        } catch (\RuntimeException $e) {
            $expiredException = true;
            $this->assertStringContainsString('Waktu respon penawaran telah habis', $e->getMessage());
        }

        $this->assertTrue($expiredException);
        $expiredDispatch->refresh();
        $this->assertEquals(HelpDispatch::STATUS_EXPIRED, $expiredDispatch->status);
        $this->assertNull($help->fresh()->mitra_id);

        // Kasus 2: Dispatch baru yang valid
        $helpValid = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->city->id,
            'title'          => 'Help Valid Offer',
            'description'    => 'Tes valid offer acceptance',
            'amount'         => 75000,
            'admin_fee'      => 7500,
            'total_amount'   => 82500,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_OFFERED,
        ]);

        PartnerOnlineState::where('user_id', $this->mitraA->id)->update([
            'matching_status' => PartnerOnlineState::STATUS_OFFER_PENDING,
            'current_help_id' => $helpValid->id,
            'last_seen_at'    => now(),
        ]);

        $validDispatch = HelpDispatch::create([
            'help_id'        => $helpValid->id,
            'mitra_id'       => $this->mitraA->id,
            'round'          => 1,
            'rank'           => 1,
            'status'         => HelpDispatch::STATUS_OFFERED,
            'offered_at'     => now(),
            'expires_at'     => now()->addSeconds(45),
            'score_snapshot' => ['distance' => 1.0, 'total_score' => 0.95],
        ]);

        $acceptedHelp = $this->matchingService->acceptOffer($validDispatch->id, $this->mitraA);

        $validDispatch->refresh();
        $this->assertEquals(HelpDispatch::STATUS_ACCEPTED, $validDispatch->status);
        $this->assertEquals($this->mitraA->id, $acceptedHelp->mitra_id);
        $this->assertEquals(Help::STATUS_TAKEN, $acceptedHelp->status);

        // Coba jalankan handleExpiry paksa pada dispatch yang sudah ACCEPTED -> Tidak boleh mengubah status ke EXPIRED
        $this->matchingService->handleExpiry($validDispatch->id, true);
        $validDispatch->refresh();
        $this->assertEquals(HelpDispatch::STATUS_ACCEPTED, $validDispatch->status, 'Dispatch yang sudah ACCEPTED tidak boleh diubah menjadi EXPIRED');
    }
}
