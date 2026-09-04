<?php

namespace App\Livewire\Mitra\Withdraw;

use App\Models\AppSetting;
use App\Models\BalanceTransaction;
use App\Models\UserBalance;
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

    public function mount()
    {
        $banks = collect(AppSetting::getWithdrawBanks())->filter(fn($b) => ($b['is_active'] ?? true) !== false);
        if ($banks->isNotEmpty() && !$banks->contains('code', $this->bankCode)) {
            $this->bankCode = $banks->first()['code'] ?? 'BCA';
        }
    }

    public function submit()
    {
        $user = auth()->user();
        $balance = (float) ($user->balance ?? 0);
        $minAmount = (float) AppSetting::getWithdrawMinAmount();

        $this->validate([
            'amount' => [
                'required',
                'numeric',
                'min:' . $minAmount,
                function ($attribute, $value, $fail) {
                    if ((int) $value % 100 !== 0) {
                        $fail('Nominal pencairan harus berupa kelipatan 100 atau 1.000 rupiah (contoh: 10.000, 25.000, 50.000).');
                    }
                },
            ],
            'bankCode' => ['required', 'string'],
            'accountNumber' => ['required', 'string', 'min:5', 'max:30'],
            'accountName' => ['required', 'string', 'min:3', 'max:100'],
        ], [
            'amount.required' => 'Nominal penarikan wajib diisi.',
            'amount.numeric' => 'Nominal harus berupa angka.',
            'amount.min' => 'Jumlah penarikan minimal Rp ' . number_format($minAmount, 0, ',', '.'),
            'bankCode.required' => 'Silakan pilih bank atau e-wallet tujuan.',
            'accountNumber.required' => 'Nomor rekening atau nomor HP e-wallet wajib diisi.',
            'accountName.required' => 'Nama pemilik rekening wajib diisi.',
        ]);

        if ($user->hasPendingOrProcessingWithdraws()) {
            $this->addError('amount', 'Anda masih memiliki permintaan pencairan dana yang sedang diproses. Mohon tunggu hingga proses selesai.');
            return;
        }

        $amountVal = (float) $this->amount;
        $feeCalc = AppSetting::calculateWithdrawFee($this->bankCode, (int) $amountVal);
        $adminFee = (float) $feeCalc['fee'];
        $netAmount = (float) $feeCalc['net_amount'];
        $totalDeduction = $amountVal + $adminFee;

        if ($totalDeduction > $balance) {
            $this->addError('amount', 'Saldo dompet tidak mencukupi untuk penarikan Rp ' . number_format($amountVal, 0, ',', '.') . ' + biaya admin Rp ' . number_format($adminFee, 0, ',', '.') . ' (Total dipotong: Rp ' . number_format($totalDeduction, 0, ',', '.') . '. Saldo Anda: Rp ' . number_format($balance, 0, ',', '.') . ')');
            return;
        }

        DB::transaction(function () use ($user, $amountVal, $adminFee, $netAmount, $totalDeduction) {
            // Deduct balance from UserBalance record (amount + admin fee)
            $userBalance = UserBalance::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
            $userBalance->decrement('balance', $totalDeduction);

            $withdraw = WithdrawRequest::create([
                'user_id' => $user->id,
                'amount' => $amountVal,
                'admin_fee' => $adminFee,
                'net_amount' => $netAmount,
                'bank_code' => strtoupper($this->bankCode),
                'account_number' => $this->accountNumber,
                'account_name' => $this->accountName,
                'status' => 'pending',
                'description' => $this->notes ?: ('A/N: ' . $this->accountName),
            ]);

            BalanceTransaction::create([
                'user_id' => $user->id,
                'order_id' => 'WD-' . $withdraw->id,
                'reference_id' => $withdraw->id,
                'type' => 'withdraw',
                'amount' => $amountVal,
                'admin_fee' => $adminFee,
                'total_payment' => $totalDeduction,
                'status' => 'pending',
                'description' => "Pencairan penghasilan mitra ke {$this->bankCode} ({$this->accountNumber} a.n {$this->accountName})",
            ]);

            // Kirim notifikasi ke Admin regional & SuperAdmin
            try {
                $cityId = $user->city_id;
                $admins = \App\Models\User::where('role', 'admin')
                    ->when($cityId, fn($q) => $q->where('city_id', $cityId))
                    ->where('status', 'active')
                    ->get();
                if ($admins->isEmpty()) {
                    $admins = \App\Models\User::where('role', 'admin')->where('status', 'active')->get();
                }
                $superAdmins = \App\Models\User::whereIn('role', ['superadmin', 'super_admin'])->where('status', 'active')->get();
                $recipients = $admins->merge($superAdmins)->unique('id');

                foreach ($recipients as $recipient) {
                    $recipient->notify(new \App\Notifications\NewWithdrawNotification($withdraw));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[MitraWithdraw] Gagal kirim notifikasi withdraw: ' . $e->getMessage());
            }
        });

        session()->flash('success', 'Permintaan pencairan dana berhasil diajukan dan sedang diproses oleh admin.');
        return redirect()->route('mitra.withdraw.history');
    }

    public function render()
    {
        $user = auth()->user();
        $balance = (float) ($user->balance ?? 0);
        $banks = collect(AppSetting::getWithdrawBanks())->filter(fn($b) => ($b['is_active'] ?? true) !== false)->values();
        $minAmount = (float) AppSetting::getWithdrawMinAmount();
        $amountVal = (float) ($this->amount ?: 0);
        $feeCalc = AppSetting::calculateWithdrawFee($this->bankCode ?: 'BCA', (int) $amountVal);
        $adminFee = (float) $feeCalc['fee'];
        $netAmount = (float) $feeCalc['net_amount'];
        $totalDeduction = (float) ($amountVal > 0 ? ($amountVal + $adminFee) : 0);

        return view('livewire.mitra.withdraw.withdraw-form', [
            'balance' => $balance,
            'banks' => $banks,
            'minAmount' => $minAmount,
            'adminFee' => $adminFee,
            'netAmount' => $netAmount,
            'totalDeduction' => $totalDeduction,
            'isPlatform' => $feeCalc['is_platform_account'],
            'selectedBankName' => $feeCalc['bank_name'],
        ])->layout('layouts.mitra');
    }
}
