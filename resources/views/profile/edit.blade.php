<x-app-layout>
    <x-slot name="title">Edit Profile</x-slot>

    <div class="min-h-screen bg-white">
        <div class="max-w-md mx-auto">
            <!-- Header - BRImo Style -->
            <div class="px-5 pt-5 pb-8 relative overflow-hidden" style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
                <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center justify-between text-white mb-3">
                        <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <h1 class="text-lg font-bold">Edit Profil</h1>

                        <div class="w-9"></div>
                    </div>

                    <p class="text-center text-sm text-white/90 mt-2">Perbarui informasi profil Anda</p>
                </div>

                <!-- Curved separator -->
                <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 72" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0,32 C360,72 1080,0 1440,40 L1440,72 L0,72 Z" fill="#ffffff"></path>
                </svg>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-t-3xl -mt-6 px-5 pt-6 pb-24">
                <livewire:profile.update-profile-information-form />
            </div>
        </div>
    </div>
</x-app-layout>