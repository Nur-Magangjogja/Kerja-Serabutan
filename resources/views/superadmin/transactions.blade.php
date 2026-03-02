@php
    $title = 'Log Transaksi';
    $breadcrumb = 'Super Admin / Log Transaksi';
@endphp

<div>
    <div class="bg-white shadow-sm border-b border-gray-200 mb-6">
        <div class="px-8 py-4">
            <p class="text-sm text-gray-600">Riwayat topup, withdraw, dan mutasi saldo pengguna.</p>
        </div>
    </div>

    <div class="px-6 py-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl shadow-md p-4 border border-gray-200 w-full">
                <livewire:super-admin.transactions-log />
            </div>
        </div>
    </div>
</div>