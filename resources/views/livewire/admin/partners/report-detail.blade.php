@extends(in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'layouts.superadmin' : 'layouts.admin')

@section('content')
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
<div class="space-y-6">
    {{-- ===== Flash Notification ===== --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-2xl text-xs flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 rounded-2xl text-xs flex items-center gap-3 shadow-xs">
            <svg class="w-5 h-5 text-rose-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <span class="font-semibold">{{ session('error') }}</span>
        </div>
    @endif

    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Detail Laporan Aduan #{{ $report->id }}</h1>
                @if ($report->refund_status === 'requested')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-800 animate-pulse">
                        🛡️ Pengajuan Refund
                    </span>
                @elseif ($report->refund_status === 'approved')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                        ✅ Refund Disetujui
                    </span>
                @elseif ($report->refund_status === 'rejected')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                        ❌ Refund Ditolak
                    </span>
                @endif
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Penanganan sengketa, klarifikasi mitra & customer, investigasi bukti, dan moderasi dana</p>
        </div>
        <a href="{{ route($routePrefix . 'partners.report') }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Laporan
        </a>
    </div>

    {{-- ===== Main Content ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Informasi Laporan --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
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
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $stClass }}">
                        Status: {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                    </span>
                </div>

                <div class="space-y-3 text-xs sm:text-sm">
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">Judul Aduan</p>
                        <p class="font-bold text-gray-900 dark:text-white mt-0.5 text-sm sm:text-base">{{ $report->title }}</p>
                    </div>
                    <div class="p-3.5 bg-gray-50 dark:bg-gray-700/40 rounded-xl border border-gray-100 dark:border-gray-700">
                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-1 font-medium">Pesan / Keluhan Pelapor</p>
                        <p class="text-gray-700 dark:text-gray-200 whitespace-pre-line leading-relaxed text-xs sm:text-sm">{{ $report->message }}</p>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs pt-1">
                        <div>
                            <p class="text-gray-400">Jenis Laporan</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 mt-1">
                                {{ $report->report_type_label }}
                            </span>
                        </div>
                        <div>
                            <p class="text-gray-400">Kategori</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold {{ $report->isFromCustomer() ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' }} mt-1">
                                {{ $report->category_label }}
                            </span>
                        </div>
                        <div>
                            <p class="text-gray-400">Tanggal Aduan</p>
                            <p class="font-medium text-gray-700 dark:text-gray-300 mt-0.5">{{ $report->created_at->format('d M Y, H:i') }} WIB</p>
                        </div>
                        @if ($report->resolved_at)
                        <div>
                            <p class="text-gray-400">Selesai / Resolusi</p>
                            <p class="font-medium text-gray-700 dark:text-gray-300 mt-0.5">{{ $report->resolved_at->format('d M Y, H:i') }} WIB</p>
                            @if ($report->resolvedBy)
                                <p class="text-[10px] text-gray-400">Oleh: {{ $report->resolvedBy->name }}</p>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Komparasi Bukti Foto Hasil Pengerjaan Mitra vs Bukti Laporan Customer --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-5 sm:p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2 pb-2 border-b border-gray-100 dark:border-gray-700">
                    <span>🔍 Komparasi Bukti Foto (Investigasi)</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Bukti Mitra --}}
                    <div class="p-3.5 bg-gray-50 dark:bg-gray-700/40 rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                                <span>🛵</span> Bukti Selesai Mitra
                            </span>
                            @if ($help?->proof_photo)
                                <span class="text-[10px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full font-semibold">Tersedia</span>
                            @else
                                <span class="text-[10px] bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">Tidak ada foto</span>
                            @endif
                        </div>

                        @if ($help?->proof_photo)
                            <a href="{{ asset('storage/' . $help->proof_photo) }}" target="_blank" rel="noopener">
                                <img src="{{ asset('storage/' . $help->proof_photo) }}" alt="Bukti Mitra" class="w-full h-44 object-cover rounded-lg border border-gray-200 dark:border-gray-600 hover:opacity-90 transition cursor-pointer">
                            </a>
                            @if($help->completion_notes)
                                <p class="text-[11px] text-gray-600 dark:text-gray-300 mt-2 italic bg-white dark:bg-gray-800 p-2 rounded-md border border-gray-100">
                                    "{{ $help->completion_notes }}"
                                </p>
                            @endif
                        @else
                            <div class="w-full h-44 flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-800 rounded-lg text-gray-400 text-xs">
                                <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Mitra tidak melampirkan foto</span>
                            </div>
                        @endif
                    </div>

                    {{-- Bukti Customer --}}
                    <div class="p-3.5 bg-gray-50 dark:bg-gray-700/40 rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                                <span>👤</span> Bukti Aduan Customer
                            </span>
                            @if ($report->evidence_photo)
                                <span class="text-[10px] bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full font-semibold">Tersedia</span>
                            @else
                                <span class="text-[10px] bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">Tidak ada foto</span>
                            @endif
                        </div>

                        @if ($report->evidence_photo)
                            <a href="{{ asset('storage/' . $report->evidence_photo) }}" target="_blank" rel="noopener">
                                <img src="{{ asset('storage/' . $report->evidence_photo) }}" alt="Bukti Customer" class="w-full h-44 object-cover rounded-lg border border-gray-200 dark:border-gray-600 hover:opacity-90 transition cursor-pointer">
                            </a>
                        @else
                            <div class="w-full h-44 flex flex-col items-center justify-center bg-gray-100 dark:bg-gray-800 rounded-lg text-gray-400 text-xs">
                                <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Customer tidak melampirkan foto tambahan</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Hubungi Pihak Terkait (Customer & Mitra) - Fitur Kontak & Konfirmasi --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-5 sm:p-6 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="flex items-center gap-1.5">
                        <span>📞</span> Hubungi Customer & Mitra (Konfirmasi Langsung)
                    </span>
                    <span class="text-xs text-gray-400 font-normal">WhatsApp • Telepon • Email</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Kontak Pelapor (Customer) --}}
                    <div class="p-4 bg-gradient-to-br from-blue-50/50 to-white dark:from-gray-700/50 dark:to-gray-800 rounded-xl border border-blue-100 dark:border-gray-600 space-y-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Pelapor (Customer)</span>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $rep?->name ?? 'Customer' }}</h4>
                                <p class="text-xs text-gray-500">{{ $rep?->email ?? '-' }}</p>
                            </div>
                            <span class="text-xs font-extrabold text-blue-600 bg-blue-100 dark:bg-blue-900/60 px-2 py-0.5 rounded-md">
                                Saldo: Rp {{ number_format($rep?->balance?->balance ?? 0, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="text-xs space-y-1 text-gray-600 dark:text-gray-300">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-400">No. HP:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $rep?->phone ?? 'Belum tercantum' }}</span>
                            </div>
                        </div>

                        {{-- Action Buttons Customer --}}
                        <div class="flex items-center gap-2 pt-2 border-t border-gray-100 dark:border-gray-600 flex-wrap">
                            @if ($rep?->phone)
                                @php $custWaUrl = $formatWa($rep->phone, $customerWaText); @endphp
                                <a href="{{ $custWaUrl }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    WhatsApp
                                </a>
                                <a href="tel:{{ $rep->phone }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg text-xs font-semibold transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    Telepon
                                </a>
                            @endif

                            @if ($rep?->email)
                                <a href="mailto:{{ $rep->email }}?subject=Konfirmasi Laporan Aduan %23{{ $report->id }} - SayaBantu&body={{ urlencode($customerWaText) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg text-xs font-semibold transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    Email
                                </a>
                            @endif

                            @if($rep)
                                <a href="{{ route($routePrefix . 'partners.greylist', ['search' => $rep->name]) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-200 border border-blue-200 dark:border-blue-800 rounded-lg text-xs font-bold transition">
                                    ⚠️ Pengawasan / SP Customer
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Kontak Terlapor (Mitra) --}}
                    <div class="p-4 bg-gradient-to-br from-amber-50/50 to-white dark:from-gray-700/50 dark:to-gray-800 rounded-xl border border-amber-100 dark:border-gray-600 space-y-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Terlapor (Mitra)</span>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $mitra?->name ?? 'Mitra' }}</h4>
                                <p class="text-xs text-gray-500">{{ $mitra?->email ?? '-' }}</p>
                            </div>
                            <span class="text-xs font-extrabold text-emerald-600 bg-emerald-100 dark:bg-emerald-900/60 px-2 py-0.5 rounded-md">
                                Saldo: Rp {{ number_format($mitra?->balance?->balance ?? 0, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="text-xs space-y-1 text-gray-600 dark:text-gray-300">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-400">No. HP:</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $mitra?->phone ?? 'Belum tercantum' }}</span>
                            </div>
                        </div>

                        {{-- Action Buttons Mitra --}}
                        <div class="flex items-center gap-2 pt-2 border-t border-gray-100 dark:border-gray-600 flex-wrap">
                            @if ($mitra?->phone)
                                @php $mitraWaUrl = $formatWa($mitra->phone, $mitraWaText); @endphp
                                <a href="{{ $mitraWaUrl }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-xs">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    WhatsApp
                                </a>
                                <a href="tel:{{ $mitra->phone }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg text-xs font-semibold transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    Telepon
                                </a>
                            @endif

                            @if ($mitra?->email)
                                <a href="mailto:{{ $mitra->email }}?subject=Klarifikasi Laporan Aduan %23{{ $report->id }} - SayaBantu&body={{ urlencode($mitraWaText) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg text-xs font-semibold transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    Email
                                </a>
                            @endif

                            @if($mitra)
                                <a href="{{ route($routePrefix . 'partners.greylist', ['search' => $mitra->name]) }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800 rounded-lg text-xs font-bold transition">
                                    ⚠️ Pengawasan / SP Mitra
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Akses Ruang Obrolan Khusus Sengketa & Klarifikasi Laporan --}}
            <div class="bg-gradient-to-r from-primary-500/10 via-primary-600/5 to-amber-500/10 dark:from-gray-800 dark:to-gray-800 rounded-2xl border border-primary-200 dark:border-gray-700 p-5 sm:p-6 shadow-xs flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-primary-600 text-white flex items-center justify-center shadow-sm flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Ruang Obrolan Investigasi & Klarifikasi Sengketa</h3>
                            <span class="text-xs bg-primary-100 dark:bg-primary-950 text-primary-700 dark:text-primary-300 font-bold px-2.5 py-0.5 rounded-full">
                                {{ $report->messages->count() }} Pesan
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kirim pesan klarifikasi resmi, minta bukti foto tambahan, dan diskusikan sengketa di ruang chat terpisah.</p>
                    </div>
                </div>

                <a href="{{ route($routePrefix . 'partners.reports.chat', $report) }}"
                    class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-2 cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                    <span>Buka Ruang Chat Sengketa</span>
                </a>
            </div>

            {{-- Integrasi Rekonsiliasi Transaksi Keuangan Bantuan --}}
            @if ($help)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-5 sm:p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span>💳 Rekonsiliasi Transaksi Bantuan #{{ $help->id }}</span>
                        </h3>
                        @if ($isWithin24H)
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                🛡️ Garansi 1x24 Jam Aktif (hingga {{ $help->completed_at?->addHours(24)->format('d M, H:i') }} WIB)
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                Garansi 24 Jam Berakhir
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-slate-50 dark:bg-gray-700/40 p-3.5 rounded-xl border border-gray-100 dark:border-gray-700">
                        <div>
                            <span class="text-gray-400 block">Nominal Tugas:</span>
                            <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($help->amount, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">Biaya Layanan Platform:</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($help->platform_fee_amount ?? $help->admin_fee, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">Total Bayar Customer:</span>
                            <span class="font-extrabold text-gray-900 dark:text-white">Rp {{ number_format($help->total_amount ?: $help->amount, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block">Saldo Masuk ke Mitra:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($help->mitra_earning ?: $help->amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Log Transaksi Terkait --}}
                    <div>
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Riwayat Ledger Transaksi Terkait:</h4>
                        @if ($transactions->isNotEmpty())
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs text-left text-gray-600 dark:text-gray-300">
                                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold">
                                        <tr>
                                            <th class="py-2 px-3 rounded-l-lg">Tipe</th>
                                            <th class="py-2 px-3">User</th>
                                            <th class="py-2 px-3">Nominal</th>
                                            <th class="py-2 px-3">Keterangan</th>
                                            <th class="py-2 px-3 rounded-r-lg">Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @foreach ($transactions as $tx)
                                            <tr>
                                                <td class="py-2 px-3 font-semibold">
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] {{ $tx->type === 'refund' ? 'bg-emerald-100 text-emerald-800' : ($tx->type === 'penalty' ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-blue-800') }}">
                                                        {{ strtoupper($tx->type) }}
                                                    </span>
                                                </td>
                                                <td class="py-2 px-3">{{ $tx->user->name ?? 'Kas Platform' }}</td>
                                                <td class="py-2 px-3 font-bold {{ in_array($tx->type, ['refund', 'earning', 'topup']) ? 'text-emerald-600' : 'text-rose-600' }}">
                                                    Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                                </td>
                                                <td class="py-2 px-3 max-w-xs truncate">{{ $tx->description }}</td>
                                                <td class="py-2 px-3 text-gray-400 whitespace-nowrap">{{ $tx->created_at->format('d/m/y H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-xs text-gray-400 italic">Belum ada rekaman transaksi terhubung.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar Actions & Refund Control --}}
        <div class="space-y-6">
            {{-- Panel Moderasi Pengembalian Dana (Refund Control) --}}
            @if ($help)
                <div class="bg-gradient-to-br from-amber-500/10 via-white to-amber-50/50 dark:from-gray-800 dark:to-gray-800 rounded-2xl border-2 border-amber-300 dark:border-amber-700 shadow-xs p-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-extrabold text-gray-900 dark:text-white flex items-center gap-1.5">
                            <span>🛡️ Moderasi Dana / Refund</span>
                        </h3>
                        @php
                            $refAmount = $report->refund_amount ?: ($help->total_amount > 0 ? $help->total_amount : $help->amount);
                        @endphp
                        <span class="text-xs font-extrabold text-amber-700 dark:text-amber-400">
                            Rp {{ number_format($refAmount, 0, ',', '.') }}
                        </span>
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                        Jika mitra terbukti berbohong / tidak menyelesaikan tugas, Anda dapat menyetujui refund. Sistem akan menarik saldo dari mitra dan mengembalikan 100% dana ke saldo customer.
                    </p>

                    @if ($report->refund_status === 'approved')
                        <div class="p-3 bg-emerald-100 dark:bg-emerald-950/60 border border-emerald-300 text-emerald-800 dark:text-emerald-200 rounded-xl text-xs">
                            <span class="font-bold block mb-0.5">✅ Refund Telah Selesai</span>
                            Dana Rp {{ number_format($report->refund_amount, 0, ',', '.') }} telah dikreditkan ke saldo Customer pada {{ $report->refund_processed_at?->format('d M Y, H:i') }} WIB.
                        </div>
                    @elseif ($report->refund_status === 'rejected')
                        <div class="p-3 bg-rose-100 dark:bg-rose-950/60 border border-rose-300 text-rose-800 dark:text-rose-200 rounded-xl text-xs">
                            <span class="font-bold block mb-0.5">❌ Klaim Refund Ditolak</span>
                            Hak mitra dipertahankan dan aduan dinyatakan ditolak.
                        </div>
                    @else
                        {{-- Tombol Tindakan Refund --}}
                        <div class="space-y-2 pt-1">
                            <form method="POST" action="{{ route($routePrefix . 'partners.reports.process-refund', $report) }}" onsubmit="return confirm('Apakah Anda yakin ingin MENYETUJUI REFUND 100% sebesar Rp {{ number_format($refAmount, 0, ',', '.') }} ke Customer dan MENARIK saldo dari Mitra?');">
                                @csrf
                                <button type="submit"
                                    class="w-full py-2.5 px-4 text-xs font-extrabold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Setujui Refund 100% ke Customer
                                </button>
                            </form>

                            <button type="button" onclick="document.getElementById('reject-refund-form').classList.toggle('hidden');"
                                class="w-full py-2.5 px-4 text-xs font-bold bg-gray-100 dark:bg-gray-700 hover:bg-rose-50 hover:text-rose-600 text-gray-700 dark:text-gray-200 rounded-xl border border-gray-200 dark:border-gray-600 transition flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Tolak Klaim Refund
                            </button>

                            <form id="reject-refund-form" method="POST" action="{{ route($routePrefix . 'partners.reports.reject-refund', $report) }}" class="hidden space-y-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                @csrf
                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300">Alasan Penolakan Klaim:</label>
                                <textarea name="admin_notes" rows="2" required placeholder="Tulis alasan bukti mitra valid..." class="w-full p-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                                <button type="submit" class="w-full py-2 px-3 text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition">
                                    Konfirmasi Tolak Refund
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Update Status Aduan Umum --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-5 space-y-3">
                <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">Status & Penutupan Aduan</h3>
                <form method="POST" action="{{ route($routePrefix . 'partners.reports.update', $report) }}" class="space-y-2">
                    @csrf
                    <select name="status"
                        class="w-full py-2 px-3 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ $report->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                    </select>
                    <button type="submit"
                        class="w-full py-2 px-4 text-xs font-bold bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                        Simpan Status
                    </button>
                </form>

                @if (!$report->isResolved())
                    <form method="POST" action="{{ route($routePrefix . 'partners.reports.resolve', $report) }}" class="pt-2 border-t border-gray-100 dark:border-gray-700">
                        @csrf
                        <button type="submit"
                            class="w-full py-2 px-4 text-xs font-bold bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-colors">
                            Tandai Kasus Selesai (Resolved)
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route($routePrefix . 'partners.reports.reopen', $report) }}" class="pt-2 border-t border-gray-100 dark:border-gray-700">
                        @csrf
                        <button type="submit"
                            class="w-full py-2 px-4 text-xs font-bold bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                            Buka Kembali Aduan
                        </button>
                    </form>
                @endif
            </div>

            {{-- Catatan Investigasi Admin & Kronologi --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs p-5 space-y-3">
                <h3 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">Catatan Investigasi & Kronologi</h3>
                @if ($report->admin_notes)
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-xl text-xs text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed border border-gray-100 dark:border-gray-700 max-h-60 overflow-y-auto">
                        {{ $report->admin_notes }}
                    </div>
                @endif
                <form method="POST" action="{{ route($routePrefix . 'partners.reports.add-note', $report) }}" class="space-y-2">
                    @csrf
                    <textarea name="admin_notes" rows="3" placeholder="Tulis catatan penanganan kasus dan kronologi..."
                        class="w-full p-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">{{ $report->admin_notes }}</textarea>
                    <button type="submit"
                        class="w-full py-2 px-4 text-xs font-bold bg-gray-800 dark:bg-gray-600 text-white rounded-xl hover:bg-gray-900 dark:hover:bg-gray-500 transition-colors">
                        {{ $report->admin_notes ? 'Perbarui Catatan' : 'Tambah Catatan' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
