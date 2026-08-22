<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ \App\Models\AppSetting::get('app_name', config('app.name', 'SayaBantu')) }}</title>
    <meta name="description" content="{{ \App\Models\AppSetting::get('app_description', 'Solusi bantuan cepat, aman, dan terpercaya.') }}">
    @php
        $fav = \App\Models\AppSetting::get('app_favicon') ?: \App\Models\AppSetting::get('app_logo');
        $siteLogo = \App\Models\AppSetting::get('app_logo');
        $siteName = \App\Models\AppSetting::get('app_name', 'SayaBantu');
        $siteTagline = \App\Models\AppSetting::get('app_tagline', 'Platform Bantuan Sosial & Layanan');
    @endphp
    @if($fav && \Illuminate\Support\Facades\Storage::disk('public')->exists($fav))
        <link rel="icon" href="{{ asset('storage/' . $fav) }}">
    @endif

    <!-- Instant Theme Script -->
    <script>
        (function() {
            var mode = localStorage.getItem('theme') || 'system';
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (mode === 'dark' || (mode === 'system' && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|outfit:400,500,600,700,800|inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased bg-gray-50 dark:bg-gray-950 min-h-screen text-gray-800 dark:text-gray-100 flex flex-col justify-start">
    <div class="w-full max-w-lg mx-auto flex flex-col min-h-screen">
        <!-- Modern Clean Solid Header -->
        <header class="relative bg-primary-600 dark:bg-gray-900 border-b border-primary-700/50 dark:border-gray-800 text-white px-6 pt-7 pb-8 sm:pb-9 shadow-sm">
            <div class="relative z-10 max-w-md mx-auto">
                <div class="flex items-center justify-between gap-3 mb-2.5">
                    <!-- Back Button -->
                    <button onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ route('login') }}'; }" 
                        aria-label="Kembali" 
                        class="p-2.5 rounded-xl bg-white/10 hover:bg-white/20 active:bg-white/30 text-white border border-white/15 transition-all shadow-xs flex-shrink-0 cursor-pointer group">
                        <svg class="w-5 h-5 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- Brand Centerpiece -->
                    <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5 group min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-white/15 border border-white/25 flex items-center justify-center p-1.5 shadow-inner group-hover:scale-105 transition-transform flex-shrink-0">
                            @if ($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                                <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="w-full h-full object-contain" />
                            @else
                                <svg class="w-5 h-5 text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            @endif
                        </div>
                        <x-brand-title as="h1" size="xl" theme="light" withDot="true" class="truncate" />
                    </a>

                    <!-- Right Action placeholder / Login Link -->
                    <a href="{{ route('login') }}" wire:navigate 
                        class="text-xs font-semibold text-white/95 hover:text-white bg-white/10 hover:bg-white/20 px-3.5 py-1.5 rounded-xl border border-white/15 transition shadow-xs flex-shrink-0">
                        Masuk
                    </a>
                </div>

                <!-- Tagline Subtitle -->
                <p class="text-center text-xs text-blue-100 dark:text-gray-400 font-medium tracking-wide">
                    {{ $siteTagline }}
                </p>
            </div>
        </header>

        <!-- Main Content Card with Distinct Separation & Generous Spacing -->
        <main class="flex-1 px-4 sm:px-6 pt-5 sm:pt-6 pb-12 sm:pb-16 relative z-20">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700/80 p-6 sm:p-8 md:p-9 min-h-[50vh] transition-all">
                {{ $slot }}
            </div>
        </main>
    </div>
    @livewireScripts
</body>

</html>