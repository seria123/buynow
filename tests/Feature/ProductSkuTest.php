<?php

namespace Tests\Feature;

use App\Models\Catalogue\Category;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductVariant;
use App\Models\User;
use App\Settings\ProductSkuSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductSkuTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_uses_category_pattern_for_sku(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        /** @var ProductSkuSettings $settings */
        $settings = app(ProductSkuSettings::class);
        $settings->category_patterns = [
            [
                'category_id' => $category->id,
                'prefix' => 'ELEC',
                'separator' => '#',
            ],
        ];
        $settings->save();

        $product = Product::create([
            'name' => 'Noise Cancelling Headphones',
            'slug' => 'noise-cancelling-'.Str::uuid(),
            'category_id' => $category->id,
            'price' => 199.99,
            'creator_id' => $user->id,
        ]);

        $this->assertNotNull($product->sku);
        $this->assertStringStartsWith('SKU-ELEC#', $product->sku);
        $this->assertMatchesRegularExpression('/^SKU-ELEC#[0-9A-F]{8}$/', $product->sku);
    }

    public function test_product_uses_default_sku_format_with_category_name(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        $product = Product::create([
            'name' => 'Wireless Mouse',
            'slug' => 'wireless-mouse-'.Str::uuid(),
            'category_id' => $category->id,
            'price' => 29.99,
            'creator_id' => $user->id,
        ]);

        $this->assertNotNull($product->sku);
        $this->assertStringStartsWith('SKU(ELECTRONICS)-', $product->sku);
        $this->assertMatchesRegularExpression('/^SKU\(ELECTRONICS\)-[0-9A-F]{8}$/', $product->sku);
    }

    public function test_product_uses_default_sku_format_without_category(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Generic Product',
            'slug' => 'generic-product-'.Str::uuid(),
            'category_id' => null,
            'price' => 19.99,
            'creator_id' => $user->id,
        ]);

        $this->assertNotNull($product->sku);
        $this->assertStringStartsWith('SKU-', $product->sku);
        $this->assertMatchesRegularExpression('/^SKU-[0-9A-F]{8}$/', $product->sku);
    }

    public function test_variant_uses_default_sku_format_with_category_name(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'is_active' => true,
            'creator_id' => $user->id,
        ]);

        $product = Product::create([
            'name' => 'Wireless Mouse',
            'slug' => 'wireless-mouse-'.Str::uuid(),
            'category_id' => $category->id,
            'price' => 29.99,
            'creator_id' => $user->id,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Black',
            'price' => 29.99,
            'quantity' => 10,
        ]);

        $this->assertNotNull($variant->sku);
        $this->assertStringStartsWith('SKU-VAR(ELECTRONICS)-', $variant->sku);
        $this->assertMatchesRegularExpression('/^SKU-VAR\(ELECTRONICS\)-[0-9A-F]{8}$/', $variant->sku);
    }

    public function test_variant_uses_default_sku_format_without_category(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Generic Product',
            'slug' => 'generic-product-'.Str::uuid(),
            'category_id' => null,
            'price' => 19.99,
            'creator_id' => $user->id,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Standard',
            'price' => 19.99,
            'quantity' => 5,
        ]);

        $this->assertNotNull($variant->sku);
        $this->assertStringStartsWith('SKU-VAR-', $variant->sku);
        $this->assertMatchesRegularExpression('/^SKU-VAR-[0-9A-F]{8}$/', $variant->sku);
    }
}
