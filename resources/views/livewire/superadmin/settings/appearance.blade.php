<div class="py-2 space-y-6">
    <!-- Sub-navigation tabs -->
    <x-superadmin-settings-nav active="appearance" />

    <!-- Card: Pengaturan Tema Tampilan -->
    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl p-6 sm:p-8 shadow-xs border border-black/[0.06] dark:border-white/[0.08] flex flex-col items-center justify-center text-center space-y-4 w-full">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">Pilih Mode Tampilan</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sesuaikan tema aplikasi sesuai kenyamanan Anda.</p>
        </div>
        <div class="w-full flex justify-center">
            <x-theme-switcher />
        </div>
    </div>
</div>
