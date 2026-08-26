<div class="py-2 space-y-6"
     x-data="{}"
     x-on:settings-saved.window="
        $nextTick(() => {
            const el = document.getElementById('admin-help-alert') || document.getElementById('alert-message');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
     ">
    <!-- Sub-navigation tabs -->
    <x-admin-settings-nav active="help" />

    @if (session()->has('message'))
        <div id="admin-help-alert" class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl shadow-xs ring-2 ring-emerald-500/20 flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ session('message') }}</span>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 md:p-8">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Pengaturan Batasan Bantuan</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola nominal minimal bantuan dan biaya administrasi platform.</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                Admin Panel
            </span>
        </div>

        <form wire:submit.prevent="save" class="space-y-6">
            <div>
                <label for="min_help_nominal" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Minimal Nominal Bantuan (Rp)
                </label>
                <div class="relative rounded-xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-gray-500 dark:text-gray-400 sm:text-sm font-medium">Rp</span>
                    </div>
                    <input type="number" wire:model="min_help_nominal" id="min_help_nominal"
                        class="block w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white text-sm font-medium"
                        placeholder="10000" min="0" step="100">
                </div>
                @error('min_help_nominal') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Nominal terendah yang dapat diminta customer saat membuat bantuan baru.</p>
            </div>

            <div>
                <label for="platform_service_fee" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Biaya Layanan / Pajak Platform Tetap (Rp)
                </label>
                <div class="relative rounded-xl shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="text-gray-500 dark:text-gray-400 sm:text-sm font-medium">Rp</span>
                    </div>
                    <input type="number" wire:model="platform_service_fee" id="platform_service_fee"
                        class="block w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-gray-900 dark:text-white text-sm font-medium"
                        placeholder="2000" min="0" step="500">
                </div>
                @error('platform_service_fee') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Nominal pajak/biaya flat yang dibebankan kepada Customer saat membuat permintaan bantuan.</p>
            </div>

            <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                <button type="submit"
                    @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                    class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition duration-150 ease-in-out cursor-pointer">
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
