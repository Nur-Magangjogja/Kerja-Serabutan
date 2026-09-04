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
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800,900|space-grotesk:400,500,600,700|dm-sans:400,500,700,800,900|syne:400,500,600,700,800|nunito:400,600,700,800,900|playfair-display:400,500,600,700,800,900|outfit:400,500,600,700,800|poppins:400,500,600,700,800|lexend:400,500,600,700,800|montserrat:400,500,600,700,800|inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased bg-gray-50 dark:bg-gray-950 min-h-screen text-gray-800 dark:text-gray-100 flex flex-col justify-start">
    <div class="w-full max-w-lg mx-auto flex flex-col min-h-screen">
        <!-- Header Section -->
        <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between text-white">
                    <!-- Back Button -->
                    @if(request()->routeIs('registration.success'))
                        <a href="{{ route('login') }}" wire:navigate aria-label="Kembali ke Login" 
                            class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0 text-white flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @else
                        <button onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ route('login') }}'; }" 
                            aria-label="Kembali" 
                            class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0 text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    @endif

                    <!-- Brand Centerpiece -->
                    <div class="text-center flex-1 min-w-0 px-2">
                        <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center justify-center gap-2 group">
                            @if ($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                                <div class="w-7 h-7 rounded-lg bg-white/15 p-1 flex items-center justify-center shadow-inner">
                                    <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="w-full h-full object-contain" />
                                </div>
                            @endif
                            <x-brand-title as="h1" size="xl" theme="light" withDot="true" class="truncate font-bold text-white" />
                        </a>
                        <p class="text-xs text-white/90 truncate mt-0.5">
                            {{ $siteTagline }}
                        </p>
                    </div>

                    <!-- Right spacer for balanced centering -->
                    <div class="w-9 flex-shrink-0"></div>
                </div>
            </div>
        </div>

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