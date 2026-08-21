<?php

namespace App\Livewire\SuperAdmin\Topup;

use App\Models\BalanceTransaction;
use App\Models\User;
use App\Models\UserBalance;
use App\Notifications\TopupApproved;
use App\Notifications\TopupRejected;
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
    public $rejectionReason = '';
    
    protected $listeners = [
        'topupRequestCreated' => '$refresh',
        'confirmApprove' => 'approve',
    ];

    public function mount()
    {
        //
    }

    public function viewDetail($transactionId)
    {
        $this->selectedTransaction = BalanceTransaction::with(['user', 'user.city'])->find($transactionId);
        $this->showDetailModal = true;
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->showRejectModal = false;
        $this->selectedTransaction = null;
        $this->rejectionReason = '';
    }

    public function openRejectModal($transactionId)
    {
        $this->selectedTransaction = BalanceTransaction::with(['user'])->find($transactionId);
        $this->showRejectModal = true;
    }

    public function approve($transactionId)
    {
        $transaction = BalanceTransaction::find($transactionId);

        if (!$transaction || $transaction->status !== 'waiting_approval') {
            session()->flash('error', 'Request tidak valid atau sudah diproses.');
            return;
        }

        try {
            \DB::beginTransaction();

            // Update transaction status to 'completed' (not just 'approved')
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

            \DB::commit();

            // Send notification to customer
            $transaction->user->notify(new TopupApproved($transaction));

            session()->flash('success', 'Request top-up berhasil disetujui! Saldo customer telah ditambahkan.');

            $this->closeModal();

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error approving topup: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function reject()
    {
        $this->validate([
            'rejectionReason' => 'required|string|max:500',
        ], [
            'rejectionReason.required' => 'Alasan penolakan harus diisi',
        ]);

        if (!$this->selectedTransaction || $this->selectedTransaction->status !== 'waiting_approval') {
            session()->flash('error', 'Request tidak valid atau sudah diproses.');
            return;
        }

        try {
            // Update transaction status
            $this->selectedTransaction->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $this->rejectionReason,
            ]);

            // Send notification to customer
            $this->selectedTransaction->user->notify(new TopupRejected($this->selectedTransaction));

            session()->flash('success', 'Request top-up telah ditolak.');

            $this->closeModal();

        } catch (\Exception $e) {
            \Log::error('Error rejecting topup: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Super admin can see all pending requests
        $pendingRequests = BalanceTransaction::where('type', 'topup')
            ->where('status', 'waiting_approval')
            ->with(['user', 'user.city'])
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return view('superadmin.topup-approval', [
            'pendingRequests' => $pendingRequests,
        ]);
    }
}

