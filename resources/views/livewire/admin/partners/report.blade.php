@extends(in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'layouts.superadmin' : 'layouts.admin')

@section('content')
@php
    $routePrefix = in_array(auth()->user()->role ?? '', ['super_admin', 'superadmin']) ? 'superadmin.' : 'admin.';
@endphp
<div class="space-y-5">
    {{-- ===== Page Header ===== --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Laporan Aduan</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola aduan masalah dari customer dan mitra</p>
        </div>
        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 px-3 py-1.5 rounded-lg">
            Total {{ number_format($reports->total()) }} Laporan
        </span>
    </div>

    {{-- ===== Summary Cards ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
            <p class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $totalPending }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">In Progress</p>
            <p class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $totalInProgress }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">Resolved</p>
            <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $totalResolved }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">Dismissed</p>
            <p class="text-xl font-bold text-gray-600 dark:text-gray-400 mt-1">{{ $totalDismissed }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">Customer</p>
            <p class="text-xl font-bold text-primary-600 dark:text-primary-400 mt-1">{{ $totalFromCustomer }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">Mitra</p>
            <p class="text-xl font-bold text-violet-600 dark:text-violet-400 mt-1">{{ $totalFromMitra }}</p>
        </div>
    </div>

    {{-- ===== Filter Toolbar ===== --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl px-4 py-3.5 shadow-sm">
        <form method="GET" action="{{ route('admin.partners.report') }}" class="flex flex-wrap items-end gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Cari Laporan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama reporter, judul, kata kunci..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Status</label>
                <select name="status"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ $status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="dismissed" {{ $status === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Kategori</label>
                <select name="category"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all" {{ $category === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                    <option value="dari_customer" {{ $category === 'dari_customer' ? 'selected' : '' }}>Dari Customer</option>
                    <option value="dari_mitra" {{ $category === 'dari_mitra' ? 'selected' : '' }}>Dari Mitra</option>
                </select>
            </div>

            <div>
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">Jenis Laporan</label>
                <select name="report_type"
                    class="py-2 pl-3 pr-8 text-sm border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all" {{ $reportType === 'all' ? 'selected' : '' }}>Semua Jenis</option>
                    @foreach ($reportTypes as $key => $label)
                        <option value="{{ $key }}" {{ $reportType === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 text-sm font-semibold bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Filter
                </button>
                @if (request()->hasAny(['status', 'category', 'report_type', 'search', 'start_date', 'end_date']))
                <a href="{{ route($routePrefix . 'partners.report') }}" class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ===== Table Card ===== --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        @if ($reports->isEmpty())
            <div class="px-4 py-16 text-center">
                <div class="flex flex-col items-center">
                    <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                        <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada laporan aduan</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Laporan baru akan muncul di sini</p>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reporter</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dilaporkan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell">Jenis</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:table-cell">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @foreach ($reports as $report)
                        @php
                        $stClass = match($report->status) {
                            'pending'     => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400',
                            'in_progress' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400',
                            'resolved'    => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400',
                            default       => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'
                        };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors duration-150">
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs">
                                <p class="font-medium text-gray-800 dark:text-gray-200">{{ $report->created_at->format('d M Y') }}</p>
                                <p class="text-gray-400 dark:text-gray-500">{{ $report->created_at->format('H:i') }}</p>
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($report->reporter)
                                    <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $report->reporter->name }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $report->reporter->email }}</p>
                                @elseif ($report->user)
                                    <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $report->user->name }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $report->user->email }}</p>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                @if ($report->reportedUser)
                                    <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $report->reportedUser->name }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $report->reportedUser->email }}</p>
                                @elseif ($report->reportedHelp)
                                    <p class="font-semibold text-primary-600 dark:text-primary-400">Bantuan #{{ $report->reportedHelp->id }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate max-w-xs">{{ $report->reportedHelp->title }}</p>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 hidden md:table-cell">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                                    {{ $report->report_type_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 hidden sm:table-cell">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $report->isFromCustomer() ? 'bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400' : 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' }}">
                                    {{ $report->category_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex flex-col items-start gap-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $stClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                    </span>
                                    @if ($report->refund_status === 'requested')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200">
                                            Klaim Refund
                                        </span>
                                    @elseif ($report->refund_status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                            Refund Disetujui
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route($routePrefix . 'partners.reports.chat', $report) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-950/60 border border-primary-200 dark:border-primary-800 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900 transition-colors shadow-2xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        <span>Chat</span>
                                        @if($report->messages_count > 0)
                                            <span class="w-4 h-4 rounded-full bg-primary-600 text-white text-[9px] flex items-center justify-center font-extrabold">{{ $report->messages_count }}</span>
                                        @endif
                                    </a>
                                    <a href="{{ route($routePrefix . 'partners.reports.show', $report) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($reports->hasPages())
                <div class="px-5 py-3.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                    {{ $reports->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
