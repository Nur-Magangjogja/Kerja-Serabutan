<div class="space-y-6">
    @if(session()->has('message'))
        <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-xs font-semibold text-emerald-700 dark:text-emerald-300 flex items-center gap-2 animate-fade-in shadow-xs">
            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    <!-- Section 1: Kategori Pemberitahuan -->
    <div>
        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 px-1">
            Kategori Pemberitahuan
        </h3>
        <div class="bg-gray-50 dark:bg-gray-800/60 rounded-2xl p-2 border border-gray-100 dark:border-gray-700/60 divide-y divide-gray-100 dark:divide-gray-700/60">
            <!-- Update Bantuan -->
            <div class="flex items-center justify-between p-3">
                <div class="flex items-start gap-3 pr-3">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-900 dark:text-white text-xs block">Update Status Bantuan</span>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-snug mt-0.5">Pemberitahuan perubahan status pesanan, pencarian mitra, & konfirmasi selesai.</p>
                    </div>
                </div>
                <button wire:click="updateSetting('help_updates')" wire:loading.attr="disabled"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition-colors duration-200 cursor-pointer {{ $help_updates ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600' }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform duration-200 {{ $help_updates ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </button>
            </div>

            <!-- Pesan Chat -->
            <div class="flex items-center justify-between p-3">
                <div class="flex items-start gap-3 pr-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-900 dark:text-white text-xs block">Pesan & Obrolan</span>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-snug mt-0.5">Pemberitahuan pesan chat masuk dari pihak yang sedang bertransaksi.</p>
                    </div>
                </div>
                <button wire:click="updateSetting('chat_messages')" wire:loading.attr="disabled"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition-colors duration-200 cursor-pointer {{ $chat_messages ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600' }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform duration-200 {{ $chat_messages ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </button>
            </div>

            <!-- Transaksi & Saldo -->
            <div class="flex items-center justify-between p-3">
                <div class="flex items-start gap-3 pr-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-900 dark:text-white text-xs block">Transaksi & Dompet</span>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-snug mt-0.5">Pemberitahuan top-up, penarikan saldo, dan mutasi pembayaran.</p>
                    </div>
                </div>
                <button wire:click="updateSetting('transactions')" wire:loading.attr="disabled"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition-colors duration-200 cursor-pointer {{ $transactions ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600' }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform duration-200 {{ $transactions ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </button>
            </div>

            <!-- Suara Notifikasi -->
            <div class="flex items-center justify-between p-3">
                <div class="flex items-start gap-3 pr-3">
                    <div class="w-8 h-8 rounded-xl bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-900 dark:text-white text-xs block">Suara Notifikasi</span>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-snug mt-0.5">Mainkan nada notifikasi ketika ada pembaruan baru masuk.</p>
                    </div>
                </div>
                <button wire:click="updateSetting('sound_enabled')" wire:loading.attr="disabled"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition-colors duration-200 cursor-pointer {{ $sound_enabled ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600' }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform duration-200 {{ $sound_enabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Section 2: Aturan Penyimpanan & Pembersihan -->
    <div>
        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 px-1">
            Penyimpanan & Riwayat Notifikasi
        </h3>
        <div class="bg-gray-50 dark:bg-gray-800/60 rounded-2xl p-2 border border-gray-100 dark:border-gray-700/60 divide-y divide-gray-100 dark:divide-gray-700/60">
            <!-- Auto Cleanup Read -->
            <div class="flex items-center justify-between p-3">
                <div class="flex items-start gap-3 pr-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-900 dark:text-white text-xs block">Pembersihan Otomatis Notifikasi Dibaca</span>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-snug mt-0.5">
                            @if($auto_cleanup_read)
                                <span class="text-rose-600 dark:text-rose-400 font-medium">Aktif:</span> Notifikasi yang telah dibaca akan otomatis dibersihkan saat keluar dari halaman notifikasi.
                            @else
                                <span class="text-emerald-600 dark:text-emerald-400 font-medium">Nonaktif (Disarankan):</span> Notifikasi tetap tersimpan di riwayat hingga Anda menghapusnya secara manual.
                            @endif
                        </p>
                    </div>
                </div>
                <button wire:click="updateSetting('auto_cleanup_read')" wire:loading.attr="disabled"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition-colors duration-200 cursor-pointer {{ $auto_cleanup_read ? 'bg-rose-500' : 'bg-gray-300 dark:bg-gray-600' }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform duration-200 {{ $auto_cleanup_read ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </button>
            </div>

            <!-- Auto Mark as Read -->
            <div class="flex items-center justify-between p-3">
                <div class="flex items-start gap-3 pr-3">
                    <div class="w-8 h-8 rounded-xl bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-900 dark:text-white text-xs block">Tandai Otomatis Sudah Dibaca</span>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-snug mt-0.5">
                            @if($auto_mark_read)
                                <span class="text-primary-600 dark:text-primary-400 font-medium">Aktif:</span> Seluruh notifikasi langsung ditandai sudah dibaca saat membuka halaman notifikasi.
                            @else
                                <span class="text-gray-600 dark:text-gray-400 font-medium">Manual:</span> Notifikasi belum dibaca tetap memiliki tanda biru sampai Anda membukanya atau menandainya.
                            @endif
                        </p>
                    </div>
                </div>
                <button wire:click="updateSetting('auto_mark_read')" wire:loading.attr="disabled"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition-colors duration-200 cursor-pointer {{ $auto_mark_read ? 'bg-primary-600' : 'bg-gray-300 dark:bg-gray-600' }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform duration-200 {{ $auto_mark_read ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Section 3: Pengelolaan Manual Riwayat Notifikasi -->
    <div>
        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3 px-1">
            Pengelolaan Riwayat Notifikasi
        </h3>
        
        <!-- Summary Counters -->
        <div class="grid grid-cols-3 gap-2 mb-3">
            <div class="bg-gray-50 dark:bg-gray-800/60 p-3 rounded-xl border border-gray-100 dark:border-gray-700/60 text-center">
                <span class="text-lg font-bold text-gray-900 dark:text-white block">{{ $totalCount }}</span>
                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">Total</span>
            </div>
            <div class="bg-blue-50/60 dark:bg-blue-900/20 p-3 rounded-xl border border-blue-100 dark:border-blue-800/40 text-center">
                <span class="text-lg font-bold text-blue-600 dark:text-blue-400 block">{{ $unreadCount }}</span>
                <span class="text-[10px] text-blue-600 dark:text-blue-400 font-medium">Belum Dibaca</span>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800/60 p-3 rounded-xl border border-gray-100 dark:border-gray-700/60 text-center">
                <span class="text-lg font-bold text-gray-600 dark:text-gray-400 block">{{ $readCount }}</span>
                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">Sudah Dibaca</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-2">
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" wire:loading.attr="disabled"
                    class="w-full py-2.5 px-3 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/60 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-200 flex items-center justify-center gap-2 transition cursor-pointer shadow-2xs">
                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Tandai Semua Sudah Dibaca</span>
                </button>
            @endif

            @if($readCount > 0)
                <button wire:click="deleteReadNotifications" wire:confirm="Hapus seluruh notifikasi yang sudah dibaca?" wire:loading.attr="disabled"
                    class="w-full py-2.5 px-3 bg-white dark:bg-gray-800 hover:bg-rose-50 dark:hover:bg-rose-950/30 border border-gray-200 dark:border-gray-700 hover:border-rose-200 dark:hover:border-rose-800/60 rounded-xl text-xs font-semibold text-rose-600 dark:text-rose-400 flex items-center justify-center gap-2 transition cursor-pointer shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Bersihkan Notifikasi yang Sudah Dibaca</span>
                </button>
            @endif

            @if($totalCount > 0)
                <button wire:click="deleteAllNotifications" wire:confirm="Apakah Anda yakin ingin menghapus SELURUH riwayat notifikasi?" wire:loading.attr="disabled"
                    class="w-full py-2.5 px-3 bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-950/30 border border-gray-200 dark:border-gray-700 hover:border-red-200 dark:hover:border-red-800/60 rounded-xl text-xs font-semibold text-red-600 dark:text-red-400 flex items-center justify-center gap-2 transition cursor-pointer shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Hapus Seluruh Notifikasi</span>
                </button>
            @endif
        </div>
    </div>
</div>