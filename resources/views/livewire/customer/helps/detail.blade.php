    <div class="min-h-screen bg-gray-50" 
    wire:poll.10s.visible="loadHelp"
    x-data="{ 
        showNotification: false, 
        notificationMessage: '',
        trackingData: {
            partnerLat: {{ $help->partner_current_lat ?? ($help->mitra->latitude ?? ($help->latitude ? $help->latitude - 0.01 : -6.2088)) }},
            partnerLng: {{ $help->partner_current_lng ?? ($help->mitra->longitude ?? ($help->longitude ? $help->longitude - 0.01 : 106.8456)) }},
            customerLat: {{ $help->latitude ?? -6.2088 }},
            customerLng: {{ $help->longitude ?? 106.8456 }},
            partnerName: '{{ $help->mitra->name ?? "Mitra" }}',
            location: '{{ $help->location ?? "Tujuan" }}'
        }
    }"
    x-init="
        // Update tracking data setiap kali Livewire refresh
        Livewire.hook('morph.updated', () => {
            const oldLat = trackingData.partnerLat;
            const oldLng = trackingData.partnerLng;
            
            trackingData.partnerLat = {{ $help->partner_current_lat ?? ($help->mitra->latitude ?? ($help->latitude ? $help->latitude - 0.01 : -6.2088)) }};
            trackingData.partnerLng = {{ $help->partner_current_lng ?? ($help->mitra->longitude ?? ($help->longitude ? $help->longitude - 0.01 : 106.8456)) }};
            trackingData.customerLat = {{ $help->latitude ?? -6.2088 }};
            trackingData.customerLng = {{ $help->longitude ?? 106.8456 }};
            
            // Log perubahan lokasi
            if (oldLat !== trackingData.partnerLat || oldLng !== trackingData.partnerLng) {
                console.log('📍 Lokasi mitra diperbarui:', {
                    old: { lat: oldLat, lng: oldLng },
                    new: { lat: trackingData.partnerLat, lng: trackingData.partnerLng }
                });
            }
            
            // Trigger update ke peta jika modal terbuka
            if (window.updateMapFromAlpine) {
                window.updateMapFromAlpine();
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

        {{-- Live tracking summary (updates continuously, visible without opening modal) --}}
        <div id="live-tracking-summary" wire:ignore class="bg-white mt-2 px-4 py-2 rounded-lg shadow-sm border border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-600">Estimasi Tiba</p>
                    <p id="summary-eta" class="text-sm font-semibold text-blue-700">Menghitung...</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-600">Jarak</p>
                <p id="summary-distance" class="text-sm font-semibold text-blue-700">-</p>
            </div>
        </div>
    </div>

    {{-- Header - match other customer pages (gradient BRImo style) --}}
    <div class="px-5 pt-5 pb-8 relative overflow-hidden" style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
        <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16"></div>

        <div class="relative z-10 max-w-md mx-auto">
            <div class="flex items-center justify-between text-white mb-6">
                <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="text-center flex-1 px-2">
                    <h1 class="text-lg font-bold">Detail Pesanan</h1>
                    <p class="text-xs text-white/90 mt-0.5">Detail permintaan bantuan Anda</p>
                </div>

                {{-- <div class="w-9">
                    <button class="p-2 hover:bg-white/20 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01" />
                        </svg>
                    </button>
                </div> --}}
            </div>
        </div>        

            <!-- Curved separator (SVG) to create non-flat divider into content -->
            <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 72" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,32 C360,72 1080,0 1440,40 L1440,72 L0,72 Z" fill="#ffffff"></path>
            </svg>
    </div>

    <!-- Content -->
    <div class="bg-white rounded-t-3xl -mt-6 px-5 pt-6 pb-8 max-w-md mx-auto">
        {{-- Order ID --}}
        <div class="bg-white px-4 py-3 flex items-center justify-between rounded-xl shadow-sm border border-gray-100">
            <span class="text-sm text-gray-600">ID Pesanan: <span class="font-semibold text-gray-900">{{ $help->order_id }}</span></span>
            <button wire:click="copyOrderId" class="text-blue-500 text-sm font-semibold flex items-center gap-1">
                Salin
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </button>
        </div>

        {{-- Service Info --}}
        <div class="bg-white mt-2 px-4 py-4 rounded-xl shadow-sm border border-gray-100">
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 rounded-lg bg-pink-100 flex items-center justify-center flex-shrink-0">
                    @if($help->photo)
                        <img src="{{ asset('storage/' . $help->photo) }}" alt="{{ $help->title }}" class="w-full h-full object-cover rounded-lg">
                    @else
                        <svg class="w-6 h-6 text-pink-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                        </svg>
                    @endif
                </div>
                <div class="flex-1">
                    <h2 class="font-semibold text-base text-gray-900">{{ $help->title }}</h2>
                </div>
            </div>

                {{-- Partner Info --}}
            @if($help->mitra)
                <div class="mt-4 p-3 bg-white rounded-xl flex items-center justify-between shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3">
                        @if($help->mitra->selfie_photo)
                            <img src="{{ asset('storage/' . $help->mitra->selfie_photo) }}" alt="{{ $help->mitra->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-blue-100">
                        @else
                            <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($help->mitra->name ?? 'M', 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="font-semibold text-sm text-gray-900">{{ $help->mitra->name ?? 'Mitra' }}</h3>
                            <div class="flex items-center gap-1 mt-0.5">
                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                @php
                                    $mitra = $help->mitra;
                                    $avgRating = $mitra ? ($mitra->mitra_average_rating ?? ($mitra->rating ?? 0)) : 0;
                                    $ratingCount = $mitra ? ($mitra->mitra_rating_count ?? null) : null;
                                @endphp
                                <span class="text-sm font-semibold text-gray-900">{{ number_format($avgRating, 2) }}</span>
                                @if($ratingCount)
                                    <span class="text-xs text-gray-400 ml-2">({{ $ratingCount }})</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        {{-- <a href="tel:{{ $help->mitra->phone ?? '' }}" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </a> --}}
                        <a href="{{ route('customer.chat', $help->id) }}" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </a>
                    </div>
                </div>

            @endif

            {{-- Description & Additional Details --}}
            <div class="bg-white mt-2 px-4 py-4 rounded-lg shadow-sm border border-gray-100">
                <h3 class="font-bold text-sm text-gray-900 mb-3">Deskripsi & Detail</h3>

                @if(!empty($help->description))
                    <div class="mb-3">
                        <p class="text-sm text-gray-700 whitespace-pre-line break-words break-all">{{ $help->description }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-3 text-sm text-gray-700">
                    @if(!empty($help->equipment_provided))
                        <div>
                            <div class="text-xs text-gray-500">Perlengkapan</div>
                            <div class="font-semibold break-words break-all">{{ $help->equipment_provided }}</div>
                        </div>
                    @endif

                    @if($help->mitra && !empty($help->mitra->phone))
                        <div>
                            <div class="text-xs text-gray-500">Kontak Mitra</div>
                            <div class="font-semibold"><a href="tel:{{ $help->mitra->phone }}" class="text-blue-600">{{ $help->mitra->phone }}</a></div>
                        </div>
                    @endif

                    @if(!empty($help->city->name) || !empty($help->province->name))
                        <div>
                            <div class="text-xs text-gray-500">Kota / Provinsi</div>
                            <div class="font-semibold">{{ $help->city->name ?? '-' }}{{ $help->province ? (', ' . $help->province->name) : '' }}</div>
                        </div>
                    @endif

                    {{-- <div>
                        <div class="text-xs text-gray-500">Koordinat</div>
                        <div class="font-semibold">{{ $help->latitude ? $help->latitude : '-' }}, {{ $help->longitude ? $help->longitude : '-' }}</div>
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

            @php
                $status = $help->status;
                $statusLabel = match($status) {
                    'menunggu_pembayaran' => 'Pembayaran',
                    'mencari_mitra', 'menunggu_mitra', 'memperoleh_mitra' => 'Mencari Rekan Jasa',
                    'taken' => 'Menunggu Rekan Jasa berangkat',
                    'partner_on_the_way' => 'Rekan Jasa menuju ke lokasi',
                    'partner_arrived' => 'Rekan Jasa tiba di lokasi',
                    'in_progress', 'sedang_diproses' => 'Pelayanan dalam proses',
                    'waiting_customer_confirmation' => 'Menunggu konfirmasi Anda',
                    'selesai', 'completed' => 'Pesanan selesai',
                    'dibatalkan', 'cancelled' => 'Dibatalkan',
                    default => ucfirst(str_replace('_', ' ', $status)),
                };

                $statusBg = match($status) {
                    'menunggu_pembayaran' => 'bg-yellow-500',
                    'mencari_mitra', 'menunggu_mitra', 'memperoleh_mitra' => 'bg-blue-600',
                    'taken', 'partner_on_the_way' => 'bg-blue-600',
                    'partner_arrived' => 'bg-green-600',
                    'in_progress', 'sedang_diproses' => 'bg-cyan-600',
                    'waiting_customer_confirmation' => 'bg-orange-500',
                    'selesai', 'completed' => 'bg-green-700',
                    default => 'bg-gray-400'
                };
            @endphp

            <button 
                @if(in_array($help->status, ['taken', 'partner_on_the_way', 'partner_arrived']))
                    wire:click="showTrackingMap"
                @endif
                class="w-full mt-3 py-2.5 text-white rounded-lg font-semibold text-sm hover:opacity-95 transition flex items-center justify-center gap-2 {{ $statusBg }} {{ in_array($help->status, ['taken', 'partner_on_the_way', 'partner_arrived']) ? 'cursor-pointer' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if(in_array($help->status, ['taken', 'partner_on_the_way', 'partner_arrived']))
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    @endif
                </svg>
                {{ $statusLabel }}
                @if(in_array($help->status, ['taken', 'partner_on_the_way', 'partner_arrived']))
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                @endif
            </button>
        </div>

        {{-- Warning Info --}}
        {{-- <div class="bg-yellow-50 mt-2 px-4 py-3 rounded-lg shadow-sm border border-yellow-100">
            <div class="flex gap-3">
                <div class="w-9 h-9 rounded-md bg-yellow-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-sm text-gray-900 mb-1">Selalu jaga keamanan pesananmu</h3>
                    <p class="text-xs text-gray-700 leading-relaxed">Pastikan hanya transaksi di aplikasi agar pesananmu terlindungi asuransi. Laporkan Rekan Jasa yang meminta pembayaran di luar aplikasi untuk dapatkan pengembalian dana atau layanan gratis.</p>
                </div>
            </div>
        </div> --}}

        {{-- Location --}}
        <div class="bg-white mt-2 px-4 py-4 rounded-lg shadow-sm border border-gray-100">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="font-bold text-sm text-gray-900 mb-1">Lokasi</h3>
                    <p class="text-sm text-gray-700">{{ $help->location ?? $help->full_address ?? 'Rumah warna coklat' }}</p>
                    @if($help->full_address)
                        <p class="text-xs text-gray-500 mt-1"><span class="font-semibold">Detail :</span> {{ $help->full_address }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Schedule --}}
        <div class="bg-white mt-2 px-4 py-4 rounded-lg shadow-sm border border-gray-100">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="font-bold text-sm text-gray-900 mb-1">Jadwal Pesanan</h3>
                    <p class="text-sm text-gray-700">
                        {{ \Carbon\Carbon::parse($help->scheduled_at ?? $help->created_at)->translatedFormat('l, d F Y') }} 
                        (Jam {{ \Carbon\Carbon::parse($help->scheduled_at ?? $help->created_at)->format('H:i') }}
                        {{-- {{ \Carbon\Carbon::parse($help->scheduled_at ?? $help->created_at)->addHour()->format('H:i') }}  --}}
                        WIB)
                    </p>
                    <p class="text-xs text-gray-500 mt-1">*Jadwal tertera dalam WIB</p>
                </div>
            </div>
        </div>

        {{-- Status Timeline - Redesigned visual to match reference --}}
        <div class="bg-white mt-2 px-4 py-4 rounded-lg shadow-sm border border-gray-100">
            <h3 class="font-bold text-sm text-gray-900 mb-4">Status Pesanan</h3>

            @php
                $statuses = [
                    [ 'key' => 'payment', 'title' => 'Pembayaran', 'time' => $help->created_at, 'active' => in_array($help->status, ['menunggu_pembayaran','mencari_mitra','menunggu_mitra','memperoleh_mitra','taken','partner_on_the_way','partner_arrived','in_progress','sedang_diproses','waiting_customer_confirmation','selesai','completed']), 'current' => $help->status === 'menunggu_pembayaran' ],
                    [ 'key' => 'searching', 'title' => 'Mencari Rekan Jasa', 'time' => $help->mitra_assigned_at ?? $help->taken_at, 'active' => in_array($help->status, ['mencari_mitra','menunggu_mitra','memperoleh_mitra','taken','partner_on_the_way','partner_arrived','in_progress','sedang_diproses','waiting_customer_confirmation','selesai','completed']), 'current' => in_array($help->status, ['mencari_mitra','menunggu_mitra','memperoleh_mitra']) ],
                    [ 'key' => 'accepted', 'title' => 'Menunggu Rekan Jasa berangkat', 'time' => $help->taken_at, 'active' => in_array($help->status, ['taken','partner_on_the_way','partner_arrived','in_progress','sedang_diproses','waiting_customer_confirmation','selesai','completed']), 'current' => $help->status === 'taken' ],
                    [ 'key' => 'on_the_way', 'title' => 'Rekan Jasa menuju ke lokasi', 'time' => $help->partner_started_moving_at, 'active' => in_array($help->status, ['partner_on_the_way','partner_arrived','in_progress','sedang_diproses','waiting_customer_confirmation','selesai','completed']), 'current' => $help->status === 'partner_on_the_way' ],
                    [ 'key' => 'arrived', 'title' => 'Rekan Jasa tiba di lokasi', 'time' => $help->partner_arrived_at, 'active' => in_array($help->status, ['partner_arrived','in_progress','sedang_diproses','waiting_customer_confirmation','selesai','completed']), 'current' => $help->status === 'partner_arrived' ],
                    [ 'key' => 'in_progress', 'title' => 'Pelayanan dalam proses', 'time' => $help->service_started_at, 'active' => in_array($help->status, ['in_progress','sedang_diproses','waiting_customer_confirmation','selesai','completed']), 'current' => in_array($help->status, ['in_progress','sedang_diproses']) ],
                    [ 'key' => 'waiting_confirmation', 'title' => 'Menunggu konfirmasi Anda', 'time' => $help->service_completed_at, 'active' => in_array($help->status, ['waiting_customer_confirmation','selesai','completed']), 'current' => $help->status === 'waiting_customer_confirmation' ],
                    [ 'key' => 'completed', 'title' => 'Pesanan selesai', 'time' => $help->completed_at, 'active' => in_array($help->status, ['selesai','completed']), 'current' => in_array($help->status, ['selesai','completed']) ]
                ];
            @endphp

            <div>
                <div class="space-y-4">
                    @foreach($statuses as $index => $status)
                        <div class="flex items-start">
                            {{-- left column: dot + connector --}}
                            <div class="w-12 flex flex-col items-center">
                                {{-- dot --}}
                                <div class="relative z-10">
                                    @if($status['active'])
                                        @if($status['current'])
                                            <div class="w-5 h-5 rounded-full border-2 border-blue-500 bg-white flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-blue-500 rounded-full animate-pulse"></div>
                                            </div>
                                        @else
                                            <div class="w-4 h-4 rounded-full bg-blue-500"></div>
                                        @endif
                                    @else
                                        <div class="w-4 h-4 rounded-full border-2 border-gray-300 bg-white"></div>
                                    @endif
                                </div>

                                {{-- connector below dot (except last) --}}
                                @if(!$loop->last)
                                    <div class="flex-1 w-px mt-2 {{ $status['active'] ? 'bg-blue-200' : 'bg-gray-200' }}"></div>
                                @endif
                            </div>

                            {{-- content --}}
                            <div class="flex-1 pl-2">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-semibold {{ $status['active'] ? 'text-gray-900' : 'text-gray-400' }}">{{ $status['title'] }}</h4>
                                    <div class="text-xs {{ $status['active'] ? 'text-gray-600' : 'text-gray-400' }} whitespace-nowrap">
                                        @if($status['time'])
                                            {{ \Carbon\Carbon::parse($status['time'])->format('d M, H:i') }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>

                                @if($status['current'])
                                    <div class="mt-1 text-xs">
                                        @if($status['key'] === 'on_the_way' && $help->partner_current_lat && $help->latitude)
                                            @php
                                                $earthRadius = 6371000;
                                                $lat1 = deg2rad($help->partner_current_lat);
                                                $lat2 = deg2rad($help->latitude);
                                                $latDiff = deg2rad($help->latitude - $help->partner_current_lat);
                                                $lngDiff = deg2rad($help->longitude - $help->partner_current_lng);
                                                $a = sin($latDiff / 2) * sin($latDiff / 2) + cos($lat1) * cos($lat2) * sin($lngDiff / 2) * sin($lngDiff / 2);
                                                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                                                $distance = round($earthRadius * $c);
                                            @endphp
                                            <p class="text-xs text-blue-600 flex items-center gap-1">Jarak: {{ $distance > 1000 ? number_format($distance/1000, 1) . ' km' : $distance . ' m' }}</p>
                                        @elseif($status['key'] === 'accepted')
                                            {{-- <p class="text-xs text-blue-600">GPS tracking aktif</p> --}}
                                        @elseif($status['key'] === 'arrived')
                                            <p class="text-xs text-green-600">Rekan jasa sudah sampai</p>
                                        @elseif($status['key'] === 'in_progress')
                                            <p class="text-xs text-blue-600">Pekerjaan sedang berlangsung</p>
                                        @elseif($status['key'] === 'waiting_confirmation')
                                            <p class="text-xs text-orange-600 font-semibold">Silakan konfirmasi pesanan selesai</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Confirmation Button with Proof Photo --}}
        @if($help->status === 'waiting_customer_confirmation')
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 mt-2 px-5 py-5 border border-amber-200 rounded-2xl shadow-sm">
                <div class="flex items-start gap-3 mb-4">
                    <div class="w-11 h-11 rounded-xl bg-amber-500 flex items-center justify-center flex-shrink-0 text-white shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-sm text-gray-900 mb-1">Konfirmasi Penyelesaian Pesanan</h3>
                        <p class="text-xs text-gray-700 leading-relaxed">Mitra telah menyelesaikan pekerjaan dan mengirimkan bukti hasil jasa. Silakan periksa bukti foto dan lakukan konfirmasi.</p>
                    </div>
                </div>

                @if($help->proof_photo)
                    <div class="mb-4 p-3 bg-white rounded-xl border border-amber-200">
                        <span class="text-xs font-bold text-gray-800 block mb-1.5 flex items-center gap-1">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Foto Bukti Hasil Pengerjaan:
                        </span>
                        <a href="{{ asset('storage/' . $help->proof_photo) }}" target="_blank" rel="noopener">
                            <img src="{{ asset('storage/' . $help->proof_photo) }}" alt="Bukti Pengerjaan" class="w-full max-h-52 object-cover rounded-lg border border-gray-100 hover:opacity-95 transition cursor-pointer shadow-xs">
                        </a>
                        @if($help->completion_notes)
                            <p class="text-xs text-gray-600 mt-2 italic bg-amber-50/50 p-2 rounded-md">"{{ $help->completion_notes }}"</p>
                        @endif
                    </div>
                @endif

                <button wire:click="confirmCompletion" 
                        class="w-full bg-gradient-to-r from-emerald-500 to-green-600 text-white font-bold py-3.5 px-4 rounded-xl hover:from-emerald-600 hover:to-green-700 transition-all duration-200 shadow-md flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Konfirmasi Pesanan Selesai
                </button>
                <p class="text-[11px] text-center text-gray-500 mt-2">Dengan mengonfirmasi, pesanan dinyatakan tuntas dan dana diteruskan ke mitra</p>
            </div>
        @endif

        {{-- Rating Form --}}
        @if(in_array($help->status, ['selesai', 'completed']))
            @php
                $customerRating = \App\Models\Rating::where('help_id', $help->id)
                    ->where('rater_id', auth()->id())
                    ->where('type', 'customer_to_mitra')
                    ->first();
            @endphp

            @if($customerRating)
                {{-- Already Rated - Show Rating --}}
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 mt-2 px-4 py-4 border border-green-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-sm text-gray-900 mb-2">Rating Anda</h3>
                            <div class="flex items-center gap-1 mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= $customerRating->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                                <span class="ml-2 text-sm font-semibold text-gray-900">{{ $customerRating->rating }}/5</span>
                            </div>
                            @if($customerRating->review)
                                <p class="text-sm text-gray-700 italic">"{{ $customerRating->review }}"</p>
                            @endif
                            <p class="text-xs text-gray-500 mt-2">{{ $customerRating->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            @else
                {{-- Rating Form --}}
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 mt-2 px-4 py-4 border border-yellow-200 rounded-lg">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-12 h-12 rounded-full bg-yellow-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-sm text-gray-900 mb-1">Bagaimana Pengalaman Anda?</h3>
                            <p class="text-xs text-gray-700">Berikan rating untuk {{ $help->mitra->name ?? 'mitra' }}</p>
                        </div>
                    </div>

                    {{-- Star Rating --}}
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Rating *</label>
                        <div class="flex items-center gap-2">
                            @for($i = 1; $i <= 5; $i++)
                                <button 
                                    type="button"
                                    wire:click="setRating({{ $i }})"
                                    class="focus:outline-none transition-transform hover:scale-110">
                                    <svg class="w-10 h-10 {{ $rating >= $i ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                            @endfor
                            @if($rating > 0)
                                <span class="ml-2 text-sm font-semibold text-gray-900">{{ $rating }}/5</span>
                            @endif
                        </div>
                        @error('rating')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Review Text --}}
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Ulasan (Opsional)</label>
                        <textarea 
                            wire:model="review"
                            rows="3"
                            placeholder="Ceritakan pengalaman Anda..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent text-sm"
                            maxlength="500"></textarea>
                        <div class="flex justify-between items-center mt-1">
                            @error('review')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @else
                                <p class="text-xs text-gray-500">Maksimal 500 karakter</p>
                            @enderror
                            <p class="text-xs text-gray-500">{{ strlen($review ?? '') }}/500</p>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button 
                        wire:click="submitRating"
                        wire:loading.attr="disabled"
                        class="w-full bg-gradient-to-r from-yellow-500 to-orange-500 text-gray-900 font-semibold py-3 px-4 rounded-lg hover:from-yellow-600 hover:to-orange-600 transition-all duration-200 shadow-md flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove class="text-gray-900 font-semibold">Kirim Rating</span>
                        <span wire:loading class="text-gray-900 font-semibold">Mengirim...</span>
                    </button>
                </div>
            @endif
        @endif

        {{-- Payment Details --}}
        <div class="bg-white mt-2 px-4 py-4">
            <h3 class="font-bold text-sm text-gray-900 mb-4">Rincian Pembayaran</h3>

            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-700">Harga Asli</span>
                    <span class="font-semibold text-gray-900">Rp{{ number_format($help->amount, 0, ',', '.') }}</span>
                </div>

                {{-- Perlengkapan ditampilkan di bagian "Deskripsi & Detail" di atas --}}

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-700">Biaya Admin</span>
                    <span class="font-semibold text-gray-900">Rp{{ number_format($help->admin_fee ?? $help->booking_fee ?? 0, 0, ',', '.') }}</span>
                </div>

                @if(!empty($help->voucher_code) && ($help->discount_amount ?? 0) > 0)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-red-500 font-semibold">{{ $help->voucher_code }}</span>
                        </div>
                        <span class="font-semibold text-red-500">-Rp{{ number_format($help->discount_amount ?? 0, 0, ',', '.') }}</span>
                    </div>
                @endif

                <div class="border-t pt-3 flex items-center justify-between">
                    <span class="font-bold text-gray-900">Total Pembayaran</span>
                    <span class="font-bold text-gray-900">Rp{{ number_format($help->total_amount ?? ($help->amount + ($help->admin_fee ?? $help->booking_fee ?? 0) - ($help->discount_amount ?? 0)), 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                <p class="text-xs text-gray-700 leading-relaxed">
                    Kamu dapat meminta tindakan tambahan selama sesi layanan berlangsung (mis. tambahan bahan atau tindakan kecil). Pastikan semua pembayaran dilakukan melalui aplikasi agar pesananmu tercatat dan terlindungi. Jika ada masalah kualitas, ajukan keluhan atau klaim garansi dalam waktu 1x24 jam setelah layanan selesai.
                </p>
            </div>
        </div>

        {{-- Cancel Button --}}
        @if(in_array($help->status, ['menunggu_pembayaran', 'mencari_mitra', 'menunggu_mitra']))
            <div class="bg-white mt-2 px-4 py-4">
                <button wire:click="confirmCancel" class="w-full py-3 border-2 border-red-500 text-red-500 rounded-lg font-semibold text-sm hover:bg-red-50 transition">
                    Batalkan Pesanan
                </button>
            </div>
        @endif

        {{-- Partner requested cancellation - DIGANTI DENGAN MODAL --}}

        {{-- Floating Help Card (mobile) - fixed above bottom nav --}}
        {{-- <div id="floating-help-card" class="md:hidden fixed left-1/2 transform -translate-x-1/2 w-full max-w-md px-4 z-50" style="bottom: calc(env(safe-area-inset-bottom, 0px) + 76px);">
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-3 flex items-center justify-between gap-3">
                <div class="flex-1 text-sm text-gray-700">Butuh bantuan atau ada keluhan atas Rekan Jasa?</div>
                <a href="{{ route('customer.help-support') }}" class="inline-flex items-center gap-2 px-3 py-2 bg-blue-50 text-blue-600 rounded-lg font-semibold hover:bg-blue-100 transition">
                    <span class="text-sm">Hubungi Kami</span>
                </a>
            </div>
        </div> --}}
    </div>

    {{-- Real-time Tracking Map Modal --}}
    @if($showMapModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" wire:click="closeMapModal" data-tracking-modal>
            <div class="bg-white rounded-2xl w-full max-w-md mx-auto flex flex-col shadow-2xl" style="max-height: 85vh;" wire:click.stop>
                {{-- Header --}}
                <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-gradient-to-r from-blue-500 to-blue-600 rounded-t-2xl shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center">
                            <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-sm">Tracking Real-time</h3>
                            <p class="text-white/80 text-xs" x-text="'Lokasi ' + trackingData.partnerName"></p>
                        </div>
                    </div>
                    <button wire:click="closeMapModal" class="text-white hover:bg-white/20 p-1.5 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- ETA Info Bar --}}
                <div wire:ignore class="px-4 py-2.5 bg-blue-50 border-b border-blue-100 shrink-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-blue-500 flex items-center justify-center animate-pulse">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600">Estimasi Tiba</p>
                                <p class="text-xs font-bold text-blue-700" id="eta-time">Menghitung...</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-600">Jarak</p>
                            <p class="text-xs font-bold text-blue-700" id="distance-text">0.0 km</p>
                        </div>
                    </div>
                </div>

                {{-- Map Container --}}
                <div class="relative shrink-0" style="height: 400px;" wire:ignore>
                    <div id="tracking-map" class="w-full h-full"></div>
                    
                    {{-- Loading Overlay --}}
                    <div id="map-loading" class="absolute inset-0 bg-white/90 flex items-center justify-center">
                        <div class="text-center">
                            <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                            <p class="text-xs text-gray-600">Memuat peta...</p>
                        </div>
                    </div>
                </div>

                {{-- Footer Info --}}
                <div class="px-4 py-2.5 border-t border-gray-200 bg-gray-50 rounded-b-2xl shrink-0">
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span>Lokasi diperbarui setiap 5 detik</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Cancel Confirmation Modal --}}
    @if($showCancelConfirm)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center px-4" wire:click="closeModal" data-confirm-modal>
            <div class="bg-white rounded-2xl max-w-sm w-full p-6" wire:click.stop style="transform: translateY(-120px);">
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Batalkan Pesanan?</h3>
                    <p class="text-sm text-gray-600 mb-6">Apakah Anda yakin ingin membatalkan pesanan ini? Tindakan ini tidak dapat dibatalkan.</p>
                    
                    <div class="flex gap-3">
                        <button wire:click="closeModal" class="flex-1 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition">
                            Tidak
                        </button>
                        <button wire:click="cancelHelp" class="flex-1 py-2.5 bg-red-500 text-white rounded-lg font-semibold hover:bg-red-600 transition">
                            Ya, Batalkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Mitra Request Pembatalan - Bottom Sheet --}}
    @if($help->status === 'partner_cancel_requested')
    <div class="modal-overlay fixed inset-0 z-[9999] flex items-end justify-center animate-fade-in" 
         style="background: rgba(0,0,0,0.7);">
        <div class="bg-white rounded-t-3xl w-full max-w-md shadow-2xl animate-slide-up relative" 
             @click.stop
             style="padding-bottom: env(safe-area-inset-bottom,24px);">
            
            {{-- Header --}}
            <div class="sticky top-0 bg-gradient-to-r from-orange-500 to-red-500 px-5 py-4 rounded-t-3xl">
                <div class="text-center text-white">
                    <div class="flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <h3 class="text-lg font-bold">Permintaan Pembatalan</h3>
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div class="p-5 pb-6">
                {{-- Warning Icon --}}
                <div class="flex items-center justify-center mb-4">
                    <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center relative">
                        <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 rounded-full flex items-center justify-center animate-pulse">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <h4 class="text-center font-bold text-lg text-gray-900 mb-2">Mitra Mengajukan Pembatalan</h4>
                <p class="text-sm text-gray-700 text-center mb-5">
                    Rekan Jasa mengajukan pembatalan untuk pesanan ini. Silakan pilih tindakan yang Anda inginkan.
                </p>

                {{-- Mitra Info --}}
                @if($help->mitra)
                    <div class="bg-gray-50 rounded-xl p-4 mb-5">
                        <div class="flex items-center gap-3 mb-3">
                            @if($help->mitra->selfie_photo)
                                <img src="{{ asset('storage/' . $help->mitra->selfie_photo) }}" alt="{{ $help->mitra->name }}" class="w-12 h-12 rounded-full object-cover border-2 border-blue-100">
                            @else
                                <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-lg">
                                    {{ strtoupper(substr($help->mitra->name ?? 'M', 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex-1">
                                <p class="font-semibold text-sm text-gray-900">{{ $help->mitra->name ?? 'Rekan Jasa' }}</p>
                                <p class="text-xs text-gray-500">Mengajukan pembatalan</p>
                            </div>
                        </div>

                        @if($help->partner_cancel_reason)
                            <div class="border-t border-gray-200 pt-3">
                                <p class="text-xs font-semibold text-gray-500 mb-1">Alasan Pembatalan:</p>
                                <p class="text-sm text-gray-900 italic">"{{ $help->partner_cancel_reason }}"</p>
                            </div>
                        @endif

                        @if($help->partner_cancel_requested_at)
                            <div class="flex items-center gap-2 text-xs text-gray-600 mt-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Diajukan: {{ \Carbon\Carbon::parse($help->partner_cancel_requested_at)->translatedFormat('d F Y, H:i') }} WIB
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Info Box --}}
                <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mb-5">
                    <div class="flex gap-2">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-blue-900 mb-1">Pilihan Anda:</p>
                            <ul class="text-xs text-blue-800 space-y-1">
                                <li><strong>• Terima:</strong> Pesanan dibatalkan, dana dikembalikan penuh</li>
                                <li><strong>• Tolak:</strong> Rekan Jasa harus melanjutkan pekerjaan</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="space-y-2">
                    <button wire:click="acceptPartnerCancellation" 
                            wire:loading.attr="disabled"
                            class="w-full px-5 py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span wire:loading.remove wire:target="acceptPartnerCancellation">Terima Pembatalan</span>
                        <span wire:loading wire:target="acceptPartnerCancellation">Memproses...</span>
                    </button>
                    <button wire:click="rejectPartnerCancellation"
                            wire:loading.attr="disabled"
                            class="w-full px-5 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span wire:loading.remove wire:target="rejectPartnerCancellation">Tolak Pembatalan</span>
                        <span wire:loading wire:target="rejectPartnerCancellation">Memproses...</span>
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
</div>

{{-- Leaflet Maps Script - Load once, pushed to head --}}
@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
    
    <script>
        (function() {
            console.log('🚀 Map script loaded');
            
            let map;
            let partnerMarker;
            let customerMarker;
            let routingControl;
            let routePolyline;
            let initAttempts = 0;
            const maxAttempts = 50;
            let mapInitialized = false; // Flag untuk track map status

            function showError(message) {
                console.error('❌ Error:', message);
                const loading = document.getElementById('map-loading');
                if (loading) {
                    loading.innerHTML = `
                        <div class="text-center p-4">
                            <svg class="w-12 h-12 text-red-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-red-600 mb-2">${message}</p>
                            <button onclick="location.reload()" class="px-4 py-2 bg-blue-500 text-white rounded-lg text-xs hover:bg-blue-600">Muat Ulang Halaman</button>
                        </div>
                    `;
                }
            }

            // Wait for both Leaflet and Alpine to be ready
            function waitAndInit() {
                // Jika map sudah ada, skip init (cegah reinit saat Livewire polling)
                if (map) {
                    console.log('✅ Map already initialized, skipping...');
                    return;
                }
                
                initAttempts++;
                
                const hasLeaflet = typeof L !== 'undefined';
                const hasAlpine = typeof Alpine !== 'undefined';
                const hasContainer = document.getElementById('tracking-map') !== null;
                
                console.log(`⏳ Attempt ${initAttempts}/${maxAttempts}:`, { 
                    Leaflet: hasLeaflet, 
                    Alpine: hasAlpine,
                    Container: hasContainer,
                    mapExists: !!map
                });
                
                if (hasLeaflet && hasAlpine && hasContainer) {
                    console.log('✅ All dependencies ready! Initializing map...');
                    setTimeout(() => {
                        try {
                            initializeMap();
                        } catch (err) {
                            console.error('❌ Init error:', err);
                            showError('Error: ' + err.message);
                        }
                    }, 100);
                } else if (initAttempts >= maxAttempts) {
                    console.error('❌ Timeout waiting for dependencies');
                    showError('Timeout: Gagal memuat library peta');
                } else {
                    setTimeout(waitAndInit, 100);
                }
            }

            function initializeMap() {
                // Cegah double init
                if (map) {
                    console.log('⚠️ Map already exists, skipping initialization');
                    return;
                }
                
                console.log('🗺️ Starting map initialization...');
                
                // Get tracking data from Alpine
                let trackingData;
                try {
                    const alpineEl = document.querySelector('[x-data]');
                    if (!alpineEl) {
                        throw new Error('Alpine element tidak ditemukan');
                    }
                    trackingData = Alpine.$data(alpineEl).trackingData;
                    if (!trackingData) {
                        throw new Error('Tracking data tidak tersedia');
                    }
                } catch (err) {
                    console.error('❌ Error getting Alpine data:', err);
                    showError('Error mengakses data tracking');
                    return;
                }
            
            const partnerLat = parseFloat(trackingData.partnerLat);
            const partnerLng = parseFloat(trackingData.partnerLng);
            const customerLat = parseFloat(trackingData.customerLat);
            const customerLng = parseFloat(trackingData.customerLng);

            console.log('📍 Koordinat:', { 
                partner: { lat: partnerLat, lng: partnerLng },
                customer: { lat: customerLat, lng: customerLng }
            });

            // Validate coordinates
            if (!partnerLat || !partnerLng || !customerLat || !customerLng || 
                isNaN(partnerLat) || isNaN(partnerLng) || isNaN(customerLat) || isNaN(customerLng)) {
                console.error('❌ Koordinat tidak valid');
                showError('Data lokasi tidak valid atau tidak tersedia');
                return;
            }

            // Initialize map centered between partner and customer
            const centerLat = (partnerLat + customerLat) / 2;
            const centerLng = (partnerLng + customerLng) / 2;

            console.log('🎯 Center peta:', { lat: centerLat, lng: centerLng });

            try {
                // Initialize map without the default Leaflet prefix in attribution
                map = L.map('tracking-map', {
                    zoomControl: true,
                    attributionControl: false
                }).setView([centerLat, centerLng], 14);
                console.log('✓ Map object created');
            } catch (err) {
                console.error('❌ Error creating map:', err);
                showError('Gagal membuat peta');
                return;
            }

            // Add OpenStreetMap tiles
            try {
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(map);

                // Add attribution control explicitly without the default Leaflet prefix/link
                try {
                    L.control.attribution({ prefix: false }).addTo(map);
                } catch (err) {
                    console.warn('Unable to set attribution prefix:', err);
                }
                console.log('✓ Tiles loaded');
            } catch (err) {
                console.error('❌ Error loading tiles:', err);
                showError('Gagal memuat tiles peta');
                return;
            }

            // Custom icon for partner (blue pulse)
            const partnerIcon = L.divIcon({
                className: 'custom-div-icon',
                html: `
                    <div style="position: relative;">
                        <div style="position: absolute; width: 40px; height: 40px; background: rgba(37, 99, 235, 0.3); border-radius: 50%; animation: pulse 2s infinite;"></div>
                        <div style="position: absolute; width: 24px; height: 24px; margin: 8px; background: #2563eb; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>
                    </div>
                    <style>
                        @keyframes pulse {
                            0% { transform: scale(1); opacity: 1; }
                            50% { transform: scale(1.3); opacity: 0.5; }
                            100% { transform: scale(1); opacity: 1; }
                        }
                    </style>
                `,
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });

            // Custom icon for customer (red marker)
            const customerIcon = L.divIcon({
                className: 'custom-div-icon',
                html: `
                    <div style="position: relative;">
                        <svg width="32" height="42" viewBox="0 0 32 42" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 0C9.37 0 4 5.37 4 12c0 7.07 12 30 12 30s12-22.93 12-30c0-6.63-5.37-12-12-12z" fill="#dc2626"/>
                            <circle cx="16" cy="12" r="5" fill="white"/>
                        </svg>
                    </div>
                `,
                iconSize: [32, 42],
                iconAnchor: [16, 42]
            });

            // Create markers
            try {
                partnerMarker = L.marker([partnerLat, partnerLng], { 
                    icon: partnerIcon,
                    title: trackingData.partnerName
                }).addTo(map);
                console.log('✓ Partner marker created');

                customerMarker = L.marker([customerLat, customerLng], { 
                    icon: customerIcon,
                    title: 'Lokasi Anda'
                }).addTo(map);
                console.log('✓ Customer marker created');

                // Add popups
                partnerMarker.bindPopup(`
                    <div class="p-2">
                        <strong>${trackingData.partnerName}</strong><br>
                        <small>Sedang menuju ke lokasi Anda</small>
                    </div>
                `);

                customerMarker.bindPopup(`
                    <div class="p-2">
                        <strong>Lokasi Anda</strong><br>
                        <small>${trackingData.location}</small>
                    </div>
                `);
            } catch (err) {
                console.error('❌ Error creating markers:', err);
                showError('Gagal membuat marker');
                return;
            }

            // Calculate and display route
            try {
                calculateRoute(partnerLat, partnerLng, customerLat, customerLng);
                console.log('✓ Route calculation started');
            } catch (err) {
                console.error('⚠️ Warning: Route calculation failed:', err);
                // Continue anyway, map will still work without route
            }

            // Fit bounds to show both markers
            try {
                const bounds = L.latLngBounds([
                    [partnerLat, partnerLng],
                    [customerLat, customerLng]
                ]);
                map.fitBounds(bounds, { padding: [50, 50] });
                console.log('✓ Map bounds set');
            } catch (err) {
                console.error('❌ Error setting bounds:', err);
            }

            // Hide loading overlay dan set flag
            const loadingEl = document.getElementById('map-loading');
            if (loadingEl) {
                loadingEl.style.display = 'none';
                console.log('✓ Loading overlay hidden');
            }
            
            mapInitialized = true;

            // Force map to refresh tiles after short delay
            setTimeout(() => {
                if (map) {
                    map.invalidateSize();
                    console.log('🔄 Map size recalculated');
                }
            }, 100);

            // Update akan otomatis dari Livewire polling + Alpine hook
            console.log('✅ Map initialization complete! Auto-update enabled via Livewire polling (5s).');
        }

            function calculateRoute(fromLat, fromLng, toLat, toLng) {
            // Remove old routing control if exists
            if (routingControl) {
                try { map.removeControl(routingControl); } catch(e){}
                routingControl = null;
            }
            
            // Remove old polyline if exists
            if (routePolyline) {
                try { map.removeLayer(routePolyline); } catch(e){}
                routePolyline = null;
            }

            // If Leaflet Routing Machine is available, use it. Otherwise fallback to straight-line.
            if (window.L && L.Routing && typeof L.Routing.control === 'function') {
                try {
                    routingControl = L.Routing.control({
                        waypoints: [
                            L.latLng(fromLat, fromLng),
                            L.latLng(toLat, toLng)
                        ],
                        routeWhileDragging: false,
                        addWaypoints: false,
                        draggableWaypoints: false,
                        fitSelectedRoutes: false,
                        showAlternatives: false,
                        lineOptions: {
                            styles: [{
                                color: '#2563eb',
                                opacity: 0.8,
                                weight: 5
                            }]
                        },
                        createMarker: function() { return null; }, // Don't create default markers
                        router: L.Routing.osrmv1({
                            serviceUrl: 'https://router.project-osrm.org/route/v1'
                        })
                    }).addTo(map);

                    // Hide the routing instructions panel
                    const routingContainer = document.querySelector('.leaflet-routing-container');
                    if (routingContainer) routingContainer.style.display = 'none';

                    // Listen for route found event
                    routingControl.on('routesfound', function(e) {
                        const routes = e.routes;
                        const route = routes[0];
                        
                        // Get distance and time
                        const distanceKm = (route.summary.totalDistance / 1000).toFixed(1);
                        const timeMinutes = Math.ceil(route.summary.totalTime / 60);
                        
                        // Calculate ETA
                        const hours = Math.floor(timeMinutes / 60);
                        const minutes = timeMinutes % 60;
                        let etaText = '';
                        
                        if (hours > 0) {
                            etaText = `${hours} jam ${minutes} menit`;
                        } else {
                            etaText = `${minutes} menit`;
                        }

                        // Update UI
                        document.getElementById('distance-text').textContent = distanceKm + ' km';
                        document.getElementById('eta-time').textContent = etaText;
                    });

                    // Fallback: routing error
                    routingControl.on('routingerror', function(err) {
                        console.warn('Routing error, falling back to straight-line:', err);
                        fallbackStraightLine();
                    });
                } catch (err) {
                    console.error('Routing control failed, fallback:', err);
                    fallbackStraightLine();
                }
            } else {
                // Routing library not available — fallback
                console.warn('Leaflet Routing Machine not available, using straight-line fallback');
                fallbackStraightLine();
            }

            function fallbackStraightLine() {
                const distance = calculateDistance(fromLat, fromLng, toLat, toLng);
                const distanceKm = distance.toFixed(1);

                // Draw straight line as fallback
                routePolyline = L.polyline([
                    [fromLat, fromLng],
                    [toLat, toLng]
                ], {
                    color: '#2563eb',
                    weight: 5,
                    opacity: 0.8,
                    dashArray: '10, 10'
                }).addTo(map);

                // Estimate time (assuming 40 km/h average speed)
                const estimatedMinutes = Math.ceil((distance / 40) * 60);

                const distanceEl = document.getElementById('distance-text');
                const etaEl = document.getElementById('eta-time');
                if (distanceEl) distanceEl.textContent = distanceKm + ' km';
                    if (etaEl) etaEl.textContent = estimatedMinutes + ' menit (estimasi)';
                }
            }

            function calculateDistance(lat1, lng1, lat2, lng2) {
            // Haversine formula for distance calculation
            const R = 6371; // Earth radius in km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                     Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                     Math.sin(dLng/2) * Math.sin(dLng/2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                return R * c;
            }

            // Fungsi untuk update peta dari Livewire event
            window.updateMapFromTracking = function(data) {
                if (!map || !partnerMarker) {
                    console.log('⚠️ Map or marker not ready yet');
                    return;
                }
                
                const newLat = parseFloat(data.partnerLat);
                const newLng = parseFloat(data.partnerLng);
                const customerLat = parseFloat(data.customerLat);
                const customerLng = parseFloat(data.customerLng);

                console.log('📍 Updating map from Livewire:', { 
                    partner: { lat: newLat, lng: newLng },
                    customer: { lat: customerLat, lng: customerLng }
                });

                if (newLat && newLng && !isNaN(newLat) && !isNaN(newLng) && partnerMarker) {
                    const currentLatLng = partnerMarker.getLatLng();
                    
                    // Hanya update jika posisi berubah
                    if (Math.abs(currentLatLng.lat - newLat) > 0.0001 || Math.abs(currentLatLng.lng - newLng) > 0.0001) {
                        console.log('🚶 Partner bergerak dari', currentLatLng, 'ke', {lat: newLat, lng: newLng});
                        
                        // Animate marker movement
                        animateMarker(partnerMarker, [newLat, newLng]);

                        // Recalculate route setelah marker bergerak
                        setTimeout(() => {
                            if (map && partnerMarker) {
                                calculateRoute(newLat, newLng, customerLat, customerLng);
                            }
                        }, 1000);
                    } else {
                        console.log('📍 Partner masih di posisi yang sama');
                    }
                }
            };
            
            // Backward compatibility
            window.updateMapFromAlpine = function() {
                try {
                    const trackingData = Alpine.$data(document.querySelector('[x-data]')).trackingData;
                    window.updateMapFromTracking(trackingData);
                } catch (err) {
                    console.error('Error in updateMapFromAlpine:', err);
                }
            };

            function updatePartnerLocation() {
                // Livewire polling akan trigger x-init hook yang memanggil updateMapFromAlpine
                // Fungsi ini tetap ada untuk kompatibilitas
                window.updateMapFromAlpine();
            }

            function animateMarker(marker, newLatLng) {
            const startLatLng = marker.getLatLng();
            const endLatLng = L.latLng(newLatLng);
            
            let step = 0;
            const numSteps = 50;
            const deltaLat = (endLatLng.lat - startLatLng.lat) / numSteps;
            const deltaLng = (endLatLng.lng - startLatLng.lng) / numSteps;

            const moveMarker = setInterval(() => {
                step++;
                const lat = startLatLng.lat + (deltaLat * step);
                const lng = startLatLng.lng + (deltaLng * step);
                marker.setLatLng([lat, lng]);

                    if (step >= numSteps) {
                        clearInterval(moveMarker);
                    }
                }, 20);
            }

            // Cleanup ketika modal ditutup
            window.addEventListener('beforeunload', () => {
                if (map) {
                    try { map.remove(); } catch(e) {}
                }
            });

            // Initialize map when modal opens (listen to Livewire)
            document.addEventListener('livewire:init', () => {
                Livewire.on('mapModalOpened', () => {
                    console.log('📢 Map modal opened');
                    initAttempts = 0; // Reset counter
                    setTimeout(waitAndInit, 100);
                });
                
                // Listen untuk tracking data updates dari Livewire
                Livewire.on('tracking-data-updated', (event) => {
                    console.log('📡 Tracking data updated event received:', event);
                    if (map && mapInitialized) {
                        window.updateMapFromTracking(event);
                    }
                });
            });

            // Also check on Livewire update - only init if modal exists and map doesn't
            Livewire.hook('morph.updated', ({ el, component }) => {
                const modalElement = document.querySelector('[wire\\:click="closeMapModal"]');
                
                // Jika modal ada dan map belum di-init
                if (modalElement && !map) {
                    console.log('📢 Modal detected in DOM, initializing...');
                    initAttempts = 0; // Reset counter
                    setTimeout(waitAndInit, 100);
                }
                
                // Jika modal tidak ada tapi map masih ada, cleanup
                if (!modalElement && map) {
                    console.log('🧹 Modal closed, cleaning up map...');
                    try {
                        map.remove();
                        map = null;
                        partnerMarker = null;
                        customerMarker = null;
                        routingControl = null;
                        routePolyline = null;
                        mapInitialized = false;
                    } catch (e) {
                        console.error('Error cleaning up map:', e);
                    }
                }
            });
        })();
    </script>

    <script>
        // Expose current help id so polling can start immediately
        window.currentHelpId = '{{ $help->id }}';

        // Client-side polling runs continuously and updates Alpine + map
        (function() {
            let pollingInterval = null;
            const POLL_MS = 4000; // poll every 4 seconds

            function startPolling(helpId) {
                if (!helpId) return;
                if (pollingInterval) return; // already running
                console.log('🔁 Starting tracking polling for help', helpId);
                // immediate fetch
                fetchAndUpdate(helpId);
                pollingInterval = setInterval(() => fetchAndUpdate(helpId), POLL_MS);
            }

            function stopPolling() {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                    console.log('⏹️ Stopped tracking polling');
                }
            }

            async function fetchAndUpdate(helpId) {
                try {
                    const resp = await fetch(`/customer/helps/${helpId}/tracking`, {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!resp.ok) {
                        console.warn('Tracking endpoint returned', resp.status);
                        return;
                    }
                    const data = await resp.json();

                    // Update Alpine's trackingData so UI and any bindings reflect latest coords
                    try {
                        if (window.Alpine) {
                            const alpineEl = document.querySelector('[x-data]');
                            if (alpineEl) {
                                const alpine = Alpine.$data(alpineEl);
                                if (alpine && alpine.trackingData) {
                                    alpine.trackingData.partnerLat = data.partnerLat ?? alpine.trackingData.partnerLat;
                                    alpine.trackingData.partnerLng = data.partnerLng ?? alpine.trackingData.partnerLng;
                                    alpine.trackingData.customerLat = data.customerLat ?? alpine.trackingData.customerLat;
                                    alpine.trackingData.customerLng = data.customerLng ?? alpine.trackingData.customerLng;
                                    alpine.trackingData.partnerName = data.partnerName ?? alpine.trackingData.partnerName;
                                }
                            }
                        }
                    } catch (err) {
                        console.warn('Failed updating Alpine data', err);
                    }

                    // Call existing global function used by map code to update markers & route
                    if (window.updateMapFromTracking && data) {
                        window.updateMapFromTracking({
                            partnerLat: data.partnerLat,
                            partnerLng: data.partnerLng,
                            customerLat: data.customerLat,
                            customerLng: data.customerLng
                        });
                    }

                    // Calculate simple straight-line distance + ETA fallback and update summary + modal placeholders
                    try {
                        const pLat = parseFloat(data.partnerLat);
                        const pLng = parseFloat(data.partnerLng);
                        const cLat = parseFloat(data.customerLat);
                        const cLng = parseFloat(data.customerLng);

                        if (!isNaN(pLat) && !isNaN(pLng) && !isNaN(cLat) && !isNaN(cLng)) {
                            // Haversine formula (km)
                            function haversine(lat1, lon1, lat2, lon2) {
                                const R = 6371; // km
                                const dLat = (lat2 - lat1) * Math.PI / 180;
                                const dLon = (lon2 - lon1) * Math.PI / 180;
                                const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) * Math.sin(dLon/2) * Math.sin(dLon/2);
                                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                                return R * c;
                            }

                            const distKm = haversine(pLat, pLng, cLat, cLng);
                            const distText = distKm >= 1 ? distKm.toFixed(1) + ' km' : Math.round(distKm * 1000) + ' m';

                            // Estimate time assuming avg speed 30 km/h in city
                            const estMinutes = Math.max(1, Math.ceil((distKm / 30) * 60));
                            const hours = Math.floor(estMinutes / 60);
                            const minutes = estMinutes % 60;
                            const etaText = hours > 0 ? `${hours} jam ${minutes} menit` : `${minutes} menit`;

                            // Update always-visible summary
                            const summaryD = document.getElementById('summary-distance');
                            const summaryE = document.getElementById('summary-eta');
                            if (summaryD) summaryD.textContent = distText;
                            if (summaryE) summaryE.textContent = etaText;

                            // Also update modal placeholders if present
                            const modalD = document.getElementById('distance-text');
                            const modalE = document.getElementById('eta-time');
                            if (modalD) modalD.textContent = distText;
                            if (modalE) modalE.textContent = etaText;
                        }
                    } catch (err) {
                        console.warn('Failed calculating distance/ETA fallback', err);
                    }

                } catch (err) {
                    console.error('Error fetching tracking data:', err);
                }
            }

            // Start polling immediately for the current help id (so modal doesn't need open/close)
            try {
                const initialId = window.currentHelpId || null;
                if (initialId) startPolling(initialId);
            } catch (e) {
                console.error('Error starting initial polling:', e);
            }

            // Hook into Livewire modal events to ensure polling persists or can be stopped if desired
            document.addEventListener('livewire:init', () => {
                Livewire.on('mapModalOpened', (helpId) => {
                    const idToUse = helpId || window.currentHelpId;
                    startPolling(idToUse);
                });

                // stop polling when modal closed (optional) - we will keep polling in background, so don't stop here
                Livewire.on('mapModalClosed', () => {
                    // intentionally left blank to allow continuous background polling
                });
            });

            // Stop polling on page unload
            window.addEventListener('beforeunload', stopPolling);
        })();
    </script>

    {{-- Toast notification for copy --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('copied', (event) => {
                // Show toast notification
                const toast = document.createElement('div');
                toast.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white px-4 py-2 rounded-lg shadow-lg z-50 text-sm';
                toast.textContent = 'ID Pesanan disalin: ' + event.orderId;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.remove();
                }, 2000);
            });
        });
    </script>
@endpush

{{-- Flash Messages --}}
@if(session()->has('success'))
    <div class="fixed top-20 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in-down">
        {{ session('success') }}
    </div>
@endif

@if(session()->has('error'))
    <div class="fixed top-20 left-1/2 transform -translate-x-1/2 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in-down">
        {{ session('error') }}
    </div>
@endif