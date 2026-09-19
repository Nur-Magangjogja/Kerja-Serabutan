<?php

namespace Database\Seeders;

use App\Models\BalanceTransaction;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\WithdrawRequest;
use Illuminate\Database\Seeder;

class UserBalancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi saldo awal dan riwayat transaksi untuk akun pengujian secara dinamis dan rapi.
     */
    public function run(): void
    {
        $now = now();

        // 1. Inisialisasi Saldo Customer
        $customers = User::where('role', 'customer')->get();
        foreach ($customers as $customer) {
            UserBalance::updateOrCreate(
                ['user_id' => $customer->id],
                ['balance' => 500000.00]
            );

            BalanceTransaction::firstOrCreate(
                [
                    'user_id'        => $customer->id,
                    'type'           => 'topup',
                    'idempotency_key' => 'topup_init_' . $customer->id,
                ],
                [
                    'amount'          => 500000.00,
                    'direction'       => 'credit',
                    'total_payment'   => 500000.00,
                    'payment_method'  => 'qris',
                    'status'          => 'settlement',
                    'description'     => 'Top Up Saldo Awal (QRIS Mandiri)',
                    'processed_at'    => $now->copy()->subDays(3),
                    'created_at'      => $now->copy()->subDays(3),
                    'updated_at'      => $now->copy()->subDays(3),
                ]
            );
        }

        // 2. Inisialisasi Saldo Mitra
        $mitras = User::where('role', 'mitra')->where('verified', true)->get();
        foreach ($mitras as $mitra) {
            UserBalance::updateOrCreate(
                ['user_id' => $mitra->id],
                ['balance' => 250000.00]
            );

            BalanceTransaction::firstOrCreate(
                [
                    'user_id'        => $mitra->id,
                    'type'           => 'earning',
                    'idempotency_key' => 'earning_init_' . $mitra->id,
                ],
                [
                    'amount'          => 250000.00,
                    'direction'       => 'credit',
                    'total_payment'   => 250000.00,
                    'status'          => 'completed',
                    'description'     => 'Pendapatan Jasa Selesai',
                    'processed_at'    => $now->copy()->subDays(2),
                    'created_at'      => $now->copy()->subDays(2),
                    'updated_at'      => $now->copy()->subDays(2),
                ]
            );
        }

        // 3. Buatkan Sampel Permintaan Withdraw untuk Mitra Utama
        $mitraUtama = User::where('email', 'mitra@sayabantu.com')->first();
        if ($mitraUtama) {
            WithdrawRequest::firstOrCreate(
                [
                    'user_id'     => $mitraUtama->id,
                    'account_number' => '1370012345678',
                ],
                [
                    'amount'       => 100000.00,
                    'admin_fee'    => 2500.00,
                    'net_amount'   => 97500.00,
                    'bank_code'    => 'MANDIRI',
                    'account_name' => 'Budi Santoso',
                    'status'       => 'pending',
                    'description'  => 'Penarikan Dana ke Rekening Mandiri',
                    'created_at'   => $now->copy()->subHours(2),
                ]
            );
        }

        $this->command->info('✓ UserBalancesSeeder berhasil menginisialisasi saldo & transaksi.');
    }
}