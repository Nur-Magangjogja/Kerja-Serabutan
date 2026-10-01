<?php

namespace Tests\Feature\Customer;

use App\Livewire\Customer\Helps\Create;
use App\Livewire\Customer\Helps\Detail;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\HelpCancellationService;
use App\Services\HelpPricingService;
use App\Services\HelpTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PickupDeliveryDynamicPricingAndStagedCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected City $city;
    protected District $district;
    protected User $customer;
    protected User $mitra;
    protected HelpPricingService $pricingService;
    protected HelpCancellationService $cancellationService;
    protected HelpTrackingService $trackingService;

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

        $this->district = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'role'    => 'customer',
            'city_id' => $this->city->id,
        ]);
        UserBalance::create(['user_id' => $this->customer->id, 'balance' => 1000000]);

        $this->mitra = User::factory()->create([
            'role'    => 'mitra',
            'city_id' => $this->city->id,
        ]);
        UserBalance::create(['user_id' => $this->mitra->id, 'balance' => 0]);

        $this->pricingService      = app(HelpPricingService::class);
        $this->cancellationService = app(HelpCancellationService::class);
        $this->trackingService     = app(HelpTrackingService::class);
    }

    /**
     * Test Tiered Pricing Calculations for pickup_delivery
     */
    public function test_pickup_delivery_tiered_pricing_formulas()
    {
        // <= 20 km -> Base Fare 10.000 + ceil(d) * 2.500
        $this->assertEquals(12500, $this->pricingService->calculatePickupDeliveryFare(1.0)); // 10000 + 1*2500 = 12500
        $this->assertEquals(20000, $this->pricingService->calculatePickupDeliveryFare(3.8)); // 10000 + 4*2500 = 20000
        $this->assertEquals(20000, $this->pricingService->calculatePickupDeliveryFare(4.0)); // 10000 + 4*2500 = 20000
        $this->assertEquals(22500, $this->pricingService->calculatePickupDeliveryFare(4.1)); // 10000 + 5*2500 = 22500
        $this->assertEquals(30000, $this->pricingService->calculatePickupDeliveryFare(7.2)); // 10000 + 8*2500 = 30000
        $this->assertEquals(35000, $this->pricingService->calculatePickupDeliveryFare(10.0)); // 10000 + 10*2500 = 35000
        $this->assertEquals(60000, $this->pricingService->calculatePickupDeliveryFare(20.0)); // 10000 + 20*2500 = 60000

        // Long distance > 20 km -> 60.000 + (ceil(d) - 20) * 2.750
        $this->assertEquals(62750, $this->pricingService->calculatePickupDeliveryFare(20.2)); // 60000 + (21 - 20)*2750 = 62750
        $this->assertEquals(73750, $this->pricingService->calculatePickupDeliveryFare(24.5)); // 60000 + (25 - 20)*2750 = 73750
        $this->assertEquals(87500, $this->pricingService->calculatePickupDeliveryFare(30.0)); // 60000 + 10*2750 = 87500
    }

    /**
     * Test creating a pickup_delivery help via Livewire and verify exact fare + 0% partner commission
     */
    public function test_create_pickup_delivery_service_saves_correct_tiered_fare()
    {
        $this->actingAs($this->customer);

        // 14.3 KM -> 10.000 + ceil(14.3)*2500 = 10.000 + 15*2500 = 47.500
        Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_PICKUP_DELIVERY)
            ->set('title', 'Kirim Dokumen ke Kantor Sleman')
            ->set('description', 'Tolong antar dokumen penting')
            ->set('pickup_address', 'Jl. Malioboro No. 10')
            ->set('pickup_latitude', -7.7956)
            ->set('pickup_longitude', 110.3695)
            ->set('delivery_address', 'Jl. Kaliurang KM 9')
            ->set('delivery_latitude', -7.7123)
            ->set('delivery_longitude', 110.3645)
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->call('updateRouteDistanceRoad', 14.3)
            ->assertSet('route_distance_km', 14.3)
            ->assertSet('amount', 47500)
            ->call('prepareConfirm')
            ->assertSet('confirmServiceFee', 47500.0)
            ->assertSet('confirmPlatformFee', 2000.0)
            ->assertSet('confirmTotal', 49500.0)
            ->call('save')
            ->assertHasNoErrors();

        $help = Help::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($help);
        $this->assertEquals(Help::SERVICE_TYPE_PICKUP_DELIVERY, $help->service_type);
        $this->assertEquals(47500, (int) $help->service_fee);
        $this->assertEquals(2000, (int) $help->platform_fee_amount);
        $this->assertEquals(49500, (int) $help->total_amount);
    }

    /**
     * Test selecting route > 40 KM safely catches exception, displays warning and blocks confirmation
     */
    public function test_pickup_delivery_exceeding_max_distance_shows_warning_and_blocks_confirmation()
    {
        $this->actingAs($this->customer);

        Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_PICKUP_DELIVERY)
            ->set('title', 'Kirim Barang Sangat Jauh')
            ->set('description', 'Antar ke luar kota antar provinsi')
            ->set('pickup_address', 'Jl. Malioboro No. 10')
            ->set('pickup_latitude', -7.7956)
            ->set('pickup_longitude', 110.3695)
            ->set('delivery_address', 'Jl. Sudirman Jakarta')
            ->set('delivery_latitude', -6.2088)
            ->set('delivery_longitude', 106.8456)
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->call('updateRouteDistanceRoad', 1996.16)
            ->assertSet('route_distance_km', 1996.16)
            ->assertHasErrors(['delivery_address'])
            ->assertDispatched('max-distance-exceeded')
            ->call('prepareConfirm')
            ->assertHasErrors(['delivery_address'])
            ->assertSet('showConfirmModal', false);
    }

    /**
     * Test Phase 1 Cancellation: Mitra has not moved (0 km travelled)
     * Result: Customer 100% refund (fare + platform fee), Mitra Rp 0.
     */
    public function test_cancellation_phase_1_full_refund_customer_zero_partner()
    {
        $help = Help::create([
            'user_id'             => $this->customer->id,
            'mitra_id'            => $this->mitra->id,
            'title'               => 'Antar Makanan',
            'description'         => 'Desc',
            'service_type'        => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'status'              => Help::STATUS_TAKEN,
            'service_stage'       => null,
            'amount'              => 25000,
            'service_fee'         => 25000,
            'travel_fee'          => 0,
            'item_fund'           => 0,
            'platform_fee_amount' => 2000,
            'total_amount'        => 27000,
            'city_id'             => $this->city->id,
            'district_id'         => $this->district->id,
            'latitude'            => -7.7956,
            'longitude'           => 110.3695,
        ]);

        $split = $this->cancellationService->calculatePickupDeliveryCancellationSplit($help);
        $this->assertEquals(1, $split['phase']);
        $this->assertEquals(0, $split['partner_compensation']);
        $this->assertEquals(27000, $split['customer_refund']);

        $initialCustomerBal = (float) UserBalance::where('user_id', $this->customer->id)->value('balance');
        $this->cancellationService->cancelPickupDeliveryByCustomer($help, $this->customer, 'Ganti rencana');

        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->fresh()->status);
        $this->assertEquals($initialCustomerBal + 27000, (int) UserBalance::where('user_id', $this->customer->id)->value('balance'));
        $this->assertEquals(0, (int) UserBalance::where('user_id', $this->mitra->id)->value('balance'));
    }

    /**
     * Test Phase 2 Cancellation (Kondisi C): Mitra en route to pickup (e.g. 3.2 km travelled)
     * Rule: Pre-pickup travel -> 100% refund to customer (Rp 37.000), Rp 0 to Mitra (kompensasi mitra ditiadakan).
     */
    public function test_cancellation_phase_2_mitra_en_route_travel_compensation()
    {
        $help = Help::create([
            'user_id'             => $this->customer->id,
            'mitra_id'            => $this->mitra->id,
            'title'               => 'Antar Paket',
            'description'         => 'Desc',
            'service_type'        => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'status'              => Help::STATUS_PARTNER_ON_THE_WAY,
            'service_stage'       => Help::STAGE_GOING_TO_PICKUP,
            'amount'              => 35000,
            'service_fee'         => 35000,
            'travel_fee'          => 0,
            'item_fund'           => 0,
            'platform_fee_amount' => 2000,
            'total_amount'        => 37000,
            'city_id'             => $this->city->id,
            'district_id'         => $this->district->id,
            'latitude'            => -7.7956,
            'longitude'           => 110.3695,
            'travel_distance_km'  => 3.2,
        ]);

        $split = $this->cancellationService->calculatePickupDeliveryCancellationSplit($help);
        $this->assertEquals(2, $split['phase']);
        $this->assertEquals(0, $split['partner_compensation']); // Kompensasi ditiadakan
        $this->assertEquals(37000, $split['customer_refund']);  // 100% Total Paid

        $initialCustomerBal = (float) UserBalance::where('user_id', $this->customer->id)->value('balance');
        $this->cancellationService->cancelPickupDeliveryByCustomer($help, $this->customer, 'Dibatalkan saat mitra di jalan');

        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->fresh()->status);
        $this->assertEquals(0, (int) UserBalance::where('user_id', $this->mitra->id)->value('balance'));
        $this->assertEquals($initialCustomerBal + 37000, (int) UserBalance::where('user_id', $this->customer->id)->value('balance'));
    }

    /**
     * Test Phase 2 Pre-Pickup Full Refund: Both service fee and platform fee refunded to customer
     */
    public function test_cancellation_phase_2_safety_cap_never_exceeds_service_fee()
    {
        $help = Help::create([
            'user_id'             => $this->customer->id,
            'mitra_id'            => $this->mitra->id,
            'title'               => 'Antar Dekat',
            'description'         => 'Desc',
            'service_type'        => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'status'              => Help::STATUS_PARTNER_ON_THE_WAY,
            'service_stage'       => Help::STAGE_GOING_TO_PICKUP,
            'amount'              => 10000,
            'service_fee'         => 10000,
            'travel_fee'          => 0,
            'item_fund'           => 0,
            'platform_fee_amount' => 2000,
            'total_amount'        => 12000,
            'city_id'             => $this->city->id,
            'district_id'         => $this->district->id,
            'latitude'            => -7.7956,
            'longitude'           => 110.3695,
            'travel_distance_km'  => 2.0,
        ]);

        $split = $this->cancellationService->calculatePickupDeliveryCancellationSplit($help);
        $this->assertEquals(2, $split['phase']);
        $this->assertEquals(0, $split['partner_compensation']);
        $this->assertEquals(12000, $split['customer_refund']);
    }

    /**
     * Test Phase 3 Cancellation: Mitra arrived at pickup location (waiting for customer)
     * Result: Pre-pickup rule -> Mitra 0 compensation, Customer 100% refund (Rp 32.000).
     */
    public function test_cancellation_phase_3_mitra_at_pickup_receives_100_percent_service_fare()
    {
        $help = Help::create([
            'user_id'             => $this->customer->id,
            'mitra_id'            => $this->mitra->id,
            'title'               => 'Antar Berkas',
            'description'         => 'Desc',
            'service_type'        => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'status'              => Help::STATUS_PARTNER_ARRIVED,
            'service_stage'       => Help::STAGE_AT_PICKUP,
            'amount'              => 30000,
            'service_fee'         => 30000,
            'travel_fee'          => 0,
            'item_fund'           => 0,
            'platform_fee_amount' => 2000,
            'total_amount'        => 32000,
            'city_id'             => $this->city->id,
            'district_id'         => $this->district->id,
            'latitude'            => -7.7956,
            'longitude'           => 110.3695,
            'partner_arrived_at'  => now()->subMinutes(3),
            'travel_distance_km'  => 2.0,
        ]);

        $split = $this->cancellationService->calculatePickupDeliveryCancellationSplit($help);
        $this->assertEquals(3, $split['phase']);
        $this->assertEquals(0, $split['partner_compensation']); // 0 Kompensasi
        $this->assertEquals(32000, $split['customer_refund']);  // 100% Refund Customer

        $initialCustomerBal = (float) UserBalance::where('user_id', $this->customer->id)->value('balance');
        $this->cancellationService->cancelPickupDeliveryByCustomer($help, $this->customer, 'Batal di lokasi');

        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->fresh()->status);
        $this->assertEquals(0, (int) UserBalance::where('user_id', $this->mitra->id)->value('balance'));
        $this->assertEquals($initialCustomerBal + 32000, (int) UserBalance::where('user_id', $this->customer->id)->value('balance'));
    }

    /**
     * Test No-Show Trigger: Mitra waiting at pickup >= 10 minutes
     * Result: 100% service fare to Mitra, Customer loses fare.
     */
    public function test_no_show_trigger_after_10_minutes_wait()
    {
        $help = Help::create([
            'user_id'             => $this->customer->id,
            'mitra_id'            => $this->mitra->id,
            'title'               => 'Jemput Barang',
            'description'         => 'Desc',
            'service_type'        => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'status'              => Help::STATUS_PARTNER_ARRIVED,
            'service_stage'       => Help::STAGE_WAITING_FOR_CUSTOMER,
            'amount'              => 25000,
            'service_fee'         => 25000,
            'travel_fee'          => 0,
            'item_fund'           => 0,
            'platform_fee_amount' => 2000,
            'total_amount'        => 27000,
            'city_id'             => $this->city->id,
            'district_id'         => $this->district->id,
            'latitude'            => -7.7956,
            'longitude'           => 110.3695,
            'partner_arrived_at'  => now()->subMinutes(5),
        ]);

        // Still only 5 minutes -> cannot trigger no show
        $this->assertFalse($help->canPartnerTriggerNoShow());

        // Update arrived timestamp to 11 minutes ago
        $help->update([
            'partner_arrived_at' => now()->subMinutes(11),
        ]);

        $this->assertTrue($help->fresh()->canPartnerTriggerNoShow());

        // Trigger no show by mitra
        $this->cancellationService->triggerCustomerNoShow($help->fresh(), $this->mitra, 'Customer tidak dapat dihubungi selama 10 menit.');

        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->fresh()->status);
        // Mitra receives full service fare (25.000)
        $this->assertEquals(25000, (int) UserBalance::where('user_id', $this->mitra->id)->value('balance'));
    }

    /**
     * Test Stage 6 Cancellation / Completion: Post-pickup > 5 KM or Final Approach
     * Result: Button is NOT locked (canCustomerCancel is true), treated as arrived,
     * requires customer confirmation and releases 100% service fee to mitra.
     */
    public function test_stage_6_requires_customer_confirmation_and_releases_fare_to_mitra()
    {
        $stages = [
            Help::STAGE_GOING_TO_DESTINATION, // With partner > 5 KM from pickup
            Help::STAGE_FINAL_APPROACH,
        ];

        foreach ($stages as $stage) {
            $help = Help::create([
                'user_id'             => $this->customer->id,
                'mitra_id'            => $this->mitra->id,
                'title'               => "Antar Dokumen {$stage}",
                'description'         => 'Desc',
                'service_type'        => Help::SERVICE_TYPE_PICKUP_DELIVERY,
                'status'              => Help::STATUS_IN_PROGRESS,
                'service_stage'       => $stage,
                'amount'              => 40000,
                'service_fee'         => 40000,
                'travel_fee'          => 0,
                'item_fund'           => 0,
                'platform_fee_amount' => 2000,
                'total_amount'        => 42000,
                'city_id'             => $this->city->id,
                'district_id'         => $this->district->id,
                'pickup_latitude'     => -7.7956,
                'pickup_longitude'    => 110.3695,
                'latitude'            => -7.7956,
                'longitude'           => 110.3695,
                'partner_current_lat' => -7.7200, // ~8.4 KM dari titik jemput (> 5 KM)
                'partner_current_lng' => 110.3695,
            ]);

            // Model canCustomerCancel must return true (button is NOT locked)
            $this->assertTrue($help->canCustomerCancel());
            $this->assertTrue($help->isStage6Arrived());

            // Check eligibility evaluation: allowed = true, condition = F_ARRIVED_REQUIRES_CONFIRMATION
            $eligibility = app(\App\Services\Cancellation\PickupDeliveryCancellationService::class)
                ->evaluateCancellationEligibility($help);
            $this->assertTrue($eligibility['allowed']);
            $this->assertEquals('F_ARRIVED_REQUIRES_CONFIRMATION', $eligibility['condition']);

            // Financials: compensation_mitra = 100% service fee (40.000)
            $financials = app(\App\Services\Cancellation\PickupDeliveryCancellationService::class)
                ->calculateCancellationFinancials($help, $eligibility['condition']);
            $this->assertEquals(40000, $financials['compensation_mitra']);

            // Livewire Detail component should display confirmation button and handle confirmation
            $initialMitraBal = (float) UserBalance::where('user_id', $this->mitra->id)->value('balance');
            $this->actingAs($this->customer);
            Livewire::test(Detail::class, ['id' => $help->id])
                ->assertSee('Dianggap Sampai')
                ->call('cancelHelp')
                ->assertSet('showStage6ConfirmModal', true)
                ->call('confirmStage6Completion')
                ->assertSet('showStage6ConfirmModal', false);

            $this->assertEquals(Help::STATUS_DIBATALKAN, $help->fresh()->status);
            $this->assertEquals($initialMitraBal + 40000, (int) UserBalance::where('user_id', $this->mitra->id)->value('balance'));
        }
    }

    /**
     * Test Pre-Pickup Cancellation Flow: Mutual Confirmation between Customer & Mitra
     */
    public function test_pre_pickup_mutual_confirmation_cancellation_flow()
    {
        $help = Help::create([
            'user_id'             => $this->customer->id,
            'mitra_id'            => $this->mitra->id,
            'title'               => 'Antar Makanan Cepat Saji',
            'description'         => 'Desc',
            'service_type'        => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'status'              => Help::STATUS_PARTNER_ON_THE_WAY,
            'service_stage'       => Help::STAGE_GOING_TO_PICKUP,
            'amount'              => 20000,
            'service_fee'         => 20000,
            'travel_fee'          => 0,
            'item_fund'           => 0,
            'platform_fee_amount' => 2000,
            'total_amount'        => 22000,
            'city_id'             => $this->city->id,
            'district_id'         => $this->district->id,
            'latitude'            => -7.7956,
            'longitude'           => 110.3695,
        ]);

        $this->actingAs($this->customer);
        // Customer clicks cancelHelp -> opens withdrawal modal instead of instant cancel
        Livewire::test(Detail::class, ['id' => $help->id])
            ->call('cancelHelp')
            ->assertSet('showCustomerCancelModal', true)
            ->set('customerCancelReason', 'Toko tujuan sudah tutup')
            ->set('customerCancelNotes', 'Mohon batalkan pesanan')
            ->set('customerCancelPhoto', \Illuminate\Http\UploadedFile::fake()->image('bukti.jpg'))
            ->call('submitCustomerCancel')
            ->assertSet('showCustomerCancelModal', false);

        $this->assertEquals(Help::STATUS_CUSTOMER_CANCEL_REQUESTED, $help->fresh()->status);
        $req = \App\Models\HelpCancelRequest::where('help_id', $help->id)->latest()->first();
        $this->assertNotNull($req);

        // Mitra responds and confirms the withdrawal
        $initialCustBal = (float) UserBalance::where('user_id', $this->customer->id)->value('balance');
        $this->cancellationService->respondWithdrawByPartner(
            $req,
            $this->mitra,
            true, // Mitra setuju
            'Baik toko memang tutup'
        );

        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->fresh()->status);
        // Customer receives full refund (22000) and Mitra receives 0
        $this->assertEquals($initialCustBal + 22000, (int) UserBalance::where('user_id', $this->customer->id)->value('balance'));
        $this->assertEquals(0, (int) UserBalance::where('user_id', $this->mitra->id)->value('balance'));
    }

    /**
     * Test Stage 5 Cancellation: Post-pickup under 5 KM allows cancellation with compensated distance formula
     */
    public function test_stage_5_post_pickup_under_5km_cancellation()
    {
        $help = Help::create([
            'user_id'             => $this->customer->id,
            'mitra_id'            => $this->mitra->id,
            'title'               => 'Antar Paket Stage 5',
            'description'         => 'Desc',
            'service_type'        => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'status'              => Help::STATUS_IN_PROGRESS,
            'service_stage'       => Help::STAGE_GOING_TO_DESTINATION,
            'amount'              => 35000,
            'service_fee'         => 35000,
            'travel_fee'          => 0,
            'item_fund'           => 0,
            'platform_fee_amount' => 2000,
            'total_amount'        => 37000,
            'city_id'             => $this->city->id,
            'district_id'         => $this->district->id,
            'pickup_latitude'     => -7.7956,
            'pickup_longitude'    => 110.3695,
            'latitude'            => -7.7956,
            'longitude'           => 110.3695,
            'partner_initial_lat' => -7.7956,
            'partner_initial_lng' => 110.3695,
            'partner_current_lat' => -7.7800, // ~1.7 KM dari pickup (<= 5 KM)
            'partner_current_lng' => 110.3695,
            'travel_distance_km'  => 1.0,
        ]);

        $this->assertTrue($help->canCustomerCancel());
        $this->assertFalse($help->isStage6Arrived());

        $eligibility = app(\App\Services\Cancellation\PickupDeliveryCancellationService::class)
            ->evaluateCancellationEligibility($help);
        $this->assertTrue($eligibility['allowed']);
        $this->assertEquals('E_POST_PICKUP_UNDER_5KM', $eligibility['condition']);
    }

    /**
     * Test Auto Transition to Final Approach in HelpTrackingService
     */
    public function test_auto_transition_to_final_approach_on_progress_threshold()
    {
        $help = Help::create([
            'user_id'                    => $this->customer->id,
            'mitra_id'                   => $this->mitra->id,
            'title'                      => 'Antar Parcel',
            'description'                => 'Desc',
            'service_type'               => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'status'                     => Help::STATUS_IN_PROGRESS,
            'service_stage'              => Help::STAGE_GOING_TO_DESTINATION,
            'amount'                     => 30000,
            'service_fee'                => 30000,
            'travel_fee'                 => 0,
            'item_fund'                  => 0,
            'platform_fee_amount'        => 2000,
            'total_amount'               => 32000,
            'city_id'                    => $this->city->id,
            'district_id'                => $this->district->id,
            'latitude'                   => -7.7956,
            'longitude'                  => 110.3695,
            'delivery_latitude'          => -7.7500,
            'delivery_longitude'         => 110.3700,
            'service_route_distance_km'  => 5.0,
        ]);

        // When mitra updates location very close to destination (e.g. within 200m -> progress > 80%)
        $this->trackingService->updatePartnerLocation($help, -7.7505, 110.3700, 10.0);

        $help->refresh();
        $this->assertEquals(Help::STAGE_FINAL_APPROACH, $help->service_stage);
        $this->assertTrue($help->isFinalApproach());
    }

    /**
     * Test Isolation: Standard on_site_service and buy_for_customer are NOT affected by pickup_delivery lock/split
     */
    public function test_service_isolation_on_site_and_buy_for_customer()
    {
        // On site service can cancel normally in draft/open status without pickup_delivery calculations
        $onSiteHelp = Help::create([
            'user_id'             => $this->customer->id,
            'title'               => 'Bantu Cuci AC',
            'description'         => 'Desc',
            'service_type'        => Help::SERVICE_TYPE_ON_SITE,
            'status'              => Help::STATUS_MENUNGGU_MITRA,
            'amount'              => 50000,
            'service_fee'         => 50000,
            'travel_fee'          => 0,
            'item_fund'           => 0,
            'platform_fee_amount' => 2000,
            'total_amount'        => 52000,
            'city_id'             => $this->city->id,
            'district_id'         => $this->district->id,
            'latitude'            => -7.7956,
            'longitude'           => 110.3695,
        ]);

        $this->assertTrue($onSiteHelp->canCustomerCancel());

        $this->actingAs($this->customer);
        Livewire::test(Detail::class, ['id' => $onSiteHelp->id])
            ->call('cancelHelp')
            ->assertRedirect(route('customer.helps.index'));

        $this->assertEquals(Help::STATUS_DIBATALKAN, $onSiteHelp->fresh()->status);
    }
}
