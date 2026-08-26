<?php

namespace App\Livewire\Mitra\Withdraw;

use App\Models\AppSetting;
use App\Models\BalanceTransaction;
use App\Models\WithdrawRequest;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class WithdrawForm extends Component
{
    public $amount = '';
    public $bankCode = 'BCA';
    public $accountNumber = '';
    public $accountName = '';
    public $notes = '';

    public function submit()
    {
        $user = auth()->user();
        $balance = (float) ($user->balance?->balance ?? 0);
        $minAmount = (float) (AppSetting::where('key', 'withdraw_min_amount')->value('value') ?? 10000);
        $adminFee = (float) (AppSetting::where('key', 'withdraw_admin_fee')->value('value') ?? 2500);

        $this->validate([
            'amount' => ['required', 'numeric', 'min:' . $minAmount, 'max:' . $balance],
            'bankCode' => ['required', 'string'],
            'accountNumber' => ['required', 'string', 'min:5', 'max:30'],
            'accountName' => ['required', 'string', 'min:3', 'max:100'],
        ], [
            'amount.min' => 'Jumlah penarikan minimal Rp ' . number_format($minAmount, 0, ',', '.'),
            'amount.max' => 'Saldo dompet Anda tidak mencukupi (Saldo: Rp ' . number_format($balance, 0, ',', '.') . ')',
            'accountNumber.required' => 'Nomor rekening wajib diisi.',
            'accountName.required' => 'Nama pemilik rekening wajib diisi.',
        ]);

        $amountVal = (float) $this->amount;
        $netAmount = max(0, $amountVal - $adminFee);

        DB::transaction(function () use ($user, $amountVal, $adminFee, $netAmount) {
            // Deduct balance
            $user->balance->decrement('balance', $amountVal);

            $withdraw = WithdrawRequest::create([
                'user_id' => $user->id,
                'amount' => $amountVal,
                'admin_fee' => $adminFee,
                'net_amount' => $netAmount,
                'bank_code' => $this->bankCode,
                'account_number' => $this->accountNumber,
                'account_name' => $this->accountName,
                'status' => 'pending',
                'description' => $this->notes,
            ]);

            BalanceTransaction::create([
                'user_id' => $user->id,
                'order_id' => 'WD-' . $withdraw->id,
                'reference_id' => $withdraw->id,
                'type' => 'withdraw',
                'amount' => $amountVal,
                'admin_fee' => $adminFee,
                'total_payment' => $amountVal,
                'status' => 'pending',
                'description' => "Pencairan penghasilan mitra ke {$this->bankCode} ({$this->accountNumber} a.n {$this->accountName})",
            ]);
        });

        session()->flash('success', 'Permintaan pencairan dana berhasil diajukan dan sedang diproses oleh admin.');
        return redirect()->route('mitra.withdraw.history');
    }

    public function render()
    {
        $user = auth()->user();
        $balance = (float) ($user->balance?->balance ?? 0);
        $minAmount = (float) (AppSetting::where('key', 'withdraw_min_amount')->value('value') ?? 10000);
        $adminFee = (float) (AppSetting::where('key', 'withdraw_admin_fee')->value('value') ?? 2500);
        $amountVal = (float) ($this->amount ?: 0);
        $netAmount = max(0, $amountVal - $adminFee);

        return view('livewire.mitra.withdraw.withdraw-form', [
            'balance' => $balance,
            'minAmount' => $minAmount,
            'adminFee' => $adminFee,
            'netAmount' => $netAmount,
        ])->layout('layouts.app');
    }
}
