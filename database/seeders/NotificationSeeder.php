<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi notifikasi in-app untuk Customer dan Mitra.
     */
    public function run(): void
    {
        $users = User::whereIn('role', ['customer', 'mitra'])->get();

        foreach ($users as $user) {
            $isMitra = $user->role === 'mitra';

            $notifs = [
                [
                    'type'    => 'App\Notifications\CustomNotification',
                    'data'    => [
                        'type'    => 'verification_success',
                        'title'   => 'Verifikasi Akun Berhasil',
                        'message' => 'Selamat, akun identitas KTP Anda telah berhasil diverifikasi oleh Admin.',
                    ],
                    'read_at' => Carbon::parse('2026-08-04 10:00:00'),
                    'date'    => Carbon::parse('2026-08-04 09:30:00'),
                ],
                [
                    'type'    => 'App\Notifications\CustomNotification',
                    'data'    => $isMitra ? [
                        'type'    => 'earning_received',
                        'title'   => 'Pendapatan Diterima',
                        'message' => 'Pembayaran hasil kerja bantuan telah berhasil masuk ke saldo dompet Anda.',
                    ] : [
                        'type'    => 'topup_success',
                        'title'   => 'Top-Up Saldo Berhasil',
                        'message' => 'Top-up saldo dompet Anda telah berhasil diproses.',
                    ],
                    'read_at' => Carbon::parse('2026-08-10 12:00:00'),
                    'date'    => Carbon::parse('2026-08-10 11:30:00'),
                ],
                [
                    'type'    => 'App\Notifications\CustomNotification',
                    'data'    => [
                        'type'    => 'help_completed',
                        'title'   => 'Bantuan Selesai',
                        'message' => 'Tugas bantuan telah selesai dikonfirmasi dan rating 5 bintang telah diberikan.',
                    ],
                    'read_at' => null,
                    'date'    => Carbon::parse('2026-09-01 11:00:00'),
                ],
            ];

            foreach ($notifs as $n) {
                DB::table('notifications')->insert([
                    'id'              => (string) Str::uuid(),
                    'type'            => $n['type'],
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $user->id,
                    'data'            => json_encode($n['data']),
                    'read_at'         => $n['read_at'],
                    'created_at'      => $n['date'],
                    'updated_at'      => $n['date'],
                ]);
            }
        }

        $this->command->info('NotificationSeeder berhasil membuat notifikasi in-app untuk seluruh Mitra & Customer.');
    }
}
