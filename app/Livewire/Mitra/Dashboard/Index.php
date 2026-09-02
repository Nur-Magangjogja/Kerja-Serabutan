<?php

namespace App\Livewire\Mitra\Dashboard;

use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\UserBalance;
use App\Services\HelpTransactionService;
use App\Services\LocationTrackingService;
use App\Services\PartnerOnlineService;
use App\Notifications\HelpTakenNotification;
use Illuminate\Support\Facades\Cache;
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
        $this->clearDashboardCache();
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

            $this->clearDashboardCache();
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
            $this->clearDashboardCache();
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
            $this->clearDashboardCache();
            $state = app(\App\Services\PartnerOnlineService::class)->getOrCreateState(auth()->id());
            if ($state->matching_status === PartnerOnlineState::STATUS_ONLINE) {
                session()->flash('message', 'Tawaran dilewati. Batas penolakan berturut-turut tercapai, status dialihkan ke Standby. Klik "Cari Order" saat Anda siap menerima pesanan.');
                $this->dispatch('show-status-notification', message: 'Status Anda beralih ke Standby.');
            } else {
                session()->flash('message', 'Tawaran dilewati. Status tetap AKTIF mencari order baru di sekitar Anda.');
                $this->dispatch('show-status-notification', message: 'Tawaran dilewati. Tetap mencari order...');
            }
        } catch (\Throwable $e) {
            Log::error('[Mitra/Dashboard] rejectOffer error: ' . $e->getMessage(), ['dispatch_id' => $dispatchId]);
            session()->flash('error', 'Gagal menolak tawaran.');
        }
    }

    public function handleExpiry(int $dispatchId)
    {
        try {
            app(\App\Services\HelpMatchingService::class)->handleExpiry($dispatchId, true);
            $this->clearDashboardCache();
            $state = app(\App\Services\PartnerOnlineService::class)->getOrCreateState(auth()->id());
            if ($state->matching_status === PartnerOnlineState::STATUS_ONLINE) {
                session()->flash('message', 'Waktu tawaran habis. Status dialihkan ke Standby. Klik "Cari Order" untuk mulai matching lagi.');
                $this->dispatch('show-status-notification', message: 'Waktu habis. Status beralih ke Standby.');
            } else {
                session()->flash('message', 'Waktu tawaran habis. Sistem melanjutkan pencarian order baru untuk Anda.');
                $this->dispatch('show-status-notification', message: 'Waktu habis. Melanjutkan pencarian...');
            }
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

        // Lepaskan status BUSY mitra agar dapat langsung mencari pesanan baru
        app(\App\Services\PartnerOnlineService::class)->releaseBusy(auth()->id(), $help->id);
        $this->clearDashboardCache();

        session()->flash('message', 'Pekerjaan selesai! Dana escrow diamankan (maks. 24 jam). Status Anda kembali Bebas Tugas — Klik "Cari Order" untuk mulai pekerjaan baru.');
        $this->setTab('diproses');
    }

    public function clearDashboardCache(?int $userId = null): void
    {
        $uid = $userId ?? auth()->id();
        if (!$uid) return;

        $userCityId = auth()->user()?->city_id;
        app(\App\Services\DashboardStatsService::class)->clearStatsCache($uid, $userCityId);
    }

    public function render()
    {
        $user = auth()->user();
        if (!$user) {
            return view('livewire.mitra.dashboard.index');
        }

        $statsService = app(\App\Services\DashboardStatsService::class);

        // 1. Statistik ringkas & saldo di-cache via DashboardStatsService
        $stats               = $statsService->getSummaryStats($user);
        $balance             = $stats['balance'];
        $availableHelpsCount = $stats['available'];
        $inProgressCount     = $stats['inProgress'];
        $completedCount      = $stats['completed'];

        $userCityId = $user->city_id;

        // 2. Data paginasi berdasarkan tab aktif
        if ($this->activeTab === 'tersedia' || $this->activeTab === 'semua') {
            $helpsQuery = Help::where('status', 'menunggu_mitra')
                ->where(function ($q) {
                    $q->where('dispatch_mode', Help::DISPATCH_MODE_POOL)
                      ->orWhereNull('dispatch_mode');
                })
                ->whereNull('mitra_id')
                ->availableForMitra($user->id)
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

        // 3. Rekomendasi, Terbaru, dan Terdekat via DashboardStatsService
        $recommendedHelps = $statsService->getRecommendedHelps($user, 3);
        $latestHelps      = $statsService->getLatestHelps($user, 5);
        $nearbyHelps      = $statsService->getNearbyHelps($user, 3);

        // 4. Unread Chat Count via DashboardStatsService
        $unreadChatCount = $statsService->getUnreadChatCount($user);

        // 5. Real-Time State & Task Check (Terkonsolidasi dalam 1 Query Index Tunggal)
        $onlineState = app(PartnerOnlineService::class)->getOrCreateState($user->id);

        $relevantMitraHelps = Help::where('mitra_id', $user->id)
            ->where(function ($q) {
                $q->active()
                  ->orWhere('status', Help::STATUS_WAITING_CONFIRMATION);
            })
            ->with(['user', 'city'])
            ->latest()
            ->get();

        $activeTask = $relevantMitraHelps->first(function ($h) {
            return in_array($h->status, [
                Help::STATUS_TAKEN,
                'memperoleh_mitra',
                Help::STATUS_PARTNER_ON_THE_WAY,
                Help::STATUS_PARTNER_ARRIVED,
                Help::STATUS_IN_PROGRESS,
                'sedang_diproses',
                Help::STATUS_PARTNER_CANCEL_REQUESTED,
            ]);
        });

        $waitingConfirmationHelps = $relevantMitraHelps->filter(function ($h) {
            return $h->status === Help::STATUS_WAITING_CONFIRMATION;
        })->take(3)->values();

        return view('livewire.mitra.dashboard.index', [
            'helps'                    => $helps,
            'balance'                  => $balance,
            'availableHelpsCount'      => $availableHelpsCount,
            'inProgressCount'          => $inProgressCount,
            'completedCount'           => $completedCount,
            'user'                     => $user,
            'recommendedHelps'         => $recommendedHelps,
            'latestHelps'              => $latestHelps,
            'nearbyHelps'              => $nearbyHelps,
            'unreadChatCount'          => $unreadChatCount,
            'activeTask'               => $activeTask,
            'waitingConfirmationHelps' => $waitingConfirmationHelps,
            'onlineState'              => $onlineState,
        ]);
    }
}
