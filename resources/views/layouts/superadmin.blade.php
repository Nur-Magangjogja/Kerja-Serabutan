<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \App\Models\AppSetting::get('app_name', 'SayaBantu') }} - Super Admin Panel</title>
    @php
        $fav = \App\Models\AppSetting::get('app_favicon') ?: \App\Models\AppSetting::get('app_logo');
    @endphp
    @if($fav && \Illuminate\Support\Facades\Storage::disk('public')->exists($fav))
        <link rel="icon" href="{{ asset('storage/' . $fav) }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|outfit:400,500,600,700,800|poppins:400,500,600,700,800|lexend:400,500,600,700,800|montserrat:400,500,600,700,800|inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Flowbite & Global Theme Initialization Script (Anti-FOUC) -->
    <script>
        window.getTheme = function() {
            return localStorage.getItem('color-theme') || localStorage.getItem('theme') || 'system';
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
            localStorage.setItem('color-theme', mode);
            window.applyTheme(mode);
        };

        // Execute immediately to set dark class before DOM render (Flowbite compatible)
        window.applyTheme();

        window.updateChartDefaults = function() {
            if (window.Chart) {
                const isDark = document.documentElement.classList.contains('dark');
                Chart.defaults.color = isDark ? '#9ca3af' : '#64748b';
                Chart.defaults.borderColor = isDark ? '#374151' : 'rgba(15, 23, 42, 0.06)';
            }
        };

        window.addEventListener('theme-changed', function() {
            window.updateChartDefaults();
        });

        document.addEventListener('DOMContentLoaded', function() {
            window.updateChartDefaults();
        });

        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
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
        <aside class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-lg fixed inset-y-0 left-0 flex flex-col z-30 transition-colors duration-200">
            @php
                $siteName = \App\Models\AppSetting::get('app_name', 'SayaBantu');
                $siteLogo = \App\Models\AppSetting::get('app_logo');
            @endphp
            <!-- Brand / Logo (Pinned Top) -->
            <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                <a href="{{ route('superadmin.dashboard') }}" class="flex items-center gap-3 group">
                    @if($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                        <div class="w-10 h-10 rounded-xl bg-white dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 shadow-md shadow-purple-500/10 group-hover:scale-105 transition-all duration-200 flex items-center justify-center p-1.5 flex-shrink-0">
                            <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="w-full h-full object-contain" />
                        </div>
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 via-purple-600 to-primary-600 flex items-center justify-center text-white shadow-md shadow-purple-500/25 group-hover:scale-105 group-hover:shadow-lg group-hover:shadow-purple-500/40 transition-all duration-200 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1">
                            <x-brand-title :name="$siteName" size="lg" theme="admin" withDot="true" class="leading-tight truncate" />
                        </div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60 uppercase tracking-wider">
                                Super Admin
                            </span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Scrollable Navigation -->
            <nav class="flex-1 overflow-y-auto overflow-x-hidden p-4 space-y-1.5 custom-scrollbar min-h-0">
                <a href="{{ route('superadmin.dashboard') }}"
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.dashboard') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <div class="pt-4 pb-1">
                    <p class="px-4 text-[11px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Manajemen Data</p>
                </div>

                <a href="{{ route('superadmin.users') }}"
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.users*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Manajemen User
                </a>

                <a href="{{ route('superadmin.cities') }}"
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.cities*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Manajemen Kota
                </a>

                <a href="{{ route('superadmin.admin.users') }}"
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.admin.users*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Manajemen Admin
                </a>

                <div class="pt-4 pb-1">
                    <p class="px-4 text-[11px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Keuangan & Logs</p>
                </div>

                <a href="{{ route('superadmin.transactions.log') }}"
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.transactions.log*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Financial Report
                </a>

                <a href="{{ route('superadmin.withdraws.index') }}"
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.withdraws*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Manajemen Withdraw
                </a>

                <a href="{{ route('superadmin.topup.approvals') }}"
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.topup.approvals*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Approval Top-Up
                </a>

                <a href="{{ route('superadmin.activity.logs') }}"
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.activity.logs*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Activity Logs
                </a>

            </nav>

            <!-- Fixed Bottom Actions (Pengaturan & Logout) -->
            <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex-shrink-0 space-y-1 transition-colors duration-200">
                <a href="{{ route('superadmin.settings.appearance') }}"
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.settings.*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Pengaturan
                </a>

                <button 
                    @click="$dispatch('open-logout-modal')" 
                    type="button" 
                    class="w-full flex items-center px-4 py-2.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition text-left text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64 min-h-screen min-w-0">
            <!-- Topbar -->
            <div x-data="{ 
                     isScrolled: false,
                     checkScroll() { this.isScrolled = (window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0) > 10; }
                 }" 
                 x-init="checkScroll()"
                 @scroll.window="checkScroll()"
                 class="sticky top-0 z-30 transition-all duration-300 border-b"
                 :class="isScrolled 
                     ? 'bg-white/20 dark:bg-gray-900/20 backdrop-blur-md border-gray-200/40 dark:border-gray-700/40 shadow-sm' 
                     : 'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700'"
                 style="position: -webkit-sticky; position: sticky; top: 0;">
                <div class="px-6 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="hidden sm:flex items-center text-xs text-gray-400 dark:text-gray-400 gap-2">
                            <span>Super Admin</span>
                            <svg class="w-3 h-3 text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <span class="font-medium text-gray-700 dark:text-gray-200 truncate">@yield('page-title', $title ?? 'Dashboard')</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Notifications Dropdown -->
                        <livewire:superadmin.notification-dropdown />

                        <!-- User Profile -->
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-primary-600 text-white flex items-center justify-center font-semibold">{{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}</div>
                            <div class="hidden sm:block">
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ auth()->user()->name ?? 'Super Admin' }}</div>
                                <div class="text-xs text-gray-400 dark:text-gray-400">Super Admin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                @hasSection('content')
                    @yield('content')
                @elseif(isset($slot))
                    {{ $slot }}
                @endif
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

    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof initFlowbite === 'function') initFlowbite();
        });
        document.addEventListener('livewire:navigated', () => {
            if (typeof initFlowbite === 'function') initFlowbite();
        });
    </script>
    @livewireScripts
    @stack('scripts')
</body>

</html>