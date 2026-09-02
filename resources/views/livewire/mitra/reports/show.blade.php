@extends('layouts.mitra')

@section('content')

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-4 pb-32 transition-colors">
        <div class="max-w-2xl mx-auto">
            <div class="mb-6">
                <a href="{{ route('mitra.reports.create') }}"
                    class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-700 mb-4 text-sm font-semibold">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Status Laporan</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Detail status laporan yang Anda kirim</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-200 dark:border-gray-700 p-6 transition-colors">
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $report->title }}</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dikirim pada: {{ $report->created_at->format('d M Y H:i') }} WIB</p>
                </div>

                <div class="mt-4">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status saat ini:</p>
                    @php
                        $status = $report->status ?? 'pending';
                    @endphp
                    <div class="mt-2 inline-flex items-center text-xs font-bold">
                        @if($status === 'pending')
                            <span class="bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 px-3 py-1 rounded-full">Menunggu Peninjauan</span>
                        @elseif($status === 'in_progress' || $status === 'processing')
                            <span class="bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800 px-3 py-1 rounded-full">Sedang Ditangani</span>
                        @elseif($status === 'resolved' || $status === 'closed')
                            <span class="bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 px-3 py-1 rounded-full">Selesai</span>
                        @elseif($status === 'rejected')
                            <span class="bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800 px-3 py-1 rounded-full">Ditolak</span>
                        @else
                            <span class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1 rounded-full">{{ ucfirst($status) }}</span>
                        @endif
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis Laporan</p>
                    <p class="mt-1 text-gray-800 dark:text-gray-200 text-sm font-medium">{{ $report->report_type }}</p>
                </div>

                <div class="mt-4">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis Bantuan (jika ada)</p>
                    <p class="mt-1 text-gray-800 dark:text-gray-200 text-sm font-medium">{{ $report->reported_help_text ?? '-' }}</p>
                </div>

                <div class="mt-4">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer yang Dilaporkan (jika ada)</p>
                    <p class="mt-1 text-gray-800 dark:text-gray-200 text-sm font-medium">
                        {{ $report->reported_user_text ?? ($report->reported_user_id ? $report->reported_user_id : '-') }}
                    </p>
                </div>

                <div class="mt-6">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Detail Laporan</p>
                    <p class="mt-1 text-gray-800 dark:text-gray-200 text-sm whitespace-pre-line leading-relaxed">{{ $report->message }}</p>
                </div>

                <div class="mt-6 flex justify-end">
                    <a href="{{ route('mitra.dashboard') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-bold rounded-xl transition">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection