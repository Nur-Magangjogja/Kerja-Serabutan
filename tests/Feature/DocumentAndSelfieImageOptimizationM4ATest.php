<?php

namespace Tests\Feature;

use App\Livewire\Customer\Profile\UpdatePhoto as CustomerUpdatePhoto;
use App\Livewire\Mitra\Profile\UpdatePhoto as MitraUpdatePhoto;
use App\Livewire\Mitra\Profile\VehicleProfile;
use App\Models\City;
use App\Models\District;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentAndSelfieImageOptimizationM4ATest extends TestCase
{
    use RefreshDatabase;

    protected City $city;
    protected District $district;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->city = City::create([
            'name'      => 'Kota Yogyakarta',
            'province'  => 'DI Yogyakarta',
            'latitude'  => -7.7956,
            'longitude' => 110.3695,
        ]);

        $this->district = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);
    }

    /*
     * -------------------------------------------------------------
     * 1. REGISTRATION SELFIE TESTS
     * -------------------------------------------------------------
     */

    public function test_selfie_registration_accepts_optimized_jpeg_under_1536kb(): void
    {
        $user = User::factory()->create([
            'role'              => 'customer',
            'status'            => 'active',
            'verified'          => false,
            'email_verified_at' => now(),
        ]);

        $registration = Registration::create([
            'uuid'           => (string) \Illuminate\Support\Str::uuid(),
            'name'           => $user->name,
            'email'          => strtolower(trim($user->email)),
            'role'           => 'customer',
            'status'         => 'in_progress',
            'ktp_photo_path' => 'ktp-photos/test_ktp.jpg',
        ]);

        $this->actingAs($user);
        session(['registration_uuid' => $registration->uuid]);

        // Simulated optimized selfie JPEG (800 KB)
        $file = UploadedFile::fake()->image('selfie_optimized.jpg', 1600, 1200)->size(800);

        Livewire::test('pages.auth.register-step3')
            ->set('selfie_photo', $file)
            ->assertHasNoErrors('selfie_photo');

        $registration->refresh();
        $this->assertNotNull($registration->selfie_photo_path);
        $this->assertTrue(Storage::disk('public')->exists($registration->selfie_photo_path));
        $this->assertStringStartsWith('selfie-photos/', $registration->selfie_photo_path);

        $user->refresh();
        $this->assertEquals($registration->selfie_photo_path, $user->selfie_photo);
    }

    public function test_selfie_registration_rejects_file_exceeding_1536kb(): void
    {
        $user = User::factory()->create([
            'role'              => 'customer',
            'status'            => 'active',
            'verified'          => false,
            'email_verified_at' => now(),
        ]);

        $registration = Registration::create([
            'uuid'           => (string) \Illuminate\Support\Str::uuid(),
            'name'           => $user->name,
            'email'          => strtolower(trim($user->email)),
            'role'           => 'customer',
            'status'         => 'in_progress',
            'ktp_photo_path' => 'ktp-photos/test_ktp.jpg',
        ]);

        $this->actingAs($user);
        session(['registration_uuid' => $registration->uuid]);

        // 2000 KB file exceeds 1536 KB limit
        $largeFile = UploadedFile::fake()->image('selfie_huge.jpg', 2000, 2000)->size(2000);

        Livewire::test('pages.auth.register-step3')
            ->set('selfie_photo', $largeFile)
            ->assertHasErrors(['selfie_photo' => 'max']);
    }

    public function test_selfie_registration_rejects_non_image_file(): void
    {
        $user = User::factory()->create([
            'role'              => 'customer',
            'status'            => 'active',
            'verified'          => false,
            'email_verified_at' => now(),
        ]);

        $registration = Registration::create([
            'uuid'           => (string) \Illuminate\Support\Str::uuid(),
            'name'           => $user->name,
            'email'          => strtolower(trim($user->email)),
            'role'           => 'customer',
            'status'         => 'in_progress',
            'ktp_photo_path' => 'ktp-photos/test_ktp.jpg',
        ]);

        $this->actingAs($user);
        session(['registration_uuid' => $registration->uuid]);

        $pdfFile = UploadedFile::fake()->create('selfie.pdf', 300, 'application/pdf');

        Livewire::test('pages.auth.register-step3')
            ->set('selfie_photo', $pdfFile)
            ->assertHasErrors(['selfie_photo' => 'image']);
    }

    public function test_selfie_registration_remains_required_to_complete_registration(): void
    {
        $user = User::factory()->create([
            'role'              => 'customer',
            'status'            => 'active',
            'verified'          => false,
            'email_verified_at' => now(),
        ]);

        $registration = Registration::create([
            'uuid'              => (string) \Illuminate\Support\Str::uuid(),
            'name'              => $user->name,
            'email'             => strtolower(trim($user->email)),
            'role'              => 'customer',
            'status'            => 'in_progress',
            'ktp_photo_path'    => 'ktp-photos/test_ktp.jpg',
            'selfie_photo_path' => null,
        ]);

        $this->actingAs($user);
        session(['registration_uuid' => $registration->uuid]);

        Livewire::test('pages.auth.register-step3')
            ->call('nextStep')
            ->assertHasErrors(['selfie_photo' => 'required']);
    }

    /*
     * -------------------------------------------------------------
     * 2. MITRA VEHICLE SIM TESTS
     * -------------------------------------------------------------
     */

    public function test_sim_initial_submission_requires_photo(): void
    {
        $mitra = User::factory()->create([
            'role'                 => 'mitra',
            'city_id'              => $this->city->id,
            'email_verified_at'    => now(),
            'vehicle_sim_photo'    => null,
            'vehicle_stnk_photo'   => null,
        ]);

        $this->actingAs($mitra);

        Livewire::test(VehicleProfile::class)
            ->set('vehicle_plate_number', 'AB 1234 CD')
            ->set('vehicle_sim_number', '12345678901234')
            ->set('vehicle_stnk_number', 'STNK1234')
            ->set('new_sim_photo', null)
            ->call('save')
            ->assertHasErrors(['new_sim_photo' => 'required']);
    }

    public function test_sim_update_remains_nullable_when_existing_photo_present(): void
    {
        $mitra = User::factory()->create([
            'role'                 => 'mitra',
            'city_id'              => $this->city->id,
            'email_verified_at'    => now(),
            'vehicle_plate_number' => 'AB 1234 CD',
            'vehicle_sim_number'   => '12345678901234',
            'vehicle_sim_photo'    => 'vehicles/sim/existing_sim.jpg',
            'vehicle_stnk_number'  => 'STNK1234',
            'vehicle_stnk_photo'   => 'vehicles/stnk/existing_stnk.jpg',
        ]);

        $this->actingAs($mitra);

        Livewire::test(VehicleProfile::class)
            ->set('vehicle_plate_number', 'AB 9999 ZZ')
            ->set('vehicle_sim_number', '12345678901234')
            ->set('vehicle_stnk_number', 'STNK1234')
            ->set('new_sim_photo', null)
            ->set('new_stnk_photo', null)
            ->call('save')
            ->assertHasNoErrors();

        $mitra->refresh();
        $this->assertEquals('AB 9999 ZZ', $mitra->vehicle_plate_number);
        $this->assertEquals('vehicles/sim/existing_sim.jpg', $mitra->vehicle_sim_photo);
    }

    public function test_sim_accepts_valid_image_under_2048kb(): void
    {
        $mitra = User::factory()->create([
            'role'                 => 'mitra',
            'city_id'              => $this->city->id,
            'email_verified_at'    => now(),
            'vehicle_sim_photo'    => null,
            'vehicle_stnk_photo'   => null,
        ]);

        $this->actingAs($mitra);

        $simFile = UploadedFile::fake()->image('sim_optimized.jpg', 1920, 1080)->size(1200);
        $stnkFile = UploadedFile::fake()->image('stnk_optimized.jpg', 1920, 1080)->size(1200);

        Livewire::test(VehicleProfile::class)
            ->set('vehicle_plate_number', 'AB 1234 CD')
            ->set('vehicle_sim_number', '12345678901234')
            ->set('vehicle_stnk_number', 'STNK1234')
            ->set('new_sim_photo', $simFile)
            ->set('new_stnk_photo', $stnkFile)
            ->call('save')
            ->assertHasNoErrors();

        $mitra->refresh();
        $this->assertNotNull($mitra->vehicle_sim_photo);
        $this->assertStringStartsWith('vehicles/sim/', $mitra->vehicle_sim_photo);
        $this->assertTrue(Storage::disk('public')->exists($mitra->vehicle_sim_photo));
    }

    public function test_sim_rejects_file_exceeding_2048kb(): void
    {
        $mitra = User::factory()->create([
            'role'                 => 'mitra',
            'city_id'              => $this->city->id,
            'email_verified_at'    => now(),
            'vehicle_sim_photo'    => null,
        ]);

        $this->actingAs($mitra);

        $largeSim = UploadedFile::fake()->image('huge_sim.jpg', 3000, 2000)->size(2500);

        Livewire::test(VehicleProfile::class)
            ->set('vehicle_plate_number', 'AB 1234 CD')
            ->set('vehicle_sim_number', '12345678901234')
            ->set('vehicle_stnk_number', 'STNK1234')
            ->set('new_sim_photo', $largeSim)
            ->call('save')
            ->assertHasErrors(['new_sim_photo' => 'max']);
    }

    public function test_sim_rejects_non_image_file(): void
    {
        $mitra = User::factory()->create([
            'role'                 => 'mitra',
            'city_id'              => $this->city->id,
            'email_verified_at'    => now(),
        ]);

        $this->actingAs($mitra);

        $pdfFile = UploadedFile::fake()->create('sim.pdf', 500, 'application/pdf');

        Livewire::test(VehicleProfile::class)
            ->set('vehicle_plate_number', 'AB 1234 CD')
            ->set('vehicle_sim_number', '12345678901234')
            ->set('vehicle_stnk_number', 'STNK1234')
            ->set('new_sim_photo', $pdfFile)
            ->call('save')
            ->assertHasErrors(['new_sim_photo' => 'image']);
    }

    /*
     * -------------------------------------------------------------
     * 3. MITRA VEHICLE STNK TESTS
     * -------------------------------------------------------------
     */

    public function test_stnk_initial_submission_requires_photo(): void
    {
        $mitra = User::factory()->create([
            'role'                 => 'mitra',
            'city_id'              => $this->city->id,
            'email_verified_at'    => now(),
            'vehicle_sim_photo'    => null,
            'vehicle_stnk_photo'   => null,
        ]);

        $this->actingAs($mitra);

        $simFile = UploadedFile::fake()->image('sim.jpg', 1000, 800)->size(500);

        Livewire::test(VehicleProfile::class)
            ->set('vehicle_plate_number', 'AB 1234 CD')
            ->set('vehicle_sim_number', '12345678901234')
            ->set('vehicle_stnk_number', 'STNK1234')
            ->set('new_sim_photo', $simFile)
            ->set('new_stnk_photo', null)
            ->call('save')
            ->assertHasErrors(['new_stnk_photo' => 'required']);
    }

    public function test_stnk_accepts_valid_image_under_2048kb(): void
    {
        $mitra = User::factory()->create([
            'role'                 => 'mitra',
            'city_id'              => $this->city->id,
            'email_verified_at'    => now(),
            'vehicle_sim_photo'    => null,
            'vehicle_stnk_photo'   => null,
        ]);

        $this->actingAs($mitra);

        $simFile = UploadedFile::fake()->image('sim_opt.jpg', 1920, 1080)->size(1100);
        $stnkFile = UploadedFile::fake()->image('stnk_opt.jpg', 1920, 1080)->size(1100);

        Livewire::test(VehicleProfile::class)
            ->set('vehicle_plate_number', 'AB 1234 CD')
            ->set('vehicle_sim_number', '12345678901234')
            ->set('vehicle_stnk_number', 'STNK1234')
            ->set('new_sim_photo', $simFile)
            ->set('new_stnk_photo', $stnkFile)
            ->call('save')
            ->assertHasNoErrors();

        $mitra->refresh();
        $this->assertNotNull($mitra->vehicle_stnk_photo);
        $this->assertStringStartsWith('vehicles/stnk/', $mitra->vehicle_stnk_photo);
        $this->assertTrue(Storage::disk('public')->exists($mitra->vehicle_stnk_photo));
    }

    public function test_stnk_rejects_file_exceeding_2048kb(): void
    {
        $mitra = User::factory()->create([
            'role'                 => 'mitra',
            'city_id'              => $this->city->id,
            'email_verified_at'    => now(),
        ]);

        $this->actingAs($mitra);

        $largeStnk = UploadedFile::fake()->image('huge_stnk.jpg', 3000, 2000)->size(2500);

        Livewire::test(VehicleProfile::class)
            ->set('vehicle_plate_number', 'AB 1234 CD')
            ->set('vehicle_sim_number', '12345678901234')
            ->set('vehicle_stnk_number', 'STNK1234')
            ->set('new_stnk_photo', $largeStnk)
            ->call('save')
            ->assertHasErrors(['new_stnk_photo' => 'max']);
    }

    public function test_stnk_rejects_non_image_file(): void
    {
        $mitra = User::factory()->create([
            'role'                 => 'mitra',
            'city_id'              => $this->city->id,
            'email_verified_at'    => now(),
        ]);

        $this->actingAs($mitra);

        $pdfFile = UploadedFile::fake()->create('stnk.pdf', 500, 'application/pdf');

        Livewire::test(VehicleProfile::class)
            ->set('vehicle_plate_number', 'AB 1234 CD')
            ->set('vehicle_sim_number', '12345678901234')
            ->set('vehicle_stnk_number', 'STNK1234')
            ->set('new_stnk_photo', $pdfFile)
            ->call('save')
            ->assertHasErrors(['new_stnk_photo' => 'image']);
    }

    /*
     * -------------------------------------------------------------
     * 4. CUSTOMER PROFILE AVATAR TESTS
     * -------------------------------------------------------------
     */

    public function test_customer_avatar_save_cropped_photo_accepts_valid_base64_under_1536kb(): void
    {
        $customer = User::factory()->create([
            'role'              => 'customer',
            'city_id'           => $this->city->id,
            'email_verified_at' => now(),
            'profile_photo'     => null,
        ]);

        $this->actingAs($customer);

        // Simulated canvas JPEG data URL (~50 KB)
        $rawImage = UploadedFile::fake()->image('avatar.jpg', 512, 512)->size(50)->getContent();
        $dataUrl = 'data:image/jpeg;base64,' . base64_encode($rawImage);

        Livewire::test(CustomerUpdatePhoto::class)
            ->call('saveCroppedPhoto', $dataUrl)
            ->assertHasNoErrors()
            ->assertRedirect(route('profile'));

        $customer->refresh();
        $this->assertNotNull($customer->profile_photo);
        $this->assertStringStartsWith('profile-photos/', $customer->profile_photo);
        $this->assertTrue(Storage::disk('public')->exists($customer->profile_photo));
    }

    public function test_customer_avatar_save_cropped_photo_rejects_payload_exceeding_1536kb(): void
    {
        $customer = User::factory()->create([
            'role'              => 'customer',
            'city_id'           => $this->city->id,
            'email_verified_at' => now(),
            'profile_photo'     => null,
        ]);

        $this->actingAs($customer);

        // Generate genuine JPEG binary > 1536 KB via valid comment segments
        $base = UploadedFile::fake()->image('avatar.jpg', 100, 100)->getContent();
        $comments = '';
        for ($i = 0; $i < 26; $i++) {
            $comments .= "\xFF\xFE" . pack('n', 65000) . str_repeat('A', 64998);
        }
        $oversizedData = substr($base, 0, 2) . $comments . substr($base, 2);
        $dataUrl = 'data:image/jpeg;base64,' . base64_encode($oversizedData);

        Livewire::test(CustomerUpdatePhoto::class)
            ->call('saveCroppedPhoto', $dataUrl)
            ->assertHasErrors(['photo']);
    }

    public function test_customer_avatar_save_cropped_photo_rejects_empty_data_url(): void
    {
        $customer = User::factory()->create([
            'role'              => 'customer',
            'city_id'           => $this->city->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($customer);

        Livewire::test(CustomerUpdatePhoto::class)
            ->call('saveCroppedPhoto', '')
            ->assertHasErrors(['photo']);
    }

    public function test_customer_avatar_save_cropped_photo_rejects_malformed_base64(): void
    {
        $customer = User::factory()->create([
            'role'              => 'customer',
            'city_id'           => $this->city->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($customer);

        $dataUrl = 'data:image/jpeg;base64,invalid!@#$characters';

        Livewire::test(CustomerUpdatePhoto::class)
            ->call('saveCroppedPhoto', $dataUrl)
            ->assertHasErrors(['photo']);
    }

    public function test_customer_avatar_save_cropped_photo_rejects_non_image_data_uri(): void
    {
        $customer = User::factory()->create([
            'role'              => 'customer',
            'city_id'           => $this->city->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($customer);

        $htmlPayload = '<h1>Test</h1>';
        $dataUrl = 'data:text/html;base64,' . base64_encode($htmlPayload);

        Livewire::test(CustomerUpdatePhoto::class)
            ->call('saveCroppedPhoto', $dataUrl)
            ->assertHasErrors(['photo']);
    }

    public function test_customer_avatar_save_cropped_photo_rejects_fake_jpeg_with_non_image_bytes(): void
    {
        $customer = User::factory()->create([
            'role'              => 'customer',
            'city_id'           => $this->city->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($customer);

        $fakeBytes = 'NOT_AN_IMAGE_BINARY_DATA';
        $dataUrl = 'data:image/jpeg;base64,' . base64_encode($fakeBytes);

        Livewire::test(CustomerUpdatePhoto::class)
            ->call('saveCroppedPhoto', $dataUrl)
            ->assertHasErrors(['photo']);
    }

    /*
     * -------------------------------------------------------------
     * 5. MITRA PROFILE AVATAR TESTS
     * -------------------------------------------------------------
     */

    public function test_mitra_avatar_save_cropped_photo_accepts_valid_base64_under_1536kb(): void
    {
        $mitra = User::factory()->create([
            'role'              => 'mitra',
            'city_id'           => $this->city->id,
            'email_verified_at' => now(),
            'profile_photo'     => null,
        ]);

        $this->actingAs($mitra);

        $rawImage = UploadedFile::fake()->image('avatar_mitra.jpg', 512, 512)->size(50)->getContent();
        $dataUrl = 'data:image/jpeg;base64,' . base64_encode($rawImage);

        Livewire::test(MitraUpdatePhoto::class)
            ->call('saveCroppedPhoto', $dataUrl)
            ->assertHasNoErrors()
            ->assertRedirect(route('mitra.profile'));

        $mitra->refresh();
        $this->assertNotNull($mitra->profile_photo);
        $this->assertStringStartsWith('profile-photos/', $mitra->profile_photo);
        $this->assertTrue(Storage::disk('public')->exists($mitra->profile_photo));
    }

    public function test_mitra_avatar_save_cropped_photo_rejects_payload_exceeding_1536kb(): void
    {
        $mitra = User::factory()->create([
            'role'              => 'mitra',
            'city_id'           => $this->city->id,
            'email_verified_at' => now(),
            'profile_photo'     => null,
        ]);

        $this->actingAs($mitra);

        // Generate genuine JPEG binary > 1536 KB via valid comment segments
        $base = UploadedFile::fake()->image('avatar_mitra.jpg', 100, 100)->getContent();
        $comments = '';
        for ($i = 0; $i < 26; $i++) {
            $comments .= "\xFF\xFE" . pack('n', 65000) . str_repeat('A', 64998);
        }
        $oversizedData = substr($base, 0, 2) . $comments . substr($base, 2);
        $dataUrl = 'data:image/jpeg;base64,' . base64_encode($oversizedData);

        Livewire::test(MitraUpdatePhoto::class)
            ->call('saveCroppedPhoto', $dataUrl)
            ->assertHasErrors(['photo']);
    }

    public function test_mitra_avatar_save_cropped_photo_rejects_empty_data_url(): void
    {
        $mitra = User::factory()->create([
            'role'              => 'mitra',
            'city_id'           => $this->city->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($mitra);

        Livewire::test(MitraUpdatePhoto::class)
            ->call('saveCroppedPhoto', '')
            ->assertHasErrors(['photo']);
    }

    public function test_mitra_avatar_save_cropped_photo_rejects_malformed_base64(): void
    {
        $mitra = User::factory()->create([
            'role'              => 'mitra',
            'city_id'           => $this->city->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($mitra);

        $dataUrl = 'data:image/jpeg;base64,invalid!@#$characters';

        Livewire::test(MitraUpdatePhoto::class)
            ->call('saveCroppedPhoto', $dataUrl)
            ->assertHasErrors(['photo']);
    }

    public function test_mitra_avatar_save_cropped_photo_rejects_non_image_data_uri(): void
    {
        $mitra = User::factory()->create([
            'role'              => 'mitra',
            'city_id'           => $this->city->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($mitra);

        $htmlPayload = '<h1>Test</h1>';
        $dataUrl = 'data:text/html;base64,' . base64_encode($htmlPayload);

        Livewire::test(MitraUpdatePhoto::class)
            ->call('saveCroppedPhoto', $dataUrl)
            ->assertHasErrors(['photo']);
    }

    public function test_mitra_avatar_save_cropped_photo_rejects_fake_jpeg_with_non_image_bytes(): void
    {
        $mitra = User::factory()->create([
            'role'              => 'mitra',
            'city_id'           => $this->city->id,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($mitra);

        $fakeBytes = 'NOT_AN_IMAGE_BINARY_DATA';
        $dataUrl = 'data:image/jpeg;base64,' . base64_encode($fakeBytes);

        Livewire::test(MitraUpdatePhoto::class)
            ->call('saveCroppedPhoto', $dataUrl)
            ->assertHasErrors(['photo']);
    }
}
