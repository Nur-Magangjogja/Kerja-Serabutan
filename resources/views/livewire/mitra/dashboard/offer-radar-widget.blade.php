<div @if(($onlineState?->matching_status ?? '') === 'searching') wire:poll.6s.visible @elseif(($onlineState?->matching_status ?? '') === 'offer_pending') wire:poll.3s.visible @endif
     x-data="{
         isGettingLocation: false,
         status: '{{ $onlineState?->matching_status ?? 'offline' }}',
         heartbeatTimer: null,
         heartbeatBackoff: 25000,
         
         triggerAction(actionName) {
             const invokeAction = (lat = null, lng = null) => {
                 if (actionName === 'goOnline') {
                     $wire.goOnline(lat, lng);
                 } else if (actionName === 'startSearching') {
                     $wire.startSearching(lat, lng);
                 } else if (actionName === 'stopSearching') {
                     $wire.stopSearching();
                 } else if (actionName === 'goOffline') {
                     $wire.goOffline();
                 }
             };

             if (navigator.geolocation) {
                 this.isGettingLocation = true;
                 navigator.geolocation.getCurrentPosition(
                     (position) => {
                         this.isGettingLocation = false;
                         invokeAction(position.coords.latitude, position.coords.longitude);
                     },
                     (error) => {
                         this.isGettingLocation = false;
                         console.warn('Geolocation error:', error.message);
                         invokeAction();
                     },
                     { enableHighAccuracy: true, timeout: 5000, maximumAge: 60000 }
                 );
             } else {
                 invokeAction();
             }
         },

         sendHeartbeat(lat = null, lng = null) {
             if (this.status === 'offline') return;
             if (document.hidden && Math.random() > 0.3) return; // Save resources when tab in background

             const token = document.querySelector('meta[name=\'csrf-token\']')?.getAttribute('content');
             fetch('{{ route('mitra.heartbeat') }}', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                     'Accept': 'application/json',
                     'X-CSRF-TOKEN': token || ''
                 },
                 body: JSON.stringify({ latitude: lat, longitude: lng })
             })
             .then(res => res.json())
             .then(data => {
                 this.heartbeatBackoff = 25000;
                 if (data.matching_status && data.matching_status !== this.status) {
                     this.status = data.matching_status;
                     $wire.$refresh();
                 }
             })
             .catch(err => {
                 this.heartbeatBackoff = Math.min(60000, this.heartbeatBackoff * 1.5);
             });
         },

         scheduleHeartbeat() {
             if (this.heartbeatTimer) clearTimeout(this.heartbeatTimer);
             if (this.status !== 'offline') {
                 if (navigator.geolocation) {
                     navigator.geolocation.getCurrentPosition(
                         (pos) => { this.sendHeartbeat(pos.coords.latitude, pos.coords.longitude); },
                         () => { this.sendHeartbeat(); },
                         { timeout: 5000, maximumAge: 60000 }
                     );
                 } else {
                     this.sendHeartbeat();
                 }
             }
             this.heartbeatTimer = setTimeout(() => this.scheduleHeartbeat(), this.heartbeatBackoff);
         },

         init() {
             this.scheduleHeartbeat();
             document.addEventListener('visibilitychange', () => {
                 if (!document.hidden) this.scheduleHeartbeat();
             });
         }
     }">

    <!-- Card Status Mitra Online / Offline / Searching / Busy -->
    <div class="px-5 mt-3.5 sm:mt-4 relative z-10">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700/80">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 transition-colors
                        @if(($onlineState?->matching_status ?? 'offline') === 'searching')
                            bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 ring-2 ring-emerald-500/20
                        @elseif(($onlineState?->matching_status ?? 'offline') === 'online')
                            bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400
                        @elseif(($onlineState?->matching_status ?? 'offline') === 'offer_pending')
                            bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 ring-2 ring-amber-500/30 animate-pulse
                        @elseif(($onlineState?->matching_status ?? 'offline') === 'busy')
                            bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400
                        @else
                            bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500
                        @endif">
                        @if(($onlineState?->matching_status ?? 'offline') === 'searching')
                            <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        @elseif(($onlineState?->matching_status ?? 'offline') === 'offer_pending')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @elseif(($onlineState?->matching_status ?? 'offline') === 'busy')
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white">
                                @if(($onlineState?->matching_status ?? 'offline') === 'searching')
                                    Mencari Order
                                @elseif(($onlineState?->matching_status ?? 'offline') === 'online')
                                    Online (Standby)
                                @elseif(($onlineState?->matching_status ?? 'offline') === 'offer_pending')
                                    Tawaran Masuk!
                                @elseif(($onlineState?->matching_status ?? 'offline') === 'busy')
                                    Sedang Bertugas
                                @else
                                    Offline
                                @endif
                            </h3>
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] font-bold
                                @if(($onlineState?->matching_status ?? 'offline') === 'searching')
                                    bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300
                                @elseif(($onlineState?->matching_status ?? 'offline') === 'online')
                                    bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300
                                @elseif(($onlineState?->matching_status ?? 'offline') === 'offer_pending')
                                    bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 animate-pulse
                                @elseif(($onlineState?->matching_status ?? 'offline') === 'busy')
                                    bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300
                                @else
                                    bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400
                                @endif">
                                <span class="w-1.5 h-1.5 rounded-full
                                    @if(($onlineState?->matching_status ?? 'offline') === 'searching')
                                        bg-emerald-500 animate-ping
                                    @elseif(($onlineState?->matching_status ?? 'offline') === 'online')
                                        bg-blue-500
                                    @elseif(($onlineState?->matching_status ?? 'offline') === 'offer_pending')
                                        bg-amber-500 animate-ping
                                    @elseif(($onlineState?->matching_status ?? 'offline') === 'busy')
                                        bg-indigo-500
                                    @else
                                        bg-gray-400
                                    @endif"></span>
                                {{ strtoupper($onlineState?->matching_status ?? 'OFFLINE') }}
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight mt-0.5">
                            @if(($onlineState?->matching_status ?? 'offline') === 'searching')
                                Radar aktif mencari order di sekitar Anda
                            @elseif(($onlineState?->matching_status ?? 'offline') === 'online')
                                Siap menerima pekerjaan. Klik "Cari Order" untuk mengaktifkan radar.
                            @elseif(($onlineState?->matching_status ?? 'offline') === 'offer_pending')
                                Ada tawaran order khusus untuk Anda!
                            @elseif(($onlineState?->matching_status ?? 'offline') === 'busy')
                                Anda sedang memiliki tugas aktif yang berjalan
                            @else
                                Aktifkan status online untuk mulai menerima bantuan
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 flex-shrink-0">
                    @if(($onlineState?->matching_status ?? 'offline') === 'searching')
                        <button wire:click="stopSearching"
                                wire:loading.attr="disabled"
                                class="px-3 py-1.5 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 rounded-xl text-xs font-bold hover:bg-amber-100 transition cursor-pointer">
                            Jeda Cari
                        </button>
                        <button wire:click="goOffline"
                                wire:loading.attr="disabled"
                                class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-bold hover:bg-gray-200 transition cursor-pointer">
                            Offline
                        </button>
                    @elseif(($onlineState?->matching_status ?? 'offline') === 'online')
                        <button @click="triggerAction('startSearching')"
                                :disabled="isGettingLocation"
                                class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1">
                            <span x-show="!isGettingLocation">Cari Order</span>
                            <span x-show="isGettingLocation" x-cloak>GPS...</span>
                        </button>
                        <button wire:click="goOffline"
                                wire:loading.attr="disabled"
                                class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-bold hover:bg-gray-200 transition cursor-pointer">
                            Offline
                        </button>
                    @elseif(($onlineState?->matching_status ?? 'offline') === 'offline')
                        <button @click="triggerAction('goOnline')"
                                :disabled="isGettingLocation"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5">
                            <span x-show="!isGettingLocation">Aktifkan Online</span>
                            <span x-show="isGettingLocation" x-cloak>Memuat GPS...</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Indikator Gamifikasi Status Antrean & Waktu Tunggu --}}
            @if(($onlineState?->matching_status ?? 'offline') === 'searching')
                @php
                    $searchingSince = $onlineState->searching_since ?? $onlineState->last_seen_at ?? now();
                    $totalSeconds   = (int) floor(max(0, $searchingSince->diffInSeconds(now())));
                    $waitMinutes    = (int) floor($totalSeconds / 60);

                    if ($totalSeconds < 60) {
                        $formattedWaitTime = (int) $totalSeconds . ' Second';
                    } elseif ($waitMinutes < 60) {
                        $formattedWaitTime = (int) $waitMinutes . ' Minute';
                    } else {
                        $formattedWaitTime = '1 Hour';
                    }

                    $boostDays = \App\Models\AppSetting::getNewbieBoostDays();
                    $threshold = \App\Models\AppSetting::getNewbieOrderThreshold();
                    $isNewbie  = \App\Models\AppSetting::isNewbieBoostEnabled()
                        && auth()->user()
                        && (
                            (auth()->user()->created_at && auth()->user()->created_at->diffInDays(now()) <= $boostDays)
                            && \App\Models\Help::where('mitra_id', auth()->id())->where('status', 'selesai')->count() < $threshold
                        );
                @endphp
                <div class="mt-3 pt-2.5 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between text-xs flex-wrap gap-2">
                    <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                        <svg class="w-3.5 h-3.5 text-emerald-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Menunggu: <strong>{{ $formattedWaitTime }}</strong></span>
                        @if($isNewbie)
                            <span class="bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 text-[10px] font-bold px-2 py-0.5 rounded-md border border-indigo-200 dark:border-indigo-800 flex items-center gap-1">
                                🚀 Newbie Boost Aktif
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-1">
                        @if($waitMinutes >= 45)
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 flex items-center gap-1">
                                🔥 Prioritas Maksimal
                            </span>
                        @elseif($waitMinutes >= 15)
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 flex items-center gap-1">
                                ⚡ Prioritas Meningkat
                            </span>
                        @else
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 flex items-center gap-1">
                                🟢 Radar Aktif
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Sequential Matching Active Offer Card --}}
    @if(isset($activeOffer) && $activeOffer && $activeOffer->help)
        @php
            $secondsRemaining = (int) max(0, $activeOffer->expires_at ? now()->diffInSeconds($activeOffer->expires_at, false) : \App\Models\AppSetting::getOfferTimeoutSeconds());
            $initialTimeout   = (int) \App\Models\AppSetting::getOfferTimeoutSeconds();

            $mitraLat = $onlineState?->latitude ?? auth()->user()->latitude;
            $mitraLng = $onlineState?->longitude ?? auth()->user()->longitude;
            $helpLat  = $activeOffer->help->latitude;
            $helpLng  = $activeOffer->help->longitude;

            $formattedDistance = null;
            $estimatedMinutes  = null;

            if ($mitraLat && $mitraLng && $helpLat && $helpLng) {
                $distanceMeters = app(\App\Services\LocationTrackingService::class)->calculateDistance(
                    (float) $mitraLat, (float) $mitraLng,
                    (float) $helpLat, (float) $helpLng
                );
                $distanceKm = $distanceMeters / 1000;
                if ($distanceKm < 1) {
                    $formattedDistance = round($distanceMeters) . ' m';
                } else {
                    $formattedDistance = round($distanceKm, 1) . ' km';
                }
                $estimatedMinutes = max(1, (int) round(($distanceKm / 30) * 60));
            }
        @endphp
        <div class="px-5 mt-4 relative z-20"
             wire:key="active-offer-card-{{ $activeOffer->id }}"
             x-data="{
                 expiresAt: {{ $activeOffer->expires_at ? $activeOffer->expires_at->getTimestamp() * 1000 : 'Date.now() + 45000' }},
                 totalTime: Math.max(1, parseInt('{{ (int) $initialTimeout }}', 10)),
                 timeLeft: Math.max(0, parseInt('{{ (int) $secondsRemaining }}', 10)),
                 timer: null,
                 syncCountdown() {
                     const now = Date.now();
                     this.timeLeft = Math.max(0, Math.ceil((this.expiresAt - now) / 1000));
                     if (this.timeLeft <= 0) {
                         if (this.timer) clearInterval(this.timer);
                         $wire.handleExpiry({{ $activeOffer->id }});
                     }
                 },
                 init() {
                     if (this.timer) clearInterval(this.timer);
                     this.syncCountdown();
                     this.timer = setInterval(() => this.syncCountdown(), 1000);
                 },
                 destroy() {
                     if (this.timer) clearInterval(this.timer);
                 }
             }">
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 text-gray-900 dark:text-white shadow-xl dark:shadow-2xl border border-gray-200 dark:border-slate-800 relative overflow-hidden ring-1 ring-gray-900/5 dark:ring-white/10 transition-colors">
                <!-- Ambient Glow Effect -->
                <div class="absolute -right-10 -top-10 w-36 h-36 bg-blue-500/10 dark:bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-10 -bottom-10 w-36 h-36 bg-emerald-500/10 dark:bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Header: Badge & Countdown -->
                <div class="flex items-center justify-between gap-3 mb-2 relative z-10">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 text-[10px] uppercase tracking-widest font-black bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30 px-2.5 py-1 rounded-full shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-ping"></span>
                            🔒 Tawaran Eksklusif (Khusus Anda)
                        </span>
                    </div>

                    <!-- Countdown Timer Pill -->
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-xl font-mono text-xs sm:text-sm font-black shadow-xs border transition-colors"
                         :class="timeLeft <= 10 ? 'bg-rose-100 border-rose-400 text-rose-800 dark:bg-rose-950 dark:border-rose-700 dark:text-rose-200 animate-pulse' : 'bg-amber-50 border-amber-300 text-amber-800 dark:bg-amber-950 dark:border-amber-800 dark:text-amber-200'">
                        <svg class="w-3.5 h-3.5" :class="timeLeft <= 10 ? 'text-rose-600 animate-spin' : 'text-amber-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span x-text="Math.floor(timeLeft) + ' Detik'"></span>
                    </div>
                </div>

                <!-- Countdown Progress Bar -->
                <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5 overflow-hidden mb-3 relative z-10">
                    <div class="h-full rounded-full transition-all duration-1000 ease-linear"
                         :class="timeLeft <= 10 ? 'bg-rose-500' : 'bg-emerald-500'"
                         :style="'width: ' + Math.max(0, Math.min(100, (timeLeft / totalTime) * 100)) + '%'">
                    </div>
                </div>

                <!-- Exclusive Allocation Explainer Banner -->
                <div class="mb-3 px-3 py-1.5 rounded-xl bg-blue-50/70 dark:bg-indigo-950/40 border border-blue-200/60 dark:border-indigo-800/40 text-[11px] text-blue-800 dark:text-indigo-200 flex items-center gap-1.5 relative z-10">
                    <svg class="w-4 h-4 text-blue-600 dark:text-indigo-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                    <span>Pesanan ini dialokasikan <strong>khusus untuk Anda</strong>. Mitra lain belum dapat mengambil sebelum waktu Anda berakhir.</span>
                </div>

                <!-- Judul Bantuan -->
                <div class="relative z-10">
                    <h3 class="text-base sm:text-lg font-extrabold text-gray-900 dark:text-white tracking-tight leading-snug">
                        {{ $activeOffer->help->title }}
                    </h3>
                </div>

                <!-- Detail Pekerjaan / Deskripsi -->
                @if(!empty($activeOffer->help->description))
                    <div class="mt-2.5 p-3 rounded-xl bg-gray-50 dark:bg-white/[0.04] border border-gray-200/80 dark:border-white/[0.07] text-xs text-gray-700 dark:text-slate-300 relative z-10">
                        <div class="flex items-center gap-1 text-[11px] font-bold text-gray-500 dark:text-slate-400 mb-1">
                            <svg class="w-3.5 h-3.5 text-blue-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Detail Pekerjaan:</span>
                        </div>
                        <p class="line-clamp-2 leading-relaxed">{{ $activeOffer->help->description }}</p>
                    </div>
                @endif

                <!-- Meta Cards Grid: Lokasi, Jarak, & Customer -->
                <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2 relative z-10">
                    <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-white/[0.04] border border-gray-200/60 dark:border-white/[0.06]">
                        <div class="text-[10px] text-gray-500 dark:text-slate-400">Jarak Tempuh</div>
                        <div class="text-xs font-bold text-gray-900 dark:text-white mt-0.5 flex items-center gap-1">
                            <span>📍 {{ $formattedDistance ?? ($activeOffer->help->city?->name ?? 'Terjangkau') }}</span>
                            @if($estimatedMinutes)
                                <span class="text-[10px] font-normal text-emerald-600 dark:text-emerald-400">(~{{ $estimatedMinutes }}m)</span>
                            @endif
                        </div>
                    </div>

                    <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-white/[0.04] border border-gray-200/60 dark:border-white/[0.06]">
                        <div class="text-[10px] text-gray-500 dark:text-slate-400">Customer</div>
                        <div class="text-xs font-bold text-gray-900 dark:text-white mt-0.5 truncate">
                            👤 {{ $activeOffer->help->user->name ?? 'Customer' }}
                        </div>
                    </div>

                    <div class="col-span-2 sm:col-span-1 p-2.5 rounded-xl bg-gray-50 dark:bg-white/[0.04] border border-gray-200/60 dark:border-white/[0.06]">
                        <div class="text-[10px] text-gray-500 dark:text-slate-400">Waktu Permintaan</div>
                        <div class="text-xs font-bold text-gray-900 dark:text-white mt-0.5">
                            ⚡ Segera (Sekarang)
                        </div>
                    </div>
                </div>

                <!-- Honor / Tarif Pekerjaan -->
                <div class="mt-4 p-3 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 flex items-center justify-between relative z-10">
                    <div>
                        <span class="text-[10px] font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider block">Pendapatan Bersih Anda</span>
                        <span class="text-lg sm:text-xl font-black text-emerald-700 dark:text-white">Rp {{ number_format($activeOffer->help->amount, 0, ',', '.') }}</span>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-500/20 px-2.5 py-1 rounded-lg border border-emerald-200 dark:border-emerald-500/30 flex items-center gap-1">
                        <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Dana Escrow Dijamin
                    </span>
                </div>

                <!-- Action Buttons: TERIMA & TOLAK -->
                <div class="mt-4 grid grid-cols-2 gap-3 relative z-10">
                    <button wire:click="rejectOffer({{ $activeOffer->id }})"
                            wire:loading.attr="disabled"
                            :disabled="timeLeft <= 0"
                            :class="timeLeft <= 0 ? 'opacity-50 cursor-not-allowed' : 'active:scale-98 cursor-pointer'"
                            class="w-full py-3 bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 text-gray-700 hover:text-gray-900 dark:text-slate-300 dark:hover:text-white border border-gray-200 dark:border-white/10 rounded-xl text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Lewati Tawaran</span>
                    </button>

                    <button wire:click="acceptOffer({{ $activeOffer->id }})"
                            wire:loading.attr="disabled"
                            :disabled="timeLeft <= 0"
                            :class="timeLeft <= 0 ? 'opacity-50 cursor-not-allowed' : 'active:scale-98 cursor-pointer hover:from-emerald-500 hover:to-teal-500 shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40'"
                            class="w-full py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl text-xs font-extrabold transition flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Terima Pekerjaan</span>
                    </button>
                </div>

                <!-- Reject Consequence Microcopy -->
                <div class="mt-3 text-center relative z-10">
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-tight">
                        💡 <strong>Jika dilewati:</strong> Tawaran akan diteruskan ke kandidat berikutnya atau dibuka ke pool bila tidak ada kandidat lain. Anda tetap dalam mode mencari (Maks. 2x tolak sebelum standby).
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
