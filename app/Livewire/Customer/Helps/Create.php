<?php

namespace App\Livewire\Customer\Helps;

use App\Models\City;
use App\Models\Help;
use App\Models\PartnerActivity;
use App\Models\UserBalance;
use App\Models\AppSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithFileUploads;
use Carbon\Carbon;

class Create extends Component
{
    use WithFileUploads;

    public $title = '';
    public $description = '';
    public $equipment_provided = '';
    public $amount = '';
    public $city_id = '';
    public $cityQuery = '';
    public $searchResults = [];
    // Req tables selectors
    public $req_province_id = '';
    public $req_regency_id = '';
    public $req_district_id = '';
    public $req_provinces = [];
    public $req_regencies = [];
    public $req_districts = [];
    public $location = '';
    public $full_address = '';
    public $latitude = null;
    public $longitude = null;
    // Scheduling
    public $scheduled_date = null; // YYYY-MM-DD
    public $scheduled_time = null; // HH:MM
    public $timezoneLabel = 'WIB';
    public $timezoneIana = 'Asia/Jakarta';
    public $photo;
    public $showInsufficientModal = false;
    public $insufficientMessage = '';
    public $showConfirmModal = false;
    public $confirmAmount = 0;
    public $confirmAdminFee = 0;
    public $confirmTotal = 0;
    public $currentBalance = 0;
    public $confirmScheduled = null;

    protected $listeners = [
        'citySelected' => 'setCityId',
    ];

    public function mount()
    {
        // Guard against missing req_* tables (some installs may not have these import tables)
        if (Schema::hasTable('req_provinces')) {
            $this->req_provinces = \Illuminate\Support\Facades\DB::table('req_provinces')->orderBy('province')->get()->toArray();
        } else {
            $this->req_provinces = [];
        }
    }

    public function updatedReqProvinceId($value)
    {
        if (! Schema::hasTable('req_regencies') || empty($value)) {
            $this->req_regencies = [];
            $this->req_regency_id = '';
            $this->req_districts = [];
            $this->req_district_id = '';
            return;
        }
        $this->req_regencies = \Illuminate\Support\Facades\DB::table('req_regencies')
            ->where('province_id', $value)
            ->orderBy('regency')
            ->get()
            ->toArray();
        $this->req_regency_id = '';
        $this->req_districts = [];
        $this->req_district_id = '';
    }

    public function updatedReqRegencyId($value)
    {
        if (! Schema::hasTable('req_districts') || empty($value)) {
            $this->req_districts = [];
            $this->req_district_id = '';
            return;
        }
        $this->req_districts = \Illuminate\Support\Facades\DB::table('req_districts')
            ->where('regency_id', $value)
            ->orderBy('district')
            ->get()
            ->toArray();
        $this->req_district_id = '';
    }

    public function selectReqDistrict($districtId)
    {
        if (! Schema::hasTable('req_districts') || ! Schema::hasTable('req_regencies') || ! Schema::hasTable('req_provinces')) {
            return;
        }

        $row = \Illuminate\Support\Facades\DB::table('req_districts')
            ->join('req_regencies', 'req_districts.regency_id', '=', 'req_regencies.id')
            ->join('req_provinces', 'req_regencies.province_id', '=', 'req_provinces.id')
            ->where('req_districts.id', $districtId)
            ->select('req_districts.id as district_id', 'req_districts.district', 'req_regencies.id as regency_id', 'req_regencies.regency', 'req_provinces.province')
            ->first();

        if (! $row) {
            return;
        }

        // Prefer mapping the district to its parent regency (so Mitra filtered by regency/city will match).
        $regencyCode = 'reqr-' . $row->regency_id;
        $city = City::where('code', $regencyCode)->first();
        if (! $city) {
            // create a regency-level city record if not exists
            $city = City::create([
                'name' => $row->regency,
                'province' => $row->province,
                'type' => null,
                'is_active' => true,
                'code' => $regencyCode,
            ]);
        }

        $this->city_id = $city->id;
        $this->cityQuery = $row->district . ', ' . $row->regency . ', ' . $row->province;
        $this->searchResults = [];

        // set timezone based on selected district's city/province and notify frontend
        $zone = $this->computeTimezoneLabelFromCity($city);
        $iana = $this->ianaForZone($zone);
        $this->timezoneLabel = $zone;
        $this->timezoneIana = $iana;
        $this->dispatch('help:timezone-changed', zone: $zone, iana: $iana);

        // set selected req_* ids to reflect selection
        $this->req_district_id = $row->district_id;
        // find regency and province ids
        $reg = \Illuminate\Support\Facades\DB::table('req_regencies')->where('regency', $row->regency)->first();
        if ($reg) {
            $this->req_regency_id = $reg->id;
            $this->req_regencies = \Illuminate\Support\Facades\DB::table('req_regencies')->where('province_id', $reg->province_id)->orderBy('regency')->get()->toArray();
            $this->req_province_id = $reg->province_id;
            $this->req_provinces = \Illuminate\Support\Facades\DB::table('req_provinces')->orderBy('province')->get()->toArray();
            $this->req_districts = \Illuminate\Support\Facades\DB::table('req_districts')->where('regency_id', $reg->id)->orderBy('district')->get()->toArray();
        }
    }

    public function updatedCityQuery($value)
    {
        $q = trim($value);

        \Illuminate\Support\Facades\Log::info('Livewire updatedCityQuery called', ['q' => $q]);

        if ($q === '') {
            $this->searchResults = [];
            return;
        }

        $limit = 10;

        $results = City::where('is_active', true)
            ->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                        ->orWhere('province', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
            })
            // Prefer regency-level cities: ignore existing district-coded rows
            ->whereRaw("COALESCE(code,'') NOT LIKE 'reqd-%' AND COALESCE(code,'') NOT LIKE 'regd-%'")
            ->select('id', 'name', 'province', 'code')
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->toArray();

        // If not enough results, also search imported regencies/provinces
        if (count($results) < $limit) {
            $remaining = $limit - count($results);

            // Prefer req_* tables if present
            $regRows = collect();

            if (Schema::hasTable('req_regencies') && Schema::hasTable('req_provinces')) {
                $regRows = \Illuminate\Support\Facades\DB::table('req_regencies')
                    ->join('req_provinces', 'req_regencies.province_id', '=', 'req_provinces.id')
                    ->where(function ($builder) use ($q) {
                        $builder->where('req_regencies.regency', 'like', "%{$q}%")
                                ->orWhere('req_provinces.province', 'like', "%{$q}%");
                    })
                    ->select('req_regencies.id as regency_id', 'req_regencies.regency', 'req_regencies.type', 'req_provinces.province')
                    ->orderBy('req_regencies.regency')
                    ->limit($remaining)
                    ->get();
            }

            // Also search kecamatan-level tables if present (match district names)
            if (count($regRows) < $remaining && Schema::hasTable('req_districts') && Schema::hasTable('req_regencies') && Schema::hasTable('req_provinces')) {
                $remaining2 = $remaining - count($regRows);
                $distRows = \Illuminate\Support\Facades\DB::table('req_districts')
                    ->join('req_regencies', 'req_districts.regency_id', '=', 'req_regencies.id')
                    ->join('req_provinces', 'req_regencies.province_id', '=', 'req_provinces.id')
                    ->where(function ($builder) use ($q) {
                        $builder->where('req_districts.district', 'like', "%{$q}%")
                                ->orWhere('req_regencies.regency', 'like', "%{$q}%")
                                ->orWhere('req_provinces.province', 'like', "%{$q}%");
                    })
                    ->select(\Illuminate\Support\Facades\DB::raw("CONCAT('reqd-', req_districts.id) as regency_id"), 'req_districts.district as regency', 'req_regencies.regency as parent_regency', \Illuminate\Support\Facades\DB::raw('null as type'), 'req_provinces.province')
                    ->orderBy('req_districts.district')
                    ->limit($remaining2)
                    ->get();

                foreach ($distRows as $r) {
                    $regRows->push($r);
                }
            }

            // Also support legacy 'reg_districts' / 'reg_regencies' / 'reg_provinces' naming
            if (count($regRows) < $remaining && Schema::hasTable('reg_districts') && Schema::hasTable('reg_regencies') && Schema::hasTable('reg_provinces')) {
                $remaining3 = $remaining - count($regRows);
                $rows = \Illuminate\Support\Facades\DB::table('reg_districts')
                    ->join('reg_regencies', 'reg_districts.regency_id', '=', 'reg_regencies.id')
                    ->join('reg_provinces', 'reg_regencies.province_id', '=', 'reg_provinces.id')
                    ->where(function ($builder) use ($q) {
                        $builder->where('reg_districts.name', 'like', "%{$q}%")
                                ->orWhere('reg_regencies.name', 'like', "%{$q}%")
                                ->orWhere('reg_provinces.name', 'like', "%{$q}%");
                    })
                    ->select(\Illuminate\Support\Facades\DB::raw("CONCAT('regd-', reg_districts.id) as regency_id"), 'reg_districts.name as regency', 'reg_regencies.name as parent_regency', \Illuminate\Support\Facades\DB::raw('null as type'), 'reg_provinces.name as province')
                    ->orderBy('reg_districts.name')
                    ->limit($remaining3)
                    ->get();

                foreach ($rows as $r) {
                    $regRows->push($r);
                }
            }

            // If still not enough, try reg_regencies/reg_provinces (older import naming)
            if (count($regRows) < $remaining && Schema::hasTable('reg_regencies') && Schema::hasTable('reg_provinces')) {
                $remaining2 = $remaining - count($regRows);
                $rows = \Illuminate\Support\Facades\DB::table('reg_regencies')
                    ->join('reg_provinces', 'reg_regencies.province_id', '=', 'reg_provinces.id')
                    ->where(function ($builder) use ($q) {
                        $builder->where('reg_regencies.name', 'like', "%{$q}%")
                                ->orWhere('reg_provinces.name', 'like', "%{$q}%");
                    })
                    ->select('reg_regencies.id as regency_id', 'reg_regencies.name as regency', \Illuminate\Support\Facades\DB::raw('null as type'), 'reg_provinces.name as province')
                    ->orderBy('reg_regencies.name')
                    ->limit($remaining2)
                    ->get();

                foreach ($rows as $r) {
                    $regRows->push($r);
                }
            }

            // As a last fallback, try legacy regencies/provinces tables if they exist
            if (count($regRows) < $remaining && Schema::hasTable('regencies') && Schema::hasTable('provinces')) {
                $remaining3 = $remaining - count($regRows);
                $rows = \Illuminate\Support\Facades\DB::table('regencies')
                    ->join('provinces', 'regencies.province_id', '=', 'provinces.id')
                    ->where(function ($builder) use ($q) {
                        $builder->where('regencies.regency', 'like', "%{$q}%")
                                ->orWhere('provinces.province', 'like', "%{$q}%");
                    })
                    ->select('regencies.id as regency_id', 'regencies.regency', 'regencies.type', 'provinces.province')
                    ->orderBy('regencies.regency')
                    ->limit($remaining3)
                    ->get();

                foreach ($rows as $r) {
                    $regRows->push($r);
                }
            }

            foreach ($regRows as $r) {
                // If this row represents a district (reqd-/regd-) prefer to map
                // selection to the parent regency (kabupaten/kota) so stored
                // `city_id` matches mitra's city. We still provide a display
                // string that includes the district name for UX.
                $regencyIdStr = is_string($r->regency_id) ? $r->regency_id : (string) $r->regency_id;

                $display = null;
                $targetCity = null;

                if (strpos($regencyIdStr, 'reqd-') === 0) {
                    // req_districts -> find parent regency id
                    try {
                        $did = substr($regencyIdStr, 5);
                        $parentRow = \Illuminate\Support\Facades\DB::table('req_districts')
                            ->join('req_regencies','req_districts.regency_id','=','req_regencies.id')
                            ->join('req_provinces','req_regencies.province_id','=','req_provinces.id')
                            ->where('req_districts.id', $did)
                            ->select('req_districts.district as district','req_regencies.id as parent_regency_id','req_regencies.regency as parent_regency','req_provinces.province')
                            ->first();
                        if ($parentRow) {
                            $parentCode = 'reqr-' . $parentRow->parent_regency_id;
                            $targetCity = City::firstOrCreate(
                                ['code' => $parentCode],
                                ['name' => $parentRow->parent_regency, 'province' => $parentRow->province, 'is_active' => true]
                            );
                            $display = $parentRow->district . ', ' . $parentRow->parent_regency . ', ' . $parentRow->province;
                        }
                    } catch (\Throwable $e) {
                        // fall back to using the original row values
                    }
                } elseif (strpos($regencyIdStr, 'regd-') === 0) {
                    // reg_districts legacy
                    try {
                        $did = substr($regencyIdStr, 5);
                        $parentRow = \Illuminate\Support\Facades\DB::table('reg_districts')
                            ->join('reg_regencies','reg_districts.regency_id','=','reg_regencies.id')
                            ->join('reg_provinces','reg_regencies.province_id','=','reg_provinces.id')
                            ->where('reg_districts.id', $did)
                            ->select('reg_districts.name as district','reg_regencies.id as parent_regency_id','reg_regencies.name as parent_regency','reg_provinces.name as province')
                            ->first();
                        if ($parentRow) {
                            $parentCode = 'reqr-' . $parentRow->parent_regency_id;
                            $targetCity = City::firstOrCreate(
                                ['code' => $parentCode],
                                ['name' => $parentRow->parent_regency, 'province' => $parentRow->province, 'is_active' => true]
                            );
                            $display = $parentRow->district . ', ' . $parentRow->parent_regency . ', ' . $parentRow->province;
                        }
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }

                if (! $targetCity) {
                    // Not a district-coded row or parent lookup failed: use the
                    // regency-level code/name as provided in $r
                    $targetCity = City::firstOrCreate(
                        ['code' => $r->regency_id],
                        ['name' => $r->regency, 'province' => $r->province, 'type' => $r->type ?? null, 'is_active' => true]
                    );
                    // if no explicit display was set, and this row actually came
                    // from a district search that didn't include parent name, try
                    // to construct a display when parent info exists
                    if (! $display) {
                        if (! empty($r->parent_regency)) {
                            $display = $r->regency . ', ' . $r->parent_regency . ', ' . $r->province;
                        }
                    }
                }

                // Ensure minimal display fallback
                if (! $display) {
                    $display = $targetCity->name . ', ' . $targetCity->province;
                }

                // Add to results if city id not already present
                $exists = false;
                foreach ($results as $res) {
                    if ($res['id'] == $targetCity->id) { $exists = true; break; }
                }
                if (! $exists) {
                    $item = ['id' => $targetCity->id, 'name' => $targetCity->name, 'province' => $targetCity->province, 'code' => $targetCity->code];
                    if ($display) $item['display'] = $display;
                    $results[] = $item;
                }
            }
        }

        // Enrich results: if a result code indicates a district (reqd- or regd-),
        // fetch parent regency and province names to build a display string
        foreach ($results as $idx => $res) {
            if (! empty($res['code']) && is_string($res['code'])) {
                $code = $res['code'];
                try {
                    if (strpos($code, 'reqd-') === 0 && Schema::hasTable('req_districts')) {
                        $did = substr($code, 5);
                        $row = DB::table('req_districts')
                            ->join('req_regencies', 'req_districts.regency_id', '=', 'req_regencies.id')
                            ->join('req_provinces', 'req_regencies.province_id', '=', 'req_provinces.id')
                            ->where('req_districts.id', $did)
                            ->select('req_districts.district as district', 'req_regencies.regency as regency', 'req_provinces.province as province')
                            ->first();
                        if ($row) {
                            $results[$idx]['display'] = $row->district . ', ' . $row->regency . ', ' . $row->province;
                        }
                    } elseif (strpos($code, 'regd-') === 0 && Schema::hasTable('reg_districts')) {
                        $did = substr($code, 5);
                        $row = DB::table('reg_districts')
                            ->join('reg_regencies', 'reg_districts.regency_id', '=', 'reg_regencies.id')
                            ->join('reg_provinces', 'reg_regencies.province_id', '=', 'reg_provinces.id')
                            ->where('reg_districts.id', $did)
                            ->select('reg_districts.name as district', 'reg_regencies.name as regency', 'reg_provinces.name as province')
                            ->first();
                        if ($row) {
                            $results[$idx]['display'] = $row->district . ', ' . $row->regency . ', ' . $row->province;
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore and leave without display
                }
            }
        }

        $this->searchResults = $results;
    }

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'equipment_provided' => 'nullable|string|max:1000',
        'amount' => 'required|numeric|min:0|max:100000000',
        'city_id' => 'required|exists:cities,id',
        'location' => 'nullable|string|max:255',
        'full_address' => 'nullable|string|max:1000',
        'latitude' => 'nullable|numeric|between:-90,90',
        'longitude' => 'nullable|numeric|between:-180,180',
        'photo' => 'nullable|image|max:2048',
        'scheduled_date' => 'nullable|date',
        'scheduled_time' => ['nullable','regex:/^(?:[0-1]?\d|2[0-3]):[0-5]\d$/'],
    ];

    protected $messages = [
        'amount.required' => 'Nominal uang harus diisi',
        'amount.numeric' => 'Nominal harus berupa angka',
        'amount.min' => 'Nominal tidak boleh kurang dari nilai minimal yang ditetapkan',
        'amount.max' => 'Nominal maksimal Rp 100.000.000',
        'scheduled_date.date' => 'Format tanggal tidak valid',
        'scheduled_time.regex' => 'Format waktu tidak valid. Gunakan format 24-jam HH:MM, contoh: 9:30 atau 09:30',
    ];

    public function save()
    {
        // Finalize creation after confirmation
        // Load settings (double-check)
        $minNominal = (float) AppSetting::get('min_help_nominal', 10000);
        $adminFee = (float) AppSetting::get('admin_fee', 0);

        $this->rules['amount'] = 'required|numeric|min:' . $minNominal . '|max:100000000';
        $this->validate();

        $userId = auth()->id();
        $userBalance = UserBalance::firstOrCreate(['user_id' => $userId], ['balance' => 0]);

        $amount = (float) $this->amount;
        $total = $amount + $adminFee;

        if ($userBalance->balance < $total) {
            $this->insufficientMessage = 'Saldo Anda tidak cukup. Total yang harus dibayar: Rp ' . number_format($total, 0, ',', '.');
            $this->showInsufficientModal = true;
            return;
        }

        // Final server-side check: scheduled_at not in the past
        if ($this->scheduled_date) {
            $time = $this->scheduled_time ?: '00:00';
            $scheduledAtCheck = Carbon::parse($this->scheduled_date . ' ' . $time);
            if ($scheduledAtCheck->lt(Carbon::now())) {
                $this->addError('scheduled_date', 'Jadwal tidak boleh berada di masa lalu');
                return;
            }
        }

        // Proceed to create help and deduct balance atomically
        DB::transaction(function () use ($userId, $amount, $adminFee, $total) {
            $photoPath = null;
            if ($this->photo) {
                $photoPath = $this->photo->store('helps', 'public');
            }

            // Generate unique order id for this help
            $orderId = $this->generateOrderId();

            // Combine scheduled_date and scheduled_time into scheduled_at if provided
            $scheduledAt = null;
            if ($this->scheduled_date) {
                $time = $this->scheduled_time ?: '00:00';
                $scheduledAt = date('Y-m-d H:i:s', strtotime($this->scheduled_date . ' ' . $time));
            }

            \Log::info('Create Help - Saving to database', [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'user_id' => $userId
            ]);
            
            $help = Help::create([
                'user_id' => $userId,
                'order_id' => $orderId,
                'city_id' => $this->city_id,
                'title' => $this->title,
                'amount' => $amount,
                'admin_fee' => $adminFee,
                'total_amount' => $total,
                'description' => $this->description,
                'equipment_provided' => $this->equipment_provided,
                'location' => $this->location,
                'full_address' => $this->full_address,
                'scheduled_at' => $scheduledAt,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'photo' => $photoPath,
                'status' => 'menunggu_mitra',
            ]);
            
            \Log::info('Create Help - Saved successfully', [
                'help_id' => $help->id,
                'latitude' => $help->latitude,
                'longitude' => $help->longitude
            ]);

            // Deduct balance using UserBalance helper to keep history
            $userBalance = UserBalance::firstOrCreate(['user_id' => $userId], ['balance' => 0]);
            $userBalance->deductBalance($total, 'Pembayaran bantuan #' . $help->id, $help->id);
        });

        session()->flash('message', 'Permintaan bantuan berhasil dibuat! Saldo Anda telah dikurangi dan permintaan menunggu mitra.');

        return redirect()->route('customer.helps.index');
    }

    public function prepareConfirm()
    {
        // Load settings
        $minNominal = (float) AppSetting::get('min_help_nominal', 10000);
        $adminFee = (float) AppSetting::get('admin_fee', 0);

        $this->rules['amount'] = 'required|numeric|min:' . $minNominal . '|max:100000000';
        $this->validate();
        
        // Log koordinat untuk debugging
        \Log::info('Create Help - Koordinat diterima', [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'user_id' => auth()->id()
        ]);

        $amount = (float) $this->amount;
        $total = $amount + $adminFee;

        // Validate scheduled datetime is not in the past
        if ($this->scheduled_date) {
            $time = $this->scheduled_time ?: '00:00';
            $scheduledAt = Carbon::parse($this->scheduled_date . ' ' . $time);
            if ($scheduledAt->lt(Carbon::now())) {
                $this->addError('scheduled_date', 'Jadwal tidak boleh berada di masa lalu');
                return;
            }
        }

        $userId = auth()->id();
        $userBalance = UserBalance::firstOrCreate(['user_id' => $userId], ['balance' => 0]);

        if ($userBalance->balance < $total) {
            $this->insufficientMessage = 'Saldo Anda tidak cukup. Total yang harus dibayar: Rp ' . number_format($total, 0, ',', '.');
            $this->showInsufficientModal = true;
            return;
        }

        $this->confirmAmount = $amount;
        $this->confirmAdminFee = $adminFee;
        $this->confirmTotal = $total;
        // Prepare scheduled display
        if ($this->scheduled_date) {
            $time = $this->scheduled_time ?: '00:00';
            $this->confirmScheduled = date('d M Y H:i', strtotime($this->scheduled_date . ' ' . $time));
        } else {
            $this->confirmScheduled = null;
        }
        $this->currentBalance = $userBalance->balance ?? 0;
        $this->showConfirmModal = true;
    }

    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
    }

    public function closeInsufficientModal()
    {
        $this->showInsufficientModal = false;
        $this->insufficientMessage = '';
    }

    public function setCityId($id)
    {
        $this->city_id = $id;
        $city = City::find($id);
        if ($city) {
            // If the current search results included a custom 'display' for this id (district, regency, province), prefer it
            $usedDisplay = false;
            if (! empty($this->searchResults)) {
                foreach ($this->searchResults as $res) {
                    if (isset($res['id']) && $res['id'] == $id) {
                        if (! empty($res['display'])) {
                            // Use the custom display string (includes kecamatan, kabupaten, provinsi)
                            $this->cityQuery = $res['display'];
                            $this->searchResults = [];
                            $usedDisplay = true;
                            break;
                        }
                        break;
                    }
                }
            }

            // Only overwrite the cityQuery with the canonical city name if we
            // didn't already set a richer display value from the search result.
            if (! $usedDisplay) {
                $this->cityQuery = $city->name . ', ' . $city->province;
            }
            // determine timezone label from city (longitude preferred, fallback to province)
            $zone = $this->computeTimezoneLabelFromCity($city);
            $iana = $this->ianaForZone($zone);
            $this->timezoneLabel = $zone;
            $this->timezoneIana = $iana;
            $this->dispatch('help:timezone-changed', zone: $zone, iana: $iana);
        }
        $this->searchResults = [];
    }

    private function computeTimezoneLabelFromCity(City $city)
    {
        // Prefer longitude if available
        if (! empty($city->longitude)) {
            $lon = floatval($city->longitude);
            if ($lon >= 130) return 'WIT';
            if ($lon >= 115) return 'WITA';
            return 'WIB';
        }

        $prov = strtolower($city->province ?? '');
        // eastern provinces => WIT
        $eastern = ['papua', 'papua barat', 'maluku', 'maluku utara'];
        foreach ($eastern as $p) {
            if (strpos($prov, $p) !== false) return 'WIT';
        }

        // central provinces => WITA
        $centralKeywords = ['bali', 'nusa tenggara', 'sulawesi', 'kalimantan tengah', 'kalimantan timur', 'kalimantan selatan'];
        foreach ($centralKeywords as $p) {
            if (strpos($prov, $p) !== false) return 'WITA';
        }

        // default to WIB
        return 'WIB';
    }

    private function ianaForZone($zone)
    {
        switch ($zone) {
            case 'WITA': return 'Asia/Makassar';
            case 'WIT': return 'Asia/Jayapura';
            default: return 'Asia/Jakarta';
        }
    }

    public function clearCity()
    {
        $this->city_id = '';
        $this->cityQuery = '';
        $this->searchResults = [];
    }

    /**
     * Generate a unique order id for a Help record.
     * Format: HELP-YYYYMMDDHHIISS-<random4>
     */
    private function generateOrderId()
    {
        // Try a few times to avoid collision
        for ($i = 0; $i < 5; $i++) {
            $candidate = 'HELP-' . date('YmdHis') . '-' . random_int(1000, 9999);
            if (!Help::where('order_id', $candidate)->exists()) {
                return $candidate;
            }
            // small sleep to change timestamp if collision
            usleep(200);
        }

        // Fallback - use uniqid
        return 'HELP-' . uniqid();
    }

    public function render()
    {
        $cities = City::where('is_active', true)->get();

        return view('livewire.customer.helps.create', [
            'cities' => $cities,
        ]);
    }
}
