<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Panel' }} - sayabantu</title>

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
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Admin Panel</p>
            </div>

            <nav class="p-4 pb-28">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('admin.dashboard') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <div class="mt-6 mb-2">
                    <p class="px-4 text-xs font-semibold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Moderasi</p>
                </div>

                <a href="{{ route('admin.verifications') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('admin.verifications*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Verifikasi KTP
                </a>

                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('admin.users.*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5V10l-5-5m-5 15h5V10l-5-5m0 15H7a2 2 0 01-2-2V7a2 2 0 012-2h5" />
                    </svg>
                    Kelola Pengguna
                </a>

                <a href="{{ route('admin.partners.activity') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('admin.partners.activity') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 13l4 4L19 7M9 5a3 3 0 00-3 3v10h12V8a3 3 0 00-3-3H9z" />
                    </svg>
                    Aktivitas Mitra
                </a>

                <a href="{{ route('admin.partners.report') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('admin.partners.report') || request()->routeIs('admin.partners.reports.*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Manajemen Laporan Aduan
                </a>

                <a href="{{ route('admin.partners.blocked') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('admin.partners.blocked') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 5.636l-12.728 12.728M6.343 6.343l11.314 11.314M9 5h6a2 2 0 012 2v10a2 2 0 01-2 2H9a2 2 0 01-2-2V7a2 2 0 012-2z" />
                    </svg>
                    Blokir Mitra
                </a>

                <div class="mt-6 mb-2">
                    <p class="px-4 text-xs font-semibold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Keuangan</p>
                </div>

                <a href="{{ route('admin.withdraws.index') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('admin.withdraws.*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8v8" />
                    </svg>
                    Manajemen Withdraw
                </a>

                <a href="{{ route('admin.topup.approvals') }}"
                    class="flex items-center px-4 py-3 mb-2 {{ request()->routeIs('admin.topup.approvals*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7 7h.01M7 11h.01M7 15h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Manajemen Approval
                </a>

                <a href="{{ route('admin.settings.appearance') }}"
                    class="flex items-center px-4 py-3 mb-2 mt-4 {{ request()->routeIs('admin.settings.appearance*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition">
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
                        <p class="text-xs text-gray-500 dark:text-gray-400">Admin</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64 overflow-y-auto min-h-screen">
            <!-- Topbar -->
            <div class="sticky top-0 z-20 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 transition-colors duration-200">
                <div class="px-6 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="hidden sm:flex items-center text-xs text-gray-400 dark:text-gray-400 gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="hover:underline">Admin</a>
                            <svg class="w-3 h-3 text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <span class="font-medium text-gray-700 dark:text-gray-200">@yield('page-title', 'Dashboard')</span>
                        </div>

                        <div class="min-w-0">
                            @hasSection('page-title')
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white truncate">@yield('page-title')</h2>
                                    @hasSection('page-description')
                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5 truncate">@yield('page-description')</div>
                                    @endif
                                </div>
                            @else
                                <div></div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Quick actions (Refresh) -->
                        <div class="hidden sm:flex items-center gap-2">
                            <button onclick="location.reload()" class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6"/></svg>
                                <span class="hidden md:inline">Refresh</span>
                            </button>
                        </div>

                        <!-- Notifications -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click.prevent="open = !open" class="p-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition" aria-haspopup="true" :aria-expanded="open">
                                <svg class="w-5 h-5 text-gray-600 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                @php
                                    $notes = collect($notifications ?? []);
                                    $unread = $notes->where('read', false)->count();
                                @endphp
                                @if($unread)
                                    <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full">{{ $unread }}</span>
                                @endif
                            </button>

                            <div x-show="open" x-cloak x-transition class="origin-top-right absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-xl overflow-hidden z-50">
                                <div class="p-3 border-b border-gray-100 dark:border-gray-700 text-sm font-semibold text-gray-800 dark:text-gray-100">Notifikasi</div>
                                <div class="max-h-64 overflow-auto">
                                    @if($notes->isEmpty())
                                        <div class="p-4 text-sm text-gray-500 dark:text-gray-400">Tidak ada notifikasi.</div>
                                    @else
                                        @foreach($notes->take(20) as $note)
                                            <a href="{{ $note['link'] ?? '#' }}" class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                                <div class="flex-shrink-0">
                                                    <div class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300"> 
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1"/></svg>
                                                    </div>
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-sm text-gray-800 dark:text-gray-200">{{ $note['title'] ?? ($note['message'] ?? 'Notifikasi') }}</div>
                                                    @if(!empty($note['time']))
                                                        <div class="text-xs text-gray-400 dark:text-gray-400 mt-1">{{ $note['time'] }}</div>
                                                    @endif
                                                </div>
                                            </a>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="p-2 border-t border-gray-100 dark:border-gray-700 text-center">
                                    @if(Route::has('admin.notifications.index'))
                                        <a href="{{ route('admin.notifications.index') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">Lihat semua</a>
                                    @else
                                        <a href="#" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">Lihat semua</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- User Profile -->
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-primary-600 text-white flex items-center justify-center font-semibold">{{ strtoupper(substr(auth()->user()->name ?? 'A',0,1)) }}</div>
                            <div class="hidden sm:block">
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ auth()->user()->name ?? 'Admin' }}</div>
                                <div class="text-xs text-gray-400 dark:text-gray-400">Admin</div>
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
                                Apakah Anda yakin ingin keluar dari panel Admin?
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                Anda harus login kembali untuk mengakses panel ini.
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