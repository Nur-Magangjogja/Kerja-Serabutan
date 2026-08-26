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
        if (auth()->user() && auth()->user()->role === 'admin') {
            $admin = auth()->user();
            $cityIds = collect([$admin->city_id])
                ->merge($admin->managedCities?->pluck('id') ?? [])
                ->merge(\App\Models\City::where('admin_id', $admin->id)->pluck('id'))
                ->filter()
                ->unique();

            if ($cityIds->isNotEmpty()) {
                $statsQuery->where(function ($q) use ($cityIds) {
                    $q->whereHas('reporter', function ($sq) use ($cityIds) {
                        $sq->whereIn('city_id', $cityIds);
                    })->orWhereHas('reportedUser', function ($sq) use ($cityIds) {
                        $sq->whereIn('city_id', $cityIds);
                    });
                });
            }
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
        $query = PartnerReport::with(['reporter', 'reportedUser', 'reportedHelp', 'resolvedBy'])->withCount('messages');

        // Filter by admin's city if user is admin
        if (auth()->user() && auth()->user()->role === 'admin') {
            $admin = auth()->user();
            $cityIds = collect([$admin->city_id])
                ->merge($admin->managedCities?->pluck('id') ?? [])
                ->merge(\App\Models\City::where('admin_id', $admin->id)->pluck('id'))
                ->filter()
                ->unique();

            if ($cityIds->isNotEmpty()) {
                $query->where(function ($q) use ($cityIds) {
                    $q->whereHas('reporter', function ($sq) use ($cityIds) {
                        $sq->whereIn('city_id', $cityIds);
                    })->orWhereHas('reportedUser', function ($sq) use ($cityIds) {
                        $sq->whereIn('city_id', $cityIds);
                    });
                });
            }
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

        return view('livewire.admin.partners.report', compact(
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
        $report->load([
            'messages.sender',
            'reporter.balance',
            'reportedUser.balance',
            'reportedHelp.mitra',
            'reportedHelp.user',
            'resolvedBy',
            'refundProcessedBy'
        ]);

        $help = $report->reportedHelp;
        $transactions = collect();
        $isWithin24H = false;

        if ($help) {
            $transactions = \App\Models\BalanceTransaction::where('reference_id', $help->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $isWithin24H = $help->completed_at && $help->completed_at->addHours(24)->isFuture();
        }

        return view('livewire.admin.partners.report-detail', compact('report', 'help', 'transactions', 'isWithin24H'));
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

    /**
     * Setujui klaim refund customer:
     * 1. Tarik saldo mitra yang bersangkutan (penalty/clawback).
     * 2. Kembalikan 100% dana ke saldo customer (refund).
     * 3. Catat di BalanceTransaction & perbarui status laporan.
     */
    public function processRefund(PartnerReport $report, Request $request)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($report->refund_status === 'approved') {
            return back()->with('error', 'Klaim pengembalian dana untuk laporan ini sudah disetujui sebelumnya.');
        }

        $help = $report->reportedHelp;
        if (!$help) {
            return back()->with('error', 'Data bantuan terkait tidak ditemukan untuk memproses refund.');
        }

        $customer = $report->reporter ?? $help->user;
        if (!$customer) {
            return back()->with('error', 'Data customer pelapor tidak ditemukan.');
        }

        $refundAmount = (float) ($report->refund_amount ?: ($help->total_amount > 0 ? $help->total_amount : $help->amount));

        \Illuminate\Support\Facades\DB::transaction(function () use ($report, $help, $customer, $refundAmount, $request) {
            // 1. Tarik dana dari Mitra (Clawback / Penalti)
            $mitra = $report->reportedUser ?? $help->mitra;
            if ($mitra) {
                $mitraBalance = \App\Models\UserBalance::firstOrCreate(['user_id' => $mitra->id], ['balance' => 0]);
                $mitraEarning = (float) ($help->mitra_earning > 0 ? $help->mitra_earning : $help->amount);

                $mitraBalance->decrement('balance', $mitraEarning);

                \App\Models\BalanceTransaction::create([
                    'user_id'      => $mitra->id,
                    'amount'       => $mitraEarning,
                    'type'         => 'penalty',
                    'description'  => "Penarikan Dana / Sanksi Klaim Laporan Fiktif (#{$report->id}) Bantuan '{$help->title}'",
                    'reference_id' => $help->id,
                    'order_id'     => $help->order_id,
                    'status'       => 'completed',
                ]);
            }

            // 2. Refund 100% ke Customer
            $customerBalance = \App\Models\UserBalance::firstOrCreate(['user_id' => $customer->id], ['balance' => 0]);
            $customerBalance->refundToCustomer(
                $refundAmount,
                $help->id,
                $help->order_id,
                "Pengembalian Dana 100% (Klaim Laporan #{$report->id} Disetujui Admin - Bantuan '{$help->title}')"
            );

            // 3. Update Laporan
            $adminName = auth()->user()->name ?? 'Admin';
            $existingNotes = $report->admin_notes ? $report->admin_notes . "\n" : "";
            $extraNotes = $request->admin_notes ? " Catatan: " . $request->admin_notes : "";

            $report->update([
                'status'              => 'resolved',
                'refund_status'       => 'approved',
                'refund_amount'       => $refundAmount,
                'refund_processed_at' => now(),
                'refund_processed_by' => auth()->id(),
                'resolved_at'         => now(),
                'resolved_by'         => auth()->id(),
                'admin_notes'         => $existingNotes . "[Refund Rp " . number_format($refundAmount, 0, ',', '.') . " Disetujui oleh {$adminName} pada " . now()->format('d M Y, H:i') . " WIB.{$extraNotes}]",
            ]);

            // 4. Update Bantuan ke status Dibatalkan
            $help->update([
                'status'      => \App\Models\Help::STATUS_DIBATALKAN,
                'admin_notes' => "Dibatalkan & Direfund 100% melalui investigasi Laporan Aduan #{$report->id}",
            ]);

            // 5. Catat pesan sistem moderasi di percakapan laporan
            try {
                \App\Models\PartnerReportMessage::create([
                    'partner_report_id' => $report->id,
                    'sender_id'         => auth()->id(),
                    'recipient_type'    => 'all',
                    'message'           => "🛡️ [Moderasi Selesai] Pengembalian dana (refund) sebesar Rp " . number_format($refundAmount, 0, ',', '.') . " telah disetujui oleh {$adminName} dan dikreditkan ke saldo Customer.",
                ]);
            } catch (\Throwable $e) {}
        });

        return back()->with('success', "Pengembalian dana sebesar Rp " . number_format($refundAmount, 0, ',', '.') . " berhasil diproses dan dikreditkan ke saldo customer!");
    }

    /**
     * Tolak klaim refund customer (pertahankan hasil pengerjaan mitra).
     */
    public function rejectRefund(PartnerReport $report, Request $request)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $adminName = auth()->user()->name ?? 'Admin';
        $existingNotes = $report->admin_notes ? $report->admin_notes . "\n" : "";

        $report->update([
            'status'              => 'dismissed',
            'refund_status'       => 'rejected',
            'refund_processed_at' => now(),
            'refund_processed_by' => auth()->id(),
            'resolved_at'         => now(),
            'resolved_by'         => auth()->id(),
            'admin_notes'         => $existingNotes . "[Klaim Refund Ditolak oleh {$adminName} pada " . now()->format('d M Y, H:i') . " WIB. Alasan: " . $request->admin_notes . "]",
        ]);

        try {
            \App\Models\PartnerReportMessage::create([
                'partner_report_id' => $report->id,
                'sender_id'         => auth()->id(),
                'recipient_type'    => 'all',
                'message'           => "❌ [Moderasi Selesai] Klaim pengembalian dana telah ditinjau dan DITOLAK oleh {$adminName}. Alasan: " . $request->admin_notes,
            ]);
        } catch (\Throwable $e) {}

        return back()->with('success', 'Klaim pengembalian dana telah ditolak dan laporan ditandai sebagai dismissed.');
    }

    /**
     * Kirim pesan klarifikasi resmi dari Admin ke Customer dan/atau Mitra di thread Laporan Aduan.
     */
    public function sendMessage(PartnerReport $report, Request $request)
    {
        $request->validate([
            'target'  => 'required|in:all,customer,mitra',
            'message' => 'required_without:photo|nullable|string|max:3000',
            'photo'   => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('reports/messages', 'public');
        }

        $content = trim($request->input('message') ?? '');
        $target = $request->input('target', 'all');

        $messageRecord = \App\Models\PartnerReportMessage::create([
            'partner_report_id' => $report->id,
            'sender_id'         => auth()->id(),
            'recipient_type'    => $target,
            'message'           => $content ?: ($photoPath ? '[Lampiran Foto]' : ''),
            'photo'             => $photoPath,
            'is_read'           => false,
        ]);

        $customer = $report->reporter ?? $report->reportedHelp?->user;
        $mitra = $report->reportedUser ?? $report->reportedHelp?->mitra;
        $adminName = auth()->user()->name ?? 'Admin';

        // Notifikasi ke Customer jika target all / customer
        if (($target === 'all' || $target === 'customer') && $customer) {
            try {
                $customer->notify(new \App\Notifications\ChatMessageNotification(
                    $report->reported_help_id,
                    "Admin mengirim pesan klarifikasi di Laporan #{$report->id}: " . \Illuminate\Support\Str::limit($content, 100),
                    auth()->id(),
                    'Admin Moderasi SayaBantu'
                ));
            } catch (\Throwable $e) {}
        }

        // Notifikasi ke Mitra jika target all / mitra
        if (($target === 'all' || $target === 'mitra') && $mitra) {
            try {
                $mitra->notify(new \App\Notifications\ChatMessageNotification(
                    $report->reported_help_id,
                    "Admin meminta klarifikasi pada Laporan #{$report->id}: " . \Illuminate\Support\Str::limit($content, 100),
                    auth()->id(),
                    'Admin Moderasi SayaBantu'
                ));
            } catch (\Throwable $e) {}
        }

        // Update status laporan jika masih pending
        if ($report->status === 'pending') {
            $report->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Pesan resmi berhasil dikirimkan ke thread laporan aduan!');
    }

    /**
     * Customer mengirim balasan / bukti klarifikasi ke Admin pada laporan aduan.
     */
    public function replyCustomer(PartnerReport $report, Request $request)
    {
        $request->validate([
            'message' => 'required_without:photo|nullable|string|max:3000',
            'photo'   => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $userId = auth()->id();
        if ($report->reporter_id !== $userId && $report->reportedHelp?->user_id !== $userId) {
            abort(403, 'Unauthorized');
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('reports/messages', 'public');
        }

        $content = trim($request->input('message') ?? '');

        \App\Models\PartnerReportMessage::create([
            'partner_report_id' => $report->id,
            'sender_id'         => $userId,
            'recipient_type'    => 'admin',
            'message'           => $content ?: ($photoPath ? '[Lampiran Foto Bukti]' : ''),
            'photo'             => $photoPath,
            'is_read'           => false,
        ]);

        if ($report->status === 'pending') {
            $report->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Tanggapan dan bukti Anda berhasil dikirimkan ke Admin moderasi!');
    }

    /**
     * Mitra mengirim balasan / bukti klarifikasi ke Admin pada laporan aduan.
     */
    public function replyMitra(PartnerReport $report, Request $request)
    {
        $request->validate([
            'message' => 'required_without:photo|nullable|string|max:3000',
            'photo'   => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $userId = auth()->id();
        if ($report->reported_user_id !== $userId && $report->reportedHelp?->mitra_id !== $userId) {
            abort(403, 'Unauthorized');
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('reports/messages', 'public');
        }

        $content = trim($request->input('message') ?? '');

        \App\Models\PartnerReportMessage::create([
            'partner_report_id' => $report->id,
            'sender_id'         => $userId,
            'recipient_type'    => 'admin',
            'message'           => $content ?: ($photoPath ? '[Lampiran Foto Bukti]' : ''),
            'photo'             => $photoPath,
            'is_read'           => false,
        ]);

        if ($report->status === 'pending') {
            $report->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Klarifikasi dan bukti hasil pengerjaan Anda berhasil dikirimkan ke Admin!');
    }

    /**
     * Ruang Obrolan Khusus Sengketa & Klarifikasi Admin.
     */
    public function chatRoom(PartnerReport $report)
    {
        $report->load([
            'messages.sender',
            'reporter.balance',
            'reportedUser.balance',
            'reportedHelp.mitra',
            'reportedHelp.user',
        ]);

        $help = $report->reportedHelp;
        $customer = $report->reporter ?? $help?->user;
        $mitra = $report->reportedUser ?? $help?->mitra;

        return view('livewire.admin.partners.report-chat', compact('report', 'help', 'customer', 'mitra'));
    }

    /**
     * Ruang Obrolan Khusus Laporan Aduan Customer ke Admin (Diarahkan ke Chat Customer).
     */
    public function customerChatRoom(PartnerReport $report)
    {
        return redirect()->route('customer.chat', ['admin' => 1, 'report' => $report->id]);
    }

    /**
     * Ruang Obrolan Khusus Laporan Aduan Mitra ke Admin (Diarahkan ke Chat Mitra).
     */
    public function mitraChatRoom(PartnerReport $report)
    {
        return redirect()->route('mitra.chat', ['admin' => 1, 'report' => $report->id]);
    }
}
