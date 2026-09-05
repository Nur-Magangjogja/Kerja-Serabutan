<?php

namespace Database\Seeders;

use App\Models\PartnerOnlineState;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartnerOnlineStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menyiapkan status online mitra agar muncul aktif pada peta live tracking & daftar armada.
     */
    public function run(): void
    {
        $mitraUsers = User::where('role', 'mitra')->get();

        $coords = [
            'mitra.sleman1@sayabantu.com'   => ['lat' => -7.7712000, 'lng' => 110.3854000],
            'mitra.sleman2@sayabantu.com'   => ['lat' => -7.7610000, 'lng' => 110.3725000],
            'mitra@sayabantu.com'           => ['lat' => -7.7845000, 'lng' => 110.3341000],
            'mitra.jogja1@sayabantu.com'    => ['lat' => -7.7942000, 'lng' => 110.3689000],
            'mitra.surakarta1@sayabantu.com'=> ['lat' => -7.5645000, 'lng' => 110.8142000],
            'mitra.surakarta2@sayabantu.com'=> ['lat' => -7.5582000, 'lng' => 110.8521000],
            'mitra.sukoharjo1@sayabantu.com'=> ['lat' => -7.5521000, 'lng' => 110.7482000],
            'mitra.sukoharjo2@sayabantu.com'=> ['lat' => -7.5912000, 'lng' => 110.8123000],
        ];

        foreach ($mitraUsers as $mitra) {
            $coord = $coords[$mitra->email] ?? ['lat' => -7.7712000, 'lng' => 110.3854000];

            PartnerOnlineState::updateOrCreate(
                ['user_id' => $mitra->id],
                [
                    'matching_status'      => 'online',
                    'current_help_id'      => null,
                    'consecutive_declines' => 0,
                    'last_seen_at'         => now(),
                    'searching_since'      => now()->subHours(2),
                    'last_completed_at'    => now()->subDays(1),
                    'latitude'             => $coord['lat'],
                    'longitude'            => $coord['lng'],
                ]
            );
        }

        $this->command->info('PartnerOnlineStateSeeder berhasil menyiapkan status online untuk seluruh mitra di 4 wilayah.');
    }
}
