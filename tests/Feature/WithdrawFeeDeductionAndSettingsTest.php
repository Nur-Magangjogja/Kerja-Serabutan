<?php

namespace Tests\Feature;

use App\Livewire\Admin\Withdraws\Index as AdminWithdraws;
use App\Livewire\Mitra\Withdraw\WithdrawForm as MitraWithdrawForm;
use App\Livewire\SuperAdmin\Settings\WithdrawSettings;
use App\Models\AppSetting;
use App\Models\BalanceTransaction;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\WithdrawRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WithdrawFeeDeductionAndSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_withdraw_fee_adds_fee_to_balance_deduction()
    {
        $feeCalc = AppSetting::calculateWithdrawFee('BRI', 50000);

        $this->assertEquals(2500, $feeCalc['fee']);
        $this->assertEquals(50000, $feeCalc['net_amount']);
        $this->assertEquals(52500, $feeCalc['total_deduction']);
    }

    public function test_superadmin_saves_withdraw_general_settings()
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'status' => 'active']);

        Livewire::actingAs($superAdmin)
            ->test(WithdrawSettings::class)
            ->set('min_amount', 20000)
            ->set('default_other_fee', 3000)
            ->call('saveGeneralSettings')
            ->assertDispatched('settings-saved');

        $this->assertEquals(20000, AppSetting::getWithdrawMinAmount());
        $this->assertEquals(3000, AppSetting::getWithdrawDefaultFee());
    }

    public function test_mitra_withdraw_deducts_amount_plus_admin_fee_from_wallet()
    {
        $mitra = User::factory()->create(['role' => 'mitra', 'status' => 'active']);
        UserBalance::create(['user_id' => $mitra->id, 'balance' => 100000]);

        Livewire::actingAs($mitra)
            ->test(MitraWithdrawForm::class)
            ->set('amount', 50000)
            ->set('bankCode', 'BRI')
            ->set('accountNumber', '1234567890')
            ->set('accountName', 'Budi Santoso')
            ->call('submit')
            ->assertRedirect(route('mitra.withdraw.history'));

        // Saldo dompet awal 100.000 - (50.000 + 2.500) = 47.500
        $this->assertEquals(47500, (float) $mitra->fresh()->balance);

        $withdraw = WithdrawRequest::where('user_id', $mitra->id)->first();
        $this->assertNotNull($withdraw);
        $this->assertEquals(50000, $withdraw->amount);
        $this->assertEquals(2500, $withdraw->admin_fee);
        $this->assertEquals(50000, $withdraw->net_amount);
    }

    public function test_mitra_withdraw_fails_when_balance_less_than_amount_plus_fee()
    {
        $mitra = User::factory()->create(['role' => 'mitra', 'status' => 'active']);
        // Saldo hanya 51.000, sedangkan penarikan 50.000 + 2.500 butuh 52.500
        UserBalance::create(['user_id' => $mitra->id, 'balance' => 51000]);

        Livewire::actingAs($mitra)
            ->test(MitraWithdrawForm::class)
            ->set('amount', 50000)
            ->set('bankCode', 'BRI')
            ->set('accountNumber', '1234567890')
            ->set('accountName', 'Budi Santoso')
            ->call('submit')
            ->assertHasErrors(['amount']);

        // Saldo tetap 51.000
        $this->assertEquals(51000, (float) $mitra->fresh()->balance);
        $this->assertEquals(0, WithdrawRequest::count());
    }

    public function test_admin_reject_refunds_full_amount_plus_fee_to_mitra()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $mitra = User::factory()->create(['role' => 'mitra', 'status' => 'active']);
        UserBalance::create(['user_id' => $mitra->id, 'balance' => 47500]);

        $withdraw = WithdrawRequest::create([
            'user_id' => $mitra->id,
            'amount' => 50000,
            'admin_fee' => 2500,
            'net_amount' => 50000,
            'bank_code' => 'BRI',
            'account_number' => '1234567890',
            'account_name' => 'Budi Santoso',
            'status' => 'pending',
        ]);

        Livewire::actingAs($admin)
            ->test(AdminWithdraws::class)
            ->call('openRejectModal', $withdraw->id)
            ->set('rejectReason', 'Nomor rekening salah')
            ->call('submitReject');

        // Saldo 47.500 + 52.500 (50.000 + 2.500) = 100.000
        $this->assertEquals(100000, (float) $mitra->fresh()->balance);
        $this->assertEquals('rejected', $withdraw->fresh()->status);

        // Verifikasi ledger refund tercatat dengan benar
        $refundTx = BalanceTransaction::where('order_id', 'REFUND-WD-' . $withdraw->id)->first();
        $this->assertNotNull($refundTx);
        $this->assertEquals(52500, $refundTx->amount);
        $this->assertEquals('refund', $refundTx->type);
        $this->assertEquals('success', $refundTx->status);
        $this->assertEquals("withdraw:{$withdraw->id}:refund", $refundTx->idempotency_key);
    }

    public function test_end_to_end_mitra_withdraw_request_then_admin_reject_flow()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $mitra = User::factory()->create(['role' => 'mitra', 'status' => 'active']);
        UserBalance::create(['user_id' => $mitra->id, 'balance' => 100000]);

        // 1. Mitra mengajukan penarikan 50.000 + fee 2.500
        Livewire::actingAs($mitra)
            ->test(MitraWithdrawForm::class)
            ->set('amount', 50000)
            ->set('bankCode', 'BRI')
            ->set('accountNumber', '1234567890')
            ->set('accountName', 'Budi Santoso')
            ->call('submit')
            ->assertRedirect(route('mitra.withdraw.history'));

        // Saldo terpotong: 100.000 - 52.500 = 47.500
        $this->assertEquals(47500, (float) $mitra->fresh()->balance);

        $withdraw = WithdrawRequest::where('user_id', $mitra->id)->first();
        $this->assertNotNull($withdraw);
        $this->assertEquals('pending', $withdraw->status);

        // Ledger awal: withdraw pending
        $txInitial = BalanceTransaction::where('order_id', 'WD-' . $withdraw->id)->first();
        $this->assertNotNull($txInitial);
        $this->assertEquals('withdraw', $txInitial->type);
        $this->assertEquals(52500, $txInitial->total_payment);
        $this->assertEquals('pending', $txInitial->status);

        // 2. Admin menolak penarikan
        Livewire::actingAs($admin)
            ->test(AdminWithdraws::class)
            ->call('openRejectModal', $withdraw->id)
            ->set('rejectReason', 'Nomor rekening atas nama orang lain')
            ->call('submitReject');

        // Saldo kembali utuh ke saldo awal: 47.500 + 52.500 = 100.000
        $this->assertEquals(100000, (float) $mitra->fresh()->balance);
        $this->assertEquals('rejected', $withdraw->fresh()->status);

        // Ledger kedua: refund success
        $txRefund = BalanceTransaction::where('order_id', 'REFUND-WD-' . $withdraw->id)->first();
        $this->assertNotNull($txRefund);
        $this->assertEquals('refund', $txRefund->type);
        $this->assertEquals(52500, $txRefund->amount);
        $this->assertEquals('success', $txRefund->status);
    }

    public function test_admin_reject_is_idempotent_and_prevents_double_refund()
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $mitra = User::factory()->create(['role' => 'mitra', 'status' => 'active']);
        UserBalance::create(['user_id' => $mitra->id, 'balance' => 47500]);

        $withdraw = WithdrawRequest::create([
            'user_id' => $mitra->id,
            'amount' => 50000,
            'admin_fee' => 2500,
            'net_amount' => 50000,
            'bank_code' => 'BRI',
            'account_number' => '1234567890',
            'account_name' => 'Budi Santoso',
            'status' => 'pending',
        ]);

        // Reject pertama kali
        Livewire::actingAs($admin)
            ->test(AdminWithdraws::class)
            ->call('openRejectModal', $withdraw->id)
            ->set('rejectReason', 'Alasan pertama')
            ->call('submitReject');

        $this->assertEquals(100000, (float) $mitra->fresh()->balance);
        $this->assertEquals('rejected', $withdraw->fresh()->status);

        // Upaya reject kedua kali pada penarikan yang sudah ditolak
        Livewire::actingAs($admin)
            ->test(AdminWithdraws::class)
            ->call('openRejectModal', $withdraw->id)
            ->set('rejectReason', 'Alasan kedua kali')
            ->call('submitReject')
            ->assertSee('Permintaan penarikan ini sudah diproses sebelumnya.');

        // Saldo tetap 100.000 (TIDAK menjadi 152.500 karena double refund)
        $this->assertEquals(100000, (float) $mitra->fresh()->balance);

        // Ledger refund tetap hanya 1
        $this->assertEquals(1, BalanceTransaction::where('reference_id', $withdraw->id)->where('type', 'refund')->count());
    }

    public function test_admin_approve_completes_withdraw_without_double_deduction()
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $mitra = User::factory()->create(['role' => 'mitra', 'status' => 'active']);
        UserBalance::create(['user_id' => $mitra->id, 'balance' => 100000]);

        // 1. Mitra ajukan penarikan (saldo terpotong 52.500 -> sisa 47.500)
        Livewire::actingAs($mitra)
            ->test(MitraWithdrawForm::class)
            ->set('amount', 50000)
            ->set('bankCode', 'BRI')
            ->set('accountNumber', '0987654321')
            ->set('accountName', 'Budi Santoso')
            ->call('submit');

        $this->assertEquals(47500, (float) $mitra->fresh()->balance);
        $withdraw = WithdrawRequest::where('user_id', $mitra->id)->first();
        $this->assertEquals('pending', $withdraw->status);

        // 2. Admin menyetujui dan mengunggah bukti transfer
        $fakePhoto = UploadedFile::fake()->image('bukti_transfer.jpg');

        Livewire::actingAs($admin)
            ->test(AdminWithdraws::class)
            ->call('openApproveModal', $withdraw->id)
            ->set('proofPhoto', $fakePhoto)
            ->call('submitApprove');

        $withdraw->refresh();
        $this->assertEquals('completed', $withdraw->status);
        $this->assertNotNull($withdraw->proof_of_transfer);
        Storage::disk('public')->assertExists($withdraw->proof_of_transfer);

        // Saldo tetap 47.500 (TIDAK terpotong dua kali saat disetujui)
        $this->assertEquals(47500, (float) $mitra->fresh()->balance);

        // Ledger status berubah menjadi success
        $ledger = BalanceTransaction::where('order_id', 'WD-' . $withdraw->id)->first();
        $this->assertNotNull($ledger);
        $this->assertEquals('success', $ledger->status);
        $this->assertEquals($withdraw->proof_of_transfer, $ledger->proof_of_payment);
    }
}
