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
        'districtFilter' => ['except' => 'all'],
        'sortBy'         => ['except' => 'nearby'],
    ];

    public $search         = '';
    public $districtFilter = 'all'; // all, my_district, or district_id
    public $sortBy         = 'nearby'; // nearby, latest, oldest, price_high, price_low
    public $mitraLat       = null;
    public $mitraLng       = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDistrictFilter()
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
        $user = auth()->user();
        if ($user && ($user->isShadowBanned() || $user->warning_level >= 3 || $user->status === 'blocked')) {
            session()->flash('error', 'Akun Anda saat ini dibatasi dari mengambil tugas bantuan karena dalam pembatasan moderasi / sanksi.');
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
        $maxOperationalKm = \App\Models\AppSetting::MAX_OPERATIONAL_RADIUS_KM; // Baku 10.0 KM

        // Jika Mitra terkena shadow ban atau sanksi SP 3 / blocked, jangan tampilkan daftar pekerjaan
        if ($user && ($user->isShadowBanned() || $user->warning_level >= 3 || $user->status === 'blocked')) {
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
                'userDistrict'   => $user->district_id ? \App\Models\District::find($user->district_id) : null,
                'userCity'       => $user->city_id ? \App\Models\City::find($user->city_id) : null,
                'districtFilter' => $this->districtFilter,
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

        // Filter Wilayah Kecamatan
        if ($this->districtFilter === 'my_district' && $user && !empty($user->district_id)) {
            $query->where('district_id', $user->district_id);
        } elseif (is_numeric($this->districtFilter) && (int) $this->districtFilter > 0) {
            $query->where('district_id', (int) $this->districtFilter);
        }

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', '%' . $this->search . '%'))
                  ->orWhereHas('district', fn($d) => $d->where('name', 'like', '%' . $this->search . '%'))
                  ->orWhereHas('city', fn($c) => $c->where('name', 'like', '%' . $this->search . '%'));
            });
        }

        $hasGps = ($this->mitraLat && $this->mitraLng);
        $haversineSql = null;

        if ($hasGps) {
            $lat = (float) $this->mitraLat;
            $lng = (float) $this->mitraLng;

            // 1. Batas jangkauan operasional baku: 10.0 KM
            $filterRadius = $maxOperationalKm;

            // 2. Bounding Box Pre-Filter (Index B-Tree)
            $latDelta = $filterRadius / 111.045;
            $lngDelta = $filterRadius / (111.045 * max(0.01, cos(deg2rad($lat))));

            $minLat = $lat - $latDelta;
            $maxLat = $lat + $latDelta;
            $minLng = $lng - $lngDelta;
            $maxLng = $lng + $lngDelta;

            // 3. Formula Haversine SQL Presisi
            $haversineSql = "(6371 * acos(least(1.0, greatest(-1.0, cos(radians($lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($lng)) + sin(radians($lat)) * sin(radians(latitude))))))";

            $query->select('helps.*')
                  ->selectRaw("$haversineSql AS distance_km");

            // Filter: Jarak fisik GPS maksimal <= 10.0 KM
            $query->where(function ($q) use ($minLat, $maxLat, $minLng, $maxLng, $haversineSql, $filterRadius, $user) {
                $q->where(function ($sub) use ($minLat, $maxLat, $minLng, $maxLng, $haversineSql, $filterRadius) {
                    $sub->whereBetween('latitude', [$minLat, $maxLat])
                        ->whereBetween('longitude', [$minLng, $maxLng])
                        ->whereRaw("$haversineSql <= ?", [$filterRadius]);
                })->orWhere(function ($sub) use ($user) {
                    // Jika order belum ada koordinat, sertakan hanya jika dalam kecamatan yang sama
                    $sub->where(function($s) {
                        $s->whereNull('latitude')->orWhereNull('longitude');
                    });
                    if ($user && $user->district_id) {
                        $sub->where('district_id', $user->district_id);
                    }
                });
            });
        }

        // Database-Level Sorting: Prioritaskan terdekat GPS <= 10 KM
        match ($this->sortBy) {
            'nearby' => $hasGps
                ? $query->orderByRaw("CASE WHEN latitude IS NOT NULL AND longitude IS NOT NULL THEN $haversineSql ELSE 99999 END ASC")
                : ($user && $user->district_id ? $query->orderByRaw("(district_id = ?) DESC", [$user->district_id])->latest() : $query->latest()),
            'latest'     => $query->latest(),
            'oldest'     => $query->oldest(),
            'price_high' => $query->orderByDesc('amount'),
            'price_low'  => $query->orderBy('amount'),
            default      => $hasGps 
                ? $query->orderByRaw("CASE WHEN latitude IS NOT NULL AND longitude IS NOT NULL THEN $haversineSql ELSE 99999 END ASC")
                : $query->latest(),
        };

        // Native Database Pagination
        $helps = $query->with(['user', 'city', 'district'])->paginate(15);

        // Format angka distance_km jika dihitung dari SQL
        if ($hasGps) {
            $helps->getCollection()->transform(function ($h) {
                if ($h->distance_km !== null) {
                    $h->distance_km = round((float) $h->distance_km, 1);
                }
                return $h;
            });
        }

        $activeTask = $user ? Help::where('mitra_id', $user->id)->active()->first() : null;

        return view('livewire.mitra.helps.all-helps', [
            'helps'          => $helps,
            'needsCity'      => false,
            'userDistrict'   => $user && $user->district_id ? \App\Models\District::find($user->district_id) : null,
            'userCity'       => $user && $user->city_id ? \App\Models\City::find($user->city_id) : null,
            'districtFilter' => $this->districtFilter,
            'sortBy'         => $this->sortBy,
            'search'         => $this->search,
            'mitraLat'       => $this->mitraLat,
            'mitraLng'       => $this->mitraLng,
            'activeTask'     => $activeTask,
        ]);
    }
}
