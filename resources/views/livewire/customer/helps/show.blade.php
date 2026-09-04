<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors">
    <div class="max-w-md mx-auto px-4 py-6 pb-24 space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-3xl overflow-hidden shadow-xs border border-gray-100 dark:border-gray-700/70">
            @if($help->photo)
                <div class="relative bg-gray-900/10 max-h-64 overflow-hidden flex items-center justify-center border-b border-gray-100 dark:border-gray-700">
                    <img src="{{ asset('storage/' . $help->photo) }}" alt="Foto bantuan" class="w-full h-auto max-h-64 object-contain">
                </div>
            @endif

            <div class="p-5 space-y-4">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-1.5 flex-wrap">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200/60 dark:border-sky-800">
                            {{ $help->category->name ?? 'Permintaan Jasa' }}
                        </span>
                        <span class="text-base font-black text-sky-600 dark:text-sky-400">
                            Rp {{ number_format($help->amount, 0, ',', '.') }}
                        </span>
                    </div>
                    <h1 class="text-lg font-bold text-gray-900 dark:text-white leading-snug">{{ $help->title }}</h1>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $help->created_at->diffForHumans() }}</span>
                        <span>•</span>
                        <span>{{ $help->city->name ?? '-' }}</span>
                    </p>
                </div>

                @if(!empty($help->description))
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700/60">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1.5">Deskripsi Bantuan</h3>
                        <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed bg-gray-50/70 dark:bg-gray-750/50 p-3.5 rounded-2xl border border-gray-100 dark:border-gray-700/60 whitespace-pre-line">{{ $help->description }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                    @if($help->location)
                        <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/60">
                            <span class="text-[10px] text-gray-400 font-semibold block mb-0.5">Lokasi:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 truncate block">{{ $help->location }}</span>
                        </div>
                    @endif
                    @if($help->user)
                        <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/60">
                            <span class="text-[10px] text-gray-400 font-semibold block mb-0.5">Pengaju:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 truncate block">{{ $help->user->name }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex gap-2.5 pt-2 border-t border-gray-100 dark:border-gray-700/60">
                    @if($help->user_id === auth()->id())
                        <button wire:click="$emit('confirm-delete', {{ $help->id }})"
                            class="flex-1 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Hapus</span>
                        </button>
                    @else
                        <a href="{{ route('customer.chat', $help->id) }}" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-sky-600 to-[#0077cc] hover:from-sky-700 hover:to-[#0060b0] text-white rounded-xl text-xs font-bold transition shadow-xs text-center flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <span>Hubungi</span>
                        </a>
                    @endif

                    <a href="{{ route('customer.helps.index') }}"
                        class="flex-1 px-4 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold text-center hover:bg-gray-200 dark:hover:bg-gray-600 transition cursor-pointer">
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>