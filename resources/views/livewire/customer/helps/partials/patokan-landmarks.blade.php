<!-- Patokan & Detail Khusus Tempat (Opsional + Preset dari Profil) -->
<div id="group-full-address" class="space-y-2">
    <div class="flex items-center justify-between flex-wrap gap-1">
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">
            <span class="flex items-center">
                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                </svg>
                Detail Patokan Tempat / Ciri Rumah
                <span class="text-gray-400 dark:text-gray-500 text-xs ml-1 font-normal">(Opsional)</span>
            </span>
        </label>
        <a href="{{ route('profile.edit') }}" target="_blank" class="text-[11px] font-medium text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-0.5">
            ⚙️ Kelola di Profil
        </a>
    </div>

    <!-- Preset Patokan Tersimpan dari Profil Customer -->
    @if(!empty($savedLandmarks))
        <div class="space-y-1">
            <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1">
                <span>Pilih dari Patokan Profil:</span>
            </span>
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-hide flex-wrap">
                @foreach($savedLandmarks as $lm)
                    @php
                        $isActive = trim((string)$full_address) === trim((string)($lm['patokan'] ?? ''));
                    @endphp
                    <button type="button"
                        wire:click="applySavedLandmark(@js($lm['patokan'] ?? ''))"
                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-lg transition active:scale-95 cursor-pointer border {{ $isActive ? 'bg-blue-600 text-white font-bold border-blue-600 shadow-xs' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:border-blue-300' }}">
                        <span>📍</span>
                        <span>{{ $lm['label'] ?? 'Patokan' }}</span>
                        @if($isActive)
                            <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </button>
                @endforeach
                @if(!empty($full_address))
                    <button type="button" wire:click="$set('full_address', '')" class="text-[11px] text-gray-400 hover:text-red-500 px-1 py-0.5 transition cursor-pointer">
                        ✕ Kosongkan
                    </button>
                @endif
            </div>
        </div>
    @endif

    <textarea wire:model.live="full_address" rows="2"
        placeholder="Contoh: Pagar hitam samping toko kelontong Bu Siti, gang melati no. 4"
        class="w-full px-4 py-2.5 text-sm rounded-lg border @error('full_address') border-red-500 ring-1 ring-red-500 bg-red-50/20 dark:bg-red-950/20 @else border-gray-300 dark:border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @enderror transition resize-none bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500"></textarea>

    <!-- Quick Save Landmark to Profile -->
    @if(!empty(trim((string)$full_address)))
        @php
            $alreadySaved = collect($savedLandmarks)->contains(function($item) use ($full_address) {
                return trim((string)($item['patokan'] ?? '')) === trim((string)$full_address);
            });
        @endphp

        @if(!$alreadySaved)
            <div class="pt-0.5" x-data="{ openSaveInput: false }">
                <div x-show="!openSaveInput">
                    <button type="button" @click="openSaveInput = true" class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-700 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        <span>💾 Simpan patokan ini ke profil agar bisa dipakai lagi</span>
                    </button>
                </div>
                <div x-show="openSaveInput" x-cloak class="p-2.5 bg-blue-50/70 dark:bg-blue-950/30 rounded-lg border border-blue-200 dark:border-blue-800 flex items-center gap-2 flex-wrap text-xs">
                    <input type="text" wire:model="newLandmarkLabel" placeholder="Nama/Label (cth: Rumah Utama, Kost)"
                        class="flex-1 min-w-[140px] px-2.5 py-1.5 text-xs rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white outline-none focus:border-blue-500">
                    <button type="button" wire:click="saveCurrentPatokanToProfile" class="px-3 py-1.5 bg-blue-600 text-white font-bold rounded-md hover:bg-blue-700 transition active:scale-95 cursor-pointer shadow-2xs">
                        Simpan
                    </button>
                    <button type="button" @click="openSaveInput = false" class="px-2 py-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 cursor-pointer">
                        Batal
                    </button>
                </div>
            </div>
        @endif
    @endif

    @if(session()->has('landmark_saved'))
        <div class="p-2 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-lg text-xs text-emerald-700 dark:text-emerald-300 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 flex-shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('landmark_saved') }}</span>
        </div>
    @endif

    @error('full_address')
        <span class="field-error-message text-red-500 dark:text-red-400 text-xs mt-1 block flex items-center font-medium">
            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            {{ $message }}
        </span>
    @enderror
</div>
