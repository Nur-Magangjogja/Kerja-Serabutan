<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-200"
    x-data="{ 
        showNotification: false, 
        notificationMessage: '',
        previousStatus: '{{ $help->status }}'
    }"
    x-init="
        // Update status setiap kali Livewire refresh
        Livewire.hook('morph.updated', () => {
            const currentStatus = '{{ $help->status }}';
            
            // Jika status berubah, tampilkan notifikasi
            if (previousStatus !== currentStatus) {
                console.log('📍 Status berubah:', {
                    old: previousStatus,
                    new: currentStatus
                });
                
                notificationMessage = 'Status pesanan diperbarui';
                showNotification = true;
                setTimeout(() => showNotification = false, 5000);
                
                previousStatus = currentStatus;
            }
        });
    "
    @show-status-notification.window="
        notificationMessage = $event.detail.message;
        showNotification = true;
        setTimeout(() => showNotification = false, 5000);
    "
>
    {{-- Status Notification --}}
    <div x-show="showNotification" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 max-w-sm w-full px-4"
         style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-900" x-text="notificationMessage"></p>
                    <p class="text-xs text-gray-500 mt-0.5">Status pesanan diperbarui</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Header - BRImo Style --}}
    <div class="px-5 pt-5 pb-8 relative overflow-hidden"
        style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
        <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16"></div>

        <div class="relative z-10 max-w-md mx-auto">
            <div class="flex items-center justify-between text-white mb-6">
                <button onclick="window.history.back()" aria-label="Kembali"
                    class="p-2 hover:bg-white/20 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="text-center flex-1 px-2">
                    <h1 class="text-lg font-bold">Detail Pesanan</h1>
                    <p class="text-xs text-white/90 mt-0.5">Informasi lengkap pesanan Anda</p>
                </div>

                <div class="w-9 flex items-center justify-end">
                    <button wire:click="loadHelp" wire:loading.attr="disabled" title="Segarkan Status" class="p-2 hover:bg-white/20 rounded-lg transition cursor-pointer flex items-center justify-center">
                        <svg wire:loading.remove wire:target="loadHelp" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <svg wire:loading wire:target="loadHelp" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Curved separator -->
        <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 72" preserveAspectRatio="none"
            aria-hidden="true">
            <path d="M0,32 C360,72 1080,0 1440,40 L1440,72 L0,72 Z" fill="#f9fafb"></path>
        </svg>
    </div>

    <!-- Content -->
    <div class="bg-gray-50 -mt-6 px-5 pt-6 pb-20 max-w-md mx-auto">
        {{-- GPS Tracker - Auto tracking untuk status aktif --}}
        @if (in_array($help->status, ['memperoleh_mitra', 'taken', 'partner_on_the_way', 'partner_arrived']))
            {{-- <div class="mb-3">
                <livewire:mitra.gps-tracker :helpId="$help->id" :key="'gps-tracker-'.$help->id" />
            </div> --}}
        @endif

        {{-- Modal: Customer confirmed rejection (cancel_rejected) --}}
        <div id="cancel-confirmed-modal"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center px-4"
            style="display:none;">
            <div class="bg-white rounded-2xl max-w-sm w-full p-6" role="dialog" aria-modal="true">
                <div class="text-center">
                    <div class="w-16 h-16 bg-yellow-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Penolakan Dikonfirmasi</h3>
                    <p class="text-sm text-gray-600 mb-4">Customer telah mengkonfirmasi bahwa pembatalan Anda ditolak.
                        Silakan lanjutkan pekerjaan.</p>

                    <div class="flex gap-3">
                        <button id="cancel-confirmed-close"
                            class="flex-1 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition">Tutup</button>
                        <a href="{{ route('mitra.helps.all') }}" id="cancel-confirmed-go"
                            class="flex-1 inline-flex items-center justify-center py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">Kembali
                            ke Bantuan</a>
                    </div>
                </div>
            </div>
        </div>

        @if (session('message'))
            <div
                class="mb-4 p-3 bg-green-50 border border-green-100 rounded-lg text-green-700 text-sm flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('message') }}
            </div>
        @endif

        {{-- Service Info --}}
        <div class="bg-white px-4 py-4 rounded-xl shadow-sm border border-gray-100 mb-3">
            <div class="flex items-start gap-3">
                <div class="w-14 h-14 rounded-lg bg-pink-100 flex items-center justify-center flex-shrink-0">
                    @if ($help->photo)
                        <img src="{{ asset('storage/' . $help->photo) }}" alt="{{ $help->title }}"
                            class="w-full h-full object-cover rounded-lg">
                    @else
                        <svg class="w-7 h-7 text-pink-500" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                        </svg>
                    @endif
                </div>
                <div class="flex-1">
                    <h2 class="font-semibold text-base text-gray-900">{{ $help->title }}</h2>
                    <p class="text-lg font-bold text-blue-600 mt-2">Rp {{ number_format($help->amount, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- Order ID --}}
            <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                <span class="text-sm text-gray-600">ID: <span class="font-semibold text-gray-900">{{ $help->order_id }}</span></span>
                <button wire:click="copyOrderId" class="text-blue-500 text-sm font-semibold flex items-center gap-1">
                    Salin
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Status Badge dengan Alpine.js --}}
        @php
            $status = $help->status;
            $statusLabel = match($status) {
                'menunggu_pembayaran' => 'Menunggu Pembayaran',
                'mencari_mitra', 'menunggu_mitra' => 'Mencari Mitra',
                'memperoleh_mitra' => 'Pesanan Diterima',
                'taken' => 'Pesanan Diambil',
                'partner_on_the_way' => 'Dalam Perjalanan',
                'partner_arrived' => 'Tiba di Lokasi',
                'in_progress', 'sedang_diproses' => 'Sedang Dikerjakan',
                'waiting_customer_confirmation' => 'Menunggu Konfirmasi Customer',
                'selesai', 'completed' => 'Pesanan Selesai',
                'dibatalkan', 'cancelled' => 'Dibatalkan',
                'partner_cancel_requested' => 'Permintaan Pembatalan',
                default => ucfirst(str_replace('_', ' ', $status)),
            };

            $statusBg = match($status) {
                'menunggu_pembayaran' => 'bg-yellow-500',
                'mencari_mitra', 'menunggu_mitra' => 'bg-blue-500',
                'memperoleh_mitra', 'taken' => 'bg-blue-600',
                'partner_on_the_way' => 'bg-blue-700',
                'partner_arrived' => 'bg-green-600',
                'in_progress', 'sedang_diproses' => 'bg-cyan-600',
                'waiting_customer_confirmation' => 'bg-orange-500',
                'selesai', 'completed' => 'bg-green-700',
                'partner_cancel_requested' => 'bg-yellow-600',
                default => 'bg-gray-400'
            };
        @endphp

        <div class="bg-white mt-3 px-4 py-3 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-center">
                <div class="px-4 py-2 rounded-lg {{ $statusBg }} text-white font-semibold text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if(in_array($help->status, ['memperoleh_mitra', 'taken', 'partner_on_the_way']))
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        @elseif($help->status === 'partner_arrived')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        @elseif(in_array($help->status, ['in_progress', 'sedang_diproses']))
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        @elseif($help->status === 'waiting_customer_confirmation')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        @elseif(in_array($help->status, ['selesai', 'completed']))
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        @endif
                    </svg>
                    {{ $statusLabel }}
                </div>
            </div>
        </div>

        {{-- Payment note removed - moved to customer view per request --}}

        {{-- Schedule --}}
        <div class="bg-white px-4 py-4 rounded-xl shadow-sm border border-gray-100 mb-3">
            <h3 class="font-semibold text-sm text-gray-900 mb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Jadwal Permintaan
            </h3>
            <p class="text-sm text-gray-700">
                {{ \Carbon\Carbon::parse($help->scheduled_at ?? $help->created_at)->translatedFormat('l, d F Y') }}
                (Jam {{ \Carbon\Carbon::parse($help->scheduled_at ?? $help->created_at)->format('H:i') }})
            </p>
            <p class="text-xs text-gray-500 mt-1"></p>
        </div>

        {{-- Customer Info --}}
        <div class="bg-white px-4 py-4 rounded-xl shadow-sm border border-gray-100 mb-3">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-sm text-gray-900">Informasi Customer</h3>
            </div>
            <div class="flex items-center gap-3 mb-3">
                @if ($help->user->selfie_photo)
                    <img src="{{ asset('storage/' . $help->user->selfie_photo) }}" alt="{{ $help->user->name }}"
                        class="w-12 h-12 rounded-full object-cover border-2 border-blue-100">
                @else
                    <div
                        class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-lg">
                        {{ strtoupper(substr($help->user->name ?? 'C', 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1">
                    <h4 class="font-semibold text-sm text-gray-900">{{ $help->user->name }}</h4>
                    @if ($help->user->phone)
                        <p class="text-sm text-gray-600 mt-0.5">{{ $help->user->phone }}</p>
                    @endif
                    <p class="text-sm text-gray-600">{{ $help->city->name ?? '-' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                {{-- @if ($help->user->phone)
                    <a href="tel:{{ $help->user->phone }}"
                        class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span class="text-sm font-semibold text-gray-700">Telepon</span>
                    </a>
                @endif --}}
                <a href="{{ route('mitra.chat', ['help' => $help->id]) }}"
                    class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <span class="text-sm font-semibold text-gray-700">Chat</span>
                </a>
            </div>
        </div>

        {{-- Location --}}
        <div class="bg-white px-4 py-4 rounded-xl shadow-sm border border-gray-100 mb-3">
            <h3 class="font-semibold text-sm text-gray-900 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Lokasi
            </h3>
            <div class="space-y-2 text-sm">
                @if ($help->location)
                    <p class="font-medium text-gray-900">{{ $help->location }}</p>
                @endif
                @if ($help->full_address)
                    <p class="text-gray-600">{{ $help->full_address }}</p>
                @endif
                @if ($help->latitude && $help->longitude)
                    <a href="https://www.google.com/maps?q={{ $help->latitude }},{{ $help->longitude }}"
                        target="_blank"
                        class="inline-flex items-center gap-2 mt-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        Buka Peta
                    </a>
                @endif
            </div>
        </div>

        {{-- Additional Details (equipment, coords, timestamps, voucher) --}}
        <div class="bg-white px-4 py-4 rounded-xl shadow-sm border border-gray-100 mb-3">
            <h3 class="font-semibold text-sm text-gray-900 mb-2">Deskripsi & Detail</h3>

            @if(!empty($help->description))
                <div class="mb-3 text-sm text-gray-700 whitespace-pre-line break-words break-all">{{ $help->description }}</div>
            @endif

            <div class="grid grid-cols-2 gap-3 text-sm text-gray-700">
                @if(!empty($help->equipment_provided))
                    <div>
                        <div class="text-xs text-gray-500">Perlengkapan</div>
                        <div class="font-semibold break-words break-all">{{ $help->equipment_provided }}</div>
                    </div>
                @endif

                {{-- <div>
                    <div class="text-xs text-gray-500">Koordinat</div>
                    <div class="font-semibold">{{ $help->latitude ?? '-' }}, {{ $help->longitude ?? '-' }}</div>
                </div> --}}

                @if(!empty($help->voucher_code))
                    <div>
                        <div class="text-xs text-gray-500">Voucher</div>
                        <div class="font-semibold text-red-600">{{ $help->voucher_code }} @if($help->discount_amount) ( -Rp{{ number_format($help->discount_amount,0,',','.') }})@endif</div>
                    </div>
                @endif

                {{-- <div>
                    <div class="text-xs text-gray-500">Biaya Admin</div>
                    <div class="font-semibold">Rp{{ number_format($help->admin_fee ?? $help->booking_fee ?? 0, 0, ',', '.') }}</div>
                </div> --}}
            </div>

            @if(!empty($help->photo))
                <div class="mt-3">
                    <div class="text-xs text-gray-500">Foto Pesanan</div>
                    <img src="{{ asset('storage/' . $help->photo) }}" alt="Foto bantuan" class="w-full mt-2 rounded-lg object-cover">
                </div>
            @endif

            <div class="mt-3 text-xs text-gray-500">
                <div>Dibuat: {{ \Carbon\Carbon::parse($help->created_at)->translatedFormat('d F Y, H:i') }}</div>
                <div>Terakhir diperbarui: {{ \Carbon\Carbon::parse($help->updated_at)->translatedFormat('d F Y, H:i') }}</div>
                @if(!empty($help->scheduled_at))
                    <div>Jadwal: {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d F Y, H:i') }}</div>
                @endif
            </div>
        </div>

        {{-- GPS Simulator (toggle via GPS_SIMULATOR env) --}}
        @if (config('app.gps_simulator', true) &&
                $help->mitra_id === auth()->id() &&
                !in_array($help->status, ['selesai', 'dibatalkan']))
            <div class="mb-3">
                <livewire:mitra.gps-simulator :help-id="$help->id" :key="'gps-simulator-' . $help->id" />
            </div>
        @endif

        {{-- Update Status Section --}}


        {{-- Action Buttons --}}
        

        @if ($help->status === 'partner_arrived')
            <div class="bg-white px-4 py-4 rounded-xl shadow-sm border border-gray-100 mb-3">
                <button wire:click="startService"
                    class="w-full py-3 bg-green-600 text-white rounded-lg font-semibold text-sm hover:bg-green-700 transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Mulai Pekerjaan
                </button>
                <p class="text-xs text-gray-500 text-center mt-2">Klik tombol ini setelah Anda sampai di lokasi dan
                    siap memulai pekerjaan</p>
            </div>
        @endif

        @if ($help->status === 'in_progress' || $help->status === 'sedang_diproses')
            <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 mb-3">
                <button wire:click="openCompletionModal"
                    class="w-full py-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Selesaikan & Upload Bukti Pekerjaan
                </button>
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-2">Upload foto hasil pengerjaan sebagai bukti penyelesaian kepada customer</p>
            </div>
        @endif

        @if ($help->status === 'waiting_customer_confirmation')
            <div class="bg-amber-50 dark:bg-amber-950/40 px-4 py-4 rounded-2xl border border-amber-200 dark:border-amber-800 mb-3">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-500 flex items-center justify-center text-white flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-sm text-gray-900 dark:text-white">Menunggu Konfirmasi Customer</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">Anda telah mengunggah bukti penyelesaian. Menunggu customer memeriksa dan mengonfirmasi penyelesaian tugas.</p>
                        
                        @if($help->proof_photo)
                            <div class="mt-3 pt-3 border-t border-amber-200/60 dark:border-amber-800/40">
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 block mb-1.5">Foto Bukti yang Terkirim:</span>
                                <img src="{{ asset('storage/' . $help->proof_photo) }}" alt="Bukti Pekerjaan" class="w-full max-h-48 object-cover rounded-xl border border-amber-200 shadow-sm">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if (in_array($help->status, ['completed', 'selesai']) && $help->proof_photo)
            <div class="bg-emerald-50 dark:bg-emerald-950/30 px-4 py-3.5 rounded-2xl border border-emerald-200 dark:border-emerald-800 mb-3">
                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 block mb-2">📸 Foto Bukti Penyelesaian Pekerjaan:</span>
                <img src="{{ asset('storage/' . $help->proof_photo) }}" alt="Bukti Selesai" class="w-full max-h-48 object-cover rounded-xl border border-emerald-200 shadow-sm">
                @if($help->completion_notes)
                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-2 italic">"{{ $help->completion_notes }}"</p>
                @endif
            </div>
        @endif

        {{-- Penilaian & Ulasan dari Customer --}}
        @if (in_array($help->status, ['completed', 'selesai']))
            @php
                $customerReview = \App\Models\Rating::where('help_id', $help->id)
                    ->where('type', 'customer_to_mitra')
                    ->first();
            @endphp

            @if ($customerReview)
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/40 dark:to-orange-950/30 rounded-2xl p-4 border border-amber-200/80 dark:border-amber-700/50 shadow-sm mb-3">
                    <div class="flex items-center justify-between mb-2.5 pb-2.5 border-b border-amber-200/60 dark:border-amber-800/40">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm">
                                ⭐
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-gray-900 dark:text-white">Ulasan dari Customer</h4>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ optional($customerReview->created_at)->translatedFormat('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 bg-amber-100 dark:bg-amber-900/60 px-2.5 py-1 rounded-full border border-amber-200">
                            <span class="text-sm font-extrabold text-amber-700 dark:text-amber-300">{{ $customerReview->rating }}.0</span>
                            <div class="flex">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= $customerReview->rating ? 'text-amber-500 fill-current' : 'text-gray-300 dark:text-gray-600 fill-current' }}" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                    </div>

                    @if(!empty($customerReview->review))
                        <div class="bg-white/80 dark:bg-gray-800/80 rounded-xl p-3 border border-amber-100 dark:border-amber-900/40 text-sm text-gray-800 dark:text-gray-200 italic leading-relaxed">
                            "{{ $customerReview->review }}"
                        </div>
                    @else
                        <p class="text-xs text-gray-500 italic">Customer memberikan rating {{ $customerReview->rating }} bintang tanpa ulasan tertulis.</p>
                    @endif
                </div>
            @else
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-3.5 border border-gray-200 dark:border-gray-700 text-center mb-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <span>Menunggu customer memberikan penilaian & ulasan</span>
                    </p>
                </div>
            @endif
        @endif

        @if (in_array($help->status, ['memperoleh_mitra', 'taken', 'partner_on_the_way', 'partner_arrived']) &&
                $help->mitra_id === auth()->id())
            <div class="bg-white px-4 py-4 rounded-xl shadow-sm border border-gray-100 mb-3">
                <button wire:click="openPartnerCancelModal"
                    class="w-full py-3 bg-red-600 text-white rounded-lg font-semibold text-sm hover:bg-red-700 transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Batalkan Bantuan
                </button>
                <p class="text-xs text-gray-500 text-center mt-2">Ajukan pembatalan ke customer. Menunggu konfirmasi
                    customer.</p>
            </div>
        @endif

        {{-- Informasi setelah mitra mengirim permintaan pembatalan - DIGANTI DENGAN MODAL --}}


        {{-- Status Timeline --}}
        @if (
            $help->partner_started_at ||
                $help->partner_arrived_at ||
                $help->service_started_at ||
                $help->service_completed_at ||
                $help->completed_at)
            <div class="bg-white px-4 py-4 rounded-xl shadow-sm border border-gray-100">
                <h3 class="font-semibold text-sm text-gray-900 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Riwayat Timeline
                </h3>
                <div class="space-y-2 text-xs">
                    @if ($help->partner_started_at)
                        <div class="flex items-center justify-between text-gray-600 py-1">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                                <span>Mulai Perjalanan</span>
                            </div>
                            <span
                                class="text-gray-500">{{ \Carbon\Carbon::parse($help->partner_started_at)->format('d M, H:i') }}</span>
                        </div>
                    @endif
                    @if ($help->partner_arrived_at)
                        <div class="flex items-center justify-between text-gray-600 py-1">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                                <span>Tiba di Lokasi</span>
                            </div>
                            <span
                                class="text-gray-500">{{ \Carbon\Carbon::parse($help->partner_arrived_at)->format('d M, H:i') }}</span>
                        </div>
                    @endif
                    @if ($help->service_started_at)
                        <div class="flex items-center justify-between text-gray-600 py-1">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                                <span>Mulai Pengerjaan</span>
                            </div>
                            <span
                                class="text-gray-500">{{ \Carbon\Carbon::parse($help->service_started_at)->format('d M, H:i') }}</span>
                        </div>
                    @endif
                    @if ($help->service_completed_at)
                        <div class="flex items-center justify-between text-gray-600 py-1">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                                <span>Selesai Pengerjaan</span>
                            </div>
                            <span
                                class="text-gray-500">{{ \Carbon\Carbon::parse($help->service_completed_at)->format('d M, H:i') }}</span>
                        </div>
                    @endif
                    @if ($help->status === 'waiting_customer_confirmation')
                        <div class="flex items-center justify-between text-orange-700 font-semibold py-1">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div>
                                <span>Menunggu Konfirmasi Customer</span>
                            </div>
                            <span
                                class="text-orange-600">{{ \Carbon\Carbon::parse($help->service_completed_at ?? now())->format('d M, H:i') }}</span>
                        </div>
                    @endif
                    @if ($help->completed_at)
                        <div class="flex items-center justify-between text-green-700 font-semibold py-1">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-600"></div>
                                <span>Pesanan Selesai</span>
                            </div>
                            <span
                                class="text-green-600">{{ \Carbon\Carbon::parse($help->completed_at)->format('d M, H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
    {{-- Partner Cancel Modal - Bottom Sheet Style --}}
    @if ($showPartnerCancelModal)
        <div class="modal-overlay fixed inset-0 z-[9999] flex items-end justify-center animate-fade-in" 
             style="background: rgba(0,0,0,0.5);" 
             wire:click="$set('showPartnerCancelModal', false)">
            <div class="bg-white rounded-t-3xl w-full max-w-md shadow-2xl animate-slide-up relative" 
                 wire:click.stop 
                 style="padding-bottom: env(safe-area-inset-bottom,24px);">
                
                {{-- Header --}}
                <div class="sticky top-0 bg-white border-b px-5 py-4 rounded-t-3xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Ajukan Pembatalan</h3>
                        <button type="button" 
                                wire:click="$set('showPartnerCancelModal', false)" 
                                class="p-2 hover:bg-gray-100 rounded-full transition">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-5 pb-6">
                    {{-- Info Icon --}}
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>

                    <p class="text-sm text-gray-700 text-center mb-3">
                        Tulis alasan singkat kenapa Anda ingin membatalkan bantuan ini. Customer akan menerima permintaan pembatalan dan dapat menyetujui atau menolak.
                    </p>

                    {{-- Warning Denda Pembatalan --}}
                    <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl p-3.5 mb-4 text-left">
                        <div class="flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-xs font-bold text-amber-900 dark:text-amber-200 mb-0.5">Ketentuan Denda Penalti:</p>
                                <p class="text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                                    Pembatalan tugas yang sudah diambil akan dikenakan denda penalti sebesar <strong>Rp {{ number_format(\App\Models\AppSetting::get('mitra_cancel_penalty_fee', 5000), 0, ',', '.') }}</strong> dari saldo Anda setelah pembatalan disetujui.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Textarea --}}
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Alasan Pembatalan</label>
                        <textarea wire:model.defer="partnerCancelReason" 
                                  rows="4" 
                                  class="w-full p-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 transition"
                                  placeholder="Contoh: kendaraan rusak, alat tidak tersedia, kendala darurat..."></textarea>
                        <p class="text-xs text-gray-500 mt-1">*Opsional - tetapi disarankan mengisi alasan</p>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-3">
                        <button wire:click="$set('showPartnerCancelModal', false)"
                                class="flex-1 px-5 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition">
                            Batal
                        </button>
                        <button wire:click="requestPartnerCancel" 
                                wire:loading.attr="disabled"
                                class="flex-1 px-5 py-3 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="requestPartnerCancel">Kirim Permintaan</span>
                            <span wire:loading wire:target="requestPartnerCancel">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Mengirim...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Styles --}}
        <style>
            @keyframes fade-in {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            @keyframes slide-up {
                from { 
                    transform: translateY(100%);
                    opacity: 0;
                }
                to { 
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            .animate-fade-in {
                animation: fade-in 0.3s ease-out;
            }

            .animate-slide-up {
                animation: slide-up 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
        </style>
    @endif

    {{-- Modal: Status Pembatalan Pending (Menunggu Konfirmasi) - Bottom Sheet --}}
    @if ($help->status === 'partner_cancel_requested' && $help->mitra_id === auth()->id())
        <div class="modal-overlay fixed inset-0 z-[9999] flex items-end justify-center animate-fade-in" 
             style="background: rgba(0,0,0,0.5);">
            <div class="bg-white rounded-t-3xl w-full max-w-md shadow-2xl animate-slide-up relative" 
                 style="padding-bottom: env(safe-area-inset-bottom,24px);">
                
                {{-- Header --}}
                <div class="sticky top-0 bg-gradient-to-r from-yellow-500 to-orange-500 px-5 py-4 rounded-t-3xl">
                    <div class="flex items-center justify-between text-white">
                        <h3 class="text-lg font-bold">Menunggu Konfirmasi</h3>
                        <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-5 pb-6">
                    {{-- Icon & Animation --}}
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center relative">
                            <svg class="w-10 h-10 text-yellow-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="absolute -top-1 -right-1 w-6 h-6 bg-yellow-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <h4 class="text-center font-bold text-lg text-gray-900 mb-2">Permintaan Pembatalan Terkirim</h4>
                    <p class="text-sm text-gray-700 text-center mb-5">
                        Anda telah mengajukan pembatalan pesanan ini. Menunggu konfirmasi dari customer.
                    </p>

                    {{-- Detail Info --}}
                    <div class="bg-gray-50 rounded-xl p-4 mb-5 space-y-3">
                        @if ($help->partner_cancel_reason)
                            <div>
                                <p class="text-xs font-semibold text-gray-500 mb-1">Alasan Pembatalan:</p>
                                <p class="text-sm text-gray-900">{{ $help->partner_cancel_reason }}</p>
                            </div>
                        @endif
                        @if ($help->partner_cancel_requested_at)
                            <div class="flex items-center gap-2 text-xs text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Diajukan: {{ \Carbon\Carbon::parse($help->partner_cancel_requested_at)->translatedFormat('d F Y, H:i') }} WIB
                            </div>
                        @endif
                    </div>

                    {{-- Loading Animation --}}
                    <div class="flex items-center justify-center gap-2 mb-5">
                        <div class="w-2 h-2 bg-yellow-500 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                        <div class="w-2 h-2 bg-yellow-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-yellow-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>

                    {{-- Info Text --}}
                    <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mb-5">
                        <div class="flex gap-2">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-xs text-blue-900">
                                Modal ini akan otomatis update saat customer memberikan konfirmasi. Halaman akan refresh otomatis setiap 5 detik.
                            </p>
                        </div>
                    </div>

                    {{-- Button --}}
                    <a href="{{ route('mitra.helps.all') }}"
                       class="w-full px-5 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition text-center block">
                        Kembali ke Daftar Bantuan
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Pembatalan Diterima - Bottom Sheet --}}
    @if ((in_array($help->status, ['cancelled']) || $help->partner_cancel_prev_status === 'cancel_accepted') && 
         $help->partner_cancel_requested_at &&
         !session()->has('cancel_accepted_modal_shown_' . $help->id))
        <div class="modal-overlay fixed inset-0 z-[9999] flex items-end justify-center animate-fade-in" 
             style="background: rgba(0,0,0,0.7);"
             x-data="{ show: true }"
             x-show="show">
            <div class="bg-white rounded-t-3xl w-full max-w-md shadow-2xl animate-slide-up relative" 
                 @click.stop
                 style="padding-bottom: env(safe-area-inset-bottom,24px);">
                
                {{-- Header --}}
                <div class="sticky top-0 bg-gradient-to-r from-green-500 to-emerald-500 px-5 py-4 rounded-t-3xl">
                    <div class="text-center text-white">
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <h3 class="text-lg font-bold">Pembatalan Diterima</h3>
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-5 pb-6">
                    {{-- Success Icon --}}
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>

                    <h4 class="text-center font-bold text-lg text-gray-900 mb-2">Permintaan Diterima!</h4>
                    <p class="text-sm text-gray-700 text-center mb-5">
                        Customer telah menyetujui permintaan pembatalan Anda. Pesanan telah dikembalikan ke daftar bantuan.
                    </p>

                    {{-- Success Message --}}
                    <div class="bg-green-50 border border-green-100 rounded-lg p-4 mb-5">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-green-900 mb-1">Pembatalan Berhasil Disetujui</p>
                                <p class="text-xs text-green-800">Pesanan telah dikembalikan ke sistem agar dapat diambil oleh Rekan Jasa lain.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="space-y-2">
                        <a href="{{ route('mitra.dashboard') }}"
                           wire:click="acknowledgeAcceptedCancellation"
                           class="w-full px-5 py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition text-center block">
                            Ke Dashboard
                        </a>
                        <a href="{{ route('mitra.helps.all') }}"
                           wire:click="acknowledgeAcceptedCancellation"
                           class="w-full px-5 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition text-center block">
                            Lihat Bantuan Lain
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Pembatalan Ditolak - Bottom Sheet --}}
    @if ($help->partner_cancel_prev_status === 'cancel_rejected' &&
         !session()->has('cancel_rejected_modal_shown_' . $help->id))
        <div class="modal-overlay fixed inset-0 z-[9999] flex items-end justify-center animate-fade-in" 
             style="background: rgba(0,0,0,0.7);"
             x-data="{ show: true }"
             x-show="show">
            <div class="bg-white rounded-t-3xl w-full max-w-md shadow-2xl animate-slide-up relative" 
                 @click.stop
                 style="padding-bottom: env(safe-area-inset-bottom,24px);">
                
                {{-- Header --}}
                <div class="sticky top-0 bg-gradient-to-r from-red-500 to-pink-500 px-5 py-4 rounded-t-3xl">
                    <div class="text-center text-white">
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <h3 class="text-lg font-bold">Pembatalan Ditolak</h3>
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-5 pb-6">
                    {{-- Error Icon --}}
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    </div>

                    <h4 class="text-center font-bold text-lg text-gray-900 mb-2">Permintaan Ditolak</h4>
                    <p class="text-sm text-gray-700 text-center mb-5">
                        Customer telah menolak permintaan pembatalan Anda. Silakan lanjutkan pekerjaan sesuai pesanan.
                    </p>

                    {{-- Warning Message --}}
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-5">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-amber-900 mb-1">Lanjutkan Pekerjaan</p>
                                <p class="text-xs text-amber-800">Harap segera lanjutkan pekerjaan untuk menghindari rating buruk dari customer.</p>
                            </div>
                        </div>
                    </div>

                    @if($help->partner_cancel_reason)
                        <div class="bg-gray-50 rounded-xl p-3 mb-5">
                            <p class="text-xs font-semibold text-gray-500 mb-1">Alasan Anda:</p>
                            <p class="text-sm text-gray-700 italic">"{{ $help->partner_cancel_reason }}"</p>
                        </div>
                    @endif

                    {{-- Buttons --}}
                    <div class="space-y-2">
                        <button wire:click="acknowledgeRejectedCancellation"
                                class="w-full px-5 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition">
                            Mengerti, Lanjutkan
                        </button>
                        <a href="tel:{{ $help->user->phone ?? '' }}"
                           class="w-full px-5 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition text-center block flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Hubungi Customer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Completion Proof Upload Modal --}}
    @if($showCompletionModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-md shadow-2xl overflow-hidden animate-scale-in"
                 @click.stop>
                
                {{-- Header --}}
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-base leading-tight">Bukti Selesai Pekerjaan</h3>
                            <p class="text-xs text-blue-100">Kirim foto hasil kerja ke customer</p>
                        </div>
                    </div>
                    <button wire:click="closeCompletionModal" class="p-1.5 rounded-lg hover:bg-white/20 transition">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Form Body --}}
                <div class="p-6 space-y-4">
                    {{-- Upload Area --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                            Upload Foto Bukti Jasa <span class="text-rose-500">*</span>
                        </label>

                        @if ($proof_photo)
                            <div class="relative rounded-2xl overflow-hidden border-2 border-primary-500 bg-gray-50 dark:bg-gray-800 mb-2">
                                @php
                                    $canPreview = false;
                                    try {
                                        $canPreview = method_exists($proof_photo, 'temporaryUrl') && $proof_photo->isPreviewable();
                                    } catch (\Throwable $e) {
                                        $canPreview = false;
                                    }
                                @endphp
                                @if ($canPreview)
                                    <img src="{{ $proof_photo->temporaryUrl() }}" alt="Preview Bukti" class="w-full h-48 object-cover">
                                @else
                                    <div class="w-full h-48 flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 p-4 text-center">
                                        <svg class="w-10 h-10 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-xs font-semibold">{{ $proof_photo->getClientOriginalName() }}</span>
                                    </div>
                                @endif
                                <button type="button" wire:click="$set('proof_photo', null)" class="absolute top-2 right-2 p-1.5 bg-rose-600 text-white rounded-full shadow-lg hover:bg-rose-700 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @else
                            <label class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl cursor-pointer bg-gray-50 dark:bg-gray-700/50 hover:bg-blue-50/50 dark:hover:bg-gray-700 transition">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6 px-4 text-center">
                                    <svg class="w-10 h-10 text-primary-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-200">Klik untuk ambil foto / upload</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">Format: PNG, JPG, atau JPEG (Maks. 5MB)</p>
                                </div>
                                <input type="file" wire:model="proof_photo" accept="image/png, image/jpeg, image/jpg, .png, .jpg, .jpeg" class="hidden" capture="environment">
                            </label>
                        @endif

                        <div wire:loading wire:target="proof_photo" class="text-xs text-blue-600 font-medium mt-1 flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            Mengunggah foto bukti...
                        </div>

                        @error('proof_photo')
                            <p class="text-xs text-rose-500 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Completion Notes --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                            Catatan Pengerjaan (Opsional)
                        </label>
                        <textarea wire:model="completion_notes" rows="3" placeholder="Contoh: Pekerjaan perbaikan pipa wastafel sudah tuntas dan tidak ada kebocoran lagi..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 text-xs focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-2.5 pt-2">
                        <button type="button" wire:click="closeCompletionModal"
                            class="flex-1 py-3 px-4 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-semibold text-xs hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            Batal
                        </button>
                        <button type="button" wire:click="submitCompletionProof" wire:loading.attr="disabled"
                            class="flex-1 py-3 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold text-xs shadow-md hover:from-blue-700 hover:to-indigo-700 transition flex items-center justify-center gap-1.5 disabled:opacity-50">
                            <span wire:loading.remove wire:target="submitCompletionProof">Kirim Bukti & Selesai</span>
                            <span wire:loading wire:target="submitCompletionProof" class="inline-flex items-center gap-1">
                                <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>