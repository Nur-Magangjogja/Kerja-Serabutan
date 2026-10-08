<?php

namespace App\Services;

use App\Models\Help;
use App\Models\HelpDispatch;
use App\Models\User;
use App\Services\Cancellation\CancellationSettlementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduledDepartureTimeoutService
{
    protected RegionService $regionService;
    protected PartnerOnlineService $onlineService;
    protected HelpMatchingService $matchingService;
    protected CancellationSettlementService $settlementService;
    protected HelpNotificationService $notificationService;

    public function __construct(
        RegionService $regionService,
        PartnerOnlineService $onlineService,
        HelpMatchingService $matchingService,
        CancellationSettlementService $settlementService,
        HelpNotificationService $notificationService
    ) {
        $this->regionService = $regionService;
        $this->onlineService = $onlineService;
        $this->matchingService = $matchingService;
        $this->settlementService = $settlementService;
        $this->notificationService = $notificationService;
    }

    /**
     * Canonical handler for scheduled departure timeout / overdue.
     * Thread-safe with DB::transaction() and lockForUpdate().
     *
     * @return array{handled: bool, action: string, reason?: string}
     */
    public function handleOverdueHelp(Help $help): array
    {
        return DB::transaction(function () use ($help) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            if (!$lockedHelp) {
                return ['handled' => false, 'action' => 'none', 'reason' => 'not_found'];
            }

            // Must be scheduled order
            if (!$lockedHelp->isScheduled()) {
                return ['handled' => false, 'action' => 'none', 'reason' => 'not_scheduled'];
            }

            // Terminal status orders cannot be processed
            if (in_array($lockedHelp->status, [Help::STATUS_SELESAI, Help::STATUS_DIBATALKAN], true)) {
                return ['handled' => false, 'action' => 'none', 'reason' => 'terminal'];
            }

            // If partner has already started departure, do not touch
            if ($lockedHelp->hasPartnerStartedDeparture()) {
                return ['handled' => false, 'action' => 'none', 'reason' => 'partner_already_started'];
            }

            // Re-check overdue boundary: departure_at + departure_grace_minutes
            if (!$lockedHelp->isDepartureOverdue()) {
                return ['handled' => false, 'action' => 'none', 'reason' => 'not_overdue'];
            }

            $now = now();
            $oldPartnerId = $lockedHelp->mitra_id;

            // 1. PRECEDENCE: expires_at (Requirement 12)
            $isExpired = $lockedHelp->isExpired() || ($lockedHelp->expires_at && $now->gte($lockedHelp->expires_at));
            if ($isExpired) {
                if ($oldPartnerId) {
                    $this->onlineService->releaseBusy($oldPartnerId, $lockedHelp->id);
                    $lockedHelp->addExcludedPartner($oldPartnerId, 'scheduled_departure_timeout');
                }

                HelpDispatch::where('help_id', $lockedHelp->id)
                    ->where('status', HelpDispatch::STATUS_OFFERED)
                    ->update([
                        'status'           => HelpDispatch::STATUS_CANCELLED,
                        'responded_at'     => $now,
                        'rejection_reason' => 'Scheduled departure timeout & expired',
                    ]);

                if ($lockedHelp->schedule_overdue_at === null) {
                    $lockedHelp->update(['schedule_overdue_at' => $now]);
                }

                if ($lockedHelp->status === Help::STATUS_TAKEN) {
                    $lockedHelp->transitionTo(Help::STATUS_MENUNGGU_MITRA, [
                        'mitra_id'          => null,
                        'taken_at'          => null,
                        'mitra_assigned_at' => null,
                        'dispatch_mode'     => Help::DISPATCH_MODE_CLOSED,
                    ]);
                }

                $reason = 'Batas waktu pencarian atau pesanan kedaluwarsa saat batas keberangkatan terlewat.';
                $this->settlementService->processFullRefund($lockedHelp, $reason);

                $lockedHelp->update([
                    'admin_notes' => "Dibatalkan otomatis oleh sistem. Alasan: {$reason}",
                ]);

                if ($oldPartnerId) {
                    $this->notificationService->logActivity(
                        $oldPartnerId,
                        $lockedHelp->id,
                        'scheduled_departure_expired',
                        $reason
                    );
                }

                if ($lockedHelp->user) {
                    $this->notificationService->sendStatusNotification(
                        $lockedHelp,
                        Help::STATUS_DIBATALKAN,
                        $lockedHelp->user,
                        null,
                        $reason,
                        'Bantuan Dibatalkan'
                    );
                }

                Log::info("[ScheduledDepartureTimeoutService] Help #{$lockedHelp->id} auto-cancelled due to expiry precedence.");
                return ['handled' => true, 'action' => 'expired_cancelled'];
            }

            // 2. PRECEDENCE: Region Lifecycle (Requirement 11)
            $isRegionActive = $this->regionService->isRegionActive($lockedHelp->district_id, $lockedHelp->city_id);
            if (!$isRegionActive) {
                if ($oldPartnerId) {
                    $this->onlineService->releaseBusy($oldPartnerId, $lockedHelp->id);
                    $lockedHelp->addExcludedPartner($oldPartnerId, 'scheduled_departure_timeout');
                }

                HelpDispatch::where('help_id', $lockedHelp->id)
                    ->where('status', HelpDispatch::STATUS_OFFERED)
                    ->update([
                        'status'           => HelpDispatch::STATUS_CANCELLED,
                        'responded_at'     => $now,
                        'rejection_reason' => 'Scheduled departure timeout & region inactive',
                    ]);

                if ($lockedHelp->schedule_overdue_at === null) {
                    $lockedHelp->update(['schedule_overdue_at' => $now]);
                }

                if ($lockedHelp->status === Help::STATUS_TAKEN) {
                    $lockedHelp->transitionTo(Help::STATUS_MENUNGGU_MITRA, [
                        'mitra_id'          => null,
                        'taken_at'          => null,
                        'mitra_assigned_at' => null,
                        'dispatch_mode'     => Help::DISPATCH_MODE_CLOSED,
                    ]);
                }

                $reason = 'Pesanan dibatalkan otomatis karena jadwal keberangkatan terlewat di wilayah yang sedang tidak aktif.';
                $this->settlementService->processFullRefund($lockedHelp, $reason);

                $lockedHelp->update([
                    'admin_notes' => "Dibatalkan otomatis oleh sistem. Alasan: {$reason}",
                ]);

                if ($oldPartnerId) {
                    $this->notificationService->logActivity(
                        $oldPartnerId,
                        $lockedHelp->id,
                        'scheduled_departure_region_inactive',
                        $reason
                    );
                }

                if ($lockedHelp->user) {
                    $this->notificationService->sendStatusNotification(
                        $lockedHelp,
                        Help::STATUS_DIBATALKAN,
                        $lockedHelp->user,
                        null,
                        $reason,
                        'Bantuan Dibatalkan'
                    );
                }

                Log::info("[ScheduledDepartureTimeoutService] Help #{$lockedHelp->id} cancelled due to region inactive precedence.");
                return ['handled' => true, 'action' => 'region_inactive_cancelled'];
            }

            // 3. CONDITION A: Scheduled with partner assigned (status = taken, mitra_id != null)
            if ($lockedHelp->status === Help::STATUS_TAKEN && $oldPartnerId !== null) {
                // Release Mitra & Busy state
                $this->onlineService->releaseBusy($oldPartnerId, $lockedHelp->id);

                // Exclude old partner from taking this order again
                $lockedHelp->addExcludedPartner($oldPartnerId, 'scheduled_departure_timeout');

                // Cancel stale dispatches
                HelpDispatch::where('help_id', $lockedHelp->id)
                    ->where('status', HelpDispatch::STATUS_OFFERED)
                    ->update([
                        'status'           => HelpDispatch::STATUS_CANCELLED,
                        'responded_at'     => $now,
                        'rejection_reason' => 'Scheduled departure timeout',
                    ]);

                // Transition back to menunggu_mitra with schedule_overdue_at
                $lockedHelp->transitionTo(Help::STATUS_MENUNGGU_MITRA, [
                    'mitra_id'            => null,
                    'taken_at'            => null,
                    'mitra_assigned_at'   => null,
                    'dispatch_mode'       => Help::DISPATCH_MODE_SEEKING,
                    'schedule_overdue_at' => $lockedHelp->schedule_overdue_at ?? $now,
                ]);

                // Log activity & notify
                $this->notificationService->logActivity(
                    $oldPartnerId,
                    $lockedHelp->id,
                    'scheduled_departure_timeout',
                    'Penugasan bantuan dibatalkan otomatis karena batas toleransi keberangkatan telah terlewat.'
                );

                $oldMitra = User::find($oldPartnerId);
                if ($oldMitra) {
                    $this->notificationService->sendStatusNotification(
                        $lockedHelp,
                        Help::STATUS_MENUNGGU_MITRA,
                        $oldMitra,
                        null
                    );
                }

                if ($lockedHelp->user) {
                    $customerNotice = $lockedHelp->getCustomerScheduleStatusNotice()
                        ?? 'Jadwal keberangkatan telah terlewat dan mitra sebelumnya belum memulai perjalanan. Sistem sedang mencari mitra pengganti.';
                    $this->notificationService->sendStatusNotification(
                        $lockedHelp,
                        Help::STATUS_MENUNGGU_MITRA,
                        $lockedHelp->user,
                        null,
                        $customerNotice,
                        'Jadwal Keberangkatan Terlewat'
                    );
                }

                // Trigger ready-now matching for active region
                $this->matchingService->initiateMatching($lockedHelp);

                Log::info("[ScheduledDepartureTimeoutService] Help #{$lockedHelp->id} released from partner #{$oldPartnerId} due to departure timeout and rematched.");
                return ['handled' => true, 'action' => 'rematched_from_taken'];
            }

            // 4. CONDITION B: Scheduled unassigned (status = menunggu_mitra, mitra_id = null, schedule_overdue_at null)
            if ($lockedHelp->status === Help::STATUS_MENUNGGU_MITRA && $oldPartnerId === null) {
                if ($lockedHelp->schedule_overdue_at !== null) {
                    return ['handled' => false, 'action' => 'none', 'reason' => 'already_normalized'];
                }

                $lockedHelp->update([
                    'schedule_overdue_at' => $now,
                    'dispatch_mode'       => Help::DISPATCH_MODE_SEEKING,
                ]);

                // Customer notification: generic wording without blaming prior mitra
                if ($lockedHelp->user) {
                    $customerNotice = $lockedHelp->getCustomerScheduleStatusNotice()
                        ?? 'Jadwal keberangkatan telah terlewat. Sistem sedang mencari mitra untuk segera mengerjakan pesanan Anda.';
                    $this->notificationService->sendStatusNotification(
                        $lockedHelp,
                        Help::STATUS_MENUNGGU_MITRA,
                        $lockedHelp->user,
                        null,
                        $customerNotice,
                        'Jadwal Keberangkatan Terlewat'
                    );
                }

                // Trigger ready-now matching / pool
                $this->matchingService->initiateMatching($lockedHelp);

                Log::info("[ScheduledDepartureTimeoutService] Unassigned Help #{$lockedHelp->id} marked as overdue and initiated ready-now matching.");
                return ['handled' => true, 'action' => 'ready_now_matching'];
            }

            return ['handled' => false, 'action' => 'none', 'reason' => 'unhandled_state'];
        });
    }

    /**
     * Periodic sweep scanner for scheduler.
     * Finds candidates, validates deadlines safely, and handles overdue helps idempotently.
     */
    public function sweepScheduledTimeouts(): int
    {
        $handledCount = 0;

        // Chunking safely through candidates
        Help::where('order_mode', Help::ORDER_MODE_SCHEDULED)
            ->whereIn('status', [Help::STATUS_TAKEN, Help::STATUS_MENUNGGU_MITRA])
            ->whereNull('partner_started_at')
            ->chunkById(50, function ($helps) use (&$handledCount) {
                foreach ($helps as $help) {
                    if ($help->status === Help::STATUS_MENUNGGU_MITRA && $help->schedule_overdue_at !== null) {
                        continue;
                    }

                    if ($help->isDepartureOverdue()) {
                        $result = $this->handleOverdueHelp($help);
                        if (!empty($result['handled'])) {
                            $handledCount++;
                        }
                    }
                }
            });

        return $handledCount;
    }
}
