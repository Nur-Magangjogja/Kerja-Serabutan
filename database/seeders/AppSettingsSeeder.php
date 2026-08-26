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
            'app_name'                  => 'SayaBantu',
            'app_tagline'               => 'Platform Layanan & Bantuan Serabutan Terpercaya',
            'platform_commission_rate'  => '10.00', // 10% komisi platform
            'mitra_cancel_penalty_fee'  => '5000',  // Rp 5.000 denda pembatalan mitra
            'min_withdraw_amount'       => '50000', // Rp 50.000 minimal withdraw
            'topup_admin_fee'           => '0',      // Bebas biaya admin / 0% pajak topup
            'topup_qris_image'          => 'images/payment/qris.png',
            'topup_qris_merchant_name'  => 'PT SayaBantu',
            'topup_qris_nmid'           => 'ID1020030040050',
            'topup_qris_enabled'        => '1',
            'contact_email'             => 'support@sayabantu.com',
            'contact_phone'             => '081234567890',
            'default_city'              => 'Sleman',
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
