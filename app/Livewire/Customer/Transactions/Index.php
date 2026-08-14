<?php

namespace App\Livewire\Customer\Transactions;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\BalanceTransaction;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $selectedTransaction = null;

    public function showTransaction($id)
    {
        $transaction = BalanceTransaction::find($id);
        
        if (!$transaction || $transaction->user_id !== auth()->id()) {
            session()->flash('error', 'Transaksi tidak ditemukan.');
            return;
        }

        $this->selectedTransaction = [
            'id' => $transaction->id,
            'type' => $transaction->type ?? 'debit',
            'amount' => $transaction->amount,
            'description' => $transaction->description,
            'payment_type' => $transaction->payment_type,
            'order_id' => $transaction->order_id,
            'reference_id' => $transaction->reference_id,
            'created_at' => $transaction->created_at->format('d M Y • H:i'),
            'created_at_human' => $transaction->created_at->diffForHumans(),
        ];
    }

    public function closeTransaction()
    {
        $this->selectedTransaction = null;
    }

    public function render()
    {
        $transactions = BalanceTransaction::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('livewire.customer.transactions.index', [
            'transactions' => $transactions,
        ]);
    }
}
