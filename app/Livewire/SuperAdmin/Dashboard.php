<?php

namespace App\Livewire\SuperAdmin;

use App\Models\User;
use App\Models\City;
use App\Models\Category;
use App\Models\Help;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Layout('layouts.superadmin')]
class Dashboard extends Component
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
        // Prepare user registration charts based on selected date/month
        // Daily - shows days in the selected month
        $selectedMonthCarbon = Carbon::parse($this->selectedMonth . '-01');
        $daysInMonth = $selectedMonthCarbon->daysInMonth;
        $dailyLabels = [];
        $dailyData = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = $selectedMonthCarbon->copy()->day($i);
            $dailyLabels[] = $date->format('d M');
            $dailyData[] = User::whereDate('created_at', $date->toDateString())->count();
        }

        // Monthly (12 months of selected year)
        $months = 12;
        $selectedYearCarbon = Carbon::createFromDate($this->selectedYear, 1, 1);
        $monthlyLabels = [];
        $monthlyData = [];
        for ($i = 1; $i <= $months; $i++) {
            $m = $selectedYearCarbon->copy()->month($i);
            $monthlyLabels[] = $m->format('M Y');
            $monthlyData[] = User::whereYear('created_at', $m->year)->whereMonth('created_at', $m->month)->count();
        }

        // Yearly (last 5 years from selected year)
        $years = 5;
        $startYear = Carbon::createFromDate($this->selectedYear, 1, 1)->subYears($years - 1);
        $yearlyLabels = [];
        $yearlyData = [];
        for ($i = 0; $i < $years; $i++) {
            $y = $startYear->copy()->addYears($i);
            $yearlyLabels[] = (string) $y->year;
            $yearlyData[] = User::whereYear('created_at', $y->year)->count();
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
            'pending_helps' => Help::where('status', 'pending')->count(),
            'active_helps' => Help::where('status', 'active')->count(),
        ];

        // Recent items for quick view
        $recentUsers = User::orderByDesc('created_at')->limit(6)->get(['id', 'name', 'email', 'role', 'created_at']);
        $recentHelps = Help::with('user')->orderByDesc('created_at')->limit(6)->get(['id', 'title', 'status', 'created_at', 'user_id']);

        return view('superadmin.dashboard', compact('stats', 'recentUsers', 'recentHelps'));
    }
}
