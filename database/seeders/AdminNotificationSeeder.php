<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = User::where('role', 'admin')->get()->keyBy('email');
        
        $adminData = [
            // Sleman
            'admin.sleman@sayabantu.com' => [
                ['type' => 'new_registration', 'message' => 'Pendaftaran mitra baru (Agus Prasetyo - Depok Sleman) telah diverifikasi.', 'read' => true, 'time' => '2026-08-04 09:00:00'],
                ['type' => 'help_completed',   'message' => 'Bantuan "Bantu Pindahan & Angkat Kasur Busa Gejayan" telah selesai dikonfirmasi.', 'read' => true, 'time' => '2026-08-04 11:30:00'],
                ['type' => 'withdraw_request', 'message' => 'Pengajuan penarikan dana Rp 75.000 dari Mitra Agus Prasetyo (BCA) telah diproses.', 'read' => true, 'time' => '2026-08-08 17:00:00'],
            ],
            // Yogyakarta
            'admin.jogja@sayabantu.com' => [
                ['type' => 'new_registration', 'message' => 'Pendaftaran mitra baru (Surya Saputra - Gondomanan) telah diverifikasi.', 'read' => true, 'time' => '2026-08-05 09:00:00'],
                ['type' => 'help_completed',   'message' => 'Bantuan "Instalasi Lampu Sorot LED & Saklar Danurejan" telah selesai dikonfirmasi.', 'read' => true, 'time' => '2026-08-20 16:30:00'],
                ['type' => 'withdraw_request', 'message' => 'Pengajuan penarikan dana Rp 65.000 dari Mitra Hendra Setiawan (BPD DIY) menunggu persetujuan.', 'read' => false, 'time' => '2026-09-03 11:15:00'],
            ],
            // Surakarta
            'admin.surakarta@sayabantu.com' => [
                ['type' => 'new_registration', 'message' => 'Pendaftaran mitra baru (Eko Susanto - Banjarsari Surakarta) telah diverifikasi.', 'read' => true, 'time' => '2026-08-04 09:00:00'],
                ['type' => 'help_completed',   'message' => 'Bantuan "Pemasangan Lampu Gantung Hias Ruang Tamu" telah selesai dikonfirmasi.', 'read' => true, 'time' => '2026-08-06 15:30:00'],
            ],
            // Sukoharjo
            'admin.sukoharjo@sayabantu.com' => [
                ['type' => 'new_registration', 'message' => 'Pendaftaran mitra baru (Sugeng Riyadi - Kartasura Sukoharjo) telah diverifikasi.', 'read' => true, 'time' => '2026-08-05 09:00:00'],
                ['type' => 'help_completed',   'message' => 'Bantuan "Perbaikan Pompa Air Jetpump & Kran Bocor" telah selesai dikonfirmasi.', 'read' => true, 'time' => '2026-08-11 15:00:00'],
            ],
            // Semarang
            'admin.semarang@sayabantu.com' => [
                ['type' => 'new_registration', 'message' => 'Pendaftaran mitra baru (Bambang Pamungkas - Banyumanik) telah diverifikasi.', 'read' => true, 'time' => '2026-08-16 09:00:00'],
                ['type' => 'help_completed',   'message' => 'Bantuan "Pembersihan Toren Air & Ganti Pelampung Otomatis" telah selesai dikonfirmasi.', 'read' => true, 'time' => '2026-08-23 12:00:00'],
            ],
            // Jakarta Selatan
            'admin.jaksel@sayabantu.com' => [
                ['type' => 'new_registration', 'message' => 'Pendaftaran mitra baru (Fahmi Idris - Tebet) telah diverifikasi.', 'read' => true, 'time' => '2026-08-10 09:00:00'],
                ['type' => 'help_completed',   'message' => 'Bantuan "Servis Cuci 2 Unit AC Apartemen Casablanca Tebet" telah selesai dikonfirmasi.', 'read' => true, 'time' => '2026-08-18 12:30:00'],
                ['type' => 'withdraw_request', 'message' => 'Pengajuan penarikan dana Rp 110.000 dari Mitra Teguh Santoso (BCA) menunggu persetujuan.', 'read' => false, 'time' => '2026-09-03 09:30:00'],
            ],
            // Jakarta Barat
            'admin.jakbar@sayabantu.com' => [
                ['type' => 'new_registration', 'message' => 'Pendaftaran mitra baru (Hendra Kurniawan - Kebon Jeruk) telah diverifikasi.', 'read' => true, 'time' => '2026-08-11 09:00:00'],
                ['type' => 'withdraw_request', 'message' => 'Penarikan dana Rp 75.000 Mitra Hendra Kurniawan ditolak (rekening tidak valid).', 'read' => true, 'time' => '2026-09-02 14:30:00'],
            ],
            // Jakarta Timur
            'admin.jaktim@sayabantu.com' => [
                ['type' => 'new_registration', 'message' => 'Pendaftaran mitra baru (Rizky Ramadhan - Duren Sawit) telah diverifikasi.', 'read' => true, 'time' => '2026-08-12 09:00:00'],
                ['type' => 'help_completed',   'message' => 'Bantuan "Perbaikan Mesin Cuci 2 Tabung Air Tidak Keluar" telah selesai dikonfirmasi.', 'read' => true, 'time' => '2026-08-22 13:00:00'],
            ],
            // Bandung
            'admin.bandung@sayabantu.com' => [
                ['type' => 'new_registration', 'message' => 'Pendaftaran mitra baru (Asep Sunandar - Coblong) telah diverifikasi.', 'read' => true, 'time' => '2026-08-12 09:00:00'],
                ['type' => 'help_completed',   'message' => 'Bantuan "Bantu Rakit Lemari Pakaian Knockdown 3 Pintu Dago" telah selesai dikonfirmasi.', 'read' => true, 'time' => '2026-08-25 12:30:00'],
            ],
            // Surabaya
            'admin.surabaya@sayabantu.com' => [
                ['type' => 'new_registration', 'message' => 'Pendaftaran mitra baru (Cak Slamet Riyadi - Wonokromo) telah diverifikasi.', 'read' => true, 'time' => '2026-08-15 09:00:00'],
                ['type' => 'help_completed',   'message' => 'Bantuan "Beres Halaman Rumah & Potong Rumput Manyar Gubeng" telah selesai dikonfirmasi.', 'read' => true, 'time' => '2026-08-28 11:00:00'],
            ],
            // Malang
            'admin.malang@sayabantu.com' => [
                ['type' => 'new_registration', 'message' => 'Pendaftaran mitra baru (Bayu Wicaksono - Lowokwaru) telah diverifikasi.', 'read' => true, 'time' => '2026-08-17 09:00:00'],
                ['type' => 'help_completed',   'message' => 'Bantuan "Servis AC Kamar Kost Mahasiswa Sigura-gura" telah selesai dikonfirmasi.', 'read' => true, 'time' => '2026-08-29 14:00:00'],
            ],
            // Denpasar
            'admin.denpasar@sayabantu.com' => [
                ['type' => 'new_registration', 'message' => 'Pendaftaran mitra baru (I Made Sukadana - Denpasar Selatan) telah diverifikasi.', 'read' => true, 'time' => '2026-08-18 09:00:00'],
                ['type' => 'help_completed',   'message' => 'Bantuan "Perawatan & Pengecekan Pompa Sirkulasi Kolam Sanur" telah selesai dikonfirmasi.', 'read' => true, 'time' => '2026-09-01 13:00:00'],
            ],
            // Medan
            'admin.medan@sayabantu.com' => [
                ['type' => 'new_registration', 'message' => 'Pendaftaran mitra baru (Ucok Harahap - Medan Kota) telah diverifikasi.', 'read' => true, 'time' => '2026-08-20 09:00:00'],
                ['type' => 'help_completed',   'message' => 'Bantuan "Tambal Ban Tubeless Darurat & Ganti Oli Mesin Sisingamangaraja" telah selesai dikonfirmasi.', 'read' => true, 'time' => '2026-09-02 17:30:00'],
            ],
            // Palembang
            'admin.palembang@sayabantu.com' => [
                ['type' => 'new_registration', 'message' => 'Pendaftaran mitra baru (Rian Kurniawan - Ilir Barat I) telah diverifikasi.', 'read' => true, 'time' => '2026-08-21 09:00:00'],
                ['type' => 'help_completed',   'message' => 'Bantuan "Pemasangan MCB Box & Instalasi Jalur Lampu Dapur" telah selesai dikonfirmasi.', 'read' => true, 'time' => '2026-08-31 16:00:00'],
            ],
            // Makassar
            'admin.makassar@sayabantu.com' => [
                ['type' => 'new_registration', 'message' => 'Pendaftaran mitra baru (Baso Daeng Tompo - Panakkukang) telah diverifikasi.', 'read' => true, 'time' => '2026-08-22 09:00:00'],
                ['type' => 'help_completed',   'message' => 'Bantuan "Pengelasan Engsel Pagar Rumah & Ganti Roda Bawah Panakkukang" telah selesai dikonfirmasi.', 'read' => true, 'time' => '2026-09-03 12:30:00'],
            ],
            // Tangerang Selatan
            'admin.tangsel@sayabantu.com' => [
                ['type' => 'new_registration', 'message' => 'Pendaftaran mitra baru (Agung Nugroho - Serpong) telah diverifikasi.', 'read' => true, 'time' => '2026-08-23 09:00:00'],
                ['type' => 'help_completed',   'message' => 'Bantuan "Deep Cleaning Sofa 3-Seater & Sedot Tungau Kasur BSD" telah selesai dikonfirmasi.', 'read' => true, 'time' => '2026-09-02 15:00:00'],
            ],
        ];

        foreach ($adminData as $email => $list) {
            $admin = $admins->get($email);
            if (!$admin) {
                continue;
            }

            foreach ($list as $n) {
                $time = Carbon::parse($n['time']);
                $readAt = $n['read'] ? $time->copy()->addHour() : null;

                DB::table('notifications')->updateOrInsert(
                    [
                        'notifiable_type' => 'App\Models\User',
                        'notifiable_id'   => $admin->id,
                        'data'            => json_encode([
                            'type'    => $n['type'],
                            'message' => $n['message'],
                        ]),
                    ],
                    [
                        'id'         => (string) Str::uuid(),
                        'type'       => 'App\Notifications\CustomNotification',
                        'read_at'    => $readAt,
                        'created_at' => $time,
                        'updated_at' => $time,
                    ]
                );
            }
        }

        $this->command->info('AdminNotificationSeeder berhasil membuat notifikasi untuk seluruh Admin Wilayah Kecamatan se-Indonesia.');
    }
}

