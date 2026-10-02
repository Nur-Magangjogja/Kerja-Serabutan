<?php

namespace App\Services\Territory;

use App\Models\ActivityLog;
use App\Models\City;
use App\Models\District;
use App\Models\Province;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProfileTerritoryMigrationService
{
    /**
     * Migrate a customer or mitra user's identity/profile territory.
     *
     * @param User $actor The performing admin or superadmin
     * @param User $targetUser The user to be migrated (customer or mitra)
     * @param int $newCityId Destination City ID
     * @param int|null $newDistrictId Destination District ID
     * @param string $reason Justification for migration (required)
     * @param int|null $newProvinceId Optional Destination Province ID for hierarchy check
     * @return array{success: bool, user: User, activity_log: ActivityLog}
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function migrate(
        User $actor,
        User $targetUser,
        int $newCityId,
        ?int $newDistrictId,
        string $reason,
        ?int $newProvinceId = null
    ): array {
        // 1. Authorize Actor
        $isSuperAdmin = in_array($actor->role, ['super_admin', 'superadmin'], true);
        $isAdmin = ($actor->role === 'admin');

        if (!$isSuperAdmin && !$isAdmin) {
            throw new AuthorizationException('Hanya SuperAdmin dan Admin Wilayah yang berwenang melakukan migrasi wilayah profil.');
        }

        // 2. Validate Target User Role & Identity
        if (!in_array($targetUser->role, ['customer', 'mitra'], true)) {
            throw new ValidationException(validator([], []), [
                'target_user' => ['Hanya profil Customer dan Mitra yang dapat dimigrasikan.'],
            ]);
        }

        if ((int) $actor->id === (int) $targetUser->id) {
            throw new AuthorizationException('Anda tidak dapat melakukan migrasi pada akun Anda sendiri.');
        }

        $reason = trim($reason);
        if (empty($reason)) {
            throw ValidationException::withMessages([
                'reason' => 'Alasan migrasi wilayah wajib diisi.',
            ]);
        }

        // 3. Admin Wilayah Scope Validation
        if ($isAdmin) {
            $adminDistrictIds = $actor->getAdminDistrictIds();
            $adminCityIds = $actor->getAdminCityIds();

            // Check current user territory ownership:
            // Admin must own the current territory of the user (cannot "pull" user from outside)
            $ownsCurrentTerritory = false;
            if (!empty($targetUser->district_id)) {
                $ownsCurrentTerritory = in_array((int) $targetUser->district_id, $adminDistrictIds, true);
            } elseif (!empty($targetUser->city_id)) {
                $ownsCurrentTerritory = in_array((int) $targetUser->city_id, $adminCityIds, true);
            }

            if (!$ownsCurrentTerritory) {
                throw new AuthorizationException('Pengguna saat ini berada di luar wilayah kewenangan Anda.');
            }

            // Check destination territory ownership:
            // Destination must also be within the admin's effective authority
            $ownsDestination = false;
            if (!empty($newDistrictId)) {
                $ownsDestination = in_array((int) $newDistrictId, $adminDistrictIds, true);
            } elseif (!empty($newCityId)) {
                $ownsDestination = in_array((int) $newCityId, $adminCityIds, true);
            }

            if (!$ownsDestination) {
                throw new AuthorizationException('Perpindahan ke wilayah di luar kewenangan Anda harus dilakukan oleh SuperAdmin.');
            }
        }

        // 4. Validate Destination Hierarchy
        $newCity = City::find($newCityId);
        if (!$newCity) {
            throw ValidationException::withMessages([
                'new_city_id' => 'Kota tujuan tidak valid dalam sistem.',
            ]);
        }

        $newProvince = null;
        if (!empty($newProvinceId)) {
            $newProvince = Province::find($newProvinceId);
            if (!$newProvince) {
                throw ValidationException::withMessages([
                    'new_province_id' => 'Provinsi tujuan tidak valid dalam sistem.',
                ]);
            }

            // Revalidate City belongs to Province
            $belongsToProvince = false;
            if (!empty($newCity->province_id)) {
                $belongsToProvince = ((int) $newCity->province_id === (int) $newProvince->id);
            } elseif (!empty($newCity->province)) {
                $belongsToProvince = (strcasecmp(trim($newCity->province), trim($newProvince->name)) === 0);
            }

            if (!$belongsToProvince) {
                throw ValidationException::withMessages([
                    'new_city_id' => 'Kota yang dipilih tidak berada dalam provinsi yang dipilih.',
                ]);
            }
        } else {
            $newProvince = $newCity->provinceRelation ?: Province::where('name', $newCity->province)->first();
        }

        $newDistrict = null;
        $cityHasDistricts = $newCity->districts()->exists();

        if ($cityHasDistricts && empty($newDistrictId)) {
            throw ValidationException::withMessages([
                'new_district_id' => 'Kecamatan tujuan wajib dipilih untuk kota ini.',
            ]);
        }

        if (!empty($newDistrictId)) {
            $newDistrict = District::find($newDistrictId);
            if (!$newDistrict) {
                throw ValidationException::withMessages([
                    'new_district_id' => 'Kecamatan tujuan tidak valid dalam sistem.',
                ]);
            }

            // Revalidate District belongs to City
            if ((int) $newDistrict->city_id !== (int) $newCity->id) {
                throw ValidationException::withMessages([
                    'new_district_id' => 'Kecamatan yang dipilih tidak berada dalam kota yang dipilih.',
                ]);
            }
        }

        // 5. Same Territory Check (No fake mutation)
        $isSameCity = ((int) $targetUser->city_id === (int) $newCity->id);
        $isSameDistrict = ((int) ($targetUser->district_id ?? 0) === (int) ($newDistrict?->id ?? 0));

        if ($isSameCity && $isSameDistrict) {
            throw ValidationException::withMessages([
                'destination' => 'Wilayah tujuan sama dengan wilayah saat ini.',
            ]);
        }

        // 6. Execute Atomic Transaction
        return DB::transaction(function () use ($actor, $targetUser, $newCity, $newDistrict, $newProvince, $reason) {
            /** @var User $user */
            $user = User::where('id', $targetUser->id)->lockForUpdate()->firstOrFail();

            $oldCityId = $user->city_id;
            $oldDistrictId = $user->district_id;
            $oldProvinceId = $user->cityRelation?->province_id
                ?? Province::where('name', $user->province)->value('id');

            // Mutate ONLY identity profile territory columns
            $user->city_id = $newCity->id;
            $user->district_id = $newDistrict?->id;
            $user->city = $newCity->name;
            $user->province = $newProvince?->name ?? $newCity->province;
            $user->kecamatan = $newDistrict?->name;
            $user->save();

            // Record ActivityLog
            $log = ActivityLog::record(
                $actor,
                'profile_territory_migrated',
                "Migrasi wilayah profil pengguna {$user->name} oleh {$actor->name}",
                [
                    'target_user_id'  => $user->id,
                    'target_role'     => $user->role,
                    'old_city_id'     => $oldCityId,
                    'old_district_id' => $oldDistrictId,
                    'old_province_id' => $oldProvinceId,
                    'new_city_id'     => $newCity->id,
                    'new_district_id' => $newDistrict?->id,
                    'new_province_id' => $newProvince?->id,
                    'changed_by'      => $actor->id,
                    'reason'          => $reason,
                ]
            );

            if (!$log) {
                throw new \RuntimeException('Gagal mencatat log aktivitas migrasi wilayah.');
            }

            return [
                'success'      => true,
                'user'         => $user,
                'activity_log' => $log,
            ];
        });
    }
}
