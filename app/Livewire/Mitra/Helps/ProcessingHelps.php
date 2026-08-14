<?php

namespace App\Livewire\Mitra\Helps;

use App\Models\Help;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.mitra')]
class ProcessingHelps extends Component
{
    public $helps = [];

    public function mount()
    {
        $this->loadHelps();
    }

    public function loadHelps()
    {
        // Include all processing statuses including new GPS tracking statuses
        $statuses = [
            'memperoleh_mitra',
            'taken',
            'partner_on_the_way',
            'partner_arrived',
            'in_progress',
            'sedang_diproses',
            'partner_cancel_requested',
            'diproses_mitra',
            'waiting_customer_confirmation'
        ];

        $this->helps = Help::where('mitra_id', auth()->id())
            ->whereIn('status', $statuses)
            ->orderByDesc('taken_at')
            ->get();
    }

    public function completeHelp($helpId)
    {
        $help = Help::where('id', $helpId)->where('mitra_id', auth()->id())->first();
        if (!$help) {
            $this->dispatch('error', 'Bantuan tidak ditemukan atau bukan milik Anda');
            return;
        }

        // Set to waiting_customer_confirmation instead of marking complete immediately
        $help->update([
            'status' => 'waiting_customer_confirmation',
        ]);

        if (!$help->service_completed_at) {
            $help->update(['service_completed_at' => now()]);
        }

        $this->dispatch('help-completed');
        session()->flash('success', 'Menunggu konfirmasi dari customer');
        $this->loadHelps();
    }

    public function render()
    {
        return view('livewire.mitra.helps.processing-helps');
    }
}
