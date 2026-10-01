<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Verifications\Index as VerificationsIndex;
use App\Models\City;
use App\Models\District;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KtpVerificationLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected City $city;
    protected District $districtA;
    protected District $districtB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->city = City::create([
            'name'      => 'Yogyakarta',
            'province'  => 'DI Yogyakarta',
            'latitude'  => -7.7956,
            'longitude' => 110.3695,
            'is_active' => true,
        ]);

        $this->districtA = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);

        $this->districtB = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Gondomanan',
            'is_active' => true,
        ]);
    }

    public function test_superadmin_can_access_verifications_page(): void
    {
        $superAdmin = User::factory()->create([
            'role'     => 'super_admin',
            'status'   => 'active',
            'verified' => true,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('superadmin.verifications'));
        $response->assertOk();
        $response->assertSeeLivewire(VerificationsIndex::class);
    }

    public function test_admin_can_access_verifications_page(): void
    {
        $admin = User::factory()->create([
            'role'        => 'admin',
            'city_id'     => $this->city->id,
            'district_id' => $this->districtA->id,
            'status'      => 'active',
            'verified'    => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.verifications'));
        $response->assertOk();
        $response->assertSeeLivewire(VerificationsIndex::class);
    }

    public function test_livewire_can_filter_and_search_verifications(): void
    {
        $admin = User::factory()->create([
            'role'        => 'admin',
            'city_id'     => $this->city->id,
            'district_id' => $this->districtA->id,
            'status'      => 'active',
            'verified'    => true,
        ]);

        $reg1 = Registration::create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'full_name'   => 'Budi Santoso',
            'name'        => 'Budi Santoso',
            'email'       => 'budi@example.com',
            'phone'       => '081234567891',
            'role'        => 'mitra',
            'nik'         => '3201011111110001',
            'city_id'     => $this->city->id,
            'city'        => $this->city->name,
            'district_id' => $this->districtA->id,
            'kecamatan'   => $this->districtA->name,
            'status'      => 'pending_verification',
        ]);

        $reg2 = Registration::create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'full_name'   => 'Siti Rahma',
            'name'        => 'Siti Rahma',
            'email'       => 'siti@example.com',
            'phone'       => '081234567892',
            'role'        => 'customer',
            'nik'         => '3201012222220002',
            'city_id'     => $this->city->id,
            'city'        => $this->city->name,
            'district_id' => $this->districtA->id,
            'kecamatan'   => $this->districtA->name,
            'status'      => 'approved',
        ]);

        // Registration di wilayah B (Gondomanan) - harus terisolasi dari Admin wilayah A
        $reg3 = Registration::create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'full_name'   => 'Agus Priyono',
            'name'        => 'Agus Priyono',
            'email'       => 'agus@example.com',
            'phone'       => '081234567893',
            'role'        => 'mitra',
            'nik'         => '3201013333330003',
            'city_id'     => $this->city->id,
            'city'        => $this->city->name,
            'district_id' => $this->districtB->id,
            'kecamatan'   => $this->districtB->name,
            'status'      => 'pending_verification',
        ]);

        $this->actingAs($admin);

        Livewire::test(VerificationsIndex::class)
            // 1. Admin wilayah A melihat registrasi wilayah A, tetapi TIDAK melihat wilayah B
            ->assertSee('Budi Santoso')
            ->assertSee('Siti Rahma')
            ->assertDontSee('Agus Priyono')
            // 2. Search 'Budi' bekerja
            ->set('search', 'Budi')
            ->assertSee('Budi Santoso')
            ->assertDontSee('Siti Rahma')
            ->assertDontSee('Agus Priyono')
            // 3. Filter role bekerja
            ->set('search', '')
            ->set('roleFilter', 'customer')
            ->assertSee('Siti Rahma')
            ->assertDontSee('Budi Santoso')
            ->assertDontSee('Agus Priyono')
            // 4. Filter status bekerja
            ->set('roleFilter', '')
            ->set('statusFilter', 'pending')
            ->assertSee('Budi Santoso')
            ->assertDontSee('Siti Rahma')
            ->assertDontSee('Agus Priyono');
    }

    public function test_superadmin_can_view_registrations_across_all_districts(): void
    {
        $superAdmin = User::factory()->create([
            'role'     => 'super_admin',
            'status'   => 'active',
            'verified' => true,
        ]);

        $regA = Registration::create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'full_name'   => 'User Wilayah A',
            'name'        => 'User Wilayah A',
            'email'       => 'usera@example.com',
            'role'        => 'mitra',
            'city_id'     => $this->city->id,
            'district_id' => $this->districtA->id,
            'status'      => 'pending_verification',
        ]);

        $regB = Registration::create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'full_name'   => 'User Wilayah B',
            'name'        => 'User Wilayah B',
            'email'       => 'userb@example.com',
            'role'        => 'customer',
            'city_id'     => $this->city->id,
            'district_id' => $this->districtB->id,
            'status'      => 'pending_verification',
        ]);

        $this->actingAs($superAdmin);

        Livewire::test(VerificationsIndex::class)
            ->assertSee('User Wilayah A')
            ->assertSee('User Wilayah B');
    }

    public function test_admin_cannot_view_or_approve_registration_from_other_district(): void
    {
        $adminA = User::factory()->create([
            'role'        => 'admin',
            'city_id'     => $this->city->id,
            'district_id' => $this->districtA->id,
            'status'      => 'active',
            'verified'    => true,
        ]);

        $regB = Registration::create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'full_name'   => 'User Wilayah B',
            'name'        => 'User Wilayah B',
            'email'       => 'userb@example.com',
            'role'        => 'mitra',
            'city_id'     => $this->city->id,
            'district_id' => $this->districtB->id,
            'status'      => 'pending_verification',
        ]);

        $this->actingAs($adminA);

        Livewire::test(VerificationsIndex::class)
            ->call('approveKtp', $regB->id)
            ->assertSee('Registrasi tidak ditemukan atau berada di luar wilayah wewenang Anda.');

        $this->assertSame('pending_verification', $regB->fresh()->status);
    }

    public function test_livewire_can_approve_verification(): void
    {
        $superAdmin = User::factory()->create([
            'role'     => 'super_admin',
            'status'   => 'active',
            'verified' => true,
        ]);

        $user = User::factory()->create([
            'email'    => 'pendaftar@example.com',
            'status'   => 'inactive',
            'verified' => false,
            'nik'      => null,
        ]);

        $reg = Registration::create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'full_name'   => 'Pendaftar Baru',
            'name'        => 'Pendaftar Baru',
            'email'       => 'pendaftar@example.com',
            'phone'       => '081234567890',
            'role'        => 'mitra',
            'nik'         => '3201019999990001',
            'city_id'     => $this->city->id,
            'city'        => $this->city->name,
            'district_id' => $this->districtA->id,
            'rt'          => 1,
            'rw'          => 2,
            'kelurahan'   => 'Gondokusuman',
            'kecamatan'   => 'Kotabaru',
            'province'    => 'DI Yogyakarta',
            'status'      => 'pending_verification',
        ]);

        $this->actingAs($superAdmin);

        Livewire::test(VerificationsIndex::class)
            ->call('approveKtp', $reg->id)
            ->assertSet('showModal', false);

        $this->assertSame('approved', $reg->fresh()->status);
        $user->refresh();
        $this->assertSame('active', $user->status);
        $this->assertTrue((bool)$user->verified);
        $this->assertSame('3201019999990001', $user->nik);
    }

    public function test_livewire_can_reject_verification_with_reason(): void
    {
        $superAdmin = User::factory()->create([
            'role'     => 'super_admin',
            'status'   => 'active',
            'verified' => true,
        ]);

        $user = User::factory()->create([
            'email'    => 'ditolak@example.com',
            'status'   => 'inactive',
            'verified' => false,
        ]);

        $reg = Registration::create([
            'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            'full_name'   => 'Pendaftar Ditolak',
            'name'        => 'Pendaftar Ditolak',
            'email'       => 'ditolak@example.com',
            'phone'       => '081234567895',
            'role'        => 'customer',
            'nik'         => '3201018888880001',
            'city_id'     => $this->city->id,
            'city'        => $this->city->name,
            'district_id' => $this->districtA->id,
            'status'      => 'pending_verification',
        ]);

        $this->actingAs($superAdmin);

        Livewire::test(VerificationsIndex::class)
            ->call('openRejectModal', $reg->id)
            ->assertSet('showRejectModal', true)
            ->set('rejectReason', 'Foto KTP buram dan tidak terbaca.')
            ->call('confirmReject')
            ->assertSet('showRejectModal', false);

        $this->assertSame('rejected', $reg->fresh()->status);
        $this->assertSame('Foto KTP buram dan tidak terbaca.', $reg->fresh()->rejection_reason);
        $user->refresh();
        $this->assertSame('inactive', $user->status);
        $this->assertFalse((bool)$user->verified);
    }
}
