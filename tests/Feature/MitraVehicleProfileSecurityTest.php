<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\DashboardStatsService;
use App\Services\HelpTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MitraVehicleProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

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

        $this->customer = User::factory()->create([
            'role'     => 'customer',
            'city_id'  => $this->city->id,
            'verified' => true,
        ]);
    }

    public function test_vehicle_profile_validation_and_verification_rules(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);

        // 1. Data kosong sama sekali
        $this->assertFalse($mitra->hasVehicleProfile());
        $this->assertFalse($mitra->canTakePickupDelivery());

        // 2. Hanya Plat Nomor tanpa dokumen
        $mitra->vehicle_plate_number = 'AB 1234 CD';
        $this->assertFalse($mitra->hasVehicleProfile());
        $this->assertFalse($mitra->canTakePickupDelivery());

        // 3. Plat Nomor + SIM Motor saja (tanpa STNK) -> belum valid karena wajib keduanya
        $mitra->vehicle_sim_number = '123456789012';
        $mitra->vehicle_sim_photo = 'vehicles/sim/test_sim.jpg';
        $this->assertTrue($mitra->hasSimMotor());
        $this->assertFalse($mitra->hasVehicleProfile());
        $this->assertFalse($mitra->canTakePickupDelivery());

        // 4. Plat Nomor + STNK saja (tanpa SIM) -> belum valid karena wajib keduanya
        $mitraWithoutSim = User::factory()->create([
            'role'                 => 'mitra',
            'vehicle_plate_number' => 'B 9999 XYZ',
            'vehicle_stnk_number'  => 'STNK-987654',
            'vehicle_stnk_photo'   => 'vehicles/stnk/test_stnk.jpg',
        ]);
        $this->assertTrue($mitraWithoutSim->hasStnk());
        $this->assertFalse($mitraWithoutSim->hasVehicleProfile());
        $this->assertFalse($mitraWithoutSim->canTakePickupDelivery());

        // 5. Lengkap Keduanya (Plat Nomor + SIM Motor + STNK)
        $mitraWithoutSim->vehicle_sim_number = '123456789012';
        $mitraWithoutSim->vehicle_sim_photo = 'vehicles/sim/test_sim.jpg';
        $this->assertTrue($mitraWithoutSim->hasVehicleProfile());
        // Belum diverifikasi admin
        $this->assertFalse($mitraWithoutSim->canTakePickupDelivery());

        // 6. Diverifikasi Admin
        $mitraWithoutSim->vehicle_verified = true;
        $mitraWithoutSim->vehicle_verification_status = 'verified';
        $this->assertTrue($mitraWithoutSim->canTakePickupDelivery());

        // 7. Jika status rejected
        $mitraWithoutSim->vehicle_verification_status = 'rejected';
        $this->assertFalse($mitraWithoutSim->canTakePickupDelivery());
    }

    public function test_available_pool_query_hides_pickup_delivery_for_unverified_mitra(): void
    {
        $statsService = app(DashboardStatsService::class);

        // Mitra A: Tidak punya data kendaraan
        $mitraA = User::factory()->create([
            'role'     => 'mitra',
            'city_id'  => $this->city->id,
            'verified' => true,
            'status'   => 'active',
        ]);

        // Mitra B: Terverifikasi kendaraannya
        $mitraB = User::factory()->create([
            'role'                        => 'mitra',
            'city_id'                     => $this->city->id,
            'verified'                    => true,
            'status'                      => 'active',
            'vehicle_plate_number'        => 'AB 5555 ZZ',
            'vehicle_sim_number'          => '998877665544',
            'vehicle_sim_photo'           => 'vehicles/sim/photo.jpg',
            'vehicle_stnk_number'         => 'STNK-5555ZZ',
            'vehicle_stnk_photo'          => 'vehicles/stnk/photo.jpg',
            'vehicle_verification_status' => 'verified',
            'vehicle_verified'            => true,
        ]);

        // 1 order on_site_service (Kerja Serabutan / Bantuan Biasa)
        $onSiteHelp = Help::create([
            'user_id'       => $this->customer->id,
            'city_id'       => $this->city->id,
            'title'         => 'Bantu Angkat Lemari',
            'description'   => 'Bantuan biasa on-site',
            'amount'        => 50000,
            'admin_fee'     => 5000,
            'total_amount'  => 55000,
            'status'        => Help::STATUS_MENUNGGU_MITRA,
            'service_type'  => Help::SERVICE_TYPE_ON_SITE,
            'dispatch_mode' => Help::DISPATCH_MODE_POOL,
        ]);

        // 1 order pickup_delivery (Antar & Jemput)
        $pickupHelp = Help::create([
            'user_id'          => $this->customer->id,
            'city_id'          => $this->city->id,
            'title'            => 'Antar Dokumen Penting',
            'description'      => 'Pengantaran dokumen',
            'amount'           => 30000,
            'admin_fee'        => 3000,
            'total_amount'     => 33000,
            'status'           => Help::STATUS_MENUNGGU_MITRA,
            'service_type'     => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'service_category' => 'goods_document',
            'dispatch_mode'    => Help::DISPATCH_MODE_POOL,
        ]);

        // Query untuk Mitra A (belum punya kendaraan / belum terverifikasi)
        $poolA = $statsService->availablePoolQuery($mitraA)->get();
        // Hanya melihat bantuan biasa on_site, bantuan pickup_delivery DISEMBUNYIKAN!
        $this->assertEquals(1, $poolA->count());
        $this->assertEquals($onSiteHelp->id, $poolA->first()->id);
        $this->assertFalse($poolA->contains('id', $pickupHelp->id));

        // Query untuk Mitra B (terverifikasi)
        $poolB = $statsService->availablePoolQuery($mitraB)->get();
        // Melihat KEDUANYA (on_site dan pickup_delivery)
        $this->assertEquals(2, $poolB->count());
        $this->assertTrue($poolB->contains('id', $onSiteHelp->id));
        $this->assertTrue($poolB->contains('id', $pickupHelp->id));
    }

    public function test_assert_mitra_eligible_blocks_unverified_mitra_from_taking_pickup_delivery(): void
    {
        $txService = app(HelpTransactionService::class);

        $mitraUnverified = User::factory()->create([
            'role'     => 'mitra',
            'city_id'  => $this->city->id,
            'verified' => true,
            'status'   => 'active',
        ]);
        UserBalance::create(['user_id' => $mitraUnverified->id, 'balance' => 50000]);

        // Prerequisite W2 GPS-First: Mitra harus memiliki runtime GPS fresh di territory sebelum pengujian kapabilitas kendaraan
        PartnerOnlineState::create([
            'user_id'         => $mitraUnverified->id,
            'latitude'        => $this->city->latitude,
            'longitude'       => $this->city->longitude,
            'last_seen_at'    => now(),
            'matching_status' => PartnerOnlineState::STATUS_SEARCHING,
        ]);

        $onSiteHelp = Help::create([
            'user_id'       => $this->customer->id,
            'city_id'       => $this->city->id,
            'title'         => 'Bantu Pasang Lampu',
            'description'   => 'Kerja Serabutan',
            'amount'        => 40000,
            'admin_fee'     => 4000,
            'total_amount'  => 44000,
            'status'        => Help::STATUS_MENUNGGU_MITRA,
            'service_type'  => Help::SERVICE_TYPE_ON_SITE,
            'dispatch_mode' => Help::DISPATCH_MODE_POOL,
        ]);

        $pickupHelp = Help::create([
            'user_id'          => $this->customer->id,
            'city_id'          => $this->city->id,
            'title'            => 'Antar Makanan',
            'description'      => 'Pickup & Delivery',
            'amount'           => 25000,
            'admin_fee'        => 2500,
            'total_amount'     => 27500,
            'status'           => Help::STATUS_MENUNGGU_MITRA,
            'service_type'     => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'service_category' => 'goods_document',
            'dispatch_mode'    => Help::DISPATCH_MODE_POOL,
        ]);

        // 1. Mitra tanpa kendaraan BISA mengambil on_site_service (tidak diblokir)
        $exceptionThrown = false;
        try {
            $txService->assertMitraEligibleToTakeHelp($mitraUnverified, $onSiteHelp);
        } catch (\RuntimeException $e) {
            $exceptionThrown = true;
        }
        $this->assertFalse($exceptionThrown, 'Mitra tanpa kendaraan harus tetap diizinkan mengambil bantuan on_site_service');

        // 2. Mitra tanpa kendaraan DIBLOKIR saat mengambil pickup_delivery
        $blockedMessage = null;
        try {
            $txService->assertMitraEligibleToTakeHelp($mitraUnverified, $pickupHelp);
        } catch (\RuntimeException $e) {
            $blockedMessage = $e->getMessage();
        }
        $this->assertNotNull($blockedMessage);
        $this->assertStringContainsString('Plat Nomor, SIM Motor, dan STNK', $blockedMessage);

        // 3. Mitra dengan data pending juga DIBLOKIR dengan pesan verifikasi admin
        $mitraUnverified->update([
            'vehicle_plate_number'        => 'AB 1234 CD',
            'vehicle_sim_number'          => '1234567890',
            'vehicle_sim_photo'           => 'vehicles/sim/photo.jpg',
            'vehicle_stnk_number'         => 'STNK-1234CD',
            'vehicle_stnk_photo'          => 'vehicles/stnk/photo.jpg',
            'vehicle_verification_status' => 'pending',
            'vehicle_verified'            => false,
        ]);

        $pendingBlockedMessage = null;
        try {
            $txService->assertMitraEligibleToTakeHelp($mitraUnverified->fresh(), $pickupHelp);
        } catch (\RuntimeException $e) {
            $pendingBlockedMessage = $e->getMessage();
        }
        $this->assertNotNull($pendingBlockedMessage);
        $this->assertStringContainsString('sedang dalam proses verifikasi oleh Admin', $pendingBlockedMessage);

        // 4. Setelah disetujui Admin, BISA mengambil pickup_delivery!
        $mitraUnverified->update([
            'vehicle_verification_status' => 'verified',
            'vehicle_verified'            => true,
            'vehicle_verified_at'         => now(),
        ]);

        $approvedException = false;
        try {
            $txService->assertMitraEligibleToTakeHelp($mitraUnverified->fresh(), $pickupHelp);
        } catch (\RuntimeException $e) {
            $approvedException = true;
        }
        $this->assertFalse($approvedException, 'Mitra yang sudah terverifikasi kendaraannya harus diizinkan mengambil pickup_delivery');
    }
}
