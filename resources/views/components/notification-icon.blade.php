@props([
    'route' => '#',
    'class' => 'p-2 hover:bg-white/20 rounded-lg transition cursor-pointer relative',
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
    $targetUrl = (string) $route;
    $encoded = base64_encode($targetUrl);
@endphp

<div
    role="link"
    tabindex="0"
    data-target="{{ $encoded }}"
    title="Notifikasi"
    aria-label="Notifikasi"
    onclick="const u = atob(this.getAttribute('data-target')); if(u && u !== '#') { window.location.href = u; }"
    onkeydown="if(event.key==='Enter'||event.key===' '){ event.preventDefault(); const u = atob(this.getAttribute('data-target')); if(u && u !== '#') { window.location.href = u; } }"
    {{ $attributes->merge(['class' => $class]) }}
>
    <div class="relative">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        @if(!empty($unread) && $unread > 0)
            <span class="absolute -top-1.5 -right-1.5 inline-flex items-center justify-center text-[10px] font-semibold bg-red-500 text-white rounded-full px-1.5 py-0.5 shadow">{{ $unread > 99 ? '99+' : $unread }}</span>
        @endif
    </div>
</div>
