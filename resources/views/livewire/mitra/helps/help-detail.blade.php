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
            <div class="flex items-center justify-between text-white">
                <button onclick="window.history.back()" aria-label="Kembali"
                    class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <div class="text-center flex-1 min-w-0 px-2">
                    <h1 class="text-base font-bold truncate">Detail Pesanan</h1>
                    <p class="text-xs text-white/90 truncate mt-0.5">Informasi lengkap pesanan Anda</p>
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
    <div class="px-5 pt-5 pb-20 max-w-md mx-auto">
        {{-- GPS Tracker - Auto tracking untuk status aktif --}}
        @if (in_array($help->status, ['memperoleh_mitra', 'taken', 'partner_on_the_way', 'partner_arrived']))
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
                <div class="w-12 h-12 rounded-xl bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center flex-shrink-0 overflow-hidden border border-pink-200/50 dark:border-pink-800/40">
                    @if ($help->photo)
                        <img src="{{ asset('storage/' . $help->photo) }}" alt="{{ $help->title }}"
                            class="w-full h-full object-cover">
                    @else
                        <svg class="w-6 h-6 text-pink-500" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                        </svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="font-bold text-base text-gray-900 dark:text-white truncate leading-snug">{{ $help->title }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium truncate mt-0.5">{{ optional($help->category)->name ?? 'Bantuan Tugas' }}</p>
                </div>
            </div>

            {{-- Commission & Earnings Breakdown (Full Width) --}}
            @if($help->isV2Model())
                <div class="bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/70 rounded-xl p-3.5 space-y-2 mb-3">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-500 dark:text-gray-400 font-medium">Nilai Tugas:</span>
                        <span class="font-bold text-gray-900 dark:text-gray-100">Rp {{ number_format($help->amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-rose-600 dark:text-rose-400 font-medium">Potongan Komisi ({{ $help->getCommissionRateLabel() }}):</span>
                        <span class="font-semibold text-rose-600 dark:text-rose-400">- Rp {{ number_format($help->getPlatformFee(), 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-600/80 pt-2 flex justify-between items-center">
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">Pendapatan Bersih:</span>
                        <span class="text-base font-extrabold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($help->getNetEarning(), 0, ',', '.') }}</span>
                    </div>
                </div>
            @else
                <div class="bg-blue-50/70 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/40 rounded-xl p-3 mb-3 flex items-center justify-between">
                    <span class="text-xs text-gray-600 dark:text-gray-400 font-medium">Nilai Tugas:</span>
                    <span class="text-base font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($help->amount, 0, ',', '.') }}</span>
                </div>
            @endif

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

        {{-- Progress Stepper Card --}}
        <div class="bg-white dark:bg-gray-800 mt-3 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60">
            <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100 dark:border-gray-700/50">
                <div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Progres Pengerjaan</span>
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
                    <div class="h-full bg-blue-600 dark:bg-blue-500 transition-all duration-700 rounded-full"
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
        </div>

        {{-- Card Klarifikasi Laporan Aduan / Sengketa Admin --}}
        @php
            $mitraReport = \App\Models\PartnerReport::where('reported_help_id', $help->id)
                ->latest()
                ->first();
        @endphp
        @if($mitraReport)
            <div class="bg-amber-50 dark:bg-amber-950/40 px-4 py-4 rounded-xl shadow-sm border border-amber-200/80 dark:border-amber-700 mb-3 space-y-3">
                <div class="flex items-start gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-xs font-bold text-amber-950 dark:text-amber-100">Klarifikasi Laporan Aduan (#{{ $mitraReport->id }})</h4>
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ $mitraReport->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-200 text-amber-900' }}">
                                {{ ucfirst($mitraReport->status) }}
                            </span>
                        </div>
                        <p class="text-[11px] text-amber-900/90 dark:text-amber-300 mt-1">
                            Customer mengajukan klaim/aduan pada bantuan ini: <em>"{{ $mitraReport->message }}"</em>
                        </p>
                    </div>
                </div>

                {{-- Tombol Buka Ruang Chat Klarifikasi Khusus --}}
                <div class="pt-2 border-t border-amber-200/60 dark:border-amber-800/60 flex items-center justify-between gap-2 flex-wrap">
                    <div class="text-[11px] text-amber-800 dark:text-amber-300">
                        @php $mitraMsgCount = $mitraReport->messages()->count(); @endphp
                        <span>{{ $mitraMsgCount > 0 ? $mitraMsgCount . ' pesan klarifikasi tersedia' : 'Ruang klarifikasi dengan Admin aktif' }}</span>
                    </div>
                    <a href="{{ route('mitra.chat', ['admin' => 1, 'report' => $mitraReport->id]) }}"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
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
                @if ($help->user->selfie_photo)
                    <img src="{{ asset('storage/' . $help->user->selfie_photo) }}" alt="{{ $help->user->name }}"
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
                <div class="flex items-start gap-2.5">
                    <div class="w-6 h-6 rounded-full bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Titik Tujuan Customer</p>
                        @if ($help->location)
                            <p class="font-bold text-sm text-gray-900 dark:text-white mt-0.5 leading-snug">{{ $help->location }}</p>
                        @endif
                        @if ($help->full_address)
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 leading-relaxed">{{ $help->full_address }}</p>
                        @endif
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            {{ $help->city->name ?? 'Kota Lokasi' }}
                        </p>
                    </div>
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

        {{-- GPS Simulator (toggle via GPS_SIMULATOR env) --}}
        @if (config('app.gps_simulator', true) &&
                $help->mitra_id === auth()->id() &&
                !in_array($help->status, ['selesai', 'dibatalkan']))
            <div class="mb-3">
                <livewire:mitra.gps.simulator :help-id="$help->id" :key="'gps-simulator-' . $help->id" />
            </div>
        @endif

        {{-- Update Status Section --}}
        @if ($help->status === 'partner_arrived')
            <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3">
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
                <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-2">Klik tombol ini setelah Anda sampai di lokasi dan
                    siap memulai pekerjaan</p>
            </div>
        @endif

        @if ($help->status === 'in_progress' || $help->status === 'sedang_diproses')
            <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 mb-3">
                <button wire:click="openCompletionModal"
                    class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2">
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
                $customerReview = $help->rating;
            @endphp

            @if ($customerReview)
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-amber-200/70 dark:border-amber-500/30 shadow-xs mb-3">
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
                 wire:click.stop 
                 style="padding-bottom: env(safe-area-inset-bottom,24px);">
                
                {{-- Header --}}
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 px-5 py-4 rounded-t-3xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Ajukan Pembatalan</h3>
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
                    {{-- Info Icon --}}
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>

                    <p class="text-sm text-gray-700 dark:text-gray-300 text-center mb-3">
                        Tulis alasan singkat kenapa Anda ingin membatalkan bantuan ini. Customer akan menerima permintaan pembatalan dan dapat menyetujui atau menolak.
                    </p>

                    {{-- Catatan Kepatuhan Pembatalan --}}
                    <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl p-3.5 mb-4 text-left">
                        <div class="flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <p class="text-xs font-bold text-amber-900 dark:text-amber-200 mb-0.5">Ketentuan Pembatalan Tugas:</p>
                                <p class="text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                                    Pembatalan tugas yang sudah diambil dapat dicatat oleh sistem. Pelanggaran berulang dapat menyebabkan akun masuk ke <strong>Daftar Abu-Abu</strong> dan menerima <strong>Surat Peringatan (SP)</strong> dari Admin.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Textarea --}}
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-900 dark:text-white mb-2">Alasan Pembatalan</label>
                        <textarea wire:model.defer="partnerCancelReason" 
                                  rows="4" 
                                  class="w-full p-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-red-500 focus:border-red-500 transition"
                                  placeholder="Contoh: kendaraan rusak, alat tidak tersedia, kendala darurat..."></textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">*Opsional - tetapi disarankan mengisi alasan</p>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-3">
                        <button wire:click="$set('showPartnerCancelModal', false)"
                                class="flex-1 px-5 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition">
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
             style="background: rgba(0,0,0,0.6);">
            <div class="bg-white dark:bg-gray-800 rounded-t-3xl w-full max-w-md shadow-2xl animate-slide-up relative" 
                 style="padding-bottom: env(safe-area-inset-bottom,24px);">
                
                {{-- Header --}}
                <div class="sticky top-0 bg-amber-600 px-5 py-4 rounded-t-3xl">
                    <div class="flex items-center justify-between text-white">
                        <h3 class="text-lg font-bold">Menunggu Konfirmasi</h3>
                        <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-5 pb-6">
                    {{-- Icon & Animation --}}
                    <div class="flex items-center justify-center mb-4">
                        <div class="w-20 h-20 bg-yellow-100 dark:bg-yellow-900/40 rounded-full flex items-center justify-center relative">
                            <svg class="w-10 h-10 text-yellow-600 dark:text-yellow-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="absolute -top-1 -right-1 w-6 h-6 bg-yellow-500 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <h4 class="text-center font-bold text-lg text-gray-900 dark:text-white mb-2">Permintaan Pembatalan Terkirim</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300 text-center mb-5">
                        Anda telah mengajukan pembatalan pesanan ini. Menunggu konfirmasi dari customer.
                    </p>

                    {{-- Detail Info --}}
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4 mb-5 space-y-3">
                        @if ($help->partner_cancel_reason)
                            <div>
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Alasan Pembatalan:</p>
                                <p class="text-sm text-gray-900 dark:text-white">{{ $help->partner_cancel_reason }}</p>
                            </div>
                        @endif
                        @if ($help->partner_cancel_requested_at)
                            <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
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
                    <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-800 rounded-lg p-3 mb-5">
                        <div class="flex gap-2">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-xs text-blue-900 dark:text-blue-200">
                                Modal ini akan otomatis update saat customer memberikan konfirmasi. Halaman akan refresh otomatis setiap 5 detik.
                            </p>
                        </div>
                    </div>

                    {{-- Button --}}
                    <a href="{{ route('mitra.helps.all') }}"
                       class="w-full px-5 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition text-center block">
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
            <div class="bg-white dark:bg-gray-800 rounded-t-3xl w-full max-w-md shadow-2xl animate-slide-up relative" 
                 @click.stop
                 style="padding-bottom: env(safe-area-inset-bottom,24px);">
                
                {{-- Header --}}
                <div class="sticky top-0 bg-emerald-600 px-5 py-4 rounded-t-3xl">
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
                        <div class="w-20 h-20 bg-green-100 dark:bg-green-900/40 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>

                    <h4 class="text-center font-bold text-lg text-gray-900 dark:text-white mb-2">Permintaan Diterima!</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300 text-center mb-5">
                        Customer telah menyetujui permintaan pembatalan Anda. Pesanan telah dikembalikan ke daftar bantuan.
                    </p>

                    {{-- Success Message --}}
                    <div class="bg-green-50 dark:bg-green-950/40 border border-green-100 dark:border-green-800 rounded-lg p-4 mb-5">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-green-900 dark:text-green-200 mb-1">Pembatalan Berhasil Disetujui</p>
                                <p class="text-xs text-green-800 dark:text-green-300">Pesanan telah dikembalikan ke sistem agar dapat diambil oleh Rekan Jasa lain.</p>
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
                           class="w-full px-5 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition text-center block">
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
            <div class="bg-white dark:bg-gray-800 rounded-t-3xl w-full max-w-md shadow-2xl animate-slide-up relative" 
                 @click.stop
                 style="padding-bottom: env(safe-area-inset-bottom,24px);">
                
                {{-- Header --}}
                <div class="sticky top-0 bg-rose-600 px-5 py-4 rounded-t-3xl">
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
                        <div class="w-20 h-20 bg-red-100 dark:bg-red-900/40 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                    </div>

                    <h4 class="text-center font-bold text-lg text-gray-900 dark:text-white mb-2">Permintaan Ditolak</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300 text-center mb-5">
                        Customer menolak permintaan pembatalan Anda. Mohon untuk tetap melanjutkan pengerjaan pesanan ini dengan profesional.
                    </p>

                    @if($help->partner_cancel_reason)
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 mb-5">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Alasan Anda:</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300 italic">"{{ $help->partner_cancel_reason }}"</p>
                        </div>
                    @endif

                    {{-- Action Button --}}
                    <button wire:click="acknowledgeRejectedCancellation"
                            class="w-full px-5 py-3 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Saya Mengerti, Lanjutkan Pekerjaan
                    </button>
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

            document.addEventListener('livewire:navigated', () => {
                setTimeout(initMitraRouteMap, 200);
            });
        })();
    </script>
@endpush