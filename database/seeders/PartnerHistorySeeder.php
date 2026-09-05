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
        // Admin
        $adminSleman = User::whereIn('email', ['admin.sleman@sayabantu.com', 'admin@sayabantu.com'])->first();
        $adminSolo   = User::where('email', 'admin.surakarta@sayabantu.com')->first();

        // Sleman Users
        $custSleman1  = User::where('email', 'customer.sleman1@sayabantu.com')->first();
        $custSleman2  = User::where('email', 'customer.sleman2@sayabantu.com')->first();
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
        // 1. RIWAYAT LAPORAN PENGADUAN (RESOLVED 100%)
        // Jalur Investigasi Terpisah:
        // - Jalur Pelapor (Customer Channel: Admin <-> Customer, recipient_type = 'customer')
        // - Jalur Terlapor (Mitra Channel: Admin <-> Mitra, recipient_type = 'mitra')
        // ─────────────────────────────────────────────────────────────────────
        $reportsData = [
            // Laporan 1: Sleman (18 Agustus 2026)
            [
                'title'         => 'Klarifikasi Estimasi Waktu Kedatangan Mitra Pompa Air',
                'reporter'      => $custSleman1,
                'reported_user' => $mitraSleman2,
                'help_order_id' => 'HELP-20260818-SLM11',
                'admin'         => $adminSleman,
                'type'          => 'pelayanan_tidak_sesuai',
                'category'      => 'dari_customer',
                'message'       => 'Mitra belum sampai di lokasi setelah 20 menit dari waktu kesepakatan. Mohon bantuan admin untuk konfirmasi keberadaan mitra.',
                'admin_notes'   => 'Admin telah memfasilitasi komunikasi bilateral. Mitra mengalami kendala ban bocor di Jl. Kaliurang dan telah tiba di lokasi menyelesaikan tugas.',
                'created_at'    => '2026-08-18 13:00:00',
                'resolved_at'   => '2026-08-18 15:30:00',
                'messages'      => [
                    // Jalur Pelapor (Admin <-> Customer)
                    [
                        'sender'         => $custSleman1,
                        'recipient_type' => 'customer',
                        'msg'            => 'Halo Admin SayaBantu, apakah bisa dibantu konfirmasi posisi mitra? Belum ada kabar setelah 20 menit dari waktu janji temu.',
                        'time'           => '2026-08-18 13:05:00',
                    ],
                    [
                        'sender'         => $adminSleman,
                        'recipient_type' => 'customer',
                        'msg'            => 'Halo Bu Rina, laporan aduan Anda telah kami terima. Kami segera menghubungi Mas Budi untuk verifikasi posisi saat ini.',
                        'time'           => '2026-08-18 13:12:00',
                    ],
                    [
                        'sender'         => $adminSleman,
                        'recipient_type' => 'customer',
                        'msg'            => 'Update dari Admin: Mas Budi mengonfirmasi ada kendala ban bocor di Jl. Kaliurang dan saat ini sudah selesai tambal ban, langsung menuju ke lokasi Anda.',
                        'time'           => '2026-08-18 13:25:00',
                    ],
                    [
                        'sender'         => $custSleman1,
                        'recipient_type' => 'customer',
                        'msg'            => 'Terima kasih banyak infonya Min, Mas Budi sudah tiba di lokasi dan sedang memperbaiki shower pompa air.',
                        'time'           => '2026-08-18 13:40:00',
                    ],

                    // Jalur Terlapor (Admin <-> Mitra)
                    [
                        'sender'         => $adminSleman,
                        'recipient_type' => 'mitra',
                        'msg'            => 'Halo Mas Budi, ada aduan dari customer Bu Rina terkait keterlambatan 20 menit. Mohon segera berikan konfirmasi status posisi dan kendala Anda saat ini.',
                        'time'           => '2026-08-18 13:08:00',
                    ],
                    [
                        'sender'         => $mitraSleman2,
                        'recipient_type' => 'mitra',
                        'msg'            => 'Mohon maaf Admin SayaBantu, motor saya mengalami ban bocor mendadak di dekat Ringroad Kaliurang. Ini baru selesai tambal dan langsung saya gas ke lokasi customer.',
                        'time'           => '2026-08-18 13:20:00',
                    ],
                    [
                        'sender'         => $adminSleman,
                        'recipient_type' => 'mitra',
                        'msg'            => 'Baik Mas Budi, info telah kami teruskan ke customer. Tetap utamakan keselamatan dan kabari kami saat pekerjaan selesai.',
                        'time'           => '2026-08-18 13:22:00',
                    ],
                    [
                        'sender'         => $mitraSleman2,
                        'recipient_type' => 'mitra',
                        'msg'            => 'Lapor Admin, pekerjaan pompa air telah selesai dengan baik dan sudah diuji coba bersama customer.',
                        'time'           => '2026-08-18 15:15:00',
                    ],
                ],
            ],

            // Laporan 2: Solo (20 Agustus 2026)
            [
                'title'         => 'Konsultasi Lapisan Cat Primer Anti Karat Pagar Depan',
                'reporter'      => $custSolo1,
                'reported_user' => $mitraSolo2,
                'help_order_id' => 'HELP-20260820-SKT09',
                'admin'         => $adminSolo,
                'type'          => 'pelayanan_tidak_sesuai',
                'category'      => 'dari_customer',
                'message'       => 'Ingin memastikan apakah cat pelapis anti karat sudah diaplikasikan merata pada seluruh sudut sambungan besi pagar sebelum cat finishing.',
                'admin_notes'   => 'Mitra Hendra telah mendokumentasikan lapisan primer anti karat kepada admin dan customer sangat puas dengan hasil akhirnya.',
                'created_at'    => '2026-08-20 13:30:00',
                'resolved_at'   => '2026-08-20 16:00:00',
                'messages'      => [
                    // Jalur Pelapor (Admin <-> Customer)
                    [
                        'sender'         => $custSolo1,
                        'recipient_type' => 'customer',
                        'msg'            => 'Halo Admin Solo, saya ingin memastikan apakah pengecatan pagar besi depan sudah diberi lapisan cat primer anti karat sebelum cat warna utama?',
                        'time'           => '2026-08-20 13:35:00',
                    ],
                    [
                        'sender'         => $adminSolo,
                        'recipient_type' => 'customer',
                        'msg'            => 'Halo Bu Dewi, terima kasih laporannya. Kami segera meminta dokumentasi teknis lapisan primer dari Mas Hendra.',
                        'time'           => '2026-08-20 13:42:00',
                    ],
                    [
                        'sender'         => $adminSolo,
                        'recipient_type' => 'customer',
                        'msg'            => 'Update: Mas Hendra telah mengonfirmasi dan melampirkan bukti aplikasi zinkromat 2 lapis pada seluruh sambungan las.',
                        'time'           => '2026-08-20 14:28:00',
                    ],
                    [
                        'sender'         => $custSolo1,
                        'recipient_type' => 'customer',
                        'msg'            => 'Hasilnya sangat memuaskan dan rapi sekali. Terima kasih bantuan koordinasinya Admin SayaBantu.',
                        'time'           => '2026-08-20 15:55:00',
                    ],

                    // Jalur Terlapor (Admin <-> Mitra)
                    [
                        'sender'         => $adminSolo,
                        'recipient_type' => 'mitra',
                        'msg'            => 'Halo Mas Hendra, mohon kirimkan konfirmasi dan dokumentasi foto aplikasi lapisan primer zinkromat pada pagar Bu Dewi sebelum lanjut pengecatan warna.',
                        'time'           => '2026-08-20 13:40:00',
                    ],
                    [
                        'sender'         => $mitraSolo2,
                        'recipient_type' => 'mitra',
                        'msg'            => 'Halo Admin Solo, siap sudah saya lapisi primer zinkromat 2 lapis di semua sudut las dan pagar. Sudah saya dokumentasikan sesuai SOP.',
                        'time'           => '2026-08-20 14:15:00',
                    ],
                    [
                        'sender'         => $adminSolo,
                        'recipient_type' => 'mitra',
                        'msg'            => 'Dokumentasi telah kami verifikasi dan sesuai standar. Silakan lanjut ke finishing cat hitam doff.',
                        'time'           => '2026-08-20 14:25:00',
                    ],
                    [
                        'sender'         => $mitraSolo2,
                        'recipient_type' => 'mitra',
                        'msg'            => 'Pengecatan finishing selesai 100% dan sudah serah terima dengan customer.',
                        'time'           => '2026-08-20 15:50:00',
                    ],
                ],
            ],

            // Laporan 3: Sukoharjo (23 Agustus 2026)
            [
                'title'         => 'Konfirmasi Jarak Titik Bor Ambalan Rak Dinding Farmasi',
                'reporter'      => $custSkh2,
                'reported_user' => $mitraSkh1,
                'help_order_id' => 'HELP-20260823-SKH10',
                'admin'         => $adminSolo,
                'type'          => 'pelayanan_tidak_sesuai',
                'category'      => 'dari_customer',
                'message'       => 'Ingin memastikan ulang ketinggian ambalan buku agar tidak membentur kotak P3K dinding.',
                'admin_notes'   => 'Admin memfasilitasi koordinasi denah dinding. Mitra Tri telah menggeser posisi ambalan 10 cm lebih tinggi dan hasil sangat rapi.',
                'created_at'    => '2026-08-23 09:30:00',
                'resolved_at'   => '2026-08-23 11:15:00',
                'messages'      => [
                    // Jalur Pelapor (Admin <-> Customer)
                    [
                        'sender'         => $custSkh2,
                        'recipient_type' => 'customer',
                        'msg'            => 'Halo Admin, mohon bantuan koordinasi ke teknisi di tempat agar titik bor ambalan rak farmasi dinaikkan sedikit agar tidak mepet kotak P3K dinding.',
                        'time'           => '2026-08-23 09:35:00',
                    ],
                    [
                        'sender'         => $adminSolo,
                        'recipient_type' => 'customer',
                        'msg'            => 'Halo Pak Bayu, pesan penyesuaian telah kami sampaikan langsung ke Mas Tri untuk menaikkan posisi bor.',
                        'time'           => '2026-08-23 09:42:00',
                    ],
                    [
                        'sender'         => $custSkh2,
                        'recipient_type' => 'customer',
                        'msg'            => 'Posisi ambalan sudah disesuaikan dan sangat presisi. Terima kasih atas respon cepat admin.',
                        'time'           => '2026-08-23 10:45:00',
                    ],

                    // Jalur Terlapor (Admin <-> Mitra)
                    [
                        'sender'         => $adminSolo,
                        'recipient_type' => 'mitra',
                        'msg'            => 'Mas Tri, customer Pak Bayu meminta ketinggian titik bor ambalan dinaikkan sekitar 10 cm agar tidak membentur kotak obat. Mohon disesuaikan ya.',
                        'time'           => '2026-08-23 09:38:00',
                    ],
                    [
                        'sender'         => $mitraSkh1,
                        'recipient_type' => 'mitra',
                        'msg'            => 'Siap Min, titik bor sudah saya ukur ulang dengan waterpass dan dinaikkan 10 cm sesuai permintaan customer.',
                        'time'           => '2026-08-23 09:50:00',
                    ],
                    [
                        'sender'         => $adminSolo,
                        'recipient_type' => 'mitra',
                        'msg'            => 'Bagus Mas Tri, pastikan dynabolt terpasang kokoh dan bersihkan serbuk bor sebelum selesai.',
                        'time'           => '2026-08-23 09:55:00',
                    ],
                    [
                        'sender'         => $mitraSkh1,
                        'recipient_type' => 'mitra',
                        'msg'            => 'Ambalan sudah terpasang kokoh dan area kerja sudah bersih. Pekerjaan selesai.',
                        'time'           => '2026-08-23 10:40:00',
                    ],
                ],
            ],

            // Laporan 4: Yogyakarta (25 Agustus 2026)
            [
                'title'         => 'Konfirmasi Kunci Gembok Tambahan Pintu Gerbang Butik',
                'reporter'      => $custJogja1,
                'reported_user' => $mitraJogja1,
                'help_order_id' => 'HELP-20260825-JOG07',
                'admin'         => $adminSleman,
                'type'          => 'pelayanan_tidak_sesuai',
                'category'      => 'dari_customer',
                'message'       => 'Memastikan ketebalan plat las kupingan gembok tahan cuaca hujan di pintu gerbang toko.',
                'admin_notes'   => 'Mitra Danang melampirkan foto hasil las dobel dan pengecatan anti karat kepada admin. Aduan diselesaikan.',
                'created_at'    => '2026-08-25 14:15:00',
                'resolved_at'   => '2026-08-25 15:30:00',
                'messages'      => [
                    // Jalur Pelapor (Admin <-> Customer)
                    [
                        'sender'         => $custJogja1,
                        'recipient_type' => 'customer',
                        'msg'            => 'Siang Admin, ingin konfirmasi apakah plat las kupingan gembok gerbang butik sudah diberi pelapis anti-karat agar tidak mudah korosi kena hujan?',
                        'time'           => '2026-08-25 14:20:00',
                    ],
                    [
                        'sender'         => $adminSleman,
                        'recipient_type' => 'customer',
                        'msg'            => 'Siang Mbak Nia, kami akan minta Mas Danang mengonfirmasi spesifikasi pelapisan dan pengelasan plat kupingan tersebut.',
                        'time'           => '2026-08-25 14:26:00',
                    ],
                    [
                        'sender'         => $adminSleman,
                        'recipient_type' => 'customer',
                        'msg'            => 'Mas Danang telah mengonfirmasi bahwa plat besi tebal 4mm telah dilas dobel luar-dalam dan disemprot primer anti-karat tahan air.',
                        'time'           => '2026-08-25 14:52:00',
                    ],
                    [
                        'sender'         => $custJogja1,
                        'recipient_type' => 'customer',
                        'msg'            => 'Mantap min, sudah saya coba kuncian gemboknya kokoh sekali. Terima kasih banyak.',
                        'time'           => '2026-08-25 15:25:00',
                    ],

                    // Jalur Terlapor (Admin <-> Mitra)
                    [
                        'sender'         => $adminSleman,
                        'recipient_type' => 'mitra',
                        'msg'            => 'Halo Mas Danang, mohon konfirmasi ketebalan plat dan lapisan anti-karat untuk kupingan gembok di lokasi Mbak Nia.',
                        'time'           => '2026-08-25 14:22:00',
                    ],
                    [
                        'sender'         => $mitraJogja1,
                        'recipient_type' => 'mitra',
                        'msg'            => 'Halo Admin, plat tebal 4mm sudah saya las penuh dobel luar-dalam dan sudah disemprot primer anti-karat hitam tahan hujan.',
                        'time'           => '2026-08-25 14:45:00',
                    ],
                    [
                        'sender'         => $adminSleman,
                        'recipient_type' => 'mitra',
                        'msg'            => 'Sip Mas Danang, pastikan tes buka-tutup gembok lancar sebelum serah terima kunci.',
                        'time'           => '2026-08-25 14:50:00',
                    ],
                    [
                        'sender'         => $mitraJogja1,
                        'recipient_type' => 'mitra',
                        'msg'            => 'Sudah dicoba dengan gembok bawaan customer dan sangat lancar. Pemasangan tuntas 100%.',
                        'time'           => '2026-08-25 15:20:00',
                    ],
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

            // Bersihkan pesan lama agar tidak ada residu recipient_type = 'all'
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
            // Mitra 1 Sleman (Agus Prasetyo)
            [
                'user'          => $mitraSleman1,
                'admin'         => $adminSleman,
                'warning_level' => 1,
                'reason'        => 'Keterlambatan konfirmasi kehadiran pesanan akibat kendala sinyal seluler.',
                'action_taken'  => 'Pemberian SP1 & Peringatan Ringan untuk meningkatkan keaktifan GPS.',
                'logged_at'     => '2026-08-08 14:00:00',
            ],
            // Mitra 2 Sleman (Budi Santoso)
            [
                'user'          => $mitraSleman2,
                'admin'         => $adminSleman,
                'warning_level' => 1,
                'reason'        => 'Klarifikasi estimasi waktu perjalanan akibat penambalan ban darurat.',
                'action_taken'  => 'Verifikasi log perjalanan dan edukasi fitur komunikasi darurat aplikasi.',
                'logged_at'     => '2026-08-18 16:00:00',
            ],
            // Mitra 1 Solo (Eko Saputra)
            [
                'user'          => $mitraSolo1,
                'admin'         => $adminSolo,
                'warning_level' => 1,
                'reason'        => 'Penundaan pembatalan awal sebelum penugasan diambil.',
                'action_taken'  => 'Konseling standar operasional & refresh pelatihan mitra.',
                'logged_at'     => '2026-08-12 11:30:00',
            ],
            // Mitra 2 Solo (Hendra Wijaya)
            [
                'user'          => $mitraSolo2,
                'admin'         => $adminSolo,
                'warning_level' => 2,
                'reason'        => 'Evaluasi dokumentasi foto sebelum dan sesudah pengerjaan.',
                'action_taken'  => 'Pemberian SP2 dan peninjauan berkas foto. Mitra telah melengkapi standar SOP.',
                'logged_at'     => '2026-08-20 17:00:00',
            ],
            // Mitra 1 Sukoharjo (Tri Wahyudi)
            [
                'user'          => $mitraSkh1,
                'admin'         => $adminSolo,
                'warning_level' => 1,
                'reason'        => 'Evaluasi komunikasi kesepakatan titik pengerjaan rak apotek.',
                'action_taken'  => 'Konseling komunikasi sopan dan koordinasi lancar dengan customer.',
                'logged_at'     => '2026-08-23 12:00:00',
            ],
            // Mitra 1 Jogja (Danang Saputra)
            [
                'user'          => $mitraJogja1,
                'admin'         => $adminSleman,
                'warning_level' => 1,
                'reason'        => 'Pengecekan kelengkapan sertifikasi alat las portabel.',
                'action_taken'  => 'Verifikasi sertifikat keahlian teknik elektro & pengelasan oleh Admin.',
                'logged_at'     => '2026-08-25 16:00:00',
            ],
        ];

        foreach ($greylistRecords as $g) {
            $user  = $g['user'];
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

        $this->command->info('PartnerHistorySeeder berhasil membuat riwayat laporan resolved & log evaluasi greylist di 4 wilayah.');
    }
}
