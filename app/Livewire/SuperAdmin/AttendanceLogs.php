<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\ActivityLog;

#[Layout('layouts.superadmin')]
class AttendanceLogs extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = 'all';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 20;

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
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
        $this->reset(['search', 'roleFilter', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render()
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        // Limit to attendance-related actions (accept common variants)
        $query->where(function ($q) {
            $q->where('action', 'attendance')
              ->orWhere('action', 'absensi')
              ->orWhere('action', 'absen')
              ->orWhere('action', 'check_in')
              ->orWhere('action', 'check_out')
              ->orWhere('action', 'clock_in')
              ->orWhere('action', 'clock_out')
              ->orWhere('action', 'attendance_record')
              ->orWhere('action', 'attendance_log')
              ->orWhere('action', 'attendance_mark')
              ->orWhere('action', 'attendance_update')
              ->orWhere('description', 'like', '%absen%')
              ->orWhere('description', 'like', '%attendance%');
        });

        if ($this->search) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', '%' . $s . '%')
                  ->orWhere('action', 'like', '%' . $s . '%')
                  ->orWhereHas('user', function ($qu) use ($s) {
                      $qu->where('name', 'like', '%' . $s . '%')
                         ->orWhere('email', 'like', '%' . $s . '%');
                  });
            });
        }

        if ($this->roleFilter !== 'all') {
            $query->byRole($this->roleFilter);
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $logs = $query->paginate($this->perPage);

        // Stats
        $stats = [
            'total' => ActivityLog::where(function ($q) {
                $q->where('action', 'attendance')
                  ->orWhere('action', 'absensi')
                  ->orWhere('action', 'absen')
                  ->orWhere('description', 'like', '%absen%')
                  ->orWhere('description', 'like', '%attendance%');
            })->count(),
            'today' => ActivityLog::whereDate('created_at', today())->where(function ($q) {
                $q->where('action', 'attendance')
                  ->orWhere('action', 'absensi')
                  ->orWhere('action', 'absen')
                  ->orWhere('description', 'like', '%absen%')
                  ->orWhere('description', 'like', '%attendance%');
            })->count(),
        ];

        return view('livewire.superadmin.attendance-logs', [
            'logs' => $logs,
            'stats' => $stats,
        ]);
    }
}
