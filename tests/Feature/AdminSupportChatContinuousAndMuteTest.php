<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSupportChatContinuousAndMuteTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;
    protected City $city;
    protected District $district;
    protected User $admin;
    protected User $customer;
    protected User $mitra;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->province = Province::create([
            'name'      => 'DI Yogyakarta',
            'is_active' => true,
        ]);

        $this->city = City::create([
            'name'        => 'Kabupaten Sleman',
            'province'    => 'DI Yogyakarta',
            'province_id' => $this->province->id,
            'is_active'   => true,
        ]);

        $this->district = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Ngaglik',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'name'        => 'Admin Sleman',
            'email'       => 'admin.sleman@sayabantu.com',
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);
        DB::table('admin_city')->insert([
            'user_id'    => $this->admin->id,
            'city_id'    => $this->city->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('admin_district')->insert([
            'user_id'     => $this->admin->id,
            'district_id' => $this->district->id,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $this->customer = User::factory()->create([
            'name'        => 'Customer Sleman',
            'email'       => 'cust.sleman@sayabantu.com',
            'role'        => 'customer',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);

        $this->mitra = User::factory()->create([
            'name'        => 'Mitra Sleman',
            'email'       => 'mitra.sleman@sayabantu.com',
            'role'        => 'mitra',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);
    }

    public function test_customer_support_chat_remains_single_continuous_thread_across_time(): void
    {
        $this->actingAs($this->customer);

        // 1. Pesan pertama dikirim
        Livewire::test(\App\Livewire\Customer\Chat\Index::class)
            ->call('selectAdmin')
            ->set('message', 'Pertanyaan pertama customer')
            ->call('sendMessage');

        $reportsCount = PartnerReport::where('reporter_id', $this->customer->id)
            ->where('report_type', 'dukungan_umum')
            ->count();
        $this->assertEquals(1, $reportsCount);

        $report = PartnerReport::where('reporter_id', $this->customer->id)
            ->where('report_type', 'dukungan_umum')
            ->first();
        $this->assertEquals('pending', $report->status);

        // 2. Admin membuka chat -> status otomatis menjadi in_progress
        $this->actingAs($this->admin);
        Livewire::test(\App\Livewire\Admin\Support\Chat::class, ['report' => $report]);
        $this->assertEquals('in_progress', $report->fresh()->status);

        // 3. Keesokan harinya, customer mengirim pesan lagi
        $this->actingAs($this->customer);
        Livewire::test(\App\Livewire\Customer\Chat\Index::class)
            ->call('selectAdmin')
            ->set('message', 'Pertanyaan lanjutan beberapa hari kemudian')
            ->call('sendMessage');

        // Pastikan TIDAK TERPISAH: tetap tepat 1 PartnerReport untuk customer ini
        $reportsCountAfter = PartnerReport::where('reporter_id', $this->customer->id)
            ->where('report_type', 'dukungan_umum')
            ->count();
        $this->assertEquals(1, $reportsCountAfter, 'Thread must not split into multiple support reports');

        // Log pesan tersambung utuh (2 pesan dalam thread yang sama)
        $messages = PartnerReportMessage::where('partner_report_id', $report->id)->orderBy('id')->get();
        $this->assertCount(2, $messages);
        $this->assertEquals('Pertanyaan pertama customer', $messages[0]->message);
        $this->assertEquals('Pertanyaan lanjutan beberapa hari kemudian', $messages[1]->message);

        // Status kembali pending (menunggu respon) untuk pesan baru
        $this->assertEquals('pending', $report->fresh()->status);
    }

    public function test_mitra_support_chat_remains_single_continuous_thread_across_time(): void
    {
        $this->actingAs($this->mitra);

        // 1. Pesan pertama
        Livewire::test(\App\Livewire\Mitra\Chat\Index::class)
            ->call('selectAdmin')
            ->set('message', 'Pertanyaan komisi dari mitra')
            ->call('sendMessage');

        $report = PartnerReport::where('reporter_id', $this->mitra->id)
            ->where('report_type', 'dukungan_umum')
            ->first();
        $this->assertNotNull($report);
        $this->assertEquals('pending', $report->status);

        // 2. Admin buka chat
        $this->actingAs($this->admin);
        Livewire::test(\App\Livewire\Admin\Support\Chat::class, ['report' => $report]);
        $this->assertEquals('in_progress', $report->fresh()->status);

        // 3. Mitra mengirim pesan kedua
        $this->actingAs($this->mitra);
        Livewire::test(\App\Livewire\Mitra\Chat\Index::class)
            ->call('selectAdmin')
            ->set('message', 'Pertanyaan pencairan berikutnya')
            ->call('sendMessage');

        $reportsCount = PartnerReport::where('reporter_id', $this->mitra->id)
            ->where('report_type', 'dukungan_umum')
            ->count();
        $this->assertEquals(1, $reportsCount, 'Mitra thread must not split');

        $messages = PartnerReportMessage::where('partner_report_id', $report->id)->orderBy('id')->get();
        $this->assertCount(2, $messages);
        $this->assertEquals('pending', $report->fresh()->status);
    }

    public function test_admin_can_mute_and_unmute_support_chat_and_badge_suppresses_tanda(): void
    {
        // Buat 1 dukungan umum
        $report = PartnerReport::create([
            'reporter_id' => $this->customer->id,
            'category'    => 'dari_customer',
            'report_type' => 'dukungan_umum',
            'title'       => 'Pusat Bantuan Pelanggan',
            'message'     => 'Bantuan penting',
            'status'      => 'pending',
        ]);

        $this->actingAs($this->admin);

        // Sebelum dibisukan, badge count di sidebar adalah 1
        $this->assertEquals(1, PartnerReport::getActiveSupportCountForUser($this->admin));
        $this->assertFalse($report->isMuted());

        // Admin membisukan chat selama 8 jam
        Livewire::test(\App\Livewire\Admin\Support\Chat::class, ['report' => $report])
            ->call('mute', '8_hours');

        $report->refresh();
        $this->assertTrue($report->isMuted());
        $this->assertNotNull($report->muted_until);

        // Tanda di menu chat admin TIDAK MEMUNCULKAN badge (count = 0)
        $this->assertEquals(0, PartnerReport::getActiveSupportCountForUser($this->admin));

        // Admin membunyikan kembali (unmute)
        Livewire::test(\App\Livewire\Admin\Support\Chat::class, ['report' => $report])
            ->call('unmute');

        $report->refresh();
        $this->assertFalse($report->isMuted());
        $this->assertNull($report->muted_until);

        // Tanda di menu chat admin kembali muncul (count = 1)
        $this->assertEquals(1, PartnerReport::getActiveSupportCountForUser($this->admin));
    }

    public function test_muted_support_chat_suppresses_new_message_notification(): void
    {
        Notification::fake();

        $report = PartnerReport::create([
            'reporter_id' => $this->customer->id,
            'category'    => 'dari_customer',
            'report_type' => 'dukungan_umum',
            'title'       => 'Pusat Bantuan Pelanggan',
            'message'     => 'Bantuan pesan',
            'status'      => 'pending',
            'muted_until' => now()->addDays(1), // Sedang dibisukan!
        ]);

        // Customer mengirim pesan saat chat sedang dibisukan
        PartnerReportMessage::create([
            'partner_report_id' => $report->id,
            'sender_id'         => $this->customer->id,
            'recipient_type'    => 'admin',
            'message'           => 'Pesan ketika sedang dibisukan',
            'is_read'           => false,
        ]);

        // Admin TIDAK menerima notifikasi karena chat dibisukan
        Notification::assertNotSentTo(
            $this->admin,
            \App\Notifications\NewReportMessageNotification::class
        );
    }
}
