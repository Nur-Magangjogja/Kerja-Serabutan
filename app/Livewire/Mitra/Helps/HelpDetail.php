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

    // ─── Cancel modal & Clarification ─────────────────────────────────────────
    public $showPartnerCancelModal       = false;
    public $partnerCancelReason          = '';
    public $partnerCancelNotes           = '';
    public $cancel_evidence_photo        = null;
    public $work_completed_percentage    = 0;
    public $showPartnerCancelStatusModal = false;
    public $partnerCancelStatus          = null; // 'pending' | 'accepted' | 'rejected'

    // Clarification on customer-requested cancel
    public $showClarificationModal       = false;
    public $partnerClarificationText     = '';
    public $partnerClarificationPhoto    = null;

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
        $this->help   = Help::with(['user', 'city', 'rating', 'latestCancelRequest.reviewedBy', 'latestCancelRequest.customer', 'latestCancelRequest.partner'])->findOrFail($id);

        if ($this->help->mitra_id !== auth()->id()) {
            // Akses diizinkan jika pernah terlibat (audit activity, cancel request, atau notifikasi)
            if (!$this->wasInvolvedInHelp($id)) {
                session()->flash('error', 'Bantuan ini tidak ditugaskan kepada Anda.');
                $this->redirectRoute('mitra.dashboard');
                return;
            }

            // Tampilkan modal info pembatalan diterima jika ada flag
            if ($this->help->partner_cancel_prev_status === 'cancel_accepted') {
                $this->showPartnerCancelStatusModal = true;
                $this->partnerCancelStatus          = 'accepted';
            }
        }

        $this->currentStatus = $this->help->status;
    }

    /**
     * Cek apakah mitra pernah terlibat pada help ini (via activity log, cancel request, atau notifikasi).
     */
    private function wasInvolvedInHelp(int $helpId): bool
    {
        if ($this->help && $this->help->mitra_id === auth()->id()) {
            return true;
        }

        if (\App\Models\HelpCancelRequest::where('help_id', $helpId)->where('partner_id', auth()->id())->exists()) {
            return true;
        }

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
        $this->help->load(['user', 'city', 'rating', 'latestCancelRequest.reviewedBy', 'latestCancelRequest.customer', 'latestCancelRequest.partner']);

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

    // ─── Revision 3: Multi-Stage Tracking & Advance ──────────────────────────

    public function advanceStage(string $stage): void
    {
        try {
            app(\App\Services\HelpTrackingService::class)->advanceStage($this->help, auth()->user(), $stage);
            $this->loadHelp();
            $this->dispatch('show-status-notification', message: 'Tahapan berhasil diperbarui!');
            session()->flash('message', 'Tahapan berhasil diperbarui: ' . str_replace('_', ' ', ucfirst($stage)));
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[MitraHelpDetail] advanceStage error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat memperbarui tahapan.');
        }
    }

    public function updatePartnerGps($lat, $lng, $accuracy = null): void
    {
        try {
            app(\App\Services\HelpTrackingService::class)->updatePartnerLocation(
                $this->help,
                auth()->user(),
                (float) $lat,
                (float) $lng,
                $accuracy ? (float) $accuracy : null
            );
            $this->loadHelp();
        } catch (\Throwable $e) {
            Log::warning('[MitraHelpDetail] updatePartnerGps warning: ' . $e->getMessage());
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

    // ─── Partner Cancel (State-Aware Cancel Request with Photo & Audit) ──────
    public function openPartnerCancelModal()
    {
        $this->reset(['partnerCancelReason', 'partnerCancelNotes', 'cancel_evidence_photo']);
        $this->showPartnerCancelModal = true;
    }

    public function closePartnerCancelModal()
    {
        $this->showPartnerCancelModal = false;
        $this->reset(['partnerCancelReason', 'partnerCancelNotes', 'cancel_evidence_photo']);
    }

    public function requestPartnerCancel()
    {
        $this->validate([
            'partnerCancelReason'   => 'required|string|min:3|max:255',
            'partnerCancelNotes'    => 'nullable|string|max:1000',
            'cancel_evidence_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'partnerCancelReason.required' => 'Pilih atau isi alasan pembatalan.',
            'partnerCancelReason.min'      => 'Alasan pembatalan minimal 3 karakter.',
            'cancel_evidence_photo.image'  => 'Foto bukti harus berupa file gambar (JPG/PNG).',
            'cancel_evidence_photo.max'    => 'Ukuran foto bukti maksimal 5MB.',
        ]);

        try {
            $evidencePath = null;
            if ($this->cancel_evidence_photo) {
                $evidencePath = $this->cancel_evidence_photo->store('cancel_evidence', 'public');
            }

            app(\App\Services\HelpCancellationService::class)->submitPartnerCancelRequest(
                $this->help,
                auth()->user(),
                $this->partnerCancelReason,
                $this->partnerCancelNotes ?: null,
                $evidencePath,
                false,
                0.0,
                0.0
            );

            $this->showPartnerCancelModal = false;
            session()->flash('message', 'Tugas berhasil dibatalkan. Akun Anda telah aktif kembali untuk menerima pekerjaan lain.');
            return $this->redirectRoute('mitra.dashboard');
        } catch (\RuntimeException $e) {
            $this->showPartnerCancelModal = false;
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            $this->showPartnerCancelModal = false;
            Log::error('[MitraHelpDetail] requestPartnerCancel error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat membatalkan tugas: ' . $e->getMessage());
        }
    }

    // ─── Clarification for Customer-Requested Cancel ─────────────────────────
    public function openClarificationModal()
    {
        $this->partnerClarificationText  = '';
        $this->partnerClarificationPhoto = null;
        $this->showClarificationModal    = true;
    }

    public function closeClarificationModal()
    {
        $this->showClarificationModal    = false;
        $this->partnerClarificationText  = '';
        $this->partnerClarificationPhoto = null;
    }

    public function submitClarification()
    {
        $this->validate([
            'partnerClarificationText'  => 'required|string|min:5|max:1000',
            'partnerClarificationPhoto' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'partnerClarificationText.required' => 'Isi penjelasan klarifikasi/pengakuan Anda.',
            'partnerClarificationText.min'      => 'Penjelasan minimal 5 karakter.',
            'partnerClarificationPhoto.image'   => 'Foto bukti harus berupa gambar (JPG/PNG).',
        ]);

        try {
            $pendingRequest = \App\Models\HelpCancelRequest::where('help_id', $this->help->id)
                ->where('status', \App\Models\HelpCancelRequest::STATUS_PENDING)
                ->where('requester_type', \App\Models\HelpCancelRequest::REQUESTER_CUSTOMER)
                ->latest()
                ->first();

            if (!$pendingRequest) {
                session()->flash('error', 'Tidak ditemukan tiket pengajuan pembatalan customer yang aktif.');
                $this->showClarificationModal = false;
                return;
            }

            $clarificationPhotoPath = null;
            if ($this->partnerClarificationPhoto) {
                $clarificationPhotoPath = $this->partnerClarificationPhoto->store('cancel_clarifications', 'public');
            }

            app(\App\Services\HelpCancellationService::class)->submitPartnerClarification(
                $pendingRequest,
                auth()->user(),
                $this->partnerClarificationText,
                $clarificationPhotoPath
            );

            $this->showClarificationModal = false;
            $this->loadHelp();
            session()->flash('message', 'Tanggapan / klarifikasi Anda telah berhasil dicatat untuk ditinjau oleh Admin Wilayah.');
        } catch (\Throwable $e) {
            Log::error('[MitraHelpDetail] submitClarification error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat mengirim klarifikasi: ' . $e->getMessage());
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
        $cancelRequest = \App\Models\HelpCancelRequest::with(['customer', 'partner', 'reviewedBy'])
            ->where('help_id', $this->help->id)
            ->where(function ($q) {
                $q->where('partner_id', auth()->id())
                  ->orWhereNull('partner_id');
            })
            ->latest()
            ->first();

        if (!$cancelRequest) {
            $cancelRequest = \App\Models\HelpCancelRequest::with(['customer', 'partner', 'reviewedBy'])
                ->where('help_id', $this->help->id)
                ->latest()
                ->first();
        }

        return view('livewire.mitra.helps.help-detail', [
            'cancelRequest' => $cancelRequest,
        ]);
    }
}
