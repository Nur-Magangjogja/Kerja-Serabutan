<?php

namespace Tests\Feature;

use App\Livewire\Admin\Disputes\Chat as AdminDisputesChat;
use App\Livewire\Admin\Partners\Reports\Chat as AdminPartnerReportsChat;
use App\Livewire\Admin\Support\Chat as AdminSupportChat;
use App\Livewire\Admin\Withdraws\Index as AdminWithdrawsIndex;
use App\Livewire\Customer\Chat\Index as CustomerChat;
use App\Livewire\Customer\Topup\TopupRequest;
use App\Livewire\Mitra\Chat\Index as MitraChat;
use App\Models\AppSetting;
use App\Models\BalanceTransaction;
use App\Models\Chat as ChatModel;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpCancelMessage;
use App\Models\HelpCancelRequest;
use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use App\Models\Province;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\WithdrawRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ChatAndFinanceImageOptimizationM4CTest extends TestCase
{
    use RefreshDatabase;

    protected Province $province;
    protected City $city;
    protected District $district;
    protected User $customer;
    protected User $mitra;
    protected User $admin;
    protected User $superAdmin;
    protected Help $help;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->province = Province::create([
            'name'      => 'DI Yogyakarta',
            'is_active' => true,
        ]);

        $this->city = City::create([
            'name'        => 'Kota Yogyakarta',
            'province'    => 'DI Yogyakarta',
            'province_id' => $this->province->id,
            'is_active'   => true,
        ]);

        $this->district = District::create([
            'city_id'   => $this->city->id,
            'name'      => 'Danurejan',
            'is_active' => true,
        ]);

        $this->customer = User::factory()->create([
            'role'              => 'customer',
            'city_id'           => $this->city->id,
            'district_id'       => $this->district->id,
            'status'            => 'active',
            'verified'          => true,
            'email_verified_at' => now(),
        ]);
        UserBalance::create(['user_id' => $this->customer->id, 'balance' => 500000]);

        $this->mitra = User::factory()->create([
            'role'              => 'mitra',
            'city_id'           => $this->city->id,
            'district_id'       => $this->district->id,
            'status'            => 'active',
            'verified'          => true,
            'email_verified_at' => now(),
        ]);
        UserBalance::create(['user_id' => $this->mitra->id, 'balance' => 500000]);

        $this->admin = User::factory()->create([
            'role'              => 'admin',
            'city_id'           => $this->city->id,
            'district_id'       => $this->district->id,
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);
        $this->admin->managedCities()->sync([$this->city->id]);
        $this->admin->managedDistricts()->sync([$this->district->id]);

        $this->superAdmin = User::factory()->create([
            'role'              => 'super_admin',
            'city_id'           => $this->city->id,
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);

        $this->help = Help::create([
            'user_id'     => $this->customer->id,
            'mitra_id'    => $this->mitra->id,
            'city_id'     => $this->city->id,
            'district_id' => $this->district->id,
            'category'    => 'kebersihan',
            'title'       => 'Pembersihan Rumah',
            'description' => 'Tolong bersihkan ruang tamu',
            'address'     => 'Jl Malioboro',
            'latitude'    => -7.7956,
            'longitude'   => 110.3695,
            'price'       => 50000,
            'total_price' => 50000,
            'status'      => 'in_progress',
        ]);
    }

    // ==========================================
    // 1. CUSTOMER CHAT PHOTO TESTS
    // ==========================================

    public function test_customer_chat_allows_message_only(): void
    {
        Livewire::actingAs($this->customer)
            ->test(CustomerChat::class, ['help' => $this->help->id])
            ->set('message', 'Halo mitra')
            ->set('photo', null)
            ->call('sendMessage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('chats', [
            'help_id'     => $this->help->id,
            'sender_id'   => $this->customer->id,
            'sender_type' => 'customer',
            'message'     => 'Halo mitra',
            'photo'       => null,
        ]);
    }

    public function test_customer_chat_allows_photo_only(): void
    {
        $file = UploadedFile::fake()->image('chat.jpg', 800, 600)->size(500);

        Livewire::actingAs($this->customer)
            ->test(CustomerChat::class, ['help' => $this->help->id])
            ->set('message', '')
            ->set('photo', $file)
            ->call('sendMessage')
            ->assertHasNoErrors();

        $message = ChatModel::where('help_id', $this->help->id)->where('sender_id', $this->customer->id)->first();
        $this->assertNotNull($message);
        $this->assertNotNull($message->photo);
        $this->assertStringStartsWith('chats/photos/', $message->photo);
        Storage::disk('public')->assertExists($message->photo);
    }

    public function test_customer_chat_rejects_empty_message_and_empty_photo(): void
    {
        Livewire::actingAs($this->customer)
            ->test(CustomerChat::class, ['help' => $this->help->id])
            ->set('message', '')
            ->set('photo', null)
            ->call('sendMessage')
            ->assertHasErrors(['message']);
    }

    public function test_customer_chat_rejects_oversized_photo(): void
    {
        $oversized = UploadedFile::fake()->image('huge.jpg', 2000, 2000)->size(1537);

        Livewire::actingAs($this->customer)
            ->test(CustomerChat::class, ['help' => $this->help->id])
            ->set('photo', $oversized)
            ->call('sendMessage')
            ->assertHasErrors(['photo']);
    }

    public function test_customer_chat_rejects_non_image_file(): void
    {
        $textDoc = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        Livewire::actingAs($this->customer)
            ->test(CustomerChat::class, ['help' => $this->help->id])
            ->set('photo', $textDoc)
            ->call('sendMessage')
            ->assertHasErrors(['photo']);
    }

    // ==========================================
    // 2. MITRA CHAT PHOTO TESTS
    // ==========================================

    public function test_mitra_chat_allows_message_only(): void
    {
        Livewire::actingAs($this->mitra)
            ->test(MitraChat::class, ['help' => $this->help->id])
            ->set('message', 'Siap meluncur')
            ->set('photo', null)
            ->call('sendMessage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('chats', [
            'help_id'     => $this->help->id,
            'sender_id'   => $this->mitra->id,
            'sender_type' => 'mitra',
            'message'     => 'Siap meluncur',
            'photo'       => null,
        ]);
    }

    public function test_mitra_chat_allows_photo_only(): void
    {
        $file = UploadedFile::fake()->image('mitra_work.jpg', 800, 600)->size(800);

        Livewire::actingAs($this->mitra)
            ->test(MitraChat::class, ['help' => $this->help->id])
            ->set('message', '')
            ->set('photo', $file)
            ->call('sendMessage')
            ->assertHasNoErrors();

        $message = ChatModel::where('help_id', $this->help->id)->where('sender_id', $this->mitra->id)->first();
        $this->assertNotNull($message);
        $this->assertNotNull($message->photo);
        $this->assertStringStartsWith('chats/photos/', $message->photo);
        Storage::disk('public')->assertExists($message->photo);
    }

    public function test_mitra_chat_rejects_empty_message_and_empty_photo(): void
    {
        Livewire::actingAs($this->mitra)
            ->test(MitraChat::class, ['help' => $this->help->id])
            ->set('message', '')
            ->set('photo', null)
            ->call('sendMessage')
            ->assertHasErrors(['message']);
    }

    public function test_mitra_chat_rejects_oversized_photo(): void
    {
        $oversized = UploadedFile::fake()->image('big.jpg', 2000, 2000)->size(1600);

        Livewire::actingAs($this->mitra)
            ->test(MitraChat::class, ['help' => $this->help->id])
            ->set('photo', $oversized)
            ->call('sendMessage')
            ->assertHasErrors(['photo']);
    }

    public function test_mitra_chat_rejects_non_image(): void
    {
        $script = UploadedFile::fake()->create('test.txt', 50, 'text/plain');

        Livewire::actingAs($this->mitra)
            ->test(MitraChat::class, ['help' => $this->help->id])
            ->set('photo', $script)
            ->call('sendMessage')
            ->assertHasErrors(['photo']);
    }

    // ==========================================
    // 3. ADMIN SUPPORT CHAT PHOTO TESTS
    // ==========================================

    public function test_admin_support_chat_allows_message_only_and_photo_only(): void
    {
        $report = PartnerReport::create([
            'reporter_id' => $this->customer->id,
            'mitra_id'    => $this->mitra->id,
            'help_id'     => $this->help->id,
            'category'    => 'dukungan',
            'report_type' => 'dukungan_umum',
            'title'       => 'Tiket Bantuan Layanan',
            'description' => 'Keluhan layanan',
            'message'     => 'Pesan keluhan dari customer',
            'status'      => 'investigating',
        ]);

        // Message only
        Livewire::actingAs($this->admin)
            ->test(AdminSupportChat::class, ['report' => $report])
            ->set('message', 'Halo dari admin support')
            ->set('photo', null)
            ->call('sendMessage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('partner_report_messages', [
            'partner_report_id' => $report->id,
            'sender_id'         => $this->admin->id,
            'message'           => 'Halo dari admin support',
        ]);

        // Photo only
        $file = UploadedFile::fake()->image('admin_evidence.jpg', 800, 600)->size(700);

        Livewire::actingAs($this->admin)
            ->test(AdminSupportChat::class, ['report' => $report])
            ->set('message', '')
            ->set('photo', $file)
            ->call('sendMessage')
            ->assertHasNoErrors();

        $msg = PartnerReportMessage::where('partner_report_id', $report->id)
            ->whereNotNull('photo')
            ->first();
        $this->assertNotNull($msg);
        $this->assertStringStartsWith('reports/messages/', $msg->photo);
        Storage::disk('public')->assertExists($msg->photo);
    }

    public function test_admin_support_chat_validates_required_without_and_max_size(): void
    {
        $report = PartnerReport::create([
            'reporter_id' => $this->customer->id,
            'mitra_id'    => $this->mitra->id,
            'help_id'     => $this->help->id,
            'category'    => 'dukungan',
            'report_type' => 'dukungan_umum',
            'title'       => 'Tiket Bantuan Layanan',
            'description' => 'Keluhan layanan',
            'message'     => 'Pesan keluhan',
            'status'      => 'investigating',
        ]);

        // Empty message and photo
        Livewire::actingAs($this->admin)
            ->test(AdminSupportChat::class, ['report' => $report])
            ->set('message', '')
            ->set('photo', null)
            ->call('sendMessage')
            ->assertHasErrors(['message']);

        // Oversized photo
        $oversized = UploadedFile::fake()->image('oversized.jpg', 2000, 2000)->size(1537);
        Livewire::actingAs($this->admin)
            ->test(AdminSupportChat::class, ['report' => $report])
            ->set('photo', $oversized)
            ->call('sendMessage')
            ->assertHasErrors(['photo']);

        // Non image
        $doc = UploadedFile::fake()->create('doc.pdf', 50, 'application/pdf');
        Livewire::actingAs($this->admin)
            ->test(AdminSupportChat::class, ['report' => $report])
            ->set('photo', $doc)
            ->call('sendMessage')
            ->assertHasErrors(['photo']);
    }

    // ==========================================
    // 4. ADMIN PARTNER REPORTS CHAT PHOTO TESTS
    // ==========================================

    public function test_admin_partner_reports_chat_photo_validation_and_storage(): void
    {
        $report = PartnerReport::create([
            'reporter_id'      => $this->customer->id,
            'reported_user_id' => $this->mitra->id,
            'reported_help_id' => $this->help->id,
            'category'         => 'dari_customer',
            'report_type'      => 'laporan_kejadian',
            'title'            => 'Laporan Investigasi',
            'description'      => 'Laporan investigasi',
            'message'          => 'Pesan laporan investigasi',
            'status'           => 'investigating',
        ]);

        $file = UploadedFile::fake()->image('report_investigation.png', 900, 700)->size(600);

        Livewire::actingAs($this->admin)
            ->test(AdminPartnerReportsChat::class, ['report' => $report])
            ->set('message', '')
            ->set('photo', $file)
            ->call('sendMessage')
            ->assertHasNoErrors();

        $msg = PartnerReportMessage::where('partner_report_id', $report->id)->first();
        $this->assertNotNull($msg);
        $this->assertStringStartsWith('reports/messages/', $msg->photo);
        Storage::disk('public')->assertExists($msg->photo);

        // Oversized rejected
        $oversized = UploadedFile::fake()->image('big_report.png', 2000, 2000)->size(1600);
        Livewire::actingAs($this->admin)
            ->test(AdminPartnerReportsChat::class, ['report' => $report])
            ->set('photo', $oversized)
            ->call('sendMessage')
            ->assertHasErrors(['photo']);
    }

    // ==========================================
    // 5. ADMIN DISPUTES CHAT PHOTO TESTS
    // ==========================================

    public function test_admin_disputes_chat_photo_validation_and_storage(): void
    {
        $cancelRequest = HelpCancelRequest::create([
            'help_id'         => $this->help->id,
            'customer_id'     => $this->customer->id,
            'partner_id'      => $this->mitra->id,
            'district_id'     => $this->district->id,
            'requester_type'  => 'customer',
            'reason'          => 'Mitra tidak kunjung datang',
            'status'          => 'pending',
            'previous_status' => 'in_progress',
            'refund_amount'   => 50000,
        ]);

        $file = UploadedFile::fake()->image('dispute_clarification.jpg', 800, 600)->size(500);

        Livewire::actingAs($this->admin)
            ->test(AdminDisputesChat::class, ['cancelRequest' => $cancelRequest])
            ->set('activeTab', 'all')
            ->set('message', '')
            ->set('photo', $file)
            ->call('sendMessage')
            ->assertHasNoErrors();

        $msg = HelpCancelMessage::where('help_cancel_request_id', $cancelRequest->id)->first();
        $this->assertNotNull($msg);
        $this->assertStringStartsWith('chat/cancellations/', $msg->photo);
        Storage::disk('public')->assertExists($msg->photo);

        // Oversized rejected
        $oversized = UploadedFile::fake()->image('huge_dispute.jpg', 2000, 2000)->size(1537);
        Livewire::actingAs($this->admin)
            ->test(AdminDisputesChat::class, ['cancelRequest' => $cancelRequest])
            ->set('photo', $oversized)
            ->call('sendMessage')
            ->assertHasErrors(['photo']);
    }

    // ==========================================
    // 6. CUSTOMER TOPUP PROOF TESTS
    // ==========================================

    public function test_customer_topup_proof_validation_and_processing(): void
    {
        AppSetting::set('topup_qris_image', 'images/payment/custom_qris.png');
        AppSetting::set('topup_qris_merchant_name', 'PT SayaBantu');
        AppSetting::set('topup_qris_nmid', 'ID1234567890');

        $file = UploadedFile::fake()->image('transfer_proof.jpg', 600, 600)->size(400);

        Livewire::actingAs($this->customer)
            ->test(TopupRequest::class)
            ->set('amount', 100000)
            ->set('customerPhone', '081234567890')
            ->call('nextStep')
            ->call('nextStep')
            ->set('proofOfPayment', $file)
            ->call('submitRequest')
            ->assertRedirect(route('customer.transactions.index'));

        $this->assertDatabaseHas('balance_transactions', [
            'user_id' => $this->customer->id,
            'amount'  => 100000,
            'type'    => 'topup',
            'status'  => 'waiting_approval',
        ]);

        $tx = BalanceTransaction::where('user_id', $this->customer->id)->latest('id')->first();
        $this->assertNotNull($tx);
        $this->assertNotNull($tx->proof_of_payment);
        $this->assertStringStartsWith('proof-of-payment/', $tx->proof_of_payment);
        Storage::disk('public')->assertExists($tx->proof_of_payment);
    }

    public function test_customer_topup_rejects_missing_or_oversized_proof(): void
    {
        AppSetting::set('topup_qris_image', 'images/payment/custom_qris.png');

        // Missing proof
        Livewire::actingAs($this->customer)
            ->test(TopupRequest::class)
            ->set('amount', 50000)
            ->set('customerPhone', '081234567890')
            ->call('nextStep')
            ->call('nextStep')
            ->set('proofOfPayment', null)
            ->call('submitRequest')
            ->assertHasErrors(['proofOfPayment' => 'required']);

        // Oversized proof (>1536 KB)
        $oversized = UploadedFile::fake()->image('huge_proof.jpg', 2000, 2000)->size(1537);
        Livewire::actingAs($this->customer)
            ->test(TopupRequest::class)
            ->set('amount', 50000)
            ->set('customerPhone', '081234567890')
            ->call('nextStep')
            ->call('nextStep')
            ->set('proofOfPayment', $oversized)
            ->call('submitRequest')
            ->assertHasErrors(['proofOfPayment' => 'max']);

        // Non image
        $pdf = UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf');
        Livewire::actingAs($this->customer)
            ->test(TopupRequest::class)
            ->set('amount', 50000)
            ->set('customerPhone', '081234567890')
            ->call('nextStep')
            ->call('nextStep')
            ->set('proofOfPayment', $pdf)
            ->call('submitRequest')
            ->assertHasErrors(['proofOfPayment' => 'image']);
    }

    // ==========================================
    // 7. ADMIN WITHDRAW PROOF TESTS
    // ==========================================

    public function test_admin_withdraw_proof_approval_and_storage(): void
    {
        $withdraw = WithdrawRequest::create([
            'user_id'           => $this->mitra->id,
            'amount'            => 100000,
            'bank_code'         => 'BCA',
            'account_number'    => '1234567890',
            'account_name'      => 'Mitra Satu',
            'admin_fee'         => 2500,
            'total_deduction'   => 102500,
            'net_amount'        => 100000,
            'status'            => 'pending',
        ]);

        $proof = UploadedFile::fake()->image('bank_transfer.jpg', 800, 600)->size(500);

        Livewire::actingAs($this->admin)
            ->test(AdminWithdrawsIndex::class)
            ->call('openReviewModal', $withdraw->id, 'approve')
            ->set('proofPhoto', $proof)
            ->call('submitApprove');

        $withdraw->refresh();
        $this->assertEquals('completed', $withdraw->status);
        $this->assertNotNull($withdraw->proof_of_transfer);
        $this->assertStringStartsWith('withdraws/proofs/', $withdraw->proof_of_transfer);
        Storage::disk('public')->assertExists($withdraw->proof_of_transfer);
    }

    public function test_admin_withdraw_rejects_missing_or_oversized_proof(): void
    {
        $withdraw = WithdrawRequest::create([
            'user_id'           => $this->mitra->id,
            'amount'            => 50000,
            'bank_code'         => 'BCA',
            'account_number'    => '1234567890',
            'account_name'      => 'Mitra Satu',
            'admin_fee'         => 2500,
            'total_deduction'   => 52500,
            'net_amount'        => 50000,
            'status'            => 'pending',
        ]);

        // Missing proof
        Livewire::actingAs($this->admin)
            ->test(AdminWithdrawsIndex::class)
            ->call('openReviewModal', $withdraw->id, 'approve')
            ->set('proofPhoto', null)
            ->call('submitApprove')
            ->assertHasErrors(['proofPhoto' => 'required']);

        // Oversized (>1536 KB)
        $oversized = UploadedFile::fake()->image('huge_bank.jpg', 2000, 2000)->size(1537);
        Livewire::actingAs($this->admin)
            ->test(AdminWithdrawsIndex::class)
            ->call('openReviewModal', $withdraw->id, 'approve')
            ->set('proofPhoto', $oversized)
            ->call('submitApprove')
            ->assertHasErrors(['proofPhoto' => 'max']);

        // Non image
        $pdf = UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf');
        Livewire::actingAs($this->admin)
            ->test(AdminWithdrawsIndex::class)
            ->call('openReviewModal', $withdraw->id, 'approve')
            ->set('proofPhoto', $pdf)
            ->call('submitApprove')
            ->assertHasErrors(['proofPhoto' => 'image']);
    }

    // ==========================================
    // 8. ADMIN WITHDRAW EDIT PROOF TESTS
    // ==========================================

    public function test_admin_withdraw_edit_proof_validation_and_update(): void
    {
        $withdraw = WithdrawRequest::create([
            'user_id'           => $this->mitra->id,
            'amount'            => 100000,
            'bank_code'         => 'BCA',
            'account_number'    => '1234567890',
            'account_name'      => 'Mitra Satu',
            'admin_fee'         => 2500,
            'total_deduction'   => 102500,
            'net_amount'        => 100000,
            'status'            => 'completed',
            'proof_of_transfer' => 'withdraws/proofs/old_proof.jpg',
        ]);

        // Edit with valid image
        $newProof = UploadedFile::fake()->image('new_bank_transfer.jpg', 800, 600)->size(400);

        Livewire::actingAs($this->admin)
            ->test(AdminWithdrawsIndex::class)
            ->call('openEditProofModal', $withdraw->id)
            ->set('editProofPhoto', $newProof)
            ->call('submitUpdateProof');

        $withdraw->refresh();
        $this->assertNotEquals('withdraws/proofs/old_proof.jpg', $withdraw->proof_of_transfer);
        $this->assertStringStartsWith('withdraws/proofs/', $withdraw->proof_of_transfer);
        Storage::disk('public')->assertExists($withdraw->proof_of_transfer);

        // Edit with oversized file
        $oversized = UploadedFile::fake()->image('huge_new.jpg', 2000, 2000)->size(1537);
        Livewire::actingAs($this->admin)
            ->test(AdminWithdrawsIndex::class)
            ->call('openEditProofModal', $withdraw->id)
            ->set('editProofPhoto', $oversized)
            ->call('submitUpdateProof')
            ->assertHasErrors(['editProofPhoto' => 'max']);
    }
}
