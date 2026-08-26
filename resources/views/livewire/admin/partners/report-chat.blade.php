@extends(in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'layouts.superadmin' : 'layouts.admin')

@section('content')
@php
    $routePrefix = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'superadmin.' : 'admin.';
    
    // Pisahkan percakapan: Customer <-> Admin dan Mitra <-> Admin
    $customerMessages = $report->messages->filter(function($m) use ($customer) {
        return ($m->sender_id == $customer?->id) || ($m->isFromAdmin() && $m->recipient_type === 'customer');
    });

    $mitraMessages = $report->messages->filter(function($m) use ($mitra) {
        return ($m->sender_id == $mitra?->id) || ($m->isFromAdmin() && $m->recipient_type === 'mitra');
    });
@endphp

<div class="space-y-4 max-w-5xl mx-auto" x-data="{ activeTab: 'customer' }">
    {{-- Header Navigation --}}
    <div class="flex items-center justify-between gap-3 flex-wrap bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
        <div class="flex items-center gap-3">
            <a href="{{ route($routePrefix . 'partners.reports.show', $report) }}"
                class="p-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-semibold transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Detail
            </a>
            <div>
                <h1 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span>💬 Ruang Obrolan Investigasi Laporan #{{ $report->id }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-bold uppercase {{ $report->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ ucfirst($report->status) }}
                    </span>
                </h1>
                <p class="text-xs text-gray-500 dark:text-gray-400">Jalur obrolan terpisah: Chat 1-on-1 Admin dengan Pelapor (Customer) & Admin dengan Terlapor (Mitra)</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route($routePrefix . 'partners.report') }}" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 hover:underline">
                Daftar Laporan
            </a>
        </div>
    </div>

    {{-- Detail Subjek Laporan --}}
    <div class="bg-purple-50/70 dark:bg-purple-950/40 p-3.5 rounded-2xl border border-purple-200 dark:border-purple-800/60 shadow-xs flex items-center justify-between gap-3 flex-wrap">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700 dark:text-purple-300">🚩 Keluhan Awal Pelapor:</span>
                <span class="text-[10px] text-purple-600 dark:text-purple-400">{{ $report->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
            </div>
            <p class="text-xs font-semibold text-purple-950 dark:text-purple-100 mt-0.5">"{{ $report->message }}"</p>
        </div>
        @if($help)
            <div class="text-right">
                <span class="text-[10px] text-purple-700 dark:text-purple-300 block font-bold">Bantuan #{{ $help->id }}</span>
                <span class="text-xs font-extrabold text-purple-900 dark:text-purple-100">Rp {{ number_format($help->amount, 0, ',', '.') }}</span>
            </div>
        @endif
    </div>

    {{-- TAB SWITCHER: Ruang Chat Customer vs Ruang Chat Mitra --}}
    <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700 pb-1">
        {{-- Tab Button 1: Customer --}}
        <button type="button" @click="activeTab = 'customer'"
            :class="activeTab === 'customer' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 font-medium'"
            class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-xs transition cursor-pointer">
            <span class="text-sm">👤</span>
            <span>Ruang Chat Customer (Pelapor: {{ $customer?->name ?? 'Customer' }})</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                :class="activeTab === 'customer' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'">
                {{ $customerMessages->count() }}
            </span>
        </button>

        {{-- Tab Button 2: Mitra --}}
        <button type="button" @click="activeTab = 'mitra'"
            :class="activeTab === 'mitra' ? 'bg-emerald-600 text-white shadow-xs font-bold' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-emerald-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 font-medium'"
            class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-xs transition cursor-pointer">
            <span class="text-sm">🛵</span>
            <span>Ruang Chat Mitra (Terlapor: {{ $mitra?->name ?? 'Mitra' }})</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                :class="activeTab === 'mitra' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200'">
                {{ $mitraMessages->count() }}
            </span>
        </button>
    </div>

    {{-- ============================================================== --}}
    {{-- RUANG CHAT 1: PRIVATE CHAT ADMIN <-> CUSTOMER --}}
    {{-- ============================================================== --}}
    <div x-show="activeTab === 'customer'" x-transition class="space-y-3">
        {{-- Profile Header Card Customer --}}
        <div class="bg-blue-50/70 dark:bg-blue-950/40 p-3.5 rounded-2xl border border-blue-200 dark:border-blue-800/60 shadow-xs flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold text-base shadow-xs">
                    👤
                </div>
                <div>
                    <h3 class="text-xs font-bold text-blue-950 dark:text-blue-100">{{ $customer?->name ?? 'Customer' }} (Pelapor)</h3>
                    <p class="text-[11px] text-blue-900/80 dark:text-blue-300">{{ $customer?->phone ?? $customer?->email ?? '-' }}</p>
                </div>
            </div>
            @if($customer?->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->phone) }}" target="_blank" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1">
                    <span>WhatsApp Customer</span>
                </a>
            @endif
        </div>

        {{-- Chat Box Room Customer --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col min-h-[460px]">
            <div id="adminCustomerChatScroll" class="flex-1 p-4 md:p-6 overflow-y-auto space-y-4 bg-slate-50/60 dark:bg-gray-900/80 max-h-[50vh]">
                @forelse($customerMessages as $msg)
                    @php $isAdmin = $msg->isFromAdmin(); @endphp
                    <div class="flex {{ $isAdmin ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] md:max-w-[75%] rounded-2xl p-3.5 shadow-xs {{ $isAdmin ? 'bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-700 text-gray-900 dark:text-gray-100 rounded-br-xs' : 'bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-800 rounded-bl-xs' }}">
                            <div class="flex items-center justify-between gap-2 mb-1 pb-1 border-b {{ $isAdmin ? 'border-amber-200/80 text-amber-900 dark:text-amber-300' : 'border-blue-100 text-blue-900 dark:text-blue-300' }} text-[10px] font-bold">
                                <span>{{ $isAdmin ? '🛡️ Tim Admin (Anda)' : '👤 ' . ($customer?->name ?? 'Customer') }}</span>
                                <span class="font-normal opacity-80">{{ $msg->created_at->format('d M, H:i') }}</span>
                            </div>

                            @if($msg->photo)
                                <div class="mb-2 rounded-xl overflow-hidden border border-black/10 max-w-sm">
                                    <a href="{{ asset('storage/' . $msg->photo) }}" target="_blank" rel="noopener">
                                        <img src="{{ asset('storage/' . $msg->photo) }}" alt="Foto Bukti" class="w-full max-h-56 object-cover hover:opacity-95 transition cursor-pointer">
                                    </a>
                                </div>
                            @endif

                            <p class="text-xs whitespace-pre-line leading-relaxed">{{ $msg->message }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-400 text-xs">
                        Belum ada percakapan dengan Customer. Kirim pesan bantuan pertama di bawah.
                    </div>
                @endforelse
            </div>

            {{-- Form Kirim ke Customer --}}
            <form method="POST" action="{{ route($routePrefix . 'partners.reports.send-message', $report) }}" enctype="multipart/form-data" class="p-3.5 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 space-y-2.5">
                @csrf
                <input type="hidden" name="target" value="customer">

                <div class="flex items-center justify-between gap-2 flex-wrap text-[10px]">
                    <span class="font-bold text-blue-700 dark:text-blue-300">Mengirim pesan 1-on-1 ke Customer:</span>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="document.getElementById('custAdminMsg').value='Halo Customer, kami sedang memeriksa kronologi dengan pihak mitra. Mohon menunggu info dari tim moderasi.';" class="px-2 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded transition">
                            + Template Kronologi
                        </button>
                        <button type="button" onclick="document.getElementById('custAdminMsg').value='Halo Customer, permohonan pengembalian dana (refund) Anda telah diproses oleh admin.';" class="px-2 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded transition">
                            + Template Refund
                        </button>
                    </div>
                </div>

                <div class="flex items-end gap-2">
                    <label class="p-2.5 bg-gray-100 hover:bg-blue-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:text-blue-600 rounded-xl transition cursor-pointer flex-shrink-0" title="Lampirkan Foto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <input type="file" name="photo" id="custAdminPhoto" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="document.getElementById('custPhotoLabel').textContent = this.files[0] ? this.files[0].name : ''">
                    </label>

                    <textarea id="custAdminMsg" name="message" rows="2" placeholder="Ketik pesan resmi untuk Customer..."
                        class="flex-1 p-3 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/70 text-gray-800 dark:text-white focus:ring-2 focus:ring-blue-500 resize-none outline-none"></textarea>

                    <button type="submit" class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer flex-shrink-0">
                        <span>Kirim ke Customer</span>
                    </button>
                </div>
                <div id="custPhotoLabel" class="text-[10px] text-blue-600 font-bold"></div>
            </form>
        </div>
    </div>

    {{-- ============================================================== --}}
    {{-- RUANG CHAT 2: PRIVATE CHAT ADMIN <-> MITRA --}}
    {{-- ============================================================== --}}
    <div x-show="activeTab === 'mitra'" x-transition class="space-y-3">
        {{-- Profile Header Card Mitra --}}
        <div class="bg-emerald-50/70 dark:bg-emerald-950/40 p-3.5 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 shadow-xs flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold text-base shadow-xs">
                    🛵
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-xs font-bold text-emerald-950 dark:text-emerald-100">{{ $mitra?->name ?? 'Mitra' }} (Terlapor)</h3>
                        <span class="text-[10px] font-extrabold text-emerald-700 bg-emerald-100 dark:bg-emerald-900/80 px-2 py-0.5 rounded">
                            Saldo: Rp {{ number_format($mitra?->balance?->balance ?? 0, 0, ',', '.') }}
                        </span>
                    </div>
                    <p class="text-[11px] text-emerald-900/80 dark:text-emerald-300">{{ $mitra?->phone ?? $mitra?->email ?? '-' }}</p>
                </div>
            </div>
            @if($mitra?->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $mitra->phone) }}" target="_blank" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1">
                    <span>WhatsApp Mitra</span>
                </a>
            @endif
        </div>

        {{-- Chat Box Room Mitra --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col min-h-[460px]">
            <div id="adminMitraChatScroll" class="flex-1 p-4 md:p-6 overflow-y-auto space-y-4 bg-slate-50/60 dark:bg-gray-900/80 max-h-[50vh]">
                @forelse($mitraMessages as $msg)
                    @php $isAdmin = $msg->isFromAdmin(); @endphp
                    <div class="flex {{ $isAdmin ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] md:max-w-[75%] rounded-2xl p-3.5 shadow-xs {{ $isAdmin ? 'bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-700 text-gray-900 dark:text-gray-100 rounded-br-xs' : 'bg-white dark:bg-gray-800 border border-emerald-200 dark:border-emerald-800 rounded-bl-xs' }}">
                            <div class="flex items-center justify-between gap-2 mb-1 pb-1 border-b {{ $isAdmin ? 'border-amber-200/80 text-amber-900 dark:text-amber-300' : 'border-emerald-100 text-emerald-900 dark:text-emerald-300' }} text-[10px] font-bold">
                                <span>{{ $isAdmin ? '🛡️ Tim Admin (Anda)' : '🛵 ' . ($mitra?->name ?? 'Mitra') }}</span>
                                <span class="font-normal opacity-80">{{ $msg->created_at->format('d M, H:i') }}</span>
                            </div>

                            @if($msg->photo)
                                <div class="mb-2 rounded-xl overflow-hidden border border-black/10 max-w-sm">
                                    <a href="{{ asset('storage/' . $msg->photo) }}" target="_blank" rel="noopener">
                                        <img src="{{ asset('storage/' . $msg->photo) }}" alt="Foto Bukti" class="w-full max-h-56 object-cover hover:opacity-95 transition cursor-pointer">
                                    </a>
                                </div>
                            @endif

                            <p class="text-xs whitespace-pre-line leading-relaxed">{{ $msg->message }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-400 text-xs">
                        Belum ada percakapan dengan Mitra. Kirim instruksi klarifikasi pertama di bawah.
                    </div>
                @endforelse
            </div>

            {{-- Form Kirim ke Mitra --}}
            <form method="POST" action="{{ route($routePrefix . 'partners.reports.send-message', $report) }}" enctype="multipart/form-data" class="p-3.5 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 space-y-2.5">
                @csrf
                <input type="hidden" name="target" value="mitra">

                <div class="flex items-center justify-between gap-2 flex-wrap text-[10px]">
                    <span class="font-bold text-emerald-700 dark:text-emerald-300">Mengirim pesan 1-on-1 ke Mitra:</span>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="document.getElementById('mitraAdminMsg').value='Halo Mitra, mohon kirimkan bukti foto tambahan pengerjaan tugas bantuan ini secara jelas dalam 1x24 jam.';" class="px-2 py-0.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded transition">
                            + Minta Foto Pengerjaan
                        </button>
                        <button type="button" onclick="document.getElementById('mitraAdminMsg').value='Halo Mitra, mohon berikan klarifikasi terkait kendala pelaksanaan tugas pada bantuan ini.';" class="px-2 py-0.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded transition">
                            + Minta Klarifikasi
                        </button>
                    </div>
                </div>

                <div class="flex items-end gap-2">
                    <label class="p-2.5 bg-gray-100 hover:bg-emerald-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:text-emerald-600 rounded-xl transition cursor-pointer flex-shrink-0" title="Lampirkan Foto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <input type="file" name="photo" id="mitraAdminPhoto" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="document.getElementById('mitraPhotoLabel').textContent = this.files[0] ? this.files[0].name : ''">
                    </label>

                    <textarea id="mitraAdminMsg" name="message" rows="2" placeholder="Ketik pesan klarifikasi untuk Mitra..."
                        class="flex-1 p-3 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/70 text-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500 resize-none outline-none"></textarea>

                    <button type="submit" class="px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer flex-shrink-0">
                        <span>Kirim ke Mitra</span>
                    </button>
                </div>
                <div id="mitraPhotoLabel" class="text-[10px] text-emerald-600 font-bold"></div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const custScroll = document.getElementById('adminCustomerChatScroll');
        if (custScroll) custScroll.scrollTop = custScroll.scrollHeight;

        const mitraScroll = document.getElementById('adminMitraChatScroll');
        if (mitraScroll) mitraScroll.scrollTop = mitraScroll.scrollHeight;
    });
</script>
@endsection
