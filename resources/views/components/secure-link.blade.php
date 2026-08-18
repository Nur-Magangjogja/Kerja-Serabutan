@props([
    'href' => '#',
    'class' => '',
    'role' => 'link',
    'title' => null,
])

@php
    $targetUrl = (string) $href;
    $isExternal = str_starts_with($targetUrl, 'http://') || str_starts_with($targetUrl, 'https://');
    $encoded = base64_encode($targetUrl);
@endphp

<div
    role="{{ $role }}"
    tabindex="0"
    data-target="{{ $encoded }}"
    @if($title) title="{{ $title }}" @endif
    onclick="const u = atob(this.getAttribute('data-target')); if(u && u !== '#') { window.location.href = u; }"
    onkeydown="if(event.key==='Enter'||event.key===' '){ event.preventDefault(); const u = atob(this.getAttribute('data-target')); if(u && u !== '#') { window.location.href = u; } }"
    {{ $attributes->merge(['class' => 'cursor-pointer select-none ' . $class]) }}
>
    {{ $slot }}
</div>
