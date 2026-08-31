<?php

namespace App\Livewire\SuperAdmin\Transactions;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\BalanceTransaction;
use App\Models\User;
use App\Models\AppSetting;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

#[Layout('layouts.superadmin')]
class Log extends Component
{
    use WithPagination;

    // Status sukses yang sah untuk transaksi finansial
    public const SUCCESS_STATUSES = ['completed', 'success', 'approved', 'ok'];

    // Tab Navigation: 'overview' (Ringkasan & Grafik), 'users' (Direktori Pengguna), 'streams' (Log Aliran Transaksi)
    public $tab = 'overview';

    // Periode Filter: Bulan & Tahun (Livewire Reactive)
    public $selectedMonth = 'all'; // 'all', 1, 2, ..., 12
    public $selectedYear = '2026';  // 'all', 2026, 2025, 2024, etc.

    // Filters for Transaction Streams
    public $search = '';
    public $type = 'all'; // all, topup, platform_fee, earning, withdraw, escrow_lock, refund, cancellation, deduction
    public $status = 'all'; // all, completed, pending, rejected, failed
    public $perPage = 15;

    // User Filter & User Directory Tab
    public $selectedUserId = null;
    public $selectedUserName = null;
    public $userSearch = '';
    public $userRole = 'all'; // all, customer, mitra
    public $usersPerPage = 12;

    // Public chart data for Alpine.js $watch reactivity
    public $chartData = [
        'labels' => [],
        'topup' => [],
        'platform_fee' => [],
        'earning' => [],
        'withdraw' => [],
        'escrow_lock' => [],
        'refund' => [],
    ];

    protected $queryString = [
        'tab' => ['except' => 'overview'],
        'selectedMonth' => ['except' => 'all'],
        'selectedYear' => ['except' => 'all'],
        'search' => ['except' => ''],
        'type' => ['except' => 'all'],
        'status' => ['except' => 'all'],
        'selectedUserId' => ['except' => null],
    ];

    public function mount()
    {
        // Default to current month & current year on first visit
        if (!request()->has('selectedMonth') && $this->selectedMonth === 'all') {
            $this->selectedMonth = (string) now()->month;
        }
        if (!request()->has('selectedYear') && $this->selectedYear === '2026') {
            $this->selectedYear = (string) now()->year;
        }

        if ($this->selectedUserId) {
            $user = User::find($this->selectedUserId);
            $this->selectedUserName = $user ? $user->name : null;
        }
    }

    public function setTab(string $tabName)
    {
        $this->tab = $tabName;
        $this->resetPage();
        $this->resetPage('usersPage');
    }

    public function updatedSelectedMonth()
    {
        $this->resetPage();
    }

    public function updatedSelectedYear()
    {
        $this->resetPage();
    }

    public function filterByUser($userId, $userName)
    {
        $this->selectedUserId = $userId;
        $this->selectedUserName = $userName;
        $this->tab = 'streams';
        $this->resetPage();
    }

    public function clearUserFilter()
    {
        $this->selectedUserId = null;
        $this->selectedUserName = null;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingUserSearch()
    {
        $this->resetPage('usersPage');
    }

    public function updatingUserRole()
    {
        $this->resetPage('usersPage');
    }

    /**
     * Dapatkan query transaksi dasar dengan filter periode bulan & tahun, user, tipe, status, search.
     */
    private function getBaseQuery()
    {
        $query = BalanceTransaction::query();

        if ($this->selectedUserId) {
            $query->where('user_id', $this->selectedUserId);
        }

        if ($this->type && $this->type !== 'all') {
            $query->where('type', $this->type);
        }

        if ($this->status && $this->status !== 'all') {
            if ($this->status === 'completed') {
                $query->whereIn('status', self::SUCCESS_STATUSES);
            } else {
                $query->where('status', $this->status);
            }
        }

        // Filter Tahun & Bulan
        if ($this->selectedYear && $this->selectedYear !== 'all') {
            $query->whereYear('created_at', (int) $this->selectedYear);

            if ($this->selectedMonth && $this->selectedMonth !== 'all') {
                $query->whereMonth('created_at', (int) $this->selectedMonth);
            }
        } elseif ($this->selectedMonth && $this->selectedMonth !== 'all') {
            // Jika tahun 'all' tapi bulan dipilih
            $query->whereMonth('created_at', (int) $this->selectedMonth);
        }

        if ($this->search) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('reference_id', 'like', "%{$s}%")
                    ->orWhere('request_code', 'like', "%{$s}%")
                    ->orWhere('order_id', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")
                    ->orWhereHas('user', function ($qu) use ($s) {
                        $qu->where('name', 'like', "%{$s}%")
                            ->orWhere('email', 'like', "%{$s}%");
                    });
            });
        }

        return $query;
    }

    private function getUserQuery()
    {
        $query = User::whereIn('role', ['customer', 'mitra'])
            ->with('balance');

        if ($this->userRole && $this->userRole !== 'all') {
            $query->where('role', $this->userRole);
        }

        if ($this->userSearch) {
            $s = trim($this->userSearch);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone_number', 'like', "%{$s}%");
            });
        }

        return $query->withSum(['transactions as total_topup' => function ($q) {
            $q->where('type', 'topup')->whereIn('status', self::SUCCESS_STATUSES);
        }], 'amount')
        ->withSum(['transactions as total_earning' => function ($q) {
            $q->where('type', 'earning')->whereIn('status', self::SUCCESS_STATUSES);
        }], 'amount')
        ->withSum(['transactions as total_withdraw' => function ($q) {
            $q->where('type', 'withdraw')->whereIn('status', self::SUCCESS_STATUSES);
        }], 'amount')
        ->withCount('transactions')
        ->orderBy('name', 'asc');
    }

    /**
     * Hitung dataset grafik berdasarkan pilihan bulan & tahun secara reaktif.
     */
    private function calculateChartData(): array
    {
        $labels = [];
        $topup = [];
        $platformFee = [];
        $earning = [];
        $withdraw = [];
        $escrowLock = [];
        $refund = [];

        $isSpecificMonth = ($this->selectedYear !== 'all' && $this->selectedMonth !== 'all');
        $isSpecificYearOnly = ($this->selectedYear !== 'all' && $this->selectedMonth === 'all');

        $statusList = "'" . implode("','", self::SUCCESS_STATUSES) . "'";

        if ($isSpecificMonth) {
            // Mode 1: Harian dalam Bulan Terpilih (1 s/d hari terakhir bulan)
            $year = (int) $this->selectedYear;
            $month = (int) $this->selectedMonth;
            $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, $month, $daysInMonth)->endOfDay();

            $stats = BalanceTransaction::selectRaw("
                DAY(created_at) as day_num,
                SUM(CASE WHEN type = 'topup' AND status IN ({$statusList}) THEN amount ELSE 0 END) as topup,
                SUM(CASE WHEN type = 'platform_fee' AND status IN ({$statusList}) THEN amount ELSE 0 END) as platform_fee,
                SUM(CASE WHEN type = 'earning' AND status IN ({$statusList}) THEN amount ELSE 0 END) as earning,
                SUM(CASE WHEN type = 'withdraw' AND status IN ({$statusList}) THEN amount ELSE 0 END) as withdraw,
                SUM(CASE WHEN type = 'escrow_lock' AND status IN ({$statusList}) THEN amount ELSE 0 END) as escrow_lock,
                SUM(CASE WHEN type = 'refund' AND status IN ({$statusList}) THEN amount ELSE 0 END) as refund
            ")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupByRaw("DAY(created_at)")
            ->get()
            ->keyBy('day_num');

            $monthShortName = Carbon::createFromDate($year, $month, 1)->translatedFormat('M');

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $stat = $stats->get($d);
                $labels[] = "{$d} {$monthShortName}";
                $topup[] = (float) ($stat->topup ?? 0);
                $platformFee[] = (float) ($stat->platform_fee ?? 0);
                $earning[] = (float) ($stat->earning ?? 0);
                $withdraw[] = (float) ($stat->withdraw ?? 0);
                $escrowLock[] = (float) ($stat->escrow_lock ?? 0);
                $refund[] = (float) ($stat->refund ?? 0);
            }
        } elseif ($isSpecificYearOnly) {
            // Mode 2: 12 Bulan dalam Tahun Terpilih
            $year = (int) $this->selectedYear;

            $stats = BalanceTransaction::selectRaw("
                MONTH(created_at) as month_num,
                SUM(CASE WHEN type = 'topup' AND status IN ({$statusList}) THEN amount ELSE 0 END) as topup,
                SUM(CASE WHEN type = 'platform_fee' AND status IN ({$statusList}) THEN amount ELSE 0 END) as platform_fee,
                SUM(CASE WHEN type = 'earning' AND status IN ({$statusList}) THEN amount ELSE 0 END) as earning,
                SUM(CASE WHEN type = 'withdraw' AND status IN ({$statusList}) THEN amount ELSE 0 END) as withdraw,
                SUM(CASE WHEN type = 'escrow_lock' AND status IN ({$statusList}) THEN amount ELSE 0 END) as escrow_lock,
                SUM(CASE WHEN type = 'refund' AND status IN ({$statusList}) THEN amount ELSE 0 END) as refund
            ")
            ->whereYear('created_at', $year)
            ->groupByRaw("MONTH(created_at)")
            ->get()
            ->keyBy('month_num');

            for ($m = 1; $m <= 12; $m++) {
                $stat = $stats->get($m);
                $monthName = Carbon::createFromDate($year, $m, 1)->translatedFormat('M');
                $labels[] = $monthName;
                $topup[] = (float) ($stat->topup ?? 0);
                $platformFee[] = (float) ($stat->platform_fee ?? 0);
                $earning[] = (float) ($stat->earning ?? 0);
                $withdraw[] = (float) ($stat->withdraw ?? 0);
                $escrowLock[] = (float) ($stat->escrow_lock ?? 0);
                $refund[] = (float) ($stat->refund ?? 0);
            }
        } else {
            // Mode 3: 5 Tahun Terakhir (Semua Tahun)
            $currentYear = (int) now()->year;
            $startYear = $currentYear - 4;

            $stats = BalanceTransaction::selectRaw("
                YEAR(created_at) as year_num,
                SUM(CASE WHEN type = 'topup' AND status IN ({$statusList}) THEN amount ELSE 0 END) as topup,
                SUM(CASE WHEN type = 'platform_fee' AND status IN ({$statusList}) THEN amount ELSE 0 END) as platform_fee,
                SUM(CASE WHEN type = 'earning' AND status IN ({$statusList}) THEN amount ELSE 0 END) as earning,
                SUM(CASE WHEN type = 'withdraw' AND status IN ({$statusList}) THEN amount ELSE 0 END) as withdraw,
                SUM(CASE WHEN type = 'escrow_lock' AND status IN ({$statusList}) THEN amount ELSE 0 END) as escrow_lock,
                SUM(CASE WHEN type = 'refund' AND status IN ({$statusList}) THEN amount ELSE 0 END) as refund
            ")
            ->whereYear('created_at', '>=', $startYear)
            ->groupByRaw("YEAR(created_at)")
            ->get()
            ->keyBy('year_num');

            for ($y = $startYear; $y <= $currentYear; $y++) {
                $stat = $stats->get($y);
                $labels[] = (string) $y;
                $topup[] = (float) ($stat->topup ?? 0);
                $platformFee[] = (float) ($stat->platform_fee ?? 0);
                $earning[] = (float) ($stat->earning ?? 0);
                $withdraw[] = (float) ($stat->withdraw ?? 0);
                $escrowLock[] = (float) ($stat->escrow_lock ?? 0);
                $refund[] = (float) ($stat->refund ?? 0);
            }
        }

        return [
            'labels'       => $labels,
            'topup'        => $topup,
            'platform_fee' => $platformFee,
            'earning'      => $earning,
            'withdraw'     => $withdraw,
            'escrow_lock'  => $escrowLock,
            'refund'       => $refund,
        ];
    }

    /**
     * Dapatkan teks deskriptif periode aktif
     */
    public function getPeriodLabel(): string
    {
        if ($this->selectedYear === 'all') {
            return 'Semua Tahun (All-Time)';
        }

        if ($this->selectedMonth === 'all') {
            return "Tahun {$this->selectedYear} (Semua Bulan)";
        }

        $monthName = Carbon::createFromDate((int)$this->selectedYear, (int)$this->selectedMonth, 1)->translatedFormat('F');
        return "{$monthName} {$this->selectedYear}";
    }

    /**
     * Export Transactions to CSV format
     */
    public function exportCsv()
    {
        $filename = 'financial_report_' . now()->format('Ymd_His') . '.csv';
        $transactions = $this->getBaseQuery()->with('user')->orderBy('created_at', 'desc')->get();

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV Header Row
            fputcsv($handle, [
                'No',
                'Waktu Transaksi',
                'Nama Pengguna',
                'Email',
                'Tipe Transaksi',
                'Deskripsi',
                'Nominal (Rp)',
                'Referensi',
                'Status Transaksi',
            ]);

            foreach ($transactions as $index => $t) {
                $userName = $t->user ? $t->user->name : ($t->type === 'platform_fee' ? 'Kas Platform' : 'Sistem');
                $userEmail = $t->user ? $t->user->email : '-';
                $ref = $t->reference_id ? "Bantuan #{$t->reference_id}" : ($t->request_code ? "Kode: {$t->request_code}" : '-');

                fputcsv($handle, [
                    $index + 1,
                    optional($t->created_at)->format('Y-m-d H:i:s'),
                    $userName,
                    $userEmail,
                    strtoupper($t->type),
                    $t->description ?? '-',
                    $t->amount,
                    $ref,
                    ucfirst($t->status ?? 'ok'),
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export Transactions to Excel (.xlsx) format
     */
    public function exportXlsx()
    {
        return $this->exportExcel();
    }

    public function exportExcel()
    {
        $filename = 'financial_report_' . now()->format('Ymd_His') . '.xlsx';
        $transactions = $this->getBaseQuery()->with('user')->orderBy('created_at', 'desc')->get();

        $summaryQuery = $this->getBaseQuery();
        $totalRevenue = $summaryQuery->clone()->where('type', 'topup')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $totalPlatformFee = $summaryQuery->clone()->where('type', 'platform_fee')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $totalWithdraw = $summaryQuery->clone()->where('type', 'withdraw')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $totalTransactions = $summaryQuery->clone()->count();

        $appName = AppSetting::get('app_name', config('app.name', 'SayaBantu'));

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Financial Report');

        // Document Title
        $sheet->setCellValue('A1', strtoupper($appName) . ' - FINANCIAL REPORT');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB('0F172A');

        // Subtitle / Generated Time
        $sheet->setCellValue('A2', 'Dicetak pada: ' . now()->format('d/m/Y H:i') . ' WIB | Periode: ' . $this->getPeriodLabel());
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true)->getColor()->setRGB('64748B');

        // KPI Summary Box
        $sheet->setCellValue('A4', 'Total Top Up:');
        $sheet->setCellValue('B4', $totalRevenue);
        $sheet->getStyle('B4')->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('A4:B4')->getFont()->setBold(true);

        $sheet->setCellValue('C4', 'Komisi Platform:');
        $sheet->setCellValue('D4', $totalPlatformFee);
        $sheet->getStyle('D4')->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('C4:D4')->getFont()->setBold(true);

        $sheet->setCellValue('E4', 'Total Withdraw:');
        $sheet->setCellValue('F4', $totalWithdraw);
        $sheet->getStyle('F4')->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('E4:F4')->getFont()->setBold(true);

        $sheet->setCellValue('G4', 'Total Transaksi:');
        $sheet->setCellValue('H4', number_format($totalTransactions) . ' baris');
        $sheet->getStyle('G4:H4')->getFont()->setBold(true);

        // Header Row
        $headers = [
            'A6' => 'No',
            'B6' => 'Waktu Transaksi',
            'C6' => 'Nama Pengguna',
            'D6' => 'Role',
            'E6' => 'Tipe Transaksi',
            'F6' => 'Deskripsi',
            'G6' => 'Nominal (Rp)',
            'H6' => 'Status',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerRange = 'A6:H6';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(6)->setRowHeight(26);

        // Data Rows
        $row = 7;
        foreach ($transactions as $index => $t) {
            $userName = $t->user ? $t->user->name : ($t->type === 'platform_fee' ? 'Kas Platform' : 'Sistem');
            $userRole = $t->user ? ucfirst($t->user->role) : 'Platform';

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, optional($t->created_at)->format('d/m/Y H:i'));
            $sheet->setCellValue('C' . $row, $userName);
            $sheet->setCellValue('D' . $row, $userRole);
            $sheet->setCellValue('E' . $row, strtoupper($t->type));
            $sheet->setCellValue('F' . $row, $t->description ?? '-');
            $sheet->setCellValue('G' . $row, $t->amount);
            $sheet->setCellValue('H' . $row, ucfirst($t->status ?? 'ok'));

            // Format Currency
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0');

            // Alignment
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Zebra striping
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:H{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');
            }

            $row++;
        }

        // Borders
        $lastDataRow = $row - 1;
        if ($lastDataRow >= 6) {
            $sheet->getStyle("A6:H{$lastDataRow}")->getBorders()->getAllBorders()->applyFromArray([
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'E2E8F0'],
            ]);
        }

        // Auto-fit columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export Transactions to PDF format
     */
    public function exportPdf()
    {
        $filename = 'financial_report_' . now()->format('Ymd_His') . '.pdf';
        $transactions = $this->getBaseQuery()->with('user')->orderBy('created_at', 'desc')->limit(500)->get();

        $summaryQuery = $this->getBaseQuery();
        $totalRevenue = $summaryQuery->clone()->where('type', 'topup')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $totalPlatformFee = $summaryQuery->clone()->where('type', 'platform_fee')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $totalWithdraw = $summaryQuery->clone()->where('type', 'withdraw')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $totalTransactions = $summaryQuery->clone()->count();

        $appName = AppSetting::get('app_name', config('app.name', 'SayaBantu'));
        $appLogo = AppSetting::get('app_logo', null);

        $data = [
            'appName'           => $appName,
            'appLogo'           => $appLogo,
            'generatedAt'       => now()->format('d F Y, H:i') . ' WIB',
            'periodLabel'       => $this->getPeriodLabel(),
            'totalRevenue'      => $totalRevenue,
            'totalPlatformFee'  => $totalPlatformFee,
            'totalWithdraw'     => $totalWithdraw,
            'totalTransactions' => $totalTransactions,
            'transactions'      => $transactions,
        ];

        $pdf = Pdf::loadView('exports.transactions-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function render()
    {
        // ─────────────────────────────────────────────────────────────────────
        // 1. STATISTIK RINGKASAN SESUAI PERIODE BULAN & TAHUN TERPILIH
        // ─────────────────────────────────────────────────────────────────────
        $summaryQuery = $this->getBaseQuery();

        $periodTopup            = (float) $summaryQuery->clone()->where('type', 'topup')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $periodPlatformFee      = (float) $summaryQuery->clone()->where('type', 'platform_fee')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $periodEarning          = (float) $summaryQuery->clone()->where('type', 'earning')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $periodWithdraw         = (float) $summaryQuery->clone()->where('type', 'withdraw')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $periodWithdrawCustomer = (float) $summaryQuery->clone()->where('type', 'withdraw')->whereIn('status', self::SUCCESS_STATUSES)->whereHas('user', fn($q) => $q->where('role', 'customer'))->sum('amount');
        $periodWithdrawMitra    = (float) $summaryQuery->clone()->where('type', 'withdraw')->whereIn('status', self::SUCCESS_STATUSES)->whereHas('user', fn($q) => $q->where('role', 'mitra'))->sum('amount');
        $periodEscrow           = (float) $summaryQuery->clone()->where('type', 'escrow_lock')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $periodRefund           = (float) $summaryQuery->clone()->where('type', 'refund')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $periodCancellation     = (float) $summaryQuery->clone()->whereIn('type', ['cancellation', 'penalty'])->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $periodTransactions     = $summaryQuery->clone()->count();

        // ─────────────────────────────────────────────────────────────────────
        // 2. TOTAL ALL-TIME (KESELURUHAN SEPANJANG MASA)
        // ─────────────────────────────────────────────────────────────────────
        $allTimeQuery = BalanceTransaction::query();
        if ($this->selectedUserId) {
            $allTimeQuery->where('user_id', $this->selectedUserId);
        }
        $allTimeTopup       = (float) $allTimeQuery->clone()->where('type', 'topup')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $allTimePlatformFee = (float) $allTimeQuery->clone()->where('type', 'platform_fee')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $allTimeEarning     = (float) $allTimeQuery->clone()->where('type', 'earning')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $allTimeWithdraw    = (float) $allTimeQuery->clone()->where('type', 'withdraw')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $allTimeEscrow      = (float) $allTimeQuery->clone()->where('type', 'escrow_lock')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $allTimeRefund      = (float) $allTimeQuery->clone()->where('type', 'refund')->whereIn('status', self::SUCCESS_STATUSES)->sum('amount');
        $allTimeCount       = $allTimeQuery->clone()->count();

        // ─────────────────────────────────────────────────────────────────────
        // 3. TABEL REKAPITULASI ARUS KAS BULANAN (SESUAI TAHUN TERPILIH / 12 BULAN)
        // ─────────────────────────────────────────────────────────────────────
        $statusList = "'" . implode("','", self::SUCCESS_STATUSES) . "'";
        $monthlyBreakdownQuery = BalanceTransaction::selectRaw("
            YEAR(created_at) as year,
            MONTH(created_at) as month,
            SUM(CASE WHEN type = 'topup' AND status IN ({$statusList}) THEN amount ELSE 0 END) as topup,
            SUM(CASE WHEN type = 'platform_fee' AND status IN ({$statusList}) THEN amount ELSE 0 END) as platform_fee,
            SUM(CASE WHEN type = 'earning' AND status IN ({$statusList}) THEN amount ELSE 0 END) as earning,
            SUM(CASE WHEN type = 'withdraw' AND status IN ({$statusList}) THEN amount ELSE 0 END) as withdraw,
            SUM(CASE WHEN type = 'escrow_lock' AND status IN ({$statusList}) THEN amount ELSE 0 END) as escrow_lock,
            SUM(CASE WHEN type = 'refund' AND status IN ({$statusList}) THEN amount ELSE 0 END) as refund,
            COUNT(*) as total_count
        ");

        if ($this->selectedYear !== 'all') {
            $monthlyBreakdownQuery->whereYear('created_at', (int) $this->selectedYear);
        }

        $monthlyBreakdownRows = $monthlyBreakdownQuery
            ->groupByRaw("YEAR(created_at), MONTH(created_at)")
            ->orderByRaw("YEAR(created_at) DESC, MONTH(created_at) DESC")
            ->limit(12)
            ->get();

        // ─────────────────────────────────────────────────────────────────────
        // 4. HITUNG ULANG DATA GRAFIK REAKTIF LIVEWIRE
        // ─────────────────────────────────────────────────────────────────────
        $this->chartData = $this->calculateChartData();

        // ─────────────────────────────────────────────────────────────────────
        // 5. DATA UNTUK TAB STREAMS & TAB USERS
        // ─────────────────────────────────────────────────────────────────────
        $transactions = $this->getBaseQuery()->with('user')->orderBy('created_at', 'desc')->paginate($this->perPage);
        $users = $this->getUserQuery()->paginate($this->usersPerPage, ['*'], 'usersPage');

        // Available years for dropdown
        $availableYears = BalanceTransaction::selectRaw("DISTINCT YEAR(created_at) as yr")
            ->orderByDesc('yr')
            ->pluck('yr')
            ->toArray();
        if (empty($availableYears)) {
            $availableYears = [(int) date('Y')];
        }
        if (!in_array((int) date('Y'), $availableYears, true)) {
            array_unshift($availableYears, (int) date('Y'));
        }

        return view('livewire.superadmin.transactions.log', [
            'transactions'           => $transactions,
            'users'                  => $users,
            'periodTopup'            => $periodTopup,
            'periodPlatformFee'      => $periodPlatformFee,
            'periodEarning'          => $periodEarning,
            'periodWithdraw'         => $periodWithdraw,
            'periodWithdrawCustomer' => $periodWithdrawCustomer,
            'periodWithdrawMitra'    => $periodWithdrawMitra,
            'periodEscrow'           => $periodEscrow,
            'periodRefund'           => $periodRefund,
            'periodCancellation'     => $periodCancellation,
            'periodTransactions'     => $periodTransactions,
            'allTimeTopup'           => $allTimeTopup,
            'allTimePlatformFee'     => $allTimePlatformFee,
            'allTimeEarning'         => $allTimeEarning,
            'allTimeWithdraw'        => $allTimeWithdraw,
            'allTimeEscrow'          => $allTimeEscrow,
            'allTimeRefund'          => $allTimeRefund,
            'allTimeCount'           => $allTimeCount,
            'monthlyBreakdownRows'   => $monthlyBreakdownRows,
            'availableYears'         => $availableYears,
            'periodLabel'            => $this->getPeriodLabel(),
            'chartData'              => $this->chartData,
            'chartDataJson'          => json_encode($this->chartData),
        ]);
    }
}
