<?php
namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use App\Models\BalanceTransaction;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.superadmin')]
class TransactionsLog extends Component
{
    use WithPagination;

    public $search = '';
    public $type = 'all'; // types: all, topup, withdraw, other
    public $perPage = 15;
    public $from = null;
    public $to = null;

    protected $queryString = ['search' => ['except' => ''], 'type' => ['except' => 'all']];

    public function mount()
    {
        // Set default date range only on first load
        if (!request()->has('from') && !$this->from) {
            $this->from = now()->startOfMonth()->format('Y-m-d');
        }
        if (!request()->has('to') && !$this->to) {
            $this->to = now()->format('Y-m-d');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
    }

    public function exportExcel()
    {
        // Will be implemented with Laravel Excel
        $this->dispatch('alert', ['type' => 'info', 'message' => 'Export Excel akan tersedia segera']);
    }

    public function exportPdf()
    {
        // Will be implemented with DomPDF
        $this->dispatch('alert', ['type' => 'info', 'message' => 'Export PDF akan tersedia segera']);
    }

    private function getBaseQuery()
    {
        $query = BalanceTransaction::query();

        if ($this->type && $this->type !== 'all') {
            $query->where('type', $this->type);
        }

        if ($this->search) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%{$s}%")
                    ->orWhereHas('user', function ($qu) use ($s) {
                        $qu->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%");
                    });
            });
        }

        if ($this->from) {
            $query->whereDate('created_at', '>=', $this->from);
        }
        if ($this->to) {
            $query->whereDate('created_at', '<=', $this->to);
        }

        return $query;
    }

    public function render()
    {
        $query = $this->getBaseQuery()->with('user');
        $transactions = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        // Calculate summary statistics (filtered)
        $summaryQuery = $this->getBaseQuery();
        
        $totalRevenue = $summaryQuery->clone()->where('type', 'topup')->sum('amount');
        $totalWithdraw = $summaryQuery->clone()->where('type', 'withdraw')->sum('amount');
        $totalTransactions = $summaryQuery->clone()->count();
        $avgTransaction = $totalTransactions > 0 ? $summaryQuery->clone()->avg('amount') : 0;

        return view('superadmin.transactions-log', [
            'transactions' => $transactions,
            'totalRevenue' => $totalRevenue,
            'totalWithdraw' => $totalWithdraw,
            'totalTransactions' => $totalTransactions,
            'avgTransaction' => $avgTransaction,
        ]);
    }
}
