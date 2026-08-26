<?php

namespace App\Livewire\SuperAdmin\Users;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\City;
use Illuminate\Validation\Rule;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $perPage = 10;
    public $selectedUser = null;

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
    public $rt = null;
    public $rw = null;
    public $kelurahan = null;
    public $kecamatan = null;
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

    public function viewUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            session()->flash('error', 'User not found');
            return;
        }
        $this->selectedUser = $user;
        $this->showViewModal = true;
    }


    public function editUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            session()->flash('error', 'User not found');
            return;
        }
        $this->selectedUser = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->role;
        $this->status = $user->status ?? 'inactive';
        $this->verified = (bool) ($user->verified ?? false);
        $this->city_id = $user->city_id;
        $this->managed_city_ids = $user->managedCities->pluck('id')->toArray();
        $this->address = $user->address;
        $this->nik = $user->nik;
        $this->place_of_birth = $user->place_of_birth;
        $this->date_of_birth = optional($user->date_of_birth)?->format('Y-m-d');
        $this->gender = $user->gender;
        $this->rt = $user->rt;
        $this->rw = $user->rw;
        $this->kelurahan = $user->kelurahan;
        $this->kecamatan = $user->kecamatan;
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
        $this->rt = null;
        $this->rw = null;
        $this->kelurahan = null;
        $this->kecamatan = null;
        $this->province = null;
        $this->religion = null;
        $this->marital_status = null;
        $this->occupation = null;
        $this->password = null;
    }

    public function saveUser()
    {
        // build validation rules and handle unique email on update
        $emailRules = ['required', 'email', 'max:255'];
        if ($this->selectedUser) {
            $emailRules[] = Rule::unique('users', 'email')->ignore($this->selectedUser->id);
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
            'nik' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'rt' => 'nullable|integer|min:1|max:999',
            'rw' => 'nullable|integer|min:1|max:999',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Laki-laki,Perempuan',
            'occupation' => 'nullable|string|max:150',
        ];

        if ($this->selectedUser) {
            $rules['password'] = 'nullable|string|min:8';
        } else {
            $rules['password'] = 'required|string|min:8';
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'status' => $this->status,
            'verified' => $this->verified,
            'city_id' => $this->city_id,
            'address' => $this->address,
            'nik' => $this->nik,
            'place_of_birth' => $this->place_of_birth,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'rt' => $this->rt,
            'rw' => $this->rw,
            'kelurahan' => $this->kelurahan,
            'kecamatan' => $this->kecamatan,
            'province' => $this->province,
            'religion' => $this->religion,
            'marital_status' => $this->marital_status,
            'occupation' => $this->occupation,
        ];

        if ($this->selectedUser) {
            $user = User::find($this->selectedUser->id);
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
            
            // Sync managed cities for admin role
            if ($this->role === 'admin') {
                $user->managedCities()->sync($this->managed_city_ids ?? []);
            } else {
                $user->managedCities()->sync([]);
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
            
            // Sync managed cities for admin role
            if ($this->role === 'admin') {
                $user->managedCities()->sync($this->managed_city_ids ?? []);
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
        $this->confirmingDeleteId = null;
        $this->userToDelete = null;
        $this->adminPassword = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        $currentUser = auth()->user();
        $isSuperAdmin = in_array($currentUser->role ?? '', ['super_admin', 'superadmin']);

        $query = User::with(['city', 'managedCities'])
            ->withMax('helps', 'updated_at')
            ->withMax('takenHelps', 'updated_at');

        if (! $isSuperAdmin) {
            $managedCityIds = $currentUser->managedCities()->pluck('cities.id')->toArray();
            if (!empty($managedCityIds)) {
                $query->whereIn('city_id', $managedCityIds);
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
            ->when($this->roleFilter, function ($q) {
                $q->where('role', $this->roleFilter);
            }, function ($q) {
                // default: show only mitra and customer
                $q->whereIn('role', ['mitra', 'customer']);
            })
            ->latest()
            ->paginate($this->perPage);

        $cities = City::orderBy('name')->get();
        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.superadmin.users.index', compact('users', 'cities'))->layout($layout);
    }
}

