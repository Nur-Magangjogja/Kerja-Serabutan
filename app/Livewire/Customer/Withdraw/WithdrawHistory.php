<?php

namespace App\Livewire\Customer\Withdraw;

use App\Models\WithdrawRequest;
use Livewire\Component;
use Livewire\WithPagination;

class WithdrawHistory extends Component
{
    use WithPagination;

    public $filterStatus = 'all'; // all, pending, completed, rejected
    public $showProofModal = false;
    public $selectedProofUrl = null;
    public $selectedWithdrawId = null;

    public function filterByStatus($status)
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function viewProof($id, $url)
    {
        $this->selectedWithdrawId = $id;
        $this->selectedProofUrl = $url;
        $this->showProofModal = true;
    }

    public function closeProofModal()
    {
        $this->showProofModal = false;
        $this->selectedProofUrl = null;
        $this->selectedWithdrawId = null;
    }

    public function render()
    {
        $query = WithdrawRequest::where('user_id', auth()->id());

        if ($this->filterStatus === 'pending') {
            $query->where('status', 'pending');
        } elseif ($this->filterStatus === 'completed') {
            $query->whereIn('status', ['completed', 'success']);
        } elseif ($this->filterStatus === 'rejected') {
            $query->where('status', 'rejected');
        }

        $withdraws = $query->latest()->paginate(10);

        return view('livewire.customer.withdraw.withdraw-history', [
            'withdraws' => $withdraws,
        ])->layout('layouts.app');
    }
}
