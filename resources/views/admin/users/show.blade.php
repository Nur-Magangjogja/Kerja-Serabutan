@extends('layouts.admin')

@section('content')
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="px-8 py-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Detail Pengguna</h1>
                <p class="text-sm text-gray-600 mt-1">Tinjau informasi akun, status KTP, dan status keamanan pengguna.</p>
            </div>
            <a href="{{ route('admin.users.index') }}"
                class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-full text-xs text-gray-700 hover:bg-gray-50">
                &larr; Kembali ke daftar
            </a>
        </div>
    </div>

    <div class="p-8 space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-md border border-gray-200 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Profil Pengguna</h2>
                        <p class="text-xs text-gray-500 mt-1">Informasi dasar akun dan kota operasional.</p>
                    </div>
                    <span
                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->role === 'mitra' ? 'bg-green-100 text-green-800' : ($user->role === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700') }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>

                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Nama</dt>
                        <dd class="font-medium text-gray-900">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Email</dt>
                        <dd class="font-medium text-gray-900">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">No. HP</dt>
                        <dd class="font-medium text-gray-900">{{ $user->phone ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Kota</dt>
                        <dd class="font-medium text-gray-900">{{ $user->city_name ?? '-' }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-gray-500">Alamat</dt>
                        <dd class="font-medium text-gray-900">{{ $user->address ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="space-y-4">
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-5">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Status Akun</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Status</span>
                            <span
                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->status === 'blocked' ? 'bg-red-100 text-red-800' : ($user->status === 'inactive' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Blokir</span>
                            <span
                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->is_blocked ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700' }}">
                                {{ $user->is_blocked ? 'Diblokir' : 'Tidak diblokir' }}
                            </span>
                        </div>
                        <div class="pt-3 border-t border-gray-100">
                            <form action="{{ route('admin.partners.toggle', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full px-3 py-2 rounded-xl text-xs font-semibold {{ $user->is_blocked ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-red-600 text-white hover:bg-red-700' }}">
                                    {{ $user->is_blocked ? 'Buka Blokir Pengguna' : 'Blokir Pengguna' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-5">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">Status KTP</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Verifikasi</span>
                            <span
                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->verified ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $user->verified ? 'Terverifikasi' : 'Belum Verifikasi' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">File KTP</span>
                            <span class="font-medium text-gray-900">
                                {{ $user->ktp_path ? 'Terunggah' : 'Belum ada' }}
                            </span>
                        </div>
                        @if ($user->ktp_path)
                            <div class="mt-2">
                                <a href="{{ Storage::url($user->ktp_path) }}" target="_blank"
                                    class="inline-flex items-center text-xs text-primary-600 hover:text-primary-700">
                                    Lihat file KTP
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection