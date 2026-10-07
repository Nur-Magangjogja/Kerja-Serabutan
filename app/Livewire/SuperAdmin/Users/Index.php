<?php

namespace App\Livewire\SuperAdmin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\City;
use App\Models\District;
use App\Models\Province;
use App\Services\Territory\ProfileTerritoryMigrationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $perPage = 10;
    public $selectedUser = null;
    public $selectedUserId = null;

    // form fields
    public $name;
    public $email;
    public $phone;
    public $role = 'customer';
    public $status = 'inactive';
    public $verified = false;
    public $city_id = null;
    public $managed_city_ids = []; // Array for multiple cities
    public $address = null;
    public $nik = null;
    public $place_of_birth = null;
    public $date_of_birth = null;
    public $gender = null;
    public $province = null;
    public $religion = null;
    public $marital_status = null;
    public $occupation = null;
    public $password = null;

    // modal flags
    public $showViewModal = false;
    public $showEditModal = false;
    public $showCreateModal = false;
    public $showConfirmDelete = false;
    public $showMigrationModal = false;
    public $confirmingDeleteId = null;
    public $userToDelete = null;
    public $adminPassword = '';

    // view modal tabs & audit state
    public $activeModalTab = 'profile'; // 'profile' or 'audit'
    public $auditFilter = 'all'; // 'all', 'help', 'cancel_dispute', 'report', 'discipline', 'financial'
    public $auditPage = 1;

    // migration modal state
    public $migrationUserId = null;
    public $migrationUser = null;
    public $migrationProvinceId = null;
    public $migrationCityId = null;
    public $migrationDistrictId = null;
    public $migrationReason = '';
    public $migrationAvailableProvinces = [];
    public $migrationAvailableCities = [];
    public $migrationAvailableDistricts = [];


    protected $listeners = [
        'superadmin-territory-changed' => '$refresh',
        'admin-district-changed'       => '$refresh',
        'admin-city-changed'           => '$refresh',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedRoleFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function canManageTargetUser(?User $target): bool
    {
        if (!$target) {
            return false;
        }

        $actor = auth()->user();
        if (!$actor) {
            return false;
        }

        $isSuperAdmin = in_array($actor->role ?? '', ['super_admin', 'superadmin'], true);
        if ($isSuperAdmin) {
            return true;
        }

        if ($actor->role !== 'admin') {
            return false;
        }

        // Admin Wilayah can only manage customer and mitra accounts
        if (!in_array($target->role, ['customer', 'mitra'], true)) {
            return false;
        }

        // Flush actor instance cache to guarantee fresh DB territory assignment
        $actor->flushInstanceCache();

        $authService = app(\App\Services\Territory\AdminTerritoryAuthorizationService::class);
        $authService->clearCache();

        return $authService->canAccessTerritory(
            $actor,
            $target->district_id ? (int)$target->district_id : null,
            $target->city_id ? (int)$target->city_id : null
        );
    }

    public function toggleVerified($id)
    {
        $user = User::find($id);
        if (!$user) {
            session()->flash('error', 'User not found');
            return;
        }

        if (!$this->canManageTargetUser($user)) {
            session()->flash('error', 'Pengguna berada di luar wilayah kewenangan Anda.');
            return;
        }

        $user->verified = !$user->verified;
        $user->save();

        session()->flash('message', 'User verification status updated.');
    }

    public function toggleStatus($id)
    {
        $user = User::find($id);
        if (!$user) {
            session()->flash('error', 'User not found');
            return;
        }

        if (!$this->canManageTargetUser($user)) {
            session()->flash('error', 'Pengguna berada di luar wilayah kewenangan Anda.');
            return;
        }

        $user->status = ($user->status === 'active') ? 'inactive' : 'active';
        $user->save();

        session()->flash('message', 'User status updated.');
    }

    public function viewUser($id)
    {
        $user = User::with(['district', 'city', 'managedDistricts'])->find($id);
        if (!$user) {
            session()->flash('error', 'User not found');
            return;
        }

        if (!$this->canManageTargetUser($user)) {
            session()->flash('error', 'Pengguna berada di luar wilayah kewenangan Anda.');
            return;
        }

        $this->selectedUser = $user;
        $this->selectedUserId = $user->id;
        $this->activeModalTab = 'profile';
        $this->auditFilter = 'all';
        $this->auditPage = 1;
        $this->showViewModal = true;
    }

    public function setModalTab(string $tab)
    {
        $this->activeModalTab = in_array($tab, ['profile', 'audit'], true) ? $tab : 'profile';
        if ($this->activeModalTab === 'audit') {
            $this->auditPage = 1;
        }
    }

    public function setAuditFilter(string $filter)
    {
        $this->auditFilter = $filter;
        $this->auditPage = 1;
    }

    public function setTimelineFilter(string $filter)
    {
        $this->setAuditFilter($filter);
    }

    public function nextAuditPage()
    {
        $this->auditPage++;
    }

    public function previousAuditPage()
    {
        if ($this->auditPage > 1) {
            $this->auditPage--;
        }
    }


    public function editUser($id)
    {
        $user = User::with(['district', 'city', 'managedDistricts'])->find($id);
        if (!$user) {
            session()->flash('error', 'User not found');
            return;
        }

        if (!$this->canManageTargetUser($user)) {
            session()->flash('error', 'Pengguna berada di luar wilayah kewenangan Anda.');
            return;
        }

        $this->selectedUser = $user;
        $this->selectedUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->role;
        $this->status = $user->status ?? 'inactive';
        $this->verified = (bool) ($user->verified ?? false);
        
        $this->managed_city_ids = [];
        $this->city_id = $user->city_id;
        $this->address = $user->address;
        $this->nik = $user->nik;
        $this->place_of_birth = $user->place_of_birth;
        $this->date_of_birth = optional($user->date_of_birth)?->format('Y-m-d');
        $this->gender = $user->gender;
        $this->province = $user->province;
        $this->religion = $user->religion;
        $this->marital_status = $user->marital_status;
        $this->occupation = $user->occupation;
        $this->adminPassword = '';
        $this->resetErrorBag();
        $this->showEditModal = true;
    }

    public function confirmDelete($id)
    {
        $user = User::find($id);
        if (!$user) {
            session()->flash('error', 'User tidak ditemukan.');
            return;
        }

        if (!$this->canManageTargetUser($user)) {
            session()->flash('error', 'Pengguna berada di luar wilayah kewenangan Anda.');
            return;
        }

        $this->confirmingDeleteId = $id;
        $this->userToDelete = $user;
        $this->adminPassword = '';
        $this->resetErrorBag();
        $this->showConfirmDelete = true;
    }

    public function openCreateModal()
    {
        $actor = auth()->user();
        if ($actor && $actor->role === 'admin') {
            session()->flash('error', 'Admin Wilayah tidak memiliki wewenang untuk membuat pengguna baru.');
            return;
        }

        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function resetForm()
    {
        $this->selectedUser = null;
        $this->selectedUserId = null;
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->role = 'customer';
        $this->status = 'inactive';
        $this->verified = false;
        $this->city_id = null;
        $this->managed_city_ids = [];
        $this->address = null;
        $this->nik = null;
        $this->place_of_birth = null;
        $this->date_of_birth = null;
        $this->gender = null;
        $this->province = null;
        $this->religion = null;
        $this->marital_status = null;
        $this->occupation = null;
        $this->password = null;
    }

    public function saveUser()
    {
        $userId = $this->selectedUserId ?? (is_array($this->selectedUser) ? ($this->selectedUser['id'] ?? null) : ($this->selectedUser->id ?? null));

        $actor = auth()->user();
        $isSuperAdmin = in_array($actor?->role ?? '', ['super_admin', 'superadmin'], true);

        // Security check for Admin Wilayah before any processing
        if (!$isSuperAdmin && $actor?->role === 'admin') {
            // Cannot create arbitrary new user accounts
            if (!$userId) {
                session()->flash('error', 'Admin Wilayah tidak memiliki wewenang untuk membuat pengguna baru.');
                return;
            }

            // Target must exist and be authorized at execution time (TOCTOU guard)
            $targetUser = User::find($userId);
            if (!$targetUser || !$this->canManageTargetUser($targetUser)) {
                session()->flash('error', 'Pengguna berada di luar wilayah kewenangan Anda.');
                return;
            }

            // Role escalation protection: Admin Wilayah can only maintain customer or mitra
            if (!in_array($this->role, ['customer', 'mitra'], true)) {
                $this->addError('role', 'Anda tidak memiliki wewenang untuk mengubah role menjadi Admin atau Super Admin.');
                return;
            }

            // Cannot assign or modify admin territory pivots
            $this->managed_city_ids = [];

            // If changing city_id, must remain inside admin's authorized parent cities
            if (!empty($this->city_id) && !in_array((int)$this->city_id, $actor->getAdminCityIds(), true)) {
                $this->addError('city_id', 'Kota berada di luar wilayah kewenangan Anda.');
                return;
            }
        }

        // build validation rules and handle unique email on update
        $emailRules = ['required', 'email', 'max:255'];
        if ($userId) {
            $emailRules[] = Rule::unique('users', 'email')->ignore($userId);
        } else {
            $emailRules[] = 'unique:users,email';
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => $emailRules,
            'phone' => 'nullable|string|max:30',
            'role' => 'required|string',
            'status' => 'required|in:active,inactive',
            'verified' => 'boolean',
            'city_id' => 'nullable|exists:cities,id',
            'managed_city_ids' => 'nullable|array',
            'managed_city_ids.*' => 'exists:cities,id',
            'nik' => ['nullable', 'string', 'max:50', Rule::unique('users', 'nik')->ignore($userId)],
            'province' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Laki-laki,Perempuan',
            'occupation' => 'nullable|string|max:150',
        ];

        if ($userId) {
            $rules['password'] = 'nullable|string|min:8';
        } else {
            $rules['password'] = 'required|string|min:8';
        }

        $this->validate($rules);

        $managedCityIds = array_values(array_unique(array_filter(array_map('intval', (array)($this->managed_city_ids ?? [])))));
        if ($this->role === 'admin') {
            $this->city_id = !empty($managedCityIds) ? $managedCityIds[0] : null;
        }

        $cityName = null;
        if ($this->city_id) {
            $cityRec = City::find($this->city_id);
            if ($cityRec) {
                $cityName = $cityRec->name;
                if (empty($this->province)) {
                    $this->province = $cityRec->province;
                }
            }
        }

        $isPrivileged = in_array($this->role, ['admin', 'super_admin']);
        $isVerified = $isPrivileged ? true : (bool)$this->verified;

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'status' => $this->status,
            'verified' => $isVerified,
            'email_verified_at' => $isVerified ? ($userId && ($user = User::find($userId)) && $user->email_verified_at ? $user->email_verified_at : now()) : null,
            'city_id' => $this->city_id,
            'city_name' => $cityName,
            'address' => $this->address,
            'nik' => $this->nik,
            'place_of_birth' => $this->place_of_birth,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'province' => $this->province,
            'religion' => $this->religion,
            'marital_status' => $this->marital_status,
            'occupation' => $this->occupation,
        ];

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                session()->flash('error', 'User not found');
                return;
            }
            // Only update password if provided
            if (!empty($this->password)) {
                $data['password'] = bcrypt($this->password);
            } else {
                unset($data['password']);
            }
            $user->update($data);
            
            // If user role was changed away from admin, clear any old territory assignments
            if ($this->role !== 'admin') {
                $user->managedDistricts()->sync([]);
                $user->managedCities()->sync([]);
                $user->flushInstanceCache();
                User::flushRequestCache($user->id);
            }
            
            session()->flash('message', 'User updated successfully');
        } else {
            // create new user with provided password
            $data['password'] = bcrypt($this->password);
            $user = User::create($data);
            
            try {
                \App\Models\UserBalance::firstOrCreate(['user_id' => $user->id], ['balance' => 0.00]);
            } catch (\Throwable $e) {
                // ignore
            }
            
            session()->flash('message', 'User created successfully');
        }

        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function deleteUser()
    {
        if (!$this->confirmingDeleteId) {
            return;
        }

        $this->validate([
            'adminPassword' => ['required', 'string'],
        ], [
            'adminPassword.required' => 'Kata sandi akun Anda wajib dimasukkan untuk konfirmasi penghapusan.',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($this->adminPassword, auth()->user()->password)) {
            $this->addError('adminPassword', 'Kata sandi yang Anda masukkan salah. Penghapusan akun dibatalkan.');
            return;
        }

        $user = User::find($this->confirmingDeleteId);
        if (!$user) {
            session()->flash('error', 'User tidak ditemukan.');
            $this->closeModal();
            return;
        }

        if ($user->id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            $this->closeModal();
            return;
        }

        if (!$this->canManageTargetUser($user)) {
            session()->flash('error', 'Pengguna berada di luar wilayah kewenangan Anda.');
            $this->closeModal();
            return;
        }

        $userName = $user->name;
        $user->delete();

        \Illuminate\Support\Facades\Log::info("[SuperAdmin] Superadmin #" . auth()->id() . " deleted user #{$user->id} ({$userName}) after password confirmation.");

        session()->flash('message', "User '{$userName}' berhasil dihapus dari sistem.");
        $this->closeModal();
        $this->resetPage();
    }

    /**
     * Close any open modal and reset relevant state
     */
    public function closeModal()
    {
        $this->resetForm();
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showViewModal = false;
        $this->showConfirmDelete = false;
        $this->showMigrationModal = false;
        $this->migrationUserId = null;
        $this->migrationUser = null;
        $this->migrationProvinceId = null;
        $this->migrationCityId = null;
        $this->migrationDistrictId = null;
        $this->migrationReason = '';
        $this->migrationAvailableProvinces = [];
        $this->migrationAvailableCities = [];
        $this->migrationAvailableDistricts = [];
        $this->confirmingDeleteId = null;
        $this->userToDelete = null;
        $this->adminPassword = '';
        $this->activeModalTab = 'profile';
        $this->auditFilter = 'all';
        $this->auditPage = 1;
        $this->resetErrorBag();
    }

    public function openMigrationModal($id)
    {
        $currentUser = auth()->user();
        $isSuperAdmin = in_array($currentUser?->role ?? '', ['super_admin', 'superadmin'], true);
        $isAdmin = ($currentUser?->role === 'admin');

        if (!$isSuperAdmin && !$isAdmin) {
            session()->flash('error', 'Anda tidak berwenang mengakses fitur ini.');
            return;
        }

        $user = User::with(['district', 'city'])->find($id);
        if (!$user) {
            session()->flash('error', 'Pengguna tidak ditemukan.');
            return;
        }

        if (!in_array($user->role, ['customer', 'mitra'], true)) {
            session()->flash('error', 'Hanya profil Customer dan Mitra yang dapat dimigrasikan.');
            return;
        }

        if ($isAdmin) {
            if (!$this->canManageTargetUser($user)) {
                session()->flash('error', 'Pengguna berada di luar wilayah kewenangan Anda.');
                return;
            }
        }

        $this->migrationUserId = $user->id;
        $this->migrationUser = $user;
        $this->migrationProvinceId = null;
        $this->migrationCityId = null;
        $this->migrationDistrictId = null;
        $this->migrationReason = '';
        $this->migrationAvailableCities = [];
        $this->migrationAvailableDistricts = [];
        $this->resetErrorBag();

        if ($isSuperAdmin) {
            $this->migrationAvailableProvinces = Province::orderBy('name')->get()->toArray();
        } else {
            $adminCityIds = $currentUser->getAdminCityIds();
            $adminCities = City::whereIn('id', $adminCityIds)->get();
            $provinceIds = $adminCities->pluck('province_id')->filter()->unique()->all();
            $provinceNames = $adminCities->pluck('province')->filter()->unique()->all();

            $this->migrationAvailableProvinces = Province::where(function ($q) use ($provinceIds, $provinceNames) {
                if (!empty($provinceIds)) {
                    $q->whereIn('id', $provinceIds);
                }
                if (!empty($provinceNames)) {
                    $q->orWhereIn('name', $provinceNames);
                }
            })->orderBy('name')->get()->toArray();

            // Auto-select if only 1 province managed by this admin
            if (count($this->migrationAvailableProvinces) === 1) {
                $this->migrationProvinceId = $this->migrationAvailableProvinces[0]['id'];
                $this->updatedMigrationProvinceId($this->migrationProvinceId);

                // Auto-select if only 1 city managed by this admin
                if (count($this->migrationAvailableCities) === 1) {
                    $this->migrationCityId = $this->migrationAvailableCities[0]['id'];
                    $this->updatedMigrationCityId($this->migrationCityId);
                }
            }
        }

        $this->showMigrationModal = true;
    }

    public function updatedMigrationProvinceId($provinceId)
    {
        $this->migrationCityId = null;
        $this->migrationDistrictId = null;
        $this->migrationAvailableDistricts = [];

        if (empty($provinceId)) {
            $this->migrationAvailableCities = [];
            return;
        }

        $currentUser = auth()->user();
        $isSuperAdmin = in_array($currentUser?->role ?? '', ['super_admin', 'superadmin'], true);

        $province = Province::find($provinceId);
        $provName = $province?->name;

        $cityQuery = City::where(function ($q) use ($provinceId, $provName) {
            $q->where('province_id', $provinceId);
            if ($provName) {
                $q->orWhere('province', $provName);
            }
        });

        if (!$isSuperAdmin && $currentUser?->role === 'admin') {
            $cityQuery->whereIn('id', $currentUser->getAdminCityIds());
        }

        $this->migrationAvailableCities = $cityQuery->orderBy('name')->get()->toArray();
    }

    public function updatedMigrationCityId($cityId)
    {
        $this->migrationDistrictId = null;

        if (empty($cityId)) {
            $this->migrationAvailableDistricts = [];
            return;
        }

        $currentUser = auth()->user();
        $isSuperAdmin = in_array($currentUser?->role ?? '', ['super_admin', 'superadmin'], true);

        $districtQuery = District::where('city_id', $cityId);
        if (!$isSuperAdmin && $currentUser?->role === 'admin') {
            $districtQuery->whereIn('id', $currentUser->getAdminDistrictIds());
        }

        $this->migrationAvailableDistricts = $districtQuery->orderBy('name')->get()->toArray();
    }

    public function submitMigration(ProfileTerritoryMigrationService $migrationService)
    {
        $this->resetErrorBag();

        $rules = [
            'migrationReason' => ['required', 'string', 'min:5'],
            'migrationCityId' => ['required', 'exists:cities,id'],
        ];

        if (!empty($this->migrationProvinceId)) {
            $rules['migrationProvinceId'] = ['required', 'exists:provinces,id'];
        }

        if (!empty($this->migrationAvailableDistricts)) {
            $rules['migrationDistrictId'] = ['required', 'exists:districts,id'];
        } else {
            $rules['migrationDistrictId'] = ['nullable', 'exists:districts,id'];
        }

        $this->validate($rules, [
            'migrationReason.required' => 'Alasan migrasi wilayah wajib diisi.',
            'migrationReason.min'      => 'Alasan migrasi minimal 5 karakter.',
            'migrationCityId.required' => 'Kota tujuan wajib dipilih.',
            'migrationDistrictId.required' => 'Kecamatan tujuan wajib dipilih.',
        ]);

        $user = User::find($this->migrationUserId);
        if (!$user) {
            $this->addError('migrationReason', 'Pengguna tidak ditemukan.');
            return;
        }

        if (!$this->canManageTargetUser($user)) {
            $this->addError('migrationReason', 'Pengguna berada di luar wilayah kewenangan Anda.');
            return;
        }

        try {
            $migrationService->migrate(
                actor: auth()->user(),
                targetUser: $user,
                newCityId: (int) $this->migrationCityId,
                newDistrictId: $this->migrationDistrictId ? (int) $this->migrationDistrictId : null,
                reason: $this->migrationReason,
                newProvinceId: $this->migrationProvinceId ? (int) $this->migrationProvinceId : null
            );

            session()->flash('message', "Wilayah profil untuk {$user->name} berhasil dimigrasikan.");
            $this->closeModal();
            $this->resetPage();
        } catch (ValidationException $e) {
            foreach ($e->errors() as $key => $messages) {
                foreach ($messages as $msg) {
                    $this->addError('migrationReason', $msg);
                }
            }
        } catch (AuthorizationException $e) {
            $this->addError('migrationReason', $e->getMessage());
        } catch (\Throwable $e) {
            $this->addError('migrationReason', 'Terjadi kesalahan saat memproses migrasi: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $currentUser = auth()->user();
        $isSuperAdmin = in_array($currentUser->role ?? '', ['super_admin', 'superadmin']);

        $query = User::with(['district', 'city', 'managedDistricts'])
            ->withMax('helps', 'updated_at')
            ->withMax('takenHelps', 'updated_at')
            ->where('verified', true)
            ->whereIn('role', ['mitra', 'customer']);

        if (! $isSuperAdmin) {
            $managedDistrictIds = $currentUser ? $currentUser->getEffectiveAdminDistrictIds() : [];
            if (!empty($managedDistrictIds)) {
                $query->whereIn('district_id', $managedDistrictIds);
            } elseif ($currentUser && $currentUser->role === 'admin') {
                $query->whereRaw('1 = 0');
            }
        } else {
            $territory = $currentUser ? $currentUser->getActiveSuperadminTerritory() : ['type' => 'all', 'id' => null];
            if ($territory['type'] === 'district' && $territory['id']) {
                $query->where('district_id', (int) $territory['id']);
            } elseif ($territory['type'] === 'city' && $territory['id']) {
                $cId = (int) $territory['id'];
                $districtIds = $currentUser ? $currentUser->getEffectiveSuperadminDistrictIds() : [];
                $query->where(function ($q) use ($cId, $districtIds) {
                    $q->where('city_id', $cId);
                    if (!empty($districtIds)) {
                        $q->orWhereIn('district_id', $districtIds);
                    }
                });
            }
        }

        $users = $query
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->when(in_array($this->roleFilter, ['mitra', 'customer']), function ($q) {
                $q->where('role', $this->roleFilter);
            })
            ->latest()
            ->paginate($this->perPage);

        $cities = City::getAllCached();
        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        $auditTimeline = null;
        if ($this->showViewModal && $this->selectedUser && $this->activeModalTab === 'audit') {
            $timelineService = app(\App\Services\UserAuditTimelineService::class);
            $auditTimeline = $timelineService->getTimelineForUser(
                $this->selectedUser,
                $currentUser,
                $this->auditPage,
                10,
                $this->auditFilter
            );
        }

        return view('livewire.superadmin.users.index', compact('users', 'cities', 'auditTimeline'))->layout($layout);
    }
}

