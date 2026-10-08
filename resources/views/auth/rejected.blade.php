<x-guest-layout>
    <div class="space-y-6 text-center py-2">
        {{-- Status Icon --}}
        <div class="relative inline-flex items-center justify-center">
            <div class="w-18 h-18 bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 rounded-3xl flex items-center justify-center shadow-lg shadow-rose-500/10 border-2 border-rose-200 dark:border-rose-800">
                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

        {{-- Heading & Subtitle --}}
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Pendaftaran Belum Disetujui</h2>
            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-2 max-w-sm mx-auto leading-relaxed">
                Pengajuan verifikasi berkas Anda belum dapat disetujui. Silakan periksa catatan admin di bawah ini untuk melakukan perbaikan.
            </p>
        </div>

        {{-- Alasan Penolakan Box --}}
        <div class="bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/60 rounded-2xl p-4 text-left space-y-1.5">
            <span class="text-[11px] font-bold text-rose-700 dark:text-rose-300 uppercase tracking-wider block">
                Alasan Penolakan dari Admin
            </span>
            <p class="text-xs sm:text-sm text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-line font-medium">
                {{ $registration->rejection_reason ?: 'Tidak ada alasan khusus yang dicantumkan. Silakan periksa kembali kejelasan foto KTP dan kesesuaian data diri Anda.' }}
            </p>
        </div>

        {{-- Info notice --}}
        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto text-center leading-relaxed">
            Data diri Anda sebelumnya tetap tersimpan. Anda hanya perlu memeriksa dan memperbarui berkas yang bermasalah.
        </p>

        {{-- Action Buttons --}}
        <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
            @if(Auth::check())
                <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-4 py-2.5 text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700/60 hover:bg-gray-200 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl transition cursor-pointer">
                        Keluar
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-4 py-2.5 text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700/60 hover:bg-gray-200 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl transition text-center cursor-pointer">
                    Kembali ke Login
                </a>
            @endif

            <form method="POST" action="{{ route('registration.reapply', ['registration' => $registration->id]) }}" class="w-full sm:w-auto">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 active:bg-primary-800 rounded-xl shadow-xs transition inline-flex items-center justify-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Perbaiki Berkas & Ajukan Ulang</span>
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
