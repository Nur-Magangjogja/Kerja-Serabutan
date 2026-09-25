<?php

namespace App\Livewire\Mitra\Dashboard;

use App\Actions\Matching\MitraMatchingActions;
use App\Models\HelpDispatch;
use App\Models\PartnerOnlineState;
use App\Services\Dashboard\DashboardQueryService;
use App\Services\PartnerOnlineService;
use Livewire\Attributes\On;
use Livewire\Component;

class OfferRadarWidget extends Component
{
    public ?int $userId = null;
    public string $servicePreference = PartnerOnlineState::PREFERENCE_ALL;
    public ?int $lastNotifiedHelpId = null;

    public function mount(): void
    {
        $this->userId = auth()->id();
        $this->validateAndRepairState();

        $user = auth()->user();
        if ($user) {
            $onlineState = app(PartnerOnlineService::class)->getOrCreateState($user);
            $this->servicePreference = $onlineState->service_preference ?? PartnerOnlineState::PREFERENCE_ALL;
            if ($onlineState->matching_status === PartnerOnlineState::STATUS_OFFER_PENDING) {
                $this->lastNotifiedHelpId = $onlineState->current_help_id;
            }
        }
    }

    public function setServicePreference(string $preference): void
    {
        $user = auth()->user();
        if (!$user) return;

        $onlineState = app(PartnerOnlineService::class)->getOrCreateState($user);
        $activeOffer = app(DashboardQueryService::class)->getActiveOfferForRadar($user->id, $onlineState);

        // Jika status offer_pending tetapi record tawaran sudah tidak valid/expired di DB, bersihkan dulu
        if ($onlineState->matching_status === PartnerOnlineState::STATUS_OFFER_PENDING && !$activeOffer) {
            $this->validateAndRepairState();
            $onlineState->refresh();
        }

        // Guard: Kunci filter jika sedang ada tawaran aktif atau sedang bertugas
        if ($onlineState->matching_status === PartnerOnlineState::STATUS_OFFER_PENDING || !empty($onlineState->current_help_id) || $onlineState->matching_status === PartnerOnlineState::STATUS_BUSY || $activeOffer) {
            $msg = ($onlineState->matching_status === PartnerOnlineState::STATUS_BUSY)
                ? 'Filter bantuan terkunci saat sedang bertugas. Selesaikan tugas saat ini terlebih dahulu.'
                : 'Filter bantuan terkunci saat sedang ada tawaran masuk. Harap tanggapi tawaran terlebih dahulu.';
            $this->servicePreference = $onlineState->service_preference ?? PartnerOnlineState::PREFERENCE_ALL;
            session()->flash('error', $msg);
            $this->dispatch('show-status-notification', message: $msg);
            return;
        }

        $res = app(PartnerOnlineService::class)->updateServicePreference($user, $preference);

        if ($res['success']) {
            $this->servicePreference = $res['preference'];
            PartnerOnlineService::clearStateCache($user->id);

            // Jika mitra sedang aktif mencari order, cari order yang sesuai filter ini segera
            $onlineState = app(PartnerOnlineService::class)->getOrCreateState($user, true);
            if ($onlineState->matching_status === PartnerOnlineState::STATUS_SEARCHING) {
                try {
                    $matched = app(\App\Services\HelpMatchingService::class)->matchPendingOrderForPartner($user);
                    if ($matched) {
                        PartnerOnlineService::clearStateCache($user->id);
                        // Saat mengganti filter, jika langsung menemukan order, update UI secara senyap tanpa pesan & suara
                        $this->dispatch('offer-found', silent: true);
                        $this->dispatch('partner-state-changed');
                        $this->dispatch('$refresh');
                        return;
                    }
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            // Saat mengganti filter biasa, tidak usah diberikan pesan dan suara notifikasi
            $this->dispatch('partner-state-changed');
            $this->dispatch('$refresh');
        } else {
            $this->servicePreference = $res['preference'] ?? ($onlineState->service_preference ?? PartnerOnlineState::PREFERENCE_ALL);
            session()->flash('error', $res['message']);
        }
    }

    /**
     * Periodic radar polling while partner is in SEARCHING or STANDBY state.
     */
    public function pollRadar(): void
    {
        $user = auth()->user();
        if (!$user) return;

        PartnerOnlineService::clearStateCache($user->id);
        $onlineState = app(PartnerOnlineService::class)->getOrCreateState($user, true);

        // Jika status mitra sudah berubah menjadi OFFER_PENDING:
        if ($onlineState->matching_status === PartnerOnlineState::STATUS_OFFER_PENDING) {
            $helpId = $onlineState->current_help_id;
            // Cegah spam: hanya dispatch jika tawaran ini belum pernah dinotifikasi sebelumnya
            if ($helpId && $this->lastNotifiedHelpId !== $helpId) {
                $this->lastNotifiedHelpId = $helpId;
                $this->dispatch('offer-found', helpId: $helpId, message: 'Tawaran pesanan baru masuk! Harap respon tawaran.');
                $this->dispatch('partner-state-changed');
            }
            $this->dispatch('$refresh');
            return;
        }

        // Jika tidak lagi dalam status tawaran, reset ID tawaran terakhir
        if ($this->lastNotifiedHelpId !== null) {
            $this->lastNotifiedHelpId = null;
        }

        if ($onlineState->matching_status === PartnerOnlineState::STATUS_SEARCHING) {
            try {
                $matched = app(\App\Services\HelpMatchingService::class)->matchPendingOrderForPartner($user);
                if ($matched) {
                    PartnerOnlineService::clearStateCache($user->id);
                    $freshState = app(PartnerOnlineService::class)->getOrCreateState($user, true);
                    $helpId = $freshState->current_help_id;
                    if ($helpId && $this->lastNotifiedHelpId !== $helpId) {
                        $this->lastNotifiedHelpId = $helpId;
                        $this->dispatch('offer-found', helpId: $helpId, message: 'Tawaran pesanan baru ditemukan!');
                    }
                    $this->dispatch('partner-state-changed');
                    $this->dispatch('$refresh');
                    return;
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    /**
     * Reconcile state if partner state is desynchronized.
     */
    public function validateAndRepairState(): void
    {
        $user = auth()->user();
        if (!$user) return;

        $onlineState = app(PartnerOnlineService::class)->getOrCreateState($user);
        if ($onlineState && $onlineState->matching_status === PartnerOnlineState::STATUS_OFFER_PENDING && $onlineState->current_help_id) {
            $offer = app(DashboardQueryService::class)->getActiveOfferForRadar($user->id, $onlineState);
            if (!$offer) {
                app(PartnerOnlineService::class)->releaseCancelledOffer($user->id, $onlineState->current_help_id);
            }
        }
    }

    public function goOnline($latitude = null, $longitude = null): void
    {
        $user = auth()->user();
        if (!$user) return;

        $res = app(MitraMatchingActions::class)->goOnline($user, $latitude ? (float) $latitude : null, $longitude ? (float) $longitude : null);

        if ($res['success']) {
            $this->dispatch('show-status-notification', message: $res['message']);
            $this->dispatch('partner-state-changed');
        } else {
            session()->flash('error', $res['message']);
        }
    }

    public function startSearching($latitude = null, $longitude = null): void
    {
        $user = auth()->user();
        if (!$user) return;

        $res = app(MitraMatchingActions::class)->startSearching($user, $latitude ? (float) $latitude : null, $longitude ? (float) $longitude : null);

        PartnerOnlineService::clearStateCache($user->id);

        if ($res['success']) {
            if (!empty($res['matched'])) {
                $freshState = app(PartnerOnlineService::class)->getOrCreateState($user, true);
                $helpId = $freshState->current_help_id;
                if ($helpId && $this->lastNotifiedHelpId !== $helpId) {
                    $this->lastNotifiedHelpId = $helpId;
                    $this->dispatch('offer-found', helpId: $helpId, message: $res['message']);
                }
            } else {
                $this->dispatch('show-status-notification', message: $res['message']);
            }
            $this->dispatch('partner-state-changed');
            $this->dispatch('$refresh');
        } else {
            session()->flash('error', $res['message']);
        }
    }

    public function stopSearching(): void
    {
        $user = auth()->user();
        if (!$user) return;

        $this->lastNotifiedHelpId = null;
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

        $this->lastNotifiedHelpId = null;
        $res = app(MitraMatchingActions::class)->goOffline($user);

        if ($res['success']) {
            $this->dispatch('show-status-notification', message: $res['message']);
            $this->dispatch('partner-state-changed');
        } else {
            session()->flash('error', $res['message']);
        }
    }

    public function heartbeat($latitude = null, $longitude = null): void
    {
        $user = auth()->user();
        if (!$user) return;

        app(MitraMatchingActions::class)->heartbeat($user, $latitude ? (float) $latitude : null, $longitude ? (float) $longitude : null);
    }

    public function acceptOffer(int $dispatchId)
    {
        $user = auth()->user();
        if (!$user) return;

        $this->lastNotifiedHelpId = null;
        $res = app(MitraMatchingActions::class)->acceptOffer($dispatchId, $user);

        if ($res['success']) {
            session()->flash('message', $res['message']);
            $this->dispatch('start-gps-tracking', helpId: $res['help']->id);
            $this->dispatch('partner-state-changed');
            return $this->redirectRoute('mitra.helps.detail', ['id' => $res['help']->id]);
        }

        session()->flash('error', $res['message']);
    }

    public function rejectOffer(int $dispatchId, ?string $reason = 'Mitra menolak tawaran')
    {
        $user = auth()->user();
        if (!$user) return;

        $this->lastNotifiedHelpId = null;
        $res = app(MitraMatchingActions::class)->rejectOffer($dispatchId, $user, $reason);

        if ($res['success']) {
            session()->flash('message', $res['message']);
            $this->dispatch('show-status-notification', message: $res['is_demoted_to_standby'] ? 'Status Anda beralih ke Standby.' : 'Tawaran dilewati. Tetap mencari order...');
            $this->dispatch('partner-state-changed');
        } else {
            session()->flash('error', $res['message']);
        }
    }

    public function handleExpiry(int $dispatchId): void
    {
        $user = auth()->user();
        if (!$user) return;

        $this->lastNotifiedHelpId = null;
        $res = app(MitraMatchingActions::class)->handleExpiry($dispatchId, $user);

        if ($res['success']) {
            session()->flash('message', $res['message']);
            $this->dispatch('show-status-notification', message: $res['is_demoted_to_standby'] ? 'Waktu habis. Status beralih ke Standby.' : 'Waktu habis. Melanjutkan pencarian...');
            $this->dispatch('partner-state-changed');
        }
    }

    #[On('echo-private:mitra.{userId},MitraOfferDispatched')]
    public function onOfferDispatched(): void
    {
        PartnerOnlineService::clearStateCache($this->userId);
        $this->validateAndRepairState();

        $user = auth()->user();
        $onlineState = $user ? app(PartnerOnlineService::class)->getOrCreateState($user, true) : null;
        $helpId = $onlineState?->current_help_id;

        // Cegah spam: jika tawaran ini sudah dinotifikasikan, cukup refresh tanpa spam toast & chime
        if ($helpId && $this->lastNotifiedHelpId === $helpId) {
            $this->dispatch('$refresh');
            return;
        }

        if ($helpId) {
            $this->lastNotifiedHelpId = $helpId;
        }

        $this->dispatch('offer-found', helpId: $helpId, message: 'Tawaran pesanan baru ditemukan! Harap respon tawaran.');
        $this->dispatch('partner-state-changed');
        $this->dispatch('$refresh');
    }

    public function render()
    {
        $user = auth()->user();
        if (!$user) {
            return view('livewire.mitra.dashboard.offer-radar-widget', [
                'onlineState'  => null,
                'activeOffer'  => null,
                'isRestricted' => false,
            ]);
        }

        PartnerOnlineService::clearStateCache($user->id);
        $onlineState       = app(PartnerOnlineService::class)->getOrCreateState($user, true);
        $this->servicePreference = $onlineState->service_preference ?? PartnerOnlineState::PREFERENCE_ALL;
        $activeOffer       = app(DashboardQueryService::class)->getActiveOfferForRadar($user->id, $onlineState);
        $isRestricted      = app(MitraMatchingActions::class)->isRestricted($user);
        $isSeekingEnabled  = \App\Models\AppSetting::isMatchingSeekingEnabledForUser($user);
        $canTakePickupDelivery = $user->canTakePickupDelivery();

        return view('livewire.mitra.dashboard.offer-radar-widget', [
            'onlineState'           => $onlineState,
            'activeOffer'           => $activeOffer,
            'isRestricted'          => $isRestricted,
            'isSeekingEnabled'      => $isSeekingEnabled,
            'canTakePickupDelivery' => $canTakePickupDelivery,
        ]);
    }
}
