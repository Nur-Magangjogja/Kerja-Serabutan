<?php

namespace App\Livewire\Mitra\Helps;

use App\Models\Help;
use App\Services\HelpTransactionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.mitra')]
class HelpDetail extends Component
{
    use WithFileUploads;

    protected $listeners = [
        'closePartnerCancelStatusModal' => 'closePartnerCancelStatusModal',
    ];

    public $helpId;
    public $help;
    public $currentStatus;

    // ─── Cancel modal ─────────────────────────────────────────────────────────
    public $showPartnerCancelModal      = false;
    public $partnerCancelReason         = '';
    public $showPartnerCancelStatusModal = false;
    public $partnerCancelStatus         = null; // 'pending' | 'accepted' | 'rejected'

    // ─── Completion modal ────────────────────────────────────────────────────
    public $proof_photo;
    public $completion_notes = '';
    public $showCompletionModal = false;

    // ─────────────────────────────────────────────────────────────────────────
    // LIFECYCLE
    // ─────────────────────────────────────────────────────────────────────────

    public function mount($id)
    {
        $this->helpId = $id;
        $this->help   = Help::with(['user', 'city', 'rating'])->findOrFail($id);

        if ($this->help->mitra_id !== auth()->id()) {
            // Akses diizinkan jika pernah terlibat (audit activity atau notifikasi)
            if (!$this->wasInvolvedInHelp($id)) {
                session()->flash('error', 'Bantuan ini tidak ditugaskan kepada Anda.');
                return redirect()->route('mitra.dashboard');
            }

            // Tampilkan modal info pembatalan diterima
            $this->showPartnerCancelStatusModal = true;
            $this->partnerCancelStatus          = 'accepted';
        }

        $this->currentStatus = $this->help->status;
    }

    /**
     * Cek apakah mitra pernah terlibat pada help ini (via activity log atau notifikasi).
     */
    private function wasInvolvedInHelp(int $helpId): bool
    {
        if (\App\Models\PartnerActivity::where('help_id', $helpId)->where('user_id', auth()->id())->exists()) {
            return true;
        }

        return DB::table('notifications')
            ->where('notifiable_id', auth()->id())
            ->where('data', 'like', "%{$helpId}%")
            ->exists();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LOAD & POLLING
    // ─────────────────────────────────────────────────────────────────────────

    public function loadHelp(): void
    {
        $oldStatus = $this->help?->status;
        $oldFlag   = $this->help?->partner_cancel_prev_status;

        $this->help->refresh();
        $this->help->load(['user', 'city', 'rating']);

        // Auto-confirm jika batas waktu 24 jam telah terlewati tanpa komplain/sengketa
        if (
            $this->help->status === Help::STATUS_WAITING_CONFIRMATION &&
            $this->help->escrow_status === Help::ESCROW_STATUS_HELD &&
            $this->help->disputed_at === null &&
            $this->help->confirmation_deadline_at &&
            $this->help->confirmation_deadline_at->isPast()
        ) {
            app(\App\Services\HelpTransactionService::class)->autoConfirmExpiredConfirmation($this->help);
            $this->help->refresh();
        }

        $newStatus = $this->help->status;
        $newFlag   = $this->help->partner_cancel_prev_status;

        // Deteksi keputusan customer terhadap permintaan pembatalan
        if ($oldStatus !== $newStatus || $oldFlag !== $newFlag) {
            if ($newFlag === 'cancel_accepted') {
                $this->dispatch('show-status-notification', message: 'Customer menerima pembatalan!');
            }
            if ($newFlag === 'cancel_rejected') {
                $this->dispatch('show-status-notification', message: 'Pembatalan ditolak customer! Silakan lanjutkan pekerjaan.');
            }
        }

        $this->currentStatus = $newStatus;
    }

    /**
     * Dipanggil tiap poll (wire:poll.4s) untuk sinkronisasi status realtime.
     */
    public function checkForUpdates(): void
    {
        $this->loadHelp();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MITRA ACTIONS — semua didelegasikan ke HelpTransactionService
    // ─────────────────────────────────────────────────────────────────────────

    public function markPartnerStarted()
    {
        try {
            app(HelpTransactionService::class)->markOnTheWay($this->help, auth()->user());
            $this->loadHelp();
            $this->dispatch('show-status-notification', message: 'Perjalanan dimulai!');
            session()->flash('message', 'Perjalanan dimulai! Jangan lupa update lokasi Anda.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[MitraHelpDetail] markPartnerStarted error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan.');
        }
    }

    public function markPartnerArrived()
    {
        try {
            app(HelpTransactionService::class)->markArrived($this->help, auth()->user());
            $this->loadHelp();
            $this->dispatch('show-status-notification', message: 'Anda sudah tiba di lokasi!');
            session()->flash('message', 'Anda sudah tiba di lokasi! Silakan mulai pekerjaan.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[MitraHelpDetail] markPartnerArrived error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan.');
        }
    }

    public function startService()
    {
        try {
            app(HelpTransactionService::class)->startService($this->help, auth()->user());
            $this->loadHelp();
            $this->dispatch('show-status-notification', message: 'Pekerjaan telah dimulai!');
            session()->flash('message', 'Pekerjaan telah dimulai!');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[MitraHelpDetail] startService error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan.');
        }
    }

    // ─── Completion ──────────────────────────────────────────────────────────

    public function openCompletionModal()
    {
        $this->reset(['proof_photo', 'completion_notes']);
        $this->showCompletionModal = true;
    }

    public function closeCompletionModal()
    {
        $this->showCompletionModal = false;
        $this->reset(['proof_photo', 'completion_notes']);
    }

    public function submitCompletionProof()
    {
        $this->validate([
            'proof_photo'      => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'completion_notes' => 'nullable|string|max:1000',
        ], [
            'proof_photo.required' => 'Foto bukti pengerjaan wajib diunggah.',
            'proof_photo.image'    => 'File bukti harus berupa gambar (JPG, JPEG, PNG).',
            'proof_photo.mimes'    => 'Format foto bukti harus berupa PNG, JPG, atau JPEG.',
            'proof_photo.max'      => 'Ukuran foto bukti maksimal 5MB.',
        ]);

        try {
            app(HelpTransactionService::class)->submitCompletion(
                $this->help,
                auth()->user(),
                $this->proof_photo,
                $this->completion_notes ?: null
            );

            $this->showCompletionModal = false;
            $this->reset(['proof_photo', 'completion_notes']);
            $this->loadHelp();

            $this->dispatch('show-status-notification', message: 'Bukti pengerjaan terkirim! Menunggu konfirmasi customer (maks. 24 jam).');
            session()->flash('message', 'Bukti pengerjaan berhasil dikirim! Menunggu konfirmasi customer (maks. 24 jam). Dana akan diteruskan ke saldo Anda setelah dikonfirmasi atau otomatis selesai.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[MitraHelpDetail] submitCompletionProof error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat mengunggah bukti.');
        }
    }

    /** Alias — buka modal completion */
    public function markCompleted()
    {
        $this->openCompletionModal();
    }

    // ─── Partner Cancel ──────────────────────────────────────────────────────

    public function openPartnerCancelModal()
    {
        $this->partnerCancelReason    = '';
        $this->showPartnerCancelModal = true;
    }

    public function requestPartnerCancel()
    {
        try {
            app(HelpTransactionService::class)->requestPartnerCancel(
                $this->help,
                auth()->user(),
                $this->partnerCancelReason ?: null
            );

            $this->showPartnerCancelModal       = false;
            $this->showPartnerCancelStatusModal = false;
            $this->partnerCancelStatus          = null;

            session()->flash('message', 'Pembatalan berhasil. Tugas telah dilepaskan dan dikembalikan ke sistem pencarian untuk Rekan Jasa lain.');
            return redirect()->route('mitra.helps.all');
        } catch (\RuntimeException $e) {
            $this->showPartnerCancelModal = false;
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            $this->showPartnerCancelModal = false;
            Log::error('[MitraHelpDetail] requestPartnerCancel error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat membatalkan tugas.');
        }
    }

    public function closePartnerCancelStatusModal()
    {
        $this->showPartnerCancelStatusModal = false;
        $this->partnerCancelStatus          = null;
    }

    public function acknowledgeAcceptedCancellation()
    {
        // Bersihkan flag setelah mitra acknowledge
        if ($this->help->partner_cancel_prev_status === 'cancel_accepted') {
            $this->help->update(['partner_cancel_prev_status' => null]);
        }
        $this->help->refresh();
    }

    public function acknowledgeRejectedCancellation()
    {
        if ($this->help->partner_cancel_prev_status === 'cancel_rejected') {
            $this->help->update(['partner_cancel_prev_status' => null]);
        }
        $this->loadHelp();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UTILITIES
    // ─────────────────────────────────────────────────────────────────────────

    public function copyOrderId()
    {
        $this->dispatch('show-status-notification', message: 'ID Pesanan disalin ke clipboard');
        $this->js('navigator.clipboard.writeText("' . $this->help->order_id . '")');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RENDER
    // ─────────────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.mitra.helps.help-detail');
    }
}
