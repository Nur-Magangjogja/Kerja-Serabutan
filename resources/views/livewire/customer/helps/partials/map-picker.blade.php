<!-- Tandai Lokasi di Peta -->
<div id="group-map" class="space-y-2">
    <div class="flex items-center justify-between mb-1 flex-wrap gap-2">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
            <span class="flex items-center">
                <svg class="w-3.5 h-3.5 mr-1.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                </svg>
                {{ $service_type === 'pickup_delivery' ? 'Peta Rute & Titik Pengantaran' : 'Titik Lokasi Pekerjaan' }}
                <span class="text-red-500 ml-1">*</span>
            </span>
        </label>
        <button type="button" onclick="locateUserGPS()" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-800/60 transition shadow-sm active:scale-95 cursor-pointer">
            <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2m10-10h-2M4 12H2m15.071-7.071l-1.414 1.414M8.343 15.657l-1.414 1.414m12.728 0l-1.414-1.414M8.343 8.343L6.929 6.929M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
            <span id="btn-gps-label">Gunakan Lokasi GPS</span>
        </button>
    </div>

    <!-- Segmented Switcher untuk Antar / Jemput -->
    @if($service_type === 'pickup_delivery')
        <div class="grid grid-cols-2 gap-2 p-1 bg-gray-100 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 text-xs"
             x-data="{
                 activePoint: window.activeMapPoint || 'pickup',
                 setPoint(p) {
                     this.activePoint = p;
                     if (window.setActiveMapPoint) window.setActiveMapPoint(p);
                 }
             }"
             x-init="
                 window.addEventListener('active-point-changed', (e) => {
                     const pt = e.detail ? (e.detail.point || e.detail) : 'pickup';
                     activePoint = pt;
                 });
             ">
            <button type="button" id="btn-tab-pickup" @click="setPoint('pickup')"
                :class="activePoint === 'pickup' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                class="py-2 px-3 rounded-lg font-bold flex items-center justify-center gap-1.5 transition cursor-pointer">
                <span :class="activePoint === 'pickup' ? 'bg-white text-blue-600' : 'bg-blue-600 text-white'" class="w-4 h-4 rounded-full text-[10px] flex items-center justify-center font-bold">1</span>
                <span>Titik Jemput</span>
            </button>
            <button type="button" id="btn-tab-delivery" @click="setPoint('delivery')"
                :class="activePoint === 'delivery' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                class="py-2 px-3 rounded-lg font-bold flex items-center justify-center gap-1.5 transition cursor-pointer">
                <span :class="activePoint === 'delivery' ? 'bg-white text-emerald-600' : 'bg-emerald-600 text-white'" class="w-4 h-4 rounded-full text-[10px] flex items-center justify-center font-bold">2</span>
                <span>Titik Antar</span>
            </button>
        </div>

        <div id="active-point-banner"
             x-data="{ activePoint: window.activeMapPoint || 'pickup' }"
             x-init="
                 window.addEventListener('active-point-changed', (e) => {
                     activePoint = e.detail ? (e.detail.point || e.detail) : 'pickup';
                 });
             "
             :class="activePoint === 'pickup' ? 'bg-blue-50 dark:bg-blue-950/50 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800' : 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'"
             class="p-2.5 rounded-xl text-xs font-semibold flex items-center justify-between gap-2 border">
            <div class="flex items-center gap-2">
                <span class="text-base" id="active-point-icon" x-text="activePoint === 'pickup' ? '📦' : '🎯'">📦</span>
                <span id="active-point-text">
                    <template x-if="activePoint === 'pickup'">
                        <span>Sedang Menentukan: <strong>Titik 1 (Jemput)</strong>. Klik pada peta atau cari tempat.</span>
                    </template>
                    <template x-if="activePoint === 'delivery'">
                        <span>Sedang Menentukan: <strong>Titik 2 (Antar/Tujuan)</strong>. Klik pada peta atau cari tempat.</span>
                    </template>
                </span>
            </div>
        </div>
    @endif

    <!-- Search Place on Map -->
    <div class="relative mb-2" x-data="{
        searchQuery: '',
        searchResults: [],
        isSearching: false,
        showResults: false,
        searchTimeout: null,
        handleSearchInput() {
            clearTimeout(this.searchTimeout);
            if (this.searchQuery.trim().length < 3) {
                this.searchResults = [];
                this.showResults = false;
                return;
            }
            this.isSearching = true;
            this.searchTimeout = setTimeout(() => {
                fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(this.searchQuery + ', Indonesia') + '&addressdetails=1&limit=6', {
                    headers: { 'Accept-Language': 'id' }
                })
                .then(res => res.json())
                .then(data => {
                    this.searchResults = data || [];
                    this.showResults = true;
                    this.isSearching = false;
                })
                .catch(() => {
                    this.isSearching = false;
                });
            }, 300);
        },
        selectResult(item) {
            const lat = parseFloat(item.lat);
            const lon = parseFloat(item.lon);
            this.searchQuery = item.display_name.split(',').slice(0, 3).join(', ');
            this.showResults = false;
            
            if (window.selectMapLocation) {
                window.selectMapLocation(lat, lon, item.display_name);
            }
        }
    }" @click.outside="showResults = false">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text"
                x-model="searchQuery"
                @input="handleSearchInput()"
                @focus="if(searchResults.length > 0) showResults = true"
                id="map-search-input"
                placeholder="{{ $service_type === 'pickup_delivery' ? '🔍 Cari alamat Titik 1 (Jemput)...' : '🔍 Cari nama tempat, gedung, jalan, atau area...' }}"
                class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none shadow-2xs">
            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center">
                <span x-show="isSearching" class="text-blue-500 animate-spin text-xs">⏳</span>
                <button type="button" x-show="!isSearching && searchQuery.length > 0" @click="searchQuery = ''; searchResults = []; showResults = false" class="text-gray-400 hover:text-gray-600 text-xs cursor-pointer">✕</button>
            </div>
        </div>

        <!-- Search Results Dropdown -->
        <div x-show="showResults && searchResults.length > 0" x-cloak
            class="absolute z-50 left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xl divide-y divide-gray-100 dark:divide-gray-700/60">
            <template x-for="item in searchResults" :key="item.place_id">
                <button type="button" @click="selectResult(item)" class="w-full text-left p-2.5 hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-start gap-2 transition cursor-pointer group">
                    <span class="text-base flex-shrink-0">📍</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 truncate" x-text="item.display_name.split(',')[0]"></p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate" x-text="item.display_name"></p>
                    </div>
                </button>
            </template>
        </div>
    </div>

    <!-- Restricted Zone Warning Alert -->
    <div id="restricted-zone-warning"
         x-data="{ show: false, message: '' }"
         x-show="show"
         x-cloak
         x-init="
             window.addEventListener('restricted-location-detected', (e) => {
                 message = (e.detail && e.detail.reason) ? e.detail.reason : 'Titik lokasi berada di wilayah terlarang atau perairan.';
                 show = true;
                 setTimeout(() => show = false, 8000);
             });
         "
         class="bg-red-50 dark:bg-red-950/60 border border-red-300 dark:border-red-800 rounded-xl p-3 mb-2 text-xs flex items-start gap-2.5 text-red-800 dark:text-red-300 shadow-sm transition-all">
        <span class="text-base flex-shrink-0">⛔</span>
        <div class="flex-1 min-w-0">
            <p class="font-bold text-red-900 dark:text-red-200">Wilayah Tidak Dapat Dilayani</p>
            <p class="text-[11px] mt-0.5 text-red-700 dark:text-red-400" x-text="message"></p>
        </div>
        <button type="button" @click="show = false" class="text-red-400 hover:text-red-600 font-bold">&times;</button>
    </div>

    <!-- Map Container -->
    <div class="relative rounded-xl overflow-hidden border @error('latitude') border-red-500 ring-2 ring-red-500/30 @else border-gray-300 dark:border-gray-700 @enderror shadow-inner bg-gray-100 dark:bg-gray-800 mb-2">
        <div wire:ignore id="map" style="height: 300px; min-height: 300px;" class="w-full"></div>
    </div>

    <!-- Koordinat Display & Status Geocoding -->
    <div id="coordinates-display"
        class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-lg p-2.5 mb-2 hidden flex items-center justify-between flex-wrap gap-2 text-xs">
        <div class="flex items-center gap-1.5 text-emerald-800 dark:text-emerald-300 font-medium">
            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Titik GPS: <span id="lat-display" class="font-mono font-semibold">-</span>, <span id="lng-display" class="font-mono font-semibold">-</span></span>
        </div>
        <span id="gps-status-pill" class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 text-[11px] font-semibold">Tersimpan</span>
    </div>

    <!-- Auto-Detected Territory Badge -->
    <div id="territory-display"
        class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-lg p-2.5 mb-2 hidden flex items-center justify-between flex-wrap gap-2 text-xs">
        <div class="flex items-center gap-1.5 text-blue-800 dark:text-blue-300 font-medium">
            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
            <span>Wilayah Terdeteksi dari Peta: <strong id="territory-name-display" class="font-bold">-</strong></span>
        </div>
        <span class="px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-[11px] font-semibold">Tersinkron Peta</span>
    </div>

    <!-- Hidden inputs for Livewire coordinates & service type -->
    <input type="hidden" wire:model="service_type" id="service-type-input" value="{{ $service_type }}">
    <input type="hidden" wire:model="latitude" id="latitude-input">
    <input type="hidden" wire:model="longitude" id="longitude-input">
    <input type="hidden" wire:model="pickup_latitude" id="pickup-latitude-input">
    <input type="hidden" wire:model="pickup_longitude" id="pickup-longitude-input">
    <input type="hidden" wire:model="delivery_latitude" id="delivery-latitude-input">
    <input type="hidden" wire:model="delivery_longitude" id="delivery-longitude-input">

    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center">
        <svg class="w-3.5 h-3.5 mr-1 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ $service_type === 'pickup_delivery' ? 'Klik pada peta atau geser pin 1 (Jemput) & 2 (Antar) untuk menentukan rute.' : 'Klik pada peta atau geser pin untuk menentukan titik lokasi yang tepat.' }}
    </p>

    @error('latitude')
        <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1.5 block font-medium flex items-center">
            <svg class="w-3.5 h-3.5 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            {{ $message }}
        </span>
    @enderror
</div>
