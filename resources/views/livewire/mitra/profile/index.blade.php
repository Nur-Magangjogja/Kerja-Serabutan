<div class="min-h-screen bg-white">
    @php
        // Mitra stats
        $totalHelped = \App\Models\Help::where('mitra_id', $user->id)->count();
        $completedHelps = \App\Models\Help::where('mitra_id', $user->id)->where('status', 'selesai')->count();
        $averageRating = round($user->average_rating ?? 0, 1);
        $totalRatings = $user->rating_count ?? 0;
    @endphp

    <div class="max-w-md mx-auto">
        <!-- Header - BRImo Style -->
        <div class="px-5 pt-5 pb-32 relative overflow-hidden" style="background: linear-gradient(to bottom right, #0098e7, #0077cc, #0060b0);">
            <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -mr-20 -mt-20"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-16 -mb-16"></div>
            
            <div class="relative z-10">
                <div class="flex items-center justify-between text-white mb-8">
                    <button onclick="window.history.back()" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <h1 class="text-lg font-bold">Profil Saya</h1>

                    <a href="{{ route('mitra.profile.edit') }}" class="p-2 hover:bg-white/20 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </a>
                </div>

                <!-- Profile Avatar & Info -->
                <div class="text-center">
                    <div class="relative inline-block">
                        @if($user->selfie_photo)
                            <img src="{{ asset('storage/' . $user->selfie_photo) }}" alt="Avatar" class="w-24 h-24 rounded-full object-cover mx-auto ring-4 ring-white/30 shadow-xl">
                        @else
                            <div class="w-24 h-24 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white text-3xl font-bold mx-auto ring-4 ring-white/30 shadow-xl">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif

                        <button onclick="openMitraPhotoModal()" class="absolute bottom-0 right-0 bg-white p-2 rounded-full shadow-lg hover:scale-110 active:scale-95 transition-transform duration-200">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                    </div>

                    <h2 class="text-xl font-bold text-white mt-4">{{ $user->name }}</h2>
                    <p class="text-sm text-white/80 mt-1">{{ $user->email }}</p>

                    @if($user->is_verified ?? false)
                        <div class="inline-flex items-center gap-1.5 bg-green-500/20 backdrop-blur-sm px-3 py-1.5 rounded-full mt-3">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-xs font-semibold text-white">Terverifikasi</span>
                        </div>
                    @else
                        <div class="inline-flex items-center gap-1.5 bg-yellow-500/20 backdrop-blur-sm px-3 py-1.5 rounded-full mt-3">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-xs font-semibold text-white">Belum Verifikasi</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Curved separator -->
            <svg class="absolute bottom-0 left-0 w-full" viewBox="0 0 1440 72" preserveAspectRatio="none" aria-hidden="true">
                <path d="M0,32 C360,72 1080,0 1440,40 L1440,72 L0,72 Z" fill="#ffffff"></path>
            </svg>
        </div>

        <!-- Stats Card (overlapping header) -->
        <div class="px-5 -mt-20 relative z-20">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100">
                <div class="grid grid-cols-3 divide-x divide-gray-100">
                    <div class="p-4 text-center group cursor-pointer hover:bg-gray-50 transition-colors duration-200 rounded-l-2xl">
                        <div class="text-2xl font-bold transition-transform duration-300 group-hover:scale-110" style="color: #0098e7;">{{ $totalHelped }}</div>
                        <p class="text-xs text-gray-600 mt-1">Total Bantuan</p>
                    </div>
                    <div class="p-4 text-center group cursor-pointer hover:bg-gray-50 transition-colors duration-200">
                        <div class="text-2xl font-bold text-green-600 transition-transform duration-300 group-hover:scale-110">{{ $completedHelps }}</div>
                        <p class="text-xs text-gray-600 mt-1">Selesai</p>
                    </div>
                    <div class="p-4 text-center group cursor-pointer hover:bg-gray-50 transition-colors duration-200 rounded-r-2xl">
                        <div class="flex items-center justify-center gap-1">
                            <span class="text-2xl font-bold text-yellow-600 transition-transform duration-300 group-hover:scale-110">{{ $averageRating }}</span>
                            <svg class="w-5 h-5 text-yellow-500 transition-transform duration-300 group-hover:rotate-12" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                        <p class="text-xs text-gray-600 mt-1">Rating ({{ $totalRatings }})</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Items -->
        <div class="px-5 pt-6 pb-24">
            @livewire('mitra.update-profile-photo')

            <!-- Menu Grid -->
            <div class="grid grid-cols-2 gap-3 mb-4">
                <!-- Edit Profil -->
                <a href="{{ route('mitra.profile.edit') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center gap-3 hover:shadow-md hover:border-primary-200 transition">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #0098e7 0%, #0060b0 100%);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-900 text-center">Edit Profil</span>
                </a>

                <!-- Riwayat Bantuan -->
                <a href="{{ route('mitra.helps.completed') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center gap-3 hover:shadow-md hover:border-primary-200 transition">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-green-400 to-green-600">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-900 text-center">Riwayat</span>
                </a>

                <!-- Rating & Ulasan -->
                <a href="{{ route('mitra.ratings') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center gap-3 hover:shadow-md hover:border-primary-200 transition">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-yellow-400 to-yellow-600">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-900 text-center">Rating</span>
                </a>

                <!-- Pengaturan -->
                <a href="{{ route('mitra.settings') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col items-center gap-3 hover:shadow-md hover:border-primary-200 transition">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-gray-400 to-gray-600">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-900 text-center">Pengaturan</span>
                </a>
            </div>

            <!-- Full Width Menu Items -->
            <div class="space-y-3">
                <!-- Bantuan & Dukungan -->
                <a href="{{ route('mitra.help-support') }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-4 hover:shadow-md hover:border-primary-200 transition">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-purple-400 to-purple-600 flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="flex-1 font-semibold text-gray-900">Bantuan & Dukungan</span>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <!-- Logout -->
                <button onclick="document.getElementById('logout-modal').classList.remove('hidden')" class="w-full bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center gap-4 hover:shadow-md hover:border-red-200 transition">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br from-red-400 to-red-600 flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <span class="flex-1 font-semibold text-gray-900 text-left">Logout</span>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Logout Modal -->
        <div id="logout-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] flex items-center justify-center p-5" onclick="if(event.target === this) this.classList.add('hidden')">
            <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 transform transition-all">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Logout</h2>
                    <p class="text-sm text-gray-600">Apakah Anda yakin ingin keluar dari aplikasi?</p>
                </div>

                <form action="{{ route('logout') }}" method="POST" class="space-y-3">
                    @csrf
                    <button type="submit" class="w-full bg-red-600 text-white font-semibold py-3.5 rounded-xl hover:bg-red-700 transition shadow-lg shadow-red-600/30">
                        Ya, Logout
                    </button>
                    <button type="button" onclick="document.getElementById('logout-modal').classList.add('hidden')" class="w-full bg-gray-100 text-gray-700 font-semibold py-3.5 rounded-xl hover:bg-gray-200 transition">
                        Batal
                    </button>
                </form>
            </div>
        </div>
    </div>


    <script>
        // Expose robust helpers on the window so onclick handlers can call them reliably.
        window.openMitraPhotoModal = function () {
            const tryEmit = () => {
                if (window.livewire && typeof window.livewire.emit === 'function') {
                    window.livewire.emit('openModal');
                    return true;
                }
                if (window.Livewire) {
                    if (typeof window.Livewire.emit === 'function') {
                        window.Livewire.emit('openModal');
                        return true;
                    }
                    if (typeof window.Livewire.dispatch === 'function') {
                        window.Livewire.dispatch('openModal');
                        return true;
                    }
                }
                return false;
            };

            if (tryEmit()) return;

            let attempts = 0;
            const interval = setInterval(() => {
                attempts++;
                if (tryEmit()) {
                    clearInterval(interval);
                    return;
                }
                if (attempts >= 10) {
                    clearInterval(interval);
                    console.warn('openMitraPhotoModal: Livewire emit not available.');
                }
            }, 200);
        };
    </script>
</div>