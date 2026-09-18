<?php

namespace Database\Seeders;

use App\Models\Help;
use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use App\Models\User;
use Illuminate\Database\Seeder;

class PartnerHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi contoh laporan aduan / pusat bantuan (PartnerReport) untuk pengujian sistem moderasi admin.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin.sleman@sayabantu.com')->first() ?? User::where('role', 'admin')->first();
        $customer = User::where('email', 'customer@sayabantu.com')->first();
        $mitra = User::where('email', 'mitra@sayabantu.com')->first();
        $help = Help::first();

        if (!$admin || !$customer || !$mitra) {
            return;
        }

        $now = now();

        // 1. Laporan Selesai (Resolved Report)
        $report1 = PartnerReport::updateOrCreate(
            ['title' => 'Klarifikasi Estimasi Waktu Kedatangan Mitra'],
            [
                'reporter_id'      => $customer->id,
                'reported_user_id' => $mitra->id,
                'help_id'          => $help?->id,
                'report_type'      => 'pelayanan_tidak_sesuai',
                'category'         => 'dari_customer',
                'message'          => 'Mitra belum sampai di lokasi setelah 15 menit dari kesepakatan. Mohon bantuan konfirmasi admin.',
                'status'           => 'resolved',
                'admin_notes'      => 'Admin telah memfasilitasi komunikasi bilateral. Mitra mengalami kendala ban bocor dan telah selesai mengerjakan tugas.',
                'resolved_by'      => $admin->id,
                'resolved_at'      => $now->copy()->subDays(2),
                'created_at'       => $now->copy()->subDays(2)->subHours(2),
            ]
        );

        PartnerReportMessage::firstOrCreate(
            [
                'partner_report_id' => $report1->id,
                'sender_id'         => $customer->id,
                'recipient_type'    => 'customer',
                'message'           => 'Halo Admin SayaBantu, apakah bisa dibantu konfirmasi posisi mitra?',
            ],
            [
                'is_read'    => true,
                'read_at'    => $now->copy()->subDays(2),
                'created_at' => $now->copy()->subDays(2)->subHours(2),
            ]
        );

        PartnerReportMessage::firstOrCreate(
            [
                'partner_report_id' => $report1->id,
                'sender_id'         => $admin->id,
                'recipient_type'    => 'customer',
                'message'           => 'Halo Kak, laporan telah kami terima. Mitra sudah terkonfirmasi segera sampai ke lokasi.',
            ],
            [
                'is_read'    => true,
                'read_at'    => $now->copy()->subDays(2),
                'created_at' => $now->copy()->subDays(2)->subHour(),
            ]
        );

        $this->command->info('✓ PartnerHistorySeeder berhasil mengisi sampel laporan kendala.');
    }
}
