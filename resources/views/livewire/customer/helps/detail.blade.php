<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100" 
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

    {{-- Header Section --}}
    <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
        <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

        <div class="relative z-10 max-w-md mx-auto">
            <div class="flex items-center justify-between text-white">
                <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="text-center flex-1 min-w-0 px-2">
                    <h1 class="text-base font-bold truncate">Detail Pesanan</h1>
                    <p class="text-xs text-white/90 truncate mt-0.5">Detail permintaan bantuan Anda</p>
                </div>

                <div class="w-9 flex items-center justify-end flex-shrink-0">
                    <button wire:click="loadHelp" wire:loading.attr="disabled" title="Segarkan Status" class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex items-center justify-center">
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
    </div>

    <!-- Content -->
    <div class="px-5 pt-5 pb-8 max-w-md mx-auto">
        {{-- Order ID --}}
        <div class="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60">
            <span class="text-sm text-gray-600 dark:text-gray-400">ID Pesanan: <span class="font-semibold text-gray-900 dark:text-white">{{ $help->order_id }}</span></span>
            <button wire:click="copyOrderId" class="text-blue-500 hover:text-blue-600 dark:text-blue-400 text-sm font-semibold flex items-center gap-1">
                Salin
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </button>
        </div>

        {{-- Service Info --}}
        <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60">
            <div class="flex items-start gap-3">
                <div class="w-12 h-12 rounded-lg bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center flex-shrink-0">
                    @if($help->photo)
                        <img src="{{ asset('storage/' . $help->photo) }}" alt="{{ $help->title }}" class="w-full h-full object-cover rounded-lg">
                    @else
                        <svg class="w-6 h-6 text-pink-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                        </svg>
                    @endif
                </div>
                <div class="flex-1">
                    <h2 class="font-semibold text-base text-gray-900 dark:text-white">{{ $help->title }}</h2>
                </div>
            </div>

            {{-- Partner Info --}}
            @if($help->mitra)
                <div class="mt-4 p-3 bg-white dark:bg-gray-700/50 rounded-xl flex items-center justify-between shadow-sm border border-gray-100 dark:border-gray-600">
                    <div class="flex items-center gap-3">
                        @if($help->mitra->selfie_photo)
                            <img src="{{ asset('storage/' . $help->mitra->selfie_photo) }}" alt="{{ $help->mitra->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-blue-100 dark:border-blue-900">
                        @else
                            <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                                {{ strtoupper(substr($help->mitra->name ?? 'M', 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="font-semibold text-sm text-gray-900 dark:text-white">{{ $help->mitra->name ?? 'Mitra' }}</h3>
                            <div class="flex items-center gap-1 mt-0.5">
                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                @php
                                    $mitra = $help->mitra;
                                    $avgRating = $mitra ? ($mitra->mitra_average_rating ?? ($mitra->rating ?? 0)) : 0;
                                    $ratingCount = $mitra ? ($mitra->mitra_rating_count ?? null) : null;
                                @endphp
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($avgRating, 2) }}</span>
                                @if($ratingCount)
                                    <span class="text-xs text-gray-400 dark:text-gray-400 ml-2">({{ $ratingCount }})</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('customer.chat', $help->id) }}" class="w-10 h-10 rounded-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 flex items-center justify-center hover:bg-gray-50 dark:hover:bg-gray-700 transition text-gray-700 dark:text-gray-200">
                            <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @endif

            {{-- Description & Additional Details --}}
            <div class="bg-white dark:bg-gray-800 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/60">
                <h3 class="font-bold text-sm text-gray-900 dark:text-white mb-3">Deskripsi & Detail</h3>

                @if(!empty($help->description))
                    <div class="mb-3">
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line break-words break-all">{{ $help->description }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-3 text-sm text-gray-700 dark:text-gray-300">
                    @if(!empty($help->equipment_provided))
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Perlengkapan</div>
                            <div class="font-semibold break-words break-all text-gray-900 dark:text-white">{{ $help->equipment_provided }}</div>
                        </div>
                    @endif

                    @if($help->mitra && !empty($help->mitra->phone))
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Kontak Mitra</div>
                            <div class="font-semibold"><a href="tel:{{ $help->mitra->phone }}" class="text-blue-600 dark:text-blue-400">{{ $help->mitra->phone }}</a></div>
                        </div>
                    @endif

                    @if(!empty($help->city->name) || !empty($help->province->name))
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Kota / Provinsi</div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $help->city->name ?? '-' }}{{ $help->province ? (', ' . $help->province->name) : '' }}</div>
                        </div>
                    @endif
                </div>

                @if(!empty($help->photo))
                    <div class="mt-3">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Foto Pesanan</div>
                        <img src="{{ asset('storage/' . $help->photo) }}" alt="Foto bantuan" class="w-full mt-2 rounded-lg object-cover">
                    </div>
                @endif

                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    <div>Dibuat: {{ \Carbon\Carbon::parse($help->created_at)->translatedFormat('d F Y, H:i') }}</div>
                    <div>Terakhir diperbarui: {{ \Carbon\Carbon::parse($help->updated_at)->translatedFormat('d F Y, H:i') }}</div>
                    @if(!empty($help->scheduled_at))
                        <div>Jadwal: {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d F Y, H:i') }}</div>
                    @endif
                </div>
            </div>

        {{-- Progress Stepper Card --}}
        <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100 dark:border-gray-700/50">
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Progres Pesanan</span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5 mt-0.5">
                        <span>{{ $help->progress_icon }}</span>
                        <span>{{ $help->progress_summary }}</span>
                    </h3>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-extrabold bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800">
                        {{ $help->progress_percentage }}%
                    </span>
                </div>
            </div>

            <!-- Stepper 5-Steps Horizontal -->
            <div class="relative pt-2 pb-1">
                <!-- Connecting Line -->
                <div class="absolute top-6 left-6 right-6 h-1 bg-gray-100 dark:bg-gray-700 -z-0">
                    <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600 transition-all duration-700 rounded-full"
                         style="width: {{ max(0, min(100, ($help->progress_step - 1) * 25)) }}%;"></div>
                </div>

                <!-- Step Nodes -->
                <div class="flex items-start justify-between relative z-10">
                    @php
                        $steps = [
                            ['step' => 1, 'icon' => '🔍', 'title' => 'Mencari'],
                            ['step' => 2, 'icon' => '🤝', 'title' => 'Diambil'],
                            ['step' => 3, 'icon' => '🛵', 'title' => 'Menuju Lokasi'],
                            ['step' => 4, 'icon' => '⚡', 'title' => 'Pengerjaan'],
                            ['step' => 5, 'icon' => '✅', 'title' => 'Selesai'],
                        ];
                        $currentStep = $help->progress_step;
                        $isDone = in_array($help->status, ['selesai', 'completed']);
                    @endphp

                    @foreach($steps as $s)
                        @php
                            $isPassed = $s['step'] < $currentStep || ($s['step'] === 5 && $isDone);
                            $isCurrent = $s['step'] === $currentStep && !$isDone;
                        @endphp
                        <div class="flex flex-col items-center text-center" style="width: 18%;">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 shadow-xs
                                {{ $isPassed ? 'bg-blue-600 text-white shadow-blue-500/30' : ($isCurrent ? 'bg-indigo-600 text-white ring-4 ring-indigo-100 dark:ring-indigo-900/50 animate-pulse' : 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500') }}">
                                @if($isPassed)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    <span>{{ $s['icon'] }}</span>
                                @endif
                            </div>
                            <span class="text-[10px] font-semibold mt-1.5 leading-tight {{ $isCurrent ? 'text-indigo-600 dark:text-indigo-400 font-bold' : ($isPassed ? 'text-gray-800 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500') }}">
                                {{ $s['title'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            @if(in_array($help->status, ['taken', 'partner_on_the_way', 'partner_arrived']))
                <button wire:click="showTrackingMap" class="w-full mt-3 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-xs transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Lihat Lokasi Rekan Jasa di Peta Real-time
                    <svg class="w-3.5 h-3.5 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            @endif
        </div>

        {{-- Location --}}
        <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white mb-1">Lokasi</h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $help->location ?? $help->full_address ?? 'Rumah warna coklat' }}</p>
                    @if($help->full_address)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><span class="font-semibold">Detail :</span> {{ $help->full_address }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Schedule --}}
        <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white mb-1">Jadwal Pesanan</h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        {{ \Carbon\Carbon::parse($help->scheduled_at ?? $help->created_at)->translatedFormat('l, d F Y') }} 
                        (Jam {{ \Carbon\Carbon::parse($help->scheduled_at ?? $help->created_at)->format('H:i') }} WIB)
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">*Jadwal tertera dalam WIB</p>
                </div>
            </div>
        </div>

        {{-- Card Penjelasan Selesai Otomatis saat Sedang Berjalan --}}
        @if(in_array($help->status, ['taken', 'memperoleh_mitra', 'partner_on_the_way', 'partner_arrived', 'in_progress', 'sedang_diproses']))
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-950/40 dark:to-indigo-950/40 mt-2 px-4 py-3.5 rounded-xl border border-blue-200/80 dark:border-blue-800/60 flex items-start gap-3 shadow-xs">
                <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1 text-xs text-blue-950 dark:text-blue-200 leading-relaxed">
                    <span class="font-bold block text-blue-950 dark:text-blue-100 text-xs mb-0.5">Selesai Otomatis oleh Rekan Jasa</span>
                    Pesanan ini akan otomatis selesai begitu Rekan Jasa menyelesaikan tugas dan mengunggah foto bukti pengerjaan. Tidak perlu konfirmasi manual dari 2 pihak, sehingga Rekan Jasa dapat segera melanjutkan pekerjaan berikutnya dan Anda dapat langsung memberikan rating & ulasan.
                </div>
            </div>
        @endif

        {{-- Status Timeline - Redesigned visual to match reference --}}
        <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60">
            <h3 class="font-bold text-sm text-gray-900 dark:text-white mb-4">Status Pesanan</h3>

            @php
                $statuses = [
                    [ 'key' => 'payment', 'title' => 'Pembayaran', 'time' => $help->created_at, 'active' => in_array($help->status, ['menunggu_pembayaran','mencari_mitra','menunggu_mitra','memperoleh_mitra','taken','partner_on_the_way','partner_arrived','in_progress','sedang_diproses','waiting_customer_confirmation','selesai','completed']), 'current' => $help->status === 'menunggu_pembayaran' ],
                    [ 'key' => 'searching', 'title' => 'Mencari Rekan Jasa', 'time' => $help->mitra_assigned_at ?? $help->taken_at, 'active' => in_array($help->status, ['mencari_mitra','menunggu_mitra','memperoleh_mitra','taken','partner_on_the_way','partner_arrived','in_progress','sedang_diproses','waiting_customer_confirmation','selesai','completed']), 'current' => in_array($help->status, ['mencari_mitra','menunggu_mitra','memperoleh_mitra']) ],
                    [ 'key' => 'accepted', 'title' => 'Menunggu Rekan Jasa berangkat', 'time' => $help->taken_at, 'active' => in_array($help->status, ['taken','partner_on_the_way','partner_arrived','in_progress','sedang_diproses','waiting_customer_confirmation','selesai','completed']), 'current' => $help->status === 'taken' ],
                    [ 'key' => 'on_the_way', 'title' => 'Rekan Jasa menuju ke lokasi', 'time' => $help->partner_started_moving_at, 'active' => in_array($help->status, ['partner_on_the_way','partner_arrived','in_progress','sedang_diproses','waiting_customer_confirmation','selesai','completed']), 'current' => $help->status === 'partner_on_the_way' ],
                    [ 'key' => 'arrived', 'title' => 'Rekan Jasa tiba di lokasi', 'time' => $help->partner_arrived_at, 'active' => in_array($help->status, ['partner_arrived','in_progress','sedang_diproses','waiting_customer_confirmation','selesai','completed']), 'current' => $help->status === 'partner_arrived' ],
                    [ 'key' => 'in_progress', 'title' => 'Pelayanan dalam proses', 'time' => $help->service_started_at, 'active' => in_array($help->status, ['in_progress','sedang_diproses','waiting_customer_confirmation','selesai','completed']), 'current' => in_array($help->status, ['in_progress','sedang_diproses']) ],
                    [ 'key' => 'completed', 'title' => 'Pesanan selesai', 'time' => $help->completed_at ?? $help->service_completed_at, 'active' => in_array($help->status, ['selesai','completed']), 'current' => in_array($help->status, ['selesai','completed']) ]
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
                                            <div class="w-5 h-5 rounded-full border-2 border-blue-500 bg-white dark:bg-gray-800 flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-blue-500 rounded-full animate-pulse"></div>
                                            </div>
                                        @else
                                            <div class="w-4 h-4 rounded-full bg-blue-500"></div>
                                        @endif
                                    @else
                                        <div class="w-4 h-4 rounded-full border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800"></div>
                                    @endif
                                </div>

                                {{-- connector below dot (except last) --}}
                                @if(!$loop->last)
                                    <div class="flex-1 w-px mt-2 {{ $status['active'] ? 'bg-blue-200 dark:bg-blue-800' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                                @endif
                            </div>

                            {{-- content --}}
                            <div class="flex-1 pl-2">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-semibold {{ $status['active'] ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }}">{{ $status['title'] }}</h4>
                                    <div class="text-xs {{ $status['active'] ? 'text-gray-600 dark:text-gray-400' : 'text-gray-400 dark:text-gray-500' }} whitespace-nowrap">
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
                                            <p class="text-xs text-blue-600 dark:text-blue-400 flex items-center gap-1">Jarak: {{ $distance > 1000 ? number_format($distance/1000, 1) . ' km' : $distance . ' m' }}</p>
                                        @elseif($status['key'] === 'accepted')
                                            {{-- <p class="text-xs text-blue-600">GPS tracking aktif</p> --}}
                                        @elseif($status['key'] === 'arrived')
                                            <p class="text-xs text-green-600 dark:text-green-400">Rekan jasa sudah sampai</p>
                                        @elseif($status['key'] === 'in_progress')
                                            <p class="text-xs text-blue-600 dark:text-blue-400">Pekerjaan sedang berlangsung (otomatis selesai saat mitra kirim bukti)</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Bukti Foto Hasil Pengerjaan jika sudah Selesai --}}
        @if($help->proof_photo && in_array($help->status, ['selesai', 'completed']))
            <div class="bg-white dark:bg-gray-800 mt-2 px-5 py-4 border border-emerald-200/80 dark:border-emerald-500/30 rounded-2xl shadow-xs">
                <div class="flex items-center justify-between mb-2.5">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-xs font-bold text-gray-900 dark:text-white">Bukti Hasil Pengerjaan Rekan Jasa</span>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800/60">
                        Otomatis Selesai
                    </span>
                </div>
                <a href="{{ asset('storage/' . $help->proof_photo) }}" target="_blank" rel="noopener">
                    <img src="{{ asset('storage/' . $help->proof_photo) }}" alt="Bukti Pengerjaan" class="w-full max-h-56 object-cover rounded-xl border border-gray-100 dark:border-gray-700 hover:opacity-95 transition cursor-pointer shadow-xs">
                </a>
                @if($help->completion_notes)
                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-2 italic bg-gray-50 dark:bg-gray-700/50 p-2.5 rounded-lg border border-gray-100 dark:border-gray-700">"{{ $help->completion_notes }}"</p>
                @endif
                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-2 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Pesanan telah selesai dan dana saldo telah diteruskan ke Rekan Jasa.
                </p>
            </div>
        @endif

        {{-- Garansi Perlindungan 1x24 Jam & Form Aduan/Refund --}}
        @if(in_array($help->status, ['selesai', 'completed']))
            @php
                $isWithin24H = $help->completed_at && $help->completed_at->addHours(24)->isFuture();
                $existingReport = \App\Models\PartnerReport::where('reporter_id', auth()->id())
                    ->where('reported_help_id', $help->id)
                    ->latest()
                    ->first();
            @endphp

            @if($existingReport)
                <div class="bg-gradient-to-br from-purple-50 to-indigo-50/50 dark:from-purple-950/50 dark:to-indigo-950/30 mt-2.5 p-4 rounded-2xl border border-purple-200 dark:border-purple-800/70 shadow-xs space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center flex-shrink-0 mt-0.5 border border-purple-500/20">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 flex-wrap mb-1">
                                <h4 class="text-xs sm:text-sm font-bold text-purple-950 dark:text-purple-100">Laporan Aduan (#{{ $existingReport->id }})</h4>
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $existingReport->status === 'resolved' ? 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300' }}">
                                    {{ $existingReport->refund_status === 'approved' ? 'Refund Disetujui' : ucfirst($existingReport->status) }}
                                </span>
                            </div>
                            <p class="text-xs text-purple-900/85 dark:text-purple-300/90 leading-relaxed break-words">
                                {{ $existingReport->message }}
                            </p>
                        </div>
                    </div>

                    {{-- Tombol Buka Ruang Chat Dukungan Khusus --}}
                    <div class="pt-2.5 border-t border-purple-200/60 dark:border-purple-800/60 flex flex-col sm:flex-row items-center justify-between gap-2.5">
                        <div class="text-[11px] text-purple-800 dark:text-purple-300 w-full sm:w-auto text-center sm:text-left">
                            @php $msgCount = $existingReport->messages()->count(); @endphp
                            <span>{{ $msgCount > 0 ? $msgCount . ' pesan klarifikasi tersedia' : 'Ruang obrolan dengan tim Admin aktif' }}</span>
                        </div>
                        <a href="{{ route('customer.chat', ['admin' => 1, 'report' => $existingReport->id]) }}"
                            class="w-full sm:w-auto px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <span>Buka Ruang Chat Admin</span>
                        </a>
                    </div>
                </div>
            @elseif($isWithin24H)
                <div class="bg-gradient-to-br from-amber-50 to-amber-100/50 dark:from-amber-950/50 dark:to-yellow-950/30 mt-2.5 p-4 rounded-2xl border border-amber-200 dark:border-amber-800/70 shadow-xs space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0 mt-0.5 border border-amber-500/20">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 flex-wrap mb-1">
                                <h4 class="text-xs sm:text-sm font-bold text-amber-950 dark:text-amber-100">
                                    Garansi Perlindungan Layanan 1x24 Jam
                                </h4>
                                <span class="text-[10px] bg-amber-200/90 dark:bg-amber-900/80 text-amber-900 dark:text-amber-200 px-2 py-0.5 rounded-full font-bold shadow-2xs">
                                    Aktif
                                </span>
                            </div>
                            <p class="text-xs text-amber-900/85 dark:text-amber-300/90 leading-relaxed">
                                Jika mitra berbohong, tidak menyelesaikan tugas, atau melanggar aturan, Anda dapat mengajukan laporan refund sebelum: <strong class="font-bold text-amber-950 dark:text-amber-200">{{ \Carbon\Carbon::parse($help->completed_at)->addHours(24)->translatedFormat('d M Y, H:i') }} WIB</strong>.
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('customer.reports.create', ['help_id' => $help->id, 'user_id' => $help->mitra_id, 'type' => 'klaim_refund_pekerjaan_fiktif']) }}" 
                       class="w-full py-2.5 px-4 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>Laporkan / Ajukan Refund</span>
                    </a>
                </div>
            @endif
        @endif

        {{-- Status Pembekuan Sengketa (Disputed Freeze) --}}
        @if($help->isDisputed())
            <div class="bg-gradient-to-br from-rose-50 to-red-100/60 dark:from-rose-950/60 dark:to-red-950/40 mt-2 px-5 py-5 border border-rose-300 dark:border-rose-800 rounded-2xl shadow-xs space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-11 h-11 rounded-xl bg-rose-600 text-white flex items-center justify-center flex-shrink-0 shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between gap-2 flex-wrap mb-1">
                            <h3 class="font-bold text-sm text-rose-950 dark:text-rose-100">Pesanan Dalam Proses Sengketa / Mediasi</h3>
                            <span class="text-[10px] font-extrabold bg-rose-200 text-rose-800 dark:bg-rose-900/80 dark:text-rose-200 px-2.5 py-0.5 rounded-full">
                                Escrow Dibekukan
                            </span>
                        </div>
                        <p class="text-xs text-rose-900/85 dark:text-rose-300 leading-relaxed">
                            Dana pembayaran sebesar <strong>Rp {{ number_format($help->total_amount ?: $help->amount, 0, ',', '.') }}</strong> saat ini dibekukan oleh sistem dan sedang dalam pemeriksaan Admin Wilayah.
                        </p>
                    </div>
                </div>

                <div class="p-3 bg-white/80 dark:bg-gray-800/80 rounded-xl border border-rose-200 dark:border-rose-900/60 text-xs text-gray-700 dark:text-gray-300">
                    <span class="font-bold block text-rose-950 dark:text-rose-200 mb-1">Alasan Komplain Anda:</span>
                    <p class="italic">"{{ $help->dispute_reason }}"</p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1.5">Diajukan pada: {{ $help->disputed_at ? $help->disputed_at->translatedFormat('d M Y, H:i') : '-' }} WIB</p>
                </div>
            </div>
        @elseif(in_array($help->status, ['waiting_customer_confirmation', 'waiting_confirmation', 'konfirmasi_selesai']))
            @php
                $remainingMin = $help->confirmation_remaining_minutes ?? 0;
                $remHours = floor($remainingMin / 60);
                $remMins = $remainingMin % 60;
            @endphp
            <div class="bg-white dark:bg-gray-800 mt-2 px-5 py-5 border border-blue-200/80 dark:border-blue-500/30 rounded-2xl shadow-xs">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl bg-blue-600 flex items-center justify-center flex-shrink-0 text-white shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between gap-2 flex-wrap mb-1">
                            <h3 class="font-bold text-sm text-gray-900 dark:text-white">Menunggu Konfirmasi Anda</h3>
                            <span class="text-[11px] font-bold bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-200 px-2.5 py-0.5 rounded-full flex items-center gap-1 animate-pulse">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/></svg>
                                Sisa: {{ $remHours }}j {{ $remMins }}m
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                            Mitra telah menyelesaikan pekerjaan. Mohon periksa hasil pengerjaan di bawah. Jika tidak ada konfirmasi dalam 24 jam, sistem akan menyelesaikan pesanan secara otomatis.
                        </p>
                    </div>
                </div>

                @if($help->proof_photo)
                    <div class="mb-4 p-3 bg-slate-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-700">
                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block mb-1.5 flex items-center gap-1">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Foto Bukti Hasil Pengerjaan:
                        </span>
                        <a href="{{ asset('storage/' . $help->proof_photo) }}" target="_blank" rel="noopener">
                            <img src="{{ asset('storage/' . $help->proof_photo) }}" alt="Bukti Pengerjaan" class="w-full max-h-52 object-cover rounded-lg border border-gray-100 dark:border-gray-700 hover:opacity-95 transition cursor-pointer shadow-xs">
                        </a>
                        @if($help->completion_notes)
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-2 italic bg-white dark:bg-gray-800 p-2 rounded-md border border-gray-100 dark:border-gray-700">"{{ $help->completion_notes }}"</p>
                        @endif
                    </div>
                @endif

                <div class="space-y-2">
                    <button wire:click="confirmCompletion" 
                            wire:loading.attr="disabled"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-bold py-3 px-4 rounded-xl transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Konfirmasi Selesai & Teruskan Dana
                    </button>

                    <button wire:click="openDisputeModal" 
                            type="button"
                            class="w-full bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 font-bold py-2.5 px-4 rounded-xl transition flex items-center justify-center gap-2 cursor-pointer text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Ajukan Komplain / Sengketa
                    </button>
                </div>
                <p class="text-[11px] text-center text-gray-500 dark:text-gray-400 mt-2">Batas waktu auto-konfirmasi: {{ $help->confirmation_deadline_at ? $help->confirmation_deadline_at->translatedFormat('d M Y, H:i') : '-' }} WIB</p>
            </div>
        @endif

        {{-- Rating Form --}}
        @if(in_array($help->status, ['selesai', 'completed']))
            @php
                $customerRating = $help->ratings->first(function ($r) {
                    return $r->rater_id == auth()->id() || $r->user_id == auth()->id();
                });
            @endphp

            @if($customerRating)
                {{-- Already Rated - Show Rating --}}
                <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 border border-emerald-200/80 dark:border-emerald-500/30 rounded-2xl shadow-xs">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-sm text-gray-900 dark:text-white mb-2">Rating Anda</h3>
                            <div class="flex items-center gap-1 mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= $customerRating->rating ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                                <span class="ml-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $customerRating->rating }}/5</span>
                            </div>
                            @if($customerRating->review)
                                <p class="text-sm text-gray-700 dark:text-gray-300 italic">"{{ $customerRating->review }}"</p>
                            @endif
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ $customerRating->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            @else
                {{-- Rating Form --}}
                <div class="bg-white dark:bg-gray-800 mt-2 px-5 py-5 border border-amber-200/80 dark:border-amber-500/30 rounded-2xl shadow-xs">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="w-12 h-12 rounded-full bg-amber-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-sm text-gray-900 dark:text-white mb-1">Bagaimana Pengalaman Anda?</h3>
                            <p class="text-xs text-gray-600 dark:text-gray-300">Berikan rating untuk {{ $help->mitra->name ?? 'mitra' }}</p>
                        </div>
                    </div>

                    {{-- Star Rating --}}
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Rating *</label>
                        <div class="flex items-center gap-2">
                            @for($i = 1; $i <= 5; $i++)
                                <button 
                                    type="button"
                                    wire:click="setRating({{ $i }})"
                                    class="focus:outline-none transition-transform hover:scale-110">
                                    <svg class="w-9 h-9 {{ $rating >= $i ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                            @endfor
                            @if($rating > 0)
                                <span class="ml-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $rating }}/5</span>
                            @endif
                        </div>
                        @error('rating')
                            <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Review Text --}}
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Ulasan (Opsional)</label>
                        <textarea 
                            wire:model="review"
                            rows="3"
                            placeholder="Ceritakan pengalaman Anda..."
                            class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent text-sm text-gray-900 dark:text-white placeholder-gray-400"
                            maxlength="500"></textarea>
                        <div class="flex justify-between items-center mt-1">
                            @error('review')
                                <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @else
                                <p class="text-xs text-gray-400">Maksimal 500 karakter</p>
                            @enderror
                            <p class="text-xs text-gray-400">{{ strlen($review ?? '') }}/500</p>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button 
                        wire:click="submitRating"
                        wire:loading.attr="disabled"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-4 rounded-xl transition shadow-sm flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove>Kirim Rating</span>
                        <span wire:loading>Mengirim...</span>
                    </button>
                </div>
            @endif
        @endif

        {{-- Payment Details --}}
        <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-sm text-gray-900 dark:text-white">Rincian Pembayaran</h3>
                @if($help->isV2Model() && !in_array($help->status, ['selesai', 'completed', 'dibatalkan', 'cancelled']))
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60">
                        <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Dana Ditahan (Escrow)
                    </span>
                @endif
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-700 dark:text-gray-300">Imbalan Rekan Jasa</span>
                    <span class="font-semibold text-gray-900 dark:text-white">Rp{{ number_format($help->amount, 0, ',', '.') }}</span>
                </div>

                {{-- Biaya Layanan / Pajak Platform --}}
                @if(($help->platform_fee_amount ?? $help->admin_fee ?? 0) > 0)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-700 dark:text-gray-300">Biaya Layanan Platform</span>
                        <span class="font-semibold text-blue-600 dark:text-blue-400">+Rp{{ number_format($help->platform_fee_amount ?? $help->admin_fee ?? 0, 0, ',', '.') }}</span>
                    </div>
                @endif

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

                <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex items-center justify-between">
                    <span class="font-bold text-gray-900 dark:text-white">Total Pembayaran</span>
                    <span class="font-bold text-primary-600 dark:text-primary-400">Rp{{ number_format($help->total_amount > 0 ? $help->total_amount : ($help->amount + ($help->platform_fee_amount ?? $help->admin_fee ?? 0) - ($help->discount_amount ?? 0)), 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-800 rounded-lg">
                <p class="text-xs text-gray-700 dark:text-blue-200 leading-relaxed">
                    @if($help->isV2Model())
                        🛡️ <strong>Proteksi Escrow:</strong> Pembayaran Anda ditahan aman oleh sistem SayaBantu selama pengerjaan. Dana baru akan diteruskan ke Rekan Jasa setelah Anda mengonfirmasi pekerjaan selesai dengan baik.
                    @else
                        Kamu dapat meminta tindakan tambahan selama sesi layanan berlangsung. Pastikan semua pembayaran dilakukan melalui aplikasi agar pesananmu tercatat dan terlindungi.
                    @endif
                </p>
            </div>
        </div>

        {{-- Cancel Button --}}
        @if(in_array($help->status, ['menunggu_pembayaran', 'mencari_mitra', 'menunggu_mitra']))
            <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60">
                <button wire:click="confirmCancel" class="w-full py-3 border-2 border-red-500 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg font-semibold text-sm transition">
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
            <div class="bg-white rounded-2xl w-full max-w-md mx-auto flex flex-col shadow-2xl" style="max-height: 85vh;" @click.stop>
                {{-- Header --}}
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-2xl shrink-0">
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
                <div wire:ignore class="px-4 py-2.5 bg-blue-50 dark:bg-blue-950/40 border-b border-blue-100 dark:border-blue-800 shrink-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-blue-500 flex items-center justify-center animate-pulse">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 dark:text-gray-400">Estimasi Tiba</p>
                                <p class="text-xs font-bold text-blue-700 dark:text-blue-400" id="eta-time">Menghitung...</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-600 dark:text-gray-400">Jarak</p>
                            <p class="text-xs font-bold text-blue-700 dark:text-blue-400" id="distance-text">0.0 km</p>
                        </div>
                    </div>
                </div>

                {{-- Map Container --}}
                <div class="relative shrink-0" style="height: 400px;" wire:ignore>
                    <div id="tracking-map" class="w-full h-full"></div>
                    
                    {{-- Loading Overlay --}}
                    <div id="map-loading" class="absolute inset-0 bg-white/90 dark:bg-gray-800/90 flex items-center justify-center">
                        <div class="text-center">
                            <div class="w-10 h-10 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                            <p class="text-xs text-gray-600 dark:text-gray-300">Memuat peta...</p>
                        </div>
                    </div>
                </div>

                {{-- Footer Info --}}
                <div class="px-4 py-2.5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-b-2xl shrink-0">
                    <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
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
        <div class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center px-4" wire:click="closeModal" data-confirm-modal>
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700" @click.stop style="transform: translateY(-120px);">
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 dark:bg-red-900/40 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Batalkan Pesanan?</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">Apakah Anda yakin ingin membatalkan pesanan ini? Tindakan ini tidak dapat dibatalkan.</p>
                    
                    <div class="flex gap-3">
                        <button wire:click="closeModal" class="flex-1 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-lg font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
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

    {{-- Modal Ajukan Komplain / Sengketa --}}
    @if($showDisputeModal)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-fade-in"
             wire:click.self="closeDisputeModal">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-gray-900 dark:text-white">Ajukan Komplain / Sengketa</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pembekuan dana escrow & mediasi admin</p>
                        </div>
                    </div>
                    <button wire:click="closeDisputeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mb-4 p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                    Pengajuan komplain akan <strong>membekukan dana pembayaran (escrow freeze)</strong> secara seketika dan meneruskan bukti pengerjaan ke Admin Wilayah untuk mediasi.
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Jelaskan Masalah / Ketidaksesuaian *</label>
                    <textarea wire:model="disputeReason"
                              rows="4"
                              placeholder="Jelaskan secara detail alasan komplain (contoh: pekerjaan belum tuntas, hasil tidak sesuai kesepakatan, mitra tidak hadir, dsb)..."
                              class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-rose-500"></textarea>
                    @error('disputeReason')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2">
                    <button wire:click="closeDisputeModal"
                            type="button"
                            class="flex-1 py-2.5 px-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-xl transition">
                        Batal
                    </button>
                    <button wire:click="submitDispute"
                            wire:loading.attr="disabled"
                            type="button"
                            class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center justify-center gap-1.5">
                        <span wire:loading.remove wire:target="submitDispute">Kirim Komplain</span>
                        <span wire:loading wire:target="submitDispute">Mengirim...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

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