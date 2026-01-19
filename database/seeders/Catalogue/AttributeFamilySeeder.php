<?php

namespace Database\Seeders\Catalogue;

use App\Models\Catalogue\AttributeFamily;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AttributeFamilySeeder extends Seeder
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

        $attributeFamilies = [
            [
                'name' => 'Smartphone Attributes',
                'description' => 'Common attributes for smartphones including color, storage, RAM, screen size, camera, and battery specifications.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Laptop Attributes',
                'description' => 'Attributes for laptops including processor, RAM, storage, screen size, graphics, and operating system.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Audio Device Attributes',
                'description' => 'Attributes for audio devices like headphones, earbuds, and speakers including connectivity, driver size, frequency response, and noise cancellation.',
                'sort_order' => 3,
            ],
            [
                'name' => 'Accessory Attributes',
                'description' => 'Common attributes for accessories including compatibility, material, dimensions, and features.',
                'sort_order' => 4,
            ],
            [
                'name' => 'Gaming Device Attributes',
                'description' => 'Attributes specific to gaming devices including refresh rate, response time, RGB lighting, and gaming-specific features.',
                'sort_order' => 5,
            ],
            [
                'name' => 'Tablet Attributes',
                'description' => 'Attributes for tablets including screen size, storage, connectivity, and stylus support.',
                'sort_order' => 6,
            ],
        ];

        foreach ($attributeFamilies as $familyData) {
            AttributeFamily::firstOrCreate(
                ['slug' => Str::slug($familyData['name'])],
                [
                    'name' => $familyData['name'],
                    'description' => $familyData['description'],
                    'creator_id' => $creator->id,
                    'sort_order' => $familyData['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
