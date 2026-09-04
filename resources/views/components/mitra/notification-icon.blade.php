@props([
    'route' => route('mitra.notifications.index'),
    'class' => 'w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 hover:bg-white/25 active:scale-95 transition-all flex items-center justify-center text-white shadow-xs cursor-pointer relative',
])

@php
    $unread = 0;
    try {
        if (auth()->check()) {
            $unread = auth()->user()->unreadNotifications()->count();
        }
    } catch (\Exception $e) {
        $unread = 0;
    }
@endphp

<a
    href="{{ $route }}"
    wire:navigate
    title="Notifikasi"
    aria-label="Notifikasi"
    {{ $attributes->merge(['class' => $class]) }}
>
    <div class="relative flex items-center justify-center">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        @if(!empty($unread) && $unread > 0)
            <span class="absolute -top-2 -right-2 min-w-[18px] h-[18px] px-1 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-sm ring-2 ring-white/30">
                {{ $unread > 99 ? '99+' : $unread }}
            </span>
        @endif
    </div>
</a>
