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
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fadeInUp {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>

<body class="font-sans antialiased bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50">
    <div class="max-w-sm mx-auto min-h-screen flex flex-col">

        <!-- Header Navigation -->
        <header class="px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
                <span class="text-xl font-bold text-gray-800">sayabantu</span>
            </div>
            <a href="{{ route('login') }}" class="text-sm font-semibold text-purple-600 hover:text-purple-700">
                Masuk
            </a>
        </header>

        <!-- Hero Section -->
        <main class="flex-1 flex flex-col justify-center px-6 py-12">
            
            <div class="text-center mb-8 animate-fadeInUp">
                <!-- Main Icon with Gradient Background -->
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full gradient-bg shadow-xl mb-6">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>

                <!-- Headline -->
                    <h1 class="text-4xl font-bold text-gray-900 mb-4 leading-tight">
                    Berbagi <span class="text-transparent bg-clip-text gradient-bg">Kebaikan</span><br/>
                    Saling Membantu
                </h1>

                <!-- Subheadline -->
                <p class="text-gray-600 text-base leading-relaxed max-w-xs mx-auto">
                    Platform yang menghubungkan mereka yang membutuhkan dengan mereka yang ingin membantu
                </p>
            </div>

            <!-- Feature Cards -->
            <div class="grid grid-cols-3 gap-4 mb-8 animate-fadeInUp" style="animation-delay: 0.2s;">
                <!-- Feature 1 -->
                <div class="bg-white rounded-2xl p-4 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 mb-2">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-700">Bantuan Dana</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white rounded-2xl p-4 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-purple-100 mb-2">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-700">Terpercaya</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-2xl p-4 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-pink-100 mb-2">
                        <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-700">Cepat & Mudah</p>
                </div>
            </div>

            <!-- CTA Buttons -->
            <div class="space-y-3 animate-fadeInUp" style="animation-delay: 0.4s;">
                <!-- Primary CTA -->
                <a href="{{ route('register') }}"
                    class="block w-full gradient-bg text-white font-bold py-4 rounded-2xl text-center transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    Mulai Sekarang
                </a>

                <!-- Secondary CTA -->
                <a href="{{ route('login') }}"
                    class="block w-full bg-white text-gray-700 font-semibold py-4 rounded-2xl text-center transition shadow-sm border-2 border-gray-200 hover:border-purple-300 hover:shadow-md">
                    Sudah Punya Akun? Masuk
                </a>
            </div>

        </main>

        <!-- Footer -->
        <footer class="px-6 py-6 text-center">
            <p class="text-xs text-gray-500">
                © {{ date('Y') }} sayabantu. Platform Bantuan Sosial Indonesia.
            </p>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                    class="inline-block mt-3 text-xs text-purple-600 hover:text-purple-700 font-semibold">
                    Lupa Password?
                </a>
            @endif
        </footer>

    </div>
</body>

</html>