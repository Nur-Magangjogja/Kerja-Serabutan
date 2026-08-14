@php
    $title = 'Verifikasi KTP';
    $breadcrumb = 'Super Admin / Verifikasi KTP';
@endphp

<div class="min-h-screen flex items-center justify-center p-12">
    <div class="bg-white rounded-xl shadow-md p-10 text-center max-w-2xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Halaman Verifikasi KTP Dihapus</h1>
        <p class="text-sm text-gray-600 mb-6">Fitur verifikasi KTP untuk SuperAdmin telah dihapus dari panel. Jika Anda
            membutuhkan akses ke data verifikasi, gunakan halaman Manajemen User atau Admin area.</p>
        <a href="{{ route('superadmin.dashboard') }}"
            class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg">Kembali ke Dashboard</a>
    </div>
</div>