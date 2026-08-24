<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\Help;
use App\Models\Registration;
use App\Models\User;
use App\Models\BalanceTransaction;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.admin')]
#[Title('Dashboard Admin')]
class Index extends Component
{
    public function render()
    {
        $user = auth()->user();
        $adminCityId = ($user && $user->role === 'admin') ? $user->city_id : null;

        // Base Help Query
        $helpQuery = Help::query();
        if ($adminCityId) {
            $helpQuery->where(function ($q) use ($adminCityId) {
                $q->where('city_id', $adminCityId)
                  ->orWhereHas('customer', function ($cq) use ($adminCityId) {
                      $cq->where('city_id', $adminCityId);
                  });
            });
        }

        $totalHelps = (clone $helpQuery)->count();
        $pendingHelps = (clone $helpQuery)->whereIn('status', ['pending', 'menunggu_mitra'])->count();
        $activeHelps = (clone $helpQuery)->whereIn('status', ['active', 'taken', 'memperoleh_mitra', 'sedang_diproses', 'in_progress', 'partner_on_the_way', 'partner_arrived', 'waiting_customer_confirmation'])->count();
        $completedHelps = (clone $helpQuery)->whereIn('status', ['completed', 'selesai'])->count();

        // KTP / Registration Verifications
        if ($adminCityId) {
            $pendingVerifications = Registration::where('city_id', $adminCityId)
                ->where('status', 'pending_verification')
                ->count();
            $verifiedMitras = User::where('role', 'mitra')
                ->where('city_id', $adminCityId)
                ->count();
        } else {
            $pendingVerifications = Registration::where('status', 'pending_verification')->count();
            $verifiedMitras = User::where('role', 'mitra')->count();
        }

        // Pending topup approvals (filtered by admin's city)
        $topupQuery = BalanceTransaction::where('type', 'topup')
            ->where('status', 'waiting_approval');
        if ($adminCityId) {
            $topupQuery->whereHas('user', function ($q) use ($adminCityId) {
                $q->where('city_id', $adminCityId);
            });
        }
        $pendingTopups = $topupQuery->count();

        // Pending withdraw requests (filtered by admin's city)
        $withdrawQuery = \App\Models\WithdrawRequest::where('status', \App\Models\WithdrawRequest::STATUS_PENDING);
        if ($adminCityId) {
            $withdrawQuery->whereHas('user', function ($q) use ($adminCityId) {
                $q->where('city_id', $adminCityId)->orWhereNull('city_id');
            });
        }
        $pendingWithdraws = $withdrawQuery->count();

        // Latest 6 helps
        $latestHelps = (clone $helpQuery)
            ->with(['customer', 'user', 'city'])
            ->latest()
            ->take(6)
            ->get();

        // 7-day Chart Data (Single Grouped Query)
        $startDate = now()->subDays(6)->startOfDay();
        $countsByDate = (clone $helpQuery)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, count(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->all();

        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $chartLabels[] = $day->format('j M');
            $dateKey = $day->toDateString();
            $chartData[] = (int) ($countsByDate[$dateKey] ?? 0);
        }

        return view('livewire.admin.dashboard.index', [
            'adminCityId'          => $adminCityId,
            'totalHelps'           => $totalHelps,
            'pendingHelps'         => $pendingHelps,
            'activeHelps'          => $activeHelps,
            'completedHelps'       => $completedHelps,
            'pendingVerifications' => $pendingVerifications,
            'verifiedMitras'       => $verifiedMitras,
            'pendingTopups'        => $pendingTopups,
            'pendingWithdraws'     => $pendingWithdraws,
            'latestHelps'          => $latestHelps,
            'chartLabels'          => $chartLabels,
            'chartData'            => $chartData,
        ]);
    }
}
