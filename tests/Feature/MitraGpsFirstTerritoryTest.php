<?php

namespace Tests\Feature;

use App\Actions\Matching\MitraMatchingActions;
use App\Livewire\Mitra\Helps\AllHelps;
use App\Models\AppSetting;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpDispatch;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Services\HelpMatchingService;
use App\Services\PartnerOnlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MitraGpsFirstTerritoryTest extends TestCase
{
    use RefreshDatabase;

    protected City $cityA;
    protected District $districtA;
    protected City $cityB;
    protected District $districtB;
    protected User $customer;
    protected User $mitra;
    protected PartnerOnlineService $onlineService;
    protected HelpMatchingService $matchingService;

    protected function setUp(): void
    {
        parent::setUp();

        // City A (Kabupaten Sleman) - Coordinates around Sleman
        $this->cityA = City::create([
            'name'       => 'Kabupaten Sleman',
            'state_name' => 'DI Yogyakarta',
            'is_active'  => true,
            'latitude'   => -7.7167,
            'longitude'  => 110.3556,
        ]);

        $this->districtA = District::create([
            'city_id'   => $this->cityA->id,
            'name'      => 'Depok',
            'is_active' => true,
        ]);

        // City B (Kota Yogyakarta) - Coordinates around Yogyakarta
        $this->cityB = City::create([
            'name'       => 'Kota Yogyakarta',
            'state_name' => 'DI Yogyakarta',
            'is_active'  => true,
            'latitude'   => -7.7956,
            'longitude'  => 110.3695,
        ]);

        $this->districtB = District::create([
            'city_id'   => $this->cityB->id,
            'name'      => 'Gondomanan',
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
        ]);

        // Mitra Profile: Domiciled in City A / District A
        $this->mitra = User::factory()->create([
            'role'        => 'mitra',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->districtA->id,
            'status'      => 'active',
        ]);

        $this->onlineService = app(PartnerOnlineService::class);
        $this->matchingService = app(HelpMatchingService::class);
    }

    /**
     * Test A: Profile City A/District A + GPS City B/District B
     * Expected: Activity uses City B. Mitra gets matched to Help in City B based on runtime GPS.
     */
    public function test_a_profile_city_a_district_a_with_gps_city_b_district_b_uses_b(): void
    {
        // Mitra starts searching at City B coordinates
        $this->onlineService->startSearching($this->mitra, -7.7956, 110.3695);

        $state = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertEquals(-7.79560000, (float) $state->latitude);
        $this->assertEquals(110.36950000, (float) $state->longitude);

        // Help in City B
        $help = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->cityB->id,
            'district_id'    => $this->districtB->id,
            'title'          => 'Bantu Angkut Lemari di Kota Yogya',
            'description'    => 'Angkut lemari di Kota Yogyakarta',
            'amount'         => 50000,
            'admin_fee'      => 5000,
            'total_amount'   => 55000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_SEEKING,
            'latitude'       => -7.7960,
            'longitude'      => 110.3700,
        ]);

        $matched = $this->matchingService->initiateMatching($help);
        $this->assertTrue($matched);

        // Verification: Dispatch offer is sent to Mitra whose GPS is in City B
        $dispatch = HelpDispatch::where('help_id', $help->id)->where('mitra_id', $this->mitra->id)->first();
        $this->assertNotNull($dispatch);
        $this->assertEquals(HelpDispatch::STATUS_OFFERED, $dispatch->status);
    }

    /**
     * Test B: Profile region OFF + GPS region ON
     * Expected: Operational activity follows GPS ON. Mitra can search and match in City B.
     */
    public function test_b_profile_region_off_with_gps_region_on_operational_activity_follows_gps_on(): void
    {
        // City A (Profile region) is DEACTIVATED
        $this->cityA->update(['is_active' => false]);
        $this->districtA->update(['is_active' => false]);

        // City B (GPS region) is ACTIVE
        $this->cityB->update(['is_active' => true]);

        // Mitra starts searching at City B coordinates
        $this->onlineService->startSearching($this->mitra, -7.7956, 110.3695);

        // Help in City B
        $help = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->cityB->id,
            'district_id'    => $this->districtB->id,
            'title'          => 'Bantu Cuci AC di Yogya',
            'description'    => 'Cuci AC di Gondomanan',
            'amount'         => 60000,
            'admin_fee'      => 6000,
            'total_amount'   => 66000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_SEEKING,
            'latitude'       => -7.7956,
            'longitude'      => 110.3695,
        ]);

        $matched = $this->matchingService->initiateMatching($help);
        $this->assertTrue($matched);

        $dispatch = HelpDispatch::where('help_id', $help->id)->where('mitra_id', $this->mitra->id)->first();
        $this->assertNotNull($dispatch);
    }

    /**
     * Test C: Profile region ON + GPS region OFF
     * Expected: Mitra cannot start searching or match in region OFF.
     */
    public function test_c_profile_region_on_with_gps_region_off_no_new_matching(): void
    {
        // City A (Profile region) is ACTIVE
        $this->cityA->update(['is_active' => true]);

        // City B (GPS region) is DEACTIVATED
        $this->cityB->update(['is_active' => false]);

        // Mitra attempts to start searching at City B coordinates via action
        $res = app(MitraMatchingActions::class)->startSearching($this->mitra, -7.7956, 110.3695);

        $this->assertFalse($res['success']);
        $this->assertStringContainsString('sedang ditutup sementara demi keamanan / penataan operasional dan tidak menerima permintaan bantuan baru', $res['message']);

        // Directly via PartnerOnlineService should also throw RuntimeException
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('sedang ditutup sementara demi keamanan / penataan operasional dan tidak menerima permintaan bantuan baru');
        $this->onlineService->startSearching($this->mitra, -7.7956, 110.3695);
    }

    /**
     * Test D: GPS missing
     * Expected: No fallback to profile. Mitra is not eligible for matching or radar jobs.
     */
    public function test_d_gps_missing_no_fallback_to_profile(): void
    {
        // Create state with NO coordinates (GPS missing)
        PartnerOnlineState::create([
            'user_id'         => $this->mitra->id,
            'matching_status' => PartnerOnlineState::STATUS_SEARCHING,
            'last_seen_at'    => now(),
            'latitude'        => null,
            'longitude'       => null,
        ]);

        // Help in Mitra's profile city (City A)
        $help = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->cityA->id,
            'district_id'    => $this->districtA->id,
            'title'          => 'Bantu Taman di Sleman',
            'description'    => 'Potong rumput di Depok Sleman',
            'amount'         => 50000,
            'admin_fee'      => 5000,
            'total_amount'   => 55000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_SEEKING,
            'latitude'       => -7.7167,
            'longitude'      => 110.3556,
        ]);

        // Candidate matching should NOT match this mitra
        $candidates = $this->matchingService->getRankedCandidates($help);
        $candidatePartnerIds = $candidates->pluck('user_id')->all();
        $this->assertNotContains($this->mitra->id, $candidatePartnerIds);

        // matchPendingOrderForPartner should return null
        $pendingOrder = $this->matchingService->matchPendingOrderForPartner($this->mitra);
        $this->assertNull($pendingOrder);

        // In AllHelps Livewire, currentCityId should remain null without GPS
        $this->actingAs($this->mitra);
        Livewire::test(AllHelps::class)
            ->assertSet('currentCityId', null)
            ->assertSet('currentDistrictId', null);
    }

    /**
     * Test E: GPS stale
     * Expected: Existing freshness mechanism excludes partner from operational matching.
     */
    public function test_e_gps_stale_no_operational_matching(): void
    {
        // Partner was searching 10 minutes ago (TTL is default 60s)
        $state = PartnerOnlineState::create([
            'user_id'         => $this->mitra->id,
            'matching_status' => PartnerOnlineState::STATUS_SEARCHING,
            'latitude'        => -7.7956,
            'longitude'       => 110.3695,
            'last_seen_at'    => now()->subMinutes(10),
        ]);

        $this->assertFalse($state->isHeartbeatFresh(60));

        // Help in City B
        $help = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->cityB->id,
            'district_id'    => $this->districtB->id,
            'title'          => 'Bantu Perbaiki Kran',
            'description'    => 'Kran bocor di Gondomanan',
            'amount'         => 40000,
            'admin_fee'      => 4000,
            'total_amount'   => 44000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_SEEKING,
            'latitude'       => -7.7956,
            'longitude'      => 110.3695,
        ]);

        $matched = $this->matchingService->initiateMatching($help);
        $this->assertFalse($matched);

        $dispatch = HelpDispatch::where('help_id', $help->id)->where('mitra_id', $this->mitra->id)->first();
        $this->assertNull($dispatch);
    }

    /**
     * Test F: Help district null + city valid + Mitra GPS in same city
     * Expected: Matching still works based on coordinates/radius.
     */
    public function test_f_help_district_null_city_valid_mitra_gps_in_same_city_matching_works(): void
    {
        $this->onlineService->startSearching($this->mitra, -7.7956, 110.3695);

        // Help with known city but NULL district (W1 support)
        $help = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->cityB->id,
            'district_id'    => null, // Nullable district
            'title'          => 'Bantu Bersih Rumah (Kecamatan Belum Teridentifikasi)',
            'description'    => 'Deskripsi pekerjaan di Kota Yogyakarta',
            'amount'         => 70000,
            'admin_fee'      => 7000,
            'total_amount'   => 77000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_SEEKING,
            'latitude'       => -7.7960,
            'longitude'      => 110.3700,
        ]);

        $matched = $this->matchingService->initiateMatching($help);
        $this->assertTrue($matched);

        $dispatch = HelpDispatch::where('help_id', $help->id)->where('mitra_id', $this->mitra->id)->first();
        $this->assertNotNull($dispatch);
        $this->assertEquals(HelpDispatch::STATUS_OFFERED, $dispatch->status);
    }

    /**
     * Test G: Mitra profile coordinates near Help + runtime GPS far away
     * Expected: Profile coordinates are ignored. Mitra is eliminated because runtime GPS is far away.
     */
    public function test_g_mitra_profile_coordinates_near_help_with_runtime_gps_far_away_profile_coordinates_ignored(): void
    {
        // Mitra's User profile coordinates are set right next to Help (< 100 meters)
        $this->mitra->update([
            'latitude'  => -7.7956,
            'longitude' => 110.3695,
        ]);

        // BUT Mitra's actual runtime GPS is in Jakarta (-6.2088, 106.8456) ~ 450 KM away
        $this->onlineService->startSearching($this->mitra, -6.2088, 106.8456);

        // Help in Kota Yogyakarta
        $help = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->cityB->id,
            'district_id'    => $this->districtB->id,
            'title'          => 'Bantu Cat Pagar di Yogyakarta',
            'description'    => 'Cat pagar depan rumah',
            'amount'         => 80000,
            'admin_fee'      => 8000,
            'total_amount'   => 88000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_SEEKING,
            'latitude'       => -7.7956,
            'longitude'      => 110.3695,
        ]);

        // Candidate scoring must use runtime GPS (-6.2088, 106.8456), which exceeds max radius
        $candidates = $this->matchingService->getRankedCandidates($help);
        $candidatePartnerIds = $candidates->pluck('user_id')->all();

        $this->assertNotContains($this->mitra->id, $candidatePartnerIds);

        // Initiate matching must NOT dispatch offer to this mitra
        $matched = $this->matchingService->initiateMatching($help);
        $this->assertFalse($matched);

        $dispatch = HelpDispatch::where('help_id', $help->id)->where('mitra_id', $this->mitra->id)->first();
        $this->assertNull($dispatch);
    }

    /**
     * Test H: Direct takeHelp() STALE GPS GUARD
     * Expected: Even if profile & state coordinates are near Help, stale last_seen_at is REJECTED.
     */
    public function test_h_direct_take_help_rejected_when_gps_stale(): void
    {
        // Profile coordinates near Help
        $this->mitra->update([
            'latitude'  => -7.7956,
            'longitude' => 110.3695,
        ]);

        // PartnerOnlineState coordinates near Help, but last_seen_at is STALE (10 minutes ago)
        PartnerOnlineState::updateOrCreate(
            ['user_id' => $this->mitra->id],
            [
                'matching_status' => PartnerOnlineState::STATUS_ONLINE,
                'latitude'        => -7.7956,
                'longitude'       => 110.3695,
                'last_seen_at'    => now()->subMinutes(10),
            ]
        );

        $help = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->cityB->id,
            'district_id'    => $this->districtB->id,
            'title'          => 'Bantuan Pool Dekat Posisi Mitra',
            'description'    => 'Pekerjaan di Gondomanan',
            'amount'         => 50000,
            'admin_fee'      => 5000,
            'total_amount'   => 55000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_POOL,
            'latitude'       => -7.7956,
            'longitude'      => 110.3695,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('stale');

        try {
            app(\App\Services\HelpTransactionService::class)->takeHelp($help, $this->mitra);
        } finally {
            // Verify order is not assigned and mitra_id remains null
            $help->refresh();
            $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);
            $this->assertNull($help->mitra_id);
        }
    }

    /**
     * Test I: MISSING GPS DIRECT TAKE
     * Expected: Direct takeHelp() without coordinates is REJECTED.
     */
    public function test_i_direct_take_help_rejected_when_gps_missing(): void
    {
        // PartnerOnlineState without valid coordinates
        PartnerOnlineState::updateOrCreate(
            ['user_id' => $this->mitra->id],
            [
                'matching_status' => PartnerOnlineState::STATUS_ONLINE,
                'latitude'        => null,
                'longitude'       => null,
                'last_seen_at'    => now(),
            ]
        );

        $help = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->cityB->id,
            'district_id'    => $this->districtB->id,
            'title'          => 'Bantuan Pool GPS Missing Test',
            'description'    => 'Pekerjaan di Gondomanan',
            'amount'         => 50000,
            'admin_fee'      => 5000,
            'total_amount'   => 55000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_POOL,
            'latitude'       => -7.7956,
            'longitude'      => 110.3695,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Lokasi GPS operasional tidak tersedia');

        try {
            app(\App\Services\HelpTransactionService::class)->takeHelp($help, $this->mitra);
        } finally {
            $help->refresh();
            $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);
            $this->assertNull($help->mitra_id);
        }
    }

    /**
     * Test J: START SEARCHING STALE GPS
     * Expected: startSearching is rejected when state coordinates are stale.
     */
    public function test_j_start_searching_rejected_when_gps_stale(): void
    {
        PartnerOnlineState::updateOrCreate(
            ['user_id' => $this->mitra->id],
            [
                'matching_status' => PartnerOnlineState::STATUS_ONLINE,
                'latitude'        => -7.7956,
                'longitude'       => 110.3695,
                'last_seen_at'    => now()->subMinutes(10),
            ]
        );

        // 1. Via MitraMatchingActions: returns failure result
        $actionRes = app(MitraMatchingActions::class)->startSearching($this->mitra);
        $this->assertFalse($actionRes['success']);
        $this->assertStringContainsString('stale', $actionRes['message']);

        // 2. Via PartnerOnlineService: throws RuntimeException
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('stale');
        $this->onlineService->startSearching($this->mitra);
    }

    /**
     * Test K: ACCEPT DISPATCHED OFFER REVALIDATES FRESH GPS
     * Expected: If heartbeat becomes stale before accept, accept is REJECTED.
     */
    public function test_k_accept_dispatched_offer_rejected_when_gps_becomes_stale(): void
    {
        // 1. Initially fresh: start searching and match
        $this->onlineService->startSearching($this->mitra, -7.7956, 110.3695);

        $help = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->cityB->id,
            'district_id'    => $this->districtB->id,
            'title'          => 'Bantuan Khusus Revalidate Stale',
            'description'    => 'Pekerjaan di Gondomanan',
            'amount'         => 80000,
            'admin_fee'      => 8000,
            'total_amount'   => 88000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_SEEKING,
            'latitude'       => -7.7956,
            'longitude'      => 110.3695,
        ]);

        $this->matchingService->initiateMatching($help);

        $dispatch = HelpDispatch::where('help_id', $help->id)->where('mitra_id', $this->mitra->id)->first();
        $this->assertNotNull($dispatch);
        $this->assertEquals(HelpDispatch::STATUS_OFFERED, $dispatch->status);

        // 2. BEFORE Mitra accepts: Heartbeat becomes STALE
        PartnerOnlineState::where('user_id', $this->mitra->id)->update([
            'last_seen_at' => now()->subMinutes(10),
        ]);

        // 3. Attempting to accept offer must be rejected
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('stale');

        try {
            $this->matchingService->acceptOffer($dispatch->id, $this->mitra);
        } finally {
            $help->refresh();
            $this->assertNull($help->mitra_id);
            $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);

            $dispatch->refresh();
            $this->assertNotEquals(HelpDispatch::STATUS_ACCEPTED, $dispatch->status);
        }
    }

    /**
     * Test L: ACCEPT DISPATCHED OFFER REVALIDATES FRESH GPS RADIUS BOUNDS
     * Expected: If partner moves far away before accept, accept is REJECTED.
     */
    public function test_l_accept_dispatched_offer_rejected_when_partner_moves_far_away(): void
    {
        // 1. Initially fresh: start searching and match
        $this->onlineService->startSearching($this->mitra, -7.7956, 110.3695);

        $help = Help::create([
            'user_id'        => $this->customer->id,
            'city_id'        => $this->cityB->id,
            'district_id'    => $this->districtB->id,
            'title'          => 'Bantuan Khusus Revalidate Distance',
            'description'    => 'Pekerjaan di Gondomanan',
            'amount'         => 80000,
            'admin_fee'      => 8000,
            'total_amount'   => 88000,
            'status'         => Help::STATUS_MENUNGGU_MITRA,
            'payment_status' => Help::PAYMENT_STATUS_PAID,
            'escrow_status'  => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'  => Help::DISPATCH_MODE_SEEKING,
            'latitude'       => -7.7956,
            'longitude'      => 110.3695,
        ]);

        $this->matchingService->initiateMatching($help);

        $dispatch = HelpDispatch::where('help_id', $help->id)->where('mitra_id', $this->mitra->id)->first();
        $this->assertNotNull($dispatch);
        $this->assertEquals(HelpDispatch::STATUS_OFFERED, $dispatch->status);

        // 2. BEFORE Mitra accepts: Partner location moves far away (Jakarta, ~450 KM away), with fresh timestamp
        PartnerOnlineState::where('user_id', $this->mitra->id)->update([
            'latitude'     => -6.2088,
            'longitude'    => 106.8456,
            'last_seen_at' => now(),
        ]);

        // 3. Attempting to accept offer must be rejected due to out of radius bounds
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('di luar batas radius');

        try {
            $this->matchingService->acceptOffer($dispatch->id, $this->mitra);
        } finally {
            $help->refresh();
            $this->assertNull($help->mitra_id);
            $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);

            $dispatch->refresh();
            $this->assertNotEquals(HelpDispatch::STATUS_ACCEPTED, $dispatch->status);
        }
    }
}
