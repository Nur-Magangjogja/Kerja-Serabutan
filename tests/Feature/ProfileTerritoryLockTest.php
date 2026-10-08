<?php

namespace Tests\Feature;

use App\Livewire\Mitra\Profile\Edit as MitraProfileEditModal;
use App\Livewire\Mitra\Profile\EditPage as MitraProfileEditPage;
use App\Livewire\Profile\UpdateProfileInformationForm;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\Province;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileTerritoryLockTest extends TestCase
{
    use RefreshDatabase;

    protected Province $provinceYogya;
    protected Province $provinceJateng;
    protected City $cityA; // Sleman
    protected City $cityB; // Surakarta
    protected District $districtA1; // Depok (Sleman)
    protected District $districtB1; // Banjarsari (Surakarta)

    protected function setUp(): void
    {
        parent::setUp();

        $this->provinceYogya = Province::create([
            'code' => '34',
            'name' => 'DI Yogyakarta',
            'is_active' => true,
        ]);

        $this->provinceJateng = Province::create([
            'code' => '33',
            'name' => 'Jawa Tengah',
            'is_active' => true,
        ]);

        $this->cityA = City::create([
            'code' => '3404',
            'name' => 'Kabupaten Sleman',
            'type' => 'Kabupaten',
            'province' => 'DI Yogyakarta',
            'province_id' => $this->provinceYogya->id,
            'is_active' => true,
            'latitude' => -7.71556,
            'longitude' => 110.35556,
        ]);

        $this->cityB = City::create([
            'code' => '3372',
            'name' => 'Kota Surakarta',
            'type' => 'Kota',
            'province' => 'Jawa Tengah',
            'province_id' => $this->provinceJateng->id,
            'is_active' => true,
            'latitude' => -7.56667,
            'longitude' => 110.81667,
        ]);

        $this->districtA1 = District::create([
            'city_id' => $this->cityA->id,
            'name' => 'Depok',
            'code' => '340407',
            'is_active' => true,
        ]);

        $this->districtB1 = District::create([
            'city_id' => $this->cityB->id,
            'name' => 'Banjarsari',
            'code' => '337205',
            'is_active' => true,
        ]);
    }

    /**
     * C1: Customer registration assigns City A / District A1 and stores properly.
     */
    public function test_c1_customer_registration_stores_initial_territory_correctly(): void
    {
        $user = User::create([
            'name' => 'Customer Baru',
            'email' => 'customer.baru@example.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'customer',
            'status' => 'active',
            'verified' => true,
            'phone' => '081234567890',
            'city_id' => $this->cityA->id,
            'district_id' => $this->districtA1->id,
            'city' => $this->cityA->name,
            'province' => $this->cityA->province,
            'kecamatan' => $this->districtA1->name,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'city_id' => $this->cityA->id,
            'district_id' => $this->districtA1->id,
        ]);
    }

    /**
     * C2: Customer profile edit form displays territory as readonly information.
     */
    public function test_c2_customer_profile_displays_territory(): void
    {
        $customer = User::create([
            'name' => 'Customer Sleman',
            'email' => 'customer.sleman@example.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'customer',
            'status' => 'active',
            'verified' => true,
            'phone' => '081234567891',
            'city_id' => $this->cityA->id,
            'district_id' => $this->districtA1->id,
            'city' => $this->cityA->name,
            'province' => $this->cityA->province,
            'kecamatan' => $this->districtA1->name,
        ]);

        $this->actingAs($customer);

        Livewire::test(UpdateProfileInformationForm::class)
            ->assertSet('name', 'Customer Sleman')
            ->assertSet('city', $this->cityA->name)
            ->assertSet('province', $this->cityA->province)
            ->assertSet('kecamatan', $this->districtA1->name)
            ->assertSee($this->cityA->name)
            ->assertSee('Wilayah domisili dikelola melalui administrasi wilayah.');
    }

    /**
     * C3: Customer normal UI update without territory changes name/phone while territory remains A/A1.
     */
    public function test_c3_customer_normal_update_preserves_territory(): void
    {
        $customer = User::create([
            'name' => 'Customer Sleman',
            'email' => 'customer.sleman@example.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'customer',
            'status' => 'active',
            'verified' => true,
            'phone' => '081234567891',
            'city_id' => $this->cityA->id,
            'district_id' => $this->districtA1->id,
            'city' => $this->cityA->name,
            'province' => $this->cityA->province,
            'kecamatan' => $this->districtA1->name,
        ]);

        $this->actingAs($customer);

        Livewire::test(UpdateProfileInformationForm::class)
            ->set('name', 'Customer Sleman Updated')
            ->set('phone', '081299998888')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $customer->refresh();
        $this->assertEquals('Customer Sleman Updated', $customer->name);
        $this->assertEquals('081299998888', $customer->phone);
        $this->assertEquals($this->cityA->id, $customer->city_id);
        $this->assertEquals($this->districtA1->id, $customer->district_id);
    }

    /**
     * C4: Customer crafted request attempting to modify territory is ignored / rejected.
     */
    public function test_c4_customer_crafted_request_cannot_modify_territory(): void
    {
        $customer = User::create([
            'name' => 'Customer Sleman',
            'email' => 'customer.sleman@example.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'customer',
            'status' => 'active',
            'verified' => true,
            'phone' => '081234567891',
            'city_id' => $this->cityA->id,
            'district_id' => $this->districtA1->id,
            'city' => $this->cityA->name,
            'province' => $this->cityA->province,
            'kecamatan' => $this->districtA1->name,
        ]);

        $this->actingAs($customer);

        // Attempting to inject new city_id and district_id directly into component
        Livewire::test(UpdateProfileInformationForm::class)
            ->set('city_id', $this->cityB->id)
            ->set('district_id', $this->districtB1->id)
            ->set('city', $this->cityB->name)
            ->set('province', $this->cityB->province)
            ->set('kecamatan', $this->districtB1->name)
            ->call('setCityId', $this->cityB->id)
            ->call('updateProfileInformation');

        $customer->refresh();
        $this->assertEquals($this->cityA->id, $customer->city_id, 'users.city_id must NOT be altered by manipulated request');
        $this->assertEquals($this->districtA1->id, $customer->district_id, 'users.district_id must NOT be altered by manipulated request');
        $this->assertEquals($this->cityA->name, $customer->city);
        $this->assertEquals($this->districtA1->name, $customer->kecamatan);
    }

    /**
     * C5: Customer editable fields (name, phone, landmarks) remain functional.
     */
    public function test_c5_customer_unrelated_fields_remain_editable(): void
    {
        $customer = User::create([
            'name' => 'Customer Sleman',
            'email' => 'customer.sleman@example.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'customer',
            'status' => 'active',
            'verified' => true,
            'phone' => '081234567891',
            'city_id' => $this->cityA->id,
            'district_id' => $this->districtA1->id,
            'city' => $this->cityA->name,
            'province' => $this->cityA->province,
            'kecamatan' => $this->districtA1->name,
        ]);

        $this->actingAs($customer);

        Livewire::test(UpdateProfileInformationForm::class)
            ->call('openNewLandmarkForm')
            ->set('newLandmarkLabel', 'Rumah Nenek')
            ->set('newLandmarkPatokan', 'Depan gapura RT 02')
            ->call('saveLandmark')
            ->assertHasNoErrors();

        $customer->refresh();
        $this->assertCount(1, $customer->getSavedLandmarksList());
        $this->assertEquals('Rumah Nenek', $customer->getSavedLandmarksList()[0]['label']);
    }

    /**
     * M1: Mitra registration assigns City A / District A1 and stores properly.
     */
    public function test_m1_mitra_registration_stores_initial_territory_correctly(): void
    {
        $mitra = User::create([
            'name' => 'Mitra Sleman',
            'email' => 'mitra.sleman@example.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'mitra',
            'status' => 'active',
            'verified' => true,
            'phone' => '081234567892',
            'city_id' => $this->cityA->id,
            'district_id' => $this->districtA1->id,
            'city' => $this->cityA->name,
            'province' => $this->cityA->province,
            'kecamatan' => $this->districtA1->name,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $mitra->id,
            'role' => 'mitra',
            'city_id' => $this->cityA->id,
            'district_id' => $this->districtA1->id,
        ]);
    }

    /**
     * M2: Mitra profile display shows City A / District A1.
     */
    public function test_m2_mitra_profile_displays_territory(): void
    {
        $mitra = User::create([
            'name' => 'Mitra Sleman',
            'email' => 'mitra.sleman@example.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'mitra',
            'status' => 'active',
            'verified' => true,
            'phone' => '081234567892',
            'city_id' => $this->cityA->id,
            'district_id' => $this->districtA1->id,
            'city' => $this->cityA->name,
            'province' => $this->cityA->province,
            'kecamatan' => $this->districtA1->name,
        ]);

        $this->actingAs($mitra);

        Livewire::test(UpdateProfileInformationForm::class)
            ->assertSee($this->cityA->name)
            ->assertSee($this->districtA1->name)
            ->assertSee('Wilayah domisili dikelola melalui administrasi wilayah.');
    }

    /**
     * M3 & M4: Mitra attempts to update City B or District B1 -> territory remains City A / District A1.
     */
    public function test_m3_and_m4_mitra_cannot_change_city_or_district(): void
    {
        $mitra = User::create([
            'name' => 'Mitra Sleman',
            'email' => 'mitra.sleman@example.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'mitra',
            'status' => 'active',
            'verified' => true,
            'phone' => '081234567892',
            'city_id' => $this->cityA->id,
            'district_id' => $this->districtA1->id,
            'city' => $this->cityA->name,
            'province' => $this->cityA->province,
            'kecamatan' => $this->districtA1->name,
        ]);

        $this->actingAs($mitra);

        // Test through UpdateProfileInformationForm
        Livewire::test(UpdateProfileInformationForm::class)
            ->set('city_id', $this->cityB->id)
            ->set('district_id', $this->districtB1->id)
            ->call('updateProfileInformation');

        $mitra->refresh();
        $this->assertEquals($this->cityA->id, $mitra->city_id);
        $this->assertEquals($this->districtA1->id, $mitra->district_id);

        // Test through MitraProfileEditPage
        Livewire::test(MitraProfileEditPage::class)
            ->set('city_id', $this->cityB->id)
            ->call('save');

        $mitra->refresh();
        $this->assertEquals($this->cityA->id, $mitra->city_id);
        $this->assertEquals($this->districtA1->id, $mitra->district_id);

        // Test through MitraProfileEditModal
        Livewire::test(MitraProfileEditModal::class)
            ->call('openModal')
            ->set('city_id', $this->cityB->id)
            ->set('district_id', $this->districtB1->id)
            ->call('save');

        $mitra->refresh();
        $this->assertEquals($this->cityA->id, $mitra->city_id);
        $this->assertEquals($this->districtA1->id, $mitra->district_id);
    }

    /**
     * M5: Mitra manipulated Livewire request ignored.
     */
    public function test_m5_mitra_manipulated_request_does_not_mutate_profile_territory(): void
    {
        $mitra = User::create([
            'name' => 'Mitra Sleman',
            'email' => 'mitra.sleman@example.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'mitra',
            'status' => 'active',
            'verified' => true,
            'phone' => '081234567892',
            'city_id' => $this->cityA->id,
            'district_id' => $this->districtA1->id,
        ]);

        $this->actingAs($mitra);

        Livewire::test(MitraProfileEditModal::class)
            ->set('city_id', 99999)
            ->set('district_id', 88888)
            ->call('save');

        $mitra->refresh();
        $this->assertEquals($this->cityA->id, $mitra->city_id);
        $this->assertEquals($this->districtA1->id, $mitra->district_id);
    }

    /**
     * M6: Unrelated Mitra profile fields remain editable.
     */
    public function test_m6_mitra_unrelated_fields_remain_editable(): void
    {
        $mitra = User::create([
            'name' => 'Mitra Sleman',
            'email' => 'mitra.sleman@example.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'mitra',
            'status' => 'active',
            'verified' => true,
            'phone' => '081234567892',
            'address' => 'Alamat Lama',
            'city_id' => $this->cityA->id,
            'district_id' => $this->districtA1->id,
        ]);

        $this->actingAs($mitra);

        Livewire::test(MitraProfileEditPage::class)
            ->set('name', 'Mitra Sleman Hebat')
            ->set('phone', '081277776666')
            ->set('address', 'Jl. Kaliurang KM 5 No. 12')
            ->call('save')
            ->assertHasNoErrors();

        $mitra->refresh();
        $this->assertEquals('Mitra Sleman Hebat', $mitra->name);
        $this->assertEquals('081277776666', $mitra->phone);
        $this->assertEquals('Jl. Kaliurang KM 5 No. 12', $mitra->address);
        $this->assertEquals($this->cityA->id, $mitra->city_id);
    }

    /**
     * GPS Immutability Test: Mitra in City A / District A1 with PartnerOnlineState in City B
     * users.city_id remains City A, users.district_id remains District A1.
     */
    public function test_gps_does_not_mutate_mitra_profile_territory(): void
    {
        $mitra = User::create([
            'name' => 'Mitra Sleman',
            'email' => 'mitra.gps@example.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'mitra',
            'status' => 'active',
            'verified' => true,
            'city_id' => $this->cityA->id,
            'district_id' => $this->districtA1->id,
        ]);

        // Mitra is physically in City B (Surakarta)
        PartnerOnlineState::create([
            'user_id' => $mitra->id,
            'city_id' => $this->cityB->id,
            'district_id' => $this->districtB1->id,
            'latitude' => $this->cityB->latitude,
            'longitude' => $this->cityB->longitude,
            'is_online' => true,
            'is_seeking' => true,
            'last_seen_at' => now(),
        ]);

        $mitra->refresh();
        $this->assertEquals($this->cityA->id, $mitra->city_id, 'Profile city must remain City A despite GPS in City B');
        $this->assertEquals($this->districtA1->id, $mitra->district_id, 'Profile district must remain District A1 despite GPS in District B1');
    }

    /**
     * Customer Map Immutability Test: Customer in City A / District A1 creates Help in City B
     * Help is B/B1, users.city_id remains A, users.district_id remains A1.
     */
    public function test_customer_map_order_does_not_mutate_profile_territory(): void
    {
        $customer = User::create([
            'name' => 'Customer Sleman',
            'email' => 'customer.map@example.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'customer',
            'status' => 'active',
            'verified' => true,
            'city_id' => $this->cityA->id,
            'district_id' => $this->districtA1->id,
        ]);

        $help = Help::create([
            'user_id' => $customer->id,
            'city_id' => $this->cityB->id,
            'district_id' => $this->districtB1->id,
            'title' => 'Bantuan Antar Barang di Solo',
            'description' => 'Tolong antarkan barang',
            'status' => 'waiting_worker',
            'price' => 50000,
            'latitude' => $this->cityB->latitude,
            'longitude' => $this->cityB->longitude,
        ]);

        $customer->refresh();
        $this->assertEquals($this->cityB->id, $help->city_id, 'Help operational city is City B');
        $this->assertEquals($this->districtB1->id, $help->district_id, 'Help operational district is District B1');
        $this->assertEquals($this->cityA->id, $customer->city_id, 'Customer profile city remains City A');
        $this->assertEquals($this->districtA1->id, $customer->district_id, 'Customer profile district remains District A1');
    }

    /**
     * Admin Visibility Regression: Admin for City A finds customer based on profile city A.
     */
    public function test_admin_visibility_regression_based_on_profile_identity(): void
    {
        $customer = User::create([
            'name' => 'Customer Managed by Admin A',
            'email' => 'customer.admin@example.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'customer',
            'status' => 'active',
            'verified' => true,
            'city_id' => $this->cityA->id,
            'district_id' => $this->districtA1->id,
        ]);

        // Create help in City B
        Help::create([
            'user_id' => $customer->id,
            'city_id' => $this->cityB->id,
            'district_id' => $this->districtB1->id,
            'title' => 'Bantuan di Solo',
            'description' => 'Test',
            'status' => 'waiting_worker',
            'price' => 25000,
            'latitude' => $this->cityB->latitude,
            'longitude' => $this->cityB->longitude,
        ]);

        // Query customers managed under City A administration
        $cityACustomers = User::where('role', 'customer')->where('city_id', $this->cityA->id)->get();
        $this->assertTrue($cityACustomers->contains('id', $customer->id), 'Customer remains managed by City A admin regardless of operational orders in City B');
    }
}
