<?php

namespace App\Livewire\SuperAdmin\AccountDeletions;

use App\Models\AccountDeletionRequest;
use App\Models\Help;
use App\Models\User;
use App\Models\UserBalance;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.superadmin')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'pending'; // 'pending', 'all', 'approved', 'rejected', 'cancelled'
    public $roleFilter = '';
    public $perPage = 10;

    // Modal state
    public $showReviewModal = false;
    public $selectedRequest = null;
    public $targetUser = null;
    public $liveBalance = 0;
    public $liveActiveTasks = 0;
    public $adminNotes = '';

    public $showConfirmApproveModal = false;
    public $showConfirmRejectModal = false;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function setStatusFilter($status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function reviewRequest($id)
    {
        $request = AccountDeletionRequest::with(['user', 'processor'])->findOrFail($id);
        $this->selectedRequest = $request;
        $this->adminNotes = $request->admin_notes ?? '';

        $user = $request->user;
        $this->targetUser = $user;

        if ($user) {
            $userBal = UserBalance::where('user_id', $user->id)->first();
            $this->liveBalance = $userBal ? (float) $userBal->balance : 0.0;

            $this->liveActiveTasks = $user->isMitra()
                ? Help::where('mitra_id', $user->id)->active()->count()
                : Help::where('user_id', $user->id)->active()->count();
        } else {
            $this->liveBalance = (float) $request->balance_at_request;
            $this->liveActiveTasks = 0;
        }

        $this->showReviewModal = true;
    }

    public function confirmApprove()
    {
        $this->showConfirmApproveModal = true;
    }

    public function cancelConfirmApprove()
    {
        $this->showConfirmApproveModal = false;
    }

    public function confirmReject()
    {
        $this->showConfirmRejectModal = true;
    }

    public function cancelConfirmReject()
    {
        $this->showConfirmRejectModal = false;
    }

    public function approveRequest()
    {
        if (!$this->selectedRequest) {
            return;
        }

        $requestId = $this->selectedRequest->id;
        $delRequest = AccountDeletionRequest::findOrFail($requestId);

        if ($delRequest->status !== 'pending') {
            session()->flash('error', 'Permintaan ini sudah diproses sebelumnya.');
            $this->closeModal();
            return;
        }

        DB::transaction(function () use ($delRequest) {
            $delRequest->update([
                'status'       => 'approved',
                'admin_notes'  => $this->adminNotes ?: 'Disetujui dan dihapus oleh Superadmin.',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            $user = User::find($delRequest->user_id);
            if ($user) {
                // Hapus user (nullOnDelete pada FK menjaga ledger keuangan dan log historis)
                $user->delete();
                Log::info("[SuperAdmin] Approved account deletion request #{$delRequest->id}. User #{$user->id} ({$user->name}) deleted.");
            }
        });

        session()->flash('message', "Permintaan penghapusan akun #{$delRequest->id} berhasil disetujui dan akun pengguna telah dihapus.");
        $this->closeModal();
    }

    public function rejectRequest()
    {
        if (!$this->selectedRequest) {
            return;
        }

        $requestId = $this->selectedRequest->id;
        $delRequest = AccountDeletionRequest::findOrFail($requestId);

        if ($delRequest->status !== 'pending') {
            session()->flash('error', 'Permintaan ini sudah diproses sebelumnya.');
            $this->closeModal();
            return;
        }

        $delRequest->update([
            'status'       => 'rejected',
            'admin_notes'  => $this->adminNotes ?: 'Permintaan ditolak oleh Superadmin.',
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        // Kirim notifikasi in-app ke pengguna
        $user = User::find($delRequest->user_id);
        if ($user) {
            try {
                $user->notify(new \App\Notifications\AccountDeletionStatusNotification($delRequest));
            } catch (\Throwable $e) {
                Log::error("[SuperAdmin] Failed to send deletion rejection notification to user #{$user->id}: " . $e->getMessage());
            }
        }

        Log::info("[SuperAdmin] Rejected account deletion request #{$delRequest->id} by Superadmin #" . auth()->id());

        session()->flash('message', "Permintaan penghapusan akun #{$delRequest->id} telah ditolak dan pemberitahuan telah dikirimkan ke pengguna.");
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showReviewModal = false;
        $this->showConfirmApproveModal = false;
        $this->showConfirmRejectModal = false;
        $this->selectedRequest = null;
        $this->targetUser = null;
        $this->adminNotes = '';
    }

    public function render()
    {
        $query = AccountDeletionRequest::with(['user', 'processor'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('user_name', 'like', '%' . $this->search . '%')
                        ->orWhere('user_email', 'like', '%' . $this->search . '%')
                        ->orWhere('user_phone', 'like', '%' . $this->search . '%')
                        ->orWhere('reason', 'like', '%' . $this->search . '%')
                        ->orWhere('city_name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter && $this->statusFilter !== 'all', function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->when($this->roleFilter, function ($q) {
                $q->where('role', $this->roleFilter);
            })
            ->latest();

        $requests = $query->paginate($this->perPage);

        // Counts for tab badges
        $counts = [
            'pending'   => AccountDeletionRequest::where('status', 'pending')->count(),
            'approved'  => AccountDeletionRequest::where('status', 'approved')->count(),
            'rejected'  => AccountDeletionRequest::where('status', 'rejected')->count(),
            'cancelled' => AccountDeletionRequest::where('status', 'cancelled')->count(),
            'all'       => AccountDeletionRequest::count(),
        ];

        return view('livewire.superadmin.account-deletions.index', [
            'requests' => $requests,
            'counts'   => $counts,
        ]);
    }
}
