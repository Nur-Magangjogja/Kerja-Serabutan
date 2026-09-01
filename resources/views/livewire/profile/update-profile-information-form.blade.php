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
            <input type="tel" wire:model="phone" required
                class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 dark:focus:ring-primary-950/60 transition shadow-2xs outline-none"
                placeholder="081234567890">
            @error('phone')
                <p class="mt-1.5 text-xs text-red-500 dark:text-red-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- RT & RW -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                    RT <span class="text-red-500">*</span>
                </label>
                <input type="number" min="1" max="999" wire:model="rt" required
                    class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 dark:focus:ring-primary-950/60 transition shadow-2xs outline-none"
                    placeholder="Contoh: 01">
                @error('rt')
                    <p class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                    RW <span class="text-red-500">*</span>
                </label>
                <input type="number" min="1" max="999" wire:model="rw" required
                    class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 dark:focus:ring-primary-950/60 transition shadow-2xs outline-none"
                    placeholder="Contoh: 05">
                @error('rw')
                    <p class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Kelurahan / Desa & Kecamatan -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <div>
                <label class="block text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                    Kelurahan / Desa <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model="kelurahan" required
                    class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 dark:focus:ring-primary-950/60 transition shadow-2xs outline-none"
                    placeholder="Nama Kelurahan/Desa">
                @error('kelurahan')
                    <p class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                    Kecamatan <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model="kecamatan" required
                    class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-100 dark:focus:ring-primary-950/60 transition shadow-2xs outline-none"
                    placeholder="Nama Kecamatan">
                @error('kecamatan')
                    <p class="mt-1.5 text-xs text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Kota / Kabupaten & Provinsi -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <!-- Kota / Kabupaten (Pencarian Livewire) -->
            <div class="min-w-0 relative" x-data="{ open: false }" @click.outside="open = false">
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
                        @focus="open = true"
                        @input="open = true"
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
                        <div x-show="open"
                            class="absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl divide-y divide-gray-100 dark:divide-gray-700/60">
                            @foreach($searchResults as $c)
                                <button type="button"
                                    wire:click="setCityId({{ $c['id'] }})"
                                    @click="open = false"
                                    class="w-full text-left px-4 py-2.5 hover:bg-primary-50 dark:hover:bg-primary-950/40 text-xs sm:text-sm text-gray-800 dark:text-gray-100 flex items-center justify-between transition-colors cursor-pointer group">
                                    <span class="font-medium group-hover:text-primary-600 dark:group-hover:text-primary-400">{{ $c['name'] }}</span>
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500 font-normal">{{ $c['province'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    @elseif(strlen(trim($cityQuery)) >= 2 && empty($city_id))
                        <div x-show="open"
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


        <!-- Tombol Simpan -->
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