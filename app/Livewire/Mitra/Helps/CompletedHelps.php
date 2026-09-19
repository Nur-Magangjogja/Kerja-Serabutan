<?php

namespace App\Livewire\Mitra\Helps;

use App\Models\Help;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.mitra')]
class CompletedHelps extends Component
{
    use WithPagination;

    public $activeTab = 'completed'; // 'completed', 'cancelled'
    public $search = '';
    public $sortBy = 'latest';

    // Detail modal state
    public $showDetailModal = false;
    public $selectedHelp = null;

    protected $listeners = [
        'balance-updated' => '$refresh',
        'help-updated' => '$refresh',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        // 1. Stats Queries
        $completedStats = Help::where('mitra_id', $user->id)
            ->whereIn('status', [Help::STATUS_SELESAI, 'completed'])
            ->selectRaw("COUNT(*) as total_count")
            ->selectRaw("COALESCE(SUM(amount), 0) as total_amount")
            ->selectRaw("COUNT(DISTINCT user_id) as unique_customers")
            ->first();

        $totalCompletedCount = (int) ($completedStats->total_count ?? 0);
        $totalCompletedAmount = (int) ($completedStats->total_amount ?? 0);
        $uniqueCustomersCount = (int) ($completedStats->unique_customers ?? 0);

        $batalStatuses = [
            Help::STATUS_DIBATALKAN,
            'batal',
            'cancelled',
            'canceled',
            Help::STATUS_PARTNER_CANCEL_REQUESTED,
            Help::STATUS_CUSTOMER_CANCEL_REQUESTED,
        ];

        $baseCancellationQuery = Help::where(function ($q) use ($user, $batalStatuses) {
            // 1. Current mitra_id on help matching user
            $q->where(function ($sq) use ($user, $batalStatuses) {
                $sq->where('mitra_id', $user->id)
                   ->where(function ($ssq) use ($batalStatuses) {
                       $ssq->whereIn('status', $batalStatuses)
                           ->orWhereHas('cancelRequests')
                           ->orWhereHas('partnerReports');
                   });
            });

            // 2. Mitra was unlinked during cancellation (cancelled_mitra_ids JSON)
            $q->orWhere(function ($sq) use ($user) {
                $sq->whereJsonContains('cancelled_mitra_ids', $user->id);
            });

            // 3. Cancel request involving this partner
            $q->orWhereHas('cancelRequests', function ($sq) use ($user) {
                $sq->where('partner_id', $user->id);
            });

            // 4. Partner report involving this partner
            $q->orWhereHas('partnerReports', function ($sq) use ($user) {
                $sq->where('reported_user_id', $user->id)
                   ->orWhere('reporter_id', $user->id);
            });
        });

        $totalCancelledCount = (clone $baseCancellationQuery)->count();

        // 2. Tab Query
        $perPage = 10;

        if ($this->activeTab === 'cancelled') {
            $cancellationsQuery = (clone $baseCancellationQuery)->with([
                'user',
                'city',
                'district',
                'mitra',
                'cancelRequests' => fn($cr) => $cr->with('reviewedBy')->latest(),
                'partnerReports' => fn($pr) => $pr->with('resolvedBy')->latest(),
            ]);

            if ($this->search) {
                $cancellationsQuery->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhere('order_id', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', fn($u) => $u->where('name', 'like', '%' . $this->search . '%'))
                      ->orWhereHas('cancelRequests', function ($cr) {
                          $cr->where('reason', 'like', '%' . $this->search . '%')
                             ->orWhere('notes', 'like', '%' . $this->search . '%')
                             ->orWhere('admin_notes', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('partnerReports', function ($pr) {
                          $pr->where('title', 'like', '%' . $this->search . '%')
                             ->orWhere('message', 'like', '%' . $this->search . '%')
                             ->orWhere('admin_notes', 'like', '%' . $this->search . '%');
                      });
                });
            }

            if ($this->sortBy === 'oldest') {
                $cancellationsQuery->oldest();
            } else {
                $cancellationsQuery->latest();
            }

            $cancellations = $cancellationsQuery->paginate($perPage);
            $helps = null;
        } else {
            $helpsQuery = Help::with(['user', 'city', 'rating'])
                ->where('mitra_id', $user->id)
                ->whereIn('status', [Help::STATUS_SELESAI, 'completed']);

            if ($this->search) {
                $helpsQuery->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($u) {
                            $u->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            }

            if ($this->sortBy === 'oldest') {
                $helpsQuery->oldest();
            } else {
                $helpsQuery->latest();
            }

            $helps = $helpsQuery->paginate($perPage);
            $cancellations = null;
        }

        return view('livewire.mitra.helps.completed-helps', [
            'helps'                => $helps,
            'cancellations'        => $cancellations,
            'cancelledActivities'  => $cancellations, // backward-compat alias
            'totalCompletedCount'  => $totalCompletedCount,
            'totalCompletedAmount' => $totalCompletedAmount,
            'uniqueCustomersCount' => $uniqueCustomersCount,
            'totalCancelledCount'  => $totalCancelledCount,
        ]);
    }
}
