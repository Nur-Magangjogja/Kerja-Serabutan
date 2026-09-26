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
            partnerPlate: '{{ $help->isPickup() ? ($help->mitra?->vehicle_plate_number ?? "") : "" }}',
            partnerVehicle: '{{ $help->isPickup() ? ($help->mitra?->vehicle_display_name ?? "") : "" }}',
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
        if (window.playNotificationSound) {
            window.playNotificationSound();
        }
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
    <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-[#0098e7] rounded-b-2xl shadow-sm text-white">
        <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

        <div class="relative z-10 max-w-md mx-auto">
            <div class="flex items-center justify-between min-h-[40px] text-white">
                <div class="w-10 flex items-center">
                    <a href="{{ route('customer.helps.index') }}" wire:navigate class="p-2 hover:bg-white/20 rounded-xl transition-colors duration-200 cursor-pointer flex items-center justify-center text-white" title="Kembali" aria-label="Kembali">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                </div>

                <div class="text-center flex-1 min-w-0 px-2">
                    <h1 class="text-base font-bold truncate">Detail Pesanan</h1>
                    <p class="text-xs text-white font-medium truncate mt-0.5">Detail permintaan bantuan Anda</p>
                </div>

                <div class="w-10 flex items-center justify-end">
                    <button wire:click="loadHelp" wire:loading.attr="disabled" title="Segarkan Status" aria-label="Segarkan Status" class="p-2 hover:bg-white/20 rounded-xl transition-colors duration-200 cursor-pointer flex items-center justify-center text-white">
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
        {{-- Hero Card Batas Waktu Pencarian Rekan Jasa (Khusus Status Menunggu Mitra) --}}
        @if($help->status === 'menunggu_mitra')
            @php
                $effectiveExpiry = $help->effective_expires_at;
                $expiryIso = $effectiveExpiry ? $effectiveExpiry->toIso8601String() : null;
            @endphp
            <div x-data="customerDetailCountdownTimer('{{ $expiryIso }}')" class="bg-gradient-to-br from-primary-800 via-primary-900 to-primary-900 rounded-2xl p-4 sm:p-5 text-white shadow-lg space-y-3.5 mb-3.5 animate-in fade-in duration-200 relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

                <div class="flex items-center justify-between gap-2 relative z-10">
                    <div class="flex items-center gap-2">
                        <div>
                            <h3 class="font-bold text-sm sm:text-base leading-tight">Sedang Mencari Rekan Jasa</h3>
                            <p class="text-[11px] text-white/90">Sistem sedang menunggu mitra terdekat</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-black/20 text-white border border-white/30 backdrop-blur-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        <span x-text="isExpired ? 'Waktu Habis' : 'Aktif'">Aktif</span>
                    </span>
                </div>

                <!-- Digital Countdown Timer Display -->
                <div class="bg-sky-900/10 backdrop-blur-md rounded-xl p-3.5 border border-white/10 relative z-10">
                    <div class="flex items-center justify-between text-xs font-semibold text-white mb-2.5">
                        <span>⏳ Batas Waktu Pencarian Rekan Jasa:</span>
                        <span class="text-[11px] text-white font-bold">{{ $effectiveExpiry ? $effectiveExpiry->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}</span>
                    </div>

                    <div class="flex items-center justify-center gap-2 font-mono">
                        <div class="flex flex-col items-center bg-white/95 text-gray-900 rounded-xl px-3 py-1.5 shadow-sm min-w-[56px]">
                            <span x-text="hours" class="text-lg sm:text-xl font-black text-amber-950">00</span>
                            <span class="text-[9px] font-sans font-semibold text-gray-500 uppercase tracking-wider">Jam</span>
                        </div>
                        <span class="text-xl font-bold text-white pb-2">:</span>
                        <div class="flex flex-col items-center bg-white/95 text-gray-900 rounded-xl px-3 py-1.5 shadow-sm min-w-[56px]">
                            <span x-text="minutes" class="text-lg sm:text-xl font-black text-amber-950">00</span>
                            <span class="text-[9px] font-sans font-semibold text-gray-500 uppercase tracking-wider">Menit</span>
                        </div>
                        <span class="text-xl font-bold text-white pb-2">:</span>
                        <div class="flex flex-col items-center bg-white/95 text-gray-900 rounded-xl px-3 py-1.5 shadow-sm min-w-[56px]">
                            <span x-text="seconds" class="text-lg sm:text-xl font-black text-rose-600 animate-pulse">00</span>
                            <span class="text-[9px] font-sans font-semibold text-rose-600 uppercase tracking-wider">Detik</span>
                        </div>
                    </div>
                </div>

                <!-- Info Jaminan Pengembalian Dana Tahan 100% -->
                <div class="bg-gray-800/20 backdrop-blur-xs rounded-xl p-3 text-xs leading-relaxed text-white border border-white/10 relative z-10 flex items-start gap-2.5">
                    <span class="text-base shrink-0"></span>
                    <div>
                        <span class="font-bold block text-white text-[11.5px] mb-0.5">Jaminan 100% Saldo Kembali (Dana Tahan)</span>
                        <p class="text-[11px] text-white">
                            Pembayaran Anda sebesar <strong>Rp {{ number_format($help->total_amount > 0 ? $help->total_amount : $help->amount, 0, ',', '.') }}</strong> saat ini aman ditahan oleh sistem. Jika hingga batas waktu di atas tidak ada mitra yang mengambil, tugas akan otomatis berakhir dan dana langsung 100% dikembalikan ke saldo dompet Anda.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Review Banner (Khusus Status Pengajuan Batal / Kendala Lapangan oleh Mitra - ) --}}
        @if($help->status === 'partner_cancel_requested')
            @php
                $effectiveExpiry = $help->effective_expires_at;
                $isSearchExpired = $effectiveExpiry ? now()->gte($effectiveExpiry) : false;
                $reasonText = $help->partner_cancel_reason ?: ($help->cancel_reason ?: 'Kendala lapangan saat proses pengerjaan');
            @endphp
            <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/70 rounded-2xl p-4 mb-3.5 shadow-xs space-y-2.5 animate-in fade-in duration-200">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center font-bold text-sm shadow-xs">
                            ⚠️
                        </div>
                        <div>
                            <h3 class="font-bold text-xs sm:text-sm text-rose-950 dark:text-rose-100 leading-tight">
                                Mitra Melaporkan Kendala Lapangan
                            </h3>
                            <p class="text-[11px] text-rose-700 dark:text-rose-300">Pengajuan pembatalan / kendala pengerjaan mitra</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200 border border-rose-300 dark:border-rose-700">
                        Menunggu Keputusan Anda
                    </span>
                </div>

                <p class="text-xs text-rose-900/90 dark:text-rose-200/90 leading-relaxed">
                    Mitra <strong>{{ $help->mitra?->name ?? 'Mitra' }}</strong> melaporkan kendala dan mengajukan pembatalan. Anda dapat memilih untuk <strong>mencari rekan jasa pengganti</strong> (mengembalikan pesanan ke pool daftar mitra) atau <strong>menyetujui pembatalan (refund saldo 100%)</strong> pada panel di bagian bawah.
                </p>

                <div class="p-2.5 bg-white/80 dark:bg-gray-800/80 rounded-xl border border-rose-200/70 dark:border-rose-800/60 text-xs">
                    <span class="font-bold text-gray-700 dark:text-gray-300 block mb-0.5 text-[11px]">Alasan Dilaporkan Mitra:</span>
                    <span class="text-rose-700 dark:text-rose-400 font-semibold italic">"{{ $reasonText }}"</span>
                </div>
            </div>
        @endif

        {{-- Service Info --}}
        <div class="bg-white dark:bg-gray-800 p-4 sm:p-5 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-700/70 space-y-3.5">
            {{-- Title --}}
            <div>
                <h2 class="font-bold text-base sm:text-lg text-gray-900 dark:text-white leading-snug break-words">
                    {{ $help->title }}
                </h2>
            </div>

            {{-- Category & Schedule Tags --}}
            <div class="flex items-center gap-1.5 flex-wrap">
                @if($help->isPickup())
                    @if($help->service_category === 'passenger')
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200/60 dark:border-gray-600">
                            <span>👥</span>
                            <span>Antar Penumpang</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200/60 dark:border-gray-600">
                            <span>📦</span>
                            <span>Barang & Dokumen</span>
                        </span>
                    @endif
                @else
                    <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200/60 dark:border-gray-600">
                        <span>🛠️</span>
                        <span>Kerja Serabutan</span>
                    </span>
                @endif

                @if($help->isScheduled())
                    <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200/60 dark:border-gray-600">
                        <span>📅</span>
                        <span>Terjadwal</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200/60 dark:border-gray-600">
                        <span>⚡</span>
                        <span>Segera</span>
                    </span>
                @endif
            </div>

            {{-- Clean Neutral Payment Bar --}}
            <div class="bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/80 rounded-xl p-3 sm:p-3.5 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 block">Total Terbayar (Dana Tahan)</span>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500 block truncate">Pembayaran aman tersimpan</span>
                </div>

                <div class="text-right shrink-0">
                    <span class="text-base sm:text-lg font-bold text-gray-900 dark:text-white font-mono tracking-tight block">
                        Rp {{ number_format($help->total_amount > 0 ? $help->total_amount : $help->amount, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Partner Info --}}
            @if($help->mitra)
                <div class="p-3.5 bg-gray-50/80 dark:bg-gray-750/70 rounded-2xl border border-gray-100 dark:border-gray-700/60 shadow-2xs space-y-2.5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($help->mitra->profile_photo ?? $help->mitra->photo)
                                <img src="{{ asset('storage/' . ($help->mitra->profile_photo ?? $help->mitra->photo)) }}" alt="{{ $help->mitra->name }}" class="w-11 h-11 rounded-full object-cover border-2 border-sky-200 dark:border-sky-800 shrink-0">
                            @else
                                <div class="w-11 h-11 rounded-full bg-sky-600 flex items-center justify-center text-white font-bold shrink-0 shadow-2xs">
                                    {{ strtoupper(substr($help->mitra->name ?? 'M', 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <h3 class="font-bold text-sm text-gray-900 dark:text-white truncate">{{ $help->mitra->name ?? 'Mitra' }}</h3>
                                    <span class="text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 px-1.5 py-0.2 rounded-md">Mitra</span>
                                </div>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <div class="flex items-center gap-0.5 text-amber-500">
                                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        @php
                                            $mitra = $help->mitra;
                                            $avgRating = $mitra ? ($mitra->mitra_average_rating ?? ($mitra->rating ?? 0)) : 0;
                                            $ratingCount = $mitra ? ($mitra->mitra_rating_count ?? null) : null;
                                        @endphp
                                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ number_format($avgRating, 1) }}</span>
                                    </div>
                                    @if($ratingCount)
                                        <span class="text-[11px] text-gray-400 dark:text-gray-400">({{ $ratingCount }} ulasan)</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('customer.chat', $help->id) }}" wire:navigate class="w-10 h-10 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center hover:bg-sky-50 dark:hover:bg-gray-700 hover:border-sky-300 dark:hover:border-sky-600 transition shadow-2xs text-gray-700 dark:text-gray-200 cursor-pointer">
                                <svg class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    {{-- Informasi Kendaraan Khusus Layanan Antar & Jemput --}}
                    @if($help->isPickup())
                        <div class="pt-2.5 border-t border-gray-200/60 dark:border-gray-700/60 flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60 flex items-center justify-center text-xs shrink-0 shadow-2xs">
                                    🛵
                                </span>
                                <div class="min-w-0">
                                    <div class="text-[10px] text-gray-400 dark:text-gray-400 font-semibold uppercase tracking-wider">Kendaraan Mitra</div>
                                    <div class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">
                                        {{ $help->mitra->vehicle_display_name }}
                                    </div>
                                </div>
                            </div>
                            @if(!empty($help->mitra->vehicle_plate_number))
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-zinc-900 dark:bg-zinc-950 text-white rounded-lg border border-zinc-700 shadow-xs">
                                    <span class="text-[9px] font-bold text-amber-400 font-mono tracking-wide">PLAT</span>
                                    <span class="font-mono font-black text-xs tracking-wider">{{ $help->mitra->vehicle_plate_number }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            {{-- Description & Additional Details --}}
            <div class="pt-3 border-t border-gray-100 dark:border-gray-700/60 space-y-3">
                <div>
                    <h3 class="font-bold text-xs uppercase tracking-wider text-gray-400 dark:text-gray-400 mb-1.5">Deskripsi Bantuan</h3>
                    @if(!empty($help->description))
                        <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-line break-words bg-gray-50/50 dark:bg-gray-750/40 p-3 rounded-xl border border-gray-100 dark:border-gray-700/40">{{ $help->description }}</p>
                    @else
                        <p class="text-xs text-gray-400 italic">Tidak ada keterangan tambahan.</p>
                    @endif
                </div>

                {{-- Detail Grid Pills --}}
                <div class="grid grid-cols-2 gap-2 text-xs">
                    @if($help->isPickup() && $help->mitra)
                        <div class="col-span-2 p-2.5 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-800/40 flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-base shrink-0">🛵</span>
                                <div class="min-w-0">
                                    <span class="text-[10px] font-semibold text-amber-900/80 dark:text-amber-300/80 block">Kendaraan Rekan Jasa:</span>
                                    <span class="font-bold text-xs text-gray-900 dark:text-white truncate block">{{ $help->mitra->vehicle_display_name }}</span>
                                </div>
                            </div>
                            @if(!empty($help->mitra->vehicle_plate_number))
                                <div class="px-2 py-0.5 bg-black text-white dark:bg-zinc-900 rounded font-mono font-bold text-xs tracking-wider border border-zinc-700 shrink-0">
                                    {{ $help->mitra->vehicle_plate_number }}
                                </div>
                            @endif
                        </div>
                    @endif

                    @if(!empty($help->equipment_provided))
                        <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/60">
                            <span class="text-[10px] font-semibold text-gray-400 dark:text-gray-400 block mb-0.5">Perlengkapan:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 break-words">{{ $help->equipment_provided }}</span>
                        </div>
                    @endif

                    @if($help->mitra && !empty($help->mitra->phone))
                        <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/60">
                            <span class="text-[10px] font-semibold text-gray-400 dark:text-gray-400 block mb-0.5">Kontak Mitra:</span>
                            <a href="tel:{{ $help->mitra->phone }}" class="font-bold text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1 truncate">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span>{{ $help->mitra->phone }}</span>
                            </a>
                        </div>
                    @endif

                    @if(!empty($help->city->name) || !empty($help->province->name))
                        <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/60">
                            <span class="text-[10px] font-semibold text-gray-400 dark:text-gray-400 block mb-0.5">Lokasi Wilayah:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 truncate block">{{ $help->city->name ?? '-' }}{{ $help->province ? (', ' . $help->province->name) : '' }}</span>
                        </div>
                    @endif

                    @if($help->isScheduled() && !empty($help->scheduled_at))
                        <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/60">
                            <span class="text-[10px] font-semibold text-gray-400 dark:text-gray-400 block mb-0.5">Jadwal Pelaksanaan:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 block">📅 {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }} WIB</span>
                        </div>
                    @else
                        <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/60">
                            <span class="text-[10px] font-semibold text-gray-400 dark:text-gray-400 block mb-0.5">Waktu Pelaksanaan:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 block">⚡ Langsung Dikerjakan (Segera)</span>
                        </div>
                    @endif
                </div>

                {{-- Order Photo Attachment --}}
                @if(!empty($help->photo))
                    <div class="mt-3 pt-2">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Foto Lampiran Pesanan
                            </span>
                            <a href="{{ asset('storage/' . $help->photo) }}" target="_blank" rel="noopener" class="text-[11px] font-semibold text-sky-600 dark:text-sky-400 hover:underline">Lihat Gambar Penuh ↗</a>
                        </div>
                        <div class="relative rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-xs group bg-gray-100 dark:bg-gray-900 max-h-64 flex items-center justify-center">
                            <img src="{{ asset('storage/' . $help->photo) }}" alt="Foto pesanan" class="w-full h-auto max-h-64 object-contain group-hover:scale-105 transition-transform duration-300">
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-between text-[11px] text-gray-400 dark:text-gray-400 pt-1 border-t border-gray-100 dark:border-gray-700/50">
                    <span>Dibuat: {{ \Carbon\Carbon::parse($help->created_at)->translatedFormat('d M Y, H:i') }}</span>
                    <span>Diperbarui: {{ \Carbon\Carbon::parse($help->updated_at)->diffForHumans() }}</span>
                </div>
            </div>
        </div>

        {{-- Progress Stepper Card (Revisi 3 Dynamic Multi-Stage Stepper) --}}
        <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100 dark:border-gray-700/50">
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Progres & Tahapan Layanan</span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5 mt-0.5">
                        <span class="text-base">{{ $help->progress_icon }}</span>
                        <span class="text-primary-600 dark:text-primary-400 font-bold">{{ $help->progress_summary }}</span>
                    </h3>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-white dark:bg-gray-800 text-primary-600 dark:text-primary-400 border border-gray-200 dark:border-gray-700 shadow-2xs">
                        {{ $help->multi_stage_progress_percentage }}%
                    </span>
                </div>
            </div>

            <!-- Dynamic Stepper -->
            @php
                $multiSteps = $help->multi_stage_steps ?? [];
                $stepCount = is_countable($multiSteps) ? count($multiSteps) : 0;
                $isDone = in_array($help->status, ['selesai', 'completed']);
                
                $activeIndex = 0;
                foreach ($multiSteps as $idx => $st) {
                    if (!empty($st['active'])) {
                        $activeIndex = $idx;
                        break;
                    }
                }
                if ($isDone && $stepCount > 0) $activeIndex = $stepCount - 1;
            @endphp

            <div class="relative pt-2 pb-1">
                <!-- Connecting Line -->
                <div class="absolute top-6 left-6 right-6 h-1 bg-gray-100 dark:bg-gray-700 -z-0">
                    <div class="h-full bg-primary-600 dark:bg-primary-500 transition-all duration-700 rounded-full"
                         style="width: {{ $stepCount > 1 ? max(0, min(100, ($activeIndex / ($stepCount - 1)) * 100)) : 0 }}%;"></div>
                </div>

                <!-- Step Nodes -->
                <div class="flex items-start justify-between relative z-10">
                    @foreach($multiSteps as $idx => $s)
                        @php
                            $isPassed = $idx < $activeIndex || ($idx === $stepCount - 1 && $isDone);
                            $isCurrent = $idx === $activeIndex && !$isDone;
                            $colWidth = $stepCount > 0 ? (100 / $stepCount) : 20;
                        @endphp
                        <div class="flex flex-col items-center text-center" style="width: {{ $colWidth }}%;">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 shadow-xs
                                {{ $isPassed ? 'bg-primary-600 text-white shadow-primary-500/20' : ($isCurrent ? 'bg-primary-600 text-white ring-4 ring-primary-100 dark:ring-primary-950/60 animate-pulse' : 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500') }}">
                                @if($isPassed)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    <span>{{ $s['icon'] }}</span>
                                @endif
                            </div>
                            <span class="text-[10px] mt-1.5 leading-tight {{ $isCurrent ? 'text-primary-600 dark:text-primary-400 font-bold' : ($isPassed ? 'text-gray-700 dark:text-gray-300 font-medium' : 'text-gray-400 dark:text-gray-500 font-normal') }}">
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

        {{-- Live Dynamic Travel ETA & Progress Card  --}}
        @if($help->mitra_id && in_array($help->status, ['taken', 'partner_on_the_way', 'partner_arrived']))
            @php
                $travelProgress = app(\App\Services\HelpScheduleService::class)->getLiveTravelProgress($help);
            @endphp
            <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 p-4 rounded-2xl shadow-xs border border-gray-200 dark:border-gray-700 mt-2 space-y-3 transition-colors" wire:poll.5s.visible>
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/80 pb-2.5">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm flex-shrink-0 {{ $help->isPickup() ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60 shadow-2xs' : 'bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/80 dark:border-blue-800/60 shadow-2xs' }}">
                            @if($help->isPassenger())
                                <span>🛵</span>
                            @elseif($help->isPickup())
                                <span>📦</span>
                            @else
                                <span>🛠️</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate">
                                {{ $travelProgress['is_arrived'] ? 'Rekan Jasa Telah Tiba' : 'Pemantauan Perjalanan Rekan Jasa' }}
                            </h4>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate mt-0.5">Menuju: {{ $travelProgress['target_label'] }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 flex-wrap justify-end">
                        @if(!empty($travelProgress['is_near_arrival']) && !$travelProgress['is_arrived'])
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-700/60 animate-pulse">
                                📍 Hampir Sampai
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $travelProgress['is_arrived'] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700/60' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300 border border-blue-300 dark:border-blue-700/60 animate-pulse' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $travelProgress['is_arrived'] ? 'bg-emerald-600 dark:bg-emerald-400' : 'bg-blue-600 dark:bg-blue-400' }}"></span>
                            {{ $travelProgress['is_arrived'] ? 'Tiba di Lokasi' : 'Live GPS Sync' }}
                        </span>
                    </div>
                </div>

                @if($travelProgress['is_arrived'])
                    <div class="bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/80 rounded-xl p-3 text-center">
                        <p class="text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>Rekan Jasa sudah sampai di lokasi tujuan</span>
                        </p>
                        <p class="text-[11px] text-emerald-700 dark:text-emerald-300/80 mt-0.5">Silakan temui rekan jasa untuk koordinasi dan memulai bantuan.</p>
                    </div>
                @elseif($help->status === 'taken' && $help->isScheduled() && !$help->canPartnerStartDeparture())
                    {{-- Status Terjadwal Menunggu Waktu Buka Keberangkatan --}}
                    <div class="bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800/80 rounded-xl p-3 text-center space-y-1">
                        <div class="flex items-center justify-center gap-1.5 text-xs font-bold text-indigo-900 dark:text-indigo-200">
                            <span>📅 Tugas Terjadwal (Pukul {{ $help->getScheduledTargetTime()?->format('H:i') }})</span>
                        </div>
                        <p class="text-[11px] text-indigo-800 dark:text-indigo-300/90 leading-relaxed">
                            Mitra <strong>{{ $help->mitra?->name }}</strong> telah ditugaskan dan bersiap. Mitra akan mulai berangkat menuju lokasi Anda pada pukul <strong>{{ $help->departure_window_opens_at?->format('H:i') }}</strong> ({{ $help->departure_lead_minutes }} menit sebelum jadwal).
                        </p>
                        <div class="pt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 border border-indigo-300 dark:border-indigo-700">
                                ⏳ Berangkat dalam {{ $help->departure_countdown_formatted }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-2 text-center">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-2.5 border border-gray-200/70 dark:border-gray-700/60">
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 block font-medium">Jarak Tersisa</span>
                            <span class="text-sm font-extrabold text-gray-900 dark:text-white font-mono block mt-0.5">
                                {{ $travelProgress['formatted_distance'] }}
                            </span>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-2.5 border border-gray-200/70 dark:border-gray-700/60">
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 block font-medium">Estimasi Waktu Tiba (Live ETA)</span>
                            <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400 font-mono block mt-0.5">
                                {{ $travelProgress['formatted_eta'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Identitas Kendaraan & Penjemput --}}
                    @if($help->isPickup() && $help->mitra)
                        <div class="bg-amber-50/90 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 rounded-xl p-3 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-200 flex items-center justify-center text-sm shrink-0 shadow-2xs">
                                    🛵
                                </div>
                                <div class="min-w-0">
                                    <span class="text-[10px] text-amber-900/70 dark:text-amber-300/70 font-semibold block uppercase tracking-wider">Mitra & Kendaraan:</span>
                                    <span class="text-xs font-bold text-gray-900 dark:text-white truncate block">
                                        {{ $help->mitra->name }} • {{ $help->mitra->vehicle_display_name }}
                                    </span>
                                </div>
                            </div>
                            @if(!empty($help->mitra->vehicle_plate_number))
                                <div class="px-2.5 py-1 bg-black dark:bg-zinc-900 text-white rounded-lg font-mono font-black text-xs tracking-wider border border-zinc-700 shadow-xs shrink-0 text-center">
                                    <span class="block text-[8px] text-amber-400 leading-none mb-0.5">PLAT NO</span>
                                    <span>{{ $help->mitra->vehicle_plate_number }}</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Traffic Condition Bar (Deteksi Evaluasi 10 Menit) --}}
                    @if($help->status === 'partner_on_the_way')
                        <div class="bg-gray-50 dark:bg-gray-700/40 border border-gray-200/70 dark:border-gray-700 rounded-xl p-2.5 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-1.5">
                                <span>🚦</span>
                                <span class="text-[11px] text-gray-600 dark:text-gray-300 font-medium">Kondisi Lalu Lintas:</span>
                            </div>
                            <div>
                                @if(($travelProgress['traffic_status'] ?? '') === 'macet' || ($travelProgress['is_delayed'] ?? false))
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300 border border-rose-300 dark:border-rose-800 text-[10px] font-bold">
                                        🔴 Macet / Padat
                                    </span>
                                @elseif(($travelProgress['traffic_status'] ?? '') === 'padat_merayap')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300 dark:border-amber-800 text-[10px] font-bold">
                                        🟡 Ramai Padat
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 text-[10px] font-bold">
                                        🟢 Lancar
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center justify-between text-[10px] text-gray-500 dark:text-gray-400 pt-1 border-t border-gray-100 dark:border-gray-700/60">
                        <span>💡 Waktu tiba disesuaikan otomatis mengikuti posisi GPS Rekan Jasa</span>
                        @if($travelProgress['partner_last_seen'])
                            <span class="font-mono text-gray-700 dark:text-gray-300 font-semibold">{{ $travelProgress['partner_last_seen'] }}</span>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        {{-- Location / Route Section --}}
        <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60 space-y-3">
            @if($help->isPickup())
                {{-- 2-Point Route: Pickup & Delivery --}}
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-2">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Rute Layanan Antar Jemput</span>
                    @if($help->service_route_distance_km)
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-950/50 px-2 py-0.5 rounded-md border border-sky-200/60 dark:border-sky-800">
                            ± {{ number_format($help->service_route_distance_km, 1) }} KM
                        </span>
                    @endif
                </div>

                {{-- Titik Penjemputan --}}
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0 mt-0.5 border border-emerald-100 dark:border-emerald-900/50 shadow-2xs">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-0.5">
                            Titik Penjemputan (Titik Awal)
                        </div>
                        <p class="font-bold text-sm text-gray-900 dark:text-white leading-snug break-words">
                            {{ $help->pickup_address ?: ($help->location ?: 'Alamat sesuai titik jemput') }}
                        </p>
                    </div>
                </div>

                {{-- Titik Pengantaran --}}
                <div class="flex items-start gap-3 pt-2 border-t border-gray-100 dark:border-gray-700/40">
                    <div class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0 mt-0.5 border border-rose-100 dark:border-rose-900/50 shadow-2xs">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-0.5">
                            Titik Pengantaran / Tujuan (Titik Akhir)
                        </div>
                        <p class="font-bold text-sm text-gray-900 dark:text-white leading-snug break-words">
                            {{ $help->delivery_address ?: ($help->full_address ?: 'Alamat sesuai titik antar') }}
                        </p>
                    </div>
                </div>
            @else
                {{-- Single Location for On-Site Service --}}
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 flex items-center justify-center flex-shrink-0 mt-0.5 border border-red-100 dark:border-red-900/50 shadow-2xs">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 flex-wrap mb-0.5">
                            <h3 class="text-[11px] font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Titik Alamat Lokasi</h3>
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700/60 px-2 py-0.5 rounded-md border border-gray-200/60 dark:border-gray-700">
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ $help->city->name ?? 'Kota Lokasi' }}
                            </span>
                        </div>
                        <p class="font-bold text-sm text-gray-900 dark:text-white leading-snug break-words">
                            {{ $help->location ?? 'Alamat sesuai titik peta' }}
                        </p>
                    </div>
                </div>

                <!-- Detail Patokan Tempat / Ciri Rumah (Opsional) -->
                <div class="rounded-xl border p-3 transition-colors {{ !empty($help->full_address) ? 'bg-amber-50/70 dark:bg-amber-950/20 border-amber-200/80 dark:border-amber-800/60' : 'bg-gray-50 dark:bg-gray-700/20 border-gray-200/70 dark:border-gray-700/60' }}">
                    <div class="flex items-center gap-1.5 mb-1.5">
                        <svg class="w-3.5 h-3.5 {{ !empty($help->full_address) ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400 dark:text-gray-500' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                        </svg>
                        <h4 class="text-xs font-bold {{ !empty($help->full_address) ? 'text-amber-900 dark:text-amber-200' : 'text-gray-600 dark:text-gray-400' }}">
                            Detail Ciri" Tempat
                        </h4>
                        <span class="text-[11px] font-normal text-gray-400 dark:text-gray-500">(Opsional)</span>
                    </div>

                    @if(!empty($help->full_address))
                        <p class="text-xs text-white dark:text-white font-medium leading-relaxed whitespace-pre-line break-words pl-5">
                            {{ $help->full_address }}
                        </p>
                    @else
                        <p class="text-xs text-gray-400 dark:text-gray-500 italic pl-5">
                            Anda tidak menyertakan patokan/ ciri khusus rumah. Rekan jasa akan mengikuti navigasi GPS.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Schedule --}}
        <div class="bg-white dark:bg-gray-800 mt-2.5 p-4 rounded-2xl shadow-xs border border-gray-100 dark:border-gray-700/70">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="font-bold text-sm text-gray-900 dark:text-white mb-1">Waktu Pelaksanaan</h3>
                    @if($help->isScheduled())
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                {{ \Carbon\Carbon::parse($help->scheduled_at ?? $help->service_scheduled_at)->locale('id')->translatedFormat('l, d F Y • H:i') }} WIB
                            </span>
                        </div>
                    @else
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                Dikerjakan langsung setelah pesanan diambil oleh Rekan Jasa
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card Penjelasan Selesai Otomatis saat Sedang Berjalan --}}
        @if(in_array($help->status, ['taken', 'partner_on_the_way', 'partner_arrived', 'in_progress']))
            <div class="bg-blue-50 dark:bg-blue-950/40 mt-2 px-4 py-3.5 rounded-xl border border-blue-200 dark:border-blue-800/60 flex items-start gap-3 shadow-xs">
                <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0 mt-0.5 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1 text-xs text-blue-950 dark:text-blue-200 leading-relaxed">
                    <span class="font-bold block text-blue-950 dark:text-blue-100 text-xs mb-0.5">Sedang di selesaikan oleh Rekan Jasa</span>
                    Tugas ini akan otomatis selesai begitu Rekan Jasa menyelesaikan tugas dan mengunggah foto bukti pengerjaan.
                </div>
            </div>
        @endif

        {{-- Status Timeline - Redesigned visual to match reference --}}
        <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60">
            <h3 class="font-bold text-sm text-gray-900 dark:text-white mb-4">Status Pesanan</h3>

            @php
                $statuses = [
                    [ 'key' => 'searching', 'title' => 'Mencari Rekan Jasa', 'time' => $help->mitra_assigned_at ?? $help->taken_at, 'active' => in_array($help->status, ['menunggu_mitra', 'taken', 'partner_on_the_way', 'partner_arrived', 'in_progress', 'waiting_customer_confirmation', 'selesai']), 'current' => $help->status === 'menunggu_mitra' ],
                    [ 'key' => 'accepted', 'title' => 'Menunggu Rekan Jasa berangkat', 'time' => $help->taken_at, 'active' => in_array($help->status, ['taken', 'partner_on_the_way', 'partner_arrived', 'in_progress', 'waiting_customer_confirmation', 'selesai']), 'current' => $help->status === 'taken' ],
                    [ 'key' => 'on_the_way', 'title' => 'Rekan Jasa menuju ke lokasi', 'time' => $help->partner_started_moving_at, 'active' => in_array($help->status, ['partner_on_the_way', 'partner_arrived', 'in_progress', 'waiting_customer_confirmation', 'selesai']), 'current' => $help->status === 'partner_on_the_way' ],
                    [ 'key' => 'arrived', 'title' => 'Rekan Jasa tiba di lokasi', 'time' => $help->partner_arrived_at, 'active' => in_array($help->status, ['partner_arrived', 'in_progress', 'waiting_customer_confirmation', 'selesai']), 'current' => $help->status === 'partner_arrived' ],
                    [ 'key' => 'in_progress', 'title' => 'Pelayanan dalam proses', 'time' => $help->service_started_at, 'active' => in_array($help->status, ['in_progress', 'waiting_customer_confirmation', 'selesai']), 'current' => $help->status === 'in_progress' ],
                    [ 'key' => 'completed', 'title' => 'Pesanan selesai', 'time' => $help->completed_at ?? $help->service_completed_at, 'active' => $help->status === 'selesai', 'current' => $help->status === 'selesai' ]
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
                <div class="bg-purple-50 dark:bg-purple-950/40 mt-2.5 p-4 rounded-2xl border border-purple-200 dark:border-purple-800/70 shadow-xs space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center flex-shrink-0 mt-0.5 border border-purple-500/20">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 flex-wrap mb-1">
                                <h4 class="text-xs sm:text-sm font-bold text-purple-950 dark:text-purple-100">Laporan Aduan</h4>
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
                            wire:navigate
                            class="w-full sm:w-auto px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition-all shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            <span>Buka Ruang Chat Admin</span>
                        </a>
                    </div>
                </div>
            @elseif($isWithin24H)
                <div class="bg-sky-50 dark:bg-sky-950/40 mt-2.5 p-4 sm:p-5 rounded-2xl border border-sky-200 dark:border-sky-800/60 shadow-xs space-y-3.5">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-xl bg-sky-500/15 dark:bg-sky-500/25 text-sky-600 dark:text-sky-400 flex items-center justify-center flex-shrink-0 mt-0.5 border border-sky-500/20 shadow-2xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 flex-wrap mb-1">
                                <h4 class="text-xs sm:text-sm font-bold text-sky-950 dark:text-sky-100">
                                    Garansi Perlindungan Layanan 1x24 Jam
                                </h4>
                                <span class="text-[10px] bg-sky-100 dark:bg-sky-900/70 text-sky-700 dark:text-sky-300 px-2 py-0.5 rounded-full font-extrabold border border-sky-200/80 dark:border-sky-700 shadow-2xs">
                                    Aktif
                                </span>
                            </div>
                            <p class="text-xs text-sky-900/80 dark:text-sky-200/85 leading-relaxed">
                                Jika mitra berbohong, tidak menyelesaikan tugas, atau melanggar aturan, Anda dapat mengajukan laporan refund sebelum: <strong class="font-bold text-sky-950 dark:text-sky-100">{{ \Carbon\Carbon::parse($help->completed_at)->addHours(24)->translatedFormat('d M Y, H:i') }} WIB</strong>.
                            </p>
                        </div>
                    </div>

                    @if ($this->activeReport)
                        <div class="p-3 bg-amber-100/80 dark:bg-amber-950/50 rounded-xl border border-amber-300 dark:border-amber-800 text-xs flex items-center justify-between gap-3 flex-wrap">
                            <div class="flex items-center gap-2">
                                <span class="text-base">⚠️</span>
                                <div>
                                    <span class="font-bold text-amber-950 dark:text-amber-200 block">Laporan Aduan Sedang Ditinjau Admin</span>
                                    <span class="text-[11px] text-amber-800 dark:text-amber-300">Status: {{ ucfirst($this->activeReport->status) }}</span>
                                </div>
                            </div>
                            <a href="{{ route('customer.chat', ['admin' => 1, 'report' => $this->activeReport->id]) }}" wire:navigate class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold text-[11px] transition shadow-2xs">
                                💬 Buka Diskusi Admin
                            </a>
                        </div>
                    @else
                        <a href="{{ route('customer.reports.create', ['help_id' => $help->id, 'user_id' => $help->mitra_id, 'type' => 'klaim_refund_pekerjaan_fiktif']) }}" 
                           wire:navigate
                           class="w-full py-2.5 px-4 bg-[#0098e7] hover:bg-[#0086cc] text-white rounded-xl text-xs font-bold transition-all shadow-xs active:scale-[0.99] flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>Laporkan / Ajukan Refund</span>
                        </a>
                    @endif
                </div>
            @endif
        @endif

        {{-- Status Pembekuan Sengketa (Disputed Freeze) --}}
        @if($help->isDisputed())
            <div class="bg-rose-50 dark:bg-rose-950/40 mt-2 px-5 py-5 border border-rose-200 dark:border-rose-800 rounded-2xl shadow-xs space-y-3">
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
                                Dana Tahan Dibekukan
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
            <div class="bg-white dark:bg-gray-800 mt-2.5 p-5 border border-sky-200/80 dark:border-sky-500/30 rounded-2xl shadow-xs">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-11 h-11 rounded-2xl bg-sky-600 flex items-center justify-center flex-shrink-0 text-white shadow-2xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between gap-2 flex-wrap mb-1">
                            <h3 class="font-bold text-sm text-gray-900 dark:text-white">Menunggu Konfirmasi Anda</h3>
                            <span class="text-[11px] font-bold bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 px-2.5 py-0.5 rounded-full flex items-center gap-1 border border-sky-200 dark:border-sky-800 animate-pulse">
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
                    <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-750/70 rounded-2xl border border-gray-100 dark:border-gray-700">
                        <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block mb-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Foto Bukti Hasil Pengerjaan:
                        </span>
                        <a href="{{ asset('storage/' . $help->proof_photo) }}" target="_blank" rel="noopener" class="block rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-900/10 max-h-56 flex items-center justify-center group">
                            <img src="{{ asset('storage/' . $help->proof_photo) }}" alt="Bukti Pengerjaan" class="w-full h-auto max-h-56 object-contain group-hover:scale-105 transition-transform duration-300">
                        </a>
                        @if($help->completion_notes)
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-2 italic bg-white dark:bg-gray-800 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700">"{{ $help->completion_notes }}"</p>
                        @endif
                    </div>
                @endif

                <div class="space-y-2">
                    <button wire:click="confirmCompletion" 
                            wire:loading.attr="disabled"
                            wire:target="confirmCompletion"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-bold py-3 px-4 rounded-xl transition shadow-xs flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed disabled:pointer-events-none">
                        <svg wire:loading.remove wire:target="confirmCompletion" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg wire:loading wire:target="confirmCompletion" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="confirmCompletion">Konfirmasi Selesai & Teruskan Dana</span>
                        <span wire:loading wire:target="confirmCompletion">Memproses Konfirmasi...</span>
                    </button>

                    <button wire:click="openDisputeModal" 
                            wire:loading.attr="disabled"
                            wire:target="confirmCompletion"
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
                <div class="bg-white dark:bg-gray-800 mt-2.5 px-5 py-4 border border-emerald-200/80 dark:border-emerald-500/30 rounded-2xl shadow-xs">
                    <div class="flex items-start gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 shadow-2xs">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <h3 class="font-bold text-sm text-gray-900 dark:text-white">Penilaian Anda</h3>
                                <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-full border border-emerald-200/60 dark:border-emerald-800">
                                    {{ $customerRating->rating }}.0 / 5
                                </span>
                            </div>
                            <div class="flex items-center gap-1 mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $customerRating->rating ? 'text-amber-400 fill-current' : 'text-gray-200 dark:text-gray-700' }}" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            @if($customerRating->review)
                                <p class="text-xs text-gray-700 dark:text-gray-300 italic bg-gray-50/60 dark:bg-gray-750/40 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700">"{{ $customerRating->review }}"</p>
                            @endif
                            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1.5">{{ $customerRating->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            @else
                {{-- Rating Form --}}
                <div class="bg-white dark:bg-gray-800 mt-2.5 px-5 py-5 border border-gray-100 dark:border-gray-700/80 rounded-2xl shadow-xs">
                    <div class="flex items-start gap-3.5 mb-4">
                        <div class="w-11 h-11 rounded-2xl bg-sky-600 text-white flex items-center justify-center flex-shrink-0 shadow-2xs">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-sm text-gray-900 dark:text-white">Bagaimana Pengalaman Anda?</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Beri rating & ulasan untuk {{ $help->mitra->name ?? 'mitra' }}</p>
                        </div>
                    </div>

                    {{-- Star Rating --}}
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Pilih Rating Bintang *</label>
                        <div class="flex items-center gap-2 justify-center py-2 bg-gray-50/70 dark:bg-gray-750/50 rounded-2xl border border-gray-100 dark:border-gray-700/60">
                            @for($i = 1; $i <= 5; $i++)
                                <button 
                                    type="button"
                                    wire:click="setRating({{ $i }})"
                                    class="p-1 focus:outline-none transition-transform hover:scale-125 cursor-pointer">
                                    <svg class="w-8 h-8 {{ $rating >= $i ? 'text-amber-400 fill-current drop-shadow-xs' : 'text-gray-300 dark:text-gray-600 fill-current hover:text-amber-300' }}" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                            @endfor
                            @if($rating > 0)
                                <span class="ml-2 text-xs font-black text-sky-600 dark:text-sky-400 bg-white dark:bg-gray-800 px-2.5 py-1 rounded-full border border-sky-200 dark:border-sky-800 shadow-2xs">{{ $rating }}.0 / 5</span>
                            @endif
                        </div>
                        @error('rating')
                            <p class="text-xs text-rose-600 dark:text-rose-400 mt-1.5 text-center">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Review Text --}}
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Ulasan (Opsional)</label>
                        <textarea 
                            wire:model="review"
                            rows="3"
                            placeholder="Ceritakan pengalaman Anda dengan mitra ini..."
                            class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-750/70 border border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-transparent text-xs text-gray-900 dark:text-white placeholder-gray-400 transition"
                            maxlength="500"></textarea>
                        <div class="flex justify-between items-center mt-1">
                            @error('review')
                                <p class="text-[11px] text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @else
                                <p class="text-[10px] text-gray-400">Maksimal 500 karakter</p>
                            @enderror
                            <p class="text-[10px] text-gray-400">{{ strlen($review ?? '') }}/500</p>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button 
                        wire:click="submitRating"
                        wire:loading.attr="disabled"
                        class="w-full bg-[#0098e7] hover:bg-[#0086cc] text-white font-bold py-3 px-4 rounded-xl transition shadow-xs flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="submitRating">Kirim Rating</span>
                        <span wire:loading wire:target="submitRating" class="inline-flex items-center gap-1.5">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Mengirim...</span>
                        </span>
                    </button>
                </div>
            @endif
        @endif

        {{-- Payment Details --}}
        <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-sm text-gray-900 dark:text-white">Rincian Pembayaran</h3>
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
                        🛡️ <strong>Proteksi Dana Tahan:</strong> Pembayaran Anda ditahan aman oleh sistem SayaBantu selama pengerjaan. Dana baru akan diteruskan ke Rekan Jasa setelah Anda mengonfirmasi pekerjaan selesai dengan baik.
                    @else
                        Kamu dapat meminta tindakan tambahan selama sesi layanan berlangsung. Pastikan semua pembayaran dilakukan melalui aplikasi agar pesananmu tercatat dan terlindungi.
                    @endif
                </p>
            </div>
        </div>

        {{-- Cancel Actions & Statuses --}}
        @if($help->status === 'menunggu_mitra')
            <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60">
                <button wire:click="confirmCancel" class="w-full py-3 border-2 border-red-500 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg font-semibold text-sm transition cursor-pointer">
                    Batalkan Pesanan (Refund 100%)
                </button>
            </div>
        @elseif($help->isPickup() && in_array($help->status, ['taken', 'partner_on_the_way', 'partner_arrived', 'in_progress']))
            @if($help->isPrePickup())
                <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60 space-y-2">
                    <button wire:click="openCustomerCancelModal" class="w-full py-3 border-2 border-amber-500 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-xl font-bold text-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                        <span>🛑 Ajukan Pembatalan / Ganti Rekan Jasa</span>
                    </button>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 text-center">
                        Rekan Jasa dalam proses keberangkatan atau menunggu di titik jemput. Anda dapat mengajukan ganti mitra atau penarikan pekerjaan dengan konfirmasi (100% Full Refund).
                    </p>
                </div>
            @elseif($help->isStage6Arrived())
                <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60 space-y-2">
                    <button wire:click="$set('showStage6ConfirmModal', true)" class="w-full py-3 border-2 border-primary-500 text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/40 rounded-xl font-bold text-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                        <span>✅ Selesaikan Pesanan (Dianggap Sampai) / Batalkan</span>
                    </button>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 text-center">
                        Pengantaran telah menempuh > 5 KM atau mendekati tujuan dan dianggap telah sampai. Ongkos antar akan diteruskan ke Rekan Jasa setelah konfirmasi.
                    </p>
                </div>
            @elseif($help->canCustomerCancel())
                <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60 space-y-2">
                    <button wire:click="confirmCancel" class="w-full py-3 border-2 border-amber-500 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-xl font-bold text-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                        <span>🛑 Batalkan Pesanan (Kompensasi Jarak Tempuh)</span>
                    </button>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 text-center">
                        Pembatalan pada tahap ini memberikan kompensasi biaya perjalanan ke Rekan Jasa dan mengembalikan sisa saldo ke akun Anda.
                    </p>
                </div>
            @endif
        @elseif(in_array($help->status, ['taken', 'partner_on_the_way', 'partner_arrived', 'in_progress']))
            <div class="bg-white dark:bg-gray-800 mt-2 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60">
                <button wire:click="openCustomerCancelModal" class="w-full py-3 border-2 border-amber-500 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 rounded-xl font-bold text-xs transition cursor-pointer flex items-center justify-center gap-1.5">
                    <span>🛑 Ajukan Pembatalan Pesanan (Review Admin)</span>
                </button>
            </div>
        @elseif($help->status === 'partner_cancel_requested')
            @php
                $effectiveExpiry = $help->effective_expires_at;
                $isSearchExpired = $effectiveExpiry ? now()->gte($effectiveExpiry) : false;
                $cancelReasonText = $help->partner_cancel_reason ?: ($help->cancel_reason ?: 'Kendala lapangan saat proses bantuan');
            @endphp
            <div class="bg-white dark:bg-gray-800 border-2 border-amber-300/80 dark:border-amber-600/70 rounded-2xl p-4 sm:p-5 mt-3 shadow-md space-y-4 animate-in fade-in duration-200">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 flex items-center justify-center shrink-0 border border-amber-200 dark:border-amber-800 shadow-2xs font-bold text-lg">
                        ⚠️
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 flex-wrap mb-0.5">
                            <h4 class="font-bold text-sm text-gray-900 dark:text-white">Konfirmasi Pembatalan dari Mitra</h4>
                            <span class="text-[10px] font-extrabold bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-200 px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-700">
                                Butuh Respon Anda
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                            Mitra <strong class="text-gray-900 dark:text-white">{{ $help->mitra?->name ?? 'Mitra' }}</strong> melaporkan kendala dan tidak dapat melanjutkan tugas ini.
                        </p>
                    </div>
                </div>

                {{-- Box Alasan & Catatan Mitra --}}
                <div class="p-3 bg-amber-50/80 dark:bg-amber-950/40 rounded-xl border border-amber-200/80 dark:border-amber-800/60 text-xs space-y-1.5">
                    <div class="flex items-start gap-1.5">
                        <span class="font-bold text-amber-950 dark:text-amber-200 shrink-0 text-[11px]">Alasan Mitra:</span>
                        <span class="text-amber-900 dark:text-amber-300 font-semibold italic">"{{ $cancelReasonText }}"</span>
                    </div>
                    @if($help->cancel_deadline_at)
                        <p class="text-[10.5px] text-amber-700 dark:text-amber-400 pt-1 border-t border-amber-200/60 dark:border-amber-800/40">
                            ⏳ Batas respon otomatis: <strong>{{ $help->cancel_deadline_at->diffForHumans() }}</strong>. Jika belum direspon, sistem otomatis membatalkan & refund 100%.
                        </p>
                    @endif
                </div>

                {{-- Opsi Aksi Customer --}}
                <div class="space-y-2.5 pt-1">
                    {{-- Opsi 1: Relist ke Pool (Cari Pengganti) --}}
                    @if(!$isSearchExpired)
                        <button wire:click="relistPartnerCancel" 
                                wire:loading.attr="disabled"
                                class="w-full py-3 px-4 bg-gradient-to-r from-primary-600 to-sky-600 hover:from-primary-700 hover:to-sky-700 active:scale-[0.99] text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-primary-500/20 flex items-center justify-center gap-2 cursor-pointer">
                            <span wire:loading.remove wire:target="relistPartnerCancel" class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Cari Rekan Jasa Lain (Kembalikan ke Pool)</span>
                            </span>
                            <span wire:loading wire:target="relistPartnerCancel" class="inline-flex items-center gap-1.5">
                                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>Mengembalikan ke Pool...</span>
                            </span>
                        </button>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 text-center px-1">
                            Tugas Anda akan langsung ditayangkan kembali untuk dicari oleh mitra lain. Saldo Anda tetap aman tersimpan.
                        </p>
                    @else
                        <div class="p-2.5 bg-gray-100 dark:bg-gray-750 rounded-xl text-center text-xs text-gray-600 dark:text-gray-300">
                            ⏱️ <em>Batas waktu pencarian awal pesanan ini telah berakhir. Opsi pencarian pengganti dinonaktifkan.</em>
                        </div>
                    @endif

                    {{-- Opsi 2: Terima Pembatalan & Full Refund --}}
                    <button wire:click="acceptPartnerCancel" 
                            wire:loading.attr="disabled"
                            class="w-full py-2.5 px-4 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <span wire:loading.remove wire:target="acceptPartnerCancel" class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>Batalkan & Tarik Saldo (Refund 100%)</span>
                        </span>
                        <span wire:loading wire:target="acceptPartnerCancel" class="inline-flex items-center gap-1.5">
                            <svg class="animate-spin h-3.5 w-3.5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Memproses Refund...</span>
                        </span>
                    </button>
                </div>
            </div>
        @elseif($help->status === 'customer_cancel_requested')
            @php
                $latestCancelReq = $help->latestCancelRequest;
                $isSwitchPartner = $latestCancelReq && $latestCancelReq->action_type === 'switch_partner';
            @endphp
            @if($isSwitchPartner)
                <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-2xl p-4 mt-3 flex items-start gap-2.5">
                    <span class="text-xl leading-none">🔄</span>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="font-bold text-sm text-blue-900 dark:text-blue-200">Pengajuan Ganti Mitra Sedang Diproses</h4>
                            <span class="text-[10px] font-extrabold bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300 px-2 py-0.5 rounded-full border border-blue-200 dark:border-blue-700">2x Konfirmasi</span>
                        </div>
                        <p class="text-xs text-blue-800 dark:text-blue-300 leading-relaxed">
                            Sistem telah meminta konfirmasi kepada mitra dan meminta mitra menghubungi Anda terlebih dahulu. Jika mitra menyetujui, atau bila tidak ada respon/konfirmasi dari mitra, Admin dapat langsung memutuskan untuk mengembalikan pesanan ke pool pencarian rekan jasa baru. Saldo Anda tetap aman tersimpan.
                        </p>
                        @if($help->cancel_deadline_at)
                            <p class="text-[10.5px] text-blue-600 dark:text-blue-400 font-medium">
                                ⏳ Batas waktu konfirmasi: <strong>{{ $help->cancel_deadline_at->diffForHumans() }}</strong>
                            </p>
                        @endif
                    </div>
                </div>
            @else
                <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-2xl p-4 mt-3 flex items-start gap-2.5">
                    <span class="text-xl leading-none">⏳</span>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="font-bold text-sm text-rose-900 dark:text-rose-200">Pengajuan Penarikan Pekerjaan Sedang Ditinjau</h4>
                            <span class="text-[10px] font-extrabold bg-rose-100 dark:bg-rose-900/60 text-rose-800 dark:text-rose-300 px-2 py-0.5 rounded-full border border-rose-200 dark:border-rose-700">Full Refund 100%</span>
                        </div>
                        <p class="text-xs text-rose-800 dark:text-rose-300 leading-relaxed">
                            Pengajuan penarikan pekerjaan dan pengembalian dana 100% Anda sedang dalam proses konfirmasi mitra dan tinjauan Admin Wilayah.
                        </p>
                        @if($help->cancel_deadline_at)
                            <p class="text-[10.5px] text-rose-600 dark:text-rose-400 font-medium">
                                ⏳ Batas waktu konfirmasi: <strong>{{ $help->cancel_deadline_at->diffForHumans() }}</strong>
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- Real-time Tracking Map Modal --}}
    @if($showMapModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" wire:click.self="closeMapModal" data-tracking-modal>
            <div class="bg-white rounded-2xl w-full max-w-md mx-auto flex flex-col shadow-2xl" style="max-height: 85vh;">
                {{-- Header --}}
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-[#0098e7] rounded-t-2xl shrink-0">
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
                    <button type="button" wire:click="closeMapModal" class="text-white hover:bg-white/20 p-1.5 rounded-lg transition cursor-pointer">
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

                {{-- Vehicle Info Bar in Modal --}}
                @if($help->isPickup() && $help->mitra)
                    <div class="px-4 py-2 bg-amber-50/90 dark:bg-amber-950/40 border-b border-amber-200/80 dark:border-amber-800/60 flex items-center justify-between text-xs shrink-0">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-sm">🛵</span>
                            <div class="min-w-0">
                                <span class="font-bold text-gray-900 dark:text-white truncate block">
                                    {{ $help->mitra->name }} • {{ $help->mitra->vehicle_display_name }}
                                </span>
                            </div>
                        </div>
                        @if(!empty($help->mitra->vehicle_plate_number))
                            <span class="px-2 py-0.5 bg-black text-white dark:bg-zinc-900 rounded font-mono font-bold text-[11px] tracking-wider border border-zinc-700 shrink-0">
                                {{ $help->mitra->vehicle_plate_number }}
                            </span>
                        @endif
                    </div>
                @endif

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
        <div class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center px-4" wire:click.self="closeModal" data-confirm-modal>
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700 animate-scale-in" style="transform: translateY(-40px);">
                <div class="text-center">
                    <div class="w-16 h-16 bg-red-100 dark:bg-red-900/40 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Batalkan Pesanan?</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">Apakah Anda yakin ingin membatalkan pesanan ini? Tindakan ini tidak dapat dibatalkan.</p>
                    
                    <div class="flex gap-3">
                        <button type="button" wire:click="closeModal" class="flex-1 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition cursor-pointer">
                            Tidak
                        </button>
                        <button type="button" wire:click="cancelHelp" wire:loading.attr="disabled" class="flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold transition cursor-pointer disabled:opacity-50 flex items-center justify-center gap-1 shadow-sm">
                            <span wire:loading.remove wire:target="cancelHelp">Ya, Batalkan</span>
                            <span wire:loading wire:target="cancelHelp">Memproses...</span>
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
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pembekuan dana tahan & mediasi admin</p>
                        </div>
                    </div>
                    <button wire:click="closeDisputeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mb-4 p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                    Pengajuan komplain akan <strong>membekukan dana pembayaran (dana tahan dibekukan)</strong> secara seketika dan meneruskan bukti pengerjaan ke Admin Wilayah untuk mediasi.
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

    {{-- Modal Pilihan Pembatalan Customer (Ganti Mitra vs Tarik Pekerjaan) --}}
    @if($showCustomerCancelModal)
        @php
            if ($help->isPickup()) {
                $canWithdraw = $help->isPrePickup();
            } else {
                $isPartnerWorking = in_array($help->status, ['partner_arrived', 'in_progress', 'waiting_customer_confirmation', 'waiting_confirmation', 'konfirmasi_selesai', 'selesai', 'completed']);
                $canWithdraw = !$isPartnerWorking && in_array($help->status, ['taken', 'partner_on_the_way']);
            }
        @endphp
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-fade-in"
             wire:click.self="closeCustomerCancelModal">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-gray-100 dark:border-gray-700 max-h-[90vh] overflow-y-auto space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-gray-900 dark:text-white">Kendala / Pembatalan Pesanan</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Pilih opsi penanganan yang Anda butuhkan</p>
                        </div>
                    </div>
                    <button wire:click="closeCustomerCancelModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Tab Selection Cards: Ganti Mitra vs Tarik Pekerjaan --}}
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Pilih Jenis Tindakan:</label>
                    <div class="grid {{ $canWithdraw ? 'grid-cols-2' : 'grid-cols-1' }} gap-2 text-xs">
                        <label wire:click="$set('cancelOption', 'switch')"
                               class="p-3 rounded-xl border cursor-pointer transition flex flex-col justify-between {{ $cancelOption === 'switch' ? 'border-primary-500 bg-primary-50/60 dark:bg-primary-950/40 ring-2 ring-primary-500/20' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750' }}">
                            <div>
                                <div class="flex items-center gap-1.5 mb-1">
                                    <input type="radio" name="cancel_opt" {{ $cancelOption === 'switch' ? 'checked' : '' }} class="text-primary-600">
                                    <strong class="text-gray-900 dark:text-white font-bold text-xs">Ganti Mitra</strong>
                                </div>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-snug">Lepaskan mitra yang lambat & cari mitra baru via konfirmasi Admin.</p>
                            </div>
                            <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 mt-2">✓ Saldo Tetap Aman • 2x Konfirmasi</span>
                        </label>

                        @if($canWithdraw)
                            <label wire:click="$set('cancelOption', 'withdraw')"
                                   class="p-3 rounded-xl border cursor-pointer transition flex flex-col justify-between {{ $cancelOption === 'withdraw' ? 'border-rose-500 bg-rose-50/60 dark:bg-rose-950/40 ring-2 ring-rose-500/20' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750' }}">
                                <div>
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <input type="radio" name="cancel_opt" {{ $cancelOption === 'withdraw' ? 'checked' : '' }} class="text-rose-600">
                                        <strong class="text-gray-900 dark:text-white font-bold text-xs">Tarik Pekerjaan</strong>
                                    </div>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-snug">Batalkan total pesanan & minta 100% refund saldo.</p>
                                </div>
                                <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 mt-2">100% Full Refund • 2x Konfirmasi</span>
                            </label>
                        @endif
                    </div>

                    @if(!$canWithdraw)
                        <div class="p-2.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-xl text-[11px] text-amber-800 dark:text-amber-300 flex items-start gap-2">
                            <span class="text-xs mt-0.5">ℹ️</span>
                            <span>Opsi <strong>Tarik Pekerjaan / Refund Total</strong> disembunyikan karena mitra telah tiba atau sedang memulai pengerjaan. Untuk kendala pengerjaan, Anda dapat mengajukan <strong>Ganti Mitra</strong> atau menghubungi Bantuan CS.</span>
                        </div>
                    @endif
                </div>

                {{-- FORM CASE 1: GANTI MITRA --}}
                @if($cancelOption === 'switch')
                    <div class="p-3.5 bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/70 rounded-xl text-xs space-y-3">
                        <div class="text-blue-900 dark:text-blue-200 leading-relaxed text-[11px]">
                            <strong>Konfirmasi 2 Arah:</strong> Pengajuan ganti mitra memerlukan konfirmasi mitra & Admin. Sistem akan meminta mitra untuk segera mengonfirmasi dan menghubungi Anda di awal atas kendala yang dialami. Jika mitra menyetujui, atau <strong>bila tidak ada respon dan tidak dikonfirmasi</strong> dalam batas waktu, Admin dapat langsung memutuskan untuk mengembalikannya ke pool pencarian rekan jasa baru tanpa memotong saldo Anda.
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Alasan Ganti Mitra <span class="text-red-500">*</span></label>
                            <select wire:model="switchReason" class="w-full p-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white">
                                <option value="Mitra tidak bergerak / tidak kunjung datang">Mitra tidak kunjung bergerak / terlalu lama</option>
                                <option value="Mitra tidak merespons chat / telepon">Mitra tidak merespons chat / telepon</option>
                                <option value="Mitra meminta ganti mitra lain">Mitra meminta ganti mitra lain</option>
                                <option value="Lainnya">Lainnya (Tuliskan di catatan)</option>
                            </select>
                            @error('switchReason') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan Tambahan (Opsional)</label>
                            <textarea wire:model="switchNotes" rows="2" placeholder="Tuliskan keterangan tambahan..." class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white"></textarea>
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <button wire:click="closeCustomerCancelModal" type="button" class="flex-1 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold rounded-xl transition cursor-pointer">
                                Batal
                            </button>
                            <button wire:click="switchPartner" wire:loading.attr="disabled" type="button" class="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl transition shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                                <span wire:loading.remove wire:target="switchPartner">🔄 Ajukan Ganti Mitra</span>
                                <span wire:loading wire:target="switchPartner">Memproses...</span>
                            </button>
                        </div>
                    </div>

                {{-- FORM CASE 2: TARIK PEKERJAAN (BATAL TOTAL & REFUND) --}}
                @elseif($canWithdraw && $cancelOption === 'withdraw')
                    <div class="p-3.5 bg-rose-50/70 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/70 rounded-xl text-xs space-y-3">
                        <div class="text-rose-900 dark:text-rose-200 leading-relaxed text-[11px]">
                            <strong>Konfirmasi 2 Arah (Khusus Saat Perjalanan):</strong> Opsi ini hanya berlaku saat mitra masih dalam perjalanan menuju lokasi Anda. Pengajuan penarikan pekerjaan membutuhkan 2 konfirmasi (Mitra & Admin). Permintaan mendesak akan dikirimkan ke mitra dan diverifikasi oleh Admin Wilayah sebelum saldo 100% full refund dikembalikan ke akun Anda.
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">Alasan Penarikan <span class="text-red-500">*</span></label>
                            <select wire:model="customerCancelReason" class="w-full p-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white">
                                <option value="">-- Pilih Alasan --</option>
                                <option value="Perubahan Rencana Mendesak">Perubahan Rencana Mendesak</option>
                                <option value="Mitra Tidak Kunjung Datang / Mangkir">Mitra Tidak Kunjung Datang / Mangkir</option>
                                <option value="Sudah Selesai Sendiri / Tidak Butuh Lagi">Sudah Selesai Sendiri / Tidak Butuh Lagi</option>
                                <option value="Kesalahan Input Data Bantuan">Kesalahan Input Data Bantuan</option>
                                <option value="Lainnya">Lainnya (Tuliskan di catatan)</option>
                            </select>
                            @error('customerCancelReason') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Foto Bukti Kendala <span class="text-rose-500 font-bold">* Wajib</span>
                            </label>
                            @if ($customerCancelPhoto)
                                <div class="relative rounded-xl overflow-hidden border border-rose-300 dark:border-rose-700 bg-white dark:bg-gray-800 p-2 mb-2 flex items-center justify-between">
                                    <span class="text-xs text-gray-700 dark:text-gray-300 truncate max-w-[240px]">
                                        📸 {{ method_exists($customerCancelPhoto, 'getClientOriginalName') ? $customerCancelPhoto->getClientOriginalName() : 'Foto bukti terpilih' }}
                                    </span>
                                    <button type="button" wire:click="$set('customerCancelPhoto', null)" class="text-xs text-rose-600 hover:text-rose-700 font-bold px-2 py-1 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/50 cursor-pointer">
                                        Hapus
                                    </button>
                                </div>
                            @else
                                <input type="file" wire:model="customerCancelPhoto" accept="image/*" class="w-full p-2 text-xs bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl cursor-pointer text-gray-700 dark:text-gray-200 file:mr-2.5 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-rose-50 file:text-rose-700 dark:file:bg-rose-950 dark:file:text-rose-300">
                            @endif
                            <div wire:loading wire:target="customerCancelPhoto" class="text-[11px] text-blue-600 font-medium mt-1 flex items-center gap-1.5">
                                <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                Mengunggah foto bukti...
                            </div>
                            @error('customerCancelPhoto') <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-300 mb-1">
                                Catatan Tambahan <span class="text-rose-500 font-bold">* Wajib</span>
                            </label>
                            <textarea wire:model="customerCancelNotes" rows="2" placeholder="Jelaskan alasan penarikan kepada mitra & admin..." class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition"></textarea>
                            @error('customerCancelNotes') <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <button wire:click="closeCustomerCancelModal" type="button" class="flex-1 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold rounded-xl transition cursor-pointer">
                                Batal
                            </button>
                            <button wire:click="submitCustomerCancel" wire:loading.attr="disabled" wire:target="submitCustomerCancel, customerCancelPhoto" type="button" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                                <span wire:loading.remove wire:target="submitCustomerCancel">🛑 Ajukan Tarik Pekerjaan</span>
                                <span wire:loading wire:target="submitCustomerCancel">Mengirim...</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Modal Konfirmasi Penyelesaian Tahap 6 (Dianggap Sudah Sampai) --}}
    @if($showStage6ConfirmModal)
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-fade-in"
             wire:click.self="$set('showStage6ConfirmModal', false)">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl shrink-0">
                        📍
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-gray-900 dark:text-white">Pengantaran Dianggap Sampai</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tahap Akhir Perjalanan Antar & Jemput</p>
                    </div>
                </div>

                <div class="p-3 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 rounded-xl text-xs text-blue-900 dark:text-blue-200 leading-relaxed">
                    Pengantaran telah menempuh sebagian besar perjalanan (> 5 KM) atau telah mendekati titik tujuan. Oleh karena itu, pesanan <strong>dianggap telah sampai di tujuan</strong> dan ongkos antar (<strong>Rp {{ number_format($help->service_fee > 0 ? $help->service_fee : $help->amount, 0, ',', '.') }}</strong>) dialokasikan penuh untuk Rekan Jasa.
                </div>

                <p class="text-xs text-gray-600 dark:text-gray-300">
                    Silakan konfirmasi penyelesaian agar ongkos antar diteruskan ke saldo Rekan Jasa. Sisa dana belanja (jika ada) akan dikembalikan ke saldo Anda.
                </p>

                <div class="flex items-center gap-2 pt-2">
                    <button type="button" wire:click="$set('showStage6ConfirmModal', false)" class="flex-1 py-2.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold rounded-xl text-xs transition cursor-pointer">
                        Kembali
                    </button>
                    <button type="button" wire:click="confirmStage6Completion" wire:loading.attr="disabled" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                        <span wire:loading.remove wire:target="confirmStage6Completion">✓ Konfirmasi Sampai & Lepaskan Ongkos</span>
                        <span wire:loading wire:target="confirmStage6Completion">Memproses...</span>
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
            let mapResizeTimer = null;

            function safeInvalidateSize(mapObj, containerId = 'tracking-map') {
                if (!mapObj) return;
                try {
                    const el = typeof containerId === 'string' ? document.getElementById(containerId) : containerId;
                    if (el && document.body.contains(el) && mapObj._mapPane && mapObj._loaded && typeof mapObj.invalidateSize === 'function') {
                        mapObj.invalidateSize();
                    }
                } catch (e) {
                    // Suppress Leaflet detached element errors
                }
            }

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
            mapResizeTimer = setTimeout(() => {
                safeInvalidateSize(map, 'tracking-map');
                console.log('🔄 Map size recalculated');
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
        // Expose current help id so polling and WebSocket subscription can start immediately
        window.currentHelpId = '{{ $help->id }}';

        (function() {
            let pollingInterval = null;
            let echoChannel = null;
            const POLL_MS = 25000; // Relaxed 25s fallback poll (WebSockets provide real-time updates)

            function applyTrackingData(data) {
                if (!data) return;

                // Update Alpine's trackingData so UI and any bindings reflect latest coords
                try {
                    if (window.Alpine) {
                        const alpineEl = document.querySelector('[x-data]');
                        if (alpineEl) {
                            const alpine = Alpine.$data(alpineEl);
                            if (alpine && alpine.trackingData) {
                                if (data.partnerLat != null) alpine.trackingData.partnerLat = data.partnerLat;
                                if (data.partnerLng != null) alpine.trackingData.partnerLng = data.partnerLng;
                                if (data.customerLat != null) alpine.trackingData.customerLat = data.customerLat;
                                if (data.customerLng != null) alpine.trackingData.customerLng = data.customerLng;
                                if (data.partnerName != null) alpine.trackingData.partnerName = data.partnerName;
                            }
                        }
                    }
                } catch (err) {
                    console.warn('Failed updating Alpine data', err);
                }

                // Call existing global function used by map code to update markers & route
                if (window.updateMapFromTracking) {
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

                        const estMinutes = Math.max(1, Math.ceil((distKm / 30) * 60));
                        const hours = Math.floor(estMinutes / 60);
                        const minutes = estMinutes % 60;
                        const etaText = hours > 0 ? `${hours} jam ${minutes} menit` : `${minutes} menit`;

                        const summaryD = document.getElementById('summary-distance');
                        const summaryE = document.getElementById('summary-eta');
                        if (summaryD) summaryD.textContent = distText;
                        if (summaryE) summaryE.textContent = etaText;

                        const modalD = document.getElementById('distance-text');
                        const modalE = document.getElementById('eta-time');
                        if (modalD) modalD.textContent = distText;
                        if (modalE) modalE.textContent = etaText;
                    }
                } catch (err) {
                    console.warn('Failed calculating distance/ETA fallback', err);
                }
            }

            function subscribeToEcho(helpId) {
                if (!helpId || typeof window.Echo === 'undefined' || typeof window.Echo.private !== 'function') return;
                try {
                    if (echoChannel) {
                        window.Echo.leave('chat.help.' + helpId);
                    }
                    echoChannel = window.Echo.private('chat.help.' + helpId)
                        .listen('PartnerLocationUpdated', (data) => {
                            applyTrackingData(data);
                        })
                        .listen('.PartnerLocationUpdated', (data) => {
                            applyTrackingData(data);
                        });
                } catch (e) {
                    console.warn('Echo tracking subscription error:', e);
                }
            }

            function unsubscribeEcho(helpId) {
                if (!helpId || typeof window.Echo === 'undefined' || typeof window.Echo.leave !== 'function') return;
                try {
                    window.Echo.leave('chat.help.' + helpId);
                    echoChannel = null;
                } catch (e) {}
            }

            function startPolling(helpId) {
                if (!helpId) return;
                subscribeToEcho(helpId);
                if (pollingInterval) return; // already running
                fetchAndUpdate(helpId);
                pollingInterval = setInterval(() => fetchAndUpdate(helpId), POLL_MS);
            }

            function stopPolling() {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                }
            }

            async function fetchAndUpdate(helpId) {
                try {
                    const resp = await fetch(`/customer/helps/${helpId}/tracking`, {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!resp.ok) {
                        return;
                    }
                    const data = await resp.json();
                    applyTrackingData(data);
                } catch (err) {
                    // silent fallback
                }
            }

            // Start polling & websocket immediately for the current help id
            try {
                const initialId = window.currentHelpId || null;
                if (initialId) startPolling(initialId);
            } catch (e) {
                console.error('Error starting initial tracking:', e);
            }

            // Hook into Livewire modal events
            document.addEventListener('livewire:init', () => {
                Livewire.on('mapModalOpened', (helpId) => {
                    const idToUse = helpId || window.currentHelpId;
                    startPolling(idToUse);
                });
            });

            // Stop polling and cleanup map on Livewire navigation and page unload
            document.addEventListener('livewire:navigating', () => {
                if (mapResizeTimer) {
                    clearTimeout(mapResizeTimer);
                    mapResizeTimer = null;
                }
                unsubscribeEcho(window.currentHelpId);
                stopPolling();
                if (map) {
                    try { map.remove(); } catch(e){}
                    map = null;
                }
                const mapEl = document.getElementById('tracking-map');
                if (mapEl && mapEl._leaflet_id) {
                    mapEl._leaflet_id = null;
                }
            });
            window.addEventListener('beforeunload', () => {
                unsubscribeEcho(window.currentHelpId);
                stopPolling();
            });
        })();
    </script>

    {{-- Toast notification for copy & Search Countdown Timer --}}
    <script>
        function customerDetailCountdownTimer(isoExpiry) {
            return {
                isoExpiry: isoExpiry,
                timeString: '--:--:--',
                isExpired: false,
                hours: '00',
                minutes: '00',
                seconds: '00',
                timer: null,
                hasTriggeredExpire: false,
                init() {
                    if (!this.isoExpiry) {
                        this.timeString = 'Batas sistem';
                        return;
                    }
                    this.update();
                    this.timer = setInterval(() => this.update(), 1000);
                },
                update() {
                    const target = new Date(this.isoExpiry).getTime();
                    const now = new Date().getTime();
                    const diff = target - now;

                    if (diff <= 0) {
                        this.isExpired = true;
                        this.timeString = '00:00:00 (Waktu Habis)';
                        this.hours = '00';
                        this.minutes = '00';
                        this.seconds = '00';
                        if (this.timer) {
                            clearInterval(this.timer);
                            this.timer = null;
                        }
                        if (!this.hasTriggeredExpire) {
                            this.hasTriggeredExpire = true;
                            if (typeof this.$wire !== 'undefined' && typeof this.$wire.loadHelp === 'function') {
                                this.$wire.loadHelp();
                            } else if (typeof Livewire !== 'undefined') {
                                Livewire.dispatch('refreshHelp');
                            }
                        }
                        return;
                    }

                    const totalSeconds = Math.max(0, Math.floor(diff / 1000));
                    const h = Math.floor(totalSeconds / 3600);
                    const m = Math.floor((totalSeconds % 3600) / 60);
                    const s = totalSeconds % 60;

                    const pad = (n) => String(n).padStart(2, '0');
                    this.hours = pad(h);
                    this.minutes = pad(m);
                    this.seconds = pad(s);
                    this.timeString = `${this.hours}:${this.minutes}:${this.seconds}`;
                }
            };
        }

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