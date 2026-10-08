<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class RejectedController extends Controller
{
    public function show(Registration $registration)
    {
        return view('auth.rejected', ['registration' => $registration]);
    }

    public function reapply(Request $request, Registration $registration)
    {
        $user = Auth::user();

        // Jika user belum login, coba cocokkan atau arahkan ke login
        if (!$user) {
            $user = User::where('email', strtolower(trim($registration->email)))->first();
            if ($user) {
                Auth::login($user);
            } else {
                return redirect()->route('login')->with('message', 'Silakan masuk terlebih dahulu untuk memperbaiki berkas pendaftaran Anda.');
            }
        } elseif (strtolower(trim($user->email)) !== strtolower(trim($registration->email))) {
            return redirect()->route('login')->with('message', 'Sesi akun tidak sesuai dengan data pendaftaran.');
        }

        // Kembalikan status pendaftaran menjadi in_progress agar sesuai enum dan dapat diedit kembali
        $registration->update([
            'status' => 'in_progress',
        ]);

        // Simpan UUID registrasi ke session dan cookie
        Session::put('registration_uuid', $registration->uuid);
        Cookie::queue('registration_uuid', $registration->uuid, 60 * 24);

        return redirect()->route('register.step1')->with('status', 'Silakan periksa kembali data Anda dan perbarui berkas yang ditolak.');
    }
}
