<div class="relative" x-data="{ open: false }">
    <!-- Notification Button -->
    <button 
        @click="open = !open"
        class="relative p-2 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-700/80 rounded-lg transition cursor-pointer"
        type="button">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if($unreadCount > 0)
            <span class="absolute top-1 right-1 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Menu -->
    <div 
        x-show="open"
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 z-50 overflow-hidden"
        x-cloak>
        
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/90 rounded-t-xl">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Notifikasi</h3>
                @if($unreadCount > 0)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $unreadCount }} notifikasi belum dibaca</p>
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
        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                <div 
                    wire:key="notification-{{ $notification->id }}"
                    class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/60 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition {{ $notification->read_at ? 'bg-white dark:bg-gray-800' : 'bg-blue-50/50 dark:bg-blue-950/20' }}">
                    <div class="flex items-start space-x-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full {{ $notification->read_at ? 'bg-gray-100 dark:bg-gray-700' : 'bg-primary-100 dark:bg-primary-900/50' }} flex items-center justify-center">
                                <svg class="w-5 h-5 {{ $notification->read_at ? 'text-gray-600 dark:text-gray-300' : 'text-primary-600 dark:text-primary-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $notification->data['title'] ?? 'Notifikasi' }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                {{ $notification->data['message'] ?? $notification->data['body'] ?? 'Tidak ada pesan' }}
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-400 mt-2">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <!-- Mark as Read Button -->
                        @if(!$notification->read_at)
                            <button 
                                wire:click="markAsRead('{{ $notification->id }}')"
                                class="flex-shrink-0 text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition cursor-pointer"
                                title="Tandai sudah dibaca">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">Tidak ada notifikasi</p>
                    <p class="text-sm text-gray-400 dark:text-gray-400 mt-1">Semua notifikasi akan muncul di sini</p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        @if($notifications->count() > 0)
            <div class="px-6 py-3 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                <a href="{{ route('superadmin.notifications.index') }}" 
                    class="block text-center text-sm font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition">
                    Lihat Semua Notifikasi
                </a>
            </div>
        @endif
    </div>
</div>
