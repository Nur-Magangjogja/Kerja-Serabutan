<?php

namespace App\Livewire\Mitra\Transactions;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\BalanceTransaction;

#[Layout('layouts.mitra')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $filterType = 'all'; // 'all', 'earning', 'cancellation', 'withdraw', 'topup'
    public $selectedTransaction = null;

    protected $listeners = [
        'balance-updated' => '$refresh',
        'transaction-updated' => '$refresh',
    ];

    public function setFilter($type)
    {
        $this->filterType = $type;
        $this->resetPage();
    }

    public function showTransaction($id)
    {
        $transaction = BalanceTransaction::with('help')->find($id);
        
        if (!$transaction || $transaction->user_id !== auth()->id()) {
            session()->flash('error', 'Transaksi tidak ditemukan.');
            return;
        }

        $type = $transaction->type ?? 'earning';
        $isCredit = in_array($type, ['earning', 'topup', 'refund'], true);

        $typeLabel = match($type) {
            'earning' => 'Pendapatan Bantuan Selesai',
            'cancellation', 'penalty' => 'Pembatalan Tugas',
            'withdraw' => 'Penarikan Dana (Withdraw)',
            'topup' => 'Isi Ulang Saldo',
            'deduction' => 'Potongan Saldo',
            'refund' => 'Pengembalian Dana',
            default => 'Transaksi Saldo',
        };

        $helpTitle = $transaction->help?->title;
        $orderId = $transaction->order_id ?: ($transaction->help?->order_id ?? null);

        $this->selectedTransaction = [
            'id' => $transaction->id,
            'type' => $type,
            'type_label' => $typeLabel,
            'is_credit' => $isCredit,
            'status' => $transaction->status ?? 'completed',
            'amount' => (float) $transaction->amount,
            'admin_fee' => (float) ($transaction->admin_fee ?? 0),
            'total_payment' => (float) ($transaction->total_payment ?? $transaction->amount),
            'description' => $transaction->description,
            'payment_type' => $transaction->payment_type,
            'order_id' => $orderId,
            'reference_id' => $transaction->reference_id,
            'help_title' => $helpTitle,
            'created_at' => $transaction->created_at ? $transaction->created_at->format('d M Y • H:i') : '-',
            'created_at_human' => $transaction->created_at ? $transaction->created_at->diffForHumans() : '-',
        ];
    }

    public function closeTransaction()
    {
        $this->selectedTransaction = null;
    }

    public function render()
    {
        $query = BalanceTransaction::with('help')
            ->where('user_id', auth()->id());

        if ($this->filterType === 'earning') {
            $query->where('type', 'earning');
        } elseif ($this->filterType === 'cancellation' || $this->filterType === 'penalty') {
            $query->whereIn('type', ['cancellation', 'penalty']);
        } elseif ($this->filterType === 'withdraw') {
            $query->where('type', 'withdraw');
        } elseif ($this->filterType === 'topup') {
            $query->where('type', 'topup');
        }

        $transactions = $query->latest()->paginate(10);

        return view('livewire.mitra.transactions.index', [
            'transactions' => $transactions,
        ]);
    }
}
