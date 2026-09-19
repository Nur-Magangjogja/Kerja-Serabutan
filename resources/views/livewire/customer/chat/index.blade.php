<div class="h-full flex-1 min-h-0 flex flex-col overflow-hidden bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100" @if($selected_partner_id || $is_admin_chat) wire:poll.4s.visible @else wire:poll.8s.visible @endif style="overscroll-behavior: none; overscroll-behavior-y: none;">
    {{-- CASE 1: Help belum memiliki mitra --}}
    @if($unassigned_help)
        <!-- Top Header Section -->
        <div class="shrink-0 px-4 pt-4 pb-4 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] shadow-sm text-white select-none rounded-b-2xl">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full -mr-12 -mt-12 blur-xl pointer-events-none"></div>

            <div class="relative z-10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button wire:click="closeChat" aria-label="Kembali ke Daftar Percakapan" class="p-2 -ml-1 hover:bg-white/20 rounded-xl transition-colors duration-200 cursor-pointer flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-base font-bold text-white leading-tight">Pesan</h1>
                        <p class="text-[11px] text-white/80">Status Permohonan Bantuan</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain flex flex-col justify-center p-4" style="overscroll-behavior: contain; overscroll-behavior-y: contain; -webkit-overflow-scrolling: touch; touch-action: pan-y;">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700/80 shadow-sm text-center my-auto">
                <div class="w-16 h-16 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center mx-auto mb-4 border border-blue-100 dark:border-blue-900/60">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-white text-base mb-1">Pesanan Sedang Mencari Rekan Jasa</h3>
                <p class="text-xs text-gray-600 dark:text-gray-300 max-w-xs mx-auto mb-5 leading-relaxed">
                    Permohonan bantuan <strong>"{{ $unassigned_help->title }}"</strong> belum diambil oleh Rekan Jasa. Ruang percakapan akan terbuka otomatis setelah Rekan Jasa mengambil pesanan Anda.
                </p>
                <div class="flex gap-2 justify-center">
                    <button wire:click="closeChat" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-xl transition cursor-pointer">
                        Lihat Semua Pesan
                    </button>
                    <a href="{{ route('customer.helps.detail', $unassigned_help->id) }}" wire:navigate class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl transition shadow-sm">
                        Lihat Detail Pesanan
                    </a>
                </div>
            </div>
        </div>

    {{-- CASE 2: DAFTAR PERCAKAPAN (INBOX LIST) --}}
    @elseif(!$selected_partner_id)
        <!-- Top Header Section for Inbox List -->
        <div class="shrink-0 px-4 pt-4 pb-4 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] shadow-sm text-white select-none rounded-b-2xl">
            <!-- Decorative Ambient Glows -->
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full -mr-12 -mt-12 blur-xl pointer-events-none"></div>

            <div class="relative z-10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('customer.dashboard') }}" wire:navigate aria-label="Kembali ke Beranda" class="p-2 -ml-1 hover:bg-white/20 rounded-xl transition-colors duration-200 cursor-pointer flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-base font-bold text-white leading-tight">Pesan Masuk</h1>
                        <p class="text-[11px] text-white/80">Percakapan Anda dengan Rekan Jasa</p>
                    </div>
                </div>

                <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Search & List Body -->
        <div class="flex-1 min-h-0 flex flex-col overflow-hidden px-4 pt-3 pb-3">
            <!-- Search Input -->
            <div class="shrink-0 mb-3 relative">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama Rekan Jasa..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 border border-gray-200/80 dark:border-gray-700 focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20 outline-none text-xs sm:text-sm transition shadow-xs">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Scrollable Conversation List -->
            <div class="flex-1 min-h-0 overflow-y-auto space-y-2.5 pr-0.5 custom-scrollbar pb-28" style="overscroll-behavior: contain; overscroll-behavior-y: contain; -webkit-overflow-scrolling: touch; touch-action: pan-y;">
                @if($conversations && $conversations->count() > 0)
                    @foreach($conversations as $conv)
                        @if($conv->is_admin ?? false)
                            <!-- Card Percakapan Khusus Tim Admin -->
                            <button wire:key="conv-admin" wire:click="selectAdmin"
                                class="w-full p-3.5 rounded-2xl transition-all text-left bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-700/80 hover:bg-blue-50/50 dark:hover:bg-blue-950/40 shadow-xs hover:shadow-sm flex items-center gap-3.5 cursor-pointer">
                                <div class="w-11 h-11 rounded-2xl bg-sky-500 text-white flex-shrink-0 flex items-center justify-center font-bold text-lg shadow-xs border border-sky-400">
                                    🛡️
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1 mb-0.5">
                                        <div class="flex items-center gap-1.5 truncate">
                                            <h3 class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white truncate">Tim Admin SayaBantu</h3>
                                            <span class="text-[9px] px-1.5 py-0.5 rounded font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 uppercase">Resmi</span>
                                        </div>
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 flex-shrink-0">
                                            {{ $conv->last_message ? $conv->last_message->created_at->diffForHumans(null, true, true) : '' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-1 leading-relaxed">
                                        @if($conv->last_message)
                                            {{ $conv->last_message->message }}
                                        @else
                                            <span class="text-blue-600 dark:text-amber-400 italic">Pusat Layanan Bantuan & Moderasi Resmi</span>
                                        @endif
                                    </p>
                                </div>
                                @if($conv->unread_count > 0)
                                    <div class="w-5 h-5 rounded-full bg-amber-600 text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0 shadow-xs animate-pulse">
                                        {{ $conv->unread_count }}
                                    </div>
                                @endif
                            </button>
                        @else
                            <button wire:key="conv-mitra-{{ $conv->partner->id }}" wire:click="selectPartner({{ $conv->partner->id }})"
                                class="w-full p-3.5 rounded-2xl transition-all text-left bg-white dark:bg-gray-800/90 border border-gray-100 dark:border-gray-700/80 hover:border-blue-200 dark:hover:border-blue-500/40 hover:bg-blue-50/20 dark:hover:bg-gray-750 shadow-xs hover:shadow-sm flex items-center gap-3.5 cursor-pointer">
                                
                                <!-- Avatar -->
                                <div class="w-11 h-11 rounded-2xl overflow-hidden bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-950 dark:to-indigo-950 flex-shrink-0 flex items-center justify-center text-blue-700 dark:text-blue-300 font-bold text-sm shadow-xs border border-blue-200/50 dark:border-blue-800/50">
                                    @if($conv->partner->profile_photo ?? $conv->partner->photo)
                                        <img src="{{ asset('storage/' . ($conv->partner->profile_photo ?? $conv->partner->photo)) }}" alt="{{ $conv->partner->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ strtoupper(substr($conv->partner->name ?? 'M', 0, 1)) }}
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-1 mb-0.5">
                                        <h3 class="font-bold text-xs sm:text-sm text-gray-900 dark:text-white truncate">{{ $conv->partner->name }}</h3>
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500 flex-shrink-0">
                                            {{ $conv->last_message ? $conv->last_message->created_at->diffForHumans(null, true, true) : '' }}
                                        </span>
                                    </div>

                                    <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-1 leading-relaxed">
                                        @if($conv->last_message)
                                            @if($conv->last_message->sender_type === 'customer')
                                                <span class="text-gray-400 dark:text-gray-500 font-medium">Anda: </span>
                                            @endif
                                            @if($conv->last_message->photo)
                                                <span class="inline-flex items-center gap-0.5 text-blue-600 dark:text-blue-400 font-medium"> Foto bukti • </span>
                                            @endif
                                            {{ $conv->last_message->message }}
                                        @elseif($conv->latest_help)
                                            <span class="text-blue-600 dark:text-blue-400 italic">Pesanan: {{ $conv->latest_help->title }}</span>
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500 italic">Mulai percakapan...</span>
                                        @endif
                                    </p>

                                    @if($conv->latest_help && in_array($conv->latest_help->status, ['taken', 'partner_on_the_way', 'partner_arrived', 'in_progress', 'waiting_customer_confirmation']))
                                        <div class="mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-900/60">
                                                ● {{ $conv->latest_help->status_label }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Unread Badge -->
                                @if($conv->unread_count > 0)
                                    <div class="w-5 h-5 rounded-full bg-blue-600 dark:bg-blue-500 text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0 shadow-xs animate-pulse">
                                        {{ $conv->unread_count }}
                                    </div>
                                @endif
                            </button>
                        @endif
                    @endforeach
                @else
                    <div class="text-center py-14 px-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs">
                        <div class="w-14 h-14 mx-auto bg-gray-50 dark:bg-gray-700/50 rounded-full flex items-center justify-center text-gray-400 dark:text-gray-500 mb-3 border border-gray-100 dark:border-gray-700">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200">Belum Ada Percakapan</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-xs mx-auto">Percakapan akan muncul otomatis ketika Rekan Jasa mengambil pesanan bantuan Anda.</p>
                    </div>
                @endif
            </div>
        </div>

    {{-- CASE 3: ADMIN GROUPING HUB (DAFTAR PENGELOMPOKAN TOPIK ADMIN) --}}
    @elseif($is_admin_chat && !$selected_cancel_request_id && !$selected_report_id)
        <!-- Top Header Section for Admin Grouping Hub -->
        <div class="shrink-0 px-4 pt-4 pb-4 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] shadow-sm text-white select-none rounded-b-2xl">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full -mr-12 -mt-12 blur-xl pointer-events-none"></div>

            <div class="relative z-10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button wire:click="closeChat" aria-label="Kembali ke Pesan Masuk" class="p-2 -ml-1 hover:bg-white/20 rounded-xl transition-colors duration-200 cursor-pointer flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-primary-500 text-white flex items-center justify-center font-bold text-base shadow-xs flex-shrink-0 border border-primary-400">
                            🛡️
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h1 class="text-base font-bold text-white leading-tight">Tim Admin SayaBantu</h1>
                                <span class="text-[8px] px-1.5 py-0.2 rounded font-extrabold bg-primary-100 text-primary-900 uppercase">Resmi</span>
                            </div>
                            <p class="text-[11px] text-white/80">Pusat Layanan Bantuan & Moderasi Resmi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Switcher Tabs -->
        <div class="shrink-0 bg-white dark:bg-gray-850 border-b border-gray-200/80 dark:border-gray-750 px-4 pt-2.5 pb-0 flex items-center gap-2 shadow-2xs">
            <button type="button" wire:click="switchAdminTab('cancellation')"
                class="flex-1 pb-3 pt-1 text-center font-bold text-xs flex items-center justify-center gap-1.5 transition border-b-2 {{ $admin_tab === 'cancellation' ? 'border-[#0098e7] text-[#0098e7] dark:text-[#38bdf8]' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }} cursor-pointer">
                <span>🛵 Tinjauan Pembatalan</span>
                @if($userCancelRequests->count() > 0)
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold {{ $admin_tab === 'cancellation' ? 'bg-sky-100 text-sky-800 dark:bg-sky-950/80 dark:text-sky-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                        {{ $userCancelRequests->count() }}
                    </span>
                @endif
            </button>

            <button type="button" wire:click="switchAdminTab('report')"
                class="flex-1 pb-3 pt-1 text-center font-bold text-xs flex items-center justify-center gap-1.5 transition border-b-2 {{ $admin_tab === 'report' ? 'border-[#0098e7] text-[#0098e7] dark:text-[#38bdf8]' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }} cursor-pointer">
                <span>📋 Laporan Aduan</span>
                @if($userReports->count() > 0)
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold {{ $admin_tab === 'report' ? 'bg-sky-100 text-sky-800 dark:bg-sky-950/80 dark:text-sky-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                        {{ $userReports->count() }}
                    </span>
                @endif
            </button>
        </div>

        <!-- Grouping List Body with safe bottom padding for mobile bottom nav -->
        <div class="flex-1 min-h-0 overflow-y-auto px-4 pt-4 pb-36 space-y-3 custom-scrollbar" style="overscroll-behavior: contain; overscroll-behavior-y: contain; -webkit-overflow-scrolling: touch; touch-action: pan-y;">
            @if($admin_tab === 'cancellation')
                @forelse($userCancelRequests as $cReq)
                    <div wire:key="cancel-group-{{ $cReq->id }}"
                         wire:click="selectAdminCancel({{ $cReq->id }})"
                         class="bg-white dark:bg-gray-850 rounded-2xl p-3.5 sm:p-4 border border-gray-200/80 dark:border-gray-750 shadow-xs hover:border-[#0098e7] dark:hover:border-primary-500 hover:shadow-md transition-all cursor-pointer group">
                        
                        <div class="flex items-start justify-between gap-2.5 mb-2.5">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0 border {{ $cReq->job_icon_box_class }}">
                                    {{ $cReq->job_icon }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-[9.5px] font-extrabold px-1.5 py-0.5 rounded-md whitespace-nowrap {{ $cReq->job_category_badge_class }}">
                                            {{ $cReq->job_label }}
                                        </span>
                                        <span class="text-[10px] font-semibold text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                            • {{ $cReq->cancellation_type_label }}
                                        </span>
                                    </div>
                                    <h3 class="font-bold text-sm text-gray-900 dark:text-gray-100 truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors mt-0.5" title="{{ $cReq->help->title ?? 'Permohonan Bantuan' }}">
                                        {{ $cReq->help->title ?? 'Permohonan Bantuan' }}
                                    </h3>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-400">
                                        {{ $cReq->created_at->format('d M Y, H:i') }}
                                    </p>
                                </div>
                            </div>

                            @php
                                $st = strtolower($cReq->status);
                                $badgeStyle = match($st) {
                                    'approved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800/60',
                                    'rejected' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/70 dark:text-rose-300 border-rose-200/80 dark:border-rose-800/60',
                                    default => 'bg-amber-50 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/60',
                                };
                                $badgeLabel = match($st) {
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                    default => 'Menunggu Tinjauan',
                                };
                            @endphp
                            <span class="text-[9.5px] sm:text-[10px] px-2 py-0.5 rounded-full font-bold uppercase shrink-0 border whitespace-nowrap {{ $badgeStyle }}">
                                {{ $badgeLabel }}
                            </span>
                        </div>

                        <div class="bg-gray-50/90 dark:bg-gray-900/60 rounded-xl p-3 space-y-1.5 text-xs mb-3 border border-gray-200/70 dark:border-gray-750">
                            <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300 min-w-0">
                                <span class="text-gray-500 dark:text-gray-400 font-medium shrink-0">Pemohon:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200 truncate">
                                    {{ $cReq->requester_type === 'customer' ? 'Customer (Anda)' : 'Rekan Jasa Mitra (' . ($cReq->partner->name ?? 'Mitra') . ')' }}
                                </span>
                            </div>
                            <div class="text-gray-600 dark:text-gray-300 min-w-0">
                                <span class="text-gray-500 dark:text-gray-400 font-medium shrink-0">Alasan:</span>
                                <span class="text-gray-700 dark:text-gray-300 font-medium italic break-words [overflow-wrap:anywhere]">"{{ $cReq->reason }}"</span>
                            </div>
                            @if($cReq->help && $cReq->help->isPickup() && ($cReq->help->pickup_address || $cReq->help->delivery_address))
                                <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300 min-w-0">
                                    <span class="text-gray-500 dark:text-gray-400 font-medium shrink-0">Rute:</span>
                                    <span class="font-semibold text-gray-800 dark:text-gray-200 truncate">
                                        {{ Str::limit($cReq->help->pickup_address ?: 'Titik Jemput', 25) }} ➔ {{ Str::limit($cReq->help->delivery_address ?: 'Tujuan', 25) }}
                                    </span>
                                </div>
                            @endif
                            @if($cReq->work_completed_percentage > 0)
                                <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                    <span class="text-gray-500 dark:text-gray-400 font-medium shrink-0">Porsi Selesai:</span>
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($cReq->work_completed_percentage, 0) }}%</span>
                                </div>
                            @endif
                        </div>

                        {{-- Pesan Terakhir & Badge Belum Dibaca --}}
                        <div class="flex items-center justify-between pt-1 text-xs border-t border-gray-100 dark:border-gray-750">
                            <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 min-w-0 flex-1 mr-2">
                                <svg class="w-3.5 h-3.5 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <p class="truncate text-[11px]">
                                    @if($cReq->last_message)
                                        {{ $cReq->last_message->message }}
                                    @else
                                        <span class="italic text-gray-400 dark:text-gray-500">Belum ada obrolan</span>
                                    @endif
                                </p>
                            </div>

                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if($cReq->unread_count > 0)
                                    <span class="w-5 h-5 rounded-full bg-primary-600 text-white text-[10px] font-bold flex items-center justify-center animate-pulse shadow-xs">
                                        {{ $cReq->unread_count }}
                                    </span>
                                @endif
                                <span class="text-[11px] font-bold text-primary-600 dark:text-primary-400 flex items-center gap-0.5 group-hover:translate-x-0.5 transition-transform">
                                    Buka Ruang Obrolan
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 px-4 bg-white dark:bg-gray-850 rounded-2xl border border-gray-100 dark:border-gray-750 shadow-xs">
                        <div class="w-12 h-12 mx-auto bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center text-gray-400 dark:text-gray-500 mb-2">
                            <span class="text-2xl">🛵</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200">Tidak Ada Tinjauan Pembatalan</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Saat ini Anda tidak memiliki pengajuan pembatalan yang memerlukan tindak lanjut.</p>
                    </div>
                @endforelse
            @elseif($admin_tab === 'report')
                @forelse($userReports as $rep)
                    <div wire:key="report-group-{{ $rep->id }}"
                         wire:click="selectAdminReport({{ $rep->id }})"
                         class="bg-white dark:bg-gray-850 rounded-2xl p-4 border border-gray-200/80 dark:border-gray-750 shadow-xs hover:border-[#0098e7] dark:hover:border-primary-500 hover:shadow-md transition-all cursor-pointer group">
                        
                        <div class="flex items-start justify-between gap-3 mb-2.5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-rose-500/10 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg flex-shrink-0 border border-rose-500/20 dark:border-rose-500/30">
                                    📋
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-sm text-gray-900 dark:text-gray-100 truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                                        {{ $rep->display_title }}
                                    </h3>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-400">
                                        {{ $rep->created_at->format('d M Y, H:i') }}
                                    </p>
                                </div>
                            </div>

                            @php
                                $st = strtolower($rep->status);
                                $badgeStyle = match($st) {
                                    'resolved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800/60',
                                    'investigating', 'in_progress' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/70 dark:text-sky-300 border-sky-200/80 dark:border-sky-800/60',
                                    'dismissed' => 'bg-gray-100 text-gray-700 dark:bg-gray-800/80 dark:text-gray-300 border-gray-200/80 dark:border-gray-700',
                                    default => 'bg-amber-50 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/60',
                                };
                                $badgeLabel = match($st) {
                                    'resolved' => 'Selesai',
                                    'investigating', 'in_progress' => 'Investigasi Admin',
                                    'dismissed' => 'Ditutup',
                                    default => 'Menunggu Antrean',
                                };
                            @endphp
                            <span class="text-[10px] px-2.5 py-0.5 rounded-full font-bold uppercase shrink-0 border {{ $badgeStyle }}">
                                {{ $badgeLabel }}
                            </span>
                        </div>

                        <div class="bg-gray-50/80 dark:bg-gray-900/60 rounded-xl p-3 space-y-1.5 text-xs mb-3 border border-gray-200/60 dark:border-gray-750">
                            <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                <span class="text-gray-500 dark:text-gray-400 font-medium">Terkait:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200 truncate">
                                    {{ $rep->display_topic }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                <span class="text-gray-500 dark:text-gray-400 font-medium">Pihak:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200 truncate">
                                    {{ $rep->reportedUser?->name ?? ($rep->reported_user_text ?? 'Pengguna') }}
                                </span>
                            </div>
                            @if($rep->message)
                                <div class="text-gray-600 dark:text-gray-300">
                                    <span class="text-gray-500 dark:text-gray-400 font-medium">Kronologi:</span>
                                    <span class="text-gray-700 dark:text-gray-300 font-medium italic">"{{ Str::limit($rep->message, 80) }}"</span>
                                </div>
                            @endif
                        </div>

                        {{-- Pesan Terakhir & Badge Belum Dibaca --}}
                        <div class="flex items-center justify-between pt-1 text-xs border-t border-gray-100 dark:border-gray-750">
                            <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400 min-w-0 flex-1 mr-2">
                                <svg class="w-3.5 h-3.5 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <p class="truncate text-[11px]">
                                    @if($rep->last_message)
                                        {{ $rep->last_message->message }}
                                    @else
                                        <span class="italic text-gray-400 dark:text-gray-500">Belum ada obrolan</span>
                                    @endif
                                </p>
                            </div>

                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if($rep->unread_count > 0)
                                    <span class="w-5 h-5 rounded-full bg-primary-600 text-white text-[10px] font-bold flex items-center justify-center animate-pulse shadow-xs">
                                        {{ $rep->unread_count }}
                                    </span>
                                @endif
                                <span class="text-[11px] font-bold text-primary-600 dark:text-primary-400 flex items-center gap-0.5 group-hover:translate-x-0.5 transition-transform">
                                    Buka Ruang Obrolan
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 px-4 bg-white dark:bg-gray-850 rounded-2xl border border-gray-100 dark:border-gray-750 shadow-xs">
                        <div class="w-12 h-12 mx-auto bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center text-gray-400 dark:text-gray-500 mb-2">
                            <span class="text-2xl">📋</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800 dark:text-gray-200">Tidak Ada Laporan Aduan</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Saat ini Anda tidak memiliki laporan aduan yang sedang diproses.</p>
                    </div>
                @endforelse
            @endif
        </div>

    {{-- CASE 4: RUANG OBROLAN AKTIF (CHAT ROOM) --}}
    @else
        <!-- Single Unified Top Chat Header -->
        <div class="shrink-0 px-3.5 py-3 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] shadow-sm text-white select-none z-20" style="touch-action: none;">
            <!-- Ambient Glow -->
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full -mr-12 -mt-12 blur-xl pointer-events-none"></div>

            <div class="relative z-10 flex items-center justify-between gap-2.5">
                <!-- Left: Back Button + Avatar + Name -->
                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                    @if($is_admin_chat)
                        <button wire:click="unselectAdminIssue" aria-label="Kembali ke Daftar Topik Moderasi"
                            class="p-1.5 -ml-1 hover:bg-white/20 rounded-xl transition-colors duration-200 cursor-pointer flex-shrink-0 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    @else
                        <button wire:click="closeChat" aria-label="Kembali ke Daftar Percakapan"
                            class="p-1.5 -ml-1 hover:bg-white/20 rounded-xl transition-colors duration-200 cursor-pointer flex-shrink-0 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    @endif

                    @if($selected_partner->is_admin ?? false)
                        <div class="w-9 h-9 rounded-xl bg-primary-500 text-white flex items-center justify-center font-bold text-base shadow-xs flex-shrink-0 border border-primary-400">
                            🛡️
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5">
                                <h2 class="font-bold text-sm text-white truncate leading-tight">Tim Admin SayaBantu</h2>
                                <span class="text-[8px] px-1.5 py-0.2 rounded font-extrabold bg-primary-100 text-primary-900 uppercase">Resmi</span>
                            </div>
                            <p class="text-[10px] text-white/80 truncate">Pusat Layanan Bantuan & Moderasi Resmi</p>
                        </div>
                    @else
                        <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0 bg-white/20 flex items-center justify-center text-white font-bold text-xs border border-white/40 shadow-xs">
                            @if($selected_partner->profile_photo ?? $selected_partner->photo)
                                <img src="{{ asset('storage/' . ($selected_partner->profile_photo ?? $selected_partner->photo)) }}" alt="{{ $selected_partner->name }}" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr($selected_partner->name ?? 'M', 0, 1)) }}
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="font-bold text-sm text-white truncate leading-tight">{{ $selected_partner->name }}</h2>
                            <p class="text-[10px] text-white/80">Rekan Jasa</p>
                        </div>
                    @endif
                </div>

                <!-- Right: Action Buttons -->
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    @if($is_admin_chat)
                        <button wire:click="unselectAdminIssue" class="px-2.5 py-1 bg-white/15 hover:bg-white/25 text-white text-[11px] font-bold rounded-lg transition border border-white/20 shadow-xs cursor-pointer" title="Daftar Topik Moderasi">
                            Daftar Topik
                        </button>
                    @endif
                    @if($active_help && !($selected_partner->is_admin ?? false))
                        <a href="{{ route('customer.helps.detail', $active_help->id) }}" wire:navigate class="px-2.5 py-1 bg-white/15 hover:bg-white/25 text-white text-[11px] font-bold rounded-lg transition border border-white/20 shadow-xs" title="Rincian Pesanan">
                            Pesanan
                        </a>
                    @endif
                    @if(!($selected_partner->is_admin ?? false))
                        <a href="{{ route('customer.reports.create.user', ['user_id' => $selected_partner->id, 'help_id' => $active_help_id ?? ($active_help->id ?? null)]) }}" wire:navigate class="px-2.5 py-1 bg-rose-500/80 hover:bg-rose-600 text-white text-[11px] font-bold rounded-lg transition border border-rose-400/50 shadow-xs" title="Laporkan">
                            Lapor
                        </a>
                    @endif
                </div>
            </div>
        </div>

        @if($is_admin_chat)
            {{-- Context Rincian Masalah yang Sedang Dibahas (Tanpa #) --}}
            @if($admin_tab === 'cancellation' && $selected_cancel_request)
                <div class="shrink-0 bg-slate-50 dark:bg-gray-850 px-3.5 sm:px-4 py-2.5 border-b border-gray-200/80 dark:border-gray-750 space-y-2 shadow-2xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                            <span class="text-sm shrink-0">{{ $selected_cancel_request->job_icon }}</span>
                            <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-gray-100 truncate" title="{{ $selected_cancel_request->help->title ?? 'Permohonan Bantuan' }}">
                                {{ $selected_cancel_request->help->title ?? 'Permohonan Bantuan' }}
                            </span>
                            <span class="text-[9.5px] font-extrabold px-1.5 py-0.5 rounded-md whitespace-nowrap shrink-0 {{ $selected_cancel_request->job_category_badge_class }}">
                                {{ $selected_cancel_request->job_label }}
                            </span>
                        </div>
                        @php
                            $st = strtolower($selected_cancel_request->status);
                            $badgeStyle = match($st) {
                                'approved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800/60',
                                'rejected' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/70 dark:text-rose-300 border-rose-200/80 dark:border-rose-800/60',
                                default => 'bg-amber-50 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/60',
                            };
                            $badgeLabel = match($st) {
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                default => 'Menunggu Tinjauan',
                            };
                        @endphp
                        <div class="flex items-center gap-1.5 flex-wrap shrink-0">
                            <span class="text-[9.5px] px-2 py-0.5 rounded-full font-bold bg-sky-50 dark:bg-sky-950/70 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800/60 whitespace-nowrap">
                                {{ $selected_cancel_request->cancellation_type_label }}
                            </span>
                            <span class="text-[9.5px] sm:text-[10px] px-2.5 py-0.5 rounded-full font-bold uppercase shrink-0 border whitespace-nowrap {{ $badgeStyle }}">
                                {{ $badgeLabel }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-3.5 gap-y-1.5 text-[11px] text-gray-600 dark:text-gray-300">
                        <div class="flex items-center gap-1 min-w-0">
                            <span class="text-gray-500 dark:text-gray-400 shrink-0">Pemohon:</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200 capitalize truncate">{{ $selected_cancel_request->requester_type === 'customer' ? 'Customer (Anda)' : 'Rekan Jasa Mitra' }}</span>
                        </div>
                        <div class="min-w-0 max-w-full">
                            <span class="text-gray-500 dark:text-gray-400 shrink-0">Alasan:</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300 break-words [overflow-wrap:anywhere] italic">"{{ $selected_cancel_request->reason }}"</span>
                        </div>
                        @if($selected_cancel_request->help && $selected_cancel_request->help->isPickup() && ($selected_cancel_request->help->pickup_address || $selected_cancel_request->help->delivery_address))
                            <div class="flex items-center gap-1 min-w-0">
                                <span class="text-gray-500 dark:text-gray-400 shrink-0">Rute:</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200 truncate">{{ Str::limit($selected_cancel_request->help->pickup_address ?: 'Jemput', 20) }} ➔ {{ Str::limit($selected_cancel_request->help->delivery_address ?: 'Tujuan', 20) }}</span>
                            </div>
                        @endif
                        @if($selected_cancel_request->work_completed_percentage > 0)
                            <div class="flex items-center gap-1">
                                <span class="text-gray-500 dark:text-gray-400 shrink-0">Porsi Selesai:</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($selected_cancel_request->work_completed_percentage, 0) }}%</span>
                            </div>
                        @endif
                        @if($selected_cancel_request->partner_clarification)
                            <div class="w-full text-[10.5px] text-sky-800 dark:text-sky-200 bg-sky-50/80 dark:bg-sky-950/50 px-2.5 py-1 rounded-lg border border-sky-200/70 dark:border-sky-850 break-words [overflow-wrap:anywhere]">
                                <span class="font-bold">Klarifikasi Rekan Jasa:</span> {{ $selected_cancel_request->partner_clarification }}
                            </div>
                        @endif
                        @if($selected_cancel_request->admin_notes)
                            <div class="w-full text-[10.5px] text-purple-800 dark:text-purple-200 bg-purple-50/80 dark:bg-purple-950/50 px-2.5 py-1 rounded-lg border border-purple-200/70 dark:border-purple-850 break-words [overflow-wrap:anywhere]">
                                <span class="font-bold">Catatan Putusan Admin:</span> {{ $selected_cancel_request->admin_notes }}
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($admin_tab === 'report' && $selected_report)
                <div class="shrink-0 bg-slate-50 dark:bg-gray-850 px-3.5 sm:px-4 py-2.5 border-b border-gray-200/80 dark:border-gray-750 space-y-2 shadow-2xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                            <span class="text-sm shrink-0">📋</span>
                            <span class="text-xs sm:text-sm font-bold text-gray-900 dark:text-gray-100 truncate" title="{{ $selected_report->display_title }}">
                                {{ $selected_report->display_title }}
                            </span>
                        </div>
                        @php
                            $st = strtolower($selected_report->status);
                            $badgeStyle = match($st) {
                                'resolved' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800/60',
                                'investigating', 'in_progress' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/70 dark:text-sky-300 border-sky-200/80 dark:border-sky-800/60',
                                'dismissed' => 'bg-gray-100 text-gray-700 dark:bg-gray-800/80 dark:text-gray-300 border-gray-200/80 dark:border-gray-700',
                                default => 'bg-amber-50 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300 border-amber-200/80 dark:border-amber-800/60',
                            };
                            $badgeLabel = match($st) {
                                'resolved' => 'Selesai',
                                'investigating', 'in_progress' => 'Investigasi Admin',
                                'dismissed' => 'Ditutup',
                                default => 'Menunggu Antrean',
                            };
                        @endphp
                        <span class="text-[9.5px] sm:text-[10px] px-2.5 py-0.5 rounded-full font-bold uppercase shrink-0 border whitespace-nowrap self-start sm:self-auto {{ $badgeStyle }}">
                            {{ $badgeLabel }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-x-3.5 gap-y-1.5 text-[11px] text-gray-600 dark:text-gray-300">
                        <div class="flex items-center gap-1 min-w-0">
                            <span class="text-gray-500 dark:text-gray-400 shrink-0">Terkait:</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200 truncate">{{ $selected_report->display_topic }}</span>
                        </div>
                        <div class="flex items-center gap-1 min-w-0">
                            <span class="text-gray-500 dark:text-gray-400 shrink-0">Pihak:</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200 truncate">{{ $selected_report->reportedUser?->name ?? ($selected_report->reported_user_text ?? 'Pengguna') }}</span>
                        </div>
                        @if($selected_report->message)
                            <div class="w-full text-[10.5px] text-gray-800 dark:text-gray-200 bg-gray-100/80 dark:bg-gray-900/60 px-2.5 py-1 rounded-lg border border-gray-200/70 dark:border-gray-750 break-words [overflow-wrap:anywhere]">
                                <span class="font-bold text-gray-500 dark:text-gray-400">Kronologi:</span> "{{ $selected_report->message }}"
                            </div>
                        @endif
                        @if($selected_report->admin_notes)
                            <div class="w-full text-[10.5px] text-purple-800 dark:text-purple-200 bg-purple-50/80 dark:bg-purple-950/50 px-2.5 py-1 rounded-lg border border-purple-200/70 dark:border-purple-850 break-words [overflow-wrap:anywhere]">
                                <span class="font-bold">Catatan Putusan Admin:</span> {{ $selected_report->admin_notes }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endif

        <!-- Messages Stream Feed -->
        <div id="messagesWrapper"
             data-active-partner-id="{{ $selected_partner_id }}"
             data-active-help-id="{{ $active_help_id }}"
             data-active-cancel-id="{{ $selected_cancel_request_id }}"
             data-active-report-id="{{ $selected_report_id }}"
             x-data="{
                 scrollToBottom(smooth = false) {
                     this.$nextTick(() => {
                         this.$el.scrollTo({
                             top: this.$el.scrollHeight,
                             behavior: smooth ? 'smooth' : 'instant'
                         });
                     });
                 },
                 isNearBottom() {
                     return this.$el.scrollHeight - this.$el.scrollTop - this.$el.clientHeight < 160;
                 },
                 init() {
                     this.scrollToBottom(false);
                     setTimeout(() => this.scrollToBottom(false), 80);
                     const observer = new MutationObserver(() => {
                         if (this.isNearBottom()) {
                             this.scrollToBottom(true);
                         }
                     });
                     observer.observe(this.$el, { childList: true });
                 }
             }"
             x-on:scroll-chat-bottom.window="scrollToBottom(false)"
             x-on:message-sent.window="scrollToBottom(true)"
             class="flex-1 min-h-0 overflow-y-auto p-3.5 sm:p-4 space-y-3 bg-slate-100/70 dark:bg-gray-900/90 custom-scrollbar"
             style="overscroll-behavior: contain; overscroll-behavior-y: contain; -webkit-overflow-scrolling: touch; touch-action: pan-y;">

            @if($messages && $messages->count() > 0)
                @php $lastHelpContextId = null; @endphp
                @foreach($messages as $msg)
                    {{-- Context Separator jika berpindah bantuan (untuk chat mitra reguler) --}}
                    @if(!$is_admin_chat && $msg->help_id && $msg->help_id !== $lastHelpContextId && $msg->help)
                        @php $lastHelpContextId = $msg->help_id; @endphp
                        <div wire:key="ctx-sep-{{ $msg->help_id }}" class="flex items-center justify-center my-3">
                            <div class="bg-blue-50 dark:bg-blue-950/60 border border-blue-100 dark:border-blue-900/80 px-3 py-1 rounded-full text-[11px] text-blue-700 dark:text-blue-300 font-semibold shadow-xs flex items-center gap-1">
                                <span>📌 {{ Str::limit($msg->help->title, 35) }}</span>
                            </div>
                        </div>
                    @endif

                    @if($is_admin_chat)
                        {{-- DI DALAM RUANG 🛡️ TIM ADMIN SAYABANTU --}}
                        @if($msg->is_admin)
                            <div wire:key="msg-adm-{{ $msg->id }}" class="flex justify-start my-1">
                                <div class="rounded-2xl p-3.5 max-w-[85%] shadow-xs bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 text-gray-900 dark:text-gray-100 rounded-bl-xs">
                                    <div class="flex items-center gap-1.5 mb-1 text-sky-600 dark:text-sky-400 font-bold text-[11px]">
                                        <span>🛡️</span>
                                        <span>Tim Admin SayaBantu</span>
                                        <span class="text-[8px] px-1 py-0.2 rounded font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 uppercase">Resmi</span>
                                    </div>
                                    @if($msg->photo)
                                        <div class="mb-2 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                                            <a href="{{ asset('storage/' . $msg->photo) }}" target="_blank" rel="noopener">
                                                <img src="{{ asset('storage/' . $msg->photo) }}" alt="Foto Bukti" class="w-full max-h-56 object-cover hover:opacity-95 transition cursor-pointer">
                                            </a>
                                        </div>
                                    @endif
                                    <p class="text-xs leading-relaxed break-words whitespace-pre-line text-gray-900 dark:text-gray-100 font-normal">
                                        {{ $msg->message }}
                                    </p>
                                    <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 text-right select-none">
                                        {{ $msg->created_at->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div wire:key="msg-cust-{{ $msg->id }}" class="flex justify-end my-1">
                                <div class="rounded-2xl p-3.5 max-w-[85%] shadow-xs bg-[#0098e7] text-white rounded-br-xs">
                                    @if($msg->photo)
                                        <div class="mb-2 rounded-xl overflow-hidden border border-black/10 dark:border-white/10">
                                            <a href="{{ asset('storage/' . $msg->photo) }}" target="_blank" rel="noopener">
                                                <img src="{{ asset('storage/' . $msg->photo) }}" alt="Foto" class="w-full max-h-56 object-cover hover:opacity-95 transition cursor-pointer">
                                            </a>
                                        </div>
                                    @endif
                                    <p class="text-xs leading-relaxed break-words whitespace-pre-line">{{ $msg->message }}</p>
                                    <div class="text-[10px] mt-1 flex items-center justify-end gap-1 text-white/80 select-none">
                                        <span>{{ $msg->created_at->format('H:i') }}</span>
                                        @if(!empty($msg->is_read) || !empty($msg->read_at))
                                            <span class="text-blue-200 font-bold" title="Dibaca">✓✓</span>
                                        @else
                                            <span class="text-white/60 font-medium" title="Terkirim">✓</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        {{-- DI DALAM RUANG CHAT MITRA --}}
                        @if($msg->sender_type === 'system' || str_starts_with($msg->message, 'Sistem SayaBantu:'))
                            <div wire:key="msg-sys-{{ $msg->id }}" class="flex justify-center my-2">
                                <div class="max-w-md bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3.5 py-2 rounded-xl text-center shadow-2xs">
                                    <p class="text-[11px] text-gray-700 dark:text-gray-300 leading-relaxed font-medium">
                                        {{ str_replace('Sistem SayaBantu: ', '', $msg->message) }}
                                    </p>
                                    <span class="text-[9px] text-gray-400 dark:text-gray-500 mt-0.5 block">
                                        {{ $msg->created_at->format('H:i') }}
                                    </span>
                                </div>
                            </div>
                        @elseif(str_contains($msg->message, '🛡️') || str_contains($msg->message, '[Pesan Resmi Admin') || str_contains($msg->message, '[Sistem Moderasi'))
                            <div wire:key="msg-adm-relay-{{ $msg->id }}" class="flex justify-start my-1">
                                <div class="rounded-2xl p-3.5 max-w-[85%] shadow-xs bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 text-gray-900 dark:text-gray-100 rounded-bl-xs">
                                    <div class="flex items-center gap-1.5 mb-1 text-sky-600 dark:text-sky-400 font-bold text-[11px]">
                                        <span>🛡️</span>
                                        <span>Pesan Resmi Admin Moderasi</span>
                                    </div>
                                    <p class="text-xs leading-relaxed break-words whitespace-pre-line text-gray-900 dark:text-gray-100 font-normal">
                                        {{ $msg->message }}
                                    </p>
                                    <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 text-right select-none">
                                        {{ $msg->created_at->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div wire:key="msg-item-{{ $msg->id }}" class="flex {{ $msg->sender_type === 'customer' ? 'justify-end' : 'justify-start' }} my-1">
                                <div class="rounded-2xl p-3.5 max-w-[85%] shadow-xs {{ $msg->sender_type === 'customer' ? 'bg-[#0098e7] text-white rounded-br-xs' : 'bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 text-gray-900 dark:text-gray-100 rounded-bl-xs' }}">
                                    @if($msg->photo)
                                        <div class="mb-2 rounded-xl overflow-hidden border border-black/10 dark:border-white/10">
                                            <a href="{{ asset('storage/' . $msg->photo) }}" target="_blank" rel="noopener">
                                                <img src="{{ asset('storage/' . $msg->photo) }}" alt="Foto Bukti" class="w-full max-h-56 object-cover hover:opacity-95 transition cursor-pointer">
                                            </a>
                                            <div class="px-2 py-1 bg-black/60 text-[10px] text-white flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                Lampiran Foto
                                            </div>
                                        </div>
                                    @endif

                                    <p class="text-xs leading-relaxed break-words whitespace-pre-line">{{ $msg->message }}</p>
                                    @if($msg->sender_type === 'customer')
                                        <div class="text-[10px] mt-1 flex items-center justify-end gap-1 text-white/80 select-none">
                                            <span>{{ $msg->created_at->format('H:i') }}</span>
                                            @if(!empty($msg->is_read) || !empty($msg->read_at))
                                                <span class="text-blue-200 font-bold" title="Dibaca">✓✓</span>
                                            @else
                                                <span class="text-white/60 font-medium" title="Terkirim">✓</span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-[10px] mt-1 text-right text-gray-400 dark:text-gray-500 select-none">
                                            {{ $msg->created_at->format('H:i') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif
                @endforeach
            @else
                <div class="text-center text-gray-400 dark:text-gray-500 text-xs py-10">
                    @if($is_admin_chat)
                        Belum ada pesan percakapan pada topik ini. Ketik pesan di bawah untuk memberikan tanggapan kepada Tim Admin.
                    @else
                        Belum ada pesan. Ketik pesan di bawah untuk memulai percakapan.
                    @endif
                </div>
            @endif
        </div>

        <!-- Bottom Fixed Input Bar -->
        <form wire:submit="sendMessage" class="shrink-0 p-2.5 sm:p-3 border-t border-gray-200/80 dark:border-gray-800 bg-white dark:bg-gray-850 space-y-2 shadow-lg">
            @if($photo)
                <div class="flex items-center gap-2 p-2 bg-blue-50/70 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-800/80 rounded-xl">
                    @php
                        $canPreview = false;
                        try {
                            $canPreview = method_exists($photo, 'temporaryUrl') && $photo->isPreviewable();
                        } catch (\Throwable $e) { $canPreview = false; }
                    @endphp
                    @if($canPreview)
                        <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="w-12 h-12 object-cover rounded-lg border border-blue-300 shadow-xs">
                    @else
                        <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/60 flex items-center justify-center text-blue-600 text-[10px] font-bold">
                            FOTO
                        </div>
                    @endif
                    <div class="flex-1 min-w-0 text-xs">
                        <p class="font-bold text-gray-800 dark:text-gray-200 truncate">{{ $photo->getClientOriginalName() }}</p>
                        <p class="text-[10px] text-gray-400">Siap dikirimkan bersama pesan</p>
                    </div>
                    <button type="button" wire:click="removePhoto" class="p-1.5 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg text-xs transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            <div class="flex items-center gap-2">
                {{-- Tombol Lampirkan Foto --}}
                <label class="p-2.5 bg-gray-100 hover:bg-blue-50 dark:bg-gray-750 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 hover:text-blue-600 rounded-xl transition cursor-pointer flex-shrink-0 flex items-center justify-center" title="Lampirkan Foto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <input type="file" wire:model="photo" accept="image/png, image/jpeg, image/jpg" class="hidden">
                </label>

                @php
                    $placeholder = "Tulis pesan ke " . ($selected_partner->name ?? 'User') . "...";
                    if ($is_admin_chat) {
                        if ($admin_tab === 'cancellation') {
                            $placeholder = "Tulis tanggapan / klarifikasi pembatalan ke Admin...";
                        } else {
                            $placeholder = "Tulis penjelasan / bukti aduan ke Tim Admin...";
                        }
                    }
                @endphp

                <input type="text" wire:model="message" placeholder="{{ $placeholder }}"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700 text-xs text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-400 outline-none focus:ring-2 focus:ring-blue-500/30 focus:bg-white dark:focus:bg-gray-700 transition"
                    autofocus>

                <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="sendMessage"
                    class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center justify-center gap-1 cursor-pointer flex-shrink-0 disabled:opacity-75 disabled:cursor-not-allowed">
                    Kirim
                </button>
            </div>
            @error('photo')
                <p class="text-[10px] text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </form>
    @endif

    <script>
        function scrollChatToBottom(smooth = false) {
            const el = document.getElementById('messagesWrapper');
            if (!el) return;
            el.scrollTo({
                top: el.scrollHeight,
                behavior: smooth ? 'smooth' : 'instant'
            });
        }

        document.addEventListener('DOMContentLoaded', () => setTimeout(() => scrollChatToBottom(false), 60));
        document.addEventListener('livewire:navigated', () => setTimeout(() => scrollChatToBottom(false), 60));
        
        window.addEventListener('message-sent', () => {
            setTimeout(() => scrollChatToBottom(true), 60);
        });

        window.addEventListener('scroll-chat-bottom', () => setTimeout(() => scrollChatToBottom(false), 40));

        window.addEventListener('help-new-message', () => {
            setTimeout(() => scrollChatToBottom(false), 60);
        });

        const observer = new MutationObserver(() => {
            const el = document.getElementById('messagesWrapper');
            if (el) {
                const isNearBottom = el.scrollHeight - el.scrollTop - el.clientHeight < 160;
                if (isNearBottom || el.scrollTop === 0) {
                    el.scrollTop = el.scrollHeight;
                }
            }
        });

        function initChatObserver() {
            const el = document.getElementById('messagesWrapper');
            if (el) observer.observe(el, { childList: true, subtree: true });
        }

        document.addEventListener('DOMContentLoaded', initChatObserver);
        document.addEventListener('livewire:navigated', initChatObserver);
    </script>
</div>