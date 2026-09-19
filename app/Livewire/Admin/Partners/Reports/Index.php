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

    protected $listeners = [
        'superadmin-territory-changed' => '$refresh',
        'admin-district-changed'       => '$refresh',
        'admin-city-changed'           => '$refresh',
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
            $districtIds = $admin ? $admin->getEffectiveAdminDistrictIds() : [];

            if (!empty($districtIds)) {
                $statsQuery->where(function ($q) use ($districtIds) {
                    $q->whereHas('reporter', function ($sq) use ($districtIds) {
                        $sq->whereIn('district_id', $districtIds);
                    })->orWhereHas('reportedUser', function ($sq) use ($districtIds) {
                        $sq->whereIn('district_id', $districtIds);
                    })->orWhereHas('reportedHelp', function ($sq) use ($districtIds) {
                        $sq->whereIn('district_id', $districtIds);
                    });
                });
            } else {
                $statsQuery->whereRaw('1 = 0');
            }
        } else {
            $territory = $admin ? $admin->getActiveSuperadminTerritory() : ['type' => 'all', 'id' => null];
            if ($territory['type'] === 'district' && $territory['id']) {
                $dId = (int) $territory['id'];
                $statsQuery->where(function ($q) use ($dId) {
                    $q->whereHas('reporter', fn($sq) => $sq->where('district_id', $dId))
                      ->orWhereHas('reportedUser', fn($sq) => $sq->where('district_id', $dId))
                      ->orWhereHas('reportedHelp', fn($sq) => $sq->where('district_id', $dId));
                });
            } elseif ($territory['type'] === 'city' && $territory['id']) {
                $cId = (int) $territory['id'];
                $districtIds = $admin ? $admin->getEffectiveSuperadminDistrictIds() : [];
                $statsQuery->where(function ($q) use ($cId, $districtIds) {
                    $q->whereHas('reporter', function ($sq) use ($cId, $districtIds) {
                        $sq->where('city_id', $cId);
                        if (!empty($districtIds)) $sq->orWhereIn('district_id', $districtIds);
                    })->orWhereHas('reportedUser', function ($sq) use ($cId, $districtIds) {
                        $sq->where('city_id', $cId);
                        if (!empty($districtIds)) $sq->orWhereIn('district_id', $districtIds);
                    })->orWhereHas('reportedHelp', function ($sq) use ($cId, $districtIds) {
                        $sq->where('city_id', $cId);
                        if (!empty($districtIds)) $sq->orWhereIn('district_id', $districtIds);
                    });
                });
            }
        }

        // Stats
        $totalPending = (clone $statsQuery)->where('status', 'pending')->count();
        $totalInProgress = (clone $statsQuery)->whereIn('status', ['in_progress', 'investigating'])->count();
        $totalResolved = (clone $statsQuery)->where('status', 'resolved')->count();
        $totalRefundRequested = (clone $statsQuery)->whereIn('refund_status', ['requested', 'pending'])->count();
        $totalFromCustomer = (clone $statsQuery)->where(function ($q) {
            $q->whereHas('reporter', fn($sq) => $sq->where('role', 'customer'))
              ->orWhere('report_type', 'customer_to_partner');
        })->count();
        $totalFromMitra = (clone $statsQuery)->where(function ($q) {
            $q->whereHas('reporter', fn($sq) => $sq->where('role', 'mitra'))
              ->orWhere('report_type', 'partner_to_customer');
        })->count();

        // Main Query
        $query = PartnerReport::with(['reporter.district', 'reportedUser.district', 'reportedHelp.district', 'reportedHelp.city', 'resolvedBy'])->withCount('messages');

        if (! $isSuperAdmin) {
            if (!empty($districtIds)) {
                $query->where(function ($q) use ($districtIds) {
                    $q->whereHas('reporter', function ($sq) use ($districtIds) {
                        $sq->whereIn('district_id', $districtIds);
                    })->orWhereHas('reportedUser', function ($sq) use ($districtIds) {
                        $sq->whereIn('district_id', $districtIds);
                    })->orWhereHas('reportedHelp', function ($sq) use ($districtIds) {
                        $sq->whereIn('district_id', $districtIds);
                    });
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            $territory = $admin ? $admin->getActiveSuperadminTerritory() : ['type' => 'all', 'id' => null];
            if ($territory['type'] === 'district' && $territory['id']) {
                $dId = (int) $territory['id'];
                $query->where(function ($q) use ($dId) {
                    $q->whereHas('reporter', fn($sq) => $sq->where('district_id', $dId))
                      ->orWhereHas('reportedUser', fn($sq) => $sq->where('district_id', $dId))
                      ->orWhereHas('reportedHelp', fn($sq) => $sq->where('district_id', $dId));
                });
            } elseif ($territory['type'] === 'city' && $territory['id']) {
                $cId = (int) $territory['id'];
                $districtIds = $admin ? $admin->getEffectiveSuperadminDistrictIds() : [];
                $query->where(function ($q) use ($cId, $districtIds) {
                    $q->whereHas('reporter', function ($sq) use ($cId, $districtIds) {
                        $sq->where('city_id', $cId);
                        if (!empty($districtIds)) $sq->orWhereIn('district_id', $districtIds);
                    })->orWhereHas('reportedUser', function ($sq) use ($cId, $districtIds) {
                        $sq->where('city_id', $cId);
                        if (!empty($districtIds)) $sq->orWhereIn('district_id', $districtIds);
                    })->orWhereHas('reportedHelp', function ($sq) use ($cId, $districtIds) {
                        $sq->where('city_id', $cId);
                        if (!empty($districtIds)) $sq->orWhereIn('district_id', $districtIds);
                    });
                });
            }
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
