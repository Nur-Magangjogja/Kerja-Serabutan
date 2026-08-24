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

    // form fields
    public $name = '';
    public $email = '';
    public $phone = '';
    public $role = 'admin';
    public $status = 'active';
    public $verified = true;
    public $city_id = null;
    public $managed_city_ids = []; 
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
        $user = User::with(['city', 'managedCities'])->find($id);
        if (!$user) {
            session()->flash('error', 'Admin tidak ditemukan');
            return;
        }
        $this->selectedUser = $user;
        $this->showViewModal = true;
    }

    public function editUser($id)
    {
        $user = User::with('managedCities')->find($id);
        if (!$user) {
            session()->flash('error', 'Admin tidak ditemukan');
            return;
        }
        $this->selectedUser = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->role ?? 'admin';
        $this->status = $user->status ?? 'active';
        $this->verified = (bool) ($user->verified ?? true);
        $this->city_id = $user->city_id;
        $this->managed_city_ids = $user->managedCities->pluck('id')->toArray();
        if ($this->city_id && !in_array($this->city_id, $this->managed_city_ids)) {
            $this->managed_city_ids[] = $this->city_id;
        }
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
        $this->password = '';
        $this->adminPassword = '';
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
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->role = 'admin';
        $this->status = 'active';
        $this->verified = true;
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
        $this->adminPassword = '';
        $this->userToDelete = null;
    }

    public function saveUser()
    {
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
            $rules['adminPassword'] = 'required|string';
        } else {
            $rules['password'] = 'required|string|min:8';
        }

        $this->validate($rules, [
            'adminPassword.required' => 'Kata sandi Superadmin wajib dimasukkan untuk mengonfirmasi perubahan data admin.',
        ]);

        if ($this->selectedUser) {
            if (!\Illuminate\Support\Facades\Hash::check($this->adminPassword, auth()->user()->password)) {
                $this->addError('adminPassword', 'Kata sandi Superadmin yang Anda masukkan salah. Perubahan data admin dibatalkan.');
                return;
            }
        }

        // Compute primary city_id
        $primaryCityId = $this->city_id;
        if (!$primaryCityId && !empty($this->managed_city_ids)) {
            $primaryCityId = $this->managed_city_ids[0];
        }
        if ($primaryCityId && !in_array($primaryCityId, $this->managed_city_ids)) {
            $this->managed_city_ids[] = $primaryCityId;
        }

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role ?: 'admin',
            'status' => $this->status ?: 'active',
            'verified' => (bool) $this->verified,
            'city_id' => $primaryCityId,
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

        if ($this->verified) {
            $data['email_verified_at'] = now();
        } else {
            $data['email_verified_at'] = null;
        }

        if ($this->selectedUser) {
            $user = User::find($this->selectedUser->id);
            if (!$user) {
                session()->flash('error', 'Admin tidak ditemukan');
                return;
            }
            if (!empty($this->password)) {
                $data['password'] = bcrypt($this->password);
            }
            $user->update($data);

            if ($this->role === 'admin') {
                $user->managedCities()->sync($this->managed_city_ids ?? []);
            } else {
                $user->managedCities()->sync([]);
            }

            \Illuminate\Support\Facades\Log::info("[SuperAdmin] Superadmin #" . auth()->id() . " updated admin #{$user->id} ({$user->name}) with password confirmation.");

            session()->flash('message', 'Data admin ' . $user->name . ' berhasil diperbarui.');
        } else {
            $data['password'] = bcrypt($this->password);
            $user = User::create($data);

            if ($this->role === 'admin') {
                $user->managedCities()->sync($this->managed_city_ids ?? []);
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
        $users = User::with(['city', 'managedCities'])
            ->where('role', 'admin')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate($this->perPage);

        $cities = City::orderBy('name')->get();

        return view('livewire.superadmin.users.admin-users', compact('users', 'cities'));
    }
}
