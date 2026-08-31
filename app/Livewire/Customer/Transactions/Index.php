<?php

namespace App\Livewire\Customer\Transactions;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\BalanceTransaction;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $filterType = 'all'; // 'all', 'topup', 'payment', 'refund'
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

        $type = $transaction->type ?? 'deduction';
        $isCredit = in_array($type, ['topup', 'refund', 'earning'], true);

        $typeLabel = match($type) {
            'topup' => 'Top Up Saldo',
            'withdraw' => 'Penarikan Saldo (Withdraw)',
            'refund' => 'Pengembalian Dana (Refund)',
            'escrow_lock' => 'Pembayaran Permintaan Bantuan',
            'deduction' => 'Potongan / Penyesuaian Saldo',
            'cancellation', 'penalty' => 'Pembatalan Tugas',
            'earning' => 'Pendapatan Bantuan',
            default => 'Transaksi Saldo',
        };

        $helpTitle = $transaction->help?->title;
        $orderId = $transaction->request_code ?: ($transaction->order_id ?: ($transaction->help?->order_id ?? null));

        $this->selectedTransaction = [
            'id' => $transaction->id,
            'type' => $type,
            'type_label' => $typeLabel,
            'is_credit' => $isCredit,
            'status' => $transaction->status ?? 'completed',
            'amount' => (float) $transaction->amount,
            'description' => $transaction->description,
            'payment_type' => $transaction->payment_type ?: ($transaction->payment_method ? strtoupper($transaction->payment_method) : null),
            'order_id' => $orderId,
            'reference_id' => $transaction->reference_id,
            'request_code' => $transaction->request_code,
            'proof_of_payment' => $transaction->proof_of_payment,
            'rejection_reason' => $transaction->rejection_reason ?? $transaction->notes,
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

        if ($this->filterType === 'topup') {
            $query->where('type', 'topup');
        } elseif ($this->filterType === 'withdraw') {
            $query->where('type', 'withdraw');
        } elseif ($this->filterType === 'payment') {
            $query->whereIn('type', ['escrow_lock', 'deduction']);
        } elseif ($this->filterType === 'refund') {
            $query->where('type', 'refund');
        }

        $transactions = $query->latest()->paginate(10);

        return view('livewire.customer.transactions.index', [
            'transactions' => $transactions,
        ]);
    }
}
