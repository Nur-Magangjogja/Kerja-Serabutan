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
    
    protected $queryString = [
        'filterStatus' => ['except' => 'waiting_approval'],
        'search' => ['except' => ''],
    ];

    protected $listeners = [
        'topupRequestCreated' => '$refresh',
        'confirmApprove' => 'approve',
    ];

    public function updatingSearch()
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
        $transaction = BalanceTransaction::find($transactionId);

        if (!$transaction || $transaction->status !== 'waiting_approval') {
            session()->flash('error', 'Request tidak valid atau sudah diproses.');
            return;
        }

        try {
            DB::beginTransaction();

            // Update transaction status to 'completed'
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

        if (!$this->selectedTransaction || $this->selectedTransaction->status !== 'waiting_approval') {
            session()->flash('error', 'Request tidak valid atau sudah diproses.');
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
                    'description' => 'Menolak top-up #' . ($this->selectedTransaction->request_code ?? $this->selectedTransaction->id) . ' milik ' . ($this->selectedTransaction->user->name ?? 'Customer') . '. Alasan: ' . $this->rejectionReason,
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

        if (!$this->selectedTransaction || !in_array($this->selectedTransaction->status, ['completed', 'approved'])) {
            session()->flash('error', 'Transaksi tidak valid atau belum disetujui.');
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

            // 2. Buat mutasi koreksi deduction untuk transparansi riwayat saldo
            BalanceTransaction::create([
                'user_id' => $this->selectedTransaction->user_id,
                'amount' => $amount,
                'type' => 'deduction',
                'description' => 'Penarikan/Koreksi Saldo: Top-Up #' . ($this->selectedTransaction->request_code ?? $this->selectedTransaction->id) . ' Dibatalkan (Alasan: ' . $this->cancellationReason . ')',
                'reference_id' => $this->selectedTransaction->id,
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            // 3. Update status top-up menjadi 'cancelled'
            $this->selectedTransaction->update([
                'status' => 'cancelled',
                'rejection_reason' => '[DIBATALKAN SUPERADMIN: ' . auth()->user()->name . '] ' . $this->cancellationReason,
            ]);

            // 4. Catat Activity Log SuperAdmin
            try {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'topup_approval_cancelled',
                    'description' => 'Membatalkan approval top-up #' . ($this->selectedTransaction->request_code ?? $this->selectedTransaction->id) . ' milik ' . ($customer->name ?? 'Customer') . ' (Rp ' . number_format($amount, 0, ',', '.') . '). Alasan: ' . $this->cancellationReason,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'properties' => [
                        'transaction_id' => $this->selectedTransaction->id,
                        'customer_id' => $this->selectedTransaction->user_id,
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
                $customer->notify(new TopupCancelled($this->selectedTransaction, $this->cancellationReason));
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
        // Hitung total count per status
        $totalPending = BalanceTransaction::where('type', 'topup')->where('status', 'waiting_approval')->count();
        $totalCompleted = BalanceTransaction::where('type', 'topup')->where('status', 'completed')->count();
        $totalCancelled = BalanceTransaction::where('type', 'topup')->where('status', 'cancelled')->count();
        $totalRejected = BalanceTransaction::where('type', 'topup')->where('status', 'rejected')->count();
        $totalAll = BalanceTransaction::where('type', 'topup')->count();

        $query = BalanceTransaction::where('type', 'topup')
            ->with(['user', 'user.city', 'approvedBy']);

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

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('superadmin.topup-approval', [
            'transactions' => $transactions,
            'totalPending' => $totalPending,
            'totalCompleted' => $totalCompleted,
            'totalCancelled' => $totalCancelled,
            'totalRejected' => $totalRejected,
            'totalAll' => $totalAll,
        ]);
    }
}


