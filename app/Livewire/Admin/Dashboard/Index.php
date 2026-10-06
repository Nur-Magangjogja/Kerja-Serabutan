<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\Help;
use App\Models\Registration;
use App\Models\User;
use App\Models\District;
use App\Models\City;
use App\Models\BalanceTransaction;
use App\Services\Territory\AdminTerritoryAuthorizationService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.admin')]
#[Title('Dashboard Admin')]
class Index extends Component
{
    public $selectedMonth = '';
    public $selectedDistrict = 'all';
    public $selectedCity = 'all'; // Backward compatibility alias

    protected $listeners = [
        'admin-district-changed' => 'onAdminDistrictChanged',
        'admin-city-changed'     => 'onAdminDistrictChanged',
    ];

    public function mount()
    {
        // Default to current month (e.g. 2026-09)
        $this->selectedMonth = Carbon::today()->format('Y-m');
        $this->selectedDistrict = auth()->user()?->getActiveAdminDistrictFilter() ?? 'all';
        $this->selectedCity = $this->selectedDistrict;
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

    public function setDistrictFilter(string $district)
    {
        $this->selectedDistrict = $district;
        $this->selectedCity = $district;
        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $admin->setActiveAdminDistrictFilter($district);
            $admin->setActiveAdminCityFilter($district);
        }
        $this->dispatch('chart-refresh');
        $this->dispatch('admin-district-changed', districtId: $district);
    }

    // Alias for backward compatibility
    public function setCityFilter(string $city)
    {
        $this->setDistrictFilter($city);
    }

    public function onAdminDistrictChanged($districtId = null)
    {
        $admin = auth()->user();
        $target = (string) ($districtId ?? ($admin ? ($admin->getActiveAdminDistrictFilter() !== 'all' ? $admin->getActiveAdminDistrictFilter() : $admin->getActiveAdminCityFilter()) : 'all'));
        if ($admin && $admin->role === 'admin') {
            $admin->setActiveAdminDistrictFilter($target);
            $admin->setActiveAdminCityFilter($target);
        }
        $this->selectedDistrict = $target;
        $this->selectedCity = $target;
        $this->dispatch('chart-refresh');
    }

    public function onAdminCityChanged($cityId = null)
    {
        $this->onAdminDistrictChanged($cityId);
    }

    public function setYear(string $year)
    {
        $cleanYr = str_replace('year-', '', $year);
        $this->selectedMonth = 'year-' . $cleanYr;
        $this->dispatch('chart-refresh');
    }

    public function prevMonth()
    {
        if ($this->selectedMonth === 'all') {
            $this->selectedMonth = Carbon::today()->subMonth()->format('Y-m');
        } elseif (str_starts_with($this->selectedMonth, 'year-') || (strlen($this->selectedMonth) === 4 && is_numeric($this->selectedMonth))) {
            $yr = (int) str_replace('year-', '', $this->selectedMonth);
            $this->selectedMonth = 'year-' . ($yr - 1);
        } else {
            $this->selectedMonth = Carbon::parse($this->selectedMonth . '-01')->subMonth()->format('Y-m');
        }
        $this->dispatch('chart-refresh');
    }

    public function nextMonth()
    {
        if ($this->selectedMonth === 'all') {
            $this->selectedMonth = Carbon::today()->format('Y-m');
        } elseif (str_starts_with($this->selectedMonth, 'year-') || (strlen($this->selectedMonth) === 4 && is_numeric($this->selectedMonth))) {
            $yr = (int) str_replace('year-', '', $this->selectedMonth);
            $this->selectedMonth = 'year-' . ($yr + 1);
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
        
        // District and City Resolution for Admin
        $authService = app(AdminTerritoryAuthorizationService::class);
        $allowedDistrictIds = ($user && $user->role === 'admin') ? $user->getAdminDistrictIds() : [];
        $managedDistricts = ($user && $user->role === 'admin') ? $user->getAdminDistricts() : collect();
        $explicitCityIds = ($user && $user->role === 'admin') ? $authService->getExplicitAdminCityIds($user) : [];
        $allowedCityIds = ($user && $user->role === 'admin') ? $user->getAdminCityIds() : [];
        $managedCities = ($user && $user->role === 'admin') ? $user->getAdminCities() : collect();

        // Determine active district / city scope
        $activeDistrictIds = [];
        $activeCityIds = [];
        $activeExplicitCityIds = [];

        if ($user && $user->role === 'admin') {
            $filterValue = $this->selectedDistrict !== 'all' ? $this->selectedDistrict : ($this->selectedCity !== 'all' ? $this->selectedCity : 'all');

            if ($filterValue !== 'all') {
                $valInt = (int) $filterValue;
                if (in_array($valInt, $allowedDistrictIds, true)) {
                    $activeDistrictIds = [$valInt];
                    $activeCityIds = [];
                    $activeExplicitCityIds = [];
                    $districtObj = $managedDistricts->firstWhere('id', $valInt);
                    $activeDistrictLabel = $districtObj ? 'Kec. ' . $districtObj->name : 'Wilayah Terpilih';
                } elseif (in_array($valInt, $allowedCityIds, true)) {
                    $activeCityIds = [$valInt];
                    $districtsInCity = District::where('city_id', $valInt)->pluck('id')->map('intval')->all();
                    $activeDistrictIds = array_values(array_intersect($districtsInCity, $allowedDistrictIds));
                    $activeExplicitCityIds = in_array($valInt, $explicitCityIds, true) ? [$valInt] : [];
                    $cityObj = $managedCities->firstWhere('id', $valInt) ?? City::find($valInt);
                    $activeDistrictLabel = $cityObj ? 'Kota/Kab. ' . $cityObj->name : 'Wilayah Terpilih';
                } else {
                    $activeDistrictIds = $allowedDistrictIds;
                    $activeCityIds = $allowedCityIds;
                    $activeExplicitCityIds = $explicitCityIds;
                    $activeDistrictLabel = $user->active_admin_district_label ?: $user->active_admin_city_label;
                }
            } else {
                $activeDistrictIds = $allowedDistrictIds;
                $activeCityIds = $allowedCityIds;
                $activeExplicitCityIds = $explicitCityIds;
                $activeDistrictLabel = $user->admin_city_names ?: ($user->admin_district_names ?: 'Semua Wilayah');
            }
        } else {
            $activeDistrictIds = $allowedDistrictIds;
            $activeCityIds = $allowedCityIds;
            $activeExplicitCityIds = $explicitCityIds;
            $activeDistrictLabel = 'Semua Wilayah';
        }

        // 1. Base Help & Registration Queries (Canonical Territory Scoped)
        $baseHelpQuery = Help::query();
        if ($user && $user->role === 'admin') {
            if (!empty($activeDistrictIds) && !empty($activeExplicitCityIds)) {
                $baseHelpQuery->where(function ($q) use ($activeDistrictIds, $activeExplicitCityIds) {
                    $q->whereIn('district_id', $activeDistrictIds)
                      ->orWhere(function ($sq) use ($activeExplicitCityIds) {
                          $sq->whereNull('district_id')
                             ->whereIn('city_id', $activeExplicitCityIds);
                      });
                });
            } elseif (!empty($activeDistrictIds)) {
                $baseHelpQuery->whereIn('district_id', $activeDistrictIds);
            } elseif (!empty($activeExplicitCityIds)) {
                $baseHelpQuery->whereNull('district_id')
                  ->whereIn('city_id', $activeExplicitCityIds);
            } else {
                $baseHelpQuery->whereRaw('1 = 0');
            }
        }

        $baseRegQuery = Registration::query();
        if ($user && $user->role === 'admin') {
            if (!empty($activeDistrictIds) && !empty($activeCityIds)) {
                $baseRegQuery->where(function ($q) use ($activeDistrictIds, $activeCityIds) {
                    $q->whereIn('district_id', $activeDistrictIds)
                      ->orWhere(function ($sq) use ($activeCityIds) {
                          $sq->whereNull('district_id')
                             ->whereIn('city_id', $activeCityIds);
                      });
                });
            } elseif (!empty($activeDistrictIds)) {
                $baseRegQuery->whereIn('district_id', $activeDistrictIds);
            } elseif (!empty($activeCityIds)) {
                $baseRegQuery->whereIn('city_id', $activeCityIds);
            } else {
                $baseRegQuery->whereRaw('1 = 0');
            }
        }

        // 2. Discover Active Operational Months & Years
        $currentDate = Carbon::today();
        $currentYear = (int) $currentDate->year;
        $currentMonth = (int) $currentDate->month;
        $currentMonthKey = $currentDate->format('Y-m');

        if (empty($this->selectedMonth)) {
            $this->selectedMonth = $currentMonthKey;
        }

        $isAllPeriod = ($this->selectedMonth === 'all');
        $isYearPeriod = str_starts_with($this->selectedMonth, 'year-') || (strlen($this->selectedMonth) === 4 && is_numeric($this->selectedMonth));

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $formatExpr = $isSqlite ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')";

        try {
            $helpMonths = (clone $baseHelpQuery)
                ->whereNotNull('created_at')
                ->selectRaw("DISTINCT {$formatExpr} as ym")
                ->pluck('ym')
                ->filter()
                ->map(fn($v) => trim((string) $v))
                ->all();
        } catch (\Throwable $e) {
            $helpMonths = [];
        }

        try {
            $regMonths = (clone $baseRegQuery)
                ->whereNotNull('created_at')
                ->selectRaw("DISTINCT {$formatExpr} as ym")
                ->pluck('ym')
                ->filter()
                ->map(fn($v) => trim((string) $v))
                ->all();
        } catch (\Throwable $e) {
            $regMonths = [];
        }

        // Only include operational months + active current month (omit months before operations started or with no history)
        $operationalMonths = array_values(array_unique(array_filter(array_merge([$currentMonthKey], $helpMonths, $regMonths))));

        if ($this->selectedMonth !== 'all' && !empty($this->selectedMonth) && preg_match('/^\d{4}-\d{2}$/', $this->selectedMonth)) {
            if (!in_array($this->selectedMonth, $operationalMonths, true)) {
                $operationalMonths[] = $this->selectedMonth;
            }
        }

        rsort($operationalMonths);

        $availableYears = [];
        foreach ($operationalMonths as $ym) {
            $y = (int) substr($ym, 0, 4);
            if ($y > 2000 && !in_array($y, $availableYears, true)) {
                $availableYears[] = $y;
            }
        }

        if (!in_array($currentYear, $availableYears, true)) {
            $availableYears[] = $currentYear;
        }

        if ($isYearPeriod) {
            $selectedYearInt = (int) str_replace('year-', '', $this->selectedMonth);
            if ($selectedYearInt > 2000 && !in_array($selectedYearInt, $availableYears, true)) {
                $availableYears[] = $selectedYearInt;
            }
        }

        // Multi-year testing list: ensure multiple registered years (e.g. 2026 down to 2020)
        // are available so admin can inspect the year pills and horizontal scrolling display when many years exist.
        $multiYears = range($currentYear, max($currentYear - 6, 2020));
        foreach ($multiYears as $my) {
            if (!in_array($my, $availableYears, true)) {
                $availableYears[] = $my;
            }
        }

        rsort($availableYears);

        $availableMonths = [];
        $monthsByYear = [];

        foreach ($availableYears as $year) {
            $monthsByYear[$year] = [];
        }

        foreach ($operationalMonths as $key) {
            $parts = explode('-', $key);
            if (count($parts) !== 2) continue;
            $yr = (int) $parts[0];
            $m = (int) $parts[1];

            $monthCarbon = Carbon::createFromDate($yr, $m, 1);
            $isCurrent = ($key === $currentMonthKey);
            $label = $isCurrent
                ? 'Bulan Ini (' . $monthCarbon->translatedFormat('F Y') . ')'
                : $monthCarbon->translatedFormat('F Y');

            $monthData = [
                'key'         => $key,
                'label'       => $label,
                'short_label' => $monthCarbon->translatedFormat('F Y'),
                'year'        => (string) $yr,
                'month'       => $m,
                'is_current'  => $isCurrent,
            ];

            $availableMonths[$key] = $monthData;
            if (isset($monthsByYear[$yr])) {
                $monthsByYear[$yr][] = $monthData;
            }
        }

        $initialYear = (string) $currentYear;
        if ($this->selectedMonth !== 'all' && !empty($this->selectedMonth)) {
            $cleanYrStr = str_replace('year-', '', $this->selectedMonth);
            $initialYear = substr($cleanYrStr, 0, 4);
        }

        // Determine date range & period label
        if ($isAllPeriod) {
            $periodLabel = 'Semua Periode (Akumulasi)';
            $selectedMonthCarbon = Carbon::today();
            $startRange = null;
            $endRange = null;
        } elseif ($isYearPeriod) {
            $selectedYearInt = (int) str_replace('year-', '', $this->selectedMonth);
            $periodLabel = "Tahun {$selectedYearInt} (Semua Bulan)";
            $selectedMonthCarbon = Carbon::createFromDate($selectedYearInt, 1, 1);
            $startRange = Carbon::createFromDate($selectedYearInt, 1, 1)->startOfYear();
            $endRange = Carbon::createFromDate($selectedYearInt, 12, 31)->endOfYear();
        } else {
            $selectedMonthCarbon = Carbon::parse($this->selectedMonth . '-01');
            $periodLabel = $selectedMonthCarbon->translatedFormat('F Y');
            $startRange = $selectedMonthCarbon->copy()->startOfMonth();
            $endRange = $selectedMonthCarbon->copy()->endOfMonth();
        }

        // 3. Query Statistics
        $helpQuery = clone $baseHelpQuery;
        if ($startRange && $endRange) {
            $helpQuery->whereBetween('created_at', [$startRange, $endRange]);
        }

        // 1 query agregasi menggantikan 5 query COUNT terpisah
        $activeStatusList = array_merge(Help::activeStatuses(), [Help::STATUS_WAITING_CONFIRMATION]);
        $activePlaceholders = implode(',', array_fill(0, count($activeStatusList), '?'));

        $statsAgg = (clone $helpQuery)
            ->selectRaw("COUNT(*) as total_helps")
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_helps", [Help::STATUS_MENUNGGU_MITRA])
            ->selectRaw("SUM(CASE WHEN status IN ($activePlaceholders) THEN 1 ELSE 0 END) as active_helps", $activeStatusList)
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_helps", [Help::STATUS_SELESAI])
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled_helps", [Help::STATUS_DIBATALKAN])
            ->first();

        $totalHelps = (int) ($statsAgg->total_helps ?? 0);
        $pendingHelps = (int) ($statsAgg->pending_helps ?? 0);
        $activeHelps = (int) ($statsAgg->active_helps ?? 0);
        $completedHelps = (int) ($statsAgg->completed_helps ?? 0);
        $cancelledHelps = (int) ($statsAgg->cancelled_helps ?? 0);

        // KTP / Registration Verifications
        $regQuery = clone $baseRegQuery;
        if ($startRange && $endRange) {
            $regQuery->whereBetween('created_at', [$startRange, $endRange]);
        }
        $pendingVerifications = (clone $regQuery)->whereIn('status', ['pending', 'pending_verification'])->count();

        // Total Verified Mitras
        $mitraQuery = User::where('role', 'mitra');
        if ($user && $user->role === 'admin') {
            if (!empty($activeDistrictIds) && !empty($activeCityIds)) {
                $mitraQuery->where(function ($q) use ($activeDistrictIds, $activeCityIds) {
                    $q->whereIn('district_id', $activeDistrictIds)
                      ->orWhere(function ($sq) use ($activeCityIds) {
                          $sq->whereNull('district_id')
                             ->whereIn('city_id', $activeCityIds);
                      });
                });
            } elseif (!empty($activeDistrictIds)) {
                $mitraQuery->whereIn('district_id', $activeDistrictIds);
            } elseif (!empty($activeCityIds)) {
                $mitraQuery->whereIn('city_id', $activeCityIds);
            } else {
                $mitraQuery->whereRaw('1 = 0');
            }
        }
        $totalAllMitras = (clone $mitraQuery)->count();
        if ($startRange && $endRange) {
            $mitraQuery->whereBetween('created_at', [$startRange, $endRange]);
        }
        $verifiedMitrasInPeriod = $mitraQuery->count();

        // Pending Top-Up Approvals
        $topupQuery = BalanceTransaction::where('type', 'topup')
            ->where('status', 'waiting_approval');
        if ($user && $user->role === 'admin') {
            if (!empty($activeDistrictIds) && !empty($activeCityIds)) {
                $topupQuery->whereHas('user', function ($q) use ($activeDistrictIds, $activeCityIds) {
                    $q->whereIn('district_id', $activeDistrictIds)
                      ->orWhere(function ($sq) use ($activeCityIds) {
                          $sq->whereNull('district_id')
                             ->whereIn('city_id', $activeCityIds);
                      });
                });
            } elseif (!empty($activeDistrictIds)) {
                $topupQuery->whereHas('user', fn($q) => $q->whereIn('district_id', $activeDistrictIds));
            } elseif (!empty($activeCityIds)) {
                $topupQuery->whereHas('user', fn($q) => $q->whereIn('city_id', $activeCityIds));
            } else {
                $topupQuery->whereRaw('1 = 0');
            }
        }
        $pendingTopups = $topupQuery->count();

        // Pending Withdraw Requests
        $withdrawQuery = \App\Models\WithdrawRequest::where('status', \App\Models\WithdrawRequest::STATUS_PENDING);
        if ($user && $user->role === 'admin') {
            if (!empty($activeDistrictIds) && !empty($activeCityIds)) {
                $withdrawQuery->whereHas('user', function ($q) use ($activeDistrictIds, $activeCityIds) {
                    $q->whereIn('district_id', $activeDistrictIds)
                      ->orWhere(function ($sq) use ($activeCityIds) {
                          $sq->whereNull('district_id')
                             ->whereIn('city_id', $activeCityIds);
                      });
                });
            } elseif (!empty($activeDistrictIds)) {
                $withdrawQuery->whereHas('user', fn($q) => $q->whereIn('district_id', $activeDistrictIds));
            } elseif (!empty($activeCityIds)) {
                $withdrawQuery->whereHas('user', fn($q) => $q->whereIn('city_id', $activeCityIds));
            } else {
                $withdrawQuery->whereRaw('1 = 0');
            }
        }
        $pendingWithdraws = $withdrawQuery->count();

        // Latest 6 Helps in Selected Period
        $latestHelps = (clone $helpQuery)
            ->with(['user', 'district', 'city'])
            ->latest()
            ->take(6)
            ->get();

        // 4. UNIFIED MULTI-METRIC CHART DATA
        $chartLabels = [];
        $chartHelpsData = [];
        $chartCompletedData = [];
        $chartCancelledData = [];
        $chartVerificationsData = [];

        if ($isYearPeriod) {
            // Full Year: Group by Month across the year
            $selectedYearInt = (int) str_replace('year-', '', $this->selectedMonth);
            $monthExpr = $isSqlite ? "CAST(strftime('%m', created_at) AS INTEGER)" : "MONTH(created_at)";

            $monthlyHelpMetrics = (clone $baseHelpQuery)
                ->whereBetween('created_at', [$startRange, $endRange])
                ->selectRaw("{$monthExpr} as m_num")
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN status IN (?, 'completed') THEN 1 ELSE 0 END) as completed", [Help::STATUS_SELESAI])
                ->selectRaw("SUM(CASE WHEN status IN (?, 'cancelled', 'rejected') THEN 1 ELSE 0 END) as cancelled", [Help::STATUS_DIBATALKAN])
                ->groupBy('m_num')
                ->get()
                ->keyBy('m_num');

            $monthlyRegistrations = (clone $baseRegQuery)
                ->whereBetween('created_at', [$startRange, $endRange])
                ->selectRaw("{$monthExpr} as m_num, count(*) as total")
                ->groupBy('m_num')
                ->pluck('total', 'm_num')
                ->all();

            $maxMonth = ($selectedYearInt === $currentYear) ? $currentMonth : 12;
            for ($mNum = 1; $mNum <= $maxMonth; $mNum++) {
                $mCarbon = Carbon::createFromDate($selectedYearInt, $mNum, 1);
                $chartLabels[] = $mCarbon->translatedFormat('M');
                $row = $monthlyHelpMetrics[$mNum] ?? null;
                $chartHelpsData[] = (int) ($row->total ?? 0);
                $chartCompletedData[] = (int) ($row->completed ?? 0);
                $chartCancelledData[] = (int) ($row->cancelled ?? 0);
                $chartVerificationsData[] = (int) ($monthlyRegistrations[$mNum] ?? 0);
            }
        } elseif (!$isAllPeriod) {
            // Month breakdown (day-by-day)
            $daysInMonth = $selectedMonthCarbon->daysInMonth;

            $dailyHelpMetrics = (clone $baseHelpQuery)
                ->whereBetween('created_at', [$startRange, $endRange])
                ->selectRaw('DATE(created_at) as date')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN status IN (?, 'completed') THEN 1 ELSE 0 END) as completed", [Help::STATUS_SELESAI])
                ->selectRaw("SUM(CASE WHEN status IN (?, 'cancelled', 'rejected') THEN 1 ELSE 0 END) as cancelled", [Help::STATUS_DIBATALKAN])
                ->groupBy('date')
                ->get()
                ->keyBy('date');

            $dailyRegistrations = (clone $baseRegQuery)
                ->whereBetween('created_at', [$startRange, $endRange])
                ->selectRaw('DATE(created_at) as date, count(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->all();

            for ($dayNum = 1; $dayNum <= $daysInMonth; $dayNum++) {
                $date = $selectedMonthCarbon->copy()->day($dayNum);
                $chartLabels[] = $date->format('j M');
                $dateKey = $date->toDateString();

                $row = $dailyHelpMetrics[$dateKey] ?? null;
                $chartHelpsData[] = (int) ($row->total ?? 0);
                $chartCompletedData[] = (int) ($row->completed ?? 0);
                $chartCancelledData[] = (int) ($row->cancelled ?? 0);
                $chartVerificationsData[] = (int) ($dailyRegistrations[$dateKey] ?? 0);
            }
        } else {
            // All-time: Last 14 days activity across all metrics
            $startDate = Carbon::today()->subDays(13)->startOfDay();
            $endDate = Carbon::today()->endOfDay();

            $dailyHelpMetrics = (clone $baseHelpQuery)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN status IN (?, 'completed') THEN 1 ELSE 0 END) as completed", [Help::STATUS_SELESAI])
                ->selectRaw("SUM(CASE WHEN status IN (?, 'cancelled', 'rejected') THEN 1 ELSE 0 END) as cancelled", [Help::STATUS_DIBATALKAN])
                ->groupBy('date')
                ->get()
                ->keyBy('date');

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

                $row = $dailyHelpMetrics[$dateKey] ?? null;
                $chartHelpsData[] = (int) ($row->total ?? 0);
                $chartCompletedData[] = (int) ($row->completed ?? 0);
                $chartCancelledData[] = (int) ($row->cancelled ?? 0);
                $chartVerificationsData[] = (int) ($dailyRegistrations[$dateKey] ?? 0);
            }
        }

        return view('livewire.admin.dashboard.index', [
            'managedDistricts'       => $managedDistricts,
            'managedCities'          => $managedDistricts, // For backward view compatibility
            'selectedDistrict'       => $this->selectedDistrict,
            'selectedCity'           => $this->selectedDistrict,
            'activeDistrictLabel'    => $activeDistrictLabel,
            'activeCityLabel'        => $activeDistrictLabel,
            'selectedMonth'          => $this->selectedMonth,
            'availableMonths'        => $availableMonths,
            'availableYears'         => $availableYears,
            'monthsByYear'           => $monthsByYear,
            'initialYear'            => $initialYear,
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
