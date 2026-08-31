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
                </div>
            </div>
        </div>

        <!-- 2. Kalibrasi Algoritma Matching & Keadilan (Fairness Engine) -->
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
                <!-- Row 1: Parameter Teknis -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Timeout Penawaran (Detik)</label>
                        <input type="number" wire:model="offer_timeout_seconds" min="15" max="120"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1">Batas waktu respon mitra (15 - 120s).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Maks. Kandidat Top N</label>
                        <input type="number" wire:model="max_dispatch_candidates" min="1" max="30"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1">Batas tawaran sebelum pool terbuka.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Batas Heartbeat Segar (Detik)</label>
                        <input type="number" wire:model="heartbeat_ttl_seconds" min="30" max="300"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1">TTL eligibility matching (30 - 300s).</p>
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
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Prior Rating Netral Mitra Baru</label>
                        <input type="number" step="0.1" wire:model="neutral_rating_prior" min="3.0" max="5.0"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1">Nilai $m$ Bayesian (default 4.5).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Bobot Keyakinan Review ($C$)</label>
                        <input type="number" wire:model="rating_min_votes" min="1" max="50"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1">Jumlah ulasan ekuivalen prior.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Cap Waktu Tunggu Fairness (Menit)</label>
                        <input type="number" wire:model="max_fairness_boost_minutes" min="10" max="240"
                               class="w-full px-3.5 py-2.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl text-xs text-gray-900 dark:text-white">
                        <p class="text-[11px] text-gray-400 mt-1">Batas maksimal skor keadilan (10-240m).</p>
                    </div>
                </div>

                <!-- Row 3: Bobot Formula Skoring Komposit -->
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Bobot Formula Skoring (Total = 1.0 / 100%)</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-900/60 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">1. Jarak (Distance)</span>
                            <input type="number" step="0.05" min="0" max="1" wire:model="weight_distance"
                                   class="w-full mt-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs">
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900/60 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">2. Rating Bayesian</span>
                            <input type="number" step="0.05" min="0" max="1" wire:model="weight_rating"
                                   class="w-full mt-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs">
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900/60 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">3. Keandalan (Reliability)</span>
                            <input type="number" step="0.05" min="0" max="1" wire:model="weight_reliability"
                                   class="w-full mt-1.5 px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs">
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-900/60 p-3 rounded-xl border border-gray-200 dark:border-gray-700">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">4. Keadilan (Fairness)</span>
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
                                    onclick="return confirm('Hapus gambar QRIS yang tersimpan saat ini?')"
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
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                                Upload Gambar QRIS Baru (PNG, JPG, WebP)
                            </label>
                            <input type="file" wire:model="qris_image" accept="image/png,image/jpeg,image/jpg,image/webp"
                                class="w-full text-xs text-gray-500 dark:text-gray-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-950/60 dark:file:text-primary-300 hover:file:bg-primary-100 cursor-pointer bg-white dark:bg-gray-800 rounded-xl border border-gray-300 dark:border-gray-700 p-1" />
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