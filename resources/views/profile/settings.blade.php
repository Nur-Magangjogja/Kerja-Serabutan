<x-app-layout>
    <x-slot name="title">Pengaturan</x-slot>

    @php
        $user = auth()->user();
        $pendingDeletion = \App\Models\AccountDeletionRequest::where('user_id', $user->id)->where('status', 'pending')->first();
        $userBalance = (float) $user->balance;
        $activeHelps = \App\Models\Help::where('user_id', $user->id)->active()->count();
    @endphp

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 pb-24">
        <!-- Header Section -->
        <div class="px-5 pt-4 pb-5 relative overflow-hidden bg-gradient-to-br from-[#0098e7] via-[#0077cc] to-[#0060b0] rounded-b-2xl shadow-sm text-white">
            <div class="absolute top-0 right-0 w-36 h-36 bg-white/10 rounded-full blur-xl -mr-12 -mt-12 pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between text-white">
                    <a href="{{ route('profile') }}" aria-label="Kembali" class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>

                    <div class="text-center flex-1 min-w-0 px-2">
                        <h1 class="text-base font-bold truncate">Pengaturan</h1>
                        <p class="text-xs text-white/90 truncate mt-0.5">Kelola preferensi dan keamanan akun Anda</p>
                    </div>

                    <a href="{{ route('customer.notifications.index') }}" class="p-2 hover:bg-white/20 rounded-xl transition cursor-pointer relative flex-shrink-0" title="Notifikasi">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @php
                            $unreadNotif = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
                        @endphp
                        @if($unreadNotif > 0)
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <div class="px-5 pt-4">
            @if (session()->has('message'))
                <div class="p-4 rounded-2xl bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 text-xs flex items-center gap-2.5 shadow-sm">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('message') }}</span>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-xs flex items-center gap-2.5 shadow-sm">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-xs space-y-1 shadow-sm">
                    @foreach ($errors->all() as $err)
                        <div class="flex items-center gap-2">
                            <span>•</span>
                            <span>{{ $err }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Settings Content -->
        <div class="px-5 pt-3 relative z-10">
            <div class="space-y-3">
                <!-- Theme / Appearance Settings -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 transition">
                    <div class="flex items-center gap-3.5 mb-3.5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 dark:text-white text-sm">Tema Tampilan</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pilih tema terang, gelap, atau otomatis sistem</p>
                        </div>
                    </div>
                    <div class="pt-1 flex justify-center">
                        <x-theme-switcher />
                    </div>
                </div>

                <!-- Notification Settings -->
                <a href="{{ route('profile.settings.notifications') }}"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex items-center gap-3.5 hover:shadow-md hover:border-primary-500/30 transition">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">Pengaturan Notifikasi</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola preferensi notifikasi</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <!-- Password Settings -->
                <a href="{{ route('profile.settings.password') }}"
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex items-center gap-3.5 hover:shadow-md hover:border-primary-500/30 transition">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">Ubah Kata Sandi</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Perbarui keamanan akun Anda</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                <!-- Account Deletion Section -->
                @if($pendingDeletion)
                    <!-- Active Pending Request Banner -->
                    <div class="bg-amber-50 dark:bg-amber-900/25 rounded-2xl border border-amber-200 dark:border-amber-800/60 p-4 space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-amber-900 dark:text-amber-200 text-sm">Permintaan Hapus Akun Diproses</h3>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-200 dark:bg-amber-800 text-amber-800 dark:text-amber-200 animate-pulse">
                                        Menunggu Review
                                    </span>
                                </div>
                                <p class="text-xs text-amber-800/80 dark:text-amber-300/80 mt-1 leading-relaxed">
                                    Permintaan Anda telah diajukan pada <strong>{{ $pendingDeletion->created_at->translatedFormat('d M Y, H:i') }} WIB</strong> dan sedang dalam proses peninjauan oleh tim Superadmin.
                                </p>
                                <p class="text-xs text-amber-700 dark:text-amber-300 mt-1 italic">
                                    Alasan: "{{ $pendingDeletion->reason }}"
                                </p>
                            </div>
                        </div>

                        <form action="{{ route('profile.deletion.cancel') }}" method="POST" class="pt-1">
                            @csrf
                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin membatalkan pengajuan penghapusan akun?')"
                                class="w-full py-2.5 px-4 bg-white dark:bg-gray-800 border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300 hover:bg-amber-100/50 font-bold rounded-xl text-xs transition cursor-pointer shadow-sm">
                                Batalkan Pengajuan Hapus Akun
                            </button>
                        </form>
                    </div>
                @else
                    @php
                        $lastRejected = \App\Models\AccountDeletionRequest::where('user_id', $user->id)->where('status', 'rejected')->latest()->first();
                    @endphp
                    @if($lastRejected)
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/60 rounded-2xl p-4 space-y-2">
                            <div class="flex items-center gap-2 text-red-700 dark:text-red-300 font-bold text-xs">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Permintaan Hapus Akun Sebelumnya Ditolak</span>
                            </div>
                            <p class="text-xs text-red-800/90 dark:text-red-300/90 leading-relaxed">
                                <strong>Catatan Superadmin:</strong> {{ $lastRejected->admin_notes ?: 'Permintaan tidak dapat disetujui saat ini.' }}
                            </p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                Silakan selesaikan kendala di atas (misal menarik sisa saldo), lalu Anda dapat mengajukan permohonan baru di bawah.
                            </p>
                        </div>
                    @endif

                    <!-- Request Account Deletion Button -->
                    <button onclick="openDeletionModal()"
                        class="w-full bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 flex items-center gap-3.5 hover:shadow-md hover:border-red-200 dark:hover:border-red-800/60 transition cursor-pointer">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <div class="flex-1 text-left">
                            <h3 class="font-bold text-gray-900 dark:text-white text-sm">Permintaan Hapus Akun</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Ajukan penutupan akun kepada Superadmin</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Request Account Deletion Modal -->
    <div id="deletionModal"
        class="hidden fixed inset-0 bg-gray-900/70 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6 space-y-4">
            <!-- Icon -->
            <div class="w-14 h-14 mx-auto rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center text-red-600 dark:text-red-400">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <div class="text-center">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Permintaan Hapus Akun</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Permintaan Anda akan ditinjau oleh tim Superadmin untuk memverifikasi saldo dan transaksi.
                </p>
            </div>

            @if($userBalance > 0)
                <div class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-xl p-3.5 text-xs text-amber-800 dark:text-amber-200 space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Sisa Saldo Akun: Rp {{ number_format($userBalance, 0, ',', '.') }}
                    </div>
                    <p class="text-[11px] leading-relaxed opacity-90">
                        Harap lakukan penarikan dana terlebih dahulu. Akun dengan sisa saldo aktif dapat ditolak oleh Superadmin sampai saldo diselesaikan.
                    </p>
                </div>
            @endif

            @if($activeHelps > 0)
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-3 text-xs text-red-800 dark:text-red-200">
                    <strong>Perhatian:</strong> Anda masih memiliki {{ $activeHelps }} permintaan bantuan yang sedang aktif/berjalan.
                </div>
            @endif

            <form action="{{ route('profile.deletion.request') }}" method="POST" class="space-y-3.5">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Alasan Penghapusan Akun <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason" rows="3" required
                        placeholder="Contoh: Sudah tidak membutuhkan layanan, ingin membuat akun baru, dll."
                        class="w-full p-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-xs text-gray-800 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">
                        Konfirmasi Kata Sandi Saat Ini <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" required placeholder="Masukkan kata sandi Anda"
                        class="w-full p-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-xs text-gray-800 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                </div>

                <div class="pt-2 space-y-2">
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold py-3 rounded-xl shadow-md transition text-xs cursor-pointer">
                        Kirim Permintaan ke Superadmin
                    </button>

                    <button type="button" onclick="closeDeletionModal()"
                        class="w-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold py-2.5 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition text-xs cursor-pointer">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openDeletionModal() {
            document.getElementById('deletionModal').classList.remove('hidden');
        }

        function closeDeletionModal() {
            document.getElementById('deletionModal').classList.add('hidden');
        }
    </script>
</x-app-layout>