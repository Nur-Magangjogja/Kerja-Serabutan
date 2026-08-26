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
    public $help_auto_cancel_hours = 24;
    public $admin_fee; // legacy

    // QRIS Top-Up Settings (QRIS Tunggal)
    public $qris_image;
    public $existing_qris_image;
    public $qris_merchant_name;
    public $qris_nmid;
    public $qris_instructions;

    protected function rules()
    {
        return [
            'min_help_nominal' => 'required|numeric|min:0',
            'platform_service_fee' => 'required|numeric|min:0',
            'help_auto_cancel_hours' => 'required|integer|min:1|max:168',
            'admin_fee' => 'nullable|numeric|min:0',
            'qris_image' => 'nullable|image|max:3072|mimes:jpg,jpeg,png,webp',
            'qris_merchant_name' => 'required|string|max:150',
            'qris_nmid' => 'nullable|string|max:100',
            'qris_instructions' => 'nullable|string|max:500',
        ];
    }

    protected function messages()
    {
        return [
            'min_help_nominal.required' => 'Nominal minimal bantuan tidak boleh kosong.',
            'min_help_nominal.numeric' => 'Nominal minimal bantuan harus berupa angka.',
            'platform_service_fee.required' => 'Biaya layanan platform tidak boleh kosong.',
            'platform_service_fee.numeric' => 'Biaya layanan platform harus berupa angka.',
            'help_auto_cancel_hours.required' => 'Batas waktu pembatalan otomatis tidak boleh kosong.',
            'help_auto_cancel_hours.integer' => 'Batas waktu harus berupa bilangan bulat (jam).',
            'help_auto_cancel_hours.min' => 'Batas waktu minimal 1 jam.',
            'qris_image.image' => 'File QRIS harus berupa gambar.',
            'qris_image.max' => 'Ukuran gambar QRIS maksimal 3MB.',
            'qris_image.mimes' => 'Format gambar QRIS harus JPG, JPEG, PNG, atau WEBP.',
            'qris_merchant_name.required' => 'Nama Merchant / Akun QRIS wajib diisi.',
        ];
    }

    public function mount()
    {
        $this->min_help_nominal = (int) AppSetting::get('min_help_nominal', 10000);
        $this->platform_service_fee = (int) AppSetting::getPlatformServiceFee();
        $this->help_auto_cancel_hours = AppSetting::getHelpAutoCancelHours();
        $this->admin_fee = (float) AppSetting::get('admin_fee', 0);
        
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
        AppSetting::set('help_auto_cancel_hours', (string) $this->help_auto_cancel_hours);
        AppSetting::set('platform_fee_type', 'fixed');
        AppSetting::set('platform_commission_rate', '0');
        if ($this->admin_fee !== null) {
            AppSetting::set('admin_fee', (string) $this->admin_fee);
        }

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
        // Platform fee from customer help requests
        $adminFeeChart = [
            'daily' => ['labels' => [], 'data' => []],
            'monthly' => ['labels' => [], 'data' => []],
            'yearly' => ['labels' => [], 'data' => []],
        ];

        // Daily - last 30 days
        $days = 30;
        $startDay = Carbon::today()->subDays($days - 1);
        for ($i = 0; $i < $days; $i++) {
            $d = $startDay->copy()->addDays($i);
            $label = $d->format('d M');
            $helpAdminFee = (float) Help::whereDate('created_at', $d->toDateString())
                ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(NULLIF(platform_fee_amount, 0), admin_fee)'));
            $adminFeeChart['daily']['labels'][] = $label;
            $adminFeeChart['daily']['data'][] = $helpAdminFee;
        }

        // Monthly - last 12 months
        $months = 12;
        $startMonth = Carbon::now()->startOfMonth()->subMonths($months - 1);
        for ($i = 0; $i < $months; $i++) {
            $m = $startMonth->copy()->addMonths($i);
            $label = $m->format('M Y');
            $helpAdminFee = (float) Help::whereYear('created_at', $m->year)
                ->whereMonth('created_at', $m->month)
                ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(NULLIF(platform_fee_amount, 0), admin_fee)'));
            $adminFeeChart['monthly']['labels'][] = $label;
            $adminFeeChart['monthly']['data'][] = $helpAdminFee;
        }

        // Yearly - last 5 years
        $years = 5;
        $startYear = Carbon::now()->startOfYear()->subYears($years - 1);
        for ($i = 0; $i < $years; $i++) {
            $y = $startYear->copy()->addYears($i);
            $label = (string) $y->year;
            $helpAdminFee = (float) Help::whereYear('created_at', $y->year)
                ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(NULLIF(platform_fee_amount, 0), admin_fee)'));
            $adminFeeChart['yearly']['labels'][] = $label;
            $adminFeeChart['yearly']['data'][] = $helpAdminFee;
        }

        // Summary stats from Customer Help Platform Fees
        $totalAll = (float) Help::sum(\Illuminate\Support\Facades\DB::raw('COALESCE(NULLIF(platform_fee_amount, 0), admin_fee)'));
        
        $total30 = (float) Help::whereDate('created_at', '>=', Carbon::today()->subDays(29))
            ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(NULLIF(platform_fee_amount, 0), admin_fee)'));
        
        $totalMonth = (float) Help::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(NULLIF(platform_fee_amount, 0), admin_fee)'));
        
        $helpsWithFee = (int) Help::whereRaw('COALESCE(NULLIF(platform_fee_amount, 0), admin_fee) > 0')->count();
        $avgAdmin = $helpsWithFee ? ($totalAll / $helpsWithFee) : 0;

        return view('livewire.superadmin.settings.help-settings', compact(
            'adminFeeChart',
            'totalAll',
            'total30',
            'totalMonth',
            'helpsWithFee',
            'avgAdmin'
        ));
    }
}
