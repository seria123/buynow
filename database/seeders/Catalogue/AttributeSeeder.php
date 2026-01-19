<?php

namespace Database\Seeders\Catalogue;

use App\Models\Catalogue\Attribute;
use App\Models\Catalogue\AttributeFamily;
use App\Models\Catalogue\AttributeType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AttributeSeeder extends Seeder
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

        $smartphoneFamily = AttributeFamily::where('slug', 'smartphone-attributes')->first();
        $laptopFamily = AttributeFamily::where('slug', 'laptop-attributes')->first();
        $audioFamily = AttributeFamily::where('slug', 'audio-device-attributes')->first();
        $accessoryFamily = AttributeFamily::where('slug', 'accessory-attributes')->first();
        $gamingFamily = AttributeFamily::where('slug', 'gaming-device-attributes')->first();
        $tabletFamily = AttributeFamily::where('slug', 'tablet-attributes')->first();

        if (! $smartphoneFamily || ! $laptopFamily || ! $audioFamily || ! $accessoryFamily) {
            $this->command->warn('Attribute families not found. Please run AttributeFamilySeeder first.');

            return;
        }

        $attributes = [
            // Smartphone Attributes
            [
                'name' => 'Color',
                'attribute_family_id' => $smartphoneFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Device color options',
                'sort_order' => 1,
            ],
            [
                'name' => 'Storage',
                'attribute_family_id' => $smartphoneFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Internal storage capacity',
                'sort_order' => 2,
            ],
            [
                'name' => 'RAM',
                'attribute_family_id' => $smartphoneFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Random Access Memory',
                'sort_order' => 3,
            ],
            [
                'name' => 'Screen Size',
                'attribute_family_id' => $smartphoneFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Display screen size in inches',
                'sort_order' => 4,
            ],
            [
                'name' => 'Camera',
                'attribute_family_id' => $smartphoneFamily->id,
                'type' => AttributeType::Manual,
                'description' => 'Camera specifications',
                'sort_order' => 5,
            ],
            [
                'name' => 'Battery',
                'attribute_family_id' => $smartphoneFamily->id,
                'type' => AttributeType::Manual,
                'description' => 'Battery capacity and specifications',
                'sort_order' => 6,
            ],
            [
                'name' => 'Operating System',
                'attribute_family_id' => $smartphoneFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Mobile operating system',
                'sort_order' => 7,
            ],

            // Laptop Attributes
            [
                'name' => 'Processor',
                'attribute_family_id' => $laptopFamily->id,
                'type' => AttributeType::Manual,
                'description' => 'CPU processor model and specifications',
                'sort_order' => 1,
            ],
            [
                'name' => 'RAM',
                'attribute_family_id' => $laptopFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Random Access Memory',
                'sort_order' => 2,
            ],
            [
                'name' => 'Storage',
                'attribute_family_id' => $laptopFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Storage capacity and type',
                'sort_order' => 3,
            ],
            [
                'name' => 'Screen Size',
                'attribute_family_id' => $laptopFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Display screen size in inches',
                'sort_order' => 4,
            ],
            [
                'name' => 'Graphics',
                'attribute_family_id' => $laptopFamily->id,
                'type' => AttributeType::Manual,
                'description' => 'Graphics card specifications',
                'sort_order' => 5,
            ],
            [
                'name' => 'Operating System',
                'attribute_family_id' => $laptopFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Pre-installed operating system',
                'sort_order' => 6,
            ],
            [
                'name' => 'Color',
                'attribute_family_id' => $laptopFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Laptop color options',
                'sort_order' => 7,
            ],

            // Audio Device Attributes
            [
                'name' => 'Connectivity',
                'attribute_family_id' => $audioFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Connection type',
                'sort_order' => 1,
            ],
            [
                'name' => 'Driver Size',
                'attribute_family_id' => $audioFamily->id,
                'type' => AttributeType::Manual,
                'description' => 'Driver size in millimeters',
                'sort_order' => 2,
            ],
            [
                'name' => 'Frequency Response',
                'attribute_family_id' => $audioFamily->id,
                'type' => AttributeType::Manual,
                'description' => 'Frequency response range',
                'sort_order' => 3,
            ],
            [
                'name' => 'Noise Cancellation',
                'attribute_family_id' => $audioFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Active noise cancellation support',
                'sort_order' => 4,
            ],
            [
                'name' => 'Battery Life',
                'attribute_family_id' => $audioFamily->id,
                'type' => AttributeType::Manual,
                'description' => 'Battery life in hours',
                'sort_order' => 5,
            ],
            [
                'name' => 'Color',
                'attribute_family_id' => $audioFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Device color options',
                'sort_order' => 6,
            ],

            // Accessory Attributes
            [
                'name' => 'Compatibility',
                'attribute_family_id' => $accessoryFamily->id,
                'type' => AttributeType::Manual,
                'description' => 'Device compatibility',
                'sort_order' => 1,
            ],
            [
                'name' => 'Material',
                'attribute_family_id' => $accessoryFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Material used in construction',
                'sort_order' => 2,
            ],
            [
                'name' => 'Color',
                'attribute_family_id' => $accessoryFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Accessory color options',
                'sort_order' => 3,
            ],
            [
                'name' => 'Dimensions',
                'attribute_family_id' => $accessoryFamily->id,
                'type' => AttributeType::Manual,
                'description' => 'Product dimensions',
                'sort_order' => 4,
            ],

            // Gaming Device Attributes
            [
                'name' => 'Refresh Rate',
                'attribute_family_id' => $gamingFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Display refresh rate in Hz',
                'sort_order' => 1,
            ],
            [
                'name' => 'Response Time',
                'attribute_family_id' => $gamingFamily->id,
                'type' => AttributeType::Manual,
                'description' => 'Response time in milliseconds',
                'sort_order' => 2,
            ],
            [
                'name' => 'RGB Lighting',
                'attribute_family_id' => $gamingFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'RGB lighting support',
                'sort_order' => 3,
            ],
            [
                'name' => 'Color',
                'attribute_family_id' => $gamingFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Device color options',
                'sort_order' => 4,
            ],

            // Tablet Attributes
            [
                'name' => 'Screen Size',
                'attribute_family_id' => $tabletFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Display screen size in inches',
                'sort_order' => 1,
            ],
            [
                'name' => 'Storage',
                'attribute_family_id' => $tabletFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Internal storage capacity',
                'sort_order' => 2,
            ],
            [
                'name' => 'Connectivity',
                'attribute_family_id' => $tabletFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Connection options',
                'sort_order' => 3,
            ],
            [
                'name' => 'Stylus Support',
                'attribute_family_id' => $tabletFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Stylus pen support',
                'sort_order' => 4,
            ],
            [
                'name' => 'Color',
                'attribute_family_id' => $tabletFamily->id,
                'type' => AttributeType::Predefined,
                'description' => 'Tablet color options',
                'sort_order' => 5,
            ],
        ];

        foreach ($attributes as $attributeData) {
            Attribute::firstOrCreate(
                [
                    'slug' => Str::slug($attributeData['name']),
                    'attribute_family_id' => $attributeData['attribute_family_id'],
                ],
                [
                    'name' => $attributeData['name'],
                    'type' => $attributeData['type'],
                    'description' => $attributeData['description'],
                    'sort_order' => $attributeData['sort_order'],
                    'is_active' => true,
                    'creator_id' => $creator->id,
                ]
            );
        }
    }
}
