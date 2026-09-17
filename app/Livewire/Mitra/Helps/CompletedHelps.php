<?php

namespace App\Livewire\Mitra\Helps;

use App\Models\Help;
use App\Models\HelpCancelRequest;
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

        // 1 query agregasi menggantikan 3 query terpisah (count, sum, distinct count)
        $completedStats = Help::where('mitra_id', $user->id)
            ->whereIn('status', [Help::STATUS_SELESAI, 'completed'])
            ->selectRaw("COUNT(*) as total_count")
            ->selectRaw("COALESCE(SUM(amount), 0) as total_amount")
            ->selectRaw("COUNT(DISTINCT user_id) as unique_customers")
            ->first();

        $totalCompletedCount = (int) $completedStats->total_count;
        $totalCompletedAmount = (int) $completedStats->total_amount;
        $uniqueCustomersCount = (int) $completedStats->unique_customers;

        $cancellationQuery = HelpCancelRequest::where(function ($q) use ($user) {
            $q->where('partner_id', $user->id)
              ->orWhereHas('help', fn($h) => $h->where('mitra_id', $user->id));
        });
        $totalCancelledCount = (clone $cancellationQuery)->count();

        // 2. Tab Query
        $perPage = 10;
        $page = $this->getPage();

        if ($this->activeTab === 'cancelled') {
            $cancellationsQuery = HelpCancelRequest::with(['help.user', 'help.city', 'reviewedBy'])
                ->where(function ($q) use ($user) {
                    $q->where('partner_id', $user->id)
                      ->orWhereHas('help', fn($h) => $h->where('mitra_id', $user->id));
                });

            if ($this->search) {
                $cancellationsQuery->where(function ($q) {
                    $q->where('reason', 'like', '%' . $this->search . '%')
                      ->orWhere('notes', 'like', '%' . $this->search . '%')
                      ->orWhere('admin_notes', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', fn($c) => $c->where('name', 'like', '%' . $this->search . '%'))
                      ->orWhereHas('help', function ($h) {
                          $h->where('title', 'like', '%' . $this->search . '%')
                            ->orWhere('order_id', 'like', '%' . $this->search . '%')
                            ->orWhereHas('user', fn($u) => $u->where('name', 'like', '%' . $this->search . '%'));
                      });
                });
            }

            if ($this->sortBy === 'oldest') {
                $cancellationsQuery->oldest('requested_at');
            } else {
                $cancellationsQuery->latest('requested_at');
            }

            if (empty($this->search)) {
                $items = $cancellationsQuery->forPage($page, $perPage)->get();
                $cancellations = new \Illuminate\Pagination\LengthAwarePaginator(
                    $items,
                    $totalCancelledCount,
                    $perPage,
                    $page,
                    ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'pageName' => 'page']
                );
            } else {
                $cancellations = $cancellationsQuery->paginate($perPage);
            }

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

            if (empty($this->search)) {
                $items = $helpsQuery->forPage($page, $perPage)->get();
                $helps = new \Illuminate\Pagination\LengthAwarePaginator(
                    $items,
                    $totalCompletedCount,
                    $perPage,
                    $page,
                    ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'pageName' => 'page']
                );
            } else {
                $helps = $helpsQuery->paginate($perPage);
            }

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
