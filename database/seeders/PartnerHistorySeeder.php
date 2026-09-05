<?php

namespace Database\Seeders;

use App\Models\Help;
use App\Models\PartnerReport;
use App\Models\PartnerReportMessage;
use App\Models\User;
use App\Models\UserGreylistLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PartnerHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi riwayat Laporan Pengaduan Selesai (Resolved) & Log Riwayat Evaluasi/SP (Greylist History)
     * Tersebar di Sleman & Surakarta sepanjang rentang 4 Agustus s/d 3 September 2026.
     * Tanpa ada akun yang terkena ban atau blokir (seluruh akun tetap active).
     */
    public function run(): void
    {
        // Admin
        $adminSleman = User::whereIn('email', ['admin.sleman@sayabantu.com', 'admin@sayabantu.com'])->first();
        $adminSolo   = User::where('email', 'admin.surakarta@sayabantu.com')->first();

        // Sleman Users
        $custSleman1  = User::where('email', 'customer.sleman1@sayabantu.com')->first() ?? User::where('email', 'customer@sayabantu.com')->first();
        $custSleman2  = User::where('email', 'customer.sleman2@sayabantu.com')->first();
        $mitraSleman1 = User::where('email', 'mitra.sleman1@sayabantu.com')->first() ?? User::where('email', 'mitra@sayabantu.com')->first();
        $mitraSleman2 = User::where('email', 'mitra.sleman2@sayabantu.com')->first();

        // Surakarta Users
        $custSolo1  = User::where('email', 'customer.solo1@sayabantu.com')->first();
        $custSolo2  = User::where('email', 'customer.solo2@sayabantu.com')->first();
        $mitraSolo1 = User::where('email', 'mitra.solo1@sayabantu.com')->first();
        $mitraSolo2 = User::where('email', 'mitra.solo2@sayabantu.com')->first();

        // ─────────────────────────────────────────────────────────────────────
        // 1. RIWAYAT LAPORAN PENGADUAN (RESOLVED 100%)
        // ─────────────────────────────────────────────────────────────────────
        $reportsData = [
            // Laporan 1: Sleman (18 Agustus 2026)
            [
                'title'         => 'Klarifikasi Estimasi Waktu Kedatangan Mitra Pompa Air',
                'reporter'      => $custSleman1,
                'reported_user' => $mitraSleman2,
                'help_order_id' => 'HELP-20260818-SLM08',
                'admin'         => $adminSleman,
                'type'          => 'pelayanan_tidak_sesuai',
                'category'      => 'dari_customer',
                'message'       => 'Mitra belum sampai di lokasi setelah 20 menit dari waktu kesepakatan. Mohon bantuan admin untuk konfirmasi keberadaan mitra.',
                'admin_notes'   => 'Admin telah memfasilitasi komunikasi. Mitra mengalami kendala ban bocor di Jl. Kaliurang dan sudah tiba di lokasi menyelesaikan tugas.',
                'created_at'    => '2026-08-18 13:00:00',
                'resolved_at'   => '2026-08-18 15:30:00',
                'messages'      => [
                    ['sender' => $custSleman1,  'msg' => 'Halo Admin, apakah nomor mitra bisa dihubungi? Karena belum ada konfirmasi.', 'time' => '2026-08-18 13:05:00'],
                    ['sender' => $adminSleman,  'msg' => 'Halo Bu Rina, kami sudah hubungi Mas Budi. Beliau sedang tambal ban dekat Jl. Kaliurang dan langsung menuju lokasi sekarang.', 'time' => '2026-08-18 13:20:00'],
                    ['sender' => $mitraSleman2, 'msg' => 'Mohon maaf Bu Rina dan Admin, saya sudah sampai di lokasi dan langsung memperbaiki pipa serta pompa.', 'time' => '2026-08-18 13:35:00'],
                ],
            ],
            // Laporan 2: Solo (21 Agustus 2026)
            [
                'title'         => 'Konsultasi Lapisan Cat Primer Anti Karat Pagar Depan',
                'reporter'      => $custSolo1,
                'reported_user' => $mitraSolo2,
                'help_order_id' => 'HELP-20260821-SKT09',
                'admin'         => $adminSolo,
                'type'          => 'pelayanan_tidak_sesuai',
                'category'      => 'dari_customer',
                'message'       => 'Ingin memastikan apakah cat pelapis anti karat sudah diaplikasikan merata pada seluruh sudut sambungan besi pagar sebelum cat finishing.',
                'admin_notes'   => 'Mitra Hendra telah mendokumentasikan lapisan primer anti karat dan customer sangat puas dengan hasil akhirnya.',
                'created_at'    => '2026-08-21 09:00:00',
                'resolved_at'   => '2026-08-21 14:00:00',
                'messages'      => [
                    ['sender' => $custSolo1,   'msg' => 'Halo Admin, mohon bantuan cek apakah amplas dan cat dasarnya sudah sesuai spek.', 'time' => '2026-08-21 09:15:00'],
                    ['sender' => $mitraSolo2,  'msg' => 'Halo Bu Dewi, sudah saya lapisi zinkromat 2 lapis dan sudah saya fotokan ke admin.', 'time' => '2026-08-21 10:00:00'],
                    ['sender' => $adminSolo,   'msg' => 'Laporan telah diverifikasi oleh Admin Solo. Dokumentasi lengkap dan standar kualitas terpenuhi.', 'time' => '2026-08-21 14:00:00'],
                ],
            ],
            // Laporan 3: Sleman (28 Agustus 2026)
            [
                'title'         => 'Konfirmasi Penataan Pot Gerabah Tanaman Hias',
                'reporter'      => $custSleman2,
                'reported_user' => $mitraSleman1,
                'help_order_id' => 'HELP-20260828-SLM13',
                'admin'         => $adminSleman,
                'type'          => 'pelayanan_tidak_sesuai',
                'category'      => 'dari_customer',
                'message'       => 'Meminta saran mitra untuk penataan sudut pot tanaman palem agar tidak menghalangi aliran air hujan.',
                'admin_notes'   => 'Mitra Agus telah memberikan ganjal tatakan pot gerabah dan air mengalir lancar ke saluran drainase.',
                'created_at'    => '2026-08-28 09:00:00',
                'resolved_at'   => '2026-08-28 10:30:00',
                'messages'      => [
                    ['sender' => $custSleman2,  'msg' => 'Mas Agus, apakah pot besar perlu diberi tatakan bata ringan?', 'time' => '2026-08-28 09:10:00'],
                    ['sender' => $mitraSleman1, 'msg' => 'Sudah saya pasangkan tatakan karet anti licin Bu, aman dan air lancar.', 'time' => '2026-08-28 09:40:00'],
                    ['sender' => $adminSleman,  'msg' => 'Admin menutup tiket aduan dengan status selesai dan sepakat.', 'time' => '2026-08-28 10:30:00'],
                ],
            ],
            // Laporan 4: Solo (31 Agustus 2026)
            [
                'title'         => 'Klarifikasi Pengeringan Karpet Permadani Ruang Tamu',
                'reporter'      => $custSolo2,
                'reported_user' => $mitraSolo1,
                'help_order_id' => 'HELP-20260831-SKT14',
                'admin'         => $adminSolo,
                'type'          => 'pelayanan_tidak_sesuai',
                'category'      => 'dari_customer',
                'message'       => 'Meminta mitra memastikan karpet sudah benar-benar kering sebelum dipasang kembali ke ruang tamu.',
                'admin_notes'   => 'Mitra Eko menggunakan blower pengering tambahan dan karpet kering sempurna tanpa bau apek.',
                'created_at'    => '2026-08-31 11:00:00',
                'resolved_at'   => '2026-08-31 12:30:00',
                'messages'      => [
                    ['sender' => $custSolo2,  'msg' => 'Pak Eko, tolong dipastikan serat dalamnya tidak lembab ya.', 'time' => '2026-08-31 11:15:00'],
                    ['sender' => $mitraSolo1, 'msg' => 'Sudah kering 100% Mbak, sudah saya tes dengan tisu kering.', 'time' => '2026-08-31 12:00:00'],
                    ['sender' => $adminSolo,  'msg' => 'Verifikasi selesai, laporan ditutup.', 'time' => '2026-08-31 12:30:00'],
                ],
            ],
        ];

        foreach ($reportsData as $r) {
            $reporter     = $r['reporter'];
            $reportedUser = $r['reported_user'];
            $admin        = $r['admin'];

            if (!$reporter || !$reportedUser || !$admin) {
                continue;
            }

            $help = Help::where('order_id', $r['help_order_id'])->first();

            $report = PartnerReport::updateOrCreate(
                [
                    'title'       => $r['title'],
                    'reporter_id' => $reporter->id,
                ],
                [
                    'reported_user_id'   => $reportedUser->id,
                    'help_id'            => $help ? $help->id : null,
                    'reported_help_id'   => $help ? $help->id : null,
                    'reported_help_text' => $help ? $help->title : $r['title'],
                    'reported_user_text' => $reportedUser->name . ' (' . ucfirst($reportedUser->role) . ')',
                    'report_type'        => $r['type'],
                    'category'           => $r['category'],
                    'message'            => $r['message'],
                    'status'             => 'resolved',
                    'refund_status'      => 'none',
                    'refund_amount'      => 0,
                    'admin_notes'        => $r['admin_notes'],
                    'resolved_at'        => Carbon::parse($r['resolved_at']),
                    'resolved_by'        => $admin->id,
                    'created_at'         => Carbon::parse($r['created_at']),
                    'updated_at'         => Carbon::parse($r['resolved_at']),
                ]
            );

            foreach ($r['messages'] as $m) {
                PartnerReportMessage::updateOrCreate(
                    [
                        'partner_report_id' => $report->id,
                        'sender_id'         => $m['sender']->id,
                        'message'           => $m['msg'],
                    ],
                    [
                        'recipient_type' => 'all',
                        'is_read'        => true,
                        'read_at'        => Carbon::parse($m['time']),
                        'created_at'     => Carbon::parse($m['time']),
                        'updated_at'     => Carbon::parse($m['time']),
                    ]
                );
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 2. RIWAYAT EVALUASI / DAFTAR ABU-ABU & SP (PULIH NORMAL / TIDAK BANNED)
        // ─────────────────────────────────────────────────────────────────────
        $greylistLogs = [
            // Mitra Sleman 2 (Budi Santoso): Evaluasi keterlambatan & Pemulihan
            [
                'user'          => $mitraSleman2,
                'admin'         => $adminSleman,
                'action'        => 'warning_issued',
                'warning_level' => 1,
                'reason'        => 'Terlambat konfirmasi kehadiran pada pekerjaan pembersihan halaman.',
                'message'       => 'Diberikan Surat Peringatan ke-1 (SP 1) sebagai evaluasi ketepatan waktu.',
                'date'          => '2026-08-12 10:00:00',
            ],
            [
                'user'          => $mitraSleman2,
                'admin'         => $adminSleman,
                'action'        => 'greylist_remove',
                'warning_level' => 0,
                'reason'        => 'Performa pekerjaan berikutnya sangat baik, rating bintang 5 berturut-turut, evaluasi tuntas.',
                'message'       => 'Status mitra dipulihkan normal (warning level 0).',
                'date'          => '2026-08-26 14:00:00',
            ],

            // Mitra Surakarta 2 (Hendra Wijaya): Audit kelengkapan alat & Pemulihan
            [
                'user'          => $mitraSolo2,
                'admin'         => $adminSolo,
                'action'        => 'greylist_add',
                'warning_level' => 1,
                'reason'        => 'Audit berkala verifikasi kelengkapan peralatan kerja dan SOP keselamatan pengecatan.',
                'message'       => 'Akun dimasukkan ke daftar peninjauan berkala Admin.',
                'date'          => '2026-08-15 11:00:00',
            ],
            [
                'user'          => $mitraSolo2,
                'admin'         => $adminSolo,
                'action'        => 'greylist_remove',
                'warning_level' => 0,
                'reason'        => 'Verifikasi sertifikasi alat dan kepatuhan SOP selesai dan valid.',
                'message'       => 'Akun dipulihkan ke status normal aktif.',
                'date'          => '2026-08-23 15:30:00',
            ],

            // Mitra Surakarta 1 (Eko Saputra): Audit berkala & Pemulihan
            [
                'user'          => $mitraSolo1,
                'admin'         => $adminSolo,
                'action'        => 'greylist_add',
                'warning_level' => 1,
                'reason'        => 'Pembaruan data masa berlaku STNK dan SIM motor dinas operasional.',
                'message'       => 'Diminta melampirkan foto dokumen perpanjangan STNK.',
                'date'          => '2026-08-17 14:00:00',
            ],
            [
                'user'          => $mitraSolo1,
                'admin'         => $adminSolo,
                'action'        => 'greylist_remove',
                'warning_level' => 0,
                'reason'        => 'Dokumen STNK dan SIM telah diverifikasi valid oleh Admin.',
                'message'       => 'Audit berkala selesai, status dipulihkan aktif.',
                'date'          => '2026-08-25 10:00:00',
            ],
        ];

        foreach ($greylistLogs as $g) {
            $user  = $g['user'];
            $admin = $g['admin'];

            if (!$user || !$admin) {
                continue;
            }

            $logDate = Carbon::parse($g['date']);

            UserGreylistLog::updateOrCreate(
                [
                    'user_id'    => $user->id,
                    'action'     => $g['action'],
                    'created_at' => $logDate,
                ],
                [
                    'admin_id'      => $admin->id,
                    'warning_level' => $g['warning_level'],
                    'reason'        => $g['reason'],
                    'message'       => $g['message'],
                    'updated_at'    => $logDate,
                ]
            );

            // Pastikan akun tetap berstatus aktif & bersih
            $user->update([
                'status'           => 'active',
                'is_greylisted'    => false,
                'is_shadow_banned' => false,
                'warning_level'    => 0,
            ]);
        }

        $this->command->info('PartnerHistorySeeder berhasil membuat 4 riwayat laporan selesai (resolved) & 6 log evaluasi daftar abu-abu tanpa blokir.');
    }
}
