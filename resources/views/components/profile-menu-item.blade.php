@props([
    'href' => null,
    'title' => '',
    'subtitle' => null,
    'iconBg' => '',
    'iconStyle' => '',
    'showChevron' => true,
    'onClick' => null,
    'danger' => false,
])

@php
    $hasHref = !empty($href) && $href !== '#';
    $baseClass = "p-3.5 flex items-center gap-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition cursor-pointer select-none";
@endphp

@if($hasHref)
    <a
        href="{{ $href }}"
        wire:navigate
        class="{{ $baseClass }}"
    >
        <!-- Icon Container -->
        <div 
            class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 shadow-xs {{ $iconBg }}"
            @if($iconStyle) style="{{ $iconStyle }}" @endif
        >
            {{ $slot }}
        </div>

        <!-- Title & Subtitle -->
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-semibold {{ $danger ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }} truncate">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-xs {{ $danger ? 'text-red-400 dark:text-red-400/80' : 'text-gray-500 dark:text-gray-400' }} truncate">{{ $subtitle }}</p>
            @endif
        </div>

        <!-- Chevron Arrow -->
        @if($showChevron)
            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        @endif
    </a>
@else
    <div
        role="button"
        tabindex="0"
        @if($onClick)
            onclick="{{ $onClick }}"
            onkeydown="if(event.key==='Enter'||event.key===' '){ event.preventDefault(); {{ $onClick }} }"
        @endif
        class="{{ $baseClass }}"
    >
        <!-- Icon Container -->
        <div 
            class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 shadow-xs {{ $iconBg }}"
            @if($iconStyle) style="{{ $iconStyle }}" @endif
        >
            {{ $slot }}
        </div>

        <!-- Title & Subtitle -->
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-semibold {{ $danger ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }} truncate">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-xs {{ $danger ? 'text-red-400 dark:text-red-400/80' : 'text-gray-500 dark:text-gray-400' }} truncate">{{ $subtitle }}</p>
            @endif
        </div>

        <!-- Chevron Arrow -->
        @if($showChevron)
            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        @endif
    </div>
@endif
