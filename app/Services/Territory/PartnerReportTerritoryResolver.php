<?php

namespace App\Services\Territory;

use App\Models\Help;
use App\Models\PartnerReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class PartnerReportTerritoryResolver
{
    /**
     * Resolves the single canonical territory for a PartnerReport instance.
     *
     * Returns:
     * [
     *     'district_id' => ?int,
     *     'city_id'     => ?int,
     *     'source'      => ?string, // 'help' | 'profile_reporter' | 'profile_reported_user' | 'report_snapshot' | null
     * ]
     */
    public function resolve(PartnerReport $report): array
    {
        // 1. HELP CONTEXT WINS: If record has a valid Help relation
        $help = $report->reportedHelp 
            ?? ($report->reported_help_id ? Help::find($report->reported_help_id) : null)
            ?? ($report->help_id ? Help::find($report->help_id) : null);

        if ($help) {
            return [
                'district_id' => $help->district_id ? (int) $help->district_id : null,
                'city_id'     => $help->city_id ? (int) $help->city_id : null,
                'source'      => 'help',
            ];
        }

        // 2. Non-Help general support (dukungan_umum): reporter current Profile Territory
        if ($report->report_type === 'dukungan_umum') {
            $reporter = $report->reporter?->fresh() ?? $report->reporter ?? ($report->reporter_id ? User::find($report->reporter_id) : null);
            if ($reporter) {
                return [
                    'district_id' => $reporter->district_id ? (int) $reporter->district_id : null,
                    'city_id'     => $reporter->city_id ? (int) $reporter->city_id : null,
                    'source'      => 'profile_reporter',
                ];
            }
        }

        // 3. Non-Help report against another user: reported user current Profile Territory
        if ($report->reported_user_id) {
            $reportedUser = $report->reportedUser?->fresh() ?? $report->reportedUser ?? User::find($report->reported_user_id);
            if ($reportedUser && ($reportedUser->district_id || $reportedUser->city_id)) {
                return [
                    'district_id' => $reportedUser->district_id ? (int) $reportedUser->district_id : null,
                    'city_id'     => $reportedUser->city_id ? (int) $reportedUser->city_id : null,
                    'source'      => 'profile_reported_user',
                ];
            }

            return [
                'district_id' => null,
                'city_id'     => null,
                'source'      => 'unresolved',
            ];
        }

        // 4. Non-Help + NOT dukungan_umum + NO reported_user_id:
        // DO NOT fallback to reporter profile! Fail-closed as UNRESOLVED.
        \Illuminate\Support\Facades\Log::warning("[PartnerReportTerritoryResolver] Report #{$report->id} has no Help context, is not dukungan_umum, and has no reported_user_id. Territory is UNRESOLVED.");
        return [
            'district_id' => null,
            'city_id'     => null,
            'source'      => 'unresolved',
        ];
    }

    /**
     * Applies canonical territory scoping for PartnerReport listing/stats queries.
     * Enforces single-region canonical ownership and prevents multi-region leaks.
     */
    public function applyAdminTerritoryScope(Builder $query, User $admin): Builder
    {
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        if (!$isSuperAdmin) {
            $districtIds = $admin->getEffectiveAdminDistrictIds();
            $adminCityId = $admin->city_id;

            if (!empty($districtIds)) {
                return $query->where(function (Builder $q) use ($districtIds) {
                    // Branch 1: Help context exists -> Help district matches
                    $q->where(function (Builder $hq) use ($districtIds) {
                        $hq->where(function ($sub) {
                            $sub->whereNotNull('reported_help_id')
                                ->orWhereNotNull('help_id');
                        })->whereHas('reportedHelp', fn($sub) => $sub->whereIn('district_id', $districtIds));
                    })
                    // Branch 2: Non-Help dukungan_umum -> Reporter profile district matches
                    ->orWhere(function (Builder $sq) use ($districtIds) {
                        $sq->whereNull('reported_help_id')
                           ->whereNull('help_id')
                           ->where('report_type', 'dukungan_umum')
                           ->whereHas('reporter', fn($sub) => $sub->whereIn('district_id', $districtIds));
                    })
                    // Branch 3: Non-Help user report -> Reported user profile district matches
                    ->orWhere(function (Builder $uq) use ($districtIds) {
                        $uq->whereNull('reported_help_id')
                           ->whereNull('help_id')
                           ->where(function ($sub) {
                               $sub->whereNull('report_type')
                                   ->orWhere('report_type', '!=', 'dukungan_umum');
                           })
                           ->whereNotNull('reported_user_id')
                           ->whereHas('reportedUser', fn($sub) => $sub->whereIn('district_id', $districtIds));
                    });
                });
            } elseif ($adminCityId && method_exists($admin, 'isCityOnlyAdmin') && $admin->isCityOnlyAdmin()) {
                // Explicit City-only Admin (city-level fallback)
                return $query->where(function (Builder $q) use ($adminCityId) {
                    $q->where(function (Builder $hq) use ($adminCityId) {
                        $hq->where(function ($sub) {
                            $sub->whereNotNull('reported_help_id')
                                ->orWhereNotNull('help_id');
                        })->whereHas('reportedHelp', fn($sub) => $sub->where('city_id', $adminCityId));
                    })
                    ->orWhere(function (Builder $sq) use ($adminCityId) {
                        $sq->whereNull('reported_help_id')
                           ->whereNull('help_id')
                           ->where('report_type', 'dukungan_umum')
                           ->whereHas('reporter', fn($sub) => $sub->where('city_id', $adminCityId));
                    })
                    ->orWhere(function (Builder $uq) use ($adminCityId) {
                        $uq->whereNull('reported_help_id')
                           ->whereNull('help_id')
                           ->where(function ($sub) {
                               $sub->whereNull('report_type')
                                   ->orWhere('report_type', '!=', 'dukungan_umum');
                           })
                           ->whereNotNull('reported_user_id')
                           ->whereHas('reportedUser', fn($sub) => $sub->where('city_id', $adminCityId));
                    });
                });
            } else {
                return $query->whereRaw('1 = 0');
            }
        }

        // SuperAdmin scoping
        $saTerritory = $admin->getActiveSuperadminTerritory();
        if ($saTerritory && $saTerritory['type'] === 'district' && !empty($saTerritory['id'])) {
            $dId = (int) $saTerritory['id'];
            return $query->where(function (Builder $q) use ($dId) {
                $q->where(function (Builder $hq) use ($dId) {
                    $hq->where(function ($sub) {
                        $sub->whereNotNull('reported_help_id')
                            ->orWhereNotNull('help_id');
                    })->whereHas('reportedHelp', fn($sub) => $sub->where('district_id', $dId));
                })
                ->orWhere(function (Builder $sq) use ($dId) {
                    $sq->whereNull('reported_help_id')
                       ->whereNull('help_id')
                       ->where('report_type', 'dukungan_umum')
                       ->whereHas('reporter', fn($sub) => $sub->where('district_id', $dId));
                })
                ->orWhere(function (Builder $uq) use ($dId) {
                    $uq->whereNull('reported_help_id')
                       ->whereNull('help_id')
                       ->where(function ($sub) {
                           $sub->whereNull('report_type')
                               ->orWhere('report_type', '!=', 'dukungan_umum');
                       })
                       ->whereNotNull('reported_user_id')
                       ->whereHas('reportedUser', fn($sub) => $sub->where('district_id', $dId));
                });
            });
        } elseif ($saTerritory && $saTerritory['type'] === 'city' && !empty($saTerritory['id'])) {
            $cId = (int) $saTerritory['id'];
            $saDistrictIds = $admin->getEffectiveSuperadminDistrictIds();

            return $query->where(function (Builder $q) use ($cId, $saDistrictIds) {
                $q->where(function (Builder $hq) use ($cId, $saDistrictIds) {
                    $hq->where(function ($sub) {
                        $sub->whereNotNull('reported_help_id')
                            ->orWhereNotNull('help_id');
                    })->whereHas('reportedHelp', function ($sub) use ($cId, $saDistrictIds) {
                        $sub->where('city_id', $cId);
                        if (!empty($saDistrictIds)) $sub->orWhereIn('district_id', $saDistrictIds);
                    });
                })
                ->orWhere(function (Builder $sq) use ($cId, $saDistrictIds) {
                    $sq->whereNull('reported_help_id')
                       ->whereNull('help_id')
                       ->where('report_type', 'dukungan_umum')
                       ->whereHas('reporter', function ($sub) use ($cId, $saDistrictIds) {
                           $sub->where('city_id', $cId);
                           if (!empty($saDistrictIds)) $sub->orWhereIn('district_id', $saDistrictIds);
                       });
                })
                ->orWhere(function (Builder $uq) use ($cId, $saDistrictIds) {
                    $uq->whereNull('reported_help_id')
                       ->whereNull('help_id')
                       ->where(function ($sub) {
                           $sub->whereNull('report_type')
                               ->orWhere('report_type', '!=', 'dukungan_umum');
                       })
                       ->whereNotNull('reported_user_id')
                       ->whereHas('reportedUser', function ($sub) use ($cId, $saDistrictIds) {
                           $sub->where('city_id', $cId);
                           if (!empty($saDistrictIds)) $sub->orWhereIn('district_id', $saDistrictIds);
                       });
                });
            });
        }

        // All territories (Superadmin default)
        return $query;
    }
}
