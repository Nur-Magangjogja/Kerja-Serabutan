<div>
    @if ($showModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto" id="vehicle-profile-modal" data-vehicle-modal="true" aria-labelledby="modal-title" role="dialog" aria-modal="true"
             x-data="{
                 isOptimizingSim: false,
                 isOptimizingStnk: false,
                 simError: '',
                 stnkError: '',
                 async handleSimUpload(e) {
                     const file = e.target.files?.[0];
                     if (!file) return;
                     this.simError = '';
                     this.isOptimizingSim = true;
                     try {
                         let uploadFile = file;
                         if (window.MobileImageOptimizer && typeof window.MobileImageOptimizer.optimizeImage === 'function') {
                             const result = await window.MobileImageOptimizer.optimizeImage(file, 'document');
                             if (result.error || !result.file) {
                                 this.simError = result.message || 'Gagal memproses foto SIM. Silakan gunakan format JPG atau PNG.';
                                 if (e.target) e.target.value = '';
                                 return;
                             }
                             uploadFile = result.file;
                         } else if (file.size > 2048 * 1024) {
                             this.simError = 'Ukuran file asli melebihi batas 2MB.';
                             if (e.target) e.target.value = '';
                             return;
                         }
                         await new Promise((resolve, reject) => {
                             @this.upload('new_sim_photo', uploadFile, resolve, reject);
                         });
                     } catch (err) {
                         this.simError = err?.message || 'Gagal memproses foto SIM. Silakan coba lagi.';
                         if (e.target) e.target.value = '';
                     } finally {
                         this.isOptimizingSim = false;
                     }
                 },
                 async handleStnkUpload(e) {
                     const file = e.target.files?.[0];
                     if (!file) return;
                     this.stnkError = '';
                     this.isOptimizingStnk = true;
                     try {
                         let uploadFile = file;
                         if (window.MobileImageOptimizer && typeof window.MobileImageOptimizer.optimizeImage === 'function') {
                             const result = await window.MobileImageOptimizer.optimizeImage(file, 'document');
                             if (result.error || !result.file) {
                                 this.stnkError = result.message || 'Gagal memproses foto STNK. Silakan gunakan format JPG atau PNG.';
                                 if (e.target) e.target.value = '';
                                 return;
                             }
                             uploadFile = result.file;
                         } else if (file.size > 2048 * 1024) {
                             this.stnkError = 'Ukuran file asli melebihi batas 2MB.';
                             if (e.target) e.target.value = '';
                             return;
                         }
                         await new Promise((resolve, reject) => {
                             @this.upload('new_stnk_photo', uploadFile, resolve, reject);
                         });
                     } catch (err) {
                         this.stnkError = err?.message || 'Gagal memproses foto STNK. Silakan coba lagi.';
                         if (e.target) e.target.value = '';
                     } finally {
                         this.isOptimizingStnk = false;
                     }
                 },
                 init() {
                     this.$dispatch('vehicle-modal-opened');
                 },
                 destroy() {
                     this.$dispatch('vehicle-modal-closed');
                 }
             }">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-zinc-900/70 backdrop-blur-sm transition-opacity" wire:click="closeModal" @click="$dispatch('vehicle-modal-closed')"></div>

            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-zinc-200 dark:border-zinc-800">
                    
                    {{-- Modal Header --}}
                    <div class="px-6 py-5 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between bg-zinc-50/50 dark:bg-zinc-800/30">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xl">
                                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-zinc-900 dark:text-white" id="modal-title">
                                    Kelengkapan Data Kendaraan
                                </h3>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Khusus untuk mengakses jenis layanan Antar & Jemput
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeModal" @click="$dispatch('vehicle-modal-closed')" class="rounded-lg p-1.5 text-zinc-400 hover:text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <form wire:submit.prevent="save" class="p-6 space-y-5 max-h-[80vh] overflow-y-auto">
                        
                        {{-- Flash Message --}}
                        @if (session()->has('success_vehicle'))
                            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs flex items-start space-x-2">
                                <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>{{ session('success_vehicle') }}</span>
                            </div>
                        @endif

                        {{-- Status Banner --}}
                        @php
                            $badge = auth()->user()?->vehicle_status_badge ?? [
                                'label' => 'Belum Dilengkapi', 'color' => 'zinc',
                                'bg' => 'bg-zinc-100 dark:bg-zinc-800/60', 'text' => 'text-zinc-600 dark:text-zinc-400',
                                'border' => 'border-zinc-200 dark:border-zinc-700'
                            ];
                        @endphp
                        <div class="p-4 rounded-xl border {{ $badge['bg'] }} {{ $badge['border'] }} flex items-start justify-between">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Status Verifikasi:</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold {{ $badge['text'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </div>
                                @if ($verification_status === 'verified')
                                    <p class="text-xs text-emerald-700 dark:text-emerald-300">
                                        Selamat! Data kendaraan Anda telah diverifikasi. Anda dapat melihat dan mengambil pekerjaan Antar & Jemput.
                                    </p>
                                @elseif ($verification_status === 'pending')
                                    <p class="text-xs text-amber-700 dark:text-amber-300">
                                        Data kendaraan Anda sedang ditinjau oleh Admin. Layanan Antar & Jemput akan aktif setelah diverifikasi.
                                    </p>
                                @elseif ($verification_status === 'rejected')
                                    <div class="mt-2 p-2.5 rounded-lg bg-rose-100/70 dark:bg-rose-900/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-800 dark:text-rose-200">
                                        <strong>Alasan Penolakan:</strong> {{ $rejection_reason ?? 'Dokumen tidak valid atau foto kurang jelas. Silakan periksa dan unggah ulang.' }}
                                    </div>
                                @else
                                    <p class="text-xs text-zinc-600 dark:text-zinc-400">
                                        Lengkapi Plat Nomor + (SIM Motor atau STNK) untuk membuka akses ke layanan Antar & Jemput.
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Notice: Pekerjaan Biasa Tetap Terbuka --}}
                        <div class="p-3.5 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200/70 dark:border-blue-800/50 flex items-start space-x-2.5">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-xs text-blue-900 dark:text-blue-300 space-y-0.5">
                                <p class="font-semibold">Info Layanan SayaBantu:</p>
                                <p class="text-blue-700 dark:text-blue-400">
                                    Layanan <strong>Kerja Serabutan / Bantuan Biasa</strong> tetap dapat Anda ambil tanpa perlu mengisi data kendaraan ini. Data kendaraan hanya diwajibkan khusus untuk <strong>Layanan Antar & Jemput</strong> demi keamanan bersama.
                                </p>
                            </div>
                        </div>

                        {{-- Section 1: Plat Nomor Kendaraan (Wajib) --}}
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                Nomor Plat Kendaraan <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text"
                                    wire:model.defer="vehicle_plate_number"
                                    placeholder="Contoh: AB 1234 CD atau B 1234 ABC"
                                    class="w-full px-3.5 py-2.5 text-sm uppercase tracking-wider font-mono rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition">
                            </div>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">
                                Masukkan nomor plat motor aktif yang akan Anda gunakan.
                            </p>
                            @error('vehicle_plate_number')
                                <p class="text-xs text-rose-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Section 2: Dokumen Legalitas (SIM Motor & STNK - Keduanya Wajib) --}}
                        <div class="space-y-1.5 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                Dokumen Legalitas Pengemudi & Kendaraan <span class="text-rose-500">*</span>
                            </label>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                Untuk keamanan layanan Antar & Jemput, Anda <strong>wajib melengkapi kedua dokumen</strong> di bawah ini (SIM Motor dan STNK aktif):
                            </p>
                        </div>

                        {{-- Dokumen 1: SIM Motor (Wajib) --}}
                        <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/20 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 flex items-center space-x-1.5">
                                    <svg class="w-4 h-4 text-zinc-600 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                    <span>1. Data SIM Motor (SIM C) <span class="text-rose-500">*</span></span>
                                </span>
                                @if ($current_sim_photo)
                                    <span class="text-[11px] px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 font-medium">Foto Tersimpan</span>
                                @endif
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[11px] font-medium text-zinc-600 dark:text-zinc-400">Nomor SIM C <span class="text-rose-500">*</span></label>
                                    <span class="text-[10px] text-zinc-400 font-medium">Maks. 14 karakter</span>
                                </div>
                                <input type="text"
                                    wire:model.defer="vehicle_sim_number"
                                    maxlength="14"
                                    placeholder="Masukkan 14 digit nomor SIM C"
                                    class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-mono">
                                @error('vehicle_sim_number')
                                    <p class="text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[11px] font-medium text-zinc-600 dark:text-zinc-400">Foto Fisik SIM C <span class="text-rose-500">*</span></label>
                                
                                @if ($current_sim_photo && !$new_sim_photo)
                                    <div class="mb-2 relative rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 max-w-xs">
                                        <img src="{{ asset('storage/' . $current_sim_photo) }}" alt="Foto SIM" class="w-full h-28 object-cover">
                                    </div>
                                @endif

                                @if ($new_sim_photo)
                                    <div class="mb-2 relative rounded-lg overflow-hidden border border-amber-300 max-w-xs">
                                        <img src="{{ $new_sim_photo->temporaryUrl() }}" alt="Preview SIM" class="w-full h-28 object-cover">
                                        <span class="absolute top-1 right-1 bg-amber-500 text-white text-[10px] px-1.5 py-0.5 rounded">Baru</span>
                                    </div>
                                @endif

                                <input type="file"
                                    accept="image/*"
                                    @change="handleSimUpload($event)"
                                    class="w-full text-xs text-zinc-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 dark:file:bg-zinc-700 dark:file:text-zinc-200">
                                
                                <div x-show="isOptimizingSim" class="text-xs text-amber-600 dark:text-amber-400 font-medium">
                                    Mengoptimalkan foto SIM...
                                </div>
                                <div x-show="simError" x-text="simError" class="text-xs text-rose-500"></div>

                                <p class="text-[10px] text-zinc-400">Pastikan nama & nomor SIM terbaca dengan jelas (Maks. 2MB).</p>
                                @error('new_sim_photo')
                                    <p class="text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Dokumen 2: STNK (Wajib) --}}
                        <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/20 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 flex items-center space-x-1.5">
                                    <svg class="w-4 h-4 text-zinc-600 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>2. Data STNK Kendaraan <span class="text-rose-500">*</span></span>
                                </span>
                                @if ($current_stnk_photo)
                                    <span class="text-[11px] px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 font-medium">Foto Tersimpan</span>
                                @endif
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[11px] font-medium text-zinc-600 dark:text-zinc-400">Nomor STNK <span class="text-rose-500">*</span></label>
                                    <span class="text-[10px] text-zinc-400 font-medium">Maks. 8 karakter</span>
                                </div>
                                <input type="text"
                                    wire:model.defer="vehicle_stnk_number"
                                    maxlength="8"
                                    placeholder="Masukkan 8 karakter nomor STNK"
                                    class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 uppercase font-mono">
                                @error('vehicle_stnk_number')
                                    <p class="text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[11px] font-medium text-zinc-600 dark:text-zinc-400">Foto Fisik STNK <span class="text-rose-500">*</span></label>
                                
                                @if ($current_stnk_photo && !$new_stnk_photo)
                                    <div class="mb-2 relative rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 max-w-xs">
                                        <img src="{{ asset('storage/' . $current_stnk_photo) }}" alt="Foto STNK" class="w-full h-28 object-cover">
                                    </div>
                                @endif

                                @if ($new_stnk_photo)
                                    <div class="mb-2 relative rounded-lg overflow-hidden border border-amber-300 max-w-xs">
                                        <img src="{{ $new_stnk_photo->temporaryUrl() }}" alt="Preview STNK" class="w-full h-28 object-cover">
                                        <span class="absolute top-1 right-1 bg-amber-500 text-white text-[10px] px-1.5 py-0.5 rounded">Baru</span>
                                    </div>
                                @endif

                                <input type="file"
                                    accept="image/*"
                                    @change="handleStnkUpload($event)"
                                    class="w-full text-xs text-zinc-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 dark:file:bg-zinc-700 dark:file:text-zinc-200">
                                
                                <div x-show="isOptimizingStnk" class="text-xs text-amber-600 dark:text-amber-400 font-medium">
                                    Mengoptimalkan foto STNK...
                                </div>
                                <div x-show="stnkError" x-text="stnkError" class="text-xs text-rose-500"></div>

                                <p class="text-[10px] text-zinc-400">Foto bagian identitas kendaraan & nomor plat (Maks. 2MB).</p>
                                @error('new_stnk_photo')
                                    <p class="text-xs text-rose-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Section 3: Informasi Tambahan Motor (Opsional) --}}
                        <div class="pt-2 border-t border-zinc-100 dark:border-zinc-800 space-y-3">
                            <span class="block text-xs font-bold text-zinc-700 dark:text-zinc-300">
                                Informasi Tambahan Kendaraan (Opsional)
                            </span>

                            <div class="grid grid-cols-3 gap-2">
                                <div class="space-y-1">
                                    <label class="block text-[11px] text-zinc-500 dark:text-zinc-400">Merek</label>
                                    <input type="text"
                                        wire:model.defer="vehicle_brand"
                                        placeholder="Contoh: Honda"
                                        class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white">
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-[11px] text-zinc-500 dark:text-zinc-400">Tipe / Model</label>
                                    <input type="text"
                                        wire:model.defer="vehicle_model"
                                        placeholder="Contoh: Vario 125"
                                        class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white">
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-[11px] text-zinc-500 dark:text-zinc-400">Warna</label>
                                    <input type="text"
                                        wire:model.defer="vehicle_color"
                                        placeholder="Contoh: Hitam"
                                        class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white">
                                </div>
                            </div>
                        </div>

                        {{-- Notice: Verifikasi Ulang bila edit --}}
                        @if ($is_verified)
                            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 text-[11px] text-amber-800 dark:text-amber-300 flex items-center space-x-2">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Menyimpan perubahan data akan me-reset status verifikasi ke <strong>Menunggu Verifikasi</strong> sampai diperiksa kembali oleh Admin.</span>
                            </div>
                        @endif

                        {{-- Modal Footer --}}
                        <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-end space-x-3">
                            <button type="button" wire:click="closeModal" @click="$dispatch('vehicle-modal-closed')" class="px-4 py-2 text-xs font-semibold text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-md hover:shadow-lg transition flex items-center space-x-1.5 disabled:opacity-50">
                                <span wire:loading.remove wire:target="save">Kirim Data Kendaraan</span>
                                <span wire:loading wire:target="save">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
