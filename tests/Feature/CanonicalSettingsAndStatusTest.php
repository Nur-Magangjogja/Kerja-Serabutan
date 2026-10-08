<?php

namespace Tests\Feature;

use App\Enums\HelpStatus;
use App\Models\AppSetting;
use App\Models\Help;
use App\Models\User;
use App\Support\Presenters\HelpStatusPresenter;
use App\Support\Settings\AppSettingKey;
use App\Support\Settings\HelpSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanonicalSettingsAndStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_help_status_enum_has_all_canonical_statuses(): void
    {
        $cases = HelpStatus::cases();
        $this->assertCount(10, $cases);

        $this->assertEquals('menunggu_mitra', HelpStatus::MENUNGGU_MITRA->value);
        $this->assertEquals('taken', HelpStatus::TAKEN->value);
        $this->assertEquals('partner_on_the_way', HelpStatus::PARTNER_ON_THE_WAY->value);
        $this->assertEquals('partner_arrived', HelpStatus::PARTNER_ARRIVED->value);
        $this->assertEquals('in_progress', HelpStatus::IN_PROGRESS->value);
        $this->assertEquals('waiting_customer_confirmation', HelpStatus::WAITING_CONFIRMATION->value);
        $this->assertEquals('selesai', HelpStatus::SELESAI->value);
        $this->assertEquals('dibatalkan', HelpStatus::DIBATALKAN->value);
        $this->assertEquals('partner_cancel_requested', HelpStatus::PARTNER_CANCEL_REQUESTED->value);
        $this->assertEquals('customer_cancel_requested', HelpStatus::CUSTOMER_CANCEL_REQUESTED->value);
    }

    public function test_help_status_normalizes_legacy_aliases(): void
    {
        $this->assertEquals(HelpStatus::SELESAI, HelpStatus::tryFromOrNormalize('completed'));
        $this->assertEquals(HelpStatus::DIBATALKAN, HelpStatus::tryFromOrNormalize('cancelled'));
        $this->assertEquals(HelpStatus::TAKEN, HelpStatus::tryFromOrNormalize('memperoleh_mitra'));
        $this->assertEquals(HelpStatus::IN_PROGRESS, HelpStatus::tryFromOrNormalize('sedang_diproses'));
        $this->assertEquals(HelpStatus::MENUNGGU_MITRA, HelpStatus::tryFromOrNormalize('mencari_mitra'));
        $this->assertEquals(HelpStatus::MENUNGGU_MITRA, HelpStatus::tryFromOrNormalize('pending'));
        $this->assertNull(HelpStatus::tryFromOrNormalize(null));
        $this->assertNull(HelpStatus::tryFromOrNormalize(''));
    }

    public function test_help_status_active_and_terminal_helpers(): void
    {
        $this->assertTrue(HelpStatus::TAKEN->isActive());
        $this->assertTrue(HelpStatus::PARTNER_ON_THE_WAY->isActive());
        $this->assertTrue(HelpStatus::PARTNER_ARRIVED->isActive());
        $this->assertTrue(HelpStatus::IN_PROGRESS->isActive());
        $this->assertFalse(HelpStatus::SELESAI->isActive());
        $this->assertFalse(HelpStatus::MENUNGGU_MITRA->isActive());

        $this->assertTrue(HelpStatus::SELESAI->isTerminal());
        $this->assertTrue(HelpStatus::DIBATALKAN->isTerminal());
        $this->assertFalse(HelpStatus::IN_PROGRESS->isTerminal());
    }

    public function test_help_status_presenter_returns_complete_ui_metadata(): void
    {
        $meta = HelpStatusPresenter::for(HelpStatus::SELESAI->value);

        $this->assertEquals('Selesai', $meta['label']);
        $this->assertStringContainsString('emerald', $meta['badge_class']);
        $this->assertTrue($meta['is_terminal']);
        $this->assertFalse($meta['is_active']);

        $pendingMeta = HelpStatusPresenter::for(HelpStatus::MENUNGGU_MITRA);
        $this->assertEquals('Menunggu Mitra', $pendingMeta['label']);
        $this->assertStringContainsString('amber', $pendingMeta['badge_class']);
    }

    public function test_help_model_status_enum_and_presenter_accessors(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $help = Help::create([
            'user_id' => $customer->id,
            'title' => 'Test Help Order',
            'description' => 'Test description for help order',
            'amount' => 50000,
            'status' => Help::STATUS_IN_PROGRESS,
            'service_type' => Help::SERVICE_TYPE_ON_SITE,
        ]);

        $this->assertEquals(HelpStatus::IN_PROGRESS, $help->statusEnum());
        $presenter = $help->statusPresenter();
        $this->assertEquals('Sedang Dikerjakan', $presenter['label']);
        $this->assertTrue($presenter['is_active']);
    }

    public function test_help_settings_service_reads_and_writes_via_keys(): void
    {
        $service = app(HelpSettingsService::class);

        $this->assertEquals(2000.0, $service->getPlatformFee());
        $this->assertEquals(24, $service->getAutoCancelHours());
        $this->assertEquals(45, $service->getOfferTimeoutSeconds());

        // Test custom set and get
        $service->set(AppSettingKey::PLATFORM_SERVICE_FEE, 3500);
        $this->assertEquals(3500.0, $service->getPlatformFee());

        $breakdown = $service->calculatePlatformFee(100000);
        $this->assertEquals(3500.0, $breakdown['fee_amount']);
        $this->assertEquals(103500.0, $breakdown['total']);
    }
}
