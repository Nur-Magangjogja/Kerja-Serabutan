<?php

namespace App\Livewire\Customer\Transactions;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\BalanceTransaction;
use App\Models\Help;

class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $filterType = 'all'; // 'all', 'topup', 'withdraw', 'payment', 'completed', 'refund'
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
        $transaction = BalanceTransaction::with(['help.mitra'])->find($id);
        
        if (!$transaction || $transaction->user_id !== auth()->id()) {
            session()->flash('error', 'Transaksi tidak ditemukan.');
            return;
        }

        $type = $transaction->type ?? 'deduction';
        $isCredit = in_array($type, ['topup', 'refund', 'earning'], true);
        $help = $transaction->help;
        $isHelpCompleted = $help && ($help->status === Help::STATUS_SELESAI || $help->escrow_status === Help::ESCROW_STATUS_RELEASED);

        if ($type === 'topup') {
            $typeLabel = 'Top Up Saldo';
        } elseif ($type === 'withdraw') {
            $typeLabel = 'Penarikan Saldo (Withdraw)';
        } elseif ($type === 'refund') {
            $typeLabel = 'Pengembalian Dana (Refund)';
        } elseif ($type === 'escrow_lock' || $type === 'deduction') {
            $typeLabel = $isHelpCompleted ? 'Pekerjaan Selesai' : 'Pembayaran Bantuan (Dana Tahan)';
        } elseif (in_array($type, ['cancellation', 'penalty'], true)) {
            $typeLabel = 'Pembatalan Tugas';
        } elseif ($type === 'earning') {
            $typeLabel = 'Pendapatan Bantuan';
        } else {
            $typeLabel = 'Transaksi Saldo';
        }

        $helpTitle = $help?->title;
        $orderId = $transaction->request_code ?: ($transaction->order_id ?: ($help?->order_id ?? null));

        $this->selectedTransaction = [
            'id' => $transaction->id,
            'type' => $type,
            'type_label' => $typeLabel,
            'is_credit' => $isCredit,
            'is_help_completed' => $isHelpCompleted,
            'help_status' => $help?->status,
            'help_status_label' => $help?->statusPresenter()['label'] ?? null,
            'mitra_name' => $help?->mitra?->name,
            'completed_at' => $help?->completed_at ? $help->completed_at->format('d M Y • H:i') : null,
            'status' => $transaction->status ?? 'completed',
            'amount' => (float) $transaction->amount,
            'admin_fee' => (float) ($transaction->admin_fee ?? 0),
            'total_payment' => (float) ($transaction->total_payment ?? $transaction->amount),
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
        $query = BalanceTransaction::with(['help.mitra'])
            ->where('user_id', auth()->id());

        if ($this->filterType === 'topup') {
            $query->where('type', 'topup');
        } elseif ($this->filterType === 'withdraw') {
            $query->where('type', 'withdraw');
        } elseif ($this->filterType === 'payment' || $this->filterType === 'escrow') {
            // Dana Tahan (Escrow) - Bantuan yang masih berlangsung / belum selesai
            $query->whereIn('type', ['escrow_lock', 'deduction'])
                  ->where(function ($q) {
                      $q->whereHas('help', function ($hq) {
                          $hq->where('status', '!=', Help::STATUS_SELESAI)
                             ->where('escrow_status', '!=', Help::ESCROW_STATUS_RELEASED);
                      })->orWhereDoesntHave('help');
                  });
        } elseif ($this->filterType === 'completed' || $this->filterType === 'selesai') {
            // Pekerjaan Selesai - Bantuan yang telah selesai dan dana telah diteruskan
            $query->whereIn('type', ['escrow_lock', 'deduction'])
                  ->whereHas('help', function ($q) {
                      $q->where('status', Help::STATUS_SELESAI)
                        ->orWhere('escrow_status', Help::ESCROW_STATUS_RELEASED);
                  });
        } elseif ($this->filterType === 'refund') {
            $query->where('type', 'refund');
        }

        $transactions = $query->latest()->paginate(10);

        return view('livewire.customer.transactions.index', [
            'transactions' => $transactions,
        ]);
    }
}
