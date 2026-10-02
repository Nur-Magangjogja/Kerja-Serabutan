<?php

namespace App\Livewire\Admin\Support;

use App\Models\PartnerReport;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $status = 'all';
    public $category = 'all';
    public $search = '';

    protected $queryString = [
        'status'   => ['except' => 'all'],
        'category' => ['except' => 'all'],
        'search'   => ['except' => ''],
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

    public function render()
    {
        $admin = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        $baseQuery = PartnerReport::where('report_type', 'dukungan_umum');

        if (!$isSuperAdmin) {
            $districtIds = $admin ? $admin->getEffectiveAdminDistrictIds() : [];
            $adminCityId = $admin ? $admin->city_id : null;

            if (!empty($districtIds)) {
                $baseQuery->whereHas('reporter', fn($sq) => $sq->whereIn('district_id', $districtIds));
            } elseif ($adminCityId && method_exists($admin, 'isCityOnlyAdmin') && $admin->isCityOnlyAdmin()) {
                $baseQuery->whereHas('reporter', fn($sq) => $sq->where('city_id', $adminCityId));
            } else {
                $baseQuery->whereRaw('1 = 0');
            }
        } else {
            $territory = $admin ? $admin->getActiveSuperadminTerritory() : ['type' => 'all', 'id' => null];
            if ($territory['type'] === 'district' && $territory['id']) {
                $dId = (int) $territory['id'];
                $baseQuery->whereHas('reporter', fn($sq) => $sq->where('district_id', $dId));
            } elseif ($territory['type'] === 'city' && $territory['id']) {
                $cId = (int) $territory['id'];
                $districtIds = $admin ? $admin->getEffectiveSuperadminDistrictIds() : [];
                $baseQuery->whereHas('reporter', function ($sq) use ($cId, $districtIds) {
                    $sq->where('city_id', $cId);
                    if (!empty($districtIds)) $sq->orWhereIn('district_id', $districtIds);
                });
            }
        }

        // Stats
        $totalPending    = (clone $baseQuery)->where('status', 'pending')->count();
        $totalInProgress = (clone $baseQuery)->whereIn('status', ['in_progress', 'investigating', 'under_review'])->count();
        $totalResolved   = (clone $baseQuery)->whereIn('status', ['resolved', 'closed', 'dismissed'])->count();
        $totalFromCustomer = (clone $baseQuery)->where('category', 'dari_customer')->count();
        $totalFromMitra    = (clone $baseQuery)->where('category', 'dari_mitra')->count();

        // Main Query with filters
        $query = (clone $baseQuery)
            ->with(['reporter.district', 'reporter.city', 'reportedHelp'])
            ->withCount('messages');

        if ($this->status !== 'all') {
            if ($this->status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($this->status === 'in_progress') {
                $query->whereIn('status', ['in_progress', 'investigating', 'under_review']);
            } elseif ($this->status === 'resolved') {
                $query->whereIn('status', ['resolved', 'closed', 'dismissed']);
            }
        }

        if ($this->category !== 'all') {
            $query->where('category', $this->category);
        }

        if ($this->search) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('message', 'like', $term)
                  ->orWhereHas('reporter', fn($sq) => $sq->where('name', 'like', $term));
            });
        }

        $reports = $query->latest('updated_at')->paginate(15);
        $routePrefix = $isSuperAdmin ? 'superadmin.' : 'admin.';
        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.admin.support.index', [
            'reports'           => $reports,
            'totalPending'      => $totalPending,
            'totalInProgress'   => $totalInProgress,
            'totalResolved'     => $totalResolved,
            'totalFromCustomer' => $totalFromCustomer,
            'totalFromMitra'    => $totalFromMitra,
            'routePrefix'       => $routePrefix,
        ])->layout($layout);
    }
}
