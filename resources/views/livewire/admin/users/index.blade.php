@extends('layouts.admin')

@section('content')
<div class="space-y-5">
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Kelola Pengguna</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Pantau dan kelola akun mitra & customer di kota Anda</p>
        </div>
        @if ($users->total() > 0)
        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 px-3 py-1.5 rounded-lg">
            Total {{ number_format($users->total()) }} Pengguna
        </span>
        @endif
    </div>

    {{-- ===== Inline Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3.5 shadow-sm">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Cari Pengguna</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama atau email pengguna..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Role</label>
                <select name="role"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all" {{ request('role') === 'all' ? 'selected' : '' }}>Semua Role</option>
                    <option value="mitra" {{ request('role') === 'mitra' ? 'selected' : '' }}>Mitra</option>
                    <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Customer</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Status Akun</label>
                <select name="account_status"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="" {{ request('account_status') === null ? 'selected' : '' }}>Semua</option>
                    <option value="active" {{ request('account_status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('account_status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    <option value="blocked" {{ request('account_status') === 'blocked' ? 'selected' : '' }}>Diblokir</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Status KTP</label>
                <select name="ktp_status"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="" {{ request('ktp_status') === null ? 'selected' : '' }}>Semua</option>
                    <option value="uploaded" {{ request('ktp_status') === 'uploaded' ? 'selected' : '' }}>Sudah Upload</option>
                    <option value="missing" {{ request('ktp_status') === 'missing' ? 'selected' : '' }}>Belum Upload</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 text-sm font-semibold bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'role', 'account_status', 'ktp_status']))
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        @if ($users->isEmpty())
            <div class="px-4 py-16 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-5-4M9 20H4v-2a4 4 0 015-4m4-6a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada pengguna di kota Anda</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Pengguna baru akan muncul di sini setelah mereka mendaftar</p>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengguna</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Kota</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell">Terdaftar</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Rating</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status & Aktivitas</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @foreach ($users as $user)
                        @php
                        $roleClass = match($user->role) {
                            'mitra'    => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400',
                            'admin'    => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400',
                            default    => 'bg-violet-100 dark:bg-violet-900/40 text-violet-700 dark:text-violet-400',
                        };
                        $statusClass = match($user->status) {
                            'blocked'  => 'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400',
                            'inactive' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400',
                            default    => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400',
                        };
                        $avgRating = $user->average_rating ?? null;
                        $ratingsCount = $user->ratings_count ?? 0;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="#" data-url="{{ route('admin.users.show', $user) }}" class="open-user-detail font-semibold text-gray-800 dark:text-gray-100 hover:text-primary-600 dark:hover:text-primary-400 truncate block">
                                            {{ $user->name }}
                                        </a>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $roleClass }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300 hidden md:table-cell">
                                {{ $user->city_name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-gray-400 dark:text-gray-500 hidden lg:table-cell whitespace-nowrap">
                                {{ optional($user->created_at)->format('d M Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 hidden sm:table-cell">
                                @if (!is_null($avgRating) && $ratingsCount > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                        ★ {{ number_format($avgRating, 1) }}
                                        <span class="text-gray-400 dark:text-gray-500 font-normal">({{ $ratingsCount }})</span>
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="space-y-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                                        {{ $user->status === 'blocked' ? 'Diblokir' : ($user->status === 'inactive' ? 'Nonaktif' : 'Aktif') }}
                                    </span>
                                    <div class="flex items-center gap-1 text-[11px] text-gray-500 dark:text-gray-400" title="{{ $user->last_activity_at ? 'Aktivitas terbaru: ' . $user->last_activity_at->translatedFormat('d M Y, H:i') . ' WIB' : 'Belum ada riwayat aktivitas bantuan' }}">
                                        <svg class="w-3 h-3 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="truncate">{{ $user->last_activity_for_humans }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="#" data-url="{{ route('admin.users.show', $user) }}"
                                        class="open-user-detail inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 border border-primary-100 dark:border-primary-800 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Detail
                                    </a>

                                    <form action="{{ route('admin.partners.toggle', $user->id) }}" method="POST" class="inline block-toggle-form" id="block-form-{{ $user->id }}" data-user-name="{{ $user->name }}" data-is-blocked="{{ $user->status === 'blocked' ? '1' : '0' }}">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-semibold {{ $user->status === 'blocked' ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-rose-600 text-white hover:bg-rose-700' }} transition-colors">
                                            {{ $user->status === 'blocked' ? 'Buka Blokir' : 'Blokir' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                    {{ $users->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

{{-- Confirm Block Modal with password verification --}}
<div id="confirm-block-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div id="confirm-block-backdrop" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md z-10 border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Konfirmasi Tindakan</h3>
            <button id="confirm-block-close" class="p-1 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">&times;</button>
        </div>
        <div class="p-6 space-y-4">
            <p id="confirm-block-message" class="text-sm text-gray-600 dark:text-gray-300">Apakah Anda yakin ingin melakukan tindakan ini pada pengguna?</p>

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
                    <input type="password" id="adminUserBlockPassword"
                        placeholder="Masukkan kata sandi Admin Anda"
                        class="w-full px-3 py-2 text-xs border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    <p id="adminUserBlockPassError" class="text-xs text-rose-600 dark:text-rose-400 mt-1 hidden font-medium"></p>
                </div>
            </div>
        </div>
        <div class="px-6 py-3.5 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
            <button id="confirm-block-cancel" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">Batal</button>
            <button id="confirm-block-confirm" class="px-4 py-2 text-sm font-semibold bg-rose-600 text-white rounded-lg hover:bg-rose-700 transition-colors">Konfirmasi</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function(){
        if (window.__adminUserDetailScriptLoaded) return;
        window.__adminUserDetailScriptLoaded = true;

        var isFetching = false;
        var currentModal = null;

        function closeModal(wrapper){
            var target = wrapper || currentModal || document.getElementById('user-detail-modal');
            if (target && target.parentNode) {
                target.parentNode.removeChild(target);
            }
            document.body.style.overflow = '';
            document.removeEventListener('keydown', onKeyDown);
            currentModal = null;
        }

        function onKeyDown(e){
            if (e.key === 'Escape') {
                closeModal();
            }
        }

        function setupModalListeners(wrapper){
            if (!wrapper) return;
            currentModal = wrapper;
            document.body.style.overflow = 'hidden';

            var closeBtn = wrapper.querySelector('#modal-close-btn');
            var closeBtn2 = wrapper.querySelector('#modal-close-btn-2');
            var backdrop = wrapper.querySelector('#modal-backdrop');

            if (closeBtn) closeBtn.addEventListener('click', function(){ closeModal(wrapper); });
            if (closeBtn2) closeBtn2.addEventListener('click', function(){ closeModal(wrapper); });
            if (backdrop) backdrop.addEventListener('click', function(){ closeModal(wrapper); });
            document.addEventListener('keydown', onKeyDown);
        }

        document.addEventListener('click', function(e){
            var el = e.target.closest && e.target.closest('.open-user-detail');
            if (!el) return;
            e.preventDefault();
            e.stopPropagation();

            if (isFetching) return;

            var url = el.getAttribute('data-url') || el.getAttribute('href');
            if (!url) return;

            // Remove any existing user detail modal first
            var existingModal = document.getElementById('user-detail-modal');
            if (existingModal) {
                existingModal.remove();
            }

            isFetching = true;
            el.style.opacity = '0.6';

            fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(res){
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.text();
                })
                .then(function(html){
                    var old = document.getElementById('user-detail-modal');
                    if (old) old.remove();

                    var div = document.createElement('div');
                    div.innerHTML = html.trim();
                    var modal = div.firstElementChild;
                    if (modal) {
                        modal.id = 'user-detail-modal';
                        document.body.appendChild(modal);
                        setupModalListeners(modal);
                    }
                })
                .catch(function(err){
                    console.error('Failed to load user detail modal', err);
                    window.location.href = url;
                })
                .finally(function(){
                    isFetching = false;
                    el.style.opacity = '';
                });
        });

        // Block confirmation modal logic with password verification
        var blockModal = null;
        var blockBackdrop = null;
        var blockMsg = null;
        var blockCancel = null;
        var blockClose = null;
        var blockConfirm = null;
        var blockPassword = null;
        var blockPassError = null;
        var blockFormToSubmit = null;

        function initBlockModalElements(){
            blockModal = document.getElementById('confirm-block-modal');
            if (!blockModal) return false;
            blockBackdrop = document.getElementById('confirm-block-backdrop');
            blockMsg = document.getElementById('confirm-block-message');
            blockCancel = document.getElementById('confirm-block-cancel');
            blockClose = document.getElementById('confirm-block-close');
            blockConfirm = document.getElementById('confirm-block-confirm');
            blockPassword = document.getElementById('adminUserBlockPassword');
            blockPassError = document.getElementById('adminUserBlockPassError');
            return true;
        }

        function showBlockModal(form){
            if (!initBlockModalElements()) return;
            blockFormToSubmit = form;
            var name = form.dataset.userName || '';
            var isBlocked = (form.dataset.isBlocked === '1');

            if (isBlocked) {
                blockMsg.textContent = 'Apakah Anda yakin ingin membuka blokir pengguna "' + name + '"? Pengguna akan dapat masuk kembali.';
                blockConfirm.textContent = 'Buka Blokir';
                blockConfirm.className = 'px-4 py-2 text-sm font-semibold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors';
            } else {
                blockMsg.textContent = 'Apakah Anda yakin ingin memblokir pengguna "' + name + '"? Pengguna tidak akan dapat mengakses aplikasi.';
                blockConfirm.textContent = 'Blokir Pengguna';
                blockConfirm.className = 'px-4 py-2 text-sm font-semibold bg-rose-600 text-white rounded-lg hover:bg-rose-700 transition-colors';
            }

            if (blockPassword) blockPassword.value = '';
            if (blockPassError) {
                blockPassError.classList.add('hidden');
                blockPassError.textContent = '';
            }

            blockModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(function(){ if (blockPassword) blockPassword.focus(); }, 50);
        }

        function hideBlockModal(){
            if (!initBlockModalElements()) return;
            blockModal.classList.add('hidden');
            document.body.style.overflow = '';
            blockFormToSubmit = null;
            if (blockPassword) blockPassword.value = '';
            if (blockPassError) blockPassError.classList.add('hidden');
        }

        document.addEventListener('submit', function(ev){
            var f = ev.target.closest && ev.target.closest('.block-toggle-form');
            if (!f) return;
            ev.preventDefault();
            showBlockModal(f);
        }, true);

        document.addEventListener('click', function(ev){
            var t = ev.target;
            if (!t) return;
            if (t.id === 'confirm-block-cancel' || t.id === 'confirm-block-close' || t.id === 'confirm-block-backdrop'){
                hideBlockModal();
            }
            if (t.id === 'confirm-block-confirm'){
                if (!blockFormToSubmit) return hideBlockModal();

                var passVal = blockPassword ? blockPassword.value.trim() : '';
                if (!passVal) {
                    if (blockPassError) {
                        blockPassError.textContent = 'Kata sandi Admin wajib dimasukkan untuk mengonfirmasi tindakan ini.';
                        blockPassError.classList.remove('hidden');
                    }
                    if (blockPassword) blockPassword.focus();
                    return;
                }

                var hiddenPass = blockFormToSubmit.querySelector('input[name="admin_password"]');
                if (!hiddenPass) {
                    hiddenPass = document.createElement('input');
                    hiddenPass.type = 'hidden';
                    hiddenPass.name = 'admin_password';
                    blockFormToSubmit.appendChild(hiddenPass);
                }
                hiddenPass.value = passVal;

                blockFormToSubmit.submit();
                hideBlockModal();
            }
        });
    })();
</script>
@endpush
@endsection
