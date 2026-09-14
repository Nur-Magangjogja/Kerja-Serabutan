<?php

use App\Livewire\Actions\CancelRegistration;
use App\Models\Registration;
use App\Models\City;
use App\Models\District;
use App\Services\CitySearchService;
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
    public ?int $district_id = null;
    public string $kecamatan = '';
    public string $province = '';
    
    public $cities = [];
    public array $districtsList = [];
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

        $user = \Illuminate\Support\Facades\Auth::user();

        // 0. Blokir akses admin, superadmin, dan akun yang SUDAH diverifikasi/disetujui admin
        if ($user) {
            if (in_array($user->role ?? '', ['admin', 'super_admin', 'superadmin'])) {
                $route = in_array($user->role, ['super_admin', 'superadmin']) ? 'superadmin.dashboard' : 'admin.dashboard';
                $this->redirect(route($route), navigate: true);
                return;
            }

            if ($user->verified && $user->status === 'active') {
                $route = $user->role === 'mitra' ? 'mitra.dashboard' : 'customer.dashboard';
                $this->redirect(route($route), navigate: true);
                return;
            }
        }

        // 1. Pastikan email sudah terverifikasi sebelum mengisi form Step 1
        if ($user && !$user->hasVerifiedEmail()) {
            $this->redirect(route('verification.notice'), navigate: true);
            return;
        }

        // 2. Cek jika batas waktu 1x24 jam untuk pengisian form telah kedaluwarsa
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

        // 3. Cari record Registration yang HANYA milik user yang sedang login
        $registration = null;
        if ($user) {
            $registration = Registration::where('email', strtolower(trim($user->email)))->latest()->first();
        }

        $uuid = Session::get('registration_uuid') ?? $getCookieVal('registration_uuid');
        if ($uuid && !$registration) {
            $found = Registration::where('uuid', $uuid)->first();
            // Validasi ketat: hanya gunakan UUID jika email cocok dengan user yang sedang login
            if ($found && $user && strtolower(trim($found->email ?? '')) === strtolower(trim($user->email))) {
                $registration = $found;
            } else {
                // Buang cookie UUID pendaftaran lama milik akun lain
                Session::forget('registration_uuid');
                Cookie::queue(Cookie::forget('registration_uuid'));
            }
        }

        if ($registration) {
            Session::put('registration_uuid', $registration->uuid);
            Cookie::queue('registration_uuid', $registration->uuid, 60 * 24 * 7);

            $this->nik = $registration->nik ?? $this->nik;
            $this->full_name = $registration->full_name ?? ($user?->name ?? $this->full_name);
            $this->phone = $registration->phone ?? ($user?->phone ?? $this->phone);
            $this->gender = $registration->gender ?? $this->gender;
            $this->city = $registration->city ?? $this->city;
            $this->city_id = $registration->city_id ? (int) $registration->city_id : null;
            $this->district_id = $registration->district_id ? (int) $registration->district_id : null;
            $this->kecamatan = $registration->kecamatan ?? $this->kecamatan;
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

            if ($this->city_id) {
                $this->districtsList = app(CitySearchService::class)->getDistrictsByCity((int) $this->city_id);
                if ($this->district_id && empty($this->kecamatan)) {
                    $dist = District::find($this->district_id);
                    if ($dist) {
                        $this->kecamatan = $dist->name;
                    }
                }
            }
        } else {
            // Pengguna baru: isi otomatis dari data akun yang baru dibuat
            if ($user) {
                $this->full_name = $user->name ?? '';
                $this->phone = $user->phone ?? '';
                $this->nik = $user->nik ?? '';
                if (!empty($user->city_id)) {
                    $this->city_id = (int) $user->city_id;
                    $this->city = $user->city ?? '';
                    $this->province = $user->province ?? '';
                }
                if (!empty($user->district_id)) {
                    $this->district_id = (int) $user->district_id;
                    $this->kecamatan = $user->kecamatan ?? '';
                }
                if ($this->city_id && empty($this->districtsList)) {
                    $this->districtsList = app(CitySearchService::class)->getDistrictsByCity((int) $this->city_id);
                }
            }
        }

        // 4. Restore draft dari cookie HANYA jika draft tersebut milik email user yang sedang login
        $draftCookie = $getCookieVal('registration_step1_draft');
        if ($draftCookie) {
            $draft = is_string($draftCookie) ? json_decode($draftCookie, true) : (is_array($draftCookie) ? $draftCookie : null);
            if (is_array($draft)) {
                $draftEmail = $draft['email'] ?? null;
                // Jika cookie draf milik akun lain, buang cookie tersebut
                if ($draftEmail && $user && strtolower(trim($draftEmail)) !== strtolower(trim($user->email))) {
                    Cookie::queue(Cookie::forget('registration_step1_draft'));
                } else {
                    $this->nik = $draft['nik'] ?? $this->nik;
                    $this->full_name = $draft['full_name'] ?? $this->full_name;
                    $this->phone = $draft['phone'] ?? $this->phone;
                    $this->gender = $draft['gender'] ?? $this->gender;
                    $this->city = $draft['city'] ?? $this->city;
                    $this->city_id = isset($draft['city_id']) && $draft['city_id'] ? (int) $draft['city_id'] : $this->city_id;
                    $this->district_id = isset($draft['district_id']) && $draft['district_id'] ? (int) $draft['district_id'] : $this->district_id;
                    $this->kecamatan = $draft['kecamatan'] ?? $this->kecamatan;
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

                    if ($this->city_id) {
                        $this->districtsList = app(CitySearchService::class)->getDistrictsByCity((int) $this->city_id);
                    }
                }
            }
        }
    }

    public function updated($propertyName): void
    {
        if ($propertyName === 'phone') {
            $this->phone = \App\Models\User::normalizePhone($this->phone) ?? '';
        }

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

        if ($propertyName === 'district_id') {
            if ($this->district_id) {
                $dist = District::find((int) $this->district_id);
                if ($dist) {
                    $this->kecamatan = $dist->name;
                }
            } else {
                $this->kecamatan = '';
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
            'district_id' => $this->district_id,
            'kecamatan' => $this->kecamatan,
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
            'district_id.required' => 'Kecamatan (Wilayah Operasional) wajib dipilih.',
            'district_id.exists' => 'Kecamatan yang dipilih tidak valid dalam sistem.',
            'kecamatan.required' => 'Nama Kecamatan wajib diisi.',
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
                $this->districtsList = app(CitySearchService::class)->getDistrictsByCity((int) $matched->id);
            }
        }

        $this->phone = \App\Models\User::normalizePhone($this->phone) ?? '';

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

            if (!empty($this->districtsList) && count($this->districtsList) > 0) {
                $rules['district_id'] = ['required', 'exists:districts,id'];
            } elseif (!empty($this->city_id)) {
                $rules['district_id'] = ['required', 'exists:districts,id'];
            } else {
                $rules['kecamatan'] = ['required', 'string', 'min:2', 'max:100'];
            }

            $validated = $this->validate($rules, $this->getValidationMessages());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('scroll-to-error-alert');
            throw $e;
        }

        // Simpan atau update record registration di database
        $authUser = \Illuminate\Support\Facades\Auth::user();
        $email = $authUser ? strtolower(trim($authUser->email)) : null;
        $role = $authUser ? $authUser->role : (Session::get('registration_role') ?? request()->cookie('registration_role', 'customer'));
        Session::put('registration_role', $role);
        Cookie::queue('registration_role', $role, 60 * 24 * 7);

        $registration = null;
        if ($email) {
            $registration = Registration::where('email', $email)->latest()->first();
        }
        if (!$registration) {
            $uuid = Session::get('registration_uuid') ?? request()->cookie('registration_uuid');
            if ($uuid) {
                $found = Registration::where('uuid', $uuid)->first();
                if ($found && $email && strtolower(trim($found->email ?? '')) === $email) {
                    $registration = $found;
                }
            }
        }

        // Resolve city and district records
        $cityId = $validated['city_id'] ?? $this->city_id;
        $cityName = null;
        if ($cityId) {
            $cityRec = City::find($cityId);
            if ($cityRec) {
                $cityName = $cityRec->name;
            }
        }

        $districtId = $validated['district_id'] ?? $this->district_id;
        $kecamatanName = $this->kecamatan;
        if ($districtId) {
            $distRec = District::find($districtId);
            if ($distRec) {
                $kecamatanName = $distRec->name;
            }
        }

        $dataToSave = array_merge($validated, [
            'status' => 'in_progress',
            'role' => $role,
            'email' => $email,
            'city_id' => $cityId,
            'city' => $cityName,
            'district_id' => $districtId,
            'kecamatan' => $kecamatanName,
        ]);

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
                'district_id' => $districtId,
                'kecamatan' => $kecamatanName,
                'province' => $validated['province'],
            ]);
        }

        Session::put('registration_uuid', $registration->uuid);
        Cookie::queue('registration_uuid', $registration->uuid, 60 * 24 * 7);
        Cookie::queue('registration_step1_draft', json_encode($dataToSave), 60 * 24 * 7);

        $this->redirect(route('register.step2'), navigate: true);
    }

    public function updatedCityQuery($value)
    {
        $q = trim($value);
        if ($q === '') {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = app(CitySearchService::class)->search($q, 10);
    }

    public string $districtSearch = '';

    public function getFilteredDistricts(): array
    {
        if (empty($this->districtSearch)) {
            return $this->districtsList;
        }
        $q = strtolower(trim($this->districtSearch));
        return array_values(array_filter($this->districtsList, function($d) use ($q) {
            return str_contains(strtolower($d['name'] ?? ''), $q);
        }));
    }

    public function setDistrictId(int $id, string $name): void
    {
        $this->district_id = $id;
        $this->kecamatan = $name;
        $this->districtSearch = '';
        $this->updated('district_id');
    }

    public function clearDistrict(): void
    {
        $this->district_id = null;
        $this->kecamatan = '';
        $this->districtSearch = '';
        $this->updated('district_id');
    }

    public function setCityId($id)
    {
        $this->city_id = (int) $id;
        $city = City::find($id);
        if ($city) {
            $this->city = $city->name;
            $this->province = $city->province ?? '';
            $this->cityQuery = $city->name;
            
            // Load districts for this chosen city
            $this->districtsList = app(CitySearchService::class)->getDistrictsByCity((int) $id);
            
            // Reset district selection so user actively picks from this city's districts
            $this->district_id = null;
            $this->kecamatan = '';
            $this->districtSearch = '';
        }
        $this->searchResults = [];
        $this->updated('city_id');
    }

    public function clearCity(): void
    {
        $this->city_id = null;
        $this->city = '';
        $this->province = '';
        $this->cityQuery = '';
        $this->district_id = null;
        $this->kecamatan = '';
        $this->districtSearch = '';
        $this->districtsList = [];
        $this->searchResults = [];
        $this->updated('city_id');
    }

    public function cancelRegistration(CancelRegistration $cancelRegistration): void
    {
        $cancelRegistration();
        $this->redirect(route('login'), navigate: true);
    }
}; ?>

<div class="space-y-5"
     x-data="{ confirmCancelModal: false }"
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
                    <input wire:model="full_name" id="full_name" type="text" placeholder="Nama Lengkap"
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
                        <input wire:model.blur="phone" id="phone" type="tel" placeholder="08xxxxxxxxxx"
                            class="w-full pl-10 pr-4 py-3 rounded-xl border @error('phone') border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/30 dark:bg-rose-950/20 @else border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 @enderror text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition shadow-xs text-xs sm:text-sm">
                    </div>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Nomor aktif untuk koordinasi bantuan dan akun (otomatis diawali 08).</p>
                    <x-input-error :messages="$errors->get('phone')" />
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 p-3.5 rounded-xl border @if($gender === 'Laki-laki') border-primary-500 bg-primary-50/50 dark:bg-primary-950/40 ring-1 ring-primary-500 @elseif($errors->has('gender')) border-rose-400 bg-rose-50/20 dark:bg-rose-950/20 @else border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 @endif cursor-pointer hover:border-primary-400 transition">
                            <input wire:model.live="gender" type="radio" value="Laki-laki" name="gender" class="text-primary-600 focus:ring-primary-500">
                            <div class="flex items-center gap-2">
                                <span class="text-base"></span>
                                <span class="text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200">Laki-laki</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3.5 rounded-xl border @if($gender === 'Perempuan') border-primary-500 bg-primary-50/50 dark:bg-primary-950/40 ring-1 ring-primary-500 @elseif($errors->has('gender')) border-rose-400 bg-rose-50/20 dark:bg-rose-950/20 @else border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 @endif cursor-pointer hover:border-primary-400 transition">
                            <input wire:model.live="gender" type="radio" value="Perempuan" name="gender" class="text-primary-600 focus:ring-primary-500">
                            <div class="flex items-center gap-2">
                                <span class="text-base"></span>
                                <span class="text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200">Perempuan</span>
                            </div>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('gender')" />
                </div>

                <!-- Section Wilayah -->
                <div class="pt-3 pb-1 border-t border-gray-100 dark:border-gray-800 space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-primary-500"></span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Wilayah Domisili Sesuai KTP</h3>
                    </div>

                    <!-- 1. Kota / Kabupaten (Langkah Pertama Pengisian Wilayah) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="city-search-input" class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                1. Kota / Kabupaten <span class="text-red-500">*</span>
                            </label>
                            @if(!empty($city_id) || !empty($city))
                                <button type="button" wire:click="clearCity" class="text-[11px] text-primary-600 dark:text-sky-400 hover:underline flex items-center gap-1 font-medium cursor-pointer">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Ganti Kota</span>
                                </button>
                            @endif
                        </div>

                        @if(isset($cities) && count($cities) > 0)
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                
                                <input type="text" 
                                    wire:model.live.debounce.300ms="cityQuery" 
                                    id="city-search-input"
                                    placeholder="Ketik nama Kota atau Kabupaten"
                                    class="w-full pl-10 pr-10 py-3 rounded-xl border @error('city_id') border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/30 dark:bg-rose-950/20 @else border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 @enderror text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition shadow-xs text-xs sm:text-sm" 
                                    autocomplete="off">

                                @if(!empty($cityQuery))
                                    <button type="button" wire:click="clearCity" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                @endif

                                <input type="hidden" wire:model="city_id" id="city_id">

                                @if (!empty($searchResults))
                                    <ul class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-60 overflow-y-auto dropdown-scrollbar z-50 divide-y divide-gray-100 dark:divide-gray-700/60">
                                        @foreach ($searchResults as $c)
                                            <li wire:click="setCityId({{ $c['id'] }})"
                                                class="px-4 py-3 text-xs sm:text-sm hover:bg-primary-50/80 dark:hover:bg-gray-700/80 cursor-pointer transition flex items-center justify-between gap-2 group">
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-bold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-sky-400 truncate">{{ $c['name'] }}</div>
                                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 flex items-center gap-1.5 mt-0.5">
                                                        <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                        <span class="truncate">Provinsi: {{ $c['province'] }}</span>
                                                    </div>
                                                </div>
                                                <span class="text-[11px] font-semibold text-primary-600 dark:text-sky-400 bg-primary-50 dark:bg-primary-950/60 px-2.5 py-1 rounded-lg border border-primary-200 dark:border-primary-800 group-hover:bg-primary-600 group-hover:text-white transition shrink-0">Pilih</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @elseif (!empty($cityQuery) && strlen($cityQuery) >= 2 && empty($city_id))
                                    <div class="absolute left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl p-4 z-50">
                                        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                            <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>Kota/Kabupaten tidak ditemukan. Coba ketik nama lain.</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <x-input-error :messages="$errors->get('city_id')" />
                        @else
                            <input wire:model="city" id="city" type="text" placeholder="Ketik nama Kota/Kabupaten"
                                class="w-full px-4 py-3 rounded-xl border @error('city') border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/30 dark:bg-rose-950/20 @else border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 @enderror text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition shadow-xs text-xs sm:text-sm">
                            <x-input-error :messages="$errors->get('city')" />
                        @endif
                    </div>

                    <!-- 2. Provinsi (Hasil Pencocokan Otomatis dari Kota / Kabupaten) -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            2. Provinsi <span class="text-red-500">*</span>
                        </label>
                        @if(!empty($province))
                            <!-- Box Provinsi Terdeteksi Otomatis -->
                            <div class="p-3.5 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/70 flex items-center justify-between gap-3 shadow-xs transition-all">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div class="truncate">
                                        <div class="text-[11px] font-medium text-emerald-800 dark:text-emerald-300">Provinsi Terdeteksi:</div>
                                        <div class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate">{{ $province }}</div>
                                    </div>
                                </div>
                                <span class="text-[10px] uppercase font-extrabold tracking-wider bg-emerald-100 dark:bg-emerald-900/80 text-emerald-800 dark:text-emerald-200 px-2 py-1 rounded-md border border-emerald-300 dark:border-emerald-700 shrink-0">
                                    Otomatis
                                </span>
                            </div>
                            <input type="hidden" wire:model="province" id="province">
                        @else
                            <!-- Placeholder Menunggu Kota Dipilih -->
                            <div class="p-3 rounded-xl border border-dashed border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/30 text-xs text-gray-400 dark:text-gray-500 flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Provinsi akan otomatis dicocokkan setelah Anda memilih Kota / Kabupaten di atas.</span>
                            </div>
                        @endif
                        <x-input-error :messages="$errors->get('province')" />
                    </div>

                    <!-- 3. Kecamatan (Tampilan Responsif Tanpa Dropdown Native - Anti Overflow) -->
                    @if(!empty($city_id) || !empty($city))
                        <div class="pt-2 animate-fadeIn transition-all">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    3. Kecamatan (Patokan Wilayah Utama) <span class="text-red-500">*</span>
                                </label>
                                @if(!empty($district_id) && !empty($kecamatan))
                                    <button type="button" wire:click="clearDistrict" class="text-[11px] text-primary-600 dark:text-sky-400 hover:underline flex items-center gap-1 font-medium cursor-pointer">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <span>Ganti Kecamatan</span>
                                    </button>
                                @else
                                    <span class="text-[10px] font-semibold text-primary-600 dark:text-sky-400 bg-primary-50 dark:bg-primary-950/60 px-2 py-0.5 rounded-full border border-primary-200 dark:border-primary-800">
                                        Wilayah Operasional
                                    </span>
                                @endif
                            </div>

                            @if(!empty($district_id) && !empty($kecamatan))
                                <!-- Card Kecamatan Terpilih (Compact & Clear) -->
                                <div class="p-3.5 rounded-xl bg-primary-50/80 dark:bg-primary-950/40 border-2 border-primary-400 dark:border-primary-600 flex items-center justify-between gap-3 shadow-xs">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-7 h-7 rounded-lg bg-primary-100 dark:bg-primary-900/60 text-primary-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </div>
                                        <div class="truncate">
                                            <div class="text-[11px] font-medium text-primary-800 dark:text-sky-300">Kecamatan Terpilih:</div>
                                            <div class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate">Kec. {{ $kecamatan }}</div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950/80 px-2.5 py-1 rounded-md border border-emerald-300 dark:border-emerald-700 shrink-0 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Terpilih</span>
                                    </span>
                                </div>
                                <input type="hidden" wire:model="district_id" id="district_id">
                                <input type="hidden" wire:model="kecamatan" id="kecamatan">
                            @elseif(!empty($districtsList) && count($districtsList) > 0)
                                <!-- Daftar Pilihan Kecamatan Interaktif (Card List Scrollable, Anti-Overflow) -->
                                <div class="space-y-2">
                                    <!-- Search filter input jika kecamatan banyak -->
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        </div>
                                        <input type="text" 
                                            wire:model.live.debounce.150ms="districtSearch"
                                            placeholder="Cari kecamatan di {{ $city }} (contoh: Depok, Mlati)..." 
                                            class="w-full pl-9 pr-3 py-2.5 text-xs sm:text-sm rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-primary-500 outline-none transition shadow-xs">
                                    </div>

                                    <!-- Container List Kecamatan -->
                                    <div class="max-h-56 overflow-y-auto dropdown-scrollbar divide-y divide-gray-100 dark:divide-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-xs">
                                        @forelse($this->getFilteredDistricts() as $d)
                                            <button type="button" 
                                                wire:click="setDistrictId({{ $d['id'] }}, '{{ addslashes($d['name']) }}')"
                                                class="w-full text-left px-4 py-3 flex items-center justify-between hover:bg-primary-50/80 dark:hover:bg-gray-800/80 transition cursor-pointer group active:scale-[0.99]">
                                                <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                                    <div class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-400 group-hover:bg-primary-100 group-hover:text-primary-600 dark:group-hover:bg-primary-950 dark:group-hover:text-sky-400 flex items-center justify-center shrink-0 transition">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    </div>
                                                    <span class="text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary-600 dark:group-hover:text-sky-400 truncate">
                                                        Kec. {{ $d['name'] }}
                                                    </span>
                                                </div>
                                                <span class="text-[11px] font-bold text-primary-600 dark:text-sky-400 bg-primary-50 dark:bg-primary-950/60 px-2.5 py-1 rounded-lg border border-primary-200 dark:border-primary-800 group-hover:bg-primary-600 group-hover:text-white transition shrink-0">
                                                    Pilih
                                                </span>
                                            </button>
                                        @empty
                                            <div class="p-4 text-center text-xs text-gray-400 dark:text-gray-500">
                                                Kecamatan tidak ditemukan.
                                            </div>
                                        @endforelse
                                    </div>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Pilih kecamatan tempat tinggal Anda sebagai wilayah operasional.</p>
                                </div>
                            @else
                                <input wire:model="kecamatan" id="kecamatan" type="text" placeholder="Ketik nama Kecamatan Anda di {{ $city }}..."
                                    class="w-full px-4 py-3 rounded-xl border @error('kecamatan') border-rose-500 ring-2 ring-rose-500/20 bg-rose-50/30 dark:bg-rose-950/20 @else border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-900 @enderror text-gray-900 dark:text-white placeholder-gray-400 focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition shadow-xs text-xs sm:text-sm">
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Ketik nama kecamatan Anda sesuai KTP.</p>
                            @endif
                            <x-input-error :messages="$errors->get('district_id')" />
                            <x-input-error :messages="$errors->get('kecamatan')" />
                        </div>
                    @endif
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

            <!-- Cancel Registration Link / Button -->
            <div class="pt-2 text-center border-t border-gray-100 dark:border-gray-750 mt-4">
                <button type="button" 
                    @click="confirmCancelModal = true"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 transition-colors py-1.5 px-3 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Batalkan Pembuatan Akun & Masuk</span>
                </button>
            </div>
        </div>
    </form>

    <!-- Modal Konfirmasi Pembatalan Pendaftaran (Alpine.js) -->
    <div x-show="confirmCancelModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop Blur Overlay -->
        <div x-show="confirmCancelModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
             @click="confirmCancelModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="confirmCancelModal"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-left shadow-2xl border border-gray-100 dark:border-gray-700 transition-all sm:my-8 sm:w-full sm:max-w-md p-6 sm:p-7">
                
                <div class="flex items-start gap-4">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl bg-rose-100 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400 sm:mx-0 shadow-xs">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div class="text-left flex-1 min-w-0">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white" id="modal-title">
                            Batalkan Pembuatan Akun?
                        </h3>
                        <div class="mt-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                Apakah Anda yakin ingin membatalkan pendaftaran ini? Semua draf data diri yang telah diisi akan dihapus dan Anda akan dialihkan kembali ke halaman <strong>Masuk (Login)</strong>.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                    <button type="button" 
                        @click="confirmCancelModal = false"
                        class="w-full sm:w-auto inline-flex justify-center items-center rounded-xl px-4 py-2.5 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-650 transition cursor-pointer">
                        Lanjutkan Pengisian
                    </button>
                    <button type="button" 
                        wire:click="cancelRegistration"
                        wire:loading.attr="disabled"
                        class="w-full sm:w-auto inline-flex justify-center items-center gap-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white px-4 py-2.5 text-xs font-bold shadow-sm transition active:scale-[0.98] disabled:opacity-50 cursor-pointer">
                        <svg wire:loading wire:target="cancelRegistration" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="cancelRegistration">Ya, Batalkan & Keluar</span>
                        <span wire:loading wire:target="cancelRegistration">Membatalkan...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>