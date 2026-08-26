<?php

namespace App\Livewire\Admin\Partners\Reports;

use App\Models\City;
use App\Models\PartnerReport;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $status = 'all';
    public $category = 'all';
    public $reportType = 'all';
    public $refundStatus = 'all';
    public $search = '';

    protected $queryString = [
        'status' => ['except' => 'all'],
        'category' => ['except' => 'all'],
        'reportType' => ['except' => 'all'],
        'refundStatus' => ['except' => 'all'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatingRefundStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $admin = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        $statsQuery = PartnerReport::query();

        if (! $isSuperAdmin) {
            $cityIds = collect([$admin->city_id])
                ->merge($admin->managedCities?->pluck('id') ?? [])
                ->merge(City::where('admin_id', $admin->id)->pluck('id'))
                ->filter()
                ->unique();

            if ($cityIds->isNotEmpty()) {
                $statsQuery->where(function ($q) use ($cityIds) {
                    $q->whereHas('reporter', function ($sq) use ($cityIds) {
                        $sq->whereIn('city_id', $cityIds);
                    })->orWhereHas('reportedUser', function ($sq) use ($cityIds) {
                        $sq->whereIn('city_id', $cityIds);
                    });
                });
            }
        }

        // Stats
        $totalPending = (clone $statsQuery)->where('status', 'pending')->count();
        $totalInProgress = (clone $statsQuery)->whereIn('status', ['in_progress', 'investigating'])->count();
        $totalResolved = (clone $statsQuery)->where('status', 'resolved')->count();
        $totalRefundRequested = (clone $statsQuery)->where('refund_status', 'requested')->count();
        $totalFromCustomer = (clone $statsQuery)->where(function ($q) {
            $q->whereHas('reporter', fn($sq) => $sq->where('role', 'customer'))
              ->orWhere('report_type', 'customer_to_partner');
        })->count();
        $totalFromMitra = (clone $statsQuery)->where(function ($q) {
            $q->whereHas('reporter', fn($sq) => $sq->where('role', 'mitra'))
              ->orWhere('report_type', 'partner_to_customer');
        })->count();

        // Main Query
        $query = PartnerReport::with(['reporter', 'reportedUser', 'reportedHelp.city', 'resolvedBy'])->withCount('messages');

        if (! $isSuperAdmin && isset($cityIds) && $cityIds->isNotEmpty()) {
            $query->where(function ($q) use ($cityIds) {
                $q->whereHas('reporter', function ($sq) use ($cityIds) {
                    $sq->whereIn('city_id', $cityIds);
                })->orWhereHas('reportedUser', function ($sq) use ($cityIds) {
                    $sq->whereIn('city_id', $cityIds);
                });
            });
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->refundStatus !== 'all') {
            $query->where('refund_status', $this->refundStatus);
        }

        if ($this->category === 'dari_customer') {
            $query->where(function ($q) {
                $q->whereHas('reporter', fn($sq) => $sq->where('role', 'customer'))
                  ->orWhere('report_type', 'customer_to_partner');
            });
        } elseif ($this->category === 'dari_mitra') {
            $query->where(function ($q) {
                $q->whereHas('reporter', fn($sq) => $sq->where('role', 'mitra'))
                  ->orWhere('report_type', 'partner_to_customer');
            });
        }

        if ($this->reportType !== 'all') {
            $query->where('report_type', $this->reportType);
        }

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('reporter', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('reportedUser', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $reports = $query->latest()->paginate(10);
        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.admin.partners.reports.index', [
            'reports' => $reports,
            'totalPending' => $totalPending,
            'totalInProgress' => $totalInProgress,
            'totalResolved' => $totalResolved,
            'totalRefundRequested' => $totalRefundRequested,
            'totalFromCustomer' => $totalFromCustomer,
            'totalFromMitra' => $totalFromMitra,
        ])->layout($layout);
    }
}
