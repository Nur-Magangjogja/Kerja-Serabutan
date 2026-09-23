<?php

namespace App\Livewire\Customer\Helps;

use App\Models\AppSetting;
use App\Models\City;
use App\Models\Help;
use App\Models\UserBalance;
use App\Services\HelpTransactionService;
use App\Services\CitySearchService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    // ─── Form fields (Revisi 3) ──────────────────────────────────────────────
    public $service_type       = 'on_site_service'; // 'on_site_service', 'pickup_delivery'
    public $order_mode         = 'instant'; // 'instant', 'scheduled'
    public $service_category   = 'general';
    public $service_duration_hours = 1.0;
    public $title              = '';
    public $description        = '';
    public $equipment_provided = '';
    public $amount             = '';
    public $material_fee       = 0;
    public $item_fund          = 0;
    public $item_fund_mode     = 'customer_paid_in_app'; // 'customer_paid_in_app', 'partner_advance', 'cash_on_delivery'
    public $customer_reimbursement_method = 'cash'; // 'cash', 'wallet'
    public $advance_limit      = 100000;
    public $minHelpNominal     = 10000;
    public $city_id            = '';
    public $cityQuery          = '';
    public $district_id        = '';
    public $districtQuery      = '';
    public $districtsList      = [];
    public $searchResults      = [];
    public $location           = '';
    public $full_address       = '';
    public $latitude           = null;
    public $longitude          = null;
    public $photo;

    // ─── Multi-Point Locations (Pickup / Delivery / Store) ───────────────────
    public $pickup_address     = '';
    public $pickup_latitude    = null;
    public $pickup_longitude   = null;
    public $delivery_address   = '';
    public $delivery_latitude  = null;
    public $delivery_longitude = null;
    public $store_name         = '';
    public $store_address      = '';
    public $store_latitude     = null;
    public $store_longitude    = null;
    public $route_distance_km  = 0.0;
    public $item_count         = 1;
    public $shopping_list      = '';

    // ─── Req province/regency/district selectors ─────────────────────────────
    public $req_province_id = '';
    public $req_regency_id  = '';
    public $req_district_id = '';
    public $req_provinces   = [];
    public $req_regencies   = [];
    public $req_districts   = [];

    // ─── Scheduling & Radar Pool Range ──────────────────────────────────────
    public $scheduled_date          = null;
    public $scheduled_time          = null;
    public $publish_mode            = 'now'; // 'now', 'custom'
    public $publish_date            = null;
    public $publish_time            = null;
    public $early_departure_minutes = 60; // Jeda keberangkatan mitra: 30, 45, 60, 90, 120 menit
    public $timezoneLabel           = 'WIB';
    public $timezoneIana            = 'Asia/Jakarta';

    // ─── Customer Saved Landmarks (Patokan Tempat dari Profil) ───────────────
    public array $savedLandmarks = [];
    public bool $showSaveLandmarkModal = false;
    public string $newLandmarkLabel = '';

    // ─── Batas Waktu Kadaluwarsa / Auto-Cancel ──────────────────────────────
    public $expiry_option      = '24_hours'; // '1_hour', '3_hours', '6_hours', '12_hours', '24_hours', '2_days', '3_days', 'custom'
    public $custom_expiry_date = null;
    public $custom_expiry_time = null;

    // ─── Confirm modal (model v3: breakdown biaya lengkap) ───────────────────
    public $showConfirmModal      = false;
    public $confirmAmount         = 0;
    public $confirmServiceFee     = 0;
    public $confirmTravelFee      = 0;
    public $confirmMaterialFee    = 0;
    public $confirmItemFund       = 0;
    public $confirmAdminFee       = 0;
    public $confirmTotal          = 0;
    public $confirmScheduled      = null;
    public $confirmExpiresAt      = null;
    public $confirmCommissionRate = 0;
    public $confirmPlatformFee    = 0;
    public $confirmFeeType        = 'fixed';
    public $confirmFeeLabel       = 'Rp 2.000';
    public $confirmMitraEarning   = 0;
    public $confirmMinServiceFee  = 0;
    public bool $isDraftRestored  = false;

    protected $listeners = [
        'citySelected'            => 'setCityId',
        'setPickupLocation'       => 'resolvePickupLocation',
        'setDeliveryLocation'     => 'resolveDeliveryLocation',
        'setStoreLocation'        => 'resolveStoreLocation',
        'calculateRouteDistance'  => 'calculateRouteDistance',
        'clearLocationPoint'      => 'clearLocationPoint',
        'discardDraft'            => 'discardDraft',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // LIFECYCLE
    // ─────────────────────────────────────────────────────────────────────────

    public function mount()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->isCustomer()) {
            abort(403, 'Akses ditolak. Hanya akun Customer yang dapat membuat permintaan bantuan.');
        }

        if (auth()->user()->isShadowBanned() || (int) auth()->user()->warning_level >= 3) {
            session()->flash('error', 'Akun Anda saat ini dibatasi dari membuat pekerjaan bantuan baru karena dalam status peninjauan moderasi / sanksi SP 3.');
        }

        $this->minHelpNominal = (int) AppSetting::get('min_help_nominal', 10000);

        // Set default nominal yang wajar
        if (empty($this->amount) || $this->amount < $this->minHelpNominal) {
            $this->amount = $this->minHelpNominal;
        }

        // Muat patokan tempat yang tersimpan di profil customer
        if (auth()->check()) {
            $this->savedLandmarks = auth()->user()->getSavedLandmarksList();
        }

        if (Schema::hasTable('req_provinces')) {
            $this->req_provinces = DB::table('req_provinces')->orderBy('province')->get()->toArray();
        }

        // Cek apakah ada cookie penyimpanan sementara bantuan (TTL 15 detik)
        if (request()->hasCookie('sb_help_draft')) {
            try {
                $rawCookie = request()->cookie('sb_help_draft');
                $parsed = json_decode($rawCookie, true);
                if (is_array($parsed) && isset($parsed['expires_at']) && $parsed['expires_at'] > time()) {
                    if (isset($parsed['data']) && is_array($parsed['data'])) {
                        $this->restoreDraft($parsed['data']);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore parsing failure
            }
        }
    }

    /**
     * Pilih & terapkan patokan tersimpan dari profil customer.
     */
    public function applySavedLandmark(string $patokan): void
    {
        $this->full_address = $patokan;
    }

    /**
     * Simpan teks patokan saat ini ke profil customer secara instan.
     */
    public function saveCurrentPatokanToProfile(): void
    {
        if (empty(trim((string)$this->full_address))) {
            $this->addError('full_address', 'Tuliskan detail patokan terlebih dahulu sebelum disimpan.');
            return;
        }

        $label = trim($this->newLandmarkLabel);
        if (empty($label)) {
            $label = 'Patokan ' . (count($this->savedLandmarks) + 1);
        }

        $user = auth()->user();
        if ($user) {
            $user->addSavedLandmark($label, $this->full_address);
            $this->savedLandmarks = $user->getSavedLandmarksList();
            $this->newLandmarkLabel = '';
            $this->showSaveLandmarkModal = false;
            session()->flash('landmark_saved', 'Patokan "' . $label . '" berhasil disimpan ke profil!');
        }
    }

    /**
     * Tambah/kurang nominal bantuan secara instan dan user friendly.
     */
    public function adjustAmount(int $delta): void
    {
        $min = (int) ($this->minHelpNominal ?: AppSetting::get('min_help_nominal', 10000));
        if ($this->service_type === Help::SERVICE_TYPE_PICKUP_DELIVERY) {
            $maxDistanceKm = AppSetting::getPickupDeliveryMaxDistanceKm();
            if ((float) $this->route_distance_km > $maxDistanceKm) {
                $min = 0;
            } else {
                try {
                    $min = (int) app(\App\Services\HelpPricingService::class)->calculatePickupDeliveryFare((float) $this->route_distance_km);
                } catch (\Throwable $e) {
                    $min = 0;
                }
            }
        }
        $current = (int) ($this->amount ?: 0);
        $new = max($min, min(100000000, $current + $delta));
        $this->amount = $new;
    }

    /**
     * Set nominal langsung dari pilihan cepat.
     */
    public function setPresetAmount(int $value): void
    {
        $min = (int) ($this->minHelpNominal ?: AppSetting::get('min_help_nominal', 10000));
        if ($this->service_type === Help::SERVICE_TYPE_PICKUP_DELIVERY) {
            $maxDistanceKm = AppSetting::getPickupDeliveryMaxDistanceKm();
            if ((float) $this->route_distance_km > $maxDistanceKm) {
                $min = 0;
            } else {
                try {
                    $min = (int) app(\App\Services\HelpPricingService::class)->calculatePickupDeliveryFare((float) $this->route_distance_km);
                } catch (\Throwable $e) {
                    $min = 0;
                }
            }
        }
        $this->amount = max($min, min(100000000, $value));
    }

    /**
     * Hapus pilihan jadwal bantuan.
     */
    public function clearSchedule(): void
    {
        $this->scheduled_date          = null;
        $this->scheduled_time          = null;
        $this->publish_mode            = 'now';
        $this->publish_date            = null;
        $this->publish_time            = null;
        $this->early_departure_minutes = 60;
    }

    /**
     * Set pilihan jadwal cepat.
     */
    public function setPresetSchedule(string $preset): void
    {
        if ($preset === 'plus_2h') {
            $t = now()->addHours(2);
            $this->scheduled_date = $t->format('Y-m-d');
            $this->scheduled_time = $t->format('H:i');
        } elseif ($preset === 'tomorrow_morning') {
            $t = now()->addDay()->setTime(8, 0);
            $this->scheduled_date = $t->format('Y-m-d');
            $this->scheduled_time = '08:00';
        } elseif ($preset === 'tomorrow_afternoon') {
            $t = now()->addDay()->setTime(13, 0);
            $this->scheduled_date = $t->format('Y-m-d');
            $this->scheduled_time = '13:00';
        }
        if ($this->publish_mode === 'custom') {
            $this->publish_date = $this->scheduled_date;
        }
    }

    /**
     * Hook saat scheduled_date diupdate.
     * Otomatis sinkronkan publish_date jika publish_mode custom.
     */
    public function updatedScheduledDate($value): void
    {
        if (!empty($value) && $this->publish_mode === 'custom') {
            $this->publish_date = $value;
        }
    }

    /**
     * Hook saat scheduled_time diupdate.
     * Otomatis set tanggal ke hari ini jika tanggal masih kosong.
     */
    public function updatedScheduledTime($value): void
    {
        if (!empty($value) && empty($this->scheduled_date)) {
            $this->scheduled_date = now()->format('Y-m-d');
        }
    }

    /**
     * Hook saat publish_time diupdate.
     * Otomatis set tanggal ke hari ini jika tanggal masih kosong dan switch mode ke custom.
     */
    public function updatedPublishTime($value): void
    {
        if (!empty($value) && empty($this->scheduled_date)) {
            $this->scheduled_date = now()->format('Y-m-d');
        }
        if (!empty($value)) {
            $this->publish_mode = 'custom';
            $this->publish_date = $this->scheduled_date ?: now()->format('Y-m-d');
        }
    }

    /**
     * Hook saat custom_expiry_time diupdate.
     * Otomatis set tanggal kadaluwarsa kustom ke hari ini jika masih kosong.
     */
    public function updatedCustomExpiryTime($value): void
    {
        if (!empty($value) && empty($this->custom_expiry_date)) {
            $this->custom_expiry_date = now()->format('Y-m-d');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CITY SEARCH — didelegasikan ke CitySearchService
    // ─────────────────────────────────────────────────────────────────────────

    public function updatedCityQuery($value)
    {
        $this->city_id = '';
        if (trim($value) === '') {
            $this->searchResults = [];
            return;
        }
        $this->searchResults = app(CitySearchService::class)->search($value, 10);
    }

    public function setCityId($id, $name = null, $province = null)
    {
        $city = City::find($id);
        if ($city && !$city->is_active) {
            $this->city_id       = '';
            $this->cityQuery     = '';
            $this->district_id   = '';
            $this->districtQuery = '';
            $this->districtsList = [];
            $this->searchResults = [];
            $this->addError('city_id', 'Wilayah "' . $city->name . '" sedang ditutup sementara dan tidak menerima permintaan bantuan baru.');
            return;
        }

        $this->city_id = $id;
        if ($city) {
            // Cari display dari searchResults (bisa lebih kaya, mencakup kecamatan)
            $usedDisplay = false;
            foreach ($this->searchResults as $res) {
                if (isset($res['id']) && $res['id'] == $id && !empty($res['display'])) {
                    $this->cityQuery = $res['display'];
                    $usedDisplay     = true;
                    break;
                }
            }
            if (!$usedDisplay) {
                $this->cityQuery = $city->name . ($city->province ? ', ' . $city->province : '');
            }

            // Muat daftar kecamatan untuk kota ini
            $this->districtsList = app(CitySearchService::class)->getDistrictsByCity((int) $id);

            // Jika district_id sebelumnya tidak ada di kota baru ini, reset
            if ($this->district_id) {
                $existsInNewCity = collect($this->districtsList)->contains('id', (int) $this->district_id);
                if (!$existsInNewCity) {
                    $this->district_id   = '';
                    $this->districtQuery = '';
                }
            }

            $zone                = $this->computeTimezoneLabelFromCity($city);
            $iana                = $this->ianaForZone($zone);
            $this->timezoneLabel = $zone;
            $this->timezoneIana  = $iana;
            $this->dispatch('help:timezone-changed', zone: $zone, iana: $iana);
            $this->dispatch('city-selected', cityName: $city->name, province: $city->province);
        }
        $this->searchResults = [];
    }

    public function clearCity()
    {
        $this->city_id       = '';
        $this->cityQuery     = '';
        $this->district_id   = '';
        $this->districtQuery = '';
        $this->districtsList = [];
        $this->searchResults = [];
    }

    public function setDistrictId($id, $name = null)
    {
        $this->district_id = (int) $id;
        if ($name) {
            $this->districtQuery = $name;
        } else {
            $dist = \App\Models\District::find($id);
            if ($dist) {
                $this->districtQuery = $dist->name;
                if (!$this->city_id && $dist->city_id) {
                    $this->setCityId($dist->city_id);
                }
            }
        }
    }

    public function clearDistrict()
    {
        $this->district_id   = '';
        $this->districtQuery = '';
    }

    /**
     * Sinkronisasi Titik Lokasi Peta (Map Coordinates as Single Source of Truth).
     * Otomatis mencocokkan Kabupaten/Kota & Kecamatan dari koordinat GPS/Peta,
     * BUKAN mengambil dari profil akun pengguna, agar lokasi kerja akurat untuk Rekan Jasa.
     */
    public function resolveLocationFromMap($lat, $lng, $cityName = null, $districtName = null, $fullAddress = null, $provinceName = null)
    {
        $geoService = app(\App\Services\GeoService::class);
        $safety = $geoService->validateLocationSafety((float) $lat, (float) $lng);
        if (!$safety['is_safe']) {
            $this->latitude  = null;
            $this->longitude = null;
            $this->location  = '';
            $this->addError('latitude', $safety['reason'] ?? 'Titik lokasi berada di area terlarang / tidak dapat diakses.');
            $this->dispatch('restricted-location-detected', [
                'reason' => $safety['reason'] ?? 'Titik lokasi berada di area terlarang / tidak dapat diakses.',
                'point'  => 'onsite'
            ]);
            return;
        }

        $this->latitude  = (float) $lat;
        $this->longitude = (float) $lng;
        $this->resetErrorBag(['latitude', 'longitude', 'location']);

        if ($fullAddress) {
            $this->location = $fullAddress;
        }

        // 1. Bersihkan String Kota & Kabupaten
        $cleanCity = trim($cityName ?? '');
        $cleanCity = preg_replace('/^(Kota\s+|Kabupaten\s+|Kab\.\s+|City of\s+|Regency\s+)/i', '', $cleanCity);
        $cleanCity = trim($cleanCity);

        $matchedCity = null;
        if (!empty($cleanCity)) {
            $matchedCity = City::where('name', 'LIKE', '%' . $cleanCity . '%')
                ->orWhere('name', 'LIKE', '%' . trim($cityName ?? '') . '%')
                ->first();
        }

        // Fallback pencarian kota berdasarkan kedekatan koordinat jika nama tidak cocok atau null
        if (!$matchedCity && $this->latitude && $this->longitude) {
            $matchedCity = City::select('*')
                ->selectRaw("(6371 * acos(least(1.0, greatest(-1.0, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))))) AS dist", [$this->latitude, $this->longitude, $this->latitude])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('dist')
                ->first();
        }

        if ($matchedCity) {
            // Guard Wilayah Nonaktif
            if (!$matchedCity->is_active) {
                $this->latitude      = null;
                $this->longitude     = null;
                $this->location      = '';
                $this->city_id       = '';
                $this->cityQuery     = '';
                $this->district_id   = '';
                $this->districtQuery = '';
                $this->districtsList = [];
                $errorMsg = 'Wilayah "' . $matchedCity->name . '" sedang ditutup sementara demi keamanan / penataan operasional dan tidak menerima permintaan bantuan baru.';
                $this->addError('latitude', $errorMsg);
                $this->addError('city_id', $errorMsg);
                $this->dispatch('restricted-location-detected', [
                    'reason' => $errorMsg,
                    'point'  => 'onsite'
                ]);
                return;
            }

            $this->city_id       = $matchedCity->id;
            $this->cityQuery     = $matchedCity->name;
            $this->districtsList = app(CitySearchService::class)->getDistrictsByCity((int) $matchedCity->id);

            // SELALU RESET district_id saat berpindah/memilih titik peta baru (mencegah membawa profil lama)
            $this->district_id   = '';
            $this->districtQuery = '';

            // Update Timezone
            $zone = $this->computeTimezoneLabelFromCity($matchedCity);
            $iana = $this->ianaForZone($zone);
            $this->timezoneLabel = $zone;
            $this->timezoneIana  = $iana;
            $this->dispatch('help:timezone-changed', zone: $zone, iana: $iana);

            // 2. Bersihkan String Kecamatan
            $cleanDistrict = trim($districtName ?? '');
            $cleanDistrict = preg_replace('/^(Kecamatan\s+|Kec\.\s+|Kapanewon\s+|Kemantren\s+|District of\s+)/i', '', $cleanDistrict);
            $cleanDistrict = trim($cleanDistrict);

            $matchedDistrict = null;

            // Strategi A: Cocokkan langsung nama kecamatan pada kota tersebut
            if (!empty($cleanDistrict)) {
                $matchedDistrict = \App\Models\District::where('city_id', $matchedCity->id)
                    ->where(function ($q) use ($cleanDistrict, $districtName) {
                        $q->where('name', 'LIKE', '%' . $cleanDistrict . '%')
                          ->orWhere('name', 'LIKE', '%' . trim($districtName ?? '') . '%');
                    })
                    ->first();
            }

            // Strategi B: Jika belum cocok, cari apakah ada nama kecamatan resmi di kota ini yang terkandung di fullAddress
            if (!$matchedDistrict && !empty($fullAddress)) {
                $existingDistricts = \App\Models\District::where('city_id', $matchedCity->id)->get();
                foreach ($existingDistricts as $dist) {
                    if (stripos($fullAddress, $dist->name) !== false) {
                        $matchedDistrict = $dist;
                        break;
                    }
                }
            }

            // Strategi C: Jika belum ada di tabel districts kota ini dan $cleanDistrict valid, auto-create
            if (!$matchedDistrict && !empty($cleanDistrict) && strlen($cleanDistrict) >= 3) {
                $matchedDistrict = \App\Models\District::firstOrCreate(
                    ['city_id' => $matchedCity->id, 'name' => ucwords(strtolower($cleanDistrict))],
                    ['is_active' => true]
                );
                $this->districtsList = app(CitySearchService::class)->getDistrictsByCity((int) $matchedCity->id);
            }

            // Strategi D: Fallback ke kecamatan pertama yang terdaftar dan aktif di kota tersebut jika ada
            if (!$matchedDistrict) {
                $matchedDistrict = \App\Models\District::where('city_id', $matchedCity->id)
                    ->where('is_active', true)
                    ->first();
            }

            if ($matchedDistrict) {
                if (!$matchedDistrict->is_active) {
                    $this->district_id   = '';
                    $this->districtQuery = '';
                    $this->addError('district_id', 'Kecamatan "' . $matchedDistrict->name . '" sedang ditutup sementara dan tidak menerima permintaan bantuan baru.');
                } else {
                    $this->district_id   = $matchedDistrict->id;
                    $this->districtQuery = $matchedDistrict->name;
                }
            }

            if (empty($this->location)) {
                $this->location = ($this->districtQuery ? 'Kec. ' . $this->districtQuery . ', ' : '') . $matchedCity->name;
            }
        }

        $this->dispatch('map-location-resolved', [
            'cityName'     => $this->cityQuery ?: ($cityName ?? '-'),
            'districtName' => $this->districtQuery ?: ($districtName ?? '-'),
            'lat'          => $this->latitude,
            'lng'          => $this->longitude,
        ]);
    }

    /**
     * Sinkronisasi Tunggal Atomik untuk Kerja Serabutan (On-Site).
     * Mencegah multiple concurrent AJAX race conditions.
     */
    public function syncOnSiteLocation($lat, $lng, $fullAddress = null, $cityName = null, $districtName = null, $provinceName = null): void
    {
        $this->resolveLocationFromMap($lat, $lng, $cityName, $districtName, $fullAddress, $provinceName);
    }

    /**
     * Sinkronisasi Tunggal Atomik untuk Titik 1 (Jemput / Pickup).
     */
    public function syncPickupLocation($lat, $lng, $fullAddress = null, $cityName = null, $districtName = null, $provinceName = null): void
    {
        $geoService = app(\App\Services\GeoService::class);
        $safety = $geoService->validateLocationSafety((float) $lat, (float) $lng);
        if (!$safety['is_safe']) {
            $this->pickup_latitude  = null;
            $this->pickup_longitude = null;
            $this->pickup_address   = '';
            $this->addError('pickup_address', $safety['reason'] ?? 'Titik 1 (Jemput) berada di area terlarang / perairan.');
            $this->dispatch('restricted-location-detected', [
                'reason' => $safety['reason'] ?? 'Titik 1 (Jemput) berada di area terlarang / perairan.',
                'point'  => 'pickup'
            ]);
            return;
        }

        $this->pickup_latitude  = (float) $lat;
        $this->pickup_longitude = (float) $lng;
        $this->latitude         = (float) $lat;
        $this->longitude        = (float) $lng;
        $this->resetErrorBag(['pickup_address', 'pickup_latitude', 'pickup_longitude']);

        if ($fullAddress) {
            $this->pickup_address = $fullAddress;
            $this->location       = $fullAddress;
        }

        $this->resolveLocationFromMap($lat, $lng, $cityName, $districtName, $fullAddress ?: $this->pickup_address, $provinceName);

        if ($this->latitude === null) {
            $this->pickup_latitude  = null;
            $this->pickup_longitude = null;
            $this->pickup_address   = '';
            $this->location         = '';
            return;
        }

        if (empty($this->pickup_address)) {
            $this->pickup_address = ($this->districtQuery ? 'Kec. ' . $this->districtQuery . ', ' : '') . ($this->cityQuery ?: sprintf('Titik Jemput (%.4f, %.4f)', (float)$lat, (float)$lng));
            $this->location       = $this->pickup_address;
        }

        $this->calculateRouteDistance();
    }

    /**
     * Sinkronisasi Tunggal Atomik untuk Titik 2 (Antar / Delivery).
     */
    public function syncDeliveryLocation($lat, $lng, $fullAddress = null): void
    {
        $geoService = app(\App\Services\GeoService::class);
        $safety = $geoService->validateLocationSafety((float) $lat, (float) $lng);
        if (!$safety['is_safe']) {
            $this->delivery_latitude  = null;
            $this->delivery_longitude = null;
            $this->delivery_address   = '';
            $this->addError('delivery_address', $safety['reason'] ?? 'Titik 2 (Antar) berada di area terlarang / perairan.');
            $this->dispatch('restricted-location-detected', [
                'reason' => $safety['reason'] ?? 'Titik 2 (Antar) berada di area terlarang / perairan.',
                'point'  => 'delivery'
            ]);
            return;
        }

        $this->delivery_latitude  = (float) $lat;
        $this->delivery_longitude = (float) $lng;
        $this->resetErrorBag(['delivery_address', 'delivery_latitude', 'delivery_longitude']);

        if ($fullAddress) {
            $this->delivery_address = $fullAddress;
        }

        if (empty($this->delivery_address)) {
            $this->delivery_address = sprintf('Titik Antar (%.4f, %.4f)', (float) $lat, (float) $lng);
        }

        $this->calculateRouteDistance();
    }

    /**
     * Hapus / Clear titik lokasi tertentu secara atomik (dari tombol X atau reset peta).
     */
    public function clearLocationPoint(string $point = 'onsite'): void
    {
        if ($point === 'pickup') {
            $this->pickup_latitude    = null;
            $this->pickup_longitude   = null;
            $this->pickup_address     = '';
            $this->pickup_district_id = null;
            $this->latitude           = null;
            $this->longitude          = null;
            $this->route_distance_km  = 0.0;
            $this->amount             = $this->minHelpNominal;
            $this->resetErrorBag(['pickup_address', 'delivery_address', 'route_distance_km']);
            $this->dispatch('max-distance-cleared');
        } elseif ($point === 'delivery') {
            $this->delivery_latitude    = null;
            $this->delivery_longitude   = null;
            $this->delivery_address     = '';
            $this->delivery_district_id = null;
            $this->route_distance_km    = 0.0;
            $this->amount               = $this->minHelpNominal;
            $this->resetErrorBag(['delivery_address', 'route_distance_km']);
            $this->dispatch('max-distance-cleared');
        } else {
            $this->latitude      = null;
            $this->longitude     = null;
            $this->location      = '';
            $this->full_address  = '';
            $this->district_id   = '';
            $this->districtQuery = '';
            $this->resetErrorBag(['location', 'latitude', 'longitude', 'route_distance_km']);
            $this->dispatch('max-distance-cleared');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REQ DISTRICT SELECTORS
    // ─────────────────────────────────────────────────────────────────────────

    public function updatedReqProvinceId($value)
    {
        if (!Schema::hasTable('req_regencies') || empty($value)) {
            $this->req_regencies   = [];
            $this->req_regency_id  = '';
            $this->req_districts   = [];
            $this->req_district_id = '';
            return;
        }
        $this->req_regencies = DB::table('req_regencies')
            ->where('province_id', $value)
            ->orderBy('regency')
            ->get()
            ->toArray();
        $this->req_regency_id  = '';
        $this->req_districts   = [];
        $this->req_district_id = '';
    }

    public function updatedReqRegencyId($value)
    {
        if (!Schema::hasTable('req_districts') || empty($value)) {
            $this->req_districts   = [];
            $this->req_district_id = '';
            return;
        }
        $this->req_districts = DB::table('req_districts')
            ->where('regency_id', $value)
            ->orderBy('district')
            ->get()
            ->toArray();
        $this->req_district_id = '';
    }

    public function selectReqDistrict($districtId)
    {
        if (!Schema::hasTable('req_districts') || !Schema::hasTable('req_regencies') || !Schema::hasTable('req_provinces')) {
            return;
        }

        $row = DB::table('req_districts')
            ->join('req_regencies', 'req_districts.regency_id', '=', 'req_regencies.id')
            ->join('req_provinces', 'req_regencies.province_id', '=', 'req_provinces.id')
            ->where('req_districts.id', $districtId)
            ->select('req_districts.id as district_id', 'req_districts.district',
                     'req_regencies.id as regency_id', 'req_regencies.regency', 'req_provinces.province')
            ->first();

        if (!$row) return;

        $city = City::firstOrCreate(
            ['code' => 'reqr-' . $row->regency_id],
            ['name' => $row->regency, 'province' => $row->province, 'type' => null, 'is_active' => true]
        );

        $this->city_id         = $city->id;
        $this->cityQuery       = $row->district . ', ' . $row->regency . ', ' . $row->province;
        $this->searchResults   = [];
        $this->req_district_id = $row->district_id;

        $districtModel = \App\Models\District::where('name', $row->district)
            ->where('city_id', $city->id)
            ->first();
        if (!$districtModel) {
            $districtModel = \App\Models\District::firstOrCreate(
                ['code' => 'reqd-' . $row->district_id],
                ['city_id' => $city->id, 'name' => $row->district, 'is_active' => true]
            );
        }
        if ($districtModel) {
            $this->district_id   = $districtModel->id;
            $this->districtQuery = $districtModel->name;
        }
        $this->districtsList = app(CitySearchService::class)->getDistrictsByCity((int) $city->id);

        $zone             = $this->computeTimezoneLabelFromCity($city);
        $iana             = $this->ianaForZone($zone);
        $this->timezoneLabel = $zone;
        $this->timezoneIana  = $iana;
        $this->dispatch('help:timezone-changed', zone: $zone, iana: $iana);

        $reg = DB::table('req_regencies')->where('regency', $row->regency)->first();
        if ($reg) {
            $this->req_regency_id  = $reg->id;
            $this->req_regencies   = DB::table('req_regencies')->where('province_id', $reg->province_id)->orderBy('regency')->get()->toArray();
            $this->req_province_id = $reg->province_id;
            $this->req_provinces   = DB::table('req_provinces')->orderBy('province')->get()->toArray();
            $this->req_districts   = DB::table('req_districts')->where('regency_id', $reg->id)->orderBy('district')->get()->toArray();
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BATAS WAKTU PENCARIAN REKAN JASA / KADALUWARSA OTOMATIS
    // ─────────────────────────────────────────────────────────────────────────

    public function setExpiryOption(string $option): void
    {
        $this->expiry_option = $option;
        if ($option === 'custom') {
            if (!$this->custom_expiry_date) {
                $this->custom_expiry_date = $this->scheduled_date ?: Carbon::now()->format('Y-m-d');
            }
            if (!$this->custom_expiry_time) {
                $this->custom_expiry_time = $this->scheduled_time ?: Carbon::now()->format('H:i');
            }
        }
    }

    public function computeBaseStartTime(): Carbon
    {
        $now = Carbon::now();
        if ($this->scheduled_date) {
            $dateStr = trim($this->scheduled_date);
            $timeStr = $this->scheduled_time ? trim($this->scheduled_time) : null;
            if ($timeStr) {
                try {
                    $dt = Carbon::parse($dateStr . ' ' . $timeStr);
                    return $dt->isPast() ? $now : $dt;
                } catch (\Throwable $e) {
                    return Carbon::parse($dateStr . ' 08:00');
                }
            }
            $dt = Carbon::parse($dateStr . ' 08:00');
            return $dt->isPast() ? $now : $dt;
        }
        return $now;
    }

    public function computePublishedAt(): Carbon
    {
        $now = Carbon::now();
        if (!$this->scheduled_date) {
            return $now;
        }

        $dateStr = trim($this->scheduled_date);
        $timeStr = $this->scheduled_time ? trim($this->scheduled_time) : '08:00';
        try {
            $targetScheduledAt = Carbon::parse($dateStr . ' ' . $timeStr);
        } catch (\Throwable $e) {
            $targetScheduledAt = Carbon::parse($dateStr . ' 08:00');
        }

        $leadMinutes = isset($this->early_departure_minutes) && is_numeric($this->early_departure_minutes) ? (int) $this->early_departure_minutes : 60;
        $departureAt = ($leadMinutes === 0) ? $targetScheduledAt->copy() : $targetScheduledAt->copy()->subMinutes($leadMinutes);
        if ($departureAt->lt($now)) {
            $departureAt = $now;
        }

        if ($this->publish_mode === 'custom' && !empty($this->publish_time)) {
            $pubDateStr = $this->publish_date ?: $dateStr;
            try {
                $publishAt = Carbon::parse($pubDateStr . ' ' . trim($this->publish_time));
            } catch (\Throwable $e) {
                $publishAt = $now;
            }
        } elseif ($this->publish_mode === 'now') {
            $publishAt = $now;
        } else {
            // Hitung published_at dinamis berdasarkan jarak waktu ke jadwal pelaksanaan
            $deltaHours = $now->diffInHours($targetScheduledAt, false);
            if ($deltaHours <= 2) {
                $publishAt = $now;
            } elseif ($deltaHours <= 24) {
                $publishAt = $targetScheduledAt->copy()->subHours(2);
            } else {
                $publishAt = $targetScheduledAt->copy()->subHours(4);
            }
        }

        if ($publishAt->lt($now)) {
            $publishAt = $now;
        }
        if ($publishAt->gt($departureAt)) {
            $publishAt = $departureAt;
        }

        return $publishAt;
    }

    public function computeExpiresAt(): Carbon
    {
        $base = $this->computeBaseStartTime();
        $publishedAt = $this->computePublishedAt();

        switch ($this->expiry_option) {
            case '1_hour':
                $dt = $base->copy()->addHour();
                return $dt->lte($publishedAt) ? $publishedAt->copy()->addHour() : $dt;
            case '6_hours':
                $dt = $base->copy()->addHours(6);
                return $dt->lte($publishedAt) ? $publishedAt->copy()->addHours(6) : $dt;
            case '24_hours':
                $dt = $base->copy()->addHours(24);
                return $dt->lte($publishedAt) ? $publishedAt->copy()->addHours(24) : $dt;
            case 'custom':
                if ($this->custom_expiry_date) {
                    $dateStr = trim($this->custom_expiry_date);
                    $timeStr = $this->custom_expiry_time ? trim($this->custom_expiry_time) : '23:59';
                    try {
                        return Carbon::parse($dateStr . ' ' . $timeStr);
                    } catch (\Throwable $e) {
                        return $publishedAt->copy()->addHours(24);
                    }
                }
                return $publishedAt->copy()->addHours(24);
            default:
                return $publishedAt->copy()->addHours(24);
        }
    }

    public function getExpiryPreviewProperty(): string
    {
        $dt = $this->computeExpiresAt();
        return $dt->translatedFormat('d M Y, H:i') . ' ' . $this->timezoneLabel;
    }

    public function getSchedulePreviewProperty(): ?string
    {
        if (!$this->scheduled_date) return null;
        $base = $this->computeBaseStartTime();
        return $base->translatedFormat('d M Y, H:i') . ' ' . $this->timezoneLabel;
    }

    /**
     * Dapatkan rincian alur jadwal & radar visibilitas 3-langkah (Timeline Array).
     */
    public function getScheduleTimelineProperty(): array
    {
        if (!$this->scheduled_date) {
            $todayStr = Carbon::now()->translatedFormat('d M Y');
            return [
                'has_schedule'    => false,
                'publish_mode'    => $this->publish_mode,
                'publish_label'   => 'Langsung Sekarang (Saat Dibuat)',
                'departure_label' => 'Langsung Berangkat (Setelah Terima Order)',
                'target_label'    => 'Segera Dikerjakan (Hari ini, ' . $todayStr . ')',
                'target_date'     => 'Pelaksanaan Segera',
                'lead_minutes'    => 0,
                'expiry_label'    => $this->expiryPreview,
            ];
        }

        $dateStr = trim($this->scheduled_date);
        $timeStr = $this->scheduled_time ? trim($this->scheduled_time) : '08:00';

        try {
            $targetDt = Carbon::parse($dateStr . ' ' . $timeStr);
        } catch (\Throwable $e) {
            $targetDt = Carbon::parse($dateStr . ' 08:00');
        }

        $leadMinutes = isset($this->early_departure_minutes) && is_numeric($this->early_departure_minutes) ? (int) $this->early_departure_minutes : 60;
        $departureDt = ($leadMinutes === 0) ? $targetDt->copy() : $targetDt->copy()->subMinutes($leadMinutes);

        // Publish time
        if ($this->publish_mode === 'custom' && !empty($this->publish_time)) {
            $pubDateStr = $this->publish_date ?: $dateStr;
            try {
                $publishDt = Carbon::parse($pubDateStr . ' ' . trim($this->publish_time));
                $publishLabel = 'Pukul ' . $publishDt->format('H:i') . ' ' . $this->timezoneLabel . ($pubDateStr !== $dateStr ? ' (' . $publishDt->format('d M') . ')' : '');
            } catch (\Throwable $e) {
                $publishLabel = 'Pukul ' . $this->publish_time . ' ' . $this->timezoneLabel;
            }
        } else {
            $publishLabel = 'Langsung Sekarang (Saat Dibuat)';
        }

        $departureLabel = ($leadMinutes === 0)
            ? 'Pukul ' . $departureDt->format('H:i') . ' ' . $this->timezoneLabel . ' (Langsung / Sesuai Target)'
            : 'Pukul ' . $departureDt->format('H:i') . ' ' . $this->timezoneLabel . ' (' . $leadMinutes . ' mnt sebelum)';

        return [
            'has_schedule'    => true,
            'publish_mode'    => $this->publish_mode,
            'publish_label'   => $publishLabel,
            'departure_label' => $departureLabel,
            'departure_time'  => $departureDt->format('H:i'),
            'target_label'    => $targetDt->translatedFormat('d M Y, H:i') . ' ' . $this->timezoneLabel,
            'target_time'     => $targetDt->format('H:i'),
            'target_date'     => $targetDt->translatedFormat('d M Y'),
            'lead_minutes'    => $leadMinutes,
            'expiry_label'    => $this->expiryPreview,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VALIDATION RULES
    // ─────────────────────────────────────────────────────────────────────────

    protected $rules = [];

    protected function rules()
    {
        return [
            'title'              => 'required|string|max:255',
            'description'        => 'required|string',
            'equipment_provided' => 'nullable|string|max:1000',
            'amount'             => 'required|numeric|min:0|max:100000000',
            'city_id'            => ['required', Rule::exists('cities', 'id')->where('is_active', true)],
            'district_id'        => ['nullable', Rule::exists('districts', 'id')->where('is_active', true)],
            'location'           => 'nullable|string|max:255',
            'full_address'       => 'nullable|string|max:1000',
            'latitude'           => 'required|numeric|between:-90,90',
            'longitude'          => 'required|numeric|between:-180,180',
            'photo'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'scheduled_date'     => 'nullable|date',
            'scheduled_time'     => ['nullable', 'regex:/^(?:[0-1]?\d|2[0-3]):[0-5]\d$/'],
            'publish_time'       => ['nullable', 'regex:/^(?:[0-1]?\d|2[0-3]):[0-5]\d$/'],
            'custom_expiry_date' => 'nullable|date',
            'custom_expiry_time' => ['nullable', 'regex:/^(?:[0-1]?\d|2[0-3]):[0-5]\d$/'],
        ];
    }

    protected $messages = [
        'title.required'        => 'Judul bantuan wajib diisi',
        'description.required'  => 'Deskripsi bantuan wajib diisi',
        'city_id.required'      => 'Silakan pilih kota lokasi bantuan',
        'city_id.exists'        => 'Kota yang dipilih tidak valid, belum terdaftar, atau sedang ditutup sementara.',
        'district_id.required'  => 'Silakan pilih kecamatan lokasi bantuan',
        'district_id.exists'    => 'Kecamatan yang dipilih tidak valid, belum terdaftar, atau sedang ditutup sementara.',
        'amount.required'       => 'Nominal uang harus diisi',
        'amount.numeric'        => 'Nominal harus berupa angka',
        'amount.min'            => 'Nominal tidak boleh kurang dari nilai minimal yang ditetapkan',
        'amount.max'            => 'Nominal maksimal Rp 100.000.000',
        'latitude.required'     => 'Titik lokasi pada peta wajib ditentukan. Silakan klik pada peta atau gunakan tombol GPS.',
        'longitude.required'    => 'Titik lokasi pada peta wajib ditentukan. Silakan klik pada peta atau gunakan tombol GPS.',
        'location.required'     => 'Alamat lokasi pekerjaan wajib ditentukan.',
        'pickup_address.required'   => 'Alamat titik jemput (Titik 1) wajib ditentukan.',
        'pickup_latitude.required'  => 'Titik 1 (Jemput) pada peta wajib ditentukan.',
        'pickup_longitude.required' => 'Titik 1 (Jemput) pada peta wajib ditentukan.',
        'delivery_address.required'   => 'Alamat titik antar tujuan (Titik 2) wajib ditentukan.',
        'delivery_latitude.required'  => 'Titik 2 (Antar) pada peta wajib ditentukan.',
        'delivery_longitude.required' => 'Titik 2 (Antar) pada peta wajib ditentukan.',
        'scheduled_date.date'  => 'Format tanggal tidak valid',
        'scheduled_time.regex' => 'Format waktu tidak valid. Gunakan format 24-jam HH:MM, contoh: 9:30 atau 09:30',
        'publish_time.regex'   => 'Format jam mulai siar tidak valid. Gunakan format 24-jam HH:MM, contoh: 07:00',
        'custom_expiry_time.regex' => 'Format jam batas waktu tidak valid. Gunakan format 24-jam HH:MM, contoh: 23:59',
        'photo.image'          => 'File harus berupa gambar (JPG, PNG, JPEG, WebP)',
        'photo.mimes'          => 'Format foto harus berupa JPG, JPEG, PNG, atau WebP',
        'photo.max'            => 'Ukuran foto maksimal 2MB',
    ];

    public function setServiceType(string $type): void
    {
        $this->service_type = ($type === Help::SERVICE_TYPE_PICKUP_DELIVERY)
            ? Help::SERVICE_TYPE_PICKUP_DELIVERY
            : Help::SERVICE_TYPE_ON_SITE;

        if ($this->service_type === Help::SERVICE_TYPE_PICKUP_DELIVERY) {
            // Isolasi data: inisialisasi pickup dari koordinat sebelumnya jika ada
            if (!$this->pickup_latitude && $this->latitude) {
                $this->pickup_latitude  = $this->latitude;
                $this->pickup_longitude = $this->longitude;
                $this->pickup_address   = $this->location;
            }
            $this->store_name      = '';
            $this->store_address   = '';
            $this->store_latitude  = null;
            $this->store_longitude = null;
            $this->item_fund       = 0;

            $this->resetErrorBag(['location', 'latitude', 'longitude', 'amount']);

            $maxDistanceKm = AppSetting::getPickupDeliveryMaxDistanceKm();
            if ((float) $this->route_distance_km > $maxDistanceKm) {
                $this->minHelpNominal = 0;
                $this->amount = 0;
                $this->addError('delivery_address', "Jarak rute ({$this->route_distance_km} KM) melebihi batas maksimal {$maxDistanceKm} KM untuk layanan pengantaran sepeda motor.");
            } else {
                try {
                    $calcFee = (int) app(\App\Services\HelpPricingService::class)->calculatePickupDeliveryFare((float) $this->route_distance_km);
                    $this->minHelpNominal = $calcFee;
                    $this->amount = $calcFee;
                } catch (\Throwable $e) {
                    $this->minHelpNominal = (int) AppSetting::get('min_help_nominal', 10000);
                    $this->amount = $this->minHelpNominal;
                }
            }
            $this->calculateRouteDistance();

            $this->dispatch('service-type-changed', [
                'serviceType' => 'pickup_delivery',
                'pickupLat'   => $this->pickup_latitude,
                'pickupLng'   => $this->pickup_longitude,
                'deliveryLat' => $this->delivery_latitude,
                'deliveryLng' => $this->delivery_longitude,
            ]);
        } else {
            // Isolasi data: bersihkan semua titik antar/jemput agar tidak ada tabrakan layer/data
            $this->pickup_address     = '';
            $this->pickup_latitude    = null;
            $this->pickup_longitude   = null;
            $this->delivery_address   = '';
            $this->delivery_latitude  = null;
            $this->delivery_longitude = null;
            $this->route_distance_km  = 0.0;
            $this->store_name         = '';
            $this->store_address      = '';
            $this->store_latitude     = null;
            $this->store_longitude    = null;
            $this->item_fund          = 0;

            $this->resetErrorBag(['pickup_address', 'delivery_address', 'pickup_latitude', 'delivery_latitude', 'amount']);

            $this->minHelpNominal = 10000;
            if (empty($this->amount) || (float) $this->amount < 10000) {
                $this->amount = 10000;
            }

            $this->dispatch('service-type-changed', [
                'serviceType' => 'on_site_service',
                'lat'         => $this->latitude,
                'lng'         => $this->longitude,
            ]);
        }
    }

    public function resolvePickupLocation($lat, $lng, $address = null)
    {
        $this->pickup_latitude  = (float) $lat;
        $this->pickup_longitude = (float) $lng;
        if ($address) {
            $this->pickup_address = $address;
            $this->location       = $address;
        }
        $this->latitude  = (float) $lat;
        $this->longitude = (float) $lng;
        $this->calculateRouteDistance();
    }

    public function resolveDeliveryLocation($lat, $lng, $address = null)
    {
        $this->delivery_latitude  = (float) $lat;
        $this->delivery_longitude = (float) $lng;
        if ($address) {
            $this->delivery_address = $address;
        }
        $this->calculateRouteDistance();
    }

    public function resolveStoreLocation($lat, $lng, $address = null, $storeName = null)
    {
        $this->store_latitude  = (float) $lat;
        $this->store_longitude = (float) $lng;
        if ($address) {
            $this->store_address = $address;
        }
        if ($storeName) {
            $this->store_name = $storeName;
        }
        $this->calculateRouteDistance();
    }

    public function calculateRouteDistance(): void
    {
        $maxDistanceKm = AppSetting::getPickupDeliveryMaxDistanceKm();

        try {
            $pricingService = app(\App\Services\HelpPricingService::class);
            $estimate = $pricingService->calculateInitialOrderEstimate([
                'service_type'       => $this->service_type,
                'amount'             => (float) ($this->amount ?: 0),
                'material_fee'       => 0,
                'item_fund'          => 0,
                'pickup_latitude'    => $this->pickup_latitude ?? $this->latitude,
                'pickup_longitude'   => $this->pickup_longitude ?? $this->longitude,
                'delivery_latitude'  => $this->delivery_latitude ?? $this->latitude,
                'delivery_longitude' => $this->delivery_longitude ?? $this->longitude,
                'store_latitude'     => $this->store_latitude,
                'store_longitude'    => $this->store_longitude,
                'latitude'           => $this->latitude,
                'longitude'          => $this->longitude,
            ]);

            $this->route_distance_km = (float) ($estimate['service_route_distance_km'] ?? 0.0);

            if ($this->service_type === Help::SERVICE_TYPE_PICKUP_DELIVERY) {
                if ($this->route_distance_km > $maxDistanceKm) {
                    $this->addError('delivery_address', "Jarak rute ({$this->route_distance_km} KM) melebihi batas maksimal {$maxDistanceKm} KM untuk layanan pengantaran sepeda motor.");
                    $this->minHelpNominal = 0;
                    $this->amount = 0;
                    $this->dispatch('max-distance-exceeded', [
                        'distance' => $this->route_distance_km,
                        'max'      => $maxDistanceKm,
                        'message'  => "Jarak pengantaran ({$this->route_distance_km} KM) melebihi batas maksimal {$maxDistanceKm} KM untuk armada sepeda motor."
                    ]);
                    return;
                } else {
                    $this->resetErrorBag(['delivery_address', 'route_distance_km']);
                }

                $calcFee = (int) ($estimate['service_fee'] ?? 10000);
                $this->minHelpNominal = $calcFee;
                $this->amount = $calcFee;
            } elseif ($this->service_type !== Help::SERVICE_TYPE_ON_SITE && ($estimate['service_fee'] ?? 0) > (float) ($this->amount ?: 0)) {
                $this->amount = (int) $estimate['service_fee'];
            }
        } catch (\InvalidArgumentException $e) {
            if ($this->pickup_latitude && $this->pickup_longitude && $this->delivery_latitude && $this->delivery_longitude) {
                $this->route_distance_km = round(app(\App\Services\GeoService::class)->getRouteDistance(
                    (float) $this->pickup_latitude,
                    (float) $this->pickup_longitude,
                    (float) $this->delivery_latitude,
                    (float) $this->delivery_longitude
                ), 2);
            }
            $this->addError('delivery_address', $e->getMessage());
            $this->minHelpNominal = 0;
            $this->amount = 0;
            $this->dispatch('max-distance-exceeded', [
                'distance' => $this->route_distance_km,
                'max'      => $maxDistanceKm,
                'message'  => $e->getMessage()
            ]);
        }
    }

    /**
     * Terima hasil pengukuran jarak rute jalan raya nyata (road network routing) dari client.
     */
    public function updateRouteDistanceRoad(float $distanceKm): void
    {
        if ($this->service_type !== Help::SERVICE_TYPE_PICKUP_DELIVERY) {
            return;
        }

        if ($distanceKm > 0) {
            $this->route_distance_km = round($distanceKm, 2);
            $maxDistanceKm = AppSetting::getPickupDeliveryMaxDistanceKm();

            if ($this->route_distance_km > $maxDistanceKm) {
                $this->addError('delivery_address', "Jarak pengantaran ({$this->route_distance_km} KM) melebihi batas maksimal {$maxDistanceKm} KM untuk armada sepeda motor.");
                $this->minHelpNominal = 0;
                $this->amount = 0;
                $this->dispatch('max-distance-exceeded', [
                    'distance' => $this->route_distance_km,
                    'max'      => $maxDistanceKm,
                    'message'  => "Jarak pengantaran ({$this->route_distance_km} KM) melebihi batas maksimal {$maxDistanceKm} KM untuk armada sepeda motor."
                ]);
                return;
            } else {
                $this->resetErrorBag(['delivery_address', 'route_distance_km']);
            }

            try {
                $calcFee = (int) app(\App\Services\HelpPricingService::class)->calculatePickupDeliveryFare((float) $this->route_distance_km);
                $this->amount = $calcFee;
                $this->minHelpNominal = $calcFee;
            } catch (\Throwable $e) {
                $this->amount = 0;
                $this->minHelpNominal = 0;
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONFIRM MODAL — dengan Pricing Engine V3 Transparan & Isolasi Layanan
    // ─────────────────────────────────────────────────────────────────────────

    public function prepareConfirm()
    {
        if (!auth()->check() || !auth()->user()->isCustomer()) {
            abort(403, 'Akses ditolak. Hanya akun Customer yang dapat membuat permintaan bantuan.');
        }

        if (auth()->user()->isShadowBanned() || (int) auth()->user()->warning_level >= 3) {
            $this->addError('amount', 'Akun Anda saat ini dibatasi dari membuat pesanan bantuan baru karena dalam peninjauan moderasi / sanksi SP 3.');
            $this->dispatch('scroll-to-first-error');
            return;
        }

        if (empty($this->district_id) && !empty($this->city_id)) {
            $matchedDistrict = \App\Models\District::where('city_id', $this->city_id)->where('is_active', true)->first();
            if ($matchedDistrict) {
                $this->district_id = $matchedDistrict->id;
                $this->districtQuery = $matchedDistrict->name;
            }
        }

        // ISOLASI KETAT DATA ANTAR JENIS LAYANAN SEBELUM VALIDASI & TRANSAKSI
        if ($this->service_type === Help::SERVICE_TYPE_PICKUP_DELIVERY) {
            $this->store_name      = null;
            $this->store_address   = null;
            $this->store_latitude  = null;
            $this->store_longitude = null;
            $this->item_fund       = 0;

            if (empty($this->pickup_latitude) || empty($this->pickup_longitude)) {
                $this->addError('pickup_address', 'Silakan tentukan Titik 1 (Jemput) pada peta terlebih dahulu.');
                $this->dispatch('scroll-to-first-error');
                return;
            }
            if (empty($this->delivery_latitude) || empty($this->delivery_longitude)) {
                $this->addError('delivery_address', 'Silakan tentukan Titik 2 (Antar/Tujuan) pada peta terlebih dahulu.');
                $this->dispatch('scroll-to-first-error');
                return;
            }

            $geoService = app(\App\Services\GeoService::class);
            $pSafety = $geoService->validateLocationSafety((float) $this->pickup_latitude, (float) $this->pickup_longitude);
            if (!$pSafety['is_safe']) {
                $this->addError('pickup_address', $pSafety['reason'] ?? 'Titik 1 (Jemput) berada di area terlarang.');
                $this->dispatch('scroll-to-first-error');
                return;
            }
            $dSafety = $geoService->validateLocationSafety((float) $this->delivery_latitude, (float) $this->delivery_longitude);
            if (!$dSafety['is_safe']) {
                $this->addError('delivery_address', $dSafety['reason'] ?? 'Titik 2 (Antar) berada di area terlarang.');
                $this->dispatch('scroll-to-first-error');
                return;
            }

            if (empty($this->pickup_address)) {
                $this->pickup_address = 'Titik Jemput (' . round((float)$this->pickup_latitude, 4) . ', ' . round((float)$this->pickup_longitude, 4) . ')';
            }
            if (empty($this->delivery_address)) {
                $this->delivery_address = 'Titik Antar (' . round((float)$this->delivery_latitude, 4) . ', ' . round((float)$this->delivery_longitude, 4) . ')';
            }

            $this->latitude  = $this->pickup_latitude;
            $this->longitude = $this->pickup_longitude;
            $this->location  = $this->pickup_address;

            if ($this->route_distance_km <= 0) {
                $this->calculateRouteDistance();
            }

            $maxDistanceKm = AppSetting::getPickupDeliveryMaxDistanceKm();
            if ($this->route_distance_km > $maxDistanceKm) {
                $this->addError('delivery_address', "Jarak rute ({$this->route_distance_km} KM) melebihi batas maksimal {$maxDistanceKm} KM untuk layanan pengantaran sepeda motor.");
                $this->dispatch('scroll-to-first-error');
                return;
            }

            $this->rules = [
                'title'              => 'required|string|max:255',
                'description'        => 'required|string',
                'equipment_provided' => 'nullable|string|max:1000',
                'amount'             => 'required|numeric|min:10000|max:100000000',
                'city_id'            => ['required', Rule::exists('cities', 'id')->where('is_active', true)],
                'district_id'        => ['nullable', Rule::exists('districts', 'id')->where('is_active', true)],
                'pickup_address'     => 'required|string|max:500',
                'pickup_latitude'    => 'required|numeric|between:-90,90',
                'pickup_longitude'   => 'required|numeric|between:-180,180',
                'delivery_address'   => 'required|string|max:500',
                'delivery_latitude'  => 'required|numeric|between:-90,90',
                'delivery_longitude' => 'required|numeric|between:-180,180',
                'photo'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'scheduled_date'     => 'nullable|date',
                'scheduled_time'     => ['nullable', 'regex:/^(?:[0-1]?\d|2[0-3]):[0-5]\d$/'],
            ];
        } else {
            // On-Site Service
            $this->pickup_address     = null;
            $this->pickup_latitude    = null;
            $this->pickup_longitude   = null;
            $this->delivery_address   = null;
            $this->delivery_latitude  = null;
            $this->delivery_longitude = null;
            $this->route_distance_km  = 0.0;
            $this->store_name         = null;
            $this->store_address      = null;
            $this->store_latitude     = null;
            $this->store_longitude    = null;
            $this->item_fund          = 0;

            if (empty($this->latitude) || empty($this->longitude)) {
                $this->addError('latitude', 'Silakan pilih titik lokasi pekerjaan pada peta terlebih dahulu.');
                $this->dispatch('scroll-to-first-error');
                return;
            }

            $geoService = app(\App\Services\GeoService::class);
            $oSafety = $geoService->validateLocationSafety((float) $this->latitude, (float) $this->longitude);
            if (!$oSafety['is_safe']) {
                $this->addError('latitude', $oSafety['reason'] ?? 'Titik lokasi berada di area terlarang.');
                $this->dispatch('scroll-to-first-error');
                return;
            }

            if (empty($this->location)) {
                $this->location = 'Titik Lokasi (' . round((float)$this->latitude, 4) . ', ' . round((float)$this->longitude, 4) . ')';
            }

            $this->minHelpNominal = 10000;

            $this->rules = [
                'title'              => 'required|string|max:255',
                'description'        => 'required|string',
                'equipment_provided' => 'nullable|string|max:1000',
                'amount'             => 'required|numeric|min:10000|max:100000000',
                'city_id'            => ['required', Rule::exists('cities', 'id')->where('is_active', true)],
                'district_id'        => ['nullable', Rule::exists('districts', 'id')->where('is_active', true)],
                'location'           => 'required|string|max:500',
                'full_address'       => 'nullable|string|max:1000',
                'latitude'           => 'required|numeric|between:-90,90',
                'longitude'          => 'required|numeric|between:-180,180',
                'photo'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'scheduled_date'     => 'nullable|date',
                'scheduled_time'     => ['nullable', 'regex:/^(?:[0-1]?\d|2[0-3]):[0-5]\d$/'],
            ];
        }

        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('scroll-to-first-error');
            throw $e;
        }

        // Kalkulasi Estimasi Awal berbasis Pricing Engine V3
        $pricingService = app(\App\Services\HelpPricingService::class);
        $estimate = $pricingService->calculateInitialOrderEstimate([
            'service_type'                  => $this->service_type,
            'service_category'              => $this->service_category ?: 'general',
            'service_duration_hours'        => (float) ($this->service_duration_hours ?: 1.0),
            'amount'                        => (float) ($this->amount ?: 0),
            'service_route_distance_km'     => (float) ($this->route_distance_km ?: 0),
            'route_distance_km'             => (float) ($this->route_distance_km ?: 0),
            'material_fee'                  => 0,
            'item_fund'                     => 0,
            'item_fund_mode'                => $this->item_fund_mode,
            'customer_reimbursement_method' => $this->customer_reimbursement_method,
            'advance_limit'                 => (float) ($this->advance_limit ?: 100000),
            'pickup_latitude'               => $this->pickup_latitude,
            'pickup_longitude'              => $this->pickup_longitude,
            'delivery_latitude'             => $this->delivery_latitude,
            'delivery_longitude'            => $this->delivery_longitude,
            'store_latitude'                => null,
            'store_longitude'               => null,
            'latitude'                      => $this->latitude,
            'longitude'                     => $this->longitude,
        ]);

        $totalAmount = (float) $estimate['total_amount'];

        // Validasi saldo customer mencukupi total pembayaran
        $customer        = auth()->user();
        $customerBalance = \App\Models\UserBalance::where('user_id', $customer->id)->first();
        $currentBalance  = $customerBalance ? (float) $customerBalance->balance : 0;

        if ($currentBalance < $totalAmount) {
            $this->addError('amount', 'Saldo tidak mencukupi. Total saldo yang dibutuhkan: Rp ' . number_format($totalAmount, 0, ',', '.') . '. Saldo Anda: Rp ' . number_format($currentBalance, 0, ',', '.') . '. Silakan top up terlebih dahulu.');
            $this->dispatch('scroll-to-first-error');
            return;
        }

        // Validasi dan normalisasi jadwal
        if ($this->scheduled_date) {
            $dateStr = trim($this->scheduled_date);
            $timeStr = $this->scheduled_time ? trim($this->scheduled_time) : null;
            
            if ($timeStr) {
                $scheduledAt = Carbon::parse($dateStr . ' ' . $timeStr);
            } else {
                $scheduledAt = ($dateStr === Carbon::now()->format('Y-m-d'))
                    ? Carbon::now()
                    : Carbon::parse($dateStr . ' 08:00');
            }

            $leadMinutes = isset($this->early_departure_minutes) && is_numeric($this->early_departure_minutes) ? (int) $this->early_departure_minutes : 60;
            $minScheduledAt = Carbon::now()->addMinutes($leadMinutes);

            if ($scheduledAt->lt($minScheduledAt->copy()->subMinutes(2))) {
                if ($leadMinutes > 0) {
                    $minTimeStr = $minScheduledAt->format('H:i');
                    $this->addError('scheduled_time', "Waktu pelaksanaan tugas (Target Mulai) minimal Pukul {$minTimeStr} {$this->timezoneLabel} (Waktu saat ini + Jeda keberangkatan mitra {$leadMinutes} menit).");
                } else {
                    $this->addError('scheduled_time', 'Waktu pelaksanaan tugas (Target Mulai) tidak boleh berada di masa lalu.');
                }
                $this->dispatch('scroll-to-first-error');
                return;
            }

            $departureAt = ($leadMinutes === 0)
                ? $scheduledAt->copy()
                : $scheduledAt->copy()->subMinutes($leadMinutes);

            if ($this->publish_mode === 'custom' && !empty($this->publish_time)) {
                $pubDateStr = $this->publish_date ?: $dateStr;
                try {
                    $publishAt = Carbon::parse($pubDateStr . ' ' . trim($this->publish_time));
                    if ($publishAt->gt($departureAt)) {
                        $departureTimeStr = $departureAt->format('H:i');
                        $this->addError('publish_time', "Jam mulai muncul di radar ({$this->publish_time}) tidak boleh melebihi waktu keberangkatan mitra (Pukul {$departureTimeStr} {$this->timezoneLabel}).");
                        $this->dispatch('scroll-to-first-error');
                        return;
                    }
                    if ($publishAt->lt(Carbon::now()->subMinutes(5))) {
                        $this->addError('publish_time', "Jam mulai muncul di radar ({$this->publish_time}) sudah terlewat untuk tanggal yang dipilih. Silakan atur jam setelah waktu saat ini atau pilih 'Mulai Sekarang'.");
                        $this->dispatch('scroll-to-first-error');
                        return;
                    }
                } catch (\Throwable $e) {
                    $this->addError('publish_time', 'Format waktu mulai siar tidak valid');
                    $this->dispatch('scroll-to-first-error');
                    return;
                }
            }
        }

        $publishedAt = $this->computePublishedAt();
        $expiresAt   = $this->computeExpiresAt();

        if ($expiresAt->lte($publishedAt)) {
            $pubTimeStr = $publishedAt->translatedFormat('d M Y, H:i') . ' ' . $this->timezoneLabel;
            if ($this->expiry_option === 'custom') {
                $this->addError('custom_expiry_time', "Batas waktu pencarian rekan jasa ({$expiresAt->translatedFormat('d M Y, H:i')}) tidak boleh kurang dari atau sama dengan waktu kemunculan order ({$pubTimeStr}).");
            } else {
                $this->addError('expiry_option', "Batas waktu pencarian rekan jasa harus melebihi waktu kemunculan order ({$pubTimeStr}).");
            }
            $this->dispatch('scroll-to-first-error');
            return;
        }

        if ($expiresAt->lt(Carbon::now()->subMinutes(5))) {
            $this->addError('custom_expiry_time', 'Batas waktu pencarian rekan jasa tidak boleh berada di masa lalu.');
            $this->dispatch('scroll-to-first-error');
            return;
        }

        // Confirm modal data
        $this->confirmServiceFee     = $estimate['service_fee'];
        $this->confirmTravelFee      = 0;
        $this->confirmMaterialFee    = 0;
        $this->confirmItemFund       = 0;
        $this->confirmAmount         = $estimate['service_fee'];
        $this->confirmAdminFee       = $estimate['platform_fee'];
        $this->confirmTotal          = $totalAmount;
        $this->confirmPlatformFee    = $estimate['platform_fee'];
        $this->confirmFeeLabel       = 'Rp ' . number_format($estimate['platform_fee'], 0, ',', '.');
        $this->confirmMitraEarning   = $estimate['mitra_earning'];
        $this->confirmMinServiceFee  = $estimate['minimum_service_fee'];
        $this->route_distance_km     = $estimate['service_route_distance_km'] ?? 0.0;
        $this->confirmScheduled      = $this->scheduled_date
            ? (date('d M Y', strtotime($this->scheduled_date)) . ($this->scheduled_time ? ' Pukul ' . $this->scheduled_time . ' ' . $this->timezoneLabel : ''))
            : null;

        $this->confirmExpiresAt = $expiresAt->translatedFormat('d M Y, H:i') . ' ' . $this->timezoneLabel;

        $this->showConfirmModal = true;
    }

    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SAVE — Model V3 Transparan, Escrow Lock Terpadu, & Isolasi Data Penuh
    // ─────────────────────────────────────────────────────────────────────────

    public function save()
    {
        if (auth()->user()->isShadowBanned() || (int) auth()->user()->warning_level >= 3) {
            $this->addError('amount', 'Akun Anda saat ini dibatasi dari membuat pesanan bantuan baru karena dalam peninjauan moderasi / sanksi SP 3.');
            return;
        }

        if (empty($this->district_id) && !empty($this->city_id)) {
            $matchedDistrict = \App\Models\District::where('city_id', $this->city_id)->where('is_active', true)->first();
            if ($matchedDistrict) {
                $this->district_id = $matchedDistrict->id;
                $this->districtQuery = $matchedDistrict->name;
            }
        }

        // ISOLASI KETAT DATA SEBELUM PENYIMPANAN TRANSAKSI
        if ($this->service_type === Help::SERVICE_TYPE_PICKUP_DELIVERY) {
            $maxDistanceKm = AppSetting::getPickupDeliveryMaxDistanceKm();
            if ($this->route_distance_km > $maxDistanceKm) {
                $this->addError('delivery_address', "Jarak pengantaran ({$this->route_distance_km} KM) melebihi batas maksimal {$maxDistanceKm} KM untuk layanan pengantaran sepeda motor.");
                return;
            }

            $this->store_name      = null;
            $this->store_address   = null;
            $this->store_latitude  = null;
            $this->store_longitude = null;
            $this->item_fund       = 0;

            if (empty($this->pickup_address) && $this->pickup_latitude && $this->pickup_longitude) {
                $this->pickup_address = 'Titik Jemput (' . round((float)$this->pickup_latitude, 4) . ', ' . round((float)$this->pickup_longitude, 4) . ')';
            }
            if (empty($this->delivery_address) && $this->delivery_latitude && $this->delivery_longitude) {
                $this->delivery_address = 'Titik Antar (' . round((float)$this->delivery_latitude, 4) . ', ' . round((float)$this->delivery_longitude, 4) . ')';
            }

            $this->latitude  = $this->pickup_latitude;
            $this->longitude = $this->pickup_longitude;
            $this->location  = $this->pickup_address;

            $this->rules = [
                'title'              => 'required|string|max:255',
                'description'        => 'required|string',
                'city_id'            => ['required', Rule::exists('cities', 'id')->where('is_active', true)],
                'district_id'        => ['nullable', Rule::exists('districts', 'id')->where('is_active', true)],
                'pickup_address'     => 'required|string|max:500',
                'pickup_latitude'    => 'required|numeric|between:-90,90',
                'pickup_longitude'   => 'required|numeric|between:-180,180',
                'delivery_address'   => 'required|string|max:500',
                'delivery_latitude'  => 'required|numeric|between:-90,90',
                'delivery_longitude' => 'required|numeric|between:-180,180',
                'amount'             => 'required|numeric|min:10000|max:100000000',
            ];
        } else {
            $this->pickup_address     = null;
            $this->pickup_latitude    = null;
            $this->pickup_longitude   = null;
            $this->delivery_address   = null;
            $this->delivery_latitude  = null;
            $this->delivery_longitude = null;
            $this->route_distance_km  = 0.0;
            $this->store_name         = null;
            $this->store_address      = null;
            $this->store_latitude     = null;
            $this->store_longitude    = null;
            $this->item_fund          = 0;

            if (empty($this->location) && $this->latitude && $this->longitude) {
                $this->location = 'Titik Lokasi (' . round((float)$this->latitude, 4) . ', ' . round((float)$this->longitude, 4) . ')';
            }

            $this->rules = [
                'title'        => 'required|string|max:255',
                'description'  => 'required|string',
                'city_id'      => ['required', Rule::exists('cities', 'id')->where('is_active', true)],
                'district_id'  => ['nullable', Rule::exists('districts', 'id')->where('is_active', true)],
                'location'     => 'required|string|max:500',
                'latitude'     => 'required|numeric|between:-90,90',
                'longitude'    => 'required|numeric|between:-180,180',
                'amount'       => 'required|numeric|min:10000|max:100000000',
            ];
        }

        $this->validate();

        $photoPath = $this->photo ? $this->photo->store('helps', 'public') : null;

        try {
            $creationService = app(\App\Services\HelpCreationService::class);
            $createdHelp = $creationService->createHelp(auth()->user(), [
                'city_id'                       => $this->city_id,
                'district_id'                   => $this->district_id ?: null,
                'title'                         => $this->title,
                'service_type'                  => $this->service_type,
                'service_category'              => $this->service_category ?: ($this->service_type === Help::SERVICE_TYPE_PICKUP_DELIVERY ? 'goods_document' : 'general'),
                'service_duration_hours'        => (float) ($this->service_duration_hours ?: 1.0),
                'amount'                        => (float) ($this->amount ?: 0),
                'route_distance_km'             => (float) ($this->route_distance_km ?: 0),
                'service_route_distance_km'     => (float) ($this->route_distance_km ?: 0),
                'material_fee'                  => 0.0,
                'item_fund'                     => 0.0,
                'item_fund_mode'                => $this->item_fund_mode,
                'customer_reimbursement_method' => $this->customer_reimbursement_method,
                'advance_limit'                 => (float) ($this->advance_limit ?: 100000),
                'description'                   => $this->description,
                'equipment_provided'            => $this->equipment_provided,
                'location'                      => $this->location,
                'full_address'                  => $this->full_address,
                'latitude'                      => $this->latitude,
                'longitude'                     => $this->longitude,
                'pickup_address'                => $this->pickup_address,
                'pickup_latitude'               => $this->pickup_latitude,
                'pickup_longitude'              => $this->pickup_longitude,
                'delivery_address'              => $this->delivery_address,
                'delivery_latitude'             => $this->delivery_latitude,
                'delivery_longitude'            => $this->delivery_longitude,
                'scheduled_date'                => $this->scheduled_date,
                'scheduled_time'                => $this->scheduled_time,
                'publish_mode'                  => $this->publish_mode,
                'publish_date'                  => $this->publish_date,
                'publish_time'                  => $this->publish_time,
                'early_departure_minutes'       => $this->early_departure_minutes,
                'expires_at'                    => $this->computeExpiresAt()->format('Y-m-d H:i:s'),
                'photo_path'                    => $photoPath,
            ]);

            $this->dispatch('draft-cleared');
            $this->dispatch('help:draft-cleared');
            session()->flash('message', 'Permintaan bantuan berhasil dibuat! Sistem sedang memproses untuk Anda.');
            return redirect()->route('customer.helps.detail', ['id' => $createdHelp->id]);
        } catch (\Throwable $e) {
            $this->addError('amount', $e->getMessage());
            return;
        }
    }

    /**
     * Pulihkan data draf dari Cookie penyimpanan sementara (kedua jenis layanan).
     */
    public function restoreDraft(array $data): void
    {
        if (isset($data['service_type']) && in_array($data['service_type'], [Help::SERVICE_TYPE_ON_SITE, Help::SERVICE_TYPE_PICKUP_DELIVERY])) {
            $this->service_type = $data['service_type'];
        }
        if (isset($data['service_category']) && is_string($data['service_category'])) {
            $this->service_category = $data['service_category'];
        }
        if (isset($data['service_duration_hours']) && is_numeric($data['service_duration_hours'])) {
            $this->service_duration_hours = (float) $data['service_duration_hours'];
        }
        if (isset($data['title']) && is_string($data['title']) && !empty($data['title'])) {
            $this->title = $data['title'];
        }
        if (isset($data['description']) && is_string($data['description']) && !empty($data['description'])) {
            $this->description = $data['description'];
        }
        if (isset($data['equipment_provided']) && is_string($data['equipment_provided'])) {
            $this->equipment_provided = $data['equipment_provided'];
        }
        if (isset($data['amount']) && is_numeric($data['amount'])) {
            $this->amount = (int) $data['amount'];
        }
        if (isset($data['city_id']) && !empty($data['city_id'])) {
            $this->setCityId($data['city_id']);
        }
        if (isset($data['district_id']) && !empty($data['district_id'])) {
            $this->setDistrictId($data['district_id']);
        }
        if (isset($data['cityQuery']) && is_string($data['cityQuery']) && empty($this->cityQuery)) {
            $this->cityQuery = $data['cityQuery'];
        }
        if (isset($data['districtQuery']) && is_string($data['districtQuery']) && empty($this->districtQuery)) {
            $this->districtQuery = $data['districtQuery'];
        }
        if (isset($data['location']) && is_string($data['location'])) {
            $this->location = $data['location'];
        }
        if (isset($data['full_address']) && is_string($data['full_address'])) {
            $this->full_address = $data['full_address'];
        }
        if (isset($data['latitude']) && is_numeric($data['latitude'])) {
            $this->latitude = (float) $data['latitude'];
        }
        if (isset($data['longitude']) && is_numeric($data['longitude'])) {
            $this->longitude = (float) $data['longitude'];
        }

        // Multi-Point Pickup & Delivery Fields
        if (isset($data['pickup_address']) && is_string($data['pickup_address'])) {
            $this->pickup_address = $data['pickup_address'];
        }
        if (isset($data['pickup_latitude']) && is_numeric($data['pickup_latitude'])) {
            $this->pickup_latitude = (float) $data['pickup_latitude'];
        }
        if (isset($data['pickup_longitude']) && is_numeric($data['pickup_longitude'])) {
            $this->pickup_longitude = (float) $data['pickup_longitude'];
        }
        if (isset($data['delivery_address']) && is_string($data['delivery_address'])) {
            $this->delivery_address = $data['delivery_address'];
        }
        if (isset($data['delivery_latitude']) && is_numeric($data['delivery_latitude'])) {
            $this->delivery_latitude = (float) $data['delivery_latitude'];
        }
        if (isset($data['delivery_longitude']) && is_numeric($data['delivery_longitude'])) {
            $this->delivery_longitude = (float) $data['delivery_longitude'];
        }
        if (isset($data['route_distance_km']) && is_numeric($data['route_distance_km'])) {
            $this->route_distance_km = (float) $data['route_distance_km'];
        }

        // Scheduling & Expiry
        if (isset($data['order_mode']) && in_array($data['order_mode'], ['instant', 'scheduled'])) {
            $this->order_mode = $data['order_mode'];
        }
        if (isset($data['scheduled_date']) && is_string($data['scheduled_date'])) {
            $this->scheduled_date = $data['scheduled_date'];
        }
        if (isset($data['scheduled_time']) && is_string($data['scheduled_time'])) {
            $this->scheduled_time = $data['scheduled_time'];
        }
        if (isset($data['publish_mode']) && in_array($data['publish_mode'], ['now', 'custom'])) {
            $this->publish_mode = $data['publish_mode'];
        }
        if (isset($data['publish_date']) && is_string($data['publish_date'])) {
            $this->publish_date = $data['publish_date'];
        }
        if (isset($data['publish_time']) && is_string($data['publish_time'])) {
            $this->publish_time = $data['publish_time'];
        }
        if (isset($data['early_departure_minutes']) && is_numeric($data['early_departure_minutes'])) {
            $this->early_departure_minutes = (int) $data['early_departure_minutes'];
        }
        if (isset($data['expiry_option']) && is_string($data['expiry_option'])) {
            $this->expiry_option = $data['expiry_option'];
        }
        if (isset($data['custom_expiry_date']) && is_string($data['custom_expiry_date'])) {
            $this->custom_expiry_date = $data['custom_expiry_date'];
        }
        if (isset($data['custom_expiry_time']) && is_string($data['custom_expiry_time'])) {
            $this->custom_expiry_time = $data['custom_expiry_time'];
        }

        $this->isDraftRestored = true;
        $this->dispatch('help:draft-restored', [
            'service_type'       => $this->service_type,
            'latitude'           => $this->latitude,
            'longitude'          => $this->longitude,
            'pickup_latitude'    => $this->pickup_latitude,
            'pickup_longitude'   => $this->pickup_longitude,
            'delivery_latitude'  => $this->delivery_latitude,
            'delivery_longitude' => $this->delivery_longitude,
        ]);
    }

    /**
     * Buang dan bersihkan draft tersimpan.
     */
    public function discardDraft(): void
    {
        $this->isDraftRestored = false;
        $this->title = '';
        $this->description = '';
        $this->equipment_provided = '';
        $this->location = '';
        $this->full_address = '';
        $this->latitude = null;
        $this->longitude = null;
        $this->pickup_address = '';
        $this->pickup_latitude = null;
        $this->pickup_longitude = null;
        $this->delivery_address = '';
        $this->delivery_latitude = null;
        $this->delivery_longitude = null;
        $this->route_distance_km = 0.0;
        $this->scheduled_date = null;
        $this->scheduled_time = null;
        $this->publish_mode = 'now';
        $this->publish_date = null;
        $this->publish_time = null;
        $this->early_departure_minutes = 60;
        $this->order_mode = 'instant';
        $this->amount = $this->minHelpNominal;
        $this->dispatch('help:draft-cleared');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function computeTimezoneLabelFromCity(City $city): string
    {
        if (!empty($city->longitude)) {
            $lon = floatval($city->longitude);
            if ($lon >= 130) return 'WIT';
            if ($lon >= 115) return 'WITA';
            return 'WIB';
        }

        $prov    = strtolower($city->province ?? '');
        $eastern = ['papua', 'papua barat', 'maluku', 'maluku utara'];
        foreach ($eastern as $p) {
            if (str_contains($prov, $p)) return 'WIT';
        }
        $central = ['bali', 'nusa tenggara', 'sulawesi', 'kalimantan tengah', 'kalimantan timur', 'kalimantan selatan'];
        foreach ($central as $p) {
            if (str_contains($prov, $p)) return 'WITA';
        }
        return 'WIB';
    }

    private function ianaForZone(string $zone): string
    {
        return match($zone) {
            'WITA'  => 'Asia/Makassar',
            'WIT'   => 'Asia/Jayapura',
            default => 'Asia/Jakarta',
        };
    }

    private function generateOrderId(): string
    {
        for ($i = 0; $i < 5; $i++) {
            $candidate = 'HELP-' . date('YmdHis') . '-' . random_int(1000, 9999);
            if (!Help::where('order_id', $candidate)->exists()) {
                return $candidate;
            }
            usleep(200);
        }
        return 'HELP-' . uniqid();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RENDER
    // ─────────────────────────────────────────────────────────────────────────

    public function render()
    {
        if ($this->service_type === Help::SERVICE_TYPE_PICKUP_DELIVERY) {
            $maxDistanceKm = AppSetting::getPickupDeliveryMaxDistanceKm();
            if ((float) $this->route_distance_km > $maxDistanceKm) {
                $this->minHelpNominal = 0;
            } else {
                try {
                    $this->minHelpNominal = (int) app(\App\Services\HelpPricingService::class)->calculatePickupDeliveryFare((float) $this->route_distance_km);
                } catch (\Throwable $e) {
                    $this->minHelpNominal = 0;
                }
            }
        } else {
            $this->minHelpNominal = (int) AppSetting::get('min_help_nominal', 10000);
        }

        return view('livewire.customer.helps.create', [
            'cities' => City::where('is_active', true)->get(),
            'minHelpNominal' => $this->minHelpNominal,
        ]);
    }
}
