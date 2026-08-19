<x-mitra-layout>
    <x-slot name="title">Pengaturan</x-slot>

    <div class="min-h-screen bg-gray-50 pb-24">
        <div class="max-w-md mx-auto">
            <!-- Header - Mitra style -->
            <div class="px-5 pt-5 pb-32 relative overflow-hidden" style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
                <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16"></div>

                <div class="relative z-10">
                    <div class="flex items-center justify-between text-white mb-8">
                        <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <h1 class="text-lg font-bold">Pengaturan</h1>

                        <a href="{{ route('mitra.settings.notifications') }}" class="p-2 hover:bg-white/20 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </a>
                    </div>

                    <p class="text-sm text-white/80">Kelola preferensi dan keamanan akun Anda</p>
                </div>

                <!-- Curved separator -->
                <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 72" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0,32 C360,72 1080,0 1440,40 L1440,72 L0,72 Z" fill="#ffffff"></path>
                </svg>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="max-w-md mx-auto px-6 -mt-16 relative z-10">
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
                <a href="{{ route('mitra.settings.notifications') }}"
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
                <a href="{{ route('mitra.settings.password') }}"
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

                <!-- Delete Account -->
                <button onclick="confirmDelete()"
                    class="w-full bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex items-center gap-3.5 hover:shadow-md hover:border-red-200 dark:hover:border-red-800/60 transition cursor-pointer">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div class="flex-1 text-left">
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">Hapus Akun</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Hapus akun secara permanen</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Account Modal -->
    <div id="deleteModal"
        class="hidden fixed inset-0 bg-gray-900/70 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <!-- Icon -->
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h2 class="text-xl font-bold text-gray-900 mb-2 text-center">Hapus Akun</h2>
            <p class="text-sm text-gray-600 mb-4 text-center">Apakah Anda yakin ingin menghapus akun?</p>

            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                <p class="text-xs text-red-800 text-center leading-relaxed">
                    Dengan menghapus akun, Anda memahami bahwa tindakan ini akan menghapus secara permanen akun dan semua data terkait. Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <form action="{{ route('profile.delete') }}" method="POST" class="space-y-3">
                @csrf
                @method('DELETE')

                <button type="submit"
                    class="w-full bg-gradient-to-r from-red-500 to-red-600 text-white font-bold py-3 rounded-xl hover:shadow-lg transition">
                    Ya, Hapus Akun
                </button>

                <button type="button" onclick="closeDeleteModal()"
                    class="w-full bg-gray-100 text-gray-700 font-bold py-3 rounded-xl hover:bg-gray-200 transition">
                    Batal
                </button>
            </form>
        </div>
    </div>

    <script>
        function confirmDelete() {
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
    </script>
</x-mitra-layout>
