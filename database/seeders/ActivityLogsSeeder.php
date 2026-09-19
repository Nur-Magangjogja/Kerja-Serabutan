<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Help;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi riwayat log aktivitas sistem secara dinamis untuk pengujian audit log admin.
     */
    public function run(): void
    {
        $admins = User::where('role', 'admin')->get();
        $now = now();

        // 1. Log Verifikasi Admin
        foreach ($admins as $admin) {
            ActivityLog::create([
                'user_id'     => $admin->id,
                'action'      => 'verify_user',
                'description' => "Admin {$admin->name} memverifikasi berkas identitas & KTP pendaftar.",
                'ip_address'  => '127.0.0.1',
                'created_at'  => $now->copy()->subDays(2),
                'updated_at'  => $now->copy()->subDays(2),
            ]);
        }

        // 2. Log Bantuan
        $helps = Help::all();
        foreach ($helps as $h) {
            if ($h->user_id) {
                ActivityLog::create([
                    'user_id'     => $h->user_id,
                    'action'      => 'create_help',
                    'description' => "Customer membuat pesanan bantuan '{$h->title}' (Order ID: {$h->order_id}).",
                    'ip_address'  => '127.0.0.1',
                    'created_at'  => $h->created_at,
                    'updated_at'  => $h->created_at,
                ]);
            }

            if ($h->mitra_id && in_array($h->status, ['in_progress', 'waiting_customer_confirmation', 'selesai'])) {
                ActivityLog::create([
                    'user_id'     => $h->mitra_id,
                    'action'      => 'take_help',
                    'description' => "Mitra mengambil dan menyetujui pesanan bantuan '{$h->title}'.",
                    'ip_address'  => '127.0.0.1',
                    'created_at'  => $h->taken_at ?? $h->created_at,
                    'updated_at'  => $h->taken_at ?? $h->created_at,
                ]);
            }
        }

        $this->command->info('✓ ActivityLogsSeeder berhasil mengisi log aktivitas.');
    }
}
