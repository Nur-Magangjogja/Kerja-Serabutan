<x-mitra-layout>
    <x-slot name="title">Pengaturan Notifikasi</x-slot>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 pb-24">
        <!-- Header Section -->
        <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

            <div class="relative z-10">
                <div class="relative flex items-center justify-center min-h-[40px] text-white">
                    <a href="{{ route('mitra.settings') }}" aria-label="Kembali" class="absolute left-0 top-1/2 -translate-y-1/2 z-20 p-2 hover:bg-white/20 rounded-xl transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>

                    <div class="text-center w-full min-w-0 px-12">
                        <h1 class="text-base font-bold truncate">Pengaturan Notifikasi</h1>
                        <p class="text-xs text-white/90 truncate mt-0.5">Kelola preferensi pemberitahuan Anda</p>
                    </div>

                    <div class="absolute right-0 top-1/2 -translate-y-1/2 z-20 flex items-center">
                        <x-mitra.notification-icon />
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Settings Content -->
        <div class="px-5 pt-5 relative z-10">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <livewire:profile.notification-settings />
            </div>
        </div>
    </div>
</x-mitra-layout>
