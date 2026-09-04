<x-app-layout>
    <x-slot name="title">Edit Profile</x-slot>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <div class="max-w-md mx-auto">
            <!-- Header Section -->
            <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
                <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>
                
                <div class="relative z-10 text-white text-center">
                    <h1 class="text-base font-bold truncate">Edit Profil</h1>
                    <p class="text-xs text-white/90 truncate mt-0.5">Perbarui informasi profil Anda</p>
                </div>
            </div>

            <!-- Form -->
            <div class="px-5 pt-5 pb-24">
                <livewire:profile.update-profile-information-form />
            </div>
        </div>
    </div>
</x-app-layout>