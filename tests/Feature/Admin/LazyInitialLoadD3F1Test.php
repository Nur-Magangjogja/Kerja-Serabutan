<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Partners\Activity as PartnerActivityComponent;
use App\Livewire\Admin\Verifications\Index as VerificationIndex;
use App\Livewire\SuperAdmin\TerritorySwitcher;
use App\Livewire\SuperAdmin\Users\AdminUsers;
use App\Livewire\SuperAdmin\Users\Index as UserManagementIndex;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\PartnerActivity;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class LazyInitialLoadD3F1Test extends TestCase
{
    use RefreshDatabase;

    protected City $city;
    protected District $district;
    protected User $superAdmin;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::create(['name' => 'Kota Yogyakarta', 'province' => 'DIY']);
        $this->district = District::create(['city_id' => $this->city->id, 'name' => 'Danurejan', 'is_active' => true]);

        $this->superAdmin = User::factory()->create([
            'name'        => 'Super Administrator',
            'email'       => 'superadmin@sayabantu.test',
            'role'        => 'super_admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
            'password'    => Hash::make('password123'),
        ]);

        $this->admin = User::factory()->create([
            'name'        => 'Admin Wilayah Yogyakarta',
            'email'       => 'admin.yk@sayabantu.test',
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
            'password'    => Hash::make('password123'),
        ]);
        $this->admin->managedDistricts()->attach($this->district->id);
    }

    /**
     * 1. VERIFICATION TAB TESTS
     */
    public function test_verification_index_ktp_tab_loads_only_ktp_and_leaves_vehicle_empty(): void
    {
        Registration::create([
            'uuid'        => (string) Str::uuid(),
            'name'        => 'Pendaftar KTP Test',
            'full_name'   => 'Pendaftar KTP Test',
            'email'       => 'pendaftar.ktp@test.com',
            'phone'       => '081234567890',
            'nik'         => '3471012345670001',
            'role'        => 'customer',
            'status'      => 'pending_verification',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);

        User::factory()->create([
            'name'                        => 'Mitra Vehicle Test',
            'email'                       => 'mitra.vehicle@test.com',
            'role'                        => 'mitra',
            'status'                      => 'active',
            'verified'                    => true,
            'city_id'                     => $this->city->id,
            'district_id'                 => $this->district->id,
            'vehicle_verification_status' => 'pending',
            'vehicle_plate_number'        => 'AB 1234 XY',
        ]);

        Livewire::actingAs($this->admin)
            ->test(VerificationIndex::class)
            ->assertSet('activeTab', 'ktp')
            ->assertViewHas('verifications', function ($verifications) {
                return $verifications->total() === 1;
            })
            ->assertViewHas('vehicleVerifications', function ($vehicleVerifications) {
                return $vehicleVerifications->total() === 0 && count($vehicleVerifications->items()) === 0;
            })
            ->assertViewHas('pendingKtpCount', 1)
            ->assertViewHas('pendingVehicleCount', 1)
            ->assertSee('Pendaftar KTP Test');
    }

    public function test_verification_index_vehicle_tab_loads_only_vehicle_and_leaves_ktp_empty(): void
    {
        Registration::create([
            'uuid'        => (string) Str::uuid(),
            'name'        => 'Pendaftar KTP Test',
            'full_name'   => 'Pendaftar KTP Test',
            'email'       => 'pendaftar.ktp@test.com',
            'phone'       => '081234567890',
            'nik'         => '3471012345670001',
            'role'        => 'customer',
            'status'      => 'pending_verification',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);

        User::factory()->create([
            'name'                        => 'Mitra Vehicle Test',
            'email'                       => 'mitra.vehicle@test.com',
            'role'                        => 'mitra',
            'status'                      => 'active',
            'verified'                    => true,
            'city_id'                     => $this->city->id,
            'district_id'                 => $this->district->id,
            'vehicle_verification_status' => 'pending',
            'vehicle_plate_number'        => 'AB 1234 XY',
        ]);

        Livewire::actingAs($this->admin)
            ->test(VerificationIndex::class)
            ->call('setActiveTab', 'vehicle')
            ->assertSet('activeTab', 'vehicle')
            ->assertViewHas('verifications', function ($verifications) {
                return $verifications->total() === 0 && count($verifications->items()) === 0;
            })
            ->assertViewHas('vehicleVerifications', function ($vehicleVerifications) {
                return $vehicleVerifications->total() === 1;
            })
            ->assertViewHas('pendingKtpCount', 1)
            ->assertViewHas('pendingVehicleCount', 1)
            ->assertSee('Mitra Vehicle Test')
            ->assertSee('AB 1234 XY');
    }

    /**
     * 2. PARTNER ACTIVITY TAB TESTS
     */
    public function test_partner_activity_directory_tab_loads_only_users_and_leaves_activities_empty(): void
    {
        $user = User::factory()->create([
            'name'        => 'Mitra Activity Test',
            'email'       => 'mitra.act@test.com',
            'role'        => 'mitra',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);

        PartnerActivity::create([
            'user_id'       => $user->id,
            'activity_type' => 'partner_on_the_way',
            'description'   => 'Mitra sedang menuju lokasi',
            'created_at'    => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(PartnerActivityComponent::class)
            ->assertSet('tab', 'directory')
            ->assertViewHas('users', function ($users) {
                return $users->total() >= 1;
            })
            ->assertViewHas('activities', function ($activities) {
                return $activities->total() === 0 && count($activities->items()) === 0;
            })
            ->assertSee('Mitra Activity Test');
    }

    public function test_partner_activity_streams_tab_loads_only_activities_and_leaves_users_empty(): void
    {
        $user = User::factory()->create([
            'name'        => 'Mitra Stream Test',
            'email'       => 'mitra.stream@test.com',
            'role'        => 'mitra',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);

        PartnerActivity::create([
            'user_id'       => $user->id,
            'activity_type' => 'partner_arrived',
            'description'   => 'Mitra tiba di titik jemput',
            'created_at'    => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(PartnerActivityComponent::class)
            ->call('setTab', 'streams')
            ->assertSet('tab', 'streams')
            ->assertViewHas('users', function ($users) {
                return $users->total() === 0 && count($users->items()) === 0;
            })
            ->assertViewHas('activities', function ($activities) {
                return $activities->total() >= 1;
            })
            ->assertSee('Mitra tiba di titik jemput');
    }

    /**
     * 3. USER AUDIT TIMELINE LAZY LOAD TESTS
     */
    public function test_user_detail_modal_profile_tab_does_not_load_audit_timeline(): void
    {
        $targetUser = User::factory()->create([
            'name'        => 'Target User Profile',
            'email'       => 'target.profile@test.com',
            'role'        => 'customer',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);

        Livewire::actingAs($this->superAdmin)
            ->test(UserManagementIndex::class)
            ->call('viewUser', $targetUser->id)
            ->assertSet('showViewModal', true)
            ->assertSet('activeModalTab', 'profile')
            ->assertViewHas('auditTimeline', null)
            ->assertSee('Target User Profile');
    }

    public function test_user_detail_modal_switching_to_audit_tab_loads_audit_timeline(): void
    {
        $targetUser = User::factory()->create([
            'name'        => 'Target User Audit',
            'email'       => 'target.audit@test.com',
            'role'        => 'customer',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);

        Livewire::actingAs($this->superAdmin)
            ->test(UserManagementIndex::class)
            ->call('viewUser', $targetUser->id)
            ->assertViewHas('auditTimeline', null)
            ->call('setModalTab', 'audit')
            ->assertSet('activeModalTab', 'audit')
            ->assertViewHas('auditTimeline', function ($timeline) {
                return $timeline instanceof LengthAwarePaginator;
            });
    }

    /**
     * 4. ADMIN USER MODAL OPTIONS LAZY LOAD TESTS
     */
    public function test_admin_users_index_does_not_preload_modal_datasets(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(AdminUsers::class)
            ->assertSet('showCreateModal', false)
            ->assertSet('showEditModal', false)
            ->assertSet('showViewModal', false)
            ->assertViewHas('cities', function ($cities) {
                return $cities->isEmpty();
            })
            ->assertViewHas('districts', function ($districts) {
                return $districts->isEmpty();
            });
    }

    public function test_admin_users_opening_create_or_edit_modal_loads_modal_datasets(): void
    {
        $targetAdmin = User::factory()->create([
            'name'        => 'Target Admin Edit',
            'email'       => 'target.admin@test.com',
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);
        $targetAdmin->managedDistricts()->attach($this->district->id);

        // 1. Create Modal
        Livewire::actingAs($this->superAdmin)
            ->test(AdminUsers::class)
            ->call('openCreateModal')
            ->assertSet('showCreateModal', true)
            ->assertViewHas('cities', function ($cities) {
                return $cities->isNotEmpty() && $cities->first()->id === $this->city->id;
            })
            ->assertViewHas('districts', function ($districts) {
                return $districts->isNotEmpty();
            });

        // 2. Edit Modal
        Livewire::actingAs($this->superAdmin)
            ->test(AdminUsers::class)
            ->call('editUser', $targetAdmin->id)
            ->assertSet('showEditModal', true)
            ->assertSet('selectedUserId', $targetAdmin->id)
            ->assertViewHas('cities', function ($cities) {
                return $cities->isNotEmpty();
            })
            ->assertViewHas('districts', function ($districts) {
                return $districts->isNotEmpty();
            })
            ->assertSet('managed_district_ids', [$this->district->id]);
    }

    /**
     * 5. SUPERADMIN TERRITORY SWITCHER LAZY LOAD TESTS
     */
    public function test_territory_switcher_closed_state_does_not_load_city_collection(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(TerritorySwitcher::class)
            ->assertSet('isOpen', false)
            ->assertSet('search', '')
            ->assertViewHas('cities', function ($cities) {
                return $cities->isEmpty();
            })
            ->assertViewHas('totalCities', 0)
            ->assertViewHas('totalDistricts', 0);
    }

    public function test_territory_switcher_opened_state_loads_city_collection_and_selection_works(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(TerritorySwitcher::class)
            ->call('openDropdown')
            ->assertSet('isOpen', true)
            ->assertViewHas('cities', function ($cities) {
                return $cities->isNotEmpty() && $cities->first()->id === $this->city->id;
            })
            ->assertViewHas('totalCities', function ($count) {
                return $count >= 1;
            })
            ->call('selectTerritory', 'city', $this->city->id)
            ->assertDispatched('superadmin-territory-changed')
            ->assertDispatched('admin-city-changed')
            ->assertSet('isOpen', false);
    }

    /**
     * 6. QUERY MEASUREMENT PROOFS (D3F1)
     */
    public function test_query_measurement_verification_component(): void
    {
        Registration::create([
            'uuid'        => (string) Str::uuid(),
            'name'        => 'KTP Reg',
            'full_name'   => 'KTP Reg',
            'email'       => 'ktp@test.com',
            'phone'       => '081234567890',
            'nik'         => '3471012345670002',
            'role'        => 'customer',
            'status'      => 'pending_verification',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::actingAs($this->admin)
            ->test(VerificationIndex::class);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Ensure no vehicle pagination query (latest('updated_at')->paginate()) was run, only pendingVehicleCount badge query
        $vehiclePaginationQueries = array_filter($queries, function ($q) {
            return str_contains($q['query'], 'vehicle_verification_status') && str_contains($q['query'], 'limit');
        });

        $this->assertEmpty($vehiclePaginationQueries, 'Vehicle pagination query must not execute when activeTab is ktp.');
    }

    public function test_query_measurement_partner_activity_component(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::actingAs($this->admin)
            ->test(PartnerActivityComponent::class);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Ensure no partner_activities stream pagination query was run (limit 15 perPage)
        $streamPaginationQueries = array_filter($queries, function ($q) {
            return str_contains($q['query'], 'from "partner_activities"') && str_contains($q['query'], 'limit 15');
        });

        $this->assertEmpty($streamPaginationQueries, 'Stream pagination query must not execute when tab is directory.');
    }

    public function test_query_measurement_user_detail_timeline_component(): void
    {
        $targetUser = User::factory()->create([
            'name'        => 'Target User Measurement',
            'email'       => 'target.meas@test.com',
            'role'        => 'customer',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::actingAs($this->superAdmin)
            ->test(UserManagementIndex::class)
            ->call('viewUser', $targetUser->id);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Ensure unionAll timeline queries were NOT executed
        $timelineQueries = array_filter($queries, function ($q) {
            return str_contains($q['query'], 'timeline_u') || str_contains($q['query'], 'source_type');
        });

        $this->assertEmpty($timelineQueries, 'Timeline unionAll queries must not execute when activeModalTab is profile.');
    }

    public function test_query_measurement_admin_users_modal_component(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::actingAs($this->superAdmin)
            ->test(AdminUsers::class);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Ensure no modal district queries or withCount queries were run for modals
        $modalDistrictQueries = array_filter($queries, function ($q) {
            return str_contains($q['query'], 'from "districts"') && str_contains($q['query'], 'is_active');
        });

        $this->assertEmpty($modalDistrictQueries, 'District dataset query must not execute when modals are closed.');
    }

    public function test_query_measurement_territory_switcher_component(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        Livewire::actingAs($this->superAdmin)
            ->test(TerritorySwitcher::class);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Closed territory switcher should execute ZERO queries during render
        $this->assertEmpty($queries, 'TerritorySwitcher should execute 0 queries when closed.');
    }
}
