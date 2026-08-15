@php
    $title = 'Pengaturan';
    $breadcrumb = 'Super Admin / Pengaturan';
@endphp

<div>
    <div class="py-2">
        <!-- Sub-navigation tabs -->
        <x-superadmin-settings-nav />
    </div>

    <div class="bg-white shadow-sm border-b border-gray-200 mb-6">
        <div class="px-6 py-4">
            <p class="text-sm text-gray-600">Kelola banner yang tampil di dashboard Customer dan Mitra. Anda
                dapat menambah lebih dari satu banner untuk setiap tipe.</p>
        </div>
    </div>

    <div class="py-4">
        <!-- Modal Notifikasi Success -->
        @if (session('message'))
            <div id="success-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
                    <div class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">Berhasil!</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ session('message') }}</p>
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

        <!-- Modal Notifikasi Info -->
        @if (session('info'))
            <div id="info-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
                    <div class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">Info</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ session('info') }}</p>
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

        <!-- Modal Notifikasi Error -->
        @if (session('error'))
            <div id="error-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
                    <div class="p-6">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">Terjadi Kesalahan</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ session('error') }}</p>
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Customer Banner Section -->
            <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200">
                <h2 class="text-lg font-semibold mb-3">Banner Customer</h2>

                <div class="mb-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        @forelse($customerBanners as $i => $b)
                            <div class="relative rounded-lg overflow-hidden border border-gray-100">
                                <img src="{{ asset('storage/' . $b) }}" alt="banner-{{ $i }}"
                                    class="w-full h-32 object-cover">
                                <button wire:click="removeCustomer({{ $i }})" type="button"
                                    class="absolute top-2 right-2 bg-white/90 rounded-full p-1 hover:bg-red-50"
                                    title="Hapus">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <div class="col-span-full text-sm text-gray-500">Belum ada banner untuk customer.</div>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="block text-sm font-medium text-gray-700">Upload banner (bisa pilih beberapa)</label>

                    <!-- Drag & Drop Zone -->
                    <div id="customer-dropzone"
                        class="relative border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-primary-400 transition-colors bg-gray-50 hover:bg-gray-100 cursor-pointer">
                        <input type="file" id="customer-file-input" wire:model="customerUploads" accept="image/*"
                            multiple class="hidden" />
                        <div class="space-y-2">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                viewBox="0 0 48 48">
                                <path
                                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="text-sm text-gray-600">
                                <span class="font-semibold text-primary-600 hover:text-primary-700">Klik untuk
                                    upload</span>
                                <span> atau drag & drop</span>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, JPEG hingga 10MB</p>
                        </div>
                    </div>

                    @error('customerUploads.*') <div class="text-sm text-red-600">{{ $message }}</div> @enderror

                    <!-- Preview Upload -->
                    <div wire:ignore id="customer-preview-uploads"
                        class="hidden grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mt-4"></div>

                    <div class="flex items-center gap-2">
                        <button id="customer-save-btn" wire:click.prevent="save"
                            class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors shadow-sm">
                            Unggah & Simpan
                        </button>
                        <button wire:ignore id="customer-clear-preview"
                            class="hidden px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors">
                            Batal
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mitra Banner Section -->
            <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200">
                <h2 class="text-lg font-semibold mb-3">Banner Mitra</h2>

                <div class="mb-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        @forelse($mitraBanners as $i => $b)
                            <div class="relative rounded-lg overflow-hidden border border-gray-100">
                                <img src="{{ asset('storage/' . $b) }}" alt="banner-mitra-{{ $i }}"
                                    class="w-full h-32 object-cover">
                                <button wire:click="removeMitra({{ $i }})" type="button"
                                    class="absolute top-2 right-2 bg-white/90 rounded-full p-1 hover:bg-red-50"
                                    title="Hapus">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @empty
                            <div class="col-span-full text-sm text-gray-500">Belum ada banner untuk mitra.</div>
                        @endforelse
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="block text-sm font-medium text-gray-700">Upload banner (bisa pilih beberapa)</label>

                    <!-- Drag & Drop Zone -->
                    <div id="mitra-dropzone"
                        class="relative border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-primary-400 transition-colors bg-gray-50 hover:bg-gray-100 cursor-pointer">
                        <input type="file" id="mitra-file-input" wire:model="mitraUploads" accept="image/*" multiple
                            class="hidden" />
                        <div class="space-y-2">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                viewBox="0 0 48 48">
                                <path
                                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="text-sm text-gray-600">
                                <span class="font-semibold text-primary-600 hover:text-primary-700">Klik untuk
                                    upload</span>
                                <span> atau drag & drop</span>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, JPEG hingga 10MB</p>
                        </div>
                    </div>

                    @error('mitraUploads.*') <div class="text-sm text-red-600">{{ $message }}</div> @enderror

                    <!-- Preview Upload -->
                    <div wire:ignore id="mitra-preview-uploads"
                        class="hidden grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mt-4"></div>

                    <div class="flex items-center gap-2">
                        <button id="mitra-save-btn" wire:click.prevent="save"
                            class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors shadow-sm">
                            Unggah & Simpan
                        </button>
                        <button wire:ignore id="mitra-clear-preview"
                            class="hidden px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Home Banner Section -->
    <div class="px-8 py-6">
        <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200">
            <h2 class="text-lg font-semibold mb-3">Banner Beranda</h2>

            <div class="mb-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    @forelse($homeBanners as $i => $b)
                        <div class="relative rounded-lg overflow-hidden border border-gray-100">
                            <img src="{{ asset('storage/' . $b) }}" alt="banner-home-{{ $i }}" class="w-full h-32 object-cover">
                            <button wire:click="removeHome({{ $i }})" type="button" class="absolute top-2 right-2 bg-white/90 rounded-full p-1 hover:bg-red-50" title="Hapus">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @empty
                        <div class="col-span-full text-sm text-gray-500">Belum ada banner untuk beranda.</div>
                    @endforelse
                </div>
            </div>

            <div class="space-y-4">
                <label class="block text-sm font-medium text-gray-700">Upload banner Beranda (bisa pilih beberapa)</label>

                <div id="home-dropzone" class="relative border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-primary-400 transition-colors bg-gray-50 hover:bg-gray-100 cursor-pointer">
                    <input type="file" id="home-file-input" wire:model="homeUploads" accept="image/*" multiple class="hidden" />
                    <div class="space-y-2">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="text-sm text-gray-600">
                            <span class="font-semibold text-primary-600 hover:text-primary-700">Klik untuk upload</span>
                            <span> atau drag & drop</span>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, JPEG hingga 10MB</p>
                    </div>
                </div>

                @error('homeUploads.*') <div class="text-sm text-red-600">{{ $message }}</div> @enderror

                <div wire:ignore id="home-preview-uploads" class="hidden grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mt-4"></div>

                <div class="flex items-center gap-2">
                    <button id="home-save-btn" wire:click.prevent="save" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors shadow-sm">Unggah & Simpan</button>
                    <button wire:ignore id="home-clear-preview" class="hidden px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Section -->
    <div class="px-8 py-8 bg-gradient-to-b from-gray-50 to-white border-t border-gray-200 mt-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Preview Tampilan Dashboard</h2>
            <p class="text-sm text-gray-600">Lihat bagaimana banner akan tampil di dashboard Customer dan Mitra secara
                real-time</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Preview Customer -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-5 py-4">
                    <h3 class="text-base font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Dashboard Customer
                    </h3>
                </div>
                <div class="p-5">
                    <div id="customer-preview"
                        class="h-52 bg-gradient-to-br from-gray-100 to-gray-50 rounded-xl overflow-hidden shadow-inner border border-gray-200 relative group">
                        @if(!empty($customerBanners) && count($customerBanners))
                            <div id="customerSlider" class="w-full h-full overflow-hidden">
                                <div class="customer-slides flex h-full will-change-transform"
                                    style="transition: transform 700ms cubic-bezier(.2,.9,.2,1);">
                                    @foreach($customerBanners as $b)
                                        <div class="flex-shrink-0 w-full h-full">
                                            <img src="{{ asset('storage/' . $b) }}" alt="preview-customer"
                                                class="w-full h-full object-cover" />
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" data-role="prev" data-target="customer"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 bg-white/95 backdrop-blur-sm rounded-full p-2.5 shadow-lg hover:bg-white opacity-0 group-hover:opacity-100 transition-all duration-200 hover:scale-110 pointer-events-auto z-50">
                                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <button type="button" data-role="next" data-target="customer"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 bg-white/95 backdrop-blur-sm rounded-full p-2.5 shadow-lg hover:bg-white opacity-0 group-hover:opacity-100 transition-all duration-200 hover:scale-110 pointer-events-auto z-50">
                                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                                <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
                                    @foreach($customerBanners as $index => $b)
                                        <div class="customer-dot w-2 h-2 rounded-full bg-white/60 transition-all duration-300"
                                            data-index="{{ $index }}"></div>
                                    @endforeach
                                </div>
                                <!-- Click overlays (left/right) to ensure clicks captured even if buttons are blocked -->
                                <div class="arrow-overlay left" data-target="customer" data-role="prev"
                                    style="position:absolute;left:0;top:0;bottom:0;width:12%;z-index:9998;cursor:pointer;background:transparent;">
                                </div>
                                <div class="arrow-overlay right" data-target="customer" data-role="next"
                                    style="position:absolute;right:0;top:0;bottom:0;width:12%;z-index:9998;cursor:pointer;background:transparent;">
                                </div>
                            </div>
                        @else
                            <div class="h-full flex flex-col items-center justify-center text-center px-6">
                                <svg class="w-16 h-16 text-gray-300 mb-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-sm text-gray-500 font-medium">Tidak ada banner untuk Customer</p>
                                <p class="text-xs text-gray-400 mt-1">Sistem akan menampilkan carousel default</p>
                            </div>
                        @endif
                    </div>
                    <div class="mt-4 flex items-start gap-2 bg-blue-50 border border-blue-100 rounded-lg p-3">
                        <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-xs text-blue-700 leading-relaxed">Banner akan berganti otomatis setiap 3.5 detik.
                            Hover untuk jeda, klik panah untuk navigasi manual.</p>
                    </div>
                </div>
            </div>

            <!-- Preview Mitra -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-5 py-4">
                    <h3 class="text-base font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Dashboard Mitra
                    </h3>
                </div>
                <div class="p-5">
                    <div id="mitra-preview"
                        class="h-52 bg-gradient-to-br from-gray-100 to-gray-50 rounded-xl overflow-hidden shadow-inner border border-gray-200 relative group">
                        @if(!empty($mitraBanners) && count($mitraBanners))
                            <div id="mitraSlider" class="w-full h-full overflow-hidden">
                                <div class="mitra-slides flex h-full will-change-transform"
                                    style="transition: transform 700ms cubic-bezier(.2,.9,.2,1);">
                                    @foreach($mitraBanners as $b)
                                        <div class="flex-shrink-0 w-full h-full">
                                            <img src="{{ asset('storage/' . $b) }}" alt="preview-mitra"
                                                class="w-full h-full object-cover" />
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" data-role="prev" data-target="mitra"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 bg-white/95 backdrop-blur-sm rounded-full p-2.5 shadow-lg hover:bg-white opacity-0 group-hover:opacity-100 transition-all duration-200 hover:scale-110 pointer-events-auto z-50">
                                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <button type="button" data-role="next" data-target="mitra"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 bg-white/95 backdrop-blur-sm rounded-full p-2.5 shadow-lg hover:bg-white opacity-0 group-hover:opacity-100 transition-all duration-200 hover:scale-110 pointer-events-auto z-50">
                                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                                <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
                                    @foreach($mitraBanners as $index => $b)
                                        <div class="mitra-dot w-2 h-2 rounded-full bg-white/60 transition-all duration-300"
                                            data-index="{{ $index }}"></div>
                                    @endforeach
                                </div>
                                <!-- Click overlays (left/right) to ensure clicks captured even if buttons are blocked -->
                                <div class="arrow-overlay left" data-target="mitra" data-role="prev"
                                    style="position:absolute;left:0;top:0;bottom:0;width:12%;z-index:9998;cursor:pointer;background:transparent;">
                                </div>
                                <div class="arrow-overlay right" data-target="mitra" data-role="next"
                                    style="position:absolute;right:0;top:0;bottom:0;width:12%;z-index:9998;cursor:pointer;background:transparent;">
                                </div>
                            </div>
                        @else
                            <div class="h-full flex flex-col items-center justify-center text-center px-6">
                                <svg class="w-16 h-16 text-gray-300 mb-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-sm text-gray-500 font-medium">Tidak ada banner untuk Mitra</p>
                                <p class="text-xs text-gray-400 mt-1">Sistem akan menampilkan carousel default</p>
                            </div>
                        @endif
                    </div>
                    <div class="mt-4 flex items-start gap-2 bg-green-50 border border-green-100 rounded-lg p-3">
                        <svg class="w-4 h-4 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-xs text-green-700 leading-relaxed">Banner akan berganti otomatis setiap 3.5
                            detik. Hover untuk jeda, klik panah untuk navigasi manual.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Drag & Drop functionality
            function setupDragDrop(dropzoneId, fileInputId, previewId, clearBtnId) {
                const dropzone = document.getElementById(dropzoneId);
                const fileInput = document.getElementById(fileInputId);
                const previewContainer = document.getElementById(previewId);
                const clearBtn = document.getElementById(clearBtnId);

                if (!dropzone || !fileInput || !previewContainer || !clearBtn) return;

                // Click to upload
                dropzone.addEventListener('click', () => fileInput.click());

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
                        dropzone.classList.add('border-primary-500', 'bg-primary-50');
                    });
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropzone.addEventListener(eventName, () => {
                        dropzone.classList.remove('border-primary-500', 'bg-primary-50');
                    });
                });

                dropzone.addEventListener('drop', (e) => {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    // Try to assign files to input and trigger change so Livewire picks them up.
                    // If assignment succeeds we'll rely on the change listener to call handleFiles;
                    // otherwise fall back to calling handleFiles(files) directly.
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
                    handleFiles(e.target.files);
                });

                clearBtn.addEventListener('click', () => {
                    // only clear the preview UI; do not reset the file input here so Livewire
                    // still has access to the uploaded files until server responds
                    previewContainer.innerHTML = '';
                    previewContainer.classList.add('hidden');
                    clearBtn.classList.add('hidden');
                });

                function handleFiles(files) {
                    if (files.length === 0) return;

                    previewContainer.innerHTML = '';
                    previewContainer.classList.remove('hidden');
                    clearBtn.classList.remove('hidden');

                    Array.from(files).forEach((file, index) => {
                        if (!file.type.startsWith('image/')) return;

                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const div = document.createElement('div');
                            div.className = 'relative rounded-lg overflow-hidden border-2 border-primary-200 shadow-sm';
                            div.innerHTML = `
                                <img src="${e.target.result}" alt="preview-${index}" class="w-full h-32 object-cover">
                                <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition-all flex items-center justify-center">
                                    <div class="bg-white px-2 py-1 rounded text-xs font-medium text-gray-700 opacity-0 hover:opacity-100 transition-opacity">
                                        ${file.name.length > 15 ? file.name.substring(0, 15) + '...' : file.name}
                                    </div>
                                </div>
                            `;
                            previewContainer.appendChild(div);
                        };
                        reader.readAsDataURL(file);
                    });
                }
            }

            setupDragDrop('customer-dropzone', 'customer-file-input', 'customer-preview-uploads', 'customer-clear-preview');
            setupDragDrop('mitra-dropzone', 'mitra-file-input', 'mitra-preview-uploads', 'mitra-clear-preview');
            setupDragDrop('home-dropzone', 'home-file-input', 'home-preview-uploads', 'home-clear-preview');

            // Slider functionality with dots
            function initSimpleSlider(prefix, items) {
                const sliderId = prefix + 'Slider';
                const slidesWrapper = document.querySelector('#' + sliderId + ' > div');
                if (!slidesWrapper) return;
                const total = (items && items.length) ? items.length : (slidesWrapper.children.length || 0);
                let idx = 0;
                // initialize current index on the wrapper so external handlers can read it
                slidesWrapper.dataset.idx = String(idx);
                console.debug('initSimpleSlider', prefix, 'total=', total, 'slidesWrapper=', slidesWrapper);
                let interval = null;
                // guard to prevent overlapping transitions triggered by multiple handlers
                let isTransitioning = false;
                const TRANSITION_MS = 700; // should match CSS transition duration
                const dots = document.querySelectorAll('.' + prefix + '-dot');

                function updateDots() {
                    dots.forEach((dot, i) => {
                        if (i === idx) {
                            dot.classList.add('bg-white', 'w-6');
                            dot.classList.remove('bg-white/60', 'w-2');
                        } else {
                            dot.classList.add('bg-white/60', 'w-2');
                            dot.classList.remove('bg-white', 'w-6');
                        }
                    });
                }

                function goTo(i) {
                    if (isTransitioning) return;
                    isTransitioning = true;
                    idx = (i + total) % total;
                    slidesWrapper.style.transform = 'translateX(' + (-idx * 100) + '%)';
                    // store current index on wrapper so external handlers can read it
                    slidesWrapper.dataset.idx = String(idx);
                    updateDots();
                    // clear transitioning flag after transition completes
                    setTimeout(function () { isTransitioning = false; }, TRANSITION_MS + 30);
                }

                function next() { goTo(idx + 1); }
                function prev() { goTo(idx - 1); }

                // reset the autoplay interval after manual navigation with a delay
                function resetInterval() {
                    try {
                        if (interval) { clearInterval(interval); interval = null; }
                        // restart autoplay after a pause
                        interval = setInterval(function () { if (!isTransitioning) next(); }, 3500);
                    } catch (err) { console.warn('resetInterval err', err); }
                }

                // attach direct handlers to prev/next buttons inside this slider
                try {
                    const prevBtnLocal = document.querySelector('#' + sliderId + ' button[data-role="prev"]');
                    const nextBtnLocal = document.querySelector('#' + sliderId + ' button[data-role="next"]');
                    console.debug(prefix, 'found prevBtnLocal=', prevBtnLocal, 'nextBtnLocal=', nextBtnLocal);
                    if (prevBtnLocal) {
                        // lift above overlays and ensure pointer events
                        prevBtnLocal.style.zIndex = '9999';
                        prevBtnLocal.style.pointerEvents = 'auto';
                        prevBtnLocal.addEventListener('click', function (ev) {
                            ev.stopPropagation();
                            console.debug(prefix + ' prev clicked');
                            prev();
                            if (interval) { clearInterval(interval); interval = setInterval(next, 3500); }
                        }, true);
                    }
                    if (nextBtnLocal) {
                        nextBtnLocal.style.zIndex = '9999';
                        nextBtnLocal.style.pointerEvents = 'auto';
                        nextBtnLocal.addEventListener('click', function (ev) {
                            ev.stopPropagation();
                            console.debug(prefix + ' next clicked');
                            next();
                            if (interval) { clearInterval(interval); interval = setInterval(next, 3500); }
                        }, true);
                    }

                    // capture pointerdown on container to help diagnose overlays
                    const containerLocal = document.getElementById(prefix + '-preview');
                    if (containerLocal) {
                        containerLocal.addEventListener('pointerdown', function (ev) {
                            console.debug(prefix + ' container pointerdown target=', ev.target);
                        }, true);
                    }
                } catch (e) { console.warn('attach slider buttons error', e); }

                updateDots();

                if (total > 1) {
                    interval = setInterval(next, 3500);
                }

                const container = document.getElementById(prefix + '-preview');
                if (container) {
                    // keep existing container-level handlers for mouse/pointer interactions
                    container.addEventListener('mouseenter', function () { if (interval) clearInterval(interval); });
                    container.addEventListener('mouseleave', function () { if (total > 1) interval = setInterval(next, 3500); });



                    let startX = null;
                    container.addEventListener('pointerdown', function (ev) { startX = ev.clientX; try { container.setPointerCapture(ev.pointerId); } catch (e) { } });
                    container.addEventListener('pointerup', function (ev) {
                        if (startX === null) return;
                        const diff = ev.clientX - startX;
                        if (Math.abs(diff) > 30) {
                            if (diff > 0) prev(); else next();
                        }
                        startX = null;
                    });

                    // click-position fallback: click left/right area of container to navigate
                    container.addEventListener('click', function (ev) {
                        try {
                            const rect = container.getBoundingClientRect();
                            const x = ev.clientX - rect.left;
                            const w = rect.width || 1;
                            // left 20% -> prev, right 20% -> next
                            if (x < w * 0.2) {
                                console.debug(prefix + ' container click left -> prev');
                                prev();
                                if (interval) { clearInterval(interval); interval = setInterval(next, 3500); }
                                ev.stopPropagation();
                                return;
                            }
                            if (x > w * 0.8) {
                                console.debug(prefix + ' container click right -> next');
                                next();
                                if (interval) { clearInterval(interval); interval = setInterval(next, 3500); }
                                ev.stopPropagation();
                                return;
                            }
                        } catch (err) { console.warn('container click fallback err', err); }
                    }, true);
                }
            }

            // expose re-init function for Livewire re-renders
            window.initBannerSliders = function () {
                try {
                    initSimpleSlider('customer', @json($customerBanners ?? []));
                } catch (e) { console.warn('init customer slider error', e); }
                try {
                    initSimpleSlider('mitra', @json($mitraBanners ?? []));
                } catch (e) { console.warn('init mitra slider error', e); }
            };

            try {
                window.initBannerSliders();
            } catch (err) {
                console.warn('Slider init error:', err);
            }

            // Re-init after Livewire updates to ensure handlers and datasets are in sync
            if (window.Livewire && Livewire.hook) {
                try {
                    Livewire.hook('message.processed', function () {
                        try { window.initBannerSliders(); } catch (e) { console.warn('Livewire re-init slider err', e); }
                    });
                } catch (e) { console.warn('Livewire hook attach error', e); }
            }

            // Document-level click handler for slider prev/next buttons.
            // This uses the `data-target` attribute on the button to locate the correct slider
            // and reads/writes the current index from `slidesWrapper.dataset.idx` (set in initSimpleSlider).
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('button[data-role]');
                if (!btn) return;
                const role = btn.getAttribute('data-role');
                const target = btn.getAttribute('data-target');
                if (!role || !target) return;
                const slidesWrapper = document.querySelector('#' + target + 'Slider > div');
                if (!slidesWrapper) return;
                const total = slidesWrapper.children.length || 0;

                // try dataset index first, fallback to computed transform
                let idx = 0;
                if (typeof slidesWrapper.dataset.idx !== 'undefined') {
                    idx = parseInt(slidesWrapper.dataset.idx || '0', 10) || 0;
                } else {
                    const cs = window.getComputedStyle(slidesWrapper);
                    const transform = cs.transform || cs.webkitTransform || 'none';
                    if (transform && transform !== 'none') {
                        try {
                            const m = transform.match(/matrix\(([-0-9., ]+)\)/);
                            if (m) {
                                const vals = m[1].split(',').map(s => parseFloat(s.trim()));
                                // matrix(a, b, c, d, tx, ty) => tx is vals[4]
                                const tx = vals[4] || 0;
                                const w = slidesWrapper.clientWidth || slidesWrapper.offsetWidth || 1;
                                idx = Math.round(Math.abs(tx) / w) || 0;
                            }
                        } catch (err) { idx = 0; }
                    }
                }

                if (role === 'prev') idx = (idx - 1 + total) % total;
                if (role === 'next') idx = (idx + 1) % total;
                slidesWrapper.style.transform = 'translateX(' + (-idx * 100) + '%)';
                slidesWrapper.dataset.idx = String(idx);

                // update dots if present
                const dots = document.querySelectorAll('.' + target + '-dot');
                dots.forEach((dot, i) => {
                    if (i === idx) {
                        dot.classList.add('bg-white', 'w-6');
                        dot.classList.remove('bg-white/60', 'w-2');
                    } else {
                        dot.classList.add('bg-white/60', 'w-2');
                        dot.classList.remove('bg-white', 'w-6');
                    }
                });
            });

            // Overlay click zones fallback: handle clicks on transparent left/right areas
            document.querySelectorAll('.arrow-overlay').forEach(function (el) {
                el.addEventListener('click', function (ev) {
                    try {
                        const role = el.getAttribute('data-role');
                        const target = el.getAttribute('data-target');
                        if (!role || !target) return;
                        const slidesWrapper = document.querySelector('#' + target + 'Slider > div');
                        if (!slidesWrapper) return;
                        const total = slidesWrapper.children.length || 0;
                        let idx = parseInt(slidesWrapper.dataset.idx || '0', 10) || 0;
                        if (role === 'prev') idx = (idx - 1 + total) % total;
                        if (role === 'next') idx = (idx + 1) % total;
                        slidesWrapper.style.transform = 'translateX(' + (-idx * 100) + '%)';
                        slidesWrapper.dataset.idx = String(idx);
                        const dots = document.querySelectorAll('.' + target + '-dot');
                        dots.forEach((dot, i) => {
                            if (i === idx) {
                                dot.classList.add('bg-white', 'w-6');
                                dot.classList.remove('bg-white/60', 'w-2');
                            } else {
                                dot.classList.add('bg-white/60', 'w-2');
                                dot.classList.remove('bg-white', 'w-6');
                            }
                        });
                    } catch (err) { console.warn('arrow-overlay click err', err); }
                }, true);
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
                            if (payload && payload.home) {
                                const hp = document.getElementById('home-preview-uploads');
                                const hi = document.getElementById('home-file-input');
                                const hclear = document.getElementById('home-clear-preview');
                                if (hp) {
                                    hp.innerHTML = '';
                                    hp.classList.remove('hidden');
                                    payload.home.forEach(function (p) {
                                        const div = document.createElement('div');
                                        div.className = 'relative rounded-lg overflow-hidden border-2 border-primary-200 shadow-sm';
                                        const img = document.createElement('img');
                                        img.src = '/storage/' + p;
                                        img.className = 'w-full h-32 object-cover';
                                        div.appendChild(img);
                                        hp.appendChild(div);
                                    });
                                }
                                if (hi) hi.value = '';
                                if (hclear) hclear.classList.remove('hidden');
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
                    const hp = document.getElementById('home-preview-uploads');
                    // do NOT reset file input values here; Livewire needs them until upload completes
                    const cclear = document.getElementById('customer-clear-preview');
                    const mclear = document.getElementById('mitra-clear-preview');
                    const hclear = document.getElementById('home-clear-preview');

                    if (cp) { cp.innerHTML = ''; cp.classList.add('hidden'); }
                    if (mp) { mp.innerHTML = ''; mp.classList.add('hidden'); }
                    if (hp) { hp.innerHTML = ''; hp.classList.add('hidden'); }
                    if (cclear) cclear.classList.add('hidden');
                    if (mclear) mclear.classList.add('hidden');
                    if (hclear) hclear.classList.add('hidden');
                } catch (err) {
                    console.warn('clearUploadPreviews error', err);
                }
            }

            ['customer-save-btn', 'mitra-save-btn', 'home-save-btn'].forEach(function (id) {
                const btn = document.getElementById(id);
                if (btn) {
                    btn.addEventListener('click', function () {
                        clearUploadPreviews();
                    });
                }
            });
        });
    </script>

</div>