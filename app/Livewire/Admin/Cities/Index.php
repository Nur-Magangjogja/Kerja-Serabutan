<?php

namespace App\Livewire\Admin\Cities;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\City;
use App\Models\User;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $cityId;

    // Form fields
    public $name = '';
    public $province = '';
    public $admin_id = '';
    public $is_active = true;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset(['name', 'province', 'admin_id', 'is_active', 'cityId', 'editMode']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $city = City::findOrFail($id);
        $this->cityId = $city->id;
        $this->name = $city->name;
        $this->province = $city->province;
        $this->admin_id = $city->admin_id;
        $this->is_active = $city->is_active;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'admin_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        if ($this->editMode) {
            City::findOrFail($this->cityId)->update($validated);
        } else {
            City::create($validated);
        }

        $this->showModal = false;
        $this->reset(['name', 'province', 'admin_id', 'is_active', 'cityId', 'editMode']);
    }

    public function deleteCity($id)
    {
        City::findOrFail($id)->delete();
    }

    public function render()
    {
        $query = City::query()->with('admin');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('province', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.admin.cities.index', [
            'cities' => $query->latest()->paginate(15),
            'admins' => User::where('role', 'admin')->get(),
        ]);
    }
}
