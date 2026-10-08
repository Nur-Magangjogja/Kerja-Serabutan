@php
    $title = 'Notifikasi Super Admin';
    $breadcrumb = 'Super Admin / Notifikasi';
@endphp

<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xs border border-gray-200/80 dark:border-gray-700 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span>Notifikasi Super Admin</span>
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Menampilkan notifikasi aktivitas verifikasi KTP, Kendaraan, penarikan dana (Withdraw), dan Top-Up.
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <!-- Filter Tabs -->
                <div class="inline-flex rounded-xl p-1 bg-gray-100 dark:bg-gray-700/60 border border-gray-200/80 dark:border-gray-600 text-xs">
                    <button 
                        wire:click="$set('filter', 'all')"
                        class="px-3 py-1.5 rounded-lg font-semibold transition cursor-pointer {{ $filter === 'all' ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-2xs' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                        Semua
                    </button>
                    <button 
                        wire:click="$set('filter', 'unread')"
                        class="px-3 py-1.5 rounded-lg font-semibold transition cursor-pointer flex items-center gap-1.5 {{ $filter === 'unread' ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-2xs' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                        <span>Belum Dibaca</span>
                        @if($unreadCount > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold bg-red-600 text-white">{{ $unreadCount }}</span>
                        @endif
                    </button>
                    <button 
                        wire:click="$set('filter', 'read')"
                        class="px-3 py-1.5 rounded-lg font-semibold transition cursor-pointer {{ $filter === 'read' ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-2xs' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                        Sudah Dibaca
                    </button>
                </div>

                @if($unreadCount > 0)
                    <button 
                        wire:click="markAllAsRead"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span>Tandai Semua Dibaca</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xs border border-gray-200/80 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700/60 overflow-hidden">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data ?? [];
                $type = strtolower($data['type'] ?? $data['category'] ?? '');
                $titleText = strtolower($data['title'] ?? '');
                $msgText = strtolower($data['message'] ?? $data['body'] ?? '');

                $isTopup = str_contains($type, 'topup') || str_contains($type, 'top_up') || str_contains($titleText, 'top-up') || str_contains($titleText, 'top up');
                $isWithdraw = str_contains($type, 'withdraw') || str_contains($type, 'penarikan') || str_contains($titleText, 'penarikan') || str_contains($titleText, 'withdraw');
                $isKtp = str_contains($type, 'ktp') || str_contains($titleText, 'ktp') || str_contains($msgText, 'ktp');
                $isVehicle = str_contains($type, 'vehicle') || str_contains($type, 'kendaraan') || str_contains($titleText, 'kendaraan') || str_contains($msgText, 'kendaraan');

                $targetUrl = match(true) {
                    $isTopup => route('superadmin.topup.approvals'),
                    $isWithdraw => route('superadmin.withdraws.index'),
                    $isKtp => route('superadmin.verifications'),
                    $isVehicle => route('superadmin.verifications'),
                    default => route('superadmin.notifications.index')
                };

                $categoryMeta = match(true) {
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
                        'badge' => 'Notifikasi',
                        'badge_bg' => 'bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                        'icon' => 'bell',
                    ]
                };
            @endphp
            <div 
                wire:key="notification-{{ $notification->id }}"
                class="p-4 sm:p-5 hover:bg-gray-50/70 dark:hover:bg-gray-700/40 transition {{ $notification->read_at ? 'opacity-85' : 'bg-blue-50/30 dark:bg-blue-950/20' }}">
                <div class="flex items-start gap-4">
                    <!-- Icon SVG -->
                    <div class="w-10 h-10 rounded-xl {{ $categoryMeta['bg'] }} flex items-center justify-center font-bold text-sm shrink-0 shadow-2xs">
                        @if($categoryMeta['icon'] === 'card')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        @elseif($categoryMeta['icon'] === 'cash')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        @elseif($categoryMeta['icon'] === 'id-card')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        @elseif($categoryMeta['icon'] === 'truck')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8h4.586a1 1 0 01.707.293l2.414 2.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h2"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @endif
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $categoryMeta['badge_bg'] }}">
                                        {{ $categoryMeta['badge'] }}
                                    </span>
                                    @if(!$notification->read_at)
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-primary-100 text-primary-700 dark:bg-primary-950/60 dark:text-primary-300">
                                            Baru
                                        </span>
                                    @endif
                                </div>
                                <a href="{{ $targetUrl }}" 
                                   wire:click="markAsRead('{{ $notification->id }}')" 
                                   class="block group hover:text-primary-600 dark:hover:text-primary-400 transition">
                                    <h2 class="text-sm font-bold text-gray-900 dark:text-white leading-snug">
                                        {{ $data['title'] ?? 'Pemberitahuan Sistem' }}
                                    </h2>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 leading-relaxed">
                                        {{ $data['message'] ?? $data['body'] ?? 'Tidak ada pesan' }}
                                    </p>
                                </a>
                                <div class="flex items-center gap-4 mt-2.5 text-[11px] text-gray-400 dark:text-gray-500">
                                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                                    <span>•</span>
                                    <span>{{ $notification->created_at->format('d M Y, H:i') }}</span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-1.5 shrink-0">
                                @if(!$notification->read_at)
                                    <button 
                                        wire:click="markAsRead('{{ $notification->id }}')"
                                        class="p-2 text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/50 rounded-xl transition cursor-pointer"
                                        title="Tandai sudah dibaca">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                @endif
                                <button 
                                    wire:click="deleteNotification('{{ $notification->id }}')"
                                    wire:confirm="Yakin ingin menghapus notifikasi ini?"
                                    class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/50 rounded-xl transition cursor-pointer"
                                    title="Hapus notifikasi">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="py-16 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-gray-700/60 text-gray-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300">Tidak ada notifikasi</h2>
                <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                    @if($filter === 'unread')
                        Semua notifikasi KTP, Kendaraan, Withdraw, dan Top Up telah dibaca.
                    @elseif($filter === 'read')
                        Belum ada notifikasi yang telah dibaca.
                    @else
                        Belum ada notifikasi untuk ditampilkan pada kategori ini.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="px-5 py-4 bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 rounded-2xl shadow-xs">
            {{ $notifications->links('vendor.pagination.superadmin') }}
        </div>
    @endif
</div>
