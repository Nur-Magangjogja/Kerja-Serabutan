<?php

namespace App\Livewire\Mitra\Helps;

use App\Models\Help;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
    public $rating = 0;
    public $review = '';
    public $showPartnerCancelModal = false;
    public $partnerCancelReason = '';
    public $showPartnerCancelStatusModal = false;
    public $partnerCancelStatus = null; // 'pending' | 'accepted' | 'rejected'

    // Proof photo and completion
    public $proof_photo;
    public $completion_notes = '';
    public $showCompletionModal = false;

    public function mount($id)
    {
        $this->helpId = $id;
        $this->help = Help::with(['user', 'city', 'rating'])->findOrFail($id);

        // Verify this help belongs to the authenticated mitra.
        // If not assigned anymore, allow access only when there is a recent
        // notification that confirms the customer's acceptance of the
        // partner-cancellation request (so the mitra can see the confirmation modal after refresh).
        if ($this->help->mitra_id !== auth()->id()) {
            $allowed = false;

            // Look up recent notifications for this mitra related to this help
            $recent = DB::table('notifications')
                ->where('notifiable_id', auth()->id())
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();

            foreach ($recent as $n) {
                $data = json_decode($n->data, true);
                if (!is_array($data)) continue;
                if (isset($data['type']) && $data['type'] === 'help_status'
                    && isset($data['help_id']) && $data['help_id'] == $id
                    && isset($data['new_status']) && $data['new_status'] === 'cancel_accepted') {
                    $allowed = true;
                    break;
                }
            }

            if (!$allowed) {
                abort(403, 'Anda tidak memiliki akses ke bantuan ini.');
            }

            // If allowed because of a recent cancel_accepted notification,
            // show the partner-cancel accepted modal on mount.
            $this->showPartnerCancelStatusModal = true;
            $this->partnerCancelStatus = 'accepted';
        }

        $this->currentStatus = $this->help->status;

        // Tidak perlu session flash lagi, gunakan flag di database
    }

    public function loadHelp()
    {
        // Reload help data dari database untuk mendeteksi perubahan
        $oldStatus = $this->help->status;
        $oldFlag = $this->help->partner_cancel_prev_status;
        
        $this->help->refresh();
        $this->help->load(['user', 'city', 'rating']);
        
        $newStatus = $this->help->status;
        $newFlag = $this->help->partner_cancel_prev_status;
        
        // Detect status change untuk trigger notifikasi
        if ($oldStatus !== $newStatus || $oldFlag !== $newFlag) {
            if ($newFlag === 'cancel_accepted') {
                $this->dispatch('show-status-notification', message: 'Customer menerima pembatalan!');
            }

            if ($newFlag === 'cancel_rejected') {
                $this->dispatch('show-status-notification', message: 'Pembatalan ditolak customer!');
            }
        }
        
        $this->currentStatus = $newStatus;
    }

    public function copyOrderId()
    {
        $this->dispatch('show-status-notification', message: 'ID Pesanan disalin ke clipboard');
        $this->js('navigator.clipboard.writeText("' . $this->help->order_id . '")');
    }

    public function updateStatus($status, $timestampField = null)
    {
        $this->help->update([
            'status' => $status,
        ]);

        // Update timestamp field if provided
        if ($timestampField && !$this->help->$timestampField) {
            $this->help->update([
                $timestampField => now(),
            ]);
        }

        $this->currentStatus = $status;
        $this->help->refresh();

        // Dispatch notifikasi ke Alpine.js
        $this->dispatch('show-status-notification', message: 'Status berhasil diperbarui!');
        
        session()->flash('message', 'Status berhasil diperbarui!');
    }

    public function openPartnerCancelModal()
    {
        $this->partnerCancelReason = '';
        $this->showPartnerCancelModal = true;
    }

    public function requestPartnerCancel()
    {
        // Only allow partner assigned mitra to request cancel and only for certain statuses
        if ($this->help->mitra_id !== auth()->id()) {
            session()->flash('error', 'Anda tidak memiliki izin untuk membatalkan bantuan ini.');
            return;
        }

        if (!in_array($this->help->status, ['memperoleh_mitra', 'taken', 'partner_on_the_way', 'partner_arrived'])) {
            session()->flash('error', 'Pembatalan tidak dapat diminta pada status ini.');
            return;
        }

        $oldStatus = $this->help->status;

        $this->help->update([
            'partner_cancel_prev_status' => $oldStatus,
            'status' => 'partner_cancel_requested',
            'partner_cancel_requested_at' => now(),
            'partner_cancel_reason' => $this->partnerCancelReason ?: null,
        ]);

        // Notify customer
        try {
            $this->help->user->notify(new \App\Notifications\HelpStatusNotification($this->help, $oldStatus, 'partner_cancel_requested', $this->help->mitra));
        } catch (\Exception $e) {
            // silent fail for notification
        }

        $this->showPartnerCancelModal = false;
        $this->help->refresh();
        $this->currentStatus = $this->help->status;

        // Show status modal to indicate request sent and await customer confirmation
        $this->showPartnerCancelStatusModal = true;
        $this->partnerCancelStatus = 'pending';

        session()->flash('message', 'Permintaan pembatalan telah dikirim ke customer. Menunggu konfirmasi.');
    }

    public function closePartnerCancelStatusModal()
    {
        $this->showPartnerCancelStatusModal = false;
        $this->partnerCancelStatus = null;
    }

    public function acknowledgeAcceptedCancellation()
    {
        // Clear flag setelah mitra acknowledge modal
        if ($this->help->partner_cancel_prev_status === 'cancel_accepted') {
            $this->help->update([
                'partner_cancel_prev_status' => null,
            ]);
        }
        
        // Mark session untuk tidak tampilkan lagi
        session()->put('cancel_accepted_modal_shown_' . $this->helpId, true);
        
        $this->help->refresh();
    }

    public function acknowledgeRejectedCancellation()
    {
        // Clear flag setelah mitra acknowledge modal
        if ($this->help->partner_cancel_prev_status === 'cancel_rejected') {
            $this->help->update([
                'partner_cancel_prev_status' => null,
            ]);
        }
        
        // Mark session untuk tidak tampilkan lagi
        session()->put('cancel_rejected_modal_shown_' . $this->helpId, true);
        
        $this->help->refresh();
    }

    public function markPartnerStarted()
    {
        $this->help->update([
            'status' => 'partner_on_the_way',
            'partner_started_at' => now(),
        ]);

        $this->currentStatus = 'partner_on_the_way';
        $this->help->refresh();

        // Dispatch notifikasi ke Alpine.js
        $this->dispatch('show-status-notification', message: 'Perjalanan dimulai!');

        session()->flash('message', 'Perjalanan dimulai! Jangan lupa update lokasi Anda.');
    }

    public function markPartnerArrived()
    {
        $this->help->update([
            'status' => 'partner_arrived',
            'partner_arrived_at' => now(),
        ]);

        $this->currentStatus = 'partner_arrived';
        $this->help->refresh();

        // Dispatch notifikasi ke Alpine.js
        $this->dispatch('show-status-notification', message: 'Anda sudah tiba di lokasi!');

        session()->flash('message', 'Anda sudah tiba di lokasi! Silakan mulai pekerjaan.');
    }

    public function markServiceStarted()
    {
        $this->updateStatus('sedang_diproses', 'service_started_at');
    }

    public function markServiceCompleted()
    {
        $data = ['status' => 'waiting_customer_confirmation'];
        if (!$this->help->service_completed_at) {
            $data['service_completed_at'] = now();
        }
        $this->help->update($data);
        $this->currentStatus = 'waiting_customer_confirmation';
        $this->help->refresh();

        $this->dispatch('show-status-notification', message: 'Menunggu konfirmasi dari customer!');
        session()->flash('message', 'Menunggu konfirmasi dari customer!');
    }

    public function startService()
    {
        // Ubah status ke in_progress dan set service_started_at
        $this->help->update([
            'status' => 'in_progress',
            'service_started_at' => now(),
        ]);

        $this->currentStatus = 'in_progress';
        $this->help->refresh();

        // Dispatch notifikasi ke Alpine.js
        $this->dispatch('show-status-notification', message: 'Pekerjaan telah dimulai!');

        session()->flash('message', 'Pekerjaan telah dimulai!');
    }

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
            'proof_photo' => 'required|image|max:5120',
            'completion_notes' => 'nullable|string|max:1000',
        ], [
            'proof_photo.required' => 'Foto bukti pengerjaan wajib diunggah.',
            'proof_photo.image' => 'File bukti harus berupa gambar (JPG, PNG, WEBP).',
            'proof_photo.max' => 'Ukuran foto bukti maksimal 5MB.',
        ]);

        $path = $this->proof_photo->store('helps/proofs', 'public');

        $data = [
            'status' => 'waiting_customer_confirmation',
            'proof_photo' => $path,
            'completion_notes' => $this->completion_notes,
        ];
        if (!$this->help->service_completed_at) {
            $data['service_completed_at'] = now();
        }
        $this->help->update($data);

        $this->currentStatus = 'waiting_customer_confirmation';
        $this->showCompletionModal = false;
        $this->help->refresh();

        // Send automatic chat message with attached proof photo to Customer
        try {
            $customer = $this->help->user;
            $mitra = auth()->user();
            if ($customer && $mitra) {
                $caption = "Halo Kak {$customer->name}, pekerjaan '{$this->help->title}' telah selesai saya kerjakan. " . 
                    ($this->completion_notes ? "Catatan: \"{$this->completion_notes}\". " : "") .
                    "Berikut terlampir bukti foto hasil pengerjaan. Mohon periksa dan konfirmasi penyelesaian ya. Terima kasih!";

                \App\Models\Chat::create([
                    'help_id' => $this->help->id,
                    'mitra_id' => $mitra->id,
                    'customer_id' => $customer->id,
                    'message' => $caption,
                    'photo' => $path,
                    'sender_type' => 'mitra',
                    'read_at' => null,
                ]);

                $customer->notify(new \App\Notifications\ChatMessageNotification(
                    $this->help->id,
                    $caption,
                    $mitra->id,
                    $mitra->name
                ));
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to send completion proof chat: ' . $e->getMessage());
        }

        $this->dispatch('show-status-notification', message: 'Bukti pengerjaan berhasil dikirim! Menunggu konfirmasi customer.');
        session()->flash('message', 'Bukti pengerjaan berhasil dikirim! Menunggu konfirmasi customer.');
    }

    public function markCompleted()
    {
        $this->openCompletionModal();
    }

    public function submitCustomerRating()
    {
        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:500',
        ], [
            'rating.required' => 'Rating harus diisi',
            'rating.min' => 'Rating minimal 1 bintang',
            'rating.max' => 'Rating maksimal 5 bintang',
            'review.max' => 'Review maksimal 500 karakter',
        ]);

        // Check if already rated
        $existingRating = \App\Models\Rating::where('help_id', $this->help->id)
            ->where('rater_id', auth()->id())
            ->where('type', 'mitra_to_customer')
            ->first();

        if ($existingRating) {
            session()->flash('error', 'Anda sudah memberikan rating untuk customer ini.');
            return;
        }

        // Check if order is completed
        if (!in_array($this->help->status, ['selesai', 'completed'])) {
            session()->flash('error', 'Rating hanya bisa diberikan untuk pesanan yang sudah selesai.');
            return;
        }

        // Create rating
        \App\Models\Rating::create([
            'help_id' => $this->help->id,
            'user_id' => $this->help->user_id, // Legacy: customer being rated
            'mitra_id' => auth()->id(), // Legacy: mitra giving rating
            'rater_id' => auth()->id(), // New: mitra giving rating
            'ratee_id' => $this->help->user_id, // New: customer receiving rating
            'type' => 'mitra_to_customer',
            'rating' => $this->rating,
            'review' => $this->review,
        ]);

        // Reset form
        $this->rating = 0;
        $this->review = '';

        // Reload help
        $this->help->refresh();
        $this->help->load('rating');

        session()->flash('message', 'Terima kasih atas rating Anda!');
    }

    public function setRating($value)
    {
        $this->rating = $value;
    }

    public function render()
    {
        return view('livewire.mitra.helps.help-detail');
    }
}
