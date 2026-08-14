@extends('layouts.admin')

@section('content')

    <div class="p-8">
        <!-- Statistik Ringkasan -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-md p-4 border border-gray-200">
                <div class="text-xs font-semibold text-gray-500 mb-1">Pending</div>
                <div class="text-2xl font-bold text-yellow-600">{{ $totalPending }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 border border-gray-200">
                <div class="text-xs font-semibold text-gray-500 mb-1">In Progress</div>
                <div class="text-2xl font-bold text-blue-600">{{ $totalInProgress }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 border border-gray-200">
                <div class="text-xs font-semibold text-gray-500 mb-1">Resolved</div>
                <div class="text-2xl font-bold text-green-600">{{ $totalResolved }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 border border-gray-200">
                <div class="text-xs font-semibold text-gray-500 mb-1">Dismissed</div>
                <div class="text-2xl font-bold text-gray-600">{{ $totalDismissed }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 border border-gray-200">
                <div class="text-xs font-semibold text-gray-500 mb-1">Dari Customer</div>
                <div class="text-2xl font-bold text-primary-600">{{ $totalFromCustomer }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-4 border border-gray-200">
                <div class="text-xs font-semibold text-gray-500 mb-1">Dari Mitra</div>
                <div class="text-2xl font-bold text-amber-600">{{ $totalFromMitra }}</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 mb-6">
            <form method="GET" action="{{ route('admin.partners.report') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Status</label>
                        <select name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ $status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="dismissed" {{ $status === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Kategori</label>
                        <select name="category"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="all" {{ $category === 'all' ? 'selected' : '' }}>Semua Kategori</option>
                            <option value="dari_customer" {{ $category === 'dari_customer' ? 'selected' : '' }}>Dari Customer</option>
                            <option value="dari_mitra" {{ $category === 'dari_mitra' ? 'selected' : '' }}>Dari Mitra</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Jenis Laporan</label>
                        <select name="report_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="all" {{ $reportType === 'all' ? 'selected' : '' }}>Semua Jenis</option>
                            @foreach ($reportTypes as $key => $label)
                                <option value="{{ $key }}" {{ $reportType === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex-1 mr-4">
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Cari</label>
                        <input type="text" name="search" value="{{ $search }}"
                            placeholder="Nama reporter, user yang dilaporkan, atau kata kunci..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit"
                            class="px-6 py-2 bg-primary-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Terapkan Filter
                        </button>
                        @if (request()->hasAny(['status', 'category', 'report_type', 'search', 'start_date', 'end_date']))
                            <a href="{{ route('admin.partners.report') }}"
                                class="px-6 py-2 bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-300">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Laporan -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Daftar Laporan Aduan</h2>
                    <p class="text-xs text-gray-500 mt-1">Total {{ $reports->total() }} laporan ditemukan.</p>
                </div>
            </div>

            @if ($reports->isEmpty())
                <div class="px-6 py-12 flex flex-col items-center justify-center text-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-gray-500 text-sm font-medium">Belum ada laporan aduan.</p>
                    <p class="text-gray-400 text-xs mt-1">Laporan aduan dari customer dan mitra akan muncul di sini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600">
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Reporter</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Dilaporkan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Jenis</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($reports as $report)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 whitespace-nowrap text-gray-600 text-xs">
                                        {{ $report->created_at->format('d M Y') }}
                                        <div class="text-gray-400">{{ $report->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        @if ($report->reporter)
                                            <a href="{{ route('admin.users.show', $report->reporter) }}"
                                                class="text-primary-600 hover:text-primary-700 hover:underline font-medium">
                                                {{ $report->reporter->name }}
                                            </a>
                                            <div class="text-xs text-gray-500">{{ $report->reporter->email }}</div>
                                        @elseif ($report->user)
                                            <a href="{{ route('admin.users.show', $report->user) }}"
                                                class="text-primary-600 hover:text-primary-700 hover:underline font-medium">
                                                {{ $report->user->name }}
                                            </a>
                                            <div class="text-xs text-gray-500">{{ $report->user->email }}</div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        @if ($report->reportedUser)
                                            <a href="{{ route('admin.users.show', $report->reportedUser) }}"
                                                class="text-primary-600 hover:text-primary-700 hover:underline font-medium">
                                                {{ $report->reportedUser->name }}
                                            </a>
                                            <div class="text-xs text-gray-500">{{ $report->reportedUser->email }}</div>
                                        @elseif ($report->reportedHelp)
                                            <a href="#" class="text-primary-600 hover:text-primary-700 hover:underline font-medium">
                                                Bantuan #{{ $report->reportedHelp->id }}
                                            </a>
                                            <div class="text-xs text-gray-500">{{ Str::limit($report->reportedHelp->title, 30) }}</div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                            {{ $report->report_type_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $report->isFromCustomer() ? 'bg-primary-100 text-primary-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $report->category_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap">
                                        @if ($report->status === 'pending')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                                Pending
                                            </span>
                                        @elseif ($report->status === 'in_progress')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                                In Progress
                                            </span>
                                        @elseif ($report->status === 'resolved')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                                Resolved
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                                Dismissed
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('admin.partners.reports.show', $report) }}"
                                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-full text-xs text-gray-700 hover:bg-gray-50">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($reports->hasPages())
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-center">
                        {{ $reports->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
