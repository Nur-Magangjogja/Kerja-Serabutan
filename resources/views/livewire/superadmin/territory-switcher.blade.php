<div class="relative" 
     x-data="{ open: false }" 
     @click.away="open = false">

    {{-- Trigger Button --}}
    <button type="button" @click="open = !open"
        class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl text-xs font-bold shadow-2xs cursor-pointer active:scale-95 transition-all max-w-[130px] xs:max-w-[160px] sm:max-w-[220px] shrink-0
        @if($territory['type'] === 'district')
            bg-emerald-500/15 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 ring-2 ring-emerald-500/20
        @elseif($territory['type'] === 'city')
            bg-indigo-500/15 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30 ring-2 ring-indigo-500/20
        @else
            bg-primary-500/10 dark:bg-primary-500/20 text-primary-700 dark:text-primary-300 border border-primary-500/30 hover:bg-primary-500/20
        @endif"
        title="{{ $territory['label'] }} (Klik untuk ganti filter wilayah)">
        
        @if($territory['type'] === 'district')
            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
            </svg>
        @elseif($territory['type'] === 'city')
            <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        @else
            <svg class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        @endif

        <span class="truncate leading-tight text-[11px] sm:text-xs">{{ $territory['label'] }}</span>

        <svg class="w-3 h-3 text-current transition-transform duration-200 shrink-0" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Mobile Backdrop Overlay --}}
    <div x-cloak x-show="open" 
        @click="open = false" 
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/40 backdrop-blur-[2px] z-[55] sm:hidden">
    </div>

    {{-- Dropdown Modal Popover (Responsive Mobile & Desktop) --}}
    <div x-cloak x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="fixed inset-x-3 top-16 sm:absolute sm:inset-auto sm:right-0 sm:top-full sm:mt-2 w-auto sm:w-[26rem] max-h-[85vh] sm:max-h-[35rem] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 p-3 z-[60] flex flex-col overscroll-contain">
        
        {{-- Popover Header --}}
        <div class="pb-2.5 mb-2 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">Filter Wilayah Super Admin</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-semibold text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">
                    {{ $totalCities }} Kota • {{ $totalDistricts }} Kec.
                </span>
                {{-- Close Button for Mobile --}}
                <button type="button" @click="open = false" class="sm:hidden p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Guide Banner: 2 Opsi --}}
        <div class="mb-2 p-2 rounded-xl bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/60 text-[10px] text-gray-500 dark:text-gray-400 space-y-1">
            <div class="flex items-center gap-1.5 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                <span><strong>Opsi 1:</strong> Klik <span class="text-indigo-600 dark:text-indigo-400 font-bold">Semua Data Kota</span> untuk pantau 1 kota utuh</span>
            </div>
            <div class="flex items-center gap-1.5 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                <span><strong>Opsi 2:</strong> Buka kota lalu klik salah satu <span class="text-emerald-600 dark:text-emerald-400 font-bold">Kecamatan</span> khusus</span>
            </div>
        </div>

        {{-- Search Input with Clear Button --}}
        <div class="relative mb-2">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text"
                wire:model.live.debounce.200ms="search"
                placeholder="Cari Kota / Kabupaten / Kecamatan..."
                class="w-full pl-9 pr-8 py-2 text-xs bg-gray-50 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
            
            @if(!empty($search))
                <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            @endif
        </div>

        {{-- Scrollable Territory List --}}
        <div class="overflow-y-auto flex-1 max-h-60 sm:max-h-76 pr-1 space-y-1.5 dropdown-scrollbar">
            
            {{-- Option: Semua Wilayah (Nasional) --}}
            <button type="button" wire:click="selectTerritory('all'); open = false"
                class="w-full flex items-center justify-between p-2 sm:p-2.5 rounded-xl text-xs font-bold transition text-left cursor-pointer
                @if($territory['type'] === 'all')
                    bg-primary-50 dark:bg-primary-950/70 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800 shadow-2xs
                @else
                    text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-750 border border-transparent
                @endif">
                <div class="flex items-center gap-2 sm:gap-2.5 min-w-0">
                    <div class="w-7 h-7 rounded-lg @if($territory['type'] === 'all') bg-primary-600 text-white @else bg-gray-100 dark:bg-gray-700 text-gray-500 @endif flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="font-bold leading-tight truncate">Semua Wilayah (Nasional)</p>
                        <p class="text-[10px] font-normal text-gray-400 mt-0.5 truncate">Monitoring global tanpa filter wilayah</p>
                    </div>
                </div>
                @if($territory['type'] === 'all')
                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </button>

            {{-- Hierarchical Cities & Districts (Active City is Pinned to the Top) --}}
            @forelse($cities as $city)
                @php
                    $isPinnedCity = ($activeCityId !== null && (int)$city->id === (int)$activeCityId);
                    $isCityExpanded = in_array((int)$city->id, $expandedCityIds, true);
                    $isCityActive = ($territory['type'] === 'city' && (int)$territory['id'] === (int)$city->id);
                    $hasActiveChildDistrict = ($territory['type'] === 'district' && $activeCityId !== null && (int)$city->id === (int)$activeCityId);
                @endphp

                <div class="rounded-xl border 
                    @if($isCityActive)
                        border-indigo-400 dark:border-indigo-600 bg-indigo-50/70 dark:bg-indigo-950/40 ring-2 ring-indigo-500/20 shadow-2xs
                    @elseif($hasActiveChildDistrict)
                        border-emerald-400 dark:border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/30 ring-2 ring-emerald-500/20 shadow-2xs
                    @elseif($isPinnedCity)
                        border-amber-300 dark:border-amber-700 bg-amber-50/30 dark:bg-amber-950/20 shadow-2xs
                    @else
                        border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-750/50
                    @endif 
                    overflow-hidden transition-all">
                    
                    {{-- City Header Row (Responsive Layout) --}}
                    <div class="flex items-center justify-between p-2 gap-1.5">
                        <div wire:click="toggleExpandCity({{ $city->id }})"
                             class="flex items-center gap-1.5 sm:gap-2 min-w-0 flex-1 cursor-pointer select-none hover:opacity-85 transition">
                            
                            {{-- Accordion Toggle Icon / Loading Spinner --}}
                            <button type="button" 
                                    wire:click.stop="toggleExpandCity({{ $city->id }})"
                                    class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-transform cursor-pointer shrink-0" 
                                    title="{{ $isCityExpanded ? 'Tutup Daftar Kecamatan' : 'Buka Daftar Kecamatan' }}">
                                
                                <div wire:loading.remove wire:target="toggleExpandCity({{ $city->id }})">
                                    <svg class="w-3.5 h-3.5 transition-transform duration-150 {{ $isCityExpanded ? 'rotate-90 text-primary-500' : '' }}"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                                
                                <div wire:loading wire:target="toggleExpandCity({{ $city->id }})">
                                    <svg class="w-3.5 h-3.5 text-primary-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </div>
                            </button>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1 sm:gap-1.5 flex-wrap">
                                    <span class="text-xs font-bold text-gray-900 dark:text-gray-100 truncate">{{ $city->name }}</span>
                                    
                                    {{-- Pinned Badge --}}
                                    @if($isPinnedCity)
                                        <span class="text-[9px] font-bold text-amber-700 dark:text-amber-300 bg-amber-500/15 dark:bg-amber-500/25 px-1.5 py-0.2 rounded-md border border-amber-500/30 shrink-0">📌 Tersemat</span>
                                    @endif

                                    @if($isCityActive)
                                        <span class="text-[9px] font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-500/15 px-1.5 py-0.2 rounded-md shrink-0">Kota Aktif</span>
                                    @elseif($hasActiveChildDistrict)
                                        <span class="text-[9px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-500/15 px-1.5 py-0.2 rounded-md shrink-0">Kec. Aktif</span>
                                    @endif

                                    @if($city->province && !$isPinnedCity)
                                        <span class="text-[9px] text-gray-400 truncate hidden xs:inline">({{ $city->province }})</span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-gray-400 block truncate">
                                    {{ $city->districts_count ?? ($city->relationLoaded('districts') ? $city->districts->count() : 0) }} Kec. • 
                                    <span class="hover:underline text-primary-600 dark:text-primary-400 font-medium">
                                        {{ $isCityExpanded ? 'Tutup daftar' : 'Buka daftar' }}
                                    </span>
                                </span>
                            </div>
                        </div>

                        {{-- Opsi 1: Quick Select Entire City (Semua Data Kota) --}}
                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button" wire:click="selectTerritory('city', {{ $city->id }}); open = false"
                                class="px-2 sm:px-2.5 py-1 sm:py-1.5 rounded-lg text-[10px] font-bold transition flex items-center gap-1 cursor-pointer whitespace-nowrap
                                @if($isCityActive)
                                    bg-indigo-600 text-white shadow-xs
                                @else
                                    bg-white dark:bg-gray-700 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700/60 hover:bg-indigo-600 hover:text-white
                                @endif"
                                title="Opsi 1: Pantau seluruh data di kota {{ $city->name }}">
                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                @if($isCityActive)
                                    <span>✓ Kota Aktif</span>
                                @else
                                    <span><span class="hidden xs:inline">Semua </span>Kota</span>
                                @endif
                            </button>
                        </div>
                    </div>

                    {{-- Opsi 2: Expanded Districts List (Kecamatan Spesifik Dimuat On-Demand) --}}
                    @if($isCityExpanded)
                        <div class="px-2 sm:px-2.5 pb-2.5 pt-1.5 border-t border-gray-100 dark:border-gray-700/80 bg-white/80 dark:bg-gray-800/80 space-y-1.5 transition-all">
                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-wider py-0.5 flex items-center justify-between">
                                <span class="flex items-center gap-1 truncate">
                                    <span>↳</span>
                                    <span class="truncate">Opsi 2: Pilih 1 Kec. di {{ $city->name }}</span>
                                </span>
                                <span class="text-[9px] font-normal text-emerald-600 dark:text-emerald-400 shrink-0 hidden xs:inline">Filter khusus</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 max-h-44 overflow-y-auto dropdown-scrollbar pr-1">
                                @forelse($city->districts ?? [] as $district)
                                    @php
                                        $isDistrictActive = ($territory['type'] === 'district' && (int)$territory['id'] === (int)$district->id);
                                        $isMatched = !empty($searchTerm) && (
                                            stripos($district->name, $searchTerm) !== false || 
                                            (!empty($cleanSearch) && stripos($district->name, $cleanSearch) !== false)
                                        );
                                    @endphp
                                    <button type="button" wire:click="selectTerritory('district', {{ $district->id }}); open = false"
                                        class="flex items-center justify-between px-2 py-1.5 rounded-lg text-xs transition text-left cursor-pointer min-w-0
                                        @if($isDistrictActive)
                                            bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-300 dark:border-emerald-800 shadow-2xs
                                        @elseif($isMatched)
                                            bg-emerald-500/10 dark:bg-emerald-500/15 text-emerald-800 dark:text-emerald-200 font-semibold border border-emerald-500/30 ring-1 ring-emerald-500/20
                                        @else
                                            text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/70 font-medium
                                        @endif">
                                        <div class="flex items-center gap-1.5 truncate min-w-0">
                                            <span class="w-1.5 h-1.5 rounded-full shrink-0 @if($isDistrictActive) bg-emerald-500 @elseif($isMatched) bg-emerald-400 animate-pulse @else bg-gray-300 dark:bg-gray-600 @endif"></span>
                                            <span class="truncate text-[11px]">Kec. {{ $district->name }}</span>
                                        </div>
                                        @if($isDistrictActive)
                                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @elseif($isMatched)
                                            <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-tighter shrink-0 ml-1">Cocok</span>
                                        @endif
                                    </button>
                                @empty
                                    <div class="col-span-1 sm:col-span-2 py-3 text-center text-gray-400 text-xs">
                                        Tidak ada data kecamatan
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-6 text-center text-gray-400 text-xs">
                    <p>Tidak ditemukan wilayah yang sesuai kata kunci "{{ $search }}"</p>
                </div>
            @endforelse
        </div>

        {{-- Popover Footer --}}
        @if($territory['type'] !== 'all')
            <div class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-[11px]">
                <span class="text-gray-500 dark:text-gray-400 truncate mr-2">Filter aktif: <strong class="text-gray-800 dark:text-gray-200">{{ $territory['label'] }}</strong></span>
                <button type="button" wire:click="selectTerritory('all'); open = false" class="text-primary-600 dark:text-primary-400 font-bold hover:underline cursor-pointer shrink-0">
                    Reset Global
                </button>
            </div>
        @endif
    </div>
</div>
