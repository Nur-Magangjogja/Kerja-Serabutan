<?php

namespace App\Livewire\Customer\Topup;

use App\Models\BalanceTransaction;
use Livewire\Component;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    public $amount;
    public $method = 'bank';
    public $snapToken;
    public $orderId;
    public bool $isSubmitting = false;

    protected $rules = [
        'amount' => 'required|numeric|min:10000',
        'method' => 'required|string',
    ];

    protected $messages = [
        'amount.min' => 'Minimal top up adalah Rp 10.000',
    ];

    public function mount()
    {
        $this->amount = null;
    }

    public function submit()
    {
        if ($this->isSubmitting) {
            return;
        }

        $this->validate();

        $user = auth()->user();
        if (!$user) {
            return;
        }

        // Atomic lock (10s) untuk mencegah duplicate submit
        $lock = Cache::lock("user_midtrans_topup_submit_{$user->id}", 10);
        if (!$lock->get()) {
            return;
        }

        $this->isSubmitting = true;

        try {
            // Set Midtrans configuration
            Config::$serverKey = config('services.midtrans.server_key');
            Config::$isProduction = config('services.midtrans.is_production');
            Config::$isSanitized = config('services.midtrans.is_sanitized');
            Config::$is3ds = config('services.midtrans.is_3ds');

            // Validate Midtrans configuration
            $serverKey = config('services.midtrans.server_key');
            $clientKey = config('services.midtrans.client_key');

            if (
                empty($serverKey) ||
                $serverKey === 'your-server-key-here' ||
                $serverKey === 'SB-Mid-server-YOUR_SERVER_KEY_HERE'
            ) {
                session()->flash('error', 'Midtrans belum dikonfigurasi. Silakan daftar di https://dashboard.sandbox.midtrans.com/ dan masukkan kredensial Anda ke file .env');
                \Log::error('Midtrans Server Key not configured in .env file');
                $this->isSubmitting = false;
                $lock->release();
                return;
            }

            if (
                empty($clientKey) ||
                $clientKey === 'your-client-key-here' ||
                $clientKey === 'SB-Mid-client-YOUR_CLIENT_KEY_HERE'
            ) {
                session()->flash('error', 'Midtrans belum dikonfigurasi. Silakan daftar di https://dashboard.sandbox.midtrans.com/ dan masukkan kredensial Anda ke file .env');
                \Log::error('Midtrans Client Key not configured in .env file');
                $this->isSubmitting = false;
                $lock->release();
                return;
            }

            // Generate unique order ID
            $this->orderId = 'TOPUP-' . $user->id . '-' . time();

            // Create transaction record
            $transaction = BalanceTransaction::create([
                'user_id' => $user->id,
                'amount' => $this->amount,
                'type' => 'topup',
                'description' => 'Top up saldo via Midtrans',
                'order_id' => $this->orderId,
                'status' => 'pending',
            ]);

            // Prepare transaction params for Midtrans
            $params = [
                'transaction_details' => [
                    'order_id' => $this->orderId,
                    'gross_amount' => (int) $this->amount,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '08123456789',
                ],
                'enabled_payments' => $this->getEnabledPayments(),
                'callbacks' => [
                    'finish' => route('topup.finish'),
                    'unfinish' => route('topup.unfinish'),
                    'error' => route('topup.error'),
                ],
            ];

            \Log::info('Midtrans Request', [
                'server_key_prefix' => substr(Config::$serverKey, 0, 15),
                'is_production' => Config::$isProduction,
                'params' => $params,
            ]);

            // Get Snap token
            $this->snapToken = Snap::getSnapToken($params);

            \Log::info('Midtrans Snap Token Generated', [
                'snap_token' => substr($this->snapToken, 0, 20) . '...',
            ]);

            // Save snap token to transaction
            $transaction->snap_token = $this->snapToken;
            $transaction->save();

            // Store last order id in session so finish callback can use it if Midtrans doesn't include order_id
            session()->put('midtrans_last_order_id', $this->orderId);

            // Dispatch event to open Midtrans Snap
            $this->dispatch('openMidtransSnap', snapToken: $this->snapToken);

        } catch (\Exception $e) {
            $this->isSubmitting = false;
            if (isset($lock)) {
                $lock->release();
            }
            \Log::error('Midtrans error: ' . $e->getMessage());
            \Log::error('Error details', [
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        } finally {
            $this->isSubmitting = false;
        }
    }

    private function getEnabledPayments()
    {
        // Return payment methods based on selected method
        if ($this->method === 'bank') {
            return ['bank_transfer', 'echannel'];
        } elseif ($this->method === 'ewallet') {
            return ['gopay', 'shopeepay', 'qris'];
        }

        // Default: all payment methods
        return ['bank_transfer', 'echannel', 'gopay', 'shopeepay', 'qris'];
    }

    public function render()
    {
        return view('livewire.customer.topup.index');
    }
}
