<?php

namespace App\Livewire\Mitra\Dashboard;

use App\Models\HelpDispatch;
use App\Models\PartnerOnlineState;
use App\Services\HelpMatchingService;
use App\Services\PartnerOnlineService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class OfferRadarWidget extends Component
{
    public ?int $userId = null;

    public function mount(): void
    {
        $this->userId = auth()->id();
    }

    public function goOnline($latitude = null, $longitude = null): void
    {
        $service = app(PartnerOnlineService::class);
        $user    = auth()->user();

        if (!$user) return;

        try {
            $service->goOnline($user, $latitude ? (float) $latitude : null, $longitude ? (float) $longitude : null);
            $this->dispatch('show-status-notification', message: 'Status Anda sekarang ONLINE (Standby).');
            $this->dispatch('partner-state-changed');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[OfferRadarWidget] goOnline error: ' . $e->getMessage());
            session()->flash('error', 'Gagal memperbarui status online.');
        }
    }

    public function startSearching($latitude = null, $longitude = null): void
    {
        $service = app(PartnerOnlineService::class);
        $user    = auth()->user();

        if (!$user) return;

        try {
            $service->startSearching($user, $latitude ? (float) $latitude : null, $longitude ? (float) $longitude : null);
            $this->dispatch('show-status-notification', message: 'Mode pencarian aktif! Radar mencari order di sekitar Anda.');
            $this->dispatch('partner-state-changed');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[OfferRadarWidget] startSearching error: ' . $e->getMessage());
            session()->flash('error', 'Gagal memulai pencarian order.');
        }
    }

    public function stopSearching(): void
    {
        $service = app(PartnerOnlineService::class);
        $user    = auth()->user();

        if (!$user) return;

        try {
            $service->stopSearching($user);
            $this->dispatch('show-status-notification', message: 'Pencarian dihentikan. Status kembali ke Online (Standby).');
            $this->dispatch('partner-state-changed');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[OfferRadarWidget] stopSearching error: ' . $e->getMessage());
            session()->flash('error', 'Gagal menghentikan pencarian.');
        }
    }

    public function goOffline(): void
    {
        $service = app(PartnerOnlineService::class);
        $user    = auth()->user();

        if (!$user) return;

        try {
            $service->goOffline($user);
            $this->dispatch('show-status-notification', message: 'Status Anda sekarang OFFLINE.');
            $this->dispatch('partner-state-changed');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[OfferRadarWidget] goOffline error: ' . $e->getMessage());
            session()->flash('error', 'Gagal mengubah status ke offline.');
        }
    }

    public function heartbeat($latitude = null, $longitude = null): void
    {
        if (!auth()->check()) {
            return;
        }

        app(PartnerOnlineService::class)->heartbeat(
            auth()->user(),
            $latitude ? (float) $latitude : null,
            $longitude ? (float) $longitude : null
        );
    }

    public function acceptOffer(int $dispatchId)
    {
        try {
            $help = app(HelpMatchingService::class)->acceptOffer($dispatchId, auth()->user());
            session()->flash('message', "Tawaran pekerjaan '{$help->title}' berhasil diterima! Segera menuju lokasi customer.");
            $this->dispatch('start-gps-tracking', helpId: $help->id);
            $this->dispatch('partner-state-changed');
            return $this->redirectRoute('mitra.helps.detail', ['id' => $help->id]);
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[OfferRadarWidget] acceptOffer error: ' . $e->getMessage(), ['dispatch_id' => $dispatchId]);
            session()->flash('error', 'Gagal menerima tawaran pekerjaan.');
        }
    }

    public function rejectOffer(int $dispatchId, ?string $reason = 'Mitra menolak tawaran')
    {
        try {
            app(HelpMatchingService::class)->rejectOffer($dispatchId, auth()->user(), $reason);
            $state = app(PartnerOnlineService::class)->getOrCreateState(auth()->id());
            if ($state->matching_status === PartnerOnlineState::STATUS_ONLINE) {
                session()->flash('message', 'Tawaran dilewati. Batas penolakan berturut-turut tercapai, status dialihkan ke Standby. Klik "Cari Order" saat Anda siap menerima pesanan.');
                $this->dispatch('show-status-notification', message: 'Status Anda beralih ke Standby.');
            } else {
                session()->flash('message', 'Tawaran dilewati. Status tetap AKTIF mencari order baru di sekitar Anda.');
                $this->dispatch('show-status-notification', message: 'Tawaran dilewati. Tetap mencari order...');
            }
            $this->dispatch('partner-state-changed');
        } catch (\Throwable $e) {
            Log::error('[OfferRadarWidget] rejectOffer error: ' . $e->getMessage(), ['dispatch_id' => $dispatchId]);
            session()->flash('error', 'Gagal menolak tawaran.');
        }
    }

    public function handleExpiry(int $dispatchId): void
    {
        try {
            app(HelpMatchingService::class)->handleExpiry($dispatchId, true);
            $state = app(PartnerOnlineService::class)->getOrCreateState(auth()->id());
            if ($state->matching_status === PartnerOnlineState::STATUS_ONLINE) {
                session()->flash('message', 'Waktu tawaran habis. Status dialihkan ke Standby. Klik "Cari Order" untuk mulai matching lagi.');
                $this->dispatch('show-status-notification', message: 'Waktu habis. Status beralih ke Standby.');
            } else {
                session()->flash('message', 'Waktu tawaran habis. Sistem melanjutkan pencarian order baru untuk Anda.');
                $this->dispatch('show-status-notification', message: 'Waktu habis. Melanjutkan pencarian...');
            }
            $this->dispatch('partner-state-changed');
        } catch (\Throwable $e) {
            Log::error('[OfferRadarWidget] handleExpiry error: ' . $e->getMessage(), ['dispatch_id' => $dispatchId]);
        }
    }

    #[On('echo-private:mitra.{userId},MitraOfferDispatched')]
    public function onOfferDispatched(): void
    {
        // Realtime event trigger via Laravel Echo
        $this->dispatch('$refresh');
    }

    public function render()
    {
        $userId = auth()->id();
        if (!$userId) {
            return view('livewire.mitra.dashboard.offer-radar-widget', [
                'onlineState' => null,
                'activeOffer' => null,
            ]);
        }

        // 1. Query 1: Partner Online State (Single indexed key lookup)
        $onlineState = app(PartnerOnlineService::class)->getOrCreateState($userId);

        // 2. Query 2: Active Offer (Hanya dijalankan jika status OFFER_PENDING)
        $activeOffer = null;
        if ($onlineState && $onlineState->matching_status === PartnerOnlineState::STATUS_OFFER_PENDING && $onlineState->current_help_id) {
            $candidateOffer = HelpDispatch::with(['help.user', 'help.city'])
                ->where('help_id', $onlineState->current_help_id)
                ->where('mitra_id', $userId)
                ->where('status', HelpDispatch::STATUS_OFFERED)
                ->latest()
                ->first();

            if ($candidateOffer && (!$candidateOffer->expires_at || $candidateOffer->expires_at->isFuture())) {
                $activeOffer = $candidateOffer;
            }
        }

        return view('livewire.mitra.dashboard.offer-radar-widget', [
            'onlineState' => $onlineState,
            'activeOffer' => $activeOffer,
        ]);
    }
}
