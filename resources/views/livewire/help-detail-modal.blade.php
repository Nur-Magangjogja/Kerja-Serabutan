<div>
    @if(!empty($open) && !empty($help))
        <div class="fixed inset-0 z-[9999] flex items-center justify-center">
            <div class="absolute inset-0 bg-black bg-opacity-50" wire:click="close"></div>

            <div class="relative w-full max-w-xl mx-4 bg-white rounded-2xl shadow-xl overflow-hidden max-h-[90vh]">
                <div class="h-full overflow-y-auto">
                    <div class="p-4 border-b">
                        <div class="flex items-start gap-3">
                            <div class="w-16 h-16 flex-shrink-0 overflow-hidden rounded-xl bg-gray-100">
                                @if(!empty($help->photo))
                                    <img src="{{ asset('storage/' . $help->photo) }}" alt="Foto bantuan"
                                        class="w-full h-full object-cover">
                                @elseif(optional($help->user)->photo)
                                    <img src="{{ asset('storage/' . optional($help->user)->photo) }}" alt="Avatar"
                                        class="w-full h-full object-cover">
                                @else
                                    <div
                                        class="w-full h-full flex items-center justify-center text-white text-lg font-bold bg-gradient-to-br from-orange-300 to-orange-400">
                                        {{ strtoupper(substr($help->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $help->title }}</h3>
                                <div class="text-sm text-gray-500">{{ $help->user->name ?? 'Unknown' }} •
                                    {{ $help->created_at->diffForHumans() }}
                                </div>
                            </div>

                            <button wire:click="close" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-4 space-y-3">
                        <div class="text-sm text-gray-700">{{ $help->description }}</div>

                        <div class="flex items-center gap-3">
                            <span class="text-xs px-2 py-1 rounded-md bg-green-50 text-green-700 font-semibold">Rp
                                {{ number_format($help->amount ?? 0, 0, ',', '.') }}</span>

                            <div class="text-xs text-gray-500 flex items-center gap-1">
                                <svg class="w-3 h-3 text-pink-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z" />
                                </svg>
                                <span>{{ $help->city->name ?? '-' }}</span>
                            </div>

                            @if($help->category)
                                <div class="ml-auto text-xs text-gray-500">{{ $help->category->name }}</div>
                            @endif
                        </div>

                        @if($help->location)
                            <div class="text-xs text-gray-500">Lokasi: {{ $help->location }}</div>
                        @endif

                        <div class="grid grid-cols-2 gap-3 mt-2">
                            <div class="text-xs text-gray-600">
                                <div class="font-semibold text-gray-800">Status</div>
                                <div class="mt-1">{{ $help->status }}</div>
                            </div>

                            <div class="text-xs text-gray-600">
                                <div class="font-semibold text-gray-800">Mitra</div>
                                <div class="mt-1">{{ optional($help->mitra)->name ?? 'Belum ada' }}</div>
                            </div>
                        </div>

                        <div class="pt-2 border-t">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-600">ID: {{ $help->id }} • Dibuat:
                                    {{ $help->created_at->format('d M Y H:i') }}
                                </div>
                                <div class="text-sm text-gray-600">Ulasan: {{ $help->ratings->count() ?? 0 }}</div>
                            </div>
                        </div>

                        <div class="pt-3">
                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ auth()->user() && auth()->user()->role === 'mitra' ? route('mitra.reports.create.help', $help->id) : route('customer.reports.create.help', $help->id) }}"
                                    class="block text-center bg-red-500 text-white px-3 py-2 rounded-lg text-sm font-semibold hover:bg-red-600">Laporkan
                                    Bantuan</a>

                                @if(optional($help->user)->id)
                                    <a href="{{ auth()->user() && auth()->user()->role === 'mitra' ? route('mitra.reports.create.user', optional($help->user)->id) : route('customer.reports.create.user', optional($help->user)->id) }}"
                                        class="block text-center bg-red-100 text-red-700 px-3 py-2 rounded-lg text-sm font-semibold hover:bg-red-200">Laporkan
                                        Pengguna</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    @endif
    </div>

    <script>
        // Attach listeners immediately so fallback CustomEvent will be handled
        function handleOpenHelpId(id) {
            // Try emitTo first (targeted), then emit, then try to call on any component
            try {
                if (window.livewire && typeof window.livewire.emitTo === 'function') {
                    window.livewire.emitTo('help-detail-modal', 'openModal', id);
                    return;
                }
            } catch (err) { }

            try {
                if (window.Livewire && typeof window.Livewire.emitTo === 'function') {
                    window.Livewire.emitTo('help-detail-modal', 'openModal', id);
                    return;
                }
            } catch (err) { }

            try {
                if (window.livewire && typeof window.livewire.emit === 'function') {
                    window.livewire.emit('openHelp', id);
                    return;
                }
            } catch (err) { }

            try {
                if (window.Livewire && typeof window.Livewire.emit === 'function') {
                    window.Livewire.emit('openHelp', id);
                    return;
                }
            } catch (err) { }

            // Last resort: try invoking openModal on any livewire component instance
            try {
                if (window.livewire && typeof window.livewire.components === 'object') {
                    Object.values(window.livewire.components).forEach(function (comp) {
                        try { if (comp && typeof comp.call === 'function') comp.call('openModal', id); } catch (er r) { }
                });
            }
        } catch (e rr) { }
    }

        window.addEventListener('openHelpClient', function (e) {
            if (!e || !e.detail || typeof e.detail.id === 'undefined') return;
            handleOpenHelpId(e.detail.id);
        });

        // also support legacy 'openHelp' CustomEvent detail being a plain id
        window.addEventListener('openHelp', function (e) {
            if (!e || typeof e.detail === 'undefined') return;
            handleOpenHelpId(e.detail);
        });
    </script>