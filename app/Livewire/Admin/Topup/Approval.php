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
    public $districtFilter = 'all';
    public $cityFilter = 'all'; // Backward compatibility alias

    protected $queryString = [
        'filterStatus' => ['except' => 'waiting_approval'],
        'search' => ['except' => ''],
    ];

    protected $listeners = [
        'topupRequestCreated'            => '$refresh',
        'confirmApprove'                 => 'approve',
        'admin-district-changed'         => 'onAdminDistrictChanged',
        'admin-city-changed'             => 'onAdminDistrictChanged',
        'superadmin-territory-changed'   => '$refresh',
    ];

    public function mount()
    {
        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $this->districtFilter = $admin->getActiveAdminDistrictFilter();
            $this->cityFilter = $this->districtFilter;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDistrictFilter()
    {
        $this->resetPage();
    }

    public function updatedDistrictFilter()
    {
        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $admin->setActiveAdminDistrictFilter($this->districtFilter);
            $this->cityFilter = $this->districtFilter;
            $this->dispatch('admin-district-changed', districtId: $this->districtFilter);
        }
        $this->resetPage();
    }

    public function updatedCityFilter()
    {
        $this->districtFilter = $this->cityFilter;
        $this->updatedDistrictFilter();
    }

    public function onAdminDistrictChanged($districtId = null)
    {
        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $this->districtFilter = $admin->getActiveAdminDistrictFilter();
            $this->cityFilter = $this->districtFilter;
            $this->resetPage();
        }
    }

    public function onAdminCityChanged($cityId = null)
    {
        $this->onAdminDistrictChanged($cityId);
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
            $allowedDistrictIds = $admin->getAdminDistrictIds();
            if (!empty($allowedDistrictIds)) {
                $userDistrictId = $tx->user?->district_id;
                if (!empty($userDistrictId)) {
                    return in_array((int) $userDistrictId, $allowedDistrictIds, true);
                }
            }
            if ($admin->city_id && $tx->user?->city_id) {
                return (int) $admin->city_id === (int) $tx->user->city_id;
            }
            if (empty($allowedDistrictIds) && empty($admin->city_id)) {
                return true;
            }
        }
        return false;
    }

    public function viewDetail($transactionId)
    {
        $tx = BalanceTransaction::with(['user', 'user.district', 'user.city', 'approvedBy'])->find($transactionId);
        
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
        $tx = BalanceTransaction::with(['user', 'user.district', 'user.city'])->find($transactionId);

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
        $tx = BalanceTransaction::with(['user', 'user.district', 'user.city', 'approvedBy'])->find($transactionId);

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
                $districtLabel = auth()->user()->kecamatan ?? (auth()->user()->district?->name ?? 'Wilayah Kecamatan');
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'topup_approved',
                    'description' => 'Admin (Kec. ' . $districtLabel . ') menyetujui top-up #' . ($transaction->request_code ?? $transaction->id) . ' milik ' . ($transaction->user->name ?? 'Customer') . ' sebesar Rp ' . number_format($transaction->amount, 0, ',', '.'),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'properties' => [
                        'transaction_id' => $transaction->id,
                        'customer_id' => $transaction->user_id,
                        'amount' => $transaction->amount,
                        'admin_district' => $districtLabel,
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
            session()->flash('error', 'Anda tidak memiliki wewenang untuk menolak transaksi di luar wilayah wewenang Anda.');
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
                $districtLabel = auth()->user()->kecamatan ?? (auth()->user()->district?->name ?? 'Wilayah Kecamatan');
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'topup_rejected',
                    'description' => 'Admin (Kec. ' . $districtLabel . ') menolak top-up #' . ($this->selectedTransaction->request_code ?? $this->selectedTransaction->id) . ' milik ' . ($this->selectedTransaction->user->name ?? 'Customer') . '. Alasan: ' . $this->rejectionReason,
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
            session()->flash('error', 'Anda tidak memiliki wewenang untuk membatalkan transaksi di luar wilayah wewenang Anda.');
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
                $districtLabel = auth()->user()->kecamatan ?? (auth()->user()->district?->name ?? 'Wilayah Kecamatan');
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'topup_approval_cancelled',
                    'description' => 'Admin (Kec. ' . $districtLabel . ') membatalkan approval top-up #' . ($this->selectedTransaction->request_code ?? $this->selectedTransaction->id) . ' milik ' . ($customer->name ?? 'Customer') . ' (Rp ' . number_format($amount, 0, ',', '.') . '). Alasan: ' . $this->cancellationReason,
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
        $isSuperAdmin = $admin && in_array($admin->role, ['super_admin', 'superadmin']);

        if (!$isSuperAdmin && $admin && $admin->role === 'admin') {
            $this->districtFilter = $admin->getActiveAdminDistrictFilter();
            $this->cityFilter = $this->districtFilter;
        }
        $adminDistrictIds = $admin ? $admin->getEffectiveAdminDistrictIds() : [];
        $adminDistrictName = $admin ? $admin->admin_district_names : null;

        // Base scoped query for counts
        $adminCityId = $admin?->city_id;
        $baseQuery = BalanceTransaction::where('type', 'topup');
        if (!$isSuperAdmin) {
            if (!empty($adminDistrictIds)) {
                $baseQuery->whereHas('user', fn($q) => $q->whereIn('district_id', $adminDistrictIds));
            } elseif (!empty($adminCityId) && $admin && $admin->role === 'admin') {
                $baseQuery->whereHas('user', fn($q) => $q->where('city_id', $adminCityId));
            }
        } elseif ($isSuperAdmin && $admin) {
            $saTerritory = $admin->getActiveSuperadminTerritory();
            if ($saTerritory['type'] === 'district' && !empty($saTerritory['id'])) {
                $dId = (int) $saTerritory['id'];
                $baseQuery->whereHas('user', fn($q) => $q->where('district_id', $dId));
            } elseif ($saTerritory['type'] === 'city' && !empty($saTerritory['id'])) {
                $cityId = (int) $saTerritory['id'];
                $saDistrictIds = $admin->getEffectiveSuperadminDistrictIds();
                $baseQuery->whereHas('user', function ($q) use ($cityId, $saDistrictIds) {
                    if (!empty($saDistrictIds)) $q->whereIn('district_id', $saDistrictIds);
                    if ($cityId) $q->orWhere('city_id', $cityId);
                });
            }
        }

        $totalPending = (clone $baseQuery)->where('status', 'waiting_approval')->count();
        $totalCompleted = (clone $baseQuery)->where('status', 'completed')->count();
        $totalCancelled = (clone $baseQuery)->where('status', 'cancelled')->count();
        $totalRejected = (clone $baseQuery)->where('status', 'rejected')->count();
        $totalAll = (clone $baseQuery)->count();

        $query = BalanceTransaction::where('type', 'topup')
            ->with(['user', 'user.district', 'user.city', 'approvedBy']);

        if (!$isSuperAdmin) {
            if (!empty($adminDistrictIds)) {
                $query->whereHas('user', fn($q) => $q->whereIn('district_id', $adminDistrictIds));
            } elseif (!empty($adminCityId) && $admin && $admin->role === 'admin') {
                $query->whereHas('user', fn($q) => $q->where('city_id', $adminCityId));
            }
        } elseif ($isSuperAdmin && $admin) {
            $saTerritory = $admin->getActiveSuperadminTerritory();
            if ($saTerritory['type'] === 'district' && !empty($saTerritory['id'])) {
                $dId = (int) $saTerritory['id'];
                $query->whereHas('user', fn($q) => $q->where('district_id', $dId));
            } elseif ($saTerritory['type'] === 'city' && !empty($saTerritory['id'])) {
                $cityId = (int) $saTerritory['id'];
                $saDistrictIds = $admin->getEffectiveSuperadminDistrictIds();
                $query->whereHas('user', function ($q) use ($cityId, $saDistrictIds) {
                    if (!empty($saDistrictIds)) $q->whereIn('district_id', $saDistrictIds);
                    if ($cityId) $q->orWhere('city_id', $cityId);
                });
            }
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

        $districts = $admin ? $admin->getAdminDistricts() : collect();
        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('livewire.admin.topup.approval', [
            'transactions'      => $transactions,
            'districts'         => $districts,
            'cities'            => $districts, // Backward compatibility
            'adminDistrictName' => $adminDistrictName,
            'adminCityName'     => $adminDistrictName,
            'totalPending'      => $totalPending,
            'totalCompleted'    => $totalCompleted,
            'totalCancelled'    => $totalCancelled,
            'totalRejected'     => $totalRejected,
            'totalAll'          => $totalAll,
        ]);
    }
}

