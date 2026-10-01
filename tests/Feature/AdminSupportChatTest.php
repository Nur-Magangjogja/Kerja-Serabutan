<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpCancelMessage;
use App\Models\HelpCancelRequest;
use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSupportChatTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;
    protected City $cityA;
    protected City $cityB;
    protected District $districtA;
    protected District $districtB;
    protected User $adminA;
    protected User $adminB;
    protected User $customerA;
    protected User $mitraA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->province = Province::create([
            'name'      => 'DI Yogyakarta',
            'is_active' => true,
        ]);

        $this->cityA = City::create([
            'name'        => 'Kota Yogyakarta',
            'province'    => 'DI Yogyakarta',
            'province_id' => $this->province->id,
            'is_active'   => true,
        ]);

        $this->cityB = City::create([
            'name'        => 'Kabupaten Sleman',
            'province'    => 'DI Yogyakarta',
            'province_id' => $this->province->id,
            'is_active'   => true,
        ]);

        $this->districtA = District::create([
            'city_id'   => $this->cityA->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);

        $this->districtB = District::create([
            'city_id'   => $this->cityB->id,
            'name'      => 'Depok',
            'is_active' => true,
        ]);

        // Admin Wilayah A (assigned to District A)
        $this->adminA = User::factory()->create([
            'name'        => 'Admin Wilayah A',
            'email'       => 'admin.a@sayabantu.com',
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->cityA->id,
            'district_id' => $this->districtA->id,
        ]);
        DB::table('admin_city')->insert([
            'user_id' => $this->adminA->id,
            'city_id' => $this->cityA->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('admin_district')->insert([
            'user_id'     => $this->adminA->id,
            'district_id' => $this->districtA->id,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Admin Wilayah B (assigned to District B)
        $this->adminB = User::factory()->create([
            'name'        => 'Admin Wilayah B',
            'email'       => 'admin.b@sayabantu.com',
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->cityB->id,
            'district_id' => $this->districtB->id,
        ]);
        DB::table('admin_city')->insert([
            'user_id' => $this->adminB->id,
            'city_id' => $this->cityB->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('admin_district')->insert([
            'user_id'     => $this->adminB->id,
            'district_id' => $this->districtB->id,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Customer in District A
        $this->customerA = User::factory()->create([
            'name'        => 'Customer A',
            'email'       => 'customer.a@sayabantu.com',
            'role'        => 'customer',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->cityA->id,
            'district_id' => $this->districtA->id,
        ]);

        // Mitra in District A
        $this->mitraA = User::factory()->create([
            'name'        => 'Mitra A',
            'email'       => 'mitra.a@sayabantu.com',
            'role'        => 'mitra',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->cityA->id,
            'district_id' => $this->districtA->id,
        ]);
    }

    // 1. Customer mengirim Chat Admin pertama kali -> PartnerReport dukungan_umum dibuat
    public function test_customer_first_admin_chat_creates_dukungan_umum_partner_report()
    {
        $this->actingAs($this->customerA);

        Livewire::test(\App\Livewire\Customer\Chat\Index::class)
            ->call('selectAdmin', null, null, 'report')
            ->set('message', 'Halo Admin, saya butuh informasi platform.')
            ->call('sendMessage');

        $report = PartnerReport::where('reporter_id', $this->customerA->id)
            ->where('report_type', 'dukungan_umum')
            ->first();

        $this->assertNotNull($report, 'PartnerReport dukungan_umum should be created');
        $this->assertEquals('dari_customer', $report->category);
        $this->assertEquals('in_progress', $report->status);

        $message = PartnerReportMessage::where('partner_report_id', $report->id)->first();
        $this->assertNotNull($message);
        $this->assertEquals($this->customerA->id, $message->sender_id);
        $this->assertEquals('admin', $message->recipient_type);
        $this->assertEquals('Halo Admin, saya butuh informasi platform.', $message->message);
    }

    // 2. Customer mengirim pesan berikutnya -> memakai support conversation yang sama
    public function test_customer_subsequent_message_reuses_same_support_conversation()
    {
        $this->actingAs($this->customerA);

        $component = Livewire::test(\App\Livewire\Customer\Chat\Index::class)
            ->call('selectAdmin', null, null, 'report')
            ->set('message', 'Pesan pertama')
            ->call('sendMessage');

        $firstReport = PartnerReport::where('reporter_id', $this->customerA->id)
            ->where('report_type', 'dukungan_umum')
            ->first();

        // Send second message
        $component->set('message', 'Pesan kedua lanjutan')
            ->call('sendMessage');

        $reportsCount = PartnerReport::where('reporter_id', $this->customerA->id)
            ->where('report_type', 'dukungan_umum')
            ->count();

        $this->assertEquals(1, $reportsCount, 'Subsequent message must reuse the same support report');

        $messagesCount = PartnerReportMessage::where('partner_report_id', $firstReport->id)->count();
        $this->assertEquals(2, $messagesCount);
    }

    // 3. Customer yang memiliki Laporan Aduan lama -> Chat Admin tidak memakai Laporan Aduan tersebut
    public function test_customer_with_old_dispute_report_does_not_mix_support_chat()
    {
        // Old dispute report by Customer A against a mitra
        $disputeReport = PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_user_id' => $this->mitraA->id,
            'category'         => 'dari_customer',
            'report_type'      => 'penipuan',
            'title'            => 'Laporan Penipuan Lama',
            'message'          => 'Mitra meminta transfer di luar aplikasi',
            'status'           => 'pending',
        ]);

        $this->actingAs($this->customerA);

        Livewire::test(\App\Livewire\Customer\Chat\Index::class)
            ->call('selectAdmin', null, null, 'report')
            ->set('message', 'Pertanyaan umum ke Admin')
            ->call('sendMessage');

        // Dispute report must NOT have this message
        $disputeMessages = PartnerReportMessage::where('partner_report_id', $disputeReport->id)->count();
        $this->assertEquals(0, $disputeMessages, 'Old dispute report must not receive general support messages');

        // Support report must be created separately
        $supportReport = PartnerReport::where('reporter_id', $this->customerA->id)
            ->where('report_type', 'dukungan_umum')
            ->first();
        $this->assertNotNull($supportReport);
        $this->assertNotEquals($disputeReport->id, $supportReport->id);
    }

    // 4. Mitra mengirim Chat Admin -> reporter_id = mitra, category = dari_mitra, report_type = dukungan_umum
    public function test_mitra_first_admin_chat_creates_dukungan_umum_with_mitra_as_reporter()
    {
        $this->actingAs($this->mitraA);

        Livewire::test(\App\Livewire\Mitra\Chat\Index::class)
            ->call('selectAdmin', null, null, 'report')
            ->set('message', 'Halo Admin, saya ingin konsultasi teknis.')
            ->call('sendMessage');

        $report = PartnerReport::where('reporter_id', $this->mitraA->id)
            ->where('report_type', 'dukungan_umum')
            ->first();

        $this->assertNotNull($report, 'PartnerReport with reporter_id = mitra must be created');
        $this->assertEquals('dari_mitra', $report->category);
        $this->assertNull($report->reported_user_id, 'reported_user_id should be null for general support');
    }

    // 5. Mitra dengan report lama sebagai reported_user_id -> support message tidak masuk ke report tersebut
    public function test_mitra_with_old_report_as_reported_user_does_not_mix_support_chat()
    {
        // Old report where Mitra A was reported by Customer A
        $reportedAgainstMitra = PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_user_id' => $this->mitraA->id,
            'category'         => 'dari_customer',
            'report_type'      => 'mitra_berperilaku_buruk',
            'title'            => 'Laporan Perilaku Buruk',
            'message'          => 'Keluhan customer terhadap mitra',
            'status'           => 'pending',
        ]);

        $this->actingAs($this->mitraA);

        Livewire::test(\App\Livewire\Mitra\Chat\Index::class)
            ->call('selectAdmin', null, null, 'report')
            ->set('message', 'Mitra meminta bantuan akun ke Admin')
            ->call('sendMessage');

        // Old report must not have the new message
        $disputeMessages = PartnerReportMessage::where('partner_report_id', $reportedAgainstMitra->id)->count();
        $this->assertEquals(0, $disputeMessages);

        // A new support report with reporter_id = mitraA must be created
        $supportReport = PartnerReport::where('reporter_id', $this->mitraA->id)
            ->where('report_type', 'dukungan_umum')
            ->first();
        $this->assertNotNull($supportReport);
        $this->assertEquals($this->mitraA->id, $supportReport->reporter_id);
    }

    // 6. Admin Support Inbox -> hanya menampilkan dukungan_umum
    public function test_admin_support_inbox_only_displays_dukungan_umum()
    {
        // 1 dispute report
        PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_user_id' => $this->mitraA->id,
            'category'         => 'dari_customer',
            'report_type'      => 'penipuan',
            'title'            => 'Dispute Aduan',
            'message'          => 'Pesan sengketa',
            'status'           => 'pending',
        ]);

        // 1 support report
        $support = PartnerReport::create([
            'reporter_id' => $this->customerA->id,
            'category'    => 'dari_customer',
            'report_type' => 'dukungan_umum',
            'title'       => 'Dukungan Umum Customer',
            'message'     => 'Tolong bantu saya',
            'status'      => 'pending',
        ]);

        $this->actingAs($this->adminA);

        Livewire::test(\App\Livewire\Admin\Support\Index::class)
            ->assertSee('Dukungan Umum Customer')
            ->assertDontSee('Dispute Aduan');
    }

    // 7. Laporan Aduan -> tidak menampilkan dukungan_umum
    public function test_admin_partners_reports_index_excludes_dukungan_umum()
    {
        PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_user_id' => $this->mitraA->id,
            'category'         => 'dari_customer',
            'report_type'      => 'penipuan',
            'title'            => 'Laporan Penipuan Asli',
            'message'          => 'Pesan penipuan',
            'status'           => 'pending',
        ]);

        PartnerReport::create([
            'reporter_id' => $this->customerA->id,
            'category'    => 'dari_customer',
            'report_type' => 'dukungan_umum',
            'title'       => 'Pusat Bantuan Rahasia',
            'message'     => 'Pesan bantuan',
            'status'      => 'pending',
        ]);

        $this->actingAs($this->adminA);

        Livewire::test(\App\Livewire\Admin\Partners\Reports\Index::class)
            ->assertSee('Laporan Penipuan Asli')
            ->assertDontSee('Pusat Bantuan Rahasia');
    }

    // 8. Laporan pending/in-progress/resolved count -> tidak menghitung dukungan_umum
    public function test_admin_partners_reports_counts_exclude_dukungan_umum()
    {
        PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_user_id' => $this->mitraA->id,
            'category'         => 'dari_customer',
            'report_type'      => 'penipuan',
            'title'            => 'Aduan 1',
            'message'          => 'Aduan valid',
            'status'           => 'pending',
        ]);

        // 3 Dukungan umum
        for ($i = 0; $i < 3; $i++) {
            PartnerReport::create([
                'reporter_id' => $this->customerA->id,
                'category'    => 'dari_customer',
                'report_type' => 'dukungan_umum',
                'title'       => "Support {$i}",
                'message'     => 'Support msg',
                'status'      => 'pending',
            ]);
        }

        $this->actingAs($this->adminA);

        Livewire::test(\App\Livewire\Admin\Partners\Reports\Index::class)
            ->assertViewHas('totalPending', 1); // Only the 1 dispute report, not 4!
    }

    // 9. Badge Laporan Aduan tidak menghitung support conversation
    public function test_badge_laporan_aduan_does_not_count_dukungan_umum()
    {
        PartnerReport::create([
            'reporter_id' => $this->customerA->id,
            'category'    => 'dari_customer',
            'report_type' => 'dukungan_umum',
            'title'       => 'Support Chat',
            'message'     => 'Support msg',
            'status'      => 'pending',
        ]);

        $reportsCount = PartnerReport::getActiveReportsCountForUser($this->adminA);
        $this->assertEquals(0, $reportsCount, 'getActiveReportsCountForUser must exclude dukungan_umum');

        $supportCount = PartnerReport::getActiveSupportCountForUser($this->adminA);
        $this->assertEquals(1, $supportCount, 'getActiveSupportCountForUser must count dukungan_umum');
    }

    // 10. Investigation Report Chat existing tetap bekerja
    public function test_investigation_report_chat_still_works()
    {
        $report = PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_user_id' => $this->mitraA->id,
            'category'         => 'dari_customer',
            'report_type'      => 'penipuan',
            'title'            => 'Laporan Investigasi',
            'message'          => 'Investigasi 3 pihak',
            'status'           => 'pending',
        ]);

        $this->actingAs($this->adminA);

        Livewire::test(\App\Livewire\Admin\Partners\Reports\Chat::class, ['report' => $report])
            ->set('activeTab', 'customer')
            ->set('message', 'Halo pelapor, admin sedang memverifikasi laporan ini.')
            ->call('sendMessage');

        $this->assertDatabaseHas('partner_report_messages', [
            'partner_report_id' => $report->id,
            'sender_id'         => $this->adminA->id,
            'recipient_type'    => 'customer',
            'message'           => 'Halo pelapor, admin sedang memverifikasi laporan ini.',
        ]);
    }

    // 11. Cancellation Chat existing tetap bekerja
    public function test_cancellation_chat_existing_still_works()
    {
        $help = Help::create([
            'user_id'     => $this->customerA->id,
            'mitra_id'    => $this->mitraA->id,
            'city_id'     => $this->cityA->id,
            'district_id' => $this->districtA->id,
            'title'       => 'Bantuan Cancel',
            'description' => 'Deskripsi',
            'status'      => Help::STATUS_TAKEN,
            'order_mode'  => Help::ORDER_MODE_INSTANT,
        ]);

        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $help->id,
            'customer_id'     => $this->customerA->id,
            'partner_id'      => $this->mitraA->id,
            'district_id'     => $this->districtA->id,
            'requester_type'  => 'customer',
            'previous_status' => Help::STATUS_TAKEN,
            'reason'          => 'Mitra tidak bergerak',
            'status'          => 'pending',
        ]);

        $this->actingAs($this->customerA);

        Livewire::test(\App\Livewire\Customer\Chat\Index::class)
            ->call('selectAdmin', null, $cancelReq->id, 'cancellation')
            ->set('message', 'Mohon tinjau pembatalan saya.')
            ->call('sendMessage');

        $this->assertDatabaseHas('help_cancel_messages', [
            'help_cancel_request_id' => $cancelReq->id,
            'sender_id'              => $this->customerA->id,
            'message'                => 'Mohon tinjau pembatalan saya.',
        ]);
    }

    // 12. Customer <-> Mitra chat existing tetap bekerja
    public function test_customer_mitra_regular_chat_still_works()
    {
        $help = Help::create([
            'user_id'     => $this->customerA->id,
            'mitra_id'    => $this->mitraA->id,
            'city_id'     => $this->cityA->id,
            'district_id' => $this->districtA->id,
            'title'       => 'Pekerjaan Harian',
            'description' => 'Deskripsi pekerjaan',
            'status'      => Help::STATUS_TAKEN,
            'order_mode'  => Help::ORDER_MODE_INSTANT,
        ]);

        $this->actingAs($this->customerA);

        Livewire::test(\App\Livewire\Customer\Chat\Index::class)
            ->call('selectPartner', $this->mitraA->id, $help->id)
            ->set('message', 'Halo Mitra, saya menunggu di lokasi.')
            ->call('sendMessage');

        $this->assertDatabaseHas('chats', [
            'customer_id' => $this->customerA->id,
            'mitra_id'    => $this->mitraA->id,
            'help_id'     => $help->id,
            'message'     => 'Halo Mitra, saya menunggu di lokasi.',
        ]);
    }

    // 13. Admin wilayah A tidak dapat membuka support wilayah B
    public function test_admin_region_a_cannot_access_support_from_region_b()
    {
        // User in District B
        $userB = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->cityB->id,
            'district_id' => $this->districtB->id,
        ]);

        $supportB = PartnerReport::create([
            'reporter_id' => $userB->id,
            'category'    => 'dari_customer',
            'report_type' => 'dukungan_umum',
            'title'       => 'Support Wilayah B',
            'message'     => 'Halo Admin Wilayah B',
            'status'      => 'pending',
        ]);

        // Admin A tries to access support B -> 403 Forbidden
        $this->actingAs($this->adminA);

        $response = $this->get(route('admin.support.chat', $supportB->id));
        $response->assertStatus(403);
    }

    // 14. Admin wilayah yang berwenang dapat membaca dan me-reply
    public function test_authorized_admin_can_read_and_reply_support()
    {
        $supportA = PartnerReport::create([
            'reporter_id' => $this->customerA->id,
            'category'    => 'dari_customer',
            'report_type' => 'dukungan_umum',
            'title'       => 'Bantuan Wilayah A',
            'message'     => 'Pertanyaan pelanggan',
            'status'      => 'pending',
        ]);

        $this->actingAs($this->adminA);

        Livewire::test(\App\Livewire\Admin\Support\Chat::class, ['report' => $supportA])
            ->set('message', 'Halo Customer, kami dari Tim Admin siap membantu.')
            ->call('sendMessage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('partner_report_messages', [
            'partner_report_id' => $supportA->id,
            'sender_id'         => $this->adminA->id,
            'recipient_type'    => 'customer',
            'message'           => 'Halo Customer, kami dari Tim Admin siap membantu.',
        ]);

        $this->assertEquals('in_progress', $supportA->fresh()->status);
    }

    // 15. Admin inactive tidak dapat membuka/reply karena middleware Phase 1
    public function test_inactive_admin_cannot_access_support_routes()
    {
        $this->adminA->update(['status' => 'inactive']);

        $this->actingAs($this->adminA);

        $response = $this->get(route('admin.support.index'));
        // EnsureAdmin logs out inactive admin and redirects to login
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // 16. Support reply Admin masuk PartnerReportMessage bukan tabel baru
    public function test_support_reply_admin_stored_in_partner_report_message()
    {
        $supportA = PartnerReport::create([
            'reporter_id' => $this->customerA->id,
            'category'    => 'dari_customer',
            'report_type' => 'dukungan_umum',
            'title'       => 'Pusat Bantuan',
            'message'     => 'Pertanyaan pelanggan',
            'status'      => 'pending',
        ]);

        $this->actingAs($this->adminA);

        Livewire::test(\App\Livewire\Admin\Support\Chat::class, ['report' => $supportA])
            ->set('message', 'Balasan resmi Admin Wilayah')
            ->call('sendMessage');

        $this->assertDatabaseHas('partner_report_messages', [
            'partner_report_id' => $supportA->id,
            'sender_id'         => $this->adminA->id,
            'message'           => 'Balasan resmi Admin Wilayah',
        ]);
    }

    // 17. Tidak ada migration Chat Admin baru
    public function test_no_new_migrations_created_for_chat_admin()
    {
        $migrations = glob(database_path('migrations/*support*'));
        $chatAdminMigrations = glob(database_path('migrations/*admin_support*'));
        $this->assertEmpty($migrations, 'No migration should be created for support');
        $this->assertEmpty($chatAdminMigrations, 'No migration should be created for admin support');
    }

    // 18. Notification support tidak bocor ke Admin wilayah tidak berwenang
    public function test_support_notification_does_not_leak_to_unauthorized_regional_admin()
    {
        Notification::fake();

        $supportA = PartnerReport::create([
            'reporter_id' => $this->customerA->id,
            'category'    => 'dari_customer',
            'report_type' => 'dukungan_umum',
            'title'       => 'Support Notification Test',
            'message'     => 'Pesan uji notifikasi',
            'status'      => 'pending',
        ]);

        // Customer in District A sends message
        PartnerReportMessage::create([
            'partner_report_id' => $supportA->id,
            'sender_id'         => $this->customerA->id,
            'recipient_type'    => 'admin',
            'message'           => 'Pesan baru dari pelanggan di Wilayah A',
            'is_read'           => false,
        ]);

        // Admin A (District A) MUST receive notification
        Notification::assertSentTo(
            $this->adminA,
            \App\Notifications\NewReportMessageNotification::class
        );

        // Admin B (District B) MUST NOT receive notification (No regional leak!)
        Notification::assertNotSentTo(
            $this->adminB,
            \App\Notifications\NewReportMessageNotification::class
        );
    }

    // 19. Customer end-to-end Chat Admin flow
    public function test_customer_admin_chat_end_to_end_flow()
    {
        // Step 1: Customer sends message
        $this->actingAs($this->customerA);
        Livewire::test(\App\Livewire\Customer\Chat\Index::class)
            ->call('selectAdmin')
            ->set('message', 'Halo Admin, saya butuh info panduan layanan.')
            ->call('sendMessage');

        $supportReport = PartnerReport::where('reporter_id', $this->customerA->id)
            ->where('report_type', 'dukungan_umum')
            ->first();

        $this->assertNotNull($supportReport);

        // Step 2: Authorized Admin reads and replies
        $this->actingAs($this->adminA);
        Livewire::test(\App\Livewire\Admin\Support\Chat::class, ['report' => $supportReport])
            ->set('message', 'Halo Customer, silakan cek menu panduan di profil Anda.')
            ->call('sendMessage');

        $adminReply = PartnerReportMessage::where('partner_report_id', $supportReport->id)
            ->where('sender_id', $this->adminA->id)
            ->first();

        $this->assertNotNull($adminReply);
        $this->assertEquals('Halo Customer, silakan cek menu panduan di profil Anda.', $adminReply->message);

        // Step 3: Customer reopens chat
        $this->actingAs($this->customerA);
        $custChat = Livewire::test(\App\Livewire\Customer\Chat\Index::class)
            ->call('selectAdmin');

        // Customer sees admin reply, uses same conversation, not chats table
        $messages = $custChat->instance()->getChatMessages();
        $this->assertNotEmpty($messages);
        $lastMsg = $messages->last();
        $this->assertEquals('admin', $lastMsg->sender_type);
        $this->assertEquals('Halo Customer, silakan cek menu panduan di profil Anda.', $lastMsg->message);
        $this->assertDatabaseMissing('chats', [
            'message' => 'Halo Customer, silakan cek menu panduan di profil Anda.',
        ]);
    }

    // 20. Mitra end-to-end Chat Admin flow
    public function test_mitra_admin_chat_end_to_end_flow()
    {
        // Create an old dispute report where Mitra was reported
        $oldDispute = PartnerReport::create([
            'reporter_id'      => $this->customerA->id,
            'reported_user_id' => $this->mitraA->id,
            'category'         => 'dari_customer',
            'report_type'      => 'pekerjaan_tidak_selesai',
            'title'            => 'Laporan Dispute Lama',
            'message'          => 'Pekerjaan belum beres',
            'status'           => 'resolved',
        ]);

        // Step 1: Mitra sends message
        $this->actingAs($this->mitraA);
        Livewire::test(\App\Livewire\Mitra\Chat\Index::class)
            ->call('selectAdmin')
            ->set('message', 'Selamat siang Admin, tanya pencairan dana.')
            ->call('sendMessage');

        $supportReport = PartnerReport::where('reporter_id', $this->mitraA->id)
            ->where('report_type', 'dukungan_umum')
            ->first();

        $this->assertNotNull($supportReport);
        $this->assertNotEquals($oldDispute->id, $supportReport->id);

        // Step 2: Authorized Admin reads and replies
        $this->actingAs($this->adminA);
        Livewire::test(\App\Livewire\Admin\Support\Chat::class, ['report' => $supportReport])
            ->set('message', 'Pencairan diproses maksimal 1x24 jam kerja.')
            ->call('sendMessage');

        // Step 3: Mitra reopens chat
        $this->actingAs($this->mitraA);
        $mitraChat = Livewire::test(\App\Livewire\Mitra\Chat\Index::class)
            ->call('selectAdmin');

        $messages = $mitraChat->instance()->getChatMessages();
        $this->assertNotEmpty($messages);
        $lastMsg = $messages->last();
        $this->assertEquals('admin', $lastMsg->sender_type);
        $this->assertEquals('Pencairan diproses maksimal 1x24 jam kerja.', $lastMsg->message);

        // Assert dispute messages untouched
        $this->assertDatabaseMissing('partner_report_messages', [
            'partner_report_id' => $oldDispute->id,
            'message'           => 'Pencairan diproses maksimal 1x24 jam kerja.',
        ]);
    }

    // 21. Super Admin support inbox & chat verification
    public function test_superadmin_support_inbox_and_chat_access()
    {
        $superAdmin = User::factory()->create([
            'role'     => 'super_admin',
            'status'   => 'active',
            'verified' => true,
        ]);

        $support = PartnerReport::create([
            'reporter_id' => $this->customerA->id,
            'category'    => 'dari_customer',
            'report_type' => 'dukungan_umum',
            'title'       => 'Pusat Bantuan Umum SuperAdmin',
            'message'     => 'Pesan dari customer',
            'status'      => 'pending',
        ]);

        $this->actingAs($superAdmin);

        // 1. Super Admin can view Inbox
        $inboxRes = $this->get(route('superadmin.support.index'));
        $inboxRes->assertStatus(200);

        // 2. Super Admin can view Chat page without 403
        $chatRes = $this->get(route('superadmin.support.chat', $support->id));
        $chatRes->assertStatus(200);

        // 3. Super Admin can reply
        Livewire::test(\App\Livewire\Admin\Support\Chat::class, ['report' => $support])
            ->set('message', 'Balasan dari Super Admin Pusat')
            ->call('sendMessage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('partner_report_messages', [
            'partner_report_id' => $support->id,
            'sender_id'         => $superAdmin->id,
            'message'           => 'Balasan dari Super Admin Pusat',
        ]);
    }
}

