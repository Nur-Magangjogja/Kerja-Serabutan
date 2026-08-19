<?php

namespace App\Livewire\Customer;

use App\Models\BalanceTransaction;
use App\Models\User;
use App\Models\AppSetting;
use App\Notifications\TopupRequestSubmitted;
use App\Notifications\NewTopupRequest;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class TopupRequest extends Component
{
    use WithFileUploads;

    // Step management
    public $currentStep = 1;

    // Step 1 - Form data
    public $amount;
    public $customerName;
    public $customerPhone;
    public $customerEmail;
    public $customerNotes;

    // Step 2 - Payment detail (calculated)
    public $adminFee = 0;
    public $totalPayment = 0;
    // Unique 3-digit code and final transfer amount (includes code)
    public $uniqueCode = null;
    public $uniqueTotal = 0;

    // Step 3 - Payment method
    public $paymentMethod;
    public $proofOfPayment;
    public $proofPreview;

    // Others
    public $requestCode;
    public $transactionId;
    public $availableBanks = [];
    public $qrisEnabled = false;

    protected $rules = [
        'amount' => 'required|numeric|min:10000|max:10000000',
        'customerName' => 'required|string|max:100',
        'customerPhone' => 'required|numeric|digits_between:10,15',
        'customerEmail' => 'nullable|email|max:100',
        'customerNotes' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'amount.required' => 'Nominal harus diisi',
        'amount.min' => 'Minimal top-up adalah Rp 10.000',
        'amount.max' => 'Maksimal top-up adalah Rp 10.000.000',
        'customerName.required' => 'Nama lengkap harus diisi',
        'customerPhone.required' => 'Nomor telepon harus diisi',
        'customerPhone.digits_between' => 'Nomor telepon tidak valid',
        'customerEmail.email' => 'Format email tidak valid',
    ];

    public function mount()
    {
        $user = auth()->user();
        
        // Load from session if exists
        $sessionData = session('topup_form_data');
        
        if ($sessionData) {
            $this->currentStep = $sessionData['currentStep'] ?? 1;
            $this->amount = $sessionData['amount'] ?? null;
            $this->customerName = $sessionData['customerName'] ?? $user->name;
            $this->customerPhone = $sessionData['customerPhone'] ?? ($user->phone ?? '');
            $this->customerEmail = $sessionData['customerEmail'] ?? $user->email;
            $this->customerNotes = $sessionData['customerNotes'] ?? null;
            $this->paymentMethod = $sessionData['paymentMethod'] ?? null;
            $this->adminFee = $sessionData['adminFee'] ?? 0;
            $this->totalPayment = $sessionData['totalPayment'] ?? 0;
            $this->uniqueCode = $sessionData['uniqueCode'] ?? null;
            $this->uniqueTotal = $sessionData['uniqueTotal'] ?? 0;
        } else {
            $this->customerName = $user->name;
            $this->customerPhone = $user->phone ?? '';
            $this->customerEmail = $user->email;
        }

        $this->loadPaymentSettings();
    }

    protected function loadPaymentSettings()
    {
        // Prefer settings stored in AppSetting under 'topup_payment_methods'
        $raw = AppSetting::get('topup_payment_methods', '{}');
        $methods = json_decode((string) $raw, true) ?: [];

        // QRIS settings (optional)
        $this->qrisEnabled = $methods['qris']['enabled'] ?? true;

        // Banks: ensure fallback defaults if none configured
        $defaultBanks = [
            ['code' => 'bca', 'name' => 'BCA', 'account_number' => '1234567890', 'account_name' => 'PT sayabantu', 'enabled' => true],
            ['code' => 'mandiri', 'name' => 'Mandiri', 'account_number' => '0987654321', 'account_name' => 'PT sayabantu', 'enabled' => true],
            ['code' => 'bni', 'name' => 'BNI', 'account_number' => '5555666677', 'account_name' => 'PT sayabantu', 'enabled' => true],
            ['code' => 'bri', 'name' => 'BRI', 'account_number' => '8888999900', 'account_name' => 'PT sayabantu', 'enabled' => true],
        ];

        $banks = $methods['banks'] ?? $defaultBanks;

        // Normalize banks to include a `value` used as paymentMethod identifier (e.g. bank_bca)
        $this->availableBanks = collect($banks)
            ->filter(fn($bank) => $bank['enabled'] ?? false)
            ->map(fn($bank) => array_merge($bank, ['value' => 'bank_' . ($bank['code'] ?? '')]))
            ->values()
            ->toArray();
    }

    public function setQuickAmount($amount)
    {
        $this->amount = $amount;
        $this->calculateFees();
    }

    public function calculateFees()
    {
        if (!$this->amount) {
            $this->adminFee = 0;
            $this->totalPayment = 0;
            return;
        }

        $amount = floatval($this->amount);

        // Load settings from database
        $tier1_limit = (int) AppSetting::get('topup_tier1_limit', 50000);
        $tier1_fee = (int) AppSetting::get('topup_tier1_fee', 5000);
        $tier2_limit = (int) AppSetting::get('topup_tier2_limit', 100000);
        $tier2_fee = (int) AppSetting::get('topup_tier2_fee', 7500);
        $tier3_percentage = (float) AppSetting::get('topup_tier3_percentage', 3);
        $tier3_max = (int) AppSetting::get('topup_tier3_max', 15000);

        // Logika biaya admin berdasarkan tier
        if ($amount < $tier1_limit) {
            $this->adminFee = $tier1_fee;
        } elseif ($amount < $tier2_limit) {
            $this->adminFee = $tier2_fee;
        } else {
            $fee = $amount * ($tier3_percentage / 100);
            $this->adminFee = min($fee, $tier3_max);
        }

        $this->totalPayment = $amount + $this->adminFee;
        // generate or refresh unique code for this total payment
        $this->ensureUniqueSuffix();
        $this->saveFormData();
    }

    protected function saveFormData()
    {
        session([
            'topup_form_data' => [
                'currentStep' => $this->currentStep,
                'amount' => $this->amount,
                'customerName' => $this->customerName,
                'customerPhone' => $this->customerPhone,
                'customerEmail' => $this->customerEmail,
                'customerNotes' => $this->customerNotes,
                'paymentMethod' => $this->paymentMethod,
                'adminFee' => $this->adminFee,
                'totalPayment' => $this->totalPayment,
                'uniqueCode' => $this->uniqueCode,
                'uniqueTotal' => $this->uniqueTotal,
            ]
        ]);
    }

    public function resetFormData()
    {
        session()->forget('topup_form_data');
        
        $user = auth()->user();
        $this->currentStep = 1;
        $this->amount = null;
        $this->customerName = $user->name;
        $this->customerPhone = $user->phone ?? '';
        $this->customerEmail = $user->email;
        $this->customerNotes = null;
        $this->paymentMethod = null;
        $this->proofOfPayment = null;
        $this->adminFee = 0;
        $this->totalPayment = 0;
        
        session()->flash('success', 'Data form berhasil direset');
    }

    public function nextStep()
    {
        if ($this->currentStep == 1) {
            $this->validate();
            $this->calculateFees();
            $this->currentStep = 2;
        } elseif ($this->currentStep == 2) {
            $this->currentStep = 3;
        }
        $this->saveFormData();
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            $this->saveFormData();
        }
    }

    public function selectPaymentMethod($method)
    {
        $this->paymentMethod = $method;
        $this->saveFormData();
    }

    public function submitRequest()
    {
        // Validate step 3
        $this->validate([
            'paymentMethod' => 'required',
            'proofOfPayment' => 'required|image|max:2048|mimes:jpg,jpeg,png',
        ], [
            'paymentMethod.required' => 'Silakan pilih metode pembayaran',
            'proofOfPayment.required' => 'Bukti transfer wajib diupload',
            'proofOfPayment.image' => 'File harus berupa gambar (JPG, JPEG, PNG)',
            'proofOfPayment.mimes' => 'Format file harus berupa JPG, JPEG, atau PNG',
            'proofOfPayment.max' => 'Ukuran file maksimal 2MB',
        ]);

        // Verify selected payment method exists
        $allowed = array_merge(
            $this->qrisEnabled ? ['qris'] : [],
            array_map(fn($b) => $b['value'], $this->availableBanks)
        );

        if (!in_array($this->paymentMethod, $allowed)) {
            session()->flash('error', 'Metode pembayaran tidak valid');
            return;
        }

        try {
            // Upload proof of payment
            $proofPath = $this->proofOfPayment->store('proof-of-payment', 'public');

            // Generate request code
            $this->requestCode = $this->generateRequestCode();

            // Ensure unique suffix exists before creating transaction
            $this->ensureUniqueSuffix();

            // Create transaction (store total_payment as uniqueTotal so admin can verify exact transfer)
            $transaction = BalanceTransaction::create([
                'user_id' => auth()->id(),
                'amount' => $this->amount,
                'admin_fee' => $this->adminFee,
                'total_payment' => $this->uniqueTotal ?: $this->totalPayment,
                'type' => 'topup',
                'description' => 'Top-up saldo via ' . $this->getPaymentMethodName(),
                'status' => 'waiting_approval',
                'customer_name' => $this->customerName,
                'customer_phone' => $this->customerPhone,
                'customer_email' => $this->customerEmail,
                'payment_method' => $this->paymentMethod,
                'proof_of_payment' => $proofPath,
                'request_code' => $this->requestCode,
                // store unique code in description so admins can quickly see it (no schema change required)
                'customer_notes' => ($this->customerNotes ? $this->customerNotes . ' | ' : '') . 'UniqueCode:' . ($this->uniqueCode ?? '000'),
                'expired_at' => now()->addHours(24),
            ]);

            $this->transactionId = $transaction->id;

            // Send notification to customer
            auth()->user()->notify(new TopupRequestSubmitted($transaction));

            // Send notification to admin based on city
            $this->notifyAdmins($transaction);

            // Broadcast event to admin approval page (global event)
            $this->dispatch('topupRequestCreated');

            // Clear session data after successful submission
            session()->forget('topup_form_data');

            session()->flash('success', 'Request top-up berhasil dikirim! Kode request: ' . $this->requestCode);

            return redirect()->route('customer.topup.history');

        } catch (\Exception $e) {
            \Log::error('Topup request error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    protected function generateRequestCode()
    {
        $date = now()->format('Ymd');
        $lastCode = BalanceTransaction::where('request_code', 'like', "TPU-{$date}-%")
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastCode) {
            $parts = explode('-', $lastCode->request_code);
            $sequence = intval($parts[2] ?? 0) + 1;
        }

        return "TPU-{$date}-" . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Ensure a 3-digit unique suffix exists and compute uniqueTotal.
     * The uniqueTotal is calculated from the nearest lower thousand plus the 3-digit code.
     * If that produces a value lower than the calculated totalPayment, bump the base by 1000.
     */
    protected function ensureUniqueSuffix()
    {
        if (!$this->totalPayment) {
            $this->uniqueCode = null;
            $this->uniqueTotal = 0;
            return;
        }

        $total = (int) round($this->totalPayment);

        $base = intdiv($total, 1000) * 1000; // nearest lower thousand

        // If we already have a uniqueCode and base matches, verify uniqueTotal still valid
        if ($this->uniqueCode) {
            $existingBase = intdiv((int) $this->uniqueTotal, 1000) * 1000;
            if ($existingBase === $base && (int) $this->uniqueTotal >= $total) {
                return; // still valid
            }
        }

        // Generate a random 3-digit code
        try {
            $code = random_int(1, 999);
        } catch (\Exception $e) {
            $code = mt_rand(1, 999);
        }

        $padded = str_pad($code, 3, '0', STR_PAD_LEFT);
        $uniqueTotal = $base + $code;

        if ($uniqueTotal < $total) {
            // bump base by 1k to ensure uniqueTotal >= total
            $base += 1000;
            $uniqueTotal = $base + $code;
        }

        $this->uniqueCode = $padded;
        $this->uniqueTotal = $uniqueTotal;
    }

    protected function getPaymentMethodName()
    {
        if ($this->paymentMethod === 'qris') {
            return 'QRIS';
        }

        if (str_starts_with($this->paymentMethod, 'bank_')) {
            $code = substr($this->paymentMethod, 5);
            $bank = collect($this->availableBanks)->first(fn($b) => ($b['code'] ?? '') === $code);
            if ($bank) {
                return 'Transfer Bank ' . ($bank['name'] ?? strtoupper($code));
            }
        }

        return 'Transfer Bank';
    }

    protected function notifyAdmins($transaction)
    {
        // Get admin users based on customer's city
        $customerCity = auth()->user()->city_id;

        $admins = User::where('role', 'admin')
            ->where('city_id', $customerCity)
            ->where('status', 'active')
            ->get();

        // If no city-specific admin, notify super admin
        if ($admins->isEmpty()) {
            $admins = User::where('role', 'superadmin')
                ->where('status', 'active')
                ->get();
        }

        foreach ($admins as $admin) {
            $admin->notify(new NewTopupRequest($transaction));
        }
    }

    public function render()
    {
        return view('livewire.customer.topup-request');
    }
}
