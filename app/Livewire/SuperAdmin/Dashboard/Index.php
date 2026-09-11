<?php

namespace App\Livewire\SuperAdmin\Dashboard;

use App\Models\User;
use App\Models\City;
use App\Models\Help;
use App\Models\Registration;
use App\Models\WithdrawRequest;
use App\Models\BalanceTransaction;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Layout('layouts.superadmin')]
class Index extends Component
{
    public $selectedDate;
    public $selectedMonth;
    public $selectedYear;
    public $userChart = [];

    protected $listeners = [
        'superadmin-territory-changed' => 'onTerritoryChanged',
        'admin-district-changed'       => 'onTerritoryChanged',
        'admin-city-changed'           => 'onTerritoryChanged',
        'chart-refresh'                => 'updateChartData',
    ];

    public function onTerritoryChanged()
    {
        $this->updateChartData();
    }

    public function mount()
    {
        // Set default to current date
        $this->selectedDate = Carbon::today()->toDateString();
        $this->selectedMonth = Carbon::today()->format('Y-m');
        $this->selectedYear = Carbon::today()->year;
        $this->updateChartData();
    }

    public function updatedSelectedDate()
    {
        // Update selectedMonth when date changes
        if ($this->selectedDate) {
            $this->selectedMonth = Carbon::parse($this->selectedDate)->format('Y-m');
            $this->selectedYear = Carbon::parse($this->selectedDate)->year;
            $this->updateChartData();
        }
    }

    public function updatedSelectedMonth()
    {
        // Update selectedDate to first day of the month when month changes
        if ($this->selectedMonth) {
            $this->selectedDate = Carbon::parse($this->selectedMonth . '-01')->toDateString();
            $this->selectedYear = Carbon::parse($this->selectedMonth)->year;
            $this->updateChartData();
        }
    }

    protected function applyUserTerritoryScope($query, array $territory, array $districtIds)
    {
        if ($territory['type'] === 'district' && $territory['id']) {
            $query->where('district_id', (int) $territory['id']);
        } elseif ($territory['type'] === 'city' && $territory['id']) {
            $cityId = (int) $territory['id'];
            $query->where(function ($q) use ($cityId, $districtIds) {
                $q->where('city_id', $cityId);
                if (!empty($districtIds)) {
                    $q->orWhereIn('district_id', $districtIds);
                }
            });
        }
        return $query;
    }

    public function updateChartData()
    {
        $user = auth()->user();
        $territory = $user ? $user->getActiveSuperadminTerritory() : ['type' => 'all', 'id' => null];
        $districtIds = $user ? $user->getEffectiveSuperadminDistrictIds() : [];

        // 1. Daily - single grouped query for days in the selected month
        $selectedMonthCarbon = Carbon::parse($this->selectedMonth . '-01');
        $startOfMonth = $selectedMonthCarbon->copy()->startOfMonth();
        $endOfMonth = $selectedMonthCarbon->copy()->endOfMonth();
        $daysInMonth = $selectedMonthCarbon->daysInMonth;

        $dailyQuery = User::whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        $this->applyUserTerritoryScope($dailyQuery, $territory, $districtIds);

        $dailyCounts = $dailyQuery
            ->selectRaw('DATE(created_at) as date, count(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->all();

        $dailyLabels = [];
        $dailyData = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = $selectedMonthCarbon->copy()->day($i);
            $dailyLabels[] = $date->format('d M');
            $dateKey = $date->toDateString();
            $dailyData[] = (int) ($dailyCounts[$dateKey] ?? 0);
        }

        // 2. Monthly - single grouped query for 12 months of selected year
        $selectedYearCarbon = Carbon::createFromDate($this->selectedYear, 1, 1);
        $startOfYear = $selectedYearCarbon->copy()->startOfYear();
        $endOfYear = $selectedYearCarbon->copy()->endOfYear();

        $monthlyQuery = User::whereBetween('created_at', [$startOfYear, $endOfYear]);
        $this->applyUserTerritoryScope($monthlyQuery, $territory, $districtIds);

        $monthlyCounts = $monthlyQuery
            ->selectRaw('MONTH(created_at) as month, count(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->all();

        $monthlyLabels = [];
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $m = $selectedYearCarbon->copy()->month($i);
            $monthlyLabels[] = $m->format('M Y');
            $monthlyData[] = (int) ($monthlyCounts[$i] ?? 0);
        }

        // 3. Yearly - single grouped query for last 5 years from selected year
        $years = 5;
        $startYear = Carbon::createFromDate($this->selectedYear, 1, 1)->subYears($years - 1);
        $startOf5Years = $startYear->copy()->startOfYear();
        $endOf5Years = Carbon::createFromDate($this->selectedYear, 12, 31)->endOfDay();

        $yearlyQuery = User::whereBetween('created_at', [$startOf5Years, $endOf5Years]);
        $this->applyUserTerritoryScope($yearlyQuery, $territory, $districtIds);

        $yearlyCounts = $yearlyQuery
            ->selectRaw('YEAR(created_at) as year, count(*) as total')
            ->groupBy('year')
            ->pluck('total', 'year')
            ->all();

        $yearlyLabels = [];
        $yearlyData = [];
        for ($i = 0; $i < $years; $i++) {
            $y = $startYear->copy()->addYears($i);
            $yearKey = (int) $y->year;
            $yearlyLabels[] = (string) $yearKey;
            $yearlyData[] = (int) ($yearlyCounts[$yearKey] ?? 0);
        }

        $this->userChart = [
            'daily'   => ['labels' => $dailyLabels, 'data' => $dailyData],
            'monthly' => ['labels' => $monthlyLabels, 'data' => $monthlyData],
            'yearly'  => ['labels' => $yearlyLabels, 'data' => $yearlyData],
        ];
    }

    public function render()
    {
        $user = auth()->user();
        $territory = $user ? $user->getActiveSuperadminTerritory() : ['type' => 'all', 'id' => null, 'label' => 'Semua Wilayah (Nasional)'];
        $districtIds = $user ? $user->getEffectiveSuperadminDistrictIds() : [];
        $cityId = ($territory['type'] === 'city') ? (int) $territory['id'] : null;
        $districtId = ($territory['type'] === 'district') ? (int) $territory['id'] : null;

        $userQuery = User::query();
        $helpQuery = Help::query();
        $regQuery = Registration::query();
        $withdrawQuery = WithdrawRequest::query();
        $topupQuery = BalanceTransaction::where('type', 'topup');

        if ($districtId) {
            $userQuery->where('district_id', $districtId);
            $helpQuery->where(function ($q) use ($districtId) {
                $q->where('district_id', $districtId)
                  ->orWhereHas('customer', fn($cq) => $cq->where('district_id', $districtId));
            });
            $regQuery->where('district_id', $districtId);
            $withdrawQuery->whereHas('user', fn($uq) => $uq->where('district_id', $districtId));
            $topupQuery->whereHas('user', fn($uq) => $uq->where('district_id', $districtId));
        } elseif ($cityId) {
            $userQuery->where(function ($q) use ($cityId, $districtIds) {
                $q->where('city_id', $cityId);
                if (!empty($districtIds)) {
                    $q->orWhereIn('district_id', $districtIds);
                }
            });
            $helpQuery->where(function ($q) use ($cityId, $districtIds) {
                $q->where('city_id', $cityId)
                  ->orWhereHas('customer', fn($cq) => $cq->where('city_id', $cityId));
                if (!empty($districtIds)) {
                    $q->orWhereIn('district_id', $districtIds)
                      ->orWhereHas('customer', fn($cq) => $cq->whereIn('district_id', $districtIds));
                }
            });
            $regQuery->where(function ($q) use ($cityId, $districtIds) {
                $q->where('city_id', $cityId);
                if (!empty($districtIds)) {
                    $q->orWhereIn('district_id', $districtIds);
                }
            });
            $withdrawQuery->whereHas('user', function ($uq) use ($cityId, $districtIds) {
                $uq->where('city_id', $cityId);
                if (!empty($districtIds)) {
                    $uq->orWhereIn('district_id', $districtIds);
                }
            });
            $topupQuery->whereHas('user', function ($uq) use ($cityId, $districtIds) {
                $uq->where('city_id', $cityId);
                if (!empty($districtIds)) {
                    $uq->orWhereIn('district_id', $districtIds);
                }
            });
        }

        $stats = [
            'total_users'           => (clone $userQuery)->count(),
            'total_customers'       => (clone $userQuery)->where('role', 'customer')->count(),
            'total_mitras'          => (clone $userQuery)->where('role', 'mitra')->count(),
            'total_admins'          => (clone $userQuery)->whereIn('role', ['admin', 'super_admin'])->count(),
            'total_cities'          => City::count(),
            'pending_helps'         => (clone $helpQuery)->where('status', Help::STATUS_MENUNGGU_MITRA)->count(),
            'active_helps'          => (clone $helpQuery)->whereIn('status', array_merge(Help::activeStatuses(), [Help::STATUS_WAITING_CONFIRMATION]))->count(),
            'completed_helps'       => (clone $helpQuery)->where('status', Help::STATUS_SELESAI)->count(),
            'pending_withdraws'     => (clone $withdrawQuery)->where('status', WithdrawRequest::STATUS_PENDING)->count(),
            'pending_topups'        => (clone $topupQuery)->where('status', 'waiting_approval')->count(),
            'pending_verifications' => (clone $regQuery)->whereIn('status', ['pending', 'pending_verification'])->count(),
        ];

        // Recent items for quick view
        $recentUsers = (clone $userQuery)->orderByDesc('created_at')->limit(6)->get(['id', 'name', 'email', 'role', 'created_at']);
        
        $recentTransactions = BalanceTransaction::with('user')
            ->when($districtId, fn($q) => $q->whereHas('user', fn($uq) => $uq->where('district_id', $districtId)))
            ->when($cityId, function ($q) use ($cityId, $districtIds) {
                $q->whereHas('user', function ($uq) use ($cityId, $districtIds) {
                    $uq->where('city_id', $cityId);
                    if (!empty($districtIds)) $uq->orWhereIn('district_id', $districtIds);
                });
            })
            ->orderByDesc('created_at')->limit(6)->get();

        $recentHelps = (clone $helpQuery)->with('customer')->orderByDesc('created_at')->limit(8)->get(['id', 'title', 'status', 'created_at', 'user_id', 'amount']);

        return view('livewire.superadmin.dashboard.index', compact('stats', 'recentUsers', 'recentTransactions', 'recentHelps', 'territory'));
    }
}
