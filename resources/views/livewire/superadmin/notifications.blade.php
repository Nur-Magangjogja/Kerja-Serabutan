@php
    $title = 'Notifikasi';
    $breadcrumb = 'Super Admin / Notifikasi';
@endphp

<div>
    <!-- Header Actions -->
    <div class="bg-white shadow-sm border-b border-gray-200 mb-6">
        <div class="px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <!-- Filter Tabs -->
                    <button 
                        wire:click="$set('filter', 'all')"
                        class="px-4 py-2 rounded-lg font-semibold transition {{ $filter === 'all' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Semua
                    </button>
                    <button 
                        wire:click="$set('filter', 'unread')"
                        class="px-4 py-2 rounded-lg font-semibold transition {{ $filter === 'unread' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Belum Dibaca
                        @if($unreadCount > 0)
                            <span class="ml-2 px-2 py-0.5 text-xs bg-red-500 text-white rounded-full">{{ $unreadCount }}</span>
                        @endif
                    </button>
                    <button 
                        wire:click="$set('filter', 'read')"
                        class="px-4 py-2 rounded-lg font-semibold transition {{ $filter === 'read' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Sudah Dibaca
                    </button>
                </div>

                @if($unreadCount > 0)
                    <button 
                        wire:click="markAllAsRead"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition">
                        <div class="flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>Tandai Semua Dibaca</span>
                        </div>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @forelse($notifications as $notification)
            <div 
                wire:key="notification-{{ $notification->id }}"
                class="border-b border-gray-100 hover:bg-gray-50 transition {{ $notification->read_at ? 'bg-white' : 'bg-blue-50' }}">
                <div class="px-8 py-6">
                    <div class="flex items-start space-x-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full {{ $notification->read_at ? 'bg-gray-200' : 'bg-primary-100' }} flex items-center justify-center">
                                <svg class="w-6 h-6 {{ $notification->read_at ? 'text-gray-600' : 'text-primary-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-base font-bold text-gray-900">
                                        {{ $notification->data['title'] ?? 'Notifikasi' }}
                                    </h3>
                                    <p class="text-sm text-gray-600 mt-2">
                                        {{ $notification->data['message'] ?? $notification->data['body'] ?? 'Tidak ada pesan' }}
                                    </p>
                                    <div class="flex items-center space-x-4 mt-3">
                                        <p class="text-xs text-gray-400">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            {{ $notification->created_at->format('d M Y, H:i') }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center space-x-2 ml-4">
                                    @if(!$notification->read_at)
                                        <button 
                                            wire:click="markAsRead('{{ $notification->id }}')"
                                            class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg transition"
                                            title="Tandai sudah dibaca">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    @endif
                                    <button 
                                        wire:click="deleteNotification('{{ $notification->id }}')"
                                        wire:confirm="Yakin ingin menghapus notifikasi ini?"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                                        title="Hapus notifikasi">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="px-8 py-20 text-center">
                <svg class="w-24 h-24 mx-auto text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <h3 class="text-xl font-bold text-gray-700 mb-2">Tidak ada notifikasi</h3>
                <p class="text-gray-500">
                    @if($filter === 'unread')
                        Semua notifikasi sudah dibaca
                    @elseif($filter === 'read')
                        Belum ada notifikasi yang dibaca
                    @else
                        Belum ada notifikasi untuk ditampilkan
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
