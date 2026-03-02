<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class DashboardCards extends Component
{
    public $newLastHour = 0;
    public $todayCount = 0;
    public $newUsers24h = 0;
    public $activeMitra = 0;
    public $unresolved = 0;
    public $avgCompletionHours = null;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        // Use try/catch to avoid breaking when models/columns are different
        try {
            if (class_exists(\App\Models\Help::class)) {
                $this->newLastHour = \App\Models\Help::where('created_at', '>=', now()->subHour())->count();
                $this->todayCount = \App\Models\Help::whereDate('created_at', now()->toDateString())->count();
                // unresolved: try common statuses
                $this->unresolved = \App\Models\Help::whereIn('status', ['pending', 'menunggu_mitra', 'open'])->count();

                // average completion time in hours if completed_at exists, fallback null
                if (\Schema::hasColumn('helps', 'completed_at')) {
                    $avgSeconds = \App\Models\Help::whereNotNull('completed_at')
                        ->avg(DB::raw('TIMESTAMPDIFF(SECOND, created_at, completed_at)'));
                    $this->avgCompletionHours = $avgSeconds ? round($avgSeconds / 3600, 2) : null;
                }
            }

            if (class_exists(\App\Models\User::class)) {
                $this->newUsers24h = \App\Models\User::where('created_at', '>=', now()->subDay())->count();

                // active mitra heuristics: role or is_partner flag
                if (\Schema::hasColumn('users', 'role')) {
                    $this->activeMitra = \App\Models\User::where('role', 'mitra')->count();
                } elseif (\Schema::hasColumn('users', 'is_partner')) {
                    $this->activeMitra = \App\Models\User::where('is_partner', 1)->count();
                } else {
                    $this->activeMitra = 0;
                }
            }
        } catch (\Throwable $e) {
            logger()->error('DashboardCards loadData error: ' . $e->getMessage());
            $this->newLastHour = $this->todayCount = $this->newUsers24h = $this->activeMitra = $this->unresolved = 0;
            $this->avgCompletionHours = null;
        }
    }

    public function render()
    {
        // Keep polling safe: Livewire `wire:poll` will call mount/loadData automatically on re-render.
        return view('livewire.admin.dashboard-cards');
    }
}
