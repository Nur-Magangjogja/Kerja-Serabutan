@extends('layouts.admin')

@section('content')
<div class="space-y-5">
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Kelola Pengguna</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Pantau dan kelola akun mitra & kustomer di kota Anda</p>
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
                    <option value="kustomer" {{ request('role') === 'kustomer' ? 'selected' : '' }}>Kustomer</option>
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
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
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
                        $ratingsCount = $user->ratings_count ?? 0;
                        $avgRating = $user->average_rating ?? null;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $user->name }}</p>
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
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                                    {{ $user->status === 'blocked' ? 'Diblokir' : ($user->status === 'inactive' ? 'Nonaktif' : 'Aktif') }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="#" data-url="{{ route('admin.users.show', $user) }}"
                                        class="open-user-detail inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
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

{{-- Confirm Block Modal --}}
<div id="confirm-block-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div id="confirm-block-backdrop" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md z-10 border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 dark:text-white">Konfirmasi Tindakan</h3>
            <button id="confirm-block-close" class="p-1 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">&times;</button>
        </div>
        <div class="p-6">
            <p id="confirm-block-message" class="text-sm text-gray-600 dark:text-gray-300">Apakah Anda yakin ingin melakukan tindakan ini pada pengguna?</p>
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
        function setupModalListeners(wrapper){
            if (!wrapper) return;
            var closeBtn = wrapper.querySelector('#modal-close-btn');
            var closeBtn2 = wrapper.querySelector('#modal-close-btn-2');
            var backdrop = wrapper.querySelector('#modal-backdrop');

            function removeWrapper(){
                if (wrapper && wrapper.parentNode) wrapper.parentNode.removeChild(wrapper);
                document.removeEventListener('keydown', onKeyDown);
            }

            function onKeyDown(e){
                if (e.key === 'Escape') removeWrapper();
            }

            if (closeBtn) closeBtn.addEventListener('click', removeWrapper);
            if (closeBtn2) closeBtn2.addEventListener('click', removeWrapper);
            if (backdrop) backdrop.addEventListener('click', removeWrapper);
            document.addEventListener('keydown', onKeyDown);
        }

        function openUserDetail(url){
            fetch(url, {headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                .then(function(res){
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.text();
                })
                .then(function(html){
                    var wrapper = document.createElement('div');
                    wrapper.id = 'user-detail-modal-wrapper';
                    wrapper.innerHTML = html;
                    document.body.appendChild(wrapper);
                    setupModalListeners(wrapper);
                })
                .catch(function(err){
                    console.error('Failed to load user detail:', err);
                    alert('Gagal memuat detail pengguna. Coba lagi.');
                });
        }

        document.addEventListener('click', function(e){
            var el = e.target.closest && e.target.closest('.open-user-detail');
            if (!el) return;
            e.preventDefault();
            var url = el.getAttribute('data-url');
            if (url) openUserDetail(url);
        });

        // Block/unblock confirmation modal
        var blockModal = null;
        var blockBackdrop = null;
        var blockMsg = null;
        var blockCancel = null;
        var blockClose = null;
        var blockConfirm = null;
        var blockFormToSubmit = null;

        function initBlockModalElements(){
            blockModal = document.getElementById('confirm-block-modal');
            if (!blockModal) return false;
            blockBackdrop = document.getElementById('confirm-block-backdrop');
            blockMsg = document.getElementById('confirm-block-message');
            blockCancel = document.getElementById('confirm-block-cancel');
            blockClose = document.getElementById('confirm-block-close');
            blockConfirm = document.getElementById('confirm-block-confirm');
            return true;
        }

        function showBlockModal(form){
            if (!initBlockModalElements()) return;
            blockFormToSubmit = form;
            var name = form.dataset.userName || '';
            var isBlocked = (form.dataset.isBlocked === '1');

            if (isBlocked) {
                blockMsg.textContent = 'Apakah Anda yakin ingin membuka blokir pengguna "' + name + '"?';
                blockConfirm.textContent = 'Buka Blokir';
                blockConfirm.className = 'px-4 py-2 text-sm font-semibold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors';
            } else {
                blockMsg.textContent = 'Apakah Anda yakin ingin memblokir pengguna "' + name + '"?';
                blockConfirm.textContent = 'Blokir Pengguna';
                blockConfirm.className = 'px-4 py-2 text-sm font-semibold bg-rose-600 text-white rounded-lg hover:bg-rose-700 transition-colors';
            }

            blockModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function hideBlockModal(){
            if (!initBlockModalElements()) return;
            blockModal.classList.add('hidden');
            document.body.style.overflow = '';
            blockFormToSubmit = null;
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
                if (blockFormToSubmit) {
                    blockFormToSubmit.submit();
                }
            }
        });
    })();
</script>
@endpush
@endsection
