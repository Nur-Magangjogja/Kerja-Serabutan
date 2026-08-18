<?php

namespace App\Livewire\Mitra;

use App\Models\Help;
use App\Models\User;
use App\Notifications\HelpTakenNotification;
use Livewire\Component;
use App\Services\LocationTrackingService;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Schema;

#[Layout('layouts.mitra')]
class AllHelps extends Component
{
    use WithPagination;

    protected $queryString = [
        'search' => ['except' => ''],
        'distanceRadius' => ['except' => 'all'],
        'sortBy' => ['except' => 'nearby'],
        'viewMode' => ['except' => 'list'],
    ];

    public $search = '';
    public $filterStatus = 'all'; // all, my_city
    public $distanceRadius = 'all'; // all, 5, 15, 25, city
    public $sortBy = 'nearby'; // nearby, latest, oldest, price_high, price_low
    public $viewMode = 'list'; // list, map
    public $mitraLat = null;
    public $mitraLng = null;

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

    public function render()
    {
        $user = auth()->user();
        $locationService = app(LocationTrackingService::class);

        $query = Help::where('status', 'menunggu_mitra')->whereNull('mitra_id');

        // Filter Kota Saya
        if ($this->distanceRadius === 'city' && $user && !empty($user->city_id)) {
            $query->where('city_id', $user->city_id);
        }

        // Search berdasarkan judul, deskripsi, user, atau kota
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($userQuery) {
                        $userQuery->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('city', function ($cityQuery) {
                        $cityQuery->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // Get all items for distance calculation and sorting
        $allHelps = $query->with(['user', 'city'])->get();

        // Attach distance if coordinates available
        $allHelps->transform(function ($help) use ($locationService) {
            if ($this->mitraLat && $this->mitraLng && $help->latitude && $help->longitude) {
                $distanceMeters = $locationService->calculateDistance(
                    $this->mitraLat,
                    $this->mitraLng,
                    (float) $help->latitude,
                    (float) $help->longitude
                );
                $help->distance_km = round($distanceMeters / 1000, 1);
            } else {
                $help->distance_km = null;
            }
            return $help;
        });

        // Filter by Radius (5 km, 15 km, 25 km)
        if (in_array($this->distanceRadius, ['5', '15', '25'])) {
            $maxKm = (float) $this->distanceRadius;
            $allHelps = $allHelps->filter(function ($help) use ($maxKm) {
                return $help->distance_km !== null ? $help->distance_km <= $maxKm : true;
            });
        }

        // Sorting
        if ($this->sortBy === 'nearby') {
            $allHelps = $allHelps->sortBy(function ($help) use ($user) {
                // If has distance, sort by distance; else sort by city match then latest
                if ($help->distance_km !== null) {
                    return $help->distance_km;
                }
                return ($user && $help->city_id == $user->city_id) ? 9999 : 99999;
            });
        } elseif ($this->sortBy === 'latest') {
            $allHelps = $allHelps->sortByDesc('created_at');
        } elseif ($this->sortBy === 'oldest') {
            $allHelps = $allHelps->sortBy('created_at');
        } elseif ($this->sortBy === 'price_high') {
            $allHelps = $allHelps->sortByDesc('amount');
        } elseif ($this->sortBy === 'price_low') {
            $allHelps = $allHelps->sortBy('amount');
        }

        // Paginate manually from collection
        $currentPage = \Livewire\WithPagination::class ? $this->getPage() : 1;
        $perPage = 15;
        $currentPageItems = $allHelps->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $helps = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $allHelps->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        // Prepare data for map view
        $mapHelps = $allHelps->filter(fn($h) => !empty($h->latitude) && !empty($h->longitude))->values()->map(function($h) {
            return [
                'id' => $h->id,
                'title' => $h->title,
                'amount' => $h->amount,
                'formatted_amount' => 'Rp ' . number_format($h->amount, 0, ',', '.'),
                'lat' => (float) $h->latitude,
                'lng' => (float) $h->longitude,
                'city' => $h->city?->name ?? '',
                'location' => $h->location ?? '',
                'distance_km' => $h->distance_km,
                'creator' => $h->user?->name ?? 'Pengguna',
                'scheduled' => $h->scheduled_at ? \Carbon\Carbon::parse($h->scheduled_at)->translatedFormat('d M Y, H:i') : null,
            ];
        });

        return view('livewire.mitra.helps.all-helps', [
            'helps' => $helps,
            'mapHelps' => $mapHelps,
            'needsCity' => false,
            'userCity' => $user && $user->city_id ? \App\Models\City::find($user->city_id) : null,
            'viewMode' => $this->viewMode,
            'distanceRadius' => $this->distanceRadius,
            'sortBy' => $this->sortBy,
            'search' => $this->search,
            'mitraLat' => $this->mitraLat,
            'mitraLng' => $this->mitraLng,
        ]);
    }

    public function takeHelp($helpId, $latitude = null, $longitude = null)
    {
        $help = Help::findOrFail($helpId);

        if ($help->mitra_id) {
            session()->flash('error', 'Bantuan ini sudah diambil oleh mitra lain.');
            return;
        }

        $help->update([
            'mitra_id' => auth()->id(),
            'status' => 'taken',
            'taken_at' => now(),
        ]);

        // Set lokasi awal mitra jika GPS tersedia dari parameter
        if ($latitude && $longitude) {
            try {
                $locationService = app(LocationTrackingService::class);
                $locationService->setInitialLocation($help, (float) $latitude, (float) $longitude);
                \Log::info('Set initial partner location from GPS on takeHelp', [
                    'help_id' => $help->id, 
                    'mitra_id' => auth()->id(),
                    'lat' => $latitude,
                    'lng' => $longitude
                ]);
            } catch (\Throwable $e) {
                \Log::warning('Failed to set initial location from GPS: ' . $e->getMessage(), ['help_id' => $help->id]);
            }
        } else {
            // Fallback: Jika GPS tidak tersedia, coba gunakan koordinat yang tersimpan pada profil mitra
            try {
                $mitra = auth()->user();
                if (!empty($mitra->latitude) && !empty($mitra->longitude)) {
                    try {
                        $locationService = app(LocationTrackingService::class);
                        $locationService->setInitialLocation($help, (float) $mitra->latitude, (float) $mitra->longitude);
                        \Log::info('Set initial partner location from profile on takeHelp', ['help_id' => $help->id, 'mitra_id' => $mitra->id]);
                    } catch (\Throwable $e) {
                        \Log::warning('Failed to set initial location from profile: ' . $e->getMessage(), ['help_id' => $help->id]);
                    }
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        session()->flash('message', 'Bantuan berhasil diambil. Silakan hubungi pengguna.');

        // Create a notification for the customer so it appears in their notifications page
        try {
            $customer = User::find($help->user_id);
            if ($customer) {
                $customer->notify(new HelpTakenNotification($helpId, auth()->id(), optional(auth()->user())->name));
            }
        } catch (\Throwable $e) {
            // ignore notification failures
        }

        // Emit event untuk redirect ke detail page
        $this->dispatch('help-taken', helpId: $helpId);

        // refresh pagination and query so the help disappears from the list
        $this->resetPage();
    }

}