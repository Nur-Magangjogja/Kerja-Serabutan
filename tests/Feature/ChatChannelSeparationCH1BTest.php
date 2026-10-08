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
use App\Services\Territory\PartnerReportTerritoryResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class ChatChannelSeparationCH1BTest extends TestCase
{
    use RefreshDatabase;

    protected City $city1;
    protected District $district1;
    protected City $city2;
    protected District $district2;

    protected User $customer;
    protected User $mitra;
    protected User $otherCustomer;
    protected User $otherMitra;
    protected Help $help;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city1 = City::create([
            'name'       => 'Kota Yogyakarta',
            'state_name' => 'DI Yogyakarta',
            'is_active'  => true,
            'latitude'   => -7.7956,
            'longitude'  => 110.3695,
        ]);

        $this->district1 = District::create([
            'city_id'   => $this->city1->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);

        $this->city2 = City::create([
            'name'       => 'Kabupaten Sleman',
            'state_name' => 'DI Yogyakarta',
            'is_active'  => true,
            'latitude'   => -7.7167,
            'longitude'  => 110.3556,
        ]);

        $this->district2 = District::create([
            'city_id'   => $this->city2->id,
            'name'      => 'Depok',
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'city_id'     => $this->city1->id,
            'district_id' => $this->district1->id,
        ]);

        $this->mitra = User::factory()->create([
            'role'        => 'mitra',
            'status'      => 'active',
            'city_id'     => $this->city2->id,
            'district_id' => $this->district2->id,
        ]);

        $this->otherCustomer = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'city_id'     => $this->city1->id,
            'district_id' => $this->district1->id,
        ]);

        $this->otherMitra = User::factory()->create([
            'role'        => 'mitra',
            'status'      => 'active',
            'city_id'     => $this->city2->id,
            'district_id' => $this->district2->id,
        ]);

        $this->help = Help::create([
            'user_id'        => $this->customer->id,
            'mitra_id'       => $this->mitra->id,
            'title'          => 'Bantuan Reparasi AC',
            'description'    => 'Deskripsi reparasi AC',
            'price'          => 75000,
            'status'         => 'in_progress',
            'address'        => 'Jl Kaliurang KM 5',
            'city_id'        => $this->city1->id,
            'district_id'    => $this->district1->id,
            'latitude'       => -7.7600,
            'longitude'      => 110.3800,
            'payment_status' => 'paid',
        ]);
    }

    /**
     * CH1-01: Customer Support Channel (General Admin Support)
     * - No Help binding (help_id = null)
     * - PartnerReport with report_type = 'dukungan_umum'
     * - PartnerReportMessage created
     * - Resolved to customer's current profile territory
     */
    public function test_customer_support_creates_general_support_without_help_binding(): void
    {
        Auth::login($this->customer);
        Cache::flush();

        $test = Livewire::test(CustomerChatIndex::class)
            ->call('selectAdmin')
            ->assertSet('is_admin_chat', true)
            ->assertSet('admin_tab', 'support')
            ->assertSet('selected_partner_id', 'admin')
            ->assertSet('active_help_id', null)
            ->set('message', 'Halo Admin, saya butuh informasi umum.')
            ->call('sendMessage');

        // Check PartnerReport table
        $report = PartnerReport::where('reporter_id', $this->customer->id)
            ->where('report_type', 'dukungan_umum')
            ->first();

        $this->assertNotNull($report, 'PartnerReport dengan report_type dukungan_umum harus terbentuk.');
        $this->assertNull($report->reported_help_id, 'Report dukungan_umum tidak boleh terikat Help.');
        $this->assertEquals('dukungan_umum', $report->report_type);

        // Check PartnerReportMessage
        $msg = PartnerReportMessage::where('partner_report_id', $report->id)->first();
        $this->assertNotNull($msg);
        $this->assertEquals('Halo Admin, saya butuh informasi umum.', $msg->message);
        $this->assertEquals($this->customer->id, $msg->sender_id);

        // Verify territory resolver
        $resolver = app(PartnerReportTerritoryResolver::class);
        $territory = $resolver->resolve($report);
        $this->assertEquals('profile_reporter', $territory['source']);
        $this->assertEquals($this->city1->id, $territory['city_id']);
        $this->assertEquals($this->district1->id, $territory['district_id']);
    }

    /**
     * CH1-02: Mitra Support Channel (General Admin Support)
     * - No Help binding
     * - PartnerReport with report_type = 'dukungan_umum'
     * - Resolved to mitra's current profile territory
     */
    public function test_mitra_support_creates_general_support_without_help_binding(): void
    {
        Auth::login($this->mitra);
        Cache::flush();

        $test = Livewire::test(MitraChatIndex::class)
            ->call('selectAdmin')
            ->assertSet('is_admin_chat', true)
            ->assertSet('admin_tab', 'support')
            ->assertSet('selected_partner_id', 'admin')
            ->assertSet('active_help_id', null)
            ->set('message', 'Halo Admin Mitra, saya butuh panduan penarikan saldo.')
            ->call('sendMessage');

        $report = PartnerReport::where('reporter_id', $this->mitra->id)
            ->where('report_type', 'dukungan_umum')
            ->first();

        $this->assertNotNull($report);
        $this->assertNull($report->reported_help_id);
        $this->assertEquals('dukungan_umum', $report->report_type);

        $msg = PartnerReportMessage::where('partner_report_id', $report->id)->first();
        $this->assertNotNull($msg);
        $this->assertEquals('Halo Admin Mitra, saya butuh panduan penarikan saldo.', $msg->message);

        // Territory
        $resolver = app(PartnerReportTerritoryResolver::class);
        $territory = $resolver->resolve($report);
        $this->assertEquals('profile_reporter', $territory['source']);
        $this->assertEquals($this->city2->id, $territory['city_id']);
        $this->assertEquals($this->district2->id, $territory['district_id']);
    }

    /**
     * CH1-03: Customer Investigation Channel
     * - Preserves same report ID
     * - Never overwrites report_type to dukungan_umum
     */
    public function test_customer_investigation_preserves_report_identity_and_type(): void
    {
        Auth::login($this->customer);

        $existingReport = PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra->id,
            'reported_help_id' => $this->help->id,
            'report_type'      => 'pelanggaran',
            'title'            => 'Mitra tidak hadir di lokasi',
            'message'          => 'Saya sudah menunggu 1 jam.',
            'status'           => 'investigating',
        ]);

        Livewire::test(CustomerChatIndex::class)
            ->call('selectAdmin')
            ->call('switchAdminTab', 'report')
            ->call('selectAdminReport', $existingReport->id)
            ->assertSet('admin_tab', 'report')
            ->assertSet('selected_report_id', $existingReport->id)
            ->set('message', 'Ini bukti tambahan percakapan saya.')
            ->call('sendMessage');

        $existingReport->refresh();
        $this->assertEquals('pelanggaran', $existingReport->report_type, 'report_type tidak boleh ditimpa menjadi dukungan_umum!');
        $this->assertEquals($this->help->id, $existingReport->reported_help_id);

        $msg = PartnerReportMessage::where('partner_report_id', $existingReport->id)
            ->where('message', 'Ini bukti tambahan percakapan saya.')
            ->first();
        $this->assertNotNull($msg);
    }

    /**
     * CH1-04: Mitra Investigation Channel
     * - Preserves same report ID
     * - Never overwrites report_type to dukungan_umum
     */
    public function test_mitra_investigation_preserves_report_identity_and_type(): void
    {
        Auth::login($this->mitra);

        $existingReport = PartnerReport::create([
            'reporter_id'      => $this->mitra->id,
            'reported_user_id' => $this->customer->id,
            'reported_help_id' => $this->help->id,
            'report_type'      => 'kendala_lapangan',
            'title'            => 'Customer tidak dapat dihubungi',
            'message'          => 'Saya sudah sampai lokasi tapi pagar terkunci.',
            'status'           => 'investigating',
        ]);

        Livewire::test(MitraChatIndex::class)
            ->call('selectAdmin')
            ->call('switchAdminTab', 'report')
            ->call('selectAdminReport', $existingReport->id)
            ->assertSet('admin_tab', 'report')
            ->assertSet('selected_report_id', $existingReport->id)
            ->set('message', 'Saya lampirkan klarifikasi foto pagar.')
            ->call('sendMessage');

        $existingReport->refresh();
        $this->assertEquals('kendala_lapangan', $existingReport->report_type, 'report_type mitra tidak boleh ditimpa!');

        $msg = PartnerReportMessage::where('partner_report_id', $existingReport->id)
            ->where('message', 'Saya lampirkan klarifikasi foto pagar.')
            ->first();
        $this->assertNotNull($msg);
    }

    /**
     * CH1-05: Customer Cancellation Channel
     * - Persisted in HelpCancelMessage only
     * - Requires explicit selected cancellation request
     * - No implicit fallback
     */
    public function test_customer_cancellation_stores_only_in_cancel_messages(): void
    {
        Auth::login($this->customer);

        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $this->help->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'requester_type'  => 'customer',
            'status'          => 'pending',
            'previous_status' => 'in_progress',
            'reason'          => 'Perlu jadwal ulang mendesak.',
        ]);

        Livewire::test(CustomerChatIndex::class)
            ->call('selectAdmin')
            ->call('switchAdminTab', 'cancellation')
            ->call('selectAdminCancel', $cancelReq->id)
            ->assertSet('selected_cancel_request_id', $cancelReq->id)
            ->set('message', 'Mohon admin tinjau pengajuan pembatalan ini.')
            ->call('sendMessage');

        // Check HelpCancelMessage
        $cMsg = HelpCancelMessage::where('help_cancel_request_id', $cancelReq->id)->first();
        $this->assertNotNull($cMsg);
        $this->assertEquals('Mohon admin tinjau pengajuan pembatalan ini.', $cMsg->message);
        $this->assertEquals($this->customer->id, $cMsg->sender_id);
        $this->assertTrue($cMsg->isFromCustomer());

        // Ensure zero partner reports created
        $this->assertEquals(0, PartnerReport::where('reporter_id', $this->customer->id)->count());
    }

    /**
     * CH1-06: Mitra Cancellation Channel
     * - Persisted in HelpCancelMessage only
     * - Requires explicit selected cancellation request
     */
    public function test_mitra_cancellation_stores_only_in_cancel_messages(): void
    {
        Auth::login($this->mitra);

        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $this->help->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'requester_type'  => 'partner',
            'status'          => 'pending',
            'previous_status' => 'in_progress',
            'reason'          => 'Kendaraan mitra mogok.',
        ]);

        Livewire::test(MitraChatIndex::class)
            ->call('selectAdmin')
            ->call('switchAdminTab', 'cancellation')
            ->call('selectAdminCancel', $cancelReq->id)
            ->assertSet('selected_cancel_request_id', $cancelReq->id)
            ->set('message', 'Ban motor saya bocor parah di perjalanan.')
            ->call('sendMessage');

        $cMsg = HelpCancelMessage::where('help_cancel_request_id', $cancelReq->id)->first();
        $this->assertNotNull($cMsg);
        $this->assertEquals('Ban motor saya bocor parah di perjalanan.', $cMsg->message);
        $this->assertEquals($this->mitra->id, $cMsg->sender_id);
        $this->assertTrue($cMsg->isFromMitra());

        $this->assertEquals(0, PartnerReport::where('reporter_id', $this->mitra->id)->count());
    }

    /**
     * CH1-07: Cancellation without explicit selection does not implicitly send
     */
    public function test_cancellation_without_explicit_selection_does_not_send(): void
    {
        Auth::login($this->customer);

        // Create a pending cancel request, but user does NOT select it
        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $this->help->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'requester_type'  => 'customer',
            'status'          => 'pending',
            'previous_status' => 'in_progress',
            'reason'          => 'Alasan pembatalan',
        ]);

        Livewire::test(CustomerChatIndex::class)
            ->call('selectAdmin')
            ->call('switchAdminTab', 'cancellation')
            ->assertSet('selected_cancel_request_id', null)
            ->set('message', 'Pesan tanpa memilih kasus')
            ->call('sendMessage');

        $this->assertEquals(0, HelpCancelMessage::count(), 'Tidak boleh ada pesan terkirim jika kasus pembatalan belum dipilih eksplisit.');
    }

    /**
     * IDOR Test 1: Customer cannot access other user's cancellation request
     */
    public function test_customer_cannot_access_other_user_cancellation(): void
    {
        Auth::login($this->otherCustomer);

        $otherCancel = HelpCancelRequest::create([
            'help_id'         => $this->help->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'requester_type'  => 'customer',
            'status'          => 'pending',
            'previous_status' => 'in_progress',
            'reason'          => 'Kasus user lain',
        ]);

        Livewire::test(CustomerChatIndex::class)
            ->call('selectAdmin')
            ->call('switchAdminTab', 'cancellation')
            ->call('selectAdminCancel', $otherCancel->id)
            ->assertSet('selected_cancel_request_id', null, 'IDOR harus dicegah fail-closed!');
    }

    /**
     * IDOR Test 2: Customer cannot access other user's report
     */
    public function test_customer_cannot_access_other_user_report(): void
    {
        Auth::login($this->otherCustomer);

        $otherReport = PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra->id,
            'reported_help_id' => $this->help->id,
            'report_type'      => 'pelanggaran',
            'title'            => 'Laporan User Lain',
            'message'          => 'Rahasia',
            'status'           => 'investigating',
        ]);

        Livewire::test(CustomerChatIndex::class)
            ->call('selectAdmin')
            ->call('switchAdminTab', 'report')
            ->call('selectAdminReport', $otherReport->id)
            ->assertSet('selected_report_id', null, 'IDOR report harus dicegah fail-closed!');
    }

    /**
     * IDOR Test 3: Being reported_user_id alone does NOT grant access to read or reply to complaint
     */
    public function test_reported_user_alone_cannot_access_complaint_against_them(): void
    {
        // Another customer files complaint against $this->mitra on a different help
        $otherHelp = Help::create([
            'user_id'        => $this->otherCustomer->id,
            'mitra_id'       => $this->otherMitra->id,
            'title'          => 'Bantuan Lain',
            'description'    => 'Test',
            'price'          => 50000,
            'status'         => 'in_progress',
            'address'        => 'Alamat',
            'latitude'       => -7.7600,
            'longitude'      => 110.3800,
            'payment_status' => 'paid',
        ]);

        // Report where reported_user_id is our customer (unrelated to help)
        $complaintReport = PartnerReport::create([
            'reporter_id'      => $this->otherCustomer->id,
            'reported_user_id' => $this->customer->id,
            'reported_help_id' => $otherHelp->id,
            'report_type'      => 'pelanggaran',
            'title'            => 'Aduan terhadap customer',
            'message'          => 'Tindakan merugikan',
            'status'           => 'investigating',
        ]);

        Auth::login($this->customer);

        Livewire::test(CustomerChatIndex::class)
            ->call('selectAdmin')
            ->call('switchAdminTab', 'report')
            ->call('selectAdminReport', $complaintReport->id)
            ->assertSet('selected_report_id', null, 'Pengguna yang dilaporkan tidak boleh membaca/mengakses aduan terhadap dirinya!');
    }

    /**
     * Cross-channel isolation: switching tabs resets active case and routing
     */
    public function test_switching_channels_resets_selected_cases_cleanly(): void
    {
        Auth::login($this->customer);

        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $this->help->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'requester_type'  => 'customer',
            'status'          => 'pending',
            'previous_status' => 'in_progress',
            'reason'          => 'Alasan pembatalan',
        ]);

        Livewire::test(CustomerChatIndex::class)
            ->call('selectAdmin')
            ->assertSet('admin_tab', 'support')
            ->call('switchAdminTab', 'cancellation')
            ->assertSet('admin_tab', 'cancellation')
            ->assertSet('selected_cancel_request_id', null)
            ->call('selectAdminCancel', $cancelReq->id)
            ->assertSet('selected_cancel_request_id', $cancelReq->id)
            ->call('switchAdminTab', 'support')
            ->assertSet('admin_tab', 'support')
            ->assertSet('selected_cancel_request_id', null)
            ->assertSet('selected_report_id', null);
    }

    /**
     * Tim Admin conversation row has NO latest_help auto-binding
     */
    public function test_tim_admin_conversation_row_has_no_latest_help_binding(): void
    {
        Auth::login($this->customer);

        $component = Livewire::test(CustomerChatIndex::class);
        $conversations = $component->viewData('conversations');

        $adminConv = $conversations->firstWhere('is_admin', true);
        $this->assertNotNull($adminConv);
        $this->assertNull($adminConv->latest_help, 'Tim Admin tidak boleh terikat dengan latest_help!');
    }
}
