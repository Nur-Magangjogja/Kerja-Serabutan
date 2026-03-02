<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>sayabantu - Platform Bantuan Sosial</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .header-pattern {
            position: relative;
            overflow: hidden;
        }
        .header-pattern::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        .header-pattern::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
    </style>
</head>

<body class="font-sans antialiased bg-gradient-to-br from-blue-50 via-white to-purple-50 overflow-hidden">
    <div class="max-w-sm mx-auto h-screen flex flex-col overflow-hidden">

        @php
            $showCards = ! request()->cookie('shown_bantuan');
            $helps = $showCards ? \App\Models\Help::latest()->get() : collect();
        @endphp

        @if($showCards)

            <!-- Header (matching mitra/customer help pages) -->
            <div class="px-5 pt-5 pb-8 relative overflow-hidden header-pattern" style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
                <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16"></div>

                <div class="relative z-10">
                    <div class="flex items-center justify-between text-white mb-3">
                        <div class="text-center flex-1">
                            <h1 class="text-lg font-bold">Bantuan</h1>
                            <p class="text-xs text-white/90 mt-0.5">Temukan bantuan yang tersedia</p>
                        </div>
                    </div>
                </div>

                <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 72" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0,32 C360,72 1080,0 1440,40 L1440,72 L0,72 Z" fill="#ffffff"></path>
                </svg>
            </div>

            <!-- Home Banner -->
            @php
                $homeBanners = json_decode((string) \App\Models\AppSetting::get('banner_home', '[]'), true) ?: [];
            @endphp
            @if(!empty($homeBanners) && count($homeBanners))
                <div class="px-5 mt-4">
                    <div class="rounded-xl overflow-hidden shadow-md">
                        <div class="relative h-36 overflow-hidden">
                            <div class="flex h-full will-change-transform home-banner-slides" style="transition: transform 700ms cubic-bezier(.2,.9,.2,1);">
                                @foreach($homeBanners as $b)
                                    <div class="flex-shrink-0 w-full h-full">
                                        <img src="{{ asset('storage/' . $b) }}" alt="Banner" class="w-full h-full object-cover" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="px-5 mt-4">
                    <div id="promo-banner" class="rounded-xl overflow-hidden shadow-md">
                        <div class="relative h-36 overflow-hidden" style="background: linear-gradient(to right, #0098e7, #0077cc);">
                            <div id="promo-track" class="flex h-full transition-transform duration-700 ease-in-out"></div>
                        </div>
                    </div>
                    <div id="promo-dots" class="flex justify-center mt-3 gap-2 px-5">
                        <button data-dot="0" class="w-2 h-2 rounded-full transition-all" style="background: #0098e7;"></button>
                        <button data-dot="1" class="w-2 h-2 rounded-full bg-gray-300 transition-all"></button>
                        <button data-dot="2" class="w-2 h-2 rounded-full bg-gray-300 transition-all"></button>
                    </div>
                </div>
            @endif

            <br>

            <!-- Help Cards Section -->
            <div class="flex-1 px-5 overflow-y-auto hide-scrollbar">
                <div class="mb-4">
                    <h2 class="text-base font-bold text-gray-800 mb-1">Bantuan Terbaru</h2>
                    <p class="text-xs text-gray-500">Login untuk melihat detail dan memberikan bantuan</p>
                </div>

                <div class="space-y-4 pb-6">
                    @forelse($helps as $help)
                        <a href="{{ route('login') }}" class="block bg-white rounded-xl p-3.5 shadow-sm hover:shadow-md transition-all border border-gray-100">
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                                    @if($help->photo)
                                        <img src="{{ asset('storage/' . $help->photo) }}" alt="{{ $help->title }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-lg">
                                            {{ ['🩺', '🏠', '💡', '🔧', '🎯'][($loop->index) % 5] }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <h3 class="font-semibold text-sm text-gray-900 line-clamp-1">{{ $help->title }}</h3>
                                        <span class="text-xs font-bold whitespace-nowrap" style="color: #0098e7;">Rp {{ number_format($help->amount, 0, ',', '.') }}</span>
                                    </div>

                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xs text-gray-400">{{ optional($help->created_at)->diffForHumans() }}</span>
                                    </div>

                                    <p class="text-xs text-gray-600 line-clamp-2 mb-2">{{ Str::limit($help->description, 100) }}</p>

                                    @if($help->scheduled_at)
                                        <div class="text-xs text-gray-500 mb-2">📅 {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }}</div>
                                    @endif

                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500">📍 {{ $help->city->name ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500">Tidak ada bantuan tersedia saat ini</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Bottom CTA -->
            <div class="px-6 py-6 bg-white border-t border-gray-100">
                <a href="{{ route('login') }}" class="block w-full bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-bold py-4 rounded-2xl text-center transition-all duration-200 shadow-lg shadow-primary-500/30">
                    Mulai Berbagi Kebaikan
                </a>
                <p class="text-xs text-center text-gray-500 mt-3">
                    Belum punya akun? 
                    <a href="{{ route('register') }}" class="text-primary-600 font-semibold hover:text-primary-700">Daftar sekarang</a>
                </p>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function(){
                    document.cookie = "shown_bantuan=1; path=/; max-age=" + (60*60*24*30);
                });
            </script>

        @else
            <!-- Hero Section -->
            <div class="flex-1 flex flex-col items-center justify-center px-6">
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-primary-500 to-primary-600 rounded-3xl shadow-xl shadow-primary-500/30 mb-6">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-3">sayabantu</h1>
                    <p class="text-gray-600 max-w-xs mx-auto">Platform berbagi bantuan sosial untuk mereka yang membutuhkan</p>
                </div>

                <!-- Features -->
                <div class="w-full max-w-xs space-y-3 mb-8">
                    <div class="flex items-center gap-3 bg-white/60 backdrop-blur-sm p-3 rounded-xl">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-sm text-gray-700">Transparan dan Terpercaya</span>
                    </div>
                    <div class="flex items-center gap-3 bg-white/60 backdrop-blur-sm p-3 rounded-xl">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <span class="text-sm text-gray-700">Aman dan Mudah</span>
                    </div>
                    <div class="flex items-center gap-3 bg-white/60 backdrop-blur-sm p-3 rounded-xl">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span class="text-sm text-gray-700">Komunitas Peduli</span>
                    </div>
                </div>
            </div>

            <!-- Buttons Section -->
            <div class="px-6 pb-8 space-y-3">
                <!-- Log In Button -->
                <a href="{{ route('login') }}"
                    class="block w-full bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-bold py-4 rounded-2xl text-center transition-all duration-200 shadow-lg shadow-primary-500/30">
                    Log In
                </a>

                <!-- Sign Up Button -->
                <a href="{{ route('register') }}"
                    class="block w-full bg-white hover:bg-gray-50 text-gray-800 font-bold py-4 rounded-2xl text-center transition-all duration-200 shadow-sm border-2 border-gray-200 hover:border-gray-300">
                    Sign Up
                </a>

                <!-- Forgot Password Link -->
                @if (Route::has('password.request'))
                    <div class="text-center pt-2">
                        <a href="{{ route('password.request') }}"
                            class="text-sm text-gray-600 hover:text-gray-900 font-semibold transition-colors">
                            Forgot Password?
                        </a>
                    </div>
                @endif
            </div>
        @endif

    </div>
</body>

</html>