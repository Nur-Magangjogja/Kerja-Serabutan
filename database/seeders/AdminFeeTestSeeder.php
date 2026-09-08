<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Help;
use App\Models\BalanceTransaction;
use App\Models\User;
use Carbon\Carbon;

class AdminFeeTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get test users
        $customer = User::where('email', 'customer@sayabantu.com')->first()
            ?? User::where('role', 'customer')->first();
        $mitra = User::where('email', 'mitra@sayabantu.com')->first()
            ?? User::where('role', 'mitra')->first();

        if (!$customer || !$mitra) {
            $this->command->error('Test users not found. Please run UserSeeder first.');
            return;
        }

        // Create some completed helps with admin fees
        $helps = [
            [
                'order_id'  => 'HELP-TEST-FEE01',
                'amount'    => 100000,
                'admin_fee' => 10000, // 10% admin fee
                'title'     => 'Bantuan Perbaikan Atap',
                'days_ago'  => 5,
            ],
            [
                'order_id'  => 'HELP-TEST-FEE02',
                'amount'    => 200000,
                'admin_fee' => 20000, // 10% admin fee
                'title'     => 'Bantuan Renovasi Dapur',
                'days_ago'  => 10,
            ],
            [
                'order_id'  => 'HELP-TEST-FEE03',
                'amount'    => 50000,
                'admin_fee' => 5000, // 10% admin fee
                'title'     => 'Bantuan Cat Rumah',
                'days_ago'  => 15,
            ],
        ];

        foreach ($helps as $helpData) {
            $createdAt = Carbon::now()->subDays($helpData['days_ago']);
            $completedAt = Carbon::now()->subDays($helpData['days_ago'] - 1);

            Help::updateOrCreate(
                ['order_id' => $helpData['order_id']],
                [
                    'user_id'                  => $customer->id,
                    'mitra_id'                 => $mitra->id,
                    'city_id'                  => $customer->city_id,
                    'district_id'              => $customer->district_id,
                    'title'                    => $helpData['title'],
                    'description'              => 'Test bantuan dengan admin fee untuk dashboard',
                    'location'                 => $customer->address ?? 'Yogyakarta',
                    'full_address'             => $customer->address ?? 'Yogyakarta',
                    'latitude'                 => -7.7712,
                    'longitude'                => 110.3854,
                    'service_type'             => 'daily_chores',
                    'order_mode'               => 'manual',
                    'amount'                   => $helpData['amount'],
                    'service_fee'              => $helpData['amount'],
                    'travel_fee'               => 0,
                    'item_fund'                => 0,
                    'item_fund_mode'           => null,
                    'minimum_order_value'      => $helpData['amount'],
                    'total_amount'             => $helpData['amount'] + $helpData['admin_fee'],
                    'platform_fee_amount'      => $helpData['admin_fee'],
                    'admin_fee'                => $helpData['admin_fee'],
                    'mitra_earning'            => $helpData['amount'],
                    'matching_distance_km'     => 1.5,
                    'travel_distance_km'       => 1.5,
                    'service_route_distance_km'=> 0,
                    'route_source'             => 'fallback_estimation',
                    'gps_accuracy'             => 10.0,
                    'status'                   => Help::STATUS_SELESAI,
                    'escrow_status'            => Help::ESCROW_STATUS_RELEASED,
                    'payment_status'           => Help::PAYMENT_STATUS_PAID,
                    'rating_status'            => Help::RATING_STATUS_RATED,
                    'dispatch_mode'            => 'assigned',
                    'model_version'            => 3,
                    'completed_at'             => $completedAt,
                    'created_at'               => $createdAt,
                    'updated_at'               => $completedAt,
                ]
            );
        }

        // Create some completed top-up transactions with admin fees
        $topups = [
            [
                'order_id'  => 'TOPUP-TEST-FEE01',
                'amount'    => 50000,
                'admin_fee' => 7500, // Tier 2 fee
                'days_ago'  => 3,
            ],
            [
                'order_id'  => 'TOPUP-TEST-FEE02',
                'amount'    => 100000,
                'admin_fee' => 7500, // Tier 2 fee
                'days_ago'  => 7,
            ],
            [
                'order_id'  => 'TOPUP-TEST-FEE03',
                'amount'    => 200000,
                'admin_fee' => 6000, // 3% of 200000
                'days_ago'  => 12,
            ],
        ];

        foreach ($topups as $topupData) {
            $txDate = Carbon::now()->subDays($topupData['days_ago']);
            BalanceTransaction::updateOrCreate(
                [
                    'user_id'  => $customer->id,
                    'order_id' => $topupData['order_id'],
                ],
                [
                    'amount'         => $topupData['amount'],
                    'direction'      => 'credit',
                    'admin_fee'      => $topupData['admin_fee'],
                    'total_payment'  => $topupData['amount'] + $topupData['admin_fee'],
                    'type'           => 'topup',
                    'description'    => 'Top-up saldo test dengan admin fee',
                    'status'         => 'completed',
                    'payment_method' => 'bank_bca',
                    'processed_at'   => $txDate,
                    'created_at'     => $txDate,
                    'updated_at'     => $txDate,
                ]
            );
        }

        $this->command->info('Admin fee test data created successfully!');
        $this->command->info('Total help admin fees: Rp ' . number_format(array_sum(array_column($helps, 'admin_fee')), 0, ',', '.'));
        $this->command->info('Total topup admin fees: Rp ' . number_format(array_sum(array_column($topups, 'admin_fee')), 0, ',', '.'));
        $this->command->info('Grand total admin fees: Rp ' . number_format(
            array_sum(array_column($helps, 'admin_fee')) + array_sum(array_column($topups, 'admin_fee')),
            0,
            ',',
            '.'
        ));
    }
}
