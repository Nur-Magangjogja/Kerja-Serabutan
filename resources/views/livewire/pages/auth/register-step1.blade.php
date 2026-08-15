<?php

use App\Models\Registration;
use App\Models\City;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $nik = '';
    public string $full_name = '';
    public string $place_of_birth = '';
    public string $date_of_birth = '';
    public string $gender = '';
    public string $address = '';
    public string $rt = '';
    public string $rw = '';
    public string $kelurahan = '';
    public string $kecamatan = '';
    public string $city = '';
    public ?int $city_id = null;
    public string $province = '';
    
    public $cities = [];
    // realtime city search (for nicer UX)
    public string $cityQuery = '';
    public array $searchResults = [];

    // Auto-detect gender from NIK
    public function updatedNik($value)
    {
        if (strlen($value) >= 8) {
            $tglLahir = (int) substr($value, 6, 2);
            // Jika tanggal > 40, berarti perempuan
            $this->gender = $tglLahir > 40 ? 'Perempuan' : 'Laki-laki';
        }
    }

    // Preload saved registration values if a registration UUID exists in session
    public function mount(): void
    {
        // Always load available cities so the dropdown can be rendered from DB
        $this->cities = City::orderBy('name')->get();

        $uuid = Session::get('registration_uuid');
        if (!$uuid) {
            return;
        }

        $registration = Registration::where('uuid', $uuid)->first();
        if (!$registration) {
            return;
        }

        $this->nik = $registration->nik ?? $this->nik;
        $this->full_name = $registration->full_name ?? $this->full_name;
        $this->place_of_birth = $registration->place_of_birth ?? $this->place_of_birth;
        $this->date_of_birth = $registration->date_of_birth ?? $this->date_of_birth;
        $this->gender = $registration->gender ?? $this->gender;
        $this->address = $registration->address ?? $this->address;
        $this->rt = $registration->rt ?? $this->rt;
        $this->rw = $registration->rw ?? $this->rw;
        $this->kelurahan = $registration->kelurahan ?? $this->kelurahan;
        $this->kecamatan = $registration->kecamatan ?? $this->kecamatan;
        $this->city = $registration->city ?? $this->city;
        $this->city_id = $registration->city_id ?? null;
        $this->province = $registration->province ?? $this->province;
        // load available cities so registrants pick canonical city names
        $this->cities = City::orderBy('name')->get();
    }

    public function nextStep(): void
    {
        $validated = $this->validate([
            'nik' => ['required', 'string', 'size:16', 'regex:/^[0-9]+$/'],
            'full_name' => ['required', 'string', 'max:255'],
            'place_of_birth' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', 'in:Laki-laki,Perempuan'],
            'address' => ['required', 'string', 'max:500'],
            'rt' => ['required', 'string', 'max:5'],
            'rw' => ['required', 'string', 'max:5'],
            'kelurahan' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
        ]);

        // Simpan atau update record registration di database
        $uuid = Session::get('registration_uuid');

        if ($uuid) {
            $registration = Registration::where('uuid', $uuid)->first();
        } else {
            $registration = null;
        }

        $role = Session::get('registration_role', 'customer');

        // Use selected city_id if provided; also store city name for readability
        $cityId = $validated['city_id'] ?? null;
        $cityName = null;
        if ($cityId) {
            $cityRec = City::find($cityId);
            if ($cityRec) {
                $cityName = $cityRec->name;
            }
        }

        $dataToSave = $validated + ['status' => 'in_progress', 'role' => $role, 'city_id' => $cityId, 'city' => $cityName];

        if ($registration) {
            $registration->update($dataToSave);
        } else {
            $registration = Registration::create(array_merge($dataToSave, [
                'uuid' => Str::uuid()->toString(),
            ]));
            Session::put('registration_uuid', $registration->uuid);
        }

        // Clear client-side saved draft for step1 (if any)
        $this->dispatch('clear-registration-step1');

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
    }
}; ?>

<div class="w-full">
    <div class="bg-white rounded-2xl p-6 w-full shadow-lg max-w-md mx-auto">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('register') }}" wire:navigate class="text-gray-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-lg font-bold text-gray-900">Data Diri</h1>
            <div class="w-6"></div>
        </div>

        <div class="flex items-center gap-2 mb-4">
            <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
            <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
            <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
            <div class="flex-1 h-1 bg-gray-200 rounded-full"></div>
        </div>
        <p class="text-sm text-gray-500 mb-4 text-center">Step 1 of 4</p>

        <form wire:submit="nextStep" class="">
            <div>
                <p class="text-sm text-gray-600 mb-4">Isi data sesuai dengan KTP Anda</p>

                <!-- NIK -->
                <div>
                    <label for="nik" class="block text-xs font-semibold text-gray-700 mb-2">NIK *</label>
                    <input wire:model.live.debounce.500ms="nik" id="nik" type="text" maxlength="16" placeholder="16 digit NIK"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        class="w-full px-4 py-3 bg-white border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition shadow-sm">
                    <p class="text-xs text-gray-500 mt-1">{{ strlen($nik) }}/16 digit</p>
                    <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                </div>

                <!-- Nama Lengkap -->
                <div>
                    <label for="full_name" class="block text-xs font-semibold text-gray-700 mb-2">Nama Lengkap *</label>
                    <input wire:model="full_name" id="full_name" type="text" placeholder="Sesuai KTP"
                        class="w-full px-4 py-3 bg-white border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition shadow-sm">
                    <x-input-error :messages="$errors->get('full_name')" class="mt-2" />
                </div>

                <!-- Tempat & Tanggal Lahir -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="place_of_birth" class="block text-xs font-semibold text-gray-700 mb-2">Tempat Lahir
                            *</label>
                        <input wire:model="place_of_birth" id="place_of_birth" type="text" placeholder="Kota"
                            class="w-full px-4 py-3 bg-white border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition shadow-sm">
                        <x-input-error :messages="$errors->get('place_of_birth')" class="mt-2" />
                    </div>
                    <div>
                        <label for="date_of_birth" class="block text-xs font-semibold text-gray-700 mb-2">Tanggal Lahir
                            *</label>
                        <input wire:model="date_of_birth" id="date_of_birth" type="date"
                            class="w-full px-4 py-3 bg-white border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition shadow-sm">
                        <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                    </div>
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Jenis Kelamin *</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label
                            class="flex items-center px-4 py-3 bg-white rounded-xl cursor-pointer border-2 transition"
                            :class="$wire.gender === 'Laki-laki' ? 'border-primary-500' : 'border-transparent'">
                            <input wire:model="gender" type="radio" value="Laki-laki" class="w-4 h-4 text-primary-500">
                            <span class="ml-2 text-sm text-gray-700">Laki-laki</span>
                        </label>
                        <label
                            class="flex items-center px-4 py-3 bg-white rounded-xl cursor-pointer border-2 transition"
                            :class="$wire.gender === 'Perempuan' ? 'border-primary-500' : 'border-transparent'">
                            <input wire:model="gender" type="radio" value="Perempuan" class="w-4 h-4 text-primary-500">
                            <span class="ml-2 text-sm text-gray-700">Perempuan</span>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                </div>

                <!-- Alamat -->
                <div>
                    <label for="address" class="block text-xs font-semibold text-gray-700 mb-2">Alamat *</label>
                    <textarea wire:model="address" id="address" rows="3" placeholder="Alamat lengkap sesuai KTP"
                        class="w-full px-4 py-3 bg-white border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition shadow-sm"></textarea>
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>

                <!-- RT/RW -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="rt" class="block text-xs font-semibold text-gray-700 mb-2">RT *</label>
                        <input wire:model="rt" id="rt" type="text" placeholder="001"
                            class="w-full px-4 py-3 bg-white border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition shadow-sm">
                        <x-input-error :messages="$errors->get('rt')" class="mt-2" />
                    </div>
                    <div>
                        <label for="rw" class="block text-xs font-semibold text-gray-700 mb-2">RW *</label>
                        <input wire:model="rw" id="rw" type="text" placeholder="002"
                            class="w-full px-4 py-3 bg-white border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition shadow-sm">
                        <x-input-error :messages="$errors->get('rw')" class="mt-2" />
                    </div>
                </div>

                <!-- Kelurahan/Desa -->
                <div>
                    <label for="kelurahan" class="block text-xs font-semibold text-gray-700 mb-2">Kelurahan/Desa
                        *</label>
                    <input wire:model="kelurahan" id="kelurahan" type="text" placeholder="Nama Kelurahan/Desa"
                        class="w-full px-4 py-3 bg-white border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition shadow-sm">
                    <x-input-error :messages="$errors->get('kelurahan')" class="mt-2" />
                </div>

                <!-- Kecamatan -->
                <div>
                    <label for="kecamatan" class="block text-xs font-semibold text-gray-700 mb-2">Kecamatan *</label>
                    <input wire:model="kecamatan" id="kecamatan" type="text" placeholder="Nama Kecamatan"
                        class="w-full px-4 py-3 bg-white border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition shadow-sm">
                    <x-input-error :messages="$errors->get('kecamatan')" class="mt-2" />
                </div>

                <script>
                    (function () {
                        const prefix = 'registration_step1_';
                        const fields = ['nik', 'full_name', 'place_of_birth', 'date_of_birth', 'gender', 'address', 'rt', 'rw', 'kelurahan', 'kecamatan', 'city_id', 'city', 'province'];

                        // Load saved draft from localStorage into inputs
                        window.addEventListener('DOMContentLoaded', () => {
                            try {
                                fields.forEach(name => {
                                    const key = prefix + name;
                                    const el = document.getElementById(name);
                                    if (!el) return;
                                    const val = localStorage.getItem(key);
                                    if (val !== null) {
                                        el.value = val;
                                        el.dispatchEvent(new Event('input', { bubbles: true }));
                                    }
                                });
                            } catch (e) {
                                // ignore
                            }
                        });

                        // Save changes to localStorage on input
                        fields.forEach(name => {
                            const el = document.getElementById(name);
                            if (!el) return;
                            el.addEventListener('input', (ev) => {
                                try { localStorage.setItem(prefix + name, ev.target.value); } catch (e) { }
                            });
                        });

                        // Clear saved draft when Livewire dispatches clear-registration-step1
                        document.addEventListener('clear-registration-step1', () => {
                            try { fields.forEach(name => localStorage.removeItem(prefix + name)); } catch (e) { }
                        });

                        // Livewire will trigger a custom event name when dispatch() is called server-side
                        document.addEventListener('livewire:load', function () {
                            if (window.Livewire) {
                                window.Livewire.on('clear-registration-step1', () => {
                                    try { fields.forEach(name => localStorage.removeItem(prefix + name)); } catch (e) { }
                                });
                            }
                        });
                    })();
                </script>

                <!-- Auto-fill province when user picks kota -->
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const citySelect = document.getElementById('city_id');
                        const provinceInput = document.getElementById('province');
                        if (!citySelect || !provinceInput) return;

                        citySelect.addEventListener('change', function (ev) {
                            const opt = ev.target.selectedOptions && ev.target.selectedOptions[0];
                            if (opt && opt.dataset && opt.dataset.province) {
                                provinceInput.value = opt.dataset.province;
                                provinceInput.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        });
                    });
                </script>

                <!-- Kota/Kabupaten -->

                <!-- Kota/Kabupaten (realtime search) -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Kota/Kabupaten *</label>
                    @if(isset($cities) && count($cities) > 0)
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="cityQuery" id="city-search-input"
                                placeholder="Ketik nama Kota/Kabupaten..."
                                class="w-full px-4 py-3 bg-white border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition shadow-sm" autocomplete="off">

                            <input type="hidden" wire:model="city_id" id="city_id">

                            @if (!empty($searchResults))
                                <ul class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto z-50">
                                    @foreach ($searchResults as $c)
                                        <li wire:click="setCityId({{ $c['id'] }})"
                                            class="px-4 py-3 text-sm hover:bg-blue-50 cursor-pointer transition border-b border-gray-100 last:border-b-0 flex items-start gap-2">
                                            <div class="flex-1">
                                                <div class="font-semibold text-gray-900">{{ $c['name'] }}</div>
                                                <div class="text-xs text-gray-500">{{ $c['province'] }}</div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif (!empty($cityQuery) && strlen($cityQuery) >= 2 && empty($city_id))
                                <div class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-50">
                                    <div class="flex items-center gap-2 text-sm text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Kota tidak ditemukan
                                    </div>
                                </div>
                            @endif
                        </div>
                        <x-input-error :messages="$errors->get('city_id')" class="mt-2" />
                    @else
                        <input wire:model="city" id="city" type="text" placeholder="Ketik nama Kota/Kabupaten"
                            class="w-full px-4 py-3 bg-white border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition shadow-sm">
                        <p class="text-xs text-gray-500 mt-2">Daftar kota belum tersedia. Ketik nama kota secara manual.</p>
                        <x-input-error :messages="$errors->get('city')" class="mt-2" />
                    @endif
                </div>

                <!-- Provinsi -->
                <div>
                    <label for="province" class="block text-xs font-semibold text-gray-700 mb-2">Provinsi *</label>
                    <input wire:model="province" id="province" type="text" placeholder="Nama Provinsi"
                        class="w-full px-4 py-3 bg-white border-0 rounded-xl text-gray-700 text-sm placeholder-gray-400 focus:ring-2 focus:ring-primary-400 transition shadow-sm">
                    <x-input-error :messages="$errors->get('province')" class="mt-2" />
                </div>

                <!-- Agama, Status Perkawinan, dan Pekerjaan dihapus sesuai permintaan -->

            </div>

            <!-- Next Button -->
            <div class="pt-6 pb-2">
                <button type="submit" wire:loading.attr="disabled"
                    class="w-full bg-primary-500 hover:bg-primary-600 text-white font-bold py-3.5 rounded-full shadow-lg hover:shadow-xl transition disabled:opacity-50">
                    <span wire:loading.remove>Lanjutkan</span>
                    <span wire:loading>Memproses...</span>
                </button>
            </div>
        </form>
    </div>
</div>