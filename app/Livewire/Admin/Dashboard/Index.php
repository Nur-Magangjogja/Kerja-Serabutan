<?php

namespace App\Livewire\Admin\Dashboard;

use App\Models\Help;
use App\Models\Registration;
use App\Models\User;
use App\Models\District;
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
        
        // District and City Resolution for Admin
        $allowedDistrictIds = ($user && $user->role === 'admin') ? $user->getAdminDistrictIds() : [];
        $managedDistricts = ($user && $user->role === 'admin') ? $user->getAdminDistricts() : collect();
        $allowedCityIds = ($user && $user->role === 'admin') ? $user->getAdminCityIds() : [];
        $managedCities = ($user && $user->role === 'admin') ? $user->getAdminCities() : collect();

        // Determine active district / city scope
        $activeDistrictIds = [];
        $activeCityIds = [];

        if ($user && $user->role === 'admin') {
            $filterValue = $this->selectedDistrict !== 'all' ? $this->selectedDistrict : ($this->selectedCity !== 'all' ? $this->selectedCity : 'all');

            if ($filterValue !== 'all') {
                $valInt = (int) $filterValue;
                if (in_array($valInt, $allowedDistrictIds, true)) {
                    $activeDistrictIds = [$valInt];
                    $districtObj = $managedDistricts->firstWhere('id', $valInt);
                    $activeDistrictLabel = $districtObj ? 'Kec. ' . $districtObj->name : 'Wilayah Terpilih';
                } elseif (in_array($valInt, $allowedCityIds, true)) {
                    $activeCityIds = [$valInt];
                    $activeDistrictIds = District::where('city_id', $valInt)->pluck('id')->map('intval')->all();
                    $cityObj = $managedCities->firstWhere('id', $valInt) ?? City::find($valInt);
                    $activeDistrictLabel = $cityObj ? 'Kota/Kab. ' . $cityObj->name : 'Wilayah Terpilih';
                } else {
                    $activeDistrictIds = $allowedDistrictIds;
                    $activeCityIds = $allowedCityIds;
                    $activeDistrictLabel = $user->active_admin_district_label ?: $user->active_admin_city_label;
                }
            } else {
                $activeDistrictIds = $allowedDistrictIds;
                $activeCityIds = $allowedCityIds;
                $activeDistrictLabel = $user->admin_city_names ?: ($user->admin_district_names ?: 'Semua Wilayah');
            }
        } else {
            $activeDistrictIds = $allowedDistrictIds;
            $activeCityIds = $allowedCityIds;
            $activeDistrictLabel = 'Semua Wilayah';
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

        // 2. Base Help Query (District & City Scoped)
        $baseHelpQuery = Help::query();
        if ($user && $user->role === 'admin') {
            if (!empty($activeDistrictIds) && !empty($activeCityIds)) {
                $baseHelpQuery->where(function ($q) use ($activeDistrictIds, $activeCityIds) {
                    $q->whereIn('district_id', $activeDistrictIds)
                      ->orWhereHas('customer', fn($cq) => $cq->whereIn('district_id', $activeDistrictIds))
                      ->orWhere(function ($sq) use ($activeCityIds) {
                          $sq->whereNull('district_id')
                             ->whereIn('city_id', $activeCityIds);
                      })
                      ->orWhereHas('customer', function ($cq) use ($activeCityIds) {
                          $cq->whereNull('district_id')
                             ->whereIn('city_id', $activeCityIds);
                      });
                });
            } elseif (!empty($activeDistrictIds)) {
                $baseHelpQuery->where(function ($q) use ($activeDistrictIds) {
                    $q->whereIn('district_id', $activeDistrictIds)
                      ->orWhereHas('customer', fn($cq) => $cq->whereIn('district_id', $activeDistrictIds));
                });
            } elseif (!empty($activeCityIds)) {
                $baseHelpQuery->where(function ($q) use ($activeCityIds) {
                    $q->whereIn('city_id', $activeCityIds)
                      ->orWhereHas('customer', fn($cq) => $cq->whereIn('city_id', $activeCityIds));
                });
            } else {
                $baseHelpQuery->whereRaw('1 = 0');
            }
        }

        $helpQuery = clone $baseHelpQuery;
        if (!$isAllPeriod) {
            $helpQuery->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        }

        // Calculate Synchronized Operational Metrics
        $totalHelps = (clone $helpQuery)->count();
        $pendingHelps = (clone $helpQuery)->where('status', Help::STATUS_MENUNGGU_MITRA)->count();
        $activeHelps = (clone $helpQuery)->whereIn('status', array_merge(Help::activeStatuses(), [Help::STATUS_WAITING_CONFIRMATION]))->count();
        $completedHelps = (clone $helpQuery)->where('status', Help::STATUS_SELESAI)->count();
        $cancelledHelps = (clone $helpQuery)->where('status', Help::STATUS_DIBATALKAN)->count();

        // 3. KTP / Registration Verifications
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

        $regQuery = clone $baseRegQuery;
        if (!$isAllPeriod) {
            $regQuery->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
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
        if (!$isAllPeriod) {
            $mitraQuery->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        }
        $verifiedMitrasInPeriod = $mitraQuery->count();

        // 4. Pending Top-Up Approvals
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

        // 5. Pending Withdraw Requests
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

        // 6. Latest 6 Helps in Selected Period
        $latestHelps = (clone $helpQuery)
            ->with(['customer', 'user', 'district', 'city'])
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
                ->whereIn('status', [Help::STATUS_SELESAI, 'completed'])
                ->selectRaw('DATE(created_at) as date, count(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->all();

            // Cancelled Helps per Day
            $dailyCancelledHelps = (clone $baseHelpQuery)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->whereIn('status', [Help::STATUS_DIBATALKAN, 'cancelled', 'rejected'])
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
                ->whereIn('status', [Help::STATUS_SELESAI, 'completed'])
                ->selectRaw('DATE(created_at) as date, count(*) as total')
                ->groupBy('date')
                ->pluck('total', 'date')
                ->all();

            $dailyCancelledHelps = (clone $baseHelpQuery)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereIn('status', [Help::STATUS_DIBATALKAN, 'cancelled', 'rejected'])
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
            'managedDistricts'       => $managedDistricts,
            'managedCities'          => $managedDistricts, // For backward view compatibility
            'selectedDistrict'       => $this->selectedDistrict,
            'selectedCity'           => $this->selectedDistrict,
            'activeDistrictLabel'    => $activeDistrictLabel,
            'activeCityLabel'        => $activeDistrictLabel,
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
