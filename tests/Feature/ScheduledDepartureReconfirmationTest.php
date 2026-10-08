<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\Province;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\Chat;
use App\Services\HelpTransactionService;
use App\Services\HelpTrackingService;
use App\Support\Settings\AppSettingKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScheduledDepartureReconfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;
    protected City $city;
    protected District $district;
    protected User $customer;
    protected User $mitra;
    protected HelpTransactionService $transactionService;
    protected HelpTrackingService $trackingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->province = Province::create([
            'name'      => 'DI Yogyakarta',
            'is_active' => true,
        ]);

        $this->city = City::create([
            'name'        => 'Kota Yogyakarta',
            'province'    => 'DI Yogyakarta',
            'province_id' => $this->province->id,
            'latitude'    => -7.7956,
            'longitude'   => 110.3695,
            'is_active'   => true,
        ]);

        $this->district = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Danurejan',
            'latitude'  => -7.7930,
            'longitude' => 110.3700,
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'name'     => 'Customer Test',
            'email'    => 'customer@sayabantu.com',
            'role'     => 'customer',
            'status'   => 'active',
            'verified' => true,
        ]);

        UserBalance::create([
            'user_id' => $this->customer->id,
            'balance' => 500000,
        ]);

        $this->mitra = User::factory()->create([
            'name'                        => 'Mitra Test',
            'email'                       => 'mitra@sayabantu.com',
            'role'                        => 'mitra',
            'status'                      => 'active',
            'verified'                    => true,
            'city_id'                     => $this->city->id,
            'district_id'                 => $this->district->id,
            'warning_level'               => 0,
            'vehicle_verification_status' => 'approved',
        ]);

        PartnerOnlineState::create([
            'user_id'         => $this->mitra->id,
            'is_online'       => true,
            'matching_status' => PartnerOnlineState::STATUS_SEARCHING,
            'latitude'        => -7.7932,
            'longitude'       => 110.3702,
            'last_heartbeat'  => now(),
            'last_seen_at'    => now(),
        ]);

        $this->transactionService = app(HelpTransactionService::class);
        $this->trackingService    = app(HelpTrackingService::class);

        AppSetting::set(AppSettingKey::SCHEDULED_DEPARTURE_GRACE_MINUTES, '10');
    }

    protected function createOverdueScheduledHelp(): Help
    {
        return Help::create([
            'user_id'                 => $this->customer->id,
            'city_id'                 => $this->city->id,
            'district_id'             => $this->district->id,
            'title'                   => 'Pembersihan Rumah Terjadwal',
            'amount'                  => 50000,
            'total_amount'            => 52000,
            'description'             => 'Pembersihan terlewat jadwal',
            'location'                => 'Danurejan, Kota Yogyakarta',
            'latitude'                => -7.7930,
            'longitude'               => 110.3700,
            'order_mode'              => Help::ORDER_MODE_SCHEDULED,
            'service_type'            => Help::SERVICE_TYPE_ON_SITE,
            'status'                  => Help::STATUS_MENUNGGU_MITRA,
            'payment_status'          => Help::PAYMENT_STATUS_PAID,
            'escrow_status'           => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'           => Help::DISPATCH_MODE_POOL,
            'published_at'            => now()->subMinutes(60),
            'departure_at'            => now()->subMinutes(30),
            'service_scheduled_at'    => now()->subMinutes(15),
            'scheduled_at'            => now()->subMinutes(15),
            'departure_grace_minutes' => 10,
            'schedule_overdue_at'     => now()->subMinutes(20),
            'expires_at'              => now()->addHours(2),
        ]);
    }

    public function test_overdue_scheduled_help_when_taken_enters_pending_reconfirmation()
    {
        $help = $this->createOverdueScheduledHelp();

        $this->transactionService->takeHelp($help, $this->mitra);

        $help->refresh();

        $this->assertEquals(Help::STATUS_TAKEN, $help->status);
        $this->assertEquals($this->mitra->id, $help->mitra_id);
        $this->assertNotNull($help->schedule_reconfirmation_requested_at);
        $this->assertNull($help->schedule_confirmed_by_customer_at);
        $this->assertTrue($help->isCustomerConfirmationPending());
        $this->assertFalse($help->canPartnerStartDeparture());

        // Pastikan chat otomatis terkirim dengan tag [KONFIRMASI_JADWAL]
        $chat = Chat::where('help_id', $help->id)
            ->where('message', 'like', '%[KONFIRMASI_JADWAL]%')
            ->first();

        $this->assertNotNull($chat);
        $this->assertEquals('system', $chat->sender_type);
    }

    public function test_partner_cannot_start_departure_while_confirmation_pending()
    {
        $help = $this->createOverdueScheduledHelp();
        $this->transactionService->takeHelp($help, $this->mitra);
        $help->refresh();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tugas ini sedang menunggu konfirmasi dari pelanggan');

        $this->transactionService->markOnTheWay($help, $this->mitra);
    }

    public function test_partner_cannot_start_journey_tracking_while_confirmation_pending()
    {
        $help = $this->createOverdueScheduledHelp();
        $this->transactionService->takeHelp($help, $this->mitra);
        $help->refresh();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tugas ini sedang menunggu konfirmasi dari pelanggan');

        $this->trackingService->startJourney($help, -7.7932, 110.3702, 10.0);
    }

    public function test_customer_confirms_schedule_still_needed_unlocks_partner()
    {
        $help = $this->createOverdueScheduledHelp();
        $this->transactionService->takeHelp($help, $this->mitra);
        $help->refresh();

        $this->assertTrue($help->isCustomerConfirmationPending());

        // Customer mengonfirmasi
        $this->transactionService->confirmScheduleNeeded($help, $this->customer);
        $help->refresh();

        $this->assertNotNull($help->schedule_confirmed_by_customer_at);
        $this->assertFalse($help->isCustomerConfirmationPending());
        $this->assertTrue($help->canPartnerStartDeparture());

        // Cek chat konfirmasi diterima
        $chat = Chat::where('help_id', $help->id)
            ->where('message', 'like', '%[KONFIRMASI_JADWAL_DITERIMA]%')
            ->first();
        $this->assertNotNull($chat);

        // Sekarang mitra dapat berangkat
        $this->transactionService->markOnTheWay($help, $this->mitra);
        $help->refresh();

        $this->assertEquals(Help::STATUS_PARTNER_ON_THE_WAY, $help->status);
    }

    public function test_customer_cancels_overdue_schedule_executes_full_refund_and_releases_partner()
    {
        $help = $this->createOverdueScheduledHelp();
        $this->transactionService->takeHelp($help, $this->mitra);
        $help->refresh();

        $partnerState = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertEquals(PartnerOnlineState::STATUS_BUSY, $partnerState->matching_status);

        // Customer membatalkan karena jadwal terlewat dan tidak lagi butuh
        $this->transactionService->cancelScheduleNotNeeded($help, $this->customer);
        $help->refresh();

        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $help->escrow_status);

        // Dana 100% kembali ke customer (500000 + 52000 = 552000)
        $customerBalance = UserBalance::where('user_id', $this->customer->id)->first();
        $this->assertEquals(552000, (int) $customerBalance->balance);

        // Mitra dilepas dari status BUSY
        $partnerState->refresh();
        $this->assertEquals(PartnerOnlineState::STATUS_ONLINE, $partnerState->matching_status);

        // Cek chat pembatalan
        $chat = Chat::where('help_id', $help->id)
            ->where('message', 'like', '%[KONFIRMASI_JADWAL_DIBATALKAN]%')
            ->first();
        $this->assertNotNull($chat);
    }

    public function test_livewire_customer_chat_reconfirmation_actions()
    {
        $help = $this->createOverdueScheduledHelp();
        $this->transactionService->takeHelp($help, $this->mitra);
        $help->refresh();

        $this->actingAs($this->customer);

        Livewire::test(\App\Livewire\Customer\Chat\Index::class, ['help' => $help->id])
            ->call('confirmScheduleNeeded', $help->id)
            ->assertDispatched('show-alert');

        $help->refresh();
        $this->assertNotNull($help->schedule_confirmed_by_customer_at);
        $this->assertFalse($help->isCustomerConfirmationPending());
    }

    public function test_livewire_customer_help_detail_reconfirmation_actions()
    {
        $help = $this->createOverdueScheduledHelp();
        $this->transactionService->takeHelp($help, $this->mitra);
        $help->refresh();

        $this->actingAs($this->customer);

        Livewire::test(\App\Livewire\Customer\Helps\Detail::class, ['id' => $help->id])
            ->call('cancelScheduleNotNeeded')
            ->assertDispatched('show-status-notification');

        $help->refresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->status);
    }

    public function test_livewire_customer_help_detail_reconfirmation_modals_lifecycle()
    {
        $help = $this->createOverdueScheduledHelp();
        $this->transactionService->takeHelp($help, $this->mitra);
        $help->refresh();

        $this->actingAs($this->customer);

        Livewire::test(\App\Livewire\Customer\Helps\Detail::class, ['id' => $help->id])
            ->assertSet('showConfirmScheduleModal', false)
            ->assertSet('showCancelScheduleModal', false)
            ->call('openConfirmScheduleModal')
            ->assertSet('showConfirmScheduleModal', true)
            ->assertSee('Konfirmasi Kebutuhan Bantuan')
            ->call('closeConfirmScheduleModal')
            ->assertSet('showConfirmScheduleModal', false)
            ->call('openCancelScheduleModal')
            ->assertSet('showCancelScheduleModal', true)
            ->assertSee('Pengembalian Dana')
            ->call('closeCancelScheduleModal')
            ->assertSet('showCancelScheduleModal', false)
            ->call('openConfirmScheduleModal')
            ->call('confirmScheduleNeeded')
            ->assertSet('showConfirmScheduleModal', false)
            ->assertDispatched('show-status-notification');

        $help->refresh();
        $this->assertNotNull($help->schedule_confirmed_by_customer_at);
    }

    public function test_livewire_customer_chat_reconfirmation_modals_lifecycle()
    {
        $help = $this->createOverdueScheduledHelp();
        $this->transactionService->takeHelp($help, $this->mitra);
        $help->refresh();

        $this->actingAs($this->customer);

        Livewire::test(\App\Livewire\Customer\Chat\Index::class, ['help' => $help->id])
            ->assertSet('showConfirmScheduleModal', false)
            ->assertSet('showCancelScheduleModal', false)
            ->call('openConfirmScheduleModal', $help->id)
            ->assertSet('showConfirmScheduleModal', true)
            ->assertSet('pendingScheduleHelpId', $help->id)
            ->assertSee('Konfirmasi Kebutuhan Bantuan')
            ->call('closeConfirmScheduleModal')
            ->assertSet('showConfirmScheduleModal', false)
            ->assertSet('pendingScheduleHelpId', null)
            ->call('openCancelScheduleModal', $help->id)
            ->assertSet('showCancelScheduleModal', true)
            ->assertSee('Pengembalian Dana')
            ->call('cancelScheduleNotNeeded')
            ->assertSet('showCancelScheduleModal', false)
            ->assertDispatched('show-alert');

        $help->refresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $help->status);
    }
}

