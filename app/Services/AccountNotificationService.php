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
}
