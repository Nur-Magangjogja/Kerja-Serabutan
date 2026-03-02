<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
                'icon' => '🏠',
                'color' => '#3B82F6',
                'description' => 'Bantuan untuk kebutuhan rumah tangga sehari-hari'
            ],
            [
                'name' => 'Bencana Alam',
                'icon' => '⚠️',
                'color' => '#EF4444',
                'description' => 'Bantuan untuk korban bencana alam'
            ],
            [
                'name' => 'Kesehatan',
                'icon' => '🏥',
                'color' => '#10B981',
                'description' => 'Bantuan untuk kebutuhan kesehatan dan medis'
            ],
            [
                'name' => 'Pendidikan',
                'icon' => '📚',
                'color' => '#F59E0B',
                'description' => 'Bantuan untuk pendidikan dan sekolah'
            ],
            [
                'name' => 'Transportasi',
                'icon' => '🚗',
                'color' => '#8B5CF6',
                'description' => 'Bantuan transportasi dan kendaraan'
            ],
            [
                'name' => 'Sosial',
                'icon' => '🤝',
                'color' => '#EC4899',
                'description' => 'Bantuan sosial dan kemanusiaan'
            ],
            [
                'name' => 'Pangan',
                'icon' => '🍲',
                'color' => '#14B8A6',
                'description' => 'Bantuan makanan dan kebutuhan pangan'
            ],
            [
                'name' => 'Lainnya',
                'icon' => '📦',
                'color' => '#6B7280',
                'description' => 'Bantuan lainnya'
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
