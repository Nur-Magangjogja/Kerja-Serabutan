<?php

namespace Database\Seeders;

use App\Models\BalanceTransaction;
use App\Models\Help;
use App\Models\User;
use App\Models\UserBalance;
use App\Models\WithdrawRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserBalancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi transaksi riwayat Top-Up, Pembayaran Jasa/Escrow, Pendapatan Mitra, dan Penarikan Dana (Withdraw)
     * Terdistribusi lengkap untuk setiap hari dari rentang 4 Agustus 2026 s/d 3 September 2026 di seluruh wilayah.
     */
    public function run(): void
    {
        // Sleman Users
        $custSleman1  = User::where('email', 'customer.sleman1@sayabantu.com')->first();
        $custSleman2  = User::where('email', 'customer.sleman2@sayabantu.com')->first();
        $custSleman3  = User::where('email', 'customer@sayabantu.com')->first();
        $mitraSleman1 = User::where('email', 'mitra.sleman1@sayabantu.com')->first();
        $mitraSleman2 = User::where('email', 'mitra.sleman2@sayabantu.com')->first();
        $mitraSleman3 = User::where('email', 'mitra@sayabantu.com')->first();

        // Yogyakarta Users
        $custJogja1   = User::where('email', 'customer.jogja1@sayabantu.com')->first();
        $mitraJogja1  = User::where('email', 'mitra.jogja1@sayabantu.com')->first();

        // Surakarta Users
        $custSolo1    = User::where('email', 'customer.surakarta1@sayabantu.com')->first();
        $custSolo2    = User::where('email', 'customer.surakarta2@sayabantu.com')->first();
        $mitraSolo1   = User::where('email', 'mitra.surakarta1@sayabantu.com')->first();
        $mitraSolo2   = User::where('email', 'mitra.surakarta2@sayabantu.com')->first();

        // Sukoharjo Users
        $custSkh1     = User::where('email', 'customer.sukoharjo1@sayabantu.com')->first();
        $custSkh2     = User::where('email', 'customer.sukoharjo2@sayabantu.com')->first();
        $mitraSkh1    = User::where('email', 'mitra.sukoharjo1@sayabantu.com')->first();
        $mitraSkh2    = User::where('email', 'mitra.sukoharjo2@sayabantu.com')->first();

        // ─────────────────────────────────────────────────────────────────────
        // 1. TRANSAKSI TOP-UP CUSTOMER (Tersebar 4 Agustus - 3 September 2026)
        // ─────────────────────────────────────────────────────────────────────
        $topupPlans = [
            // Periode Awal (04 - 10 Agustus 2026)
            ['user' => $custSleman1, 'order_id' => 'TOPUP-20260804-SLM01', 'amount' => 500000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS Mandiri', 'date' => '2026-08-04 07:30:00'],
            ['user' => $custSolo1,   'order_id' => 'TOPUP-20260804-SKT01', 'amount' => 500000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BCA', 'date' => '2026-08-04 08:00:00'],
            ['user' => $custSkh1,    'order_id' => 'TOPUP-20260805-SKH01', 'amount' => 600000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA Mandiri', 'date' => '2026-08-05 07:45:00'],
            ['user' => $custJogja1,  'order_id' => 'TOPUP-20260805-JOG01', 'amount' => 500000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BPD DIY', 'date' => '2026-08-05 08:15:00'],
            ['user' => $custSleman2, 'order_id' => 'TOPUP-20260806-SLM02', 'amount' => 450000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BRI', 'date' => '2026-08-06 07:15:00'],
            ['user' => $custSolo2,   'order_id' => 'TOPUP-20260806-SKT02', 'amount' => 400000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS ShopeePay', 'date' => '2026-08-06 08:00:00'],
            ['user' => $custSkh2,    'order_id' => 'TOPUP-20260807-SKH02', 'amount' => 550000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BCA', 'date' => '2026-08-07 07:30:00'],
            ['user' => $custSleman3, 'order_id' => 'TOPUP-20260807-SLM03', 'amount' => 500000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS GoPay', 'date' => '2026-08-07 08:30:00'],
            ['user' => $custSleman1, 'order_id' => 'TOPUP-20260808-SLM04', 'amount' => 350000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BCA', 'date' => '2026-08-08 07:00:00'],
            ['user' => $custSolo1,   'order_id' => 'TOPUP-20260808-SKT03', 'amount' => 400000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS Mandiri', 'date' => '2026-08-08 08:15:00'],
            ['user' => $custSkh1,    'order_id' => 'TOPUP-20260809-SKH03', 'amount' => 450000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BCA', 'date' => '2026-08-09 07:15:00'],
            ['user' => $custJogja1,  'order_id' => 'TOPUP-20260809-JOG02', 'amount' => 350000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BNI', 'date' => '2026-08-09 08:00:00'],
            ['user' => $custSleman2, 'order_id' => 'TOPUP-20260810-SLM05', 'amount' => 400000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BNI', 'date' => '2026-08-10 07:00:00'],
            ['user' => $custSolo2,   'order_id' => 'TOPUP-20260810-SKT04', 'amount' => 350000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS GoPay', 'date' => '2026-08-10 08:30:00'],

            // Periode Tengah (11 - 20 Agustus 2026)
            ['user' => $custSkh2,    'order_id' => 'TOPUP-20260811-SKH04', 'amount' => 400000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA Bank Jateng', 'date' => '2026-08-11 07:15:00'],
            ['user' => $custSleman3, 'order_id' => 'TOPUP-20260811-SLM06', 'amount' => 350000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BPD DIY', 'date' => '2026-08-11 08:00:00'],
            ['user' => $custSolo1,   'order_id' => 'TOPUP-20260812-SKT05', 'amount' => 300000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA Mandiri', 'date' => '2026-08-12 07:30:00'],
            ['user' => $custJogja1,  'order_id' => 'TOPUP-20260812-JOG03', 'amount' => 400000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS ShopeePay', 'date' => '2026-08-12 08:45:00'],
            ['user' => $custSleman1, 'order_id' => 'TOPUP-20260813-SLM07', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS Mandiri', 'date' => '2026-08-13 07:00:00'],
            ['user' => $custSkh1,    'order_id' => 'TOPUP-20260813-SKH05', 'amount' => 350000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BCA', 'date' => '2026-08-13 08:15:00'],
            ['user' => $custSolo2,   'order_id' => 'TOPUP-20260814-SKT06', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BCA', 'date' => '2026-08-14 06:45:00'],
            ['user' => $custSleman2, 'order_id' => 'TOPUP-20260814-SLM08', 'amount' => 500000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BRI', 'date' => '2026-08-14 07:30:00'],
            ['user' => $custSkh2,    'order_id' => 'TOPUP-20260815-SKH06', 'amount' => 400000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS Bank Jateng', 'date' => '2026-08-15 07:15:00'],
            ['user' => $custJogja1,  'order_id' => 'TOPUP-20260815-JOG04', 'amount' => 350000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BPD DIY', 'date' => '2026-08-15 08:00:00'],
            ['user' => $custSleman3, 'order_id' => 'TOPUP-20260816-SLM09', 'amount' => 400000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS GoPay', 'date' => '2026-08-16 07:00:00'],
            ['user' => $custSolo1,   'order_id' => 'TOPUP-20260816-SKT07', 'amount' => 350000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BCA', 'date' => '2026-08-16 08:30:00'],
            ['user' => $custSleman1, 'order_id' => 'TOPUP-20260817-SLM10', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS Mandiri', 'date' => '2026-08-17 06:30:00'],
            ['user' => $custSkh1,    'order_id' => 'TOPUP-20260817-SKH07', 'amount' => 400000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA Mandiri', 'date' => '2026-08-17 08:00:00'],
            ['user' => $custSolo2,   'order_id' => 'TOPUP-20260818-SKT08', 'amount' => 350000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS ShopeePay', 'date' => '2026-08-18 07:00:00'],
            ['user' => $custSleman2, 'order_id' => 'TOPUP-20260818-SLM11', 'amount' => 450000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BNI', 'date' => '2026-08-18 08:15:00'],
            ['user' => $custSkh2,    'order_id' => 'TOPUP-20260819-SKH08', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BCA', 'date' => '2026-08-19 07:30:00'],
            ['user' => $custJogja1,  'order_id' => 'TOPUP-20260819-JOG05', 'amount' => 300000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BNI', 'date' => '2026-08-19 08:15:00'],
            ['user' => $custSleman3, 'order_id' => 'TOPUP-20260820-SLM12', 'amount' => 350000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BPD DIY', 'date' => '2026-08-20 07:00:00'],
            ['user' => $custSolo1,   'order_id' => 'TOPUP-20260820-SKT09', 'amount' => 400000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BRI', 'date' => '2026-08-20 08:00:00'],

            // Periode Akhir (21 Agustus - 03 September 2026)
            ['user' => $custSkh1,    'order_id' => 'TOPUP-20260821-SKH09', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS Bank Jateng', 'date' => '2026-08-21 07:30:00'],
            ['user' => $custSleman1, 'order_id' => 'TOPUP-20260821-SLM13', 'amount' => 350000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BCA', 'date' => '2026-08-21 08:15:00'],
            ['user' => $custSolo2,   'order_id' => 'TOPUP-20260822-SKT10', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS GoPay', 'date' => '2026-08-22 07:00:00'],
            ['user' => $custJogja1,  'order_id' => 'TOPUP-20260822-JOG06', 'amount' => 400000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA Mandiri', 'date' => '2026-08-22 08:30:00'],
            ['user' => $custSkh2,    'order_id' => 'TOPUP-20260823-SKH10', 'amount' => 350000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BCA', 'date' => '2026-08-23 07:15:00'],
            ['user' => $custSleman2, 'order_id' => 'TOPUP-20260823-SLM14', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS Mandiri', 'date' => '2026-08-23 08:00:00'],
            ['user' => $custSolo1,   'order_id' => 'TOPUP-20260824-SKT11', 'amount' => 400000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BNI', 'date' => '2026-08-24 07:45:00'],
            ['user' => $custSleman3, 'order_id' => 'TOPUP-20260824-SLM15', 'amount' => 450000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BPD DIY', 'date' => '2026-08-24 08:30:00'],
            ['user' => $custSkh1,    'order_id' => 'TOPUP-20260825-SKH11', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS ShopeePay', 'date' => '2026-08-25 07:00:00'],
            ['user' => $custJogja1,  'order_id' => 'TOPUP-20260825-JOG07', 'amount' => 300000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BRI', 'date' => '2026-08-25 08:15:00'],
            ['user' => $custSleman1, 'order_id' => 'TOPUP-20260826-SLM16', 'amount' => 350000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BCA', 'date' => '2026-08-26 07:30:00'],
            ['user' => $custSolo2,   'order_id' => 'TOPUP-20260826-SKT12', 'amount' => 300000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BCA', 'date' => '2026-08-26 08:00:00'],
            ['user' => $custSkh2,    'order_id' => 'TOPUP-20260827-SKH12', 'amount' => 250000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS Bank Jateng', 'date' => '2026-08-27 07:15:00'],
            ['user' => $custSleman2, 'order_id' => 'TOPUP-20260827-SLM17', 'amount' => 350000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA Mandiri', 'date' => '2026-08-27 08:30:00'],
            ['user' => $custSleman1, 'order_id' => 'TOPUP-20260828-SLM18', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS Mandiri', 'date' => '2026-08-28 07:00:00'],
            ['user' => $custSolo1,   'order_id' => 'TOPUP-20260828-SKT13', 'amount' => 300000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BNI', 'date' => '2026-08-28 08:15:00'],
            ['user' => $custSkh1,    'order_id' => 'TOPUP-20260829-SKH13', 'amount' => 400000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BCA', 'date' => '2026-08-29 07:30:00'],
            ['user' => $custJogja1,  'order_id' => 'TOPUP-20260829-JOG08', 'amount' => 300000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BPD DIY', 'date' => '2026-08-29 08:00:00'],
            ['user' => $custSleman3, 'order_id' => 'TOPUP-20260830-SLM19', 'amount' => 350000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS GoPay', 'date' => '2026-08-30 07:00:00'],
            ['user' => $custSolo2,   'order_id' => 'TOPUP-20260830-SKT14', 'amount' => 300000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA Mandiri', 'date' => '2026-08-30 08:15:00'],
            ['user' => $custSkh2,    'order_id' => 'TOPUP-20260831-SKH14', 'amount' => 350000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BRI', 'date' => '2026-08-31 07:15:00'],
            ['user' => $custSleman2, 'order_id' => 'TOPUP-20260831-SLM20', 'amount' => 350000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BPD DIY', 'date' => '2026-08-31 08:30:00'],
            ['user' => $custSleman1, 'order_id' => 'TOPUP-20260901-SLM21', 'amount' => 350000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS Mandiri', 'date' => '2026-09-01 07:00:00'],
            ['user' => $custSolo1,   'order_id' => 'TOPUP-20260901-SKT15', 'amount' => 350000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BCA', 'date' => '2026-09-01 08:00:00'],
            ['user' => $custSkh1,    'order_id' => 'TOPUP-20260902-SKH15', 'amount' => 450000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA Mandiri', 'date' => '2026-09-02 07:00:00'],
            ['user' => $custJogja1,  'order_id' => 'TOPUP-20260902-JOG09', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS ShopeePay', 'date' => '2026-09-02 08:15:00'],
            ['user' => $custSleman2, 'order_id' => 'TOPUP-20260903-SLM22', 'amount' => 300000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA BNI', 'date' => '2026-09-03 07:15:00'],
            ['user' => $custSolo2,   'order_id' => 'TOPUP-20260903-SKT16', 'amount' => 300000, 'method' => 'qris', 'desc' => 'Top-Up Saldo via QRIS BCA', 'date' => '2026-09-03 08:00:00'],
            ['user' => $custSkh2,    'order_id' => 'TOPUP-20260903-SKH16', 'amount' => 250000, 'method' => 'bank_transfer', 'desc' => 'Top-Up Saldo via VA Bank Jateng', 'date' => '2026-09-03 08:30:00'],
        ];

        foreach ($topupPlans as $index => $p) {
            $user = $p['user'];
            if (!$user) {
                continue;
            }

            $date = Carbon::parse($p['date']);
            $dateFormatted = $date->format('Ymd');
            $seq = str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $requestCode = "TPU-{$dateFormatted}-{$seq}";

            BalanceTransaction::updateOrCreate(
                [
                    'user_id'  => $user->id,
                    'order_id' => $p['order_id'],
                ],
                [
                    'amount'         => (float) $p['amount'],
                    'direction'      => 'credit',
                    'admin_fee'      => 0.00,
                    'total_payment'  => (float) $p['amount'],
                    'type'           => 'topup',
                    'request_code'   => $requestCode,
                    'description'    => $p['desc'],
                    'payment_method' => $p['method'],
                    'customer_name'  => $user->name,
                    'customer_email' => $user->email,
                    'customer_phone' => $user->phone ?? '081234567890',
                    'status'         => 'completed',
                    'processed_at'   => $date,
                    'created_at'     => $date,
                    'updated_at'     => $date,
                ]
            );
        }

        // ─────────────────────────────────────────────────────────────────────
        // 2. TRANSAKSI JASA BANTUAN (ESCROW LOCK, MITRA EARNING, PLATFORM FEE)
        // ─────────────────────────────────────────────────────────────────────
        $completedHelps = Help::where('status', Help::STATUS_SELESAI)->with('user', 'mitra')->orderBy('created_at')->get();

        foreach ($completedHelps as $h) {
            $createdAt   = $h->created_at ?? Carbon::parse('2026-08-04 09:00:00');
            $completedAt = $h->completed_at ?? $createdAt->copy()->addHours(2);

            $customer = $h->user;
            $mitra    = $h->mitra;

            // 2A. Escrow Lock Customer (Debit)
            if ($h->user_id && $customer) {
                $totalEscrow = (float) ($h->amount + $h->platform_fee_amount);

                $escrowTx = BalanceTransaction::updateOrCreate(
                    [
                        'user_id'      => $h->user_id,
                        'reference_id' => (string) $h->id,
                        'type'         => 'escrow_lock',
                    ],
                    [
                        'order_id'       => $h->order_id,
                        'reference_type' => 'help',
                        'amount'         => $totalEscrow,
                        'direction'      => 'debit',
                        'customer_name'  => $customer->name,
                        'customer_email' => $customer->email,
                        'customer_phone' => $customer->phone ?? '081234567890',
                        'description'    => "Pembayaran Bantuan '{$h->title}' (Jasa: Rp " . number_format($h->amount, 0, ',', '.') . " + Layanan: Rp " . number_format($h->platform_fee_amount, 0, ',', '.') . ")",
                        'status'         => 'completed',
                        'processed_at'   => $createdAt,
                        'created_at'     => $createdAt,
                        'updated_at'     => $createdAt,
                    ]
                );

                $h->update(['escrow_transaction_id' => $escrowTx->id]);
            }

            // 2B. Pendapatan Mitra / Earning (Credit)
            if ($h->mitra_id && $h->mitra_earning > 0 && $mitra) {
                BalanceTransaction::updateOrCreate(
                    [
                        'user_id'      => $h->mitra_id,
                        'reference_id' => (string) $h->id,
                        'type'         => 'earning',
                    ],
                    [
                        'order_id'       => $h->order_id,
                        'reference_type' => 'help',
                        'amount'         => (float) $h->mitra_earning,
                        'direction'      => 'credit',
                        'customer_name'  => $customer ? $customer->name : 'Customer',
                        'customer_email' => $customer ? $customer->email : '',
                        'customer_phone' => $customer ? $customer->phone : '',
                        'description'    => "Pendapatan Selesai Bantuan '{$h->title}'" . ($customer ? " dari {$customer->name}" : ""),
                        'status'         => 'completed',
                        'processed_at'   => $completedAt,
                        'created_at'     => $completedAt,
                        'updated_at'     => $completedAt,
                    ]
                );
            }

            // 2C. Biaya Layanan Platform / Platform Fee (Kas Platform)
            if ($h->platform_fee_amount > 0) {
                BalanceTransaction::updateOrCreate(
                    [
                        'user_id'      => null,
                        'reference_id' => (string) $h->id,
                        'type'         => 'platform_fee',
                    ],
                    [
                        'order_id'       => $h->order_id,
                        'reference_type' => 'help',
                        'amount'         => (float) $h->platform_fee_amount,
                        'direction'      => 'credit',
                        'description'    => "Biaya Layanan Platform Bantuan '{$h->title}' (Order: {$h->order_id})",
                        'status'         => 'completed',
                        'processed_at'   => $completedAt,
                        'created_at'     => $completedAt,
                        'updated_at'     => $completedAt,
                    ]
                );
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 3. TRANSAKSI WITHDRAW MITRA (Terdistribusi Sepanjang Bulan)
        // ─────────────────────────────────────────────────────────────────────
        $withdrawPlans = [
            // Mitra 1 Sleman (Agus Prasetyo)
            ['user' => $mitraSleman1, 'order_id' => 'WD-20260808-SLM01', 'amount' => 100000, 'bank' => 'BCA', 'acc_no' => '1234567890', 'acc_name' => 'Agus Prasetyo', 'date' => '2026-08-08 17:00:00'],
            ['user' => $mitraSleman1, 'order_id' => 'WD-20260818-SLM02', 'amount' => 150000, 'bank' => 'BCA', 'acc_no' => '1234567890', 'acc_name' => 'Agus Prasetyo', 'date' => '2026-08-18 16:30:00'],
            ['user' => $mitraSleman1, 'order_id' => 'WD-20260828-SLM03', 'amount' => 120000, 'bank' => 'BCA', 'acc_no' => '1234567890', 'acc_name' => 'Agus Prasetyo', 'date' => '2026-08-28 18:00:00'],

            // Mitra 2 Sleman (Budi Santoso)
            ['user' => $mitraSleman2, 'order_id' => 'WD-20260810-SLM04', 'amount' => 120000, 'bank' => 'MANDIRI', 'acc_no' => '1370012345678', 'acc_name' => 'Budi Santoso', 'date' => '2026-08-10 17:30:00'],
            ['user' => $mitraSleman2, 'order_id' => 'WD-20260820-SLM05', 'amount' => 150000, 'bank' => 'MANDIRI', 'acc_no' => '1370012345678', 'acc_name' => 'Budi Santoso', 'date' => '2026-08-20 17:00:00'],
            ['user' => $mitraSleman2, 'order_id' => 'WD-20260901-SLM06', 'amount' => 100000, 'bank' => 'MANDIRI', 'acc_no' => '1370012345678', 'acc_name' => 'Budi Santoso', 'date' => '2026-09-01 16:45:00'],

            // Mitra 3 Sleman (Fajar Nugroho)
            ['user' => $mitraSleman3, 'order_id' => 'WD-20260812-SLM07', 'amount' => 100000, 'bank' => 'BNI', 'acc_no' => '0543219876', 'acc_name' => 'Fajar Nugroho', 'date' => '2026-08-12 17:00:00'],
            ['user' => $mitraSleman3, 'order_id' => 'WD-20260826-SLM08', 'amount' => 150000, 'bank' => 'BNI', 'acc_no' => '0543219876', 'acc_name' => 'Fajar Nugroho', 'date' => '2026-08-26 17:30:00'],

            // Mitra 1 Jogja (Danang Saputra)
            ['user' => $mitraJogja1,  'order_id' => 'WD-20260814-JOG01', 'amount' => 120000, 'bank' => 'BPD DIY', 'acc_no' => '0012345678', 'acc_name' => 'Danang Saputra', 'date' => '2026-08-14 17:00:00'],
            ['user' => $mitraJogja1,  'order_id' => 'WD-20260828-JOG02', 'amount' => 150000, 'bank' => 'BPD DIY', 'acc_no' => '0012345678', 'acc_name' => 'Danang Saputra', 'date' => '2026-08-28 17:30:00'],

            // Mitra 1 Surakarta (Eko Saputra)
            ['user' => $mitraSolo1,   'order_id' => 'WD-20260809-SKT01', 'amount' => 100000, 'bank' => 'BRI', 'acc_no' => '012301098765504', 'acc_name' => 'Eko Saputra', 'date' => '2026-08-09 17:15:00'],
            ['user' => $mitraSolo1,   'order_id' => 'WD-20260821-SKT02', 'amount' => 120000, 'bank' => 'BRI', 'acc_no' => '012301098765504', 'acc_name' => 'Eko Saputra', 'date' => '2026-08-21 16:30:00'],
            ['user' => $mitraSolo1,   'order_id' => 'WD-20260902-SKT03', 'amount' => 110000, 'bank' => 'BRI', 'acc_no' => '012301098765504', 'acc_name' => 'Eko Saputra', 'date' => '2026-09-02 17:45:00'],

            // Mitra 2 Surakarta (Hendra Wijaya)
            ['user' => $mitraSolo2,   'order_id' => 'WD-20260811-SKT04', 'amount' => 150000, 'bank' => 'BNI', 'acc_no' => '0987654321', 'acc_name' => 'Hendra Wijaya', 'date' => '2026-08-11 17:00:00'],
            ['user' => $mitraSolo2,   'order_id' => 'WD-20260823-SKT05', 'amount' => 130000, 'bank' => 'BNI', 'acc_no' => '0987654321', 'acc_name' => 'Hendra Wijaya', 'date' => '2026-08-23 18:15:00'],
            ['user' => $mitraSolo2,   'order_id' => 'WD-20260903-SKT06', 'amount' => 120000, 'bank' => 'BNI', 'acc_no' => '0987654321', 'acc_name' => 'Hendra Wijaya', 'date' => '2026-09-03 17:30:00'],

            // Mitra 1 Sukoharjo (Tri Wahyudi)
            ['user' => $mitraSkh1,    'order_id' => 'WD-20260813-SKH01', 'amount' => 150000, 'bank' => 'BCA', 'acc_no' => '7890123456', 'acc_name' => 'Tri Wahyudi', 'date' => '2026-08-13 17:00:00'],
            ['user' => $mitraSkh1,    'order_id' => 'WD-20260825-SKH02', 'amount' => 140000, 'bank' => 'BCA', 'acc_no' => '7890123456', 'acc_name' => 'Tri Wahyudi', 'date' => '2026-08-25 17:30:00'],

            // Mitra 2 Sukoharjo (Joko Susilo)
            ['user' => $mitraSkh2,    'order_id' => 'WD-20260815-SKH03', 'amount' => 120000, 'bank' => 'Bank Jateng', 'acc_no' => '2034567890', 'acc_name' => 'Joko Susilo', 'date' => '2026-08-15 17:15:00'],
            ['user' => $mitraSkh2,    'order_id' => 'WD-20260829-SKH04', 'amount' => 130000, 'bank' => 'Bank Jateng', 'acc_no' => '2034567890', 'acc_name' => 'Joko Susilo', 'date' => '2026-08-29 17:45:00'],
        ];

        foreach ($withdrawPlans as $wd) {
            $user = $wd['user'];
            if (!$user) {
                continue;
            }

            $amount = (float) $wd['amount'];
            $date   = Carbon::parse($wd['date']);

            $withdrawRequest = WithdrawRequest::updateOrCreate(
                [
                    'user_id'     => $user->id,
                    'external_id' => $wd['order_id'],
                ],
                [
                    'amount'         => (int) $amount,
                    'admin_fee'      => 0,
                    'net_amount'     => (int) $amount,
                    'bank_code'      => $wd['bank'],
                    'account_number' => $wd['acc_no'],
                    'account_name'   => $wd['acc_name'],
                    'status'         => 'success',
                    'description'    => "Penarikan Saldo Mitra ke Rekening {$wd['bank']} - {$wd['acc_no']} a.n {$wd['acc_name']}",
                    'processed_at'   => $date,
                    'created_at'     => $date,
                    'updated_at'     => $date,
                ]
            );

            BalanceTransaction::updateOrCreate(
                [
                    'user_id'      => $user->id,
                    'reference_id' => (string) $withdrawRequest->id,
                    'type'         => 'withdraw',
                ],
                [
                    'order_id'       => $wd['order_id'],
                    'reference_type' => 'withdraw_request',
                    'amount'         => $amount,
                    'direction'      => 'debit',
                    'admin_fee'      => 0.00,
                    'total_payment'  => $amount,
                    'payment_method' => 'bank_transfer',
                    'customer_name'  => $user->name,
                    'customer_email' => $user->email,
                    'customer_phone' => $user->phone ?? '081234567890',
                    'description'    => "Penarikan Saldo Mitra ke Rekening {$wd['bank']} - {$wd['acc_no']} a.n {$wd['acc_name']}",
                    'status'         => 'completed',
                    'processed_at'   => $date,
                    'created_at'     => $date,
                    'updated_at'     => $date,
                ]
            );
        }

        // ─────────────────────────────────────────────────────────────────────
        // 4. SINKRONISASI SALDO DI TABEL USER_BALANCES
        // ─────────────────────────────────────────────────────────────────────
        User::query()->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $credits = BalanceTransaction::where('user_id', $user->id)
                    ->whereIn('type', BalanceTransaction::creditTypes())
                    ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = 'completed'")
                    ->sum('amount');

                $debits = BalanceTransaction::where('user_id', $user->id)
                    ->whereIn('type', BalanceTransaction::debitTypes())
                    ->whereRaw("LOWER(TRIM(COALESCE(status, ''))) = 'completed'")
                    ->sum('amount');

                $balance = max(0.00, (float) $credits - (float) $debits);

                UserBalance::updateOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => $balance]
                );
            }
        });

        $this->command->info('UserBalancesSeeder berhasil membuat transaksi keuangan harian yang saling terhubung antara Customer & Mitra serta menyinkronkan saldo seluruh akun.');
    }
}