<?php

namespace App\Livewire\Mitra\Helps;

use App\Models\Help;
use App\Models\BalanceTransaction;
use App\Models\PartnerActivity;
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

        // 1. Stats Calculation
        $completedHelpsQuery = Help::where('mitra_id', $user->id)->whereIn('status', ['selesai', 'completed']);
        $totalCompletedCount = (clone $completedHelpsQuery)->count();
        $totalCompletedAmount = (clone $completedHelpsQuery)->sum('amount');
        $uniqueCustomersCount = (clone $completedHelpsQuery)->distinct('user_id')->count('user_id');

        $cancellationQuery = PartnerActivity::where('user_id', $user->id)
            ->whereIn('activity_type', [
                'cancel_requested',
                'cancel_accepted',
                'partner_request_cancel',
                'help_cancelled',
                'partner_cancel'
            ]);
        $totalCancelledCount = (clone $cancellationQuery)->count();

        // 2. Tab Query
        if ($this->activeTab === 'cancelled') {
            $cancelledActivitiesQuery = PartnerActivity::with(['help.user', 'help.city'])
                ->where('user_id', $user->id)
                ->whereIn('activity_type', [
                    'cancel_requested',
                    'cancel_accepted',
                    'partner_request_cancel',
                    'help_cancelled',
                    'partner_cancel'
                ]);

            if ($this->search) {
                $cancelledActivitiesQuery->where(function ($q) {
                    $q->where('description', 'like', '%' . $this->search . '%')
                        ->orWhereHas('help', function ($h) {
                            $h->where('title', 'like', '%' . $this->search . '%');
                        });
                });
            }

            $cancelledActivities = $cancelledActivitiesQuery->latest()->paginate(10);
            $helps = null;
        } else {
            $helpsQuery = Help::with(['user', 'city', 'rating'])
                ->where('mitra_id', $user->id)
                ->whereIn('status', ['selesai', 'completed']);

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

            $helps = $helpsQuery->paginate(10);
            $cancelledActivities = null;
        }

        return view('livewire.mitra.helps.completed-helps', [
            'helps'                => $helps,
            'cancelledActivities'  => $cancelledActivities,
            'totalCompletedCount'  => $totalCompletedCount,
            'totalCompletedAmount' => $totalCompletedAmount,
            'uniqueCustomersCount' => $uniqueCustomersCount,
            'totalCancelledCount'  => $totalCancelledCount,
        ]);
    }
}
