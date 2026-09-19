<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi notifikasi in-app untuk Admin Wilayah.
     */
    public function run(): void
    {
        $admins = User::where('role', 'admin')->get();
        $now = now();

        foreach ($admins as $admin) {
            DB::table('notifications')->insert([
                [
                    'id'              => (string) Str::uuid(),
                    'type'            => 'App\\Notifications\\AdminNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $admin->id,
                    'data'            => json_encode([
                        'title'      => 'Pendaftaran Mitra Baru Perlu Verifikasi',
                        'message'    => 'Terdapat 1 pendaftar baru yang menunggu verifikasi berkas KTP di wilayah kerja Anda.',
                        'url'        => '/admin/verifications',
                        'created_at' => $now->copy()->subHours(2)->toISOString(),
                    ]),
                    'read_at'         => null,
                    'created_at'      => $now->copy()->subHours(2),
                    'updated_at'      => $now->copy()->subHours(2),
                ],
                [
                    'id'              => (string) Str::uuid(),
                    'type'            => 'App\\Notifications\\AdminNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $admin->id,
                    'data'            => json_encode([
                        'title'      => 'Pengajuan Penarikan Dana Baru',
                        'message'    => 'Mitra Budi Santoso mengajukan penarikan dana sebesar Rp 100.000.',
                        'url'        => '/admin/finances/withdrawals',
                        'created_at' => $now->copy()->subHours(1)->toISOString(),
                    ]),
                    'read_at'         => null,
                    'created_at'      => $now->copy()->subHours(1),
                    'updated_at'      => $now->copy()->subHours(1),
                ],
            ]);
        }

        $this->command->info('✓ AdminNotificationSeeder berhasil mengisi notifikasi admin.');
    }
}
