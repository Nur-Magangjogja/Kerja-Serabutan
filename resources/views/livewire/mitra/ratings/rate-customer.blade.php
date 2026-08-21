<div>
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    @if($inline)
        {{-- Inline rating area used inside help detail modal --}}
        @if($help && !$alreadyRated && in_array($help->status, ['completed', 'selesai']))
            <div class="mt-4 pt-3 border-t">
                <h4 class="font-semibold text-sm mb-2">Beri Rating untuk Customer</h4>
                <form wire:submit.prevent="submitRating">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Rating</label>
                        <div class="flex gap-2">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" wire:click="setRating({{ $i }})" class="focus:outline-none">
                                    <svg class="w-8 h-8 {{ $rating >= $i ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                            @endfor
                        </div>
                        @error('rating') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Ulasan (Opsional)</label>
                        <textarea wire:model="review" rows="3" class="w-full px-3 py-2 border border-gray-200 rounded-lg"></textarea>
                        @error('review') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-2">
                        <button type="button" wire:click="closeModal" class="flex-1 px-3 py-2 bg-gray-100 rounded-lg">Batal</button>
                        <button type="submit" class="flex-1 px-3 py-2 bg-yellow-500 text-white rounded-lg">Kirim Rating</button>
                    </div>
                </form>
            </div>
        @elseif($help && $alreadyRated)
            <div class="mt-4 pt-3 border-t p-3 bg-gray-50 rounded-lg text-sm text-gray-600">Anda sudah memberikan rating untuk customer ini</div>
        @endif
    @else
        @if(!$alreadyRated && in_array($help->status, ['completed', 'selesai']))
            <button 
                wire:click="openModal" 
                class="w-full px-6 py-3 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                Beri Rating untuk Customer
            </button>
        @elseif($alreadyRated)
            <div class="p-4 bg-gray-100 border border-gray-300 text-gray-600 rounded-lg text-center">
                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="font-semibold">Anda sudah memberikan rating untuk customer ini</p>
            </div>
        @else
            <div class="p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg text-center">
                <p class="text-sm">Status bantuan: <strong>{{ $help->status }}</strong></p>
                <p class="text-xs mt-1">Rating hanya tersedia untuk bantuan yang sudah selesai</p>
            </div>
        @endif
    @endif

    <!-- Rating Modal -->
    @if($showModal)
        <div x-data="{ show: @entangle('showModal') }" x-init="document.body.style.overflow = 'hidden'" x-on:close.window="document.body.style.overflow = 'auto'">
            <!-- Backdrop - Covers entire viewport -->
            <div class="fixed inset-0 bg-black/80 transition-all duration-300" 
                 style="z-index: 9998;"
                 wire:click="closeModal"></div>
            
            <!-- Modal Container -->
            <div class="fixed inset-0 overflow-y-auto flex items-center justify-center p-4" style="z-index: 9999;">
                <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-6 transform transition-all">
                    <!-- Close Button -->
                    <button 
                        wire:click="closeModal" 
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <!-- Header -->
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Beri Rating Customer</h3>
                        <p class="text-sm text-gray-600">Bagaimana pengalaman Anda dengan customer ini?</p>
                    </div>

                    <!-- Customer Info -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-primary-600 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                                {{ substr($help->customer->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $help->customer->name ?? 'Unknown' }}</p>
                                <p class="text-sm text-gray-500">{{ $help->customer->email ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Rating Form -->
                    <form wire:submit.prevent="submitRating">
                        <!-- Star Rating -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Rating</label>
                            <div class="flex justify-center gap-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <button 
                                        type="button"
                                        wire:click="setRating({{ $i }})"
                                        class="focus:outline-none transition transform hover:scale-110">
                                        <svg class="w-12 h-12 {{ $rating >= $i ? 'text-yellow-400' : 'text-gray-300' }}" 
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </button>
                                @endfor
                            </div>
                            @error('rating')
                                <p class="mt-2 text-sm text-red-600 text-center">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Review Text -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Ulasan (Opsional)</label>
                            <textarea 
                                wire:model="review"
                                rows="4"
                                placeholder="Ceritakan pengalaman Anda dengan customer ini..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent resize-none"></textarea>
                            <p class="mt-1 text-xs text-gray-500">Maksimal 500 karakter</p>
                            @error('review')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3">
                            <button 
                                type="button"
                                wire:click="closeModal"
                                class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                                Batal
                            </button>
                            <button 
                                type="submit"
                                class="flex-1 px-4 py-3 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition">
                                Kirim Rating
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Script to hide bottom nav when modal is open -->
    @if($showModal)
        <script>
            // Hide bottom navigation
            document.querySelectorAll('nav.fixed.bottom-0').forEach(nav => {
                nav.style.display = 'none';
            });
        </script>
    @else
        <script>
            // Show bottom navigation
            document.querySelectorAll('nav.fixed.bottom-0').forEach(nav => {
                nav.style.display = '';
            });
        </script>
    @endif
</div>

