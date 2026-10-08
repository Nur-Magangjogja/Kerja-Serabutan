<?php

namespace Tests\Feature;

use App\Livewire\SuperAdmin\Users\Index as UsersIndex;
use App\Models\City;
use App\Models\District;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserManagementAuthorizationAW5Test extends TestCase
{
    use RefreshDatabase;

    protected City $citySleman;
    protected City $cityYogya;

    protected District $distNgaglik;
    protected District $distDepok;
    protected District $distGondomanan;
    protected District $distDanurejan;

    protected User $superAdmin;
    protected User $adminNgaglik;
    protected User $adminZero;
    protected User $adminMulti;

    protected User $customerNgaglik;
    protected User $customerDepok;
    protected User $customerGondomanan;
    protected User $customerDanurejan;

    protected User $mitraNgaglik;
    protected User $otherAdmin;

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

        $this->distDanurejan = District::create([
            'city_id'   => $this->cityYogya->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);

        $this->superAdmin = User::factory()->create([
            'name'     => 'Super Admin',
            'email'    => 'superadmin@sayabantu.test',
            'role'     => 'super_admin',
            'status'   => 'active',
            'verified' => true,
            'nik'      => '3404010000000001',
            'password' => Hash::make('password123'),
        ]);

        // Admin assigned strictly to Ngaglik
        $this->adminNgaglik = User::factory()->create([
            'name'     => 'Admin Ngaglik',
            'email'    => 'admin.ngaglik@sayabantu.test',
            'role'     => 'admin',
            'status'   => 'active',
            'verified' => true,
            'nik'      => '3404010000000002',
            'city_id'  => $this->citySleman->id,
            'password' => Hash::make('password123'),
        ]);
        $this->adminNgaglik->managedDistricts()->sync([$this->distNgaglik->id]);

        // Admin with Zero assigned districts
        $this->adminZero = User::factory()->create([
            'name'     => 'Admin Zero Territory',
            'email'    => 'admin.zero@sayabantu.test',
            'role'     => 'admin',
            'status'   => 'active',
            'verified' => true,
            'nik'      => '3404010000000003',
            'city_id'  => $this->citySleman->id,
            'password' => Hash::make('password123'),
        ]);

        // Admin assigned to Ngaglik (Sleman) and Gondomanan (Yogyakarta)
        $this->adminMulti = User::factory()->create([
            'name'     => 'Admin Multi Territory',
            'email'    => 'admin.multi@sayabantu.test',
            'role'     => 'admin',
            'status'   => 'active',
            'verified' => true,
            'nik'      => '3404010000000004',
            'city_id'  => $this->citySleman->id,
            'password' => Hash::make('password123'),
        ]);
        $this->adminMulti->managedDistricts()->sync([$this->distNgaglik->id, $this->distGondomanan->id]);

        // Target Users
        $this->customerNgaglik = User::factory()->create([
            'name'        => 'Customer Ngaglik',
            'email'       => 'cust.ngaglik@sayabantu.test',
            'role'        => 'customer',
            'status'      => 'active',
            'verified'    => true,
            'nik'         => '3404010000000005',
            'city_id'     => $this->citySleman->id,
            'district_id' => $this->distNgaglik->id,
            'password'    => Hash::make('secret123'),
        ]);

        $this->customerDepok = User::factory()->create([
            'name'        => 'Customer Depok',
            'email'       => 'cust.depok@sayabantu.test',
            'role'        => 'customer',
            'status'      => 'active',
            'verified'    => true,
            'nik'         => '3404010000000006',
            'city_id'     => $this->citySleman->id,
            'district_id' => $this->distDepok->id,
            'password'    => Hash::make('secret123'),
        ]);

        $this->customerGondomanan = User::factory()->create([
            'name'        => 'Customer Gondomanan',
            'email'       => 'cust.gondomanan@sayabantu.test',
            'role'        => 'customer',
            'status'      => 'active',
            'verified'    => true,
            'nik'         => '3404010000000007',
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->distGondomanan->id,
            'password'    => Hash::make('secret123'),
        ]);

        $this->customerDanurejan = User::factory()->create([
            'name'        => 'Customer Danurejan',
            'email'       => 'cust.danurejan@sayabantu.test',
            'role'        => 'customer',
            'status'      => 'active',
            'verified'    => true,
            'nik'         => '3404010000000008',
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->distDanurejan->id,
            'password'    => Hash::make('secret123'),
        ]);

        $this->mitraNgaglik = User::factory()->create([
            'name'        => 'Mitra Ngaglik',
            'email'       => 'mitra.ngaglik@sayabantu.test',
            'role'        => 'mitra',
            'status'      => 'active',
            'verified'    => true,
            'nik'         => '3404010000000009',
            'city_id'     => $this->citySleman->id,
            'district_id' => $this->distNgaglik->id,
            'password'    => Hash::make('secret123'),
        ]);

        $this->otherAdmin = User::factory()->create([
            'name'        => 'Other Admin Account',
            'email'       => 'other.admin@sayabantu.test',
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'nik'         => '3404010000000010',
            'city_id'     => $this->citySleman->id,
            'district_id' => $this->distNgaglik->id,
            'password'    => Hash::make('secret123'),
        ]);
    }

    /**
     * AW5-1: Authorized View.
     * Admin Ngaglik viewing Customer Ngaglik -> Allowed.
     */
    public function test_aw5_1_authorized_view(): void
    {
        $comp = Livewire::actingAs($this->adminNgaglik)
            ->test(UsersIndex::class)
            ->call('viewUser', $this->customerNgaglik->id);

        $this->assertTrue($comp->get('showViewModal'));
        $this->assertEquals($this->customerNgaglik->id, $comp->get('selectedUserId'));
        $this->assertEquals($this->customerNgaglik->name, $comp->get('selectedUser')->name);
    }

    /**
     * AW5-2: Sibling View Denied.
     * Admin Ngaglik viewing Customer Depok (same city, unassigned sibling) -> Denied.
     */
    public function test_aw5_2_sibling_view_denied(): void
    {
        $comp = Livewire::actingAs($this->adminNgaglik)
            ->test(UsersIndex::class)
            ->call('viewUser', $this->customerDepok->id)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');

        $this->assertFalse($comp->get('showViewModal'));
        $this->assertNull($comp->get('selectedUserId'));
    }

    /**
     * AW5-3: Authorized Edit.
     * Admin Ngaglik editing Customer Ngaglik -> Edit modal allowed.
     */
    public function test_aw5_3_authorized_edit(): void
    {
        $comp = Livewire::actingAs($this->adminNgaglik)
            ->test(UsersIndex::class)
            ->call('editUser', $this->customerNgaglik->id);

        $this->assertTrue($comp->get('showEditModal'));
        $this->assertEquals($this->customerNgaglik->id, $comp->get('selectedUserId'));
        $this->assertEquals($this->customerNgaglik->name, $comp->get('name'));
    }

    /**
     * AW5-4: Out-of-Territory Edit.
     * Admin Ngaglik editing Customer Depok -> Denied.
     */
    public function test_aw5_4_out_of_territory_edit(): void
    {
        $comp = Livewire::actingAs($this->adminNgaglik)
            ->test(UsersIndex::class)
            ->call('editUser', $this->customerDepok->id)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');

        $this->assertFalse($comp->get('showEditModal'));
        $this->assertNull($comp->get('selectedUserId'));
    }

    /**
     * AW5-5: Save Reauthorization (TOCTOU protection).
     * Open authorized target. Before save: revoke Admin's assignment -> save denied, DB unchanged.
     */
    public function test_aw5_5_save_reauthorization_blocks_stale_assignment(): void
    {
        $originalName = $this->customerNgaglik->name;

        $comp = Livewire::actingAs($this->adminNgaglik)
            ->test(UsersIndex::class)
            ->call('editUser', $this->customerNgaglik->id)
            ->set('name', 'Maliciously Changed Name');

        // Stale assignment simulation: SuperAdmin removes Ngaglik from Admin's territory before save
        $this->adminNgaglik->managedDistricts()->sync([]);

        // Admin submits save
        $comp->call('saveUser')
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');

        // Assert database record was NOT mutated
        $this->assertSame($originalName, $this->customerNgaglik->fresh()->name);
    }

    /**
     * AW5-6: Direct Foreign-ID Invocation.
     * Malicious calls directly invoking editUser, confirmDelete, and deleteUser with foreign target -> Denied.
     */
    public function test_aw5_6_direct_foreign_id_invocation(): void
    {
        $comp = Livewire::actingAs($this->adminNgaglik)->test(UsersIndex::class);

        // 1. Direct editUser(foreignId)
        $comp->call('editUser', $this->customerGondomanan->id)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');
        $this->assertFalse($comp->get('showEditModal'));

        // 2. Direct confirmDelete(foreignId)
        $comp->call('confirmDelete', $this->customerGondomanan->id)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');
        $this->assertFalse($comp->get('showConfirmDelete'));

        // 3. Direct deleteUser with foreign ID set
        $comp->set('confirmingDeleteId', $this->customerGondomanan->id)
            ->set('adminPassword', 'password123')
            ->call('deleteUser')
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');

        // Target record must still exist in DB
        $this->assertNotNull(User::find($this->customerGondomanan->id));

        // 4. Direct toggleVerified and toggleStatus on foreign ID
        $comp->call('toggleVerified', $this->customerGondomanan->id)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');
        $this->assertTrue((bool)$this->customerGondomanan->fresh()->verified);

        $comp->call('toggleStatus', $this->customerGondomanan->id)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');
        $this->assertSame('active', $this->customerGondomanan->fresh()->status);
    }

    /**
     * AW5-7: Zero Territory.
     * Admin with no district: view, edit, save, delete, status toggle all denied.
     */
    public function test_aw5_7_zero_territory_denied_all_actions(): void
    {
        $comp = Livewire::actingAs($this->adminZero)->test(UsersIndex::class);

        // View denied
        $comp->call('viewUser', $this->customerNgaglik->id)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');

        // Edit denied
        $comp->call('editUser', $this->customerNgaglik->id)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');

        // Save denied
        $comp->set('selectedUserId', $this->customerNgaglik->id)
            ->set('name', 'Changed Name Zero')
            ->set('email', 'changed.zero@sayabantu.test')
            ->set('role', 'customer')
            ->set('status', 'active')
            ->call('saveUser')
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');
        $this->assertNotEquals('Changed Name Zero', $this->customerNgaglik->fresh()->name);

        // Delete denied
        $comp->set('confirmingDeleteId', $this->customerNgaglik->id)
            ->set('adminPassword', 'password123')
            ->call('deleteUser')
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');
        $this->assertNotNull(User::find($this->customerNgaglik->id));

        // Create new user denied for admin
        $comp->call('openCreateModal')
            ->assertSee('Admin Wilayah tidak memiliki wewenang untuk membuat pengguna baru.');
    }

    /**
     * AW5-8: Multi-District.
     * Admin assigned to Ngaglik and Gondomanan: targets in those exact districts allowed; siblings denied.
     */
    public function test_aw5_8_multidistrict_allowed_exact_and_denied_siblings(): void
    {
        $comp = Livewire::actingAs($this->adminMulti)->test(UsersIndex::class);

        // 1. Exact assigned districts: ALLOWED
        // Ngaglik
        $comp->call('viewUser', $this->customerNgaglik->id);
        $this->assertTrue($comp->get('showViewModal'));
        $comp->call('closeModal');

        $comp->call('editUser', $this->customerNgaglik->id);
        $this->assertTrue($comp->get('showEditModal'));
        $comp->call('closeModal');

        // Gondomanan
        $comp->call('viewUser', $this->customerGondomanan->id);
        $this->assertTrue($comp->get('showViewModal'));
        $comp->call('closeModal');

        $comp->call('editUser', $this->customerGondomanan->id);
        $this->assertTrue($comp->get('showEditModal'));
        $comp->call('closeModal');

        // 2. Sibling districts: DENIED
        // Depok (sibling of Ngaglik in Sleman)
        $comp->call('viewUser', $this->customerDepok->id)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');
        $comp->call('editUser', $this->customerDepok->id)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');

        // Danurejan (sibling of Gondomanan in Yogyakarta)
        $comp->call('viewUser', $this->customerDanurejan->id)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');
        $comp->call('editUser', $this->customerDanurejan->id)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');
    }

    /**
     * AW5-9: SuperAdmin Global Authority.
     * SuperAdmin retains existing global capability over any user.
     */
    public function test_aw5_9_superadmin_retains_global_capability(): void
    {
        $comp = Livewire::actingAs($this->superAdmin)->test(UsersIndex::class);

        // SuperAdmin can view any user
        $comp->call('viewUser', $this->customerNgaglik->id);
        $this->assertTrue($comp->get('showViewModal'));
        $comp->call('closeModal');

        $comp->call('viewUser', $this->customerGondomanan->id);
        $this->assertTrue($comp->get('showViewModal'));
        $comp->call('closeModal');

        // SuperAdmin can edit and update user
        $comp->call('editUser', $this->customerNgaglik->id)
            ->set('name', 'Updated By SuperAdmin')
            ->call('saveUser')
            ->assertSee('User updated successfully');

        $this->assertSame('Updated By SuperAdmin', $this->customerNgaglik->fresh()->name);
    }

    /**
     * AW5-10: Role Escalation Protection.
     * Admin Wilayah cannot promote customer/mitra to admin or super_admin.
     */
    public function test_aw5_10_role_escalation_blocked(): void
    {
        $comp = Livewire::actingAs($this->adminNgaglik)
            ->test(UsersIndex::class)
            ->call('editUser', $this->customerNgaglik->id)
            ->set('role', 'admin')
            ->call('saveUser')
            ->assertHasErrors(['role']);

        $this->assertSame('customer', $this->customerNgaglik->fresh()->role);

        $comp->set('role', 'super_admin')
            ->call('saveUser')
            ->assertHasErrors(['role']);

        $this->assertSame('customer', $this->customerNgaglik->fresh()->role);
    }

    /**
     * AW5-11: Privileged Account Target Protection.
     * Admin Wilayah cannot view or mutate Admin or SuperAdmin accounts.
     */
    public function test_aw5_11_privileged_account_target_blocked(): void
    {
        $comp = Livewire::actingAs($this->adminNgaglik)->test(UsersIndex::class);

        // 1. Direct view on SuperAdmin
        $comp->call('viewUser', $this->superAdmin->id)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');

        // 2. Direct edit on Other Admin
        $comp->call('editUser', $this->otherAdmin->id)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');

        // 3. Direct delete on Other Admin
        $comp->set('confirmingDeleteId', $this->otherAdmin->id)
            ->set('adminPassword', 'password123')
            ->call('deleteUser')
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');

        $this->assertNotNull(User::find($this->otherAdmin->id));
        $this->assertSame('admin', $this->otherAdmin->fresh()->role);
    }

    /**
     * AW5-12: Listing Scoping Preserved.
     * Zero territory -> zero rows; exact district scope preserves fail-closed isolation.
     */
    public function test_aw5_12_listing_scope_preserved(): void
    {
        // 1. Zero territory admin sees 0 rows
        $compZero = Livewire::actingAs($this->adminZero)->test(UsersIndex::class);
        $this->assertCount(0, $compZero->viewData('users'));

        // 2. Admin Ngaglik only sees Ngaglik users, never Depok or Gondomanan
        $compNgaglik = Livewire::actingAs($this->adminNgaglik)->test(UsersIndex::class);
        $users = $compNgaglik->viewData('users');
        $this->assertCount(2, $users); // customerNgaglik and mitraNgaglik
        $compNgaglik->assertSee($this->customerNgaglik->name)
                    ->assertSee($this->mitraNgaglik->name)
                    ->assertDontSee($this->customerDepok->name)
                    ->assertDontSee($this->customerGondomanan->name);
    }
}
