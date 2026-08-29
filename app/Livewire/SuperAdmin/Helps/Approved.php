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
        $help->update(['status' => Help::STATUS_MENUNGGU_MITRA]);
        session()->flash('message', 'Bantuan berhasil disetujui');
    }

    public function rejectHelp($id)
    {
        $help = Help::findOrFail($id);
        if ($help->escrow_status === Help::ESCROW_STATUS_HELD) {
            app(\App\Services\HelpTransactionService::class)->autoCancelExpiredHelp($help, 'Ditolak oleh SuperAdmin');
        } else {
            $help->update([
                'status'         => Help::STATUS_DIBATALKAN,
                'dispatch_mode'  => Help::DISPATCH_MODE_CLOSED,
                'escrow_status'  => Help::ESCROW_STATUS_REFUNDED,
                'payment_status' => Help::PAYMENT_STATUS_REFUNDED,
            ]);
        }
        session()->flash('message', 'Bantuan ditolak dan dana escrow dikembalikan 100% ke pemohon.');
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

