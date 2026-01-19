<?php

namespace Database\Seeders\Catalogue;

use App\Models\Catalogue\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');

            return;
        }

        $creator = $users->first();

        // Top-level categories
        $topCategories = [
            ['name' => 'Electronics', 'sort_order' => 1, 'icon' => 'bolt'],
            ['name' => 'Mobile Devices', 'sort_order' => 2, 'icon' => 'device-phone-mobile'],
            ['name' => 'Computers', 'sort_order' => 3, 'icon' => 'computer-desktop'],
            ['name' => 'Audio & Video', 'sort_order' => 4, 'icon' => 'speaker-wave'],
            ['name' => 'Gaming', 'sort_order' => 5, 'icon' => 'cursor-arrow-rays'],
            ['name' => 'Accessories', 'sort_order' => 6, 'icon' => 'wrench-screwdriver'],
        ];

        $createdCategories = [];

        foreach ($topCategories as $categoryData) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryData['name'])],
                [
                    'name' => $categoryData['name'],
                    'parent_id' => null,
                    'creator_id' => $creator->id,
                    'sort_order' => $categoryData['sort_order'],
                    'icon' => $categoryData['icon'] ?? null,
                    'is_active' => true,
                ]
            );
            $createdCategories[$categoryData['name']] = $category;
        }

        // Sub-categories for Electronics
        $electronicsSubCategories = [
            ['name' => 'Smartphones', 'sort_order' => 1, 'icon' => 'device-phone-mobile'],
            ['name' => 'Tablets', 'sort_order' => 2, 'icon' => 'device-tablet'],
            ['name' => 'Smart Watches', 'sort_order' => 3, 'icon' => 'clock'],
            ['name' => 'Wearables', 'sort_order' => 4, 'icon' => 'sparkles'],
        ];

        foreach ($electronicsSubCategories as $subCategory) {
            Category::firstOrCreate(
                ['slug' => Str::slug($subCategory['name'])],
                [
                    'name' => $subCategory['name'],
                    'parent_id' => $createdCategories['Electronics']->id,
                    'creator_id' => $creator->id,
                    'sort_order' => $subCategory['sort_order'],
                    'icon' => $subCategory['icon'] ?? null,
                    'is_active' => true,
                ]
            );
        }

        // Sub-categories for Mobile Devices
        $mobileSubCategories = [
            ['name' => 'Android Phones', 'sort_order' => 1, 'icon' => 'device-phone-mobile'],
            ['name' => 'iOS Phones', 'sort_order' => 2, 'icon' => 'device-phone-mobile'],
            ['name' => 'Feature Phones', 'sort_order' => 3, 'icon' => 'phone'],
        ];

        foreach ($mobileSubCategories as $subCategory) {
            Category::firstOrCreate(
                ['slug' => Str::slug($subCategory['name'])],
                [
                    'name' => $subCategory['name'],
                    'parent_id' => $createdCategories['Mobile Devices']->id,
                    'creator_id' => $creator->id,
                    'sort_order' => $subCategory['sort_order'],
                    'icon' => $subCategory['icon'] ?? null,
                    'is_active' => true,
                ]
            );
        }

        // Sub-categories for Computers
        $computerSubCategories = [
            ['name' => 'Laptops', 'sort_order' => 1, 'icon' => 'computer-desktop'],
            ['name' => 'Desktops', 'sort_order' => 2, 'icon' => 'server-stack'],
            ['name' => 'Monitors', 'sort_order' => 3, 'icon' => 'tv'],
            ['name' => 'Keyboards', 'sort_order' => 4, 'icon' => 'command-line'],
            ['name' => 'Mice', 'sort_order' => 5, 'icon' => 'cursor-arrow-rays'],
            ['name' => 'Webcams', 'sort_order' => 6, 'icon' => 'video-camera'],
        ];

        foreach ($computerSubCategories as $subCategory) {
            Category::firstOrCreate(
                ['slug' => Str::slug($subCategory['name'])],
                [
                    'name' => $subCategory['name'],
                    'parent_id' => $createdCategories['Computers']->id,
                    'creator_id' => $creator->id,
                    'sort_order' => $subCategory['sort_order'],
                    'icon' => $subCategory['icon'] ?? null,
                    'is_active' => true,
                ]
            );
        }

        // Sub-categories for Audio & Video
        $audioVideoSubCategories = [
            ['name' => 'Headphones', 'sort_order' => 1, 'icon' => 'musical-note'],
            ['name' => 'Earbuds', 'sort_order' => 2, 'icon' => 'musical-note'],
            ['name' => 'Speakers', 'sort_order' => 3, 'icon' => 'speaker-wave'],
            ['name' => 'Soundbars', 'sort_order' => 4, 'icon' => 'speaker-wave'],
            ['name' => 'Microphones', 'sort_order' => 5, 'icon' => 'microphone'],
            ['name' => 'TVs', 'sort_order' => 6, 'icon' => 'tv'],
        ];

        foreach ($audioVideoSubCategories as $subCategory) {
            Category::firstOrCreate(
                ['slug' => Str::slug($subCategory['name'])],
                [
                    'name' => $subCategory['name'],
                    'parent_id' => $createdCategories['Audio & Video']->id,
                    'creator_id' => $creator->id,
                    'sort_order' => $subCategory['sort_order'],
                    'icon' => $subCategory['icon'] ?? null,
                    'is_active' => true,
                ]
            );
        }

        // Sub-categories for Gaming
        $gamingSubCategories = [
            ['name' => 'Gaming Consoles', 'sort_order' => 1, 'icon' => 'cpu-chip'],
            ['name' => 'Gaming Laptops', 'sort_order' => 2, 'icon' => 'computer-desktop'],
            ['name' => 'Gaming Accessories', 'sort_order' => 3, 'icon' => 'puzzle-piece'],
            ['name' => 'Gaming Headsets', 'sort_order' => 4, 'icon' => 'musical-note'],
        ];

        foreach ($gamingSubCategories as $subCategory) {
            Category::firstOrCreate(
                ['slug' => Str::slug($subCategory['name'])],
                [
                    'name' => $subCategory['name'],
                    'parent_id' => $createdCategories['Gaming']->id,
                    'creator_id' => $creator->id,
                    'sort_order' => $subCategory['sort_order'],
                    'icon' => $subCategory['icon'] ?? null,
                    'is_active' => true,
                ]
            );
        }

        // Sub-categories for Accessories
        $accessoriesSubCategories = [
            ['name' => 'Phone Cases', 'sort_order' => 1, 'icon' => 'rectangle-group'],
            ['name' => 'Screen Protectors', 'sort_order' => 2, 'icon' => 'shield-check'],
            ['name' => 'Chargers', 'sort_order' => 3, 'icon' => 'bolt'],
            ['name' => 'Cables', 'sort_order' => 4, 'icon' => 'link'],
            ['name' => 'Power Banks', 'sort_order' => 5, 'icon' => 'battery-100'],
            ['name' => 'Stands & Mounts', 'sort_order' => 6, 'icon' => 'presentation-chart-bar'],
            ['name' => 'Memory Cards', 'sort_order' => 7, 'icon' => 'rectangle-stack'],
            ['name' => 'USB Drives', 'sort_order' => 8, 'icon' => 'arrow-down-tray'],
        ];

        foreach ($accessoriesSubCategories as $subCategory) {
            Category::firstOrCreate(
                ['slug' => Str::slug($subCategory['name'])],
                [
                    'name' => $subCategory['name'],
                    'parent_id' => $createdCategories['Accessories']->id,
                    'creator_id' => $creator->id,
                    'sort_order' => $subCategory['sort_order'],
                    'icon' => $subCategory['icon'] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
