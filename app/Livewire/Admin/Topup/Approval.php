<?php

namespace App\Livewire\Admin\Topup;

use App\Models\BalanceTransaction;
use App\Models\User;
use App\Models\UserBalance;
use App\Notifications\TopupApproved;
use App\Notifications\TopupRejected;
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
    public $rejectionReason = '';
    
    protected $listeners = ['topupRequestCreated' => '$refresh'];

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
        // Admin tidak memiliki akses untuk approve
        session()->flash('error', 'Admin tidak memiliki akses untuk approve. Hubungi Super Admin.');
        return;
    }

    public function reject()
    {
        // Admin tidak memiliki akses untuk reject
        session()->flash('error', 'Admin tidak memiliki akses untuk reject. Hubungi Super Admin.');
        $this->closeModal();
        return;
    }

    public function render()
    {
        $user = auth()->user();
        
        $query = BalanceTransaction::where('type', 'topup')
            ->where('status', 'waiting_approval')
            ->with(['user', 'user.city']);

        // Filter by city for admin
        if ($user->role === 'admin') {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('city_id', $user->city_id);
            });
        }

        $pendingRequests = $query->orderBy('created_at', 'asc')->paginate(15);

        return view('admin.topup-approval', [
            'pendingRequests' => $pendingRequests,
        ]);
    }
}

