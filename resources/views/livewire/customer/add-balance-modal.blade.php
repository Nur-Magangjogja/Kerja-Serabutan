<div x-data="{ open: @entangle('showModal') }">
    <!-- Modal -->
    <div x-show="open" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
        @click="if($event.target === $event.currentTarget) $wire.closeModal()" style="display: none;">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl animate-slide-up" @click.stop>
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">Tambah Saldo</h2>
                <button type="button" @click="$wire.closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6">

                <!-- Success Message -->
                @if(session('success'))
                    <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200">
                        <p class="text-green-700 text-sm">{{ session('success') }}</p>
                    </div>
                @endif

                <!-- Error Message -->
                @if(session('error'))
                    <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200">
                        <p class="text-red-700 text-sm">{{ session('error') }}</p>
                    </div>
                @endif

                <!-- Form -->
                <form wire:submit.prevent="addBalance" class="space-y-4">
                    <!-- Amount Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jumlah Saldo <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span
                                class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-600 font-medium">Rp</span>
                            <input type="number" wire:model.live="amount" placeholder="0" min="1" step="1000"
                                class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400 @error('amount') border-red-500 @enderror" />
                        </div>
                        @error('amount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Keterangan <span class="text-gray-400">(Opsional)</span>
                        </label>
                        <input type="text" wire:model="description" placeholder="Contoh: Topup untuk bantuan..."
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-400" />
                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="$wire.closeModal()"
                            class="flex-1 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="flex-1 py-2.5 rounded-lg bg-blue-500 text-white font-medium hover:bg-blue-600 transition disabled:opacity-50">
                            <span wire:loading.remove>Tambah Saldo</span>
                            <span wire:loading>Processing...</span>
                        </button>
                    </div>

                    <!-- Info Text -->
                    <p class="text-xs text-gray-500 text-center mt-3">
                        Saldo akan langsung tersedia setelah transaksi berhasil diproses.
                    </p>
                </form>
            </div>
        </div>
    </div>

    <!-- Styles -->
    <style>
        @keyframes slide-up {
            from {
                transform: scale(0.95);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .animate-slide-up {
            animation: slide-up 0.2s ease-out;
        }
    </style>
</div>