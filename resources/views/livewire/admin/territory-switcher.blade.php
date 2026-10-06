<div class="relative" x-data="{ open: false, search: '' }" @click.away="open = false">
    {{-- Trigger Button --}}
    <button type="button" @click="open = !open"
        class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl text-xs font-bold shadow-2xs cursor-pointer active:scale-95 transition-all max-w-[130px] sm:max-w-[220px] md:max-w-[260px] shrink-0
        @if($activeDistrictFilter !== 'all')
            bg-emerald-500/15 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30 dark:border-emerald-500/30 ring-2 ring-emerald-500/20
        @else
            bg-primary-500/15 dark:bg-primary-500/20 text-primary-700 dark:text-primary-300 border border-primary-500/30 dark:border-primary-500/30 hover:bg-primary-500/20 dark:hover:bg-primary-500/30
        @endif"
        title="{{ $activeLabel }} (Pilih Wilayah Kecamatan Pantauan)">
        <svg class="w-3.5 h-3.5 @if($activeDistrictFilter !== 'all') text-emerald-600 dark:text-emerald-400 @else text-primary-600 dark:text-primary-400 @endif shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
        </svg>
        <span class="truncate leading-tight text-[11px] sm:text-xs">{{ $activeLabel }}</span>
        <svg class="w-3 h-3 text-current transition-transform duration-200 shrink-0" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Mobile Backdrop Overlay --}}
    <div x-cloak x-show="open" 
        @click="open = false" 
        x-transition:enter="transition-opacity ease-linear duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-950/40 backdrop-blur-xs z-40 sm:hidden">
    </div>

    {{-- Dropdown Popover (Responsive Fixed on Mobile, Absolute on Desktop) --}}
    <div x-cloak x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="fixed sm:absolute inset-x-3 sm:inset-x-auto top-16 sm:top-full sm:right-0 sm:mt-2 w-auto sm:w-[26rem] md:w-[28rem] max-h-[calc(100vh-5rem)] sm:max-h-[36rem] bg-white dark:bg-gray-800 rounded-2xl sm:rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700 p-3 sm:p-4 z-50 flex flex-col overflow-hidden">
        
        {{-- Popover Header --}}
        <div class="px-1 pb-2.5 mb-2 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                <span class="text-xs font-bold text-gray-800 dark:text-white truncate">Wilayah Kecamatan Pantauan</span>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">
                    {{ $managedDistricts->count() }} Kec.
                </span>
                <button type="button" @click="open = false" class="sm:hidden p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer" title="Tutup">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Instant Alpine Search (Visible if more than 4 districts) --}}
        @if($managedDistricts->count() > 4)
            <div class="relative mb-2.5 shrink-0">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text"
                    x-model="search"
                    placeholder="Cari kecamatan wewenang..."
                    class="w-full pl-8.5 pr-8 py-2 text-xs bg-gray-50 dark:bg-gray-700/60 border border-gray-200 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
                <button type="button" x-show="search.length > 0" @click="search = ''" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        {{-- Scrollable Territory Content (True Vertical Overflow Handling) --}}
        <div class="flex-1 min-h-0 overflow-y-auto pr-1 space-y-2.5 custom-scrollbar overscroll-contain">
            
            {{-- Opsi: Semua Wilayah --}}
            <button type="button" wire:click="selectDistrict('all'); open = false"
                x-show="!search || 'semua wilayah'.includes(search.toLowerCase())"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition text-left cursor-pointer
                @if($activeDistrictFilter === 'all')
                    bg-primary-50 dark:bg-primary-950/70 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800 shadow-2xs
                @else
                    text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/60 border border-gray-100 dark:border-gray-700/60
                @endif">
                <div class="flex items-center gap-2 truncate min-w-0">
                    <div class="w-6 h-6 rounded-lg @if($activeDistrictFilter === 'all') bg-primary-600 text-white @else bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 @endif flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="truncate min-w-0">
                        <span class="block truncate">Semua Wilayah Wewenang</span>
                        <span class="block text-[10px] font-normal text-gray-400 dark:text-gray-400">{{ $managedDistricts->count() }} Kecamatan Aktif</span>
                    </div>
                </div>
                @if($activeDistrictFilter === 'all')
                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400 shrink-0 ml-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </button>

            @if($managedDistricts->isNotEmpty())
                <div>
                    <div class="pt-1 pb-1.5 px-1 text-[10px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3 h-3 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            <span>Daftar Kecamatan Wewenang</span>
                        </span>
                        <span class="text-[9px] font-medium text-gray-400 hidden sm:inline">Pilih salah satu</span>
                    </div>

                    {{-- Responsive Grid: 1 column on Mobile, 2 columns on Desktop --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                        @foreach($managedDistricts as $md)
                            @php 
                                $isDistSelected = ((string)$activeDistrictFilter === (string)$md->id);
                                $distSearchKeywords = strtolower($md->name . ' ' . ($md->city->name ?? ''));
                            @endphp
                            <button type="button" wire:click="selectDistrict('{{ $md->id }}'); open = false"
                                x-show="!search || '{{ addslashes($distSearchKeywords) }}'.includes(search.toLowerCase())"
                                class="flex items-center justify-between px-2.5 py-2 rounded-xl text-xs transition text-left cursor-pointer border min-w-0
                                @if($isDistSelected)
                                    bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 font-bold border-emerald-300 dark:border-emerald-800 shadow-2xs
                                @else
                                    border-gray-100 dark:border-gray-700/60 text-gray-700 dark:text-gray-200 font-medium hover:bg-gray-50 dark:hover:bg-gray-700/60
                                @endif">
                                <div class="flex items-center gap-1.5 truncate min-w-0">
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0 @if($isDistSelected) bg-emerald-500 @else bg-gray-300 dark:bg-gray-600 @endif"></span>
                                    <span class="truncate text-xs">Kec. {{ $md->name }}</span>
                                </div>
                                @if($isDistSelected)
                                    <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400 shrink-0 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Popover Footer --}}
        @if($activeDistrictFilter !== 'all')
            <div class="mt-2.5 pt-2 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-[11px] shrink-0">
                <span class="text-gray-500 dark:text-gray-400 truncate mr-2">Filter aktif: <strong class="text-gray-800 dark:text-white">{{ $activeLabel }}</strong></span>
                <button type="button" wire:click="selectDistrict('all'); open = false" class="text-primary-600 dark:text-primary-400 font-bold hover:underline cursor-pointer shrink-0">
                    Reset Semua
                </button>
            </div>
        @endif
    </div>
</div>

