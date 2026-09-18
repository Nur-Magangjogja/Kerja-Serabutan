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

    public function mount(): void
    {
        $this->userId = auth()->id();
        $this->validateAndRepairState();
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
        $this->validateAndRepairState();
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

        $onlineState  = app(PartnerOnlineService::class)->getOrCreateState($user);
        $activeOffer  = app(DashboardQueryService::class)->getActiveOfferForRadar($user->id, $onlineState);
        $isRestricted = app(MitraMatchingActions::class)->isRestricted($user);

        return view('livewire.mitra.dashboard.offer-radar-widget', [
            'onlineState'  => $onlineState,
            'activeOffer'  => $activeOffer,
            'isRestricted' => $isRestricted,
        ]);
    }
}
