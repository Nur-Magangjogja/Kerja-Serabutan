<div class="min-h-screen bg-white">
    <div class="max-w-md mx-auto">
        <!-- Header - match mitra style for consistency -->
        <div class="px-5 pt-5 pb-8 relative overflow-hidden" style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
            <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between text-white mb-3">
                    <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div class="text-center flex-1">
                        <h1 class="text-lg font-bold">Chat</h1>
                        <p class="text-xs text-white/90 mt-0.5">Percakapan antara Anda dan mitra</p>
                    </div>
                    <div class="w-8"></div>
                </div>
            </div>

            <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 72" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,32 C360,72 1080,0 1440,40 L1440,72 L0,72 Z" fill="#ffffff"></path>
            </svg>
        </div>

        <div class="bg-white rounded-t-3xl -mt-6 px-5 pt-6 pb-24 min-h-[60vh]">
            <div class="space-y-4">
                <div class="relative">
                    <input type="text" wire:model.debounce.400ms="search" placeholder="Cari percakapan atau mitra..."
                        class="w-full px-4 py-2.5 rounded-xl bg-gray-50 text-gray-900 placeholder-gray-400 border border-gray-200 focus:ring-2 focus:ring-blue-200 outline-none text-sm">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                @if(!$selected_help_id)
                    <div class="space-y-3">
                        @if($conversations && $conversations->count() > 0)
                            @foreach($conversations as $conversation)
                                <button wire:click="selectHelp({{ $conversation->id }})"
                                    class="w-full px-3 py-3 rounded-xl hover:shadow-md transition text-left {{ $selected_help_id === $conversation->id ? 'bg-primary-50 border border-primary-200' : 'bg-white border border-gray-100' }}">
                                    <div class="flex items-start gap-3">
                                        <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0 flex items-center justify-center text-lg">
                                            @if(optional($conversation->mitra)->selfie_photo)
                                                <img src="{{ asset('storage/' . optional($conversation->mitra)->selfie_photo) }}" alt="Mitra" class="w-full h-full object-cover">
                                            @else
                                                {{ strtoupper(substr($conversation->mitra->name ?? 'M', 0, 1)) }}
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-2 mb-1">
                                                <h3 class="font-semibold text-sm text-gray-900 line-clamp-1">{{ $conversation->mitra->name ?? 'Mitra' }}</h3>
                                                <span class="text-xs text-gray-400">{{ optional($conversation->updated_at)->format('H:i') }}</span>
                                            </div>
                                            <p class="text-xs text-gray-600 line-clamp-2">{{ $conversation->chatMessages->first()?->message ?? 'Mulai percakapan...' }}</p>
                                        </div>

                                        @if($conversation->chatMessages->first()?->sender_type === 'mitra' && !$conversation->chatMessages->first()?->read_at)
                                            <div class="ml-2 w-2 h-2 rounded-full bg-blue-500 mt-2 flex-shrink-0"></div>
                                        @endif
                                    </div>
                                </button>
                            @endforeach
                        @else
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <p class="text-sm font-semibold text-gray-700">Tidak ada percakapan</p>
                                <p class="text-xs text-gray-500 mt-1">Percakapan akan muncul saat Anda membuat pesanan atau mitra menghubungi Anda</p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="bg-white rounded-xl p-3.5 shadow-sm border border-gray-100 flex flex-col min-h-[60vh]">
                        <div class="flex items-center gap-3 mb-3">
                            <button wire:click="$set('selected_help_id', null)" class="p-2 hover:bg-gray-50 rounded-md">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                                @if(optional($selected_help->mitra)->selfie_photo)
                                    <img src="{{ asset('storage/' . optional($selected_help->mitra)->selfie_photo) }}" alt="Mitra" class="w-full h-full object-cover">
                                @elseif(optional($selected_help->mitra)->photo)
                                    <img src="{{ asset('storage/' . optional($selected_help->mitra)->photo) }}" alt="Mitra" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-blue-600 text-white flex items-center justify-center font-semibold">{{ strtoupper(substr($selected_help->mitra->name ?? 'M',0,1)) }}</div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-sm text-gray-900 truncate">{{ $selected_help->mitra->name ?? 'Mitra' }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ Str::limit($selected_help->description, 60) }}</div>
                            </div>
                            @if(optional($selected_help->mitra)->id)
                                <a href="{{ route('customer.reports.create.user', optional($selected_help->mitra)->id) }}" class="ml-2 px-3 py-1.5 text-sm text-red-600 rounded-lg border border-red-100 hover:bg-red-50">Laporkan</a>
                            @endif
                        </div>

                        <div id="mitraMessagesWrapper" class="flex-1 overflow-auto px-1 py-2 bg-white">
                            <div class="space-y-3">
                                @if($messages && $messages->count() > 0)
                                    @foreach($messages as $msg)
                                        <div class="flex {{ $msg->sender_type === 'customer' ? 'justify-end' : 'justify-start' }}">
                                            <div class="rounded-lg p-3 max-w-[80%] {{ $msg->sender_type === 'customer' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-900' }}">
                                                <p class="text-sm leading-snug break-words">{{ $msg->message }}</p>
                                                <div class="text-xs mt-1 text-gray-300 text-right {{ $msg->sender_type === 'customer' ? 'text-white/70' : 'text-gray-400' }}">{{ $msg->created_at->format('H:i') }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center text-gray-500 py-8">Mulai percakapan dengan mitra</div>
                                @endif
                            </div>
                        </div>

                        <form wire:submit.prevent="sendMessage" class="chat-input-fixed mt-3">
                            <div class="flex items-center gap-3">
                                <input type="text" wire:model.defer="message" placeholder="Tulis pesan..."
                                    class="flex-1 px-4 py-2 rounded-xl bg-white border border-gray-200 text-sm outline-none focus:ring-2 focus:ring-blue-200">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl">Kirim</button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        /* ensure messages area has room for input on mobile */
        #mitraMessagesWrapper { padding-bottom: 12px; }
        @media (min-width: 640px) { #mitraMessagesWrapper { min-height: calc(60vh); } }

        .chat-input-fixed {
            position: fixed;
            left: 50%;
            transform: translateX(-50%);
            bottom: calc(env(safe-area-inset-bottom, 0px) + 90px);
            width: calc(100% - 48px);
            max-width: 420px;
            z-index: 60;
            background: transparent;
            padding-left: 8px;
            padding-right: 8px;
            box-shadow: 0 6px 18px rgba(2,6,23,0.06);
            border-radius: 12px;
        }

        @media (min-width: 768px) {
            .chat-input-fixed { position: static; transform: none; bottom: auto; width: 100%; max-width: none; padding-left: 0; padding-right: 0; }
        }
    </style>
</div>

<script>
    function scrollMitraMessagesToBottom() {
        const el = document.getElementById('mitraMessagesWrapper');
        if (!el) return;
        setTimeout(() => { try { el.scrollTop = el.scrollHeight; } catch (e) {} }, 50);
    }

    document.addEventListener('DOMContentLoaded', scrollMitraMessagesToBottom);
    window.addEventListener('message-sent', scrollMitraMessagesToBottom);
    const wrapper = document.getElementById('mitraMessagesWrapper');
    if (wrapper) {
        const mo = new MutationObserver(scrollMitraMessagesToBottom);
        mo.observe(wrapper, { childList: true, subtree: true });
    }
</script>