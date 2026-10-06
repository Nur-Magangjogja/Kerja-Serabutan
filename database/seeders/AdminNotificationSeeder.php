<?php

namespace Database\Seeders;

use App\Models\Help;
use App\Models\Registration;
use App\Models\User;
use App\Services\Territory\AdminTerritoryAuthorizationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class AdminNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi notifikasi in-app untuk Admin Wilayah yang terotentikasi dan terotori sesuai wewenang wilayahnya (Idempoten).
     */
    public function run(): void
    {
        $admins = User::where('role', 'admin')->get();
        $authService = app(AdminTerritoryAuthorizationService::class);
        $now = now();

        $calonMitra = Registration::where('email', 'calon.mitra@sayabantu.com')->first();
        $mitraUtama = User::where('email', 'mitra@sayabantu.com')->first();
        $disputeHelp = Help::where('status', 'disputed')->first();

        foreach ($admins as $admin) {
            // 1. ACCOUNT NOTIFICATION: Verifikasi KTP Pendaftaran Mitra Baru
            // Teritorial: Profile Territory Registrant (Sleman / Ngaglik)
            if ($calonMitra && $authService->canAccessTerritory($admin, (int) $calonMitra->district_id, (int) $calonMitra->city_id)) {
                $notifId1 = Uuid::uuid5(Uuid::NAMESPACE_DNS, "admin_notif_reg_{$admin->id}_{$calonMitra->id}")->toString();
                DB::table('notifications')->updateOrInsert(
                    ['id' => $notifId1],
                    [
                        'type'            => 'App\\Notifications\\AdminNotification',
                        'notifiable_type' => User::class,
                        'notifiable_id'   => $admin->id,
                        'data'            => json_encode([
                            'title'      => 'Pendaftaran Mitra Baru Perlu Verifikasi',
                            'message'    => 'Terdapat 1 pendaftar baru (' . $calonMitra->full_name . ') yang menunggu verifikasi berkas KTP di wilayah kerja Anda.',
                            'url'        => '/admin/verifications',
                            'created_at' => $now->copy()->subHours(2)->toISOString(),
                        ]),
                        'read_at'         => null,
                        'created_at'      => $now->copy()->subHours(2),
                        'updated_at'      => $now->copy()->subHours(2),
                    ]
                );
            }

            // 2. ACCOUNT NOTIFICATION: Pengajuan Penarikan Dana (Withdraw)
            // Teritorial: Profile Territory Mitra (Sleman / Ngaglik)
            if ($mitraUtama && $authService->canAccessTerritory($admin, (int) $mitraUtama->district_id, (int) $mitraUtama->city_id)) {
                $notifId2 = Uuid::uuid5(Uuid::NAMESPACE_DNS, "admin_notif_wd_{$admin->id}_{$mitraUtama->id}")->toString();
                DB::table('notifications')->updateOrInsert(
                    ['id' => $notifId2],
                    [
                        'type'            => 'App\\Notifications\\AdminNotification',
                        'notifiable_type' => User::class,
                        'notifiable_id'   => $admin->id,
                        'data'            => json_encode([
                            'title'      => 'Pengajuan Penarikan Dana Baru',
                            'message'    => 'Mitra ' . $mitraUtama->name . ' mengajukan penarikan dana sebesar Rp 100.000.',
                            'url'        => '/admin/finances/withdrawals',
                            'created_at' => $now->copy()->subHours(1)->toISOString(),
                        ]),
                        'read_at'         => null,
                        'created_at'      => $now->copy()->subHours(1),
                        'updated_at'      => $now->copy()->subHours(1),
                    ]
                );
            }

            // 3. JOB / CASE NOTIFICATION: Sengketa Tugas Bantuan Baru
            // Teritorial: Help Territory (Sleman / Ngaglik)
            if ($disputeHelp && $authService->canAccessHelp($admin, $disputeHelp)) {
                $notifId3 = Uuid::uuid5(Uuid::NAMESPACE_DNS, "admin_notif_dispute_{$admin->id}_{$disputeHelp->id}")->toString();
                DB::table('notifications')->updateOrInsert(
                    ['id' => $notifId3],
                    [
                        'type'            => 'App\\Notifications\\AdminNotification',
                        'notifiable_type' => User::class,
                        'notifiable_id'   => $admin->id,
                        'data'            => json_encode([
                            'help_id'    => $disputeHelp->id,
                            'title'      => 'Sengketa Pesanan Baru Perlu Ditinjau',
                            'message'    => 'Pesanan ' . $disputeHelp->order_id . ' (' . $disputeHelp->title . ') dilaporkan memiliki sengketa dan memerlukan mediasi.',
                            'url'        => '/admin/disputes',
                            'created_at' => $now->copy()->subHours(3)->toISOString(),
                        ]),
                        'read_at'         => null,
                        'created_at'      => $now->copy()->subHours(3),
                        'updated_at'      => $now->copy()->subHours(3),
                    ]
                );
            }
        }

        $this->command?->info('✓ AdminNotificationSeeder berhasil mengisi notifikasi admin berbasis wewenang wilayah secara idempoten.');
    }
}
