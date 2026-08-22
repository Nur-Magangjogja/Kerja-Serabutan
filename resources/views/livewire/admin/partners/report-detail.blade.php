@extends(in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'layouts.superadmin' : 'layouts.admin')

@section('content')
@php
    $routePrefix = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'superadmin.' : 'admin.';
@endphp
<div class="space-y-6">
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Detail Laporan Aduan #{{ $report->id }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Informasi lengkap dan tindakan moderasi untuk aduan</p>
        </div>
        <a href="{{ route($routePrefix . 'partners.report') }}"
            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    {{-- ===== Main Content ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Informasi Laporan --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Informasi Laporan</h2>
                    @php
                    $stClass = match($report->status) {
                        'pending'     => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400',
                        'in_progress' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400',
                        'resolved'    => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400',
                        default       => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
                    };
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $stClass }}">
                        {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                    </span>
                </div>

                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Judul</p>
                        <p class="font-bold text-gray-900 dark:text-white mt-0.5">{{ $report->title }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-lg">
                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-1">Pesan / Keluhan</p>
                        <p class="text-gray-700 dark:text-gray-200 whitespace-pre-line leading-relaxed">{{ $report->message }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <p class="text-gray-400 dark:text-gray-500">Jenis Laporan</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 mt-1">
                                {{ $report->report_type_label }}
                            </span>
                        </div>
                        <div>
                            <p class="text-gray-400 dark:text-gray-500">Kategori</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $report->isFromCustomer() ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' }} mt-1">
                                {{ $report->category_label }}
                            </span>
                        </div>
                        <div>
                            <p class="text-gray-400 dark:text-gray-500">Tanggal Dibuat</p>
                            <p class="font-medium text-gray-700 dark:text-gray-300 mt-0.5">{{ $report->created_at->format('d M Y, H:i') }} WIB</p>
                        </div>
                        @if ($report->resolved_at)
                        <div>
                            <p class="text-gray-400 dark:text-gray-500">Tanggal Selesai</p>
                            <p class="font-medium text-gray-700 dark:text-gray-300 mt-0.5">{{ $report->resolved_at->format('d M Y, H:i') }} WIB</p>
                            @if ($report->resolvedBy)
                                <p class="text-[11px] text-gray-400">Oleh: {{ $report->resolvedBy->name }}</p>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Reporter & Terlapor Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Reporter --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 space-y-3">
                    <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Pelapor (Reporter)</h3>
                    @php $rep = $report->reporter ?? $report->user; @endphp
                    @if ($rep)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-bold text-gray-900 dark:text-white">{{ $rep->name }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $rep->email }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 mt-1">
                                    {{ ucfirst($rep->role) }}
                                </span>
                            </div>
                            <a href="{{ route('admin.users.show', $rep) }}"
                                class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                Profil
                            </a>
                        </div>
                    @else
                        <p class="text-xs text-gray-400">Data pelapor tidak tersedia</p>
                    @endif
                </div>

                {{-- Terlapor --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 space-y-3">
                    <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Yang Dilaporkan</h3>
                    @if ($report->reportedUser)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-bold text-gray-900 dark:text-white">{{ $report->reportedUser->name }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $report->reportedUser->email }}</p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 mt-1">
                                    {{ ucfirst($report->reportedUser->role) }}
                                </span>
                            </div>
                            <a href="{{ route('admin.users.show', $report->reportedUser) }}"
                                class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                Profil
                            </a>
                        </div>
                    @elseif ($report->reportedHelp)
                        <div>
                            <p class="font-semibold text-primary-600 dark:text-primary-400">Bantuan #{{ $report->reportedHelp->id }}</p>
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">{{ $report->reportedHelp->title }}</p>
                        </div>
                    @else
                        <p class="text-xs text-gray-400">Tidak ada user/bantuan yang dilaporkan secara spesifik</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar Actions --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Update Status Aduan</h3>
                <form method="POST" action="{{ route($routePrefix . 'partners.reports.update', $report) }}" class="space-y-2">
                    @csrf
                    <select name="status"
                        class="w-full py-2 px-3 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ $report->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                    </select>
                    <button type="submit"
                        class="w-full py-2 px-4 text-xs font-semibold bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        Simpan Status
                    </button>
                </form>

                @if (!$report->isResolved())
                    <form method="POST" action="{{ route($routePrefix . 'partners.reports.resolve', $report) }}" class="pt-2 border-t border-gray-100 dark:border-gray-700">
                        @csrf
                        <button type="submit"
                            class="w-full py-2 px-4 text-xs font-semibold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                            Tandai Selesai (Resolved)
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route($routePrefix . 'partners.reports.reopen', $report) }}" class="pt-2 border-t border-gray-100 dark:border-gray-700">
                        @csrf
                        <button type="submit"
                            class="w-full py-2 px-4 text-xs font-semibold bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Buka Kembali Aduan
                        </button>
                    </form>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Catatan Internal Admin</h3>
                @if ($report->admin_notes)
                    <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-lg text-xs text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                        {{ $report->admin_notes }}
                    </div>
                @endif
                <form method="POST" action="{{ route($routePrefix . 'partners.reports.add-note', $report) }}" class="space-y-2">
                    @csrf
                    <textarea name="admin_notes" rows="3" placeholder="Tulis catatan penanganan kasus..."
                        class="w-full p-2.5 text-xs border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">{{ $report->admin_notes }}</textarea>
                    <button type="submit"
                        class="w-full py-2 px-4 text-xs font-semibold bg-gray-800 dark:bg-gray-600 text-white rounded-lg hover:bg-gray-900 dark:hover:bg-gray-500 transition-colors">
                        {{ $report->admin_notes ? 'Perbarui Catatan' : 'Tambah Catatan' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
