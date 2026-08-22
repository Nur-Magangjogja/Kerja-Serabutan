<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="max-w-md mx-auto">
        <!-- Header Section -->
        <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>
            
            <div class="relative z-10">
                <div class="flex items-center justify-between text-white">
                    <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <div class="text-center flex-1 min-w-0 px-2">
                        <h1 class="text-base font-bold truncate">Edit Profil</h1>
                        <p class="text-xs text-white/90 truncate mt-0.5">Perbarui informasi profil Anda</p>
                    </div>

                    <div class="w-9 flex-shrink-0"></div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="px-5 pt-5 pb-24">
            <livewire:profile.update-profile-information-form />
        </div>
    </div>
</div>