<?php

namespace App\Livewire\Customer;

use App\Models\BalanceTransaction;
use Livewire\Component;
use Livewire\Attributes\On;

class BalanceTransactionHistory extends Component
{
    #[On('balance-updated')]
    public function refreshTransactions()
    {
        $this->dispatch('$refresh');
    }

    public function render()
    {
        $user = auth()->user();

        $transactions = BalanceTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('livewire.customer.balance-transaction-history', [
            'transactions' => $transactions,
        ]);
    }
}
