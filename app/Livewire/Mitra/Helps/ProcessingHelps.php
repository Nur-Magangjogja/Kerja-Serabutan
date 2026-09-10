<?php

namespace App\Livewire\Mitra\Helps;

use App\Models\Help;
use App\Services\HelpTransactionService;
use Illuminate\Support\Facades\Log;
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
        $this->helps = Help::where('mitra_id', auth()->id())
            ->with(['user', 'city'])
            ->whereIn('status', array_merge(Help::activeStatuses(), [
                Help::STATUS_WAITING_CONFIRMATION,
                Help::STATUS_PARTNER_CANCEL_REQUESTED,
                Help::STATUS_CUSTOMER_CANCEL_REQUESTED,
            ]))
            ->orderByDesc('taken_at')
            ->get();
    }

    /**
     * Dipanggil tiap poll (wire:poll.5s) untuk sinkronisasi list realtime.
     */
    public function checkForUpdates(): void
    {
        $this->loadHelps();
    }

    /**
     * Mitra menyelesaikan bantuan → delegasi ke HelpTransactionService.
     */
    public function completeHelp($helpId)
    {
        $help = Help::where('id', $helpId)->where('mitra_id', auth()->id())->first();
        if (!$help) {
            session()->flash('error', 'Bantuan tidak ditemukan atau bukan milik Anda');
            return;
        }

        try {
            app(HelpTransactionService::class)->submitCompletion($help, auth()->user(), null, 'Selesai dari daftar tugas');

            $this->dispatch('help-completed');
            session()->flash('success', 'Pekerjaan ditandai selesai! Menunggu konfirmasi customer. Anda kini dapat mencari bantuan baru.');
            $this->loadHelps();
        } catch (\Throwable $e) {
            Log::error('[ProcessingHelps] completeHelp error: ' . $e->getMessage(), ['help_id' => $helpId]);
            session()->flash('error', 'Terjadi kesalahan saat menyelesaikan bantuan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.mitra.helps.processing-helps');
    }
}
