<div class="p-2 sm:p-4 md:p-6 max-w-5xl mx-auto flex flex-col h-[calc(100dvh-4.5rem)] sm:h-[calc(100vh-5rem)] overflow-hidden">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-t-xl border border-gray-200 dark:border-gray-700 p-2.5 sm:p-4 shadow-xs flex items-center justify-between gap-2 sm:gap-4 shrink-0 relative z-20">
        {{-- Sisi Kiri: Kembali + Profil Pengirim --}}
        <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
            <a href="{{ route($routePrefix . 'support.index') }}" wire:navigate
                class="p-1.5 sm:p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-750 transition shrink-0" title="Kembali">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>

            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5 sm:gap-2 min-w-0">
                    <span class="font-bold text-gray-900 dark:text-white text-xs sm:text-base truncate">
                        {{ $report->reporter?->name ?? 'Pengguna' }}
                    </span>
                    @if($report->category === 'dari_customer')
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] sm:text-[10px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 shrink-0">
                            Customer
                        </span>
                    @else
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] sm:text-[10px] font-semibold bg-violet-50 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300 border border-violet-200 dark:border-violet-800 shrink-0">
                            Mitra
                        </span>
                    @endif
                </div>
                <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                    {{ $report->reporter?->district?->name ?? 'Kecamatan' }}, {{ $report->reporter?->city?->name ?? $report->reporter?->city ?? 'Kota' }}
                </div>
            </div>
        </div>

        {{-- Sisi Kanan: Status & Mute Controls --}}
        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0 justify-end">
            @if($report->isMuted())
                <span class="inline-flex items-center gap-1 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full text-[10px] sm:text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 shrink-0"
                    title="{{ $report->muted_until && $report->muted_until->year < 2050 ? 'Dibisukan hingga ' . $report->muted_until->format('d M Y, H:i') . ' WIB' : 'Dibisukan permanen hingga diaktifkan lagi' }}">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-gray-500 dark:text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                    </svg>
                    <span class="hidden xs:inline sm:inline">Dibisukan</span>
                </span>
            @endif

            @if($report->status === 'pending')
                <span class="inline-flex items-center px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full text-[10px] sm:text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 shrink-0 whitespace-nowrap">
                    Menunggu Respon
                </span>
            @else
                <span class="inline-flex items-center px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full text-[10px] sm:text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 shrink-0 whitespace-nowrap">
                    Sedang Diproses
                </span>
            @endif

            @if($report->isMuted())
                <button type="button" wire:click="unmute"
                    class="px-2.5 sm:px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-[11px] sm:text-xs font-semibold transition shadow-2xs flex items-center gap-1 sm:gap-1.5 cursor-pointer shrink-0 whitespace-nowrap"
                    title="Aktifkan kembali notifikasi dan tanda percakapan ini">
                    <svg class="w-3.5 h-3.5 text-primary-600 dark:text-primary-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                    </svg>
                    <span>Bunyikan</span>
                </button>
            @else
                <div class="relative shrink-0" x-data="{ open: false }">
                    <button type="button" @click="open = !open" @click.outside="open = false"
                        class="px-2.5 sm:px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-[11px] sm:text-xs font-semibold transition shadow-2xs flex items-center gap-1 sm:gap-1.5 cursor-pointer shrink-0 whitespace-nowrap">
                        <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                        </svg>
                        <span>Bisukan</span>
                        <svg class="w-3 h-3 text-gray-400 transition-transform shrink-0" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open" x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-1.5 w-44 sm:w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1.5 z-50 text-xs">
                        <div class="px-3 py-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                            Pilih Durasi Bisu
                        </div>
                        <button type="button" wire:click="mute('1_hour')" @click="open = false"
                            class="w-full text-left px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300">
                            1 Jam
                        </button>
                        <button type="button" wire:click="mute('8_hours')" @click="open = false"
                            class="w-full text-left px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300">
                            8 Jam
                        </button>
                        <button type="button" wire:click="mute('24_hours')" @click="open = false"
                            class="w-full text-left px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300">
                            24 Jam (1 Hari)
                        </button>
                        <button type="button" wire:click="mute('3_days')" @click="open = false"
                            class="w-full text-left px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300">
                            3 Hari
                        </button>
                        <button type="button" wire:click="mute('7_days')" @click="open = false"
                            class="w-full text-left px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300">
                            7 Hari (1 Minggu)
                        </button>
                        <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
                        <button type="button" wire:click="mute('forever')" @click="open = false"
                            class="w-full text-left px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300">
                            Sampai diaktifkan lagi
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Messages Container --}}
    <div id="support-messages-scroll"
        class="flex-1 min-h-0 bg-gray-50 dark:bg-gray-850 border-x border-gray-200 dark:border-gray-700 p-2.5 sm:p-4 overflow-y-auto overflow-x-hidden space-y-2"
        style="overscroll-behavior: contain; -webkit-overflow-scrolling: touch;">

        {{-- Messages Thread --}}
        @forelse($messages as $msg)
            @php
                $isMine = ($msg->sender_id === auth()->id()) || $msg->isFromAdmin();
            @endphp
            <div wire:key="msg-{{ $msg->id }}" class="flex {{ $isMine ? 'justify-end' : 'justify-start' }} my-1">
                <div class="max-w-[85%] sm:max-w-[70%] rounded-2xl p-2.5 sm:p-3 text-xs shadow-2xs {{ $isMine ? 'bg-primary-600 text-white rounded-br-xs' : 'bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 text-gray-900 dark:text-gray-100 rounded-bl-xs' }}">
                    @if($msg->photo)
                        <div class="mb-1.5 rounded-xl overflow-hidden border border-black/10 dark:border-white/10 bg-black/5 dark:bg-black/20">
                            <a href="{{ asset('storage/' . $msg->photo) }}" target="_blank" rel="noopener" class="block group">
                                <img src="{{ asset('storage/' . $msg->photo) }}" alt="Lampiran Foto" class="w-auto h-auto max-w-full max-h-[220px] sm:max-h-[260px] mx-auto object-contain hover:opacity-95 transition cursor-pointer rounded-xl" loading="lazy">
                            </a>
                        </div>
                    @endif

                    @if($msg->message)
                        <p class="text-xs leading-relaxed break-words whitespace-pre-line">{{ $msg->message }}</p>
                    @endif

                    <div class="text-[10px] mt-1 flex items-center justify-end gap-1 {{ $isMine ? 'text-white/80' : 'text-gray-400 dark:text-gray-500' }} select-none">
                        <span>{{ $msg->created_at->format('H:i') }}</span>
                        @if($isMine)
                            @if(!empty($msg->is_read) || !empty($msg->read_at))
                                <span class="text-sky-200 font-bold" title="Dibaca">✓✓</span>
                            @else
                                <span class="text-white/60 font-medium" title="Terkirim">✓</span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="py-12 text-center text-gray-400 text-xs">
                Belum ada pesan obrolan. Tulis pesan di bawah untuk membalas pengguna.
            </div>
        @endforelse
    </div>

    {{-- Chat Input Bar --}}
    <div class="bg-white dark:bg-gray-800 rounded-b-xl border border-gray-200 dark:border-gray-700 p-2.5 sm:p-3 shadow-xs shrink-0 relative z-10"
        x-data="{
            optimizing: false,
            compressError: '',
            async handlePhotoUpload(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.compressError = '';
                this.optimizing = true;
                try {
                    const optimized = await window.MobileImageOptimizer.optimizeImage(file, 'evidence');
                    if (optimized.error || !optimized.file) {
                        this.optimizing = false;
                        this.compressError = optimized.message || 'Gagal memproses foto.';
                        event.target.value = '';
                        return;
                    }
                    @this.upload('photo', optimized.file, () => {
                        this.optimizing = false;
                    }, () => {
                        this.optimizing = false;
                        this.compressError = 'Gagal mengunggah foto. Silakan coba lagi.';
                        event.target.value = '';
                    });
                } catch (err) {
                    this.optimizing = false;
                    this.compressError = err.message || 'Format atau ukuran file tidak didukung.';
                    event.target.value = '';
                }
            }
        }">

        {{-- Optimizing & Error Notices --}}
        <div x-show="optimizing" x-cloak class="mb-2 p-2 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-lg text-xs text-blue-700 dark:text-blue-300 flex items-center gap-2">
            <svg class="w-4 h-4 animate-spin text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="truncate">Mengoptimalkan foto lampiran...</span>
        </div>

        <div x-show="compressError" x-cloak class="mb-2 p-2 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 rounded-lg text-xs text-red-700 dark:text-red-300 flex items-center justify-between gap-2">
            <span class="truncate" x-text="compressError"></span>
            <button type="button" @click="compressError = ''" class="text-red-600 hover:text-red-800 font-bold ml-2 shrink-0">&times;</button>
        </div>

        @if($photo)
            <div class="mb-2 p-2 bg-gray-50 dark:bg-gray-750 rounded-lg flex items-center justify-between gap-2">
                <span class="text-xs text-gray-700 dark:text-gray-300 truncate max-w-xs">{{ $photo->getClientOriginalName() }}</span>
                <button type="button" wire:click="$set('photo', null)" class="text-rose-600 hover:text-rose-700 text-xs font-semibold cursor-pointer shrink-0">
                    Hapus
                </button>
            </div>
        @endif

        <form wire:submit.prevent="sendMessage" class="flex items-center gap-1.5 sm:gap-2">
            <label for="support-chat-photo-input" class="p-2 sm:p-2.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-750 cursor-pointer transition shrink-0" title="Lampirkan Gambar">
                <input type="file" id="support-chat-photo-input" name="photo" accept="image/*" @change="handlePhotoUpload" class="hidden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </label>

            <input type="text"
                id="support-chat-message-input"
                name="message"
                autocomplete="off"
                wire:model="message"
                placeholder="Ketik balasan..."
                class="flex-1 min-w-0 py-2 px-3 text-xs sm:text-sm bg-gray-50 dark:bg-gray-750 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">

            <button type="submit"
                wire:loading.attr="disabled"
                :disabled="optimizing"
                class="px-3.5 sm:px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs sm:text-sm font-semibold shadow-xs transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50 shrink-0 whitespace-nowrap">
                <span wire:loading.remove>Kirim</span>
                <span wire:loading>...</span>
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        const scrollToBottom = () => {
            const container = document.getElementById('support-messages-scroll');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        };
        scrollToBottom();
        Livewire.on('message-sent', scrollToBottom);
        Livewire.on('scroll-chat-bottom', scrollToBottom);
    });
</script>
