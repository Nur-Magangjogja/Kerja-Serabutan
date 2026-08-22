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
                <div class="md:col-span-2 p-3 bg-gray-50 dark:bg-gray-700/40 rounded-lg">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Alamat</p>
                    <p class="font-medium text-gray-800 dark:text-gray-200 mt-0.5">{{ $user->address ?? '—' }}</p>
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
                        {{ ucfirst($user->status) }}
                    </span>
                </div>
                <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
                    <form action="{{ route('admin.partners.toggle', $user->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full px-4 py-2.5 rounded-lg text-xs font-semibold {{ $user->is_blocked ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-rose-600 text-white hover:bg-rose-700' }} transition-colors">
                            {{ $user->is_blocked ? 'Buka Blokir Pengguna' : 'Blokir Pengguna' }}
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
@endsection