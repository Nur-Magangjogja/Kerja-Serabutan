<?php

namespace App\Livewire\Mitra\Helps;

use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Services\HelpTransactionService;
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
    public $districtFilter = 'all'; // 'all' (radius 10 km), 'my_district', 'my_city', or district_id
    public $sortBy         = 'nearby'; // nearby, latest, oldest, price_high, price_low
    public $mitraLat       = null;
    public $mitraLng       = null;

    public function mount()
    {
        $user = auth()->user();
        if ($user) {
            // Ambil koordinat GPS terakhir yang tersimpan pada PartnerOnlineState atau User
            $onlineState = PartnerOnlineState::where('user_id', $user->id)->first();
            if ($onlineState && $onlineState->latitude && $onlineState->longitude) {
                $this->mitraLat = (float) $onlineState->latitude;
                $this->mitraLng = (float) $onlineState->longitude;
            } elseif ($user->latitude && $user->longitude) {
                $this->mitraLat = (float) $user->latitude;
                $this->mitraLng = (float) $user->longitude;
            }
        }
    }

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

        $user = auth()->user();
        if ($user) {
            PartnerOnlineState::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'latitude'     => $this->mitraLat,
                    'longitude'    => $this->mitraLng,
                    'last_seen_at' => now(),
                ]
            );
        }
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
                $latitude ? (float) $latitude : ($this->mitraLat ? (float) $this->mitraLat : null),
                $longitude ? (float) $longitude : ($this->mitraLng ? (float) $this->mitraLng : null)
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
        $user             = auth()->user();
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
                'helps'           => $emptyPaginator,
                'needsCity'       => false,
                'userDistrict'    => $user->district ?? null,
                'userCity'        => $user->city ?? null,
                'districtFilter'  => $this->districtFilter,
                'sortBy'          => $this->sortBy,
                'search'          => $this->search,
                'mitraLat'        => $this->mitraLat,
                'mitraLng'        => $this->mitraLng,
                'activeTask'      => null,
                'isShadowBanned'  => true,
                'countRadius10km' => 0,
                'countDistrict'   => 0,
                'countCity'       => 0,
            ]);
        }

        // Sapu bantuan expired yang belum dibatalkan
        app(\App\Services\HelpCancellationService::class)->sweepAndAutoCancelExpiredHelps();

        // Base Pool Query: Bantuan menunggu mitra yang terbuka untuk pool (belum kadaluwarsa)
        $basePoolQuery = Help::where('status', Help::STATUS_MENUNGGU_MITRA)
            ->where(function ($q) {
                $q->where('dispatch_mode', Help::DISPATCH_MODE_POOL)
                  ->orWhereNull('dispatch_mode');
            })
            ->whereNull('mitra_id')
            ->availableForMitra($user?->id)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });

        // Setup GPS parameters & Haversine SQL formula jika ada koordinat mitra
        $hasGps = ($this->mitraLat && $this->mitraLng);
        $haversineSql = null;
        $minLat = null;
        $maxLat = null;
        $minLng = null;
        $maxLng = null;

        if ($hasGps) {
            $lat = (float) $this->mitraLat;
            $lng = (float) $this->mitraLng;
            $filterRadius = $maxOperationalKm;

            // Bounding Box Pre-Filter (Index B-Tree)
            $latDelta = $filterRadius / 111.045;
            $lngDelta = $filterRadius / (111.045 * max(0.01, cos(deg2rad($lat))));

            $minLat = $lat - $latDelta;
            $maxLat = $lat + $latDelta;
            $minLng = $lng - $lngDelta;
            $maxLng = $lng + $lngDelta;

            // Formula Haversine SQL Presisi dengan koordinat titik awal sesuai jenis layanan (Output: distance_km)
            $initialLatSql = "CAST(COALESCE(helps.pickup_latitude, helps.latitude) AS REAL)";
            $initialLngSql = "CAST(COALESCE(helps.pickup_longitude, helps.longitude) AS REAL)";
            $haversineSql = "(6371 * acos(least(1.0, greatest(-1.0, cos(radians($lat)) * cos(radians($initialLatSql)) * cos(radians($initialLngSql) - radians($lng)) + sin(radians($lat)) * sin(radians($initialLatSql))))))";
        }

        // Hitung total order per tab untuk badge indikator
        $countRadius10km = 0;
        if ($hasGps) {
            $countRadius10km = (clone $basePoolQuery)->where(function ($q) use ($minLat, $maxLat, $minLng, $maxLng, $haversineSql, $initialLatSql, $initialLngSql, $maxOperationalKm, $user) {
                $q->where(function ($sub) use ($minLat, $maxLat, $minLng, $maxLng, $haversineSql, $initialLatSql, $initialLngSql, $maxOperationalKm) {
                    $sub->whereRaw("$initialLatSql BETWEEN $minLat AND $maxLat")
                        ->whereRaw("$initialLngSql BETWEEN $minLng AND $maxLng")
                        ->whereRaw("$haversineSql <= $maxOperationalKm");
                })->orWhere(function ($sub) use ($user) {
                    $sub->where(function($s) {
                        $s->whereNull('latitude')->orWhereNull('longitude');
                    });
                    if ($user && $user->district_id) {
                        $sub->where('district_id', $user->district_id);
                    }
                });
            })->count();
        } else {
            $countRadius10km = (clone $basePoolQuery)->count();
        }

        $countDistrict = ($user && $user->district_id)
            ? (clone $basePoolQuery)->where('district_id', $user->district_id)->count()
            : 0;

        $countCity = ($user && $user->city_id)
            ? (clone $basePoolQuery)->where('city_id', $user->city_id)->count()
            : 0;

        // Clone query untuk data yang akan ditampilkan
        $query = clone $basePoolQuery;

        // Jika GPS aktif, hitung distance_km untuk seluruh baris hasil
        if ($hasGps) {
            $query->select('helps.*')
                  ->selectRaw("$haversineSql AS distance_km");
        }

        // 1. FILTER BERDASARKAN TAB AKTIF:
        if ($this->districtFilter === 'all' || $this->districtFilter === 'radius_10km') {
            // TAB 1: Radius 10 KM dari tempat Mitra berdiri
            if ($hasGps) {
                $query->where(function ($q) use ($minLat, $maxLat, $minLng, $maxLng, $haversineSql, $initialLatSql, $initialLngSql, $maxOperationalKm, $user) {
                    $q->where(function ($sub) use ($minLat, $maxLat, $minLng, $maxLng, $haversineSql, $initialLatSql, $initialLngSql, $maxOperationalKm) {
                        $sub->whereRaw("$initialLatSql BETWEEN $minLat AND $maxLat")
                            ->whereRaw("$initialLngSql BETWEEN $minLng AND $maxLng")
                            ->whereRaw("$haversineSql <= $maxOperationalKm");
                    })->orWhere(function ($sub) use ($user) {
                        // Fallback untuk order legacy yang belum ada koordinat map
                        $sub->where(function($s) {
                            $s->whereNull('latitude')->orWhereNull('longitude');
                        });
                        if ($user && $user->district_id) {
                            $sub->where('district_id', $user->district_id);
                        }
                    });
                });
            } else {
                // Fallback jika GPS browser belum aktif: tampilkan order dalam kota mitra
                if ($user && !empty($user->city_id)) {
                    $query->where('city_id', $user->city_id);
                }
            }
        } elseif ($this->districtFilter === 'my_district' && $user && !empty($user->district_id)) {
            // TAB 2: SEMUA order dari kecamatan mitra (tanpa terpotong batas radius 10 KM)
            $query->where('district_id', $user->district_id);
        } elseif ($this->districtFilter === 'my_city' && $user && !empty($user->city_id)) {
            // TAB 3: SEMUA order dari kabupaten/kota mitra (tanpa terpotong batas radius 10 KM)
            $query->where('city_id', $user->city_id);
        } elseif (is_numeric($this->districtFilter) && (int) $this->districtFilter > 0) {
            $query->where('district_id', (int) $this->districtFilter);
        }

        // 2. SEARCH FILTER:
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', '%' . $this->search . '%'))
                  ->orWhereHas('district', fn($d) => $d->where('name', 'like', '%' . $this->search . '%'))
                  ->orWhereHas('city', fn($c) => $c->where('name', 'like', '%' . $this->search . '%'));
            });
        }

        // 3. DATABASE-LEVEL SORTING:
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
            'helps'           => $helps,
            'needsCity'       => false,
            'userDistrict'    => $user?->district ?? null,
            'userCity'        => $user?->city ?? null,
            'districtFilter'  => $this->districtFilter,
            'sortBy'          => $this->sortBy,
            'search'          => $this->search,
            'mitraLat'        => $this->mitraLat,
            'mitraLng'        => $this->mitraLng,
            'activeTask'      => $activeTask,
            'countRadius10km' => $countRadius10km,
            'countDistrict'   => $countDistrict,
            'countCity'       => $countCity,
        ]);
    }
}

