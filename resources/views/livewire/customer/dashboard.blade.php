<div class="min-h-screen bg-gray-100">
    <style>
        :root{
            --brand-500: #0ea5a4;
            --brand-600: #08979a;
            --muted-600: #6b7280;
        }
        .card-shadow { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .card-shadow-hover { box-shadow: 0 4px 12px rgba(0,0,0,0.12); }
        .focus-ring:focus{ outline: none; box-shadow: 0 0 0 3px rgba(14,165,164,0.2); }
        
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

    <!-- Header Section - BRImo Style -->
    <div class="px-5 pt-5 pb-20 relative overflow-hidden header-pattern" style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
        <!-- Decorative circles (like BRImo) -->
        <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16"></div>
        
        <div class="relative z-10">
            <!-- Top Bar -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    @php
                        $__avatar = optional(auth()->user())->selfie_photo ?? optional(auth()->user())->photo ?? optional(auth()->user())->profile_photo_path ?? null;
                    @endphp
                    <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center overflow-hidden ring-2 ring-white/30">
                        <img src="{{ $__avatar ? asset('storage/' . $__avatar) : asset('images/avatar-placeholder.svg') }}" alt="Avatar" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <p class="text-xs text-white/80">Selamat datang</p>
                        <h1 class="text-sm font-bold text-white">{{ optional(auth()->user())->name ?? 'Pengguna' }}</h1>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @include('components.notification-icon', ['route' => route('customer.notifications.index'), 'class' => 'bg-white/10 backdrop-blur-sm p-2 rounded-lg hover:bg-white/20 transition'])
                </div>
            </div>

            <!-- Header Content - Info Section -->
            <div class="mt-4 mb-2">
                <h2 class="text-white text-lg font-bold mb-1">Dashboard Saya</h2>
                <p class="text-white/80 text-sm leading-relaxed">Kelola permintaan bantuan dan transaksi Anda dengan mudah</p>
                
                <!-- Quick Stats -->
                {{-- <div class="flex items-center gap-4 mt-4">
                    <div class="flex items-center gap-2 text-white/90">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-xs font-medium">{{ $availableHelps->count() }} Bantuan</span>
                    </div>
                    <div class="flex items-center gap-2 text-white/90">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-xs font-medium">Saldo Aktif</span>
                    </div>
                    <div class="flex items-center gap-2 text-white/90">
                        <a href="{{ route('customer.chat') }}" class="inline-flex items-center gap-2 text-white/90 hover:text-white/100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m-5 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1l-4 4z" />
                            </svg>
                            <span class="text-xs font-medium">Chat</span>
                        </a>
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
    <br>

    <!-- Small account info bar placed above the balance card (over header) -->
    <div class="px-5 -mt-10 relative z-20">
        <div class="max-w-full">
            <div class="inline-flex items-center gap-3 bg-white/10 backdrop-blur-sm text-white text-sm px-3 py-1 rounded-full shadow-sm">
                <svg class="w-4 h-4 text-white/90" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6c0 4.5 6 10 6 10s6-5.5 6-10a6 6 0 00-6-6z"/></svg>
                <span>{{ optional(optional(auth()->user())->city)->name ?? (auth()->user()->city ?? '-') }}</span>
                <span class="opacity-60">•</span>
                <span>Member sejak {{ optional(auth()->user())->created_at ? optional(auth()->user())->created_at->format('M Y') : '-' }}</span>
            </div>
        </div>
    </div>

    <!-- Balance Card - BRImo Style (overlapping header) -->
    <div class="px-5 -mt-20 relative z-20">
        <div class="bg-white rounded-2xl p-4 shadow-xl">
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1">
                    <p class="text-xs text-gray-500 mb-1">Total Saldo</p>
                    

                    <div class="flex items-center gap-2" x-data="{ show: false }">
                        <h2 class="text-2xl font-bold text-gray-900" x-show="show">
                            Rp {{ number_format($balance ?? 0, 0, ',', '.') }}
                        </h2>
                        <h2 class="text-2xl font-bold text-gray-900" x-show="!show">Rp ••••••</h2>
                        <button @click="show = !show" class="p-1.5 hover:bg-gray-100 rounded-lg transition">
                            <svg class="w-4 h-4 text-gray-500" x-show="!show" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="w-4 h-4 text-gray-500" x-show="show" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>
                <a href="{{ route('customer.topup.request') }}" class="text-white px-4 py-2 rounded-lg text-xs font-semibold transition shadow-md" style="background: #0098e7;">
                    + Top Up
                </a>
            </div>

            <!-- Quick Actions Row -->
            <div class="border-t pt-3 mt-3">
                <div class="flex items-center justify-between gap-2">
                    <a href="{{ route('customer.helps.create') }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-gray-50 transition">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(0, 152, 231, 0.1);">
                            <svg class="w-5 h-5" style="color: #0098e7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-medium text-gray-700 text-center">Buat</span>
                    </a>
                    
                    <a href="{{ route('customer.helps.index', ['statusFilter' => 'selesai']) }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-gray-50 transition">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(0, 152, 231, 0.1);">
                            <svg class="w-5 h-5" style="color: #0098e7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-medium text-gray-700 text-center">Bantuan</span>
                    </a>

                    <a href="{{ route('customer.helps.history') }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-gray-50 transition">
                        <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-medium text-gray-700 text-center">Riwayat</span>
                    </a>

                    {{-- Profil quick-action removed per request --}}
                    <a href="{{ route('customer.chat') }}" class="flex flex-col items-center gap-1.5 p-2 rounded-xl hover:bg-gray-50 transition">
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(0, 152, 231, 0.1);">
                                <svg class="w-5 h-5" style="color: #0098e7;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m-5 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1l-4 4z" />
                                </svg>
                            </div>
                            @if(!empty($unreadChatCount) && $unreadChatCount > 0)
                                <span class="absolute -top-1 -right-2 inline-flex items-center justify-center text-[10px] font-semibold bg-red-500 text-white rounded-full px-1.5 py-0.5 shadow">{{ $unreadChatCount > 99 ? '99+' : $unreadChatCount }}</span>
                            @endif
                        </div>
                        <span class="text-[10px] font-medium text-gray-700 text-center">Chat</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="px-5 pt-5 pb-6">
        <!-- Banner Section -->
        @php
            $customerBanners = json_decode((string) \App\Models\AppSetting::get('banner_customer', '[]'), true) ?: [];
        @endphp
        @if(!empty($customerBanners) && count($customerBanners))
            <div class="mb-5">
                <div class="rounded-xl overflow-hidden shadow-md">
                    <div class="relative h-36 overflow-hidden">
                        <div class="flex h-full will-change-transform customer-banner-slides"
                            style="transition: transform 700ms cubic-bezier(.2,.9,.2,1);">
                            @foreach($customerBanners as $b)
                                <div class="flex-shrink-0 w-full h-full">
                                    <img src="{{ asset('storage/' . $b) }}" alt="Banner" class="w-full h-full object-cover" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="mb-5">
                <div id="promo-banner" class="rounded-xl overflow-hidden shadow-md">
                    <div class="relative h-36 overflow-hidden" style="background: linear-gradient(to right, #0098e7, #0077cc);">
                        <div id="promo-track" class="flex h-full transition-transform duration-700 ease-in-out"></div>
                    </div>
                </div>
                <div id="promo-dots" class="flex justify-center mt-3 gap-2">
                    <button data-dot="0" class="w-2 h-2 rounded-full transition-all" style="background: #0098e7;"></button>
                    <button data-dot="1" class="w-2 h-2 rounded-full bg-gray-300 transition-all"></button>
                    <button data-dot="2" class="w-2 h-2 rounded-full bg-gray-300 transition-all"></button>
                </div>
            </div>
        @endif

        <!-- Bantuan Saya Section -->
        <div class="mb-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-bold text-gray-900">Bantuan Saya</h2>
                <a href="{{ route('customer.helps.index') }}" class="text-xs font-semibold" style="color: #0098e7;">Lihat Semua →</a>
            </div>

            <div class="space-y-3">
                <div wire:loading class="space-y-3">
                    @for($i=0;$i<3;$i++)
                        <div class="bg-white rounded-xl p-3 shadow-sm animate-pulse">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gray-200 rounded-lg"></div>
                                <div class="flex-1">
                                    <div class="h-3 bg-gray-200 rounded w-3/5 mb-2"></div>
                                    <div class="h-2.5 bg-gray-200 rounded w-4/5"></div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>

                @if($activeTab !== 'history')
                    @php
                        // Only show helps that are waiting for a mitra (include legacy status names)
                        $waitingHelps = collect($availableHelps)->filter(function($h) {
                            return in_array($h->status, ['mencari_mitra', 'menunggu_mitra', 'memperoleh_mitra', 'taken']);
                        });
                    @endphp
                    @forelse($waitingHelps as $help)
                        <a href="{{ route('customer.helps.detail', $help->id) }}"
                            class="block w-full text-left bg-white rounded-xl p-3.5 shadow-sm hover:shadow-md transition-all">
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
                                    <p class="text-xs text-gray-600 line-clamp-1 mb-1.5">{{ $help->description }}</p>
                                    @if($help->scheduled_at)
                                        <div class="text-xs text-gray-500 mb-1">📅 {{ \Carbon\Carbon::parse($help->scheduled_at)->translatedFormat('d M Y, H:i') }}</div>
                                    @endif
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500">📍 {{ $help->city->name ?? '-' }}</span>
                                        <span class="text-xs text-gray-400">{{ $help->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-10 bg-white rounded-xl shadow-sm">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <p class="text-sm font-semibold text-gray-700">Tidak ada permintaan yang menunggu mitra</p>
                            <p class="text-xs text-gray-500 mt-1">Buat permintaan baru dengan klik tombol <span class="font-semibold">Buat</span></p>
                        </div>
                    @endforelse
                @endif

                @if(method_exists($availableHelps, 'links'))
                    <div class="mt-4">{{ $availableHelps->links() }}</div>
                @endif
            </div>
        </div>
    </div>

        <!-- Transaction History Component -->
        @if($activeTab === 'history')
            @livewire('customer.balance-transaction-history')
        @endif

        <!-- Help Detail Modal (bottom-sheet style like helps index) -->
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

                            @if(data_get($selectedHelpData, 'scheduled_at'))
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 mb-1">Jadwal Permintaan</p>
                                    <p class="text-sm text-gray-700">{{ \Carbon\Carbon::parse(data_get($selectedHelpData, 'scheduled_at'))->translatedFormat('d M Y, H:i') }}</p>
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
    </div>
</div>

@if(empty($customerBanners) || !count($customerBanners))
    <script>
        (function () {
            const banners = [
                { title: 'Promo Spesial', desc: 'Dapatkan diskon layanan untuk bantuan pertama Anda.', bgCss: 'linear-gradient(135deg,#6366f1,#4f46e5)' },
                { title: 'Gratis Ongkir', desc: 'Pengiriman gratis untuk bantuan di kota yang sama.', bgCss: 'linear-gradient(135deg,#10b981,#059669)' },
                { title: 'Dapatkan Badge', desc: 'Selesaikan 5 bantuan dan dapatkan badge Mitra Aktif.', bgCss: 'linear-gradient(135deg,#f59e0b,#f97316)' }
            ];

            const track = document.getElementById('promo-track');
            const dotsContainer = document.getElementById('promo-dots');
            const dots = dotsContainer ? Array.from(dotsContainer.querySelectorAll('button')) : [];
            let idx = 0;
            let timer = null;

            // build slides (create DOM nodes and use inline background to avoid Tailwind purge issues)
            if (track) {
                track.innerHTML = '';
                const frag = document.createDocumentFragment();
                banners.forEach(b => {
                    const slide = document.createElement('div');
                    slide.className = 'w-full flex-shrink-0 p-6 flex items-center justify-center text-white';
                    slide.style.background = b.bgCss;
                    const inner = document.createElement('div');
                    inner.className = 'text-center';
                    const title = document.createElement('div');
                    title.className = 'font-extrabold text-xl mb-1 tracking-tight';
                    title.textContent = b.title;
                    const desc = document.createElement('div');
                    desc.className = 'text-sm opacity-90 font-medium';
                    desc.textContent = b.desc;
                    inner.appendChild(title);
                    inner.appendChild(desc);
                    slide.appendChild(inner);
                    frag.appendChild(slide);
                });
                track.appendChild(frag);
            }

            function update() {
                if (track) {
                    const percent = (idx * 100) / banners.length;
                    track.style.transform = `translateX(${-percent}%)`;
                }
                if (dots.length) {
                    dots.forEach((d, k) => {
                        d.classList.toggle('bg-primary-600', k === idx);
                        d.classList.toggle('bg-gray-300', k !== idx);
                    });
                }
            }

            function go(i) {
                idx = (i + banners.length) % banners.length;
                update();
            }

            function resetTimer() {
                if (timer) clearInterval(timer);
                timer = setInterval(() => go(idx + 1), 4200);
            }

            // dot clicks
            if (dotsContainer) {
                dotsContainer.addEventListener('click', function (e) {
                    const dot = e.target.closest('button[data-dot]');
                    if (!dot) return;
                    const i = parseInt(dot.dataset.dot);
                    go(i);
                    resetTimer();
                });
            }

            // init
            if (track) {
                // ensure track has width for transform to work correctly
                track.style.width = `${banners.length * 100}%`;
                Array.from(track.children).forEach(child => child.style.width = `${100 / banners.length}%`);
                update();
                resetTimer();
            }
        })();
    </script>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initBannerSlider(wrapperSelector) {
            const wrapper = document.querySelector(wrapperSelector);
            if (!wrapper) return;
            const container = wrapper.parentElement; // expected visible container
            const slides = Array.from(wrapper.children || []);
            if (!slides.length || slides.length <= 1) return;

            function setup() {
                const cw = container.clientWidth || container.getBoundingClientRect().width;
                wrapper.style.width = (cw * slides.length) + 'px';
                wrapper.style.display = 'flex';
                wrapper.style.transition = 'transform 700ms cubic-bezier(.2,.9,.2,1)';
                slides.forEach(s => {
                    s.style.width = cw + 'px';
                    s.style.flex = '0 0 auto';
                });
            }

            let idx = 0;
            let timer = null;

            function go(i) {
                idx = (i + slides.length) % slides.length;
                const shift = -(idx * (container.clientWidth || container.getBoundingClientRect().width));
                wrapper.style.transform = 'translateX(' + shift + 'px)';
            }

            setup();
            window.addEventListener('resize', setup);

            timer = setInterval(function () { go(idx + 1); }, 3500);

            container.addEventListener('mouseenter', function () { if (timer) clearInterval(timer); });
            container.addEventListener('mouseleave', function () { if (timer) clearInterval(timer); timer = setInterval(function () { go(idx + 1); }, 3500); });
        }

        try { initBannerSlider('.customer-banner-slides'); } catch (e) { console.warn('customer slider init', e); }
        try { initBannerSlider('.mitra-banner-slides'); } catch (e) { /* ignore */ }
    });
</script>