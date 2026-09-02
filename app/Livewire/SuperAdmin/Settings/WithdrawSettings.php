<?php

namespace App\Livewire\SuperAdmin\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\AppSetting;
use App\Models\WithdrawRequest;

#[Layout('layouts.superadmin')]
class WithdrawSettings extends Component
{
    // General Settings
    public $min_amount;
    public $default_other_fee;

    // Banks configuration list
    public $banks = [];

    // Modal state for Add/Edit bank
    public $modalOpen = false;
    public $editingIndex = null;
    public $bank_code = '';
    public $bank_name = '';
    public $bank_category = 'Bank';
    public $bank_icon = '🏦';
    public $bank_fee = 2500;
    public $is_platform_account = false;
    public $is_active = true;

    // Search and filter in admin table
    public $search = '';
    public $filterCategory = 'all';

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        $this->min_amount = AppSetting::getWithdrawMinAmount();
        $this->default_other_fee = AppSetting::getWithdrawDefaultFee();
        $this->banks = AppSetting::getWithdrawBanks();
    }

    public function saveGeneralSettings()
    {
        $this->validate([
            'min_amount' => 'required|integer|min:100|max:10000000',
            'default_other_fee' => 'required|integer|min:0|max:50000',
        ], [
            'min_amount.required' => 'Batas minimum penarikan wajib diisi.',
            'min_amount.min' => 'Batas minimum penarikan minimal Rp 100.',
            'default_other_fee.required' => 'Biaya admin default wajib diisi.',
        ]);

        AppSetting::set('withdraw_min_amount', $this->min_amount);
        AppSetting::set('withdraw_default_other_fee', $this->default_other_fee);
        AppSetting::set('withdraw_fee_mode', 'deduct_from_balance');

        session()->flash('message', 'Pengaturan umum penarikan dana berhasil disimpan.');
        $this->dispatch('settings-saved');
    }

    public function openAddModal()
    {
        $this->resetValidation();
        $this->editingIndex = null;
        $this->bank_code = '';
        $this->bank_name = '';
        $this->bank_category = 'Bank';
        $this->bank_icon = '🏦';
        $this->bank_fee = 2500;
        $this->is_platform_account = false;
        $this->is_active = true;
        $this->modalOpen = true;
    }

    public function openEditModal($index)
    {
        if (!isset($this->banks[$index])) return;

        $this->resetValidation();
        $this->editingIndex = $index;
        $b = $this->banks[$index];

        $this->bank_code = $b['code'] ?? '';
        $this->bank_name = $b['name'] ?? '';
        $this->bank_category = $b['category'] ?? 'Bank';
        $this->bank_icon = $b['icon'] ?? '🏦';
        $this->bank_fee = (int) ($b['fee'] ?? 0);
        $this->is_platform_account = !empty($b['is_platform_account']);
        $this->is_active = isset($b['is_active']) ? (bool) $b['is_active'] : true;

        $this->modalOpen = true;
    }

    public function closeModal()
    {
        $this->modalOpen = false;
        $this->editingIndex = null;
    }

    public function saveBank()
    {
        $this->validate([
            'bank_code' => 'required|string|max:30',
            'bank_name' => 'required|string|max:100',
            'bank_category' => 'required|in:Bank,E-Wallet,Lainnya',
            'bank_icon' => 'required|string|max:10',
            'bank_fee' => 'required|integer|min:0|max:50000',
        ], [
            'bank_code.required' => 'Kode bank / e-wallet wajib diisi.',
            'bank_name.required' => 'Nama bank / e-wallet wajib diisi.',
            'bank_fee.required' => 'Biaya admin wajib diisi.',
            'bank_fee.min' => 'Biaya admin minimal Rp 0.',
        ]);

        $codeUpper = strtoupper(trim($this->bank_code));

        $item = [
            'code' => $codeUpper,
            'name' => trim($this->bank_name),
            'category' => $this->bank_category,
            'icon' => $this->bank_icon,
            'fee' => (int) $this->bank_fee,
            'is_platform_account' => (bool) $this->is_platform_account,
            'is_active' => (bool) $this->is_active,
        ];

        if ($this->editingIndex !== null && isset($this->banks[$this->editingIndex])) {
            $this->banks[$this->editingIndex] = $item;
        } else {
            // Check if code already exists
            $exists = collect($this->banks)->contains(function ($b) use ($codeUpper) {
                return strtoupper($b['code'] ?? '') === $codeUpper;
            });

            if ($exists) {
                $this->addError('bank_code', "Kode bank '{$codeUpper}' sudah ada di daftar.");
                return;
            }

            $this->banks[] = $item;
        }

        $this->saveBanksToDatabase();
        $this->closeModal();
        session()->flash('message', "Pengaturan bank '{$item['name']}' berhasil disimpan.");
    }

    public function deleteBank($index)
    {
        if (isset($this->banks[$index])) {
            $name = $this->banks[$index]['name'] ?? 'Bank';
            unset($this->banks[$index]);
            $this->banks = array_values($this->banks);
            $this->saveBanksToDatabase();
            session()->flash('message', "Bank/E-Wallet '{$name}' berhasil dihapus.");
        }
    }

    public function togglePlatformAccount($index)
    {
        if (isset($this->banks[$index])) {
            $current = !empty($this->banks[$index]['is_platform_account']);
            $this->banks[$index]['is_platform_account'] = !$current;
            // If marked as platform account, automatically set fee to 0 by default
            if (!$current) {
                $this->banks[$index]['fee'] = 0;
            }
            $this->saveBanksToDatabase();
            session()->flash('message', "Status Rekening Utama Platform untuk {$this->banks[$index]['name']} diperbarui.");
        }
    }

    public function toggleBankStatus($index)
    {
        if (isset($this->banks[$index])) {
            $current = isset($this->banks[$index]['is_active']) ? (bool) $this->banks[$index]['is_active'] : true;
            $this->banks[$index]['is_active'] = !$current;
            $this->saveBanksToDatabase();
            session()->flash('message', "Status aktif {$this->banks[$index]['name']} berhasil diperbarui.");
        }
    }

    public function resetToDefaults()
    {
        $this->banks = AppSetting::getDefaultWithdrawBanks();
        $this->saveBanksToDatabase();
        session()->flash('message', 'Daftar bank dan tarif standar BI-FAST berhasil direset ke setelan awal.');
    }

    protected function saveBanksToDatabase()
    {
        AppSetting::set('withdraw_banks_config', $this->banks);
        $this->banks = AppSetting::getWithdrawBanks();
    }

    public function render()
    {
        $filtered = collect($this->banks);

        if ($this->search) {
            $q = strtolower(trim($this->search));
            $filtered = $filtered->filter(function ($b) use ($q) {
                return str_contains(strtolower($b['name'] ?? ''), $q) || str_contains(strtolower($b['code'] ?? ''), $q);
            });
        }

        if ($this->filterCategory !== 'all') {
            $filtered = $filtered->where('category', $this->filterCategory);
        }

        $stats = [
            'total_banks' => count($this->banks),
            'active_banks' => collect($this->banks)->where('is_active', true)->count(),
            'platform_accounts' => collect($this->banks)->where('is_platform_account', true)->count(),
            'total_withdraw_count' => WithdrawRequest::count(),
            'total_withdraw_amount' => WithdrawRequest::whereIn('status', ['completed', 'approved', 'success'])->sum('amount'),
        ];

        return view('livewire.superadmin.settings.withdraw-settings', [
            'filteredBanks' => $filtered,
            'stats' => $stats,
            'min_amount' => $this->min_amount,
            'default_other_fee' => $this->default_other_fee,
            'banks' => $this->banks,
            'modalOpen' => $this->modalOpen,
            'editingIndex' => $this->editingIndex,
            'bank_code' => $this->bank_code,
            'bank_name' => $this->bank_name,
            'bank_category' => $this->bank_category,
            'bank_icon' => $this->bank_icon,
            'bank_fee' => $this->bank_fee,
            'is_platform_account' => $this->is_platform_account,
            'is_active' => $this->is_active,
            'search' => $this->search,
            'filterCategory' => $this->filterCategory,
            'errors' => (new \Illuminate\Support\ViewErrorBag)->put('default', $this->getErrorBag()),
        ]);
    }
}
