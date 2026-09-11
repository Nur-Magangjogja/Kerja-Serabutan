<div class="relative" x-data="{ open: false }" @click.away="open = false">
    {{-- Trigger Button --}}
    <button type="button" @click="open = !open"
        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-bold shadow-2xs cursor-pointer active:scale-95 transition-all
        @if($territory['type'] === 'district')
            bg-emerald-500/15 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 ring-2 ring-emerald-500/20
        @elseif($territory['type'] === 'city')
            bg-indigo-500/15 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30 ring-2 ring-indigo-500/20
        @else
            bg-primary-500/10 dark:bg-primary-500/20 text-primary-700 dark:text-primary-300 border border-primary-500/30 hover:bg-primary-500/20
        @endif"
        title="Ganti Wilayah Pantauan Super Admin (Kabupaten/Kota -> Kecamatan)">
        
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

        <div class="flex flex-col text-left">
            <span class="max-w-[190px] truncate leading-tight">{{ $territory['label'] }}</span>
        </div>

        <svg class="w-3 h-3 text-current transition-transform duration-200 shrink-0" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown Modal Popover --}}
    <div x-cloak x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="absolute right-0 sm:left-0 sm:right-auto mt-2 w-84 sm:w-96 max-h-[34rem] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 p-3 z-[60] flex flex-col">
        
        {{-- Popover Header --}}
        <div class="pb-2.5 mb-2 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                <span class="text-xs font-bold text-gray-800 dark:text-gray-200">Filter Wilayah Super Admin</span>
            </div>
            <span class="text-[10px] font-semibold text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">
                {{ $totalCities }} Kota • {{ $totalDistricts }} Kec.
            </span>
        </div>

        {{-- Search Input with Clear Button --}}
        <div class="relative mb-2.5">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text"
                wire:model.live.debounce.150ms="search"
                placeholder="Cari Kota / Kabupaten / Kecamatan..."
                class="w-full pl-9 pr-8 py-2 text-xs bg-gray-50 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
            
            @if(!empty($search))
                <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            @endif
        </div>

        {{-- Scrollable Territory List --}}
        <div class="overflow-y-auto max-h-80 pr-1.5 space-y-1.5 dropdown-scrollbar">
            
            {{-- Option: Semua Wilayah (Nasional) --}}
            <button type="button" wire:click="selectTerritory('all'); open = false"
                class="w-full flex items-center justify-between p-2.5 rounded-xl text-xs font-bold transition text-left cursor-pointer
                @if($territory['type'] === 'all')
                    bg-primary-50 dark:bg-primary-950/70 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800 shadow-2xs
                @else
                    text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-750 border border-transparent
                @endif">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-7 h-7 rounded-lg @if($territory['type'] === 'all') bg-primary-600 text-white @else bg-gray-100 dark:bg-gray-700 text-gray-500 @endif flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold leading-tight">Semua Wilayah (Nasional)</p>
                        <p class="text-[10px] font-normal text-gray-400 mt-0.5">Monitoring global tanpa batasan regional</p>
                    </div>
                </div>
                @if($territory['type'] === 'all')
                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </button>

            {{-- Hierarchical Cities & Districts --}}
            @forelse($cities as $city)
                @php
                    $isCityActive = ($territory['type'] === 'city' && (int)$territory['id'] === (int)$city->id);
                    $isExpanded   = in_array((int)$city->id, $effectiveExpanded, true);
                    $hasActiveChildDistrict = ($territory['type'] === 'district' && $city->districts->pluck('id')->contains((int)$territory['id']));
                @endphp

                <div class="rounded-xl border @if($isCityActive) border-indigo-300 dark:border-indigo-700 bg-indigo-50/40 dark:bg-indigo-950/30 @elseif($hasActiveChildDistrict) border-emerald-300 dark:border-emerald-700 bg-emerald-50/20 dark:bg-emerald-950/20 @else border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-750/50 @endif overflow-hidden transition-all">
                    
                    {{-- City Header Row --}}
                    <div class="flex items-center justify-between p-2">
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                            <button type="button" wire:click="toggleExpandCity({{ $city->id }})" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-transform cursor-pointer" title="Perluas / Tutup Kecamatan">
                                <svg class="w-3.5 h-3.5 transition-transform duration-150 {{ $isExpanded ? 'rotate-90 text-primary-500' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>

                            <div class="truncate">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-bold text-gray-900 dark:text-gray-100 truncate">{{ $city->name }}</span>
                                    @if($city->province)
                                        <span class="text-[9px] text-gray-400 truncate">({{ $city->province }})</span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-gray-400 block">{{ $city->districts->count() }} Kecamatan</span>
                            </div>
                        </div>

                        {{-- Quick Select Entire City --}}
                        <div class="flex items-center gap-1 shrink-0">
                            <button type="button" wire:click="selectTerritory('city', {{ $city->id }}); open = false"
                                class="px-2 py-1 rounded-lg text-[10px] font-bold transition cursor-pointer
                                @if($isCityActive)
                                    bg-indigo-600 text-white shadow-xs
                                @else
                                    bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-600
                                @endif"
                                title="Pantau seluruh kota {{ $city->name }}">
                                @if($isCityActive)
                                    ✓ Aktif
                                @else
                                    Pilih Kota
                                @endif
                            </button>
                        </div>
                    </div>

                    {{-- Expanded Districts List --}}
                    @if($isExpanded && $city->districts->isNotEmpty())
                        <div class="px-2.5 pb-2.5 pt-1 border-t border-gray-100 dark:border-gray-700/80 bg-white/70 dark:bg-gray-800/70 space-y-1">
                            <div class="text-[9px] font-bold text-gray-400 uppercase tracking-wider py-0.5 flex items-center gap-1">
                                <span>↳</span>
                                <span>Kecamatan di {{ $city->name }}</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 max-h-48 overflow-y-auto dropdown-scrollbar pr-1">
                                @foreach($city->districts as $district)
                                    @php $isDistrictActive = ($territory['type'] === 'district' && (int)$territory['id'] === (int)$district->id); @endphp
                                    <button type="button" wire:click="selectTerritory('district', {{ $district->id }}); open = false"
                                        class="flex items-center justify-between px-2 py-1.5 rounded-lg text-xs transition text-left cursor-pointer
                                        @if($isDistrictActive)
                                            bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-300 dark:border-emerald-800
                                        @else
                                            text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/70 font-medium
                                        @endif">
                                        <div class="flex items-center gap-1.5 truncate">
                                            <span class="w-1.5 h-1.5 rounded-full shrink-0 @if($isDistrictActive) bg-emerald-500 @else bg-gray-300 dark:bg-gray-600 @endif"></span>
                                            <span class="truncate text-[11px]">Kec. {{ $district->name }}</span>
                                        </div>
                                        @if($isDistrictActive)
                                            <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </button>
                                @endforeach
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
            <div class="mt-2.5 pt-2 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-[11px]">
                <span class="text-gray-500 dark:text-gray-400">Filter aktif: <strong class="text-gray-800 dark:text-gray-200">{{ $territory['label'] }}</strong></span>
                <button type="button" wire:click="selectTerritory('all'); open = false" class="text-primary-600 font-bold hover:underline cursor-pointer">
                    Reset Global
                </button>
            </div>
        @endif
    </div>
</div>
