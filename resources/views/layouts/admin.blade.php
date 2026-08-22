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
            window.applyTheme = function(mode) {
                try {
                    mode = mode || localStorage.getItem('theme') || localStorage.getItem('color-theme') || 'system';
                    if (mode !== 'dark' && mode !== 'light') mode = 'system';
                    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    var isDark = (mode === 'dark') || (mode === 'system' && prefersDark);
                    var d = document.documentElement;
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

            // Execute immediately on page load
            window.applyTheme();

            document.addEventListener('livewire:navigating', function() { if (window.applyTheme) window.applyTheme(); });
            document.addEventListener('livewire:navigated', function() { if (window.applyTheme) window.applyTheme(); });
        })();
    </script>

    <title>{{ $title ?? 'Admin Panel' }} - {{ \App\Models\AppSetting::get('app_name', 'SayaBantu') }}</title>
    @php
        $fav = \App\Models\AppSetting::get('app_favicon') ?: \App\Models\AppSetting::get('app_logo');
    @endphp
    @if($fav && \Illuminate\Support\Facades\Storage::disk('public')->exists($fav))
        <link rel="icon" href="{{ asset('storage/' . $fav) }}">
    @endif

    <!-- Fonts (Loaded asynchronously / non-blocking) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|outfit:400,500,600,700,800|poppins:400,500,600,700,800|lexend:400,500,600,700,800|montserrat:400,500,600,700,800|inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
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
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-3 group min-w-0">
                    @if($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                        <div class="w-10 h-10 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 shadow-md shadow-primary-500/10 group-hover:scale-105 transition-transform flex items-center justify-center p-1.5 flex-shrink-0">
                            <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="w-full h-full object-contain" />
                        </div>
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 via-primary-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-primary-500/25 group-hover:scale-105 group-hover:shadow-lg group-hover:shadow-primary-500/40 transition-transform flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1">
                            <x-brand-title :name="$siteName" size="lg" theme="admin" withDot="true" class="leading-tight truncate" />
                        </div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary-50 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800/60 uppercase tracking-wider">
                                Admin Panel
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
            <nav class="flex-1 overflow-y-auto overflow-x-hidden p-4 space-y-1.5 custom-scrollbar min-h-0">
                <a href="{{ route('admin.dashboard') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.dashboard') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>

                <div class="pt-4 pb-1">
                    <p class="px-4 text-[11px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Moderasi</p>
                </div>

                <a href="{{ route('admin.verifications') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.verifications*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                    Verifikasi KTP
                </a>

                <a href="{{ route('admin.users.index') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.users.*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Kelola Pengguna
                </a>

                <a href="{{ route('admin.partners.activity') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.partners.activity') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Aktivitas Mitra
                </a>

                <a href="{{ route('admin.partners.report') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.partners.report') || request()->routeIs('admin.partners.reports.*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    Manajemen Laporan Aduan
                </a>

                <a href="{{ route('admin.partners.blocked') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.partners.blocked') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    Blokir Mitra
                </a>

                <div class="pt-4 pb-1">
                    <p class="px-4 text-[11px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Keuangan</p>
                </div>

                <a href="{{ route('admin.withdraws.index') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.withdraws.*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Manajemen Withdraw
                </a>

                <a href="{{ route('admin.topup.approvals') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.topup.approvals*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Manajemen Approval
                </a>

            </nav>

            <!-- Fixed Bottom Actions (Pengaturan & Logout) -->
            <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex-shrink-0 space-y-1">
                <a href="{{ route('admin.settings.appearance') }}" wire:navigate
                    class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.settings.*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition text-sm font-medium">
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
            <header class="sticky top-0 z-30 border-b w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 shadow-xs">
                <div class="px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Menu Toggle Button (Always visible on all screens: Desktop, Tablet & Mobile) -->
                        <button @click="toggleSidebar()" 
                                type="button" 
                                class="inline-flex items-center justify-center p-2 rounded-xl bg-gray-100 dark:bg-gray-700/80 text-gray-600 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all flex-shrink-0 cursor-pointer" 
                                title="Toggle Menu Sidebar"
                                aria-label="Toggle Menu Sidebar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div class="hidden sm:flex items-center text-xs text-gray-400 dark:text-gray-400 gap-2">
                            <span>Admin</span>
                            <svg class="w-3 h-3 text-gray-300 dark:text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <span class="font-medium text-gray-700 dark:text-gray-200">@yield('page-title', 'Dashboard')</span>
                        </div>

                        <div class="min-w-0">
                            @hasSection('page-title')
                                <div>
                                    <h2 class="text-base sm:text-lg font-semibold text-gray-800 dark:text-white truncate">@yield('page-title')</h2>
                                    @hasSection('page-description')
                                        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5 truncate hidden sm:block">@yield('page-description')</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                        <!-- Quick actions (Refresh) -->
                        <div class="hidden sm:flex items-center gap-2">
                            <button onclick="location.reload()" class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                                <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6"/></svg>
                                <span class="hidden md:inline">Refresh</span>
                            </button>
                        </div>

                        <!-- Notifications -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click.prevent="open = !open" class="p-2 rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600" aria-haspopup="true" :aria-expanded="open">
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

                            <div x-show="open" x-cloak class="origin-top-right absolute right-0 mt-2 w-72 sm:w-80 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-xl overflow-hidden z-50">
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
                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="w-9 h-9 rounded-full bg-primary-600 text-white flex items-center justify-center font-semibold text-sm">{{ strtoupper(substr(auth()->user()->name ?? 'A',0,1)) }}</div>
                            <div class="hidden sm:block">
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-200 max-w-[120px] truncate">{{ auth()->user()->name ?? 'Admin' }}</div>
                                <div class="text-xs text-gray-400 dark:text-gray-400">Admin</div>
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