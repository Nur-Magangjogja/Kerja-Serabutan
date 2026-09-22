<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100"
    x-data="{ 
        showNotification: false, 
        notificationMessage: '',
        previewPhotoUrl: null
    }"
    @show-status-notification.window="
        notificationMessage = $event.detail.message;
        showNotification = true;
        if (window.playNotificationSound) {
            window.playNotificationSound();
        }
        setTimeout(() => showNotification = false, 5000);
    "
>
    @php
        $isCompleted = in_array($help->status, ['selesai', 'completed', 'confirmed']);
        $isCancelled = in_array($help->status, ['dibatalkan', 'cancelled']);
        $isPartnerCancelRequested = ($help->status === 'partner_cancel_requested');
        $isCancelRequested = ($help->status === 'customer_cancel_requested');
        $isActive = !$isCompleted && !$isCancelled && !$isCancelRequested && !$isPartnerCancelRequested;
        $customerReview = $help->rating;
        $settlementLabel = match($cancelRequest?->settlement_type ?? null) {
            'full_refund'        => 'Pengembalian Dana Penuh 100% ke Customer',
            'partial_settlement' => 'Penyelesaian Kompensasi Sebagian (Proporsional)',
            'item_settled'       => 'Penyelesaian Biaya Operasional / Tambahan Mitra',
            'no_refund'          => 'Dana Diteruskan Penuh ke Mitra (Tanpa Refund)',
            'relist_pool'        => 'Tugas Dilempar Kembali ke Pool Mitra Lain',
            default              => null
        };
        $evidencePhoto = $cancelRequest?->evidence_photo ?: $help->cancel_evidence_photo;
    @endphp

    {{-- Status Notification Toast --}}
    <div x-show="showNotification" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 max-w-sm w-full px-4"
         style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white" x-text="notificationMessage"></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Status pesanan diperbarui</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Header Section (Dynamic Theme per Status) --}}
    <div class="px-5 pt-4 pb-5 relative overflow-hidden shadow-sm text-white rounded-b-2xl transition-colors duration-300
        {{ $isCompleted ? 'bg-emerald-600' : (($isCancelled || $isCancelRequested || $isPartnerCancelRequested) ? 'bg-rose-600' : 'bg-[#0098e7]') }}">
        <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

        <div class="relative z-10 max-w-md mx-auto">
            <div class="relative flex items-center justify-between min-h-[40px] text-white">
                {{-- Back Navigation Arrow --}}
                <div class="flex items-center">
                    <a href="{{ $isCancelled ? route('mitra.helps.completed') : route('mitra.dashboard') }}" 
                       wire:navigate
                       class="p-2 hover:bg-white/20 rounded-xl transition-colors duration-200 cursor-pointer flex items-center justify-center text-white"
                       title="{{ $isCancelled ? 'Kembali ke Riwayat' : 'Kembali ke Dashboard' }}"
                       aria-label="{{ $isCancelled ? 'Kembali ke Riwayat' : 'Kembali ke Dashboard' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                </div>

                {{-- Header Title & Subtitle --}}
                <div class="text-center flex-1 min-w-0 px-2">
                    @if($isCompleted)
                        <h1 class="text-base font-bold truncate">Detail Riwayat Selesai</h1>
                        <p class="text-xs text-white font-medium truncate mt-0.5">Tugas tuntas & pembayaran berhasil</p>
                    @elseif($isCancelled)
                        <h1 class="text-base font-bold truncate">Detail Riwayat Pembatalan</h1>
                        <p class="text-xs text-white font-medium truncate mt-0.5">Catatan & keputusan pembatalan</p>
                    @elseif($isPartnerCancelRequested)
                        <h1 class="text-base font-bold truncate">Pengajuan Kendala Lapangan</h1>
                        <p class="text-xs text-white font-medium truncate mt-0.5">Menunggu peninjauan & konfirmasi Admin</p>
                    @elseif($isCancelRequested)
                        <h1 class="text-base font-bold truncate">Permintaan Penarikan</h1>
                        <p class="text-xs text-white font-medium truncate mt-0.5">Customer mengajukan pembatalan</p>
                    @else
                        <h1 class="text-base font-bold truncate">Detail Pesanan</h1>
                        <p class="text-xs text-white font-medium truncate mt-0.5">Informasi lengkap pesanan aktif</p>
                    @endif
                </div>

                {{-- Refresh Button --}}
                <div class="flex items-center justify-end">
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
    <div class="px-5 pt-5 pb-24 max-w-md mx-auto">
        @if (session('message'))
            <div class="mb-4 p-3 bg-green-50 dark:bg-green-950/40 border border-green-100 dark:border-green-800 rounded-xl text-green-700 dark:text-green-300 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('message') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-800 rounded-xl text-rose-700 dark:text-rose-300 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- CASE 1: BANTUAN SELESAI (COMPLETED) STATUS CARD                   --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($isCompleted)
            <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/70 rounded-2xl p-4 mb-3 shadow-xs space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-emerald-500/30">
                            ✓
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700">
                                Selesai
                            </span>
                            <h3 class="font-bold text-sm text-white-950 dark:text-white-100 mt-0.5">Tugas Berhasil Diselesaikan</h3>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] text-white-700/80 dark:text-white-300/80 block font-medium">Upah Bersih Diterima:</span>
                        <span class="text-lg sm:text-xl font-black text-white-700 dark:text-white-300">
                            Rp {{ number_format($help->getNetEarning(), 0, ',', '.') }}
                        </span>
                    </div>
                </div>
                <div class="pt-2 border-t border-emerald-200/60 dark:border-emerald-800/60 flex items-center justify-between text-[11px] text-emerald-800 dark:text-emerald-300">
                    <span>🕒 Selesai pada:</span>
                    <span class="font-bold">
                        {{ \Carbon\Carbon::parse($help->completed_at ?? $help->updated_at)->locale('id')->translatedFormat('l, d F Y • H:i') }} WIB
                    </span>
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- CASE: MITRA MENGAJUKAN KENDALA LAPANGAN ( IN-PROGRESS)    --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($isPartnerCancelRequested)
            <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/70 rounded-2xl p-4 mb-3 shadow-xs space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                            ⏳
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-200 border border-amber-300 dark:border-amber-700">
                                    Menunggu Tinjauan Admin
                                </span>
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-900/60 text-rose-800 dark:text-rose-200 border border-rose-200 dark:border-rose-800">
                                     (Saat Pengerjaan)
                                </span>
                            </div>
                            <h3 class="font-bold text-sm text-gray-900 dark:text-white mt-1">Pengajuan Kendala Lapangan</h3>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 text-[10px] font-bold border border-amber-300 dark:border-amber-700">
                            🔒 Akun Sibuk
                        </span>
                    </div>
                </div>

                {{-- Detail Pengajuan --}}
                <div class="bg-white/90 dark:bg-gray-800/90 rounded-xl p-3 border border-rose-100 dark:border-rose-900/40 text-xs space-y-2 text-left">
                    <div class="flex items-start gap-2">
                        <span class="text-gray-400 dark:text-gray-500 font-medium shrink-0 w-24 text-[11px]">Pemohon:</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400 text-xs">
                            Diajukan oleh Anda (Mitra)
                        </span>
                    </div>

                    <div class="flex items-start gap-2">
                        <span class="text-gray-400 dark:text-gray-500 font-medium shrink-0 w-24 text-[11px]">Alasan:</span>
                        <span class="font-bold text-rose-600 dark:text-rose-400 break-words flex-1 text-xs">
                            {{ $cancelRequest?->reason ?: ($help->cancel_reason ?: 'Kendala lapangan saat proses pengerjaan') }}
                        </span>
                    </div>

                    @if($cancelRequest?->notes)
                        <div class="flex items-start gap-2">
                            <span class="text-gray-400 dark:text-gray-500 font-medium shrink-0 w-24 text-[11px]">Catatan:</span>
                            <span class="text-gray-700 dark:text-gray-300 italic break-words flex-1 text-xs">
                                "{{ $cancelRequest->notes }}"
                            </span>
                        </div>
                    @endif

                    @if($evidencePhoto)
                        <div class="pt-2 border-t border-rose-100 dark:border-rose-900/40 space-y-1.5">
                            <span class="text-gray-500 dark:text-gray-400 font-medium text-[11px] block">Foto Bukti Kendala Lapangan:</span>
                            <div class="relative group cursor-pointer w-full max-w-[200px] rounded-xl overflow-hidden border border-rose-200 dark:border-rose-800 bg-black/5"
                                 @click="previewPhotoUrl = '{{ asset('storage/' . $evidencePhoto) }}'">
                                <img src="{{ asset('storage/' . $evidencePhoto) }}" 
                                     alt="Bukti Kendala Lapangan" 
                                     class="w-full h-28 object-cover group-hover:scale-105 transition duration-200">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-[11px] font-semibold gap-1">
                                    <span>🔍 Perbesar</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 rounded-xl text-xs text-amber-900 dark:text-amber-200 space-y-1.5">
                    <p class="font-bold flex items-center gap-1.5 text-[11px]">
                        <span>📞</span> Tim Admin Wilayah Sedang Menindaklanjuti
                    </p>
                    <p class="text-[11px] text-amber-800 dark:text-amber-300 leading-relaxed">
                        Admin Wilayah sedang menghubungi Customer terlebih dahulu untuk memvalidasi kendala lapangan dan menyepakati pengembalian dana (refund) yang adil. Anda akan dihubungi jika diperlukan informasi tambahan.
                    </p>
                    <p class="text-[10px] text-amber-700 dark:text-amber-400 pt-1 border-t border-amber-200/60 dark:border-amber-800/40">
                        ℹ️ <strong>Status Akun:</strong> Akun Anda berstatus <strong>SIBUK</strong> sementara dan tidak dapat mengambil pesanan lain hingga evaluasi pesanan ini diputuskan oleh Admin.
                    </p>
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- CASE: CUSTOMER MEMINTA PENARIKAN PEKERJAAN (WITHDRAWAL REQUEST)   --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($isCancelRequested)
            @php
                $isSwitchPartner = ($cancelRequest?->action_type === 'switch_partner');
                $rawPhone = $help->user->phone ?? '';
                $waPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                if (str_starts_with($waPhone, '0')) {
                    $waPhone = '62' . substr($waPhone, 1);
                } elseif (str_starts_with($waPhone, '8')) {
                    $waPhone = '62' . $waPhone;
                }
                $waText = urlencode("Halo Kak " . ($help->user->name ?? 'Customer') . ", saya " . (auth()->user()->name ?? 'Mitra') . " mengenai pesanan bantuan #" . ($help->order_id ?: $help->id) . ".");
            @endphp
            @if ($isSwitchPartner)
                <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/70 rounded-2xl p-4 mb-3 shadow-xs space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-blue-500 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-blue-500/30">
                                🔄
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-200 border border-blue-300 dark:border-blue-700">
                                    Permintaan Ganti Mitra
                                </span>
                                <h3 class="font-bold text-sm text-blue-950 dark:text-blue-100 mt-0.5">Customer Mengajukan Ganti Mitra</h3>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/80 dark:bg-gray-800/80 rounded-xl p-3 border border-blue-100 dark:border-blue-900/40 text-xs space-y-1.5 text-left">
                        <div class="flex items-start gap-2">
                            <span class="text-gray-400 font-medium shrink-0 w-20 text-[11px]">Alasan:</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400 break-words flex-1 text-xs">
                                {{ $cancelRequest?->reason ?: 'Mitra tidak bergerak / tidak kunjung datang' }}
                            </span>
                        </div>
                        @if($cancelRequest?->notes)
                            <div class="flex items-start gap-2">
                                <span class="text-gray-400 font-medium shrink-0 w-20 text-[11px]">Catatan:</span>
                                <span class="text-gray-700 dark:text-gray-300 italic break-words flex-1 text-xs">
                                    "{{ $cancelRequest->notes }}"
                                </span>
                            </div>
                        @endif
                    </div>

                    @if($cancelRequest?->partner_response_type === 'rejected')
                        <div class="bg-blue-100/70 dark:bg-blue-950/60 p-3 rounded-xl border border-blue-200 dark:border-blue-800 text-xs text-blue-900 dark:text-blue-200 space-y-1">
                            <p class="font-bold flex items-center gap-1">
                                <span>✕ Anda Mengajukan Klarifikasi / Keberatan</span>
                            </p>
                            <p class="text-[11px] text-blue-800 dark:text-blue-300">
                                Penjelasan Anda: <em>"{{ $cancelRequest->partner_response_notes }}"</em>
                            </p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                Permintaan ini sedang ditinjau oleh Admin Wilayah untuk evaluasi pengalihan pesanan ke pool mitra lain.
                            </p>
                        </div>
                    @else
                        <div class="space-y-2 pt-1">
                            <p class="text-[11px] text-gray-600 dark:text-gray-300 leading-relaxed">
                                Customer meminta ganti rekan jasa. Anda disarankan menghubungi customer terlebih dahulu di awal. Jika Anda bersedia melepaskan tugas ini, silakan klik tombol setujui. <strong>Bila tidak ada respons dan tidak dikonfirmasi</strong> dalam batas waktu, Admin dapat memutuskan untuk langsung mengembalikannya ke pool.
                            </p>

                            {{-- Shortcut Hubungi Customer di Awal --}}
                            <div class="flex items-center gap-2 py-1">
                                <a href="{{ route('mitra.chat', ['help' => $help->id]) }}"
                                   wire:navigate
                                   class="flex-1 py-2 px-3 bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-700 rounded-xl text-xs font-semibold text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition flex items-center justify-center gap-1.5 shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    <span> Chat Customer</span>
                                </a>
                                @if($help->user->phone ?? null)
                                    <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}"
                                       target="_blank"
                                       rel="noopener"
                                       class="flex-1 py-2 px-3 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 rounded-xl text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 transition flex items-center justify-center gap-1.5 shadow-2xs">
                                        <span>📞 Hubungi WA</span>
                                    </a>
                                @endif
                            </div>

                            <div class="flex flex-col sm:flex-row gap-2 pt-1">
                                <button type="button" wire:click="confirmWithdrawal" wire:loading.attr="disabled"
                                        class="flex-1 py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer shadow-xs">
                                    <span wire:loading.remove wire:target="confirmWithdrawal">✓ Setujui Ganti Mitra (Lepaskan Tugas)</span>
                                    <span wire:loading wire:target="confirmWithdrawal">Memproses...</span>
                                </button>
                                <button type="button" wire:click="openRejectWithdrawModal"
                                        class="flex-1 py-2.5 px-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer shadow-xs">
                                    <span>✕ Tolak / Klarifikasi</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/70 rounded-2xl p-4 mb-3 shadow-xs space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                                ⚠️
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-200 border border-amber-300 dark:border-amber-700">
                                    Permintaan Penarikan
                                </span>
                                <h3 class="font-bold text-sm text-rose-950 dark:text-rose-100 mt-0.5">Customer Meminta Penarikan Pesanan</h3>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white/80 dark:bg-gray-800/80 rounded-xl p-3 border border-rose-100 dark:border-rose-900/40 text-xs space-y-1.5 text-left">
                        <div class="flex items-start gap-2">
                            <span class="text-gray-400 font-medium shrink-0 w-20 text-[11px]">Alasan:</span>
                            <span class="font-bold text-rose-600 dark:text-rose-400 break-words flex-1 text-xs">
                                {{ $cancelRequest?->reason ?: 'Customer mengajukan pembatalan pesanan' }}
                            </span>
                        </div>
                        @if($cancelRequest?->notes)
                            <div class="flex items-start gap-2">
                                <span class="text-gray-400 font-medium shrink-0 w-20 text-[11px]">Catatan:</span>
                                <span class="text-gray-700 dark:text-gray-300 italic break-words flex-1 text-xs">
                                    "{{ $cancelRequest->notes }}"
                                </span>
                            </div>
                        @endif
                    </div>

                    @if($cancelRequest?->partner_response_type === 'rejected')
                        <div class="bg-rose-100/70 dark:bg-rose-950/60 p-3 rounded-xl border border-rose-200 dark:border-rose-800 text-xs text-rose-900 dark:text-rose-200 space-y-1">
                            <p class="font-bold flex items-center gap-1">
                                <span>✕ Anda Telah Menolak Penarikan Ini</span>
                            </p>
                            <p class="text-[11px] text-rose-800 dark:text-rose-300">
                                Pembelaan Anda: <em>"{{ $cancelRequest->partner_response_notes }}"</em>
                            </p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                Kasus ini sedang diaudit oleh Admin Wilayah dengan memeriksa telemetri pergerakan GPS Anda & log percakapan.
                            </p>
                        </div>
                    @else
                        <div class="space-y-2 pt-1">
                            <p class="text-[11px] text-gray-600 dark:text-gray-300">
                                Customer ingin membatalkan pesanan ini dan menarik kembali dananya. Silakan tentukan respons Anda:
                            </p>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <button type="button" wire:click="confirmWithdrawal" wire:loading.attr="disabled"
                                        class="flex-1 py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer shadow-xs">
                                    <span wire:loading.remove wire:target="confirmWithdrawal">✓ Setujui (Batal Total)</span>
                                    <span wire:loading wire:target="confirmWithdrawal">Memproses...</span>
                                </button>
                                <button type="button" wire:click="openRejectWithdrawModal"
                                        class="flex-1 py-2.5 px-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer shadow-xs">
                                    <span>✕ Tolak & Ajukan Pembelaan</span>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- CASE 2: BANTUAN DIBATALKAN (CANCELLED) STATUS & AUDIT CARD         --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($isCancelled)
            @php
                $statusBadge = match($cancelRequest?->status ?? 'approved') {
                    'pending'   => ['class' => 'bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border-amber-300 dark:border-amber-700', 'label' => '⏳ Menunggu Audit Admin'],
                    'approved'  => ['class' => 'bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border-rose-300 dark:border-rose-700', 'label' => '❌ Pembatalan Disetujui'],
                    'rejected'  => ['class' => 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700', 'label' => '✅ Ditolak (Tugas Lanjut)'],
                    default     => ['class' => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300', 'label' => 'Dibatalkan']
                };
            @endphp
            <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/70 rounded-2xl p-4 mb-3 shadow-xs space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-rose-500/30">
                            ✕
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full border {{ $statusBadge['class'] }}">
                                {{ $statusBadge['label'] }}
                            </span>
                            <h3 class="font-bold text-sm text-rose-950 dark:text-rose-100 mt-0.5">Tugas Bantuan Dibatalkan</h3>
                        </div>
                    </div>
                    <div class="text-right">
                        @php
                            $hasPartnerSp = in_array($cancelRequest?->sp_target, ['partner', 'both'], true) && ((int)($cancelRequest?->partner_sp_level ?? 0) > 0);
                        @endphp
                        @if($hasPartnerSp)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 text-[10px] font-bold border border-rose-300 dark:border-rose-700">
                                ⚠️ SP {{ $cancelRequest->partner_sp_level }}
                            </span>
                        @elseif($cancelRequest?->audit_decision === 'valid_no_sp' || ($cancelRequest?->status === 'approved' && $cancelRequest?->sp_target === 'none'))
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold border border-emerald-300 dark:border-emerald-700">
                                🛡️ Bebas Sanksi
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Detail Pengajuan Pembatalan (Teks Rata Kiri & Rapi) --}}
                <div class="bg-white/80 dark:bg-gray-800/80 rounded-xl p-3 border border-rose-100 dark:border-rose-900/40 text-xs space-y-2 text-left">
                    <div class="flex items-start gap-2 text-left">
                        <span class="text-gray-400 dark:text-gray-500 font-medium shrink-0 w-20 text-[11px]">Pemohon:</span>
                        <span class="font-bold text-left text-xs {{ ($cancelRequest?->requester_type === 'partner' || $help->cancel_requested_by === 'partner') ? 'text-blue-600 dark:text-blue-400' : 'text-purple-600 dark:text-purple-400' }}">
                            {{ ($cancelRequest?->requester_type === 'partner' || $help->cancel_requested_by === 'partner') ? 'Diajukan oleh Anda (Mitra)' : 'Diajukan oleh Customer' }}
                        </span>
                    </div>

                    <div class="flex items-start gap-2 text-left">
                        <span class="text-gray-400 dark:text-gray-500 font-medium shrink-0 w-20 text-[11px]">Alasan:</span>
                        <span class="font-bold text-rose-600 dark:text-rose-400 break-words flex-1 text-left text-xs">
                            {{ $cancelRequest?->reason ?: ($help->partner_cancel_reason ?: 'Kendala Lapangan / Darurat') }}
                        </span>
                    </div>

                    @if($cancelRequest?->notes ?: $help->partner_cancel_notes)
                        <div class="flex items-start gap-2 text-left">
                            <span class="text-gray-400 dark:text-gray-500 font-medium shrink-0 w-20 text-[11px]">Catatan:</span>
                            <span class="text-gray-700 dark:text-gray-300 italic break-words flex-1 text-left text-xs">
                                "{{ $cancelRequest?->notes ?: $help->partner_cancel_notes }}"
                            </span>
                        </div>
                    @endif

                    @if($evidencePhoto)
                        <div class="pt-1.5 border-t border-gray-100 dark:border-gray-700/60">
                            <span class="text-[11px] font-semibold text-gray-600 dark:text-gray-300 block mb-1">Foto Bukti Kendala Lapangan:</span>
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

                {{-- Tanggapan Klarifikasi Mitra (Jika diajukan oleh customer) --}}
                @if($cancelRequest?->requester_type === 'customer' && ($cancelRequest?->partner_clarification || $cancelRequest?->partner_clarification_photo))
                    <div class="bg-blue-50/80 dark:bg-blue-950/40 rounded-xl p-3 border border-blue-100 dark:border-blue-900/40 text-xs space-y-1.5">
                        <div class="flex items-center justify-between pb-1 border-b border-blue-100/80 dark:border-blue-900/30">
                            <span class="font-bold text-blue-900 dark:text-blue-200">🗣️ Tanggapan / Klarifikasi Anda</span>
                            @if($cancelRequest->partner_clarified_at)
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">
                                    {{ $cancelRequest->partner_clarified_at->locale('id')->translatedFormat('d M Y • H:i') }}
                                </span>
                            @endif
                        </div>
                        @if($cancelRequest->partner_clarification)
                            <p class="text-gray-800 dark:text-gray-200 italic leading-relaxed">"{{ $cancelRequest->partner_clarification }}"</p>
                        @endif
                        @if($cancelRequest->partner_clarification_photo)
                            <div class="w-16 h-16 rounded-xl overflow-hidden border border-blue-200 dark:border-blue-800/60 cursor-pointer hover:opacity-90 transition shadow-2xs mt-1"
                                 @click="previewPhotoUrl = '{{ asset('storage/' . $cancelRequest->partner_clarification_photo) }}'">
                                <img src="{{ asset('storage/' . $cancelRequest->partner_clarification_photo) }}" alt="Foto Klarifikasi" class="w-full h-full object-cover">
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Keputusan & Audit Admin Wilayah --}}
                @if($cancelRequest)
                    <div class="bg-indigo-50/80 dark:bg-indigo-950/40 rounded-xl p-3 border border-indigo-100 dark:border-indigo-900/40 text-xs space-y-2">
                        <div class="flex items-center justify-between pb-1.5 border-b border-indigo-100/80 dark:border-indigo-900/30">
                            <span class="font-bold text-indigo-950 dark:text-indigo-200 flex items-center gap-1">
                                <span>⚖️ Keputusan & Audit Admin</span>
                            </span>
                            @if($cancelRequest->reviewed_at)
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">
                                    Ditinjau: {{ $cancelRequest->reviewed_at->locale('id')->translatedFormat('d M Y • H:i') }}
                                </span>
                            @endif
                        </div>

                        @if($cancelRequest->admin_notes)
                            <div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 font-semibold">Catatan Keputusan:</div>
                                <div class="text-gray-800 dark:text-gray-200 mt-0.5 leading-relaxed bg-white/60 dark:bg-gray-800/60 p-2 rounded-lg border border-indigo-100/60 dark:border-indigo-900/30">
                                    {{ $cancelRequest->admin_notes }}
                                </div>
                            </div>
                        @else
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 italic">
                                {{ $cancelRequest->status === 'pending' ? 'Pengajuan pembatalan ini sedang dalam proses review investigasi Admin Wilayah.' : 'Tidak ada catatan khusus dari admin.' }}
                            </div>
                        @endif

                        @if($cancelRequest->reviewedBy)
                            <div class="text-[10px] text-gray-400 dark:text-gray-500 text-right pt-0.5">
                                Diverifikasi oleh: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $cancelRequest->reviewedBy->name }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                {{--  Penyelesaian Finansial (HANYA UNTUK LAYANAN ANTAR / JEMPUT) --}}
                @if($help->isPickup() && ($settlementLabel || ($cancelRequest && ($cancelRequest->payout_amount_mitra > 0 || $cancelRequest->refund_amount_customer > 0))))
                    <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-xl text-xs space-y-2 border border-gray-200/80 dark:border-gray-700">
                        <div class="font-bold text-gray-700 dark:text-gray-200 text-[11px] flex items-center gap-1">
                            <span> Penyelesaian Finansial:</span>
                        </div>
                        @if($settlementLabel)
                            <div class="text-[11px] text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-750 p-2 rounded-lg border border-gray-200/60 dark:border-gray-700/60 font-medium">
                                {{ $settlementLabel }}
                            </div>
                        @endif
                        <div class="grid grid-cols-2 gap-2 pt-1 border-t border-gray-200/60 dark:border-gray-700/60 text-xs">
                            <div class="bg-white dark:bg-gray-750 p-2 rounded-lg border border-gray-200/60 dark:border-gray-700/60">
                                <span class="text-gray-400 text-[10px] block">Kompensasi Mitra:</span>
                                <span class="font-bold text-sm block mt-0.5 {{ ($cancelRequest?->payout_amount_mitra ?? 0) > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-400' }}">
                                    Rp {{ number_format($cancelRequest?->payout_amount_mitra ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="bg-white dark:bg-gray-750 p-2 rounded-lg border border-gray-200/60 dark:border-gray-700/60 text-right">
                                <span class="text-gray-400 text-[10px] block">Refund Customer:</span>
                                <span class="font-bold text-sm block mt-0.5 {{ ($cancelRequest?->refund_amount_customer ?? 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-600 dark:text-gray-400' }}">
                                    Rp {{ number_format($cancelRequest?->refund_amount_customer ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- CARD INFORMASI UTAMA LAYANAN & TARIF                             --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3">
            {{-- Header Row: Category Badge & Title --}}
            <div class="space-y-1.5 mb-3">
                <div class="flex items-center gap-1.5 flex-wrap">
                    @if($help->isPickup())
                        @if($help->service_category === 'passenger')
                            <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/60">
                                👥 Antar Penumpang
                            </span>
                        @else
                            <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200/60 dark:border-indigo-800/60">
                                📦 Barang & Dokumen
                            </span>
                        @endif
                    @else
                        <span class="text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60">
                            🛠️ Kerja Serabutan
                        </span>
                    @endif

                    @if($help->isScheduled())
                        <span class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/60">
                            📅 Terjadwal
                        </span>
                    @else
                        <span class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60">
                            ⚡ Segera
                        </span>
                    @endif
                </div>

                <div class="flex-1 min-w-0">
                    <h2 class="font-bold text-base text-gray-900 dark:text-white leading-snug">{{ $help->title }}</h2>
                </div>
            </div>

            {{-- Earnings Display --}}
            <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/60 rounded-2xl p-4 shadow-2xs space-y-2.5">
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
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- PROGRES MULTI-STAGE STEPPER (Hanya Tampil Saat Tugas Aktif)        --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($isActive)
            <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3">
                <div class="flex items-center justify-between mb-3">
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
                    
                    $activeIndex = 0;
                    foreach ($multiSteps as $idx => $st) {
                        if (!empty($st['active'])) {
                            $activeIndex = $idx;
                            break;
                        }
                    }
                @endphp

                <div class="relative pt-2 pb-1">
                    <div class="absolute top-6 left-6 right-6 h-1 bg-gray-100 dark:bg-gray-700 -z-0">
                        <div class="h-full bg-primary-600 dark:bg-primary-500 transition-all duration-700 rounded-full"
                             style="width: {{ $stepCount > 1 ? max(0, min(100, ($activeIndex / ($stepCount - 1)) * 100)) : 0 }}%;"></div>
                    </div>

                    <div class="flex items-start justify-between relative z-10">
                        @foreach($multiSteps as $idx => $s)
                            @php
                                $isPassed = $idx < $activeIndex;
                                $isCurrent = $idx === $activeIndex;
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
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- LIVE ETA & NAVIGATION & GPS SIMULATOR (Saat Penugasan Aktif)      --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if(in_array($help->status, ['taken', 'partner_on_the_way', 'partner_arrived', 'in_progress']) && $help->mitra_id === auth()->id())
            @php
                $travelProgress = app(\App\Services\HelpScheduleService::class)->getLiveTravelProgress($help);
            @endphp
            <div class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 p-4 rounded-2xl shadow-xs border border-gray-200 dark:border-gray-700 mb-3 space-y-3 transition-colors" wire:poll.5s.visible>
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
                                {{ $travelProgress['is_arrived'] ? 'Telah Tiba di Lokasi' : 'Perjalanan Menuju Lokasi Sasaran' }}
                            </h4>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ $travelProgress['target_label'] }}</p>
                            </div>
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
                            {{ $travelProgress['is_arrived'] ? 'Tiba di Lokasi' : 'Live GPS ETA' }}
                        </span>
                    </div>
                </div>

                @if($travelProgress['is_arrived'])
                    <div class="bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/80 rounded-xl p-3 text-center">
                        <p class="text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>Anda telah sampai di lokasi sasaran</span>
                        </p>
                        <p class="text-[11px] text-emerald-700 dark:text-emerald-300/80 mt-0.5">Silakan koordinasi dan lanjutkan tahapan pekerjaan melalui tombol aksi di bawah.</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-2 text-center">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-2.5 border border-gray-200/70 dark:border-gray-700/60">
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 block font-medium">Jarak ke Sasaran</span>
                            <span class="text-sm font-extrabold text-gray-900 dark:text-white font-mono block mt-0.5">
                                {{ $travelProgress['formatted_distance'] }}
                            </span>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-2.5 border border-gray-200/70 dark:border-gray-700/60">
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 block font-medium">Estimasi Waktu Tiba (ETA)</span>
                            <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400 font-mono block mt-0.5">
                                {{ $travelProgress['formatted_eta'] }}
                            </span>
                        </div>
                    </div>

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

                    @if($travelProgress['target_lat'] && $travelProgress['target_lng'])
                        <div class="pt-1 flex items-center gap-2">
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $travelProgress['target_lat'] }},{{ $travelProgress['target_lng'] }}" 
                               target="_blank" rel="noopener noreferrer"
                               class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                <span>Google Maps</span>
                            </a>
                            <a href="https://waze.com/ul?ll={{ $travelProgress['target_lat'] }},{{ $travelProgress['target_lng'] }}&navigate=yes" 
                               target="_blank" rel="noopener noreferrer"
                               class="py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs">
                                <span>Waze</span>
                            </a>
                        </div>
                    @endif
                @endif
            </div>

            {{-- GPS Simulator Widget --}}
            <div class="mb-3">
                <livewire:mitra.gps.simulator :helpId="$help->id" :key="'gps-sim-'.$help->id" />
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- BUKTI PENGERJAAN & ULASAN CUSTOMER (KHUSUS STATUS SELESAI)        --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($isCompleted)
            {{-- Foto Bukti Penyelesaian --}}
            @if ($help->proof_photo)
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-emerald-200 dark:border-emerald-800/60 shadow-xs mb-3 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Foto Bukti Penyelesaian Tugas:
                        </span>
                        <button type="button" @click="previewPhotoUrl = '{{ asset('storage/' . $help->proof_photo) }}'" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline cursor-pointer flex items-center gap-1">
                            <span>Perbesar</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </div>
                    
                    <div @click="previewPhotoUrl = '{{ asset('storage/' . $help->proof_photo) }}'" class="rounded-xl overflow-hidden border border-emerald-200/80 dark:border-emerald-800 bg-gray-900/10 max-h-56 flex items-center justify-center cursor-pointer group relative">
                        <img src="{{ asset('storage/' . $help->proof_photo) }}" alt="Bukti Selesai" class="w-full h-auto max-h-56 object-contain group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors flex items-center justify-center pointer-events-none">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity bg-black/75 text-white text-[10px] font-bold px-3 py-1 rounded-full backdrop-blur-xs shadow-xs">
                                🔍 Klik untuk Perbesar
                            </span>
                        </div>
                    </div>

                    @if($help->completion_notes)
                        <div class="bg-emerald-50/70 dark:bg-emerald-950/30 p-2.5 rounded-xl border border-emerald-100 dark:border-emerald-900/40 text-xs text-emerald-950 dark:text-emerald-100 italic">
                            "{{ $help->completion_notes }}"
                        </div>
                    @endif
                </div>
            @endif

            {{-- Penilaian & Ulasan dari Customer --}}
            @if ($customerReview)
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-amber-200/80 dark:border-amber-800/60 shadow-xs mb-3">
                    <div class="flex items-center justify-between mb-3 pb-2.5 border-b border-gray-100 dark:border-gray-700/60">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-sm shadow-2xs">
                                ⭐
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-gray-900 dark:text-white">Ulasan dari Customer</h4>
                                <p class="text-[11px] text-gray-400 dark:text-gray-400">{{ optional($customerReview->created_at)->locale('id')->translatedFormat('d M Y, H:i') }} WIB</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 bg-amber-50 dark:bg-amber-950/60 px-2.5 py-1 rounded-full border border-amber-200 dark:border-amber-800/50">
                            <span class="text-xs font-black text-amber-700 dark:text-amber-300">{{ number_format($customerReview->rating, 1) }}</span>
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

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- WAKTU PELAKSANAAN / JADWAL                                        --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3">
            <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Waktu Pelaksanaan
            </h3>
            @if($help->isScheduled())
                <div class="p-3 bg-blue-50/70 dark:bg-blue-950/40 rounded-xl border border-blue-100 dark:border-blue-900/50 space-y-1">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-blue-900 dark:text-blue-200">
                        <span>📅 Tugas Terjadwal:</span>
                    </div>
                    <p class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">
                        {{ \Carbon\Carbon::parse($help->scheduled_at ?? $help->service_scheduled_at)->locale('id')->translatedFormat('l, d F Y • H:i') }} WIB
                    </p>
                    @if($help->departure_window_opens_at && $isActive)
                        <p class="text-[11px] text-blue-700 dark:text-blue-300 font-medium">
                            Jendela keberangkatan dapat dimulai sejak pukul {{ $help->departure_window_opens_at->format('H:i') }} WIB.
                        </p>
                    @endif
                </div>
            @else
                <div class="p-3 bg-emerald-50/70 dark:bg-emerald-950/40 rounded-xl border border-emerald-100 dark:border-emerald-900/50 space-y-1">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-white-900 dark:text-white-200">
                        <span>⚡ Segera (Langsung):</span>
                    </div>
                    <p class="text-xs text-white-800 dark:text-white-300 leading-relaxed">
                        Tugas ini tidak dijadwalkan secara khusus dan dilaksanakan langsung saat penugasan diterima.
                    </p>
                </div>
            @endif
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- INFORMASI CUSTOMER                                                --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-sm text-gray-900 dark:text-white">Informasi Customer</h3>
            </div>
            <div class="flex items-center gap-3 mb-3">
                @if ($help->user->profile_photo ?? $help->user->photo)
                    <img src="{{ asset('storage/' . ($help->user->profile_photo ?? $help->user->photo)) }}" alt="{{ $help->user->name }}"
                        class="w-12 h-12 rounded-full object-cover border-2 border-blue-100 dark:border-blue-900">
                @else
                    <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-lg">
                        {{ strtoupper(substr($help->user->name ?? 'C', 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-sm text-gray-900 dark:text-white truncate">{{ $help->user->name ?? 'Customer' }}</h4>
                    @if ($help->user->phone ?? null)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">{{ $help->user->phone }}</p>
                    @endif
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $help->city->name ?? '-' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('mitra.chat', ['help' => $help->id]) }}"
                    wire:navigate
                    class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-white dark:bg-gray-700/80 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 transition text-xs font-semibold">
                    <svg class="w-4 h-4 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <span>Ruang Chat</span>
                </a>
                @if($help->user->phone ?? null)
                    @php
                        $rawPhone = $help->user->phone ?? '';
                        $waPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                        if (str_starts_with($waPhone, '0')) {
                            $waPhone = '62' . substr($waPhone, 1);
                        } elseif (str_starts_with($waPhone, '8')) {
                            $waPhone = '62' . $waPhone;
                        }
                        $waText = urlencode("Halo Kak " . ($help->user->name ?? 'Customer') . ", saya " . (auth()->user()->name ?? 'Mitra SayaBantu') . " terkait pesanan bantuan #" . ($help->order_id ?: $help->id) . ".");
                    @endphp
                    <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}"
                        target="_blank"
                        rel="noopener"
                        class="px-4 py-2.5 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-emerald-600 dark:text-emerald-300 font-semibold text-xs flex items-center gap-1.5 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition">
                        <svg class="w-4 h-4 fill-current text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24">
                            <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2zm.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 012.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.196 8.196 0 01-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.45c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.24-.74-.66-1.24-1.48-1.39-1.73-.14-.25-.02-.39.11-.51.11-.11.25-.29.37-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.34-.76-1.84-.2-.49-.4-.42-.56-.43h-.47c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.71 4.3 3.8.6.26 1.07.41 1.44.53.61.19 1.16.17 1.6.1.49-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.11-.23-.17-.48-.29z"/>
                        </svg>
                        <span>WhatsApp</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- PETA & RUTE TITIK LOKASI                                          --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3 overflow-hidden" id="group-route-map">
            <div class="p-4 border-b border-gray-100 dark:border-gray-700/60 flex items-center justify-between">
                <h3 class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span>{{ $help->isPickup() ? 'Peta Rute Layanan' : 'Peta Lokasi Tugas' }}</span>
                </h3>
                @if($isActive)
                    <div class="flex items-center gap-1.5" id="gps-status-badge">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-full border border-emerald-200/60 dark:border-emerald-800" id="gps-status-text">GPS Terhubung</span>
                    </div>
                @else
                    <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/60 px-2.5 py-0.5 rounded-full">
                        {{ $help->city->name ?? 'Lokasi Terdaftar' }}
                    </span>
                @endif
            </div>

            {{-- Leaflet Route Map --}}
            <div class="relative w-full h-72 bg-gray-100 dark:bg-gray-700" wire:ignore>
                <div id="mitra-route-map" class="w-full h-full z-0"></div>

                {{-- Map Overlay Info Bar --}}
                <div class="absolute top-3 left-3 right-3 z-[400] flex items-center justify-between pointer-events-none">
                    <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-md px-3 py-1.5 rounded-xl shadow-lg border border-gray-200/80 dark:border-gray-700 pointer-events-auto flex items-center gap-2.5">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium">Jarak:</span>
                            <span class="text-xs font-bold text-blue-600 dark:text-blue-400" id="route-dist-badge">
                                {{ $help->service_route_distance_km ? '± ' . number_format($help->service_route_distance_km, 1) . ' KM' : 'Menghitung...' }}
                            </span>
                        </div>
                    </div>

                    <button type="button" id="btn-recenter-route-map" class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-md p-2 rounded-xl shadow-lg border border-gray-200/80 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:text-blue-600 hover:bg-white dark:hover:bg-gray-700 pointer-events-auto transition active:scale-95 cursor-pointer" title="Pusatkan Rute">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Address Text & Navigation Buttons --}}
            <div class="p-4 space-y-3 bg-white dark:bg-gray-800">
                @if($help->isPickup())
                    {{-- 2-Point Route: Pickup & Delivery --}}
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/60 pb-2">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400">Rute Layanan (2 Titik)</span>
                        @if($help->service_route_distance_km)
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-sky-600 dark:text-sky-400 bg-sky-50 dark:bg-sky-950/50 px-2 py-0.5 rounded-md border border-sky-200/60 dark:border-sky-800">
                                ± {{ number_format($help->service_route_distance_km, 1) }} KM
                            </span>
                        @endif
                    </div>

                    {{-- Titik Penjemputan --}}
                    <div class="space-y-2">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0 mt-0.5 border border-emerald-100 dark:border-emerald-900/50 shadow-2xs">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 mb-0.5">
                                    1. Titik Penjemputan (Titik Awal)
                                </div>
                                <p class="font-bold text-sm text-gray-900 dark:text-white leading-snug break-words">
                                    {{ $help->pickup_address ?: ($help->location ?: 'Alamat sesuai titik jemput') }}
                                </p>
                            </div>
                        </div>
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $help->pickup_latitude ?: $help->latitude }},{{ $help->pickup_longitude ?: $help->longitude }}&travelmode=driving"
                           target="_blank" rel="noopener noreferrer"
                           class="w-full py-2 px-3 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-900/50 border border-emerald-200/80 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            <span>Navigasi ke Titik Jemput</span>
                        </a>
                    </div>

                    {{-- Titik Pengantaran --}}
                    <div class="space-y-2 pt-2 border-t border-gray-100 dark:border-gray-700/40">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0 mt-0.5 border border-rose-100 dark:border-rose-900/50 shadow-2xs">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 mb-0.5">
                                    2. Titik Pengantaran / Tujuan (Titik Akhir)
                                </div>
                                <p class="font-bold text-sm text-gray-900 dark:text-white leading-snug break-words">
                                    {{ $help->delivery_address ?: ($help->full_address ?: 'Alamat sesuai titik antar') }}
                                </p>
                            </div>
                        </div>
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $help->delivery_latitude ?: $help->latitude }},{{ $help->delivery_longitude ?: $help->longitude }}&travelmode=driving"
                           target="_blank" rel="noopener noreferrer"
                           class="w-full py-2 px-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/50 border border-rose-200/80 dark:border-rose-800 text-rose-700 dark:text-rose-300 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            <span>Navigasi ke Titik Antar</span>
                        </a>
                    </div>
                @else
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
                                    {{ $help->city->name ?? 'Kota Lokasi' }}
                                </span>
                            </div>
                            <p class="font-bold text-sm text-gray-900 dark:text-white leading-snug break-words">
                                {{ $help->location ?? 'Alamat sesuai titik peta' }}
                            </p>
                        </div>
                    </div>

                    <!-- Detail Patokan Tempat / Ciri Rumah -->
                    <div class="rounded-xl border p-3 transition-colors {{ !empty($help->full_address) ? 'bg-amber-50/70 dark:bg-amber-950/20 border-amber-200/80 dark:border-amber-800/60' : 'bg-gray-50 dark:bg-gray-700/20 border-gray-200/70 dark:border-gray-700/60' }}">
                        <div class="flex items-center gap-1.5 mb-1.5">
                            <svg class="w-3.5 h-3.5 {{ !empty($help->full_address) ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400 dark:text-gray-500' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                            </svg>
                            <h4 class="text-xs font-bold {{ !empty($help->full_address) ? 'text-amber-900 dark:text-amber-200' : 'text-gray-600 dark:text-gray-400' }}">
                                Detail Patokan Tempat / Ciri Rumah
                            </h4>
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

                    {{-- Action Buttons: Google Maps Nav --}}
                    <div class="pt-1">
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $help->latitude ?? '' }},{{ $help->longitude ?? '' }}&travelmode=driving"
                           target="_blank" rel="noopener noreferrer"
                           class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-sm shadow-emerald-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            <span>Navigasi Google Maps</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- DESKRIPSI & RINCIAN TAMBAHAN                                      --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3">
            <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-2">Deskripsi & Rincian Tugas</h3>

            @if(!empty($help->description))
                <div class="mb-3 text-xs sm:text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line break-words bg-gray-50 dark:bg-gray-750 p-3 rounded-xl border border-gray-100 dark:border-gray-700/40">{{ $help->description }}</div>
            @endif

            <div class="grid grid-cols-2 gap-3 text-xs text-gray-700 dark:text-gray-300">
                @if(!empty($help->equipment_provided))
                    <div class="bg-gray-50 dark:bg-gray-750 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700/40">
                        <div class="text-[10px] text-gray-400 uppercase font-semibold">Perlengkapan</div>
                        <div class="font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5 truncate">✓ {{ $help->equipment_provided }}</div>
                    </div>
                @endif

                @if(!empty($help->voucher_code))
                    <div class="bg-gray-50 dark:bg-gray-750 p-2.5 rounded-xl border border-gray-100 dark:border-gray-700/40">
                        <div class="text-[10px] text-gray-400 uppercase font-semibold">Voucher Diskon</div>
                        <div class="font-semibold text-rose-600 dark:text-rose-400 mt-0.5 truncate">{{ $help->voucher_code }}</div>
                    </div>
                @endif
            </div>

            @if(!empty($help->photo))
                <div class="mt-3 pt-2.5 border-t border-gray-100 dark:border-gray-750">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Foto Lampiran Pemesanan
                        </span>
                        <button type="button" @click="previewPhotoUrl = '{{ asset('storage/' . $help->photo) }}'" class="text-[11px] font-semibold text-primary-600 dark:text-primary-400 hover:underline cursor-pointer flex items-center gap-1">
                            <span>Perbesar</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </button>
                    </div>
                    <div @click="previewPhotoUrl = '{{ asset('storage/' . $help->photo) }}'" class="relative rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-xs group bg-gray-100 dark:bg-gray-900 max-h-60 flex items-center justify-center cursor-pointer">
                        <img src="{{ asset('storage/' . $help->photo) }}" alt="Foto lampiran pesanan" class="w-full h-auto max-h-60 object-contain group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors flex items-center justify-center pointer-events-none">
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity bg-black/75 text-white text-[10px] font-bold px-3 py-1 rounded-full backdrop-blur-xs shadow-xs">
                                🔍 Klik untuk Perbesar
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-3 text-xs text-gray-500 dark:text-gray-400 space-y-0.5 border-t border-gray-100 dark:border-gray-700/60 pt-2.5">
                <div>Dibuat: {{ \Carbon\Carbon::parse($help->created_at)->locale('id')->translatedFormat('d F Y, H:i') }} WIB</div>
                <div>Terakhir diperbarui: {{ \Carbon\Carbon::parse($help->updated_at)->locale('id')->translatedFormat('d F Y, H:i') }} WIB</div>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- RIWAYAT TIMELINE                                                  --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($help->partner_started_at || $help->partner_arrived_at || $help->service_started_at || $help->service_completed_at || $help->completed_at || $help->cancelled_at || $cancelRequest?->requested_at)
            <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60 mb-3">
                <h3 class="font-semibold text-sm text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Riwayat Timeline
                </h3>
                <div class="space-y-2 text-xs">
                    @if ($help->partner_started_at)
                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-300 py-1 border-b border-gray-50 dark:border-gray-750">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                <span>Mulai Perjalanan</span>
                            </div>
                            <span class="text-gray-500 dark:text-gray-400 font-medium">{{ \Carbon\Carbon::parse($help->partner_started_at)->locale('id')->translatedFormat('d M, H:i') }}</span>
                        </div>
                    @endif
                    @if ($help->partner_arrived_at)
                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-300 py-1 border-b border-gray-50 dark:border-gray-750">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                <span>Tiba di Lokasi</span>
                            </div>
                            <span class="text-gray-500 dark:text-gray-400 font-medium">{{ \Carbon\Carbon::parse($help->partner_arrived_at)->locale('id')->translatedFormat('d M, H:i') }}</span>
                        </div>
                    @endif
                    @if ($help->service_started_at)
                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-300 py-1 border-b border-gray-50 dark:border-gray-750">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                                <span>Mulai Pengerjaan</span>
                            </div>
                            <span class="text-gray-500 dark:text-gray-400 font-medium">{{ \Carbon\Carbon::parse($help->service_started_at)->locale('id')->translatedFormat('d M, H:i') }}</span>
                        </div>
                    @endif
                    @if ($help->service_completed_at)
                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-300 py-1 border-b border-gray-50 dark:border-gray-750">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                <span>Selesai Pengerjaan</span>
                            </div>
                            <span class="text-gray-500 dark:text-gray-400 font-medium">{{ \Carbon\Carbon::parse($help->service_completed_at)->locale('id')->translatedFormat('d M, H:i') }}</span>
                        </div>
                    @endif
                    @if ($help->completed_at)
                        <div class="flex items-center justify-between text-emerald-700 dark:text-emerald-400 font-semibold py-1">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-emerald-600"></div>
                                <span>Pesanan Selesai Penuh</span>
                            </div>
                            <span class="text-emerald-600 dark:text-emerald-300">{{ \Carbon\Carbon::parse($help->completed_at)->locale('id')->translatedFormat('d M, H:i') }}</span>
                        </div>
                    @endif
                    @if ($help->cancelled_at || $cancelRequest?->requested_at)
                        <div class="flex items-center justify-between text-rose-700 dark:text-rose-400 font-semibold py-1">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-rose-600"></div>
                                <span>Tugas Dibatalkan</span>
                            </div>
                            <span class="text-rose-600 dark:text-rose-300">{{ \Carbon\Carbon::parse($cancelRequest?->requested_at ?? $help->cancelled_at)->locale('id')->translatedFormat('d M, H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- ACTION BUTTONS / NAVIGASI BAWAH                                   --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        @if ($isCompleted || $isCancelled)
            {{-- Tombol Navigasi Riwayat untuk Completed / Cancelled --}}
            <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 space-y-2.5">
                <a href="{{ route('mitra.helps.completed') }}"
                   wire:navigate
                   class="w-full py-3.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-bold text-xs shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Kembali ke Riwayat Bantuan</span>
                </a>
            </div>
        @elseif ($help->status === 'taken' && $help->mitra_id === auth()->id())
            {{-- Action Controls saat status TAKEN --}}
            @php
                $isScheduledLocked = $help->isScheduled() && !$help->canPartnerStartDeparture();
                $targetTimeStr = $help->getScheduledTargetTime()?->format('H:i') ?? '-';
                $windowOpensStr = $help->departure_window_opens_at?->format('H:i') ?? '-';
            @endphp

            @if ($isScheduledLocked)
                <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80 rounded-2xl p-4 mb-3 space-y-3 shadow-xs">
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
                </div>

                <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 space-y-2.5">
                    <button disabled class="w-full py-3.5 bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 rounded-xl font-bold text-sm shadow-xs flex items-center justify-center gap-2 cursor-not-allowed">
                        <span>Keberangkatan Terkunci (Aktif Pkl {{ $windowOpensStr }})</span>
                    </button>
                    <button type="button" wire:click="openPartnerCancelModal"
                        class="w-full py-2.5 px-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800/60 text-rose-700 dark:text-rose-300 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Batalkan Penugasan (Kendala Perjalanan)</span>
                    </button>
                </div>
            @else
                @if ($help->isScheduled())
                    <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/80 rounded-2xl p-4 mb-3 space-y-2 shadow-xs">
                        <div class="flex items-center gap-2 text-blue-950 dark:text-blue-100 font-bold text-xs">
                            <span class="text-base">⏰</span>
                            <span>Waktunya Berangkat (Tugas Terjadwal)</span>
                        </div>
                        <p class="text-xs text-blue-900/85 dark:text-blue-200/90 leading-relaxed">
                            Jadwal pelaksanaan tugas: <strong>Pukul {{ $targetTimeStr }}</strong>. Jendela keberangkatan telah dibuka, silakan segera mulai perjalanan menuju lokasi customer.
                        </p>
                    </div>
                @endif

                <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 space-y-3">
                    @if ($help->service_type === 'pickup_delivery')
                        <button wire:click="advanceStage('going_to_pickup')" wire:loading.attr="disabled"
                            class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Mulai Menuju Lokasi Penjemputan</span>
                        </button>
                    @else
                        <button wire:click="markPartnerStarted" wire:loading.attr="disabled"
                            class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Mulai Berangkat ke Lokasi Customer</span>
                        </button>
                    @endif

                    <button type="button" wire:click="openPartnerCancelModal"
                        class="w-full py-2.5 px-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800/60 text-rose-700 dark:text-rose-300 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Batalkan Penugasan (Kendala Perjalanan)</span>
                    </button>
                </div>
            @endif
        @elseif ($help->status === 'partner_on_the_way' && $help->mitra_id === auth()->id())
            {{-- Action Controls saat OTW --}}
            @php
                $travelProgress = $travelProgress ?? app(\App\Services\HelpScheduleService::class)->getLiveTravelProgress($help);
                $isArrivedAtTarget = (bool) ($travelProgress['is_arrived'] ?? false);
            @endphp
            <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/60 space-y-2.5">
                @if ($help->service_type === 'pickup_delivery')
                    @if (!$help->service_stage || $help->service_stage === 'going_to_pickup')
                        @if ($isArrivedAtTarget)
                            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-center">
                                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span>GPS Mendeteksi Anda Telah Tiba di Lokasi Jemput!</span>
                                </span>
                            </div>
                            <button wire:click="advanceStage('at_pickup')" wire:loading.attr="disabled"
                                class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                <span>Saya Sudah Tiba di Lokasi Penjemputan</span>
                            </button>
                        @else
                            <div class="p-3.5 bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-800/60 rounded-xl">
                                <div class="flex items-center justify-between text-xs font-bold text-blue-900 dark:text-blue-200 mb-1">
                                    <span class="flex items-center gap-1.5">
                                        <span class="inline-block w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                        Sedang Menuju Titik Penjemputan
                                    </span>
                                    <span class="font-mono text-[11px] bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300 px-2 py-0.5 rounded-full">{{ $travelProgress['formatted_distance'] ?? '--' }}</span>
                                </div>
                                <p class="text-[11px] text-blue-800/80 dark:text-blue-300/80 leading-relaxed">
                                    Tombol konfirmasi tiba akan otomatis muncul saat Anda berada dalam radius 50 meter dari titik jemput.
                                </p>
                            </div>
                        @endif
                    @elseif ($help->service_stage === 'at_pickup')
                        <button wire:click="advanceStage('item_collected')" wire:loading.attr="disabled"
                            class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span>Barang Sudah Diambil (Mulai Pengantaran)</span>
                        </button>
                    @elseif ($help->service_stage === 'item_collected' || $help->service_stage === 'going_to_destination')
                        @if ($isArrivedAtTarget)
                            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-center">
                                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <span>GPS Mendeteksi Anda Telah Tiba di Lokasi Tujuan!</span>
                                </span>
                            </div>
                            <button wire:click="advanceStage('at_destination')" wire:loading.attr="disabled"
                                class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Tiba di Lokasi Tujuan Customer</span>
                            </button>
                        @else
                            <div class="p-3.5 bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-800/60 rounded-xl">
                                <div class="flex items-center justify-between text-xs font-bold text-blue-900 dark:text-blue-200 mb-1">
                                    <span class="flex items-center gap-1.5">
                                        <span class="inline-block w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                        Sedang Menuju Lokasi Tujuan
                                    </span>
                                    <span class="font-mono text-[11px] bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300 px-2 py-0.5 rounded-full">{{ $travelProgress['formatted_distance'] ?? '--' }}</span>
                                </div>
                                <p class="text-[11px] text-blue-800/80 dark:text-blue-300/80 leading-relaxed">
                                    Tombol konfirmasi tiba akan otomatis muncul saat Anda berada dalam radius 50 meter dari tujuan pengantaran.
                                </p>
                            </div>
                        @endif
                    @endif
                @else
                    {{-- On-Site Service --}}
                    @if ($isArrivedAtTarget)
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-center">
                            <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <span>GPS Mendeteksi Anda Telah Tiba di Lokasi Penugasan!</span>
                            </span>
                        </div>
                        <button wire:click="markPartnerArrived" wire:loading.attr="disabled"
                            class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Saya Sudah Tiba di Lokasi</span>
                        </button>
                    @else
                        <div class="p-3.5 bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-800/60 rounded-xl">
                            <div class="flex items-center justify-between text-xs font-bold text-blue-900 dark:text-blue-200 mb-1">
                                <span class="flex items-center gap-1.5">
                                    <span class="inline-block w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                    Sedang Dalam Perjalanan Menuju Lokasi
                                </span>
                                <span class="font-mono text-[11px] bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300 px-2 py-0.5 rounded-full">{{ $travelProgress['formatted_distance'] ?? '--' }} ({{ $travelProgress['formatted_eta'] ?? '--' }})</span>
                            </div>
                            <p class="text-[11px] text-blue-800/80 dark:text-blue-300/80 leading-relaxed">
                                Tombol <strong>"Saya Sudah Tiba di Lokasi"</strong> akan otomatis muncul dan dapat ditekan saat titik GPS Anda telah sampai di lokasi penugasan (radius 50 meter).
                            </p>
                        </div>
                    @endif
                @endif

                <button type="button" wire:click="openPartnerCancelModal"
                    class="w-full py-2.5 px-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800/60 text-rose-700 dark:text-rose-300 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Batalkan Penugasan (Kendala Perjalanan)</span>
                </button>
            </div>
        @elseif ($help->status === 'partner_arrived' || in_array($help->service_stage, ['at_customer', 'at_destination']))
            {{-- Action Controls saat Tiba --}}
            <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/60 space-y-2.5">
                @if ($help->service_type === 'on_site_service')
                    <button wire:click="startService"
                        class="w-full py-3.5 bg-green-600 text-white rounded-xl font-bold text-sm hover:bg-green-700 transition flex items-center justify-center gap-2 cursor-pointer shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Mulai Pekerjaan</span>
                    </button>
                @elseif ($help->service_stage === 'at_pickup')
                    <button wire:click="advanceStage('going_to_destination')" wire:loading.attr="disabled"
                        class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Barang / Penumpang Sudah Diambil (Mulai Pengantaran)</span>
                    </button>
                @else
                    <button wire:click="openCompletionModal"
                        class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                        <span>Selesaikan & Unggah Bukti Serah Terima</span>
                    </button>
                @endif

                <button type="button" wire:click="openPartnerCancelModal"
                    class="w-full py-2.5 px-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800/60 text-rose-700 dark:text-rose-300 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Batalkan Penugasan (Kendala Perjalanan)</span>
                </button>
            </div>
        @elseif ($help->status === 'in_progress')
            {{-- Action Controls saat In Progress --}}
            <div class="bg-white dark:bg-gray-800 px-4 py-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 space-y-2.5">
                @if ($help->service_type === 'pickup_delivery' && $help->service_stage !== 'at_destination')
                    @php
                        $travelProgress = $travelProgress ?? app(\App\Services\HelpScheduleService::class)->getLiveTravelProgress($help);
                        $isArrivedAtTarget = (bool) ($travelProgress['is_arrived'] ?? false);
                    @endphp
                    @if ($isArrivedAtTarget)
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-center">
                            <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <span>GPS Mendeteksi Anda Telah Tiba di Lokasi Tujuan!</span>
                            </span>
                        </div>
                        <button wire:click="advanceStage('at_destination')" wire:loading.attr="disabled"
                            class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Tiba di Lokasi Tujuan Customer</span>
                        </button>
                    @else
                        <div class="p-3.5 bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-800/60 rounded-xl">
                            <div class="flex items-center justify-between text-xs font-bold text-blue-900 dark:text-blue-200 mb-1">
                                <span class="flex items-center gap-1.5">
                                    <span class="inline-block w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                    Sedang Menuju Lokasi Tujuan
                                </span>
                                <span class="font-mono text-[11px] bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300 px-2 py-0.5 rounded-full">{{ $travelProgress['formatted_distance'] ?? '--' }}</span>
                            </div>
                            <p class="text-[11px] text-blue-800/80 dark:text-blue-300/80 leading-relaxed">
                                Tombol konfirmasi tiba akan otomatis muncul saat Anda berada dalam radius 50 meter dari tujuan pengantaran.
                            </p>
                        </div>
                    @endif
                @else
                    <button wire:click="openCompletionModal"
                        class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                        <span>{{ $help->service_type === 'pickup_delivery' ? 'Selesaikan & Unggah Bukti Serah Terima' : 'Selesaikan & Upload Bukti Pekerjaan' }}</span>
                    </button>
                @endif

                <button type="button" wire:click="openPartnerCancelModal"
                    class="w-full py-2.5 px-3 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-800/60 text-rose-700 dark:text-rose-300 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Ajukan Kendala Lapangan </span>
                </button>
            </div>
        @elseif ($help->status === 'waiting_customer_confirmation')
            {{-- Status Menunggu Konfirmasi Customer --}}
            <div class="bg-sky-50 dark:bg-sky-950/40 p-4 rounded-2xl border border-sky-200 dark:border-sky-800/60 shadow-xs mb-3 space-y-3">
                <div class="flex items-center justify-between gap-4 flex-wrap mb-1">
                    <h4 class="font-bold text-sm text-sky-950 dark:text-sky-100">Menunggu Konfirmasi Customer</h4>
                    <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700 shadow-2xs">
                        🛡️ Dana Diamankan
                    </span>
                </div>
                <p class="text-xs text-sky-900/80 dark:text-sky-200/85 leading-relaxed">
                    Bukti pengerjaan telah terkirim. Dana otomatis cair ke saldo Anda saat customer mengonfirmasi atau dalam batas waktu 24 jam.
                </p>
            </div>
        @endif
    </div>

    {{-- ───────────────────────────────────────────────────────────────── --}}
    {{-- LIGHTBOX PHOTO PREVIEW MODAL                                      --}}
    {{-- ───────────────────────────────────────────────────────────────── --}}
    <div x-show="previewPhotoUrl" 
         x-cloak 
         @click.self="previewPhotoUrl = null"
         class="fixed inset-0 z-[9999] bg-black/85 backdrop-blur-sm flex items-center justify-center p-4 animate-fade-in">
        <div class="relative max-w-3xl w-full max-h-[92vh] flex flex-col items-center">
            <button type="button" @click="previewPhotoUrl = null" class="absolute -top-10 right-0 text-white/90 hover:text-white bg-white/10 hover:bg-white/20 rounded-full p-2 transition cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <img :src="previewPhotoUrl" alt="Foto Penuh" class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl">
        </div>
    </div>

    {{-- ───────────────────────────────────────────────────────────────── --}}
    {{-- PARTNER CANCEL MODAL                                              --}}
    {{-- ───────────────────────────────────────────────────────────────── --}}
    @if ($showPartnerCancelModal)
        @php
            $isInProgress = ($help->status === 'in_progress');
        @endphp
        <div class="fixed inset-0 z-[9999] overflow-y-auto bg-black/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4 animate-fade-in" 
             wire:click.self="closePartnerCancelModal">
            <div class="bg-white dark:bg-gray-800 rounded-t-3xl sm:rounded-2xl w-full max-w-md shadow-2xl animate-slide-up relative max-h-[90vh] flex flex-col overflow-hidden border border-gray-100 dark:border-gray-700" 
                 style="padding-bottom: env(safe-area-inset-bottom, 16px);">
                
                <div class="sticky top-0 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 px-5 py-4 flex-shrink-0 z-10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl {{ $isInProgress ? 'bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400' : 'bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400' }} flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white leading-tight">
                                    {{ $isInProgress ? 'Ajukan Kendala Lapangan' : 'Batalkan Penugasan' }}
                                </h3>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ $isInProgress ? 'Pengajuan pembatalan tahap pengerjaan ' : 'Pengajuan pembatalan kendala perjalanan ' }}
                                </p>
                            </div>
                        </div>
                        <button type="button" 
                                wire:click="closePartnerCancelModal" 
                                class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <form wire:submit.prevent="requestPartnerCancel" class="p-5 overflow-y-auto space-y-4 text-xs">
                    @if($isInProgress)
                        {{-- INFO BANNER : PENGERJAAN SUDAH DIMULAI --}}
                        <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl p-3.5 space-y-2">
                            <div class="flex items-start gap-2.5">
                                <span class="text-lg leading-none mt-0.5">⚠️</span>
                                <div class="text-rose-950 dark:text-rose-200 leading-relaxed text-xs">
                                    <strong class="block font-bold mb-0.5">Penting (Tahap Pengerjaan Berjalan):</strong>
                                    Karena Anda telah menekan 'Mulai Pekerjaan', pembatalan ini memerlukan verifikasi dan konfirmasi dari <strong>Admin Wilayah</strong>.
                                </div>
                            </div>
                            <div class="pt-2 border-t border-rose-200/60 dark:border-rose-800/60 text-[11px] text-rose-900 dark:text-rose-300 space-y-1">
                                <p>📞 Tim Admin akan <strong>menghubungi Customer terlebih dahulu</strong> untuk klarifikasi kendala lapangan dan menyepakati pengembalian dana (refund) yang adil.</p>
                                <p>📸 <strong>Wajib / Sangat Disarankan:</strong> Sertakan foto bukti kondisi lapangan agar audit dan penyesuaian saldo berjalan lancar.</p>
                            </div>
                        </div>
                    @else
                        {{-- INFO BANNER : SAAT PERJALANAN / BELUM MULAI KERJA --}}
                        <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-2xl p-3.5 space-y-2">
                            <div class="flex items-start gap-2.5">
                                <span class="text-lg leading-none mt-0.5">ℹ️</span>
                                <div class="text-amber-900 dark:text-amber-200 leading-relaxed text-xs">
                                    <strong class="block font-bold mb-0.5">Pelepasan Tugas & Relist ke Pool:</strong>
                                    Tugas ini akan langsung dilepaskan dan dicarikan mitra lain agar customer tidak menunggu lama. Pengajuan Anda akan ditinjau oleh <strong>Admin Wilayah</strong>.
                                </div>
                            </div>
                            <div class="pt-2 border-t border-amber-200/60 dark:border-amber-800/60 text-[11px] text-amber-800 dark:text-amber-300">
                                ✓ <strong>Bukti Sah (Darurat / Kendala Nyata):</strong> Bebas Surat Peringatan (SP).<br>
                                ⚠️ <strong>Klaim Palsu / Berbohong:</strong> Admin Wilayah berhak memberikan sanksi SP (SP 1 / SP 2 / SP 3).
                            </div>
                        </div>
                    @endif

                    <div class="space-y-3.5">
                        <div>
                            <label class="block text-xs font-bold text-gray-900 dark:text-white mb-1.5">
                                Alasan Pembatalan <span class="text-rose-500">*</span>
                            </label>
                            <select wire:model="partnerCancelReason" 
                                    class="w-full p-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition cursor-pointer">
                                @if($isInProgress)
                                    <option value="">-- Pilih Alasan Kendala Lapangan  --</option>
                                    <option value="Lokasi / Kondisi Kerja Berbahaya & Tidak Aman">Lokasi / Kondisi Kerja Berbahaya & Tidak Aman</option>
                                    <option value="Beban / Volume Pekerjaan Melebihi Kesepakatan Awal">Beban / Volume Pekerjaan Melebihi Kesepakatan Awal</option>
                                    <option value="Alat Kerja / Material Mengalami Kerusakan di Lokasi">Alat Kerja / Material Mengalami Kerusakan di Lokasi</option>
                                    <option value="Customer Tidak Kooperatif / Meminta Penghentian Pekerjaan">Customer Tidak Kooperatif / Meminta Penghentian Pekerjaan</option>
                                    <option value="Mitra Mengalami Cedera / Sakit Mendadak Saat Bekerja">Mitra Mengalami Cedera / Sakit Mendadak Saat Bekerja</option>
                                    <option value="Lainnya">Lainnya (Tuliskan rincian di catatan)</option>
                                @else
                                    <option value="">-- Pilih Alasan Kendala Perjalanan  --</option>
                                    <option value="Kendaraan Bermasalah / Mogok / Ban Bocor">Kendaraan Bermasalah / Mogok / Ban Bocor</option>
                                    <option value="Terjebak Macet Total / Cuaca Ekstrem Tidak Memungkinkan">Terjebak Macet Total / Cuaca Ekstrem Tidak Memungkinkan</option>
                                    <option value="Kondisi Darurat Pribadi / Sakit di Perjalanan">Kondisi Darurat Pribadi / Sakit di Perjalanan</option>
                                    <option value="Customer Tidak Dapat Dihubungi Sebelum Mulai">Customer Tidak Dapat Dihubungi Sebelum Mulai</option>
                                    <option value="Lokasi / Akses Menuju Titik Ditutup / Bahaya">Lokasi / Akses Menuju Titik Ditutup / Bahaya</option>
                                    <option value="Lainnya">Lainnya (Tuliskan rincian di catatan)</option>
                                @endif
                            </select>
                            @error('partnerCancelReason') 
                                <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p> 
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-900 dark:text-white mb-1.5">
                                Foto Bukti Kendala <span class="text-rose-500 font-bold">* Wajib</span>
                            </label>
                            
                            @if ($cancel_evidence_photo)
                                <div class="relative rounded-xl overflow-hidden border border-rose-300 dark:border-rose-700 bg-gray-50 dark:bg-gray-800 p-2 mb-2 flex items-center justify-between">
                                    <span class="text-xs text-gray-700 dark:text-gray-300 truncate max-w-[240px]">
                                        📸 {{ method_exists($cancel_evidence_photo, 'getClientOriginalName') ? $cancel_evidence_photo->getClientOriginalName() : 'Foto bukti terpilih' }}
                                    </span>
                                    <button type="button" wire:click="$set('cancel_evidence_photo', null)" class="text-xs text-rose-600 hover:text-rose-700 font-bold px-2 py-1 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/50 cursor-pointer">
                                        Hapus
                                    </button>
                                </div>
                            @else
                                <input type="file" wire:model="cancel_evidence_photo" accept="image/*"
                                       class="w-full p-2 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-rose-50 file:text-rose-700 dark:file:bg-rose-950 dark:file:text-rose-300 cursor-pointer">
                            @endif
                            
                            <div wire:loading wire:target="cancel_evidence_photo" class="text-[11px] text-blue-600 font-medium mt-1 flex items-center gap-1.5">
                                <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                Mengunggah foto bukti...
                            </div>
                            @error('cancel_evidence_photo') 
                                <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p> 
                            @enderror
                            <p class="text-[10px] text-gray-400 mt-1">
                                {{ $isInProgress ? 'Unggah foto kondisi di tempat kerja/material rusak untuk memudahkan klarifikasi Admin dan Customer.' : 'Unggah foto ban bocor, kendala teknis, atau kondisi darurat untuk mempermudah audit bebas SP.' }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-900 dark:text-white mb-1.5">
                                Catatan Tambahan <span class="text-rose-500 font-bold">* Wajib</span>
                            </label>
                            <textarea wire:model="partnerCancelNotes" rows="3"
                                      class="w-full p-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition"
                                      placeholder="{{ $isInProgress ? 'Jelaskan kronologi kendala pengerjaan di lokasi secara detail...' : 'Jelaskan kendala darurat yang dialami secara rinci...' }}"></textarea>
                            @error('partnerCancelNotes') 
                                <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p> 
                            @enderror
                        </div>
                    </div>

                    <div class="flex gap-2.5 pt-2">
                        <button type="button"
                                wire:click="closePartnerCancelModal"
                                class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-semibold transition cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="requestPartnerCancel, cancel_evidence_photo"
                                class="flex-1 px-4 py-3 bg-rose-600 hover:bg-rose-700 active:bg-rose-800 text-white rounded-xl text-xs font-bold transition disabled:opacity-50 flex items-center justify-center gap-1.5 cursor-pointer shadow-sm shadow-rose-500/20 active:scale-[0.99]">
                            <span wire:loading.remove wire:target="requestPartnerCancel">
                                {{ $isInProgress ? 'Kirim Pengajuan ' : 'Kirim Pengajuan Batal' }}
                            </span>
                            <span wire:loading wire:target="requestPartnerCancel" class="inline-flex items-center gap-1">
                                <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ───────────────────────────────────────────────────────────────── --}}
    {{-- COMPLETION PROOF MODAL                                            --}}
    {{-- ───────────────────────────────────────────────────────────────── --}}
    @if($showCompletionModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4"
             wire:click.self="closeCompletionModal">
            <div class="bg-white dark:bg-gray-800 rounded-3xl w-full max-w-md shadow-2xl overflow-hidden animate-scale-in">
                
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
                            <p class="text-xs text-blue-100">Tugas otomatis tuntas & dana langsung masuk</p>
                        </div>
                    </div>
                    <button wire:click="closeCompletionModal" class="p-1.5 rounded-lg hover:bg-white/20 transition">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="submitCompletionProof" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                            Upload Foto Bukti Jasa / Serah Terima <span class="text-rose-500">*</span>
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

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-200 mb-1.5">
                            Catatan Pengerjaan (Opsional)
                        </label>
                        <textarea wire:model="completion_notes" rows="3" placeholder="Contoh: Pekerjaan sudah tuntas dan diserahterimakan dengan baik..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 text-xs focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                    </div>

                    <div class="flex gap-2.5 pt-2">
                        <button type="button" wire:click="closeCompletionModal"
                            class="flex-1 py-3 px-4 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-semibold text-xs hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="submitCompletionProof, proof_photo"
                            class="flex-1 py-3 px-4 rounded-xl bg-blue-600 text-white font-bold text-xs shadow-md hover:bg-blue-700 transition flex items-center justify-center gap-1.5 disabled:opacity-50">
                            <span wire:loading.remove wire:target="submitCompletionProof">Selesaikan Tugas</span>
                            <span wire:loading wire:target="submitCompletionProof" class="inline-flex items-center gap-1">
                                <svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    {{-- ───────────────────────────────────────────────────────────────── --}}
    {{-- REJECT WITHDRAWAL MODAL                                           --}}
    {{-- ───────────────────────────────────────────────────────────────── --}}
    @if ($showRejectWithdrawModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 animate-fade-in"
             wire:click.self="closeRejectWithdrawModal">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
                <div class="bg-rose-600 px-5 py-4 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center font-bold">
                            ✕
                        </div>
                        <div>
                            <h3 class="font-bold text-sm leading-tight">Tolak Penarikan & Ajukan Pembelaan</h3>
                            <p class="text-[11px] text-rose-100">Kirim keterangan pembelaan untuk Admin Wilayah</p>
                        </div>
                    </div>
                    <button wire:click="closeRejectWithdrawModal" class="p-1 rounded-lg hover:bg-white/20 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="rejectWithdrawal" class="p-5 space-y-3.5 text-xs">
                    <div class="bg-amber-50 dark:bg-amber-950/40 p-3 rounded-xl border border-amber-200 dark:border-amber-800 text-[11px] text-amber-900 dark:text-amber-200 leading-relaxed">
                        Jika Anda sudah dalam perjalanan atau telah tiba di lokasi, jelaskan alasan penolakan ini agar Admin Wilayah dapat mengaudit telemetri GPS dan memberikan kompensasi/sanksi yang adil.
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-200 mb-1">
                            Alasan Penolakan / Penjelasan Anda <span class="text-rose-500">*</span>
                        </label>
                        <textarea wire:model="rejectWithdrawNotes" rows="3"
                                  placeholder="Contoh: Saya sudah di jalan menempuh 3 km dan hampir sampai di lokasi customer..."
                                  class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-750 text-gray-900 dark:text-white placeholder-gray-400 text-xs focus:ring-2 focus:ring-rose-500"></textarea>
                        @error('rejectWithdrawNotes') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 dark:text-gray-200 mb-1">Foto Bukti Lapangan (Opsional)</label>
                        <input type="file" wire:model="rejectWithdrawPhoto" accept="image/*" class="w-full p-2 text-xs bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-600 rounded-xl">
                        @error('rejectWithdrawPhoto') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" wire:click="closeRejectWithdrawModal"
                                class="flex-1 py-2.5 px-3 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-bold hover:bg-gray-100 dark:hover:bg-gray-750 transition">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="rejectWithdrawal, rejectWithdrawPhoto"
                                class="flex-1 py-2.5 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold shadow-md transition flex items-center justify-center gap-1.5 disabled:opacity-50">
                            <span wire:loading.remove wire:target="rejectWithdrawal">Kirim Pembelaan</span>
                            <span wire:loading wire:target="rejectWithdrawal">Mengirim...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Photo Preview Modal (Tap to Zoom) --}}
    <div x-show="previewPhotoUrl" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
         @click="previewPhotoUrl = null"
         style="display: none;">
        <div class="relative max-w-2xl w-full bg-transparent p-2 text-center" @click.stop>
            <button type="button" @click="previewPhotoUrl = null" 
                    class="absolute top-4 right-4 bg-black/60 hover:bg-black text-white rounded-full p-2.5 transition z-10 cursor-pointer shadow-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <img :src="previewPhotoUrl" alt="Foto Bukti Diperbesar" class="max-h-[85vh] w-auto mx-auto rounded-2xl shadow-2xl object-contain border border-white/10">
        </div>
    </div>
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
    </style>
@endpush

@push('scripts')
    <script>
        (function() {
            let mapInstance = null;
            let markersGroup = null;

            function ensureLeaflet(callback) {
                if (typeof window.L !== 'undefined') {
                    callback();
                    return;
                }

                if (!document.querySelector('link[href*="leaflet.css"]')) {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    document.head.appendChild(link);
                }

                let script = document.querySelector('script[src*="leaflet.js"]');
                if (!script) {
                    script = document.createElement('script');
                    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    script.onload = function() {
                        callback();
                    };
                    document.head.appendChild(script);
                } else {
                    let attempts = 0;
                    const interval = setInterval(function() {
                        attempts++;
                        if (typeof window.L !== 'undefined') {
                            clearInterval(interval);
                            callback();
                        } else if (attempts > 50) {
                            clearInterval(interval);
                        }
                    }, 50);
                }
            }

            function initMitraRouteMap() {
                const mapEl = document.getElementById('mitra-route-map');
                if (!mapEl) return;

                ensureLeaflet(function() {
                    if (mapInstance) {
                        try { mapInstance.remove(); } catch(e) {}
                        mapInstance = null;
                    }

                    if (mapEl._leaflet_id) {
                        mapEl._leaflet_id = null;
                    }

                    @if($help->isPickup())
                        const pLat = parseFloat("{{ $help->pickup_latitude ?: $help->latitude }}") || -7.7956;
                        const pLng = parseFloat("{{ $help->pickup_longitude ?: $help->longitude }}") || 110.3695;
                        const dLat = parseFloat("{{ $help->delivery_latitude ?: $help->latitude }}") || pLat;
                        const dLng = parseFloat("{{ $help->delivery_longitude ?: $help->longitude }}") || pLng;
                    @else
                        const pLat = parseFloat("{{ $help->latitude }}") || -7.7956;
                        const pLng = parseFloat("{{ $help->longitude }}") || 110.3695;
                        const dLat = null;
                        const dLng = null;
                    @endif

                    try {
                        mapInstance = L.map(mapEl, {
                            zoomControl: false,
                            attributionControl: false
                        }).setView([pLat, pLng], 14);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19
                        }).addTo(mapInstance);

                        markersGroup = L.featureGroup().addTo(mapInstance);

                        // Pickup / Main marker
                        const pIcon = L.divIcon({
                            className: 'mitra-pulse-icon',
                            html: '<div class="mitra-pulse-dot" style="background: #10b981;"></div>',
                            iconSize: [20, 20],
                            iconAnchor: [10, 10]
                        });
                        L.marker([pLat, pLng], { icon: pIcon }).addTo(markersGroup)
                            .bindPopup("{{ $help->isPickup() ? '1. Titik Jemput' : 'Lokasi Tugas Customer' }}");

                        // Delivery marker (if pickup_delivery)
                        if (dLat && dLng && (dLat !== pLat || dLng !== pLng)) {
                            const dIcon = L.divIcon({
                                className: 'dest-pulse-icon',
                                html: '<div class="dest-pulse-dot"></div>',
                                iconSize: [22, 22],
                                iconAnchor: [11, 11]
                            });
                            L.marker([dLat, dLng], { icon: dIcon }).addTo(markersGroup)
                                .bindPopup("2. Titik Antar / Tujuan");

                            L.polyline([[pLat, pLng], [dLat, dLng]], {
                                color: '#2563eb',
                                weight: 4,
                                opacity: 0.8,
                                dashArray: '8, 8'
                            }).addTo(markersGroup);

                            mapInstance.fitBounds(markersGroup.getBounds().pad(0.2));
                        }

                        const recenterBtn = document.getElementById('btn-recenter-route-map');
                        if (recenterBtn) {
                            recenterBtn.onclick = function() {
                                if (markersGroup && markersGroup.getLayers().length > 0) {
                                    mapInstance.fitBounds(markersGroup.getBounds().pad(0.2));
                                } else {
                                    mapInstance.setView([pLat, pLng], 14);
                                }
                            };
                        }

                        setTimeout(function() {
                            if (mapInstance) mapInstance.invalidateSize();
                        }, 120);

                        setTimeout(function() {
                            if (mapInstance) mapInstance.invalidateSize();
                        }, 450);
                    } catch(err) {
                        console.warn('[MitraMap] Init warning:', err);
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', initMitraRouteMap);
            document.addEventListener('livewire:navigated', function() {
                setTimeout(initMitraRouteMap, 60);
            });
            document.addEventListener('livewire:init', function() {
                if (window.Livewire) {
                    Livewire.hook('commit', function({ component, commit, respond, succeed, fail }) {
                        succeed(function() {
                            setTimeout(function() {
                                const mapEl = document.getElementById('mitra-route-map');
                                if (mapEl && (!mapInstance || !mapEl.hasChildNodes())) {
                                    initMitraRouteMap();
                                } else if (mapInstance) {
                                    mapInstance.invalidateSize();
                                }
                            }, 100);
                        });
                    });
                }
            });
        })();
    </script>
@endpush