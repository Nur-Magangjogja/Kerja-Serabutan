<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\PartnerActivity;
use App\Models\User;

#[Layout('layouts.superadmin')]
class ActivityLogs extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = 'all';
    public $actionFilter = 'all';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => 'all'],
        'actionFilter' => ['except' => 'all'],
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

    public function render()
    {
        $query = PartnerActivity::with('user')
            ->orderBy('created_at', 'desc');

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('action', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function ($userQuery) {
                      $userQuery->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Role filter
        if ($this->roleFilter !== 'all') {
            $query->byRole($this->roleFilter);
        }

        // Action filter
        if ($this->actionFilter !== 'all') {
            $query->where('activity_type', $this->actionFilter);
        }

        // Date range filter
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $logs = $query->paginate($this->perPage);

        // Get unique activity types for filter dropdown
        $actions = PartnerActivity::select('activity_type')
            ->distinct()
            ->orderBy('activity_type')
            ->pluck('activity_type');

        // Statistics
        $stats = [
            'total_logs' => PartnerActivity::count(),
            'today_logs' => PartnerActivity::whereDate('created_at', today())->count(),
            'admin_logs' => PartnerActivity::whereHas('user', function ($q) { $q->where('role', 'admin'); })->count(),
            'customer_logs' => PartnerActivity::whereHas('user', function ($q) { $q->where('role', 'customer'); })->count(),
            'mitra_logs' => PartnerActivity::whereHas('user', function ($q) { $q->where('role', 'mitra'); })->count(),
        ];

        return view('livewire.superadmin.activity-logs', [
            'logs' => $logs,
            'actions' => $actions,
            'stats' => $stats,
        ]);
    }
}
