<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Super Admin Panel' }} - sayabantu</title>

    <!-- Theme Initialization Script (Anti-FOUC & Global Theme Controller) -->
    <script>
        window.getTheme = function() {
            return localStorage.getItem('theme') || 'system';
        };

        window.applyTheme = function(mode) {
            mode = mode || window.getTheme();
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = mode === 'dark' || (mode === 'system' && prefersDark);
            
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: mode, isDark: isDark } }));
        };

        window.setTheme = function(mode) {
            localStorage.setItem('theme', mode);
            window.applyTheme(mode);
        };

        // Execute immediately to set dark class before render
        window.applyTheme();

        window.updateChartDefaults = function() {
            if (window.Chart) {
                const isDark = document.documentElement.classList.contains('dark');
                Chart.defaults.color = isDark ? '#94a3b8' : '#64748b';
                Chart.defaults.borderColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(15, 23, 42, 0.06)';
            }
        };

        window.addEventListener('theme-changed', function() {
            window.updateChartDefaults();
        });

        document.addEventListener('DOMContentLoaded', function() {
            window.updateChartDefaults();
        });

        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
                if (window.getTheme() === 'system') {
                    window.applyTheme('system');
                }
            });
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="antialiased bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 transition-colors duration-200" x-data="{ showLogoutModal: false }" @open-logout-modal.window="showLogoutModal = true">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex transition-colors duration-200">
        <!-- Sidebar -->
        <aside class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-lg fixed h-full overflow-y-auto transition-colors duration-200">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h1 class="text-2xl font-bold text-primary-600 dark:text-primary-400">sayabantu</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Super Admin Panel</p>
            </div>

            <nav class="p-4 pb-28">
                <a href="{{ route('superadmin.dashboard') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('superadmin.dashboard') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <div class="mt-6 mb-2">
                    <p class="px-4 text-xs font-semibold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Manajemen Data</p>
                </div>

                <a href="{{ route('superadmin.users') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('superadmin.users*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Manajemen User
                </a>

                <a href="{{ route('superadmin.cities') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('superadmin.cities*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Manajemen Kota
                </a>

                <a href="{{ route('superadmin.admin.users') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('superadmin.admin.users*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11c1.657 0 3-1.343 3-3S17.657 5 16 5s-3 1.343-3 3 1.343 3 3 3zM6 21v-2a4 4 0 014-4h4a4 4 0 014 4v2" />
                    </svg>
                    Manajemen Admin
                </a>

                <div class="mt-6 mb-2">
                    <p class="px-4 text-xs font-semibold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Keuangan & Logs</p>
                </div>

                <a href="{{ route('superadmin.transactions.log') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('superadmin.transactions.log*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Financial Report
                </a>

                <a href="{{ route('superadmin.withdraws.index') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('superadmin.withdraws*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c.79 0 1.5.3 2.04.78L20 14v6a1 1 0 01-1 1h-6l-5.22-5.22A4 4 0 1112 8z" />
                    </svg>
                    Manajemen Withdraw
                </a>

                <a href="{{ route('superadmin.topup.approvals') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('superadmin.topup.approvals*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Approval Top-Up
                </a>

                <a href="{{ route('superadmin.activity.logs') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('superadmin.activity.logs*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Activity Logs
                </a>

                <div class="mt-6 mb-2">
                    <p class="px-4 text-xs font-semibold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Pengaturan Platform</p>
                </div>

                <a href="{{ route('superadmin.settings.help') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('superadmin.settings.help*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c.79 0 1.5.3 2.04.78L20 14v6a1 1 0 01-1 1h-6l-5.22-5.22A4 4 0 1112 8z" />
                    </svg>
                    Pengaturan Bantuan
                </a>

                <a href="{{ route('superadmin.settings.banners') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('superadmin.settings.banners*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    Pengaturan Banner
                </a>

                <a href="{{ route('superadmin.settings.appearance') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('superadmin.settings.appearance*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Pengaturan
                </a>

                <button 
                    @click="$dispatch('open-logout-modal')" 
                    type="button" 
                    class="w-full flex items-center px-4 py-3 mb-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition text-left font-medium">
                    <svg class="w-5 h-5 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </nav>

            <div class="absolute bottom-0 w-64 p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 transition-colors duration-200">
                <div class="flex items-center min-w-0">
                    <div class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center text-white font-semibold flex-shrink-0">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="ml-3 min-w-0 truncate">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Super Admin</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64 overflow-y-auto min-h-screen">
            <!-- Navbar -->
            <nav class="bg-white dark:bg-gray-800 shadow-md sticky top-0 z-40 border-b border-gray-200 dark:border-gray-700 transition-colors duration-200">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <!-- Page Title & Breadcrumb -->
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $title ?? 'Super Admin Panel' }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ $breadcrumb ?? 'Dashboard' }}
                            </p>
                        </div>

                        <!-- Right Side Actions -->
                        <div class="flex items-center space-x-4">
                            <!-- Notifications Dropdown -->
                            <livewire:super-admin.notification-dropdown />

                            <!-- User Profile -->
                            <div class="flex items-center space-x-3 border-l border-gray-200 dark:border-gray-700 pl-4">
                                <div class="text-right hidden sm:block">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Super Admin</p>
                                </div>
                                <div class="w-10 h-10 bg-primary-600 text-white rounded-full flex items-center justify-center font-semibold flex-shrink-0">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="p-6">
                @if(isset($slot))
                    {{ $slot }}
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Logout Confirmation Modal -->
    <div 
        x-show="showLogoutModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" 
        role="dialog" 
        aria-modal="true">
        
        <!-- Background overlay -->
        <div 
            x-show="showLogoutModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500/75 dark:bg-black/80 backdrop-blur-sm transition-opacity"
            @click="showLogoutModal = false">
        </div>

        <!-- Modal panel -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div 
                x-show="showLogoutModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-2xl transition-all sm:w-full sm:max-w-lg border border-gray-100 dark:border-gray-700"
                @click.stop>
                
                <div class="bg-white dark:bg-gray-800 px-6 pt-6 pb-4">
                    <!-- Icon -->
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/40">
                        <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    
                    <!-- Content -->
                    <div class="mt-4 text-center">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white" id="modal-title">
                            Konfirmasi Logout
                        </h3>
                        <div class="mt-3">
                            <p class="text-base text-gray-600 dark:text-gray-300">
                                Apakah Anda yakin ingin keluar dari panel Super Admin?
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="bg-gray-50 dark:bg-gray-750 px-6 py-4 flex flex-col-reverse sm:flex-row gap-3 sm:gap-3 border-t border-gray-100 dark:border-gray-700">
                    <button 
                        type="button"
                        @click="showLogoutModal = false"
                        class="flex-1 inline-flex justify-center items-center rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-6 py-3 text-base font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Batal
                    </button>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button 
                            type="submit"
                            class="w-full inline-flex justify-center items-center rounded-xl bg-red-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-red-700 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Ya, Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>

</html>