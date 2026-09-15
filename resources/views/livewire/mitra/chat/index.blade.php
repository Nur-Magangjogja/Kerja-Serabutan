<div class="h-full flex-1 min-h-0 flex flex-col overflow-hidden bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100" @if($selected_partner_id) wire:poll.2500ms.visible @else wire:poll.6s.visible @endif style="overscroll-behavior: none; overscroll-behavior-y: none;">
    {{-- CASE 1: DAFTAR PERCAKAPAN (INBOX LIST) --}}
    @if(!$selected_partner_id)
        <!-- Top Header Section for Inbox List -->
        <div class="shrink-0 px-4 pt-4 pb-4 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] shadow-sm text-white select-none rounded-b-2xl">
            <!-- Decorative Ambient Glows -->
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full -mr-12 -mt-12 blur-xl pointer-events-none"></div>

            <div class="relative z-10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('mitra.dashboard') }}" aria-label="Kembali ke Beranda" class="p-2 -ml-1 hover:bg-white/20 rounded-xl transition cursor-pointer flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-base font-bold text-white leading-tight">Pesan Masuk</h1>
                        <p class="text-[11px] text-white/80">Percakapan Anda dengan Customer</p>
                    </div>
                </div>

                <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Search & List Body -->
        <div class="flex-1 min-h-0 flex flex-col overflow-hidden px-4 pt-3 pb-3">
            <!-- Search Input -->
            <div class="shrink-0 mb-3 relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama customer..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 border border-gray-200/80 dark:border-gray-700 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 outline-none text-xs sm:text-sm transition shadow-xs">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Scrollable Conversation List -->
            <div class="flex-1 min-h-0 overflow-y-auto space-y-2.5 pr-0.5 custom-scrollbar pb-28" style="overscroll-behavior: contain; overscroll-behavior-y: contain; -webkit-overflow-scrolling: touch; touch-action: pan-y;">
                @if($conversations && $conversations->count() > 0)
                    @foreach($conversations as $conv)
                        @if($conv->is_admin ?? false)
                            <!-- Card Percakapan Khusus Tim Admin -->
                            <button wire:key="conv-admin" wire:click="selectAdmin"
                                class="w-full p-3.5 rounded-2xl transition-all text-left bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-700/80 hover:bg-blue-50/50 dark:hover:bg-blue-950/40 shadow-xs hover:shadow-sm flex items-center gap-3.5 cursor-pointer">
                                <div class="w-11 h-11 rounded-2xl bg-sky-500 text-white flex-shrink-0 flex items-center justify-center font-bold text-lg shadow-xs border border-sky-400">
                                    🛡️
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1 mb-0.5">
                                        <div class="flex items-center gap-1.5 truncate">
                                            <h3 class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white truncate">Tim Admin SayaBantu</h3>
                                            <span class="text-[9px] px-1.5 py-0.5 rounded font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 uppercase">Resmi</span>
                                        </div>
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 flex-shrink-0">
                                            {{ $conv->last_message ? $conv->last_message->created_at->diffForHumans(null, true, true) : '' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-1 leading-relaxed">
                                        @if($conv->last_message)
                                            {{ $conv->last_message->message }}
                                        @else
                                            <span class="text-blue-600 dark:text-amber-400 italic">Pusat Layanan Bantuan & Moderasi Mitra</span>
                                        @endif
                                    </p>
                                </div>
                                @if($conv->unread_count > 0)
                                    <div class="w-5 h-5 rounded-full bg-amber-600 text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0 shadow-xs animate-pulse">
                                        {{ $conv->unread_count }}
                                    </div>
                                @endif
                            </button>
                        @else
                            <button wire:key="conv-customer-{{ $conv->partner->id }}" wire:click="selectPartner({{ $conv->partner->id }})"
                                class="w-full p-3.5 rounded-2xl transition-all text-left bg-white dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700/80 hover:border-blue-200 dark:hover:border-blue-500/40 hover:bg-blue-50/20 dark:hover:bg-gray-750 shadow-xs hover:shadow-sm flex items-center gap-3.5 cursor-pointer">
                                
                                <!-- Avatar -->
                                <div class="w-11 h-11 rounded-2xl overflow-hidden bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-950 dark:to-teal-950 flex-shrink-0 flex items-center justify-center text-emerald-700 dark:text-emerald-300 font-bold text-sm shadow-xs border border-emerald-200/50 dark:border-emerald-800/50">
                                    @if($conv->partner->profile_photo ?? $conv->partner->photo)
                                        <img src="{{ asset('storage/' . ($conv->partner->profile_photo ?? $conv->partner->photo)) }}" alt="{{ $conv->partner->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr($conv->partner->name ?? 'C', 0, 1)) }}
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1 mb-0.5">
                                        <h3 class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white truncate">{{ $conv->partner->name }}</h3>
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 flex-shrink-0">
                                            {{ $conv->last_message ? $conv->last_message->created_at->diffForHumans(null, true, true) : '' }}
                                        </span>
                                    </div>

                                    <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-1 leading-relaxed">
                                        @if($conv->last_message)
                                            @if($conv->last_message->sender_type === 'mitra')
                                                <span class="text-gray-400 dark:text-gray-500 font-medium">Anda: </span>
                                            @endif
                                            @if($conv->last_message->photo)
                                                <span class="inline-flex items-center gap-0.5 text-blue-600 dark:text-blue-400 font-medium"> Foto bukti • </span>
                                            @endif
                                            {{ $conv->last_message->message }}
                                        @elseif($conv->latest_help)
                                            <span class="text-blue-600 dark:text-blue-400 italic">Tugas: {{ $conv->latest_help->title }}</span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500 italic">Mulai percakapan...</span>
                                        @endif
                                    </p>

                                    @if($conv->latest_help && in_array($conv->latest_help->status, ['taken', 'partner_on_the_way', 'partner_arrived', 'in_progress', 'waiting_customer_confirmation']))
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-900/60">
                                                ● {{ $conv->latest_help->status_label }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Unread Badge -->
                                @if($conv->unread_count > 0)
                                    <div class="w-5 h-5 rounded-full bg-blue-600 dark:bg-blue-500 text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0 shadow-xs animate-pulse">
                                        {{ $conv->unread_count }}
                                    </div>
                                @endif
                            </button>
                        @endif
                    @endforeach
                @else
                    <div class="text-center py-14 px-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs">
                        <div class="w-14 h-14 mx-auto bg-gray-50 dark:bg-gray-700/50 rounded-full flex items-center justify-center text-gray-400 dark:text-gray-500 mb-3 border border-gray-100 dark:border-gray-700">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200">Belum Ada Percakapan</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-xs mx-auto">Percakapan akan muncul otomatis ketika Anda mengambil tugas bantuan dari customer.</p>
                    </div>
                @endif
            </div>
        </div>

    {{-- CASE 2: RUANG OBROLAN AKTIF (CHAT ROOM) --}}
    @else
        <!-- Single Unified Top Chat Header -->
        <div class="shrink-0 px-3.5 py-3 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] shadow-sm text-white select-none z-20" style="touch-action: none;">
            <!-- Ambient Glow -->
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full -mr-12 -mt-12 blur-xl pointer-events-none"></div>

            <div class="relative z-10 flex items-center justify-between gap-2.5">
                <!-- Left: Back Button + Avatar + Name -->
                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                    <button wire:click="closeChat" aria-label="Kembali ke Daftar Percakapan"
                        class="p-1.5 -ml-1 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    @if($selected_partner->is_admin ?? false)
                        <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-base shadow-xs flex-shrink-0 border border-amber-400">
                            🛡️
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5">
                                <h2 class="font-bold text-sm text-white truncate leading-tight">Tim Admin SayaBantu</h2>
                                <span class="text-[8px] px-1.5 py-0.2 rounded font-extrabold bg-amber-300 text-amber-950 uppercase">Resmi</span>
                            </div>
                            <p class="text-[10px] text-white/80 truncate">Pusat Layanan Bantuan & Moderasi</p>
                        </div>
                    @else
                        <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0 bg-white/20 flex items-center justify-center text-white font-bold text-xs border border-white/40 shadow-xs">
                            @if($selected_partner->profile_photo ?? $selected_partner->photo)
                                <img src="{{ asset('storage/' . ($selected_partner->profile_photo ?? $selected_partner->photo)) }}" alt="{{ $selected_partner->name }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($selected_partner->name ?? 'C', 0, 1)) }}
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="font-bold text-sm text-white truncate leading-tight">{{ $selected_partner->name }}</h2>
                            <p class="text-[10px] text-white/80">Customer</p>
                        </div>
                    @endif
                </div>

                <!-- Right: Action Buttons -->
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    @if($active_help)
                        <a href="{{ route('mitra.helps.detail', $active_help->id) }}" class="px-2.5 py-1 bg-white/15 hover:bg-white/25 text-white text-[11px] font-bold rounded-lg transition border border-white/20 shadow-xs" title="Rincian Tugas">
                            Tugas
                        </a>
                    @endif
                    @if(!($selected_partner->is_admin ?? false))
                        <a href="{{ route('mitra.reports.create.user', $selected_partner->id) }}" class="px-2.5 py-1 bg-rose-500/80 hover:bg-rose-600 text-white text-[11px] font-bold rounded-lg transition border border-rose-400/50 shadow-xs" title="Laporkan">
                            Lapor
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Messages Stream Feed -->
        <div id="messagesWrapper"
             x-data="{
                 scrollToBottom(smooth = false) {
                     this.$nextTick(() => {
                         this.$el.scrollTo({
                             top: this.$el.scrollHeight,
                             behavior: smooth ? 'smooth' : 'instant'
                         });
                     });
                 },
                 isNearBottom() {
                     return this.$el.scrollHeight - this.$el.scrollTop - this.$el.clientHeight < 160;
                 },
                 init() {
                     this.scrollToBottom(false);
                     setTimeout(() => this.scrollToBottom(false), 80);
                     const observer = new MutationObserver(() => {
                         if (this.isNearBottom()) {
                             this.scrollToBottom(true);
                         }
                     });
                     observer.observe(this.$el, { childList: true });
                 }
             }"
             x-on:scroll-chat-bottom.window="scrollToBottom(false)"
             x-on:message-sent.window="scrollToBottom(true)"
             class="flex-1 min-h-0 overflow-y-auto p-3.5 sm:p-4 space-y-3 bg-slate-100/70 dark:bg-gray-900/90 custom-scrollbar"
             style="overscroll-behavior: contain; overscroll-behavior-y: contain; -webkit-overflow-scrolling: touch; touch-action: pan-y;">
            @if($messages && $messages->count() > 0)
                @php $lastHelpContextId = null; @endphp
                @foreach($messages as $msg)
                    {{-- Context Separator jika berpindah bantuan --}}
                    @if($msg->help_id && $msg->help_id !== $lastHelpContextId && $msg->help)
                        @php $lastHelpContextId = $msg->help_id; @endphp
                        <div wire:key="ctx-sep-{{ $msg->help_id }}" class="flex items-center justify-center my-3">
                            <div class="bg-blue-50 dark:bg-blue-950/60 border border-blue-100 dark:border-blue-900/80 px-3 py-1 rounded-full text-[11px] text-blue-700 dark:text-blue-300 font-semibold shadow-xs flex items-center gap-1">
                                <span>📌 {{ Str::limit($msg->help->title, 35) }}</span>
                            </div>
                        </div>
                    @endif

                    @if($msg->sender_type === 'system' || str_starts_with($msg->message, 'Sistem SayaBantu:'))
                        <div wire:key="msg-sys-{{ $msg->id }}" class="flex justify-center my-3">
                            <div class="w-full max-w-lg bg-blue-50/90 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/80 p-3.5 rounded-2xl shadow-xs text-center">
                                <div class="flex items-center justify-center gap-1.5 mb-1 text-blue-800 dark:text-blue-300 font-bold text-xs uppercase tracking-wider">
                                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Pemberitahuan Sistem SayaBantu</span>
                                </div>
                                <p class="text-xs text-blue-950 dark:text-blue-100 whitespace-pre-line leading-relaxed font-medium">
                                    {{ str_replace('Sistem SayaBantu: ', '', $msg->message) }}
                                </p>
                                <div class="text-[10px] text-blue-600/80 dark:text-blue-400 mt-1.5 text-center">
                                    {{ $msg->created_at->format('d M Y, H:i') }} WIB
                                </div>
                            </div>
                        </div>
                    @elseif(str_contains($msg->message, '🛡️') || str_contains($msg->message, '[Pesan Resmi Admin') || str_contains($msg->message, '[Sistem Moderasi'))
                        <div wire:key="msg-adm-{{ $msg->id }}" class="flex justify-center my-3">
                            <div class="w-full max-w-lg bg-gradient-to-br from-amber-50 to-amber-100/70 dark:from-amber-950/60 dark:to-gray-800 p-4 rounded-2xl border-2 border-amber-300 dark:border-amber-700 shadow-xs">
                                <div class="flex items-center gap-1.5 mb-1.5 text-amber-900 dark:text-amber-200">
                                    <span class="text-base">🛡️</span>
                                    <span class="text-xs font-bold uppercase tracking-wider">Pesan Resmi Admin Moderasi</span>
                                </div>
                                <p class="text-xs text-amber-950 dark:text-amber-100 whitespace-pre-line leading-relaxed font-medium">
                                    {{ $msg->message }}
                                </p>
                                <div class="text-[10px] text-amber-700/80 dark:text-amber-400 mt-2 text-right">
                                    {{ $msg->created_at->format('d M Y, H:i') }} WIB
                                </div>
                            </div>
                        </div>
                    @else
                        <div wire:key="msg-item-{{ $msg->id }}" class="flex {{ $msg->sender_type === 'mitra' ? 'justify-end' : 'justify-start' }}">
                            <div class="rounded-2xl p-3.5 max-w-[85%] shadow-xs {{ $msg->sender_type === 'mitra' ? 'bg-[#0098e7] text-white rounded-br-xs' : 'bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700/80 text-gray-900 dark:text-gray-100 rounded-bl-xs' }}">
                                @if($msg->photo)
                                    <div class="mb-2 rounded-xl overflow-hidden border border-black/10 dark:border-white/10">
                                        <a href="{{ asset('storage/' . $msg->photo) }}" target="_blank" rel="noopener">
                                            <img src="{{ asset('storage/' . $msg->photo) }}" alt="Foto Bukti" class="w-full max-h-56 object-cover hover:opacity-95 transition cursor-pointer">
                                        </a>
                                        <div class="px-2 py-1 bg-black/60 text-[10px] text-white flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            Lampiran Foto Bukti
                                        </div>
                                    </div>
                                @endif

                                <p class="text-xs leading-relaxed break-words whitespace-pre-line">{{ $msg->message }}</p>
                                <div class="text-[10px] mt-1 text-right {{ $msg->sender_type === 'mitra' ? 'text-white/80' : 'text-gray-400 dark:text-gray-500' }}">
                                    {{ $msg->created_at->format('H:i') }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="text-center text-gray-400 dark:text-gray-500 text-xs py-12">
                    Belum ada pesan. Ketik pesan di bawah untuk memulai percakapan.
                </div>
            @endif
        </div>

        <!-- Bottom Fixed Input Bar -->
        <form wire:submit.prevent="sendMessage" class="shrink-0 p-2.5 sm:p-3 border-t border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-850 space-y-2 shadow-lg">
            @if($photo)
                <div class="flex items-center gap-2 p-2 bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-800/80 rounded-xl">
                    @php
                        $canPreview = false;
                        try {
                            $canPreview = method_exists($photo, 'temporaryUrl') && $photo->isPreviewable();
                        } catch (\Throwable $e) { $canPreview = false; }
                    @endphp
                    @if($canPreview)
                        <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="w-12 h-12 object-cover rounded-lg border border-blue-300 shadow-xs">
                    @else
                        <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/60 flex items-center justify-center text-blue-600 text-[10px] font-bold">
                            FOTO
                        </div>
                    @endif
                    <div class="flex-1 min-w-0 text-xs">
                        <p class="font-bold text-gray-800 dark:text-gray-200 truncate">{{ $photo->getClientOriginalName() }}</p>
                        <p class="text-[10px] text-gray-400">Siap dikirimkan bersama pesan</p>
                    </div>
                    <button type="button" wire:click="removePhoto" class="p-1.5 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg text-xs transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            <div class="flex items-center gap-2">
                {{-- Tombol Lampirkan Foto --}}
                <label class="p-2.5 bg-gray-100 hover:bg-blue-50 dark:bg-gray-750 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 hover:text-blue-600 rounded-xl transition cursor-pointer flex-shrink-0 flex items-center justify-center" title="Lampirkan Foto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <input type="file" wire:model="photo" accept="image/png, image/jpeg, image/jpg" class="hidden">
                </label>

                <input type="text" wire:model="message" placeholder="Tulis pesan ke {{ $selected_partner->name }}..."
                    class="flex-1 px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 outline-none focus:ring-2 focus:ring-blue-500/30 focus:bg-white dark:focus:bg-gray-700 transition"
                    autofocus>

                <button type="submit"
                    class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center justify-center gap-1 cursor-pointer flex-shrink-0">
                    Kirim
                </button>
            </div>
            @error('photo')
                <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </form>
    @endif
</div>

<script>
    function scrollMitraChatToBottom(smooth = false) {
        const el = document.getElementById('messagesWrapper');
        if (!el) return;
        el.scrollTo({
            top: el.scrollHeight,
            behavior: smooth ? 'smooth' : 'instant'
        });
    }

    document.addEventListener('DOMContentLoaded', () => setTimeout(() => scrollMitraChatToBottom(false), 60));
    document.addEventListener('livewire:navigated', () => setTimeout(() => scrollMitraChatToBottom(false), 60));
    
    window.addEventListener('message-sent', () => {
        if (typeof window.playNotificationSound === 'function') {
            window.playNotificationSound({ volume: 0.65 });
        }
        setTimeout(() => scrollMitraChatToBottom(true), 60);
    });

    window.addEventListener('scroll-chat-bottom', () => setTimeout(() => scrollMitraChatToBottom(false), 40));

    window.addEventListener('help-new-message', () => {
        if (typeof window.playNotificationSound === 'function') {
            window.playNotificationSound({ volume: 0.85 });
        }
    });

    const mitraObserver = new MutationObserver(() => {
        const el = document.getElementById('messagesWrapper');
        if (el) {
            const isNearBottom = el.scrollHeight - el.scrollTop - el.clientHeight < 160;
            if (isNearBottom || el.scrollTop === 0) {
                el.scrollTop = el.scrollHeight;
            }
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const el = document.getElementById('messagesWrapper');
        if (el) mitraObserver.observe(el, { childList: true, subtree: true });
    });
</script>