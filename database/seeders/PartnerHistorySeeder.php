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
     * Tersebar di Sleman, Yogyakarta, Surakarta, dan Sukoharjo sepanjang rentang 4 Agustus s/d 3 September 2026.
     * Tanpa ada akun yang terkena ban atau blokir (seluruh akun tetap active).
     */
    public function run(): void
    {
        // Admins
        $admins = User::where('role', 'admin')->get()->keyBy('email');
        $adminSleman = $admins->get('admin.sleman@sayabantu.com') ?? User::where('role', 'admin')->first();
        $adminSolo   = $admins->get('admin.surakarta@sayabantu.com') ?? $adminSleman;
        $adminJaksel = $admins->get('admin.jaksel@sayabantu.com') ?? $adminSleman;
        $adminBdg    = $admins->get('admin.bandung@sayabantu.com') ?? $adminSleman;
        $adminSby    = $admins->get('admin.surabaya@sayabantu.com') ?? $adminSleman;

        // Users
        $users = User::all()->keyBy('email');

        // ─────────────────────────────────────────────────────────────────────
        // 1. RIWAYAT LAPORAN PENGADUAN (RESOLVED 100%)
        // Jalur Investigasi Terpisah:
        // - Jalur Pelapor (Customer Channel: Admin <-> Customer, recipient_type = 'customer')
        // - Jalur Terlapor (Mitra Channel: Admin <-> Mitra, recipient_type = 'mitra')
        // ─────────────────────────────────────────────────────────────────────
        $reportsData = [
            // Laporan 1: Sleman (18 Agustus 2026)
            [
                'title'         => 'Klarifikasi Estimasi Waktu Kedatangan Mitra Pompa Air',
                'reporter'      => $users->get('customer.sleman1@sayabantu.com'),
                'reported_user' => $users->get('mitra.sleman2@sayabantu.com'),
                'help_order_id' => 'HELP-20260810-SLM02',
                'admin'         => $adminSleman,
                'type'          => 'pelayanan_tidak_sesuai',
                'category'      => 'dari_customer',
                'message'       => 'Mitra belum sampai di lokasi setelah 20 menit dari waktu kesepakatan. Mohon bantuan admin untuk konfirmasi keberadaan mitra.',
                'admin_notes'   => 'Admin telah memfasilitasi komunikasi bilateral. Mitra mengalami kendala ban bocor di Jl. Kaliurang dan telah tiba di lokasi menyelesaikan tugas.',
                'created_at'    => '2026-08-18 13:00:00',
                'resolved_at'   => '2026-08-18 15:30:00',
                'messages'      => [
                    ['sender' => $users->get('customer.sleman1@sayabantu.com'), 'recipient_type' => 'customer', 'msg' => 'Halo Admin SayaBantu, apakah bisa dibantu konfirmasi posisi mitra? Belum ada kabar setelah 20 menit dari waktu janji temu.', 'time' => '2026-08-18 13:05:00'],
                    ['sender' => $adminSleman, 'recipient_type' => 'customer', 'msg' => 'Halo Bu Rina, laporan aduan Anda telah kami terima. Kami segera menghubungi Mas Budi untuk verifikasi posisi saat ini.', 'time' => '2026-08-18 13:12:00'],
                    ['sender' => $adminSleman, 'recipient_type' => 'customer', 'msg' => 'Update dari Admin: Mas Budi mengonfirmasi ada kendala ban bocor di Jl. Kaliurang dan saat ini sudah selesai tambal ban, langsung menuju ke lokasi Anda.', 'time' => '2026-08-18 13:25:00'],
                    ['sender' => $users->get('customer.sleman1@sayabantu.com'), 'recipient_type' => 'customer', 'msg' => 'Terima kasih banyak infonya Min, Mas Budi sudah tiba di lokasi dan sedang memperbaiki AC kamar.', 'time' => '2026-08-18 13:40:00'],
                    ['sender' => $adminSleman, 'recipient_type' => 'mitra', 'msg' => 'Halo Mas Budi, ada aduan dari customer Bu Rina terkait keterlambatan 20 menit. Mohon segera berikan konfirmasi status posisi dan kendala Anda saat ini.', 'time' => '2026-08-18 13:08:00'],
                    ['sender' => $users->get('mitra.sleman2@sayabantu.com'), 'recipient_type' => 'mitra', 'msg' => 'Mohon maaf Admin SayaBantu, motor saya mengalami ban bocor mendadak di dekat Ringroad Kaliurang. Ini baru selesai tambal dan langsung saya gas ke lokasi customer.', 'time' => '2026-08-18 13:20:00'],
                    ['sender' => $adminSleman, 'recipient_type' => 'mitra', 'msg' => 'Baik Mas Budi, info telah kami teruskan ke customer. Tetap utamakan keselamatan dan kabari kami saat pekerjaan selesai.', 'time' => '2026-08-18 13:22:00'],
                    ['sender' => $users->get('mitra.sleman2@sayabantu.com'), 'recipient_type' => 'mitra', 'msg' => 'Lapor Admin, pekerjaan AC telah selesai dengan baik dan sudah diuji coba bersama customer.', 'time' => '2026-08-18 15:15:00'],
                ],
            ],

            // Laporan 2: Solo (20 Agustus 2026)
            [
                'title'         => 'Konsultasi Lapisan Cat Tembok Kamar & Plafon',
                'reporter'      => $users->get('customer.surakarta2@sayabantu.com'),
                'reported_user' => $users->get('mitra.surakarta2@sayabantu.com'),
                'help_order_id' => 'HELP-20260812-SKT02',
                'admin'         => $adminSolo,
                'type'          => 'pelayanan_tidak_sesuai',
                'category'      => 'dari_customer',
                'message'       => 'Ingin memastikan apakah cat tembok sudah diaplikasikan 2 lapis merata pada sudut plafon.',
                'admin_notes'   => 'Mitra Dwi telah mendokumentasikan hasil lapisan kedua kepada admin dan customer sangat puas dengan hasil akhirnya.',
                'created_at'    => '2026-08-20 13:30:00',
                'resolved_at'   => '2026-08-20 16:00:00',
                'messages'      => [
                    ['sender' => $users->get('customer.surakarta2@sayabantu.com'), 'recipient_type' => 'customer', 'msg' => 'Halo Admin Solo, saya ingin memastikan apakah pengecatan plafon sudah diberi 2 lapis agar tidak berbayang?', 'time' => '2026-08-20 13:35:00'],
                    ['sender' => $adminSolo, 'recipient_type' => 'customer', 'msg' => 'Halo Mas Rizky, terima kasih laporannya. Kami segera meminta dokumentasi teknis lapisan cat dari Mas Dwi.', 'time' => '2026-08-20 13:42:00'],
                    ['sender' => $adminSolo, 'recipient_type' => 'customer', 'msg' => 'Update: Mas Dwi telah mengonfirmasi dan melampirkan bukti pengecatan 2 lapis tebal merata.', 'time' => '2026-08-20 14:28:00'],
                    ['sender' => $users->get('customer.surakarta2@sayabantu.com'), 'recipient_type' => 'customer', 'msg' => 'Hasilnya sangat memuaskan dan rapi sekali. Terima kasih bantuan koordinasinya Admin SayaBantu.', 'time' => '2026-08-20 15:55:00'],
                    ['sender' => $adminSolo, 'recipient_type' => 'mitra', 'msg' => 'Halo Mas Dwi, mohon kirimkan konfirmasi dan dokumentasi foto aplikasi lapisan kedua cat pada plafon Mas Rizky.', 'time' => '2026-08-20 13:40:00'],
                    ['sender' => $users->get('mitra.surakarta2@sayabantu.com'), 'recipient_type' => 'mitra', 'msg' => 'Halo Admin Solo, siap sudah saya lapisi cat 2 lapis di semua sudut plafon dan dinding.', 'time' => '2026-08-20 14:15:00'],
                    ['sender' => $adminSolo, 'recipient_type' => 'mitra', 'msg' => 'Dokumentasi telah kami verifikasi dan sesuai standar. Silakan lanjut ke pembersihan area kerja.', 'time' => '2026-08-20 14:25:00'],
                    ['sender' => $users->get('mitra.surakarta2@sayabantu.com'), 'recipient_type' => 'mitra', 'msg' => 'Pekerjaan selesai 100% dan sudah serah terima dengan customer.', 'time' => '2026-08-20 15:50:00'],
                ],
            ],

            // Laporan 3: Jakarta Selatan (22 Agustus 2026)
            [
                'title'         => 'Konfirmasi Pipa Siphon Wastafel Dapur',
                'reporter'      => $users->get('customer.jaksel2@sayabantu.com'),
                'reported_user' => $users->get('mitra.jaksel2@sayabantu.com'),
                'help_order_id' => 'HELP-20260822-JKT02',
                'admin'         => $adminJaksel,
                'type'          => 'pelayanan_tidak_sesuai',
                'category'      => 'dari_customer',
                'message'       => 'Ingin memastikan karet seal pipa siphon terpasang kedap tanpa rembesan air ke bawah kabinet kitchen set.',
                'admin_notes'   => 'Admin Tebet/Kebayoran memfasilitasi pengujian aliran air debit kencang. Pipa siphon terpasang presisi dan kedap air.',
                'created_at'    => '2026-08-22 14:00:00',
                'resolved_at'   => '2026-08-22 15:00:00',
                'messages'      => [
                    ['sender' => $users->get('customer.jaksel2@sayabantu.com'), 'recipient_type' => 'customer', 'msg' => 'Min, tolong pastikan seal tape drat kran dapur dililit tebal agar tidak merembes ya.', 'time' => '2026-08-22 14:05:00'],
                    ['sender' => $adminJaksel, 'recipient_type' => 'customer', 'msg' => 'Baik Pak Kevin, pesan sudah diteruskan ke teknisi Doni.', 'time' => '2026-08-22 14:10:00'],
                    ['sender' => $adminJaksel, 'recipient_type' => 'mitra', 'msg' => 'Pak Doni, pastikan tes alirkan air 5 menit untuk memastikan tidak ada tetesan di bawah kabinet dapur.', 'time' => '2026-08-22 14:12:00'],
                    ['sender' => $users->get('mitra.jaksel2@sayabantu.com'), 'recipient_type' => 'mitra', 'msg' => 'Siap Admin, sudah diuji coba air kencang dan bawah wastafel kering sempurna.', 'time' => '2026-08-22 14:40:00'],
                    ['sender' => $users->get('customer.jaksel2@sayabantu.com'), 'recipient_type' => 'customer', 'msg' => 'Pemasangan mantap dan tidak ada bocor sama sekali. Terima kasih!', 'time' => '2026-08-22 14:55:00'],
                ],
            ],
        ];

        foreach ($reportsData as $r) {
            $reporter = $r['reporter'];
            $reported = $r['reported_user'];
            $admin    = $r['admin'];

            if (!$reporter || !$reported) {
                continue;
            }

            $help = Help::where('order_id', $r['help_order_id'])->first();

            $report = PartnerReport::updateOrCreate(
                [
                    'title'            => $r['title'],
                    'reporter_id'      => $reporter->id,
                    'reported_user_id' => $reported->id,
                ],
                [
                    'help_id'          => $help ? $help->id : null,
                    'resolved_by'      => $admin ? $admin->id : null,
                    'report_type'      => $r['type'],
                    'category'         => $r['category'],
                    'message'          => $r['message'],
                    'status'           => 'resolved',
                    'admin_notes'      => $r['admin_notes'],
                    'resolved_at'      => Carbon::parse($r['resolved_at']),
                    'created_at'       => Carbon::parse($r['created_at']),
                    'updated_at'       => Carbon::parse($r['resolved_at']),
                ]
            );

            // Bersihkan pesan lama agar rapi
            PartnerReportMessage::where('partner_report_id', $report->id)->delete();

            foreach ($r['messages'] as $m) {
                $sender = $m['sender'];
                if (!$sender) {
                    continue;
                }

                PartnerReportMessage::create([
                    'partner_report_id' => $report->id,
                    'sender_id'         => $sender->id,
                    'recipient_type'    => $m['recipient_type'] ?? 'customer',
                    'message'           => $m['msg'],
                    'is_read'           => true,
                    'read_at'           => Carbon::parse($m['time']),
                    'created_at'        => Carbon::parse($m['time']),
                    'updated_at'        => Carbon::parse($m['time']),
                ]);
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 2. RIWAYAT DAFTAR ABU-ABU / GREYLIST LOGS (EVALUASI TANPA BLOKIR)
        // ─────────────────────────────────────────────────────────────────────
        $greylistRecords = [
            // Sleman (Agus Prasetyo)
            [
                'email'         => 'mitra.sleman1@sayabantu.com',
                'admin'         => $adminSleman,
                'warning_level' => 1,
                'reason'        => 'Keterlambatan konfirmasi kehadiran pesanan akibat kendala sinyal seluler.',
                'action_taken'  => 'Pemberian SP1 & Peringatan Ringan untuk meningkatkan keaktifan GPS.',
                'logged_at'     => '2026-08-08 14:00:00',
            ],
            // Sleman (Budi Santoso)
            [
                'email'         => 'mitra.sleman2@sayabantu.com',
                'admin'         => $adminSleman,
                'warning_level' => 1,
                'reason'        => 'Klarifikasi estimasi waktu perjalanan akibat penambalan ban darurat.',
                'action_taken'  => 'Verifikasi log perjalanan dan edukasi fitur komunikasi darurat aplikasi.',
                'logged_at'     => '2026-08-18 16:00:00',
            ],
            // Surakarta (Eko Saputra)
            [
                'email'         => 'mitra.surakarta1@sayabantu.com',
                'admin'         => $adminSolo,
                'warning_level' => 1,
                'reason'        => 'Penundaan pembatalan awal sebelum penugasan diambil.',
                'action_taken'  => 'Konseling standar operasional & refresh pelatihan mitra.',
                'logged_at'     => '2026-08-12 11:30:00',
            ],
            // Jakarta Selatan (Fahmi Ramadhan)
            [
                'email'         => 'mitra.jaksel1@sayabantu.com',
                'admin'         => $adminJaksel,
                'warning_level' => 1,
                'reason'        => 'Pengecekan sertifikat freon dan kelengkapan manifold gauge AC.',
                'action_taken'  => 'Verifikasi sertifikasi teknisi pendingin udara oleh Admin Jakarta Selatan.',
                'logged_at'     => '2026-08-19 15:00:00',
            ],
            // Bandung (Asep Sunandar)
            [
                'email'         => 'mitra.bandung1@sayabantu.com',
                'admin'         => $adminBdg,
                'warning_level' => 1,
                'reason'        => 'Evaluasi waktu perakitan mebel knockdown lemari 3 pintu.',
                'action_taken'  => 'Edukasi panduan buku instruksi dan checklist alat perkakas bor baterai.',
                'logged_at'     => '2026-08-26 14:30:00',
            ],
        ];

        foreach ($greylistRecords as $g) {
            $user  = $users->get($g['email']);
            $admin = $g['admin'];

            if (!$user) {
                continue;
            }

            $date = Carbon::parse($g['logged_at']);

            UserGreylistLog::updateOrCreate(
                [
                    'user_id'       => $user->id,
                    'warning_level' => $g['warning_level'],
                    'reason'        => $g['reason'],
                ],
                [
                    'admin_id'      => $admin ? $admin->id : null,
                    'action'        => 'warning_issued',
                    'message'       => $g['action_taken'],
                    'created_at'    => $date,
                    'updated_at'    => $date,
                ]
            );
        }

        // Pastikan seluruh user tetap aktif (tidak terkena ban/blokir)
        User::query()->update([
            'is_shadow_banned' => false,
            'is_greylisted'    => false,
            'status'           => 'active',
            'warning_level'    => 0,
        ]);

        $this->command->info('PartnerHistorySeeder berhasil membuat riwayat laporan resolved & log evaluasi greylist se-Indonesia.');
    }
}
