@props([
    'route' => null,
    'class' => 'w-10 h-10 rounded-xl bg-white/15 backdrop-blur-md border border-white/20 hover:bg-white/25 active:scale-95 transition-all flex items-center justify-center text-white shadow-xs cursor-pointer relative',
])

@php
    $isMitra = auth()->check() && auth()->user()->role === 'mitra';
    
    if (!$route) {
        $route = $isMitra ? route('mitra.chat') : route('customer.chat');
    }
@endphp

@if($isMitra)
    <x-mitra.chat-icon :route="$route" :class="$class" {{ $attributes }} />
@else
    <x-customer.chat-icon :route="$route" :class="$class" {{ $attributes }} />
@endif
