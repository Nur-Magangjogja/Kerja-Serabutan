{{--
    Panel Filter Toolbar Component (Usable by Admin & SuperAdmin)
    Renders a compact inline filter bar above data tables.

    Slot usage:
    <x-panel-filter-toolbar>
        <input wire:model.debounce.400ms="search" ... />
        <select wire:model="filter" ...>...</select>
    </x-panel-filter-toolbar>
--}}
@props(['loading' => true])

<div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3 mb-4 shadow-sm">
    <div class="flex flex-wrap items-center gap-3">
        {{ $slot }}

        @if($loading)
        <div wire:loading class="flex items-center gap-1.5 text-xs text-primary-600 dark:text-primary-400 ml-auto">
            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            Memuat...
        </div>
        @endif
    </div>
</div>
