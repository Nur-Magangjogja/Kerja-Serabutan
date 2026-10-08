<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\Registration;
use App\Models\User;
use Database\Seeders\CitySeeder;
use Database\Seeders\ProvinceSeeder;
use Database\Seeders\RegistrationsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederRegistrationStateAlignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ProvinceSeeder::class);
        $this->seed(CitySeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(RegistrationsSeeder::class);
    }

    /**
     * S1 — Seeded Customer memiliki Registration state canonical completed.
     */
    public function test_s1_seeded_customer_has_canonical_registration_complete_state(): void
    {
        $customer = User::where('email', 'customer@sayabantu.com')->firstOrFail();

        // 1. User Table assertions
        $this->assertSame('customer', $customer->role);
        $this->assertSame('active', $customer->status);
        $this->assertTrue((bool) $customer->verified);
        $this->assertNotNull($customer->email_verified_at);
        $this->assertNotEmpty($customer->nik);
        $this->assertSame(16, strlen($customer->nik));
        $this->assertMatchesRegularExpression('/^[0-9]{16}$/', $customer->nik);
        $this->assertNotEmpty($customer->ktp_photo);
        $this->assertNotEmpty($customer->ktp_path);
        $this->assertSame($customer->ktp_photo, $customer->ktp_path);
        $this->assertNotEmpty($customer->selfie_photo);
        $this->assertNotEmpty($customer->place_of_birth);
        $this->assertNotEmpty($customer->date_of_birth);
        $this->assertNotNull($customer->rt);
        $this->assertNotNull($customer->rw);
        $this->assertNotEmpty($customer->kelurahan);

        // 2. Registration Table assertions
        $registration = Registration::where('email', $customer->email)->firstOrFail();
        $this->assertSame('approved', $registration->status);
        $this->assertSame('customer', $registration->role);
        $this->assertSame($customer->nik, $registration->nik);
        $this->assertSame($customer->name, $registration->full_name);
        $this->assertSame($customer->phone, $registration->phone);
        $this->assertSame($customer->ktp_photo, $registration->ktp_photo_path);
        $this->assertSame($customer->selfie_photo, $registration->selfie_photo_path);
        $this->assertSame($customer->city_id, $registration->city_id);
        $this->assertSame($customer->district_id, $registration->district_id);
    }

    /**
     * S2 — Seeded Customer login tidak redirect Step 1 dan langsung dapat mengakses dashboard.
     */
    public function test_s2_seeded_customer_login_does_not_redirect_step1(): void
    {
        $customer = User::where('email', 'customer@sayabantu.com')->firstOrFail();

        $response = $this->actingAs($customer)->get(route('customer.dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('register/step1');

        // Test root landing redirect as authenticated customer
        $rootResponse = $this->actingAs($customer)->get(route('home'));
        $rootResponse->assertRedirect(route('dashboard'));

        $dashboardResponse = $this->actingAs($customer)->get(route('dashboard'));
        $dashboardResponse->assertRedirect(route('customer.dashboard'));
    }

    /**
     * S3 — Seeded Mitra memiliki Registration state canonical completed.
     */
    public function test_s3_seeded_mitra_has_canonical_registration_complete_state(): void
    {
        $mitra = User::where('email', 'mitra@sayabantu.com')->firstOrFail();

        // 1. User Table assertions
        $this->assertSame('mitra', $mitra->role);
        $this->assertSame('active', $mitra->status);
        $this->assertTrue((bool) $mitra->verified);
        $this->assertNotNull($mitra->email_verified_at);
        $this->assertNotEmpty($mitra->nik);
        $this->assertSame(16, strlen($mitra->nik));
        $this->assertMatchesRegularExpression('/^[0-9]{16}$/', $mitra->nik);
        $this->assertNotEmpty($mitra->ktp_photo);
        $this->assertNotEmpty($mitra->ktp_path);
        $this->assertSame($mitra->ktp_photo, $mitra->ktp_path);
        $this->assertNotEmpty($mitra->selfie_photo);
        $this->assertNotEmpty($mitra->occupation);

        // 2. Registration Table assertions
        $registration = Registration::where('email', $mitra->email)->firstOrFail();
        $this->assertSame('approved', $registration->status);
        $this->assertSame('mitra', $registration->role);
        $this->assertSame($mitra->nik, $registration->nik);
        $this->assertSame($mitra->name, $registration->full_name);
        $this->assertSame($mitra->ktp_photo, $registration->ktp_photo_path);
        $this->assertSame($mitra->selfie_photo, $registration->selfie_photo_path);
        $this->assertSame($mitra->city_id, $registration->city_id);
        $this->assertSame($mitra->district_id, $registration->district_id);
    }

    /**
     * S4 — Seeded Mitra login tidak redirect Step 1 dan langsung dapat mengakses mitra dashboard.
     */
    public function test_s4_seeded_mitra_login_does_not_redirect_step1(): void
    {
        $mitra = User::where('email', 'mitra@sayabantu.com')->firstOrFail();

        $response = $this->actingAs($mitra)->get(route('mitra.dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('register/step1');

        // Test root landing redirect as authenticated mitra
        $rootResponse = $this->actingAs($mitra)->get(route('home'));
        $rootResponse->assertRedirect(route('dashboard'));

        $dashboardResponse = $this->actingAs($mitra)->get(route('dashboard'));
        $dashboardResponse->assertRedirect(route('mitra.dashboard'));
    }

    /**
     * S5 & S6 — Seeded Customer dan Mitra Profile Territory valid (G2/G3).
     */
    public function test_s5_and_s6_seeded_customer_and_mitra_profile_territory_valid(): void
    {
        $users = User::whereIn('role', ['customer', 'mitra'])->get();

        $this->assertGreaterThan(0, $users->count());

        foreach ($users as $user) {
            $this->assertNotNull($user->city_id, "User {$user->email} must have city_id");
            $this->assertNotNull($user->district_id, "User {$user->email} must have district_id");

            $city = City::find($user->city_id);
            $this->assertNotNull($city, "City ID {$user->city_id} must exist in database");

            $district = District::find($user->district_id);
            $this->assertNotNull($district, "District ID {$user->district_id} must exist in database");

            // Strict hierarchy check: District must belong to City
            $this->assertSame(
                (int) $city->id,
                (int) $district->city_id,
                "District {$district->name} must belong to City {$city->name} for user {$user->email}"
            );

            // Territory textual naming consistency
            $this->assertSame($city->name, $user->city);
            $this->assertSame($district->name, $user->kecamatan);
            $this->assertSame($city->province, $user->province);
        }
    }

    /**
     * S7 — Normal unverified real registration tetap diarahkan ke verification/registration flow.
     */
    public function test_s7_normal_unverified_real_registration_guarded_strictly(): void
    {
        // 1. Real user with unverified email
        $unverifiedEmailUser = User::create([
            'name'              => 'Unverified Real User',
            'email'             => 'unverified.real@gmail.com',
            'password'          => Hash::make('password123'),
            'role'              => 'customer',
            'status'            => 'inactive',
            'verified'          => false,
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($unverifiedEmailUser)->get(route('customer.dashboard'));
        $response->assertRedirect(route('verification.notice'));

        // 2. Real user with verified email but no KTP submitted yet (Step 1 incomplete)
        $unverifiedEmailUser->email_verified_at = now();
        $unverifiedEmailUser->save();

        $response2 = $this->actingAs($unverifiedEmailUser)->get(route('customer.dashboard'));
        $response2->assertRedirect(route('register.step1'));
    }

    /**
     * S8 — Pending seeded/test registration tetap tidak dapat melewati verification.
     */
    public function test_s8_pending_seeded_mitra_cannot_bypass_verification(): void
    {
        $pendingMitra = User::where('email', 'mitra.pending@sayabantu.com')->firstOrFail();

        // 1. Ensure state matches Step 4 submitted awaiting admin review
        $this->assertSame('inactive', $pendingMitra->status);
        $this->assertFalse((bool) $pendingMitra->verified);
        $this->assertNotNull($pendingMitra->email_verified_at);
        $this->assertNotEmpty($pendingMitra->nik);
        $this->assertNotEmpty($pendingMitra->ktp_photo);
        $this->assertNotEmpty($pendingMitra->selfie_photo);

        $registration = Registration::where('email', $pendingMitra->email)->firstOrFail();
        $this->assertSame('pending_verification', $registration->status);

        // 2. Attempting to access operational dashboard is blocked and redirected to waiting screen
        $response = $this->actingAs($pendingMitra)->get(route('mitra.dashboard'));
        $response->assertRedirect(route('registration.success'));

        // 3. Waiting screen itself loads successfully with HTTP 200
        $successResponse = $this->actingAs($pendingMitra)->get(route('registration.success'));
        $successResponse->assertStatus(200);

        // 4. Must NOT be redirected to register.step1
        $this->assertFalse($response->isRedirect(route('register.step1')));
    }

    /**
     * S9 — Verifikasi ketiadaan infinite redirect loop antara dashboard dan register.step1.
     */
    public function test_redirect_loop_prevention_explicit_route_step_follow(): void
    {
        // 1. Customer loop verification
        $customer = User::where('email', 'customer@sayabantu.com')->firstOrFail();

        $dashboardRes = $this->actingAs($customer)->get(route('customer.dashboard'));
        $dashboardRes->assertStatus(200);

        // If customer accesses register.step1, PreventAdminRegistrationAccess redirects to customer.dashboard
        $step1Res = $this->actingAs($customer)->get(route('register.step1'));
        $step1Res->assertRedirect(route('customer.dashboard'));

        // Following the redirect lands on 200 OK dashboard (terminates immediately without loop)
        $followRes = $this->actingAs($customer)->get($step1Res->headers->get('Location'));
        $followRes->assertStatus(200);

        // 2. Mitra loop verification
        $mitra = User::where('email', 'mitra@sayabantu.com')->firstOrFail();

        $mitraDashboardRes = $this->actingAs($mitra)->get(route('mitra.dashboard'));
        $mitraDashboardRes->assertStatus(200);

        $mitraStep1Res = $this->actingAs($mitra)->get(route('register.step1'));
        $mitraStep1Res->assertRedirect(route('mitra.dashboard'));

        $mitraFollowRes = $this->actingAs($mitra)->get($mitraStep1Res->headers->get('Location'));
        $mitraFollowRes->assertStatus(200);
    }

    /**
     * S10 — Seeder Idempotency: rerunning seeders does not produce duplicates or state drift.
     */
    public function test_seeder_is_fully_idempotent_without_duplicates_or_drift(): void
    {
        $initialUserCount = User::count();
        $initialRegCount = Registration::count();

        // Rerun UserSeeder and RegistrationsSeeder
        $this->seed(UserSeeder::class);
        $this->seed(RegistrationsSeeder::class);

        $this->assertSame($initialUserCount, User::count(), 'User count must remain identical upon rerun');
        $this->assertSame($initialRegCount, Registration::count(), 'Registration count must remain identical upon rerun');

        // Verify customer and mitra state remains canonical
        $customer = User::where('email', 'customer@sayabantu.com')->firstOrFail();
        $this->assertSame('active', $customer->status);
        $this->assertTrue((bool) $customer->verified);
        $this->assertNotEmpty($customer->ktp_photo);

        $customerReg = Registration::where('email', 'customer@sayabantu.com')->firstOrFail();
        $this->assertSame('approved', $customerReg->status);
        $this->assertSame($customer->ktp_photo, $customerReg->ktp_photo_path);

        $mitra = User::where('email', 'mitra@sayabantu.com')->firstOrFail();
        $this->assertSame('active', $mitra->status);
        $this->assertTrue((bool) $mitra->verified);
        $this->assertNotEmpty($mitra->ktp_photo);

        $mitraReg = Registration::where('email', 'mitra@sayabantu.com')->firstOrFail();
        $this->assertSame('approved', $mitraReg->status);
        $this->assertSame($mitra->ktp_photo, $mitraReg->ktp_photo_path);
    }

    /**
     * S11 — Fresh Environment Asset Creation:
     * In an isolated storage where sample assets do not pre-exist,
     * running UserSeeder deterministically installs the synthetic fixtures.
     */
    public function test_fresh_environment_asset_creation_in_isolated_storage(): void
    {
        // 1. Isolate public storage using fake disk
        \Illuminate\Support\Facades\Storage::fake('public');
        $fakeDisk = \Illuminate\Support\Facades\Storage::disk('public');

        // Confirm absence of sample assets on the clean fake disk
        $this->assertFalse($fakeDisk->exists('ktp-photos/sample_ktp.jpg'));
        $this->assertFalse($fakeDisk->exists('selfie-photos/sample_selfie.png'));

        // 2. Run UserSeeder & RegistrationsSeeder
        $this->seed(UserSeeder::class);
        $this->seed(RegistrationsSeeder::class);

        // 3. Verify synthetic fixtures were deterministically installed to the storage disk
        $this->assertTrue($fakeDisk->exists('ktp-photos/sample_ktp.jpg'), 'sample_ktp.jpg must be installed on fake public disk');
        $this->assertTrue($fakeDisk->exists('selfie-photos/sample_selfie.png'), 'sample_selfie.png must be installed on fake public disk');
        $this->assertGreaterThan(0, strlen($fakeDisk->get('ktp-photos/sample_ktp.jpg')));
        $this->assertGreaterThan(0, strlen($fakeDisk->get('selfie-photos/sample_selfie.png')));

        // 4. Verify User paths match
        $customer = User::where('email', 'customer@sayabantu.com')->firstOrFail();
        $this->assertSame('ktp-photos/sample_ktp.jpg', $customer->ktp_photo);
        $this->assertSame('selfie-photos/sample_selfie.png', $customer->selfie_photo);

        // 5. Verify Registration paths match User
        $customerReg = Registration::where('email', $customer->email)->firstOrFail();
        $this->assertSame($customer->ktp_photo, $customerReg->ktp_photo_path);
        $this->assertSame($customer->selfie_photo, $customerReg->selfie_photo_path);

        // 6. Login still succeeds
        $response = $this->actingAs($customer)->get(route('customer.dashboard'));
        $response->assertStatus(200);
    }
}
