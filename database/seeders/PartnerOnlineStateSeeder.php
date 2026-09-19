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
     * Menyiapkan status online mitra agar muncul aktif pada radar dan dashboard mitra.
     */
    public function run(): void
    {
        $mitraUsers = User::where('role', 'mitra')->where('verified', true)->get();

        foreach ($mitraUsers as $mitra) {
            $cityLat = $mitra->cityRelation?->latitude ?? -7.7155600;
            $cityLng = $mitra->cityRelation?->longitude ?? 110.3555600;

            $activeHelp = Help::where('mitra_id', $mitra->id)
                ->whereIn('status', [
                    'taken',
                    'partner_on_the_way',
                    'partner_arrived',
                    'in_progress',
                    'waiting_customer_confirmation',
                    'disputed',
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
                    'searching_since'      => now()->subHours(1),
                    'last_completed_at'    => now()->subDays(1),
                    'latitude'             => $cityLat,
                    'longitude'            => $cityLng,
                ]
            );
        }

        $this->command->info('✓ PartnerOnlineStateSeeder berhasil menyiapkan status online mitra.');
    }
}
