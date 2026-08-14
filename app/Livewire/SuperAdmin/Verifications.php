<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\User;

#[Layout('layouts.superadmin')]
class Verifications extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $perPage = 10;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function viewKtp($id)
    {
        session()->flash('message', 'View KTP #' . $id);
    }

    public function approveKtp($id)
    {
        // KTP verification feature will be implemented when database columns are added
        session()->flash('message', 'KTP verification feature is under development');
    }

    public function rejectKtp($id)
    {
        // KTP verification feature will be implemented when database columns are added
        session()->flash('message', 'KTP verification feature is under development');
    }

    public function render()
    {
        $verifications = User::query()
            ->where('role', 'mitra')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate($this->perPage);

        return view('superadmin.verifications', compact('verifications'));
    }
}
