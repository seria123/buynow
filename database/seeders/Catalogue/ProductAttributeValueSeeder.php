<?php

namespace Database\Seeders\Catalogue;

use App\Models\Catalogue\Attribute;
use App\Models\Catalogue\AttributeType;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductAttributeValue;
use Illuminate\Database\Seeder;

class ProductAttributeValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::with('attributeFamily.attributes.attributeValues')->get();

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Please run ProductSeeder first.');

            return;
        }

        // Sample manual values for different attribute types
        $manualValues = [
            'Camera' => [
                '48MP Main, 12MP Ultra Wide, 12MP Telephoto',
                '200MP Main, 12MP Ultra Wide, 10MP Telephoto, 50MP Periscope',
                '50MP Main, 12MP Ultra Wide, 48MP Telephoto',
                '50MP Main, 48MP Ultra Wide, 64MP Telephoto',
            ],
            'Battery' => [
                '3274 mAh with 20W fast charging',
                '5000 mAh with 45W fast charging',
                '5050 mAh with 100W SuperVOOC',
                '4000 mAh with 30W fast charging',
            ],
            'Processor' => [
                'Apple M3 Pro (12-core CPU, 18-core GPU)',
                'Intel Core i7-13700H (14 cores)',
                'Intel Core i7-1355U (10 cores)',
                'Intel Core i7-12800H (14 cores)',
            ],
            'Graphics' => [
                'NVIDIA GeForce RTX 4060 (8GB)',
                'Intel Iris Xe Graphics',
                'NVIDIA GeForce RTX 4070 (12GB)',
                'Apple M3 Pro 18-core GPU',
            ],
            'Driver Size' => [
                '40mm dynamic drivers',
                '30mm custom drivers',
                '10mm dynamic drivers',
                '50mm full-range drivers',
            ],
            'Frequency Response' => [
                '20Hz - 20kHz',
                '10Hz - 40kHz',
                '20Hz - 22kHz',
                '15Hz - 25kHz',
            ],
            'Battery Life' => [
                'Up to 30 hours with ANC off',
                'Up to 6 hours with ANC on',
                'Up to 20 hours total with case',
                'Up to 12 hours continuous playback',
            ],
            'Compatibility' => [
                'iPhone 12 and later, AirPods Pro',
                'Samsung Galaxy S21 and later',
                'All USB-C devices',
                'iPhone 8 and later, iPad, AirPods',
            ],
            'Dimensions' => [
                '150 x 75 x 8mm',
                '200 x 100 x 15mm',
                '100 x 50 x 10mm',
                '180 x 90 x 12mm',
            ],
            'Response Time' => [
                '1ms (GTG)',
                '2ms (GTG)',
                '0.5ms (GTG)',
                '3ms (GTG)',
            ],
        ];

        foreach ($products as $product) {
            $attributeFamily = $product->attributeFamily;

            if (! $attributeFamily) {
                continue;
            }

            $attributes = $attributeFamily->attributes()->where('is_active', true)->get();

            foreach ($attributes as $attribute) {
                // Skip if already has a value
                $existing = ProductAttributeValue::where('product_id', $product->id)
                    ->where('attribute_id', $attribute->id)
                    ->first();

                if ($existing) {
                    continue;
                }

                $value = null;

                if ($attribute->type === AttributeType::Predefined) {
                    // Get a random attribute value for predefined attributes
                    $attributeValue = $attribute->attributeValues()
                        ->where('is_active', true)
                        ->inRandomOrder()
                        ->first();

                    if ($attributeValue) {
                        $value = $attributeValue->value;
                    }
                } else {
                    // Use manual value based on attribute name
                    $manualValueOptions = $manualValues[$attribute->name] ?? null;

                    if ($manualValueOptions) {
                        $value = $manualValueOptions[array_rand($manualValueOptions)];
                    } else {
                        // Fallback: generate a generic value
                        $value = 'Custom '.$attribute->name.' specification';
                    }
                }

                if ($value) {
                    ProductAttributeValue::create([
                        'product_id' => $product->id,
                        'attribute_id' => $attribute->id,
                        'value' => $value,
                    ]);
                }
            }
        }
    }
}
