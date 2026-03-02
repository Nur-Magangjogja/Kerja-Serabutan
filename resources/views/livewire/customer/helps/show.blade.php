<div class="min-h-screen bg-gray-50">
    <div class="max-w-md mx-auto pb-24">
        <div class="bg-white rounded-2xl overflow-hidden shadow-md mt-6">
            @if($help->photo)
                <img src="{{ asset('storage/' . $help->photo) }}" alt="Foto bantuan" class="w-full h-56 object-cover">
            @endif

            <div class="p-4">
                <h1 class="text-lg font-bold text-gray-900">{{ $help->title }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $help->created_at->diffForHumans() }} •
                    {{ $help->city->name ?? '—' }}</p>

                <div class="mt-4 text-gray-700 text-sm leading-relaxed">
                    {{ $help->description }}
                </div>

                <div class="mt-4 text-sm text-gray-600">
                    <div><strong>Nominal:</strong> Rp {{ number_format($help->amount, 0, ',', '.') }}</div>
                    @if($help->location)
                        <div class="mt-1"><strong>Lokasi:</strong> {{ $help->location }}</div>
                    @endif
                    @if($help->user)
                        <div class="mt-1"><strong>Pengaju:</strong> {{ $help->user->name }}</div>
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
                        class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm text-center">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>