<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Log;

class Dashboard extends Component
{
    public $totalHelps = 0;
    public $pendingHelps = 0;
    public $activeHelps = 0;
    public $completedHelps = 0;
    public $pendingVerifications = 0;
    public $verifiedMitras = 0;
    public $latestHelps = [];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        try {
            if (class_exists(\App\Models\Help::class)) {
                $this->totalHelps = \App\Models\Help::count();
                $this->pendingHelps = \App\Models\Help::where('status', 'pending')->count();
                $this->activeHelps = \App\Models\Help::where('status', 'active')->count();
                $this->completedHelps = \App\Models\Help::where('status', 'completed')->count();
                $this->latestHelps = \App\Models\Help::latest()->take(6)->get();
            }

            if (class_exists(\App\Models\User::class)) {
                // Try to guess a KTP verification column; fallback to 0 if not present
                try {
                    $this->pendingVerifications = \App\Models\User::whereNull('ktp_verified_at')->count();
                    $this->verifiedMitras = \App\Models\User::whereNotNull('ktp_verified_at')->count();
                } catch (\Throwable $e) {
                    $this->pendingVerifications = 0;
                    $this->verifiedMitras = 0;
                }
            }

            // Prepare a simple chart dataset based on last 7 days of Help counts if model exists
            $chartLabels = [];
            $chartData = [];
            if (class_exists(\App\Models\Help::class)) {
                for ($i = 6; $i >= 0; $i--) {
                    $day = now()->subDays($i);
                    $chartLabels[] = $day->format('j M');
                    $chartData[] = \App\Models\Help::whereDate('created_at', $day->toDateString())->count();
                }
            } else {
                $chartLabels = ['-6', '-5', '-4', '-3', '-2', '-1', 'now'];
                $chartData = [0, 0, 0, 0, 0, 0, 0];
            }

            $this->dispatchBrowserEvent('chartDataUpdated', [
                'labels' => $chartLabels,
                'data' => $chartData,
            ]);
        } catch (\Throwable $e) {
            Log::error('Livewire Admin Dashboard loadData error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
