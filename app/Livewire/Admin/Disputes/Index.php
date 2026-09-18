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

    public $activeTab = 'cancellations'; // Default to cancellations
    public $status = 'pending'; // 'frozen', 'resolved', 'all' for disputes | 'pending', 'approved', 'rejected', 'all' for cancellations
    public $search = '';
    public $requesterTypeFilter = 'all'; // 'all', 'partner', 'customer'

    protected $listeners = [
        'superadmin-territory-changed' => '$refresh',
        'admin-district-changed'       => '$refresh',
        'admin-city-changed'           => '$refresh',
    ];

    public function mount()
    {
        if (request()->routeIs('*disputes*') || request('tab') === 'disputes') {
            $this->activeTab = 'disputes';
            $this->status = request('status', 'frozen');
            if (!in_array($this->status, ['frozen', 'resolved', 'all'], true)) {
                $this->status = 'frozen';
            }
        } else {
            $this->activeTab = 'cancellations';
            $this->status = request('status', 'pending');
            if (!in_array($this->status, ['pending', 'approved', 'rejected', 'all'], true)) {
                $this->status = 'pending';
            }
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
    public $cancelLogs                = [];
    public $expandedHelpIds           = [];
    public $cancelDecision           = 'approved'; // 'approved' | 'rejected'
    public $settlementType           = 'full_refund'; // 'full_refund' | 'item_settled' | 'partial_settlement'
    public $cancelRefundAmount       = 0;
    public $cancelPartnerAmount      = 0;
    public $cancelAdminNotes         = '';

    // SP Controls for Admin
    public $spTarget         = 'none'; // 'none', 'partner', 'customer', 'both'
    public $partnerSpLevel   = 1;
    public $partnerSpReason  = '';
    public $customerSpLevel  = 1;
    public $customerSpReason = '';

    // In-Modal Chat / Klarifikasi Lapangan Khusus Pembatalan (Tugas Bantuan)
    public $showChatPanel     = false;
    public $chatTarget        = 'customer'; // 'customer', 'mitra', 'both'
    public $adminChatMessage  = '';
    public $chatMessages      = [];

    protected $queryString = [
        'activeTab'           => ['except' => 'cancellations'],
        'status'              => ['except' => ''],
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

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = ($tab === 'disputes') ? 'disputes' : 'cancellations';
        $this->status = ($this->activeTab === 'disputes') ? 'frozen' : 'pending';
        $this->resetPage();
    }

    public function updatedActiveTab($value): void
    {
        $this->status = ($value === 'disputes') ? 'frozen' : 'pending';
        $this->resetPage();
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

    public function getWaLink(?string $phone, string $text = ''): ?string
    {
        if (!$phone) {
            return null;
        }
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }
        return 'https://wa.me/' . $clean . ($text ? '?text=' . urlencode($text) : '');
    }

    public function openPlatformChat(string $target = 'customer', ?int $helpId = null)
    {
        $this->chatTarget = in_array($target, ['customer', 'mitra', 'both']) ? $target : 'customer';
        $this->showChatPanel = true;
        $this->loadChatHistory($helpId);
    }

    public function closeChatPanel()
    {
        $this->showChatPanel = false;
        $this->adminChatMessage = '';
    }

    public function setChatTarget(string $target)
    {
        $this->chatTarget = in_array($target, ['customer', 'mitra', 'both']) ? $target : 'customer';
    }

    public function loadChatHistory(?int $helpId = null)
    {
        $targetHelpId = $helpId ?? $this->selectedCancelRequest?->help_id ?? $this->selectedHelpId ?? $this->selectedHelp?->id;
        if (!$targetHelpId) {
            $this->chatMessages = [];
            return;
        }

        $this->chatMessages = \App\Models\Chat::where('help_id', $targetHelpId)
            ->with(['mitra', 'customer'])
            ->latest('created_at')
            ->take(30)
            ->get()
            ->reverse()
            ->values()
            ->toArray();
    }

    public function sendAdminChatMessage()
    {
        $targetHelpId = $this->selectedCancelRequest?->help_id ?? $this->selectedHelpId ?? $this->selectedHelp?->id;
        if (!$targetHelpId || empty(trim($this->adminChatMessage))) {
            return;
        }

        $help = Help::with(['user', 'mitra'])->find($targetHelpId);
        if (!$help) {
            return;
        }

        $this->validate([
            'adminChatMessage' => 'required|string|max:1500',
        ]);

        $customer = $help->user ?? $this->selectedCancelRequest?->customer;
        $partner = $help->mitra ?? $this->selectedCancelRequest?->partner;

        $targetLabel = $this->chatTarget === 'customer' ? 'Customer' : ($this->chatTarget === 'mitra' ? 'Mitra' : 'Kedua Pihak');
        $prefix = "🛡️ [Pesan Resmi Admin - Ditujukan ke {$targetLabel}]:\n";
        $formattedMessage = $prefix . trim($this->adminChatMessage);

        \App\Models\Chat::create([
            'help_id'     => $help->id,
            'customer_id' => $customer?->id ?? $help->user_id,
            'mitra_id'    => $partner?->id ?? $help->mitra_id,
            'sender_id'   => auth()->id(),
            'sender_type' => \App\Enums\ChatSenderType::ADMIN->value,
            'message'     => $formattedMessage,
            'is_read'     => false,
        ]);


        // Notifikasi ke pihak yang dituju
        try {
            if ($this->chatTarget === 'customer' || $this->chatTarget === 'both') {
                $customer?->notify(new \App\Notifications\HelpStatusNotification(
                    $help,
                    $help->status,
                    'admin_clarification',
                    "Pesan dari Admin: \"" . \Illuminate\Support\Str::limit($this->adminChatMessage, 80) . "\""
                ));
            }
            if ($this->chatTarget === 'mitra' || $this->chatTarget === 'both') {
                $partner?->notify(new \App\Notifications\HelpStatusNotification(
                    $help,
                    $help->status,
                    'admin_clarification',
                    "Pesan dari Admin: \"" . \Illuminate\Support\Str::limit($this->adminChatMessage, 80) . "\""
                ));
            }
        } catch (\Throwable $e) {
            Log::warning('[AdminDisputes] Gagal mengirim notifikasi chat klarifikasi: ' . $e->getMessage());
        }

        $this->adminChatMessage = '';
        $this->loadChatHistory($targetHelpId);
        session()->flash('chat_sent_success', "Pesan resmi klarifikasi berhasil dikirim ke {$targetLabel}.");
    }

    public function updatedSettlementType($val)
    {
        if (!$this->selectedCancelRequest || !$this->selectedCancelRequest->help) {
            return;
        }

        $help = $this->selectedCancelRequest->help;
        $gross = (float) ($help->total_amount > 0 ? $help->total_amount : $help->amount);

        if ($val === 'relist_pool') {
            $this->cancelRefundAmount = 0;
            $this->cancelPartnerAmount = 0;
        } elseif ($val === 'full_refund') {
            $this->cancelRefundAmount = $gross;
            $this->cancelPartnerAmount = 0;
        } elseif ($val === 'item_settled') {
            $pAmt = (float) ($this->selectedCancelRequest->item_purchase_amount ?: 0);
            $this->cancelPartnerAmount = $pAmt;
            $this->cancelRefundAmount = max(0, $gross - $pAmt);
        } elseif ($val === 'partial_settlement') {
            $pAmt = round($gross * 0.5);
            $this->cancelPartnerAmount = $pAmt;
            $this->cancelRefundAmount = max(0, $gross - $pAmt);
        }
    }

    public function updatedCancelRefundAmount($val)
    {
        if (!$this->selectedCancelRequest || !$this->selectedCancelRequest->help) {
            return;
        }
        $gross = (float) ($this->selectedCancelRequest->help->total_amount > 0 ? $this->selectedCancelRequest->help->total_amount : $this->selectedCancelRequest->help->amount);
        $refund = (float) ($val ?: 0);
        $this->cancelPartnerAmount = max(0, round($gross - $refund));
    }

    public function updatedCancelPartnerAmount($val)
    {
        if (!$this->selectedCancelRequest || !$this->selectedCancelRequest->help) {
            return;
        }
        $gross = (float) ($this->selectedCancelRequest->help->total_amount > 0 ? $this->selectedCancelRequest->help->total_amount : $this->selectedCancelRequest->help->amount);
        $payout = (float) ($val ?: 0);
        $this->cancelRefundAmount = max(0, round($gross - $payout));
    }

    public function toggleHelpLogs(int $helpId)
    {
        if (in_array($helpId, $this->expandedHelpIds, true)) {
            $this->expandedHelpIds = array_values(array_diff($this->expandedHelpIds, [$helpId]));
        } else {
            $this->expandedHelpIds[] = $helpId;
        }
    }

    public function openCancelReviewModal(int $cancelRequestId)
    {
        $this->selectedCancelRequestId = $cancelRequestId;
        $this->selectedCancelRequest   = HelpCancelRequest::with(['help.user', 'help.mitra', 'requestedBy', 'district'])->findOrFail($cancelRequestId);

        // Muat seluruh log pembatalan untuk ID pekerjaan yang sama
        $this->cancelLogs = HelpCancelRequest::with(['requestedBy', 'partner', 'customer', 'reviewedBy'])
            ->where('help_id', $this->selectedCancelRequest->help_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $effectiveDistricts = $admin->getEffectiveAdminDistrictIds();
            $targetDistrictId = $this->selectedCancelRequest->district_id ?? $this->selectedCancelRequest->help?->district_id;
            if ($targetDistrictId && !in_array((int)$targetDistrictId, $effectiveDistricts, true)) {
                session()->flash('error', 'Anda tidak memiliki wewenang untuk meninjau pembatalan di luar wilayah kecamatan Anda.');
                $this->selectedCancelRequest = null;
                $this->selectedCancelRequestId = null;
                $this->cancelLogs = [];
                return;
            }
        }

        $help = $this->selectedCancelRequest->help;
        $gross = (float) ($help->total_amount > 0 ? $help->total_amount : $help->amount);
        $isPartner = ($this->selectedCancelRequest->requester_type === 'partner');
        $actionType = $this->selectedCancelRequest->action_type;
        $partnerResponse = $this->selectedCancelRequest->partner_response_type;
        $cancellationStage = $this->selectedCancelRequest->cancellation_stage;

        $this->cancelDecision = 'approved';

        $isKonsep2PartnerCancel = ($isPartner && ($cancellationStage === 'in_progress' || $this->selectedCancelRequest->previous_status === 'in_progress' || $help?->status === Help::STATUS_PARTNER_CANCEL_REQUESTED));

        if ($isKonsep2PartnerCancel) {
            // Konsep 2: Mitra membatalkan saat pengerjaan telah dimulai -> Default Full Refund / Penyesuaian Saldo Admin & Customer
            $this->settlementType      = 'full_refund';
            $this->cancelRefundAmount  = $gross;
            $this->cancelPartnerAmount = 0;
        } elseif ($actionType === HelpCancelRequest::ACTION_SWITCH_PARTNER || ($isPartner && $actionType === HelpCancelRequest::ACTION_PARTNER_INCIDENT)) {
            // Konsep 1: Transit / Kendala perjalanan / Ganti Mitra -> Relist Pool
            $this->settlementType      = 'relist_pool';
            $this->cancelRefundAmount  = 0;
            $this->cancelPartnerAmount = 0;
        } elseif ($actionType === HelpCancelRequest::ACTION_CUSTOMER_WITHDRAW && $partnerResponse === HelpCancelRequest::PARTNER_RESPONSE_CONFIRMED) {
            $this->settlementType      = 'full_refund';
            $this->cancelRefundAmount  = $gross;
            $this->cancelPartnerAmount = 0;
        } elseif ($actionType === HelpCancelRequest::ACTION_CUSTOMER_WITHDRAW && $partnerResponse === HelpCancelRequest::PARTNER_RESPONSE_REJECTED) {
            $this->settlementType      = 'partial_settlement';
            $dist = (float) ($this->selectedCancelRequest->partner_moved_km ?? 0);
            $pAmt = min(round($gross * 0.5), round($gross * min(1.0, max(0.2, $dist / 5.0))));
            $this->cancelPartnerAmount = max(5000, $pAmt);
            $this->cancelRefundAmount  = max(0, $gross - $this->cancelPartnerAmount);
        } else {
            $this->settlementType      = $this->selectedCancelRequest->item_purchased ? 'item_settled' : 'full_refund';
            $this->cancelRefundAmount  = $gross;
            $this->cancelPartnerAmount = $this->selectedCancelRequest->item_purchase_amount ?: 0;
        }
        $this->cancelAdminNotes = '';

        // Reset SP Controls
        $this->spTarget         = 'none';
        $this->partnerSpLevel   = 1;
        $this->partnerSpReason  = '';
        $this->customerSpLevel  = 1;
        $this->customerSpReason = '';

        $this->showCancelReviewModal = true;
    }

    public function closeCancelReviewModal()
    {
        $this->showCancelReviewModal = false;
        $this->cancelLogs = [];
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
            'showChatPanel',
            'adminChatMessage',
            'chatMessages',
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

    public function unlinkPartnerAndHoldTask()
    {
        if (!$this->selectedCancelRequest) {
            return;
        }

        try {
            app(HelpCancellationService::class)->adminUnlinkPartnerAndHoldTask(
                $this->selectedCancelRequest,
                auth()->user(),
                $this->cancelAdminNotes ?: 'Mitra dipisahkan & dibebaskan oleh Admin karena customer belum mengonfirmasi.'
            );

            session()->flash('message', "Mitra berhasil dipisahkan dan dibebaskan dari tugas #{$this->selectedCancelRequest->help_id}. Tugas ditahan (tidak tampil di pool) sampai Customer mengonfirmasi.");
            $this->closeCancelReviewModal();
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[AdminDisputes] unlinkPartnerAndHoldTask error: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat memisahkan mitra dari tugas: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Otomatis cek dan batalkan permintaan yang batas waktunya telah habis
        app(HelpCancellationService::class)->checkAndAutoCancelExpiredRequests();

        $admin        = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        // Hitung count untuk indikator badge tab secara dinamis
        $pendingCancelsCount = 0;
        $activeFrozenDisputesCount = 0;

        if (!$isSuperAdmin) {
            $districtIds = $admin ? $admin->getEffectiveAdminDistrictIds() : [];
            if (!empty($districtIds)) {
                $pendingCancelsCount = HelpCancelRequest::where('status', HelpCancelRequest::STATUS_PENDING)
                    ->where(function ($q) use ($districtIds) {
                        $q->whereIn('district_id', $districtIds)
                          ->orWhereHas('help', fn($hq) => $hq->whereIn('district_id', $districtIds));
                    })->count();

                $activeFrozenDisputesCount = Help::where('escrow_status', Help::ESCROW_STATUS_DISPUTED_FREEZE)
                    ->where(function ($q) use ($districtIds) {
                        $q->whereIn('district_id', $districtIds)
                          ->orWhereHas('user', fn($uq) => $uq->whereIn('district_id', $districtIds));
                    })->count();
            }
        } else {
            $territory = $admin ? $admin->getActiveSuperadminTerritory() : ['type' => 'all', 'id' => null];
            $cancelCountQuery = HelpCancelRequest::where('status', HelpCancelRequest::STATUS_PENDING);
            $disputeCountQuery = Help::where('escrow_status', Help::ESCROW_STATUS_DISPUTED_FREEZE);

            if ($territory['type'] === 'district' && $territory['id']) {
                $dId = (int) $territory['id'];
                $cancelCountQuery->where(function ($q) use ($dId) {
                    $q->where('district_id', $dId)
                      ->orWhereHas('help', fn($hq) => $hq->where('district_id', $dId));
                });
                $disputeCountQuery->where(function ($q) use ($dId) {
                    $q->where('district_id', $dId)
                      ->orWhereHas('user', fn($uq) => $uq->where('district_id', $dId));
                });
            } elseif ($territory['type'] === 'city' && $territory['id']) {
                $cId = (int) $territory['id'];
                $districtIds = $admin ? $admin->getEffectiveSuperadminDistrictIds() : [];
                $cancelCountQuery->where(function ($q) use ($cId, $districtIds) {
                    $q->whereHas('help', fn($hq) => $hq->where('city_id', $cId));
                    if (!empty($districtIds)) {
                        $q->orWhereIn('district_id', $districtIds)
                          ->orWhereHas('help', fn($hq) => $hq->whereIn('district_id', $districtIds));
                    }
                });
                $disputeCountQuery->where(function ($q) use ($cId, $districtIds) {
                    $q->where('city_id', $cId)
                      ->orWhereHas('user', fn($uq) => $uq->where('city_id', $cId));
                    if (!empty($districtIds)) {
                        $q->orWhereIn('district_id', $districtIds)
                          ->orWhereHas('user', fn($uq) => $uq->whereIn('district_id', $districtIds));
                    }
                });
            }
            $pendingCancelsCount = $cancelCountQuery->count();
            $activeFrozenDisputesCount = $disputeCountQuery->count();
        }

        if ($this->activeTab === 'cancellations') {
            if (!in_array($this->status, ['pending', 'approved', 'rejected', 'all'], true)) {
                $this->status = 'pending';
            }

            $query = HelpCancelRequest::with([
                'help.user', 
                'help.mitra', 
                'help.cancelRequests.requestedBy', 
                'help.cancelRequests.partner', 
                'help.cancelRequests.customer', 
                'help.cancelRequests.reviewedBy', 
                'requestedBy', 
                'district', 
                'reviewedBy'
            ]);

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

            $routePrefix = $isSuperAdmin ? 'superadmin.' : 'admin.';

            return view('livewire.admin.disputes.index', [
                'cancellations'             => $cancellations,
                'disputes'                  => collect(),
                'isSuperAdmin'              => $isSuperAdmin,
                'routePrefix'               => $routePrefix,
                'pendingCancelsCount'       => $pendingCancelsCount,
                'activeFrozenDisputesCount' => $activeFrozenDisputesCount,
            ])->layout($layout);
        }

        // Tab Disputes
        if (!in_array($this->status, ['frozen', 'resolved', 'all'], true)) {
            $this->status = 'frozen';
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
        $routePrefix = $isSuperAdmin ? 'superadmin.' : 'admin.';

        return view('livewire.admin.disputes.index', [
            'disputes'                  => $disputes,
            'cancellations'             => collect(),
            'isSuperAdmin'              => $isSuperAdmin,
            'routePrefix'               => $routePrefix,
            'pendingCancelsCount'       => $pendingCancelsCount,
            'activeFrozenDisputesCount' => $activeFrozenDisputesCount,
        ])->layout($layout);
    }
}
