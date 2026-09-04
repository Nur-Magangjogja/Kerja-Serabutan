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

                    <div class="absolute right-0 top-1/2 -translate-y-1/2 z-20 flex items-center gap-2">
                        <x-notification-icon :route="route('mitra.notifications.index')" />
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
        <div class="px-5 pt-4">
            <div class="flex items-center gap-2 bg-gray-200/70 dark:bg-gray-800 p-1 rounded-xl">
                <button wire:click="setTab('completed')" class="flex-1 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer flex items-center justify-center gap-1.5 {{ $activeTab === 'completed' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800' }}">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Bantuan Selesai ({{ $totalCompletedCount }})</span>
                </button>
                <button wire:click="setTab('cancelled')" class="flex-1 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer flex items-center justify-center gap-1.5 {{ $activeTab === 'cancelled' ? 'bg-white dark:bg-gray-700 text-rose-600 dark:text-rose-400 shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800' }}">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Pembatalan Tugas ({{ $totalCancelledCount }})</span>
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="px-5 pt-3 pb-24">

            {{-- TAB 1: BANTUAN SELESAI --}}
            @if($activeTab === 'completed')
                @if(isset($helps) && $helps->isEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 p-8 text-center mt-2">
                        <div class="w-14 h-14 rounded-full bg-gray-50 dark:bg-gray-700/50 flex items-center justify-center mx-auto mb-3 text-gray-400">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Belum Ada Riwayat Bantuan Selesai</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Bantuan yang telah Anda selesaikan akan dicatat di sini</p>
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
                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm truncate leading-snug">{{ $help->title ?? 'Permintaan Bantuan' }}</h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ optional($help->city)->name }} • {{ optional($help->updated_at)->format('d M Y') }}</p>
                                            @if($help->scheduled_at)
                                                <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">📅 {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }}</div>
                                            @endif
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
                                    @if($help->description)
                                        <div>
                                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Deskripsi Tugas</h4>
                                            <p class="text-xs text-gray-800 dark:text-gray-200 leading-relaxed">{{ $help->description }}</p>
                                        </div>
                                    @endif

                                    <div class="grid grid-cols-2 gap-3 bg-gray-50 dark:bg-gray-750 p-3 rounded-xl">
                                        <div>
                                            <div class="text-[11px] text-gray-400 dark:text-gray-400 mb-0.5">Lokasi</div>
                                            <div class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate">{{ $help->full_address ?? optional($help->city)->name ?? '-' }}</div>
                                        </div>

                                        @if($help->user)
                                            <div>
                                                <div class="text-[11px] text-gray-400 dark:text-gray-400 mb-0.5">Customer</div>
                                                <div class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">{{ $help->user->name }}</div>
                                                @if($help->user->phone)
                                                    <a href="tel:{{ $help->user->phone }}" class="text-[11px] font-semibold text-primary-600 dark:text-sky-400">{{ $help->user->phone }}</a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

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
                @if(isset($penalties) && $penalties->isEmpty())
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 p-8 text-center mt-2">
                        <div class="w-14 h-14 rounded-full bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center mx-auto mb-3 text-emerald-500">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Tidak Ada Riwayat Pembatalan</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Bagus! Anda belum pernah melakukan pembatalan tugas bantuan.</p>
                    </div>
                @elseif(isset($cancelledActivities))
                    <div class="space-y-3">
                        @foreach($cancelledActivities as $activity)
                            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 border-l-4 border-l-rose-500 p-4 shadow-xs">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                        <div class="w-9 h-9 rounded-xl bg-rose-100 dark:bg-rose-950/70 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="font-bold text-gray-900 dark:text-gray-100 text-sm truncate">
                                                {{ $activity->help?->title ?? 'Pembatalan Bantuan' }}
                                            </h3>
                                            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">
                                                @if($activity->help?->order_id)
                                                    Order: <span class="font-mono">{{ $activity->help->order_id }}</span> • 
                                                @elseif($activity->help_id ?? $activity->reference_id)
                                                    ID: #{{ $activity->help_id ?? $activity->reference_id }} • 
                                                @endif
                                                {{ $activity->created_at ? $activity->created_at->format('d M Y • H:i') : '-' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 text-[10px] font-bold mt-1">
                                            {{ $activity->activity_type === 'cancel_requested' ? 'Pengajuan Batal' : 'Dibatalkan' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="bg-rose-50/70 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/30 rounded-xl p-2.5 mt-2">
                                    <p class="text-xs text-rose-800 dark:text-rose-300 leading-relaxed">
                                        <span class="font-bold">Keterangan:</span> {{ $activity->description ?: 'Pembatalan tugas bantuan sebelum diselesaikan.' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-4 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs">
                            {{ $cancelledActivities->links('vendor.pagination.superadmin') }}
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
