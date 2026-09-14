@props([
    'name'     => null,
    'show'     => false,
    'size'     => '2xl',
    'title'    => null,
    'subtitle' => null,
    'closable' => true,
])

@php
$sizeClasses = [
    'sm'   => 'sm:max-w-sm',
    'md'   => 'sm:max-w-md',
    'lg'   => 'sm:max-w-lg',
    'xl'   => 'sm:max-w-xl',
    '2xl'  => 'sm:max-w-2xl',
    '3xl'  => 'sm:max-w-3xl',
    '4xl'  => 'sm:max-w-4xl',
    '5xl'  => 'sm:max-w-5xl',
    'full' => 'sm:max-w-7xl',
];
$maxClass = $sizeClasses[$size] ?? ($sizeClasses['2xl']);
@endphp

@teleport('body')
<div
    x-data="{
        show: @js($show),
        focusables() {
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])';
            return [...$el.querySelectorAll(selector)].filter(el => ! el.hasAttribute('disabled'));
        },
        firstFocusable() { return this.focusables()[0]; },
        lastFocusable() { return this.focusables().slice(-1)[0]; },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable(); },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable(); },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1); },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) - 1; },
    }"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
            {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    @if(isset($name))
        x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
        x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    @endif
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50 flex items-center justify-center min-h-screen"
    style="display: {{ $show ? 'flex' : 'none' }};"
>
    {{-- Backdrop --}}
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all bg-gray-900/60 dark:bg-black/80 backdrop-blur-sm"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    {{-- Modal Content Card --}}
    <div
        x-show="show"
        class="relative w-full {{ $maxClass }} bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-700/80 overflow-hidden transform transition-all my-8 z-10"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        @if($title || isset($header))
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/80 flex items-center justify-between">
            @if(isset($header))
                {{ $header }}
            @else
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">{{ $title }}</h3>
                    @if($subtitle)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif

            @if($closable)
            <button
                type="button"
                x-on:click="show = false"
                class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors"
                aria-label="Tutup Modal"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            @endif
        </div>
        @endif

        <div class="px-6 py-4">
            {{ $slot }}
        </div>

        @if(isset($footer))
        <div class="px-6 py-3.5 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700/80 flex items-center justify-end gap-2.5">
            {{ $footer }}
        </div>
        @endif
    </div>
</div>
@endteleport
