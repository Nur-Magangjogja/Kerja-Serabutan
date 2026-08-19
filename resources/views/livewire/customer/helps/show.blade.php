<div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors">
    <div class="max-w-md mx-auto pb-24">
        <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-md mt-6 border border-gray-100 dark:border-gray-700/60">
            @if($help->photo)
                <img src="{{ asset('storage/' . $help->photo) }}" alt="Foto bantuan" class="w-full h-56 object-cover">
            @endif

            <div class="p-4">
                <h1 class="text-lg font-bold text-gray-900 dark:text-white">{{ $help->title }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $help->created_at->diffForHumans() }} •
                    {{ $help->city->name ?? '—' }}</p>

                <div class="mt-4 text-gray-700 dark:text-gray-300 text-sm leading-relaxed">
                    {{ $help->description }}
                </div>

                <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    <div><strong class="text-gray-900 dark:text-white">Nominal:</strong> Rp {{ number_format($help->amount, 0, ',', '.') }}</div>
                    @if($help->location)
                        <div class="mt-1"><strong class="text-gray-900 dark:text-white">Lokasi:</strong> {{ $help->location }}</div>
                    @endif
                    @if($help->user)
                        <div class="mt-1"><strong class="text-gray-900 dark:text-white">Pengaju:</strong> {{ $help->user->name }}</div>
                    @endif
                </div>

                <div class="flex gap-2 mt-6">
                    @if($help->user_id === auth()->id())
                        <button wire:click="$emit('confirm-delete', {{ $help->id }})"
                            class="flex-1 px-3 py-2 bg-red-500 text-white rounded-lg text-sm">Hapus</button>
                    @else
                        <button class="flex-1 px-3 py-2 bg-primary-500 text-white rounded-lg text-sm">Hubungi</button>
                    @endif

                    <a href="{{ route('customer.helps.index') }}"
                        class="flex-1 px-3 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-sm text-center hover:bg-gray-200 dark:hover:bg-gray-600 transition">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>