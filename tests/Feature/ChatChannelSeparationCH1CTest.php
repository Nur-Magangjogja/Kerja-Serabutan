<?php

namespace Tests\Feature;

use App\Livewire\Customer\Chat\Index as CustomerChatIndex;
use App\Livewire\Mitra\Chat\Index as MitraChatIndex;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpCancelMessage;
use App\Models\HelpCancelRequest;
use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use App\Models\User;
use App\Notifications\NewReportMessageNotification;
use App\Services\Territory\PartnerReportTerritoryResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ChatChannelSeparationCH1CTest extends TestCase
{
    use RefreshDatabase;

    protected City $cityA;
    protected District $districtA;
    protected City $cityB;
    protected District $districtB;

    protected User $customer;
    protected User $mitra;
    protected User $otherCustomer;
    protected User $adminA;
    protected User $adminB;
    protected Help $help;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cityA = City::create([
            'name'       => 'Kota Yogyakarta',
            'state_name' => 'DI Yogyakarta',
            'is_active'  => true,
            'latitude'   => -7.7956,
            'longitude'  => 110.3695,
        ]);

        $this->districtA = District::create([
            'city_id'   => $this->cityA->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);

        $this->cityB = City::create([
            'name'       => 'Kabupaten Sleman',
            'state_name' => 'DI Yogyakarta',
            'is_active'  => true,
            'latitude'   => -7.7167,
            'longitude'  => 110.3556,
        ]);

        $this->districtB = District::create([
            'city_id'   => $this->cityB->id,
            'name'      => 'Depok',
            'is_active' => true,
        ]);

        // Customer in Territory A
        $this->customer = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->districtA->id,
        ]);

        // Mitra in Territory A
        $this->mitra = User::factory()->create([
            'role'        => 'mitra',
            'status'      => 'active',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->districtA->id,
        ]);

        $this->otherCustomer = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->districtA->id,
        ]);

        // Admin A (Territory A)
        $this->adminA = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->districtA->id,
        ]);
        \Illuminate\Support\Facades\DB::table('admin_city')->insert([
            'user_id' => $this->adminA->id,
            'city_id' => $this->cityA->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \Illuminate\Support\Facades\DB::table('admin_district')->insert([
            'user_id'     => $this->adminA->id,
            'district_id' => $this->districtA->id,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Admin B (Territory B)
        $this->adminB = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => $this->cityB->id,
            'district_id' => $this->districtB->id,
        ]);
        \Illuminate\Support\Facades\DB::table('admin_city')->insert([
            'user_id' => $this->adminB->id,
            'city_id' => $this->cityB->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \Illuminate\Support\Facades\DB::table('admin_district')->insert([
            'user_id'     => $this->adminB->id,
            'district_id' => $this->districtB->id,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Help in Territory B
        $this->help = Help::create([
            'user_id'        => $this->customer->id,
            'mitra_id'       => $this->mitra->id,
            'title'          => 'Bantuan Reparasi AC Sleman',
            'description'    => 'Deskripsi bantuan di Sleman',
            'price'          => 100000,
            'status'         => 'in_progress',
            'address'        => 'Jl Kaliurang KM 9, Sleman',
            'city_id'        => $this->cityB->id,
            'district_id'    => $this->districtB->id,
            'latitude'       => -7.7100,
            'longitude'      => 110.4000,
            'payment_status' => 'paid',
        ]);
    }

    /**
     * 1. SUPPORT THREAD LIFECYCLE - Customer
     * Send 3 support messages sequentially.
     * Assert canonical support lifecycle preserved, no unintended new PartnerReport per message,
     * all messages belong to the same expected support thread.
     */
    public function test_customer_support_thread_reuse_sequential_messages(): void
    {
        Auth::login($this->customer);
        Cache::flush();

        $comp = Livewire::test(CustomerChatIndex::class)
            ->call('selectAdmin')
            ->assertSet('admin_tab', 'support');

        // Message 1
        $comp->set('message', 'Pesan Support 1')->call('sendMessage');
        $this->assertEquals(1, PartnerReport::where('reporter_id', $this->customer->id)->where('report_type', 'dukungan_umum')->count());
        $report1 = PartnerReport::where('reporter_id', $this->customer->id)->where('report_type', 'dukungan_umum')->first();

        // Message 2
        $comp->set('message', 'Pesan Support 2')->call('sendMessage');
        $this->assertEquals(1, PartnerReport::where('reporter_id', $this->customer->id)->where('report_type', 'dukungan_umum')->count());

        // Message 3
        $comp->set('message', 'Pesan Support 3')->call('sendMessage');
        $this->assertEquals(1, PartnerReport::where('reporter_id', $this->customer->id)->where('report_type', 'dukungan_umum')->count());

        // Assert all 3 messages belong to same support report
        $messages = PartnerReportMessage::where('partner_report_id', $report1->id)->orderBy('id')->get();
        $this->assertCount(3, $messages);
        $this->assertEquals('Pesan Support 1', $messages[0]->message);
        $this->assertEquals('Pesan Support 2', $messages[1]->message);
        $this->assertEquals('Pesan Support 3', $messages[2]->message);
    }

    /**
     * 1. SUPPORT THREAD LIFECYCLE - Mitra
     * Send 3 support messages sequentially.
     * Assert canonical support lifecycle preserved, no unintended new PartnerReport per message,
     * all messages belong to the same expected support thread.
     */
    public function test_mitra_support_thread_reuse_sequential_messages(): void
    {
        Auth::login($this->mitra);
        Cache::flush();

        $comp = Livewire::test(MitraChatIndex::class)
            ->call('selectAdmin')
            ->assertSet('admin_tab', 'support');

        // Message 1
        $comp->set('message', 'Mitra Support 1')->call('sendMessage');
        $this->assertEquals(1, PartnerReport::where('reporter_id', $this->mitra->id)->where('report_type', 'dukungan_umum')->count());
        $report1 = PartnerReport::where('reporter_id', $this->mitra->id)->where('report_type', 'dukungan_umum')->first();

        // Message 2
        $comp->set('message', 'Mitra Support 2')->call('sendMessage');
        $this->assertEquals(1, PartnerReport::where('reporter_id', $this->mitra->id)->where('report_type', 'dukungan_umum')->count());

        // Message 3
        $comp->set('message', 'Mitra Support 3')->call('sendMessage');
        $this->assertEquals(1, PartnerReport::where('reporter_id', $this->mitra->id)->where('report_type', 'dukungan_umum')->count());

        $messages = PartnerReportMessage::where('partner_report_id', $report1->id)->orderBy('id')->get();
        $this->assertCount(3, $messages);
        $this->assertEquals('Mitra Support 1', $messages[0]->message);
        $this->assertEquals('Mitra Support 2', $messages[1]->message);
        $this->assertEquals('Mitra Support 3', $messages[2]->message);
    }

    /**
     * 2. THREE-CHANNEL SIMULTANEOUS ISOLATION
     * Same user has support ticket, cancellation request, and investigation report.
     * Send SUPPORT-CH1, CANCEL-CH1, REPORT-CH1.
     * Assert each exists only in its expected persistence resource, with zero cross-write.
     */
    public function test_three_channel_simultaneous_isolation(): void
    {
        Auth::login($this->customer);
        Cache::flush();

        // 1. Create existing cancellation request
        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $this->help->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'requester_type'  => 'customer',
            'status'          => 'pending',
            'previous_status' => 'in_progress',
            'reason'          => 'Kendala waktu',
        ]);

        // 2. Create existing investigation report
        $investigationReport = PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra->id,
            'reported_help_id' => $this->help->id,
            'report_type'      => 'pelanggaran',
            'title'            => 'Investigasi Aduan Resmi',
            'message'          => 'Keluhan awal aduan',
            'status'           => 'investigating',
        ]);

        $comp = Livewire::test(CustomerChatIndex::class);

        // A. Send in Support Channel
        $comp->call('selectAdmin')
            ->assertSet('admin_tab', 'support')
            ->set('message', 'SUPPORT-CH1')
            ->call('sendMessage');

        // B. Send in Cancellation Channel
        $comp->call('switchAdminTab', 'cancellation')
            ->call('selectAdminCancel', $cancelReq->id)
            ->set('message', 'CANCEL-CH1')
            ->call('sendMessage');

        // C. Send in Report Investigation Channel
        $comp->call('switchAdminTab', 'report')
            ->call('selectAdminReport', $investigationReport->id)
            ->set('message', 'REPORT-CH1')
            ->call('sendMessage');

        // Assert SUPPORT-CH1 exists ONLY in support PartnerReportMessage
        $supportReport = PartnerReport::where('reporter_id', $this->customer->id)
            ->where('report_type', 'dukungan_umum')
            ->first();
        $this->assertNotNull($supportReport);
        $this->assertDatabaseHas('partner_report_messages', [
            'partner_report_id' => $supportReport->id,
            'message'           => 'SUPPORT-CH1',
        ]);
        $this->assertDatabaseMissing('help_cancel_messages', ['message' => 'SUPPORT-CH1']);
        $this->assertDatabaseMissing('partner_report_messages', [
            'partner_report_id' => $investigationReport->id,
            'message'           => 'SUPPORT-CH1',
        ]);

        // Assert CANCEL-CH1 exists ONLY in HelpCancelMessage for selected cancellation
        $this->assertDatabaseHas('help_cancel_messages', [
            'help_cancel_request_id' => $cancelReq->id,
            'message'                => 'CANCEL-CH1',
        ]);
        $this->assertDatabaseMissing('partner_report_messages', ['message' => 'CANCEL-CH1']);

        // Assert REPORT-CH1 exists ONLY in investigation PartnerReportMessage
        $this->assertDatabaseHas('partner_report_messages', [
            'partner_report_id' => $investigationReport->id,
            'message'           => 'REPORT-CH1',
        ]);
        $this->assertDatabaseMissing('help_cancel_messages', ['message' => 'REPORT-CH1']);
        $this->assertDatabaseMissing('partner_report_messages', [
            'partner_report_id' => $supportReport->id,
            'message'           => 'REPORT-CH1',
        ]);
    }

    /**
     * 3. TERRITORY ROUTING
     * Customer Profile = Territory A (Kota Yogyakarta)
     * Help = Territory B (Kabupaten Sleman)
     * Support target = Territory A
     * Cancellation target = Territory B
     * Investigation target = Territory B
     */
    public function test_territory_routing_across_channels(): void
    {
        Auth::login($this->customer);
        $resolver = app(PartnerReportTerritoryResolver::class);

        // A. Support Message
        Livewire::test(CustomerChatIndex::class)
            ->call('selectAdmin')
            ->set('message', 'Support message for territory check')
            ->call('sendMessage');

        $supportReport = PartnerReport::where('reporter_id', $this->customer->id)
            ->where('report_type', 'dukungan_umum')
            ->first();
        $this->assertNotNull($supportReport);
        $supportTerritory = $resolver->resolve($supportReport);
        $this->assertEquals('profile_reporter', $supportTerritory['source']);
        $this->assertEquals($this->cityA->id, $supportTerritory['city_id'], 'Support must target Profile Territory A city');
        $this->assertEquals($this->districtA->id, $supportTerritory['district_id'], 'Support must target Profile Territory A district');

        // B. Cancellation
        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $this->help->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'requester_type'  => 'customer',
            'status'          => 'pending',
            'previous_status' => 'in_progress',
            'reason'          => 'Alasan pembatalan',
        ]);
        $this->assertEquals($this->cityB->id, $cancelReq->help->city_id, 'Cancellation follows Help Territory B city');
        $this->assertEquals($this->districtB->id, $cancelReq->help->district_id, 'Cancellation follows Help Territory B district');

        // C. Investigation (Help-linked)
        $investigationReport = PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra->id,
            'reported_help_id' => $this->help->id,
            'report_type'      => 'pelanggaran',
            'title'            => 'Laporan Pelanggaran',
            'message'          => 'Pelanggaran Help',
            'status'           => 'investigating',
        ]);
        $invTerritory = $resolver->resolve($investigationReport);
        $this->assertEquals('help', $invTerritory['source']);
        $this->assertEquals($this->cityB->id, $invTerritory['city_id'], 'Help-linked report must target Help Territory B city');
        $this->assertEquals($this->districtB->id, $invTerritory['district_id'], 'Help-linked report must target Help Territory B district');
    }

    /**
     * 4. PROFILE MIGRATION SUPPORT
     * User has Profile A and existing support history.
     * Official migration: Profile A -> B.
     * Send NEW support message.
     * Expected: new Admin routing uses B, historical messages not rewritten.
     */
    public function test_profile_migration_support_routing(): void
    {
        Auth::login($this->customer);
        $resolver = app(PartnerReportTerritoryResolver::class);

        // 1. Initial support message while in Territory A
        Livewire::test(CustomerChatIndex::class)
            ->call('selectAdmin')
            ->set('message', 'Initial support in Territory A')
            ->call('sendMessage');

        $supportReport = PartnerReport::where('reporter_id', $this->customer->id)
            ->where('report_type', 'dukungan_umum')
            ->first();
        $this->assertNotNull($supportReport);
        $terrBefore = $resolver->resolve($supportReport);
        $this->assertEquals($this->cityA->id, $terrBefore['city_id']);

        // 2. Official Profile Migration: A -> B
        $this->customer->update([
            'city_id'     => $this->cityB->id,
            'district_id' => $this->districtB->id,
        ]);
        $this->customer->refresh();

        // 3. Send NEW support message after migration
        Livewire::test(CustomerChatIndex::class)
            ->call('selectAdmin')
            ->set('message', 'New support message after migration to B')
            ->call('sendMessage');

        // Territory resolution now reflects Territory B
        $terrAfter = $resolver->resolve($supportReport->fresh());
        $this->assertEquals('profile_reporter', $terrAfter['source']);
        $this->assertEquals($this->cityB->id, $terrAfter['city_id'], 'Routing must resolve to new Profile Territory B');
        $this->assertEquals($this->districtB->id, $terrAfter['district_id']);

        // Historical messages are preserved and not rewritten
        $allMsgs = PartnerReportMessage::where('partner_report_id', $supportReport->id)->orderBy('id')->get();
        $this->assertCount(2, $allMsgs);
        $this->assertEquals('Initial support in Territory A', $allMsgs[0]->message);
        $this->assertEquals('New support message after migration to B', $allMsgs[1]->message);
    }

    /**
     * 5. ADMIN MENU SEPARATION
     * Assert Admin Support listing includes dukungan_umum and excludes investigation reports.
     * Assert Admin Partner Reports listing excludes dukungan_umum.
     * Assert Cancellation listing contains cancellation resources only.
     */
    public function test_admin_menu_separation(): void
    {
        // Support report
        $support = PartnerReport::create([
            'reporter_id' => $this->customer->id,
            'category'    => 'dari_customer',
            'report_type' => 'dukungan_umum',
            'title'       => 'Tiket Bantuan Umum',
            'message'     => 'Pertanyaan umum',
            'status'      => 'pending',
        ]);

        // Investigation report
        $investigation = PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra->id,
            'category'         => 'dari_customer',
            'report_type'      => 'pelanggaran',
            'title'            => 'Laporan Investigasi Aduan',
            'message'          => 'Pelanggaran mitra',
            'status'           => 'pending',
        ]);

        // Cancellation request
        $cancel = HelpCancelRequest::create([
            'help_id'         => $this->help->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'requester_type'  => 'customer',
            'status'          => 'pending',
            'previous_status' => 'in_progress',
            'reason'          => 'Alasan pembatalan unik',
        ]);

        $this->actingAs($this->adminA);

        // A. Admin Support Listing: includes dukungan_umum, excludes investigation
        Livewire::test(\App\Livewire\Admin\Support\Index::class)
            ->assertSee('Tiket Bantuan Umum')
            ->assertDontSee('Laporan Investigasi Aduan');

        // B. Admin Partner Reports Listing: excludes dukungan_umum, includes investigation
        Livewire::test(\App\Livewire\Admin\Partners\Reports\Index::class)
            ->assertSee('Laporan Investigasi Aduan')
            ->assertDontSee('Tiket Bantuan Umum');

        // C. Cancellation Listing: contains cancellation resources only (Admin B manages Territory B of Help)
        $this->actingAs($this->adminB);
        Livewire::test(\App\Livewire\Admin\Disputes\Index::class)
            ->assertSee('Alasan pembatalan unik')
            ->assertDontSee('Tiket Bantuan Umum')
            ->assertDontSee('Laporan Investigasi Aduan');
    }

    /**
     * 6. NOTIFICATION SEPARATION
     * Support message -> category / destination = Admin Support
     * Investigation message -> category / destination = Partner Report investigation
     * Cancellation message -> category / destination = Cancellation review
     */
    public function test_notification_separation_across_channels(): void
    {
        Notification::fake();
        Auth::login($this->customer);

        // A. Support message notification
        $support = PartnerReport::create([
            'reporter_id' => $this->customer->id,
            'category'    => 'dari_customer',
            'report_type' => 'dukungan_umum',
            'title'       => 'Support Notification Check',
            'message'     => 'Pesan support',
            'status'      => 'in_progress',
        ]);

        $supportMsg = PartnerReportMessage::create([
            'partner_report_id' => $support->id,
            'sender_id'         => $this->customer->id,
            'recipient_type'    => 'admin',
            'message'           => 'Notif support test',
            'is_read'           => false,
        ]);

        Notification::assertSentTo($this->adminA, NewReportMessageNotification::class, function ($notif) use ($support) {
            $data = $notif->toArray($this->adminA);
            return $data['category'] === 'support' && str_contains($data['url'], 'support');
        });

        // B. Investigation message notification
        Notification::fake();
        $investigation = PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra->id,
            'reported_help_id' => $this->help->id,
            'report_type'      => 'pelanggaran',
            'title'            => 'Investigation Notif Check',
            'message'          => 'Pesan investigasi',
            'status'           => 'in_progress',
        ]);

        $invMsg = PartnerReportMessage::create([
            'partner_report_id' => $investigation->id,
            'sender_id'         => $this->customer->id,
            'recipient_type'    => 'admin',
            'message'           => 'Notif investigasi test',
            'is_read'           => false,
        ]);

        Notification::assertSentTo($this->adminB, NewReportMessageNotification::class, function ($notif) use ($investigation) {
            $data = $notif->toArray($this->adminB);
            return $data['category'] === 'report' && str_contains($data['url'], 'partners/reports');
        });

        // C. Cancellation message notification
        Notification::fake();
        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $this->help->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'requester_type'  => 'customer',
            'status'          => 'pending',
            'previous_status' => 'in_progress',
            'reason'          => 'Alasan pembatalan',
        ]);

        $cancelMsg = HelpCancelMessage::create([
            'help_cancel_request_id' => $cancelReq->id,
            'sender_id'              => $this->customer->id,
            'recipient_type'         => 'admin',
            'message'                => 'Notif cancel test',
            'is_read'                => false,
        ]);

        Notification::assertSentTo($this->adminB, \App\Notifications\HelpStatusNotification::class, function ($notif) {
            $data = $notif->toArray($this->adminB);
            return isset($data['url']) && str_contains($data['url'], 'cancellations');
        });
    }

    /**
     * 7. REPORT ACCESS SCOPE CONSISTENCY
     * Compare user-visible canonical report listing scope against resolveCustomerReport() and resolveMitraReport().
     * Resolver MUST NOT grant access to a report that the canonical user-side listing considers inaccessible.
     * Being only reported_user_id must remain insufficient.
     */
    public function test_report_access_scope_consistency(): void
    {
        Auth::login($this->customer);

        // Accessible Report 1: where customer is reporter
        $rep1 = PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra->id,
            'report_type'      => 'pelanggaran',
            'title'            => 'Report 1',
            'message'          => 'Test',
            'status'           => 'pending',
        ]);

        // Accessible Report 2: where customer is user of reportedHelp
        $rep2 = PartnerReport::create([
            'reporter_id'      => $this->mitra->id,
            'reported_user_id' => $this->customer->id,
            'reported_help_id' => $this->help->id,
            'report_type'      => 'pelanggaran',
            'title'            => 'Report 2 Help Linked',
            'message'          => 'Test',
            'status'           => 'pending',
        ]);

        // INACCESSIBLE Report 3: Other customer against someone else (unrelated)
        $rep3 = PartnerReport::create([
            'reporter_id'      => $this->otherCustomer->id,
            'reported_user_id' => $this->mitra->id,
            'report_type'      => 'pelanggaran',
            'title'            => 'Report 3 Inaccessible',
            'message'          => 'Test',
            'status'           => 'pending',
        ]);

        // INACCESSIBLE Report 4: Where customer is ONLY reported_user_id on non-help report
        $rep4 = PartnerReport::create([
            'reporter_id'      => $this->otherCustomer->id,
            'reported_user_id' => $this->customer->id,
            'report_type'      => 'pelanggaran',
            'title'            => 'Report 4 Customer is only reported_user_id',
            'message'          => 'Test',
            'status'           => 'pending',
        ]);

        // Evaluate Listing Query
        $userId = $this->customer->id;
        $listingIds = PartnerReport::where(function ($q) use ($userId) {
                $q->where('reporter_id', $userId)
                  ->orWhereHas('reportedHelp', fn($sub) => $sub->where('user_id', $userId));
            })
            ->where('report_type', '!=', 'dukungan_umum')
            ->pluck('id')
            ->all();

        $this->assertContains($rep1->id, $listingIds);
        $this->assertContains($rep2->id, $listingIds);
        $this->assertNotContains($rep3->id, $listingIds);
        $this->assertNotContains($rep4->id, $listingIds);

        // Test Livewire Customer Resolver
        $testComp = Livewire::test(CustomerChatIndex::class);

        // Accessible cases:
        $testComp->call('selectAdmin')
            ->call('switchAdminTab', 'report')
            ->call('selectAdminReport', $rep1->id)
            ->assertSet('selected_report_id', $rep1->id);

        $testComp->call('selectAdminReport', $rep2->id)
            ->assertSet('selected_report_id', $rep2->id);

        // Inaccessible cases (Fail closed):
        $testComp->call('selectAdminReport', $rep3->id)
            ->assertSet('selected_report_id', null, 'Resolver must fail-closed for inaccessible report 3');

        $testComp->call('selectAdminReport', $rep4->id)
            ->assertSet('selected_report_id', null, 'Resolver must fail-closed when user is only reported_user_id');
    }
}
