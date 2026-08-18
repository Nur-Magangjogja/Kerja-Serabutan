<div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex transition-colors duration-200">
    <!-- Sidebar -->
    <aside class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-lg fixed inset-y-0 left-0 flex flex-col z-30 transition-colors duration-200">
        @php
            $siteName = \App\Models\AppSetting::get('app_name', 'SayaBantu');
            $siteLogo = \App\Models\AppSetting::get('app_logo');
        @endphp
        <!-- Logo/Brand (Pinned Top) -->
        <div class="p-5 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                @if($siteLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteLogo))
                    <div class="w-10 h-10 rounded-xl bg-white dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 shadow-md shadow-primary-500/10 group-hover:scale-105 transition-all duration-200 flex items-center justify-center p-1.5 flex-shrink-0">
                        <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="w-full h-full object-contain" />
                    </div>
                @else
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 via-primary-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-primary-500/25 group-hover:scale-105 group-hover:shadow-lg group-hover:shadow-primary-500/40 transition-all duration-200 flex-shrink-0">
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
                            {{ auth()->user()->role === 'super_admin' ? 'Super Admin' : 'Admin Panel' }}
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Scrollable Navigation Menu -->
        <nav class="flex-1 overflow-y-auto overflow-x-hidden p-4 space-y-1.5 custom-scrollbar min-h-0">
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.dashboard') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>

            @if(auth()->user()->role === 'super_admin')
                <div class="pt-4 pb-1">
                    <p class="px-4 text-[11px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Super Admin</p>
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
            @endif

            <div class="pt-4 pb-1">
                <p class="px-4 text-[11px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Moderasi</p>
            </div>

            <a href="{{ route('admin.helps') }}"
                class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.helps*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Moderasi Bantuan
            </a>

            <a href="{{ route('admin.verifications') }}"
                class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.verifications*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                </svg>
                Verifikasi KTP
            </a>

            <div class="pt-4 pb-1">
                <p class="px-4 text-[11px] font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider">Keuangan</p>
            </div>

            <a href="{{ route('admin.withdraws.index') }}"
                class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.withdraws.*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Manajemen Withdraw
            </a>

            <a href="{{ route('admin.topup.approvals') }}"
                class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.topup*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-xl transition text-sm font-medium">
                <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Approval Top-Up
            </a>

        </nav>

        <!-- Fixed Bottom Actions (Pengaturan & Logout) -->
        <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex-shrink-0 space-y-1 transition-colors duration-200">
            <a href="{{ route('admin.settings.appearance') }}"
                class="flex items-center px-4 py-2.5 {{ request()->routeIs('admin.settings.*') ? 'text-white bg-primary-600 shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg transition text-sm font-medium">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Pengaturan
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center px-4 py-2.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg transition text-left text-sm font-medium">
                    <svg class="w-5 h-5 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 min-h-screen">
        <!-- Header -->
        <header x-data="{ isScrolled: false }"
                @scroll.window="isScrolled = (window.pageYOffset > 10)"
                class="shadow-sm border-b sticky top-0 z-10 transition-all duration-300"
                :class="isScrolled 
                    ? 'bg-white/20 dark:bg-gray-800/20 backdrop-blur-md border-gray-200/30 dark:border-gray-700/30 shadow-xs' 
                    : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700'">
            <div class="px-8 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $title ?? 'Admin Panel' }}</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">{{ $subtitle ?? '' }}</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        {{ $headerActions ?? '' }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="p-6 sm:p-8">
            {{ $slot }}
        </div>
    </main>
</div>