<?php

namespace Database\Seeders;

use App\Models\Help;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi notifikasi in-app untuk Customer dan Mitra dengan timestamp yang
     * sinkron terhadap tanggal KYC dan riwayat transaksi masing-masing user.
     */
    public function run(): void
    {
        // Peta tanggal KYC tiap user (sinkron dengan ActivityLogsSeeder)
        $kycDates = [
            'customer.sleman1@sayabantu.com'    => '2026-08-03 09:00:00',
            'customer.sleman2@sayabantu.com'    => '2026-08-04 08:30:00',
            'customer@sayabantu.com'            => '2026-08-02 09:00:00',
            'customer.sleman3@sayabantu.com'    => '2026-08-05 08:00:00',
            'customer.jogja1@sayabantu.com'     => '2026-08-04 10:00:00',
            'customer.jogja2@sayabantu.com'     => '2026-08-06 09:00:00',
            'customer.surakarta1@sayabantu.com' => '2026-08-04 09:30:00',
            'customer.surakarta2@sayabantu.com' => '2026-08-05 08:30:00',
            'customer.sukoharjo1@sayabantu.com' => '2026-08-04 10:00:00',
            'customer.sukoharjo2@sayabantu.com' => '2026-08-05 09:00:00',
            'customer.semarang1@sayabantu.com'  => '2026-08-12 09:00:00',
            'customer.jaksel1@sayabantu.com'    => '2026-08-07 10:00:00',
            'customer.jaksel2@sayabantu.com'    => '2026-08-08 09:30:00',
            'customer.jaksel3@sayabantu.com'    => '2026-08-09 10:00:00',
            'customer.jakbar1@sayabantu.com'    => '2026-08-09 11:00:00',
            'customer.jaktim1@sayabantu.com'    => '2026-08-10 09:00:00',
            'customer.bandung1@sayabantu.com'   => '2026-08-08 10:00:00',
            'customer.bandung2@sayabantu.com'   => '2026-08-11 09:30:00',
            'customer.surabaya1@sayabantu.com'  => '2026-08-12 10:00:00',
            'customer.surabaya2@sayabantu.com'  => '2026-08-14 09:00:00',
            'customer.malang1@sayabantu.com'    => '2026-08-13 09:30:00',
            'customer.denpasar1@sayabantu.com'  => '2026-08-17 10:00:00',
            'customer.denpasar2@sayabantu.com'  => '2026-08-18 09:00:00',
            'customer.medan1@sayabantu.com'     => '2026-08-19 09:30:00',
            'customer.medan2@sayabantu.com'     => '2026-08-20 10:00:00',
            'customer.palembang1@sayabantu.com' => '2026-08-21 09:00:00',
            'customer.makassar1@sayabantu.com'  => '2026-08-22 10:00:00',
            'customer.makassar2@sayabantu.com'  => '2026-08-24 09:30:00',
            'customer.tangsel1@sayabantu.com'   => '2026-08-25 09:00:00',
            'mitra.sleman1@sayabantu.com'       => '2026-08-03 10:00:00',
            'mitra.sleman2@sayabantu.com'       => '2026-08-04 10:30:00',
            'mitra.sleman3@sayabantu.com'       => '2026-08-05 09:00:00',
            'mitra@sayabantu.com'               => '2026-08-02 10:00:00',
            'mitra.jogja1@sayabantu.com'        => '2026-08-04 11:00:00',
            'mitra.jogja2@sayabantu.com'        => '2026-08-05 10:00:00',
            'mitra.jogja3@sayabantu.com'        => '2026-08-06 09:30:00',
            'mitra.surakarta1@sayabantu.com'    => '2026-08-03 09:30:00',
            'mitra.surakarta2@sayabantu.com'    => '2026-08-04 09:00:00',
            'mitra.sukoharjo1@sayabantu.com'    => '2026-08-04 10:00:00',
            'mitra.sukoharjo2@sayabantu.com'    => '2026-08-05 09:30:00',
            'mitra.semarang1@sayabantu.com'     => '2026-08-12 10:30:00',
            'mitra.semarang2@sayabantu.com'     => '2026-08-13 10:00:00',
            'mitra.jaksel1@sayabantu.com'       => '2026-08-07 09:30:00',
            'mitra.jaksel2@sayabantu.com'       => '2026-08-08 10:00:00',
            'mitra.jaksel3@sayabantu.com'       => '2026-08-09 10:30:00',
            'mitra.jakbar1@sayabantu.com'       => '2026-08-09 11:30:00',
            'mitra.jaktim1@sayabantu.com'       => '2026-08-10 10:00:00',
            'mitra.bandung1@sayabantu.com'      => '2026-08-08 10:30:00',
            'mitra.bandung2@sayabantu.com'      => '2026-08-11 10:00:00',
            'mitra.bandung3@sayabantu.com'      => '2026-08-12 09:30:00',
            'mitra.surabaya1@sayabantu.com'     => '2026-08-12 11:00:00',
            'mitra.surabaya2@sayabantu.com'     => '2026-08-14 10:00:00',
            'mitra.surabaya3@sayabantu.com'     => '2026-08-15 09:30:00',
            'mitra.malang1@sayabantu.com'       => '2026-08-13 10:30:00',
            'mitra.denpasar1@sayabantu.com'     => '2026-08-17 11:00:00',
            'mitra.denpasar2@sayabantu.com'     => '2026-08-18 10:00:00',
            'mitra.medan1@sayabantu.com'        => '2026-08-19 10:30:00',
            'mitra.medan2@sayabantu.com'        => '2026-08-20 11:00:00',
            'mitra.palembang1@sayabantu.com'    => '2026-08-21 10:00:00',
            'mitra.makassar1@sayabantu.com'     => '2026-08-22 11:00:00',
            'mitra.makassar2@sayabantu.com'     => '2026-08-24 10:30:00',
            'mitra.tangsel1@sayabantu.com'      => '2026-08-25 10:00:00',
        ];

        $users = User::whereIn('role', ['customer', 'mitra'])->get();

        foreach ($users as $user) {
            $isMitra = $user->role === 'mitra';

            // Tentukan tanggal KYC user ini sebagai anchor timestamp
            $kycDateStr  = $kycDates[$user->email] ?? '2026-08-04 09:00:00';
            $kycDate     = Carbon::parse($kycDateStr);

            // Cari tanggal penyelesaian help terakhir user ini (untuk timestamp notif earning/selesai)
            $latestHelp = $isMitra
                ? Help::where('mitra_id', $user->id)->where('status', 'selesai')->orderByDesc('completed_at')->first()
                : Help::where('user_id', $user->id)->where('status', 'selesai')->orderByDesc('completed_at')->first();

            $helpCompletedAt = $latestHelp?->completed_at ?? $kycDate->copy()->addDays(14);

            $notifs = [
                // Notif 1: Verifikasi akun berhasil (KYC date + 30 menit setelah verif)
                [
                    'type'    => 'App\Notifications\CustomNotification',
                    'data'    => [
                        'type'    => 'verification_success',
                        'title'   => 'Verifikasi Akun Berhasil',
                        'message' => 'Selamat, akun identitas KTP Anda telah berhasil diverifikasi oleh Admin.',
                    ],
                    'read_at' => $kycDate->copy()->addHours(1),
                    'date'    => $kycDate->copy()->addMinutes(30),
                ],
                // Notif 2: Top-up/Earning (5 hari setelah KYC)
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
                    'read_at' => $kycDate->copy()->addDays(5)->addHour(),
                    'date'    => $kycDate->copy()->addDays(5),
                ],
                // Notif 3: Bantuan selesai (tanggal penyelesaian help terakhir + 5 menit)
                [
                    'type'    => 'App\Notifications\CustomNotification',
                    'data'    => [
                        'type'    => 'help_completed',
                        'title'   => 'Bantuan Selesai',
                        'message' => 'Tugas bantuan telah selesai dikonfirmasi dan rating 5 bintang telah diberikan.',
                    ],
                    'read_at' => null, // belum dibaca (notif terbaru)
                    'date'    => $helpCompletedAt->copy()->addMinutes(5),
                ],
            ];

            foreach ($notifs as $n) {
                DB::table('notifications')->updateOrInsert(
                    [
                        'notifiable_type' => User::class,
                        'notifiable_id'   => $user->id,
                        'data'            => json_encode($n['data']),
                    ],
                    [
                        'id'              => (string) Str::uuid(),
                        'type'            => $n['type'],
                        'read_at'         => $n['read_at'],
                        'created_at'      => $n['date'],
                        'updated_at'      => $n['date'],
                    ]
                );
            }
        }

        $this->command->info('NotificationSeeder berhasil membuat notifikasi in-app dengan timestamp sinkron untuk seluruh Mitra & Customer.');
    }
}
