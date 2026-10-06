<?php

namespace Tests\Feature;

use App\Livewire\Mitra\Helps\HelpDetail;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\PartnerOnlineState;
use App\Models\Registration;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PilotImageOptimizationM3Test extends TestCase
{
    use RefreshDatabase;

    protected User $mitra;
    protected User $customer;
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

        $this->customer = User::factory()->create([
            'role'              => 'customer',
            'city_id'           => $this->city->id,
            'email_verified_at' => now(),
        ]);
        UserBalance::create(['user_id' => $this->customer->id, 'balance' => 1000000]);

        $this->mitra = User::factory()->create([
            'role'              => 'mitra',
            'city_id'           => $this->city->id,
            'district_id'       => $this->district->id,
            'email_verified_at' => now(),
        ]);
        UserBalance::create(['user_id' => $this->mitra->id, 'balance' => 0]);

        PartnerOnlineState::updateOrCreate(
            ['user_id' => $this->mitra->id],
            [
                'is_online'    => true,
                'is_busy'      => true,
                'latitude'     => -7.7960,
                'longitude'    => 110.3700,
                'last_seen_at' => now(),
            ]
        );
    }

    /**
     * KTP PILOT TESTS
     */
    public function test_ktp_pilot_accepts_valid_optimized_jpeg_under_2mb(): void
    {
        $user = User::factory()->create([
            'role'              => 'customer',
            'status'            => 'active',
            'verified'          => false,
            'email_verified_at' => now(),
        ]);

        $registration = Registration::create([
            'uuid'   => (string) \Illuminate\Support\Str::uuid(),
            'name'   => $user->name,
            'email'  => strtolower(trim($user->email)),
            'role'   => 'customer',
            'status' => 'in_progress',
        ]);

        $this->actingAs($user);
        session(['registration_uuid' => $registration->uuid]);

        // Simulated optimized JPEG (e.g. 800 KB)
        $file = UploadedFile::fake()->image('ktp_optimized.jpg', 1200, 800)->size(800);

        $component = Livewire::test('pages.auth.register-step2')
            ->set('ktp_photo', $file)
            ->assertHasNoErrors('ktp_photo');

        $registration->refresh();
        $this->assertNotNull($registration->ktp_photo_path);
        $this->assertTrue(Storage::disk('public')->exists($registration->ktp_photo_path));
        $this->assertStringStartsWith('ktp-photos/', $registration->ktp_photo_path);

        $user->refresh();
        $this->assertEquals($registration->ktp_photo_path, $user->ktp_photo);
    }

    public function test_ktp_pilot_rejects_file_exceeding_2mb_limit(): void
    {
        $user = User::factory()->create([
            'role'              => 'customer',
            'status'            => 'active',
            'verified'          => false,
            'email_verified_at' => now(),
        ]);

        $registration = Registration::create([
            'uuid'   => (string) \Illuminate\Support\Str::uuid(),
            'name'   => $user->name,
            'email'  => strtolower(trim($user->email)),
            'role'   => 'customer',
            'status' => 'in_progress',
        ]);

        $this->actingAs($user);
        session(['registration_uuid' => $registration->uuid]);

        // File exceeding 2048 KB (e.g. 2500 KB)
        $largeFile = UploadedFile::fake()->image('ktp_huge.jpg', 3000, 2000)->size(2500);

        Livewire::test('pages.auth.register-step2')
            ->set('ktp_photo', $largeFile)
            ->assertHasErrors(['ktp_photo' => 'max']);
    }

    public function test_ktp_pilot_rejects_non_image_file(): void
    {
        $user = User::factory()->create([
            'role'              => 'customer',
            'status'            => 'active',
            'verified'          => false,
            'email_verified_at' => now(),
        ]);

        $registration = Registration::create([
            'uuid'   => (string) \Illuminate\Support\Str::uuid(),
            'name'   => $user->name,
            'email'  => strtolower(trim($user->email)),
            'role'   => 'customer',
            'status' => 'in_progress',
        ]);

        $this->actingAs($user);
        session(['registration_uuid' => $registration->uuid]);

        $pdfFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        Livewire::test('pages.auth.register-step2')
            ->set('ktp_photo', $pdfFile)
            ->assertHasErrors(['ktp_photo' => 'image']);
    }

    public function test_ktp_pilot_requires_ktp_photo_to_proceed_to_step3(): void
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
            'ktp_photo_path' => null,
        ]);

        $this->actingAs($user);
        session(['registration_uuid' => $registration->uuid]);

        Livewire::test('pages.auth.register-step2')
            ->call('nextStep')
            ->assertHasErrors(['ktp_photo' => 'required']);
    }

    /**
     * CANCELLATION EVIDENCE PILOT TESTS
     */
    public function test_cancellation_pilot_accepts_valid_optimized_evidence_under_1536kb(): void
    {
        $this->actingAs($this->mitra);

        $help = Help::create([
            'order_id'      => 'HLP-' . strtoupper(uniqid()),
            'user_id'       => $this->customer->id,
            'mitra_id'      => $this->mitra->id,
            'city_id'       => $this->city->id,
            'district_id'   => $this->district->id,
            'title'         => 'Bantuan Lampu Korslet',
            'description'   => 'Kabel terbakar',
            'location'      => 'Jl. Malioboro No. 15',
            'service_type'  => 'on_site_service',
            'status'        => Help::STATUS_TAKEN,
            'taken_at'      => now(),
            'amount'        => 60000,
            'escrow_status' => Help::ESCROW_STATUS_HELD,
        ]);

        // Simulated optimized JPEG (e.g. 500 KB)
        $photo = UploadedFile::fake()->image('evidence_optimized.jpg', 1600, 1200)->size(500);

        Livewire::test(HelpDetail::class, ['id' => $help->id])
            ->call('openPartnerCancelModal')
            ->set('partnerCancelReason', 'Kendaraan Bermasalah / Mogok / Ban Bocor')
            ->set('partnerCancelNotes', 'Ban bocor terkena paku di jalan')
            ->set('cancel_evidence_photo', $photo)
            ->call('requestPartnerCancel')
            ->assertHasNoErrors()
            ->assertRedirect(route('mitra.dashboard'));

        $cancelRequest = HelpCancelRequest::where('help_id', $help->id)->first();
        $this->assertNotNull($cancelRequest);
        $this->assertNotNull($cancelRequest->evidence_photo);
        $this->assertStringStartsWith('cancel_evidence/', $cancelRequest->evidence_photo);
        $this->assertTrue(Storage::disk('public')->exists($cancelRequest->evidence_photo));

        $help->refresh();
        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);
        $this->assertNull($help->mitra_id);
    }

    public function test_cancellation_pilot_rejects_evidence_exceeding_1536kb_limit(): void
    {
        $this->actingAs($this->mitra);

        $help = Help::create([
            'order_id'      => 'HLP-' . strtoupper(uniqid()),
            'user_id'       => $this->customer->id,
            'mitra_id'      => $this->mitra->id,
            'city_id'       => $this->city->id,
            'district_id'   => $this->district->id,
            'title'         => 'Bantuan Lampu Korslet',
            'description'   => 'Kabel terbakar',
            'location'      => 'Jl. Malioboro No. 15',
            'service_type'  => 'on_site_service',
            'status'        => Help::STATUS_TAKEN,
            'taken_at'      => now(),
            'amount'        => 60000,
            'escrow_status' => Help::ESCROW_STATUS_HELD,
        ]);

        // File exceeding 1536 KB (e.g. 2000 KB)
        $largePhoto = UploadedFile::fake()->image('evidence_large.jpg', 2400, 1800)->size(2000);

        Livewire::test(HelpDetail::class, ['id' => $help->id])
            ->call('openPartnerCancelModal')
            ->set('partnerCancelReason', 'Kendaraan Bermasalah / Mogok / Ban Bocor')
            ->set('partnerCancelNotes', 'Ban bocor terkena paku di jalan')
            ->set('cancel_evidence_photo', $largePhoto)
            ->call('requestPartnerCancel')
            ->assertHasErrors(['cancel_evidence_photo' => 'max']);
    }

    public function test_cancellation_pilot_requires_evidence_photo(): void
    {
        $this->actingAs($this->mitra);

        $help = Help::create([
            'order_id'      => 'HLP-' . strtoupper(uniqid()),
            'user_id'       => $this->customer->id,
            'mitra_id'      => $this->mitra->id,
            'city_id'       => $this->city->id,
            'district_id'   => $this->district->id,
            'title'         => 'Bantuan Lampu Korslet',
            'description'   => 'Kabel terbakar',
            'location'      => 'Jl. Malioboro No. 15',
            'service_type'  => 'on_site_service',
            'status'        => Help::STATUS_TAKEN,
            'taken_at'      => now(),
            'amount'        => 60000,
            'escrow_status' => Help::ESCROW_STATUS_HELD,
        ]);

        Livewire::test(HelpDetail::class, ['id' => $help->id])
            ->call('openPartnerCancelModal')
            ->set('partnerCancelReason', 'Kendaraan Bermasalah / Mogok / Ban Bocor')
            ->set('partnerCancelNotes', 'Ban bocor di jalan')
            ->set('cancel_evidence_photo', null)
            ->call('requestPartnerCancel')
            ->assertHasErrors(['cancel_evidence_photo' => 'required']);
    }

    public function test_cancellation_pilot_rejects_non_image_file(): void
    {
        $this->actingAs($this->mitra);

        $help = Help::create([
            'order_id'      => 'HLP-' . strtoupper(uniqid()),
            'user_id'       => $this->customer->id,
            'mitra_id'      => $this->mitra->id,
            'city_id'       => $this->city->id,
            'district_id'   => $this->district->id,
            'title'         => 'Bantuan Lampu Korslet',
            'description'   => 'Kabel terbakar',
            'location'      => 'Jl. Malioboro No. 15',
            'service_type'  => 'on_site_service',
            'status'        => Help::STATUS_TAKEN,
            'taken_at'      => now(),
            'amount'        => 60000,
            'escrow_status' => Help::ESCROW_STATUS_HELD,
        ]);

        $pdfFile = UploadedFile::fake()->create('proof.pdf', 300, 'application/pdf');

        Livewire::test(HelpDetail::class, ['id' => $help->id])
            ->call('openPartnerCancelModal')
            ->set('partnerCancelReason', 'Kendaraan Bermasalah / Mogok / Ban Bocor')
            ->set('partnerCancelNotes', 'Ban bocor di jalan')
            ->set('cancel_evidence_photo', $pdfFile)
            ->call('requestPartnerCancel')
            ->assertHasErrors(['cancel_evidence_photo' => 'image']);
    }
}
