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
        $help->update([
            'status'        => Help::STATUS_MENUNGGU_MITRA,
            'dispatch_mode' => Help::DISPATCH_MODE_POOL,
        ]);
        session()->flash('message', 'Bantuan berhasil disetujui dan dibuka ke pool mitra');
    }

    public function rejectHelp($id)
    {
        $help = Help::findOrFail($id);
        app(\App\Services\HelpCancellationService::class)->cancelByPartnerUnilaterally($help, auth()->user(), 'Ditolak oleh SuperAdmin');
        session()->flash('message', 'Bantuan ditolak dan dana escrow dikembalikan 100% ke pemohon.');
    }

    public function render()
    {
        $approvedStatuses = array_merge(Help::activeStatuses(), [
            Help::STATUS_MENUNGGU_MITRA,
            Help::STATUS_WAITING_CONFIRMATION,
            Help::STATUS_SELESAI,
        ]);

        $helps = Help::query()
            ->with(['customer', 'mitra', 'city'])
            ->whereIn('status', $approvedStatuses)
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

