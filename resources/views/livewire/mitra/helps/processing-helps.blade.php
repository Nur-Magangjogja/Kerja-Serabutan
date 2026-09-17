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
        <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-[#0098e7] rounded-b-2xl shadow-sm text-white">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>
            
            <div class="relative z-10">
                <div class="relative flex items-center justify-center min-h-[40px] text-white">
                    <div class="text-center w-full min-w-0 px-12">
                        <h1 class="text-base font-bold truncate">Bantuan Sedang Diproses</h1>
                        <p class="text-xs text-white font-medium truncate mt-0.5">Kelola pekerjaan yang sedang berjalan</p>
                    </div>

                    <div class="absolute right-0 top-1/2 -translate-y-1/2 z-20 flex items-center">
                        <x-mitra.notification-icon />
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
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $help->status_color }}">
                                        <span>{{ $help->progress_icon }}</span>
                                        <span>{{ $help->progress_summary }}</span>
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ optional($help->taken_at)->diffForHumans() ?? optional($help->created_at)->diffForHumans() }}</span>
                                </div>

                                <!-- Progress Track -->
                                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden mb-3">
                                    <div class="h-full rounded-full transition-all duration-500 {{ $help->progress_percentage == 100 ? 'bg-emerald-500' : 'bg-blue-600 dark:bg-blue-500' }}"
                                         style="width: {{ $help->progress_percentage }}%;"></div>
                                </div>

                                <p class="text-xs text-gray-600 line-clamp-2 mb-3">{{ Str::limit($help->description ?? $help->location ?? '-', 100) }}</p>

                                @if($help->isScheduled())
                                    <div class="text-xs text-blue-600 dark:text-blue-400 font-semibold mb-2">📅 Terjadwal: {{ \Carbon\Carbon::parse($help->scheduled_at ?? $help->service_scheduled_at)->locale('id')->translatedFormat('d M Y, H:i') }} WIB</div>
                                @else
                                    <div class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mb-2">⚡ Segera</div>
                                @endif

                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-xs text-gray-500">👤 {{ optional($help->user)->name ?? 'Customer' }}</span>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('mitra.helps.detail', $help->id) }}" wire:navigate class="px-3 py-1.5 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600 transition">
                                            Detail
                                        </a>
                                        @if(optional($help->user)->phone)
                                            @php
                                                $rawPhone = optional($help->user)->phone ?? '';
                                                $waPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                                                if (str_starts_with($waPhone, '0')) {
                                                    $waPhone = '62' . substr($waPhone, 1);
                                                } elseif (str_starts_with($waPhone, '8')) {
                                                    $waPhone = '62' . $waPhone;
                                                }
                                            @endphp
                                            <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener" class="p-1.5 bg-emerald-50 text-emerald-600 rounded-md hover:bg-emerald-100 transition" title="Chat WhatsApp">
                                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                                    <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2zm.01 1.67c2.2 0 4.26.86 5.82 2.42a8.225 8.225 0 012.41 5.83c0 4.54-3.7 8.24-8.24 8.24-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.196 8.196 0 01-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24zm4.52 11.45c-.25-.13-1.47-.72-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.13-1.06-.39-2.02-1.24-.74-.66-1.24-1.48-1.39-1.73-.14-.25-.02-.39.11-.51.11-.11.25-.29.37-.44.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.34-.76-1.84-.2-.49-.4-.42-.56-.43h-.47c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1 0 1.24.9 2.44 1.03 2.61.13.17 1.77 2.71 4.3 3.8.6.26 1.07.41 1.44.53.61.19 1.16.17 1.6.1.49-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.15-1.18-.06-.11-.23-.17-.48-.29z"/>
                                                </svg>
                                            </a>
                                        @endif
                                        <a href="{{ route('mitra.chat', ['help' => $help->id]) }}" wire:navigate class="p-1.5 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
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