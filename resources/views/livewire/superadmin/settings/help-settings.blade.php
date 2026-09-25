@php
    $title = 'Pengaturan';
    $breadcrumb = 'Super Admin / Pengaturan / Biaya Platform & QRIS';
@endphp

<div class="py-2 max-w-full overflow-x-hidden"
     x-data="{}" 
     x-on:settings-saved.window="
        $nextTick(() => {
            const el = document.getElementById('help-settings-alert') || document.getElementById('settings-form-start');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
     ">
    <!-- Sub-navigation tabs -->
    <x-superadmin-settings-nav active="help" />

    <!-- Notifikasi Sukses / Alert Section -->
    @if(session()->has('message'))
        <div id="help-settings-alert" class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl shadow-xs ring-2 ring-emerald-500/20">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-emerald-800 dark:text-emerald-300 font-semibold text-sm">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    <!-- Settings Form Section -->
    <div id="settings-form-start" class="scroll-mt-8"></div>
    <form wire:submit.prevent="save" class="space-y-8 mb-12">
        <!-- Settings flash hook for JS -->
        <div id="settingsFlash" data-message="{{ session('message') ?? '' }}" style="display:none"></div>

        <!-- 1. Konfigurasi Layanan Bantuan -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700 px-4 sm:px-8 py-5 bg-gray-50/80 dark:bg-gray-900/60 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        Konfigurasi Bantuan Platform
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-0.5">Atur nominal minimal bantuan yang dapat diposting dan persentase komisi platform</p>
                </div>
            </div>

            <div class="p-4 sm:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Nominal Minimal Bantuan (Rp)</label>
                        <input type="number" wire:model="min_help_nominal" placeholder="10000"
                            class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" />
                        @error('min_help_nominal')
                            <div class="flex items-center gap-2 mt-2 text-red-600 dark:text-red-400 text-xs">
                                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Customer tidak bisa membuat permintaan bantuan dengan nominal di bawah nilai ini.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Biaya Layanan / Pajak Platform Tetap (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">Rp</span>
                            <input type="number" wire:model="platform_service_fee" placeholder="2000" min="0" step="500"
                                class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" />
                        </div>
                        @error('platform_service_fee')
                            <div class="flex items-center gap-2 mt-2 text-red-600 dark:text-red-400 text-xs">
                                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            Nominal pajak / biaya layanan flat yang dibebankan kepada <strong>Customer saat membuat permintaan bantuan</strong>. Mitra menerima 100% nominal bantuan penuh tanpa potongan.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Batas Waktu Otomatis Batal / Timeout Pencarian (Jam)</label>
                        <div class="relative">
                            <input type="number" wire:model="help_auto_cancel_hours" placeholder="24" min="1" max="168"
                                class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all" />
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-semibold text-xs">Jam (Default 24 Jam)</span>
                        </div>
                        @error('help_auto_cancel_hours')
                            <div class="flex items-center gap-2 mt-2 text-red-600 dark:text-red-400 text-xs">
                                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            Jika dalam kurun waktu ini pesanan belum diambil oleh mitra manapun, sistem akan <strong>otomatis membatalkan pesanan dan mengembalikan saldo 100%</strong> ke customer.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Konfigurasi Layanan Antar & Jemput (Pickup & Delivery Motor) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700 px-4 sm:px-8 py-5 bg-gray-50/80 dark:bg-gray-900/60 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Konfigurasi Tarif & Kebijakan Antar / Jemput (Motor)
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                        Atur tarif dasar, biaya per KM, batas jarak maksimal 40 KM, dan batas pembatalan pasca-jemput 5 KM.
                    </p>
                </div>
            </div>

            <div class="p-4 sm:p-8 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Tarif Dasar / Base Fare (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">Rp</span>
                            <input type="number" wire:model="pickup_delivery_base_fare" min="1000" step="500"
                                class="w-full pl-10 pr-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Tarif dasar pengantaran/penjemputan.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Tarif Per KM (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">Rp</span>
                            <input type="number" wire:model="pickup_delivery_price_per_km" min="500" step="250"
                                class="w-full pl-10 pr-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Tarif untuk jarak &le; 20 KM.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Tarif Per KM Jarak Jauh (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-xs">Rp</span>
                            <input type="number" wire:model="pickup_delivery_long_distance_price_per_km" min="500" step="250"
                                class="w-full pl-10 pr-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Tarif per KM tier jarak jauh &gt; 20 KM (default: Rp 2.750/KM).</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Batas Jarak Maksimal Antar (KM)</label>
                        <input type="number" step="1" wire:model="pickup_delivery_max_distance_km" min="5" max="100"
                            class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1">Order dengan jarak rute &gt; 40 KM otomatis ditolak demi batas aman motor.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Batas Maksimal Pembatalan Pasca-Jemput (KM Lock)</label>
                        <input type="number" step="0.5" wire:model="pickup_delivery_max_cancellation_distance_after_pickup" min="1" max="20"
                            class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1">Setelah barang/penumpang diambil, pembatalan terkunci otomatis jika jarak dari titik jemput &gt; 5 KM.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Kalibrasi Algoritma Matching & Keadilan (Fairness Engine) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700 px-4 sm:px-8 py-5 bg-gray-50/80 dark:bg-gray-900/60 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Kalibrasi Matching Engine & Keadilan (Fairness)
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                        Konfigurasi parameter pencocokan otomatis, timeout penawaran, prior rating Bayesian, dan bobot distribusi order.
                    </p>
                </div>
            </div>

            <div class="p-4 sm:p-8 space-y-6">
                <!-- Toggle Fitur Cari Order / Antrean Mitra (Global Switch) -->
                <div class="p-4 bg-indigo-50/60 dark:bg-indigo-950/30 rounded-xl border border-indigo-100 dark:border-indigo-900/50 flex items-center justify-between gap-4">
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-indigo-950 dark:text-white">Saklar Global: Fitur Cari Order / Antrean Mitra</h4>
                        <p class="text-[11px] sm:text-xs text-indigo-700/80 dark:text-gray-200 mt-0.5">
                            Bila dinonaktifkan secara global, mitra di wilayah yang mengikuti pengaturan global tidak perlu mengaktifkan mode mencari antrean (order langsung masuk ke daftar bantuan).
                        </p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                        <input type="checkbox" wire:model="matching_seeking_enabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                    </label>
                </div>

                <!-- Sub-Section: Pengaturan Pencocokan Otomatis (Matching) Khusus Layanan Antar & Jemput -->
                <div class="p-4 sm:p-5 bg-emerald-50/60 dark:bg-emerald-950/20 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg flex-shrink-0">
                                🛵
                            </div>
                            <div>
                                <h4 class="text-xs sm:text-sm font-bold text-emerald-950 dark:text-emerald-100 flex items-center gap-2">
                                    <span>Pencocokan Radar Otomatis Layanan Antar & Jemput</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300">
                                        Motor
                                    </span>
                                </h4>
                                <p class="text-[11px] sm:text-xs text-emerald-800/80 dark:text-emerald-300 mt-0.5">
                                    Aktifkan pencocokan otomatis radar sekuensial khusus untuk pesanan antar jemput. Jika dinonaktifkan, pesanan antar jemput langsung masuk ke pool umum.
                                </p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" wire:model="pickup_delivery_matching_enabled" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <!-- Dual Ring Radius Inputs -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-emerald-200/60 dark:border-emerald-800/50">
                        <div>
                            <label class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-1.5 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Ring 1: Radius Prioritas Penjemputan (KM)</span>
                            </label>
                            <div class="relative">
                                <input type="number" step="0.5" wire:model="pickup_delivery_matching_ring1_km" min="1" max="50"
                                       class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-emerald-300 dark:border-emerald-700 rounded-xl text-xs text-gray-900 dark:text-white">
                                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 font-semibold text-xs">KM</span>
                            </div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Jangkauan ring pertama untuk mitra terdekat ke titik penjemputan (default: 5.0 KM).</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-800 dark:text-gray-200 mb-1.5 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span>Ring 2: Radius Fallback Perluasan (KM)</span>
                            </label>
                            <div class="relative">
                                <input type="number" step="0.5" wire:model="pickup_delivery_matching_radius_km" min="1" max="50"
                                       class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-emerald-300 dark:border-emerald-700 rounded-xl text-xs text-gray-900 dark:text-white">
                                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 font-semibold text-xs">KM</span>
                            </div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Perluasan jangkauan jika tidak ada mitra aktif di Ring 1 (default: 10.0 KM).</p>
                        </div>
                    </div>
                </div>

                <!-- Sub-Section: Kustomisasi Pengaturan per Wilayah / Kota -->
                <div class="p-4 sm:p-5 bg-gray-50/70 dark:bg-gray-900/40 rounded-2xl border border-gray-200 dark:border-gray-700/80 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                        <div>
                            <h4 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Pengaturan Kebijakan Wilayah / Kota
                            </h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-300 mt-0.5">
                                Atur mode pencarian order untuk masing-masing kota secara fleksibel tanpa perlu berganti wilayah.
                            </p>
                        </div>
                        <div class="w-full sm:w-64 relative">
                            <input type="text" wire:model.live.debounce.300ms="city_search" placeholder="Cari nama kota / provinsi..."
                                class="w-full pl-9 pr-3 py-1.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                            <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Container Daftar Wilayah (Responsif Mobile & Web Bebas Overflow) -->
                    <div class="rounded-2xl border border-gray-200 dark:border-gray-700/80 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">
                        
                        <!-- 1. Tampilan Desktop & Tablet (Tabular Grid) -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-50/90 dark:bg-gray-900/80 text-[11px] uppercase font-bold text-gray-500 dark:text-gray-200 border-b border-gray-100 dark:border-gray-700">
                                    <tr>
                                        <th scope="col" class="px-5 py-3.5">Wilayah & Administrasi</th>
                                        <th scope="col" class="px-5 py-3.5">Status Kebijakan Efektif</th>
                                        <th scope="col" class="px-5 py-3.5 text-right">Pengaturan Mode</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                    @forelse($regionalCities as $city)
                                        @php
                                            $currentChoice = $city_overrides[$city->id] ?? 'inherit';
                                            $effectiveEnabled = match($currentChoice) {
                                                'enabled'  => true,
                                                'disabled' => false,
                                                default    => (bool) $matching_seeking_enabled,
                                            };
                                        @endphp
                                        <tr class="hover:bg-primary-50/20 dark:hover:bg-gray-700/30 transition-colors duration-150">
                                            <td class="px-5 py-4">
                                                <div class="flex items-center gap-3.5">
                                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-primary-500/10 to-indigo-500/10 dark:from-primary-500/20 dark:to-indigo-500/20 border border-primary-100 dark:border-primary-800/40 flex items-center justify-center flex-shrink-0 text-primary-600 dark:text-primary-400 shadow-2xs">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <div class="font-bold text-gray-900 dark:text-white text-sm leading-tight">{{ $city->name }}</div>
                                                        <div class="flex items-center gap-2 mt-1">
                                                            <span class="text-xs text-gray-500 dark:text-gray-300 font-medium">{{ $city->province }}</span>
                                                            <span class="text-gray-300 dark:text-gray-600">•</span>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 dark:bg-gray-700/80 text-gray-600 dark:text-white">
                                                                {{ $city->districts_count ?? 0 }} kecamatan
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                @if($effectiveEnabled)
                                                    <div class="inline-flex flex-col gap-0.5">
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-white border border-emerald-200 dark:border-emerald-800/80 shadow-2xs">
                                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                                            Mode Antrean Aktif
                                                        </span>
                                                        <span class="text-[10px] text-gray-400 dark:text-gray-300 pl-1 font-medium">
                                                             {{ $currentChoice === 'inherit' ? '↳ Mewarisi Saklar Global' : '↳ Kustom Khusus Wilayah' }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="inline-flex flex-col gap-0.5">
                                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800/70 text-slate-700 dark:text-white border border-slate-200 dark:border-slate-700 shadow-2xs">
                                                            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                                            Langsung ke Daftar Bantuan
                                                        </span>
                                                        <span class="text-[10px] text-gray-400 dark:text-gray-300 pl-1 font-medium">
                                                            {{ $currentChoice === 'inherit' ? '↳ Mewarisi Saklar Global' : '↳ Kustom Khusus Wilayah' }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                                <div class="inline-block relative">
                                                    <select wire:model.live="city_overrides.{{ $city->id }}"
                                                        class="py-2 pl-3.5 pr-8 text-xs font-semibold border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50/80 dark:bg-gray-900 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 shadow-2xs transition-all cursor-pointer">
                                                        <option value="inherit">🌐 Ikuti Pengaturan Global (Bawaan)</option>
                                                        <option value="enabled">⚡ Aktifkan Antrean Mitra</option>
                                                        <option value="disabled">📋 Langsung ke Daftar Bantuan</option>
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-5 py-10 text-center text-gray-400 text-xs">
                                                <div class="flex flex-col items-center justify-center gap-2.5">
                                                    <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                        </svg>
                                                    </div>
                                                    <span class="font-medium text-gray-500 dark:text-gray-300">Tidak ada wilayah yang sesuai dengan pencarian "{{ $city_search }}".</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- 2. Tampilan Khusus Mobile (< 768px - Bebas Overflow) -->
                        <div class="block md:hidden divide-y divide-gray-100 dark:divide-gray-700/60">
                            @forelse($regionalCities as $city)
                                @php
                                    $currentChoice = $city_overrides[$city->id] ?? 'inherit';
                                    $effectiveEnabled = match($currentChoice) {
                                        'enabled'  => true,
                                        'disabled' => false,
                                        default    => (bool) $matching_seeking_enabled,
                                    };
                                @endphp
                                <div class="p-4 space-y-3.5 bg-white dark:bg-gray-800">
                                    <!-- Header Wilayah -->
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-primary-500/10 to-indigo-500/10 dark:from-primary-500/20 dark:to-indigo-500/20 border border-primary-100 dark:border-primary-800/40 flex items-center justify-center flex-shrink-0 text-primary-600 dark:text-primary-400 shadow-2xs mt-0.5">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-bold text-gray-900 dark:text-white text-sm truncate">{{ $city->name }}</div>
                                            <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                                                <span class="text-xs text-gray-500 dark:text-gray-300 font-medium">{{ $city->province }}</span>
                                                <span class="text-gray-300 dark:text-gray-600">•</span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 dark:bg-gray-700/80 text-gray-600 dark:text-white">
                                                    {{ $city->districts_count ?? 0 }} kec
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Status Efektif Badge -->
                                    <div class="flex items-center justify-between gap-2 pt-1 border-t border-gray-50 dark:border-gray-700/40">
                                        <span class="text-[11px] text-gray-500 dark:text-gray-300 font-medium">Status Efektif:</span>
                                        @if($effectiveEnabled)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-white border border-emerald-200 dark:border-emerald-800/80">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                Mode Antrean Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 dark:bg-slate-800/70 text-slate-700 dark:text-white border border-slate-200 dark:border-slate-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                                Langsung Daftar Bantuan
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Pilihan Pengaturan Dropdown (Full Width Mobile) -->
                                    <div class="space-y-1">
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-300">Pilihan Mode Kota</label>
                                        <select wire:model.live="city_overrides.{{ $city->id }}"
                                            class="w-full py-2 pl-3 pr-8 text-xs font-semibold border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50/80 dark:bg-gray-900 text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 shadow-2xs">
                                            <option value="inherit">🌐 Ikuti Pengaturan Global (Bawaan)</option>
                                            <option value="enabled">⚡ Aktifkan Antrean Mitra</option>
                                            <option value="disabled">📋 Langsung ke Daftar Bantuan</option>
                                        </select>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-gray-400 text-xs">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="w-10 h-10 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                        <span>Tidak ada wilayah yang sesuai dengan pencarian "{{ $city_search }}".</span>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <!-- 3. Pagination Footer -->
                        @if($regionalCities->hasPages())
                            <div class="p-3.5 bg-gray-50/70 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700">
                                {{ $regionalCities->links('vendor.pagination.superadmin') }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Row 1: Parameter Teknis -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Timeout Penawaran (Detik)</label>
                        <input type="number" wire:model="offer_timeout_seconds" min="15" max="300"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1">Batas waktu respon mitra (15 - 300 detik, default: 120 detik / 2 menit).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Jumlah yang Ditawarkan ke Mitra (Maksimal)</label>
                        <input type="number" wire:model="max_dispatch_candidates" min="1" max="30"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1">Batas tawaran yang diberikan kepada mitra.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Batas Waktu Mitra Aktif (Detik)</label>
                        <input type="number" wire:model="heartbeat_ttl_seconds" min="30" max="300"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1">Batas waktu mitra aktif di sistem (30 - 300 detik).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Radius Maksimal (KM)</label>
                        <input type="number" step="0.5" wire:model="max_matching_radius_km" min="1" max="100"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1">Jangkauan radius pencocokan.</p>
                    </div>
                </div>


                <!-- Row 2: Prior Bayesian & Fairness Cap -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Boost Rating Netral Mitra Baru</label>
                        <input type="number" step="0.1" wire:model="neutral_rating_prior" min="3.0" max="5.0"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1">Nilai Bantuan (default 4.5).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Bobot Keyakinan Review (Minimal)</label>
                        <input type="number" wire:model="rating_min_votes" min="1" max="50"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1">Jumlah ulasan minimal untuk prior.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Cap Waktu Tunggu Fairness (Menit)</label>
                        <input type="number" wire:model="max_fairness_boost_minutes" min="10" max="240"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1 text-red-600">Batas maksimal waktu tunggu (10-240 menit).</p>
                    </div>
                </div>

                <!-- Row 3: Bobot Formula Skoring Komposit -->
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Bobot Formula Pembagian Order (Total = 1.0 / 100%)</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-900/60 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">1. Boost Jarak Tempuh (Distance)</span>
                            <input type="number" step="0.05" min="0" max="1" wire:model="weight_distance"
                                   class="w-full mt-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs">
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900/60 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">2. Boost Rating (prioritas tertinggi) </span>
                            <input type="number" step="0.05" min="0" max="1" wire:model="weight_rating"
                                   class="w-full mt-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs">
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900/60 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">3. Boost Kehandalan (Reliability)</span>
                            <input type="number" step="0.05" min="0" max="1" wire:model="weight_reliability"
                                   class="w-full mt-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs">
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900/60 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">4. Boost Keadilan Menunggu (Fairness)</span>
                            <input type="number" step="0.05" min="0" max="1" wire:model="weight_fairness"
                                   class="w-full mt-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Pengaturan Metode Pembayaran Top-Up (QRIS Tunggal) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="border-b border-gray-200 dark:border-gray-700 px-4 sm:px-8 py-5 bg-gray-50/80 dark:bg-gray-900/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="p-1.5 bg-blue-50 dark:bg-blue-900/40 text-primary-600 dark:text-primary-400 rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </span>
                        Pengaturan Metode Top-Up (QRIS Tunggal)
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                        Kelola barcode QRIS resmi platform untuk seluruh proses pengisian saldo pengguna.
                    </p>
                </div>
            </div>

            <div class="p-4 sm:p-8 space-y-6">
                <!-- Alert Peringatan jika QRIS Belum Diunggah -->
                @if(empty($existing_qris_image) && empty($qris_image))
                    <div class="p-4 bg-amber-50 dark:bg-amber-950/40 border-l-4 border-amber-500 rounded-r-2xl flex items-start gap-3.5 shadow-xs">
                        <div class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="text-xs text-amber-900 dark:text-amber-200 space-y-1">
                            <p class="font-bold text-sm text-amber-950 dark:text-amber-100">Perhatian: Barcode QRIS Belum Diunggah!</p>
                            <p>Saat ini barcode QRIS platform masih kosong. Harap unggah gambar QRIS di bawah ini agar customer dapat melakukan pengisian saldo (top-up).</p>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <!-- Left: QRIS Image Preview & Upload Box (5 cols) -->
                    <div class="lg:col-span-5 bg-gray-50/90 dark:bg-gray-900/60 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span>📱</span> Gambar Barcode QRIS
                            </h3>
                            @if($existing_qris_image)
                                <button type="button" wire:click="removeQrisImage"
                                    wire:confirm="Hapus gambar QRIS yang tersimpan saat ini?"
                                    class="text-[11px] font-semibold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer">
                                    Hapus Gambar QRIS
                                </button>
                            @endif
                        </div>

                        <!-- Image Display Box -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-200 dark:border-gray-700 text-center shadow-xs">
                            @if($qris_image)
                                <div class="space-y-2">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200">
                                        Preview Gambar Baru (Belum Disimpan)
                                    </span>
                                    <div class="w-56 h-56 mx-auto rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 p-2 bg-white flex items-center justify-center">
                                        <img src="{{ $qris_image->temporaryUrl() }}" alt="Preview QRIS" class="max-w-full max-h-full object-contain">
                                    </div>
                                </div>
                            @elseif($existing_qris_image)
                                @php
                                    $qrisUrl = str_starts_with($existing_qris_image, 'images/') 
                                        ? asset($existing_qris_image) 
                                        : asset('storage/' . $existing_qris_image);
                                @endphp
                                <div class="space-y-2">
                                    <div class="w-56 h-56 mx-auto rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 p-2 bg-white flex items-center justify-center">
                                        <img src="{{ $qrisUrl }}" alt="QRIS Aktif" class="max-w-full max-h-full object-contain">
                                    </div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                        {{ $qris_merchant_name ?: 'SayaBantu QRIS' }}
                                    </p>
                                </div>
                            @else
                                <div class="w-56 h-56 mx-auto border-2 border-dashed border-amber-300 dark:border-amber-700/60 bg-amber-50/40 dark:bg-amber-950/20 rounded-xl flex flex-col items-center justify-center text-amber-700 dark:text-amber-400 p-4 text-center">
                                    <svg class="w-10 h-10 mb-2 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                    <span class="text-xs font-bold">QRIS Belum Diunggah</span>
                                    <span class="text-[10px] text-amber-600/80 dark:text-amber-400/80 mt-1">Pilih file gambar di bawah untuk mengunggah</span>
                                </div>
                            @endif
                        </div>

                        <!-- Upload Control -->
                        <div x-data="{ fileName: 'Belum ada file dipilih' }">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                Upload Gambar QRIS Baru (PNG, JPG, WebP)
                            </label>
                            <div class="flex items-center gap-2 w-full bg-white dark:bg-gray-800 rounded-xl border border-gray-300 dark:border-gray-700 p-1 cursor-pointer"
                                 @click="$refs.qrisInput.click()">
                                <span class="shrink-0 py-2 px-4 rounded-xl text-xs font-bold bg-primary-50 text-primary-700 dark:bg-primary-950/60 dark:text-primary-300 hover:bg-primary-100 transition-colors">
                                    Pilih File
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="fileName"></span>
                            </div>
                            <input type="file" x-ref="qrisInput" wire:model="qris_image"
                                   accept="image/png,image/jpeg,image/jpg,image/webp"
                                   class="hidden"
                                   @change="fileName = $event.target.files[0] ? $event.target.files[0].name : 'Belum ada file dipilih'" />
                            @error('qris_image')
                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                            <div wire:loading wire:target="qris_image" class="text-xs text-primary-600 dark:text-primary-400 mt-1">
                                Mengunggah dan memproses gambar preview...
                            </div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1.5">
                                Rekomendasi: Gunakan gambar QRIS berbentuk persegi (1:1) dengan resolusi minimal 500x500 piksel agar mudah di-scan oleh kamera smartphone.
                            </p>
                        </div>
                    </div>

                    <!-- Right: QRIS Configuration Details (7 cols) -->
                    <div class="lg:col-span-7 space-y-4">
                        <!-- Merchant Name -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-1.5">
                                Nama Akun / Merchant QRIS <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="qris_merchant_name" placeholder="Contoh: PT SayaBantu Indonesia"
                                class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition" />
                            @error('qris_merchant_name')
                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Nama resmi penerima pembayaran yang tertera pada aplikasi perbankan saat customer melakukan scan.
                            </p>
                        </div>

                        <!-- NMID -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-1.5">
                                Nomor NMID QRIS
                            </label>
                            <input type="text" wire:model="qris_nmid" placeholder="Contoh: ID1020030040050"
                                class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition" />
                            @error('qris_nmid')
                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Nomor Identifikasi Merchant Nasional yang tercetak di bawah barcode QRIS.
                            </p>
                        </div>

                        <!-- Petunjuk Pembayaran -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-1.5">
                                Petunjuk Pembayaran untuk Customer
                            </label>
                            <textarea wire:model="qris_instructions" rows="3" placeholder="Tulis instruksi transfer QRIS..."
                                class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"></textarea>
                            @error('qris_instructions')
                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Teks panduan yang akan dibaca oleh customer pada halaman pembayaran top-up saldo.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="pt-6 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end">
                    <button type="submit" 
                        @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-sm hover:shadow-md active:scale-[0.98] disabled:opacity-50 cursor-pointer w-full sm:w-auto">
                        <svg wire:loading.remove wire:target="save" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Simpan Perubahan Pengaturan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Saved Confirmation Modal -->
    <div id="settingsSavedModal" class="fixed inset-0 z-50 flex items-center justify-center hidden transition-opacity duration-300">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 dark:bg-black/70 backdrop-blur-xs transition-opacity"></div>
        
        <!-- Modal Content -->
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 max-w-sm w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="settingsSavedContent">
            <!-- Close Button -->
            <button id="settingsSavedClose" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200 transition-colors p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            
            <!-- Content -->
            <div class="p-6 text-center">
                <!-- Success Icon -->
                <div class="mx-auto w-16 h-16 bg-emerald-100 dark:bg-emerald-950/60 rounded-2xl flex items-center justify-center mb-4 animate-bounce-once shadow-xs">
                    <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                
                <!-- Title -->
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2">Berhasil Disimpan!</h3>
                
                <!-- Message -->
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed" id="settingsSavedMessage">
                    Perubahan pengaturan dan QRIS telah berhasil disimpan ke sistem.
                </p>
            </div>
        </div>
    </div>

    <style>
        @keyframes bounce-once {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }
        .animate-bounce-once {
            animation: bounce-once 0.5s ease-in-out;
        }
        #settingsSavedModal:not(.hidden) #settingsSavedContent {
            transform: scale(1);
            opacity: 1;
        }
    </style>

    <script>
        (function() {
            let localModalTimeout = null;
            
            function showSettingsSaved(message) {
                const modal = document.getElementById('settingsSavedModal');
                const msgEl = document.getElementById('settingsSavedMessage');
                if (!modal) return;
                if (!message) return;
                
                if (msgEl) msgEl.textContent = message;
                
                if (localModalTimeout) {
                    clearTimeout(localModalTimeout);
                    localModalTimeout = null;
                }
                
                modal.classList.add('hidden');
                
                setTimeout(() => {
                    modal.classList.remove('hidden');
                    localModalTimeout = setTimeout(() => {
                        modal.classList.add('hidden');
                        localModalTimeout = null;
                    }, 3000);
                }, 50);
            }

            if (window.Livewire && typeof window.Livewire.on === 'function') {
                window.Livewire.on('settingsSaved', (event) => {
                    const message = event[0]?.message || event.message || 'Pengaturan berhasil disimpan';
                    showSettingsSaved(message);
                });
            } else {
                document.addEventListener('livewire:init', () => {
                    Livewire.on('settingsSaved', (event) => {
                        const message = event[0]?.message || event.message || 'Pengaturan berhasil disimpan';
                        showSettingsSaved(message);
                    });
                });
            }

            document.addEventListener('click', function(e) {
                const closeBtn = e.target.closest('#settingsSavedClose');
                if (closeBtn) {
                    if (localModalTimeout) {
                        clearTimeout(localModalTimeout);
                        localModalTimeout = null;
                    }
                    const modal = document.getElementById('settingsSavedModal');
                    if (modal) modal.classList.add('hidden');
                }
            });
        })();
    </script>
</div>