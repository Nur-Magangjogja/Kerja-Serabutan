<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Rumah Tangga',
                'slug' => 'rumah-tangga',
                'icon' => 'home',
                'color' => '#3B82F6',
                'description' => 'Bantuan perbaikan rumah ringan, instalasi alat rumah tangga, dan kebutuhan domestik.',
                'is_active' => true,
            ],
            [
                'name' => 'Kebersihan & Taman',
                'slug' => 'kebersihan-taman',
                'icon' => 'sparkles',
                'color' => '#10B981',
                'description' => 'Pembersihan halaman, potong rumput, kuras tandon air, dan bebenah rumah.',
                'is_active' => true,
            ],
            [
                'name' => 'Angkut & Pindahan Kost',
                'slug' => 'angkut-pindahan',
                'icon' => 'truck',
                'color' => '#F59E0B',
                'description' => 'Bantuan angkat barang, pindahan kamar kost, angkut perabotan, dan bongkar muat.',
                'is_active' => true,
            ],
            [
                'name' => 'Antar Jemput & Kurir',
                'slug' => 'antar-jemput-kurir',
                'icon' => 'motorcycle',
                'color' => '#6366F1',
                'description' => 'Pengantaran dokumen penting, paket lokal kilat, dan bantuan antar barang.',
                'is_active' => true,
            ],
            [
                'name' => 'Pertukangan & Teknisi',
                'slug' => 'pertukangan-teknisi',
                'icon' => 'wrench',
                'color' => '#EC4899',
                'description' => 'Servis pompa air, kelistrikan rumah, perbaikan atap bocor, dan perakitan furnitur.',
                'is_active' => true,
            ],
            [
                'name' => 'Bantuan Belanja & Antre',
                'slug' => 'belanja-antre',
                'icon' => 'shopping-cart',
                'color' => '#8B5CF6',
                'description' => 'Jasa antre loket/tiket, titip beli barang di pasar tradisional, dan belanja kebutuhan harian.',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }

        $this->command->info('CategorySeeder completed successfully (' . count($categories) . ' kategori dibuat).');
    }
}
