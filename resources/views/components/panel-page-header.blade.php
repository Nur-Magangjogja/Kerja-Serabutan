{{--
    Panel Page Header Component (Usable by Admin & SuperAdmin)
    Usage: <x-panel-page-header title="Judul" description="Deskripsi">
               <x-slot:actions>
                   <button ...>Aksi</button>
               </x-slot:actions>
           </x-panel-page-header>
--}}
@props(['title', 'description' => null, 'icon' => null])

<div class="mb-6">
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3 min-w-0">
            @if($icon)
            <div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center flex-shrink-0">
                {!! $icon !!}
            </div>
            @endif
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white truncate">{{ $title }}</h1>
                @if($description)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $description }}</p>
                @endif
            </div>
        </div>

        @if(isset($actions))
        <div class="flex items-center gap-2 flex-shrink-0">
            {{ $actions }}
        </div>
        @endif
    </div>
</div>
