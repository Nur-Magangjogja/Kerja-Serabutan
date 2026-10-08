<?php

namespace Tests\Feature;

use App\Livewire\Mitra\Helps\HelpDetail;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MitraHelpDetailCancellationTest extends TestCase
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
            'role'    => 'customer',
            'city_id' => $this->city->id,
        ]);
        UserBalance::create(['user_id' => $this->customer->id, 'balance' => 1000000]);

        $this->mitra = User::factory()->create([
            'role'        => 'mitra',
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
        ]);
        UserBalance::create(['user_id' => $this->mitra->id, 'balance' => 0]);
        PartnerOnlineState::updateOrCreate(
            ['user_id' => $this->mitra->id],
            [
                'is_online'       => true,
                'is_busy'         => true,
                'latitude'        => -7.7960,
                'longitude'       => 110.3700,
                'last_seen_at'    => now(),
            ]
        );
    }

    /**
     * Konsep 1 (Transit): Mitra membatalkan penugasan saat status 'taken' (sebelum mulai pengerjaan).
     * Label tombol canonical: 'Batalkan Penugasan (Kendala Perjalanan)'.
     * Order di-relist ke pool, mitra dilepas dari BUSY, tiket audit dibuat tanpa SP otomatis.
     */
    public function test_mitra_can_open_modal_and_cancel_taken_help(): void
    {
        $this->actingAs($this->mitra);

        $help = Help::create([
            'order_id'      => 'HLP-' . strtoupper(uniqid()),
            'user_id'       => $this->customer->id,
            'mitra_id'      => $this->mitra->id,
            'city_id'       => $this->city->id,
            'district_id'   => $this->district->id,
            'title'         => 'Bantuan Kebocoran Atap',
            'description'   => 'Genteng bocor parah di ruang tamu',
            'location'      => 'Jl. Malioboro No. 10, Danurejan',
            'service_type'  => 'on_site_service',
            'status'        => Help::STATUS_TAKEN,
            'taken_at'      => now(),
            'amount'        => 50000,
            'escrow_status' => Help::ESCROW_STATUS_HELD,
        ]);

        $photo = UploadedFile::fake()->image('ban_bocor.jpg');

        Livewire::test(HelpDetail::class, ['id' => $help->id])
            ->assertSee('Batalkan Penugasan (Kendala Perjalanan)')
            ->call('openPartnerCancelModal')
            ->assertSet('showPartnerCancelModal', true)
            ->set('partnerCancelReason', 'Kendaraan Bermasalah / Mogok / Ban Bocor')
            ->set('partnerCancelNotes', 'Ban motor bocor terkena paku di jalan')
            ->set('cancel_evidence_photo', $photo)
            ->call('requestPartnerCancel')
            ->assertRedirect(route('mitra.dashboard'));

        $this->assertDatabaseHas('help_cancel_requests', [
            'help_id'            => $help->id,
            'requester_type'     => HelpCancelRequest::REQUESTER_PARTNER,
            'partner_id'         => $this->mitra->id,
            'cancellation_stage' => 'transit',
            'reason'             => 'Kendaraan Bermasalah / Mogok / Ban Bocor',
            'status'             => HelpCancelRequest::STATUS_PENDING,
            'sp_target'          => HelpCancelRequest::SP_TARGET_NONE,
        ]);

        $help->refresh();
        $this->assertEquals(Help::STATUS_MENUNGGU_MITRA, $help->status);
        $this->assertNull($help->mitra_id);

        // Mitra dilepas dari status BUSY
        $onlineState = PartnerOnlineState::where('user_id', $this->mitra->id)->first();
        $this->assertFalse((bool) $onlineState->is_busy);

        // Tidak ada penerbitan SP otomatis
        $this->mitra->refresh();
        $this->assertEquals(0, (int) ($this->mitra->sp_level ?? 0));
    }

    /**
     * Konsep 2: Mitra melihat tombol 'Ajukan Kendala Lapangan' saat status 'in_progress'.
     * Modal dapat dibuka dan ditutup kembali.
     */
    public function test_mitra_can_open_and_close_kendala_lapangan_modal_when_status_is_in_progress(): void
    {
        $this->actingAs($this->mitra);

        $help = Help::create([
            'order_id'           => 'HLP-' . strtoupper(uniqid()),
            'user_id'            => $this->customer->id,
            'mitra_id'           => $this->mitra->id,
            'city_id'            => $this->city->id,
            'district_id'        => $this->district->id,
            'title'              => 'Bantuan Cat Tembok',
            'description'        => 'Pengecatan dinding kamar',
            'location'           => 'Jl. Mataram No. 20, Danurejan',
            'service_type'       => 'on_site_service',
            'status'             => Help::STATUS_IN_PROGRESS,
            'taken_at'           => now()->subMinutes(30),
            'partner_arrived_at' => now()->subMinutes(15),
            'service_started_at' => now()->subMinutes(10),
            'amount'             => 75000,
            'escrow_status'      => Help::ESCROW_STATUS_HELD,
        ]);

        Livewire::test(HelpDetail::class, ['id' => $help->id])
            ->assertSee('Ajukan Kendala Lapangan')
            ->call('openPartnerCancelModal')
            ->assertSet('showPartnerCancelModal', true)
            ->call('closePartnerCancelModal')
            ->assertSet('showPartnerCancelModal', false);
    }

    /**
     * Konsep 2 Flow Lengkap: Mitra mengajukan kendala saat status 'in_progress'.
     * Status help berubah menjadi 'partner_cancel_requested' (tidak relist langsung ke pool),
     * tiket audit dibuat dengan stage 'in_progress', dan tidak ada SP otomatis.
     */
    public function test_mitra_can_submit_kendala_lapangan_when_in_progress(): void
    {
        $this->actingAs($this->mitra);

        $help = Help::create([
            'order_id'           => 'HLP-' . strtoupper(uniqid()),
            'user_id'            => $this->customer->id,
            'mitra_id'           => $this->mitra->id,
            'city_id'            => $this->city->id,
            'district_id'        => $this->district->id,
            'title'              => 'Bantuan Cat Tembok',
            'description'        => 'Pengecatan dinding kamar',
            'location'           => 'Jl. Mataram No. 20, Danurejan',
            'service_type'       => 'on_site_service',
            'status'             => Help::STATUS_IN_PROGRESS,
            'taken_at'           => now()->subMinutes(30),
            'partner_arrived_at' => now()->subMinutes(15),
            'service_started_at' => now()->subMinutes(10),
            'amount'             => 75000,
            'escrow_status'      => Help::ESCROW_STATUS_HELD,
        ]);

        $photo = UploadedFile::fake()->image('dinding_rusak.jpg');

        Livewire::test(HelpDetail::class, ['id' => $help->id])
            ->assertSee('Ajukan Kendala Lapangan')
            ->call('openPartnerCancelModal')
            ->assertSet('showPartnerCancelModal', true)
            ->set('partnerCancelReason', 'Lokasi / Kondisi Kerja Berbahaya & Tidak Aman')
            ->set('partnerCancelNotes', 'Tembok rapuh dan berisiko roboh jika dicat')
            ->set('cancel_evidence_photo', $photo)
            ->call('requestPartnerCancel')
            ->assertSet('showPartnerCancelModal', false);

        $this->assertDatabaseHas('help_cancel_requests', [
            'help_id'            => $help->id,
            'requester_type'     => HelpCancelRequest::REQUESTER_PARTNER,
            'partner_id'         => $this->mitra->id,
            'cancellation_stage' => 'in_progress',
            'status'             => HelpCancelRequest::STATUS_PENDING,
            'sp_target'          => HelpCancelRequest::SP_TARGET_NONE,
        ]);

        $help->refresh();
        $this->assertEquals(Help::STATUS_PARTNER_CANCEL_REQUESTED, $help->status);

        // Tidak ada penerbitan SP otomatis
        $this->mitra->refresh();
        $this->assertEquals(0, (int) ($this->mitra->sp_level ?? 0));
    }

    /**
     * Validasi: alasan, catatan, dan foto bukti wajib diisi saat mengajukan kendala/pembatalan.
     */
    public function test_validation_requires_cancel_reason_notes_and_photo(): void
    {
        $this->actingAs($this->mitra);

        $help = Help::create([
            'order_id'      => 'HLP-' . strtoupper(uniqid()),
            'user_id'       => $this->customer->id,
            'mitra_id'      => $this->mitra->id,
            'city_id'       => $this->city->id,
            'district_id'   => $this->district->id,
            'title'         => 'Bantuan Angkat Lemari',
            'description'   => 'Angkat lemari kayu ke lantai 2',
            'location'      => 'Jl. Suryatmajan No. 5, Danurejan',
            'service_type'  => 'on_site_service',
            'status'        => Help::STATUS_TAKEN,
            'taken_at'      => now(),
            'amount'        => 40000,
            'escrow_status' => Help::ESCROW_STATUS_HELD,
        ]);

        Livewire::test(HelpDetail::class, ['id' => $help->id])
            ->call('openPartnerCancelModal')
            ->set('partnerCancelReason', '')
            ->set('partnerCancelNotes', '')
            ->call('requestPartnerCancel')
            ->assertHasErrors([
                'partnerCancelReason'   => 'required',
                'partnerCancelNotes'    => 'required',
                'cancel_evidence_photo' => 'required',
            ]);
    }

    /**
     * Guard: Mitra lain yang tidak berhak tidak boleh mengakses atau membatalkan penugasan.
     */
    public function test_unauthorized_mitra_cannot_access_or_cancel_other_mitra_help(): void
    {
        $otherMitra = User::factory()->create([
            'role'              => 'mitra',
            'city_id'           => $this->city->id,
            'district_id'       => $this->district->id,
            'status'            => 'active',
            'verified'          => true,
            'nik'               => '3404112233440001',
            'ktp_photo'         => 'ktp/other.jpg',
            'email_verified_at' => now(),
        ]);

        $help = Help::create([
            'order_id'      => 'HLP-' . strtoupper(uniqid()),
            'user_id'       => $this->customer->id,
            'mitra_id'      => $this->mitra->id,
            'city_id'       => $this->city->id,
            'district_id'   => $this->district->id,
            'title'         => 'Bantuan Cuci AC',
            'description'   => 'Cuci AC 1 PK kamar utama',
            'location'      => 'Jl. Malioboro No. 15, Danurejan',
            'service_type'  => 'on_site_service',
            'status'        => Help::STATUS_TAKEN,
            'taken_at'      => now(),
            'amount'        => 65000,
            'escrow_status' => Help::ESCROW_STATUS_HELD,
        ]);

        $this->actingAs($otherMitra);

        // Pastikan mitra lain tidak pernah menerima notifikasi atau terlibat pada help ini
        \Illuminate\Support\Facades\DB::table('notifications')->truncate();

        $response = $this->get(route('mitra.helps.detail', ['id' => $help->id]));
        $response->assertRedirect(route('mitra.dashboard'));
        $response->assertSessionHas('error', 'Bantuan ini tidak ditugaskan kepada Anda.');
    }
}
