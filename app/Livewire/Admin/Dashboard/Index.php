<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\Help;
use App\Models\Registration;
use App\Models\User;
use App\Models\City;
use App\Models\BalanceTransaction;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;

#[Layout('layouts.admin')]
#[Title('Dashboard Admin')]
class Index extends Component
{
    public $selectedMonth = '';
    public $selectedCity = 'all';
    protected $listeners = [
        'admin-city-changed' => 'onAdminCityChanged',
    ];

    public function mount()
    {
        // Default to current month (e.g. 2026-09)
        $this->selectedMonth = Carbon::today()->format('Y-m');
        $this->selectedCity = auth()->user()?->getActiveAdminCityFilter() ?? 'all';
    }

    public function updatedSelectedMonth()
    {
        $this->dispatch('chart-refresh');
    }

    public function setMonth(string $month)
    {
        $this->selectedMonth = $month;
        $this->dispatch('chart-refresh');
    }

    public function setCityFilter(string $city)
    {
        $this->selectedCity = $city;
        auth()->user()?->setActiveAdminCityFilter($city);
        $this->dispatch('chart-refresh');
        $this->dispatch('admin-city-changed', cityId: $city);
    }

    public function onAdminCityChanged($cityId = null)
    {
        $admin = auth()->user();
        $targetCity = (string) ($cityId ?? ($admin ? $admin->getActiveAdminCityFilter() : 'all'));
        if ($admin && $admin->role === 'admin') {
            $admin->setActiveAdminCityFilter($targetCity);
        }
        $this->selectedCity = $targetCity;
        $this->dispatch('chart-refresh');
    }

    public function prevMonth()
    {
        if ($this->selectedMonth === 'all') {
            $this->selectedMonth = Carbon::today()->subMonth()->format('Y-m');
        } else {
            $this->selectedMonth = Carbon::parse($this->selectedMonth . '-01')->subMonth()->format('Y-m');
        }
        $this->dispatch('chart-refresh');
    }

    public function nextMonth()
    {
        if ($this->selectedMonth === 'all') {
            $this->selectedMonth = Carbon::today()->format('Y-m');
        } else {
            $this->selectedMonth = Carbon::parse($this->selectedMonth . '-01')->addMonth()->format('Y-m');
        }
        $this->dispatch('chart-refresh');
    }

    public function setCurrentMonth()
    {
        $this->selectedMonth = Carbon::today()->format('Y-m');
        $this->dispatch('chart-refresh');
    }

    public function setAllPeriod()
    {
        $this->selectedMonth = 'all';
        $this->dispatch('chart-refresh');
    }

    public function render()
    {
        $user = auth()->user();
        
        // Multi-City Resolution
        $allowedCityIds = ($user && $user->role === 'admin') ? $user->getAdminCityIds() : [];
        $managedCities = ($user && $user->role === 'admin') ? $user->getAdminCities() : collect();

        // Determine active city scope
        if ($user && $user->role === 'admin') {
            if ($this->selectedCity !== 'all' && in_array((int) $this->selectedCity, $allowedCityIds, true)) {
                $activeCityIds = [(int) $this->selectedCity];
                $activeCityLabel = $managedCities->firstWhere('id', (int) $this->selectedCity)?->name ?? 'Wilayah Terpilih';
            } else {
                $this->selectedCity = $user->getActiveAdminCityFilter();
                $activeCityIds = $user->getEffectiveAdminCityIds();
                $activeCityLabel = $user->active_admin_city_label;
            }
        } else {
            $activeCityIds = $allowedCityIds;
            $activeCityLabel = 'Semua Wilayah';
        }

        // 1. Build Available Months list (Current month + past 11 months)
        $availableMonths = [];
        $currentMonthKey = Carbon::today()->format('Y-m');
        $availableMonths[$currentMonthKey] = [
            'key' => $currentMonthKey,
            'label' => 'Bulan Ini (' . Carbon::today()->translatedFormat('F Y') . ')',
            'short_label' => Carbon::today()->translatedFormat('F Y'),
            'is_current' => true,
        ];

        for ($i = 1; $i <= 11; $i++) {
            $dt = Carbon::today()->subMonths($i);
            $key = $dt->format('Y-m');
            $availableMonths[$key] = [
                'key' => $key,
                'label' => $dt->translatedFormat('F Y'),
                'short_label' => $dt->translatedFormat('F Y'),
                'is_current' => false,
            ];
        }

        if (empty($this->selectedMonth)) {
            $this->selectedMonth = $currentMonthKey;
        }

        // Determine date range
        $isAllPeriod = ($this->selectedMonth === 'all');
        $periodLabel = $isAllPeriod ? 'Semua Periode (Akumulasi)' : Carbon::parse($this->selectedMonth . '-01')->translatedFormat('F Y');

        if (!$isAllPeriod) {
            $selectedMonthCarbon = Carbon::parse($this->selectedMonth . '-01');
            $startOfMonth = $selectedMonthCarbon->copy()->startOfMonth();
            $endOfMonth = $selectedMonthCarbon->copy()->endOfMonth();
        } else {
            $selectedMonthCarbon = Carbon::today();
            $startOfMonth = null;
            $endOfMonth = null;
        }

        // 2. Base Help Query (Multi-City Scoped)
        $baseHelpQuery = Help::query();
        if (!empty($activeCityIds)) {
            $baseHelpQuery->where(function ($q) use ($activeCityIds) {
                $q->whereIn('city_id', $activeCityIds)
                  ->orWhereHas('customer', function ($cq) use ($activeCityIds) {
                      $cq->whereIn('city_id', $activeCityIds);
                  });
            });
        } elseif ($user && $user->role === 'admin') {
            $baseHelpQuery->whereRaw('1 = 0');
        }

        $helpQuery = clone $baseHelpQuery;
        if (!$isAllPeriod) {
            $helpQuery->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        }

        // Calculate Synchronized Operational Metrics
        $totalHelps = (clone $helpQuery)->count();
        $pendingHelps = (clone $helpQuery)->whereIn('status', ['pending', 'menunggu_mitra'])->count();
        $activeHelps = (clone $helpQuery)->whereIn('status', ['active', 'taken', 'memperoleh_mitra', 'sedang_diproses', 'in_progress', 'partner_on_the_way', 'partner_arrived', 'waiting_customer_confirmation'])->count();
        $completedHelps = (clone $helpQuery)->whereIn('status', ['completed', 'selesai'])->count();
        $cancelledHelps = (clone $helpQuery)->whereIn('status', ['cancelled', 'dibatalkan', 'rejected'])->count();

        // 3. KTP / Registration Verifications
        $baseRegQuery = Registration::query();
        if (!empty($activeCityIds)) {
            $baseRegQuery->whereIn('city_id', $activeCityIds);
        } elseif ($user && $user->role === 'admin') {
            $baseRegQuery->whereRaw('1 = 0');
        }

        $regQuery = clone $baseRegQuery;
        if (!$isAllPeriod) {
            $regQuery->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        }
        $pendingVerifications = (clone $regQuery)->whereIn('status', ['pending', 'pending_verification'])->count();

        // Total Verified Mitras
        $mitraQuery = User::where('role', 'mitra');
        if (!empty($activeCityIds)) {
            $mitraQuery->whereIn('city_id', $activeCityIds);
        } elseif ($user && $user->role === 'admin') {
            $mitraQuery->whereRaw('1 = 0');
        }
        $totalAllMitras = (clone $mitraQuery)->count();
        if (!$isAllPeriod) {
            $mitraQuery->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        }
        $verifiedMitrasInPeriod = $mitraQuery->count();

        // 4. Pending Top-Up Approvals
        $topupQuery = BalanceTransaction::where('type', 'topup')
            ->where('status', 'waiting_approval');
        if (!empty($activeCityIds)) {
            $topupQuery->whereHas('user', function ($q) use ($activeCityIds) {
                $q->whereIn('city_id', $activeCityIds);
            });
        } elseif ($user && $user->role === 'admin') {
            $topupQuery->whereRaw('1 = 0');
        }
        $pendingTopups = $topupQuery->count();

        // 5. Pending Withdraw Requests
        $withdrawQuery = \App\Models\WithdrawRequest::where('status', \App\Models\WithdrawRequest::STATUS_PENDING);
        if (!empty($activeCityIds)) {
            $withdrawQuery->whereHas('user', function ($q) use ($activeCityIds) {
                $q->whereIn('city_id', $activeCityIds);
            });
        } elseif ($user && $user->role === 'admin') {
            $withdrawQuery->whereRaw('1 = 0');
        }
        $pendingWithdraws = $withdrawQuery->count();

        // 6. Latest 6 Helps in Selected Period
        $latestHelps = (clone $helpQuery)
            ->with(['customer', 'user', 'city'])
            ->latest()
            ->take(6)
            ->get();

        // 7. UNIFIED MULTI-METRIC CHART DATA (Follows All Dashboard Data Combined)
        $chartLabels = [];
        $chartHelpsData = [];
        $chartCompletedData = [];
        $chartCancelledData = [];
        $chartVerificationsData = [];

        if (!$isAllPeriod) {
            $daysInMonth = $selectedMonthCarbon->daysInMonth;

            // Total Helps per Day
            $dailyTotalHelps = (clone $baseHelpQuery)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->selectRaw('DATE(created_at) as date, count(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->all();

            // Completed Helps per Day
            $dailyCompletedHelps = (clone $baseHelpQuery)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->whereIn('status', ['completed', 'selesai'])
                ->selectRaw('DATE(created_at) as date, count(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->all();

            // Cancelled Helps per Day
            $dailyCancelledHelps = (clone $baseHelpQuery)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->whereIn('status', ['cancelled', 'dibatalkan', 'rejected'])
                ->selectRaw('DATE(created_at) as date, count(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->all();

            // Registration / KTP per Day
            $dailyRegistrations = (clone $baseRegQuery)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->selectRaw('DATE(created_at) as date, count(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->all();

            for ($dayNum = 1; $dayNum <= $daysInMonth; $dayNum++) {
                $date = $selectedMonthCarbon->copy()->day($dayNum);
                $chartLabels[] = $date->format('j M');
                $dateKey = $date->toDateString();

                $chartHelpsData[] = (int) ($dailyTotalHelps[$dateKey] ?? 0);
                $chartCompletedData[] = (int) ($dailyCompletedHelps[$dateKey] ?? 0);
                $chartCancelledData[] = (int) ($dailyCancelledHelps[$dateKey] ?? 0);
                $chartVerificationsData[] = (int) ($dailyRegistrations[$dateKey] ?? 0);
            }
        } else {
            // All-time: Last 14 days activity across all metrics
            $startDate = Carbon::today()->subDays(13)->startOfDay();
            $endDate = Carbon::today()->endOfDay();

            $dailyTotalHelps = (clone $baseHelpQuery)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, count(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->all();

            $dailyCompletedHelps = (clone $baseHelpQuery)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'selesai'])
                ->selectRaw('DATE(created_at) as date, count(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->all();

            $dailyCancelledHelps = (clone $baseHelpQuery)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', ['cancelled', 'dibatalkan', 'rejected'])
                ->selectRaw('DATE(created_at) as date, count(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->all();

            $dailyRegistrations = (clone $baseRegQuery)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, count(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->all();

            for ($i = 13; $i >= 0; $i--) {
                $day = Carbon::today()->subDays($i);
                $chartLabels[] = $day->format('j M');
                $dateKey = $day->toDateString();

                $chartHelpsData[] = (int) ($dailyTotalHelps[$dateKey] ?? 0);
                $chartCompletedData[] = (int) ($dailyCompletedHelps[$dateKey] ?? 0);
                $chartCancelledData[] = (int) ($dailyCancelledHelps[$dateKey] ?? 0);
                $chartVerificationsData[] = (int) ($dailyRegistrations[$dateKey] ?? 0);
            }
        }

        return view('livewire.admin.dashboard.index', [
            'managedCities'          => $managedCities,
            'selectedCity'           => $this->selectedCity,
            'activeCityLabel'        => $activeCityLabel,
            'selectedMonth'          => $this->selectedMonth,
            'availableMonths'        => $availableMonths,
            'periodLabel'            => $periodLabel,
            'isAllPeriod'            => $isAllPeriod,
            'totalHelps'             => $totalHelps,
            'pendingHelps'           => $pendingHelps,
            'activeHelps'            => $activeHelps,
            'completedHelps'         => $completedHelps,
            'cancelledHelps'         => $cancelledHelps,
            'pendingVerifications'   => $pendingVerifications,
            'verifiedMitrasInPeriod' => $verifiedMitrasInPeriod,
            'totalAllMitras'         => $totalAllMitras,
            'pendingTopups'          => $pendingTopups,
            'pendingWithdraws'       => $pendingWithdraws,
            'latestHelps'            => $latestHelps,
            // Unified Chart Data
            'chartLabels'            => $chartLabels,
            'chartHelpsData'         => $chartHelpsData,
            'chartCompletedData'     => $chartCompletedData,
            'chartCancelledData'     => $chartCancelledData,
            'chartVerificationsData' => $chartVerificationsData,
        ]);
    }
}
