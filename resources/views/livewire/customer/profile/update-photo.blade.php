<div>
    <!-- Upload & Crop Photo Modal (Customer) -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
             x-data="{
                imageSrc: null,
                userZoom: 1.0,
                fitScale: 1.0,
                rotation: 0,
                offsetX: 0,
                offsetY: 0,
                isDragging: false,
                startX: 0,
                startY: 0,
                initialOffsetX: 0,
                initialOffsetY: 0,
                isUploading: false,
                cropDiameter: 220,

                get currentRenderScale() {
                    return this.fitScale * this.userZoom;
                },

                onFileSelect(e) {
                    const file = e.target.files?.[0];
                    if (!file) return;
                    if (!file.type.match('image.*')) {
                        alert('Silakan pilih file gambar yang valid (JPG, PNG, JPEG).');
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = (evt) => {
                        this.imageSrc = evt.target.result;
                        this.userZoom = 1.0;
                        this.rotation = 0;
                        this.offsetX = 0;
                        this.offsetY = 0;
                        
                        this.$nextTick(() => {
                            const img = this.$refs.sourceImg;
                            if (img) {
                                img.onload = () => {
                                    const nw = img.naturalWidth || 400;
                                    const nh = img.naturalHeight || 400;
                                    const minDim = Math.min(nw, nh);
                                    this.fitScale = this.cropDiameter / minDim;
                                };
                                if (img.complete) {
                                    img.onload();
                                }
                            }
                        });
                    };
                    reader.readAsDataURL(file);
                },

                startDrag(e) {
                    if (!this.imageSrc) return;
                    this.isDragging = true;
                    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                    this.startX = clientX;
                    this.startY = clientY;
                    this.initialOffsetX = this.offsetX;
                    this.initialOffsetY = this.offsetY;
                },

                onDrag(e) {
                    if (!this.isDragging) return;
                    if (e.cancelable && e.type.startsWith('touch')) {
                        e.preventDefault();
                    }
                    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                    const dx = clientX - this.startX;
                    const dy = clientY - this.startY;
                    this.offsetX = this.initialOffsetX + dx;
                    this.offsetY = this.initialOffsetY + dy;
                },

                endDrag() {
                    this.isDragging = false;
                },

                zoomIn() {
                    this.userZoom = Math.min(3.0, parseFloat((this.userZoom + 0.15).toFixed(2)));
                },

                zoomOut() {
                    this.userZoom = Math.max(0.8, parseFloat((this.userZoom - 0.15).toFixed(2)));
                },

                rotateRight() {
                    this.rotation = (this.rotation + 90) % 360;
                },

                resetCrop() {
                    this.userZoom = 1.0;
                    this.rotation = 0;
                    this.offsetX = 0;
                    this.offsetY = 0;
                },

                clearImage() {
                    this.imageSrc = null;
                    this.isUploading = false;
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                },

                saveCropped() {
                    if (!this.imageSrc || !this.$refs.sourceImg) return;
                    this.isUploading = true;

                    try {
                        const img = this.$refs.sourceImg;
                        const targetSize = 512;
                        const canvas = document.createElement('canvas');
                        canvas.width = targetSize;
                        canvas.height = targetSize;
                        const ctx = canvas.getContext('2d');

                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, targetSize, targetSize);

                        const ratio = targetSize / this.cropDiameter;

                        ctx.save();
                        ctx.translate(targetSize / 2, targetSize / 2);
                        ctx.rotate((this.rotation * Math.PI) / 180);
                        ctx.translate(this.offsetX * ratio, this.offsetY * ratio);
                        
                        const finalCanvasScale = this.currentRenderScale * ratio;
                        ctx.scale(finalCanvasScale, finalCanvasScale);

                        ctx.drawImage(img, -img.naturalWidth / 2, -img.naturalHeight / 2);
                        ctx.restore();

                        const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
                        
                        $wire.saveCroppedPhoto(dataUrl).catch(() => {
                            this.isUploading = false;
                        });
                    } catch (err) {
                        console.error('Crop error:', err);
                        this.isUploading = false;
                        alert('Gagal memproses pemotongan gambar.');
                    }
                }
             }">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-xs transition-opacity" 
                 @click="if(!isUploading) { clearImage(); $wire.closeModal(); }"></div>

            <!-- Modal Card -->
            <div class="relative bg-white dark:bg-gray-900 rounded-3xl w-full max-w-sm sm:max-w-md p-5 sm:p-6 shadow-2xl transform transition-all border border-gray-100 dark:border-gray-800 my-auto z-10 max-h-[92vh] flex flex-col overflow-hidden animate-slide-up sm:animate-none">
                
                <!-- Header -->
                <div class="flex items-center justify-between pb-3.5 border-b border-gray-100 dark:border-gray-800 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-primary-50 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white leading-tight">
                                <span x-show="!imageSrc">Foto Profil</span>
                                <span x-show="imageSrc" x-cloak>Atur Posisi Foto</span>
                            </h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                <span x-show="!imageSrc">Pilih foto terbaik untuk avatar profil Anda</span>
                                <span x-show="imageSrc" x-cloak>Geser & sesuaikan posisi foto di dalam lingkaran</span>
                            </p>
                        </div>
                    </div>
                    <button type="button" 
                            @click="if(!isUploading) { clearImage(); $wire.closeModal(); }"
                            :disabled="isUploading"
                            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Hidden Native File Input -->
                <input type="file" 
                       x-ref="fileInput" 
                       @change="onFileSelect($event)" 
                       accept="image/png, image/jpeg, image/jpg, .png, .jpg, .jpeg" 
                       class="hidden" 
                       id="customerPhotoInput">

                <!-- Modal Body (Scrollable) -->
                <div class="py-4 overflow-y-auto space-y-4 overscroll-contain flex-1">
                    
                    {{-- STATE 1: SEBELUM MEMILIH FOTO --}}
                    <div x-show="!imageSrc" class="space-y-4">
                        <!-- Current Avatar Display -->
                        <div class="text-center py-2">
                            <div class="relative inline-block">
                                @php
                                    $currentUser = auth()->user();
                                    $currentAvatar = $currentUser?->profile_photo ?? $currentUser?->photo;
                                @endphp
                                @if($currentAvatar)
                                    <img src="{{ asset('storage/' . $currentAvatar) }}" 
                                         alt="Current Avatar" 
                                         class="w-24 h-24 rounded-full object-cover mx-auto ring-4 ring-primary-500/20 shadow-lg border-2 border-white dark:border-gray-800">
                                @else
                                    <div class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-white font-bold text-3xl flex items-center justify-center mx-auto ring-4 ring-primary-500/20 shadow-lg border-2 border-white dark:border-gray-800">
                                        {{ strtoupper(substr($currentUser?->name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="absolute -bottom-1 -right-1 p-1 bg-white dark:bg-gray-800 rounded-full shadow-md">
                                    <span class="block w-3.5 h-3.5 rounded-full bg-emerald-500"></span>
                                </div>
                            </div>
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-2">Foto Profil Saat Ini</p>
                        </div>

                        <!-- Dropzone / Picker Button -->
                        <label for="customerPhotoInput"
                               class="flex flex-col items-center justify-center w-full py-8 px-4 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-2xl cursor-pointer hover:border-primary-500 dark:hover:border-primary-500 bg-gray-50/70 dark:bg-gray-800/40 hover:bg-primary-50/30 dark:hover:bg-primary-950/20 transition-all text-center group">
                            <div class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span class="text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-200 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                Ketuk untuk Memilih Foto
                            </span>
                            <span class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">
                                Format PNG, JPG, atau JPEG (Maks. 5MB)
                            </span>
                        </label>

                        <!-- Delete Existing Photo Option -->
                        @if($currentAvatar)
                            <div class="pt-2 border-t border-gray-100 dark:border-gray-800 text-center">
                                <button type="button" 
                                        wire:click="removePhoto" 
                                        wire:confirm="Yakin ingin menghapus foto profil dan kembali menggunakan inisial nama?"
                                        class="text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400 hover:underline inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Hapus Foto Profil Saat Ini</span>
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- STATE 2: CROPPING AREA (PENGATURAN POSISI FOTO DALAM LINGKARAN) --}}
                    <div x-show="imageSrc" x-cloak class="space-y-3.5">
                        
                        <!-- Interactive Crop Viewport -->
                        <div class="relative w-full max-w-[280px] h-[280px] mx-auto bg-gray-950 rounded-2xl overflow-hidden shadow-inner border border-gray-200 dark:border-gray-800 flex items-center justify-center select-none touch-none cursor-grab active:cursor-grabbing"
                             x-ref="viewport"
                             @mousedown="startDrag($event)"
                             @mousemove="onDrag($event)"
                             @mouseup="endDrag()"
                             @mouseleave="endDrag()"
                             @touchstart="startDrag($event)"
                             @touchmove="onDrag($event)"
                             @touchend="endDrag()"
                             @touchcancel="endDrag()">
                            
                            <!-- Source Image with Real-time CSS Transform -->
                            <img :src="imageSrc"
                                 x-ref="sourceImg"
                                 alt="Crop Area"
                                 class="max-w-none pointer-events-none absolute transition-transform duration-75"
                                 :style="`transform: translate(calc(-50% + ${offsetX}px), calc(-50% + ${offsetY}px)) rotate(${rotation}deg) scale(${currentRenderScale}); left: 50%; top: 50%;`">

                            <!-- Circular Crop Mask Overlay (Outside is dimmed) -->
                            <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                                <div class="w-[220px] h-[220px] rounded-full border-2 border-dashed border-white shadow-[0_0_0_9999px_rgba(0,0,0,0.65)] relative ring-2 ring-primary-500/60">
                                    
                                    <!-- Rule of Thirds Crop Grid -->
                                    <div class="absolute inset-0 rounded-full overflow-hidden opacity-25">
                                        <div class="w-full h-full grid grid-cols-3 grid-rows-3 pointer-events-none">
                                            <div class="border-r border-b border-white"></div>
                                            <div class="border-r border-b border-white"></div>
                                            <div class="border-b border-white"></div>
                                            <div class="border-r border-b border-white"></div>
                                            <div class="border-r border-b border-white"></div>
                                            <div class="border-b border-white"></div>
                                            <div class="border-r border-b border-white"></div>
                                            <div class="border-r border-b border-white"></div>
                                            <div></div>
                                        </div>
                                    </div>

                                    <!-- Center Focus Ring -->
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-40">
                                        <div class="w-3 h-3 border border-white rounded-full"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Drag Instruction Pill -->
                            <div class="absolute bottom-2 left-1/2 -translate-x-1/2 pointer-events-none px-2.5 py-1 rounded-full bg-black/70 backdrop-blur-xs text-[10px] font-medium text-white/90 border border-white/10 shrink-0">
                                
                            </div>
                        </div>

                        <!-- Zoom Slider & Controls -->
                        <div class="p-3 bg-gray-50 dark:bg-gray-800/80 rounded-2xl border border-gray-200 dark:border-gray-700/80 space-y-2.5">
                            
                            <!-- Zoom Slider Row -->
                            <div class="flex items-center gap-3">
                                <button type="button" 
                                        @click="zoomOut()" 
                                        class="p-1 text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition cursor-pointer"
                                        title="Perkecil">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                </button>
                                
                                <div class="flex-1 flex items-center gap-2">
                                    <input type="range" 
                                           min="0.8" 
                                           max="3.0" 
                                           step="0.05" 
                                           x-model.number="userZoom" 
                                           class="w-full accent-primary-600 cursor-pointer h-1.5 bg-gray-200 dark:bg-gray-700 rounded-lg">
                                </div>

                                <button type="button" 
                                        @click="zoomIn()" 
                                        class="p-1 text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400 transition cursor-pointer"
                                        title="Perbesar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>

                                <span class="text-[11px] font-bold font-mono text-gray-600 dark:text-gray-300 min-w-[38px] text-right" 
                                      x-text="`${Math.round(userZoom * 100)}%`"></span>
                            </div>

                            <!-- Quick Action Buttons -->
                            <div class="flex items-center justify-center gap-2 pt-1 border-t border-gray-200 dark:border-gray-700/60">
                                <button type="button" 
                                        @click="rotateRight()" 
                                        class="px-2.5 py-1.5 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 text-[11px] font-semibold flex items-center gap-1.5 transition cursor-pointer shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span>Putar 90°</span>
                                </button>

                                <button type="button" 
                                        @click="resetCrop()" 
                                        class="px-2.5 py-1.5 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 text-[11px] font-semibold flex items-center gap-1.5 transition cursor-pointer shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                    <span>Pusatkan</span>
                                </button>

                                <label for="customerPhotoInput" 
                                       class="px-2.5 py-1.5 rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 text-[11px] font-semibold flex items-center gap-1.5 transition cursor-pointer shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Ganti Foto</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    @error('photo')
                        <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-600 dark:text-rose-400 flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>

                <!-- Footer Actions -->
                <div class="pt-3.5 border-t border-gray-100 dark:border-gray-800 flex gap-2.5 shrink-0">
                    <button type="button" 
                            @click="if(imageSrc) { clearImage(); } else { $wire.closeModal(); }"
                            :disabled="isUploading"
                            class="flex-1 py-3 px-4 border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-xl font-bold text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition cursor-pointer text-center">
                        <span x-show="!imageSrc">Tutup</span>
                        <span x-show="imageSrc" x-cloak>Pilih Ulang</span>
                    </button>

                    <button type="button" 
                            x-show="imageSrc"
                            x-cloak
                            @click="saveCropped()"
                            :disabled="isUploading"
                            class="flex-1 py-3 px-4 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 rounded-xl font-bold text-xs text-white transition shadow-md shadow-primary-500/20 disabled:opacity-50 cursor-pointer flex items-center justify-center gap-2">
                        <span x-show="!isUploading">Simpan Foto</span>
                        <span x-show="isUploading" x-cloak class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Menyimpan Foto...</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
