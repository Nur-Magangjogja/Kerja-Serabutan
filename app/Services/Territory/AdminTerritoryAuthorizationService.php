<?php

namespace App\Services\Territory;

use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class AdminTerritoryAuthorizationService
{
    protected array $canAccessCache = [];
    protected array $districtCache = [];
    protected array $cityExistsCache = [];
    protected array $adminDistrictIdsCache = [];

    /**
     * Clear request-scoped cache.
     */
    public function clearCache(): void
    {
        $this->canAccessCache = [];
        $this->districtCache = [];
        $this->cityExistsCache = [];
        $this->adminDistrictIdsCache = [];
    }

    /**
     * Check if an actor can access a territory or resource defined by district_id and/or city_id.
     *
     * Fail-closed principles:
     * - Null actor or non-admin actor -> false (unless super_admin).
     * - Both districtId and cityId null -> false.
     * - Inconsistent district and city pair -> false.
     * - Unknown district or city -> false.
     * - District-only admin cannot access city-level resource (districtId = null, cityId = valid).
     *
     * @param User|null $admin
     * @param int|null $districtId
     * @param int|null $cityId
     * @return bool
     */
    public function canAccessTerritory(?User $admin, ?int $districtId, ?int $cityId): bool
    {
        if (!$admin) {
            return false;
        }

        $cacheKey = "{$admin->id}:" . ($districtId ?? 'null') . ":" . ($cityId ?? 'null');
        if (isset($this->canAccessCache[$cacheKey])) {
            return $this->canAccessCache[$cacheKey];
        }

        // 1. SuperAdmin has global access
        if ($this->isSuperAdmin($admin)) {
            // Verify territory validity if provided
            if ($districtId !== null) {
                $district = $this->districtCache[$districtId] ??= District::find($districtId);
                if (!$district) {
                    return $this->canAccessCache[$cacheKey] = false;
                }
            }
            if ($cityId !== null) {
                $cityExists = $this->cityExistsCache[$cityId] ??= City::where('id', $cityId)->exists();
                if (!$cityExists) {
                    return $this->canAccessCache[$cacheKey] = false;
                }
            }
            if ($districtId !== null && $cityId !== null) {
                $district = $this->districtCache[$districtId] ??= District::find($districtId);
                $actualCityId = $district?->city_id;
                if ((int) $actualCityId !== (int) $cityId) {
                    return $this->canAccessCache[$cacheKey] = false;
                }
            }
            if ($districtId === null && $cityId === null) {
                return $this->canAccessCache[$cacheKey] = false;
            }
            return $this->canAccessCache[$cacheKey] = true;
        }

        // 2. Must have admin role
        if ($admin->role !== 'admin') {
            return $this->canAccessCache[$cacheKey] = false;
        }

        // 3. Null + Null -> DENY
        if ($districtId === null && $cityId === null) {
            return $this->canAccessCache[$cacheKey] = false;
        }

        // 4. Validate District if provided
        $district = null;
        if ($districtId !== null) {
            $district = $this->districtCache[$districtId] ??= District::find($districtId);
            if (!$district) {
                return $this->canAccessCache[$cacheKey] = false; // Unknown district -> DENY
            }
        }

        // 5. Validate City if provided
        if ($cityId !== null) {
            $cityExists = $this->cityExistsCache[$cityId] ??= City::where('id', $cityId)->exists();
            if (!$cityExists) {
                return $this->canAccessCache[$cacheKey] = false; // Unknown city -> DENY
            }
        }

        // 6. Validate pair consistency if both provided
        if ($district !== null && $cityId !== null) {
            if ((int) $district->city_id !== (int) $cityId) {
                return $this->canAccessCache[$cacheKey] = false; // Inconsistent pair -> DENY
            }
        }

        // 7. Case: District is available
        if ($district !== null) {
            $adminDistrictIds = $this->adminDistrictIdsCache[$admin->id] ??= $admin->getAdminDistrictIds();
            $allowed = in_array((int) $district->id, $adminDistrictIds, true);
            return $this->canAccessCache[$cacheKey] = $allowed;
        }

        // 8. Case: District is NULL, but City is available
        // District-only admin CANNOT access city-level resource. Must have explicit city authority.
        if ($cityId !== null) {
            $allowed = $this->hasExplicitCityAuthority($admin, (int) $cityId);
            return $this->canAccessCache[$cacheKey] = $allowed;
        }

        return $this->canAccessCache[$cacheKey] = false;
    }

    /**
     * Authorize territory access, or throw an AuthorizationException.
     *
     * @param User|null $admin
     * @param int|null $districtId
     * @param int|null $cityId
     * @param string $message
     * @return void
     *
     * @throws AuthorizationException
     */
    public function authorizeTerritory(?User $admin, ?int $districtId, ?int $cityId, string $message = 'Anda tidak memiliki wewenang untuk mengakses wilayah ini.'): void
    {
        if (!$this->canAccessTerritory($admin, $districtId, $cityId)) {
            throw new AuthorizationException($message);
        }
    }

    /**
     * Check if admin has explicit city-level authority over the given city.
     *
     * Rule:
     * - SuperAdmin: TRUE.
     * - Target city exists in managedCities (admin_city pivot): TRUE.
     * - Any explicit pivot assignment exists (managedCities or managedDistricts),
     *   and target city is NOT in managedCities: FALSE (no profile fallback).
     * - No explicit pivot assignments exist:
     *   Legacy fallback ONLY if:
     *   `users.city_id === target city` AND `users.district_id === null`.
     *   Otherwise: FALSE.
     *
     * @param User $admin
     * @param int $cityId
     * @return bool
     */
    public function hasExplicitCityAuthority(User $admin, int $cityId): bool
    {
        if ($this->isSuperAdmin($admin)) {
            return $this->cityExistsCache[$cityId] ??= City::where('id', $cityId)->exists();
        }

        if ($admin->role !== 'admin') {
            return false;
        }

        if (!($this->cityExistsCache[$cityId] ??= City::where('id', $cityId)->exists())) {
            return false;
        }

        // 1. Directly assigned managed cities (admin_city pivot)
        $managedCityIds = $admin->relationLoaded('managedCities')
            ? $admin->managedCities->pluck('id')->all()
            : $admin->managedCities()->allRelatedIds()->all();

        $managedCityIds = array_map('intval', $managedCityIds);

        if (in_array((int) $cityId, $managedCityIds, true)) {
            return true;
        }

        // 2. Check if ANY explicit assignment exists (managedCities or managedDistricts)
        $managedDistrictIds = $admin->relationLoaded('managedDistricts')
            ? $admin->managedDistricts->pluck('id')->all()
            : $admin->managedDistricts()->allRelatedIds()->all();

        $hasAnyExplicitAssignment = !empty($managedCityIds) || !empty($managedDistrictIds);

        if ($hasAnyExplicitAssignment) {
            // Explicit assignment exists, but cityId is not in managedCities.
            // Profile territory MUST NOT grant or leak authority.
            return false;
        }

        // 3. No explicit assignments exist at all:
        // Pure legacy city admin fallback ONLY if:
        // users.city_id matches target AND users.district_id is null.
        if (!empty($admin->city_id) && (int) $admin->city_id === (int) $cityId && empty($admin->district_id)) {
            return true;
        }

        return false;
    }

    /**
     * Get list of city IDs where the admin has explicit city-level authority.
     *
     * @param User $admin
     * @return array<int>
     */
     public function getExplicitAdminCityIds(User $admin): array
     {
         if ($this->isSuperAdmin($admin)) {
             return City::where('is_active', true)->pluck('id')->map('intval')->all();
         }
 
         if ($admin->role !== 'admin') {
             return [];
         }
 
         $managedCityIds = $admin->relationLoaded('managedCities')
             ? $admin->managedCities->pluck('id')->all()
             : $admin->managedCities()->allRelatedIds()->all();
 
         $managedCityIds = array_values(array_unique(array_map('intval', $managedCityIds)));
 
         if (!empty($managedCityIds)) {
             return $managedCityIds;
         }
 
         $managedDistrictIds = $admin->relationLoaded('managedDistricts')
             ? $admin->managedDistricts->pluck('id')->all()
             : $admin->managedDistricts()->allRelatedIds()->all();
 
         if (!empty($managedDistrictIds)) {
             // District assignment exists, so profile territory cannot leak or grant city authority
             return [];
         }
 
         // Pure legacy fallback
         if (!empty($admin->city_id) && empty($admin->district_id)) {
             return [(int) $admin->city_id];
         }
 
         return [];
     }
 
     /**
      * Check if admin can access a Help order by its territory.
      *
      * @param User|null $admin
      * @param Help $help
      * @return bool
      */
     public function canAccessHelp(?User $admin, Help $help): bool
     {
         return $this->canAccessTerritory(
             $admin,
             $help->district_id ? (int) $help->district_id : null,
             $help->city_id ? (int) $help->city_id : null
         );
     }
 
     /**
      * Authorize Help access or throw AuthorizationException.
      *
      * @param User|null $admin
      * @param Help $help
      * @param string $message
      * @return void
      *
      * @throws AuthorizationException
      */
     public function authorizeHelp(?User $admin, Help $help, string $message = 'Anda tidak memiliki wewenang untuk mengakses pekerjaan di luar wilayah Anda.'): void
     {
         $this->authorizeTerritory(
             $admin,
             $help->district_id ? (int) $help->district_id : null,
             $help->city_id ? (int) $help->city_id : null,
             $message
         );
     }
 
    /**
     * Check if user is SuperAdmin (accepting canonical 'super_admin' and historical alias 'superadmin').
     */
    protected function isSuperAdmin(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'superadmin'], true);
    }
}
