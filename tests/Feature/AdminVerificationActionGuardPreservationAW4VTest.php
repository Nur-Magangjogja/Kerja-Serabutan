<?php

namespace Tests\Feature;

use App\Livewire\Admin\Verifications\Index as AdminVerifications;
use App\Models\City;
use App\Models\District;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class AdminVerificationActionGuardPreservationAW4VTest extends TestCase
{
    use RefreshDatabase;

    protected City $citySleman;
    protected City $cityYogya;

    protected District $distNgaglik;
    protected District $distDepok;
    protected District $distGondomanan;
    protected District $distDanurejan;

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
    }

    protected function createAdminWithDistricts(array $districtIds): User
    {
        $admin = User::factory()->create([
            'name'        => 'Admin Wilayah Test',
            'email'       => 'admin.' . uniqid() . '@sayabantu.test',
            'role'        => 'admin',
            'status'      => 'active',
            'verified'    => true,
            'city_id'     => $this->citySleman->id,
            'password'    => Hash::make('password123'),
        ]);

        if (!empty($districtIds)) {
            $admin->managedDistricts()->sync($districtIds);
        }

        return $admin;
    }

    protected function createKtpRegistration(District $district, string $name = 'Pendaftar KTP'): Registration
    {
        $user = User::factory()->create([
            'name'        => $name,
            'email'       => 'ktp.' . uniqid() . '@sayabantu.test',
            'role'        => 'mitra',
            'city_id'     => $district->city_id,
            'district_id' => $district->id,
            'verified'    => false,
            'status'      => 'inactive',
        ]);

        return Registration::create([
            'uuid'        => (string) Str::uuid(),
            'user_id'     => $user->id,
            'name'        => $name,
            'full_name'   => $name,
            'email'       => $user->email,
            'phone'       => '0812' . rand(10000000, 99999999),
            'nik'         => '3404' . rand(100000000000, 999999999999),
            'city_id'     => $district->city_id,
            'district_id' => $district->id,
            'status'      => 'pending_verification',
            'role'        => 'mitra',
        ]);
    }

    protected function createVehicleMitra(District $district, string $name = 'Mitra Kendaraan'): User
    {
        return User::factory()->create([
            'name'                        => $name,
            'email'                       => 'vehicle.' . uniqid() . '@sayabantu.test',
            'role'                        => 'mitra',
            'city_id'                     => $district->city_id,
            'district_id'                 => $district->id,
            'verified'                    => true,
            'status'                      => 'active',
            'vehicle_verification_status' => 'pending',
            'vehicle_plate_number'        => 'AB ' . rand(1000, 9999) . ' XX',
        ]);
    }

    /**
     * Case A — Exact assigned district:
     * Admin assigned to Ngaglik. Target in Ngaglik.
     * Actions: view, approve, reject ALLOWED.
     */
    public function test_case_a_exact_assigned_district_allowed(): void
    {
        $admin = $this->createAdminWithDistricts([$this->distNgaglik->id]);

        // 1. KTP
        $regNgaglik = $this->createKtpRegistration($this->distNgaglik, 'Target KTP Ngaglik');

        // View allowed
        $ktpComp = Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('viewKtp', $regNgaglik->id);
        $this->assertTrue($ktpComp->get('showModal'));
        $this->assertEquals($regNgaglik->id, $ktpComp->get('selected')->id);

        // Approve allowed
        $ktpCompApprove = Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('approveKtp', $regNgaglik->id)
            ->assertSee('Registrasi berhasil disetujui.');
        $this->assertEquals('approved', $regNgaglik->fresh()->status);

        // 2. Vehicle
        $mitraNgaglik = $this->createVehicleMitra($this->distNgaglik, 'Target Vehicle Ngaglik');

        // View allowed
        $vehComp = Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->set('activeTab', 'vehicle')
            ->call('viewVehicle', $mitraNgaglik->id);
        $this->assertTrue($vehComp->get('showVehicleModal'));
        $this->assertEquals($mitraNgaglik->id, $vehComp->get('selectedVehicleUser')->id);

        // Approve allowed
        $vehCompApprove = Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->set('activeTab', 'vehicle')
            ->call('approveVehicle', $mitraNgaglik->id)
            ->assertSee('berhasil disetujui');
        $this->assertEquals('verified', $mitraNgaglik->fresh()->vehicle_verification_status);
    }

    /**
     * Case B — Sibling district:
     * Admin assigned to Ngaglik. Target in Depok (same Sleman city).
     * Actions: view, approve, reject DENIED.
     */
    public function test_case_b_sibling_district_denied(): void
    {
        $admin = $this->createAdminWithDistricts([$this->distNgaglik->id]);

        // 1. KTP
        $regDepok = $this->createKtpRegistration($this->distDepok, 'Sibling KTP Depok');

        // View denied
        $ktpComp = Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('viewKtp', $regDepok->id)
            ->assertSee('Data tidak ditemukan atau berada di luar wilayah wewenang Anda.');
        $this->assertFalse($ktpComp->get('showModal'));

        // Approve denied
        $ktpCompApprove = Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('approveKtp', $regDepok->id)
            ->assertSee('Registrasi tidak ditemukan atau berada di luar wilayah wewenang Anda.');
        $this->assertEquals('pending_verification', $regDepok->fresh()->status);

        // Reject modal denied
        $ktpCompReject = Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('openRejectModal', $regDepok->id)
            ->assertSee('Data tidak ditemukan atau berada di luar wilayah wewenang Anda.');
        $this->assertFalse($ktpCompReject->get('showRejectModal'));

        // 2. Vehicle
        $mitraDepok = $this->createVehicleMitra($this->distDepok, 'Sibling Vehicle Depok');

        // View denied
        $vehComp = Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->set('activeTab', 'vehicle')
            ->call('viewVehicle', $mitraDepok->id)
            ->assertSee('Data mitra tidak ditemukan atau berada di luar wilayah wewenang Anda.');
        $this->assertFalse($vehComp->get('showVehicleModal'));

        // Approve denied
        $vehCompApprove = Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->set('activeTab', 'vehicle')
            ->call('approveVehicle', $mitraDepok->id)
            ->assertSee('Mitra tidak ditemukan atau berada di luar wilayah wewenang Anda.');
        $this->assertEquals('pending', $mitraDepok->fresh()->vehicle_verification_status);

        // Reject modal denied
        $vehCompReject = Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->set('activeTab', 'vehicle')
            ->call('openRejectVehicleModal', $mitraDepok->id)
            ->assertSee('Mitra tidak ditemukan atau berada di luar wilayah wewenang Anda.');
        $this->assertFalse($vehCompReject->get('showRejectVehicleModal'));
    }

    /**
     * Case C — Cross-city unauthorized:
     * Admin assigned to Ngaglik (Sleman). Target in Gondomanan (Yogyakarta).
     * Actions: DENIED.
     */
    public function test_case_c_cross_city_unauthorized_denied(): void
    {
        $admin = $this->createAdminWithDistricts([$this->distNgaglik->id]);

        // 1. KTP
        $regGondomanan = $this->createKtpRegistration($this->distGondomanan, 'Cross-City KTP Gondomanan');

        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('viewKtp', $regGondomanan->id)
            ->assertSee('Data tidak ditemukan atau berada di luar wilayah wewenang Anda.');

        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('approveKtp', $regGondomanan->id)
            ->assertSee('Registrasi tidak ditemukan atau berada di luar wilayah wewenang Anda.');
        $this->assertEquals('pending_verification', $regGondomanan->fresh()->status);

        // 2. Vehicle
        $mitraGondomanan = $this->createVehicleMitra($this->distGondomanan, 'Cross-City Vehicle Gondomanan');

        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->set('activeTab', 'vehicle')
            ->call('viewVehicle', $mitraGondomanan->id)
            ->assertSee('Data mitra tidak ditemukan atau berada di luar wilayah wewenang Anda.');

        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->set('activeTab', 'vehicle')
            ->call('approveVehicle', $mitraGondomanan->id)
            ->assertSee('Mitra tidak ditemukan atau berada di luar wilayah wewenang Anda.');
        $this->assertEquals('pending', $mitraGondomanan->fresh()->vehicle_verification_status);
    }

    /**
     * Case D — Zero territory:
     * Admin has managedDistricts = [].
     * Actions: List empty, all direct actions DENIED.
     */
    public function test_case_d_zero_territory_denied(): void
    {
        $admin = $this->createAdminWithDistricts([]); // 0 districts assigned

        $reg = $this->createKtpRegistration($this->distNgaglik, 'Target KTP');
        $mitra = $this->createVehicleMitra($this->distNgaglik, 'Target Vehicle');

        // Listings are empty
        $comp = Livewire::actingAs($admin)->test(AdminVerifications::class);
        $this->assertCount(0, $comp->viewData('verifications'));
        $this->assertEquals(0, $comp->viewData('pendingKtpCount'));

        $comp->set('activeTab', 'vehicle');
        $this->assertCount(0, $comp->viewData('vehicleVerifications'));
        $this->assertEquals(0, $comp->viewData('pendingVehicleCount'));

        // Direct actions denied
        $comp->call('viewKtp', $reg->id)
            ->assertSee('Data tidak ditemukan atau berada di luar wilayah wewenang Anda.');

        $comp->call('approveKtp', $reg->id)
            ->assertSee('Registrasi tidak ditemukan atau berada di luar wilayah wewenang Anda.');

        $comp->call('viewVehicle', $mitra->id)
            ->assertSee('Data mitra tidak ditemukan atau berada di luar wilayah wewenang Anda.');

        $comp->call('approveVehicle', $mitra->id)
            ->assertSee('Mitra tidak ditemukan atau berada di luar wilayah wewenang Anda.');

        $this->assertEquals('pending_verification', $reg->fresh()->status);
        $this->assertEquals('pending', $mitra->fresh()->vehicle_verification_status);
    }

    /**
     * Case E — Multi-district:
     * Admin assigned to Ngaglik (Sleman) and Gondomanan (Yogyakarta).
     * Exact assigned targets: ALLOWED.
     * Sibling districts (Depok in Sleman, Danurejan in Yogya): DENIED.
     */
    public function test_case_e_multidistrict_allowed_and_siblings_denied(): void
    {
        $admin = $this->createAdminWithDistricts([$this->distNgaglik->id, $this->distGondomanan->id]);

        $regNgaglik = $this->createKtpRegistration($this->distNgaglik, 'Target Ngaglik');
        $regGondomanan = $this->createKtpRegistration($this->distGondomanan, 'Target Gondomanan');
        $regDepok = $this->createKtpRegistration($this->distDepok, 'Sibling Depok');
        $regDanurejan = $this->createKtpRegistration($this->distDanurejan, 'Sibling Danurejan');

        $mitraNgaglik = $this->createVehicleMitra($this->distNgaglik, 'Mitra Ngaglik');
        $mitraGondomanan = $this->createVehicleMitra($this->distGondomanan, 'Mitra Gondomanan');
        $mitraDepok = $this->createVehicleMitra($this->distDepok, 'Mitra Depok');
        $mitraDanurejan = $this->createVehicleMitra($this->distDanurejan, 'Mitra Danurejan');

        // 1. Exact assigned districts: KTP & Vehicle ALLOWED
        // Ngaglik
        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('viewKtp', $regNgaglik->id)
            ->assertDontSee('Data tidak ditemukan atau berada di luar wilayah wewenang Anda.');
        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('approveKtp', $regNgaglik->id)
            ->assertSee('Registrasi berhasil disetujui.');

        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->set('activeTab', 'vehicle')
            ->call('viewVehicle', $mitraNgaglik->id)
            ->assertDontSee('Data mitra tidak ditemukan atau berada di luar wilayah wewenang Anda.');
        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->set('activeTab', 'vehicle')
            ->call('approveVehicle', $mitraNgaglik->id)
            ->assertSee('berhasil disetujui');

        // Gondomanan
        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('viewKtp', $regGondomanan->id)
            ->assertDontSee('Data tidak ditemukan atau berada di luar wilayah wewenang Anda.');
        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('approveKtp', $regGondomanan->id)
            ->assertSee('Registrasi berhasil disetujui.');

        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->set('activeTab', 'vehicle')
            ->call('viewVehicle', $mitraGondomanan->id)
            ->assertDontSee('Data mitra tidak ditemukan atau berada di luar wilayah wewenang Anda.');
        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->set('activeTab', 'vehicle')
            ->call('approveVehicle', $mitraGondomanan->id)
            ->assertSee('berhasil disetujui');

        // 2. Sibling districts: DENIED
        // Depok (sibling of Ngaglik in Sleman)
        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('viewKtp', $regDepok->id)
            ->assertSee('Data tidak ditemukan atau berada di luar wilayah wewenang Anda.');
        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('approveKtp', $regDepok->id)
            ->assertSee('Registrasi tidak ditemukan atau berada di luar wilayah wewenang Anda.');

        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->set('activeTab', 'vehicle')
            ->call('viewVehicle', $mitraDepok->id)
            ->assertSee('Data mitra tidak ditemukan atau berada di luar wilayah wewenang Anda.');
        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->set('activeTab', 'vehicle')
            ->call('approveVehicle', $mitraDepok->id)
            ->assertSee('Mitra tidak ditemukan atau berada di luar wilayah wewenang Anda.');

        // Danurejan (sibling of Gondomanan in Yogyakarta)
        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('viewKtp', $regDanurejan->id)
            ->assertSee('Data tidak ditemukan atau berada di luar wilayah wewenang Anda.');
        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('approveKtp', $regDanurejan->id)
            ->assertSee('Registrasi tidak ditemukan atau berada di luar wilayah wewenang Anda.');

        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->set('activeTab', 'vehicle')
            ->call('viewVehicle', $mitraDanurejan->id)
            ->assertSee('Data mitra tidak ditemukan atau berada di luar wilayah wewenang Anda.');
        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->set('activeTab', 'vehicle')
            ->call('approveVehicle', $mitraDanurejan->id)
            ->assertSee('Mitra tidak ditemukan atau berada di luar wilayah wewenang Anda.');
    }
}
