@extends('layouts.admin')

@section('content')

    <div class="p-8">
        <!-- Filter Bar -->
        <div class="mb-6">
            <form method="GET" action="{{ route('admin.users.index') }}"
                class="bg-white rounded-2xl shadow-md border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-gray-700">Filter Pengguna</p>
                    @if (request()->hasAny(['search', 'role', 'account_status', 'ktp_status']))
                        <a href="{{ route('admin.users.index') }}"
                            class="text-[11px] text-gray-400 hover:text-gray-600 underline">Reset filter</a>
                    @endif
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Cari Pengguna</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Nama atau email pengguna"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                            <select name="role"
                                class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="all" {{ request('role') === 'all' ? 'selected' : '' }}>Semua Role</option>
                                <option value="mitra" {{ request('role') === 'mitra' ? 'selected' : '' }}>Mitra</option>
                                <option value="kustomer" {{ request('role') === 'kustomer' ? 'selected' : '' }}>Kustomer</option>
                                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status Akun</label>
                            <select name="account_status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="" {{ request('account_status') === null ? 'selected' : '' }}>Semua</option>
                                <option value="active" {{ request('account_status') === 'active' ? 'selected' : '' }}>Aktif
                                </option>
                                <option value="inactive"
                                    {{ request('account_status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                                <option value="blocked" {{ request('account_status') === 'blocked' ? 'selected' : '' }}>Diblokir
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Status KTP</label>
                            <select name="ktp_status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="" {{ request('ktp_status') === null ? 'selected' : '' }}>Semua</option>
                                <option value="uploaded" {{ request('ktp_status') === 'uploaded' ? 'selected' : '' }}>Sudah
                                    Upload</option>
                                <option value="missing" {{ request('ktp_status') === 'missing' ? 'selected' : '' }}>Belum
                                    Upload</option>
                            </select>
                        </div>

                        <div class="flex justify-start md:justify-end">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-xs font-semibold rounded-xl shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <p class="text-[11px] text-gray-400">Kombinasikan pencarian, role, status akun, dan status KTP untuk
                        menemukan pengguna secara cepat.</p>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Daftar Pengguna</h2>
                    <p class="text-xs text-gray-500 mt-1">Pantau dan kelola akun pengguna yang terdaftar di platform.</p>
                </div>
                @if ($users->total() > 0)
                    <p class="text-[11px] text-gray-500">Total {{ $users->total() }} pengguna.</p>
                @endif
            </div>

            @if ($users->isEmpty())
                <div class="px-6 py-12 flex flex-col items-center justify-center text-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a4 4 0 00-5-4M9 20H4v-2a4 4 0 015-4m4-6a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <p class="text-gray-500 text-sm font-medium">Belum ada pengguna di kota Anda.</p>
                    <p class="text-gray-400 text-xs mt-1">Pengguna baru akan muncul di sini setelah mereka mendaftar.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Nama
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Role
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Kota
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Terdaftar
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Rating
                                </th>
                                
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Status Akun
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 text-sm text-gray-900 whitespace-nowrap">
                                        {{ $user->name }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-500 whitespace-nowrap">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-500 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-500 whitespace-nowrap">
                                        @if(!empty($user->city_name))
                                            {{ $user->city_name }}
                                        @elseif($user->city_id)
                                            {{ $user->city_name ?? $user->city_id }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-500 whitespace-nowrap">
                                        {{ optional($user->created_at)->format('Y-m-d') ?? '-' }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-right whitespace-nowrap">
                                        @php
                                            $ratingsCount = $user->ratings_count ?? ($user->ratings_count ?? 0);
                                            $avgRating = $user->average_rating ?? null;
                                        @endphp

                                        @if (!is_null($avgRating) && $ratingsCount > 0)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                                {{ number_format($avgRating, 1) }}
                                            </span>
                                            <span class="text-[11px] text-gray-600 ml-2">({{ $ratingsCount }})</span>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-3 text-sm">
                                        @if ($user->status === 'blocked')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                Diblokir
                                            </span>
                                        @elseif ($user->status === 'inactive')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                Nonaktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                Aktif
                                            </span>
                                        @endif
                                    </td>
                                    {{-- removed Bantuan and Pelanggaran columns; rating placed earlier --}}
                                    <td class="px-6 py-3 text-sm text-right space-x-2 whitespace-nowrap">
                                        <a href="#" data-url="{{ route('admin.users.show', $user) }}" class="open-user-detail inline-flex items-center px-3 py-1 border border-gray-300 rounded-full text-xs text-gray-700 hover:bg-gray-50">
                                            Detail
                                        </a>

                                        <form action="{{ route('admin.partners.toggle', $user->id) }}" method="POST" class="inline block-toggle-form" id="block-form-{{ $user->id }}" data-user-name="{{ $user->name }}" data-is-blocked="{{ $user->status === 'blocked' ? '1' : '0' }}">
                                            @csrf
                                            <button
                                                class="px-3 py-1 rounded-full text-xs {{ $user->status === 'blocked' ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-red-600 text-white hover:bg-red-700' }}">
                                                {{ $user->status === 'blocked' ? 'Buka Blokir' : 'Blokir' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-center">
                        {{ $users->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection

<!-- Confirm Block Modal -->
<div id="confirm-block-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div id="confirm-block-backdrop" class="absolute inset-0 bg-black bg-opacity-40"></div>
    <div class="relative bg-white rounded-lg shadow-lg w-full max-w-md z-10">
        <div class="p-4 border-b flex items-center justify-between">
            <h3 class="text-lg font-semibold">Konfirmasi</h3>
            <button id="confirm-block-close" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <div class="p-4">
            <p id="confirm-block-message" class="text-sm text-gray-700">Apakah Anda yakin ingin memblokir pengguna ini?</p>
        </div>
        <div class="p-4 border-t flex justify-end gap-2">
            <button id="confirm-block-cancel" class="px-4 py-2 bg-gray-100 rounded">Batal</button>
            <button id="confirm-block-confirm" class="px-4 py-2 bg-red-600 text-white rounded">Konfirmasi</button>
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
                    // create wrapper and insert modal HTML
                    var wrapper = document.createElement('div');
                    wrapper.id = 'user-detail-modal-wrapper';
                    wrapper.innerHTML = html;
                    document.body.appendChild(wrapper);
                    // attach listeners to modal elements inside the wrapper
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

        // Block/unblock confirmation modal handling
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
                blockConfirm.classList.remove('bg-red-600');
                blockConfirm.classList.add('bg-green-600');
            } else {
                blockMsg.textContent = 'Apakah Anda yakin ingin memblokir pengguna "' + name + '"?';
                blockConfirm.textContent = 'Konfirmasi';
                blockConfirm.classList.remove('bg-green-600');
                blockConfirm.classList.add('bg-red-600');
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

        // Intercept submit on block forms
        document.addEventListener('submit', function(ev){
            var f = ev.target.closest && ev.target.closest('.block-toggle-form');
            if (!f) return;
            ev.preventDefault();
            showBlockModal(f);
        }, true);

        // Delegate clicks for modal buttons (in case init runs before modal exists)
        document.addEventListener('click', function(ev){
            var t = ev.target;
            if (!t) return;
            if (t.id === 'confirm-block-cancel' || t.id === 'confirm-block-close' || t.id === 'confirm-block-backdrop'){
                hideBlockModal();
            }
            if (t.id === 'confirm-block-confirm'){
                if (blockFormToSubmit) {
                    // submit the original form
                    blockFormToSubmit.submit();
                }
            }
        });
    })();
</script>
@endpush
