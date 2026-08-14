<div class="min-h-screen bg-white">
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

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
    </style>

    <div class="max-w-md mx-auto">
        <!-- Header - BRImo Style -->
        <div class="px-5 pt-5 pb-8 relative overflow-hidden header-pattern" style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
            <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16"></div>
            
            <div class="relative z-10">
                <div class="flex items-center justify-between text-white mb-3">
                    <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <div class="text-center flex-1">
                        <h1 class="text-lg font-bold">Riwayat Bantuan</h1>
                        <p class="text-xs text-white/90 mt-0.5">Bantuan yang telah selesai</p>
                    </div>

                    <div class="flex items-center gap-2">
                        @include('components.notification-icon', ['route' => route('mitra.notifications.index')])
                    </div>
                </div>

                <!-- Navigation Tabs - BRImo Style -->
                <div role="tablist" aria-label="Menu Bantuan" class="flex items-center gap-2 mb-4 overflow-x-auto hide-scrollbar py-2 px-1">
                    <a href="{{ route('mitra.helps.all') }}" 
                        class="inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold transition whitespace-nowrap bg-white/10 text-white/90 hover:bg-white/20">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Tersedia
                    </a>

                    <a href="{{ route('mitra.helps.processing') }}" 
                        class="inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold transition whitespace-nowrap bg-white/10 text-white/90 hover:bg-white/20">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Sedang Dikerjakan
                    </a>

                    <a href="{{ route('mitra.helps.completed') }}" 
                        class="inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold transition whitespace-nowrap bg-white text-[#0098e7] shadow-md">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Selesai
                    </a>
                </div>

                {{-- Stats Cards --}}
                <div class="grid grid-cols-3 gap-2">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center">
                        <div class="text-2xl font-bold text-white">{{ $helps->total() ?? 0 }}</div>
                        <div class="text-xs text-white/80 mt-1">Selesai</div>
                    </div>

                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center">
                        <div class="text-xs text-white/80 mb-1">Total Nilai</div>
                        <div class="text-sm font-bold text-white">Rp {{ number_format($helps->sum('amount') ?? 0, 0, ',', '.') }}</div>
                    </div>

                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 text-center">
                        <div class="text-2xl font-bold text-white">{{ $helps->unique('user_id')->count() ?? 0 }}</div>
                        <div class="text-xs text-white/80 mt-1">Customer</div>
                    </div>
                </div>
            </div>

            <!-- Curved separator -->
            <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 72" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,32 C360,72 1080,0 1440,40 L1440,72 L0,72 Z" fill="#ffffff"></path>
            </svg>
        </div>

        <!-- Main Content -->
        <div class="bg-white rounded-t-3xl -mt-6 px-5 pt-6 pb-24">

                {{-- List --}}
                @if(isset($helps) && $helps->isEmpty())
                    <div class="text-center py-16">
                        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Belum Ada Riwayat</h3>
                        <p class="text-sm text-gray-500 mb-6">Belum ada bantuan yang selesai</p>
                    </div>
                @elseif(isset($helps))
                    <div class="space-y-3">
                        @foreach($helps as $help)
                            <div x-data="{ open: false }" class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                                <div class="p-4">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0 bg-gradient-to-br from-gray-100 to-gray-50">
                                            @if($help->photo)
                                                <img src="{{ asset('storage/' . $help->photo) }}" alt="foto" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-gray-900 truncate">{{ $help->title ?? 'Permintaan Bantuan' }}</h3>
                                            <p class="text-xs text-gray-500 truncate">{{ optional($help->city)->name }} • {{ optional($help->updated_at)->format('d M Y') }}</p>
                                            @if($help->scheduled_at)
                                                <div class="text-xs text-gray-500 mt-1">📅 {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }}</div>
                                            @endif
                                        </div>

                                        <div class="text-right flex-shrink-0">
                                            <div class="text-sm font-bold" style="color: #0098e7;">Rp {{ number_format($help->amount ?? 0, 0, ',', '.') }}</div>
                                            <div class="flex flex-col items-end mt-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    Selesai
                                                </span>
                                                @php
                                                    $customerRatedCard = \App\Models\Rating::hasRated($help->id, auth()->id(), 'mitra_to_customer');
                                                @endphp
                                                @if($customerRatedCard)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-50 text-green-700 text-xs font-medium mt-2">✓ Dinilai</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-700 text-xs font-medium mt-2">Belum dinilai</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <button @click="open = !open" class="w-full flex items-center justify-center gap-2 text-sm font-semibold py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition" style="color: #0098e7;">
                                        <span x-text="open ? 'Sembunyikan Detail' : 'Lihat Detail'"></span>
                                        <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>

                                <div x-show="open" x-cloak x-transition class="px-4 pb-4 border-t border-gray-100 mt-3 pt-4">
                                    <div class="space-y-4">
                                        @if($help->photo)
                                            <img src="{{ asset('storage/' . $help->photo) }}" alt="foto bantuan" class="w-full h-48 object-cover rounded-xl">
                                        @endif

                                        @if($help->description)
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Deskripsi</h4>
                                                <p class="text-sm text-gray-700 leading-relaxed">{{ $help->description }}</p>
                                            </div>
                                        @endif

                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <div class="text-xs text-gray-500 mb-1">Lokasi</div>
                                                <div class="text-sm text-gray-900">{{ $help->full_address ?? optional($help->city)->name ?? '-' }}</div>
                                            </div>

                                            @if($help->user)
                                                <div>
                                                    <div class="text-xs text-gray-500 mb-1">Customer</div>
                                                    <div class="text-sm text-gray-900">{{ $help->user->name }}</div>
                                                    @if($help->user->phone)
                                                        <a href="tel:{{ $help->user->phone }}" class="text-xs font-semibold" style="color: #0098e7;">{{ $help->user->phone }}</a>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        <div class="text-xs text-gray-500">
                                            Selesai pada: <span class="text-gray-700 font-medium">{{ optional($help->updated_at)->format('d M Y H:i') }}</span>
                                        </div>

                                        {{-- Rating form for mitra -> customer (shown inline when not yet rated) --}}
                                        @if($help->user && !\App\Models\Rating::hasRated($help->id, auth()->id(), 'mitra_to_customer'))
                                            <div class="pt-3 border-t border-gray-100">
                                                @livewire('mitra.rate-customer', ['helpId' => $help->id, 'inline' => true], key('mitra-rate-' . $help->id))
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($helps->hasPages())
                        <div class="mt-6">
                            {{ $helps->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-8 text-gray-600">Data riwayat belum tersedia.</div>
                @endif
            </div>
        </div>
    </div>
</div>
