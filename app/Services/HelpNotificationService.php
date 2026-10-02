<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Help;
use App\Models\PartnerActivity;
use App\Models\User;
use App\Notifications\HelpStatusNotification;
use App\Notifications\HelpTakenNotification;
use App\Services\Territory\AdminTerritoryAuthorizationService;
use Illuminate\Support\Facades\Log;

class HelpNotificationService
{
    /**
     * Catat aktivitas mitra / customer ke PartnerActivity & ActivityLog.
     */
    public function logActivity($userId, $helpId, string $activityType, ?string $description = null, ?string $photo = null): void
    {
        try {
            PartnerActivity::create([
                'user_id'       => $userId,
                'help_id'       => $helpId,
                'activity_type' => $activityType,
                'description'   => $description,
                'photo'         => $photo,
                'ip_address'    => function_exists('request') ? request()?->ip() : null,
                'user_agent'    => function_exists('request') ? request()?->header('User-Agent') : null,
            ]);

            ActivityLog::record(
                $userId,
                $activityType,
                $description ?? "Aktivitas bantuan"
            );
        } catch (\Throwable $e) {
            Log::warning('[HelpNotificationService] logActivity failed: ' . $e->getMessage(), [
                'user_id'       => $userId,
                'help_id'       => $helpId,
                'activity_type' => $activityType,
            ]);
        }
    }

    /**
     * Notifikasi ke customer saat bantuan diambil mitra.
     */
    public function notifyHelpTaken(Help $help, User $mitra): void
    {
        try {
            $customer = $help->user ?? User::find($help->user_id);
            if ($customer) {
                $customer->notify(new HelpTakenNotification($help, $mitra));
            }
        } catch (\Throwable $e) {
            Log::warning('[HelpNotificationService] Failed to send HelpTakenNotification: ' . $e->getMessage());
        }
    }

    /**
     * Notifikasi perubahan status bantuan.
     */
    public function sendStatusNotification(
        Help $help,
        string $newStatus,
        ?User $recipient,
        ?User $actor,
        ?string $customMessage = null,
        ?string $customTitle = null
    ): void {
        try {
            if (!$recipient) return;

            $recipient->notify(new HelpStatusNotification(
                $help,
                $help->status,
                $newStatus,
                $actor,
                $customMessage,
                $customTitle
            ));
        } catch (\Throwable $e) {
            Log::warning('[HelpNotificationService] sendStatusNotification failed: ' . $e->getMessage(), [
                'help_id'    => $help->id,
                'new_status' => $newStatus,
            ]);
        }
    }

    /**
     * Resolve eligible Admins (and optionally SuperAdmins) to receive job-related notifications for a Help.
     *
     * Rules:
     * - District-level Help: Admins with full authority over Help.district_id (G4 canonical authority).
     * - City-level Help (district_id null): Explicit City Admins or approved legacy City-only Admins. District-only Admins excluded.
     * - Navbar active filter is ignored (uses canonical authority, not effective/presentation filter).
     * - Admin profile is NOT used to resolve territory.
     * - Deduped by user ID.
     * - No broadcast fallback if empty.
     *
     * @param Help $help
     * @param bool $includeSuperAdmin
     * @return \Illuminate\Support\Collection<int, User>
     */
    public function resolveAdminsForHelp(Help $help, bool $includeSuperAdmin = false): \Illuminate\Support\Collection
    {
        return $this->resolveAdminsForTerritory(
            $help->district_id ? (int) $help->district_id : null,
            $help->city_id ? (int) $help->city_id : null,
            $includeSuperAdmin
        );
    }

    /**
     * Resolve eligible Admins (and optionally SuperAdmins) to receive notifications based on territory.
     *
     * @param int|null $districtId
     * @param int|null $cityId
     * @param bool $includeSuperAdmin
     * @return \Illuminate\Support\Collection<int, User>
     */
    public function resolveAdminsForTerritory(?int $districtId, ?int $cityId, bool $includeSuperAdmin = false): \Illuminate\Support\Collection
    {
        // Fail-closed if both null
        if ($districtId === null && $cityId === null) {
            Log::warning('[HelpNotificationService] resolveAdminsForTerritory: Both district_id and city_id are null. No recipients resolved.');
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
            Log::warning("[HelpNotificationService] No eligible admin found for territory: district={$districtId}, city={$cityId}. Broadcast fallback suppressed.");
        }

        return $recipients;
    }
}
