<?php

namespace App\Livewire\SuperAdmin\ActivityLogs;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\City;

#[Layout('layouts.superadmin')]
class Index extends Component
{
    use WithPagination;

    // Navigation Tabs: 'directory' (Daftar Pengguna / Direktori Pelaku Aksi - Default) | 'streams' (Log Aliran Aktivitas)
    public $tab = 'directory';

    // Filter Direktori Pengguna
    public $userSearch = '';
    public $userRoleFilter = 'all'; // all, super_admin, admin, customer, mitra
    public $userPerPage = 12;

    // Filter Khusus Pengguna Terpilih (saat klik dari direktori)
    public $selectedUserId = null;
    public $selectedUserName = null;

    // Filter Aliran Log Aktivitas (Streams)
    public $search = '';
    public $roleFilter = 'all'; // all, super_admin, admin, customer, mitra
    public $actionFilter = 'all';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 20;

    // Modal Properties Detail
    public $selectedLogId = null;
    public $selectedLog = null;
    public $targetUser = null;
    public $targetHelp = null;
    public $parsedAgent = [];
    public $showRawJson = false;
    public $showPropertiesModal = false;

    protected $queryString = [
        'tab'                => ['except' => 'directory'],
        'selectedUserId'     => ['except' => null],
        'userSearch'         => ['except' => ''],
        'userRoleFilter'     => ['except' => 'all'],
        'search'             => ['except' => ''],
        'roleFilter'         => ['except' => 'all'],
        'actionFilter'       => ['except' => 'all'],
        'dateFrom'           => ['except' => ''],
        'dateTo'             => ['except' => ''],
    ];

    protected $listeners = [
        'superadmin-territory-changed' => '$refresh',
    ];

    public function mount()
    {
        if ($this->selectedUserId) {
            $u = User::find($this->selectedUserId);
            $this->selectedUserName = $u ? $u->name : null;
        }
    }

    public function setTab(string $tabName)
    {
        $this->tab = $tabName;
        $this->resetPage();
        $this->resetPage('usersPage');
    }

    public function filterByUser($userId, $userName)
    {
        $this->selectedUserId = $userId;
        $this->selectedUserName = $userName;
        $this->tab = 'streams';
        $this->resetPage();
    }

    public function clearUserFilter()
    {
        $this->selectedUserId = null;
        $this->selectedUserName = null;
        $this->resetPage();
    }

    public function updatingUserSearch()
    {
        $this->resetPage('usersPage');
    }

    public function updatingUserRoleFilter()
    {
        $this->resetPage('usersPage');
    }

    public function clearUserFilters()
    {
        $this->reset(['userSearch', 'userRoleFilter']);
        $this->resetPage('usersPage');
    }

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
        $this->showRawJson = false;

        $properties = $this->selectedLog?->properties ?? [];

        $this->targetUser = null;
        if (!empty($properties['target_user_id'])) {
            $this->targetUser = User::with('city')->find($properties['target_user_id']);
        } elseif (!empty($properties['user_id']) && $this->selectedLog?->user_id != $properties['user_id']) {
            $this->targetUser = User::with('city')->find($properties['user_id']);
        }

        $this->targetHelp = null;
        if (!empty($properties['help_id'])) {
            $this->targetHelp = \App\Models\Help::find($properties['help_id']);
        } elseif (!empty($properties['reference_id'])) {
            $this->targetHelp = \App\Models\Help::find($properties['reference_id']);
        }

        $this->parsedAgent = $this->parseUserAgent($this->selectedLog?->user_agent);
        $this->showPropertiesModal = true;
    }

    public function toggleRawJson()
    {
        $this->showRawJson = !$this->showRawJson;
    }

    public function closePropertiesModal()
    {
        $this->showPropertiesModal = false;
        $this->selectedLogId = null;
        $this->selectedLog = null;
        $this->targetUser = null;
        $this->targetHelp = null;
        $this->parsedAgent = [];
        $this->showRawJson = false;
    }

    public function parseUserAgent(?string $userAgent): array
    {
        if (!$userAgent) {
            return [
                'browser' => 'Tidak Diketahui',
                'os'      => 'Tidak Diketahui',
                'device'  => '🌐 Tidak Diketahui',
            ];
        }

        $os = 'Sistem Lainnya';
        if (stripos($userAgent, 'windows nt 10.0') !== false) $os = 'Windows 10 / 11';
        elseif (stripos($userAgent, 'windows nt 6.3') !== false) $os = 'Windows 8.1';
        elseif (stripos($userAgent, 'windows nt 6.1') !== false) $os = 'Windows 7';
        elseif (stripos($userAgent, 'macintosh') !== false || stripos($userAgent, 'mac os x') !== false) $os = 'macOS';
        elseif (stripos($userAgent, 'android') !== false) $os = 'Android';
        elseif (stripos($userAgent, 'iphone') !== false || stripos($userAgent, 'ipad') !== false) $os = 'iOS (Apple)';
        elseif (stripos($userAgent, 'linux') !== false) $os = 'Linux';

        $browser = 'Web Browser';
        if (stripos($userAgent, 'edg') !== false) $browser = 'Microsoft Edge';
        elseif (stripos($userAgent, 'chrome') !== false) $browser = 'Google Chrome';
        elseif (stripos($userAgent, 'safari') !== false) $browser = 'Apple Safari';
        elseif (stripos($userAgent, 'firefox') !== false) $browser = 'Mozilla Firefox';
        elseif (stripos($userAgent, 'opera') !== false || stripos($userAgent, 'opr') !== false) $browser = 'Opera';

        $isMobile = (stripos($userAgent, 'mobile') !== false || stripos($userAgent, 'android') !== false || stripos($userAgent, 'iphone') !== false);

        return [
            'browser' => $browser,
            'os'      => $os,
            'device'  => $isMobile ? '📱 Mobile Device' : '💻 Komputer Desktop',
        ];
    }

    public function render()
    {
        $admin = auth()->user();

        // ─────────────────────────────────────────────────────────────────────
        // 1. QUERY DIREKTORI PENGGUNA (PELAKU AKSI) - DIURUTKAN TERAKHIR AKTIF
        // ─────────────────────────────────────────────────────────────────────
        $userQuery = User::with(['city', 'latestActivityLog'])
            ->withCount('activityLogs as total_activities')
            ->withMax('activityLogs as last_activity_at', 'created_at');

        // Superadmin territory filtering
        if ($admin && in_array($admin->role, ['super_admin', 'superadmin'])) {
            $saTerritory = $admin->getActiveSuperadminTerritory();
            if ($saTerritory['type'] === 'district' && !empty($saTerritory['id'])) {
                $userQuery->where('district_id', (int) $saTerritory['id']);
            } elseif ($saTerritory['type'] === 'city' && !empty($saTerritory['id'])) {
                $cityId = (int) $saTerritory['id'];
                $saDistrictIds = $admin->getEffectiveSuperadminDistrictIds();
                $userQuery->where(function ($q) use ($cityId, $saDistrictIds) {
                    if (!empty($saDistrictIds)) $q->whereIn('district_id', $saDistrictIds);
                    if ($cityId) $q->orWhere('city_id', $cityId);
                });
            }
        }

        if ($this->userRoleFilter !== 'all') {
            if ($this->userRoleFilter === 'admin_superadmin') {
                $userQuery->whereIn('role', ['admin', 'super_admin', 'superadmin']);
            } else {
                $userQuery->where('role', $this->userRoleFilter);
            }
        }

        if (!empty($this->userSearch)) {
            $us = trim($this->userSearch);
            $userQuery->where(function ($q) use ($us) {
                $q->where('name', 'like', "%{$us}%")
                  ->orWhere('email', 'like', "%{$us}%")
                  ->orWhere('phone', 'like', "%{$us}%")
                  ->orWhere('phone_number', 'like', "%{$us}%");
            });
        }

        // Penempatan berdasarkan terakhir kali aktivitas pengguna
        $users = $userQuery
            ->orderByRaw('last_activity_at IS NULL, last_activity_at DESC, created_at DESC')
            ->paginate($this->userPerPage, ['*'], 'usersPage');

        // ─────────────────────────────────────────────────────────────────────
        // 2. QUERY DAFTAR LOG ALIRAN AKTIVITAS REAL-TIME (STREAMS)
        // ─────────────────────────────────────────────────────────────────────
        $logQuery = ActivityLog::with(['user.city', 'user.district'])
            ->orderBy('created_at', 'desc');

        // Superadmin territory filtering on logs
        if ($admin && in_array($admin->role, ['super_admin', 'superadmin'])) {
            $saTerritory = $admin->getActiveSuperadminTerritory();
            if ($saTerritory['type'] === 'district' && !empty($saTerritory['id'])) {
                $dId = (int) $saTerritory['id'];
                $logQuery->whereHas('user', fn($uq) => $uq->where('district_id', $dId));
            } elseif ($saTerritory['type'] === 'city' && !empty($saTerritory['id'])) {
                $cityId = (int) $saTerritory['id'];
                $saDistrictIds = $admin->getEffectiveSuperadminDistrictIds();
                $logQuery->whereHas('user', function ($uq) use ($cityId, $saDistrictIds) {
                    if (!empty($saDistrictIds)) $uq->whereIn('district_id', $saDistrictIds);
                    if ($cityId) $uq->orWhere('city_id', $cityId);
                });
            }
        }

        if ($this->selectedUserId) {
            $logQuery->where('user_id', $this->selectedUserId);
        }

        if ($this->search) {
            $s = trim($this->search);
            $logQuery->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('action', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($userQuery) use ($s) {
                      $userQuery->where('name', 'like', "%{$s}%")
                                ->orWhere('email', 'like', "%{$s}%")
                                ->orWhere('phone', 'like', "%{$s}%")
                                ->orWhere('phone_number', 'like', "%{$s}%");
                  });
            });
        }

        if ($this->roleFilter !== 'all') {
            $logQuery->byRole($this->roleFilter);
        }

        if ($this->actionFilter !== 'all') {
            $logQuery->where('action', $this->actionFilter);
        }

        if ($this->dateFrom) {
            $logQuery->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $logQuery->whereDate('created_at', '<=', $this->dateTo);
        }

        $logs = $logQuery->paginate($this->perPage);

        // ─────────────────────────────────────────────────────────────────────
        // 3. STATISTIK, OPSI FILTER, & WILAYAH
        // ─────────────────────────────────────────────────────────────────────
        $actions = ActivityLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $stats = [
            'total_logs'    => ActivityLog::count(),
            'today_logs'    => ActivityLog::whereDate('created_at', today())->count(),
            'admin_logs'    => ActivityLog::whereHas('user', fn($q) => $q->whereIn('role', ['admin', 'super_admin', 'superadmin']))->count(),
            'customer_logs' => ActivityLog::whereHas('user', fn($q) => $q->where('role', 'customer'))->count(),
            'mitra_logs'    => ActivityLog::whereHas('user', fn($q) => $q->where('role', 'mitra'))->count(),
        ];

        return view('livewire.superadmin.activity-logs.index', [
            'users'   => $users,
            'logs'    => $logs,
            'actions' => $actions,
            'stats'   => $stats,
        ]);
    }
}
