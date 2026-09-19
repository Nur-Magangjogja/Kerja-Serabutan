<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'app_name'                                        => 'SayaBantu',
            'app_tagline'                                     => 'Platform Layanan & Bantuan Serabutan Terpercaya',
            \App\Support\Settings\AppSettingKey::MIN_HELP_NOMINAL       => '10000', // Rp 10.000 minimal bantuan
            \App\Support\Settings\AppSettingKey::PLATFORM_SERVICE_FEE   => '2000',  // Rp 2.000 biaya layanan tetap platform
            \App\Support\Settings\AppSettingKey::HELP_AUTO_CANCEL_HOURS => '24',    // 24 jam batas auto cancel
            'min_withdraw_amount'                             => '50000', // Rp 50.000 minimal withdraw
            'topup_admin_fee'                                 => '0',      // Bebas biaya admin / 0% pajak topup
            'topup_qris_image'                                => 'images/payment/qris.png',
            'topup_qris_merchant_name'                        => 'PT SayaBantu',
            'topup_qris_nmid'                                 => 'ID1020030040050',
            'topup_qris_enabled'                              => '1',
            'contact_email'                                   => 'support@sayabantu.com',
            'contact_phone'                                   => '081234567890',
            'default_city'                                    => 'Sleman',
            // ─── REVISI 3 CONFIGS ────────────────────────────────────────────
            'travel_fee_per_km'                               => '2500', // Rp 2.500 per km setelah free radius
            \App\Support\Settings\AppSettingKey::TRAVEL_FREE_RADIUS_KM  => '2.0',  // 0-2 KM free travel fee baseline
            'max_matching_distance_km'                        => '10.0', // Batas maksimum matching mitra -> titik awal
            \App\Support\Settings\AppSettingKey::ARRIVAL_RADIUS_METERS  => '50',   // Radius tiba di lokasi (50m)
            'arrival_acceptable_accuracy'                     => '50',   // Akurasi GPS maksimal yang diizinkan untuk tiba
            \App\Support\Settings\AppSettingKey::MOVEMENT_MIN_METERS    => '30',   // Jarak minimum pergerakan tercatat
            'drift_max_velocity_kmh'                          => '120',  // Batas kecepatan anti-drift (km/jam)
            'adjacent_district_match_enabled'                 => '1',    // Ekspansi matching ke kecamatan tetangga aktif
        ];

        foreach ($settings as $key => $value) {
            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        $this->command->info('AppSettingsSeeder completed successfully.');
    }
}
