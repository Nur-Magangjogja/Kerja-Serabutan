<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminSleman = User::whereIn('email', ['admin.sleman@sayabantu.com', 'admin@sayabantu.com'])->first();
        $adminSolo   = User::where('email', 'admin.surakarta@sayabantu.com')->first();
        
        $notifications = [];

        if ($adminSleman) {
            $notifications[] = [
                'admin_id' => $adminSleman->id,
                'type'     => 'App\Notifications\CustomNotification',
                'data'     => [
                    'type'    => 'new_registration',
                    'message' => 'Pendaftaran mitra baru (Agus Prasetyo - Sleman) telah diverifikasi.',
                ],
                'read_at'    => Carbon::parse('2026-08-05 10:00:00'),
                'created_at' => Carbon::parse('2026-08-05 09:00:00'),
            ];

            $notifications[] = [
                'admin_id' => $adminSleman->id,
                'type'     => 'App\Notifications\CustomNotification',
                'data'     => [
                    'type'    => 'help_completed',
                    'message' => 'Bantuan "Bantu Pindahan & Angkat Kasur Kos Dekat Kampus UNY/UGM" telah selesai dikonfirmasi.',
                ],
                'read_at'    => Carbon::parse('2026-08-05 12:00:00'),
                'created_at' => Carbon::parse('2026-08-05 11:30:00'),
            ];
        }

        if ($adminSolo) {
            $notifications[] = [
                'admin_id' => $adminSolo->id,
                'type'     => 'App\Notifications\CustomNotification',
                'data'     => [
                    'type'    => 'new_registration',
                    'message' => 'Pendaftaran mitra baru (Eko Saputra - Surakarta) telah diverifikasi.',
                ],
                'read_at'    => Carbon::parse('2026-08-07 10:00:00'),
                'created_at' => Carbon::parse('2026-08-07 09:00:00'),
            ];

            $notifications[] = [
                'admin_id' => $adminSolo->id,
                'type'     => 'App\Notifications\CustomNotification',
                'data'     => [
                    'type'    => 'help_completed',
                    'message' => 'Bantuan "Bantu Angkut & Penataan Etalase Toko di Banjarsari" telah selesai dikonfirmasi.',
                ],
                'read_at'    => Carbon::parse('2026-08-07 13:00:00'),
                'created_at' => Carbon::parse('2026-08-07 12:45:00'),
            ];
        }

        foreach ($notifications as $n) {
            DB::table('notifications')->insert([
                'id'              => Str::uuid(),
                'type'            => $n['type'],
                'notifiable_type' => User::class,
                'notifiable_id'   => $n['admin_id'],
                'data'            => json_encode($n['data']),
                'read_at'         => $n['read_at'],
                'created_at'      => $n['created_at'],
                'updated_at'      => $n['created_at'],
            ]);
        }

        $this->command->info('AdminNotificationSeeder berhasil membuat notifikasi untuk Admin Sleman & Admin Surakarta.');
    }
}
