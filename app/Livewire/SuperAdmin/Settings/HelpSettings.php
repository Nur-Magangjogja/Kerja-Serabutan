<?php

namespace App\Livewire\SuperAdmin\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\AppSetting;
use App\Models\Help;
use App\Models\BalanceTransaction;
use Carbon\Carbon;

#[Layout('layouts.superadmin')]
class HelpSettings extends Component
{
    public $min_help_nominal;
    public $admin_fee;
    
    // Top-up admin fee settings
    public $tier1_limit;
    public $tier1_fee;
    public $tier2_limit;
    public $tier2_fee;
    public $tier3_percentage;
    public $tier3_max;
    // Payment methods for top-up (banks + qris)
    public $payment_banks = [];

    protected function rules()
    {
        return [
            'min_help_nominal' => 'required|numeric|min:0',
            'admin_fee' => 'required|numeric|min:0',
            'tier1_limit' => 'required|numeric|min:0',
            'tier1_fee' => 'required|numeric|min:0',
            'tier2_limit' => 'required|numeric|min:0',
            'tier2_fee' => 'required|numeric|min:0',
            'tier3_percentage' => 'required|numeric|min:0|max:100',
            'tier3_max' => 'required|numeric|min:0',
            'payment_banks' => 'array',
            'payment_banks.*.code' => 'required|string|max:20',
            'payment_banks.*.name' => 'required|string|max:100',
            'payment_banks.*.account_number' => 'nullable|string|max:100',
            'payment_banks.*.account_name' => 'nullable|string|max:200',
            'payment_banks.*.enabled' => 'boolean',
        ];
    }

    public function mount()
    {
        $this->min_help_nominal = (int) AppSetting::get('min_help_nominal', 10000);
        $this->admin_fee = (float) AppSetting::get('admin_fee', 0);
        
        // Load top-up fee settings
        $this->tier1_limit = (int) AppSetting::get('topup_tier1_limit', 50000);
        $this->tier1_fee = (int) AppSetting::get('topup_tier1_fee', 5000);
        $this->tier2_limit = (int) AppSetting::get('topup_tier2_limit', 100000);
        $this->tier2_fee = (int) AppSetting::get('topup_tier2_fee', 7500);
        $this->tier3_percentage = (float) AppSetting::get('topup_tier3_percentage', 3);
        $this->tier3_max = (int) AppSetting::get('topup_tier3_max', 15000);

        // Load payment banks config (stored as JSON)
        $paymentMethods = json_decode((string) AppSetting::get('topup_payment_methods', '{}'), true) ?: [];
        $this->payment_banks = $paymentMethods['banks'] ?? [
            ['code' => 'bca', 'name' => 'BCA', 'account_number' => '1234567890', 'account_name' => 'PT sayabantu', 'enabled' => true],
            ['code' => 'mandiri', 'name' => 'Mandiri', 'account_number' => '0987654321', 'account_name' => 'PT sayabantu', 'enabled' => true],
            ['code' => 'bni', 'name' => 'BNI', 'account_number' => '5555666677', 'account_name' => 'PT sayabantu', 'enabled' => true],
            ['code' => 'bri', 'name' => 'BRI', 'account_number' => '8888999900', 'account_name' => 'PT sayabantu', 'enabled' => true],
        ];
    }

    public function save()
    {
        $this->validate();

        AppSetting::set('min_help_nominal', (string) $this->min_help_nominal);
        AppSetting::set('admin_fee', (string) $this->admin_fee);
        
        // Save top-up fee settings
        AppSetting::set('topup_tier1_limit', (string) $this->tier1_limit);
        AppSetting::set('topup_tier1_fee', (string) $this->tier1_fee);
        AppSetting::set('topup_tier2_limit', (string) $this->tier2_limit);
        AppSetting::set('topup_tier2_fee', (string) $this->tier2_fee);
        AppSetting::set('topup_tier3_percentage', (string) $this->tier3_percentage);
        AppSetting::set('topup_tier3_max', (string) $this->tier3_max);

        // Save payment banks
        $paymentMethods = [
            'banks' => array_values(array_map(function($b) {
                return [
                    'code' => (string) ($b['code'] ?? ''),
                    'name' => (string) ($b['name'] ?? ''),
                    'account_number' => isset($b['account_number']) ? (string) $b['account_number'] : null,
                    'account_name' => isset($b['account_name']) ? (string) $b['account_name'] : null,
                    'enabled' => !empty($b['enabled']) ? true : false,
                ];
            }, $this->payment_banks ?? [])),
        ];

        AppSetting::set('topup_payment_methods', json_encode($paymentMethods));
        session()->flash('message', 'Pengaturan bantuan dan biaya top-up berhasil disimpan.');

        // Notify frontend to show a transient confirmation modal using app's dispatch helper
        $this->dispatch('settingsSaved', ['message' => 'Pengaturan bantuan dan biaya top-up berhasil disimpan.']);
    }

    public function addBank()
    {
        $this->payment_banks[] = ['code' => '', 'name' => '', 'account_number' => '', 'account_name' => '', 'enabled' => true];
    }

    public function removeBank($index)
    {
        if (isset($this->payment_banks[$index])) {
            unset($this->payment_banks[$index]);
            $this->payment_banks = array_values($this->payment_banks);
        }
    }

    public function render()
    {
        // Prepare admin fee revenue chart data: daily (30 days), monthly (12 months), yearly (5 years)
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
            // Sum admin fees from both Help and BalanceTransaction
            $helpAdminFee = (float) Help::whereDate('created_at', $d->toDateString())->sum('admin_fee');
            $topupAdminFee = (float) BalanceTransaction::whereDate('created_at', $d->toDateString())
                ->where('status', 'completed')
                ->sum('admin_fee');
            $sum = $helpAdminFee + $topupAdminFee;
            $adminFeeChart['daily']['labels'][] = $label;
            $adminFeeChart['daily']['data'][] = $sum;
        }

        // Monthly - last 12 months
        $months = 12;
        $startMonth = Carbon::now()->startOfMonth()->subMonths($months - 1);
        for ($i = 0; $i < $months; $i++) {
            $m = $startMonth->copy()->addMonths($i);
            $label = $m->format('M Y');
            // Sum admin fees from both Help and BalanceTransaction
            $helpAdminFee = (float) Help::whereYear('created_at', $m->year)
                ->whereMonth('created_at', $m->month)
                ->sum('admin_fee');
            $topupAdminFee = (float) BalanceTransaction::whereYear('created_at', $m->year)
                ->whereMonth('created_at', $m->month)
                ->where('status', 'completed')
                ->sum('admin_fee');
            $sum = $helpAdminFee + $topupAdminFee;
            $adminFeeChart['monthly']['labels'][] = $label;
            $adminFeeChart['monthly']['data'][] = $sum;
        }

        // Yearly - last 5 years
        $years = 5;
        $startYear = Carbon::now()->startOfYear()->subYears($years - 1);
        for ($i = 0; $i < $years; $i++) {
            $y = $startYear->copy()->addYears($i);
            $label = (string) $y->year;
            // Sum admin fees from both Help and BalanceTransaction
            $helpAdminFee = (float) Help::whereYear('created_at', $y->year)->sum('admin_fee');
            $topupAdminFee = (float) BalanceTransaction::whereYear('created_at', $y->year)
                ->where('status', 'completed')
                ->sum('admin_fee');
            $sum = $helpAdminFee + $topupAdminFee;
            $adminFeeChart['yearly']['labels'][] = $label;
            $adminFeeChart['yearly']['data'][] = $sum;
        }

        // Summary stats - combine admin fees from Help and BalanceTransaction
        $helpAdminFeeTotal = (float) Help::sum('admin_fee');
        $topupAdminFeeTotal = (float) BalanceTransaction::where('status', 'completed')->sum('admin_fee');
        $totalAll = $helpAdminFeeTotal + $topupAdminFeeTotal;
        
        $helpAdminFee30 = (float) Help::whereDate('created_at', '>=', Carbon::today()->subDays(29))->sum('admin_fee');
        $topupAdminFee30 = (float) BalanceTransaction::whereDate('created_at', '>=', Carbon::today()->subDays(29))
            ->where('status', 'completed')
            ->sum('admin_fee');
        $total30 = $helpAdminFee30 + $topupAdminFee30;
        
        $helpAdminFeeMonth = (float) Help::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('admin_fee');
        $topupAdminFeeMonth = (float) BalanceTransaction::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->where('status', 'completed')
            ->sum('admin_fee');
        $totalMonth = $helpAdminFeeMonth + $topupAdminFeeMonth;
        
        // Count transactions with admin fee
        $helpsWithFee = (int) Help::where('admin_fee', '>', 0)->count();
        $topupsWithFee = (int) BalanceTransaction::where('admin_fee', '>', 0)
            ->where('status', 'completed')
            ->count();
        $totalTransactionsWithFee = $helpsWithFee + $topupsWithFee;
        
        $avgAdmin = $totalTransactionsWithFee ? ($totalAll / $totalTransactionsWithFee) : 0;

        // Breakdown by source
        $breakdown = [
            'help' => [
                'total' => $helpAdminFeeTotal,
                'count' => $helpsWithFee,
                'avg' => $helpsWithFee ? ($helpAdminFeeTotal / $helpsWithFee) : 0,
            ],
            'topup' => [
                'total' => $topupAdminFeeTotal,
                'count' => $topupsWithFee,
                'avg' => $topupsWithFee ? ($topupAdminFeeTotal / $topupsWithFee) : 0,
            ],
        ];

        return view('superadmin.help-settings', compact('adminFeeChart', 'totalAll', 'total30', 'totalMonth', 'helpsWithFee', 'avgAdmin', 'breakdown'));
    }
}
