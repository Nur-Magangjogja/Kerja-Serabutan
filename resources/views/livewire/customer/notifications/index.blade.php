<div class="min-h-screen bg-gray-50 dark:bg-gray-900 pb-20">
    <div class="max-w-md mx-auto">
        <!-- Header Section -->
        <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between text-white">
                    <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <div class="text-center flex-1 min-w-0 px-2">
                        <h1 class="text-base font-bold truncate">Notifikasi</h1>
                        <p class="text-xs text-white/90 truncate mt-0.5">{{ $totalCount }} Pesan Masuk</p>
                    </div>

                    <a href="{{ route('profile.settings.notifications') }}" title="Pengaturan Notifikasi" class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="px-4 pt-4">
            <!-- Flash Message Banner -->
            @if(session()->has('message'))
                <div class="mb-3 p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl flex items-center justify-between text-xs text-emerald-800 dark:text-emerald-300">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ session('message') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 text-base font-bold leading-none">&times;</button>
                </div>
            @endif

            @if($notifications->count() > 0)
                <!-- Action Toolbar: Delete / Bulk Delete Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-3 mb-3 flex items-center justify-between gap-2">
                    @if(count($selected) > 0)
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                {{ count($selected) }} Terpilih
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="bulkDelete" wire:confirm="Hapus {{ count($selected) }} notifikasi terpilih?" class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold flex items-center gap-1.5 shadow-sm transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Hapus ({{ count($selected) }})</span>
                            </button>
                            <button wire:click="clearSelection" class="px-2.5 py-1.5 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs hover:bg-gray-200 transition">
                                Batal
                            </button>
                        </div>
                    @else
                        <button wire:click="selectAllOnPage({{ json_encode($notifications->pluck('id')->toArray()) }})" class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <span>Pilih Semua</span>
                        </button>

                        <button wire:click="deleteAllNotifications" wire:confirm="Hapus SEMUA notifikasi Anda?" class="text-xs font-medium text-rose-600 hover:text-rose-700 hover:underline flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Hapus Semua</span>
                        </button>
                    @endif
                </div>

                <!-- Notifications List -->
                <div class="space-y-3">
                    @foreach($notifications as $notification)
                        @php
                            $data = $notification->data;
                            $type = $data['type'] ?? 'general';

                            if($type === 'chat_message') {
                                $titleText = 'Pesan Baru';
                                $badgeColor = 'bg-blue-50 text-blue-700 border-blue-200';
                                $iconColor = 'text-blue-500 bg-blue-50';
                            } elseif($type === 'help_taken') {
                                $titleText = $data['title'] ?? 'Bantuan Diambil';
                                $badgeColor = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                $iconColor = 'text-emerald-500 bg-emerald-50';
                            } elseif($type === 'help_status') {
                                $statusKey = strtolower($data['new_status'] ?? '');
                                $titleText = $data['title'] ?? match($statusKey) {
                                    'in_progress', 'sedang_diproses', 'service_started' => 'Pekerjaan Dimulai',
                                    'waiting_customer_confirmation' => 'Pekerjaan Selesai (Konfirmasi)',
                                    'completed', 'selesai' => 'Bantuan Selesai',
                                    'partner_on_the_way' => 'Mitra Menuju Lokasi',
                                    'partner_arrived' => 'Mitra Telah Tiba',
                                    'partner_cancel_requested' => 'Permintaan Pembatalan',
                                    default => 'Pembaruan Status Bantuan',
                                };
                                $badgeColor = in_array($statusKey, ['completed', 'selesai', 'waiting_customer_confirmation']) ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-sky-50 text-sky-700 border-sky-200';
                                $iconColor = 'text-amber-500 bg-amber-50';
                            } else {
                                $titleText = $data['title'] ?? 'Notifikasi';
                                $badgeColor = 'bg-gray-50 text-gray-700 border-gray-200';
                                $iconColor = 'text-gray-500 bg-gray-50';
                            }

                            $bodyText = $data['message'] ?? ($data['body'] ?? 'Pemberitahuan bantuan Anda');
                        @endphp

                        <div wire:key="notification-{{ $notification->id }}" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 transition-all hover:shadow-md">
                            <div class="flex items-start gap-3">
                                <!-- Checkbox Selection -->
                                <div class="flex-shrink-0 pt-1">
                                    <input type="checkbox" wire:model.live="selected" value="{{ $notification->id }}" class="form-checkbox h-4 w-4 text-primary-600 rounded border-gray-300 focus:ring-primary-500 cursor-pointer" aria-label="Pilih notifikasi">
                                </div>

                                <!-- Icon -->
                                <div class="w-9 h-9 rounded-xl {{ $iconColor }} dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                    @if($type === 'chat_message')
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    @elseif($type === 'help_status' && in_array($data['new_status'] ?? '', ['completed', 'selesai', 'waiting_customer_confirmation']))
                                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    @elseif($type === 'help_status' && in_array($data['new_status'] ?? '', ['partner_on_the_way', 'partner_arrived']))
                                        <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    @else
                                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    @endif
                                </div>

                                <!-- Text Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-1">
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white leading-snug">{{ $titleText }}</h3>
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 whitespace-nowrap">{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>

                                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 leading-relaxed">{{ $bodyText }}</p>

                                    @if(isset($data['from_name']) || isset($data['mitra_name']))
                                        <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-1.5">
                                            Dari: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $data['from_name'] ?? $data['mitra_name'] }}</span>
                                        </div>
                                    @endif

                                    <!-- Bottom Action Links -->
                                    <div class="flex items-center justify-between gap-2 mt-3 pt-2.5 border-t border-gray-100 dark:border-gray-700/60">
                                        <!-- Delete Button -->
                                        <button wire:click="deleteNotification('{{ $notification->id }}')" class="inline-flex items-center gap-1 text-xs text-rose-500 hover:text-rose-700 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            <span>Hapus</span>
                                        </button>

                                        <!-- Help Detail Link -->
                                        @if(isset($data['help_id']))
                                            <a href="{{ route('customer.helps.detail', $data['help_id']) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400">
                                                <span>Lihat Bantuan</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs">
                    {{ $notifications->links('vendor.pagination.superadmin') }}
                </div>
            @else
                <!-- Clean Empty State -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center mt-2">
                    <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg class="w-8 h-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Tidak Ada Notifikasi</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto">Semua notifikasi dan pembaruan pesanan Anda yang masuk akan ditampilkan di halaman ini.</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function triggerDelayedCleanup() {
                try {
                    if (navigator.sendBeacon) {
                        const formData = new FormData();
                        formData.append('_token', '{{ csrf_token() }}');
                        navigator.sendBeacon('{{ route('customer.notifications.cleanup-on-exit') }}', formData);
                    } else {
                        fetch('{{ route('customer.notifications.cleanup-on-exit') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            },
                            keepalive: true
                        });
                    }
                } catch (e) {}
            }

            // Trigger cleanup when app or tab is closed/exited/navigated away
            window.addEventListener('pagehide', triggerDelayedCleanup);
            window.addEventListener('beforeunload', triggerDelayedCleanup);

            // Trigger when app is closed / minimized on mobile devices
            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'hidden') {
                    triggerDelayedCleanup();
                }
            });
        });
    </script>
</div>