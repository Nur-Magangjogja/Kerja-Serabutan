<?php

namespace Database\Seeders;

use App\Models\Help;
use App\Models\User;
use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class HelpsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan folder storage/helps ada
        if (!File::exists(storage_path('app/public/helps'))) {
            File::makeDirectory(storage_path('app/public/helps'), 0755, true);
        }

        // Data bantuan realistis untuk Ponorogo dan sekitarnya
        $helps = [
            [
                'title' => 'Perbaikan Atap Rumah Bocor di Musim Hujan',
                'description' => 'Atap rumah saya bocor parah saat musim hujan. Air masuk ke kamar tidur dan merusak kasur serta lemari. Butuh tukang untuk menambal dan mengganti genteng yang pecah sekitar 20 buah. Lokasi mudah dijangkau di pusat kota.',
                'amount' => 150000,
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
                'location' => 'Kelurahan Nologaten, Ponorogo',
                'full_address' => 'Jalan Basuki Rahmat No. 234, Kelurahan Nologaten, Kecamatan Ponorogo, Kabupaten Ponorogo, Jawa Timur 63419',
                'latitude' => -7.8734,
                'longitude' => 111.4712,
                'photo' => 'helps/kirim-paket.jpg',
            ],
        ];

        // Get Budi user only
        $budiUser = User::where('email', 'budi@example.com')->first();
        
        if (!$budiUser) {
            $this->command->error('❌ User budi@example.com tidak ditemukan! Pastikan UserSeeder sudah dijalankan.');
            return;
        }

        $cities = City::all();
        if ($cities->isEmpty()) {
            $cities = collect([['id' => 1]]); // fallback
        }

        // Create placeholder images
        $this->createPlaceholderImages();

        foreach ($helps as $index => $helpData) {
            $data = [
                'user_id' => $budiUser->id,
                'city_id' => $cities->random()->id,
                'title' => $helpData['title'],
                'description' => $helpData['description'],
                'amount' => $helpData['amount'],
                'location' => $helpData['location'],
                'photo' => $helpData['photo'],
                'status' => 'menunggu_mitra',
            ];

            // Add map fields if they exist
            if (Schema::hasColumn('helps', 'full_address')) {
                $data['full_address'] = $helpData['full_address'];
                $data['latitude'] = $helpData['latitude'];
                $data['longitude'] = $helpData['longitude'];
            }

            Help::create($data);
        }

        $this->command->info('✅ Berhasil membuat 10 data bantuan dengan detail lengkap!');
    }

    /**
     * Create placeholder images for helps
     */
    private function createPlaceholderImages()
    {
        $images = [
            'atap-bocor.jpg',
            'obat-diabetes.jpg',
            'ibu-hamil.jpg',
            'beras.jpg',
            'perlengkapan-sekolah.jpg',
            'pipa-bocor.jpg',
            'karpet-masjid.jpg',
            'pasang-lampu.jpg',
            'tagihan-listrik.jpg',
            'kirim-paket.jpg',
        ];

        foreach ($images as $image) {
            $path = storage_path('app/public/helps/' . $image);

            // Create a simple placeholder image using GD
            if (!file_exists($path)) {
                $width = 800;
                $height = 600;
                $img = imagecreatetruecolor($width, $height);

                // Random soft colors
                $colors = [
                    [173, 216, 230], // Light Blue
                    [144, 238, 144], // Light Green
                    [255, 182, 193], // Light Pink
                    [255, 218, 185], // Peach
                    [221, 160, 221], // Plum
                    [176, 224, 230], // Powder Blue
                    [255, 239, 213], // Papaya Whip
                    [240, 230, 140], // Khaki
                    [255, 228, 225], // Misty Rose
                    [230, 230, 250], // Lavender
                ];

                $colorIndex = array_rand($colors);
                $bgColor = imagecolorallocate($img, $colors[$colorIndex][0], $colors[$colorIndex][1], $colors[$colorIndex][2]);
                imagefill($img, 0, 0, $bgColor);

                // Add text
                $textColor = imagecolorallocate($img, 60, 60, 60);
                $text = strtoupper(str_replace(['.jpg', '-'], ['', ' '], $image));

                // Center text
                $fontSize = 5;
                $textWidth = imagefontwidth($fontSize) * strlen($text);
                $textHeight = imagefontheight($fontSize);
                $x = ($width - $textWidth) / 2;
                $y = ($height - $textHeight) / 2;

                imagestring($img, $fontSize, $x, $y, $text, $textColor);

                // Add icon/symbol
                $white = imagecolorallocate($img, 255, 255, 255);
                imagefilledellipse($img, $width / 2, $height / 2 - 50, 100, 100, $white);

                imagejpeg($img, $path, 90);
                imagedestroy($img);
            }
        }
    }
}
