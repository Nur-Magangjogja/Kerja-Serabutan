<?php

namespace Database\Seeders;

use App\Models\Help;
use App\Models\User;
use App\Models\City;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class HelpsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan folder storage/app/public/helps ada
        $storageHelpsPath = storage_path('app/public/helps');
        if (!File::exists($storageHelpsPath)) {
            File::makeDirectory($storageHelpsPath, 0755, true);
        }

        // Data bantuan realistis siap diambil oleh Mitra (status mentah: menunggu_mitra)
        $helps = [
            [
                'title' => 'Perbaikan Atap Rumah Bocor di Musim Hujan',
                'description' => 'Atap rumah saya bocor parah saat musim hujan. Air masuk ke kamar tidur dan merusak kasur serta lemari. Butuh tukang untuk menambal dan mengganti genteng yang pecah sekitar 20 buah. Lokasi mudah dijangkau di pusat kota.',
                'amount' => 150000,
                'category_name' => 'Rumah Tangga',
                'location' => 'Kelurahan Mangkujayan, Ponorogo',
                'full_address' => 'Jalan Jendral Sudirman No. 45, Kelurahan Mangkujayan, Kecamatan Ponorogo, Kabupaten Ponorogo, Jawa Timur 63419',
                'latitude' => -7.8698,
                'longitude' => 111.4619,
                'photo' => 'helps/atap-bocor.jpg',
            ],
            [
                'title' => 'Bantuan Obat Diabetes dan Pemeriksaan Gula Darah',
                'description' => 'Saya penderita diabetes yang membutuhkan obat rutin setiap bulan. Saat ini stok obat habis dan belum ada biaya untuk membeli. Butuh bantuan untuk membeli metformin, glimepiride, dan alat cek gula darah. Resep dokter tersedia.',
                'amount' => 200000,
                'category_name' => 'Kesehatan',
                'location' => 'Kelurahan Tonatan, Ponorogo',
                'full_address' => 'Jalan Soekarno Hatta No. 123, Kelurahan Tonatan, Kecamatan Ponorogo, Kabupaten Ponorogo, Jawa Timur 63418',
                'latitude' => -7.8723,
                'longitude' => 111.4701,
                'photo' => 'helps/obat-diabetes.jpg',
            ],
            [
                'title' => 'Transportasi Ibu Hamil ke Rumah Sakit untuk Kontrol',
                'description' => 'Istri saya sedang hamil 8 bulan dan harus rutin kontrol ke Rumah Sakit setiap minggu. Kami tidak punya kendaraan dan biaya ojek online cukup mahal untuk pulang-pergi. Butuh bantuan antar-jemput dari rumah ke RS Ponorogo atau bantuan biaya transportasi.',
                'amount' => 100000,
                'category_name' => 'Transportasi',
                'location' => 'Desa Banjarejo, Ponorogo',
                'full_address' => 'Dusun Krajan RT 02 RW 01, Desa Banjarejo, Kecamatan Balong, Kabupaten Ponorogo, Jawa Timur 63515',
                'latitude' => -7.9012,
                'longitude' => 111.4523,
                'photo' => 'helps/ibu-hamil.jpg',
            ],
            [
                'title' => 'Bantuan Beras 10 Kg untuk Keluarga Kurang Mampu',
                'description' => 'Keluarga kami terdiri dari 5 orang (saya, istri, dan 3 anak). Penghasilan hanya dari buruh tani yang tidak menentu. Saat ini persediaan beras habis dan belum ada uang untuk membeli. Mohon bantuan beras minimal 10 kg agar anak-anak bisa makan.',
                'amount' => 120000,
                'category_name' => 'Pangan',
                'location' => 'Desa Sumberejo, Ponorogo',
                'full_address' => 'Dukuh Sabet RT 03 RW 02, Desa Sumberejo, Kecamatan Balong, Kabupaten Ponorogo, Jawa Timur 63515',
                'latitude' => -7.9123,
                'longitude' => 111.4389,
                'photo' => 'helps/beras.jpg',
            ],
            [
                'title' => 'Perlengkapan Sekolah Anak Kelas 1 SD',
                'description' => 'Anak saya baru masuk kelas 1 SD tahun ini. Butuh bantuan untuk membeli seragam, sepatu, tas, buku tulis, dan alat tulis. Sekolah di SD Negeri 1 Ponorogo sudah mulai dan anak saya belum punya perlengkapan lengkap.',
                'amount' => 250000,
                'category_name' => 'Pendidikan',
                'location' => 'Kelurahan Banyudono, Ponorogo',
                'full_address' => 'Jalan Kenanga No. 17, Kelurahan Banyudono, Kecamatan Ponorogo, Kabupaten Ponorogo, Jawa Timur 63418',
                'latitude' => -7.8654,
                'longitude' => 111.4578,
                'photo' => 'helps/perlengkapan-sekolah.jpg',
            ],
            [
                'title' => 'Perbaikan Pipa Air PDAM yang Bocor di Halaman',
                'description' => 'Pipa PDAM di halaman rumah bocor besar sejak seminggu lalu. Tagihan air membengkak dan halaman jadi becek. Sudah lapor ke PDAM tapi belum ada teknisi yang datang. Butuh tukang ledeng yang bisa segera memperbaiki sebelum tagihan makin besar.',
                'amount' => 180000,
                'category_name' => 'Rumah Tangga',
                'location' => 'Kelurahan Surodikraman, Ponorogo',
                'full_address' => 'Jalan Gatot Subroto No. 89, Kelurahan Surodikraman, Kecamatan Ponorogo, Kabupaten Ponorogo, Jawa Timur 63419',
                'latitude' => -7.8756,
                'longitude' => 111.4645,
                'photo' => 'helps/pipa-bocor.jpg',
            ],
            [
                'title' => 'Jasa Cuci Karpet Masjid Ukuran 6x8 Meter',
                'description' => 'Karpet masjid kampung kami sudah kotor dan berbau. Ukuran sekitar 6x8 meter. Butuh jasa cuci karpet profesional yang bisa angkut, cuci, dan antar kembali. Dana dari kas masjid tidak cukup sehingga butuh bantuan dari mitra.',
                'amount' => 220000,
                'category_name' => 'Sosial',
                'location' => 'Desa Kauman, Ponorogo',
                'full_address' => 'Jalan Masjid Agung No. 3, Desa Kauman, Kecamatan Ponorogo, Kabupaten Ponorogo, Jawa Timur 63419',
                'latitude' => -7.8689,
                'longitude' => 111.4601,
                'photo' => 'helps/karpet-masjid.jpg',
            ],
            [
                'title' => 'Pemasangan Lampu dan Stop Kontak di Kamar Baru',
                'description' => 'Baru renovasi rumah dan menambah satu kamar. Butuh teknisi listrik untuk memasang 4 lampu LED, 3 stop kontak, dan 2 saklar. Kabel sudah tersedia, tinggal pemasangan. Harus selesai minggu ini karena kamar akan ditempati.',
                'amount' => 175000,
                'category_name' => 'Rumah Tangga',
                'location' => 'Kelurahan Ronowijayan, Ponorogo',
                'full_address' => 'Jalan Pemuda No. 56, Kelurahan Ronowijayan, Kecamatan Ponorogo, Kabupaten Ponorogo, Jawa Timur 63419',
                'latitude' => -7.8612,
                'longitude' => 111.4589,
                'photo' => 'helps/pasang-lampu.jpg',
            ],
            [
                'title' => 'Bantuan Bayar Tagihan Listrik Bulan Ini Rp 350.000',
                'description' => 'Tagihan listrik bulan ini Rp 350.000 dan sudah lewat jatuh tempo. Khawatir listrik akan diputus. Suami saya sakit sehingga tidak bisa bekerja bulan ini. Mohon bantuan untuk membayar tagihan agar listrik tidak diputus karena ada balita di rumah.',
                'amount' => 350000,
                'category_name' => 'Rumah Tangga',
                'location' => 'Kelurahan Kertosari, Ponorogo',
                'full_address' => 'Jalan Raya Kertosari No. 78, Kelurahan Kertosari, Kecamatan Babadan, Kabupaten Ponorogo, Jawa Timur 63491',
                'latitude' => -7.8456,
                'longitude' => 111.5012,
                'photo' => 'helps/tagihan-listrik.jpg',
            ],
            [
                'title' => 'Jasa Antar Paket ke Madiun (Barang Kecil 2 Kg)',
                'description' => 'Saya punya paket berisi dokumen penting yang harus sampai ke Madiun hari ini atau besok. Ukuran kecil sekitar 2 kg. Ekspedisi terdekat sudah tutup. Butuh seseorang yang kebetulan ke Madiun untuk mengantarkan. Alamat tujuan di pusat kota Madiun.',
                'amount' => 80000,
                'category_name' => 'Transportasi',
                'location' => 'Kelurahan Nologaten, Ponorogo',
                'full_address' => 'Jalan Basuki Rahmat No. 234, Kelurahan Nologaten, Kecamatan Ponorogo, Kabupaten Ponorogo, Jawa Timur 63419',
                'latitude' => -7.8734,
                'longitude' => 111.4712,
                'photo' => 'helps/kirim-paket.jpg',
            ],
        ];

        // Cari atau buat customer
        $customers = User::where('role', 'customer')->get();
        if ($customers->isEmpty()) {
            $defaultCustomer = User::firstOrCreate(
                ['email' => 'budi@example.com'],
                [
                    'name' => 'Budi Santoso',
                    'password' => bcrypt('password'),
                    'role' => 'customer',
                    'city_id' => 1,
                    'verified' => true,
                    'status' => 'active',
                ]
            );
            $customers = collect([$defaultCustomer]);
        }

        // Cari kota Ponorogo atau kota yang tersedia
        $ponorogoCity = City::where('name', 'like', '%Ponorogo%')->first();
        $defaultCityId = $ponorogoCity ? $ponorogoCity->id : (City::first()->id ?? 1);

        // Buat dummy images jika belum ada
        $this->createPlaceholderImages();

        foreach ($helps as $index => $helpData) {
            // Assign customer secara merata
            $customer = $customers[$index % $customers->count()];

            // Cari kategori
            $category = Category::where('name', $helpData['category_name'])->first();
            $categoryId = $category ? $category->id : null;

            // Generate order_id unik
            $orderId = sprintf('SB-HELP-%s-%03d', date('Ymd'), $index + 1);

            $data = [
                'user_id'       => $customer->id,
                'city_id'       => $defaultCityId,
                'title'         => $helpData['title'],
                'description'   => $helpData['description'],
                'amount'        => $helpData['amount'],
                'location'      => $helpData['location'],
                'photo'         => $helpData['photo'],
                'status'        => 'menunggu_mitra', // Semua data mentah menunggu mitra
                'order_id'      => $orderId,
                'scheduled_at'  => now()->addHours(2 + ($index * 3)),
                'created_at'    => now()->subMinutes(120 - ($index * 10)),
                'updated_at'    => now()->subMinutes(120 - ($index * 10)),
            ];

            if ($categoryId && Schema::hasColumn('helps', 'category_id')) {
                $data['category_id'] = $categoryId;
            }

            if (Schema::hasColumn('helps', 'full_address')) {
                $data['full_address'] = $helpData['full_address'];
                $data['latitude']     = $helpData['latitude'];
                $data['longitude']    = $helpData['longitude'];
            }

            Help::create($data);
        }

        if (isset($this->command)) {
            $this->command->info('✅ Berhasil membuat 10 data bantuan mentah (status: menunggu_mitra)!');
        }
    }

    /**
     * Create placeholder images for helps using GD
     */
    private function createPlaceholderImages(): void
    {
        $images = [
            'atap-bocor.jpg'           => ['title' => 'PERBAIKAN ATAP BOCOR', 'bg' => [45, 85, 125]],
            'obat-diabetes.jpg'        => ['title' => 'BANTUAN OBAT MEDIS', 'bg' => [34, 139, 94]],
            'ibu-hamil.jpg'            => ['title' => 'TRANSPORTASI KONTROL RS', 'bg' => [180, 83, 9]],
            'beras.jpg'                => ['title' => 'BANTUAN PANGAN BERAS', 'bg' => [14, 116, 144]],
            'perlengkapan-sekolah.jpg' => ['title' => 'PERLENGKAPAN SEKOLAH SD', 'bg' => [109, 40, 217]],
            'pipa-bocor.jpg'           => ['title' => 'PERBAIKAN PIPA PDAM', 'bg' => [3, 105, 161]],
            'karpet-masjid.jpg'        => ['title' => 'CUCI KARPET MASJID', 'bg' => [190, 24, 93]],
            'pasang-lampu.jpg'         => ['title' => 'INSTALASI LISTRIK LAMPU', 'bg' => [161, 98, 7]],
            'tagihan-listrik.jpg'      => ['title' => 'TAGIHAN LISTRIK BULANAN', 'bg' => [185, 28, 28]],
            'kirim-paket.jpg'          => ['title' => 'PENGIRIMAN PAKET DOKUMEN', 'bg' => [67, 56, 202]],
        ];

        foreach ($images as $filename => $info) {
            $path = storage_path('app/public/helps/' . $filename);

            if (!file_exists($path) || filesize($path) === 0) {
                if (extension_loaded('gd')) {
                    $width = 700;
                    $height = 480;
                    $img = imagecreatetruecolor($width, $height);

                    $bg = imagecolorallocate($img, $info['bg'][0], $info['bg'][1], $info['bg'][2]);
                    $white = imagecolorallocate($img, 255, 255, 255);
                    $dark = imagecolorallocate($img, 15, 23, 42);
                    $accent = imagecolorallocate($img, 245, 158, 11);
                    $subColor = imagecolorallocate($img, 226, 232, 240);

                    // Background & Panel
                    imagefilledrectangle($img, 0, 0, $width, $height, $bg);
                    imagefilledrectangle($img, 30, 30, $width - 30, $height - 30, $dark);

                    // Badge status mentah
                    imagefilledrectangle($img, 50, 50, 290, 85, $accent);
                    imagestring($img, 5, 65, 60, "STATUS: MENUNGGU MITRA", $dark);

                    // Title
                    imagestring($img, 5, 50, 130, $info['title'], $white);

                    // Watermark / details
                    imagestring($img, 4, 50, 200, "SAYABANTU - LAYANAN BANTUAN SERABUTAN", $subColor);
                    imagestring($img, 3, 50, 240, "Lokasi: Area Ponorogo & Sekitarnya", $subColor);
                    imagestring($img, 3, 50, 270, "Status Permohonan: Terbuka untuk Diambil Rekan Jasa", imagecolorallocate($img, 52, 211, 153));

                    // Footer bar
                    imagefilledrectangle($img, 30, $height - 60, $width - 30, $height - 30, imagecolorallocate($img, 30, 41, 59));
                    imagestring($img, 3, 50, $height - 45, "SayaBantu Digital Request Record", $white);

                    imagejpeg($img, $path, 85);
                    imagedestroy($img);
                } else {
                    file_put_contents($path, "DUMMY_IMAGE_{$info['title']}");
                }
            }
        }
    }
}
