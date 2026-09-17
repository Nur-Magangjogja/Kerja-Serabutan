<?php

namespace App\Livewire\SuperAdmin\Topup;

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

#[Layout('layouts.superadmin')]
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
        'cityFilter' => ['except' => 'all'],
    ];

    protected $listeners = [
        'topupRequestCreated'          => '$refresh',
        'confirmApprove'               => 'approve',
        'superadmin-territory-changed' => '$refresh',
        'admin-district-changed'       => '$refresh',
        'admin-city-changed'           => '$refresh',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCityFilter()
    {
        $this->resetPage();
    }

    public function filterByStatus($status)
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function viewDetail($transactionId)
    {
        $this->selectedTransaction = BalanceTransaction::with(['user', 'user.city', 'approvedBy'])->find($transactionId);
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
        $this->selectedTransaction = BalanceTransaction::with(['user'])->find($transactionId);
        $this->showRejectModal = true;
    }

    public function openCancelApprovalModal($transactionId)
    {
        $this->selectedTransaction = BalanceTransaction::with(['user', 'approvedBy'])->find($transactionId);
        $this->showCancelApprovalModal = true;
        $this->cancellationReason = '';
    }

    public function approve($transactionId)
    {
        try {
            DB::beginTransaction();

            $transaction = BalanceTransaction::where('id', $transactionId)->lockForUpdate()->first();

            if (!$transaction || $transaction->status !== 'waiting_approval') {
                DB::rollBack();
                session()->flash('error', 'Request tidak valid atau sudah diproses.');
                return;
            }

            // Update transaction status to 'completed'
            $transaction->update([
                'status' => 'completed',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'processed_at' => now(),
            ]);

            // Update user balance
            $userBalance = UserBalance::where('user_id', $transaction->user_id)->lockForUpdate()->firstOrCreate(
                ['user_id' => $transaction->user_id],
                ['balance' => 0]
            );

            $userBalance->increment('balance', $transaction->amount);

            try {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'topup_approved',
                    'description' => 'Menyetujui top-up #' . ($transaction->request_code ?? $transaction->id) . ' milik ' . ($transaction->user->name ?? 'Customer') . ' sebesar Rp ' . number_format($transaction->amount, 0, ',', '.'),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'properties' => [
                        'transaction_id' => $transaction->id,
                        'customer_id' => $transaction->user_id,
                        'amount' => $transaction->amount,
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

            session()->flash('success', 'Request top-up berhasil disetujui! Saldo customer telah ditambahkan.');

            $this->closeModal();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving topup: ' . $e->getMessage());
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

        if (!$this->selectedTransaction) {
            session()->flash('error', 'Transaksi tidak ditemukan.');
            return;
        }

        try {
            DB::beginTransaction();

            $transaction = BalanceTransaction::where('id', $this->selectedTransaction->id)->lockForUpdate()->first();

            if (!$transaction || $transaction->status !== 'waiting_approval') {
                DB::rollBack();
                session()->flash('error', 'Request tidak valid atau sudah diproses.');
                $this->closeModal();
                return;
            }

            $transaction->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $this->rejectionReason,
            ]);

            try {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'topup_rejected',
                    'description' => 'Menolak top-up #' . ($transaction->request_code ?? $transaction->id) . ' milik ' . ($transaction->user->name ?? 'Customer') . '. Alasan: ' . $this->rejectionReason,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('ActivityLog failed on topup reject: ' . $e->getMessage());
            }

            DB::commit();

            // Send notification to customer
            if ($transaction->user) {
                $transaction->user->notify(new TopupRejected($transaction));
            }

            session()->flash('success', 'Request top-up telah ditolak.');

            $this->closeModal();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting topup: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Membatalkan approval top-up yang sebelumnya sudah disetujui (Kasus Barcode Salah / Penipuan / Fraud)
     */
    public function cancelApproval()
    {
        $this->validate([
            'cancellationReason' => 'required|string|min:5|max:500',
        ], [
            'cancellationReason.required' => 'Alasan pembatalan (indikasi barcode salah / fraud) wajib diisi',
            'cancellationReason.min' => 'Alasan pembatalan minimal 5 karakter',
        ]);

        if (!$this->selectedTransaction) {
            session()->flash('error', 'Transaksi tidak ditemukan.');
            return;
        }

        try {
            DB::beginTransaction();

            $transaction = BalanceTransaction::where('id', $this->selectedTransaction->id)->lockForUpdate()->first();

            if (!$transaction || !in_array($transaction->status, ['completed', 'approved'])) {
                DB::rollBack();
                session()->flash('error', 'Transaksi tidak valid atau belum disetujui.');
                $this->closeModal();
                return;
            }

            $amount = (float) $transaction->amount;
            $customer = $transaction->user;

            // 1. Tarik/kurangi kembali saldo customer
            $userBalance = UserBalance::where('user_id', $transaction->user_id)->lockForUpdate()->firstOrCreate(
                ['user_id' => $transaction->user_id],
                ['balance' => 0]
            );

            $userBalance->decrement('balance', $amount);

            // 2. Buat mutasi koreksi deduction untuk transparansi riwayat saldo
            BalanceTransaction::create([
                'user_id' => $transaction->user_id,
                'amount' => $amount,
                'type' => 'deduction',
                'description' => 'Penarikan/Koreksi Saldo: Top-Up #' . ($transaction->request_code ?? $transaction->id) . ' Dibatalkan (Alasan: ' . $this->cancellationReason . ')',
                'reference_id' => $transaction->id,
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            // 3. Update status top-up menjadi 'cancelled'
            $transaction->update([
                'status' => 'cancelled',
                'rejection_reason' => '[DIBATALKAN SUPERADMIN: ' . auth()->user()->name . '] ' . $this->cancellationReason,
            ]);

            // 4. Catat Activity Log SuperAdmin
            try {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'topup_approval_cancelled',
                    'description' => 'Membatalkan approval top-up #' . ($transaction->request_code ?? $transaction->id) . ' milik ' . ($customer->name ?? 'Customer') . ' (Rp ' . number_format($amount, 0, ',', '.') . '). Alasan: ' . $this->cancellationReason,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'properties' => [
                        'transaction_id' => $transaction->id,
                        'customer_id' => $transaction->user_id,
                        'amount' => $amount,
                        'reason' => $this->cancellationReason,
                        'cancelled_by' => auth()->user()->name,
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::warning('ActivityLog failed on cancel topup approval: ' . $e->getMessage());
            }

            DB::commit();

            // 5. Kirim notifikasi ke customer bahwa top-up dibatalkan
            if ($customer) {
                $customer->notify(new TopupCancelled($transaction, $this->cancellationReason));
            }

            session()->flash('success', 'Top-up berhasil dibatalkan! Saldo sebesar Rp ' . number_format($amount, 0, ',', '.') . ' telah ditarik/dikurangi kembali dari akun customer.');

            $this->closeModal();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling topup approval: ' . $e->getMessage());
            session()->flash('error', 'Gagal membatalkan top-up: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $currentUser = auth()->user();
        $territory = $currentUser ? $currentUser->getActiveSuperadminTerritory() : ['type' => 'all', 'id' => null];
        $districtIds = $currentUser ? $currentUser->getEffectiveSuperadminDistrictIds() : [];

        $applyTerritory = function ($q) use ($territory, $districtIds) {
            if ($territory['type'] === 'district' && $territory['id']) {
                $dId = (int) $territory['id'];
                $q->whereHas('user', fn($uq) => $uq->where('district_id', $dId));
            } elseif ($territory['type'] === 'city' && $territory['id']) {
                $cId = (int) $territory['id'];
                $q->whereHas('user', function ($uq) use ($cId, $districtIds) {
                    $uq->where('city_id', $cId);
                    if (!empty($districtIds)) {
                        $uq->orWhereIn('district_id', $districtIds);
                    }
                });
            }
        };

        $baseCountQuery = BalanceTransaction::where('type', 'topup');
        if ($territory['type'] !== 'all') {
            $applyTerritory($baseCountQuery);
        }

        // Hitung total count per status
        $totalPending = (clone $baseCountQuery)->where('status', 'waiting_approval')->count();
        $totalCompleted = (clone $baseCountQuery)->where('status', 'completed')->count();
        $totalCancelled = (clone $baseCountQuery)->where('status', 'cancelled')->count();
        $totalRejected = (clone $baseCountQuery)->where('status', 'rejected')->count();
        $totalAll = (clone $baseCountQuery)->count();

        $query = BalanceTransaction::where('type', 'topup')
            ->with(['user', 'user.city', 'user.district', 'approvedBy']);

        if ($territory['type'] !== 'all') {
            $applyTerritory($query);
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

        if ($this->cityFilter !== 'all') {
            $query->whereHas('user', fn($q) => $q->where('city_id', (int) $this->cityFilter));
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
                         ->orWhere('phone', 'like', $searchTerm)
                         ->orWhere('city', 'like', $searchTerm)
                         ->orWhereHas('city', fn($cq) => $cq->where('name', 'like', $searchTerm));
                  });
            });
        }

        $cities = \App\Models\City::orderBy('name')->get();
        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('livewire.superadmin.topup.approval', [
            'transactions' => $transactions,
            'cities' => $cities,
            'totalPending' => $totalPending,
            'totalCompleted' => $totalCompleted,
            'totalCancelled' => $totalCancelled,
            'totalRejected' => $totalRejected,
            'totalAll' => $totalAll,
        ]);
    }
}


