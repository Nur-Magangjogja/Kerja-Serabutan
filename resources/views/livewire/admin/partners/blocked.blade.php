@extends(in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'layouts.superadmin' : 'layouts.admin')

@section('content')
@php
    $routePrefix = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'superadmin.' : 'admin.';
@endphp
<div class="space-y-5">
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Blokir & Akses Mitra</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola status pemblokiran dan hak akses pengguna</p>
        </div>
    </div>

    {{-- ===== Summary Cards ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $counts['total'] ?? ($blocked->total ?? $blocked->count()) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-900/40 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Diblokir</p>
                <p class="text-lg font-bold text-rose-600 dark:text-rose-400">{{ $counts['blocked'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/40 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Aktif</p>
                <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $counts['active'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/40 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Mitra</p>
                <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $counts['mitra'] ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-900/40 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Customer</p>
                <p class="text-lg font-bold text-violet-600 dark:text-violet-400">{{ $counts['customer'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    {{-- ===== Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3.5 shadow-sm">
        <form method="GET" action="{{ route($routePrefix . 'partners.blocked') }}" class="flex flex-wrap items-end gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Cari Pengguna</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input id="partner-search" type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau telepon..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Role</label>
                <select name="role"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua Role</option>
                    <option value="mitra" {{ request('role')=='mitra' ? 'selected' : '' }}>Mitra</option>
                    <option value="customer" {{ request('role')=='customer' ? 'selected' : '' }}>Customer</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Status</label>
                <select name="status"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status')=='inactive' ? 'selected' : '' }}>Nonaktif</option>
                    <option value="blocked" {{ request('status')=='blocked' ? 'selected' : '' }}>Diblokir</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 text-sm font-semibold bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Filter
                </button>
                @if (request()->hasAny(['search', 'role', 'status']))
                <a href="{{ route($routePrefix . 'partners.blocked') }}" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        @if ($blocked->isEmpty())
            <div class="px-4 py-16 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9V5a4 4 0 118 0v4m-1 4H9m10 0a2 2 0 01-2 2H9a2 2 0 01-2-2m12 0V9a2 2 0 00-2-2h-1M7 13v-2a2 2 0 012-2h1" /></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada pengguna yang ditemukan</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Pengguna dapat diblokir atau diaktifkan kembali dari halaman ini</p>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Role</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Kota</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status & Aktivitas</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @foreach ($blocked as $user)
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
                        $cityRelation = $user->getRelation('city');
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route($routePrefix . 'users.show', $user) }}" data-url="{{ route($routePrefix . 'users.show', $user) }}" class="open-user-detail font-semibold text-gray-800 dark:text-gray-100 hover:text-primary-600 dark:hover:text-primary-400 truncate block">
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
                                @if($cityRelation && is_object($cityRelation))
                                    {{ $cityRelation->name }}
                                @else
                                    —
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
                                    <a href="{{ route($routePrefix . 'users.show', $user) }}"
                                        data-url="{{ route($routePrefix . 'users.show', $user) }}"
                                        class="open-user-detail inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 border border-primary-100 dark:border-primary-800 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Detail
                                    </a>
                                    <a href="{{ route($routePrefix . 'partners.activity', ['search' => $user->email]) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        Aktivitas
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 flex items-center justify-between">
                <span class="text-xs text-gray-500 dark:text-gray-400">Total {{ $blocked->total() ?? $blocked->count() }} pengguna</span>
                <div>
                    {{ $blocked->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
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
    })();
</script>
@endpush
@endsection
