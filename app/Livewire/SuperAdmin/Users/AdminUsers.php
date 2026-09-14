<?php

namespace App\Livewire\SuperAdmin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\City;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

#[Layout('layouts.superadmin')]
class AdminUsers extends Component
{
    use WithPagination;

    public $search = '';
    public $title = 'Manajemen Admin';
    public $breadcrumb = 'Manajemen Admin';
    public $roleFilter = 'admin';
    public $perPage = 10;
    public $selectedUser = null;
    public $selectedUserId = null;

    // form fields
    public $name = '';
    public $email = '';
    public $phone = '';
    public $role = 'admin';
    public $status = 'active';
    public $verified = true;
    public $district_id = null;
    public $managed_district_ids = []; 
    public $managed_city_ids = [];
    public $districtSearch = '';
    public $cityFilter = 'all';
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
    public $confirmingDeleteId = null;
    public $userToDelete = null;
    public $adminPassword = '';

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

    public function toggleVerified($id)
    {
        $user = User::find($id);
        if (!$user) {
            session()->flash('error', 'Admin tidak ditemukan');
            return;
        }

        $newVerified = !$user->verified;
        $user->verified = $newVerified;
        if ($newVerified) {
            $user->email_verified_at = now();
            if ($user->status === 'inactive') {
                $user->status = 'active';
            }
        } else {
            $user->email_verified_at = null;
        }
        $user->save();

        session()->flash('message', 'Status verifikasi admin ' . $user->name . ' berhasil diperbarui.');
    }

    public function toggleStatus($id)
    {
        $user = User::find($id);
        if (!$user) {
            session()->flash('error', 'Admin tidak ditemukan');
            return;
        }

        $user->status = ($user->status === 'active') ? 'inactive' : 'active';
        $user->save();

        session()->flash('message', 'Status akun admin ' . $user->name . ' berhasil diubah menjadi ' . $user->status . '.');
    }

    public function viewUser($id)
    {
        $user = User::with(['district.city', 'managedDistricts.city', 'city'])->find($id);
        if (!$user) {
            session()->flash('error', 'Admin tidak ditemukan');
            return;
        }
        $this->selectedUser = $user;
        $this->selectedUserId = $user->id;
        $this->showViewModal = true;
    }

    public function editUser($id)
    {
        $user = User::with(['managedDistricts.city', 'managedCities', 'district'])->find($id);
        if (!$user) {
            session()->flash('error', 'Admin tidak ditemukan');
            return;
        }
        $this->selectedUser = $user;
        $this->selectedUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->role ?? 'admin';
        $this->status = $user->status ?? 'active';
        $this->verified = (bool) ($user->verified ?? true);
        
        $managedIds = $user->managedDistricts->pluck('id')->map(fn($did) => (int)$did)->toArray();
        if (empty($managedIds) && $user->district_id) {
            $managedIds = [(int)$user->district_id];
        }
        $this->managed_district_ids = $managedIds;
        $this->district_id = !empty($managedIds) ? $managedIds[0] : null;

        $managedCityIds = $user->managedCities->pluck('id')->map(fn($cid) => (int)$cid)->toArray();
        if (empty($managedCityIds) && $user->city_id) {
            $managedCityIds = [(int)$user->city_id];
        }
        $this->managed_city_ids = $managedCityIds;

        $this->address = $user->address;
        $this->nik = $user->nik;
        $this->place_of_birth = $user->place_of_birth;
        $this->date_of_birth = optional($user->date_of_birth)?->format('Y-m-d');
        $this->gender = $user->gender;
        $this->province = $user->province;
        $this->religion = $user->religion;
        $this->marital_status = $user->marital_status;
        $this->occupation = $user->occupation;
        $this->password = '';
        $this->adminPassword = '';
        $this->districtSearch = '';
        $this->cityFilter = 'all';
        $this->resetErrorBag();
        $this->showEditModal = true;
    }

    public function confirmDelete($id)
    {
        $user = User::find($id);
        if (!$user) {
            session()->flash('error', 'Admin tidak ditemukan');
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
        $this->role = 'admin';
        $this->status = 'active';
        $this->verified = true;
        $this->district_id = null;
        $this->managed_district_ids = [];
        $this->managed_city_ids = [];
        $this->districtSearch = '';
        $this->cityFilter = 'all';
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
        $this->adminPassword = '';
        $this->userToDelete = null;
    }

    public function toggleDistrict($id)
    {
        $id = (int) $id;
        $current = array_values(array_unique(array_filter(array_map('intval', (array)($this->managed_district_ids ?? [])))));
        
        if (in_array($id, $current, true)) {
            $this->managed_district_ids = array_values(array_diff($current, [$id]));
        } else {
            $current[] = $id;
            $this->managed_district_ids = array_values($current);
        }
        
        $this->syncManagedCities();
    }

    public function removeDistrict($id)
    {
        $id = (int) $id;
        $current = array_values(array_unique(array_filter(array_map('intval', (array)($this->managed_district_ids ?? [])))));
        $this->managed_district_ids = array_values(array_diff($current, [$id]));
        $this->syncManagedCities();
    }

    public function selectAllFilteredDistricts()
    {
        if ($this->cityFilter === 'all' && empty($this->districtSearch)) {
            session()->flash('warning', 'Silakan pilih Kota/Kabupaten terlebih dahulu untuk memilih semua kecamatan di kota tersebut.');
            return;
        }

        $query = \App\Models\District::where('is_active', true);
        if ($this->cityFilter !== 'all' && is_numeric($this->cityFilter)) {
            $query->where('city_id', (int) $this->cityFilter);
        }
        if (!empty($this->districtSearch)) {
            $dq = trim($this->districtSearch);
            $query->where(function($b) use ($dq) {
                $b->where('name', 'like', "%{$dq}%")
                  ->orWhereHas('city', fn($cq) => $cq->where('name', 'like', "%{$dq}%"));
            });
        }
        $matchingIds = $query->limit(300)->pluck('id')->map(fn($id) => (int)$id)->toArray();
        $current = array_values(array_unique(array_filter(array_map('intval', (array)($this->managed_district_ids ?? [])))));
        $this->managed_district_ids = array_values(array_unique(array_merge($current, $matchingIds)));
        $this->syncManagedCities();
    }

    public function deselectAllFilteredDistricts()
    {
        $query = \App\Models\District::where('is_active', true);
        if ($this->cityFilter !== 'all' && is_numeric($this->cityFilter)) {
            $query->where('city_id', (int) $this->cityFilter);
        }
        if (!empty($this->districtSearch)) {
            $dq = trim($this->districtSearch);
            $query->where(function($b) use ($dq) {
                $b->where('name', 'like', "%{$dq}%")
                  ->orWhereHas('city', fn($cq) => $cq->where('name', 'like', "%{$dq}%"));
            });
        }
        $matchingIds = $query->limit(300)->pluck('id')->map(fn($id) => (int)$id)->toArray();
        $current = array_values(array_unique(array_filter(array_map('intval', (array)($this->managed_district_ids ?? [])))));
        $this->managed_district_ids = array_values(array_diff($current, $matchingIds));
        $this->syncManagedCities();
    }

    public function clearAllDistricts()
    {
        $this->managed_district_ids = [];
        $this->managed_city_ids = [];
        $this->district_id = null;
    }

    protected function syncManagedCities()
    {
        $normalizedIds = array_values(array_unique(array_filter(array_map('intval', (array)($this->managed_district_ids ?? [])))));
        if (empty($normalizedIds)) {
            $this->managed_city_ids = [];
            $this->district_id = null;
            return;
        }

        $districts = \App\Models\District::whereIn('id', $normalizedIds)->get();
        $this->managed_city_ids = $districts->pluck('city_id')->filter()->unique()->values()->map(fn($id) => (int)$id)->toArray();
        $this->district_id = $normalizedIds[0] ?? null;
    }

    public function saveUser()
    {
        $userId = $this->selectedUserId ?? (is_array($this->selectedUser) ? ($this->selectedUser['id'] ?? null) : ($this->selectedUser->id ?? null));

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
            'district_id' => 'nullable|exists:districts,id',
            'managed_district_ids' => 'nullable|array',
            'managed_district_ids.*' => 'exists:districts,id',
            'nik' => ['nullable', 'string', 'max:50', Rule::unique('users', 'nik')->ignore($userId)],
            'address' => 'nullable|string|max:1000',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Laki-laki,Perempuan',
            'occupation' => 'nullable|string|max:150',
        ];

        if ($userId) {
            $rules['password'] = 'nullable|string|min:8';
            $rules['adminPassword'] = 'required|string';
        } else {
            $rules['password'] = 'required|string|min:8';
        }

        try {
            $this->validate($rules, [
                'adminPassword.required' => 'Kata sandi Superadmin wajib dimasukkan untuk mengonfirmasi perubahan data admin.',
                'password.required'      => 'Password admin baru wajib diisi minimal 8 karakter.',
                'password.min'           => 'Password minimal terdiri dari 8 karakter.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($e->validator->errors()->has('adminPassword')) {
                $this->dispatch('focus-superadmin-password');
            }
            throw $e;
        }

        if ($userId) {
            if (!\Illuminate\Support\Facades\Hash::check($this->adminPassword, auth()->user()->password)) {
                $this->addError('adminPassword', 'Kata sandi Superadmin yang Anda masukkan salah. Perubahan data admin dibatalkan.');
                $this->dispatch('focus-superadmin-password');
                return;
            }
        }

        // Process managed districts and deduce parent cities
        $managedDistrictIds = array_values(array_unique(array_filter(array_map('intval', (array)($this->managed_district_ids ?? [])))));
        
        $selectedDistricts = !empty($managedDistrictIds) ? \App\Models\District::with('city')->whereIn('id', $managedDistrictIds)->get() : collect();
        $derivedCityIds = $selectedDistricts->pluck('city_id')->filter()->unique()->values()->map(fn($id) => (int)$id)->toArray();
        $managedCityIds = array_values(array_unique(array_filter(array_merge(
            array_map('intval', (array)($this->managed_city_ids ?? [])),
            $derivedCityIds
        ))));

        $primaryDistrict = $selectedDistricts->first();
        $primaryDistrictId = $primaryDistrict ? $primaryDistrict->id : null;
        $primaryCityId = $primaryDistrict ? $primaryDistrict->city_id : (!empty($managedCityIds) ? $managedCityIds[0] : null);
        $primaryCity = $primaryCityId ? City::find($primaryCityId) : null;
        $primaryCityName = $primaryDistrict?->city?->name ?: $primaryCity?->name;
        $primaryProvince = $primaryDistrict?->city?->province ?: $primaryCity?->province;
        $primaryKecamatan = $primaryDistrict?->name;

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role ?: 'admin',
            'status' => $this->status ?: 'active',
            'verified' => true,
            'email_verified_at' => now(),
            'district_id' => $primaryDistrictId,
            'kecamatan' => $primaryKecamatan,
            'city_id' => $primaryCityId,
            'city_name' => $primaryCityName,
            'city' => $primaryCityName,
            'address' => $this->address,
            'nik' => $this->nik,
            'place_of_birth' => $this->place_of_birth,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'province' => $this->province ?? $primaryProvince,
            'religion' => $this->religion,
            'marital_status' => $this->marital_status,
            'occupation' => $this->occupation,
        ];

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                session()->flash('error', 'Admin tidak ditemukan');
                return;
            }
            if (!empty($this->password)) {
                $data['password'] = bcrypt($this->password);
            }
            $user->update($data);

            if ($this->role === 'admin') {
                $user->managedDistricts()->sync($managedDistrictIds);
                $user->managedCities()->sync($managedCityIds);
            } else {
                $user->managedDistricts()->sync([]);
                $user->managedCities()->sync([]);
            }

            \Illuminate\Support\Facades\Log::info("[SuperAdmin] Superadmin #" . auth()->id() . " updated admin #{$user->id} ({$user->name}) with password confirmation.");

            session()->flash('message', 'Data admin ' . $user->name . ' berhasil diperbarui.');
        } else {
            $data['password'] = bcrypt($this->password);
            $user = User::create($data);

            try {
                \App\Models\UserBalance::firstOrCreate(['user_id' => $user->id], ['balance' => 0.00]);
            } catch (\Throwable $e) {
                // ignore
            }

            if ($this->role === 'admin') {
                $user->managedDistricts()->sync($managedDistrictIds);
                $user->managedCities()->sync($managedCityIds);
            }

            session()->flash('message', 'Admin baru ' . $user->name . ' berhasil dibuat dan terverifikasi.');
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
            'adminPassword.required' => 'Kata sandi Superadmin wajib dimasukkan untuk mengonfirmasi penghapusan admin.',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($this->adminPassword, auth()->user()->password)) {
            $this->addError('adminPassword', 'Kata sandi Superadmin salah. Penghapusan admin dibatalkan.');
            return;
        }

        $user = User::find($this->confirmingDeleteId);
        if (!$user) {
            session()->flash('error', 'Admin tidak ditemukan');
            $this->closeModal();
            return;
        }

        if ($user->id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            $this->closeModal();
            return;
        }

        $userName = $user->name;
        $user->managedDistricts()->sync([]);
        $user->managedCities()->sync([]);
        $user->delete();

        \Illuminate\Support\Facades\Log::info("[SuperAdmin] Superadmin #" . auth()->id() . " deleted admin #{$user->id} ({$userName}) with password confirmation.");

        session()->flash('message', 'Admin ' . $userName . ' berhasil dihapus.');
        $this->closeModal();
        $this->resetPage();
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showViewModal = false;
        $this->showConfirmDelete = false;
        $this->confirmingDeleteId = null;
        $this->userToDelete = null;
        $this->adminPassword = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = User::with(['district.city', 'managedDistricts.city', 'city'])
            ->where('role', 'admin');

        $currentUser = auth()->user();
        $territory = $currentUser ? $currentUser->getActiveSuperadminTerritory() : ['type' => 'all', 'id' => null];
        if ($territory['type'] === 'district' && $territory['id']) {
            $dId = (int) $territory['id'];
            $query->where(function ($q) use ($dId) {
                $q->where('district_id', $dId)
                  ->orWhereHas('managedDistricts', fn($dq) => $dq->where('districts.id', $dId));
            });
        } elseif ($territory['type'] === 'city' && $territory['id']) {
            $cId = (int) $territory['id'];
            $districtIds = $currentUser ? $currentUser->getEffectiveSuperadminDistrictIds() : [];
            $query->where(function ($q) use ($cId, $districtIds) {
                $q->where('city_id', $cId)
                  ->orWhereHas('managedCities', fn($cq) => $cq->where('cities.id', $cId));
                if (!empty($districtIds)) {
                    $q->orWhereIn('district_id', $districtIds)
                      ->orWhereHas('managedDistricts', fn($dq) => $dq->whereIn('districts.id', $districtIds));
                }
            });
        }

        $users = $query
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate($this->perPage);

        $cities = City::withCount('districts')->orderBy('name')->get();

        // Load districts for modal selection (with search and city filter)
        $districtQuery = \App\Models\District::with('city')->where('is_active', true);
        if ($this->cityFilter !== 'all' && is_numeric($this->cityFilter)) {
            $districtQuery->where('city_id', (int) $this->cityFilter);
        }
        if (!empty($this->districtSearch)) {
            $dq = trim($this->districtSearch);
            $districtQuery->where(function($b) use ($dq) {
                $b->where('name', 'like', "%{$dq}%")
                  ->orWhereHas('city', fn($cq) => $cq->where('name', 'like', "%{$dq}%"));
            });
        }
        
        $limit = ($this->cityFilter === 'all' && empty($this->districtSearch)) ? 100 : 300;
        $districts = $districtQuery->orderBy('name')->limit($limit)->get();

        // Ensure selected districts are always available in view & preview chips
        $selectedDistrictsList = collect();
        if (!empty($this->managed_district_ids)) {
            $normalizedIds = array_values(array_unique(array_filter(array_map('intval', (array)$this->managed_district_ids))));
            $selectedDistrictsList = \App\Models\District::with('city')
                ->whereIn('id', $normalizedIds)
                ->orderBy('name')
                ->get();
            
            // Also merge into $districts so selected items are never missing in grid
            $districts = $districts->merge($selectedDistrictsList)->unique('id')->values();
        }

        return view('livewire.superadmin.users.admin-users', compact('users', 'cities', 'districts', 'selectedDistrictsList'));
    }
}
