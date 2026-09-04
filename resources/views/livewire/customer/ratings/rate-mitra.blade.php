<div class="mt-2">
    @if(session()->has('message'))
        <div class="p-3 mb-3 text-xs text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="p-3 mb-3 text-xs text-rose-800 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v4a1 1 0 001 1h.01a1 1 0 100-2V7zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($alreadyRated && $userRating)
        {{-- Display already submitted rating by the customer --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/80 rounded-2xl p-4 sm:p-5 shadow-xs">
            <div class="flex items-center justify-between gap-2 mb-2.5 pb-2.5 border-b border-gray-100 dark:border-gray-700/60">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-xs font-bold text-gray-900 dark:text-white">Penilaian Anda untuk Mitra</span>
                </div>
                <div class="flex items-center gap-1 bg-gray-50 dark:bg-gray-750 px-2.5 py-1 rounded-full border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-3.5 h-3.5 {{ $i <= $userRating->rating ? 'text-amber-400 fill-current' : 'text-gray-200 dark:text-gray-700' }}" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <span class="text-xs font-black text-gray-900 dark:text-white ml-1">{{ $userRating->rating }}.0</span>
                </div>
            </div>

            @if(!empty($userRating->review))
                <p class="text-xs text-gray-700 dark:text-gray-300 italic leading-relaxed bg-gray-50/70 dark:bg-gray-750/50 p-3 rounded-xl border border-gray-100 dark:border-gray-700/60">
                    "{{ $userRating->review }}"
                </p>
            @else
                <p class="text-[11px] text-gray-400 dark:text-gray-500 italic">
                    Anda memberikan rating {{ $userRating->rating }} bintang tanpa ulasan tertulis.
                </p>
            @endif

            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-2 text-right">
                Dinilai pada {{ $userRating->created_at ? $userRating->created_at->translatedFormat('d M Y • H:i') : '-' }} WIB
            </p>
        </div>
    @else
        {{-- Form to submit rating --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/80 rounded-2xl p-4 sm:p-5 space-y-3.5 shadow-xs">
            <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-gray-900 dark:text-white block">Beri Penilaian untuk Mitra:</label>
                @if($rating > 0)
                    <span class="text-xs font-black text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-950/60 px-2.5 py-0.5 rounded-full border border-sky-200 dark:border-sky-800">{{ $rating }}.0 dari 5 Bintang</span>
                @endif
            </div>

            <div class="flex items-center gap-2 justify-center py-2 bg-gray-50/70 dark:bg-gray-750/50 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                @for($i = 1; $i <= 5; $i++)
                    <button type="button" wire:click="setRating({{ $i }})" class="p-1 transition-transform hover:scale-125 focus:outline-none cursor-pointer" aria-label="Beri {{ $i }} bintang">
                        @if($rating >= $i)
                            <svg class="w-8 h-8 text-amber-400 fill-current drop-shadow-xs" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @else
                            <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 hover:text-amber-300 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endif
                    </button>
                @endfor
            </div>
            @error('rating')
                <p class="text-xs text-rose-500 text-center">{{ $message }}</p>
            @enderror

            <div>
                <textarea wire:model="review" rows="3" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-750/70 p-3 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-sky-500 focus:outline-none transition" placeholder="Bagikan pengalaman Anda tentang kinerja mitra ini... (opsional)"></textarea>
                <div class="text-[10px] text-gray-400 mt-0.5">Maksimal 500 karakter</div>
            </div>

            <div class="flex items-center justify-between pt-1">
                <button type="button" wire:click="resetForm" class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-650 transition cursor-pointer">
                    Reset
                </button>
                <button wire:click="submitRating" wire:loading.attr="disabled" class="px-4 py-2 text-xs font-bold rounded-xl bg-gradient-to-r from-[#0098e7] to-[#0077cc] hover:from-sky-600 hover:to-blue-700 text-white shadow-xs flex items-center gap-1.5 cursor-pointer transition">
                    <span wire:loading.remove wire:target="submitRating">Kirim Penilaian</span>
                    <span wire:loading wire:target="submitRating" class="inline-flex items-center gap-1.5">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>Menyimpan...</span>
                    </span>
                </button>
            </div>
        </div>
    @endif
</div>
