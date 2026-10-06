<?php

namespace Database\Seeders;

use App\Models\Help;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi notifikasi in-app untuk Customer dan Mitra dengan integritas referensial dan idempoten.
     */
    public function run(): void
    {
        $now = now();
        $customer = User::where('email', 'customer@sayabantu.com')->first();
        $mitra = User::where('email', 'mitra@sayabantu.com')->first();

        // 1. Notifikasi Customer
        if ($customer) {
            // Notifikasi A: Akun Terverifikasi (Account / Profile Level - Tanpa Help ID)
            $notifCust1Id = Uuid::uuid5(Uuid::NAMESPACE_DNS, "cust_notif_acc_verified_{$customer->id}")->toString();
            DB::table('notifications')->updateOrInsert(
                ['id' => $notifCust1Id],
                [
                    'type'            => 'App\\Notifications\\HelpStatusNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $customer->id,
                    'data'            => json_encode([
                        'title'      => 'Akun Anda Telah Diverifikasi',
                        'message'    => 'Selamat! Berkas identitas Anda telah berhasil diverifikasi oleh Admin.',
                        'url'        => '/customer/dashboard',
                        'created_at' => $now->copy()->subDays(3)->toISOString(),
                    ]),
                    'read_at'         => $now->copy()->subDays(2),
                    'created_at'      => $now->copy()->subDays(3),
                    'updated_at'      => $now->copy()->subDays(3),
                ]
            );

            // Notifikasi B: Mitra Mengambil Pesanan (Job Level - Real Existing Customer Help)
            $customerHelp = Help::where('user_id', $customer->id)->whereNotNull('mitra_id')->first()
                ?? Help::where('user_id', $customer->id)->first();

            if ($customerHelp) {
                $notifCust2Id = Uuid::uuid5(Uuid::NAMESPACE_DNS, "cust_notif_help_taken_{$customer->id}_{$customerHelp->id}")->toString();
                $mitraName = $customerHelp->mitra?->name ?? 'Budi Santoso';
                DB::table('notifications')->updateOrInsert(
                    ['id' => $notifCust2Id],
                    [
                        'type'            => 'App\\Notifications\\HelpStatusNotification',
                        'notifiable_type' => User::class,
                        'notifiable_id'   => $customer->id,
                        'data'            => json_encode([
                            'help_id'    => $customerHelp->id,
                            'title'      => 'Mitra Mengambil Pesanan Anda',
                            'message'    => "Rekan Jasa {$mitraName} telah mengambil pesanan bantuan '{$customerHelp->title}'.",
                            'url'        => '/customer/helps/' . $customerHelp->id,
                            'created_at' => $now->copy()->subHours(1)->toISOString(),
                        ]),
                        'read_at'         => null,
                        'created_at'      => $now->copy()->subHours(1),
                        'updated_at'      => $now->copy()->subHours(1),
                    ]
                );
            }
        }

        // 2. Notifikasi Mitra
        if ($mitra) {
            // Notifikasi A: Pendaftaran Mitra Disetujui (Account / Profile Level - Tanpa Help ID)
            $notifMitra1Id = Uuid::uuid5(Uuid::NAMESPACE_DNS, "mitra_notif_approved_{$mitra->id}")->toString();
            DB::table('notifications')->updateOrInsert(
                ['id' => $notifMitra1Id],
                [
                    'type'            => 'App\\Notifications\\HelpStatusNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id'   => $mitra->id,
                    'data'            => json_encode([
                        'title'      => 'Pendaftaran Mitra Disetujui',
                        'message'    => 'Selamat! Anda kini resmi menjadi Mitra SayaBantu. Siap menerima tawaran bantuan.',
                        'url'        => '/mitra/dashboard',
                        'created_at' => $now->copy()->subDays(3)->toISOString(),
                    ]),
                    'read_at'         => $now->copy()->subDays(2),
                    'created_at'      => $now->copy()->subDays(3),
                    'updated_at'      => $now->copy()->subDays(3),
                ]
            );

            // Notifikasi B: Penugasan Pesanan Bantuan (Job Level - Real Existing Assigned Help)
            $mitraHelp = Help::where('mitra_id', $mitra->id)->first();
            if ($mitraHelp) {
                $notifMitra2Id = Uuid::uuid5(Uuid::NAMESPACE_DNS, "mitra_notif_help_assigned_{$mitra->id}_{$mitraHelp->id}")->toString();
                DB::table('notifications')->updateOrInsert(
                    ['id' => $notifMitra2Id],
                    [
                        'type'            => 'App\\Notifications\\HelpStatusNotification',
                        'notifiable_type' => User::class,
                        'notifiable_id'   => $mitra->id,
                        'data'            => json_encode([
                            'help_id'    => $mitraHelp->id,
                            'title'      => 'Pesanan Bantuan Diterima',
                            'message'    => "Anda telah menerima tugas pesanan '{$mitraHelp->title}'.",
                            'url'        => '/mitra/helps/' . $mitraHelp->id,
                            'created_at' => $now->copy()->subMinutes(30)->toISOString(),
                        ]),
                        'read_at'         => null,
                        'created_at'      => $now->copy()->subMinutes(30),
                        'updated_at'      => $now->copy()->subMinutes(30),
                    ]
                );
            }
        }

        $this->command?->info('✓ NotificationSeeder berhasil mengisi sampel notifikasi customer & mitra secara referensial dan idempoten.');
    }
}
