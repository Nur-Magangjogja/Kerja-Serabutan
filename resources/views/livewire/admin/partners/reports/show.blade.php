<div class="space-y-6 max-w-full">
    @php
        $routePrefix = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'superadmin.' : 'admin.';

        $formatWa = function(?string $phone, string $text = '') {
            if (!$phone) return null;
            $clean = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($clean, '0')) {
                $clean = '62' . substr($clean, 1);
            } elseif (str_starts_with($clean, '8')) {
                $clean = '62' . $clean;
            }
            return 'https://wa.me/' . $clean . ($text ? '?text=' . urlencode($text) : '');
        };

        $rep = $report->reporter ?? $report->user;
        $mitra = $report->reportedUser ?? $help?->mitra;

        $customerWaText = "Halo Kak " . ($rep?->name ?? 'Customer') . ", Kami dari Tim Admin Moderasi SayaBantu ingin mengkonfirmasi Laporan Aduan #" . $report->id . ($help ? " terkait Bantuan #" . $help->id . " '" . $help->title . "'" : "") . ". Mohon waktu sebentar untuk verifikasi data.";
        $mitraWaText = "Halo Rekan " . ($mitra?->name ?? 'Mitra') . ", Kami dari Tim Admin Moderasi SayaBantu ingin meminta klarifikasi dan konfirmasi terkait Laporan Aduan #" . $report->id . ($help ? " pada tugas Bantuan #" . $help->id . " '" . $help->title . "'" : "") . ". Mohon segera merespons.";
    @endphp

    {{-- ===== Flash Notification ===== --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-2xl text-xs flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span class="font-semibold break-words">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 rounded-2xl text-xs flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <span class="font-semibold break-words">{{ session('error') }}</span>
        </div>
    @endif

    {{-- ===== Page Header ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white break-words">Detail Laporan Aduan</h1>
                @if ($report->refund_status === 'requested')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-800 animate-pulse whitespace-nowrap">
                        🛡️ Pengajuan Refund
                    </span>
                @elseif ($report->refund_status === 'approved')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 whitespace-nowrap">
                        ✅ Refund Disetujui
                    </span>
                @elseif ($report->refund_status === 'rejected')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800 whitespace-nowrap">
                        ❌ Refund Ditolak
                    </span>
                @endif
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Penanganan sengketa, klarifikasi mitra & customer, investigasi bukti, audit disiplin SP, dan moderasi dana</p>
        </div>
        <a href="{{ route($routePrefix . 'partners.reports') }}"
            class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors w-full sm:w-auto shrink-0 shadow-xs">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Daftar Laporan</span>
        </a>
    </div>

    {{-- ===== Main Content ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6 min-w-0">
            {{-- Informasi Laporan --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-4 sm:p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span>📝 Informasi Aduan</span>
                    </h2>
                    @php
                    $stClass = match($report->status) {
                        'pending'     => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400',
                        'in_progress' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400',
                        'resolved'    => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400',
                        default       => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
                    };
                    @endphp
                    <span class="inline-flex items-center self-start sm:self-auto px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap {{ $stClass }}">
                        Status: {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                    </span>
                </div>

                <div class="space-y-3 text-xs sm:text-sm">
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">Judul Aduan</p>
                        <p class="font-bold text-gray-900 dark:text-white mt-0.5 text-sm sm:text-base break-words">{{ $report->title }}</p>
                    </div>
                    <div class="p-3.5 bg-gray-50 dark:bg-gray-700/40 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-1 font-medium">Pesan / Keluhan Pelapor</p>
                        <p class="text-gray-700 dark:text-gray-200 whitespace-pre-line leading-relaxed text-xs sm:text-sm break-words [overflow-wrap:anywhere]">{{ $report->message }}</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs pt-1">
                        <div class="min-w-0">
                            <span class="text-gray-400 block text-[11px]">Kategori Aduan</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 truncate block">{{ ucfirst($report->category ?? 'Umum') }}</span>
                        </div>
                        <div class="min-w-0">
                            <span class="text-gray-400 block text-[11px]">Jenis Alur</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 truncate block">{{ $report->report_type ?: 'Bantuan' }}</span>
                        </div>
                        <div class="min-w-0">
                            <span class="text-gray-400 block text-[11px]">Waktu Dilaporkan</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 block">{{ $report->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="min-w-0">
                            <span class="text-gray-400 block text-[11px]">Terakhir Diupdate</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 block">{{ $report->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                {{-- Bukti Foto Lampiran --}}
                @if ($report->photo || $report->evidence_photo)
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Bukti Foto Lampiran:</h4>
                        <div class="flex items-center gap-3 flex-wrap">
                            @if ($report->photo)
                                <a href="{{ asset('storage/' . $report->photo) }}" target="_blank" class="block w-24 h-24 sm:w-28 sm:h-28 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 hover:opacity-90 transition shrink-0">
                                    <img src="{{ asset('storage/' . $report->photo) }}" alt="Bukti 1" class="w-full h-full object-cover">
                                </a>
                            @endif
                            @if ($report->evidence_photo)
                                <a href="{{ asset('storage/' . $report->evidence_photo) }}" target="_blank" class="block w-24 h-24 sm:w-28 sm:h-28 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-600 hover:opacity-90 transition shrink-0">
                                    <img src="{{ asset('storage/' . $report->evidence_photo) }}" alt="Bukti 2" class="w-full h-full object-cover">
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Kartu Kontak & Disiplin SP Pelapor & Terlapor --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-4 sm:p-6 space-y-4">
                <div class="flex items-center justify-between gap-2 pb-3 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span>👥 Pihak Terlibat & Audit Status Kedisiplinan (SP)</span>
                    </h3>
                    <span class="text-[11px] font-semibold text-gray-500 dark:text-gray-400">Pemberian SP Instan Tanpa Berpindah Halaman</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Kontak & Status SP Pelapor --}}
                    <div class="p-4 sm:p-5 bg-gradient-to-br from-blue-50/40 via-white to-gray-50 dark:from-gray-700/40 dark:via-gray-800 dark:to-gray-800 rounded-2xl border border-blue-100 dark:border-gray-700 space-y-3.5 min-w-0 shadow-2xs">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 min-w-0">
                            <div class="min-w-0 flex-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                                    Pelapor ({{ $rep?->role === 'mitra' ? 'Mitra' : 'Customer' }})
                                </span>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white break-words mt-1">{{ $rep?->name ?? 'User' }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $rep?->email ?? '-' }}</p>
                            </div>
                            <span class="text-xs font-extrabold text-blue-600 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/60 px-2.5 py-1 rounded-lg self-start sm:self-auto shrink-0 whitespace-nowrap">
                                Saldo: Rp {{ number_format($rep?->balance ?? 0, 0, ',', '.') }}
                            </span>
                        </div>

                        {{-- Section Status SP & Greylist Pelapor --}}
                        <div class="p-3 bg-white/80 dark:bg-gray-700/60 rounded-xl border border-blue-100 dark:border-gray-600 space-y-2">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400">Tingkat SP Saat Ini:</span>
                                @php
                                    $repSp = (int) ($rep?->warning_level ?? 0);
                                @endphp
                                @if($repSp === 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300">
                                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        Bersih (0 SP)
                                    </span>
                                @elseif($repSp === 1)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 animate-pulse">
                                        ⚠️ SP 1 (Ringan)
                                    </span>
                                @elseif($repSp === 2)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-orange-100 text-orange-800 dark:bg-orange-950/60 dark:text-orange-300 border border-orange-300 animate-pulse">
                                        🟠 SP 2 (Keras)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 animate-pulse">
                                        🔴 SP 3 (Sanksi Berat)
                                    </span>
                                @endif
                            </div>

                            @if($rep?->is_greylisted)
                                <div class="pt-1.5 border-t border-gray-100 dark:border-gray-600 flex items-start gap-1.5 text-[11px] text-amber-800 dark:text-amber-300">
                                    <span class="font-extrabold">🏴 Daftar Abu-Abu:</span>
                                    <span class="text-gray-600 dark:text-gray-300 line-clamp-2" title="{{ $rep->greylist_reason }}">{{ $rep->greylist_reason ?: 'Dalam pengawasan khusus' }}</span>
                                </div>
                            @endif

                            @if($rep && $rep->greylistLogs->isNotEmpty())
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                    Total {{ $rep->greylistLogs->count() }} catatan riwayat SP/kedisiplinan tercatat di sistem.
                                </p>
                            @endif
                        </div>

                        <div class="text-xs space-y-1 text-gray-600 dark:text-gray-300">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-gray-400">No. HP:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $rep?->phone ?? 'Belum tercantum' }}</span>
                            </div>
                        </div>

                        {{-- Action Buttons Pelapor --}}
                        <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 pt-3 border-t border-gray-100 dark:border-gray-600">
                            @if ($rep?->phone)
                                @php $customerWaUrl = $formatWa($rep->phone, $customerWaText); @endphp
                                <a href="{{ $customerWaUrl }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                                    <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    <span>WhatsApp</span>
                                </a>
                                <a href="tel:{{ $rep->phone }}"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-xl text-xs font-semibold transition">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span>Telepon</span>
                                </a>
                            @endif

                            @if($rep)
                                <button type="button" wire:click="openSpModal({{ $rep->id }})"
                                    class="col-span-2 sm:col-span-1 inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800 rounded-xl text-xs font-extrabold transition cursor-pointer shadow-2xs active:scale-95">
                                    <span>⚡ Beri SP Instan</span>
                                </button>
                                <a href="{{ route($routePrefix . 'partners.greylist', ['search' => $rep->name]) }}" target="_blank"
                                    class="col-span-2 sm:col-span-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl text-xs font-semibold transition">
                                    <span>Log SP</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Kontak & Status SP Terlapor --}}
                    <div class="p-4 sm:p-5 bg-gradient-to-br from-amber-50/40 via-white to-gray-50 dark:from-gray-700/40 dark:via-gray-800 dark:to-gray-800 rounded-2xl border border-amber-100 dark:border-gray-700 space-y-3.5 min-w-0 shadow-2xs">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 min-w-0">
                            <div class="min-w-0 flex-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                                    Terlapor ({{ $mitra?->role === 'customer' ? 'Customer' : 'Mitra' }})
                                </span>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white break-words mt-1">{{ $mitra?->name ?? 'Mitra' }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $mitra?->email ?? '-' }}</p>
                            </div>
                            <span class="text-xs font-extrabold text-emerald-600 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/60 px-2.5 py-1 rounded-lg self-start sm:self-auto shrink-0 whitespace-nowrap">
                                Saldo: Rp {{ number_format($mitra?->balance ?? 0, 0, ',', '.') }}
                            </span>
                        </div>

                        {{-- Section Status SP & Greylist Terlapor --}}
                        <div class="p-3 bg-white/80 dark:bg-gray-700/60 rounded-xl border border-amber-100 dark:border-gray-600 space-y-2">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400">Tingkat SP Saat Ini:</span>
                                @php
                                    $mitraSp = (int) ($mitra?->warning_level ?? 0);
                                @endphp
                                @if($mitraSp === 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300">
                                        <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        Bersih (0 SP)
                                    </span>
                                @elseif($mitraSp === 1)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 animate-pulse">
                                        ⚠️ SP 1 (Ringan)
                                    </span>
                                @elseif($mitraSp === 2)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-orange-100 text-orange-800 dark:bg-orange-950/60 dark:text-orange-300 border border-orange-300 animate-pulse">
                                        🟠 SP 2 (Keras)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 animate-pulse">
                                        🔴 SP 3 (Sanksi Berat)
                                    </span>
                                @endif
                            </div>

                            @if($mitra?->is_greylisted)
                                <div class="pt-1.5 border-t border-gray-100 dark:border-gray-600 flex items-start gap-1.5 text-[11px] text-amber-800 dark:text-amber-300">
                                    <span class="font-extrabold">🏴 Daftar Abu-Abu:</span>
                                    <span class="text-gray-600 dark:text-gray-300 line-clamp-2" title="{{ $mitra->greylist_reason }}">{{ $mitra->greylist_reason ?: 'Dalam pengawasan khusus' }}</span>
                                </div>
                            @endif

                            @if($mitra && $mitra->greylistLogs->isNotEmpty())
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">
                                    Total {{ $mitra->greylistLogs->count() }} catatan riwayat SP/kedisiplinan tercatat di sistem.
                                </p>
                            @endif
                        </div>

                        <div class="text-xs space-y-1 text-gray-600 dark:text-gray-300">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-gray-400">No. HP:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $mitra?->phone ?? 'Belum tercantum' }}</span>
                            </div>
                        </div>

                        {{-- Action Buttons Mitra --}}
                        <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 pt-3 border-t border-gray-100 dark:border-gray-600">
                            @if ($mitra?->phone)
                                @php $mitraWaUrl = $formatWa($mitra->phone, $mitraWaText); @endphp
                                <a href="{{ $mitraWaUrl }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                                    <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    <span>WhatsApp</span>
                                </a>
                                <a href="tel:{{ $mitra->phone }}"
                                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-xl text-xs font-semibold transition">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span>Telepon</span>
                                </a>
                            @endif

                            @if($mitra)
                                <button type="button" wire:click="openSpModal({{ $mitra->id }})"
                                    class="col-span-2 sm:col-span-1 inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800 rounded-xl text-xs font-extrabold transition cursor-pointer shadow-2xs active:scale-95">
                                    <span>⚡ Beri SP Instan</span>
                                </button>
                                <a href="{{ route($routePrefix . 'partners.greylist', ['search' => $mitra->name]) }}" target="_blank"
                                    class="col-span-2 sm:col-span-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/60 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl text-xs font-semibold transition">
                                    <span>Log SP</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Informasi Bantuan & Aliran Dana Escrow --}}
            @if ($help)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-4 sm:p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span>🛡️ Informasi Tugas & Aliran Dana Escrow</span>
                        </h3>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 self-start sm:self-auto whitespace-nowrap">
                            Status Bantuan: {{ strtoupper($help->status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs bg-gray-50 dark:bg-gray-700/40 p-3.5 rounded-xl border border-gray-100 dark:border-gray-600">
                        <div class="min-w-0">
                            <span class="text-gray-400 block text-[11px]">Judul Tugas:</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 break-words block">{{ $help->title }}</span>
                        </div>
                        <div class="min-w-0">
                            <span class="text-gray-400 block text-[11px]">Total Pembayaran Customer:</span>
                            <span class="font-extrabold text-gray-900 dark:text-white block">Rp {{ number_format($help->total_amount ?: $help->amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="min-w-0">
                            <span class="text-gray-400 block text-[11px]">Saldo Masuk ke Mitra:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 block">Rp {{ number_format($help->mitra_earning ?: $help->amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Log Transaksi Terkait --}}
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Riwayat Ledger Transaksi Terkait:</h4>
                        @if ($transactions->isNotEmpty())
                            <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                                <table class="w-full text-xs text-left text-gray-600 dark:text-gray-300 min-w-[340px]">
                                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold">
                                        <tr>
                                            <th class="py-2.5 px-3">Tipe</th>
                                            <th class="py-2.5 px-3">Nominal</th>
                                            <th class="py-2.5 px-3">Keterangan</th>
                                            <th class="py-2.5 px-3">Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @foreach ($transactions as $tx)
                                            <tr>
                                                <td class="py-2 px-3 font-semibold whitespace-nowrap">
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] {{ $tx->type === 'refund' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                                        {{ strtoupper($tx->type) }}
                                                    </span>
                                                </td>
                                                <td class="py-2 px-3 font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                                    Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                                </td>
                                                <td class="py-2 px-3 text-gray-500 break-words">{{ $tx->description ?: '-' }}</td>
                                                <td class="py-2 px-3 text-gray-400 whitespace-nowrap">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic">Belum ada mutasi ledger khusus untuk pesanan ini.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Kolom Kanan: Aksi Moderasi, Refund & Catatan --}}
        <div class="space-y-6 min-w-0">
            {{-- Panel Moderasi Status --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-4 sm:p-5 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 pb-2 border-b border-gray-100 dark:border-gray-700">
                    <span>⚡ Aksi Moderasi Aduan</span>
                </h3>

                <div class="space-y-2">
                    <button type="button" wire:click="updateStatus('in_progress')"
                        class="w-full py-2.5 px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer {{ $report->status === 'in_progress' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300' }}">
                        <span>🔍 Set In Progress (Investigasi)</span>
                    </button>

                    <button type="button" wire:click="updateStatus('resolved')"
                        class="w-full py-2.5 px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 cursor-pointer {{ $report->status === 'resolved' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300' }}">
                        <span>✅ Selesaikan Aduan (Resolved)</span>
                    </button>

                    <button type="button" wire:click="updateStatus('dismissed')"
                        class="w-full py-2.5 px-4 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-semibold transition cursor-pointer flex items-center justify-center gap-2">
                        <span>Tutup / Abaikan (Dismiss)</span>
                    </button>
                </div>

                {{-- Ruang Chat Khusus --}}
                <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                    <a href="{{ route($routePrefix . 'partners.reports.chat', $report->id) }}"
                        class="w-full py-2.5 px-4 bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-700 hover:to-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center justify-center gap-2">
                        <span>💬 Buka Ruang Chat Investigasi</span>
                    </a>
                </div>
            </div>

            {{-- Panel Pengajuan Refund Escrow --}}
            @if ($report->refund_status === 'requested')
                <div class="bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent dark:from-amber-950/40 border border-amber-300 dark:border-amber-700/60 rounded-2xl p-4 sm:p-5 shadow-xs space-y-3">
                    <div class="flex items-center gap-2 text-amber-900 dark:text-amber-300 font-extrabold text-sm">
                        <span>🛡️ Pengajuan Refund Pelanggan</span>
                    </div>

                    <p class="text-xs text-amber-800 dark:text-amber-200">
                        Customer mengajukan pengembalian dana 100% untuk pesanan ini.
                    </p>

                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <button type="button" wire:click="openRefundModal"
                            class="py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer text-center">
                            ✓ Setujui Refund
                        </button>
                        <button type="button" wire:click="openRejectModal"
                            class="py-2.5 px-3 bg-rose-100 hover:bg-rose-200 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 rounded-xl text-xs font-bold transition cursor-pointer text-center">
                            ✕ Tolak
                        </button>
                    </div>
                </div>
            @endif

            {{-- Catatan Internal Tim Admin --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-4 sm:p-5 space-y-3">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>📌 Catatan Internal Admin</span>
                </h3>

                <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-xl text-xs text-gray-700 dark:text-gray-300 whitespace-pre-line max-h-48 overflow-y-auto border border-gray-100 dark:border-gray-600 break-words">
                    {{ $adminNotes ?: 'Belum ada catatan internal.' }}
                </div>

                <div class="space-y-2 pt-2">
                    <textarea wire:model="newNote" rows="2" placeholder="Tulis catatan investigasi baru..."
                        class="w-full p-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    <button type="button" wire:click="saveAdminNote"
                        class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer">
                        + Tambahkan Catatan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MODAL PENERBITAN SP INSTAN (SURAT PERINGATAN LANGSUNG)        --}}
    {{-- ============================================================ --}}
    @if($showSpModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-sm">
                            ⚡
                        </span>
                        <div>
                            <h3 class="text-sm font-extrabold text-gray-900 dark:text-white">Penerbitan Surat Peringatan (SP) Instan</h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Jatuhkan sanksi kedisiplinan langsung dari laporan ini</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeSpModal" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Ringkasan Target User --}}
                <div class="p-3.5 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-200 dark:border-gray-600 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[10px] uppercase tracking-wider font-extrabold text-gray-400">Target Pelanggar</p>
                        <p class="font-bold text-sm text-gray-900 dark:text-white truncate">{{ $spTargetUserName }}</p>
                        <span class="inline-block text-[10px] font-semibold text-gray-500 capitalize">
                            Peran: {{ $spTargetUserRole === 'mitra' ? 'Mitra Lapangan' : 'Customer' }}
                        </span>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-[10px] text-gray-400 font-medium">Status Saat Ini</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-extrabold {{ $spCurrentWarningLevel > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' }}">
                            {{ $spCurrentWarningLevel > 0 ? "SP {$spCurrentWarningLevel}" : 'Bersih (0 SP)' }}
                        </span>
                    </div>
                </div>

                {{-- Pilih Tingkat SP --}}
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300">Pilih Tingkat SP yang Diterbitkan:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition {{ (int)$spWarningLevel === 1 ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200 ring-2 ring-amber-500/20' : 'border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/40 text-gray-700 dark:text-gray-300' }}">
                            <div class="flex items-center gap-2">
                                <input type="radio" wire:model="spWarningLevel" value="1" class="text-amber-600 focus:ring-amber-500">
                                <span class="font-bold text-xs">SP 1</span>
                            </div>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">Peringatan Ringan / Awal</span>
                        </label>

                        <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition {{ (int)$spWarningLevel === 2 ? 'border-orange-500 bg-orange-50/50 dark:bg-orange-950/30 text-orange-900 dark:text-orange-200 ring-2 ring-orange-500/20' : 'border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/40 text-gray-700 dark:text-gray-300' }}">
                            <div class="flex items-center gap-2">
                                <input type="radio" wire:model="spWarningLevel" value="2" class="text-orange-600 focus:ring-orange-500">
                                <span class="font-bold text-xs">SP 2</span>
                            </div>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">Peringatan Keras (Pelanggaran Berulang)</span>
                        </label>

                        <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition {{ (int)$spWarningLevel === 3 ? 'border-rose-500 bg-rose-50/50 dark:bg-rose-950/30 text-rose-900 dark:text-rose-200 ring-2 ring-rose-500/20' : 'border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/40 text-gray-700 dark:text-gray-300' }}">
                            <div class="flex items-center gap-2">
                                <input type="radio" wire:model="spWarningLevel" value="3" class="text-rose-600 focus:ring-rose-500">
                                <span class="font-bold text-xs text-rose-600 dark:text-rose-400">SP 3</span>
                            </div>
                            <span class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">Sanksi Berat & Shadow Ban (Mitra)</span>
                        </label>
                    </div>
                </div>

                {{-- Alasan Pelanggaran --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Alasan Pelanggaran / Dasar Penerbitan SP <span class="text-rose-500">*</span>
                    </label>
                    <textarea wire:model="spReason" rows="3" placeholder="Jelaskan alasan detail pemberian SP (misal: Terbukti lalai dalam pengerjaan tugas, tidak hadir tanpa konfirmasi, dll)..."
                        class="w-full p-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                    @error('spReason') <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Opsi Catat ke Catatan Admin --}}
                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="spAutoNoteInReport" wire:model="spAutoNoteInReport" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <label for="spAutoNoteInReport" class="text-xs text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                        Otomatis cantumkan tindakan SP ini ke dalam Catatan Internal Laporan
                    </label>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="closeSpModal" class="w-full sm:w-auto px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-semibold text-center">Batal</button>
                    <button type="button" wire:click="submitInstantSp" wire:loading.attr="disabled" class="w-full sm:w-auto px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-extrabold shadow-sm text-center flex items-center justify-center gap-2 cursor-pointer active:scale-95 transition">
                        <span wire:loading.remove wire:target="submitInstantSp">⚡ Terbitkan SP {{ $spWarningLevel }} Sekarang</span>
                        <span wire:loading wire:target="submitInstantSp">Memproses...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL REFUND APPROVAL --}}
    @if($showRefundModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4 max-h-[90vh] overflow-y-auto">
                <h3 class="text-sm font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>🛡️</span> Konfirmasi Persetujuan Refund
                </h3>
                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed break-words">
                    Dana sebesar <strong>Rp {{ number_format($help?->total_amount ?: $help?->amount ?: 0, 0, ',', '.') }}</strong> akan dikembalikan utuh ke saldo dompet customer <strong>{{ $rep?->name }}</strong>.
                </p>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Catatan Persetujuan (Opsional)</label>
                    <textarea wire:model="refundAdminNotes" rows="2" placeholder="Contoh: Refund disetujui karena mitra tidak dapat hadir."
                        class="w-full p-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                </div>
                <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="closeRefundModal" class="w-full sm:w-auto px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-semibold text-center">Batal</button>
                    <button type="button" wire:click="processRefund" class="w-full sm:w-auto px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs text-center">Proses Refund Sekarang</button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL REFUND REJECTION --}}
    @if($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4 max-h-[90vh] overflow-y-auto">
                <h3 class="text-sm font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>❌</span> Tolak Permintaan Refund
                </h3>
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Alasan Penolakan Resmi</label>
                    <textarea wire:model="rejectReason" rows="3" placeholder="Jelaskan alasan penolakan refund..."
                        class="w-full p-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    @error('rejectReason') <span class="text-rose-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" wire:click="closeRejectModal" class="w-full sm:w-auto px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-semibold text-center">Batal</button>
                    <button type="button" wire:click="submitRejectRefund" class="w-full sm:w-auto px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-xs text-center">Tolak Refund</button>
                </div>
            </div>
        </div>
    @endif
</div>
