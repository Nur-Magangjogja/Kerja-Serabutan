<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - {{ config('app.name', 'SayaBantu') }}</title>
    <style>
        @page {
            margin: 28px 32px 36px 32px;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .brand-title {
            font-size: 20px;
            font-weight: bold;
            color: #0369a1;
            margin: 0;
        }
        .brand-sub {
            font-size: 10px;
            color: #6b7280;
            margin: 2px 0 0 0;
        }
        .report-title {
            text-align: right;
        }
        .report-title h2 {
            font-size: 16px;
            margin: 0;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-title p {
            font-size: 9.5px;
            color: #6b7280;
            margin: 3px 0 0 0;
        }
        
        /* Summary Boxes */
        .summary-table {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: separate;
            border-spacing: 6px 0;
        }
        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
            text-align: left;
        }
        .summary-label {
            font-size: 9px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .summary-val {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }
        .text-emerald { color: #059669; }
        .text-rose { color: #e11d48; }
        .text-blue { color: #0284c7; }
        .text-violet { color: #7c3aed; }

        /* Filter info */
        .filter-info {
            background: #f1f5f9;
            border-left: 3px solid #0284c7;
            padding: 6px 10px;
            font-size: 9.5px;
            color: #475569;
            margin-bottom: 14px;
        }

        /* Transactions Data Table */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        table.data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 7px 6px;
            border: 1px solid #0f172a;
            text-align: left;
        }
        table.data-table td {
            padding: 6px 6px;
            font-size: 9px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-topup { background: #dcfce7; color: #166534; }
        .badge-withdraw { background: #ffe4e6; color: #9f1239; }
        .badge-other { background: #e0f2fe; color: #075985; }
        
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-failed { background: #fee2e2; color: #991b1b; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 8.5px;
            color: #94a3b8;
            padding-top: 4px;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <h1 class="brand-title">{{ $appName ?? config('app.name', 'SayaBantu') }}</h1>
                <p class="brand-sub">{{ $appTagline ?? 'Platform Layanan & Bantuan Terpercaya' }}</p>
            </td>
            <td class="report-title" style="vertical-align: middle;">
                <h2>Financial Report</h2>
                <p>Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
            </td>
        </tr>
    </table>

    <!-- Filter Meta Info -->
    <div class="filter-info">
        <strong>Filter Laporan:</strong> 
        Periode: <strong>{{ $from ? \Carbon\Carbon::parse($from)->format('d/m/Y') : 'Awal' }}</strong> s/d <strong>{{ $to ? \Carbon\Carbon::parse($to)->format('d/m/Y') : 'Sekarang' }}</strong>
        &nbsp;|&nbsp; Tipe: <strong>{{ strtoupper($type === 'all' ? 'Semua Tipe' : $type) }}</strong>
        @if($search) &nbsp;|&nbsp; Pencarian: <strong>"{{ $search }}"</strong> @endif
    </div>

    <!-- Summary Cards -->
    <table class="summary-table">
        <tr>
            <td width="25%">
                <div class="summary-card">
                    <div class="summary-label">Total Top Up</div>
                    <div class="summary-val text-emerald">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="25%">
                <div class="summary-card">
                    <div class="summary-label">Total Withdraw</div>
                    <div class="summary-val text-rose">Rp {{ number_format($totalWithdraw, 0, ',', '.') }}</div>
                </div>
            </td>
            <td width="25%">
                <div class="summary-card">
                    <div class="summary-label">Total Transaksi</div>
                    <div class="summary-val text-blue">{{ number_format($totalTransactions) }} Trx</div>
                </div>
            </td>
            <td width="25%">
                <div class="summary-card">
                    <div class="summary-label">Rata-rata Nominal</div>
                    <div class="summary-val text-violet">Rp {{ number_format($avgTransaction, 0, ',', '.') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Transactions Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="4%" class="text-center">No</th>
                <th width="15%">Waktu Transaksi</th>
                <th width="24%">Pengguna</th>
                <th width="12%">Tipe</th>
                <th width="16%" class="text-right">Nominal</th>
                <th width="16%">Referensi</th>
                <th width="13%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $t)
            @php
                $badgeType = match($t->type) {
                    'topup' => 'badge-topup',
                    'withdraw' => 'badge-withdraw',
                    default => 'badge-other',
                };
                $badgeStatus = match(strtolower($t->status ?? 'ok')) {
                    'approved', 'success', 'ok' => 'badge-success',
                    'pending' => 'badge-pending',
                    'rejected', 'failed' => 'badge-failed',
                    default => 'badge-other',
                };
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ optional($t->created_at)->format('d/m/Y H:i') }}</td>
                <td>
                    <strong>{{ $t->user_display_name }}</strong><br>
                    <span style="color: #64748b; font-size: 8px;">{{ optional($t->user)->email ?? ($t->isPlatformTransaction() ? 'sistem@internal' : '-') }}</span>
                </td>
                <td>
                    <span class="badge {{ $badgeType }}">{{ ucfirst($t->type) }}</span>
                </td>
                <td class="text-right" style="font-weight: bold; {{ $t->type === 'topup' ? 'color: #059669;' : '' }}">
                    Rp {{ number_format($t->amount, 0, ',', '.') }}
                </td>
                <td style="font-size: 8.5px; color: #334155;">
                    {{ $t->formatted_reference }}
                </td>
                <td class="text-center">
                    <span class="badge {{ $badgeStatus }}">{{ ucfirst($t->status ?? 'Success') }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 24px; color: #94a3b8;">
                    Tidak ada data transaksi yang sesuai dengan filter.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dokumen resmi digenerate otomatis oleh Sistem {{ config('app.name', 'SayaBantu') }} &bull; Halaman dicetak pada {{ now()->format('d/m/Y H:i:s') }} WIB
    </div>

</body>
</html>
