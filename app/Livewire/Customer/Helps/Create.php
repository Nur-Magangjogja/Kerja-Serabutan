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

    // ─── Form fields ─────────────────────────────────────────────────────────
    public $title              = '';
    public $description        = '';
    public $equipment_provided = '';
    public $amount             = '';
    public $minHelpNominal     = 10000;
    public $city_id            = '';
    public $cityQuery          = '';
    public $searchResults      = [];
    public $location           = '';
    public $full_address       = '';
    public $latitude           = null;
    public $longitude          = null;
    public $photo;

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

    // ─── Confirm modal (model v2: tampilkan komisi + earning + deadline) ────────
    public $showConfirmModal      = false;
    public $confirmAmount         = 0; // Nilai tugas = total yang didebit dari customer
    public $confirmAdminFee       = 0; // Legacy — selalu 0 di model v2
    public $confirmTotal          = 0; // = confirmAmount (Seller-Pays: customer bayar full)
    public $confirmScheduled      = null;
    public $confirmExpiresAt      = null;
    // Model v2 — transparansi komisi
    public $confirmCommissionRate = 0;  // persentase, contoh: 10
    public $confirmPlatformFee    = 0;  // nominal komisi platform
    public $confirmFeeType        = 'fixed'; // 'fixed'
    public $confirmFeeLabel       = 'Rp 2.000'; // e.g. 'Rp 2.000'
    public $confirmMitraEarning   = 0;  // nominal bersih mitra

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

        // Otomatis isi kota / kabupaten dari akun Customer
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

            // Otomatis isi alamat lengkap jika sudah ada di profil customer
            if (empty($this->full_address)) {
                if (!empty($user->full_address) && $user->full_address !== '—') {
                    $this->full_address = $user->full_address;
                } elseif (!empty($user->address)) {
                    $this->full_address = $user->address;
                }
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

            $zone              = $this->computeTimezoneLabelFromCity($city);
            $iana              = $this->ianaForZone($zone);
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
        $this->searchResults = [];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REQ DISTRICT SELECTORS
    // ─────────────────────────────────────────────────────────────────────────

    public function updatedReqProvinceId($value)
    {
        if (!Schema::hasTable('req_regencies') || empty($value)) {
            $this->req_regencies  = [];
            $this->req_regency_id = '';
            $this->req_districts  = [];
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
        'location'           => 'nullable|string|max:255',
        'full_address'       => 'nullable|string|max:1000',
        'latitude'           => 'required|numeric|between:-90,90',
        'longitude'          => 'required|numeric|between:-180,180',
        'photo'              => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'scheduled_date'     => 'nullable|date',
        'scheduled_time'     => ['nullable', 'regex:/^(?:[0-1]?\d|2[0-3]):[0-5]\d$/'],
    ];

    protected $messages = [
        'title.required'       => 'Judul bantuan wajib diisi',
        'description.required' => 'Deskripsi bantuan wajib diisi',
        'city_id.required'     => 'Silakan pilih kota lokasi bantuan',
        'city_id.exists'       => 'Kota yang dipilih tidak valid atau belum terdaftar',
        'amount.required'      => 'Nominal uang harus diisi',
        'amount.numeric'       => 'Nominal harus berupa angka',
        'amount.min'           => 'Nominal tidak boleh kurang dari nilai minimal yang ditetapkan',
        'amount.max'           => 'Nominal maksimal Rp 100.000.000',
        'latitude.required'    => 'Titik lokasi pada peta wajib ditentukan. Silakan klik pada peta atau gunakan tombol GPS.',
        'longitude.required'   => 'Titik lokasi pada peta wajib ditentukan. Silakan klik pada peta atau gunakan tombol GPS.',
        'scheduled_date.date'  => 'Format tanggal tidak valid',
        'scheduled_time.regex' => 'Format waktu tidak valid. Gunakan format 24-jam HH:MM, contoh: 9:30 atau 09:30',
        'photo.image'          => 'File harus berupa gambar (JPG, PNG, JPEG)',
        'photo.max'            => 'Ukuran foto maksimal 2MB',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // CONFIRM MODAL — tanpa cek saldo
    // ─────────────────────────────────────────────────────────────────────────

    public function prepareConfirm()
    {
        if (!auth()->check() || !auth()->user()->isCustomer()) {
            abort(403, 'Akses ditolak. Hanya akun Customer yang dapat membuat permintaan bantuan.');
        }

        $this->minHelpNominal = (int) AppSetting::get('min_help_nominal', 10000);
        $minNominal           = (float) $this->minHelpNominal;

        $this->rules['amount']      = 'required|numeric|min:' . $minNominal . '|max:100000000';
        $this->rules['city_id']     = 'required|exists:cities,id';
        $this->rules['title']       = 'required|string|max:255';
        $this->rules['description'] = 'required|string';
        $this->rules['latitude']    = 'required|numeric|between:-90,90';
        $this->rules['longitude']   = 'required|numeric|between:-180,180';
        
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
        } elseif (!empty($this->scheduled_time)) {
            $this->addError('scheduled_date', 'Silakan pilih tanggal untuk jadwal bantuan');
            $this->dispatch('scroll-to-first-error');
            return;
        }

        $amount      = (float) $this->amount;
        $calc        = AppSetting::calculatePlatformFee($amount);
        $feeAmount   = $calc['fee_amount'];
        $commissionRate = $calc['rate'];
        $totalAmount = $amount + $feeAmount;
        $earning     = $amount; // Mitra menerima 100% penuh tanpa potongan komisi

        // Validasi saldo customer mencukupi total pembayaran (nominal + biaya layanan platform)
        $customer        = auth()->user();
        $customerBalance = \App\Models\UserBalance::where('user_id', $customer->id)->first();
        $currentBalance  = $customerBalance ? (float) $customerBalance->balance : 0;

        if ($currentBalance < $totalAmount) {
            $this->addError('amount', 'Saldo tidak mencukupi. Total yang dibutuhkan (termasuk biaya layanan platform): Rp ' . number_format($totalAmount, 0, ',', '.') . '. Saldo Anda: Rp ' . number_format($currentBalance, 0, ',', '.') . '. Silakan top up terlebih dahulu.');
            $this->dispatch('scroll-to-first-error');
            return;
        }

        // Confirm modal data (Customer-Pays: customer membayar imbalan + biaya layanan platform)
        $this->confirmAmount         = $amount;
        $this->confirmAdminFee       = $feeAmount;
        $this->confirmTotal          = $totalAmount;
        $this->confirmCommissionRate = $commissionRate;
        $this->confirmPlatformFee    = $feeAmount;
        $this->confirmFeeLabel       = $calc['label'];
        $this->confirmFeeType        = $calc['type'];
        $this->confirmMitraEarning   = $earning;
        $this->confirmScheduled      = $this->scheduled_date
            ? (date('d M Y', strtotime($this->scheduled_date)) . ($this->scheduled_time ? ' Pukul ' . $this->scheduled_time . ' ' . $this->timezoneLabel : ''))
            : null;

        // Validasi dan normalisasi batas waktu pencarian (auto-cancel expiry)
        $expiresAt = $this->computeExpiresAt();
        if ($this->expiry_option === 'custom') {
            if (empty($this->custom_expiry_date)) {
                $this->addError('custom_expiry_date', 'Silakan pilih tanggal batas waktu pencarian bantuan');
                $this->dispatch('scroll-to-first-error');
                return;
            }
            if ($expiresAt->lt(Carbon::now()->addMinutes(10))) {
                $this->addError('custom_expiry_date', 'Batas waktu minimal 10 menit dari sekarang');
                $this->dispatch('scroll-to-first-error');
                return;
            }
        }
        $this->confirmExpiresAt = $expiresAt->translatedFormat('d M Y, H:i') . ' ' . $this->timezoneLabel;

        $this->showConfirmModal = true;
    }

    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SAVE — Model v2: Escrow Lock + Biaya Layanan Platform dari Customer
    // ─────────────────────────────────────────────────────────────────────────

    public function save()
    {
        if (auth()->user()->isShadowBanned()) {
            $this->addError('amount', 'Akun Anda saat ini dibatasi dari membuat pesanan bantuan baru karena dalam peninjauan moderasi.');
            return;
        }

        $this->minHelpNominal = (int) AppSetting::get('min_help_nominal', 10000);
        $minNominal           = (float) $this->minHelpNominal;

        $this->rules['amount']      = 'required|numeric|min:' . $minNominal . '|max:100000000';
        $this->rules['latitude']    = 'required|numeric|between:-90,90';
        $this->rules['longitude']   = 'required|numeric|between:-180,180';
        $this->validate();

        $userId      = auth()->id();
        $customer    = auth()->user();
        $amount      = (float) $this->amount;
        $calc        = AppSetting::calculatePlatformFee($amount);
        $feeAmount   = $calc['fee_amount'];
        $commissionRate = $calc['rate'];
        $totalAmount = $amount + $feeAmount;
        $earning     = $amount; // Mitra menerima 100% penuh tanpa potongan

        // Validasi saldo customer mencukupi total pembayaran
        $customerBalance = \App\Models\UserBalance::where('user_id', $userId)->first();
        $currentBalance  = $customerBalance ? (float) $customerBalance->balance : 0;

        if ($currentBalance < $totalAmount) {
            $this->addError('amount', 'Saldo tidak mencukupi. Total yang dibutuhkan: Rp ' . number_format($totalAmount, 0, ',', '.') . '. Saldo Anda: Rp ' . number_format($currentBalance, 0, ',', '.') . '. Silakan top up terlebih dahulu.');
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
                return;
            }
        } elseif (!empty($this->scheduled_time)) {
            $this->addError('scheduled_date', 'Silakan pilih tanggal untuk jadwal bantuan');
            return;
        }

        $expiresAt = $this->computeExpiresAt();
        if ($this->expiry_option === 'custom' && $expiresAt->lt(Carbon::now()->addMinutes(5))) {
            $this->addError('custom_expiry_date', 'Batas waktu pencarian tidak boleh berada di masa lalu');
            return;
        }

        $createdHelp = DB::transaction(function () use ($userId, $customer, $amount, $commissionRate, $feeAmount, $totalAmount, $earning, $expiresAt) {
            $photoPath   = $this->photo ? $this->photo->store('helps', 'public') : null;
            $orderId     = $this->generateOrderId();
            $scheduledAt = null;
            if ($this->scheduled_date) {
                $time        = $this->scheduled_time ?: '08:00';
                $scheduledAt = date('Y-m-d H:i:s', strtotime($this->scheduled_date . ' ' . $time));
            }

            // Simpan data bantuan dengan rincian biaya layanan platform & batas waktu pembatalan otomatis
            $help = Help::create([
                'user_id'                    => $userId,
                'order_id'                   => $orderId,
                'city_id'                    => $this->city_id,
                'title'                      => $this->title,
                'amount'                     => $amount,
                'admin_fee'                  => $feeAmount,
                'total_amount'               => $totalAmount,
                'description'                => $this->description,
                'equipment_provided'         => $this->equipment_provided,
                'location'                   => $this->location,
                'full_address'               => $this->full_address,
                'scheduled_at'               => $scheduledAt,
                'expires_at'                 => $expiresAt->format('Y-m-d H:i:s'),
                'latitude'                   => $this->latitude,
                'longitude'                  => $this->longitude,
                'photo'                      => $photoPath,
                'status'                     => Help::STATUS_MENUNGGU_MITRA,
                // Status Arsitektur v2.3.4
                'payment_status'             => Help::PAYMENT_STATUS_PAID,
                'escrow_status'              => Help::ESCROW_STATUS_HELD,
                'dispatch_mode'              => Help::DISPATCH_MODE_SEEKING,
                'rating_status'              => Help::RATING_STATUS_PENDING,
                // Kolom model v2
                'model_version'              => 2,
                'platform_commission_rate'   => $commissionRate,
                'platform_fee_amount'        => $feeAmount,
                'mitra_earning'              => $earning,
                'escrow_locked_at'           => now(),
            ]);

            // Escrow Lock: tahan dana total customer (nominal bantuan + biaya layanan) ke Holding
            $customerBalance = \App\Models\UserBalance::firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0]
            );
            $escrowTx = $customerBalance->lockForEscrow(
                $totalAmount,
                $help->id,
                $help->order_id,
                "Dana Ditahan untuk Permintaan Bantuan '{$help->title}' (Nilai Jasa: Rp " . number_format($amount, 0, ',', '.') . " + Biaya Layanan Platform: Rp " . number_format($feeAmount, 0, ',', '.') . ")"
            );
            $help->update(['escrow_transaction_id' => $escrowTx->id]);

            return $help;
        });

        // POST-COMMIT: Picu Sequential Matching Engine jika order siap dicocokkan
        if ($createdHelp) {
            try {
                app(\App\Services\HelpMatchingService::class)->initiateMatching($createdHelp);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('[Customer/Helps/Create] Gagal initiate matching: ' . $e->getMessage(), [
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
