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
     * Tersebar di 4 Wilayah (Sleman, Yogyakarta, Surakarta, Sukoharjo).
     * Seluruh Customer & Mitra memiliki transaksi aktif.
     * 0 Tugas Sedang Berjalan & 0 Tugas di Pool.
     */
    public function run(): void
    {
        $storageHelpsPath = storage_path('app/public/helps');
        if (!File::exists($storageHelpsPath)) {
            File::makeDirectory($storageHelpsPath, 0755, true);
        }

        // Sleman Users
        $custSleman1  = User::where('email', 'customer.sleman1@sayabantu.com')->first();
        $custSleman2  = User::where('email', 'customer.sleman2@sayabantu.com')->first();
        $custSleman3  = User::where('email', 'customer@sayabantu.com')->first();
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

        $slemanCity    = City::where('code', '3404')->orWhere('name', 'like', '%Sleman%')->first();
        $jogjaCity     = City::where('code', '3471')->orWhere('name', 'like', '%Yogyakarta%')->first();
        $soloCity      = City::where('code', '3372')->orWhere('name', 'like', '%Surakarta%')->first();
        $sukoharjoCity = City::where('code', '3311')->orWhere('name', 'like', '%Sukoharjo%')->first();

        $slemanId    = $slemanCity ? $slemanCity->id : 226;
        $jogjaId     = $jogjaCity ? $jogjaCity->id : 227;
        $soloId      = $soloCity ? $soloCity->id : 218;
        $sukoharjoId = $sukoharjoCity ? $sukoharjoCity->id : 198;

        $fixedPlatformFee = (float) AppSetting::getPlatformServiceFee();
        if ($fixedPlatformFee <= 0) {
            $fixedPlatformFee = 2500.00;
        }

        // =========================================================================
        // DAFTAR TUGAS BANTUAN LENGKAP (31 HARI PENUH, 4 AGUSTUS - 3 SEPTEMBER 2026)
        // Tersebar di Sleman, Yogyakarta, Surakarta, dan Sukoharjo
        // =========================================================================
        $tasksData = [
            // --- HARI 01: 04 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260804-SLM01', 'cust' => $custSleman1, 'mitra' => $mitraSleman1, 'city_id' => $slemanId,
                'title' => 'Bantu Pindahan & Angkat Kasur Busa Gejayan', 'amount' => 80000,
                'desc' => 'Pindahan kosan berjarak 500 meter, butuh 1 orang membantu angkut kasur busa tebal dan 3 kardus buku dari lantai 2.',
                'loc' => 'Caturtunggal, Depok, Sleman', 'addr' => 'Jl. Affandi No. 45, Caturtunggal, Depok, Sleman',
                'lat' => -7.7712, 'lng' => 110.3854, 'date' => '2026-08-04', 'start' => '09:00', 'end' => '11:15',
                'cust_msg' => 'Halo Mas Agus, saya tunggu di depan gerbang kos ya.', 'mitra_msg' => 'Siap Bu Rina, saya meluncur bawa tali tambang.',
                'cust_rev' => 'Mas Agus sangat cekatan dan sopan. Sangat membantu.', 'mitra_rev' => 'Customer sangat ramah dan komunikasi lancar.'
            ],
            [
                'order_id' => 'HELP-20260804-SKT01', 'cust' => $custSolo1, 'mitra' => $mitraSolo1, 'city_id' => $soloId,
                'title' => 'Pemasangan Lampu Gantung Hias Ruang Tamu', 'amount' => 65000,
                'desc' => 'Butuh bantuan memasang 2 unit lampu gantung vintage di ruang tamu. Tangga lipat dan bor sudah tersedia.',
                'loc' => 'Timuran, Banjarsari, Surakarta', 'addr' => 'Jl. Slamet Riyadi No. 182, Timuran, Banjarsari, Surakarta',
                'lat' => -7.5645, 'lng' => 110.8142, 'date' => '2026-08-04', 'start' => '13:30', 'end' => '15:30',
                'cust_msg' => 'Pak Eko, posisi stop kontak utama sudah saya matikan.', 'mitra_msg' => 'Bagus Bu Dewi, saya sudah bawa tespen dan obeng set.',
                'cust_rev' => 'Pemasangan rapi dan kabel tertutup aman.', 'mitra_rev' => 'Pekerjaan berjalan lancar dan aman.'
            ],

            // --- HARI 02: 05 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260805-SKH01', 'cust' => $custSkh1, 'mitra' => $mitraSkh1, 'city_id' => $sukoharjoId,
                'title' => 'Pembersihan Filter & Cuci AC Kamar Tidur', 'amount' => 90000,
                'desc' => 'Cuci AC 1 PK kamar utama yang mulai kurang dingin dan bersihkan saluran buang air pembuangan.',
                'loc' => 'Ngadirejo, Kartasura, Sukoharjo', 'addr' => 'Perum Baki Permai Blok C-12, Ngadirejo, Kartasura, Sukoharjo',
                'lat' => -7.5521, 'lng' => 110.7482, 'date' => '2026-08-05', 'start' => '09:30', 'end' => '11:45',
                'cust_msg' => 'Mas Tri, keran air dekat garasi bisa digunakan untuk selang ya.', 'mitra_msg' => 'Siap Bu Indah, saya bawa jet washer dan terpal pelindung.',
                'cust_rev' => 'AC jadi dingin semriwing dan pengerjaan sangat bersih.', 'mitra_rev' => 'Customer sangat baik dan menyediakan akses air mudah.'
            ],
            [
                'order_id' => 'HELP-20260805-JOG01', 'cust' => $custJogja1, 'mitra' => $mitraJogja1, 'city_id' => $jogjaId,
                'title' => 'Perakitan Rak Display Butik Malioboro', 'amount' => 75000,
                'desc' => 'Merakit 2 set rak display kayu knock-down untuk display pakaian di butik.',
                'loc' => 'Suryatmajan, Danurejan, Yogyakarta', 'addr' => 'Jl. Mataram No. 42, Danurejan, Kota Yogyakarta',
                'lat' => -7.7942, 'lng' => 110.3689, 'date' => '2026-08-05', 'start' => '14:00', 'end' => '16:30',
                'cust_msg' => 'Mas Danang, baut dan obeng bawaan ada di dalam kardus ya.', 'mitra_msg' => 'Baik Mbak Nia, saya bawa obeng cordless biar cepat kokoh.',
                'cust_rev' => 'Rak kokoh dan perakitan sangat presisi.', 'mitra_rev' => 'Tempat kerja nyaman dan instruksi sangat jelas.'
            ],

            // --- HARI 03: 06 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260806-SLM02', 'cust' => $custSleman2, 'mitra' => $mitraSleman2, 'city_id' => $slemanId,
                'title' => 'Pembersihan Rumput Liar & Halaman Mlati', 'amount' => 75000,
                'desc' => 'Babat rumput liar di halaman samping seluas 30m2 dan merapikan tanaman rambat di pagar.',
                'loc' => 'Sinduadi, Mlati, Sleman', 'addr' => 'Jl. Magelang KM 6.5, Sinduadi, Mlati, Sleman',
                'lat' => -7.7610, 'lng' => 110.3725, 'date' => '2026-08-06', 'start' => '08:00', 'end' => '11:00',
                'cust_msg' => 'Pak Budi, sampah rumputnya tolong dimasukkan karung hitam di teras ya.', 'mitra_msg' => 'Siap Bu Siti, saya sudah bawa sabit dan sapu lidi.',
                'cust_rev' => 'Halaman jadi bersih rapi, rumput dicabut sampai akar.', 'mitra_rev' => 'Lokasi kerja teduh dan customer sangat ramah.'
            ],
            [
                'order_id' => 'HELP-20260806-SKT02', 'cust' => $custSolo2, 'mitra' => $mitraSolo2, 'city_id' => $soloId,
                'title' => 'Pengantaran Paket Kain Batik & Dokumen Pasar Kliwon', 'amount' => 50000,
                'desc' => 'Antar 2 gulung kain batik tulis dan dokumen penawaran dari Pasar Kliwon ke Jebres.',
                'loc' => 'Joyosuran, Pasar Kliwon, Surakarta', 'addr' => 'Jl. Veteran No. 89, Joyosuran, Pasar Kliwon, Surakarta',
                'lat' => -7.5812, 'lng' => 110.8321, 'date' => '2026-08-06', 'start' => '13:00', 'end' => '14:30',
                'cust_msg' => 'Mas Hendra, mohon kainnya dibungkus plastik rangkap anti hujan.', 'mitra_msg' => 'Aman Mbak Anisa, tas kurir saya full waterproof.',
                'cust_rev' => 'Pengiriman super cepat dan paket sampai tanpa cacat.', 'mitra_rev' => 'Paket sudah terbungkus rapi saat diambil.'
            ],

            // --- HARI 04: 07 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260807-SKH02', 'cust' => $custSkh2, 'mitra' => $mitraSkh2, 'city_id' => $sukoharjoId,
                'title' => 'Bantu Angkut Dus Obat & Rak Etalase Apotek Sukoharjo', 'amount' => 85000,
                'desc' => 'Bongkar muat 15 kardus perlengkapan apotek dan penataan ulang rak obat di Sukoharjo kota.',
                'loc' => 'Jombor, Sukoharjo', 'addr' => 'Jl. Jenderal Sudirman No. 80, Jombor, Sukoharjo',
                'lat' => -7.6841, 'lng' => 110.8392, 'date' => '2026-08-07', 'start' => '10:00', 'end' => '12:45',
                'cust_msg' => 'Pak Joko, barangnya lumayan berat mohon hati-hati ya.', 'mitra_msg' => 'Tenang Pak Bayu, saya bawa troli dorong lipat.',
                'cust_rev' => 'Pak Joko sangat kuat dan teratur menyusun kardus.', 'mitra_rev' => 'Customer sangat apresiatif dan menyediakan minum dingin.'
            ],
            [
                'order_id' => 'HELP-20260807-SLM03', 'cust' => $custSleman3, 'mitra' => $mitraSleman3, 'city_id' => $slemanId,
                'title' => 'Pemotongan Dahan Pohon Mangga Ngaglik', 'amount' => 70000,
                'desc' => 'Pangkas 4 cabang dahan pohon mangga yang sudah menyentuh kabel listrik di depan teras rumah.',
                'loc' => 'Sariharjo, Ngaglik, Sleman', 'addr' => 'Jl. Palagan Tentara Pelajar KM 7 No. 20, Sariharjo, Ngaglik',
                'lat' => -7.7314, 'lng' => 110.3751, 'date' => '2026-08-07', 'start' => '14:00', 'end' => '16:15',
                'cust_msg' => 'Mas Fajar, tangganya ada di dekat gudang ya.', 'mitra_msg' => 'Siap Mbak Maya, saya bawa gergaji dahan tajam.',
                'cust_rev' => 'Dahan pohon terpotong rapi, tidak ada kabel yang tersenggol.', 'mitra_rev' => 'Pekerjaan selesai aman tanpa kendala.'
            ],

            // --- HARI 05: 08 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260808-SLM04', 'cust' => $custSleman1, 'mitra' => $mitraSleman2, 'city_id' => $slemanId,
                'title' => 'Perbaikan Keran Wastafel Bocor & Pipa Pembuangan', 'amount' => 60000,
                'desc' => 'Ganti drat keran wastafel dapur yang patah dan lem ulang sambungan pipa PVC.',
                'loc' => 'Sinduharjo, Ngaglik, Sleman', 'addr' => 'Jl. Kaliurang KM 8 No. 12, Sinduharjo, Ngaglik, Sleman',
                'lat' => -7.7289, 'lng' => 110.3891, 'date' => '2026-08-08', 'start' => '09:00', 'end' => '10:30',
                'cust_msg' => 'Pak Budi, keran pengganti sudah saya belikan merk Onda.', 'mitra_msg' => 'Sip Bu Rina, saya bawa seal tape dan lem PVC Isarplas.',
                'cust_rev' => 'Air tidak menetes lagi, pengerjaan cepat.', 'mitra_rev' => 'Kerusakan mudah diatasi dan part sesuai.'
            ],
            [
                'order_id' => 'HELP-20260808-SKT03', 'cust' => $custSolo1, 'mitra' => $mitraSolo2, 'city_id' => $soloId,
                'title' => 'Pengangkutan Lemari Kayu Jati Laweyan', 'amount' => 95000,
                'desc' => 'Angkut lemari pakaian jati 2 pintu dari kamar bawah ke lantai atas.',
                'loc' => 'Sondakan, Laweyan, Surakarta', 'addr' => 'Jl. Dr. Radjiman No. 512, Sondakan, Laweyan, Surakarta',
                'lat' => -7.5689, 'lng' => 110.7981, 'date' => '2026-08-08', 'start' => '14:00', 'end' => '16:00',
                'cust_msg' => 'Mas Hendra, pintu lemari bisa dilepas dulu jika terlalu berat.', 'mitra_msg' => 'Siap Bu Dewi, saya lepas pintu dan laci dulu biar aman.',
                'cust_rev' => 'Lemari naik ke lantai 2 tanpa lecet sedikitpun.', 'mitra_rev' => 'Tangga rumah cukup lebar, pengerjaan lancar.'
            ],

            // --- HARI 06: 09 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260809-SKH03', 'cust' => $custSkh1, 'mitra' => $mitraSkh2, 'city_id' => $sukoharjoId,
                'title' => 'Pembersihan Kaca Jendela & Kanopi Rumah Grogol', 'amount' => 80000,
                'desc' => 'Bersihkan 6 jendela kaca besar dan kanopi carport dari debu tebal.',
                'loc' => 'Madegondo, Grogol, Sukoharjo', 'addr' => 'Jl. Ir. Soekarno No. 45, Grogol, Sukoharjo',
                'lat' => -7.5912, 'lng' => 110.8123, 'date' => '2026-08-09', 'start' => '08:30', 'end' => '11:00',
                'cust_msg' => 'Pak Joko, pembersih kaca Cling ada di bawah wastafel.', 'mitra_msg' => 'Baik Bu Indah, saya bawa wiper karet bertangkai panjang.',
                'cust_rev' => 'Kaca jadi bening kinclong tanpa noda air.', 'mitra_rev' => 'Peralatan memadai dan lokasi strategis.'
            ],
            [
                'order_id' => 'HELP-20260809-JOG02', 'cust' => $custJogja1, 'mitra' => $mitraSleman1, 'city_id' => $jogjaId,
                'title' => 'Pemasangan Tirai Gulung Rol Blinds Kantor Jogja', 'amount' => 70000,
                'desc' => 'Pasang 3 unit roller blind di ruang kantor depan Jl. Mataram.',
                'loc' => 'Danurejan, Kota Yogyakarta', 'addr' => 'Jl. Mataram No. 42, Danurejan, Yogyakarta',
                'lat' => -7.7942, 'lng' => 110.3689, 'date' => '2026-08-09', 'start' => '13:30', 'end' => '15:15',
                'cust_msg' => 'Mas Agus, bor tembok sudah ada ya.', 'mitra_msg' => 'Siap Mbak Nia, saya bawa fischer dan waterpass.',
                'cust_rev' => 'Tirai terpasang simetris dan rapi.', 'mitra_rev' => 'Dinding kokoh dan instruksi jelas.'
            ],

            // --- HARI 07: 10 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260810-SLM05', 'cust' => $custSleman2, 'mitra' => $mitraSleman3, 'city_id' => $slemanId,
                'title' => 'Pengecatan Ulang Pagar Besi Minimalis Gamping', 'amount' => 100000,
                'desc' => 'Amplas karat dan cat ulang pagar besi rumah sepanjang 6 meter dengan cat hitam doff.',
                'loc' => 'Banyuraden, Gamping, Sleman', 'addr' => 'Jl. Ringroad Barat No. 108, Banyuraden, Gamping, Sleman',
                'lat' => -7.7845, 'lng' => 110.3341, 'date' => '2026-08-10', 'start' => '08:00', 'end' => '12:00',
                'cust_msg' => 'Mas Fajar, cat Seiv dan kuas sudah tersedia di teras.', 'mitra_msg' => 'Siap Bu Siti, saya amplas bersih dulu baru mulai kuas.',
                'cust_rev' => 'Pagar tampak baru kembali, cat merata tanpa belepotan.', 'mitra_rev' => 'Cat dan tiner kualitas bagus, hasil memuaskan.'
            ],
            [
                'order_id' => 'HELP-20260810-SKT04', 'cust' => $custSolo2, 'mitra' => $mitraSolo1, 'city_id' => $soloId,
                'title' => 'Servis & Pasang Kipas Angin Plafon Pasar Kliwon', 'amount' => 65000,
                'desc' => 'Perbaiki kipas angin gantung plafon yang macet dan ganti kapasitor.',
                'loc' => 'Joyosuran, Pasar Kliwon, Surakarta', 'addr' => 'Jl. Veteran No. 89, Joyosuran, Pasar Kliwon, Surakarta',
                'lat' => -7.5812, 'lng' => 110.8321, 'date' => '2026-08-10', 'start' => '14:00', 'end' => '15:45',
                'cust_msg' => 'Pak Eko, kapasitor cadangan sudah saya siapkan.', 'mitra_msg' => 'Bagus Mbak Anisa, saya lumasi bearing sekalian biar senyap.',
                'cust_rev' => 'Kipas berputar kencang dan tidak berisik lagi.', 'mitra_rev' => 'Permasalahan kapasitor cepat terselesaikan.'
            ],

            // --- HARI 08: 11 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260811-SKH04', 'cust' => $custSkh2, 'mitra' => $mitraSkh1, 'city_id' => $sukoharjoId,
                'title' => 'Ganti Saklar & Tambah Titik Lampu Gudang Apotek', 'amount' => 75000,
                'desc' => 'Pasang instalasi 1 titik lampu LED tube di lorong gudang dan pasang saklar ganda.',
                'loc' => 'Jombor, Sukoharjo', 'addr' => 'Jl. Jenderal Sudirman No. 80, Jombor, Sukoharjo',
                'lat' => -7.6841, 'lng' => 110.8392, 'date' => '2026-08-11', 'start' => '09:00', 'end' => '11:30',
                'cust_msg' => 'Mas Tri, kabel kawat Eterna ada di dekat meja kasir.', 'mitra_msg' => 'Siap Pak Bayu, kabel saya pasang klem rapi di dinding.',
                'cust_rev' => 'Gudang jadi terang benderang, kabel sangat rapi.', 'mitra_rev' => 'Instalasi berjalan aman dan mudah.'
            ],
            [
                'order_id' => 'HELP-20260811-SLM06', 'cust' => $custSleman3, 'mitra' => $mitraSleman1, 'city_id' => $slemanId,
                'title' => 'Pindahan Meja Kerja Kayu & Kursi Kantor Ngaglik', 'amount' => 60000,
                'desc' => 'Angkut meja kerja kayu mahoni dan 2 kursi ergonomis antar ruangan lantai 1.',
                'loc' => 'Sariharjo, Ngaglik, Sleman', 'addr' => 'Jl. Palagan KM 7 No. 20, Sariharjo, Ngaglik, Sleman',
                'lat' => -7.7314, 'lng' => 110.3751, 'date' => '2026-08-11', 'start' => '13:00', 'end' => '14:30',
                'cust_msg' => 'Mas Agus, tolong dialasi kardus saat digeser ya.', 'mitra_msg' => 'Pasti Mbak Maya, lantai granit dijamin aman.',
                'cust_rev' => 'Pekerjaan cepat dan sangat hati-hati.', 'mitra_rev' => 'Pelayanan customer sangat ramah.'
            ],

            // --- HARI 09: 12 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260812-SKT05', 'cust' => $custSolo1, 'mitra' => $mitraSolo2, 'city_id' => $soloId,
                'title' => 'Perapihan Kabel LAN & Rak Server Studio Foto', 'amount' => 70000,
                'desc' => 'Pemasangan kabel duct protector dan penataan 8 jalur kabel LAN di studio kerja.',
                'loc' => 'Sondakan, Laweyan, Surakarta', 'addr' => 'Jl. Dr. Radjiman No. 512, Sondakan, Laweyan, Surakarta',
                'lat' => -7.5689, 'lng' => 110.7981, 'date' => '2026-08-12', 'start' => '10:00', 'end' => '12:00',
                'cust_msg' => 'Mas Hendra, kabel duct putih sudah saya beli 4 batang.', 'mitra_msg' => 'Siap Bu Dewi, saya tata jalur kabelnya biar estetik.',
                'cust_rev' => 'Studio jadi rapi tanpa kabel berseliweran.', 'mitra_rev' => 'Ruang kerja sangat nyaman.'
            ],
            [
                'order_id' => 'HELP-20260812-JOG03', 'cust' => $custJogja1, 'mitra' => $mitraJogja1, 'city_id' => $jogjaId,
                'title' => 'Pembersihan Kipas Exhaust & Plafon Dapur Resto', 'amount' => 85000,
                'desc' => 'Cuci kerak minyak di 2 exhaust fan dapur kafe dan lap dinding keramik sekitarnya.',
                'loc' => 'Danurejan, Kota Yogyakarta', 'addr' => 'Jl. Mataram No. 42, Danurejan, Yogyakarta',
                'lat' => -7.7942, 'lng' => 110.3689, 'date' => '2026-08-12', 'start' => '15:00', 'end' => '17:30',
                'cust_msg' => 'Mas Danang, sabun pembersih grease ada di rak dapur.', 'mitra_msg' => 'Siap Mbak Nia, saya bongkar baling-balingnya dan rendam air panas.',
                'cust_rev' => 'Exhaust fan bersih seperti baru, putaran kembali enteng.', 'mitra_rev' => 'Peralatan dapur lengkap dan sangat higienis.'
            ],

            // --- HARI 10: 13 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260813-SLM07', 'cust' => $custSleman1, 'mitra' => $mitraSleman3, 'city_id' => $slemanId,
                'title' => 'Pengangkutan Kardus Arsip & Dokumen Pajak Sleman', 'amount' => 65000,
                'desc' => 'Pindahkan 10 kardus berkas arsip dari kantor cabang ke tempat penyimpanan.',
                'loc' => 'Sinduharjo, Ngaglik, Sleman', 'addr' => 'Jl. Kaliurang KM 8 No. 12, Sinduharjo, Ngaglik, Sleman',
                'lat' => -7.7289, 'lng' => 110.3891, 'date' => '2026-08-13', 'start' => '08:30', 'end' => '10:30',
                'cust_msg' => 'Mas Fajar, tolong disusun sesuai label tahun ya.', 'mitra_msg' => 'Siap Bu Rina, saya tata urut 2023 sampai 2025.',
                'cust_rev' => 'Penyusunan arsip rapi dan sesuai instruksi.', 'mitra_rev' => 'Kardus sudah berlabel jelas, kerja lebih efisien.'
            ],
            [
                'order_id' => 'HELP-20260813-SKH05', 'cust' => $custSkh1, 'mitra' => $mitraSkh1, 'city_id' => $sukoharjoId,
                'title' => 'Perbaikan Pintu Kamar Mandi Macet Kartasura', 'amount' => 55000,
                'desc' => 'Setel engsel aluminium pintu kamar mandi yang turun dan seret saat ditutup.',
                'loc' => 'Ngadirejo, Kartasura, Sukoharjo', 'addr' => 'Perum Baki Permai Blok C-12, Ngadirejo, Kartasura, Sukoharjo',
                'lat' => -7.5521, 'lng' => 110.7482, 'date' => '2026-08-13', 'start' => '13:00', 'end' => '14:30',
                'cust_msg' => 'Mas Tri, engsel baru sudah disiapkan jika perlu ganti.', 'mitra_msg' => 'Saya cek dulu Bu, jika cukup diganjal ring stainless saya pasangkan.',
                'cust_rev' => 'Pintu tertutup rapat dan lancar tanpa bunyi derit.', 'mitra_rev' => 'Pengerjaan cepat dan customer sangat baik.'
            ],

            // --- HARI 11: 14 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260814-SKT06', 'cust' => $custSolo2, 'mitra' => $mitraSolo2, 'city_id' => $soloId,
                'title' => 'Pengantaran Kue Basah & Snack Box Acara Hajatan', 'amount' => 50000,
                'desc' => 'Antar 50 box kue basah tradisional dari Joyosuran ke Balai Warga Pasar Kliwon.',
                'loc' => 'Joyosuran, Pasar Kliwon, Surakarta', 'addr' => 'Jl. Veteran No. 89, Joyosuran, Pasar Kliwon, Surakarta',
                'lat' => -7.5812, 'lng' => 110.8321, 'date' => '2026-08-14', 'start' => '07:30', 'end' => '09:00',
                'cust_msg' => 'Mas Hendra, kuenya jangan ditumpuk lebih dari 5 ya.', 'mitra_msg' => 'Siap Mbak Anisa, saya susun di box khusus datar.',
                'cust_rev' => 'Kue tiba tepat waktu dan bentuknya tetap sempurna.', 'mitra_rev' => 'Pengemasan dari customer sangat rapi.'
            ],
            [
                'order_id' => 'HELP-20260814-SLM08', 'cust' => $custSleman2, 'mitra' => $mitraSleman1, 'city_id' => $slemanId,
                'title' => 'Pemasangan Tandon Air & Pipa Sambungan Bypass Gamping', 'amount' => 110000,
                'desc' => 'Bantu pasang pipa bypass tandon air 500 liter dan instalasi otomatis radar pelampung.',
                'loc' => 'Banyuraden, Gamping, Sleman', 'addr' => 'Jl. Ringroad Barat No. 108, Banyuraden, Gamping, Sleman',
                'lat' => -7.7845, 'lng' => 110.3341, 'date' => '2026-08-14', 'start' => '13:00', 'end' => '16:30',
                'cust_msg' => 'Mas Agus, posisi radar pelampung sudah disesuaikan ketinggiannya ya.', 'mitra_msg' => 'Baik Bu Siti, otomatis toren sudah ditest jalan normal.',
                'cust_rev' => 'Pompa otomatis mati saat toren penuh, mantap.', 'mitra_rev' => 'Pemasangan radar toren presisi dan rapi.'
            ],

            // --- HARI 12: 15 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260815-SKH06', 'cust' => $custSkh2, 'mitra' => $mitraSkh2, 'city_id' => $sukoharjoId,
                'title' => 'Pengangkutan Meja Kasir Kaca & Etalase Kecil Sukoharjo', 'amount' => 80000,
                'desc' => 'Pindahkan meja kasir kaca dan etalase suplemen antar toko berjarak 1 km.',
                'loc' => 'Jombor, Sukoharjo', 'addr' => 'Jl. Jenderal Sudirman No. 80, Jombor, Sukoharjo',
                'lat' => -7.6841, 'lng' => 110.8392, 'date' => '2026-08-15', 'start' => '09:00', 'end' => '11:15',
                'cust_msg' => 'Pak Joko, sudut kacanya tolong dibungkus lakban tebal ya.', 'mitra_msg' => 'Aman Pak Bayu, saya bungkus bubble wrap rangkap.',
                'cust_rev' => 'Etalase sampai selamat tanpa goresan.', 'mitra_rev' => 'Customer sangat kooperatif saat pengangkatan.'
            ],
            [
                'order_id' => 'HELP-20260815-JOG04', 'cust' => $custJogja1, 'mitra' => $mitraJogja1, 'city_id' => $jogjaId,
                'title' => 'Pemasangan Lampu Spotlight Display Butik Jogja', 'amount' => 70000,
                'desc' => 'Pasang 4 unit track light LED warm white di plafon showroom busana.',
                'loc' => 'Danurejan, Kota Yogyakarta', 'addr' => 'Jl. Mataram No. 42, Danurejan, Yogyakarta',
                'lat' => -7.7942, 'lng' => 110.3689, 'date' => '2026-08-15', 'start' => '14:00', 'end' => '16:00',
                'cust_msg' => 'Mas Danang, sorot lampu diarahkan ke manekin tengah ya.', 'mitra_msg' => 'Siap Mbak Nia, sudut pencahayaan sudah saya atur pas.',
                'cust_rev' => 'Toko jadi tampak mewah dan pencahayaan sangat pas.', 'mitra_rev' => 'Track light berkualitas bagus dan mudah disetel.'
            ],

            // --- HARI 13: 16 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260816-SLM09', 'cust' => $custSleman3, 'mitra' => $mitraSleman2, 'city_id' => $slemanId,
                'title' => 'Pembersihan Toren Air & Kuras Bak Kamar Mandi Ngaglik', 'amount' => 85000,
                'desc' => 'Kuras toren air 650 liter dan sikat lumut dasar bak mandi sampai bersih.',
                'loc' => 'Sariharjo, Ngaglik, Sleman', 'addr' => 'Jl. Palagan KM 7 No. 20, Sariharjo, Ngaglik, Sleman',
                'lat' => -7.7314, 'lng' => 110.3751, 'date' => '2026-08-16', 'start' => '08:00', 'end' => '10:45',
                'cust_msg' => 'Pak Budi, kaporit bubuk ada di dekat garasi jika dibutuhkan.', 'mitra_msg' => 'Saya kuras dengan spons dan bilas bersih alami Bu.',
                'cust_rev' => 'Air kran kembali jernih dan toren bebas lumut.', 'mitra_rev' => 'Kondisi toren mudah dijangkau dan pengerjaan lancar.'
            ],
            [
                'order_id' => 'HELP-20260816-SKT07', 'cust' => $custSolo1, 'mitra' => $mitraSolo1, 'city_id' => $soloId,
                'title' => 'Pemasangan Papan Nama Akrilik Studio Banjarsari', 'amount' => 60000,
                'desc' => 'Bor dinding dan pasang baut pen akrilik signboard studio foto di teras depan.',
                'loc' => 'Timuran, Banjarsari, Surakarta', 'addr' => 'Jl. Slamet Riyadi No. 182, Timuran, Banjarsari, Surakarta',
                'lat' => -7.5645, 'lng' => 110.8142, 'date' => '2026-08-16', 'start' => '13:30', 'end' => '15:15',
                'cust_msg' => 'Pak Eko, posisi papan sejajar dengan lis plang atas ya.', 'mitra_msg' => 'Siap Bu Dewi, sudah saya timbang waterpass lurus presisi.',
                'cust_rev' => 'Papan nama terpasang kokoh dan rapi.', 'mitra_rev' => 'Baut pen akrilik bagus dan presisi.'
            ],

            // --- HARI 14: 17 AGUSTUS 2026 (Spesial Hari Kemerdekaan) ---
            [
                'order_id' => 'HELP-20260817-SLM10', 'cust' => $custSleman1, 'mitra' => $mitraSleman1, 'city_id' => $slemanId,
                'title' => 'Pemasangan Tiang Bendera & Umbul-umbul 17 Agustus', 'amount' => 75000,
                'desc' => 'Pasang tiang bendera bambu 4 meter dan 8 umbul-umbul merah putih di sepanjang pagar depan.',
                'loc' => 'Sinduharjo, Ngaglik, Sleman', 'addr' => 'Jl. Kaliurang KM 8 No. 12, Sinduharjo, Ngaglik, Sleman',
                'lat' => -7.7289, 'lng' => 110.3891, 'date' => '2026-08-17', 'start' => '07:00', 'end' => '09:30',
                'cust_msg' => 'Mas Agus, tali kawat dan bendera sudah di teras.', 'mitra_msg' => 'Siap Bu Rina, saya ikat kencang anti roboh ditiup angin.',
                'cust_rev' => 'Depan rumah jadi meriah dan rapi menyambut kemerdekaan.', 'mitra_rev' => 'Semangat 17-an, kerja gembira dan cepat selesai.'
            ],
            [
                'order_id' => 'HELP-20260817-SKH07', 'cust' => $custSkh1, 'mitra' => $mitraSkh1, 'city_id' => $sukoharjoId,
                'title' => 'Dekorasi Gapura Merah Putih & Lampu Selang Sukoharjo', 'amount' => 85000,
                'desc' => 'Bantu pasang lampu selang hias warna warni di gapura komplek rumah Kartasura.',
                'loc' => 'Ngadirejo, Kartasura, Sukoharjo', 'addr' => 'Perum Baki Permai Blok C-12, Ngadirejo, Kartasura, Sukoharjo',
                'lat' => -7.5521, 'lng' => 110.7482, 'date' => '2026-08-17', 'start' => '14:00', 'end' => '16:30',
                'cust_msg' => 'Mas Tri, sambungan steker tolong dibungkus solasi bakar ya.', 'mitra_msg' => 'Pasti Bu Indah, sambungan listrik aman outdoor.',
                'cust_rev' => 'Lampu menyala indah dan sambungan listrik aman.', 'mitra_rev' => 'Gotong royong lingkungan sangat menyenangkan.'
            ],

            // --- HARI 15: 18 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260818-SKT08', 'cust' => $custSolo2, 'mitra' => $mitraSolo1, 'city_id' => $soloId,
                'title' => 'Pembersihan Lumut Dinding Belakang & Paving Solo', 'amount' => 75000,
                'desc' => 'Sikat lumut paving blok carport dan semprot cairan anti jamur di dinding belakang.',
                'loc' => 'Joyosuran, Pasar Kliwon, Surakarta', 'addr' => 'Jl. Veteran No. 89, Joyosuran, Pasar Kliwon, Surakarta',
                'lat' => -7.5812, 'lng' => 110.8321, 'date' => '2026-08-18', 'start' => '08:30', 'end' => '11:15',
                'cust_msg' => 'Pak Eko, selang air panjang ada di pojok taman.', 'mitra_msg' => 'Siap Mbak Anisa, saya sikat sampai warna paving terang lagi.',
                'cust_rev' => 'Paving carport tidak licin lagi dan sangat bersih.', 'mitra_rev' => 'Saluran pembuangan air lancar, kerja tuntas.'
            ],
            [
                'order_id' => 'HELP-20260818-SLM11', 'cust' => $custSleman2, 'mitra' => $mitraSleman2, 'city_id' => $slemanId,
                'title' => 'Penggantian Pompa Air Pendorong Shower Mlati', 'amount' => 95000,
                'desc' => 'Bongkar pompa pendorong lama yang terbakar dan pasang pompa booster baru merk Shimizu.',
                'loc' => 'Sinduadi, Mlati, Sleman', 'addr' => 'Jl. Magelang KM 6.5, Sinduadi, Mlati, Sleman',
                'lat' => -7.7610, 'lng' => 110.3725, 'date' => '2026-08-18', 'start' => '13:00', 'end' => '15:30',
                'cust_msg' => 'Pak Budi, kabel power pompa disambung ke saklar MCB dalam ya.', 'mitra_msg' => 'Siap Bu Siti, saya rapikan kabel dan tes tekanan shower.',
                'cust_rev' => 'Tekanan air shower kencang dan suara mesin halus.', 'mitra_rev' => 'Pompa baru kualitas bagus, mudah dipasang.'
            ],

            // --- HARI 16: 19 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260819-SKH08', 'cust' => $custSkh2, 'mitra' => $mitraSkh1, 'city_id' => $sukoharjoId,
                'title' => 'Penggantian Stop Kontak & Lampu Downlight Sukoharjo', 'amount' => 60000,
                'desc' => 'Ganti 4 unit stop kontak dinding Panasonic dan pasang 2 lampu downlight Philips.',
                'loc' => 'Jombor, Sukoharjo', 'addr' => 'Jl. Jenderal Sudirman No. 80, Jombor, Sukoharjo',
                'lat' => -7.6841, 'lng' => 110.8392, 'date' => '2026-08-19', 'start' => '09:00', 'end' => '11:00',
                'cust_msg' => 'Mas Tri, MCB utama sudah saya matikan.', 'mitra_msg' => 'Bagus Pak Bayu, pemasangan aman dan baut kencang.',
                'cust_rev' => 'Semua saklar berfungsi normal dan rapi.', 'mitra_rev' => 'Peralatan dan part lengkap, pengerjaan cepat.'
            ],
            [
                'order_id' => 'HELP-20260819-JOG05', 'cust' => $custJogja1, 'mitra' => $mitraJogja1, 'city_id' => $jogjaId,
                'title' => 'Perbaikan Engsel Pintu Lemari Kaca Display Kafe', 'amount' => 50000,
                'desc' => 'Setel engsel sendok lemari kaca cake display yang longgar dan bunyi derit.',
                'loc' => 'Danurejan, Kota Yogyakarta', 'addr' => 'Jl. Mataram No. 42, Danurejan, Yogyakarta',
                'lat' => -7.7942, 'lng' => 110.3689, 'date' => '2026-08-19', 'start' => '14:00', 'end' => '15:15',
                'cust_msg' => 'Mas Danang, kaca display tolong jangan ditekan kuat ya.', 'mitra_msg' => 'Siap Mbak Nia, saya ganti baut pengunci baru yang presisi.',
                'cust_rev' => 'Pintu lemari tertutup lembut tanpa celah.', 'mitra_rev' => 'Pengerjaan lancar dan aman.'
            ],

            // --- HARI 17: 20 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260820-SLM12', 'cust' => $custSleman3, 'mitra' => $mitraSleman1, 'city_id' => $slemanId,
                'title' => 'Pembersihan Talang Air Hujan & Genteng Ngaglik', 'amount' => 80000,
                'desc' => 'Bersihkan tumpukan daun kering di talang seng dan rapikan 5 genteng yang geser.',
                'loc' => 'Sariharjo, Ngaglik, Sleman', 'addr' => 'Jl. Palagan KM 7 No. 20, Sariharjo, Ngaglik, Sleman',
                'lat' => -7.7314, 'lng' => 110.3751, 'date' => '2026-08-20', 'start' => '08:30', 'end' => '11:00',
                'cust_msg' => 'Mas Agus, hati-hati saat melangkah di atap belakang ya.', 'mitra_msg' => 'Siap Mbak Maya, saya bawa sepatu karet anti selip.',
                'cust_rev' => 'Talang air bersih, tidak takut bocor saat musim hujan.', 'mitra_rev' => 'Konstruksi atap kokoh dan aman dipijak.'
            ],
            [
                'order_id' => 'HELP-20260820-SKT09', 'cust' => $custSolo1, 'mitra' => $mitraSolo2, 'city_id' => $soloId,
                'title' => 'Pengecatan Primer Anti Karat Pagar Depan Solo', 'amount' => 90000,
                'desc' => 'Amplas karat dan aplikasikan 2 lapis cat dasar zinkromat anti karat pada pagar.',
                'loc' => 'Sondakan, Laweyan, Surakarta', 'addr' => 'Jl. Dr. Radjiman No. 512, Sondakan, Laweyan, Surakarta',
                'lat' => -7.5689, 'lng' => 110.7981, 'date' => '2026-08-20', 'start' => '13:00', 'end' => '16:00',
                'cust_msg' => 'Mas Hendra, cat primer zinkromat abu-abu ada di teras.', 'mitra_msg' => 'Siap Bu Dewi, saya kuas rata sampai celah baut.',
                'cust_rev' => 'Cat dasar rata dan rapi, siap untuk cat utama.', 'mitra_rev' => 'Bahan cat berkualitas dan lokasi teduh.'
            ],

            // --- HARI 18: 21 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260821-SKH09', 'cust' => $custSkh1, 'mitra' => $mitraSkh2, 'city_id' => $sukoharjoId,
                'title' => 'Pindahan Rak Sepatu & Meja Teras Kartasura', 'amount' => 50000,
                'desc' => 'Pindahkan 1 set rak sepatu kayu jati dan meja taman ke teras samping.',
                'loc' => 'Ngadirejo, Kartasura, Sukoharjo', 'addr' => 'Perum Baki Permai Blok C-12, Ngadirejo, Kartasura, Sukoharjo',
                'lat' => -7.5521, 'lng' => 110.7482, 'date' => '2026-08-21', 'start' => '09:00', 'end' => '10:15',
                'cust_msg' => 'Pak Joko, tolong dialasi kain saat digeser ya.', 'mitra_msg' => 'Siap Bu Indah, saya angkat berdua tanpa geser lantai.',
                'cust_rev' => 'Barang dipindah tanpa lecet sedikitpun.', 'mitra_rev' => 'Customer sangat baik dan ramah.'
            ],
            [
                'order_id' => 'HELP-20260821-SLM13', 'cust' => $custSleman1, 'mitra' => $mitraSleman3, 'city_id' => $slemanId,
                'title' => 'Pemasangan Gantungan Baju & Rak Handuk Kamar Mandi', 'amount' => 55000,
                'desc' => 'Bor keramik kamar mandi dan pasang gantungan stainless 5 kait serta rak sabun sudut.',
                'loc' => 'Sinduharjo, Ngaglik, Sleman', 'addr' => 'Jl. Kaliurang KM 8 No. 12, Sinduharjo, Ngaglik, Sleman',
                'lat' => -7.7289, 'lng' => 110.3891, 'date' => '2026-08-21', 'start' => '14:00', 'end' => '15:30',
                'cust_msg' => 'Mas Fajar, mata bor kaca khusus keramik sudah ada di rak.', 'mitra_msg' => 'Baik Bu Rina, saya bor perlahan agar keramik tidak retak.',
                'cust_rev' => 'Keramik utuh mulus dan rak terpasang kuat.', 'mitra_rev' => 'Keramik berkualitas dan mudah dibor.'
            ],

            // --- HARI 19: 22 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260822-SKT10', 'cust' => $custSolo2, 'mitra' => $mitraSolo1, 'city_id' => $soloId,
                'title' => 'Pengangkutan Kardus Souvenir Pernikahan Solo', 'amount' => 60000,
                'desc' => 'Angkut 12 kardus souvenir keramik dari mobil ke dalam ruang tamu keluarga.',
                'loc' => 'Joyosuran, Pasar Kliwon, Surakarta', 'addr' => 'Jl. Veteran No. 89, Joyosuran, Pasar Kliwon, Surakarta',
                'lat' => -7.5812, 'lng' => 110.8321, 'date' => '2026-08-22', 'start' => '10:00', 'end' => '11:45',
                'cust_msg' => 'Pak Eko, kardusnya ada label barang pecah belah ya.', 'mitra_msg' => 'Siap Mbak Anisa, saya bawa satu per satu dengan hati-hati.',
                'cust_rev' => 'Semua souvenir utuh tanpa ada yang pecah.', 'mitra_rev' => 'Kardus tertata rapi di dalam mobil, mudah diturunkan.'
            ],
            [
                'order_id' => 'HELP-20260822-JOG06', 'cust' => $custJogja1, 'mitra' => $mitraSleman1, 'city_id' => $jogjaId,
                'title' => 'Pemasangan Exhaust Fan Dinding Kamar Mandi Kafe', 'amount' => 75000,
                'desc' => 'Pasang unit exhaust fan dinding 8 inch merk Maspion di ventilasi kamar mandi tamu.',
                'loc' => 'Danurejan, Kota Yogyakarta', 'addr' => 'Jl. Mataram No. 42, Danurejan, Yogyakarta',
                'lat' => -7.7942, 'lng' => 110.3689, 'date' => '2026-08-22', 'start' => '14:00', 'end' => '16:00',
                'cust_msg' => 'Mas Agus, kabel disambung ke saklar lampu otomatis ya.', 'mitra_msg' => 'Siap Mbak Nia, jadi saat lampu nyala exhaust otomatis hisap.',
                'cust_rev' => 'Sirkulasi udara kamar mandi jadi sangat segar.', 'mitra_rev' => 'Sistem saklar terintegrasi dengan baik.'
            ],

            // --- HARI 20: 23 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260823-SKH10', 'cust' => $custSkh2, 'mitra' => $mitraSkh1, 'city_id' => $sukoharjoId,
                'title' => 'Pemasangan Rak Dinding Kayu Buku Apotek Sukoharjo', 'amount' => 70000,
                'desc' => 'Pasang 3 ambalan rak buku melayang dinding untuk buku referensi obat farmasi.',
                'loc' => 'Jombor, Sukoharjo', 'addr' => 'Jl. Jenderal Sudirman No. 80, Jombor, Sukoharjo',
                'lat' => -7.6841, 'lng' => 110.8392, 'date' => '2026-08-23', 'start' => '09:00', 'end' => '11:15',
                'cust_msg' => 'Mas Tri, jarak antar ambalan 35 cm ya.', 'mitra_msg' => 'Siap Pak Bayu, sudah saya ukur dan beri tanda pensil.',
                'cust_rev' => 'Rak dinding kokoh dan sangat presisi.', 'mitra_rev' => 'Dinding bata merah kokoh dan baut masuk sempurna.'
            ],
            [
                'order_id' => 'HELP-20260823-SLM14', 'cust' => $custSleman2, 'mitra' => $mitraSleman2, 'city_id' => $slemanId,
                'title' => 'Penggantian Keran Shower Kamar Mandi Utama Mlati', 'amount' => 55000,
                'desc' => 'Ganti keran mixer panas dingin shower yang rembes air di dinding kamar mandi.',
                'loc' => 'Sinduadi, Mlati, Sleman', 'addr' => 'Jl. Magelang KM 6.5, Sinduadi, Mlati, Sleman',
                'lat' => -7.7610, 'lng' => 110.3725, 'date' => '2026-08-23', 'start' => '13:30', 'end' => '15:00',
                'cust_msg' => 'Pak Budi, seal tape tebal sudah ada di atas wastafel.', 'mitra_msg' => 'Siap Bu Siti, drat kuningan saya lapisi seal tape rapat.',
                'cust_rev' => 'Shower tidak bocor lagi dan aliran lancar.', 'mitra_rev' => 'Part mixer shower baru sangat pas di soket.'
            ],

            // --- HARI 21: 24 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260824-SKT11', 'cust' => $custSolo1, 'mitra' => $mitraSolo2, 'city_id' => $soloId,
                'title' => 'Perakitan Meja Rias & Cermin LED Banjarsari', 'amount' => 80000,
                'desc' => 'Merakit meja rias modern 3 laci beserta pemasangan cermin bulat lampu sentuh LED.',
                'loc' => 'Timuran, Banjarsari, Surakarta', 'addr' => 'Jl. Slamet Riyadi No. 182, Timuran, Banjarsari, Surakarta',
                'lat' => -7.5645, 'lng' => 110.8142, 'date' => '2026-08-24', 'start' => '10:00', 'end' => '12:30',
                'cust_msg' => 'Mas Hendra, adaptor colokan cermin ditaruh di laci bawah ya.', 'mitra_msg' => 'Siap Bu Dewi, rel laci dan sensor sentuh cermin sudah ditest normal.',
                'cust_rev' => 'Meja rias cantik terpasang kuat, lampu LED menyala sempurna.', 'mitra_rev' => 'Produk IKEA berkualitas, instruksi jelas.'
            ],
            [
                'order_id' => 'HELP-20260824-SLM15', 'cust' => $custSleman3, 'mitra' => $mitraSleman3, 'city_id' => $slemanId,
                'title' => 'Pembersihan Lumut Kolam Ikan Koi Ngaglik', 'amount' => 90000,
                'desc' => 'Kuras filter chamber kolam koi dan sikat lumut dinding kaca kolam tanpa bahan kimia.',
                'loc' => 'Sariharjo, Ngaglik, Sleman', 'addr' => 'Jl. Palagan KM 7 No. 20, Sariharjo, Ngaglik, Sleman',
                'lat' => -7.7314, 'lng' => 110.3751, 'date' => '2026-08-24', 'start' => '13:30', 'end' => '16:30',
                'cust_msg' => 'Mas Fajar, ikan koi tolong ditampung di bak aerasi dulu ya.', 'mitra_msg' => 'Pasti Mbak Maya, media filter bioball dan matala saya cuci bersih.',
                'cust_rev' => 'Kolam bening kembali dan ikan koi berenang aktif.', 'mitra_rev' => 'Sistem filter kolam sangat bagus dan terawat.'
            ],

            // --- HARI 22: 25 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260825-SKH11', 'cust' => $custSkh1, 'mitra' => $mitraSkh2, 'city_id' => $sukoharjoId,
                'title' => 'Pengangkutan Tanaman Pot Tabulampot Kartasura', 'amount' => 65000,
                'desc' => 'Pindahkan 6 pot besar tanaman buah jambu air dan kelengkeng dari depan ke halaman samping.',
                'loc' => 'Ngadirejo, Kartasura, Sukoharjo', 'addr' => 'Perum Baki Permai Blok C-12, Ngadirejo, Kartasura, Sukoharjo',
                'lat' => -7.5521, 'lng' => 110.7482, 'date' => '2026-08-25', 'start' => '08:30', 'end' => '10:30',
                'cust_msg' => 'Pak Joko, potnya cukup berat tolong gunakan papan ganjal ya.', 'mitra_msg' => 'Siap Bu Indah, saya pakai troli dorong tanaman.',
                'cust_rev' => 'Tanaman tertata rapi dan pot tidak ada yang retak.', 'mitra_rev' => 'Halaman asri dan customer sangat ramah.'
            ],
            [
                'order_id' => 'HELP-20260825-JOG07', 'cust' => $custJogja1, 'mitra' => $mitraJogja1, 'city_id' => $jogjaId,
                'title' => 'Pemasangan Kunci Gembok Pintu Gerbang Lipat Jogja', 'amount' => 50000,
                'desc' => 'Las kupingan plat gembok baru pada pintu lipat garasi butik.',
                'loc' => 'Danurejan, Kota Yogyakarta', 'addr' => 'Jl. Mataram No. 42, Danurejan, Yogyakarta',
                'lat' => -7.7942, 'lng' => 110.3689, 'date' => '2026-08-25', 'start' => '14:00', 'end' => '15:30',
                'cust_msg' => 'Mas Danang, plat kupingannya tebal 4mm ya.', 'mitra_msg' => 'Siap Mbak Nia, saya las matang dan lapisi cat anti karat.',
                'cust_rev' => 'Kupingan gembok kokoh dan keamanan toko terjamin.', 'mitra_rev' => 'Pintu gerbang mudah dilas dan rapi.'
            ],

            // --- HARI 23: 26 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260826-SLM16', 'cust' => $custSleman1, 'mitra' => $mitraSleman1, 'city_id' => $slemanId,
                'title' => 'Pemasangan Rak Sepatu Besi Gantung Dinding Gejayan', 'amount' => 60000,
                'desc' => 'Bor dinding teras dan pasang 2 unit rak sepatu besi hollow 4 susun.',
                'loc' => 'Sinduharjo, Ngaglik, Sleman', 'addr' => 'Jl. Kaliurang KM 8 No. 12, Sinduharjo, Ngaglik, Sleman',
                'lat' => -7.7289, 'lng' => 110.3891, 'date' => '2026-08-26', 'start' => '09:00', 'end' => '10:45',
                'cust_msg' => 'Mas Agus, baut dynabolt sudah disiapkan di kotak.', 'mitra_msg' => 'Siap Bu Rina, saya bor rapi dan kencangkan kuat.',
                'cust_rev' => 'Rak sepatu kokoh menempel dinding, teras jadi luas.', 'mitra_rev' => 'Dinding bata keras, dynabolt mengikat kuat.'
            ],
            [
                'order_id' => 'HELP-20260826-SKT12', 'cust' => $custSolo2, 'mitra' => $mitraSolo1, 'city_id' => $soloId,
                'title' => 'Penggantian Keran Air Taman & Cuci Piring Pasar Kliwon', 'amount' => 50000,
                'desc' => 'Ganti 2 unit kran air model engkol di taman depan dan tempat cuci piring belakang.',
                'loc' => 'Joyosuran, Pasar Kliwon, Surakarta', 'addr' => 'Jl. Veteran No. 89, Joyosuran, Pasar Kliwon, Surakarta',
                'lat' => -7.5812, 'lng' => 110.8321, 'date' => '2026-08-26', 'start' => '14:00', 'end' => '15:15',
                'cust_msg' => 'Pak Eko, kran kuningan baru ada di dapur.', 'mitra_msg' => 'Siap Mbak Anisa, saya pasangkan seal tape tebal biar tidak rembes.',
                'cust_rev' => 'Keran air lancar dibuka tutup dan tidak menetes.', 'mitra_rev' => 'Penggantian keran standar dan berjalan mulus.'
            ],

            // --- HARI 24: 27 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260827-SKH12', 'cust' => $custSkh2, 'mitra' => $mitraSkh1, 'city_id' => $sukoharjoId,
                'title' => 'Pemasangan Tirai Magnet Anti Nyamuk Pintu Belakang Sukoharjo', 'amount' => 45000,
                'desc' => 'Pasang tirai magnet pintu kasa nyamuk di pintu dapur menuju halaman belakang.',
                'loc' => 'Jombor, Sukoharjo', 'addr' => 'Jl. Jenderal Sudirman No. 80, Jombor, Sukoharjo',
                'lat' => -7.6841, 'lng' => 110.8392, 'date' => '2026-08-27', 'start' => '09:30', 'end' => '10:45',
                'cust_msg' => 'Mas Tri, paku payung emasnya dipasang rapat ya.', 'mitra_msg' => 'Siap Pak Bayu, magnet tengah sudah saya coba menutup rapat otomatis.',
                'cust_rev' => 'Tirai menutup rapat otomatis, nyamuk tidak bisa masuk.', 'mitra_rev' => 'Kusen kayu jati lurus, pemasangan mudah.'
            ],
            [
                'order_id' => 'HELP-20260827-SLM17', 'cust' => $custSleman2, 'mitra' => $mitraSleman3, 'city_id' => $slemanId,
                'title' => 'Pembersihan Rumput Samping & Pangkas Pucuk Merah Gamping', 'amount' => 75000,
                'desc' => 'Babat rumput liar di samping rumah dan bentuk bulat 4 pohon tanaman pucuk merah.',
                'loc' => 'Banyuraden, Gamping, Sleman', 'addr' => 'Jl. Ringroad Barat No. 108, Banyuraden, Gamping, Sleman',
                'lat' => -7.7845, 'lng' => 110.3341, 'date' => '2026-08-27', 'start' => '13:00', 'end' => '15:45',
                'cust_msg' => 'Mas Fajar, gunting tanaman besar ada di dekat sumur.', 'mitra_msg' => 'Siap Bu Siti, saya rapikan bentuk bulat rapi.',
                'cust_rev' => 'Pucuk merah berbentuk bulat cantik dan halaman bersih.', 'mitra_rev' => 'Tanaman subur dan mudah dibentuk.'
            ],

            // --- HARI 25: 28 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260828-SLM18', 'cust' => $custSleman1, 'mitra' => $mitraSleman2, 'city_id' => $slemanId,
                'title' => 'Penataan Tatakan Pot Gerabah Tanaman Palem', 'amount' => 50000,
                'desc' => 'Tata ulang 6 pot gerabah besar tanaman palem dan pasang tatakan karet drainase.',
                'loc' => 'Sinduharjo, Ngaglik, Sleman', 'addr' => 'Jl. Kaliurang KM 8 No. 12, Sinduharjo, Ngaglik, Sleman',
                'lat' => -7.7289, 'lng' => 110.3891, 'date' => '2026-08-28', 'start' => '08:30', 'end' => '10:00',
                'cust_msg' => 'Pak Budi, tatakan karetnya ditaruh di bawah lubang air pot ya.', 'mitra_msg' => 'Siap Bu Rina, air siraman mengalir lancar ke saluran.',
                'cust_rev' => 'Teras bersih tanpa bekas genangan air pot.', 'mitra_rev' => 'Customer sangat teliti dan menghargai kerapian.'
            ],
            [
                'order_id' => 'HELP-20260828-SKT13', 'cust' => $custSolo1, 'mitra' => $mitraSolo1, 'city_id' => $soloId,
                'title' => 'Pemasangan Gantungan Pigura Foto & Lukisan Solo', 'amount' => 55000,
                'desc' => 'Bor dan pasang 5 pigura foto kanvas keluarga berukuran 40x60 di dinding ruang keluarga.',
                'loc' => 'Timuran, Banjarsari, Surakarta', 'addr' => 'Jl. Slamet Riyadi No. 182, Timuran, Banjarsari, Surakarta',
                'lat' => -7.5645, 'lng' => 110.8142, 'date' => '2026-08-28', 'start' => '13:30', 'end' => '15:15',
                'cust_msg' => 'Pak Eko, jarak antar bingkai 10 cm rata ya.', 'mitra_msg' => 'Beres Bu Dewi, sudah saya timbang waterpass sejajar sempurna.',
                'cust_rev' => 'Foto keluarga terpasang sejajar indah dan kokoh.', 'mitra_rev' => 'Pigura kanvas rapi dan dinding mulus.'
            ],

            // --- HARI 26: 29 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260829-SKH13', 'cust' => $custSkh1, 'mitra' => $mitraSkh1, 'city_id' => $sukoharjoId,
                'title' => 'Pembersihan Filter Udara AC & Cek Freon Kartasura', 'amount' => 80000,
                'desc' => 'Bersihkan evaporator dan cek tekanan manifold freon R32 AC ruang tamu.',
                'loc' => 'Ngadirejo, Kartasura, Sukoharjo', 'addr' => 'Perum Baki Permai Blok C-12, Ngadirejo, Kartasura, Sukoharjo',
                'lat' => -7.5521, 'lng' => 110.7482, 'date' => '2026-08-29', 'start' => '09:00', 'end' => '11:00',
                'cust_msg' => 'Mas Tri, tekanan freonnya tolong dipastikan normal ya.', 'mitra_msg' => 'Siap Bu Indah, tekanan 140 psi sangat ideal dan dingin segar.',
                'cust_rev' => 'AC kembali dingin maksimal dan hembusan angin wangi.', 'mitra_rev' => 'Kondisi kompresor prima dan terawat.'
            ],
            [
                'order_id' => 'HELP-20260829-JOG08', 'cust' => $custJogja1, 'mitra' => $mitraJogja1, 'city_id' => $jogjaId,
                'title' => 'Perbaikan Handel Pintu Kaca & Kunci Showcase Jogja', 'amount' => 60000,
                'desc' => 'Ganti handle pintu kaca aluminium dan pasang kunci camlock showcase display.',
                'loc' => 'Danurejan, Kota Yogyakarta', 'addr' => 'Jl. Mataram No. 42, Danurejan, Yogyakarta',
                'lat' => -7.7942, 'lng' => 110.3689, 'date' => '2026-08-29', 'start' => '14:00', 'end' => '15:30',
                'cust_msg' => 'Mas Danang, kuncinya ada 2 anak kunci di dalam box.', 'mitra_msg' => 'Siap Mbak Nia, kunci sudah ditest putar lancar.',
                'cust_rev' => 'Handle kokoh dan kunci showcase mengunci sempurna.', 'mitra_rev' => 'Pengerjaan mudah dan part presisi.'
            ],

            // --- HARI 27: 30 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260830-SLM19', 'cust' => $custSleman3, 'mitra' => $mitraSleman1, 'city_id' => $slemanId,
                'title' => 'Pengangkutan Kardus Pindahan Kantor Ngaglik', 'amount' => 70000,
                'desc' => 'Bantu angkut 8 box dokumen dan 2 printer laser ke mobil pick-up.',
                'loc' => 'Sariharjo, Ngaglik, Sleman', 'addr' => 'Jl. Palagan KM 7 No. 20, Sariharjo, Ngaglik, Sleman',
                'lat' => -7.7314, 'lng' => 110.3751, 'date' => '2026-08-30', 'start' => '08:30', 'end' => '10:30',
                'cust_msg' => 'Mas Agus, printer tolong ditaruh di kabin depan ya.', 'mitra_msg' => 'Siap Mbak Maya, saya jaga agar tidak terkena guncangan.',
                'cust_rev' => 'Pengangkutan cepat dan semua barang aman.', 'mitra_rev' => 'Barang sudah terbungkus bubble wrap dengan baik.'
            ],
            [
                'order_id' => 'HELP-20260830-SKT14', 'cust' => $custSolo2, 'mitra' => $mitraSolo2, 'city_id' => $soloId,
                'title' => 'Pengantaran Paket Hampers Kain Sutra Solo', 'amount' => 50000,
                'desc' => 'Antar 3 box hampers kain sutra eksklusif ke pelanggan di Laweyan.',
                'loc' => 'Joyosuran, Pasar Kliwon, Surakarta', 'addr' => 'Jl. Veteran No. 89, Joyosuran, Pasar Kliwon, Surakarta',
                'lat' => -7.5812, 'lng' => 110.8321, 'date' => '2026-08-30', 'start' => '13:00', 'end' => '14:30',
                'cust_msg' => 'Mas Hendra, pita hampers tolong dijaga agar tidak kusut.', 'mitra_msg' => 'Aman Mbak Anisa, saya bawa di dalam box datar khusus.',
                'cust_rev' => 'Paket diterima pelanggan dalam kondisi prima dan tepat waktu.', 'mitra_rev' => 'Pelanggan penerima sangat ramah.'
            ],

            // --- HARI 28: 31 AGUSTUS 2026 ---
            [
                'order_id' => 'HELP-20260831-SKH14', 'cust' => $custSkh2, 'mitra' => $mitraSkh2, 'city_id' => $sukoharjoId,
                'title' => 'Pembersihan Plafon Gudang Obat & Sarang Laba-laba Sukoharjo', 'amount' => 75000,
                'desc' => 'Bersihkan sarang laba-laba di seluruh sudut plafon gudang apotek setinggi 4 meter.',
                'loc' => 'Jombor, Sukoharjo', 'addr' => 'Jl. Jenderal Sudirman No. 80, Jombor, Sukoharjo',
                'lat' => -7.6841, 'lng' => 110.8392, 'date' => '2026-08-31', 'start' => '09:00', 'end' => '11:30',
                'cust_msg' => 'Pak Joko, sapu bertangkai teleskopik ada di belakang pintu.', 'mitra_msg' => 'Siap Pak Bayu, saya bersihkan sudut atas sampai bersih total.',
                'cust_rev' => 'Plafon gudang bersih total, higienitas apotek terjaga.', 'mitra_rev' => 'Peralatan teleskopik sangat membantu efisiensi kerja.'
            ],
            [
                'order_id' => 'HELP-20260831-SLM20', 'cust' => $custSleman2, 'mitra' => $mitraSleman3, 'city_id' => $slemanId,
                'title' => 'Pemasangan Lampu Sorot LED Taman Belakang Gamping', 'amount' => 65000,
                'desc' => 'Pasang lampu sorot LED outdoor 50 watt pada tiang taman dan sambungkan saklar otomatis sensor cahaya.',
                'loc' => 'Banyuraden, Gamping, Sleman', 'addr' => 'Jl. Ringroad Barat No. 108, Banyuraden, Gamping, Sleman',
                'lat' => -7.7845, 'lng' => 110.3341, 'date' => '2026-08-31', 'start' => '14:00', 'end' => '16:00',
                'cust_msg' => 'Mas Fajar, sensor cahaya ditaruh di tempat kena sinar matahari ya.', 'mitra_msg' => 'Siap Bu Siti, jadi saat senja otomatis menyala sendiri.',
                'cust_rev' => 'Taman jadi terang otomatis saat malam tiba.', 'mitra_rev' => 'Sensor photocell bekerja sempurna saat diuji.'
            ],

            // --- HARI 29: 01 SEPTEMBER 2026 ---
            [
                'order_id' => 'HELP-20260901-SLM21', 'cust' => $custSleman1, 'mitra' => $mitraSleman1, 'city_id' => $slemanId,
                'title' => 'Pemasangan Rak Dinding Dapur Stainless Gejayan', 'amount' => 65000,
                'desc' => 'Pasang rak gantung bumbu dapur stainless steel 2 tingkat di dinding keramik dapur.',
                'loc' => 'Sinduharjo, Ngaglik, Sleman', 'addr' => 'Jl. Kaliurang KM 8 No. 12, Sinduharjo, Ngaglik, Sleman',
                'lat' => -7.7289, 'lng' => 110.3891, 'date' => '2026-09-01', 'start' => '09:00', 'end' => '10:45',
                'cust_msg' => 'Mas Agus, bor keramiknya pelan-pelan ya.', 'mitra_msg' => 'Siap Bu Rina, saya beri selotip di titik bor agar tidak meleset.',
                'cust_rev' => 'Rak bumbu kokoh dan keramik tidak retak sedikitpun.', 'mitra_rev' => 'Dinding dapur rata, pemasangan berjalan lancar.'
            ],
            [
                'order_id' => 'HELP-20260901-SKT15', 'cust' => $custSolo1, 'mitra' => $mitraSolo2, 'city_id' => $soloId,
                'title' => 'Servis Engsel Pintu Garasi Mobil Solo', 'amount' => 70000,
                'desc' => 'Setel engsel dan lumasi rel roda geser pintu garasi lipat besi.',
                'loc' => 'Sondakan, Laweyan, Surakarta', 'addr' => 'Jl. Dr. Radjiman No. 512, Sondakan, Laweyan, Surakarta',
                'lat' => -7.5689, 'lng' => 110.7981, 'date' => '2026-09-01', 'start' => '13:30', 'end' => '15:30',
                'cust_msg' => 'Mas Hendra, gemuk pelumas roda ada di kaleng dekat kran.', 'mitra_msg' => 'Siap Bu Dewi, saya lumasi roda atas dan bawah biar dorongnya enteng.',
                'cust_rev' => 'Pintu garasi sangat enteng didorong dan senyap.', 'mitra_rev' => 'Roda pintu masih bagus, hanya perlu dibersihkan dan dilumasi.'
            ],

            // --- HARI 30: 02 SEPTEMBER 2026 ---
            [
                'order_id' => 'HELP-20260902-SKH15', 'cust' => $custSkh1, 'mitra' => $mitraSkh1, 'city_id' => $sukoharjoId,
                'title' => 'Pengecatan Ulang Lis Plang & Kusen Jendela Kartasura', 'amount' => 90000,
                'desc' => 'Cat ulang lis plang kayu teras dan 4 kusen jendela kamar tidur dengan politur cat kayu.',
                'loc' => 'Ngadirejo, Kartasura, Sukoharjo', 'addr' => 'Perum Baki Permai Blok C-12, Ngadirejo, Kartasura, Sukoharjo',
                'lat' => -7.5521, 'lng' => 110.7482, 'date' => '2026-09-02', 'start' => '08:30', 'end' => '12:00',
                'cust_msg' => 'Mas Tri, warna politurnya walnut brown ya.', 'mitra_msg' => 'Siap Bu Indah, saya lapisi 2 lapis agar urat kayunya keluar mengkilap.',
                'cust_rev' => 'Kusen mengkilap tampak mewah dan serat kayunya indah.', 'mitra_rev' => 'Politur cat berkualitas tinggi dan cepat kering.'
            ],
            [
                'order_id' => 'HELP-20260902-JOG09', 'cust' => $custJogja1, 'mitra' => $mitraJogja1, 'city_id' => $jogjaId,
                'title' => 'Pemasangan Signboard Akrilik Menu Kafe Malioboro', 'amount' => 60000,
                'desc' => 'Pasang 2 papan menu akrilik gantung dengan rantai stainless di atas meja kasir.',
                'loc' => 'Danurejan, Kota Yogyakarta', 'addr' => 'Jl. Mataram No. 42, Danurejan, Yogyakarta',
                'lat' => -7.7942, 'lng' => 110.3689, 'date' => '2026-09-02', 'start' => '14:00', 'end' => '15:30',
                'cust_msg' => 'Mas Danang, tingginya sejajar pandangan mata pengunjung ya.', 'mitra_msg' => 'Siap Mbak Nia, rantai gantungan sudah saya potong presisi.',
                'cust_rev' => 'Papan menu terlihat jelas dan terpasang sangat kokoh.', 'mitra_rev' => 'Desain menu sangat menarik dan pengerjaan lancar.'
            ],

            // --- HARI 31: 03 SEPTEMBER 2026 ---
            [
                'order_id' => 'HELP-20260903-SLM22', 'cust' => $custSleman2, 'mitra' => $mitraSleman1, 'city_id' => $slemanId,
                'title' => 'Pemasangan Penutup Kisi-kisi Saluran Air Anti Tikus', 'amount' => 50000,
                'desc' => 'Pasang kawat ram stainless pada lubang pembuangan air got kamar mandi dan dapur.',
                'loc' => 'Banyuraden, Gamping, Sleman', 'addr' => 'Jl. Ringroad Barat No. 108, Banyuraden, Gamping, Sleman',
                'lat' => -7.7845, 'lng' => 110.3341, 'date' => '2026-09-03', 'start' => '09:00', 'end' => '10:30',
                'cust_msg' => 'Mas Agus, kawat ramnya dipasang kuat pakai semen instan ya.', 'mitra_msg' => 'Beres Bu Siti, lubang tertutup rapat dan air tetap lancar.',
                'cust_rev' => 'Pengerjaan rapi, rumah jadi aman dari tikus got.', 'mitra_rev' => 'Semen instan cepat kering, hasil memuaskan.'
            ],
            [
                'order_id' => 'HELP-20260903-SKT16', 'cust' => $custSolo2, 'mitra' => $mitraSolo1, 'city_id' => $soloId,
                'title' => 'Pemasangan Rak Gantung Handuk & Cermin Rias Solo', 'amount' => 60000,
                'desc' => 'Pasang rak handuk ganda stainless dan cermin rias dinding kamar tidur.',
                'loc' => 'Joyosuran, Pasar Kliwon, Surakarta', 'addr' => 'Jl. Veteran No. 89, Joyosuran, Pasar Kliwon, Surakarta',
                'lat' => -7.5812, 'lng' => 110.8321, 'date' => '2026-09-03', 'start' => '13:30', 'end' => '15:15',
                'cust_msg' => 'Pak Eko, cerminnya dipasang agak tinggi sedikit ya.', 'mitra_msg' => 'Siap Mbak Anisa, saya ukur ketinggian 160 cm standar nyaman.',
                'cust_rev' => 'Cermin dan rak handuk terpasang sempurna dan kuat.', 'mitra_rev' => 'Pekerjaan selesai tepat waktu dan aman.'
            ],
            [
                'order_id' => 'HELP-20260903-SKH16', 'cust' => $custSkh2, 'mitra' => $mitraSkh2, 'city_id' => $sukoharjoId,
                'title' => 'Pengangkutan & Penataan 8 Galon Air Mineral Apotek', 'amount' => 45000,
                'desc' => 'Bantu angkut 8 galon air mineral isi ulang ke rak dispenser lantai 1 dan 2 apotek.',
                'loc' => 'Jombor, Sukoharjo', 'addr' => 'Jl. Jenderal Sudirman No. 80, Jombor, Sukoharjo',
                'lat' => -7.6841, 'lng' => 110.8392, 'date' => '2026-09-03', 'start' => '15:30', 'end' => '16:45',
                'cust_msg' => 'Pak Joko, 4 galon ditaruh di lantai 2 ya.', 'mitra_msg' => 'Siap Pak Bayu, saya bawa satu per satu ke atas.',
                'cust_rev' => 'Pak Joko sangat gesit dan tenaganya luar biasa.', 'mitra_rev' => 'Customer sangat baik dan ramah.'
            ],
        ];

        foreach ($tasksData as $data) {
            $customer = $data['cust'];
            $mitra    = $data['mitra'];

            if (!$customer || !$mitra) {
                continue;
            }

            $dateStr     = $data['date'];
            $createdAt   = Carbon::parse("{$dateStr} {$data['start']}:00")->subMinutes(30);
            $assignedAt  = Carbon::parse("{$dateStr} {$data['start']}:00")->subMinutes(15);
            $startedAt   = Carbon::parse("{$dateStr} {$data['start']}:00");
            $completedAt = Carbon::parse("{$dateStr} {$data['end']}:00");

            $baseAmount   = (float) $data['amount'];
            $adminFee     = $fixedPlatformFee;
            $totalAmount  = $baseAmount + $adminFee;
            $mitraEarning = $baseAmount;

            // 1. Simpan Record Bantuan
            $help = Help::updateOrCreate(
                ['order_id' => $data['order_id']],
                [
                    'user_id'                  => $customer->id,
                    'mitra_id'                 => $mitra->id,
                    'city_id'                  => $data['city_id'],
                    'title'                    => $data['title'],
                    'description'              => $data['desc'],
                    'amount'                   => $baseAmount,
                    'admin_fee'                => $adminFee,
                    'platform_fee_amount'      => $adminFee,
                    'total_amount'             => $totalAmount,
                    'mitra_earning'            => $mitraEarning,
                    'platform_commission_rate' => 0.00,
                    'location'                 => $data['loc'],
                    'full_address'             => $data['addr'],
                    'latitude'                 => $data['lat'],
                    'longitude'                => $data['lng'],
                    'status'                   => Help::STATUS_SELESAI,
                    'escrow_status'            => Help::ESCROW_STATUS_RELEASED,
                    'payment_status'           => Help::PAYMENT_STATUS_PAID,
                    'rating_status'            => Help::RATING_STATUS_RATED,
                    'dispatch_mode'            => Help::DISPATCH_MODE_CLOSED,
                    'equipment_provided'       => 'Peralatan standar telah disediakan',
                    'completion_notes'         => 'Pekerjaan bantuan telah selesai dikerjakan 100% dengan sangat memuaskan.',
                    'taken_at'                 => $assignedAt,
                    'assigned_at'              => $assignedAt,
                    'mitra_assigned_at'        => $assignedAt,
                    'partner_started_at'       => $startedAt,
                    'partner_arrived_at'       => $startedAt->copy()->addMinutes(15),
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

        $this->command->info('HelpsSeeder berhasil membuat riwayat bantuan lengkap untuk seluruh customer & mitra di Sleman, Yogyakarta, Surakarta, dan Sukoharjo.');
    }
}
