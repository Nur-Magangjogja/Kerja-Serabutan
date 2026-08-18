<?php

namespace Database\Seeders;

use App\Models\Help;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\City;
use Illuminate\Support\Facades\Schema;
use Faker\Factory as Faker;

class HelpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Only query users with customer role
        $customerIds = User::where('role', 'customer')->pluck('id')->toArray();
        $cityIds = City::pluck('id')->toArray();

        if (empty($customerIds)) {
            $customer = User::firstOrCreate(
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
            $customerIds = [$customer->id];
        }

        if (empty($cityIds)) {
            $cityIds = [1];
        }

        $sampleTasks = [
            ['title' => 'Bantu beli & pasang tabung gas LPG 3kg', 'category' => 'Rumah Tangga', 'amount' => 25000, 'desc' => 'Tolong belikan gas elpiji 3kg di pangkalan terdekat dan bantu pasangkan regulator di dapur.'],
            ['title' => 'Tolong angkat 2 galon air ke lantai 2', 'category' => 'Tenaga & Angkut', 'amount' => 20000, 'desc' => 'Butuh bantuan tenaga untuk mengangkat 2 galon air mineral dari teras depan ke dispenser lantai 2.'],
            ['title' => 'Bantu perbaiki keran air yang bocor', 'category' => 'Pertukangan', 'amount' => 35000, 'desc' => 'Keran wastafel di dapur menetes terus, butuh mitra yang bisa bantu ganti kran baru atau isolasi seal.'],
            ['title' => 'Jasa potong rumput halaman depan rumah', 'category' => 'Kebersihan', 'amount' => 50000, 'desc' => 'Rumput di halaman depan sudah agak tinggi. Butuh bantuan untuk merapikan dan membuang sampahnya.'],
            ['title' => 'Bantu antar berkas dokumen penting', 'category' => 'Pengantaran', 'amount' => 30000, 'desc' => 'Tolong bantu antarkan map dokumen penting ke kantor cabang sebelum jam 3 sore.'],
            ['title' => 'Bantu pasang lampu teras plafon tinggi', 'category' => 'Listrik', 'amount' => 25000, 'desc' => 'Lampu LED di teras rumah mati. Plafon agak tinggi butuh bantuan tangga dan pemasangan lampu baru.'],
            ['title' => 'Pindahan meja belajar & lemari kecil', 'category' => 'Tenaga & Angkut', 'amount' => 45000, 'desc' => 'Butuh bantuan 1 orang untuk memindahkan meja belajar dan lemari susun ke kamar sebelah.'],
            ['title' => 'Bantu bersihkan AC kamar netes air', 'category' => 'Elektronik', 'amount' => 60000, 'desc' => 'AC kamar tidur meneteskan air dari talang indoor, butuh mitra yang berpengalaman membersihkan selang pembuangan.'],
            ['title' => 'Bantu tambal ban sepeda anak & pompa', 'category' => 'Otomotif & Servis', 'amount' => 20000, 'desc' => 'Sepeda anak kempes dan bocor halus, tolong dibantu tambal atau ganti ban dalam.'],
            ['title' => 'Tolong belikan obat & vitamin di apotek', 'category' => 'Belanja & Titip', 'amount' => 25000, 'desc' => 'Sedang demam butuh bantuan titip beli obat penurun panas dan vitamin C di apotek 24 jam terdekat.'],
        ];

        $hasCategory = Schema::hasColumn('helps', 'category_id');
        $hasCoords = Schema::hasColumn('helps', 'latitude');
        $categoryIds = \DB::table('categories')->pluck('id')->toArray();

        foreach ($sampleTasks as $index => $task) {
            $assignedCustomerId = $customerIds[$index % count($customerIds)];
            $assignedCityId = $cityIds[$index % count($cityIds)];

            // Coordinates near Ponorogo / Jakarta center
            $baseLat = -7.8667 + ($faker->randomFloat(4, -0.04, 0.04));
            $baseLng = 111.4667 + ($faker->randomFloat(4, -0.04, 0.04));

            $data = [
                'user_id' => $assignedCustomerId,
                'city_id' => $assignedCityId,
                'title' => $task['title'],
                'description' => $task['desc'],
                'location' => $faker->streetAddress() . ', ' . $faker->city(),
                'amount' => $task['amount'],
                'photo' => null,
                'status' => 'menunggu_mitra',
            ];

            if ($hasCategory && !empty($categoryIds)) {
                $data['category_id'] = $faker->randomElement($categoryIds);
            }

            if ($hasCoords) {
                $data['latitude'] = $baseLat;
                $data['longitude'] = $baseLng;
            }

            Help::create($data);
        }
    }
}

