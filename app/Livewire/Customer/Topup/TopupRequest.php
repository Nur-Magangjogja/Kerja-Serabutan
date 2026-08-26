<?php

namespace App\Livewire\Customer\Topup;

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

    // Step 2 - Payment detail (No Admin Fee / 0% Tax)
    public $adminFee = 0;
    public $totalPayment = 0;
    public $uniqueCode = null;
    public $uniqueTotal = 0;

    // Step 3 - QRIS Payment
    public $paymentMethod = 'qris';
    public $proofOfPayment;
    public $proofPreview;

    // QRIS Settings
    public $qrisImage;
    public $qrisMerchantName;
    public $qrisNmid;
    public $qrisInstructions;
    public $qrisEnabled = true;

    // Others
    public $requestCode;
    public $transactionId;

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
        
        // Load QRIS configuration from AppSetting
        $this->loadPaymentSettings();

        // Load from session if exists
        $sessionData = session('topup_form_data');
        
        if ($sessionData) {
            $this->currentStep = $sessionData['currentStep'] ?? 1;
            $this->amount = $sessionData['amount'] ?? null;
            $this->customerName = $sessionData['customerName'] ?? $user->name;
            $this->customerPhone = $sessionData['customerPhone'] ?? ($user->phone ?? '');
            $this->customerEmail = $sessionData['customerEmail'] ?? $user->email;
            $this->customerNotes = $sessionData['customerNotes'] ?? null;
            $this->paymentMethod = 'qris';
            $this->adminFee = 0;
            $this->totalPayment = $this->amount ? floatval($this->amount) : 0;
            $this->uniqueCode = null;
            $this->uniqueTotal = $this->totalPayment;
        } else {
            $this->customerName = $user->name;
            $this->customerPhone = $user->phone ?? '';
            $this->customerEmail = $user->email;
        }
    }

    protected function loadPaymentSettings()
    {
        $this->qrisImage = AppSetting::get('topup_qris_image', null);
        if ($this->qrisImage === 'images/payment/qris.png') {
            $this->qrisImage = null;
        }
        $this->qrisMerchantName = AppSetting::get('topup_qris_merchant_name', 'PT SayaBantu');
        $this->qrisNmid = AppSetting::get('topup_qris_nmid', '');
        $this->qrisInstructions = AppSetting::get(
            'topup_qris_instructions',
            'Scan kode QRIS di atas menggunakan aplikasi mobile banking (BCA, Mandiri, BRI, BNI) atau e-wallet (GoPay, OVO, DANA, LinkAja, ShopeePay).'
        );
        $this->qrisEnabled = !empty($this->qrisImage);
        $this->paymentMethod = 'qris';
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
            $this->uniqueTotal = 0;
            return;
        }

        $amount = floatval($this->amount);

        // Top-up saldo 100% bebas biaya admin / pajak
        $this->adminFee = 0;
        $this->totalPayment = $amount;
        $this->uniqueCode = null;
        $this->uniqueTotal = $this->totalPayment;

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
                'paymentMethod' => 'qris',
                'adminFee' => 0,
                'totalPayment' => $this->totalPayment,
                'uniqueCode' => null,
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
        $this->paymentMethod = 'qris';
        $this->proofOfPayment = null;
        $this->adminFee = 0;
        $this->totalPayment = 0;
        $this->uniqueTotal = 0;
        
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
        $this->paymentMethod = 'qris';
        $this->saveFormData();
    }

    public function submitRequest()
    {
        $this->loadPaymentSettings();
        if (empty($this->qrisImage) || !$this->qrisEnabled) {
            $this->addError('proofOfPayment', 'Metode pembayaran QRIS sedang tidak tersedia / belum diatur admin. Silakan hubungi customer service.');
            return;
        }

        // Validate step 3 proof of payment
        $this->validate([
            'proofOfPayment' => 'required|image|max:2048|mimes:jpg,jpeg,png',
        ], [
            'proofOfPayment.required' => 'Bukti pembayaran QRIS wajib diupload',
            'proofOfPayment.image' => 'File harus berupa gambar (JPG, JPEG, PNG)',
            'proofOfPayment.mimes' => 'Format file harus berupa JPG, JPEG, atau PNG',
            'proofOfPayment.max' => 'Ukuran file maksimal 2MB',
        ]);

        try {
            // Upload proof of payment
            $proofPath = $this->proofOfPayment->store('proof-of-payment', 'public');

            // Generate request code
            $this->requestCode = $this->generateRequestCode();

            // Create transaction (total_payment = amount, admin_fee = 0)
            $transaction = BalanceTransaction::create([
                'user_id' => auth()->id(),
                'amount' => $this->amount,
                'admin_fee' => 0,
                'total_payment' => $this->amount,
                'type' => 'topup',
                'description' => 'Top-up saldo via QRIS',
                'status' => 'waiting_approval',
                'customer_name' => $this->customerName,
                'customer_phone' => $this->customerPhone,
                'customer_email' => $this->customerEmail,
                'payment_method' => 'qris',
                'proof_of_payment' => $proofPath,
                'request_code' => $this->requestCode,
                'customer_notes' => $this->customerNotes ?: null,
                'expired_at' => now()->addHours(24),
            ]);

            // Send notification to customer safely
            try {
                auth()->user()->notify(new TopupRequestSubmitted($transaction));
            } catch (\Throwable $e) {
                \Log::warning('Topup notification to customer failed: ' . $e->getMessage());
            }

            // Send notification to admin based on city safely
            try {
                $this->notifyAdmins($transaction);
            } catch (\Throwable $e) {
                \Log::warning('Topup notification to admin failed: ' . $e->getMessage());
            }

            // Broadcast event to admin approval page (global event)
            $this->dispatch('topupRequestCreated');

            // Clear session data after successful submission
            session()->forget('topup_form_data');

            session()->flash('success', 'Request top-up berhasil dikirim! Kode request: ' . $this->requestCode);

            return $this->redirectRoute('customer.transactions.index', navigate: true);

        } catch (\Exception $e) {
            \Log::error('Topup request error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat memproses top-up: ' . $e->getMessage());
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
        return view('livewire.customer.topup.request');
    }
}
