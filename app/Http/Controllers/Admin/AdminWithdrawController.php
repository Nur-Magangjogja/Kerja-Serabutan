<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Notifications\WithdrawStatusNotification;

class AdminWithdrawController extends Controller
{
    public function index()
    {
        // Accept filters from the request (GET)
        $request = request();
        $query = WithdrawRequest::with('user');

        // Filter by admin's city if user is admin
        if (auth()->user() && auth()->user()->role === 'admin' && auth()->user()->city_id) {
            $query->whereHas('user', function ($q) {
                $q->where('city_id', auth()->user()->city_id);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by bank code
        if ($request->filled('bank_code')) {
            $query->where('bank_code', $request->input('bank_code'));
        }

        // Filter by user search (id or name)
        if ($request->filled('user')) {
            $userTerm = $request->input('user');
            $query->where(function ($q) use ($userTerm) {
                $q->where('user_id', $userTerm)
                    ->orWhereHas('user', function ($uq) use ($userTerm) {
                        $uq->where('name', 'like', "%{$userTerm}%");
                    });
            });
        }

        // Filter by date range (created_at)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Order and paginate (keep query string)
        $items = $query->orderByDesc('created_at')->paginate(25)->appends($request->except('page'));

        // For filter controls: list of bank codes present
        $banks = WithdrawRequest::select('bank_code')->distinct()->orderBy('bank_code')->pluck('bank_code');

        // Summary counts - filter by admin's city
        $countsQuery = WithdrawRequest::query();
        if (auth()->user() && auth()->user()->role === 'admin' && auth()->user()->city_id) {
            $countsQuery->whereHas('user', function ($q) {
                $q->where('city_id', auth()->user()->city_id);
            });
        }

        $counts = [
            'all' => (clone $countsQuery)->count(),
            'pending' => (clone $countsQuery)->where('status', WithdrawRequest::STATUS_PENDING)->count(),
            'processing' => (clone $countsQuery)->where('status', WithdrawRequest::STATUS_PROCESSING)->count(),
            'success' => (clone $countsQuery)->where('status', WithdrawRequest::STATUS_SUCCESS)->count(),
            'failed' => (clone $countsQuery)->where('status', WithdrawRequest::STATUS_FAILED)->count(),
        ];

        $routeName = request()->route() ? request()->route()->getName() : null;
        if ($routeName && strpos($routeName, 'superadmin.') === 0) {
            return view('superadmin.withdraws.index', ['items' => $items, 'banks' => $banks, 'counts' => $counts]);
        }

        return view('admin.withdraws.index', ['items' => $items, 'banks' => $banks, 'counts' => $counts]);
    }

    public function show(WithdrawRequest $withdraw)
    {
        $withdraw->load('user');
        $routeName = request()->route() ? request()->route()->getName() : null;
        if ($routeName && strpos($routeName, 'superadmin.') === 0) {
            return view('superadmin.withdraws.show', ['withdraw' => $withdraw]);
        }

        return view('admin.withdraws.show', ['withdraw' => $withdraw]);
    }

    /** Return withdraw details as a partial for modal (AJAX) */
    public function modal(WithdrawRequest $withdraw)
    {
        $withdraw->load('user');
        $routeName = request()->route() ? request()->route()->getName() : null;
        if ($routeName && strpos($routeName, 'superadmin.') === 0) {
            return view('superadmin.withdraws._modal', ['withdraw' => $withdraw]);
        }

        return view('admin.withdraws._modal', ['withdraw' => $withdraw]);
    }

    /** Approve and perform deduction */
    public function approve(Request $request, WithdrawRequest $withdraw)
    {
        $request->validate([
            'transfer_reference' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
        ]);

        if ($withdraw->status !== WithdrawRequest::STATUS_PENDING) {
            return back()->withErrors(['general' => 'Hanya request dengan status pending yang dapat diproses.']);
        }

        $user = $withdraw->user;
        $userBalance = $user->balance()->first();
        $current = $userBalance ? (int) round((float) $userBalance->balance) : 0;

        if ($current < $withdraw->amount) {
            return back()->withErrors(['general' => 'Saldo user tidak mencukupi untuk melakukan transfer.']);
        }

        try {
            // Deduct balance now
            $user->adjustBalance(-((int) $withdraw->amount));

            $withdraw->update([
                'status' => WithdrawRequest::STATUS_SUCCESS,
                'processed_at' => now(),
                'description' => $request->input('note') ? $request->input('note') : $withdraw->description,
                'external_id' => $request->input('transfer_reference') ?? $withdraw->external_id,
            ]);

            // Notify user via database notification with link to success page
            try {
                $withdraw->user?->notify(new WithdrawStatusNotification($withdraw));
            } catch (\Throwable $e) {
                Log::warning('Failed to send withdraw success notification: ' . $e->getMessage());
            }

            return redirect()->route('superadmin.withdraws.index')->with('status', 'Withdraw berhasil diproses dan saldo telah dipotong.');
        } catch (\Throwable $e) {
            Log::error('AdminWithdrawController: approve error', ['error' => $e->getMessage(), 'withdraw_id' => $withdraw->id]);
            return back()->withErrors(['general' => 'Terjadi kesalahan saat memproses withdraw.']);
        }
    }

    public function reject(Request $request, WithdrawRequest $withdraw)
    {
        $request->validate(['note' => ['nullable', 'string']]);

        if ($withdraw->status !== WithdrawRequest::STATUS_PENDING) {
            return back()->withErrors(['general' => 'Hanya request dengan status pending yang dapat dibatalkan.']);
        }

        $withdraw->update([
            'status' => WithdrawRequest::STATUS_FAILED,
            'processed_at' => now(),
            'description' => $request->input('note') ?? $withdraw->description,
        ]);

        // Notify user about rejection/failure
        try {
            $withdraw->user?->notify(new WithdrawStatusNotification($withdraw));
        } catch (\Throwable $e) {
            Log::warning('Failed to send withdraw failed notification: ' . $e->getMessage());
        }

        return redirect()->route('superadmin.withdraws.index')->with('status', 'Withdraw dibatalkan.');
    }
}
