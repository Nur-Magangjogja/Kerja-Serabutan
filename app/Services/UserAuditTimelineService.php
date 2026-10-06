<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\BalanceTransaction;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\PartnerReport;
use App\Models\User;
use App\Models\UserGreylistLog;
use App\Models\WithdrawRequest;
use App\Services\Territory\AdminTerritoryAuthorizationService;
use App\Services\Territory\PartnerReportTerritoryResolver;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserAuditTimelineService
{
    public function __construct(
        protected AdminTerritoryAuthorizationService $authService,
        protected PartnerReportTerritoryResolver $reportResolver
    ) {}

    /**
     * Resolve, normalize, deduplicate, and paginate consolidated audit timeline entries for a user.
     * Supports full historical reach with bounded per-request query memory via unified database pagination.
     *
     * @param User $targetUser The user whose history is being audited
     * @param User $viewingAdmin The authenticated admin requesting the audit
     * @param int $page Current page number
     * @param int $perPage Entries per page (default: 15)
     * @param string $filter Event type filter ('all', 'help', 'cancel_dispute', 'report', 'discipline', 'financial')
     * @return LengthAwarePaginator
     */
    public function getTimelineForUser(
        User $targetUser,
        User $viewingAdmin,
        int $page = 1,
        int $perPage = 15,
        string $filter = 'all'
    ): LengthAwarePaginator {
        $filter = match ($filter) {
            'job', 'jobs', 'help', 'helps'                  => 'help',
            'cancel', 'cancellation', 'dispute', 'disputes' => 'cancel_dispute',
            'report', 'reports'                             => 'report',
            'discipline', 'disciplinary', 'sp'              => 'discipline',
            'financial', 'wallet', 'finance'                => 'financial',
            default                                         => $filter,
        };

        $queries = [];

        // 1. HELPS (Customer & Mitra)
        if ($filter === 'all' || $filter === 'help') {
            $queries[] = DB::table('helps')
                ->select(
                    DB::raw("'help' as source_type"),
                    'id as source_id',
                    'created_at as occurred_at'
                )
                ->where(function ($q) use ($targetUser) {
                    $q->where('user_id', $targetUser->id)
                      ->orWhere('mitra_id', $targetUser->id);
                });
        }

        // 2. CANCELLATION REQUESTS & DISPUTES
        if ($filter === 'all' || $filter === 'cancel_dispute') {
            // Canonical cancellation query using existing participant fields (NO requested_by)
            $queries[] = DB::table('help_cancel_requests')
                ->select(
                    DB::raw("'cancellation' as source_type"),
                    'id as source_id',
                    'created_at as occurred_at'
                )
                ->where(function ($q) use ($targetUser) {
                    $q->where('customer_id', $targetUser->id)
                      ->orWhere('partner_id', $targetUser->id);
                });

            // Disputed escrow events
            $queries[] = DB::table('helps')
                ->select(
                    DB::raw("'dispute' as source_type"),
                    'id as source_id',
                    DB::raw("COALESCE(dispute_resolved_at, updated_at, created_at) as occurred_at")
                )
                ->where(function ($q) use ($targetUser) {
                    $q->where('user_id', $targetUser->id)
                      ->orWhere('mitra_id', $targetUser->id);
                })
                ->where(function ($q) {
                    $q->where('escrow_status', Help::ESCROW_STATUS_DISPUTED_FREEZE)
                      ->orWhereNotNull('dispute_reason')
                      ->orWhereNotNull('dispute_resolved_at');
                });
        }

        // 3. PARTNER REPORTS & INVESTIGATIONS
        if ($filter === 'all' || $filter === 'report') {
            $queries[] = DB::table('partner_reports')
                ->select(
                    DB::raw("'report' as source_type"),
                    'id as source_id',
                    'created_at as occurred_at'
                )
                ->where(function ($q) use ($targetUser) {
                    $q->where('reporter_id', $targetUser->id)
                      ->orWhere('reported_user_id', $targetUser->id);
                });
        }

        // 4. DISCIPLINARY & MODERATION (SP, Greylist, Shadow Ban, Pardons)
        if ($filter === 'all' || $filter === 'discipline') {
            $queries[] = DB::table('user_greylist_logs')
                ->select(
                    DB::raw("'discipline' as source_type"),
                    'id as source_id',
                    'created_at as occurred_at'
                )
                ->where('user_id', $targetUser->id);

            // Deterministic ActivityLog query for administrative moderation
            $regQuery = DB::table('registrations')->where('email', $targetUser->email);
            if (!empty($targetUser->nik)) {
                $regQuery->orWhere('nik', $targetUser->nik);
            }
            $regIds = $regQuery->pluck('id')->all();

            $queries[] = DB::table('activity_logs')
                ->select(
                    DB::raw("'activity' as source_type"),
                    'id as source_id',
                    'created_at as occurred_at'
                )
                ->whereIn('action', [
                    'profile_territory_migrated',
                    'partner_blocked',
                    'partner_unblocked',
                    'ktp_verified',
                    'ktp_rejected',
                    'vehicle_verified',
                    'vehicle_rejected',
                    'konsep1_pardon',
                ])
                ->where(function ($sq) use ($targetUser, $regIds) {
                    $sq->where('properties->target_user_id', $targetUser->id)
                       ->orWhere('properties->user_id', $targetUser->id);
                    if (!empty($regIds)) {
                        foreach ($regIds as $rId) {
                            $sq->orWhere('properties->registration_id', $rId);
                        }
                    }
                    if (!empty($targetUser->email)) {
                        $sq->orWhere('properties->email', $targetUser->email);
                    }
                });
        }

        // 5. FINANCIAL TRANSACTIONS
        if ($filter === 'all' || $filter === 'financial') {
            // WithdrawRequest is canonical for withdraw lifecycle
            $queries[] = DB::table('withdraw_requests')
                ->select(
                    DB::raw("'withdraw' as source_type"),
                    'id as source_id',
                    'created_at as occurred_at'
                )
                ->where('user_id', $targetUser->id);

            // BalanceTransaction: exclude withdraw ledger records to prevent duplicate withdraw cards
            $queries[] = DB::table('balance_transactions')
                ->select(
                    DB::raw("'financial' as source_type"),
                    'id as source_id',
                    'created_at as occurred_at'
                )
                ->where('user_id', $targetUser->id)
                ->where('type', '!=', 'withdraw')
                ->where(function ($q) {
                    $q->whereNull('order_id')
                      ->orWhere(function ($sq) {
                          $sq->where('order_id', 'not like', 'WD-%')
                             ->where('order_id', 'not like', 'REFUND-WD-%');
                      });
                });
        }

        if (empty($queries)) {
            return new LengthAwarePaginator(collect(), 0, $perPage, $page, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
            ]);
        }

        // Chain subqueries using unionAll for unified deterministic sorting
        $primaryQuery = array_shift($queries);
        foreach ($queries as $subQuery) {
            $primaryQuery->unionAll($subQuery);
        }

        // Bounded database-level total count
        $total = (int) DB::table($primaryQuery, 'timeline_u')->count();

        // Safe pagination offset
        $offset = max(0, ($page - 1) * $perPage);

        // Fetch ONLY the exact page-sized window from database
        $pageItems = DB::table($primaryQuery, 'timeline_u')
            ->orderBy('occurred_at', 'desc')
            ->orderBy('source_id', 'desc')
            ->limit($perPage)
            ->offset($offset)
            ->get();

        if ($pageItems->isEmpty()) {
            return new LengthAwarePaginator(collect(), $total, $perPage, $page, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
            ]);
        }

        // Batch hydration for the fetched page items only
        $helpIds = $pageItems->where('source_type', 'help')->pluck('source_id')->all();
        $cancelIds = $pageItems->where('source_type', 'cancellation')->pluck('source_id')->all();
        $disputeIds = $pageItems->where('source_type', 'dispute')->pluck('source_id')->all();
        $reportIds = $pageItems->where('source_type', 'report')->pluck('source_id')->all();
        $disciplineIds = $pageItems->where('source_type', 'discipline')->pluck('source_id')->all();
        $withdrawIds = $pageItems->where('source_type', 'withdraw')->pluck('source_id')->all();
        $financialIds = $pageItems->where('source_type', 'financial')->pluck('source_id')->all();
        $activityIds = $pageItems->where('source_type', 'activity')->pluck('source_id')->all();

        $helps = !empty($helpIds)
            ? Help::with(['district', 'city', 'mitra', 'user'])->whereIn('id', $helpIds)->get()->keyBy('id')
            : collect();

        $cancels = !empty($cancelIds)
            ? HelpCancelRequest::with(['help.district', 'help.city', 'district', 'reviewedBy'])->whereIn('id', $cancelIds)->get()->keyBy('id')
            : collect();

        $disputes = !empty($disputeIds)
            ? Help::with(['district', 'city', 'mitra', 'user'])->whereIn('id', $disputeIds)->get()->keyBy('id')
            : collect();

        $reports = !empty($reportIds)
            ? PartnerReport::with([
                'reportedHelp.district', 'reportedHelp.city',
                'reporter.district', 'reporter.city',
                'reportedUser.district', 'reportedUser.city',
                'resolvedBy'
            ])->whereIn('id', $reportIds)->get()->keyBy('id')
            : collect();

        $disciplines = !empty($disciplineIds)
            ? UserGreylistLog::with(['admin', 'partnerReport.reportedHelp.district', 'partnerReport.reportedHelp.city'])->whereIn('id', $disciplineIds)->get()->keyBy('id')
            : collect();

        $withdraws = !empty($withdrawIds)
            ? WithdrawRequest::with(['reviewedBy'])->whereIn('id', $withdrawIds)->get()->keyBy('id')
            : collect();

        $financials = !empty($financialIds)
            ? BalanceTransaction::with(['approvedBy'])->whereIn('id', $financialIds)->get()->keyBy('id')
            : collect();

        $activities = !empty($activityIds)
            ? ActivityLog::with('user')->whereIn('id', $activityIds)->get()->keyBy('id')
            : collect();

        // Map items to normalized canonical timeline cards
        $cards = collect();
        foreach ($pageItems as $item) {
            $card = match ($item->source_type) {
                'help'         => isset($helps[$item->source_id]) ? $this->mapHelpCard($helps[$item->source_id], $targetUser, $viewingAdmin) : null,
                'cancellation' => isset($cancels[$item->source_id]) ? $this->mapCancellationCard($cancels[$item->source_id], $targetUser, $viewingAdmin) : null,
                'dispute'      => isset($disputes[$item->source_id]) ? $this->mapDisputeCard($disputes[$item->source_id], $targetUser, $viewingAdmin) : null,
                'report'       => isset($reports[$item->source_id]) ? $this->mapReportCard($reports[$item->source_id], $targetUser, $viewingAdmin) : null,
                'discipline'   => isset($disciplines[$item->source_id]) ? $this->mapDisciplineCard($disciplines[$item->source_id], $targetUser, $viewingAdmin) : null,
                'withdraw'     => isset($withdraws[$item->source_id]) ? $this->mapWithdrawCard($withdraws[$item->source_id], $targetUser, $viewingAdmin) : null,
                'financial'    => isset($financials[$item->source_id]) ? $this->mapFinancialCard($financials[$item->source_id], $targetUser, $viewingAdmin) : null,
                'activity'     => isset($activities[$item->source_id]) ? $this->mapActivityCard($activities[$item->source_id], $targetUser, $viewingAdmin) : null,
                default        => null,
            };

            if ($card) {
                $cards->push($card);
            }
        }

        return new LengthAwarePaginator(
            $cards,
            $total,
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
    }

    /**
     * Map Help order into timeline card.
     */
    protected function mapHelpCard(Help $help, User $targetUser, User $viewingAdmin): array
    {
        $isCustomer = (int) $help->user_id === (int) $targetUser->id;
        $userRole = $isCustomer ? 'Customer' : 'Mitra Bertugas';
        $partnerName = $isCustomer ? ($help->mitra->name ?? 'Belum ada mitra') : ($help->user->name ?? 'Customer');

        $canAccessCase = $this->authService->canAccessTerritory($viewingAdmin, $help->district_id, $help->city_id);
        $routeBase = $this->isSuperAdmin($viewingAdmin) ? 'superadmin.helps.approved' : 'admin.helps';
        $caseRoute = $canAccessCase ? ($this->safeRoute($routeBase, ['search' => $help->order_id ?: $help->id]) ?? $this->safeRoute('admin.helps', ['search' => $help->order_id ?: $help->id])) : null;

        $cityName = $this->extractCityName($help->cityRelation ?? $help->city);
        $incidentTerritory = $this->formatTerritory($help->district?->name, $cityName);
        $isCrossTerritory = $this->isCrossTerritory($targetUser, $help->district_id, $help->city_id);

        return [
            'event_key'            => "help:{$help->id}:order",
            'occurred_at'          => $help->created_at,
            'event_type'           => 'help',
            'event_type_label'     => 'Pekerjaan (Help)',
            'user_role'            => $userRole,
            'source_type'          => 'Help',
            'source_id'            => $help->id,
            'public_ref'           => $help->order_id ?: "SRB-{$help->id}",
            'title'                => $help->title,
            'summary'              => "Pekerjaan: {$help->title}. Total: Rp " . number_format((float) $help->total_amount, 0, ',', '.') . " ({$userRole} berpasangan dengan {$partnerName})",
            'status'               => $help->status,
            'status_label'         => $this->formatStatusLabel($help->status),
            'incident_city_id'     => $help->city_id,
            'incident_district_id' => $help->district_id,
            'incident_territory'   => $incidentTerritory,
            'is_cross_territory'   => $isCrossTerritory,
            'actor_name'           => $help->mitra->name ?? null,
            'monetary_amount'      => (float) $help->total_amount,
            'can_access_case'      => $canAccessCase,
            'case_route'           => $caseRoute,
        ];
    }

    /**
     * Map HelpCancelRequest into timeline card.
     */
    protected function mapCancellationCard(HelpCancelRequest $cr, User $targetUser, User $viewingAdmin): array
    {
        $isRequester = ($cr->requester_type === 'customer' && (int) $cr->customer_id === (int) $targetUser->id)
            || ($cr->requester_type === 'partner' && (int) $cr->partner_id === (int) $targetUser->id);

        if ($isRequester) {
            $userRole = 'Pemohon Pembatalan';
        } elseif ((int) $cr->customer_id === (int) $targetUser->id) {
            $userRole = 'Customer Terdampak';
        } elseif ((int) $cr->partner_id === (int) $targetUser->id) {
            $userRole = 'Mitra Terdampak';
        } else {
            $userRole = 'Pihak Terkait';
        }

        $help = $cr->help;
        $incidentDistrictId = $help?->district_id ?? $cr->district_id;
        $incidentCityId = $help?->city_id ?? $cr->city_id;

        $cityName = $this->extractCityName($help?->cityRelation ?? $help?->city);
        $districtName = $cr->district?->name ?? $help?->district?->name;
        $incidentTerritory = $this->formatTerritory($districtName, $cityName);
        $isCrossTerritory = $this->isCrossTerritory($targetUser, $incidentDistrictId, $incidentCityId);

        $canAccessCase = $this->authService->canAccessTerritory($viewingAdmin, $incidentDistrictId, $incidentCityId);
        $routeBase = $this->isSuperAdmin($viewingAdmin) ? 'superadmin.cancellations.index' : 'admin.cancellations.index';
        $caseRoute = $canAccessCase ? ($this->safeRoute($routeBase, ['selectedCancelId' => $cr->id]) ?? $this->safeRoute('admin.cancellations.index', ['selectedCancelId' => $cr->id])) : null;

        $adminName = $cr->reviewedBy?->name ?? ($cr->admin_id ? 'Admin Wilayah' : null);

        return [
            'event_key'            => "cancellation:{$cr->id}:request",
            'occurred_at'          => $cr->created_at,
            'event_type'           => 'cancellation',
            'event_type_label'     => 'Pengajuan Pembatalan',
            'user_role'            => $userRole,
            'source_type'          => 'HelpCancelRequest',
            'source_id'            => $cr->id,
            'public_ref'           => "CANCEL-{$cr->id}",
            'title'                => "Pembatalan Tugas (" . ($help?->title ?? 'Pekerjaan') . ")",
            'summary'              => "Alasan pembatalan: {$cr->reason}" . ($cr->rejection_reason ? ". Alasan tolak admin: {$cr->rejection_reason}" : ''),
            'status'               => $cr->status,
            'status_label'         => $this->formatStatusLabel($cr->status, $cr->admin_decision),
            'incident_city_id'     => $incidentCityId,
            'incident_district_id' => $incidentDistrictId,
            'incident_territory'   => $incidentTerritory,
            'is_cross_territory'   => $isCrossTerritory,
            'actor_name'           => $adminName,
            'monetary_amount'      => (float) ($cr->refund_amount ?? 0),
            'can_access_case'      => $canAccessCase,
            'case_route'           => $caseRoute,
        ];
    }

    /**
     * Map Disputed Help into timeline card.
     */
    protected function mapDisputeCard(Help $dh, User $targetUser, User $viewingAdmin): array
    {
        $isCustomer = (int) $dh->user_id === (int) $targetUser->id;
        $userRole = $isCustomer ? 'Customer' : 'Mitra';

        $canAccessCase = $this->authService->canAccessTerritory($viewingAdmin, $dh->district_id, $dh->city_id);
        $routeBase = $this->isSuperAdmin($viewingAdmin) ? 'superadmin.disputes.index' : 'admin.disputes.index';
        $caseRoute = $canAccessCase ? ($this->safeRoute($routeBase, ['search' => $dh->order_id ?: $dh->id]) ?? $this->safeRoute('admin.disputes.index', ['search' => $dh->order_id ?: $dh->id])) : null;

        $cityName = $this->extractCityName($dh->cityRelation ?? $dh->city);
        $incidentTerritory = $this->formatTerritory($dh->district?->name, $cityName);
        $isCrossTerritory = $this->isCrossTerritory($targetUser, $dh->district_id, $dh->city_id);

        $adminName = !empty($dh->dispute_resolved_by) ? User::find($dh->dispute_resolved_by)?->name : null;

        return [
            'event_key'            => "dispute:{$dh->id}:frozen",
            'occurred_at'          => $dh->dispute_resolved_at ?? $dh->updated_at ?? $dh->created_at,
            'event_type'           => 'dispute',
            'event_type_label'     => 'Sengketa Escrow',
            'user_role'            => $userRole,
            'source_type'          => 'Help',
            'source_id'            => $dh->id,
            'public_ref'           => "DISPUTE-{$dh->id}",
            'title'                => "Sengketa Dana Pekerjaan: {$dh->title}",
            'summary'              => "Sengketa pada order {$dh->title}: " . ($dh->dispute_reason ?: 'Dana dibekukan untuk mediasi admin'),
            'status'               => $dh->escrow_status,
            'status_label'         => $dh->dispute_resolved_at ? 'Sengketa Terselesaikan' : 'Dana Escrow Dibekukan',
            'incident_city_id'     => $dh->city_id,
            'incident_district_id' => $dh->district_id,
            'incident_territory'   => $incidentTerritory,
            'is_cross_territory'   => $isCrossTerritory,
            'actor_name'           => $adminName,
            'monetary_amount'      => (float) $dh->total_amount,
            'can_access_case'      => $canAccessCase,
            'case_route'           => $caseRoute,
        ];
    }

    /**
     * Map PartnerReport into timeline card.
     */
    protected function mapReportCard(PartnerReport $rep, User $targetUser, User $viewingAdmin): array
    {
        $isReporter = (int) $rep->reporter_id === (int) $targetUser->id;
        $userRole = $isReporter ? 'Pelapor' : 'Pihak Terlapor';

        $canonical = $this->reportResolver->resolve($rep);
        $incidentDistrictId = $canonical['district_id'];
        $incidentCityId = $canonical['city_id'];

        $cityName = $canonical['city_name'] ?? $this->extractCityName($rep->reportedHelp?->city ?? $rep->reporter?->city);
        $districtName = $canonical['district_name'] ?? $rep->reportedHelp?->district?->name;
        $incidentTerritory = $this->formatTerritory($districtName, $cityName);
        $isCrossTerritory = $this->isCrossTerritory($targetUser, $incidentDistrictId, $incidentCityId);

        $canAccessCase = $this->authService->canAccessTerritory($viewingAdmin, $incidentDistrictId, $incidentCityId);
        $routeBase = $this->isSuperAdmin($viewingAdmin) ? 'superadmin.partners.reports.show' : 'admin.partners.reports.show';
        $caseRoute = $canAccessCase ? ($this->safeRoute($routeBase, ['report' => $rep->id]) ?? $this->safeRoute('admin.partners.reports.show', ['report' => $rep->id])) : null;

        $adminName = $rep->resolvedBy?->name;
        $isGeneralSupport = ($rep->report_type === 'dukungan_umum');

        return [
            'event_key'            => "report:{$rep->id}:investigation",
            'occurred_at'          => $rep->created_at,
            'event_type'           => 'report',
            'event_type_label'     => $isGeneralSupport ? 'Dukungan Umum' : 'Laporan Investigasi',
            'user_role'            => $userRole,
            'source_type'          => 'PartnerReport',
            'source_id'            => $rep->id,
            'public_ref'           => "REP-{$rep->id}",
            'title'                => $rep->title ?: ($isGeneralSupport ? 'Tiket Bantuan Layanan' : 'Pengaduan Layanan'),
            'summary'              => "Kategori: " . ucfirst(str_replace('_', ' ', $rep->category)) . ". {$rep->description}",
            'status'               => $rep->status,
            'status_label'         => match ($rep->status) {
                'resolved'         => 'Selesai',
                'in_investigation' => 'Investigasi',
                'action_taken'     => 'Tindakan Diambil',
                default            => ucfirst($rep->status),
            },
            'incident_city_id'     => $incidentCityId,
            'incident_district_id' => $incidentDistrictId,
            'incident_territory'   => $incidentTerritory,
            'is_cross_territory'   => $isCrossTerritory,
            'actor_name'           => $adminName,
            'can_access_case'      => $canAccessCase,
            'case_route'           => $caseRoute,
        ];
    }

    /**
     * Map UserGreylistLog into timeline card.
     */
    protected function mapDisciplineCard(UserGreylistLog $log, User $targetUser, User $viewingAdmin): array
    {
        $adminName = $log->admin?->name ?? 'Admin Wilayah';

        if ($log->partnerReport) {
            $canonical = $this->reportResolver->resolve($log->partnerReport);
            $incidentDistrictId = $canonical['district_id'];
            $incidentCityId = $canonical['city_id'];
            $cityName = $canonical['city_name'] ?? $this->extractCityName($log->partnerReport->reportedHelp?->city ?? $log->partnerReport->reporter?->city);
            $districtName = $canonical['district_name'] ?? $log->partnerReport->reportedHelp?->district?->name;
        } else {
            $incidentDistrictId = $log->admin?->district_id ?? $targetUser->district_id;
            $incidentCityId = $log->admin?->city_id ?? $targetUser->city_id;
            $cityName = $this->extractCityName($log->admin?->cityRelation ?? $log->admin?->city ?? $targetUser->cityRelation ?? $targetUser->city);
            $districtName = $log->admin?->district?->name ?? $targetUser->district?->name;
        }

        $incidentTerritory = $this->formatTerritory($districtName, $cityName);
        $isCrossTerritory = $this->isCrossTerritory($targetUser, $incidentDistrictId, $incidentCityId);

        $canAccessCase = $this->authService->canAccessTerritory($viewingAdmin, $incidentDistrictId, $incidentCityId);
        $routeBase = $this->isSuperAdmin($viewingAdmin) ? 'superadmin.partners.reports.show' : 'admin.partners.reports.show';
        $caseRoute = ($canAccessCase && $log->partner_report_id) ? ($this->safeRoute($routeBase, ['report' => $log->partner_report_id]) ?? $this->safeRoute('admin.partners.reports.show', ['report' => $log->partner_report_id])) : null;

        $actionLabel = match ($log->action) {
            'sp_1'                 => 'Peringatan SP 1',
            'sp_2'                 => 'Peringatan SP 2',
            'sp_3'                 => 'Peringatan Keras SP 3',
            'greylist_add'         => 'Dimasukkan Daftar Abu-Abu',
            'greylist_remove'      => 'Dikeluarkan dari Daftar Abu-Abu',
            'shadow_ban_enabled'   => 'Aktivasi Pembatasan Akun (Shadow Ban)',
            'shadow_ban_disabled'  => 'Pencabutan Pembatasan Akun',
            'pardon'               => 'Pengampunan Pelanggaran',
            default                => ucfirst(str_replace('_', ' ', $log->action)),
        };

        return [
            'event_key'            => "disciplinary:{$log->id}:{$log->action}",
            'occurred_at'          => $log->created_at,
            'event_type'           => 'discipline',
            'event_type_label'     => 'Sanksi Disiplin',
            'user_role'            => 'Subjek Disiplin',
            'source_type'          => 'UserGreylistLog',
            'source_id'            => $log->id,
            'public_ref'           => "SP-{$log->id}",
            'title'                => $actionLabel,
            'summary'              => $log->reason ?: "Tindakan pendisiplinan: {$actionLabel}",
            'status'               => 'active',
            'status_label'         => 'Diberlakukan',
            'incident_city_id'     => $incidentCityId,
            'incident_district_id' => $incidentDistrictId,
            'incident_territory'   => $incidentTerritory,
            'is_cross_territory'   => $isCrossTerritory,
            'actor_name'           => $adminName,
            'sp_level'             => $log->warning_level ?: null,
            'can_access_case'      => $canAccessCase,
            'case_route'           => $caseRoute,
        ];
    }

    /**
     * Map WithdrawRequest into timeline card.
     */
    protected function mapWithdrawCard(WithdrawRequest $wd, User $targetUser, User $viewingAdmin): array
    {
        $adminName = $wd->reviewedBy?->name;

        $cityName = $this->extractCityName($targetUser->cityRelation ?? $targetUser->city);
        $incidentTerritory = $this->formatTerritory($targetUser->district?->name, $cityName);

        $canAccessCase = $this->authService->canAccessTerritory($viewingAdmin, $targetUser->district_id, $targetUser->city_id);
        $routeBase = $this->isSuperAdmin($viewingAdmin) ? 'superadmin.withdraws.index' : 'admin.withdraws.index';
        $caseRoute = $canAccessCase ? ($this->safeRoute($routeBase, ['search' => $wd->account_number]) ?? $this->safeRoute('admin.withdraws.index', ['search' => $wd->account_number])) : null;

        $statusLabel = match ($wd->status) {
            'approved'                   => 'Disetujui',
            'rejected'                   => 'Ditolak',
            'pending', 'waiting_approval'=> 'Menunggu Review',
            default                      => ucfirst($wd->status),
        };

        $summary = "Pengajuan penarikan dana sebesar Rp " . number_format((float) $wd->amount, 0, ',', '.') . " ke rekening {$wd->bank_code} ({$wd->account_number} a.n {$wd->account_name})";
        if ($wd->status === 'approved') {
            $summary .= ". Pencairan telah disetujui & transfer terkirim.";
        } elseif ($wd->status === 'rejected') {
            $summary .= ". Permintaan ditolak (Alasan: " . ($wd->description ?: 'Tidak memenuhi syarat') . "). Saldo dikembalikan ke dompet user.";
        }

        return [
            'event_key'            => "withdraw:{$wd->id}:request",
            'occurred_at'          => $wd->created_at,
            'event_type'           => 'financial',
            'event_type_label'     => 'Penarikan Saldo (Withdraw)',
            'user_role'            => 'Pemilik Dana',
            'source_type'          => 'WithdrawRequest',
            'source_id'            => $wd->id,
            'public_ref'           => "WD-{$wd->id}",
            'title'                => 'Penarikan Saldo (Withdraw)',
            'summary'              => $summary,
            'status'               => $wd->status,
            'status_label'         => $statusLabel,
            'incident_city_id'     => $targetUser->city_id,
            'incident_district_id' => $targetUser->district_id,
            'incident_territory'   => $incidentTerritory,
            'is_cross_territory'   => false,
            'actor_name'           => $adminName,
            'monetary_amount'      => (float) $wd->amount,
            'can_access_case'      => $canAccessCase,
            'case_route'           => $caseRoute,
        ];
    }

    /**
     * Map BalanceTransaction into timeline card.
     */
    protected function mapFinancialCard(BalanceTransaction $tx, User $targetUser, User $viewingAdmin): array
    {
        $adminName = $tx->approvedBy?->name;

        $typeLabel = match ($tx->type) {
            'topup'       => 'Top-up Saldo',
            'withdraw'    => 'Penarikan Dana (Withdraw)',
            'payment'     => 'Pembayaran Jasa',
            'refund'      => 'Pengembalian Dana (Refund)',
            'service_fee' => 'Biaya Layanan Platform',
            'earning'     => 'Penerimaan Upah Kerja',
            'deduction'   => 'Pemotongan Saldo',
            default       => ucfirst(str_replace('_', ' ', $tx->type)),
        };

        $cityName = $this->extractCityName($targetUser->cityRelation ?? $targetUser->city);
        $incidentTerritory = $this->formatTerritory($targetUser->district?->name, $cityName);

        $canAccessCase = $this->authService->canAccessTerritory($viewingAdmin, $targetUser->district_id, $targetUser->city_id);
        $routeBase = $this->isSuperAdmin($viewingAdmin) ? 'superadmin.topup.approvals' : 'admin.topup.approvals';
        $caseRoute = ($canAccessCase && $tx->type === 'topup') ? ($this->safeRoute($routeBase) ?? $this->safeRoute('admin.topup.approvals')) : null;

        $publicRef = $tx->request_code ? "#{$tx->request_code}" : ($tx->order_id ?: "TX-{$tx->id}");

        $statusLabel = match ($tx->status) {
            'waiting_approval' => 'Menunggu Approval',
            'completed', 'success' => 'Berhasil',
            'rejected'         => 'Ditolak',
            'cancelled'        => 'Dibatalkan',
            default            => ucfirst($tx->status ?? 'success'),
        };

        return [
            'event_key'            => "financial:{$tx->id}:balance_tx",
            'occurred_at'          => $tx->created_at,
            'event_type'           => 'financial',
            'event_type_label'     => $typeLabel,
            'user_role'            => 'Pemilik Akun',
            'source_type'          => 'BalanceTransaction',
            'source_id'            => $tx->id,
            'public_ref'           => $publicRef,
            'title'                => $typeLabel,
            'summary'              => "Transaksi {$typeLabel}: Rp " . number_format((float) $tx->amount, 0, ',', '.') . " (" . ($tx->description ?: 'Mutasi saldo') . ")",
            'status'               => $tx->status ?? 'completed',
            'status_label'         => $statusLabel,
            'incident_city_id'     => $targetUser->city_id,
            'incident_district_id' => $targetUser->district_id,
            'incident_territory'   => $incidentTerritory,
            'is_cross_territory'   => false,
            'actor_name'           => $adminName,
            'monetary_amount'      => (float) $tx->amount,
            'can_access_case'      => $canAccessCase,
            'case_route'           => $caseRoute,
        ];
    }

    /**
     * Map ActivityLog into timeline card.
     */
    protected function mapActivityCard(ActivityLog $act, User $targetUser, User $viewingAdmin): array
    {
        $adminName = $act->user?->name ?? 'Admin Wilayah';
        $label = match ($act->action) {
            'profile_territory_migrated' => 'Migrasi Wilayah Profil',
            'partner_blocked'            => 'Akun Diblokir',
            'partner_unblocked'          => 'Blokir Akun Dibuka',
            'ktp_verified'               => 'Verifikasi KTP Disetujui',
            'ktp_rejected'               => 'Verifikasi KTP Ditolak',
            'vehicle_verified'           => 'Verifikasi Kendaraan Disetujui',
            'vehicle_rejected'           => 'Verifikasi Kendaraan Ditolak',
            'konsep1_pardon'             => 'Pengampunan Pembatalan Tugas',
            default                      => ucfirst(str_replace('_', ' ', $act->action)),
        };

        $cityName = $this->extractCityName($targetUser->cityRelation ?? $targetUser->city);

        return [
            'event_key'            => "activity:{$act->id}:{$act->action}",
            'occurred_at'          => $act->created_at,
            'event_type'           => 'admin_action',
            'event_type_label'     => $label,
            'user_role'            => 'Subjek Tindakan Admin',
            'source_type'          => 'ActivityLog',
            'source_id'            => $act->id,
            'public_ref'           => "ACT-{$act->id}",
            'title'                => $label,
            'summary'              => $act->description ?: "Tindakan administrasi: {$label}",
            'status'               => 'recorded',
            'status_label'         => 'Tercatat',
            'incident_city_id'     => $targetUser->city_id,
            'incident_district_id' => $targetUser->district_id,
            'incident_territory'   => $this->formatTerritory($targetUser->district?->name, $cityName),
            'is_cross_territory'   => false,
            'actor_name'           => $adminName,
            'can_access_case'      => true,
            'case_route'           => null,
        ];
    }

    /**
     * Check if an event's territory differs from the user's home profile territory.
     */
    protected function isCrossTerritory(User $targetUser, ?int $districtId, ?int $cityId): bool
    {
        if ($districtId && (int) $districtId !== (int) ($targetUser->district_id ?? 0)) {
            return true;
        }

        if ($cityId && (int) $cityId !== (int) ($targetUser->city_id ?? 0)) {
            return true;
        }

        return false;
    }

    /**
     * Helper to format territory string without throwing on null.
     */
    protected function formatTerritory(?string $districtName, ?string $cityName): string
    {
        $parts = [];
        if ($districtName) {
            $parts[] = "Kec. {$districtName}";
        }
        if ($cityName) {
            $parts[] = $cityName;
        }

        return !empty($parts) ? implode(', ', $parts) : 'Wilayah Tidak Tercatat';
    }

    /**
     * Safely extract city name whether model object or string.
     */
    protected function extractCityName(mixed $cityEntityOrString): ?string
    {
        if (empty($cityEntityOrString)) {
            return null;
        }
        if (is_string($cityEntityOrString)) {
            return $cityEntityOrString;
        }
        if (is_object($cityEntityOrString) && isset($cityEntityOrString->name)) {
            return (string) $cityEntityOrString->name;
        }
        return null;
    }

    /**
     * Translate system status string into user-friendly Indonesian text.
     */
    protected function formatStatusLabel(string $status, ?string $decision = null): string
    {
        if ($decision) {
            return match ($decision) {
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                default    => ucfirst($decision),
            };
        }

        return match ($status) {
            'pending', 'waiting_approval', 'menunggu_mitra' => 'Menunggu',
            'taken', 'in_progress', 'partner_on_the_way', 'partner_arrived' => 'Sedang Berjalan',
            'waiting_customer_confirmation' => 'Menunggu Konfirmasi',
            'selesai', 'approved', 'resolved' => 'Selesai',
            'dibatalkan', 'rejected' => 'Dibatalkan',
            'partner_cancel_requested', 'customer_cancel_requested' => 'Menunggu Review Admin',
            'disputed_freeze' => 'Sengketa Escrow',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    /**
     * Determine if admin is super admin.
     */
    protected function isSuperAdmin(User $admin): bool
    {
        return method_exists($admin, 'isSuperAdmin')
            ? $admin->isSuperAdmin()
            : in_array($admin->role, ['super_admin', 'superadmin'], true);
    }

    /**
     * Safely resolve a named route or return null if route not defined.
     */
    protected function safeRoute(string $name, array $params = []): ?string
    {
        return \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : null;
    }
}
