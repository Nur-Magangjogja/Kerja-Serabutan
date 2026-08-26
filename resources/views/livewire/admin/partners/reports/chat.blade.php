<div class="space-y-4 max-w-5xl mx-auto" wire:poll.3000ms="markAsRead">
    @php
        $routePrefix = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'superadmin.' : 'admin.';
        $customer = $report->reporter ?? $report->user;
        $mitra = $report->reportedUser ?? $report->reportedHelp?->mitra;
    @endphp

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
                <p class="text-xs text-gray-500 dark:text-gray-400">Jalur obrolan 1-on-1 Admin dengan Pelapor & Admin dengan Terlapor</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route($routePrefix . 'partners.reports') }}" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 hover:underline">
                Daftar Laporan
            </a>
        </div>
    </div>

    {{-- Detail Subjek Laporan --}}
    <div class="bg-purple-50/70 dark:bg-purple-950/40 p-3.5 rounded-2xl border border-purple-200 dark:border-purple-800/60 shadow-xs flex items-center justify-between gap-3 flex-wrap">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700 dark:text-purple-300">🚩 Keluhan Awal Pelapor:</span>
                <span class="text-[10px] text-purple-600 dark:text-purple-400">{{ $report->created_at->format('d M Y, H:i') }}</span>
            </div>
            <p class="text-xs font-semibold text-purple-950 dark:text-purple-100 mt-0.5">"{{ $report->message }}"</p>
        </div>
        @if($report->reportedHelp)
            <div class="text-right">
                <span class="text-[10px] text-purple-700 dark:text-purple-300 block font-bold">Bantuan #{{ $report->reportedHelp->id }}</span>
                <span class="text-xs font-extrabold text-purple-900 dark:text-purple-100">Rp {{ number_format($report->reportedHelp->amount, 0, ',', '.') }}</span>
            </div>
        @endif
    </div>

    {{-- TAB SWITCHER: Ruang Chat Customer vs Ruang Chat Mitra --}}
    <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700 pb-1">
        <button type="button" wire:click="selectTab('customer')"
            class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-xs transition cursor-pointer {{ $activeTab === 'customer' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 font-medium' }}">
            <span class="text-sm">👤</span>
            <span>Ruang Chat Customer (Pelapor: {{ $customer?->name ?? 'Customer' }})</span>
            @if($unreadCustomer > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500 text-white animate-pulse">
                    {{ $unreadCustomer }} Baru
                </span>
            @endif
        </button>

        <button type="button" wire:click="selectTab('mitra')"
            class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-xs transition cursor-pointer {{ $activeTab === 'mitra' ? 'bg-emerald-600 text-white shadow-xs font-bold' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-emerald-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 font-medium' }}">
            <span class="text-sm">🛵</span>
            <span>Ruang Chat Mitra (Terlapor: {{ $mitra?->name ?? 'Mitra' }})</span>
            @if($unreadMitra > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500 text-white animate-pulse">
                    {{ $unreadMitra }} Baru
                </span>
            @endif
        </button>
    </div>

    {{-- Chat Box Container --}}
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col h-[520px]">
        {{-- Messages Stream --}}
        <div class="flex-1 p-4 overflow-y-auto space-y-3" id="adminReportChatBox">
            @if($messages->isEmpty())
                <div class="h-full flex flex-col items-center justify-center text-center p-6">
                    <span class="text-3xl mb-2">💬</span>
                    <p class="text-xs font-bold text-gray-700 dark:text-gray-300">Belum ada pesan di jalur ini.</p>
                    <p class="text-[11px] text-gray-400 mt-1 max-w-xs">
                        Kirim pesan pertama untuk mengklarifikasi masalah kepada {{ $activeTab === 'customer' ? 'Customer' : 'Mitra' }}.
                    </p>
                </div>
            @else
                @foreach($messages as $msg)
                    @php $isMe = ($msg->sender_id === auth()->id()); @endphp
                    <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                        <div class="flex items-center gap-1.5 mb-0.5 text-[10px] text-gray-400">
                            <span class="font-bold {{ $isMe ? 'text-primary-600 dark:text-primary-400' : 'text-gray-600 dark:text-gray-300' }}">
                                {{ $isMe ? 'Anda (Admin)' : ($msg->sender?->name ?? 'Pengguna') }}
                            </span>
                            <span>•</span>
                            <span>{{ $msg->created_at->format('H:i') }}</span>
                        </div>

                        <div class="max-w-[75%] rounded-2xl p-3 text-xs shadow-xs {{ $isMe ? 'bg-primary-600 text-white rounded-br-none' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-bl-none' }}">
                            @if($msg->photo)
                                <a href="{{ asset('storage/' . $msg->photo) }}" target="_blank" class="block mb-2 rounded-xl overflow-hidden max-h-48">
                                    <img src="{{ asset('storage/' . $msg->photo) }}" alt="Foto Pesan" class="w-full h-full object-cover">
                                </a>
                            @endif

                            @if($msg->message)
                                <p class="whitespace-pre-line leading-relaxed">{{ $msg->message }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Chat Input Form --}}
        <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
            @if($photo)
                <div class="mb-2 p-2 bg-white dark:bg-gray-800 rounded-xl flex items-center justify-between border border-gray-200 dark:border-gray-600">
                    <span class="text-xs text-gray-600 dark:text-gray-300 truncate">Foto Terlampir: {{ $photo->getClientOriginalName() }}</span>
                    <button type="button" wire:click="$set('photo', null)" class="text-rose-500 font-bold text-xs">Hapus</button>
                </div>
            @endif

            <form wire:submit="sendMessage" class="flex items-center gap-2">
                <label class="p-2.5 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-300 rounded-xl border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="Lampirkan Gambar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <input type="file" wire:model="photo" class="hidden" accept="image/*">
                </label>

                <input type="text" wire:model="message" placeholder="Tulis pesan ke {{ $activeTab === 'customer' ? 'Customer' : 'Mitra' }}..."
                    class="flex-1 px-4 py-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">

                <button type="submit" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <span>Kirim</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>
