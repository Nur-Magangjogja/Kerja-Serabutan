<?php

namespace App\Livewire\Customer\Helps;

use App\Models\Help;
use App\Models\Rating;
use App\Services\HelpCancellationService;
use App\Services\HelpTransactionService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

class Detail extends Component
{
    use WithFileUploads;

    public $help;
    public $helpId;

    // Modal state
    public $showCancelConfirm          = false;
    public $showCustomerCancelModal    = false;
    public $cancelOption               = 'switch'; // 'switch' (Ganti Mitra) | 'withdraw' (Tarik Pekerjaan)
    public $switchReason               = 'Mitra tidak bergerak / tidak merespons chat';
    public $switchNotes                = '';
    public $customerCancelReason       = '';
    public $customerCancelNotes        = '';
    public $customerCancelPhoto        = null;

    public $showMapModal               = false;
    public $showRatingForm             = false;
    public $showDisputeModal           = false;
    public $disputeReason              = '';

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
            'user', 'mitra', 'city', 'district', 'ratings',
        ])->findOrFail($this->helpId);

        if ($this->help->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }

        // Auto-cancel jika batas waktu pencarian rekan jasa (expires_at) telah berakhir
        if ($this->help->status === Help::STATUS_MENUNGGU_MITRA && $this->help->isExpired()) {
            app(HelpCancellationService::class)->autoCancelExpiredHelp($this->help, 'Batas waktu pencarian Rekan Jasa telah berakhir');
            $this->help->refresh();
        }

        // Auto-confirm jika batas waktu 24 jam telah terlewati tanpa komplain/sengketa
        if (
            $this->help->status === Help::STATUS_WAITING_CONFIRMATION &&
            $this->help->escrow_status === Help::ESCROW_STATUS_HELD &&
            $this->help->disputed_at === null &&
            $this->help->confirmation_deadline_at &&
            $this->help->confirmation_deadline_at->isPast()
        ) {
            app(HelpTransactionService::class)->autoConfirmExpiredConfirmation($this->help);
            $this->help->refresh();
        }

        // Kirim data tracking ke frontend bila map terbuka
        if ($this->showMapModal && in_array($this->help->status, [Help::STATUS_TAKEN, Help::STATUS_PARTNER_ON_THE_WAY, Help::STATUS_PARTNER_ARRIVED])) {
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
        if ($oldStatus === Help::STATUS_PARTNER_CANCEL_REQUESTED && $newStatus !== Help::STATUS_PARTNER_CANCEL_REQUESTED) {
            $this->dispatch('show-status-notification', message: 'Status pesanan diperbarui!');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AKSI CUSTOMER
    // ─────────────────────────────────────────────────────────────────────────

    public function cancelHelp()
    {
        // Khusus pickup_delivery: Cek aturan Anti-Bypass Lock & Staged Cancellation
        if ($this->help->isPickup()) {
            if ($this->help->status === Help::STATUS_MENUNGGU_MITRA || empty($this->help->mitra_id)) {
                try {
                    app(HelpCancellationService::class)->cancelOrderBeforePartnerTaken($this->help, auth()->user(), 'Dibatalkan oleh customer sebelum ada mitra');
                    session()->flash('success', 'Permintaan bantuan antar/jemput berhasil dibatalkan dan saldo telah dikembalikan 100%.');
                    $this->showCancelConfirm = false;
                    return redirect()->route('customer.helps.index');
                } catch (\RuntimeException $e) {
                    session()->flash('error', $e->getMessage());
                    return;
                } catch (\Throwable $e) {
                    Log::error('[CustomerHelpDetail] cancelHelp pickup error: ' . $e->getMessage());
                    session()->flash('error', 'Terjadi kesalahan saat membatalkan bantuan: ' . $e->getMessage());
                    return;
                }
            }

            if ($this->help->canCustomerCancel()) {
                try {
                    app(HelpCancellationService::class)->cancelPickupDeliveryByCustomer($this->help, auth()->user(), 'Dibatalkan oleh customer pada tahap penjemputan.');
                    session()->flash('success', 'Pesanan antar/jemput berhasil dibatalkan. Kompensasi mitra dan pengembalian saldo telah diproses sesuai tahap perjalanan.');
                    $this->showCancelConfirm = false;
                    $this->loadHelp();
                    return;
                } catch (\RuntimeException $e) {
                    session()->flash('error', $e->getMessage());
                    return;
                } catch (\Throwable $e) {
                    Log::error('[CustomerHelpDetail] cancelPickupDelivery error: ' . $e->getMessage());
                    session()->flash('error', 'Terjadi kesalahan saat membatalkan pesanan antar/jemput: ' . $e->getMessage());
                    return;
                }
            } else {
                session()->flash('error', 'Pembatalan otomatis terkunci karena pengantaran fisik barang telah dimulai. Silakan hubungi Bantuan CS / Admin Wilayah.');
                $this->showCancelConfirm = false;
                return;
            }
        }

        // Layanan Reguler (On-Site Service / Buy for Customer)
        if ($this->help->status === Help::STATUS_MENUNGGU_MITRA || empty($this->help->mitra_id)) {
            try {
                app(HelpCancellationService::class)->cancelOrderBeforePartnerTaken($this->help, auth()->user(), 'Dibatalkan oleh customer sebelum ada mitra');
                session()->flash('success', 'Permintaan bantuan berhasil dibatalkan dan saldo telah dikembalikan 100%.');
                $this->showCancelConfirm = false;
                return redirect()->route('customer.helps.index');
            } catch (\RuntimeException $e) {
                session()->flash('error', $e->getMessage());
            } catch (\Throwable $e) {
                Log::error('[CustomerHelpDetail] cancelHelp error: ' . $e->getMessage());
                session()->flash('error', 'Terjadi kesalahan saat membatalkan bantuan: ' . $e->getMessage());
            }
        } else {
            // Jika sudah diambil mitra, arahkan ke modal pengajuan pembatalan customer
            $this->showCancelConfirm = false;
            $this->openCustomerCancelModal();
        }
    }

    public function openCustomerCancelModal()
    {
        $this->cancelOption         = 'switch';
        $this->switchReason         = 'Mitra tidak bergerak / tidak kunjung datang';
        $this->switchNotes          = '';
        $this->customerCancelReason = '';
        $this->customerCancelNotes  = '';
        $this->customerCancelPhoto  = null;
        $this->showCustomerCancelModal = true;
    }

    public function closeCustomerCancelModal()
    {
        $this->showCustomerCancelModal = false;
        $this->cancelOption         = 'switch';
        $this->switchReason         = 'Mitra tidak bergerak / tidak kunjung datang';
        $this->switchNotes          = '';
        $this->customerCancelReason = '';
        $this->customerCancelNotes  = '';
        $this->customerCancelPhoto  = null;
    }

    /**
     * Customer memilih Ganti Mitra (Tetap Lanjut Cari Mitra Baru).
     */
    public function switchPartner()
    {
        $this->validate([
            'switchReason' => 'required|string|min:3|max:255',
            'switchNotes'  => 'nullable|string|max:1000',
        ], [
            'switchReason.required' => 'Pilih atau isi alasan penggantian mitra.',
            'switchReason.min'      => 'Alasan minimal 3 karakter.',
        ]);

        try {
            app(HelpCancellationService::class)->switchPartnerByCustomer(
                $this->help,
                auth()->user(),
                $this->switchReason,
                $this->switchNotes ?: null
            );

            $this->showCustomerCancelModal = false;
            $this->loadHelp();
            session()->flash('success', 'Permintaan ganti mitra berhasil diajukan. Tim Admin dan mitra akan berkoordinasi dan pesanan dialihkan untuk mencari mitra baru.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[CustomerHelpDetail] switchPartner error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat mengajukan ganti mitra: ' . $e->getMessage());
        }
    }

    /**
     * Customer memilih Tarik Pekerjaan (Batal Total & Refund 100%).
     */
    public function submitCustomerCancel()
    {
        // Guard: Pastikan mitra belum mulai bekerja (hanya saat dalam perjalanan)
        $isPartnerWorking = in_array($this->help->status, [
            Help::STATUS_PARTNER_ARRIVED,
            Help::STATUS_IN_PROGRESS,
            Help::STATUS_WAITING_CONFIRMATION,
            Help::STATUS_SELESAI,
        ]) || ($this->help->isPickup() && in_array($this->help->service_stage, [
            Help::STAGE_AT_PICKUP,
            Help::STAGE_ITEM_COLLECTED,
            Help::STAGE_GOING_TO_DELIVERY,
            Help::STAGE_FINAL_APPROACH,
            Help::STAGE_AT_DESTINATION,
        ]));

        if ($isPartnerWorking) {
            session()->flash('error', 'Opsi penarikan pekerjaan tidak tersedia karena mitra telah tiba di lokasi atau sedang bekerja. Silakan gunakan opsi Ganti Mitra atau hubungi Bantuan CS.');
            $this->showCustomerCancelModal = false;
            return;
        }

        $this->validate([
            'customerCancelReason' => 'required|string|min:5|max:255',
            'customerCancelNotes'  => 'nullable|string|max:1000',
            'customerCancelPhoto'  => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'customerCancelReason.required' => 'Pilih atau isi alasan penarikan pekerjaan.',
            'customerCancelReason.min'      => 'Alasan penarikan minimal 5 karakter.',
            'customerCancelPhoto.image'     => 'Foto bukti harus berupa gambar (JPG/PNG).',
        ]);

        try {
            $photoPath = null;
            if ($this->customerCancelPhoto) {
                $photoPath = $this->customerCancelPhoto->store('customer_cancels', 'public');
            }

            app(HelpCancellationService::class)->submitCustomerCancelRequest(
                $this->help,
                auth()->user(),
                $this->customerCancelReason,
                $this->customerCancelNotes ?: null,
                $photoPath
            );

            $this->showCustomerCancelModal = false;
            $this->loadHelp();
            session()->flash('warning', 'Permintaan penarikan pekerjaan telah dikirimkan. Mitra akan mengonfirmasi dan kasus ini dipantau oleh Admin Wilayah.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[CustomerHelpDetail] submitCustomerCancel error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function acceptPartnerCancel()
    {
        try {
            app(HelpCancellationService::class)->customerAcceptPartnerCancellation($this->help, auth()->user());
            $this->loadHelp();
            session()->flash('success', 'Pembatalan mitra telah disetujui. Dana 100% telah dikembalikan ke saldo Anda.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[CustomerHelpDetail] acceptPartnerCancel error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat menyetujui pembatalan mitra.');
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

    public function openDisputeModal()
    {
        if ($this->help->dispute_resolved_at !== null) {
            session()->flash('error', 'Sengketa untuk pesanan ini telah diputuskan oleh Admin secara final.');
            return;
        }

        if ($this->help->escrow_status !== \App\Models\Help::ESCROW_STATUS_HELD) {
            session()->flash('error', 'Dana bantuan tidak berada dalam status holding (telah dicairkan atau telah dibatalkan).');
            return;
        }

        $this->disputeReason = '';
        $this->showDisputeModal = true;
    }

    public function closeDisputeModal()
    {
        $this->showDisputeModal = false;
        $this->disputeReason = '';
    }

    public function submitDispute()
    {
        $this->validate([
            'disputeReason' => 'required|string|min:10|max:1000',
        ], [
            'disputeReason.required' => 'Alasan komplain wajib diisi.',
            'disputeReason.min'      => 'Alasan komplain minimal 10 karakter.',
            'disputeReason.max'      => 'Alasan komplain maksimal 1000 karakter.',
        ]);

        try {
            app(HelpTransactionService::class)->raiseDispute($this->help, auth()->user(), $this->disputeReason);
            $this->showDisputeModal = false;
            $this->disputeReason = '';
            $this->loadHelp();
            session()->flash('warning', 'Komplain Anda telah dicatat dan diteruskan ke Admin Wilayah. Dana escrow telah dibekukan.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[CustomerHelpDetail] submitDispute error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat mengajukan sengketa.');
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

        if (!$this->help->canBeRated()) {
            session()->flash('error', 'Rating belum dapat diberikan atau pesanan sedang dalam proses sengketa.');
            return;
        }

        if (Rating::hasRated($this->help->id, auth()->id(), 'customer_to_mitra')) {
            session()->flash('error', 'Anda sudah memberikan rating untuk pesanan ini.');
            return;
        }

        $ratingRecord = \Illuminate\Support\Facades\DB::transaction(function () {
            $record = Rating::create([
                'help_id'  => $this->help->id,
                'rater_id' => auth()->id(),
                'ratee_id' => $this->help->mitra_id,
                'type'     => 'customer_to_mitra',
                'rating'   => $this->rating,
                'review'   => $this->review,
            ]);

            $this->help->update(['rating_status' => Help::RATING_STATUS_RATED]);

            return $record;
        });

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
        if (!in_array($this->help->status, [Help::STATUS_TAKEN, Help::STATUS_PARTNER_ON_THE_WAY, Help::STATUS_PARTNER_ARRIVED])) {
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
            Help::STATUS_TAKEN                     => '✅ Rekan Jasa telah mengambil pesanan Anda',
            Help::STATUS_PARTNER_ON_THE_WAY        => '🚗 Rekan Jasa sedang menuju lokasi Anda',
            Help::STATUS_PARTNER_ARRIVED           => '📍 Rekan Jasa telah tiba di lokasi',
            Help::STATUS_IN_PROGRESS               => '⚙️ Pekerjaan sedang dikerjakan',
            Help::STATUS_WAITING_CONFIRMATION      => '✋ Menunggu konfirmasi Anda untuk menyelesaikan pesanan',
            Help::STATUS_SELESAI                   => '✅ Pesanan telah selesai',
            Help::STATUS_DIBATALKAN                => '❌ Pesanan dibatalkan',
            Help::STATUS_PARTNER_CANCEL_REQUESTED  => '⚠️ Mitra mengajukan kendala/pembatalan',
            Help::STATUS_CUSTOMER_CANCEL_REQUESTED => '⚠️ Pengajuan pembatalan sedang ditinjau admin',
            default                                => 'Status pesanan diperbarui',
        };
    }

    public function render()
    {
        return view('livewire.customer.helps.detail')
            ->layout('layouts.app', ['title' => 'Detail Pesanan']);
    }
}
