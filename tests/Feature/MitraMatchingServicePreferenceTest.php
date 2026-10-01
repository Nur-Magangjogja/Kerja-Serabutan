<?php

namespace Tests\Feature;

use App\Livewire\Mitra\Dashboard\OfferRadarWidget;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpDispatch;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\HelpCreationService;
use App\Services\HelpMatchingService;
use App\Services\PartnerOnlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MitraMatchingServicePreferenceTest extends TestCase
{
    use RefreshDatabase;

    protected City $city;
    protected District $district;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::create([
            'name'      => 'Yogyakarta',
            'latitude'  => -7.7956,
            'longitude' => 110.3695,
        ]);

        $this->district = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Danurejan',
            'latitude'  => -7.7956,
            'longitude' => 110.3695,
        ]);

        $this->customer = User::factory()->create([
            'role'        => 'customer',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
            'verified'    => true,
        ]);

        PartnerOnlineService::clearStateCache();
    }

    public function test_partner_online_service_preference_validation_and_update(): void
    {
        PartnerOnlineService::clearStateCache();
        $service = app(PartnerOnlineService::class);

        // 1. Mitra tanpa verifikasi kendaraan
        $mitraUnverified = User::factory()->create([
            'role'   => 'mitra',
            'status' => 'active',
        ]);

        // Default preference is 'all'
        $stateUnverified = $service->getOrCreateState($mitraUnverified);
        $this->assertEquals(PartnerOnlineState::PREFERENCE_ALL, $stateUnverified->service_preference);

        // Mitra unverified mencoba memilih pickup_delivery -> harus ditolak
        $result = $service->updateServicePreference($mitraUnverified, PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('SIM, dan STNK', $result['message']);

        // Mitra unverified memilih on_site_service -> berhasil
        $result = $service->updateServicePreference($mitraUnverified, PartnerOnlineState::PREFERENCE_ON_SITE);
        $this->assertTrue($result['success']);
        $this->assertEquals(PartnerOnlineState::PREFERENCE_ON_SITE, $result['preference']);

        // 2. Mitra dengan kendaraan lengkap & terverifikasi
        $mitraVerified = User::factory()->create([
            'role'                        => 'mitra',
            'status'                      => 'active',
            'vehicle_plate_number'        => 'AB 1234 CD',
            'vehicle_sim_number'          => '123456789012',
            'vehicle_sim_photo'           => 'vehicles/sim/sim.jpg',
            'vehicle_stnk_number'         => '987654321098',
            'vehicle_stnk_photo'          => 'vehicles/stnk/stnk.jpg',
            'vehicle_verified'            => true,
            'vehicle_verification_status' => 'verified',
        ]);

        $this->assertTrue($mitraVerified->canTakePickupDelivery());

        // Mitra verified memilih pickup_delivery -> sukses
        $resultVerified = $service->updateServicePreference($mitraVerified, PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY);
        $this->assertTrue($resultVerified['success']);
        $this->assertEquals(PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY, $resultVerified['preference']);

        // Mitra verified memilih all -> sukses
        $resultAll = $service->updateServicePreference($mitraVerified, PartnerOnlineState::PREFERENCE_ALL);
        $this->assertTrue($resultAll['success']);
        $this->assertEquals(PartnerOnlineState::PREFERENCE_ALL, $resultAll['preference']);
    }

    public function test_matching_radar_filters_candidates_by_service_preference(): void
    {
        PartnerOnlineService::clearStateCache();
        $matchingService = app(HelpMatchingService::class);
        $onlineService   = app(PartnerOnlineService::class);

        // Mitra 1: Preferensi Khusus On-Site (Kerja Serabutan Saja)
        $mitraOnSiteOnly = User::factory()->create([
            'role'        => 'mitra',
            'status'      => 'active',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);
        $state1 = $onlineService->getOrCreateState($mitraOnSiteOnly);
        $state1->update([
            'matching_status'    => PartnerOnlineState::STATUS_SEARCHING,
            'service_preference' => PartnerOnlineState::PREFERENCE_ON_SITE,
            'last_seen_at'       => now(),
            'latitude'           => -7.7956,
            'longitude'          => 110.3695,
        ]);

        // Mitra 2: Preferensi Khusus Antar & Jemput (Verified Kendaraan)
        $mitraPickupOnly = User::factory()->create([
            'role'                        => 'mitra',
            'status'                      => 'active',
            'city_id'                     => $this->city->id,
            'district_id'                 => $this->district->id,
            'vehicle_plate_number'        => 'AB 9999 XY',
            'vehicle_sim_number'          => '111122223333',
            'vehicle_sim_photo'           => 'vehicles/sim/sim.jpg',
            'vehicle_stnk_number'         => '444455556666',
            'vehicle_stnk_photo'          => 'vehicles/stnk/stnk.jpg',
            'vehicle_verified'            => true,
            'vehicle_verification_status' => 'verified',
        ]);
        $state2 = $onlineService->getOrCreateState($mitraPickupOnly);
        $state2->update([
            'matching_status'    => PartnerOnlineState::STATUS_SEARCHING,
            'service_preference' => PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY,
            'last_seen_at'       => now(),
            'latitude'           => -7.7956,
            'longitude'          => 110.3695,
        ]);

        // Mitra 3: Preferensi Semua Layanan (Verified Kendaraan)
        $mitraAll = User::factory()->create([
            'role'                        => 'mitra',
            'status'                      => 'active',
            'city_id'                     => $this->city->id,
            'district_id'                 => $this->district->id,
            'vehicle_plate_number'        => 'AB 7777 ZZZ',
            'vehicle_sim_number'          => '555566667777',
            'vehicle_sim_photo'           => 'vehicles/sim/sim2.jpg',
            'vehicle_stnk_number'         => '888899990000',
            'vehicle_stnk_photo'          => 'vehicles/stnk/stnk2.jpg',
            'vehicle_verified'            => true,
            'vehicle_verification_status' => 'verified',
        ]);
        $state3 = $onlineService->getOrCreateState($mitraAll);
        $state3->update([
            'matching_status'    => PartnerOnlineState::STATUS_SEARCHING,
            'service_preference' => PartnerOnlineState::PREFERENCE_ALL,
            'last_seen_at'       => now(),
            'latitude'           => -7.7956,
            'longitude'          => 110.3695,
        ]);

        // ══════════════════════════════════════════════════════════════════════
        // Skenario A: Order Kerja Serabutan (on_site_service)
        // ══════════════════════════════════════════════════════════════════════
        $helpOnSite = Help::create([
            'user_id'      => $this->customer->id,
            'title'        => 'Bantu Bersih Rumah',
            'description'  => 'Merapikan halaman dan kebun',
            'amount'       => 50000,
            'status'       => Help::STATUS_MENUNGGU_MITRA,
            'service_type' => Help::SERVICE_TYPE_ON_SITE,
            'city_id'      => $this->city->id,
            'district_id'  => $this->district->id,
            'latitude'     => -7.7956,
            'longitude'    => 110.3695,
        ]);

        $candidatesOnSite = $matchingService->getRankedCandidates($helpOnSite);
        $candidateMitraIds = $candidatesOnSite->pluck('user_id')->all();

        // Mitra OnSiteOnly & Mitra All HARUS ada
        $this->assertContains($mitraOnSiteOnly->id, $candidateMitraIds);
        $this->assertContains($mitraAll->id, $candidateMitraIds);

        // Mitra PickupOnly TIDAK BOLEH ada dalam order Kerja Serabutan
        $this->assertNotContains($mitraPickupOnly->id, $candidateMitraIds);

        // ══════════════════════════════════════════════════════════════════════
        // Skenario B: Order Antar & Jemput (pickup_delivery)
        // ══════════════════════════════════════════════════════════════════════
        $helpPickup = Help::create([
            'user_id'          => $this->customer->id,
            'title'            => 'Antar Dokumen Kantor',
            'description'      => 'Antar berkas penting ke jalan kaliurang',
            'amount'           => 35000,
            'status'           => Help::STATUS_MENUNGGU_MITRA,
            'service_type'     => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'service_category' => 'goods',
            'pickup_address'   => 'Jl. Malioboro',
            'pickup_latitude'  => -7.7956,
            'pickup_longitude' => 110.3695,
            'city_id'          => $this->city->id,
            'district_id'      => $this->district->id,
            'latitude'         => -7.7956,
            'longitude'        => 110.3695,
        ]);

        $candidatesPickup = $matchingService->getRankedCandidates($helpPickup);
        $candidatePickupMitraIds = $candidatesPickup->pluck('user_id')->all();

        // Mitra PickupOnly & Mitra All HARUS ada
        $this->assertContains($mitraPickupOnly->id, $candidatePickupMitraIds);
        $this->assertContains($mitraAll->id, $candidatePickupMitraIds);

        // Mitra OnSiteOnly TIDAK BOLEH ada dalam order Antar & Jemput
        $this->assertNotContains($mitraOnSiteOnly->id, $candidatePickupMitraIds);
    }

    public function test_livewire_offer_radar_widget_service_preference_switching(): void
    {
        PartnerOnlineService::clearStateCache();
        $mitra = User::factory()->create([
            'role'                        => 'mitra',
            'status'                      => 'active',
            'vehicle_plate_number'        => 'AB 5555 KL',
            'vehicle_sim_number'          => '112233445566',
            'vehicle_sim_photo'           => 'vehicles/sim/sim.jpg',
            'vehicle_stnk_number'         => '778899001122',
            'vehicle_stnk_photo'          => 'vehicles/stnk/stnk.jpg',
            'vehicle_verified'            => true,
            'vehicle_verification_status' => 'verified',
        ]);

        Livewire::actingAs($mitra)
            ->test(OfferRadarWidget::class)
            ->assertSet('servicePreference', PartnerOnlineState::PREFERENCE_ALL)
            ->call('setServicePreference', PartnerOnlineState::PREFERENCE_ON_SITE)
            ->assertSet('servicePreference', PartnerOnlineState::PREFERENCE_ON_SITE)
            ->assertNotDispatched('show-status-notification')
            ->call('setServicePreference', PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY)
            ->assertSet('servicePreference', PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY);

        $state = PartnerOnlineState::where('user_id', $mitra->id)->first();
        $this->assertEquals(PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY, $state->service_preference);
    }

    public function test_help_creation_service_initiates_matching_for_instant_pickup_delivery(): void
    {
        PartnerOnlineService::clearStateCache();
        $onlineService = app(PartnerOnlineService::class);

        // 1. Setup mitra terverifikasi yang sedang aktif mencari order khusus Antar & Jemput
        $mitraPickup = User::factory()->create([
            'role'                        => 'mitra',
            'status'                      => 'active',
            'city_id'                     => $this->city->id,
            'district_id'                 => $this->district->id,
            'vehicle_plate_number'        => 'AB 8888 JJ',
            'vehicle_sim_number'          => '111122223333',
            'vehicle_sim_photo'           => 'vehicles/sim/sim.jpg',
            'vehicle_stnk_number'         => '444455556666',
            'vehicle_stnk_photo'          => 'vehicles/stnk/stnk.jpg',
            'vehicle_verified'            => true,
            'vehicle_verification_status' => 'verified',
        ]);
        $state = $onlineService->getOrCreateState($mitraPickup);
        $state->update([
            'matching_status'    => PartnerOnlineState::STATUS_SEARCHING,
            'service_preference' => PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY,
            'last_seen_at'       => now(),
            'latitude'           => -7.7956,
            'longitude'          => 110.3695,
        ]);

        // 2. Setup Saldo Customer mencukupi
        UserBalance::create([
            'user_id' => $this->customer->id,
            'balance' => 200000,
        ]);

        // 3. Customer membuat order instan Antar & Jemput
        $creationService = app(HelpCreationService::class);
        $createdHelp = $creationService->createHelp($this->customer, [
            'city_id'                   => $this->city->id,
            'district_id'               => $this->district->id,
            'title'                     => 'Kirim Paket Dokumen Kantor',
            'description'               => 'Antar paket map merah ke kantor pos',
            'service_type'              => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'service_category'          => 'goods_document',
            'amount'                    => 25000,
            'route_distance_km'         => 3.0,
            'pickup_address'            => 'Jl. Malioboro No. 10',
            'pickup_latitude'           => -7.7956,
            'pickup_longitude'          => 110.3695,
            'delivery_address'          => 'Jl. Solo KM 5',
            'delivery_latitude'         => -7.7850,
            'delivery_longitude'        => 110.3900,
            'latitude'                  => -7.7956,
            'longitude'                 => 110.3695,
        ]);

        // 4. Pastikan dispatch_mode bukan POOL, melainkan OFFERED (karena tawaran sudah terkirim ke Mitra)
        $this->assertEquals(Help::DISPATCH_MODE_OFFERED, $createdHelp->fresh()->dispatch_mode);

        // 5. Pastikan status Mitra menjadi OFFER_PENDING dan ada record HelpDispatch
        $state->refresh();
        $this->assertEquals(PartnerOnlineState::STATUS_OFFER_PENDING, $state->matching_status);
        $this->assertEquals($createdHelp->id, $state->current_help_id);

        $dispatch = HelpDispatch::where('help_id', $createdHelp->id)
            ->where('mitra_id', $mitraPickup->id)
            ->first();
        $this->assertNotNull($dispatch);
        $this->assertEquals(HelpDispatch::STATUS_OFFERED, $dispatch->status);
    }

    public function test_match_pending_order_for_partner_when_mitra_starts_searching_with_pickup_delivery_filter(): void
    {
        PartnerOnlineService::clearStateCache();
        $matchingService = app(HelpMatchingService::class);
        $onlineService   = app(PartnerOnlineService::class);

        // 1. Order Antar & Jemput sudah ada di sistem dan menunggu mitra
        $help = Help::create([
            'user_id'                   => $this->customer->id,
            'order_id'                  => 'HELP-TEST-PICKUP-01',
            'title'                     => 'Antar Paket Makanan Siap Saji',
            'description'               => 'Antar catering ke lokasi acara',
            'amount'                    => 30000,
            'service_fee'               => 30000,
            'total_amount'              => 32000,
            'status'                    => Help::STATUS_MENUNGGU_MITRA,
            'order_mode'                => Help::ORDER_MODE_INSTANT,
            'dispatch_mode'             => Help::DISPATCH_MODE_POOL,
            'service_type'              => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'service_category'          => 'goods',
            'pickup_address'            => 'Jl. Kaliurang KM 4',
            'pickup_latitude'           => -7.7956,
            'pickup_longitude'          => 110.3695,
            'delivery_address'          => 'Jl. Affandi No. 12',
            'delivery_latitude'         => -7.7800,
            'delivery_longitude'        => 110.3700,
            'city_id'                   => $this->city->id,
            'district_id'               => $this->district->id,
            'latitude'                  => -7.7956,
            'longitude'                 => 110.3695,
            'service_route_distance_km' => 2.5,
        ]);

        // 2. Mitra verified baru mengaktifkan mode mencari order dengan filter Antar-Jemput
        $mitra = User::factory()->create([
            'role'                        => 'mitra',
            'status'                      => 'active',
            'city_id'                     => $this->city->id,
            'district_id'                 => $this->district->id,
            'vehicle_plate_number'        => 'AB 1111 AB',
            'vehicle_sim_number'          => '998877665544',
            'vehicle_sim_photo'           => 'vehicles/sim/sim.jpg',
            'vehicle_stnk_number'         => '112233445566',
            'vehicle_stnk_photo'          => 'vehicles/stnk/stnk.jpg',
            'vehicle_verified'            => true,
            'vehicle_verification_status' => 'verified',
        ]);

        $state = $onlineService->getOrCreateState($mitra);
        $state->update([
            'matching_status'    => PartnerOnlineState::STATUS_SEARCHING,
            'service_preference' => PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY,
            'last_seen_at'       => now(),
            'latitude'           => -7.7956,
            'longitude'          => 110.3695,
        ]);

        // 3. Eksekusi matchPendingOrderForPartner
        $matched = $matchingService->matchPendingOrderForPartner($mitra);

        $this->assertNotNull($matched);
        $this->assertEquals($help->id, $matched->id);

        // 4. Verifikasi status tawaran terkirim ke mitra
        $state->refresh();
        $this->assertEquals(PartnerOnlineState::STATUS_OFFER_PENDING, $state->matching_status);
        $this->assertEquals($help->id, $state->current_help_id);

        $this->assertTrue(HelpDispatch::where('help_id', $help->id)->where('mitra_id', $mitra->id)->exists());
    }

    public function test_partner_cannot_change_filter_when_offer_is_pending(): void
    {
        PartnerOnlineService::clearStateCache();
        $onlineService = app(PartnerOnlineService::class);

        $mitra = User::factory()->create([
            'role'                        => 'mitra',
            'status'                      => 'active',
            'city_id'                     => $this->city->id,
            'district_id'                 => $this->district->id,
            'vehicle_plate_number'        => 'AB 2222 BB',
            'vehicle_sim_number'          => '222233334444',
            'vehicle_sim_photo'           => 'vehicles/sim/sim.jpg',
            'vehicle_stnk_number'         => '555566667777',
            'vehicle_stnk_photo'          => 'vehicles/stnk/stnk.jpg',
            'vehicle_verified'            => true,
            'vehicle_verification_status' => 'verified',
        ]);

        $help = Help::create([
            'user_id'          => $this->customer->id,
            'title'            => 'Bantu Angkat Barang',
            'description'      => 'Pindahan kosan',
            'amount'           => 40000,
            'status'           => Help::STATUS_MENUNGGU_MITRA,
            'service_type'     => Help::SERVICE_TYPE_ON_SITE,
            'city_id'          => $this->city->id,
            'district_id'      => $this->district->id,
            'latitude'         => -7.7956,
            'longitude'        => 110.3695,
        ]);

        $state = $onlineService->getOrCreateState($mitra);
        $state->update([
            'matching_status'    => PartnerOnlineState::STATUS_OFFER_PENDING,
            'current_help_id'    => $help->id,
            'service_preference' => PartnerOnlineState::PREFERENCE_ALL,
            'last_seen_at'       => now(),
        ]);

        HelpDispatch::create([
            'help_id'    => $help->id,
            'mitra_id'   => $mitra->id,
            'round'      => 1,
            'rank'       => 1,
            'status'     => HelpDispatch::STATUS_OFFERED,
            'offered_at' => now(),
            'expires_at' => now()->addSeconds(30),
        ]);

        // Coba ubah preferensi saat status offer_pending -> harus ditolak
        $result = $onlineService->updateServicePreference($mitra, PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Filter bantuan terkunci saat sedang ada tawaran masuk', $result['message']);

        $state->refresh();
        $this->assertEquals(PartnerOnlineState::PREFERENCE_ALL, $state->service_preference);
    }

    public function test_partner_cannot_change_filter_when_busy_with_active_task(): void
    {
        PartnerOnlineService::clearStateCache();
        $onlineService = app(PartnerOnlineService::class);

        $mitra = User::factory()->create([
            'role'                        => 'mitra',
            'status'                      => 'active',
            'city_id'                     => $this->city->id,
            'district_id'                 => $this->district->id,
            'vehicle_plate_number'        => 'AB 3333 CC',
            'vehicle_sim_number'          => '333344445555',
            'vehicle_sim_photo'           => 'vehicles/sim/sim.jpg',
            'vehicle_stnk_number'         => '666677778888',
            'vehicle_stnk_photo'          => 'vehicles/stnk/stnk.jpg',
            'vehicle_verified'            => true,
            'vehicle_verification_status' => 'verified',
        ]);

        $activeHelp = Help::create([
            'user_id'          => $this->customer->id,
            'mitra_id'         => $mitra->id,
            'title'            => 'Sedang Dikerjakan',
            'description'      => 'Tugas berjalan',
            'amount'           => 50000,
            'status'           => Help::STATUS_IN_PROGRESS,
            'service_type'     => Help::SERVICE_TYPE_ON_SITE,
            'city_id'          => $this->city->id,
            'district_id'      => $this->district->id,
            'latitude'         => -7.7956,
            'longitude'        => 110.3695,
        ]);

        $state = $onlineService->getOrCreateState($mitra);
        $state->update([
            'matching_status'    => PartnerOnlineState::STATUS_BUSY,
            'current_help_id'    => $activeHelp->id,
            'service_preference' => PartnerOnlineState::PREFERENCE_ON_SITE,
            'last_seen_at'       => now(),
        ]);

        // Coba ubah preferensi saat status busy -> harus ditolak
        $result = $onlineService->updateServicePreference($mitra, PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Filter bantuan terkunci saat sedang bertugas', $result['message']);

        $state->refresh();
        $this->assertEquals(PartnerOnlineState::PREFERENCE_ON_SITE, $state->service_preference);
    }

    public function test_livewire_radar_widget_disallows_filter_change_when_offer_pending_and_unlocks_when_rejected(): void
    {
        PartnerOnlineService::clearStateCache();
        $onlineService = app(PartnerOnlineService::class);

        $mitra = User::factory()->create([
            'role'                        => 'mitra',
            'status'                      => 'active',
            'city_id'                     => $this->city->id,
            'district_id'                 => $this->district->id,
            'vehicle_plate_number'        => 'AB 4444 DD',
            'vehicle_sim_number'          => '444455556666',
            'vehicle_sim_photo'           => 'vehicles/sim/sim.jpg',
            'vehicle_stnk_number'         => '777788889999',
            'vehicle_stnk_photo'          => 'vehicles/stnk/stnk.jpg',
            'vehicle_verified'            => true,
            'vehicle_verification_status' => 'verified',
        ]);

        $help = Help::create([
            'user_id'          => $this->customer->id,
            'title'            => 'Antar Paket Cepat',
            'description'      => 'Antar berkas',
            'amount'           => 30000,
            'status'           => Help::STATUS_MENUNGGU_MITRA,
            'service_type'     => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'city_id'          => $this->city->id,
            'district_id'      => $this->district->id,
            'latitude'         => -7.7956,
            'longitude'        => 110.3695,
            'pickup_latitude'  => -7.7956,
            'pickup_longitude' => 110.3695,
        ]);

        $state = $onlineService->getOrCreateState($mitra);
        $state->update([
            'matching_status'    => PartnerOnlineState::STATUS_OFFER_PENDING,
            'current_help_id'    => $help->id,
            'service_preference' => PartnerOnlineState::PREFERENCE_ALL,
            'last_seen_at'       => now(),
        ]);

        $dispatch = HelpDispatch::create([
            'help_id'    => $help->id,
            'mitra_id'   => $mitra->id,
            'round'      => 1,
            'rank'       => 1,
            'status'     => HelpDispatch::STATUS_OFFERED,
            'offered_at' => now(),
            'expires_at' => now()->addSeconds(30),
        ]);

        // 1. Coba ubah filter melalui Livewire saat ada tawaran aktif -> harus diblokir
        $component = Livewire::actingAs($mitra)
            ->test(OfferRadarWidget::class)
            ->call('setServicePreference', PartnerOnlineState::PREFERENCE_ON_SITE)
            ->assertSet('servicePreference', PartnerOnlineState::PREFERENCE_ALL)
            ->assertDispatched('show-status-notification');

        $state->refresh();
        $this->assertEquals(PartnerOnlineState::PREFERENCE_ALL, $state->service_preference);

        // 2. Tolak tawaran (reject offer)
        $component->call('rejectOffer', $dispatch->id);

        $state->refresh();
        $this->assertNotEquals(PartnerOnlineState::STATUS_OFFER_PENDING, $state->matching_status);

        // 3. Setelah tawaran selesai/ditolak, filter kini BISA diubah dengan lancar
        $component->call('setServicePreference', PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY)
            ->assertSet('servicePreference', PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY);

        $state->refresh();
        $this->assertEquals(PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY, $state->service_preference);
    }

    public function test_livewire_radar_widget_immediately_dispatches_offer_found_when_filter_matches_pending_order(): void
    {
        PartnerOnlineService::clearStateCache();
        $onlineService = app(PartnerOnlineService::class);

        $mitra = User::factory()->create([
            'role'                        => 'mitra',
            'status'                      => 'active',
            'city_id'                     => $this->city->id,
            'district_id'                 => $this->district->id,
            'vehicle_plate_number'        => 'AB 6666 ZZ',
            'vehicle_sim_number'          => '666677778888',
            'vehicle_sim_photo'           => 'vehicles/sim/sim.jpg',
            'vehicle_stnk_number'         => '999900001111',
            'vehicle_stnk_photo'          => 'vehicles/stnk/stnk.jpg',
            'vehicle_verified'            => true,
            'vehicle_verification_status' => 'verified',
        ]);

        $state = $onlineService->getOrCreateState($mitra);
        $state->update([
            'matching_status'    => PartnerOnlineState::STATUS_SEARCHING,
            'service_preference' => PartnerOnlineState::PREFERENCE_ON_SITE,
            'last_seen_at'       => now(),
            'latitude'           => -7.7956,
            'longitude'          => 110.3695,
        ]);

        // Buat pesanan Antar-Jemput yang sedang menunggu mitra
        $helpPickup = Help::create([
            'user_id'                   => $this->customer->id,
            'title'                     => 'Kirim Barang Segera',
            'description'               => 'Antar paket makanan',
            'amount'                    => 35000,
            'service_fee'               => 35000,
            'total_amount'              => 37000,
            'status'                    => Help::STATUS_MENUNGGU_MITRA,
            'order_mode'                => Help::ORDER_MODE_INSTANT,
            'dispatch_mode'             => Help::DISPATCH_MODE_POOL,
            'service_type'              => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'service_category'          => 'goods',
            'pickup_address'            => 'Jl. Gejayan No. 5',
            'pickup_latitude'           => -7.7956,
            'pickup_longitude'          => 110.3695,
            'delivery_address'          => 'Jl. Kaliurang KM 5',
            'delivery_latitude'         => -7.7800,
            'delivery_longitude'        => 110.3700,
            'city_id'                   => $this->city->id,
            'district_id'               => $this->district->id,
            'latitude'                  => -7.7956,
            'longitude'                 => 110.3695,
            'service_route_distance_km' => 3.0,
        ]);

        // Mitra saat ini dengan filter on_site belum mencocokkan order pickup
        $state->refresh();
        $this->assertEquals(PartnerOnlineState::STATUS_SEARCHING, $state->matching_status);

        // Ketika mitra mengubah filter ke pickup_delivery, radar seketika mencocokkan order dan memicu offer-found
        Livewire::actingAs($mitra)
            ->test(OfferRadarWidget::class)
            ->call('setServicePreference', PartnerOnlineState::PREFERENCE_PICKUP_DELIVERY)
            ->assertDispatched('offer-found')
            ->assertNotDispatched('show-status-notification')
            ->assertDispatched('$refresh');

        $state->refresh();
        $this->assertEquals(PartnerOnlineState::STATUS_OFFER_PENDING, $state->matching_status);
        $this->assertEquals($helpPickup->id, $state->current_help_id);
    }
}
