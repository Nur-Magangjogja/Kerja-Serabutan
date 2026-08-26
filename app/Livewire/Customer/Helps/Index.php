<?php

namespace App\Livewire\Customer\Helps;

use App\Models\City;
use App\Models\Help;
use App\Models\Rating;
use App\Services\CitySearchService;
use App\Services\HelpTransactionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    protected $queryString = [
        'statusFilter' => ['except' => 'menunggu_mitra'],
    ];

    public $statusFilter = 'menunggu_mitra';

    /** Status yang masuk dalam tab "Diproses" */
    protected $diprosesStatuses = [
        'taken',
        'memperoleh_mitra',
        'partner_on_the_way',
        'partner_arrived',
        'in_progress',
        'sedang_diproses',
        'partner_cancel_requested',
    ];

    public function mount()
    {
        if (request()->has('statusFilter') && !empty(request()->get('statusFilter'))) {
            $this->statusFilter = request()->get('statusFilter');
            return;
        }

        $user = auth()->user();
        if ($user) {
            // Urutan prioritas proses paling akhir (kecuali selesai):
            // 1. Menunggu konfirmasi penyelesaian dari customer (tahap terakhir sebelum selesai)
            $hasWaitingConfirmation = Help::where('user_id', $user->id)
                ->where('status', 'waiting_customer_confirmation')
                ->exists();

            if ($hasWaitingConfirmation) {
                $this->statusFilter = 'waiting_customer_confirmation';
                return;
            }

            // 2. Sedang berlangsung / diproses / di perjalanan / pengerjaan / minta pembatalan
            $hasDiproses = Help::where('user_id', $user->id)
                ->whereIn('status', $this->diprosesStatuses)
                ->exists();

            if ($hasDiproses) {
                $this->statusFilter = 'diproses';
                return;
            }

            // 3. Menunggu / mencari rekan jasa (baru dibuat)
            $hasWaitingMitra = Help::where('user_id', $user->id)
                ->whereIn('status', ['menunggu_mitra', 'mencari_mitra', 'menunggu_pembayaran', 'pending'])
                ->exists();

            if ($hasWaitingMitra) {
                $this->statusFilter = 'menunggu_mitra';
                return;
            }

            // 4. Jika tidak ada yang aktif, cek apakah ada riwayat selesai/batal
            $hasCompleted = Help::where('user_id', $user->id)
                ->whereIn('status', ['selesai', 'completed', 'dibatalkan', 'cancelled'])
                ->exists();

            if ($hasCompleted) {
                $this->statusFilter = 'selesai';
                return;
            }
        }

        $this->statusFilter = 'menunggu_mitra';
    }

    // ─── Edit modal ─────────────────────────────────────────────────────────
    public $editingHelp            = null;
    public $editTitle              = null;
    public $editDescription        = null;
    public $editAmount             = null;
    public $editLocation           = null;
    public $editFullAddress        = null;
    public $editEquipmentProvided  = null;
    public $editCityId             = null;
    public $editLatitude           = null;
    public $editLongitude          = null;
    public $editPhoto              = null;
    public $editExistingPhoto      = null;
    public $editSearchResults      = [];
    public $editCityQuery          = null;
    public $cities                 = null;

    // ─── Delete modal ────────────────────────────────────────────────────────
    public $showDeleteConfirm = false;
    public $deletingHelpId    = null;

    // ─── Completion confirmation ──────────────────────────────────────────────
    public $confirmingHelpId = null;

    // ─── Detail modal ─────────────────────────────────────────────────────────
    public $selectedHelpData = null;

    // ─── Rating ──────────────────────────────────────────────────────────────
    public $pendingRating        = null;
    public $pendingHelpForRating = null;
    public $ratingComment        = null;

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────────────────────────────────────

    public function confirmDelete($id)
    {
        $help = Help::find($id);
        if (!$help || $help->user_id !== auth()->id()) {
            session()->flash('error', 'Anda tidak memiliki izin untuk membatalkan bantuan ini.');
            return;
        }
        $this->deletingHelpId    = $id;
        $this->showDeleteConfirm = true;
    }

    public function cancelDelete()
    {
        $this->deletingHelpId    = null;
        $this->showDeleteConfirm = false;
    }

    public function deleteConfirmed()
    {
        if (!$this->deletingHelpId) {
            $this->showDeleteConfirm = false;
            return;
        }

        $help = Help::find($this->deletingHelpId);
        if (!$help || $help->user_id !== auth()->id()) {
            session()->flash('error', 'Permintaan tidak ditemukan atau Anda tidak memiliki izin.');
            $this->cancelDelete();
            return;
        }

        try {
            app(\App\Services\HelpTransactionService::class)->customerCancelHelp($help, auth()->user());
            session()->flash('message', 'Permintaan bantuan berhasil dibatalkan dan dana Anda telah dikembalikan 100% ke saldo akun.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('[CustomerIndex] deleteConfirmed error: ' . $e->getMessage());
            session()->flash('error', 'Gagal membatalkan permintaan bantuan.');
        }

        $this->cancelDelete();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // COMPLETION CONFIRMATION
    // ─────────────────────────────────────────────────────────────────────────

    public function confirmCompletion($id)
    {
        $help = Help::find($id);
        if (!$help || $help->user_id !== auth()->id()) {
            session()->flash('error', 'Anda tidak memiliki izin untuk mengonfirmasi pesanan ini.');
            return;
        }
        $this->confirmingHelpId = $id;
    }

    public function completeConfirmed()
    {
        if (!$this->confirmingHelpId) return;

        $help = Help::find($this->confirmingHelpId);
        if (!$help || $help->user_id !== auth()->id()) {
            session()->flash('error', 'Permintaan tidak ditemukan atau Anda tidak memiliki izin.');
            $this->confirmingHelpId = null;
            return;
        }

        try {
            app(HelpTransactionService::class)->customerConfirmCompletion($help, auth()->user());
            session()->flash('message', 'Permintaan telah ditandai selesai.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[CustomerIndex] completeConfirmed error: ' . $e->getMessage());
            session()->flash('error', 'Gagal menandai permintaan sebagai selesai.');
        }

        $this->confirmingHelpId = null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PARTNER CANCELLATION (dari daftar, bukan detail)
    // ─────────────────────────────────────────────────────────────────────────

    public function acceptPartnerCancellation($helpId)
    {
        $help = Help::find($helpId);
        if (!$help || $help->user_id !== auth()->id()) {
            session()->flash('error', 'Permintaan tidak ditemukan atau Anda tidak memiliki izin.');
            return;
        }

        try {
            app(HelpTransactionService::class)->customerAcceptCancel($help, auth()->user());
            session()->flash('message', 'Pembatalan diterima. Bantuan telah dikembalikan ke daftar pencarian untuk Rekan Jasa lain.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[CustomerIndex] acceptPartnerCancellation error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan.');
        }
    }

    public function rejectPartnerCancellation($helpId)
    {
        $help = Help::find($helpId);
        if (!$help || $help->user_id !== auth()->id()) {
            session()->flash('error', 'Permintaan tidak ditemukan atau Anda tidak memiliki izin.');
            return;
        }

        try {
            app(HelpTransactionService::class)->customerRejectCancel($help, auth()->user());
            session()->flash('message', 'Permintaan pembatalan ditolak. Rekan Jasa diminta untuk melanjutkan pekerjaan.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[CustomerIndex] rejectPartnerCancellation error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RATING
    // ─────────────────────────────────────────────────────────────────────────

    public function setRating($helpId, $value)
    {
        $this->pendingHelpForRating = $helpId;
        $this->pendingRating        = (int) $value;
    }

    public function submitRating($helpId)
    {
        $this->validate([
            'pendingRating' => 'required|integer|min:1|max:5',
            'ratingComment' => 'nullable|string|max:1000',
        ]);

        $help = Help::findOrFail($helpId);
        if ($help->user_id !== auth()->id()) {
            session()->flash('error', 'Anda tidak memiliki izin untuk memberi rating pada bantuan ini.');
            return;
        }

        $ratingRecord = Rating::updateOrCreate(
            ['help_id' => $helpId, 'user_id' => auth()->id()],
            [
                'mitra_id' => $help->mitra_id,
                'rater_id' => auth()->id(),
                'ratee_id' => $help->mitra_id,
                'type'     => 'customer_to_mitra',
                'rating'   => $this->pendingRating,
                'review'   => $this->ratingComment,
            ]
        );

        if ($help->mitra) {
            try {
                $help->mitra->notify(new \App\Notifications\RatingReceivedNotification($help, $ratingRecord, auth()->user()));
            } catch (\Throwable $e) {
                Log::warning('[CustomerIndex] Failed to notify mitra of rating: ' . $e->getMessage());
            }
        }

        session()->flash('message', 'Terima kasih — rating Anda telah disimpan.');
        $this->pendingHelpForRating = null;
        $this->pendingRating        = null;
        $this->ratingComment        = null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DETAIL MODAL
    // ─────────────────────────────────────────────────────────────────────────

    public function showHelp($id)
    {
        $help = Help::with(['city', 'user', 'mitra'])->find($id);
        if (!$help || $help->user_id !== auth()->id()) {
            return;
        }

        $this->selectedHelpData = [
            'id'                 => $help->id,
            'title'              => $help->title,
            'description'        => $help->description,
            'amount'             => $help->amount,
            'photo'              => $help->photo,
            'location'           => $help->location,
            'full_address'       => $help->full_address,
            'equipment_provided' => $help->equipment_provided,
            'user_name'          => $help->user?->name,
            'city_name'          => $help->city?->name,
            'status'             => $help->status,
            'created_at_human'   => $help->created_at?->diffForHumans(),
            'latitude'           => $help->latitude,
            'longitude'          => $help->longitude,
        ];
    }

    public function closeHelp()
    {
        $this->selectedHelpData = null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EDIT FLOW
    // ─────────────────────────────────────────────────────────────────────────

    public function editHelp($id)
    {
        $help = Help::findOrFail($id);

        if ($help->user_id !== auth()->id()) {
            session()->flash('error', 'Anda tidak memiliki izin untuk mengedit bantuan ini.');
            return;
        }

        $this->editingHelp           = $help->id;
        $this->editTitle             = $help->title;
        $this->editDescription       = $help->description;
        $this->editAmount            = $help->amount;
        $this->editLocation          = $help->location;
        $this->editFullAddress       = $help->full_address;
        $this->editEquipmentProvided = $help->equipment_provided;
        $this->editCityId            = $help->city_id;
        $this->editLatitude          = $help->latitude;
        $this->editLongitude         = $help->longitude;
        $this->editExistingPhoto     = $help->photo;

        $this->dispatch('open-edit', id: $help->id, title: $help->title, description: $help->description,
            amount: $help->amount, location: $help->location, city_id: $help->city_id,
            latitude: $help->latitude, longitude: $help->longitude);
    }

    public function setEditCityId($id)
    {
        $this->editCityId = $id;
        $city = City::find($id);
        if ($city) {
            $this->editCityQuery    = $city->name . ' — ' . $city->province;
        }
        $this->editSearchResults = [];
    }

    public function updatedEditCityQuery($value)
    {
        $this->editSearchResults = app(CitySearchService::class)->search($value, 10);
    }

    public function saveEdit()
    {
        $this->validate([
            'editTitle'             => 'required|string|max:255',
            'editDescription'       => 'required|string',
            'editAmount'            => 'required|numeric|min:10000|max:100000000',
            'editLocation'          => 'nullable|string|max:255',
            'editFullAddress'       => 'nullable|string|max:500',
            'editEquipmentProvided' => 'nullable|string|max:1000',
            'editCityId'            => 'required|exists:cities,id',
            'editPhoto'             => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $help = Help::findOrFail($this->editingHelp);
        if ($help->user_id !== auth()->id()) {
            session()->flash('error', 'Anda tidak memiliki izin untuk mengedit bantuan ini.');
            return;
        }

        $updateData = [
            'title'              => $this->editTitle,
            'description'        => $this->editDescription,
            'amount'             => $this->editAmount,
            'location'           => $this->editLocation,
            'full_address'       => $this->editFullAddress,
            'equipment_provided' => $this->editEquipmentProvided,
            'city_id'            => $this->editCityId,
            'latitude'           => $this->editLatitude,
            'longitude'          => $this->editLongitude,
        ];

        if ($this->editPhoto) {
            if ($help->photo) {
                Storage::disk('public')->delete($help->photo);
            }
            $updateData['photo'] = $this->editPhoto->store('helps', 'public');
        }

        $help->update($updateData);
        session()->flash('message', 'Perubahan bantuan berhasil disimpan.');
        $this->closeEdit();
    }

    public function closeEdit()
    {
        $this->editingHelp           = null;
        $this->editTitle             = null;
        $this->editDescription       = null;
        $this->editAmount            = null;
        $this->editLocation          = null;
        $this->editFullAddress       = null;
        $this->editEquipmentProvided = null;
        $this->editCityId            = null;
        $this->editLatitude          = null;
        $this->editLongitude         = null;
        $this->editPhoto             = null;
        $this->editExistingPhoto     = null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RENDER
    // ─────────────────────────────────────────────────────────────────────────

    public function render()
    {
        $user = auth()->user();

        // Auto-cancel bantuan yang kadaluwarsa secara on-the-fly jika batas waktu terlewati
        if ($user) {
            $now = \Carbon\Carbon::now();
            $hours = \App\Models\AppSetting::getHelpAutoCancelHours();
            $cutoff = $now->copy()->subHours($hours);
            $expiredWaiting = Help::where('user_id', $user->id)
                ->whereNull('mitra_id')
                ->whereIn('status', ['menunggu_mitra', 'mencari_mitra', 'menunggu_pembayaran', 'pending'])
                ->where(function ($q) use ($now, $cutoff) {
                    $q->where(function ($sub) use ($now) {
                        $sub->whereNotNull('expires_at')
                            ->where('expires_at', '<=', $now);
                    })
                    ->orWhere(function ($sub) use ($now) {
                        $sub->whereNotNull('scheduled_at')
                            ->where('scheduled_at', '<=', $now);
                    })
                    ->orWhere(function ($sub) use ($cutoff) {
                        $sub->whereNull('expires_at')
                            ->whereNull('scheduled_at')
                            ->where('created_at', '<=', $cutoff);
                    });
                })
                ->get();

            foreach ($expiredWaiting as $expHelp) {
                if ($expHelp->expires_at && \Carbon\Carbon::parse($expHelp->expires_at)->isPast()) {
                    $reason = 'Batas waktu pencarian Rekan Jasa yang ditentukan telah berakhir';
                } elseif ($expHelp->scheduled_at && \Carbon\Carbon::parse($expHelp->scheduled_at)->isPast()) {
                    $reason = 'Waktu jadwal bantuan telah terlewat tanpa Rekan Jasa tersedia';
                } else {
                    $reason = "Tidak ada Rekan Jasa yang mengambil bantuan dalam batas waktu {$hours} jam";
                }
                app(\App\Services\HelpTransactionService::class)->autoCancelExpiredHelp($expHelp, $reason);
            }
        }

        $query = Help::where('user_id', $user->id)
            ->with([
                'city',
                'mitra',
                'rating' => fn($q) => $q->where('user_id', $user->id),
            ])
            ->withCount('chatMessages');

        if ($this->statusFilter === 'diproses') {
            $query->whereIn('status', $this->diprosesStatuses);
        } elseif ($this->statusFilter === 'menunggu_mitra') {
            $query->whereIn('status', ['menunggu_mitra', 'mencari_mitra', 'menunggu_pembayaran', 'pending']);
        } elseif ($this->statusFilter === 'waiting_customer_confirmation') {
            $query->where('status', 'waiting_customer_confirmation');
        } elseif ($this->statusFilter === 'selesai') {
            $query->whereIn('status', ['selesai', 'completed', 'dibatalkan', 'cancelled']);
        } elseif (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        $helps = $query->latest()->paginate(10);

        $counts = [
            'menunggu'   => Help::where('user_id', $user->id)->whereIn('status', ['menunggu_mitra', 'mencari_mitra', 'menunggu_pembayaran', 'pending'])->count(),
            'diproses'   => Help::where('user_id', $user->id)->whereIn('status', $this->diprosesStatuses)->count(),
            'konfirmasi' => Help::where('user_id', $user->id)->where('status', 'waiting_customer_confirmation')->count(),
            'selesai'    => Help::where('user_id', $user->id)->whereIn('status', ['selesai', 'completed', 'dibatalkan', 'cancelled'])->count(),
        ];

        $this->cities = City::where('is_active', true)->orderBy('name')->get();

        return view('livewire.customer.helps.index', [
            'helps'  => $helps,
            'cities' => $this->cities,
            'counts' => $counts,
        ]);
    }
}
