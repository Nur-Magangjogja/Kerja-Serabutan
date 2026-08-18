<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ \App\Models\AppSetting::get('app_name', config('app.name', 'SayaBantu')) }}</title>
    <meta name="description" content="{{ \App\Models\AppSetting::get('app_description', 'Solusi bantuan cepat, aman, dan terpercaya.') }}">
    @php
        $fav = \App\Models\AppSetting::get('app_favicon') ?: \App\Models\AppSetting::get('app_logo');
    @endphp
    @if($fav && \Illuminate\Support\Facades\Storage::disk('public')->exists($fav))
        <link rel="icon" href="{{ asset('storage/' . $fav) }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|outfit:400,500,600,700,800|poppins:400,500,600,700,800|lexend:400,500,600,700,800|montserrat:400,500,600,700,800|inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="font-sans antialiased bg-gray-100 min-h-screen">
    <!-- Header styled like customer 'Bantuan' page -->
    <div class="max-w-md mx-auto">
        <div class="px-5 pt-5 pb-8 relative overflow-hidden header-pattern" style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
            <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between text-white mb-3">
                    <div class="flex items-center">
                        <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    </div>

                    <div class="text-center flex-1">
                        <x-brand-title as="h1" size="xl" theme="light" withDot="true" />
                        <p class="text-xs text-white/90 font-medium mt-0.5">{{ \App\Models\AppSetting::get('app_tagline', 'Platform Bantuan Sosial') }}</p>
                    </div>

                    <div class="w-8"></div>
                </div>
            </div>

            <!-- Curved separator into content -->
            <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 72" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,32 C360,72 1080,0 1440,40 L1440,72 L0,72 Z" fill="#ffffff"></path>
            </svg>
        </div>

        <div class="bg-white rounded-t-3xl -mt-6 px-5 pt-6 pb-6 min-h-[40vh]">
            {{ $slot }}
        </div>
    </div>
    @livewireScripts
</body>

</html>