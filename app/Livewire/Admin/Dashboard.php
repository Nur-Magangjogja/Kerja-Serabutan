<?php

namespace App\Livewire\Admin;

use App\Models\Help;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Dashboard extends Component
{
    public function render()
    {
        // If current user is an admin, scope metrics to their assigned city
        if (auth()->user() && auth()->user()->role === 'admin') {
            $cityId = auth()->user()->city_id;
            $totalHelps = Help::where('city_id', $cityId)->count();
            $pendingHelps = Help::where('city_id', $cityId)->where('status', 'pending')->count();
            $activeHelps = Help::where('city_id', $cityId)->where('status', 'active')->count();
            $completedHelps = Help::where('city_id', $cityId)->where('status', 'completed')->count();
            $pendingVerifications = \App\Models\Registration::where('city_id', $cityId)->where('status', 'pending_verification')->count();
            $verifiedMitras = User::where('role', 'mitra')->where('city_id', $cityId)->count();
            
            // Pending topup approvals (filtered by city)
            $pendingTopups = \App\Models\BalanceTransaction::where('type', 'topup')
                ->where('status', 'waiting_approval')
                ->whereHas('user', function ($q) use ($cityId) {
                    $q->where('city_id', $cityId);
                })
                ->count();
        } else {
            $totalHelps = Help::count();
            $pendingHelps = Help::where('status', 'pending')->count();
            $activeHelps = Help::where('status', 'active')->count();
            $completedHelps = Help::where('status', 'completed')->count();
            $pendingVerifications = 0;
            $verifiedMitras = User::where('role', 'mitra')->count();
            
            // Pending topup approvals (all cities for super admin)
            $pendingTopups = \App\Models\BalanceTransaction::where('type', 'topup')
                ->where('status', 'waiting_approval')
                ->count();
        }

        // Health check
        $health = [
            'database' => [
                'status' => 'unknown',
            ],
            'queue' => [
                'status' => 'inactive',
                'pending' => null,
            ],
            'disk' => [
                'status' => 'unknown',
                'usage' => null,
            ],
        ];

        try {
            DB::select('select 1');
            $health['database']['status'] = 'ok';
        } catch (\Throwable $e) {
            $health['database']['status'] = 'down';
        }

        if (Schema::hasTable('jobs')) {
            try {
                $pending = DB::table('jobs')->count();
                $health['queue']['pending'] = $pending;
                $health['queue']['status'] = $pending > 0 ? 'warning' : 'ok';
            } catch (\Throwable $e) {
                $health['queue']['pending'] = null;
                $health['queue']['status'] = 'inactive';
            }
        } else {
            $health['queue']['pending'] = null;
            $health['queue']['status'] = 'inactive';
        }

        try {
            $path = base_path();
            $total = @disk_total_space($path);
            $free = @disk_free_space($path);
            if ($total > 0 && $free !== false) {
                $used = $total - $free;
                $usagePct = (int) round(($used / $total) * 100);
                $health['disk']['usage'] = $usagePct;
                if ($usagePct >= 90) {
                    $health['disk']['status'] = 'critical';
                } elseif ($usagePct >= 75) {
                    $health['disk']['status'] = 'warning';
                } else {
                    $health['disk']['status'] = 'ok';
                }
            } else {
                $health['disk']['usage'] = null;
                $health['disk']['status'] = 'unknown';
            }
        } catch (\Throwable $e) {
            $health['disk']['usage'] = null;
            $health['disk']['status'] = 'unknown';
        }

        return view('livewire.admin.dashboard', [
            'totalHelps' => $totalHelps,
            'pendingHelps' => $pendingHelps,
            'activeHelps' => $activeHelps,
            'completedHelps' => $completedHelps,
            'pendingVerifications' => $pendingVerifications,
            'verifiedMitras' => $verifiedMitras,
            'pendingTopups' => $pendingTopups,
            'health' => $health,
        ]);
    }
}
