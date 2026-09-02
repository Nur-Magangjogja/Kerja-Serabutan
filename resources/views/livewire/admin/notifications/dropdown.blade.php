<div class="relative" x-data="{ isOpen: false }" wire:poll.15s="loadNotifications">
    <!-- Notification Bell Button -->
    <button 
        @click="isOpen = !isOpen"
        class="relative p-2 rounded-xl bg-gray-100/80 dark:bg-gray-700/60 border border-gray-200/60 dark:border-gray-600/60 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 focus:outline-none focus:ring-2 focus:ring-primary-500 transition cursor-pointer text-gray-600 dark:text-gray-200"
        type="button"
        aria-label="Notifikasi Admin">
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

    <!-- Dropdown Menu -->
    <div 
        x-show="isOpen"
        @click.away="isOpen = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-1"
        class="origin-top-right absolute right-0 mt-2 w-80 sm:w-96 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200/80 dark:border-gray-700 z-50 overflow-hidden"
        x-cloak>
        
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3.5 border-b border-gray-200/80 dark:border-gray-700 bg-gray-50/90 dark:bg-gray-800/90">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-gray-900 dark:text-white">🔔 Notifikasi Admin</span>
                @if($unreadCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-primary-100 text-primary-700 dark:bg-primary-950/60 dark:text-primary-300">
                        {{ $unreadCount }} baru
                    </span>
                @endif
            </div>
            @if($unreadCount > 0)
                <button 
                    wire:click="markAllAsRead"
                    class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-semibold cursor-pointer">
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
                    $targetUrl = $data['url'] ?? null;
                    if (!$targetUrl) {
                        if (str_contains($type, 'report')) {
                            $targetUrl = isset($data['report_id']) ? route('admin.partners.reports.show', $data['report_id']) : route('admin.partners.reports');
                        } elseif (str_contains($type, 'topup')) {
                            $targetUrl = route('admin.topup.approvals');
                        } elseif (str_contains($type, 'withdraw')) {
                            $targetUrl = route('admin.withdraws.index');
                        } elseif (str_contains($type, 'ktp') || str_contains($type, 'verification')) {
                            $targetUrl = route('admin.verifications');
                        } else {
                            $targetUrl = '#';
                        }
                    }

                    $iconData = match(true) {
                        str_contains($type, 'report') => ['icon' => '📢', 'bg' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300'],
                        str_contains($type, 'topup') => ['icon' => '💳', 'bg' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300'],
                        str_contains($type, 'withdraw') => ['icon' => '💸', 'bg' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300'],
                        str_contains($type, 'ktp') || str_contains($type, 'verification') => ['icon' => '🪪', 'bg' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/50 dark:text-teal-300'],
                        default => ['icon' => '🔔', 'bg' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
                    };
                @endphp
                <div 
                    wire:key="admin-notif-{{ $notification->id }}"
                    class="group relative flex items-start gap-3 p-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition {{ $notification->read_at ? 'opacity-80' : 'bg-blue-50/40 dark:bg-blue-950/20' }}">
                    
                    <!-- Icon -->
                    <div class="w-9 h-9 rounded-xl {{ $iconData['bg'] }} flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-2xs">
                        {{ $iconData['icon'] }}
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <a href="{{ $targetUrl }}" 
                           @click="isOpen = false; $wire.markAsRead('{{ $notification->id }}')" 
                           class="block group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">
                            <p class="text-xs font-bold text-gray-900 dark:text-white leading-tight">
                                {{ $data['title'] ?? 'Notifikasi Baru' }}
                            </p>
                            <p class="text-[11px] text-gray-600 dark:text-gray-300 mt-0.5 line-clamp-2 leading-relaxed">
                                {{ $data['message'] ?? $data['body'] ?? '-' }}
                            </p>
                        </a>
                        <span class="text-[10px] text-gray-400 mt-1 block">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <!-- Actions -->
                    @if(!$notification->read_at)
                        <button 
                            wire:click="markAsRead('{{ $notification->id }}')"
                            class="text-gray-300 hover:text-primary-600 dark:text-gray-500 dark:hover:text-primary-400 p-1 transition"
                            title="Tandai telah dibaca">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        </button>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-700/60 text-gray-400 flex items-center justify-center mx-auto mb-2 text-xl">
                        📭
                    </div>
                    <p class="text-xs font-bold text-gray-700 dark:text-gray-300">Belum Ada Notifikasi</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Laporan aduan, top up, withdraw, dan verifikasi KTP akan muncul di sini.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
