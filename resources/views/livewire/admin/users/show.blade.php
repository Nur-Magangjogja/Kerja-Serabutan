@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Detail Pengguna</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Informasi akun, status KTP, dan keamanan pengguna</p>
        </div>
        <a href="{{ route('admin.users.index') }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar
        </a>
    </div>

    {{-- ===== Main Content ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Kolom Kiri: Informasi Akun & Aksi (8 cols) --}}
        <div class="lg:col-span-8 space-y-6">
            {{-- Data Profil & Identitas --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 space-y-6">
                {{-- User Header Card --}}
                <div class="flex items-center justify-between pb-5 border-b border-gray-100 dark:border-gray-700 flex-wrap gap-4">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            @if($user->selfie_url)
                                <img src="{{ $user->selfie_url }}" alt="{{ $user->name }}" class="w-14 h-14 rounded-2xl object-cover ring-2 ring-primary-500/20 shadow-xs">
                            @else
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-xl shadow-xs">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                            @if($user->verified)
                                <span class="absolute -bottom-1 -right-1 bg-emerald-500 text-white rounded-full p-0.5 ring-2 ring-white dark:ring-gray-800" title="KTP Terverifikasi">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </span>
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                                <span class="text-xs font-mono text-gray-400 dark:text-gray-500">#{{ $user->id }}</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-xl text-xs font-bold {{ $user->role === 'mitra' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                        <span class="px-3 py-1 rounded-xl text-xs font-semibold {{ $user->status === 'blocked' ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800' : ($user->status === 'inactive' ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800') }}">
                            {{ $user->status === 'blocked' ? 'Diblokir' : ($user->status === 'inactive' ? 'Nonaktif' : 'Aktif') }}
                        </span>
                    </div>
                </div>

                {{-- Detail Fields Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                    <div class="p-3 bg-gray-50 dark:bg-gray-750/50 rounded-xl border border-gray-100 dark:border-gray-700/80">
                        <span class="text-gray-400 block text-[10px]">Nomor NIK KTP</span>
                        <span class="font-bold font-mono text-gray-900 dark:text-gray-100 text-sm block mt-0.5">{{ $user->nik ?: 'Belum diisi' }}</span>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-750/50 rounded-xl border border-gray-100 dark:border-gray-700/80">
                        <span class="text-gray-400 block text-[10px]">No. HP / WhatsApp</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200 text-xs sm:text-sm block mt-0.5">{{ $user->phone ?: '—' }}</span>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-750/50 rounded-xl border border-gray-100 dark:border-gray-700/80">
                        <span class="text-gray-400 block text-[10px]">Jenis Kelamin</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200 block mt-0.5">{{ $user->gender ?: '—' }}</span>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-750/50 rounded-xl border border-gray-100 dark:border-gray-700/80">
                        <span class="text-gray-400 block text-[10px]">Kota Operasional</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200 block mt-0.5">{{ $user->city_name ?: '—' }}</span>
                    </div>
                    <div class="sm:col-span-2 p-3.5 bg-gray-50 dark:bg-gray-750/50 rounded-xl border border-gray-100 dark:border-gray-700/80 space-y-1">
                        <span class="text-gray-400 block text-[10px]">Kota Wilayah</span>
                        <p class="text-xs sm:text-sm font-medium text-gray-800 dark:text-gray-200 leading-relaxed">{{ $user->full_address }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-750/50 rounded-xl border border-gray-100 dark:border-gray-700/80">
                        <span class="text-gray-400 block text-[10px]">Waktu Registrasi</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200 block mt-0.5">{{ optional($user->created_at)->translatedFormat('d M Y, H:i') ?? '—' }} WIB</span>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-750/50 rounded-xl border border-gray-100 dark:border-gray-700/80">
                        <span class="text-gray-400 block text-[10px]">Aktivitas Terakhir</span>
                        <span class="font-medium text-gray-800 dark:text-gray-200 truncate block mt-0.5">{{ $user->last_activity_at ? $user->last_activity_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Belum ada aktivitas' }}</span>
                    </div>
                </div>
            </div>

            {{-- Card Aksi Akun --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Kelola Status Akses Akun</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Blokir atau aktifkan kembali akses pengguna ke sistem.</p>
                </div>
                <form action="{{ route('admin.partners.toggle', $user->id) }}" method="POST" id="show-block-form">
                    @csrf
                    <input type="hidden" name="admin_password" id="show-admin-password-input" value="" />
                    <button type="button" id="btn-show-block-trigger"
                        class="px-5 py-2.5 rounded-xl text-xs font-bold {{ $user->status === 'blocked' ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-rose-600 hover:bg-rose-700 text-white' }} shadow-xs hover:shadow transition-all cursor-pointer">
                        {{ $user->status === 'blocked' ? 'Buka Blokir Akun' : 'Blokir Akun Pengguna' }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Kolom Kanan: Dokumen KTP & Selfie (4 cols) --}}
        <div class="lg:col-span-4 space-y-6">
            {{-- Card Foto KTP --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 space-y-3.5">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                        <span>🪪</span> Foto e-KTP
                    </h3>
                    @if($user->verified)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                            ✓ Terverifikasi
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                            Belum Verifikasi
                        </span>
                    @endif
                </div>

                @if($user->ktp_url)
                    <div class="relative group rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-900/10 dark:bg-gray-900/40 shadow-xs aspect-16/10">
                        <img src="{{ $user->ktp_url }}" alt="Dokumen KTP {{ $user->name }}" class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-300">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                            <a href="{{ $user->ktp_url }}" target="_blank" class="px-3.5 py-1.5 bg-white/95 hover:bg-white text-gray-900 text-xs font-bold rounded-lg shadow transition">
                                Buka Foto
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs pt-1">
                        <a href="{{ $user->ktp_url }}" target="_blank" class="text-primary-600 dark:text-primary-400 font-semibold hover:underline flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Buka Foto Asli
                        </a>
                        <a href="{{ $user->ktp_url }}" download class="text-gray-500 dark:text-gray-400 hover:underline flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Unduh Berkas
                        </a>
                    </div>
                @else
                    <div class="rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 p-6 text-center space-y-2">
                        <div class="w-10 h-10 mx-auto rounded-xl bg-gray-100 dark:bg-gray-750 flex items-center justify-center text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Belum Ada Dokumen KTP</p>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Pengguna belum mengunggah dokumen e-KTP.</p>
                    </div>
                @endif
            </div>

            {{-- Card Foto Selfie jika ada --}}
            @if($user->selfie_url)
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 space-y-3">
                <h3 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                    <span>🤳</span> Foto Selfie Verifikasi
                </h3>
                <div class="relative group rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-900/10 shadow-xs aspect-4/3">
                    <img src="{{ $user->selfie_url }}" alt="Selfie {{ $user->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                </div>
                <div class="text-right">
                    <a href="{{ $user->selfie_url }}" target="_blank" class="text-xs text-primary-600 dark:text-primary-400 font-semibold hover:underline">
                        Buka Foto Asli &rarr;
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Confirm Block Modal with password verification --}}
<div id="show-block-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div id="show-block-backdrop" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md z-10 border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Konfirmasi Tindakan</h3>
            <button id="show-block-close" class="p-1 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">&times;</button>
        </div>
        <div class="p-6 space-y-4">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                {{ $user->status === 'blocked' ? 'Apakah Anda yakin ingin membuka blokir pengguna "' . $user->name . '"? Pengguna akan dapat masuk kembali.' : 'Apakah Anda yakin ingin memblokir pengguna "' . $user->name . '"? Pengguna tidak akan dapat mengakses aplikasi.' }}
            </p>

            <div class="p-3.5 bg-amber-50 dark:bg-amber-900/25 border border-amber-200 dark:border-amber-800 rounded-xl space-y-2">
                <div class="flex items-center gap-2 text-xs font-bold text-amber-900 dark:text-amber-200">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <span>Verifikasi Kata Sandi Admin</span>
                </div>
                <p class="text-[11px] text-amber-800/80 dark:text-amber-300/80 leading-relaxed">
                    Masukkan kata sandi akun Admin Anda untuk mengonfirmasi perubahan status blokir pengguna ini.
                </p>
                <div>
                    <input type="password" id="showModalPassword"
                        placeholder="Masukkan kata sandi Admin Anda"
                        class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    <p id="showModalPasswordError" class="text-xs text-rose-600 dark:text-rose-400 mt-1 hidden font-medium"></p>
                </div>
            </div>
        </div>
        <div class="px-6 py-3.5 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
            <button id="show-block-cancel" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">Batal</button>
            <button id="show-block-confirm" class="px-4 py-2 text-sm font-semibold {{ $user->status === 'blocked' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700' }} text-white rounded-lg transition-colors">
                {{ $user->status === 'blocked' ? 'Buka Blokir' : 'Blokir' }}
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function(){
        const modal = document.getElementById('show-block-modal');
        const trigger = document.getElementById('btn-show-block-trigger');
        const cancelBtn = document.getElementById('show-block-cancel');
        const closeBtn = document.getElementById('show-block-close');
        const confirmBtn = document.getElementById('show-block-confirm');
        const backdrop = document.getElementById('show-block-backdrop');
        const passwordInput = document.getElementById('showModalPassword');
        const passwordError = document.getElementById('showModalPasswordError');
        const form = document.getElementById('show-block-form');
        const hiddenPassword = document.getElementById('show-admin-password-input');

        function openModal(){
            if (passwordInput) passwordInput.value = '';
            if (passwordError) {
                passwordError.classList.add('hidden');
                passwordError.textContent = '';
            }
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => { if (passwordInput) passwordInput.focus(); }, 50);
        }

        function closeModal(){
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            if (passwordInput) passwordInput.value = '';
            if (passwordError) passwordError.classList.add('hidden');
        }

        if (trigger) trigger.addEventListener('click', openModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);

        if (confirmBtn) {
            confirmBtn.addEventListener('click', function(){
                const val = passwordInput ? passwordInput.value.trim() : '';
                if (!val) {
                    if (passwordError) {
                        passwordError.textContent = 'Kata sandi Admin wajib dimasukkan untuk mengonfirmasi tindakan ini.';
                        passwordError.classList.remove('hidden');
                    }
                    if (passwordInput) passwordInput.focus();
                    return;
                }
                if (hiddenPassword) hiddenPassword.value = val;
                if (form) form.submit();
                closeModal();
            });
        }
    });
</script>
@endpush
@endsection