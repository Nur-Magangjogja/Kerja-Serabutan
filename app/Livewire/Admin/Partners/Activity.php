<?php

namespace App\Livewire\Admin\Partners;

use App\Models\City;
use App\Models\Help;
use App\Models\PartnerActivity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Activity extends Component
{
    use WithPagination;

    // Navigation Tabs: 'directory' (Direktori Pelaku Aksi - Default) | 'streams' (Log Seluruh Aliran Aktivitas)
    public $tab = 'directory';

    // Filter Direktori Pengguna
    public $userSearch = '';
    public $userRoleFilter = 'all'; // all, customer, mitra
    public $userCityId = 'all';
    public $userPerPage = 12;

    // Filter Khusus Pengguna Terpilih (saat klik user dari direktori)
    public $selectedUserId = null;
    public $selectedUserName = null;

    // Filter Aliran Aktivitas (Streams)
    public $search = '';
    public $roleFilter = 'all'; // all, customer, mitra
    public $activityTypeFilter = 'all';
    public $cityId = 'all';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 15;

    // Modal Detail Bantuan
    public $selectedHelpId = null;
    public $selectedHelp = null;
    public $showHelpModal = false;

    protected $queryString = [
        'tab'                => ['except' => 'directory'],
        'selectedUserId'     => ['except' => null],
        'userSearch'         => ['except' => ''],
        'userRoleFilter'     => ['except' => 'all'],
        'search'             => ['except' => ''],
        'roleFilter'         => ['except' => 'all'],
        'activityTypeFilter' => ['except' => 'all'],
        'dateFrom'           => ['except' => ''],
        'dateTo'             => ['except' => ''],
    ];

    protected $listeners = [
        'admin-city-changed'             => '$refresh',
        'superadmin-territory-changed'   => '$refresh',
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

    public function updatingUserCityId()
    {
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

    public function updatingActivityTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingCityId()
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
        $this->reset(['search', 'roleFilter', 'activityTypeFilter', 'cityId', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function showHelpDetails($helpId)
    {
        $this->selectedHelpId = $helpId;
        $this->selectedHelp = Help::with(['customer', 'mitra', 'city', 'ratings.rater'])
            ->find($helpId);

        if ($this->selectedHelp) {
            $this->showHelpModal = true;
        } else {
            session()->flash('error', 'Data bantuan tidak ditemukan.');
        }
    }

    public function closeHelpDetails()
    {
        $this->showHelpModal = false;
        $this->selectedHelpId = null;
        $this->selectedHelp = null;
    }

    /**
     * Pemetaan kategori filter ke nilai acuan activity_type di database
     * agar kompatibel dengan seluruh varian penamaan riwayat aktivitas di sistem.
     */
    public static function getActivityTypeAliases(string $filter): array
    {
        $map = [
            'help_created'            => ['help_created'],
            'take_help'               => ['take_help', 'help_accepted'],
            'partner_on_the_way'      => ['partner_on_the_way', 'partner_departed', 'partner_started_moving'],
            'partner_arrived'         => ['partner_arrived'],
            'help_started'            => ['help_started', 'service_started'],
            'help_completed'          => ['help_completed', 'service_completed', 'help_completed_waiting_confirmation'],
            'help_confirmed'          => ['help_confirmed', 'confirm_completion', 'help_auto_confirmed', 'auto_complete'],
            'cancel_help'             => ['cancel_help', 'help_cancelled', 'help_auto_cancelled', 'partner_cancel_executed', 'request_partner_cancel'],
            'partner_cancel_executed' => ['partner_cancel_executed', 'request_partner_cancel'],
            'dispute_raised'          => ['dispute_raised', 'dispute_resolved', 'warranty_claim_escrow_clawback'],
            'help_reviewed'           => ['help_reviewed'],
            'account_updated'         => ['profile_updated', 'password_changed'],
            'balance_topup'           => ['balance_topup'],
        ];

        return $map[$filter] ?? [$filter];
    }

    public function render()
    {
        $admin = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        // ─────────────────────────────────────────────────────────────────────
        // 1. QUERY DIREKTORI PENGGUNA (PELAKU AKSI) - DIURUTKAN TERAKHIR AKTIF
        // ─────────────────────────────────────────────────────────────────────
        $userQuery = User::whereIn('role', ['customer', 'mitra'])
            ->with(['city', 'latestPartnerActivity.help'])
            ->withCount('partnerActivities as total_activities')
            ->withMax('partnerActivities as last_activity_at', 'created_at');

        $effectiveDistrictIds = $admin ? $admin->getEffectiveAdminDistrictIds() : [];

        if (!$isSuperAdmin && !empty($effectiveDistrictIds)) {
            $userQuery->whereIn('district_id', $effectiveDistrictIds);
        } elseif (!$isSuperAdmin && $admin && $admin->role === 'admin') {
            $userQuery->whereRaw('1 = 0');
        } elseif ($isSuperAdmin && $admin) {
            $saTerritory = $admin->getActiveSuperadminTerritory();
            if ($saTerritory['type'] === 'district' && !empty($saTerritory['id'])) {
                $userQuery->where('district_id', (int) $saTerritory['id']);
            } elseif ($saTerritory['type'] === 'city' && !empty($saTerritory['id'])) {
                $cityId = (int) $saTerritory['id'];
                $saDistrictIds = $admin->getEffectiveSuperadminDistrictIds();
                $userQuery->where(function ($q) use ($cityId, $saDistrictIds) {
                    if (!empty($saDistrictIds)) {
                        $q->whereIn('district_id', $saDistrictIds);
                    }
                    if ($cityId) {
                        $q->orWhere('city_id', $cityId);
                    }
                });
            }
        }

        if ($this->userRoleFilter !== 'all') {
            $userQuery->where('role', $this->userRoleFilter);
        }

        if ($this->userCityId !== 'all') {
            $userQuery->where(function($q) {
                $q->where('district_id', $this->userCityId)
                  ->orWhere('city_id', $this->userCityId);
            });
        }

        if (!empty($this->userSearch)) {
            $us = trim($this->userSearch);
            $userQuery->where(function ($q) use ($us) {
                $q->where('name', 'like', "%{$us}%")
                  ->orWhere('email', 'like', "%{$us}%")
                  ->orWhere('phone_number', 'like', "%{$us}%");
            });
        }

        // Penempatan berdasarkan terakhir kali aktivitas pengguna
        $users = $userQuery
            ->orderByRaw('last_activity_at IS NULL, last_activity_at DESC, created_at DESC')
            ->paginate($this->userPerPage, ['*'], 'usersPage');

        // ─────────────────────────────────────────────────────────────────────
        // 2. QUERY DAFTAR LOG ALIRAN AKTIVITAS REAL-TIME
        // ─────────────────────────────────────────────────────────────────────
        $activityQuery = PartnerActivity::with([
            'user.district',
            'user.city',
            'help.customer',
            'help.mitra',
            'help.district',
            'help.city'
        ])
        ->whereHas('user', function ($q) {
            $q->whereIn('role', ['customer', 'mitra']);
        })
        ->latest();

        if (!$isSuperAdmin && !empty($effectiveDistrictIds)) {
            $activityQuery->where(function ($q) use ($effectiveDistrictIds) {
                $q->whereHas('user', fn($uq) => $uq->whereIn('district_id', $effectiveDistrictIds))
                  ->orWhereHas('help', fn($hq) => $hq->whereIn('district_id', $effectiveDistrictIds));
            });
        } elseif (!$isSuperAdmin && $admin && $admin->role === 'admin') {
            $activityQuery->whereRaw('1 = 0');
        } elseif ($isSuperAdmin && $admin) {
            $saTerritory = $admin->getActiveSuperadminTerritory();
            if ($saTerritory['type'] === 'district' && !empty($saTerritory['id'])) {
                $dId = (int) $saTerritory['id'];
                $activityQuery->where(function ($q) use ($dId) {
                    $q->whereHas('user', fn($uq) => $uq->where('district_id', $dId))
                      ->orWhereHas('help', fn($hq) => $hq->where('district_id', $dId));
                });
            } elseif ($saTerritory['type'] === 'city' && !empty($saTerritory['id'])) {
                $cityId = (int) $saTerritory['id'];
                $saDistrictIds = $admin->getEffectiveSuperadminDistrictIds();
                $activityQuery->where(function ($q) use ($cityId, $saDistrictIds) {
                    $q->whereHas('user', function ($uq) use ($cityId, $saDistrictIds) {
                        if (!empty($saDistrictIds)) $uq->whereIn('district_id', $saDistrictIds);
                        if ($cityId) $uq->orWhere('city_id', $cityId);
                    })->orWhereHas('help', function ($hq) use ($cityId, $saDistrictIds) {
                        if (!empty($saDistrictIds)) $hq->whereIn('district_id', $saDistrictIds);
                        if ($cityId) $hq->orWhere('city_id', $cityId);
                    });
                });
            }
        }

        if ($this->selectedUserId) {
            $activityQuery->where('user_id', $this->selectedUserId);
        }

        if ($this->roleFilter !== 'all') {
            $activityQuery->whereHas('user', fn($q) => $q->where('role', $this->roleFilter));
        }

        if ($this->activityTypeFilter !== 'all') {
            $aliases = self::getActivityTypeAliases($this->activityTypeFilter);
            $activityQuery->whereIn('activity_type', $aliases);
        }

        if ($this->cityId !== 'all') {
            $cId = $this->cityId;
            $activityQuery->where(function ($q) use ($cId) {
                $q->whereHas('user', fn($uq) => $uq->where('district_id', $cId)->orWhere('city_id', $cId))
                  ->orWhereHas('help', fn($hq) => $hq->where('district_id', $cId)->orWhere('city_id', $cId));
            });
        }

        if ($this->dateFrom) {
            $activityQuery->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $activityQuery->whereDate('created_at', '<=', $this->dateTo);
        }

        if (!empty($this->search)) {
            $s = trim($this->search);
            $activityQuery->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('activity_type', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('name', 'like', "%{$s}%")
                         ->orWhere('email', 'like', "%{$s}%")
                         ->orWhere('phone_number', 'like', "%{$s}%");
                  })
                  ->orWhereHas('help', function ($hq) use ($s) {
                      $hq->where('title', 'like', "%{$s}%")
                         ->orWhere('order_id', 'like', "%{$s}%")
                         ->orWhere('location', 'like', "%{$s}%")
                         ->orWhere('full_address', 'like', "%{$s}%");
                  });
            });
        }

        $activities = $activityQuery->paginate($this->perPage);

        // ─────────────────────────────────────────────────────────────────────
        // 3. DAFTAR WILAYAH & STATISTIK
        // ─────────────────────────────────────────────────────────────────────
        if ($isSuperAdmin) {
            $cities = City::orderBy('name')->get();
        } else {
            $cities = $admin ? $admin->getAdminDistricts() : collect();
        }

        $baseStats = PartnerActivity::whereHas('user', fn($q) => $q->whereIn('role', ['customer', 'mitra']));
        if (!$isSuperAdmin && !empty($effectiveDistrictIds)) {
            $baseStats->where(function ($q) use ($effectiveDistrictIds) {
                $q->whereHas('user', fn($uq) => $uq->whereIn('district_id', $effectiveDistrictIds))
                  ->orWhereHas('help', fn($hq) => $hq->whereIn('district_id', $effectiveDistrictIds));
            });
        } elseif (!$isSuperAdmin && $admin && $admin->role === 'admin') {
            $baseStats->whereRaw('1 = 0');
        } elseif ($isSuperAdmin && $admin) {
            $saTerritory = $admin->getActiveSuperadminTerritory();
            if ($saTerritory['type'] === 'district' && !empty($saTerritory['id'])) {
                $dId = (int) $saTerritory['id'];
                $baseStats->where(function ($q) use ($dId) {
                    $q->whereHas('user', fn($uq) => $uq->where('district_id', $dId))
                      ->orWhereHas('help', fn($hq) => $hq->where('district_id', $dId));
                });
            } elseif ($saTerritory['type'] === 'city' && !empty($saTerritory['id'])) {
                $cityId = (int) $saTerritory['id'];
                $saDistrictIds = $admin->getEffectiveSuperadminDistrictIds();
                $baseStats->where(function ($q) use ($cityId, $saDistrictIds) {
                    $q->whereHas('user', function ($uq) use ($cityId, $saDistrictIds) {
                        if (!empty($saDistrictIds)) $uq->whereIn('district_id', $saDistrictIds);
                        if ($cityId) $uq->orWhere('city_id', $cityId);
                    })->orWhereHas('help', function ($hq) use ($cityId, $saDistrictIds) {
                        if (!empty($saDistrictIds)) $hq->whereIn('district_id', $saDistrictIds);
                        if ($cityId) $hq->orWhere('city_id', $cityId);
                    });
                });
            }
        }

        // Terapkan filter khusus pengguna pelaku jika sedang aktif
        if ($this->selectedUserId) {
            $baseStats->where('user_id', $this->selectedUserId);
        }

        $stats = [
            'total'          => (clone $baseStats)->count(),
            'today'          => (clone $baseStats)->whereDate('created_at', today())->count(),
            'customer_acts'  => (clone $baseStats)->whereHas('user', fn($q) => $q->where('role', 'customer'))->count(),
            'mitra_acts'     => (clone $baseStats)->whereHas('user', fn($q) => $q->where('role', 'mitra'))->count(),
            'completed_jobs' => (clone $baseStats)->whereIn('activity_type', ['help_completed', 'service_completed', 'confirm_completion', 'help_confirmed'])->count(),
        ];


        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.admin.partners.activity', [
            'users'      => $users,
            'activities' => $activities,
            'cities'     => $cities,
            'stats'      => $stats,
        ])->layout($layout);
    }
}
