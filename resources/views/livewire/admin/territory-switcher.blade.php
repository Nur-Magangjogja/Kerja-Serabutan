<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <button type="button" @click="open = !open"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition shadow-xs cursor-pointer
        @if($activeFilter !== 'all')
            bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 ring-2 ring-emerald-500/20
        @else
            bg-primary-50 dark:bg-primary-950/60 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800 hover:bg-primary-100 dark:hover:bg-primary-900/50
        @endif"
        title="Pilih Wilayah Pantauan">
        <svg class="w-3.5 h-3.5 @if($activeFilter !== 'all') text-emerald-600 dark:text-emerald-400 @else text-primary-600 dark:text-primary-400 @endif shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
        </svg>
        <span class="max-w-[160px] truncate">{{ $activeLabel }}</span>
        <svg class="w-3 h-3 text-current transition-transform duration-200 shrink-0" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-cloak x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="absolute right-0 sm:left-0 sm:right-auto mt-2 w-64 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 p-2 z-[60] overflow-hidden">
        
        <div class="px-2.5 py-1.5 border-b border-gray-100 dark:border-gray-700 mb-1 flex items-center justify-between">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Wilayah Pantauan Aktif</span>
            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">Tersimpan</span>
        </div>

        <div class="space-y-1">
            {{-- Opsi: Semua Wilayah --}}
            <button type="button" wire:click="selectCity('all'); open = false"
                class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition text-left cursor-pointer
                @if($activeFilter === 'all')
                    bg-primary-50 dark:bg-primary-950/70 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800
                @else
                    text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/60
                @endif">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full @if($activeFilter === 'all') bg-primary-600 @else bg-gray-300 dark:bg-gray-600 @endif"></span>
                    Semua Wilayah Saya ({{ $managedCities->count() }} Kota)
                </span>
                @if($activeFilter === 'all')
                    <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </button>

            {{-- Opsi: Masing-masing Kota --}}
            @foreach($managedCities as $mc)
                @php $isSelected = ((string)$activeFilter === (string)$mc->id); @endphp
                <button type="button" wire:click="selectCity('{{ $mc->id }}'); open = false"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs transition text-left cursor-pointer
                    @if($isSelected)
                        bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 font-bold border border-emerald-200 dark:border-emerald-800
                    @else
                        text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-50 dark:hover:bg-gray-700/60
                    @endif">
                    <span class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full @if($isSelected) bg-emerald-500 @else bg-gray-300 dark:bg-gray-600 @endif"></span>
                        {{ $mc->name }}
                    </span>
                    @if($isSelected)
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </button>
            @endforeach
        </div>
    </div>
</div>
