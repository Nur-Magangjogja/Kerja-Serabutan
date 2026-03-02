<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartnerReport;
use App\Models\User;
use Illuminate\Http\Request;

class PartnerReportController extends Controller
{
    public function index()
    {
        // Build base query for statistics
        $statsQuery = PartnerReport::query();
        
        // Filter by admin's city if user is admin
        if (auth()->user() && auth()->user()->role === 'admin' && auth()->user()->city_id) {
            $statsQuery->where(function ($q) {
                $q->whereHas('reporter', function ($sq) {
                    $sq->where('city_id', auth()->user()->city_id);
                })->orWhereHas('reportedUser', function ($sq) {
                    $sq->where('city_id', auth()->user()->city_id);
                });
            });
        }

        // Statistik ringkasan
        $totalPending = (clone $statsQuery)->pending()->count();
        $totalInProgress = (clone $statsQuery)->inProgress()->count();
        $totalResolved = (clone $statsQuery)->resolved()->count();
        $totalDismissed = (clone $statsQuery)->dismissed()->count();
        $totalFromCustomer = (clone $statsQuery)->fromCustomer()->count();
        $totalFromMitra = (clone $statsQuery)->fromMitra()->count();

        // Filter parameters
        $status = request('status', 'all');
        $category = request('category', 'all');
        $reportType = request('report_type', 'all');
        $search = request('search');
        $startDate = request('start_date');
        $endDate = request('end_date');

        // Build query
        $query = PartnerReport::with(['reporter', 'reportedUser', 'reportedHelp', 'resolvedBy']);

        // Filter by admin's city if user is admin
        if (auth()->user() && auth()->user()->role === 'admin' && auth()->user()->city_id) {
            $query->where(function ($q) {
                $q->whereHas('reporter', function ($sq) {
                    $sq->where('city_id', auth()->user()->city_id);
                })->orWhereHas('reportedUser', function ($sq) {
                    $sq->where('city_id', auth()->user()->city_id);
                });
            });
        }

        // Apply filters
        if ($status !== 'all') {
            $query->byStatus($status);
        }

        if ($category !== 'all') {
            if ($category === 'dari_customer') {
                $query->fromCustomer();
            } elseif ($category === 'dari_mitra') {
                $query->fromMitra();
            }
        }

        if ($reportType !== 'all') {
            $query->byReportType($reportType);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('reporter', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('reportedUser', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Get report types for filter dropdown
        $reportTypes = [
            'mitra_berperilaku_buruk' => 'Mitra Berperilaku Buruk',
            'bantuan_fiktif' => 'Bantuan Fiktif',
            'penipuan' => 'Penipuan',
            'pelanggaran_aturan' => 'Pelanggaran Aturan',
            'konten_tidak_pantas' => 'Konten Tidak Pantas',
            'pelayanan_tidak_sesuai' => 'Pelayanan Tidak Sesuai',
            'pengguna_spam' => 'Pengguna Spam',
            'pengguna_kasar' => 'Pengguna Kasar',
            'data_tidak_valid' => 'Data Tidak Valid',
        ];

        $reports = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('admin.partners.report', compact(
            'reports',
            'totalPending',
            'totalInProgress',
            'totalResolved',
            'totalDismissed',
            'totalFromCustomer',
            'totalFromMitra',
            'reportTypes',
            'status',
            'category',
            'reportType',
            'search',
            'startDate',
            'endDate'
        ));
    }

    public function show(PartnerReport $report)
    {
        $report->load(['reporter', 'reportedUser', 'reportedHelp', 'resolvedBy']);

        return view('admin.partners.report-detail', compact('report'));
    }

    public function reportsIndex()
    {
        // Alias untuk backward compatibility, redirect ke index
        return redirect()->route('admin.partners.report');
    }

    public function updateStatus(PartnerReport $report, Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,dismissed',
        ]);

        $data = ['status' => $request->status];

        // Jika status resolved, set resolved_by dan resolved_at
        if ($request->status === 'resolved') {
            $data['resolved_by'] = auth()->id();
            $data['resolved_at'] = now();
        } elseif ($report->status === 'resolved' && $request->status !== 'resolved') {
            // Jika mengubah dari resolved ke status lain, clear resolved info
            $data['resolved_by'] = null;
            $data['resolved_at'] = null;
        }

        $report->update($data);

        return back()->with('success', 'Status laporan berhasil diperbarui.');
    }

    public function addNote(PartnerReport $report, Request $request)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:5000',
        ]);

        $report->update([
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with('success', 'Catatan admin berhasil ditambahkan.');
    }

    public function resolve(PartnerReport $report)
    {
        $report->update([
            'status' => 'resolved',
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Laporan telah ditandai sebagai resolved.');
    }

    public function reopen(PartnerReport $report)
    {
        $report->update([
            'status' => 'in_progress',
            'resolved_by' => null,
            'resolved_at' => null,
        ]);

        return back()->with('success', 'Laporan telah dibuka kembali.');
    }
}
