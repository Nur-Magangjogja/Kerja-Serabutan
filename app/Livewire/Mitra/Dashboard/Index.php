<?php

namespace App\Livewire\Mitra\Dashboard;

use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\UserBalance;
use App\Services\HelpTransactionService;
use App\Services\LocationTrackingService;
use App\Services\PartnerOnlineService;
use App\Notifications\HelpTakenNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.mitra')]
class Index extends Component
{
    use WithPagination;

    public $activeTab = 'tersedia'; // tersedia, semua, diproses, selesai

    // ─────────────────────────────────────────────────────────────────────────
    // ONLINE / SEARCHING STATE ACTIONS
    // ─────────────────────────────────────────────────────────────────────────

    public function toggleOnline($lat = null, $lng = null)
    {
        $service = app(PartnerOnlineService::class);
        $user    = auth()->user();
        $state   = $service->getOrCreateState($user->id);

        try {
            if ($state->matching_status === PartnerOnlineState::STATUS_OFFLINE) {
                $service->goOnline($user, $lat ? (float) $lat : null, $lng ? (float) $lng : null);
                $this->dispatch('show-status-notification', message: 'Status Anda sekarang ONLINE (Standby).');
            } else {
                $service->goOffline($user);
                $this->dispatch('show-status-notification', message: 'Status Anda sekarang OFFLINE.');
            }
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[MitraDashboard] toggleOnline error: ' . $e->getMessage());
            session()->flash('error', 'Gagal memperbarui status online.');
        }
    }

    public function startSearching($lat = null, $lng = null)
    {
        $service = app(PartnerOnlineService::class);
        $user    = auth()->user();

        try {
            $service->startSearching($user, $lat ? (float) $lat : null, $lng ? (float) $lng : null);
            $this->dispatch('show-status-notification', message: 'Mode pencarian aktif! Anda akan menerima tawaran otomatis saat ada customer.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[MitraDashboard] startSearching error: ' . $e->getMessage());
            session()->flash('error', 'Gagal memulai pencarian order.');
        }
    }

    public function stopSearching()
    {
        $service = app(PartnerOnlineService::class);
        $user    = auth()->user();

        try {
            $service->stopSearching($user);
            $this->dispatch('show-status-notification', message: 'Pencarian dihentikan. Status kembali ke Online (Standby).');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[MitraDashboard] stopSearching error: ' . $e->getMessage());
            session()->flash('error', 'Gagal menghentikan pencarian.');
        }
    }

    public function goOffline()
    {
        $service = app(PartnerOnlineService::class);
        $user    = auth()->user();

        try {
            $service->goOffline($user);
            $this->dispatch('show-status-notification', message: 'Status Anda sekarang OFFLINE.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[MitraDashboard] goOffline error: ' . $e->getMessage());
            session()->flash('error', 'Gagal mengubah status ke offline.');
        }
    }

    public function heartbeat($lat = null, $lng = null)
    {
        if (!auth()->check()) {
            return;
        }

        app(PartnerOnlineService::class)->heartbeat(
            auth()->user(),
            $lat ? (float) $lat : null,
            $lng ? (float) $lng : null
        );
    }

    public function mount()
    {
        // Check if tab parameter is in the query string
        $tab = request()->query('tab');
        if ($tab && in_array($tab, ['tersedia', 'semua', 'diproses', 'selesai'])) {
            $this->activeTab = $tab;
        }
    }

    #[On('balance-updated')]
    public function refreshBalance()
    {
        $this->dispatch('$refresh');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function takeHelp($helpId, $latitude = null, $longitude = null)
    {
        $help = Help::findOrFail($helpId);

        try {
            app(\App\Services\HelpTransactionService::class)->takeHelp(
                $help,
                auth()->user(),
                $latitude ? (float) $latitude : null,
                $longitude ? (float) $longitude : null
            );

            session()->flash('message', 'Bantuan berhasil diambil! GPS tracking aktif. Segera menuju lokasi customer.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            return;
        } catch (\Throwable $e) {
            \Log::error('[Mitra/Dashboard] takeHelp error: ' . $e->getMessage(), ['help_id' => $helpId]);
            session()->flash('error', 'Terjadi kesalahan saat mengambil bantuan.');
            return;
        }
        
        // Emit event untuk mulai GPS tracking
        $this->dispatch('start-gps-tracking', helpId: $helpId);
        
        $this->setTab('diproses');

        // Redirect mitra to the help detail page so they can see full info and navigation
        return $this->redirectRoute('mitra.helps.detail', ['id' => $helpId]);
    }

    public function acceptOffer(int $dispatchId)
    {
        try {
            $help = app(\App\Services\HelpMatchingService::class)->acceptOffer($dispatchId, auth()->user());
            session()->flash('message', "Tawaran pekerjaan '{$help->title}' berhasil diterima! Segera menuju lokasi customer.");
            $this->dispatch('start-gps-tracking', helpId: $help->id);
            $this->setTab('diproses');
            return $this->redirectRoute('mitra.helps.detail', ['id' => $help->id]);
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[Mitra/Dashboard] acceptOffer error: ' . $e->getMessage(), ['dispatch_id' => $dispatchId]);
            session()->flash('error', 'Gagal menerima tawaran pekerjaan.');
        }
    }

    public function rejectOffer(int $dispatchId, ?string $reason = 'Mitra menolak tawaran')
    {
        try {
            app(\App\Services\HelpMatchingService::class)->rejectOffer($dispatchId, auth()->user(), $reason);
            session()->flash('message', 'Tawaran pekerjaan dilewati. Status kembali mencari order baru.');
            $this->dispatch('show-status-notification', message: 'Tawaran dilewati.');
        } catch (\Throwable $e) {
            Log::error('[Mitra/Dashboard] rejectOffer error: ' . $e->getMessage(), ['dispatch_id' => $dispatchId]);
            session()->flash('error', 'Gagal menolak tawaran.');
        }
    }

    public function handleExpiry(int $dispatchId)
    {
        try {
            app(\App\Services\HelpMatchingService::class)->handleExpiry($dispatchId, true);
            $this->dispatch('show-status-notification', message: 'Batas waktu respon tawaran telah berakhir.');
        } catch (\Throwable $e) {
            Log::error('[Mitra/Dashboard] handleExpiry error: ' . $e->getMessage(), ['dispatch_id' => $dispatchId]);
        }
    }

    public function completeHelp($helpId)
    {
        $help = Help::where('id', $helpId)
            ->where('mitra_id', auth()->id())
            ->firstOrFail();

        $help->update([
            'status'                   => Help::STATUS_WAITING_CONFIRMATION,
            'service_completed_at'     => $help->service_completed_at ?? now(),
            'confirmation_deadline_at' => now()->addHours(24),
        ]);

        session()->flash('message', 'Pekerjaan telah ditandai selesai! Menunggu konfirmasi customer (maks. 24 jam). Dana akan otomatis diteruskan jika tidak ada komplain.');
        $this->setTab('diproses');
    }

    public function render()
    {
        $user = auth()->user();
        $userBalance = UserBalance::where('user_id', $user->id)->first();
        $balance = $userBalance ? $userBalance->balance : 0;

        // Statistik bantuan
        $availableHelpsCount = Help::where('status', 'menunggu_mitra')
            ->where(function ($q) {
                $q->where('dispatch_mode', Help::DISPATCH_MODE_POOL)
                  ->orWhereNull('dispatch_mode');
            })
            ->whereNull('mitra_id')
            ->availableForMitra($user?->id)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            })
            ->count();

        $inProgressCount = Help::where('mitra_id', $user->id)
            ->whereIn('status', ['memperoleh_mitra', 'taken', 'sedang_diproses', 'in_progress', 'partner_on_the_way', 'partner_arrived', 'waiting_customer_confirmation', 'partner_cancel_requested'])
            ->count();

        $completedCount = Help::where('mitra_id', $user->id)
            ->whereIn('status', ['selesai', 'completed'])
            ->count();

        $userCityId = optional($user)->city_id;

        // Data berdasarkan tab
        if ($this->activeTab === 'tersedia' || $this->activeTab === 'semua') {
            $helpsQuery = Help::where('status', 'menunggu_mitra')
                ->where(function ($q) {
                    $q->where('dispatch_mode', Help::DISPATCH_MODE_POOL)
                      ->orWhereNull('dispatch_mode');
                })
                ->whereNull('mitra_id')
                ->availableForMitra($user?->id)
                ->where(function ($q) {
                    $q->whereNull('scheduled_at')
                      ->orWhere('scheduled_at', '<=', now());
                })
                ->with(['user', 'city']);

            if ($userCityId) {
                $helpsQuery->orderByRaw("(city_id = ?) DESC", [$userCityId])->latest();
            } else {
                $helpsQuery->latest();
            }

            $helps = $helpsQuery->paginate(6);
        } elseif ($this->activeTab === 'diproses') {
            $helps = Help::where('mitra_id', $user->id)
                ->whereIn('status', ['memperoleh_mitra', 'taken', 'sedang_diproses', 'in_progress', 'partner_on_the_way', 'partner_arrived', 'waiting_customer_confirmation', 'partner_cancel_requested'])
                ->with(['user', 'city'])
                ->latest()
                ->paginate(6);
        } elseif ($this->activeTab === 'selesai') {
            $helps = Help::where('mitra_id', $user->id)
                ->whereIn('status', ['selesai', 'completed'])
                ->with(['user', 'city'])
                ->latest()
                ->paginate(6);
        } else {
            $helps = Help::where('mitra_id', $user->id)
                ->with(['user', 'city'])
                ->latest()
                ->paginate(6);
        }

        // Rekomendasi bantuan (berdasarkan kota atau kategori)
        $recommendedHelps = Help::where('status', 'menunggu_mitra')
            ->where(function ($q) {
                $q->where('dispatch_mode', Help::DISPATCH_MODE_POOL)
                  ->orWhereNull('dispatch_mode');
            })
            ->whereNull('mitra_id')
            ->availableForMitra($user?->id)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            })
            ->when($userCityId, function ($query, $cityId) {
                return $query->where('city_id', $cityId);
            })
            ->with(['user', 'city'])
            ->latest()
            ->take(3)
            ->get();

        // Bantuan terbaru
        $latestHelps = Help::where('status', 'menunggu_mitra')
            ->where(function ($q) {
                $q->where('dispatch_mode', Help::DISPATCH_MODE_POOL)
                  ->orWhereNull('dispatch_mode');
            })
            ->whereNull('mitra_id')
            ->availableForMitra($user?->id)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            })
            ->with(['user', 'city'])
            ->latest()
            ->take(5)
            ->get();

        // Bantuan terdekat
        $nearbyHelps = Help::where('status', 'menunggu_mitra')
            ->where(function ($q) {
                $q->where('dispatch_mode', Help::DISPATCH_MODE_POOL)
                  ->orWhereNull('dispatch_mode');
            })
            ->whereNull('mitra_id')
            ->availableForMitra($user?->id)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            })
            ->when($userCityId, function ($query, $cityId) {
                return $query->where('city_id', $cityId);
            })
            ->with(['user', 'city'])
            ->take(3)
            ->get();

        // Check unread messages count across all active helps for this mitra
        $unreadChatCount = 0;
        try {
            if ($user && Schema::hasTable('chats')) {
                $myHelpIds = Help::where('mitra_id', $user->id)
                    ->whereIn('status', ['memperoleh_mitra', 'taken', 'sedang_diproses', 'in_progress', 'partner_on_the_way', 'partner_arrived', 'waiting_customer_confirmation'])
                    ->pluck('id');

                $unreadChatCount = \App\Models\Chat::whereIn('help_id', $myHelpIds)
                    ->where('sender_id', '!=', $user->id)
                    ->where('is_read', false)
                    ->count();
            }
        } catch (\Throwable $e) {
            // ignore if Chat table or column is missing
        }

        // Active task checking
        $activeTask = $user ? Help::where('mitra_id', $user->id)->active()->first() : null;

        $onlineState = $user ? app(PartnerOnlineService::class)->getOrCreateState($user->id) : null;

        // Active Offer Checking (jika mitra sedang berstatus OFFER_PENDING)
        $activeOffer = null;
        if ($onlineState && $onlineState->matching_status === PartnerOnlineState::STATUS_OFFER_PENDING && $onlineState->current_help_id) {
            $activeOffer = \App\Models\HelpDispatch::with('help.user', 'help.city')
                ->where('help_id', $onlineState->current_help_id)
                ->where('mitra_id', $user->id)
                ->where('status', \App\Models\HelpDispatch::STATUS_OFFERED)
                ->latest()
                ->first();

            if ($activeOffer && $activeOffer->expires_at && $activeOffer->expires_at->isPast()) {
                app(\App\Services\HelpMatchingService::class)->handleExpiry($activeOffer->id, true);
                $onlineState->refresh();
                $activeOffer = null;
            } elseif (!$activeOffer) {
                app(\App\Services\PartnerOnlineService::class)->revertFromOfferPending($user->id, $onlineState->current_help_id);
                $onlineState->refresh();
            }
        }

        return view('livewire.mitra.dashboard.index', [
            'helps' => $helps,
            'balance' => $balance,
            'availableHelpsCount' => $availableHelpsCount,
            'inProgressCount' => $inProgressCount,
            'completedCount' => $completedCount,
            'user' => $user,
            'recommendedHelps' => $recommendedHelps,
            'latestHelps' => $latestHelps,
            'nearbyHelps' => $nearbyHelps,
            'unreadChatCount' => $unreadChatCount,
            'activeTask' => $activeTask,
            'onlineState' => $onlineState,
            'activeOffer' => $activeOffer,
        ]);
    }
}
