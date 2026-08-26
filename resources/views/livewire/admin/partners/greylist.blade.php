@extends(in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'layouts.superadmin' : 'layouts.admin')

@section('content')
@php
    $routePrefix = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'superadmin.' : 'admin.';
@endphp

<div class="space-y-6 max-w-7xl mx-auto" x-data="{ addModal: false, warningModal: false, selectedUser: null, selectedUserName: '', currentWarningLevel: 1 }">
    {{-- Header Section --}}
    <div class="flex items-center justify-between gap-4 flex-wrap bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
        <div>
            <h1 class="text-lg font-extrabold text-gray-900 dark:text-white flex items-center gap-2.5">
                <span class="p-2 bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded-xl">⚠️</span>
                <span>Daftar Abu-Abu (Pengawasan & Shadow Ban)</span>
            </h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Kelola daftar user bermasalah (Mitra & Customer), terbitkan Surat Peringatan (SP 1 - 3), dan terapkan Shadow Ban untuk membatasi akses tanpa memutus akun.
            </p>
        </div>

        <button type="button" @click="addModal = true"
            class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>+ Masukkan User Bermasalah</span>
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
        {{-- Total Greylisted --}}
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Pengawasan</span>
            <div class="text-xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $counts['total_greylist'] }}</div>
            <span class="text-[10px] text-gray-400">User dalam daftar abu-abu</span>
        </div>

        {{-- Shadow Banned --}}
        <div class="bg-rose-50/70 dark:bg-rose-950/40 p-4 rounded-2xl border border-rose-200 dark:border-rose-800/60 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-700 dark:text-rose-300">🚫 Shadow Banned</span>
            <div class="text-xl font-extrabold text-rose-950 dark:text-rose-100 mt-1">{{ $counts['total_shadow_banned'] }}</div>
            <span class="text-[10px] text-rose-800 dark:text-rose-300">Akses bantuan dibatasi</span>
        </div>

        {{-- Active Warnings --}}
        <div class="bg-amber-50/70 dark:bg-amber-950/40 p-4 rounded-2xl border border-amber-200 dark:border-amber-800/60 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">📢 Dalam Peringatan (SP)</span>
            <div class="text-xl font-extrabold text-amber-950 dark:text-amber-100 mt-1">{{ $counts['total_warning'] }}</div>
            <span class="text-[10px] text-amber-800 dark:text-amber-300">Menerima surat peringatan</span>
        </div>

        {{-- Mitra --}}
        <div class="bg-emerald-50/70 dark:bg-emerald-950/40 p-4 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">🛵 Mitra Diawasi</span>
            <div class="text-xl font-extrabold text-emerald-950 dark:text-emerald-100 mt-1">{{ $counts['total_mitra'] }}</div>
            <span class="text-[10px] text-emerald-800 dark:text-emerald-300">Mitra bermasalah</span>
        </div>

        {{-- Customer --}}
        <div class="bg-blue-50/70 dark:bg-blue-950/40 p-4 rounded-2xl border border-blue-200 dark:border-blue-800/60 shadow-xs">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:text-blue-300">👤 Customer Diawasi</span>
            <div class="text-xl font-extrabold text-blue-950 dark:text-blue-100 mt-1">{{ $counts['total_customer'] }}</div>
            <span class="text-[10px] text-blue-800 dark:text-blue-300">Customer bermasalah</span>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
        <form method="GET" action="{{ route($routePrefix . 'partners.greylist') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            {{-- Search --}}
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, no HP..."
                    class="w-full px-3.5 py-2 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
            </div>

            {{-- Role --}}
            <div>
                <select name="role" class="w-full px-3 py-2 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua Peran (Mitra & Customer)</option>
                    <option value="mitra" {{ request('role') === 'mitra' ? 'selected' : '' }}>🛵 Hanya Mitra</option>
                    <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>👤 Hanya Customer</option>
                </select>
            </div>

            {{-- Filter Status --}}
            <div>
                <select name="filter" class="w-full px-3 py-2 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua Status Pengawasan</option>
                    <option value="shadow_banned" {{ request('filter') === 'shadow_banned' ? 'selected' : '' }}>🚫 Shadow Banned</option>
                    <option value="warning" {{ request('filter') === 'warning' ? 'selected' : '' }}>📢 Memiliki SP (Peringatan)</option>
                    <option value="greylisted" {{ request('filter') === 'greylisted' ? 'selected' : '' }}>⚠️ Dalam Daftar Abu-Abu</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold transition shadow-xs">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'role', 'filter']))
                    <a href="{{ route($routePrefix . 'partners.greylist') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-semibold transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Users Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left text-gray-600 dark:text-gray-300">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-200 font-bold border-b border-gray-100 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3.5">User Bermasalah</th>
                        <th class="px-4 py-3.5">Peran</th>
                        <th class="px-4 py-3.5">Status Pengawasan</th>
                        <th class="px-4 py-3.5">Alasan / Pesan Peringatan</th>
                        <th class="px-4 py-3.5">Waktu Masuk</th>
                        <th class="px-4 py-3.5 text-right">Aksi Moderasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/40 transition">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center font-bold text-sm text-gray-700 dark:text-gray-200 flex-shrink-0">
                                        @if($user->selfie_photo)
                                            <img src="{{ asset('storage/' . $user->selfie_photo) }}" alt="{{ $user->name }}" class="w-full h-full object-cover rounded-xl">
                                        @else
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white">{{ $user->name }}</p>
                                        <p class="text-[11px] text-gray-400">{{ $user->phone ?? $user->email }}</p>
                                        @php
                                            $cityName = $user->city_name ?? (is_object($user->city) ? $user->city->name : ($user->city ?? null));
                                        @endphp
                                        @if($cityName)
                                            <span class="text-[10px] text-gray-400">{{ $cityName }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $user->role === 'mitra' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' }}">
                                    {{ $user->role === 'mitra' ? '🛵 Mitra' : '👤 Customer' }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5">
                                <div class="flex flex-col items-start gap-1">
                                    @if($user->is_shadow_banned)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800 animate-pulse">
                                            🚫 SHADOW BANNED
                                        </span>
                                    @endif

                                    @if($user->warning_level > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ $user->warning_level === 3 ? 'bg-rose-100 text-rose-900 dark:bg-rose-950 dark:text-rose-200 border border-rose-300' : ($user->warning_level === 2 ? 'bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-200' : 'bg-yellow-100 text-yellow-900 dark:bg-yellow-950 dark:text-yellow-200') }}">
                                            📢 SP {{ $user->warning_level }} ({{ $user->warning_level === 3 ? 'Peringatan Terakhir' : ($user->warning_level === 2 ? 'Peringatan Sedang' : 'Teguran') }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                            Dalam Pengawasan
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-3.5 max-w-xs">
                                @if($user->latest_warning_message)
                                    <p class="text-xs text-amber-900 dark:text-amber-300 font-medium line-clamp-2">"{{ $user->latest_warning_message }}"</p>
                                @elseif($user->greylist_reason)
                                    <p class="text-xs text-gray-700 dark:text-gray-300 line-clamp-2">{{ $user->greylist_reason }}</p>
                                @else
                                    <span class="text-gray-400 italic">Tidak ada catatan</span>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-gray-400 text-[11px]">
                                {{ $user->greylisted_at ? $user->greylisted_at->format('d/m/Y H:i') : '-' }}
                            </td>

                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    {{-- Tombol Beri SP --}}
                                    <button type="button" @click="selectedUser = {{ $user->id }}; selectedUserName = '{{ addslashes($user->name) }}'; currentWarningLevel = {{ $user->warning_level }}; warningModal = true;"
                                        class="px-2.5 py-1 text-xs font-bold text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 rounded-lg hover:bg-amber-100 transition"
                                        title="Beri Surat Peringatan">
                                        📢 Beri SP
                                    </button>

                                    {{-- Tombol Toggle Shadow Ban --}}
                                    <form method="POST" action="{{ route($routePrefix . 'partners.greylist.shadow_ban', $user) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin {{ $user->is_shadow_banned ? 'mencabut' : 'mengaktifkan' }} Shadow Ban untuk {{ addslashes($user->name) }}?')">
                                        @csrf
                                        <button type="submit"
                                            class="px-2.5 py-1 text-xs font-bold rounded-lg transition {{ $user->is_shadow_banned ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 border border-rose-200 hover:bg-rose-100' }}">
                                            {{ $user->is_shadow_banned ? '🔓 Buka Ban' : '🚫 Shadow Ban' }}
                                        </button>
                                    </form>

                                    {{-- Tombol Pulihkan / Hapus dari Greylist --}}
                                    <form method="POST" action="{{ route($routePrefix . 'partners.greylist.remove', $user) }}" class="inline" onsubmit="return confirm('Hapus {{ addslashes($user->name) }} dari Daftar Abu-Abu dan pulihkan status akun menjadi normal?')">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 text-xs text-gray-500 hover:text-emerald-600 hover:bg-gray-100 rounded-lg transition" title="Pulihkan / Hapus dari Pengawasan">
                                            ✅ Pulihkan
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400 text-xs">
                                Tidak ada user yang sedang berada dalam Daftar Abu-Abu / Pengawasan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL 1: Tambah User ke Daftar Abu-Abu --}}
    <div x-show="addModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-100 dark:border-gray-700 space-y-4" @click.away="addModal = false">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                    <span>⚠️ Masukkan User ke Daftar Abu-Abu</span>
                </h3>
                <button type="button" @click="addModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <form method="POST" action="{{ route($routePrefix . 'partners.greylist.add') }}" class="space-y-3.5">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Pilih User (Mitra / Customer):</label>
                    <select name="user_id" required class="w-full px-3 py-2 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white">
                        <option value="">-- Pilih User Bermasalah --</option>
                        @foreach($candidateUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ strtoupper($u->role) }} • {{ $u->phone ?? $u->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tingkat Surat Peringatan Awal:</label>
                    <select name="warning_level" class="w-full px-3 py-2 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white">
                        <option value="0">Tanpa SP (Hanya Masuk Pengawasan)</option>
                        <option value="1" selected>SP 1 (Peringatan Ringan / Teguran)</option>
                        <option value="2">SP 2 (Peringatan Sedang)</option>
                        <option value="3">SP 3 (Peringatan Keras / Terakhir)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Alasan Pelanggaran / Masuk Pengawasan:</label>
                    <textarea name="reason" rows="2" required placeholder="Contoh: Membatalkan tugas sepihak berulang kali, tidak menyelesaikan pekerjaan, dsb."
                        class="w-full p-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Pesan Teguran Resmi ke User (Opsional):</label>
                    <textarea name="warning_message" rows="2" placeholder="Pesan ini akan tampil di dashboard user sebagai peringatan resmi admin..."
                        class="w-full p-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white resize-none"></textarea>
                </div>

                <div class="flex items-center gap-2 p-2.5 bg-rose-50 dark:bg-rose-950/40 rounded-xl border border-rose-200 dark:border-rose-800">
                    <input type="checkbox" name="shadow_ban" id="modal_shadow_ban" value="1" class="rounded text-rose-600">
                    <label for="modal_shadow_ban" class="text-xs text-rose-900 dark:text-rose-200 font-semibold cursor-pointer">
                        Langsung aktifkan Shadow Ban (Batasi fitur bantuan)
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="addModal = false" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-bold shadow-xs">Simpan ke Pengawasan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL 2: Terbitkan Surat Peringatan (SP) --}}
    <div x-show="warningModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-100 dark:border-gray-700 space-y-4" @click.away="warningModal = false">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                    <span>📢 Terbitkan Surat Peringatan (SP)</span>
                </h3>
                <button type="button" @click="warningModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>

            <form method="POST" :action="'{{ url($routePrefix . 'partners/greylist') }}/' + selectedUser + '/warning'" class="space-y-3.5">
                @csrf

                <div class="p-3 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-800 text-xs text-amber-900 dark:text-amber-200">
                    Penerima SP: <strong x-text="selectedUserName"></strong>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Tingkat Surat Peringatan:</label>
                    <select name="warning_level" required class="w-full px-3 py-2 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white">
                        <option value="1">SP 1 (Peringatan Ringan / Teguran Tertulis)</option>
                        <option value="2">SP 2 (Peringatan Sedang)</option>
                        <option value="3">SP 3 (Peringatan Keras / Batas Terakhir)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Isi Pesan Surat Peringatan:</label>
                    <textarea name="warning_message" rows="3" required placeholder="Tuliskan teguran resmi dan tindakan yang harus diperbaiki oleh pengguna..."
                        class="w-full p-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-white resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="warningModal = false" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-xs">Terbitkan SP</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
