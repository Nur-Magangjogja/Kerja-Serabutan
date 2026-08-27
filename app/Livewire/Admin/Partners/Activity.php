<?php

namespace App\Livewire\Admin\Partners;

use App\Models\City;
use App\Models\Help;
use App\Models\PartnerActivity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class Activity extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = 'all'; // all, customer, mitra
    public $activityType = 'all';
    public $cityId = 'all';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 15;

    // Modal Detail Bantuan
    public $selectedHelpId = null;
    public $selectedHelp = null;
    public $showHelpModal = false;

    protected $queryString = [
        'search'       => ['except' => ''],
        'roleFilter'   => ['except' => 'all'],
        'activityType' => ['except' => 'all'],
        'cityId'       => ['except' => 'all'],
        'dateFrom'     => ['except' => ''],
        'dateTo'       => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingActivityType()
    {
        $this->resetPage();
    }

    public function updatingCityId()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'roleFilter', 'activityType', 'cityId', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function showHelpDetails($helpId)
    {
        $this->selectedHelpId = $helpId;
        $this->selectedHelp = Help::with(['customer', 'mitra', 'city', 'ratings.rater'])
            ->find($helpId);

        if ($this->selectedHelp) {
            $this->showHelpModal = true;
        } else {
            session()->flash('error', 'Data bantuan tidak ditemukan.');
        }
    }

    public function closeHelpDetails()
    {
        $this->showHelpModal = false;
        $this->selectedHelpId = null;
        $this->selectedHelp = null;
    }

    public function resetSession($userId)
    {
        $user = User::findOrFail($userId);
        DB::table('sessions')->where('user_id', $user->id)->delete();
        session()->flash('success', "Seluruh sesi aktif untuk {$user->name} berhasil di-reset.");
    }

    public function resetPassword($userId)
    {
        $user = User::findOrFail($userId);
        $user->update([
            'password' => Hash::make('password123'),
        ]);
        session()->flash('success', "Password {$user->name} telah di-reset ke default: 'password123'.");
    }

    public function render()
    {
        $admin = auth()->user();
        $isSuperAdmin = in_array($admin->role ?? '', ['super_admin', 'superadmin']);

        // Hanya aktivitas Mitra & Customer (TIDAK ADA aktivitas admin/superadmin)
        $query = PartnerActivity::with([
            'user.city',
            'help.customer',
            'help.mitra',
            'help.city'
        ])
        ->whereHas('user', function ($q) {
            $q->whereIn('role', ['customer', 'mitra']);
        })
        ->latest();

        // Batasan kota untuk Admin biasa (berdasarkan city_id admin)
        if (! $isSuperAdmin) {
            if ($admin->city_id) {
                $adminCityId = $admin->city_id;
                $query->where(function ($q) use ($adminCityId) {
                    $q->whereHas('user', fn($uq) => $uq->where('city_id', $adminCityId))
                      ->orWhereHas('help', fn($hq) => $hq->where('city_id', $adminCityId));
                });
            }
        }

        // Filter Role Pelaku (Customer / Mitra)
        if ($this->roleFilter !== 'all') {
            $query->whereHas('user', fn($q) => $q->where('role', $this->roleFilter));
        }

        // Filter Tipe Aktivitas Pekerjaan
        if ($this->activityType !== 'all') {
            $query->where('activity_type', $this->activityType);
        }

        // Filter Kota (Pilihan Admin/Superadmin)
        if ($this->cityId !== 'all') {
            $cId = $this->cityId;
            $query->where(function ($q) use ($cId) {
                $q->whereHas('user', fn($uq) => $uq->where('city_id', $cId))
                  ->orWhereHas('help', fn($hq) => $hq->where('city_id', $cId));
            });
        }

        // Filter Tanggal
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        // Pencarian (Judul bantuan, Order ID, nama/email customer/mitra, deskripsi, IP)
        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('activity_type', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('name', 'like', "%{$s}%")
                         ->orWhere('email', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  })
                  ->orWhereHas('help', function ($hq) use ($s) {
                      $hq->where('title', 'like', "%{$s}%")
                         ->orWhere('order_id', 'like', "%{$s}%")
                         ->orWhere('location', 'like', "%{$s}%")
                         ->orWhere('full_address', 'like', "%{$s}%");
                  });
            });
        }

        $activities = $query->paginate($this->perPage);

        // Daftar kota untuk dropdown filter
        if ($isSuperAdmin) {
            $cities = City::orderBy('name')->get();
        } else {
            $cities = City::where('id', $admin->city_id)->get();
        }

        // Statistik ringkas aktivitas pekerjaan
        $baseStats = PartnerActivity::whereHas('user', fn($q) => $q->whereIn('role', ['customer', 'mitra']));
        if (! $isSuperAdmin && $admin->city_id) {
            $baseStats->whereHas('user', fn($q) => $q->where('city_id', $admin->city_id));
        }

        $stats = [
            'total'          => (clone $baseStats)->count(),
            'today'          => (clone $baseStats)->whereDate('created_at', today())->count(),
            'customer_acts'  => (clone $baseStats)->whereHas('user', fn($q) => $q->where('role', 'customer'))->count(),
            'mitra_acts'     => (clone $baseStats)->whereHas('user', fn($q) => $q->where('role', 'mitra'))->count(),
            'completed_jobs' => (clone $baseStats)->whereIn('activity_type', ['help_completed', 'service_completed', 'confirm_completion', 'help_confirmed'])->count(),
        ];

        $layout = $isSuperAdmin ? 'layouts.superadmin' : 'layouts.admin';

        return view('livewire.admin.partners.activity', [
            'activities' => $activities,
            'cities'     => $cities,
            'stats'      => $stats,
        ])->layout($layout);
    }
}
