<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\Chat;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\Rating;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class HelpsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi data contoh pesanan bantuan terstruktur, multi-periode, dan memiliki skenario cross-territory yang deterministic.
     */
    public function run(): void
    {
        $storageHelpsPath = storage_path('app/public/helps');
        if (!File::exists($storageHelpsPath)) {
            File::makeDirectory($storageHelpsPath, 0755, true);
        }

        $fixedPlatformFee = (float) AppSetting::getPlatformServiceFee();
        if ($fixedPlatformFee <= 0) {
            $fixedPlatformFee = 2000.00;
        }

        $customerSleman = User::where('email', 'customer@sayabantu.com')->first();
        $customerJogja  = User::where('email', 'customer.jogja@sayabantu.com')->first();
        $customerSolo   = User::where('email', 'customer.solo@sayabantu.com')->first();

        $mitraSleman    = User::where('email', 'mitra@sayabantu.com')->first();
        $mitraJogja     = User::where('email', 'mitra.jogja@sayabantu.com')->first();
        $mitraSolo      = User::where('email', 'mitra.solo@sayabantu.com')->first();

        $slemanCity     = City::where('code', '3404')->first() ?? City::first();
        $ngaglikDist    = District::where('city_id', $slemanCity?->id)->where('name', 'like', '%Ngaglik%')->first() ?? District::first();

        $jogjaCity      = City::where('code', '3471')->first() ?? $slemanCity;
        $gondomananDist = District::where('city_id', $jogjaCity?->id)->where('name', 'like', '%Gondomanan%')->first() ?? $ngaglikDist;

        $soloCity       = City::where('code', '3372')->first() ?? $slemanCity;
        $banjarsariDist = District::where('city_id', $soloCity?->id)->where('name', 'like', '%Banjarsari%')->first() ?? $ngaglikDist;

        $now = now();
        $prevMonth = $now->copy()->subMonth();
        $olderMonth = $now->copy()->subMonths(3);
        $prevYear = $now->copy()->subYear()->subMonths(2);

        $helpCases = [
            // 1. Pesanan Baru (Instant On-Site) - Menunggu Mitra (Sleman)
            [
                'order_id'              => 'HLP-SEED-0001',
                'user_id'               => $customerSleman?->id,
                'mitra_id'              => null,
                'city_id'               => $slemanCity?->id,
                'district_id'           => $ngaglikDist?->id,
                'title'                 => 'Cuci & Servis AC 1 PK di Ruang Tamu',
                'description'           => 'AC split 1 PK terasa kurang dingin dan mengeluarkan hembusan angin lemah. Mohon bantuan cuci filter dan cuci indoor outdoor.',
                'location'              => 'Jl. Kaliurang KM 9.5, Ngaglik, Sleman',
                'full_address'          => 'Perumahan Pondok Permai No. A-12, Sardonoharjo, Ngaglik, Sleman',
                'latitude'              => -7.7155600,
                'longitude'             => 110.3555600,
                'amount'                => 75000.00,
                'service_fee'           => 75000.00,
                'admin_fee'             => $fixedPlatformFee,
                'total_amount'          => 75000.00 + $fixedPlatformFee,
                'service_type'          => 'on_site_service',
                'order_mode'            => 'instant',
                'status'                => 'menunggu_mitra',
                'dispatch_mode'         => 'pool',
                'payment_status'        => 'paid',
                'escrow_status'         => 'locked',
                'created_at'            => $now->copy()->subMinutes(15),
            ],
            // 2. DETERMINISTIC CROSS-TERRITORY DEMO JOB:
            // Customer Profile: Sleman / Ngaglik (customer@sayabantu.com)
            // Help Location: Kota Yogyakarta / Gondomanan
            // Case Admin: Admin DIY / Gondomanan, Profile Admin: Admin Sleman / Ngaglik
            [
                'order_id'              => 'HLP-SEED-0002',
                'user_id'               => $customerSleman?->id,
                'mitra_id'              => $mitraJogja?->id,
                'city_id'               => $jogjaCity?->id,
                'district_id'           => $gondomananDist?->id,
                'title'                 => 'Pembersihan & Penataan Galeri Seni Malioboro',
                'description'           => 'Customer berdomisili Sleman memesan bantuan pembersihan & penataan display galeri seni keluarga di kawasan Gondomanan Kota Yogyakarta.',
                'location'              => 'Gondomanan, Kota Yogyakarta',
                'full_address'          => 'Jl. Malioboro No. 20, Gondomanan, Kota Yogyakarta',
                'latitude'              => -7.7956000,
                'longitude'             => 110.3695000,
                'partner_current_lat'   => -7.7956000,
                'partner_current_lng'   => 110.3695000,
                'amount'                => 95000.00,
                'service_fee'           => 95000.00,
                'admin_fee'             => $fixedPlatformFee,
                'total_amount'          => 95000.00 + $fixedPlatformFee,
                'service_type'          => 'on_site_service',
                'order_mode'            => 'instant',
                'status'                => 'in_progress',
                'dispatch_mode'         => 'assigned',
                'payment_status'        => 'paid',
                'escrow_status'         => 'locked',
                'mitra_assigned_at'     => $now->copy()->subHours(1),
                'taken_at'              => $now->copy()->subHours(1),
                'service_started_at'    => $now->copy()->subMinutes(30),
                'created_at'            => $now->copy()->subHours(1)->subMinutes(15),
            ],
            // 3. Pesanan Baru (Pickup Delivery) - Menunggu Mitra (Kota Yogyakarta)
            [
                'order_id'              => 'HLP-SEED-0003',
                'user_id'               => $customerJogja?->id,
                'mitra_id'              => null,
                'city_id'               => $jogjaCity?->id,
                'district_id'           => $gondomananDist?->id,
                'title'                 => 'Antar Berkas Dokumen Penting ke Kantor Pos Malioboro',
                'description'           => 'Tolong ambil dokumen tersegel di rumah dan antarkan ke Kantor Pos Besar dekat Titik Nol Yogyakarta.',
                'location'              => 'Gondomanan, Kota Yogyakarta',
                'full_address'          => 'Jl. Panembahan Senopati No. 8, Gondomanan, Kota Yogyakarta',
                'latitude'              => -7.7956000,
                'longitude'             => 110.3695000,
                'pickup_latitude'       => -7.7956000,
                'pickup_longitude'      => 110.3695000,
                'pickup_address'        => 'Jl. Panembahan Senopati No. 8, Gondomanan, Kota Yogyakarta',
                'delivery_latitude'     => -7.7980000,
                'delivery_longitude'    => 110.3670000,
                'delivery_address'      => 'Kantor Pos Besar Yogyakarta, Gondomanan, Kota Yogyakarta',
                'service_route_distance_km' => 2.5,
                'amount'                => 25000.00,
                'service_fee'           => 15000.00,
                'travel_fee'            => 10000.00,
                'admin_fee'             => $fixedPlatformFee,
                'total_amount'          => 25000.00 + $fixedPlatformFee,
                'service_type'          => 'pickup_delivery',
                'service_stage'         => 'going_to_pickup',
                'order_mode'            => 'instant',
                'status'                => 'menunggu_mitra',
                'dispatch_mode'         => 'pool',
                'payment_status'        => 'paid',
                'escrow_status'         => 'locked',
                'created_at'            => $now->copy()->subMinutes(10),
            ],
            // 4. Pesanan Sedang Berjalan (Mitra Sedang Menuju Lokasi) - Sleman
            [
                'order_id'              => 'HLP-SEED-0004',
                'user_id'               => $customerSleman?->id,
                'mitra_id'              => $mitraSleman?->id,
                'city_id'               => $slemanCity?->id,
                'district_id'           => $ngaglikDist?->id,
                'title'                 => 'Perbaikan Pipa Kran Bocor & Pompa Air',
                'description'           => 'Pipa utama dekat tandon bocor halus dan otomatis pompa air terus menyala. Peralatan dasar sudah disediakan.',
                'location'              => 'Ngaglik, Sleman',
                'full_address'          => 'Perumahan Pondok Permai No. A-12, Ngaglik, Sleman',
                'latitude'              => -7.7155600,
                'longitude'             => 110.3555600,
                'partner_initial_lat'   => -7.7250000,
                'partner_initial_lng'   => 110.3650000,
                'partner_current_lat'   => -7.7180000,
                'partner_current_lng'   => 110.3580000,
                'partner_location_updated_at' => $now,
                'amount'                => 60000.00,
                'service_fee'           => 60000.00,
                'admin_fee'             => $fixedPlatformFee,
                'total_amount'          => 60000.00 + $fixedPlatformFee,
                'service_type'          => 'on_site_service',
                'order_mode'            => 'instant',
                'status'                => 'partner_on_the_way',
                'dispatch_mode'         => 'assigned',
                'payment_status'        => 'paid',
                'escrow_status'         => 'locked',
                'mitra_assigned_at'     => $now->copy()->subMinutes(20),
                'taken_at'              => $now->copy()->subMinutes(20),
                'partner_started_at'    => $now->copy()->subMinutes(15),
                'created_at'            => $now->copy()->subMinutes(25),
            ],
            // 5. Pesanan Menunggu Konfirmasi Penyelesaian Customer - Surakarta
            [
                'order_id'              => 'HLP-SEED-0005',
                'user_id'               => $customerSolo?->id,
                'mitra_id'              => $mitraSolo?->id,
                'city_id'               => $soloCity?->id,
                'district_id'           => $banjarsariDist?->id,
                'title'                 => 'Pembersihan Rumput Halaman Depan & Belakang',
                'description'           => 'Babat rumput liar dan pembersihan sisa dahan kering di pekarangan rumah.',
                'location'              => 'Banjarsari, Surakarta',
                'full_address'          => 'Jl. Gajah Mada No. 34, Banjarsari, Surakarta',
                'latitude'              => -7.5666700,
                'longitude'             => 110.8166700,
                'amount'                => 85000.00,
                'service_fee'           => 85000.00,
                'admin_fee'             => $fixedPlatformFee,
                'total_amount'          => 85000.00 + $fixedPlatformFee,
                'service_type'          => 'on_site_service',
                'order_mode'            => 'instant',
                'status'                => 'waiting_customer_confirmation',
                'dispatch_mode'         => 'assigned',
                'payment_status'        => 'paid',
                'escrow_status'         => 'locked',
                'mitra_assigned_at'     => $now->copy()->subHours(3),
                'taken_at'              => $now->copy()->subHours(3),
                'service_started_at'    => $now->copy()->subHours(2),
                'service_completed_at'  => $now->copy()->subMinutes(10),
                'confirmation_deadline_at' => $now->copy()->addHours(24),
                'created_at'            => $now->copy()->subHours(3)->subMinutes(15),
            ],
            // 6. Pesanan Selesai (Completed & Rated) - Bulan Ini (Sleman)
            [
                'order_id'              => 'HLP-SEED-0006',
                'user_id'               => $customerSleman?->id,
                'mitra_id'              => $mitraSleman?->id,
                'city_id'               => $slemanCity?->id,
                'district_id'           => $ngaglikDist?->id,
                'title'                 => 'Bantu Pindahan Lemari & Meja Belajar',
                'description'           => 'Bantu angkat lemari pakaian 2 pintu dan meja belajar ke lantai 2.',
                'location'              => 'Ngaglik, Sleman',
                'full_address'          => 'Perumahan Pondok Permai No. A-12, Ngaglik, Sleman',
                'latitude'              => -7.7155600,
                'longitude'             => 110.3555600,
                'amount'                => 50000.00,
                'service_fee'           => 50000.00,
                'admin_fee'             => $fixedPlatformFee,
                'total_amount'          => 50000.00 + $fixedPlatformFee,
                'service_type'          => 'on_site_service',
                'order_mode'            => 'instant',
                'status'                => 'selesai',
                'dispatch_mode'         => 'assigned',
                'payment_status'        => 'paid',
                'escrow_status'         => 'released',
                'rating_status'         => 'rated',
                'mitra_assigned_at'     => $now->copy()->subDays(1)->subHours(3),
                'taken_at'              => $now->copy()->subDays(1)->subHours(3),
                'service_started_at'    => $now->copy()->subDays(1)->subHours(2),
                'service_completed_at'  => $now->copy()->subDays(1)->subHour(),
                'completed_at'          => $now->copy()->subDays(1)->subHour(),
                'created_at'            => $now->copy()->subDays(1)->subHours(4),
            ],
            // 7. Pesanan Dalam Sengketa (Disputed - Sleman)
            [
                'order_id'              => 'HLP-SEED-0007',
                'user_id'               => $customerSleman?->id,
                'mitra_id'              => $mitraSleman?->id,
                'city_id'               => $slemanCity?->id,
                'district_id'           => $ngaglikDist?->id,
                'title'                 => 'Pemasangan Saklar & Instalasi Lampu Taman',
                'description'           => 'Pemasangan 3 titik lampu taman dan 1 saklar otomatis.',
                'location'              => 'Ngaglik, Sleman',
                'full_address'          => 'Perumahan Pondok Permai No. A-12, Ngaglik, Sleman',
                'latitude'              => -7.7155600,
                'longitude'             => 110.3555600,
                'amount'                => 90000.00,
                'service_fee'           => 90000.00,
                'admin_fee'             => $fixedPlatformFee,
                'total_amount'          => 90000.00 + $fixedPlatformFee,
                'service_type'          => 'on_site_service',
                'order_mode'            => 'instant',
                'status'                => 'disputed',
                'dispatch_mode'         => 'assigned',
                'payment_status'        => 'paid',
                'escrow_status'         => 'locked',
                'mitra_assigned_at'     => $now->copy()->subDays(2)->subHours(5),
                'taken_at'              => $now->copy()->subDays(2)->subHours(5),
                'service_started_at'    => $now->copy()->subDays(2)->subHours(4),
                'service_completed_at'  => $now->copy()->subDays(2)->subHours(2),
                'disputed_at'           => $now->copy()->subDays(2)->subHours(1),
                'dispute_reason'        => 'Customer menyatakan pekerjaan belum selesai sepenuhnya tapi mitra sudah klik selesai.',
                'created_at'            => $now->copy()->subDays(2)->subHours(6),
            ],
            // 8. HISTORICAL DEMO: Bulan Lalu (Previous Month) - Selesai (Kota Yogyakarta)
            [
                'order_id'              => 'HLP-SEED-0008',
                'user_id'               => $customerJogja?->id,
                'mitra_id'              => $mitraJogja?->id,
                'city_id'               => $jogjaCity?->id,
                'district_id'           => $gondomananDist?->id,
                'title'                 => 'Pengecatan Ulang Dinding Kamar & Plafon',
                'description'           => 'Pengecatan kamar tidur ukuran 3x4 meter. Cat tembok dan kuas roll sudah disiapkan pemilik rumah.',
                'location'              => 'Gondomanan, Kota Yogyakarta',
                'full_address'          => 'Jl. Panembahan Senopati No. 8, Gondomanan, Kota Yogyakarta',
                'latitude'              => -7.7956000,
                'longitude'             => 110.3695000,
                'amount'                => 120000.00,
                'service_fee'           => 120000.00,
                'admin_fee'             => $fixedPlatformFee,
                'total_amount'          => 120000.00 + $fixedPlatformFee,
                'service_type'          => 'on_site_service',
                'order_mode'            => 'instant',
                'status'                => 'selesai',
                'dispatch_mode'         => 'assigned',
                'payment_status'        => 'paid',
                'escrow_status'         => 'released',
                'rating_status'         => 'rated',
                'mitra_assigned_at'     => $prevMonth->copy()->startOfMonth()->addDays(5)->setHour(9),
                'taken_at'              => $prevMonth->copy()->startOfMonth()->addDays(5)->setHour(9),
                'service_started_at'    => $prevMonth->copy()->startOfMonth()->addDays(5)->setHour(10),
                'service_completed_at'  => $prevMonth->copy()->startOfMonth()->addDays(5)->setHour(12),
                'completed_at'          => $prevMonth->copy()->startOfMonth()->addDays(5)->setHour(12),
                'created_at'            => $prevMonth->copy()->startOfMonth()->addDays(5)->setHour(8),
            ],
            // 9. HISTORICAL DEMO: 3 Bulan Lalu (Older Month, Current Year) - Selesai (Surakarta)
            [
                'order_id'              => 'HLP-SEED-0009',
                'user_id'               => $customerSolo?->id,
                'mitra_id'              => $mitraSolo?->id,
                'city_id'               => $soloCity?->id,
                'district_id'           => $banjarsariDist?->id,
                'title'                 => 'Renovasi Pintu Kayu & Kunci Kamar Mandi',
                'description'           => 'Perbaikan engsel pintu kayu utama dan penggantian silinder kunci kamar mandi.',
                'location'              => 'Banjarsari, Surakarta',
                'full_address'          => 'Jl. Gajah Mada No. 34, Banjarsari, Surakarta',
                'latitude'              => -7.5666700,
                'longitude'             => 110.8166700,
                'amount'                => 110000.00,
                'service_fee'           => 110000.00,
                'admin_fee'             => $fixedPlatformFee,
                'total_amount'          => 110000.00 + $fixedPlatformFee,
                'service_type'          => 'on_site_service',
                'order_mode'            => 'instant',
                'status'                => 'selesai',
                'dispatch_mode'         => 'assigned',
                'payment_status'        => 'paid',
                'escrow_status'         => 'released',
                'rating_status'         => 'rated',
                'mitra_assigned_at'     => $olderMonth->copy()->startOfMonth()->addDays(10)->setHour(10),
                'taken_at'              => $olderMonth->copy()->startOfMonth()->addDays(10)->setHour(10),
                'service_started_at'    => $olderMonth->copy()->startOfMonth()->addDays(10)->setHour(11),
                'service_completed_at'  => $olderMonth->copy()->startOfMonth()->addDays(10)->setHour(14),
                'completed_at'          => $olderMonth->copy()->startOfMonth()->addDays(10)->setHour(14),
                'created_at'            => $olderMonth->copy()->startOfMonth()->addDays(10)->setHour(9),
            ],
            // 10. HISTORICAL DEMO: Tahun Lalu (Previous Year) - Selesai (Sleman)
            [
                'order_id'              => 'HLP-SEED-0010',
                'user_id'               => $customerSleman?->id,
                'mitra_id'              => $mitraSleman?->id,
                'city_id'               => $slemanCity?->id,
                'district_id'           => $ngaglikDist?->id,
                'title'                 => 'Pembersihan Toren Air & Pemasangan Filter',
                'description'           => 'Kuras toren air kapasitas 500L di atap rumah dan pemasangan cartridge filter sedimen baru.',
                'location'              => 'Ngaglik, Sleman',
                'full_address'          => 'Perumahan Pondok Permai No. A-12, Ngaglik, Sleman',
                'latitude'              => -7.7155600,
                'longitude'             => 110.3555600,
                'amount'                => 130000.00,
                'service_fee'           => 130000.00,
                'admin_fee'             => $fixedPlatformFee,
                'total_amount'          => 130000.00 + $fixedPlatformFee,
                'service_type'          => 'on_site_service',
                'order_mode'            => 'instant',
                'status'                => 'selesai',
                'dispatch_mode'         => 'assigned',
                'payment_status'        => 'paid',
                'escrow_status'         => 'released',
                'rating_status'         => 'rated',
                'mitra_assigned_at'     => $prevYear->copy()->startOfMonth()->addDays(15)->setHour(13),
                'taken_at'              => $prevYear->copy()->startOfMonth()->addDays(15)->setHour(13),
                'service_started_at'    => $prevYear->copy()->startOfMonth()->addDays(15)->setHour(14),
                'service_completed_at'  => $prevYear->copy()->startOfMonth()->addDays(15)->setHour(16),
                'completed_at'          => $prevYear->copy()->startOfMonth()->addDays(15)->setHour(16),
                'created_at'            => $prevYear->copy()->startOfMonth()->addDays(15)->setHour(12),
            ],
        ];

        foreach ($helpCases as $caseData) {
            $help = Help::updateOrCreate(
                ['order_id' => $caseData['order_id']],
                $caseData
            );

            // Jika pesanan selesai, buatkan rating timbal balik
            if ($help->status === 'selesai' && $help->user_id && $help->mitra_id) {
                Rating::updateOrCreate(
                    [
                        'help_id'  => $help->id,
                        'rater_id' => $help->user_id,
                        'ratee_id' => $help->mitra_id,
                    ],
                    [
                        'type'       => 'customer_to_mitra',
                        'rating'     => 5,
                        'review'     => 'Kerja sangat rapi, ramah, dan cepat tanggap. Sangat direkomendasikan!',
                        'created_at' => $help->completed_at ?? $now,
                        'updated_at' => $help->completed_at ?? $now,
                    ]
                );
            }

            // Jika ada interaksi mitra & customer, buatkan sampel chat
            if (in_array($help->status, ['partner_on_the_way', 'in_progress', 'disputed', 'selesai']) && $help->mitra_id && $help->user_id) {
                Chat::firstOrCreate(
                    [
                        'help_id'     => $help->id,
                        'customer_id' => $help->user_id,
                        'mitra_id'    => $help->mitra_id,
                        'sender_type' => 'customer',
                        'message'     => 'Halo Mas, apakah perlengkapannya sudah siap?',
                    ],
                    [
                        'is_read'    => true,
                        'read_at'    => $help->created_at->copy()->addMinutes(6),
                        'created_at' => $help->created_at->copy()->addMinutes(5),
                    ]
                );

                Chat::firstOrCreate(
                    [
                        'help_id'     => $help->id,
                        'customer_id' => $help->user_id,
                        'mitra_id'    => $help->mitra_id,
                        'sender_type' => 'mitra',
                        'message'     => 'Halo kak, semua alat sudah siap. Saya sedang perjalanan ke lokasi ya.',
                    ],
                    [
                        'is_read'    => true,
                        'read_at'    => $help->created_at->copy()->addMinutes(8),
                        'created_at' => $help->created_at->copy()->addMinutes(7),
                    ]
                );
            }
        }

        $this->command?->info('✓ HelpsSeeder berhasil mengisi sampel skenario pesanan bantuan (termasuk cross-territory & multi-periode).');
    }
}
