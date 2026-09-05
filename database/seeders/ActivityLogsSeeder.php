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
     * Mengisi riwayat log aktivitas sistem dari 4 Agustus s/d 3 September 2026.
     */
    public function run(): void
    {
        $superAdmin     = User::where('email', 'superadmin@sayabantu.com')->first();
        $adminSleman    = User::whereIn('email', ['admin.sleman@sayabantu.com', 'admin@sayabantu.com'])->first();
        $adminSurakarta = User::where('email', 'admin.surakarta@sayabantu.com')->first();

        // 1. Log Registrasi dan Verifikasi (04 Agustus 2026)
        if ($adminSleman) {
            ActivityLog::create([
                'user_id'     => $adminSleman->id,
                'action'      => 'verify_user',
                'description' => 'Admin Sleman memverifikasi KTP dan data identitas Mitra Agus Prasetyo.',
                'ip_address'  => '127.0.0.1',
                'created_at'  => Carbon::parse('2026-08-04 08:30:00'),
                'updated_at'  => Carbon::parse('2026-08-04 08:30:00'),
            ]);
        }

        if ($adminSurakarta) {
            ActivityLog::create([
                'user_id'     => $adminSurakarta->id,
                'action'      => 'verify_user',
                'description' => 'Admin Surakarta memverifikasi KTP dan data identitas Mitra Eko Saputra.',
                'ip_address'  => '127.0.0.1',
                'created_at'  => Carbon::parse('2026-08-04 09:00:00'),
                'updated_at'  => Carbon::parse('2026-08-04 09:00:00'),
            ]);
        }

        // 2. Log Bantuan Selesai untuk setiap Tugas
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
                ActivityLog::create([
                    'user_id'     => $h->mitra_id,
                    'action'      => 'complete_help',
                    'description' => "Mitra menyelesaikan pesanan bantuan '{$h->title}' dan menerima pembayaran jasa.",
                    'ip_address'  => '127.0.0.1',
                    'created_at'  => $h->completed_at,
                    'updated_at'  => $h->completed_at,
                ]);
            }
        }

        // 3. Log Persetujuan Withdraw
        $withdraws = WithdrawRequest::all();
        foreach ($withdraws as $w) {
            $admin = ($w->user && (str_contains($w->user->email, 'surakarta') || str_contains($w->user->email, 'sukoharjo'))) ? $adminSurakarta : $adminSleman;
            if ($admin) {
                ActivityLog::create([
                    'user_id'     => $admin->id,
                    'action'      => 'approve_withdraw',
                    'description' => "Admin menyetujui penarikan saldo sebesar Rp " . number_format($w->amount, 0, ',', '.') . " untuk {$w->account_name} ({$w->bank_code}).",
                    'ip_address'  => '127.0.0.1',
                    'created_at'  => $w->processed_at ?? $w->created_at,
                    'updated_at'  => $w->processed_at ?? $w->created_at,
                ]);
            }
        }

        $this->command->info('ActivityLogsSeeder berhasil membuat riwayat aktivitas sistem untuk seluruh rentang tanggal 4 Agustus - 3 September 2026.');
    }
}
