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
        $this->assertInstanceOf(Help::class, $accepted);

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

    public function test_partner_cancellation_accepted_releases_busy_mode_and_allows_searching_again()
    {
        // 1. Mitra 1 accepts help -> becomes BUSY
        $this->onlineService->startSearching($this->mitra1, -7.7960, 110.3700);
        $help = $this->createHelp();
        $this->matchingService->initiateMatching($help);
        $dispatch = HelpDispatch::where('help_id', $help->id)->where('rank', 1)->first();
        $this->matchingService->acceptOffer($dispatch->id, $this->mitra1);
        $help->refresh();

        $state1 = PartnerOnlineState::where('user_id', $this->mitra1->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_BUSY, $state1->matching_status);

        // 2. Mitra 1 requests cancellation
        $txService = app(\App\Services\HelpTransactionService::class);
        $txService->requestPartnerCancel($help, $this->mitra1, 'Motor mogok di jalan');

        $help->refresh();
        $this->assertEquals(Help::STATUS_PARTNER_CANCEL_REQUESTED, $help->status);

        // 3. Customer accepts cancellation
        $txService->customerAcceptCancel($help, $this->customer);

        // 4. Verify Help is returned to pool
        $help->refresh();
        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);
        $this->assertEquals(Help::DISPATCH_MODE_POOL, $help->dispatch_mode);
        $this->assertNull($help->mitra_id);

        // 5. Verify Mitra 1 is NO LONGER BUSY and can immediately search for new orders
        $state1->refresh();
        $this->assertNotEquals(PartnerOnlineState::STATUS_BUSY, $state1->matching_status);
        $this->assertNull($state1->current_help_id);

        // Mitra 1 starts searching again -> should succeed without any runtime exception
        $searching = $this->onlineService->startSearching($this->mitra1, -7.7960, 110.3700);
        $this->assertTrue($searching);

        $state1->refresh();
        $this->assertEquals(PartnerOnlineState::STATUS_SEARCHING, $state1->matching_status);
    }

    public function test_two_consecutive_rejections_or_timeouts_demotes_mitra_to_online_standby()
    {
        // 1. Mitra 1 starts searching
        $this->onlineService->startSearching($this->mitra1, -7.7960, 110.3700);

        // Order 1: Mitra 1 rejects offer (Decline 1)
        $help1 = $this->createHelp();
        $this->matchingService->initiateMatching($help1);
        $dispatch1 = HelpDispatch::where('help_id', $help1->id)->where('rank', 1)->first();
        $this->matchingService->rejectOffer($dispatch1->id, $this->mitra1);

        $state1 = PartnerOnlineState::where('user_id', $this->mitra1->id)->first();
        // Masih searching setelah 1x tolak
        $this->assertEquals(PartnerOnlineState::STATUS_SEARCHING, $state1->matching_status);
        $this->assertEquals(1, $state1->consecutive_declines);

        // Order 2: Mitra 1 ignores offer until expiry (Decline 2)
        $help2 = $this->createHelp();
        $this->matchingService->initiateMatching($help2);
        $dispatch2 = HelpDispatch::where('help_id', $help2->id)->where('rank', 1)->first();
        $this->matchingService->handleExpiry($dispatch2->id, force: true);

        $state1->refresh();
        // Demosi ke ONLINE (Standby) dan reset timer antrean setelah 2x tolak/abaikan
        $this->assertEquals(PartnerOnlineState::STATUS_ONLINE, $state1->matching_status);
        $this->assertNull($state1->searching_since);
        $this->assertEquals(0, $state1->consecutive_declines);

        // Mitra harus menekan tombol Cari Order lagi secara sadar
        $this->onlineService->startSearching($this->mitra1, -7.7960, 110.3700);
        $state1->refresh();
        $this->assertEquals(PartnerOnlineState::STATUS_SEARCHING, $state1->matching_status);
    }

    public function test_customer_cannot_raise_dispute_or_claim_refund_after_dispute_resolved_or_escrow_released()
    {
        $txService = app(\App\Services\HelpTransactionService::class);

        // 1. Buat pesanan dan proses sampai menunggu konfirmasi
        $help = $this->createHelp(100000, 10000);
        $help->update([
            'mitra_id'      => $this->mitra1->id,
            'status'        => Help::STATUS_WAITING_CONFIRMATION,
            'escrow_status' => Help::ESCROW_STATUS_HELD,
        ]);

        // 2. Customer mengajukan sengketa
        $report = $txService->raiseDispute($help, $this->customer, 'Pekerjaan belum rapi');
        $help->refresh();
        $this->assertEquals(Help::ESCROW_STATUS_DISPUTED_FREEZE, $help->escrow_status);

        // 3. Admin menyelesaikan sengketa dengan Full Release ke Mitra
        $txService->resolveDispute($help, $this->admin, 'full_release');
        $help->refresh();
        $this->assertEquals(Help::STATUS_SELESAI, $help->status);
        $this->assertEquals(Help::ESCROW_STATUS_RELEASED, $help->escrow_status);
        $this->assertNotNull($help->dispute_resolved_at);

        // 4. Pastikan customer TIDAK BISA mengajukan sengketa kedua pada pesanan yang sudah diputuskan
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Sengketa untuk pesanan ini telah diputuskan oleh Admin secara final');
        $txService->raiseDispute($help, $this->customer, 'Saya masih tidak puas');
    }

    public function test_process_report_refund_blocks_double_refund_on_released_help()
    {
        $txService = app(\App\Services\HelpTransactionService::class);

        // Buat pesanan yang sudah selesai & dirilis ke Mitra
        $help = $this->createHelp(100000, 10000);
        $help->update([
            'mitra_id'            => $this->mitra1->id,
            'status'              => Help::STATUS_SELESAI,
            'escrow_status'       => Help::ESCROW_STATUS_RELEASED,
            'dispute_resolved_at' => now(),
            'dispute_resolved_by' => $this->admin->id,
        ]);

        // Buat laporan manual
        $report = \App\Models\PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra1->id,
            'reported_help_id' => $help->id,
            'report_type'      => 'klaim_refund_pekerjaan_fiktif',
            'title'            => 'Klaim Refund Ulang',
            'message'          => 'Ingin refund lagi setelah sengketa selesai',
            'refund_status'    => 'requested',
            'refund_amount'    => 110000,
            'status'           => 'pending',
        ]);

        // Pastikan Admin ditolak jika mencoba memproses refund pada bantuan yang dananya sudah dicairkan ke Mitra
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Dana escrow untuk bantuan ini telah dicairkan ke Mitra');
        $txService->processReportRefund($report, $this->admin, 'Coba refund');
    }

    public function test_customer_can_claim_1x24_warranty_on_completed_order_and_claws_back_mitra_escrow()
    {
        $txService = app(\App\Services\HelpTransactionService::class);

        // 1. Mitra mengambil pesanan Rp 100.000 (net earning 90.000)
        $help = $this->createHelp(90000, 10000);
        $help->update([
            'mitra_id'      => $this->mitra1->id,
            'status'        => Help::STATUS_WAITING_CONFIRMATION,
            'escrow_status' => Help::ESCROW_STATUS_HELD,
        ]);

        // Customer mengonfirmasi pesanan selesai
        $txService->customerConfirmCompletion($help, $this->customer);
        $help->refresh();
        $this->assertEquals(Help::STATUS_SELESAI, $help->status);
        $this->assertEquals(Help::ESCROW_STATUS_RELEASED, $help->escrow_status);

        $mitraBalance = UserBalance::where('user_id', $this->mitra1->id)->first();
        $this->assertEquals(90000, $mitraBalance->balance);

        // 2. Customer baru sadar ada masalah, mengajukan klaim garansi 1x24 jam
        $report = $txService->claimWarrantyAndClawbackEscrow(
            $help,
            $this->customer,
            'Pekerjaan ternyata fiktif setelah saya cek 2 jam kemudian'
        );

        // Verifikasi dana earning mitra ditarik kembali (clawback)
        $mitraBalance->refresh();
        $this->assertEquals(0, $mitraBalance->balance);

        // Verifikasi status bantuan kembali ke disputed_freeze
        $help->refresh();
        $this->assertEquals(Help::STATUS_WAITING_CONFIRMATION, $help->status);
        $this->assertEquals(Help::ESCROW_STATUS_DISPUTED_FREEZE, $help->escrow_status);

        // Verifikasi tiket laporan PartnerReport dibuat
        $this->assertEquals('pending', $report->status);
        $this->assertEquals('requested', $report->refund_status);

        // 3. Admin memediasi dan memutuskan Full Release ke Mitra
        $txService->resolveDispute($help, $this->admin, 'full_release');

        // Verifikasi dana dikreditkan kembali ke Mitra
        $mitraBalance->refresh();
        $this->assertEquals(90000, $mitraBalance->balance);

        $help->refresh();
        $this->assertEquals(Help::STATUS_SELESAI, $help->status);
        $this->assertEquals(Help::ESCROW_STATUS_RELEASED, $help->escrow_status);
        $this->assertNotNull($help->dispute_resolved_at);
    }

    public function test_customer_cannot_claim_warranty_after_24_hours()
    {
        $txService = app(\App\Services\HelpTransactionService::class);

        // Pesanan yang diselesaikan 25 jam yang lalu
        $help = $this->createHelp(90000, 10000);
        $help->update([
            'mitra_id'      => $this->mitra1->id,
            'status'        => Help::STATUS_SELESAI,
            'escrow_status' => Help::ESCROW_STATUS_RELEASED,
            'completed_at'  => now()->subHours(25),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Masa garansi asuransi 1x24 jam untuk pesanan ini telah berakhir');
        $txService->claimWarrantyAndClawbackEscrow($help, $this->customer, 'Klaim terlambat');
    }

    public function test_three_consecutive_1_star_ratings_automatically_flags_mitra_to_greylist()
    {
        // 1. Buat 3 pesanan terpisah untuk Mitra 1
        $help1 = $this->createHelp();
        $help1->update(['mitra_id' => $this->mitra1->id, 'status' => Help::STATUS_SELESAI]);

        $help2 = $this->createHelp();
        $help2->update(['mitra_id' => $this->mitra1->id, 'status' => Help::STATUS_SELESAI]);

        $help3 = $this->createHelp();
        $help3->update(['mitra_id' => $this->mitra1->id, 'status' => Help::STATUS_SELESAI]);

        $this->assertFalse((bool) $this->mitra1->is_greylisted);

        // Rating 1: 1 Bintang
        Rating::create([
            'help_id'  => $help1->id,
            'rater_id' => $this->customer->id,
            'ratee_id' => $this->mitra1->id,
            'type'     => 'customer_to_mitra',
            'rating'   => 1,
            'review'   => 'Kurang memuaskan 1',
        ]);
        $this->mitra1->refresh();
        $this->assertFalse((bool) $this->mitra1->is_greylisted);

        // Rating 2: 1 Bintang
        Rating::create([
            'help_id'  => $help2->id,
            'rater_id' => $this->customer->id,
            'ratee_id' => $this->mitra1->id,
            'type'     => 'customer_to_mitra',
            'rating'   => 1,
            'review'   => 'Kurang memuaskan 2',
        ]);
        $this->mitra1->refresh();
        $this->assertFalse((bool) $this->mitra1->is_greylisted);

        // Rating 3: 1 Bintang (Memicu 3x berturut-turut)
        Rating::create([
            'help_id'  => $help3->id,
            'rater_id' => $this->customer->id,
            'ratee_id' => $this->mitra1->id,
            'type'     => 'customer_to_mitra',
            'rating'   => 1,
            'review'   => 'Kurang memuaskan 3',
        ]);
        $this->mitra1->refresh();

        // Verifikasi Mitra sekarang masuk ke Daftar Abu-Abu (Greylist)
        $this->assertTrue((bool) $this->mitra1->is_greylisted);
        $this->assertStringContainsString('1 bintang', $this->mitra1->greylist_reason);
        $this->assertNotNull($this->mitra1->greylisted_at);

        // Verifikasi log UserGreylistLog tercatat
        $log = \App\Models\UserGreylistLog::where('user_id', $this->mitra1->id)->latest()->first();
        $this->assertNotNull($log);
        $this->assertEquals('greylist_add', $log->action);
        $this->assertStringContainsString('1 bintang 3x berturut-turut', $log->reason);
    }
}
