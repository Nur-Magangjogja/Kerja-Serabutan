<?php

namespace App\Livewire\SuperAdmin\Dashboard;

use App\Models\User;
use App\Models\City;
use App\Models\Category;
use App\Models\Help;
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

    public function updateChartData()
    {
        // 1. Daily - single grouped query for days in the selected month
        $selectedMonthCarbon = Carbon::parse($this->selectedMonth . '-01');
        $startOfMonth = $selectedMonthCarbon->copy()->startOfMonth();
        $endOfMonth = $selectedMonthCarbon->copy()->endOfMonth();
        $daysInMonth = $selectedMonthCarbon->daysInMonth;

        $dailyCounts = User::whereBetween('created_at', [$startOfMonth, $endOfMonth])
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

        $monthlyCounts = User::whereBetween('created_at', [$startOfYear, $endOfYear])
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

        $yearlyCounts = User::whereBetween('created_at', [$startOf5Years, $endOf5Years])
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
            'daily' => ['labels' => $dailyLabels, 'data' => $dailyData],
            'monthly' => ['labels' => $monthlyLabels, 'data' => $monthlyData],
            'yearly' => ['labels' => $yearlyLabels, 'data' => $yearlyData],
        ];
    }

    public function render()
    {
        $stats = [
            'total_users' => User::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_mitras' => User::where('role', 'mitra')->count(),
            'total_admins' => User::whereIn('role', ['admin', 'super_admin'])->count(),
            'total_cities' => City::count(),
            'total_categories' => Category::count(),
            'pending_helps' => Help::whereIn('status', ['pending', 'menunggu_mitra'])->count(),
            'active_helps' => Help::whereIn('status', ['active', 'memperoleh_mitra', 'sedang_diproses', 'taken', 'partner_on_the_way', 'partner_arrived', 'in_progress', 'waiting_customer_confirmation'])->count(),
            'completed_helps' => Help::whereIn('status', ['selesai', 'completed'])->count(),
            'pending_withdraws' => \App\Models\WithdrawRequest::where('status', \App\Models\WithdrawRequest::STATUS_PENDING)->count(),
            'pending_topups' => \App\Models\BalanceTransaction::where('type', 'topup')->where('status', 'waiting_approval')->count(),
        ];

        // Recent items for quick view
        $recentUsers = User::orderByDesc('created_at')->limit(6)->get(['id', 'name', 'email', 'role', 'created_at']);
        $recentTransactions = \App\Models\BalanceTransaction::with('user')->orderByDesc('created_at')->limit(6)->get();
        $recentHelps = Help::with('user')->orderByDesc('created_at')->limit(8)->get(['id', 'title', 'status', 'created_at', 'user_id', 'amount']);

        return view('livewire.superadmin.dashboard.index', compact('stats', 'recentUsers', 'recentTransactions', 'recentHelps'));
    }
}

