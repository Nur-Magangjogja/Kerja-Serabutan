<?php

namespace App\Livewire\Admin\Withdraws;

use App\Models\WithdrawRequest;
use App\Models\BalanceTransaction;
use App\Models\UserBalance;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $status = 'all';
    public $roleFilter = 'all'; // all, mitra, customer
    public $cityFilter = 'all'; // all or city_id

    // Approval Modal
    public $showApproveModal = false;
    public $selectedWithdrawId = null;
    public $selectedWithdraw = null;
    public $proofPhoto;

    // Reject Modal
    public $showRejectModal = false;
    public $rejectReason = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'roleFilter' => ['except' => 'all'],
    ];

    protected $listeners = [
        'admin-city-changed' => 'onAdminCityChanged',
    ];

    public function mount()
    {
        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $this->cityFilter = $admin->getActiveAdminCityFilter();
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingCityFilter()
    {
        $this->resetPage();
    }

    public function updatedCityFilter()
    {
        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $admin->setActiveAdminCityFilter($this->cityFilter);
            $this->dispatch('admin-city-changed', cityId: $this->cityFilter);
        }
        $this->resetPage();
    }

    public function onAdminCityChanged($cityId = null)
    {
        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $this->cityFilter = $admin->getActiveAdminCityFilter();
            $this->resetPage();
        }
    }

    protected function isAuthorizedForWithdraw(WithdrawRequest $withdraw): bool
    {
        $admin = auth()->user();
        if (!$admin) return false;
        if (in_array($admin->role, ['super_admin', 'superadmin'])) return true;
        if ($admin->role === 'admin') {
            $allowedCityIds = $admin->getAdminCityIds();
            $userCityId = $withdraw->user?->city_id;
            return !empty($userCityId) && in_array((int) $userCityId, $allowedCityIds, true);
        }
        return false;
    }

    public function openApproveModal($id)
    {
        $this->selectedWithdrawId = $id;
        $withdraw = WithdrawRequest::with(['user.balance', 'user.city'])->findOrFail($id);
        
        if (!$this->isAuthorizedForWithdraw($withdraw)) {
            session()->flash('error', 'Anda tidak memiliki wewenang untuk memproses penarikan dana dari luar wilayah Anda.');
            return;
        }

        $this->selectedWithdraw = $withdraw;
        $this->proofPhoto = null;
        $this->showApproveModal = true;
    }

    public function closeApproveModal()
    {
        $this->showApproveModal = false;
    }

    public function submitApprove()
    {
        $this->validate([
            'proofPhoto' => 'required|image|max:5120',
        ], [
            'proofPhoto.required' => 'Foto bukti transfer wajib diunggah.',
            'proofPhoto.image' => 'File bukti harus berupa gambar.',
        ]);

        $withdraw = WithdrawRequest::with(['user'])->findOrFail($this->selectedWithdrawId);

        if (!$this->isAuthorizedForWithdraw($withdraw)) {
            $this->showApproveModal = false;
            session()->flash('error', 'Anda tidak memiliki wewenang untuk memproses penarikan dana dari luar wilayah Anda.');
            return;
        }

        $photoPath = $this->proofPhoto->store('withdraws/proofs', 'public');

        $withdraw->update([
            'status' => 'completed',
            'proof_of_transfer' => $photoPath,
            'processed_at' => now(),
        ]);

        // Update corresponding balance transaction if any
        BalanceTransaction::where('order_id', 'WD-' . $withdraw->id)
            ->orWhere('reference_id', $withdraw->id)
            ->update([
                'status' => 'success',
                'proof_of_payment' => $photoPath,
                'processed_at' => now(),
            ]);

        // Catat ke log aktivitas sistem
        \App\Models\ActivityLog::record(
            auth()->user(),
            'withdraw_approved',
            "Admin " . (auth()->user()->name ?? 'Admin') . " menyetujui pencairan dana #WD-{$withdraw->id} sebesar Rp " . number_format($withdraw->amount, 0, ',', '.') . " untuk user {$withdraw->user->name}",
            ['withdraw_id' => $withdraw->id, 'amount' => $withdraw->amount, 'user_id' => $withdraw->user_id]
        );

        $this->showApproveModal = false;
        session()->flash('success', "Pencairan dana #WD-{$withdraw->id} berhasil disetujui & bukti transfer tersimpan.");
    }

    public function openRejectModal($id)
    {
        $this->selectedWithdrawId = $id;
        $withdraw = WithdrawRequest::with(['user.balance', 'user.city'])->findOrFail($id);

        if (!$this->isAuthorizedForWithdraw($withdraw)) {
            session()->flash('error', 'Anda tidak memiliki wewenang untuk memproses penarikan dana dari luar wilayah Anda.');
            return;
        }

        $this->selectedWithdraw = $withdraw;
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal()
    {
        $this->showRejectModal = false;
    }

    public function submitReject()
    {
        $this->validate([
            'rejectReason' => 'required|string|min:5',
        ], [
            'rejectReason.required' => 'Alasan penolakan pencairan wajib diisi.',
            'rejectReason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        $withdraw = WithdrawRequest::with(['user'])->findOrFail($this->selectedWithdrawId);

        if (!$this->isAuthorizedForWithdraw($withdraw)) {
            $this->showRejectModal = false;
            session()->flash('error', 'Anda tidak memiliki wewenang untuk memproses penarikan dana dari luar wilayah Anda.');
            return;
        }

        $user = $withdraw->user;
        $refundAmount = (float) ($withdraw->amount + ($withdraw->admin_fee ?? 0));

        // Refund balance back to user
        if ($user) {
            $userBalance = UserBalance::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
            $userBalance->increment('balance', $refundAmount);
        }

        $withdraw->update([
            'status' => 'rejected',
            'description' => $this->rejectReason,
            'processed_at' => now(),
        ]);

        BalanceTransaction::create([
            'user_id' => $user->id,
            'order_id' => 'REFUND-WD-' . $withdraw->id,
            'type' => 'refund',
            'amount' => $refundAmount,
            'total_payment' => $refundAmount,
            'status' => 'success',
            'description' => "Pengembalian dana penarikan #WD-{$withdraw->id} yang ditolak: {$this->rejectReason}",
        ]);

        // Catat ke log aktivitas sistem
        \App\Models\ActivityLog::record(
            auth()->user(),
            'withdraw_rejected',
            "Admin " . (auth()->user()->name ?? 'Admin') . " menolak pencairan dana #WD-{$withdraw->id} sebesar Rp " . number_format($refundAmount, 0, ',', '.') . " untuk user {$user->name}. Alasan: {$this->rejectReason}",
            ['withdraw_id' => $withdraw->id, 'amount' => $refundAmount, 'user_id' => $user->id, 'reason' => $this->rejectReason]
        );

        $this->showRejectModal = false;
        session()->flash('success', "Pencairan dana #WD-{$withdraw->id} telah ditolak dan saldo telah dikembalikan ke user.");
    }

    public function render()
    {
        $admin = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        // Ambil daftar kota untuk filter
        if ($isSuperAdmin) {
            $cities = \App\Models\City::orderBy('name')->get();
        } else {
            $cities = $admin ? $admin->getAdminCities() : collect();
        }

        $query = WithdrawRequest::with(['user.city'])->latest();

        if (! $isSuperAdmin) {
            $this->cityFilter = $admin ? $admin->getActiveAdminCityFilter() : 'all';
            $effectiveCityIds = $admin ? $admin->getEffectiveAdminCityIds() : [];
            if (!empty($effectiveCityIds)) {
                $query->whereHas('user', fn($q) => $q->whereIn('city_id', $effectiveCityIds));
            } elseif ($admin && $admin->role === 'admin') {
                $query->whereRaw('1 = 0');
            }
        } elseif ($this->cityFilter !== 'all') {
            $query->whereHas('user', fn($q) => $q->where('city_id', (int) $this->cityFilter));
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->roleFilter !== 'all') {
            $query->whereHas('user', fn($q) => $q->where('role', $this->roleFilter));
        }

        if (!empty($this->search)) {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('bank_code', 'like', "%{$s}%")
                  ->orWhere('account_number', 'like', "%{$s}%")
                  ->orWhere('account_name', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($sq) use ($s) {
                      $sq->where('name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%")
                        ->orWhereHas('city', fn($cq) => $cq->where('name', 'like', "%{$s}%"));
                  });
            });
        }

        $withdraws = $query->paginate(10);
        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.admin.withdraws.index', [
            'withdraws'    => $withdraws,
            'cities'       => $cities,
            'isSuperAdmin' => $isSuperAdmin,
        ])->layout($layout);
    }
}
