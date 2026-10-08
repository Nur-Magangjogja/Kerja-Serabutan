<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpDispatch;
use App\Models\PartnerOnlineState;
use App\Models\Province;
use App\Models\User;
use App\Models\UserBalance;
use App\Services\Cancellation\CancellationSettlementService;
use App\Services\HelpCreationService;
use App\Services\HelpMatchingService;
use App\Services\HelpTrackingService;
use App\Services\HelpTransactionService;
use App\Services\RegionService;
use App\Services\ScheduledDepartureTimeoutService;
use App\Support\Presenters\HelpStatusPresenter;
use App\Support\Settings\AppSettingKey;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ScheduledDepartureTimeoutTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;
    protected City $city;
    protected District $district;
    protected User $customer;
    protected User $mitra;
    protected HelpCreationService $creationService;
    protected HelpTransactionService $transactionService;
    protected HelpTrackingService $trackingService;
    protected ScheduledDepartureTimeoutService $timeoutService;
    protected RegionService $regionService;

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

        $customerBalance = UserBalance::create([
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

        $this->creationService    = app(HelpCreationService::class);
        $this->transactionService = app(HelpTransactionService::class);
        $this->trackingService    = app(HelpTrackingService::class);
        $this->timeoutService     = app(ScheduledDepartureTimeoutService::class);
        $this->regionService      = app(RegionService::class);

        AppSetting::set(AppSettingKey::SCHEDULED_DEPARTURE_GRACE_MINUTES, '10');
    }

    protected function createScheduledHelp(array $overrides = []): Help
    {
        $departureAt = $overrides['departure_at'] ?? now()->addHour();
        $targetScheduledAt = $overrides['service_scheduled_at'] ?? now()->addHours(2);

        $payload = array_merge([
            'user_id'                 => $this->customer->id,
            'city_id'                 => $this->city->id,
            'district_id'             => $this->district->id,
            'title'                   => 'Bantuan Terjadwal Test',
            'amount'                  => 50000,
            'total_amount'            => 52000,
            'description'             => 'Deskripsi uji coba scheduled',
            'location'                => 'Danurejan, Kota Yogyakarta',
            'latitude'                => -7.7930,
            'longitude'               => 110.3700,
            'order_mode'              => Help::ORDER_MODE_SCHEDULED,
            'service_type'            => Help::SERVICE_TYPE_ON_SITE,
            'status'                  => Help::STATUS_MENUNGGU_MITRA,
            'payment_status'          => Help::PAYMENT_STATUS_PAID,
            'escrow_status'           => Help::ESCROW_STATUS_HELD,
            'dispatch_mode'           => Help::DISPATCH_MODE_SEEKING,
            'published_at'            => now()->subMinutes(5),
            'departure_at'            => $departureAt,
            'service_scheduled_at'    => $targetScheduledAt,
            'scheduled_at'            => $targetScheduledAt,
            'departure_grace_minutes' => 10,
            'expires_at'              => now()->addDay(),
        ], $overrides);

        return Help::create($payload);
    }

    // 1. departure 21:00 + grace 10 -> 21:09 belum overdue
    public function test_departure_boundary_before_deadline_is_not_overdue()
    {
        $base = Carbon::parse('2026-10-01 21:00:00');
        Carbon::setTestNow(Carbon::parse('2026-10-01 21:09:00'));

        $help = $this->createScheduledHelp([
            'departure_at'            => $base,
            'departure_grace_minutes' => 10,
        ]);

        $this->assertFalse($help->isDepartureOverdue());
        $this->assertFalse($help->isOverdue());
        $this->assertEquals('2026-10-01 21:10:00', $help->getDepartureDeadline()->toDateTimeString());

        Carbon::setTestNow();
    }

    // 2. departure 21:00 + grace 10 -> 21:10 overdue
    public function test_departure_boundary_at_and_after_deadline_is_overdue()
    {
        $base = Carbon::parse('2026-10-01 21:00:00');
        Carbon::setTestNow(Carbon::parse('2026-10-01 21:10:00'));

        $help = $this->createScheduledHelp([
            'departure_at'            => $base,
            'departure_grace_minutes' => 10,
        ]);

        $this->assertTrue($help->isDepartureOverdue());
        $this->assertTrue($help->isOverdue());

        Carbon::setTestNow();
    }

    // 3. taken + belum start -> release + exclusion + rematch
    public function test_taken_and_not_started_releases_mitra_excludes_and_rematches()
    {
        $help = $this->createScheduledHelp([
            'status'                  => Help::STATUS_TAKEN,
            'mitra_id'                => $this->mitra->id,
            'taken_at'                => now()->subMinutes(30),
            'partner_started_at'      => null,
            'departure_at'            => now()->subMinutes(20),
            'departure_grace_minutes' => 10,
        ]);

        // Partner is currently busy
        PartnerOnlineState::where('user_id', $this->mitra->id)->update([
            'matching_status' => PartnerOnlineState::STATUS_BUSY,
            'current_help_id' => $help->id,
        ]);

        $result = $this->timeoutService->handleOverdueHelp($help);

        $this->assertTrue($result['handled']);
        $this->assertEquals('rematched_from_taken', $result['action']);

        $fresh = $help->fresh();
        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $fresh->status);
        $this->assertNull($fresh->mitra_id);
        $this->assertNotNull($fresh->schedule_overdue_at);
        $this->assertTrue($fresh->hasCancelledBy($this->mitra->id));

        // Partner is no longer busy
        $partnerState = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertNotEquals(PartnerOnlineState::STATUS_BUSY, $partnerState->matching_status);
        $this->assertNull($partnerState->current_help_id);
    }

    // 4. taken + sudah start -> tidak disentuh
    public function test_taken_and_already_started_is_not_touched()
    {
        $help = $this->createScheduledHelp([
            'status'                  => Help::STATUS_PARTNER_ON_THE_WAY,
            'mitra_id'                => $this->mitra->id,
            'partner_started_at'      => now()->subMinutes(5),
            'departure_at'            => now()->subMinutes(25),
            'departure_grace_minutes' => 10,
        ]);

        $result = $this->timeoutService->handleOverdueHelp($help);

        $this->assertFalse($result['handled']);
        $this->assertEquals('partner_already_started', $result['reason']);

        $fresh = $help->fresh();
        $this->assertEquals(Help::STATUS_PARTNER_ON_THE_WAY, $fresh->status);
        $this->assertEquals($this->mitra->id, $fresh->mitra_id);
        $this->assertNull($fresh->schedule_overdue_at);
    }

    // 5. scheduled belum punya Mitra + deadline lewat -> overdue + ready-now
    public function test_unassigned_scheduled_past_deadline_becomes_overdue_and_ready_now()
    {
        $help = $this->createScheduledHelp([
            'status'                  => Help::STATUS_MENUNGGU_MITRA,
            'mitra_id'                => null,
            'departure_at'            => now()->subMinutes(15),
            'departure_grace_minutes' => 10,
        ]);

        $result = $this->timeoutService->handleOverdueHelp($help);

        $this->assertTrue($result['handled']);
        $this->assertEquals('ready_now_matching', $result['action']);

        $fresh = $help->fresh();
        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $fresh->status);
        $this->assertNull($fresh->mitra_id);
        $this->assertNotNull($fresh->schedule_overdue_at);
        $this->assertContains($fresh->dispatch_mode, [Help::DISPATCH_MODE_SEEKING, Help::DISPATCH_MODE_OFFERED, Help::DISPATCH_MODE_POOL]);

        $presenter = HelpStatusPresenter::for($fresh);
        $this->assertEquals('Segera • Jadwal Terlewat', $presenter['label']);
    }

    // 6. setting berubah 10 -> 5 -> order lama tetap memakai snapshot 10
    public function test_snapshot_grace_minutes_immutability_when_global_setting_changes()
    {
        AppSetting::set(AppSettingKey::SCHEDULED_DEPARTURE_GRACE_MINUTES, '10');

        $help = $this->createScheduledHelp([
            'departure_grace_minutes' => 10,
        ]);

        // Global setting is changed to 5
        AppSetting::set(AppSettingKey::SCHEDULED_DEPARTURE_GRACE_MINUTES, '5');
        AppSetting::clearCache();

        $this->assertEquals(10, $help->fresh()->getEffectiveDepartureGraceMinutes());
    }

    // 7. legacy Help dengan snapshot NULL -> fallback aman
    public function test_legacy_help_with_null_snapshot_falls_back_safely()
    {
        AppSetting::set(AppSettingKey::SCHEDULED_DEPARTURE_GRACE_MINUTES, '15');
        AppSetting::clearCache();

        $help = $this->createScheduledHelp([
            'departure_grace_minutes' => null,
        ]);

        $this->assertEquals(15, $help->getEffectiveDepartureGraceMinutes());
    }

    // 8. stale scheduled diambil setelah deadline -> tidak berhasil sebagai scheduled normal
    public function test_stale_scheduled_take_rejected_after_deadline()
    {
        $help = $this->createScheduledHelp([
            'status'                  => Help::STATUS_MENUNGGU_MITRA,
            'dispatch_mode'           => Help::DISPATCH_MODE_POOL,
            'published_at'            => now()->subHour(),
            'departure_at'            => now()->subMinutes(20),
            'departure_grace_minutes' => 10,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Batas toleransi jadwal keberangkatan untuk bantuan ini telah terlewat.');

        $this->transactionService->takeHelp($help, $this->mitra);
    }

    // 9. markOnTheWay setelah deadline -> tidak berhasil memulai
    public function test_mark_on_the_way_rejected_after_deadline()
    {
        $help = $this->createScheduledHelp([
            'status'                  => Help::STATUS_TAKEN,
            'mitra_id'                => $this->mitra->id,
            'departure_at'            => now()->subMinutes(25),
            'departure_grace_minutes' => 10,
            'partner_started_at'      => null,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Batas waktu keberangkatan telah terlewat.');

        $this->transactionService->markOnTheWay($help, $this->mitra);
    }

    // 10. Pickup/Delivery going_to_pickup setelah deadline -> tidak berhasil memulai
    public function test_pickup_delivery_going_to_pickup_rejected_after_deadline()
    {
        $help = $this->createScheduledHelp([
            'service_type'            => Help::SERVICE_TYPE_PICKUP_DELIVERY,
            'status'                  => Help::STATUS_TAKEN,
            'mitra_id'                => $this->mitra->id,
            'departure_at'            => now()->subMinutes(25),
            'departure_grace_minutes' => 10,
            'partner_started_at'      => null,
            'pickup_latitude'         => -7.7930,
            'pickup_longitude'        => 110.3700,
            'delivery_latitude'       => -7.7990,
            'delivery_longitude'      => 110.3750,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Batas waktu keberangkatan telah terlewat.');

        $this->trackingService->advanceServiceStage($help, Help::STAGE_GOING_TO_PICKUP);
    }

    // 11. old Mitra excluded -> tidak dapat mengambil lagi
    public function test_old_mitra_excluded_cannot_retake_order()
    {
        $help = $this->createScheduledHelp([
            'status'                  => Help::STATUS_TAKEN,
            'mitra_id'                => $this->mitra->id,
            'departure_at'            => now()->subMinutes(25),
            'departure_grace_minutes' => 10,
            'partner_started_at'      => null,
        ]);

        // Run overdue handler to release and exclude
        $this->timeoutService->handleOverdueHelp($help);

        $fresh = $help->fresh();
        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $fresh->status);
        $this->assertTrue($fresh->hasCancelledBy($this->mitra->id));

        // Open pool to test manual retake
        $fresh->update(['dispatch_mode' => Help::DISPATCH_MODE_POOL]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Anda tidak dapat mengambil bantuan ini karena sebelumnya telah Anda batalkan.');

        $this->transactionService->takeHelp($fresh, $this->mitra);
    }

    // 12. overdue + region active -> rematch
    public function test_overdue_in_active_region_triggers_rematch()
    {
        $help = $this->createScheduledHelp([
            'status'                  => Help::STATUS_TAKEN,
            'mitra_id'                => $this->mitra->id,
            'departure_at'            => now()->subMinutes(25),
            'departure_grace_minutes' => 10,
            'partner_started_at'      => null,
        ]);

        $result = $this->timeoutService->handleOverdueHelp($help);

        $this->assertTrue($result['handled']);
        $this->assertEquals('rematched_from_taken', $result['action']);
        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->fresh()->status);
    }

    // 13. overdue + region inactive -> cancel/refund
    public function test_overdue_in_inactive_region_cancels_and_refunds_directly()
    {
        $help = $this->createScheduledHelp([
            'status'                  => Help::STATUS_TAKEN,
            'mitra_id'                => $this->mitra->id,
            'departure_at'            => now()->subMinutes(25),
            'departure_grace_minutes' => 10,
            'partner_started_at'      => null,
        ]);

        // Region is deactivated while order was taken
        $this->district->update(['is_active' => false]);

        $initialBalance = UserBalance::where('user_id', $this->customer->id)->value('balance');

        $result = $this->timeoutService->handleOverdueHelp($help);

        $this->assertTrue($result['handled']);
        $this->assertEquals('region_inactive_cancelled', $result['action']);

        $fresh = $help->fresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $fresh->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $fresh->escrow_status);

        // Refunded 100%
        $refundedBalance = UserBalance::where('user_id', $this->customer->id)->value('balance');
        $this->assertEquals($initialBalance + 52000, $refundedBalance);
    }

    // 14. overdue + expires_at habis -> existing auto-cancel/refund
    public function test_overdue_with_expired_at_past_auto_cancels_directly()
    {
        $help = $this->createScheduledHelp([
            'status'                  => Help::STATUS_TAKEN,
            'mitra_id'                => $this->mitra->id,
            'departure_at'            => now()->subMinutes(30),
            'departure_grace_minutes' => 10,
            'expires_at'              => now()->subMinute(),
            'partner_started_at'      => null,
        ]);

        $initialBalance = UserBalance::where('user_id', $this->customer->id)->value('balance');

        $result = $this->timeoutService->handleOverdueHelp($help);

        $this->assertTrue($result['handled']);
        $this->assertEquals('expired_cancelled', $result['action']);

        $fresh = $help->fresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $fresh->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $fresh->escrow_status);

        $refundedBalance = UserBalance::where('user_id', $this->customer->id)->value('balance');
        $this->assertEquals($initialBalance + 52000, $refundedBalance);
    }

    // 15. scheduler dijalankan dua kali -> state/refund tidak duplicate
    public function test_scheduler_sweep_is_idempotent()
    {
        $help = $this->createScheduledHelp([
            'status'                  => Help::STATUS_TAKEN,
            'mitra_id'                => $this->mitra->id,
            'departure_at'            => now()->subMinutes(30),
            'departure_grace_minutes' => 10,
            'partner_started_at'      => null,
        ]);

        $firstRun = $this->timeoutService->sweepScheduledTimeouts();
        $this->assertEquals(1, $firstRun);

        $secondRun = $this->timeoutService->sweepScheduledTimeouts();
        // On second run, order is already menunggu_mitra with schedule_overdue_at set, no new overdue action
        $this->assertEquals(0, $secondRun);
    }

    // 16. race scheduler vs Mitra start -> satu hasil konsisten
    public function test_race_scheduler_vs_mitra_start_consistency()
    {
        $help = $this->createScheduledHelp([
            'status'                  => Help::STATUS_TAKEN,
            'mitra_id'                => $this->mitra->id,
            'departure_at'            => now()->subMinutes(25),
            'departure_grace_minutes' => 10,
            'partner_started_at'      => null,
        ]);

        // Scheduler runs first
        $this->timeoutService->handleOverdueHelp($help);

        // Mitra attempts to start afterwards
        $this->expectException(\RuntimeException::class);
        $this->transactionService->markOnTheWay($help->fresh(), $this->mitra);
    }

    // 17. state transition taken -> menunggu_mitra hanya terjadi pada valid overdue flow
    public function test_state_transition_taken_to_menunggu_mitra_integrity()
    {
        $help = $this->createScheduledHelp([
            'status' => Help::STATUS_TAKEN,
        ]);

        // Direct valid transition
        $this->assertTrue($help->canTransitionTo(Help::STATUS_MENUNGGU_MITRA));
        $help->transitionTo(Help::STATUS_MENUNGGU_MITRA);
        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);

        // Terminal status cannot transition back to menunggu_mitra
        $help->update(['status' => Help::STATUS_SELESAI]);
        $this->assertFalse($help->canTransitionTo(Help::STATUS_MENUNGGU_MITRA));

        $this->expectException(\RuntimeException::class);
        $help->transitionTo(Help::STATUS_MENUNGGU_MITRA);
    }

    // 18. matchPendingOrderForPartner includes overdue scheduled orders
    public function test_match_pending_order_for_partner_includes_overdue_scheduled()
    {
        $help = $this->createScheduledHelp([
            'status'              => Help::STATUS_MENUNGGU_MITRA,
            'order_mode'          => Help::ORDER_MODE_SCHEDULED,
            'schedule_overdue_at' => now(),
            'dispatch_mode'       => Help::DISPATCH_MODE_SEEKING,
            'mitra_id'            => null,
        ]);

        $matched = app(HelpMatchingService::class)->matchPendingOrderForPartner($this->mitra);
        $this->assertNotNull($matched);
        $this->assertEquals($help->id, $matched->id);
    }

    // 19. UI Customer message differentiation
    public function test_ui_customer_notice_distinguishes_previous_partner()
    {
        // Case with previous partner
        $helpWithPrev = $this->createScheduledHelp([
            'status'              => Help::STATUS_MENUNGGU_MITRA,
            'schedule_overdue_at' => now(),
            'cancelled_mitra_ids' => [$this->mitra->id],
        ]);
        $this->assertStringContainsString('mitra sebelumnya belum memulai perjalanan', $helpWithPrev->getCustomerScheduleStatusNotice());

        // Case without previous partner
        $helpWithoutPrev = $this->createScheduledHelp([
            'status'              => Help::STATUS_MENUNGGU_MITRA,
            'schedule_overdue_at' => now(),
            'cancelled_mitra_ids' => null,
        ]);
        $this->assertStringNotContainsString('mitra sebelumnya', $helpWithoutPrev->getCustomerScheduleStatusNotice());
        $this->assertStringContainsString('Sistem sedang mencari mitra', $helpWithoutPrev->getCustomerScheduleStatusNotice());
    }

    // 20. Province Inactive -> taken order cancelled & refunded without rematch
    public function test_overdue_when_province_inactive_releases_mitra_cancels_and_refunds_without_rematch()
    {
        $help = $this->createScheduledHelp([
            'status'                  => Help::STATUS_TAKEN,
            'mitra_id'                => $this->mitra->id,
            'departure_at'            => now()->subMinutes(25),
            'departure_grace_minutes' => 10,
            'partner_started_at'      => null,
        ]);

        // Province is deactivated (District and City remain is_active = true)
        $this->province->update(['is_active' => false]);

        $initialBalance = UserBalance::where('user_id', $this->customer->id)->value('balance');

        $result = $this->timeoutService->handleOverdueHelp($help);

        $this->assertTrue($result['handled']);
        $this->assertEquals('region_inactive_cancelled', $result['action']);

        $fresh = $help->fresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $fresh->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $fresh->escrow_status);
        $this->assertNull($fresh->mitra_id);
        $this->assertTrue($fresh->hasCancelledBy($this->mitra->id));

        // Partner is released
        $state = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertFalse((bool) $state->is_busy);

        // Refunded 100% to customer
        $refundedBalance = UserBalance::where('user_id', $this->customer->id)->value('balance');
        $this->assertEquals($initialBalance + 52000, $refundedBalance);

        // Dispatches closed, no active dispatch
        $activeDispatches = HelpDispatch::where('help_id', $help->id)
            ->where('status', HelpDispatch::STATUS_OFFERED)
            ->count();
        $this->assertEquals(0, $activeDispatches);
    }

    // 21. Province Inactive -> unassigned order cancelled & refunded without ready-now matching
    public function test_overdue_when_province_inactive_unassigned_cancels_and_refunds_without_ready_now_matching()
    {
        $help = $this->createScheduledHelp([
            'status'                  => Help::STATUS_MENUNGGU_MITRA,
            'mitra_id'                => null,
            'departure_at'            => now()->subMinutes(25),
            'departure_grace_minutes' => 10,
            'partner_started_at'      => null,
        ]);

        // Province is deactivated (City & District still true)
        $this->province->update(['is_active' => false]);

        $initialBalance = UserBalance::where('user_id', $this->customer->id)->value('balance');

        $result = $this->timeoutService->handleOverdueHelp($help);

        $this->assertTrue($result['handled']);
        $this->assertEquals('region_inactive_cancelled', $result['action']);

        $fresh = $help->fresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $fresh->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $fresh->escrow_status);
        $this->assertEquals(Help::DISPATCH_MODE_CLOSED, $fresh->dispatch_mode);

        // Customer refunded 100%
        $refundedBalance = UserBalance::where('user_id', $this->customer->id)->value('balance');
        $this->assertEquals($initialBalance + 52000, $refundedBalance);
    }

    // 22. Customer notification on assigned scheduled timeout
    public function test_overdue_assigned_creates_customer_notification_record()
    {
        $help = $this->createScheduledHelp([
            'status'                  => Help::STATUS_TAKEN,
            'mitra_id'                => $this->mitra->id,
            'departure_at'            => now()->subMinutes(25),
            'departure_grace_minutes' => 10,
            'partner_started_at'      => null,
        ]);

        $this->timeoutService->handleOverdueHelp($help);

        $notif = $this->customer->notifications()->latest()->first();
        $this->assertNotNull($notif, 'Customer should receive a notification record in database');
        $this->assertEquals('Jadwal Keberangkatan Terlewat', $notif->data['title']);
        $this->assertStringContainsString('mitra sebelumnya belum memulai perjalanan', $notif->data['message']);
        $this->assertStringContainsString('mencari mitra pengganti', $notif->data['message']);
    }

    // 23. Customer notification on unassigned scheduled timeout (generic wording)
    public function test_overdue_unassigned_creates_customer_notification_record_with_generic_wording()
    {
        $help = $this->createScheduledHelp([
            'status'                  => Help::STATUS_MENUNGGU_MITRA,
            'mitra_id'                => null,
            'departure_at'            => now()->subMinutes(25),
            'departure_grace_minutes' => 10,
            'partner_started_at'      => null,
        ]);

        $this->timeoutService->handleOverdueHelp($help);

        $notif = $this->customer->notifications()->latest()->first();
        $this->assertNotNull($notif, 'Customer should receive a notification record in database');
        $this->assertEquals('Jadwal Keberangkatan Terlewat', $notif->data['title']);
        $this->assertStringContainsString('Sistem sedang mencari mitra untuk segera mengerjakan pesanan Anda', $notif->data['message']);
        $this->assertStringNotContainsString('mitra sebelumnya', $notif->data['message']);
        $this->assertStringNotContainsString('terlambat', $notif->data['message']);
    }

    // 24. Expiry precedence fulfills all canonical cancellation side-effects
    public function test_overdue_expiry_precedence_includes_all_canonical_cancellation_side_effects()
    {
        $help = $this->createScheduledHelp([
            'status'                  => Help::STATUS_TAKEN,
            'mitra_id'                => $this->mitra->id,
            'departure_at'            => now()->subMinutes(30),
            'departure_grace_minutes' => 10,
            'expires_at'              => now()->subMinute(),
            'partner_started_at'      => null,
        ]);

        $result = $this->timeoutService->handleOverdueHelp($help);

        $this->assertTrue($result['handled']);
        $this->assertEquals('expired_cancelled', $result['action']);

        $fresh = $help->fresh();
        $this->assertEquals(Help::STATUS_DIBATALKAN, $fresh->status);
        $this->assertEquals(Help::ESCROW_STATUS_REFUNDED, $fresh->escrow_status);
        $this->assertEquals(Help::PAYMENT_STATUS_REFUNDED, $fresh->payment_status);
        $this->assertEquals(Help::DISPATCH_MODE_CLOSED, $fresh->dispatch_mode);
        $this->assertStringContainsString('Dibatalkan otomatis oleh sistem', $fresh->admin_notes);

        // Partner released and excluded
        $this->assertFalse((bool) PartnerOnlineState::where('user_id', $this->mitra->id)->first()->is_busy);
        $this->assertTrue($fresh->hasCancelledBy($this->mitra->id));

        // Activity log recorded
        $hasLog = \App\Models\ActivityLog::where('user_id', $this->mitra->id)
            ->where('action', 'scheduled_departure_expired')
            ->exists();
        $this->assertTrue($hasLog, 'ActivityLog record should exist');

        // Customer notified
        $custNotif = $this->customer->notifications()->latest()->first();
        $this->assertNotNull($custNotif);
        $this->assertEquals('Bantuan Dibatalkan', $custNotif->data['title']);
    }
}
