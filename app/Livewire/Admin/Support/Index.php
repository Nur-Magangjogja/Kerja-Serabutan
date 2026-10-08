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

        // Consolidated Support Statistics - strictly report_type = 'dukungan_umum' and Profile Territory scoped
        $nowStr = now()->toDateTimeString();
        $stats = (clone $baseQuery)
            ->selectRaw("
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as total_pending,
                SUM(CASE WHEN status IN ('in_progress', 'investigating', 'under_review') THEN 1 ELSE 0 END) as total_in_progress,
                SUM(CASE WHEN muted_until IS NOT NULL AND muted_until > ? THEN 1 ELSE 0 END) as total_muted,
                SUM(CASE WHEN category = 'dari_customer' THEN 1 ELSE 0 END) as total_from_customer,
                SUM(CASE WHEN category = 'dari_mitra' THEN 1 ELSE 0 END) as total_from_mitra
            ", [$nowStr])
            ->first();

        $totalPending      = (int) ($stats->total_pending ?? 0);
        $totalInProgress   = (int) ($stats->total_in_progress ?? 0);
        $totalMuted        = (int) ($stats->total_muted ?? 0);
        $totalFromCustomer = (int) ($stats->total_from_customer ?? 0);
        $totalFromMitra    = (int) ($stats->total_from_mitra ?? 0);

        // Main Query with filters
        $query = (clone $baseQuery)
            ->with(['reporter.district', 'reporter.city', 'reportedHelp'])
            ->withCount('messages');

        if ($this->status !== 'all') {
            if ($this->status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($this->status === 'in_progress') {
                $query->whereIn('status', ['in_progress', 'investigating', 'under_review']);
            } elseif ($this->status === 'muted') {
                $query->whereNotNull('muted_until')->where('muted_until', '>', now());
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
            'totalMuted'        => $totalMuted,
            'totalFromCustomer' => $totalFromCustomer,
            'totalFromMitra'    => $totalFromMitra,
            'routePrefix'       => $routePrefix,
        ])->layout($layout);
    }
}
