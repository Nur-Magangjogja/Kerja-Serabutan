<?php

namespace App\Livewire\SuperAdmin\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\AppSetting;
use App\Models\Help;
use App\Models\BalanceTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.superadmin')]
class HelpSettings extends Component
{
    use WithFileUploads;

    public $min_help_nominal;
    public $platform_service_fee = 2000;
    public $admin_fee; // legacy

    // Matching & Fairness Calibration Properties
    public $offer_timeout_seconds = 45;
    public $max_dispatch_candidates = 5;
    public $heartbeat_ttl_seconds = 60;
    public $max_matching_radius_km = 15.0;
    public $neutral_rating_prior = 4.5;
    public $rating_min_votes = 5;
    public $weight_distance = 0.35;
    public $weight_rating = 0.30;
    public $weight_reliability = 0.25;
    public $weight_fairness = 0.10;
    public $max_fairness_boost_minutes = 60;

    // QRIS Top-Up Settings (QRIS Tunggal)
    public $qris_image;
    public $existing_qris_image;
    public $qris_merchant_name;
    public $qris_nmid;
    public $qris_instructions;

    protected function rules()
    {
        return [
            'min_help_nominal'           => 'required|numeric|min:0',
            'platform_service_fee'       => 'required|numeric|min:0',
            'admin_fee'                  => 'nullable|numeric|min:0',
            'offer_timeout_seconds'      => 'required|integer|min:15|max:120',
            'max_dispatch_candidates'    => 'required|integer|min:1|max:30',
            'heartbeat_ttl_seconds'      => 'required|integer|min:30|max:300',
            'max_matching_radius_km'     => 'required|numeric|min:1|max:100',
            'neutral_rating_prior'       => 'required|numeric|min:3.0|max:5.0',
            'rating_min_votes'           => 'required|integer|min:1|max:50',
            'weight_distance'            => 'required|numeric|min:0|max:1',
            'weight_rating'              => 'required|numeric|min:0|max:1',
            'weight_reliability'         => 'required|numeric|min:0|max:1',
            'weight_fairness'            => 'required|numeric|min:0|max:1',
            'max_fairness_boost_minutes' => 'required|numeric|min:10|max:240',
            'qris_image'                 => 'nullable|image|max:3072|mimes:jpg,jpeg,png,webp',
            'qris_merchant_name'         => 'required|string|max:150',
            'qris_nmid'                  => 'nullable|string|max:100',
            'qris_instructions'          => 'nullable|string|max:500',
        ];
    }

    protected function messages()
    {
        return [
            'min_help_nominal.required'     => 'Nominal minimal bantuan tidak boleh kosong.',
            'min_help_nominal.numeric'      => 'Nominal minimal bantuan harus berupa angka.',
            'platform_service_fee.required' => 'Biaya layanan platform tidak boleh kosong.',
            'platform_service_fee.numeric'  => 'Biaya layanan platform harus berupa angka.',
            'offer_timeout_seconds.min'     => 'Batas waktu respon penawaran minimal 15 detik.',
            'offer_timeout_seconds.max'     => 'Batas waktu respon penawaran maksimal 120 detik.',
            'qris_image.image'              => 'File QRIS harus berupa gambar.',
            'qris_image.max'                => 'Ukuran gambar QRIS maksimal 3MB.',
            'qris_image.mimes'              => 'Format gambar QRIS harus JPG, JPEG, PNG, atau WEBP.',
            'qris_merchant_name.required'   => 'Nama Merchant / Akun QRIS wajib diisi.',
        ];
    }

    public function mount()
    {
        $this->min_help_nominal     = (int) AppSetting::get('min_help_nominal', 10000);
        $this->platform_service_fee = (int) AppSetting::getPlatformServiceFee();
        $this->admin_fee            = (float) AppSetting::get('admin_fee', 0);

        // Load Matching & Fairness settings
        $this->offer_timeout_seconds      = AppSetting::getOfferTimeoutSeconds();
        $this->max_dispatch_candidates    = AppSetting::getMaxDispatchCandidates();
        $this->heartbeat_ttl_seconds      = AppSetting::getHeartbeatTtlSeconds();
        $this->max_matching_radius_km     = AppSetting::getMaxMatchingRadiusKm();
        $this->neutral_rating_prior       = AppSetting::getNeutralRatingPrior();
        $this->rating_min_votes           = AppSetting::getRatingMinVotes();
        $weights                          = AppSetting::getMatchingWeights();
        $this->weight_distance            = $weights['distance'];
        $this->weight_rating              = $weights['rating'];
        $this->weight_reliability         = $weights['reliability'];
        $this->weight_fairness            = $weights['fairness'];
        $this->max_fairness_boost_minutes = AppSetting::getMaxFairnessBoostMinutes();
        
        // Load QRIS settings (tanpa default asset agar kosong jika belum diisi)
        $this->existing_qris_image = AppSetting::get('topup_qris_image', null);
        if ($this->existing_qris_image === 'images/payment/qris.png') {
            $this->existing_qris_image = null;
        }
        $this->qris_merchant_name = AppSetting::get('topup_qris_merchant_name', 'PT SayaBantu');
        $this->qris_nmid = AppSetting::get('topup_qris_nmid', '');
        $this->qris_instructions = AppSetting::get(
            'topup_qris_instructions',
            'Scan kode QRIS di atas menggunakan aplikasi mobile banking (BCA, Mandiri, BRI, BNI) atau e-wallet (GoPay, OVO, DANA, LinkAja, ShopeePay).'
        );
    }

    public function save()
    {
        $this->validate();

        AppSetting::set('min_help_nominal', (string) $this->min_help_nominal);
        AppSetting::set('platform_service_fee', (string) $this->platform_service_fee);
        AppSetting::set('platform_fixed_fee', (string) $this->platform_service_fee);
        AppSetting::set('platform_fee_type', 'fixed');
        AppSetting::set('platform_commission_rate', '0');
        if ($this->admin_fee !== null) {
            AppSetting::set('admin_fee', (string) $this->admin_fee);
        }

        // Save Matching & Fairness settings
        AppSetting::set('offer_timeout_seconds', (string) $this->offer_timeout_seconds);
        AppSetting::set('max_dispatch_candidates', (string) $this->max_dispatch_candidates);
        AppSetting::set('heartbeat_ttl_seconds', (string) $this->heartbeat_ttl_seconds);
        AppSetting::set('max_matching_radius_km', (string) $this->max_matching_radius_km);
        AppSetting::set('neutral_rating_prior', (string) $this->neutral_rating_prior);
        AppSetting::set('rating_min_votes', (string) $this->rating_min_votes);
        AppSetting::set('weight_distance', (string) $this->weight_distance);
        AppSetting::set('weight_rating', (string) $this->weight_rating);
        AppSetting::set('weight_reliability', (string) $this->weight_reliability);
        AppSetting::set('weight_fairness', (string) $this->weight_fairness);
        AppSetting::set('max_fairness_boost_minutes', (string) $this->max_fairness_boost_minutes);

        // Handle QRIS Image Upload
        if ($this->qris_image) {
            // Delete old uploaded image if stored in public storage disk
            if ($this->existing_qris_image && !str_starts_with($this->existing_qris_image, 'images/') && Storage::disk('public')->exists($this->existing_qris_image)) {
                Storage::disk('public')->delete($this->existing_qris_image);
            }

            $path = $this->qris_image->store('payment-qris', 'public');
            AppSetting::set('topup_qris_image', $path);
            $this->existing_qris_image = $path;
            $this->qris_image = null;
        }

        $isQrisActive = !empty($this->existing_qris_image);

        // Save QRIS Settings
        AppSetting::set('topup_qris_merchant_name', trim($this->qris_merchant_name));
        AppSetting::set('topup_qris_nmid', trim((string) $this->qris_nmid));
        AppSetting::set('topup_qris_instructions', trim((string) $this->qris_instructions));
        AppSetting::set('topup_qris_enabled', $isQrisActive ? '1' : '0');

        // Zero out any topup tax/fee settings
        AppSetting::set('topup_tier1_limit', '0');
        AppSetting::set('topup_tier1_fee', '0');
        AppSetting::set('topup_tier2_limit', '0');
        AppSetting::set('topup_tier2_fee', '0');
        AppSetting::set('topup_tier3_percentage', '0');
        AppSetting::set('topup_tier3_max', '0');
        AppSetting::set('topup_admin_fee', '0');

        // Save JSON config for backward-compatibility
        $paymentMethods = [
            'qris' => [
                'enabled' => $isQrisActive,
                'image' => $this->existing_qris_image,
                'merchant_name' => $this->qris_merchant_name,
                'nmid' => $this->qris_nmid,
                'instructions' => $this->qris_instructions,
            ],
            'banks' => [], // Bank transfer removed
        ];
        AppSetting::set('topup_payment_methods', json_encode($paymentMethods));

        session()->flash('message', 'Pengaturan layanan bantuan dan metode pembayaran QRIS berhasil disimpan.');

        // Notify frontend
        $this->dispatch('settingsSaved', ['message' => 'Pengaturan layanan bantuan dan metode pembayaran QRIS berhasil disimpan.']);
    }

    public function removeQrisImage()
    {
        if ($this->existing_qris_image && !str_starts_with($this->existing_qris_image, 'images/') && Storage::disk('public')->exists($this->existing_qris_image)) {
            Storage::disk('public')->delete($this->existing_qris_image);
        }

        AppSetting::set('topup_qris_image', '');
        $this->existing_qris_image = null;
        $this->qris_image = null;

        session()->flash('message', 'Gambar QRIS berhasil dihapus. Barcode QRIS saat ini kosong.');
        $this->dispatch('settingsSaved', ['message' => 'Gambar QRIS berhasil dikosongkan.']);
    }

    public function render()
    {
        return view('livewire.superadmin.settings.help-settings');
    }
}
