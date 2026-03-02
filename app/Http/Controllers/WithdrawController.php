<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\WithdrawRequest;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WithdrawController extends Controller
{
    protected PaymentGatewayService $gateway;

    public function __construct(PaymentGatewayService $gateway)
    {
        $this->gateway = $gateway;
    }

    /** Show withdraw form */
    public function showForm(Request $request)
    {
        $user = $request->user();
        // If the user's latest withdraw was already processed successfully,
        // redirect them to the success page so they see the confirmation.
        $latest = WithdrawRequest::where('user_id', $user->id)->orderByDesc('created_at')->first();
        // If caller explicitly requests the form (force=1) skip redirect so "Kembali ke Pengajuan" works
        $force = $request->query('force');

        // Also avoid redirecting when the user clicked the link from the mitra dashboard
        $referer = $request->headers->get('referer');
        $fromDashboard = false;
        try {
            $dashboardUrl = route('mitra.dashboard');
            if ($referer && str_contains($referer, $dashboardUrl)) {
                $fromDashboard = true;
            }
        } catch (\Throwable $e) {
            // ignore route exceptions
        }

        // Redirect to success only if the latest withdraw is success AND it was processed recently
        // This avoids redirecting to the success page on refresh long after processing occurred.
        $recentWindowMinutes = 15;
        $isRecent = false;
        if ($latest && $latest->processed_at) {
            try {
                $isRecent = Carbon::parse($latest->processed_at)->greaterThanOrEqualTo(Carbon::now()->subMinutes($recentWindowMinutes));
            } catch (\Throwable $e) {
                $isRecent = false;
            }
        }

        if (!$force && !$fromDashboard && $latest && $isRecent) {
            if ($latest->status === WithdrawRequest::STATUS_SUCCESS) {
                return redirect()->route('mitra.withdraw.success', $latest->id);
            }

            if ($latest->status === WithdrawRequest::STATUS_FAILED) {
                return redirect()->route('mitra.withdraw.rejected', $latest->id);
            }
        }

        // Otherwise render the withdraw form (the form view will show pending message if needed)
        return view('livewire.mitra.withdraw.form', ['user' => $user]);
    }

    /** Handle withdraw request */
    public function requestWithdraw(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'integer', 'min:10000'],
            'bank_code' => ['required', 'string'],
            'account_number' => ['required', 'string'],
        ]);

        $user = $request->user();
        $amount = (int) $request->input('amount');

        // Business checks
        if ($user->hasPendingOrProcessingWithdraws()) {
            return back()->withErrors(['general' => 'Anda memiliki permintaan tarik saldo yang sedang diproses. Mohon tunggu hingga selesai.']);
        }

        try {
            // Create withdraw request WITHOUT deducting balance.
            // Admin will review and deduct when approving the transfer.
            $withdraw = WithdrawRequest::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'bank_code' => $request->input('bank_code'),
                'account_number' => $request->input('account_number'),
                'status' => WithdrawRequest::STATUS_PENDING,
            ]);

            // Redirect back to form — the form view will show a 'pending' message when a request exists
            return redirect()->route('mitra.withdraw.form')->with('status', 'Pengajuan Anda berhasil dikirim dan sedang menunggu proses oleh admin. Mohon tunggu 1-5 hari kerja.');
        } catch (\Throwable $e) {
            Log::error('WithdrawController: error creating withdraw', ['error' => $e->getMessage()]);
            return back()->withErrors(['general' => 'Terjadi kesalahan saat membuat permintaan tarik saldo.']);
        }
    }

    /** Show withdraw history for user */
    public function withdrawHistory(Request $request)
    {
        $user = $request->user();
        $history = WithdrawRequest::where('user_id', $user->id)->orderByDesc('created_at')->paginate(20);
        return view('livewire.mitra.withdraw.history', ['history' => $history, 'user' => $user]);
    }

    /** Show success page for a completed withdraw (Mitra) */
    public function showSuccess(Request $request, WithdrawRequest $withdraw)
    {
        $user = $request->user();
        if ($withdraw->user_id !== $user->id) {
            abort(403);
        }

        if ($withdraw->status !== WithdrawRequest::STATUS_SUCCESS) {
            return redirect()->route('mitra.withdraw.history')->with('status', 'Penarikan belum selesai atau tidak ditemukan.');
        }

        return view('livewire.mitra.withdraw.success', ['withdraw' => $withdraw, 'user' => $user]);
    }

    /** Show rejected page for a failed withdraw (Mitra) */
    public function showRejected(Request $request, WithdrawRequest $withdraw)
    {
        $user = $request->user();
        if ($withdraw->user_id !== $user->id) {
            abort(403);
        }

        if ($withdraw->status !== WithdrawRequest::STATUS_FAILED) {
            return redirect()->route('mitra.withdraw.history')->with('status', 'Penarikan belum dibatalkan atau tidak ditemukan.');
        }

        return view('livewire.mitra.withdraw.rejected', ['withdraw' => $withdraw, 'user' => $user]);
    }

    /** Public endpoint: gateway callback (for real integration) */
    public function gatewayCallback(Request $request)
    {
        // In production validate signature / secret
        $payload = $request->all();

        if (empty($payload['external_id']) || empty($payload['status'])) {
            return response()->json(['error' => 'invalid_payload'], 422);
        }

        $withdraw = WithdrawRequest::where('external_id', $payload['external_id'])->first();
        if (!$withdraw) {
            return response()->json(['error' => 'not_found'], 404);
        }

        $status = $payload['status'];

        if ($status === WithdrawRequest::STATUS_SUCCESS) {
            $withdraw->update(['status' => WithdrawRequest::STATUS_SUCCESS, 'processed_at' => now()]);
        } else {
            $withdraw->update(['status' => WithdrawRequest::STATUS_FAILED, 'processed_at' => now()]);
            // refund
            $user = $withdraw->user;
            if ($user) {
                $user->adjustBalance($withdraw->amount);
            }
        }

        return response()->json(['ok' => true]);
    }
}
