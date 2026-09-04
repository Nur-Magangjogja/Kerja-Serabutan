<div class="relative" wire:poll.15s="loadNotifications">
    <!-- Notification Bell Button -->
    <button 
        wire:click="toggleDropdown"
        class="relative inline-flex items-center justify-center p-2 rounded-xl bg-gray-500/10 dark:bg-gray-400/10 border border-gray-500/15 dark:border-gray-400/15 text-gray-700 dark:text-gray-200 hover:bg-gray-500/15 dark:hover:bg-gray-400/20 focus:outline-none focus:ring-2 focus:ring-primary-500 cursor-pointer shadow-2xs active:scale-95 transition-transform"
        type="button"
        aria-label="Notifikasi Keuangan Super Admin">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-extrabold leading-none text-white bg-red-600 rounded-full shadow-xs animate-pulse">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    @if($isOpen)
        <!-- Backdrop to close dropdown on click outside -->
        <div class="fixed inset-0 z-40 bg-transparent" wire:click="closeDropdown"></div>

        <!-- Dropdown Menu -->
        <div 
            class="origin-top-right absolute right-0 mt-2 w-80 sm:w-96 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200/80 dark:border-gray-700 z-50 overflow-hidden">
            
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/90">
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">💰 Transaksi Keuangan</h3>
                    @if($unreadCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-primary-100 text-primary-700 dark:bg-primary-950/60 dark:text-primary-300">
                            {{ $unreadCount }} baru
                        </span>
                    @endif
                </div>
                @if($unreadCount > 0)
                    <button 
                        wire:click="markAllAsRead"
                        class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-semibold transition cursor-pointer">
                        Tandai Semua Dibaca
                    </button>
                @endif
            </div>

            <!-- Notifications List -->
            <div class="max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700/60">
                @forelse($notifications as $notification)
                    @php
                        $data = $notification->data ?? [];
                        $type = $data['type'] ?? $data['category'] ?? '';
                        $isTopup = str_contains($type, 'topup');
                        $targetUrl = $isTopup ? route('superadmin.topup.approvals') : route('superadmin.withdraws.index');
                    @endphp
                    <div 
                        wire:key="superadmin-notif-{{ $notification->id }}"
                        class="group relative flex items-start gap-3 p-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition {{ $notification->read_at ? 'opacity-80' : 'bg-blue-50/40 dark:bg-blue-950/20' }}">
                        
                        <!-- Icon -->
                        <div class="w-9 h-9 rounded-xl {{ $isTopup ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' }} flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-2xs">
                            {{ $isTopup ? '💳' : '💸' }}
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <a href="{{ $targetUrl }}" 
                               wire:click="markAsRead('{{ $notification->id }}')" 
                               class="block group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">
                                <p class="text-xs font-bold text-gray-900 dark:text-white leading-tight">
                                    {{ $data['title'] ?? ($isTopup ? 'Permintaan Top-Up Saldo' : 'Permintaan Penarikan Dana') }}
                                </p>
                                <p class="text-[11px] text-gray-600 dark:text-gray-300 mt-0.5 line-clamp-2 leading-relaxed">
                                    {{ $data['message'] ?? $data['body'] ?? '-' }}
                                </p>
                            </a>
                            <span class="text-[10px] text-gray-400 mt-1 block">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <!-- Mark as Read Button -->
                        @if(!$notification->read_at)
                            <button 
                                wire:click="markAsRead('{{ $notification->id }}')"
                                class="text-gray-300 hover:text-primary-600 dark:text-gray-500 dark:hover:text-primary-400 p-1 transition"
                                title="Tandai sudah dibaca">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </button>
                        @endif
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-700/60 text-gray-400 flex items-center justify-center mx-auto mb-2 text-xl">
                            💰
                        </div>
                        <p class="text-xs font-bold text-gray-700 dark:text-gray-300">Tidak Ada Notifikasi Keuangan</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Permintaan top up dan withdraw baru akan ditampilkan di sini.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
