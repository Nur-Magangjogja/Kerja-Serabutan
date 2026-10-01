<?php

namespace Tests\Feature\Customer;

use App\Livewire\Customer\Helps\Create;
use App\Livewire\Customer\Helps\Index as CustomerIndex;
use App\Livewire\Mitra\Helps\AllHelps as MitraAllHelps;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Models\UserBalance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HelpSchedulingAndExpiryPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected City $city;
    protected District $district;
    protected User $customer;
    protected User $mitra;

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
        UserBalance::create(['user_id' => $this->customer->id, 'balance' => 500000]);

        $this->mitra = User::factory()->create([
            'role'        => 'mitra',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);
        UserBalance::create(['user_id' => $this->mitra->id, 'balance' => 0]);
        PartnerOnlineState::create([
            'user_id'         => $this->mitra->id,
            'latitude'        => -7.7956,
            'longitude'       => 110.3695,
            'matching_status' => PartnerOnlineState::STATUS_SEARCHING,
            'last_seen_at'    => now(),
        ]);
    }

    public function test_instant_help_remains_active_after_travel_minutes_pass()
    {
        $this->actingAs($this->customer);

        // Buat bantuan instan dengan batas waktu 6 jam
        Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_ON_SITE)
            ->set('title', 'Bantu Angkat Meja')
            ->set('description', 'Perlu bantuan angkat meja sekarang')
            ->set('amount', 25000)
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->call('setExpiryOption', '6_hours')
            ->call('save')
            ->assertHasNoErrors();

        $help = Help::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($help);
        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);

        // Majukan waktu 15 menit (melewati estimasi scheduled_at awal yang 5 menit)
        Carbon::setTestNow(now()->addMinutes(15));

        // Customer buka halaman index (trigger on-the-fly auto cancel)
        Livewire::test(CustomerIndex::class);

        // Jalankan juga scheduler artisan
        $this->artisan('helps:auto-cancel')->assertExitCode(0);

        // Bantuan HARUS TETAP AKTIF menunggu mitra, tidak boleh dibatalkan
        $help->refresh();
        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);
        $this->assertEquals(Help::ESCROW_STATUS_HELD, $help->escrow_status);
    }

    public function test_scheduled_help_appears_in_mitra_pool_and_can_be_taken_in_advance()
    {
        $this->actingAs($this->customer);

        $scheduledDate = now()->addDays(2)->format('Y-m-d');
        $scheduledTime = '09:00';

        // Buat bantuan terjadwal untuk 2 hari ke depan
        Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_ON_SITE)
            ->set('title', 'Bantu Cat Pagar Rumah')
            ->set('description', 'Pengecatan dijadwalkan 2 hari lagi')
            ->set('amount', 50000)
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->set('scheduled_date', $scheduledDate)
            ->set('scheduled_time', $scheduledTime)
            ->call('setExpiryOption', '24_hours')
            ->call('save')
            ->assertHasNoErrors();

        $help = Help::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($help);
        $this->assertEquals(Help::ORDER_MODE_SCHEDULED, $help->order_mode);
        // Mitra login dan melihat pool pekerjaan setelah waktu published_at tercapai
        $this->actingAs($this->mitra);

        // Majukan waktu ke published_at (waktu visibility pool)
        if ($help->published_at) {
            Carbon::setTestNow($help->published_at->copy()->addMinute());
        }

        Livewire::test(MitraAllHelps::class)
            ->call('setMitraLocation', -7.7956, 110.3695)
            ->assertSee('Bantu Cat Pagar Rumah')
            ->call('takeHelp', $help->id);

        $help->refresh();
        $this->assertEquals(Help::STATUS_TAKEN, $help->status);
        $this->assertEquals($this->mitra->id, $help->mitra_id);
    }

    public function test_scheduled_help_with_custom_departure_lead_minutes_is_persisted()
    {
        $this->actingAs($this->customer);

        $scheduledDate = now()->addDays(1)->format('Y-m-d');
        $scheduledTime = '07:00';

        Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_ON_SITE)
            ->set('title', 'Tugas Bersih Halaman Pagi')
            ->set('description', 'Dijadwalkan jam 7 pagi dengan jeda berangkat 45 menit')
            ->set('amount', 40000)
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->set('scheduled_date', $scheduledDate)
            ->set('scheduled_time', $scheduledTime)
            ->set('early_departure_minutes', 45)
            ->call('save')
            ->assertHasNoErrors();

        $help = Help::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($help);
        $this->assertEquals(Help::ORDER_MODE_SCHEDULED, $help->order_mode);
        $this->assertEquals(45, $help->early_departure_minutes);
        $this->assertEquals(45, $help->departure_lead_minutes);

        // Target: 07:00, Jeda: 45 min -> Buka: 06:15
        $this->assertEquals('06:15', $help->departure_window_opens_at->format('H:i'));
    }

    public function test_scheduled_help_departure_is_locked_before_departure_window()
    {
        $this->actingAs($this->customer);

        // Buat jadwal untuk 5 jam lagi (misal target jam 12:00) dengan default jeda 60 min
        $targetTime = now()->addHours(5);
        $scheduledDate = $targetTime->format('Y-m-d');
        $scheduledTime = $targetTime->format('H:i');

        Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_ON_SITE)
            ->set('title', 'Perbaikan Pintu')
            ->set('description', 'Perbaikan dijadwalkan siang nanti')
            ->set('amount', 50000)
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->set('scheduled_date', $scheduledDate)
            ->set('scheduled_time', $scheduledTime)
            ->set('early_departure_minutes', 60)
            ->call('save')
            ->assertHasNoErrors();

        $help = Help::where('user_id', $this->customer->id)->latest()->first();

        // Mitra ambil pesanan di awal
        $this->actingAs($this->mitra);
        app(\App\Services\HelpTransactionService::class)->takeHelp($help, $this->mitra);
        $help->refresh();

        $this->assertEquals(Help::STATUS_TAKEN, $help->status);
        $this->assertFalse($help->canPartnerStartDeparture());

        // Mitra mencoba mulai berangkat sekarang (sebelum jendela buka) -> Harus gagal
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tombol keberangkatan baru dapat diaktifkan');
        app(\App\Services\HelpTransactionService::class)->markOnTheWay($help, $this->mitra);
    }

    public function test_scheduled_help_departure_unlocks_when_within_departure_window()
    {
        $this->actingAs($this->customer);

        // Buat jadwal untuk 2 jam ke depan dengan jeda 60 min
        $targetTime = now()->addHours(2);
        $scheduledDate = $targetTime->format('Y-m-d');
        $scheduledTime = $targetTime->format('H:i');

        Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_ON_SITE)
            ->set('title', 'Tugas Potong Rumput')
            ->set('description', 'Potong rumput terjadwal')
            ->set('amount', 45000)
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->set('scheduled_date', $scheduledDate)
            ->set('scheduled_time', $scheduledTime)
            ->set('early_departure_minutes', 60)
            ->call('save')
            ->assertHasNoErrors();

        $help = Help::where('user_id', $this->customer->id)->latest()->first();

        // Mitra ambil pesanan
        $this->actingAs($this->mitra);
        app(\App\Services\HelpTransactionService::class)->takeHelp($help, $this->mitra);
        $help->refresh();

        // Majukan waktu 1 jam 5 menit (masuk jendela keberangkatan 60 min dan masih dalam batas grace 10 min)
        Carbon::setTestNow(now()->addMinutes(65));

        $this->assertTrue($help->canPartnerStartDeparture());

        // Mitra mulai berangkat -> Harus berhasil karena dalam batas toleransi
        app(\App\Services\HelpTransactionService::class)->markOnTheWay($help, $this->mitra);
        $help->refresh();

        $this->assertEquals(Help::STATUS_PARTNER_ON_THE_WAY, $help->status);
        $this->assertNotNull($help->partner_started_at);
    }

    public function test_send_departure_reminders_command_triggers_notification_when_due()
    {
        $this->actingAs($this->customer);

        // Buat jadwal untuk 1.5 jam ke depan dengan jeda 60 menit
        $targetTime = now()->addMinutes(90);
        $scheduledDate = $targetTime->format('Y-m-d');
        $scheduledTime = $targetTime->format('H:i');

        Livewire::test(Create::class)
            ->call('setServiceType', Help::SERVICE_TYPE_ON_SITE)
            ->set('title', 'Service AC')
            ->set('description', 'Service AC terjadwal')
            ->set('amount', 75000)
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->set('scheduled_date', $scheduledDate)
            ->set('scheduled_time', $scheduledTime)
            ->set('early_departure_minutes', 60)
            ->call('save')
            ->assertHasNoErrors();

        $help = Help::where('user_id', $this->customer->id)->latest()->first();

        // Mitra ambil pesanan
        $this->actingAs($this->mitra);
        app(\App\Services\HelpTransactionService::class)->takeHelp($help, $this->mitra);
        $help->refresh();

        // Jalankan command sebelum masuk jendela (masih 90 menit) -> Reminder belum dikirim
        $this->artisan('helps:send-departure-reminders')->assertExitCode(0);
        $help->refresh();
        $this->assertNull($help->departure_reminder_sent_at);

        // Majukan waktu 35 menit (sehingga sisa 55 menit s.d. jadwal, masuk jendela 60 menit)
        Carbon::setTestNow(now()->addMinutes(35));

        // Jalankan command -> Reminder harus terkirim
        $this->artisan('helps:send-departure-reminders')->assertExitCode(0);
        $help->refresh();
        $this->assertNotNull($help->departure_reminder_sent_at);

        // Pastikan notifikasi tersimpan di database untuk mitra
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->mitra->id,
            'type'          => \App\Notifications\HelpStatusNotification::class,
        ]);
    }
}

