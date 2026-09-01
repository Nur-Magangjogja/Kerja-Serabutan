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
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-lg">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $user->name }}</h2>
                        <p class="text-xs text-gray-400 dark:text-gray-500">ID Pengguna: #{{ $user->id }}</p>
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $user->role === 'mitra' ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400' : 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400' }}">
                    {{ ucfirst($user->role) }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-lg">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Email</p>
                    <p class="font-medium text-gray-800 dark:text-gray-200 mt-0.5">{{ $user->email }}</p>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-lg">
                    <p class="text-xs text-gray-400 dark:text-gray-500">No. HP / Telepon</p>
                    <p class="font-medium text-gray-800 dark:text-gray-200 mt-0.5">{{ $user->phone ?? '—' }}</p>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-lg">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Kota Operasional</p>
                    <p class="font-medium text-gray-800 dark:text-gray-200 mt-0.5">{{ $user->city_name ?? '—' }}</p>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-lg">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Waktu Registrasi</p>
                    <p class="font-medium text-gray-800 dark:text-gray-200 mt-0.5">{{ optional($user->created_at)->format('d M Y, H:i') ?? '—' }} WIB</p>
                </div>
                <div class="md:col-span-2 p-3.5 bg-gray-50 dark:bg-gray-700/40 rounded-lg space-y-2">
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold">Alamat Lengkap</p>
                    <p class="font-medium text-gray-800 dark:text-gray-200 text-sm">{{ $user->full_address }}</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 text-xs">
                        <div class="bg-white dark:bg-gray-800 p-2 rounded-md border border-gray-100 dark:border-gray-700"><span class="text-gray-400 block text-[10px]">RT / RW</span><span class="font-semibold text-gray-800 dark:text-gray-200">RT {{ $user->rt ? sprintf('%02d', (int)$user->rt) : '-' }}/RW {{ $user->rw ? sprintf('%02d', (int)$user->rw) : '-' }}</span></div>
                        <div class="bg-white dark:bg-gray-800 p-2 rounded-md border border-gray-100 dark:border-gray-700"><span class="text-gray-400 block text-[10px]">Kelurahan</span><span class="font-semibold text-gray-800 dark:text-gray-200 truncate block">{{ $user->kelurahan ?? '-' }}</span></div>
                        <div class="bg-white dark:bg-gray-800 p-2 rounded-md border border-gray-100 dark:border-gray-700"><span class="text-gray-400 block text-[10px]">Kecamatan</span><span class="font-semibold text-gray-800 dark:text-gray-200 truncate block">{{ $user->kecamatan ?? '-' }}</span></div>
                        <div class="bg-white dark:bg-gray-800 p-2 rounded-md border border-gray-100 dark:border-gray-700"><span class="text-gray-400 block text-[10px]">Provinsi</span><span class="font-semibold text-gray-800 dark:text-gray-200 truncate block">{{ $user->province ?? '-' }}</span></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar Action Cards --}}
        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Status Akun</h3>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Status</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $user->status === 'blocked' ? 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400' : 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400' }}">
                        {{ $user->status === 'blocked' ? 'Diblokir' : ($user->status === 'inactive' ? 'Nonaktif' : 'Aktif') }}
                    </span>
                </div>
                <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
                    <form action="{{ route('admin.partners.toggle', $user->id) }}" method="POST" id="show-block-form">
                        @csrf
                        <input type="hidden" name="admin_password" id="show-admin-password-input" value="" />
                        <button type="button" id="btn-show-block-trigger"
                            class="w-full px-4 py-2.5 rounded-lg text-xs font-semibold {{ $user->status === 'blocked' ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-rose-600 text-white hover:bg-rose-700' }} transition-colors">
                            {{ $user->status === 'blocked' ? 'Buka Blokir Pengguna' : 'Blokir Pengguna' }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Verifikasi KTP</h3>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Status</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $user->verified ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400' }}">
                        {{ $user->verified ? 'Terverifikasi' : 'Belum Verifikasi' }}
                    </span>
                </div>
                @if ($user->ktp_path)
                <a href="{{ Storage::url($user->ktp_path) }}" target="_blank"
                    class="block text-center px-4 py-2 text-xs font-semibold bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-900/50 rounded-lg transition-colors">
                    Lihat Dokumen KTP
                </a>
                @endif
            </div>
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