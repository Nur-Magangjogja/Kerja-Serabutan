<?php

namespace App\Services;

use App\Models\User;
use App\Services\Territory\AdminTerritoryAuthorizationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AccountNotificationService
{
    /**
     * Resolve eligible Admins (and optionally SuperAdmins) to receive account-related notifications for a User.
     *
     * Rules:
     * - Uses target user's locked profile territory (district_id and city_id).
     * - District-level profile: Admins with full authority over user.district_id (G4 canonical authority).
     * - City-level profile (district_id null): Explicit City Admins or approved legacy City-only Admins. District-only Admins excluded.
     * - Navbar active filter is ignored (uses canonical authority, not effective/presentation filter).
     * - Admin profile is NOT used to resolve territory.
     * - Deduped by user ID.
     * - No broadcast fallback if empty.
     *
     * @param User $user
     * @param bool $includeSuperAdmin
     * @return Collection<int, User>
     */
    public function resolveAdminsForUser(User $user, bool $includeSuperAdmin = false): Collection
    {
        return $this->resolveAdminsForTerritory(
            $user->district_id ? (int) $user->district_id : null,
            $user->city_id ? (int) $user->city_id : null,
            $includeSuperAdmin
        );
    }

    /**
     * Resolve eligible Admins (and optionally SuperAdmins) to receive notifications based on account/profile territory.
     *
     * @param int|null $districtId
     * @param int|null $cityId
     * @param bool $includeSuperAdmin
     * @return Collection<int, User>
     */
    public function resolveAdminsForTerritory(?int $districtId, ?int $cityId, bool $includeSuperAdmin = false): Collection
    {
        // Fail-closed if both null
        if ($districtId === null && $cityId === null) {
            Log::warning('[AccountNotificationService] resolveAdminsForTerritory: Both district_id and city_id are null. No recipients resolved.');
            return collect();
        }

        $authService = app(AdminTerritoryAuthorizationService::class);

        // Preload relations to prevent N+1 queries
        $admins = User::where('role', 'admin')
            ->where('status', 'active')
            ->with(['managedDistricts', 'managedCities'])
            ->get();

        $eligibleAdmins = $admins->filter(function (User $admin) use ($authService, $districtId, $cityId) {
            return $authService->canAccessTerritory($admin, $districtId, $cityId);
        });

        if ($includeSuperAdmin) {
            $superAdmins = User::whereIn('role', ['super_admin', 'superadmin'])
                ->where('status', 'active')
                ->get()
                ->filter(function (User $sa) use ($authService, $districtId, $cityId) {
                    return $authService->canAccessTerritory($sa, $districtId, $cityId);
                });

            $recipients = $eligibleAdmins->merge($superAdmins)->unique('id')->values();
        } else {
            $recipients = $eligibleAdmins->unique('id')->values();
        }

        if ($recipients->isEmpty()) {
            Log::warning("[AccountNotificationService] No eligible admin found for territory: district={$districtId}, city={$cityId}. Broadcast fallback suppressed.");
        }

        return $recipients;
    }

    /**
     * Dispatch informational oversight notification to Profile Admin(s) of a managed User
     * when a significant event occurs in a foreign (Case) territory.
     *
     * Rules:
     * - Case Admin(s) receive actionable notification from their respective case workflows.
     * - Profile Admin(s) are resolved using target user's current Profile Territory ($managedUser->district_id, $managedUser->city_id).
     * - Case Admins are excluded from oversight recipients (Profile Admins - Case Admins).
     * - SuperAdmins are excluded from duplicate oversight notification (since SuperAdmins have global oversight).
     * - Multi-Admin deduplicated by Admin ID.
     * - Does NOT grant foreign-case access or mutation rights.
     *
     * @param User $managedUser
     * @param iterable|User|null $caseAdmins
     * @param string $eventType 'cancellation' | 'dispute' | 'investigation' | 'discipline' | 'shadow_ban' | 'greylist'
     * @param string $title
     * @param string $message
     * @param string|null $publicRef
     * @param string|null $incidentTerritory
     * @param string|null $status
     * @param array $extraData
     * @return Collection<int, User>
     */
    public function notifyCrossTerritoryOversight(
        User $managedUser,
        iterable|User|null $caseAdmins,
        string $eventType,
        string $title,
        string $message,
        ?string $publicRef = null,
        ?string $incidentTerritory = null,
        ?string $status = null,
        array $extraData = []
    ): Collection {
        // Collect case admin IDs to exclude
        $caseAdminIds = [];
        if ($caseAdmins instanceof User) {
            $caseAdminIds[] = $caseAdmins->id;
        } elseif (is_iterable($caseAdmins)) {
            foreach ($caseAdmins as $ca) {
                if ($ca instanceof User) {
                    $caseAdminIds[] = $ca->id;
                } elseif (is_numeric($ca)) {
                    $caseAdminIds[] = (int) $ca;
                }
            }
        }

        // Resolve Profile Admins for user's CURRENT profile territory (excluding SuperAdmins)
        $profileAdmins = $this->resolveAdminsForUser($managedUser, includeSuperAdmin: false);

        // Filter out any Case Admins (same-admin dedup)
        $oversightRecipients = $profileAdmins->reject(function (User $admin) use ($caseAdminIds) {
            return in_array($admin->id, $caseAdminIds, true);
        })->unique('id')->values();

        if ($oversightRecipients->isEmpty()) {
            return collect();
        }

        $notification = new \App\Notifications\AccountOversightNotification(
            $managedUser,
            $eventType,
            $title,
            $message,
            $publicRef,
            $incidentTerritory,
            $status,
            $extraData
        );

        foreach ($oversightRecipients as $recipient) {
            try {
                $recipient->notify($notification);
            } catch (\Throwable $e) {
                Log::warning("[AccountNotificationService] Failed sending oversight notification to Admin #{$recipient->id}: " . $e->getMessage());
            }
        }

        return $oversightRecipients;
    }
}

