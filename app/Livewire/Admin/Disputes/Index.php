<?php

namespace App\Livewire\Admin\Disputes;

use App\Models\City;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\PartnerReport;
use App\Services\HelpCancellationService;
use App\Services\HelpTransactionService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $activeTab = 'cancellations'; // Default to cancellations since cancellations are frequent
    public $status = 'pending'; // 'frozen', 'resolved', 'all' (or 'pending', 'approved', 'rejected' for cancellations)
    public $search = '';
    public $requesterTypeFilter = 'all'; // 'all', 'partner', 'customer'

    protected $listeners = [
        'superadmin-territory-changed' => '$refresh',
        'admin-district-changed'       => '$refresh',
        'admin-city-changed'           => '$refresh',
    ];

    public function mount()
    {
        if (request()->routeIs('*disputes*') && !request()->has('tab')) {
            $this->activeTab = 'disputes';
            $this->status = 'frozen';
        } elseif (request('tab') === 'disputes') {
            $this->activeTab = 'disputes';
            $this->status = 'frozen';
        } else {
            $this->activeTab = 'cancellations';
            $this->status = 'pending';
        }
    }

    // Modal state (Disputes)
    public $showResolveModal = false;
    public $selectedHelpId   = null;
    public $selectedHelp     = null;
    public $resolutionType   = 'full_release'; // 'full_release' | 'full_refund' | 'partial_split'
    public $partnerAmount    = 0;
    public $platformFee      = 0;
    public $customerRefund   = 0;
    public $adminNotes       = '';

    // Modal state (Cancellation Requests)
    public $showCancelReviewModal     = false;
    public $selectedCancelRequestId   = null;
    public $selectedCancelRequest     = null;
    public $cancelDecision           = 'approved'; // 'approved' | 'rejected'
    public $settlementType           = 'full_refund'; // 'full_refund' | 'item_settled' | 'partial_settlement'
    public $cancelRefundAmount       = 0;
    public $cancelPartnerAmount      = 0;
    public $cancelAdminNotes         = '';

    // SP Controls for Admin
    public $spTarget         = 'none'; // 'none', 'partner', 'customer', 'both'
    public $partnerSpLevel   = 1;
    public $partnerSpReason  = 'Pelanggaran pembatalan tugas bantuan';
    public $customerSpLevel  = 1;
    public $customerSpReason = 'Pelanggaran / kejanggalan pesanan bantuan';

    protected $queryString = [
        'activeTab'           => ['except' => 'disputes'],
        'status'              => ['except' => 'frozen'],
        'requesterTypeFilter' => ['except' => 'all'],
        'search'              => ['except' => ''],
    ];


    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingRequesterTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingActiveTab()
    {
        $this->resetPage();
        $this->status = ($this->activeTab === 'cancellations') ? 'pending' : 'frozen';
    }

    // ─── DISPUTE ACTIONS ─────────────────────────────────────────────────────

    public function openResolveModal(int $helpId)
    {
        $this->selectedHelpId = $helpId;
        $this->selectedHelp   = Help::with(['user.district', 'mitra.district', 'district', 'city'])->findOrFail($helpId);

        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $effectiveDistricts = $admin->getEffectiveAdminDistrictIds();
            $targetDistrictId = $this->selectedHelp->district_id ?? $this->selectedHelp->user?->district_id;
            if ($targetDistrictId && !in_array((int)$targetDistrictId, $effectiveDistricts, true)) {
                session()->flash('error', 'Anda tidak memiliki wewenang untuk menyelesaikan sengketa di luar wilayah kecamatan Anda.');
                $this->selectedHelp = null;
                $this->selectedHelpId = null;
                return;
            }
        }

        $gross = (float) ($this->selectedHelp->total_amount > 0 ? $this->selectedHelp->total_amount : $this->selectedHelp->amount);
        $fee   = (float) $this->selectedHelp->getPlatformFee();
        $net   = max(0, $gross - $fee);

        $this->resolutionType = 'full_release';
        $this->partnerAmount  = $net;
        $this->platformFee    = $fee;
        $this->customerRefund = 0;
        $this->adminNotes     = '';
        $this->showResolveModal = true;
    }

    public function closeResolveModal()
    {
        $this->showResolveModal = false;
        $this->reset(['selectedHelpId', 'selectedHelp', 'partnerAmount', 'platformFee', 'customerRefund', 'adminNotes']);
    }

    public function updatedResolutionType($val)
    {
        if (!$this->selectedHelp) {
            return;
        }

        $gross = (float) ($this->selectedHelp->total_amount > 0 ? $this->selectedHelp->total_amount : $this->selectedHelp->amount);
        $fee   = (float) $this->selectedHelp->getPlatformFee();
        $net   = max(0, $gross - $fee);

        if ($val === 'full_release') {
            $this->partnerAmount  = $net;
            $this->platformFee    = $fee;
            $this->customerRefund = 0;
        } elseif ($val === 'full_refund') {
            $this->partnerAmount  = 0;
            $this->platformFee    = 0;
            $this->customerRefund = $gross;
        } elseif ($val === 'partial_split') {
            $this->partnerAmount  = round($net * 0.5);
            $this->platformFee    = $fee;
            $this->customerRefund = $gross - ($this->partnerAmount + $this->platformFee);
        }
    }

    public function executeResolution()
    {
        if (!$this->selectedHelp) {
            return;
        }

        $this->validate([
            'resolutionType' => 'required|in:full_release,full_refund,partial_split',
            'partnerAmount'  => 'required_if:resolutionType,partial_split|numeric|min:0',
            'platformFee'    => 'required_if:resolutionType,partial_split|numeric|min:0',
            'customerRefund' => 'required_if:resolutionType,partial_split|numeric|min:0',
        ]);

        $service = app(HelpTransactionService::class);
        $admin   = auth()->user();
        $gross   = (float) ($this->selectedHelp->total_amount > 0 ? $this->selectedHelp->total_amount : $this->selectedHelp->amount);

        try {
            if ($this->resolutionType === 'partial_split') {
                $pAmt = (float) $this->partnerAmount;
                $fee  = (float) $this->platformFee;
                $ref  = (float) $this->customerRefund;

                if (abs(($pAmt + $fee + $ref) - $gross) > 0.01) {
                    $this->addError('customerRefund', "Total pembagian (Rp " . number_format($pAmt + $fee + $ref, 0) . ") tidak sama dengan nilai bruto transaksi (Rp " . number_format($gross, 0) . ").");
                    return;
                }

                $service->resolveDispute($this->selectedHelp, $admin, 'partial_split', [
                    'partner_amount'  => $pAmt,
                    'platform_fee'    => $fee,
                    'customer_refund' => $ref,
                ]);
            } else {
                $service->resolveDispute($this->selectedHelp, $admin, $this->resolutionType);
            }

            session()->flash('message', "Sengketa bantuan #{$this->selectedHelp->id} berhasil diselesaikan.");
            $this->closeResolveModal();
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[AdminDisputes] executeResolution error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat memproses resolusi sengketa.');
        }
    }

    // ─── CANCELLATION REQUEST ACTIONS ────────────────────────────────────────

    public function openCancelReviewModal(int $cancelRequestId)
    {
        $this->selectedCancelRequestId = $cancelRequestId;
        $this->selectedCancelRequest   = HelpCancelRequest::with(['help.user', 'help.mitra', 'requestedBy', 'district'])->findOrFail($cancelRequestId);

        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $effectiveDistricts = $admin->getEffectiveAdminDistrictIds();
            $targetDistrictId = $this->selectedCancelRequest->district_id ?? $this->selectedCancelRequest->help?->district_id;
            if ($targetDistrictId && !in_array((int)$targetDistrictId, $effectiveDistricts, true)) {
                session()->flash('error', 'Anda tidak memiliki wewenang untuk meninjau pembatalan di luar wilayah kecamatan Anda.');
                $this->selectedCancelRequest = null;
                $this->selectedCancelRequestId = null;
                return;
            }
        }

        $help = $this->selectedCancelRequest->help;
        $gross = (float) ($help->total_amount > 0 ? $help->total_amount : $help->amount);

        $this->cancelDecision     = 'approved';
        $this->settlementType     = $this->selectedCancelRequest->item_purchased ? 'item_settled' : 'full_refund';
        $this->cancelRefundAmount = $gross;
        $this->cancelPartnerAmount= $this->selectedCancelRequest->item_purchase_amount ?: 0;
        $this->cancelAdminNotes   = '';

        // Reset SP Controls
        $this->spTarget         = 'none';
        $this->partnerSpLevel   = 1;
        $this->partnerSpReason  = ($this->selectedCancelRequest->requester_type === 'partner')
            ? 'Mitra terbukti membatalkan tugas secara sepihak tanpa alasan sah'
            : 'Mitra terbukti mangkir / menelantarkan tugas bantuan customer';
        $this->customerSpLevel  = 1;
        $this->customerSpReason = 'Customer terindikasi melakukan pembatalan tidak wajar / order fiktif';

        $this->showCancelReviewModal = true;
    }

    public function closeCancelReviewModal()
    {
        $this->showCancelReviewModal = false;
        $this->reset([
            'selectedCancelRequestId',
            'selectedCancelRequest',
            'cancelDecision',
            'settlementType',
            'cancelRefundAmount',
            'cancelPartnerAmount',
            'cancelAdminNotes',
            'spTarget',
            'partnerSpLevel',
            'partnerSpReason',
            'customerSpLevel',
            'customerSpReason',
        ]);
    }

    public function executeCancelReview()
    {
        if (!$this->selectedCancelRequest) {
            return;
        }

        $this->validate([
            'cancelDecision'     => 'required|in:approved,rejected',
            'settlementType'     => 'required_if:cancelDecision,approved|in:full_refund,item_settled,partial_settlement,relist_pool',
            'cancelRefundAmount' => 'nullable|numeric|min:0',
            'cancelPartnerAmount'=> 'nullable|numeric|min:0',
            'spTarget'           => 'required|in:none,partner,customer,both',
            'partnerSpLevel'     => 'required_if:spTarget,partner,both|integer|min:1|max:3',
            'customerSpLevel'    => 'required_if:spTarget,customer,both|integer|min:1|max:3',
        ]);

        try {
            $isApproved = ($this->cancelDecision === 'approved');
            app(HelpCancellationService::class)->reviewByAdmin(
                $this->selectedCancelRequest,
                auth()->user(),
                $isApproved,
                $this->settlementType,
                [
                    'refund_amount'      => (float) $this->cancelRefundAmount,
                    'partner_amount'     => (float) $this->cancelPartnerAmount,
                    'admin_notes'        => $this->cancelAdminNotes,
                    'sp_target'          => $this->spTarget,
                    'partner_sp_level'   => (int) $this->partnerSpLevel,
                    'partner_sp_reason'  => $this->partnerSpReason,
                    'customer_sp_level'  => (int) $this->customerSpLevel,
                    'customer_sp_reason' => $this->customerSpReason,
                ]
            );

            session()->flash('message', "Permintaan pembatalan #{$this->selectedCancelRequest->id} berhasil diproses.");
            $this->closeCancelReviewModal();
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[AdminDisputes] executeCancelReview error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat memproses permintaan pembatalan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Otomatis cek dan batalkan permintaan yang batas waktunya telah habis
        app(HelpCancellationService::class)->checkAndAutoCancelExpiredRequests();

        $admin        = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        if ($this->activeTab === 'cancellations') {
            $query = HelpCancelRequest::with(['help.user', 'help.mitra', 'requestedBy', 'district', 'reviewedBy']);

            if (!$isSuperAdmin) {
                $districtIds = $admin ? $admin->getEffectiveAdminDistrictIds() : [];
                if (!empty($districtIds)) {
                    $query->where(function ($q) use ($districtIds) {
                        $q->whereIn('district_id', $districtIds)
                          ->orWhereHas('help', fn($hq) => $hq->whereIn('district_id', $districtIds));
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $territory = $admin ? $admin->getActiveSuperadminTerritory() : ['type' => 'all', 'id' => null];
                if ($territory['type'] === 'district' && $territory['id']) {
                    $dId = (int) $territory['id'];
                    $query->where(function ($q) use ($dId) {
                        $q->where('district_id', $dId)
                          ->orWhereHas('help', fn($hq) => $hq->where('district_id', $dId));
                    });
                } elseif ($territory['type'] === 'city' && $territory['id']) {
                    $cId = (int) $territory['id'];
                    $districtIds = $admin ? $admin->getEffectiveSuperadminDistrictIds() : [];
                    $query->where(function ($q) use ($cId, $districtIds) {
                        $q->whereHas('help', fn($hq) => $hq->where('city_id', $cId));
                        if (!empty($districtIds)) {
                            $q->orWhereIn('district_id', $districtIds)
                              ->orWhereHas('help', fn($hq) => $hq->whereIn('district_id', $districtIds));
                        }
                    });
                }
            }

            if ($this->requesterTypeFilter !== 'all') {
                $query->where('requester_type', $this->requesterTypeFilter);
            }

            if ($this->status !== 'all') {
                $query->where('status', $this->status);
            }

            if (!empty($this->search)) {
                $query->where(function ($q) {
                    $q->where('reason', 'like', "%{$this->search}%")
                      ->orWhereHas('help', fn($hq) => $hq->where('title', 'like', "%{$this->search}%")->orWhere('order_id', 'like', "%{$this->search}%"))
                      ->orWhereHas('requestedBy', fn($uq) => $uq->where('name', 'like', "%{$this->search}%"));
                });
            }

            $cancellations = $query->latest()->paginate(10);
            $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

            return view('livewire.admin.disputes.index', [
                'cancellations' => $cancellations,
                'disputes'      => collect(),
                'isSuperAdmin'  => $isSuperAdmin,
            ])->layout($layout);
        }

        $query = Help::with(['user.district', 'mitra.district', 'district', 'city', 'disputeResolvedBy'])
            ->where(function ($q) {
                $q->where('escrow_status', Help::ESCROW_STATUS_DISPUTED_FREEZE)
                  ->orWhereNotNull('disputed_at');
            });

        // District scoping
        if (!$isSuperAdmin) {
            $districtIds = $admin ? $admin->getEffectiveAdminDistrictIds() : [];

            if (!empty($districtIds)) {
                $query->where(function ($q) use ($districtIds) {
                    $q->whereIn('district_id', $districtIds)
                      ->orWhereHas('user', fn($uq) => $uq->whereIn('district_id', $districtIds));
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            $territory = $admin ? $admin->getActiveSuperadminTerritory() : ['type' => 'all', 'id' => null];
            if ($territory['type'] === 'district' && $territory['id']) {
                $dId = (int) $territory['id'];
                $query->where(function ($q) use ($dId) {
                    $q->where('district_id', $dId)
                      ->orWhereHas('user', fn($uq) => $uq->where('district_id', $dId));
                });
            } elseif ($territory['type'] === 'city' && $territory['id']) {
                $cId = (int) $territory['id'];
                $districtIds = $admin ? $admin->getEffectiveSuperadminDistrictIds() : [];
                $query->where(function ($q) use ($cId, $districtIds) {
                    $q->where('city_id', $cId)
                      ->orWhereHas('user', fn($uq) => $uq->where('city_id', $cId));
                    if (!empty($districtIds)) {
                        $q->orWhereIn('district_id', $districtIds)
                          ->orWhereHas('user', fn($uq) => $uq->whereIn('district_id', $districtIds));
                    }
                });
            }
        }

        // Status filter
        if ($this->status === 'frozen') {
            $query->where('escrow_status', Help::ESCROW_STATUS_DISPUTED_FREEZE);
        } elseif ($this->status === 'resolved') {
            $query->whereNotNull('dispute_resolved_at');
        }

        // Search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('id', 'like', "%{$this->search}%")
                  ->orWhere('order_id', 'like', "%{$this->search}%")
                  ->orWhereHas('user', fn($sq) => $sq->where('name', 'like', "%{$this->search}%"))
                  ->orWhereHas('mitra', fn($sq) => $sq->where('name', 'like', "%{$this->search}%"));
            });
        }

        $disputes = $query->latest('disputed_at')->paginate(10);
        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.admin.disputes.index', [
            'disputes'      => $disputes,
            'cancellations' => collect(),
            'isSuperAdmin'  => $isSuperAdmin,
        ])->layout($layout);
    }
}
