@php
    $title = 'Moderasi Bantuan';
    $breadcrumb = 'Super Admin / Moderasi Bantuan';
@endphp

<div class="min-h-screen bg-gray-50 flex items-center justify-center p-8">
    <div class="bg-white rounded-xl shadow-lg p-12 text-center max-w-xl">
        <h1 class="text-2xl font-bold text-gray-900">Halaman Moderasi Bantuan Dihapus</h1>
        <p class="text-sm text-gray-600 mt-3">Fitur moderasi permintaan bantuan untuk SuperAdmin telah dihapus dari
            panel.
        </p>
        <div class="mt-6">
            <a href="{{ route('superadmin.dashboard') }}"
                class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg">Kembali ke Dashboard</a>
        </div>
    </div>
</div>