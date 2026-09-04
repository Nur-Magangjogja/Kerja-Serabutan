@props([
    'duration' => 850,
    'text' => 'Menyiapkan Aplikasi...',
    'tagline' => null,
])

@php
    $siteLogo = \App\Models\AppSetting::get('app_logo');
    $siteTagline = $tagline ?: \App\Models\AppSetting::get('app_tagline', 'Platform Layanan & Bantuan Serabutan');
@endphp

<!-- Reusable Splash Screen Loading Component -->
<div id="app-splash-screen"
     wire:ignore
     x-data="{ 
    showSplash: true,
    init() {
        setTimeout(() => {
            this.showSplash = false;
        }, {{ (int) $duration }});
    }
}" 
x-show="showSplash" 
x-transition:leave="transition-all duration-700 ease-in-out" 
x-transition:leave-start="opacity-100 scale-100" 
x-transition:leave-end="opacity-0 scale-105 pointer-events-none"
{{ $attributes->merge(['class' => 'fixed inset-0 z-50 flex flex-col items-center justify-center bg-gradient-to-br from-gray-900 via-primary-950 to-indigo-950 text-white p-6']) }}>
    
    <!-- Ambient Glow Circles -->
    <div class="absolute w-72 h-72 rounded-full bg-primary-500/20 blur-3xl animate-pulse -top-10 -left-10 pointer-events-none"></div>
    <div class="absolute w-80 h-80 rounded-full bg-indigo-500/20 blur-3xl animate-pulse -bottom-10 -right-10 pointer-events-none"></div>

    <div class="relative z-10 flex flex-col items-center text-center max-w-xs">
        <!-- App Logo / Emblem with Pulsing Aura -->
        <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-sky-400 via-primary-500 to-indigo-600 p-0.5 shadow-2xl shadow-primary-500/40 mb-6 flex items-center justify-center transform hover:scale-105 transition duration-300">
            <div class="w-full h-full bg-gray-900/60 backdrop-blur-xs rounded-[22px] flex items-center justify-center p-3.5">
                @if ($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                    <img src="{{ asset('storage/' . $siteLogo) }}" alt="Logo" class="w-full h-full object-contain filter drop-shadow-md" />
                @else
                    <svg class="w-10 h-10 text-sky-400 drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                @endif
            </div>
        </div>

        <!-- Brand Name Title -->
        <x-brand-title as="h1" size="3xl" theme="light" withDot="true" class="mb-2" />
        <p class="text-xs text-primary-200/80 mb-8 font-medium tracking-wide">
            {{ $siteTagline }}
        </p>

        <!-- Loading Shimmer Progress Bar -->
        <div class="w-48 h-1.5 bg-white/10 rounded-full overflow-hidden relative shadow-inner">
            <div class="absolute inset-0 bg-gradient-to-r from-sky-400 via-primary-400 to-indigo-400 rounded-full animate-progress-indeterminate"></div>
        </div>
        <span class="text-[11px] text-gray-400 mt-3 font-medium tracking-wider uppercase">{{ $text }}</span>
    </div>
</div>
