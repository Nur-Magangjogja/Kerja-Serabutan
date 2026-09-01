<?php

namespace App\Livewire\Admin\Disputes;

use App\Models\City;
use App\Models\Help;
use App\Models\PartnerReport;
use App\Services\HelpTransactionService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    public $status = 'frozen'; // 'frozen', 'resolved', 'all'
    public $search = '';

    // Modal state
    public $showResolveModal = false;
    public $selectedHelpId   = null;
    public $selectedHelp     = null;
    public $resolutionType   = 'full_release'; // 'full_release' | 'full_refund' | 'partial_split'
    public $partnerAmount    = 0;
    public $platformFee      = 0;
    public $customerRefund   = 0;
    public $adminNotes       = '';

    protected $queryString = [
        'status' => ['except' => 'frozen'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function openResolveModal(int $helpId)
    {
        $this->selectedHelpId = $helpId;
        $this->selectedHelp   = Help::with(['user', 'mitra', 'city'])->findOrFail($helpId);

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

    public function render()
    {
        $admin        = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        $query = Help::with(['user', 'mitra', 'city', 'disputeResolvedBy'])
            ->where(function ($q) {
                $q->where('escrow_status', Help::ESCROW_STATUS_DISPUTED_FREEZE)
                  ->orWhereNotNull('disputed_at');
            });

        // City scoping for Regional Admins
        if (!$isSuperAdmin) {
            $cityIds = $admin ? $admin->getAdminCityIds() : [];

            if (!empty($cityIds)) {
                $query->whereIn('city_id', $cityIds);
            } else {
                $query->whereRaw('1 = 0');
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

        return view('livewire.admin.disputes.index', [
            'disputes' => $disputes,
        ]);
    }
}
