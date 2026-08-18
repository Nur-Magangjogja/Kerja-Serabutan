<?php

namespace Database\Seeders;

use App\Models\Help;
use App\Models\Chat;
use App\Models\PartnerActivity;
use App\Models\Rating;
use App\Models\User;
use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Faker\Factory as Faker;

class HelpSeeder extends Seeder
{
    /**
     * Helper to create a dummy image on public disk if GD is available.
     */
    protected function createDummyImage(string $relativeDir, string $filename, string $title, string $tag, int $bgR, int $bgG, int $bgB): string
    {
        $fullDir = storage_path('app/public/' . $relativeDir);
        if (!file_exists($fullDir)) {
            mkdir($fullDir, 0755, true);
        }

        $filePath = $fullDir . '/' . $filename;
        $relativePath = $relativeDir . '/' . $filename;

        // If file already exists, return relative path
        if (file_exists($filePath) && filesize($filePath) > 0) {
            return $relativePath;
        }

        if (extension_loaded('gd')) {
            $img = imagecreatetruecolor(700, 480);
            $bg = imagecolorallocate($img, $bgR, $bgG, $bgB);
            $white = imagecolorallocate($img, 255, 255, 255);
            $dark = imagecolorallocate($img, 30, 41, 59);
            $accent = imagecolorallocate($img, 245, 158, 11);
            $subColor = imagecolorallocate($img, 226, 232, 240);

            // Background
            imagefilledrectangle($img, 0, 0, 700, 480, $bg);

            // Card panel inside
            imagefilledrectangle($img, 40, 40, 660, 440, imagecolorallocate($img, 15, 23, 42));

            // Badge
            imagefilledrectangle($img, 60, 60, 260, 95, $accent);
            imagestring($img, 5, 75, 70, strtoupper($tag), $dark);

            // Title
            imagestring($img, 5, 60, 130, substr($title, 0, 45), $white);
            if (strlen($title) > 45) {
                imagestring($img, 5, 60, 160, substr($title, 45, 45), $white);
            }

            // Description / watermark
            imagestring($img, 4, 60, 220, "SAYABANTU - PLATFORM BANTUAN SERABUTAN", $subColor);
            imagestring($img, 3, 60, 260, "Status Dokumentasi: " . $tag, $subColor);
            imagestring($img, 3, 60, 290, "Waktu Rekam: " . date('d M Y, H:i:s') . " WIB", $subColor);
            imagestring($img, 3, 60, 320, "Verifikasi Sistem: ASLI / VALIDATED", imagecolorallocate($img, 52, 211, 153));

            // Footer bar
            imagefilledrectangle($img, 40, 400, 660, 440, imagecolorallocate($img, 30, 41, 59));
            imagestring($img, 3, 60, 412, "SayaBantu Digital Service Proof & Activity Record", $white);

            imagejpeg($img, $filePath, 85);
            imagedestroy($img);
        } else {
            // Fallback: simple text dummy file
            file_put_contents($filePath, "DUMMY_IMAGE_{$title}");
        }

        return $relativePath;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Retrieve customers and mitras
        $customers = User::where('role', 'customer')->get();
        $mitras = User::where('role', 'mitra')->get();
        $cities = City::pluck('id')->toArray();
        if (empty($cities)) {
            $cities = [1];
        }

        if ($customers->isEmpty()) {
            $customer = User::create([
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'password' => bcrypt('password'),
                'role' => 'customer',
                'city_id' => 1,
                'verified' => true,
                'status' => 'active',
            ]);
            $customers = collect([$customer]);
        }

        if ($mitras->isEmpty()) {
            $mitra = User::create([
                'name' => 'Ahmad Relawan',
                'email' => 'ahmad@example.com',
                'password' => bcrypt('password'),
                'role' => 'mitra',
                'city_id' => 1,
                'verified' => true,
                'status' => 'active',
            ]);
            $mitras = collect([$mitra]);
        }

        $sampleTasks = [
            [
                'title' => 'Bantu beli & pasang tabung gas LPG 3kg',
                'desc' => 'Tolong belikan gas elpiji 3kg di pangkalan terdekat dan bantu pasangkan regulator di dapur.',
                'amount' => 25000,
                'status' => 'selesai',
                'photo_slug' => 'foto_gas_awal.jpg',
                'proof_slug' => 'bukti_gas_selesai.jpg',
                'proof_note' => 'Tabung gas LPG 3kg sudah dibelikan dan dipasang dengan regulator baru. Tidak ada kebocoran.',
                'tag_awal' => 'FOTO PERMOHONAN',
                'tag_bukti' => 'BUKTI SELESAI',
            ],
            [
                'title' => 'Tolong angkat 2 galon air ke lantai 2',
                'desc' => 'Butuh bantuan tenaga untuk mengangkat 2 galon air mineral dari teras depan ke dispenser lantai 2.',
                'amount' => 20000,
                'status' => 'selesai',
                'photo_slug' => 'foto_galon_awal.jpg',
                'proof_slug' => 'bukti_galon_selesai.jpg',
                'proof_note' => '2 Galon air mineral sudah berhasil dipindahkan dan dipasang di dispenser lantai 2.',
                'tag_awal' => 'FOTO PERMOHONAN',
                'tag_bukti' => 'BUKTI SELESAI',
            ],
            [
                'title' => 'Bantu perbaiki keran air yang bocor',
                'desc' => 'Keran wastafel di dapur menetes terus, butuh mitra yang bisa bantu ganti kran baru atau isolasi seal.',
                'amount' => 35000,
                'status' => 'selesai',
                'photo_slug' => 'foto_kran_awal.jpg',
                'proof_slug' => 'bukti_kran_selesai.jpg',
                'proof_note' => 'Seal pipa kran wastafel sudah diganti dengan seal tape baru. Aliran air lancar dan rapat.',
                'tag_awal' => 'FOTO PERMOHONAN',
                'tag_bukti' => 'BUKTI SELESAI',
            ],
            [
                'title' => 'Jasa potong rumput halaman depan rumah',
                'desc' => 'Rumput di halaman depan sudah agak tinggi. Butuh bantuan untuk merapikan dan membuang sampahnya.',
                'amount' => 50000,
                'status' => 'waiting_customer_confirmation',
                'photo_slug' => 'foto_rumput_awal.jpg',
                'proof_slug' => 'bukti_rumput_selesai.jpg',
                'proof_note' => 'Halaman depan sudah dipotong rapi dan seluruh potongan rumput sudah dimasukkan ke karung sampah.',
                'tag_awal' => 'FOTO PERMOHONAN',
                'tag_bukti' => 'BUKTI SELESAI',
            ],
            [
                'title' => 'Bantu pasang lampu teras plafon tinggi',
                'desc' => 'Lampu LED di teras rumah mati. Plafon agak tinggi butuh bantuan tangga dan pemasangan lampu baru.',
                'amount' => 25000,
                'status' => 'waiting_customer_confirmation',
                'photo_slug' => 'foto_lampu_awal.jpg',
                'proof_slug' => 'bukti_lampu_selesai.jpg',
                'proof_note' => 'Lampu LED Philips 12W sudah terpasang di plafon dan menyala dengan terang.',
                'tag_awal' => 'FOTO PERMOHONAN',
                'tag_bukti' => 'BUKTI SELESAI',
            ],
            [
                'title' => 'Bantu bersihkan AC kamar netes air',
                'desc' => 'AC kamar tidur meneteskan air dari talang indoor, butuh mitra yang berpengalaman membersihkan selang pembuangan.',
                'amount' => 60000,
                'status' => 'in_progress',
                'photo_slug' => 'foto_ac_awal.jpg',
                'proof_slug' => null,
                'proof_note' => null,
                'tag_awal' => 'FOTO PERMOHONAN',
                'tag_bukti' => null,
            ],
            [
                'title' => 'Bantu antar berkas dokumen penting ke kantor',
                'desc' => 'Tolong bantu antarkan map dokumen penting ke kantor cabang sebelum jam 3 sore.',
                'amount' => 30000,
                'status' => 'partner_on_the_way',
                'photo_slug' => 'foto_dokumen_awal.jpg',
                'proof_slug' => null,
                'proof_note' => null,
                'tag_awal' => 'FOTO PERMOHONAN',
                'tag_bukti' => null,
            ],
            [
                'title' => 'Pindahan meja belajar & lemari kecil ke kamar sebelah',
                'desc' => 'Butuh bantuan 1 orang untuk memindahkan meja belajar dan lemari susun ke kamar sebelah.',
                'amount' => 45000,
                'status' => 'menunggu_mitra',
                'photo_slug' => 'foto_meja_awal.jpg',
                'proof_slug' => null,
                'proof_note' => null,
                'tag_awal' => 'FOTO PERMOHONAN',
                'tag_bukti' => null,
            ],
            [
                'title' => 'Bantu tambal ban sepeda anak & pompa',
                'desc' => 'Sepeda anak kempes dan bocor halus, tolong dibantu tambal atau ganti ban dalam.',
                'amount' => 20000,
                'status' => 'menunggu_mitra',
                'photo_slug' => 'foto_sepeda_awal.jpg',
                'proof_slug' => null,
                'proof_note' => null,
                'tag_awal' => 'FOTO PERMOHONAN',
                'tag_bukti' => null,
            ],
            [
                'title' => 'Tolong belikan obat & vitamin di apotek',
                'desc' => 'Sedang demam butuh bantuan titip beli obat penurun panas dan vitamin C di apotek 24 jam terdekat.',
                'amount' => 25000,
                'status' => 'menunggu_mitra',
                'photo_slug' => 'foto_obat_awal.jpg',
                'proof_slug' => null,
                'proof_note' => null,
                'tag_awal' => 'FOTO PERMOHONAN',
                'tag_bukti' => null,
            ],
        ];

        $hasCategory = Schema::hasColumn('helps', 'category_id');
        $hasCoords = Schema::hasColumn('helps', 'latitude');
        $categoryIds = \DB::table('categories')->pluck('id')->toArray();

        foreach ($sampleTasks as $index => $task) {
            $customer = $customers[$index % $customers->count()];
            $cityId = $cities[$index % count($cities)];

            // Coordinates near Ponorogo / Jakarta center
            $baseLat = -7.8667 + ($faker->randomFloat(4, -0.03, 0.03));
            $baseLng = 111.4667 + ($faker->randomFloat(4, -0.03, 0.03));

            // Generate initial photo
            $initialPhotoPath = $this->createDummyImage(
                'helps',
                $task['photo_slug'],
                $task['title'],
                'FOTO PERMOHONAN AWAL',
                30, 58, 138 // Deep Blue
            );

            // Generate proof photo if needed
            $proofPhotoPath = null;
            if ($task['proof_slug']) {
                $proofPhotoPath = $this->createDummyImage(
                    'helps/proofs',
                    $task['proof_slug'],
                    $task['title'],
                    'BUKTI SELESAI PEKERJAAN',
                    5, 150, 105 // Emerald Green
                );
            }

            // Assign mitra if status is not menunggu_mitra
            $mitra = null;
            if ($task['status'] !== 'menunggu_mitra') {
                $mitra = $mitras[$index % $mitras->count()];
            }

            $data = [
                'user_id' => $customer->id,
                'mitra_id' => $mitra?->id,
                'city_id' => $cityId,
                'title' => $task['title'],
                'description' => $task['desc'],
                'location' => $faker->streetAddress() . ', Ponorogo',
                'amount' => $task['amount'],
                'photo' => $initialPhotoPath,
                'proof_photo' => $proofPhotoPath,
                'completion_notes' => $task['proof_note'],
                'status' => $task['status'],
                'taken_at' => $mitra ? now()->subHours(rand(3, 8)) : null,
                'service_started_at' => in_array($task['status'], ['in_progress', 'waiting_customer_confirmation', 'selesai', 'completed']) ? now()->subHours(rand(1, 3)) : null,
                'service_completed_at' => in_array($task['status'], ['waiting_customer_confirmation', 'selesai', 'completed']) ? now()->subMinutes(rand(10, 50)) : null,
                'completed_at' => in_array($task['status'], ['selesai', 'completed']) ? now()->subMinutes(rand(5, 30)) : null,
            ];

            if ($hasCategory && !empty($categoryIds)) {
                $data['category_id'] = $faker->randomElement($categoryIds);
            }

            if ($hasCoords) {
                $data['latitude'] = $baseLat;
                $data['longitude'] = $baseLng;
            }

            $help = Help::create($data);

            // ==============================================
            // Create Audit PartnerActivities from Start to End
            // ==============================================
            PartnerActivity::create([
                'user_id' => $customer->id,
                'help_id' => $help->id,
                'activity_type' => 'help_created',
                'description' => "Customer {$customer->name} membuat permohonan bantuan #{$help->id} ('{$help->title}')",
                'photo' => $initialPhotoPath,
                'created_at' => now()->subHours(rand(4, 9)),
            ]);

            if ($mitra) {
                PartnerActivity::create([
                    'user_id' => $mitra->id,
                    'help_id' => $help->id,
                    'activity_type' => 'take_help',
                    'description' => "Mitra {$mitra->name} mengambil bantuan #{$help->id} ('{$help->title}')",
                    'created_at' => now()->subHours(rand(3, 8)),
                ]);

                // Welcome chat message
                Chat::create([
                    'help_id' => $help->id,
                    'mitra_id' => $mitra->id,
                    'customer_id' => $customer->id,
                    'message' => "Halo Kak {$customer->name}, perkenalkan saya {$mitra->name}. Saya telah mengambil permohonan bantuan Anda '{$help->title}'. Saya akan segera menuju lokasi Anda!",
                    'sender_type' => 'mitra',
                    'created_at' => now()->subHours(rand(3, 8)),
                ]);

                if (in_array($task['status'], ['in_progress', 'waiting_customer_confirmation', 'selesai', 'completed'])) {
                    PartnerActivity::create([
                        'user_id' => $mitra->id,
                        'help_id' => $help->id,
                        'activity_type' => 'help_started',
                        'description' => "Mitra {$mitra->name} mulai mengerjakan bantuan #{$help->id}",
                        'created_at' => now()->subHours(rand(1, 2)),
                    ]);
                }

                if (in_array($task['status'], ['waiting_customer_confirmation', 'selesai', 'completed']) && $proofPhotoPath) {
                    PartnerActivity::create([
                        'user_id' => $mitra->id,
                        'help_id' => $help->id,
                        'activity_type' => 'help_completed',
                        'description' => "Mitra {$mitra->name} menyelesaikan bantuan #{$help->id} dan mengunggah foto bukti",
                        'photo' => $proofPhotoPath,
                        'created_at' => now()->subMinutes(rand(20, 50)),
                    ]);

                    // Chat message with attached proof photo
                    Chat::create([
                        'help_id' => $help->id,
                        'mitra_id' => $mitra->id,
                        'customer_id' => $customer->id,
                        'message' => "Halo Kak {$customer->name}, pekerjaan '{$help->title}' telah selesai dikerjakan. Catatan: \"{$task['proof_note']}\". Berikut foto bukti hasil pengerjaan. Mohon dicek dan dikonfirmasi ya!",
                        'photo' => $proofPhotoPath,
                        'sender_type' => 'mitra',
                        'created_at' => now()->subMinutes(rand(20, 50)),
                    ]);
                }

                if (in_array($task['status'], ['selesai', 'completed'])) {
                    PartnerActivity::create([
                        'user_id' => $customer->id,
                        'help_id' => $help->id,
                        'activity_type' => 'help_confirmed',
                        'description' => "Customer {$customer->name} mengonfirmasi bantuan #{$help->id} telah selesai",
                        'photo' => $proofPhotoPath,
                        'created_at' => now()->subMinutes(rand(5, 20)),
                    ]);

                    // Rating & review
                    Rating::create([
                        'help_id' => $help->id,
                        'user_id' => $customer->id,
                        'mitra_id' => $mitra->id,
                        'rater_id' => $customer->id,
                        'rating' => 5,
                        'review' => 'Pekerjaan sangat rapi, cepat, dan mitra sangat ramah. Rekomendasi sekali!',
                        'created_at' => now()->subMinutes(rand(1, 10)),
                    ]);

                    PartnerActivity::create([
                        'user_id' => $customer->id,
                        'help_id' => $help->id,
                        'activity_type' => 'help_reviewed',
                        'description' => "Customer {$customer->name} memberikan rating 5 bintang untuk bantuan #{$help->id}",
                        'created_at' => now()->subMinutes(rand(1, 10)),
                    ]);
                }
            }
        }
    }
}


