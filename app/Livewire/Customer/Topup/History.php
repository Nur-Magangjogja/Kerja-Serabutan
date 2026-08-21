<?php

namespace App\Livewire\Customer\Topup;

use App\Models\BalanceTransaction;
use Livewire\Component;
use Livewire\WithPagination;

class History extends Component
{
    use WithPagination;

    public $filterStatus = 'all';
    public $selectedTransaction = null;
    public $showDetailModal = false;

    protected $queryString = ['filterStatus'];

    public function mount()
    {
        //
    }

    public function filterByStatus($status)
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function viewDetail($transactionId)
    {
        $this->selectedTransaction = BalanceTransaction::with(['user', 'approvedBy'])->find($transactionId);
        $this->showDetailModal = true;
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedTransaction = null;
    }

    public function render()
    {
        $query = BalanceTransaction::where('user_id', auth()->id())
            ->where('type', 'topup')
            ->with(['approvedBy']);

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.customer.topup.history', [
            'transactions' => $transactions,
        ]);
    }
}



