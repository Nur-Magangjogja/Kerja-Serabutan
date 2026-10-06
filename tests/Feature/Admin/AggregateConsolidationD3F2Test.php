<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Helps\Index as AdminHelpsIndex;
use App\Livewire\Admin\Partners\Activity as PartnerActivityComponent;
use App\Livewire\Admin\Partners\Greylist as GreylistComponent;
use App\Livewire\Admin\Partners\Reports\Index as PartnerReportsIndex;
use App\Livewire\Admin\Support\Index as SupportIndex;
use App\Livewire\SuperAdmin\Dashboard\Index as SuperAdminDashboardIndex;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\PartnerActivity;
use App\Models\PartnerReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AggregateConsolidationD3F2Test extends TestCase
{
    use RefreshDatabase;

    protected City $cityA;
    protected City $cityB;
    protected District $districtA1;
    protected District $districtA2;
    protected District $districtB1;
    protected User $superAdmin;
    protected User $adminA1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cityA = City::create(['name' => 'Kota Yogyakarta', 'province' => 'DIY']);
        $this->districtA1 = District::create(['city_id' => $this->cityA->id, 'name' => 'Danurejan', 'is_active' => true]);
        $this->districtA2 = District::create(['city_id' => $this->cityA->id, 'name' => 'Gondomanan', 'is_active' => true]);

        $this->cityB = City::create(['name' => 'Kota Solo', 'province' => 'Jateng']);
        $this->districtB1 = District::create(['city_id' => $this->cityB->id, 'name' => 'Banjarsari', 'is_active' => true]);

        $this->superAdmin = User::factory()->create([
            'name'        => 'Super Administrator',
            'email'       => 'superadmin@sayabantu.test',
            'role'        => 'super_admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->cityA->id,
            'district_id' => $this->districtA1->id,
            'password'    => Hash::make('password123'),
        ]);

        $this->adminA1 = User::factory()->create([
            'name'        => 'Admin Wilayah Danurejan',
            'email'       => 'admin.danurejan@sayabantu.test',
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->cityA->id,
            'district_id' => $this->districtA1->id,
            'password'    => Hash::make('password123'),
        ]);
        $this->adminA1->managedDistricts()->attach($this->districtA1->id);
    }

    /**
     * 1. SUPERADMIN DASHBOARD CONSOLIDATED AGGREGATES
     */
    public function test_superadmin_dashboard_consolidated_user_and_help_statistics(): void
    {
        // 2 Customers
        User::factory()->count(2)->create([
            'role'        => 'customer',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->districtA1->id,
        ]);

        // 3 Mitras
        User::factory()->count(3)->create([
            'role'        => 'mitra',
            'city_id'     => $this->cityA->id,
            'district_id' => $this->districtA1->id,
        ]);

        $customer = User::where('role', 'customer')->first();
        $mitra = User::where('role', 'mitra')->first();

        // Helps across various statuses
        Help::create([
            'user_id'      => $customer->id,
            'city_id'      => $this->cityA->id,
            'district_id'  => $this->districtA1->id,
            'title'        => 'Help Menunggu',
            'description'  => 'Deskripsi Help Menunggu',
            'status'       => Help::STATUS_MENUNGGU_MITRA,
            'total_amount' => 50000,
        ]);

        Help::create([
            'user_id'      => $customer->id,
            'mitra_id'     => $mitra->id,
            'city_id'      => $this->cityA->id,
            'district_id'  => $this->districtA1->id,
            'title'        => 'Help In Progress',
            'description'  => 'Deskripsi Help In Progress',
            'status'       => Help::STATUS_IN_PROGRESS,
            'total_amount' => 75000,
        ]);

        Help::create([
            'user_id'      => $customer->id,
            'mitra_id'     => $mitra->id,
            'city_id'      => $this->cityA->id,
            'district_id'  => $this->districtA1->id,
            'title'        => 'Help Waiting Confirmation',
            'description'  => 'Deskripsi Help Waiting Confirmation',
            'status'       => Help::STATUS_WAITING_CONFIRMATION,
            'total_amount' => 100000,
        ]);

        Help::create([
            'user_id'      => $customer->id,
            'mitra_id'     => $mitra->id,
            'city_id'      => $this->cityA->id,
            'district_id'  => $this->districtA1->id,
            'title'        => 'Help Selesai',
            'description'  => 'Deskripsi Help Selesai',
            'status'       => Help::STATUS_SELESAI,
            'total_amount' => 120000,
        ]);

        // Total users: 1 superadmin + 1 admin + 2 customers + 3 mitras = 7
        Livewire::actingAs($this->superAdmin)
            ->test(SuperAdminDashboardIndex::class)
            ->assertViewHas('stats', function ($stats) {
                return $stats['total_users'] === 7
                    && $stats['total_customers'] === 2
                    && $stats['total_mitras'] === 3
                    && $stats['total_admins'] === 2 // superadmin + admin
                    && $stats['pending_helps'] === 1
                    && $stats['active_helps'] === 2 // in_progress + waiting_confirmation
                    && $stats['completed_helps'] === 1;
            });
    }

    /**
     * 2. ADMIN HELPS CONSOLIDATED AGGREGATES & TERRITORY ISOLATION
     */
    public function test_admin_helps_consolidated_aggregates_with_territory_isolation(): void
    {
        $customerA1 = User::factory()->create(['role' => 'customer', 'city_id' => $this->cityA->id, 'district_id' => $this->districtA1->id]);
        $mitraA1 = User::factory()->create(['role' => 'mitra', 'city_id' => $this->cityA->id, 'district_id' => $this->districtA1->id]);

        $customerB1 = User::factory()->create(['role' => 'customer', 'city_id' => $this->cityB->id, 'district_id' => $this->districtB1->id]);
        $mitraB1 = User::factory()->create(['role' => 'mitra', 'city_id' => $this->cityB->id, 'district_id' => $this->districtB1->id]);

        // In-scope Helps (District A1)
        Help::create(['user_id' => $customerA1->id, 'city_id' => $this->cityA->id, 'district_id' => $this->districtA1->id, 'title' => 'Help A1 Pending', 'description' => 'Desc A1 Pending', 'status' => Help::STATUS_MENUNGGU_MITRA, 'total_amount' => 50000]);
        Help::create(['user_id' => $customerA1->id, 'mitra_id' => $mitraA1->id, 'city_id' => $this->cityA->id, 'district_id' => $this->districtA1->id, 'title' => 'Help A1 Active', 'description' => 'Desc A1 Active', 'status' => Help::STATUS_PARTNER_ON_THE_WAY, 'total_amount' => 60000]);
        Help::create(['user_id' => $customerA1->id, 'mitra_id' => $mitraA1->id, 'city_id' => $this->cityA->id, 'district_id' => $this->districtA1->id, 'title' => 'Help A1 Selesai', 'description' => 'Desc A1 Selesai', 'status' => Help::STATUS_SELESAI, 'total_amount' => 70000]);
        Help::create(['user_id' => $customerA1->id, 'city_id' => $this->cityA->id, 'district_id' => $this->districtA1->id, 'title' => 'Help A1 Dibatalkan', 'description' => 'Desc A1 Dibatalkan', 'status' => Help::STATUS_DIBATALKAN, 'total_amount' => 80000]);

        // Foreign territory Helps (District B1) - should NOT be counted by Admin A1
        Help::create(['user_id' => $customerB1->id, 'city_id' => $this->cityB->id, 'district_id' => $this->districtB1->id, 'title' => 'Help B1 Pending', 'description' => 'Desc B1 Pending', 'status' => Help::STATUS_MENUNGGU_MITRA, 'total_amount' => 50000]);
        Help::create(['user_id' => $customerB1->id, 'mitra_id' => $mitraB1->id, 'city_id' => $this->cityB->id, 'district_id' => $this->districtB1->id, 'title' => 'Help B1 Selesai', 'description' => 'Desc B1 Selesai', 'status' => Help::STATUS_SELESAI, 'total_amount' => 90000]);

        Livewire::actingAs($this->adminA1)
            ->test(AdminHelpsIndex::class)
            ->assertViewHas('totalHelps', 4)
            ->assertViewHas('pendingHelps', 1)
            ->assertViewHas('activeHelps', 1)
            ->assertViewHas('completedHelps', 1)
            ->assertViewHas('cancelledHelps', 1);
    }

    /**
     * 3. PARTNER REPORTS CONSOLIDATED AGGREGATES
     */
    public function test_partner_reports_consolidated_aggregates_and_support_exclusion(): void
    {
        $customerA1 = User::factory()->create(['role' => 'customer', 'city_id' => $this->cityA->id, 'district_id' => $this->districtA1->id]);
        $mitraA1 = User::factory()->create(['role' => 'mitra', 'city_id' => $this->cityA->id, 'district_id' => $this->districtA1->id]);

        $helpA1 = Help::create(['user_id' => $customerA1->id, 'mitra_id' => $mitraA1->id, 'city_id' => $this->cityA->id, 'district_id' => $this->districtA1->id, 'title' => 'Order Report Test', 'description' => 'Desc Order Report Test', 'status' => Help::STATUS_SELESAI, 'total_amount' => 50000]);

        // Investigation Reports (Help-linked)
        PartnerReport::create([
            'reporter_id'      => $customerA1->id,
            'reported_user_id' => $mitraA1->id,
            'help_id'          => $helpA1->id,
            'report_type'      => 'customer_to_partner',
            'status'           => 'pending',
            'refund_status'    => 'requested',
            'title'            => 'Laporan Investigasi Pending',
            'message'          => 'Pesan laporan investigasi pending',
            'description'      => 'Mitra tidak sesuai SOP',
        ]);

        PartnerReport::create([
            'reporter_id'      => $mitraA1->id,
            'reported_user_id' => $customerA1->id,
            'help_id'          => $helpA1->id,
            'report_type'      => 'partner_to_customer',
            'status'           => 'in_progress',
            'refund_status'    => 'none',
            'title'            => 'Laporan Investigasi In Progress',
            'message'          => 'Pesan laporan investigasi in progress',
            'description'      => 'Customer tidak di lokasi',
        ]);

        PartnerReport::create([
            'reporter_id'      => $customerA1->id,
            'reported_user_id' => $mitraA1->id,
            'help_id'          => $helpA1->id,
            'report_type'      => 'customer_to_partner',
            'status'           => 'resolved',
            'refund_status'    => 'approved',
            'title'            => 'Laporan Investigasi Resolved',
            'message'          => 'Pesan laporan investigasi resolved',
            'description'      => 'Selesai dimediasi',
        ]);

        // General Support (dukungan_umum) - MUST BE EXCLUDED from Partner Reports
        PartnerReport::create([
            'reporter_id' => $customerA1->id,
            'report_type' => 'dukungan_umum',
            'category'    => 'dari_customer',
            'status'      => 'pending',
            'title'       => 'Tiket Bantuan Support',
            'message'     => 'Pesan bantuan support',
            'description' => 'Pertanyaan umum aplikasi',
        ]);

        Livewire::actingAs($this->adminA1)
            ->test(PartnerReportsIndex::class)
            ->assertViewHas('totalPending', 1)
            ->assertViewHas('totalInProgress', 1)
            ->assertViewHas('totalResolved', 1)
            ->assertViewHas('totalRefundRequested', 1)
            ->assertViewHas('totalFromCustomer', 2)
            ->assertViewHas('totalFromMitra', 1);
    }

    /**
     * 4. GENERAL SUPPORT CONSOLIDATED AGGREGATES
     */
    public function test_general_support_consolidated_aggregates_and_profile_territory(): void
    {
        $customerA1 = User::factory()->create(['role' => 'customer', 'city_id' => $this->cityA->id, 'district_id' => $this->districtA1->id]);
        $mitraA1 = User::factory()->create(['role' => 'mitra', 'city_id' => $this->cityA->id, 'district_id' => $this->districtA1->id]);

        $customerB1 = User::factory()->create(['role' => 'customer', 'city_id' => $this->cityB->id, 'district_id' => $this->districtB1->id]);

        // Support tickets in District A1
        PartnerReport::create([
            'reporter_id' => $customerA1->id,
            'report_type' => 'dukungan_umum',
            'category'    => 'dari_customer',
            'status'      => 'pending',
            'title'       => 'Bantuan Customer Pending',
            'message'     => 'Pesan bantuan customer pending',
            'description' => 'Cara topup',
        ]);

        PartnerReport::create([
            'reporter_id' => $mitraA1->id,
            'report_type' => 'dukungan_umum',
            'category'    => 'dari_mitra',
            'status'      => 'in_progress',
            'title'       => 'Bantuan Mitra In Progress',
            'message'     => 'Pesan bantuan mitra in progress',
            'description' => 'Update rekening',
        ]);

        PartnerReport::create([
            'reporter_id' => $customerA1->id,
            'report_type' => 'dukungan_umum',
            'category'    => 'dari_customer',
            'status'      => 'resolved',
            'title'       => 'Bantuan Customer Resolved',
            'message'     => 'Pesan bantuan customer resolved',
            'description' => 'Selesai dijawab',
        ]);

        // Foreign territory support ticket (District B1)
        PartnerReport::create([
            'reporter_id' => $customerB1->id,
            'report_type' => 'dukungan_umum',
            'category'    => 'dari_customer',
            'status'      => 'pending',
            'title'       => 'Bantuan B1',
            'message'     => 'Pesan bantuan B1',
            'description' => 'Pertanyaan di Solo',
        ]);

        // Investigation report (customer_to_partner) - MUST BE EXCLUDED from General Support
        PartnerReport::create([
            'reporter_id' => $customerA1->id,
            'report_type' => 'customer_to_partner',
            'status'      => 'pending',
            'title'       => 'Investigasi',
            'message'     => 'Pesan investigasi',
            'description' => 'Komplain layanan',
        ]);

        Livewire::actingAs($this->adminA1)
            ->test(SupportIndex::class)
            ->assertViewHas('totalPending', 1)
            ->assertViewHas('totalInProgress', 1)
            ->assertViewHas('totalResolved', 1)
            ->assertViewHas('totalFromCustomer', 2)
            ->assertViewHas('totalFromMitra', 1);
    }

    /**
     * 5. GREYLIST CONSOLIDATED AGGREGATES (OVERLAPPING STATES)
     */
    public function test_greylist_consolidated_aggregates_with_overlapping_states(): void
    {
        // User 1: Mitra, greylisted, shadow_banned, SP 3 (Overlapping)
        User::factory()->create([
            'role'             => 'mitra',
            'city_id'          => $this->cityA->id,
            'district_id'      => $this->districtA1->id,
            'is_greylisted'    => true,
            'is_shadow_banned' => true,
            'warning_level'    => 3,
        ]);

        // User 2: Customer, warning_only (SP 1, not shadow banned, greylisted)
        User::factory()->create([
            'role'             => 'customer',
            'city_id'          => $this->cityA->id,
            'district_id'      => $this->districtA1->id,
            'is_greylisted'    => true,
            'is_shadow_banned' => false,
            'warning_level'    => 1,
        ]);

        // User 3: Mitra, warning_level = 0, is_greylisted = true, is_shadow_banned = false
        User::factory()->create([
            'role'             => 'mitra',
            'city_id'          => $this->cityA->id,
            'district_id'      => $this->districtA1->id,
            'is_greylisted'    => true,
            'is_shadow_banned' => false,
            'warning_level'    => 0,
        ]);

        // User 4: Clean user (should not appear in greylist query)
        User::factory()->create([
            'role'             => 'mitra',
            'city_id'          => $this->cityA->id,
            'district_id'      => $this->districtA1->id,
            'is_greylisted'    => false,
            'is_shadow_banned' => false,
            'warning_level'    => 0,
        ]);

        Livewire::actingAs($this->adminA1)
            ->test(GreylistComponent::class)
            ->assertViewHas('totalGreylist', 3)
            ->assertViewHas('totalShadowBanned', 1)
            ->assertViewHas('totalWarning', 2)
            ->assertViewHas('totalMitra', 2)
            ->assertViewHas('totalCustomer', 1);
    }

    /**
     * 6. PARTNER ACTIVITY CONSOLIDATED AGGREGATES
     */
    public function test_partner_activity_consolidated_statistics(): void
    {
        $customerA1 = User::factory()->create(['role' => 'customer', 'city_id' => $this->cityA->id, 'district_id' => $this->districtA1->id]);
        $mitraA1 = User::factory()->create(['role' => 'mitra', 'city_id' => $this->cityA->id, 'district_id' => $this->districtA1->id]);

        $helpA1 = Help::create(['user_id' => $customerA1->id, 'mitra_id' => $mitraA1->id, 'city_id' => $this->cityA->id, 'district_id' => $this->districtA1->id, 'title' => 'Activity Test Help', 'description' => 'Desc Activity Test Help', 'status' => Help::STATUS_SELESAI, 'total_amount' => 50000]);

        // Today's activity - Customer
        PartnerActivity::create([
            'user_id'       => $customerA1->id,
            'help_id'       => $helpA1->id,
            'activity_type' => 'help_created',
            'description'   => 'Customer membuat pesanan',
            'created_at'    => now(),
        ]);

        // Today's activity - Mitra completed job
        PartnerActivity::create([
            'user_id'       => $mitraA1->id,
            'help_id'       => $helpA1->id,
            'activity_type' => 'help_completed',
            'description'   => 'Mitra menyelesaikan bantuan',
            'created_at'    => now(),
        ]);

        // Yesterday's activity - Mitra
        $yesterdayAct = PartnerActivity::create([
            'user_id'       => $mitraA1->id,
            'help_id'       => $helpA1->id,
            'activity_type' => 'take_help',
            'description'   => 'Mitra mengambil bantuan',
        ]);
        $yesterdayAct->created_at = now()->subDays(2);
        $yesterdayAct->save();

        Livewire::actingAs($this->adminA1)
            ->test(PartnerActivityComponent::class)
            ->assertViewHas('stats', function ($stats) {
                return $stats['total'] === 4
                    && $stats['today'] === 3
                    && $stats['customer_acts'] === 2
                    && $stats['mitra_acts'] === 2
                    && $stats['completed_jobs'] === 1;
            });
    }

    /**
     * 7. QUERY COUNT PROOFS (D3F2)
     */
    public function test_query_count_measurements_for_all_consolidated_components(): void
    {
        // Setup minimal baseline data
        $customer = User::factory()->create(['role' => 'customer', 'city_id' => $this->cityA->id, 'district_id' => $this->districtA1->id]);
        $mitra = User::factory()->create(['role' => 'mitra', 'city_id' => $this->cityA->id, 'district_id' => $this->districtA1->id]);

        // 1. Admin Helps: verify only 1 stats aggregate query is run instead of 5
        DB::flushQueryLog();
        DB::enableQueryLog();
        Livewire::actingAs($this->adminA1)->test(AdminHelpsIndex::class);
        $helpsQueries = DB::getQueryLog();
        DB::disableQueryLog();

        $helpStatsQueries = array_filter($helpsQueries, fn($q) => str_contains($q['query'], 'total_helps'));
        $this->assertCount(1, $helpStatsQueries, 'AdminHelps should execute exactly 1 consolidated stats query.');

        // 2. Partner Reports: verify only 1 stats aggregate query is run instead of 6
        DB::flushQueryLog();
        DB::enableQueryLog();
        Livewire::actingAs($this->adminA1)->test(PartnerReportsIndex::class);
        $reportQueries = DB::getQueryLog();
        DB::disableQueryLog();

        $reportStatsQueries = array_filter($reportQueries, fn($q) => str_contains($q['query'], 'total_pending'));
        $this->assertCount(1, $reportStatsQueries, 'PartnerReports should execute exactly 1 consolidated stats query.');

        // 3. General Support: verify only 1 stats aggregate query is run instead of 5
        DB::flushQueryLog();
        DB::enableQueryLog();
        Livewire::actingAs($this->adminA1)->test(SupportIndex::class);
        $supportQueries = DB::getQueryLog();
        DB::disableQueryLog();

        $supportStatsQueries = array_filter($supportQueries, fn($q) => str_contains($q['query'], 'total_pending'));
        $this->assertCount(1, $supportStatsQueries, 'GeneralSupport should execute exactly 1 consolidated stats query.');

        // 4. Greylist: verify only 1 stats aggregate query is run instead of 5
        DB::flushQueryLog();
        DB::enableQueryLog();
        Livewire::actingAs($this->adminA1)->test(GreylistComponent::class);
        $greylistQueries = DB::getQueryLog();
        DB::disableQueryLog();

        $greylistStatsQueries = array_filter($greylistQueries, fn($q) => str_contains($q['query'], 'total_greylist'));
        $this->assertCount(1, $greylistStatsQueries, 'Greylist should execute exactly 1 consolidated stats query.');

        // 5. Partner Activity: verify only 1 stats aggregate query is run instead of 5
        DB::flushQueryLog();
        DB::enableQueryLog();
        Livewire::actingAs($this->adminA1)->test(PartnerActivityComponent::class);
        $activityQueries = DB::getQueryLog();
        DB::disableQueryLog();

        $activityStatsQueries = array_filter($activityQueries, fn($q) => str_contains($q['query'], 'customer_count'));
        $this->assertCount(1, $activityStatsQueries, 'PartnerActivity should execute exactly 1 consolidated stats query.');
    }
}
