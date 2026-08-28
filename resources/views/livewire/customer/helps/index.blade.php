<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors" wire:poll.5s>
    <style>
        :root{
            --brand-500: #0ea5a4;
            --brand-600: #08979a;
            --muted-600: #6b7280;
        }

        [x-cloak] { display: none !important; }

        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
    </style>

    <div class="max-w-md mx-auto">
        <!-- Header Section -->
        <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>
            
            <div class="relative z-10 space-y-3">
                <div class="flex items-center justify-between text-white">
                    <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <div class="text-center flex-1 min-w-0 px-2">
                        <h1 class="text-base font-bold truncate">Permintaan Saya</h1>
                        <p class="text-xs text-white/90 truncate mt-0.5">Kelola permintaan bantuan Anda</p>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        <x-notification-icon :route="route('customer.notifications.index')" />
                    </div>
                </div>

                <!-- Filter Tabs - Segmented Grid (No Overflow) -->
                <div class="grid grid-cols-4 gap-1 bg-black/15 backdrop-blur-md p-1 rounded-xl border border-white/20 text-center">
                    <button type="button" wire:click="$set('statusFilter', 'menunggu_mitra')" role="tab"
                        class="py-2 px-1 rounded-lg text-center font-bold text-[11px] sm:text-xs transition-all cursor-pointer flex items-center justify-center gap-1 {{ $statusFilter === 'menunggu_mitra' ? 'bg-white text-primary-700 shadow-sm' : 'text-white/90 hover:bg-white/10' }}">
                        <span>Menunggu</span>
                        @if(!empty($counts['menunggu']) && $counts['menunggu'] > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $statusFilter === 'menunggu_mitra' ? 'bg-primary-100 text-primary-700' : 'bg-white/20 text-white' }}">{{ $counts['menunggu'] }}</span>
                        @endif
                    </button>
                    <button type="button" wire:click="$set('statusFilter', 'diproses')" role="tab"
                        class="py-2 px-1 rounded-lg text-center font-bold text-[11px] sm:text-xs transition-all cursor-pointer flex items-center justify-center gap-1 {{ $statusFilter === 'diproses' ? 'bg-white text-primary-700 shadow-sm' : 'text-white/90 hover:bg-white/10' }}">
                        <span>Diproses</span>
                        @if(!empty($counts['diproses']) && $counts['diproses'] > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $statusFilter === 'diproses' ? 'bg-primary-100 text-primary-700' : 'bg-white/20 text-white' }}">{{ $counts['diproses'] }}</span>
                        @endif
                    </button>
                    <button type="button" wire:click="$set('statusFilter', 'waiting_customer_confirmation')" role="tab"
                        class="py-2 px-1 rounded-lg text-center font-bold text-[11px] sm:text-xs transition-all cursor-pointer flex items-center justify-center gap-1 {{ $statusFilter === 'waiting_customer_confirmation' ? 'bg-white text-primary-700 shadow-sm' : 'text-white/90 hover:bg-white/10' }}">
                        <span>Konfirmasi</span>
                        @if(!empty($counts['konfirmasi']) && $counts['konfirmasi'] > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $statusFilter === 'waiting_customer_confirmation' ? 'bg-amber-100 text-amber-800' : 'bg-amber-400/80 text-gray-900 font-extrabold' }} animate-pulse">{{ $counts['konfirmasi'] }}</span>
                        @endif
                    </button>
                    <button type="button" wire:click="$set('statusFilter', 'selesai')" role="tab"
                        class="py-2 px-1 rounded-lg text-center font-bold text-[11px] sm:text-xs transition-all cursor-pointer flex items-center justify-center gap-1 {{ $statusFilter === 'selesai' ? 'bg-white text-primary-700 shadow-sm' : 'text-white/90 hover:bg-white/10' }}">
                        <span>Selesai</span>
                        @if(!empty($counts['selesai']) && $counts['selesai'] > 0)
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $statusFilter === 'selesai' ? 'bg-gray-100 text-gray-700' : 'bg-white/20 text-white' }}">{{ $counts['selesai'] }}</span>
                        @endif
                    </button>
                </div>
            </div>
        </div>

        <!-- Content List with Extra Bottom Padding for Float Nav -->
        <div class="px-5 pt-4 pb-36 min-h-[60vh]"> 
            <div class="space-y-3.5 transition-opacity duration-200" wire:loading.class="opacity-50 pointer-events-none" wire:target="statusFilter">
                @forelse($helps as $help)
                    @if($statusFilter === 'selesai')
                        {{-- COMPLETED / CANCELLED CARD (with expandable accordion & rating info) --}}
                        @php
                            $rating = $help->rating;
                            $isCancelled = in_array($help->status, ['dibatalkan', 'cancelled']);
                        @endphp
                        <div x-data="{ open: false }" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs hover:shadow-md transition">
                            <div class="p-4">
                                <div class="flex items-start gap-3 mb-3">
                                    <div class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                                        @if($help->photo)
                                            <img src="{{ asset('storage/' . $help->photo) }}" alt="{{ $help->title }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-sm text-gray-900 dark:text-gray-100 truncate leading-snug">{{ $help->title ?? 'Permintaan Bantuan' }}</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ optional($help->city)->name }} • {{ optional($help->updated_at)->format('d M Y') }}</p>
                                    </div>

                                    <div class="text-right flex-shrink-0">
                                        <div class="text-sm font-bold text-primary-600 dark:text-sky-400">Rp {{ number_format($help->amount ?? 0, 0, ',', '.') }}</div>
                                        <div class="flex flex-col items-end gap-1 mt-1">
                                            @if($isCancelled)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 text-[10px] font-bold border border-rose-200 dark:border-rose-800/50">
                                                    <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Dibatalkan
                                                </span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-[10px] font-medium">
                                                    Refund 100%
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold">
                                                    <svg class="w-2.5 h-2.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    Selesai
                                                </span>
                                                @if($rating)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 text-[10px] font-bold border border-amber-200 dark:border-amber-800/40">
                                                        <svg class="w-3 h-3 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                        <span>{{ number_format($rating->rating, 1) }}</span>
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-[10px] font-medium">
                                                        Belum dinilai
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <button @click="open = !open" class="w-full flex items-center justify-center gap-1.5 text-xs font-semibold py-2 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 text-primary-600 dark:text-sky-400 transition cursor-pointer">
                                    <span x-text="open ? 'Sembunyikan Detail' : '{{ $isCancelled ? 'Lihat Detail Pembatalan' : 'Lihat Detail & Penilaian' }}'"></span>
                                    <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>

                            <div x-show="open" x-cloak x-transition class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700/60 pt-3.5 space-y-3">
                                @if($isCancelled)
                                    <div class="bg-rose-50/80 dark:bg-rose-950/40 border border-rose-200/80 dark:border-rose-900/60 rounded-xl p-3 space-y-1">
                                        <div class="flex items-center gap-1.5 text-xs font-bold text-rose-800 dark:text-rose-300">
                                            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>Permintaan Bantuan Telah Dibatalkan</span>
                                        </div>
                                        <p class="text-[11px] text-rose-700 dark:text-rose-300/90 leading-relaxed">
                                            Permintaan bantuan ini telah dibatalkan (dibatalkan oleh pemesan atau batas waktu pencarian mitra telah berakhir). Dana pembayaran sebesar <strong>Rp {{ number_format($help->total_amount > 0 ? $help->total_amount : $help->amount, 0, ',', '.') }}</strong> telah dikembalikan utuh (100%) ke saldo dompet Anda.
                                        </p>
                                    </div>
                                @endif

                                @if($help->description)
                                    <div>
                                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Deskripsi</h4>
                                        <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed">{{ $help->description }}</p>
                                    </div>
                                @endif

                                <div class="grid grid-cols-2 gap-3 bg-gray-50 dark:bg-gray-750 p-3 rounded-xl">
                                    <div>
                                        <div class="text-[11px] text-gray-400 mb-0.5">Lokasi</div>
                                        <div class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate">{{ $help->full_address ?? optional($help->city)->name ?? '-' }}</div>
                                    </div>

                                    @if($help->mitra)
                                        <div>
                                            <div class="text-[11px] text-gray-400 mb-0.5">Mitra Pelaksana</div>
                                            <div class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">{{ $help->mitra->name }}</div>
                                            @if($help->mitra->phone)
                                                <a href="tel:{{ $help->mitra->phone }}" class="text-[11px] font-semibold text-primary-600 dark:text-sky-400">{{ $help->mitra->phone }}</a>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Rating Section (Only for completed with Mitra) --}}
                                @if(!$isCancelled)
                                    @if($rating)
                                        <div class="bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-800/40 rounded-xl p-3">
                                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                                <span class="text-xs font-bold text-gray-800 dark:text-gray-100">Penilaian Anda untuk Mitra:</span>
                                                <div class="flex items-center gap-1">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <svg class="w-3.5 h-3.5 {{ $i <= $rating->rating ? 'text-yellow-400 fill-current' : 'text-gray-300 dark:text-gray-600' }}" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                        </svg>
                                                    @endfor
                                                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400 ml-1">{{ $rating->rating }}.0</span>
                                                </div>
                                            </div>
                                            @if($rating->review)
                                                <p class="text-xs text-gray-700 dark:text-gray-300 italic">"{{ $rating->review }}"</p>
                                            @endif
                                        </div>
                                    @elseif($help->mitra)
                                        <div class="pt-2">
                                            <a href="{{ route('customer.helps.detail', $help->id) }}" class="w-full flex items-center justify-center gap-1.5 py-2 px-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition shadow-xs">
                                                <span>⭐ Beri Penilaian untuk Mitra</span>
                                            </a>
                                        </div>
                                    @endif
                                @endif

                                <div class="pt-1 flex items-center justify-end">
                                    <a href="{{ route('customer.helps.detail', $help->id) }}" class="px-4 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-650 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-semibold transition">
                                        Halaman Detail Lengkap
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- ACTIVE / PROCESSING / WAITING CONFIRMATION / MENUNGGU CARD --}}
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-xs hover:shadow-md transition-all border border-gray-100 dark:border-gray-700/80 space-y-3">
                            <!-- Top Section: Icon/Photo + Title & Price -->
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-12 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 flex-shrink-0 flex items-center justify-center">
                                    @if($help->photo)
                                        <img src="{{ asset('storage/' . $help->photo) }}" alt="{{ $help->title }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="text-xl">
                                            {{ ['🩺', '🏠', '💡', '🔧', '🎯'][($loop->index) % 5] }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <h3 class="font-bold text-sm text-gray-900 dark:text-gray-100 truncate leading-snug">{{ $help->title }}</h3>
                                        <span class="text-xs font-bold text-primary-600 dark:text-sky-400 whitespace-nowrap shrink-0">Rp {{ number_format($help->amount, 0, ',', '.') }}</span>
                                    </div>

                                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                                        @if($help->status === 'partner_cancel_requested')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                                <span>⚠️</span>
                                                <span>Menunggu Konfirmasi Pembatalan</span>
                                            </span>
                                        @elseif($statusFilter === 'waiting_customer_confirmation' || $help->status === 'waiting_customer_confirmation')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 dark:bg-orange-950/70 text-orange-800 dark:text-orange-300 border border-orange-200 dark:border-orange-800/60">
                                                <span>📸</span>
                                                <span>Menunggu Konfirmasi Anda</span>
                                            </span>
                                        @elseif($statusFilter === 'menunggu_mitra' || $help->status === 'menunggu_mitra')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                                <span>🔍</span>
                                                <span>Mencari Rekan Jasa</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $help->status_color }} dark:bg-opacity-20 border border-current border-opacity-20">
                                                <span>{{ $help->progress_icon }}</span>
                                                <span>{{ $help->progress_summary }}</span>
                                            </span>
                                        @endif

                                        <span class="text-[11px] text-gray-400 dark:text-gray-500">
                                            {{ optional($help->partner_cancel_requested_at ?? $help->created_at)->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Track -->
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                @if($help->status === 'partner_cancel_requested')
                                    <div class="h-full rounded-full bg-amber-500" style="width: 50%;"></div>
                                @elseif($statusFilter === 'waiting_customer_confirmation' || $help->status === 'waiting_customer_confirmation')
                                    <div class="h-full rounded-full bg-gradient-to-r from-orange-500 to-amber-500 animate-pulse" style="width: 90%;"></div>
                                @elseif($statusFilter === 'menunggu_mitra' || $help->status === 'menunggu_mitra')
                                    <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-amber-500 animate-pulse" style="width: 20%;"></div>
                                @else
                                    <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 transition-all duration-500" style="width: {{ $help->progress_percentage }}%;"></div>
                                @endif
                            </div>

                            <!-- Description (Full Width) -->
                            @if($help->description)
                                <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2 leading-relaxed">{{ Str::limit($help->description, 120) }}</p>
                            @endif

                            @if($help->scheduled_at)
                                <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                    <span>📅</span>
                                    <span>{{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }}</span>
                                </div>
                            @endif

                            <!-- Info Bar: Location + Mitra / Chat (Full Width) -->
                            <div class="flex items-center justify-between gap-2 pt-1 border-t border-gray-100 dark:border-gray-700/60 text-xs">
                                <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400 truncate min-w-0">
                                    <span>📍</span>
                                    <span class="truncate font-medium">{{ $help->city->name ?? ($help->full_address ?? '-') }}</span>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    @if($help->mitra)
                                        <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-gray-750 px-2 py-1 rounded-lg border border-gray-200/60 dark:border-gray-700/60">
                                            <div class="w-4 h-4 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-300 flex items-center justify-center text-[10px] font-bold">
                                                {{ strtoupper(substr($help->mitra->name, 0, 1)) }}
                                            </div>
                                            <span class="text-xs font-semibold text-gray-800 dark:text-gray-200 max-w-[90px] truncate">{{ $help->mitra->name }}</span>
                                        </div>

                                        <a href="{{ route('customer.chat', $help->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-600 hover:bg-primary-700 text-white transition shadow-xs cursor-pointer relative" aria-label="Buka chat">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4-.8L3 20l1.2-4A7.963 7.963 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                            </svg>
                                            @if($help->chat_messages_count > 0)
                                                <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-rose-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">
                                                    {{ $help->chat_messages_count }}
                                                </span>
                                            @endif
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Partner Cancellation Alert Box (FULL WIDTH) -->
                            @if($help->status === 'partner_cancel_requested')
                                <div class="p-3.5 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-800/60 space-y-2.5">
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-amber-900 dark:text-amber-200">Mitra Mengajukan Pembatalan</p>
                                            @if($help->partner_cancel_reason)
                                                <p class="text-xs text-amber-800 dark:text-amber-300 italic mt-0.5 leading-snug">"{{ $help->partner_cancel_reason }}"</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 pt-0.5">
                                        <button wire:click="acceptPartnerCancellation({{ $help->id }})" wire:loading.attr="disabled"
                                            class="w-full py-2 px-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer text-center">
                                            Terima & Cari Mitra Lain
                                        </button>
                                        <button wire:click="rejectPartnerCancellation({{ $help->id }})" wire:loading.attr="disabled"
                                            class="w-full py-2 px-2.5 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-bold transition cursor-pointer text-center">
                                            Tolak Pembatalan
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <!-- Action Buttons Footer -->
                            <div class="flex items-center justify-end gap-2 pt-1">
                                @if($statusFilter === 'menunggu_mitra' || $help->status === 'menunggu_mitra')
                                    <button type="button" wire:click.stop="confirmDelete({{ $help->id }})" class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40 rounded-xl text-xs font-bold transition cursor-pointer">
                                        Batalkan
                                    </button>
                                    <button type="button" wire:click.stop="editHelp({{ $help->id }})" class="px-3.5 py-2 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800/40 rounded-xl text-xs font-bold transition cursor-pointer">
                                        Edit
                                    </button>
                                @elseif($statusFilter === 'waiting_customer_confirmation' || $help->status === 'waiting_customer_confirmation')
                                    <button type="button" wire:click.stop="confirmCompletion({{ $help->id }})" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                                        Konfirmasi Selesai
                                    </button>
                                @endif

                                <a href="{{ route('customer.helps.detail', $help->id) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-650 text-gray-700 dark:text-gray-200 border border-gray-200/60 dark:border-gray-600/60 rounded-xl text-xs font-bold transition text-center cursor-pointer">
                                    Detail
                                </a>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs">
                        <div class="w-14 h-14 rounded-full bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center mx-auto mb-3 text-gray-400">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200">Belum Ada Permintaan</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Buat permintaan bantuan baru melalui menu utama</p>
                    </div>
                @endforelse

                <div class="mt-4">
                    {{ $helps->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal (z-[70] to clear floating bottom nav) -->
    @if(isset($editingHelp) && $editingHelp)
        <div class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4" wire:click="closeEdit">
            <div class="bg-white dark:bg-gray-800 rounded-t-3xl sm:rounded-2xl w-full max-w-md shadow-2xl max-h-[85vh] overflow-y-auto hide-scrollbar text-gray-900 dark:text-gray-100 border border-gray-100 dark:border-gray-700" wire:click.stop>
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 px-5 py-4 rounded-t-3xl sm:rounded-t-2xl z-10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit Permintaan</h3>
                        <button type="button" wire:click="closeEdit" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition cursor-pointer">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <form wire:submit.prevent="saveEdit" class="p-5 pb-24 space-y-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Judul Permintaan *</label>
                        <input type="text" wire:model.defer="editTitle" placeholder="Contoh: Bantu bersihkan halaman rumah"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white">
                        @error('editTitle') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Nominal Bantuan (Rp) *</label>
                        <input type="number" wire:model="editAmount" placeholder="10000"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white">
                        @error('editAmount') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Deskripsi Kebutuhan *</label>
                        <textarea wire:model.defer="editDescription" rows="3" placeholder="Jelaskan kebutuhan Anda..."
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white"></textarea>
                        @error('editDescription') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- City select -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Kota *</label>
                        <select wire:model.defer="editCityId" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white">
                            <option value="">Pilih kota / kabupaten</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}@if($city->province), {{ $city->province }}@endif</option>
                            @endforeach
                        </select>
                        @error('editCityId') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Full Address -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Alamat Lengkap</label>
                        <textarea wire:model.defer="editFullAddress" rows="2" placeholder="Alamat lengkap dengan patokan..."
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-900 dark:text-white"></textarea>
                    </div>

                    <!-- Action Buttons (Sticky Footer) -->
                    <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 pt-3 -mx-5 px-5 -mb-5 pb-5 mt-4">
                        <div class="flex gap-2">
                            <button type="button" wire:click="closeEdit" 
                                class="flex-1 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-bold text-xs hover:bg-gray-200 transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-bold text-xs transition shadow-md cursor-pointer disabled:opacity-50">
                                <span wire:loading.remove wire:target="saveEdit">Simpan Perubahan</span>
                                <span wire:loading wire:target="saveEdit">Menyimpan...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal (Centered Modal with z-[70] for Zero Interference) -->
    @if($showDeleteConfirm)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs" wire:click="cancelDelete">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-sm shadow-2xl overflow-hidden text-gray-900 dark:text-gray-100 border border-gray-100 dark:border-gray-700 animate-in fade-in zoom-in duration-150" wire:click.stop>
                <div class="p-6 text-center space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-rose-100 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto shadow-xs">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Batalkan Permintaan?</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                            Anda yakin ingin membatalkan permintaan bantuan ini? Dana yang ditahan di sistem akan otomatis dikembalikan 100% ke saldo Anda.
                        </p>
                    </div>

                    <div class="flex gap-2.5 pt-2">
                        <button type="button" wire:click="cancelDelete" class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-650 text-gray-700 dark:text-gray-200 rounded-xl font-bold text-xs transition cursor-pointer">
                            Kembali
                        </button>
                        <button type="button" wire:click="deleteConfirmed" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs transition shadow-md cursor-pointer">
                            Ya, Batalkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Confirmation Modal for Completing Help (Centered Modal with z-[70]) --}}
    @if($confirmingHelpId)
        <div data-confirm-modal class="fixed inset-0 z-[70] bg-black/60 backdrop-blur-xs flex items-center justify-center p-4" wire:click="$set('confirmingHelpId', null)">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-sm w-full p-6 text-gray-900 dark:text-gray-100 border border-gray-100 dark:border-gray-700 animate-in fade-in zoom-in duration-150 text-center space-y-4" wire:click.stop>
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto shadow-xs">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Selesaikan Pesanan?</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                        Dengan mengonfirmasi, pesanan dinyatakan selesai dan dana yang ditahan di sistem akan diteruskan kepada Rekan Jasa.
                    </p>
                </div>

                <div class="flex gap-2.5 pt-2">
                    <button wire:click="$set('confirmingHelpId', null)" 
                            class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-650 text-gray-700 dark:text-gray-200 rounded-xl font-bold text-xs transition cursor-pointer">
                        Batal
                    </button>
                    <button wire:click="completeConfirmed" 
                            class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs transition shadow-md cursor-pointer">
                        Ya, Selesaikan
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>