<?php
namespace App\Livewire\SuperAdmin\Transactions;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\BalanceTransaction;
use App\Models\AppSetting;
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

    public $search = '';
    public $type = 'all'; // types: all, topup, withdraw, other
    public $perPage = 15;
    public $from = null;
    public $to = null;

    protected $queryString = ['search' => ['except' => ''], 'type' => ['except' => 'all']];

    public function mount()
    {
        // Set default date range only on first load
        if (!request()->has('from') && !$this->from) {
            $this->from = now()->startOfMonth()->format('Y-m-d');
        }
        if (!request()->has('to') && !$this->to) {
            $this->to = now()->format('Y-m-d');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
    }

    private function getBaseQuery()
    {
        $query = BalanceTransaction::query();

        if ($this->type && $this->type !== 'all') {
            $query->where('type', $this->type);
        }

        if ($this->search) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%{$s}%")
                    ->orWhereHas('user', function ($qu) use ($s) {
                        $qu->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%");
                    });
            });
        }

        if ($this->from) {
            $query->whereDate('created_at', '>=', $this->from);
        }
        if ($this->to) {
            $query->whereDate('created_at', '<=', $this->to);
        }

        return $query;
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
                fputcsv($handle, [
                    $index + 1,
                    optional($t->created_at)->format('Y-m-d H:i:s'),
                    optional($t->user)->name ?? 'User Terhapus',
                    optional($t->user)->email ?? '-',
                    strtoupper($t->type),
                    $t->description ?? '-',
                    $t->amount,
                    $t->formatted_reference,
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
        $totalRevenue = $summaryQuery->clone()->where('type', 'topup')->sum('amount');
        $totalWithdraw = $summaryQuery->clone()->where('type', 'withdraw')->sum('amount');
        $totalTransactions = $summaryQuery->clone()->count();
        $avgTransaction = $totalTransactions > 0 ? $summaryQuery->clone()->avg('amount') : 0;

        $appName = AppSetting::get('app_name', config('app.name', 'SayaBantu'));

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Financial Report');

        // Document Title
        $sheet->setCellValue('A1', strtoupper($appName) . ' - FINANCIAL REPORT');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB('0F172A');

        // Subtitle / Generated Time
        $sheet->setCellValue('A2', 'Dicetak pada: ' . now()->format('d/m/Y H:i') . ' WIB | Periode: ' . ($this->from ? date('d/m/Y', strtotime($this->from)) : 'Semua') . ' s/d ' . ($this->to ? date('d/m/Y', strtotime($this->to)) : 'Semua'));
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true)->getColor()->setRGB('64748B');

        // KPI Summary Box
        $sheet->setCellValue('A4', 'Total Top Up:');
        $sheet->setCellValue('B4', $totalRevenue);
        $sheet->getStyle('B4')->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('A4:B4')->getFont()->setBold(true);

        $sheet->setCellValue('C4', 'Total Withdraw:');
        $sheet->setCellValue('D4', $totalWithdraw);
        $sheet->getStyle('D4')->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('C4:D4')->getFont()->setBold(true);

        $sheet->setCellValue('E4', 'Total Transaksi:');
        $sheet->setCellValue('F4', $totalTransactions);
        $sheet->getStyle('E4:F4')->getFont()->setBold(true);

        // Header Table
        $headerRow = 6;
        $headers = ['No', 'Waktu Transaksi', 'Nama Pengguna', 'Email', 'Tipe', 'Deskripsi', 'Nominal (Rp)', 'Referensi', 'Status'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];

        foreach ($headers as $i => $headerText) {
            $col = $cols[$i];
            $sheet->setCellValue($col . $headerRow, $headerText);
        }

        // Style Table Header
        $headerRange = 'A' . $headerRow . ':I' . $headerRow;
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0F172A');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Populate Data Rows
        $row = 7;
        foreach ($transactions as $index => $t) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, optional($t->created_at)->format('Y-m-d H:i:s'));
            $sheet->setCellValue('C' . $row, optional($t->user)->name ?? 'User Terhapus');
            $sheet->setCellValue('D' . $row, optional($t->user)->email ?? '-');
            $sheet->setCellValue('E' . $row, strtoupper($t->type));
            $sheet->setCellValue('F' . $row, $t->description ?? '-');
            $sheet->setCellValue('G' . $row, $t->amount);
            $sheet->setCellValue('H' . $row, $t->formatted_reference);
            $sheet->setCellValue('I' . $row, ucfirst($t->status ?? 'ok'));

            // Format numbers and alignments
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0');
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Alternate row background
            if ($index % 2 === 1) {
                $sheet->getStyle('A' . $row . ':I' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $row++;
        }

        // Border styling for whole table
        $lastRow = max(7, $row - 1);
        $tableRange = 'A6:I' . $lastRow;
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');

        // Auto-fit column widths
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $callback = function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        };

        return response()->streamDownload($callback, $filename, [
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
        $transactions = $this->getBaseQuery()->with('user')->orderBy('created_at', 'desc')->get();

        $summaryQuery = $this->getBaseQuery();
        $totalRevenue = $summaryQuery->clone()->where('type', 'topup')->sum('amount');
        $totalWithdraw = $summaryQuery->clone()->where('type', 'withdraw')->sum('amount');
        $totalTransactions = $summaryQuery->clone()->count();
        $avgTransaction = $totalTransactions > 0 ? $summaryQuery->clone()->avg('amount') : 0;

        $appName = AppSetting::get('app_name', config('app.name', 'SayaBantu'));
        $appTagline = AppSetting::get('app_tagline', 'Platform Layanan & Bantuan Serabutan');

        $pdf = Pdf::loadView('exports.financial-report-pdf', [
            'transactions' => $transactions,
            'totalRevenue' => $totalRevenue,
            'totalWithdraw' => $totalWithdraw,
            'totalTransactions' => $totalTransactions,
            'avgTransaction' => $avgTransaction,
            'from' => $this->from,
            'to' => $this->to,
            'type' => $this->type,
            'search' => $this->search,
            'appName' => $appName,
            'appTagline' => $appTagline,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function render()
    {
        $query = $this->getBaseQuery()->with('user');
        $transactions = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        // Calculate summary statistics (filtered)
        $summaryQuery = $this->getBaseQuery();
        
        $totalTopup       = (float) $summaryQuery->clone()->where('type', 'topup')->where('status', 'completed')->sum('amount');
        $totalWithdraw    = (float) $summaryQuery->clone()->where('type', 'withdraw')->where('status', 'completed')->sum('amount');
        $totalPlatformFee = (float) $summaryQuery->clone()->where('type', 'platform_fee')->where('status', 'completed')->sum('amount');
        $totalEarning     = (float) $summaryQuery->clone()->where('type', 'earning')->where('status', 'completed')->sum('amount');
        $totalPenalty     = (float) $summaryQuery->clone()->where('type', 'penalty')->where('status', 'completed')->sum('amount');
        $totalEscrow      = (float) $summaryQuery->clone()->where('type', 'escrow_lock')->where('status', 'completed')->sum('amount');
        $totalRefund      = (float) $summaryQuery->clone()->where('type', 'refund')->where('status', 'completed')->sum('amount');
        $totalTransactions = $summaryQuery->clone()->count();

        return view('superadmin.transactions-log', [
            'transactions'     => $transactions,
            'totalTopup'       => $totalTopup,
            'totalWithdraw'    => $totalWithdraw,
            'totalPlatformFee' => $totalPlatformFee,
            'totalEarning'     => $totalEarning,
            'totalPenalty'     => $totalPenalty,
            'totalEscrow'      => $totalEscrow,
            'totalRefund'      => $totalRefund,
            'totalTransactions'=> $totalTransactions,
        ]);
    }
}

