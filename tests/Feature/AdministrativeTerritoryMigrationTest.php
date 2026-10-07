<?php

namespace Tests\Feature;

use App\Livewire\SuperAdmin\Users\Index;
use App\Models\ActivityLog;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\Province;
use App\Models\User;
use App\Services\PartnerOnlineService;
use App\Services\Territory\ProfileTerritoryMigrationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class AdministrativeTerritoryMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected Province $provinceDIY;
    protected Province $provinceJateng;
    protected City $cityYogya;
    protected District $districtDanurejan;
    protected District $districtGondomanan;
    protected City $citySleman;
    protected District $districtDepok;
    protected City $citySemarang;
    protected District $districtBanyumanik;

    protected User $superAdmin;
    protected User $adminYogya;
    protected User $adminSleman;
    protected User $customer;
    protected User $mitra;

    protected ProfileTerritoryMigrationService $migrationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrationService = app(ProfileTerritoryMigrationService::class);

        // 1. Setup Provinces
        $this->provinceDIY = Province::create([
            'name'      => 'DI Yogyakarta',
            'code'      => '34',
            'is_active' => true,
        ]);

        $this->provinceJateng = Province::create([
            'name'      => 'Jawa Tengah',
            'code'      => '33',
            'is_active' => true,
        ]);

        // 2. Setup Cities & Districts
        // City 1: Kota Yogyakarta (DIY)
        $this->cityYogya = City::create([
            'name'        => 'Kota Yogyakarta',
            'province'    => 'DI Yogyakarta',
            'province_id' => $this->provinceDIY->id,
            'is_active'   => true,
            'latitude'    => -7.7956,
            'longitude'   => 110.3695,
        ]);

        $this->districtDanurejan = District::create([
            'city_id'   => $this->cityYogya->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);

        $this->districtGondomanan = District::create([
            'city_id'   => $this->cityYogya->id,
            'name'      => 'Gondomanan',
            'is_active' => true,
        ]);

        // City 2: Kabupaten Sleman (DIY)
        $this->citySleman = City::create([
            'name'        => 'Kabupaten Sleman',
            'province'    => 'DI Yogyakarta',
            'province_id' => $this->provinceDIY->id,
            'is_active'   => true,
            'latitude'    => -7.7167,
            'longitude'   => 110.3556,
        ]);

        $this->districtDepok = District::create([
            'city_id'   => $this->citySleman->id,
            'name'      => 'Depok',
            'is_active' => true,
        ]);

        // City 3: Kota Semarang (Jawa Tengah)
        $this->citySemarang = City::create([
            'name'        => 'Kota Semarang',
            'province'    => 'Jawa Tengah',
            'province_id' => $this->provinceJateng->id,
            'is_active'   => true,
            'latitude'    => -6.9667,
            'longitude'   => 110.4167,
        ]);

        $this->districtBanyumanik = District::create([
            'city_id'   => $this->citySemarang->id,
            'name'      => 'Banyumanik',
            'is_active' => true,
        ]);

        // 3. Setup SuperAdmin & Admins
        $this->superAdmin = User::factory()->create([
            'role'        => 'super_admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->districtDanurejan->id,
        ]);

        // Admin Yogya manages Kota Yogyakarta (Danurejan & Gondomanan)
        $this->adminYogya = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->districtDanurejan->id,
        ]);
        $this->adminYogya->managedCities()->sync([$this->cityYogya->id]);
        $this->adminYogya->managedDistricts()->sync([$this->districtDanurejan->id, $this->districtGondomanan->id]);

        // Admin Sleman manages Kabupaten Sleman (Depok)
        $this->adminSleman = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->citySleman->id,
            'district_id' => $this->districtDepok->id,
        ]);
        $this->adminSleman->managedCities()->sync([$this->citySleman->id]);
        $this->adminSleman->managedDistricts()->sync([$this->districtDepok->id]);

        // 4. Setup Customer & Mitra (initially in Danurejan, Yogya)
        $this->customer = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->districtDanurejan->id,
            'city'        => $this->cityYogya->name,
            'province'    => $this->provinceDIY->name,
            'kecamatan'   => $this->districtDanurejan->name,
        ]);

        $this->mitra = User::factory()->create([
            'role'        => 'mitra',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->districtDanurejan->id,
            'city'        => $this->cityYogya->name,
            'province'    => $this->provinceDIY->name,
            'kecamatan'   => $this->districtDanurejan->name,
        ]);
    }

    // =========================================================================
    // SECTION 1: CUSTOMER MIGRATION TEST MATRIX (C1 – C7)
    // =========================================================================

    public function test_c1_superadmin_migration_customer_a_to_b_preserves_id_and_updates_profile(): void
    {
        $originalId = $this->customer->id;
        $originalUserCount = User::count();

        $result = $this->migrationService->migrate(
            actor: $this->superAdmin,
            targetUser: $this->customer,
            newCityId: $this->citySleman->id,
            newDistrictId: $this->districtDepok->id,
            reason: 'Pindah domisili resmi ke Sleman',
            newProvinceId: $this->provinceDIY->id
        );

        $this->assertTrue($result['success']);

        $refreshedCustomer = $this->customer->fresh();
        $this->assertEquals($originalId, $refreshedCustomer->id, 'User ID must remain unchanged (NO user duplication).');
        $this->assertEquals($originalUserCount, User::count(), 'Total users count must remain identical.');
        $this->assertEquals($this->citySleman->id, $refreshedCustomer->city_id);
        $this->assertEquals($this->districtDepok->id, $refreshedCustomer->district_id);
        $this->assertEquals($this->citySleman->name, $refreshedCustomer->city);
        $this->assertEquals($this->districtDepok->name, $refreshedCustomer->kecamatan);
    }

    public function test_c2_customer_history_help_remains_original_territory(): void
    {
        // Customer creates historical completed help in Kota Semarang
        $historicalHelp = Help::create([
            'user_id'      => $this->customer->id,
            'customer_id'  => $this->customer->id,
            'city_id'      => $this->citySemarang->id,
            'district_id'  => $this->districtBanyumanik->id,
            'title'        => 'Bantuan Historis Semarang',
            'description'  => 'Pekerjaan masa lalu',
            'amount'       => 75000,
            'total_amount' => 75000,
            'status'       => 'selesai',
        ]);

        // Superadmin migrates customer to Sleman
        $this->migrationService->migrate(
            actor: $this->superAdmin,
            targetUser: $this->customer,
            newCityId: $this->citySleman->id,
            newDistrictId: $this->districtDepok->id,
            reason: 'Migrasi administratif'
        );

        $historicalHelp->refresh();
        $this->assertEquals(
            $this->citySemarang->id,
            $historicalHelp->city_id,
            'Historical Help city_id must remain original and untouched.'
        );
        $this->assertEquals(
            $this->districtBanyumanik->id,
            $historicalHelp->district_id,
            'Historical Help district_id must remain original and untouched.'
        );
    }

    public function test_c3_customer_active_help_remains_original_territory(): void
    {
        // Customer has an active ongoing help in Kota Semarang
        $activeHelp = Help::create([
            'user_id'      => $this->customer->id,
            'customer_id'  => $this->customer->id,
            'city_id'      => $this->citySemarang->id,
            'district_id'  => $this->districtBanyumanik->id,
            'title'        => 'Bantuan Aktif Semarang',
            'description'  => 'Sedang dikerjakan',
            'amount'       => 100000,
            'total_amount' => 100000,
            'status'       => 'sedang_dikerjakan',
        ]);

        // Superadmin migrates customer to Sleman
        $this->migrationService->migrate(
            actor: $this->superAdmin,
            targetUser: $this->customer,
            newCityId: $this->citySleman->id,
            newDistrictId: $this->districtDepok->id,
            reason: 'Migrasi domisili tanpa mengubah order aktif'
        );

        $activeHelp->refresh();
        $this->assertEquals($this->citySemarang->id, $activeHelp->city_id);
        $this->assertEquals($this->districtBanyumanik->id, $activeHelp->district_id);
        $this->assertEquals('sedang_dikerjakan', $activeHelp->status);
    }

    public function test_c4_customer_migration_writes_activity_log_exactly_once(): void
    {
        $logCountBefore = ActivityLog::where('action', 'profile_territory_migrated')->count();

        $this->migrationService->migrate(
            actor: $this->superAdmin,
            targetUser: $this->customer,
            newCityId: $this->citySleman->id,
            newDistrictId: $this->districtDepok->id,
            reason: 'Verifikasi pencatatan log'
        );

        $logs = ActivityLog::where('action', 'profile_territory_migrated')->get();
        $this->assertEquals($logCountBefore + 1, $logs->count(), 'ActivityLog must be recorded exactly once.');

        $log = $logs->last();
        $this->assertEquals($this->superAdmin->id, $log->user_id);
        $this->assertIsArray($log->properties);
        $this->assertEquals($this->customer->id, $log->properties['target_user_id']);
        $this->assertEquals('customer', $log->properties['target_role']);
        $this->assertEquals($this->cityYogya->id, $log->properties['old_city_id']);
        $this->assertEquals($this->districtDanurejan->id, $log->properties['old_district_id']);
        $this->assertEquals($this->citySleman->id, $log->properties['new_city_id']);
        $this->assertEquals($this->districtDepok->id, $log->properties['new_district_id']);
        $this->assertEquals($this->superAdmin->id, $log->properties['changed_by']);
        $this->assertEquals('Verifikasi pencatatan log', $log->properties['reason']);
    }

    public function test_c5_same_territory_request_rejected_no_mutation_no_log(): void
    {
        $logCountBefore = ActivityLog::where('action', 'profile_territory_migrated')->count();

        $this->expectException(ValidationException::class);

        try {
            $this->migrationService->migrate(
                actor: $this->superAdmin,
                targetUser: $this->customer,
                newCityId: $this->cityYogya->id,
                newDistrictId: $this->districtDanurejan->id, // Identical to current
                reason: 'Sama persis'
            );
        } finally {
            $this->assertEquals(
                $logCountBefore,
                ActivityLog::where('action', 'profile_territory_migrated')->count(),
                'No ActivityLog must be written on rejected same-territory request.'
            );
        }
    }

    public function test_c6_invalid_hierarchy_relation_rejected(): void
    {
        // 1. Mismatch City and Province (City Yogya with Province Jateng)
        $failedCityProvince = false;
        try {
            $this->migrationService->migrate(
                actor: $this->superAdmin,
                targetUser: $this->customer,
                newCityId: $this->cityYogya->id,
                newDistrictId: $this->districtDanurejan->id,
                reason: 'Hierarki salah',
                newProvinceId: $this->provinceJateng->id // Incompatible
            );
        } catch (ValidationException $e) {
            $failedCityProvince = true;
        }
        $this->assertTrue($failedCityProvince, 'Should reject City not belonging to selected Province.');

        // 2. Mismatch District and City (District Banyumanik with City Sleman)
        $failedDistrictCity = false;
        try {
            $this->migrationService->migrate(
                actor: $this->superAdmin,
                targetUser: $this->customer,
                newCityId: $this->citySleman->id,
                newDistrictId: $this->districtBanyumanik->id, // Banyumanik belongs to Semarang
                reason: 'Kecamatan salah kota'
            );
        } catch (ValidationException $e) {
            $failedDistrictCity = true;
        }
        $this->assertTrue($failedDistrictCity, 'Should reject District not belonging to selected City.');

        // 3. Nonexistent City ID
        $failedNonexistentCity = false;
        try {
            $this->migrationService->migrate(
                actor: $this->superAdmin,
                targetUser: $this->customer,
                newCityId: 999999,
                newDistrictId: null,
                reason: 'Kota fiktif'
            );
        } catch (ValidationException $e) {
            $failedNonexistentCity = true;
        }
        $this->assertTrue($failedNonexistentCity, 'Should reject nonexistent City.');
    }

    public function test_c7_customer_cannot_invoke_migration(): void
    {
        $this->expectException(AuthorizationException::class);

        $this->migrationService->migrate(
            actor: $this->customer,
            targetUser: $this->customer,
            newCityId: $this->citySleman->id,
            newDistrictId: $this->districtDepok->id,
            reason: 'Customer trying self-migration'
        );
    }

    // =========================================================================
    // SECTION 2: MITRA MIGRATION TEST MATRIX (M1 – M5)
    // =========================================================================

    public function test_m1_superadmin_migration_mitra_a_to_b(): void
    {
        $originalId = $this->mitra->id;

        $result = $this->migrationService->migrate(
            actor: $this->superAdmin,
            targetUser: $this->mitra,
            newCityId: $this->citySemarang->id,
            newDistrictId: $this->districtBanyumanik->id,
            reason: 'Mitra mutasi ke Semarang'
        );

        $this->assertTrue($result['success']);
        $refreshedMitra = $this->mitra->fresh();
        $this->assertEquals($originalId, $refreshedMitra->id);
        $this->assertEquals($this->citySemarang->id, $refreshedMitra->city_id);
        $this->assertEquals($this->districtBanyumanik->id, $refreshedMitra->district_id);
    }

    public function test_m2_partner_online_state_and_gps_unchanged_on_mitra_migration(): void
    {
        // Mitra starts searching at Sleman coordinates (Runtime GPS != Profile domicile)
        $onlineService = app(PartnerOnlineService::class);
        $onlineService->startSearching($this->mitra, -7.7167, 110.3556);

        $initialState = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertNotNull($initialState);
        $this->assertEquals(-7.7167, (float) $initialState->latitude);
        $this->assertEquals(110.3556, (float) $initialState->longitude);
        $this->assertEquals(PartnerOnlineState::STATUS_SEARCHING, $initialState->matching_status);

        // Superadmin migrates Mitra profile territory from Yogya to Semarang
        $this->migrationService->migrate(
            actor: $this->superAdmin,
            targetUser: $this->mitra,
            newCityId: $this->citySemarang->id,
            newDistrictId: $this->districtBanyumanik->id,
            reason: 'Migrasi administratif Mitra'
        );

        // Verify PartnerOnlineState is UNCHANGED
        $refreshedState = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertEquals(-7.7167, (float) $refreshedState->latitude);
        $this->assertEquals(110.3556, (float) $refreshedState->longitude);
        $this->assertEquals(PartnerOnlineState::STATUS_SEARCHING, $refreshedState->matching_status);
    }

    public function test_m3_mitra_current_taken_help_remains_original_territory(): void
    {
        // Mitra currently taking a help in Sleman
        $takenHelp = Help::create([
            'user_id'      => $this->customer->id,
            'customer_id'  => $this->customer->id,
            'mitra_id'     => $this->mitra->id,
            'city_id'      => $this->citySleman->id,
            'district_id'  => $this->districtDepok->id,
            'title'        => 'Pekerjaan Aktif Mitra Sleman',
            'description'  => 'Pekerjaan sedang berlangsung',
            'amount'       => 80000,
            'total_amount' => 80000,
            'status'       => 'sedang_dikerjakan',
        ]);

        // Migrate Mitra profile to Semarang
        $this->migrationService->migrate(
            actor: $this->superAdmin,
            targetUser: $this->mitra,
            newCityId: $this->citySemarang->id,
            newDistrictId: $this->districtBanyumanik->id,
            reason: 'Migrasi Mitra saat ada pekerjaan aktif'
        );

        $takenHelp->refresh();
        $this->assertEquals($this->citySleman->id, $takenHelp->city_id);
        $this->assertEquals($this->districtDepok->id, $takenHelp->district_id);
        $this->assertEquals($this->mitra->id, $takenHelp->mitra_id);
        $this->assertEquals('sedang_dikerjakan', $takenHelp->status);
    }

    public function test_m4_mitra_migration_writes_activity_log_exactly_once(): void
    {
        $logCountBefore = ActivityLog::where('action', 'profile_territory_migrated')->count();

        $this->migrationService->migrate(
            actor: $this->superAdmin,
            targetUser: $this->mitra,
            newCityId: $this->citySleman->id,
            newDistrictId: $this->districtDepok->id,
            reason: 'Migrasi Mitra untuk audit log'
        );

        $logs = ActivityLog::where('action', 'profile_territory_migrated')->get();
        $this->assertEquals($logCountBefore + 1, $logs->count());
        $log = $logs->last();
        $this->assertEquals('mitra', $log->properties['target_role']);
    }

    public function test_m5_mitra_cannot_invoke_migration(): void
    {
        $this->expectException(AuthorizationException::class);

        $this->migrationService->migrate(
            actor: $this->mitra,
            targetUser: $this->mitra,
            newCityId: $this->citySleman->id,
            newDistrictId: $this->districtDepok->id,
            reason: 'Mitra unauthorized self-migration'
        );
    }

    // =========================================================================
    // SECTION 3: ADMIN WILAYAH PERMISSION TEST MATRIX (A1 – A5)
    // =========================================================================

    public function test_a1_admin_owns_current_and_destination_allows_migration(): void
    {
        // Admin Yogya owns both Danurejan and Gondomanan in Kota Yogyakarta.
        // User currently in Danurejan. Destination: Gondomanan (intra-scope).
        $result = $this->migrationService->migrate(
            actor: $this->adminYogya,
            targetUser: $this->customer,
            newCityId: $this->cityYogya->id,
            newDistrictId: $this->districtGondomanan->id,
            reason: 'Koreksi kecamatan dalam wilayah wewenang Admin'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals($this->districtGondomanan->id, $this->customer->fresh()->district_id);
    }

    public function test_a2_admin_owns_current_but_destination_outside_scope_denied(): void
    {
        // Admin Yogya owns Kota Yogyakarta, but destination is Sleman (outside scope).
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Perpindahan ke wilayah di luar kewenangan Anda harus dilakukan oleh SuperAdmin.');

        $this->migrationService->migrate(
            actor: $this->adminYogya,
            targetUser: $this->customer,
            newCityId: $this->citySleman->id,
            newDistrictId: $this->districtDepok->id,
            reason: 'Mencoba pindah ke luar wilayah wewenang'
        );
    }

    public function test_a3_admin_does_not_own_current_territory_cannot_pull_user(): void
    {
        // Admin Sleman manages Sleman. Customer currently in Danurejan (Kota Yogyakarta).
        // Admin Sleman tries to migrate Customer into Sleman (pulling user).
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Pengguna saat ini berada di luar wilayah kewenangan Anda.');

        $this->migrationService->migrate(
            actor: $this->adminSleman,
            targetUser: $this->customer,
            newCityId: $this->citySleman->id,
            newDistrictId: $this->districtDepok->id,
            reason: 'Menarik user dari wilayah lain'
        );
    }

    public function test_a4_admin_crafted_destination_outside_authority_rejected_in_livewire(): void
    {
        $this->actingAs($this->adminYogya);

        // Attempting to send a crafted request in Livewire with a destination city outside authority
        Livewire::test(Index::class)
            ->call('openMigrationModal', $this->customer->id)
            ->set('migrationProvinceId', $this->provinceDIY->id)
            ->set('migrationCityId', $this->citySleman->id) // Crafted: Sleman is outside Admin Yogya
            ->set('migrationDistrictId', $this->districtDepok->id)
            ->set('migrationReason', 'Alasan testing bypass form')
            ->call('submitMigration')
            ->assertHasErrors('migrationReason'); // AuthorizationException caught and added to error bag

        // Ensure user was NOT moved
        $this->assertEquals($this->cityYogya->id, $this->customer->fresh()->city_id);
    }

    public function test_a5_superadmin_cross_scope_migration_allowed(): void
    {
        // SuperAdmin can migrate user across completely different provinces/admins
        $result = $this->migrationService->migrate(
            actor: $this->superAdmin,
            targetUser: $this->customer,
            newCityId: $this->citySemarang->id,
            newDistrictId: $this->districtBanyumanik->id,
            reason: 'SuperAdmin migrasi lintas provinsi',
            newProvinceId: $this->provinceJateng->id
        );

        $this->assertTrue($result['success']);
        $this->assertEquals($this->citySemarang->id, $this->customer->fresh()->city_id);
    }

    // =========================================================================
    // SECTION 4: VISIBILITY TEST (V1)
    // =========================================================================

    public function test_v1_old_admin_loses_visibility_new_admin_gains_visibility_after_migration(): void
    {
        // Customer is initially in Danurejan (managed by Admin Yogya)
        $this->actingAs($this->adminYogya);
        Livewire::test(Index::class)
            ->assertSee($this->customer->name);

        $this->actingAs($this->adminSleman);
        Livewire::test(Index::class)
            ->assertDontSee($this->customer->name);

        // SuperAdmin migrates Customer from Yogya to Sleman (Depok)
        $this->migrationService->migrate(
            actor: $this->superAdmin,
            targetUser: $this->customer,
            newCityId: $this->citySleman->id,
            newDistrictId: $this->districtDepok->id,
            reason: 'Pindah manajemen admin'
        );

        // After migration: Admin Yogya NO LONGER sees the customer
        $this->actingAs($this->adminYogya);
        Livewire::test(Index::class)
            ->assertDontSee($this->customer->name);

        // Admin Sleman NOW SEES the customer
        $this->actingAs($this->adminSleman);
        Livewire::test(Index::class)
            ->assertSee($this->customer->name);
    }

    // =========================================================================
    // SECTION 5: ADMIN AND SUPERADMIN UI TEST MATRIX (UI1 – UI8)
    // =========================================================================

    public function test_ui1_admin_sees_migration_action_on_customer_in_scope(): void
    {
        $this->actingAs($this->adminYogya);

        Livewire::test(Index::class)
            ->assertSee($this->customer->name)
            ->call('openMigrationModal', $this->customer->id)
            ->assertSet('showMigrationModal', true)
            ->assertSet('migrationUserId', $this->customer->id)
            ->assertSee('Migrasi Wilayah Profil');
    }

    public function test_ui2_admin_sees_migration_action_on_mitra_in_scope(): void
    {
        $this->actingAs($this->adminYogya);

        Livewire::test(Index::class)
            ->assertSee($this->mitra->name)
            ->call('openMigrationModal', $this->mitra->id)
            ->assertSet('showMigrationModal', true)
            ->assertSet('migrationUserId', $this->mitra->id)
            ->assertSee('Migrasi Wilayah Profil');
    }

    public function test_ui3_destination_dropdown_list_only_contains_territory_in_admin_authority(): void
    {
        $this->actingAs($this->adminYogya);

        $test = Livewire::test(Index::class)
            ->call('openMigrationModal', $this->customer->id);

        $availableProvinces = $test->get('migrationAvailableProvinces');
        $provIds = array_column($availableProvinces, 'id');
        $this->assertContains($this->provinceDIY->id, $provIds);
        $this->assertNotContains($this->provinceJateng->id, $provIds, 'Jateng is not managed by Admin Yogya.');

        $availableCities = $test->get('migrationAvailableCities');
        $cityIds = array_column($availableCities, 'id');
        $this->assertContains($this->cityYogya->id, $cityIds);
        $this->assertNotContains($this->citySleman->id, $cityIds, 'Sleman is not managed by Admin Yogya.');
        $this->assertNotContains($this->citySemarang->id, $cityIds, 'Semarang is not managed by Admin Yogya.');

        $availableDistricts = $test->get('migrationAvailableDistricts');
        $distIds = array_column($availableDistricts, 'id');
        $this->assertContains($this->districtDanurejan->id, $distIds);
        $this->assertContains($this->districtGondomanan->id, $distIds);
        $this->assertNotContains($this->districtDepok->id, $distIds, 'Depok is not managed by Admin Yogya.');
    }

    public function test_ui4_admin_same_scope_migration_succeeds_via_livewire_action(): void
    {
        $this->actingAs($this->adminYogya);

        Livewire::test(Index::class)
            ->call('openMigrationModal', $this->customer->id)
            ->set('migrationProvinceId', $this->provinceDIY->id)
            ->set('migrationCityId', $this->cityYogya->id)
            ->set('migrationDistrictId', $this->districtGondomanan->id)
            ->set('migrationReason', 'Pindah domisili ke Gondomanan secara resmi')
            ->call('submitMigration')
            ->assertHasNoErrors()
            ->assertSet('showMigrationModal', false);

        $this->assertEquals($this->districtGondomanan->id, $this->customer->fresh()->district_id);
        $this->assertEquals('Gondomanan', $this->customer->fresh()->kecamatan);
    }

    public function test_ui5_admin_crafted_destination_outside_scope_rejected_via_ui(): void
    {
        $this->actingAs($this->adminYogya);

        Livewire::test(Index::class)
            ->call('openMigrationModal', $this->customer->id)
            ->set('migrationProvinceId', $this->provinceJateng->id)
            ->set('migrationCityId', $this->citySemarang->id)
            ->set('migrationDistrictId', $this->districtBanyumanik->id)
            ->set('migrationReason', 'Mencoba bypass scope wilayah')
            ->call('submitMigration')
            ->assertHasErrors('migrationReason');

        $this->assertEquals($this->districtDanurejan->id, $this->customer->fresh()->district_id, 'User territory must not mutate.');
    }

    public function test_ui6_admin_trying_user_outside_current_scope_rejected_via_ui(): void
    {
        $customerInSleman = User::factory()->create([
            'role'        => 'customer',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->citySleman->id,
            'district_id' => $this->districtDepok->id,
        ]);

        $this->actingAs($this->adminYogya);

        Livewire::test(Index::class)
            ->call('openMigrationModal', $customerInSleman->id)
            ->assertSet('showMigrationModal', false)
            ->assertSee('Pengguna berada di luar wilayah kewenangan Anda.');
    }

    public function test_ui7_superadmin_cross_scope_succeeds_via_livewire_ui(): void
    {
        $this->actingAs($this->superAdmin);

        Livewire::test(Index::class)
            ->call('openMigrationModal', $this->customer->id)
            ->set('migrationProvinceId', $this->provinceJateng->id)
            ->set('migrationCityId', $this->citySemarang->id)
            ->set('migrationDistrictId', $this->districtBanyumanik->id)
            ->set('migrationReason', 'Superadmin memindahkan user lintas provinsi')
            ->call('submitMigration')
            ->assertHasNoErrors()
            ->assertSet('showMigrationModal', false);

        $refreshed = $this->customer->fresh();
        $this->assertEquals($this->citySemarang->id, $refreshed->city_id);
        $this->assertEquals($this->districtBanyumanik->id, $refreshed->district_id);
        $this->assertEquals('Kota Semarang', $refreshed->city);
        $this->assertEquals('Banyumanik', $refreshed->kecamatan);
    }

    public function test_ui8_customer_and_mitra_cannot_access_migration_routes_or_actions(): void
    {
        // 1. Customer cannot access admin users route
        $this->actingAs($this->customer);
        $resCustAdmin = $this->get(route('admin.users.index'));
        $this->assertTrue(in_array($resCustAdmin->status(), [403, 302]));

        $resCustSuper = $this->get(route('superadmin.users'));
        $this->assertTrue(in_array($resCustSuper->status(), [403, 302]));

        // 2. Mitra cannot access admin users route
        $this->actingAs($this->mitra);
        $resMitraAdmin = $this->get(route('admin.users.index'));
        $this->assertTrue(in_array($resMitraAdmin->status(), [403, 302]));

        $resMitraSuper = $this->get(route('superadmin.users'));
        $this->assertTrue(in_array($resMitraSuper->status(), [403, 302]));
    }

    // =========================================================================
    // SECTION 6: G3 CONSISTENCY & CANONICAL AUTHORITY TESTS
    // =========================================================================

    public function test_city_level_admin_without_district_pivot_allows_migration_and_expands_districts(): void
    {
        // 1. Admin assigned ONLY City Yogya via managedCities without district pivot has NO district authority
        $cityOnlyAdmin = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => $this->cityYogya->id,
            'district_id' => $this->districtDanurejan->id,
        ]);
        $cityOnlyAdmin->managedCities()->sync([$this->cityYogya->id]);

        $this->assertEquals([], $cityOnlyAdmin->getAdminDistrictIds());

        // 2. Exact district assignment grants authority for assigned districts
        $districtAdmin = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
        ]);
        $districtAdmin->managedDistricts()->sync([$this->districtDanurejan->id, $this->districtGondomanan->id]);
        $districtAdmin->managedCities()->sync([$this->cityYogya->id]);

        $this->actingAs($districtAdmin);

        // User in Danurejan (A1) is visible in user management
        Livewire::test(Index::class)
            ->assertSee($this->customer->name)
            // Migration modal can be opened
            ->call('openMigrationModal', $this->customer->id)
            ->assertSet('showMigrationModal', true)
            ->set('migrationCityId', $this->cityYogya->id)
            // Destination district Gondomanan (A2) is available in dropdown
            ->assertSet('migrationAvailableDistricts', function ($districts) {
                $ids = array_column($districts, 'id');
                return in_array($this->districtGondomanan->id, $ids, true) && in_array($this->districtDanurejan->id, $ids, true);
            })
            // Migration Danurejan -> Gondomanan ALLOWED and succeeds
            ->set('migrationDistrictId', $this->districtGondomanan->id)
            ->set('migrationReason', 'Pindah domisili internal kota Yogyakarta')
            ->call('submitMigration')
            ->assertHasNoErrors()
            ->assertSet('showMigrationModal', false);

        $this->assertEquals($this->districtGondomanan->id, $this->customer->fresh()->district_id);
    }

    public function test_mixed_assignment_admin_authority_merges_city_districts_and_specific_district(): void
    {
        // Create district Mlati in Sleman (B2)
        $districtMlati = District::create([
            'city_id'   => $this->citySleman->id,
            'name'      => 'Mlati',
            'is_active' => true,
        ]);

        // Admin has exact District Danurejan (A1 in Yogya) + exact District Depok (B1 in Sleman)
        $mixedAdmin = User::factory()->create([
            'role'   => 'admin',
            'status' => 'active',
        ]);
        $mixedAdmin->managedCities()->sync([$this->cityYogya->id, $this->citySleman->id]);
        $mixedAdmin->managedDistricts()->sync([$this->districtDanurejan->id, $this->districtDepok->id]);

        $adminDistrictIds = $mixedAdmin->getAdminDistrictIds();

        // Authority must include exactly: Danurejan (A1) and Depok (B1)
        $this->assertContains($this->districtDanurejan->id, $adminDistrictIds);
        $this->assertContains($this->districtDepok->id, $adminDistrictIds);
        // Authority must NOT include unassigned sibling Gondomanan (A2) or Mlati (B2)
        $this->assertNotContains($this->districtGondomanan->id, $adminDistrictIds);
        $this->assertNotContains($districtMlati->id, $adminDistrictIds);

        // Migration from A1 to B1 (Depok) is ALLOWED
        $this->migrationService->migrate(
            actor: $mixedAdmin,
            targetUser: $this->customer,
            newCityId: $this->citySleman->id,
            newDistrictId: $this->districtDepok->id,
            reason: 'Migrasi ke Depok Sleman yang diotorisasi'
        );
        $this->assertEquals($this->districtDepok->id, $this->customer->fresh()->district_id);

        // Migration to B2 (Mlati) must be DENIED
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->migrationService->migrate(
            actor: $mixedAdmin,
            targetUser: $this->customer,
            newCityId: $this->citySleman->id,
            newDistrictId: $districtMlati->id,
            reason: 'Mencoba migrasi ke Mlati tanpa otorisasi'
        );
    }

    public function test_no_profile_fallback_when_explicit_territory_is_assigned(): void
    {
        // Admin assigned City Yogya with Danurejan and Gondomanan, but personally lives in Semarang (Banyumanik)
        $externalLivingAdmin = User::factory()->create([
            'role'        => 'admin',
            'status'      => 'active',
            'city_id'     => $this->citySemarang->id,
            'district_id' => $this->districtBanyumanik->id,
        ]);
        $externalLivingAdmin->managedDistricts()->sync([$this->districtDanurejan->id, $this->districtGondomanan->id]);
        $externalLivingAdmin->managedCities()->sync([$this->cityYogya->id]);

        $adminCityIds = $externalLivingAdmin->getAdminCityIds();
        $adminDistrictIds = $externalLivingAdmin->getAdminDistrictIds();

        // Authorized for City Yogya and its assigned districts only
        $this->assertContains($this->cityYogya->id, $adminCityIds);
        $this->assertContains($this->districtDanurejan->id, $adminDistrictIds);
        $this->assertContains($this->districtGondomanan->id, $adminDistrictIds);

        // MUST NOT contain profile domicile Semarang or Banyumanik
        $this->assertNotContains($this->citySemarang->id, $adminCityIds);
        $this->assertNotContains($this->districtBanyumanik->id, $adminDistrictIds);

        // Customer in Semarang cannot be managed or migrated by this admin
        $customerSemarang = User::factory()->create([
            'role'        => 'customer',
            'city_id'     => $this->citySemarang->id,
            'district_id' => $this->districtBanyumanik->id,
            'verified'    => true,
            'status'      => 'active',
        ]);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->migrationService->migrate(
            actor: $externalLivingAdmin,
            targetUser: $customerSemarang,
            newCityId: $this->cityYogya->id,
            newDistrictId: $this->districtDanurejan->id,
            reason: 'Admin coba menarik customer dari kota domisili pribadinya'
        );
    }
}

