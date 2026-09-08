<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Help;
use App\Models\User;
use App\Models\WithdrawRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ActivityLogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi riwayat log aktivitas sistem lengkap & kronologis dari 1 Agustus – 8 September 2026.
     * Mencakup: Admin verifikasi, Registrasi/Login/KYC per-user, pembuatan & penyelesaian bantuan,
     * pencairan pendapatan mitra, serta persetujuan/penolakan penarikan dana.
     */
    public function run(): void
    {
        $superAdmin = User::where('email', 'superadmin@sayabantu.com')->first();
        $admins     = User::where('role', 'admin')->get();

        // ─────────────────────────────────────────────────────────────────────
        // 1. Log Verifikasi KYC oleh Admin Wilayah (batch awal 4 Agustus)
        // ─────────────────────────────────────────────────────────────────────
        foreach ($admins as $admin) {
            ActivityLog::create([
                'user_id'     => $admin->id,
                'action'      => 'verify_user',
                'description' => "Admin {$admin->name} memverifikasi berkas KTP & identitas pendaftar di wilayah kerjanya.",
                'ip_address'  => '127.0.0.1',
                'created_at'  => Carbon::parse('2026-08-04 08:30:00'),
                'updated_at'  => Carbon::parse('2026-08-04 08:30:00'),
            ]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // 2. Log Pembuatan & Penyelesaian Bantuan (hanya status selesai)
        // ─────────────────────────────────────────────────────────────────────
        $helps = Help::where('status', Help::STATUS_SELESAI)->orderBy('created_at')->get();
        foreach ($helps as $h) {
            if ($h->user_id) {
                ActivityLog::create([
                    'user_id'     => $h->user_id,
                    'action'      => 'create_help',
                    'description' => "Customer membuat pesanan bantuan '{$h->title}' (Order: {$h->order_id}).",
                    'ip_address'  => '127.0.0.1',
                    'created_at'  => $h->created_at,
                    'updated_at'  => $h->created_at,
                ]);
            }

            if ($h->mitra_id) {
                $completedAt = $h->completed_at ?? $h->created_at->copy()->addHours(2);
                ActivityLog::create([
                    'user_id'     => $h->mitra_id,
                    'action'      => 'complete_help',
                    'description' => "Mitra menyelesaikan pesanan bantuan '{$h->title}' dan menerima pembayaran jasa.",
                    'ip_address'  => '127.0.0.1',
                    'created_at'  => $completedAt,
                    'updated_at'  => $completedAt,
                ]);
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 3. Log Persetujuan / Penolakan Withdraw oleh Admin Wilayah
        // ─────────────────────────────────────────────────────────────────────
        $withdraws     = WithdrawRequest::all();
        $fallbackAdmin = $admins->first() ?? $superAdmin;

        foreach ($withdraws as $w) {
            $wUser = $w->user;
            $matchedAdmin = null;
            if ($wUser && $wUser->district_id) {
                $matchedAdmin = $admins->first(fn($adm) => $adm->hasAccessToDistrict($wUser->district_id));
            }
            if (!$matchedAdmin && $wUser && $wUser->city_id) {
                $matchedAdmin = $admins->first(fn($adm) => $adm->city_id == $wUser->city_id);
            }
            $actingAdmin = $matchedAdmin ?? $fallbackAdmin;

            if ($actingAdmin) {
                $action    = $w->status === 'rejected' ? 'reject_withdraw' : 'approve_withdraw';
                $amountFmt = number_format($w->amount, 0, ',', '.');
                $desc      = $w->status === 'rejected'
                    ? "Admin {$actingAdmin->name} menolak penarikan saldo Rp {$amountFmt} atas nama {$w->account_name} ({$w->bank_code}): rekening tidak valid."
                    : "Admin {$actingAdmin->name} menyetujui penarikan saldo Rp {$amountFmt} untuk {$w->account_name} ({$w->bank_code}).";

                ActivityLog::create([
                    'user_id'     => $actingAdmin->id,
                    'action'      => $action,
                    'description' => $desc,
                    'ip_address'  => '127.0.0.1',
                    'created_at'  => $w->processed_at ?? $w->created_at,
                    'updated_at'  => $w->processed_at ?? $w->created_at,
                ]);

                // Log dari sisi mitra / user yang mengajukan withdraw
                if ($wUser) {
                    $userDesc = $w->status === 'success'
                        ? "Penarikan saldo Rp {$amountFmt} berhasil diproses ke rekening {$w->bank_code} a.n. {$w->account_name}."
                        : "Penarikan saldo Rp {$amountFmt} ditolak: rekening tidak valid.";
                    ActivityLog::create([
                        'user_id'     => $wUser->id,
                        'action'      => 'withdraw_' . $w->status,
                        'description' => $userDesc,
                        'ip_address'  => '127.0.0.1',
                        'created_at'  => $w->processed_at ?? $w->created_at,
                        'updated_at'  => $w->processed_at ?? $w->created_at,
                    ]);
                }
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 4. Log per-user: Registrasi, Login Pertama, dan KYC/Verifikasi
        // ─────────────────────────────────────────────────────────────────────
        $registrationMap = [
            // ── Customer ────────────────────────────────────────────────────
            'customer.sleman1@sayabantu.com'    => ['reg' => '2026-08-02 07:30:00', 'login' => '2026-08-02 07:45:00', 'kyc' => '2026-08-03 09:00:00'],
            'customer.sleman2@sayabantu.com'    => ['reg' => '2026-08-03 08:00:00', 'login' => '2026-08-03 08:10:00', 'kyc' => '2026-08-04 08:30:00'],
            'customer@sayabantu.com'            => ['reg' => '2026-08-01 10:00:00', 'login' => '2026-08-01 10:15:00', 'kyc' => '2026-08-02 09:00:00'],
            'customer.sleman3@sayabantu.com'    => ['reg' => '2026-08-04 09:00:00', 'login' => '2026-08-04 09:15:00', 'kyc' => '2026-08-05 08:00:00'],
            'customer.jogja1@sayabantu.com'     => ['reg' => '2026-08-02 09:00:00', 'login' => '2026-08-02 09:20:00', 'kyc' => '2026-08-04 10:00:00'],
            'customer.jogja2@sayabantu.com'     => ['reg' => '2026-08-05 08:00:00', 'login' => '2026-08-05 08:15:00', 'kyc' => '2026-08-06 09:00:00'],
            'customer.surakarta1@sayabantu.com' => ['reg' => '2026-08-02 08:30:00', 'login' => '2026-08-02 08:45:00', 'kyc' => '2026-08-04 09:30:00'],
            'customer.surakarta2@sayabantu.com' => ['reg' => '2026-08-03 09:00:00', 'login' => '2026-08-03 09:15:00', 'kyc' => '2026-08-05 08:30:00'],
            'customer.sukoharjo1@sayabantu.com' => ['reg' => '2026-08-03 07:45:00', 'login' => '2026-08-03 08:00:00', 'kyc' => '2026-08-04 10:00:00'],
            'customer.sukoharjo2@sayabantu.com' => ['reg' => '2026-08-04 07:30:00', 'login' => '2026-08-04 07:45:00', 'kyc' => '2026-08-05 09:00:00'],
            'customer.semarang1@sayabantu.com'  => ['reg' => '2026-08-10 08:00:00', 'login' => '2026-08-10 08:20:00', 'kyc' => '2026-08-12 09:00:00'],
            'customer.jaksel1@sayabantu.com'    => ['reg' => '2026-08-05 09:00:00', 'login' => '2026-08-05 09:15:00', 'kyc' => '2026-08-07 10:00:00'],
            'customer.jaksel2@sayabantu.com'    => ['reg' => '2026-08-06 08:30:00', 'login' => '2026-08-06 08:45:00', 'kyc' => '2026-08-08 09:30:00'],
            'customer.jaksel3@sayabantu.com'    => ['reg' => '2026-08-07 08:00:00', 'login' => '2026-08-07 08:15:00', 'kyc' => '2026-08-09 10:00:00'],
            'customer.jakbar1@sayabantu.com'    => ['reg' => '2026-08-07 09:00:00', 'login' => '2026-08-07 09:15:00', 'kyc' => '2026-08-09 11:00:00'],
            'customer.jaktim1@sayabantu.com'    => ['reg' => '2026-08-08 07:30:00', 'login' => '2026-08-08 07:45:00', 'kyc' => '2026-08-10 09:00:00'],
            'customer.bandung1@sayabantu.com'   => ['reg' => '2026-08-06 09:00:00', 'login' => '2026-08-06 09:15:00', 'kyc' => '2026-08-08 10:00:00'],
            'customer.bandung2@sayabantu.com'   => ['reg' => '2026-08-09 08:30:00', 'login' => '2026-08-09 08:45:00', 'kyc' => '2026-08-11 09:30:00'],
            'customer.surabaya1@sayabantu.com'  => ['reg' => '2026-08-10 09:00:00', 'login' => '2026-08-10 09:15:00', 'kyc' => '2026-08-12 10:00:00'],
            'customer.surabaya2@sayabantu.com'  => ['reg' => '2026-08-12 08:00:00', 'login' => '2026-08-12 08:20:00', 'kyc' => '2026-08-14 09:00:00'],
            'customer.malang1@sayabantu.com'    => ['reg' => '2026-08-11 07:45:00', 'login' => '2026-08-11 08:00:00', 'kyc' => '2026-08-13 09:30:00'],
            'customer.denpasar1@sayabantu.com'  => ['reg' => '2026-08-15 09:00:00', 'login' => '2026-08-15 09:15:00', 'kyc' => '2026-08-17 10:00:00'],
            'customer.denpasar2@sayabantu.com'  => ['reg' => '2026-08-16 08:00:00', 'login' => '2026-08-16 08:15:00', 'kyc' => '2026-08-18 09:00:00'],
            'customer.medan1@sayabantu.com'     => ['reg' => '2026-08-17 08:30:00', 'login' => '2026-08-17 08:45:00', 'kyc' => '2026-08-19 09:30:00'],
            'customer.medan2@sayabantu.com'     => ['reg' => '2026-08-18 09:00:00', 'login' => '2026-08-18 09:15:00', 'kyc' => '2026-08-20 10:00:00'],
            'customer.palembang1@sayabantu.com' => ['reg' => '2026-08-19 08:00:00', 'login' => '2026-08-19 08:15:00', 'kyc' => '2026-08-21 09:00:00'],
            'customer.makassar1@sayabantu.com'  => ['reg' => '2026-08-20 09:00:00', 'login' => '2026-08-20 09:15:00', 'kyc' => '2026-08-22 10:00:00'],
            'customer.makassar2@sayabantu.com'  => ['reg' => '2026-08-22 08:00:00', 'login' => '2026-08-22 08:15:00', 'kyc' => '2026-08-24 09:30:00'],
            'customer.tangsel1@sayabantu.com'   => ['reg' => '2026-08-23 08:30:00', 'login' => '2026-08-23 08:45:00', 'kyc' => '2026-08-25 09:00:00'],
            // ── Mitra ───────────────────────────────────────────────────────
            'mitra.sleman1@sayabantu.com'       => ['reg' => '2026-08-01 09:00:00', 'login' => '2026-08-01 09:15:00', 'kyc' => '2026-08-03 10:00:00'],
            'mitra.sleman2@sayabantu.com'       => ['reg' => '2026-08-02 09:00:00', 'login' => '2026-08-02 09:20:00', 'kyc' => '2026-08-04 10:30:00'],
            'mitra.sleman3@sayabantu.com'       => ['reg' => '2026-08-03 08:30:00', 'login' => '2026-08-03 08:45:00', 'kyc' => '2026-08-05 09:00:00'],
            'mitra@sayabantu.com'               => ['reg' => '2026-08-01 09:30:00', 'login' => '2026-08-01 09:45:00', 'kyc' => '2026-08-02 10:00:00'],
            'mitra.jogja1@sayabantu.com'        => ['reg' => '2026-08-02 10:00:00', 'login' => '2026-08-02 10:15:00', 'kyc' => '2026-08-04 11:00:00'],
            'mitra.jogja2@sayabantu.com'        => ['reg' => '2026-08-03 09:30:00', 'login' => '2026-08-03 09:45:00', 'kyc' => '2026-08-05 10:00:00'],
            'mitra.jogja3@sayabantu.com'        => ['reg' => '2026-08-04 09:00:00', 'login' => '2026-08-04 09:15:00', 'kyc' => '2026-08-06 09:30:00'],
            'mitra.surakarta1@sayabantu.com'    => ['reg' => '2026-08-01 10:00:00', 'login' => '2026-08-01 10:15:00', 'kyc' => '2026-08-03 09:30:00'],
            'mitra.surakarta2@sayabantu.com'    => ['reg' => '2026-08-02 10:30:00', 'login' => '2026-08-02 10:45:00', 'kyc' => '2026-08-04 09:00:00'],
            'mitra.sukoharjo1@sayabantu.com'    => ['reg' => '2026-08-02 09:30:00', 'login' => '2026-08-02 09:45:00', 'kyc' => '2026-08-04 10:00:00'],
            'mitra.sukoharjo2@sayabantu.com'    => ['reg' => '2026-08-03 10:00:00', 'login' => '2026-08-03 10:15:00', 'kyc' => '2026-08-05 09:30:00'],
            'mitra.semarang1@sayabantu.com'     => ['reg' => '2026-08-10 09:00:00', 'login' => '2026-08-10 09:20:00', 'kyc' => '2026-08-12 10:30:00'],
            'mitra.semarang2@sayabantu.com'     => ['reg' => '2026-08-11 09:00:00', 'login' => '2026-08-11 09:15:00', 'kyc' => '2026-08-13 10:00:00'],
            'mitra.jaksel1@sayabantu.com'       => ['reg' => '2026-08-05 10:00:00', 'login' => '2026-08-05 10:15:00', 'kyc' => '2026-08-07 09:30:00'],
            'mitra.jaksel2@sayabantu.com'       => ['reg' => '2026-08-06 09:30:00', 'login' => '2026-08-06 09:45:00', 'kyc' => '2026-08-08 10:00:00'],
            'mitra.jaksel3@sayabantu.com'       => ['reg' => '2026-08-07 09:00:00', 'login' => '2026-08-07 09:15:00', 'kyc' => '2026-08-09 10:30:00'],
            'mitra.jakbar1@sayabantu.com'       => ['reg' => '2026-08-07 10:00:00', 'login' => '2026-08-07 10:15:00', 'kyc' => '2026-08-09 11:30:00'],
            'mitra.jaktim1@sayabantu.com'       => ['reg' => '2026-08-08 09:00:00', 'login' => '2026-08-08 09:15:00', 'kyc' => '2026-08-10 10:00:00'],
            'mitra.bandung1@sayabantu.com'      => ['reg' => '2026-08-06 10:00:00', 'login' => '2026-08-06 10:15:00', 'kyc' => '2026-08-08 10:30:00'],
            'mitra.bandung2@sayabantu.com'      => ['reg' => '2026-08-09 09:30:00', 'login' => '2026-08-09 09:45:00', 'kyc' => '2026-08-11 10:00:00'],
            'mitra.bandung3@sayabantu.com'      => ['reg' => '2026-08-10 08:30:00', 'login' => '2026-08-10 08:45:00', 'kyc' => '2026-08-12 09:30:00'],
            'mitra.surabaya1@sayabantu.com'     => ['reg' => '2026-08-10 10:00:00', 'login' => '2026-08-10 10:15:00', 'kyc' => '2026-08-12 11:00:00'],
            'mitra.surabaya2@sayabantu.com'     => ['reg' => '2026-08-12 09:00:00', 'login' => '2026-08-12 09:15:00', 'kyc' => '2026-08-14 10:00:00'],
            'mitra.surabaya3@sayabantu.com'     => ['reg' => '2026-08-13 08:30:00', 'login' => '2026-08-13 08:45:00', 'kyc' => '2026-08-15 09:30:00'],
            'mitra.malang1@sayabantu.com'       => ['reg' => '2026-08-11 10:00:00', 'login' => '2026-08-11 10:15:00', 'kyc' => '2026-08-13 10:30:00'],
            'mitra.denpasar1@sayabantu.com'     => ['reg' => '2026-08-15 10:00:00', 'login' => '2026-08-15 10:15:00', 'kyc' => '2026-08-17 11:00:00'],
            'mitra.denpasar2@sayabantu.com'     => ['reg' => '2026-08-16 09:00:00', 'login' => '2026-08-16 09:15:00', 'kyc' => '2026-08-18 10:00:00'],
            'mitra.medan1@sayabantu.com'        => ['reg' => '2026-08-17 09:30:00', 'login' => '2026-08-17 09:45:00', 'kyc' => '2026-08-19 10:30:00'],
            'mitra.medan2@sayabantu.com'        => ['reg' => '2026-08-18 10:00:00', 'login' => '2026-08-18 10:15:00', 'kyc' => '2026-08-20 11:00:00'],
            'mitra.palembang1@sayabantu.com'    => ['reg' => '2026-08-19 09:00:00', 'login' => '2026-08-19 09:15:00', 'kyc' => '2026-08-21 10:00:00'],
            'mitra.makassar1@sayabantu.com'     => ['reg' => '2026-08-20 10:00:00', 'login' => '2026-08-20 10:15:00', 'kyc' => '2026-08-22 11:00:00'],
            'mitra.makassar2@sayabantu.com'     => ['reg' => '2026-08-22 09:00:00', 'login' => '2026-08-22 09:15:00', 'kyc' => '2026-08-24 10:30:00'],
            'mitra.tangsel1@sayabantu.com'      => ['reg' => '2026-08-23 09:30:00', 'login' => '2026-08-23 09:45:00', 'kyc' => '2026-08-25 10:00:00'],
        ];

        $allUsersKeyed = User::all()->keyBy('email');

        foreach ($registrationMap as $email => $dates) {
            $user = $allUsersKeyed->get($email);
            if (!$user) continue;

            $regAt   = Carbon::parse($dates['reg']);
            $loginAt = Carbon::parse($dates['login']);
            $kycAt   = Carbon::parse($dates['kyc']);

            ActivityLog::create([
                'user_id'     => $user->id,
                'action'      => 'user_registered',
                'description' => "{$user->name} mendaftar sebagai {$user->role} di platform SayaBantu.",
                'ip_address'  => '127.0.0.1',
                'created_at'  => $regAt,
                'updated_at'  => $regAt,
            ]);

            ActivityLog::create([
                'user_id'     => $user->id,
                'action'      => 'user_login',
                'description' => "{$user->name} melakukan login pertama setelah mendaftar.",
                'ip_address'  => '127.0.0.1',
                'created_at'  => $loginAt,
                'updated_at'  => $loginAt,
            ]);

            ActivityLog::create([
                'user_id'     => $user->id,
                'action'      => 'kyc_verified',
                'description' => "Identitas & KTP {$user->name} telah diverifikasi dan disetujui oleh admin wilayah.",
                'ip_address'  => '127.0.0.1',
                'created_at'  => $kycAt,
                'updated_at'  => $kycAt,
            ]);
        }

        $this->command->info('ActivityLogsSeeder berhasil membuat riwayat aktivitas sistem lengkap & kronologis untuk seluruh wilayah di Indonesia.');
    }
}
