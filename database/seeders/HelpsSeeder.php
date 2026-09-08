<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\Chat;
use App\Models\City;
use App\Models\District;
use App\Models\Help;
use App\Models\HelpCancelRequest;
use App\Models\PartnerActivity;
use App\Models\Rating;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class HelpsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Mengisi data transaksi bantuan berskala besar mencakup berbagai kegiatan sehari-hari masyarakat Indonesia
     * Terdistribusi di seluruh kota dan kecamatan strategis se-Indonesia dengan ragam status operasional lengkap.
     * Mengadopsi arsitektur FULL PLAN REVISI 3:
     * 1. 3 Service Types (on_site_service, pickup_delivery, buy_for_customer)
     * 2. Order Modes (instant, scheduled)
     * 3. 3-Context Distances (matching_distance_km, travel_distance_km, service_route_distance_km, route_source)
     * 4. Multi-Stage Tracking & Stages
     * 5. Transparent Financial Breakdown (service_fee, travel_fee, item_fund, admin_fee, total_amount)
     * 6. Special Cancellation Requests & Audit (HelpCancelRequest)
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

        // Helper function for user and location resolution
        $users = User::all()->keyBy('email');

        $resolveDistCity = function ($cityName, $distName) {
            $city = City::where('name', 'like', "%{$cityName}%")->first() ?? City::first();
            $district = null;
            if ($city) {
                $district = District::where('city_id', $city->id)->where('name', 'like', "%{$distName}%")->first();
            }
            if (!$district) {
                $district = District::where('name', 'like', "%{$distName}%")->first() ?? District::first();
            }
            return [$city, $district];
        };

        // =========================================================================
        // DAFTAR KEGIATAN & TRANSAKSI BANTUAN SE-INDONESIA (REVISI 3 COMPLIANT)
        // =========================================================================
        $tasksData = [
            // ─────────────────────────────────────────────────────────────────────
            // 1. D.I. YOGYAKARTA (SLEMAN & KOTA JOGJA)
            // ─────────────────────────────────────────────────────────────────────
            [
                'order_id'      => 'HELP-20260804-SLM01',
                'cust_email'    => 'customer.sleman1@sayabantu.com',
                'mitra_email'   => 'mitra.sleman1@sayabantu.com',
                'city'          => 'Sleman', 'dist' => 'Depok',
                'service_type'  => 'on_site_service',
                'order_mode'    => 'instant',
                'service_stage' => null,
                'title'         => 'Bantu Pindahan & Angkat Kasur Busa Gejayan',
                'service_fee'   => 80000,
                'travel_fee'    => 0, // 0-2 KM free radius
                'item_fund'     => 0,
                'matching_dist' => 1.4,
                'travel_dist'   => 1.4,
                'service_dist'  => 0,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 8.5,
                'desc'          => 'Pindahan kosan berjarak 500 meter, butuh 1 orang membantu angkut kasur busa tebal dan 3 kardus buku dari lantai 2.',
                'loc'           => 'Caturtunggal, Depok, Sleman',
                'addr'          => 'Jl. Affandi No. 45, Caturtunggal, Depok, Sleman',
                'lat'           => -7.7712, 'lng' => 110.3854,
                'status'        => 'selesai',
                'escrow'        => 'released',
                'payment'       => 'paid',
                'date'          => '2026-08-04', 'start' => '09:00', 'end' => '11:15',
                'cust_msg'      => 'Halo Mas Agus, saya tunggu di depan gerbang kos ya.',
                'mitra_msg'     => 'Siap Bu Rina, saya meluncur bawa tali tambang dan troli lipat.',
                'cust_rev'      => 'Mas Agus sangat cekatan, ramah, dan hati-hati mengangkat barang. Sangat puas!',
                'mitra_rev'     => 'Customer sangat ramah, lokasi jelas dan dibantu saat menurunkan barang.'
            ],
            [
                'order_id'      => 'HELP-20260810-SLM02',
                'cust_email'    => 'customer.sleman2@sayabantu.com',
                'mitra_email'   => 'mitra.sleman2@sayabantu.com',
                'city'          => 'Sleman', 'dist' => 'Mlati',
                'service_type'  => 'on_site_service',
                'order_mode'    => 'instant',
                'service_stage' => null,
                'title'         => 'Cuci AC Split 1 PK & Bersihkan Filter Sinduadi',
                'service_fee'   => 90000,
                'travel_fee'    => 5000, // 3.5 km (1.5 km billable * 2500)
                'item_fund'     => 0,
                'matching_dist' => 3.5,
                'travel_dist'   => 3.5,
                'service_dist'  => 0,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 9.2,
                'desc'          => 'AC kamar utama mulai kurang dingin dan keluar bau apek. Butuh cuci steam indoor & outdoor serta bersihkan selang drain.',
                'loc'           => 'Sinduadi, Mlati, Sleman',
                'addr'          => 'Perumahan Mlati Asri No. C-4, Sinduadi, Mlati, Sleman',
                'lat'           => -7.7589, 'lng' => 110.3621,
                'status'        => 'selesai',
                'escrow'        => 'released',
                'payment'       => 'paid',
                'date'          => '2026-08-10', 'start' => '10:00', 'end' => '12:00',
                'cust_msg'      => 'Mas Budi, posisi tangga lipat sudah saya siapkan.',
                'mitra_msg'     => 'Baik Pak Farhan, peralatan jet washer dan terpal pelindung sudah siap.',
                'cust_rev'      => 'AC kembali dingin semriwing dan bersih rapi tidak ada ceceran air.',
                'mitra_rev'     => 'Customer kooperatif dan ramah.'
            ],
            [
                'order_id'      => 'HELP-20260812-SLM03',
                'cust_email'    => 'customer@sayabantu.com',
                'mitra_email'   => 'mitra.sleman3@sayabantu.com',
                'city'          => 'Sleman', 'dist' => 'Ngaglik',
                'service_type'  => 'buy_for_customer',
                'order_mode'    => 'instant',
                'service_stage' => 'delivered',
                'title'         => 'Titip Beli Semen Tambal & Aquaproof Toko Besi',
                'service_fee'   => 45000,
                'travel_fee'    => 7500,
                'item_fund'     => 125000, // Dana titipan belanja (escrow terlindungi)
                'matching_dist' => 2.2,
                'travel_dist'   => 2.2,
                'service_dist'  => 3.8,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 10.0,
                'store_name'    => 'TB Makmur Jaya Palagan',
                'store_addr'    => 'Jl. Palagan KM 9, Sariharjo, Ngaglik, Sleman',
                'store_lat'     => -7.7250, 'store_lng' => 110.3810,
                'desc'          => 'Titip belikan 1 sak semen instan 25kg dan 1 kaleng Aquaproof 1kg warna abu-abu untuk persiapan tambal genteng.',
                'loc'           => 'Sariharjo, Ngaglik, Sleman',
                'addr'          => 'Jl. Palagan Tentara Pelajar KM 8.5, Sariharjo, Ngaglik, Sleman',
                'lat'           => -7.7285, 'lng' => 110.3798,
                'status'        => 'selesai',
                'escrow'        => 'released',
                'payment'       => 'paid',
                'date'          => '2026-08-12', 'start' => '08:30', 'end' => '10:30',
                'cust_msg'      => 'Pak Joko, struk belanja toko besi mohon dilampirkan ya.',
                'mitra_msg'     => 'Siap Bu Siti, semen dan aquaproof sudah dibeli nota sudah difoto.',
                'cust_rev'      => 'Sangat membantu dan jujur, barang sesuai spesifikasi dan nota asli disertakan.',
                'mitra_rev'     => 'Customer ramah dan dana titipan belanja sesuai estimasi.'
            ],
            [
                'order_id'      => 'HELP-20260815-JOG01',
                'cust_email'    => 'customer.jogja1@sayabantu.com',
                'mitra_email'   => 'mitra.jogja1@sayabantu.com',
                'city'          => 'Yogyakarta', 'dist' => 'Danurejan',
                'service_type'  => 'pickup_delivery',
                'order_mode'    => 'instant',
                'service_stage' => 'at_destination',
                'title'         => 'Antar Dokumen Perjanjian Notaris & Berkas Kafe',
                'service_fee'   => 35000,
                'travel_fee'    => 5000,
                'item_fund'     => 0,
                'matching_dist' => 1.8,
                'travel_dist'   => 1.8,
                'service_dist'  => 4.5,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 7.5,
                'pickup_addr'   => 'Kantor Notaris Jl. C. Simanjuntak No. 22, Terban, Gondokusuman',
                'pickup_lat'    => -7.7785, 'pickup_lng' => 110.3750,
                'desc'          => 'Ambil map dokumen perjanjian sewa dari kantor notaris dan antar langsung ke meja kasir kafe Jl. Mataram.',
                'loc'           => 'Suryatmajan, Danurejan, Kota Yogyakarta',
                'addr'          => 'Jl. Mataram No. 60, Danurejan, Yogyakarta',
                'lat'           => -7.7942, 'lng' => 110.3705,
                'status'        => 'selesai',
                'escrow'        => 'released',
                'payment'       => 'paid',
                'date'          => '2026-08-15', 'start' => '14:00', 'end' => '15:10',
                'cust_msg'      => 'Mas Joko, map dokumen warna biru tua atas nama CV Kopi Nusantara ya.',
                'mitra_msg'     => 'Dokumen sudah diterima dari resepsionis, langsung meluncur ke kafe.',
                'cust_rev'      => 'Cepat, aman, dan dokumen tidak terlipat sama sekali.',
                'mitra_rev'     => 'Titik penjemputan dan tujuan sangat jelas.'
            ],

            // ─────────────────────────────────────────────────────────────────────
            // 2. JAWA TENGAH (SURAKARTA & SUKOHARJO)
            // ─────────────────────────────────────────────────────────────────────
            [
                'order_id'      => 'HELP-20260818-SKT01',
                'cust_email'    => 'customer.surakarta1@sayabantu.com',
                'mitra_email'   => 'mitra.surakarta1@sayabantu.com',
                'city'          => 'Surakarta', 'dist' => 'Banjarsari',
                'service_type'  => 'on_site_service',
                'order_mode'    => 'instant',
                'service_stage' => null,
                'title'         => 'Perbaikan MCB Listrik Sering Jeglek & Cek Konsleting',
                'service_fee'   => 85000,
                'travel_fee'    => 0,
                'item_fund'     => 0,
                'matching_dist' => 1.5,
                'travel_dist'   => 1.5,
                'service_dist'  => 0,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 8.0,
                'desc'          => 'MCB utama 900VA sering trip saat nyalakan pompa air dan kulkas bersamaan. Butuh pemeriksaan jalur pembagian beban sekring.',
                'loc'           => 'Manahan, Banjarsari, Kota Surakarta',
                'addr'          => 'Jl. Adi Sucipto No. 33, Manahan, Banjarsari, Surakarta',
                'lat'           => -7.5582, 'lng' => 110.8054,
                'status'        => 'selesai',
                'escrow'        => 'released',
                'payment'       => 'paid',
                'date'          => '2026-08-18', 'start' => '13:00', 'end' => '14:45',
                'cust_msg'      => 'Mas Tri, MCB pengganti merek Schneider sudah saya sediakan.',
                'mitra_msg'     => 'Siap Pak Hendra, saya bawa tespen dan tang amperemeter.',
                'cust_rev'      => 'Paham kelistrikan dengan sangat baik, rapi, dan sekarang listrik sudah tidak pernah jeglek lagi.',
                'mitra_rev'     => 'Customer kooperatif dan menyediakan perlengkapan dengan baik.'
            ],
            [
                'order_id'      => 'HELP-20260820-SKH01',
                'cust_email'    => 'customer.sukoharjo1@sayabantu.com',
                'mitra_email'   => 'mitra.sukoharjo1@sayabantu.com',
                'city'          => 'Sukoharjo', 'dist' => 'Kartasura',
                'service_type'  => 'buy_for_customer',
                'order_mode'    => 'instant',
                'service_stage' => 'delivered',
                'title'         => 'Titip Beli Obat Resep Apotek 24 Jam Kartasura',
                'service_fee'   => 35000,
                'travel_fee'    => 5000,
                'item_fund'     => 85000,
                'matching_dist' => 2.5,
                'travel_dist'   => 2.5,
                'service_dist'  => 3.2,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 9.0,
                'store_name'    => 'Apotek K-24 Kartasura',
                'store_addr'    => 'Jl. Ahmad Yani No. 110, Kartasura, Sukoharjo',
                'store_lat'     => -7.5510, 'store_lng' => 110.7490,
                'desc'          => 'Titip belikan obat sirup penurun panas anak dan vitamin resep dokter di Apotek K-24 Kartasura.',
                'loc'           => 'Pabelan, Kartasura, Sukoharjo',
                'addr'          => 'Perum Gonilan Asri No. 5, Pabelan, Kartasura, Sukoharjo',
                'lat'           => -7.5542, 'lng' => 110.7712,
                'status'        => 'selesai',
                'escrow'        => 'released',
                'payment'       => 'paid',
                'date'          => '2026-08-20', 'start' => '19:30', 'end' => '20:40',
                'cust_msg'      => 'Mas Wahyu, foto struk apotek jangan lupa ya.',
                'mitra_msg'     => 'Siap Bu, obat sudah dibeli struk asli sudah dimasukkan ke kantong plastik.',
                'cust_rev'      => 'Sangat responsif dan cepat tanggap untuk kebutuhan obat darurat malam hari.',
                'mitra_rev'     => 'Senang bisa membantu customer yang sedang membutuhkan.'
            ],

            // ─────────────────────────────────────────────────────────────────────
            // 3. DKI JAKARTA (JAKSEL, JAKBAR, JAKTIM)
            // ─────────────────────────────────────────────────────────────────────
            [
                'order_id'      => 'HELP-20260822-JKT01',
                'cust_email'    => 'customer.jaksel1@sayabantu.com',
                'mitra_email'   => 'mitra.jaksel1@sayabantu.com',
                'city'          => 'Jakarta Selatan', 'dist' => 'Kebayoran Baru',
                'service_type'  => 'on_site_service',
                'order_mode'    => 'instant',
                'service_stage' => null,
                'title'         => 'Servis Pompa Pendorong Booster Pump Apartemen',
                'service_fee'   => 160000,
                'travel_fee'    => 10000,
                'item_fund'     => 0,
                'matching_dist' => 4.2,
                'travel_dist'   => 4.2,
                'service_dist'  => 0,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 11.5,
                'desc'          => 'Pompa booster air mandi berbunyi keras mendengung tapi air kran tidak kencang. Butuh cek kapasitor dan impeller.',
                'loc'           => 'Gunung, Kebayoran Baru, Jakarta Selatan',
                'addr'          => 'Jl. Bumi No. 18, Kebayoran Baru, Jakarta Selatan',
                'lat'           => -6.2341, 'lng' => 106.7912,
                'status'        => 'selesai',
                'escrow'        => 'released',
                'payment'       => 'paid',
                'date'          => '2026-08-22', 'start' => '10:00', 'end' => '12:30',
                'cust_msg'      => 'Pak Hendra, tolong cek saklar otomatis flow switch juga ya.',
                'mitra_msg'     => 'Siap Pak Doni, saya bawa sparepart flow switch dan kapasitor cadangan.',
                'cust_rev'      => 'Pengerjaan sangat profesional, suara pompa kembali halus dan tekanan air kencang.',
                'mitra_rev'     => 'Akses lokasi mudah dan customer sangat ramah.'
            ],
            [
                'order_id'      => 'HELP-20260825-JKT02',
                'cust_email'    => 'customer.jaksel2@sayabantu.com',
                'mitra_email'   => 'mitra.jaksel2@sayabantu.com',
                'city'          => 'Jakarta Selatan', 'dist' => 'Tebet',
                'service_type'  => 'pickup_delivery',
                'order_mode'    => 'instant',
                'service_stage' => 'at_destination',
                'title'         => 'Antar Parcel Buah & Hampers Kantor Tebet ke Kuningan',
                'service_fee'   => 45000,
                'travel_fee'    => 7500,
                'item_fund'     => 0,
                'matching_dist' => 3.1,
                'travel_dist'   => 3.1,
                'service_dist'  => 5.8,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 8.2,
                'pickup_addr'   => 'Toko Buah Segar Tebet Barat Dalam No. 30',
                'pickup_lat'    => -6.2360, 'pickup_lng' => 106.8480,
                'desc'          => 'Ambil 2 keranjang parcel buah premium dan antarkan hati-hati ke lobi Menara Kuningan lt. 12.',
                'loc'           => 'Karet Kuningan, Setiabudi, Jakarta Selatan',
                'addr'          => 'Jl. HR Rasuna Said Blok X-7 No. 5, Jakarta Selatan',
                'lat'           => -6.2201, 'lng' => 106.8312,
                'status'        => 'selesai',
                'escrow'        => 'released',
                'payment'       => 'paid',
                'date'          => '2026-08-25', 'start' => '11:00', 'end' => '12:15',
                'cust_msg'      => 'Mas Arief, parcel mohon jangan ditumpuk ya.',
                'mitra_msg'     => 'Aman Pak, saya bawa tas delivery box motor beralas busa empuk.',
                'cust_rev'      => 'Parcel sampai utuh rapi dan pita hiasan tetap cantik. Recommended!',
                'mitra_rev'     => 'Penerima di lokasi tujuan sangat ramah.'
            ],

            // ─────────────────────────────────────────────────────────────────────
            // 4. JAWA BARAT (BANDUNG)
            // ─────────────────────────────────────────────────────────────────────
            [
                'order_id'      => 'HELP-20260828-BDG01',
                'cust_email'    => 'customer.bandung1@sayabantu.com',
                'mitra_email'   => 'mitra.bandung1@sayabantu.com',
                'city'          => 'Bandung', 'dist' => 'Coblong',
                'service_type'  => 'on_site_service',
                'order_mode'    => 'instant',
                'service_stage' => null,
                'title'         => 'Pembersihan Lumut & Kuras Toren Air 1000 Liter Dago',
                'service_fee'   => 120000,
                'travel_fee'    => 5000,
                'item_fund'     => 0,
                'matching_dist' => 2.4,
                'travel_dist'   => 2.4,
                'service_dist'  => 0,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 9.5,
                'desc'          => 'Kuras toren air di dak lantai 3, bersihkan endapan lumpur dan lumut agar air kran jernih kembali.',
                'loc'           => 'Dago, Coblong, Kota Bandung',
                'addr'          => 'Jl. Ir. H. Juanda No. 120, Dago, Coblong, Kota Bandung',
                'lat'           => -6.8795, 'lng' => 107.6142,
                'status'        => 'selesai',
                'escrow'        => 'released',
                'payment'       => 'paid',
                'date'          => '2026-08-28', 'start' => '09:00', 'end' => '11:30',
                'cust_msg'      => 'Kang Asep, selang pembuangan air kuras sudah saya siapkan.',
                'mitra_msg'     => 'Muhun Teh Maya, abdi bawa sikat toren dan cairan desinfektan food-grade.',
                'cust_rev'      => 'Toren air jadi kinclong bersih, air kran jernih tanpa bau karat lagi. Hatur nuhun!',
                'mitra_rev'     => 'Sami-sami Teh Maya, hatur nuhun kasaenana.'
            ],

            // ─────────────────────────────────────────────────────────────────────
            // 5. JAWA TIMUR (SURABAYA & MALANG)
            // ─────────────────────────────────────────────────────────────────────
            [
                'order_id'      => 'HELP-20260830-SBY01',
                'cust_email'    => 'customer.surabaya1@sayabantu.com',
                'mitra_email'   => 'mitra.surabaya1@sayabantu.com',
                'city'          => 'Surabaya', 'dist' => 'Gubeng',
                'service_type'  => 'buy_for_customer',
                'order_mode'    => 'instant',
                'service_stage' => 'delivered',
                'title'         => 'Titip Beli Nasi Rawon & Spiku Khas Surabaya',
                'service_fee'   => 40000,
                'travel_fee'    => 7500,
                'item_fund'     => 140000,
                'matching_dist' => 3.2,
                'travel_dist'   => 3.2,
                'service_dist'  => 4.9,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 8.8,
                'store_name'    => 'Depot Rawon & Spiku Gubeng',
                'store_addr'    => 'Jl. Raya Gubeng No. 66, Gubeng, Surabaya',
                'store_lat'     => -7.2740, 'store_lng' => 112.7530,
                'desc'          => 'Titip beli 3 porsi Rawon Daging Empal dan 1 kotak Spiku Khas Surabaya untuk jamuan tamu kantor.',
                'loc'           => 'Airlangga, Gubeng, Surabaya',
                'addr'          => 'Jl. Dharmawangsa No. 15, Gubeng, Surabaya',
                'lat'           => -7.2765, 'lng' => 112.7589,
                'status'        => 'selesai',
                'escrow'        => 'released',
                'payment'       => 'paid',
                'date'          => '2026-08-30', 'start' => '11:30', 'end' => '12:45',
                'cust_msg'      => 'Cak Slamet, kuah rawon mohon dipisah ya.',
                'mitra_msg'     => 'Beres Pak Kevin, kuah dipisah dan sambal terasi sudah komplit.',
                'cust_rev'      => 'Cepat sekali sampai, makanan masih hangat fresh dan rasa mantap.',
                'mitra_rev'     => 'Matur suwun Pak Kevin.'
            ],

            // ─────────────────────────────────────────────────────────────────────
            // 6. RIWAYAT PEKERJAAN SELESAI TAMBAHAN (SELESAI 100%)
            // ─────────────────────────────────────────────────────────────────────
            [
                'order_id'      => 'HELP-20260831-SLM06',
                'cust_email'    => 'customer.sleman1@sayabantu.com',
                'mitra_email'   => 'mitra.sleman1@sayabantu.com',
                'city'          => 'Sleman', 'dist' => 'Depok',
                'service_type'  => 'on_site_service',
                'order_mode'    => 'instant',
                'service_stage' => null,
                'title'         => 'Perbaikan Pipa Pembuangan Bak Cuci Piring Bocor',
                'service_fee'   => 75000,
                'travel_fee'    => 0,
                'item_fund'     => 0,
                'matching_dist' => 1.2,
                'travel_dist'   => 1.2,
                'service_dist'  => 0,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 7.8,
                'desc'          => 'Pipa PVC sambungan bawah wastafel bocor menetes ke lemari dapur, butuh diganti fitting dan dilem ulang.',
                'loc'           => 'Condongcatur, Depok, Sleman',
                'addr'          => 'Jl. Ring Road Utara No. 88, Condongcatur, Depok, Sleman',
                'lat'           => -7.7592, 'lng' => 110.4012,
                'status'        => 'selesai',
                'escrow'        => 'released',
                'payment'       => 'paid',
                'date'          => '2026-08-31', 'start' => '09:00', 'end' => '10:30',
                'cust_msg'      => 'Mas Agus, pipa sudah saya lap kering dulu.',
                'mitra_msg'     => 'Saya sudah pasang pipa baru dan lem kering sempurna ya Bu.',
                'cust_rev'      => 'Pengerjaan cepat dan rapi, pipa tidak bocor lagi.',
                'mitra_rev'     => 'Customer sangat kooperatif.',
            ],
            [
                'order_id'      => 'HELP-20260901-JKT04',
                'cust_email'    => 'customer.jaksel1@sayabantu.com',
                'mitra_email'   => 'mitra.jaksel3@sayabantu.com',
                'city'          => 'Jakarta Selatan', 'dist' => 'Tebet',
                'service_type'  => 'pickup_delivery',
                'order_mode'    => 'instant',
                'service_stage' => 'delivered',
                'title'         => 'Antar Paket Kue Lapis Legit & Brownies Tebet ke Pancoran',
                'service_fee'   => 40000,
                'travel_fee'    => 6000,
                'item_fund'     => 0,
                'matching_dist' => 2.8,
                'travel_dist'   => 2.8,
                'service_dist'  => 4.2,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 9.1,
                'pickup_addr'   => 'Dapur Kue Tebet Timur Dalam No. 14',
                'pickup_lat'    => -6.2390, 'pickup_lng' => 106.8520,
                'desc'          => 'Paket kue ulang tahun lapis legit siap dijemput dan diantar ke Komplek Perumahan Pancoran.',
                'loc'           => 'Pancoran, Jakarta Selatan',
                'addr'          => 'Jl. MT Haryono Kav. 23, Pancoran, Jakarta Selatan',
                'lat'           => -6.2441, 'lng' => 106.8512,
                'status'        => 'selesai',
                'escrow'        => 'released',
                'payment'       => 'paid',
                'date'          => '2026-09-01', 'start' => '10:15', 'end' => '11:15',
                'cust_msg'      => 'Mas Teguh, saya tunggu ya di rumah.',
                'mitra_msg'     => 'Barang sudah saya serahkan ke penerima di lokasi.',
                'cust_rev'      => 'Pengantaran tepat waktu dan kue dalam kondisi sangat bagus.',
                'mitra_rev'     => 'Penerima ramah dan alamat mudah ditemukan.',
            ],
            [
                'order_id'      => 'HELP-20260901-BDG02',
                'cust_email'    => 'customer.bandung1@sayabantu.com',
                'mitra_email'   => 'mitra.bandung2@sayabantu.com',
                'city'          => 'Bandung', 'dist' => 'Coblong',
                'service_type'  => 'buy_for_customer',
                'order_mode'    => 'instant',
                'service_stage' => 'delivered',
                'title'         => 'Titip Beli Gas Elpiji 12kg & Bahan Dapur Coblong',
                'service_fee'   => 45000,
                'travel_fee'    => 5000,
                'item_fund'     => 210000,
                'matching_dist' => 1.9,
                'travel_dist'   => 1.9,
                'service_dist'  => 2.5,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 8.4,
                'store_name'    => 'Pangkalan Elpiji & Agen Sembako Tubagus',
                'store_addr'    => 'Jl. Tubagus Ismail No. 20, Coblong, Bandung',
                'store_lat'     => -6.8830, 'store_lng' => 107.6175,
                'desc'          => 'Titip beli 1 tabung gas elpiji 12kg (tukar tabung kosong) dan 1 jerigen minyak goreng 5 liter.',
                'loc'           => 'Sekeloa, Coblong, Kota Bandung',
                'addr'          => 'Jl. Tubagus Ismail VIII No. 4, Sekeloa, Coblong, Kota Bandung',
                'lat'           => -6.8852, 'lng' => 107.6189,
                'status'        => 'selesai',
                'escrow'        => 'released',
                'payment'       => 'paid',
                'date'          => '2026-09-01', 'start' => '10:30', 'end' => '12:00',
                'cust_msg'      => 'Tabung kosong sudah di depan pagar ya Kang.',
                'mitra_msg'     => 'Barang belanjaan dan tabung baru sudah sampai di lokasi.',
                'cust_rev'      => 'Struk belanja jelas dan barang lengkap sesuai pesanan.',
                'mitra_rev'     => 'Terima kasih banyak Teh.',
            ],
            [
                'order_id'      => 'HELP-20260902-SLM07',
                'cust_email'    => 'customer.sleman2@sayabantu.com',
                'mitra_email'   => 'mitra.sleman2@sayabantu.com',
                'city'          => 'Sleman', 'dist' => 'Mlati',
                'service_type'  => 'on_site_service',
                'order_mode'    => 'instant',
                'service_stage' => null,
                'title'         => 'Servis Cuci 3 Unit AC Rumah Kost Sinduadi',
                'service_fee'   => 210000,
                'travel_fee'    => 7500,
                'item_fund'     => 0,
                'matching_dist' => 3.2,
                'travel_dist'   => 3.2,
                'service_dist'  => 0,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 8.0,
                'desc'          => 'Perawatan berkala cuci steam 3 unit AC kamar kost mahasiswa untuk persiapan semester baru.',
                'loc'           => 'Sinduadi, Mlati, Sleman',
                'addr'          => 'Jl. Monjali No. 102, Sinduadi, Mlati, Sleman',
                'lat'           => -7.7550, 'lng' => 110.3680,
                'status'        => 'selesai',
                'escrow'        => 'released',
                'payment'       => 'paid',
                'date'          => '2026-09-02', 'start' => '09:00', 'end' => '11:45',
                'cust_msg'      => 'Mas Budi, posisi tangga lipat sudah siap.',
                'mitra_msg'     => 'Cuci 3 unit AC sudah selesai dan dingin normal.',
                'cust_rev'      => 'AC bersih dan hembusan angin kencang.',
                'mitra_rev'     => 'Pelanggan ramah dan proses kerja lancar.',
            ],

            // ─────────────────────────────────────────────────────────────────────
            // 7. RIWAYAT ARBITRASE / PENYELESAIAN SELESAI (RESOLVED 100%)
            // ─────────────────────────────────────────────────────────────────────
            [
                'order_id'      => 'HELP-20260902-SLM08',
                'cust_email'    => 'customer.sleman2@sayabantu.com',
                'mitra_email'   => 'mitra.sleman2@sayabantu.com',
                'city'          => 'Sleman', 'dist' => 'Mlati',
                'service_type'  => 'on_site_service',
                'order_mode'    => 'instant',
                'service_stage' => null,
                'title'         => 'Servis Modul Kulkas 2 Pintu Inverter',
                'service_fee'   => 180000,
                'travel_fee'    => 5000,
                'item_fund'     => 0,
                'matching_dist' => 2.5,
                'travel_dist'   => 2.5,
                'service_dist'  => 0,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 8.5,
                'desc'          => 'Perbaikan sensor defrost dan kipas evaporator kulkas inverter.',
                'loc'           => 'Sinduadi, Mlati, Sleman',
                'addr'          => 'Jl. Selokan Mataram No. 20, Sinduadi, Mlati, Sleman',
                'lat'           => -7.7610, 'lng' => 110.3645,
                'status'        => 'selesai',
                'escrow'        => 'released',
                'payment'       => 'paid',
                'date'          => '2026-09-02', 'start' => '13:00', 'end' => '16:00',
                'cust_msg'      => 'Kulkas sudah dingin normal sekarang setelah ditunggu.',
                'mitra_msg'     => 'Alhamdulillah freon sudah bersirkulasi penuh.',
                'cust_rev'      => 'Kulkas kembali berfungsi dingin optimal.',
                'mitra_rev'     => 'Terima kasih atas kepercayaannya.',
            ],

            // ─────────────────────────────────────────────────────────────────────
            // 8. RIWAYAT PEMBATALAN SELESAI (REFUNDED 100%)
            // ─────────────────────────────────────────────────────────────────────
            [
                'order_id'      => 'HELP-CANCEL-01',
                'cust_email'    => 'customer.jogja1@sayabantu.com',
                'mitra_email'   => null,
                'city'          => 'Yogyakarta', 'dist' => 'Danurejan',
                'service_type'  => 'on_site_service',
                'order_mode'    => 'instant',
                'service_stage' => null,
                'title'         => 'Bantu Angkut Kardus Kemasan Kopi Kafe',
                'service_fee'   => 50000,
                'travel_fee'    => 0,
                'item_fund'     => 0,
                'matching_dist' => 0,
                'travel_dist'   => 0,
                'service_dist'  => 0,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 10.0,
                'desc'          => 'Pengangkutan 10 kardus biji kopi dari mobil ekspedisi ke gudang lantai 2 kafe.',
                'loc'           => 'Danurejan, Yogyakarta',
                'addr'          => 'Jl. Mataram No. 62, Danurejan, Yogyakarta',
                'lat'           => -7.7940, 'lng' => 110.3700,
                'status'        => 'batal',
                'escrow'        => 'refunded',
                'payment'       => 'refunded',
                'date'          => '2026-08-27', 'start' => '15:00', 'end' => null,
                'cust_msg'      => null, 'mitra_msg' => null,
                'cust_rev'      => null, 'mitra_rev' => null,
                'dispute_reason'=> 'Dibatalkan oleh Customer: Kurir ekspedisi sudah bersedia membantu memasukkan kardus langsung ke gudang.',
            ],
            [
                'order_id'      => 'HELP-CANCEL-AUDIT-02',
                'cust_email'    => 'customer.bandung1@sayabantu.com',
                'mitra_email'   => 'mitra.bandung1@sayabantu.com',
                'city'          => 'Bandung', 'dist' => 'Coblong',
                'service_type'  => 'on_site_service',
                'order_mode'    => 'instant',
                'service_stage' => null,
                'title'         => 'Perbaikan Pagar Besi Terjepit & Pengelasan Engsel',
                'service_fee'   => 90000,
                'travel_fee'    => 5000,
                'item_fund'     => 0,
                'matching_dist' => 2.5,
                'travel_dist'   => 2.5,
                'service_dist'  => 0,
                'route_source'  => 'actual_provider',
                'gps_accuracy'  => 9.0,
                'desc'          => 'Pagar dorong besi macet lepas jalur rel.',
                'loc'           => 'Dago, Coblong, Kota Bandung',
                'addr'          => 'Jl. Dago Asri No. 8, Coblong, Bandung',
                'lat'           => -6.8795, 'lng' => 107.6142,
                'status'        => 'batal',
                'escrow'        => 'partial_refund',
                'payment'       => 'paid',
                'date'          => '2026-08-29', 'start' => '14:00', 'end' => '15:30',
                'cust_msg'      => 'Hujan badai sangat lebat ya Kang.',
                'mitra_msg'     => 'Iya Teh, demi keselamatan kerja las listrik kami ajukan pembatalan parsial sesuai arahan admin.',
                'cust_rev'      => null, 'mitra_rev' => null,
                'cancel_req'    => [
                    'reason'                    => 'Kondisi Darurat Cuaca Ekstrem',
                    'notes'                     => 'Hujan badai petir membahayakan alat las listrik outdoor. Roda bawah sudah diperbaiki sebagian (50%).',
                    'item_purchased'            => false,
                    'item_purchase_amount'      => 0,
                    'work_completed_percentage' => 50,
                    'status'                    => 'approved',
                    'settlement_type'           => 'partial_settlement',
                    'refund_amount'             => 45000,
                    'partner_amount'            => 45000,
                    'admin_notes'               => 'Disetujui Admin Wilayah: Kompensasi proporsional 50% untuk mitra dan pengembalian 50% ke customer.',
                ],
            ],
        ];

        // Process insertion of all tasks
        foreach ($tasksData as $t) {
            $cust = $users->get($t['cust_email']);
            $mitra = !empty($t['mitra_email']) ? $users->get($t['mitra_email']) : null;

            if (!$cust) {
                continue;
            }

            [$cityModel, $distModel] = $resolveDistCity($t['city'], $t['dist']);

            $serviceFee = (float)($t['service_fee'] ?? 50000);
            $travelFee = (float)($t['travel_fee'] ?? 0);
            $itemFund = (float)($t['item_fund'] ?? 0);
            $serviceType = $t['service_type'] ?? 'on_site_service';
            $orderMode = $t['order_mode'] ?? 'instant';

            $totalAmount = $serviceFee + $travelFee + $itemFund + $fixedPlatformFee;
            $mitraEarning = $serviceFee + $travelFee; // Partner earns service fee + travel compensation
            $orderDate = Carbon::parse($t['date'] . ' ' . $t['start']);

            $help = Help::updateOrCreate(
                ['order_id' => $t['order_id']],
                [
                    'user_id'                 => $cust->id,
                    'mitra_id'                => $mitra?->id,
                    'city_id'                 => $cityModel?->id,
                    'district_id'             => $distModel?->id,
                    'title'                   => $t['title'],
                    'description'             => $t['desc'],
                    'location'                => $t['loc'],
                    'full_address'            => $t['addr'],
                    'latitude'                => $t['lat'],
                    'longitude'               => $t['lng'],
                    // ─── REVISI 3 FINANCIAL ATTRIBUTES ───────────────────────
                    'service_type'            => $serviceType,
                    'order_mode'              => $orderMode,
                    'service_stage'           => $t['service_stage'] ?? null,
                    'amount'                  => $serviceFee,
                    'service_fee'             => $serviceFee,
                    'travel_fee'              => $travelFee,
                    'item_fund'               => $itemFund,
                    'item_fund_mode'          => $itemFund > 0 ? Help::ITEM_FUND_CUSTOMER_PAID : null,
                    'minimum_order_value'     => $serviceFee,
                    'total_amount'            => $totalAmount,
                    'platform_fee_amount'     => $fixedPlatformFee,
                    'admin_fee'               => $fixedPlatformFee,
                    'mitra_earning'           => $mitraEarning,
                    // ─── REVISI 3 GEO ATTRIBUTES ─────────────────────────────
                    'matching_distance_km'    => $t['matching_dist'] ?? 1.5,
                    'travel_distance_km'      => $t['travel_dist'] ?? 1.5,
                    'service_route_distance_km'=> $t['service_dist'] ?? 0,
                    'route_source'            => $t['route_source'] ?? 'actual_provider',
                    'gps_accuracy'            => $t['gps_accuracy'] ?? 10.0,
                    'pickup_address'          => $t['pickup_addr'] ?? null,
                    'pickup_latitude'         => $t['pickup_lat'] ?? null,
                    'pickup_longitude'        => $t['pickup_lng'] ?? null,
                    'delivery_address'        => $t['delivery_addr'] ?? $t['addr'],
                    'delivery_latitude'       => $t['delivery_lat'] ?? $t['lat'],
                    'delivery_longitude'      => $t['delivery_lng'] ?? $t['lng'],
                    'store_name'              => $t['store_name'] ?? null,
                    'store_address'           => $t['store_addr'] ?? null,
                    'store_latitude'          => $t['store_lat'] ?? null,
                    'store_longitude'         => $t['store_lng'] ?? null,
                    'partner_initial_lat'     => $t['partner_init_lat'] ?? ($mitra ? $t['lat'] + 0.01 : null),
                    'partner_initial_lng'     => $t['partner_init_lng'] ?? ($mitra ? $t['lng'] + 0.01 : null),
                    'partner_current_lat'     => $t['partner_curr_lat'] ?? ($mitra ? $t['lat'] : null),
                    'partner_current_lng'     => $t['partner_curr_lng'] ?? ($mitra ? $t['lng'] : null),
                    'partner_started_moving_at'=> ($t['status'] === 'partner_on_the_way' || $t['status'] === 'in_progress') ? $orderDate->copy()->addMinutes(12) : null,
                    'last_movement_at'        => ($t['status'] === 'partner_on_the_way' || $t['status'] === 'in_progress') ? now()->subMinutes(2) : null,
                    // ─── REVISI 3 TIMESTAMPS ─────────────────────────────────
                    'departure_at'            => $t['departure_at'] ?? null,
                    'service_scheduled_at'    => $t['sched_at'] ?? null,
                    'scheduled_at'            => $t['sched_at'] ?? null,
                    'published_at'            => $orderDate,
                    'model_version'           => 3,
                    'status'                  => $t['status'],
                    'escrow_status'           => $t['escrow'],
                    'payment_status'          => $t['payment'],
                    'rating_status'           => ($t['status'] === 'selesai') ? 'rated' : 'pending',
                    'dispatch_mode'           => ($t['status'] === 'menunggu_mitra') ? 'pool' : 'assigned',
                    'taken_at'                => $mitra ? $orderDate->copy()->addMinutes(5) : null,
                    'partner_started_at'      => $mitra ? $orderDate->copy()->addMinutes(10) : null,
                    'partner_arrived_at'      => $mitra ? $orderDate->copy()->addMinutes(25) : null,
                    'service_started_at'      => $mitra ? $orderDate->copy()->addMinutes(30) : null,
                    'service_completed_at'    => ($t['status'] === 'selesai' && !empty($t['end'])) ? Carbon::parse($t['date'] . ' ' . $t['end']) : null,
                    'completed_at'            => ($t['status'] === 'selesai' && !empty($t['end'])) ? Carbon::parse($t['date'] . ' ' . $t['end']) : null,
                    'disputed_at'             => !empty($t['disputed']) ? now()->subHours(6) : null,
                    'dispute_reason'          => $t['dispute_reason'] ?? null,
                    'created_at'              => $orderDate,
                    'updated_at'              => ($t['status'] === 'selesai' && !empty($t['end'])) ? Carbon::parse($t['date'] . ' ' . $t['end']) : now(),
                ]
            );

            // 0. Seed PartnerActivity lifecycle records for completed & cancelled jobs
            if ($cust) {
                // Customer created the order
                PartnerActivity::create([
                    'user_id'       => $cust->id,
                    'help_id'       => $help->id,
                    'activity_type' => 'help_created',
                    'description'   => "Customer membuat pesanan bantuan '{$help->title}' (Order: {$help->order_id}).",
                    'ip_address'    => '127.0.0.1',
                    'user_agent'    => 'Mozilla/5.0 (SayaBantu-Seeder)',
                    'created_at'    => $orderDate,
                    'updated_at'    => $orderDate,
                ]);
            }
            if ($mitra && $t['status'] !== 'batal') {
                // Mitra accepted the order
                $takenAt = $orderDate->copy()->addMinutes(5);
                PartnerActivity::create([
                    'user_id'       => $mitra->id,
                    'help_id'       => $help->id,
                    'activity_type' => 'help_accepted',
                    'description'   => "Mitra menerima pesanan bantuan '{$help->title}'.",
                    'ip_address'    => '127.0.0.1',
                    'user_agent'    => 'Mozilla/5.0 (SayaBantu-Seeder)',
                    'created_at'    => $takenAt,
                    'updated_at'    => $takenAt,
                ]);
                // Mitra departed to customer location
                $departedAt = $orderDate->copy()->addMinutes(10);
                PartnerActivity::create([
                    'user_id'       => $mitra->id,
                    'help_id'       => $help->id,
                    'activity_type' => 'partner_departed',
                    'description'   => "Mitra berangkat menuju lokasi customer untuk pesanan '{$help->title}'.",
                    'ip_address'    => '127.0.0.1',
                    'user_agent'    => 'Mozilla/5.0 (SayaBantu-Seeder)',
                    'created_at'    => $departedAt,
                    'updated_at'    => $departedAt,
                ]);
                // Mitra arrived at customer location
                $arrivedAt = $orderDate->copy()->addMinutes(25);
                PartnerActivity::create([
                    'user_id'       => $mitra->id,
                    'help_id'       => $help->id,
                    'activity_type' => 'partner_arrived',
                    'description'   => "Mitra tiba di lokasi customer untuk pesanan '{$help->title}'.",
                    'ip_address'    => '127.0.0.1',
                    'user_agent'    => 'Mozilla/5.0 (SayaBantu-Seeder)',
                    'created_at'    => $arrivedAt,
                    'updated_at'    => $arrivedAt,
                ]);
                // Service started
                $startedAt = $orderDate->copy()->addMinutes(30);
                PartnerActivity::create([
                    'user_id'       => $mitra->id,
                    'help_id'       => $help->id,
                    'activity_type' => 'service_started',
                    'description'   => "Mitra memulai pengerjaan bantuan '{$help->title}'.",
                    'ip_address'    => '127.0.0.1',
                    'user_agent'    => 'Mozilla/5.0 (SayaBantu-Seeder)',
                    'created_at'    => $startedAt,
                    'updated_at'    => $startedAt,
                ]);
            }
            if ($t['status'] === 'selesai' && $mitra && !empty($t['end'])) {
                $completedAt = Carbon::parse($t['date'] . ' ' . $t['end']);
                // Service completed by mitra
                PartnerActivity::create([
                    'user_id'       => $mitra->id,
                    'help_id'       => $help->id,
                    'activity_type' => 'service_completed',
                    'description'   => "Mitra menyelesaikan pekerjaan bantuan '{$help->title}' dan mengajukan konfirmasi selesai.",
                    'ip_address'    => '127.0.0.1',
                    'user_agent'    => 'Mozilla/5.0 (SayaBantu-Seeder)',
                    'created_at'    => $completedAt->copy()->subMinutes(2),
                    'updated_at'    => $completedAt->copy()->subMinutes(2),
                ]);
                // Customer confirmed completion
                PartnerActivity::create([
                    'user_id'       => $cust->id,
                    'help_id'       => $help->id,
                    'activity_type' => 'confirm_completion',
                    'description'   => "Customer mengkonfirmasi penyelesaian bantuan '{$help->title}'.",
                    'ip_address'    => '127.0.0.1',
                    'user_agent'    => 'Mozilla/5.0 (SayaBantu-Seeder)',
                    'created_at'    => $completedAt,
                    'updated_at'    => $completedAt,
                ]);
            }
            if ($t['status'] === 'batal' && $cust) {
                $cancelAt = $orderDate->copy()->addMinutes(20);
                PartnerActivity::create([
                    'user_id'       => $cust->id,
                    'help_id'       => $help->id,
                    'activity_type' => 'help_cancelled',
                    'description'   => "Pesanan bantuan '{$help->title}' dibatalkan. Alasan: " . ($t['dispute_reason'] ?? 'Dibatalkan oleh customer.'),
                    'ip_address'    => '127.0.0.1',
                    'user_agent'    => 'Mozilla/5.0 (SayaBantu-Seeder)',
                    'created_at'    => $cancelAt,
                    'updated_at'    => $cancelAt,
                ]);
            }

            // 1. Seed Ratings & Reviews for completed jobs
            if ($t['status'] === 'selesai' && $mitra && !empty($t['cust_rev'])) {
                Rating::updateOrCreate(
                    ['help_id' => $help->id, 'type' => 'customer_to_mitra'],
                    [
                        'rater_id'    => $cust->id,
                        'ratee_id'    => $mitra->id,
                        'rating'      => 5,
                        'review'      => $t['cust_rev'],
                        'created_at'  => $help->completed_at ?? now(),
                        'updated_at'  => $help->completed_at ?? now(),
                    ]
                );

                if (!empty($t['mitra_rev'])) {
                    Rating::updateOrCreate(
                        ['help_id' => $help->id, 'type' => 'mitra_to_customer'],
                        [
                            'rater_id'    => $mitra->id,
                            'ratee_id'    => $cust->id,
                            'rating'      => 5,
                            'review'      => $t['mitra_rev'],
                            'created_at'  => $help->completed_at ?? now(),
                            'updated_at'  => $help->completed_at ?? now(),
                        ]
                    );
                    // PartnerActivity: rating submitted by mitra
                    PartnerActivity::create([
                        'user_id'       => $mitra->id,
                        'help_id'       => $help->id,
                        'activity_type' => 'help_reviewed',
                        'description'   => "Mitra memberikan penilaian untuk pesanan '{$help->title}'.",
                        'ip_address'    => '127.0.0.1',
                        'user_agent'    => 'Mozilla/5.0 (SayaBantu-Seeder)',
                        'created_at'    => ($help->completed_at ?? now())->copy()->addMinutes(5),
                        'updated_at'    => ($help->completed_at ?? now())->copy()->addMinutes(5),
                    ]);
                }
                // PartnerActivity: rating submitted by customer
                PartnerActivity::create([
                    'user_id'       => $cust->id,
                    'help_id'       => $help->id,
                    'activity_type' => 'help_reviewed',
                    'description'   => "Customer memberikan penilaian untuk pesanan '{$help->title}'.",
                    'ip_address'    => '127.0.0.1',
                    'user_agent'    => 'Mozilla/5.0 (SayaBantu-Seeder)',
                    'created_at'    => ($help->completed_at ?? now())->copy()->addMinutes(3),
                    'updated_at'    => ($help->completed_at ?? now())->copy()->addMinutes(3),
                ]);
            }

            // 2. Seed Chat Messages
            if ($mitra && !empty($t['cust_msg'])) {
                Chat::firstOrCreate(
                    [
                        'help_id'     => $help->id,
                        'sender_type' => 'customer',
                        'message'     => $t['cust_msg'],
                    ],
                    [
                        'mitra_id'    => $mitra->id,
                        'customer_id' => $cust->id,
                        'is_read'     => true,
                        'read_at'     => $orderDate->copy()->addMinutes(8),
                        'created_at'  => $orderDate->copy()->addMinutes(7),
                        'updated_at'  => $orderDate->copy()->addMinutes(7),
                    ]
                );

                if (!empty($t['mitra_msg'])) {
                    Chat::firstOrCreate(
                        [
                            'help_id'     => $help->id,
                            'sender_type' => 'mitra',
                            'message'     => $t['mitra_msg'],
                        ],
                        [
                            'mitra_id'    => $mitra->id,
                            'customer_id' => $cust->id,
                            'is_read'     => true,
                            'read_at'     => $orderDate->copy()->addMinutes(10),
                            'created_at'  => $orderDate->copy()->addMinutes(9),
                            'updated_at'  => $orderDate->copy()->addMinutes(9),
                        ]
                    );
                }
            }

            // 3. Seed HelpCancelRequest records if present (Revisi 3 Special Cancellation)
            if (!empty($t['cancel_req']) && $mitra) {
                $cData = $t['cancel_req'];
                $adminUser = User::where('role', 'admin')->first();

                HelpCancelRequest::updateOrCreate(
                    ['help_id' => $help->id],
                    [
                        'partner_id'                => $mitra->id,
                        'district_id'               => $help->district_id,
                        'previous_status'           => 'partner_on_the_way',
                        'previous_stage'            => $t['service_stage'] ?? null,
                        'reason'                    => $cData['reason'],
                        'notes'                     => $cData['notes'] ?? null,
                        'item_purchased'            => (bool)($cData['item_purchased'] ?? false),
                        'item_purchase_amount'      => (float)($cData['item_purchase_amount'] ?? 0),
                        'work_completed_percentage' => (int)($cData['work_completed_percentage'] ?? 0),
                        'status'                    => $cData['status'] ?? 'pending',
                        'settlement_type'           => $cData['settlement_type'] ?? null,
                        'refund_amount_customer'    => $cData['refund_amount'] ?? 0,
                        'payout_amount_mitra'       => $cData['partner_amount'] ?? 0,
                        'admin_notes'               => $cData['admin_notes'] ?? null,
                        'reviewed_by'               => ($cData['status'] === 'approved') ? $adminUser?->id : null,
                        'requested_at'              => $orderDate->copy()->addMinutes(15),
                        'reviewed_at'               => ($cData['status'] === 'approved') ? $orderDate->copy()->addMinutes(30) : null,
                    ]
                );
            }
        }

        $this->command->info('HelpsSeeder berhasil memuat ragam kegiatan otentik Indonesia (FULL PLAN REVISI 3: On-Site, Pickup & Delivery, Buy For Customer, Sengketa, & Audit Pembatalan) di seluruh wilayah.');
    }
}
