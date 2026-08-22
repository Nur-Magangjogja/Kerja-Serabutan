<div class="mt-2">
    @if(session()->has('message'))
        <div class="p-3 mb-3 text-xs text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="p-3 mb-3 text-xs text-rose-800 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-xl flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v4a1 1 0 001 1h.01a1 1 0 100-2V7zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($alreadyRated && $userRating)
        {{-- Display already submitted rating by the customer --}}
        <div class="bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/50 rounded-2xl p-4">
            <div class="flex items-center justify-between gap-2 mb-2 pb-2 border-b border-amber-200/50 dark:border-amber-800/40">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-xs font-bold text-gray-800 dark:text-gray-100">Penilaian Anda untuk Mitra</span>
                </div>
                <div class="flex items-center gap-1 bg-white/70 dark:bg-gray-800 px-2 py-0.5 rounded-full border border-amber-200/60 dark:border-amber-700/50">
                    @for($i = 1; $i <= 5; $i++)
                        <svg class="w-3.5 h-3.5 {{ $i <= $userRating->rating ? 'text-yellow-400 fill-current' : 'text-gray-300 dark:text-gray-600' }}" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                    <span class="text-xs font-extrabold text-amber-600 dark:text-amber-400 ml-0.5">{{ $userRating->rating }}.0</span>
                </div>
            </div>

            @if(!empty($userRating->review))
                <p class="text-xs text-gray-700 dark:text-gray-200 italic leading-relaxed">
                    "{{ $userRating->review }}"
                </p>
            @else
                <p class="text-[11px] text-gray-400 dark:text-gray-500 italic">
                    Anda memberikan rating bintang {{ $userRating->rating }} tanpa ulasan teks.
                </p>
            @endif

            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-2 text-right">
                Dinilai pada {{ $userRating->created_at ? $userRating->created_at->format('d M Y • H:i') : '-' }}
            </p>
        </div>
    @else
        {{-- Form to submit rating --}}
        <div class="bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/70 rounded-2xl p-4 space-y-3">
            <div class="flex items-center justify-between">
                <label class="text-xs font-bold text-gray-800 dark:text-gray-100 block">Beri Penilaian untuk Mitra:</label>
                @if($rating > 0)
                    <span class="text-xs font-bold text-amber-500">{{ $rating }} dari 5 Bintang</span>
                @endif
            </div>

            <div class="flex items-center gap-2 justify-center py-1">
                @for($i = 1; $i <= 5; $i++)
                    <button type="button" wire:click="setRating({{ $i }})" class="p-1 transition-transform hover:scale-125 focus:outline-none cursor-pointer" aria-label="Beri {{ $i }} bintang">
                        @if($rating >= $i)
                            <svg class="w-8 h-8 text-yellow-400 fill-current drop-shadow-xs" viewBox="0 0 20 20">
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
                <textarea wire:model="review" rows="3" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3 text-xs text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-amber-400 focus:outline-none" placeholder="Bagikan pengalaman Anda tentang kinerja mitra ini... (opsional)"></textarea>
                <div class="text-[10px] text-gray-400 mt-0.5">Maksimal 500 karakter</div>
            </div>

            <div class="flex items-center justify-between pt-1">
                <button type="button" wire:click="resetForm" class="px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 cursor-pointer">
                    Reset
                </button>
                <button wire:click="submitRating" wire:loading.attr="disabled" class="px-4 py-2 text-xs font-bold rounded-xl bg-amber-500 hover:bg-amber-600 text-white shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <span wire:loading.remove wire:target="submitRating">Kirim Penilaian</span>
                    <span wire:loading wire:target="submitRating" class="inline-flex items-center gap-1">
                        <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>Menyimpan...</span>
                    </span>
                </button>
            </div>
        </div>
    @endif
</div>
