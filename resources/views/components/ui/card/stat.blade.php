@props([
    'label'    => '',
    'value'    => '0',
    'sub'      => null,
    'color'    => 'blue',
    'trend'    => null,
    'trendUp'  => true,
    'icon'     => null,
])

@php
$colorMap = [
    'blue'    => ['bg' => 'bg-blue-50 dark:bg-blue-900/30',       'icon' => 'text-blue-600 dark:text-blue-400',       'border' => 'border-blue-100 dark:border-blue-900/40'],
    'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/30', 'icon' => 'text-emerald-600 dark:text-emerald-400', 'border' => 'border-emerald-100 dark:border-emerald-900/40'],
    'amber'   => ['bg' => 'bg-amber-50 dark:bg-amber-900/30',     'icon' => 'text-amber-600 dark:text-amber-400',     'border' => 'border-amber-100 dark:border-amber-900/40'],
    'indigo'  => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/30',   'icon' => 'text-indigo-600 dark:text-indigo-400',   'border' => 'border-indigo-100 dark:border-indigo-900/40'],
    'teal'    => ['bg' => 'bg-teal-50 dark:bg-teal-900/30',       'icon' => 'text-teal-600 dark:text-teal-400',       'border' => 'border-teal-100 dark:border-teal-900/40'],
    'rose'    => ['bg' => 'bg-rose-50 dark:bg-rose-900/30',       'icon' => 'text-rose-600 dark:text-rose-400',       'border' => 'border-rose-100 dark:border-rose-900/40'],
    'violet'  => ['bg' => 'bg-violet-50 dark:bg-violet-900/30',   'icon' => 'text-violet-600 dark:text-violet-400',   'border' => 'border-violet-100 dark:border-violet-900/40'],
    'primary' => ['bg' => 'bg-primary-50 dark:bg-primary-900/30', 'icon' => 'text-primary-600 dark:text-primary-400', 'border' => 'border-primary-100 dark:border-primary-900/40'],
];
$c = $colorMap[$color] ?? $colorMap['blue'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700/80 p-3.5 sm:p-4 flex items-center gap-3 shadow-2xs hover:shadow-md transition-all duration-200 min-w-0']) }}>
    @if(isset($icon))
    <div class="w-11 h-11 rounded-xl hidden sm:flex items-center justify-center shrink-0 {{ $c['bg'] }}">
        <span class="{{ $c['icon'] }}">{{ $icon }}</span>
    </div>
    @endif
    <div class="min-w-0 flex-1">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">{{ $label }}</p>
        <p class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mt-0.5 truncate">{{ $value }}</p>
        @if($sub || $trend)
        <div class="hidden sm:flex items-center gap-1 mt-0.5">
            @if($trend)
            <span class="text-xs font-medium {{ $trendUp ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                {{ $trendUp ? '↑' : '↓' }} {{ $trend }}
            </span>
            @elseif($sub)
            <span class="text-[11px] text-gray-400 dark:text-gray-500 truncate">{{ $sub }}</span>
            @endif
        </div>
        @endif
    </div>
</div>
