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
    </style>

    <div class="max-w-md mx-auto">
        <!-- Header Section -->
        <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>
            
            <div class="relative z-10">
                <div class="flex items-center justify-between text-white">
                    <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <div class="text-center flex-1 min-w-0 px-2">
                        <h1 class="text-base font-bold truncate">Bantuan Sedang Diproses</h1>
                        <p class="text-xs text-white/90 truncate mt-0.5">Kelola pekerjaan yang sedang berjalan</p>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        @include('components.notification-icon', ['route' => route('mitra.notifications.index'), 'class' => 'bg-white/15 backdrop-blur-md p-2 rounded-xl hover:bg-white/25 transition cursor-pointer text-white'])
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="px-5 pt-5 pb-20 min-h-[60vh]">
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-50 border border-green-100 rounded-lg text-green-700 text-sm">{{ session('success') }}</div>
        @endif

        @if(count($helps) === 0)
            <div class="text-center py-16 bg-white rounded-xl shadow-sm">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm font-semibold text-gray-700">Belum ada bantuan yang diproses</p>
                <p class="text-xs text-gray-500 mt-1">Bantuan yang Anda ambil akan muncul di sini</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($helps as $help)
                    <div class="bg-white rounded-xl p-3.5 shadow-sm hover:shadow-md transition-all border border-gray-100">
                        <div class="flex items-start gap-3">
                            <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                                @if($help->photo)
                                    <img src="{{ asset('storage/' . $help->photo) }}" alt="{{ $help->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-lg">
                                        {{ ['🩺', '🏠', '💡', '🔧', '🎯'][($loop->index) % 5] }}
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <h3 class="font-semibold text-sm text-gray-900 line-clamp-1">{{ $help->title }}</h3>
                                    <span class="text-xs font-bold whitespace-nowrap" style="color: #0098e7;">Rp {{ number_format($help->amount, 0, ',', '.') }}</span>
                                </div>

                                <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $help->status_color }} dark:bg-opacity-20 border border-current border-opacity-20">
                                        <span>{{ $help->progress_icon }}</span>
                                        <span>{{ $help->progress_summary }}</span>
                                    </span>
                                    <span class="text-xs text-gray-400 font-medium">{{ optional($help->taken_at)->diffForHumans() ?? optional($help->created_at)->diffForHumans() }}</span>
                                </div>

                                <!-- Progress Track -->
                                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden mb-3">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $help->progress_percentage == 100 ? 'bg-emerald-500' : 'bg-blue-600 dark:bg-blue-500' }}"
                                         style="width: {{ $help->progress_percentage }}%;"></div>
                                </div>

                                <p class="text-xs text-gray-600 line-clamp-2 mb-3">{{ Str::limit($help->description ?? $help->location ?? '-', 100) }}</p>

                                @if($help->scheduled_at)
                                    <div class="text-xs text-gray-500 mb-2">📅 {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }}</div>
                                @endif

                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-xs text-gray-500">👤 {{ optional($help->user)->name ?? 'Customer' }}</span>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('mitra.helps.detail', $help->id) }}" class="px-3 py-1.5 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600 transition">
                                            Detail
                                        </a>
                                        @if(optional($help->user)->phone)
                                            <a href="tel:{{ optional($help->user)->phone }}" class="p-1.5 bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                            </a>
                                        @endif
                                        <a href="{{ route('mitra.chat', ['help' => $help->id]) }}" class="p-1.5 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    </div>
</div>