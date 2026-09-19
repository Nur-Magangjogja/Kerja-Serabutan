@props([
    'title',
    'description' => null,
    'icon'        => null,
    'badge'       => null,
])

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3 min-w-0">
            @if($icon)
            <div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 flex items-center justify-center shrink-0">
                {!! $icon !!}
            </div>
            @endif
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white truncate">{{ $title }}</h1>
                    @if($badge)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800/50">
                        {{ $badge }}
                    </span>
                    @endif
                </div>
                @if($description)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $description }}</p>
                @endif
            </div>
        </div>

        @if(isset($actions))
        <div class="flex items-center gap-2 shrink-0">
            {{ $actions }}
        </div>
        @endif
    </div>
</div>
