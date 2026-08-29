<?php

namespace Tests\Feature;

use App\Livewire\SuperAdmin\Settings\HelpSettings;
use App\Models\AppSetting;
use App\Models\City;
use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Services\HelpMatchingService;
use App\Services\PartnerOnlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FairnessCalibrationAndAppSettingTest extends TestCase
{
    use RefreshDatabase;

    protected City $city;
    protected User $superAdmin;
    protected User $customer;
    protected User $mitra1;
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

        $this->superAdmin = User::factory()->create([
            'role'    => 'super_admin',
            'city_id' => $this->city->id,
        ]);

        $this->customer = User::factory()->create([
            'role'    => 'customer',
            'city_id' => $this->city->id,
        ]);

        $this->mitra1 = User::factory()->create([
            'role'    => 'mitra',
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
            'title'               => 'Bantu Bersih Rumah',
            'description'         => 'Sapu dan pel rumah',
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

    public function test_typed_app_setting_getters_return_valid_defaults()
    {
        $this->assertEquals(45, AppSetting::getOfferTimeoutSeconds());
        $this->assertEquals(5, AppSetting::getMaxDispatchCandidates());
        $this->assertEquals(60, AppSetting::getHeartbeatTtlSeconds());
        $this->assertEquals(15.0, AppSetting::getMaxMatchingRadiusKm());
        $this->assertEquals(4.5, AppSetting::getNeutralRatingPrior());
        $this->assertEquals(5, AppSetting::getRatingMinVotes());
        $this->assertEquals(60.0, AppSetting::getMaxFairnessBoostMinutes());

        $weights = AppSetting::getMatchingWeights();
        $this->assertEquals(0.35, $weights['distance']);
        $this->assertEquals(0.30, $weights['rating']);
        $this->assertEquals(0.25, $weights['reliability']);
        $this->assertEquals(0.10, $weights['fairness']);
    }

    public function test_typed_app_setting_getters_enforce_min_and_max_bounds()
    {
        // Set underflow value -> should be clamped to MIN
        AppSetting::set('offer_timeout_seconds', 5);
        AppSetting::set('max_dispatch_candidates', 0);
        AppSetting::set('heartbeat_ttl_seconds', 10);
        AppSetting::set('max_matching_radius_km', 0.5);
        AppSetting::set('neutral_rating_prior', 2.0);
        AppSetting::set('rating_min_votes', 0);

        $this->assertEquals(15, AppSetting::getOfferTimeoutSeconds());
        $this->assertEquals(1, AppSetting::getMaxDispatchCandidates());
        $this->assertEquals(30, AppSetting::getHeartbeatTtlSeconds());
        $this->assertEquals(1.0, AppSetting::getMaxMatchingRadiusKm());
        $this->assertEquals(3.0, AppSetting::getNeutralRatingPrior());
        $this->assertEquals(1, AppSetting::getRatingMinVotes());

        // Set overflow value -> should be clamped to MAX
        AppSetting::set('offer_timeout_seconds', 500);
        AppSetting::set('max_dispatch_candidates', 100);
        AppSetting::set('heartbeat_ttl_seconds', 1000);
        AppSetting::set('max_matching_radius_km', 500.0);
        AppSetting::set('neutral_rating_prior', 6.0);
        AppSetting::set('rating_min_votes', 100);

        $this->assertEquals(120, AppSetting::getOfferTimeoutSeconds());
        $this->assertEquals(30, AppSetting::getMaxDispatchCandidates());
        $this->assertEquals(300, AppSetting::getHeartbeatTtlSeconds());
        $this->assertEquals(100.0, AppSetting::getMaxMatchingRadiusKm());
        $this->assertEquals(5.0, AppSetting::getNeutralRatingPrior());
        $this->assertEquals(50, AppSetting::getRatingMinVotes());
    }

    public function test_matching_weights_normalization()
    {
        // Set custom weights that sum to 2.0
        AppSetting::set('weight_distance', 0.50);
        AppSetting::set('weight_rating', 0.50);
        AppSetting::set('weight_reliability', 0.50);
        AppSetting::set('weight_fairness', 0.50);

        $weights = AppSetting::getMatchingWeights();
        $this->assertEquals(0.25, $weights['distance']);
        $this->assertEquals(0.25, $weights['rating']);
        $this->assertEquals(0.25, $weights['reliability']);
        $this->assertEquals(0.25, $weights['fairness']);
        $this->assertEquals(1.0, array_sum($weights));
    }

    public function test_help_matching_service_dynamically_adapts_to_app_setting_calibration()
    {
        $this->onlineService->startSearching($this->mitra1, -7.7960, 110.3700);
        $state = PartnerOnlineState::where('user_id', $this->mitra1->id)->first();
        $help  = $this->createHelp();

        // 1. Calculate with default prior rating = 4.5
        $scoreDefault = $this->matchingService->calculatePartnerCompositeScore($help, $this->mitra1, $state);

        // 2. Adjust prior rating to 5.0 in AppSetting
        AppSetting::set('neutral_rating_prior', 5.0);
        $scoreAdjusted = $this->matchingService->calculatePartnerCompositeScore($help, $this->mitra1, $state);

        $this->assertGreaterThan($scoreDefault['rating_score'], $scoreAdjusted['rating_score']);
        $this->assertGreaterThan($scoreDefault['total_score'], $scoreAdjusted['total_score']);
    }

    public function test_superadmin_settings_livewire_can_save_matching_and_fairness_calibration()
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(HelpSettings::class)
            ->set('min_help_nominal', 10000)
            ->set('platform_service_fee', 2000)
            ->set('qris_merchant_name', 'PT SayaBantu Indonesia')
            ->set('offer_timeout_seconds', 30)
            ->set('max_dispatch_candidates', 8)
            ->set('heartbeat_ttl_seconds', 90)
            ->set('max_matching_radius_km', 20.0)
            ->set('neutral_rating_prior', 4.8)
            ->set('rating_min_votes', 5)
            ->set('weight_distance', 0.40)
            ->set('weight_rating', 0.30)
            ->set('weight_reliability', 0.20)
            ->set('weight_fairness', 0.10)
            ->set('max_fairness_boost_minutes', 60)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('settingsSaved');

        // Pastikan AppSetting benar-benar tersimpan ke database
        $this->assertEquals(30, AppSetting::getOfferTimeoutSeconds());
        $this->assertEquals(8, AppSetting::getMaxDispatchCandidates());
        $this->assertEquals(90, AppSetting::getHeartbeatTtlSeconds());
        $this->assertEquals(20.0, AppSetting::getMaxMatchingRadiusKm());
        $this->assertEquals(4.8, AppSetting::getNeutralRatingPrior());
    }
}
