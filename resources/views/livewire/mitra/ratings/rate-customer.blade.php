<div>
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 dark:bg-green-950/60 border border-green-400 dark:border-green-800 text-green-700 dark:text-green-300 rounded-2xl text-xs">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 dark:bg-red-950/60 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-300 rounded-2xl text-xs">
            {{ session('error') }}
        </div>
    @endif

    @if($inline)
        {{-- Inline rating area used inside help detail modal --}}
        @if($help && !$alreadyRated && in_array($help->status, ['completed', 'selesai']))
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                <h4 class="font-bold text-sm text-gray-900 dark:text-white mb-2">Beri Rating untuk Customer</h4>
                <form wire:submit.prevent="submitRating">
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Rating</label>
                        <div class="flex gap-2">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" wire:click="setRating({{ $i }})" class="focus:outline-none transition transform hover:scale-110">
                                    <svg class="w-8 h-8 {{ $rating >= $i ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                            @endfor
                        </div>
                        @error('rating') <p class="mt-2 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Ulasan (Opsional)</label>
                        <textarea wire:model="review" rows="3" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-yellow-500 outline-none"></textarea>
                        @error('review') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-2">
                        <button type="button" wire:click="closeModal" class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition">Batal</button>
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl text-xs font-bold transition shadow-xs">Kirim Rating</button>
                    </div>
                </form>
            </div>
        @elseif($help && $alreadyRated)
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 p-3 bg-gray-50 dark:bg-gray-800 rounded-xl text-xs text-gray-600 dark:text-gray-300">Anda sudah memberikan rating untuk customer ini</div>
        @endif
    @else
        @if(!$alreadyRated && in_array($help->status, ['completed', 'selesai']))
            <button 
                wire:click="openModal" 
                class="w-full px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded-2xl transition flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span>Beri Rating untuk Customer</span>
            </button>
        @elseif($alreadyRated)
            <div class="p-4 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 rounded-2xl text-center text-xs">
                <svg class="w-8 h-8 mx-auto mb-2 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="font-semibold">Anda sudah memberikan rating untuk customer ini</p>
            </div>
        @else
            <div class="p-4 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 rounded-2xl text-center text-xs">
                <p class="text-xs">Status bantuan: <strong>{{ $help->status }}</strong></p>
                <p class="text-[11px] mt-1 text-blue-600 dark:text-blue-400">Rating hanya tersedia untuk bantuan yang sudah selesai</p>
            </div>
        @endif
    @endif

    <!-- Rating Modal -->
    @if($showModal)
        <div x-data="{ show: @entangle('showModal') }" x-init="document.body.style.overflow = 'hidden'" x-on:close.window="document.body.style.overflow = 'auto'">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/80 backdrop-blur-xs transition-all duration-300" 
                 style="z-index: 9998;"
                 wire:click="closeModal"></div>
            
            <!-- Modal Container -->
            <div class="fixed inset-0 overflow-y-auto flex items-center justify-center p-4" style="z-index: 9999;">
                <div class="relative bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-md w-full p-6 transform transition-all border border-gray-100 dark:border-gray-700">
                    <!-- Close Button -->
                    <button 
                        wire:click="closeModal" 
                        class="absolute top-4 right-4 p-1.5 rounded-full text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <!-- Header -->
                    <div class="mb-5">
                        <h3 class="text-xl font-extrabold text-gray-900 dark:text-white mb-1">Beri Rating Customer</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Bagaimana pengalaman Anda dengan customer ini?</p>
                    </div>

                    <!-- Customer Info -->
                    <div class="mb-5 p-3.5 bg-gray-50 dark:bg-gray-700/60 rounded-2xl border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 bg-primary-600 rounded-xl flex items-center justify-center text-white font-bold text-base shadow-xs flex-shrink-0">
                                {{ substr($help->customer->name ?? 'U', 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-sm text-gray-900 dark:text-white truncate">{{ $help->customer->name ?? 'Customer' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $help->customer->email ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Rating Form -->
                    <form wire:submit.prevent="submitRating">
                        <!-- Star Rating -->
                        <div class="mb-5">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-3 text-center">Pilih Nilai Bintang</label>
                            <div class="flex justify-center gap-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <button 
                                        type="button"
                                        wire:click="setRating({{ $i }})"
                                        class="focus:outline-none transition transform hover:scale-110 cursor-pointer">
                                        <svg class="w-10 h-10 {{ $rating >= $i ? 'text-yellow-400' : 'text-gray-200 dark:text-gray-700' }}" 
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </button>
                                @endfor
                            </div>
                            @error('rating')
                                <p class="mt-2 text-xs text-rose-500 text-center font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Review Text -->
                        <div class="mb-5">
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Ulasan (Opsional)</label>
                            <textarea 
                                wire:model="review"
                                rows="3"
                                placeholder="Ceritakan pengalaman Anda dengan customer ini..."
                                class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-yellow-500 outline-none resize-none"></textarea>
                            <p class="mt-1 text-[11px] text-gray-400">Maksimal 500 karakter</p>
                            @error('review')
                                <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-3">
                            <button 
                                type="button"
                                wire:click="closeModal"
                                class="px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-xl text-xs hover:bg-gray-200 dark:hover:bg-gray-600 transition cursor-pointer">
                                Batal
                            </button>
                            <button 
                                type="submit"
                                class="px-4 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded-xl text-xs transition shadow-xs cursor-pointer">
                                Kirim Rating
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
