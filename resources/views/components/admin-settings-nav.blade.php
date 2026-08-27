@props(['active' => 'appearance'])

<div class="mb-8">
    <!-- Header Title -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengaturan</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola preferensi tema dan tampilan antarmuka panel admin.</p>
    </div>

    <!-- Sub-navigation Tabs -->
    <div class="flex items-center gap-2 p-1.5 bg-gray-100 dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 max-w-fit shadow-sm">
        <!-- Tab: Tema Tampilan -->
        <a href="{{ route('admin.settings.appearance') }}" wire:navigate
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-white dark:bg-gray-700 text-primary-600 dark:text-primary-400 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
            </svg>
            <span>Tema Tampilan</span>
        </a>
    </div>
</div>
