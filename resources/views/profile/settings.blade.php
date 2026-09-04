<x-app-layout>
    <x-slot name="title">Pengaturan</x-slot>

    @php
        $user = auth()->user();
        $userBalance = (float) $user->balance;
    @endphp

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 pb-24">
        <!-- Header Section -->
        <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

            <div class="relative z-10">
                <div class="relative flex items-center justify-center min-h-[40px] text-white">
                    <div class="text-center w-full min-w-0 px-12">
                        <h1 class="text-base font-bold truncate">Pengaturan</h1>
                        <p class="text-xs text-white/90 truncate mt-0.5">Kelola preferensi dan keamanan akun Anda</p>
                    </div>

                    <a href="{{ route('customer.notifications.index') }}" class="absolute right-0 top-1/2 -translate-y-1/2 z-20 p-2 hover:bg-white/20 rounded-xl transition cursor-pointer" title="Notifikasi">
                        <div class="relative">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @php
                                $unreadNotif = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
                            @endphp
                            @if($unreadNotif > 0)
                                <span class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                            @endif
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <div class="px-5 pt-4">
            @if (session()->has('message'))
                <div class="p-4 rounded-2xl bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 text-xs flex items-center gap-2.5 shadow-sm">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-xs flex items-center gap-2.5 shadow-sm">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-xs space-y-1 shadow-sm">
                    @foreach ($errors->all() as $err)
                        <div class="flex items-center gap-2">
                            <span>•</span>
                            <span>{{ $err }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Settings Content -->
        <div class="px-5 pt-3 relative z-10">
            <div class="space-y-3">
                <!-- Theme / Appearance Settings -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 transition">
                    <div class="flex items-center gap-3.5 mb-3.5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 dark:text-white text-sm">Tema Tampilan</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pilih tema terang, gelap, atau otomatis sistem</p>
                        </div>
                    </div>
                    <div class="pt-1 flex justify-center">
                        <x-theme-switcher />
                    </div>
                </div>

                <!-- Notification Settings -->
                <a href="{{ route('profile.settings.notifications') }}"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex items-center gap-3.5 hover:shadow-md hover:border-primary-500/30 transition">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">Pengaturan Notifikasi</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola preferensi notifikasi</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <!-- Password Settings -->
                <a href="{{ route('profile.settings.password') }}"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex items-center gap-3.5 hover:shadow-md hover:border-primary-500/30 transition">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">Ubah Kata Sandi</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Perbarui keamanan akun Anda</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

        </div>
    </div>
</x-app-layout>