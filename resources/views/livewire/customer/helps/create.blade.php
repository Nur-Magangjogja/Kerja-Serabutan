<div x-data="{ previewModalPhoto: null, previewModalTitle: 'Review Foto Pendukung' }">
    <style>
        [x-cloak] { display: none !important; }
        :root {
            --brand-500: #0ea5a4;
            --brand-600: #08979a;
            --muted-600: #6b7280;
        }

        .card-shadow {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .focus-ring:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(14, 165, 164, 0.2);
        }

        /* Leaflet Map Styles */
        #map {
            height: 280px !important;
            min-height: 280px;
            z-index: 1;
        }
        
        .leaflet-container {
            height: 100%;
            width: 100%;
            border-radius: 0.75rem;
        }

        .custom-onsite-marker,
        .custom-pickup-marker,
        .custom-delivery-marker {
            background: transparent !important;
            border: none !important;
        }

        /* Sembunyikan scrollbar pada container pill overflow */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    <div id="main-content" class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <div class="max-w-md mx-auto">
            <!-- Header Section -->
            <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-[#0098e7] rounded-b-2xl shadow-sm text-white">
                <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

                <div class="relative z-10">
                    <div class="flex items-center justify-between min-h-[40px] text-white">
                        <div class="w-10 flex items-center">
                            <a href="{{ route('customer.helps.index') }}" wire:navigate aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-xl transition-colors duration-200 cursor-pointer flex items-center justify-center text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                                </svg>
                            </a>
                        </div>

                        <div class="text-center flex-1 min-w-0 px-2">
                            <h1 class="text-base font-bold truncate">Buat Permintaan Bantuan</h1>
                            <p class="text-xs text-white font-medium truncate mt-0.5">Pilih layanan dan tentukan imbalan secara transparan</p>
                        </div>

                        <div class="w-10 flex items-center justify-end"></div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="px-5 pt-5 pb-8">
                {{-- Floating Validation Error Banner --}}
                @if ($errors->any())
                    <div x-data="{ show: true }" x-show="show" x-init="scrollToFirstError(); setTimeout(() => show = false, 6000)"
                         class="mb-4 bg-red-50 dark:bg-red-950/40 border-l-4 border-red-500 dark:border-red-500 p-3.5 rounded-r-xl shadow-sm flex items-start justify-between gap-3 animate-fade-in border border-red-100 dark:border-red-900/50">
                        <div class="flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-red-500 dark:text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-xs font-bold text-red-800 dark:text-red-300">Permintaan Belum Bisa Dikirim</p>
                                <p class="text-xs text-red-700 dark:text-red-400 mt-0.5">Mohon lengkapi dan perbaiki kolom yang bertanda merah di bawah ini.</p>
                            </div>
                        </div>
                        <button type="button" @click="show = false" class="text-red-400 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 text-base font-bold leading-none cursor-pointer">&times;</button>
                    </div>
                @endif


                <form wire:submit.prevent="prepareConfirm" enctype="multipart/form-data" class="space-y-5">
                    <!-- 1. Pilihan Jenis Layanan (On-Site vs Pickup/Delivery) -->
                    @include('livewire.customer.helps.partials.service-type-selector')

                    <!-- 2. Judul Bantuan -->
                    <div class="pt-1 pb-1" id="group-title">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                </svg>
                                Judul Bantuan
                                <span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <input type="text" wire:model="title" id="title-input"
                            placeholder="{{ $service_type === 'pickup_delivery' ? 'Contoh: Antar Paket Dokumen ke Kantor Cabang' : 'Contoh: Butuh Bantuan Kupas Bawang / Bersihkan Kebun' }}"
                            class="w-full px-4 py-3 text-sm rounded-lg border @error('title') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500">
                        @error('title')
                            <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1.5 block flex items-center font-medium">
                                <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                    
                    <!-- 3. Transparan Ringkasan Saldo Dibutuhkan -->
                    @php
                        $feeCalc = \App\Models\AppSetting::calculatePlatformFee((float) ($amount ?: 0));
                        $calculatedTotal = (float) ($amount ?: 0) + $feeCalc['fee_amount'];
                    @endphp
                    @if((float) ($amount ?: 0) > 0)
                        <div class="p-3.5 bg-blue-50/70 dark:bg-gray-800/90 rounded-xl border border-blue-200 dark:border-gray-700 text-xs space-y-2">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>{{ $service_type === 'pickup_delivery' ? 'Biaya Ongkos Antar / Jemput :' : 'Imbalan Rekan Jasa :' }}</span>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format((float)$amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Biaya Layanan Platform (Tetap) :</span>
                                <span class="font-semibold text-blue-600 dark:text-blue-400">+ Rp {{ number_format($feeCalc['fee_amount'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-baseline font-bold text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-gray-700 text-sm">
                                <span>Total Saldo yang Dibutuhkan:</span>
                                <span class="text-blue-600 dark:text-blue-400 text-base">Rp {{ number_format($calculatedTotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- 4. Nominal Imbalan Pekerjaan (Hanya untuk On-Site / Kerja Serabutan) -->
                    @include('livewire.customer.helps.partials.amount-on-site')

                    <!-- Hidden inputs for City & District (Otomatis ditentukan secara akurat dari titik Peta/GPS) -->
                    <input type="hidden" wire:model="city_id">
                    <input type="hidden" wire:model="district_id">
                    @error('city_id')
                        <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1 block flex items-center font-medium">
                            <svg class="w-3.5 h-3.5 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Silakan tentukan titik lokasi pada peta di bawah untuk mendeteksi wilayah secara otomatis.
                        </span>
                    @enderror

                    <!-- 5. Tandai Lokasi di Peta & Switcher Titik -->
                    @include('livewire.customer.helps.partials.map-picker')

                    <!-- 6. Detail Alamat Lokasi (Sesuai Jenis Layanan) -->
                    @include('livewire.customer.helps.partials.location-inputs')

                    <!-- 7. Detail Patokan & Ciri Rumah (Tersimpan di Profil) -->
                    @include('livewire.customer.helps.partials.patokan-landmarks')

                    <!-- 8. Jadwal Permintaan & Batas Waktu Kadaluwarsa Auto-Cancel -->
                    @include('livewire.customer.helps.partials.schedule-expiry')

                    <!-- 9. Deskripsi Rincian Pekerjaan -->
                    <div id="group-description">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                Deskripsi Rincian Pekerjaan / Bantuan
                                <span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <textarea wire:model="description" id="description-input" rows="4"
                            placeholder="{{ $service_type === 'pickup_delivery' ? (($service_category ?? '') === 'passenger' ? 'Contoh: Titik jemput depan lobby hotel, 1 orang penumpang, siap berangkat jam 09.00...' : 'Contoh: Paket berkas/barang berat ~5 kg (maks. 20 kg). Mohon bawa dengan hati-hati...') : 'Jelaskan instruksi atau kebutuhan Anda secara jelas untuk memudahkan Rekan Jasa...' }}"
                            class="w-full px-4 py-3 text-sm rounded-lg border @error('description') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition resize-none bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"></textarea>
                        @error('description')
                            <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1.5 block flex items-center font-medium">
                                <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- 10. Peralatan yang Sudah Disediakan -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                                </svg>
                                Peralatan yang Sudah Disediakan
                                <span class="text-gray-400 dark:text-gray-500 text-xs ml-1 font-normal">(Opsional)</span>
                            </span>
                        </label>
                        <textarea wire:model="equipment_provided" rows="2"
                            placeholder="Contoh: Sapu, kain pel, cairan pembersih sudah disiapkan"
                            class="w-full px-4 py-3 text-sm rounded-lg border border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition resize-none bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"></textarea>
                    </div>

                    <!-- 11. Foto Pendukung -->
                    <div id="group-photo" class="space-y-2">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
                            <span class="flex items-center justify-between">
                                <span class="flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                    </svg>
                                    Foto Pendukung
                                    <span class="text-gray-400 dark:text-gray-500 text-xs ml-1 font-normal">(Opsional)</span>
                                </span>
                                @if ($photo)
                                    <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Foto Terpilih
                                    </span>
                                @endif
                            </span>
                        </label>

                        <!-- Hidden Input File -->
                        <input type="file" wire:model="photo" accept="image/png, image/jpeg, image/jpg, image/webp, .png, .jpg, .jpeg, .webp" id="photo-input" class="hidden">

                        <!-- Loading State saat Upload Berlangsung -->
                        <div wire:loading wire:target="photo" class="w-full p-4 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-2xl text-center">
                            <div class="flex items-center justify-center gap-2.5 text-blue-600 dark:text-blue-400 font-semibold text-xs">
                                <svg class="animate-spin h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span>Sedang memproses & mengunggah gambar...</span>
                            </div>
                        </div>

                        @if ($photo)
                            @php
                                $canPreview = false;
                                $previewUrl = '';
                                $fileName = 'Foto Terpilih';
                                $fileSize = '';
                                try {
                                    $canPreview = method_exists($photo, 'temporaryUrl') && $photo->isPreviewable();
                                    if ($canPreview) {
                                        $previewUrl = $photo->temporaryUrl();
                                    }
                                    if (method_exists($photo, 'getClientOriginalName')) {
                                        $fileName = $photo->getClientOriginalName();
                                    }
                                    if (method_exists($photo, 'getSize')) {
                                        $sizeKb = round($photo->getSize() / 1024, 1);
                                        $fileSize = $sizeKb > 1024 ? round($sizeKb / 1024, 2) . ' MB' : $sizeKb . ' KB';
                                    }
                                } catch (\Throwable $e) {
                                    $canPreview = false;
                                }
                            @endphp

                            <!-- Uploaded Photo Review Container (Uncropped / Tanpa Terpotong di Segala Ukuran) -->
                            <div wire:loading.remove wire:target="photo" class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xs overflow-hidden transition-all duration-200">
                                <!-- Header info file & action buttons -->
                                <div class="px-3.5 py-2.5 bg-gray-50/90 dark:bg-gray-750/80 border-b border-gray-100 dark:border-gray-700/70 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0 flex-1">
                                        <span class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-300 flex items-center justify-center shrink-0">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate" title="{{ $fileName }}">
                                                {{ $fileName }}
                                            </p>
                                            @if ($fileSize)
                                                <p class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">{{ $fileSize }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1 shrink-0">
                                        @if ($canPreview)
                                            <button type="button" 
                                                @click="previewModalPhoto = '{{ $previewUrl }}'; previewModalTitle = 'Review Foto Pendukung'"
                                                class="p-1.5 text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/60 rounded-lg transition cursor-pointer"
                                                title="Perbesar Layar Penuh">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                                </svg>
                                            </button>
                                        @endif
                                        <button type="button"
                                            wire:click="$set('photo', null)"
                                            class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 rounded-lg transition cursor-pointer"
                                            title="Hapus Foto">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Preview Frame: Uncropped, Auto-fit all dimensions with neutral backdrop -->
                                <div class="p-3 bg-gray-900/5 dark:bg-black/30">
                                    @if ($canPreview)
                                        <div class="relative w-full rounded-xl overflow-hidden bg-gray-950/5 dark:bg-black/50 border border-gray-200/60 dark:border-gray-700/60 flex items-center justify-center p-2 min-h-[160px] max-h-80 cursor-pointer group shadow-2xs"
                                             @click="previewModalPhoto = '{{ $previewUrl }}'; previewModalTitle = 'Review Foto Pendukung'"
                                             title="Klik untuk melihat foto dalam ukuran penuh">
                                            <!-- Image rendered with object-contain to guarantee 100% visible uncropped rendering across all aspect ratios -->
                                            <img src="{{ $previewUrl }}" 
                                                 alt="Review Foto" 
                                                 class="w-auto h-auto max-w-full max-h-72 object-contain mx-auto rounded-lg transition-transform duration-300 group-hover:scale-[1.01]">
                                            
                                            <!-- Floating Zoom Overlay Badge -->
                                            <div class="absolute bottom-2.5 right-2.5 bg-gray-900/75 hover:bg-gray-900 text-white text-[11px] font-medium px-2.5 py-1 rounded-full backdrop-blur-xs flex items-center gap-1.5 transition shadow-sm opacity-90 group-hover:opacity-100 pointer-events-none">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                                                </svg>
                                                <span>Perbesar</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center justify-center py-6 text-center">
                                            <svg class="w-8 h-8 text-gray-400 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $fileName }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Action footer -->
                                <div class="px-3.5 py-2.5 bg-gray-50/90 dark:bg-gray-750/80 border-t border-gray-100 dark:border-gray-700/70 flex items-center justify-between text-xs">
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Foto utuh tanpa terpotong
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <label for="photo-input" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 dark:hover:bg-blue-900/60 rounded-lg cursor-pointer transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Ganti Foto
                                        </label>
                                        <button type="button" wire:click="$set('photo', null)" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-red-600 dark:text-red-400 hover:text-red-700 bg-red-50 dark:bg-red-950/60 hover:bg-red-100 rounded-lg cursor-pointer transition">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Empty Upload Dropzone -->
                            <label for="photo-input" wire:loading.remove wire:target="photo"
                                class="group flex flex-col items-center justify-center w-full py-6 px-4 rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-400 cursor-pointer transition-all duration-200 bg-white dark:bg-gray-800/80 hover:bg-blue-50/50 dark:hover:bg-blue-950/20 text-center shadow-2xs">
                                <div class="w-11 h-11 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-2.5 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-xs font-bold text-gray-800 dark:text-gray-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                    Pilih atau Ambil Foto Pendukung
                                </p>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">
                                    Maksimal 2MB (JPG, JPEG, PNG, WebP)
                                </p>
                            </label>
                        @endif

                        @error('photo')
                            <span class="text-red-500 dark:text-red-400 text-xs mt-1.5 flex items-center font-medium">
                                <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-3 pt-4">
                        <a href="{{ route('dashboard') }}" wire:navigate onclick="handleCancelCreateHelp()"
                            class="flex-1 inline-flex items-center justify-center bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 px-5 py-3 text-sm rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer">
                            Batal
                        </a>
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex-1 inline-flex items-center justify-center bg-gradient-to-r from-blue-500 to-blue-600 text-white px-5 py-3 text-sm rounded-xl font-semibold hover:from-blue-600 hover:to-blue-700 transition shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                            <span wire:loading.remove wire:target="prepareConfirm">Lanjut Konfirmasi</span>
                            <span wire:loading wire:target="prepareConfirm" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Menghitung...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 12. Modal Konfirmasi & Ringkasan Transparan -->
    @include('livewire.customer.helps.partials.confirm-modal')

    <!-- 13. Map Scripts & Leaflet Routing Engine -->
    @include('livewire.customer.helps.partials.map-scripts')

    <!-- 14. Temporary Draft Cookie Manager (15s Expiration When Away or Cancelled) -->
    <script>
        (function() {
            const DRAFT_COOKIE = 'sb_help_draft';
            const AWAY_TTL_SEC = 15; // Batas waktu 15 detik saat keluar halaman
            let isDraftCancelled = false;

            function setDraftCookie(data, seconds = AWAY_TTL_SEC) {
                if (isDraftCancelled || !data) return;
                const expiresAt = Date.now() + (seconds * 1000);
                const payload = JSON.stringify({
                    expires_at: expiresAt,
                    data: data
                });
                const d = new Date();
                d.setTime(expiresAt);
                document.cookie = DRAFT_COOKIE + '=' + encodeURIComponent(payload) + '; expires=' + d.toUTCString() + '; max-age=' + seconds + '; path=/; SameSite=Lax';
            }

            function getDraftCookie() {
                if (isDraftCancelled) return null;
                const nameEQ = DRAFT_COOKIE + '=';
                const ca = document.cookie.split(';');
                for (let i = 0; i < ca.length; i++) {
                    let c = ca[i].trim();
                    if (c.indexOf(nameEQ) === 0) {
                        try {
                            return JSON.parse(decodeURIComponent(c.substring(nameEQ.length, c.length)));
                        } catch(e) {
                            return null;
                        }
                    }
                }
                return null;
            }

            function deleteDraftCookie() {
                document.cookie = DRAFT_COOKIE + '=; path=/; max-age=0; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax';
                document.cookie = DRAFT_COOKIE + '=; path=' + window.location.pathname + '; max-age=0; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax';
                try {
                    localStorage.removeItem(DRAFT_COOKIE);
                    sessionStorage.removeItem(DRAFT_COOKIE);
                } catch(e) {}
            }

            window.clearHelpDraftCookie = function() {
                isDraftCancelled = true;
                deleteDraftCookie();
            };

            window.handleCancelCreateHelp = function() {
                isDraftCancelled = true;
                deleteDraftCookie();
                window.removeEventListener('beforeunload', persistDraftState);
                window.removeEventListener('pagehide', persistDraftState);
                
                try {
                    const root = document.getElementById('main-content')?.closest('[wire\\:id]');
                    let lw = null;
                    if (typeof @this !== 'undefined' && @this) {
                        lw = @this;
                    } else if (root && window.Livewire) {
                        const id = root.getAttribute('wire:id');
                        if (id) lw = window.Livewire.find(id);
                    }
                    if (lw && typeof lw.call === 'function') {
                        lw.call('discardDraft');
                    }
                } catch(e){}
            };

            function getFormData() {
                if (isDraftCancelled) return null;
                try {
                    const root = document.getElementById('main-content')?.closest('[wire\\:id]');
                    let lw = null;
                    if (typeof @this !== 'undefined' && @this) {
                        lw = @this;
                    } else if (root && window.Livewire) {
                        const id = root.getAttribute('wire:id');
                        if (id) lw = window.Livewire.find(id);
                    }

                    if (lw && typeof lw.get === 'function') {
                        return {
                            service_type: lw.get('service_type'),
                            service_category: lw.get('service_category'),
                            service_duration_hours: lw.get('service_duration_hours'),
                            title: lw.get('title'),
                            description: lw.get('description'),
                            equipment_provided: lw.get('equipment_provided'),
                            amount: lw.get('amount'),
                            city_id: lw.get('city_id'),
                            cityQuery: lw.get('cityQuery'),
                            district_id: lw.get('district_id'),
                            districtQuery: lw.get('districtQuery'),
                            location: lw.get('location'),
                            full_address: lw.get('full_address'),
                            latitude: lw.get('latitude'),
                            longitude: lw.get('longitude'),
                            pickup_address: lw.get('pickup_address'),
                            pickup_latitude: lw.get('pickup_latitude'),
                            pickup_longitude: lw.get('pickup_longitude'),
                            delivery_address: lw.get('delivery_address'),
                            delivery_latitude: lw.get('delivery_latitude'),
                            delivery_longitude: lw.get('delivery_longitude'),
                            route_distance_km: lw.get('route_distance_km'),
                            order_mode: lw.get('order_mode'),
                            scheduled_date: lw.get('scheduled_date'),
                            scheduled_time: lw.get('scheduled_time'),
                            publish_mode: lw.get('publish_mode'),
                            publish_date: lw.get('publish_date'),
                            publish_time: lw.get('publish_time'),
                            early_departure_minutes: lw.get('early_departure_minutes'),
                            expiry_option: lw.get('expiry_option'),
                            custom_expiry_date: lw.get('custom_expiry_date'),
                            custom_expiry_time: lw.get('custom_expiry_time'),
                        };
                    }
                } catch(e) {}
                return null;
            }

            function hasContent(data) {
                if (!data) return false;
                return (data.title && data.title.trim().length > 0) ||
                       (data.description && data.description.trim().length > 0) ||
                       (data.location && data.location.trim().length > 0) ||
                       (data.pickup_address && data.pickup_address.trim().length > 0) ||
                       (data.delivery_address && data.delivery_address.trim().length > 0) ||
                       (data.latitude !== null && data.latitude !== undefined) ||
                       (data.pickup_latitude !== null && data.pickup_latitude !== undefined);
            }

            function persistDraftState() {
                if (isDraftCancelled) {
                    deleteDraftCookie();
                    return;
                }
                const data = getFormData();
                if (data && hasContent(data)) {
                    setDraftCookie(data, AWAY_TTL_SEC);
                } else {
                    deleteDraftCookie();
                }
            }

            // Simpan ke cookie saat beralih tab, keluar halaman, atau navigasi
            window.addEventListener('beforeunload', persistDraftState);
            window.addEventListener('pagehide', persistDraftState);
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    persistDraftState();
                }
            });
            document.addEventListener('livewire:navigating', function() {
                if (isDraftCancelled) {
                    deleteDraftCookie();
                }
            });

            // Hapus cookie saat order berhasil dibuat / dibatalkan
            window.addEventListener('help:draft-cleared', () => {
                isDraftCancelled = true;
                deleteDraftCookie();
            });
            window.addEventListener('draft-cleared', () => {
                isDraftCancelled = true;
                deleteDraftCookie();
            });

            // Periksa & sinkronkan draft saat halaman dibuka
            function checkAndRestoreDraft() {
                if (isDraftCancelled) return;
                const draft = getDraftCookie();
                if (!draft) return;

                const now = Date.now();
                if (draft.expires_at && now <= draft.expires_at && draft.data && hasContent(draft.data)) {
                    // Masih dalam jendela batas waktu 15 detik
                    const root = document.getElementById('main-content')?.closest('[wire\\:id]');
                    let lw = null;
                    if (typeof @this !== 'undefined' && @this) {
                        lw = @this;
                    } else if (root && window.Livewire) {
                        const id = root.getAttribute('wire:id');
                        if (id) lw = window.Livewire.find(id);
                    }

                    if (lw && typeof lw.call === 'function') {
                        lw.call('restoreDraft', draft.data);
                    }

                    if (window.syncMapToServiceType) {
                        setTimeout(() => {
                            window.syncMapToServiceType(draft.data.service_type || 'on_site_service', {
                                lat: draft.data.latitude,
                                lng: draft.data.longitude,
                                pickupLat: draft.data.pickup_latitude,
                                pickupLng: draft.data.pickup_longitude,
                                deliveryLat: draft.data.delivery_latitude,
                                deliveryLng: draft.data.delivery_longitude,
                            });
                        }, 400);
                    }
                } else {
                    // Sudah lewat 15 detik -> buang draft
                    deleteDraftCookie();
                }
            }

            document.addEventListener('DOMContentLoaded', checkAndRestoreDraft);
            document.addEventListener('livewire:navigated', checkAndRestoreDraft);
        })();
    </script>

    <!-- 15. Lightbox Modal untuk Review Foto Penuh (Alpine.js) - Uncropped & Segala Ukuran -->
    <div x-show="previewModalPhoto" 
         x-cloak 
         class="fixed inset-0 z-[99999] overflow-y-auto"
         role="dialog" 
         aria-modal="true"
         @keydown.escape.window="previewModalPhoto = null"
         style="display: none;">
        <!-- Backdrop Blur Overlay -->
        <div x-show="previewModalPhoto"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/85 backdrop-blur-sm transition-opacity"
             @click="previewModalPhoto = null"></div>

        <div class="flex min-h-full items-center justify-center p-3 sm:p-5 text-center">
            <div x-show="previewModalPhoto"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative transform overflow-hidden rounded-2xl sm:rounded-3xl bg-white dark:bg-gray-900 text-left shadow-2xl border border-gray-200 dark:border-gray-700 transition-all w-full max-w-2xl p-4 sm:p-5">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800 mb-3">
                    <div class="flex items-center gap-2 min-w-0 pr-2">
                        <span class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white truncate" x-text="previewModalTitle || 'Review Foto Pendukung'"></h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Tampilan ukuran asli tanpa terpotong</p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="previewModalPhoto = null"
                            class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:hover:text-white flex items-center justify-center transition cursor-pointer shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="bg-gray-950/5 dark:bg-black/60 rounded-xl p-2 border border-gray-100 dark:border-gray-800/80 flex items-center justify-center min-h-[220px]">
                    <img :src="previewModalPhoto" alt="Review Foto Pendukung" class="max-h-[75vh] w-auto max-w-full object-contain mx-auto rounded-lg shadow-md">
                </div>

                <div class="mt-3 pt-2.5 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <span class="text-[11px] text-gray-400 dark:text-gray-500">Tekan ESC atau klik luar untuk menutup</span>
                    <button type="button" @click="previewModalPhoto = null" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-semibold transition cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>