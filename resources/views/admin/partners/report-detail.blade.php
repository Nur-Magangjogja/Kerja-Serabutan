@extends('layouts.admin')

@section('content')
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Detail Laporan Aduan</h1>
                    <p class="text-sm text-gray-600 mt-1">Informasi lengkap dan kelola laporan aduan.</p>
                </div>
                <a href="{{ route('admin.partners.report') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="p-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informasi Utama -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Laporan</h2>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Judul</dt>
                            <dd class="text-sm text-gray-900 font-medium">{{ $report->title }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Pesan</dt>
                            <dd class="text-sm text-gray-700 whitespace-pre-line">{{ $report->message }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Jenis Laporan</dt>
                                <dd>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                        {{ $report->report_type_label }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Kategori</dt>
                                <dd>
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold {{ $report->isFromCustomer() ? 'bg-primary-100 text-primary-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $report->category_label }}
                                    </span>
                                </dd>
                            </div>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Status</dt>
                            <dd>
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
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Tanggal Dibuat</dt>
                            <dd class="text-sm text-gray-700">{{ $report->created_at->format('d F Y, H:i') }} WIB</dd>
                        </div>
                        @if ($report->resolved_at)
                            <div>
                                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Tanggal Diselesaikan</dt>
                                <dd class="text-sm text-gray-700">{{ $report->resolved_at->format('d F Y, H:i') }} WIB</dd>
                                @if ($report->resolvedBy)
                                    <dd class="text-xs text-gray-500 mt-1">Oleh: {{ $report->resolvedBy->name }}</dd>
                                @endif
                            </div>
                        @endif
                    </dl>
                </div>

                <!-- Informasi Reporter -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Reporter</h2>
                    @if ($report->reporter)
                        <div class="flex items-center justify-between">
                            <div>
                                <a href="{{ route('admin.users.show', $report->reporter) }}"
                                    class="text-primary-600 hover:text-primary-700 hover:underline font-medium">
                                    {{ $report->reporter->name }}
                                </a>
                                <div class="text-sm text-gray-500 mt-1">{{ $report->reporter->email }}</div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Role: <span class="font-semibold">{{ ucfirst($report->reporter->role) }}</span>
                                </div>
                            </div>
                            <a href="{{ route('admin.users.show', $report->reporter) }}"
                                class="px-3 py-1.5 border border-gray-300 rounded-full text-xs text-gray-700 hover:bg-gray-50">
                                Lihat Profil
                            </a>
                        </div>
                    @elseif ($report->user)
                        <div class="flex items-center justify-between">
                            <div>
                                <a href="{{ route('admin.users.show', $report->user) }}"
                                    class="text-primary-600 hover:text-primary-700 hover:underline font-medium">
                                    {{ $report->user->name }}
                                </a>
                                <div class="text-sm text-gray-500 mt-1">{{ $report->user->email }}</div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Role: <span class="font-semibold">{{ ucfirst($report->user->role) }}</span>
                                </div>
                            </div>
                            <a href="{{ route('admin.users.show', $report->user) }}"
                                class="px-3 py-1.5 border border-gray-300 rounded-full text-xs text-gray-700 hover:bg-gray-50">
                                Lihat Profil
                            </a>
                        </div>
                    @else
                        <p class="text-sm text-gray-400">Informasi reporter tidak tersedia.</p>
                    @endif
                </div>

                <!-- Informasi User/Help yang Dilaporkan -->
                @if ($report->reportedUser || $report->reportedHelp)
                    <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Yang Dilaporkan</h2>
                        @if ($report->reportedUser)
                            <div class="flex items-center justify-between">
                                <div>
                                    <a href="{{ route('admin.users.show', $report->reportedUser) }}"
                                        class="text-primary-600 hover:text-primary-700 hover:underline font-medium">
                                        {{ $report->reportedUser->name }}
                                    </a>
                                    <div class="text-sm text-gray-500 mt-1">{{ $report->reportedUser->email }}</div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        Role: <span class="font-semibold">{{ ucfirst($report->reportedUser->role) }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('admin.users.show', $report->reportedUser) }}"
                                    class="px-3 py-1.5 border border-gray-300 rounded-full text-xs text-gray-700 hover:bg-gray-50">
                                    Lihat Profil
                                </a>
                            </div>
                        @endif
                        @if ($report->reportedHelp)
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-gray-900">Bantuan #{{ $report->reportedHelp->id }}</div>
                                        <div class="text-sm text-gray-500 mt-1">{{ $report->reportedHelp->title }}</div>
                                        <div class="text-xs text-gray-400 mt-1">
                                            Status: <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $report->reportedHelp->status)) }}</span>
                                        </div>
                                    </div>
                                    <a href="#"
                                        class="px-3 py-1.5 border border-gray-300 rounded-full text-xs text-gray-700 hover:bg-gray-50">
                                        Lihat Detail
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Sidebar Actions -->
            <div class="space-y-6">
                <!-- Update Status -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Update Status</h2>
                    <form method="POST" action="{{ route('admin.partners.reports.update', $report) }}">
                        @csrf
                        <select name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 mb-3">
                            <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ $report->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                        </select>
                        <button type="submit"
                            class="w-full px-4 py-2 bg-primary-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-primary-700">
                            Update Status
                        </button>
                    </form>

                    @if (!$report->isResolved())
                        <form method="POST" action="{{ route('admin.partners.reports.resolve', $report) }}" class="mt-3">
                            @csrf
                            <button type="submit"
                                class="w-full px-4 py-2 bg-green-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-green-700">
                                Tandai Resolved
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.partners.reports.reopen', $report) }}" class="mt-3">
                            @csrf
                            <button type="submit"
                                class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-blue-700">
                                Buka Kembali
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Catatan Admin -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Catatan Admin</h2>
                    @if ($report->admin_notes)
                        <div class="mb-4 p-3 bg-gray-50 rounded-lg text-sm text-gray-700 whitespace-pre-line">
                            {{ $report->admin_notes }}
                        </div>
                    @else
                        <p class="text-sm text-gray-400 mb-4">Belum ada catatan admin.</p>
                    @endif
                    <form method="POST" action="{{ route('admin.partners.reports.add-note', $report) }}">
                        @csrf
                        <textarea name="admin_notes" rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 mb-3"
                            placeholder="Tambahkan catatan admin...">{{ $report->admin_notes }}</textarea>
                        <button type="submit"
                            class="w-full px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-xl shadow-sm hover:bg-gray-900">
                            {{ $report->admin_notes ? 'Update Catatan' : 'Tambah Catatan' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

