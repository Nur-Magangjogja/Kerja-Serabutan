<?php

namespace App\Livewire\Admin\Partners\Reports;

use App\Models\PartnerReport;
use App\Services\HelpTransactionService;
use Livewire\Component;

class Show extends Component
{
    public PartnerReport $report;

    public $status;
    public $adminNotes = '';
    public $newNote = '';

    // Refund Modal
    public $showRefundModal = false;
    public $refundAdminNotes = '';

    // Reject Modal
    public $showRejectModal = false;
    public $rejectReason = '';

    public function mount(PartnerReport $report)
    {
        $this->report = $report->load([
            'reporter',
            'reportedUser',
            'reportedHelp.user',
            'reportedHelp.mitra',
            'reportedHelp.city',
            'resolvedBy',
            'refundProcessedBy',
            'messages.sender'
        ]);

        $this->status = $report->status;
        $this->adminNotes = $report->admin_notes ?? '';
    }

    public function updateStatus($newStatus)
    {
        $this->report->update([
            'status' => $newStatus,
            'resolved_at' => $newStatus === 'resolved' ? now() : $this->report->resolved_at,
            'resolved_by' => $newStatus === 'resolved' ? auth()->id() : $this->report->resolved_by,
        ]);

        $this->status = $newStatus;
        session()->flash('success', "Status laporan berhasil diperbarui menjadi {$newStatus}.");
    }

    public function saveAdminNote()
    {
        if (empty(trim($this->newNote))) {
            return;
        }

        $timestamp = now()->format('d M Y H:i');
        $adminName = auth()->user()->name;
        $entry = "[{$timestamp} oleh {$adminName}]: " . trim($this->newNote);

        $existing = $this->report->admin_notes ? $this->report->admin_notes . "\n" : '';
        $updatedNotes = $existing . $entry;

        $this->report->update([
            'admin_notes' => $updatedNotes,
        ]);

        $this->adminNotes = $updatedNotes;
        $this->newNote = '';
        session()->flash('success', 'Catatan admin berhasil ditambahkan.');
    }

    public function openRefundModal()
    {
        $this->refundAdminNotes = '';
        $this->showRefundModal = true;
    }

    public function closeRefundModal()
    {
        $this->showRefundModal = false;
    }

    public function processRefund(HelpTransactionService $service)
    {
        try {
            $service->processReportRefund($this->report, auth()->user(), $this->refundAdminNotes);
            $this->showRefundModal = false;
            $this->report->refresh();
            session()->flash('success', 'Dana refund berhasil dikembalikan ke saldo dompet customer 100%.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memproses refund: ' . $e->getMessage());
        }
    }

    public function openRejectModal()
    {
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal()
    {
        $this->showRejectModal = false;
    }

    public function submitRejectRefund(HelpTransactionService $service)
    {
        $this->validate([
            'rejectReason' => 'required|string|min:5',
        ], [
            'rejectReason.required' => 'Alasan penolakan refund wajib diisi.',
            'rejectReason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        try {
            $service->rejectReportRefund($this->report, auth()->user(), $this->rejectReason);
            $this->showRejectModal = false;
            $this->report->refresh();
            session()->flash('success', 'Permintaan refund telah ditolak dengan alasan resmi.');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menolak refund: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $help = $this->report->reportedHelp;
        $transactions = collect();
        if ($help) {
            $transactions = \App\Models\BalanceTransaction::where(function ($q) use ($help) {
                $q->where('order_id', $help->order_id)
                  ->orWhere('reference_id', $help->id);
            })->latest()->get();
        }

        $isSuperAdmin = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']);
        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.admin.partners.reports.show', [
            'help' => $help,
            'transactions' => $transactions,
            'isSuperAdmin' => $isSuperAdmin,
        ])->layout($layout);
    }
}
