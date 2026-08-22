@php
    $title = 'Pengaturan';
    $breadcrumb = 'Super Admin / Pengaturan';
@endphp

<div class="py-2">
    <!-- Sub-navigation tabs -->
    <x-superadmin-settings-nav active="banners" />

    <!-- Notifications Alerts -->
    @if (session('message'))
        <div id="alert-message" class="mb-6 flex items-center p-4 text-emerald-800 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 shadow-xs ring-2 ring-emerald-500/20" role="alert">
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center mr-3 flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="text-sm font-semibold flex-1">
                {{ session('message') }}
            </div>
            <button type="button" onclick="document.getElementById('alert-message')?.remove()" class="ml-auto -mx-1.5 -my-1.5 bg-emerald-50 text-emerald-500 rounded-lg p-1.5 hover:bg-emerald-100 dark:bg-transparent dark:text-emerald-400 dark:hover:bg-emerald-900/50 inline-flex h-8 w-8 transition cursor-pointer">
                <span class="sr-only">Close</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if (session('info'))
        <div id="alert-info" class="mb-6 flex items-center p-4 text-blue-800 rounded-2xl bg-blue-50 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60 shadow-xs ring-2 ring-blue-500/20" role="alert">
            <div class="w-8 h-8 rounded-xl bg-blue-500/10 dark:bg-blue-500/20 flex items-center justify-center mr-3 flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01" />
                </svg>
            </div>
            <div class="text-sm font-semibold flex-1">
                {{ session('info') }}
            </div>
            <button type="button" onclick="document.getElementById('alert-info')?.remove()" class="ml-auto -mx-1.5 -my-1.5 bg-blue-50 text-blue-500 rounded-lg p-1.5 hover:bg-blue-100 dark:bg-transparent dark:text-blue-400 dark:hover:bg-blue-900/50 inline-flex h-8 w-8 transition cursor-pointer">
                <span class="sr-only">Close</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div id="alert-error" class="mb-6 flex items-center p-4 text-red-800 rounded-2xl bg-red-50 dark:bg-red-950/60 dark:text-red-300 border border-red-200 dark:border-red-800/60 shadow-xs ring-2 ring-red-500/20" role="alert">
            <div class="w-8 h-8 rounded-xl bg-red-500/10 dark:bg-red-500/20 flex items-center justify-center mr-3 flex-shrink-0">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <div class="text-sm font-semibold flex-1">
                {{ session('error') }}
            </div>
            <button type="button" onclick="document.getElementById('alert-error')?.remove()" class="ml-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg p-1.5 hover:bg-red-100 dark:bg-transparent dark:text-red-400 dark:hover:bg-red-900/50 inline-flex h-8 w-8 transition cursor-pointer">
                <span class="sr-only">Close</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
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

                        <!-- Upload Zone Customer -->
                        <label for="customer-file-input"
                            class="relative block border-2 border-dashed {{ $customerRemain === 0 ? 'border-red-300 dark:border-red-800 bg-red-50/40 dark:bg-red-950/20 cursor-not-allowed opacity-70' : 'border-gray-300 dark:border-gray-600 hover:border-blue-500 dark:hover:border-blue-400 bg-gray-50/60 dark:bg-gray-800/80 hover:bg-blue-50/20 dark:hover:bg-gray-750 cursor-pointer' }} rounded-2xl p-5 text-center transition-all duration-200 shadow-xs group">
                            <input type="file" id="customer-file-input" wire:model="customerUploads" accept="image/png,image/jpeg,image/jpg,.png,.jpg,.jpeg" multiple class="hidden" {{ $customerRemain === 0 ? 'disabled' : '' }} />
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
                                        <span class="font-bold text-blue-600 dark:text-blue-400 group-hover:underline">Pilih Gambar</span> (PNG / JPG / JPEG)
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 text-[10px] text-gray-400 dark:text-gray-400">
                                    <span class="px-1.5 py-0.5 rounded bg-white dark:bg-gray-700 font-mono text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600">PNG / JPG / JPEG</span>
                                    <span>Khusus Gambar • Maks 5MB • Rasio 16:9 • {{ $customerRemain > 0 ? 'Maks ' . $customerRemain . ' file' : 'Tidak bisa unggah' }}</span>
                                </div>
                            </div>
                        </label>

                        <!-- Loading Indicator -->
                        <div wire:loading wire:target="customerUploads" class="text-xs text-blue-600 dark:text-blue-400 font-semibold flex items-center justify-center gap-2 py-2">
                            <svg class="animate-spin h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Sedang memproses upload gambar...</span>
                        </div>

                        @error('customerUploads') <div class="text-xs text-red-600 dark:text-red-400 font-semibold mt-1 p-2 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/60">{{ $message }}</div> @enderror
                        @error('customerUploads.*') <div class="text-xs text-red-600 dark:text-red-400 font-semibold mt-1 p-2 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/60">{{ $message }}</div> @enderror

                        <!-- Reactive Livewire Preview -->
                        @if(!empty($customerUploads))
                            <div class="space-y-2.5 mt-3">
                                <div class="flex items-center justify-between text-xs font-semibold text-blue-700 dark:text-blue-300">
                                    <span>{{ count($customerUploads) }} banner baru dipilih:</span>
                                    <button type="button" wire:click="$set('customerUploads', [])" class="text-red-500 hover:text-red-700 text-xs font-medium cursor-pointer">✕ Batal Pilihan</button>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                    @foreach($customerUploads as $upload)
                                        @if($upload)
                                            <div class="relative rounded-xl overflow-hidden border-2 border-blue-500 aspect-[16/9] bg-gray-900 shadow-sm">
                                                <img src="{{ $upload->temporaryUrl() }}" class="w-full h-full object-cover">
                                                <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded bg-blue-600/90 text-white text-[9px] font-bold">Siap Simpan</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
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

                        <!-- Upload Zone Mitra -->
                        <label for="mitra-file-input"
                            class="relative block border-2 border-dashed {{ $mitraRemain === 0 ? 'border-red-300 dark:border-red-800 bg-red-50/40 dark:bg-red-950/20 cursor-not-allowed opacity-70' : 'border-gray-300 dark:border-gray-600 hover:border-emerald-500 dark:hover:border-emerald-400 bg-gray-50/60 dark:bg-gray-800/80 hover:bg-emerald-50/20 dark:hover:bg-gray-750 cursor-pointer' }} rounded-2xl p-5 text-center transition-all duration-200 shadow-xs group">
                            <input type="file" id="mitra-file-input" wire:model="mitraUploads" accept="image/png,image/jpeg,image/jpg,.png,.jpg,.jpeg" multiple class="hidden" {{ $mitraRemain === 0 ? 'disabled' : '' }} />
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
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400 group-hover:underline">Pilih Gambar</span> (PNG / JPG / JPEG)
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5 text-[10px] text-gray-400 dark:text-gray-400">
                                    <span class="px-1.5 py-0.5 rounded bg-white dark:bg-gray-700 font-mono text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600">PNG / JPG / JPEG</span>
                                    <span>Khusus Gambar • Maks 5MB • Rasio 16:9 • {{ $mitraRemain > 0 ? 'Maks ' . $mitraRemain . ' file' : 'Tidak bisa unggah' }}</span>
                                </div>
                            </div>
                        </label>

                        <!-- Loading Indicator -->
                        <div wire:loading wire:target="mitraUploads" class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold flex items-center justify-center gap-2 py-2">
                            <svg class="animate-spin h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Sedang memproses upload gambar...</span>
                        </div>

                        @error('mitraUploads') <div class="text-xs text-red-600 dark:text-red-400 font-semibold mt-1 p-2 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/60">{{ $message }}</div> @enderror
                        @error('mitraUploads.*') <div class="text-xs text-red-600 dark:text-red-400 font-semibold mt-1 p-2 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/60">{{ $message }}</div> @enderror

                        <!-- Reactive Livewire Preview -->
                        @if(!empty($mitraUploads))
                            <div class="space-y-2.5 mt-3">
                                <div class="flex items-center justify-between text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                                    <span>{{ count($mitraUploads) }} banner baru dipilih:</span>
                                    <button type="button" wire:click="$set('mitraUploads', [])" class="text-red-500 hover:text-red-700 text-xs font-medium cursor-pointer">✕ Batal Pilihan</button>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                    @foreach($mitraUploads as $upload)
                                        @if($upload)
                                            <div class="relative rounded-xl overflow-hidden border-2 border-emerald-500 aspect-[16/9] bg-gray-900 shadow-sm">
                                                <img src="{{ $upload->temporaryUrl() }}" class="w-full h-full object-cover">
                                                <span class="absolute bottom-1 right-1 px-1.5 py-0.5 rounded bg-emerald-600/90 text-white text-[9px] font-bold">Siap Simpan</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
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
        (function () {
            window.bannerSliderInstances = window.bannerSliderInstances || {};

            function createSlider(prefix) {
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

                goTo(0);
                startAutoplay();

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

                dots.forEach((dot, i) => {
                    dot.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        goTo(i);
                        startAutoplay();
                    };
                    dot.style.cursor = 'pointer';
                });

                if (container) {
                    container.onmouseenter = stopAutoplay;
                    container.onmouseleave = startAutoplay;
                }

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
                        }
                    }
                };
            }

            function initAllSliders() {
                createSlider('customer');
                createSlider('mitra');
            }

            // Client-side file type validator before upload
            document.addEventListener('change', function(e) {
                if (e.target && (e.target.id === 'customer-file-input' || e.target.id === 'mitra-file-input')) {
                    const files = e.target.files;
                    if (!files || files.length === 0) return;
                    
                    const validExtensions = ['png', 'jpg', 'jpeg'];
                    const invalidFiles = [];
                    
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        const nameParts = file.name.split('.');
                        const ext = nameParts.length > 1 ? nameParts.pop().toLowerCase() : '';
                        const isImageMime = file.type.startsWith('image/');
                        if (!validExtensions.includes(ext) || !isImageMime) {
                            invalidFiles.push(file.name);
                        }
                    }
                    
                    if (invalidFiles.length > 0) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.target.value = '';
                        alert('⚠️ Format file tidak diizinkan:\n\n• ' + invalidFiles.join('\n• ') + '\n\nAnda hanya dapat mengunggah file gambar bertipe PNG, JPG, atau JPEG (bukan PDF, DOC, atau Excel).');
                    }
                }
            }, true);

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAllSliders);
            } else {
                initAllSliders();
            }
            document.addEventListener('livewire:navigated', initAllSliders);
        })();
    </script>
</div>