<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard\Index as AdminDashboard;
use App\Livewire\Admin\Disputes\Index as AdminDisputes;
use App\Livewire\Admin\Helps\Index as AdminHelps;
use App\Livewire\Admin\Partners\Reports\Index as AdminReports;
use App\Livewire\Admin\Support\Index as AdminSupport;
use App\Livewire\Admin\Topup\Approval as AdminTopupApproval;
use App\Livewire\Admin\Verifications\Index as AdminVerifications;
use App\Livewire\Admin\Withdraws\Index as AdminWithdraws;
use App\Livewire\SuperAdmin\Users\Index as AdminUsersIndex;
use App\Models\BalanceTransaction;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\PartnerReport;
use App\Models\Registration;
use App\Models\User;
use App\Models\WithdrawRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminZeroTerritoryVisibilityAW4Test extends TestCase
{
    use RefreshDatabase;

    protected City $citySleman;
    protected City $cityYogya;

    protected District $distNgaglik;
    protected District $distDepok;
    protected District $distGondomanan;

    protected User $customerNgaglik;
    protected User $customerDepok;
    protected User $customerGondomanan;

    protected User $mitraNgaglik;
    protected User $mitraGondomanan;

    protected Registration $regNgaglik;
    protected Registration $regGondomanan;

    protected BalanceTransaction $topupNgaglik;
    protected BalanceTransaction $topupGondomanan;

    protected WithdrawRequest $withdrawNgaglik;
    protected WithdrawRequest $withdrawGondomanan;

    protected Help $helpNgaglik;
    protected Help $helpGondomanan;

    protected PartnerReport $reportNgaglik;
    protected PartnerReport $reportGondomanan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->citySleman = City::create([
            'name'     => 'Kabupaten Sleman',
            'province' => 'D.I. Yogyakarta',
        ]);

        $this->cityYogya = City::create([
            'name'     => 'Kota Yogyakarta',
            'province' => 'D.I. Yogyakarta',
        ]);

        $this->distNgaglik = District::create([
            'city_id'   => $this->citySleman->id,
            'name'      => 'Ngaglik',
            'is_active' => true,
        ]);

        $this->distDepok = District::create([
            'city_id'   => $this->citySleman->id,
            'name'      => 'Depok',
            'is_active' => true,
        ]);

        $this->distGondomanan = District::create([
            'city_id'   => $this->cityYogya->id,
            'name'      => 'Gondomanan',
            'is_active' => true,
        ]);

        // Customers
        $this->customerNgaglik = User::factory()->create([
            'name'        => 'Customer Ngaglik',
            'email'       => 'cust.ngaglik@test.com',
            'role'        => 'customer',
            'city_id'     => $this->citySleman->id,
            'district_id' => $this->distNgaglik->id,
            'verified'    => true,
            'status'      => 'active',
        ]);

        $this->customerDepok = User::factory()->create([
            'name'        => 'Customer Depok',
            'email'       => 'cust.depok@test.com',
            'role'        => 'customer',
            'city_id'     => $this->citySleman->id,
            'district_id' => $this->distDepok->id,
            'verified'    => true,
            'status'      => 'active',
        ]);

        $this->customerGondomanan = User::factory()->create([
            'name'        => 'Customer Gondomanan',
            'email'       => 'cust.gondomanan@test.com',
            'role'        => 'customer',
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->distGondomanan->id,
            'verified'    => true,
            'status'      => 'active',
        ]);

        // Mitras
        $this->mitraNgaglik = User::factory()->create([
            'name'                       => 'Mitra Ngaglik',
            'email'                      => 'mitra.ngaglik@test.com',
            'role'                       => 'mitra',
            'city_id'                    => $this->citySleman->id,
            'district_id'                => $this->distNgaglik->id,
            'verified'                   => true,
            'status'                     => 'active',
            'vehicle_verification_status'=> 'pending',
            'vehicle_plate_number'       => 'AB 1234 NG',
        ]);

        $this->mitraGondomanan = User::factory()->create([
            'name'                       => 'Mitra Gondomanan',
            'email'                      => 'mitra.gondomanan@test.com',
            'role'                       => 'mitra',
            'city_id'                    => $this->cityYogya->id,
            'district_id'                => $this->distGondomanan->id,
            'verified'                   => true,
            'status'                     => 'active',
            'vehicle_verification_status'=> 'pending',
            'vehicle_plate_number'       => 'AB 5678 GD',
        ]);

        // KTP Registrations
        $this->regNgaglik = Registration::create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'name'        => 'Reg Ngaglik',
            'full_name'   => 'Reg Ngaglik',
            'email'       => 'reg.ngaglik@test.com',
            'phone'       => '081234567891',
            'nik'         => '3404011234560001',
            'city_id'     => $this->citySleman->id,
            'district_id' => $this->distNgaglik->id,
            'status'      => 'pending_verification',
            'role'        => 'customer',
        ]);

        $this->regGondomanan = Registration::create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'name'        => 'Reg Gondomanan',
            'full_name'   => 'Reg Gondomanan',
            'email'       => 'reg.gondomanan@test.com',
            'phone'       => '081234567892',
            'nik'         => '3471011234560002',
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->distGondomanan->id,
            'status'      => 'pending_verification',
            'role'        => 'customer',
        ]);

        // Topup Requests
        $this->topupNgaglik = BalanceTransaction::create([
            'user_id'       => $this->customerNgaglik->id,
            'type'          => 'topup',
            'amount'        => 50000,
            'total_payment' => 50000,
            'status'        => 'waiting_approval',
        ]);

        $this->topupGondomanan = BalanceTransaction::create([
            'user_id'       => $this->customerGondomanan->id,
            'type'          => 'topup',
            'amount'        => 100000,
            'total_payment' => 100000,
            'status'        => 'waiting_approval',
        ]);

        // Withdraw Requests
        $this->withdrawNgaglik = WithdrawRequest::create([
            'user_id'        => $this->mitraNgaglik->id,
            'amount'         => 75000,
            'bank_code'      => 'BCA',
            'account_number' => '1234567890',
            'account_name'   => 'Mitra Ngaglik',
            'status'         => 'pending',
        ]);

        $this->withdrawGondomanan = WithdrawRequest::create([
            'user_id'        => $this->mitraGondomanan->id,
            'amount'         => 120000,
            'bank_code'      => 'BRI',
            'account_number' => '0987654321',
            'account_name'   => 'Mitra Gondomanan',
            'status'         => 'pending',
        ]);

        // Helps
        $this->helpNgaglik = Help::create([
            'user_id'       => $this->customerNgaglik->id,
            'title'         => 'Bantuan Ngaglik',
            'description'   => 'Deskripsi bantuan di Ngaglik',
            'price'         => 30000,
            'status'        => Help::STATUS_MENUNGGU_MITRA,
            'city_id'       => $this->citySleman->id,
            'district_id'   => $this->distNgaglik->id,
            'order_id'      => 'HLP-NGAGLIK-01',
        ]);

        $this->helpGondomanan = Help::create([
            'user_id'       => $this->customerGondomanan->id,
            'title'         => 'Bantuan Gondomanan',
            'description'   => 'Deskripsi bantuan di Gondomanan',
            'price'         => 45000,
            'status'        => Help::STATUS_MENUNGGU_MITRA,
            'city_id'       => $this->cityYogya->id,
            'district_id'   => $this->distGondomanan->id,
            'order_id'      => 'HLP-GONDOMANAN-01',
        ]);

        // Partner Reports
        $this->reportNgaglik = PartnerReport::create([
            'reporter_id'      => $this->customerNgaglik->id,
            'reported_user_id' => $this->mitraNgaglik->id,
            'reported_help_id' => $this->helpNgaglik->id,
            'title'            => 'Laporan Masalah Ngaglik',
            'message'          => 'Keluhan bantuan Ngaglik',
            'status'           => 'pending',
            'report_type'      => 'customer_to_partner',
        ]);

        $this->reportGondomanan = PartnerReport::create([
            'reporter_id'      => $this->customerGondomanan->id,
            'reported_user_id' => $this->mitraGondomanan->id,
            'reported_help_id' => $this->helpGondomanan->id,
            'title'            => 'Laporan Masalah Gondomanan',
            'message'          => 'Keluhan bantuan Gondomanan',
            'status'           => 'pending',
            'report_type'      => 'customer_to_partner',
        ]);
    }

    protected function createZeroTerritoryAdmin(): User
    {
        return User::factory()->create([
            'name'        => 'Zero Territory Admin',
            'email'       => 'admin.zero.' . uniqid() . '@sayabantu.test',
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->citySleman->id,
            'district_id' => null,
            'password'    => Hash::make('password123'),
        ]);
    }

    protected function createAdminWithDistricts(array $districtIds): User
    {
        $admin = User::factory()->create([
            'name'        => 'Assigned Admin Wilayah',
            'email'       => 'admin.assigned.' . uniqid() . '@sayabantu.test',
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->citySleman->id,
            'district_id' => null,
            'password'    => Hash::make('password123'),
        ]);

        $admin->managedDistricts()->sync($districtIds);
        return $admin;
    }

    /**
     * AW4-1: Customer listing empty for zero-territory admin.
     */
    public function test_aw4_1_customer_list_empty_for_zero_territory_admin(): void
    {
        $admin = $this->createZeroTerritoryAdmin();

        $component = Livewire::actingAs($admin)
            ->test(AdminUsersIndex::class)
            ->set('roleFilter', 'customer');

        $users = $component->viewData('users');
        $this->assertCount(0, $users);
        $component->assertDontSee($this->customerNgaglik->name)
                  ->assertDontSee($this->customerGondomanan->name);
    }

    /**
     * AW4-2: Mitra listing empty for zero-territory admin.
     */
    public function test_aw4_2_mitra_list_empty_for_zero_territory_admin(): void
    {
        $admin = $this->createZeroTerritoryAdmin();

        $component = Livewire::actingAs($admin)
            ->test(AdminUsersIndex::class)
            ->set('roleFilter', 'mitra');

        $users = $component->viewData('users');
        $this->assertCount(0, $users);
        $component->assertDontSee($this->mitraNgaglik->name)
                  ->assertDontSee($this->mitraGondomanan->name);
    }

    /**
     * AW4-3: KTP verification list & pending count empty for zero-territory admin.
     */
    public function test_aw4_3_ktp_verification_empty_for_zero_territory_admin(): void
    {
        $admin = $this->createZeroTerritoryAdmin();

        $component = Livewire::actingAs($admin)
            ->test(AdminVerifications::class);

        $this->assertEquals(0, $component->viewData('pendingKtpCount'));
        $verifications = $component->viewData('verifications');
        $this->assertCount(0, $verifications);
        $component->assertDontSee($this->regNgaglik->full_name)
                  ->assertDontSee($this->regGondomanan->full_name);
    }

    /**
     * AW4-4: Vehicle verification list & pending count empty for zero-territory admin.
     */
    public function test_aw4_4_vehicle_verification_empty_for_zero_territory_admin(): void
    {
        $admin = $this->createZeroTerritoryAdmin();

        $component = Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->set('activeTab', 'vehicle');

        $this->assertEquals(0, $component->viewData('pendingVehicleCount'));
        $vehicleVerifications = $component->viewData('vehicleVerifications');
        $this->assertCount(0, $vehicleVerifications);
        $component->assertDontSee($this->mitraNgaglik->vehicle_plate_number)
                  ->assertDontSee($this->mitraGondomanan->vehicle_plate_number);
    }

    /**
     * AW4-5: Topup approval list & status counts empty for zero-territory admin.
     */
    public function test_aw4_5_topup_list_empty_for_zero_territory_admin(): void
    {
        $admin = $this->createZeroTerritoryAdmin();

        $component = Livewire::actingAs($admin)
            ->test(AdminTopupApproval::class);

        $this->assertEquals(0, $component->viewData('totalPending'));
        $this->assertEquals(0, $component->viewData('totalAll'));
        $transactions = $component->viewData('transactions');
        $this->assertCount(0, $transactions);
        $component->assertDontSee('50.000')
                  ->assertDontSee('100.000');
    }

    /**
     * AW4-6: Withdraw list empty for zero-territory admin.
     */
    public function test_aw4_6_withdraw_list_empty_for_zero_territory_admin(): void
    {
        $admin = $this->createZeroTerritoryAdmin();

        $component = Livewire::actingAs($admin)
            ->test(AdminWithdraws::class);

        $withdraws = $component->viewData('withdraws');
        $this->assertCount(0, $withdraws);
        $component->assertDontSee('75.000')
                  ->assertDontSee('120.000');
    }

    /**
     * AW4-7: Helps list & aggregate statistics empty for zero-territory admin.
     */
    public function test_aw4_7_helps_list_and_statistics_empty_for_zero_territory_admin(): void
    {
        $admin = $this->createZeroTerritoryAdmin();

        $component = Livewire::actingAs($admin)
            ->test(AdminHelps::class);

        $helps = $component->viewData('helps');
        $this->assertCount(0, $helps);
        $this->assertEquals(0, $component->viewData('totalHelps'));
        $component->assertDontSee($this->helpNgaglik->title)
                  ->assertDontSee($this->helpGondomanan->title);
    }

    /**
     * AW4-8: Reports, Support, and Disputes visibility empty for zero-territory admin.
     */
    public function test_aw4_8_reports_support_disputes_empty_for_zero_territory_admin(): void
    {
        $admin = $this->createZeroTerritoryAdmin();

        // 1. Partner Reports
        $repComp = Livewire::actingAs($admin)->test(AdminReports::class);
        $this->assertEquals(0, $repComp->viewData('totalPending'));
        $reports = $repComp->viewData('reports');
        $this->assertCount(0, $reports);
        $repComp->assertDontSee($this->reportNgaglik->title)
                ->assertDontSee($this->reportGondomanan->title);

        // 2. Support Reports
        $suppComp = Livewire::actingAs($admin)->test(AdminSupport::class);
        $this->assertEquals(0, $suppComp->viewData('totalPending'));
        $suppReports = $suppComp->viewData('reports');
        $this->assertCount(0, $suppReports);

        // 3. Disputes & Cancellations
        $dispComp = Livewire::actingAs($admin)->test(AdminDisputes::class);
        $this->assertEquals(0, $dispComp->viewData('pendingCancelsCount'));
        $this->assertEquals(0, $dispComp->viewData('activeFrozenDisputesCount'));
    }

    /**
     * AW4-9: Dashboard territorial counts are all zero for zero-territory admin.
     */
    public function test_aw4_9_dashboard_territorial_counts_zero_for_zero_territory_admin(): void
    {
        $admin = $this->createZeroTerritoryAdmin();

        $component = Livewire::actingAs($admin)->test(AdminDashboard::class);

        $this->assertEquals(0, $component->viewData('totalHelps'));
        $this->assertEquals(0, $component->viewData('pendingHelps'));
        $this->assertEquals(0, $component->viewData('activeHelps'));
        $this->assertEquals(0, $component->viewData('completedHelps'));
        $this->assertEquals(0, $component->viewData('cancelledHelps'));
        $this->assertEquals(0, $component->viewData('pendingVerifications'));
        $this->assertEquals(0, $component->viewData('totalAllMitras'));
        $this->assertEquals(0, $component->viewData('pendingTopups'));
        $this->assertEquals(0, $component->viewData('pendingWithdraws'));
        $this->assertCount(0, $component->viewData('latestHelps'));
    }

    /**
     * AW4-10: Representative direct access remains denied by existing security guards.
     */
    public function test_aw4_10_representative_direct_access_remains_denied(): void
    {
        $admin = $this->createZeroTerritoryAdmin();

        // 1. Direct KTP view attempt
        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('viewKtp', $this->regNgaglik->id)
            ->assertSee('Data tidak ditemukan atau berada di luar wilayah wewenang Anda.');

        // 2. Direct Topup view attempt
        Livewire::actingAs($admin)
            ->test(AdminTopupApproval::class)
            ->call('viewDetail', $this->topupNgaglik->id)
            ->assertSee('Transaksi tidak ditemukan atau berada di luar wilayah wewenang Anda.');

        // 3. Direct Withdraw review attempt
        Livewire::actingAs($admin)
            ->test(AdminWithdraws::class)
            ->call('openReviewModal', $this->withdrawNgaglik->id)
            ->assertSee('Anda tidak memiliki wewenang untuk memproses penarikan dana dari luar wilayah wewenang Anda.');
    }

    /**
     * AW4-11: Multi-district Admin sees only assigned territory.
     */
    public function test_aw4_11_multidistrict_admin_sees_only_assigned_territory(): void
    {
        // Admin assigned strictly to Ngaglik
        $admin = $this->createAdminWithDistricts([$this->distNgaglik->id]);

        // Customer listing
        $userComp = Livewire::actingAs($admin)
            ->test(AdminUsersIndex::class)
            ->set('roleFilter', 'customer');
        $users = $userComp->viewData('users');
        $this->assertCount(1, $users);
        $userComp->assertSee($this->customerNgaglik->name)
                 ->assertDontSee($this->customerGondomanan->name);

        // KTP listing
        $ktpComp = Livewire::actingAs($admin)->test(AdminVerifications::class);
        $this->assertEquals(1, $ktpComp->viewData('pendingKtpCount'));
        $ktpComp->assertSee($this->regNgaglik->full_name)
                ->assertDontSee($this->regGondomanan->full_name);

        // Topup listing
        $topupComp = Livewire::actingAs($admin)->test(AdminTopupApproval::class);
        $this->assertEquals(1, $topupComp->viewData('totalPending'));
        $topupComp->assertSee('50.000')
                  ->assertDontSee('100.000');

        // Withdraw listing
        $withdrawComp = Livewire::actingAs($admin)->test(AdminWithdraws::class);
        $withdraws = $withdrawComp->viewData('withdraws');
        $this->assertCount(1, $withdraws);
        $withdrawComp->assertSee('75.000')
                     ->assertDontSee('120.000');
    }

    /**
     * AW4-12: Active switch filter remains bounded by explicit assignment.
     */
    public function test_aw4_12_active_switch_filter_remains_bounded_by_assignment(): void
    {
        // Admin assigned to both Ngaglik and Depok
        $admin = $this->createAdminWithDistricts([$this->distNgaglik->id, $this->distDepok->id]);

        // 1. Filter active on Ngaglik
        $admin->setActiveAdminDistrictFilter((string) $this->distNgaglik->id);
        $userComp = Livewire::actingAs($admin)
            ->test(AdminUsersIndex::class)
            ->set('roleFilter', 'customer');
        $userComp->assertSee($this->customerNgaglik->name)
                 ->assertDontSee($this->customerDepok->name)
                 ->assertDontSee($this->customerGondomanan->name);

        // 2. Aggregate 'all' filter shows both assigned districts, but never Gondomanan (different city)
        $admin->setActiveAdminDistrictFilter('all');
        $userCompAll = Livewire::actingAs($admin)
            ->test(AdminUsersIndex::class)
            ->set('roleFilter', 'customer');
        $userCompAll->assertSee($this->customerNgaglik->name)
                    ->assertSee($this->customerDepok->name)
                    ->assertDontSee($this->customerGondomanan->name);
    }
}
