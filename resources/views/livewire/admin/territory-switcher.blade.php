<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <button type="button" @click="open = !open"
        class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl text-xs font-bold shadow-2xs cursor-pointer active:scale-95 transition-all max-w-[120px] xs:max-w-[150px] sm:max-w-[200px] shrink-0
        @if($activeDistrictFilter !== 'all')
            bg-emerald-500/15 dark:bg-emerald-500/20 text-emerald-700 dark:text-white border border-emerald-500/30 dark:border-emerald-500/30 ring-2 ring-emerald-500/20
        @else
            bg-primary-500/15 dark:bg-primary-500/20 text-primary-700 dark:text-white border border-primary-500/30 dark:border-primary-500/30 hover:bg-primary-500/20 dark:hover:bg-primary-500/30
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
        class="fixed inset-x-3 top-16 sm:absolute sm:inset-auto sm:right-0 sm:top-full sm:mt-2 w-auto sm:w-80 max-h-[85vh] sm:max-h-[30rem] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700 p-2.5 z-[60] flex flex-col overscroll-contain pr-1.5">
        
        <div class="px-2.5 py-1.5 border-b border-gray-100 dark:border-gray-700 mb-1 flex items-center justify-between">
            <span class="text-[10px] font-bold text-gray-400 dark:text-gray-300 uppercase tracking-wider">Wilayah Kecamatan Pantauan</span>
            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-200">Tersimpan</span>
        </div>

        <div class="space-y-1">
            {{-- Opsi: Semua Wilayah --}}
            <button type="button" wire:click="selectDistrict('all'); open = false"
                class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition text-left cursor-pointer
                @if($activeDistrictFilter === 'all')
                    bg-primary-50 dark:bg-primary-950/70 text-primary-700 dark:text-white border border-primary-200 dark:border-primary-800
                @else
                    text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700/60
                @endif">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full @if($activeDistrictFilter === 'all') bg-primary-600 @else bg-gray-300 dark:bg-gray-600 @endif"></span>
                    Semua Wilayah Wewenang ({{ $managedDistricts->count() }} Kec.)
                </span>
                @if($activeDistrictFilter === 'all')
                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </button>

            @if($managedDistricts->isNotEmpty())
                <div class="pt-2 pb-1 px-2.5 text-[10px] font-bold text-gray-400 dark:text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    Daftar Kecamatan Wewenang
                </div>
                @foreach($managedDistricts as $md)
                    @php $isDistSelected = ((string)$activeDistrictFilter === (string)$md->id); @endphp
                    <button type="button" wire:click="selectDistrict('{{ $md->id }}'); open = false"
                        class="w-full flex items-center justify-between px-3 py-1.5 rounded-xl text-xs transition text-left cursor-pointer
                        @if($isDistSelected)
                            bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-white font-bold border border-emerald-200 dark:border-emerald-800
                        @else
                            text-gray-700 dark:text-white font-semibold hover:bg-gray-50 dark:hover:bg-gray-700/60
                        @endif">
                        <div class="flex items-center gap-2 truncate">
                            <span class="w-2 h-2 rounded-full flex-shrink-0 @if($isDistSelected) bg-emerald-500 @else bg-gray-300 dark:bg-gray-600 @endif"></span>
                            <span class="truncate">Kec. {{ $md->name }}</span>
                            @if($md->city)
                                <span class="text-[10px] text-gray-400 dark:text-gray-300 font-normal">({{ $md->city->name }})</span>
                            @endif
                        </div>
                        @if($isDistSelected)
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </button>
                @endforeach
            @endif
        </div>
    </div>
</div>

