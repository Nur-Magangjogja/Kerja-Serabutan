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
        $adminSleman    = User::where('email', 'admin.sleman@sayabantu.com')->first();
        $adminSurakarta = User::where('email', 'admin.surakarta@sayabantu.com')->first();
        
        $notifications = [];

        if ($adminSleman) {
            $notifications[] = [
                'admin_id' => $adminSleman->id,
                'type'     => 'App\Notifications\CustomNotification',
                'data'     => [
                    'type'    => 'new_registration',
                    'message' => 'Pendaftaran mitra baru (Agus Prasetyo - Sleman) telah diverifikasi.',
                ],
                'read_at'    => Carbon::parse('2026-08-04 10:00:00'),
                'created_at' => Carbon::parse('2026-08-04 09:00:00'),
            ];

            $notifications[] = [
                'admin_id' => $adminSleman->id,
                'type'     => 'App\Notifications\CustomNotification',
                'data'     => [
                    'type'    => 'new_registration',
                    'message' => 'Pendaftaran mitra baru (Danang Saputra - Kota Yogyakarta) telah diverifikasi.',
                ],
                'read_at'    => Carbon::parse('2026-08-05 10:00:00'),
                'created_at' => Carbon::parse('2026-08-05 09:00:00'),
            ];

            $notifications[] = [
                'admin_id' => $adminSleman->id,
                'type'     => 'App\Notifications\CustomNotification',
                'data'     => [
                    'type'    => 'help_completed',
                    'message' => 'Bantuan "Bantu Pindahan & Angkat Kasur Busa Gejayan" telah selesai dikonfirmasi.',
                ],
                'read_at'    => Carbon::parse('2026-08-04 12:00:00'),
                'created_at' => Carbon::parse('2026-08-04 11:30:00'),
            ];
        }

        if ($adminSurakarta) {
            $notifications[] = [
                'admin_id' => $adminSurakarta->id,
                'type'     => 'App\Notifications\CustomNotification',
                'data'     => [
                    'type'    => 'new_registration',
                    'message' => 'Pendaftaran mitra baru (Eko Saputra - Surakarta) telah diverifikasi.',
                ],
                'read_at'    => Carbon::parse('2026-08-04 10:00:00'),
                'created_at' => Carbon::parse('2026-08-04 09:00:00'),
            ];

            $notifications[] = [
                'admin_id' => $adminSurakarta->id,
                'type'     => 'App\Notifications\CustomNotification',
                'data'     => [
                    'type'    => 'new_registration',
                    'message' => 'Pendaftaran mitra baru (Tri Wahyudi - Sukoharjo) telah diverifikasi.',
                ],
                'read_at'    => Carbon::parse('2026-08-05 10:00:00'),
                'created_at' => Carbon::parse('2026-08-05 09:00:00'),
            ];

            $notifications[] = [
                'admin_id' => $adminSurakarta->id,
                'type'     => 'App\Notifications\CustomNotification',
                'data'     => [
                    'type'    => 'help_completed',
                    'message' => 'Bantuan "Pemasangan Lampu Gantung Hias Ruang Tamu" telah selesai dikonfirmasi.',
                ],
                'read_at'    => Carbon::parse('2026-08-04 16:00:00'),
                'created_at' => Carbon::parse('2026-08-04 15:30:00'),
            ];
        }

        foreach ($notifications as $notif) {
            DB::table('notifications')->updateOrInsert(
                [
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id'   => $notif['admin_id'],
                    'data'            => json_encode($notif['data']),
                ],
                [
                    'id'         => (string) Str::uuid(),
                    'type'       => $notif['type'],
                    'read_at'    => $notif['read_at'],
                    'created_at' => $notif['created_at'],
                    'updated_at' => $notif['created_at'],
                ]
            );
        }

        $this->command->info('AdminNotificationSeeder berhasil membuat notifikasi untuk Admin Sleman & Admin Surakarta.');
    }
}
