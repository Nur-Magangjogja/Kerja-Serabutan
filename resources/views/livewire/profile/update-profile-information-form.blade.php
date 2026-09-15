<div>
    @if(session()->has('message'))
        <div class="mb-5 p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl text-xs sm:text-sm text-emerald-800 dark:text-emerald-200 flex items-center gap-3 shadow-xs animate-in fade-in duration-200">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 flex items-center justify-center flex-shrink-0 text-emerald-600 dark:text-emerald-400">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold">Berhasil!</p>
                <p class="text-xs text-emerald-600 dark:text-emerald-300 mt-0.5">{{ session('message') }}</p>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="updateProfileInformation" class="space-y-4 sm:space-y-5">
        <!-- Nama Lengkap -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    Nama Lengkap
                    <span class="text-red-500">*</span>
                </span>
            </label>
            <input type="text" wire:model="name" required
                oninput="this.value = this.value.replace(/[^a-zA-Z\s\.\'\-]/g, '')"
                class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 dark:focus:ring-primary-950/60 transition shadow-2xs outline-none"
                placeholder="Masukkan nama lengkap">
            @error('name')
                <p class="mt-1.5 text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Alamat Email -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                    </svg>
                    Alamat Email
                    <span class="text-red-500">*</span>
                </span>
            </label>
            <input type="email" wire:model="email" required
                class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 dark:focus:ring-primary-950/60 transition shadow-2xs outline-none"
                placeholder="email@contoh.com">
            @error('email')
                <p class="mt-1.5 text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Nomor Telepon / WhatsApp -->
        <div>
            <label class="block text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                    </svg>
                    Nomor HP / WhatsApp
                    <span class="text-red-500">*</span>
                </span>
            </label>
            <input type="tel" wire:model.blur="phone" required
                inputmode="tel"
                maxlength="18"
                oninput="this.value = this.value.replace(/(?!^\+)[^\d\s\-]/g, '')"
                class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 dark:focus:ring-primary-950/60 transition shadow-2xs outline-none"
                placeholder="08123456789 atau +62812... / +60...">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Format Indonesia (08... / +62...) atau internasional (+...).</p>
            @error('phone')
                <p class="mt-1.5 text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Kota / Kabupaten & Provinsi -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <!-- Kota / Kabupaten (Pencarian Livewire) -->
            <div class="min-w-0 relative" x-data="{ showDropdown: false }" @click.outside="showDropdown = false">
                <label class="block text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        Kota / Kabupaten
                        <span class="text-red-500">*</span>
                    </span>
                </label>
                <div class="relative w-full min-w-0">
                    <input type="text"
                        wire:model.live.debounce.300ms="cityQuery"
                        @focus="showDropdown = true"
                        @input="showDropdown = true"
                        placeholder="Ketik nama Kota / Kabupaten..."
                        class="w-full px-4 py-3 pl-10 pr-10 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 dark:focus:ring-primary-950/60 transition shadow-2xs outline-none truncate">
                    
                    <!-- Search Icon -->
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400 dark:text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Clear or Loading Icon -->
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <div wire:loading wire:target="cityQuery" class="text-primary-500">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        @if(!empty($cityQuery))
                            <button type="button" wire:click="clearCity" wire:loading.remove wire:target="cityQuery" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer p-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        @endif
                    </div>

                    <!-- Search Results Dropdown -->
                    @if(!empty($searchResults))
                        <div x-show="showDropdown"
                            class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto dropdown-scrollbar bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl divide-y divide-gray-100 dark:divide-gray-700/60">
                            @foreach($searchResults as $c)
                                <button type="button"
                                    wire:click="setCityId({{ $c['id'] }})"
                                    @click="showDropdown = false"
                                    class="w-full text-left px-4 py-2.5 hover:bg-primary-50 dark:hover:bg-primary-950/40 text-xs sm:text-sm text-gray-800 dark:text-gray-100 flex items-center justify-between transition-colors cursor-pointer group">
                                    <span class="font-medium group-hover:text-primary-600 dark:group-hover:text-primary-400">{{ $c['name'] }}</span>
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500 font-normal">{{ $c['province'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    @elseif(strlen(trim($cityQuery)) >= 2 && empty($city_id))
                        <div x-show="showDropdown"
                            class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl p-3 text-center text-xs text-gray-400">
                            Kota / Kabupaten tidak ditemukan
                        </div>
                    @endif
                </div>
                <input type="hidden" wire:model="city_id">
                @error('city')
                    <p class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Provinsi -->
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                    Provinsi <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model="province" required
                    class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 dark:focus:ring-primary-950/60 transition shadow-2xs outline-none"
                    placeholder="Nama Provinsi">
                @error('province')
                    <p class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Kecamatan (Wilayah Operasional) -->
        @if(!empty($city_id) && !empty($districtsList))
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Kecamatan Domisili / Operasional
                    </span>
                </label>
                <select wire:model.live="district_id"
                    class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-100 dark:focus:ring-primary-950/60 transition shadow-2xs outline-none cursor-pointer">
                    <option value="">-- Pilih Kecamatan --</option>
                    @foreach($districtsList as $d)
                        <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                    @endforeach
                </select>
                @error('district_id')
                    <p class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <!-- Section: Patokan Tempat / Ciri Rumah Tersimpan -->
        <div class="p-4 bg-gray-50/80 dark:bg-gray-800/60 rounded-2xl border border-gray-200/80 dark:border-gray-700/80 space-y-3.5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400 text-xs">
                        🏠
                    </div>
                    <div>
                        <h3 class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">Patokan Tempat / Ciri Rumah</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Tersedia untuk dipilih otomatis saat membuat bantuan</p>
                    </div>
                </div>
                @if(!$showLandmarkForm)
                    <button type="button" wire:click="openNewLandmarkForm" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-800/60 border border-blue-200 dark:border-blue-800 transition active:scale-95 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Tambah
                    </button>
                @endif
            </div>

            @if(session()->has('landmark_message'))
                <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-xs text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('landmark_message') }}</span>
                </div>
            @endif

            <!-- Form Tambah / Edit Patokan -->
            @if($showLandmarkForm)
                <div class="p-3.5 bg-white dark:bg-gray-800 rounded-xl border border-blue-200 dark:border-blue-800 space-y-3 shadow-xs animate-in fade-in">
                    <div class="flex items-center justify-between text-xs font-bold text-gray-800 dark:text-gray-200 border-b border-gray-100 dark:border-gray-700 pb-2">
                        <span>{{ $editingLandmarkId ? '✏️ Edit Patokan Tempat' : '➕ Tambah Patokan Tempat Baru' }}</span>
                        <button type="button" wire:click="cancelLandmarkForm" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs">✕ Batal</button>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Nama / Label Tempat <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="newLandmarkLabel" placeholder="Contoh: Rumah Utama, Kost Melati, Kantor, Toko"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                        @error('newLandmarkLabel')
                            <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Detail Patokan / Ciri Rumah <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="newLandmarkPatokan" rows="2" placeholder="Contoh: Pagar hitam nomor 12 samping warung Madura, ada pohon mangga di depan"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none resize-none"></textarea>
                        @error('newLandmarkPatokan')
                            <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button" wire:click="cancelLandmarkForm" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="button" wire:click="saveLandmark" class="px-3.5 py-1.5 text-xs font-bold rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition active:scale-95 cursor-pointer shadow-xs">
                            {{ $editingLandmarkId ? 'Simpan Perubahan' : 'Simpan Patokan' }}
                        </button>
                    </div>
                </div>
            @endif

            <!-- List Patokan Tersimpan -->
            @if(!empty($savedLandmarks))
                <div class="space-y-2">
                    @foreach($savedLandmarks as $landmark)
                        <div class="p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200/80 dark:border-gray-700 flex items-start justify-between gap-3 text-xs shadow-2xs group">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 font-bold text-gray-900 dark:text-white">
                                    <span class="text-sm">📍</span>
                                    <span>{{ $landmark['label'] ?? 'Patokan' }}</span>
                                </div>
                                <p class="text-[11px] text-gray-600 dark:text-gray-300 mt-1 leading-relaxed">{{ $landmark['patokan'] ?? '-' }}</p>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button" wire:click="editLandmark('{{ $landmark['id'] ?? '' }}')" class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition cursor-pointer" title="Edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button type="button" wire:click="deleteLandmark('{{ $landmark['id'] ?? '' }}')" wire:confirm="Hapus patokan ini dari profil?" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition cursor-pointer" title="Hapus">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                @if(!$showLandmarkForm)
                    <div class="p-3 bg-white/60 dark:bg-gray-800/40 rounded-xl border border-dashed border-gray-200 dark:border-gray-700 text-center text-xs text-gray-400">
                        Belum ada patokan tersimpan. Klik <strong>+ Tambah</strong> untuk menyimpan patokan rumah/tempat favorit Anda.
                    </div>
                @endif
            @endif
        </div>

        <div class="pt-3">
            <button type="submit" wire:loading.attr="disabled"
                class="w-full text-white font-bold text-xs sm:text-sm py-3.5 rounded-xl transition-all shadow-sm hover:shadow-md active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer bg-gradient-to-r from-[#0098e7] via-[#0077cc] to-[#0060b0] hover:brightness-105">
                <span wire:loading.remove wire:target="updateProfileInformation" class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </span>
                <span wire:loading wire:target="updateProfileInformation" class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Menyimpan...
                </span>
            </button>
        </div>
    </form>
</div>