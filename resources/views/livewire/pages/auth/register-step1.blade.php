<?php

use App\Models\Registration;
use App\Models\City;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $nik = '';
    public string $full_name = '';
    public string $phone = '';
    public string $gender = '';
    public string $city = '';
    public ?int $city_id = null;
    public string $province = '';
    
    public $cities = [];
    // realtime city search (for nicer UX)
    public string $cityQuery = '';
    public array $searchResults = [];

    // Preload saved registration values from DB session/cookie or draft cookie
    public function mount(): void
    {
        // Always load available cities so the dropdown can be rendered from DB
        $this->cities = City::orderBy('name')->get();

        $getCookieVal = function($name) {
            return request()->cookie($name)
                ?? request()->cookies->get($name)
                ?? (Cookie::hasQueued($name) ? Cookie::queued($name)->getValue() : null)
                ?? Cookie::get($name);
        };

        $uuid = Session::get('registration_uuid') ?? $getCookieVal('registration_uuid');
        if ($uuid) {
            $registration = Registration::where('uuid', $uuid)->first();
            if ($registration) {
                Session::put('registration_uuid', $uuid);
                $this->nik = $registration->nik ?? $this->nik;
                $this->full_name = $registration->full_name ?? $this->full_name;
                $this->phone = $registration->phone ?? $this->phone;
                $this->gender = $registration->gender ?? $this->gender;
                $this->city = $registration->city ?? $this->city;
                $this->city_id = $registration->city_id ? (int) $registration->city_id : null;
                $this->province = $registration->province ?? $this->province;

                if ($this->city) {
                    $this->cityQuery = $this->city . ($this->province ? " — {$this->province}" : '');
                } elseif ($this->city_id) {
                    $c = City::find($this->city_id);
                    if ($c) {
                        $this->city = $c->name;
                        $this->province = $c->province;
                        $this->cityQuery = $c->name . ($c->province ? " — {$c->province}" : '');
                    }
                }
            }
        }

        // Pre-fill from authenticated user if available
        if (\Illuminate\Support\Facades\Auth::check()) {
            $user = \Illuminate\Support\Facades\Auth::user();

            // Pastikan email sudah terverifikasi sebelum mengisi form Step 1
            if ($user && !$user->hasVerifiedEmail()) {
                $this->redirect(route('verification.notice'), navigate: true);
                return;
            }

            // Cek jika batas waktu 1x24 jam untuk pengisian form telah kedaluwarsa
            if ($user && $user->status === 'inactive' && (empty($user->nik) || empty($user->ktp_photo))) {
                if ($user->created_at && $user->created_at->diffInHours(now()) >= 24) {
                    \App\Models\User::purgeExpiredInactive($user->email);
                    \Illuminate\Support\Facades\Auth::logout();
                    request()->session()->invalidate();
                    Session::flash('error', 'Batas waktu penyelesaian formulir pendaftaran (1x24 jam) telah kedaluwarsa. Akun otomatis dihapus, silakan lakukan pendaftaran baru.');
                    $this->redirect(route('register'), navigate: true);
                    return;
                }
            }

            if (empty($this->full_name) && !empty($user->name)) {
                $this->full_name = $user->name;
            }
            if (empty($this->phone) && !empty($user->phone)) {
                $this->phone = $user->phone;
            }
            if (empty($this->nik) && !empty($user->nik)) {
                $this->nik = $user->nik;
            }
        }

        // Restore draft from cookie if available
        $draftCookie = $getCookieVal('registration_step1_draft');
        if ($draftCookie) {
            $draft = is_string($draftCookie) ? json_decode($draftCookie, true) : (is_array($draftCookie) ? $draftCookie : null);
            if (is_array($draft)) {
                $this->nik = $draft['nik'] ?? $this->nik;
                $this->full_name = $draft['full_name'] ?? $this->full_name;
                $this->phone = $draft['phone'] ?? $this->phone;
                $this->gender = $draft['gender'] ?? $this->gender;
                $this->city = $draft['city'] ?? $this->city;
                $this->city_id = isset($draft['city_id']) && $draft['city_id'] ? (int) $draft['city_id'] : null;
                $this->province = $draft['province'] ?? $this->province;

                if (!empty($draft['cityQuery'])) {
                    $this->cityQuery = $draft['cityQuery'];
                } elseif ($this->city) {
                    $this->cityQuery = $this->city . ($this->province ? " — {$this->province}" : '');
                } elseif ($this->city_id) {
                    $c = City::find($this->city_id);
                    if ($c) {
                        $this->city = $c->name;
                        $this->province = $c->province;
                        $this->cityQuery = $c->name . ($c->province ? " — {$c->province}" : '');
                    }
                }
            }
        }
    }

    public function updated($propertyName): void
    {
        if ($propertyName === 'nik') {
            $this->nik = trim($this->nik);
            if (strlen($this->nik) >= 8) {
                $tglLahir = (int) substr($this->nik, 6, 2);
                $this->gender = $tglLahir > 40 ? 'Perempuan' : 'Laki-laki';
            }
            if (strlen($this->nik) === 16) {
                $this->validateOnly('nik', [
                    'nik' => $this->getNikRules(),
                ], $this->getValidationMessages());
            }
        }

        $draft = [
            'nik' => $this->nik,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'city_id' => $this->city_id,
            'city' => $this->city,
            'cityQuery' => $this->cityQuery,
            'province' => $this->province,
        ];
        Cookie::queue('registration_step1_draft', json_encode($draft), 60 * 24 * 7);
    }

    protected function getNikRules(): array
    {
        $authUser = \Illuminate\Support\Facades\Auth::user();
        $authId = $authUser?->id;
        $authEmail = $authUser?->email;
        $uuid = Session::get('registration_uuid') ?? request()->cookie('registration_uuid');

        return [
            'required',
            'string',
            'size:16',
            'regex:/^[0-9]+$/',
            function ($attribute, $value, $fail) use ($authId, $authEmail, $uuid) {
                // Rule: Nomor NIK harus unik untuk setiap akun pengguna
                $query = \App\Models\User::where('nik', $value);
                if ($authId) {
                    $query->where('id', '!=', $authId);
                } elseif ($authEmail) {
                    $query->where('email', '!=', $authEmail);
                }
                if ($query->exists()) {
                    $fail('Nomor NIK ini sudah terdaftar di sistem. Setiap pengguna hanya dapat memiliki 1 akun.');
                    return;
                }

                // Cek juga pendaftaran lain yang sedang in_progress
                $regQuery = Registration::where('nik', $value);
                if ($uuid) {
                    $regQuery->where('uuid', '!=', $uuid);
                }
                if ($authEmail) {
                    $regQuery->where('email', '!=', $authEmail);
                }
                if ($regQuery->whereIn('status', ['in_progress', 'pending_verification'])->exists()) {
                    $fail('Nomor NIK ini sedang dalam proses pendaftaran aktif.');
                }
            },
        ];
    }

    protected function getValidationMessages(): array
    {
        return [
            'nik.required' => 'Nomor NIK KTP wajib diisi.',
            'nik.size' => 'Nomor NIK harus berjumlah tepat 16 digit angka.',
            'nik.regex' => 'Nomor NIK hanya boleh berisi angka.',
            'nik.unique' => 'Nomor NIK ini sudah terdaftar di sistem. Setiap pengguna hanya dapat memiliki 1 akun.',
            'full_name.required' => 'Nama lengkap sesuai KTP wajib diisi.',
            'full_name.min' => 'Nama lengkap minimal 3 karakter.',
            'phone.required' => 'Nomor HP / WhatsApp wajib diisi.',
            'phone.min' => 'Nomor HP minimal 9 karakter.',
            'phone.max' => 'Nomor HP maksimal 20 karakter.',
            'phone.regex' => 'Format nomor HP tidak valid (gunakan angka).',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.in' => 'Pilihan jenis kelamin tidak valid.',
            'city_id.required' => 'Kota / Kabupaten wajib dipilih dari daftar pencarian.',
            'city_id.exists' => 'Kota yang dipilih tidak valid dalam sistem.',
            'city.required' => 'Nama Kota / Kabupaten wajib diisi.',
            'city.min' => 'Nama Kota / Kabupaten minimal 2 karakter.',
            'province.required' => 'Nama Provinsi wajib diisi.',
            'province.min' => 'Nama Provinsi minimal 2 karakter.',
        ];
    }

    public function nextStep(): void
    {
        // Auto-match city jika pengguna telah mengetik di input kota tetapi belum mengklik opsi dropdown
        if (empty($this->city_id) && !empty($this->cityQuery)) {
            $rawQuery = trim(explode('—', $this->cityQuery)[0]);
            $matched = City::whereRaw('LOWER(name) = ?', [strtolower($rawQuery)])
                ->orWhere('name', 'like', "%{$rawQuery}%")
                ->first();
            if ($matched) {
                $this->city_id = $matched->id;
                $this->city = $matched->name;
                if (empty($this->province) && !empty($matched->province)) {
                    $this->province = $matched->province;
                }
            }
        }

        try {
            $hasCities = !empty($this->cities) && count($this->cities) > 0;
            $rules = [
                'nik' => $this->getNikRules(),
                'full_name' => ['required', 'string', 'min:3', 'max:255'],
                'phone' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+\s\-]+$/'],
                'gender' => ['required', 'in:Laki-laki,Perempuan'],
                'province' => ['required', 'string', 'min:2', 'max:100'],
            ];

            if ($hasCities) {
                $rules['city_id'] = ['required', 'exists:cities,id'];
            } else {
                $rules['city'] = ['required', 'string', 'min:2', 'max:100'];
            }

            $validated = $this->validate($rules, $this->getValidationMessages());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('scroll-to-error-alert');
            throw $e;
        }

        // Simpan atau update record registration di database
        $uuid = Session::get('registration_uuid') ?? request()->cookie('registration_uuid');
        $registration = $uuid ? Registration::where('uuid', $uuid)->first() : null;

        $authUser = \Illuminate\Support\Facades\Auth::user();
        $role = $authUser ? $authUser->role : (Session::get('registration_role') ?? request()->cookie('registration_role', 'customer'));
        $email = $authUser ? $authUser->email : null;
        Session::put('registration_role', $role);
        Cookie::queue('registration_role', $role, 60 * 24 * 7);

        // Use selected city_id if provided; also store city name for readability
        $cityId = $validated['city_id'] ?? null;
        $cityName = null;
        if ($cityId) {
            $cityRec = City::find($cityId);
            if ($cityRec) {
                $cityName = $cityRec->name;
            }
        }

        $dataToSave = $validated + ['status' => 'in_progress', 'role' => $role, 'email' => $email, 'city_id' => $cityId, 'city' => $cityName];

        if ($registration) {
            $registration->update($dataToSave);
        } else {
            $registration = Registration::create(array_merge($dataToSave, [
                'uuid' => Str::uuid()->toString(),
            ]));
        }

        if ($authUser) {
            $authUser->update([
                'nik' => $validated['nik'],
                'name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'gender' => $validated['gender'],
                'city_id' => $cityId,
                'city' => $cityName,
                'province' => $validated['province'],
            ]);
        }

        Session::put('registration_uuid', $registration->uuid);
        Cookie::queue('registration_uuid', $registration->uuid, 60 * 24 * 7);
        Cookie::queue('registration_step1_draft', json_encode($validated), 60 * 24 * 7);

        $this->redirect(route('register.step2'), navigate: true);
    }

    public function updatedCityQuery($value)
    {
        $q = trim($value);
        if ($q === '') {
            $this->searchResults = [];
            return;
        }

        $limit = 10;

        $results = City::where('is_active', true)
            ->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('province', 'like', "%{$q}%")
                  ->orWhere('code', 'like', "%{$q}%");
            })
            ->whereRaw("COALESCE(code,'') NOT LIKE 'reqd-%' AND COALESCE(code,'') NOT LIKE 'regd-%'")
            ->select('id','name','province','code')
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->toArray();

            if (count($results) < $limit) {
            $remaining = $limit - count($results);
            $regRows = collect();

            if (\Illuminate\Support\Facades\Schema::hasTable('req_regencies') && \Illuminate\Support\Facades\Schema::hasTable('req_provinces')) {
                $regRows = \Illuminate\Support\Facades\DB::table('req_regencies')
                    ->join('req_provinces', 'req_regencies.province_id', '=', 'req_provinces.id')
                    ->where('req_regencies.regency', 'like', "%{$q}%")
                    ->select('req_regencies.id as regency_id', 'req_regencies.regency', 'req_provinces.province')
                    ->orderBy('req_regencies.regency')
                    ->limit($remaining)
                    ->get();
            }

            if (count($regRows) < $remaining && \Illuminate\Support\Facades\Schema::hasTable('reg_regencies') && \Illuminate\Support\Facades\Schema::hasTable('reg_provinces')) {
                $rem2 = $remaining - count($regRows);
                $rows = \Illuminate\Support\Facades\DB::table('reg_regencies')
                    ->join('reg_provinces','reg_regencies.province_id','=','reg_provinces.id')
                    ->where('reg_regencies.name','like',"%{$q}%")
                    ->select('reg_regencies.id as regency_id', 'reg_regencies.name as regency', 'reg_provinces.name as province')
                    ->orderBy('reg_regencies.name')
                    ->limit($rem2)
                    ->get();
                foreach ($rows as $r) $regRows->push($r);
            }

            // Also try kecamatan-level tables and map results to parent regency
            if (count($regRows) < $remaining && \Illuminate\Support\Facades\Schema::hasTable('req_districts') && \Illuminate\Support\Facades\Schema::hasTable('req_regencies') && \Illuminate\Support\Facades\Schema::hasTable('req_provinces')) {
                $remD = $remaining - count($regRows);
                $dist = \Illuminate\Support\Facades\DB::table('req_districts')
                    ->join('req_regencies', 'req_districts.regency_id', '=', 'req_regencies.id')
                    ->join('req_provinces', 'req_regencies.province_id', '=', 'req_provinces.id')
                    ->where(function($b) use ($q) {
                        $b->where('req_districts.district', 'like', "%{$q}%")
                          ->orWhere('req_regencies.regency', 'like', "%{$q}%")
                          ->orWhere('req_provinces.province', 'like', "%{$q}%");
                    })
                    ->select(\Illuminate\Support\Facades\DB::raw("CONCAT('reqr-', req_regencies.id) as regency_id"), 'req_regencies.regency', \Illuminate\Support\Facades\DB::raw("req_districts.district as matched_district"), 'req_provinces.province')
                    ->orderBy('req_districts.district')
                    ->limit($remD)
                    ->get();
                foreach ($dist as $d) $regRows->push($d);
            }

            // legacy tables
            if (count($regRows) < $remaining && \Illuminate\Support\Facades\Schema::hasTable('regencies') && \Illuminate\Support\Facades\Schema::hasTable('provinces')) {
                $rem3 = $remaining - count($regRows);
                $rows = \Illuminate\Support\Facades\DB::table('regencies')
                    ->join('provinces', 'regencies.province_id', '=', 'provinces.id')
                    ->where('regencies.regency','like',"%{$q}%")
                    ->select('regencies.id as regency_id','regencies.regency','provinces.province')
                    ->orderBy('regencies.regency')
                    ->limit($rem3)
                    ->get();
                foreach ($rows as $r) $regRows->push($r);
            }

            foreach ($regRows as $r) {
                $city = City::firstOrCreate(
                    ['code' => (string)($r->regency_id)],
                    ['name' => $r->regency, 'province' => $r->province, 'is_active' => true]
                );
                $exists = false;
                foreach ($results as $res) {
                    if ($res['id'] == $city->id) { $exists = true; break; }
                }
                if (! $exists) {
                    $results[] = ['id' => $city->id, 'name' => $city->name, 'province' => $city->province, 'code' => $city->code];
                }
            }
        }

        $this->searchResults = $results;
    }

    public function setCityId($id)
    {
        $this->city_id = $id;
        $city = City::find($id);
        if ($city) {
            $this->city = $city->name;
            $this->province = $city->province;
            // show chosen city in the search input so user sees selection
            $this->cityQuery = $city->name . ' — ' . $city->province;
        }
        $this->searchResults = [];
        $this->updated('city_id');
    }
}; ?>

<div class="space-y-5"
     x-data
     x-on:scroll-to-error-alert.window="
         $nextTick(() => {
             const el = document.getElementById('step1-error-alert') || document.querySelector('.text-red-500, [aria-invalid=true]');
             if (el) {
                 el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                 if (typeof el.focus === 'function') el.focus();
             }
         })
     ">
    <!-- Step Header -->
    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
        <div>
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-primary-600 dark:text-sky-400">Langkah 1 dari 4</span>
            <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">Data Diri & Identitas</h2>
        </div>
        <div class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-sky-400 font-bold text-xs flex items-center justify-center border border-primary-200 dark:border-primary-800">
            1/4
        </div>
    </div>

    <!-- Progress Indicator Pills -->
    <div class="grid grid-cols-4 gap-1.5 mb-2">
        <div class="h-1.5 rounded-full bg-primary-600"></div>
        <div class="h-1.5 rounded-full bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-1.5 rounded-full bg-gray-200 dark:bg-gray-700"></div>
        <div class="h-1.5 rounded-full bg-gray-200 dark:bg-gray-700"></div>
    </div>

    <form wire:submit="nextStep" class="space-y-4">
        <!-- Error Alert Summary Box -->
        @if ($errors->any())
            <div id="step1-error-alert" 
                 tabindex="-1"
                 class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border-2 border-rose-400 dark:border-rose-700 text-rose-800 dark:text-rose-200 shadow-md focus:outline-none transition-all">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-rose-100 dark:bg-rose-900/80 rounded-xl text-rose-600 dark:text-rose-300 shrink-0 mt-0.5 shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-bold text-rose-900 dark:text-rose-100">Periksa Kembali Formulir Pendaftaran</h3>
                        <p class="text-xs text-rose-700 dark:text-rose-300 mt-0.5">Semua kolom wajib diisi dengan benar sebelum melanjutkan ke Langkah 2:</p>
                        <ul class="mt-2 space-y-1 text-xs list-disc list-inside text-rose-700 dark:text-rose-300 font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Lengkapi seluruh data diri Anda sesuai dengan dokumen KTP yang sah.</p>

            <div class="space-y-4">
                <!-- NIK -->
                <div>
                    <label for="nik" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">NIK <span class="text-red-500">*</span></label>
                    <input wire:model.live.debounce.500ms="nik" id="nik" type="text" maxlength="16" placeholder="16 digit NIK"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        class="w-full px-4 py-3 rounded-xl border @error('nik') border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/30 dark:bg-rose-950/20 @else border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 @enderror text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition shadow-xs text-xs sm:text-sm">
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">{{ strlen($nik) }}/16 digit</p>
                    <x-input-error :messages="$errors->get('nik')" />
                </div>

                <!-- Nama Lengkap -->
                <div>
                    <label for="full_name" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Nama Lengkap Sesuai KTP <span class="text-red-500">*</span></label>
                    <input wire:model="full_name" id="full_name" type="text" placeholder="Contoh: Budi Santoso"
                        class="w-full px-4 py-3 rounded-xl border @error('full_name') border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/30 dark:bg-rose-950/20 @else border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 @enderror text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition shadow-xs text-xs sm:text-sm">
                    <x-input-error :messages="$errors->get('full_name')" />
                </div>

                <!-- Nomor Telepon / WhatsApp -->
                <div>
                    <label for="phone" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Nomor HP / WhatsApp <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <input wire:model="phone" id="phone" type="tel" placeholder="Contoh: 081234567890"
                            class="w-full pl-10 pr-4 py-3 rounded-xl border @error('phone') border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/30 dark:bg-rose-950/20 @else border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 @enderror text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition shadow-xs text-xs sm:text-sm">
                    </div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Nomor aktif untuk koordinasi bantuan dan akun.</p>
                    <x-input-error :messages="$errors->get('phone')" />
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 p-3.5 rounded-xl border @if($gender === 'Laki-laki') border-primary-500 bg-primary-50/50 dark:bg-primary-950/40 ring-1 ring-primary-500 @elseif($errors->has('gender')) border-rose-400 bg-rose-50/20 dark:bg-rose-950/20 @else border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 @endif cursor-pointer hover:border-primary-400 transition">
                            <input wire:model.live="gender" type="radio" value="Laki-laki" name="gender" class="text-primary-600 focus:ring-primary-500">
                            <div class="flex items-center gap-2">
                                <span class="text-base">👨</span>
                                <span class="text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200">Laki-laki</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3.5 rounded-xl border @if($gender === 'Perempuan') border-primary-500 bg-primary-50/50 dark:bg-primary-950/40 ring-1 ring-primary-500 @elseif($errors->has('gender')) border-rose-400 bg-rose-50/20 dark:bg-rose-950/20 @else border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 @endif cursor-pointer hover:border-primary-400 transition">
                            <input wire:model.live="gender" type="radio" value="Perempuan" name="gender" class="text-primary-600 focus:ring-primary-500">
                            <div class="flex items-center gap-2">
                                <span class="text-base">👩</span>
                                <span class="text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200">Perempuan</span>
                            </div>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('gender')" />
                </div>

                <!-- Kota/Kabupaten (realtime search) -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Kota / Kabupaten <span class="text-red-500">*</span></label>
                    @if(isset($cities) && count($cities) > 0)
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="cityQuery" id="city-search-input"
                                placeholder="Ketik nama Kota/Kabupaten..."
                                class="w-full px-4 py-3 rounded-xl border @error('city_id') border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/30 dark:bg-rose-950/20 @else border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 @enderror text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition shadow-xs text-xs sm:text-sm" autocomplete="off">

                            <input type="hidden" wire:model="city_id" id="city_id">

                            @if (!empty($searchResults))
                                <ul class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-60 overflow-auto z-50 divide-y divide-gray-100 dark:divide-gray-700/60">
                                    @foreach ($searchResults as $c)
                                        <li wire:click="setCityId({{ $c['id'] }})"
                                            class="px-4 py-3 text-xs sm:text-sm hover:bg-primary-50/70 dark:hover:bg-gray-700/60 cursor-pointer transition flex items-start gap-2">
                                            <div class="flex-1">
                                                <div class="font-semibold text-gray-900 dark:text-white">{{ $c['name'] }}</div>
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400">{{ $c['province'] }}</div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif (!empty($cityQuery) && strlen($cityQuery) >= 2 && empty($city_id))
                                <div class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl p-4 z-50">
                                    <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Kota tidak ditemukan
                                    </div>
                                </div>
                            @endif
                        </div>
                        <x-input-error :messages="$errors->get('city_id')" />
                    @else
                        <input wire:model="city" id="city" type="text" placeholder="Ketik nama Kota/Kabupaten"
                            class="w-full px-4 py-3 rounded-xl border @error('city') border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/30 dark:bg-rose-950/20 @else border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 @enderror text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition shadow-xs text-xs sm:text-sm">
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Daftar kota belum tersedia. Ketik nama kota secara manual.</p>
                        <x-input-error :messages="$errors->get('city')" />
                    @endif
                </div>

                <!-- Provinsi -->
                <div>
                    <label for="province" class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Provinsi <span class="text-red-500">*</span></label>
                    <input wire:model="province" id="province" type="text" placeholder="Nama Provinsi"
                        class="w-full px-4 py-3 rounded-xl border @error('province') border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/30 dark:bg-rose-950/20 @else border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 @enderror text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition shadow-xs text-xs sm:text-sm">
                    <x-input-error :messages="$errors->get('province')" />
                </div>
            </div>

            <!-- Next Button -->
            <div class="pt-6 pb-2">
                <button type="submit" wire:loading.attr="disabled"
                    class="w-full bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs sm:text-sm py-3.5 rounded-xl shadow-sm hover:shadow-md transition-all active:scale-[0.98] disabled:opacity-50 cursor-pointer flex items-center justify-center gap-2">
                    <svg wire:loading wire:target="nextStep" class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="nextStep">Lanjutkan ke Langkah 2</span>
                    <span wire:loading wire:target="nextStep">Memproses Data...</span>
                    <svg wire:loading.remove wire:target="nextStep" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </div>
    </form>
</div>