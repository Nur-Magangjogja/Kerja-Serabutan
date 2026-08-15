@php
    $title = 'Identitas Aplikasi';
    $breadcrumb = 'Super Admin / Pengaturan / Identitas Aplikasi';
@endphp

<div class="py-2">
    <!-- Sub-navigation tabs -->
    <x-superadmin-settings-nav />

    <!-- Notifikasi Sukses -->
    @if (session('message'))
        <div id="alert-message" class="mb-6 flex items-center p-4 text-emerald-800 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 shadow-sm transition-all duration-300" role="alert">
            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 flex items-center justify-center mr-3 flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="text-sm font-medium flex-1">
                {{ session('message') }}
            </div>
            <button type="button" onclick="document.getElementById('alert-message').remove()" class="ml-auto -mx-1.5 -my-1.5 bg-emerald-50 text-emerald-500 rounded-lg focus:ring-2 focus:ring-emerald-400 p-1.5 hover:bg-emerald-100 dark:bg-transparent dark:text-emerald-400 dark:hover:bg-emerald-900/50 inline-flex h-8 w-8 transition">
                <span class="sr-only">Close</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Form Pengaturan (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 sm:p-8 transition-colors duration-200">
                <div class="flex items-center gap-3 pb-5 border-b border-gray-100 dark:border-gray-700 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Identitas & Branding</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Ubah nama, slogan, dan logo resmi aplikasi.</p>
                    </div>
                </div>

                <form wire:submit.prevent="save" class="space-y-6">
                    <!-- Nama Aplikasi -->
                    <div>
                        <label for="app_name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Nama Aplikasi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="app_name" 
                                wire:model.live.debounce.300ms="app_name"
                                placeholder="Contoh: SayaBantu" 
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm transition font-medium"
                                required
                            />
                        </div>
                        @error('app_name')
                            <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">Nama ini akan tampil di seluruh sidebar, header, judul tab browser, dan email sistem.</p>
                    </div>

                    <!-- Slogan / Tagline -->
                    <div>
                        <label for="app_tagline" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Slogan / Tagline
                        </label>
                        <input 
                            type="text" 
                            id="app_tagline" 
                            wire:model.live.debounce.300ms="app_tagline"
                            placeholder="Contoh: Platform Layanan & Bantuan Serabutan" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm transition"
                        />
                        @error('app_tagline')
                            <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi Singkat -->
                    <div>
                        <label for="app_description" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Deskripsi Singkat Aplikasi
                        </label>
                        <textarea 
                            id="app_description" 
                            wire:model.live.debounce.300ms="app_description"
                            rows="2" 
                            placeholder="Deskripsi singkat mengenai layanan aplikasi..."
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700/50 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm transition"
                        ></textarea>
                        @error('app_description')
                            <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Upload Logo Aplikasi -->
                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Logo Aplikasi (Icon / Simbol)
                        </label>
                        
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            <!-- Thumbnail Preview -->
                            <div class="w-20 h-20 rounded-2xl bg-gray-50 dark:bg-gray-900/80 border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center overflow-hidden flex-shrink-0 p-2 relative shadow-inner">
                                @if ($logo)
                                    <img src="{{ $logo->temporaryUrl() }}" alt="Preview Logo Baru" class="w-full h-full object-contain" />
                                @elseif ($current_logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($current_logo))
                                    <img src="{{ asset('storage/' . $current_logo) }}" alt="Logo Saat Ini" class="w-full h-full object-contain" />
                                @else
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 via-purple-600 to-primary-600 flex items-center justify-center text-white shadow-sm">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                @endif

                                <div wire:loading wire:target="logo" class="absolute inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center rounded-2xl">
                                    <svg class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Upload Button & Actions -->
                            <div class="flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-indigo-50 dark:bg-indigo-900/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 rounded-xl text-xs font-semibold transition shadow-xs border border-indigo-200 dark:border-indigo-800/60">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                        <span>Pilih Gambar Logo</span>
                                        <input type="file" wire:model="logo" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="hidden" />
                                    </label>

                                    @if ($current_logo || $logo)
                                        <button 
                                            type="button" 
                                            wire:click="removeLogo" 
                                            wire:confirm="Yakin ingin menghapus logo kustom dan kembali ke emblem logo default?"
                                            class="inline-flex items-center px-3 py-2 bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/60 text-red-600 dark:text-red-400 rounded-xl text-xs font-semibold transition border border-red-200 dark:border-red-800/60"
                                        >
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Hapus Logo
                                        </button>
                                    @endif
                                </div>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500 leading-normal">Rekomendasi rasio 1:1 (persegi) atau PNG transparan. Maksimal 2MB (PNG, JPG, SVG, WEBP).</p>
                            </div>
                        </div>
                        @error('logo')
                            <p class="text-xs text-red-500 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Upload Favicon -->
                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Favicon (Ikon Tab Browser)
                        </label>
                        
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-gray-50 dark:bg-gray-900/80 border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center overflow-hidden flex-shrink-0 p-1.5 relative shadow-inner">
                                @if ($favicon)
                                    <img src="{{ $favicon->temporaryUrl() }}" alt="Preview Favicon" class="w-full h-full object-contain" />
                                @elseif ($current_favicon && \Illuminate\Support\Facades\Storage::disk('public')->exists($current_favicon))
                                    <img src="{{ asset('storage/' . $current_favicon) }}" alt="Favicon Saat Ini" class="w-full h-full object-contain" />
                                @else
                                    <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                @endif
                            </div>

                            <div class="flex-1 space-y-1.5">
                                <div class="flex items-center gap-2">
                                    <label class="cursor-pointer inline-flex items-center px-3.5 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-semibold transition border border-gray-300 dark:border-gray-600">
                                        <span>Pilih Favicon</span>
                                        <input type="file" wire:model="favicon" accept="image/png,image/x-icon,image/svg+xml,image/jpeg" class="hidden" />
                                    </label>
                                    @if ($current_favicon || $favicon)
                                        <button type="button" wire:click="removeFavicon" class="text-xs text-red-500 hover:text-red-700 underline font-medium">Hapus</button>
                                    @endif
                                </div>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500">Ikon kecil ukuran 32x32 atau 64x64 px.</p>
                            </div>
                        </div>
                        @error('favicon')
                            <p class="text-xs text-red-500 mt-1.5 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-700">
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-700 hover:to-indigo-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-primary-500/20 transition-all duration-200 disabled:opacity-50"
                        >
                            <svg wire:loading wire:target="save,logo,favicon" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Pratinjau Interaktif (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-colors duration-200 sticky top-24">
                <div class="flex items-center gap-2 pb-4 border-b border-gray-100 dark:border-gray-700 mb-5">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Pratinjau Live</h3>
                </div>

                <!-- Simulation 1: Sidebar Brand Header Light Mode -->
                <div class="space-y-4">
                    <div>
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Tampilan Sidebar (Mode Terang)</span>
                        <div class="p-4 rounded-xl bg-white border border-gray-200 shadow-xs flex items-center gap-3">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" alt="Preview" class="w-10 h-10 rounded-xl object-contain p-1 bg-white shadow-sm border border-gray-100" />
                            @elseif ($current_logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($current_logo))
                                <img src="{{ asset('storage/' . $current_logo) }}" alt="Logo" class="w-10 h-10 rounded-xl object-contain p-1 bg-white shadow-sm border border-gray-100" />
                            @else
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 via-purple-600 to-primary-600 flex items-center justify-center text-white shadow-md shadow-purple-500/25">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="text-base font-black text-gray-900 tracking-tight leading-tight truncate">
                                    {{ $app_name ?: 'Nama Aplikasi' }}
                                </div>
                                <div class="mt-0.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 uppercase tracking-wider">
                                        Super Admin
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Simulation 2: Sidebar Brand Header Dark Mode -->
                    <div>
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Tampilan Sidebar (Mode Gelap)</span>
                        <div class="p-4 rounded-xl bg-gray-900 border border-gray-700 shadow-xs flex items-center gap-3">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" alt="Preview" class="w-10 h-10 rounded-xl object-contain p-1 bg-gray-800 shadow-sm border border-gray-700" />
                            @elseif ($current_logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($current_logo))
                                <img src="{{ asset('storage/' . $current_logo) }}" alt="Logo" class="w-10 h-10 rounded-xl object-contain p-1 bg-gray-800 shadow-sm border border-gray-700" />
                            @else
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 via-purple-600 to-primary-600 flex items-center justify-center text-white shadow-md shadow-purple-500/25">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="text-base font-black text-white tracking-tight leading-tight truncate">
                                    {{ $app_name ?: 'Nama Aplikasi' }}
                                </div>
                                <div class="mt-0.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-indigo-900/50 text-indigo-300 border border-indigo-800/60 uppercase tracking-wider">
                                        Super Admin
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Simulation 3: Browser Tab Mockup -->
                    <div>
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Simulasi Tab Browser</span>
                        <div class="rounded-xl bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-2.5 flex items-center gap-2">
                            <div class="w-4 h-4 flex items-center justify-center flex-shrink-0">
                                @if ($favicon)
                                    <img src="{{ $favicon->temporaryUrl() }}" alt="Favicon" class="w-4 h-4 object-contain" />
                                @elseif ($current_favicon && \Illuminate\Support\Facades\Storage::disk('public')->exists($current_favicon))
                                    <img src="{{ asset('storage/' . $current_favicon) }}" alt="Favicon" class="w-4 h-4 object-contain" />
                                @elseif ($logo)
                                    <img src="{{ $logo->temporaryUrl() }}" alt="Logo" class="w-4 h-4 object-contain" />
                                @elseif ($current_logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($current_logo))
                                    <img src="{{ asset('storage/' . $current_logo) }}" alt="Logo" class="w-4 h-4 object-contain" />
                                @else
                                    <div class="w-3.5 h-3.5 rounded bg-primary-600"></div>
                                @endif
                            </div>
                            <span class="text-xs text-gray-700 dark:text-gray-300 font-medium truncate">
                                {{ $app_name ?: 'SayaBantu' }} - Dashboard
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
