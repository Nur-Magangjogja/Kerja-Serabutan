@php
    $distanceRadius = $distanceRadius ?? 'all';
    $sortBy = $sortBy ?? 'nearby';
    $userCity = $userCity ?? null;
    $mitraLat = $mitraLat ?? null;
    $mitraLng = $mitraLng ?? null;
@endphp

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <style>
        :root{
            --brand-500: #0ea5a4;
            --brand-600: #08979a;
            --muted-600: #6b7280;
        }

        .card-shadow { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .card-shadow-hover { box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
        .focus-ring:focus { outline: none; box-shadow: 0 0 0 3px rgba(14,165,164,0.2); }
        
        /* BRImo-style decorative pattern */
        .header-pattern {
            position: relative;
            overflow: hidden;
        }
        
        .header-pattern::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .header-pattern::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>

    <div class="max-w-md mx-auto">
        <!-- Header Section -->
        <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>
            
            <div class="relative z-10 space-y-3">
                <div class="relative flex items-center justify-center min-h-[40px] text-white">
                    <div class="text-center w-full min-w-0 px-12">
                        <h1 class="text-base font-bold truncate">Semua Bantuan</h1>
                        <p class="text-xs text-white/90 truncate mt-0.5">Cari bantuan yang tersedia</p>
                    </div>

                    <div class="absolute right-0 top-1/2 -translate-y-1/2 z-20 flex items-center gap-2">
                        @include('components.notification-icon', ['route' => route('mitra.notifications.index'), 'class' => 'bg-white/15 backdrop-blur-md p-2 rounded-xl hover:bg-white/25 transition cursor-pointer text-white'])
                    </div>
                </div>

                <!-- Distance Radius Filter Grid (No Overflow) -->
                <div class="grid {{ $userCity ? 'grid-cols-5' : 'grid-cols-4' }} gap-1 bg-black/15 backdrop-blur-md p-1 rounded-xl border border-white/20 text-center">
                    <button type="button" wire:click="$set('distanceRadius', 'all')" role="tab"
                        class="py-1.5 px-1 rounded-lg text-center font-bold text-[11px] sm:text-xs transition-all cursor-pointer flex items-center justify-center {{ $distanceRadius === 'all' ? 'bg-white text-primary-700 shadow-sm' : 'text-white/90 hover:bg-white/10' }}">
                        <span>Semua</span>
                    </button>
                    <button type="button" wire:click="$set('distanceRadius', '5')" role="tab"
                        class="py-1.5 px-1 rounded-lg text-center font-bold text-[11px] sm:text-xs transition-all cursor-pointer flex items-center justify-center {{ $distanceRadius === '5' ? 'bg-white text-primary-700 shadow-sm' : 'text-white/90 hover:bg-white/10' }}">
                        <span>≤ 5 km</span>
                    </button>
                    <button type="button" wire:click="$set('distanceRadius', '15')" role="tab"
                        class="py-1.5 px-1 rounded-lg text-center font-bold text-[11px] sm:text-xs transition-all cursor-pointer flex items-center justify-center {{ $distanceRadius === '15' ? 'bg-white text-primary-700 shadow-sm' : 'text-white/90 hover:bg-white/10' }}">
                        <span>≤ 15 km</span>
                    </button>
                    <button type="button" wire:click="$set('distanceRadius', '60')" role="tab"
                        class="py-1.5 px-1 rounded-lg text-center font-bold text-[11px] sm:text-xs transition-all cursor-pointer flex items-center justify-center {{ $distanceRadius === '60' ? 'bg-white text-primary-700 shadow-sm' : 'text-white/90 hover:bg-white/10' }}">
                        <span>≤ 60 km</span>
                    </button>
                    @if($userCity)
                    <button type="button" wire:click="$set('distanceRadius', 'city')" role="tab"
                        class="py-1.5 px-1 rounded-lg text-center font-bold text-[11px] sm:text-xs transition-all cursor-pointer flex items-center justify-center truncate {{ $distanceRadius === 'city' ? 'bg-white text-primary-700 shadow-sm' : 'text-white/90 hover:bg-white/10' }}"
                        title="Kota {{ $userCity->name }}">
                        <span class="truncate">Kota</span>
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="px-5 pt-4 pb-6 min-h-[60vh]">
            @if(session()->has('error'))
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 p-3.5 rounded-2xl text-xs flex items-start gap-2.5 shadow-xs">
                    <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <div class="flex-1 font-medium">{{ session('error') }}</div>
                </div>
            @endif

            @if(session()->has('message'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-3.5 rounded-2xl text-xs flex items-start gap-2.5 shadow-xs">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <div class="flex-1 font-medium">{{ session('message') }}</div>
                </div>
            @endif

            @if (!empty($activeTask))
                <div class="mb-4 bg-blue-50/70 dark:bg-gray-800 border border-blue-200/80 dark:border-gray-700 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3 mb-2.5">
                        <div class="flex items-start gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center text-base flex-shrink-0 font-bold shadow-xs">
                                {{ $activeTask->progress_icon }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs font-bold text-blue-900 dark:text-blue-200 truncate">Tugas Aktif Berjalan</h4>
                                    <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-blue-600 text-white shadow-xs">
                                        {{ $activeTask->progress_percentage }}%
                                    </span>
                                </div>
                                <p class="text-xs text-blue-800 dark:text-blue-300 mt-0.5 font-medium truncate">
                                    "{{ $activeTask->title }}" • <span class="font-bold">{{ $activeTask->progress_summary }}</span>
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('mitra.helps.detail', $activeTask->id) }}" class="flex-shrink-0 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-sm whitespace-nowrap">
                            Buka Tugas
                        </a>
                    </div>
                    <!-- Mini Progress Track -->
                    <div class="w-full bg-blue-100/70 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                        <div class="h-full rounded-full bg-blue-600 dark:bg-blue-500 transition-all duration-500 {{ $activeTask->progress_percentage < 100 ? 'animate-pulse' : '' }}"
                             style="width: {{ $activeTask->progress_percentage }}%;"></div>
                    </div>
                </div>
            @endif

            <!-- GPS Status Bar -->
            <div class="flex items-center justify-between mb-4 bg-gray-50 border border-gray-100 rounded-xl p-2.5 text-xs text-gray-600 flex-wrap gap-2">
                <div class="flex items-center gap-1.5">
                    <span id="mitra-gps-indicator" class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="mitra-gps-text" class="font-medium">Mendeteksi lokasi GPS...</span>
                </div>
                <button type="button" onclick="refreshMitraGPS()" class="text-primary-600 hover:text-primary-700 font-semibold inline-flex items-center gap-1 text-[11px]">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Perbarui GPS
                </button>
            </div>

            <!-- Sort Filter in List View -->
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs text-gray-500 font-medium">Urutkan:</span>
                <select wire:model.live="sortBy" class="px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-medium text-gray-700 focus:ring-2 focus:ring-blue-200 outline-none">
                    <option value="nearby">📍 Terdekat (Jarak GPS)</option>
                    <option value="latest">Terbaru</option>
                    <option value="oldest">Terlama</option>
                    <option value="price_high">Harga Tertinggi</option>
                    <option value="price_low">Harga Terendah</option>
                </select>
            </div> 

            <div class="space-y-4">
                <div class="space-y-3.5 transition-opacity duration-200" wire:loading.class="opacity-50 pointer-events-none" wire:target="distanceRadius,sortBy,search">
                    {{-- List based on filter --}}
                    @forelse($helps as $help)
                    <div class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-md transition-all border border-gray-100">
                        <div class="flex items-start gap-3">
                            <div class="w-12 h-12 rounded-xl overflow-hidden bg-blue-50 dark:bg-gray-800 border border-blue-100 dark:border-gray-700 flex-shrink-0 flex items-center justify-center">
                                @if($help->photo)
                                    <img src="{{ asset('storage/' . $help->photo) }}" alt="{{ $help->title }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-xl">{{ ['🩺', '🏠', '💡', '🔧', '🎯'][($loop->index) % 5] }}</span>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <h3 class="font-bold text-sm text-gray-900 dark:text-white line-clamp-1">{{ $help->title }}</h3>
                                    <span class="text-xs font-extrabold whitespace-nowrap text-primary-600 dark:text-primary-400">Rp {{ number_format($help->amount, 0, ',', '.') }}</span>
                                </div>

                                <!-- Tags & Badges: Distance + Status -->
                                <div class="flex items-center gap-1.5 flex-wrap mb-2">
                                    @if(isset($help->distance_km) && $help->distance_km !== null)
                                        @if($help->distance_km <= 5)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                🟢 {{ $help->distance_km }} km (Dekat)
                                            </span>
                                        @elseif($help->distance_km <= 25)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                🟡 {{ $help->distance_km }} km
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                🔴 {{ $help->distance_km }} km (Jauh)
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                            📍 {{ $help->city->name ?? 'Indonesia' }}
                                        </span>
                                    @endif

                                    @if($help->scheduled_at)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-800">
                                            📅 Terjadwal
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-100 dark:border-purple-800">
                                            ⚡ Butuh Cepat
                                        </span>
                                    @endif
                                </div>

                                <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2 mb-2 leading-relaxed">{{ $help->description }}</p>

                                @if($help->scheduled_at)
                                    <div class="flex items-center gap-1.5 text-[11px] text-blue-700 dark:text-blue-300 font-medium bg-blue-50/70 dark:bg-blue-950/50 border border-blue-100 dark:border-blue-900/60 rounded-lg px-2.5 py-1 mb-2.5">
                                        <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Waktu: {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('l, d M Y - H:i') }} WIB</span>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between pt-1 border-t border-gray-100 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                                    <div class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="truncate max-w-[100px]">{{ $help->user->name ?? 'Pengguna' }}</span>
                                    </div>

                                    @php
                                        $previewPayload = [
                                            'id' => $help->id,
                                            'title' => $help->title,
                                            'amount' => (int) $help->amount,
                                            'description' => $help->description ?? '',
                                            'equipment_provided' => $help->equipment_provided ?? '',
                                            'location' => $help->location ?? '',
                                            'full_address' => $help->full_address ?? '',
                                            'city_name' => $help->city->name ?? '',
                                            'province_name' => $help->city->province ?? '',
                                            'photo_url' => $help->photo ? asset('storage/' . $help->photo) : null,
                                            'scheduled_at' => $help->scheduled_at ? \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('l, d M Y • H:i') . ' WIB' : null,
                                            'created_at_human' => $help->created_at ? $help->created_at->diffForHumans() : '',
                                            'customer_name' => $help->user->name ?? 'Pemohon Bantuan',
                                            'customer_avatar' => ($help->user->selfie_photo ?? $help->user->photo) ? asset('storage/' . ($help->user->selfie_photo ?? $help->user->photo)) : null,
                                            'distance_km' => $help->distance_km !== null ? (float)$help->distance_km : null,
                                        ];
                                    @endphp

                                    <button type="button" 
                                        onclick="showHelpPreview({{ json_encode($previewPayload) }})"
                                        class="px-3.5 py-1.5 bg-primary-50 dark:bg-primary-950/50 text-primary-700 dark:text-primary-300 hover:bg-primary-100 font-bold rounded-xl transition text-xs flex items-center gap-1 shadow-2xs cursor-pointer border border-primary-200 dark:border-primary-800/60">
                                        <span>Lihat Detail</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <!-- Empty State -->
                    <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                        <div class="w-14 h-14 rounded-full bg-blue-50 dark:bg-gray-700 text-blue-500 mx-auto flex items-center justify-center mb-3">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $search ? 'Tidak ada bantuan ditemukan' : 'Tidak ada bantuan di radius ini' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-xs mx-auto">
                            {{ $search ? 'Coba cari dengan kata kunci lain' : 'Pilih tab "Semua" atau perbesar radius jangkauan untuk melihat bantuan lain.' }}
                        </p>
                        @if($distanceRadius !== 'all')
                        <button type="button" wire:click="$set('distanceRadius', 'all')" class="mt-4 px-4 py-2 bg-primary-50 text-primary-600 rounded-xl text-xs font-bold border border-primary-200 cursor-pointer">
                            Lihat Semua
                        </button>
                        @endif
                    </div>
                    @endforelse

                        <!-- Pagination -->
                        <div class="mt-6 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs">
                            {{ $helps->links('vendor.pagination.superadmin') }}
                        </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Preview Bantuan (Centered Modern Dialog - No Bottom Nav Clash) -->
    <div id="helpPreviewModal" class="fixed inset-0 z-[60] flex items-center justify-center p-3.5 sm:p-4 bg-black/60 backdrop-blur-xs hidden" onclick="closePreviewModal()">
        <div class="bg-white dark:bg-gray-800 rounded-2xl sm:rounded-3xl w-full max-w-lg shadow-2xl max-h-[85vh] sm:max-h-[88vh] flex flex-col overflow-hidden border border-gray-100 dark:border-gray-700 animate-in fade-in zoom-in-95 duration-200" onclick="event.stopPropagation()">
            
            <!-- Modal Header -->
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700/80 px-5 py-4 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-primary-50 dark:bg-primary-950/60 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-sm">
                        📋
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white leading-tight">Detail Permintaan Bantuan</h3>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Periksa detail tugas sebelum mengambil</p>
                    </div>
                </div>
                <button type="button" onclick="closePreviewModal()" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Content (Scrollable) -->
            <div class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1 min-h-0 text-gray-800 dark:text-gray-200">
                
                <!-- Distance Warning (if > 25km or > 60km) -->
                <div id="previewDistanceWarning" class="hidden bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 rounded-xl p-3 text-xs text-rose-800 dark:text-rose-300">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <div>
                            <p class="font-bold">Perhatian Jarak Lokasi</p>
                            <p class="mt-0.5 text-[11px] text-rose-700 dark:text-rose-300 leading-relaxed" id="previewDistanceWarningText">Lokasi ini berada di luar radius normal.</p>
                        </div>
                    </div>
                </div>

                <!-- Judul & Badges -->
                <div class="bg-gray-50 dark:bg-gray-750/60 border border-gray-100 dark:border-gray-700/80 rounded-2xl p-4">
                    <div class="flex items-center gap-1.5 flex-wrap mb-2" id="previewBadgesContainer">
                        <span id="previewScheduledBadge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            ⚡ Butuh Cepat
                        </span>
                        <span id="previewDistanceBadge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            📍 Menghitung...
                        </span>
                    </div>
                    <h4 id="previewTitle" class="text-base sm:text-lg font-extrabold text-gray-900 dark:text-white leading-snug">
                        -
                    </h4>
                </div>

                <!-- Pendapatan Tugas (100% Penuh untuk Mitra) -->
                <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/40 dark:to-teal-950/40 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl p-4 flex items-center justify-between shadow-2xs">
                    <div>
                        <span class="text-xs font-bold text-emerald-900 dark:text-emerald-200 block">Upah Bersih untuk Anda:</span>
                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">100% Penuh (Tanpa Potongan Komisi)</span>
                    </div>
                    <div id="previewTaskValue" class="text-xl sm:text-2xl font-black text-emerald-700 dark:text-emerald-300 tracking-tight">
                        Rp 0
                    </div>
                </div>

                <!-- Pemohon Bantuan (Customer Card) -->
                <div class="flex items-center gap-3 p-3.5 bg-gray-50 dark:bg-gray-750/50 border border-gray-100 dark:border-gray-700/80 rounded-2xl">
                    <div id="previewCustomerAvatarContainer" class="w-10 h-10 rounded-full overflow-hidden bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300 font-bold flex items-center justify-center flex-shrink-0 text-sm">
                        <span id="previewCustomerInitial">U</span>
                        <img id="previewCustomerAvatarImg" src="" alt="avatar" class="w-full h-full object-cover hidden">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 font-medium">Pemohon Bantuan</p>
                        <p id="previewCustomerName" class="text-xs sm:text-sm font-bold text-gray-800 dark:text-gray-100 truncate">Pengguna</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-[10px] text-gray-400 dark:text-gray-500">Waktu Buat</p>
                        <p id="previewCreatedAt" class="text-[11px] font-semibold text-gray-600 dark:text-gray-300">-</p>
                    </div>
                </div>

                <!-- Deskripsi Pekerjaan Lengkap -->
                <div class="space-y-1.5">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-gray-800 dark:text-gray-200">
                        <span class="text-primary-500">📝</span>
                        <span>Rincian & Deskripsi Pekerjaan:</span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-750/50 border border-gray-100 dark:border-gray-700/80 rounded-2xl p-3.5">
                        <p id="previewDescription" class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line font-normal">
                            -
                        </p>
                    </div>
                </div>

                <!-- Peralatan Kerja (Equipment) -->
                <div class="space-y-1.5">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-gray-800 dark:text-gray-200">
                        <span class="text-amber-500">🧰</span>
                        <span>Peralatan Kerja:</span>
                    </div>
                    <div id="previewEquipmentBox" class="bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-900/50 rounded-2xl p-3.5">
                        <p id="previewEquipment" class="text-xs text-amber-900 dark:text-amber-200 leading-relaxed font-medium">
                            -
                        </p>
                    </div>
                </div>

                <!-- Wilayah / Patokan Lokasi -->
                <div class="space-y-1.5">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-gray-800 dark:text-gray-200">
                        <span class="text-rose-500">📍</span>
                        <span>Area & Patokan Lokasi:</span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-750/50 border border-gray-100 dark:border-gray-700/80 rounded-2xl p-3.5 space-y-1.5">
                        <p id="previewLocation" class="text-xs sm:text-sm font-semibold text-gray-800 dark:text-gray-200">
                            -
                        </p>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            <span>Rute navigasi GPS & live tracking akan aktif otomatis begitu Anda mengambil tugas ini.</span>
                        </p>
                    </div>
                </div>

                <!-- Foto Objek / Tugas (Jika ada) -->
                <div id="previewPhotoSection" class="hidden space-y-1.5">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-gray-800 dark:text-gray-200">
                        <span class="text-sky-500">📷</span>
                        <span>Foto Objek / Tempat Pekerjaan:</span>
                    </div>
                    <div class="rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-black/5 dark:bg-black/20 max-h-56">
                        <img id="previewPhotoImg" src="" alt="Foto Bantuan" class="w-full h-full max-h-56 object-contain mx-auto">
                    </div>
                </div>

            </div>

            <!-- Sticky footer -->
            <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700/80 p-3.5 sm:p-4 flex gap-2.5 flex-shrink-0">
                <button type="button" onclick="closePreviewModal()" class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 py-3 rounded-xl font-bold text-xs hover:bg-gray-200 dark:hover:bg-gray-600 transition cursor-pointer">
                    Kembali
                </button>
                @if(!empty($activeTask))
                    <a href="{{ route('mitra.helps.detail', $activeTask->id) }}" class="flex-[1.6] bg-amber-500 hover:bg-amber-600 text-white py-3 rounded-xl font-bold text-xs shadow-md transition flex items-center justify-center text-center">
                        Selesaikan Tugas Aktif
                    </a>
                @else
                    <button type="button" id="previewTakeBtn" onclick="takeHelpFromModal()" class="flex-[1.6] bg-gradient-to-r from-primary-600 to-blue-700 text-white py-3 rounded-xl font-bold text-xs hover:brightness-105 shadow-md transition active:scale-[0.98] cursor-pointer flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Ambil Tugas Ini</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    @script
    <script>
        let currentHelpId = null;
        let userMitraLat = @json($mitraLat);
        let userMitraLng = @json($mitraLng);

        window.showHelpPreview = function(data) {
            if (!data) return;
            currentHelpId = data.id;

            // Judul
            document.getElementById('previewTitle').textContent = data.title || '-';

            // Nominal
            const taskVal = Number(data.amount) || 0;
            const taskValEl = document.getElementById('previewTaskValue');
            if (taskValEl) taskValEl.textContent = 'Rp ' + taskVal.toLocaleString('id-ID');

            // Jadwal
            const schedBadge = document.getElementById('previewScheduledBadge');
            if (schedBadge) {
                if (data.scheduled_at) {
                    schedBadge.textContent = '📅 ' + data.scheduled_at;
                    schedBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800';
                } else {
                    schedBadge.textContent = '⚡ Butuh Cepat';
                    schedBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 dark:bg-purple-950/70 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800';
                }
            }

            // Customer
            document.getElementById('previewCustomerName').textContent = data.customer_name || 'Pemohon Bantuan';
            document.getElementById('previewCreatedAt').textContent = data.created_at_human || '-';
            const initialEl = document.getElementById('previewCustomerInitial');
            const avatarImg = document.getElementById('previewCustomerAvatarImg');
            if (data.customer_avatar) {
                avatarImg.src = data.customer_avatar;
                avatarImg.classList.remove('hidden');
                initialEl.classList.add('hidden');
            } else {
                avatarImg.classList.add('hidden');
                initialEl.classList.remove('hidden');
                initialEl.textContent = (data.customer_name || 'U').charAt(0).toUpperCase();
            }

            // Deskripsi Pekerjaan
            document.getElementById('previewDescription').textContent = data.description || 'Tidak ada deskripsi tambahan.';

            // Peralatan
            const equipEl = document.getElementById('previewEquipment');
            const equipBox = document.getElementById('previewEquipmentBox');
            if (data.equipment_provided && data.equipment_provided.trim().length > 0) {
                equipEl.textContent = '✓ Disediakan Pemesan: ' + data.equipment_provided;
                equipBox.className = 'bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-900/50 rounded-2xl p-3.5';
                equipEl.className = 'text-xs text-emerald-900 dark:text-emerald-200 leading-relaxed font-medium';
            } else {
                equipEl.textContent = 'Peralatan dibawa mandiri oleh Mitra / tidak ada peralatan khusus yang disediakan oleh pemesan.';
                equipBox.className = 'bg-gray-50 dark:bg-gray-750/50 border border-gray-100 dark:border-gray-700/80 rounded-2xl p-3.5';
                equipEl.className = 'text-xs text-gray-600 dark:text-gray-400 leading-relaxed font-normal';
            }

            // Lokasi
            let locStr = '';
            if (data.location) locStr += data.location;
            if (data.city_name) locStr += (locStr ? ' • ' : '') + data.city_name;
            if (data.province_name) locStr += ', ' + data.province_name;
            document.getElementById('previewLocation').textContent = locStr || 'Wilayah Belum Ditentukan';

            // Foto Pekerjaan
            const photoSection = document.getElementById('previewPhotoSection');
            const photoImg = document.getElementById('previewPhotoImg');
            if (data.photo_url) {
                photoImg.src = data.photo_url;
                photoSection.classList.remove('hidden');
            } else {
                photoSection.classList.add('hidden');
            }

            // Jarak & Peringatan
            const distBadge = document.getElementById('previewDistanceBadge');
            const warnBox = document.getElementById('previewDistanceWarning');
            const warnText = document.getElementById('previewDistanceWarningText');
            const takeBtn = document.getElementById('previewTakeBtn');
            const distanceKm = data.distance_km;

            if (distanceKm !== null && distanceKm !== undefined && !isNaN(distanceKm)) {
                distBadge.textContent = '📍 ' + distanceKm + ' km';
                if (distanceKm > 60) {
                    warnBox.classList.remove('hidden');
                    warnBox.className = 'bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 rounded-xl p-3 text-xs text-rose-800 dark:text-rose-300';
                    warnText.textContent = `Bantuan ini berjarak ${distanceKm} km (melebihi batas radius maksimal 60 km). Tugas tidak dapat diambil.`;
                    if (takeBtn) {
                        takeBtn.disabled = true;
                        takeBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                } else if (distanceKm > 25) {
                    warnBox.classList.remove('hidden');
                    warnBox.className = 'bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 rounded-xl p-3 text-xs text-amber-800 dark:text-amber-300';
                    warnText.textContent = `Bantuan ini berjarak ${distanceKm} km di wilayah ${data.city_name || 'Luar Kota'}. Pastikan Anda dapat menjangkau lokasi.`;
                    if (takeBtn) {
                        takeBtn.disabled = false;
                        takeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                } else {
                    warnBox.classList.add('hidden');
                    if (takeBtn) {
                        takeBtn.disabled = false;
                        takeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                }
            } else {
                distBadge.textContent = data.city_name ? '📍 ' + data.city_name : '📍 Indonesia';
                warnBox.classList.add('hidden');
                if (takeBtn) {
                    takeBtn.disabled = false;
                    takeBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }

            document.getElementById('helpPreviewModal').classList.remove('hidden');
        };

        window.closePreviewModal = function() {
            document.getElementById('helpPreviewModal').classList.add('hidden');
            currentHelpId = null;
        };

        window.refreshMitraGPS = function() {
            if (!navigator.geolocation) return;

            const indicator = document.getElementById('mitra-gps-indicator');
            const textEl = document.getElementById('mitra-gps-text');
            if (textEl) textEl.textContent = 'Memperbarui koordinat GPS...';
            if (indicator) indicator.className = 'w-2.5 h-2.5 rounded-full bg-blue-500 animate-ping';

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    userMitraLat = pos.coords.latitude;
                    userMitraLng = pos.coords.longitude;

                    if (textEl) textEl.textContent = `GPS Aktif (±${Math.round(pos.coords.accuracy)}m)`;
                    if (indicator) indicator.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500';

                    $wire.setMitraLocation(userMitraLat, userMitraLng);
                },
                (err) => {
                    console.warn('GPS error:', err.message);
                    if (textEl) textEl.textContent = 'GPS tidak aktif / izin ditolak';
                    if (indicator) indicator.className = 'w-2.5 h-2.5 rounded-full bg-amber-400';
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        };

        window.takeHelpFromModal = async function() {
            if (!currentHelpId) return;
            
            const btn = document.getElementById('previewTakeBtn');
            const originalText = btn ? btn.innerHTML : 'Ambil Bantuan';
            if (btn) {
                btn.innerHTML = `
                    <svg class="animate-spin w-4 h-4 text-white inline-block mr-1" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Memproses...</span>
                `;
                btn.disabled = true;
            }

            const executeTake = async (lat = null, lng = null) => {
                try {
                    if (lat && lng) {
                        await $wire.takeHelp(currentHelpId, lat, lng);
                    } else {
                        await $wire.takeHelp(currentHelpId);
                    }
                    window.closePreviewModal();
                } catch (error) {
                    console.error('Gagal mengambil bantuan:', error);
                } finally {
                    if (btn) {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                }
            };

            if (userMitraLat && userMitraLng) {
                await executeTake(userMitraLat, userMitraLng);
            } else if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async (p) => await executeTake(p.coords.latitude, p.coords.longitude),
                    async () => await executeTake(),
                    { timeout: 5000 }
                );
            } else {
                await executeTake();
            }
        };

        // Auto GPS Detection
        window.refreshMitraGPS();

        // Listen for help-taken event from Livewire
        window.addEventListener('help-taken', function(event) {
            var helpId = event?.detail?.helpId ?? event?.detail ?? null;
            if (!helpId) {
                window.location.reload();
                return;
            }
            var detailUrlTemplate = @json(route('mitra.helps.detail', ['id' => 'REPLACE_ID']));
            window.location.href = detailUrlTemplate.replace('REPLACE_ID', helpId);
        });
    </script>
    @endscript
</div>