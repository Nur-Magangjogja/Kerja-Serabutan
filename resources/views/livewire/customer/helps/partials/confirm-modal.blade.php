<!-- Confirmation Modal - Bottom Sheet Style -->
@if ($showConfirmModal)
    <div class="modal-overlay fixed inset-0 z-[9999] flex items-end justify-center animate-fade-in"
        style="background: rgba(0,0,0,0.6);" wire:click="closeConfirmModal">
        <div class="bg-white dark:bg-gray-800 rounded-t-3xl w-full max-w-md shadow-2xl max-h-[88vh] overflow-y-auto hide-scrollbar animate-slide-up relative border-t border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100"
            @click.stop style="padding-bottom: env(safe-area-inset-bottom,24px);">
            <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 px-5 py-4 rounded-t-3xl z-10 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Konfirmasi Permintaan Bantuan</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Rincian jenis layanan & pembayaran transparan</p>
                </div>
                <button type="button" wire:click="closeConfirmModal"
                    class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition text-gray-600 dark:text-gray-400 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-5 pb-6 space-y-3.5">
                <!-- 1. Detail Jenis Layanan & Pemetaan Lokasi Terpisah -->
                <div class="p-3.5 bg-gray-50 dark:bg-gray-800/90 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Jenis Layanan :</span>
                        @if ($service_type === 'pickup_delivery')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-200 border border-blue-200 dark:border-blue-800">
                                <span>📦</span> Antar / Jemput
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800">
                                <span>🛠️</span> Kerja di Lokasi
                            </span>
                        @endif
                    </div>

                    @if ($service_type === 'pickup_delivery')
                        <div class="space-y-1.5 pt-2 border-t border-gray-200 dark:border-gray-700 text-xs">
                            <div class="flex items-start gap-2">
                                <span class="w-4 h-4 rounded-full bg-blue-600 text-white text-[10px] flex items-center justify-center font-bold flex-shrink-0 mt-0.5">1</span>
                                <div class="min-w-0">
                                    <span class="text-gray-500 dark:text-gray-400 text-[11px] block font-medium">Titik Jemput (Pickup):</span>
                                    <span class="font-semibold text-gray-900 dark:text-white leading-tight block truncate">{{ $pickup_address ?: 'Titik Jemput Terpilih' }}</span>
                                </div>
                            </div>
                            <div class="flex items-start gap-2 pt-1">
                                <span class="w-4 h-4 rounded-full bg-emerald-600 text-white text-[10px] flex items-center justify-center font-bold flex-shrink-0 mt-0.5">2</span>
                                <div class="min-w-0">
                                    <span class="text-gray-500 dark:text-gray-400 text-[11px] block font-medium">Titik Antar (Tujuan):</span>
                                    <span class="font-semibold text-gray-900 dark:text-white leading-tight block truncate">{{ $delivery_address ?: 'Titik Antar Terpilih' }}</span>
                                </div>
                            </div>
                            @if((float) $route_distance_km > 0)
                                @php
                                    $estTravelMin = (new \App\Services\GeoService())->getRouteDurationMinutes((float)$route_distance_km, 25.0, 3);
                                @endphp
                                <div class="flex items-center justify-between text-[11px] font-semibold text-blue-700 dark:text-blue-300 bg-blue-50/80 dark:bg-blue-950/50 px-2.5 py-1.5 rounded-lg mt-1 border border-blue-100 dark:border-blue-900/40 flex-wrap gap-1">
                                    <span>🛣️ Rute: <strong>{{ number_format((float)$route_distance_km, 1, ',', '.') }} KM</strong> (±{{ $estTravelMin }} mnt)</span>
                                    <span>{{ (float)$route_distance_km <= 4 ? 'Tarif Dasar Rp 10.000 (≤ 4 KM)' : ((float)$route_distance_km <= 20 ? '@ Rp 2.500 / KM' : 'Tarif Jarak Jauh (> 20 KM)') }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="space-y-1 pt-2 border-t border-gray-200 dark:border-gray-700 text-xs">
                            <div class="flex items-start gap-1.5">
                                <span class="text-sm">📍</span>
                                <div class="min-w-0">
                                    <span class="text-gray-500 dark:text-gray-400 text-[11px] block font-medium">Lokasi Pekerjaan:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white leading-tight block truncate">{{ $location ?: 'Titik Lokasi Terpilih' }}</span>
                                </div>
                            </div>
                            @if($full_address)
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 pl-5">
                                    Patokan: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $full_address }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- 2. Breakdown Pembayaran Transparan V3 -->
                <div class="bg-gray-50 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 space-y-2.5">
                    <div class="flex items-center justify-between text-xs sm:text-sm">
                        <span class="text-gray-600 dark:text-gray-400">
                            {{ $service_type === 'pickup_delivery' ? 'Biaya Ongkos Antar / Jemput' : 'Imbalan Rekan Jasa' }}
                        </span>
                        <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($confirmServiceFee ?? $confirmAmount ?? 0, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs sm:text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Biaya Layanan Platform</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400">+ Rp {{ number_format($confirmPlatformFee ?? 0, 0, ',', '.') }}</span>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block">Total Saldo yang Dibutuhkan</span>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500">Ditahan aman (Escrow) hingga selesai</span>
                        </div>
                        <div class="text-xl font-extrabold text-blue-600 dark:text-blue-400">
                            Rp {{ number_format($confirmTotal ?? 0, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                @if ($confirmScheduled)
                    <div class="p-3 bg-blue-50/70 dark:bg-blue-950/30 rounded-xl border border-blue-100 dark:border-blue-900/40 text-xs flex items-center justify-between">
                        <div>
                            <span class="text-blue-800 dark:text-blue-300 font-medium block">Jadwal Tugas:</span>
                            <span class="font-bold text-blue-950 dark:text-blue-100">{{ $confirmScheduled }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 block">Jeda Keberangkatan:</span>
                            <span class="font-bold text-blue-700 dark:text-blue-300">{{ $early_departure_minutes }} Menit Sebelum</span>
                        </div>
                    </div>
                @endif

                <!-- 3. Info Box Escrow -->
                <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/80 rounded-xl p-3.5">
                    <div class="flex gap-2.5">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <div class="text-xs text-blue-900 dark:text-blue-200 leading-relaxed">
                            <p class="font-bold mb-0.5">Jaminan 100% Saldo Aman :</p>
                            Dana sebesar <strong>Rp {{ number_format($confirmTotal ?? 0, 0, ',', '.') }}</strong> akan dikunci di saldo Escrow selama tugas berlangsung. Uang baru akan diteruskan ke Rekan Jasa setelah Anda mengonfirmasi pekerjaan selesai. Jika tugas dibatalkan, dana 100% dikembalikan utuh ke saldo Anda.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky footer with action buttons -->
            <div class="sticky bottom-0 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 px-5 py-4 z-20 flex gap-3">
                <button wire:click="closeConfirmModal" type="button"
                    class="flex-1 px-5 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition cursor-pointer">
                    Kembali
                </button>
                <button wire:click="save" type="button" wire:loading.attr="disabled"
                    class="flex-1 px-5 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold hover:from-blue-600 hover:to-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer shadow-md">
                    <span wire:loading.remove wire:target="save">Konfirmasi & Buat</span>
                    <span wire:loading wire:target="save" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Memproses...
                    </span>
                </button>
            </div>
        </div>
    </div>
@endif
