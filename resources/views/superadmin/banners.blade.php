@php
    $title = 'Pengaturan';
    $breadcrumb = 'Super Admin / Pengaturan';
@endphp

<div>
    <div class="py-2">
        <!-- Sub-navigation tabs -->
        <x-superadmin-settings-nav />
    </div>

    <!-- Notifications Modals -->
    @if (session('message'))
        <div id="success-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full mx-4 border border-gray-200 dark:border-gray-700 transform transition-all">
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/40 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Berhasil!</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ session('message') }}</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button onclick="document.getElementById('success-modal').remove()"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (session('info'))
        <div id="info-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full mx-4 border border-gray-200 dark:border-gray-700 transform transition-all">
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/40 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Info</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ session('info') }}</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button onclick="document.getElementById('info-modal').remove()"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div id="error-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full mx-4 border border-gray-200 dark:border-gray-700 transform transition-all">
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Terjadi Kesalahan</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ session('error') }}</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button onclick="document.getElementById('error-modal').remove()"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Unified Banner Management & Preview Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-8">
        <!-- Header Card: Kelola Banner -->
        <div class="border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 lg:px-8 py-5 bg-gray-50/80 dark:bg-gray-900/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Kelola Banner
                </h2>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Kelola banner yang tampil di Dashboard Customer dan Dashboard Mitra. Maksimal <strong>5 banner</strong> per kategori.
                </p>
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-primary-50 dark:bg-primary-950/40 border border-primary-200 dark:border-primary-800 text-xs font-semibold text-primary-700 dark:text-primary-300 flex-shrink-0 self-start sm:self-auto">
                <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                2 Kategori Banner
            </div>
        </div>

        <!-- Main Card Body -->
        <div class="p-4 sm:p-6 lg:p-8 space-y-8 bg-white dark:bg-gray-800">
            <!-- 2-Column Banner Management Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- 1. Customer Banner Section -->
@php $customerMax = 5; $customerUsed = count($customerBanners); $customerRemain = max(0, $customerMax - $customerUsed); @endphp
                <div class="bg-gray-50/80 dark:bg-gray-900/60 rounded-2xl p-5 sm:p-6 border border-gray-200 dark:border-gray-700 flex flex-col justify-between min-w-0">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-3 h-3 rounded-full bg-blue-500 flex-shrink-0"></span>
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white truncate">Banner Customer</h3>
                            </div>
                            <span class="flex-shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $customerRemain === 0 ? 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300' : 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' }} border {{ $customerRemain === 0 ? 'border-red-200 dark:border-red-800' : 'border-blue-200 dark:border-blue-800' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $customerRemain === 0 ? 'bg-red-500' : 'bg-blue-500' }}"></span>
                                {{ $customerUsed }}/{{ $customerMax }}
                                @if($customerRemain > 0) · Sisa {{ $customerRemain }} slot @else · Penuh @endif
                            </span>
                        </div>

                        <div class="mb-4">
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                @forelse($customerBanners as $i => $b)
                                    <div class="relative rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm aspect-[16/9] bg-gray-100 dark:bg-gray-700">
                                        <img src="{{ asset('storage/' . $b) }}" alt="banner-{{ $i }}" class="w-full h-full object-cover">
                                        <button wire:click="removeCustomer({{ $i }})" type="button"
                                            class="absolute top-1.5 right-1.5 bg-white/90 dark:bg-gray-800/90 rounded-full p-1 hover:bg-red-50 dark:hover:bg-red-950/50 shadow"
                                            title="Hapus">
                                            <svg class="w-3.5 h-3.5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                @empty
                                    <div class="col-span-full text-xs text-gray-500 dark:text-gray-400 py-3 text-center bg-white dark:bg-gray-800 rounded-lg border border-dashed border-gray-200 dark:border-gray-700">Belum ada banner customer.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Unggah Banner Customer Baru</label>

                        <!-- Drag & Drop Zone Customer -->
                        <div id="customer-dropzone" data-max-slots="{{ $customerRemain }}"
                            class="relative border-2 border-dashed {{ $customerRemain === 0 ? 'border-red-300 dark:border-red-800 bg-red-50/40 dark:bg-red-950/20 cursor-not-allowed opacity-70' : 'border-gray-300 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400 bg-gray-50/60 dark:bg-gray-800/80 hover:bg-blue-50/20 dark:hover:bg-gray-750 cursor-pointer' }} rounded-2xl p-5 text-center transition-all duration-200 shadow-xs group">
                            <input type="file" id="customer-file-input" wire:model="customerUploads" accept="image/png, image/jpeg, image/jpg, .png, .jpg, .jpeg" multiple class="hidden" {{ $customerRemain === 0 ? 'disabled' : '' }} />
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <div class="w-11 h-11 rounded-2xl {{ $customerRemain === 0 ? 'bg-red-50 dark:bg-red-900/20 text-red-400' : 'bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 group-hover:scale-110' }} flex items-center justify-center transition-transform duration-200 shadow-2xs">
                                    @if($customerRemain === 0)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-700 dark:text-gray-200 font-medium">
                                    @if($customerRemain === 0)
                                        <span class="font-bold text-red-500 dark:text-red-400">Slot penuh</span> — hapus banner untuk menambah
                                    @else
                                        <span class="font-bold text-blue-600 dark:text-blue-400 hover:underline">Pilih Gambar</span> atau seret ke sini
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 text-[10px] text-gray-400 dark:text-gray-400">
                                    <span class="px-1.5 py-0.5 rounded bg-white dark:bg-gray-700 font-mono text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600">PNG / JPG</span>
                                    <span>Maks 5MB • Rasio 16:9 • {{ $customerRemain > 0 ? 'Maks ' . $customerRemain . ' file' : 'Tidak bisa unggah' }}</span>
                                </div>
                            </div>
                        </div>

                        @error('customerUploads.*') <div class="text-xs text-red-600 dark:text-red-400">{{ $message }}</div> @enderror

                        <!-- Preview Upload -->
                        <div wire:ignore id="customer-preview-uploads" class="hidden space-y-2.5 mt-2"></div>

                        <div class="flex items-center justify-end pt-1">
                            <button wire:ignore id="customer-clear-preview"
                                class="hidden px-3 py-1.5 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl text-xs font-medium transition-colors">
                                ✕ Batal Pilihan
                            </button>
                        </div>
                    </div>
                </div>

@php $mitraMax = 5; $mitraUsed = count($mitraBanners); $mitraRemain = max(0, $mitraMax - $mitraUsed); @endphp
                <!-- 2. Mitra Banner Section -->
                <div class="bg-gray-50/80 dark:bg-gray-900/60 rounded-2xl p-5 sm:p-6 border border-gray-200 dark:border-gray-700 flex flex-col justify-between min-w-0">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white truncate">Banner Mitra</h3>
                            </div>
                            <span class="flex-shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $mitraRemain === 0 ? 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 border-red-200 dark:border-red-800' : 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' }} border">
                                <span class="w-1.5 h-1.5 rounded-full {{ $mitraRemain === 0 ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                                {{ $mitraUsed }}/{{ $mitraMax }}
                                @if($mitraRemain > 0) · Sisa {{ $mitraRemain }} slot @else · Penuh @endif
                            </span>
                        </div>

                        <div class="mb-4">
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                @forelse($mitraBanners as $i => $b)
                                    <div class="relative rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm aspect-[16/9] bg-gray-100 dark:bg-gray-700">
                                        <img src="{{ asset('storage/' . $b) }}" alt="banner-mitra-{{ $i }}" class="w-full h-full object-cover">
                                        <button wire:click="removeMitra({{ $i }})" type="button"
                                            class="absolute top-1.5 right-1.5 bg-white/90 dark:bg-gray-800/90 rounded-full p-1 hover:bg-red-50 dark:hover:bg-red-950/50 shadow"
                                            title="Hapus">
                                            <svg class="w-3.5 h-3.5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                @empty
                                    <div class="col-span-full text-xs text-gray-500 dark:text-gray-400 py-3 text-center bg-white dark:bg-gray-800 rounded-lg border border-dashed border-gray-200 dark:border-gray-700">Belum ada banner mitra.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">Unggah Banner Mitra Baru</label>

                        <!-- Drag & Drop Zone Mitra -->
                        <div id="mitra-dropzone" data-max-slots="{{ $mitraRemain }}"
                            class="relative border-2 border-dashed {{ $mitraRemain === 0 ? 'border-red-300 dark:border-red-800 bg-red-50/40 dark:bg-red-950/20 cursor-not-allowed opacity-70' : 'border-gray-300 dark:border-gray-600 hover:border-emerald-500 dark:hover:border-emerald-400 bg-gray-50/60 dark:bg-gray-800/80 hover:bg-emerald-50/20 dark:hover:bg-gray-750 cursor-pointer' }} rounded-2xl p-5 text-center transition-all duration-200 shadow-xs group">
                            <input type="file" id="mitra-file-input" wire:model="mitraUploads" accept="image/png, image/jpeg, image/jpg, .png, .jpg, .jpeg" multiple class="hidden" {{ $mitraRemain === 0 ? 'disabled' : '' }} />
                            <div class="flex flex-col items-center justify-center space-y-2">
                                <div class="w-11 h-11 rounded-2xl {{ $mitraRemain === 0 ? 'bg-red-50 dark:bg-red-900/20 text-red-400' : 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 group-hover:scale-110' }} flex items-center justify-center transition-transform duration-200 shadow-2xs">
                                    @if($mitraRemain === 0)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-700 dark:text-gray-200 font-medium">
                                    @if($mitraRemain === 0)
                                        <span class="font-bold text-red-500 dark:text-red-400">Slot penuh</span> — hapus banner untuk menambah
                                    @else
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline">Pilih Gambar</span> atau seret ke sini
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 text-[10px] text-gray-400 dark:text-gray-400">
                                    <span class="px-1.5 py-0.5 rounded bg-white dark:bg-gray-700 font-mono text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600">PNG / JPG</span>
                                    <span>Maks 5MB • Rasio 16:9 • {{ $mitraRemain > 0 ? 'Maks ' . $mitraRemain . ' file' : 'Tidak bisa unggah' }}</span>
                                </div>
                            </div>
                        </div>

                        @error('mitraUploads.*') <div class="text-xs text-red-600 dark:text-red-400">{{ $message }}</div> @enderror

                        <!-- Preview Upload -->
                        <div wire:ignore id="mitra-preview-uploads" class="hidden space-y-2.5 mt-2"></div>

                        <div class="flex items-center justify-end pt-1">
                            <button wire:ignore id="mitra-clear-preview"
                                class="hidden px-3 py-1.5 bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl text-xs font-medium transition-colors">
                                ✕ Batal Pilihan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unified Save Banner Action Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 sm:p-5 rounded-2xl bg-gray-50/90 dark:bg-gray-900/70 border border-gray-200 dark:border-gray-700 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Simpan Perubahan Banner</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Klik simpan untuk menerapkan seluruh gambar yang dipilih ke banner terkait.</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 self-end sm:self-auto w-full sm:w-auto">
                    <button id="unified-save-btn" wire:click.prevent="save" wire:loading.attr="disabled"
                        class="w-full sm:w-auto px-6 py-2.5 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white rounded-xl text-xs sm:text-sm font-semibold transition-all shadow-sm flex items-center justify-center gap-2 hover:shadow-md active:scale-[0.98] disabled:opacity-50 cursor-pointer">
                        <svg wire:loading.remove wire:target="save" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Simpan Banner</span>
                    </button>
                </div>
            </div>

            <!-- Section: Preview Tampilan Real-Time -->
            <div class="pt-8 border-t border-gray-200 dark:border-gray-700">
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Preview Tampilan Real-Time
                        </h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            Lihat simulasi bagaimana banner akan tampil di Dashboard Customer dan Dashboard Mitra.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Preview Customer -->
                    <div class="bg-gray-50/80 dark:bg-gray-900/60 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden min-w-0">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-4 sm:px-5 py-3.5">
                            <h4 class="text-sm sm:text-base font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Dashboard Customer
                            </h4>
                        </div>
                        <div class="p-4 sm:p-5">
                            <div id="customer-preview" class="h-44 sm:h-48 bg-gray-100 dark:bg-gray-900 rounded-xl overflow-hidden shadow-inner border border-gray-200 dark:border-gray-700 relative group">
                                @if(!empty($customerBanners) && count($customerBanners))
                                    <div id="customerSlider" class="w-full h-full overflow-hidden">
                                        <div class="customer-slides flex h-full will-change-transform" style="transition: transform 700ms cubic-bezier(.2,.9,.2,1);">
                                            @foreach($customerBanners as $b)
                                                <div class="flex-shrink-0 w-full h-full">
                                                    <img src="{{ asset('storage/' . $b) }}" alt="preview-customer" class="w-full h-full object-cover" />
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" data-role="prev" data-target="customer"
                                            class="absolute left-2.5 top-1/2 -translate-y-1/2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-full p-2 shadow-lg hover:bg-white dark:hover:bg-gray-700 opacity-0 group-hover:opacity-100 transition-all duration-200 hover:scale-110 z-50">
                                            <svg class="w-4 h-4 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <button type="button" data-role="next" data-target="customer"
                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-full p-2 shadow-lg hover:bg-white dark:hover:bg-gray-700 opacity-0 group-hover:opacity-100 transition-all duration-200 hover:scale-110 z-50">
                                            <svg class="w-4 h-4 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div class="absolute bottom-2.5 left-1/2 -translate-x-1/2 flex gap-1.5 z-40">
                                            @foreach($customerBanners as $index => $b)
                                                <div class="customer-dot w-2 h-2 rounded-full bg-white/60 transition-all duration-300" data-index="{{ $index }}"></div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="h-full flex flex-col items-center justify-center text-center px-4">
                                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 font-medium">Tidak ada banner Customer</p>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-3 flex items-start gap-2 bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/50 rounded-lg p-2.5">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-[11px] text-blue-700 dark:text-blue-300 leading-tight">Banner berganti otomatis setiap 3.5 detik.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Mitra -->
                    <div class="bg-gray-50/80 dark:bg-gray-900/60 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden min-w-0">
                        <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 sm:px-5 py-3.5">
                            <h4 class="text-sm sm:text-base font-semibold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Dashboard Mitra
                            </h4>
                        </div>
                        <div class="p-4 sm:p-5">
                            <div id="mitra-preview" class="h-44 sm:h-48 bg-gray-100 dark:bg-gray-900 rounded-xl overflow-hidden shadow-inner border border-gray-200 dark:border-gray-700 relative group">
                                @if(!empty($mitraBanners) && count($mitraBanners))
                                    <div id="mitraSlider" class="w-full h-full overflow-hidden">
                                        <div class="mitra-slides flex h-full will-change-transform" style="transition: transform 700ms cubic-bezier(.2,.9,.2,1);">
                                            @foreach($mitraBanners as $b)
                                                <div class="flex-shrink-0 w-full h-full">
                                                    <img src="{{ asset('storage/' . $b) }}" alt="preview-mitra" class="w-full h-full object-cover" />
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" data-role="prev" data-target="mitra"
                                            class="absolute left-2.5 top-1/2 -translate-y-1/2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-full p-2 shadow-lg hover:bg-white dark:hover:bg-gray-700 opacity-0 group-hover:opacity-100 transition-all duration-200 hover:scale-110 z-50">
                                            <svg class="w-4 h-4 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <button type="button" data-role="next" data-target="mitra"
                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-full p-2 shadow-lg hover:bg-white dark:hover:bg-gray-700 opacity-0 group-hover:opacity-100 transition-all duration-200 hover:scale-110 z-50">
                                            <svg class="w-4 h-4 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                        <div class="absolute bottom-2.5 left-1/2 -translate-x-1/2 flex gap-1.5 z-40">
                                            @foreach($mitraBanners as $index => $b)
                                                <div class="mitra-dot w-2 h-2 rounded-full bg-white/60 transition-all duration-300" data-index="{{ $index }}"></div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="h-full flex flex-col items-center justify-center text-center px-4">
                                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 font-medium">Tidak ada banner Mitra</p>
                                    </div>
                                @endif
                            </div>
                            <div class="mt-3 flex items-start gap-2 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/50 rounded-lg p-2.5">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-[11px] text-emerald-700 dark:text-emerald-300 leading-tight">Banner berganti otomatis setiap 3.5 detik.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function formatBytes(bytes) {
                if (!bytes || bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return (bytes / Math.pow(k, i)).toFixed(1) + ' ' + sizes[i];
            }

            function showSlotWarning(dropzone, totalSelected, maxAllowed) {
                const existing = dropzone.parentElement.querySelector('.slot-warning-msg');
                if (existing) existing.remove();

                const warn = document.createElement('div');
                warn.className = 'slot-warning-msg mt-2 px-3 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-xs text-amber-800 dark:text-amber-300 font-medium flex items-center gap-2';

                const icon = document.createElement('span');
                icon.className = 'w-2 h-2 rounded-full bg-amber-500 flex-shrink-0';
                warn.appendChild(icon);

                const text = document.createElement('span');
                text.textContent = 'Hanya ' + maxAllowed + ' file yang dipilih (dari ' + totalSelected + ' file) sesuai sisa slot maksimal 5 banner.';
                warn.appendChild(text);

                dropzone.parentElement.appendChild(warn);

                setTimeout(() => {
                    if (warn.parentElement) warn.remove();
                }, 6000);
            }

            // Drag & Drop functionality
            function setupDragDrop(theme, dropzoneId, fileInputId, previewId, clearBtnId) {
                const dropzone = document.getElementById(dropzoneId);
                const fileInput = document.getElementById(fileInputId);
                const previewContainer = document.getElementById(previewId);
                const clearBtn = document.getElementById(clearBtnId);

                if (!dropzone || !fileInput || !previewContainer || !clearBtn) return;

                const activeThemeClass = theme === 'blue'
                    ? ['border-blue-500', 'bg-blue-50/50', 'dark:bg-blue-950/30', 'ring-4', 'ring-blue-500/20']
                    : theme === 'emerald'
                    ? ['border-emerald-500', 'bg-emerald-50/50', 'dark:bg-emerald-950/30', 'ring-4', 'ring-emerald-500/20']
                    : ['border-primary-500', 'bg-primary-50/50', 'dark:bg-primary-950/30', 'ring-4', 'ring-primary-500/20'];

                // Click to upload — block if full
                dropzone.addEventListener('click', () => {
                    const maxSlots = parseInt(dropzone.dataset.maxSlots || '5', 10);
                    if (maxSlots <= 0) return; // slot penuh, abaikan klik
                    fileInput.click();
                });

                // Drag events
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropzone.addEventListener(eventName, () => {
                        dropzone.classList.add(...activeThemeClass, 'scale-[1.01]');
                    });
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, () => {
                        dropzone.classList.remove(...activeThemeClass, 'scale-[1.01]');
                    });
                });

                dropzone.addEventListener('drop', (e) => {
                    const dt = e.dataTransfer;
                    const maxSlots = parseInt(dropzone.dataset.maxSlots || '5', 10);
                    if (maxSlots <= 0) return; // slot penuh, tolak drop

                    let files = dt.files;
                    if (files.length > maxSlots) {
                        showSlotWarning(dropzone, files.length, maxSlots);
                        // Buat DataTransfer baru dengan file yang dipotong
                        const dt2 = new DataTransfer();
                        Array.from(files).slice(0, maxSlots).forEach(f => dt2.items.add(f));
                        files = dt2.files;
                    }
                    let assigned = false;
                    try {
                        fileInput.files = files;
                        fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                        assigned = true;
                    } catch (err) {
                        console.warn('Could not assign files to input programmatically', err);
                    }

                    if (!assigned) {
                        handleFiles(files);
                    }
                });

                fileInput.addEventListener('change', (e) => {
                    const maxSlots = parseInt(dropzone.dataset.maxSlots || '5', 10);
                    let files = e.target.files;
                    if (maxSlots > 0 && files.length > maxSlots) {
                        showSlotWarning(dropzone, files.length, maxSlots);
                        const dt2 = new DataTransfer();
                        Array.from(files).slice(0, maxSlots).forEach(f => dt2.items.add(f));
                        try {
                            fileInput.files = dt2.files;
                        } catch(ex) {}
                        files = dt2.files;
                    }
                    handleFiles(files);
                });

                clearBtn.addEventListener('click', () => {
                    previewContainer.innerHTML = '';
                    previewContainer.classList.add('hidden');
                    clearBtn.classList.add('hidden');
                    try { fileInput.value = ''; } catch(e) {}
                });

                function handleFiles(files) {
                    if (!files || files.length === 0) return;

                    previewContainer.innerHTML = '';
                    previewContainer.classList.remove('hidden');
                    clearBtn.classList.remove('hidden');

                    let totalBytes = 0;
                    Array.from(files).forEach(f => { totalBytes += f.size || 0; });

                    const themeColor = theme === 'blue' ? 'blue' : (theme === 'emerald' ? 'emerald' : 'primary');

                    // Header Status Banner
                    const headerInfo = document.createElement('div');
                    headerInfo.className = 'flex items-center justify-between px-3 py-2 rounded-xl bg-' + themeColor + '-50 dark:bg-' + themeColor + '-950/40 border border-' + themeColor + '-200 dark:border-' + themeColor + '-800/60 text-xs text-' + themeColor + '-700 dark:text-' + themeColor + '-300 font-medium animate-fade-in';
                    
                    const countWrapper = document.createElement('div');
                    countWrapper.className = 'flex items-center gap-1.5 font-semibold';
                    
                    const countDot = document.createElement('span');
                    countDot.className = 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse flex-shrink-0';
                    
                    const countText = document.createElement('span');
                    countText.textContent = files.length + ' gambar dipilih';
                    
                    countWrapper.appendChild(countDot);
                    countWrapper.appendChild(countText);
                    
                    const totalBadge = document.createElement('span');
                    totalBadge.className = 'text-[10px] font-mono px-2 py-0.5 rounded-md bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold border border-' + themeColor + '-200 dark:border-' + themeColor + '-700';
                    totalBadge.textContent = 'Total: ' + formatBytes(totalBytes);
                    
                    headerInfo.appendChild(countWrapper);
                    headerInfo.appendChild(totalBadge);
                    previewContainer.appendChild(headerInfo);

                    // Grid of 16:9 Banner Cards
                    const grid = document.createElement('div');
                    grid.className = 'grid grid-cols-2 sm:grid-cols-2 gap-2.5';

                    Array.from(files).forEach((file, index) => {
                        if (!file.type.startsWith('image/')) return;

                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const card = document.createElement('div');
                            card.className = 'relative rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm bg-gray-900 aspect-[16/9] group ring-2 ring-' + themeColor + '-500/20';

                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.alt = 'preview-' + index;
                            img.className = 'w-full h-full object-cover group-hover:scale-105 transition-transform duration-300';
                            card.appendChild(img);

                            const overlay = document.createElement('div');
                            overlay.className = 'absolute inset-0 bg-gradient-to-t from-gray-950/90 via-gray-950/20 to-transparent flex flex-col justify-between p-2 pointer-events-none';

                            const topRow = document.createElement('div');
                            topRow.className = 'flex items-center justify-between';

                            const badge = document.createElement('span');
                            badge.className = 'inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-emerald-500 text-white text-[9px] font-bold shadow-xs';
                            badge.textContent = 'Siap Simpan';

                            const sizeBadge = document.createElement('span');
                            sizeBadge.className = 'text-[9px] font-mono px-1 py-0.5 rounded bg-black/60 text-gray-200 backdrop-blur-xs';
                            sizeBadge.textContent = formatBytes(file.size);

                            topRow.appendChild(badge);
                            topRow.appendChild(sizeBadge);

                            const title = document.createElement('div');
                            title.className = 'truncate text-white text-[11px] font-medium drop-shadow-sm';
                            title.textContent = file.name;

                            overlay.appendChild(topRow);
                            overlay.appendChild(title);
                            card.appendChild(overlay);

                            grid.appendChild(card);
                        };
                        reader.readAsDataURL(file);
                    });

                    previewContainer.appendChild(grid);
                }
            }

            setupDragDrop('blue', 'customer-dropzone', 'customer-file-input', 'customer-preview-uploads', 'customer-clear-preview');
            setupDragDrop('emerald', 'mitra-dropzone', 'mitra-file-input', 'mitra-preview-uploads', 'mitra-clear-preview');

            // Robust isolated slider manager
            window.bannerSliderInstances = window.bannerSliderInstances || {};

            function createSlider(prefix) {
                // Clear any existing instance and timer
                if (window.bannerSliderInstances[prefix]) {
                    window.bannerSliderInstances[prefix].destroy();
                }

                const sliderEl = document.getElementById(prefix + 'Slider');
                const container = document.getElementById(prefix + '-preview');
                if (!sliderEl) return;

                const wrapper = sliderEl.querySelector('.' + prefix + '-slides');
                if (!wrapper) return;

                const slides = Array.from(wrapper.children);
                const total = slides.length;
                if (total <= 0) return;

                const dots = Array.from(sliderEl.querySelectorAll('.' + prefix + '-dot'));
                const prevBtn = sliderEl.querySelector('button[data-role="prev"]');
                const nextBtn = sliderEl.querySelector('button[data-role="next"]');

                let currentIndex = 0;
                let timer = null;

                function updateDots() {
                    dots.forEach((dot, i) => {
                        if (i === currentIndex) {
                            dot.classList.add('bg-white', 'w-6');
                            dot.classList.remove('bg-white/60', 'w-2');
                        } else {
                            dot.classList.add('bg-white/60', 'w-2');
                            dot.classList.remove('bg-white', 'w-6');
                        }
                    });
                }

                function goTo(i) {
                    currentIndex = (i + total) % total;
                    wrapper.style.transform = 'translateX(' + (-currentIndex * 100) + '%)';
                    updateDots();
                }

                function next() {
                    goTo(currentIndex + 1);
                }

                function prev() {
                    goTo(currentIndex - 1);
                }

                function startAutoplay() {
                    stopAutoplay();
                    if (total > 1) {
                        timer = setInterval(next, 3500);
                    }
                }

                function stopAutoplay() {
                    if (timer) {
                        clearInterval(timer);
                        timer = null;
                    }
                }

                // Initial position & dots
                goTo(0);
                startAutoplay();

                // Button listeners
                if (prevBtn) {
                    prevBtn.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        prev();
                        startAutoplay();
                    };
                }

                if (nextBtn) {
                    nextBtn.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        next();
                        startAutoplay();
                    };
                }

                // Dot click listeners
                dots.forEach((dot, i) => {
                    dot.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        goTo(i);
                        startAutoplay();
                    };
                    dot.style.cursor = 'pointer';
                });

                // Pause on hover
                if (container) {
                    container.onmouseenter = stopAutoplay;
                    container.onmouseleave = startAutoplay;

                    // Touch swipe support
                    let touchStartX = 0;
                    container.ontouchstart = function(e) {
                        if (e.touches && e.touches[0]) {
                            touchStartX = e.touches[0].clientX;
                            stopAutoplay();
                        }
                    };
                    container.ontouchend = function(e) {
                        if (e.changedTouches && e.changedTouches[0]) {
                            const diff = e.changedTouches[0].clientX - touchStartX;
                            if (Math.abs(diff) > 40) {
                                if (diff > 0) prev();
                                else next();
                            }
                            startAutoplay();
                        }
                    };
                }

                // Store instance
                window.bannerSliderInstances[prefix] = {
                    goTo: goTo,
                    next: next,
                    prev: prev,
                    destroy: function() {
                        stopAutoplay();
                        if (prevBtn) prevBtn.onclick = null;
                        if (nextBtn) nextBtn.onclick = null;
                        dots.forEach(d => d.onclick = null);
                        if (container) {
                            container.onmouseenter = null;
                            container.onmouseleave = null;
                            container.ontouchstart = null;
                            container.ontouchend = null;
                        }
                    }
                };
            }

            window.initBannerSliders = function () {
                createSlider('customer');
                createSlider('mitra');
            };

            // Initialize sliders on load
            window.initBannerSliders();

            // Re-init after Livewire DOM morphs
            document.addEventListener('livewire:initialized', function () {
                if (window.Livewire && Livewire.hook) {
                    Livewire.hook('morph.updated', function () {
                        setTimeout(window.initBannerSliders, 60);
                    });
                }
            });

            // Listen for saved event from Livewire to rebuild preview with saved images
            if (window.Livewire) {
                try {
                    Livewire.on && Livewire.on('bannersSaved', function (payload) {
                        try {
                            if (payload && payload.customer) {
                                const cp = document.getElementById('customer-preview-uploads');
                                const ci = document.getElementById('customer-file-input');
                                const cclear = document.getElementById('customer-clear-preview');
                                if (cp) {
                                    cp.innerHTML = '';
                                    cp.classList.remove('hidden');
                                    payload.customer.forEach(function (p) {
                                        const div = document.createElement('div');
                                        div.className = 'relative rounded-lg overflow-hidden border-2 border-primary-200 shadow-sm';
                                        const img = document.createElement('img');
                                        img.src = '/storage/' + p;
                                        img.className = 'w-full h-32 object-cover';
                                        div.appendChild(img);
                                        cp.appendChild(div);
                                    });
                                }
                                if (ci) ci.value = '';
                                if (cclear) cclear.classList.remove('hidden');
                            }

                            if (payload && payload.mitra) {
                                const mp = document.getElementById('mitra-preview-uploads');
                                const mi = document.getElementById('mitra-file-input');
                                const mclear = document.getElementById('mitra-clear-preview');
                                if (mp) {
                                    mp.innerHTML = '';
                                    mp.classList.remove('hidden');
                                    payload.mitra.forEach(function (p) {
                                        const div = document.createElement('div');
                                        div.className = 'relative rounded-lg overflow-hidden border-2 border-primary-200 shadow-sm';
                                        const img = document.createElement('img');
                                        img.src = '/storage/' + p;
                                        img.className = 'w-full h-32 object-cover';
                                        div.appendChild(img);
                                        mp.appendChild(div);
                                    });
                                }
                                if (mi) mi.value = '';
                                if (mclear) mclear.classList.remove('hidden');
                            }
                        } catch (e) { console.warn('bannersSaved handler error', e); }
                    });
                } catch (e) { console.warn('Livewire bannersSaved attach error', e); }
            }

            // Clear previews (client-side) when user clicks any Save button
            function clearUploadPreviews() {
                try {
                    const cp = document.getElementById('customer-preview-uploads');
                    const mp = document.getElementById('mitra-preview-uploads');
                    const cclear = document.getElementById('customer-clear-preview');
                    const mclear = document.getElementById('mitra-clear-preview');

                    if (cp) { cp.innerHTML = ''; cp.classList.add('hidden'); }
                    if (mp) { mp.innerHTML = ''; mp.classList.add('hidden'); }
                    if (cclear) cclear.classList.add('hidden');
                    if (mclear) mclear.classList.add('hidden');
                } catch (err) {
                    console.warn('clearUploadPreviews error', err);
                }
            }

            const unifiedBtn = document.getElementById('unified-save-btn');
            if (unifiedBtn) {
                unifiedBtn.addEventListener('click', function () {
                    clearUploadPreviews();
                });
            }
        });
    </script>

</div>