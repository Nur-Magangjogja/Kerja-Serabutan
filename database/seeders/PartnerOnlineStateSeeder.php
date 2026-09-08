<?php

namespace Database\Seeders;

use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartnerOnlineStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menyiapkan status online mitra agar muncul aktif pada peta live tracking & daftar armada
     * untuk seluruh 36 mitra di 15 wilayah se-Indonesia.
     */
    public function run(): void
    {
        $mitraUsers = User::where('role', 'mitra')->get();

        $coords = [
            // D.I. Yogyakarta: Sleman & Kota Yogyakarta
            'mitra.sleman1@sayabantu.com'   => ['lat' => -7.7712000, 'lng' => 110.3854000],
            'mitra.sleman2@sayabantu.com'   => ['lat' => -7.7610000, 'lng' => 110.3725000],
            'mitra.sleman3@sayabantu.com'   => ['lat' => -7.7285000, 'lng' => 110.3798000],
            'mitra@sayabantu.com'           => ['lat' => -7.7845000, 'lng' => 110.3341000],
            'mitra.jogja1@sayabantu.com'    => ['lat' => -7.7942000, 'lng' => 110.3689000],
            'mitra.jogja2@sayabantu.com'    => ['lat' => -7.7931000, 'lng' => 110.3742000],
            'mitra.jogja3@sayabantu.com'    => ['lat' => -7.8185000, 'lng' => 110.3912000],

            // Jawa Tengah: Surakarta, Sukoharjo & Semarang
            'mitra.surakarta1@sayabantu.com'=> ['lat' => -7.5645000, 'lng' => 110.8142000],
            'mitra.surakarta2@sayabantu.com'=> ['lat' => -7.5582000, 'lng' => 110.8521000],
            'mitra.sukoharjo1@sayabantu.com'=> ['lat' => -7.5521000, 'lng' => 110.7482000],
            'mitra.sukoharjo2@sayabantu.com'=> ['lat' => -7.5912000, 'lng' => 110.8123000],
            'mitra.semarang1@sayabantu.com' => ['lat' => -7.0542000, 'lng' => 110.4182000],
            'mitra.semarang2@sayabantu.com' => ['lat' => -7.0515000, 'lng' => 110.4421000],

            // DKI Jakarta: Jaksel, Jakbar, Jaktim
            'mitra.jaksel1@sayabantu.com'   => ['lat' => -6.2284000, 'lng' => 106.8456000],
            'mitra.jaksel2@sayabantu.com'   => ['lat' => -6.2341000, 'lng' => 106.8095000],
            'mitra.jaksel3@sayabantu.com'   => ['lat' => -6.2365000, 'lng' => 106.8488000],
            'mitra.jakbar1@sayabantu.com'   => ['lat' => -6.1912000, 'lng' => 106.7685000],
            'mitra.jaktim1@sayabantu.com'   => ['lat' => -6.2354000, 'lng' => 106.9125000],

            // Jawa Barat: Bandung
            'mitra.bandung1@sayabantu.com'  => ['lat' => -6.8795000, 'lng' => 107.6142000],
            'mitra.bandung2@sayabantu.com'  => ['lat' => -6.9032000, 'lng' => 107.6185000],
            'mitra.bandung3@sayabantu.com'  => ['lat' => -6.9184000, 'lng' => 107.6132000],

            // Jawa Timur: Surabaya & Malang
            'mitra.surabaya1@sayabantu.com' => ['lat' => -7.2912000, 'lng' => 112.7601000],
            'mitra.surabaya2@sayabantu.com' => ['lat' => -7.2754000, 'lng' => 112.7845000],
            'mitra.surabaya3@sayabantu.com' => ['lat' => -7.3185000, 'lng' => 112.7812000],
            'mitra.malang1@sayabantu.com'   => ['lat' => -7.9512000, 'lng' => 112.6145000],

            // Bali: Denpasar
            'mitra.denpasar1@sayabantu.com' => ['lat' => -8.6985000, 'lng' => 115.2612000],
            'mitra.denpasar2@sayabantu.com' => ['lat' => -8.6712000, 'lng' => 115.1985000],

            // Sumatera Utara: Medan
            'mitra.medan1@sayabantu.com'    => ['lat' => 3.5852000,  'lng' => 98.6894000],
            'mitra.medan2@sayabantu.com'    => ['lat' => 3.5714000,  'lng' => 98.6582000],

            // Sumatera Selatan: Palembang
            'mitra.palembang1@sayabantu.com'=> ['lat' => -2.9812000, 'lng' => 104.7354000],

            // Sulawesi Selatan: Makassar
            'mitra.makassar1@sayabantu.com' => ['lat' => -5.1582000, 'lng' => 119.4452000],
            'mitra.makassar2@sayabantu.com' => ['lat' => -5.1315000, 'lng' => 119.4895000],

            // Banten: Tangerang Selatan
            'mitra.tangsel1@sayabantu.com'  => ['lat' => -6.3185000, 'lng' => 106.6712000],
        ];

        foreach ($mitraUsers as $mitra) {
            $coord = $coords[$mitra->email] ?? ['lat' => -7.7712000, 'lng' => 110.3854000];

            // Cek apakah mitra saat ini sedang mengerjakan tugas aktif (live)
            $activeHelp = Help::where('mitra_id', $mitra->id)
                ->whereIn('status', [
                    Help::STATUS_TAKEN,
                    Help::STATUS_PARTNER_ON_THE_WAY,
                    Help::STATUS_PARTNER_ARRIVED,
                    Help::STATUS_IN_PROGRESS,
                    Help::STATUS_WAITING_CONFIRMATION,
                    Help::STATUS_PARTNER_CANCEL_REQUESTED,
                    'ongoing',
                    'diperjalanan',
                ])
                ->first();

            $status = $activeHelp ? 'busy' : 'online';
            $helpId = $activeHelp ? $activeHelp->id : null;

            PartnerOnlineState::updateOrCreate(
                ['user_id' => $mitra->id],
                [
                    'matching_status'      => $status,
                    'current_help_id'      => $helpId,
                    'consecutive_declines' => 0,
                    'last_seen_at'         => now(),
                    'searching_since'      => now()->subHours(2),
                    'last_completed_at'    => now()->subDays(1),
                    'latitude'             => $coord['lat'],
                    'longitude'            => $coord['lng'],
                ]
            );
        }

        $this->command->info('PartnerOnlineStateSeeder berhasil menyiapkan status online untuk seluruh 36 mitra se-Indonesia.');
    }
}

