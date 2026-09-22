<?php

namespace App\Livewire\Mitra\Helps;

use App\Models\City;
use App\Models\District;
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
        'search'            => ['except' => ''],
        'districtFilter'    => ['except' => 'all'],
        'serviceTypeFilter' => ['except' => 'all'],
        'sortBy'            => ['except' => 'nearby'],
    ];

    public $search              = '';
    public $districtFilter      = 'all'; // 'all' (radius 10 km), 'my_district', 'my_city', or district_id
    public $serviceTypeFilter   = 'all'; // 'all', 'on_site_service', 'pickup_delivery'
    public $sortBy              = 'nearby'; // nearby, latest, oldest, price_high, price_low
    public $mitraLat            = null;
    public $mitraLng            = null;
    public ?int $currentCityId      = null;
    public ?string $currentCityName = null;
    public ?int $currentDistrictId  = null;
    public ?string $currentDistrictName = null;

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

        $this->resolveCurrentTerritory();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDistrictFilter()
    {
        $this->resetPage();
    }

    public function updatingServiceTypeFilter()
    {
        $this->resetPage();
    }

    public function setMitraLocation($lat, $lng, $cityName = null, $districtName = null)
    {
        $newLat = (float) $lat;
        $newLng = (float) $lng;

        // Cek apakah koordinat berpindah secara signifikan (> 0.001 derajat atau koordinat awal)
        $hasMovedSignificantly = (
            $this->mitraLat === null ||
            $this->mitraLng === null ||
            abs($this->mitraLat - $newLat) > 0.001 ||
            abs($this->mitraLng - $newLng) > 0.001
        );

        $this->mitraLat = $newLat;
        $this->mitraLng = $newLng;

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

        $territoryChanged = $this->resolveCurrentTerritory($cityName, $districtName);

        // Hanya reset paginasi jika ada pergerakan lokasi GPS signifikan atau wilayah kota/kecamatan berubah
        if ($hasMovedSignificantly || $territoryChanged) {
            $this->resetPage();
        }
    }

    /**
     * Resolusikan Kecamatan & Kota/Kabupaten secara dinamis dari titik koordinat GPS posisi saat ini.
     * Fallback ke data profil pengguna jika koordinat GPS belum tersedia.
     */
    public function resolveCurrentTerritory(?string $cityName = null, ?string $districtName = null): bool
    {
        $user = auth()->user();
        $oldCityId = $this->currentCityId;
        $oldDistrictId = $this->currentDistrictId;

        // 1. Jika ada koordinat GPS, temukan Kota terdekat berdasarkan koordinat
        if ($this->mitraLat && $this->mitraLng) {
            $lat = (float) $this->mitraLat;
            $lng = (float) $this->mitraLng;

            $matchedCity = null;

            // Jika ada nama kota dari reverse geocoding frontend
            if (!empty($cityName)) {
                $cleanCity = trim(preg_replace('/^(Kota\s+|Kabupaten\s+|Kab\.\s+|City of\s+|Regency\s+)/i', '', $cityName));
                $matchedCity = City::where('name', 'LIKE', '%' . $cleanCity . '%')
                    ->orWhere('name', 'LIKE', '%' . trim($cityName) . '%')
                    ->first();
            }

            // Temukan Kota terdekat dari koordinat
            if (!$matchedCity) {
                $matchedCity = City::select('*')
                    ->selectRaw("(6371 * acos(least(1.0, greatest(-1.0, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))))) AS dist", [$lat, $lng, $lat])
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->orderBy('dist')
                    ->first();
            }

            if ($matchedCity) {
                $this->currentCityId   = $matchedCity->id;
                $this->currentCityName = $matchedCity->name;

                // Cari Kecamatan pada Kota tersebut
                $matchedDistrict = null;
                if (!empty($districtName)) {
                    $cleanDistrict = trim(preg_replace('/^(Kecamatan\s+|Kec\.\s+|Kapanewon\s+|Kemantren\s+|District of\s+)/i', '', $districtName));
                    $matchedDistrict = District::where('city_id', $matchedCity->id)
                        ->where(function ($q) use ($cleanDistrict, $districtName) {
                            $q->where('name', 'LIKE', '%' . $cleanDistrict . '%')
                              ->orWhere('name', 'LIKE', '%' . trim($districtName) . '%');
                        })
                        ->first();
                }

                if (!$matchedDistrict) {
                    $matchedDistrict = District::where('city_id', $matchedCity->id)->first();
                }

                if ($matchedDistrict) {
                    $this->currentDistrictId   = $matchedDistrict->id;
                    $this->currentDistrictName = $matchedDistrict->name;
                }
            }
        }

        // 2. Fallback jika GPS belum ada / belum teresolusi: gunakan data profil pendaftaran
        if (!$this->currentCityId && $user) {
            $userCity = $user->city_id ? City::find($user->city_id) : null;
            $this->currentCityId   = $userCity?->id ?? $user->city_id;
            $this->currentCityName = $userCity?->name ?? $user->city_name ?? $user->city;

            $userDistrict = $user->district_id ? District::find($user->district_id) : null;
            $this->currentDistrictId   = $userDistrict?->id ?? $user->district_id;
            $this->currentDistrictName = $userDistrict?->name ?? $user->district ?? $user->kecamatan;
        }

        return ($oldCityId !== $this->currentCityId || $oldDistrictId !== $this->currentDistrictId);
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

        // Guard: Kelayakan kendaraan untuk layanan Antar & Jemput (pickup_delivery)
        if ($help->isPickup() && !$user->canTakePickupDelivery()) {
            if (!$user->hasVehicleProfile()) {
                session()->flash('error', 'Untuk mengambil pekerjaan Antar & Jemput, lengkapi Plat Nomor, SIM Motor, dan STNK pada profil Anda terlebih dahulu.');
            } elseif ($user->vehicle_verification_status === 'rejected') {
                session()->flash('error', 'Verifikasi data kendaraan Anda ditolak. Alasan: ' . ($user->vehicle_rejection_reason ?? '-') . '. Silakan perbarui dokumen di profil.');
            } else {
                session()->flash('error', 'Data kendaraan Anda sedang dalam proses verifikasi oleh Admin.');
            }
            return;
        }

        // Guard: Wilayah atau Kecamatan sedang dinonaktifkan
        if ($help->city && !$help->city->is_active) {
            session()->flash('error', 'Bantuan ini berada di wilayah yang sedang ditutup sementara dan tidak dapat diambil.');
            return;
        }

        if ($help->district && !$help->district->is_active) {
            session()->flash('error', 'Bantuan ini berada di kecamatan yang sedang ditutup sementara dan tidak dapat diambil.');
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
                'helps'               => $emptyPaginator,
                'needsCity'           => false,
                'userDistrict'        => $this->currentDistrictName,
                'userCity'            => $this->currentCityName,
                'userDistrictId'      => $this->currentDistrictId,
                'userCityId'          => $this->currentCityId,
                'districtFilter'      => $this->districtFilter,
                'sortBy'              => $this->sortBy,
                'search'              => $this->search,
                'mitraLat'            => $this->mitraLat,
                'mitraLng'            => $this->mitraLng,
                'activeTask'          => null,
                'isShadowBanned'      => true,
                'countRadius10km'     => 0,
                'countDistrict'       => 0,
                'countCity'           => 0,
            ]);
        }

        // Sapu bantuan expired yang belum dibatalkan & pulihkan tugas seeking stranded
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
            })
            // Sembunyikan tugas di kota atau kecamatan yang sedang dinonaktifkan
            ->whereHas('city', fn($c) => $c->where('is_active', true))
            ->where(function ($q) {
                $q->whereNull('district_id')
                  ->orWhereHas('district', fn($d) => $d->where('is_active', true));
            });

        // Sembunyikan tugas jenis Antar & Jemput (pickup_delivery) jika mitra belum melengkapi
        // dan belum terverifikasi SIM/STNK kendaraannya oleh Admin
        if ($user && !$user->canTakePickupDelivery()) {
            $basePoolQuery->where('service_type', '!=', Help::SERVICE_TYPE_PICKUP_DELIVERY);
            if ($this->serviceTypeFilter === Help::SERVICE_TYPE_PICKUP_DELIVERY) {
                $this->serviceTypeFilter = 'all';
            }
        }

        // Filter eksplisit preferensi jenis layanan yang dipilih mitra
        if ($this->serviceTypeFilter === Help::SERVICE_TYPE_ON_SITE) {
            $basePoolQuery->where('service_type', Help::SERVICE_TYPE_ON_SITE);
        } elseif ($this->serviceTypeFilter === Help::SERVICE_TYPE_PICKUP_DELIVERY && $user?->canTakePickupDelivery()) {
            $basePoolQuery->where('service_type', Help::SERVICE_TYPE_PICKUP_DELIVERY);
        }

        // Setup GPS parameters & Haversine SQL formula jika ada koordinat mitra
        $hasGps = ($this->mitraLat && $this->mitraLng);
        $haversineSql = null;
        $minLat = null;
        $maxLat = null;
        $minLng = null;
        $maxLng = null;
        $initialLatSql = "CAST(COALESCE(helps.pickup_latitude, helps.latitude) AS REAL)";
        $initialLngSql = "CAST(COALESCE(helps.pickup_longitude, helps.longitude) AS REAL)";

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
                    if ($this->currentDistrictId) {
                        $sub->where('district_id', $this->currentDistrictId);
                    } elseif ($user && $user->district_id) {
                        $sub->where('district_id', $user->district_id);
                    }
                });
            })->count();
        } else {
            $countRadius10km = (clone $basePoolQuery)
                ->when($this->currentCityId, fn($q, $cId) => $q->where('city_id', $cId))
                ->count();
        }

        $countDistrict = $this->currentDistrictId
            ? (clone $basePoolQuery)->where('district_id', $this->currentDistrictId)->count()
            : 0;

        $countCity = $this->currentCityId
            ? (clone $basePoolQuery)->where('city_id', $this->currentCityId)->count()
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
                        if ($this->currentDistrictId) {
                            $sub->where('district_id', $this->currentDistrictId);
                        } elseif ($user && $user->district_id) {
                            $sub->where('district_id', $user->district_id);
                        }
                    });
                });
            } else {
                // Fallback jika GPS browser belum aktif: tampilkan order dalam kota saat ini
                if (!empty($this->currentCityId)) {
                    $query->where('city_id', $this->currentCityId);
                }
            }
        } elseif ($this->districtFilter === 'my_district' && !empty($this->currentDistrictId)) {
            // TAB 2: SEMUA order dari kecamatan mitra saat ini (tanpa terpotong batas radius 10 KM)
            $query->where('district_id', $this->currentDistrictId);
        } elseif ($this->districtFilter === 'my_city' && !empty($this->currentCityId)) {
            // TAB 3: SEMUA order dari kabupaten/kota mitra saat ini (tanpa terpotong batas radius 10 KM)
            $query->where('city_id', $this->currentCityId);
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
                : ($this->currentDistrictId ? $query->orderByRaw("(district_id = ?) DESC", [$this->currentDistrictId])->latest() : $query->latest()),
            'latest'     => $query->latest(),
            'oldest'     => $query->oldest(),
            'price_high' => $query->orderByDesc('amount'),
            'price_low'  => $query->orderBy('amount'),
            default      => $hasGps 
                ? $query->orderByRaw("CASE WHEN latitude IS NOT NULL AND longitude IS NOT NULL THEN $haversineSql ELSE 99999 END ASC")
                : $query->latest(),
        };

        // Native Database Pagination (Reuse pre-calculated active tab count to avoid duplicate count query)
        $perPage = 15;
        $page    = $this->getPage();

        $totalCount = null;
        if (empty($this->search)) {
            $totalCount = match ($this->districtFilter) {
                'my_district'        => $countDistrict,
                'my_city'            => $countCity,
                'all', 'radius_10km' => $countRadius10km,
                default              => null,
            };
        }

        if ($totalCount !== null) {
            $items = $query->with(['user', 'city', 'district'])
                ->forPage($page, $perPage)
                ->get();

            $helps = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $totalCount,
                $perPage,
                $page,
                [
                    'path'     => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
        } else {
            $helps = $query->with(['user', 'city', 'district'])->paginate($perPage);
        }

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
            'helps'               => $helps,
            'needsCity'           => false,
            'userDistrict'        => $this->currentDistrictName,
            'userCity'            => $this->currentCityName,
            'userDistrictId'      => $this->currentDistrictId,
            'userCityId'          => $this->currentCityId,
            'districtFilter'      => $this->districtFilter,
            'sortBy'              => $this->sortBy,
            'search'              => $this->search,
            'mitraLat'            => $this->mitraLat,
            'mitraLng'            => $this->mitraLng,
            'activeTask'          => $activeTask,
            'countRadius10km'     => $countRadius10km,
            'countDistrict'       => $countDistrict,
            'countCity'           => $countCity,
        ]);
    }
}

