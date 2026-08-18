<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 pb-20">
    <!-- Top Header Gradient -->
    <div class="px-4 pt-5 pb-12 relative overflow-hidden" style="background: linear-gradient(135deg, #0098e7 0%, #0077cc 50%, #005599 100%);">
        <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-16 -mt-16 blur-xl"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/10 rounded-full -ml-12 -mb-12 blur-lg"></div>

        <div class="relative z-10 max-w-lg mx-auto">
            <div class="flex items-center justify-between text-white mb-5">
                <a href="{{ route('mitra.dashboard') }}" class="p-2 hover:bg-white/20 rounded-xl transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-lg font-bold flex-1 text-center pr-9">Rating & Ulasan Mitra</h1>
            </div>

            <!-- Rating Summary Card -->
            <div class="bg-white/15 backdrop-blur-md rounded-2xl p-5 border border-white/20 shadow-lg text-center text-white">
                <div class="flex items-center justify-center gap-1 mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= round($averageRating))
                            <svg class="w-6 h-6 text-amber-300 drop-shadow" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @else
                            <svg class="w-6 h-6 text-white/30" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        @endif
                    @endfor
                </div>
                <div class="flex items-baseline justify-center gap-1.5">
                    <span class="text-4xl font-extrabold tracking-tight">{{ number_format($averageRating, 1) }}</span>
                    <span class="text-sm font-medium text-white/80">/ 5.0</span>
                </div>
                <p class="text-xs text-white/90 mt-1 font-medium">{{ $totalRatings }} ulasan telah diterima dari customer</p>
            </div>
        </div>
    </div>

    <!-- Main Content List -->
    <div class="max-w-lg mx-auto px-4 -mt-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 sm:p-5">
            <h2 class="text-sm font-bold text-gray-800 dark:text-white mb-4 flex items-center justify-between">
                <span>Daftar Ulasan Customer</span>
                <span class="text-xs font-normal text-gray-500 dark:text-gray-400">{{ $totalRatings }} Ulasan</span>
            </h2>

            @if($ratings->count() > 0)
                <div class="space-y-3.5 divide-y divide-gray-100 dark:divide-gray-700/60">
                    @foreach($ratings as $rating)
                        @php
                            $customerName = optional($rating->rater)->name ?? optional($rating->user)->name ?? 'Customer SayaBantu';
                        @endphp
                        <div class="pt-3.5 first:pt-0">
                            <div class="flex items-start justify-between gap-2 mb-1.5">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 flex items-center justify-center font-bold text-xs">
                                            {{ strtoupper(substr($customerName, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-900 dark:text-white text-xs sm:text-sm">{{ $customerName }}</h3>
                                            <p class="text-[10px] text-gray-400 dark:text-gray-500">{{ $rating->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-0.5 bg-amber-50 dark:bg-amber-900/30 px-2 py-0.5 rounded-full border border-amber-200/50">
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    <span class="text-xs font-bold text-amber-700 dark:text-amber-300">{{ $rating->rating }}.0</span>
                                </div>
                            </div>

                            @if($rating->help)
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mt-1">
                                    Tugas: <span class="text-gray-800 dark:text-gray-200 font-semibold">{{ $rating->help->title }}</span>
                                </p>
                            @endif

                            @if($rating->review)
                                <div class="mt-2 p-2.5 bg-gray-50 dark:bg-gray-700/40 rounded-xl text-xs text-gray-700 dark:text-gray-300 italic border border-gray-100 dark:border-gray-700">
                                    "{{ $rating->review }}"
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($ratings->hasPages())
                    <div class="mt-5">
                        {{ $ratings->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <div class="w-14 h-14 mx-auto mb-3 rounded-full bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                        <svg class="w-7 h-7 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white mb-1">Belum Ada Rating Diterima</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Selesaikan bantuan untuk mendapatkan penilaian dan ulasan bintang dari customer.</p>
                </div>
            @endif
        </div>
    </div>
</div>