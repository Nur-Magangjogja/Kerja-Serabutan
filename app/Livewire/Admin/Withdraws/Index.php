<?php

namespace App\Livewire\Admin\Withdraws;

use App\Models\WithdrawRequest;
use App\Models\BalanceTransaction;
use App\Models\UserBalance;
use App\Models\District;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $status = 'all';
    public $roleFilter = 'all'; // all, mitra, customer
    public $districtFilter = 'all'; // all or district_id
    public $cityFilter = 'all'; // Backward compatibility alias

    // Approval Modal
    public $showApproveModal = false;
    public $selectedWithdrawId = null;
    public $selectedWithdraw = null;
    public $proofPhoto;

    // Reject Modal
    public $showRejectModal = false;
    public $rejectReason = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'roleFilter' => ['except' => 'all'],
    ];

    protected $listeners = [
        'superadmin-territory-changed' => '$refresh',
        'admin-district-changed'       => 'onAdminDistrictChanged',
        'admin-city-changed'           => 'onAdminDistrictChanged',
    ];

    public function mount()
    {
        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $this->districtFilter = $admin->getActiveAdminDistrictFilter();
            $this->cityFilter = $this->districtFilter;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingDistrictFilter()
    {
        $this->resetPage();
    }

    public function updatedDistrictFilter()
    {
        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $admin->setActiveAdminDistrictFilter($this->districtFilter);
            $this->cityFilter = $this->districtFilter;
            $this->dispatch('admin-district-changed', districtId: $this->districtFilter);
        }
        $this->resetPage();
    }

    public function updatedCityFilter()
    {
        $this->districtFilter = $this->cityFilter;
        $this->updatedDistrictFilter();
    }

    public function onAdminDistrictChanged($districtId = null)
    {
        $admin = auth()->user();
        if ($admin && $admin->role === 'admin') {
            $this->districtFilter = $admin->getActiveAdminDistrictFilter();
            $this->cityFilter = $this->districtFilter;
            $this->resetPage();
        }
    }

    public function onAdminCityChanged($cityId = null)
    {
        $this->onAdminDistrictChanged($cityId);
    }

    protected function isAuthorizedForWithdraw(WithdrawRequest $withdraw): bool
    {
        $admin = auth()->user();
        if (!$admin) return false;
        if (in_array($admin->role, ['super_admin', 'superadmin'])) return true;
        if ($admin->role === 'admin') {
            $allowedDistrictIds = $admin->getAdminDistrictIds();
            if (empty($allowedDistrictIds)) {
                return true;
            }
            $userDistrictId = $withdraw->user?->district_id;
            return !empty($userDistrictId) && in_array((int) $userDistrictId, $allowedDistrictIds, true);
        }
        return false;
    }

    public function openApproveModal($id)
    {
        $this->selectedWithdrawId = $id;
        $withdraw = WithdrawRequest::with(['user.balance', 'user.district', 'user.city'])->findOrFail($id);
        
        if (!$this->isAuthorizedForWithdraw($withdraw)) {
            session()->flash('error', 'Anda tidak memiliki wewenang untuk memproses penarikan dana dari luar wilayah wewenang Anda.');
            return;
        }

        $this->selectedWithdraw = $withdraw;
        $this->proofPhoto = null;
        $this->showApproveModal = true;
    }

    public function closeApproveModal()
    {
        $this->showApproveModal = false;
    }

    public function submitApprove()
    {
        $this->validate([
            'proofPhoto' => 'required|image|max:5120',
        ], [
            'proofPhoto.required' => 'Foto bukti transfer wajib diunggah.',
            'proofPhoto.image' => 'File bukti harus berupa gambar.',
        ]);

        try {
            DB::transaction(function () {
                $withdraw = WithdrawRequest::where('id', $this->selectedWithdrawId)->lockForUpdate()->firstOrFail();

                if (!$this->isAuthorizedForWithdraw($withdraw)) {
                    throw new \RuntimeException('Anda tidak memiliki wewenang untuk memproses penarikan dana dari luar wilayah wewenang Anda.');
                }

                if (!in_array($withdraw->status, ['pending', 'waiting_approval'])) {
                    throw new \RuntimeException('Permintaan penarikan ini sudah diproses sebelumnya.');
                }

                $photoPath = $this->proofPhoto->store('withdraws/proofs', 'public');

                $withdraw->update([
                    'status' => 'completed',
                    'proof_of_transfer' => $photoPath,
                    'processed_at' => now(),
                ]);

                // Update corresponding balance transaction if any
                BalanceTransaction::where(function ($q) use ($withdraw) {
                    $q->where('order_id', 'WD-' . $withdraw->id)
                      ->orWhere('reference_id', $withdraw->id);
                })->where('type', 'withdraw')->update([
                    'status' => 'success',
                    'proof_of_payment' => $photoPath,
                    'processed_at' => now(),
                ]);

                // Catat ke log aktivitas sistem
                \App\Models\ActivityLog::record(
                    auth()->user(),
                    'withdraw_approved',
                    "Admin " . (auth()->user()->name ?? 'Admin') . " menyetujui pencairan dana #WD-{$withdraw->id} sebesar Rp " . number_format($withdraw->amount, 0, ',', '.') . " untuk user {$withdraw->user?->name}",
                    ['withdraw_id' => $withdraw->id, 'amount' => $withdraw->amount, 'user_id' => $withdraw->user_id]
                );
            });

            $this->showApproveModal = false;
            session()->flash('success', "Pencairan dana #WD-{$this->selectedWithdrawId} berhasil disetujui & bukti transfer tersimpan.");
        } catch (\Throwable $e) {
            $this->showApproveModal = false;
            session()->flash('error', $e->getMessage());
        }
    }

    public function openRejectModal($id)
    {
        $this->selectedWithdrawId = $id;
        $withdraw = WithdrawRequest::with(['user.balance', 'user.district', 'user.city'])->findOrFail($id);

        if (!$this->isAuthorizedForWithdraw($withdraw)) {
            session()->flash('error', 'Anda tidak memiliki wewenang untuk memproses penarikan dana dari luar wilayah wewenang Anda.');
            return;
        }

        $this->selectedWithdraw = $withdraw;
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function closeRejectModal()
    {
        $this->showRejectModal = false;
    }

    public function submitReject()
    {
        $this->validate([
            'rejectReason' => 'required|string|min:5',
        ], [
            'rejectReason.required' => 'Alasan penolakan pencairan wajib diisi.',
            'rejectReason.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        try {
            DB::transaction(function () {
                $withdraw = WithdrawRequest::where('id', $this->selectedWithdrawId)->lockForUpdate()->firstOrFail();

                if (!$this->isAuthorizedForWithdraw($withdraw)) {
                    throw new \RuntimeException('Anda tidak memiliki wewenang untuk memproses penarikan dana dari luar wilayah wewenang Anda.');
                }

                if (!in_array($withdraw->status, ['pending', 'waiting_approval'])) {
                    throw new \RuntimeException('Permintaan penarikan ini sudah diproses sebelumnya.');
                }

                $user = $withdraw->user;
                $refundAmount = (float) ($withdraw->amount + ($withdraw->admin_fee ?? 0));

                // Refund balance back to user
                if ($user) {
                    $userBalance = UserBalance::where('user_id', $user->id)->lockForUpdate()->firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
                    $userBalance->increment('balance', $refundAmount);
                }

                $withdraw->update([
                    'status' => 'rejected',
                    'description' => $this->rejectReason,
                    'processed_at' => now(),
                ]);

                BalanceTransaction::create([
                    'idempotency_key' => "withdraw:{$withdraw->id}:refund",
                    'user_id' => $withdraw->user_id,
                    'order_id' => 'REFUND-WD-' . $withdraw->id,
                    'reference_id' => $withdraw->id,
                    'type' => 'refund',
                    'amount' => $refundAmount,
                    'total_payment' => $refundAmount,
                    'status' => 'success',
                    'description' => "Pengembalian dana penarikan #WD-{$withdraw->id} yang ditolak: {$this->rejectReason}",
                ]);

                // Catat ke log aktivitas sistem
                \App\Models\ActivityLog::record(
                    auth()->user(),
                    'withdraw_rejected',
                    "Admin " . (auth()->user()->name ?? 'Admin') . " menolak pencairan dana #WD-{$withdraw->id} sebesar Rp " . number_format($refundAmount, 0, ',', '.') . " untuk user {$user?->name}. Alasan: {$this->rejectReason}",
                    ['withdraw_id' => $withdraw->id, 'amount' => $refundAmount, 'user_id' => $withdraw->user_id, 'reason' => $this->rejectReason]
                );
            });

            $this->showRejectModal = false;
            session()->flash('success', "Pencairan dana #WD-{$this->selectedWithdrawId} telah ditolak dan saldo telah dikembalikan ke user.");
        } catch (\Throwable $e) {
            $this->showRejectModal = false;
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $admin = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        // Ambil daftar kecamatan untuk filter
        if ($isSuperAdmin) {
            $districts = District::with('city')->orderBy('name')->get();
        } else {
            $districts = $admin ? $admin->getAdminDistricts() : collect();
        }

        $query = WithdrawRequest::with(['user.district', 'user.city'])->latest();

        if (! $isSuperAdmin) {
            $this->districtFilter = $admin ? $admin->getActiveAdminDistrictFilter() : 'all';
            $this->cityFilter = $this->districtFilter;
            $effectiveDistrictIds = $admin ? $admin->getEffectiveAdminDistrictIds() : [];
            if (!empty($effectiveDistrictIds)) {
                $query->whereHas('user', fn($q) => $q->whereIn('district_id', $effectiveDistrictIds));
            } elseif ($admin && $admin->role === 'admin') {
                $query->whereRaw('1 = 0');
            }
        } else {
            $territory = $admin ? $admin->getActiveSuperadminTerritory() : ['type' => 'all', 'id' => null];
            if ($territory['type'] === 'district' && $territory['id']) {
                $dId = (int) $territory['id'];
                $query->whereHas('user', fn($q) => $q->where('district_id', $dId));
            } elseif ($territory['type'] === 'city' && $territory['id']) {
                $cId = (int) $territory['id'];
                $districtIds = $admin ? $admin->getEffectiveSuperadminDistrictIds() : [];
                $query->whereHas('user', function ($q) use ($cId, $districtIds) {
                    $q->where('city_id', $cId);
                    if (!empty($districtIds)) {
                        $q->orWhereIn('district_id', $districtIds);
                    }
                });
            } elseif ($this->districtFilter !== 'all') {
                $query->whereHas('user', fn($q) => $q->where('district_id', (int) $this->districtFilter));
            }
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->roleFilter !== 'all') {
            $query->whereHas('user', fn($q) => $q->where('role', $this->roleFilter));
        }

        if (!empty($this->search)) {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('bank_code', 'like', "%{$s}%")
                  ->orWhere('account_number', 'like', "%{$s}%")
                  ->orWhere('account_name', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($sq) use ($s) {
                      $sq->where('name', 'like', "%{$s}%")
                        ->orWhere('email', 'like', "%{$s}%")
                        ->orWhereHas('district', fn($dq) => $dq->where('name', 'like', "%{$s}%"))
                        ->orWhereHas('city', fn($cq) => $cq->where('name', 'like', "%{$s}%"));
                  });
            });
        }

        $withdraws = $query->paginate(10);
        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.admin.withdraws.index', [
            'withdraws'        => $withdraws,
            'districts'        => $districts,
            'cities'           => $districts, // Backward compatibility
            'districtFilter'   => $this->districtFilter,
            'cityFilter'       => $this->districtFilter,
            'isSuperAdmin'     => $isSuperAdmin,
        ])->layout($layout);
    }
}
