<div class="min-h-screen bg-white">
    <style>
        :root{
            --brand-500: #0ea5a4;
            --brand-600: #08979a;
            --muted-600: #6b7280;
        }

        [x-cloak] { display: none !important; }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        /* Hide scrollbar for IE, Edge and Firefox */
        .scrollbar-hide {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
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
                        <h1 class="text-lg font-bold">Permintaan Saya</h1>
                        <p class="text-xs text-white/90 mt-0.5">Kelola permintaan bantuan Anda</p>
                    </div>

                    <div class="flex items-center gap-2">
                        @include('components.notification-icon', ['route' => route('customer.notifications.index')])
                    </div>
                </div>

                <!-- Filter Tabs - horizontal scrollable -->
                <div class="flex gap-2 mt-1 overflow-x-auto pb-2 -mx-5 px-5 scrollbar-hide">
                    <button type="button" wire:click="$set('statusFilter', 'menunggu_mitra')" role="tab"
                        class="px-4 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all {{ $statusFilter === 'menunggu_mitra' ? 'bg-white text-[#0098e7] shadow-md' : 'bg-white/20 text-white' }}">
                        Menunggu Mitra
                    </button>
                    <button type="button" wire:click="$set('statusFilter', 'diproses')" role="tab"
                        class="px-4 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all {{ $statusFilter === 'diproses' ? 'bg-white text-[#0098e7] shadow-md' : 'bg-white/20 text-white' }}">
                        Diproses
                    </button>
                    <button type="button" wire:click="$set('statusFilter', 'waiting_customer_confirmation')" role="tab"
                        class="px-4 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all {{ $statusFilter === 'waiting_customer_confirmation' ? 'bg-white text-[#0098e7] shadow-md' : 'bg-white/20 text-white' }}">
                        Menunggu Konfirmasi
                    </button>
                    <button type="button" wire:click="$set('statusFilter', 'selesai')" role="tab"
                        class="px-4 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all {{ $statusFilter === 'selesai' ? 'bg-white text-[#0098e7] shadow-md' : 'bg-white/20 text-white' }}">
                        Selesai
                    </button>
                </div>
            </div>

            <!-- Curved separator (SVG) to create non-flat divider into content -->
            <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 72" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,32 C360,72 1080,0 1440,40 L1440,72 L0,72 Z" fill="#ffffff"></path>
            </svg>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-t-3xl -mt-6 px-5 pt-6 pb-6 min-h-[60vh]"> 
            <div class="space-y-4">
                {{-- Loading skeleton --}}
                <div wire:loading class="space-y-3">
                    @for($i=0;$i<4;$i++)
                        <div class="bg-gray-50 rounded-2xl p-4 card-shadow animate-pulse">
                            <div class="flex items-center gap-3">
                                <div class="w-16 h-16 bg-gray-200 rounded-lg"></div>
                                <div class="flex-1">
                                    <div class="h-4 bg-gray-200 rounded w-3/5 mb-2"></div>
                                    <div class="h-3 bg-gray-200 rounded w-4/5"></div>
                                </div>
                                <div class="w-16 h-4 bg-gray-200 rounded"></div>
                            </div>
                        </div>
                    @endfor
                </div>

                {{-- List based on filter --}}
                @forelse($helps as $help)
                    @if($statusFilter === 'menunggu_mitra')
                        {{-- Simple Menunggu Mitra Card --}}
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

                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background: rgba(255, 159, 67, 0.08); color:#ff8a00; border:1px solid rgba(255,159,67,0.12);">
                                            @if($help->status === 'menunggu_mitra')
                                                Menunggu Mitra
                                            @else
                                                {{ ucfirst(str_replace('_',' ', $help->status)) }}
                                            @endif
                                        </span>
                                        <span class="text-xs text-gray-400">{{ optional($help->created_at)->diffForHumans() }}</span>
                                    </div>

                                    <p class="text-xs text-gray-600 line-clamp-2 mb-3">{{ Str::limit($help->description, 100) }}</p>
                                    @if($help->scheduled_at)
                                        <div class="text-xs text-gray-500 mb-2">📅 {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }}</div>
                                    @endif

                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500">📍 {{ $help->city->name ?? '-' }}</span>
                                        <div class="flex-1"></div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('customer.helps.detail', $help->id) }}" class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md text-xs hover:bg-gray-200 transition">Detail</a>
                                            <button type="button" wire:click.stop="editHelp({{ $help->id }})" class="px-3 py-1.5 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600 transition">Edit</button>
                                            <button type="button" wire:click.stop="confirmDelete({{ $help->id }})" class="px-3 py-1.5 bg-red-50 text-red-600 rounded-md text-xs hover:bg-red-100 transition">Batalkan</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($statusFilter === 'diproses')
                        {{-- Diproses: tampilkan semua bantuan dalam proses (query sudah filter) --}}
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

                                    @if($help->status === 'partner_cancel_requested')
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background: rgba(250,204,21,0.08); color:#b45309; border:1px solid rgba(245,158,11,0.12);">
                                                Menunggu Konfirmasi Pembatalan
                                            </span>
                                            <span class="text-xs text-gray-400">{{ optional($help->partner_cancel_requested_at ?? $help->created_at)->diffForHumans() }}</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background: rgba(56,189,248,0.08); color:#0284c7; border:1px solid rgba(3,105,161,0.08);">
                                                @if($help->status === 'memperoleh_mitra')
                                                    Mitra Ditemukan
                                                @elseif($help->status === 'taken')
                                                    Diambil Mitra
                                                @elseif($help->status === 'partner_on_the_way')
                                                    Rekan Jasa Menuju Lokasi
                                                @elseif($help->status === 'partner_arrived')
                                                    Rekan Jasa Tiba di Lokasi
                                                @elseif(in_array($help->status, ['in_progress', 'sedang_diproses']))
                                                    Sedang Dikerjakan
                                                @elseif($help->status === 'waiting_customer_confirmation')
                                                    Menunggu Konfirmasi Anda
                                                @else
                                                    Sedang Diproses
                                                @endif
                                            </span>
                                            <span class="text-xs text-gray-400">{{ optional($help->created_at)->diffForHumans() }}</span>
                                        </div>
                                    @endif

                                    <p class="text-xs text-gray-600 line-clamp-2 mb-3">{{ Str::limit($help->description, 100) }}</p>
                                    @if($help->scheduled_at)
                                        <div class="text-xs text-gray-500 mb-2">📅 {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }}</div>
                                    @endif

                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500">📍 {{ $help->city->name ?? '-' }}</span>
                                        <div class="flex-1"></div>
                                        <div class="flex items-center gap-2">
                                            @if($help->mitra)
                                                <div class="flex items-center gap-1.5">
                                                    <div class="w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </div>
                                                    <span class="text-xs text-gray-700 font-medium">{{ $help->mitra->name }}</span>
                                                </div>
                                            @endif

                                            @if($help->chat_messages_count > 0)
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-50 text-xs text-gray-600">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    {{ $help->chat_messages_count }}
                                                </span>
                                            @endif

                                            <a href="{{ route('customer.chat', $help->id) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-white hover:shadow-md transition" style="background: linear-gradient(135deg, #0098e7 0%, #00b8d4 100%);" aria-label="Buka chat">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4-.8L3 20l1.2-4A7.963 7.963 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="mt-3 flex items-center justify-end gap-2">
                                        <a href="{{ route('customer.helps.detail', $help->id) }}" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200 transition w-32 text-center">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($statusFilter === 'waiting_customer_confirmation')
                        {{-- Menunggu Konfirmasi: bantuan yang mitra sudah tandai selesai dan menunggu konfirmasi customer --}}
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

                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background: rgba(255, 159, 67, 0.08); color:#ff8a00; border:1px solid rgba(255,159,67,0.12);">
                                            Menunggu Konfirmasi Anda
                                        </span>
                                        <span class="text-xs text-gray-400">{{ optional($help->service_completed_at)->diffForHumans() }}</span>
                                    </div>

                                    <p class="text-xs text-gray-600 line-clamp-2 mb-3">{{ Str::limit($help->description, 100) }}</p>
                                    @if($help->scheduled_at)
                                        <div class="text-xs text-gray-500 mb-2">📅 {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }}</div>
                                    @endif

                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500">📍 {{ $help->city->name ?? '-' }}</span>
                                        <div class="flex-1"></div>
                                        <div class="flex items-center gap-2">
                                            @if($help->mitra)
                                                <div class="flex items-center gap-1.5">
                                                    <div class="w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </div>
                                                    <span class="text-xs text-gray-700 font-medium">{{ $help->mitra->name }}</span>
                                                </div>
                                            @endif

                                            <a href="{{ route('customer.chat', $help->id) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-white hover:shadow-md transition" style="background: linear-gradient(135deg, #0098e7 0%, #00b8d4 100%);" aria-label="Buka chat">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4-.8L3 20l1.2-4A7.963 7.963 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="mt-3 flex items-center justify-end gap-2">
                                        <button type="button" wire:click.stop="confirmCompletion({{ $help->id }})" class="px-5 py-2 bg-emerald-500 text-white rounded-lg text-sm font-semibold hover:bg-emerald-600 transition w-32">
                                            Konfirmasi
                                        </button>

                                        <a href="{{ route('customer.helps.detail', $help->id) }}" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200 transition w-32 text-center">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif($statusFilter === 'selesai')
                        {{-- Completed (selesai) Card - History Style with Expandable Detail --}}
                        @php
                            $hasRatedThis = \App\Models\Rating::hasRated($help->id, auth()->id(), 'customer_to_mitra');
                        @endphp
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
                                    </div>

                                    <div class="text-right flex-shrink-0">
                                        <div class="text-sm font-bold" style="color: #0098e7;">Rp {{ number_format($help->amount ?? 0, 0, ',', '.') }}</div>
                                        <div class="flex flex-col gap-1 mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                                Selesai
                                            </span>
                                            @if($hasRatedThis)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                    Sudah Rating
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs font-semibold">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                    </svg>
                                                    Belum Rating
                                                </span>
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

                                        @if($help->mitra)
                                            <div>
                                                <div class="text-xs text-gray-500 mb-1">Mitra</div>
                                                <div class="text-sm text-gray-900">{{ $help->mitra->name }}</div>
                                                @if($help->mitra->phone)
                                                    <a href="tel:{{ $help->mitra->phone }}" class="text-xs font-semibold" style="color: #0098e7;">{{ $help->mitra->phone }}</a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        Selesai pada: <span class="text-gray-700 font-medium">{{ optional($help->updated_at)->format('d M Y H:i') }}</span>
                                    </div>

                                    {{-- Rating Section --}}
                                    @php
                                        $hasRated = \App\Models\Rating::hasRated($help->id, auth()->id(), 'customer_to_mitra');
                                    @endphp

                                    @if($help->mitra && !$hasRated)
                                        <div class="pt-3 border-t border-gray-100">
                                            @livewire('customer.rate-mitra', ['helpId' => $help->id], key('rate-mitra-'.$help->id))
                                        </div>
                                    @elseif($hasRated)
                                        @php
                                            $userRating = \App\Models\Rating::where('help_id', $help->id)
                                                ->where('rater_id', auth()->id())
                                                ->where('type', 'customer_to_mitra')
                                                ->first();
                                        @endphp
                                        @if($userRating)
                                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="text-sm font-semibold text-gray-700">Rating Anda:</span>
                                                    <div class="flex items-center gap-1">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <svg class="w-4 h-4 {{ $i <= $userRating->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                            </svg>
                                                        @endfor
                                                    </div>
                                                </div>
                                                @if($userRating->review)
                                                    <p class="text-sm text-gray-600 italic mt-1">"{{ $userRating->review }}"</p>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Tab "Semua": Render card based on actual help status --}}
                        @if($help->status === 'menunggu_mitra')
                            {{-- Menunggu Mitra Card --}}
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

                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background: rgba(255, 159, 67, 0.08); color:#ff8a00; border:1px solid rgba(255,159,67,0.12);">
                                                Menunggu Mitra
                                            </span>
                                            <span class="text-xs text-gray-400">{{ optional($help->created_at)->diffForHumans() }}</span>
                                        </div>

                                        <p class="text-xs text-gray-600 line-clamp-2 mb-3">{{ Str::limit($help->description, 100) }}</p>

                                        <div class="flex items-center gap-3">
                                            <span class="text-xs text-gray-500">📍 {{ $help->city->name ?? '-' }}</span>
                                            <div class="flex-1"></div>
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('customer.helps.detail', $help->id) }}" class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md text-xs hover:bg-gray-200 transition">Detail</a>
                                                <button type="button" wire:click.stop="editHelp({{ $help->id }})" class="px-3 py-1.5 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600 transition">Edit</button>
                                                <button type="button" wire:click.stop="confirmDelete({{ $help->id }})" class="px-3 py-1.5 bg-red-50 text-red-600 rounded-md text-xs hover:bg-red-100 transition">Batalkan</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($help->status === 'memperoleh_mitra')
                            {{-- Diproses Card --}}
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

                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background: rgba(56,189,248,0.08); color:#0284c7; border:1px solid rgba(3,105,161,0.08);">
                                                Sedang Diproses
                                            </span>
                                            <span class="text-xs text-gray-400">{{ optional($help->created_at)->diffForHumans() }}</span>
                                        </div>

                                        <p class="text-xs text-gray-600 line-clamp-2 mb-3">{{ Str::limit($help->description, 100) }}</p>

                                        <div class="flex items-center gap-3">
                                            <span class="text-xs text-gray-500">📍 {{ $help->city->name ?? '-' }}</span>
                                            <div class="flex-1"></div>
                                            <div class="flex items-center gap-2">
                                                @if($help->mitra)
                                                    <div class="flex items-center gap-1.5">
                                                        <div class="w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center">
                                                            <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                                            </svg>
                                                        </div>
                                                        <span class="text-xs text-gray-700 font-medium">{{ $help->mitra->name }}</span>
                                                    </div>
                                                @endif

                                                @if($help->chat_messages_count > 0)
                                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-50 text-xs text-gray-600">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                        </svg>
                                                        {{ $help->chat_messages_count }}
                                                    </span>
                                                @endif

                                                <a href="{{ route('customer.chat', $help->id) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-white hover:shadow-md transition" style="background: linear-gradient(135deg, #0098e7 0%, #00b8d4 100%);" aria-label="Buka chat">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4-.8L3 20l1.2-4A7.963 7.963 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>

                                        <div class="mt-3 flex items-center justify-end gap-2">
                                            <button type="button" wire:click.stop="confirmCompletion({{ $help->id }})" class="px-5 py-2 bg-emerald-500 text-white rounded-lg text-sm font-semibold hover:bg-emerald-600 transition w-32">
                                                Selesai
                                            </button>

                                            <a href="{{ route('customer.helps.detail', $help->id) }}" class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200 transition w-32 text-center">
                                                Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($help->status === 'selesai')
                            {{-- Selesai Card with Rating --}}
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
                                            <span class="text-xs font-bold whitespace-nowrap text-green-600">Rp {{ number_format($help->amount, 0, ',', '.') }}</span>
                                        </div>

                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                                ✓ Selesai
                                            </span>
                                            <span class="text-xs text-gray-400">{{ optional($help->completed_at)->diffForHumans() ?? optional($help->created_at)->diffForHumans() }}</span>
                                        </div>

                                        <p class="text-xs text-gray-600 line-clamp-1 mb-2">{{ Str::limit($help->description, 100) }}</p>

                                        @if($help->mitra)
                                            <div class="flex items-center gap-1.5 mb-2">
                                                <div class="w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                                <span class="text-xs text-gray-700 font-medium">{{ $help->mitra->name }}</span>
                                            </div>
                                        @endif

                                        @php
                                            $userRating = $help->rating ?? null;
                                            $hasRated = $userRating && $userRating->rating > 0;
                                        @endphp

                                        @if($hasRated)
                                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-3">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <span class="text-sm font-semibold text-gray-700">Rating Anda:</span>
                                                    <div class="flex items-center gap-1">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <svg class="w-5 h-5 {{ $i <= $userRating->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                            </svg>
                                                        @endfor
                                                    </div>
                                                </div>
                                                @if($userRating->review)
                                                    <p class="text-sm text-gray-600 italic">"{{ $userRating->review }}"</p>
                                                @endif
                                            </div>
                                        @else
                                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                                                <p class="text-sm font-semibold text-gray-700 mb-2">Beri Rating:</p>
                                                <div class="flex items-center gap-2 mb-3">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <button type="button" wire:click="setRating({{ $help->id }}, {{ $i }})" class="focus:outline-none transition transform hover:scale-110">
                                                            <svg class="w-6 h-6 {{ ($pendingHelpForRating === $help->id && $pendingRating >= $i) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                            </svg>
                                                        </button>
                                                    @endfor
                                                </div>

                                                @if($pendingHelpForRating === $help->id && $pendingRating)
                                                    <textarea wire:model.defer="ratingComment" rows="3" placeholder="Tulis ulasan Anda (opsional)" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-blue-400 focus:ring-1 focus:ring-blue-200 resize-none mb-3"></textarea>
                                                    <button type="button" wire:click="submitRating({{ $help->id }})" class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg text-sm font-semibold hover:bg-blue-600 transition">
                                                        Kirim Rating
                                                    </button>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500">📍 {{ $help->city->name ?? '-' }}</span>
                                            <div class="flex-1"></div>
                                            <a href="{{ route('customer.helps.detail', $help->id) }}" class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-xs font-medium hover:bg-gray-200 transition">
                                                Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                @empty
                    <div class="text-center py-10 bg-white rounded-xl shadow-sm">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <p class="text-sm font-semibold text-gray-700">Belum ada permintaan</p>
                        <p class="text-xs text-gray-500 mt-1">Buat permintaan baru dengan menekan tombol <span class="font-semibold">Tambah</span></p>
                    </div>
                @endforelse

                <div class="mt-4">{{ $helps->links('vendor.pagination.custom') }}</div>
            </div>
        </div>
    {{-- </div> --}}

    <!-- Helper: hide scrollbars for modals -->
    <style>
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
    </style>

    <!-- Edit Modal (Bottom Sheet) -->
    @if(isset($editingHelp) && $editingHelp)
        <div class="fixed inset-0 z-50 flex items-end justify-center" style="background: rgba(0,0,0,0.5);" wire:click="closeEdit">
            <div class="bg-white rounded-t-3xl w-full max-w-md shadow-2xl max-h-[85vh] overflow-y-auto hide-scrollbar" wire:click.stop>
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white border-b px-5 py-4 rounded-t-3xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Edit Permintaan</h3>
                        <button type="button" wire:click="closeEdit" class="p-2 hover:bg-gray-100 rounded-full transition">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <form wire:submit.prevent="saveEdit" class="p-5 pb-24 space-y-3">
                    <!-- Title -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            
                    </div> 

                    <!-- Location (Short Address) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                </svg>
                                Lokasi
                                <span class="text-gray-400 text-xs ml-1">(Opsional)</span>
                            </span>
                        </label>
                        <input type="text" wire:model.defer="editLocation" 
                            placeholder="Contoh: Jl. Merdeka No. 123, RT 01/RW 05"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border-2 border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-200 transition bg-white shadow-sm">
                        <p class="text-xs text-gray-500 mt-1.5 flex items-center">
                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Alamat singkat, contoh: nama jalan, RT/RW
                        </p>
                        @error('editLocation') 
                            <span class="text-red-500 text-xs mt-1.5 block flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Full Address -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                                </svg>
                                Alamat Lengkap
                                <span class="text-gray-400 text-xs ml-1">(Opsional)</span>
                            </span>
                        </label>
                        <textarea wire:model.defer="editFullAddress" rows="3"
                            placeholder="Contoh: Jl. Merdeka No. 123, RT 01/RW 05, Kelurahan Mangkang, Kecamatan Semarang Barat, dekat Indomaret, rumah cat hijau"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border-2 border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-200 transition resize-none bg-white shadow-sm"></textarea>
                        <p class="text-xs text-gray-500 mt-1.5 flex items-center">
                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Alamat lengkap dengan patokan agar mudah ditemukan
                        </p>
                        @error('editFullAddress') 
                            <span class="text-red-500 text-xs mt-1.5 block flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- City select -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5z" />
                                </svg>
                                Kota
                                <span class="text-gray-400 text-xs ml-1">(Wajib)</span>
                            </span>
                        </label>
                        <div>
                            <select wire:model.defer="editCityId" class="w-full px-3.5 py-2.5 text-sm rounded-xl border-2 border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-200 transition bg-white shadow-sm">
                                <option value="">Pilih kota atau kabupaten</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}@if($city->province), {{ $city->province }}@endif</option>
                                @endforeach
                            </select>
                        </div>
                        @error('editCityId')
                            <span class="text-red-500 text-xs mt-1.5 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Map Location Picker -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                                </svg>
                                Tandai Lokasi di Peta
                                <span class="text-gray-400 text-xs ml-1">(Opsional)</span>
                            </span>
                        </label>
                        <div id="edit-map" class="w-full h-48 rounded-xl border-2 border-gray-200 shadow-sm mb-2"></div>

                        <!-- Koordinat Display -->
                        <div id="edit-coordinates-display"
                            class="bg-green-50 border border-green-200 rounded-lg p-3 mb-2 hidden">
                            <p class="text-xs font-semibold text-green-800 mb-1">Lokasi Ditandai:</p>
                            <p class="text-xs text-green-900">
                                Latitude: <span id="edit-lat-display" class="font-mono">-</span>,
                                Longitude: <span id="edit-lng-display" class="font-mono">-</span>
                            </p>
                        </div>

                        <!-- Hidden inputs for Livewire -->
                        <input type="hidden" wire:model.defer="editLatitude" id="edit-latitude-input">
                        <input type="hidden" wire:model.defer="editLongitude" id="edit-longitude-input">

                        <p class="text-xs text-gray-500 mt-1.5 flex items-center">
                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Klik pada peta untuk menandai titik lokasi bantuan
                        </p>

                        @error('editLatitude')
                            <span class="text-red-500 text-xs mt-1.5 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                Deskripsi Bantuan
                                <span class="text-red-500 ml-1">*</span>
                            </span>
                        </label>
                        <textarea wire:model.defer="editDescription" rows="4"
                            placeholder="Jelaskan detail kebutuhan bantuan Anda secara lengkap..."
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border-2 border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-200 transition resize-none bg-white shadow-sm"></textarea>
                        @error('editDescription') 
                            <span class="text-red-500 text-xs mt-1.5 block flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Equipment Provided -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                                </svg>
                                Peralatan yang Sudah Disediakan
                                <span class="text-gray-400 text-xs ml-1">(Opsional)</span>
                            </span>
                        </label>
                        <textarea wire:model.defer="editEquipmentProvided" rows="3"
                            placeholder="Contoh: Sudah ada gerobak dorong, ember besar 2 buah, timbangan digital"
                            class="w-full px-3.5 py-2.5 text-sm rounded-xl border-2 border-gray-200 focus:border-primary-400 focus:ring-2 focus:ring-primary-200 transition resize-none bg-white shadow-sm"></textarea>
                        <p class="text-xs text-gray-500 mt-1.5 flex items-center">
                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Tuliskan alat atau peralatan yang sudah Anda sediakan untuk membantu mitra
                        </p>
                        @error('editEquipmentProvided') 
                            <span class="text-red-500 text-xs mt-1.5 block flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <!-- Photo -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            <span class="flex items-center">
                                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                </svg>
                                Foto Pendukung
                                <span class="text-gray-400 text-xs ml-1">(Opsional)</span>
                            </span>
                        </label>
                        <div class="relative">
                            <input type="file" wire:model="editPhoto" accept="image/*" id="edit-photo-input" class="hidden">
                            <label for="edit-photo-input"
                                class="flex flex-col items-center justify-center w-full h-28 rounded-xl border-2 border-dashed border-gray-300 hover:border-primary-400 cursor-pointer transition bg-white shadow-sm hover:bg-gray-50">
                                <svg class="w-5 h-5 text-gray-400 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="text-xs font-medium text-gray-600">Pilih atau ambil foto</span>
                                <span class="text-xs text-gray-400 mt-1">Klik untuk upload gambar</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5 flex items-center">
                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            Maksimal 2MB. Format: JPG, PNG, JPEG
                        </p>
                        @error('editPhoto') 
                            <span class="text-red-500 text-xs mt-1.5 block flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </span>
                        @enderror

                        @if ($editPhoto)
                            <div class="mt-2 relative">
                                <img src="{{ $editPhoto->temporaryUrl() }}" class="w-full h-40 object-cover rounded-xl shadow-md">
                                <button type="button" wire:click="$set('editPhoto', null)"
                                    class="absolute top-2 right-2 bg-red-500 text-white p-1.5 rounded-full shadow-lg hover:bg-red-600 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @elseif ($editExistingPhoto)
                            <div class="mt-2 relative">
                                <img src="{{ asset('storage/' . $editExistingPhoto) }}" class="w-full h-40 object-cover rounded-xl shadow-md">
                                <div class="absolute top-2 right-2 bg-white/90 px-2 py-1 rounded-full text-xs font-medium text-gray-600">
                                    Foto saat ini
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Action Buttons (Sticky Footer) -->
                    <div class="sticky bottom-0 bg-white border-t pt-4 -mx-5 px-5 -mb-5 pb-5 mt-4">
                        <div class="flex gap-3">
                            <button type="button" wire:click="closeEdit" 
                                class="flex-1 px-5 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition">
                                Batal
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 px-5 py-3 text-white rounded-xl font-semibold hover:opacity-90 transition shadow-lg disabled:opacity-50"
                                style="background: linear-gradient(135deg, var(--brand-500), var(--brand-600))">
                                <span wire:loading.remove wire:target="saveEdit">Simpan</span>
                                <span wire:loading wire:target="saveEdit">Menyimpan...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Detail Modal (Bottom Sheet Style - sama dengan Edit Modal) -->
    @if($selectedHelpData)
        <div class="fixed inset-0 z-50 flex items-end justify-center" style="background: rgba(0,0,0,0.5);" wire:click="closeHelp">
            <div class="bg-white rounded-t-3xl w-full max-w-md shadow-2xl max-h-[85vh] overflow-y-auto hide-scrollbar" wire:click.stop>
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white border-b px-5 py-4 rounded-t-3xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Detail Permintaan</h3>
                        <button type="button" wire:click="closeHelp" class="p-2 hover:bg-gray-100 rounded-full transition">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="p-5 pb-6">
                    @if(data_get($selectedHelpData, 'photo'))
                        <div class="mb-4">
                            <img src="{{ asset('storage/' . data_get($selectedHelpData, 'photo')) }}" 
                                alt="Foto bantuan" class="w-full h-48 object-cover rounded-2xl">
                        </div>
                    @endif

                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ data_get($selectedHelpData, 'title') }}</h2>
                    
                    <div class="flex items-center justify-between mb-4 pb-4 border-b">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Nominal Bantuan</p>
                            <p class="text-2xl font-bold" style="color: var(--brand-600)">
                                Rp {{ number_format(data_get($selectedHelpData, 'amount', 0), 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 mb-1">Status</p>
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold"
                                style="background: rgba(14,165,164,0.1); color: var(--brand-600)">
                                {{ ucfirst(data_get($selectedHelpData, 'status')) }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-3 mb-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 mb-1">Deskripsi</p>
                            <p class="text-sm text-gray-700 leading-relaxed">{{ data_get($selectedHelpData, 'description') }}</p>
                        </div>

                        @if(data_get($selectedHelpData, 'location'))
                            <div>
                                <p class="text-xs font-semibold text-gray-500 mb-1">Lokasi</p>
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    <p class="text-sm text-gray-700">{{ data_get($selectedHelpData, 'location') }}</p>
                                </div>
                            </div>
                        @endif

                        @if(data_get($selectedHelpData, 'full_address'))
                            <div>
                                <p class="text-xs font-semibold text-gray-500 mb-1">Alamat Lengkap</p>
                                <p class="text-sm text-gray-700">{{ data_get($selectedHelpData, 'full_address') }}</p>
                            </div>
                            <div class="mt-3">
                                <div id="detail-map" class="w-full h-48 rounded-xl overflow-hidden mb-4"></div>
                            </div>
                        @endif

                        @if(data_get($selectedHelpData, 'equipment_provided'))
                            <div>
                                <p class="text-xs font-semibold text-gray-500 mb-1">Peralatan Disediakan</p>
                                <p class="text-sm text-gray-700">{{ data_get($selectedHelpData, 'equipment_provided') }}</p>
                            </div>
                        @endif

                        @if(data_get($selectedHelpData, 'city_name'))
                            <div>
                                <p class="text-xs font-semibold text-gray-500 mb-1">Kota</p>
                                <p class="text-sm text-gray-700">{{ data_get($selectedHelpData, 'city_name') }}</p>
                            </div>
                        @endif

                        <div>
                            <p class="text-xs font-semibold text-gray-500 mb-1">Dibuat</p>
                            <p class="text-sm text-gray-700">{{ data_get($selectedHelpData, 'created_at_human') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Sticky footer (close button) -->
                <div class="sticky bottom-0 bg-white border-t pt-4 px-5 pb-5">
                    <button type="button" wire:click="closeHelp" 
                        class="w-full px-5 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal (Bottom Sheet) -->
    @if($showDeleteConfirm)
        <div class="fixed inset-0 z-50 flex items-end justify-center" style="background: rgba(0,0,0,0.5);" wire:click="cancelDelete">
            <div class="bg-white rounded-t-3xl w-full max-w-md shadow-2xl max-h-[60vh] overflow-y-auto hide-scrollbar" wire:click.stop>
                <div class="sticky top-0 bg-white border-b px-5 py-4 rounded-t-3xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">Konfirmasi Batalkan</h3>
                        <button type="button" wire:click="cancelDelete" class="p-2 hover:bg-gray-100 rounded-full transition">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-5 pb-24">
                    <p class="text-sm text-gray-700 mb-4">Anda yakin ingin membatalkan permintaan bantuan ini? Tindakan ini tidak dapat dibatalkan.</p>

                    <div class="mb-4">
                        @php
                            $delHelp = null;
                            if($deletingHelpId) {
                                try { $delHelp = \App\Models\Help::find($deletingHelpId); } catch (\Throwable $e) { $delHelp = null; }
                            }
                        @endphp

                        @if($delHelp)
                            <div class="bg-white rounded-2xl p-3 border shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="w-14 h-14 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                                        @if($delHelp->photo)
                                            <img src="{{ asset('storage/' . $delHelp->photo) }}" alt="Foto" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3v5h6v-5c0-1.657-1.343-3-3-3zM6 21v-2a4 4 0 014-4h4a4 4 0 014 4v2" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-gray-900 truncate">{{ $delHelp->title }}</div>
                                        <div class="text-xs text-gray-500 mt-1">Rp {{ number_format($delHelp->amount,0,',','.') }} • {{ optional($delHelp->city)->name }}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex gap-3">
                        <button type="button" wire:click="cancelDelete" class="flex-1 px-5 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition">Batal</button>
                        <button type="button" wire:click="deleteConfirmed" class="flex-1 px-5 py-3 bg-red-600 text-white rounded-xl font-semibold hover:opacity-90 transition">Ya, Batalkan</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

        {{-- Confirmation Modal for Completing Help --}}
    @if($confirmingHelpId)
        <div data-confirm-modal class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click="$set('confirmingHelpId', null)">
            <div class="bg-white rounded-lg shadow-md max-w-sm w-full p-5" wire:click.stop>
                <h3 class="text-lg font-semibold text-gray-800 mb-1 text-center">Tandai sebagai selesai?</h3>
                <p class="text-sm text-gray-500 text-center mb-4">Konfirmasi jika mitra telah menyelesaikan permintaan Anda.</p>

                <div class="flex gap-3">
                    <button wire:click="$set('confirmingHelpId', null)" 
                            class="flex-1 px-4 py-2 border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button wire:click="completeConfirmed" 
                            class="flex-1 px-4 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white rounded-lg hover:from-blue-600 hover:to-cyan-600 transition">
                        Ya, Selesaikan
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Leaflet CSS (if not already loaded) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Leaflet JS (if not already loaded) -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Edit Modal Map Script -->
    <script>
        let editMap = null;
        let editMarker = null;

        // Initialize map when edit modal opens
        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('open-edit', (event) => {
                // Wait for modal to render
                setTimeout(() => {
                    const detail = event.detail || event;
                    const lat = detail.latitude || detail[0]?.latitude;
                    const lng = detail.longitude || detail[0]?.longitude;
                    initEditMap(lat, lng);
                }, 300);
            });
        });

        function initEditMap(lat, lng) {
            // Check if map container exists
            const mapContainer = document.getElementById('edit-map');
            if (!mapContainer) {
                console.log('Map container not found');
                return;
            }

            // Default location (Ponorogo, Jawa Timur)
            const defaultLocation = [-7.8664, 111.4620];
            
            // Parse coordinates to numbers
            const parsedLat = lat ? parseFloat(lat) : null;
            const parsedLng = lng ? parseFloat(lng) : null;
            
            const initialLocation = (parsedLat && parsedLng) ? [parsedLat, parsedLng] : defaultLocation;

            // Destroy existing map if any
            if (editMap) {
                try {
                    editMap.remove();
                } catch(e) {
                    console.log('Error removing map:', e);
                }
                editMap = null;
                editMarker = null;
            }

            // Initialize map
            try {
                editMap = L.map('edit-map').setView(initialLocation, 13);

                // Add OpenStreetMap tiles
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    maxZoom: 19,
                }).addTo(editMap);

                // Add existing marker if coordinates exist
                if (parsedLat && parsedLng) {
                    editMarker = L.marker([parsedLat, parsedLng], {
                        draggable: true
                    }).addTo(editMap);

                    // Update display
                    updateEditCoordinates(parsedLat, parsedLng);

                    // Marker drag event
                    editMarker.on('dragend', function (event) {
                        const position = event.target.getLatLng();
                        updateEditCoordinates(position.lat, position.lng);
                    });
                }

                // Click event on map to place marker
                editMap.on('click', function (e) {
                    const clickLat = e.latlng.lat;
                    const clickLng = e.latlng.lng;

                    // Remove old marker if exists
                    if (editMarker) {
                        editMap.removeLayer(editMarker);
                    }

                    // Add new marker
                    editMarker = L.marker([clickLat, clickLng], {
                        draggable: true
                    }).addTo(editMap);

                    // Update coordinates
                    updateEditCoordinates(clickLat, clickLng);

                    // Marker drag event
                    editMarker.on('dragend', function (event) {
                        const position = event.target.getLatLng();
                        updateEditCoordinates(position.lat, position.lng);
                    });
                });

                // Try to get user's current location
                if (navigator.geolocation && !parsedLat && !parsedLng) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const pos = [position.coords.latitude, position.coords.longitude];
                            editMap.setView(pos, 13);
                        },
                        () => {
                            console.log('Geolocation not available, using default location');
                        }
                    );
                }
            } catch(error) {
                console.error('Error initializing map:', error);
            }
        }

        function updateEditCoordinates(lat, lng) {
            try {
                // Ensure lat and lng are numbers
                const numLat = parseFloat(lat);
                const numLng = parseFloat(lng);

                if (isNaN(numLat) || isNaN(numLng)) {
                    console.error('Invalid coordinates:', lat, lng);
                    return;
                }

                // Update display
                const display = document.getElementById('edit-coordinates-display');
                if (display) {
                    display.classList.remove('hidden');
                    const latDisplay = document.getElementById('edit-lat-display');
                    const lngDisplay = document.getElementById('edit-lng-display');
                    
                    if (latDisplay) latDisplay.textContent = numLat.toFixed(6);
                    if (lngDisplay) lngDisplay.textContent = numLng.toFixed(6);
                }

                // Update Livewire properties
                const latInput = document.getElementById('edit-latitude-input');
                const lngInput = document.getElementById('edit-longitude-input');
                
                if (latInput) {
                    latInput.value = numLat;
                    latInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
                
                if (lngInput) {
                    lngInput.value = numLng;
                    lngInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            } catch(error) {
                console.error('Error updating coordinates:', error);
            }
        }
    </script>

    <!-- Detail Modal Map Script (read-only map and optional geocode fallback) -->
    <script>
        let detailMap = null;
        let detailMarker = null;

        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('open-detail', (event) => {
                // Wait for modal to render
                setTimeout(() => {
                    const detail = event.detail || event;
                    const lat = detail.latitude || detail[0]?.latitude;
                    const lng = detail.longitude || detail[0]?.longitude;
                    const address = detail.full_address || detail[0]?.full_address || '';
                    initDetailMap(lat, lng, address);
                }, 300);
            });
        });

        async function initDetailMap(lat, lng, address) {
            const mapContainer = document.getElementById('detail-map');
            if (!mapContainer) return;

            // Default location
            const defaultLocation = [-7.8664, 111.4620];

            // Parse coordinates
            const parsedLat = lat ? parseFloat(lat) : null;
            const parsedLng = lng ? parseFloat(lng) : null;

            // Remove any existing map
            if (detailMap) {
                try { detailMap.remove(); } catch(e) { console.log('remove detail map error', e); }
                detailMap = null;
                detailMarker = null;
            }

            // If no coords but address exists, try simple Nominatim geocode
            let center = defaultLocation;
            if (parsedLat && parsedLng) {
                center = [parsedLat, parsedLng];
            } else if (address && address.length > 5) {
                try {
                    const resp = await fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(address));
                    const json = await resp.json();
                    if (json && json.length > 0) {
                        center = [parseFloat(json[0].lat), parseFloat(json[0].lon)];
                    }
                } catch(e) {
                    console.log('Geocode failed:', e);
                }
            }

            try {
                detailMap = L.map('detail-map', { zoomControl: true, attributionControl: false }).setView(center, 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(detailMap);

                // Add marker if we have a meaningful center
                if (center) {
                    detailMarker = L.marker(center).addTo(detailMap);
                    const popupText = address ? address : '{{ addslashes(optional(auth()->user())->name ?? '') }}';
                    detailMarker.bindPopup(popupText).openPopup();
                }
            } catch(err) {
                console.error('Error initializing detail map:', err);
            }
        }
    </script>
</div>