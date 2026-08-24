<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\WithdrawRequest;
use App\Models\AppSetting;
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

    /** Get route and view prefix based on user role */
    protected function getRoleContext($user): array
    {
        $isCustomer = ($user && $user->role === 'customer');
        return [
            'prefix' => $isCustomer ? 'customer.' : 'mitra.',
            'viewPath' => $isCustomer ? 'livewire.customer.withdraw.' : 'livewire.mitra.withdraw.',
            'dashboardRoute' => $isCustomer ? 'customer.dashboard' : 'mitra.dashboard',
        ];
    }

    /** Show withdraw form */
    public function showForm(Request $request)
    {
        $user = $request->user() ?? auth()->user();
        if (!$user) abort(401);
        $ctx = $this->getRoleContext($user);

        // If the user's latest withdraw was already processed successfully,
        // redirect them to the success page so they see the confirmation.
        $latest = WithdrawRequest::where('user_id', $user->id)->orderByDesc('created_at')->first();
        // If caller explicitly requests the form (force=1) skip redirect so "Kembali ke Pengajuan" works
        $force = $request->query('force');

        // Also avoid redirecting when the user clicked the link from their dashboard
        $referer = $request->headers->get('referer');
        $fromDashboard = false;
        try {
            $dashboardUrl = route($ctx['dashboardRoute']);
            if ($referer && str_contains($referer, $dashboardUrl)) {
                $fromDashboard = true;
            }
        } catch (\Throwable $e) {
            // ignore route exceptions
        }

        // Redirect to success only if the latest withdraw is success AND it was processed recently
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
                return redirect()->route($ctx['prefix'] . 'withdraw.success', $latest->id);
            }

            if ($latest->status === WithdrawRequest::STATUS_FAILED) {
                return redirect()->route($ctx['prefix'] . 'withdraw.rejected', $latest->id);
            }
        }

        // Otherwise render the withdraw form (the form view will show pending message if needed)
        $banks = AppSetting::getWithdrawBanks();
        $minAmount = AppSetting::getWithdrawMinAmount();
        $defaultFee = AppSetting::getWithdrawDefaultFee();
        $feeMode = AppSetting::getWithdrawFeeMode();

        return view($ctx['viewPath'] . 'form', [
            'user' => $user,
            'banks' => $banks,
            'minAmount' => $minAmount,
            'defaultFee' => $defaultFee,
            'feeMode' => $feeMode,
        ]);
    }

    /** Handle withdraw request */
    public function requestWithdraw(Request $request)
    {
        $user = $request->user() ?? auth()->user();
        if (!$user) abort(401);
        $ctx = $this->getRoleContext($user);
        $userBalance = (int) round((float) ($user->balance ?? 0));
        $minAmount = AppSetting::getWithdrawMinAmount();

        $request->validate([
            'amount' => ['required', 'integer', 'min:' . $minAmount, 'max:' . max($minAmount, $userBalance)],
            'bank_code' => ['required', 'string', 'max:50'],
            'account_number' => ['required', 'string', 'min:5', 'max:30'],
            'account_name' => ['required', 'string', 'min:3', 'max:100'],
        ], [
            'amount.required' => 'Nominal penarikan wajib diisi.',
            'amount.integer' => 'Nominal penarikan harus berupa angka bulat.',
            'amount.min' => 'Minimum penarikan saldo adalah Rp ' . number_format($minAmount, 0, ',', '.') . '.',
            'amount.max' => 'Nominal penarikan tidak boleh melebihi saldo tersedia (Rp ' . number_format($userBalance, 0, ',', '.') . ').',
            'bank_code.required' => 'Silakan pilih bank atau e-wallet tujuan.',
            'account_number.required' => 'Nomor rekening atau nomor e-wallet wajib diisi.',
            'account_number.min' => 'Nomor rekening minimal 5 karakter/digit.',
            'account_name.required' => 'Nama pemilik rekening / e-wallet wajib diisi.',
            'account_name.min' => 'Nama pemilik rekening minimal 3 karakter.',
        ]);

        $amount = (int) $request->input('amount');

        if ($userBalance < $minAmount) {
            return back()->withErrors(['amount' => 'Saldo Anda saat ini (Rp ' . number_format($userBalance, 0, ',', '.') . ') belum mencapai batas minimum penarikan Rp ' . number_format($minAmount, 0, ',', '.') . '.'])->withInput();
        }

        if ($amount > $userBalance) {
            return back()->withErrors(['amount' => 'Saldo Anda tidak mencukupi untuk melakukan penarikan sebesar Rp ' . number_format($amount, 0, ',', '.') . '. Saldo tersedia: Rp ' . number_format($userBalance, 0, ',', '.')])->withInput();
        }

        // Business checks
        if ($user->hasPendingOrProcessingWithdraws()) {
            return back()->withErrors(['general' => 'Anda masih memiliki permintaan tarik saldo yang sedang diproses. Mohon tunggu hingga proses sebelumnya selesai.'])->withInput();
        }

        try {
            $bankCode = strtoupper($request->input('bank_code'));
            $feeCalc = AppSetting::calculateWithdrawFee($bankCode, $amount);
            $adminFee = $feeCalc['fee'];
            $netAmount = $feeCalc['net_amount'];

            $accountName = trim($request->input('account_name'));
            $desc = 'A/N: ' . $accountName;

            $withdraw = WithdrawRequest::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'admin_fee' => $adminFee,
                'net_amount' => $netAmount,
                'bank_code' => $bankCode,
                'account_number' => $request->input('account_number'),
                'description' => $desc,
                'status' => WithdrawRequest::STATUS_PENDING,
            ]);

            $feeMsg = $adminFee === 0 
                ? ' (Bebas Biaya Admin - Rekening Platform)' 
                : ' (Biaya Admin Bank: Rp ' . number_format($adminFee, 0, ',', '.') . ', Dana Cair: Rp ' . number_format($netAmount, 0, ',', '.') . ')';

            return redirect()->route($ctx['prefix'] . 'withdraw.form')->with('status', 'Pengajuan penarikan dana sebesar Rp ' . number_format($amount, 0, ',', '.') . $feeMsg . ' berhasil dikirim dan sedang menunggu proses transfer dari admin.');
        } catch (\Throwable $e) {
            Log::error('WithdrawController: error creating withdraw', ['error' => $e->getMessage()]);
            return back()->withErrors(['general' => 'Terjadi kesalahan sistem saat membuat permintaan tarik saldo. Silakan coba kembali.'])->withInput();
        }
    }

    /** Show withdraw history for user */
    public function withdrawHistory(Request $request)
    {
        $user = $request->user() ?? auth()->user();
        if (!$user) abort(401);
        $ctx = $this->getRoleContext($user);
        $history = WithdrawRequest::where('user_id', $user->id)->orderByDesc('created_at')->paginate(20);
        return view($ctx['viewPath'] . 'history', ['history' => $history, 'user' => $user]);
    }

    /** Show success page for a completed withdraw */
    public function showSuccess(Request $request, WithdrawRequest $withdraw)
    {
        $user = $request->user() ?? auth()->user();
        if (!$user) abort(401);
        $ctx = $this->getRoleContext($user);
        if ($withdraw->user_id !== $user->id) {
            abort(403);
        }

        if ($withdraw->status !== WithdrawRequest::STATUS_SUCCESS) {
            return redirect()->route($ctx['prefix'] . 'withdraw.history')->with('status', 'Penarikan belum selesai atau tidak ditemukan.');
        }

        return view($ctx['viewPath'] . 'success', ['withdraw' => $withdraw, 'user' => $user]);
    }

    /** Show rejected page for a failed withdraw */
    public function showRejected(Request $request, WithdrawRequest $withdraw)
    {
        $user = $request->user() ?? auth()->user();
        if (!$user) abort(401);
        $ctx = $this->getRoleContext($user);
        if ($withdraw->user_id !== $user->id) {
            abort(403);
        }

        if ($withdraw->status !== WithdrawRequest::STATUS_FAILED) {
            return redirect()->route($ctx['prefix'] . 'withdraw.history')->with('status', 'Penarikan belum dibatalkan atau tidak ditemukan.');
        }

        return view($ctx['viewPath'] . 'rejected', ['withdraw' => $withdraw, 'user' => $user]);
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
