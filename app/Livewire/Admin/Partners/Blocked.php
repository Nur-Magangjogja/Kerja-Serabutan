<?php

namespace App\Livewire\Admin\Partners;

use App\Models\User;
use App\Models\PartnerActivity;
use Livewire\Component;
use Livewire\WithPagination;

class Blocked extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = 'all';

    // Modal Blokir User
    public $showBlockModal = false;
    public $userSearch = '';
    public $targetRole = 'all'; // all, mitra, customer
    public $selectedUserId = null;
    public $selectedUserName = '';
    public $selectedUserRole = '';
    public $selectedUserEmail = '';
    public $blockReason = '';

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

    public function openBlockModal()
    {
        $this->reset(['userSearch', 'targetRole', 'selectedUserId', 'selectedUserName', 'selectedUserRole', 'selectedUserEmail', 'blockReason']);
        $this->showBlockModal = true;
    }

    public function closeBlockModal()
    {
        $this->showBlockModal = false;
    }

    public function selectUserForBlock($userId, $userName, $userRole, $userEmail)
    {
        $this->selectedUserId = $userId;
        $this->selectedUserName = $userName;
        $this->selectedUserRole = $userRole;
        $this->selectedUserEmail = $userEmail;
    }

    public function submitBlockUser()
    {
        $this->validate([
            'selectedUserId' => 'required|exists:users,id',
            'blockReason'    => 'required|string|min:5|max:1000',
        ], [
            'selectedUserId.required' => 'Silakan pilih pengguna yang ingin diblokir.',
            'blockReason.required'    => 'Alasan pemblokiran wajib diisi.',
            'blockReason.min'         => 'Alasan pemblokiran minimal 5 karakter.',
        ]);

        $user = User::findOrFail($this->selectedUserId);
        $admin = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        // Territory validation for regular admin
        if (!$isSuperAdmin) {
            $managedCityIds = $admin ? $admin->getAdminCityIds() : [];
            if (!empty($managedCityIds) && !in_array($user->city_id, $managedCityIds)) {
                $this->addError('selectedUserId', 'Anda tidak memiliki hak akses untuk memblokir pengguna di luar wilayah Anda.');
                return;
            } elseif (empty($managedCityIds)) {
                $this->addError('selectedUserId', 'Anda belum memiliki wilayah wewenang.');
                return;
            }
        }

        $user->update(['status' => 'blocked']);

        $roleLabel = ($user->role === 'mitra') ? 'Mitra' : 'Customer';
        $descText = "Admin {$admin->name} memblokir akun {$roleLabel} {$user->name} ({$user->email}). Alasan: {$this->blockReason}";

        PartnerActivity::create([
            'user_id'       => $user->id,
            'activity_type' => 'partner_blocked',
            'description'   => $descText,
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->userAgent(),
        ]);

        \App\Models\ActivityLog::record(
            $admin,
            'partner_blocked',
            $descText,
            ['target_user_id' => $user->id, 'role' => $user->role, 'reason' => $this->blockReason]
        );

        $this->showBlockModal = false;
        $this->reset(['userSearch', 'selectedUserId', 'selectedUserName', 'selectedUserRole', 'selectedUserEmail', 'blockReason']);
        $this->resetPage();

        session()->flash('success', "Akun {$roleLabel} {$user->name} berhasil diblokir.");
    }

    public function toggleBlock($userId)
    {
        $user = User::findOrFail($userId);
        $admin = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        // Territory validation for regular admin
        if (!$isSuperAdmin) {
            $managedCityIds = $admin ? $admin->getAdminCityIds() : [];
            if (!empty($managedCityIds) && !in_array($user->city_id, $managedCityIds)) {
                session()->flash('error', 'Anda tidak memiliki hak akses untuk mengubah status pengguna di luar wilayah Anda.');
                return;
            } elseif (empty($managedCityIds)) {
                session()->flash('error', 'Anda belum memiliki wilayah wewenang.');
                return;
            }
        }

        $newStatus = ($user->status === 'blocked') ? 'active' : 'blocked';
        $user->update(['status' => $newStatus]);

        $actionText = ($newStatus === 'blocked') ? 'partner_blocked' : 'partner_unblocked';
        $descText = ($newStatus === 'blocked')
            ? "Admin " . ($admin->name ?? 'Admin') . " memblokir akun {$user->name} ({$user->email})"
            : "Admin " . ($admin->name ?? 'Admin') . " membuka blokir akun {$user->name} ({$user->email})";

        PartnerActivity::create([
            'user_id'       => $user->id,
            'activity_type' => $actionText,
            'description'   => $descText,
            'ip_address'    => request()->ip(),
            'user_agent'    => request()->userAgent(),
        ]);

        \App\Models\ActivityLog::record(
            $admin,
            $actionText,
            $descText,
            ['target_user_id' => $user->id, 'role' => $user->role]
        );

        session()->flash('success', ($newStatus === 'blocked')
            ? "Akun {$user->name} berhasil diblokir."
            : "Blokir pada akun {$user->name} berhasil dibuka."
        );
    }

    public function render()
    {
        $admin = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        $query = User::where('status', 'blocked')->with('city')->latest();

        if (! $isSuperAdmin) {
            $managedCityIds = $admin ? $admin->getAdminCityIds() : [];
            if (!empty($managedCityIds)) {
                $query->whereIn('city_id', $managedCityIds);
            } elseif ($admin && $admin->role === 'admin') {
                $query->whereRaw('1 = 0');
            }
        }

        if ($this->roleFilter !== 'all') {
            $query->where('role', $this->roleFilter);
        }

        if (!empty($this->search)) {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $blockedUsers = $query->paginate(10);

        // Search active users for blocking modal
        $availableUsers = collect();
        if ($this->showBlockModal) {
            $userQuery = User::where('status', '!=', 'blocked')
                ->whereIn('role', ['mitra', 'customer'])
                ->with('city');

            if (!$isSuperAdmin) {
                $managedCityIds = $admin ? $admin->getAdminCityIds() : [];
                if (!empty($managedCityIds)) {
                    $userQuery->whereIn('city_id', $managedCityIds);
                } elseif ($admin && $admin->role === 'admin') {
                    $userQuery->whereRaw('1 = 0');
                }
            }

            if ($this->targetRole !== 'all') {
                $userQuery->where('role', $this->targetRole);
            }

            if (!empty(trim($this->userSearch))) {
                $us = trim($this->userSearch);
                $userQuery->where(function ($q) use ($us) {
                    $q->where('name', 'like', "%{$us}%")
                      ->orWhere('email', 'like', "%{$us}%")
                      ->orWhere('phone', 'like', "%{$us}%");
                });
            }

            $availableUsers = $userQuery->latest()->limit(15)->get();
        }

        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.admin.partners.blocked', [
            'blockedUsers'   => $blockedUsers,
            'availableUsers' => $availableUsers,
            'isSuperAdmin'   => $isSuperAdmin,
        ])->layout($layout);
    }
}

