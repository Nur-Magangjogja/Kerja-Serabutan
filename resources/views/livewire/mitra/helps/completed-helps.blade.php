<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors">
    <div class="max-w-md mx-auto">
        <!-- Header Section -->
        <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>
            
            <div class="relative z-10 space-y-3">
                <div class="relative flex items-center justify-center min-h-[40px] text-white">
                    <div class="text-center w-full min-w-0 px-12">
                        <h1 class="text-base font-bold truncate">Riwayat Bantuan</h1>
                        <p class="text-xs text-white/90 truncate mt-0.5">Bantuan selesai & catatan pembatalan</p>
                    </div>

                    <div class="absolute right-0 top-1/2 -translate-y-1/2 z-20 flex items-center">
                        <x-mitra.notification-icon />
                    </div>
                </div>

                {{-- Stats Cards --}}
                <div class="grid grid-cols-3 gap-2 pt-1">
                    <div class="bg-white/15 backdrop-blur-md rounded-xl p-2.5 text-center border border-white/20">
                        <div class="text-lg font-extrabold text-white leading-tight">{{ $totalCompletedCount }}</div>
                        <div class="text-[10px] text-white/85 mt-0.5">Selesai</div>
                    </div>

                    <div class="bg-white/15 backdrop-blur-md rounded-xl p-2.5 text-center border border-white/20">
                        <div class="text-[10px] text-white/85 mb-0.5">Total Nilai</div>
                        <div class="text-xs font-bold text-white leading-tight truncate">Rp {{ number_format($totalCompletedAmount, 0, ',', '.') }}</div>
                    </div>

                    <div class="bg-white/15 backdrop-blur-md rounded-xl p-2.5 text-center border border-white/20">
                        <div class="text-lg font-extrabold text-white leading-tight">{{ $totalCancelledCount }}</div>
                        <div class="text-[10px] text-white/85 mt-0.5">Pembatalan</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sub-filter Switcher -->
        <div class="px-5 pt-4 space-y-3">
            <div class="flex items-center gap-2 bg-gray-200/70 dark:bg-gray-800 p-1 rounded-xl">
                <button wire:click="setTab('completed')" class="flex-1 py-2 rounded-lg text-xs font-bold transition cursor-pointer flex items-center justify-center gap-1.5 {{ $activeTab === 'completed' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800' }}">
                    <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Selesai ({{ $totalCompletedCount }})</span>
                </button>
                <button wire:click="setTab('cancelled')" class="flex-1 py-2 rounded-lg text-xs font-bold transition cursor-pointer flex items-center justify-center gap-1.5 {{ $activeTab === 'cancelled' ? 'bg-white dark:bg-gray-700 text-rose-600 dark:text-rose-400 shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800' }}">
                    <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Pembatalan ({{ $totalCancelledCount }})</span>
                </button>
            </div>

            <!-- Search & Sort Filter Bar -->
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="{{ $activeTab === 'completed' ? 'Cari judul, customer, deskripsi...' : 'Cari judul, alasan, catatan...' }}" 
                        class="w-full pl-8 pr-8 py-2 text-xs rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:focus:ring-sky-500/30 text-gray-800 dark:text-gray-200 placeholder-gray-400 shadow-2xs"
                    >
                    @if($search)
                        <button 
                            wire:click="$set('search', '')" 
                            class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>

                <div class="shrink-0">
                    <select 
                        wire:model.live="sortBy" 
                        class="text-xs py-2 px-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-medium focus:outline-none focus:ring-2 focus:ring-primary-500/30 cursor-pointer shadow-2xs"
                    >
                        <option value="latest">Terbaru</option>
                        <option value="oldest">Terlama</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="px-5 pt-3 pb-24">

            {{-- TAB 1: BANTUAN SELESAI --}}
            @if($activeTab === 'completed')
                @if(isset($helps) && $helps->isEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 p-8 text-center mt-2 shadow-xs">
                        <div class="w-14 h-14 rounded-full bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center mx-auto mb-3 text-gray-400">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">
                            {{ $search ? 'Tidak ada bantuan selesai yang cocok' : 'Belum Ada Riwayat Bantuan Selesai' }}
                        </h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            {{ $search ? 'Coba ubah kata kunci pencarian Anda' : 'Bantuan yang telah Anda selesaikan akan dicatat di sini' }}
                        </p>
                    </div>
                @elseif(isset($helps))
                    <div class="space-y-3">
                        @foreach($helps as $help)
                            @php
                                $rating = $help->rating;
                            @endphp
                            <div x-data="{ isExpanded: false }" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs hover:shadow-md transition">
                                <div class="p-4">
                                    <div class="flex items-start gap-3 mb-3">
                                        <div class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                                            @if($help->photo)
                                                <img src="{{ asset('storage/' . $help->photo) }}" alt="{{ $help->title }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-400 bg-sky-50 dark:bg-sky-950/40">
                                                    @if($help->service_type === 'pickup_delivery')
                                                        @if($help->service_category === 'passenger')
                                                            <span class="text-lg">👥</span>
                                                        @else
                                                            <span class="text-lg">📦</span>
                                                        @endif
                                                    @else
                                                        <span class="text-lg">🛠️</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                                @if($help->service_type === 'pickup_delivery')
                                                    @if($help->service_category === 'passenger')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                                            👥 Antar Penumpang
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 dark:bg-sky-950/70 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                                                            📦 Barang & Dokumen
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                        🛠️ Kerja di Lokasi
                                                    </span>
                                                @endif
                                            </div>
                                            <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm truncate leading-snug">{{ $help->title ?? 'Permintaan Bantuan' }}</h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                                {{ optional($help->city)->name ?? '-' }} • {{ optional($help->updated_at)->translatedFormat('d M Y') }}
                                            </p>
                                        </div>

                                        <div class="text-right flex-shrink-0">
                                            <div class="text-sm font-bold text-primary-600 dark:text-sky-400">Rp {{ number_format($help->getNetEarning(), 0, ',', '.') }}</div>
                                            <div class="flex flex-col items-end mt-1 gap-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold">
                                                    <svg class="w-2.5 h-2.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    Selesai
                                                </span>

                                                {{-- Rating status from Customer --}}
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
                                            </div>
                                        </div>
                                    </div>

                                    <button @click="isExpanded = !isExpanded" class="w-full flex items-center justify-center gap-1.5 text-xs font-semibold py-2 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 text-primary-600 dark:text-sky-400 transition cursor-pointer">
                                        <span x-text="isExpanded ? 'Sembunyikan Detail' : 'Lihat Detail & Ulasan Customer'"></span>
                                        <svg :class="isExpanded ? 'rotate-180' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>

                                <div x-show="isExpanded" x-cloak x-transition class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700/60 pt-3.5 space-y-3">
                                    @if($help->scheduled_at)
                                        <div class="bg-gray-50 dark:bg-gray-750 p-2.5 rounded-xl text-xs flex items-center justify-between border border-gray-100 dark:border-gray-700/60">
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">Jadwal Keberangkatan:</span>
                                            <span class="font-bold text-gray-800 dark:text-gray-200">📅 {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }}</span>
                                        </div>
                                    @endif

                                    @if($help->description)
                                        <div>
                                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Deskripsi Tugas</h4>
                                            <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed">{{ $help->description }}</p>
                                        </div>
                                    @endif

                                    {{-- Rute atau Lokasi --}}
                                    @if($help->service_type === 'pickup_delivery')
                                        <div class="bg-gray-50 dark:bg-gray-750 p-3 rounded-xl space-y-2 text-xs">
                                            <div class="flex items-center justify-between font-bold text-gray-700 dark:text-gray-300 text-[11px]">
                                                <span>🗺️ Rute {{ $help->service_category === 'passenger' ? 'Antar Penumpang' : 'Pengantaran' }}</span>
                                                @if($help->service_route_distance_km)
                                                    <span class="text-indigo-600 dark:text-indigo-400 font-semibold">±{{ $help->service_route_distance_km }} km</span>
                                                @endif
                                            </div>
                                            <div class="flex items-start gap-2">
                                                <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[9px] shrink-0 mt-0.5">1</span>
                                                <div class="min-w-0 flex-1">
                                                    <span class="text-[10px] uppercase font-bold text-gray-400 block">{{ $help->service_category === 'passenger' ? 'Jemput Penumpang' : 'Ambil Barang / Dokumen' }}</span>
                                                    <p class="text-gray-800 dark:text-gray-200 font-medium truncate">{{ $help->pickup_address ?: ($help->location ?: '-') }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-2">
                                                <span class="w-4 h-4 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[9px] shrink-0 mt-0.5">2</span>
                                                <div class="min-w-0 flex-1">
                                                    <span class="text-[10px] uppercase font-bold text-gray-400 block">{{ $help->service_category === 'passenger' ? 'Tujuan Turun Penumpang' : 'Tujuan Antar' }}</span>
                                                    <p class="text-gray-800 dark:text-gray-200 font-medium truncate">{{ $help->delivery_address ?: ($help->full_address ?: '-') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="grid grid-cols-2 gap-3 bg-gray-50 dark:bg-gray-750 p-3 rounded-xl">
                                            <div>
                                                <div class="text-[11px] text-gray-400 dark:text-gray-400 mb-0.5">Lokasi</div>
                                                <div class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate">{{ $help->full_address ?? optional($help->city)->name ?? '-' }}</div>
                                            </div>

                                            @if($help->equipment_provided)
                                                <div>
                                                    <div class="text-[11px] text-gray-400 dark:text-gray-400 mb-0.5">Peralatan</div>
                                                    <div class="text-xs font-medium text-emerald-600 dark:text-emerald-400 truncate">✓ {{ $help->equipment_provided }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if($help->user)
                                        <div class="bg-gray-50 dark:bg-gray-750 p-2.5 rounded-xl flex items-center justify-between text-xs">
                                            <div>
                                                <div class="text-[10px] text-gray-400 mb-0.5">Customer</div>
                                                <div class="font-bold text-gray-800 dark:text-gray-200 truncate">{{ $help->user->name }}</div>
                                            </div>
                                            @if($help->user->phone)
                                                <a href="tel:{{ $help->user->phone }}" class="text-[11px] font-semibold text-primary-600 dark:text-sky-400 hover:underline">📞 {{ $help->user->phone }}</a>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Bukti Foto Selesai --}}
                                    @if($help->proof_photo)
                                        <div class="pt-1">
                                            <div class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 mb-1">Foto Bukti Pengerjaan:</div>
                                            <div class="w-24 h-24 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-black/5">
                                                <img src="{{ asset('storage/' . $help->proof_photo) }}" alt="Bukti Pengerjaan" class="w-full h-full object-cover">
                                            </div>
                                            @if($help->completion_notes)
                                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 italic">"{{ $help->completion_notes }}"</p>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Customer rating & review received by Mitra --}}
                                    @if($rating)
                                        <div class="bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/60 dark:border-amber-800/40 rounded-xl p-3">
                                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                                <span class="text-xs font-bold text-gray-800 dark:text-gray-100 flex items-center gap-1">
                                                    <span>Ulasan dari {{ optional($help->user)->name ?? 'Customer' }}:</span>
                                                </span>
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
                                                <p class="text-xs text-gray-700 dark:text-gray-200 italic">"{{ $rating->review }}"</p>
                                            @else
                                                <p class="text-[11px] text-gray-400 dark:text-gray-500 italic">Customer memberikan rating tanpa ulasan teks.</p>
                                            @endif
                                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1.5 text-right">
                                                Dinilai pada {{ $rating->created_at ? $rating->created_at->format('d M Y • H:i') : '-' }}
                                            </p>
                                        </div>
                                    @else
                                        <div class="bg-gray-50 dark:bg-gray-750 p-2.5 rounded-xl text-center text-xs text-gray-500 dark:text-gray-400">
                                            Customer belum memberikan rating & ulasan untuk pekerjaan ini.
                                        </div>
                                    @endif

                                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700/60">
                                        <a href="{{ route('mitra.helps.detail', $help->id) }}" class="w-full py-2.5 px-3 bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 border border-emerald-200 dark:border-emerald-800/60">
                                            <span>Buka Halaman Detail Lengkap</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-4 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs">
                            {{ $helps->links('vendor.pagination.superadmin') }}
                        </div>
                    </div>
                @endif
            @endif

            {{-- TAB 2: PEMBATALAN TUGAS --}}
            @if($activeTab === 'cancelled')
                @if(isset($cancellations) && $cancellations->isEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 p-8 text-center mt-2 shadow-xs">
                        <div class="w-14 h-14 rounded-full bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center mx-auto mb-3 text-emerald-500">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">
                            {{ $search ? 'Tidak ada riwayat pembatalan yang cocok' : 'Tidak Ada Riwayat Pembatalan' }}
                        </h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            {{ $search ? 'Coba ubah kata kunci pencarian Anda' : 'Bagus! Anda belum memiliki catatan pembatalan tugas bantuan.' }}
                        </p>
                    </div>
                @elseif(isset($cancellations))
                    <div class="space-y-3" x-data="{ previewPhotoUrl: null }">
                        @foreach($cancellations as $item)
                            @php
                                $help = $item->help;
                                $customer = $item->customer ?? $help?->user;
                                $evidencePhoto = $item->evidence_photo ?: $help?->cancel_evidence_photo;

                                $statusBadge = match($item->status) {
                                    'pending'   => ['class' => 'bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border-amber-300 dark:border-amber-700', 'label' => '⏳ Menunggu Audit'],
                                    'approved'  => ['class' => 'bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border-rose-300 dark:border-rose-700', 'label' => '❌ Dibatalkan'],
                                    'rejected'  => ['class' => 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700', 'label' => '✅ Ditolak (Lanjut)'],
                                    default     => ['class' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300', 'label' => ucfirst($item->status)]
                                };

                                $settlementLabel = match($item->settlement_type) {
                                    'full_refund'        => 'Pengembalian Dana Penuh 100% ke Customer',
                                    'partial_settlement' => 'Penyelesaian Kompensasi Sebagian (Proporsional)',
                                    'item_settled'       => 'Penyelesaian Biaya Tambahan/Operasional Mitra',
                                    'no_refund'          => 'Dana Diteruskan Penuh ke Mitra (Tanpa Refund)',
                                    'relist_pool'        => 'Tugas Dilempar Kembali ke Pool Mitra Lain',
                                    default              => null
                                };
                            @endphp
                            <div x-data="{ isExpanded: false }" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs hover:shadow-md transition">
                                <div class="p-4">
                                    {{-- Header Card --}}
                                    <div class="flex items-start gap-3 mb-3">
                                        {{-- Thumbnail Image Box (Properly closed div) --}}
                                        <div class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                                            @if($help?->photo)
                                                <img src="{{ asset('storage/' . $help->photo) }}" alt="{{ $help->title }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-rose-400 bg-rose-50 dark:bg-rose-950/40">
                                                    @if($help?->service_type === 'pickup_delivery')
                                                        @if($help?->service_category === 'passenger')
                                                            <span class="text-lg">👥</span>
                                                        @else
                                                            <span class="text-lg">📦</span>
                                                        @endif
                                                    @else
                                                        <span class="text-lg">🛠️</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Middle Info --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                                @if($help?->service_type === 'pickup_delivery')
                                                    @if($help?->service_category === 'passenger')
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                                            👥 Antar Penumpang
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 dark:bg-sky-950/70 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                                                            📦 Barang & Dokumen
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                        🛠️ Kerja di Lokasi
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm truncate leading-snug">
                                                {{ $help?->title ?? 'Pembatalan Tugas Bantuan' }}
                                            </h3>
                                            
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                                                {{ optional($help?->city)->name ?? 'Kota' }} • {{ ($item->requested_at ?? $item->created_at)->translatedFormat('d M Y') }}
                                            </p>
                                        </div>

                                        {{-- Right Side Info --}}
                                        <div class="text-right flex-shrink-0">
                                            @if($help)
                                                <div class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                                    Rp {{ number_format($help->getNetEarning(), 0, ',', '.') }}
                                                </div>
                                            @endif
                                            <div class="flex flex-col items-end mt-1 gap-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge['class'] }}">
                                                    {{ $statusBadge['label'] }}
                                                </span>

                                                @if($item->partner_sp_level > 0)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 text-[10px] font-bold border border-rose-300 dark:border-rose-700">
                                                        ⚠️ SP {{ $item->partner_sp_level }}
                                                    </span>
                                                @elseif($item->audit_decision === 'valid_no_sp' || ($item->status === 'approved' && $item->sp_target === 'none'))
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold border border-emerald-200 dark:border-emerald-800/40">
                                                        🛡️ Bebas Sanksi
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Alasan Ringkas Bar --}}
                                    <div class="bg-gray-50 dark:bg-gray-750/70 border border-gray-100 dark:border-gray-700/60 rounded-xl p-2.5 mb-3 text-xs space-y-1.5 text-left">
                                        <div class="flex items-start gap-1.5 text-left">
                                            <span class="text-gray-400 dark:text-gray-500 font-medium shrink-0 w-16 text-[11px]">Pemohon:</span>
                                            <span class="font-bold text-left text-[11px] {{ $item->requester_type === 'partner' ? 'text-blue-600 dark:text-blue-400' : 'text-purple-600 dark:text-purple-400' }}">
                                                {{ $item->requester_type === 'partner' ? 'Diajukan oleh Anda (Mitra)' : 'Diajukan oleh Customer' }}
                                            </span>
                                        </div>
                                        <div class="flex items-start gap-1.5 text-left text-gray-800 dark:text-gray-200">
                                            <span class="text-gray-400 dark:text-gray-500 font-medium shrink-0 w-16 text-[11px]">Alasan:</span>
                                            <span class="font-semibold text-rose-600 dark:text-rose-400 break-words flex-1 text-left text-xs">{{ $item->reason }}</span>
                                        </div>
                                    </div>

                                    {{-- Toggle Accordion Button --}}
                                    <button @click="isExpanded = !isExpanded" class="w-full flex items-center justify-center gap-1.5 text-xs font-semibold py-2 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 text-rose-600 dark:text-rose-400 transition cursor-pointer">
                                        <span x-text="isExpanded ? 'Sembunyikan Rincian' : 'Lihat Detail Pembatalan Lengkap'"></span>
                                        <svg :class="isExpanded ? 'rotate-180' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>

                                {{-- Expanded Full Details --}}
                                <div x-show="isExpanded" x-cloak x-transition class="px-4 pb-4 border-t border-gray-100 dark:border-gray-700/60 pt-3.5 space-y-3">
                                    
                                    {{-- 1. Info Tugas & Customer --}}
                                    <div class="space-y-2">
                                        @if($help?->scheduled_at)
                                            <div class="bg-gray-50 dark:bg-gray-750 p-2.5 rounded-xl text-xs flex items-center justify-between border border-gray-100 dark:border-gray-700/60">
                                                <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">Jadwal Keberangkatan:</span>
                                                <span class="font-bold text-gray-800 dark:text-gray-200">📅 {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }}</span>
                                            </div>
                                        @endif

                                        @if($help?->description)
                                            <div>
                                                <h4 class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-400 mb-1">Deskripsi Tugas</h4>
                                                <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed bg-gray-50 dark:bg-gray-750 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700/40">
                                                    {{ $help->description }}
                                                </p>
                                            </div>
                                        @endif

                                        {{-- Rute atau Lokasi --}}
                                        @if($help?->service_type === 'pickup_delivery')
                                            <div class="bg-gray-50 dark:bg-gray-750 p-3 rounded-xl space-y-2 text-xs">
                                                <div class="flex items-center justify-between font-bold text-gray-700 dark:text-gray-300 text-[11px]">
                                                    <span>🗺️ Rute {{ $help->service_category === 'passenger' ? 'Antar Penumpang' : 'Pengantaran' }}</span>
                                                    @if($help->service_route_distance_km)
                                                        <span class="text-indigo-600 dark:text-indigo-400 font-semibold">±{{ $help->service_route_distance_km }} km</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-start gap-2">
                                                    <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[9px] shrink-0 mt-0.5">1</span>
                                                    <div class="min-w-0 flex-1">
                                                        <span class="text-[10px] uppercase font-bold text-gray-400 block">{{ $help->service_category === 'passenger' ? 'Jemput Penumpang' : 'Ambil Barang / Dokumen' }}</span>
                                                        <p class="text-gray-800 dark:text-gray-200 font-medium break-words">{{ $help->pickup_address ?: ($help->location ?: '-') }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-start gap-2">
                                                    <span class="w-4 h-4 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[9px] shrink-0 mt-0.5">2</span>
                                                    <div class="min-w-0 flex-1">
                                                        <span class="text-[10px] uppercase font-bold text-gray-400 block">{{ $help->service_category === 'passenger' ? 'Tujuan Turun Penumpang' : 'Tujuan Antar' }}</span>
                                                        <p class="text-gray-800 dark:text-gray-200 font-medium break-words">{{ $help->delivery_address ?: ($help->full_address ?: '-') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="grid grid-cols-2 gap-2 bg-gray-50 dark:bg-gray-750 p-2.5 rounded-xl text-xs">
                                                <div>
                                                    <div class="text-[10px] text-gray-400 dark:text-gray-400 uppercase font-semibold">Lokasi</div>
                                                    <div class="font-medium text-gray-800 dark:text-gray-200 truncate mt-0.5">
                                                        {{ $help?->full_address ?? optional($help?->city)->name ?? '-' }}
                                                    </div>
                                                </div>

                                                @if($customer)
                                                    <div>
                                                        <div class="text-[10px] text-gray-400 dark:text-gray-400 uppercase font-semibold">Customer</div>
                                                        <div class="font-bold text-gray-800 dark:text-gray-200 truncate mt-0.5">
                                                            {{ $customer->name }}
                                                        </div>
                                                        @if($customer->phone)
                                                            <a href="tel:{{ $customer->phone }}" class="text-[11px] font-semibold text-primary-600 dark:text-sky-400 hover:underline inline-block mt-0.5">
                                                                📞 {{ $customer->phone }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    {{-- 2. Detail Pengajuan Pembatalan --}}
                                    <div class="bg-rose-50/70 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/40 rounded-xl p-3 text-xs space-y-2">
                                        <div class="flex items-center justify-between pb-1.5 border-b border-rose-100/70 dark:border-rose-900/30">
                                            <span class="font-bold text-rose-900 dark:text-rose-200 flex items-center gap-1">
                                                <span>📋 Detail Pengajuan Pembatalan</span>
                                            </span>
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400">
                                                {{ ($item->requested_at ?? $item->created_at)->translatedFormat('d M Y • H:i') }}
                                            </span>
                                        </div>

                                        <div>
                                            <div class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">Alasan Resmi:</div>
                                            <div class="font-bold text-rose-900 dark:text-rose-200 mt-0.5 break-words">
                                                {{ $item->reason }}
                                            </div>
                                        </div>

                                        @if($item->notes)
                                            <div>
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">Catatan Pengaju:</div>
                                                <div class="text-gray-700 dark:text-gray-300 italic mt-0.5 bg-white/60 dark:bg-gray-800/60 p-2 rounded-lg border border-rose-100/60 dark:border-rose-900/30 leading-relaxed">
                                                    "{{ $item->notes }}"
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Foto Bukti Pengajuan --}}
                                        @if($evidencePhoto)
                                            <div class="pt-1">
                                                <div class="text-[11px] font-semibold text-gray-600 dark:text-gray-300 mb-1">Foto Bukti Pengajuan:</div>
                                                <div class="w-20 h-20 rounded-xl overflow-hidden border border-rose-200 dark:border-rose-800/60 cursor-pointer hover:opacity-90 transition relative group shadow-2xs"
                                                     @click="previewPhotoUrl = '{{ asset('storage/' . $evidencePhoto) }}'">
                                                    <img src="{{ asset('storage/' . $evidencePhoto) }}" alt="Bukti Pembatalan" class="w-full h-full object-cover">
                                                    <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                        </svg>
                                                    </div>
                                                </div>
                                                <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5 block">Ketuk gambar untuk memperbesar</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- 3. Tanggapan / Klarifikasi Mitra (jika diajukan oleh customer) --}}
                                    @if($item->requester_type === 'customer')
                                        <div class="bg-blue-50/70 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/40 rounded-xl p-3 text-xs space-y-1.5">
                                            <div class="flex items-center justify-between pb-1 border-b border-blue-100/70 dark:border-blue-900/30">
                                                <span class="font-bold text-blue-900 dark:text-blue-200">🗣️ Tanggapan / Klarifikasi Anda</span>
                                                @if($item->partner_clarified_at)
                                                    <span class="text-[10px] text-gray-500 dark:text-gray-400">
                                                        {{ $item->partner_clarified_at->translatedFormat('d M Y • H:i') }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($item->partner_clarification)
                                                <p class="text-gray-800 dark:text-gray-200 italic leading-relaxed bg-white/60 dark:bg-gray-800/60 p-2 rounded-lg border border-blue-100/60 dark:border-blue-900/30">
                                                    "{{ $item->partner_clarification }}"
                                                </p>
                                            @else
                                                <p class="text-[11px] text-gray-500 dark:text-gray-400 italic">
                                                    Anda tidak mengirimkan tanggapan tertulis saat proses klarifikasi.
                                                </p>
                                            @endif

                                            @if($item->partner_clarification_photo)
                                                <div class="pt-1">
                                                    <div class="w-16 h-16 rounded-xl overflow-hidden border border-blue-200 dark:border-blue-800/60 cursor-pointer hover:opacity-90 transition shadow-2xs"
                                                         @click="previewPhotoUrl = '{{ asset('storage/' . $item->partner_clarification_photo) }}'">
                                                        <img src="{{ asset('storage/' . $item->partner_clarification_photo) }}" alt="Foto Klarifikasi" class="w-full h-full object-cover">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- 4. Status Pengerjaan (Hanya tampil jika ada progres) --}}
                                    @if($item->work_completed_percentage > 0)
                                        <div class="bg-gray-50 dark:bg-gray-750 p-2.5 rounded-xl text-xs flex items-center justify-between">
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">Progres Pengerjaan:</span>
                                            <span class="font-bold text-gray-800 dark:text-gray-200">
                                                {{ number_format($item->work_completed_percentage, 0) }}% Selesai
                                            </span>
                                        </div>
                                    @endif

                                    {{-- 5. Keputusan & Catatan Audit Admin --}}
                                    <div class="bg-indigo-50/70 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 rounded-xl p-3 text-xs space-y-2">
                                        <div class="flex items-center justify-between pb-1.5 border-b border-indigo-100/70 dark:border-indigo-900/30">
                                            <span class="font-bold text-indigo-950 dark:text-indigo-200 flex items-center gap-1">
                                                <span>⚖️ Keputusan & Audit Admin</span>
                                            </span>
                                            @if($item->reviewed_at)
                                                <span class="text-[10px] text-gray-500 dark:text-gray-400">
                                                    Ditinjau: {{ $item->reviewed_at->translatedFormat('d M Y • H:i') }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($item->admin_notes)
                                            <div>
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400 font-semibold">Catatan Keputusan:</div>
                                                <div class="text-gray-800 dark:text-gray-200 mt-0.5 leading-relaxed bg-white/60 dark:bg-gray-800/60 p-2 rounded-lg border border-indigo-100/60 dark:border-indigo-900/30">
                                                    {{ $item->admin_notes }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-[11px] text-gray-500 dark:text-gray-400 italic">
                                                {{ $item->status === 'pending' ? 'Pengajuan ini sedang menunggu peninjauan dan investigasi oleh Admin Wilayah.' : 'Tidak ada catatan audit tertulis.' }}
                                            </div>
                                        @endif

                                        {{-- Sanksi / SP Information --}}
                                        @if($item->partner_sp_level > 0)
                                            <div class="p-2.5 bg-rose-100/90 dark:bg-rose-950/60 rounded-xl text-rose-900 dark:text-rose-200 font-medium border border-rose-200 dark:border-rose-900/50">
                                                <div class="font-bold flex items-center gap-1">
                                                    <span>⚠️ Sanksi Diterima: SP {{ $item->partner_sp_level }}</span>
                                                </div>
                                                @if($item->partner_sp_reason)
                                                    <span class="font-normal block text-[11px] mt-0.5 text-rose-800 dark:text-rose-300">Alasan sanksi: {{ $item->partner_sp_reason }}</span>
                                                @endif
                                            </div>
                                        @elseif($item->status === 'approved' && ($item->audit_decision === 'valid_no_sp' || $item->sp_target === 'none'))
                                            <div class="p-2.5 bg-emerald-100/90 dark:bg-emerald-950/60 rounded-xl text-emerald-900 dark:text-emerald-200 font-medium text-[11px] border border-emerald-200 dark:border-emerald-900/50">
                                                <span class="font-bold">✅ Bebas Sanksi:</span> Pembatalan dinilai sah karena kendala di lapangan. Tidak ada sanksi SP yang diberikan ke akun Anda.
                                            </div>
                                        @endif

                                        @if($item->reviewedBy)
                                            <div class="text-[10px] text-gray-400 dark:text-gray-500 text-right pt-1">
                                                Diverifikasi oleh: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $item->reviewedBy->name }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- 6. Penyelesaian Dana Keuangan (Settlement) - Khusus Layanan Antar Jemput --}}
                                    @if(($help?->service_type === 'pickup_delivery' || ($help && method_exists($help, 'isPickup') && $help->isPickup())) && ($settlementLabel || $item->payout_amount_mitra > 0 || $item->refund_amount_customer > 0))
                                        <div class="bg-gray-50 dark:bg-gray-750 p-3 rounded-xl text-xs space-y-2 border border-gray-100 dark:border-gray-700/60">
                                            <div class="font-bold text-gray-700 dark:text-gray-200 text-[11px] flex items-center gap-1">
                                                <span>💰 Penyelesaian Finansial:</span>
                                            </div>
                                            @if($settlementLabel)
                                                <div class="text-[11px] text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200/60 dark:border-gray-700/60 font-medium">
                                                    {{ $settlementLabel }}
                                                </div>
                                            @endif
                                            <div class="grid grid-cols-2 gap-2 pt-1 border-t border-gray-200/60 dark:border-gray-700/60 text-xs">
                                                <div class="bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200/60 dark:border-gray-700/60">
                                                    <span class="text-gray-400 text-[10px] block">Kompensasi Mitra:</span>
                                                    <span class="font-bold text-sm block mt-0.5 {{ $item->payout_amount_mitra > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-400' }}">
                                                        Rp {{ number_format($item->payout_amount_mitra, 0, ',', '.') }}
                                                    </span>
                                                </div>
                                                <div class="bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200/60 dark:border-gray-700/60 text-right">
                                                    <span class="text-gray-400 text-[10px] block">Refund Customer:</span>
                                                    <span class="font-bold text-sm block mt-0.5 {{ $item->refund_amount_customer > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-600 dark:text-gray-400' }}">
                                                        Rp {{ number_format($item->refund_amount_customer, 0, ',', '.') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($help)
                                        <div class="pt-2 border-t border-gray-100 dark:border-gray-700/60">
                                            <a href="{{ route('mitra.helps.detail', $help->id) }}" class="w-full py-2.5 px-3 bg-rose-50 dark:bg-rose-950/50 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-rose-700 dark:text-rose-300 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 border border-rose-200 dark:border-rose-800/60">
                                                <span>Buka Halaman Detail Lengkap</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        {{-- Pagination --}}
                        <div class="mt-4 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs">
                            {{ $cancellations->links('vendor.pagination.superadmin') }}
                        </div>

                        {{-- Lightbox Photo Modal --}}
                        <div x-show="previewPhotoUrl" 
                             x-cloak 
                             @click.self="previewPhotoUrl = null"
                             class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0">
                            <div class="relative max-w-lg w-full bg-transparent p-2 text-center">
                                <button @click="previewPhotoUrl = null" class="absolute -top-10 right-0 text-white bg-gray-800/80 hover:bg-gray-700 rounded-full p-2 cursor-pointer transition">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <img :src="previewPhotoUrl" alt="Preview Foto Bukti" class="max-h-[80vh] w-auto mx-auto rounded-2xl shadow-2xl object-contain border border-white/20">
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
