<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\Chat;
use App\Models\City;
use App\Models\Help;
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
     * Mengisi riwayat bantuan terselesaikan untuk SETIAP HARI dari 4 Agustus s/d 3 September 2026 (31 Hari Penuh).
     * 0 Tugas Sedang Berjalan & 0 Tugas di Pool.
     */
    public function run(): void
    {
        $storageHelpsPath = storage_path('app/public/helps');
        if (!File::exists($storageHelpsPath)) {
            File::makeDirectory($storageHelpsPath, 0755, true);
        }

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

        $slemanCity = City::where('name', 'like', '%Sleman%')->first();
        $soloCity   = City::where('name', 'like', '%Surakarta%')->first();

        $slemanCityId = $slemanCity ? $slemanCity->id : 1;
        $soloCityId   = $soloCity ? $soloCity->id : 2;

        $fixedPlatformFee = (float) AppSetting::getPlatformServiceFee();

        // 31 Hari Lengkap: 4 Agustus 2026 s/d 3 September 2026
        $dailyTasks = [
            // Day 1: 04 Agustus 2026 (Sleman)
            [
                'order_id'       => 'HELP-20260804-SLM01',
                'customer'       => $custSleman1,
                'mitra'          => $mitraSleman1,
                'city_id'        => $slemanCityId,
                'title'          => 'Bantu Angkut Kasur Busa & Box Buku Kos Gejayan',
                'description'    => 'Pindahan kosan berjarak 500 meter, butuh 1 orang membantu angkut kasur busa tebal dan 3 kardus buku dari lantai 2.',
                'amount'         => 80000,
                'location'       => 'Caturtunggal, Kec. Depok, Sleman',
                'full_address'   => 'Jl. Affandi (Gejayan) No. 45, Santren, Caturtunggal, Kec. Depok, Sleman',
                'lat'            => -7.7712000,
                'lng'            => 110.3854000,
                'date'           => '2026-08-04',
                'start_time'     => '09:00',
                'end_time'       => '11:15',
                'cust_msg'       => 'Halo Mas Agus, saya tunggu di depan gerbang kos ya.',
                'mitra_msg'      => 'Siap Bu Rina, saya meluncur bawa tali tambang.',
                'cust_rev'       => 'Mas Agus sangat cekatan dan sopan. Sangat membantu.',
                'mitra_rev'      => 'Customer sangat ramah dan komunikasi lancar.',
            ],
            // Day 2: 05 Agustus 2026 (Solo)
            [
                'order_id'       => 'HELP-20260805-SKT01',
                'customer'       => $custSolo1,
                'mitra'          => $mitraSolo1,
                'city_id'        => $soloCityId,
                'title'          => 'Pemasangan Lampu Gantung Hias Ruang Tamu',
                'description'    => 'Butuh bantuan memasang 2 unit lampu gantung vintage di ruang tamu. Tangga lipat dan bor sudah tersedia.',
                'amount'         => 65000,
                'location'       => 'Timuran, Kec. Banjarsari, Surakarta',
                'full_address'   => 'Jl. Slamet Riyadi No. 182, Timuran, Kec. Banjarsari, Surakarta',
                'lat'            => -7.5645000,
                'lng'            => 110.8142000,
                'date'           => '2026-08-05',
                'start_time'     => '13:30',
                'end_time'       => '15:30',
                'cust_msg'       => 'Pak Eko, posisi stop kontak utama sudah saya matikan demi keamanan.',
                'mitra_msg'      => 'Bagus Bu Dewi, saya sudah bawa tespen dan obeng set.',
                'cust_rev'       => 'Pemasangan rapi dan kabel tertutup rapi.',
                'mitra_rev'      => 'Pekerjaan berjalan aman dan lancar.',
            ],
            // Day 3: 06 Agustus 2026 (Sleman)
            [
                'order_id'       => 'HELP-20260806-SLM02',
                'customer'       => $custSleman2,
                'mitra'          => $mitraSleman2,
                'city_id'        => $slemanCityId,
                'title'          => 'Pembersihan Rumput Liar & Halaman Rumah Mlati',
                'description'    => 'Babat rumput ilalang depan pagar dan sapu daun kering sampai bersih masuk karung sampah.',
                'amount'         => 75000,
                'location'       => 'Sinduadi, Kec. Mlati, Sleman',
                'full_address'   => 'Jl. Magelang KM 6.5, Sinduadi, Kec. Mlati, Sleman',
                'lat'            => -7.7610000,
                'lng'            => 110.3725000,
                'date'           => '2026-08-06',
                'start_time'     => '08:00',
                'end_time'       => '10:45',
                'cust_msg'       => 'Pak Budi, karung sampah ada di dekat garasi ya.',
                'mitra_msg'      => 'Baik Bu Siti, saya langsung bersihkan sampai bersih tuntas.',
                'cust_rev'       => 'Halaman rumah jadi bersih rapi kembali.',
                'mitra_rev'      => 'Terima kasih Bu Siti atas kepercayaannya.',
            ],
            // Day 4: 07 Agustus 2026 (Solo)
            [
                'order_id'       => 'HELP-20260807-SKT02',
                'customer'       => $custSolo2,
                'mitra'          => $mitraSolo2,
                'city_id'        => $soloCityId,
                'title'          => 'Bantu Angkut & Penataan Etalase Display Toko',
                'description'    => 'Bantuan menggeser dan menata ulang 2 rak etalase pakaian agar lorong toko lebih luas.',
                'amount'         => 90000,
                'location'       => 'Jebres, Kec. Jebres, Surakarta',
                'full_address'   => 'Jl. Ir. Sutami No. 36, Jebres, Kec. Jebres, Surakarta',
                'lat'            => -7.5582000,
                'lng'            => 110.8521000,
                'date'           => '2026-08-07',
                'start_time'     => '10:00',
                'end_time'       => '12:30',
                'cust_msg'       => 'Mas Hendra, kaca etalase mohon diangkat berdua ya.',
                'mitra_msg'      => 'Siap Mbak Anisa, saya pastikan aman tanpa goresan.',
                'cust_rev'       => 'Pengerjaan cepat dan sangat hati-hati.',
                'mitra_rev'      => 'Lokasi toko strategis dan penataan sukses.',
            ],
            // Day 5: 08 Agustus 2026 (Sleman)
            [
                'order_id'       => 'HELP-20260808-SLM03',
                'customer'       => $custSleman1,
                'mitra'          => $mitraSleman1,
                'city_id'        => $slemanCityId,
                'title'          => 'Rakit Meja Belajar & Kursi Kerja Minimalis',
                'description'    => 'Perakitan meja komputer paket flat-pack dan setting hidrolik kursi kantor.',
                'amount'         => 60000,
                'location'       => 'Sinduharjo, Kec. Ngaglik, Sleman',
                'full_address'   => 'Jl. Kaliurang KM 8 No. 12, Sinduharjo, Kec. Ngaglik, Sleman',
                'lat'            => -7.7085000,
                'lng'            => 110.4072000,
                'date'           => '2026-08-08',
                'start_time'     => '14:00',
                'end_time'       => '16:00',
                'cust_msg'       => 'Kunci L dan bautnya ada di dalam plastik merah Mas.',
                'mitra_msg'      => 'Siap, saya rakit sesuai buku petunjuk pabrikan.',
                'cust_rev'       => 'Meja kokoh tidak goyang sama sekali.',
                'mitra_rev'      => 'Customer sangat ramah, terima kasih.',
            ],
            // Day 6: 09 Agustus 2026 (Solo)
            [
                'order_id'       => 'HELP-20260809-SKT03',
                'customer'       => $custSolo1,
                'mitra'          => $mitraSolo1,
                'city_id'        => $soloCityId,
                'title'          => 'Servis Ringan & Bersihkan Kipas Angin Dinding',
                'description'    => 'Pembersihan baling-baling dari debu dan pemberian pelumas pada as dinamo yang seret.',
                'amount'         => 55000,
                'location'       => 'Sondakan, Kec. Laweyan, Surakarta',
                'full_address'   => 'Jl. Dr. Radjiman No. 512, Sondakan, Kec. Laweyan, Surakarta',
                'lat'            => -7.5689000,
                'lng'            => 110.7954000,
                'date'           => '2026-08-09',
                'start_time'     => '09:30',
                'end_time'       => '11:00',
                'cust_msg'       => 'Pak Eko, kipasnya bunyi berderit saat swing.',
                'mitra_msg'      => 'Sudah saya beri oli pelumas khusus dinamo, sekarang senyap.',
                'cust_rev'       => 'Putaran kipas jadi kencang dan tidak berisik lagi.',
                'mitra_rev'      => 'Senang bisa membantu mengatasi masalah.',
            ],
            // Day 7: 10 Agustus 2026 (Sleman)
            [
                'order_id'       => 'HELP-20260810-SLM04',
                'customer'       => $custSleman2,
                'mitra'          => $mitraSleman2,
                'city_id'        => $slemanCityId,
                'title'          => 'Kuras & Sikat Tandon Air 1000 Liter Dak Rumah',
                'description'    => 'Kuras endapan lumpur dan sikat lumut tandon air di atas dak lantai 2.',
                'amount'         => 105000,
                'location'       => 'Banyuraden, Kec. Gamping, Sleman',
                'full_address'   => 'Jl. Ringroad Barat No. 108, Banyuraden, Kec. Gamping, Sleman',
                'lat'            => -7.7942000,
                'lng'            => 110.3381000,
                'date'           => '2026-08-10',
                'start_time'     => '08:30',
                'end_time'       => '11:15',
                'cust_msg'       => 'Pak Budi, kran pembuangan bawah toren sudah dibuka.',
                'mitra_msg'      => 'Baik Bu Siti, saya sikat bersih sampai airnya bening.',
                'cust_rev'       => 'Air tandon jadi bersih jernih.',
                'mitra_rev'      => 'Pekerjaan kuras tandon tuntas tanpa kendala.',
            ],
            // Day 8: 11 Agustus 2026 (Solo)
            [
                'order_id'       => 'HELP-20260811-SKT04',
                'customer'       => $custSolo2,
                'mitra'          => $mitraSolo2,
                'city_id'        => $soloCityId,
                'title'          => 'Pengecatan Dasar Pagar Rumah Panjang 5 Meter',
                'description'    => 'Pembersihan karat dan pengecatan primer zinkromat pada teralis pagar besi depan.',
                'amount'         => 120000,
                'location'       => 'Joyosuran, Kec. Pasar Kliwon, Surakarta',
                'full_address'   => 'Jl. Veteran No. 89, Joyosuran, Kec. Pasar Kliwon, Surakarta',
                'lat'            => -7.5812000,
                'lng'            => 110.8245000,
                'date'           => '2026-08-11',
                'start_time'     => '08:00',
                'end_time'       => '13:00',
                'cust_msg'       => 'Mas Hendra, cat dan kuas sudah saya siapkan.',
                'mitra_msg'      => 'Siap Mbak, saya amplas dulu biar cat menempel sempurna.',
                'cust_rev'       => 'Hasil cat sangat rata dan bersih rapi.',
                'mitra_rev'      => 'Customer sangat ramah dan puas.',
            ],
            // Day 9: 12 Agustus 2026 (Sleman)
            [
                'order_id'       => 'HELP-20260812-SLM05',
                'customer'       => $custSleman1,
                'mitra'          => $mitraSleman1,
                'city_id'        => $slemanCityId,
                'title'          => 'Pemasangan Rel Gorden & Bracket Jendela Kamar',
                'description'    => 'Pasang 3 batang rel gorden aluminium dengan bor tembok dan fisher plastik.',
                'amount'         => 65000,
                'location'       => 'Sardonoharjo, Kec. Ngaglik, Sleman',
                'full_address'   => 'Jl. Kaliurang KM 9.5, Sardonoharjo, Ngaglik, Sleman',
                'lat'            => -7.7085000,
                'lng'            => 110.4072000,
                'date'           => '2026-08-12',
                'start_time'     => '13:00',
                'end_time'       => '15:00',
                'cust_msg'       => 'Ketinggian bracket sekitar 15 cm di atas kusen ya Mas.',
                'mitra_msg'      => 'Siap Bu, saya waterpass dulu biar lurus presisi.',
                'cust_rev'       => 'Rel gorden terpasang lurus dan sangat kuat.',
                'mitra_rev'      => 'Pemasangan selesai dengan baik.',
            ],
            // Day 10: 13 Agustus 2026 (Solo)
            [
                'order_id'       => 'HELP-20260813-SKT05',
                'customer'       => $custSolo1,
                'mitra'          => $mitraSolo1,
                'city_id'        => $soloCityId,
                'title'          => 'Antar Map Berkas Pajak & Legalisir ke Balaikota',
                'description'    => 'Pengantaran amplop berkas administrasi kilat ke kantor pelayanan terpadu satu pintu.',
                'amount'         => 45000,
                'location'       => 'Timuran, Kec. Banjarsari, Surakarta',
                'full_address'   => 'Jl. Slamet Riyadi No. 182, Timuran, Banjarsari, Surakarta',
                'lat'            => -7.5645000,
                'lng'            => 110.8142000,
                'date'           => '2026-08-13',
                'start_time'     => '10:30',
                'end_time'       => '11:45',
                'cust_msg'       => 'Pak Eko, minta tolong difotokan tanda terima dari loket ya.',
                'mitra_msg'      => 'Sudah saya kirim foto resi tanda terimanya Bu.',
                'cust_rev'       => 'Pengantaran sangat cepat dan amanah.',
                'mitra_rev'      => 'Dokumen sukses terkirim tepat waktu.',
            ],
            // Day 11: 14 Agustus 2026 (Sleman)
            [
                'order_id'       => 'HELP-20260814-SLM06',
                'customer'       => $custSleman2,
                'mitra'          => $mitraSleman2,
                'city_id'        => $slemanCityId,
                'title'          => 'Ganti Seal Kran Dapur Bocor & Lem Sambungan Pipa',
                'description'    => 'Kran wastafel dapur menetes terus, ganti cartridge kran keramik dan lapisi seal tape.',
                'amount'         => 50000,
                'location'       => 'Sinduadi, Kec. Mlati, Sleman',
                'full_address'   => 'Jl. Selokan Mataram No. 27, Sinduadi, Mlati, Sleman',
                'lat'            => -7.7610000,
                'lng'            => 110.3725000,
                'date'           => '2026-08-14',
                'start_time'     => '15:00',
                'end_time'       => '16:15',
                'cust_msg'       => 'Kran barunya merk Onda ya Pak Budi.',
                'mitra_msg'      => 'Cocok Bu, sudah saya pasang kuat tanpa rembesan air.',
                'cust_rev'       => 'Kran sekarang tidak netes lagi. Mantap!',
                'mitra_rev'      => 'Pekerjaan beres dan lancar.',
            ],
            // Day 12: 15 Agustus 2026 (Solo)
            [
                'order_id'       => 'HELP-20260815-SKT06',
                'customer'       => $custSolo2,
                'mitra'          => $mitraSolo2,
                'city_id'        => $soloCityId,
                'title'          => 'Bantu Rapikan Dus Gudang & Ikat Kardus Bekas',
                'description'    => 'Pilah barang tak terpakai di gudang belakang dan ikat kardus-kardus tebal siap daur ulang.',
                'amount'         => 70000,
                'location'       => 'Jebres, Kec. Jebres, Surakarta',
                'full_address'   => 'Jl. Ir. Sutami No. 36, Jebres, Surakarta',
                'lat'            => -7.5582000,
                'lng'            => 110.8521000,
                'date'           => '2026-08-15',
                'start_time'     => '09:00',
                'end_time'       => '11:30',
                'cust_msg'       => 'Kardus yang dilipat ditumpuk dekat pintu samping ya Mas.',
                'mitra_msg'      => 'Siap Mbak, sudah saya sapu bersih lantainya juga.',
                'cust_rev'       => 'Gudang jadi lega dan bersih.',
                'mitra_rev'      => 'Kerja sama yang menyenangkan.',
            ],
            // Day 13: 16 Agustus 2026 (Sleman)
            [
                'order_id'       => 'HELP-20260816-SLM07',
                'customer'       => $custSleman1,
                'mitra'          => $mitraSleman1,
                'city_id'        => $slemanCityId,
                'title'          => 'Bantu Angkut Lemari Partikel Pindah Kos Pogung',
                'description'    => 'Angkut lemari 2 pintu dan meja rias kecil dari kos Pogung Baru ke Pogung Dalangan.',
                'amount'         => 85000,
                'location'       => 'Caturtunggal, Depok, Sleman',
                'full_address'   => 'Jl. Karangmalang Blok A No. 14, Caturtunggal, Sleman',
                'lat'            => -7.7712000,
                'lng'            => 110.3854000,
                'date'           => '2026-08-16',
                'start_time'     => '10:00',
                'end_time'       => '12:15',
                'cust_msg'       => 'Mas Agus, hati-hati waktu lewat tangga kosan ya.',
                'mitra_msg'      => 'Aman Bu Rina, sudah saya lindungi sudut lemarinya.',
                'cust_rev'       => 'Barang sampai tanpa lecet sedikitpun.',
                'mitra_rev'      => 'Terima kasih atas kepercayaannya.',
            ],
            // Day 14: 17 Agustus 2026 (Solo)
            [
                'order_id'       => 'HELP-20260817-SKT07',
                'customer'       => $custSolo1,
                'mitra'          => $mitraSolo1,
                'city_id'        => $soloCityId,
                'title'          => 'Pemasangan Tiang Bendera & Spanduk HUT RI Depan Toko',
                'description'    => 'Pasang tiang bendera Merah Putih di pagar dan bentangkan spanduk kemerdekaan di teras toko.',
                'amount'         => 50000,
                'location'       => 'Sondakan, Laweyan, Surakarta',
                'full_address'   => 'Jl. Dr. Radjiman No. 512, Sondakan, Laweyan, Surakarta',
                'lat'            => -7.5689000,
                'lng'            => 110.7954000,
                'date'           => '2026-08-17',
                'start_time'     => '07:30',
                'end_time'       => '09:00',
                'cust_msg'       => 'Pak Eko, bendera dinaikkan sampai puncak tiang ya.',
                'mitra_msg'      => 'Merdeka! Sudah terpasang kokoh Bu Dewi.',
                'cust_rev'       => 'Toko jadi meriah menyambut 17 Agustus.',
                'mitra_rev'      => 'Semangat Kemerdekaan, terima kasih Bu!',
            ],
            // Day 15: 18 Agustus 2026 (Sleman)
            [
                'order_id'       => 'HELP-20260818-SLM08',
                'customer'       => $custSleman1,
                'mitra'          => $mitraSleman2,
                'city_id'        => $slemanCityId,
                'title'          => 'Perbaikan Pompa Air Shimizu Macet & Ganti Sambungan Pipa',
                'description'    => 'Pompa air berdengung, periksa kapasitor dan pancing pipa hisap sampai semburan air lancar.',
                'amount'         => 95000,
                'location'       => 'Sardonoharjo, Ngaglik, Sleman',
                'full_address'   => 'Jl. Kaliurang KM 10 No. 52, Sardonoharjo, Sleman',
                'lat'            => -7.7085000,
                'lng'            => 110.4072000,
                'date'           => '2026-08-18',
                'start_time'     => '13:00',
                'end_time'       => '15:30',
                'cust_msg'       => 'Pak Budi, toren air atas sudah hampir habis airnya.',
                'mitra_msg'      => 'Sudah selesai Bu, pompa sudah menyala kencang kembali.',
                'cust_rev'       => 'Pompa air normal kembali tanpa berisik.',
                'mitra_rev'      => 'Kerusakan berhasil teratasi dengan baik.',
            ],
            // Day 16: 19 Agustus 2026 (Solo)
            [
                'order_id'       => 'HELP-20260819-SKT08',
                'customer'       => $custSolo2,
                'mitra'          => $mitraSolo2,
                'city_id'        => $soloCityId,
                'title'          => 'Pembersihan Talang Air Atap Rumah dari Daun Kering',
                'description'    => 'Bersihkan sampah daun di saluran talang seng atap rumah agar tidak meluap saat hujan.',
                'amount'         => 75000,
                'location'       => 'Joyosuran, Pasar Kliwon, Surakarta',
                'full_address'   => 'Jl. Veteran No. 89, Joyosuran, Surakarta',
                'lat'            => -7.5812000,
                'lng'            => 110.8245000,
                'date'           => '2026-08-19',
                'start_time'     => '08:30',
                'end_time'       => '10:45',
                'cust_msg'       => 'Tangga aluminium ada di samping dapur ya Mas Hendra.',
                'mitra_msg'      => 'Talang sudah bersih dan pipa buang air lancar Mbak.',
                'cust_rev'       => 'Kerja cekatan dan tidak takut ketinggian.',
                'mitra_rev'      => 'Pekerjaan talang atap beres.',
            ],
            // Day 17: 20 Agustus 2026 (Sleman)
            [
                'order_id'       => 'HELP-20260820-SLM09',
                'customer'       => $custSleman2,
                'mitra'          => $mitraSleman1,
                'city_id'        => $slemanCityId,
                'title'          => 'Potong Dahan Pohon Mangga Menghalangi Kabel Listrik',
                'description'    => 'Pangkas 3 dahan pohon depan pagar rumah dan rapikan potongan kayu ke pinggir jalan.',
                'amount'         => 80000,
                'location'       => 'Tridadi, Kec. Sleman, Sleman',
                'full_address'   => 'Jl. Magelang KM 10, Tridadi, Sleman',
                'lat'            => -7.6985000,
                'lng'            => 110.3542000,
                'date'           => '2026-08-20',
                'start_time'     => '09:00',
                'end_time'       => '11:30',
                'cust_msg'       => 'Gergaji pohon sudah disiapkan ya Mas Agus.',
                'mitra_msg'      => 'Dahan yang dekat kabel sudah terpotong aman Bu.',
                'cust_rev'       => 'Pohon jadi rapi dan aman dari kabel listrik.',
                'mitra_rev'      => 'Terima kasih atas kepercayaannya Bu Siti.',
            ],
            // Day 18: 21 Agustus 2026 (Solo)
            [
                'order_id'       => 'HELP-20260821-SKT09',
                'customer'       => $custSolo1,
                'mitra'          => $mitraSolo2,
                'city_id'        => $soloCityId,
                'title'          => 'Pengecatan Finishing Tembok Pagar Depan Rumah',
                'description'    => 'Pengecatan 2 lapis cat tembok eksterior abu-abu minimalis pada dinding depan rumah.',
                'amount'         => 125000,
                'location'       => 'Sondakan, Laweyan, Surakarta',
                'full_address'   => 'Jl. Dr. Radjiman No. 512, Sondakan, Surakarta',
                'lat'            => -7.5689000,
                'lng'            => 110.7954000,
                'date'           => '2026-08-21',
                'start_time'     => '08:00',
                'end_time'       => '14:00',
                'cust_msg'       => 'Mas Hendra, plamir yang retak rambut sudah diamplas ya?',
                'mitra_msg'      => 'Sudah halus Bu, warna catnya keluar rata dan elegan.',
                'cust_rev'       => 'Hasil cat sangat rapi dan presisi.',
                'mitra_rev'      => 'Terima kasih banyak Bu Dewi.',
            ],
            // Day 19: 22 Agustus 2026 (Sleman)
            [
                'order_id'       => 'HELP-20260822-SLM10',
                'customer'       => $custSleman1,
                'mitra'          => $mitraSleman2,
                'city_id'        => $slemanCityId,
                'title'          => 'Rakit Rak Sepatu 4 Susun & Meja TV Minimalis',
                'description'    => 'Perakitan furniture knock-down dari marketplace dan setting engsel pintu magnet.',
                'amount'         => 70000,
                'location'       => 'Caturtunggal, Depok, Sleman',
                'full_address'   => 'Jl. Affandi No. 45, Caturtunggal, Depok, Sleman',
                'lat'            => -7.7712000,
                'lng'            => 110.3854000,
                'date'           => '2026-08-22',
                'start_time'     => '14:00',
                'end_time'       => '16:15',
                'cust_msg'       => 'Pak Budi, buku panduannya ada di dalam dus meja TV.',
                'mitra_msg'      => 'Sudah selesai dirakit kokoh Bu Rina.',
                'cust_rev'       => 'Pemasangan cepat dan presisi.',
                'mitra_rev'      => 'Customer sangat komunikatif.',
            ],
            // Day 20: 23 Agustus 2026 (Solo)
            [
                'order_id'       => 'HELP-20260823-SKT10',
                'customer'       => $custSolo2,
                'mitra'          => $mitraSolo1,
                'city_id'        => $soloCityId,
                'title'          => 'Bantu Angkut Meja Kerja Kayu Jati ke Lantai 2',
                'description'    => 'Bantuan 1 orang menggotong meja tulis kayu jati solid lewat tangga belakang rumah.',
                'amount'         => 85000,
                'location'       => 'Timuran, Banjarsari, Surakarta',
                'full_address'   => 'Jl. Slamet Riyadi No. 182, Timuran, Surakarta',
                'lat'            => -7.5645000,
                'lng'            => 110.8142000,
                'date'           => '2026-08-23',
                'start_time'     => '10:00',
                'end_time'       => '11:45',
                'cust_msg'       => 'Meja kayunya agak berat Pak Eko.',
                'mitra_msg'      => 'Tenang Mbak, saya pegang sisi bawah, posisi sudah pas di atas.',
                'cust_rev'       => 'Kuat dan berpengalaman mengangkat furniture berat.',
                'mitra_rev'      => 'Pekerjaan selesai dengan lancar.',
            ],
            // Day 21: 24 Agustus 2026 (Sleman)
            [
                'order_id'       => 'HELP-20260824-SLM11',
                'customer'       => $custSleman2,
                'mitra'          => $mitraSleman1,
                'city_id'        => $slemanCityId,
                'title'          => 'Pasang Pompa Booster Otomatis Pemanas Air',
                'description'    => 'Pemasangan pompa pendorong kecil untuk shower kamar mandi utama dan setting otomatis flow switch.',
                'amount'         => 90000,
                'location'       => 'Banyuraden, Gamping, Sleman',
                'full_address'   => 'Jl. Ringroad Barat No. 108, Banyuraden, Sleman',
                'lat'            => -7.7942000,
                'lng'            => 110.3381000,
                'date'           => '2026-08-24',
                'start_time'     => '13:30',
                'end_time'       => '16:00',
                'cust_msg'       => 'Mas Agus, kabel power pompa dicolok ke stop kontak atas ya.',
                'mitra_msg'      => 'Shower sekarang semburannya kencang dan otomatis mati saat kran ditutup.',
                'cust_rev'       => 'Mandi shower jadi nyaman airnya deras.',
                'mitra_rev'      => 'Instalasi pipa rapi dan aman.',
            ],
            // Day 22: 25 Agustus 2026 (Solo)
            [
                'order_id'       => 'HELP-20260825-SKT11',
                'customer'       => $custSolo1,
                'mitra'          => $mitraSolo2,
                'city_id'        => $soloCityId,
                'title'          => 'Ganti 4 Unit Stop Kontak & Saklar Lampu Broco',
                'description'    => 'Penggantian stop kontak dinding yang longgar dan saklar seri lampu ruang keluarga.',
                'amount'         => 60000,
                'location'       => 'Jebres, Kec. Jebres, Surakarta',
                'full_address'   => 'Jl. Ir. Sutami No. 36, Jebres, Surakarta',
                'lat'            => -7.5582000,
                'lng'            => 110.8521000,
                'date'           => '2026-08-25',
                'start_time'     => '14:00',
                'end_time'       => '15:45',
                'cust_msg'       => 'Mas Hendra, kabel grounding tolong dipastikan kencang ya.',
                'mitra_msg'      => 'Beres Bu Dewi, semua sambungan sudah diisolasi dan kencang.',
                'cust_rev'       => 'Pengerjaan listrik aman dan rapi.',
                'mitra_rev'      => 'Senang bisa membantu.',
            ],
            // Day 23: 26 Agustus 2026 (Sleman)
            [
                'order_id'       => 'HELP-20260826-SLM12',
                'customer'       => $custSleman1,
                'mitra'          => $mitraSleman2,
                'city_id'        => $slemanCityId,
                'title'          => 'Pembersihan Kerak Keramik Kamar Mandi & Kloset',
                'description'    => 'Sikat kerak membandel pada lantai keramik dan dinding kamar mandi sampai putih kinclong.',
                'amount'         => 75000,
                'location'       => 'Sinduharjo, Ngaglik, Sleman',
                'full_address'   => 'Jl. Kaliurang KM 8 No. 12, Sinduharjo, Sleman',
                'lat'            => -7.7085000,
                'lng'            => 110.4072000,
                'date'           => '2026-08-26',
                'start_time'     => '09:00',
                'end_time'       => '11:30',
                'cust_msg'       => 'Pak Budi, cairan pembersih keramik ada di bawah wastafel.',
                'mitra_msg'      => 'Kamar mandi sudah bersih mengkilap tanpa noda Bu.',
                'cust_rev'       => 'Kamar mandi jadi bersih harum dan segar.',
                'mitra_rev'      => 'Terima kasih atas kepercayaannya Bu Rina.',
            ],
            // Day 24: 27 Agustus 2026 (Solo)
            [
                'order_id'       => 'HELP-20260827-SKT12',
                'customer'       => $custSolo2,
                'mitra'          => $mitraSolo1,
                'city_id'        => $soloCityId,
                'title'          => 'Antar Paket Kue & Parsel Kado Rekanan Kantor',
                'description'    => 'Pengantaran 3 box kue basah tradisional ke alamat klien kantor di sekitar Sriwedari.',
                'amount'         => 45000,
                'location'       => 'Joyosuran, Pasar Kliwon, Surakarta',
                'full_address'   => 'Jl. Veteran No. 89, Joyosuran, Surakarta',
                'lat'            => -7.5812000,
                'lng'            => 110.8245000,
                'date'           => '2026-08-27',
                'start_time'     => '10:00',
                'end_time'       => '11:15',
                'cust_msg'       => 'Box kuenya ditaruh datar ya Pak Eko biar kuenya utuh.',
                'mitra_msg'      => 'Sudah sampai tujuan dalam kondisi mulus dan diterima resepsionis.',
                'cust_rev'       => 'Pengantaran tepat waktu dan kue aman.',
                'mitra_rev'      => 'Dokumen tanda terima sudah difotokan.',
            ],
            // Day 25: 28 Agustus 2026 (Sleman)
            [
                'order_id'       => 'HELP-20260828-SLM13',
                'customer'       => $custSleman2,
                'mitra'          => $mitraSleman1,
                'city_id'        => $slemanCityId,
                'title'          => 'Angkut & Tata Pot Tanaman Hias Taman Belakang',
                'description'    => 'Pemindahan 8 pot gerabah tanaman monstera dan palem ke taman samping rumah.',
                'amount'         => 70000,
                'location'       => 'Sinduadi, Mlati, Sleman',
                'full_address'   => 'Jl. Magelang KM 6.5, Sinduadi, Sleman',
                'lat'            => -7.7610000,
                'lng'            => 110.3725000,
                'date'           => '2026-08-28',
                'start_time'     => '08:30',
                'end_time'       => '10:30',
                'cust_msg'       => 'Mas Agus, pot yang besar minta tolong dialasi kain ya.',
                'mitra_msg'      => 'Taman sudah tertata rapi dan indah Bu Siti.',
                'cust_rev'       => 'Taman belakang jadi asri dan tertata bagus.',
                'mitra_rev'      => 'Senang bisa membantu merapikan taman.',
            ],
            // Day 26: 29 Agustus 2026 (Solo)
            [
                'order_id'       => 'HELP-20260829-SKT13',
                'customer'       => $custSolo1,
                'mitra'          => $mitraSolo2,
                'city_id'        => $soloCityId,
                'title'          => 'Rakit Lemari Pakaian 3 Pintu & Pasang Kaca Cermin',
                'description'    => 'Perakitan lemari kayu partikel 3 pintu dan pemasangan cermin dinding kamar tidur.',
                'amount'         => 110000,
                'location'       => 'Sondakan, Laweyan, Surakarta',
                'full_address'   => 'Jl. Dr. Radjiman No. 512, Sondakan, Surakarta',
                'lat'            => -7.5689000,
                'lng'            => 110.7954000,
                'date'           => '2026-08-29',
                'start_time'     => '09:00',
                'end_time'       => '13:00',
                'cust_msg'       => 'Mas Hendra, pintu tengah cermin tolong disetel biar tidak gesek.',
                'mitra_msg'      => 'Engsel pintu sudah presisi dan kuncinya berfungsi lancar.',
                'cust_rev'       => 'Pemasangan lemari sangat rapi dan kokoh.',
                'mitra_rev'      => 'Terima kasih banyak Bu Dewi atas kepercayaannya.',
            ],
            // Day 27: 30 Agustus 2026 (Sleman)
            [
                'order_id'       => 'HELP-20260830-SLM14',
                'customer'       => $custSleman1,
                'mitra'          => $mitraSleman2,
                'city_id'        => $slemanCityId,
                'title'          => 'Servis Engsel Pintu Kayu Gesek Lantai & Ganti Grendel',
                'description'    => 'Serut sedikit bagian bawah pintu kayu yang seret dan pasang grendel kunci tanam baru.',
                'amount'         => 65000,
                'location'       => 'Sardonoharjo, Ngaglik, Sleman',
                'full_address'   => 'Jl. Kaliurang KM 9.5, Sardonoharjo, Sleman',
                'lat'            => -7.7085000,
                'lng'            => 110.4072000,
                'date'           => '2026-08-30',
                'start_time'     => '14:00',
                'end_time'       => '16:00',
                'cust_msg'       => 'Pak Budi, pintu depan susah ditutup waktu musim hujan.',
                'mitra_msg'      => 'Sudah saya serut 2 mm, pintu lancar dibuka tutup.',
                'cust_rev'       => 'Pintu sekarang buka tutupnya enteng sekali.',
                'mitra_rev'      => 'Pekerjaan kayu tuntas sempurna.',
            ],
            // Day 28: 31 Agustus 2026 (Solo)
            [
                'order_id'       => 'HELP-20260831-SKT14',
                'customer'       => $custSolo2,
                'mitra'          => $mitraSolo1,
                'city_id'        => $soloCityId,
                'title'          => 'Pembersihan Debu Karpet Tebal & Sedot Tungau Sofa',
                'description'    => 'Vacuum debu dan sikat kering karpet permadani 2x3 meter di ruang tamu.',
                'amount'         => 80000,
                'location'       => 'Timuran, Banjarsari, Surakarta',
                'full_address'   => 'Jl. Slamet Riyadi No. 182, Timuran, Surakarta',
                'lat'            => -7.5645000,
                'lng'            => 110.8142000,
                'date'           => '2026-08-31',
                'start_time'     => '10:00',
                'end_time'       => '12:30',
                'cust_msg'       => 'Pak Eko, vacuum cleaner ada di sudut ruang keluarga.',
                'mitra_msg'      => 'Karpet sudah disedot debunya sampai bersih bebas tungau.',
                'cust_rev'       => 'Karpet bersih wangi dan nyaman dipakai.',
                'mitra_rev'      => 'Terima kasih atas kepercayaannya Mbak Anisa.',
            ],
            // Day 29: 01 September 2026 (Sleman)
            [
                'order_id'       => 'HELP-20260901-SLM15',
                'customer'       => $custSleman1,
                'mitra'          => $mitraSleman1,
                'city_id'        => $slemanCityId,
                'title'          => 'Kuras & Sikat Tandon Air Bersih 1000L Rumah Kaliurang',
                'description'    => 'Kuras berkala toren air dan bersihkan lumut bagian dasar tandon dak atas.',
                'amount'         => 110000,
                'location'       => 'Sinduharjo, Ngaglik, Sleman',
                'full_address'   => 'Jl. Kaliurang KM 8 No. 12, Sinduharjo, Sleman',
                'lat'            => -7.7085000,
                'lng'            => 110.4072000,
                'date'           => '2026-09-01',
                'start_time'     => '08:00',
                'end_time'       => '10:30',
                'cust_msg'       => 'Mas Agus, kran pembuangan bawah toren sudah dibuka.',
                'mitra_msg'      => 'Toren sudah kinclong disikat bersih Bu Rina.',
                'cust_rev'       => 'Air toren sekarang jernih kembali. Sangat memuaskan!',
                'mitra_rev'      => 'Pekerjaan kuras tandon tuntas dan lancar.',
            ],
            // Day 30: 02 September 2026 (Solo)
            [
                'order_id'       => 'HELP-20260902-SKT15',
                'customer'       => $custSolo1,
                'mitra'          => $mitraSolo2,
                'city_id'        => $soloCityId,
                'title'          => 'Bantu Rapikan Gudang & Buang Rongsokan Kardus Pindahan',
                'description'    => 'Merapikan sisa kardus packing dan menyapu lantai gudang belakang.',
                'amount'         => 80000,
                'location'       => 'Sondakan, Laweyan, Surakarta',
                'full_address'   => 'Jl. Dr. Radjiman No. 512, Sondakan, Surakarta',
                'lat'            => -7.5689000,
                'lng'            => 110.7954000,
                'date'           => '2026-09-02',
                'start_time'     => '09:00',
                'end_time'       => '11:45',
                'cust_msg'       => 'Mas Hendra, kardus yang diikat tolong ditaruh depan ya.',
                'mitra_msg'      => 'Siap Bu Dewi, gudang belakang sudah bersih rapi.',
                'cust_rev'       => 'Gudang belakang jadi rapi dan bersih.',
                'mitra_rev'      => 'Kerja sama sangat baik dan lancar.',
            ],
            // Day 31: 03 September 2026 (Sleman)
            [
                'order_id'       => 'HELP-20260903-SLM16',
                'customer'       => $custSleman2,
                'mitra'          => $mitraSleman2,
                'city_id'        => $slemanCityId,
                'title'          => 'Antar Dokumen Kontrak & Berkas ke Kantor Pemkab Sleman',
                'description'    => 'Pengantaran map dokumen penting dari daerah Gamping langsung ke kantor Dinas Perizinan Sleman.',
                'amount'         => 50000,
                'location'       => 'Tridadi, Kec. Sleman, Sleman',
                'full_address'   => 'Jl. Parasamya No. 1, Tridadi, Sleman',
                'lat'            => -7.6985000,
                'lng'            => 110.3542000,
                'date'           => '2026-09-03',
                'start_time'     => '10:30',
                'end_time'       => '11:45',
                'cust_msg'       => 'Pak Budi, dokumen diserahkan ke bagian loket 3 ya.',
                'mitra_msg'      => 'Dokumen sudah diterima loket dan bukti tanda terima terlampir.',
                'cust_rev'       => 'Pengantaran sangat cepat dan aman. Terima kasih Pak Budi!',
                'mitra_rev'      => 'Dokumen resmi telah diserahkan dengan tanda terima jelas.',
            ],
        ];

        foreach ($dailyTasks as $data) {
            $customer = $data['customer'];
            $mitra    = $data['mitra'];

            if (!$customer || !$mitra) {
                continue;
            }

            $amount       = (float) $data['amount'];
            $platformFee  = $fixedPlatformFee;
            $mitraEarning = $amount;

            $createdAt   = Carbon::parse($data['date'] . ' ' . $data['start_time'] . ':00')->subMinutes(30);
            $takenAt     = Carbon::parse($data['date'] . ' ' . $data['start_time'] . ':00')->subMinutes(15);
            $startedAt   = Carbon::parse($data['date'] . ' ' . $data['start_time'] . ':00');
            $completedAt = Carbon::parse($data['date'] . ' ' . $data['end_time'] . ':00');

            // 1. Simpan Data Help
            $help = Help::updateOrCreate(
                ['order_id' => $data['order_id']],
                [
                    'user_id'                  => $customer->id,
                    'mitra_id'                 => $mitra->id,
                    'city_id'                  => $data['city_id'],
                    'title'                    => $data['title'],
                    'description'              => $data['description'],
                    'amount'                   => $amount,
                    'admin_fee'                => $platformFee,
                    'total_amount'             => $amount + $platformFee,
                    'location'                 => $data['location'],
                    'full_address'             => $data['full_address'],
                    'latitude'                 => $data['lat'],
                    'longitude'                => $data['lng'],
                    'status'                   => Help::STATUS_SELESAI,
                    'escrow_status'            => Help::ESCROW_STATUS_RELEASED,
                    'payment_status'           => Help::PAYMENT_STATUS_PAID,
                    'rating_status'            => Help::RATING_STATUS_RATED,
                    'dispatch_mode'            => Help::DISPATCH_MODE_CLOSED,
                    'model_version'            => 2,
                    'platform_commission_rate' => 0.00,
                    'platform_fee_amount'      => $platformFee,
                    'mitra_earning'            => $mitraEarning,
                    'escrow_locked_at'         => $createdAt,
                    'mitra_assigned_at'        => $takenAt,
                    'taken_at'                 => $takenAt,
                    'service_started_at'       => $startedAt,
                    'service_completed_at'     => $completedAt,
                    'completed_at'             => $completedAt,
                    'created_at'               => $createdAt,
                    'updated_at'               => $completedAt,
                ]
            );

            // 2. Simpan Rating Timbal Balik
            Rating::updateOrCreate(
                [
                    'help_id'  => $help->id,
                    'rater_id' => $customer->id,
                    'ratee_id' => $mitra->id,
                    'type'     => 'customer_to_mitra',
                ],
                [
                    'rating'     => 5,
                    'review'     => $data['cust_rev'],
                    'created_at' => $completedAt->copy()->addMinutes(10),
                    'updated_at' => $completedAt->copy()->addMinutes(10),
                ]
            );

            Rating::updateOrCreate(
                [
                    'help_id'  => $help->id,
                    'rater_id' => $mitra->id,
                    'ratee_id' => $customer->id,
                    'type'     => 'mitra_to_customer',
                ],
                [
                    'rating'     => 5,
                    'review'     => $data['mitra_rev'],
                    'created_at' => $completedAt->copy()->addMinutes(15),
                    'updated_at' => $completedAt->copy()->addMinutes(15),
                ]
            );

            // 3. Simpan Chat Percakapan Bantuan
            Chat::updateOrCreate(
                [
                    'help_id'     => $help->id,
                    'sender_type' => 'customer',
                    'message'     => $data['cust_msg'],
                ],
                [
                    'mitra_id'    => $mitra->id,
                    'customer_id' => $customer->id,
                    'read_at'     => $startedAt,
                    'created_at'  => $startedAt->copy()->subMinutes(10),
                    'updated_at'  => $startedAt->copy()->subMinutes(10),
                ]
            );

            Chat::updateOrCreate(
                [
                    'help_id'     => $help->id,
                    'sender_type' => 'mitra',
                    'message'     => $data['mitra_msg'],
                ],
                [
                    'mitra_id'    => $mitra->id,
                    'customer_id' => $customer->id,
                    'read_at'     => $startedAt,
                    'created_at'  => $startedAt->copy()->subMinutes(5),
                    'updated_at'  => $startedAt->copy()->subMinutes(5),
                ]
            );

            // 4. Simpan Partner Activity Logs
            PartnerActivity::updateOrCreate(
                [
                    'help_id'       => $help->id,
                    'user_id'       => $mitra->id,
                    'activity_type' => 'start_help',
                ],
                [
                    'description' => "Mitra {$mitra->name} mulai mengerjakan bantuan '{$help->title}'.",
                    'created_at'  => $startedAt,
                    'updated_at'  => $startedAt,
                ]
            );

            PartnerActivity::updateOrCreate(
                [
                    'help_id'       => $help->id,
                    'user_id'       => $mitra->id,
                    'activity_type' => 'complete_help',
                ],
                [
                    'description' => "Mitra {$mitra->name} menyelesaikan pekerjaan bantuan '{$help->title}'.",
                    'created_at'  => $completedAt,
                    'updated_at'  => $completedAt,
                ]
            );
        }

        $this->command->info('HelpsSeeder berhasil membuat 31 riwayat bantuan (terisi lengkap untuk setiap hari dari 4 Agustus s/d 3 September 2026), rating, chat, & aktivitas mitra.');
    }
}
