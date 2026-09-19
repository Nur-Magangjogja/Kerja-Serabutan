<!-- Detail Alamat Lokasi (Pencarian Berbasis Peta & GPS) -->
<div id="group-location" class="space-y-3">
    @if($service_type === 'pickup_delivery')
        <!-- Mode Antar / Jemput: 2 Alamat (Titik Jemput & Titik Antar) -->
        <div class="space-y-3">
            <!-- Titik 1: Jemput (Pickup) -->
            <div class="p-3 bg-blue-50/60 dark:bg-gray-800/80 rounded-xl border border-blue-200/80 dark:border-gray-700 space-y-1.5 shadow-2xs"
                 x-data="{
                     searchQuery: @entangle('pickup_address').live,
                     searchResults: [],
                     isSearching: false,
                     showDropdown: false,
                     searchTimeout: null,
                     searchController: null,
                     searchReqId: 0,
                     init() {
                         window.addEventListener('cancel-active-searches', () => {
                             clearTimeout(this.searchTimeout);
                             if (this.searchController) this.searchController.abort();
                             this.isSearching = false;
                             this.showDropdown = false;
                             this.searchResults = [];
                         });
                         window.addEventListener('map-address-updated', (e) => {
                             const detail = e.detail || {};
                             if (detail.point === 'pickup' && detail.address) {
                                 this.searchQuery = detail.address;
                                 clearTimeout(this.searchTimeout);
                                 if (this.searchController) this.searchController.abort();
                                 this.isSearching = false;
                                 this.showDropdown = false;
                                 this.searchResults = [];
                             }
                         });
                         window.addEventListener('map-marker-cleared', (e) => {
                             const detail = e.detail || {};
                             if (detail.point === 'pickup') {
                                 clearTimeout(this.searchTimeout);
                                 if (this.searchController) this.searchController.abort();
                                 this.searchQuery = '';
                                 this.searchResults = [];
                                 this.showDropdown = false;
                                 this.isSearching = false;
                             }
                         });
                     },
                     handleInput() {
                         clearTimeout(this.searchTimeout);
                         if (this.searchController) {
                             this.searchController.abort();
                             this.searchController = null;
                         }

                         const q = (this.searchQuery || '').trim();
                         if (q.length < 2) {
                             this.searchResults = [];
                             this.showDropdown = false;
                             this.isSearching = false;
                             return;
                         }

                         this.isSearching = true;
                         const currentReq = ++this.searchReqId;

                         this.searchTimeout = setTimeout(async () => {
                             this.searchController = new AbortController();
                             try {
                                 let data = [];
                                 if (typeof window.searchIndonesianPlaces === 'function') {
                                     data = await window.searchIndonesianPlaces(q, 6, this.searchController.signal);
                                 } else {
                                     const res = await fetch('https://nominatim.openstreetmap.org/search?format=json&countrycodes=id&addressdetails=1&limit=6&q=' + encodeURIComponent(q + ', Indonesia'), {
                                         signal: this.searchController.signal,
                                         headers: { 'Accept-Language': 'id' }
                                     });
                                     data = await res.json() || [];
                                 }
                                 if (currentReq === this.searchReqId) {
                                     this.searchResults = data || [];
                                     this.showDropdown = this.searchResults.length > 0;
                                 }
                             } catch(err) {
                                 if (err.name !== 'AbortError') {
                                     this.searchResults = [];
                                 }
                             } finally {
                                 if (currentReq === this.searchReqId) {
                                     this.isSearching = false;
                                 }
                             }
                         }, 280);
                     },
                     selectPlace(item) {
                         clearTimeout(this.searchTimeout);
                         if (this.searchController) {
                             this.searchController.abort();
                             this.searchController = null;
                         }
                         this.isSearching = false;
                         this.showDropdown = false;
                         this.searchResults = [];

                         const lat = parseFloat(item.lat);
                         const lon = parseFloat(item.lon);
                         const fullAddr = item.display_name;
                         this.searchQuery = fullAddr;
                         
                         if (window.selectMapLocationForPoint) {
                             window.selectMapLocationForPoint('pickup', lat, lon, fullAddr);
                         } else if (window.selectMapLocation) {
                             if (window.setActiveMapPoint) window.setActiveMapPoint('pickup', false);
                             window.selectMapLocation(lat, lon, fullAddr);
                         }
                     }
                 }"
                 @click.outside="showDropdown = false">
                <div class="flex items-center justify-between flex-wrap gap-1">
                    <label class="block text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded-full bg-blue-600 text-white text-[10px] flex items-center justify-center font-bold">1</span>
                        Alamat Titik Jemput (Pickup) <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="setActiveMapPoint('pickup'); focusMapSection();"
                            class="px-2 py-0.5 rounded text-[10px] font-semibold bg-white dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-800/60 border border-blue-200 dark:border-blue-800 transition cursor-pointer flex items-center gap-1">
                            <span>🎯 Tentukan di Peta</span>
                        </button>
                        <button type="button" onclick="locateUserGPS('pickup')"
                            class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-600 text-white hover:bg-blue-700 transition cursor-pointer flex items-center gap-1 shadow-2xs">
                            <span>📍 GPS</span>
                        </button>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-blue-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                        x-model="searchQuery"
                        @input="handleInput()"
                        @focus="if(searchResults.length > 0) showDropdown = true"
                        id="pickup-address-input"
                        placeholder="Cari jalan, desa, kelurahan, kecamatan, tempat (misal: Jl Kaliurang, Condongcatur, Depok...)"
                        class="w-full pl-8 pr-8 py-2.5 text-xs rounded-lg border @error('pickup_address') border-red-500 ring-1 ring-red-500 bg-red-50/20 @else border-gray-300 dark:border-gray-700 @enderror bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center gap-1">
                        <span x-show="isSearching" class="text-blue-500 animate-spin text-xs">⏳</span>
                        <button type="button" x-show="!isSearching && searchQuery && searchQuery.length > 0"
                            @click="
                                clearTimeout(searchTimeout);
                                if (searchController) { searchController.abort(); searchController = null; }
                                searchQuery = '';
                                searchResults = [];
                                showDropdown = false;
                                isSearching = false;
                                $wire.set('pickup_address', '');
                                if (window.clearMapLocation) {
                                    window.clearMapLocation('pickup');
                                }
                            "
                            class="text-gray-400 hover:text-gray-600 text-xs cursor-pointer">✕</button>
                    </div>

                    <!-- Autocomplete Suggestions Dropdown -->
                    <div x-show="showDropdown && searchResults.length > 0" x-cloak
                        class="absolute z-50 left-0 right-0 mt-1 max-h-64 overflow-y-auto dropdown-scrollbar bg-white dark:bg-gray-800 rounded-xl border border-blue-200 dark:border-blue-800 shadow-xl divide-y divide-gray-100 dark:divide-gray-700/60">
                        <div class="px-3 py-1.5 bg-blue-50/80 dark:bg-blue-950/60 text-[10px] font-bold text-blue-800 dark:text-blue-300 flex items-center justify-between">
                            <span>📍 Hasil Pencarian Alamat (Titik Jemput):</span>
                            <span class="font-normal text-gray-400">Pilih untuk pasang pin</span>
                        </div>
                        <template x-for="item in searchResults" :key="item.place_id || item.osm_id || item.lat">
                            <button type="button" @click="selectPlace(item)" class="w-full text-left p-2.5 hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-start gap-2.5 transition cursor-pointer group">
                                <span class="text-base flex-shrink-0 mt-0.5" x-text="item.badge_icon || '📦'">📦</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <p class="text-xs font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 truncate" x-text="item.main_title || item.display_name.split(',')[0]"></p>
                                        <span x-show="item.badge" :class="item.badge_class || 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'" class="px-1.5 py-0.2 rounded text-[9px] font-semibold" x-text="item.badge"></span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-1 mt-0.5" x-text="item.hierarchy_subtitle || item.display_name"></p>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
                @error('pickup_address')
                    <span class="field-error-message text-red-500 dark:text-red-400 text-[11px] block font-medium">{{ $message }}</span>
                @enderror
            </div>

            <!-- Titik 2: Antar (Tujuan) -->
            <div class="p-3 bg-emerald-50/60 dark:bg-gray-800/80 rounded-xl border border-emerald-200/80 dark:border-gray-700 space-y-1.5 shadow-2xs"
                 x-data="{
                     searchQuery: @entangle('delivery_address').live,
                     searchResults: [],
                     isSearching: false,
                     showDropdown: false,
                     searchTimeout: null,
                     searchController: null,
                     searchReqId: 0,
                     init() {
                         window.addEventListener('cancel-active-searches', () => {
                             clearTimeout(this.searchTimeout);
                             if (this.searchController) this.searchController.abort();
                             this.isSearching = false;
                             this.showDropdown = false;
                             this.searchResults = [];
                         });
                         window.addEventListener('map-address-updated', (e) => {
                             const detail = e.detail || {};
                             if (detail.point === 'delivery' && detail.address) {
                                 this.searchQuery = detail.address;
                                 clearTimeout(this.searchTimeout);
                                 if (this.searchController) this.searchController.abort();
                                 this.isSearching = false;
                                 this.showDropdown = false;
                                 this.searchResults = [];
                             }
                         });
                         window.addEventListener('map-marker-cleared', (e) => {
                             const detail = e.detail || {};
                             if (detail.point === 'delivery') {
                                 clearTimeout(this.searchTimeout);
                                 if (this.searchController) this.searchController.abort();
                                 this.searchQuery = '';
                                 this.searchResults = [];
                                 this.showDropdown = false;
                                 this.isSearching = false;
                             }
                         });
                     },
                     handleInput() {
                         clearTimeout(this.searchTimeout);
                         if (this.searchController) {
                             this.searchController.abort();
                             this.searchController = null;
                         }

                         const q = (this.searchQuery || '').trim();
                         if (q.length < 2) {
                             this.searchResults = [];
                             this.showDropdown = false;
                             this.isSearching = false;
                             return;
                         }

                         this.isSearching = true;
                         const currentReq = ++this.searchReqId;

                         this.searchTimeout = setTimeout(async () => {
                             this.searchController = new AbortController();
                             try {
                                 let data = [];
                                 if (typeof window.searchIndonesianPlaces === 'function') {
                                     data = await window.searchIndonesianPlaces(q, 6, this.searchController.signal);
                                 } else {
                                     const res = await fetch('https://nominatim.openstreetmap.org/search?format=json&countrycodes=id&addressdetails=1&limit=6&q=' + encodeURIComponent(q + ', Indonesia'), {
                                         signal: this.searchController.signal,
                                         headers: { 'Accept-Language': 'id' }
                                     });
                                     data = await res.json() || [];
                                 }
                                 if (currentReq === this.searchReqId) {
                                     this.searchResults = data || [];
                                     this.showDropdown = this.searchResults.length > 0;
                                 }
                             } catch(err) {
                                 if (err.name !== 'AbortError') {
                                     this.searchResults = [];
                                 }
                             } finally {
                                 if (currentReq === this.searchReqId) {
                                     this.isSearching = false;
                                 }
                             }
                         }, 280);
                     },
                     selectPlace(item) {
                         clearTimeout(this.searchTimeout);
                         if (this.searchController) {
                             this.searchController.abort();
                             this.searchController = null;
                         }
                         this.isSearching = false;
                         this.showDropdown = false;
                         this.searchResults = [];

                         const lat = parseFloat(item.lat);
                         const lon = parseFloat(item.lon);
                         const fullAddr = item.display_name;
                         this.searchQuery = fullAddr;
                         
                         if (window.selectMapLocationForPoint) {
                             window.selectMapLocationForPoint('delivery', lat, lon, fullAddr);
                         } else if (window.selectMapLocation) {
                             if (window.setActiveMapPoint) window.setActiveMapPoint('delivery', false);
                             window.selectMapLocation(lat, lon, fullAddr);
                         }
                     }
                 }"
                 @click.outside="showDropdown = false">
                <div class="flex items-center justify-between flex-wrap gap-1">
                    <label class="block text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded-full bg-emerald-600 text-white text-[10px] flex items-center justify-center font-bold">2</span>
                        Alamat Titik Antar (Tujuan) <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="setActiveMapPoint('delivery'); focusMapSection();"
                            class="px-2 py-0.5 rounded text-[10px] font-semibold bg-white dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-800/60 border border-emerald-200 dark:border-emerald-800 transition cursor-pointer flex items-center gap-1">
                            <span>🎯 Tentukan di Peta</span>
                        </button>
                        <button type="button" onclick="locateUserGPS('delivery')"
                            class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition cursor-pointer flex items-center gap-1 shadow-2xs">
                            <span>📍 GPS</span>
                        </button>
                    </div>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-emerald-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                        x-model="searchQuery"
                        @input="handleInput()"
                        @focus="if(searchResults.length > 0) showDropdown = true"
                        id="delivery-address-input"
                        placeholder="Cari jalan, desa, kelurahan, kecamatan, tujuan (misal: Malioboro Mall, Jl Gejayan, Condongcatur...)"
                        class="w-full pl-8 pr-8 py-2.5 text-xs rounded-lg border @error('delivery_address') border-red-500 ring-1 ring-red-500 bg-red-50/20 @else border-gray-300 dark:border-gray-700 @enderror bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center gap-1">
                        <span x-show="isSearching" class="text-emerald-500 animate-spin text-xs">⏳</span>
                        <button type="button" x-show="!isSearching && searchQuery && searchQuery.length > 0"
                            @click="
                                clearTimeout(searchTimeout);
                                if (searchController) { searchController.abort(); searchController = null; }
                                searchQuery = '';
                                searchResults = [];
                                showDropdown = false;
                                isSearching = false;
                                $wire.set('delivery_address', '');
                                if (window.clearMapLocation) {
                                    window.clearMapLocation('delivery');
                                }
                            "
                            class="text-gray-400 hover:text-gray-600 text-xs cursor-pointer">✕</button>
                    </div>

                    <!-- Autocomplete Suggestions Dropdown -->
                    <div x-show="showDropdown && searchResults.length > 0" x-cloak
                        class="absolute z-50 left-0 right-0 mt-1 max-h-64 overflow-y-auto dropdown-scrollbar bg-white dark:bg-gray-800 rounded-xl border border-emerald-200 dark:border-emerald-800 shadow-xl divide-y divide-gray-100 dark:divide-gray-700/60">
                        <div class="px-3 py-1.5 bg-emerald-50/80 dark:bg-emerald-950/60 text-[10px] font-bold text-emerald-800 dark:text-emerald-300 flex items-center justify-between">
                            <span>🎯 Hasil Pencarian Alamat (Titik Antar):</span>
                            <span class="font-normal text-gray-400">Pilih untuk pasang pin</span>
                        </div>
                        <template x-for="item in searchResults" :key="item.place_id || item.osm_id || item.lat">
                            <button type="button" @click="selectPlace(item)" class="w-full text-left p-2.5 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 flex items-start gap-2.5 transition cursor-pointer group">
                                <span class="text-base flex-shrink-0 mt-0.5" x-text="item.badge_icon || '🎯'">🎯</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <p class="text-xs font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 truncate" x-text="item.main_title || item.display_name.split(',')[0]"></p>
                                        <span x-show="item.badge" :class="item.badge_class || 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300'" class="px-1.5 py-0.2 rounded text-[9px] font-semibold" x-text="item.badge"></span>
                                    </div>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-1 mt-0.5" x-text="item.hierarchy_subtitle || item.display_name"></p>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>
                @if((float) $route_distance_km > 0)
                    @php
                        $maxDistanceKm = \App\Models\AppSetting::getPickupDeliveryMaxDistanceKm();
                        $isExceeded = (float)$route_distance_km > $maxDistanceKm;
                        $estTravelMin = (new \App\Services\GeoService())->getRouteDurationMinutes((float)$route_distance_km, 25.0, 3);
                    @endphp
                    @if($isExceeded)
                        <div class="p-3.5 bg-red-600 dark:bg-red-900 rounded-xl border border-red-500 dark:border-red-700 text-white text-xs mt-2 space-y-1 shadow-sm">
                            <div class="flex items-center gap-1.5 font-bold text-white text-xs">
                                <span>⚠️</span>
                                <span>Jarak Rute Melebihi Batas Maksimal ({{ number_format((float)$route_distance_km, 1, ',', '.') }} KM / Maks. {{ $maxDistanceKm }} KM)</span>
                            </div>
                            <p class="text-[11px] leading-relaxed text-white font-medium">
                                Demi keselamatan berkendara dan jangkauan operasional armada sepeda motor, layanan Antar/Jemput dibatasi maksimal <strong class="text-white font-bold underline decoration-white/40">{{ $maxDistanceKm }} KM</strong>. Silakan sesuaikan titik penjemputan atau pengantaran Anda.
                            </p>
                        </div>
                    @else
                        <div class="flex items-center justify-between font-semibold text-emerald-950 dark:text-white text-xs bg-emerald-100 dark:bg-emerald-900/80 px-3 py-2.5 rounded-xl border border-emerald-300 dark:border-emerald-700 mt-1.5 shadow-2xs flex-wrap gap-2">
                            <div class="flex items-center gap-1.5">
                                <span>🛣️</span>
                                <span>Jarak Rute: <strong class="font-bold text-emerald-950 dark:text-white">{{ number_format((float)$route_distance_km, 1, ',', '.') }} KM</strong> (dibulatkan {{ ceil((float)$route_distance_km) }} KM)</span>
                            </div>
                            <div class="flex items-center gap-1 text-blue-800 dark:text-blue-200">
                                <span>⏱️</span>
                                <span>Estimasi: <strong class="font-bold text-blue-900 dark:text-white">~{{ $estTravelMin }} Menit</strong></span>
                            </div>
                            <span class="font-bold text-emerald-900 dark:text-white text-sm">Rp {{ number_format(app(\App\Services\HelpPricingService::class)->calculatePickupDeliveryFare((float)$route_distance_km), 0, ',', '.') }}</span>
                        </div>
                    @endif
                @endif
                @error('delivery_address')
                    <span class="field-error-message text-red-500 dark:text-red-400 text-[11px] block font-medium">{{ $message }}</span>
                @enderror
            </div>
        </div>
    @else
        <!-- Mode Kerja Serabutan (On-Site): 1 Alamat Tunggal -->
        <div x-data="{
                 searchQuery: @entangle('location').live,
                 searchResults: [],
                 isSearching: false,
                 showDropdown: false,
                 searchTimeout: null,
                 searchController: null,
                 searchReqId: 0,
                 init() {
                     window.addEventListener('cancel-active-searches', () => {
                         clearTimeout(this.searchTimeout);
                         if (this.searchController) this.searchController.abort();
                         this.isSearching = false;
                         this.showDropdown = false;
                         this.searchResults = [];
                     });
                     window.addEventListener('map-address-updated', (e) => {
                         const detail = e.detail || {};
                         if (detail.point === 'onsite' && detail.address) {
                             this.searchQuery = detail.address;
                             clearTimeout(this.searchTimeout);
                             if (this.searchController) this.searchController.abort();
                             this.isSearching = false;
                             this.showDropdown = false;
                             this.searchResults = [];
                         }
                     });
                     window.addEventListener('map-marker-cleared', (e) => {
                         const detail = e.detail || {};
                         if (detail.point === 'onsite') {
                             clearTimeout(this.searchTimeout);
                             if (this.searchController) this.searchController.abort();
                             this.searchQuery = '';
                             this.searchResults = [];
                             this.showDropdown = false;
                             this.isSearching = false;
                         }
                     });
                 },
                 handleInput() {
                     clearTimeout(this.searchTimeout);
                     if (this.searchController) {
                         this.searchController.abort();
                         this.searchController = null;
                     }

                     const q = (this.searchQuery || '').trim();
                     if (q.length < 2) {
                         this.searchResults = [];
                         this.showDropdown = false;
                         this.isSearching = false;
                         return;
                     }

                     this.isSearching = true;
                     const currentReq = ++this.searchReqId;

                     this.searchTimeout = setTimeout(async () => {
                         this.searchController = new AbortController();
                         try {
                             let data = [];
                             if (typeof window.searchIndonesianPlaces === 'function') {
                                 data = await window.searchIndonesianPlaces(q, 6, this.searchController.signal);
                             } else {
                                 const res = await fetch('https://nominatim.openstreetmap.org/search?format=json&countrycodes=id&addressdetails=1&limit=6&q=' + encodeURIComponent(q + ', Indonesia'), {
                                     signal: this.searchController.signal,
                                     headers: { 'Accept-Language': 'id' }
                                 });
                                 data = await res.json() || [];
                             }
                             if (currentReq === this.searchReqId) {
                                 this.searchResults = data || [];
                                 this.showDropdown = this.searchResults.length > 0;
                             }
                         } catch(err) {
                             if (err.name !== 'AbortError') {
                                 this.searchResults = [];
                             }
                         } finally {
                             if (currentReq === this.searchReqId) {
                                 this.isSearching = false;
                             }
                         }
                     }, 280);
                 },
                 selectPlace(item) {
                     clearTimeout(this.searchTimeout);
                     if (this.searchController) {
                         this.searchController.abort();
                         this.searchController = null;
                     }
                     this.isSearching = false;
                     this.showDropdown = false;
                     this.searchResults = [];

                     const lat = parseFloat(item.lat);
                     const lon = parseFloat(item.lon);
                     const fullAddr = item.display_name;
                     this.searchQuery = fullAddr;
                     
                     if (window.selectMapLocation) {
                         window.selectMapLocation(lat, lon, fullAddr);
                     }
                 }
             }"
             @click.outside="showDropdown = false">
            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                <span class="flex items-center justify-between">
                    <span class="flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                        </svg>
                        Alamat / Nama Lokasi Pekerjaan
                        <span class="text-xs font-normal text-gray-400 dark:text-gray-500 ml-1">(Ketik jalan, desa, kecamatan, kota, atau nama tempat)</span>
                    </span>
                    <span id="reverse-geocode-indicator" class="hidden text-[11px] text-blue-600 dark:text-blue-400 animate-pulse font-normal">
                        📍 Mendeteksi alamat...
                    </span>
                </span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-blue-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text"
                    x-model="searchQuery"
                    @input="handleInput()"
                    @focus="if(searchResults.length > 0) showDropdown = true"
                    id="location-input"
                    placeholder="Cari jalan, desa, kelurahan, kecamatan, kab, tempat (misal: Mall Malioboro, Jl Gejayan, Sleman...)"
                    class="w-full pl-9 pr-8 py-3 text-sm rounded-lg border @error('location') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-1">
                    <span x-show="isSearching" class="text-blue-500 animate-spin text-sm">⏳</span>
                    <button type="button" x-show="!isSearching && searchQuery && searchQuery.length > 0"
                        @click="
                            clearTimeout(searchTimeout);
                            if (searchController) { searchController.abort(); searchController = null; }
                            searchQuery = '';
                            searchResults = [];
                            showDropdown = false;
                            isSearching = false;
                            $wire.set('location', '');
                            if (window.clearMapLocation) {
                                window.clearMapLocation('onsite');
                            }
                        "
                        class="text-gray-400 hover:text-gray-600 text-sm cursor-pointer">✕</button>
                </div>

                <!-- Autocomplete Suggestions Dropdown -->
                <div x-show="showDropdown && searchResults.length > 0" x-cloak
                    class="absolute z-50 left-0 right-0 mt-1 max-h-64 overflow-y-auto dropdown-scrollbar bg-white dark:bg-gray-800 rounded-xl border border-blue-200 dark:border-blue-800 shadow-xl divide-y divide-gray-100 dark:divide-gray-700/60">
                    <div class="px-3 py-1.5 bg-blue-50/80 dark:bg-blue-950/60 text-[10px] font-bold text-blue-800 dark:text-blue-300 flex items-center justify-between">
                        <span>📍 Hasil Pencarian Alamat & Lokasi:</span>
                        <span class="font-normal text-gray-400">Pilih untuk menentukan titik</span>
                    </div>
                    <template x-for="item in searchResults" :key="item.place_id || item.osm_id || item.lat">
                        <button type="button" @click="selectPlace(item)" class="w-full text-left p-2.5 hover:bg-blue-50 dark:hover:bg-blue-900/30 flex items-start gap-2.5 transition cursor-pointer group">
                            <span class="text-base flex-shrink-0 mt-0.5" x-text="item.badge_icon || '📍'">📍</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <p class="text-xs font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 truncate" x-text="item.main_title || item.display_name.split(',')[0]"></p>
                                    <span x-show="item.badge" :class="item.badge_class || 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'" class="px-1.5 py-0.2 rounded text-[9px] font-semibold" x-text="item.badge"></span>
                                </div>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 line-clamp-1 mt-0.5" x-text="item.hierarchy_subtitle || item.display_name"></p>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
            @error('location')
                <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1.5 block flex items-center font-medium">
                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                    {{ $message }}
                </span>
            @enderror
        </div>
    @endif
</div>
