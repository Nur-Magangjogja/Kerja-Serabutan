<?php

namespace App\Http\Controllers;

use App\Models\AccountDeletionRequest;
use App\Models\Help;
use App\Models\UserBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AccountDeletionRequestController extends Controller
{
    /**
     * Ajukan permintaan penghapusan akun ke Superadmin.
     */
    public function requestDeletion(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'password' => ['required', 'string'],
            'reason'   => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'password.required' => 'Kata sandi saat ini wajib diisi untuk verifikasi keamanan.',
            'reason.required'   => 'Alasan penghapusan akun wajib diisi.',
            'reason.min'        => 'Alasan penghapusan minimal 5 karakter.',
            'reason.max'        => 'Alasan penghapusan maksimal 1000 karakter.',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Kata sandi yang Anda masukkan salah.'])->withInput();
        }

        // Cek jika sudah ada permintaan pending
        $existing = AccountDeletionRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('error', 'Anda sudah memiliki pengajuan penghapusan akun yang sedang ditinjau oleh Superadmin.');
        }

        // Snapshot data keuangan dan tugas aktif
        $userBalance = UserBalance::where('user_id', $user->id)->first();
        $balance = $userBalance ? (float) $userBalance->balance : 0.0;

        $activeHelps = $user->isMitra()
            ? Help::where('mitra_id', $user->id)->active()->count()
            : Help::where('user_id', $user->id)->active()->count();

        AccountDeletionRequest::create([
            'user_id'            => $user->id,
            'user_name'          => $user->name,
            'user_email'         => $user->email,
            'user_phone'         => $user->phone,
            'role'               => $user->role,
            'city_name'          => $user->city_name,
            'reason'             => $request->reason,
            'balance_at_request' => $balance,
            'active_helps_count' => $activeHelps,
            'status'             => 'pending',
        ]);

        Log::info("[AccountDeletion] User #{$user->id} ({$user->name}, {$user->role}) requested account deletion. Balance: Rp {$balance}, Active tasks: {$activeHelps}");

        return back()->with('message', 'Permintaan penghapusan akun Anda telah berhasil diajukan dan sedang menunggu peninjauan oleh tim Superadmin.');
    }

    /**
     * Batalkan permintaan penghapusan akun oleh pengguna.
     */
    public function cancelDeletion(Request $request)
    {
        $user = $request->user();

        $deletionRequest = AccountDeletionRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$deletionRequest) {
            return back()->with('error', 'Tidak ada permintaan penghapusan akun aktif yang dapat dibatalkan.');
        }

        $deletionRequest->update([
            'status' => 'cancelled',
        ]);

        Log::info("[AccountDeletion] User #{$user->id} ({$user->name}) cancelled account deletion request #{$deletionRequest->id}.");

        return back()->with('message', 'Permintaan penghapusan akun berhasil dibatalkan.');
    }
}
