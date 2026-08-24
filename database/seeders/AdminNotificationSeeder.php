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
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@sayabantu.com')->first();
        $customer = User::where('email', 'customer@sayabantu.com')->first();
        
        if (!$admin) {
            $this->command->warn('Admin Sleman tidak ditemukan.');
            return;
        }

        $notifications = [
            [
                'type' => 'App\Notifications\CustomNotification',
                'data' => [
                    'type'          => 'new_help_created',
                    'customer_name' => $customer ? $customer->name : 'Rina Kusuma',
                    'customer_id'   => $customer ? $customer->id : 4,
                    'title'         => 'Bantu Pindahan & Angkat Barang Kos Dekat Kampus UNY/UGM',
                    'location'      => 'Kec. Depok, Sleman',
                    'message'       => 'Permintaan bantuan baru telah dibuat di wilayah Sleman.',
                ],
                'read_at' => null,
            ],
            [
                'type' => 'App\Notifications\CustomNotification',
                'data' => [
                    'type'    => 'new_registration',
                    'message' => 'Pendaftaran mitra baru (Agus Prasetyo - Sleman) telah diverifikasi.',
                ],
                'read_at' => now()->subHours(5),
            ],
            [
                'type' => 'App\Notifications\CustomNotification',
                'data' => [
                    'type'    => 'help_completed',
                    'message' => 'Bantuan "Antar Berkas Dokumen ke Pemda Sleman" telah selesai dikonfirmasi.',
                ],
                'read_at' => now()->subDays(1),
            ],
        ];

        foreach ($notifications as $n) {
            DB::table('notifications')->insert([
                'id'              => Str::uuid(),
                'type'            => $n['type'],
                'notifiable_type' => User::class,
                'notifiable_id'   => $admin->id,
                'data'            => json_encode($n['data']),
                'read_at'         => $n['read_at'],
                'created_at'      => now()->subMinutes(rand(10, 180)),
                'updated_at'      => now()->subMinutes(rand(10, 180)),
            ]);
        }

        $this->command->info('AdminNotificationSeeder berhasil membuat notifikasi untuk Admin Sleman.');
    }
}
