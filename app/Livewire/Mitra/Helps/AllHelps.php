<?php

namespace App\Livewire\Mitra\Helps;

use App\Models\Help;
use App\Services\HelpTransactionService;
use App\Services\LocationTrackingService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.mitra')]
class AllHelps extends Component
{
    use WithPagination;

    protected $queryString = [
        'search'         => ['except' => ''],
        'distanceRadius' => ['except' => 'all'],
        'sortBy'         => ['except' => 'nearby'],
    ];

    public $search         = '';
    public $distanceRadius = 'all'; // all, 5, 15, 60, city
    public $sortBy         = 'nearby'; // nearby, latest, oldest, price_high, price_low
    public $mitraLat       = null;
    public $mitraLng       = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDistanceRadius()
    {
        $this->resetPage();
    }

    public function setMitraLocation($lat, $lng)
    {
        $this->mitraLat = (float) $lat;
        $this->mitraLng = (float) $lng;
    }

    /**
     * Mitra mengambil bantuan dari pool.
     * Didelegasikan ke HelpTransactionService.
     */
    public function takeHelp($helpId, $latitude = null, $longitude = null)
    {
        if (auth()->user()?->isShadowBanned()) {
            session()->flash('error', 'Akun Anda saat ini dibatasi dari mengambil tugas bantuan karena dalam peninjauan moderasi.');
            return;
        }

        $help = Help::findOrFail($helpId);

        // Guard: manual takeHelp hanya diizinkan jika order sudah berstatus Open Pool
        if ($help->dispatch_mode && $help->dispatch_mode !== Help::DISPATCH_MODE_POOL) {
            session()->flash('error', 'Pesanan ini sedang dalam penawaran sequential khusus dan belum dibuka untuk pool umum.');
            return;
        }

        try {
            app(HelpTransactionService::class)->takeHelp(
                $help,
                auth()->user(),
                $latitude ? (float) $latitude : null,
                $longitude ? (float) $longitude : null
            );

            session()->flash('message', 'Bantuan berhasil diambil. Silakan hubungi pengguna.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            return;
        } catch (\Throwable $e) {
            \Log::error('[Mitra/AllHelps] takeHelp error: ' . $e->getMessage(), ['help_id' => $helpId]);
            session()->flash('error', 'Terjadi kesalahan saat mengambil bantuan.');
            return;
        }

        // Emit event untuk redirect ke detail page
        $this->dispatch('help-taken', helpId: $helpId);
        $this->resetPage();
    }

    public function render()
    {
        $user            = auth()->user();
        $locationService = app(LocationTrackingService::class);

        // Jika Mitra terkena shadow ban, jangan tampilkan daftar pekerjaan
        if ($user && $user->isShadowBanned()) {
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                15,
                1,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );

            return view('livewire.mitra.helps.all-helps', [
                'helps'          => $emptyPaginator,
                'needsCity'      => false,
                'userCity'       => $user->city_id ? \App\Models\City::find($user->city_id) : null,
                'distanceRadius' => $this->distanceRadius,
                'sortBy'         => $this->sortBy,
                'search'         => $this->search,
                'mitraLat'       => $this->mitraLat,
                'mitraLng'       => $this->mitraLng,
                'activeTask'     => null,
                'isShadowBanned' => true,
            ]);
        }

        // Pool Terbuka: hanya bantuan yang berstatus menunggu_mitra DAN dispatch_mode = 'pool'
        $query = Help::where('status', Help::STATUS_MENUNGGU_MITRA)
            ->where(function ($q) {
                $q->where('dispatch_mode', Help::DISPATCH_MODE_POOL)
                  ->orWhereNull('dispatch_mode');
            })
            ->whereNull('mitra_id')
            ->availableForMitra($user?->id)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '<=', now());
            });

        // Filter Kota Saya
        if ($this->distanceRadius === 'city' && $user && !empty($user->city_id)) {
            $query->where('city_id', $user->city_id);
        }

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', '%' . $this->search . '%'))
                  ->orWhereHas('city', fn($c) => $c->where('name', 'like', '%' . $this->search . '%'));
            });
        }

        $allHelps = $query->with(['user', 'city'])->get();

        // Hitung jarak
        $allHelps->transform(function ($help) use ($locationService) {
            if ($this->mitraLat && $this->mitraLng && $help->latitude && $help->longitude) {
                $distanceMeters = $locationService->calculateDistance(
                    $this->mitraLat, $this->mitraLng,
                    (float) $help->latitude, (float) $help->longitude
                );
                $help->distance_km = round($distanceMeters / 1000, 1);
            } else {
                $help->distance_km = null;
            }
            return $help;
        });

        // Filter batas maksimal jangkauan radius dinamis dari AppSetting (jika GPS terdeteksi)
        $maxAppRadiusKm = \App\Models\AppSetting::getMaxMatchingRadiusKm();
        if ($this->mitraLat && $this->mitraLng) {
            $allHelps = $allHelps->filter(function ($h) use ($maxAppRadiusKm) {
                return $h->distance_km === null || $h->distance_km <= $maxAppRadiusKm;
            });
        }

        // Filter radius pilihan km (5, 15, 30, 60)
        if (in_array($this->distanceRadius, ['5', '15', '25', '30', '60'])) {
            $maxKm    = (float) $this->distanceRadius;
            $allHelps = $allHelps->filter(fn($h) => $h->distance_km !== null ? $h->distance_km <= $maxKm : true);
        }

        // Sorting
        $allHelps = match($this->sortBy) {
            'nearby'     => $allHelps->sortBy(fn($h) => $h->distance_km ?? (($user && $h->city_id == $user->city_id) ? 9999 : 99999)),
            'latest'     => $allHelps->sortByDesc('created_at'),
            'oldest'     => $allHelps->sortBy('created_at'),
            'price_high' => $allHelps->sortByDesc('amount'),
            'price_low'  => $allHelps->sortBy('amount'),
            default      => $allHelps,
        };

        // Manual pagination
        $currentPage  = $this->getPage();
        $perPage      = 15;
        $currentItems = $allHelps->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $helps        = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $allHelps->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        $activeTask = $user ? Help::where('mitra_id', $user->id)->active()->first() : null;

        return view('livewire.mitra.helps.all-helps', [
            'helps'          => $helps,
            'needsCity'      => false,
            'userCity'       => $user && $user->city_id ? \App\Models\City::find($user->city_id) : null,
            'distanceRadius' => $this->distanceRadius,
            'sortBy'         => $this->sortBy,
            'search'         => $this->search,
            'mitraLat'       => $this->mitraLat,
            'mitraLng'       => $this->mitraLng,
            'activeTask'     => $activeTask,
        ]);
    }
}
