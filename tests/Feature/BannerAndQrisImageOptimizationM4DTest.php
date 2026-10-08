<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AppSetting;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Livewire\SuperAdmin\Banners\Index as BannerIndex;
use App\Livewire\SuperAdmin\Settings\HelpSettings;
use App\Livewire\Customer\Topup\TopupRequest;

class BannerAndQrisImageOptimizationM4DTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Seed default AppSettings for HelpSettings validation
        AppSetting::set('platform_service_fee', '5000');
        AppSetting::set('min_help_nominal', '10000');
        AppSetting::set('help_auto_cancel_hours', '24');
        AppSetting::set('scheduled_departure_grace_minutes', '15');
        AppSetting::set('offer_timeout_seconds', '45');
        AppSetting::set('max_dispatch_candidates', '5');
        AppSetting::set('heartbeat_ttl_seconds', '90');
        AppSetting::set('max_matching_radius_km', '15');
        AppSetting::set('neutral_rating_prior', '4.5');
        AppSetting::set('rating_min_votes', '5');
        AppSetting::set('matching_weight_distance', '0.35');
        AppSetting::set('matching_weight_rating', '0.25');
        AppSetting::set('matching_weight_reliability', '0.25');
        AppSetting::set('matching_weight_fairness', '0.15');
        AppSetting::set('max_fairness_boost_minutes', '60');
        AppSetting::set('topup_qris_merchant_name', 'PT SayaBantu Indonesia');

        $this->superAdmin = User::factory()->create([
            'role'   => 'super_admin',
            'status' => 'active',
        ]);

        $this->customer = User::factory()->create([
            'role'   => 'customer',
            'status' => 'active',
        ]);
    }

    // =========================================================================
    // PART A: BANNER SERVER TESTS
    // =========================================================================

    public function test_customer_banner_jpg_accepted_under_1mb()
    {
        $this->actingAs($this->superAdmin);

        $file = UploadedFile::fake()->image('banner1.jpg', 1440, 620)->size(800);

        Livewire::test(BannerIndex::class)
            ->set('customerUploads', [$file])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('bannersSaved');

        $banners = json_decode((string) AppSetting::get('banner_customer', '[]'), true);
        $this->assertCount(1, $banners);
        $this->assertStringStartsWith('banners/', $banners[0]['image']);
        Storage::disk('public')->assertExists($banners[0]['image']);
    }

    public function test_customer_banner_png_accepted_under_1mb()
    {
        $this->actingAs($this->superAdmin);

        $file = UploadedFile::fake()->image('banner1.png', 1440, 620)->size(950);

        Livewire::test(BannerIndex::class)
            ->set('customerUploads', [$file])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('bannersSaved');

        $banners = json_decode((string) AppSetting::get('banner_customer', '[]'), true);
        $this->assertCount(1, $banners);
        $this->assertStringStartsWith('banners/', $banners[0]['image']);
        Storage::disk('public')->assertExists($banners[0]['image']);
    }

    public function test_mitra_banner_jpg_and_png_accepted_under_1mb()
    {
        $this->actingAs($this->superAdmin);

        $jpg = UploadedFile::fake()->image('mitra_hero.jpg', 1440, 620)->size(750);
        $png = UploadedFile::fake()->image('mitra_promo.png', 1440, 620)->size(600);

        Livewire::test(BannerIndex::class)
            ->set('mitraUploads', [$jpg, $png])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('bannersSaved');

        $mitraBanners = json_decode((string) AppSetting::get('banner_mitra', '[]'), true);
        $this->assertCount(2, $mitraBanners);
        $this->assertStringStartsWith('banners/', $mitraBanners[0]['image']);
        $this->assertStringStartsWith('banners/', $mitraBanners[1]['image']);
        Storage::disk('public')->assertExists($mitraBanners[0]['image']);
        Storage::disk('public')->assertExists($mitraBanners[1]['image']);
    }

    public function test_customer_banner_oversized_rejected()
    {
        $this->actingAs($this->superAdmin);

        $file = UploadedFile::fake()->image('oversized.jpg', 1440, 620)->size(1500); // 1.5MB > 1024KB

        Livewire::test(BannerIndex::class)
            ->set('customerUploads', [$file])
            ->call('save')
            ->assertHasErrors(['customerUploads.0']);

        $banners = json_decode((string) AppSetting::get('banner_customer', '[]'), true);
        $this->assertEmpty($banners);
    }

    public function test_mitra_banner_oversized_rejected()
    {
        $this->actingAs($this->superAdmin);

        $file = UploadedFile::fake()->image('oversized.png', 1440, 620)->size(2048); // 2MB > 1024KB

        Livewire::test(BannerIndex::class)
            ->set('mitraUploads', [$file])
            ->call('save')
            ->assertHasErrors(['mitraUploads.0']);

        $banners = json_decode((string) AppSetting::get('banner_mitra', '[]'), true);
        $this->assertEmpty($banners);
    }

    public function test_banner_non_image_and_svg_rejected()
    {
        $this->actingAs($this->superAdmin);

        $pdf = UploadedFile::fake()->create('document.pdf', 300, 'application/pdf');
        $svg = UploadedFile::fake()->create('vector.svg', 100, 'image/svg+xml');

        Livewire::test(BannerIndex::class)
            ->set('customerUploads', [$pdf])
            ->assertHasErrors(['customerUploads']);

        Livewire::test(BannerIndex::class)
            ->set('mitraUploads', [$svg])
            ->assertHasErrors(['mitraUploads']);
    }

    public function test_banner_maximum_limit_enforced()
    {
        $this->actingAs($this->superAdmin);

        // Prepopulate 5 banners
        $existing = [
            ['image' => 'banners/b1.jpg', 'link' => ''],
            ['image' => 'banners/b2.jpg', 'link' => ''],
            ['image' => 'banners/b3.jpg', 'link' => ''],
            ['image' => 'banners/b4.jpg', 'link' => ''],
            ['image' => 'banners/b5.jpg', 'link' => ''],
        ];
        AppSetting::set('banner_customer', json_encode($existing));

        $newFile = UploadedFile::fake()->image('extra.jpg', 1440, 620)->size(500);

        Livewire::test(BannerIndex::class)
            ->set('customerUploads', [$newFile])
            ->call('save')
            ->assertDispatched('bannersError');

        $banners = json_decode((string) AppSetting::get('banner_customer', '[]'), true);
        $this->assertCount(5, $banners);
    }

    public function test_banner_role_separation_preserved()
    {
        $this->actingAs($this->superAdmin);

        $custFile = UploadedFile::fake()->image('cust.jpg', 1440, 620)->size(500);

        Livewire::test(BannerIndex::class)
            ->set('customerUploads', [$custFile])
            ->call('save')
            ->assertDispatched('bannersSaved');

        $custBanners = json_decode((string) AppSetting::get('banner_customer', '[]'), true);
        $mitraBanners = json_decode((string) AppSetting::get('banner_mitra', '[]'), true);

        $this->assertCount(1, $custBanners);
        $this->assertCount(0, $mitraBanners);
    }

    // =========================================================================
    // PART B: QRIS SERVER TESTS
    // =========================================================================

    public function test_qris_canonical_png_accepted_under_1mb()
    {
        $this->actingAs($this->superAdmin);

        $png = UploadedFile::fake()->image('qris.png', 800, 800)->size(450);

        Livewire::test(HelpSettings::class)
            ->set('qris_image', $png)
            ->set('qris_merchant_name', 'PT SayaBantu Indonesia')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('settingsSaved');

        $storedPath = AppSetting::get('topup_qris_image');
        $this->assertNotEmpty($storedPath);
        $this->assertStringStartsWith('payment-qris/', $storedPath);
        $this->assertStringEndsWith('.png', strtolower($storedPath));
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_qris_png_oversized_rejected()
    {
        $this->actingAs($this->superAdmin);

        $oversizedPng = UploadedFile::fake()->image('qris_heavy.png', 1200, 1200)->size(1500); // 1.5MB > 1024KB

        Livewire::test(HelpSettings::class)
            ->set('qris_image', $oversizedPng)
            ->set('qris_merchant_name', 'PT SayaBantu Indonesia')
            ->call('save')
            ->assertHasErrors(['qris_image']);
    }

    public function test_qris_jpeg_and_webp_rejected_on_server()
    {
        $this->actingAs($this->superAdmin);

        $jpeg = UploadedFile::fake()->image('qris.jpg', 800, 800)->size(400);
        $webp = UploadedFile::fake()->create('qris.webp', 300, 'image/webp');

        Livewire::test(HelpSettings::class)
            ->set('qris_image', $jpeg)
            ->set('qris_merchant_name', 'PT SayaBantu Indonesia')
            ->call('save')
            ->assertHasErrors(['qris_image']);

        Livewire::test(HelpSettings::class)
            ->set('qris_image', $webp)
            ->set('qris_merchant_name', 'PT SayaBantu Indonesia')
            ->call('save')
            ->assertHasErrors(['qris_image']);
    }

    public function test_qris_non_image_and_svg_rejected()
    {
        $this->actingAs($this->superAdmin);

        $pdf = UploadedFile::fake()->create('qris.pdf', 200, 'application/pdf');
        $svg = UploadedFile::fake()->create('qris.svg', 50, 'image/svg+xml');

        Livewire::test(HelpSettings::class)
            ->set('qris_image', $pdf)
            ->set('qris_merchant_name', 'PT SayaBantu Indonesia')
            ->call('save')
            ->assertHasErrors(['qris_image']);

        Livewire::test(HelpSettings::class)
            ->set('qris_image', $svg)
            ->set('qris_merchant_name', 'PT SayaBantu Indonesia')
            ->call('save')
            ->assertHasErrors(['qris_image']);
    }

    public function test_qris_optional_and_old_file_replaced()
    {
        $this->actingAs($this->superAdmin);

        // Upload first QRIS
        $png1 = UploadedFile::fake()->image('qris1.png', 800, 800)->size(300);
        Livewire::test(HelpSettings::class)
            ->set('qris_image', $png1)
            ->set('qris_merchant_name', 'Merchant One')
            ->call('save')
            ->assertHasNoErrors();

        $path1 = AppSetting::get('topup_qris_image');
        Storage::disk('public')->assertExists($path1);

        // Upload second QRIS to replace first
        $png2 = UploadedFile::fake()->image('qris2.png', 800, 800)->size(350);
        Livewire::test(HelpSettings::class)
            ->set('qris_image', $png2)
            ->set('qris_merchant_name', 'Merchant Two')
            ->call('save')
            ->assertHasNoErrors();

        $path2 = AppSetting::get('topup_qris_image');
        $this->assertNotEquals($path1, $path2);
        Storage::disk('public')->assertMissing($path1);
        Storage::disk('public')->assertExists($path2);
    }

    public function test_historical_qris_download_filename_attribute()
    {
        $this->actingAs($this->customer);

        // Test with PNG
        AppSetting::set('topup_qris_image', 'payment-qris/historical_sample.png');
        AppSetting::set('topup_qris_enabled', '1');

        $component = Livewire::test(TopupRequest::class)
            ->set('amount', 50000)
            ->set('currentStep', 3);
        $component->assertSeeHtml('download="QRIS-SayaBantu.png"');

        // Test with historical JPG
        AppSetting::set('topup_qris_image', 'payment-qris/historical_sample.jpg');
        $component = Livewire::test(TopupRequest::class)
            ->set('amount', 50000)
            ->set('currentStep', 3);
        $component->assertSeeHtml('download="QRIS-SayaBantu.jpg"');

        // Test with historical WEBP
        AppSetting::set('topup_qris_image', 'payment-qris/historical_sample.webp');
        $component = Livewire::test(TopupRequest::class)
            ->set('amount', 50000)
            ->set('currentStep', 3);
        $component->assertSeeHtml('download="QRIS-SayaBantu.webp"');
    }
}
