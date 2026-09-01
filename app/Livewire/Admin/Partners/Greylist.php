<?php

namespace App\Livewire\Admin\Partners;

use App\Models\User;
use App\Models\UserGreylistLog;
use Livewire\Component;
use Livewire\WithPagination;

class Greylist extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = 'all'; // all, mitra, customer
    public $statusFilter = 'all'; // all, shadow_banned, warning_only, active_greylist

    // Modal Add User
    public $showAddModal = false;
    public $userSearch = '';
    public $selectedUserId = null;
    public $selectedUserName = '';
    public $addReason = '';
    public $addWarningLevel = 1;
    public $addApplyShadowBan = false;

    // Modal Issue Warning
    public $showWarningModal = false;
    public $targetUserId = null;
    public $targetUserName = '';
    public $currentWarningLevel = 0;
    public $newWarningLevel = 1;
    public $warningMessage = '';
    public $warningReason = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openAddModal()
    {
        $this->reset(['userSearch', 'selectedUserId', 'selectedUserName', 'addReason', 'addWarningLevel', 'addApplyShadowBan']);
        $this->showAddModal = true;
    }

    public function closeAddModal()
    {
        $this->showAddModal = false;
    }

    public function selectUserForGreylist($userId, $userName)
    {
        $this->selectedUserId = $userId;
        $this->selectedUserName = $userName;
    }

    public function submitAddToGreylist()
    {
        $this->validate([
            'selectedUserId' => 'required|exists:users,id',
            'addReason' => 'required|string|min:5',
            'addWarningLevel' => 'required|in:0,1,2,3',
        ], [
            'selectedUserId.required' => 'Silakan pilih user terlebih dahulu.',
            'addReason.required' => 'Alasan peninjauan wajib diisi.',
            'addReason.min' => 'Alasan minimal 5 karakter.',
        ]);

        $user = User::findOrFail($this->selectedUserId);
        $admin = auth()->user();

        $user->update([
            'is_greylisted' => true,
            'greylisted_at' => now(),
            'greylist_reason' => $this->addReason,
            'warning_level' => (int) $this->addWarningLevel,
            'is_shadow_banned' => (bool) $this->addApplyShadowBan,
            'shadow_banned_at' => $this->addApplyShadowBan ? now() : null,
            'latest_warning_message' => $this->addWarningLevel > 0 ? "Surat Peringatan SP {$this->addWarningLevel}: {$this->addReason}" : null,
            'latest_warning_at' => $this->addWarningLevel > 0 ? now() : null,
        ]);

        UserGreylistLog::create([
            'user_id' => $user->id,
            'admin_id' => $admin->id,
            'action' => 'greylist_add',
            'warning_level' => (int) $this->addWarningLevel,
            'reason' => $this->addReason,
            'message' => "User dimasukkan ke Daftar Abu-Abu oleh Admin {$admin->name}. " . ($this->addApplyShadowBan ? '[Shadow Ban Aktif]' : ''),
        ]);

        \App\Models\ActivityLog::record(
            $admin,
            'greylist_add',
            "Admin {$admin->name} memasukkan akun {$user->name} ({$user->role}) ke Daftar Abu-Abu (SP {$this->addWarningLevel}). Alasan: {$this->addReason}",
            [
                'target_user_id' => $user->id,
                'role'           => $user->role,
                'warning_level'  => (int) $this->addWarningLevel,
                'reason'         => $this->addReason,
                'is_shadow_banned' => (bool) $this->addApplyShadowBan,
            ]
        );

        $this->showAddModal = false;
        session()->flash('success', "User {$user->name} berhasil dimasukkan ke Daftar Abu-Abu.");
    }

    public function openWarningModal($userId)
    {
        $user = User::findOrFail($userId);
        $this->targetUserId = $user->id;
        $this->targetUserName = $user->name;
        $this->currentWarningLevel = (int) $user->warning_level;
        $this->newWarningLevel = min(3, max(1, $this->currentWarningLevel + 1));
        $this->warningMessage = $this->getDefaultWarningTemplate($this->newWarningLevel, $user->name);
        $this->warningReason = '';
        $this->showWarningModal = true;
    }

    public function updatedNewWarningLevel($val)
    {
        $this->warningMessage = $this->getDefaultWarningTemplate((int) $val, $this->targetUserName);
    }

    private function getDefaultWarningTemplate(int $level, string $name): string
    {
        return match ($level) {
            1 => "Halo {$name}, kami memberikan Surat Peringatan Pertama (SP 1) terkait ketidaksesuaian SOP pelayanan. Harap memperbaiki kualitas layanan dan menjaga komunikasi.",
            2 => "PERINGATAN KEDUA (SP 2): Akun Anda {$name} terindikasi melakukan pelanggaran berulang. Jika pelanggaran berlanjut, akun akan dikenakan pembatasan fitur total (Shadow Ban).",
            3 => "PERINGATAN TERAKHIR (SP 3): Akun {$name} berada di batas toleransi pelanggaran sistem SayaBantu. Pelanggaran berikutnya akan menyebabkan akun dinonaktifkan secara permanen.",
            default => "Pemberitahuan resmi moderasi kepatuhan pengguna platform SayaBantu.",
        };
    }

    public function closeWarningModal()
    {
        $this->showWarningModal = false;
    }

    public function submitWarning()
    {
        $this->validate([
            'targetUserId' => 'required|exists:users,id',
            'newWarningLevel' => 'required|in:1,2,3',
            'warningMessage' => 'required|string|min:10',
            'warningReason' => 'required|string|min:5',
        ]);

        $user = User::findOrFail($this->targetUserId);
        $admin = auth()->user();

        $user->update([
            'is_greylisted' => true,
            'warning_level' => (int) $this->newWarningLevel,
            'latest_warning_message' => $this->warningMessage,
            'latest_warning_at' => now(),
        ]);

        UserGreylistLog::create([
            'user_id' => $user->id,
            'admin_id' => $admin->id,
            'action' => 'warning_issued',
            'warning_level' => (int) $this->newWarningLevel,
            'reason' => $this->warningReason,
            'message' => $this->warningMessage,
        ]);

        \App\Models\ActivityLog::record(
            $admin,
            'warning_issued',
            "Admin {$admin->name} menerbitkan Surat Peringatan SP {$this->newWarningLevel} untuk akun {$user->name} ({$user->role}). Alasan: {$this->warningReason}",
            [
                'target_user_id' => $user->id,
                'role'           => $user->role,
                'warning_level'  => (int) $this->newWarningLevel,
                'reason'         => $this->warningReason,
                'message'        => $this->warningMessage,
            ]
        );

        $this->showWarningModal = false;
        session()->flash('success', "Surat Peringatan SP {$this->newWarningLevel} berhasil diterbitkan untuk {$user->name}.");
    }

    public function toggleShadowBan($userId)
    {
        $user = User::findOrFail($userId);
        $admin = auth()->user();
        $newStatus = ! $user->is_shadow_banned;

        $user->update([
            'is_shadow_banned' => $newStatus,
            'shadow_banned_at' => $newStatus ? now() : null,
            'is_greylisted' => true,
        ]);

        UserGreylistLog::create([
            'user_id' => $user->id,
            'admin_id' => $admin->id,
            'action' => $newStatus ? 'shadow_ban_enabled' : 'shadow_ban_disabled',
            'warning_level' => $user->warning_level,
            'reason' => $newStatus ? 'Shadow Ban diaktifkan oleh Admin' : 'Shadow Ban dinonaktifkan oleh Admin',
            'message' => $newStatus
                ? "Akun {$user->name} dikenakan Shadow Ban (pembatasan akses tugas bantuan)."
                : "Shadow Ban pada akun {$user->name} telah dicabut.",
        ]);

        \App\Models\ActivityLog::record(
            $admin,
            $newStatus ? 'shadow_ban_enabled' : 'shadow_ban_disabled',
            "Admin {$admin->name} " . ($newStatus ? "mengaktifkan status Shadow Ban" : "mencabut status Shadow Ban") . " pada akun {$user->name} ({$user->role}).",
            [
                'target_user_id'   => $user->id,
                'role'             => $user->role,
                'is_shadow_banned' => $newStatus,
            ]
        );

        session()->flash('success', $newStatus
            ? "Shadow Ban berhasil diaktifkan untuk {$user->name}."
            : "Shadow Ban berhasil dicabut dari {$user->name}."
        );
    }

    public function removeFromGreylist($userId)
    {
        $user = User::findOrFail($userId);
        $admin = auth()->user();

        $user->update([
            'is_greylisted' => false,
            'greylisted_at' => null,
            'greylist_reason' => null,
            'is_shadow_banned' => false,
            'shadow_banned_at' => null,
            'warning_level' => 0,
            'latest_warning_message' => null,
            'latest_warning_at' => null,
        ]);

        UserGreylistLog::create([
            'user_id' => $user->id,
            'admin_id' => $admin->id,
            'action' => 'greylist_remove',
            'warning_level' => 0,
            'reason' => 'Dipulihkan oleh Admin',
            'message' => "Akun {$user->name} telah dipulihkan dan dihapus dari Daftar Abu-Abu.",
        ]);

        \App\Models\ActivityLog::record(
            $admin,
            'greylist_remove',
            "Admin {$admin->name} memulihkan akun {$user->name} ({$user->role}) dan menghapusnya dari Daftar Abu-Abu.",
            [
                'target_user_id' => $user->id,
                'role'           => $user->role,
            ]
        );

        session()->flash('success', "Akun {$user->name} telah dipulihkan dan status normal kembali.");
    }

    public function render()
    {
        $admin = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        $baseQuery = User::query()
            ->whereIn('role', ['mitra', 'customer'])
            ->where(function ($q) {
                $q->where('is_greylisted', true)
                  ->orWhere('is_shadow_banned', true)
                  ->orWhere('warning_level', '>', 0);
            });

        // Filter kota jika admin wilayah
        if (! $isSuperAdmin) {
            $managedCityIds = $admin ? $admin->getAdminCityIds() : [];
            if (!empty($managedCityIds)) {
                $baseQuery->whereIn('city_id', $managedCityIds);
            } elseif ($admin && $admin->role === 'admin') {
                $baseQuery->whereRaw('1 = 0');
            }
        }

        // Stats
        $totalGreylist = (clone $baseQuery)->where('is_greylisted', true)->count();
        $totalShadowBanned = (clone $baseQuery)->where('is_shadow_banned', true)->count();
        $totalWarning = (clone $baseQuery)->where('warning_level', '>', 0)->count();
        $totalMitra = (clone $baseQuery)->where('role', 'mitra')->count();
        $totalCustomer = (clone $baseQuery)->where('role', 'customer')->count();

        // Query with filters
        $query = (clone $baseQuery)->with(['city', 'greylistLogs.admin']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->roleFilter !== 'all') {
            $query->where('role', $this->roleFilter);
        }

        if ($this->statusFilter === 'shadow_banned') {
            $query->where('is_shadow_banned', true);
        } elseif ($this->statusFilter === 'warning_only') {
            $query->where('warning_level', '>', 0)->where('is_shadow_banned', false);
        } elseif ($this->statusFilter === 'active_greylist') {
            $query->where('is_greylisted', true);
        }

        $users = $query->latest('greylisted_at')->paginate(10);

        // Candidate users for Add Modal
        $candidateUsers = [];
        if ($this->showAddModal && strlen($this->userSearch) >= 2) {
            $candQuery = User::whereIn('role', ['mitra', 'customer'])
                ->where('is_greylisted', false)
                ->where('is_shadow_banned', false)
                ->where('warning_level', 0)
                ->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->userSearch . '%')
                      ->orWhere('email', 'like', '%' . $this->userSearch . '%')
                      ->orWhere('phone', 'like', '%' . $this->userSearch . '%');
                });

            if (! $isSuperAdmin) {
                $managedCityIds = $admin ? $admin->getAdminCityIds() : [];
                if (!empty($managedCityIds)) {
                    $candQuery->whereIn('city_id', $managedCityIds);
                } elseif ($admin && $admin->role === 'admin') {
                    $candQuery->whereRaw('1 = 0');
                }
            }

            $candidateUsers = $candQuery->limit(8)->get();
        }

        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.admin.partners.greylist', [
            'users' => $users,
            'totalGreylist' => $totalGreylist,
            'totalShadowBanned' => $totalShadowBanned,
            'totalWarning' => $totalWarning,
            'totalMitra' => $totalMitra,
            'totalCustomer' => $totalCustomer,
            'candidateUsers' => $candidateUsers,
        ])->layout($layout);
    }
}
