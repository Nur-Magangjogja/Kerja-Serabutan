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
            ->active()
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
     * Mitra menyelesaikan bantuan → ubah ke waiting_customer_confirmation.
     * Catatan: harus ada foto bukti (via submitCompletionProof di HelpDetail).
     * Metode ini adalah shortcut dari daftar (processing list) tanpa foto.
     */
    public function completeHelp($helpId)
    {
        $help = Help::where('id', $helpId)->where('mitra_id', auth()->id())->first();
        if (!$help) {
            session()->flash('error', 'Bantuan tidak ditemukan atau bukan milik Anda');
            return;
        }

        try {
            // Gunakan service untuk validasi transisi dan kirim notifikasi
            $help->update([
                'status'                   => Help::STATUS_WAITING_CONFIRMATION,
                'service_completed_at'     => $help->service_completed_at ?? now(),
                'confirmation_deadline_at' => now()->addHours(24),
            ]);

            $this->dispatch('help-completed');
            session()->flash('success', 'Menunggu konfirmasi dari customer');
            $this->loadHelps();
        } catch (\Throwable $e) {
            Log::error('[ProcessingHelps] completeHelp error: ' . $e->getMessage(), ['help_id' => $helpId]);
            session()->flash('error', 'Terjadi kesalahan saat menyelesaikan bantuan.');
        }
    }

    public function render()
    {
        return view('livewire.mitra.helps.processing-helps');
    }
}
