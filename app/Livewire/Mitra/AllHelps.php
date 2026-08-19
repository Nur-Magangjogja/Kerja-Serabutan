<?php

namespace App\Livewire\Mitra;

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
        'viewMode'       => ['except' => 'list'],
    ];

    public $search         = '';
    public $distanceRadius = 'all'; // all, 5, 15, 25, city
    public $sortBy         = 'nearby'; // nearby, latest, oldest, price_high, price_low
    public $viewMode       = 'list'; // list, map
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
        $help = Help::findOrFail($helpId);

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

        $query = Help::where('status', Help::STATUS_MENUNGGU_MITRA)
            ->whereNull('mitra_id')
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

        // Filter radius km
        if (in_array($this->distanceRadius, ['5', '15', '25'])) {
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

        // Data untuk map view
        $mapHelps = $allHelps
            ->filter(fn($h) => !empty($h->latitude) && !empty($h->longitude))
            ->values()
            ->map(fn($h) => [
                'id'               => $h->id,
                'title'            => $h->title,
                'amount'           => $h->amount,
                'formatted_amount' => 'Rp ' . number_format($h->amount, 0, ',', '.'),
                'lat'              => (float) $h->latitude,
                'lng'              => (float) $h->longitude,
                'city'             => $h->city?->name ?? '',
                'location'         => $h->location ?? '',
                'distance_km'      => $h->distance_km,
                'creator'          => $h->user?->name ?? 'Pengguna',
                'scheduled'        => $h->scheduled_at ? \Carbon\Carbon::parse($h->scheduled_at)->translatedFormat('d M Y, H:i') : null,
            ]);

        $activeTask = $user ? Help::where('mitra_id', $user->id)->active()->first() : null;

        return view('livewire.mitra.helps.all-helps', [
            'helps'          => $helps,
            'mapHelps'       => $mapHelps,
            'needsCity'      => false,
            'userCity'       => $user && $user->city_id ? \App\Models\City::find($user->city_id) : null,
            'viewMode'       => $this->viewMode,
            'distanceRadius' => $this->distanceRadius,
            'sortBy'         => $this->sortBy,
            'search'         => $this->search,
            'mitraLat'       => $this->mitraLat,
            'mitraLng'       => $this->mitraLng,
            'activeTask'     => $activeTask,
        ]);
    }
}