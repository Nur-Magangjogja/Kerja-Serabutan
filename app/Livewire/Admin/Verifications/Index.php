<?php

namespace App\Livewire\Admin\Verifications;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Registration;
use App\Models\User;
use App\Models\City;

class Index extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $statusFilter = '';
    public $roleFilter = '';
    public $cityFilter = '';

    public $showModal = false;
    public $selected = null;
    public $showRejectModal = false;
    public $rejectReason = '';
    public $rejectingId = null;

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

    public function updatingCityFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function viewKtp($id)
    {
        $this->selected = Registration::find($id);
        if (!$this->selected) {
            session()->flash('message', 'Data tidak ditemukan.');
            return;
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->selected = null;
        $this->showModal = false;
    }

    public function openRejectModal($id)
    {
        $this->rejectingId = $id;
        $reg = Registration::find($id);
        $this->rejectReason = $reg?->rejection_reason ?? '';
        $this->showRejectModal = true;
    }

    public function cancelReject()
    {
        $this->rejectingId = null;
        $this->rejectReason = '';
        $this->showRejectModal = false;
    }

    public function approveKtp($id)
    {
        $reg = Registration::find($id);
        if (!$reg) {
            session()->flash('message', 'Registrasi tidak ditemukan');
            return;
        }
        $reg->update(['status' => 'approved']);

        // Jika ada user terkait (dibuat saat registrasi step4), update status dan verifikasi
        try {
            if (!empty($reg->email)) {
                $user = User::where('email', $reg->email)->first();
                if ($user) {
                    $user->verified = true;
                    $user->status = 'active';
                    if (array_key_exists('email_verified_at', $user->getAttributes())) {
                        $user->email_verified_at = now();
                    }
                    $user->save();
                }
            }
        } catch (\Exception $e) {
            // do not block admin action if user update fails
        }

        // Catat ke log aktivitas sistem
        \App\Models\ActivityLog::record(
            auth()->user(),
            'ktp_verified',
            "Admin " . (auth()->user()->name ?? 'Admin') . " menyetujui verifikasi KTP untuk pendaftar {$reg->name} ({$reg->email})",
            ['registration_id' => $reg->id, 'email' => $reg->email]
        );

        session()->flash('message', 'Registrasi berhasil disetujui.');
        $this->closeModal();
    }

    public function rejectKtp($id)
    {
        $this->openRejectModal($id);
    }

    public function confirmReject()
    {
        $this->validate([
            'rejectReason' => 'nullable|string|max:500',
        ]);

        if (!$this->rejectingId) {
            session()->flash('message', 'Registrasi tidak ditemukan');
            $this->cancelReject();
            return;
        }

        $reg = Registration::find($this->rejectingId);
        if (!$reg) {
            session()->flash('message', 'Registrasi tidak ditemukan');
            $this->cancelReject();
            return;
        }

        $reg->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejectReason,
        ]);

        try {
            if (!empty($reg->email)) {
                $user = User::where('email', $reg->email)->first();
                if ($user) {
                    $user->verified = false;
                    $user->status = 'inactive';
                    $user->save();
                }
            }
        } catch (\Exception $e) {
            // ignore
        }

        // Catat ke log aktivitas sistem
        \App\Models\ActivityLog::record(
            auth()->user(),
            'ktp_rejected',
            "Admin " . (auth()->user()->name ?? 'Admin') . " menolak verifikasi KTP untuk {$reg->name} ({$reg->email}). Alasan: " . ($this->rejectReason ?: 'Tidak ada keterangan'),
            ['registration_id' => $reg->id, 'email' => $reg->email, 'reason' => $this->rejectReason]
        );

        session()->flash('message', 'Registrasi ditolak. Alasan penolakan disimpan.');
        $this->cancelReject();
        $this->closeModal();
    }

    public function render()
    {
        $query = Registration::query();
        $authUser = auth()->user();
        $isSuperAdmin = $authUser && in_array($authUser->role, ['super_admin', 'superadmin']);

        // Strict city isolation: Admin only sees registrations from their assigned city
        if (!$isSuperAdmin && $authUser && $authUser->role === 'admin' && $authUser->city_id) {
            $query->where('city_id', $authUser->city_id);
        } elseif ($isSuperAdmin) {
            // Super Admin can filter by city or see all
            if ($this->cityFilter !== '' && $this->cityFilter !== 'all') {
                if ($this->cityFilter === 'unassigned') {
                    $query->whereNull('city_id');
                } else {
                    $query->where('city_id', $this->cityFilter);
                }
            }
        }

        // Search query
        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('full_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('nik', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%");
            });
        }

        // Role filter (customer / mitra)
        if (!empty($this->roleFilter)) {
            $query->where('role', $this->roleFilter);
        }

        // Status filter
        if (!empty($this->statusFilter)) {
            if ($this->statusFilter === 'pending') {
                $query->whereIn('status', ['pending', 'pending_verification']);
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        $verifications = $query->latest()->paginate($this->perPage);
        $cities = City::orderBy('name')->get();

        $layout = ($authUser && in_array($authUser->role, ['super_admin', 'superadmin'])) 
            ? 'layouts.superadmin' 
            : 'layouts.admin';

        return view('livewire.admin.verifications.index', compact('verifications', 'cities', 'authUser'))
            ->layout($layout);
    }
}