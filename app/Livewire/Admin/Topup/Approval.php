<?php

namespace App\Livewire\Admin\Topup;

use App\Models\ActivityLog;
use App\Models\BalanceTransaction;
use App\Models\User;
use App\Models\UserBalance;
use App\Notifications\TopupApproved;
use App\Notifications\TopupCancelled;
use App\Notifications\TopupRejected;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class Approval extends Component
{
    use WithPagination;

    public $selectedTransaction = null;
    public $showDetailModal = false;
    public $showRejectModal = false;
    public $showCancelApprovalModal = false;
    public $rejectionReason = '';
    public $cancellationReason = '';
    public $filterStatus = 'waiting_approval';
    public $search = '';
    public $cityFilter = 'all';

    protected $queryString = [
        'filterStatus' => ['except' => 'waiting_approval'],
        'search' => ['except' => ''],
    ];

    protected $listeners = [
        'topupRequestCreated' => '$refresh',
        'confirmApprove' => 'approve',
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

    public function filterByStatus($status)
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    protected function isAuthorizedForTransaction(BalanceTransaction $tx): bool
    {
        $admin = auth()->user();
        if (!$admin) return false;
        if (in_array($admin->role, ['super_admin', 'superadmin'])) return true;
        if ($admin->role === 'admin') {
            $allowedCityIds = $admin->getAdminCityIds();
            $userCityId = $tx->user?->city_id;
            return !empty($userCityId) && in_array((int) $userCityId, $allowedCityIds, true);
        }
        return false;
    }

    public function viewDetail($transactionId)
    {
        $tx = BalanceTransaction::with(['user', 'user.city', 'approvedBy'])->find($transactionId);
        
        if (!$tx || !$this->isAuthorizedForTransaction($tx)) {
            session()->flash('error', 'Transaksi tidak ditemukan atau berada di luar wilayah wewenang Anda.');
            return;
        }

        $this->selectedTransaction = $tx;
        $this->showDetailModal = true;
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->showRejectModal = false;
        $this->showCancelApprovalModal = false;
        $this->selectedTransaction = null;
        $this->rejectionReason = '';
        $this->cancellationReason = '';
    }

    public function openRejectModal($transactionId)
    {
        $tx = BalanceTransaction::with(['user', 'user.city'])->find($transactionId);

        if (!$tx || !$this->isAuthorizedForTransaction($tx)) {
            session()->flash('error', 'Transaksi tidak ditemukan atau berada di luar wilayah wewenang Anda.');
            return;
        }

        $this->selectedTransaction = $tx;
        $this->showRejectModal = true;
        $this->rejectionReason = '';
    }

    public function openCancelApprovalModal($transactionId)
    {
        $tx = BalanceTransaction::with(['user', 'user.city', 'approvedBy'])->find($transactionId);

        if (!$tx || !$this->isAuthorizedForTransaction($tx)) {
            session()->flash('error', 'Transaksi tidak ditemukan atau berada di luar wilayah wewenang Anda.');
            return;
        }

        $this->selectedTransaction = $tx;
        $this->showCancelApprovalModal = true;
        $this->cancellationReason = '';
    }

    public function approve($transactionId)
    {
        $transaction = BalanceTransaction::with('user')->find($transactionId);

        if (!$transaction || $transaction->status !== 'waiting_approval' || !$this->isAuthorizedForTransaction($transaction)) {
            session()->flash('error', 'Request top-up tidak valid, sudah diproses, atau berada di luar wilayah wewenang Anda.');
            return;
        }

        try {
            DB::beginTransaction();

            // Update status transaction to 'completed'
            $transaction->update([
                'status' => 'completed',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'processed_at' => now(),
            ]);

            // Update user balance
            $userBalance = UserBalance::firstOrCreate(
                ['user_id' => $transaction->user_id],
                ['balance' => 0]
            );

            $userBalance->increment('balance', $transaction->amount);

            try {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'topup_approved',
                    'description' => 'Admin (' . (auth()->user()->city->name ?? 'Wilayah') . ') menyetujui top-up #' . ($transaction->request_code ?? $transaction->id) . ' milik ' . ($transaction->user->name ?? 'Customer') . ' sebesar Rp ' . number_format($transaction->amount, 0, ',', '.'),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'properties' => [
                        'transaction_id' => $transaction->id,
                        'customer_id' => $transaction->user_id,
                        'amount' => $transaction->amount,
                        'admin_city' => auth()->user()->city->name ?? null,
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::warning('ActivityLog failed on topup approval: ' . $e->getMessage());
            }

            DB::commit();

            // Send notification to customer
            if ($transaction->user) {
                $transaction->user->notify(new TopupApproved($transaction));
            }

            session()->flash('success', 'Request top-up #' . ($transaction->request_code ?? $transaction->id) . ' berhasil disetujui! Saldo customer telah ditambahkan.');

            $this->closeModal();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving topup by admin: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reject()
    {
        $this->validate([
            'rejectionReason' => 'required|string|min:3|max:500',
        ], [
            'rejectionReason.required' => 'Alasan penolakan harus diisi',
            'rejectionReason.min' => 'Alasan penolakan minimal 3 karakter',
        ]);

        if (!$this->selectedTransaction || $this->selectedTransaction->status !== 'waiting_approval') {
            session()->flash('error', 'Request tidak valid atau sudah diproses.');
            return;
        }

        if (!$this->isAuthorizedForTransaction($this->selectedTransaction)) {
            session()->flash('error', 'Anda tidak memiliki wewenang untuk menolak transaksi di luar wilayah Anda.');
            $this->closeModal();
            return;
        }

        try {
            $this->selectedTransaction->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $this->rejectionReason,
            ]);

            try {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'topup_rejected',
                    'description' => 'Admin (' . (auth()->user()->city->name ?? 'Wilayah') . ') menolak top-up #' . ($this->selectedTransaction->request_code ?? $this->selectedTransaction->id) . ' milik ' . ($this->selectedTransaction->user->name ?? 'Customer') . '. Alasan: ' . $this->rejectionReason,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('ActivityLog failed on topup reject: ' . $e->getMessage());
            }

            // Send notification to customer
            if ($this->selectedTransaction->user) {
                $this->selectedTransaction->user->notify(new TopupRejected($this->selectedTransaction));
            }

            session()->flash('success', 'Request top-up telah ditolak.');

            $this->closeModal();

        } catch (\Exception $e) {
            Log::error('Error rejecting topup by admin: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function cancelApproval()
    {
        $this->validate([
            'cancellationReason' => 'required|string|min:5|max:500',
        ], [
            'cancellationReason.required' => 'Alasan pembatalan (indikasi barcode salah / fraud) wajib diisi',
            'cancellationReason.min' => 'Alasan pembatalan minimal 5 karakter',
        ]);

        if (!$this->selectedTransaction || !in_array($this->selectedTransaction->status, ['completed', 'approved'])) {
            session()->flash('error', 'Transaksi tidak valid atau belum disetujui.');
            return;
        }

        if (!$this->isAuthorizedForTransaction($this->selectedTransaction)) {
            session()->flash('error', 'Anda tidak memiliki wewenang untuk membatalkan transaksi di luar wilayah Anda.');
            $this->closeModal();
            return;
        }

        try {
            DB::beginTransaction();

            $amount = (float) $this->selectedTransaction->amount;
            $customer = $this->selectedTransaction->user;

            // 1. Tarik/kurangi kembali saldo customer
            $userBalance = UserBalance::firstOrCreate(
                ['user_id' => $this->selectedTransaction->user_id],
                ['balance' => 0]
            );

            $userBalance->decrement('balance', $amount);

            // 2. Buat mutasi koreksi deduction
            BalanceTransaction::create([
                'user_id' => $this->selectedTransaction->user_id,
                'amount' => $amount,
                'type' => 'deduction',
                'description' => 'Penarikan/Koreksi Saldo: Top-Up #' . ($this->selectedTransaction->request_code ?? $this->selectedTransaction->id) . ' Dibatalkan oleh Admin (Alasan: ' . $this->cancellationReason . ')',
                'reference_id' => $this->selectedTransaction->id,
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            // 3. Update status top-up menjadi 'cancelled'
            $this->selectedTransaction->update([
                'status' => 'cancelled',
                'rejection_reason' => '[DIBATALKAN ADMIN: ' . auth()->user()->name . '] ' . $this->cancellationReason,
            ]);

            // 4. Activity Log
            try {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'topup_approval_cancelled',
                    'description' => 'Admin (' . (auth()->user()->city->name ?? 'Wilayah') . ') membatalkan approval top-up #' . ($this->selectedTransaction->request_code ?? $this->selectedTransaction->id) . ' milik ' . ($customer->name ?? 'Customer') . ' (Rp ' . number_format($amount, 0, ',', '.') . '). Alasan: ' . $this->cancellationReason,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('ActivityLog failed on admin cancel topup approval: ' . $e->getMessage());
            }

            DB::commit();

            // 5. Send notification
            if ($customer) {
                $customer->notify(new TopupCancelled($this->selectedTransaction, $this->cancellationReason));
            }

            session()->flash('success', 'Top-up berhasil dibatalkan! Saldo sebesar Rp ' . number_format($amount, 0, ',', '.') . ' telah dikurangi kembali dari akun customer.');

            $this->closeModal();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling topup approval by admin: ' . $e->getMessage());
            session()->flash('error', 'Gagal membatalkan top-up: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $this->cityFilter = $admin->getActiveAdminCityFilter();
        }
        $adminCityIds = $admin ? $admin->getEffectiveAdminCityIds() : [];
        $adminCityName = $admin ? $admin->admin_city_names : null;

        // Base scoped query for counts
        $baseQuery = BalanceTransaction::where('type', 'topup');
        if (!empty($adminCityIds)) {
            $baseQuery->whereHas('user', fn($q) => $q->whereIn('city_id', $adminCityIds));
        } elseif ($admin && $admin->role === 'admin') {
            $baseQuery->whereRaw('1 = 0');
        }

        $totalPending = (clone $baseQuery)->where('status', 'waiting_approval')->count();
        $totalCompleted = (clone $baseQuery)->where('status', 'completed')->count();
        $totalCancelled = (clone $baseQuery)->where('status', 'cancelled')->count();
        $totalRejected = (clone $baseQuery)->where('status', 'rejected')->count();
        $totalAll = (clone $baseQuery)->count();

        $query = BalanceTransaction::where('type', 'topup')
            ->with(['user', 'user.city', 'approvedBy']);

        if (!empty($adminCityIds)) {
            $query->whereHas('user', fn($q) => $q->whereIn('city_id', $adminCityIds));
        } elseif ($admin && $admin->role === 'admin') {
            $query->whereRaw('1 = 0');
        }

        if ($this->filterStatus === 'waiting_approval') {
            $query->where('status', 'waiting_approval');
        } elseif ($this->filterStatus === 'completed') {
            $query->where('status', 'completed');
        } elseif ($this->filterStatus === 'cancelled') {
            $query->where('status', 'cancelled');
        } elseif ($this->filterStatus === 'rejected') {
            $query->where('status', 'rejected');
        }

        if (!empty(trim($this->search))) {
            $searchTerm = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('request_code', 'like', $searchTerm)
                  ->orWhere('id', 'like', $searchTerm)
                  ->orWhere('amount', 'like', $searchTerm)
                  ->orWhere('customer_name', 'like', $searchTerm)
                  ->orWhere('customer_email', 'like', $searchTerm)
                  ->orWhere('customer_phone', 'like', $searchTerm)
                  ->orWhereHas('user', function ($uq) use ($searchTerm) {
                      $uq->where('name', 'like', $searchTerm)
                         ->orWhere('email', 'like', $searchTerm)
                         ->orWhere('phone', 'like', $searchTerm);
                  });
            });
        }

        $cities = $admin ? $admin->getAdminCities() : collect();
        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('livewire.admin.topup.approval', [
            'transactions' => $transactions,
            'cities' => $cities,
            'adminCityName' => $adminCityName,
            'totalPending' => $totalPending,
            'totalCompleted' => $totalCompleted,
            'totalCancelled' => $totalCancelled,
            'totalRejected' => $totalRejected,
            'totalAll' => $totalAll,
        ]);
    }
}

