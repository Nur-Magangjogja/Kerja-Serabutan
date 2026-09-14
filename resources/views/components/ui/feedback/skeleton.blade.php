@props([
    'type'  => 'card', // card, stat, list, table, text
    'count' => 1,
    'lines' => 3,
])

<div {{ $attributes->merge(['class' => 'animate-pulse space-y-3']) }}>
    @for($i = 0; $i < $count; $i++)
        @if($type === 'stat')
            <div class="bg-gray-100 dark:bg-gray-800/60 rounded-xl p-4 flex items-center gap-3 border border-gray-100 dark:border-gray-700/50">
                <div class="w-11 h-11 bg-gray-200 dark:bg-gray-700/60 rounded-xl shrink-0"></div>
                <div class="space-y-2 flex-1">
                    <div class="h-3 bg-gray-200 dark:bg-gray-700/60 rounded w-1/3"></div>
                    <div class="h-5 bg-gray-200 dark:bg-gray-700/60 rounded w-1/2"></div>
                </div>
            </div>
        @elseif($type === 'list')
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700/50 flex items-center justify-between gap-4">
                <div class="space-y-2 flex-1">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700/60 rounded w-2/5"></div>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700/60 rounded w-3/5"></div>
                </div>
                <div class="h-8 w-20 bg-gray-200 dark:bg-gray-700/60 rounded-lg shrink-0"></div>
            </div>
        @elseif($type === 'table')
            <div class="h-10 bg-gray-100 dark:bg-gray-800/40 rounded-lg w-full"></div>
        @elseif($type === 'text')
            <div class="space-y-2">
                @for($l = 0; $l < $lines; $l++)
                    <div class="h-3 bg-gray-200 dark:bg-gray-700/60 rounded {{ $l === $lines - 1 ? 'w-2/3' : 'w-full' }}"></div>
                @endfor
            </div>
        @else {{-- card --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/50 space-y-3">
                <div class="h-4 bg-gray-200 dark:bg-gray-700/60 rounded w-1/3"></div>
                <div class="h-3 bg-gray-200 dark:bg-gray-700/60 rounded w-full"></div>
                <div class="h-3 bg-gray-200 dark:bg-gray-700/60 rounded w-4/5"></div>
                <div class="pt-2 flex justify-between items-center">
                    <div class="h-5 bg-gray-200 dark:bg-gray-700/60 rounded w-1/4"></div>
                    <div class="h-8 bg-gray-200 dark:bg-gray-700/60 rounded-lg w-24"></div>
                </div>
            </div>
        @endif
    @endfor
</div>
