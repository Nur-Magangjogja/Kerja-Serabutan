@extends('layouts.mitra')

@section('content')

    <div class="min-h-screen bg-gray-50 p-4 pb-32">
        <div class="max-w-2xl mx-auto">
            <div class="mb-6">
                <a href="{{ route('mitra.reports.create') }}"
                    class="inline-flex items-center text-primary-600 hover:text-primary-700 mb-4">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Status Laporan</h1>
                <p class="text-sm text-gray-600 mt-1">Detail status laporan yang Anda kirim</p>
            </div>

            <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">{{ $report->title }}</h2>
                    <p class="text-sm text-gray-500 mt-1">Dikirim pada: {{ $report->created_at->format('d M Y H:i') }}</p>
                </div>

                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-700">Status saat ini:</p>
                    @php
                        $status = $report->status ?? 'pending';
                    @endphp
                    <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold ">
                        @if($status === 'pending')
                            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full">Menunggu Peninjauan</span>
                        @elseif($status === 'in_progress' || $status === 'processing')
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full">Sedang Ditangani</span>
                        @elseif($status === 'resolved' || $status === 'closed')
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full">Selesai</span>
                        @elseif($status === 'rejected')
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full">Ditolak</span>
                        @else
                            <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full">{{ ucfirst($status) }}</span>
                        @endif
                    </div>
                </div>

                <div class="mt-6">
                    <p class="text-sm font-medium text-gray-700">Jenis Laporan</p>
                    <p class="mt-1 text-gray-700 text-sm">{{ $report->report_type }}</p>
                </div>

                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-700">Jenis Bantuan (jika ada)</p>
                    <p class="mt-1 text-gray-700 text-sm">{{ $report->reported_help_text ?? '-' }}</p>
                </div>

                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-700">Customer yang Dilaporkan (jika ada)</p>
                    <p class="mt-1 text-gray-700 text-sm">
                        {{ $report->reported_user_text ?? ($report->reported_user_id ? $report->reported_user_id : '-') }}
                    </p>
                </div>

                <div class="mt-6">
                    <p class="text-sm font-medium text-gray-700">Detail Laporan</p>
                    <p class="mt-1 text-gray-700 text-sm whitespace-pre-line">{{ $report->message }}</p>
                </div>

                <div class="mt-6 flex justify-end">
                    <a href="{{ route('mitra.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl">Kembali
                        ke Dashboard</a>
                </div>
            </div>
        </div>
    </div>

@endsection