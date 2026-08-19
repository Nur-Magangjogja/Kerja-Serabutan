<?php

namespace App\Livewire\Customer\Helps;

use App\Models\Help;
use App\Models\Rating;
use App\Services\HelpTransactionService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class Detail extends Component
{
    public $help;
    public $helpId;

    // Modal state
    public $showCancelConfirm          = false;
    public $showMapModal               = false;
    public $showRatingForm             = false;
    public $showPartnerCancelModal     = false;

    // Rating
    public $rating = 0;
    public $review = '';

    // Poll: deteksi perubahan status setiap 4 detik
    protected $previousStatus = null;

    protected $listeners = [
        'refreshHelp'    => '$refresh',
        'status-changed' => 'handleStatusChanged',
    ];

    public function mount($id)
    {
        $this->helpId = $id;
        $this->loadHelp();
    }

    public function loadHelp(): void
    {
        $this->help = Help::with([
            'user', 'mitra', 'city', 'category', 'ratings',
        ])->findOrFail($this->helpId);

        if ($this->help->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        // Kirim data tracking ke frontend bila map terbuka
        if ($this->showMapModal && in_array($this->help->status, ['taken', 'partner_on_the_way', 'partner_arrived'])) {
            $this->dispatch('tracking-data-updated', [
                'partnerLat'  => $this->help->partner_current_lat ?? ($this->help->mitra?->latitude ?? -6.2088),
                'partnerLng'  => $this->help->partner_current_lng ?? ($this->help->mitra?->longitude ?? 106.8456),
                'customerLat' => $this->help->latitude ?? -6.2088,
                'customerLng' => $this->help->longitude ?? 106.8456,
            ]);
        }
    }

    /**
     * Dipanggil setiap poll (wire:poll.4s) untuk mendeteksi status baru.
     */
    public function checkForUpdates(): void
    {
        $oldStatus = $this->help?->status;
        $oldFlag   = $this->help?->partner_cancel_prev_status;

        $this->loadHelp();

        $newStatus = $this->help->status;
        $newFlag   = $this->help->partner_cancel_prev_status;

        if ($oldStatus !== $newStatus) {
            $this->dispatch('show-status-notification',
                message: $this->getStatusNotificationMessage($newStatus));
        }

        // Deteksi keputusan pembatalan mitra yang baru diterima
        if ($oldStatus === 'partner_cancel_requested' && $newStatus !== 'partner_cancel_requested') {
            $this->dispatch('show-status-notification', message: 'Status pesanan diperbarui!');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AKSI CUSTOMER
    // ─────────────────────────────────────────────────────────────────────────

    public function cancelHelp()
    {
        try {
            app(HelpTransactionService::class)->customerCancelHelp($this->help, auth()->user());
            session()->flash('success', 'Permintaan bantuan berhasil dibatalkan.');
            $this->showCancelConfirm = false;
            return redirect()->route('customer.helps.index');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[CustomerHelpDetail] cancelHelp error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat membatalkan bantuan.');
        }
    }

    public function confirmCompletion()
    {
        try {
            app(HelpTransactionService::class)->customerConfirmCompletion($this->help, auth()->user());
            $this->loadHelp();
            session()->flash('success', 'Pesanan telah dikonfirmasi selesai!');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[CustomerHelpDetail] confirmCompletion error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat mengkonfirmasi pesanan.');
        }
    }

    public function acceptPartnerCancellation()
    {
        try {
            app(HelpTransactionService::class)->customerAcceptCancel($this->help, auth()->user());
            $this->loadHelp();
            session()->flash('success', 'Permintaan pembatalan diterima. Pesanan Anda telah dikembalikan ke daftar pencarian untuk Rekan Jasa lain.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[CustomerHelpDetail] acceptPartnerCancellation error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan.');
        }
    }

    public function rejectPartnerCancellation()
    {
        try {
            app(HelpTransactionService::class)->customerRejectCancel($this->help, auth()->user());
            $this->loadHelp();
            session()->flash('success', 'Permintaan pembatalan ditolak. Silakan lanjutkan pekerjaan.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[CustomerHelpDetail] rejectPartnerCancellation error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RATING
    // ─────────────────────────────────────────────────────────────────────────

    public function setRating($value)
    {
        $this->rating = $value;
    }

    public function submitRating()
    {
        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:500',
        ], [
            'rating.required' => 'Rating harus diisi',
            'rating.min'      => 'Rating minimal 1 bintang',
            'rating.max'      => 'Rating maksimal 5 bintang',
            'review.max'      => 'Review maksimal 500 karakter',
        ]);

        if (!in_array($this->help->status, ['selesai', 'completed'])) {
            session()->flash('error', 'Rating hanya bisa diberikan untuk pesanan yang sudah selesai.');
            return;
        }

        if (Rating::hasRated($this->help->id, auth()->id(), 'customer_to_mitra')) {
            session()->flash('error', 'Anda sudah memberikan rating untuk pesanan ini.');
            return;
        }

        $ratingRecord = Rating::create([
            'help_id'  => $this->help->id,
            'user_id'  => auth()->id(),
            'mitra_id' => $this->help->mitra_id,
            'rater_id' => auth()->id(),
            'ratee_id' => $this->help->mitra_id,
            'type'     => 'customer_to_mitra',
            'rating'   => $this->rating,
            'review'   => $this->review,
        ]);

        // Notifikasi ke mitra
        if ($this->help->mitra) {
            try {
                $this->help->mitra->notify(new \App\Notifications\RatingReceivedNotification($this->help, $ratingRecord, auth()->user()));
            } catch (\Throwable $e) {
                Log::warning('[CustomerDetail] Failed to notify mitra of rating: ' . $e->getMessage());
            }
        }

        $this->rating         = 0;
        $this->review         = '';
        $this->showRatingForm = false;
        $this->loadHelp();
        session()->flash('success', 'Terima kasih atas rating Anda!');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MAP TRACKING
    // ─────────────────────────────────────────────────────────────────────────

    public function showTrackingMap()
    {
        if (!in_array($this->help->status, ['taken', 'partner_on_the_way', 'partner_arrived'])) {
            session()->flash('error', 'Tracking hanya tersedia saat mitra sedang menuju lokasi.');
            return;
        }

        $this->loadHelp();

        if (!$this->help->latitude || !$this->help->longitude) {
            session()->flash('error', 'Lokasi customer tidak tersedia.');
            return;
        }

        $partnerLat = $this->help->partner_current_lat ?? $this->help->mitra?->latitude ?? null;
        $partnerLng = $this->help->partner_current_lng ?? $this->help->mitra?->longitude ?? null;

        if (!$partnerLat || !$partnerLng) {
            session()->flash('error', 'Lokasi mitra belum tersedia. Mitra mungkin belum mengaktifkan GPS tracking.');
            return;
        }

        $this->showMapModal = true;
        $this->dispatch('mapModalOpened');
    }

    public function closeMapModal()
    {
        $this->showMapModal = false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EVENT HANDLERS
    // ─────────────────────────────────────────────────────────────────────────

    public function handleStatusChanged($data): void
    {
        if (isset($data['helpId']) && $data['helpId'] == $this->helpId) {
            $this->loadHelp();
            $this->dispatch('show-status-notification', [
                'message' => $this->getStatusNotificationMessage($data['newStatus'] ?? ''),
            ]);
        }
    }

    public function copyOrderId()
    {
        $this->dispatch('copied', orderId: $this->help->order_id);
    }

    public function confirmCancel()
    {
        $this->showCancelConfirm = true;
    }

    public function closeModal()
    {
        $this->showCancelConfirm = false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // COMPUTED PROPERTIES (delegasi ke model)
    // ─────────────────────────────────────────────────────────────────────────

    public function getStatusColorProperty(): string
    {
        return $this->help->status_color;
    }

    public function getStatusTextProperty(): string
    {
        return $this->help->status_label;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function getStatusNotificationMessage(string $status): string
    {
        return match($status) {
            'taken', 'memperoleh_mitra'    => '✅ Rekan Jasa telah mengambil pesanan Anda',
            'partner_on_the_way'            => '🚗 Rekan Jasa sedang menuju lokasi Anda',
            'partner_arrived'               => '📍 Rekan Jasa telah tiba di lokasi',
            'in_progress', 'sedang_diproses'=> '⚙️ Pekerjaan sedang dikerjakan',
            'waiting_customer_confirmation' => '✋ Menunggu konfirmasi Anda untuk menyelesaikan pesanan',
            'selesai', 'completed'          => '✅ Pesanan telah selesai',
            'partner_cancel_requested'      => '⚠️ Mitra mengajukan pembatalan',
            default                         => 'Status pesanan diperbarui',
        };
    }

    public function render()
    {
        return view('livewire.customer.helps.detail')
            ->layout('layouts.app', ['title' => 'Detail Pesanan']);
    }
}
