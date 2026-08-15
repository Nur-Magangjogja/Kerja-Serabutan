{{--
    Super Admin Stat Card Component
    Usage: <x-superadmin-stat-card
               label="Total Pengguna"
               value="1,234"
               sub="Semua pengguna"
               color="blue"
               icon="..." />
    Colors: blue, emerald, amber, indigo, teal, rose, violet
--}}
@props([
    'label' => '',
    'value' => '0',
    'sub' => null,
    'color' => 'blue',
    'trend' => null,
    'trendUp' => true,
])

@php
$colorMap = [
    'blue'    => ['bg' => 'bg-blue-50 dark:bg-blue-900/30',    'icon' => 'text-blue-600 dark:text-blue-400'],
    'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/30', 'icon' => 'text-emerald-600 dark:text-emerald-400'],
    'amber'   => ['bg' => 'bg-amber-50 dark:bg-amber-900/30',   'icon' => 'text-amber-600 dark:text-amber-400'],
    'indigo'  => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/30', 'icon' => 'text-indigo-600 dark:text-indigo-400'],
    'teal'    => ['bg' => 'bg-teal-50 dark:bg-teal-900/30',    'icon' => 'text-teal-600 dark:text-teal-400'],
    'rose'    => ['bg' => 'bg-rose-50 dark:bg-rose-900/30',    'icon' => 'text-rose-600 dark:text-rose-400'],
    'violet'  => ['bg' => 'bg-violet-50 dark:bg-violet-900/30', 'icon' => 'text-violet-600 dark:text-violet-400'],
    'primary' => ['bg' => 'bg-primary-50 dark:bg-primary-900/30','icon' => 'text-primary-600 dark:text-primary-400'],
];
$c = $colorMap[$color] ?? $colorMap['blue'];
@endphp

<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 p-4 flex items-center gap-3 shadow-sm hover:shadow-md transition-shadow duration-200 min-w-0">
    @if(isset($icon))
    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 {{ $c['bg'] }}">
        <span class="{{ $c['icon'] }}">{{ $icon }}</span>
    </div>
    @endif
    <div class="min-w-0 flex-1">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">{{ $label }}</p>
        <p class="text-xl font-bold text-gray-900 dark:text-white mt-0.5 truncate">{{ $value }}</p>
        @if($sub || $trend)
        <div class="flex items-center gap-1 mt-0.5">
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
