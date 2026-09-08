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
        // Ambil semua Customer dan Mitra
        $customers = User::where('role', 'customer')->get()->keyBy('email');
        $mitras    = User::where('role', 'mitra')->get()->keyBy('email');

        // ─────────────────────────────────────────────────────────────────────
        // 1. TRANSAKSI TOP-UP CUSTOMER SE-INDONESIA (Lengkap QRIS & Virtual Account)
        // ─────────────────────────────────────────────────────────────────────
        $topupTemplates = [
            // Sleman & Jogja
            ['email' => 'customer.sleman1@sayabantu.com', 'amount' => 500000, 'method' => 'qris', 'bank_desc' => 'QRIS Mandiri', 'date' => '2026-08-04 07:30:00'],
            ['email' => 'customer.sleman2@sayabantu.com', 'amount' => 450000, 'method' => 'bank_transfer', 'bank_desc' => 'VA BRI', 'date' => '2026-08-06 07:15:00'],
            ['email' => 'customer@sayabantu.com',        'amount' => 500000, 'method' => 'qris', 'bank_desc' => 'QRIS GoPay', 'date' => '2026-08-07 08:30:00'],
            ['email' => 'customer.sleman3@sayabantu.com', 'amount' => 350000, 'method' => 'bank_transfer', 'bank_desc' => 'VA BNI', 'date' => '2026-08-08 09:00:00'],
            ['email' => 'customer.jogja1@sayabantu.com',  'amount' => 500000, 'method' => 'qris', 'bank_desc' => 'QRIS BPD DIY', 'date' => '2026-08-05 08:15:00'],
            ['email' => 'customer.jogja1@sayabantu.com',  'amount' => 350000, 'method' => 'bank_transfer', 'bank_desc' => 'VA BPD DIY', 'date' => '2026-08-15 08:00:00'],
            ['email' => 'customer.jogja2@sayabantu.com',  'amount' => 400000, 'method' => 'qris', 'bank_desc' => 'QRIS BCA', 'date' => '2026-08-17 10:00:00'],

            // Surakarta, Sukoharjo & Semarang
            ['email' => 'customer.surakarta1@sayabantu.com', 'amount' => 500000, 'method' => 'qris', 'bank_desc' => 'QRIS BCA', 'date' => '2026-08-04 08:00:00'],
            ['email' => 'customer.surakarta2@sayabantu.com', 'amount' => 400000, 'method' => 'qris', 'bank_desc' => 'QRIS ShopeePay', 'date' => '2026-08-06 08:00:00'],
            ['email' => 'customer.sukoharjo1@sayabantu.com', 'amount' => 600000, 'method' => 'bank_transfer', 'bank_desc' => 'VA Mandiri', 'date' => '2026-08-05 07:45:00'],
            ['email' => 'customer.sukoharjo2@sayabantu.com', 'amount' => 550000, 'method' => 'bank_transfer', 'bank_desc' => 'VA Bank Jateng', 'date' => '2026-08-07 07:30:00'],
            ['email' => 'customer.semarang1@sayabantu.com',  'amount' => 500000, 'method' => 'bank_transfer', 'bank_desc' => 'VA Bank Jateng', 'date' => '2026-08-21 08:15:00'],

            // DKI Jakarta (Jaksel, Jakbar, Jaktim)
            ['email' => 'customer.jaksel1@sayabantu.com', 'amount' => 750000, 'method' => 'qris', 'bank_desc' => 'QRIS BCA', 'date' => '2026-08-15 09:00:00'],
            ['email' => 'customer.jaksel1@sayabantu.com', 'amount' => 500000, 'method' => 'bank_transfer', 'bank_desc' => 'VA BCA', 'date' => '2026-08-25 10:00:00'],
            ['email' => 'customer.jaksel2@sayabantu.com', 'amount' => 600000, 'method' => 'qris', 'bank_desc' => 'QRIS GoPay', 'date' => '2026-08-16 08:30:00'],
            ['email' => 'customer.jaksel3@sayabantu.com', 'amount' => 450000, 'method' => 'qris', 'bank_desc' => 'QRIS Mandiri', 'date' => '2026-08-18 11:00:00'],
            ['email' => 'customer.jakbar1@sayabantu.com', 'amount' => 500000, 'method' => 'bank_transfer', 'bank_desc' => 'VA BCA', 'date' => '2026-08-19 09:30:00'],
            ['email' => 'customer.jaktim1@sayabantu.com', 'amount' => 400000, 'method' => 'qris', 'bank_desc' => 'QRIS BRImo', 'date' => '2026-08-20 08:15:00'],

            // Bandung
            ['email' => 'customer.bandung1@sayabantu.com', 'amount' => 500000, 'method' => 'bank_transfer', 'bank_desc' => 'VA BJB', 'date' => '2026-08-20 08:00:00'],
            ['email' => 'customer.bandung1@sayabantu.com', 'amount' => 300000, 'method' => 'qris', 'bank_desc' => 'QRIS BCA', 'date' => '2026-08-28 09:15:00'],
            ['email' => 'customer.bandung2@sayabantu.com', 'amount' => 450000, 'method' => 'qris', 'bank_desc' => 'QRIS ShopeePay', 'date' => '2026-08-22 07:45:00'],

            // Surabaya & Malang
            ['email' => 'customer.surabaya1@sayabantu.com', 'amount' => 650000, 'method' => 'bank_transfer', 'bank_desc' => 'VA Bank Jatim', 'date' => '2026-08-24 08:30:00'],
            ['email' => 'customer.surabaya2@sayabantu.com', 'amount' => 500000, 'method' => 'qris', 'bank_desc' => 'QRIS Mandiri', 'date' => '2026-08-26 09:00:00'],
            ['email' => 'customer.malang1@sayabantu.com',   'amount' => 400000, 'method' => 'qris', 'bank_desc' => 'QRIS BCA', 'date' => '2026-08-25 10:30:00'],

            // Luar Jawa: Denpasar, Medan, Palembang, Makassar, Tangsel
            ['email' => 'customer.denpasar1@sayabantu.com',  'amount' => 700000, 'method' => 'qris', 'bank_desc' => 'QRIS BPD Bali', 'date' => '2026-08-27 10:00:00'],
            ['email' => 'customer.denpasar2@sayabantu.com',  'amount' => 500000, 'method' => 'bank_transfer', 'bank_desc' => 'VA BCA', 'date' => '2026-08-28 11:00:00'],
            ['email' => 'customer.medan1@sayabantu.com',     'amount' => 500000, 'method' => 'bank_transfer', 'bank_desc' => 'VA Bank Sumut', 'date' => '2026-08-29 08:45:00'],
            ['email' => 'customer.medan2@sayabantu.com',     'amount' => 600000, 'method' => 'qris', 'bank_desc' => 'QRIS Mandiri', 'date' => '2026-08-30 09:15:00'],
            ['email' => 'customer.palembang1@sayabantu.com', 'amount' => 450000, 'method' => 'bank_transfer', 'bank_desc' => 'VA Bank Sumsel Babel', 'date' => '2026-08-31 08:30:00'],
            ['email' => 'customer.makassar1@sayabantu.com',  'amount' => 550000, 'method' => 'bank_transfer', 'bank_desc' => 'VA Bank Sulselbar', 'date' => '2026-08-30 09:00:00'],
            ['email' => 'customer.makassar2@sayabantu.com',  'amount' => 400000, 'method' => 'qris', 'bank_desc' => 'QRIS BRI', 'date' => '2026-09-01 10:00:00'],
            ['email' => 'customer.tangsel1@sayabantu.com',   'amount' => 600000, 'method' => 'qris', 'bank_desc' => 'QRIS BCA', 'date' => '2026-09-02 08:45:00'],
        ];

        foreach ($topupTemplates as $index => $p) {
            $user = $customers->get($p['email']);
            if (!$user) {
                continue;
            }

            $date = Carbon::parse($p['date']);
            $dateFormatted = $date->format('Ymd');
            $seq = str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $orderId = "TOPUP-{$dateFormatted}-{$seq}";
            $requestCode = "TPU-{$dateFormatted}-{$seq}";

            BalanceTransaction::updateOrCreate(
                [
                    'user_id'  => $user->id,
                    'order_id' => $orderId,
                ],
                [
                    'amount'         => (float) $p['amount'],
                    'direction'      => 'credit',
                    'admin_fee'      => 0.00,
                    'total_payment'  => (float) $p['amount'],
                    'type'           => 'topup',
                    'request_code'   => $requestCode,
                    'description'    => "Top-Up Saldo Dompet via {$p['bank_desc']}",
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
        // 2. TRANSAKSI JASA BANTUAN (ESCROW LOCK, MITRA EARNING, PLATFORM FEE, REFUND)
        // ─────────────────────────────────────────────────────────────────────
        $allHelps = Help::with(['user', 'mitra', 'cancelRequests'])->orderBy('created_at')->get();

        foreach ($allHelps as $h) {
            $createdAt   = $h->created_at ?? Carbon::parse('2026-08-04 09:00:00');
            $completedAt = $h->completed_at ?? $createdAt->copy()->addHours(2);

            $customer = $h->user;
            $mitra    = $h->mitra;

            $serviceFee   = (float) ($h->service_fee ?? $h->amount ?? 0);
            $travelFee    = (float) ($h->travel_fee ?? 0);
            $itemFund     = (float) ($h->item_fund ?? 0);
            $platformFee  = (float) ($h->platform_fee_amount ?? $h->admin_fee ?? 0);
            $totalEscrow  = (float) ($h->total_amount > 0 ? $h->total_amount : ($serviceFee + $travelFee + $itemFund + $platformFee));

            // Breakdown description
            $descParts = [];
            $descParts[] = "Jasa: Rp " . number_format($serviceFee, 0, ',', '.');
            if ($travelFee > 0) {
                $descParts[] = "Ongkos: Rp " . number_format($travelFee, 0, ',', '.');
            }
            if ($itemFund > 0) {
                $descParts[] = "Titipan Belanja: Rp " . number_format($itemFund, 0, ',', '.');
            }
            $descParts[] = "Layanan: Rp " . number_format($platformFee, 0, ',', '.');
            $breakdownStr = implode(' + ', $descParts);

            // 2A. Escrow Lock Customer (Debit saat pesanan dibuat & dibayar)
            if ($h->user_id && $customer && $h->payment_status !== Help::PAYMENT_STATUS_UNPAID) {
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
                        'description'    => "Pembayaran Bantuan '{$h->title}' ({$breakdownStr})",
                        'status'         => 'completed',
                        'processed_at'   => $createdAt,
                        'created_at'     => $createdAt,
                        'updated_at'     => $createdAt,
                    ]
                );

                $h->update(['escrow_transaction_id' => $escrowTx->id]);
            }

            // 2B. Pendapatan Mitra / Earning (Credit saat pesanan selesai)
            if ($h->status === Help::STATUS_SELESAI && $h->mitra_id && $h->mitra_earning > 0 && $mitra) {
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
                        'description'    => "Pendapatan Selesai Bantuan '{$h->title}'" . ($customer ? " dari {$customer->name}" : "") . " (Jasa: Rp " . number_format($serviceFee, 0, ',', '.') . ($travelFee > 0 ? " + Ongkos: Rp " . number_format($travelFee, 0, ',', '.') : "") . ")",
                        'status'         => 'completed',
                        'processed_at'   => $completedAt,
                        'created_at'     => $completedAt,
                        'updated_at'     => $completedAt,
                    ]
                );
            }

            // 2C. Biaya Layanan Platform / Platform Fee (Kas Platform)
            if ($h->status === Help::STATUS_SELESAI && $platformFee > 0) {
                BalanceTransaction::updateOrCreate(
                    [
                        'user_id'      => null,
                        'reference_id' => (string) $h->id,
                        'type'         => 'platform_fee',
                    ],
                    [
                        'order_id'       => $h->order_id,
                        'reference_type' => 'help',
                        'amount'         => $platformFee,
                        'direction'      => 'credit',
                        'description'    => "Biaya Layanan Platform Bantuan '{$h->title}' (Order: {$h->order_id})",
                        'status'         => 'completed',
                        'processed_at'   => $completedAt,
                        'created_at'     => $completedAt,
                        'updated_at'     => $completedAt,
                    ]
                );
            }

            // 2D. Full Refund Kasus Pembatalan Lengkap (Credit Customer)
            if ($h->escrow_status === Help::ESCROW_STATUS_REFUNDED && $h->user_id && $customer) {
                BalanceTransaction::updateOrCreate(
                    [
                        'user_id'      => $h->user_id,
                        'reference_id' => (string) $h->id . '_full_refund',
                        'type'         => 'refund',
                    ],
                    [
                        'order_id'       => $h->order_id,
                        'reference_type' => 'help',
                        'amount'         => $totalEscrow,
                        'direction'      => 'credit',
                        'customer_name'  => $customer->name,
                        'customer_email' => $customer->email,
                        'customer_phone' => $customer->phone ?? '081234567890',
                        'description'    => "Pengembalian Dana Penuh Pembatalan Bantuan '{$h->title}' (Order: {$h->order_id})",
                        'status'         => 'completed',
                        'processed_at'   => $createdAt->copy()->addMinutes(30),
                        'created_at'     => $createdAt->copy()->addMinutes(30),
                        'updated_at'     => $createdAt->copy()->addMinutes(30),
                    ]
                );
            }

            // 2E. Partial Settlement Audit Pembatalan Khusus (Credit Customer Refund & Credit Mitra Compensation)
            if ($h->escrow_status === Help::ESCROW_STATUS_PARTIAL_REFUND) {
                $cancelReq = $h->cancelRequests()->first();
                $refundAmt = $cancelReq ? (float) $cancelReq->refund_amount : 45000.00;
                $partnerAmt = $cancelReq ? (float) $cancelReq->partner_amount : 45000.00;

                if ($h->user_id && $customer && $refundAmt > 0) {
                    BalanceTransaction::updateOrCreate(
                        [
                            'user_id'      => $h->user_id,
                            'reference_id' => (string) $h->id . '_partial_refund',
                            'type'         => 'refund',
                        ],
                        [
                            'order_id'       => $h->order_id,
                            'reference_type' => 'help',
                            'amount'         => $refundAmt,
                            'direction'      => 'credit',
                            'customer_name'  => $customer->name,
                            'customer_email' => $customer->email,
                            'customer_phone' => $customer->phone ?? '081234567890',
                            'description'    => "Pengembalian Dana Parsial Audit Pembatalan Bantuan '{$h->title}'",
                            'status'         => 'completed',
                            'processed_at'   => $createdAt->copy()->addMinutes(45),
                            'created_at'     => $createdAt->copy()->addMinutes(45),
                            'updated_at'     => $createdAt->copy()->addMinutes(45),
                        ]
                    );
                }

                if ($h->mitra_id && $mitra && $partnerAmt > 0) {
                    BalanceTransaction::updateOrCreate(
                        [
                            'user_id'      => $h->mitra_id,
                            'reference_id' => (string) $h->id . '_partial_settlement',
                            'type'         => 'earning',
                        ],
                        [
                            'order_id'       => $h->order_id,
                            'reference_type' => 'help',
                            'amount'         => $partnerAmt,
                            'direction'      => 'credit',
                            'customer_name'  => $customer ? $customer->name : 'Customer',
                            'customer_email' => $customer ? $customer->email : '',
                            'customer_phone' => $customer ? $customer->phone : '',
                            'description'    => "Kompensasi Parsial Audit Pembatalan Bantuan '{$h->title}'",
                            'status'         => 'completed',
                            'processed_at'   => $createdAt->copy()->addMinutes(45),
                            'created_at'     => $createdAt->copy()->addMinutes(45),
                            'updated_at'     => $createdAt->copy()->addMinutes(45),
                        ]
                    );
                }
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 3. TRANSAKSI WITHDRAW MITRA SE-INDONESIA (Berbagai Bank Nasional & Daerah)
        // ─────────────────────────────────────────────────────────────────────
        $withdrawPlans = [
            // Sleman & Jogja (Success)
            ['email' => 'mitra.sleman1@sayabantu.com', 'order_id' => 'WD-20260808-SLM01', 'amount' => 75000,  'bank' => 'BCA', 'acc_no' => '1234567890', 'status' => 'success', 'date' => '2026-08-08 17:00:00'],
            ['email' => 'mitra.sleman2@sayabantu.com', 'order_id' => 'WD-20260812-SLM02', 'amount' => 85000,  'bank' => 'MANDIRI', 'acc_no' => '1370012345678', 'status' => 'success', 'date' => '2026-08-12 17:30:00'],
            ['email' => 'mitra.sleman3@sayabantu.com', 'order_id' => 'WD-20260816-SLM03', 'amount' => 100000, 'bank' => 'BNI', 'acc_no' => '0543219876', 'status' => 'success', 'date' => '2026-08-16 17:00:00'],
            ['email' => 'mitra.jogja1@sayabantu.com',  'order_id' => 'WD-20260822-JOG01', 'amount' => 70000,  'bank' => 'BPD DIY', 'acc_no' => '0012345678', 'status' => 'success', 'date' => '2026-08-22 17:00:00'],

            // Surakarta & Sukoharjo (Success)
            ['email' => 'mitra.surakarta1@sayabantu.com', 'order_id' => 'WD-20260809-SKT01', 'amount' => 60000,  'bank' => 'BRI', 'acc_no' => '012301098765504', 'status' => 'success', 'date' => '2026-08-09 17:15:00'],
            ['email' => 'mitra.surakarta2@sayabantu.com', 'order_id' => 'WD-20260814-SKT02', 'amount' => 120000, 'bank' => 'BNI', 'acc_no' => '0987654321', 'status' => 'success', 'date' => '2026-08-14 17:00:00'],
            ['email' => 'mitra.sukoharjo1@sayabantu.com', 'order_id' => 'WD-20260815-SKH01', 'amount' => 100000, 'bank' => 'BCA', 'acc_no' => '7890123456', 'status' => 'success', 'date' => '2026-08-15 17:00:00'],
            ['email' => 'mitra.sukoharjo2@sayabantu.com', 'order_id' => 'WD-20260818-SKH02', 'amount' => 90000,  'bank' => 'Bank Jateng', 'acc_no' => '2034567890', 'status' => 'success', 'date' => '2026-08-18 17:15:00'],

            // Jakarta Selatan (Success, Pending & Rejected)
            ['email' => 'mitra.jaksel1@sayabantu.com', 'order_id' => 'WD-20260820-JKT01', 'amount' => 150000, 'bank' => 'BCA', 'acc_no' => '5210987654', 'status' => 'success', 'date' => '2026-08-20 18:00:00'],
            ['email' => 'mitra.jaksel2@sayabantu.com', 'order_id' => 'WD-20260824-JKT02', 'amount' => 80000,  'bank' => 'MANDIRI', 'acc_no' => '1020009876543', 'status' => 'success', 'date' => '2026-08-24 17:30:00'],
            ['email' => 'mitra.jaksel3@sayabantu.com', 'order_id' => 'WD-20260903-JKT03', 'amount' => 110000, 'bank' => 'BCA', 'acc_no' => '52109998811', 'status' => 'pending', 'date' => '2026-09-03 09:30:00'],
            ['email' => 'mitra.jakbar1@sayabantu.com', 'order_id' => 'WD-20260902-JKB01', 'amount' => 75000,  'bank' => 'CIMB', 'acc_no' => '7001234567', 'status' => 'rejected', 'date' => '2026-09-02 14:00:00'],

            // Bandung, Surabaya, Malang, Bali, Medan, Makassar (Success & Pending)
            ['email' => 'mitra.bandung1@sayabantu.com', 'order_id' => 'WD-20260827-BDG01', 'amount' => 100000, 'bank' => 'BJB', 'acc_no' => '00100998877', 'status' => 'success', 'date' => '2026-08-27 16:45:00'],
            ['email' => 'mitra.surabaya1@sayabantu.com', 'order_id' => 'WD-20260830-SBY01', 'amount' => 120000, 'bank' => 'Bank Jatim', 'acc_no' => '0151234567', 'status' => 'success', 'date' => '2026-08-30 17:00:00'],
            ['email' => 'mitra.denpasar1@sayabantu.com', 'order_id' => 'WD-20260902-DPS01', 'amount' => 150000, 'bank' => 'BPD Bali', 'acc_no' => '10101998877', 'status' => 'success', 'date' => '2026-09-02 18:00:00'],
            ['email' => 'mitra.medan1@sayabantu.com',    'order_id' => 'WD-20260903-MDN01', 'amount' => 50000,  'bank' => 'Bank Sumut', 'acc_no' => '10012345678', 'status' => 'success', 'date' => '2026-09-03 17:45:00'],
            ['email' => 'mitra.makassar1@sayabantu.com', 'order_id' => 'WD-20260904-MKS01', 'amount' => 130000, 'bank' => 'Bank Sulselbar', 'acc_no' => '13000987654', 'status' => 'success', 'date' => '2026-09-04 17:30:00'],
            ['email' => 'mitra.jogja2@sayabantu.com',   'order_id' => 'WD-20260903-JOG02', 'amount' => 65000,  'bank' => 'BPD DIY', 'acc_no' => '0019988776', 'status' => 'pending', 'date' => '2026-09-03 11:15:00'],
        ];

        foreach ($withdrawPlans as $wd) {
            $user = $mitras->get($wd['email']);
            if (!$user) {
                continue;
            }

            $amount = (float) $wd['amount'];
            $date   = Carbon::parse($wd['date']);
            $status = $wd['status'] ?? 'success';

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
                    'account_name'   => $user->name,
                    'status'         => $status,
                    'description'    => $status === 'rejected'
                        ? "Penarikan Saldo Mitra ditolak: Nama pemilik rekening tidak sesuai data KTP."
                        : "Penarikan Saldo Mitra ke Rekening {$wd['bank']} - {$wd['acc_no']} a.n {$user->name}",
                    'processed_at'   => $status === 'pending' ? null : $date,
                    'created_at'     => $date,
                    'updated_at'     => $date,
                ]
            );

            // Hanya buat BalanceTransaction debit jika sukses atau pending (jika pending memotong escrow/holding)
            // Untuk completed/success penarikan mengurangi saldo
            if ($status === 'success') {
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
                        'description'    => "Penarikan Saldo Mitra ke Rekening {$wd['bank']} - {$wd['acc_no']} a.n {$user->name}",
                        'status'         => 'completed',
                        'processed_at'   => $date,
                        'created_at'     => $date,
                        'updated_at'     => $date,
                    ]
                );
            }
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

        $this->command->info('UserBalancesSeeder berhasil membuat transaksi keuangan se-Indonesia & menyinkronkan saldo seluruh akun.');
    }
}