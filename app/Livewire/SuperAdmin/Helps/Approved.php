<?php

namespace App\Livewire\SuperAdmin\Helps;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Help;

#[Layout('layouts.superadmin')]
class Approved extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function approveHelp($id)
    {
        $help = Help::findOrFail($id);
        $help->update(['status' => 'active']);
        session()->flash('message', 'Bantuan berhasil disetujui');
    }

    public function rejectHelp($id)
    {
        $help = Help::findOrFail($id);
        $help->update(['status' => 'rejected']);
        session()->flash('message', 'Bantuan ditolak');
    }

    public function render()
    {
        $helps = Help::query()
            ->with(['customer', 'mitra', 'city'])
            ->whereIn('status', ['active', 'menunggu_mitra', 'taken', 'memperoleh_mitra', 'sedang_diproses', 'in_progress', 'waiting_customer_confirmation', 'selesai', 'completed'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.superadmin.helps.approved', compact('helps'));
    }
}

