<?php

namespace App\Livewire\SuperAdmin\ActivityLogs;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\ActivityLog;
use App\Models\User;

#[Layout('layouts.superadmin')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = 'all'; // all, super_admin, admin, customer, mitra
    public $actionFilter = 'all';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 20;

    // Modal Properties Detail
    public $selectedLogId = null;
    public $selectedLog = null;
    public $showPropertiesModal = false;

    protected $queryString = [
        'search'       => ['except' => ''],
        'roleFilter'   => ['except' => 'all'],
        'actionFilter' => ['except' => 'all'],
        'dateFrom'     => ['except' => ''],
        'dateTo'       => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingActionFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'roleFilter', 'actionFilter', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function showProperties($logId)
    {
        $this->selectedLogId = $logId;
        $this->selectedLog = ActivityLog::with('user')->find($logId);
        $this->showPropertiesModal = true;
    }

    public function closePropertiesModal()
    {
        $this->showPropertiesModal = false;
        $this->selectedLogId = null;
        $this->selectedLog = null;
    }

    public function render()
    {
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        // Search filter
        if ($this->search) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('action', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($userQuery) use ($s) {
                      $userQuery->where('name', 'like', "%{$s}%")
                                ->orWhere('email', 'like', "%{$s}%")
                                ->orWhere('phone', 'like', "%{$s}%");
                  });
            });
        }

        // Role filter
        if ($this->roleFilter !== 'all') {
            $query->byRole($this->roleFilter);
        }

        // Action filter
        if ($this->actionFilter !== 'all') {
            $query->where('action', $this->actionFilter);
        }

        // Date range filter
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $logs = $query->paginate($this->perPage);

        // Get unique actions for filter dropdown
        $actions = ActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        // Comprehensive system-wide statistics (Superadmin, Admin, Customer, Mitra)
        $stats = [
            'total_logs'    => ActivityLog::count(),
            'today_logs'    => ActivityLog::whereDate('created_at', today())->count(),
            'admin_logs'    => ActivityLog::whereHas('user', fn($q) => $q->whereIn('role', ['admin', 'super_admin']))->count(),
            'customer_logs' => ActivityLog::whereHas('user', fn($q) => $q->where('role', 'customer'))->count(),
            'mitra_logs'    => ActivityLog::whereHas('user', fn($q) => $q->where('role', 'mitra'))->count(),
        ];

        return view('livewire.superadmin.activity-logs.index', [
            'logs'    => $logs,
            'actions' => $actions,
            'stats'   => $stats,
        ]);
    }
}

