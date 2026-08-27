<?php

namespace App\Livewire\Admin\Helps;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Help;

#[Layout('layouts.admin')]
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

    // Admin view-only page, no approve/reject methods here

    public function render()
    {
        $query = Help::query()->with(['customer', 'mitra', 'city'])
            ->whereIn('status', ['active', 'menunggu_mitra', 'taken', 'memperoleh_mitra', 'sedang_diproses', 'in_progress', 'waiting_customer_confirmation', 'selesai', 'completed'])
            ->when($this->search, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            });

        // Filter by admin's city if user is admin
        if (auth()->user() && auth()->user()->role === 'admin' && auth()->user()->city_id) {
            $query->whereHas('customer', function ($q) {
                $q->where('city_id', auth()->user()->city_id);
            });
        }

        $helps = $query->latest()->paginate($this->perPage);

        return view('livewire.admin.helps.approved', compact('helps'));
    }
}
