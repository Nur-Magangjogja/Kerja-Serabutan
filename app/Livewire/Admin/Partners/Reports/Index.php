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

        $resolver = app(\App\Services\Territory\PartnerReportTerritoryResolver::class);

        $statsQuery = PartnerReport::query()->where(function ($q) {
            $q->whereNull('report_type')
              ->orWhere('report_type', '!=', 'dukungan_umum');
        });
        $statsQuery = $resolver->applyAdminTerritoryScope($statsQuery, $admin);

        // Consolidated Report Statistics - filtered by canonical territory scope
        $stats = (clone $statsQuery)
            ->selectRaw("
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as total_pending,
                SUM(CASE WHEN status IN ('in_progress', 'investigating') THEN 1 ELSE 0 END) as total_in_progress,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as total_resolved,
                SUM(CASE WHEN refund_status IN ('requested', 'pending') THEN 1 ELSE 0 END) as total_refund_requested,
                SUM(CASE WHEN (report_type = 'customer_to_partner' OR EXISTS (SELECT 1 FROM users WHERE users.id = partner_reports.reporter_id AND users.role = 'customer')) THEN 1 ELSE 0 END) as total_from_customer,
                SUM(CASE WHEN (report_type = 'partner_to_customer' OR EXISTS (SELECT 1 FROM users WHERE users.id = partner_reports.reporter_id AND users.role = 'mitra')) THEN 1 ELSE 0 END) as total_from_mitra
            ")
            ->first();

        $totalPending = (int) ($stats->total_pending ?? 0);
        $totalInProgress = (int) ($stats->total_in_progress ?? 0);
        $totalResolved = (int) ($stats->total_resolved ?? 0);
        $totalRefundRequested = (int) ($stats->total_refund_requested ?? 0);
        $totalFromCustomer = (int) ($stats->total_from_customer ?? 0);
        $totalFromMitra = (int) ($stats->total_from_mitra ?? 0);

        // Main Query
        $query = PartnerReport::with(['reporter.district', 'reportedUser.district', 'reportedHelp.district', 'reportedHelp.city', 'resolvedBy'])
            ->withCount('messages')
            ->where(function ($q) {
                $q->whereNull('report_type')
                  ->orWhere('report_type', '!=', 'dukungan_umum');
            });
        $query = $resolver->applyAdminTerritoryScope($query, $admin);

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
