<div class="mt-4">
    @if(session()->has('message'))
        <div class="p-3 mb-3 text-sm text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="p-3 mb-3 text-sm text-rose-800 bg-rose-50 border border-rose-200 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($alreadyRated)
        <div class="p-3 bg-emerald-50/80 border border-emerald-200 rounded-lg flex items-center gap-2 text-sm text-emerald-700 font-semibold">
            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Anda sudah memberikan rating & ulasan untuk mitra ini. Terima kasih!</span>
        </div>
    @else
        <div class="space-y-3">
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-200 block">Beri Rating untuk Mitra:</label>

            <div class="flex items-center gap-2">
                @for($i=1;$i<=5;$i++)
                    <button type="button" wire:click="setRating({{ $i }})" class="flex items-center justify-center p-1 rounded-lg transition-transform transform hover:scale-125 focus:outline-none"
                        aria-label="Beri {{ $i }} bintang">
                        @if($rating >= $i)
                            <svg class="w-8 h-8 text-amber-400 drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @else
                            <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 hover:text-amber-200 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endif
                    </button>
                @endfor
                @if($rating > 0)
                    <span class="ml-2 text-sm font-bold text-amber-500">{{ $rating }} / 5 Bintang</span>
                @endif
            </div>
            @error('rating')
                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
            @enderror

            <div class="mt-3">
                <textarea wire:model="review" rows="3" class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3 text-sm focus:ring-2 focus:ring-amber-400 focus:outline-none dark:text-white" placeholder="Bagikan pengalaman Anda tentang kinerja mitra ini... (opsional)"></textarea>
                <div class="text-[11px] text-gray-400 mt-1">Maksimal 500 karakter</div>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <button type="button" wire:click="resetForm" class="px-3.5 py-2 text-xs rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 transition-colors">Reset</button>
                <div class="flex items-center gap-2">
                    <button wire:click="submitRating" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-semibold rounded-xl bg-gradient-to-r from-amber-400 to-yellow-500 hover:from-amber-500 hover:to-yellow-600 text-white shadow-sm flex items-center gap-2 transition-all">
                        <span wire:loading.remove wire:target="submitRating">Kirim Rating</span>
                        <span wire:loading wire:target="submitRating" class="inline-flex items-center gap-1">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Mengirim...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

