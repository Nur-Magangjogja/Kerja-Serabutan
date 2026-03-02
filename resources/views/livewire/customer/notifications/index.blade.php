<div class="min-h-screen bg-white">
    <div class="max-w-md mx-auto">
        <!-- Header (smaller, consistent with Top-Up) -->
        <div class="px-5 pt-4 pb-8 relative overflow-hidden" style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
            <div class="absolute top-0 right-0 w-28 h-28 bg-white/5 rounded-full -mr-12 -mt-12"></div>
            <div class="absolute bottom-0 left-0 w-20 h-20 bg-white/5 rounded-full -ml-8 -mb-8"></div>

            <div class="relative z-10 max-w-md mx-auto">
                <div class="flex items-center justify-between text-white mb-4">
                    <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <div class="text-center flex-1 px-2">
                        <h1 class="text-base font-semibold">Notifikasi</h1>
                        <p class="text-xs text-white/90 mt-0.5">{{ $unreadCount }} belum dibaca</p>
                    </div>

                    <a href="#" class="p-2 hover:bg-white/20 rounded-lg transition">
                        @if($unreadCount > 0)
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        @else
                            <div class="w-5 h-5"></div>
                        @endif
                    </a>
                </div>
            </div>

            <!-- Curved separator (reduced) -->
            <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 56" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,24 C360,56 1080,0 1440,32 L1440,56 L0,56 Z" fill="#f9fafb"></path>
            </svg>
        </div>
        <br>

        <!-- Content -->
        <div class="bg-white rounded-t-xl -mt-5 px-4 pt-4 pb-6">
            @if($notifications->count() > 0)
                <!-- Clean Tabs -->
                <div class="mb-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="inline-flex bg-gray-100 rounded-lg p-1">
                            <button id="tab-all" data-mode="all" class="tab-btn px-4 py-2 rounded-md text-sm font-semibold bg-blue-600 text-white transition">
                                Semua
                            </button>
                            <button id="tab-unread" data-mode="unread" class="tab-btn px-4 py-2 rounded-md text-sm font-semibold bg-transparent text-gray-700 transition">
                                Belum Dibaca
                                @if($unreadCount > 0)
                                    <span class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-blue-600 rounded-full">{{ $unreadCount }}</span>
                                @endif
                            </button>
                        </div>

                        @if(count($selected) > 0)
                            <div class="flex items-center gap-2">
                                <button wire:click="bulkMarkAsRead" class="px-3 py-1.5 rounded-md bg-blue-600 text-white text-xs font-medium hover:bg-blue-700 whitespace-nowrap">
                                    Tandai Dibaca
                                </button>
                                <button wire:click="bulkDelete" class="px-3 py-1.5 rounded-md bg-red-600 text-white text-xs font-medium hover:bg-red-700 whitespace-nowrap">
                                    Hapus
                                </button>
                                <button wire:click="clearSelection" class="text-xs font-medium text-gray-600 hover:text-gray-700 whitespace-nowrap">
                                    Batal
                                </button>
                            </div>
                        @else
                            <button wire:click="selectAllOnPage({{ json_encode($notifications->pluck('id')->toArray()) }})" class="text-sm font-medium text-blue-600 hover:text-blue-700 whitespace-nowrap">
                                Pilih Semua
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Notifications List -->
                <div id="notifications-list" class="space-y-3">
                    @foreach($notifications as $notification)
                        @php
                            $data = $notification->data;
                            $isUnread = is_null($notification->read_at);
                        @endphp

                        <div wire:key="notification-{{ $notification->id }}" class="notification-item bg-white rounded-lg border {{ $isUnread ? 'unread border-blue-100' : 'border-gray-200' }} overflow-hidden">
                            <div class="flex items-start gap-3 p-4">
                                <div class="flex-shrink-0 mt-1">
                                    <input type="checkbox" wire:model="selected" value="{{ $notification->id }}" class="form-checkbox h-4 w-4 text-blue-600" aria-label="Pilih notifikasi">
                                </div>

                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center border {{ $isUnread ? 'border-blue-200 bg-blue-50' : 'border-gray-200 bg-white' }}">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m4 0h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                                        </svg>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div>
                                                @php
                                                    // Determine title based on notification payload
                                                    if(isset($data['type']) && $data['type'] === 'chat_message') {
                                                        $titleText = 'Pesan dari ' . ($data['from_name'] ?? 'Mitra');
                                                    } elseif(isset($data['type']) && $data['type'] === 'help_taken') {
                                                        $titleText = $data['title'] ?? ('Bantuan Diambil oleh ' . ($data['mitra_name'] ?? 'mitra'));
                                                    } else {
                                                        $titleText = $data['title'] ?? 'Notifikasi';
                                                    }
                                                    $bodyText = $data['message'] ?? ($data['body'] ?? 'Notifikasi baru');
                                                @endphp

                                                <h3 class="text-sm font-semibold text-gray-900">{{ $titleText }}</h3>
                                                <p class="text-xs text-gray-500 mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                                        </div>
                                        @if(isset($data['help_amount']))
                                            <div class="ml-2 text-right">
                                                <div class="inline-flex items-center px-3 py-1 rounded-md bg-blue-50 text-blue-700 text-sm font-semibold">Rp {{ number_format($data['help_amount'],0,',','.') }}</div>
                                            </div>
                                        @endif
                                    </div>

                                    <p class="text-sm text-gray-700 mt-2">{{ $bodyText }}</p>

                                    @if(isset($data['from_name']) || isset($data['mitra_name']))
                                        <div class="text-xs text-gray-500 mt-2">Dari: {{ $data['from_name'] ?? $data['mitra_name'] ?? '-' }}</div>
                                    @endif

                                    <div class="flex items-center gap-4 mt-3">
                                        @if($isUnread)
                                            <button wire:click="markAsRead('{{ $notification->id }}')" class="text-xs font-medium text-blue-600 hover:underline">Tandai Dibaca</button>
                                        @endif
                                        <button wire:click="deleteNotification('{{ $notification->id }}')" class="text-xs font-medium text-red-500 hover:underline">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($notifications->hasPages())
                    <nav class="mt-5 flex items-center justify-center" role="navigation" aria-label="Pagination Navigation">
                        @php
                            $current = $notifications->currentPage();
                            $last = $notifications->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $start + 4);
                            if ($end - $start < 4) { $start = max(1, $end - 4); }
                        @endphp

                        <div class="inline-flex items-center gap-2">
                            {{-- Previous --}}
                            @if($notifications->onFirstPage())
                                <span class="px-3 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed">Prev</span>
                            @else
                                <a href="{{ $notifications->previousPageUrl() }}" class="px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">Prev</a>
                            @endif

                            {{-- Page numbers --}}
                            @for($i = $start; $i <= $end; $i++)
                                @if($i == $current)
                                    <span aria-current="page" class="px-3 py-2 rounded-lg bg-blue-600 text-white font-semibold">{{ $i }}</span>
                                @else
                                    <a href="{{ $notifications->url($i) }}" class="px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">{{ $i }}</a>
                                @endif
                            @endfor

                            {{-- Next --}}
                            @if($current >= $last)
                                <span class="px-3 py-2 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed">Next</span>
                            @else
                                <a href="{{ $notifications->nextPageUrl() }}" class="px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">Next</a>
                            @endif
                        </div>
                    </nav>
                @endif
            @else
                <div class="text-center py-16">
                    <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m4 0h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Belum Ada Notifikasi</h3>
                    <p class="text-sm text-gray-500">Notifikasi Anda akan muncul di sini</p>
                </div>
            @endif
        </div>
    </div>

    <style>
        /* hide non-unread items when list has show-only-unread */
        #notifications-list.show-only-unread .notification-item:not(.unread) { display: none; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabAll = document.getElementById('tab-all');
            const tabUnread = document.getElementById('tab-unread');
            const list = document.getElementById('notifications-list');
            if (!tabAll || !tabUnread || !list) return;

            function setActive(mode) {
                if (mode === 'unread') {
                    tabUnread.classList.remove('bg-gray-100','text-gray-700'); tabUnread.classList.add('bg-blue-600','text-white');
                    tabAll.classList.remove('bg-blue-600','text-white'); tabAll.classList.add('bg-gray-100','text-gray-700');
                    list.classList.add('show-only-unread');
                } else {
                    tabAll.classList.remove('bg-gray-100','text-gray-700'); tabAll.classList.add('bg-blue-600','text-white');
                    tabUnread.classList.remove('bg-blue-600','text-white'); tabUnread.classList.add('bg-gray-100','text-gray-700');
                    list.classList.remove('show-only-unread');
                }
            }

            tabAll.addEventListener('click', () => setActive('all'));
            tabUnread.addEventListener('click', () => setActive('unread'));
        });
    </script>
</div>