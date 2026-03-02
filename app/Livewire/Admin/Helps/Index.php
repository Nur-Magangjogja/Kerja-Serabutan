<?php

namespace App\Livewire\Admin\Helps;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Help;

#[Layout('layouts.blank')]
class Index extends Component
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

    public function viewHelp($id)
    {
        session()->flash('message', 'View help #' . $id);
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
        $query = Help::query()
            ->with(['customer', 'category', 'city'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('status', $this->statusFilter);
            });

        // Filter by admin's city if user is admin
        if (auth()->user() && auth()->user()->role === 'admin' && auth()->user()->city_id) {
            $query->whereHas('customer', function ($q) {
                $q->where('city_id', auth()->user()->city_id);
            });
        }

        $helps = $query->latest()->paginate($this->perPage);

        // Statistics - also filtered by city for admin
        $statsQuery = Help::query();
        if (auth()->user() && auth()->user()->role === 'admin' && auth()->user()->city_id) {
            $statsQuery->whereHas('customer', function ($q) {
                $q->where('city_id', auth()->user()->city_id);
            });
        }

        $totalHelps = $statsQuery->count();
        $pendingHelps = (clone $statsQuery)->where('status', 'pending')->count();
        $completedHelps = (clone $statsQuery)->where('status', 'completed')->count();

        return view('admin.helps', compact('helps', 'totalHelps', 'pendingHelps', 'completedHelps'));
    }
}
