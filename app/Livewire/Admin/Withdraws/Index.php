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

    public function openApproveModal($id)
    {
        $this->selectedWithdrawId = $id;
        $this->selectedWithdraw = WithdrawRequest::with(['user.balance'])->findOrFail($id);
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

        $withdraw = WithdrawRequest::findOrFail($this->selectedWithdrawId);
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
        $this->selectedWithdraw = WithdrawRequest::with(['user.balance'])->findOrFail($id);
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

        $withdraw = WithdrawRequest::findOrFail($this->selectedWithdrawId);
        $user = $withdraw->user;

        // Refund balance back to user
        if ($user) {
            $userBalance = UserBalance::firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
            $userBalance->increment('balance', $withdraw->amount);
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
            'amount' => $withdraw->amount,
            'status' => 'success',
            'description' => "Pengembalian dana penarikan #WD-{$withdraw->id} yang ditolak: {$this->rejectReason}",
        ]);

        // Catat ke log aktivitas sistem
        \App\Models\ActivityLog::record(
            auth()->user(),
            'withdraw_rejected',
            "Admin " . (auth()->user()->name ?? 'Admin') . " menolak pencairan dana #WD-{$withdraw->id} sebesar Rp " . number_format($withdraw->amount, 0, ',', '.') . " untuk user {$user->name}. Alasan: {$this->rejectReason}",
            ['withdraw_id' => $withdraw->id, 'amount' => $withdraw->amount, 'user_id' => $user->id, 'reason' => $this->rejectReason]
        );

        $this->showRejectModal = false;
        session()->flash('success', "Pencairan dana #WD-{$withdraw->id} telah ditolak dan saldo telah dikembalikan ke user.");
    }

    public function render()
    {
        $admin = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        $query = WithdrawRequest::with(['user.city'])->latest();

        if (! $isSuperAdmin) {
            $managedCityIds = $admin->managedCities()->pluck('cities.id')->toArray();
            if (!empty($managedCityIds)) {
                $query->whereHas('user', fn($q) => $q->whereIn('city_id', $managedCityIds));
            }
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
                  ->orWhereHas('user', fn($sq) => $sq->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
            });
        }

        $withdraws = $query->paginate(10);
        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.admin.withdraws.index', [
            'withdraws' => $withdraws,
        ])->layout($layout);
    }
}
