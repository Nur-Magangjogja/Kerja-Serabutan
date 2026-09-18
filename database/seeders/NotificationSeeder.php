<?php

namespace Database\Seeders;

use App\Models\Help;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi notifikasi in-app untuk Customer dan Mitra untuk kemudahan pengujian UI notifikasi.
     */
    public function run(): void
    {
        $now = now();
        $customer = User::where('email', 'customer@sayabantu.com')->first();
        $mitra = User::where('email', 'mitra@sayabantu.com')->first();
        $help = Help::first();

        if ($customer) {
            DB::table('notifications')->insert([
                [
                    'id'              => (string) Str::uuid(),
                    'type'            => 'App\\Notifications\\HelpStatusNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $customer->id,
                    'data'            => json_encode([
                        'help_id'    => $help?->id,
                        'title'      => 'Akun Anda Telah Diverifikasi',
                        'message'    => 'Selamat! Berkas identitas Anda telah berhasil diverifikasi oleh Admin.',
                        'url'        => '/customer/dashboard',
                        'created_at' => $now->copy()->subDays(3)->toISOString(),
                    ]),
                    'read_at'         => $now->copy()->subDays(2),
                    'created_at'      => $now->copy()->subDays(3),
                    'updated_at'      => $now->copy()->subDays(3),
                ],
                [
                    'id'              => (string) Str::uuid(),
                    'type'            => 'App\\Notifications\\HelpStatusNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $customer->id,
                    'data'            => json_encode([
                        'help_id'    => $help?->id,
                        'title'      => 'Mitra Mengambil Pesanan Anda',
                        'message'    => 'Rekan Jasa Budi Santoso telah mengambil pesanan bantuan Anda.',
                        'url'        => '/customer/helps/' . ($help?->id ?? 1),
                        'created_at' => $now->copy()->subHours(1)->toISOString(),
                    ]),
                    'read_at'         => null,
                    'created_at'      => $now->copy()->subHours(1),
                    'updated_at'      => $now->copy()->subHours(1),
                ],
            ]);
        }

        if ($mitra) {
            DB::table('notifications')->insert([
                [
                    'id'              => (string) Str::uuid(),
                    'type'            => 'App\\Notifications\\HelpStatusNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $mitra->id,
                    'data'            => json_encode([
                        'help_id'    => $help?->id,
                        'title'      => 'Pendaftaran Mitra Disetujui',
                        'message'    => 'Selamat! Anda kini resmi menjadi Mitra SayaBantu. Siap menerima tawaran bantuan.',
                        'url'        => '/mitra/dashboard',
                        'created_at' => $now->copy()->subDays(3)->toISOString(),
                    ]),
                    'read_at'         => $now->copy()->subDays(2),
                    'created_at'      => $now->copy()->subDays(3),
                    'updated_at'      => $now->copy()->subDays(3),
                ],
                [
                    'id'              => (string) Str::uuid(),
                    'type'            => 'App\\Notifications\\HelpStatusNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $mitra->id,
                    'data'            => json_encode([
                        'help_id'    => $help?->id,
                        'title'      => 'Tawaran Bantuan Baru di Sekitar Anda',
                        'message'    => 'Ada pesanan bantuan baru yang cocok dengan lokasi Anda.',
                        'url'        => '/mitra/dashboard',
                        'created_at' => $now->copy()->subMinutes(30)->toISOString(),
                    ]),
                    'read_at'         => null,
                    'created_at'      => $now->copy()->subMinutes(30),
                    'updated_at'      => $now->copy()->subMinutes(30),
                ],
            ]);
        }

        $this->command->info('✓ NotificationSeeder berhasil mengisi sampel notifikasi customer & mitra.');
    }
}
