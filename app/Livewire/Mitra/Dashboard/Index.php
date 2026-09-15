<?php

namespace App\Livewire\Mitra\Dashboard;

use App\Actions\Matching\MitraMatchingActions;
use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Services\Dashboard\DashboardQueryService;
use App\Services\DashboardStatsService;
use App\Services\HelpCancellationService;
use App\Services\HelpTransactionService;
use App\Services\PartnerOnlineService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.mitra')]
class Index extends Component
{
    use WithPagination;

    public $activeTab = 'tersedia'; // tersedia, semua, diproses, selesai

    // ─────────────────────────────────────────────────────────────────────────
    // ONLINE / SEARCHING STATE ACTIONS (Delegated to MitraMatchingActions)
    // ─────────────────────────────────────────────────────────────────────────

    public function toggleOnline($lat = null, $lng = null): void
    {
        $user = auth()->user();
        if (!$user) return;

        $state = app(PartnerOnlineService::class)->getOrCreateState($user->id);
        $actions = app(MitraMatchingActions::class);

        if ($state->matching_status === PartnerOnlineState::STATUS_OFFLINE) {
            $res = $actions->goOnline($user, $lat ? (float) $lat : null, $lng ? (float) $lng : null);
        } else {
            $res = $actions->goOffline($user);
        }

        if ($res['success']) {
            $this->dispatch('show-status-notification', message: $res['message']);
            $this->dispatch('partner-state-changed');
        } else {
            session()->flash('error', $res['message']);
        }
    }

    public function startSearching($lat = null, $lng = null): void
    {
        $user = auth()->user();
        if (!$user) return;

        $res = app(MitraMatchingActions::class)->startSearching($user, $lat ? (float) $lat : null, $lng ? (float) $lng : null);

        if ($res['success']) {
            $this->dispatch('show-status-notification', message: $res['message']);
            $this->dispatch('partner-state-changed');
        } else {
            session()->flash('error', $res['message']);
        }
    }

    public function stopSearching(): void
    {
        $user = auth()->user();
        if (!$user) return;

        $res = app(MitraMatchingActions::class)->stopSearching($user);

        if ($res['success']) {
            $this->dispatch('show-status-notification', message: $res['message']);
            $this->dispatch('partner-state-changed');
        } else {
            session()->flash('error', $res['message']);
        }
    }

    public function goOffline(): void
    {
        $user = auth()->user();
        if (!$user) return;

        $res = app(MitraMatchingActions::class)->goOffline($user);

        if ($res['success']) {
            $this->dispatch('show-status-notification', message: $res['message']);
            $this->dispatch('partner-state-changed');
        } else {
            session()->flash('error', $res['message']);
        }
    }

    public function heartbeat($lat = null, $lng = null): void
    {
        $user = auth()->user();
        if (!$user) return;

        app(MitraMatchingActions::class)->heartbeat($user, $lat ? (float) $lat : null, $lng ? (float) $lng : null);
    }

    public function mount(): void
    {
        $tab = request()->query('tab');
        if ($tab && in_array($tab, ['tersedia', 'semua', 'diproses', 'selesai'], true)) {
            $this->activeTab = $tab;
        }
    }

    #[On('balance-updated')]
    public function refreshBalance(): void
    {
        $this->clearDashboardCache();
        $this->dispatch('$refresh');
    }

    public function setTab($tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function takeHelp($helpId, $latitude = null, $longitude = null)
    {
        $help = Help::findOrFail($helpId);

        try {
            app(HelpTransactionService::class)->takeHelp(
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
            Log::error('[Mitra/Dashboard] takeHelp error: ' . $e->getMessage(), ['help_id' => $helpId]);
            session()->flash('error', 'Terjadi kesalahan saat mengambil bantuan.');
            return;
        }

        $this->dispatch('start-gps-tracking', helpId: $helpId);
        $this->setTab('diproses');

        return $this->redirectRoute('mitra.helps.detail', ['id' => $helpId]);
    }

    public function acceptOffer(int $dispatchId)
    {
        $user = auth()->user();
        if (!$user) return;

        $res = app(MitraMatchingActions::class)->acceptOffer($dispatchId, $user);

        if ($res['success']) {
            $this->clearDashboardCache();
            session()->flash('message', $res['message']);
            $this->dispatch('start-gps-tracking', helpId: $res['help']->id);
            $this->setTab('diproses');
            return $this->redirectRoute('mitra.helps.detail', ['id' => $res['help']->id]);
        }

        session()->flash('error', $res['message']);
    }

    public function rejectOffer(int $dispatchId, ?string $reason = 'Mitra menolak tawaran'): void
    {
        $user = auth()->user();
        if (!$user) return;

        $res = app(MitraMatchingActions::class)->rejectOffer($dispatchId, $user, $reason);

        if ($res['success']) {
            $this->clearDashboardCache();
            session()->flash('message', $res['message']);
            $this->dispatch('show-status-notification', message: $res['is_demoted_to_standby'] ? 'Status Anda beralih ke Standby.' : 'Tawaran dilewati. Tetap mencari order...');
        } else {
            session()->flash('error', $res['message']);
        }
    }

    public function handleExpiry(int $dispatchId): void
    {
        $user = auth()->user();
        if (!$user) return;

        $res = app(MitraMatchingActions::class)->handleExpiry($dispatchId, $user);

        if ($res['success']) {
            $this->clearDashboardCache();
            session()->flash('message', $res['message']);
            $this->dispatch('show-status-notification', message: $res['is_demoted_to_standby'] ? 'Waktu habis. Status beralih ke Standby.' : 'Waktu habis. Melanjutkan pencarian...');
        }
    }

    public function completeHelp($helpId): void
    {
        $help = Help::where('id', $helpId)
            ->where('mitra_id', auth()->id())
            ->firstOrFail();

        try {
            app(HelpTransactionService::class)->submitCompletion($help, auth()->user(), null, 'Selesai dari dashboard');

            $this->clearDashboardCache();
            session()->flash('message', 'Pekerjaan selesai! Dana escrow diamankan (maks. 24 jam). Status Anda kembali Bebas Tugas — Klik "Cari Order" untuk mulai pekerjaan baru.');
            $this->setTab('diproses');
        } catch (\Throwable $e) {
            Log::error('[MitraDashboard] completeHelp error: ' . $e->getMessage(), ['help_id' => $helpId]);
            session()->flash('error', 'Terjadi kesalahan saat menyelesaikan pekerjaan: ' . $e->getMessage());
        }
    }

    public function clearDashboardCache(?int $userId = null): void
    {
        $uid = $userId ?? auth()->id();
        if (!$uid) return;

        $userCityId = auth()->user()?->city_id;
        app(DashboardStatsService::class)->clearStatsCache($uid, $userCityId);
    }

    public function render()
    {
        $user = auth()->user();
        if (!$user) {
            return view('livewire.mitra.dashboard.index');
        }

        // Lazy sweep: Batalkan pesanan kedaluwarsa secara otomatis
        app(HelpCancellationService::class)->sweepAndAutoCancelExpiredHelps();

        $queryService = app(DashboardQueryService::class);
        $statsService = app(DashboardStatsService::class);

        // 1. Summary Stats
        $stats = $queryService->getSummaryStats($user);

        // 2. Paginated Helps by Tab
        $helps = $queryService->getHelpListByTab($user, $this->activeTab, 6);

        // 3. Recommended, Latest, Nearby, Unread Chat
        $recommendedHelps = $statsService->getRecommendedHelps($user, 3);
        $latestHelps      = $statsService->getLatestHelps($user, 5);
        $nearbyHelps      = $statsService->getNearbyHelps($user, 3);
        $unreadChatCount  = $statsService->getUnreadChatCount($user);

        // 4. Partner Online State & Tasks
        $onlineState = app(PartnerOnlineService::class)->getOrCreateState($user->id);
        $taskData    = $queryService->getActiveAndPendingTasks($user);

        return view('livewire.mitra.dashboard.index', [
            'helps'                    => $helps,
            'balance'                  => $stats['balance'],
            'availableHelpsCount'      => $stats['available'],
            'inProgressCount'          => $stats['inProgress'],
            'completedCount'           => $stats['completed'],
            'user'                     => $user,
            'recommendedHelps'         => $recommendedHelps,
            'latestHelps'              => $latestHelps,
            'nearbyHelps'              => $nearbyHelps,
            'unreadChatCount'          => $unreadChatCount,
            'activeTask'               => $taskData['activeTask'],
            'waitingConfirmationHelps' => $taskData['waitingConfirmationHelps'],
            'onlineState'              => $onlineState,
        ]);
    }
}
