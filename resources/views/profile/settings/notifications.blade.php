<x-app-layout>
    <x-slot name="title">Pengaturan Notifikasi</x-slot>

    <div class="min-h-screen bg-gray-50 pb-24">
        <!-- BRImo Header -->
        <div class="relative bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] pb-24 overflow-hidden">
            <!-- Decorative Circles -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/10 rounded-full -ml-12 -mb-12"></div>
            
            <div class="relative max-w-md mx-auto px-6 pt-4 pb-6">
                <div class="flex items-center justify-between mb-6">
                    <a href="{{ route('profile.settings') }}" class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white/30 transition">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <a href="{{ route('customer.notifications.index') }}" class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white/30 transition">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </a>
                </div>
                
                <h1 class="text-2xl font-bold text-white mb-2">Pengaturan Notifikasi</h1>
                <p class="text-sm text-white/90">Kelola preferensi pemberitahuan Anda</p>
            </div>

            <!-- Curved Separator -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                    <path d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64L1440,120L1360,120C1280,120,1120,120,960,120C800,120,640,120,480,120C320,120,160,120,80,120L0,120Z" fill="#F9FAFB"/>
                </svg>
            </div>
        </div>

        <!-- Notification Settings Content -->
        <div class="max-w-md mx-auto px-6 -mt-16 relative z-10">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <livewire:profile.notification-settings />
            </div>
        </div>
    </div>
</x-app-layout>