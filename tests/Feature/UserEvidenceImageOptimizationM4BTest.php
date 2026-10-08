<?php

namespace Tests\Feature;

use App\Livewire\Customer\Helps\Create as CustomerHelpCreate;
use App\Livewire\Customer\Helps\Detail as CustomerHelpDetail;
use App\Livewire\Customer\Reports\Create as CustomerReportCreate;
use App\Livewire\Mitra\Helps\HelpDetail as MitraHelpDetail;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\PartnerOnlineState;
use App\Models\PartnerReport;
use App\Models\User;
use App\Models\UserBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class UserEvidenceImageOptimizationM4BTest extends TestCase
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
            'is_active' => true,
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

    protected function createActiveHelp(string $status = Help::STATUS_IN_PROGRESS): Help
    {
        return Help::create([
            'user_id'          => $this->customer->id,
            'mitra_id'         => $this->mitra->id,
            'city_id'          => $this->city->id,
            'district_id'      => $this->district->id,
            'title'            => 'Bantuan Uji Coba Bukti',
            'description'      => 'Deskripsi pengujian bukti gambar M4B',
            'amount'           => 50000,
            'total_amount'     => 50000,
            'status'           => $status,
            'payment_status'   => 'paid',
            'service_type'     => Help::SERVICE_TYPE_ON_SITE,
            'service_category' => 'general',
            'location'         => 'Jl. Malioboro No. 12',
            'latitude'         => -7.7956,
            'longitude'        => 110.3695,
        ]);
    }

    /**
     * 1. MITRA JOB COMPLETION PROOF (proof_photo)
     */
    public function test_completion_proof_requires_photo(): void
    {
        $this->actingAs($this->mitra);
        $help = $this->createActiveHelp(Help::STATUS_IN_PROGRESS);

        Livewire::test(MitraHelpDetail::class, ['id' => $help->id])
            ->set('proof_photo', null)
            ->call('submitCompletionProof')
            ->assertHasErrors(['proof_photo' => 'required']);
    }

    public function test_completion_proof_accepts_valid_optimized_jpeg_under_1536kb(): void
    {
        $this->actingAs($this->mitra);
        $help = $this->createActiveHelp(Help::STATUS_IN_PROGRESS);

        $file = UploadedFile::fake()->image('proof.jpg', 1600, 1200)->size(800);

        Livewire::test(MitraHelpDetail::class, ['id' => $help->id])
            ->set('proof_photo', $file)
            ->set('completion_notes', 'Tugas sudah selesai dengan baik')
            ->call('submitCompletionProof')
            ->assertHasNoErrors();

        $help->refresh();
        $this->assertNotNull($help->proof_photo);
        $this->assertStringStartsWith('helps/proofs/', $help->proof_photo);
        Storage::disk('public')->assertExists($help->proof_photo);
    }

    public function test_completion_proof_rejects_oversized_file(): void
    {
        $this->actingAs($this->mitra);
        $help = $this->createActiveHelp(Help::STATUS_IN_PROGRESS);

        $file = UploadedFile::fake()->image('huge_proof.jpg', 2000, 2000)->size(2000);

        Livewire::test(MitraHelpDetail::class, ['id' => $help->id])
            ->set('proof_photo', $file)
            ->call('submitCompletionProof')
            ->assertHasErrors(['proof_photo' => 'max']);
    }

    public function test_completion_proof_rejects_non_image(): void
    {
        $this->actingAs($this->mitra);
        $help = $this->createActiveHelp(Help::STATUS_IN_PROGRESS);

        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        Livewire::test(MitraHelpDetail::class, ['id' => $help->id])
            ->set('proof_photo', $file)
            ->call('submitCompletionProof')
            ->assertHasErrors(['proof_photo']);
    }

    /**
     * 2. MITRA CANCELLATION OBJECTION (rejectWithdrawPhoto)
     */
    public function test_mitra_objection_optional_photo_allows_null(): void
    {
        $this->actingAs($this->mitra);
        $help = $this->createActiveHelp(Help::STATUS_CUSTOMER_CANCEL_REQUESTED);

        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $help->id,
            'requester_id'    => $this->customer->id,
            'requester_type'  => HelpCancelRequest::REQUESTER_CUSTOMER,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'previous_status' => Help::STATUS_PARTNER_ON_THE_WAY,
            'reason'          => 'Customer cancel',
            'status'          => HelpCancelRequest::STATUS_PENDING,
        ]);

        Livewire::test(MitraHelpDetail::class, ['id' => $help->id])
            ->set('rejectWithdrawNotes', 'Saya sudah berada di titik jemput lokasi customer')
            ->set('rejectWithdrawPhoto', null)
            ->call('rejectWithdrawal')
            ->assertHasNoErrors();

        $cancelReq->refresh();
        $this->assertNull($cancelReq->partner_response_photo);
        $this->assertEquals('Saya sudah berada di titik jemput lokasi customer', $cancelReq->partner_response_notes);
    }

    public function test_mitra_objection_accepts_valid_optimized_jpeg(): void
    {
        $this->actingAs($this->mitra);
        $help = $this->createActiveHelp(Help::STATUS_CUSTOMER_CANCEL_REQUESTED);

        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $help->id,
            'requester_id'    => $this->customer->id,
            'requester_type'  => HelpCancelRequest::REQUESTER_CUSTOMER,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'previous_status' => Help::STATUS_PARTNER_ON_THE_WAY,
            'reason'          => 'Perubahan rencana',
            'status'          => HelpCancelRequest::STATUS_PENDING,
        ]);

        $file = UploadedFile::fake()->image('objection.jpg', 1200, 900)->size(750);

        Livewire::test(MitraHelpDetail::class, ['id' => $help->id])
            ->set('rejectWithdrawNotes', 'Saya sudah berada di titik jemput lokasi customer')
            ->set('rejectWithdrawPhoto', $file)
            ->call('rejectWithdrawal')
            ->assertHasNoErrors();

        $cancelReq->refresh();
        $this->assertNotNull($cancelReq->partner_response_photo);
        $this->assertStringStartsWith('cancel_objections/', $cancelReq->partner_response_photo);
        Storage::disk('public')->assertExists($cancelReq->partner_response_photo);
    }

    public function test_mitra_objection_rejects_oversized_photo(): void
    {
        $this->actingAs($this->mitra);
        $help = $this->createActiveHelp(Help::STATUS_CUSTOMER_CANCEL_REQUESTED);

        $file = UploadedFile::fake()->image('huge_objection.jpg', 2000, 2000)->size(2000);

        Livewire::test(MitraHelpDetail::class, ['id' => $help->id])
            ->set('rejectWithdrawNotes', 'Alasan pembelaan yang cukup panjang')
            ->set('rejectWithdrawPhoto', $file)
            ->call('rejectWithdrawal')
            ->assertHasErrors(['rejectWithdrawPhoto' => 'max']);
    }

    /**
     * 3. MITRA PARTNER CLARIFICATION (partnerClarificationPhoto)
     */
    public function test_mitra_clarification_optional_photo_allows_null(): void
    {
        $this->actingAs($this->mitra);
        $help = $this->createActiveHelp(Help::STATUS_CUSTOMER_CANCEL_REQUESTED);

        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $help->id,
            'requester_id'    => $this->customer->id,
            'requester_type'  => HelpCancelRequest::REQUESTER_CUSTOMER,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'previous_status' => Help::STATUS_PARTNER_ON_THE_WAY,
            'reason'          => 'Customer cancel',
            'status'          => HelpCancelRequest::STATUS_PENDING,
        ]);

        Livewire::test(MitraHelpDetail::class, ['id' => $help->id])
            ->set('partnerClarificationText', 'Klarifikasi mitra atas kondisi lapangan')
            ->set('partnerClarificationPhoto', null)
            ->call('submitClarification')
            ->assertHasNoErrors();

        $cancelReq->refresh();
        $this->assertEquals('Klarifikasi mitra atas kondisi lapangan', $cancelReq->partner_clarification);
    }

    public function test_mitra_clarification_accepts_valid_optimized_jpeg(): void
    {
        $this->actingAs($this->mitra);
        $help = $this->createActiveHelp(Help::STATUS_CUSTOMER_CANCEL_REQUESTED);

        $cancelReq = HelpCancelRequest::create([
            'help_id'         => $help->id,
            'requester_id'    => $this->customer->id,
            'requester_type'  => HelpCancelRequest::REQUESTER_CUSTOMER,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'previous_status' => Help::STATUS_PARTNER_ON_THE_WAY,
            'reason'          => 'Customer cancel',
            'status'          => HelpCancelRequest::STATUS_PENDING,
        ]);

        $file = UploadedFile::fake()->image('clarification.jpg', 1200, 900)->size(600);

        Livewire::test(MitraHelpDetail::class, ['id' => $help->id])
            ->set('partnerClarificationText', 'Klarifikasi mitra dengan foto pendukung')
            ->set('partnerClarificationPhoto', $file)
            ->call('submitClarification')
            ->assertHasNoErrors();

        $cancelReq->refresh();
        $this->assertNotNull($cancelReq->partner_clarification_photo);
        $this->assertStringStartsWith('cancel_clarifications/', $cancelReq->partner_clarification_photo);
        Storage::disk('public')->assertExists($cancelReq->partner_clarification_photo);
    }

    public function test_mitra_clarification_rejects_oversized_photo(): void
    {
        $this->actingAs($this->mitra);
        $help = $this->createActiveHelp(Help::STATUS_CUSTOMER_CANCEL_REQUESTED);

        $file = UploadedFile::fake()->image('huge_clarification.jpg', 2000, 2000)->size(2500);

        Livewire::test(MitraHelpDetail::class, ['id' => $help->id])
            ->set('partnerClarificationText', 'Klarifikasi mitra yang valid')
            ->set('partnerClarificationPhoto', $file)
            ->call('submitClarification')
            ->assertHasErrors(['partnerClarificationPhoto' => 'max']);
    }

    /**
     * 4. CUSTOMER CANCELLATION EVIDENCE (customerCancelPhoto)
     */
    public function test_customer_cancellation_requires_photo(): void
    {
        $this->actingAs($this->customer);
        $help = $this->createActiveHelp(Help::STATUS_PARTNER_ON_THE_WAY);

        Livewire::test(CustomerHelpDetail::class, ['id' => $help->id])
            ->set('cancelOption', 'withdraw')
            ->set('customerCancelReason', 'Perubahan Rencana Mendesak')
            ->set('customerCancelNotes', 'Ada acara mendadak keluarga')
            ->set('customerCancelPhoto', null)
            ->call('submitCustomerCancel')
            ->assertHasErrors(['customerCancelPhoto' => 'required']);
    }

    public function test_customer_cancellation_accepts_valid_optimized_jpeg(): void
    {
        $this->actingAs($this->customer);
        $help = $this->createActiveHelp(Help::STATUS_PARTNER_ON_THE_WAY);

        $file = UploadedFile::fake()->image('cancel_proof.jpg', 1200, 900)->size(650);

        Livewire::test(CustomerHelpDetail::class, ['id' => $help->id])
            ->set('cancelOption', 'withdraw')
            ->set('customerCancelReason', 'Perubahan Rencana Mendesak')
            ->set('customerCancelNotes', 'Ada acara mendadak keluarga yang tidak bisa ditinggalkan')
            ->set('customerCancelPhoto', $file)
            ->call('submitCustomerCancel')
            ->assertHasNoErrors();

        $cancelReq = HelpCancelRequest::where('help_id', $help->id)->latest()->first();
        $this->assertNotNull($cancelReq);
        $this->assertNotNull($cancelReq->evidence_photo);
        $this->assertStringStartsWith('customer_cancels/', $cancelReq->evidence_photo);
        Storage::disk('public')->assertExists($cancelReq->evidence_photo);
    }

    public function test_customer_cancellation_rejects_oversized_photo(): void
    {
        $this->actingAs($this->customer);
        $help = $this->createActiveHelp(Help::STATUS_PARTNER_ON_THE_WAY);

        $file = UploadedFile::fake()->image('huge_cancel.jpg', 2000, 2000)->size(2000);

        Livewire::test(CustomerHelpDetail::class, ['id' => $help->id])
            ->set('cancelOption', 'withdraw')
            ->set('customerCancelReason', 'Perubahan Rencana Mendesak')
            ->set('customerCancelNotes', 'Ada acara mendadak keluarga')
            ->set('customerCancelPhoto', $file)
            ->call('submitCustomerCancel')
            ->assertHasErrors(['customerCancelPhoto' => 'max']);
    }

    /**
     * 5. CUSTOMER HELP CREATION PHOTO (photo)
     */
    public function test_customer_help_creation_allows_null_photo(): void
    {
        $this->actingAs($this->customer);

        Livewire::test(CustomerHelpCreate::class)
            ->set('title', 'Bantuan Memindahkan Lemari')
            ->set('description', 'Perlu bantuan 1 orang untuk angkat lemari ke lantai 2')
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->set('location', 'Jl. Malioboro No. 12')
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->set('amount', 50000)
            ->set('photo', null)
            ->call('prepareConfirm')
            ->assertHasNoErrors();
    }

    public function test_customer_help_creation_accepts_valid_optimized_jpeg(): void
    {
        $this->actingAs($this->customer);

        $file = UploadedFile::fake()->image('item.jpg', 1600, 1200)->size(900);

        Livewire::test(CustomerHelpCreate::class)
            ->set('title', 'Bantuan Angkut Meja')
            ->set('description', 'Perlu bantuan angkut meja belajar kayu jati')
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->set('location', 'Jl. Malioboro No. 12')
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->set('amount', 60000)
            ->set('photo', $file)
            ->call('prepareConfirm')
            ->assertHasNoErrors();
    }

    public function test_customer_help_creation_rejects_oversized_photo(): void
    {
        $this->actingAs($this->customer);

        $file = UploadedFile::fake()->image('huge_item.jpg', 2000, 2000)->size(2000);

        Livewire::test(CustomerHelpCreate::class)
            ->set('title', 'Bantuan Angkut Meja')
            ->set('description', 'Perlu bantuan angkut meja')
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->set('location', 'Jl. Malioboro No. 12')
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->set('amount', 60000)
            ->set('photo', $file)
            ->call('prepareConfirm')
            ->assertHasErrors(['photo' => 'max']);
    }

    public function test_customer_help_creation_rejects_non_image(): void
    {
        $this->actingAs($this->customer);

        $file = UploadedFile::fake()->create('malicious.exe', 100, 'application/x-msdownload');

        Livewire::test(CustomerHelpCreate::class)
            ->set('title', 'Bantuan Angkut Meja')
            ->set('description', 'Perlu bantuan angkut meja')
            ->set('city_id', $this->city->id)
            ->set('district_id', $this->district->id)
            ->set('location', 'Jl. Malioboro No. 12')
            ->set('latitude', -7.7956)
            ->set('longitude', 110.3695)
            ->set('amount', 60000)
            ->set('photo', $file)
            ->call('prepareConfirm')
            ->assertHasErrors(['photo']);
    }

    /**
     * 6. CUSTOMER REPORT EVIDENCE (evidence_photo)
     */
    public function test_customer_report_allows_null_evidence_photo(): void
    {
        $this->actingAs($this->customer);
        $help = $this->createActiveHelp(Help::STATUS_SELESAI);

        Livewire::test(CustomerReportCreate::class, ['help_id' => $help->id])
            ->set('title', 'Laporan Ketidaksesuaian Layanan')
            ->set('report_type', 'pelayanan_tidak_sesuai')
            ->set('message', 'Pelayanan yang diberikan tidak sesuai dengan deskripsi tugas.')
            ->set('evidence_photo', null)
            ->call('submit')
            ->assertHasNoErrors();

        $report = PartnerReport::where('title', 'Laporan Ketidaksesuaian Layanan')->first();
        $this->assertNotNull($report);
        $this->assertNull($report->evidence_photo);
    }

    public function test_customer_report_accepts_valid_optimized_jpeg(): void
    {
        $this->actingAs($this->customer);
        $help = $this->createActiveHelp(Help::STATUS_SELESAI);

        $file = UploadedFile::fake()->image('incident_evidence.jpg', 1600, 1200)->size(850);

        Livewire::test(CustomerReportCreate::class, ['help_id' => $help->id])
            ->set('title', 'Laporan Kerusakan Barang')
            ->set('report_type', 'pelayanan_tidak_sesuai')
            ->set('message', 'Terjadi kerusakan pada saat proses pemindahan barang oleh rekan.')
            ->set('evidence_photo', $file)
            ->call('submit')
            ->assertHasNoErrors();

        $report = PartnerReport::where('title', 'Laporan Kerusakan Barang')->first();
        $this->assertNotNull($report);
        $this->assertNotNull($report->evidence_photo);
        $this->assertStringStartsWith('reports/evidence/', $report->evidence_photo);
        Storage::disk('public')->assertExists($report->evidence_photo);
    }

    public function test_customer_report_rejects_oversized_evidence_photo(): void
    {
        $this->actingAs($this->customer);
        $help = $this->createActiveHelp(Help::STATUS_SELESAI);

        $file = UploadedFile::fake()->image('huge_evidence.jpg', 2000, 2000)->size(2000);

        Livewire::test(CustomerReportCreate::class, ['help_id' => $help->id])
            ->set('title', 'Laporan Kerusakan Barang')
            ->set('report_type', 'pelayanan_tidak_sesuai')
            ->set('message', 'Terjadi kerusakan pada saat proses pemindahan barang oleh rekan.')
            ->set('evidence_photo', $file)
            ->call('submit')
            ->assertHasErrors(['evidence_photo' => 'max']);
    }

    public function test_customer_report_rejects_non_image(): void
    {
        $this->actingAs($this->customer);
        $help = $this->createActiveHelp(Help::STATUS_SELESAI);

        $file = UploadedFile::fake()->create('report_script.sh', 50, 'application/x-sh');

        Livewire::test(CustomerReportCreate::class, ['help_id' => $help->id])
            ->set('title', 'Laporan Kerusakan Barang')
            ->set('report_type', 'pelayanan_tidak_sesuai')
            ->set('message', 'Terjadi kerusakan pada saat proses pemindahan barang oleh rekan.')
            ->set('evidence_photo', $file)
            ->call('submit')
            ->assertHasErrors(['evidence_photo']);
    }
}
