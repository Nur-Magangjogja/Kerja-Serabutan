<div class="space-y-4 max-w-5xl mx-auto w-full" wire:poll.5s.visible>
    @php
        $routePrefix = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'superadmin.' : 'admin.';
        $gross = (float) ($help?->total_amount > 0 ? $help?->total_amount : ($help?->amount ?: 0));
        $isPartner = ($cancelRequest->requester_type === 'partner');
        $isKonsep2 = ($isPartner && (($cancelRequest->cancellation_stage ?? '') === 'in_progress' || ($cancelRequest->previous_status ?? '') === 'in_progress' || ($help?->status ?? '') === 'partner_cancel_requested'));

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

        $custWaUrl = $formatWa($customer?->phone, "Halo Kak " . ($customer?->name ?? 'Customer') . ", Tim Admin SayaBantu ingin mengklarifikasi pembatalan tugas '" . ($help?->title ?? 'Bantuan') . "'.");
        $mitraWaUrl = $formatWa($partner?->phone, "Halo Rekan " . ($partner?->name ?? 'Mitra') . ", Tim Admin SayaBantu ingin mengklarifikasi pengajuan pembatalan tugas '" . ($help?->title ?? 'Bantuan') . "'.");
    @endphp

    {{-- Header Navigation --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-gray-850 p-4 rounded-2xl border border-gray-200/80 dark:border-gray-750 shadow-xs">
        <div class="flex items-start sm:items-center gap-3 min-w-0">
            <a href="{{ route($routePrefix . 'cancellations.index') }}" wire:navigate
                class="p-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-750 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-semibold transition flex items-center gap-1 shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span class="hidden sm:inline">Kembali ke Tinjauan</span>
                <span class="sm:hidden">Kembali</span>
            </a>
            <div class="min-w-0">
                <h1 class="text-sm sm:text-base font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2 flex-wrap">
                    <span>{{ $cancelRequest->job_icon }} {{ $help?->title ?? 'Tugas Bantuan' }}</span>
                    <span class="text-[10px] sm:text-xs px-2.5 py-0.5 rounded-full font-bold uppercase whitespace-nowrap {{ $cancelRequest->status === 'approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : ($cancelRequest->status === 'rejected' ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300') }}">
                        {{ ucfirst($cancelRequest->status) }}
                    </span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold {{ $cancelRequest->job_category_badge_class }}">
                        {{ $cancelRequest->job_icon }} {{ $cancelRequest->job_label }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 dark:bg-sky-950 text-sky-800 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                        {{ $cancelRequest->cancellation_type_label }}
                    </span>
                </h1>
                <p class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400">Jalur obrolan resmi Admin dengan Customer & Mitra khusus tugas ini</p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
            <a href="{{ route($routePrefix . 'cancellations.index') }}" wire:navigate class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 hover:underline">
                Daftar Pembatalan
            </a>
        </div>
    </div>

    {{-- Detail Subjek Permohonan Pembatalan --}}
    <div class="bg-gradient-to-r from-amber-50/90 to-orange-50/70 dark:from-amber-950/40 dark:to-orange-950/30 p-4 rounded-2xl border border-amber-200 dark:border-amber-800/60 shadow-xs flex items-start sm:items-center justify-between gap-3 flex-wrap">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">
                    ⚠️ Alasan Pembatalan (oleh {{ $isPartner ? 'Mitra: ' . ($partner?->name ?? 'Mitra') : 'Customer: ' . ($customer?->name ?? 'Customer') }}):
                </span>
                <span class="text-[10px] text-amber-700 dark:text-amber-400">
                    {{ $cancelRequest->created_at ? $cancelRequest->created_at->translatedFormat('d M Y, H:i') : '-' }} WIB
                </span>
            </div>
            <p class="text-xs font-bold text-amber-950 dark:text-amber-100 mt-1 break-words [overflow-wrap:anywhere]">
                "{{ $cancelRequest->reason }}"
            </p>
            @if($cancelRequest->notes)
                <p class="text-[11px] text-amber-800 dark:text-amber-300 mt-0.5 italic">
                    Catatan tambahan: "{{ $cancelRequest->notes }}"
                </p>
            @endif

            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-[11px] text-amber-900/80 dark:text-amber-200/90 font-medium">
                @if($help && $help->isPickup() && ($help->pickup_address || $help->delivery_address))
                    <div>
                        <span class="opacity-75">Rute:</span>
                        <span class="font-bold">{{ Str::limit($help->pickup_address ?: 'Titik Jemput', 25) }} ➔ {{ Str::limit($help->delivery_address ?: 'Titik Tujuan', 25) }}</span>
                    </div>
                @endif
                @if($cancelRequest->work_completed_percentage > 0)
                    <div>
                        <span class="opacity-75">Porsi Kerja Selesai:</span>
                        <span class="font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($cancelRequest->work_completed_percentage, 0) }}%</span>
                    </div>
                @endif
                @if($cancelRequest->d_compensated_km > 0 || $cancelRequest->d_leg1_km > 0)
                    <div>
                        <span class="opacity-75">Jarak Kompensasi:</span>
                        <span class="font-bold">{{ $cancelRequest->d_compensated_km ?: $cancelRequest->d_leg1_km }} km</span>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="flex items-center gap-3 shrink-0">
            @if($gross > 0)
                <div class="text-left sm:text-right">
                    <span class="text-[10px] text-amber-800 dark:text-amber-300 block font-bold">Dana Escrow Terkunci</span>
                    <span class="text-xs sm:text-sm font-extrabold text-amber-950 dark:text-amber-100">Rp {{ number_format($gross, 0, ',', '.') }}</span>
                </div>
            @endif
            @if($custWaUrl || $mitraWaUrl)
                <div class="flex items-center gap-1.5 pl-2 border-l border-amber-200 dark:border-amber-800">
                    @if($custWaUrl)
                        <a href="{{ $custWaUrl }}" target="_blank" title="Chat WA Customer" class="p-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                            <span class="hidden md:inline">WA Customer</span>
                        </a>
                    @endif
                    @if($mitraWaUrl)
                        <a href="{{ $mitraWaUrl }}" target="_blank" title="Chat WA Mitra" class="p-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                            <span class="hidden md:inline">WA Mitra</span>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- TAB SWITCHER: Ruang Chat Customer vs Ruang Chat Mitra vs Chat Bersama vs Log Pesanan --}}
    <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700 pb-2 overflow-x-auto scrollbar-none">
        <button type="button" wire:click="selectTab('customer')"
            class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs transition cursor-pointer shrink-0 whitespace-nowrap {{ $activeTab === 'customer' ? 'bg-blue-600 text-white shadow-xs font-bold' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 font-medium' }}">
            <span class="text-sm">👤</span>
            <span>Chat Customer: {{ $customer?->name ?? 'Customer' }}</span>
            @if($unreadCustomer > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500 text-white animate-pulse">
                    {{ $unreadCustomer }} Baru
                </span>
            @endif
        </button>

        <button type="button" wire:click="selectTab('mitra')"
            class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs transition cursor-pointer shrink-0 whitespace-nowrap {{ $activeTab === 'mitra' ? 'bg-amber-600 text-white shadow-xs font-bold' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-amber-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 font-medium' }}">
            <span class="text-sm">🛵</span>
            <span>Chat Mitra: {{ $partner?->name ?? 'Mitra' }}</span>
            @if($unreadMitra > 0)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500 text-white animate-pulse">
                    {{ $unreadMitra }} Baru
                </span>
            @endif
        </button>

        <button type="button" wire:click="selectTab('all')"
            class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs transition cursor-pointer shrink-0 whitespace-nowrap {{ $activeTab === 'all' ? 'bg-emerald-600 text-white shadow-xs font-bold' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-emerald-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 font-medium' }}">
            <span class="text-sm">👥</span>
            <span>Chat Bersama (Kedua Pihak)</span>
        </button>

        <button type="button" wire:click="selectTab('task_log')"
            class="flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs transition cursor-pointer shrink-0 whitespace-nowrap {{ $activeTab === 'task_log' ? 'bg-purple-600 text-white shadow-xs font-bold' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-purple-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 font-medium' }}">
            <span class="text-sm">📜</span>
            <span>Log Chat Pesanan ({{ $historicalTaskChats->count() }})</span>
        </button>
    </div>

    {{-- Chat Box Container --}}
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col h-[520px]">
        {{-- Messages Stream --}}
        <div class="flex-1 p-3.5 sm:p-4 overflow-y-auto space-y-3" id="adminCancelChatBox">
            @if($activeTab === 'task_log')
                {{-- Riwayat Percakapan Langsung Pesanan Antara Customer & Mitra (Read Only) --}}
                @if($historicalTaskChats->isEmpty())
                    <div class="h-full flex flex-col items-center justify-center text-center p-8 text-gray-400">
                        <div class="w-14 h-14 rounded-2xl bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-800 flex items-center justify-center text-2xl mb-3 shadow-xs text-purple-600 dark:text-purple-400">
                            📜
                        </div>
                        <p class="text-xs font-bold text-gray-700 dark:text-gray-200">Tidak ada rekaman chat percakapan pesanan awal.</p>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1 max-w-sm">
                            Customer dan Mitra tidak melakukan percakapan melalui fitur chat dalam aplikasi pada pesanan ini sebelum pembatalan diajukan.
                        </p>
                    </div>
                @else
                    <div class="p-2.5 bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 rounded-xl text-[11px] text-purple-900 dark:text-purple-200 mb-2 flex items-center gap-2">
                        <span class="text-base">📜</span>
                        <span><strong>Arsip Audit Log:</strong> Rekaman percakapan langsung antara Customer dan Mitra saat pesanan berlangsung sebelum diajukan pembatalan (Hanya Baca).</span>
                    </div>
                    @foreach($historicalTaskChats as $hMsg)
                        @php
                            $isCust = ($hMsg->sender_type === 'customer' || ($customerId && $hMsg->sender_id === $customerId) || ($hMsg->customer_id && $hMsg->customer_id === $hMsg->sender_id));
                            $isMtr = ($hMsg->sender_type === 'mitra' || ($partnerId && $hMsg->sender_id === $partnerId) || ($hMsg->mitra_id && $hMsg->mitra_id === $hMsg->sender_id));
                        @endphp
                        <div class="flex flex-col {{ $isCust ? 'items-end' : 'items-start' }}">
                            <div class="flex items-center gap-1.5 mb-0.5 text-[10px] text-gray-400">
                                <span class="font-bold {{ $isCust ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400' }}">
                                    {{ $isCust ? '👤 ' . ($customer?->name ?? 'Customer') : '🛵 ' . ($partner?->name ?? 'Mitra') }}
                                </span>
                                <span>•</span>
                                <span>{{ $hMsg->created_at?->format('d M, H:i') }} WIB</span>
                            </div>
                            <div class="max-w-[85%] rounded-2xl p-3 text-xs shadow-xs {{ $isCust ? 'bg-blue-600 text-white rounded-br-none' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-bl-none' }}">
                                @if($hMsg->photo)
                                    <a href="{{ asset('storage/' . $hMsg->photo) }}" target="_blank" rel="noopener" class="block mb-2 rounded-xl overflow-hidden border border-black/10 dark:border-white/10 bg-black/5 dark:bg-black/20 group hover:opacity-95 transition">
                                        <img src="{{ asset('storage/' . $hMsg->photo) }}" alt="Foto" class="w-auto h-auto max-w-full max-h-[220px] sm:max-h-[260px] mx-auto object-contain rounded-xl transition duration-200 group-hover:scale-[1.01]" loading="lazy">
                                    </a>
                                @endif
                                <p class="whitespace-pre-line leading-relaxed break-words [overflow-wrap:anywhere]">{{ $hMsg->message }}</p>
                            </div>
                        </div>
                    @endforeach
                @endif
            @else
                {{-- Jalur Investigasi Klarifikasi Pembatalan (Customer, Mitra, atau Chat Bersama) --}}
                @if($activeTab === 'all')
                    <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-[11px] text-emerald-900 dark:text-emerald-200 mb-2 flex items-center gap-2">
                        <span class="text-base">📢</span>
                        <span><strong>Jalur Chat Bersama:</strong> Pesan yang dikirimkan di sini dapat dibaca oleh Customer dan Mitra secara serentak.</span>
                    </div>
                @endif

                @if($messages->isEmpty())
                    <div class="h-full flex flex-col items-center justify-center text-center p-6">
                        <span class="text-3xl mb-2">🛡️</span>
                        <p class="text-xs font-bold text-gray-700 dark:text-gray-300">Belum ada obrolan investigasi di jalur ini.</p>
                        <p class="text-[11px] text-gray-400 mt-1 max-w-xs">
                            Kirim pesan resmi Admin di bawah untuk meminta klarifikasi dari {{ $activeTab === 'customer' ? 'Customer' : ($activeTab === 'mitra' ? 'Mitra' : 'Kedua Pihak') }}.
                        </p>
                    </div>
                @else
                    @foreach($messages as $msg)
                        @php
                            $isFromAdmin = $msg->isFromAdmin();
                            $isCust = $msg->isFromCustomer();
                            $isMtr = $msg->isFromMitra();
                        @endphp

                        <div class="flex flex-col {{ $isFromAdmin ? 'items-end' : 'items-start' }}">
                            <div class="flex items-center gap-1.5 mb-0.5 text-[10px] text-gray-400">
                                <span class="font-bold {{ $isFromAdmin ? 'text-primary-600 dark:text-primary-400' : ($isCust ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400') }}">
                                    @if($isFromAdmin)
                                        🛡️ Anda (Admin: {{ $msg->sender?->name ?? 'Admin' }})
                                        @if($msg->recipient_type === 'all')
                                            <span class="text-[9px] font-normal text-emerald-600 dark:text-emerald-400">[Ke Kedua Pihak]</span>
                                        @endif
                                    @elseif($isCust)
                                        👤 Customer: {{ $customer?->name ?? 'Customer' }}
                                    @else
                                        🛵 Mitra: {{ $partner?->name ?? 'Mitra' }}
                                    @endif
                                </span>
                            </div>

                            <div class="max-w-[88%] sm:max-w-[75%] rounded-2xl p-3 text-xs shadow-xs {{ $isFromAdmin ? 'bg-primary-600 text-white rounded-br-none' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-bl-none' }}">
                                @if($msg->photo)
                                    <a href="{{ asset('storage/' . $msg->photo) }}" target="_blank" rel="noopener" class="block mb-2 rounded-xl overflow-hidden border border-black/10 dark:border-white/10 bg-black/5 dark:bg-black/20 group hover:opacity-95 transition">
                                        <img src="{{ asset('storage/' . $msg->photo) }}" alt="Foto Pesan" class="w-auto h-auto max-w-full max-h-[220px] sm:max-h-[260px] mx-auto object-contain rounded-xl transition duration-200 group-hover:scale-[1.01]" loading="lazy">
                                    </a>
                                @endif

                                @if($msg->message)
                                    <p class="whitespace-pre-line leading-relaxed break-words [overflow-wrap:anywhere]">{{ $msg->message }}</p>
                                @endif

                                {{-- Timestamp & Status Read di Bawah Bubble --}}
                                <div class="text-[10px] mt-1.5 flex items-center justify-end gap-1 {{ $isFromAdmin ? 'text-white/80' : 'text-gray-400 dark:text-gray-400' }} select-none">
                                    <span>{{ $msg->created_at->format('H:i') }} WIB</span>
                                    @if($isFromAdmin)
                                        @if($msg->is_read)
                                            <span class="text-sky-200 font-bold" title="Sudah dibaca user">✓✓</span>
                                        @else
                                            <span class="text-white/60 font-medium" title="Terkirim">✓</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endif
        </div>

        {{-- Chat Input Form / Read Only Notice --}}
        @if($activeTab === 'task_log')
            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700 text-center text-xs text-gray-500 dark:text-gray-400 font-medium flex items-center justify-center gap-1.5">
                <span>📜</span>
                <span>Arsip Rekaman Chat Pesanan Awal (Hanya Baca / Audit Log).</span>
            </div>
        @else
            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-100 dark:border-gray-700">
                @if($photo)
                    <div class="mb-2 p-2 bg-white dark:bg-gray-800 rounded-xl flex items-center justify-between border border-gray-200 dark:border-gray-600">
                        <span class="text-xs text-gray-600 dark:text-gray-300 truncate">Foto Terlampir: {{ $photo->getClientOriginalName() }}</span>
                        <button type="button" wire:click="$set('photo', null)" class="text-rose-500 font-bold text-xs cursor-pointer">Hapus</button>
                    </div>
                @endif

                <form wire:submit="sendMessage" class="flex items-center gap-2">
                    <label class="p-2.5 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-300 rounded-xl border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition shrink-0" title="Lampirkan Gambar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <input type="file" wire:model="photo" class="hidden" accept="image/*">
                    </label>

                    <input type="text" wire:model="message" placeholder="Tulis pesan klarifikasi resmi ke {{ $activeTab === 'customer' ? 'Customer' : ($activeTab === 'mitra' ? 'Mitra' : 'Kedua Pihak (Customer & Mitra)') }}..."
                        class="flex-1 min-w-0 px-3.5 sm:px-4 py-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">

                    <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="sendMessage"
                        class="px-3.5 sm:px-4 py-2.5 {{ $activeTab === 'all' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-primary-600 hover:bg-primary-700' }} text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer shrink-0 disabled:opacity-75 disabled:cursor-not-allowed">
                        <span>{{ $activeTab === 'all' ? 'Kirim ke Kedua Pihak' : 'Kirim' }}</span>
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
