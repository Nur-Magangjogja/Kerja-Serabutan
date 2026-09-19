@props(['loading' => true])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/80 rounded-xl px-4 py-3 mb-4 shadow-2xs']) }}>
    <div class="flex flex-wrap items-center gap-3">
        {{ $slot }}

        @if($loading)
        <div wire:loading class="flex items-center gap-1.5 text-xs text-primary-600 dark:text-primary-400 ml-auto font-medium">
            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            <span>Memuat...</span>
        </div>
        @endif
    </div>
</div>
