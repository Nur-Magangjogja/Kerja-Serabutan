<?php

namespace App\Livewire\Admin\Verifications;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Registration;
use App\Models\User;
use App\Models\District;
use App\Models\City;
use App\Models\ActivityLog;
use App\Notifications\VehicleVerificationNotification;

class Index extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $activeTab = 'ktp'; // 'ktp' or 'vehicle'

    #[Url(history: true)]
    public $perPage = 10;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $statusFilter = '';

    #[Url(history: true)]
    public $roleFilter = '';

    #[Url(history: true)]
    public $vehicleStatusFilter = '';

    public $districtFilter = '';
    public $cityFilter = ''; // Backward compatibility alias

    public $showModal = false;
    public $selected = null;
    public $showRejectModal = false;
    public $rejectReason = '';
    public $rejectingId = null;

    // Vehicle verification modal states
    public $showVehicleModal = false;
    public $selectedVehicleUser = null;
    public $showRejectVehicleModal = false;
    public $vehicleRejectReason = '';
    public $rejectingVehicleUserId = null;

    protected $listeners = [
        'admin-district-changed'         => 'onAdminDistrictChanged',
        'admin-city-changed'             => 'onAdminDistrictChanged',
        'superadmin-territory-changed'   => '$refresh',
    ];

    public function mount()
    {
        $authUser = auth()->user();
        if ($authUser && $authUser->role === 'admin') {
            $this->districtFilter = $authUser->getActiveAdminDistrictFilter();
            $this->cityFilter = $this->districtFilter;
        }
    }

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

    public function updatingDistrictFilter()
    {
        $this->resetPage();
    }

    public function updatedDistrictFilter()
    {
        $authUser = auth()->user();
        if ($authUser && $authUser->role === 'admin') {
            $authUser->setActiveAdminDistrictFilter($this->districtFilter);
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
        $authUser = auth()->user();
        if ($authUser && $authUser->role === 'admin') {
            $this->districtFilter = $authUser->getActiveAdminDistrictFilter();
            $this->cityFilter = $this->districtFilter;
            $this->resetPage();
        }
    }

    public function onAdminCityChanged($cityId = null)
    {
        $this->onAdminDistrictChanged($cityId);
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    protected function isAuthorizedForRegistration(Registration $reg): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if (in_array($user->role, ['super_admin', 'superadmin'])) return true;
        if ($user->role === 'admin') {
            $allowedDistrictIds = $user->getEffectiveAdminDistrictIds();
            if (!empty($allowedDistrictIds)) {
                return !empty($reg->district_id) && in_array((int) $reg->district_id, $allowedDistrictIds, true);
            }
            return !empty($user->city_id) && (int) $reg->city_id === (int) $user->city_id;
        }
        return false;
    }

    public function viewKtp($id)
    {
        $reg = Registration::find($id);
        if (!$reg || !$this->isAuthorizedForRegistration($reg)) {
            session()->flash('message', 'Data tidak ditemukan atau berada di luar wilayah wewenang Anda.');
            return;
        }
        $this->selected = $reg;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->selected = null;
        $this->showModal = false;
    }

    public function openRejectModal($id)
    {
        $reg = Registration::find($id);
        if (!$reg || !$this->isAuthorizedForRegistration($reg)) {
            session()->flash('message', 'Data tidak ditemukan atau berada di luar wilayah wewenang Anda.');
            return;
        }
        $this->rejectingId = $id;
        $this->rejectReason = $reg->rejection_reason ?? '';
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
        if (!$reg || !$this->isAuthorizedForRegistration($reg)) {
            session()->flash('message', 'Registrasi tidak ditemukan atau berada di luar wilayah wewenang Anda.');
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
                    if (empty($user->nik) && !empty($reg->nik)) $user->nik = $reg->nik;
                    if (empty($user->ktp_photo) && !empty($reg->ktp_photo_path)) $user->ktp_photo = $reg->ktp_photo_path;
                    if (empty($user->ktp_path) && !empty($reg->ktp_photo_path)) $user->ktp_path = $reg->ktp_photo_path;
                    if (empty($user->selfie_photo) && !empty($reg->selfie_photo_path)) $user->selfie_photo = $reg->selfie_photo_path;
                    if (empty($user->district_id) && !empty($reg->district_id)) $user->district_id = $reg->district_id;
                    if (empty($user->district) && !empty($reg->district)) $user->district = $reg->district;
                    if (empty($user->city_id) && !empty($reg->city_id)) $user->city_id = $reg->city_id;
                    if (empty($user->city) && !empty($reg->city)) $user->city = $reg->city;
                    if (empty($user->rt) && !empty($reg->rt)) $user->rt = $reg->rt;
                    if (empty($user->rw) && !empty($reg->rw)) $user->rw = $reg->rw;
                    if (empty($user->kelurahan) && !empty($reg->kelurahan)) $user->kelurahan = $reg->kelurahan;
                    if (empty($user->kecamatan) && !empty($reg->kecamatan)) $user->kecamatan = $reg->kecamatan;
                    if (empty($user->province) && !empty($reg->province)) $user->province = $reg->province;
                    if (empty($user->gender) && !empty($reg->gender)) $user->gender = $reg->gender;
                    if (empty($user->phone) && !empty($reg->phone)) $user->phone = $reg->phone;
                    if (array_key_exists('email_verified_at', $user->getAttributes()) && empty($user->email_verified_at)) {
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

    // ─────────────────────────────────────────────────────────────────────────
    // VEHICLE VERIFICATION METHODS
    // ─────────────────────────────────────────────────────────────────────────

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['ktp', 'vehicle']) ? $tab : 'ktp';
        $this->resetPage();
    }

    public function updatingActiveTab(): void
    {
        $this->resetPage();
    }

    public function updatingVehicleStatusFilter(): void
    {
        $this->resetPage();
    }

    protected function isAuthorizedForUser(User $u): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if (in_array($user->role, ['super_admin', 'superadmin'])) return true;
        if ($user->role === 'admin') {
            $allowedDistrictIds = $user->getEffectiveAdminDistrictIds();
            if (!empty($allowedDistrictIds)) {
                return !empty($u->district_id) && in_array((int) $u->district_id, $allowedDistrictIds, true);
            }
            return !empty($user->city_id) && (int) $u->city_id === (int) $user->city_id;
        }
        return false;
    }

    public function viewVehicle(int $id): void
    {
        $targetUser = User::find($id);
        if (!$targetUser || !$this->isAuthorizedForUser($targetUser)) {
            session()->flash('message', 'Data mitra tidak ditemukan atau berada di luar wilayah wewenang Anda.');
            return;
        }
        $this->selectedVehicleUser = $targetUser;
        $this->showVehicleModal = true;
    }

    public function closeVehicleModal(): void
    {
        $this->selectedVehicleUser = null;
        $this->showVehicleModal = false;
    }

    public function approveVehicle(int $id): void
    {
        $targetUser = User::find($id);
        if (!$targetUser || !$this->isAuthorizedForUser($targetUser)) {
            session()->flash('message', 'Mitra tidak ditemukan atau berada di luar wilayah wewenang Anda.');
            return;
        }

        $targetUser->update([
            'vehicle_verified'            => true,
            'vehicle_verification_status' => 'verified',
            'vehicle_verified_at'         => now(),
            'vehicle_verified_by'         => auth()->id(),
            'vehicle_rejection_reason'    => null,
        ]);

        ActivityLog::record(
            auth()->user(),
            'vehicle_verified',
            "Admin " . (auth()->user()->name ?? 'Admin') . " menyetujui verifikasi kendaraan untuk mitra {$targetUser->name} (Plat: {$targetUser->vehicle_plate_number})",
            ['user_id' => $targetUser->id, 'plate' => $targetUser->vehicle_plate_number]
        );

        try {
            $targetUser->notify(new VehicleVerificationNotification('verified', null, $targetUser->vehicle_plate_number));
        } catch (\Throwable $e) {
            // ignore notification failure
        }

        session()->flash('message', "Data kendaraan untuk {$targetUser->name} ({$targetUser->vehicle_plate_number}) berhasil disetujui.");
        $this->closeVehicleModal();
    }

    public function openRejectVehicleModal(int $id): void
    {
        $targetUser = User::find($id);
        if (!$targetUser || !$this->isAuthorizedForUser($targetUser)) {
            session()->flash('message', 'Mitra tidak ditemukan atau berada di luar wilayah wewenang Anda.');
            return;
        }
        $this->rejectingVehicleUserId = $id;
        $this->vehicleRejectReason = $targetUser->vehicle_rejection_reason ?? '';
        $this->showRejectVehicleModal = true;
    }

    public function cancelRejectVehicle(): void
    {
        $this->rejectingVehicleUserId = null;
        $this->vehicleRejectReason = '';
        $this->showRejectVehicleModal = false;
    }

    public function confirmRejectVehicle(): void
    {
        $this->validate([
            'vehicleRejectReason' => 'required|string|min:3|max:500',
        ], [
            'vehicleRejectReason.required' => 'Alasan penolakan wajib diisi agar mitra mengetahui apa yang perlu diperbaiki.',
            'vehicleRejectReason.min'      => 'Alasan penolakan minimal 3 karakter.',
        ]);

        if (!$this->rejectingVehicleUserId) {
            session()->flash('message', 'Mitra tidak ditemukan.');
            $this->cancelRejectVehicle();
            return;
        }

        $targetUser = User::find($this->rejectingVehicleUserId);
        if (!$targetUser) {
            session()->flash('message', 'Mitra tidak ditemukan.');
            $this->cancelRejectVehicle();
            return;
        }

        $targetUser->update([
            'vehicle_verified'            => false,
            'vehicle_verification_status' => 'rejected',
            'vehicle_rejection_reason'    => $this->vehicleRejectReason,
        ]);

        ActivityLog::record(
            auth()->user(),
            'vehicle_rejected',
            "Admin " . (auth()->user()->name ?? 'Admin') . " menolak verifikasi kendaraan untuk mitra {$targetUser->name}. Alasan: {$this->vehicleRejectReason}",
            ['user_id' => $targetUser->id, 'plate' => $targetUser->vehicle_plate_number]
        );

        try {
            $targetUser->notify(new VehicleVerificationNotification('rejected', $this->vehicleRejectReason, $targetUser->vehicle_plate_number));
        } catch (\Throwable $e) {
            // ignore notification failure
        }

        session()->flash('message', "Verifikasi kendaraan mitra {$targetUser->name} telah ditolak.");
        $this->cancelRejectVehicle();
        $this->closeVehicleModal();
    }

    public function render()
    {
        $authUser = auth()->user();
        $isSuperAdmin = $authUser && in_array($authUser->role, ['super_admin', 'superadmin']);

        // 1. KTP Registrations Query
        $baseKtpQuery = Registration::query()
            ->with(['district', 'city']);

        // Strict district isolation: Admin only sees registrations from their assigned districts/city
        if (!$isSuperAdmin && $authUser && $authUser->role === 'admin') {
            $this->districtFilter = $authUser->getActiveAdminDistrictFilter();
            $this->cityFilter = $this->districtFilter;
            $effectiveDistrictIds = $authUser->getEffectiveAdminDistrictIds();
            $adminCityId = $authUser->city_id;
            if (!empty($effectiveDistrictIds)) {
                $baseKtpQuery->whereIn('district_id', $effectiveDistrictIds);
            } elseif (!empty($adminCityId)) {
                $baseKtpQuery->where('city_id', $adminCityId);
            }
        } elseif ($isSuperAdmin && $authUser) {
            $saTerritory = $authUser->getActiveSuperadminTerritory();
            if ($saTerritory['type'] === 'district' && !empty($saTerritory['id'])) {
                $baseKtpQuery->where('district_id', $saTerritory['id']);
            } elseif ($saTerritory['type'] === 'city' && !empty($saTerritory['id'])) {
                $saDistrictIds = $authUser->getEffectiveSuperadminDistrictIds();
                $cityId = (int) $saTerritory['id'];
                $baseKtpQuery->where(function ($sub) use ($cityId, $saDistrictIds) {
                    if (!empty($saDistrictIds)) {
                        $sub->whereIn('district_id', $saDistrictIds);
                    }
                    if ($cityId) {
                        $sub->orWhere('city_id', $cityId);
                    }
                });
            }
        }

        // Badge counter: KTP Pending sesuai wilayah wewenang admin
        $pendingKtpCount = (clone $baseKtpQuery)
            ->whereIn('status', ['pending', 'pending_verification'])
            ->count();

        // Query tabel verifikasi KTP
        $query = (clone $baseKtpQuery)
            ->whereIn('status', ['pending_verification', 'pending', 'approved', 'rejected']);

        // Search query (KTP)
        if (!empty($this->search) && $this->activeTab === 'ktp') {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('full_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('nik', 'like', "%{$s}%")
                  ->orWhere('kecamatan', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%")
                  ->orWhereHas('district', fn($dq) => $dq->where('name', 'like', "%{$s}%"));
            });
        }

        // Role filter (customer / mitra)
        if (!empty($this->roleFilter)) {
            $query->where('role', $this->roleFilter);
        }

        // Status filter (KTP)
        if (!empty($this->statusFilter)) {
            if ($this->statusFilter === 'pending') {
                $query->whereIn('status', ['pending', 'pending_verification']);
            } else {
                $query->where('status', $this->statusFilter);
            }
        }

        $verifications = $query->latest()->paginate($this->perPage);

        // 2. Vehicle Verifications Query
        $baseVehicleQuery = User::query()
            ->where('role', 'mitra');

        if (!$isSuperAdmin && $authUser && $authUser->role === 'admin') {
            $effectiveDistrictIds = $authUser->getEffectiveAdminDistrictIds();
            $adminCityId = $authUser->city_id;
            if (!empty($effectiveDistrictIds)) {
                $baseVehicleQuery->whereIn('district_id', $effectiveDistrictIds);
            } elseif (!empty($adminCityId)) {
                $baseVehicleQuery->where('city_id', $adminCityId);
            }
        } elseif ($isSuperAdmin && $authUser) {
            $saTerritory = $authUser->getActiveSuperadminTerritory();
            if ($saTerritory['type'] === 'district' && !empty($saTerritory['id'])) {
                $baseVehicleQuery->where('district_id', $saTerritory['id']);
            } elseif ($saTerritory['type'] === 'city' && !empty($saTerritory['id'])) {
                $saDistrictIds = $authUser->getEffectiveSuperadminDistrictIds();
                $cityId = (int) $saTerritory['id'];
                $baseVehicleQuery->where(function ($sub) use ($cityId, $saDistrictIds) {
                    if (!empty($saDistrictIds)) {
                        $sub->whereIn('district_id', $saDistrictIds);
                    }
                    if ($cityId) {
                        $sub->orWhere('city_id', $cityId);
                    }
                });
            }
        }

        // Badge counter: Kendaraan Mitra Pending sesuai wilayah wewenang admin
        $pendingVehicleCount = (clone $baseVehicleQuery)
            ->where('vehicle_verification_status', 'pending')
            ->count();

        // Query tabel verifikasi kendaraan
        $vehicleQuery = (clone $baseVehicleQuery)
            ->whereIn('vehicle_verification_status', ['pending', 'verified', 'rejected']);

        // Search in vehicle query
        if (!empty($this->search) && $this->activeTab === 'vehicle') {
            $s = trim($this->search);
            $vehicleQuery->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('vehicle_plate_number', 'like', "%{$s}%")
                  ->orWhere('vehicle_sim_number', 'like', "%{$s}%")
                  ->orWhere('vehicle_stnk_number', 'like', "%{$s}%");
            });
        }

        // Vehicle status filter
        if (!empty($this->vehicleStatusFilter)) {
            $vehicleQuery->where('vehicle_verification_status', $this->vehicleStatusFilter);
        }

        $vehicleVerifications = $vehicleQuery->latest('updated_at')->paginate($this->perPage, ['*'], 'vehicle_page');

        $layout = ($authUser && in_array($authUser->role, ['super_admin', 'superadmin'])) 
            ? 'layouts.superadmin' 
            : 'layouts.admin';

        return view('livewire.admin.verifications.index', [
            'verifications'        => $verifications,
            'vehicleVerifications' => $vehicleVerifications,
            'pendingKtpCount'      => $pendingKtpCount,
            'pendingVehicleCount'  => $pendingVehicleCount,
            'authUser'             => $authUser,
        ])->layout($layout);
    }
}