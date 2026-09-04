@php
    $isDarkActive = ($isDark ?? (request()->cookie('theme') === 'dark'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-full scroll-smooth {{ $isDarkActive ? 'dark' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="dark light">

    <!-- Instant Theme Anti-FOUC & Scrollbar Hidden (Executed synchronously before any network requests/fonts) -->
    <style>
        html { color-scheme: light dark; -ms-overflow-style: none !important; scrollbar-width: none !important; }
        html.dark { background-color: #111827 !important; color-scheme: dark; }
        html.dark body { background-color: #111827 !important; color: #f9fafb; }
        html.dark main { background-color: #111827 !important; }
        html:not(.dark) { background-color: #f3f4f6 !important; color-scheme: light; }
        html:not(.dark) body { background-color: #f3f4f6 !important; }
        .no-transition, .no-transition * { -webkit-transition: none !important; transition: none !important; }
        html, body, *, *::before, *::after { -ms-overflow-style: none !important; scrollbar-width: none !important; }
        *::-webkit-scrollbar, html::-webkit-scrollbar, body::-webkit-scrollbar { display: none !important; width: 0 !important; height: 0 !important; }
    </style>
    <script>
        (function() {
            window.applyTheme = function(mode, opts) {
                try {
                    var skipIfSame = opts && opts.skipIfSame;
                    mode = mode || localStorage.getItem('theme') || localStorage.getItem('color-theme') || 'system';
                    if (mode !== 'dark' && mode !== 'light') mode = 'system';
                    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    var isDark = (mode === 'dark') || (mode === 'system' && prefersDark);
                    var d = document.documentElement;
                    var currentlyDark = d.classList.contains('dark');

                    // Skip redundant DOM mutation to prevent unnecessary repaints / flash
                    if (skipIfSame && isDark === currentlyDark) {
                        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: mode, isDark: isDark } }));
                        return;
                    }

                    if (isDark) {
                        d.classList.add('dark');
                        d.style.colorScheme = 'dark';
                        d.style.backgroundColor = '#111827';
                        if (document.body) document.body.style.backgroundColor = '#111827';
                    } else {
                        d.classList.remove('dark');
                        d.style.colorScheme = 'light';
                        d.style.backgroundColor = '#f3f4f6';
                        if (document.body) document.body.style.backgroundColor = '#f3f4f6';
                    }

                    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: mode, isDark: isDark } }));
                } catch(e) {}
            };

            window.setTheme = function(mode) {
                if (mode !== 'dark' && mode !== 'light') mode = 'system';
                localStorage.setItem('theme', mode);
                localStorage.setItem('color-theme', mode);
                document.cookie = "theme=" + mode + "; path=/; max-age=31536000; SameSite=Lax";
                window.applyTheme(mode);
            };

            window.getTheme = function() {
                var saved = localStorage.getItem('theme') || localStorage.getItem('color-theme');
                if (saved === 'dark' || saved === 'light') return saved;
                return 'system';
            };

            // Execute immediately on page load - skip if HTML already has correct class
            window.applyTheme(null, { skipIfSame: true });

            function scrollToActiveSidebarItem() {
                requestAnimationFrame(function() {
                    try {
                        var nav = document.getElementById('superadmin-sidebar-nav') || document.querySelector('aside nav');
                        if (!nav) return;
                        var activeLink = nav.querySelector('a.bg-primary-600, a.text-white');
                        if (activeLink) {
                            activeLink.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'instant' });
                        }
                    } catch(e) {}
                });
            }

            document.addEventListener('DOMContentLoaded', scrollToActiveSidebarItem);

            // --- Livewire SPA navigation: freeze transitions during page morph ---
            var _noTransEl = null;

            function _disableTransitions() {
                if (_noTransEl) return;
                _noTransEl = document.createElement('style');
                _noTransEl.textContent = '*,*::before,*::after{transition:none!important;animation-duration:0.01ms!important;}';
                document.head.appendChild(_noTransEl);
            }

            function _enableTransitions() {
                requestAnimationFrame(function() {
                    requestAnimationFrame(function() {
                        if (_noTransEl && _noTransEl.parentNode) _noTransEl.parentNode.removeChild(_noTransEl);
                        _noTransEl = null;
                    });
                });
            }

            document.addEventListener('livewire:navigating', function() {
                _disableTransitions();
                window.applyTheme(null, { skipIfSame: true });
            });
            document.addEventListener('livewire:navigated', function() { 
                window.applyTheme(null, { skipIfSame: false });
                _enableTransitions();
                scrollToActiveSidebarItem();
            });
        })();
    </script>

    <title>{{ \App\Models\AppSetting::get('app_name', 'SayaBantu') }} - Super Admin Panel</title>
    @php
        $fav = \App\Models\AppSetting::get('app_favicon') ?: \App\Models\AppSetting::get('app_logo');
    @endphp
    @if($fav && \Illuminate\Support\Facades\Storage::disk('public')->exists($fav))
        <link rel="icon" href="{{ asset('storage/' . $fav) }}">
    @endif

    <!-- Fonts (Loaded asynchronously / non-blocking) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800,900|space-grotesk:400,500,600,700|dm-sans:400,500,700,800,900|syne:400,500,600,700,800|nunito:400,600,700,800,900|playfair-display:400,500,600,700,800,900|outfit:400,500,600,700,800|poppins:400,500,600,700,800|lexend:400,500,600,700,800|montserrat:400,500,600,700,800|inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @livewireStyles
    @stack('head')
</head>

<body class="antialiased bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100" 
      x-data="{ 
          sidebarOpenMobile: false, 
          sidebarOpenDesktop: true,
          showLogoutModal: false,
          toggleSidebar() {
              if (window.innerWidth < 1024) {
                  this.sidebarOpenMobile = !this.sidebarOpenMobile;
              } else {
                  this.sidebarOpenDesktop = !this.sidebarOpenDesktop;
              }
          }
      }" 
      @keydown.escape.window="sidebarOpenMobile = false"
      x-on:livewire:navigated.window="if (window.innerWidth < 1024) sidebarOpenMobile = false"
      @open-logout-modal.window="showLogoutModal = true">

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex relative w-full">

        <!-- Mobile Drawer Backdrop -->
        <div x-show="sidebarOpenMobile" 
             @click="sidebarOpenMobile = false" 
             x-cloak 
             class="fixed inset-0 bg-gray-900/20 backdrop-blur-sm z-40 lg:hidden">
        </div>

        <!-- Sidebar / Drawer Menu -->
        <aside class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-2xl lg:shadow-md fixed inset-y-0 left-0 flex flex-col z-50 -translate-x-full lg:translate-x-0"
               :class="{
                   'translate-x-0': sidebarOpenMobile,
                   'lg:translate-x-0': sidebarOpenDesktop,
                   'lg:-translate-x-full': !sidebarOpenDesktop
               }">
            @php
                $siteName = \App\Models\AppSetting::get('app_name', 'SayaBantu');
                $siteLogo = \App\Models\AppSetting::get('app_logo');
            @endphp
            <!-- Brand / Logo (Pinned Top) -->
            <div class="p-4 sm:p-5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-shrink-0">
                <a href="{{ route('superadmin.dashboard') }}" wire:navigate class="flex items-center gap-3 group min-w-0">
                    @if($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                        <div class="w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 shadow-md shadow-purple-500/10 group-hover:scale-105 transition-transform flex items-center justify-center p-1.5 flex-shrink-0">
                            <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="w-full h-full object-contain" />
                        </div>
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 via-purple-600 to-primary-600 flex items-center justify-center text-white shadow-md shadow-purple-500/25 group-hover:scale-105 group-hover:shadow-lg group-hover:shadow-purple-500/40 transition-transform flex-shrink-0">
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
                <button @click="if (window.innerWidth < 1024) { sidebarOpenMobile = false } else { sidebarOpenDesktop = false }" 
                        type="button" 
                        class="p-1.5 rounded-lg text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition ml-2 flex-shrink-0 cursor-pointer" 
                        title="Tutup Menu"
                        aria-label="Tutup Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Scrollable Navigation -->
            <nav id="superadmin-sidebar-nav" class="flex-1 overflow-y-auto overflow-x-hidden p-4 space-y-1.5 custom-scrollbar min-h-0">
                <a href="{{ route('superadmin.dashboard') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.dashboard') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <div class="pt-4 pb-1">
                    <p class="px-4 text-[11px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Manajemen Data</p>
                </div>

                <a href="{{ route('superadmin.verifications') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.verifications*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                    Verifikasi KTP
                </a>

                <a href="{{ route('superadmin.users') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.users*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Manajemen User
                </a>

                <a href="{{ route('superadmin.cities') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.cities*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Manajemen Kota
                </a>

                <a href="{{ route('superadmin.admin.users') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.admin.users*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Manajemen Admin
                </a>


                <div class="pt-4 pb-1">
                    <p class="px-4 text-[11px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Moderasi & Laporan</p>
                </div>

                <a href="{{ route('superadmin.partners.activity') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.partners.activity*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Aktivitas Mitra & Customer
                </a>

                <a href="{{ route('superadmin.partners.report') }}" wire:navigate
                    class="flex items-center justify-between px-4 py-2.5 {{ request()->routeIs('superadmin.partners.report*') || request()->routeIs('superadmin.partners.reports*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <div class="flex items-center min-w-0">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="truncate">Manajemen Laporan Aduan</span>
                    </div>
                    @php
                        $pendingReportsCount = \App\Models\PartnerReport::getActiveReportsCountForUser();
                    @endphp
                    @if($pendingReportsCount > 0)
                        <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-extrabold {{ request()->routeIs('superadmin.partners.report*') || request()->routeIs('superadmin.partners.reports*') ? 'bg-white text-rose-600 shadow-2xs' : 'bg-rose-500 text-white shadow-xs' }} ml-2 shrink-0 animate-pulse" title="{{ $pendingReportsCount }} Aduan Masuk / Diproses">
                            {{ $pendingReportsCount > 99 ? '99+' : $pendingReportsCount }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('superadmin.partners.greylist') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.partners.greylist*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Daftar Abu-Abu & SP</span>
                </a>

                <a href="{{ route('superadmin.partners.blocked') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.partners.blocked*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    Blokir Mitra
                </a>

                <div class="pt-4 pb-1">
                    <p class="px-4 text-[11px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Manajemen Keuangan</p>
                </div>

                <a href="{{ route('superadmin.withdraws.index') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.withdraws.*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Manajemen Withdraw
                </a>

                <a href="{{ route('superadmin.topup.approvals') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.topup.approvals*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Manajemen Top-Up
                </a>

                <div class="pt-4 pb-1">
                    <p class="px-4 text-[11px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Report & Logs</p>
                </div>

                <a href="{{ route('superadmin.transactions.log') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.transactions.log') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Financial Report
                </a>
                
                <a href="{{ route('superadmin.activity.logs') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('superadmin.activity.logs*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    Activity Logs
                </a>

            </nav>

            <!-- Fixed Bottom Actions (Pengaturan & Logout) -->
            <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex-shrink-0 space-y-1">
                <a href="{{ route('superadmin.settings.appearance') }}" wire:navigate
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
        <main class="flex-1 min-h-screen min-w-0 flex flex-col w-full lg:ml-64"
              :class="{ 'lg:ml-64': sidebarOpenDesktop, 'lg:!ml-0': !sidebarOpenDesktop }">
            <!-- Topbar -->
            @php
                $currentMenuName = match(true) {
                    request()->routeIs('superadmin.dashboard') => 'Dashboard Utama',
                    request()->routeIs('superadmin.verifications.*') || request()->routeIs('superadmin.verifications') => 'Verifikasi Akun Mitra',
                    request()->routeIs('superadmin.ktp-ocr.*') || request()->routeIs('superadmin.ktp-ocr') => 'OCR KTP & Verifikasi',
                    request()->routeIs('superadmin.reports.*') || request()->routeIs('superadmin.partners.reports*') => 'Laporan Aduan',
                    request()->routeIs('superadmin.users.*') => 'Manajemen Pengguna',
                    request()->routeIs('superadmin.categories.*') => 'Kategori Bantuan',
                    request()->routeIs('superadmin.banners.*') => 'Manajemen Banner Promo',
                    request()->routeIs('superadmin.blacklist-partners.*') => 'Blokir Mitra',
                    request()->routeIs('superadmin.withdraws.*') => 'Manajemen Withdraw',
                    request()->routeIs('superadmin.topup.approvals*') => 'Manajemen Top-Up',
                    request()->routeIs('superadmin.transactions.log*') => 'Financial Report',
                    request()->routeIs('superadmin.activity.logs*') => 'Activity Logs',
                    request()->routeIs('superadmin.settings.*') => 'Pengaturan',
                    request()->routeIs('superadmin.notifications.*') => 'Notifikasi Super Admin',
                    default => (View::hasSection('page-title') ? trim(View::getSection('page-title')) : ($title ?? 'Dashboard Utama')),
                };
            @endphp
            <header x-data="{ isScrolled: false }"
                    x-init="isScrolled = (window.pageYOffset > 10 || document.documentElement.scrollTop > 10)"
                    @scroll.window.passive="isScrolled = (window.pageYOffset > 10 || document.documentElement.scrollTop > 10)"
                    class="sticky top-0 z-30 border-b w-full transition-all duration-300"
                    :class="isScrolled ? 'bg-white/20 dark:bg-gray-800/40 backdrop-blur-md border-gray-200/50 dark:border-gray-700/60 shadow-xs' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700'">
                <div class="px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Menu Toggle Button (Always visible on all screens: Desktop, Tablet & Mobile) -->
                        <button @click="toggleSidebar()" 
                                type="button" 
                                class="inline-flex items-center justify-center p-2 rounded-xl bg-gray-100/80 dark:bg-gray-700/60 border border-gray-200/60 dark:border-gray-600/60 text-gray-600 dark:text-gray-200 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all flex-shrink-0 cursor-pointer" 
                                title="Toggle Menu Sidebar"
                                aria-label="Toggle Menu Sidebar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Single Breadcrumb Line Format -->
                        <div class="flex items-center gap-2 min-w-0 text-sm font-bold truncate">
                            <span class="text-gray-400 dark:text-gray-400 flex-shrink-0">Super Admin</span>
                            <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            <span class="text-gray-900 dark:text-white truncate">{{ $currentMenuName }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                        <!-- Quick actions (Refresh) -->
                        <div class="hidden sm:flex items-center gap-2">
                            <button onclick="location.reload()" class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100/80 dark:bg-gray-700/60 border border-gray-200/60 dark:border-gray-600/60 text-xs font-semibold text-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-200/80 dark:hover:bg-gray-600/80 transition cursor-pointer">
                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6"/></svg>
                                <span class="hidden md:inline">Refresh</span>
                            </button>
                        </div>

                        <!-- Notifications Dropdown -->
                        <livewire:superadmin.notifications.dropdown />

                        <!-- User Profile -->
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="w-9 h-9 rounded-full bg-primary-600 text-white flex items-center justify-center font-bold text-sm shadow-xs">{{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}</div>
                            <div class="hidden sm:block">
                                <div class="text-sm font-bold text-gray-800 dark:text-gray-200 max-w-[120px] truncate">{{ auth()->user()->name ?? 'Super Admin' }}</div>
                                <div class="text-[11px] text-gray-400 dark:text-gray-400">Semua Wilayah</div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="p-4 sm:p-6 lg:p-8 w-full max-w-full flex-1 overflow-x-clip min-w-0">
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
    <script>
        // Safe fallback for Laravel Echo when WebSocket is not active
        if (typeof window !== 'undefined' && typeof window.Echo === 'undefined') {
            window.Echo = {
                socketId: () => undefined,
                private: () => ({ listen: () => ({}), stopListening: () => ({}) }),
                channel: () => ({ listen: () => ({}), stopListening: () => ({}) }),
                join: () => ({ here: () => ({ joining: () => ({ leaving: () => ({ listen: () => ({}) }) }) }) }),
                leave: () => {},
                leaveChannel: () => {},
                connector: {
                    socketId: () => undefined,
                    channels: {},
                }
            };
        }
    </script>
    @livewireScripts
    @stack('scripts')
</body>


</html>