<div class="min-h-screen bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="max-w-md mx-auto">
        <!-- Header Section -->
        <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>
            
            <div class="relative z-10 space-y-4">
                <div class="text-white text-center">
                    <h1 class="text-base font-bold truncate">Ulasan Saya</h1>
                </div>

                <!-- Rating Summary Card (compact) -->
                <div class="bg-white/15 backdrop-blur-md rounded-2xl p-4 text-center border border-white/20 shadow-xs">
                    <div class="flex items-center justify-center gap-1.5 mb-1.5">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $averageRating)
                                <svg class="w-5 h-5 text-yellow-300 drop-shadow-xs" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-white/30" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endif
                        @endfor
                    </div>
                    <div class="flex items-baseline justify-center gap-1.5">
                        <span class="text-3xl font-extrabold text-white">{{ $averageRating }}</span>
                        <span class="text-xs text-white/80">/ 5.0</span>
                    </div>
                    <p class="text-xs text-white/90 mt-1">{{ $totalRatings }} ulasan telah Anda berikan</p>
                </div>
            </div>
        </div>

        <!-- Ratings List -->
        <div class="px-5 pt-5 pb-24">
            @if($ratings->count() > 0)
                <div class="space-y-3">
                    @foreach($ratings as $rating)
                        @php
                            $mitraName = optional($rating->mitra)->name ?? optional($rating->ratee)->name ?? 'Mitra Bantuan';
                        @endphp
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 hover:shadow-md transition">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">{{ $mitraName }}</h3>
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">Mitra</span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $rating->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="flex gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $rating->rating)
                                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endif
                                    @endfor
                                </div>
                            </div>

                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">{{ optional($rating->help)->title ?? '-' }}</p>

                            @if($rating->review)
                                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-3 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">{{ $rating->review }}</p>
                            @endif

                            <div class="flex gap-2">
                                <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-semibold" style="background: rgba(0, 152, 231, 0.1); color: #0098e7;">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    {{ $rating->rating }}.0
                                </span>
                                @if($rating->rating >= 4)
                                    <span class="inline-flex items-center gap-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-3 py-1.5 rounded-full text-xs font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Puas
                                    </span>
                                @elseif($rating->rating >= 3)
                                    <span class="inline-block bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 px-3 py-1.5 rounded-full text-xs font-semibold">Biasa</span>
                                @else
                                    <span class="inline-block bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 px-3 py-1.5 rounded-full text-xs font-semibold">Kurang Puas</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-xs">
                    {{ $ratings->links('vendor.pagination.superadmin') }}
                </div>
            @else
                <div class="text-center py-16">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Belum Ada Ulasan Diberikan</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Anda belum pernah memberikan rating & ulasan untuk mitra.</p>
                </div>
            @endif
        </div>
    </div>
</div>
