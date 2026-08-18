@props([
    'href' => '#',
    'value' => '0',
    'label' => '',
    'colorClass' => 'text-primary-600 dark:text-primary-400',
    'rounded' => '',
    'isRating' => false,
])

@php
    $encoded = base64_encode((string) $href);
@endphp

<div
    role="link"
    tabindex="0"
    data-target="{{ $encoded }}"
    onclick="const u = atob(this.getAttribute('data-target')); if(u && u !== '#') { window.location.href = u; }"
    onkeydown="if(event.key==='Enter'||event.key===' '){ event.preventDefault(); const u = atob(this.getAttribute('data-target')); if(u && u !== '#') { window.location.href = u; } }"
    class="p-3 text-center group cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200 block select-none {{ $rounded }}"
>
    @if($isRating)
        <div class="flex items-center justify-center gap-1">
            <span class="text-xl font-bold {{ $colorClass }} transition-transform duration-300 group-hover:scale-105">{{ $value }}</span>
            <svg class="w-4 h-4 text-yellow-400 transition-transform duration-300 group-hover:rotate-12" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
        </div>
    @else
        <div class="text-xl font-bold {{ $colorClass }} transition-transform duration-300 group-hover:scale-105">{{ $value }}</div>
    @endif
    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium">{{ $label }}</p>
</div>
