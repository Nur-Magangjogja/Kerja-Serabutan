<div class="relative" wire:poll.60s.visible="loadUnreadCount" @keydown.escape.window="$wire.isOpen && $wire.closeDropdown()">
    <!-- Notification Bell Button -->
    <button 
        wire:click="toggleDropdown"
        class="relative inline-flex items-center justify-center p-2 rounded-xl bg-gray-500/10 dark:bg-gray-400/10 border border-gray-500/15 dark:border-gray-400/15 text-gray-700 dark:text-gray-200 hover:bg-gray-500/15 dark:hover:bg-gray-400/20 focus:outline-none focus:ring-2 focus:ring-primary-500 cursor-pointer shadow-2xs active:scale-95 transition-transform"
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

    @if($isOpen)
        <!-- Backdrop to close dropdown on click outside -->
        <div class="fixed inset-0 z-40 bg-gray-950/40 backdrop-blur-xs sm:bg-transparent" wire:click="closeDropdown"></div>

        <!-- Dropdown Menu (Responsive: Fixed on Mobile, Absolute on Desktop) -->
        <div 
            class="fixed sm:absolute inset-x-2.5 sm:inset-x-auto top-16 sm:top-full sm:right-0 sm:mt-2 w-auto sm:w-96 md:w-[26rem] max-h-[calc(100vh-5rem)] sm:max-h-[34rem] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200/80 dark:border-gray-700 z-50 flex flex-col overflow-hidden">
            
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200/80 dark:border-gray-700 bg-gray-50/90 dark:bg-gray-800/90 shrink-0 gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate">Notifikasi Admin Wilayah</span>
                    @if($unreadCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-primary-100 text-primary-700 dark:bg-primary-950/60 dark:text-primary-300 shrink-0">
                            {{ $unreadCount }} baru
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    @if($unreadCount > 0)
                        <button 
                            wire:click="markAllAsRead"
                            class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-semibold transition cursor-pointer whitespace-nowrap">
                            Tandai Semua Dibaca
                        </button>
                    @endif
                    <button 
                        type="button" 
                        wire:click="closeDropdown" 
                        class="sm:hidden p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" 
                        title="Tutup">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="flex-1 min-h-0 overflow-y-auto dropdown-scrollbar divide-y divide-gray-100 dark:divide-gray-700/60 overscroll-contain">
                @forelse($notifications as $notification)
                    @php
                        $data = $notification->data ?? [];
                        $type = strtolower($data['type'] ?? $data['category'] ?? '');
                        $titleText = strtolower($data['title'] ?? '');
                        $msgText = strtolower($data['message'] ?? $data['body'] ?? '');

                        $isSupport = str_contains($type, 'support') || str_contains($type, 'chat_admin') || (str_contains($type, 'report') && str_contains($titleText, 'chat'));
                        $isReport = !$isSupport && (str_contains($type, 'report') || str_contains($titleText, 'aduan') || str_contains($titleText, 'laporan'));
                        $isCancellation = str_contains($type, 'cancel') || str_contains($type, 'dispute') || str_contains($titleText, 'pembatalan') || str_contains($titleText, 'sengketa') || str_contains($titleText, 'kendala');
                        $isTopup = str_contains($type, 'topup') || str_contains($type, 'top_up') || str_contains($titleText, 'top-up') || str_contains($titleText, 'top up');
                        $isWithdraw = str_contains($type, 'withdraw') || str_contains($type, 'penarikan') || str_contains($titleText, 'penarikan') || str_contains($titleText, 'withdraw');
                        $isKtp = str_contains($type, 'ktp') || str_contains($titleText, 'ktp') || str_contains($msgText, 'ktp');
                        $isVehicle = str_contains($type, 'vehicle') || str_contains($type, 'kendaraan') || str_contains($titleText, 'kendaraan') || str_contains($msgText, 'kendaraan');

                        $targetUrl = match(true) {
                            $isSupport => isset($data['report_id']) ? route('admin.support.chat', $data['report_id']) : route('admin.support.index'),
                            $isReport => isset($data['report_id']) ? route('admin.partners.reports.show', $data['report_id']) : route('admin.partners.reports'),
                            $isCancellation => route('admin.cancellations.index'),
                            $isTopup => route('admin.topup.approvals'),
                            $isWithdraw => route('admin.withdraws.index'),
                            $isKtp => route('admin.verifications'),
                            $isVehicle => route('admin.verifications'),
                            str_contains($type, 'help') => route('admin.helps'),
                            default => ($data['url'] ?? route('admin.notifications.index'))
                        };

                        $categoryMeta = match(true) {
                            $isSupport => [
                                'bg' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-300',
                                'badge' => 'Chat Admin',
                                'badge_bg' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300',
                                'icon' => 'chat',
                            ],
                            $isReport => [
                                'bg' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',
                                'badge' => 'Laporan Aduan',
                                'badge_bg' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
                                'icon' => 'report',
                            ],
                            $isCancellation => [
                                'bg' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300',
                                'badge' => 'Tinjauan Pembatalan',
                                'badge_bg' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300',
                                'icon' => 'cancel',
                            ],
                            $isTopup => [
                                'bg' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
                                'badge' => 'Top-Up',
                                'badge_bg' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300',
                                'icon' => 'card',
                            ],
                            $isWithdraw => [
                                'bg' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300',
                                'badge' => 'Withdraw',
                                'badge_bg' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
                                'icon' => 'cash',
                            ],
                            $isKtp => [
                                'bg' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/50 dark:text-teal-300',
                                'badge' => 'Verifikasi KTP',
                                'badge_bg' => 'bg-teal-50 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300',
                                'icon' => 'id-card',
                            ],
                            $isVehicle => [
                                'bg' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300',
                                'badge' => 'Kendaraan',
                                'badge_bg' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300',
                                'icon' => 'truck',
                            ],
                            default => [
                                'bg' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                'badge' => 'Aktivitas',
                                'badge_bg' => 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                                'icon' => 'bell',
                            ]
                        };
                    @endphp
                    <div 
                        wire:key="admin-notif-{{ $notification->id }}"
                        class="group relative flex items-start gap-3 p-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition {{ $notification->read_at ? 'opacity-80' : 'bg-blue-50/40 dark:bg-blue-950/20' }}">
                        
                        <!-- Icon SVG -->
                        <div class="w-9 h-9 rounded-xl {{ $categoryMeta['bg'] }} flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-2xs">
                            @if($categoryMeta['icon'] === 'chat')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            @elseif($categoryMeta['icon'] === 'report')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            @elseif($categoryMeta['icon'] === 'cancel')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif($categoryMeta['icon'] === 'card')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            @elseif($categoryMeta['icon'] === 'cash')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            @elseif($categoryMeta['icon'] === 'id-card')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                            @elseif($categoryMeta['icon'] === 'truck')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8h4.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h2"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ $categoryMeta['badge_bg'] }}">
                                    {{ $categoryMeta['badge'] }}
                                </span>
                            </div>
                            <a href="{{ $targetUrl }}" 
                               wire:click="markAsRead('{{ $notification->id }}')" 
                               class="block group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">
                                <p class="text-xs font-bold text-gray-900 dark:text-white leading-tight break-words">
                                    {{ $data['title'] ?? 'Aktivitas Pengguna' }}
                                </p>
                                <p class="text-[11px] text-gray-600 dark:text-gray-300 mt-0.5 line-clamp-2 leading-relaxed break-words">
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
                                class="text-gray-300 hover:text-primary-600 dark:text-gray-500 dark:hover:text-primary-400 p-1 transition cursor-pointer shrink-0"
                                title="Tandai sudah dibaca">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </button>
                        @endif
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-700/60 text-gray-400 flex items-center justify-center mx-auto mb-2 text-xl">
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </div>
                        <p class="text-xs font-bold text-gray-700 dark:text-gray-300">Tidak Ada Notifikasi</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Aktivitas mitra dan customer di wilayah Anda akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>

            <!-- Footer: Link to Full Index -->
            <div class="p-2.5 bg-gray-50 dark:bg-gray-800/90 border-t border-gray-100 dark:border-gray-700 text-center shrink-0">
                <a href="{{ route('admin.notifications.index') }}" 
                   wire:navigate
                   class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition inline-flex items-center gap-1 cursor-pointer">
                    <span>Lihat Semua Notifikasi</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    @endif
</div>
