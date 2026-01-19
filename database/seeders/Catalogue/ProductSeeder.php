<?php

namespace Database\Seeders\Catalogue;

use App\Enums\ProductStatus;
use App\Models\Catalogue\Attribute;
use App\Models\Catalogue\AttributeFamily;
use App\Models\Catalogue\AttributeValue;
use App\Models\Catalogue\Brand;
use App\Models\Catalogue\Category;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductVariant;
use App\Models\Catalogue\ProductVariantOption;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Cache for attributes and values to avoid repeated queries.
     */
    private $attributesCache = [];

    private $attributeValuesCache = [];

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
        $reviewer = $users->skip(1)->first() ?? $users->first();

        $brands = Brand::all();
        $categories = Category::all();
        $attributeFamilies = AttributeFamily::all();

        if ($brands->isEmpty() || $categories->isEmpty() || $attributeFamilies->isEmpty()) {
            $this->command->warn('Brands, Categories, or Attribute Families not found. Please run their seeders first.');

            return;
        }

        $products = [
            // Smartphones
            [
                'name' => 'iPhone 15 Pro',
                'brand' => 'Apple',
                'category' => 'Smartphones',
                'attribute_family' => 'Smartphone Attributes',
                'short_description' => 'The most advanced iPhone yet with A17 Pro chip and titanium design.',
                'description' => 'The iPhone 15 Pro features a 6.1-inch Super Retina XDR display, A17 Pro chip with 6-core GPU, Pro camera system with 48MP main camera, and all-day battery life. Available in Natural Titanium, Blue Titanium, White Titanium, and Black Titanium.',
                'price' => 999.00,
                'compare_price' => 1099.00,
                'cost' => 750.00,
                'quantity' => 50,
                'low_stock_threshold' => 10,
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra',
                'brand' => 'Samsung',
                'category' => 'Smartphones',
                'attribute_family' => 'Smartphone Attributes',
                'short_description' => 'Flagship Android smartphone with S Pen and 200MP camera.',
                'description' => 'The Galaxy S24 Ultra features a 6.8-inch Dynamic AMOLED 2X display, Snapdragon 8 Gen 3 processor, 200MP main camera with 100x Space Zoom, and S Pen support. Available in Titanium Black, Titanium Gray, Titanium Violet, and Titanium Yellow.',
                'price' => 1199.00,
                'compare_price' => 1299.00,
                'cost' => 900.00,
                'quantity' => 35,
                'low_stock_threshold' => 8,
            ],
            [
                'name' => 'Google Pixel 8 Pro',
                'brand' => 'Google',
                'category' => 'Smartphones',
                'attribute_family' => 'Smartphone Attributes',
                'short_description' => 'AI-powered smartphone with exceptional camera capabilities.',
                'description' => 'The Pixel 8 Pro features a 6.7-inch LTPO OLED display, Google Tensor G3 chip, 50MP main camera with Super Res Zoom, and Magic Eraser. Available in Obsidian, Porcelain, and Bay.',
                'price' => 899.00,
                'compare_price' => 999.00,
                'cost' => 650.00,
                'quantity' => 40,
                'low_stock_threshold' => 10,
            ],
            [
                'name' => 'OnePlus 12',
                'brand' => 'OnePlus',
                'category' => 'Smartphones',
                'attribute_family' => 'Smartphone Attributes',
                'short_description' => 'Flagship killer with Snapdragon 8 Gen 3 and fast charging.',
                'description' => 'The OnePlus 12 features a 6.82-inch LTPO AMOLED display, Snapdragon 8 Gen 3 processor, 50MP triple camera system, and 100W SuperVOOC charging. Available in Silky Black and Flowy Emerald.',
                'price' => 799.00,
                'compare_price' => 899.00,
                'cost' => 600.00,
                'quantity' => 30,
                'low_stock_threshold' => 8,
            ],

            // Laptops
            [
                'name' => 'MacBook Pro 16-inch',
                'brand' => 'Apple',
                'category' => 'Laptops',
                'attribute_family' => 'Laptop Attributes',
                'short_description' => 'Professional laptop with M3 Pro chip and Liquid Retina XDR display.',
                'description' => 'The MacBook Pro 16-inch features an M3 Pro chip with up to 12-core CPU and 18-core GPU, 16.2-inch Liquid Retina XDR display, up to 36GB unified memory, and up to 4TB SSD storage. Perfect for professionals and creatives.',
                'price' => 2499.00,
                'compare_price' => 2799.00,
                'cost' => 2000.00,
                'quantity' => 25,
                'low_stock_threshold' => 5,
            ],
            [
                'name' => 'Dell XPS 15',
                'brand' => 'Dell',
                'category' => 'Laptops',
                'attribute_family' => 'Laptop Attributes',
                'short_description' => 'Premium Windows laptop with OLED display and powerful performance.',
                'description' => 'The Dell XPS 15 features a 15.6-inch OLED display, Intel Core i7 processor, NVIDIA GeForce RTX graphics, up to 64GB RAM, and up to 2TB SSD. Available in Platinum Silver and Frost.',
                'price' => 1899.00,
                'compare_price' => 2199.00,
                'cost' => 1500.00,
                'quantity' => 20,
                'low_stock_threshold' => 5,
            ],
            [
                'name' => 'HP Spectre x360',
                'brand' => 'HP',
                'category' => 'Laptops',
                'attribute_family' => 'Laptop Attributes',
                'short_description' => 'Convertible 2-in-1 laptop with premium design and performance.',
                'description' => 'The HP Spectre x360 features a 13.5-inch OLED touchscreen, Intel Core i7 processor, Intel Iris Xe graphics, up to 32GB RAM, and up to 2TB SSD. Convertible design for versatility.',
                'price' => 1499.00,
                'compare_price' => 1699.00,
                'cost' => 1200.00,
                'quantity' => 18,
                'low_stock_threshold' => 5,
            ],
            [
                'name' => 'Lenovo ThinkPad X1 Carbon',
                'brand' => 'Lenovo',
                'category' => 'Laptops',
                'attribute_family' => 'Laptop Attributes',
                'short_description' => 'Business laptop with legendary ThinkPad durability and performance.',
                'description' => 'The ThinkPad X1 Carbon features a 14-inch display, Intel Core i7 processor, up to 32GB RAM, up to 2TB SSD, and military-grade durability. Perfect for business professionals.',
                'price' => 1699.00,
                'compare_price' => 1899.00,
                'cost' => 1300.00,
                'quantity' => 22,
                'low_stock_threshold' => 5,
            ],

            // Audio Devices
            [
                'name' => 'AirPods Pro (2nd Generation)',
                'brand' => 'Apple',
                'category' => 'Earbuds',
                'attribute_family' => 'Audio Device Attributes',
                'short_description' => 'Premium wireless earbuds with Active Noise Cancellation and Spatial Audio.',
                'description' => 'AirPods Pro feature Active Noise Cancellation, Adaptive Transparency, Personalized Spatial Audio, and up to 6 hours of listening time with ANC enabled. Includes MagSafe Charging Case.',
                'price' => 249.00,
                'compare_price' => 279.00,
                'cost' => 180.00,
                'quantity' => 100,
                'low_stock_threshold' => 20,
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'brand' => 'Sony',
                'category' => 'Headphones',
                'attribute_family' => 'Audio Device Attributes',
                'short_description' => 'Industry-leading noise canceling headphones with exceptional sound quality.',
                'description' => 'The WH-1000XM5 features industry-leading noise cancellation, 30-hour battery life, quick charge (3 min for 3 hours), and premium sound quality with LDAC support.',
                'price' => 399.00,
                'compare_price' => 449.00,
                'cost' => 280.00,
                'quantity' => 60,
                'low_stock_threshold' => 15,
            ],
            [
                'name' => 'Bose QuietComfort Earbuds II',
                'brand' => 'Bose',
                'category' => 'Earbuds',
                'attribute_family' => 'Audio Device Attributes',
                'short_description' => 'Premium earbuds with world-class noise cancellation.',
                'description' => 'QuietComfort Earbuds II feature CustomTune technology, world-class noise cancellation, up to 6 hours of battery life, and wireless charging case.',
                'price' => 279.00,
                'compare_price' => 329.00,
                'cost' => 200.00,
                'quantity' => 75,
                'low_stock_threshold' => 15,
            ],
            [
                'name' => 'JBL Flip 6',
                'brand' => 'JBL',
                'category' => 'Speakers',
                'attribute_family' => 'Audio Device Attributes',
                'short_description' => 'Portable Bluetooth speaker with powerful sound and waterproof design.',
                'description' => 'The Flip 6 features JBL Pro Sound, IPX7 waterproof rating, 12 hours of playtime, and PartyBoost for stereo sound. Available in multiple colors.',
                'price' => 129.00,
                'compare_price' => 149.00,
                'cost' => 80.00,
                'quantity' => 80,
                'low_stock_threshold' => 20,
            ],

            // Accessories
            [
                'name' => 'MagSafe Wireless Charger',
                'brand' => 'Apple',
                'category' => 'Chargers',
                'attribute_family' => 'Accessory Attributes',
                'short_description' => 'Fast wireless charging with MagSafe technology.',
                'description' => 'The MagSafe Charger delivers up to 15W of power for fast wireless charging. Compatible with iPhone 12 and later, and AirPods with wireless charging case.',
                'price' => 39.00,
                'compare_price' => 49.00,
                'cost' => 25.00,
                'quantity' => 150,
                'low_stock_threshold' => 30,
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra Case',
                'brand' => 'Samsung',
                'category' => 'Phone Cases',
                'attribute_family' => 'Accessory Attributes',
                'short_description' => 'Protective case designed specifically for Galaxy S24 Ultra.',
                'description' => 'Official Samsung case with S Pen slot, raised edges for screen protection, and premium materials. Available in multiple colors.',
                'price' => 49.99,
                'compare_price' => 59.99,
                'cost' => 30.00,
                'quantity' => 120,
                'low_stock_threshold' => 25,
            ],
            [
                'name' => 'Anker PowerCore 20000',
                'brand' => 'Xiaomi',
                'category' => 'Power Banks',
                'attribute_family' => 'Accessory Attributes',
                'short_description' => 'High-capacity portable charger with fast charging support.',
                'description' => 'The PowerCore 20000 features 20,000mAh capacity, PowerIQ technology for fast charging, dual USB-A ports, and can charge most phones multiple times.',
                'price' => 59.99,
                'compare_price' => 79.99,
                'cost' => 35.00,
                'quantity' => 90,
                'low_stock_threshold' => 20,
            ],
            [
                'name' => 'USB-C to Lightning Cable',
                'brand' => 'Apple',
                'category' => 'Cables',
                'attribute_family' => 'Accessory Attributes',
                'short_description' => 'Official Apple USB-C to Lightning cable for fast charging.',
                'description' => '1-meter USB-C to Lightning cable supports fast charging and data transfer. MFi certified for compatibility and safety.',
                'price' => 19.00,
                'compare_price' => 29.00,
                'cost' => 12.00,
                'quantity' => 200,
                'low_stock_threshold' => 40,
            ],

            // Gaming
            [
                'name' => 'PlayStation 5',
                'brand' => 'PlayStation',
                'category' => 'Gaming Consoles',
                'attribute_family' => 'Gaming Device Attributes',
                'short_description' => 'Next-generation gaming console with 4K gaming and ray tracing.',
                'description' => 'The PlayStation 5 features an AMD Zen 2 processor, custom RDNA 2 GPU, 825GB SSD, 4K gaming at 120fps, ray tracing, and 3D Audio. Includes DualSense wireless controller.',
                'price' => 499.00,
                'compare_price' => 549.00,
                'cost' => 400.00,
                'quantity' => 15,
                'low_stock_threshold' => 3,
            ],
            [
                'name' => 'Xbox Series X',
                'brand' => 'Xbox',
                'category' => 'Gaming Consoles',
                'attribute_family' => 'Gaming Device Attributes',
                'short_description' => 'Most powerful Xbox console with 4K gaming and Game Pass.',
                'description' => 'The Xbox Series X features 12 TFLOPs of processing power, 4K gaming at 60fps (up to 120fps), 1TB SSD, and backward compatibility. Includes Xbox Wireless Controller.',
                'price' => 499.00,
                'compare_price' => 549.00,
                'cost' => 400.00,
                'quantity' => 12,
                'low_stock_threshold' => 3,
            ],
            [
                'name' => 'Razer DeathAdder V3',
                'brand' => 'Razer',
                'category' => 'Gaming Accessories',
                'attribute_family' => 'Gaming Device Attributes',
                'short_description' => 'Professional gaming mouse with 30,000 DPI sensor.',
                'description' => 'The DeathAdder V3 features a Focus Pro 30K optical sensor, 90-hour battery life, 8 programmable buttons, and ergonomic design for extended gaming sessions.',
                'price' => 99.99,
                'compare_price' => 129.99,
                'cost' => 60.00,
                'quantity' => 50,
                'low_stock_threshold' => 10,
            ],
        ];

        foreach ($products as $productData) {
            $brand = $brands->where('name', $productData['brand'])->first();
            $category = $categories->where('name', $productData['category'])->first();
            $attributeFamily = $attributeFamilies->where('name', $productData['attribute_family'])->first();

            if (! $brand || ! $category || ! $attributeFamily) {
                continue;
            }

            // Set all products to Approved status and published
            $status = ProductStatus::Approved;
            $published = true;
            $reviewerId = $reviewer->id;
            $reviewedAt = now()->subDays(rand(1, 30));

            $sku = 'SKU-'.strtoupper(Str::random(8));

            $product = Product::firstOrCreate(
                ['slug' => Str::slug($productData['name'])],
                [
                    'name' => $productData['name'],
                    'sku' => $sku,
                    'brand_id' => $brand->id,
                    'category_id' => $category->id,
                    'attribute_family_id' => $attributeFamily->id,
                    'short_description' => $productData['short_description'],
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'compare_price' => $productData['compare_price'],
                    'cost' => $productData['cost'],
                    'quantity' => $productData['quantity'],
                    'low_stock_threshold' => $productData['low_stock_threshold'],
                    'status' => $status,
                    'published' => $published,
                    'is_featured' => (bool) rand(0, 1),
                    'creator_id' => $creator->id,
                    'reviewer_id' => $reviewerId,
                    'reviewed_at' => $reviewedAt,
                ]
            );

            // Update existing products to ensure they're approved and published
            if ($product->wasRecentlyCreated === false) {
                $product->update([
                    'status' => $status,
                    'published' => $published,
                    'reviewer_id' => $reviewerId,
                    'reviewed_at' => $reviewedAt,
                ]);
            }

            // Create variants for products that support them
            $this->createProductVariants($product, $productData, $attributeFamily);
        }

        $this->ensureCategoryCoverage($categories, $brands, $attributeFamilies, $creator, $reviewer);
        $this->ensureVariantPresence();
    }

    /**
     * Create variants for products that support them.
     */
    private function createProductVariants(Product $product, array $productData, AttributeFamily $attributeFamily): void
    {
        // Skip if product already has variants
        if ($product->variants()->exists()) {
            return;
        }

        $categoryName = $productData['category'];
        $productName = $productData['name'];

        // Smartphones: Color + Storage variants
        if ($categoryName === 'Smartphones') {
            $this->createSmartphoneVariants($product, $productData, $attributeFamily);
        }
        // Laptops: Storage + RAM variants
        elseif ($categoryName === 'Laptops') {
            $this->createLaptopVariants($product, $productData, $attributeFamily);
        }
        // Audio Devices: Color variants
        elseif (in_array($categoryName, ['Earbuds', 'Headphones', 'Speakers'])) {
            $this->createAudioVariants($product, $productData, $attributeFamily);
        }
        // Accessories: Color variants (for cases, some accessories)
        elseif (in_array($categoryName, ['Phone Cases']) || str_contains(strtolower($productName), 'case')) {
            $this->createAccessoryVariants($product, $productData, $attributeFamily);
        }
        // Gaming Accessories: Color variants
        elseif ($categoryName === 'Gaming Accessories') {
            $this->createGamingAccessoryVariants($product, $productData, $attributeFamily);
        }
    }

    /**
     * Create smartphone variants (Color + Storage).
     */
    private function createSmartphoneVariants(Product $product, array $productData, AttributeFamily $attributeFamily): void
    {
        $colorAttribute = $this->getAttribute('Color', $attributeFamily->id);
        $storageAttribute = $this->getAttribute('Storage', $attributeFamily->id);

        if (! $colorAttribute || ! $storageAttribute) {
            return;
        }

        // Define color and storage combinations based on product
        $colors = $this->getAttributeValues($colorAttribute->id, ['Black', 'White', 'Blue', 'Silver', 'Space Gray', 'Gold']);
        $storages = $this->getAttributeValues($storageAttribute->id, ['128GB', '256GB', '512GB', '1TB']);

        if ($colors->isEmpty() || $storages->isEmpty()) {
            return;
        }

        // Limit to 3-4 variants per product
        $colorCount = min(3, $colors->count());
        $storageCount = min(2, $storages->count());
        $selectedColors = $colors->random($colorCount);
        $selectedStorages = $storages->random($storageCount);

        // Ensure we have collections
        $selectedColors = $selectedColors instanceof \Illuminate\Support\Collection ? $selectedColors : collect([$selectedColors]);
        $selectedStorages = $selectedStorages instanceof \Illuminate\Support\Collection ? $selectedStorages : collect([$selectedStorages]);

        $basePrice = $productData['price'];
        $baseQuantity = $productData['quantity'];
        $variantCount = $selectedColors->count() * $selectedStorages->count();
        $quantityPerVariant = max(1, (int) ($baseQuantity / $variantCount));

        $sortOrder = 1;
        $isFirst = true;

        foreach ($selectedColors as $color) {
            foreach ($selectedStorages as $storage) {
                // Calculate price based on storage
                $priceMultiplier = match ($storage->value) {
                    '128GB' => 1.0,
                    '256GB' => 1.1,
                    '512GB' => 1.2,
                    '1TB' => 1.3,
                    default => 1.0,
                };
                $variantPrice = round($basePrice * $priceMultiplier, 2);
                $variantComparePrice = round($productData['compare_price'] * $priceMultiplier, 2);
                $variantCost = round($productData['cost'] * $priceMultiplier, 2);

                $variantSku = $product->sku.'-'.strtoupper(substr($color->value, 0, 3)).'-'.str_replace('GB', '', $storage->value);

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $variantSku,
                    'name' => $color->value.' / '.$storage->value,
                    'price' => $variantPrice,
                    'compare_price' => $variantComparePrice,
                    'cost' => $variantCost,
                    'quantity' => $quantityPerVariant,
                    'low_stock_threshold' => max(1, (int) ($productData['low_stock_threshold'] / $variantCount)),
                    'is_default' => $isFirst,
                    'sort_order' => $sortOrder++,
                ]);

                // Create variant options
                ProductVariantOption::create([
                    'product_variant_id' => $variant->id,
                    'attribute_id' => $colorAttribute->id,
                    'value' => $color->value,
                ]);

                ProductVariantOption::create([
                    'product_variant_id' => $variant->id,
                    'attribute_id' => $storageAttribute->id,
                    'value' => $storage->value,
                ]);

                $isFirst = false;
            }
        }
    }

    /**
     * Create laptop variants (Storage + RAM).
     */
    private function createLaptopVariants(Product $product, array $productData, AttributeFamily $attributeFamily): void
    {
        $storageAttribute = $this->getAttribute('Storage', $attributeFamily->id);
        $ramAttribute = $this->getAttribute('RAM', $attributeFamily->id);

        if (! $storageAttribute || ! $ramAttribute) {
            return;
        }

        // Define storage and RAM combinations
        $storages = $this->getAttributeValues($storageAttribute->id, ['512GB', '1TB', '2TB']);
        $rams = $this->getAttributeValues($ramAttribute->id, ['16GB', '32GB', '64GB']);

        if ($storages->isEmpty() || $rams->isEmpty()) {
            return;
        }

        // Limit to 2-3 variants
        $storageCount = min(2, $storages->count());
        $ramCount = min(2, $rams->count());
        $selectedStorages = $storages->random($storageCount);
        $selectedRams = $rams->random($ramCount);

        // Ensure we have collections
        $selectedStorages = $selectedStorages instanceof \Illuminate\Support\Collection ? $selectedStorages : collect([$selectedStorages]);
        $selectedRams = $selectedRams instanceof \Illuminate\Support\Collection ? $selectedRams : collect([$selectedRams]);

        $basePrice = $productData['price'];
        $baseQuantity = $productData['quantity'];
        $variantCount = $selectedStorages->count() * $selectedRams->count();
        $quantityPerVariant = max(1, (int) ($baseQuantity / $variantCount));

        $sortOrder = 1;
        $isFirst = true;

        foreach ($selectedStorages as $storage) {
            foreach ($selectedRams as $ram) {
                // Calculate price based on storage and RAM
                $storageMultiplier = match ($storage->value) {
                    '512GB' => 1.0,
                    '1TB' => 1.15,
                    '2TB' => 1.3,
                    default => 1.0,
                };
                $ramMultiplier = match ($ram->value) {
                    '16GB' => 1.0,
                    '32GB' => 1.1,
                    '64GB' => 1.2,
                    default => 1.0,
                };
                $variantPrice = round($basePrice * $storageMultiplier * $ramMultiplier, 2);
                $variantComparePrice = round($productData['compare_price'] * $storageMultiplier * $ramMultiplier, 2);
                $variantCost = round($productData['cost'] * $storageMultiplier * $ramMultiplier, 2);

                $variantSku = $product->sku.'-'.str_replace('GB', '', $storage->value).'-'.str_replace('GB', '', $ram->value);

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $variantSku,
                    'name' => $storage->value.' / '.$ram->value,
                    'price' => $variantPrice,
                    'compare_price' => $variantComparePrice,
                    'cost' => $variantCost,
                    'quantity' => $quantityPerVariant,
                    'low_stock_threshold' => max(1, (int) ($productData['low_stock_threshold'] / $variantCount)),
                    'is_default' => $isFirst,
                    'sort_order' => $sortOrder++,
                ]);

                // Create variant options
                ProductVariantOption::create([
                    'product_variant_id' => $variant->id,
                    'attribute_id' => $storageAttribute->id,
                    'value' => $storage->value,
                ]);

                ProductVariantOption::create([
                    'product_variant_id' => $variant->id,
                    'attribute_id' => $ramAttribute->id,
                    'value' => $ram->value,
                ]);

                $isFirst = false;
            }
        }
    }

    /**
     * Create audio device variants (Color).
     */
    private function createAudioVariants(Product $product, array $productData, AttributeFamily $attributeFamily): void
    {
        $colorAttribute = $this->getAttribute('Color', $attributeFamily->id);

        if (! $colorAttribute) {
            return;
        }

        // Define colors
        $colors = $this->getAttributeValues($colorAttribute->id, ['Black', 'White', 'Blue', 'Silver']);

        if ($colors->isEmpty()) {
            return;
        }

        // Limit to 2-3 color variants
        $colorCount = min(3, $colors->count());
        $selectedColors = $colors->random($colorCount);

        // Ensure we have a collection
        $selectedColors = $selectedColors instanceof \Illuminate\Support\Collection ? $selectedColors : collect([$selectedColors]);

        $baseQuantity = $productData['quantity'];
        $variantCount = $selectedColors->count();
        $quantityPerVariant = max(1, (int) ($baseQuantity / $variantCount));

        $sortOrder = 1;
        $isFirst = true;

        foreach ($selectedColors as $color) {
            $variantSku = $product->sku.'-'.strtoupper(substr($color->value, 0, 3));

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $variantSku,
                'name' => $color->value,
                'price' => $productData['price'],
                'compare_price' => $productData['compare_price'],
                'cost' => $productData['cost'],
                'quantity' => $quantityPerVariant,
                'low_stock_threshold' => max(1, (int) ($productData['low_stock_threshold'] / $variantCount)),
                'is_default' => $isFirst,
                'sort_order' => $sortOrder++,
            ]);

            // Create variant option
            ProductVariantOption::create([
                'product_variant_id' => $variant->id,
                'attribute_id' => $colorAttribute->id,
                'value' => $color->value,
            ]);

            $isFirst = false;
        }
    }

    /**
     * Create accessory variants (Color).
     */
    private function createAccessoryVariants(Product $product, array $productData, AttributeFamily $attributeFamily): void
    {
        $colorAttribute = $this->getAttribute('Color', $attributeFamily->id);

        if (! $colorAttribute) {
            return;
        }

        // Define colors
        $colors = $this->getAttributeValues($colorAttribute->id, ['Black', 'White', 'Blue', 'Red', 'Silver']);

        if ($colors->isEmpty()) {
            return;
        }

        // Limit to 2-3 color variants
        $colorCount = min(3, $colors->count());
        $selectedColors = $colors->random($colorCount);

        // Ensure we have a collection
        $selectedColors = $selectedColors instanceof \Illuminate\Support\Collection ? $selectedColors : collect([$selectedColors]);

        $baseQuantity = $productData['quantity'];
        $variantCount = $selectedColors->count();
        $quantityPerVariant = max(1, (int) ($baseQuantity / $variantCount));

        $sortOrder = 1;
        $isFirst = true;

        foreach ($selectedColors as $color) {
            $variantSku = $product->sku.'-'.strtoupper(substr($color->value, 0, 3));

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $variantSku,
                'name' => $color->value,
                'price' => $productData['price'],
                'compare_price' => $productData['compare_price'],
                'cost' => $productData['cost'],
                'quantity' => $quantityPerVariant,
                'low_stock_threshold' => max(1, (int) ($productData['low_stock_threshold'] / $variantCount)),
                'is_default' => $isFirst,
                'sort_order' => $sortOrder++,
            ]);

            // Create variant option
            ProductVariantOption::create([
                'product_variant_id' => $variant->id,
                'attribute_id' => $colorAttribute->id,
                'value' => $color->value,
            ]);

            $isFirst = false;
        }
    }

    /**
     * Create gaming accessory variants (Color).
     */
    private function createGamingAccessoryVariants(Product $product, array $productData, AttributeFamily $attributeFamily): void
    {
        $colorAttribute = $this->getAttribute('Color', $attributeFamily->id);

        if (! $colorAttribute) {
            return;
        }

        // Define colors
        $colors = $this->getAttributeValues($colorAttribute->id, ['Black', 'White', 'Red', 'Blue']);

        if ($colors->isEmpty()) {
            return;
        }

        // Limit to 2-3 color variants
        $colorCount = min(3, $colors->count());
        $selectedColors = $colors->random($colorCount);

        // Ensure we have a collection
        $selectedColors = $selectedColors instanceof \Illuminate\Support\Collection ? $selectedColors : collect([$selectedColors]);

        $baseQuantity = $productData['quantity'];
        $variantCount = $selectedColors->count();
        $quantityPerVariant = max(1, (int) ($baseQuantity / $variantCount));

        $sortOrder = 1;
        $isFirst = true;

        foreach ($selectedColors as $color) {
            $variantSku = $product->sku.'-'.strtoupper(substr($color->value, 0, 3));

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $variantSku,
                'name' => $color->value,
                'price' => $productData['price'],
                'compare_price' => $productData['compare_price'],
                'cost' => $productData['cost'],
                'quantity' => $quantityPerVariant,
                'low_stock_threshold' => max(1, (int) ($productData['low_stock_threshold'] / $variantCount)),
                'is_default' => $isFirst,
                'sort_order' => $sortOrder++,
            ]);

            // Create variant option
            ProductVariantOption::create([
                'product_variant_id' => $variant->id,
                'attribute_id' => $colorAttribute->id,
                'value' => $color->value,
            ]);

            $isFirst = false;
        }
    }

    /**
     * Get an attribute by name and family ID.
     */
    private function getAttribute(string $name, string $familyId): ?Attribute
    {
        $cacheKey = "{$name}_{$familyId}";

        if (! isset($this->attributesCache[$cacheKey])) {
            $this->attributesCache[$cacheKey] = Attribute::where('name', $name)
                ->where('attribute_family_id', $familyId)
                ->first();
        }

        return $this->attributesCache[$cacheKey];
    }

    /**
     * Get attribute values by attribute ID and filter by value strings.
     */
    private function getAttributeValues(string $attributeId, array $valueFilters = []): \Illuminate\Support\Collection
    {
        $cacheKey = "{$attributeId}_".implode(',', $valueFilters);

        if (! isset($this->attributeValuesCache[$cacheKey])) {
            $query = AttributeValue::where('attribute_id', $attributeId)
                ->where('is_active', true);

            if (! empty($valueFilters)) {
                $query->whereIn('value', $valueFilters);
            }

            $this->attributeValuesCache[$cacheKey] = $query->get();
        }

        return $this->attributeValuesCache[$cacheKey];
    }

    private function ensureCategoryCoverage($categories, $brands, $attributeFamilies, User $creator, User $reviewer): void
    {
        if ($brands->isEmpty() || $attributeFamilies->isEmpty()) {
            return;
        }

        $defaultBrand = $brands->first();
        $defaultFamily = $attributeFamilies->first();

        foreach ($categories as $category) {
            if ($category->products()->exists()) {
                continue;
            }

            $baseName = $category->name.' Essentials '.Str::upper(Str::random(4));
            $price = rand(50, 900);
            $comparePrice = $price + rand(20, 150);
            $cost = max(10, (int) ($price * 0.6));
            $quantity = rand(5, 40);

            $product = Product::create([
                'name' => $baseName,
                'slug' => Str::slug($baseName).'-'.Str::lower(Str::random(4)),
                'sku' => 'SKU-'.Str::upper(Str::random(10)),
                'brand_id' => $defaultBrand->id,
                'category_id' => $category->id,
                'attribute_family_id' => $this->resolveAttributeFamilyId($category->name, $attributeFamilies, $defaultFamily),
                'short_description' => 'Auto-generated seed product to keep this category stocked.',
                'description' => 'Seeded catalogue item used to populate the storefront. Replace with real content.',
                'price' => $price,
                'compare_price' => $comparePrice,
                'cost' => $cost,
                'quantity' => $quantity,
                'low_stock_threshold' => max(2, (int) ($quantity * 0.2)),
                'status' => ProductStatus::Approved,
                'published' => true,
                'is_featured' => (bool) rand(0, 1),
                'creator_id' => $creator->id,
                'reviewer_id' => $reviewer->id,
                'reviewed_at' => now()->subDays(rand(1, 7)),
            ]);

            $this->createFallbackVariant($product);
        }
    }

    private function resolveAttributeFamilyId(string $categoryName, $attributeFamilies, AttributeFamily $defaultFamily): string
    {
        $name = Str::lower($categoryName);
        $mapping = [
            'phone' => 'Smartphone Attributes',
            'tablet' => 'Smartphone Attributes',
            'watch' => 'Smartphone Attributes',
            'wearable' => 'Smartphone Attributes',
            'laptop' => 'Laptop Attributes',
            'desktop' => 'Laptop Attributes',
            'monitor' => 'Laptop Attributes',
            'audio' => 'Audio Device Attributes',
            'earbud' => 'Audio Device Attributes',
            'headphone' => 'Audio Device Attributes',
            'speaker' => 'Audio Device Attributes',
            'charger' => 'Accessory Attributes',
            'case' => 'Accessory Attributes',
            'accessor' => 'Accessory Attributes',
            'gaming' => 'Gaming Device Attributes',
        ];

        foreach ($mapping as $keyword => $familyName) {
            if (str_contains($name, $keyword)) {
                return $attributeFamilies->firstWhere('name', $familyName)?->id ?? $defaultFamily->id;
            }
        }

        return $defaultFamily->id;
    }

    private function ensureVariantPresence(): void
    {
        Product::query()->each(function (Product $product) {
            if ($product->variants()->exists()) {
                return;
            }

            $this->createFallbackVariant($product);
        });
    }

    private function createFallbackVariant(Product $product): void
    {
        if ($product->variants()->exists()) {
            return;
        }

        $baseSku = $product->sku ?: 'SKU-'.Str::upper(Str::random(8));

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $baseSku.'-VAR',
            'name' => 'Default Variant',
            'price' => $product->price,
            'compare_price' => $product->compare_price,
            'cost' => $product->cost,
            'quantity' => $product->quantity ?? 0,
            'low_stock_threshold' => $product->low_stock_threshold ?? 0,
            'is_default' => true,
            'sort_order' => 1,
        ]);
    }
}
