<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Help;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class HelpsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Membuat data bantuan realistis di wilayah Kabupaten Sleman (Model v2).
     */
    public function run(): void
    {
        // Pastikan folder storage/app/public/helps ada
        $storageHelpsPath = storage_path('app/public/helps');
        if (!File::exists($storageHelpsPath)) {
            File::makeDirectory($storageHelpsPath, 0755, true);
        }

        // Ambil customer dan mitra di Sleman
        $customer = User::where('email', 'customer@sayabantu.com')->first();
        $mitra    = User::where('email', 'mitra@sayabantu.com')->first();

        // Cari Kota/Kabupaten Sleman
        $slemanCity = City::where('name', 'like', '%Sleman%')->first();
        $slemanCityId = $slemanCity ? $slemanCity->id : 1;

        if (!$customer) {
            $this->command->warn('Customer Sleman belum dibuat. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        // Daftar kategori
        $categories = Category::pluck('id', 'name')->toArray();

        $helpsData = [
            [
                'title'         => 'Bantu Pindahan & Angkat Barang Kos Dekat Kampus UNY/UGM',
                'description'   => 'Butuh bantuan 1 orang untuk membantu angkut kasur busa, 2 kardus buku, kipas angin, dan meja lipat dari kos lama ke kos baru berjarak 800 meter. Kos lama di lantai 2. Waktu fleksibel siang ini.',
                'amount'        => 80000,
                'category_name' => 'Angkut & Pindahan Kost',
                'location'      => 'Caturtunggal, Kec. Depok, Sleman',
                'full_address'  => 'Jl. Karangmalang Blok A No. 14, Caturtunggal, Kec. Depok, Kabupaten Sleman, D.I. Yogyakarta 55281',
                'latitude'      => -7.7712000,
                'longitude'     => 110.3854000,
                'status'        => Help::STATUS_MENUNGGU_MITRA,
                'mitra_id'      => null,
                'taken_at'      => null,
                'completed_at'  => null,
            ],
            [
                'title'         => 'Pembersihan Halaman Rumah & Potong Rumput Rimbun',
                'description'   => 'Halaman depan dan samping rumah (luas sekitar 4x6 meter) rumputnya sudah cukup tinggi dan banyak daun kering. Butuh bantuan untuk memotong rumput, mencabuti ilalang, dan memasukkan sampah ke dalam karung.',
                'amount'        => 75000,
                'category_name' => 'Kebersihan & Taman',
                'location'      => 'Sinduadi, Kec. Mlati, Sleman',
                'full_address'  => 'Jl. Selokan Mataram No. 27, Pogung Dalangan, Sinduadi, Kec. Mlati, Kabupaten Sleman, D.I. Yogyakarta 55284',
                'latitude'      => -7.7610000,
                'longitude'     => 110.3725000,
                'status'        => Help::STATUS_MENUNGGU_MITRA,
                'mitra_id'      => null,
                'taken_at'      => null,
                'completed_at'  => null,
            ],
            [
                'title'         => 'Perbaikan Pompa Air Macet & Ganti Sambungan Pipa Paralon',
                'description'   => 'Pompa air Shimizu di rumah berdengung tapi air tidak naik ke toren. Kemungkinan perlu dipancing atau sambungan pipa hisap bocor. Butuh mitra yang paham teknis pompa air ringan.',
                'amount'        => 90000,
                'category_name' => 'Pertukangan & Teknisi',
                'location'      => 'Sardonoharjo, Kec. Ngaglik, Sleman',
                'full_address'  => 'Jl. Kaliurang KM 10 No. 52, Gentan, Sardonoharjo, Kec. Ngaglik, Kabupaten Sleman, D.I. Yogyakarta 55581',
                'latitude'      => -7.7085000,
                'longitude'     => 110.4072000,
                'status'        => Help::STATUS_MENUNGGU_MITRA,
                'mitra_id'      => null,
                'taken_at'      => null,
                'completed_at'  => null,
            ],
            [
                'title'         => 'Bantu Rakit Meja Belajar & Lemari Pakaian Minimalis',
                'description'   => 'Baru beli meja kerja dan lemari pakaian knock-down dari marketplace tapi kesulitan merakitnya sendiri. Buku panduan dan baut lengkap tersedia. Butuh bantuan merakit sampai selesai dan kokoh.',
                'amount'        => 60000,
                'category_name' => 'Rumah Tangga',
                'location'      => 'Banyuraden, Kec. Gamping, Sleman',
                'full_address'  => 'Jl. Ringroad Barat No. 108, Banyuraden, Kec. Gamping, Kabupaten Sleman, D.I. Yogyakarta 55293',
                'latitude'      => -7.7942000,
                'longitude'     => 110.3381000,
                'status'        => Help::STATUS_TAKEN,
                'mitra_id'      => $mitra ? $mitra->id : null,
                'taken_at'      => now()->subHours(2),
                'completed_at'  => null,
            ],
            [
                'title'         => 'Antar Berkas Dokumen Mendesak ke Kantor Pemda Sleman',
                'description'   => 'Butuh kurir lokal cepat untuk mengantarkan map dokumen penting dari daerah Caturtunggal langsung ke Kantor Dinas Kependudukan Pemda Sleman sebelum jam tutup kantor siang ini.',
                'amount'        => 45000,
                'category_name' => 'Antar Jemput & Kurir',
                'location'      => 'Tridadi, Kec. Sleman, Sleman',
                'full_address'  => 'Jl. Magelang KM 10, Beran Lor, Tridadi, Kec. Sleman, Kabupaten Sleman, D.I. Yogyakarta 55511',
                'latitude'      => -7.6985000,
                'longitude'     => 110.3542000,
                'status'        => Help::STATUS_SELESAI,
                'mitra_id'      => $mitra ? $mitra->id : null,
                'taken_at'      => now()->subDays(2),
                'completed_at'  => now()->subDays(2)->addHours(1),
            ],
            [
                'title'         => 'Jasa Cuci & Kuras Tandon Air Rumah Tangga (1000 Liter)',
                'description'   => 'Tandon air di atas dak rumah sudah berlumut karena 6 bulan belum dibersihkan. Butuh bantuan menguras dan menyikat bagian dalam tandon kapasitas 1000 liter. Tangga dan kran pembuangan tersedia.',
                'amount'        => 100000,
                'category_name' => 'Kebersihan & Taman',
                'location'      => 'Purwomartani, Kec. Kalasan, Sleman',
                'full_address'  => 'Jl. Candi Sambisari No. 18, Purwomartani, Kec. Kalasan, Kabupaten Sleman, D.I. Yogyakarta 55571',
                'latitude'      => -7.7650000,
                'longitude'     => 110.4485000,
                'status'        => Help::STATUS_SELESAI,
                'mitra_id'      => $mitra ? $mitra->id : null,
                'taken_at'      => now()->subDays(1),
                'completed_at'  => now()->subDays(1)->addHours(2),
            ],
        ];

        $fixedPlatformFee = \App\Models\AppSetting::getPlatformServiceFee();

        foreach ($helpsData as $index => $data) {
            $catId = $categories[$data['category_name']] ?? (array_values($categories)[0] ?? 1);
            $amount = (float) $data['amount'];
            $platformFee = $fixedPlatformFee;
            $mitraEarning = $amount; // Mitra menerima 100% penuh dari nilai bantuan

            Help::updateOrCreate(
                [
                    'title'   => $data['title'],
                    'user_id' => $customer->id,
                ],
                [
                    'city_id'                  => $slemanCityId,
                    'category_id'              => $catId,
                    'order_id'                 => sprintf('HELP-SLM-%03d', $index + 1),
                    'description'              => $data['description'],
                    'amount'                   => $amount,
                    'admin_fee'                => $platformFee,
                    'total_amount'             => $amount + $platformFee,
                    'location'                 => $data['location'],
                    'full_address'             => $data['full_address'],
                    'latitude'                 => $data['latitude'],
                    'longitude'                => $data['longitude'],
                    'status'                   => $data['status'],
                    'mitra_id'                 => $data['mitra_id'],
                    'taken_at'                 => $data['taken_at'],
                    'completed_at'             => $data['completed_at'],
                    // Model v2 Escrow & Split Payment Snapshot
                    'model_version'            => 2,
                    'platform_commission_rate' => 0.00,
                    'platform_fee_amount'      => $platformFee,
                    'mitra_earning'            => $mitraEarning,
                    'escrow_locked_at'         => now()->subDays(3),
                ]
            );
        }

        $this->command->info('HelpsSeeder berhasil membuat ' . count($helpsData) . ' data bantuan riil di wilayah Sleman (Model v2).');
    }
}
