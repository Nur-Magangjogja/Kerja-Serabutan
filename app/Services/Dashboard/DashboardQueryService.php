<?php

namespace App\Services\Dashboard;

use App\Enums\HelpStatus;
use App\Models\Help;
use App\Models\HelpDispatch;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Services\DashboardStatsService;
use App\Services\PartnerOnlineService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class DashboardQueryService
{
    public function __construct(
        protected DashboardStatsService $statsService,
        protected PartnerOnlineService $onlineService
    ) {}

    /**
     * Aggregated statistics for partner dashboard.
     */
    public function getSummaryStats(User $user, bool $forceFresh = false): array
    {
        return $this->statsService->getSummaryStats($user, $forceFresh);
    }

    /**
     * Get active task and tasks waiting for confirmation in a single efficient query.
     *
     * @return array{activeTask: ?Help, waitingConfirmationHelps: \Illuminate\Support\Collection}
     */
    public function getActiveAndPendingTasks(User $user): array
    {
        $relevantHelps = Help::where('mitra_id', $user->id)
            ->where(function ($q) {
                $q->active()
                  ->orWhere('status', Help::STATUS_WAITING_CONFIRMATION)
                  ->orWhere('status', Help::STATUS_PARTNER_CANCEL_REQUESTED);
            })
            ->with(['user', 'city', 'district'])
            ->latest()
            ->get();

        $activeTask = $relevantHelps->first(function ($h) {
            return in_array($h->status, array_merge(Help::activeStatuses(), [
                Help::STATUS_PARTNER_CANCEL_REQUESTED,
            ]), true);
        });

        $waitingConfirmationHelps = $relevantHelps->filter(function ($h) {
            return $h->status === Help::STATUS_WAITING_CONFIRMATION;
        })->take(3)->values();

        return [
            'activeTask'               => $activeTask,
            'waitingConfirmationHelps' => $waitingConfirmationHelps,
        ];
    }

    /**
     * Get paginated help items for the active tab.
     */
    public function getHelpListByTab(User $user, string $activeTab, int $perPage = 6): LengthAwarePaginator
    {
        $userDistrictId = $user->district_id;
        $userCityId     = $user->city_id;

        if ($activeTab === 'tersedia' || $activeTab === 'semua') {
            $helpsQuery = $this->statsService->availablePoolQuery($user)
                ->with(['user', 'city', 'district']);

            if ($userDistrictId) {
                $helpsQuery->orderByRaw("(district_id = ?) DESC", [$userDistrictId])->latest();
            } elseif ($userCityId) {
                $helpsQuery->orderByRaw("(city_id = ?) DESC", [$userCityId])->latest();
            } else {
                $helpsQuery->latest();
            }

            return $helpsQuery->paginate($perPage);
        }

        if ($activeTab === 'diproses') {
            $inProgressStatuses = array_merge(Help::activeStatuses(), [
                Help::STATUS_WAITING_CONFIRMATION,
                Help::STATUS_PARTNER_CANCEL_REQUESTED,
                Help::STATUS_CUSTOMER_CANCEL_REQUESTED,
            ]);

            return Help::where('mitra_id', $user->id)
                ->whereIn('status', $inProgressStatuses)
                ->with(['user', 'city', 'district'])
                ->latest()
                ->paginate($perPage);
        }

        if ($activeTab === 'selesai') {
            return Help::where('mitra_id', $user->id)
                ->where('status', Help::STATUS_SELESAI)
                ->with(['user', 'city', 'district'])
                ->latest()
                ->paginate($perPage);
        }

        return Help::where('mitra_id', $user->id)
            ->with(['user', 'city', 'district'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get active dispatched offer for radar widget without performing mutation in read path.
     */
    public function getActiveOfferForRadar(int $userId, ?PartnerOnlineState $onlineState): ?HelpDispatch
    {
        if (!$onlineState || $onlineState->matching_status !== PartnerOnlineState::STATUS_OFFER_PENDING || !$onlineState->current_help_id) {
            return null;
        }

        $candidateOffer = HelpDispatch::with(['help.user', 'help.city'])
            ->where('help_id', $onlineState->current_help_id)
            ->where('mitra_id', $userId)
            ->where('status', HelpDispatch::STATUS_OFFERED)
            ->latest()
            ->first();

        if ($candidateOffer && (!$candidateOffer->expires_at || $candidateOffer->expires_at->isFuture()) && $candidateOffer->help && !in_array($candidateOffer->help->status, Help::terminalStatuses(), true)) {
            return $candidateOffer;
        }

        return null;
    }
}
