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
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    // ─── Form fields (Revisi 3) ──────────────────────────────────────────────
    public $service_type       = 'on_site_service'; // 'on_site_service', 'pickup_delivery', 'buy_for_customer'
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

    // ─── Req province/regency/district selectors ─────────────────────────────
    public $req_province_id = '';
    public $req_regency_id  = '';
    public $req_district_id = '';
    public $req_provinces   = [];
    public $req_regencies   = [];
    public $req_districts   = [];

    // ─── Scheduling ──────────────────────────────────────────────────────────
    public $scheduled_date = null;
    public $scheduled_time = null;
    public $timezoneLabel  = 'WIB';
    public $timezoneIana   = 'Asia/Jakarta';

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

    protected $listeners = [
        'citySelected' => 'setCityId',
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

        if (auth()->user()->isShadowBanned()) {
            session()->flash('error', 'Akun Anda saat ini dibatasi dari membuat pekerjaan bantuan baru karena dalam status peninjauan moderasi.');
        }

        $this->minHelpNominal = (int) AppSetting::get('min_help_nominal', 10000);

        // Set default nominal yang wajar
        if (empty($this->amount) || $this->amount < $this->minHelpNominal) {
            $this->amount = $this->minHelpNominal;
        }

        // Otomatis isi kota & kecamatan dari akun Customer
        $user = auth()->user();
        if ($user) {
            if (!empty($user->city_id)) {
                $this->setCityId($user->city_id);
            } elseif (!empty($user->city)) {
                $matchedCity = City::where('name', 'LIKE', '%' . trim($user->city) . '%')->first();
                if ($matchedCity) {
                    $this->setCityId($matchedCity->id);
                }
            }

            if (!empty($user->district_id)) {
                $this->setDistrictId($user->district_id);
            }
        }

        if (Schema::hasTable('req_provinces')) {
            $this->req_provinces = DB::table('req_provinces')->orderBy('province')->get()->toArray();
        }
    }

    /**
     * Tambah/kurang nominal bantuan secara instan dan user friendly.
     */
    public function adjustAmount(int $delta): void
    {
        $min = (int) ($this->minHelpNominal ?: AppSetting::get('min_help_nominal', 10000));
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
        $this->amount = max($min, min(100000000, $value));
    }

    /**
     * Hapus pilihan jadwal bantuan.
     */
    public function clearSchedule(): void
    {
        $this->scheduled_date = null;
        $this->scheduled_time = null;
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
        $this->city_id = $id;
        $city = City::find($id);
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
                $this->custom_expiry_date = Carbon::now()->addDay()->format('Y-m-d');
            }
            if (!$this->custom_expiry_time) {
                $this->custom_expiry_time = Carbon::now()->format('H:i');
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

    public function computeExpiresAt(): Carbon
    {
        $base = $this->computeBaseStartTime();

        switch ($this->expiry_option) {
            case '1_hour':
                return $base->copy()->addHour();
            case '6_hours':
                return $base->copy()->addHours(6);
            case '24_hours':
                return $base->copy()->addHours(24);
            case 'custom':
                if ($this->custom_expiry_date) {
                    $dateStr = trim($this->custom_expiry_date);
                    $timeStr = $this->custom_expiry_time ? trim($this->custom_expiry_time) : '23:59';
                    try {
                        return Carbon::parse($dateStr . ' ' . $timeStr);
                    } catch (\Throwable $e) {
                        return $base->copy()->addHours(24);
                    }
                }
                return $base->copy()->addHours(24);
            default:
                return $base->copy()->addHours(24);
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

    // ─────────────────────────────────────────────────────────────────────────
    // VALIDATION RULES
    // ─────────────────────────────────────────────────────────────────────────

    protected $rules = [
        'title'              => 'required|string|max:255',
        'description'        => 'required|string',
        'equipment_provided' => 'nullable|string|max:1000',
        'amount'             => 'required|numeric|min:0|max:100000000',
        'city_id'            => 'required|exists:cities,id',
        'district_id'        => 'required|exists:districts,id',
        'location'           => 'nullable|string|max:255',
        'full_address'       => 'nullable|string|max:1000',
        'latitude'           => 'required|numeric|between:-90,90',
        'longitude'          => 'required|numeric|between:-180,180',
        'photo'              => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'scheduled_date'     => 'nullable|date',
        'scheduled_time'     => ['nullable', 'regex:/^(?:[0-1]?\d|2[0-3]):[0-5]\d$/'],
    ];

    protected $messages = [
        'title.required'        => 'Judul bantuan wajib diisi',
        'description.required'  => 'Deskripsi bantuan wajib diisi',
        'city_id.required'      => 'Silakan pilih kota lokasi bantuan',
        'city_id.exists'        => 'Kota yang dipilih tidak valid atau belum terdaftar',
        'district_id.required'  => 'Silakan pilih kecamatan lokasi bantuan',
        'district_id.exists'    => 'Kecamatan yang dipilih tidak valid atau belum terdaftar',
        'amount.required'       => 'Nominal uang harus diisi',
        'amount.numeric'        => 'Nominal harus berupa angka',
        'amount.min'            => 'Nominal tidak boleh kurang dari nilai minimal yang ditetapkan',
        'amount.max'            => 'Nominal maksimal Rp 100.000.000',
        'latitude.required'     => 'Titik lokasi pada peta wajib ditentukan. Silakan klik pada peta atau gunakan tombol GPS.',
        'longitude.required'    => 'Titik lokasi pada peta wajib ditentukan. Silakan klik pada peta atau gunakan tombol GPS.',
        'scheduled_date.date'  => 'Format tanggal tidak valid',
        'scheduled_time.regex' => 'Format waktu tidak valid. Gunakan format 24-jam HH:MM, contoh: 9:30 atau 09:30',
        'photo.image'          => 'File harus berupa gambar (JPG, PNG, JPEG)',
        'photo.max'            => 'Ukuran foto maksimal 2MB',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // CONFIRM MODAL — tanpa cek saldo
    // ─────────────────────────────────────────────────────────────────────────

    // ─────────────────────────────────────────────────────────────────────────
    // CONFIRM MODAL — dengan Pricing Engine & Schedule Engine Terintegrasi
    // ─────────────────────────────────────────────────────────────────────────

    public function prepareConfirm()
    {
        if (!auth()->check() || !auth()->user()->isCustomer()) {
            abort(403, 'Akses ditolak. Hanya akun Customer yang dapat membuat permintaan bantuan.');
        }

        $this->minHelpNominal = (int) AppSetting::get('min_help_nominal', 10000);

        $this->rules['city_id']     = 'required|exists:cities,id';
        $this->rules['district_id'] = 'required|exists:districts,id';
        $this->rules['title']       = 'required|string|max:255';
        $this->rules['description'] = 'required|string';
        $this->rules['latitude']    = 'required|numeric|between:-90,90';
        $this->rules['longitude']   = 'required|numeric|between:-180,180';

        if ($this->service_type === Help::SERVICE_TYPE_PICKUP_DELIVERY) {
            $this->rules['pickup_address']   = 'nullable|string';
            $this->rules['delivery_address'] = 'nullable|string';
        } elseif ($this->service_type === Help::SERVICE_TYPE_BUY_FOR_CUSTOMER) {
            $this->rules['item_fund'] = 'nullable|numeric|min:0';
        }

        if (auth()->user()->isShadowBanned()) {
            $this->addError('amount', 'Akun Anda saat ini dibatasi dari membuat pesanan bantuan baru karena dalam peninjauan moderasi.');
            $this->dispatch('scroll-to-first-error');
            return;
        }

        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('scroll-to-first-error');
            throw $e;
        }

        // Kalkulasi Estimasi Awal berbasis Pricing Engine (Fase 1)
        $pricingService = app(\App\Services\HelpPricingService::class);
        $estimate = $pricingService->calculateInitialOrderEstimate([
            'service_type'                  => $this->service_type,
            'service_category'              => $this->service_category ?: 'general',
            'service_duration_hours'        => (float) ($this->service_duration_hours ?: 1.0),
            'amount'                        => (float) ($this->amount ?: 0),
            'material_fee'                  => (float) ($this->material_fee ?: 0),
            'item_fund'                     => (float) ($this->item_fund ?: 0),
            'item_fund_mode'                => $this->item_fund_mode,
            'customer_reimbursement_method' => $this->customer_reimbursement_method,
            'advance_limit'                 => (float) ($this->advance_limit ?: 100000),
            'pickup_latitude'               => $this->pickup_latitude,
            'pickup_longitude'              => $this->pickup_longitude,
            'delivery_latitude'             => $this->delivery_latitude,
            'delivery_longitude'            => $this->delivery_longitude,
        ]);

        $totalAmount = (float) $estimate['total_amount'];

        // Validasi saldo customer mencukupi total pembayaran
        $customer        = auth()->user();
        $customerBalance = \App\Models\UserBalance::where('user_id', $customer->id)->first();
        $currentBalance  = $customerBalance ? (float) $customerBalance->balance : 0;

        if ($currentBalance < $totalAmount) {
            $this->addError('amount', 'Saldo tidak mencukupi. Total yang dibutuhkan (termasuk kompensasi perjalanan & biaya layanan): Rp ' . number_format($totalAmount, 0, ',', '.') . '. Saldo Anda: Rp ' . number_format($currentBalance, 0, ',', '.') . '. Silakan top up terlebih dahulu.');
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

            if ($scheduledAt->lt(Carbon::now()->subMinutes(5))) {
                $this->addError('scheduled_date', 'Waktu jadwal tidak boleh berada di masa lalu');
                $this->dispatch('scroll-to-first-error');
                return;
            }
        }

        // Confirm modal data
        $this->confirmServiceFee     = $estimate['service_fee'];
        $this->confirmTravelFee      = $estimate['travel_fee'];
        $this->confirmMaterialFee    = $estimate['material_fee'];
        $this->confirmItemFund       = $estimate['item_fund'];
        $this->confirmAmount         = $estimate['service_fee'];
        $this->confirmAdminFee       = $estimate['platform_fee'];
        $this->confirmTotal          = $totalAmount;
        $this->confirmPlatformFee    = $estimate['platform_fee'];
        $this->confirmFeeLabel       = 'Rp ' . number_format($estimate['platform_fee'], 0, ',', '.');
        $this->confirmMitraEarning   = $estimate['mitra_earning'];
        $this->confirmMinServiceFee  = $estimate['minimum_service_fee'];
        $this->confirmScheduled      = $this->scheduled_date
            ? (date('d M Y', strtotime($this->scheduled_date)) . ($this->scheduled_time ? ' Pukul ' . $this->scheduled_time . ' ' . $this->timezoneLabel : ''))
            : null;

        $expiresAt = $this->computeExpiresAt();
        $this->confirmExpiresAt = $expiresAt->translatedFormat('d M Y, H:i') . ' ' . $this->timezoneLabel;

        $this->showConfirmModal = true;
    }

    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SAVE — Model v3: Escrow Lock + Dynamic Pricing & Timestamps
    // ─────────────────────────────────────────────────────────────────────────

    public function save()
    {
        if (auth()->user()->isShadowBanned()) {
            $this->addError('amount', 'Akun Anda saat ini dibatasi dari membuat pesanan bantuan baru karena dalam peninjauan moderasi.');
            return;
        }

        $this->rules['city_id']     = 'required|exists:cities,id';
        $this->rules['district_id'] = 'required|exists:districts,id';
        $this->rules['latitude']    = 'required|numeric|between:-90,90';
        $this->rules['longitude']   = 'required|numeric|between:-180,180';
        $this->validate();

        $userId   = auth()->id();
        $customer = auth()->user();

        // 1. Jalankan Kalkulasi Pricing Engine (Fase 1)
        $pricingService = app(\App\Services\HelpPricingService::class);
        $estimate = $pricingService->calculateInitialOrderEstimate([
            'service_type'                  => $this->service_type,
            'service_category'              => $this->service_category ?: 'general',
            'service_duration_hours'        => (float) ($this->service_duration_hours ?: 1.0),
            'amount'                        => (float) ($this->amount ?: 0),
            'material_fee'                  => (float) ($this->material_fee ?: 0),
            'item_fund'                     => (float) ($this->item_fund ?: 0),
            'item_fund_mode'                => $this->item_fund_mode,
            'customer_reimbursement_method' => $this->customer_reimbursement_method,
            'advance_limit'                 => (float) ($this->advance_limit ?: 100000),
            'pickup_latitude'               => $this->pickup_latitude,
            'pickup_longitude'              => $this->pickup_longitude,
            'delivery_latitude'             => $this->delivery_latitude,
            'delivery_longitude'            => $this->delivery_longitude,
        ]);

        $totalAmount = (float) $estimate['total_amount'];

        // Validasi saldo customer mencukupi total pembayaran
        $customerBalance = \App\Models\UserBalance::where('user_id', $userId)->first();
        $currentBalance  = $customerBalance ? (float) $customerBalance->balance : 0;

        if ($currentBalance < $totalAmount) {
            $this->addError('amount', 'Saldo tidak mencukupi. Total yang dibutuhkan: Rp ' . number_format($totalAmount, 0, ',', '.') . '. Saldo Anda: Rp ' . number_format($currentBalance, 0, ',', '.') . '. Silakan top up terlebih dahulu.');
            return;
        }

        // 2. Jalankan Schedule Engine
        $targetScheduledAt = null;
        if ($this->scheduled_date) {
            $time = $this->scheduled_time ?: '08:00';
            $targetScheduledAt = Carbon::parse($this->scheduled_date . ' ' . $time);
        }

        $scheduleService = app(\App\Services\HelpScheduleService::class);
        $orderMode = $targetScheduledAt ? Help::ORDER_MODE_SCHEDULED : Help::ORDER_MODE_INSTANT;
        $scheduleData = $scheduleService->computeScheduleTimestamps(
            $orderMode,
            $targetScheduledAt,
            0.0,
            $this->service_type,
            (float) ($estimate['service_route_distance_km'] ?? 0)
        );

        $expiresAt = $this->computeExpiresAt();

        $createdHelp = DB::transaction(function () use ($userId, $customer, $estimate, $totalAmount, $scheduleData, $expiresAt) {
            $photoPath = $this->photo ? $this->photo->store('helps', 'public') : null;
            $orderId   = $this->generateOrderId();

            // Simpan data bantuan dengan rincian model v3 (Layanan, Jarak, Multi-Point, dan Item Fund)
            $help = Help::create([
                'user_id'                       => $userId,
                'order_id'                      => $orderId,
                'city_id'                       => $this->city_id,
                'district_id'                   => $this->district_id ?: null,
                'title'                         => $this->title,
                'service_type'                  => $this->service_type,
                'service_stage'                 => null,
                'order_mode'                    => $scheduleData['order_mode'],
                'service_category'              => $this->service_category ?: 'general',
                'service_duration_hours'        => (float) ($this->service_duration_hours ?: 1.0),
                'amount'                        => $estimate['service_fee'] + $estimate['travel_fee'] + $estimate['material_fee'],
                'service_fee'                   => $estimate['service_fee'],
                'travel_fee'                    => $estimate['travel_fee'],
                'material_fee'                  => $estimate['material_fee'],
                'item_fund'                     => $estimate['item_fund'],
                'item_fund_mode'                => $this->item_fund_mode,
                'customer_reimbursement_method' => $this->customer_reimbursement_method,
                'advance_limit'                 => (float) ($this->advance_limit ?: 100000),
                'minimum_service_fee'           => $estimate['minimum_service_fee'],
                'minimum_order_value'           => $estimate['minimum_order_value'],
                'admin_fee'                     => $estimate['platform_fee'],
                'platform_fee_amount'           => $estimate['platform_fee'],
                'total_amount'                  => $totalAmount,
                'mitra_earning'                 => $estimate['mitra_earning'],
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
                'store_name'                    => $this->store_name,
                'store_address'                 => $this->store_address,
                'store_latitude'                => $this->store_latitude,
                'store_longitude'               => $this->store_longitude,
                'scheduled_at'                  => $scheduleData['service_scheduled_at'],
                'published_at'                  => $scheduleData['published_at'],
                'departure_at'                  => $scheduleData['departure_at'],
                'service_scheduled_at'          => $scheduleData['service_scheduled_at'],
                'pickup_scheduled_at'           => $scheduleData['pickup_scheduled_at'],
                'delivery_deadline_at'          => $scheduleData['delivery_deadline_at'],
                'expires_at'                    => $expiresAt->format('Y-m-d H:i:s'),
                'photo'                         => $photoPath,
                'status'                        => Help::STATUS_MENUNGGU_MITRA,
                'payment_status'                => Help::PAYMENT_STATUS_PAID,
                'escrow_status'                 => Help::ESCROW_STATUS_HELD,
                'dispatch_mode'                 => Help::DISPATCH_MODE_SEEKING,
                'rating_status'                 => Help::RATING_STATUS_PENDING,
                'model_version'                 => 3,
                'escrow_locked_at'              => now(),
            ]);

            // Escrow Lock: tahan dana total customer ke Holding
            $customerBalance = \App\Models\UserBalance::firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0]
            );

            $descParts = [];
            $descParts[] = "Jasa: Rp " . number_format($estimate['service_fee'], 0, ',', '.');
            if (($estimate['travel_fee'] ?? 0) > 0) {
                $descParts[] = "Ongkos: Rp " . number_format($estimate['travel_fee'], 0, ',', '.');
            }
            if (($estimate['item_fund'] ?? 0) > 0) {
                $descParts[] = "Titipan Belanja: Rp " . number_format($estimate['item_fund'], 0, ',', '.');
            }
            $descParts[] = "Layanan: Rp " . number_format($estimate['platform_fee'], 0, ',', '.');
            $lockDescription = "Dana Ditahan untuk Permintaan Bantuan '{$help->title}' (" . implode(' + ', $descParts) . ")";

            $escrowTx = $customerBalance->lockForEscrow(
                $totalAmount,
                $help->id,
                $help->order_id,
                $lockDescription
            );
            $help->update(['escrow_transaction_id' => $escrowTx->id]);

            return $help;
        });

        // POST-COMMIT: Picu Sequential Matching Engine jika order siap dicocokkan
        if ($createdHelp) {
            try {
                app(\App\Services\HelpMatchingService::class)->initiateMatching($createdHelp);
            } catch (\Throwable $e) {
                Log::error('[Customer/Helps/Create] Gagal initiate matching: ' . $e->getMessage(), [
                    'help_id' => $createdHelp->id,
                ]);
            }
        }

        $this->dispatch('draft-cleared');
        session()->flash('message', 'Permintaan bantuan berhasil dibuat! Sistem sedang mencari Rekan Jasa terdekat untuk Anda.');
        return redirect()->route('customer.helps.index');
    }

    /**
     * Pulihkan data draf dari localStorage/cookie jika halaman ter-refresh.
     */
    public function restoreDraft(array $data): void
    {
        if (isset($data['title']) && is_string($data['title']) && !empty($data['title'])) $this->title = $data['title'];
        if (isset($data['amount']) && is_numeric($data['amount'])) $this->amount = (int) $data['amount'];
        if (isset($data['city_id']) && !empty($data['city_id'])) {
            $this->setCityId($data['city_id']);
        }
        if (isset($data['district_id']) && !empty($data['district_id'])) {
            $this->setDistrictId($data['district_id']);
        }
        if (isset($data['cityQuery']) && is_string($data['cityQuery']) && empty($this->cityQuery)) {
            $this->cityQuery = $data['cityQuery'];
        }
        if (isset($data['location']) && is_string($data['location']) && !empty($data['location'])) $this->location = $data['location'];
        if (isset($data['full_address']) && is_string($data['full_address']) && !empty($data['full_address'])) $this->full_address = $data['full_address'];
        if (isset($data['scheduled_date']) && is_string($data['scheduled_date']) && !empty($data['scheduled_date'])) $this->scheduled_date = $data['scheduled_date'];
        if (isset($data['scheduled_time']) && is_string($data['scheduled_time']) && !empty($data['scheduled_time'])) $this->scheduled_time = $data['scheduled_time'];
        if (isset($data['description']) && is_string($data['description']) && !empty($data['description'])) $this->description = $data['description'];
        if (isset($data['equipment_provided']) && is_string($data['equipment_provided']) && !empty($data['equipment_provided'])) $this->equipment_provided = $data['equipment_provided'];
        if (isset($data['latitude']) && is_numeric($data['latitude'])) $this->latitude = $data['latitude'];
        if (isset($data['longitude']) && is_numeric($data['longitude'])) $this->longitude = $data['longitude'];
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
        $this->minHelpNominal = (int) AppSetting::get('min_help_nominal', 10000);

        return view('livewire.customer.helps.create', [
            'cities' => City::where('is_active', true)->get(),
            'minHelpNominal' => $this->minHelpNominal,
        ]);
    }
}
