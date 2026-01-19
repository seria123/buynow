<?php

namespace Database\Seeders\Catalogue;

use App\Models\Catalogue\Attribute;
use App\Models\Catalogue\AttributeType;
use App\Models\Catalogue\AttributeValue;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AttributeValueSeeder extends Seeder
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

        // Get all predefined attributes
        $predefinedAttributes = Attribute::where('type', AttributeType::Predefined)->get();

        if ($predefinedAttributes->isEmpty()) {
            $this->command->warn('No predefined attributes found. Please run AttributeSeeder first.');

            return;
        }

        // Map attribute names to their values
        $attributeValues = [
            'Color' => [
                'Black', 'White', 'Blue', 'Red', 'Silver', 'Gold', 'Space Gray', 'Midnight', 'Starlight', 'Graphite',
            ],
            'Storage' => [
                '64GB', '128GB', '256GB', '512GB', '1TB', '2TB',
            ],
            'RAM' => [
                '4GB', '6GB', '8GB', '12GB', '16GB', '32GB', '64GB',
            ],
            'Screen Size' => [
                '5.5"', '6.1"', '6.7"', '13"', '14"', '15.6"', '16"', '17"', '10.2"', '11"', '12.9"',
            ],
            'Operating System' => [
                'iOS', 'Android', 'Windows 11', 'Windows 10', 'macOS', 'Chrome OS', 'Linux',
            ],
            'Connectivity' => [
                'Wired', 'Wireless (Bluetooth)', 'USB-C', '3.5mm Jack', 'Wireless + Wired', 'USB-A',
            ],
            'Noise Cancellation' => [
                'Yes', 'No', 'Active Noise Cancellation', 'Passive Noise Cancellation',
            ],
            'Material' => [
                'Silicone', 'Leather', 'Plastic', 'Metal', 'Fabric', 'Carbon Fiber', 'Glass',
            ],
            'Refresh Rate' => [
                '60Hz', '120Hz', '144Hz', '165Hz', '240Hz', '360Hz',
            ],
            'RGB Lighting' => [
                'Yes', 'No', 'Per-key RGB', 'Zone RGB', 'Single Color',
            ],
            'Stylus Support' => [
                'Yes', 'No', 'Apple Pencil Compatible', 'S Pen Compatible',
            ],
        ];

        foreach ($predefinedAttributes as $attribute) {
            $values = $attributeValues[$attribute->name] ?? null;

            if ($values) {
                $sortOrder = 1;
                foreach ($values as $value) {
                    AttributeValue::firstOrCreate(
                        [
                            'attribute_id' => $attribute->id,
                            'slug' => Str::slug($value),
                        ],
                        [
                            'value' => $value,
                            'sort_order' => $sortOrder++,
                            'is_active' => true,
                            'creator_id' => $creator->id,
                        ]
                    );
                }
            }
        }
    }
}
