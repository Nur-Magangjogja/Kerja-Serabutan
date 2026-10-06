<div class="p-4 sm:p-6 max-w-5xl mx-auto flex flex-col h-[calc(100vh-5rem)]">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-t-xl border border-gray-200 dark:border-gray-700 p-4 shadow-xs flex items-center justify-between gap-3 shrink-0">
        <div class="flex items-center gap-3">
            <a href="{{ route($routePrefix . 'support.index') }}" wire:navigate
                class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-750 transition" title="Kembali">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>

            <div>
                <div class="flex items-center gap-2">
                    <span class="font-bold text-gray-900 dark:text-white text-base">
                        {{ $report->reporter?->name ?? 'Pengguna' }}
                    </span>
                    @if($report->category === 'dari_customer')
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                            Customer
                        </span>
                    @else
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-violet-50 text-violet-700 dark:bg-violet-950/60 dark:text-violet-300 border border-violet-200 dark:border-violet-800">
                            Mitra
                        </span>
                    @endif
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $report->reporter?->district?->name ?? 'Kecamatan' }}, {{ $report->reporter?->city?->name ?? $report->reporter?->city ?? 'Kota' }}
                </div>
            </div>
        </div>

        {{-- Status & Resolution Button --}}
        <div class="flex items-center gap-2">
            @if($report->status === 'pending')
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                    Menunggu Respon
                </span>
            @elseif(in_array($report->status, ['in_progress', 'investigating', 'under_review']))
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                    Sedang Diproses
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                    Selesai
                </span>
            @endif

            @if($report->status !== 'resolved')
                <button type="button" wire:click="resolveTicket" wire:confirm="Tandai percakapan bantuan ini sebagai selesai?"
                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition shadow-xs flex items-center gap-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Tandai Selesai</span>
                </button>
            @else
                <button type="button" wire:click="reopenTicket"
                    class="px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-xs font-semibold transition shadow-xs flex items-center gap-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Buka Kembali</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Messages Container --}}
    <div id="support-messages-scroll" class="flex-1 bg-gray-50 dark:bg-gray-850 border-x border-gray-200 dark:border-gray-700 p-4 overflow-y-auto space-y-3">
        {{-- Initial Issue Description --}}
        @if($report->message)
            <div class="flex justify-center my-2">
                <div class="max-w-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-3 text-xs shadow-2xs text-center text-gray-700 dark:text-gray-300">
                    <div class="font-semibold text-gray-900 dark:text-white mb-1">Pesan Awal Pengguna:</div>
                    <div>{{ $report->message }}</div>
                    <div class="text-[10px] text-gray-400 mt-1">{{ $report->created_at->format('d M Y, H:i') }} WIB</div>
                </div>
            </div>
        @endif

        {{-- Messages Thread --}}
        @forelse($messages as $msg)
            @php
                $isMine = ($msg->sender_id === auth()->id()) || $msg->isFromAdmin();
            @endphp
            <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-sm sm:max-w-md {{ $isMine ? 'bg-primary-600 text-white rounded-2xl rounded-tr-xs' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 rounded-2xl rounded-tl-xs shadow-2xs' }} p-3.5 space-y-1.5">
                    <div class="text-[11px] font-semibold {{ $isMine ? 'text-primary-100' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ $isMine ? 'Admin Wilayah' : ($msg->sender?->name ?? 'Pengguna') }}
                    </div>

                    @if($msg->photo)
                        <div class="mt-1">
                            <img src="{{ asset('storage/' . $msg->photo) }}" alt="Lampiran" class="rounded-lg max-h-48 w-auto object-cover">
                        </div>
                    @endif

                    <div class="text-sm whitespace-pre-wrap break-words leading-relaxed">
                        {{ $msg->message }}
                    </div>

                    <div class="flex items-center justify-end gap-1 text-[10px] {{ $isMine ? 'text-primary-200' : 'text-gray-400' }} pt-1">
                        <span>{{ $msg->created_at->format('H:i') }} WIB</span>
                        @if($isMine)
                            @if($msg->is_read)
                                <svg class="w-3.5 h-3.5 text-primary-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7m-4 6l4 4L23 9" />
                                </svg>
                            @else
                                <svg class="w-3.5 h-3.5 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="py-12 text-center text-gray-400 text-xs">
                Belum ada pesan balasan. Tulis pesan di bawah untuk membalas pengguna.
            </div>
        @endforelse
    </div>

    {{-- Chat Input Bar --}}
    <div class="bg-white dark:bg-gray-800 rounded-b-xl border border-gray-200 dark:border-gray-700 p-3 shadow-xs shrink-0"
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
                    @this.upload('photo', optimized, () => {
                        this.optimizing = false;
                    }, () => {
                        this.optimizing = false;
                        this.compressError = 'Gagal mengunggah foto. Silakan coba lagi.';
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
            <svg class="w-4 h-4 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Mengoptimalkan foto lampiran...</span>
        </div>

        <div x-show="compressError" x-cloak class="mb-2 p-2 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 rounded-lg text-xs text-red-700 dark:text-red-300 flex items-center justify-between">
            <span x-text="compressError"></span>
            <button type="button" @click="compressError = ''" class="text-red-600 hover:text-red-800 font-bold ml-2">&times;</button>
        </div>

        @if($photo)
            <div class="mb-2 p-2 bg-gray-50 dark:bg-gray-750 rounded-lg flex items-center justify-between">
                <span class="text-xs text-gray-700 dark:text-gray-300 truncate max-w-xs">{{ $photo->getClientOriginalName() }}</span>
                <button type="button" wire:click="$set('photo', null)" class="text-rose-600 hover:text-rose-700 text-xs font-semibold cursor-pointer">
                    Hapus
                </button>
            </div>
        @endif

        <form wire:submit.prevent="sendMessage" class="flex items-center gap-2">
            <label class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-750 cursor-pointer transition" title="Lampirkan Gambar">
                <input type="file" accept="image/*" @change="handlePhotoUpload" class="hidden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </label>

            <input type="text"
                wire:model="message"
                placeholder="Ketik balasan untuk {{ $report->reporter?->name ?? 'pengguna' }}..."
                class="flex-1 py-2 px-3 text-sm bg-gray-50 dark:bg-gray-750 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">

            <button type="submit"
                wire:loading.attr="disabled"
                :disabled="optimizing"
                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-semibold shadow-xs transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                <span wire:loading.remove>Kirim</span>
                <span wire:loading>...</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
