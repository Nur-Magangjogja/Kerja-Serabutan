<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100"
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
    </div>

    {{-- Header Section --}}
    <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
        <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

        <div class="relative z-10 max-w-md mx-auto">
            <div class="relative flex items-center justify-center min-h-[40px] text-white">
                <div class="text-center w-full min-w-0 px-12">
                    <h1 class="text-base font-bold truncate">Detail Pesanan</h1>
                    <p class="text-xs text-white/90 truncate mt-0.5">Informasi lengkap pesanan Anda</p>
                </div>

                <div class="absolute right-0 top-1/2 -translate-y-1/2 z-20 flex items-center justify-end">
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
    <div class="px-5 pt-5 pb-20 max-w-md mx-auto">
        {{-- GPS Tracker - Auto tracking untuk status aktif --}}
        @if (in_array($help->status, ['taken', 'partner_on_the_way', 'partner_arrived']))
            {{-- <div class="mb-3">
                <livewire:mitra.gps.tracker :helpId="$help->id" :key="'gps-tracker-'.$help->id" />
            </div> --}}
        @endif

        {{-- Modal: Customer confirmed rejection (cancel_rejected) --}}
        <div id="cancel-confirmed-modal"
            class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center px-4"
            style="display:none;">
            <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700" role="dialog" aria-modal="true">
                <div class="text-center">
                    <div class="w-16 h-16 bg-yellow-50 dark:bg-yellow-900/40 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Penolakan Dikonfirmasi</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">Customer telah mengkonfirmasi bahwa pembatalan Anda ditolak.
                        Silakan lanjutkan pekerjaan.</p>

                    <div class="flex gap-3">
                        <button id="cancel-confirmed-close"
                            class="flex-1 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-lg font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">Tutup</button>
                        <a href="{{ route('mitra.helps.all') }}" id="cancel-confirmed-go"
                            class="flex-1 inline-flex items-center justify-center py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">Kembali
                            ke Bantuan</a>
                    </div>
                </div>
            </div>
        </div>

        @if (session('message'))
            <div
                class="mb-4 p-3 bg-green-50 dark:bg-green-950/40 border border-green-100 dark:border-green-800 rounded-lg text-green-700 dark:text-green-300 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('message') }}
            </div>
        @endif

        {{-- Service Info --}}
        <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3">
            {{-- Header Row: Photo & Title --}}
            <div class="flex items-center gap-3.5 mb-3">
                <div class="flex-1 min-w-0">
                    <h2 class="font-bold text-base text-gray-900 dark:text-white truncate leading-snug">{{ $help->title }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium truncate mt-0.5">Tugas yang sedang dikerjakan</p>
                </div>
            </div>

            {{-- Earnings Display (Clean & Transparent Breakdown) --}}
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/40 dark:to-teal-950/40 border border-emerald-200/80 dark:border-emerald-800/60 rounded-2xl p-4 mb-3 shadow-2xs space-y-2.5">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-emerald-900 dark:text-emerald-200 block">Upah Bersih Mitra:</span>
                        <span class="text-[11px] text-emerald-700/80 dark:text-emerald-300/80">100% Penuh Tanpa Potongan Komisi</span>
                    </div>
                    <div class="text-xl sm:text-2xl font-black text-emerald-700 dark:text-emerald-300">
                        Rp {{ number_format($help->getNetEarning(), 0, ',', '.') }}
                    </div>
                </div>


            </div>

            {{-- Order ID Row (Full Width & Overflow-Safe) --}}
            <div class="pt-2.5 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between gap-2">
                <div class="flex items-center gap-1.5 min-w-0 flex-1">
                    <span class="text-xs text-gray-400 dark:text-gray-400 font-semibold shrink-0">ID:</span>
                    <span class="font-mono font-bold text-xs sm:text-sm text-gray-800 dark:text-gray-200 truncate" title="{{ $help->order_id }}">{{ $help->order_id }}</span>
                </div>
                <button wire:click="copyOrderId" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 text-xs font-bold flex items-center gap-1 shrink-0 px-2.5 py-1 bg-primary-50 dark:bg-primary-950/60 hover:bg-primary-100 dark:hover:bg-primary-900/50 rounded-lg transition cursor-pointer border border-primary-100/80 dark:border-primary-900/40">
                    <span>Salin</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Progress Section (Revisi 3 Dynamic Multi-Stage Stepper) --}}
        <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Progres & Tahapan Layanan</span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5 mt-0.5">
                        <span>{{ $help->progress_icon }}</span>
                        <span>{{ $help->progress_summary }}</span>
                    </h3>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-extrabold bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800">
                        {{ $help->multi_stage_progress_percentage }}%
                    </span>
                </div>
            </div>

            <!-- Dynamic Stepper -->
            @php
                $multiSteps = $help->multi_stage_steps ?? [];
                $stepCount = is_countable($multiSteps) ? count($multiSteps) : 0;
                $isDone = in_array($help->status, ['selesai', 'completed']);
                
                // Cari index aktif
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
                    <div class="h-full bg-blue-600 dark:bg-blue-500 transition-all duration-700 rounded-full"
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
        </div>

        {{-- Live Dynamic Travel ETA & Navigation Card (Konsep 2) --}}
        @if(in_array($help->status, ['taken', 'partner_on_the_way', 'partner_arrived']))
            @php
                $travelProgress = app(\App\Services\HelpScheduleService::class)->getLiveTravelProgress($help);
            @endphp
            <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-blue-950 text-white p-4 rounded-2xl shadow-md border border-indigo-800/60 mb-3 space-y-3">
                <div class="flex items-center justify-between border-b border-indigo-800/60 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="text-base">{{ $help->isPickup() ? '📦' : '🛵' }}</span>
                        <div>
                            <h4 class="text-xs font-bold text-indigo-200">
                                {{ $travelProgress['is_arrived'] ? 'Telah Tiba di Lokasi' : 'Perjalanan Menuju Lokasi Sasaran' }}
                            </h4>
                            <p class="text-[10px] text-indigo-300/80">{{ $travelProgress['target_label'] }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $travelProgress['is_arrived'] ? 'bg-emerald-900/80 text-emerald-300 border border-emerald-700/60' : 'bg-blue-900/80 text-blue-300 border border-blue-700/60 animate-pulse' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $travelProgress['is_arrived'] ? 'bg-emerald-400' : 'bg-blue-400' }}"></span>
                        {{ $travelProgress['is_arrived'] ? 'Tiba di Lokasi' : 'Live ETA Dinamis' }}
                    </span>
                </div>

                @if($travelProgress['is_arrived'])
                    <div class="bg-emerald-950/60 border border-emerald-800/80 rounded-xl p-3 text-center">
                        <p class="text-xs font-bold text-emerald-300 flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Anda telah sampai di lokasi tujuan
                        </p>
                        <p class="text-[11px] text-emerald-200/80 mt-0.5">Segera temui pemesan bantuan untuk mulai melaksanakan tugas.</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-2 text-center">
                        <div class="bg-white/10 rounded-xl p-2.5">
                            <span class="text-[10px] text-indigo-300 block font-medium">Jarak ke Tujuan</span>
                            <span class="text-sm font-extrabold text-white font-mono block mt-0.5">
                                {{ $travelProgress['formatted_distance'] }}
                            </span>
                        </div>
                        <div class="bg-white/10 rounded-xl p-2.5">
                            <span class="text-[10px] text-indigo-300 block font-medium">Estimasi Waktu Tiba (ETA)</span>
                            <span class="text-sm font-extrabold text-emerald-300 font-mono block mt-0.5">
                                {{ $travelProgress['formatted_eta'] }}
                            </span>
                        </div>
                    </div>

                    @if($travelProgress['target_lat'] && $travelProgress['target_lng'])
                        <div class="pt-1 flex items-center gap-2">
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $travelProgress['target_lat'] }},{{ $travelProgress['target_lng'] }}" 
                               target="_blank" rel="noopener noreferrer"
                               class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                <span>Buka Google Maps</span>
                            </a>
                            <a href="https://waze.com/ul?ll={{ $travelProgress['target_lat'] }},{{ $travelProgress['target_lng'] }}&navigate=yes" 
                               target="_blank" rel="noopener noreferrer"
                               class="py-2 px-3 bg-indigo-700 hover:bg-indigo-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                                <span>Waze</span>
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        @endif

        {{-- Card Klarifikasi Laporan Aduan / Sengketa Admin --}}
        @php
            $mitraReport = \App\Models\PartnerReport::where('reported_help_id', $help->id)
                ->latest()
                ->first();
        @endphp
        @if($mitraReport)
            <div class="bg-gradient-to-br from-purple-50 to-indigo-50/50 dark:from-purple-950/40 dark:to-indigo-950/30 px-4 py-4 rounded-2xl shadow-xs border border-purple-200/80 dark:border-purple-800/60 mb-3 space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center flex-shrink-0 mt-0.5 border border-purple-500/20 shadow-2xs">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h4 class="text-xs font-bold text-purple-950 dark:text-purple-100">Klarifikasi Laporan Aduan ({{ $mitraReport->id }})</h4>
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $mitraReport->status === 'resolved' ? 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300' : 'bg-purple-100 dark:bg-purple-900/80 text-purple-800 dark:text-purple-300' }}">
                                {{ ucfirst($mitraReport->status) }}
                            </span>
                        </div>
                        <p class="text-xs text-purple-900/85 dark:text-purple-300/90 leading-relaxed break-words">
                            Customer mengajukan klaim/aduan pada bantuan ini: <em>"{{ $mitraReport->message }}"</em>
                        </p>
                    </div>
                </div>

                {{-- Tombol Buka Ruang Chat Klarifikasi Khusus --}}
                <div class="pt-2.5 border-t border-purple-200/60 dark:border-purple-800/60 flex items-center justify-between gap-2 flex-wrap">
                    <div class="text-[11px] text-purple-800 dark:text-purple-300">
                        @php $mitraMsgCount = $mitraReport->messages()->count(); @endphp
                        <span>{{ $mitraMsgCount > 0 ? $mitraMsgCount . ' pesan klarifikasi tersedia' : 'Ruang klarifikasi dengan Admin aktif' }}</span>
                    </div>
                    <a href="{{ route('mitra.chat', ['admin' => 1, 'report' => $mitraReport->id]) }}"
                        class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Buka Ruang Chat Klarifikasi</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- Schedule --}}
        <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3">
            <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Jadwal Permintaan
            </h3>
            <p class="text-sm text-gray-700 dark:text-gray-300">
                {{ \Carbon\Carbon::parse($help->scheduled_at ?? $help->created_at)->translatedFormat('l, d F Y') }}
                (Jam {{ \Carbon\Carbon::parse($help->scheduled_at ?? $help->created_at)->format('H:i') }})
            </p>
            <p class="text-xs text-gray-500 mt-1"></p>
        </div>

        {{-- Customer Info --}}
        <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-sm text-gray-900 dark:text-white">Informasi Customer</h3>
            </div>
            <div class="flex items-center gap-3 mb-3">
                @if ($help->user->profile_photo ?? $help->user->photo)
                    <img src="{{ asset('storage/' . ($help->user->profile_photo ?? $help->user->photo)) }}" alt="{{ $help->user->name }}"
                        class="w-12 h-12 rounded-full object-cover border-2 border-blue-100 dark:border-blue-900">
                @else
                    <div
                        class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-lg">
                        {{ strtoupper(substr($help->user->name ?? 'C', 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1">
                    <h4 class="font-semibold text-sm text-gray-900 dark:text-white">{{ $help->user->name }}</h4>
                    @if ($help->user->phone)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">{{ $help->user->phone }}</p>
                    @endif
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $help->city->name ?? '-' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('mitra.chat', ['help' => $help->id]) }}"
                    class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-white dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 transition">
                    <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <span class="text-sm font-semibold">Chat</span>
                </a>
            </div>
        </div>

        {{-- Location & Interactive Connected Route Map --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3 overflow-hidden" id="group-route-map">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                <h3 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span>Peta & Rute Menuju Lokasi</span>
                </h3>
                <div class="flex items-center gap-1.5" id="gps-status-badge">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-full border border-emerald-200/60 dark:border-emerald-800" id="gps-status-text">GPS Terhubung</span>
                </div>
            </div>

            {{-- Interactive Leaflet Route Map --}}
            <div class="relative w-full h-72 bg-gray-100 dark:bg-gray-700">
                <div id="mitra-route-map" class="w-full h-full z-0"></div>

                {{-- Map Overlay Info Bar --}}
                <div class="absolute top-3 left-3 right-3 z-[400] flex items-center justify-between pointer-events-none">
                    <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-md px-3 py-1.5 rounded-xl shadow-lg border border-gray-200/80 dark:border-gray-700 pointer-events-auto flex items-center gap-2.5">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">Jarak:</span>
                            <span class="text-xs font-bold text-blue-600 dark:text-blue-400" id="route-dist-badge">Menghitung...</span>
                        </div>
                        <div class="h-3 w-px bg-gray-200 dark:bg-gray-700"></div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">Waktu:</span>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400" id="route-time-badge">~</span>
                        </div>
                    </div>

                    <button type="button" id="btn-recenter-route-map" class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-md p-2 rounded-xl shadow-lg border border-gray-200/80 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:text-blue-600 hover:bg-white dark:hover:bg-gray-700 pointer-events-auto transition active:scale-95 cursor-pointer" title="Pusatkan Rute">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                    </button>
                </div>

                {{-- Map Legend (Bottom Right) --}}
                <div class="absolute bottom-3 left-3 z-[400] bg-white/90 dark:bg-gray-800/90 backdrop-blur-md px-2.5 py-1 rounded-lg text-[10px] font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-3 pointer-events-none">
                    <span class="flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block"></span> Posisi Anda
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span> Tujuan Customer
                    </span>
                </div>
            </div>

            {{-- Address Text & Navigation CTA --}}
            <div class="p-4 space-y-3 bg-white dark:bg-gray-800">
                <!-- Titik Alamat Utama GPS -->
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 flex items-center justify-center flex-shrink-0 mt-0.5 border border-red-100 dark:border-red-900/50 shadow-2xs">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 flex-wrap mb-0.5">
                            <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Titik Alamat Customer</span>
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
                            Detail Patokan Tempat / Ciri Rumah
                        </h4>
                        <span class="text-[11px] font-normal text-gray-400 dark:text-gray-500"></span>
                    </div>

                    @if(!empty($help->full_address))
                        <p class="text-xs text-amber-950 dark:text-amber-100 font-medium leading-relaxed whitespace-pre-line break-words pl-5">
                            {{ $help->full_address }}
                        </p>
                    @else
                        <p class="text-xs text-gray-400 dark:text-gray-500 italic pl-5">
                            Customer tidak menyertakan patokan/ciri khusus rumah. Silakan gunakan navigasi peta ke titik koordinat.
                        </p>
                    @endif
                </div>

                {{-- Action Buttons: Google Maps Turn-by-Turn Nav --}}
                <div class="pt-1 flex items-center gap-2">
                    <a id="btn-google-maps-nav" 
                       href="https://www.google.com/maps/dir/?api=1&destination={{ $help->latitude ?? '' }},{{ $help->longitude ?? '' }}&travelmode=driving"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="flex-1 py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm shadow-emerald-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <span>Mulai Navigasi Google Maps</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Additional Details (equipment, coords, timestamps, voucher) --}}
        <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3">
            <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-2">Deskripsi & Detail</h3>

            @if(!empty($help->description))
                <div class="mb-3 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line break-words break-all">{{ $help->description }}</div>
            @endif

            <div class="grid grid-cols-2 gap-3 text-sm text-gray-700 dark:text-gray-300">
                @if(!empty($help->equipment_provided))
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Perlengkapan</div>
                        <div class="font-semibold break-words break-all text-gray-900 dark:text-white">{{ $help->equipment_provided }}</div>
                    </div>
                @endif

                @if(!empty($help->voucher_code))
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Voucher</div>
                        <div class="font-semibold text-red-600 dark:text-red-400">{{ $help->voucher_code }} @if($help->discount_amount) ( -Rp{{ number_format($help->discount_amount,0,',','.') }})@endif</div>
                    </div>
                @endif
            </div>

            @if(!empty($help->photo))
                <div class="mt-3 pt-2.5 border-t border-gray-100 dark:border-gray-750" x-data="{ openPhotoModal: false }">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Foto Lampiran Pesanan
                        </span>
                        <button type="button" @click="openPhotoModal = true" class="text-[11px] font-semibold text-primary-600 dark:text-primary-400 hover:underline cursor-pointer flex items-center gap-1">
                            <span>Lihat Penuh</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </div>
                    <div @click="openPhotoModal = true" class="relative rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-xs group bg-gray-100 dark:bg-gray-900 max-h-64 sm:max-h-72 flex items-center justify-center cursor-pointer">
                        <img src="{{ asset('storage/' . $help->photo) }}" alt="Foto lampiran pesanan" class="w-full h-auto max-h-64 sm:max-h-72 object-contain group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors flex items-center justify-center pointer-events-none">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity bg-black/75 text-white text-[10px] font-bold px-3 py-1 rounded-full backdrop-blur-xs shadow-xs">
                                🔍 Klik untuk Perbesar
                            </span>
                        </div>
                    </div>

                    {{-- Image Lightbox Modal --}}
                    <div x-show="openPhotoModal" x-cloak class="fixed inset-0 z-[9999] bg-black/85 backdrop-blur-sm flex items-center justify-center p-3 animate-fade-in" @click.self="openPhotoModal = false">
                        <div class="relative max-w-4xl w-full max-h-[92vh] flex flex-col items-center">
                            <button type="button" @click="openPhotoModal = false" class="absolute -top-10 right-0 text-white/90 hover:text-white bg-white/10 hover:bg-white/20 rounded-full p-1.5 transition cursor-pointer">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            <img src="{{ asset('storage/' . $help->photo) }}" alt="Foto lampiran penuh" class="max-w-full max-h-[85vh] object-contain rounded-xl shadow-2xl">
                            <a href="{{ asset('storage/' . $help->photo) }}" target="_blank" rel="noopener" class="mt-3 text-xs text-white/90 hover:text-white underline flex items-center gap-1">
                                Buka di Tab Baru ↗
                            </a>
                        </div>
                    </div>
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

        {{-- GPS Simulator (toggle via GPS_SIMULATOR env) --}}
        @if (config('app.gps_simulator', true) &&
                $help->mitra_id === auth()->id() &&
                !in_array($help->status, ['selesai', 'dibatalkan']))
            <div class="mb-3">
                <livewire:mitra.gps.simulator :help-id="$help->id" :key="'gps-simulator-' . $help->id" />
            </div>
        @endif

        {{-- Update Status Section (Revisi 3: Multi-Stage Action Controls & Scheduled Departure Window Lock) --}}
        @if ($help->status === 'taken' && $help->mitra_id === auth()->id())
            @php
                $isScheduledLocked = $help->isScheduled() && !$help->canPartnerStartDeparture();
                $targetTimeStr = $help->getScheduledTargetTime()?->format('H:i') ?? '-';
                $windowOpensStr = $help->departure_window_opens_at?->format('H:i') ?? '-';
            @endphp

            @if ($isScheduledLocked)
                {{-- Scheduled Departure Lock Banner --}}
                <div class="bg-gradient-to-br from-amber-50 to-orange-50/50 dark:from-amber-950/40 dark:to-orange-950/30 border border-amber-200 dark:border-amber-800/80 rounded-2xl p-4 mb-3 space-y-3 shadow-xs">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0 mt-0.5 border border-amber-500/30 shadow-2xs">
                            <span class="text-lg">🔒</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 flex-wrap mb-1">
                                <h4 class="text-xs font-bold text-amber-950 dark:text-amber-100">
                                    Tugas Terjadwal (Pukul {{ $targetTimeStr }})
                                </h4>
                                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-amber-200/80 dark:bg-amber-900/80 text-amber-900 dark:text-amber-200">
                                    ⏳ {{ $help->departure_countdown_formatted }}
                                </span>
                            </div>
                            <p class="text-xs text-amber-900/85 dark:text-amber-300/90 leading-relaxed">
                                Pesanan ini berhasil Anda ambil di awal. Tombol keberangkatan akan aktif otomatis pada pukul <strong>{{ $windowOpensStr }}</strong> ({{ $help->departure_lead_minutes }} menit sebelum jadwal).
                            </p>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-amber-200/60 dark:border-amber-800/60 flex items-center justify-between text-[11px] text-amber-800 dark:text-amber-300">
                        <span>📅 Jadwal: {{ $help->getScheduledTargetTime()?->translatedFormat('d M Y - H:i') }}</span>
                        <span class="font-semibold">Harap bersiap sebelum berangkat</span>
                    </div>
                </div>

                {{-- Disabled Action Button --}}
                <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3 space-y-2">
                    <button disabled
                        class="w-full py-3.5 bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 rounded-xl font-bold text-sm shadow-xs flex items-center justify-center gap-2 cursor-not-allowed">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Keberangkatan Terkunci (Aktif Pkl {{ $windowOpensStr }})</span>
                    </button>
                    <p class="text-xs text-gray-400 dark:text-gray-500 text-center">Tombol mulai berangkat akan aktif otomatis saat memasuki jeda waktu keberangkatan.</p>
                </div>
            @else
                @if ($help->isScheduled())
                    {{-- Banner Waktunya Berangkat --}}
                    <div class="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-950/50 dark:to-teal-950/40 border border-emerald-300/80 dark:border-emerald-700/70 rounded-2xl p-3.5 mb-3 flex items-center gap-3 shadow-xs">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0 text-base">
                            ⏰
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-emerald-950 dark:text-emerald-100">Waktunya Berangkat!</h4>
                            <p class="text-[11px] text-emerald-800 dark:text-emerald-300">Jadwal pelaksanaan pukul <strong>{{ $targetTimeStr }}</strong>. Silakan mulai perjalanan Anda sekarang.</p>
                        </div>
                    </div>
                @endif

                <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3 space-y-3">
                    {{-- Pre-departure confirmation notice --}}
                    <div class="p-3 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-xl text-xs text-blue-900 dark:text-blue-200 space-y-1">
                        <div class="font-bold flex items-center gap-1.5">
                            <span>📞</span>
                            <span>Konfirmasi Pra-Keberangkatan</span>
                        </div>
                        <p class="text-[11px] text-blue-800 dark:text-blue-300">
                            Disarankan menghubungi customer via Chat/Telepon untuk memastikan kesiapan dan memverifikasi alamat sebelum Anda berangkat.
                        </p>
                    </div>

                    @if ($help->service_type === 'pickup_delivery')
                        <button wire:click="advanceStage('going_to_pickup')" wire:loading.attr="disabled"
                            class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Mulai Menuju Lokasi Penjemputan</span>
                        </button>
                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">Klik saat Anda mulai bergerak menuju titik penjemputan</p>
                    @else
                        <button wire:click="markPartnerStarted" wire:loading.attr="disabled"
                            class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Mulai Berangkat ke Lokasi Customer</span>
                        </button>
                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">Klik saat Anda mulai jalan menuju lokasi pengerjaan</p>
                    @endif
                </div>
            @endif
        @endif

        @if ($help->status === 'partner_on_the_way' && $help->mitra_id === auth()->id())
            <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3 space-y-2">
                @if ($help->service_type === 'pickup_delivery')
                    @if (!$help->service_stage || $help->service_stage === 'going_to_pickup')
                        <button wire:click="advanceStage('at_pickup')" wire:loading.attr="disabled"
                            class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            <span>Saya Sudah Tiba di Lokasi Penjemputan</span>
                        </button>
                    @elseif ($help->service_stage === 'at_pickup')
                        <button wire:click="advanceStage('item_collected')" wire:loading.attr="disabled"
                            class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>Barang Sudah Diambil (Mulai Pengantaran)</span>
                        </button>
                    @elseif ($help->service_stage === 'item_collected' || $help->service_stage === 'going_to_destination')
                        <button wire:click="advanceStage('at_destination')" wire:loading.attr="disabled"
                            class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Tiba di Lokasi Tujuan Customer</span>
                        </button>
                    @endif
                @else
                    <button wire:click="markPartnerArrived" wire:loading.attr="disabled"
                        class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Saya Sudah Tiba di Lokasi</span>
                    </button>
                    <p class="text-xs text-gray-500 dark:text-gray-400 text-center">Klik saat Anda telah sampai di titik lokasi customer</p>
                @endif
            </div>
        @endif

        @if ($help->status === 'partner_arrived' || in_array($help->service_stage, ['at_customer', 'at_destination']))
            <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3">
                @if ($help->service_type === 'on_site_service')
                    <button wire:click="startService"
                        class="w-full py-3.5 bg-green-600 text-white rounded-xl font-bold text-sm hover:bg-green-700 transition flex items-center justify-center gap-2 cursor-pointer shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Mulai Pekerjaan</span>
                    </button>
                    <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-2">Klik tombol ini setelah Anda siap memulai pengerjaan</p>
                @else
                    <button wire:click="openCompletionModal"
                        class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                        <span>Selesaikan & Unggah Bukti Serah Terima</span>
                    </button>
                    <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-2">Upload foto serah terima barang/hasil pesanan kepada customer</p>
                @endif
            </div>
        @endif

        @if ($help->status === 'in_progress')
            <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 mb-3">
                <button wire:click="openCompletionModal"
                    class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Selesaikan & Upload Bukti Pekerjaan</span>
                </button>
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-2">Upload foto hasil pengerjaan sebagai bukti penyelesaian kepada customer</p>
            </div>
        @endif

        @if ($help->status === 'waiting_customer_confirmation')
            <div class="bg-gradient-to-br from-sky-50/90 via-blue-50/50 to-indigo-50/40 dark:from-sky-950/40 dark:via-blue-950/30 dark:to-gray-800/60 p-4 sm:p-5 rounded-2xl border border-sky-200/80 dark:border-sky-800/60 shadow-xs mb-3 space-y-3">
                <div class="flex items-start ">
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-4 flex-wrap mb-1">
                            <h4 class="font-bold text-sm text-sky-950 dark:text-sky-100">Menunggu Konfirmasi Customer</h4>
                            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700 shadow-2xs">
                                🛡️ Dana Rp {{ number_format($help->amount, 0, ',', '.') }} Diamankan
                            </span>
                        </div>
                        <p class="text-xs text-sky-900/80 dark:text-sky-200/85 leading-relaxed">
                            Bukti pengerjaan telah terkirim. Dana otomatis cair ke saldo Anda saat customer mengonfirmasi atau dalam batas waktu 24 jam.
                        </p>
                        
                        @if($help->proof_photo)
                            <div class="mt-3 pt-3 border-t border-sky-200/60 dark:border-sky-800/40">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 block mb-2 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-sky-600 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Foto Bukti yang Terkirim:
                                </span>
                                <a href="{{ asset('storage/' . $help->proof_photo) }}" target="_blank" rel="noopener" class="block rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-900/10 max-h-52 flex items-center justify-center group">
                                    <img src="{{ asset('storage/' . $help->proof_photo) }}" alt="Bukti Pekerjaan" class="w-full h-auto max-h-52 object-contain group-hover:scale-105 transition-transform duration-300">
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if (in_array($help->status, ['completed', 'selesai']) && $help->proof_photo)
            <div class="bg-emerald-50 dark:bg-emerald-950/30 p-4 rounded-2xl border border-emerald-200 dark:border-emerald-800 mb-3 space-y-2">
                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 block flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Foto Bukti Penyelesaian Pekerjaan:
                </span>
                <a href="{{ asset('storage/' . $help->proof_photo) }}" target="_blank" rel="noopener" class="block rounded-xl overflow-hidden border border-emerald-200/80 dark:border-emerald-800 bg-gray-900/10 max-h-52 flex items-center justify-center group">
                    <img src="{{ asset('storage/' . $help->proof_photo) }}" alt="Bukti Selesai" class="w-full h-auto max-h-52 object-contain group-hover:scale-105 transition-transform duration-300">
                </a>
                @if($help->completion_notes)
                    <p class="text-xs text-gray-700 dark:text-gray-300 italic bg-white dark:bg-gray-800 p-2.5 rounded-xl border border-emerald-100 dark:border-emerald-900/40">"{{ $help->completion_notes }}"</p>
                @endif
            </div>
        @endif

        {{-- Penilaian & Ulasan dari Customer --}}
        @if (in_array($help->status, ['completed', 'selesai']))
            @php
                $customerReview = $help->rating;
            @endphp

            @if ($customerReview)
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 sm:p-5 border border-gray-100 dark:border-gray-700/80 shadow-xs mb-3">
                    <div class="flex items-center justify-between mb-3 pb-2.5 border-b border-gray-100 dark:border-gray-700/60">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-amber-500 text-white flex items-center justify-center font-bold text-sm shadow-2xs">
                                ⭐
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-gray-900 dark:text-white">Ulasan dari Customer</h4>
                                <p class="text-[11px] text-gray-400 dark:text-gray-400">{{ optional($customerReview->created_at)->translatedFormat('d M Y, H:i') }} WIB</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 bg-gray-50 dark:bg-gray-750 px-2.5 py-1 rounded-full border border-gray-100 dark:border-gray-700">
                            <span class="text-xs font-black text-gray-900 dark:text-white">{{ $customerReview->rating }}.0</span>
                            <div class="flex ml-0.5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-3.5 h-3.5 {{ $i <= $customerReview->rating ? 'text-amber-400 fill-current' : 'text-gray-200 dark:text-gray-700 fill-current' }}" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                    </div>

                    @if(!empty($customerReview->review))
                        <div class="bg-gray-50/70 dark:bg-gray-750/50 rounded-xl p-3 border border-gray-100 dark:border-gray-700/60 text-xs text-gray-800 dark:text-gray-200 italic leading-relaxed">
                            "{{ $customerReview->review }}"
                        </div>
                    @else
                        <p class="text-xs text-gray-400 italic">Customer memberikan rating {{ $customerReview->rating }} bintang tanpa ulasan tertulis.</p>
                    @endif
                </div>
            @else
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-3.5 border border-gray-100 dark:border-gray-700/70 text-center mb-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <span>Menunggu customer memberikan penilaian & ulasan</span>
                    </p>
                </div>
            @endif
        @endif

        @if (in_array($help->status, ['taken', 'partner_on_the_way', 'partner_arrived']) &&
                $help->mitra_id === auth()->id())
            <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3">
                <button wire:click="openPartnerCancelModal"
                    class="w-full py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold text-sm transition flex items-center justify-center gap-2 shadow-xs active:scale-98 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Batalkan Penugasan (Sepihak)
                </button>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 text-center mt-2">
                    Pembatalan langsung melepaskan tugas tanpa menunggu customer (berkonsekuensi poin SP).
                </p>
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
            <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60">
                <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Riwayat Timeline
                </h3>
                <div class="space-y-2 text-xs">
                    @if ($help->partner_started_at)
                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-300 py-1">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                                <span>Mulai Perjalanan</span>
                            </div>
                            <span
                                class="text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($help->partner_started_at)->format('d M, H:i') }}</span>
                        </div>
                    @endif
                    @if ($help->partner_arrived_at)
                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-300 py-1">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                                <span>Tiba di Lokasi</span>
                            </div>
                            <span
                                class="text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($help->partner_arrived_at)->format('d M, H:i') }}</span>
                        </div>
                    @endif
                    @if ($help->service_started_at)
                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-300 py-1">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                                <span>Mulai Pengerjaan</span>
                            </div>
                            <span
                                class="text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($help->service_started_at)->format('d M, H:i') }}</span>
                        </div>
                    @endif
                    @if ($help->service_completed_at)
                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-300 py-1">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                                <span>Selesai Pengerjaan</span>
                            </div>
                            <span
                                class="text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($help->service_completed_at)->format('d M, H:i') }}</span>
                        </div>
                    @endif
                    @if ($help->status === 'waiting_customer_confirmation')
                        <div class="flex items-center justify-between text-orange-700 dark:text-orange-400 font-semibold py-1">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div>
                                <span>Menunggu Konfirmasi Customer</span>
                            </div>
                            <span
                                class="text-orange-600 dark:text-orange-300">{{ \Carbon\Carbon::parse($help->service_completed_at ?? now())->format('d M, H:i') }}</span>
                        </div>
                    @endif
                    @if ($help->completed_at)
                        <div class="flex items-center justify-between text-green-700 dark:text-green-400 font-semibold py-1">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-600"></div>
                                <span>Pesanan Selesai</span>
                            </div>
                            <span
                                class="text-green-600 dark:text-green-300">{{ \Carbon\Carbon::parse($help->completed_at)->format('d M, H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
    {{-- Partner Cancel Modal - Bottom Sheet Style --}}
    @if ($showPartnerCancelModal)
        <div class="modal-overlay fixed inset-0 z-[9999] flex items-end justify-center animate-fade-in" 
             style="background: rgba(0,0,0,0.6);" 
             wire:click="$set('showPartnerCancelModal', false)">
            <div class="bg-white dark:bg-gray-800 rounded-t-3xl w-full max-w-md shadow-2xl animate-slide-up relative" 
                 @click.stop 
                 style="padding-bottom: env(safe-area-inset-bottom,24px);">
                
                {{-- Header --}}
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 px-5 py-4 rounded-t-3xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="text-red-500">⚠️</span>
                            <span>Ajukan Pembatalan Penugasan</span>
                        </h3>
                        <button type="button" 
                                wire:click="$set('showPartnerCancelModal', false)" 
                                class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition text-gray-600 dark:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-5 pb-6">
                    {{-- Info Box --}}
                    <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-2xl p-3.5 mb-4 text-xs space-y-2">
                        <div class="flex items-start gap-2.5">
                            <span class="text-lg leading-none mt-0.5">ℹ️</span>
                            <div class="text-amber-900 dark:text-amber-200 leading-relaxed">
                                <strong class="block font-bold mb-0.5">Pelepasan Tugas & Audit Wilayah:</strong>
                                Akun Anda akan langsung dibebaskan agar dapat mencari order lain. Pengajuan ini akan ditinjau oleh <strong>Admin Wilayah</strong>.
                            </div>
                        </div>
                        <div class="pt-2 border-t border-amber-200/60 dark:border-amber-800/60 text-[11px] text-amber-800 dark:text-amber-300">
                            ✓ <strong>Bukti Sah (Darurat / Kendala Nyata):</strong> Bebas Surat Peringatan (SP).<br>
                            ⚠️ <strong>Klaim Palsu / Berbohong:</strong> Admin Wilayah berhak memberikan sanksi SP (SP 1 / SP 2 / SP 3).
                        </div>
                    </div>

                    {{-- Form Fields --}}
                    <div class="space-y-3 mb-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-900 dark:text-white mb-1">Alasan Pembatalan <span class="text-red-500">*</span></label>
                            <select wire:model.defer="partnerCancelReason" 
                                    class="w-full p-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-red-500 transition">
                                <option value="">-- Pilih Alasan --</option>
                                <option value="Kendaraan Bermasalah / Mogok">Kendaraan Bermasalah / Mogok</option>
                                <option value="Kondisi Darurat Pribadi / Sakit Mendadak">Kondisi Darurat Pribadi / Sakit Mendadak</option>
                                <option value="Barang / Toko Tidak Ditemukan / Tutup">Barang / Toko Tidak Ditemukan / Tutup</option>
                                <option value="Customer Tidak Dapat Dihubungi">Customer Tidak Dapat Dihubungi</option>
                                <option value="Lokasi Tidak Memungkinkan Dijangkau / Bahaya">Lokasi Tidak Memungkinkan Dijangkau / Bahaya</option>
                                <option value="Lainnya">Lainnya (Tuliskan di catatan)</option>
                            </select>
                            @error('partnerCancelReason') <span class="text-[11px] text-red-500 font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-900 dark:text-white mb-1">Foto Bukti Kendala (Opsional)</label>
                            <input type="file" wire:model="cancel_evidence_photo" accept="image/*"
                                   class="w-full p-2 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 dark:file:bg-primary-950 dark:file:text-primary-300">
                            @error('cancel_evidence_photo') <span class="text-[11px] text-red-500 font-semibold">{{ $message }}</span> @enderror
                            <p class="text-[10px] text-gray-400 mt-1">Unggah foto ban bocor, toko tutup, atau kendala lapangan untuk mempermudah audit bebas SP.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-900 dark:text-white mb-1">Catatan Tambahan (Opsional)</label>
                            <textarea wire:model.defer="partnerCancelNotes" rows="2"
                                      class="w-full p-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-red-500 transition"
                                      placeholder="Jelaskan kendala yang dialami..."></textarea>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-3">
                        <button type="button"
                                wire:click="$set('showPartnerCancelModal', false)"
                                class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-semibold hover:bg-gray-200 transition cursor-pointer">
                            Batal
                        </button>
                        <button type="button"
                                wire:click="requestPartnerCancel" 
                                wire:loading.attr="disabled"
                                class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-bold transition disabled:opacity-50 flex items-center justify-center gap-1.5 cursor-pointer shadow-xs active:scale-98">
                            <span wire:loading.remove wire:target="requestPartnerCancel">Kirim Pengajuan Batal</span>
                            <span wire:loading wire:target="requestPartnerCancel">Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Clarification Modal for Customer-Requested Cancel --}}
    @if ($showClarificationModal)
        <div class="modal-overlay fixed inset-0 z-[9999] flex items-end justify-center animate-fade-in" 
             style="background: rgba(0,0,0,0.6);" 
             wire:click="closeClarificationModal">
            <div class="bg-white dark:bg-gray-800 rounded-t-3xl w-full max-w-md shadow-2xl animate-slide-up relative" 
                 @click.stop 
                 style="padding-bottom: env(safe-area-inset-bottom,24px);">
                
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 px-5 py-4 rounded-t-3xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span>💬 Beri Klarifikasi / Tanggapan</span>
                        </h3>
                        <button type="button" wire:click="closeClarificationModal" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition text-gray-600 dark:text-gray-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="p-5 pb-6 space-y-4 text-xs">
                    <p class="text-gray-600 dark:text-gray-300">
                        Customer mengajukan pembatalan pesanan ini. Anda dapat memberikan penjelasan atau kesaksian Anda untuk ditinjau oleh Admin Wilayah sebelum keputusan diambil.
                    </p>

                    <div>
                        <label class="block font-bold text-gray-900 dark:text-white mb-1">Penjelasan / Klarifikasi Anda <span class="text-red-500">*</span></label>
                        <textarea wire:model.defer="partnerClarificationText" rows="3" class="w-full p-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs" placeholder="Contoh: Saya sudah di jalan namun terjebak macet total..."></textarea>
                        @error('partnerClarificationText') <span class="text-[11px] text-red-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-gray-900 dark:text-white mb-1">Foto Bukti Pendukung (Opsional)</label>
                        <input type="file" wire:model="partnerClarificationPhoto" accept="image/*" class="w-full p-2 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-300 rounded-xl">
                        @error('partnerClarificationPhoto') <span class="text-[11px] text-red-500 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="closeClarificationModal" class="flex-1 py-3 bg-gray-100 dark:bg-gray-700 rounded-xl font-semibold">Batal</button>
                        <button type="button" wire:click="submitClarification" wire:loading.attr="disabled" class="flex-1 py-3 bg-primary-600 text-white rounded-xl font-bold flex items-center justify-center">
                            <span wire:loading.remove wire:target="submitClarification">Kirim Tanggapan</span>
                            <span wire:loading wire:target="submitClarification">Mengirim...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Partner cancellation state info banner (Non-blocking) --}}
    @if ($help->status === 'partner_cancel_requested' && $help->mitra_id === auth()->id())
        <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80 rounded-2xl p-4 mb-4 text-xs">
            <div class="flex items-start gap-3">
                <span class="text-xl">⚠️</span>
                <div class="flex-1">
                    <h4 class="font-bold text-amber-900 dark:text-amber-200 text-sm">Pembatalan Tugas Diajukan</h4>
                    <p class="text-amber-800 dark:text-amber-300 mt-1 leading-relaxed">
                        Anda telah mengajukan pembatalan untuk pesanan ini (Alasan: <em>"{{ $help->partner_cancel_reason ?? 'Kendala Lapangan' }}"</em>). Akun Anda telah dibebaskan dan siap menerima tugas baru.
                    </p>
                    <div class="mt-3 flex items-center gap-2">
                        <a href="{{ route('mitra.dashboard') }}" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl transition text-xs inline-flex items-center gap-1 shadow-2xs">
                            ← Kembali ke Dashboard
                        </a>
                        <a href="{{ route('mitra.helps.all') }}" class="px-3.5 py-1.5 bg-white dark:bg-gray-800 hover:bg-gray-50 border border-amber-300 dark:border-amber-700 font-semibold text-amber-900 dark:text-amber-200 rounded-xl transition text-xs inline-flex items-center gap-1">
                            Cari Bantuan Lain ↗
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
                <div class="bg-blue-600 px-6 py-5 text-white flex items-center justify-between">
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
                            <h3 class="font-bold text-base leading-tight">Selesaikan Pekerjaan</h3>
                            <p class="text-xs text-blue-100">Tugas otomatis tuntas & Anda bisa langsung ambil bantuan lain</p>
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

                    {{-- Info Box --}}
                    <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/60 rounded-xl p-3 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-[11px] text-blue-900 dark:text-blue-200 leading-snug">
                            Setelah foto bukti dikirim, tugas akan <strong>otomatis berstatus Selesai</strong> dan dana saldo langsung masuk ke akun Anda. Anda dapat langsung mengambil pekerjaan berikutnya!
                        </p>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-2.5 pt-2">
                        <button type="button" wire:click="closeCompletionModal"
                            class="flex-1 py-3 px-4 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-semibold text-xs hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            Batal
                        </button>
                        <button type="button" wire:click="submitCompletionProof" wire:loading.attr="disabled"
                            class="flex-1 py-3 px-4 rounded-xl bg-blue-600 text-white font-bold text-xs shadow-md hover:bg-blue-700 transition flex items-center justify-center gap-1.5 disabled:opacity-50">
                            <span wire:loading.remove wire:target="submitCompletionProof">Selesaikan Tugas Sekarang</span>
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

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .mitra-pulse-icon {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .mitra-pulse-dot {
            width: 16px;
            height: 16px;
            background: #2563eb;
            border: 2.5px solid #ffffff;
            border-radius: 50%;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.6);
            z-index: 2;
        }
        .mitra-pulse-ring {
            position: absolute;
            width: 34px;
            height: 34px;
            background: rgba(37, 99, 235, 0.35);
            border-radius: 50%;
            animation: mitraPulseAnimation 2s infinite ease-in-out;
            z-index: 1;
        }
        @keyframes mitraPulseAnimation {
            0% { transform: scale(0.5); opacity: 1; }
            100% { transform: scale(1.6); opacity: 0; }
        }

        .dest-pulse-icon {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .dest-pulse-dot {
            width: 18px;
            height: 18px;
            background: #ef4444;
            border: 3px solid #ffffff;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.7);
            z-index: 2;
        }
        .dest-pulse-ring {
            position: absolute;
            width: 36px;
            height: 36px;
            background: rgba(239, 68, 68, 0.35);
            border-radius: 50%;
            animation: destPulseAnimation 2.2s infinite ease-in-out;
            z-index: 1;
        }
        @keyframes destPulseAnimation {
            0% { transform: scale(0.5); opacity: 1; }
            100% { transform: scale(1.5); opacity: 0; }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function() {
            let mapInstance = null;
            let mitraMarker = null;
            let destMarker = null;
            let routePolyline = null;
            let gpsWatchId = null;
            let isSimulating = false;

            const destLat = parseFloat("{{ $help->latitude ?? '' }}") || null;
            const destLng = parseFloat("{{ $help->longitude ?? '' }}") || null;

            let mitraLat = parseFloat("{{ $help->partner_current_lat ?? $help->partner_initial_lat ?? auth()->user()->latitude ?? '' }}") || (destLat ? destLat - 0.012 : -6.2088);
            let mitraLng = parseFloat("{{ $help->partner_current_lng ?? $help->partner_initial_lng ?? auth()->user()->longitude ?? '' }}") || (destLng ? destLng - 0.012 : 106.8456);

            function initMitraRouteMap() {
                const mapContainer = document.getElementById('mitra-route-map');
                if (!mapContainer || typeof L === 'undefined') return;

                if (mapContainer._leaflet_id && mapInstance) {
                    mapInstance.remove();
                    mapInstance = null;
                }

                const initialCenter = destLat && destLng ? [(destLat + mitraLat) / 2, (destLng + mitraLng) / 2] : [mitraLat, mitraLng];

                mapInstance = L.map('mitra-route-map', {
                    zoomControl: false,
                    attributionControl: false
                }).setView(initialCenter, 14);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(mapInstance);

                L.control.zoom({ position: 'bottomright' }).addTo(mapInstance);

                // Mitra Pulsing Marker Icon
                const mitraIcon = L.divIcon({
                    className: 'mitra-pulse-wrapper',
                    html: `<div class="mitra-pulse-icon"><div class="mitra-pulse-ring"></div><div class="mitra-pulse-dot"></div></div>`,
                    iconSize: [34, 34],
                    iconAnchor: [17, 17]
                });

                // Customer Destination Marker Icon
                const destIcon = L.divIcon({
                    className: 'dest-pulse-wrapper',
                    html: `<div class="dest-pulse-icon"><div class="dest-pulse-ring"></div><div class="dest-pulse-dot"></div></div>`,
                    iconSize: [36, 36],
                    iconAnchor: [18, 18]
                });

                mitraMarker = L.marker([mitraLat, mitraLng], { icon: mitraIcon }).addTo(mapInstance)
                    .bindPopup('<b>Lokasi Anda (Rekan Jasa)</b>');

                if (destLat && destLng) {
                    destMarker = L.marker([destLat, destLng], { icon: destIcon }).addTo(mapInstance)
                        .bindPopup('<b>Tujuan: {{ addslashes($help->user->name ?? "Customer") }}</b><br><span class="text-xs text-gray-500">{{ addslashes($help->location ?? $help->full_address ?? "Lokasi Bantuan") }}</span>');
                    
                    updateConnectedRoute(mitraLat, mitraLng, destLat, destLng);
                }

                // Recenter button
                document.getElementById('btn-recenter-route-map')?.addEventListener('click', () => {
                    if (routePolyline) {
                        mapInstance.fitBounds(routePolyline.getBounds(), { padding: [40, 40] });
                    } else if (destLat && destLng) {
                        mapInstance.fitBounds([[mitraLat, mitraLng], [destLat, destLng]], { padding: [40, 40] });
                    }
                });

                // Start GPS tracking
                trackMitraGPS();
            }

            async function updateConnectedRoute(mLat, mLng, dLat, dLng) {
                if (!mapInstance) return;

                if (mitraMarker) mitraMarker.setLatLng([mLat, mLng]);
                if (destMarker) destMarker.setLatLng([dLat, dLng]);

                // Update Google Maps navigation URL
                const gmapsBtn = document.getElementById('btn-google-maps-nav');
                if (gmapsBtn) {
                    gmapsBtn.href = `https://www.google.com/maps/dir/?api=1&origin=${mLat},${mLng}&destination=${dLat},${dLng}&travelmode=driving`;
                }

                // Try fetching OSRM road route
                try {
                    const osrmUrl = `https://router.project-osrm.org/route/v1/driving/${mLng},${mLat};${dLng},${dLat}?overview=full&geometries=geojson`;
                    const res = await fetch(osrmUrl);
                    const json = await res.json();
                    if (json && json.routes && json.routes.length > 0) {
                        const route = json.routes[0];
                        const latLngs = route.geometry.coordinates.map(c => [c[1], c[0]]);

                        if (routePolyline) mapInstance.removeLayer(routePolyline);

                        routePolyline = L.polyline(latLngs, {
                            color: '#0077cc',
                            weight: 5,
                            opacity: 0.85,
                            lineCap: 'round',
                            lineJoin: 'round'
                        }).addTo(mapInstance);

                        const distKm = (route.distance / 1000).toFixed(1);
                        const durationMins = Math.max(1, Math.ceil(route.duration / 60));

                        const distBadge = document.getElementById('route-dist-badge');
                        const timeBadge = document.getElementById('route-time-badge');
                        if (distBadge) distBadge.textContent = `± ${distKm} km`;
                        if (timeBadge) timeBadge.textContent = `~ ${durationMins} mnt`;

                        mapInstance.fitBounds(routePolyline.getBounds(), { padding: [40, 40] });
                        return;
                    }
                } catch (err) {
                    console.warn('OSRM route fetch fallback to straight polyline:', err);
                }

                // Fallback: Straight dashed polyline
                if (routePolyline) mapInstance.removeLayer(routePolyline);
                routePolyline = L.polyline([[mLat, mLng], [dLat, dLng]], {
                    color: '#0077cc',
                    weight: 4,
                    dashArray: '8, 8',
                    opacity: 0.85
                }).addTo(mapInstance);

                const meters = L.latLng(mLat, mLng).distanceTo(L.latLng(dLat, dLng));
                const distKm = (meters / 1000).toFixed(1);
                const estMins = Math.max(1, Math.ceil((meters / 1000) / 25 * 60));

                const distBadge = document.getElementById('route-dist-badge');
                const timeBadge = document.getElementById('route-time-badge');
                if (distBadge) distBadge.textContent = `± ${distKm} km`;
                if (timeBadge) timeBadge.textContent = `~ ${estMins} mnt`;

                mapInstance.fitBounds([[mLat, mLng], [dLat, dLng]], { padding: [40, 40] });
            }

            function trackMitraGPS() {
                if (!navigator.geolocation) {
                    const statusText = document.getElementById('gps-status-text');
                    if (statusText) statusText.textContent = 'GPS Nonaktif';
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        if (isSimulating) return;
                        mitraLat = pos.coords.latitude;
                        mitraLng = pos.coords.longitude;
                        const statusText = document.getElementById('gps-status-text');
                        if (statusText) statusText.textContent = 'GPS Terhubung';

                        if (destLat && destLng) {
                            updateConnectedRoute(mitraLat, mitraLng, destLat, destLng);
                        }
                    },
                    () => {},
                    { enableHighAccuracy: true, timeout: 10000 }
                );

                if (gpsWatchId) navigator.geolocation.clearWatch(gpsWatchId);

                gpsWatchId = navigator.geolocation.watchPosition(
                    (pos) => {
                        if (isSimulating) return;
                        mitraLat = pos.coords.latitude;
                        mitraLng = pos.coords.longitude;
                        if (destLat && destLng) {
                            updateConnectedRoute(mitraLat, mitraLng, destLat, destLng);
                        }
                    },
                    () => {},
                    { enableHighAccuracy: true, maximumAge: 10000 }
                );
            }

            // Listen to GPS Simulator Events
            window.addEventListener('simulation-started', () => {
                isSimulating = true;
                const statusText = document.getElementById('gps-status-text');
                if (statusText) statusText.textContent = 'Simulasi GPS Aktif';
            });

            window.addEventListener('simulation-stopped', () => {
                isSimulating = false;
                const statusText = document.getElementById('gps-status-text');
                if (statusText) statusText.textContent = 'GPS Terhubung';
            });

            window.addEventListener('partner-location-updated', (e) => {
                const detail = Array.isArray(e.detail) ? e.detail[0] : (e.detail || {});
                const lat = parseFloat(detail.latitude || detail.lat);
                const lng = parseFloat(detail.longitude || detail.lng);

                if (lat && lng && destLat && destLng) {
                    isSimulating = true;
                    mitraLat = lat;
                    mitraLng = lng;
                    const statusText = document.getElementById('gps-status-text');
                    if (statusText) statusText.textContent = 'Simulasi GPS Bergerak';

                    updateConnectedRoute(mitraLat, mitraLng, destLat, destLng);
                }
            });

            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(initMitraRouteMap, 150);
            });

            document.addEventListener('livewire:initialized', () => {
                setTimeout(initMitraRouteMap, 200);
            });

            document.addEventListener('livewire:navigating', () => {
                if (mapInstance) {
                    try { mapInstance.remove(); } catch(e){}
                    mapInstance = null;
                }
                const mapContainer = document.getElementById('mitra-route-map');
                if (mapContainer && mapContainer._leaflet_id) {
                    mapContainer._leaflet_id = null;
                }
            });

            document.addEventListener('livewire:navigated', () => {
                setTimeout(initMitraRouteMap, 200);
            });
        })();
    </script>
@endpush